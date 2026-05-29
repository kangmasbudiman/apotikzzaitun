<aside id="left-panel" class="left-panel">
<nav class="navbar navbar-expand-sm navbar-default">

    <div class="navbar-header">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa fa-bars"></i>
        </button>
        <a class="navbar-brand" href="{{ route('home') }}">{{ site('nama_toko') }}</a>
    </div>

    <div id="main-menu" class="main-menu collapse navbar-collapse">
        <ul class="nav navbar-nav">

            @can('isAdmin')
                <li class="{{ active('/') }}">
                    <a href="{{ route('home') }}"> <i class="menu-icon fa fa-dashboard"></i>Dashboard</a>
                </li>
            @endcan

            @can('isAdminGudang')
            <li class="menu-item-has-children dropdown {{ active('stuff', 'group', 'active') }} {{ active('rack', 'group', 'active') }} {{ active('distributor', 'group', 'active') }} {{ active('category', 'group', 'active') }} {{ active('barcode', 'group', 'active') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="menu-icon fa fa-table"></i>
                    Master Data
                </a>
                <ul class="sub-menu children dropdown-menu">
                    <li>
                        <i class="fa fa-    "></i>
                        <a href="{{ route('stuff.index') }}"></i>Obat</a>
                    </li>
                    <li>
                        <i class="menu-icon fa fa-tag"></i>
                        <a href="{{ route('category.index') }}">Kategori</a>
                    </li>
                    <li>
                        <i class="menu-icon fa fa-book"></i>
                        <a href="{{ route('rack.index') }}">Rak</a>
                    </li>
                    <li>
                        <i class="menu-icon fa fa-truck"></i>
                        <a href="{{ route('distributor.index') }}">Distributor</a>
                    </li>
                    <!--
                    <li>
                        <i class="menu-icon fa fa-money"></i>
                        <a href="{{ route('daylicash.index') }}">Kas Harian</a>
                    </li>
                -->
                    <li>
                        <i class="menu-icon fa fa-money"></i>
                        <a href="{{ route('cash.index') }}">Kas Harian</a>
                    </li>
                    <li>
                    @can('isAdmin')
                        <i class="menu-icon fa fa-truck"></i>
                        <a href="{{ route('opname.index') }}">Stok Opname</a>
                        @endcan
                    </li>
                </ul>
            </li>
            <li class="menu-item-has-children dropdown {{ active('stock', 'group', 'active') }} {{ active('detail_stock', 'group', 'active') }} {{ active('opname', 'group', 'active') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="menu-icon fa fa-truck"></i>
                    Pembelian
                </a>
                <ul class="sub-menu children dropdown-menu">
                
                    <li>
                        <i class="menu-icon fa fa-truck"></i>
                        <a href="{{ route('stock.index') }}">Pembelian</a>
                    </li>
                    
                    @can('isAdmin')
                    <li>
                        <i class="menu-icon fa fa-truck"></i>
                        <a href="{{ route('detail_stock.index') }}">Riwayat Pembelian</a>
                    </li>
                    
                    @endcan
                </ul>
            </li>
            @endcan
            @can('isAdminKasir')
            <li class="menu-item-has-children dropdown {{ active('transaction', 'group', 'active') }} {{ active('detail_transaction', 'group', 'active') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="menu-icon fa fa-tasks"></i>
                    Transaksi
