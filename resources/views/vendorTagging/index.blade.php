@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                  <h2>Vendor Tagging</h2>
                  {!! Breadcrumbs::render('vendor-tagging') !!}
                </div>

                <div class="panel-body">

                  <vendor-tagging
                    csrf-token="{{ csrf_token() }}"
                    :untagged-vendors="{{ json_encode($untaggedVendors) }}"
                    tag-search-route-name="{{ route('tag-search.vendor-tagging.get') }}"
                    get-vendor-tags-route-name="{{ route('vendor-tags.vendor-tagging.get') }}"
                    get-latest-tags-route-name="{{ route('get-latest-tags.vendor-tagging.get') }}"
                    get-tagged-vendors-route-name="{{ route('get-tagged-vendors.vendor-tagging.get') }}"
                    add-new-tag-route-name="{{ route('add-new-tag.vendor-tagging.post') }}"
                    add-tag-to-vendor-route-name="{{ route('add-tag-to-vendor.vendor-tagging.post') }}"
                    remove-tag-from-vendor-route-name="{{ route('remove-tag-from-vendor.vendor-tagging.post') }}"
                    mark-vendor-tagged-route-name="{{ route('mark-vendor-tagged.vendor-tagging.post') }}"
                  >
                  </vendor-tagging>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
