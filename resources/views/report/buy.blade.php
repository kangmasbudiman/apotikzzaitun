@extends('_layouts.app')

@section('title', 'Laporan Pembelian')

@section('content')
    
            
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="h6 mb-0 font-weight-bold card-title">Laporan Pembelian</h2>
            @can('isAdmin')
                <form action="{{ route('report.buy.print') }}" target="_blank">
                    <input type="hidden" name="dari" value="{{ request()->dari }}">
                    <input type="hidden" name="sampai" value="{{ request()->sampai }}">
                    <input type="hidden" name="distributor" value="{{ request()->distributor }}">
                    <button class="btn btn-sm btn-danger">Print</button>
                </form>
            @endcan
        </div>
        @can('isAdmin')
            <div class="card-body border-bottom">
               <form action="{{ route('report.buy') }}">
                    <div class="form-row form-group">
                        <div class="col-sm-3">
                            <label>Dari Tanggal</label>
                            <input type="date" name="dari" class="form-control" value="{{ request()->dari }}">
                        </div>
                        <div class="col-sm-3">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="sampai" class="form-control" value="{{ request()->sampai }}">
                        </div>
                        @can('isAdmin')
                            <div class="col-sm-3">
                                <label>Distributor</label>
                                <select class="form-control" name="distributor">
                                    <option selected disabled>Distributor</option>
                                    @foreach ($distributor as $item)
                                        <option value="{{ $item->namaDist }}" {{ request()->distributor === $item->namaDist ? 'selected' : '' }}>{{ $item->namaDist }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endcan
                    </div>
                    <button class="btn btn-primary">Tampilkan</button>
                </form>
            </div>
        @endcan
        @if ($reports)
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" width="100%">
                        <thead align="center">
                            <tr>
                                <th>#</th>
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
                                <tr align="center">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ localDate($report->tanggal) }}</td>
                                    <td>{{ $report->namaDist }}</td>
                                    <td>Rp {{ number_format($report->total) }}</td>
                                </tr>

                                @php
                                    $total += $report->total
                                @endphp
                            @empty
                                <tr>
                                    <td colspan="4" align="center">Kosong</td>
                                </tr>
                            @endforelse
                            <tr>
                                <td align="right" colspan="3"><b>Total Pembelian</b></td>
                                <td align="center">Rp {{ number_format($total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

@endsection