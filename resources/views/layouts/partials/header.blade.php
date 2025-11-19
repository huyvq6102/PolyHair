@php
    $setting = app(\App\Services\SettingService::class)->getFirst();
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
                                    <img src="{{ asset('legacy/images/' . ($setting->logo ?? 'logox.png')) }}" alt="" width="100" height="80">
                                </a>
                            </div>
                        </div>
                        <div class="col-xl-10 col-lg-10">

                            @include('layouts.partials.menu')

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
