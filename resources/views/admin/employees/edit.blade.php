@extends('admin.layouts.app')

@section('title', 'Sửa nhân viên')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Sửa nhân viên</h1>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin nhân viên</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            
            <h5 class="mb-3">Thông tin tài khoản</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Tên nhân viên <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $employee->user->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Nhập tên nhân viên" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Vui lòng nhập tên nhân viên</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email', $employee->user->email ?? '') }}" class="form-control @error('email') is-invalid @enderror" placeholder="Nhập email" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Vui lòng nhập email</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $employee->user->phone ?? '') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="Nhập số điện thoại">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Mật khẩu mới</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Để trống nếu không muốn đổi">
                        <small class="form-text text-muted">Chỉ nhập nếu muốn thay đổi mật khẩu</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="gender">Giới tính</label>
                        <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror">
                            <option value="">-- Chọn giới tính --</option>
                            <option value="Nam" {{ old('gender', $employee->gender ?? $employee->user->gender ?? '') == 'Nam' ? 'selected' : '' }}>Nam</option>
                            <option value="Nữ" {{ old('gender', $employee->gender ?? $employee->user->gender ?? '') == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                            <option value="Khác" {{ old('gender', $employee->gender ?? $employee->user->gender ?? '') == 'Khác' ? 'selected' : '' }}>Khác</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="dob">Ngày sinh</label>
                        <input type="date" name="dob" id="dob" value="{{ old('dob', $employee->dob ? $employee->dob->format('Y-m-d') : ($employee->user->dob ? $employee->user->dob->format('Y-m-d') : '')) }}" class="form-control @error('dob') is-invalid @enderror">
                        @error('dob')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3">Thông tin công việc</h5>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="position">Vị trí <span class="text-danger">*</span></label>
                        <select name="position" id="position" class="form-control @error('position') is-invalid @enderror" required>
                            <option value="">-- Chọn vị trí --</option>
                            <option value="Stylist" {{ old('position', $employee->position) == 'Stylist' ? 'selected' : '' }}>Stylist</option>
                            <option value="Barber" {{ old('position', $employee->position) == 'Barber' ? 'selected' : '' }}>Barber</option>
                            <option value="Shampooer" {{ old('position', $employee->position) == 'Shampooer' ? 'selected' : '' }}>Shampooer</option>
                            <option value="Receptionist" {{ old('position', $employee->position) == 'Receptionist' ? 'selected' : '' }}>Receptionist</option>
                        </select>
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Vui lòng chọn vị trí</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="level">Cấp độ</label>
                        <select name="level" id="level" class="form-control @error('level') is-invalid @enderror">
                            <option value="">-- Chọn cấp độ --</option>
                            <option value="Intern" {{ old('level', $employee->level) == 'Intern' ? 'selected' : '' }}>Intern</option>
                            <option value="Junior" {{ old('level', $employee->level) == 'Junior' ? 'selected' : '' }}>Junior</option>
                            <option value="Middle" {{ old('level', $employee->level) == 'Middle' ? 'selected' : '' }}>Middle</option>
                            <option value="Senior" {{ old('level', $employee->level) == 'Senior' ? 'selected' : '' }}>Senior</option>
                        </select>
                        @error('level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="experience_years">Số năm kinh nghiệm</label>
                        <input type="number" name="experience_years" id="experience_years" value="{{ old('experience_years', $employee->experience_years) }}" class="form-control @error('experience_years') is-invalid @enderror" placeholder="Nhập số năm kinh nghiệm" min="0" max="50">
                        @error('experience_years')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Trạng thái</label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="Đang làm việc" {{ old('status', $employee->status) == 'Đang làm việc' ? 'selected' : '' }}>Đang làm việc</option>
                            <option value="Nghỉ phép" {{ old('status', $employee->status) == 'Nghỉ phép' ? 'selected' : '' }}>Nghỉ phép</option>
                            <option value="Vô hiệu hóa" {{ old('status', $employee->status) == 'Vô hiệu hóa' ? 'selected' : '' }}>Vô hiệu hóa</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="avatar">Ảnh đại diện</label>
                @if($employee->avatar)
                    <div class="mb-2">
                        <img src="{{ asset('legacy/images/avatars/' . $employee->avatar) }}" alt="{{ $employee->user->name ?? 'N/A' }}" width="100" height="100" class="img-thumbnail">
                    </div>
                @endif
                <input type="file" class="form-control-file border @error('avatar') is-invalid @enderror" id="avatar" name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif">
                <small class="form-text text-muted">Chấp nhận: JPG, PNG, GIF (tối đa 2MB). Để trống nếu không muốn thay đổi.</small>
                @error('avatar')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="bio">Giới thiệu</label>
                <textarea name="bio" id="bio" rows="4" class="form-control @error('bio') is-invalid @enderror" placeholder="Nhập giới thiệu về nhân viên">{{ old('bio', $employee->bio) }}</textarea>
                @error('bio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
                <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
@endpush

