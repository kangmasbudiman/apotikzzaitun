@extends('_layouts.app')

@section('title', 'Notifikasi')

@section('content')
    <div class="card">
        <div class="card-header">
            <h2 class="h6 font-weight-bold mb-0 card-title">Notifikasi</h2>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Buku</th>
                        <th>Barcode</th>
                        <th>Penerbit</th>
                        <th>Nama Buku</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stuffs as $stuff)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $stuff->idBuku }}</td>
                            <td>{{ $stuff->barcode }}</td>
                            <td>{{ $stuff->penerbit }}</td>
                            <td>{{ $stuff->obat }}</td>
                            <td>{{ $stuff->stock }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" align="center">Kosong</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    
            {{ $stuffs->links() }}
        </div>
    </div>

@endsection