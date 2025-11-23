@extends('layouts.site')

@section('content')


@include('site.partials.slider')
<div class="container py-5">
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
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        
                        <!-- Tab Lịch sử đặt lịch -->
                        <div class="tab-pane fade show active" id="history" role="tabpanel">
                            <h5 class="mb-4">Các lịch hẹn sắp tới</h5>
                            <div class="list-group">
                                @foreach($user->appointments as $appointment)
                                @if($appointment->status != 'Đã thanh toán' && $appointment->status != 'Đã hủy')
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Cắt tóc + Gội đầu</h6>
                                        <small class="text-muted">Barber: Anh Minh Tuấn</small>
                                    </div>
                                    <div>
                                        <span class="fw-bold">09:00, 25/11/2025</span>
                                        <span class="badge bg-info ms-2">Sắp tới</span>
                                    </div>
                                    <a href="#" class="btn btn-sm btn-outline-danger ms-3">Hủy</a>
                                </div>
                                @endif
                                @endforeach

                            </div>

                            <hr class="my-4">

                            <h5 class="mb-4">Các lịch hẹn đã hoàn thành</h5>
                            <div class="list-group">
                                @foreach($user->appointments as $appointment)
                                    @if($appointment->status == 'Đã thanh toán' && $appointment->status != 'Đã hủy')
                                    <div class="list-group-item d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h6 class="mb-1">Cắt tóc tạo kiểu</h6>
                                            <small class="text-muted">{{ $appointment->employee->user->name}} - {{$appointment->start_at}}</small>
                                        </div>
                                        <span class="badge bg-success">{{ $appointment->status }}</span>
                                    </div>
                                    @endif
                               @endforeach
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
                                <div class="col-sm-9"><p class="fw-bold mb-0">{{ $user->dob }}</p></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3"><p class="text-muted mb-0">Địa chỉ</p></div>
                                <div class="col-sm-9"><p class="fw-bold mb-0"></p></div>
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