</a>
<ul class="sub-menu children dropdown-menu">
            
                    <li>
                        <i class="menu-icon fa fa-money"></i>
                        <a href="{{ route('transaction.index') }}">Transaksi</a>
                    </li>
                     <li>
                        <i class="menu-icon fa fa-money"></i>
                        <a href="{{ route('detail_transaction.searchobat') }}">Cari Riwayat Obat</a>
                    </li>
                     <li>
                        <i class="menu-icon fa fa-money"></i>
                        <a href="{{ route('detail_transaction.searchnama') }}">Cari nama</a>
                    </li>
                    <li>
                    
                    @can('isAdmin')
                        <i class="menu-icon fa fa-money"></i>
                        <a href="{{ route('detail_transaction.index') }}">Riwayat Transaksi</a>
                    </li>
                    @endcan
  </ul>
            </li>
            @endcan
            @can('isAdmin')
            <li class="menu-item-has-children dropdown {{ active('finance', false, 'active') }} {{ active('category_finance', false, 'active') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    
                    <i class="menu-icon fa fa-print"></i>
                    Pengeluaran
                </a>
                <ul class="sub-menu children dropdown-menu">
                <li>
                <i class="menu-icon fa fa-print"></i>
                            <a href="{{ route('category_finance.index') }}"></i>Kategori Pengeluaran</a>
                        </li>
                <li>
                <i class="menu-icon fa fa-print"></i>
                            <a href="{{ route('finance.index') }}"></i>Pengeluaran</a>
                        </li>
                        </ul>
            </li>
                    @endcan

            @can('isAdmin')
            <li class="menu-item-has-children dropdown {{ active('report/accumulation', true, 'active') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="menu-icon fa fa-calculator"></i>
                    Akumulasi
                </a>
                <ul class="sub-menu children dropdown-menu">
                    <li>
                        <i class="menu-icon fa fa-calculator"></i>
                        <a href="{{ route('report.accumulation') }}">Laba Kotor</a>
                    </li>
                    <li>
                        <i class="menu-icon fa fa-calculator"></i>
                        <a href="{{ route('report.accumulation.new') }}">Laba Bersih</a>
                    </li>
                </ul>
            </li>
            @endcan

            @can('isAdmin')
            <li class="menu-item-has-children dropdown {{ active('report/transaction') }} {{ active('report/expend') }} {{ active('report/stock') }} {{ active('report/sell') }} {{ active('report/buy') }} {{ active('report/ppn') }} {{ active('report/opname') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="menu-icon fa fa-file"></i>
                    Laporan
                </a>
                <ul class="sub-menu children dropdown-menu">
                    @can('isAdmin')
                    <li>
                        <i class="menu-icon fa fa-file"></i>
                        <a href="{{ route('report.transaction') }}">Laporan Pendapatan</a>
                    </li>
                    <li>
                        <i class="menu-icon fa fa-file"></i>
                        <a href="{{ route('report.sell') }}">Laporan Penjualan</a>
                    </li>
                    <li>
                        <i class="menu-icon fa fa-file"></i>
                        <a href="{{ route('report.expend') }}">Laporan Pengeluaran</a>
                    </li>
                    @endcan
                    <li>
                        <i class="menu-icon fa fa-file"></i>
                        <a href="{{ route('report.buy') }}">Laporan Pembelian</a>
                    </li>
                    <li>
                        <i class="menu-icon fa fa-file"></i>
                        <a href="{{ route('report.stock') }}">Laporan Stok</a>
                    </li>
                </ul>
            </li>
            @endcan

            <li id="laporan" class="menu-item-has-children dropdown {{ active('report/transaction/today', 'group', 'active') }} {{ active('report/stock/today', 'group', 'active') }} {{ active('report/sell/today', 'group', 'active') }} {{ active('report/buy/today', 'group', 'active') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="menu-icon fa fa-calendar"></i>
                    Laporan Harian
                </a>
                <ul class="sub-menu children dropdown-menu">
                    @can('isAdminKasir')
                    <li>
                        <i class="menu-icon fa fa-file"></i>
                        <a href="{{ route('report.transaction.today') }}" class="today">Laporan Pendapatan</a>
                    </li>
                    <li>
                        <i class="menu-icon fa fa-file"></i>
                        <a href="{{ route('report.sell.today') }}" class="today">Laporan Penjualan</a>
                    </li>
                    @endcan
                    @can('isAdminGudang')
                    <li>
                        <i class="menu-icon fa fa-file"></i>
                        <a href="{{ route('report.buy.today') }}" class="today">Laporan Pembelian</a>
                    </li>
                    <li>
                        <i class="menu-icon fa fa-file"></i>
                        <a href="{{ route('report.stock.today') }}" class="today">Laporan Stok</a>
                    </li>
                    @endcan
                </ul>
            </li>

            <li id="bulanan" class="menu-item-has-children dropdown {{ active('report/transaction/month', 'group', 'active') }} {{ active('report/stock/month', 'group', 'active') }} {{ active('report/sell/month', 'group', 'active') }} {{ active('report/buy/month', 'group', 'active') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="menu-icon fa fa-calendar"></i>
                    Laporan Bulanan
                </a>
                <ul class="sub-menu children dropdown-menu">
                    @can('isAdminKasir')
                    <li>
                        <i class="menu-icon fa fa-file"></i>
                        <a href="{{ route('report.transaction.month') }}" class="today">Laporan Pendapatan</a>
                    </li>
                    <li>
                        <i class="menu-icon fa fa-file"></i>
                        <a href="{{ route('report.sell.month') }}" class="today">Laporan Penjualan</a>
                    </li>
                    @endcan
                    @can('isAdminGudang')
                    <li>
                        <i class="menu-icon fa fa-file"></i>
                        <a href="{{ route('report.buy.month') }}" class="today">Laporan Pembelian</a>
                    </li>
                    <li>
                        <i class="menu-icon fa fa-file"></i>
                        <a href="{{ route('report.stock.month') }}" class="today">Laporan Stok</a>
                    </li>
                    @endcan
                </ul>
            </li>

            <li class="menu-item-has-children dropdown {{ active('change_password', false, 'active') }} {{ active('ppn', false, 'active') }} {{ active('setting', false, 'active') }} {{ active('user', 'group', 'active') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="menu-icon fa fa-cog"></i>
                    Pengaturan
                </a>
                <ul class="sub-menu children dropdown-menu">
                    <li>
                        <i class="menu-icon fa fa-key"></i>
                        <a href="{{ route('change_password') }}">Ganti Password</a>
                    </li>
                    @can('isAdmin')
                        <li>
                            <i class="menu-icon fa fa-cog"></i>
                            <a href="{{ route('setting.index') }}">Toko</a>
                        </li>
                        <li>
                            <i class="menu-icon fa fa-dollar"></i>
                            <a href="{{ route('ppn.index') }}">PPN</a>
                        </li>
                        <li>
                            <i class="menu-icon fa fa-user"></i>
                            <a href="{{ route('user.index') }}">Pengguna</a>
                        </li>
                    @endcan
                </ul>
            </li>
        </ul>
    </div><!-- /.navbar-collapse -->
</nav>
</aside>
