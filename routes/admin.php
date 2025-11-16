<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TypeController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\WorkingScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('categories', CategoryController::class);
    Route::resource('types', TypeController::class);
    Route::resource('products', ProductController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('appointments', AppointmentController::class);
    Route::resource('orders', OrderController::class);
    Route::resource('users', UserController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('news', NewsController::class);
    
    // Working Schedule routes (must be before resource to avoid conflicts)
    Route::get('working-schedules/trash', [WorkingScheduleController::class, 'trash'])->name('working-schedules.trash');
    Route::put('working-schedules/{id}/restore', [WorkingScheduleController::class, 'restore'])->name('working-schedules.restore');
    Route::delete('working-schedules/{id}/force-delete', [WorkingScheduleController::class, 'forceDelete'])->name('working-schedules.force-delete');
    Route::resource('working-schedules', WorkingScheduleController::class);
    
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});

