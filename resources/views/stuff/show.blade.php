@extends('_layouts.app')

@section('title', $stuff->name)

@section('content')
	
	<div class="row">
		<div class="col-sm-6 mx-auto">
			<div class="card">
				<div class="card-header">
					<h2 class="h6 font-weight-bold mb-0 card-title">Detail Barang</h2>
				</div>
				<div class="card-body">
					<dl class="row">
						<dt class="col-sm-4">No ISBN</dt>
						<dd class="col-sm-8">{{ $stuff->noisbn }}</dd>
						<dt class="col-sm-4">Barcode</dt>
						<dd class="col-sm-8">{{ $stuff->barcode }}</dd>
						<dt class="col-sm-4">Kategori</dt>
						<dd class="col-sm-8">{{ $stuff->category->nama_kategori ?? '-' }}</dd>
						<dt class="col-sm-4">Judul</dt>
						<dd class="col-sm-8">{{ $stuff->judul }}</dd>
						<dt class="col-sm-4">Rak</dt>
						<dd class="col-sm-8">{{ $stuff->rack->nama_rak ?? '-' }}</dd>
						<dt class="col-sm-4">Penulis</dt>
						<dd class="col-sm-8">{{ $stuff->penulis }}</dd>
						<dt class="col-sm-4">Penerbit</dt>
						<dd class="col-sm-8">{{ $stuff->penerbit }}</dd>
						<dt class="col-sm-4">Tahun</dt>
						<dd class="col-sm-8">{{ $stuff->tahun }}</dd>
						<dt class="col-sm-4">Stok</dt>
						<dd class="col-sm-8">{{ $stuff->stock }}</dd>
						<dt class="col-sm-4">Harga Pokok</dt>
						<dd class="col-sm-8">{{ $stuff->hargaPokok }}</dd>
						<dt class="col-sm-4">HargaJual</dt>
						<dd class="col-sm-8">{{ $stuff->hargaJual }}</dd>
						<dt class="col-sm-4">Disc</dt>
						<dd class="col-sm-8">{{ $stuff->disc }}</dd>
					</dl>
				</div>
				<div class="card-footer">
					<a href="{{ route('stuff.index') }}" class="btn btn-secondary">Kembali</a>
				</div>
			</div>
		</div>
	</div>

@endsection