<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
            <a>
                <img src="../assets/images/logos/sdmlogowithtext.png" width="180" alt="" />
            </a>
            <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                <i class="ti ti-x fs-8"></i>
            </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
            <ul id="sidebarnav">

                @if (auth()->user()->role_id == '1')
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Home</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('dashboard.page') }}" aria-expanded="false">
                        <span>
                            <i class="ti ti-layout-dashboard"></i>
                        </span>
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Essential</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{route('account.page')}}" aria-expanded="false">
                        <span>
                            <i class="ti ti-user-plus"></i>
                        </span>
                        <span class="hide-menu">Tambah Akun</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{route('role.page')}}" aria-expanded="false">
                        <span>
                            <i class="ti ti-user-plus"></i>
                        </span>
                        <span class="hide-menu">Tambah Role</span>
                    </a>
                </li>
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Absensi</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{route('absensi.page')}}" aria-expanded="false">
                        <span>
                            <i class="ti ti-file-description"></i>
                        </span>
                        <span class="hide-menu">Absensi</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{route('izin.page')}}" aria-expanded="false">
                        <span>
                            <i class="ti ti-id"></i>
                        </span>
                        <span class="hide-menu">Pengajuan Izin</span>
                    </a>
                </li>
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Dokumen</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('exported-absensi.page') }}" aria-expanded="false">
                        <span>
                            <i class="ti ti-file"></i>
                        </span>
                        <span class="hide-menu">Export Absensi</span>
                    </a>
                </li>

                @elseif (auth()->user()->role_id == '2' || auth()->user()->role_id == '3')

                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Absensi</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{route('karyawan.absensi.page')}}" aria-expanded="false">
                        <span>
                            <i class="ti ti-file-description"></i>
                        </span>
                        <span class="hide-menu">Absensi</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{route('karyawan.kehadiran.page')}}" aria-expanded="false">
                        <span>
                            <i class="ti ti-history"></i>
                        </span>
                        <span class="hide-menu">Riwayat Kehadiran</span>
                    </a>
                </li>
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Izin</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{route('karyawan.izin-absen.page')}}" aria-expanded="false">
                        <span>
                            <i class="ti ti-checkup-list"></i>
                        </span>
                        <span class="hide-menu">Pengajuan Izin Absen</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{route('karyawan.history-izin')}}" aria-expanded="false">
                        <span>
                            <i class="ti ti-history"></i>
                        </span>
                        <span class="hide-menu">Riwayat Pengajuan Izin</span>
                    </a>
                </li>

                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Profile</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{route('karyawan.profile.page')}}" aria-expanded="false">
                        <span>
                            <i class="ti ti-user"></i>
                        </span>
                        <span class="hide-menu">Profile</span>
                    </a>
                </li>
                @endif

                <!-- <li class="sidebar-item">
                    <a class="sidebar-link" href="./ui-alerts.html" aria-expanded="false">
                        <span>
                            <i class="ti ti-alert-circle"></i>
                        </span>
                        <span class="hide-menu">Alerts</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="./ui-card.html" aria-expanded="false">
                        <span>
                            <i class="ti ti-cards"></i>
                        </span>
                        <span class="hide-menu">Card</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="./ui-forms.html" aria-expanded="false">
                        <span>
                            <i class="ti ti-file-description"></i>
                        </span>
                        <span class="hide-menu">Forms</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="./ui-typography.html" aria-expanded="false">
                        <span>
                            <i class="ti ti-typography"></i>
                        </span>
                        <span class="hide-menu">Typography</span>
                    </a>
                </li> -->
                <!-- <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">AUTH</span>
                </li>
                <li class="sidebar-item">
                    <a href="#"
                        class="sidebar-link"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <span>
                            <i class="ti ti-logout"></i>
                        </span>
                        <span class="hide-menu">Logout</span>
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="./authentication-register.html" aria-expanded="false">
                        <span>
                            <i class="ti ti-user-plus"></i>
                        </span>
                        <span class="hide-menu">Register</span>
                    </a>
                </li> -->
                <!-- <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">EXTRA</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="./icon-tabler.html" aria-expanded="false">
                        <span>
                            <i class="ti ti-mood-happy"></i>
                        </span>
                        <span class="hide-menu">Icons</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="./sample-page.html" aria-expanded="false">
                        <span>
                            <i class="ti ti-aperture"></i>
                        </span>
                        <span class="hide-menu">Sample Page</span>
                    </a>
                </li> -->
            </ul>
            <!-- <div class="unlimited-access hide-menu bg-light-primary position-relative mb-7 mt-5 rounded">
                <div class="d-flex">
                    <div class="unlimited-access-title me-3">
                        <h6 class="fw-semibold fs-4 mb-6 text-dark w-85">Upgrade to pro</h6>
                        <a href="https://adminmart.com/product/modernize-bootstrap-5-admin-template/" target="_blank" class="btn btn-primary fs-2 fw-semibold lh-sm">Buy Pro</a>
                    </div>
                    <div class="unlimited-access-img">
                        <img src="../assets/images/backgrounds/rocket.png" alt="" class="img-fluid">
                    </div>
                </div>
            </div> -->
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>