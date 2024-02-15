<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use SoapClient;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use App\Models\User;
use App\Models\DlRequest;
use App\Models\UserBankAccount;
use App\Models\DlRequestStatusLog;
use App\Models\BankVerificationLog;

class FinancialAccountController extends Controller
{

  public function index() {

    // $this->createDecisionLogicRequest();

    return view('frontend.financialAccount.index');
  }

  // Submits bank information to get transaction data
  public function createDecisionLogicRequest(Request $request) {

    // Get POST Data
    $postData = $request->all();
    $userId = $postData['userData']['userId'];

    // Check if the submitted routing and account number already exist for this customer
    // TODO: This needs to be revisited, it's important to not allow users accidentally
    // access other people's account information if they enter the wrong data that does
    // exist for someone else
    $accountCheck = UserBankAccount::where('routing_number', '=', $postData['userData']['bankRoutingNumber'])
    ->where('account_number', '=', $postData['userData']['bankAccountNumber'])
    ->first();

    if(!is_null($accountCheck)) {
      return ['success' => false, 'msg' => 'This account has already been submitted.'];
    }

    // Get user data from $_POST provided userId
    $user = User::where('id', '=', $postData['userData']['userId'])->first();

    // IF the user does not exist stop and return a failure message
    if(!$user) {
      return ['success' => false, 'msg' => 'Error while fetching user data. Process terminated.'];
    }

    // Add a wm (WalletMojo) stamp to id sent to Decision Logic
    $postData['userData']['userId'] = 'wm-' . $postData['userData']['userId'];

    // Begin Decision Logic soap client call process
    $client = new SoapClient(config('soap-apis.decisionLogic.soapClientUrl'));

    // Gather data for soap call
    $paramArray = [
      'serviceKey' => config('soap-apis.decisionLogic.serviceKey'),
      'profileGuid' => config('soap-apis.decisionLogic.profileGuid'),
      'siteUserGuid' => config('soap-apis.decisionLogic.siteUserGuid'),
      'customerId' => $postData['userData']['userId'],
      'firstName' => $user->first_name,
      'lastName' => $user->last_name,
      'accountNumber' => $postData['userData']['bankAccountNumber'],
      'routingNumber' => $postData['userData']['bankRoutingNumber'],
      'contentServiceId' => '',
      'emailAddress' => $postData['userData']['userEmail']
    ];

    // Send soap call
    $response = $client->CreateRequest4($paramArray)->CreateRequest4Result;

    // Record report in WalletMojo IF soap call returns code successfully
    // DL returns a code (string) of 6 characters on success - i.e. 'ABC123'
    if(is_string($response) && count(str_split($response)) === 6) {

      $newDLReportDetails = $client->GetReportDetailFromRequestCode7([
        'serviceKey' => config('soap-apis.decisionLogic.serviceKey'),
        'requestCode' => $response
      ]);

      if(!$newDLReportDetails->GetReportDetailFromRequestCode7Result->IsError) {

        $newReportData = $newDLReportDetails->GetReportDetailFromRequestCode7Result;

        // Create a new entry in user_bank_accounts
        $newBankAccountEntry = new UserBankAccount;

        $newBankAccountEntry->user_id = $userId;
        $newBankAccountEntry->routing_number = $postData['userData']['bankRoutingNumber'];
        $newBankAccountEntry->account_number = $postData['userData']['bankAccountNumber'];
        $newBankAccountEntry->institution = $newReportData->InstitutionName;
        $newBankAccountEntry->created_by = Auth::user()->id;
        $newBankAccountEntry->updated_by = Auth::user()->id;

        $newBankAccountEntry->save();

        // Create a new entry in dl_requests
        $newReportEntry = new DlRequest;

        $newReportEntry->user_id = $userId;
        $newReportEntry->dl_request_code = $response;
        $newReportEntry->user_bank_account_id = $newBankAccountEntry->id;
        $newReportEntry->dl_date_created = date('Y-m-d H:i:s');
        $newReportEntry->dl_customer_id = $newReportData->CustomerIdentifier;
        $newReportEntry->dl_customer_name_entered = $newReportData->NameEntered;
        $newReportEntry->dl_customer_name_found = $newReportData->NameFound;
        $newReportEntry->created_by = Auth::user()->id;
        $newReportEntry->updated_by = Auth::user()->id;

        $newReportEntry->save();

        // Create a new entry in dl_request_status_logs
        $newRequestStatusLogEntry = new DlRequestStatusLog;

        $newRequestStatusLogEntry->dl_request_id = $newReportEntry->id;
        $newRequestStatusLogEntry->bank_account_id = $newBankAccountEntry->id;
        $newRequestStatusLogEntry->request_status = $newReportData->Status;
        $newRequestStatusLogEntry->created_by = Auth::user()->id;
        $newRequestStatusLogEntry->updated_by = Auth::user()->id;

        $newRequestStatusLogEntry->save();

      } else {
        return ['success' => false, 'msg' => 'Decision Logic failed to return newly created request data.'];
      }

    } else {
      return ['success' => false, 'msg' => 'Decision Logic failed to return new request id.'];
    }

    return ['success' => true, 'msg' => 'Decision Logic Request created and saved successfully.'];
  }

