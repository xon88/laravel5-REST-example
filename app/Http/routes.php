<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['prefix' => 'api/v1'], function()
{
	Route::resource('customer', 'CustomerController'        
    	,['only' => ['index','store','show','update']]
    );

    Route::group(['prefix' => 'customer/{customer_id}'], function () {
		Route::resource('deposit', 'DepositController'        
	    	,['only' => ['index','store']]
	    );

		Route::resource('withdrawal', 'WithdrawalController'        
	    	,['only' => ['index','store']]
	    );
    });

	Route::post('report/transactions', 'ReportController@transactions');

});

// Route::get('/', 'WelcomeController@index');

// Route::get('home', 'HomeController@index');

// Route::controllers([
// 	'auth' => 'Auth\AuthController',
// 	'password' => 'Auth\PasswordController',
// ]);
