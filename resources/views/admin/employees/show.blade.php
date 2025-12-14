@extends('admin.layouts.app')

@section('title', 'Chi tiết nhân viên')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chi tiết nhân viên #{{ $employee->id }}</h1>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<!-- Employee Info -->
<div class="row">
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Ảnh đại diện</h6>
            </div>
            <div class="card-body text-center">
                @if($employee->avatar)
                    <img src="{{ asset('legacy/images/avatars/' . $employee->avatar) }}" alt="{{ $employee->user->name ?? 'N/A' }}" class="img-thumbnail" style="max-width: 200px;">
                @else
                    <div class="bg-light p-5">
                        <i class="fas fa-user fa-5x text-muted"></i>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin nhân viên</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tên nhân viên:</strong>
                        <p>{{ $employee->user->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong>
                        <p>{{ $employee->user->email ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Số điện thoại:</strong>
                        <p>{{ $employee->user->phone ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Vị trí:</strong>
                        <p>{{ $employee->position ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Cấp độ:</strong>
                        <p>{{ $employee->level ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Số năm kinh nghiệm:</strong>
                        <p>{{ $employee->experience_years ?? 'N/A' }} năm</p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Trạng thái:</strong>
                        <p>
                            <span class="badge badge-{{ $employee->status == 'Đang làm việc' ? 'success' : ($employee->status == 'Nghỉ phép' ? 'warning' : 'secondary') }}">
                                {{ $employee->status }}
                            </span>
                        </p>
                    </div>
                </div>
                
                @if($employee->bio)
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Giới thiệu:</strong>
                        <p>{{ $employee->bio }}</p>
                    </div>
                </div>
                @endif
                
                @if(auth()->user()->isAdmin())
                    <div class="mt-4">
                        <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Sửa thông tin
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

