@php
    try {
        $setting = app(\App\Services\SettingService::class)->getFirst();
        $types = app(\App\Services\TypeService::class)->getAll();
        $employees = app(\App\Services\EmployeeService::class)->getAll();
        $services = app(\App\Services\ServiceService::class)->getAll();
        $wordTimes = app(\App\Services\WordTimeService::class)->getAll();
        $currentRoute = request()->route()->getName() ?? '';
        // Backward compatibility
        $barbers = $employees;
    } catch (\Exception $e) {
        $setting = null;
        $types = collect([]);
        $employees = collect([]);
        $services = collect([]);
        $wordTimes = collect([]);
        $currentRoute = '';
        $barbers = collect([]);
    }
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
    <link rel="stylesheet" href="{{ asset('legacy/content/css/header.css') }}" />
    <link rel="stylesheet" href="{{ asset('legacy/content/css/blade-custom.css') }}" />
    <link rel="stylesheet" href="{{ asset('legacy/content/css/custom-checkout.css') }}" />
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('styles')
</head>

<body>
    @include('layouts.partials.header')

    <main>
        @yield('content')
    </main>

    @include('layouts.partials.footer')
</body>
</html>

