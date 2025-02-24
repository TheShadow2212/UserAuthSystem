<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('auth')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/verify', [AuthController::class, 'showVerificationForm'])->name('verify');
    Route::post('/verify', [AuthController::class, 'verifyCode']);
});

Route::middleware(['jwt.cookie'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::fallback(function () {
     return redirect()->route('login')->withErrors(['error' => 'Ruta no encontrada.']);
});