@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                  <h3>
                    Vendor Verification
                  </h3>
                  {!! Breadcrumbs::render('decision-logic-vendor-verification') !!}
                </div>

                <div class="panel-body">

                  <vendor-verification
                    csrf-token="{{ csrf_token() }}"
                    vendor-tagging-route-name="{{ route('index.vendor-tagging.view') }}"
                    :unverified-transactions="{{ json_encode($unverifiedTransactions) }}"
                    verification-route-name="{{ route('verify-vendor.decision-logic.post') }}"
                    get-verified-vendors-route-name="{{ route('verified-vendors.decision-logic.get') }}"
                    ignore-transactions-route-name="{{ route('ignore-transactions.decision-logic.post') }}"
                  >
                  </vendor-verification>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
