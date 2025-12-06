<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
Route::get('/userDelete', [AuthController::class, 'deleteUserByID']);

Route::get('/test-s3', function () {
    try {
        $filename = 'test-file.txt';
        $content = 'This is a test file.';

        // Upload file to S3
        $path = Storage::disk('s3')->put($filename, $content, 'public');

        // Generate URL
        $url = Storage::disk('s3')->url($filename);

        return response()->json([
            'connected' => true,
            'uploaded' => $path !== false,
            'filename' => $filename,
            'url' => $url,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'connected' => false,
            'error' => $e->getMessage(),
        ]);
    }
});

// Route::post('/send-otp', [CommonController::class, 'sendOTP']);

Route::middleware(['auth:sanctum', 'validate.token'])->group(function () {
    Route::get('/getUser', [AuthController::class,'getUser']);
    Route::post('/userResetPassword',[AuthController::class,'userResetPassword']);
    Route::post('/userLogout',[AuthController::class,'userLogout']);
    // Route::get('/userDelete', [AuthController::class, 'deleteUserByID']);

    //user
    Route::post('/userRegistration', [AuthController::class, 'userRegistration']);
    Route::post('/userSetNewPassword',[AuthController::class,'userSetNewPassword']);
    Route::get('/getVendor', [AuthController::class,'getVendor']);

    //customer
    //Route::post('/customerRegister/{id}', [ CustomersController::class,'register' ]);

    //vendor
    // Route::post('/vendorRegister', [VendorController::class, 'vendorRegister' ]);
    Route::post('/businessVendorRegister', [VendorController::class, 'businessVendorRegister' ]);
    Route::post('/therapistVendorRegister', [VendorController::class, 'therapistVendorRegister' ]);
    Route::post('/vendorDocumentUpdate', [VendorController::class, 'vendorDocumentUpdate' ]);
    Route::post('/vendorDocumentUpdate_v1', [VendorController::class, 'vendorDocumentUpdate_v1' ]);
    Route::post('/vendorBankUpdate', [VendorController::class, 'vendorBankUpdate' ]);
    Route::post('/vendorConfig', [VendorController::class, 'vendorConfig' ]);
    Route::post('/vendorAvailability', [VendorController::class, 'vendorAvailability' ]);
    Route::post('/vendorSpecialCloses', [VendorController::class, 'vendorSpecialCloses' ]);
    Route::post('/addVendorServices', [VendorController::class, 'addVendorServices' ]);
    Route::get('/vendor/profile', [VendorController::class, 'getVendor' ]);
    Route::get('/getVendorDocuments',[VendorController::class,'getVendorDocuments']);
    Route::get('/getVendorAvailability', [VendorController::class, 'getVendorAvailability']);
    Route::get('/getVendorsSpecificClosings', [VendorController::class, 'getVendorsSpecificClosings']);
    Route::get('/getVendorBankDetails', [VendorController::class, 'getVendorBankDetails']);
    Route::get('/getVendorDetails', [VendorController::class, 'getVendorDetails']);
    Route::get('/vendorDetailStatus', [VendorController::class, 'getVendorDetailStatus']);
    Route::get('/vendorDetailStatus_v1', [VendorController::class, 'getVendorDetailStatus_v1']);

    //common
    Route::get('/vendors/{service_for_id}', [CommonController::class, 'getVendors' ]);
    Route::get('/required-documents', [CommonController::class, 'getRequiredDocuments' ]);
    Route::get('/searchVendors', [CommonController::class, 'searchVendors' ]);
    Route::get('/serviceTypes', [CommonController::class, 'getServiceTypes' ]);
    Route::get('/vendorTypes', [CommonController::class, 'getVendorTypes' ]);
    Route::get('/serviceFor', [CommonController::class, 'getServiceFor' ]);
    Route::get('/getServices/{vendor_id}', [CommonController::class, 'getServicesByVendor' ]);
    Route::get('/getBankList', [CommonController::class, 'getBankList']);
    Route::get('/getBusinessCategory', [CommonController::class, 'getBusinessCategory']);
    Route::get('/getAllPromoCodes', [CommonController::class, 'getAllPromoCodes']);
    Route::get('/cities', [CommonController::class, 'getCities']);
    Route::get('/getNotifications', [CommonController::class, 'notificationlist']);
    Route::get('/getStaticsByVendor', [CommonController::class, 'getStaticsByVendor']);
    Route::post('/test-notification', [CommonController::class, 'testNotification']);

    //booking
    Route::get('/getBookingSlots', [BookingController::class, 'getBookingSlots']);
    Route::post('/addOnlineBooking', [BookingController::class, 'addOnlineBooking']);
    Route::post('/addOnlineBooking_v1', [BookingController::class, 'addOnlineBooking_v1']);
    Route::post('/addManualBooking', [BookingController::class, 'addManualBooking']);
    Route::post('/addRating', [BookingController::class, 'addRating']);
    Route::get('/getBookings', [BookingController::class, 'getBookings']);
    Route::get('/bookings/{id}', [BookingController::class, 'getBookingDetailsById']);

    //customer
    Route::get('/customer', [CustomersController::class, 'getCustomer']);
    Route::post('/customer/favourite', [CustomersController::class, 'addRemoveCustomerFavourite']);
    Route::get('/customer/favourites', [CustomersController::class, 'getCustomerFavourites']);
    Route::get('/customer/bookings', [CustomersController::class, 'getBookingsByCustomerID']);
    Route::get('/vendor/{vendor_id}', [CustomersController::class, 'getVendorByID' ]);

    //transections
    Route::get('/getThisWeekEarningsByVendor', [VendorController::class, 'getThisWeekEarningsByVendor']);
    Route::get('/getPayoutHistoryByVendor', [VendorController::class, 'getPayoutHistoryByVendor']);
    Route::get('/getAllEarningsByVendor', [VendorController::class, 'getAllEarningsByVendor']);
    Route::get('/getIncentivesByVendor', [VendorController::class, 'getIncentivesByVendor']);
    Route::get('/getToBePaidByVendor', [VendorController::class, 'getToBePaidByVendor']);
});