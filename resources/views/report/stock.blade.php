@extends('_layouts.app')

@section('title', 'Laporan Stok')

@section('content')
    
            
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="h6 mb-0 font-weight-bold card-title">Laporan Stok</h2>
            <form action="{{ route('report.stock.print') }}" target="_blank">
                <input type="hidden" name="dari" value="{{ request()->dari }}">
                <input type="hidden" name="sampai" value="{{ request()->sampai }}">
                <input type="hidden" name="distributor" value="{{ request()->distributor }}">
                <button class="btn btn-sm btn-danger">Print</button>
            </form>
        </div>
        <div class="card-body border-bottom">
           <form action="{{ route('report.stock') }}">
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
        @if ($reports)
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Obat</th>
                                <th>Distributor</th>
                                <th>Qty</th>
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

                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ localDate($report->tanggal) }}</td>
                                    <td>{{ $report->obat }}</td>
                                    <td>{{ $report->namaDist }}</td>
                                    <td>{{ $report->total }}</td>
                                </tr>

                                @php
                                    $total += $report->total
                                @endphp
                            @empty
                                <tr>
                                    <td colspan="5" align="center">Kosong</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if (count($reports))
                            <tr>
                                <td colspan="4" align="right"><b>Total Stok</b></td>
                                <td>{{ $total }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        @endif
    </div>

@endsection