@php
    $setting = app(\App\Services\SettingService::class)->getFirst();
    $types = app(\App\Services\TypeService::class)->getAll();
    $employees = app(\App\Services\EmployeeService::class)->getAll();
    $services = app(\App\Services\ServiceService::class)->getAll();
    $wordTimes = app(\App\Services\WordTimeService::class)->getAll();
    $currentRoute = request()->route()->getName() ?? '';
    // Backward compatibility
    $barbers = $employees;
@endphp

<!-- header-start -->
<header>
        <div class="header-area ">
            <div id="sticky-header" class="main-header-area">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-xl-2 col-lg-2">
                            <div class="logo-img">
                                <a href="{{ route('site.home') }}">
                                    <img src="{{ asset('legacy/images/' . ($setting->logo ?? 'logox.png')) }}" alt="" width="100" height="80">
                                </a>
                            </div>
                        </div>
                        <div class="col-xl-10 col-lg-10">
                            <div class="menu_wrap d-none d-lg-block">
                                <div class="menu_wrap_inner d-flex align-items-center justify-content-end">
                                    <div class="main-menu">
                                        <nav>
                                            <ul id="navigation" class="mt-3">
                                                <li><a class="{{ $currentRoute == 'site.home' ? 'active' : '' }}" href="{{ route('site.home') }}">Trang chủ</a></li>
                                                <li><a class="{{ str_contains($currentRoute, 'service') ? 'active' : '' }}" href="{{ route('site.services.index') }}">Dịch vụ
                                                    <ul class="submenu">
                                                        @foreach($types as $type)
                                                            <li><a href="{{ route('site.services.index', ['type' => $type->id]) }}"><img src="{{ asset('legacy/images/categories/' . $type->images) }}" class="mr-2" alt="" width="20" height="20">{{ $type->name }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                                <li><a class="{{ str_contains($currentRoute, 'product') ? 'active' : '' }}" href="{{ route('site.products.index') }}">Sản phẩm</a></li>
                                                <li><a class="{{ str_contains($currentRoute, 'blog') ? 'active' : '' }}" href="{{ route('site.blog.index') }}">Tin tức</a></li>
                                                <li><a class="{{ str_contains($currentRoute, 'contact') ? 'active' : '' }}" href="{{ route('site.contact.index') }}">Liên hệ</a></li>
                                            </ul>
                                        </nav>
                                    </div>

                                    <div class="icon cart-icon">
                                        <a href="#"><i class="fa fa-shopping-bag text-white ml-2" aria-hidden="true"></i><span class="bag">0</span></a>
                                    </div>
                                    
                                    @auth
                                        <div class="dropdown no-arrow mr-1">
                                            <button type="button" class="btn bg-transparent p-0 ml-2 dropdown-toggle text-white" id="dropdownMenuOffset" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-offset="0,20">
                                                <i class="fa fa-user-o ml-2" aria-hidden="true"></i>
                                                {{ auth()->user()->name }}
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuOffset">
                                                @if(auth()->user()->isAdmin())
                                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">Trang quản trị</a>
                                                @endif
                                                <a class="dropdown-item" href="{{ route('profile.edit') }}">Tài khoản của tôi</a>
                                                <form method="POST" action="{{ route('logout') }}">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">Đăng xuất</button>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <a href="{{ route('login') }}" class="popup-with-form text-white text-uppercase ml-3 mt-1">Đăng nhập</a>
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
