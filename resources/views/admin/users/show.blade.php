@extends('admin.layouts.app')

@section('title', 'Chi tiết người dùng')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chi tiết người dùng #{{ $user->id }}</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<!-- User Info -->
<div class="row">
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Ảnh đại diện</h6>
            </div>
            <div class="card-body text-center">
                @if($user->avatar)
                    <img src="{{ asset('legacy/images/avatars/' . $user->avatar) }}" alt="{{ $user->name }}" class="img-thumbnail" style="max-width: 200px;">
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
                <h6 class="m-0 font-weight-bold text-primary">Thông tin người dùng</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tên:</strong>
                        <p>{{ $user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong>
                        <p>{{ $user->email }}</p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Số điện thoại:</strong>
                        <p>{{ $user->phone ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Ngày sinh:</strong>
                        <p>{{ $user->dob ? $user->dob->format('d/m/Y') : 'N/A' }}</p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Giới tính:</strong>
                        <p>{{ $user->gender ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Chức vụ:</strong>
                        <p>
                            <span class="badge badge-info">
                                {{ $user->role->name ?? 'N/A' }}
                            </span>
                        </p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Trạng thái:</strong>
                        <p>
                            @php
                                $statusDisplay = $user->status ?? 'N/A';
                                $statusClass = 'secondary';
                                
                                $isTemporarilyBanned = $user->banned_until && now()->lessThan($user->banned_until);
                                
                                if ($user->status === 'Cấm') {
                                    $statusDisplay = 'Cấm';
                                    $statusClass = 'danger';
                                } elseif ($isTemporarilyBanned) {
                                    $statusDisplay = 'Vô hiệu hóa';
                                    $statusClass = 'warning';
                                    
                                    $diffInMinutes = now()->diffInMinutes($user->banned_until, false);
                                    if ($diffInMinutes > 0) {
                                        $hours = floor($diffInMinutes / 60);
                                        $minutes = $diffInMinutes % 60;
                                        
                                        if ($hours > 0 && $minutes > 0) {
                                            $statusDisplay .= ' (Còn ' . $hours . 'h' . $minutes . 'p)';
                                        } elseif ($hours > 0) {
                                            $statusDisplay .= ' (Còn ' . $hours . 'h)';
                                        } else {
                                            $statusDisplay .= ' (Còn ' . $minutes . 'p)';
                                        }
                                    }
                                } elseif ($user->status === 'Hoạt động') {
                                    $statusClass = 'success';
                                } elseif ($user->status === 'Vô hiệu hóa') {
                                    $statusClass = 'warning';
                                }
                            @endphp
                            <span class="badge badge-{{ $statusClass }}">
                                {{ $statusDisplay }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Ngày tạo:</strong>
                        <p>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                    </div>
                </div>
                
                @if($user->banned_until && $user->ban_reason)
                <div class="row mb-3">
                    <div class="col-12">
                        <strong class="text-danger">Lý do bị cấm:</strong>
                        <p class="text-danger">{{ $user->ban_reason }}</p>
                    </div>
                </div>
                @endif
                
                @if(auth()->user()->isAdmin())
                    <div class="mt-4">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Sửa thông tin
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

