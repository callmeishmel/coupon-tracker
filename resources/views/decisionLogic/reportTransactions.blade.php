@extends(Entrust::hasRole(['admin','data_manager']) ? 'layouts.app' : 'layouts.frontend')

@section('content')
<div class="container">

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                  <h2>Transactions for Report</h2>
                  @role(['admin','data_manager'])
                    {!! Breadcrumbs::render('decision-logic-report', $requestCode) !!}
                  @endrole

                  @role('customer_client')
                    {!! Breadcrumbs::render('customer-report-details', $requestCode) !!}
                  @endrole
                </div>

                <div class="panel-body">

                  <h3>Tag Frequency</h3>
                  <hr>

                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th>Tag</th>
                          <th>Frequency</th>
                          <th>Percent</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($reportTagData as $data)
                          <tr>
                            <td>{{ $data->tag_name }}</td>
                            <td>{{ $data->tag_frequency_count }}</td>
                            <td>{{ $data->tag_frequency_percent }}%</td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>

                    <h3>Transactions</h3>
                    <hr>

                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th>Description</th>
                          <th>DL Category</th>
                          <th>DL Vendor Name</th>
                          <th>Purchase Date</th>
                          <th>Amount</th>
                          <th>Balance at Purchase</th>
                          <th>Codes</th>
                          <th>Verified</th>
                          <th>Created By</th>
                          <th>Created At</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($reportTransactions as $transaction)
                        <tr>
                          <td>{{ $transaction['description'] }}</td>
                          <td>{{ $transaction['dl_category'] }}</td>
                          <td>{{ $transaction['dl_category_vendor'] }}</td>
                          <td>{{ $transaction['purchase_date'] }}</td>
                          <td>{{ $transaction['amount_spent'] }}</td>
                          <td>{{ $transaction['balance_at_time_of_purchase'] }}</td>
                          <td>{{ $transaction['transaction_codes'] }}</td>
                          <td>{{ $transaction['vendor_verified'] }}</td>
                          <td>{{ $transaction['created_by'] }}</td>
                          <td>{{ $transaction['created_at'] }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
