<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BeforeLoginController;
use App\Http\Controllers\LoginController;

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
    return view('home');
});

Route::view('home', 'home');
Route::view('gallery', 'gallery');
Route::view('about', 'about');
Route::view('ccc', 'contact');
Route::view('login', 'login');
Route::view('register', 'register');
Route::post('register_insert_data', [BeforeLoginController::class, 'register_insert_data']);
Route::get('activate_account/{email}', [BeforeLoginController::class, 'activate_account']);
Route::post('login_authentication', [LoginController::class, 'login_authentication']);
Route::view('Reactivate_deleted_account', 'Reactivate_deleted_account');
Route::post('Reactivate_account', [LoginController::class, 'Reactivate_account']);
Route::get('user_dashboard', [LoginController::class, 'user_dashboard']);
Route::get('admin_dashboard', [LoginController::class, 'admin_dashboard']);
