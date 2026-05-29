@extends('_layouts.app')

@section('title', 'Laporan Pembelian Harian')

@section('content')
    
            
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="h6 mb-0 font-weight-bold card-title">Laporan Pembelian Harian</h2>
            <a href="{{ route('report.buy.print.today') }}" target="_blank" class="btn btn-sm btn-danger">Print</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%">
                    <thead align="center">
                        <tr>
                            <th>No</th>
                            <th>Jam</th>
                            <th>ID Pembelian</th>
                            <th>Nama Distributor</th>
                            <th>Total Pembelian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total = 0
                        @endphp
                        @forelse ($reports as $report)
                            <tr align="center">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ date('H:i A', strtotime($report->tanggal)) }}</td>
                                <td>{{ $report->idPembelian }}</td>
                                <td>{{ $report->namaDist }}</td>
                                <td>Rp {{ number_format($report->total) }}</td>
                            </tr>

                            @php
                                $total += $report->total
                            @endphp
                        @empty
                            <tr>
                                <td colspan="5" align="center">Kosong</td>
                            </tr>
                        @endforelse
                        @if ($reports)
                            <tr align="center">
                                <th colspan="4" align="center"><center>Total Seluruh Pembelian</center></th>
                                <th>Rp {{ number_format($total) }}</th>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection