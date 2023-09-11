<?php

use App\Http\Controllers\securityController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\sessionController;



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

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('forgotpassword', [UserController::class, 'forgotpassword']);
Route::post('resetpassword', [UserController::class, 'resetpassword']);
Route::middleware('auth:api')->group(function () {
    Route::post('refreshtoken', [UserController::class, 'refreshtoken']);
    Route::post('logout', [UserController::class, 'logout']);

    //Active session Api
    Route::post('sessionstore', [sessionController::class, 'sessionstore']);
    Route::get('getsession', [sessionController::class, 'getsession']);
    Route::post('endsession', [sessionController::class, 'endsession']);
    Route::post('signoutalldevice', [sessionController::class, 'signoutalldevice']);
    //twostepverification for email
    Route::post('twostepverification', [securityController::class, 'twostepverification']);
    Route::post('sendotp', [securityController::class, 'sendotp']);
    Route::post('verifyotp', [securityController::class, 'verifyotp']);

    //changepassword
    Route::post('changepassword', [UserController::class, 'changepassword']);
});
