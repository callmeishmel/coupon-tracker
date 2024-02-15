@extends(Entrust::hasRole('admin,data_manager') ? 'layouts.app' : 'layouts.frontend')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default text-center">
                <div class="panel-heading">
                  <h1>
                    <strong>Something Went Wrong...</strong>
                  </h1>
                </div>

                <div class="panel-body">
                  <span style="font-size: 90px; color: #0798af; font-weight: bold;">{{ $error }}</span>
                  <br/>
                  <span style="font-size: 140px; color: #ddd;">¯\_(ツ)_/¯</span>
                  <br/>
                  <span style="font-size: 50px; color: #f5984f; font-weight: bold;">{{ $msg }}</span>
                  <br/>
                  <br/>
                  <a href="{{ route('route.to.role.home') }}">
                    <button class="btn btn-primary" style="font-size: 22px;">
                      Return Home
                    </button>
                  </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
