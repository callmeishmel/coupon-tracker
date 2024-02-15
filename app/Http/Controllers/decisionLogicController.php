<?php

namespace App\Http\Controllers;

use Auth;
use SoapClient;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use App\Models\DlRequest;
use App\Models\DlRequestTransaction;

class decisionLogicController extends Controller
{

  // TODO The decision logic functions that fetch report and transaction data
  // are being shared by multiple front and back end sources now, they are
  // currently functional but will require refactoring in order to make it more
  // stable for future use

  public function index() {

    // TODO: The page will only display the last 10 reports, have to find a
    // better solution to displaying the thousands of reports we have
    $savedReports = DlRequest::orderBy('dl_date_created', 'DESC')->limit(10)->get();
    $savedReportsCount = DlRequest::count();

    return view('decisionLogic.index', compact('savedReports', 'savedReportsCount'));
  }

  public function reportTransactionsPage($id) {

    $requestCode = DlRequest::select(['id','dl_request_code'])->where('id', '=', $id)->first();
    $reportTransactions = DlRequestTransaction::where('dl_request_id', '=', $id)->get();

    $reportTagData = DlRequestTransaction::selectRaw('
      (SELECT assigned_name FROM verified_vendor_assigned_names WHERE verified_vendor_assigned_names.id = verified_vendors.vendor_assigned_name_id) AS vendor_name,
      (SELECT tag_name FROM tags WHERE id = verified_vendors_has_tags.tag_id) AS tag_name,
      COUNT(*) tag_frequency_count
    ')
    ->leftJoin('verified_vendors', 'verified_vendors.id', '=', 'dl_request_transactions.verified_vendor_id')
    ->leftJoin('verified_vendors_has_tags', 'verified_vendors_has_tags.vendor_assigned_name_id', '=', 'verified_vendors.vendor_assigned_name_id')
    ->where('dl_request_transactions.dl_request_id', '=', $id)
    ->groupBy('tag_name')
    ->orderBy('tag_frequency_count', 'DESC')
    ->get();

    // Remove NULL tag_name tags, meaning vendor is not verified
    $reportTagData = $reportTagData->reject(function ($value) {
      return is_null($value->tag_name);
    });

    // Get the frequency total for all Tags
    $totalTagsFound = $reportTagData->sum('tag_frequency_count');

    foreach($reportTagData as $tag) {
      $tag->tag_frequency_percent = round(($tag->tag_frequency_count / $totalTagsFound) * 100, 1);
    }

    return view('decisionLogic.reportTransactions', compact('requestCode', 'reportTransactions', 'reportTagData'));

  }

  public function vendorVerificationPage() {

    $transactions = DlRequestTransaction::selectRaw('
        COUNT(*) count,
        id,
        dl_request_id,
        amount_spent,
        purchase_date,
        transaction_codes,
        description,
        filtered_vendor_name,
        dl_category,
        dl_category_vendor,
        balance_at_time_of_purchase,
        is_ignored,
        vendor_verified,
        verified_vendor_id,
        created_by,
        created_at
      ')
      ->where([
        ['is_ignored', '=', 0],
        ['vendor_verified', '=', 0],
      ])
      ->groupBy('dl_category_vendor')
      ->orderBy('count', 'DESC')
      ->limit(300)
      ->get();

    $unverifiedTransactions = $this->_getFormattedRequestData($transactions->sortByDesc('count'));

    return view('decisionLogic.vendorVerification', compact('unverifiedTransactions'));
  }

  public function runFetchAndSaveReports(Request $request) {
    $this->_fetchAndSaveReportTransactions($request->reportsToFetch);

    return back()->withInput();
  }

  public function _fetchAndSaveReportTransactions($reportsToSave) {

    if($reportsToSave > 20 || !isset($reportsToSave)) {
      $reportsToSave = 20;
    }

    $savedReportsCount = 0;
    $lastReportSaved = DlRequest::orderBy('dl_date_created', 'DESC')->first();

    // TODO _fetchAllUndocumentedReports() used to search from the created_at value
    // of the lats saved report, since front end customers can created reports of
    // their own it cuts off any untracked reports in Decision Logic
    // NOTE: Will need to redo the process to not have to pull all reports each time

    //$availableReports = $this->_fetchAllUndocumentedReports($lastReportSaved['dl_date_created']);
    $availableReports = $this->_fetchAllUndocumentedReports();



    foreach($availableReports['table_data'] as $report) {

      // TODO: Add 'No reports found error message'
      if($savedReportsCount >= $reportsToSave || !isset($report->RequestCode)) {
        break;
      }

      // Get rid of Decision Logic's dummy reports
      if(in_array($report->CustomerIdentifier, ['DEMO REG', 'DEMO FREQ', 'DEMO NSF'])) {
        continue;
      }

      $reportExistsInDB = DlRequest::where(['dl_request_code' => $report->RequestCode])->first();

      if(!is_null($reportExistsInDB)) {
        continue;
      }

      $dlRequest = new DlRequest;

      $dlRequest->dl_request_code = $report->RequestCode;
      $dlRequest->dl_date_created = $report->DateCreated;
      $dlRequest->dl_customer_id = $report->CustomerIdentifier;
      $dlRequest->dl_customer_name_entered = $report->HolderNameInput;
      $dlRequest->created_by = Auth::user()->id;
      $dlRequest->updated_by = Auth::user()->id;

      $dlRequest->save();

      $this->_transactionFetchRecorder($report->RequestCode);

      $savedReportsCount++;

    }

    return;

  }

  public function _transactionFetchRecorder($requestCode) {

    $dlRequestCheck = DlRequest::select('id')->where('dl_request_code', '=', $requestCode)->first();

    if(is_null($dlRequestCheck)) {
      return ['success' => false, 'msg' => 'Request Code does not exist.'];
    }

    $transactions = $this->_fetchReportTransactions($requestCode);

    $transactionsToQueryData = [];

    foreach($transactions as $transaction) {
      $transactionData = [];
      $skippedCategories = ['Taxes', 'Transfers', 'ATM/Cash Withdrawals', 'Healthcare/Medical', 'Paychecks/Salary', 'Other Bills', 'Deposits', 'Rent', 'Utilities', 'Service Charges/Fees'];

      $categoryStrArray = explode(' | ', $transaction->Category);

      // Skip transaction if it's a type of deposit or less than $1 purchase
      if($transaction->Amount > -1) {
        continue;
      }

      if(count($categoryStrArray) === 2) {
        // Don't save transaction if it's an unwanted category
        if(in_array($categoryStrArray[1], $skippedCategories)) {
          continue;
        }

        $transactionData['dl_category'] = $categoryStrArray[1];
        $transactionData['dl_category_vendor'] = str_replace('\'', '', $categoryStrArray[0]);
        $transactionData['dl_category_vendor'] = str_replace([' ', '.'], '-', $transactionData['dl_category_vendor']);

        $isAlreadyVerified = DlRequestTransaction::select(['vendor_verified', 'verified_vendor_id', 'is_ignored'])
          ->where([
            'dl_category' => $transactionData['dl_category'],
            'dl_category_vendor' => $transactionData['dl_category_vendor']
          ])
          ->first();

        $transactionData['is_ignored'] = 0;

        if($isAlreadyVerified) {
          if($isAlreadyVerified->vendor_verified === 1) {
            $transactionData['vendor_verified'] = 1;
            $transactionData['verified_vendor_id'] = $isAlreadyVerified->verified_vendor_id;
          }
          if($isAlreadyVerified->is_ignored === 1) {
            $transactionData['is_ignored'] = 1;
          }
        }

      } else {
        $transactionData['dl_category'] = $transaction->Category;
        $transactionData['dl_category_vendor'] = null;
      }

      $transactionData['dl_request_id'] = $dlRequestCheck->id;
      $transactionData['amount_spent'] = $transaction->Amount;
      $transactionData['purchase_date'] = $transaction->TransactionDate;
      $transactionData['transaction_codes'] = $transaction->TypeCodes;
      $transactionData['description'] = $transaction->Description;
      $transactionData['filtered_vendor_name'] = $this->_categoryVendorNameFromTransactionDescription($transaction->Description);
      $transactionData['balance_at_time_of_purchase'] = $transaction->RunningBalance;
      $transactionData['created_by'] = Auth::user()->id;
      $transactionData['updated_by'] = Auth::user()->id;
      $transactionData['created_at'] = date('Y-m-d H:i:s');
      $transactionData['updated_at'] = date('Y-m-d H:i:s');

      array_push($transactionsToQueryData, $transactionData);

    }

    if(DlRequestTransaction::insert($transactionsToQueryData)) {
      return ['success' => true, 'msg' => 'Fetched report transactions successfully recorded.'];
    } else {
      return ['success' => false, 'msg' => 'Recording fetched report transactions failed.'];
    }

  }

  public function _fetchAllUndocumentedReports($fromDate = null) {
    $client = new SoapClient(config('soap-apis.decisionLogic.soapClientUrl'));
    $callData = [];

    $fromDate = is_null($fromDate) ? '2015-01-01' : date_format(new \DateTime($fromDate), 'Y-m-d');

    $paramArray = [
      'serviceKey' => config('soap-apis.decisionLogic.serviceKey'),
      'siteUserGuid' => config('soap-apis.decisionLogic.siteUserGuid'),
      'searchTerm' => '',
      'fromDate' => $fromDate,
      'toDate' => date('Y-m-d'),
      'timeZoneOffset' => 0,
      'status' => 3,
      'processStatus' => 0
    ];

    $response = $client->SearchReportSummary($paramArray);
    $reportsFound = (array)$response->SearchReportSummaryResult->SearchResults->SearchResult;

    if(count($reportsFound) > 0)  {
      $callData['table_data'] = array_reverse($reportsFound);
      $callData['table_keys'] = array_keys($reportsFound);
    }

    return $callData;
  }

  public function _fetchReportTransactions($requestCode) {

    $client = new SoapClient(config('soap-apis.decisionLogic.soapClientUrl'));
    $callData = [];

    $paramArray = [
      'serviceKey' => config('soap-apis.decisionLogic.serviceKey'),
      'siteUserGuid' => config('soap-apis.decisionLogic.siteUserGuid'),
      'requestCode' => $requestCode
    ];

    $response = $client->GetReportDetailFromRequestCode6($paramArray);

    $transactions = $response
    ->GetReportDetailFromRequestCode6Result
    ->TransactionSummaries
    ->TransactionSummary4;

    return $transactions;
  }

  public function _getFormattedRequestData($transactionData) {

    $formattedData = [];

    foreach($transactionData as $transaction) {

      if($transaction->transaction_codes !== '') {
        continue;
      }

      if($transaction->dl_category_vendor === "") {
        unset($transaction);
        continue;
      }

      $transactionFields = [];
      $transactionCategory = explode('|', $transaction->category);

      $categoryNameTakenFrom = 'category';
      $categoryName = $transaction->dl_category;
      $categoryVendorName = $transaction->dl_category_vendor;

      if($categoryName === 'Uncategorized' || $categoryVendorName === '' || $categoryVendorName === '.com' || $categoryVendorName === 'ck#') {
        $categoryVendorName = $this->_categoryVendorNameFromTransactionDescription($transaction->{'Description'});
        $categoryNameTakenFrom = 'description';
      }


      $transactionFields['transaction_table_id'] = $transaction->id;
      $transactionFields['category_name'] = $categoryName;
      $transactionFields['transaction_count'] = $transaction->count;
      $transactionFields['category_vendor_name'] = $categoryVendorName;
      $transactionFields['transaction_description'] = $transaction->description;

      array_push($formattedData, $transactionFields);

    }

    $formattedData = $this->_unique_multidim_array($formattedData, 'category_vendor_name');

    // Reset numerical array keys from sorting
    $formattedData = array_values($formattedData);

    return $formattedData;

  }

  public function _categoryVendorNameFromTransactionDescription($string) {
    $filteredTerms = ['P.O.S. PURCHASE', 'REF #', 'ID:', 'PPD'];
    $string = str_replace($filteredTerms, '', $string);
    $string = preg_replace('/\d{2}\/\d{2}\/\d{4}/', '', $string);
    $string = str_replace('/', ' ',  $string);

    // Break down into words
    $wordsInDesc = explode(' ', $string);

    // Check each word in string
    foreach($wordsInDesc as $k => $word) {

      // Remove order numbers (#123) and (xxxx123ABC)
      $regexArr = ['/[#][0-9]*/', '/x{2,}\S*/', '/[*]{2,}/', '/[-]{2,}/'];

      foreach($regexArr as $pattern) {

        preg_match($pattern, $word, $matches);

        if(count($matches) > 0 || $word === '') {
          if($pattern === '/[-]{2,}/') {
            unset($wordsInDesc[$k - 1]);
          }
          unset($wordsInDesc[$k]);
        }

      }

      if(strpos($word, '*') !== false) {
        if(preg_match('/[.]\S{2,3}/', $word) !== false) {
          $wordsInDesc[$k] = str_replace('*', ' ', $word);
        } else {
          $wordsInDesc[$k] = str_replace('*', '', $word);
        }
      }
    }

    $description = implode(' ', $wordsInDesc);

    return strtolower($description);
  }

  // Temporarily placing this utility function here
  public function _unique_multidim_array($array, $key) {
    $temp_array = array();
    $i = 0;
    $key_array = array();

    foreach($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
        }
        $i++;
    }
    return $temp_array;
  }

}
