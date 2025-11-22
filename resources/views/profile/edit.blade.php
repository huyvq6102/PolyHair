@extends('layouts.site')

@section('title', 'Tài khoản của tôi')

@push('styles')
<link rel="stylesheet" href="{{ asset('legacy/content/css/auth-pages.css') }}">
<link rel="stylesheet" href="{{ asset('legacy/content/css/profile-pages.css') }}">
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Hiển thị tên file khi chọn
        $('#avatar').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $('#avatar-label').text(fileName || 'Chọn tệp');
            
            // Preview ảnh
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var previewImg = $('.form-group:has(#avatar) .img-thumbnail');
                    if (previewImg.length) {
                        previewImg.attr('src', e.target.result);
                    } else {
                        // Tạo preview mới nếu chưa có
                        var previewDiv = $('<div class="mb-3"></div>');
                        previewDiv.html('<img src="' + e.target.result + '" class="img-thumbnail rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">');
                        $('.form-group:has(#avatar) .custom-file').before(previewDiv);
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Xử lý nút xóa tài khoản
        $('#deleteAccountBtn').on('click', function() {
            $('#deleteAccountModal').modal('show');
        });

        // Đảm bảo modal hoạt động đúng khi mở
        $('#deleteAccountModal').on('shown.bs.modal', function() {
            // Force enable pointer events
            $(this).css({
                'z-index': '9999',
                'pointer-events': 'auto'
            });
            $(this).find('.modal-dialog').css({
                'z-index': '10000',
                'pointer-events': 'auto'
            });
            $(this).find('.modal-content').css({
                'z-index': '10001',
                'pointer-events': 'auto'
            });
            $(this).find('input, button, select, textarea, form').css({
                'pointer-events': 'auto',
                'cursor': 'auto'
            });
            $('#delete_password').css('cursor', 'text').focus();
        });

        // Fix backdrop
        $(document).on('show.bs.modal', '.modal', function() {
            $('.modal-backdrop').css('z-index', '9998');
        });
    });
</script>
@endpush

@section('content')
<section class="profile-hero">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="text-center mb-5">
                    <h1 class="text-white mb-3">Tài khoản của tôi</h1>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                @if(session('status') === 'profile-updated')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Thông tin đã được cập nhật thành công!
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('status') === 'password-updated')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Mật khẩu đã được cập nhật thành công!
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="profile-section">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="profile-section">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="profile-section">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal xóa tài khoản - Đặt ở ngoài section để tránh bị chặn -->
@include('profile.partials.delete-user-modal')
@endsection
