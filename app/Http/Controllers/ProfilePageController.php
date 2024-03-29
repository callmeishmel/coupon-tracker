<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfilePageController extends Controller
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
     * Show the application profile page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('profile');
    }
}
