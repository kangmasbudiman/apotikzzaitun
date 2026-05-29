@extends('_layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-sm-8">
					<h1 class="h3">Apotik Dear</h1>
					<p>{{ site('alamat_toko') }}</p>
				</div>
				<div class="col-sm-4">
					<dl class="row mx-0">
						<dt class="col-3 px-0">Tanggal</dt>
						<dd class="col-9 row px-0 mx-0 text-right">
							@php
								date_default_timezone_set('Asia/Jakarta');
							@endphp
							<span class="col-2 px-0">:</span>
							<span class="col-10 px-0">{{ date('d/m/y H:i A', strtotime($transaction->tanggal)) }}</span>
						</dd>
						<dt class="col-3 px-0">No Faktur</dt>
						<dd class="col-9 row px-0 mx-0 text-right">
							<span class="col-2 px-0">:</span>
							<span class="col-10 px-0">{{ $transaction->idPenjualan }}</span>
						</dd>
						<dt class="col-3 px-0">Kasir</dt>
						<dd class="col-9 row px-0 mx-0 text-right">
							<span class="col-2 px-0">:</span>
							<span class="col-10 px-0">{{ $transaction->namaUser }}</span>
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
							<th>Diskon</th>
							<th>Total</th>
						</tr>
					</thead>
					<tbody>
						@php
							$subtotal = 0;
						@endphp
						@foreach($transaction->detail as $stuff)
							@php
								$total = $stuff->jumlah * $stuff->hargaJual - $stuff->disc;
								$subtotal += $total;
							@endphp
							<tr>
								<td>{{ $loop->iteration }}</td>
								<td>{{ $stuff->obat }}</td>
								<td>Rp {{ number_format($stuff->hargaJual) }}</td>
								<td>{{ $stuff->jumlah }}</td>
								<td>Rp {{ number_format($stuff->disc) }}</td>
								<td>Rp {{ number_format($total) }}</td>
							</tr>
						@endforeach
					</tbody>
					<tfoot>
						<tr>
							<td colspan="5" align="right"><strong>Total</strong></td>
							<td>Rp {{ number_format($subtotal) }}</td>
						</tr>
						<tr>
							<td colspan="5" align="right"><strong>PPN</strong></td>
							<td>Rp {{ number_format($transaction->ppn) }}</td>
						</tr>
						<tr>
							<td colspan="5" align="right"><strong>Subtotal</strong></td>
							<td>Rp {{ number_format($transaction->total) }}</td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
		<div class="card-footer">
			@if ($transaction->status)
				<a href="{{ route('transaction.print', $transaction->idPenjualan) }}" class="btn btn-primary">Print</a>
			@endif
			<a href="{{ route('transaction.index') }}" class="btn btn-secondary">Kembali</a>
		</div>
	</div>
@endsection