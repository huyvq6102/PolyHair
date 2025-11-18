<!-- footer -->
<footer class="footer" style="background: #4A3600; margin-top: 0; width: 100%;">
    <div class="footer_top" style="padding-top: 120px; padding-bottom: 120px; background: #4A3600;">
        <div class="container">
            <div class="row">
                <div class="col-xl-3 col-md-6 col-lg-3">
                    <div class="footer_widget">
                        <h3 class="footer_title" style="font-size: 22px; font-weight: 400; color: #fff; text-transform: capitalize; margin-bottom: 40px;">
                            Tham gia với chúng tôi
                        </h3>
                        <p class="footer_text doanar" style="font-size: 16px; color: #B2B2B2; margin-bottom: 23px; font-weight: 400; line-height: 28px;">
                            <a class="popup-with-form" href="#test-form" style="font-weight: 500; color: #B2B2B2; text-decoration: none;">Đặt lịch hẹn</a>
                            <br />
                            <a href="#" style="font-weight: 500; color: #B2B2B2; text-decoration: none;">+10 378 478 8768</a>
                        </p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-lg-3">
                    <div class="footer_widget">
                        <h3 class="footer_title" style="font-size: 22px; font-weight: 400; color: #fff; text-transform: capitalize; margin-bottom: 40px;">
                            Địa chỉ
                        </h3>
                        <p class="footer_text" style="font-size: 16px; color: #B2B2B2; margin-bottom: 23px; font-weight: 400; line-height: 28px;">
                            154, Cầu Giấy, Hà Nội <br />
                            +10 367 267 2678 <br />
                            <a class="domain" href="#" style="color: #B2B2B2; font-weight: 400; text-decoration: none;">contact@barbershop.com</a>
                        </p>
                        <div class="socail_links">
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <li style="display: inline-block;">
                                    <a href="#" style="color: #A8A7A0; margin: 0 10px; font-size: 15px; text-decoration: none;">
                                        <i class="fa fa-facebook-square"></i>
                                    </a>
                                </li>
                                <li style="display: inline-block;">
                                    <a href="#" style="color: #A8A7A0; margin: 0 10px; font-size: 15px; text-decoration: none;">
                                        <i class="fa fa-twitter"></i>
                                    </a>
                                </li>
                                <li style="display: inline-block;">
                                    <a href="#" style="color: #A8A7A0; margin: 0 10px; font-size: 15px; text-decoration: none;">
                                        <i class="fa fa-instagram"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-6 col-lg-2">
                    <div class="footer_widget">
                        <h3 class="footer_title" style="font-size: 22px; font-weight: 400; color: #fff; text-transform: capitalize; margin-bottom: 40px;">
                            Liên kết nhanh
                        </h3>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li><a href="{{ route('site.home') }}" style="font-size: 16px; color: #BABABA; line-height: 42px; text-decoration: none; display: block;">Trang chủ</a></li>
                            <li><a href="{{ route('site.services.index') }}" style="font-size: 16px; color: #BABABA; line-height: 42px; text-decoration: none; display: block;">Dịch vụ</a></li>
                            <li><a href="{{ route('site.products.index') }}" style="font-size: 16px; color: #BABABA; line-height: 42px; text-decoration: none; display: block;">Sản phẩm</a></li>
                            <li><a href="{{ route('site.blog.index') }}" style="font-size: 16px; color: #BABABA; line-height: 42px; text-decoration: none; display: block;">Tin tức</a></li>
                            <li><a href="{{ route('site.contact.index') }}" style="font-size: 16px; color: #BABABA; line-height: 42px; text-decoration: none; display: block;">Liên hệ</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-lg-4">
                    <div class="footer_widget">
                        <h3 class="footer_title" style="font-size: 22px; font-weight: 400; color: #fff; text-transform: capitalize; margin-bottom: 40px;">
                            Giờ hoạt động
                        </h3>
                        <ul class="opening_time" style="list-style: none; padding: 0; margin: 0;">
                            <li style="font-size: 16px; color: #BABABA; line-height: 42px;">Thứ 2 - Thứ 6: 08:30 - 20:00</li>
                            <li style="font-size: 16px; color: #BABABA; line-height: 42px;">Thứ 7: 09:00 - 17:00</li>
                            <li style="font-size: 16px; color: #BABABA; line-height: 42px;">Chủ nhật: Nghỉ</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="copy-right_text" style="padding-top: 20px; padding-bottom: 30px; background: #4A3600;">
        <div class="container">
            <div class="footer_border" style="border-top: 1px solid rgba(255, 255, 255, 0.2); padding-bottom: 30px; margin-top: 0;"></div>
            <div class="row">
                <div class="col-xl-12">
                    <p class="copy_right text-center" style="font-size: 16px; color: #919191; margin-bottom: 0; font-weight: 400;">
                        Copyright &copy; {{ date('Y') }} All rights reserved | This template is made with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank" style="color: #BC9321; text-decoration: none;">Colorlib</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- footer-end -->

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
<script src="{{ asset('legacy/content/js/plugins.js') }}"></script>
<script src="{{ asset('legacy/content/js/gijgo.min.js') }}"></script>
<script src="{{ asset('legacy/content/js/main.js') }}"></script>
@stack('scripts')
