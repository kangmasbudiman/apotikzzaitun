@extends('_layouts.app')

@section('title', 'Laporan Pengeluaran')

@section('content')
    
            
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="h6 mb-0 font-weight-bold card-title">Laporan Pengeluaran</h2>
            <form action="{{ route('report.expend.print') }}" target="_blank">
                <input type="hidden" name="dari" value="{{ request()->dari }}">
                <input type="hidden" name="sampai" value="{{ request()->sampai }}">
                <button class="btn btn-sm btn-danger">Print</button>
            </form>
        </div>
        <div class="card-body border-bottom">
           <form action="{{ route('report.expend') }}">
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
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" width="100%">
                        <thead align="center">
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Total Pengeluaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total = 0
                            @endphp
                            @forelse ($reports as $report)
                                <tr align="center">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ localDate($report->tanggal) }}</td>
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
                                    <th colspan="2" align="center"><center>Total Seluruh Pengeluaran</center></th>
                                    <th>Rp {{ number_format($total) }}</th>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

@endsection