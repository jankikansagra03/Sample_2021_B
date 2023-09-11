<?php

use Illuminate\Support\Facades\Route;

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
