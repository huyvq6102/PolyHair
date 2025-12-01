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
    <style>
        /* Enhanced Alert Styles */
        .alert {
            animation: slideInRight 0.3s ease-out;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .alert .fa-2x {
            font-size: 1.75rem;
        }
        
        .alert-heading {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
    </style>
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
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-left: 4px solid #28a745; border-radius: 0.35rem;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle fa-2x mr-3 text-success"></i>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading mb-1" style="font-weight: 600;">
                                        <i class="fas fa-check"></i> Thành công!
                                    </h5>
                                    <p class="mb-0">{{ session('success') }}</p>
                                </div>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-left: 4px solid #dc3545; border-radius: 0.35rem;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle fa-2x mr-3 text-danger"></i>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading mb-1" style="font-weight: 600;">
                                        <i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra!
                                    </h5>
                                    <p class="mb-0">{{ session('error') }}</p>
                                </div>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert" style="border-left: 4px solid #ffc107; border-radius: 0.35rem;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-2x mr-3 text-warning"></i>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading mb-1" style="font-weight: 600;">
                                        <i class="fas fa-exclamation"></i> Cảnh báo!
                                    </h5>
                                    <p class="mb-0">{{ session('warning') }}</p>
                                </div>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert" style="border-left: 4px solid #17a2b8; border-radius: 0.35rem;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle fa-2x mr-3 text-info"></i>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading mb-1" style="font-weight: 600;">
                                        <i class="fas fa-info"></i> Thông tin
                                    </h5>
                                    <p class="mb-0">{{ session('info') }}</p>
                                </div>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
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
    
    <!-- Auto-hide alerts after 5 seconds -->
    <script>
        $(document).ready(function() {
            // Auto-hide success alerts after 5 seconds
            $('.alert-success').delay(5000).fadeOut('slow', function() {
                $(this).alert('close');
            });
            
            // Auto-hide info alerts after 5 seconds
            $('.alert-info').delay(5000).fadeOut('slow', function() {
                $(this).alert('close');
            });
        });
    </script>
</body>
</html>
