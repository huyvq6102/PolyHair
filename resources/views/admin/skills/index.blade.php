@extends('admin.layouts.app')

@section('title', 'Quản lý chuyên môn')

@section("content")
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý chuyên môn</h1>
    <a href="{{ route('admin.skills.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm chuyên môn
    </a>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4" id="skillTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ $activeTab == 'skills' ? 'active' : '' }}" id="skills-tab" data-toggle="tab" href="#skills" role="tab" aria-controls="skills" aria-selected="{{ $activeTab == 'skills' ? 'true' : 'false' }}">
            <i class="fas fa-list"></i> Danh sách chuyên môn
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab == 'employees' ? 'active' : '' }}" id="employees-tab" data-toggle="tab" href="#employees" role="tab" aria-controls="employees" aria-selected="{{ $activeTab == 'employees' ? 'true' : 'false' }}">
            <i class="fas fa-users"></i> Chuyên môn nhân viên
        </a>
    </li>
</ul>

<!-- Tabs Content -->
<div class="tab-content" id="skillTabsContent">
    <!-- Tab 1: Danh sách chuyên môn -->
    <div class="tab-pane fade {{ $activeTab == 'skills' ? 'show active' : '' }}" id="skills" role="tabpanel" aria-labelledby="skills-tab">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <form method="GET" action="{{ route('admin.skills.index') }}" class="form-inline">
                    <input type="hidden" name="tab" value="skills">
                    <div class="form-group mr-2">
                        <input type="text" name="keyword" value="{{ $keyword }}" class="form-control" placeholder="Tìm theo tên">
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">Lọc</button>
                    <a href="{{ route('admin.skills.index', ['tab' => 'skills']) }}" class="btn btn-secondary">Làm mới</a>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên chuyên môn</th>
                                <th>Mô tả</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($skills as $skill)
                                <tr>
                                    <td>{{ $skill->id }}</td>
                                    <td>{{ $skill->name }}</td>
                                    <td>{{ $skill->description }}</td>
                                    <td>
                                        <a href="{{ route('admin.skills.edit', $skill->id) }}" class="btn btn-sm btn-primary">Sửa</a>
                                        <form action="{{ route('admin.skills.destroy', $skill->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa chuyên môn này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if ($skills->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $skills->appends(['tab' => 'skills'])->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 2: Chuyên môn nhân viên -->
    <div class="tab-pane fade {{ $activeTab == 'employees' ? 'show active' : '' }}" id="employees" role="tabpanel" aria-labelledby="employees-tab">
        <!-- Filter -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Lọc nhân viên</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.skills.index') }}" class="form-row">
                    <input type="hidden" name="tab" value="employees">
                    <div class="form-group col-md-4">
                        <label for="employee_keyword">Tên nhân viên</label>
                        <input type="text" name="employee_keyword" id="employee_keyword" class="form-control" placeholder="Nhập tên nhân viên"
                            value="{{ $employee_keyword ?? '' }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="employee_skill_id">Chuyên môn</label>
                        <select name="employee_skill_id" id="employee_skill_id" class="form-control">
                            <option value="">-- Tất cả chuyên môn --</option>
                            @foreach($allSkills as $skill)
                                <option value="{{ $skill->id }}" {{ ($employee_skill_id ?? '') == $skill->id ? 'selected' : '' }}>
                                    {{ $skill->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-filter"></i> Lọc</button>
                        <a href="{{ route('admin.skills.index', ['tab' => 'employees']) }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Làm mới</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Danh sách chuyên môn nhân viên</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nhân viên</th>
                                <th>Vị trí</th>
                                <th>Chuyên môn</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                                <tr>
                                    <td>{{ $employee->id }}</td>
                                    <td>{{ $employee->user->name ?? 'N/A' }}</td>
                                    <td>{{ $employee->position ?? 'N/A' }}</td>
                                    <td>
                                        @if($employee->skills->isEmpty())
                                            <span class="text-muted">Chưa thiết lập</span>
                                        @else
                                            @foreach($employee->skills as $skill)
                                                <span class="badge badge-info">{{ $skill->name }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.employee-skills.edit', $employee->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Chỉnh sửa
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($employees->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $employees->appends(['tab' => 'employees'])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Lưu tab active vào URL khi chuyển tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        var tab = target === '#skills' ? 'skills' : 'employees';
        var url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.pushState({}, '', url);
    });
});
</script>
@endpush
@endsection
