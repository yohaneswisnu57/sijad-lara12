<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>SIJAD - Login | Universitas Katolik Widya Mandala Surabaya</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta content="Sistem Informasi Jabatan Akademik Dosen — UKWMS" name="description" />
        <meta name="author" content="SIJAD UKWMS" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

        <!-- App css -->
        @include('partials.layouts.vendorcss')

        <style>
            /* Hanya ganti warna tombol submit agar sesuai warna primer Crovex */
            .btn-gradient-primary {
                background: linear-gradient(135deg, #1761fd 0%, #1761fd 100%);
            }
        </style>
    </head>

    <body class="account-body accountbg">

        <!-- Log In page -->
        <div class="container">
            <div class="row vh-100 d-flex justify-content-center">
                <div class="col-12 align-self-center">
                    <div class="auth-page">
                        <div class="card auth-card shadow-lg">
                            <div class="card-body">
                                <div class="px-3">

                                    {{-- Logo UKWMS --}}
                                    <div class="auth-logo-box">
                                        <a href="{{ url('/') }}" class="logo logo-admin">
                                            <img src="{{ asset('assets/images/logo-sm-wm.png') }}"
                                                 height="60"
                                                 alt="Universitas Katolik Widya Mandala Surabaya"
                                                 class="auth-logo">
                                        </a>
                                    </div><!--end auth-logo-box-->

                                    <div class="text-center auth-logo-text">
                                        <h4 class="mb-1 mt-5">SIJAD — UKWMS</h4>
                                        <p class="text-muted mb-0">
                                            Sistem Informasi Jabatan Akademik Dosen<br>
                                            <small>Masuk untuk melanjutkan</small>
                                        </p>
                                    </div><!--end auth-logo-text-->

                                    {{-- Error validasi --}}
                                    @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                        <ul class="mb-0 pl-3">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    @endif

                                    {{-- Status (setelah logout) --}}
                                    @if (session('status'))
                                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                        {{ session('status') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    @endif

                                    <form class="form-horizontal auth-form my-4"
                                          action="{{ route('login') }}" method="POST">
                                        @csrf

                                        <div class="form-group">
                                            <label for="userid">User ID</label>
                                            <div class="input-group mb-3">
                                                <span class="auth-form-icon">
                                                    <i class="dripicons-user"></i>
                                                </span>
                                                <input type="text"
                                                       class="form-control @error('userid') is-invalid @enderror"
                                                       id="userid"
                                                       name="userid"
                                                       value="{{ old('userid') }}"
                                                       placeholder="Masukkan User ID"
                                                       required
                                                       autofocus>
                                            </div>
                                            @error('userid')
                                                <span class="text-danger font-12">{{ $message }}</span>
                                            @enderror
                                        </div><!--end form-group-->

                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <div class="input-group mb-3">
                                                <span class="auth-form-icon">
                                                    <i class="dripicons-lock"></i>
                                                </span>
                                                <input type="password"
                                                       class="form-control @error('password') is-invalid @enderror"
                                                       id="password"
                                                       name="password"
                                                       placeholder="Masukkan Password"
                                                       required>
                                            </div>
                                            @error('password')
                                                <span class="text-danger font-12">{{ $message }}</span>
                                            @enderror
                                        </div><!--end form-group-->

                                        <div class="form-group mb-0 row">
                                            <div class="col-12 mt-2">
                                                <button class="btn btn-gradient-primary btn-round btn-block waves-effect waves-light"
                                                        type="submit">
                                                    Masuk <i class="fas fa-sign-in-alt ml-1"></i>
                                                </button>
                                            </div><!--end col-->
                                        </div><!--end form-group-->

                                    </form><!--end form-->

                                </div><!--end /div-->
                            </div><!--end card-body-->
                        </div><!--end card-->

                        <div class="account-social text-center mt-2 pt-2 text-muted small">
                            &copy; {{ date('Y') }} Universitas Katolik Widya Mandala Surabaya
                        </div>

                    </div><!--end auth-page-->
                </div><!--end col-->
            </div><!--end row-->
        </div><!--end container-->
        <!-- End Log In page -->

        @include('partials.layouts.vendorjs')
        @stack('scripts')

    </body>

</html>