<?php

namespace App\Http\Controllers;

class ErrorHandlerController extends Controller
{

  public function return403() {
    $error= 'Error 403';
    $msg = 'Permission required to view content';

    return view('errorHandling.error', compact('msg', 'error'));
  }

  public function return404() {
    $error= 'Error 404';
    $msg = 'Page does not exist';

    return view('errorHandling.error', compact('msg', 'error'));
  }

  public function return405() {
    $error= 'Error 405';
    $msg = 'Method not allowed';

    return view('errorHandling.error', compact('msg', 'error'));
  }

}