  public function getUserDLRequests($userId) {

    $msg = '';

    if(Auth::user()->id != $userId) {
      return ['success' => false, 'msg' => 'Permission required to view content.'];
    }

    // TODO Additional data available to this query for page redesign
    try {
      $userRequests = DlRequest::selectRaw('
        dl_requests.id AS id,
        dl_requests.user_bank_account_id,
        MAX(request_status) AS request_status,
        institution,
        routing_number,
        account_number,
        dl_request_code,
        dl_requests.dl_date_created,
        bank_verification_logs.name_found AS name_found
      ')
      ->leftJoin('user_bank_accounts', 'user_bank_accounts.user_id', '=', 'dl_requests.user_id')
      ->leftJoin('dl_request_status_logs', 'dl_request_status_logs.dl_request_id', '=', 'dl_requests.id')
      ->leftJoin('bank_verification_logs', 'bank_verification_logs.dl_requests_id', '=', 'dl_requests.id')
      ->where('dl_requests.user_id', '=', Auth::user()->id)
      ->groupBy('dl_request_status_logs.dl_request_id')
      ->orderBy('dl_requests.created_at', 'DESC')
      ->limit(5)
      ->get();
    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    if($userRequests) {
      if($userRequests->count() > 0) {
        foreach($userRequests as $request) {

          $request['name_found'] = str_replace('+', ' ', $request['name_found']);

          switch($request->request_status) {
            case -2:
              $request['request_status_text'] = 'Bank Error';
              $request['request_status_color'] = '#CC3333';
              break;
            case -1:
              $request['request_status_text'] = 'Account Error';
              $request['request_status_color'] = '#FFBF00';
              break;
            case 0:
              $request['request_status_text'] = 'Not Started';
              $request['request_status_color'] = '#D0D0D0';
              break;
            case 1:
              $request['request_status_text'] = 'Started, Not Completed';
              $request['request_status_color'] = '#A0A0A0';
              break;
            case 2:
              $request['request_status_text'] = 'Login, Not Verified';
              $request['request_status_color'] = '#44AA44';
              break;
            case 3:
              $request['request_status_text'] = 'Login, Verified';
              $request['request_status_color'] = '#228822';
              break;
            default:
              $request['request_status_text'] = 'n/a';
              $request['request_status_color'] = '#000000';
          }
        }

        return [
          'success' => true,
          'msg' => 'Successfully retrieved ' . $userRequests->count() . ' Decision Logic user request(s).',
          'data' => $userRequests
        ];
      } else {
        return ['success' => true, 'msg' => 'No Decision Logic user requests found.', 'data' => $userRequests];
      }
    } else {
      return ['success' => false, 'msg' => 'Error: ' . $msg, 'data' => $userRequests];
    }

  }

  public function dlIframeBankData(Request $request) {

    // TODO Add another measure where users can fetch any available reports
    // directly from Decision Logic in case this function doesn't succesfully
    // record data into the database (Probably work intensive feature)

    $post = $request->all();
    $bankDataArray = $post['bankData'];
    $msg = '';

    try {
      DB::transaction(function() use ($post, $bankDataArray) {
        // Create a new entry in bank_verification_logs
        $newBankVerificationEntry = new BankVerificationLog;

        $newBankVerificationEntry->dl_requests_id = $post['requestId'];
        $newBankVerificationEntry->dl_requests_user_bank_account_id = $post['requestBankAccountId'];
        $newBankVerificationEntry->is_login_valid = $this->convertBooleanToInt($bankDataArray['IsLoginValid']);
        $newBankVerificationEntry->account_number_confidence = $bankDataArray['AccountNumberConfidence'];
        $newBankVerificationEntry->available_balance_found = $bankDataArray['AvailableBalanceFound'];
        $newBankVerificationEntry->current_balance_found = $bankDataArray['CurrentBalanceFound'];
        $newBankVerificationEntry->is_account_number_match = $this->convertBooleanToInt($bankDataArray['IsAccountNumberMatch']);
        $newBankVerificationEntry->is_amount_verified = $this->convertBooleanToInt($bankDataArray['IsAmountVerified']);
        $newBankVerificationEntry->is_name_match = $this->convertBooleanToInt($bankDataArray['IsNameMatch']);
        $newBankVerificationEntry->is_verified = $this->convertBooleanToInt($bankDataArray['IsVerified']);
        $newBankVerificationEntry->name_confidence = $bankDataArray['NameConfidence'];
        $newBankVerificationEntry->name_found = $bankDataArray['NameFound'];
        $newBankVerificationEntry->bank_type = $bankDataArray['BankType'];
        $newBankVerificationEntry->total_deposits = $bankDataArray['TotalDeposits'];
        $newBankVerificationEntry->total_withdrawals = $bankDataArray['TotalWithdrawals'];
        $newBankVerificationEntry->transactions_from_date = $this->convertDLTimeStampToSQL($bankDataArray['TransactionsFromDate']);
        $newBankVerificationEntry->transactions_to_date = $this->convertDLTimeStampToSQL($bankDataArray['TransactionsToDate']);
        $newBankVerificationEntry->created_by = Auth::user()->id;
        $newBankVerificationEntry->updated_by = Auth::user()->id;

        $newBankVerificationEntry->save();

        // Create new entry in dl_request_status_logs
        $newRequestStatusEntry = new DlRequestStatusLog;

        $newRequestStatusEntry->dl_request_id = $post['requestId'];
        $newRequestStatusEntry->bank_account_id = $post['requestBankAccountId'];
        $newRequestStatusEntry->request_status = $bankDataArray['Status'];
        $newRequestStatusEntry->created_by = Auth::user()->id;
        $newRequestStatusEntry->updated_by = Auth::user()->id;

        $newRequestStatusEntry->save();
      });
    } catch (Exception $e) {
      return ['success' => false, 'msg' => 'Report ' . $post['requestCode'] . ': Bank Verification was not successfully recorded. Error: ' . $e];
    }

    return ['success' => true, 'msg' => 'Report ' . $post['requestCode'] . ': Bank Verification successfully recorded.'];
  }

  public function convertBooleanToInt($value) {
    if($value === 'true') {
      return 1;
    } else if ($value === 'false') {
      return 0;
    } else {
      return -1;
    }
  }

  public function convertDLTimeStampToSQL($timeStr) {
    $dlTimeStamp = [];
    preg_match("/\d+/", $timeStr, $dlTimeStamp);

    return new \DateTime(date('Y-m-d H:i:s', (int)substr($dlTimeStamp[0], 0, 10)));
  }

}
