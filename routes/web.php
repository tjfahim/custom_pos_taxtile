<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PathaoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryChargeController;
use App\Http\Controllers\InsideDhakaController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Artisan;
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
Route::get('/clear', function() {
    try {
        // Clear all caches
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('event:clear');
        Artisan::call('optimize:clear');
        
        return response()->json([
            'success' => true,
            'message' => 'All caches cleared successfully!',
            'caches' => [
                'application' => 'Cleared',
                'configuration' => 'Cleared',
                'route' => 'Cleared',
                'view' => 'Cleared',
                'event' => 'Cleared',
                'optimize' => 'Cleared'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error clearing caches: ' . $e->getMessage()
        ], 500);
    }
});

    Route::get('/', [AuthController::class, 'showLoginFrom'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegisterFrom'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/register-submit', [AuthController::class, 'registerSubmit'])->name('register.submit');


// Logout route (accessible by authenticated users)
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Public API routes for form validation (no authentication needed)
Route::get('/check-phone-today/{phone}', [InvoiceController::class, 'checkPhoneToday'])
    ->name('admin.check.phone.today');
    Route::get('/check-phone-fraud/{phone}', [PathaoController::class, 'checkPhone'])
    ->name('admin.check.phone.fraud');
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

    Route::get('/invoices/download-custom-csv', [InvoiceController::class, 'downloadCustomCSV'])->name('invoices.download-custom-csv');

   // Pathao routes
Route::get('/pathao', [PathaoController::class, 'index'])->name('pathao.index');
Route::get('/issueToken', [PathaoController::class, 'issueToken'])->name('pathao.issue-token');

// API endpoints
Route::get('/pathao/cities', [PathaoController::class, 'getCities'])->name('pathao.cities');
Route::get('/pathao/zones/{cityId}', [PathaoController::class, 'getZones'])->name('pathao.zones');
Route::get('/pathao/areas/{zoneId}', [PathaoController::class, 'getAreas'])->name('pathao.areas');

// Sync options
Route::get('/pathao/store-all-direct', [PathaoController::class, 'storeAllLocationsPaginated'])->name('pathao.store-all-direct');
Route::get('/pathao/sync-cities', [PathaoController::class, 'syncCitiesOnly'])->name('pathao.sync-cities');
Route::get('/pathao/sync-zones/{cityId}', [PathaoController::class, 'syncZonesForCity'])->name('pathao.sync-zones');
Route::get('/pathao/sync-areas/{zoneId}', [PathaoController::class, 'syncAreasForZone'])->name('pathao.sync-areas');
Route::get('/pathao/sync-status', [PathaoController::class, 'getSyncStatus'])->name('pathao.sync-status');

// Search and hierarchy
Route::get('/pathao/search', [PathaoController::class, 'searchLocation'])->name('pathao.search');
Route::get('/location/auto-submit', [PathaoController::class, 'autoSubmitLocation'])->name('location.auto-submit');
Route::get('/pathao/hierarchy/{areaId}', [PathaoController::class, 'getLocationHierarchy'])->name('pathao.hierarchy');
Route::post('/pathao/sync-city/{cityId}', [PathaoController::class, 'syncCity'])->name('pathao.sync-city');

// Management page
Route::get('/pathao/manage', [PathaoController::class, 'manage'])->name('pathao.manage');

// Pathao success rate routes
Route::get('/pathao/check-success-rate', function () {
    return view('pathao.success-rate');
})->name('pathao.success-rate');
Route::post('/pathao/user-success-rate', [PathaoController::class, 'getUserSuccessRate'])->name('pathao.user-success-rate');
Route::get('/pathao/getUserSuccessRateByPhone', [PathaoController::class, 'getUserSuccessRateByPhone'])->name('pathao.user-success-rate-by-phone');
    // Get statistics
    Route::get('/pathao/statistics', [PathaoController::class, 'getStatistics'])->name('pathao.statistics');

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

        Route::get('/print-multiple/{ids}', [InvoiceController::class, 'printMultiple'])->name('invoices.print-multiple');
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

    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    Route::post('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
    Route::post('/reports/print', [ReportController::class, 'print'])->name('reports.print');
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


    Route::resource('delivery-charges', DeliveryChargeController::class);


    Route::get('/delivery-charges-data', [DeliveryChargeController::class, 'getActiveCharges'])
        ->name('delivery-charges-data');

         Route::resource('inside-dhaka', InsideDhakaController::class);
    
    // Additional routes
    Route::get('inside-dhaka/toggle-status/{id}', [InsideDhakaController::class, 'toggleStatus'])
        ->name('inside-dhaka.toggle-status');

Route::get('/get-delivery-charge/{zoneId}/{totalQuantity?}', [PathaoController::class, 'getDeliveryChargeByZone'])
    ->name('get.delivery.charge.by.zone');
 

});

