@extends('_layouts.app')

@section('title', 'Laporan Pendapatan Bulanan')

@section('content')
    
            
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="h6 mb-0 font-weight-bold card-title">Laporan Pendapatan Bulanan</h2>
            <a href="{{ route('report.transaction.print.month') }}" target="_blank" class="btn btn-sm btn-danger">Print</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%">
                    <thead align="center">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total = 0
                        @endphp
                        @forelse ($reports as $report)
                            <tr align="center">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ date('d M', strtotime($report->tanggal)) }}</td>
                                <td>Rp {{ number_format($report->total) }}</td>
                            </tr>

                            @php
                                $total += $report->total
                            @endphp
                        @empty
                            <tr>
                                <td colspan="3" align="center">Kosong</td>
                            </tr>
                        @endforelse
                        @if ($reports)
                            <tr align="center">
                                <th colspan="2" align="center"><center>Total Seluruh Pendapatan</center></th>
                                <th>Rp {{ number_format($total) }}</th>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection