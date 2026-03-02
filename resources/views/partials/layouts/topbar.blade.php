<div class="topbar">

    <div class="topbar-inner">
        <!-- LOGO -->
        @include('partials.layouts.logo')
        <!--end logo-->
        <!-- Navbar -->
        @include('partials.layouts.navbar')
    </div><!--topbar-inner-->

    <div class="navbar-custom-menu">
        <div class="container-fluid">
            <div id="navigation">
                <!-- Navigation Menu-->
                <ul class="navigation-menu">
                    <li class="has-submenu">
                        <a href="{{ route('dashboard') }}">
                            <i class="dripicons-graph-bar"></i>
                            <span>Dashboard</span>
                        </a>
                    </li><!--end has-submenu-->

                    <li class="has-submenu {{ Request::is('unsur-penilaian*') ? 'active' : '' }}">
                        <a href="#">
                            <i class="dripicons-view-thumb"></i>
                            <span>Master Data</span>
                        </a>
                        <ul class="submenu">
                            <li class="{{ Request::is('unsur-penilaian*') ? 'active' : '' }}">
                                <a href="{{ route('unsur-penilaian.index') }}"><i class="dripicons-dot"></i>Unsur Penilaian</a>
                            </li>
                        </ul><!--end submenu-->
                    </li><!--end has-submenu-->

                    <li class="has-submenu {{ Request::is('kegiatan-dosen*') || Request::is('kelas-mengajar*') ? 'active' : '' }}">
                        <a href="#">
                            <i class="dripicons-checklist"></i>
                            <span>Transaksi</span>
                        </a>
                        <ul class="submenu">
                            <li class="{{ Request::is('kegiatan-dosen*') ? 'active' : '' }}">
                                <a href="{{ route('kegiatan-dosen.index') }}"><i class="dripicons-dot"></i>Kegiatan Dosen</a>
                            </li>
                            <li class="{{ Request::is('kelas-mengajar*') ? 'active' : '' }}">
                                <a href="{{ route('kelas-mengajar.index') }}"><i class="dripicons-dot"></i>Kelas Mengajar</a>
                            </li>
                            <li class="{{ Request::is('matkul-pengajar*') ? 'active' : '' }}">
                                <a href="{{ route('matkul-pengajar.index') }}"><i class="dripicons-dot"></i>Mata Kuliah Pengajar</a>
                            </li>
                        </ul>
                    </li><!--end has-submenu-->

                </ul><!-- End navigation menu -->
            </div> <!-- end navigation -->
        </div> <!-- end container-fluid -->
    </div> <!-- end navbar-custom -->
</div>
<!-- Top Bar End -->
