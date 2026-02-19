<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>SIJAD - Login Sistem Informasi Jabatan Akademik</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta content="Sistem Informasi Jabatan Akademik Dosen" name="description" />
        <meta name="author" content="SIJAD" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

        <!-- App css -->
        @include('partials.layouts.vendorcss')

    </head>

    <body class="account-body accountbg">

        <!-- Log In page -->
        <div class="container">
            <div class="row vh-100 ">
                <div class="col-12 align-self-center">
                    <div class="auth-page">
                        <div class="card auth-card shadow-lg">
                            <div class="card-body">
                                <div class="px-3">
                                    <div class="auth-logo-box">
                                        <a href="{{ url('/') }}" class="logo logo-admin"><img src="{{ asset('assets/images/logo-sm.png') }}" height="55" alt="logo" class="auth-logo"></a>
                                    </div><!--end auth-logo-box-->

                                    <div class="text-center auth-logo-text">
                                        <h4 class="mt-0 mb-3 mt-5">Let's Get Started SIJAD</h4>
                                        <p class="text-muted mb-0">Sign in to continue to SIJAD.</p>
                                    </div> <!--end auth-logo-text-->

                                    {{-- Tampilkan pesan error validasi --}}
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

                                    {{-- Tampilkan pesan status (mis. setelah logout) --}}
                                    @if (session('status'))
                                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                        {{ session('status') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    @endif

                                    <form class="form-horizontal auth-form my-4" action="{{ route('login') }}" method="POST">
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

                                        <div class="form-group row mt-4">
                                            <div class="col-sm-6">
                                                <div class="custom-control custom-switch switch-success">
                                                    <input type="checkbox" class="custom-control-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                                    <label class="custom-control-label text-muted" for="remember">Remember me</label>
                                                </div>
                                            </div><!--end col-->
                                        </div><!--end form-group-->

                                        <div class="form-group mb-0 row">
                                            <div class="col-12 mt-2">
                                                <button class="btn btn-gradient-primary btn-round btn-block waves-effect waves-light" type="submit">Log In <i class="fas fa-sign-in-alt ml-1"></i></button>
                                            </div><!--end col-->
                                        </div> <!--end form-group-->
                                    </form><!--end form-->
                                </div><!--end /div-->
                            </div><!--end card-body-->
                        </div><!--end card-->
                    </div><!--end auth-page-->
                </div><!--end col-->
            </div><!--end row-->
        </div><!--end container-->
        <!-- End Log In page -->


      @include('partials.layouts.vendorjs')

      @stack('scripts')

    </body>

</html>