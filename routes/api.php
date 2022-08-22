<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', 'App\Http\Controllers\api\AuthController@register')->name('api-register');
Route::post('/login', 'App\Http\Controllers\api\AuthController@login')->name('api-login');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', 'App\Http\Controllers\api\AuthController@logout')->name('api-logout');

    //vehicles
    Route::get('/vehicles', 'App\Http\Controllers\api\AuthController@index')->name('api-vehicles');
    Route::post('/add/vehicle', 'App\Http\Controllers\api\AuthController@add')->name('api-add-vehicle');
    Route::post('/update/vehicle', 'App\Http\Controllers\api\AuthController@update')->name('api-update-vehicle');

    //deposit
    Route::post('/deposit', 'App\Http\Controllers\api\AccountController@deposit')->name('api-deposit');
    Route::get('/transactions', 'App\Http\Controllers\api\AccountController@transactions')->name('api-transactions');
});
