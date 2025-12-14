<?php

// reviewed and edited admin routes
use App\Http\Controllers\Admin\ReviewController;
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
use App\Http\Controllers\Admin\EmployeeAppointmentController;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'staff'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Appointment routes (Accessible by Staff)
    Route::get('appointments/cancelled', [AppointmentController::class, 'cancelled'])->name('appointments.cancelled');
    Route::get('appointments/employee/{employeeId}/services', [AppointmentController::class, 'getServicesByEmployee'])->name('appointments.employee-services');
    Route::post('appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('appointments/{id}/restore', [AppointmentController::class, 'restore'])->name('appointments.restore');
    Route::delete('appointments/{id}/force-delete', [AppointmentController::class, 'forceDelete'])->name('appointments.force-delete');
    Route::delete('appointments/{appointmentId}/remove-service/{detailId}', [AppointmentController::class, 'removeService'])->name('appointments.remove-service');
    Route::resource('appointments', AppointmentController::class);

    // Working Schedule (View Only for Staff)
    Route::get('working-schedules', [WorkingScheduleController::class, 'index'])->name('working-schedules.index');

    // Routes accessible by both Admin and Employee (View Only)
    // Users Management (View Only for Staff)
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');

    // Employees Management (View Only for Staff)
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('employee-skills', [EmployeeSkillController::class, 'index'])->name('employee-skills.index');

    // Promotions (Accessible by Staff)
    Route::get('promotions/trash', [PromotionController::class, 'trash'])->name('promotions.trash');
    Route::put('promotions/{id}/restore', [PromotionController::class, 'restore'])->name('promotions.restore');
    Route::delete('promotions/{id}/force-delete', [PromotionController::class, 'forceDelete'])->name('promotions.force-delete');
    Route::resource('promotions', PromotionController::class);

    // Protected Admin Routes
    Route::middleware(['admin'])->group(function () {

        // Users Management (Admin Only - Full CRUD)
        Route::get('users/trash', [UserController::class, 'trash'])->name('users.trash');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');

        // Employees Management (Admin Only - Full CRUD)
        Route::get('employees/trash', [EmployeeController::class, 'trash'])->name('employees.trash');
        Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::post('employees/{employee}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
        Route::delete('employees/{employee}/force-delete', [EmployeeController::class, 'forceDelete'])->name('employees.force-delete');
        Route::get('employee-skills/{employee}/edit', [EmployeeSkillController::class, 'edit'])->name('employee-skills.edit');
        Route::put('employee-skills/{employee}', [EmployeeSkillController::class, 'update'])->name('employee-skills.update');

        // Working Schedule Management (Admin Only)
        Route::get('working-schedules/trash', [WorkingScheduleController::class, 'trash'])->name('working-schedules.trash');
        Route::put('working-schedules/{id}/restore', [WorkingScheduleController::class, 'restore'])->name('working-schedules.restore');
        Route::delete('working-schedules/{id}/force-delete', [WorkingScheduleController::class, 'forceDelete'])->name('working-schedules.force-delete');
        Route::delete('working-schedules/delete-all', [WorkingScheduleController::class, 'deleteAll'])->name('working-schedules.delete-all');
        Route::delete('working-schedules/trash/delete-all', [WorkingScheduleController::class, 'deleteAllTrash'])->name('working-schedules.trash.delete-all');
        Route::resource('working-schedules', WorkingScheduleController::class)->except(['index']);

        // Other Resources (Admin Only)
        Route::resource('categories', CategoryController::class);
        Route::resource('types', TypeController::class);
        Route::resource('products', ProductController::class);

        // Services
        Route::get('services/trash', [ServiceController::class, 'trash'])->name('services.trash');
        Route::get('services/{id}/detail', [ServiceController::class, 'showDetail'])->name('services.detail');
        Route::put('services/{id}/restore', [ServiceController::class, 'restore'])->name('services.restore');
        Route::delete('services/{id}/force-delete', [ServiceController::class, 'forceDelete'])->name('services.force-delete');
        Route::resource('services', ServiceController::class);
        Route::resource('service-categories', ServiceCategoryController::class);

        // Payments
        Route::get('payments/export', [PaymentController::class, 'export'])->name('payments.export');
        Route::resource('payments', PaymentController::class);

        Route::resource('orders', OrderController::class);

        Route::resource('news', NewsController::class);

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        // Reviews
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [ReviewController::class, 'index'])->name('index');
            Route::get('/edit/{id}', [ReviewController::class, 'edit'])->name('edit');
            Route::put('/update/{id}', [ReviewController::class, 'update'])->name('update');
            Route::post('/hide/{id}', [ReviewController::class, 'hide'])->name('hide');
            Route::delete('/{id}', [ReviewController::class, 'destroy'])->name('destroy');
            Route::get('/show/{id}', [ReviewController::class, 'show'])->name('show');
        });
    });
});

// Employee appointment routes (accessible by employees only)
Route::prefix('admin/employee')->name('employee.')->middleware(['auth', 'employee'])->group(function () {
    Route::get('appointments', [EmployeeAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('appointments/create', [EmployeeAppointmentController::class, 'create'])->name('appointments.create');
    Route::post('appointments', [EmployeeAppointmentController::class, 'store'])->name('appointments.store');
    Route::get('appointments/{id}', [EmployeeAppointmentController::class, 'show'])->name('appointments.show');
    Route::post('appointments/{id}/confirm', [EmployeeAppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('appointments/{id}/start', [EmployeeAppointmentController::class, 'start'])->name('appointments.start');
    Route::post('appointments/{id}/complete', [EmployeeAppointmentController::class, 'complete'])->name('appointments.complete');
    Route::post('appointments/{id}/cancel', [EmployeeAppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::delete('appointments/{id}', [EmployeeAppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::get('appointments/{id}/edit', [EmployeeAppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('appointments/{id}', [EmployeeAppointmentController::class, 'update'])->name('appointments.update');
});
