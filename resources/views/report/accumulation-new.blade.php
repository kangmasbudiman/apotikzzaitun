@extends('_layouts.app')

@section('title', 'Akumulasi')

@section('content')
    
            
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="h6 mb-0 font-weight-bold card-title">Akumulasi Laba / Rugi (Bersih)</h2>
            <form action="{{ route('report.accumulation.print.new') }}" target="_blank">
                <input type="hidden" name="dari" value="{{ request()->dari }}">
                <input type="hidden" name="sampai" value="{{ request()->sampai }}">
                <button class="btn btn-sm btn-danger">Print</button>
            </form>
        </div>
        <div class="card-body border-bottom">
           <form action="{{ route('report.accumulation.new') }}">
                <div class="form-row form-group">
                    <div class="col-sm-3">
                        <label>Dari Tanggal</label>
                        <input type="date" name="dari" class="form-control" value="{{ request()->dari }}">
                    </div>
                    <div class="col-sm-3">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="sampai" class="form-control" value="{{ request()->sampai }}">
                    </div>
                </div>
                <button class="btn btn-primary">Tampilkan</button>
            </form>
        </div>
        @if ($reports)
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-striped" width="100%">
                                <thead align="center">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama</th>
                                        <th colspan="2">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td width="50%">{{ localDate(request()->dari ?? date('d M Y')) }} - {{ request()->sampai ? localDate(request()->sampai) : date('d M Y') }}</td>
                                        <td width="35%">Pendapatan</td>
                                        <td width="5%">Rp</td>
                                        <td width="10%" align="right">{{ number_format($reports->pemasukan) }}</td>
                                    </tr>
                                    @foreach ($expend as $pengeluaran)
                                        <tr>
                                            <td></td>
                                            <td>{{ $pengeluaran->nama ?? '-' }}</td>
                                            <td>Rp</td>
                                            <td align="right">{{ number_format($pengeluaran->pengeluaran) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th colspan="3">Laba Kotor ( Total Penjualan - Total Pembelian )</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td width="70%">Pendapatan</td>
                                        <td width="5%">Rp</td>
                                        <td width="25%" align="right">{{ number_format($reports->pemasukan) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Pengeluaran</td>
                                        <td>Rp</td>
                                        <td align="right">{{ number_format($reports->pengeluaran) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Total</td>
                                        <td>Rp</td>
                                        <td align="right">{{ number_format($reports->labaKotor) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th colspan="3">Laba Bersih ( Laba Rugi / Kotor )</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td width="70%">Total Laba / Rugi</td>
                                        <td width="5%">Rp</td>
                                        <td width="25%" align="right">{{ number_format($reports->labaKotor) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection