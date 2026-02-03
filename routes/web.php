<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\PathaoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use Enan\PathaoCourier\Facades\PathaoCourier;
use Enan\PathaoCourier\Requests\PathaoUserSuccessRateRequest;
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

    Route::get('/test', function(){
        $new = new PathaoUserSuccessRateRequest([
            'phone' => '+8801798651200'
        ]);
    $GET_CITIES =PathaoCourier::GET_CITIES();    
    $GET_ZONES =PathaoCourier::GET_ZONES(1);    
    $GET_AREAS =PathaoCourier::GET_AREAS(298);    
    return $GET_AREAS;
    });


    Route::get('/pathao', [PathaoController::class, 'index'])->name('pathao.index');
Route::get('/pathao/cities', [PathaoController::class, 'getCities'])->name('pathao.cities');
Route::get('/pathao/zones/{cityId}', [PathaoController::class, 'getZones'])->name('pathao.zones');
Route::get('/pathao/areas/{zoneId}', [PathaoController::class, 'getAreas'])->name('pathao.areas');



Route::get('/pathao/check-success-rate', function () {
    return view('pathao.success-rate');
});

Route::post('/pathao/user-success-rate', [PathaoController::class, 'getUserSuccessRate']);
Route::get('/pathao/getUserSuccessRateByPhone', [PathaoController::class, 'getUserSuccessRateByPhone']);


// Make sure you have these routes
Route::post('/check-phone', [PathaoController::class, 'checkPhone'])->name('checkPhone');
Route::get('/check-phone/{phone?}', [PathaoController::class, 'checkPhone'])->name('checkPhone.get');
// In your routes/api.php or web.php

Route::get('/check-customer-by-phone/{phone}', [PathaoController::class, 'checkCustomerByPhone'])
    ->name('check.customer.phone');
});

Route::get('/invoices/download-today-csv', [InvoiceController::class, 'downloadTodayCSV'])
    ->name('invoices.download-today-csv');