@extends('_layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-sm-8">
					<h1 class="h3">Apotik Delly Farma</h1>
					<p>{{ site('alamat_toko') }}</p>
				</div>
				<div class="col-sm-4">
					<dl class="row mx-0">
						<dt class="col-4 px-0">Tanggal</dt>
						<dd class="col-8 row px-0 mx-0 text-right">
							@php
								date_default_timezone_set('Asia/Jakarta');
							@endphp
							<span class="col-2 px-0"></span>
							<span class="col-10 px-0">{{ date('d/m/y H:i A', strtotime($stock->tanggal)) }}</span>
						</dd>
						<dt class="col-4 px-0">No Faktur</dt>
						<dd class="col-8 row px-0 mx-0 text-right">
							<span class="col-2 px-0">:</span>
							<span class="col-10 px-0">{{ $stock->idPembelian }}</span>
						</dd>
						<dt class="col-4 px-0">Distributor</dt>
						<dd class="col-8 row px-0 mx-0 text-right">
							<span class="col-2 px-0">:</span>
							<span class="col-10 px-0">{{ $stock->namaDist }}</span>
						</dd>
						<dt class="col-4 px-0">Gudang</dt>
						<dd class="col-8 row px-0 mx-0 text-right">
							<span class="col-2 px-0">:</span>
							<span class="col-10 px-0">{{ $stock->namaUser }}</span>
						</dd>
					</dl>
				</div>
			</div>
			<div class="table-responsive">
				<table class="table table-striped" width="100%">
					<thead>
						<tr>
							<th>No</th>
							<th>Obat</th>
							<th>Harga</th>
							<th>Jumlah</th>
							<th>Total</th>
						</tr>
					</thead>
					<tbody>
						@foreach($stock->detail as $stuff)
							<tr>
								<td>{{ $loop->iteration }}</td>
								<td>{{ $stuff->obat }}</td>
								<td>Rp {{ number_format($stuff->hargaPokok) }}</td>
								<td>{{ $stuff->jumlah }}</td>
								<td>Rp {{ number_format($stuff->hargaPokok * $stuff->jumlah) }}</td>
							</tr>
						@endforeach
					</tbody>
					<tfoot>
						<tr>
							<td colspan="4" align="right"><strong>Subtotal</strong></td>
							<td>Rp {{ number_format($stock->total) }}</td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
		<div class="card-footer">
			<a href="{{ route('stock.print', $stock->idPembelian) }}" class="btn btn-primary">Print</a>
			<a href="{{ route('stock.create') }}" class="btn btn-secondary">Kembali</a>
		</div>
	</div>
@endsection