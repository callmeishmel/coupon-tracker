<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class CouponsPageController extends Controller
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
     * Show the application coupons page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('coupons');
    }

    /**
     * Fetches Groupons based on longitude/latitude/radius
     *
     * URL must include the following GET parameters
     * - lat
     * - lng
     * - rad
     * - limit
     *
     * @return [GuzzleHttp/Response] [Geographically available groupon list]
     */
    public function fetchGrouponData() {

      $lat = Input::get('lat');
      $lng = Input::get('lng');
      // $rad (radius) is not working from Groupon's API, temporarly excluding it
      $rad = Input::get('rad');
      $limit = Input::get('limit');
      $filters = Input::get('filters');

      $urlString = '?tsToken='. env('GROUPON_TOKEN');

      if(!empty($lat) && !empty($lng)) {
        $urlString .= '&lat=' . $lat . '&lng=' . $lng;
      }

      if(!empty($filters)) {
        $urlString .= '&filters=category:'.$filters['category'];
      }

      $urlString .= '&limit=30';

      try {
        $client = new Client(['base_url' => 'https://partner-api.groupon.com/deals.json']);

        $response = $client->get($urlString);

        return $response->getBody();
      } catch (\Exception $e) {
        return 'Error: ' . $e;
      }

    }

    public function grouponMap() {
      return view('grouponMap.index');
    }
}
