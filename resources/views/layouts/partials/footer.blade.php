<!-- footer -->
<footer class="footer" style="padding-top: 0;">
    <div class="footer_top" style="padding-top: 100px; padding-bottom: 90px;">
        <div class="container">
            <div class="row">
                <!-- Cột 1: TRAKY HAIR SALON -->
                <div class="col-xl-2 col-md-6 col-lg-2">
                    <div class="footer_widget">
                        <h3 class="footer_title">
                            POLY HAIR SALON
                        </h3>
                        <ul>
                            <li><a href="#">Những câu hỏi thường gặp</a></li>
                            <li><a href="#">Chính sách bảo mật</a></li>
                            <li><a href="#">Chính sách bảo hành</a></li>
                            <li><a href="#">Chính sách mua hàng</a></li>
                            <li><a href="#">Chính sách người dùng</a></li>
                            <li><a href="{{ route('site.contact.index') }}">Liên hệ</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Cột 2: Tổng Đài Hỗ Trợ -->
                <div class="col-xl-3 col-md-6 col-lg-3">
                    <div class="footer_widget">
                        <h3 class="footer_title">
                            Tổng Đài Hỗ Trợ
                        </h3>
                        <ul>
                            <li>Hotline: <a href="tel:1900636883">1900 636 883</a></li>
                            <li>Giờ mở cửa: 9h00 - 20h00</li>
                            <li>Trụ sở văn phòng: 159 Đ. Calmette, Phường Hoàn Kiếm, Thành phố Hà Nội</li>
                        </ul>
                    </div>
                </div>

                <!-- Cột 3: Thống Kê Truy Cập -->
                <div class="col-xl-2 col-md-6 col-lg-2">
                    <div class="footer_widget">
                        <h3 class="footer_title">
                            Thống Kê Truy Cập
                        </h3>
                        <ul>
                            <li>Online: <span id="online-count">7</span></li>
                            <li>Truy cập tuần: <span id="week-count">2053</span></li>
                            <li>Truy cập tháng: <span id="month-count">37669</span></li>
                            <li>Tổng truy cập: <span id="total-count">399694</span></li>
                        </ul>
                    </div>
                </div>

                <!-- Cột 4: Mạng Xã Hội -->
                <div class="col-xl-2 col-md-6 col-lg-2">
                    <div class="footer_widget">
                        <h3 class="footer_title">
                            Mạng Xã Hội
                        </h3>
                        <ul class="social-media-list">
                            <li>
                                <a href="#" class="social-item">
                                    <span class="social-icon facebook"><i class="fa fa-facebook"></i></span>
                                    <span>178K follow</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="social-item">
                                    <span class="social-icon zalo"><i class="fa fa-comment"></i></span>
                                    <span>140K follow</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="social-item">
                                    <span class="social-icon tiktok"><i class="fa fa-music"></i></span>
                                    <span>650K follow</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="social-item">
                                    <span class="social-icon instagram"><i class="fa fa-instagram"></i></span>
                                    <span>140K follow</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Cột 5: Thanh Toán -->
                <div class="col-xl-3 col-md-6 col-lg-3">
                    <div class="footer_widget">
                        <h3 class="footer_title">
                            Thanh Toán
                        </h3>
                        <div class="payment-methods">
                            <div class="payment-item" style="display: inline-block; margin-right: 15px; margin-bottom: 10px; padding: 8px 12px; background: #fff; border-radius: 4px; font-weight: 700; color: #1a1f71; font-size: 14px;">VISA</div>
                            <div class="payment-item" style="display: inline-block; margin-right: 15px; margin-bottom: 10px; padding: 8px 12px; background: #fff; border-radius: 4px; font-weight: 700; color: #eb001b; font-size: 14px;">MasterCard</div>
                            <div class="payment-item" style="display: inline-block; margin-right: 15px; margin-bottom: 10px; padding: 8px 12px; background: #fff; border-radius: 4px; font-weight: 700; color: #0066cc; font-size: 14px;">JCB</div>
                            <img src="{{ asset('legacy/images/payment/visa.png') }}" alt="VISA" style="height: 30px; margin-right: 10px; margin-bottom: 10px; display: none;" onerror="this.style.display='none';">
                            <img src="{{ asset('legacy/images/payment/mastercard.png') }}" alt="MasterCard" style="height: 30px; margin-right: 10px; margin-bottom: 10px; display: none;" onerror="this.style.display='none';">
                            <img src="{{ asset('legacy/images/payment/jcb.png') }}" alt="JCB" style="height: 30px; margin-right: 10px; margin-bottom: 10px; display: none;" onerror="this.style.display='none';">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Phần dưới cùng -->
    <div class="copy-right_text" style="padding-bottom: 20px;">
        <div class="container">
            <div class="footer_border"></div>
            <div class="row">
                <div class="col-xl-12">
                    <p class="copy_right text-center" style="margin-bottom: 10px;">
                        Copyright &copy; {{ date('Y') }} Name Company. Designed by Nina
                    </p>
                    <p class="copy_right text-center" style="margin-bottom: 10px; font-weight: 600;">
                        CÔNG TY TNHH THƯƠNG MẠI DỊCH VỤ ĐÀO TẠO FOLY HAIR VIỆT NAM
                    </p>
                    <p class="copy_right text-center" style="margin-bottom: 10px;">
                        Giấy CNĐKDN số: 0318359961 - Ngày cấp: 20-03-2024 - Sửa đổi lần cuối ngày 24-06-2024
                    </p>
                    <p class="copy_right text-center" style="margin-bottom: 10px;">
                        Cơ quan cấp: Chi cục Thuế Hà Nội
                    </p>
                    <p class="copy_right text-center" style="margin-bottom: 10px;">
                        Hotline: <a href="tel:1900636883" style="color: #fff;">1900 63 68 83</a> - Email: <a href="mailto:POLYHAIRR@gmail.com" style="color: #fff;">mkttraky@gmail.com</a>
                    </p>
                    <p class="copy_right text-center" style="font-weight: 600;">
                        POLY HAIR SALON - HỆ THỐNG SALON LÀM TÓC CHUYÊN NGHIỆP HÀNG ĐẦU VIỆT NAM
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- footer-end -->

<!-- Appointment Form Popup -->
@include('site.partials.appointment-form')

<!-- JS here -->
<script src="{{ asset('legacy/content/js/vendor/modernizr-3.5.0.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/vendor/jquery-2.1.3.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/popper.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/isotope.pkgd.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/ajax-form.js') }}"></script>
<script src="{{ asset('legacy/content/js/waypoints.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/jquery.counterup.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/imagesloaded.pkgd.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/scrollIt.js') }}"></script>
<script src="{{ asset('legacy/content/js/jquery.scrollUp.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/wow.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/nice-select.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/jquery.slicknav.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/pgwslideshow.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/plugins.js') }}"></script>
<script src="{{ asset('legacy/content/js/gijgo.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="{{ asset('legacy/content/js/main.js') }}"></script>
@stack('scripts')

<script>
    // Đảm bảo dropdown menu hoạt động
    $(document).ready(function() {
        // Khởi tạo Bootstrap dropdown
        $('[data-toggle="dropdown"]').dropdown();

        // Xử lý click vào user dropdown
        $('#userDropdown').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var menu = $(this).next('.dropdown-menu');
            $('.dropdown-menu').not(menu).hide();
            menu.toggle();
        });

        // Đóng dropdown khi click bên ngoài
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').hide();
            }
        });
    });
</script>

<!-- Appointment Booking Form -->
@include('site.partials.appointment-form')
