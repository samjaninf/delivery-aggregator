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

// === Auth-related routes ===
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth',
], function () {
    Route::post('login', 'AuthController@login');
    Route::get('user', 'AuthController@user');
    Route::middleware('jwt.refresh')->get('refresh', 'AuthController@refresh');
    Route::get('logout', 'AuthController@logout');
});

// === API routes ===
Route::group([
    'middleware' => 'api',
], function () {

    // === Stores CRUD ===
    Route::get('/stores', 'StoreController@index');
    Route::get('/stores/{store}', 'StoreController@show');
    Route::post('/stores', 'StoreController@store');
    Route::put('/stores', 'StoreController@update');
    Route::delete('/stores/{store}', 'StoreController@destroy');

    // === Users CRUD ===
    Route::get('/users', 'UserController@index');
    Route::get('/users/{user}', 'UserController@show');
    Route::post('/users', 'UserController@store');
    Route::put('/users', 'UserController@update');
    Route::delete('/users/{user}', 'UserController@destroy');

    // === Availabilities CRUD ===
    Route::get('/availabilities', 'AvailabilityController@index');
    Route::get('/availabilities/{availability}', 'AvailabilityController@show');
    Route::post('/availabilities', 'AvailabilityController@store');
    Route::put('/availabilities', 'AvailabilityController@update');
    Route::delete('/availabilities/{availability}', 'AvailabilityController@destroy');

    // === Reports ===
    Route::get('/reports/{year}/{month}', 'StatusChangeController@report');

    // === Woocommerce-related routes ===
    // All of the following routes are store-specific

    // Get list of orders
    Route::get('/stores/{store}/orders', 'WooController@orders');

    // Get list of all products
    Route::get('/stores/{store}/products', 'WooController@products');

    // Get store isOpen status
    Route::get('/stores/{store}/isOpen', 'WooController@isOpen');

    // Set store isOpen status
    Route::post('/stores/{store}/isOpen', 'WooController@setIsOpen');

    // Get store delivery slots settings
    Route::get('/stores/{store}/deliveryslots', 'WooController@deliverySlotsSettings');

    // Set store delivery slots settings
    Route::post('/stores/{store}/deliveryslots', 'WooController@setDeliverySlotsSettings');

    // Get status log for store
    Route::get('/stores/{store}/statuslog', 'StatusChangeController@index');

    // Set order state as "Prepared"
    Route::post('/stores/{store}/orders/{order}/prepared', 'WooController@orderPrepared');

    // Set order state as "Seen"
    Route::post('/stores/{store}/orders/{order}/seen', 'WooController@orderSeen');

    // Set order state as "Out for delivery"
    Route::post('/stores/{store}/orders/{order}/outfordelivery', 'WooController@orderOutForDelivery');

    // Set order state as "Completed"
    Route::post('/stores/{store}/orders/{order}/completed', 'WooController@orderCompleted');

    // Update product availability
    Route::put('/stores/{store}/products/{product}', 'WooController@updateProduct');

    // Get server status for store (uptime monitor)
    Route::get('/status/{store}', 'ServerStatusController@check');

    // Catch-all
    Route::get('/{any}', function () {
        return abort(404);
    })->where('any', '.*');
});
