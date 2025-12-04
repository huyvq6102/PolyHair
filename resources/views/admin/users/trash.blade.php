@extends('admin.layouts.app')

@section('title', 'Thùng rác người dùng')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Thùng rác người dùng</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách người dùng đã xóa</h6>
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
                        <th>Ngày xóa</th>
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
                                <span class="badge badge-{{ $user->status == 'Hoạt động' ? 'success' : ($user->status == 'Vô hiệu hóa' ? 'warning' : 'danger') }}">
                                    {{ $user->status ?? 'N/A' }}
                                </span>
                            </td>
                            <td>{{ $user->deleted_at ? $user->deleted_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>
                                <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Bạn có chắc chắn muốn khôi phục người dùng này?');">
                                        <i class="fas fa-undo"></i> Khôi phục
                                    </button>
                                </form>
                                @if(!$user->isAdmin())
                                    <form action="{{ route('admin.users.force-delete', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn người dùng này? Hành động này không thể hoàn tác!');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash-alt"></i> Xóa vĩnh viễn
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-secondary" disabled title="Không thể xóa vĩnh viễn tài khoản quản trị viên">
                                        <i class="fas fa-lock"></i> Không thể xóa
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Thùng rác trống</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($users->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

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
</script>
@endpush

