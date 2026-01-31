<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PosController;
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


Route::get('/', [AuthController::class, 'showLoginFrom'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegisterFrom'])->name('register');
Route::post('/register-submit', [AuthController::class, 'registerSubmit'])->name('register.submit');

Route::group(['middleware' => 'role:2', 'prefix' => 'admin'], function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');


    Route::resource('customers', CustomerController::class);

// Additional routes for soft delete functionality
Route::get('customers/trashed', [CustomerController::class, 'trashed'])->name('customers.trashed');
Route::post('customers/search', [CustomerController::class, 'search'])->name('customers.search');
Route::patch('customers/{id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
Route::delete('customers/{id}/force-delete', [CustomerController::class, 'forceDelete'])->name('customers.force-delete');


Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('index');
    Route::get('/pos', [InvoiceController::class, 'pos'])->name('pos');
    Route::post('/pos/store', [InvoiceController::class, 'storePos'])->name('store-pos');
    
    Route::get('/{id}/print', [InvoiceController::class, 'print'])->name('print');
    Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
    Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('destroy');
});


});