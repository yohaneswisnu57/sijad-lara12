<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>SIJAD - Admin Dashboard Sistem Informasi Jabatan Akademik</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">


        <!-- App css -->
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/jquery-ui.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/metisMenu.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

        {{-- ── SIJAD Mobile Logo Override ────────────────────────────────────── --}}
        <style>
            /*
             * Fix: Horizontal layout mobile — logo tidak muncul karena template
             * memaksa topbar-left menjadi 70px dan logo-lg display:none !important.
             * Override ini menampilkan logo penuh pada semua ukuran.
             */

            /* Desktop & tablet (> 991px): topbar-left 160px, logo horizontal penuh */
            [data-layout="horizontal"] .topbar .topbar-left {
                width: 170px;
            }
            [data-layout="horizontal"] .topbar .topbar-left .logo .logo-sm {
                display: none !important;
            }
            [data-layout="horizontal"] .topbar .topbar-left .logo .logo-lg {
                display: inline-block !important;
                height: 40px !important;
                width: auto;
                max-width: 155px;
                object-fit: contain;
            }
            [data-layout="horizontal"] .topbar .topbar-left .logo .logo-lg.logo-light {
                display: none !important;
            }
            [data-layout="horizontal"] .navbar-custom {
                margin-left: 170px;
            }

            /* Mobile (≤ 991px): topbar-left auto, tampilkan logo-sm yang cukup besar */
            @media (max-width: 991px) {
                [data-layout="horizontal"] .topbar .topbar-left {
                    width: auto !important;
                    padding: 0 8px;
                    display: flex;
                    align-items: center;
                }
                [data-layout="horizontal"] .topbar .topbar-left .logo {
                    line-height: normal;
                    display: flex;
                    align-items: center;
                }
                /* Pada mobile, sembunyikan logo-lg, tampilkan logo-sm */
                [data-layout="horizontal"] .topbar .topbar-left .logo .logo-lg {
                    display: none !important;
                }
                [data-layout="horizontal"] .topbar .topbar-left .logo .logo-sm {
                    display: inline-block !important;
                    height: 38px !important;
                    width: auto;
                    object-fit: contain;
                }
                [data-layout="horizontal"] .navbar-custom {
                    margin-left: 0 !important;
                    flex: 1;
                }
            }

            /* Avatar profil: pastikan gambar user tampil proporsional */
            .nav-user img {
                height: 36px !important;
                width: 36px !important;
                object-fit: cover;
                border-radius: 50%;
            }
        </style>
        @stack('css')
    </head>

    <body data-layout="horizontal">

         <!-- Top Bar Start -->
         @include('partials.layouts.topbar')




        <div class="page-wrapper">
            <!-- Page Content-->
            <div class="page-content">

                <div class="container-fluid">
                    <!-- Page-Title -->
                    @yield('content')


                </div><!-- container -->

                @include('partials.layouts.footer')
            </div>
            <!-- end page content -->
        </div>
        <!-- end page-wrapper -->




        <!-- jQuery  -->



        @include('partials.layouts.vendorjs')


        <!-- App js -->
        <!-- App js -->
        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            /* Penyesuaian Ukuran Toast SweetAlert2 */
            .swal2-popup.swal2-toast {
                padding: 0.625rem 1rem !important; /* Padding proporsional */
                width: auto !important;
            }
            .swal2-popup.swal2-toast .swal2-title {
                font-size: 1.15rem !important; /* Font judul tidak terlalu besar */
                margin: 0 !important;
            }
            .swal2-popup.swal2-toast .swal2-icon {
                width: 1.75em !important;      /* Perkecil icon dari default (biasanya 2em) */
                height: 1.75em !important;
                margin: 0 0.75rem 0 0 !important;
            }
            .swal2-popup.swal2-toast .swal2-html-container {
                font-size: 0.9rem !important;
            }
        </style>
        
        <script>
            // Toast Configuration
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // Display Toast from Session
            @if(session('success'))
                Toast.fire({
                    icon: 'success',
                    title: '{{ session('success') }}'
                });
            @endif

            @if(session('error'))
                Toast.fire({
                    icon: 'error',
                    title: '{{ session('error') }}'
                });
            @endif
        </script>

        @stack('scripts')


    </body>

</html>
