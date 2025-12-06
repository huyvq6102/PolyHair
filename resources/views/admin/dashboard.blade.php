@extends('admin.layouts.app')

@section('title', 'Bảng tin')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Thống kê</h1>
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

    <!-- Products Card -->

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

    <!-- Today Revenue Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="bg-primary p-3 text-white rounded-top">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-money-bill-wave icon-3x"></i>
                </div>
                <div class="col-6 text-right">
                    <p class="qty-3x">
                        @if($stats['today_revenue'] > 0)
                            {{ number_format($stats['today_revenue'] / 1000000, 1, ',', '.') }}M
                        @else
                            0
                        @endif
                    </p>
                    Doanh thu 
                </div>
            </div>
        </div>
        <div class="bg-gray-200 border-top p-3 rounded-bottom">
            <div class="row">
                <div class="col-6"><a href="{{ route('admin.payments.index') }}" class="text-primary">Xem chi tiết</a></div>
                <div class="col-6 text-right"><a href="{{ route('admin.payments.index') }}" class="text-primary"><i class="fas fa-arrow-alt-circle-right"></i></a></div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Appointments Table and Status Chart -->
<div class="row">
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Lịch hẹn gần đây</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="recentAppointmentsTable" class="table table-bordered" width="100%" cellspacing="0">
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
                                    <td>#{{ $appointment->id }}</td>
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

    <!-- Appointment Status Chart -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Trạng thái lịch hẹn</h6>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 300px;">
                    <canvas id="appointmentStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Appointments Chart -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Biểu đồ lịch hẹn theo ngày (30 ngày gần đây)</h6>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 300px;">
                    <canvas id="appointmentsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Biểu đồ doanh thu theo tháng (12 tháng gần đây)</h6>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 300px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Services Chart -->
<div class="row">
    <div class="col-xl-12 col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Top 5 dịch vụ được đặt nhiều nhất</h6>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 300px;">
                    <canvas id="topServicesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Employee Performance Chart -->
<div class="row">
    <div class="col-xl-12 col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Hiệu suất nhân viên (Top 10)</h6>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 350px;">
                    <canvas id="employeePerformanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer Type Chart and Peak Hours Chart -->
