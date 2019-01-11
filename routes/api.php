<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth',
], function () {
    Route::post('login', 'AuthController@login');
    Route::get('user', 'AuthController@user');
    Route::middleware('jwt.refresh')->get('refresh', 'AuthController@refresh');
    Route::get('logout', 'AuthController@logout');
});

Route::group([
    'middleware' => 'api',
], function () {
    Route::get('/stores', 'StoreController@index');
    Route::get('/stores/{store}', 'StoreController@show');
    Route::post('/stores', 'StoreController@store');
    Route::put('/stores', 'StoreController@update');
    Route::delete('/stores/{store}', 'StoreController@destroy');

    Route::get('/users', 'UserController@index');
    Route::get('/users/{user}', 'UserController@show');
    Route::post('/users', 'UserController@store');
    Route::put('/users', 'UserController@update');
    Route::delete('/users/{user}', 'UserController@destroy');

    Route::get('/stores/{store}/orders', 'WooController@orders');
    Route::post('/stores/{store}/orders/{order}/outfordelivery', 'WooController@orderOutForDelivery');
    Route::post('/stores/{store}/orders/{order}/completed', 'WooController@orderCompleted');
    Route::get('/stores/{store}/products', 'WooController@products');
    Route::put('/stores/{store}/products/{product}', 'WooController@updateProduct');

    Route::get('/stores/{store}/statuslog', 'StatusChangeController@index');

    Route::get('/status/{store}', 'ServerStatusController@check');

    // Catch-all
    Route::get('/{any}', function () {
        return abort(404);
    })->where('any', '.*');
});
