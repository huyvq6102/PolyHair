@php
    $setting = app(\App\Services\SettingService::class)->getFirst();
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $setting->title ?? 'Admin Panel')</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('legacy/images/' . ($setting->file_ico ?? 'favicon.ico')) }}" />
    
    <!-- Custom fonts for this template-->
    <link href="{{ asset('legacy/admin/resource/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('legacy/admin/resource/vendor/select2.min.css') }}" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    
    <!-- Custom styles for this template-->
    <link href="{{ asset('legacy/admin/resource/css/sb-admin-2.css') }}" rel="stylesheet">
    <link href="{{ asset('legacy/admin/resource/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        @include('admin.partials.sidebar')

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                @include('admin.partials.topbar')

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @yield('content')
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            @include('admin.partials.footer')
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('legacy/admin/resource/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('legacy/admin/resource/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('legacy/admin/resource/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('legacy/admin/resource/js/sb-admin-2.min.js') }}"></script>

    <!-- Page level plugins -->
    <script src="{{ asset('legacy/admin/resource/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('legacy/admin/resource/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('legacy/admin/resource/vendor/select2.min.js') }}"></script>

    @stack('scripts')
</body>
</html>
