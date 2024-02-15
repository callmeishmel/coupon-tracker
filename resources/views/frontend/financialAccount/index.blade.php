@extends('layouts.frontend')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                  <h2>Upload Financial Account to Decision Logic</h2>
                  {!! Breadcrumbs::render('customer-financial-account') !!}
                </div>

                <div class="panel-body">
                    <member-submit-bank-info
                      user-id="{{ Auth::user()->id }}"
                      user-email="{{ Auth::user()->email }}"
                      csrf-token="{{ csrf_token() }}"
                      dl-create-request-route="{{ route('dl-create-request.financial-account.post') }}"
                      dl-get-user-requests-route="{{ route('user-dl-requests.financial-account.get') }}"
                      dl-save-iframe-bank-data-route="{{ route('dl-save-iframe-bank-data.financial-account.post') }}"
                      dl-save-request-transactions-route="{{ route('dl-save-request-transactions.financial-account.get') }}"
                      dl-view-request-transactions-route="{{ route('view-transactions.financial-account.view') }}"
                    ></member-submit-bank-info>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
