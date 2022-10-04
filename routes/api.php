<?php

use App\Http\Controllers\UserController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

    Route::resource('/users', UserController::class)->only('store');
    Route::get('/gettoken', [UserController::class, 'getJWT']);
    Route::post('/password', [UserController::class, 'changePass']);

Route::group(['middleware' => 'UserCheck'], function(){
    Route::resource('/users', UserController::class)->only('destroy');
});

Route::group(['middleware' => 'AdminCheck'], function(){
    Route::resource('/users', UserController::class)->except('store', 'getJWT','destroy');
});
