<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use App\Models\DlRequest;
use App\Models\DlRequestTransaction;
use App\Models\VerifiedVendor;
use App\Models\VerifiedVendorAssignedName;

class decisionLogicAjaxCallsController extends Controller
{

  public function verifyVendor(Request $request) {

    $post = $request->all();
    $msg = '';
    $vendorNameId = null;

    // Get extra data from the transaction
    try {
      $fromTransaction = DlRequestTransaction::where(['id' => $post['transactionId']])->first();
    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    // Check if the assigned vendor name is taken
    $verifiedVendorExists = VerifiedVendorAssignedName::selectRaw('
      verified_vendor_assigned_names.*,
      users.email AS user_email
    ')
    ->leftJoin('users', 'verified_vendor_assigned_names.created_by', '=', 'users.id')
    ->where(['assigned_name' => $post['assignedVendorName']])
    ->first();

    if($verifiedVendorExists && $post['verificationType'] === 'new') {
      return [
        'success' => false,
        'msg' => 'Vendor name already exists. Select a different name or assign to an existing vendor.',
        'data' => $verifiedVendorExists
      ];
    }

    // Create a new vendor name entry if this is a new vendor
    if(!$verifiedVendorExists) {

      try {

        $newVerifiedVendorName = new VerifiedVendorAssignedName;

        $newVerifiedVendorName->assigned_name = $post['assignedVendorName'];
        $newVerifiedVendorName->created_by = Auth::user()->id;
        $newVerifiedVendorName->updated_by = Auth::user()->id;

        $newVerifiedVendorName->save();

      }  catch(Illuminate\Database\QueryException $e) {
        $msg = 'QueryException Error: ' . $e;
      } catch(PDOException $e) {
        $msg = 'PDOException Error: ' . $e;
      }

      $vendorNameId = $newVerifiedVendorName->id;

    } else {

      $vendorNameId = $verifiedVendorExists->id;

    }

    // Create new verified vendor entry
    try {

      $newVerifiedVendor = new VerifiedVendor;

      $newVerifiedVendor->dl_request_transaction_id = $fromTransaction->id;
      $newVerifiedVendor->vendor_assigned_name_id = $vendorNameId;
      $newVerifiedVendor->name_from_transaction = $fromTransaction['dl_category_vendor'];
      $newVerifiedVendor->category_name_from_transaction = $fromTransaction['dl_category'];
      $newVerifiedVendor->created_by = Auth::user()->id;
      $newVerifiedVendor->updated_by = Auth::user()->id;

      $newVerifiedVendor->save();
    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    try {
      // Get the dl_vendor_name from the transaction by it's id
      $dlCategoryVendorName = DlRequestTransaction::select(['dl_category_vendor', 'dl_category'])->where('id', '=', $post['transactionId'])->first();
      // Mark all non-verified non-ignored transactions vendor_verified
      // if dl_vendor_name string matches
      $updateMatchingTransactions = DlRequestTransaction::where([
        'dl_category_vendor' => $dlCategoryVendorName->dl_category_vendor,
        'dl_category' => $dlCategoryVendorName->dl_category,
        'vendor_verified' => '0',
        'is_ignored' => '0',
      ])
        ->update(['vendor_verified' => 1, 'verified_vendor_id' => $newVerifiedVendor->id]);
    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    // TODO Check if transactions updated correctly if not undo new vendor entry

    return ['success' => true, 'msg' => 'Vendor successfully verified.'];

  }

  public function getVerifiedVendors(Request $request) {

    $post = $request->all();
    $mag = '';

    try{
      $verifiedVendors = VerifiedVendor::selectRaw('
        verified_vendor_assigned_names.assigned_name,
        name_from_transaction,
        category_name_from_transaction,
        users.email AS user_email,
        verified_vendors.created_at
      ')
      ->leftJoin('users', 'verified_vendors.created_by', '=', 'users.id')
      ->leftJoin(
        'verified_vendor_assigned_names',
        'verified_vendors.vendor_assigned_name_id',
        '=',
        'verified_vendor_assigned_names.id'
      )
      ->limit($post['count'])
      ->latest()
      ->get();
    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    if($verifiedVendors) {
      return [
        'success' => true,
        'msg' => 'Verified vendor list successfully retrieved.',
        'data' => $verifiedVendors
      ];
    } else {
      return ['success' => false, 'msg' => 'Failed to retrieve verified vendor list. Error: ' . $msg];
    }

  }

  public function ignoreTransactions(Request $request) {
    $post = $request->all();
    $msg = '';

    try {
      // Get the dl_vendor_name from the transaction by it's id
      $dlCategoryVendorName = DlRequestTransaction::select(['dl_category_vendor', 'dl_category'])->where('id', '=', $post['transactionId'])->first();
      // Mark all transactions ignored if dl_vendor_name/category strings match
      $updateMatchingTransactions = DlRequestTransaction::where([
        'dl_category_vendor' => $dlCategoryVendorName->dl_category_vendor,
        'dl_category' => $dlCategoryVendorName->dl_category
      ])
        ->update(['is_ignored' => 1]);
    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    if($updateMatchingTransactions) {
      return ['success' => true, 'msg' => 'Successfully updated transactions as ignored'];
    } else {
      return ['success' => false, 'msg' => 'Failed to update transactions. Error: ' . $msg];
    }

  }

}
