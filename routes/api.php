<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\CustomersController;
use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CommonController;
use App\Http\Controllers\MigrationController;
use App\Http\Controllers\API\BookingController;
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
Route::post('/userMobileRegister',[AuthController::class,'userRegisterMobileNo']);
Route::post('/resendOtp',[AuthController::class,'resendOtp']);
Route::post('/userMobileVerification',[AuthController::class,'verifyVerificationCode']);
Route::post('/userLogin',[AuthController::class,'userLogin']);
Route::post('/userForgotPassword',[AuthController::class,'userForgotPassword']);
Route::post('/userResetPassword',[AuthController::class,'userResetPassword']);

Route::middleware(['auth:sanctum', 'validate.token'])->group(function () {
    Route::post('/userLogout',[AuthController::class,'userLogout']);

    //user
    Route::post('/userRegistration', [AuthController::class, 'userRegistration']);

    //customer
    Route::post('/customerRegister/{id}', [ CustomersController::class,'register' ]);

    //vendor
    Route::post('/vendorRegister', [VendorController::class, 'vendorRegister' ]);
    Route::post('/vendorDocumentUpdate', [VendorController::class, 'vendorDocumentUpdate' ]);
    Route::post('/vendorBankUpdate', [VendorController::class, 'vendorBankUpdate' ]);
    Route::post('/vendorConfig', [VendorController::class, 'vendorConfig' ]);
    Route::post('/vendorAvailability', [VendorController::class, 'vendorAvailability' ]);
    Route::post('/vendorSpecialCloses', [VendorController::class, 'vendorSpecialCloses' ]);
    Route::post('/addVendorServices/{id}', [VendorController::class, 'addVendorServices' ]);

    //common
    Route::get('/vendors/{business_type_id}', [CommonController::class, 'getVendors' ]);
    Route::post('/searchVendors', [CommonController::class, 'searchVendors' ]);
    Route::get('/serviceTypes', [CommonController::class, 'getServiceTypes' ]);
    Route::get('/vendorTypes', [CommonController::class, 'getVendorTypes' ]);
    Route::get('/serviceFor', [CommonController::class, 'getServiceFor' ]);
    Route::get('/getServices/{vendor_id}', [CommonController::class, 'getServicesByVendor' ]);
    Route::get('/getBankList', [CommonController::class, 'getBankList']);

    //booking
    Route::post('/getBookingSlots', [BookingController::class, 'getBookingSlots']);
    Route::post('/addBooking', [BookingController::class, 'addBooking']);
});