<div class="row">
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Khách mới vs Khách quay lại (30 ngày gần nhất)</h6>
            </div>
            <div class="card-body">
                @if($showCustomerChart)
                    <div style="position: relative; height: 300px;">
                        <canvas id="customerTypeChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-5">
                        @php
                            $totalCustomers = $newCustomers + $returningCustomers;
                            $newPercentage = $totalCustomers > 0 ? round(($newCustomers / $totalCustomers) * 100, 1) : 0;
                            $returningPercentage = $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100, 1) : 0;
                        @endphp
                        @if($totalCustomers > 0)
                            <div class="mb-4">
                                <h4 class="text-primary mb-3">
                                    <i class="fas fa-chart-pie"></i> Thống kê khách hàng
                                </h4>
                                @if($returningPercentage >= 50)
                                    <p class="lead text-success mb-2">
                                        <strong>{{ $returningPercentage }}%</strong> khách là khách quay lại
                                    </p>
                                    <p class="text-muted mb-0">
                                        Trong 30 ngày gần nhất có <strong>{{ $returningCustomers }}</strong> khách quay lại và <strong>{{ $newCustomers }}</strong> khách mới
                                    </p>
                                @else
                                    <p class="lead text-info mb-2">
                                        <strong>{{ $newPercentage }}%</strong> khách là khách mới
                                    </p>
                                    <p class="text-muted mb-0">
                                        Trong 30 ngày gần nhất có <strong>{{ $newCustomers }}</strong> khách mới và <strong>{{ $returningCustomers }}</strong> khách quay lại
                                    </p>
                                @endif
                            </div>
                            <div class="row mt-4">
                                <div class="col-6">
                                    <div class="border rounded p-3 bg-light">
                                        <h5 class="text-primary mb-1">{{ $newCustomers }}</h5>
                                        <small class="text-muted">Khách mới</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3 bg-light">
                                        <h5 class="text-success mb-1">{{ $returningCustomers }}</h5>
                                        <small class="text-muted">Khách quay lại</small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="py-4">
                                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Chưa có dữ liệu khách hàng trong 30 ngày gần nhất</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Peak Hours Chart -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Giờ cao điểm đặt lịch</h6>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 300px;">
                    <canvas id="peakHoursChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTables only on specific tables with error handling
        try {
            if ($('#recentOrdersTable').length && $('#recentOrdersTable tbody tr').length > 0) {
                $('#recentOrdersTable').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
                    },
                    "pageLength": 5,
                    "lengthChange": false,
                    "searching": false,
                    "info": false
                });
            }
        } catch(e) {
            console.warn('Error initializing recentOrdersTable:', e);
        }

        try {
            if ($('#recentAppointmentsTable').length && $('#recentAppointmentsTable tbody tr').length > 0) {
                $('#recentAppointmentsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
                    },
                    "pageLength": 5,
                    "lengthChange": false,
                    "searching": false,
                    "info": false
                });
            }
        } catch(e) {
            console.warn('Error initializing recentAppointmentsTable:', e);
        }

        // Appointments Chart
        var ctx = document.getElementById('appointmentsChart');
        if (!ctx) {
            console.error('Canvas element not found');
            return;
        }
        ctx = ctx.getContext('2d');

        // Get chart data
        var chartLabels = @json($chartLabels ?? []);
        var chartData = @json($chartData ?? []);

        var appointmentsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Số lịch hẹn',
                    data: chartData,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.15)',
                    borderWidth: 2,
                    fill: true,
                    lineTension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: '#4e73df',
                    pointHoverBorderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        fontSize: 14,
                        fontFamily: "'Nunito', sans-serif",
                        padding: 15
                    }
                },
                tooltips: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFontSize: 14,
                    titleFontFamily: "'Nunito', sans-serif",
                    bodyFontSize: 13,
                    bodyFontFamily: "'Nunito', sans-serif",
                    displayColors: false,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return 'Số lịch hẹn: ' + tooltipItem.yLabel;
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            stepSize: 1,
                            fontSize: 12,
                            fontFamily: "'Nunito', sans-serif"
                        },
                        gridLines: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            fontSize: 11,
                            fontFamily: "'Nunito', sans-serif",
                            maxRotation: 45,
                            minRotation: 45
                        },
                        gridLines: {
                            display: false
                        }
                    }]
                }
            }
        });

        // Revenue Chart (Bar Chart with Trendline)
        var revenueCtx = document.getElementById('revenueChart');
        if (!revenueCtx) {
            console.error('Revenue canvas element not found');
        } else {
            revenueCtx = revenueCtx.getContext('2d');

            // Get revenue data
            var revenueLabels = @json($revenueLabels ?? []);
            var revenueData = @json($revenueData ?? []);

            // Calculate total revenue
            var totalRevenue = revenueData.length > 0 ? revenueData.reduce(function(a, b) { return a + b; }, 0) : 0;

            // Debug: Log data to console
            console.log('Revenue Labels:', revenueLabels);
            console.log('Revenue Data:', revenueData);
            console.log('Total Revenue:', totalRevenue);

            // Calculate trendline data (simple linear regression)
            function calculateTrendline(data) {
                var n = data.length;
                var sumX = 0, sumY = 0, sumXY = 0, sumX2 = 0;
                for (var i = 0; i < n; i++) {
                    sumX += i;
                    sumY += data[i];
                    sumXY += i * data[i];
                    sumX2 += i * i;
                }
                var slope = (n * sumXY - sumX * sumY) / (n * sumX2 - sumX * sumX);
                var intercept = (sumY - slope * sumX) / n;

                var trendlineData = [];
                for (var i = 0; i < n; i++) {
                    trendlineData.push(slope * i + intercept);
                }
                return trendlineData;
            }

            var trendlineData = calculateTrendline(revenueData);

            // Format number to VND
            function formatVND(value) {
                return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(value);
            }

            var revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: revenueLabels,
                    datasets: [
                        {
                            label: 'Doanh thu',
                            data: revenueData,
                            backgroundColor: 'rgba(78, 115, 223, 0.6)',
                            borderColor: '#4e73df',
                            borderWidth: 2
                        },
                        {
                            label: 'Xu hướng',
                            data: trendlineData,
                            type: 'line',
                            borderColor: '#1cc88a',
                            backgroundColor: 'rgba(28, 200, 138, 0)',
                            borderWidth: 2.5,
                            borderDash: [5, 5],
                            fill: false,
                            pointRadius: 0,
                            pointHoverRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            fontSize: 14,
                            fontFamily: "'Nunito', sans-serif",
                            padding: 15
                        }
                    },
                    tooltips: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFontSize: 14,
                        titleFontFamily: "'Nunito', sans-serif",
                        bodyFontSize: 13,
                        bodyFontFamily: "'Nunito', sans-serif",
                        displayColors: true,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (tooltipItem.datasetIndex === 0) {
                                    label += formatVND(tooltipItem.yLabel);
                                } else {
                                    label += formatVND(tooltipItem.yLabel) + ' (xu hướng)';
                                }
                                return label;
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                fontSize: 12,
                                fontFamily: "'Nunito', sans-serif",
                                callback: function(value) {
                                    // Format large numbers
                                    if (value >= 1000000) {
                                        return (value / 1000000).toFixed(1) + 'M';
                                    } else if (value >= 1000) {
                                        return (value / 1000).toFixed(0) + 'K';
                                    }
                                    return value;
                                }
                            },
                            gridLines: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        }],
                        xAxes: [{
                            ticks: {
                                fontSize: 11,
                                fontFamily: "'Nunito', sans-serif",
                                maxRotation: 45,
                                minRotation: 45
                            },
                            gridLines: {
                                display: false
                            }
                        }]
                    }
                }
            });
        }

        // Top Services Chart (Horizontal Bar Chart)
        var topServicesCtx = document.getElementById('topServicesChart');
        if (!topServicesCtx) {
            console.error('Top Services canvas element not found');
        } else {
            topServicesCtx = topServicesCtx.getContext('2d');

            // Get top services data
            var topServiceLabels = @json($topServiceLabels ?? []);
            var topServiceData = @json($topServiceData ?? []);

            // Debug: Log data to console
            console.log('Top Service Labels:', topServiceLabels);
            console.log('Top Service Data:', topServiceData);

            var topServicesChart = new Chart(topServicesCtx, {
                type: 'horizontalBar',
                data: {
                    labels: topServiceLabels,
                    datasets: [{
                        label: 'Số lần đặt',
                        data: topServiceData,
                        backgroundColor: [
                            'rgba(78, 115, 223, 0.5)',
                            'rgba(28, 200, 138, 0.5)',
                            'rgba(54, 185, 204, 0.5)',
                            'rgba(246, 194, 62, 0.5)',
                            'rgba(231, 74, 59, 0.5)'
                        ],
                        borderColor: [
                            '#4e73df',
                            '#1cc88a',
                            '#36b9cc',
                            '#f6c23e',
                            '#e74a3b'
                        ],
                        borderWidth: 1.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            fontSize: 14,
                            fontFamily: "'Nunito', sans-serif",
                            padding: 15
                        }
                    },
                    tooltips: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFontSize: 14,
                        titleFontFamily: "'Nunito', sans-serif",
                        bodyFontSize: 13,
                        bodyFontFamily: "'Nunito', sans-serif",
                        displayColors: true,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return 'Số lần đặt: ' + tooltipItem.xLabel;
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            ticks: {
                                beginAtZero: true,
                                stepSize: 1,
                                fontSize: 12,
                                fontFamily: "'Nunito', sans-serif"
                            },
                            gridLines: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                fontSize: 12,
                                fontFamily: "'Nunito', sans-serif"
                            },
                            gridLines: {
                                display: false
                            }
                        }]
                    }
                }
            });
        }

        // Employee Performance Chart (Grouped Bar Chart)
        var employeePerfCtx = document.getElementById('employeePerformanceChart');
        if (!employeePerfCtx) {
            console.error('Employee Performance canvas element not found');
        } else {
            employeePerfCtx = employeePerfCtx.getContext('2d');

            // Get employee performance data
            var employeeLabels = @json($employeeLabels ?? []);
            var employeeAppointmentData = @json($employeeAppointmentData ?? []);
            var employeeRevenueData = @json($employeeRevenueData ?? []);

            // Debug: Log data to console
            console.log('Employee Labels:', employeeLabels);
            console.log('Employee Appointment Data:', employeeAppointmentData);
            console.log('Employee Revenue Data:', employeeRevenueData);

            // Format number to VND
            function formatVND(value) {
                return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(value);
            }

            var employeePerformanceChart = new Chart(employeePerfCtx, {
                type: 'bar',
                data: {
                    labels: employeeLabels,
                    datasets: [
                        {
                            label: 'Số lịch hẹn',
                            data: employeeAppointmentData,
                            backgroundColor: 'rgba(78, 115, 223, 0.5)',
                            borderColor: '#4e73df',
                            borderWidth: 1.5,
                            yAxisID: 'y-axis-1'
                        },
                        {
                            label: 'Doanh thu (VND)',
                            data: employeeRevenueData,
                            backgroundColor: 'rgba(28, 200, 138, 0.5)',
                            borderColor: '#1cc88a',
                            borderWidth: 1.5,
                            yAxisID: 'y-axis-2'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            fontSize: 14,
                            fontFamily: "'Nunito', sans-serif",
                            padding: 15
                        }
                    },
                    tooltips: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFontSize: 14,
                        titleFontFamily: "'Nunito', sans-serif",
                        bodyFontSize: 13,
                        bodyFontFamily: "'Nunito', sans-serif",
                        displayColors: true,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (tooltipItem.datasetIndex === 0) {
                                    label += tooltipItem.yLabel + ' lịch';
                                } else {
                                    label += formatVND(tooltipItem.yLabel);
                                }
                                return label;
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            ticks: {
                                fontSize: 11,
                                fontFamily: "'Nunito', sans-serif",
                                maxRotation: 45,
                                minRotation: 45
                            },
                            gridLines: {
                                display: false
                            }
                        }],
                        yAxes: [
                            {
                                id: 'y-axis-1',
                                type: 'linear',
                                position: 'left',
                                ticks: {
                                    beginAtZero: true,
                                    stepSize: 1,
                                    fontSize: 12,
                                    fontFamily: "'Nunito', sans-serif"
                                },
                                gridLines: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Số lịch hẹn',
                                    fontSize: 12,
                                    fontFamily: "'Nunito', sans-serif"
                                }
                            },
                            {
                                id: 'y-axis-2',
                                type: 'linear',
                                position: 'right',
                                ticks: {
                                    beginAtZero: true,
                                    fontSize: 12,
                                    fontFamily: "'Nunito', sans-serif",
                                    callback: function(value) {
                                        // Format large numbers
                                        if (value >= 1000000) {
                                            return (value / 1000000).toFixed(1) + 'M';
                                        } else if (value >= 1000) {
                                            return (value / 1000).toFixed(0) + 'K';
                                        }
                                        return value;
                                    }
                                },
                                gridLines: {
                                    drawOnChartArea: false
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Doanh thu (VND)',
                                    fontSize: 12,
                                    fontFamily: "'Nunito', sans-serif"
                                }
                            }
                        ]
                    }
                }
            });
        }

        // Appointment Status Chart (Doughnut Chart)
        var statusCtx = document.getElementById('appointmentStatusChart');
        if (!statusCtx) {
            console.error('Appointment Status canvas element not found');
        } else {
            statusCtx = statusCtx.getContext('2d');

            // Get status data
            var statusLabels = @json($statusLabels ?? []);
            var statusData = @json($statusData ?? []);

            // Debug: Log data to console
            console.log('Status Labels:', statusLabels);
            console.log('Status Data:', statusData);

            var appointmentStatusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusData,
                        backgroundColor: [
                            'rgba(28, 200, 138, 0.7)',  // Đã thanh toán - xanh lá
                            'rgba(246, 194, 62, 0.7)',   // Đang chờ - vàng
                            'rgba(231, 74, 59, 0.7)',   // Đã hủy - đỏ
                            'rgba(133, 135, 150, 0.7)'   // Không đến - xám
                        ],
                        borderColor: [
                            '#1cc88a',
                            '#f6c23e',
                            '#e74a3b',
                            '#858796'
                        ],
                        borderWidth: 2,
                        hoverBackgroundColor: [
                            '#1cc88a',
                            '#f6c23e',
                            '#e74a3b',
                            '#858796'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            fontSize: 13,
                            fontFamily: "'Nunito', sans-serif",
                            padding: 15,
                            boxWidth: 15
                        }
                    },
                    tooltips: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFontSize: 14,
                        titleFontFamily: "'Nunito', sans-serif",
                        bodyFontSize: 13,
                        bodyFontFamily: "'Nunito', sans-serif",
                        displayColors: true,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.labels[tooltipItem.index] || '';
                                var value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                var total = data.datasets[tooltipItem.datasetIndex].data.reduce(function(a, b) { return a + b; }, 0);
                                var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    },
                    cutoutPercentage: 50
                }
            });
        }

        // Customer Type Chart (Doughnut Chart) - Only show if >= 5 customers
        var customerTypeCtx = document.getElementById('customerTypeChart');
        if (customerTypeCtx) {
            var showCustomerChart = @json($showCustomerChart ?? false);
            if (showCustomerChart) {
                customerTypeCtx = customerTypeCtx.getContext('2d');

                // Get customer type data
                var customerLabels = @json($customerLabels ?? []);
                var customerData = @json($customerData ?? []);

                // Debug: Log data to console
                console.log('Customer Labels:', customerLabels);
                console.log('Customer Data:', customerData);

                var customerTypeChart = new Chart(customerTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: customerLabels,
                    datasets: [{
                        data: customerData,
                        backgroundColor: [
                            'rgba(78, 115, 223, 0.7)',  // Khách mới - xanh dương
                            'rgba(28, 200, 138, 0.7)'   // Khách quay lại - xanh lá
                        ],
                        borderColor: [
                            '#4e73df',
                            '#1cc88a'
                        ],
                        borderWidth: 2,
                        hoverBackgroundColor: [
                            '#4e73df',
                            '#1cc88a'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            fontSize: 13,
                            fontFamily: "'Nunito', sans-serif",
                            padding: 15,
                            boxWidth: 15
                        }
                    },
                    tooltips: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFontSize: 14,
                        titleFontFamily: "'Nunito', sans-serif",
                        bodyFontSize: 13,
                        bodyFontFamily: "'Nunito', sans-serif",
                        displayColors: true,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.labels[tooltipItem.index] || '';
                                var value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                var total = data.datasets[tooltipItem.datasetIndex].data.reduce(function(a, b) { return a + b; }, 0);
                                var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    },
                    cutoutPercentage: 50
                }
                });
            }
        }

        // Peak Hours Chart (Vertical Bar Chart)
        var peakHoursCtx = document.getElementById('peakHoursChart');
        if (!peakHoursCtx) {
            console.error('Peak Hours canvas element not found');
        } else {
            peakHoursCtx = peakHoursCtx.getContext('2d');

            // Get peak hours data
            var hourLabels = @json($hourLabels ?? []);
            var hourData = @json($hourData ?? []);

            // Debug: Log data to console
            console.log('Hour Labels:', hourLabels);
            console.log('Hour Data:', hourData);

            var peakHoursChart = new Chart(peakHoursCtx, {
                type: 'bar',
                data: {
                    labels: hourLabels,
                    datasets: [{
                        label: 'Số lịch hẹn',
                        data: hourData,
                        backgroundColor: 'rgba(78, 115, 223, 0.6)',
                        borderColor: '#4e73df',
                        borderWidth: 1.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            fontSize: 14,
                            fontFamily: "'Nunito', sans-serif",
                            padding: 15
                        }
                    },
                    tooltips: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFontSize: 14,
                        titleFontFamily: "'Nunito', sans-serif",
                        bodyFontSize: 13,
                        bodyFontFamily: "'Nunito', sans-serif",
                        displayColors: true,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return 'Số lịch hẹn: ' + tooltipItem.yLabel;
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                stepSize: 1,
                                fontSize: 12,
                                fontFamily: "'Nunito', sans-serif"
                            },
                            gridLines: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'Số lịch hẹn',
                                fontSize: 12,
                                fontFamily: "'Nunito', sans-serif"
                            }
                        }],
                        xAxes: [{
                            ticks: {
                                fontSize: 11,
                                fontFamily: "'Nunito', sans-serif",
                                maxRotation: 0,
                                minRotation: 0
                            },
                            gridLines: {
                                display: false
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'Giờ trong ngày',
                                fontSize: 12,
                                fontFamily: "'Nunito', sans-serif"
                            }
                        }]
                    }
                }
            });
        }
    });
</script>
@endpush
