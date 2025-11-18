<!-- footer -->
<footer class="footer">
    <div class="footer_top">
        <div class="container">
            <div class="row">
                <div class="col-xl-3 col-md-6 col-lg-3">
                    <div class="footer_widget">
                        <h3 class="footer_title">
                            Tham gia với chúng tôi
                        </h3>
                        <p class="footer_text doanar">
                            <a class="popup-with-form" href="#test-form">Đặt lịch hẹn</a>
                            <br />
                            <a href="#">+10 378 478 8768</a>
                        </p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-lg-3">
                    <div class="footer_widget">
                        <h3 class="footer_title">
                            Địa chỉ
                        </h3>
                        <p class="footer_text">
                            154, Cầu Giấy, Hà Nội <br />
                            +10 367 267 2678 <br />
                            <a class="domain" href="#">contact@barbershop.com</a>
                        </p>
                        <div class="socail_links">
                            <ul>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-facebook-square"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-twitter"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-instagram"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-6 col-lg-2">
                    <div class="footer_widget">
                        <h3 class="footer_title">
                            Liên kết nhanh
                        </h3>
                        <ul>
                            <li><a href="{{ route('site.home') }}">Trang chủ</a></li>
                            <li><a href="{{ route('site.services.index') }}">Dịch vụ</a></li>
                            <li><a href="{{ route('site.products.index') }}">Sản phẩm</a></li>
                            <li><a href="{{ route('site.blog.index') }}">Tin tức</a></li>
                            <li><a href="{{ route('site.contact.index') }}">Liên hệ</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-lg-4">
                    <div class="footer_widget">
                        <h3 class="footer_title">
                            Giờ hoạt động
                        </h3>
                        <ul class="opening_time">
                            <li>Thứ 2 - Thứ 6: 08:30 - 20:00</li>
                            <li>Thứ 7: 09:00 - 17:00</li>
                            <li>Chủ nhật: Nghỉ</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="copy-right_text">
        <div class="container">
            <div class="footer_border"></div>
            <div class="row">
                <div class="col-xl-12">
                    <p class="copy_right text-center">
                        Copyright &copy; {{ date('Y') }} All rights reserved | This template is made with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a>
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
