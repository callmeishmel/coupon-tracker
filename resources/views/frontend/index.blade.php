@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                  <h2 style="text-align:center;">On your mark, get set... <strong>SAVE!</strong></h2>
                </div>

                <div class="panel-body">

                    @foreach($grouponCategories as $category)
                      <groupon-viewer
                      csrf-token="{{ csrf_token() }}"
                      get-groupon-data-route="{{ route('coupons.groupon-data.view') }}"
                      groupon-search-category="{{ $category }}"
                      ></groupon-viewer>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
