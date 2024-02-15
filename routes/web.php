<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();

// REDIRECTS USERS TO THE ROLE HOME PAGE AFTER LOGIN
Route::get('/home', 'RoleLoginController@routeLoggedInUserBasedOnRole')->name('route.to.role.home');

// PUBLIC ROUTES
Route::get('/', function () {
  return view('welcome');
})->name('public.welcome.get');

// FRONTEND MEMBER TOOLS ROUTES
Route::group(['middleware' => ['auth', 'role:customer_client'], 'prefix' => 'frontend'], function() {
  Route::get('/', 'FrontendController@index')->name('index.frontend.get');

  Route::group(['prefix' => 'financial-account'], function() {
    Route::get('/', 'FinancialAccountController@index')->name('index.financial-account.get');
    Route::get('/get-user-dl-requests/{userId?}', 'FinancialAccountController@getUserDLRequests')->name('user-dl-requests.financial-account.get');
    Route::post('/dl-create-request', 'FinancialAccountController@createDecisionLogicRequest')->name('dl-create-request.financial-account.post');
    Route::post('/dl-save-iframe-bank-data', 'FinancialAccountController@dlIframeBankData')->name('dl-save-iframe-bank-data.financial-account.post');
    Route::get('/dl-save-request-transactions/{requestCode?}', 'decisionLogicController@_transactionFetchRecorder')->name('dl-save-request-transactions.financial-account.get');
    Route::get('/report-transactions/{id?}', 'decisionLogicController@reportTransactionsPage')->name('view-transactions.financial-account.view');
  });

});

// BACKEND ADMIN/AGENT TOOLS ROUTES
Route::group(['middleware' => ['auth', 'role:admin|data_manager'], 'prefix' => 'backend'], function() {
  Route::get('/', 'BackendController@index')->name('index.backend.get');
});

Route::group(['middleware' => ['auth', 'role:admin|data_manager|customer_client'], 'prefix' => 'coupons'], function() {
  Route::get('/', 'CouponsPageController@index')->name('coupons');
  Route::get('/groupon-data', 'CouponsPageController@fetchGrouponData')->name('coupons.groupon-data.view');
  Route::get('/groupon-map', 'CouponsPageController@grouponMap')->name('coupons.groupon-map.view');
});

Route::group(['middleware' => ['auth', 'role:admin|data_manager'], 'prefix' => 'decision-logic'], function() {
  Route::get('/reports', 'decisionLogicController@index')->name('reports.decision-logic.view');
  Route::post('/reports', 'decisionLogicController@runFetchAndSaveReports')->name('reports.fetch-save-reports.post');
  Route::get('/report-transactions/{id}', 'decisionLogicController@reportTransactionsPage')->name('transactions.decision-logic.view');
  Route::get('/vendor-verification', 'decisionLogicController@vendorVerificationPage')->name('vendor-verification.decision-logic.view');
  Route::post('/ajax/get-verified-vendors', 'decisionLogicAjaxCallsController@getVerifiedVendors')->name('verified-vendors.decision-logic.get');
  Route::post('/ajax/verify-vendor', 'decisionLogicAjaxCallsController@verifyVendor')->name('verify-vendor.decision-logic.post');
  Route::post('/ajax/set-transactions-ignored', 'decisionLogicAjaxCallsController@ignoreTransactions')->name('ignore-transactions.decision-logic.post');
});

Route::group(['middleware' => ['auth', 'role:admin|data_manager'], 'prefix' => 'vendor-tagging'], function() {
  Route::get('/', 'VendorTaggingController@index')->name('index.vendor-tagging.view');
  Route::get('/vendor-tags/{vendorId?}', 'VendorTaggingController@getVendorTags')->name('vendor-tags.vendor-tagging.get');
  Route::get('/get-tagged-vendors', 'VendorTaggingController@getTaggedVendors')->name('get-tagged-vendors.vendor-tagging.get');
  Route::get('/tag-search/{searchTerm?}', 'VendorTaggingController@searchForTagTerm')->name('tag-search.vendor-tagging.get');
  Route::get('/get-latest-tags', 'VendorTaggingController@getLatestTags')->name('get-latest-tags.vendor-tagging.get');
  Route::post('/add-new-tag', 'VendorTaggingController@addNewTag')->name('add-new-tag.vendor-tagging.post');
  Route::post('/add-tag-to-vendor', 'VendorTaggingController@addTagToVendor')->name('add-tag-to-vendor.vendor-tagging.post');
  Route::post('/remove-tag-from-vendor', 'VendorTaggingController@removeTagFromVendor')->name('remove-tag-from-vendor.vendor-tagging.post');
  Route::post('/mark-vendor-tagged', 'VendorTaggingController@markVendorTagged')->name('mark-vendor-tagged.vendor-tagging.post');
});

// ERROR HANDLING ROUTES
Route::group(['prefix' => 'err'], function() {
  Route::get('/405', 'ErrorHandlerController@return405')->name('405');
  Route::get('/404', 'ErrorHandlerController@return404')->name('404');
  Route::get('/403', 'ErrorHandlerController@return403')->name('403');
});

// UNFINISHED/TEST ROUTES
Route::get('/saved-coupons', 'SavedCouponsPageController@index')->name('saved-coupons');
Route::get('/profile', 'ProfilePageController@index')->name('profile');
