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

<!DOCTYPE html>
<html class="no-js" lang="vi">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>@yield('title', $setting->title ?? config('app.name'))</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('legacy/images/' . ($setting->file_ico ?? 'favicon.ico')) }}" />

    <!-- CSS here -->
    <link rel="stylesheet" href="{{ asset('legacy/content/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" />
    <link rel="stylesheet" href="{{ asset('legacy/content/css/toastr.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('legacy/content/css/owl.carousel.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('legacy/content/css/magnific-popup.css') }}" />
    <link rel="stylesheet" href="{{ asset('legacy/content/css/font-awesome.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('legacy/content/css/themify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('legacy/content/css/nice-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('legacy/content/css/flaticon.css') }}" />
    <link rel="stylesheet" href="{{ asset('legacy/content/css/gijgo.css') }}" />
    <link rel="stylesheet" href="{{ asset('legacy/content/css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('legacy/content/css/slicknav.css') }}" />
    <link rel="stylesheet" href="{{ asset('legacy/content/css/pgwslideshow.min.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy/content/css/rateit.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy/content/css/style.css') }}" />
    @stack('styles')
</head>

<body>
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
                                        <div class="dropdown mr-1" style="position: relative;">
                                            <button type="button" class="btn bg-transparent p-0 ml-2 text-white d-flex align-items-center" id="userDropdown" 
                                                    style="border: none; outline: none; cursor: pointer;">
                                                <span class="ml-1">{{ Str::limit(auth()->user()->name, 20) }}</span>
                                                <i class="fa fa-chevron-down ml-2" aria-hidden="true" style="font-size: 10px;"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="userDropdown" 
                                                style="min-width: 220px; border-radius: 8px; border: none; margin-top: 10px; padding: 0; display: none; position: absolute; right: 0; top: 100%; z-index: 1050;">
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
