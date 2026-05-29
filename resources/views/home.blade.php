@extends('_layouts.dashboard')

@section('title', 'Dashboard')

@section('nav')
    <div class="col-sm-8">
        <div class="page-header d-flex align-items-center justify-content-end">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a data-toggle="tab" href="#today" class="btn btn-sm btn-primary active" value="today">Hari Ini</a>
                </li>
                <li class="nav-item">
                    <a data-toggle="tab" href="#week" class="btn btn-sm btn-primary" value="week">7 Hari Terakhir</a>
                </li>
                <li class="nav-item">
                    <a data-toggle="tab" href="#month" class="btn btn-sm btn-primary" value="month">Bulan Ini</a>
                </li>
                <li class="nav-item">
                    <a data-toggle="tab" href="#year" class="btn btn-sm btn-primary" value="year">Tahun Ini</a>
                </li>
            </ul>
        </div>
    </div>
@endsection
<!-- awal untuk jatuh tempo-->





<!-- akhir untuk menapilkan data  jatuh tempo-->

@section('content')
    
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="tab-content">

     <div class="tab-pane fade show active" id="today">
         <x-box-data type="today" />
     </div>   
     <div class="tab-pane fade" id="week">
         <x-box-data type="week" />
     </div>
     <div class="tab-pane fade" id="month">
         <x-box-data type="month" />
     </div>
     <div class="tab-pane fade" id="year">
         <x-box-data type="year" />
     </div> 
       
    </div>



    

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="h6 font-weight-bold mb-0 card-title">Aktivitas Terbaru</h3>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        {{-- <a class="nav-link actvity {{ $activity === 'penjualan' ? 'active' : '' }}" href="" value="penjualan">Penjualan</a> --}}
                        <a class="nav-link active" data-toggle="tab" href="#penjualan" value="penjualan">Penjualan</a>
                    </li>
                    <li class="nav-item">
                        {{-- <a class="nav-link actvity {{ $activity === 'pembelian' ? 'active' : '' }}" href="" value="pembelian">Pembelian</a> --}}
                        <a class="nav-link actvity" data-toggle="tab" href="#pembelian" value="pembelian">Pembelian</a>
                    </li>
                    <li class="nav-item">
                        {{-- <a class="nav-link actvity {{ $activity === 'pengeluaran' ? 'active' : '' }}" href="" value="pengeluaran">Pengeluaran</a> --}}
                        <a class="nav-link actvity" data-toggle="tab" href="#pengeluaran" value="pengeluaran">Pengeluaran</a>
                    </li>
                    <li class="nav-item">
                        {{-- <a class="nav-link actvity {{ $activity === 'cancel' ? 'active' : '' }}" href="" value="cancel">Batal Transaksi</a> --}}
                        <a class="nav-link actvity" data-toggle="tab" href="#cancel" value="cancel">Batal Transaksi</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade table-responsive show active" id="penjualan">
                        <table class="table table-striped w-100" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No Faktur</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Kasir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactionActivity as $activity)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $activity->idPenjualan }}</td>
                                        <td>{{ date('d M Y H:i A', strtotime($activity->tanggal)) }}</td>
                                        <td>Rp {{ number_format($activity->total) }}</td>
                                        <td>{{ $activity->user->nama }}</td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" align="center">Kosong</td>
                                        </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade table-responsive" id="pembelian">
                        <table class="table table-striped w-100" width="100%">
                            <thead>
                                <tr>
                                    <th>Id Pembelian</th>
                                    <th>Tanggal</th>
                                    <th>Nama Dist</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($buyActivity as $activity)
                                    <tr>
                                        <td>{{ $activity->idPembelian }}</td>
                                        <td>{{ date('d M Y H:i A', strtotime($activity->tanggal)) }}</td>
                                        <td>{{ $activity->namaDist }}</td>
                                        <td>Rp {{ number_format($activity->total) }}</td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" align="center">Kosong</td>
                                        </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade table-responsive" id="pengeluaran">
                        <table class="table table-striped w-100" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Pengeluaran</th>
                                    <th>Kategori</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($expendActivity as $activity)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ date('d M Y H:i A', strtotime($activity->tanggal)) }}</td>
                                        <td>Rp {{ number_format($activity->pengeluaran) }}</td>
                                        <td>{{ $activity->namaKategori }}</td>
                                        <td>{{ $activity->keterangan ?? '-' }}</td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" align="center">Kosong</td>
                                        </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade table-responsive" id="cancel">
                        <table class="table table-striped w-100" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No Faktur</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Kasir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cancelActivity as $activity)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $activity->idPenjualan }}</td>
                                        <td>{{ date('d M Y H:i A', strtotime($activity->tanggal)) }}</td>
                                        <td>Rp {{ number_format($activity->total) }}</td>
                                        <td>{{ $activity->user->nama }}</td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" align="center">Kosong</td>
                                        </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    
    </div>

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="h6 font-weight-bold mb-0 card-title">Jatuh Tempo  <span>-</span> <button class="btn btn-danger">{{ $totaljatuhtempo }}</button>     Tagihan</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped w-100" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No Faktur</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Distributor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mantap as $a)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $a->faktur }}</td>
                                <td>{{ date('d M Y H:i A', strtotime($a->jatuh_tempo)) }}</td>
                                <td>Rp {{ number_format($a->total) }}</td>
                                <td>{{ $a->namaDist }}</td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="6" align="center">Kosong</td>
                                </tr>
                        @endforelse
                    </tbody>
                </table>
              
            </div>
        </div>
    
    </div>




@endsection