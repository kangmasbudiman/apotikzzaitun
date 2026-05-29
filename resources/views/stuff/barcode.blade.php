@extends('_layouts.app')

@section('title', 'Barcode Generator')

@section('content')
    
    <div class="card">
        <div class="card-header">
            <h2 class="h6 font-weight-bold mb-0 card-title">Barcode Generator</h2>
        </div>
        <div class="card-body">
            <img src="data:image/png;base64,{{ $img }}" alt="">
            <div class="text-center" style="width: 190px;">
                <div class="d-flex justify-content-between">
                    @foreach (str_split($stuff->barcode) as $barcode)
                        <div class="font-weight-bold" style="font-size: 14px;">
                            {{ $barcode }}
                        </div>
                    @endforeach
                </div>
                <div class="bg-dark text-white py-1">
                    Rp {{ number_format($stuff->hargaJual) }}
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('stuff.barcode.print', $stuff->idBuku) }}" class="btn btn-success">Print</a>
            <a href="{{ route('stuff.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

@endsection