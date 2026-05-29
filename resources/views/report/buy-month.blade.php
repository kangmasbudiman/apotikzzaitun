@extends('_layouts.app')

@section('title', 'Laporan Pembelian Bulanan')

@section('content')
    
            
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="h6 mb-0 font-weight-bold card-title">Laporan Pembelian Bulanan</h2>
            <a href="{{ route('report.buy.print.month') }}" class="btn btn-sm btn-danger" target="_blank">Print</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%">
                    <thead align="center">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Distributor</th>
                            <th>Total Pembelian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total = 0
                        @endphp

                        @forelse ($reports as $report)
                        @php
                            $total += $report->total
                        @endphp
                            <tr align="center">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ date('d M', strtotime($report->tanggal)) }}</td>
                                <td>{{ $report->namaDist }}</td>
                                <td>Rp {{ number_format($report->total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" align="center">Kosong</td>
                            </tr>
                        @endforelse

                        <tr>
                            <td colspan="3" align="center"><b>Total Pembelian</b></td>
                            <td align="center">Rp {{ number_format($total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection