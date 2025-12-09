@extends('layouts.site')

@section('content')

<div class="container py-5" style="margin-top: 120px;">
    <div class="row">
        <!-- Cột thông tin cá nhân và hành động -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <!-- Avatar -->
                    <div class="mb-3">
                        @if($user->avatar)
                            <img src="{{ asset('legacy/images/avatars/' . $user->avatar) }}" 
                                 alt="{{ $user->name }}"
                                 class="rounded-circle img-fluid shadow-sm" 
                                 style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #f8f9fa;"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="rounded-circle mx-auto d-none align-items-center justify-content-center bg-gradient-primary text-white shadow-sm" 
                                 style="width: 150px; height: 150px; font-size: 48px; font-weight: bold; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @else
                            <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center bg-gradient-primary text-white shadow-sm" 
                                 style="width: 150px; height: 150px; font-size: 48px; font-weight: bold; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    
                    <h4 class="mb-4 fw-bold text-dark">{{ $user->name }}</h4>

                    <!-- Thống kê -->
                    <div class="mb-4 p-4 bg-light rounded-3">
                        <div class="d-flex flex-column align-items-center">
                            <div class="mb-2">
                                <i class="fas fa-cut fa-2x text-primary"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-primary">{{ $user->appointments->where('status', '!=', 'Đã hủy')->count() }}</h3>
                            <small class="text-muted fw-semibold">Lần cắt</small>
                        </div>
                    </div>

                    <!-- Nút hành động chính -->
                    <div class="d-grid gap-3">
                        <a href="{{ route('site.appointment.create') }}" 
                           class="btn btn-primary btn-lg rounded-pill fw-bold d-flex align-items-center justify-content-center py-3 shadow-sm text-decoration-none">
                            <i class="fas fa-calendar-plus me-2"></i>Đặt lịch ngay
                        </a>
                        <a href="{{ route('profile.edit') }}" 
                           class="btn btn-outline-secondary btn-lg rounded-pill fw-semibold d-flex align-items-center justify-content-center py-3 text-decoration-none">
                            <i class="fas fa-user-edit me-2"></i>Sửa hồ sơ
                        </a>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0 pb-2">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-heart text-danger me-2"></i>Barber yêu thích
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if($favoriteBarber && $favoriteBarber->user)
                        <div class="d-flex align-items-center p-3 bg-light rounded-3">
                            @if($favoriteBarber->avatar)
                                <img src="{{ asset('legacy/images/avatars/' . $favoriteBarber->avatar) }}" 
                                     alt="{{ $favoriteBarber->user->name }}" 
                                     class="rounded-circle me-3 shadow-sm" 
                                     style="width: 70px; height: 70px; object-fit: cover; border: 3px solid #fff;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="rounded-circle me-3 d-none align-items-center justify-content-center bg-gradient-primary text-white shadow-sm" 
                                     style="width: 70px; height: 70px; font-size: 28px; font-weight: bold; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: 3px solid #fff;">
                                    {{ strtoupper(substr($favoriteBarber->user->name, 0, 1)) }}
                                </div>
                            @else
                                <div class="rounded-circle me-3 d-flex align-items-center justify-content-center bg-gradient-primary text-white shadow-sm" 
                                     style="width: 70px; height: 70px; font-size: 28px; font-weight: bold; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: 3px solid #fff;">
                                    {{ strtoupper(substr($favoriteBarber->user->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold text-dark">{{ $favoriteBarber->user->name }}</h6>
                                <small class="text-muted d-block mb-2">
                                    <i class="fas fa-user-tag me-1"></i>
                                    @if($favoriteBarber->position)
                                        {{ $favoriteBarber->position }}
                                    @elseif($favoriteBarber->level)
                                        {{ $favoriteBarber->level }}
                                    @else
                                        Barber
                                    @endif
                                </small>
                                @php
                                    $appointmentCount = $user->appointments()
                                        ->where('employee_id', $favoriteBarber->id)
                                        ->where('status', '!=', 'Đã hủy')
                                        ->count();
                                @endphp
                                <small class="text-primary fw-semibold">
                                    <i class="fas fa-calendar-check me-1"></i>{{ $appointmentCount }} lần đặt lịch
                                </small>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-user-slash fa-3x text-muted opacity-50"></i>
                            </div>
                            <p class="text-muted mb-1 fw-semibold">Chưa có barber yêu thích</p>
                            <small class="text-muted">Đặt lịch để tìm barber yêu thích của bạn</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cột nội dung chính với các tab -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                                <i class="fas fa-history me-2"></i>Lịch sử đặt lịch
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                                <i class="fas fa-user-cog me-2"></i>Thông tin cá nhân
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" type="button" role="tab">
                                <i class="fas fa-times-circle me-2"></i>Lịch sử đã hủy
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="payment-history-tab" data-bs-toggle="tab" data-bs-target="#payment-history" type="button" role="tab">
                                <i class="fas fa-receipt me-2"></i>Lịch sử thanh toán
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        
                        <!-- Tab Lịch sử đặt lịch -->
                        <div class="tab-pane fade show active" id="history" role="tabpanel">
                            <h5 class="mb-4">Các lịch hẹn sắp tới</h5>
                            <div class="row g-3">
                                @php
                                    $upcomingAppointments = $user->appointments->filter(function($appointment) {
                                        return $appointment->status != 'Hoàn thành' 
                                            && $appointment->status != 'Đã thanh toán' 
                                            && $appointment->status != 'Đã hủy'
                                            && !$appointment->trashed();
                                    })->sortBy('start_at');
                                @endphp
                                
                                @forelse($upcomingAppointments as $appointment)
                                <div class="col-12" data-appointment-id="{{ $appointment->id }}">
                                    <div class="card border shadow-sm h-100">
                                        <div class="card-body p-3">
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <!-- Dòng đầu: Tên dịch vụ -->
                                                    <h6 class="mb-2 fw-bold">
                                                        @if($appointment->appointmentDetails->count() > 0)
                                                            @foreach($appointment->appointmentDetails as $detail)
                                                                @if($detail->serviceVariant)
                                                                    {{ $detail->serviceVariant->name }}
                                                                @elseif($detail->combo)
                                                                    {{ $detail->combo->name }}
                                                                @else
                                                                    {{ $detail->notes ?? 'Dịch vụ' }}
                                                                @endif
                                                                @if(!$loop->last), @endif
                                                            @endforeach
                                                        @else
                                                            Dịch vụ
                                                        @endif
                                                    </h6>
                                                    
                                                    <!-- Dòng thứ 2: Mã đơn -->
                                                    <div class="mb-2">
                                                        @if($appointment->booking_code)
                                                            <span class="badge bg-secondary text-white" style="white-space: nowrap;">{{ $appointment->booking_code }}</span>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Dòng thứ 3: Thông tin barber và thời gian -->
                                                    <div class="d-flex flex-column gap-1 mb-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-user-tie me-1"></i>
                                                            @if($appointment->employee && $appointment->employee->user)
                                                                Barber: <strong>{{ $appointment->employee->user->name }}</strong>
                                                            @else
                                                                <span class="text-warning">Chưa phân công nhân viên</span>
                                                            @endif
                                                        </small>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar-alt me-1"></i>
                                                            @if($appointment->start_at)
                                                                <strong>{{ $appointment->start_at->format('H:i, d/m/Y') }}</strong>
                                                            @else
                                                                <span class="text-warning">Chưa có thời gian</span>
                                                            @endif
                                                        </small>
                                                    </div>
                                                    
                                                    <!-- Dòng thứ 4: Trạng thái -->
                                                    <div class="mb-2">
                                                        @php
                                                            $statusBadgeClass = 'bg-info text-white';
                                                            if ($appointment->status === 'Đã xác nhận') {
                                                                $statusBadgeClass = 'bg-success text-white';
                                                            } elseif ($appointment->status === 'Chờ xử lý') {
                                                                $statusBadgeClass = 'bg-warning text-white';
                                                            } elseif ($appointment->status === 'Đang thực hiện') {
                                                                $statusBadgeClass = 'bg-primary text-white';
                                                            }
                                                        @endphp
                                                        <span class="badge {{ $statusBadgeClass }} appointment-status-badge" data-status="{{ $appointment->status }}" style="white-space: nowrap;">{{ $appointment->status ?? 'Chờ xử lý' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                                    <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end appointment-actions" data-appointment-id="{{ $appointment->id }}">
                                                        <a href="{{ route('site.appointment.show', $appointment->id) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye me-1"></i>Xem
                                                        </a>
                                                        @php
                                                            // Chỉ hiển thị nút hủy nếu:
                                                            // 1. Status = 'Chờ xử lý'
                                                            // 2. Chưa quá 5 phút kể từ khi đặt
                                                            $canCancel = false;
                                                            if ($appointment->status === 'Chờ xử lý' && $appointment->created_at) {
                                                                $createdAt = \Carbon\Carbon::parse($appointment->created_at);
                                                                $minutesSinceCreated = $createdAt->diffInMinutes(now());
                                                                $canCancel = $minutesSinceCreated <= 5;
                                                            }
                                                        @endphp
                                                        @if($canCancel)
                                                            <button type="button" class="btn btn-sm btn-outline-danger appointment-cancel-btn" data-bs-toggle="modal" data-bs-target="#cancelModal{{ $appointment->id }}">
                                                                <i class="fas fa-times me-1"></i>Hủy
                                                            </button>
                                                            
                                                            <!-- Modal xác nhận hủy -->
                                                            <div class="modal fade" id="cancelModal{{ $appointment->id }}" tabindex="-1" aria-labelledby="cancelModalLabel{{ $appointment->id }}" aria-hidden="true">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="cancelModalLabel{{ $appointment->id }}">Xác nhận hủy lịch hẹn</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <form action="{{ route('site.appointment.cancel', $appointment->id) }}" method="POST">
                                                                            @csrf
                                                                            <div class="modal-body">
                                                                                <p>Bạn có chắc chắn muốn hủy lịch hẹn này?</p>
                                                                                <div class="mb-3">
                                                                                    <label for="cancellation_reason{{ $appointment->id }}" class="form-label">Lý do hủy (tùy chọn):</label>
                                                                                    <textarea class="form-control" id="cancellation_reason{{ $appointment->id }}" name="cancellation_reason" rows="3" placeholder="Nhập lý do hủy lịch hẹn..."></textarea>
                                                                                </div>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                                                <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12">
                                    <div class="card border text-center py-5">
                                        <div class="card-body">
                                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">Chưa có lịch hẹn sắp tới</p>
                                        </div>
                                    </div>
                                </div>
                                @endforelse
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-4">Các lịch hẹn đã hoàn thành</h5>
                            <div class="row g-3">
                                @php
                                    $completedAppointments = $user->appointments->filter(function($appointment) {
                                        return ($appointment->status == 'Hoàn thành' || $appointment->status == 'Đã thanh toán')
                                            && $appointment->status != 'Đã hủy'
                                            && !$appointment->trashed();
                                    })->sortByDesc('start_at');
                                @endphp
                                
                                @forelse($completedAppointments as $appointment)
                                    <div class="col-12">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-body p-3">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <!-- Dòng đầu: Tên dịch vụ -->
                                                        <h6 class="mb-2 fw-bold">
                                                            @if($appointment->appointmentDetails->count() > 0)
                                                                @foreach($appointment->appointmentDetails as $detail)
                                                                    @if($detail->serviceVariant)
                                                                        {{ $detail->serviceVariant->name }}
                                                                    @elseif($detail->combo)
                                                                        {{ $detail->combo->name }}
                                                                    @else
                                                                        {{ $detail->notes ?? 'Dịch vụ' }}
                                                                    @endif
                                                                    @if(!$loop->last), @endif
                                                                @endforeach
                                                            @else
                                                                Dịch vụ
                                                            @endif
                                                        </h6>
                                                        
                                                        <!-- Dòng thứ 2: Mã đơn và trạng thái (cố định, không wrap) -->
                                                        <div class="d-flex align-items-center gap-2 mb-2" style="flex-wrap: nowrap;">
                                                            @if($appointment->booking_code)
                                                                <span class="badge bg-secondary text-white" style="white-space: nowrap; flex-shrink: 0;">{{ $appointment->booking_code }}</span>
                                                            @endif
                                                            <span class="badge bg-success" style="white-space: nowrap; flex-shrink: 0;">{{ $appointment->status }}</span>
                                                        </div>
                                                        <!-- Dòng thứ 3: Thông tin barber và thời gian -->
                                                        <div class="d-flex flex-column gap-1">
                                                            <small class="text-muted">
                                                                <i class="fas fa-user-tie me-1"></i>
                                                                @if($appointment->employee && $appointment->employee->user)
                                                                    Barber: <strong>{{ $appointment->employee->user->name }}</strong>
                                                                @endif
                                                            </small>
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar-alt me-1"></i>
                                                                @if($appointment->start_at)
                                                                    <strong>{{ $appointment->start_at->format('H:i, d/m/Y') }}</strong>
                                                                @endif
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                                                            <a href="{{ route('site.appointment.show', $appointment->id) }}" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye me-1"></i>Xem
                                                            </a>
                                                            @php
                                                                $existingReview = $appointment->reviews->where('user_id', $user->id)->first();
                                                            @endphp
                                                            @if($existingReview)
                                                                <a href="{{ route('site.reviews.edit', $existingReview->id) }}" class="btn btn-sm btn-outline-warning">
                                                                    <i class="fas fa-star me-1"></i>Xem đánh giá
                                                                </a>
                                                            @else
                                                                <a href="{{ route('site.reviews.create', ['appointment_id' => $appointment->id]) }}" class="btn btn-sm btn-outline-success">
                                                                    <i class="fas fa-star me-1"></i>Đánh giá
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                <div class="col-12">
                                    <div class="card border text-center py-5">
                                        <div class="card-body">
                                            <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">Chưa có lịch hẹn đã hoàn thành</p>
                                        </div>
                                    </div>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Tab Lịch sử đã hủy -->
                        <div class="tab-pane fade" id="cancelled" role="tabpanel">
                            <h5 class="mb-4">Các lịch hẹn đã hủy</h5>
                            <div class="row g-3">
                                @php
                                    $cancelledAppointments = $user->appointments->filter(function($appointment) {
                                        return $appointment->status === 'Đã hủy'
                                            && !$appointment->trashed();
                                    })->sortByDesc('created_at');
                                @endphp
                                
                                @forelse($cancelledAppointments as $appointment)
                                    <div class="col-12">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-body p-3">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <!-- Dòng đầu: Tên dịch vụ -->
                                                        <h6 class="mb-2 fw-bold">
                                                            @if($appointment->appointmentDetails->count() > 0)
                                                                @foreach($appointment->appointmentDetails as $detail)
                                                                    @if($detail->serviceVariant)
                                                                        {{ $detail->serviceVariant->name }}
                                                                    @elseif($detail->combo)
                                                                        {{ $detail->combo->name }}
                                                                    @else
                                                                        {{ $detail->notes ?? 'Dịch vụ' }}
                                                                    @endif
                                                                    @if(!$loop->last), @endif
                                                                @endforeach
                                                            @else
                                                                Dịch vụ
                                                            @endif
                                                        </h6>
                                                        
                                                        <!-- Dòng thứ 2: Mã đơn và trạng thái (cố định, không wrap) -->
                                                        <div class="d-flex align-items-center gap-2 mb-2" style="flex-wrap: nowrap;">
                                                            @if($appointment->booking_code)
                                                                <span class="badge bg-secondary text-white" style="white-space: nowrap; flex-shrink: 0;">{{ $appointment->booking_code }}</span>
                                                            @endif
                                                            <span class="badge bg-danger text-white" style="white-space: nowrap; flex-shrink: 0;">Đã hủy</span>
                                                        </div>
                                                        <!-- Dòng thứ 3: Thông tin barber và thời gian -->
                                                        <div class="d-flex flex-column gap-1">
                                                            <small class="text-muted">
                                                                <i class="fas fa-user-tie me-1"></i>
                                                                @if($appointment->employee && $appointment->employee->user)
                                                                    Barber: <strong>{{ $appointment->employee->user->name }}</strong>
                                                                @else
                                                                    <span class="text-warning">Chưa phân công nhân viên</span>
                                                                @endif
                                                            </small>
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar-alt me-1"></i>
                                                                @if($appointment->start_at)
                                                                    <strong>{{ $appointment->start_at->format('H:i, d/m/Y') }}</strong>
                                                                @endif
                                                            </small>
                                                            @if($appointment->cancellation_reason)
                                                                <small class="text-muted mt-1">
                                                                    <i class="fas fa-info-circle me-1"></i>Lý do hủy: <em>{{ $appointment->cancellation_reason }}</em>
                                                                </small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                                        <a href="{{ route('site.appointment.show', $appointment->id) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye me-1"></i>Xem
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                <div class="col-12">
                                    <div class="card border text-center py-5">
                                        <div class="card-body">
                                            <i class="fas fa-ban fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">Chưa có lịch hẹn nào đã hủy</p>
                                        </div>
                                    </div>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Tab Thông tin cá nhân -->
                        <div class="tab-pane fade" id="profile" role="tabpanel">
                            <h5 class="mb-4" id="thong-tin-ca-nhan">Thông tin chi tiết</h5>
                            <div class="row mb-3">
                                <div class="col-sm-3"><p class="text-muted mb-0">Họ và tên</p></div>
                                <div class="col-sm-9"><p class="fw-bold mb-0">{{ $user->name }}</p></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><p class="text-muted mb-0">Email</p></div>
                                <div class="col-sm-9"><p class="fw-bold mb-0">{{ $user->email }}</p></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><p class="text-muted mb-0">Số điện thoại</p></div>
                                <div class="col-sm-9"><p class="fw-bold mb-0">{{ $user->phone }}</p></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><p class="text-muted mb-0">Ngày sinh</p></div>
                                <div class="col-sm-9"><p class="fw-bold mb-0">{{ $user->dob ? $user->dob->format('d/m/Y') : 'Chưa cập nhật' }}</p></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3"><p class="text-muted mb-0">Địa chỉ</p></div>
                                <div class="col-sm-9"><p class="fw-bold mb-0"></p></div>
                            </div>
                        </div>

                        <!-- Tab Lịch sử thanh toán -->
                        <div class="tab-pane fade" id="payment-history" role="tabpanel">
                            <h5 class="mb-4">Lịch sử thanh toán</h5>
                            <div class="list-group">
                                @forelse($user->payments as $payment)
                                    <div class="list-group-item mb-3">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Hóa đơn: <strong>{{ $payment->invoice_code }}</strong></h6>
                                            <small class="text-muted">{{ $payment->created_at ? $payment->created_at->format('H:i d/m/Y') : 'N/A' }}</small>
                                        </div>
                                        <p class="mb-1">Tổng tiền: <strong class="text-danger">{{ number_format($payment->total) }}đ</strong></p>
                                        <div class="d-flex justify-content-between">
                                            <p class="mb-1"><small>Phương thức: {{ $payment->payment_type }}</small></p>
                                            @php
                                                $status = $payment->status ?? 'pending';
                                                $badgeClass = 'bg-secondary';
                                                $statusText = 'Chờ xử lý';
                                                
                                                if ($status == 'completed') {
                                                    $badgeClass = 'bg-success';
                                                    $statusText = 'Thành công';
                                                } elseif ($status == 'failed') {
                                                    $badgeClass = 'bg-danger';
                                                    $statusText = 'Thất bại';
                                                } elseif ($status == 'refunded') {
                                                    $badgeClass = 'bg-warning';
                                                    $statusText = 'Hoàn tiền';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                                        </div>
                                        
                                        @php
                                            $appliedPromo = null;
                                            if ($payment->appointment_id) {
                                                foreach ($user->promotionUsages as $usage) {
                                                    if ($usage->appointment_id == $payment->appointment_id) {
                                                        $appliedPromo = $usage->promotion; // Assuming promotion relation is loaded on PromotionUsage
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp

                                        @if ($appliedPromo)
                                            <p class="mb-0 text-success">
                                                <small>
                                                    <i class="fas fa-tag me-1"></i>Mã KM: <strong>{{ $appliedPromo->code }}</strong> (-{{ $appliedPromo->discount_percent }}%)
                                                </small>
                                            </p>
                                        @endif
                                    </div>
                                @empty
                                    <div class="alert alert-info">Chưa có lịch sử thanh toán nào.</div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Đảm bảo Bootstrap JS được tải
    var tabEl = document.querySelector('button[data-bs-toggle="tab"]')
    if (tabEl) {
        tabEl.addEventListener('show.bs.tab', function (event) {
          // event.target // newly activated tab
          // event.relatedTarget // previous active tab
        })
    }

    // Tự động cập nhật trạng thái lịch hẹn
    (function() {
        const userId = {{ $user->id }};
        let updateInterval = null;
        let lastStatuses = {}; // Lưu trạng thái cuối cùng để so sánh

        function updateAppointmentStatus() {
            const url = `{{ route('site.customers.appointments-status', $user->id) }}`;
            console.log('[Polling] Fetching appointment status from:', url);
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                cache: 'no-cache'
            })
                .then(response => {
                    console.log('[Polling] Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('[Polling] Appointment status update:', data);
                    if (data.success && data.appointments) {
                        console.log('[Polling] Found', data.appointments.length, 'appointments');
                        
                        // Cập nhật lastStatuses trước khi xử lý
                        data.appointments.forEach(function(appointment) {
                            lastStatuses[appointment.id] = appointment.status;
                        });
                        
                        data.appointments.forEach(function(appointment) {
                            // Đảm bảo ID là string để so sánh
                            const appointmentId = String(appointment.id);
                            console.log('[Polling] Processing appointment:', appointmentId, 'Status:', appointment.status);
                            
                            // Tìm element với data-appointment-id
                            const appointmentElement = document.querySelector(`[data-appointment-id="${appointmentId}"]`);
                            if (!appointmentElement) {
                                console.warn('[Polling] Appointment element not found for ID:', appointmentId);
                                // Thử tìm lại với tất cả elements
                                const allElements = document.querySelectorAll('[data-appointment-id]');
                                console.log('[Polling] Available appointment IDs in DOM:', Array.from(allElements).map(el => el.getAttribute('data-appointment-id')));
                                return;
                            }

                            const statusBadge = appointmentElement.querySelector('.appointment-status-badge');
                            const actionsContainer = appointmentElement.querySelector('.appointment-actions');
                            
                            if (!statusBadge) {
                                console.warn('[Polling] Status badge not found for appointment:', appointment.id);
                                return;
                            }
                            
                            const currentStatus = statusBadge.getAttribute('data-status') || statusBadge.textContent.trim();
                            const newStatus = appointment.status;
                            console.log('[Polling] Appointment', appointment.id, '- Current:', currentStatus, 'New:', newStatus);
                            
                            // Chỉ cập nhật nếu trạng thái thay đổi
                            if (currentStatus !== newStatus) {
                                console.log('[Polling] ⚠️ STATUS CHANGED! Updating appointment', appointment.id, 'from', currentStatus, 'to', newStatus);
                                
                                // Cập nhật badge text
                                statusBadge.textContent = newStatus;
                                statusBadge.setAttribute('data-status', newStatus);
                                
                                // Cập nhật class badge
                                statusBadge.className = 'badge ms-2 appointment-status-badge';
                                if (newStatus === 'Đã xác nhận') {
                                    statusBadge.classList.add('bg-success');
                                } else if (newStatus === 'Chờ xử lý') {
                                    statusBadge.classList.add('bg-warning');
                                } else if (newStatus === 'Đang thực hiện') {
                                    statusBadge.classList.add('bg-primary');
                                } else if (newStatus === 'Chưa thanh toán') {
                                    statusBadge.classList.add('bg-danger');
                                } else if (newStatus === 'Đã thanh toán') {
                                    statusBadge.classList.add('bg-success');
                                } else {
                                    statusBadge.classList.add('bg-info');
                                }

                                // Cập nhật nút hủy
                                if (actionsContainer) {
                                    const cancelBtn = actionsContainer.querySelector('.appointment-cancel-btn');
                                    if (newStatus === 'Đã xác nhận' || !appointment.can_cancel || newStatus === 'Đã thanh toán' || newStatus === 'Chưa thanh toán') {
                                        // Ẩn nút hủy nếu đã xác nhận hoặc không thể hủy
                                        if (cancelBtn) {
                                            console.log('[Polling] Removing cancel button for appointment:', appointment.id);
                                            cancelBtn.remove();
                                        }
                                    }
                                }
                                
                                // Hiển thị thông báo trạng thái đã thay đổi
                                showStatusChangeNotification(currentStatus, newStatus);
                                
                                console.log('[Polling] ✅ Status updated successfully for appointment:', appointment.id);
                            } else {
                                console.log('[Polling] Status unchanged for appointment:', appointment.id);
                            }
                        });
                    } else {
                        console.warn('[Polling] No appointments or invalid response:', data);
                    }
                })
                .catch(error => {
                    console.error('[Polling] ❌ Error updating appointment status:', error);
                });
        }

        // Chạy polling cho tất cả lịch hẹn sắp tới
        // Đợi DOM ready trước khi khởi tạo polling
        function initPolling() {
            const allAppointments = document.querySelectorAll('.appointment-status-badge');
            console.log('[Polling] Initializing... Total appointments found:', allAppointments.length);
            
            // Khởi tạo lastStatuses từ DOM
            allAppointments.forEach(function(badge) {
                const appointmentElement = badge.closest('[data-appointment-id]');
                if (appointmentElement) {
                    const appointmentId = String(appointmentElement.getAttribute('data-appointment-id'));
                    const currentStatus = badge.getAttribute('data-status') || badge.textContent.trim();
                    lastStatuses[appointmentId] = currentStatus;
                    console.log('[Polling] Initial status for appointment', appointmentId, ':', currentStatus);
                }
            });
            
            // Luôn chạy polling nếu có lịch hẹn sắp tới
            if (allAppointments.length > 0) {
                console.log('[Polling] ✅ Starting appointment status polling for', allAppointments.length, 'appointments...');
                // Cập nhật ngay lập tức
                updateAppointmentStatus();
                
                // Cập nhật mỗi 3 giây để phát hiện thay đổi trạng thái từ admin nhanh hơn
                updateInterval = setInterval(function() {
                    console.log('[Polling] Running scheduled update...');
                    updateAppointmentStatus();
                }, 3000);
                
                // Dừng polling sau 2 giờ (để tránh polling vô hạn, nhưng đủ lâu để theo dõi)
                setTimeout(function() {
                    if (updateInterval) {
                        clearInterval(updateInterval);
                        console.log('[Polling] ⏹️ Stopped appointment status polling after 2 hours');
                    }
                }, 7200000); // 2 giờ
            } else {
                console.log('[Polling] ⚠️ No appointments found, skipping polling');
            }
        }

        // Khởi tạo khi DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPolling);
        } else {
            // DOM đã sẵn sàng
            initPolling();
        }
        
        // Hàm hiển thị thông báo khi trạng thái thay đổi
        function showStatusChangeNotification(oldStatus, newStatus) {
            const statusMessages = {
                'Chờ xử lý': 'đang chờ xử lý',
                'Đã xác nhận': 'đã được xác nhận',
                'Đang thực hiện': 'đang được thực hiện',
                'Hoàn thành': 'đã hoàn thành',
                'Đã thanh toán': 'đã thanh toán',
                'Chưa thanh toán': 'chưa thanh toán',
                'Đã hủy': 'đã bị hủy'
            };
            
            const oldStatusText = statusMessages[oldStatus] || oldStatus;
            const newStatusText = statusMessages[newStatus] || newStatus;
            
            // Tạo thông báo
            const message = `Trạng thái lịch hẹn đã thay đổi từ "${oldStatus}" sang "${newStatus}"`;
            
            // Kiểm tra xem có toastr không
            if (typeof toastr !== 'undefined') {
                toastr.success(message, 'Thông báo', {
                    timeOut: 5000,
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right'
                });
            } else {
                // Fallback: sử dụng Bootstrap toast
                showBootstrapToast(message, newStatus);
            }
        }
        
        // Hàm hiển thị Bootstrap toast
        function showBootstrapToast(message, status) {
            // Tạo toast container nếu chưa có
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }
            
            // Tạo toast element
            const toastId = 'toast-' + Date.now();
            const toastHtml = `
                <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header ${getStatusColorClass(status)}">
                        <i class="fa fa-bell me-2"></i>
                        <strong class="me-auto">Thông báo</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            // Hiển thị toast
            const toastElement = document.getElementById(toastId);
            if (toastElement && typeof bootstrap !== 'undefined') {
                const toast = new bootstrap.Toast(toastElement, {
                    autohide: true,
                    delay: 5000
                });
                toast.show();
                
                // Xóa toast element sau khi ẩn
                toastElement.addEventListener('hidden.bs.toast', function() {
                    toastElement.remove();
                });
            }
        }
        
        // Hàm lấy class màu theo trạng thái
        function getStatusColorClass(status) {
            if (status === 'Đã xác nhận') {
                return 'bg-success text-white';
            } else if (status === 'Chờ xử lý') {
                return 'bg-warning text-dark';
            } else if (status === 'Đang thực hiện') {
                return 'bg-primary text-white';
            } else if (status === 'Hoàn thành' || status === 'Đã thanh toán') {
                return 'bg-success text-white';
            } else if (status === 'Đã hủy') {
                return 'bg-danger text-white';
            }
            return 'bg-info text-white';
        }
    })();
</script>
@endsection
