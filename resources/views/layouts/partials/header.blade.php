@php
    try {
        $setting = app(\App\Services\SettingService::class)->getFirst();
        $types = app(\App\Services\TypeService::class)->getAll();
        $employees = app(\App\Services\EmployeeService::class)->getAll();
        $services = app(\App\Services\ServiceService::class)->getAll();
        $wordTimes = app(\App\Services\WordTimeService::class)->getAll();
        $currentRoute = request()->route()->getName() ?? '';
        $cartCount = count(session('cart', []));
        // Backward compatibility
        $barbers = $employees;
    } catch (\Exception $e) {
        $setting = null;
        $types = collect([]);
        $employees = collect([]);
        $services = collect([]);
        $wordTimes = collect([]);
        $currentRoute = '';
        $cartCount = 0;
        $barbers = collect([]);
    }
@endphp

<!-- header-start -->
<header>
        <div class="header-area" style="position: fixed; top: 0; left: 0; right: 0; width: 100%; z-index: 999; padding-top: 0;">
            <div id="sticky-header" class="main-header-area" style="background: #4A3600; padding: 15px 0;">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-xl-2 col-lg-2">
                            <div class="logo-img">
                                <a href="{{ route('site.home') }}">
                                    @php
                                        $logoFile = $setting->logo ?? 'logox.png';
                                        // Remove any leading slashes or legacy/images prefix if already present
                                        $logoFile = ltrim($logoFile, '/');
                                        $logoFile = str_replace('legacy/images/', '', $logoFile);
                                        $logoPath = 'legacy/images/' . $logoFile;
                                    @endphp
                                    <img src="{{ asset($logoPath) }}" alt="Logo" width="100" height="80" style="max-width: 100%; height: auto;" onerror="console.error('Logo not found: {{ $logoPath }}'); this.src='{{ asset('legacy/images/logox.png') }}';">
                                </a>
                            </div>
                        </div>
                        <div class="col-xl-10 col-lg-10">
                            <div class="menu_wrap d-none d-lg-block">
                                <div class="menu_wrap_inner d-flex align-items-center justify-content-end">
                                    <div class="main-menu">
                                        <nav>
                                            <ul id="navigation">
                                                <li><a class="{{ $currentRoute == 'site.home' ? 'active' : '' }}" href="{{ route('site.home') }}">TRANG CHỦ</a></li>
                                                <li><a class="{{ str_contains($currentRoute, 'service') ? 'active' : '' }}" href="{{ route('site.services.index') }}">DỊCH VỤ
                                                    <ul class="submenu">
                                                        @foreach($types as $type)
                                                            <li><a href="{{ route('site.services.index', ['type' => $type->id]) }}"><img src="{{ asset('legacy/images/categories/' . $type->images) }}" class="mr-2" alt="" width="20" height="20">{{ $type->name }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                                <li><a class="{{ str_contains($currentRoute, 'product') ? 'active' : '' }}" href="{{ route('site.products.index') }}">SẢN PHẨM</a></li>
                                                <li><a class="{{ str_contains($currentRoute, 'blog') ? 'active' : '' }}" href="{{ route('site.blog.index') }}">TIN TỨC</a></li>
                                                <li><a class="{{ str_contains($currentRoute, 'contact') ? 'active' : '' }}" href="{{ route('site.contact.index') }}">LIÊN HỆ</a></li>
                                            </ul>
                                        </nav>
                                    </div>
                                    <div class="icon cart-icon ml-3">
                                        <a href="{{ route('site.cart.index') }}">
                                            <i class="fa fa-shopping-bag text-white" aria-hidden="true"></i>
                                            <span class="bag">{{ $cartCount }}</span>
                                        </a>
                                    </div>
                                    @auth
                                        <div class="dropdown ml-3" style="position: relative;">
                                            <button type="button" class="btn bg-transparent p-0 text-white d-flex align-items-center" id="userDropdown" 
                                                    style="border: none; outline: none; cursor: pointer;">
                                                <span class="text-uppercase">{{ Auth::user()->name }}</span>
                                                <i class="fa fa-chevron-down ml-2" aria-hidden="true" style="font-size: 10px;"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="userDropdown" 
                                                style="min-width: 220px; border-radius: 8px; border: none; padding: 0; display: none; position: absolute; right: 0; top: 100%; z-index: 1050;">
                                                <a class="dropdown-item py-2 w-100 text-left" href="{{ route('site.customers.show', Auth::user()->id) }}" 
                                                   style="border: none; background: none; color: #000;">
                                                    <i class="fa fa-user mr-2" aria-hidden="true"></i>Thông tin cá nhân
                                                </a>
                                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item py-2 w-100 text-left" 
                                                            style="border: none; background: none; cursor: pointer; color: #dc3545;">
                                                        Đăng xuất
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            var dropdown = document.querySelector('#userDropdown').closest('.dropdown');
                                            var menu = dropdown.querySelector('.dropdown-menu');
                                            var icon = dropdown.querySelector('#userDropdown');
                                            var timeout;
                                            function showMenu() {
                                                clearTimeout(timeout);
                                                menu.style.display = 'block';
                                            }
                                            function hideMenu() {
                                                timeout = setTimeout(function() {
                                                    menu.style.display = 'none';
                                                }, 150);
                                            }
                                            icon.addEventListener('mouseenter', showMenu);
                                            icon.addEventListener('mouseleave', hideMenu);
                                            menu.addEventListener('mouseenter', showMenu);
                                            menu.addEventListener('mouseleave', hideMenu);
                                        });
                                        </script>
                                    @else
                                        <a href="{{ route('login') }}" class="text-white text-uppercase ml-3">Đăng nhập</a>
                                    @endauth
                                    
                                    <div class="book_room">
                                        <div class="book_btn">
                                            <a class="popup-with-form" href="#test-form">Đặt lịch ngay</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mobile_menu d-block d-lg-none"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</header>
<!-- header-end -->
