<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Usercontroller;

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

Route::get('/', function () {
    return View::make('pages.home');
});
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
// Route::get('/join-with-us', function () {
//     return View::make('pages.join');
// });
Route::get('/join-with-us', [Usercontroller::class, 'index']);
Route::post('/register', [Usercontroller::class, 'register']);
Route::get('/dashboard', function () {
    return View::make('pages.admin.dashboard');
});