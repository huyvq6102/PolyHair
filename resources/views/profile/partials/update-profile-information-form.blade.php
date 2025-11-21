<section>
    <h3>Thông tin tài khoản</h3>
    <p>Cập nhật thông tin tài khoản và địa chỉ email của bạn.</p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="form-group">
            <label for="name">Họ và tên <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                   value="{{ old('name', $user->name) }}" required autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email <span class="text-danger">*</span></label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                   value="{{ old('email', $user->email) }}" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-white" style="font-size: 14px;">
                        Địa chỉ email của bạn chưa được xác minh.
                        <button form="send-verification" class="btn btn-link p-0 text-warning" style="text-decoration: underline;">
                            Nhấn vào đây để gửi lại email xác minh.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success mt-2" style="font-size: 14px;">
                            Một liên kết xác minh mới đã được gửi đến địa chỉ email của bạn.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="form-group">
            <label for="phone">Số điện thoại</label>
            <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                   value="{{ old('phone', $user->phone) }}">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="gender">Giới tính</label>
            <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror">
                <option value="">-- Chọn giới tính --</option>
                <option value="Nam" {{ old('gender', $user->gender) == 'Nam' ? 'selected' : '' }}>Nam</option>
                <option value="Nữ" {{ old('gender', $user->gender) == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                <option value="Khác" {{ old('gender', $user->gender) == 'Khác' ? 'selected' : '' }}>Khác</option>
            </select>
            @error('gender')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="dob">Ngày sinh</label>
            <input type="date" id="dob" name="dob" class="form-control @error('dob') is-invalid @enderror" 
                   value="{{ old('dob', $user->dob ? $user->dob->format('Y-m-d') : '') }}">
            @error('dob')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="avatar">Ảnh đại diện</label>
            @if($user->avatar)
                <div class="mb-3">
                    <img src="{{ asset('legacy/images/avatars/' . $user->avatar) }}" 
                         alt="{{ $user->name }}" 
                         class="img-thumbnail rounded-circle" 
                         style="width: 100px; height: 100px; object-fit: cover;">
                </div>
            @endif
            <div class="custom-file">
                <input type="file" 
                       class="custom-file-input @error('avatar') is-invalid @enderror" 
                       id="avatar" 
                       name="avatar" 
                       accept="image/jpeg,image/png,image/jpg">
                <label class="custom-file-label" for="avatar" id="avatar-label">
                    @if($user->avatar)
                        {{ $user->avatar }}
                    @else
                        Chọn tệp
                    @endif
                </label>
                @error('avatar')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <small class="form-text text-muted" style="color: #8a8f9a !important;">
                Dung lượng file tối đa 1 MB. Định dạng: .JPEG, .PNG
            </small>
        </div>

        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        </div>
    </form>
</section>
