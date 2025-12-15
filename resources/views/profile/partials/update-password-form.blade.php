<section>
    <h3 style="color: #333; margin-bottom: 15px;">Cập nhật mật khẩu</h3>
    <p style="color: #666; margin-bottom: 25px;">Đảm bảo tài khoản của bạn sử dụng mật khẩu dài và ngẫu nhiên để bảo mật.</p>

    <form method="post" action="{{ route('password.update') }}" id="passwordForm" novalidate>
        @csrf
        @method('put')

        <div class="form-group">
            <label for="update_password_current_password">Mật khẩu hiện tại <span class="text-danger">*</span></label>
            <input type="password" id="update_password_current_password" name="current_password" 
                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                   autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <span class="custom-error-message" id="update_password_current_password-error"></span>
        </div>

        <div class="form-group">
            <label for="update_password_password">Mật khẩu mới <span class="text-danger">*</span></label>
            <input type="password" id="update_password_password" name="password" 
                   class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                   autocomplete="new-password" minlength="8">
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <span class="custom-error-message" id="update_password_password-error"></span>
        </div>

        <div class="form-group">
            <label for="update_password_password_confirmation">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
            <input type="password" id="update_password_password_confirmation" name="password_confirmation" 
                   class="form-control" autocomplete="new-password">
            <span class="custom-error-message" id="update_password_password_confirmation-error"></span>
        </div>

        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary">Lưu mật khẩu</button>
        </div>
    </form>
</section>
