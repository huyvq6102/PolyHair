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
                    
                    <h4 class="mb-2 fw-bold text-dark">{{ $user->name }}</h4>
                    {{-- Hạng thành viên --}}
                    <div class="mb-3">
                        @php
                            $tier = $user->tier;
                            // Màu sắc nổi bật cho từng hạng
                            $tierBadgeClass = 'bg-secondary text-white'; // Khách thường
                            if ($tier === 'Silver') {
                                $tierBadgeClass = 'bg-primary text-white';
                            } elseif ($tier === 'Gold') {
                                $tierBadgeClass = 'bg-warning text-dark';
                            } elseif ($tier === 'VIP') {
                                $tierBadgeClass = 'bg-danger text-white';
                            }

                            // Ngưỡng chi tiêu cho từng hạng
                            $thresholds = [
                                'Khách thường' => 0,
                                'Silver' => 2_000_000,
                                'Gold' => 5_000_000,
                                'VIP' => 10_000_000,
                            ];

                            // Xác định hạng tiếp theo và số tiền cần thêm
                            $nextTierName = null;
                            $nextTierThreshold = null;
                            if ($tier === 'Khách thường') {
                                $nextTierName = 'Silver';
                                $nextTierThreshold = $thresholds['Silver'];
                            } elseif ($tier === 'Silver') {
                                $nextTierName = 'Gold';
                                $nextTierThreshold = $thresholds['Gold'];
                            } elseif ($tier === 'Gold') {
                                $nextTierName = 'VIP';
                                $nextTierThreshold = $thresholds['VIP'];
                            }

                            $amountToNext = $nextTierThreshold
                                ? max(0, $nextTierThreshold - $user->total_spent)
                                : 0;
                        @endphp
                        <span class="badge {{ $tierBadgeClass }} px-3 py-2" style="font-size: 0.85rem; text-transform: uppercase;">
                            Hạng: {{ $tier }}
                        </span>
                        <div class="mt-2">
                            <small class="text-muted">
                                Tổng chi tiêu: <strong>{{ number_format($user->total_spent) }}đ</strong>
                            </small>
                            @if($nextTierName && $amountToNext > 0)
                                <div class="mt-1">
                                    <small class="text-muted">
                                        ➡️ Còn <strong>{{ number_format($amountToNext) }}đ</strong> nữa để lên hạng <strong>{{ strtoupper($nextTierName) }}</strong>
                                    </small>
                                </div>
                            @elseif($tier === 'VIP')
                                <div class="mt-1">
                                    <small class="text-muted">
                                        🎉 Bạn đang ở hạng cao nhất (VIP).
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>

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
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                                <i class="fas fa-user-cog me-2"></i>Thông tin cá nhân
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                                <i class="fas fa-history me-2"></i>Lịch sử đặt lịch
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
                        
                        <!-- Tab Thông tin cá nhân -->
                        <div class="tab-pane fade show active" id="profile" role="tabpanel">
                            <h5 class="mb-4" id="thong-tin-ca-nhan">Thông tin chi tiết</h5>
                            <div class="row mb-3">
                                <div class="col-sm-3"><p class="text-muted mb-0">Họ và tên</p></div>
                                <div class="col-sm-9"><p class="fw-bold mb-0" style="font-weight: 700 !important; color: #212529;">{{ $user->name }}</p></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><p class="text-muted mb-0">Hạng thành viên</p></div>
                                <div class="col-sm-9">
                                    <p class="fw-bold mb-0" style="font-weight: 700 !important; color: #212529;">
                                        {{ $user->tier }}
                                        <span class="text-muted" style="font-size: 0.85rem;">
                                            (Tổng chi tiêu: {{ number_format($user->total_spent) }}đ)
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><p class="text-muted mb-0">Email</p></div>
                                <div class="col-sm-9"><p class="fw-bold mb-0" style="font-weight: 700 !important; color: #212529;">{{ $user->email }}</p></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><p class="text-muted mb-0">Số điện thoại</p></div>
                                <div class="col-sm-9"><p class="fw-bold mb-0" style="font-weight: 700 !important; color: #212529;">{{ $user->phone }}</p></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><p class="text-muted mb-0">Ngày sinh</p></div>
                                <div class="col-sm-9"><p class="fw-bold mb-0" style="font-weight: 700 !important; color: #212529;">{{ $user->dob ? $user->dob->format('d/m/Y') : 'Chưa cập nhật' }}</p></div>
                            </div>
                            <hr>
                            
                            {{-- Thông tin chương trình khách hàng thân thiết --}}
                            <div class="mt-4">
                                <h6 class="fw-bold mb-2">🎖️ Chương trình khách hàng thân thiết</h6>
                                <p class="mb-2" style="font-size: 0.9rem;">
                                    Hệ thống phân hạng khách hàng dựa trên tổng chi tiêu sau khi thanh toán tại cửa hàng.
                                </p>
                                <ul class="mb-2" style="font-size: 0.9rem; padding-left: 1.2rem;">
                                    <li>Khách thường: <strong>&lt; 2.000.000đ</strong></li>
                                    <li>Silver: <strong>&ge; 2.000.000đ</strong></li>
                                    <li>Gold: <strong>&ge; 5.000.000đ</strong></li>
                                    <li>VIP: <strong>&ge; 10.000.000đ</strong></li>
                                </ul>
                                <p class="mb-0 fw-bold" style="font-size: 0.9rem; font-weight: 700 !important; color: #c89c5c;">
                                    Ưu đãi sẽ được áp dụng khi thanh toán tại cửa hàng.
                                </p>
                            </div>
                        </div>

                        <!-- Tab Lịch sử đặt lịch -->
                        <div class="tab-pane fade" id="history" role="tabpanel">
                            <h5 class="mb-4">Các lịch hẹn sắp tới</h5>
                            
                            <!-- Filter by Status -->
                            @php
                                // Danh sách các trạng thái theo thứ tự: Chờ xử lý -> Đã xác nhận -> Đang thực hiện -> Hoàn thành -> Đã hủy
                                // Luôn hiển thị tất cả các trạng thái này, không phụ thuộc vào dữ liệu
                                $allStatuses = collect([
                                    'Chờ xử lý',
                                    'Đã xác nhận',
                                    'Đang thực hiện',
                                    'Hoàn thành',
                                    'Đã hủy'
                                ]);
                                
                                // Lấy tất cả appointments để filter (bao gồm cả đã hủy)
                                $allAppointmentsForFilter = $user->appointments->filter(function($appointment) {
                                    return !$appointment->trashed();
                                })->sortByDesc('start_at');
                            @endphp
                            
                            @if($allStatuses->count() > 0)
                            <div class="mb-4">
                                <div class="d-flex flex-wrap status-filter-buttons" style="gap: 1.5rem;">
                                    <button type="button" class="btn btn-sm btn-outline-primary status-filter-btn active" data-status="all" style="margin-right: 0.5rem;">
                                        <i class="fas fa-list me-1"></i>Tất cả
                                    </button>
                                    @foreach($allStatuses as $status)
                                        @php
                                            $statusClass = 'btn-outline-secondary';
                                            if ($status === 'Đã xác nhận') {
                                                $statusClass = 'btn-outline-success';
                                            } elseif ($status === 'Chờ xử lý') {
                                                $statusClass = 'btn-outline-warning';
                                            } elseif ($status === 'Đang thực hiện') {
                                                $statusClass = 'btn-outline-info';
                                            } elseif ($status === 'Hoàn thành') {
                                                $statusClass = 'btn-outline-success';
                                            } elseif ($status === 'Đã hủy') {
                                                $statusClass = 'btn-outline-danger';
                                            }
                                        @endphp
                                        <button type="button" class="btn btn-sm {{ $statusClass }} status-filter-btn" data-status="{{ $status }}" style="margin-right: 0.5rem;">
                                            {{ $status }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            <div class="row g-3" id="appointments-list">
                                @forelse($allAppointmentsForFilter as $appointment)
                                <div class="col-12 appointment-item" data-appointment-id="{{ $appointment->id }}" data-appointment-status="{{ $appointment->status ?? 'Chờ xử lý' }}">
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
                                                            } elseif ($appointment->status === 'Hoàn thành') {
                                                                $statusBadgeClass = 'bg-success text-white';
                                                            } elseif ($appointment->status === 'Đã hủy') {
                                                                $statusBadgeClass = 'bg-danger text-white';
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
                                                            // 2. Chưa quá 30 phút kể từ khi đặt
                                                            $canCancel = false;
                                                            if ($appointment->status === 'Chờ xử lý' && $appointment->created_at) {
                                                                $createdAt = \Carbon\Carbon::parse($appointment->created_at);
                                                                $minutesSinceCreated = $createdAt->diffInMinutes(now());
                                                                $canCancel = $minutesSinceCreated <= 30;
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
                                                                        <form action="{{ route('site.appointment.cancel', $appointment->id) }}" method="POST" id="cancelForm{{ $appointment->id }}">
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
                                                        
                                                        @if($appointment->status === 'Hoàn thành')
                                                            @php
                                                                // Kiểm tra xem đã đánh giá chưa
                                                                $hasReviewed = \App\Models\Review::where('appointment_id', $appointment->id)
                                                                    ->where('user_id', auth()->id())
                                                                    ->exists();
                                                            @endphp
                                                            @if(!$hasReviewed)
                                                                <a href="{{ route('site.reviews.create', ['appointment_id' => $appointment->id]) }}" class="btn btn-sm btn-outline-warning">
                                                                    <i class="fas fa-star me-1"></i>Đánh giá
                                                                </a>
                                                            @else
                                                                <span class="btn btn-sm btn-success disabled">
                                                                    <i class="fas fa-check me-1"></i>Đã đánh giá
                                                                </span>
                                                            @endif
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

@push('scripts')
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script>
    // Filter appointments by status - Isolated to prevent interference from other scripts
    (function() {
        'use strict';
        
        try {
            let filterInitialized = false;
            
            function handleFilterClick(e) {
                try {
                    const button = e.target.closest('.status-filter-btn');
                    if (!button) return;
                    
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const selectedStatus = button.getAttribute('data-status');
                    if (!selectedStatus) return;
                    
                    console.log('[Filter] Button clicked:', selectedStatus);
                    
                    // Remove active class from all buttons
                    document.querySelectorAll('.status-filter-btn').forEach(function(btn) {
                        btn.classList.remove('active');
                    });
                    
                    // Add active class to clicked button
                    button.classList.add('active');
                    
                    // Get all appointment items
                    const appointmentItems = document.querySelectorAll('.appointment-item');
                    console.log('[Filter] Total appointments:', appointmentItems.length);
                    
                    if (appointmentItems.length === 0) {
                        console.warn('[Filter] No appointment items found');
                        return;
                    }
                    
                    let visibleCount = 0;
                    
                    // Filter appointments
                    appointmentItems.forEach(function(item) {
                        const itemStatus = item.getAttribute('data-appointment-status');
                        
                        if (selectedStatus === 'all' || itemStatus === selectedStatus) {
                            item.style.display = '';
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });
                    
                    console.log('[Filter] Visible count:', visibleCount);
                    
                    // Show/hide no results message
                    const appointmentsContainer = document.getElementById('appointments-list');
                    let noResultsMsg = document.getElementById('no-results-message');
                    
                    if (visibleCount === 0 && appointmentsContainer) {
                        if (!noResultsMsg) {
                            noResultsMsg = document.createElement('div');
                            noResultsMsg.id = 'no-results-message';
                            noResultsMsg.className = 'col-12';
                            noResultsMsg.innerHTML = `
                                <div class="card border text-center py-5">
                                    <div class="card-body">
                                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">Không có lịch hẹn nào với trạng thái "${selectedStatus === 'all' ? 'Tất cả' : selectedStatus}"</p>
                                    </div>
                                </div>
                            `;
                            appointmentsContainer.appendChild(noResultsMsg);
                        } else {
                            const pTag = noResultsMsg.querySelector('p');
                            if (pTag) {
                                pTag.textContent = `Không có lịch hẹn nào với trạng thái "${selectedStatus === 'all' ? 'Tất cả' : selectedStatus}"`;
                            }
                            noResultsMsg.style.display = '';
                        }
                    } else {
                        if (noResultsMsg) {
                            noResultsMsg.style.display = 'none';
                        }
                    }
                } catch (error) {
                    console.error('[Filter] Error in handleFilterClick:', error);
                }
            }
            
            function initFilter() {
                if (filterInitialized) {
                    return;
                }
                
                try {
                    // Use event delegation on document with capture phase
                    document.addEventListener('click', handleFilterClick, true);
                    filterInitialized = true;
                    console.log('[Filter] Initialized successfully');
                } catch (error) {
                    console.error('[Filter] Error initializing:', error);
                }
            }
            
            // Initialize when DOM is ready
            function startFilter() {
                try {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initFilter);
                    } else {
                        // DOM already ready, initialize immediately
                        setTimeout(initFilter, 100);
                    }
                } catch (error) {
                    console.error('[Filter] Error in startFilter:', error);
                }
            }
            
            // Start the filter
            startFilter();
            
        } catch (error) {
            console.error('[Filter] Critical error:', error);
        }
    })();

    // Đảm bảo Bootstrap JS được tải
    var tabEl = document.querySelector('button[data-bs-toggle="tab"]')
    if (tabEl) {
        tabEl.addEventListener('show.bs.tab', function (event) {
          // event.target // newly activated tab
          // event.relatedTarget // previous active tab
        })
    }

    // Real-time update với Pusher + Polling fallback
    (function() {
        const userId = {{ $user->id }};
        let updateInterval = null;
        let lastStatuses = {}; // Lưu trạng thái cuối cùng để so sánh
        let pusherChannels = {}; // Lưu các Pusher channels đã subscribe

        // Khởi tạo Pusher cho real-time updates
        function initPusher() {
            const pusherKey = '{{ config("broadcasting.connections.pusher.key", env("PUSHER_APP_KEY")) }}';
            const pusherCluster = '{{ config("broadcasting.connections.pusher.options.cluster", env("PUSHER_APP_CLUSTER", "ap1")) }}';
            
            if (!pusherKey || typeof Pusher === 'undefined') {
                console.warn('[Pusher] Pusher not configured, using polling only');
                return null;
            }
            
            try {
                const pusher = new Pusher(pusherKey, {
                    cluster: pusherCluster,
                    encrypted: true,
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    }
                });
                
                // Subscribe cho từng appointment
                const appointmentElements = document.querySelectorAll('[data-appointment-id]');
                appointmentElements.forEach(function(element) {
                    const appointmentId = element.getAttribute('data-appointment-id');
                    if (appointmentId && !pusherChannels[appointmentId]) {
                        const channel = pusher.subscribe('private-appointment.' + appointmentId);
                        pusherChannels[appointmentId] = channel;
                        
                        channel.bind('status.updated', function(data) {
                            console.log('[Pusher] Status updated for appointment', appointmentId, ':', data.status);
                            console.log('[Pusher] Event data:', data);
                            
                            // Cập nhật status trong DOM
                            const updated = updateAppointmentStatusInDOM(appointmentId, data.status);
                            
                            // Nếu appointment không có trong DOM (có thể bị filter), chuyển tab và thử lại
                            if (!updated) {
                                console.log('[Pusher] Appointment not found in DOM, switching to correct filter tab');
                                // Chuyển sang tab filter tương ứng với trạng thái mới
                                switchToStatusFilterTab(data.status);
                                
                                // Sau khi chuyển tab, thử cập nhật lại sau 500ms
                                setTimeout(function() {
                                    const retryUpdated = updateAppointmentStatusInDOM(appointmentId, data.status);
                                    if (!retryUpdated) {
                                        console.log('[Pusher] Still not found after switching tab, reloading page');
                                        window.location.reload();
                                    }
                                }, 500);
                            }
                        });
                        
                        console.log('[Pusher] Subscribed to appointment', appointmentId);
                    }
                });
                
                return pusher;
            } catch (error) {
                console.error('[Pusher] Error initializing:', error);
                return null;
            }
        }
        
        // Cập nhật status trong DOM
        function updateAppointmentStatusInDOM(appointmentId, newStatus) {
            const appointmentElement = document.querySelector(`[data-appointment-id="${appointmentId}"]`);
            if (!appointmentElement) {
                console.warn('[Update] Appointment element not found:', appointmentId);
                // Trả về false để báo rằng không tìm thấy element
                return false;
            }
            
            const statusBadge = appointmentElement.querySelector('.appointment-status-badge');
            if (!statusBadge) {
                console.warn('[Update] Status badge not found for appointment:', appointmentId);
                return;
            }
            
            const currentStatus = statusBadge.getAttribute('data-status') || statusBadge.textContent.trim();
            
            if (currentStatus !== newStatus) {
                console.log('[Update] ⚠️ STATUS CHANGED! Updating appointment', appointmentId, 'from', currentStatus, 'to', newStatus);
                
                // Cập nhật badge
                statusBadge.textContent = newStatus;
                statusBadge.setAttribute('data-status', newStatus);
                appointmentElement.setAttribute('data-appointment-status', newStatus);
                
                // Cập nhật class badge
                statusBadge.className = 'badge ms-2 appointment-status-badge';
                if (newStatus === 'Đã xác nhận') {
                    statusBadge.classList.add('bg-success');
                } else if (newStatus === 'Chờ xử lý') {
                    statusBadge.classList.add('bg-warning');
                } else if (newStatus === 'Đang thực hiện') {
                    statusBadge.classList.add('bg-primary');
                } else if (newStatus === 'Hoàn thành') {
                    statusBadge.classList.add('bg-success');
                } else if (newStatus === 'Đã hủy') {
                    statusBadge.classList.add('bg-danger');
                } else if (newStatus === 'Chưa thanh toán') {
                    statusBadge.classList.add('bg-danger');
                } else if (newStatus === 'Đã thanh toán') {
                    statusBadge.classList.add('bg-success');
                } else {
                    statusBadge.classList.add('bg-info');
                }
                
                // Cập nhật nút hủy
                const actionsContainer = appointmentElement.querySelector('.appointment-actions');
                if (actionsContainer) {
                    const cancelBtn = actionsContainer.querySelector('.appointment-cancel-btn');
                    if (newStatus !== 'Chờ xử lý' && cancelBtn) {
                        cancelBtn.remove();
                    }
                }
                
                // Hiển thị thông báo
                showStatusChangeNotification(currentStatus, newStatus);
                
                // Tự động chuyển sang tab filter tương ứng với trạng thái mới
                switchToStatusFilterTab(newStatus);
                
                console.log('[Update] ✅ Status updated successfully for appointment:', appointmentId);
                return true; // Trả về true nếu cập nhật thành công
            }
            return true; // Trả về true nếu status không thay đổi (đã đúng rồi)
        }
        
        // Hàm tự động chuyển sang tab filter tương ứng với trạng thái
        function switchToStatusFilterTab(status) {
            try {
                console.log('[Filter] Attempting to switch to status tab:', status);
                
                // Tìm button filter có data-status tương ứng
                const filterButton = document.querySelector(`.status-filter-btn[data-status="${status}"]`);
                
                if (filterButton) {
                    // Kiểm tra xem button đã active chưa
                    const isAlreadyActive = filterButton.classList.contains('active');
                    
                    if (!isAlreadyActive) {
                        console.log('[Filter] Auto-switching to status tab:', status);
                        
                        // Remove active class from all buttons
                        document.querySelectorAll('.status-filter-btn').forEach(function(btn) {
                            btn.classList.remove('active');
                        });
                        
                        // Add active class to target button
                        filterButton.classList.add('active');
                        
                        // Trigger filter click để cập nhật danh sách appointments
                        // Sử dụng click() thay vì dispatchEvent để đảm bảo event handler được gọi
                        filterButton.click();
                        
                        console.log('[Filter] ✅ Switched to status tab:', status);
                    } else {
                        console.log('[Filter] Tab already active, just refreshing filter');
                        // Nếu đã active, chỉ cần refresh filter
                        filterButton.click();
                    }
                } else {
                    console.warn('[Filter] Filter button not found for status:', status);
                    // Nếu không tìm thấy tab cụ thể, chuyển sang "Tất cả"
                    const allButton = document.querySelector('.status-filter-btn[data-status="all"]');
                    if (allButton) {
                        allButton.click();
                    }
                }
            } catch (error) {
                console.error('[Filter] Error switching to status tab:', error);
            }
        }

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
                            
                            // Sử dụng hàm chung để cập nhật
                            updateAppointmentStatusInDOM(appointmentId, newStatus);
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
            
            // Khởi tạo Pusher cho real-time updates
            initPusher();
            
            // Luôn chạy polling nếu có lịch hẹn sắp tới (fallback nếu Pusher không hoạt động)
            if (allAppointments.length > 0) {
                console.log('[Polling] ✅ Starting appointment status polling (fallback) for', allAppointments.length, 'appointments...');
                // Cập nhật ngay lập tức
                updateAppointmentStatus();
                
                // Cập nhật mỗi 10 giây (giảm tần suất vì đã có Pusher)
                updateInterval = setInterval(function() {
                    console.log('[Polling] Running scheduled update (fallback)...');
                    updateAppointmentStatus();
                }, 10000); // Tăng lên 10 giây vì đã có Pusher
                
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

    // Refresh CSRF token khi mở modal hủy lịch để tránh lỗi 419
    document.addEventListener('DOMContentLoaded', function() {
        // Lắng nghe sự kiện khi modal được mở
        document.querySelectorAll('[id^="cancelModal"]').forEach(function(modal) {
            modal.addEventListener('show.bs.modal', function() {
                // Lấy form trong modal
                const form = modal.querySelector('form');
                if (form) {
                    // Lấy CSRF token mới từ meta tag
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        const tokenInput = form.querySelector('input[name="_token"]');
                        if (tokenInput) {
                            tokenInput.value = csrfToken.getAttribute('content');
                        }
                    }
                }
            });
        });
    });
</script>
@endpush