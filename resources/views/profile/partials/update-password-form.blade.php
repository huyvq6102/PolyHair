<section>
    <h3 style="color: #333; margin-bottom: 15px;">Cập nhật mật khẩu</h3>
    <p style="color: #666; margin-bottom: 25px;">Đảm bảo tài khoản của bạn sử dụng mật khẩu dài và ngẫu nhiên để bảo mật.</p>

    <form method="post" action="{{ route('password.update') }}" id="passwordForm" novalidate>
        @csrf
        @method('put')

        <div class="form-group">
            <label for="update_password_current_password">Mật khẩu hiện tại <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="password" id="update_password_current_password" name="current_password" 
                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                       autocomplete="current-password">
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary password-toggle-btn"
                            data-target="update_password_current_password" aria-label="Hiển thị hoặc ẩn mật khẩu">
                        <span class="toggle-icon icon-eye" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                        <span class="toggle-icon icon-eye-off" aria-hidden="true" style="display: none;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-7 0-11-7-11-7a21.77 21.77 0 0 1 5.11-6.41"></path>
                                <path d="M9.88 9.88a3 3 0 0 0 4.24 4.24"></path>
                                <path d="M1 1l22 22"></path>
                            </svg>
                        </span>
                    </button>
                </div>
                @error('current_password', 'updatePassword')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="update_password_password">Mật khẩu mới <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="password" id="update_password_password" name="password" 
                       class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                       autocomplete="new-password" minlength="8">
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary password-toggle-btn"
                            data-target="update_password_password" aria-label="Hiển thị hoặc ẩn mật khẩu">
                        <span class="toggle-icon icon-eye" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                        <span class="toggle-icon icon-eye-off" aria-hidden="true" style="display: none;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-7 0-11-7-11-7a21.77 21.77 0 0 1 5.11-6.41"></path>
                                <path d="M9.88 9.88a3 3 0 0 0 4.24 4.24"></path>
                                <path d="M1 1l22 22"></path>
                            </svg>
                        </span>
                    </button>
                </div>
                @error('password', 'updatePassword')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="update_password_password_confirmation">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="password" id="update_password_password_confirmation" name="password_confirmation" 
                       class="form-control" autocomplete="new-password">
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary password-toggle-btn"
                            data-target="update_password_password_confirmation" aria-label="Hiển thị hoặc ẩn mật khẩu">
                        <span class="toggle-icon icon-eye" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                        <span class="toggle-icon icon-eye-off" aria-hidden="true" style="display: none;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-7 0-11-7-11-7a21.77 21.77 0 0 1 5.11-6.41"></path>
                                <path d="M9.88 9.88a3 3 0 0 0 4.24 4.24"></path>
                                <path d="M1 1l22 22"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary">Lưu mật khẩu</button>
        </div>
    </form>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.password-toggle-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (!input) return;
                
                const eye = this.querySelector('.icon-eye');
                const eyeOff = this.querySelector('.icon-eye-off');
                
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                
                if (eye && eyeOff) {
                    eye.style.display = isHidden ? 'none' : 'inline-flex';
                    eyeOff.style.display = isHidden ? 'inline-flex' : 'none';
                }
            });
        });
    });
</script>
@endpush
