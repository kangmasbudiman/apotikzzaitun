@extends('_layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')

    <div id="alert"></div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
           
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%">
                    <thead>
                        <tr>
                            <th>IdTransaksi</th>
                            <th>Tanggal</th>
                            <th>Nama Obat</th>
                            <th>Status</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Discount</th>
                            <th>PPN</th>
                            <th width="100px">Total</th>
                    </thead>
                    @foreach ($dataobat as $item)
                    <tbody>
                       
                            <td>{{ $item->idPenjualan }}</td>
                            <td>{{ $item->tanggal }}</td>
                            <td>{{ $item->obat }}</td>
                            <td>{!! $item->status_badge !!}</td>
                            <td>{{ $item->hargaPokok }}</td>
                            <td>{{ $item->jumlah }}</td>
                            <td>{{ $item->disc }}</td>
                            <td>{{ number_format($item->ppn,)}}</td>
                            <td>Rp. {{ number_format($item->total,) }}
                            </td>
                         </tbody>
                    @endforeach
                  




                </table>
            </div>
        </div>
    </div>

    <div class="modal" id="import">
    <div class="modal-dialog">
    <div class="modal-content">

    </div>
    </div>
    </div>

@endsection

@push('css')
    
    <link rel="stylesheet" href="{{ asset('sufee-admin/vendors/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">

@endpush

@push('js')
    
    <script src="{{ asset('sufee-admin/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('sufee-admin/vendors/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('sufee-admin/vendors/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>

 

   

@endpush