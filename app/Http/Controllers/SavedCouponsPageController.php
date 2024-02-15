<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SavedCouponsPageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application saved coupons page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('savedCoupons');
    }
}
