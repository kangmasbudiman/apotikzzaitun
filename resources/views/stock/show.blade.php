@extends('_layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
    
    <div class="row">
        <div class="col-sm-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h2 class="h6 font-weight-bold mb-0 card-title">Detail Pembelian</h2>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">IdPembelian</dt>
                        <dd class="col-sm-8">{{ $stock->idPembelian }}</dd>
                        <dt class="col-sm-4">Tanggal</dt>
                        <dd class="col-sm-8">{{ date('d M Y', strtotime($stock->tanggal)) }}</dd>
                        <dt class="col-sm-4">Distributor</dt>
                        <dd class="col-sm-8">{{ $stock->distributor->namaDist }}</dd>
                        <dt class="col-sm-4">Judul Buku</dt>
                        <dd class="col-sm-8">{{ $stock->stuff->judul }}</dd>
                        <dt class="col-sm-4">Jumlah Pembelian</dt>
                        <dd class="col-sm-8">{{ $stock->jumlah }}</dd>
                    </dl>
                </div>
                <div class="card-footer">
                    <a href="{{ route('stock.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>

@endsection