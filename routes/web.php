<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\ProductController;
use App\Http\Controllers\Site\ServiceController;
use App\Http\Controllers\Site\BlogController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\CartController;
use App\Http\Controllers\Site\AppointmentController;
use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\Site\CustomerController;
use App\Http\Controllers\Site\CheckoutController;

// Site Routes
Route::get('/', [HomeController::class, 'index'])->name('site.home');

Route::prefix('products')->name('site.products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/search', [ProductController::class, 'search'])->name('search');
    Route::get('/{id}', [ProductController::class, 'show'])->name('show');
});

Route::prefix('services')->name('site.services.')->group(function () {
    Route::get('/', [ServiceController::class, 'index'])->name('index');
    Route::get('/{id}', [ServiceController::class, 'show'])->name('show');
});

Route::prefix('blog')->name('site.blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/search', [BlogController::class, 'search'])->name('search');
    Route::get('/{id}', [BlogController::class, 'show'])->name('show');
});

Route::prefix('contact')->name('site.contact.')->group(function () {
    Route::get('/', [ContactController::class, 'index'])->name('index');
    Route::post('/', [ContactController::class, 'store'])->name('store');
});

Route::prefix('cart')->name('site.cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/update/{key}', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{key}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    Route::get('/count', [CartController::class, 'count'])->name('count');
    Route::get('/seed-fake-data', [CartController::class, 'seedFakeData'])->name('seed-fake-data');
});

Route::prefix('appointment')->name('site.appointment.')->group(function () {
    Route::get('/', [AppointmentController::class, 'create'])->name('create');
    Route::get('/select-services', [AppointmentController::class, 'selectServices'])->name('select-services');
    Route::get('/test-email', [\App\Http\Controllers\Site\TestEmailController::class, 'testEmail'])->name('test-email');
    Route::post('/', [AppointmentController::class, 'store'])->name('store');
    Route::match(['get', 'post'], '/available-time-slots', [AppointmentController::class, 'getAvailableTimeSlots'])->name('available-time-slots');
    Route::get('/services-by-category', [AppointmentController::class, 'getServicesByCategory'])->name('services-by-category');
    Route::get('/employees-by-service', [AppointmentController::class, 'getEmployeesByService'])->name('employees-by-service');
    Route::get('/success/{id}', [AppointmentController::class, 'success'])->name('success');
    Route::get('/{id}', [AppointmentController::class, 'show'])->name('show');
});

// Customer Routes
Route::prefix('customer')->name('site.customers.')->group(function () {
    Route::get('/{id}', [CustomerController::class, 'show'])->name('show');
});
// Payment Routes
Route::prefix('check-out')->name('site.payments.')->group(function () {

    Route::get('/', [CheckoutController::class, 'checkout'])->name('checkout');
    Route::post('/process', [CheckoutController::class, 'processPayment'])->name('process');
    Route::get('/success/{appointmentId}', [CheckoutController::class, 'paymentSuccess'])->name('success');

});


// Auth Routes
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
