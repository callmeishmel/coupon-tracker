<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use App\Models\Tag;
use App\Models\VerifiedVendor;
use App\Models\VerifiedVendorHasTag;
use App\Models\VerifiedVendorAssignedName;

class VendorTaggingController extends Controller
{

  public function index() {

    $untaggedVendors = VerifiedVendor::selectRaw('
      verified_vendors.id AS vendor_id,
      verified_vendors.vendor_assigned_name_id,
      verified_vendor_assigned_names.assigned_name,
      verified_vendors.name_from_transaction,
      verified_vendors.category_name_from_transaction
    ')
    ->leftJoin(
      'verified_vendor_assigned_names',
      'verified_vendors.vendor_assigned_name_id',
      '=',
      'verified_vendor_assigned_names.id'
    )
    ->where('verified_vendor_assigned_names.is_tagged', '=', 0)
    ->groupBy('verified_vendor_assigned_names.assigned_name')
    ->get();

    return view('vendorTagging.index', compact(['untaggedVendors']));

  }

  public function searchForTagTerm($searchTerm) {

    $msg = '';

    try {
      $matchingTags = Tag::select(['id', 'tag_name'])
        ->where('tag_name', 'like', '%' . $searchTerm . '%')
        ->limit(10)
        ->get();
    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    if($matchingTags) {
      return ['success' => true, 'msg' => 'Tag term search successful.', 'data' => $matchingTags];
    } else {
      return ['success' => false, 'msg' => 'Tag term search failed. Error: ' . $msg];
    }

  }

  public function getVendorTags($vendorId) {

    $vendorTags = VerifiedVendorHasTag::select(['tags.id', 'tags.tag_name'])
      ->leftJoin('tags', 'verified_vendors_has_tags.tag_id', '=', 'tags.id')
      ->where('vendor_assigned_name_id', '=', $vendorId)
      ->get();

    if($vendorTags) {
      return ['success' => true, 'msg' => 'Tag term search successful.', 'data' => $vendorTags];
    } else {
      return ['success' => false, 'msg' => 'Tag term search failed. Error: ' . $msg];
    }

  }

  public function addNewTag(Request $request) {
    $post = $request->all();
    $msg = '';

    try{
      $tagExists = Tag::where('tag_name', '=', $post['tag_name'])->first();

    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    if(!$tagExists) {
      try{
        $newTag = new Tag;

        $newTag->tag_name = $post['tag_name'];
        $newTag->created_by = Auth::user()->id;
        $newTag->updated_by = Auth::user()->id;
        $newTag->active = 1;

        $newTag->save();

      } catch(Illuminate\Database\QueryException $e) {
        $msg = 'QueryException Error: ' . $e;
      } catch(PDOException $e) {
        $msg = 'PDOException Error: ' . $e;
      }

      if($newTag) {
        return ['success' => true, 'msg' => 'Successfully added new tag "' . $post['tag_name'] . '".'];
      } else {
        return ['success' => false, 'msg' => 'Failed to add new tag "' . $post['tag_name'] . '".'];
      }
    }

    return ['success' => true, 'msg' => 'Tag "' . $post['tag_name'] . '" already exists.'];

  }

  public function getLatestTags() {

    try {
      $latestTags = Tag::where('active', '=', 1)
        ->limit(20)
        ->orderBy('created_at', 'DESC')
        ->get();
    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    if($latestTags) {
      return [
        'success' => true,
        'msg' => 'Successfully retrieved ' . $latestTags->count() . 'tag(s).',
        'data' => $latestTags
      ];
    } else {
      return ['success' => false, 'msg' => 'Failed to retrieve recent tags.'];
    }

  }

  public function addTagToVendor(Request $request) {
    $post = $request->all();
    $msg = '';

    try {
      $vendorHasTagCheck = VerifiedVendorHasTag::where([
        ['tag_id', '=', $post['tag_id']],
        ['vendor_assigned_name_id', '=', $post['vendor_name_id']]
      ])
      ->first();

      if($vendorHasTagCheck) {
        return ['success' => true, 'msg' => 'Vendor already has selected tag.'];
      }
    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    try {
      $newVerifiedVendorTag = new VerifiedVendorHasTag;

      $newVerifiedVendorTag->tag_id = $post['tag_id'];
      $newVerifiedVendorTag->vendor_assigned_name_id = $post['vendor_name_id'];
      $newVerifiedVendorTag->created_by = Auth::user()->id;
      $newVerifiedVendorTag->updated_by = Auth::user()->id;

      $newVerifiedVendorTag->save();
    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    if($newVerifiedVendorTag) {
      return ['success' => true, 'msg' => 'Tag successfully added to vendor.'];
    } else {
      return ['success' => false, 'msg' => 'Failed to add tag to vendor. Error: ' . $msg];
    }
  }

  public function removeTagFromVendor(Request $request) {
    $post = $request->all();
    $msg = '';

    try {

      $vendorTag = VerifiedVendorHasTag::where([
        ['tag_id', '=', $post['tag_id']],
        ['vendor_assigned_name_id', '=', $post['vendor_name_id']]
      ])
      ->delete();

    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    if($vendorTag) {
      return ['success' => true, 'msg' => 'Tag successfully removed from vendor.'];
    } else {
      return ['success' => false, 'msg' => 'Failed to remove tag from vendor. Error: ' . $msg];
    }
  }

  public function markVendorTagged(Request $request) {

    $post = $request->all();
    $msg = '';

    try {
      $verifiedVendorCheck = VerifiedVendor::where('id', '=', $post['vendor_id'])->first();
    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    if($verifiedVendorCheck) {
      try {
        $updateAssignedVendorName = VerifiedVendorAssignedName::where('id', '=', $verifiedVendorCheck->vendor_assigned_name_id)
        ->update(['is_tagged' => 1]);
      } catch(Illuminate\Database\QueryException $e) {
        $msg = 'QueryException Error: ' . $e;
      } catch(PDOException $e) {
        $msg = 'PDOException Error: ' . $e;
      }
    } else {
      return ['success' => false, 'msg' => 'Did not find submitted vendor. Error: ' . $msg];
    }

    if($updateAssignedVendorName) {
      return ['success' => true, 'msg' => 'Vendor successfully marked as tagged.'];
    } else {
      return ['success' => false, 'msg' => 'Failed to mark vendor as tagged. Error: ' . $msg];
    }

  }

  public function getTaggedVendors() {

    $msg = '';

    try {
      $taggedVendors = VerifiedVendor::selectRaw('
      verified_vendor_assigned_names.assigned_name,
      category_name_from_transaction,
      users.email AS user_email,
      verified_vendors.updated_at
      ')
        ->where('is_tagged', '=', 1)
        ->leftJoin(
          'verified_vendor_assigned_names',
          'verified_vendors.vendor_assigned_name_id',
          '=',
          'verified_vendor_assigned_names.id'
        )
        ->leftJoin('users', 'verified_vendors.created_by', '=', 'users.id')
        ->limit(5)
        ->orderBy('updated_at', 'DESC')
        ->groupBy('verified_vendor_assigned_names.assigned_name')
        ->get();
    } catch(Illuminate\Database\QueryException $e) {
      $msg = 'QueryException Error: ' . $e;
    } catch(PDOException $e) {
      $msg = 'PDOException Error: ' . $e;
    }

    if($taggedVendors) {
      return [
        'success' => true,
        'msg' => 'Successfully retrieved tagged vendor results with ' . $taggedVendors->count() . ' result(s).',
        'data' => $taggedVendors
      ];
    } else {
      return ['success' => false, 'msg' => 'Failed to retrieve tagged vendor results. Error: ' . $msg];
    }
  }

}
