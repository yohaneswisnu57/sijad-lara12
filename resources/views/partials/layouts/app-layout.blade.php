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

    </head>

    <body data-layout="horizontal">

         <!-- Top Bar Start -->
         @include('partials.layouts.topbar')




        <div class="page-wrapper">
            <!-- Page Content-->
            <div class="page-content">

                <div class="container-fluid">
                    <!-- Page-Title -->
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="page-title-box">
                                <div class="float-right">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Crovex</a></li>
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                        <li class="breadcrumb-item active">Starter</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Starter</h4>
                            </div><!--end page-title-box-->
                        </div><!--end col-->
                    </div>
                    <!-- end page title end breadcrumb -->


                </div><!-- container -->

                @include('partials.layouts.footer')
            </div>
            <!-- end page content -->
        </div>
        <!-- end page-wrapper -->




        <!-- jQuery  -->



        @include('partials.layouts.vendor')


        <!-- App js -->
        @stack('script')


    </body>

</html>
