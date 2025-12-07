@extends('admin.layouts.app')

@section('title', 'Bảng tin')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Bảng tin</h1>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Employees Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="bg-success p-3 text-white rounded-top">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-users icon-3x"></i>
                </div>
                <div class="col-6 text-right">
                    <p class="qty-3x">{{ $stats['total_employees'] }}</p>
                    Nhân viên
                </div>
            </div>
        </div>
        <div class="bg-gray-200 border-top p-3 rounded-bottom">
            <div class="row">
                <div class="col-6"><a href="{{ route('admin.employees.index') }}" class="text-success">Xem chi tiết</a></div>
                <div class="col-6 text-right"><a href="{{ route('admin.employees.index') }}" class="text-success"><i class="fas fa-arrow-alt-circle-right"></i></a></div>
            </div>
        </div>
    </div>

    <!-- Users Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="bg-info p-3 text-white rounded-top">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-user-friends icon-3x"></i>
                </div>
                <div class="col-6 text-right">
                    <p class="qty-3x">{{ $stats['total_users'] }}</p>
                    Người dùng
                </div>
            </div>
        </div>
        <div class="bg-gray-200 border-top p-3 rounded-bottom">
            <div class="row">
                <div class="col-6"><a href="{{ route('admin.users.index') }}" class="text-info">Xem chi tiết</a></div>
                <div class="col-6 text-right"><a href="{{ route('admin.users.index') }}" class="text-info"><i class="fas fa-arrow-alt-circle-right"></i></a></div>
            </div>
        </div>
    </div>

    <!-- Services Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="bg-warning p-3 text-white rounded-top">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-cut icon-3x"></i>
                </div>
                <div class="col-6 text-right">
                    <p class="qty-3x">{{ $stats['total_services'] }}</p>
                    Dịch vụ
                </div>
            </div>
        </div>
        <div class="bg-gray-200 border-top p-3 rounded-bottom">
            <div class="row">
                <div class="col-6"><a href="{{ route('admin.services.index') }}" class="text-warning">Xem chi tiết</a></div>
                <div class="col-6 text-right"><a href="{{ route('admin.services.index') }}" class="text-warning"><i class="fas fa-arrow-alt-circle-right"></i></a></div>
            </div>
        </div>
    </div>

    <!-- Products Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="bg-primary p-3 text-white rounded-top">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-box icon-3x"></i>
                </div>
                <div class="col-6 text-right">
                    <p class="qty-3x">{{ $stats['total_products'] }}</p>
                    Sản phẩm
                </div>
            </div>
        </div>
        <div class="bg-gray-200 border-top p-3 rounded-bottom">
            <div class="row">
                <div class="col-6"><a href="{{ route('admin.products.index') }}" class="text-primary">Xem chi tiết</a></div>
                <div class="col-6 text-right"><a href="{{ route('admin.products.index') }}" class="text-primary"><i class="fas fa-arrow-alt-circle-right"></i></a></div>
            </div>
        </div>
    </div>

    <!-- Appointments Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="bg-danger p-3 text-white rounded-top">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-calendar-check icon-3x"></i>
                </div>
                <div class="col-6 text-right">
                    <p class="qty-3x">{{ $stats['total_appointments'] }}</p>
                    Lịch hẹn
                </div>
            </div>
        </div>
        <div class="bg-gray-200 border-top p-3 rounded-bottom">
            <div class="row">
                <div class="col-6"><a href="{{ route('admin.appointments.index') }}" class="text-danger">Xem chi tiết</a></div>
                <div class="col-6 text-right"><a href="{{ route('admin.appointments.index') }}" class="text-danger"><i class="fas fa-arrow-alt-circle-right"></i></a></div>
            </div>
        </div>
    </div>

    <!-- Orders Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="bg-secondary p-3 text-white rounded-top">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-shopping-cart icon-3x"></i>
                </div>
                <div class="col-6 text-right">
                    <p class="qty-3x">{{ $stats['total_orders'] }}</p>
                    Đơn hàng
                </div>
            </div>
        </div>
        <div class="bg-gray-200 border-top p-3 rounded-bottom">
            <div class="row">
                <div class="col-6"><a href="{{ route('admin.orders.index') }}" class="text-secondary">Xem chi tiết</a></div>
                <div class="col-6 text-right"><a href="{{ route('admin.orders.index') }}" class="text-secondary"><i class="fas fa-arrow-alt-circle-right"></i></a></div>
            </div>
        </div>
    </div>

    <!-- Pending Appointments Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="bg-warning p-3 text-white rounded-top">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-clock icon-3x"></i>
                </div>
                <div class="col-6 text-right">
                    <p class="qty-3x">{{ $stats['pending_appointments'] }}</p>
                    Lịch chờ
                </div>
            </div>
        </div>
        <div class="bg-gray-200 border-top p-3 rounded-bottom">
            <div class="row">
                <div class="col-6"><a href="{{ route('admin.appointments.index', ['status' => 'Chờ xử lý']) }}" class="text-warning">Xem chi tiết</a></div>
                <div class="col-6 text-right"><a href="{{ route('admin.appointments.index', ['status' => 'Chờ xử lý']) }}" class="text-warning"><i class="fas fa-arrow-alt-circle-right"></i></a></div>
            </div>
        </div>
    </div>

    <!-- Pending Orders Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="bg-info p-3 text-white rounded-top">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-hourglass-half icon-3x"></i>
                </div>
                <div class="col-6 text-right">
                    <p class="qty-3x">{{ $stats['pending_orders'] }}</p>
                    Đơn chờ
                </div>
            </div>
        </div>
        <div class="bg-gray-200 border-top p-3 rounded-bottom">
            <div class="row">
                <div class="col-6"><a href="{{ route('admin.orders.index', ['status' => 'Chờ lấy hàng']) }}" class="text-info">Xem chi tiết</a></div>
                <div class="col-6 text-right"><a href="{{ route('admin.orders.index', ['status' => 'Chờ lấy hàng']) }}" class="text-info"><i class="fas fa-arrow-alt-circle-right"></i></a></div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="row">
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Đơn hàng gần đây</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td>#{{ $order->id }}</td>
                                    <td>{{ $order->user->name ?? 'N/A' }}</td>
                                    <td>{{ $order->status }}</td>
                                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Chưa có đơn hàng nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Lịch hẹn gần đây</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Mã lịch</th>
                                <th>Khách hàng</th>
                                <th>Nhân viên</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAppointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->booking_code ?? '#' . $appointment->id }}</td>
                                    <td>{{ $appointment->user->name ?? 'N/A' }}</td>
                                    <td>{{ $appointment->employee->user->name ?? 'Chưa phân công' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $appointment->status == 'Hoàn thành' ? 'success' : ($appointment->status == 'Đã hủy' ? 'danger' : ($appointment->status == 'Đã xác nhận' ? 'info' : 'warning')) }}">
                                            {{ $appointment->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Chưa có lịch hẹn nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
            }
        });
    });
</script>
@endpush
