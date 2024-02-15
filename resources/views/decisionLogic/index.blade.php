@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">

                <div class="panel-heading">
                  <h2>Decision Logic Reports</h2>
                  {!! Breadcrumbs::render('decision-logic-reports') !!}
                </div>

                <div class="panel-body">

                  <div class="row">
                    <div class="col-md-12">
                      {!! Form::open(['route' => 'reports.fetch-save-reports.post', 'class' => 'form-inline']) !!}
                      <div class="form-group" style="width: 100%;">
                        {!! Form::label('reportsToFetch', 'Report Fetch Limit') !!}
                        {!! Form::select(
                          'reportsToFetch',
                          ['1'=>1, '5'=>5, '10'=>10, '20'=>20],
                          '10',
                          ['class' => 'form-control', 'required']
                          ) !!}
                        {!! Form::submit('Get Available Reports', ['class' => 'btn btn-primary']) !!}
                        <a href="{{ route('vendor-verification.decision-logic.view') }}" class="btn btn-success pull-right">Go to Vendor Verification</a>
                      </div>
                      <br/>
                      <span class="small-print">*Fetch limit due to script timing out if too many transactions are saved</span>
                      {!! Form::close() !!}
                    </div>
                  </div>
                  <hr/>
                  <div class="row">
                    <div class="col-md-12">

                      @if(count($savedReports) < 1)
                        <div class="box-content-msg">No saved reports available</div>
                      @else
                        <span class="small-print">*Decision logic data</span>
                        <h5 class="pull-right">Latest <strong>{{ count($savedReports) }}</strong> of <strong>{{ $savedReportsCount }}</strong> Reports Found</h5>
                        <table class="table table-hover">
                          <thead>
                            <tr>
                              <th>Request Code*</th>
                              <th>Date Created*</th>
                              <th>Customer ID*</th>
                              <th>Customer Name*</th>
                              <th>Created At</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($savedReports as $report)
                            <tr>
                              <td>
                                <a href="{{ route('transactions.decision-logic.view', [$report->id]) }}">
                                  {{ $report->dl_request_code }}
                                </a>
                              </td>
                              <td>{{ $report->dl_date_created }}</td>
                              <td>{{ $report->dl_customer_id }}</td>
                              <td>{{ $report->dl_customer_name_entered }}</td>
                              <td>{{ $report->created_at }}</td>
                            </tr>
                            @endforeach
                          </tbody>
                        </table>
                      @endif

                    </div>
                  </div>


                </div>

            </div>
        </div>
    </div>
</div>

@endsection
