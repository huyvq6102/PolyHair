@php
    $setting = app(\App\Services\SettingService::class)->getFirst();
    $currentRoute = request()->route()->getName() ?? '';
    $serviceMenuActive = \Illuminate\Support\Str::startsWith($currentRoute, ['admin.services', 'admin.service-categories']);
    $isEmployee = auth()->user()->isEmployee();
    $userMenuActive = \Illuminate\Support\Str::startsWith($currentRoute, ['admin.users', 'admin.employees', 'admin.employee-skills']);
    $promotionMenuActive = \Illuminate\Support\Str::startsWith($currentRoute, 'admin.promotions');
@endphp

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center mb-3 pt-5"
        href="{{ $isEmployee ? route('admin.working-schedules.index') : route('admin.dashboard') }}">
        <img src="{{ asset('legacy/images/logox.png') }}" alt="logo" width="90" height="70">
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    @if(!$isEmployee)
        <!-- Nav Item - Dashboard (Visible to Admin Only) -->
        <li class="nav-item {{ $currentRoute == 'admin.dashboard' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Thống kê</span>
            </a>
        </li>
    @endif

    @if($isEmployee)
        <!-- Nav Item - Quản lý đơn đặt (Visible to Employee Only) -->
        <li class="nav-item {{ str_contains($currentRoute, 'employee.appointments') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('employee.appointments.index') }}">
                <i class="fas fa-fw fa-clipboard-list"></i>
                <span>Quản lý đơn đặt</span>
            </a>
        </li>
    @else
        <!-- Nav Item - Appointments (Visible to Admin Only) -->
        <li class="nav-item {{ str_contains($currentRoute, 'admin.appointments') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.appointments.index') }}">
                <i class="fas fa-fw fa-calendar-alt"></i>
                <span>Lịch hẹn</span>
            </a>
        </li>
    @endif

    <!-- Nav Item - Working schedules (Visible to All) -->
    <li class="nav-item {{ str_contains($currentRoute, 'working-schedule') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.working-schedules.index') }}">
            <i class="fas fa-fw fa-user-clock"></i>
            <span>Lịch nhân viên</span>
        </a>
    </li>

    <!-- Nav Item - Users (Visible to All Staff) -->
    <li class="nav-item {{ $userMenuActive ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages2"
            aria-expanded="{{ $userMenuActive ? 'true' : 'false' }}" aria-controls="collapsePages">
            <i class="fas fa-fw fa-folder"></i>
            <span>Thành viên</span>
        </a>
        <div id="collapsePages2" class="collapse {{ $userMenuActive ? 'show' : '' }}" aria-labelledby="headingPages"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item {{ \Illuminate\Support\Str::startsWith($currentRoute, 'admin.users') ? 'active' : '' }}"
                    href="{{ route('admin.users.index') }}">Quản lý người dùng</a>
                <a class="collapse-item {{ \Illuminate\Support\Str::startsWith($currentRoute, 'admin.employees') ? 'active' : '' }}"
                    href="{{ route('admin.employees.index') }}">Quản lý nhân viên</a>
                <a class="collapse-item {{ \Illuminate\Support\Str::startsWith($currentRoute, 'admin.employee-skills') ? 'active' : '' }}"
                    href="{{ route('admin.employee-skills.index') }}">Chuyên môn nhân viên</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Promotions (Visible to All Staff) -->
    <li class="nav-item {{ $promotionMenuActive ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.promotions.index') }}">
            <i class="fas fa-fw fa-gift"></i>
            <span>Khuyến mãi</span>
        </a>
    </li>

    <!-- Nav Item - Orders (Visible to All Staff) -->
    <li class="nav-item {{ str_contains($currentRoute, 'payment') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.payments.index') }}">
            <i class="fas fa-fw fa-shopping-cart"></i>
            <span>Hóa đơn</span>
        </a>
    </li>

    @if(!$isEmployee)
        <!-- Admin Only Items -->

        <!-- Nav Item - Services -->
        <li class="nav-item {{ $serviceMenuActive ? 'active' : '' }}">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseServices"
                aria-expanded="{{ $serviceMenuActive ? 'true' : 'false' }}" aria-controls="collapseServices">
                <i class="fas fa-fw fa-folder"></i>
                <span>Dịch vụ</span>
            </a>
            <div id="collapseServices" class="collapse {{ $serviceMenuActive ? 'show' : '' }}"
                aria-labelledby="headingServices" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item {{ \Illuminate\Support\Str::startsWith($currentRoute, 'admin.service-categories') ? 'active' : '' }}"
                        href="{{ route('admin.service-categories.index') }}">Danh mục dịch vụ</a>
                    <a class="collapse-item {{ \Illuminate\Support\Str::startsWith($currentRoute, 'admin.services') ? 'active' : '' }}"
                        href="{{ route('admin.services.index') }}">Quản lý dịch vụ</a>
                </div>
            </div>
        </li>

        <li class="nav-item {{ str_contains($currentRoute, 'reviews') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.reviews.index') }}">
                <i class="fas fa-fw fa-gift"></i>
                <span>Bình luận</span>
            </a>
        </li>
    @endif

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
