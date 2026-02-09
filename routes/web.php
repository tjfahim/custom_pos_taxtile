<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PathaoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
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
    Route::get('/register', [AuthController::class, 'showRegisterFrom'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/register-submit', [AuthController::class, 'registerSubmit'])->name('register.submit');


// Logout route (accessible by authenticated users)
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Public API routes for form validation (no authentication needed)
Route::get('/check-phone-today/{phone}', [InvoiceController::class, 'checkPhoneToday'])
    ->name('admin.check.phone.today');
Route::get('/check-phone-last-days/{phone}', [InvoiceController::class, 'checkPhoneLastDays'])
    ->name('check.phone.last.days');
Route::get('/check-customer-by-phone/{phone}', [PathaoController::class, 'checkCustomerByPhone'])
    ->name('check.customer.phone');
Route::get('/check-customer-status/{phone}', [InvoiceController::class, 'checkCustomerStatus']);


// Admin dashboard routes - accessible by both admin and staff
Route::middleware(['auth', 'check.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    
    // CSV download
    Route::get('/invoices/download-today-csv', [InvoiceController::class, 'downloadTodayCSV'])
        ->name('invoices.download-today-csv');

    // Pathao routes
    Route::get('/pathao', [PathaoController::class, 'index'])->name('pathao.index');
    Route::get('/pathao/cities', [PathaoController::class, 'getCities'])->name('pathao.cities');
    Route::get('/pathao/zones/{cityId}', [PathaoController::class, 'getZones'])->name('pathao.zones');
    Route::get('/pathao/areas/{zoneId}', [PathaoController::class, 'getAreas'])->name('pathao.areas');
    Route::get('/pathao/check-success-rate', function () {
        return view('pathao.success-rate');
    })->name('pathao.success-rate');
    Route::post('/pathao/user-success-rate', [PathaoController::class, 'getUserSuccessRate'])->name('pathao.user-success-rate');
    Route::get('/pathao/getUserSuccessRateByPhone', [PathaoController::class, 'getUserSuccessRateByPhone'])->name('pathao.user-success-rate-by-phone');
  
    // Customers
    Route::resource('customers', CustomerController::class);
    Route::get('customers/trashed', [CustomerController::class, 'trashed'])->name('customers.trashed');
    Route::post('customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::patch('customers/{id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
    Route::delete('customers/{id}/force-delete', [CustomerController::class, 'forceDelete'])->name('customers.force-delete');
    
    // Invoices
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/pos', [InvoiceController::class, 'pos'])->name('pos');
        Route::post('/pos/store', [InvoiceController::class, 'storePos'])->name('store-pos');
        Route::get('/{id}/print', [InvoiceController::class, 'print'])->name('print');
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
        Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('destroy');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::patch('/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('update-status');
    });
    
    // Reports
    Route::get('/reports/invoices', [ReportController::class, 'invoiceReport'])->name('reports.invoices');
    Route::get('/reports/invoices-data', [ReportController::class, 'getInvoiceData'])->name('reports.invoices.data');
    Route::get('/reports/invoices-export', [ReportController::class, 'exportInvoices'])->name('reports.invoices.export');
});

// Admin-only routes (User & Role Management)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // User Management
    Route::resource('users', UserController::class);
    
   // Role Management
Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
});