@extends('_layouts.app')

@section('title', 'Laporan Pendapatan')

@section('content')
    
            
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="h6 mb-0 font-weight-bold card-title">Laporan Pendapatan</h2>
            <form action="{{ route('report.transaction.print') }}" target="_blank">
                <input type="hidden" name="dari" value="{{ request()->dari }}">
                <input type="hidden" name="sampai" value="{{ request()->sampai }}">
                <input type="hidden" name="user" value="{{ request()->user }}">
                <button class="btn btn-sm btn-danger">Print</button>
            </form>
        </div>
        <div class="card-body border-bottom">
           <form action="{{ route('report.transaction') }}">
                <div class="form-row form-group">
                    <div class="col-sm-3">
                        <label>Dari Tanggal</label>
                        <input type="date" name="dari" class="form-control" value="{{ request()->dari }}">
                    </div>
                    <div class="col-sm-3">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="sampai" class="form-control" value="{{ request()->sampai }}">
                    </div>
                    <div class="col-sm-3">
                        <label>Kasir</label>
                        <select class="form-control" name="user">
                            <option selected disabled>Kasir</option>
                            @foreach ($user as $item)
                                <option value="{{ $item->idUser }}">{{ $item->nama }}</option>
                            @endforeach
                        </select>
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
                                <th>Total Pendapatan</th>
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
                                    <td>{{ localDate($report->tanggal) }}</td>
                                    <td>Rp {{ number_format($report->total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" align="center">Kosong</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" align="right"><b>Total Pendapatan</b></td>
                                <td align="center">Rp {{ number_format($total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    </div>

@endsection