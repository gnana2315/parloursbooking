<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Usercontroller;
use App\Http\Controllers\homeController;

use App\Http\Controllers\adminController;
use App\Http\Controllers\superAdminController;
use App\Http\Controllers\admin\configController;
use App\Http\Controllers\admin\vendorsController;
use App\Http\Controllers\admin\reportsController;
use App\Http\Controllers\admin\servicesController;

use App\Http\Controllers\userAdminController;
use App\Http\Controllers\SendMailController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [homeController::class, 'index']);
Route::get('/mens', function () {
    return View::make('pages.mens');
});
Route::get('/mensingle', function () {
    return View::make('pages.mensingle');
});
Route::get('/women', function () {
    return View::make('pages.women');
});
Route::get('/womensingle', function () {
    return View::make('pages.womensingle');
});
Route::get('/unisex', function () {
    return View::make('pages.unisex');
});
Route::get('/unisexsingle', function () {
    return View::make('pages.unisexsingle');
});
Route::get('/homevisit', function () {
    return View::make('pages.homevisit');
});
Route::get('/homevisitsingle', function () {
    return View::make('pages.homevisitsingle');
});


Route::group(['middleware' => 'auth.check'], function () {
    Route::group(['middleware' => 'isActive'], function () {
        Route::group(['middleware' => 'isAdmin'], function () {
            Route::get('/dashboard', [adminController::class, 'index']);

            Route::get('/serviceCategoriesView', [configController::class, 'viewServiceCategories']);
            Route::get('/get_service_category/{id}', [configController::class, 'getServiceCategory']);
            Route::post('/insertServiceCategory', [configController::class, 'insertServiceCategory']);
            Route::post('/updateServiceCategory', [configController::class, 'updateServiceCategory']);
            Route::get('/delete_service_category/{id}', [configController::class, 'deleteServiceCategory']);
            
            Route::get('/serviceTypesView', [configController::class, 'viewServiceTypes']);
            Route::get('/get_service_type/{id}', [configController::class, 'getServiceType']);
            Route::post('/insertServiceType', [configController::class, 'insertServiceType']);
            Route::post('/updateServiceType', [configController::class, 'updateServiceType']);
            Route::get('/delete_service_type/{id}', [configController::class, 'deleteServiceType']);

            Route::get('/seoIndex', [configController::class, 'seoIndex']);
            Route::get('/get_SEO/{id}', [configController::class, 'getSEOWords']);
            Route::post('/insertSEO', [configController::class, 'insertSEO']);
            Route::post('/updateSEO', [configController::class, 'updateSEOWords']);
            Route::get('/delete_seo/{id}', [configController::class, 'deleteSEO']);

            Route::get('/vendor', [vendorsController::class, 'index']);
            Route::get('/vendorlist', [vendorsController::class, 'getAllVendorsList']);
            Route::get('/viewVendor/{id}', [vendorsController::class, 'viewVendor']);

            Route::get('/reports', [reportsController::class, 'index']);
        });
        Route::group(['middleware' => 'isSuperAdmin'], function () {
            Route::get('/superdashboard', [superAdminController::class, 'index']);

            // Route::get('/serviceCategoriesView', [configController::class, 'viewServiceCategories']);

            Route::get('/vendors', [vendorsController::class, 'index']);
            Route::get('/vendorlist', [vendorsController::class, 'getAllVendorsList']);

            Route::get('/reports', [reportsController::class, 'index']);
        });
        Route::group(['middleware' => 'isUser'], function () {
            Route::get('/userdashboard', [userAdminController::class, 'index']);

            Route::get('/userservices', [servicesController::class, 'index']);
            Route::post('/insertService', [servicesController::class, 'insertService']);
            Route::get('/get_service/{id}', [servicesController::class, 'getService']);
            Route::post('/updateService', [servicesController::class, 'updateService']);            
            Route::get('/delete_service/{id}', [servicesController::class, 'deleteService']);
        });
    });
});

Route::get('/join-with-us', [Usercontroller::class, 'index']);
Route::get('/login', [Usercontroller::class, 'load_login']);
Route::get('/vendorRegistrationEmail', [SendMailController::class, 'vendorRegistrationEmail']);
Route::post('/register', [Usercontroller::class, 'register']);
Route::post('/userloging', [Usercontroller::class, 'userLogin']);
Route::get('/logout', [Usercontroller:: class, 'logout']);
