<?php

use Illuminate\Http\Request;

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

Route::middleware('api')->get('/stores', 'StoreController@index');
Route::middleware('api')->get('/stores/{store}', 'StoreController@show');
Route::middleware('api')->post('/stores', 'StoreController@store');
Route::middleware('api')->put('/stores', 'StoreController@update');
Route::middleware('api')->delete('/stores/{store}', 'StoreController@destroy');

Route::middleware('api')->get('/users', 'UserController@index');
Route::middleware('api')->get('/users/{user}', 'UserController@show');
Route::middleware('api')->post('/users', 'UserController@store');
Route::middleware('api')->put('/users', 'UserController@update');
Route::middleware('api')->delete('/users/{user}', 'UserController@destroy');

Route::middleware('api')->get('/stores/{store}/orders', 'WooController@orders');


Route::middleware(['api'])->post('/auth/login', 'AuthController@login');
Route::middleware(['api', 'jwt.auth'])->get('/auth/user', 'AuthController@user');
Route::middleware(['api','jwt.auth'])->get('/auth/refresh', 'AuthController@refresh');

// Catch-all
Route::middleware('api')->get('/{any}', function () {
  return abort(404);
})->where('any', '.*');;
