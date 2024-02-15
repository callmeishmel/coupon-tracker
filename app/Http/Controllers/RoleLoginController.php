<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class RoleLoginController extends Controller
{

    public function routeLoggedInUserBasedOnRole() {

      if(Auth::user() === null) {
        return redirect()->route('public.welcome.get');
      }

      if(Auth::user()->hasRole(['admin', 'backend-agent'])) {
        return redirect()->route('index.backend.get');
      } else if(Auth::user()->hasRole('customer_client')) {
        return redirect()->route('index.frontend.get');
      }

    }

}
