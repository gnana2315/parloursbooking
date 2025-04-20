<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\CustomersController;
use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CommonController;
use App\Http\Controllers\MigrationController;
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

//User Auth APIs
Route::post('/userRegister',[AuthController::class,'userRegisteration']);
Route::post('/userMobileVerification',[AuthController::class,'verifyVerificationCode']);
Route::post('/userLogin',[AuthController::class,'userLogin']);
Route::post('/userForgotPassword',[AuthController::class,'userForgotPassword']);
Route::post('/userResetPassword',[AuthController::class,'userResetPassword']);

// Route::controller(VendorController::class)->group(function(){
//     Route::post('vendorRegister','register');
//     Route::post('vendorlogin','login');
// });

Route::middleware(['auth:sanctum', 'validate.token'])->group(function () {
    Route::post('/userLogout',[AuthController::class,'userLogout']);

    //customer
    Route::post('/customerRegister/{id}', [ CustomersController::class,'register' ]);

    //vendor
    Route::post('/vendorRegister/{id}', [VendorController::class, 'vendorRegister' ]);
    Route::post('/vendorDocumentUpdate/{id}', [VendorController::class, 'vendorDocumentUpdate' ]);
    Route::post('/vendorConfig/{id}', [VendorController::class, 'vendorConfig' ]);
    Route::post('/vendorAvailability/{id}', [VendorController::class, 'vendorAvailability' ]);
    Route::post('/vendorSpecialCloses/{id}', [VendorController::class, 'vendorSpecialCloses' ]);
    Route::post('/addVendorServices/{id}', [VendorController::class, 'addVendorServices' ]);

    //common
    Route::get('/vendors/{business_type_id}', [CommonController::class, 'getVendors' ]);
    Route::post('/searchVendors', [CommonController::class, 'searchVendors' ]);
    Route::get('/serviceTypes', [CommonController::class, 'getServiceTypes' ]);
    Route::get('/businessTypes', [CommonController::class, 'getBusinessTypes' ]);
    Route::get('/serviceFor', [CommonController::class, 'getServiceFor' ]);
    Route::get('/getServices/{vendor_id}', [CommonController::class, 'getServicesByVendor' ]);
});