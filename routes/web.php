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


Route::prefix('pos')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('pos.index');
    Route::post('/create', [PosController::class, 'store'])->name('pos.store');
    Route::get('/recent-invoices', [PosController::class, 'recentInvoices'])->name('pos.recent');
    Route::get('/print/{id}', [PosController::class, 'printInvoice'])->name('pos.print');
});

// Invoice Routes
Route::resource('invoices', InvoiceController::class)->except(['create', 'store']);
Route::post('invoices/{id}/add-payment', [InvoiceController::class, 'addPayment'])->name('invoices.add-payment');

});