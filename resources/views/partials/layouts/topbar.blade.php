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

                    <li class="has-submenu">
                        <a href="#">
                            <i class="dripicons-lock"></i>
                            <span>Transaction</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="#"><i class="dripicons-dot"></i>Menu 1</a></li>
                            <li><a href="#"><i class="dripicons-dot"></i>Menu 2</a></li>
                        </ul>
                    </li><!--end has-submenu-->
                </ul><!-- End navigation menu -->
            </div> <!-- end navigation -->
        </div> <!-- end container-fluid -->
    </div> <!-- end navbar-custom -->
</div>
<!-- Top Bar End -->
