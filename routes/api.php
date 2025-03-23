<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\CustomersController;
use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\API\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Route::controller(CustomersController::class)->group(function(){
//     Route::post('customerregister','register');
//     Route::post('customerlogin','login');
// });
Route::post('/vendorRegister',[AuthController::class,'vendorRegisteration']);
Route::post('/vendorMobileVerification',[AuthController::class,'verifyVendorVerificationCode']);
Route::post('/vendorLogin',[AuthController::class,'vendorLogin']);

// Route::controller(VendorController::class)->group(function(){
//     Route::post('vendorRegister','register');
//     Route::post('vendorlogin','login');
// });

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', PostController::class);
    Route::post('/vendorLogout',[AuthController::class,'vendorLogout']);
});
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
