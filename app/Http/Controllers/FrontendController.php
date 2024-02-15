<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\DlRequest;
use App\Models\DlRequestTransaction;

class FrontendController extends Controller
{

  public function index() {

    $customerTagData = DlRequestTransaction::selectRaw('
      (SELECT tag_name FROM tags WHERE id = verified_vendors_has_tags.tag_id) AS tag_name,
      COUNT(*) tag_frequency_count
    ')
    ->leftJoin('verified_vendors', 'verified_vendors.id', '=', 'dl_request_transactions.verified_vendor_id')
    ->leftJoin('verified_vendors_has_tags', 'verified_vendors_has_tags.vendor_assigned_name_id', '=', 'verified_vendors.vendor_assigned_name_id')
    ->leftJoin('dl_requests', 'dl_request_transactions.dl_request_id', '=', 'dl_requests.id')
    ->where('dl_requests.user_id', '=', Auth::user()->id)
    ->groupBy('tag_name')
    ->orderBy('tag_frequency_count', 'DESC')
    ->get();

    $customerTagData = $customerTagData->reject(function ($value) {
      return is_null($value->tag_name);
    });

    $grouponCategories = $customerTagData->pluck('tag_name');

    if($grouponCategories->count() === 0) {
      $grouponCategories = ['food-and-drink', 'things-to-do', 'retail', 'personal-services'];
    }

    return view('frontend.index', compact('customerTagData', 'grouponCategories'));
  }

}
