<header id="header" class="header">

    <div class="header-menu">

        <div class="col-sm-7">
            <a id="menuToggle" class="menutoggle pull-left"><i class="fa fa fa-tasks"></i></a>

            @can('isAdminGudang')
            <div class="header-left">
                <div class="dropdown for-notification">
                    <button class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell"></i>
                        <span class="count bg-danger" style="width: auto; height: auto; padding: 2px 6px; right: {{ $limit > 10 ? '-10px' : 0 }}">{{ $limit }}</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg border">
                        <a href="{{ route('notification') }}" class="dropdown-item media bg-danger text-white">
                            <i class="fa fa-exclamation-triangle"></i>
                            {{ $limit }} produk kehabisan stok
                        </a>
                    </div>
                </div>
            </div>
            @endcan
        </div>

        <div class="col-sm-5">
            <div class="user-area dropdown float-right">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-user mt-2"></i>
                </a>

                <div class="user-menu dropdown-menu">
                    <span>{{ Auth::user()->nama }}</span>

                    <div class="dropdown-divider"></div>

                    <a class="nav-link" href="{{ route('change_password') }}"><i class="fa fa-key"></i> Ganti Password</a>
                    @can('isAdmin')
                    <a class="nav-link" href="{{ route('setting.index') }}"><i class="fa fa-cog"></i> Settings</a>
@endcan
                    <a class="nav-link" href="{{ route('logout') }}"><i class="fa fa-power-off"></i> Logout</a>
                </div>
            </div>

        </div>
    </div>

</header>