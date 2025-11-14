<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\ServiceService;
use App\Services\AppointmentService;
use App\Services\OrderService;
use App\Models\User;
use App\Models\Employee;

class DashboardController extends Controller
{
    protected $productService;
    protected $serviceService;
    protected $appointmentService;
    protected $orderService;

    public function __construct(
        ProductService $productService,
        ServiceService $serviceService,
        AppointmentService $appointmentService,
        OrderService $orderService
    ) {
        $this->productService = $productService;
        $this->serviceService = $serviceService;
        $this->appointmentService = $appointmentService;
        $this->orderService = $orderService;
    }

    public function index()
    {
        $stats = [
            'total_products' => \App\Models\Product::count(),
            'total_services' => \App\Models\Service::count(),
            'total_appointments' => \App\Models\Appointment::count(),
            'total_orders' => \App\Models\Order::count(),
            'total_users' => User::where('role_id', 2)->count(), // Assuming role_id 2 is customer
            'total_employees' => Employee::count(),
            'pending_appointments' => \App\Models\Appointment::where('status', 'Chờ xử lý')->count(),
            'pending_orders' => \App\Models\Order::where('status', 'Chờ lấy hàng')->count(),
        ];

        $recentOrders = $this->orderService->getAll()->take(5);
        $recentAppointments = $this->appointmentService->getAll()->take(5);

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentAppointments'));
    }
}
