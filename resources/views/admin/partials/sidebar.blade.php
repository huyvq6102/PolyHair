@php
    $setting = app(\App\Services\SettingService::class)->getFirst();
    $currentRoute = request()->route()->getName() ?? '';
    $serviceMenuActive = \Illuminate\Support\Str::startsWith($currentRoute, ['admin.services', 'admin.service-categories']);
    $isEmployee = auth()->user()->isEmployee();
@endphp

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center mb-3 pt-5" href="{{ $isEmployee ? route('employee.appointments.index') : route('admin.dashboard') }}">
        <img src="{{ asset('legacy/images/' . ($setting->logo ?? 'logox.png')) }}" alt="logo" width="90" height="70">
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    @if($isEmployee)
        <!-- Employee Menu Items -->
        <!-- Nav Item - Employee Appointments -->
        <li class="nav-item {{ str_contains($currentRoute, 'employee.appointments') && $currentRoute !== 'employee.appointments.create' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('employee.appointments.index') }}">
                <i class="fas fa-fw fa-clipboard-list"></i>
                <span>Quản lý đơn đặt</span>
            </a>
        </li>
        
        <!-- Nav Item - Create Appointment -->
        <li class="nav-item {{ $currentRoute === 'employee.appointments.create' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('employee.appointments.create') }}">
                <i class="fas fa-fw fa-calendar-plus"></i>
                <span>Tạo lịch hẹn</span>
            </a>
        </li>
    @else
        <!-- Admin Menu Items -->
    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ $currentRoute == 'admin.dashboard' ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Bản tin</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Nav Item - Services -->
    <li class="nav-item {{ $serviceMenuActive ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseServices" aria-expanded="{{ $serviceMenuActive ? 'true' : 'false' }}" aria-controls="collapseServices">
            <i class="fas fa-fw fa-folder"></i>
            <span>Dịch vụ</span>
        </a>
        <div id="collapseServices" class="collapse {{ $serviceMenuActive ? 'show' : '' }}" aria-labelledby="headingServices" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item {{ \Illuminate\Support\Str::startsWith($currentRoute, 'admin.service-categories') ? 'active' : '' }}" href="{{ route('admin.service-categories.index') }}">Danh mục dịch vụ</a>
                <a class="collapse-item {{ \Illuminate\Support\Str::startsWith($currentRoute, 'admin.services') ? 'active' : '' }}" href="{{ route('admin.services.index') }}">Quản lý dịch vụ</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Appointments -->
    <li class="nav-item {{ str_contains($currentRoute, 'appointment') && !str_contains($currentRoute, 'employee') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.appointments.index') }}">
            <i class="fas fa-fw fa-calendar-alt"></i>
            <span>Lịch hẹn</span>
        </a>
    </li>

    <!-- Nav Item - Working schedules -->
    <li class="nav-item {{ str_contains($currentRoute, 'working-schedule') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.working-schedules.index') }}">
            <i class="fas fa-fw fa-user-clock"></i>
            <span>Lịch nhân viên</span>
        </a>
    </li>

    <!-- Nav Item - Users -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages2" aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-fw fa-folder"></i>
            <span>Thành viên</span>
        </a>
        <div id="collapsePages2" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.users.index') }}">Quản lý người dùng</a>
                <a class="collapse-item" href="{{ route('admin.employees.index') }}">Quản lý nhân viên</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - News -->
    <li class="nav-item {{ str_contains($currentRoute, 'news') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.news.index') }}">
            <i class="fas fa-fw fa-newspaper"></i>
            <span>Tin tức</span>
        </a>
    </li>

    <!-- Nav Item - Products -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
            <i class="far fa-file-alt"></i>
            <span>Sản phẩm</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.categories.index') }}">Quản lý danh mục</a>
                <a class="collapse-item" href="{{ route('admin.products.index') }}">Quản lý sản phẩm</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Orders -->
    <li class="nav-item {{ str_contains($currentRoute, 'order') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.orders.index') }}">
            <i class="fas fa-fw fa-shopping-cart"></i>
            <span>Hóa đơn</span>
        </a>
    </li>

    <!-- Nav Item - Settings -->
    <li class="nav-item {{ str_contains($currentRoute, 'setting') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.settings.index') }}">
            <i class="fas fa-fw fa-cog"></i>
            <span>Quản lý website</span>
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

