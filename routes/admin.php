<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\TypeController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeSkillController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\WorkingScheduleController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PaymentController;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('categories', CategoryController::class);
    Route::resource('types', TypeController::class);
    Route::resource('products', ProductController::class);
    
    // Service routes with trash functionality
    Route::get('services/trash', [ServiceController::class, 'trash'])->name('services.trash');
    Route::get('services/{id}/detail', [ServiceController::class, 'showDetail'])->name('services.detail');
    Route::put('services/{id}/restore', [ServiceController::class, 'restore'])->name('services.restore');
    Route::delete('services/{id}/force-delete', [ServiceController::class, 'forceDelete'])->name('services.force-delete');
    Route::resource('services', ServiceController::class);
    
    Route::resource('service-categories', ServiceCategoryController::class);
    
    // Promotions routes with trash functionality
    Route::get('promotions/trash', [PromotionController::class, 'trash'])->name('promotions.trash');
    Route::put('promotions/{id}/restore', [PromotionController::class, 'restore'])->name('promotions.restore');
    Route::delete('promotions/{id}/force-delete', [PromotionController::class, 'forceDelete'])->name('promotions.force-delete');
    Route::resource('promotions', PromotionController::class);
    
    // Appointment routes with additional actions
    Route::get('appointments/cancelled', [AppointmentController::class, 'cancelled'])->name('appointments.cancelled');
    Route::get('appointments/employee/{employeeId}/services', [AppointmentController::class, 'getServicesByEmployee'])->name('appointments.employee-services');
    Route::post('appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('appointments/{id}/restore', [AppointmentController::class, 'restore'])->name('appointments.restore');
    Route::delete('appointments/{id}/force-delete', [AppointmentController::class, 'forceDelete'])->name('appointments.force-delete');
    Route::resource('appointments', AppointmentController::class);
    
    // Hóa đơn (Payments) routes
    Route::get('payments/export', [PaymentController::class, 'export'])
        ->name('payments.export');
    Route::resource('payments', PaymentController::class);

    Route::resource('orders', OrderController::class);
    // Users routes - đặt trước resource để tránh conflict
    Route::get('users/trash', [UserController::class, 'trash'])->name('users.trash');
    Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');
    Route::resource('users', UserController::class);
    
    // Employees routes - đặt trước resource để tránh conflict
    Route::get('employees/trash', [EmployeeController::class, 'trash'])->name('employees.trash');
    Route::post('employees/{employee}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
    Route::delete('employees/{employee}/force-delete', [EmployeeController::class, 'forceDelete'])->name('employees.force-delete');
    Route::resource('employees', EmployeeController::class);
    Route::get('employee-skills', [EmployeeSkillController::class, 'index'])->name('employee-skills.index');
    Route::get('employee-skills/{employee}/edit', [EmployeeSkillController::class, 'edit'])->name('employee-skills.edit');
    Route::put('employee-skills/{employee}', [EmployeeSkillController::class, 'update'])->name('employee-skills.update');
    Route::resource('news', NewsController::class);
    
    // Working schedule routes
    Route::get('working-schedules/trash', [WorkingScheduleController::class, 'trash'])->name('working-schedules.trash');
    Route::put('working-schedules/{id}/restore', [WorkingScheduleController::class, 'restore'])->name('working-schedules.restore');
    Route::delete('working-schedules/{id}/force-delete', [WorkingScheduleController::class, 'forceDelete'])->name('working-schedules.force-delete');
    Route::delete('working-schedules/delete-all', [WorkingScheduleController::class, 'deleteAll'])->name('working-schedules.delete-all');
    Route::delete('working-schedules/trash/delete-all', [WorkingScheduleController::class, 'deleteAllTrash'])->name('working-schedules.trash.delete-all');
    Route::resource('working-schedules', WorkingScheduleController::class);
    
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});

// Employee appointment routes (accessible by employees)
Route::prefix('admin/employee')->name('employee.')->middleware(['auth'])->group(function () {
    Route::get('appointments', [EmployeeAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('appointments/{id}', [EmployeeAppointmentController::class, 'show'])->name('appointments.show');
    Route::post('appointments/{id}/confirm', [EmployeeAppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('appointments/{id}/start', [EmployeeAppointmentController::class, 'start'])->name('appointments.start');
    Route::post('appointments/{id}/complete', [EmployeeAppointmentController::class, 'complete'])->name('appointments.complete');
    Route::post('appointments/{id}/cancel', [EmployeeAppointmentController::class, 'cancel'])->name('appointments.cancel');
});

