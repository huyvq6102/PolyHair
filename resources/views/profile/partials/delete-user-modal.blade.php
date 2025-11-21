<!-- Modal xóa tài khoản -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" role="dialog" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="background: #151922; border: 1px solid #252a34; border-radius: 18px;">
            <div class="modal-header" style="border-bottom: 1px solid #252a34;">
                <h5 class="modal-title text-white" id="deleteAccountModalLabel">Xác nhận xóa tài khoản</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                
                <div class="modal-body">
                    <p class="text-white">
                        Bạn có chắc chắn muốn xóa tài khoản của mình không?
                    </p>
                    <p class="text-white" style="font-size: 14px; color: #cbd5f5 !important;">
                        Khi tài khoản của bạn bị xóa, tất cả tài nguyên và dữ liệu sẽ bị xóa vĩnh viễn. 
                        Vui lòng nhập mật khẩu của bạn để xác nhận bạn muốn xóa vĩnh viễn tài khoản của mình.
                    </p>

                    <div class="form-group mt-4">
                        <label for="delete_password" class="text-white">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" id="delete_password" name="password" 
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                               placeholder="Nhập mật khẩu để xác nhận" required 
                               style="background: #151922; border: 1px solid #252a34; color: #fff; border-radius: 12px; padding: 12px 16px;">
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #252a34;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa tài khoản</button>
                </div>
            </form>
        </div>
    </div>
</div>

