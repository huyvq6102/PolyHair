@extends('admin.layouts.app')

@section('title', 'Quản lý người dùng')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý người dùng</h1>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm mới
        </a>
    @endif
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Bộ lọc người dùng</h6>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.users.trash') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-trash"></i> Thùng rác
            </a>
        @endif
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" class="form-inline">
            <div class="form-group mr-3">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm theo tên..." value="{{ request('keyword') }}">
            </div>
            <div class="form-group mr-3">
                <select name="role_id" class="form-control">
                    <option value="">Tất cả chức vụ</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mr-3">
                <select name="status" class="form-control">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Hoạt động" {{ request('status') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="Vô hiệu hóa" {{ request('status') == 'Vô hiệu hóa' ? 'selected' : '' }}>Vô hiệu hóa</option>
                    <option value="Cấm" {{ request('status') == 'Cấm' ? 'selected' : '' }}>Cấm</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Lọc
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary ml-2">
                <i class="fas fa-redo"></i> Làm mới
            </a>
        </form>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách người dùng</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên</th>
                        <th>Ngày sinh</th>
                        <th>Số điện thoại</th>
                        <th>Email</th>
                        <th>Chức vụ</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                @if($user->avatar)
                                    <img src="{{ asset('legacy/images/avatars/' . $user->avatar) }}" alt="{{ $user->name }}" width="50" height="50" class="img-thumbnail rounded-circle">
                                @else
                                    <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->dob ? $user->dob->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $user->role->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusDisplay = $user->status ?? 'N/A';
                                    $statusClass = 'secondary';
                                    
                                    // Kiểm tra nếu có banned_until và chưa hết thời gian
                                    $isTemporarilyBanned = $user->banned_until && now()->lessThan($user->banned_until);
                                    
                                    if ($user->status === 'Cấm') {
                                        $statusDisplay = 'Cấm';
                                        $statusClass = 'danger';
                                    } elseif ($isTemporarilyBanned) {
                                        // Nếu có banned_until và chưa hết thời gian, hiển thị "Vô hiệu hóa" dù status là gì
                                        $statusDisplay = 'Vô hiệu hóa';
                                        $statusClass = 'warning';
                                        
                                        // Tính thời gian còn lại và format đẹp
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
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary" title="Sửa thông tin">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(!$user->isAdmin())
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirmDelete('{{ $user->name }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa người dùng">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-secondary" disabled title="Không thể xóa tài khoản quản trị viên">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Chưa có người dùng nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($users->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <!-- Text hiển thị thông tin -->
                <div class="text-muted">
                    Đang xem {{ $users->firstItem() ?? 0 }} đến {{ $users->lastItem() ?? 0 }} trong tổng số {{ $users->total() }} mục
                </div>
                
                <!-- Nút phân trang -->
                <nav>
                    <ul class="pagination mb-0">
                        <!-- Nút Trước -->
                        <li class="page-item {{ $users->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $users->previousPageUrl() ? $users->appends(request()->query())->previousPageUrl() : '#' }}" 
                               style="{{ $users->onFirstPage() ? 'background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;' : 'background-color: white; color: #007bff;' }}">
                                Trước
                            </a>
                        </li>
                        
                        <!-- Các số trang -->
                        @php
                            $currentPage = $users->currentPage();
                            $lastPage = $users->lastPage();
                            
                            // Tính toán phạm vi trang hiển thị (hiển thị tối đa 5 trang)
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($lastPage, $currentPage + 2);
                            
                            // Điều chỉnh nếu gần đầu hoặc cuối
                            if ($endPage - $startPage < 4) {
                                if ($startPage == 1) {
                                    $endPage = min($lastPage, $startPage + 4);
                                } else {
                                    $startPage = max(1, $endPage - 4);
                                }
                            }
                        @endphp
                        
                        @for($page = $startPage; $page <= $endPage; $page++)
                            @if($page == $currentPage)
                                <li class="page-item active">
                                    <span class="page-link" style="background-color: #007bff; color: white; border-color: #007bff;">
                                        {{ $page }}
                                    </span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $users->appends(request()->query())->url($page) }}" 
                                       style="background-color: white; color: #007bff;">
                                        {{ $page }}
                                    </a>
                                </li>
                            @endif
                        @endfor
                        
                        <!-- Nút Tiếp -->
                        <li class="page-item {{ $users->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $users->nextPageUrl() ? $users->appends(request()->query())->nextPageUrl() : '#' }}" 
                               style="{{ $users->hasMorePages() ? 'background-color: white; color: #007bff;' : 'background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;' }}">
                                Tiếp
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        @else
            <!-- Hiển thị thông tin khi không có phân trang -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Đang xem {{ $users->count() > 0 ? 1 : 0 }} đến {{ $users->count() }} trong tổng số {{ $users->total() }} mục
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .gap-1 {
        gap: 0.25rem;
    }
    .gap-1 > * {
        margin-right: 0.25rem;
        margin-bottom: 0.25rem;
    }
    .gap-1 form {
        display: inline-block;
        margin-right: 0.25rem;
        margin-bottom: 0.25rem;
    }
    .gap-1 button, .gap-1 a {
        white-space: nowrap;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "paging": false,
            "info": false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
            }
        });
    });

    function confirmDelete(userName) {
        const today = new Date();
        const dateStr = today.toLocaleDateString('vi-VN', { 
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric' 
        });
        return confirm('Bạn có chắc chắn muốn xóa tài khoản người dùng "' + userName + '" vào ngày ' + dateStr + ' hay không?');
    }
</script>
@endpush


