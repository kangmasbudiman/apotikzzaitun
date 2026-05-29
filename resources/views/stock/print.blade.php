<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Faktur Pembelian</title>

	<style>
		body {
			font-family: "Roboto";
			color: #333;
			font-size: 20px;
		}
		.heading {
			text-align: center;
			margin-bottom: 20px;
			position: relative;
		}
		.title {
			font-size: 28px;
			color: #111;
		}
		hr {
			border: 0;
			border-top: 2px solid #000;
		}
		p {
			margin: 0;
		}
		img {
			margin: 0;
			width: 120px;
			height: 120px;
			position: absolute;
			top: -0px;
			left: -0px;
		}
    .data tr:nth-child(odd) {
        background-color: #ddd;
    }
    .data tr:last-child {
        background-color: #111;
        color: #fff;
    }
	</style>
</head>
<body>

<div class="heading">
	@if (site('pakaiLogo'))
        <img src="{{ public_path('storage/logo/'.site('logo')) }}" alt="">
    @endif
    <p class="title">{{ site('nama_toko') }}</p>
    <p>{{ site('alamat_toko') }}</p>
    <p>{{ site('telepon_toko') }}</p>
</div>
<hr>

<p class="title" style="text-align: center; margin: 20px 0;">Faktur Pembelian Obat</p>

<table width="100%" style="margin-bottom: 20px">
	<tr>
		<td width="50%">
			No Faktur : {{ $stock->idPembelian }} <br>
			Tanggal : {{ localDate($stock->tanggal) }}
		</td>
		<td width="50%" style="text-align: right;">
			Nama Distributor : {{ $stock->namaDist }} <br>
			Gudang : {{ $stock->namaUser }}
		</td>
	</tr>
</table>

<table style="margin-bottom: 20px; border-collapse: collapse;" width="100%" class="data">
	<tr style="background-color: #111; color: white">
		<td style="border: 1px solid #111;padding: 5px 10px;">No</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">ID Obat</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">Nama Obat</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">Harga</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">Qty</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">Total</td>
	</tr>
	@php
		$jumlah = 0;
	@endphp
	@foreach($stock->detail as $stuff)
		@php
			$jumlah += $stuff->jumlah;
		@endphp
		<tr>
			<td style="border: 1px solid #111;padding: 5px 10px;" width="5%" valign="top">
				{{ $loop->iteration }}.
			</td>
			<td style="border: 1px solid #111;padding: 5px 10px;" valign="top">
				{{ $stuff->idBuku }}<br>
			</td>
			<td style="border: 1px solid #111;padding: 5px 10px;" width="50%">
				{{ $stuff->obat }}<br>
			</td>
			<td style="border: 1px solid #111;padding: 5px 10px;">
				{{ number_format($stuff->hargaPokok) }}
			</td>
			<td style="border: 1px solid #111;padding: 5px 10px;">
				{{ $stuff->jumlah }}
			</td>
			<td style="border: 1px solid #111;padding: 5px 10px;" align="right">
				{{ number_format($stuff->hargaPokok * $stuff->jumlah) }}
			</td>
		</tr>
	@endforeach
	<tr>
		<td style="border: 1px solid #111;padding: 5px 10px;" colspan="4" align="center">
			Jumlah
		</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">
			{{ $jumlah }}
		</td>
		<td style="border: 1px solid #111;padding: 5px 10px;" align="right">
			{{ number_format($stock->total) }}
		</td>
	</tr>
</table>

</body>
</html>