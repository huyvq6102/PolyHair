<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item no-arrow">
            <a href="{{ route('site.home') }}" class="nav-link bg-success text-white h-50 mt-3 rounded">
                <i class="fas fa-calendar-alt mr-2"></i>Xem trang web
            </a>
        </li>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ auth()->user()->name }}</span>
                @if(auth()->user()->avatar)
                    <img class="img-profile rounded-circle" src="{{ asset('legacy/images/avatars/' . auth()->user()->avatar) }}" alt="User">
                @else
                    <i class="fas fa-user-circle fa-2x"></i>
                @endif
                <span class="ml-2 mr-2 text-black-50">{{ auth()->user()->email }}</span>
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Tài khoản
                </a>
                <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Cài đặt
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Đăng xuất
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>

