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
