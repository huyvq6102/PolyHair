@extends('layouts.site')

@section('content')

<div class="container py-5" style="margin-top: 120px;">
    <div class="row">
        <!-- Cột thông tin cá nhân và hành động -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <img src="https://img.freepik.com/free-vector/smiling-young-man-illustration_1308-174669.jpg?semt=ais_hybrid&w=740&q=80" alt="Avatar"
                         class="rounded-circle img-fluid mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">
                        <i class="fas fa-medal me-1 text-warning"></i>Thành viên Vàng
                    </p>

                    <!-- Thống kê -->
                    <div class="d-flex justify-content-around mb-4 p-3 bg-light rounded">
                        <div>
                            <h5 class="mb-0">{{ $user->appointments->count() }}</h5>
                            <small class="text-muted">Lần cắt</small>
                        </div>
                        <div>
                            <h5 class="mb-0">1.250</h5>
                            <small class="text-muted">Điểm thưởng</small>
                        </div>
                    </div>

                    <!-- Nút hành động chính -->
                    <div class="d-flex">
                        <a href="#" class="mr-3 btn btn-primary rounded-pill w-100 fw-bold d-flex align-items-center justify-content-center py-2">
                            <i class="fas fa-calendar-plus mr-2"></i>Đặt lịch
                        </a>
                        <a href="#thong-tin-ca-nhan" class="mr-2 btn btn-outline-secondary rounded-pill w-100 d-flex align-items-center justify-content-center py-2">
                            <i class="fas fa-user-edit mr-2"></i>Sửa hồ sơ
                        </a>
                    </div>


                </div>
            </div>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Barber yêu thích</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <img src="{{$user->avatar}}" alt="Barber" class="rounded-circle me-3" style="width: 50px; height: 50px;">
                        <div>
                            <h6 class="mb-0">Anh Minh Tuấn</h6>
                            <small class="text-muted">Senior Barber</small>
                        </div>
                    </div>
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
                            <div class="list-group">
                                @php
                                    $upcomingAppointments = $user->appointments->filter(function($appointment) {
                                        return $appointment->status != 'Hoàn thành' 
                                            && $appointment->status != 'Đã thanh toán' 
                                            && $appointment->status != 'Đã hủy'
                                            && !$appointment->trashed();
                                    })->sortBy('start_at');
                                @endphp
                                
                                @forelse($upcomingAppointments as $appointment)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">
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
                                        <small class="text-muted">
                                            @if($appointment->employee && $appointment->employee->user)
                                                Barber: {{ $appointment->employee->user->name }}
                                            @else
                                                Chưa phân công nhân viên
                                            @endif
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold d-block">
                                            @if($appointment->start_at)
                                                {{ $appointment->start_at->format('H:i, d/m/Y') }}
                                            @else
                                                Chưa có thời gian
                                            @endif
                                        </span>
                                        <span class="badge bg-info ms-2">{{ $appointment->status ?? 'Chờ xử lý' }}</span>
                                    </div>
                                    <div class="ms-3">
                                        <a href="{{ route('site.appointment.show', $appointment->id) }}" class="btn btn-sm btn-outline-primary me-2">Xem</a>
                                        @if($appointment->status != 'Đã hủy' && $appointment->status != 'Hoàn thành')
                                            <a href="#" class="btn btn-sm btn-outline-danger">Hủy</a>
                                        @endif
                                    </div>
                                </div>
                                @empty
                                <div class="list-group-item text-center text-muted py-4">
                                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                    <p class="mb-0">Chưa có lịch hẹn sắp tới</p>
                                </div>
                                @endforelse
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-4">Các lịch hẹn đã hoàn thành</h5>
                            <div class="list-group">
<<<<<<< HEAD
                                @php
                                    $completedAppointments = $user->appointments->filter(function($appointment) {
                                        return ($appointment->status == 'Hoàn thành' || $appointment->status == 'Đã thanh toán')
                                            && $appointment->status != 'Đã hủy'
                                            && !$appointment->trashed();
                                    })->sortByDesc('start_at');
                                @endphp
                                
                                @forelse($completedAppointments as $appointment)
                                    <div class="list-group-item d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h6 class="mb-1">
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
                                            <small class="text-muted">
                                                @if($appointment->employee && $appointment->employee->user)
                                                    {{ $appointment->employee->user->name }}
                                                @endif
                                                @if($appointment->start_at)
                                                    - {{ $appointment->start_at->format('H:i, d/m/Y') }}
                                                @endif
                                            </small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success">{{ $appointment->status }}</span>
                                            <a href="{{ route('site.appointment.show', $appointment->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Xem
                                            </a>
                                            @php
                                                $existingReview = $appointment->reviews->where('user_id', $user->id)->first();
                                            @endphp
                                            @if($existingReview)
                                                <a href="{{ route('site.reviews.edit', $existingReview->id) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-star"></i> Xem đánh giá
                                                </a>
                                            @else
                                                <a href="{{ route('site.reviews.create', ['appointment_id' => $appointment->id]) }}" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-star"></i> Đánh giá
                                                </a>
                                            @endif
=======
                                @foreach($user->appointments as $appointment)
                                    @if($appointment->status == 'Đã thanh toán' && $appointment->status != 'Đã hủy')

                                    <div class="list-group-item d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h6 class="mb-1">Cắt tóc tạo kiểu</h6>
                                          @if (!empty($appointment->employee))
                                            <small class="text-muted">
                                                {{ $appointment->employee->user->name }} - {{ $appointment->start_at }}
                                            </small>
                                          @else
                                            <small class="text-muted">{{ $appointment->start_at }}</small>
                                          @endif
>>>>>>> 82ef0c91927fd97ffae3fd08510e99409a6da62f
                                        </div>
                                    </div>
                                @empty
                                <div class="list-group-item text-center text-muted py-4">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <p class="mb-0">Chưa có lịch hẹn đã hoàn thành</p>
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
                                            <small class="text-muted">{{ $payment->created_at->format('H:i d/m/Y') }}</small>
                                        </div>
                                        <p class="mb-1">Tổng tiền: <strong class="text-danger">{{ number_format($payment->total) }}đ</strong></p>
                                        <p class="mb-1"><small>Phương thức: {{ $payment->payment_type }}</small></p>
                                        
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
    tabEl.addEventListener('show.bs.tab', function (event) {
      // event.target // newly activated tab
      // event.relatedTarget // previous active tab
    })
</script>
@endsection
