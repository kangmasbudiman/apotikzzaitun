<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>

	<style>
		body {
			font-family: "Roboto";
			color: #333;
			font-size: 18px;
		}
		.heading {
			text-align: center;
			margin-bottom: 20px;
			position: relative;
		}
		.title {
			text-align: center;
			font-size: 24px;
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
		}
        tr:nth-child(odd) {
            background-color: #ddd;
        }
        tr:last-child {
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

<p class="title" style="margin: 20px 0;">Data Buku</p>

@php
	$total = 0;
@endphp
<table style="margin-bottom: 20px; border-collapse: collapse;" width="100%">
	<tr style="background-color: #111; color: white">
		<td style="border: 1px solid #111;padding: 5px 10px;">No</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">ID Buku</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">Judul</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">Penerbit</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">Tahun</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">Harga Pokok</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">Harga Jual</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">Stok</td>
	</tr>
	@php
		$jumlah = 0;
	@endphp
	@foreach($stuffs as $buku)
		@php
			$jumlah += $buku->stock;
		@endphp
		<tr>
			<td valign="top" style="border: 1px solid #111;padding: 5px 10px;" width="5%" valign="top">
				{{ $loop->iteration }}.
			</td>
			<td valign="top" style="border: 1px solid #111;padding: 5px 10px;" valign="top">
				{{ $buku->idBuku }}<br>
			</td>
			<td valign="top" style="border: 1px solid #111;padding: 5px 10px;">
				{{ $buku->judul }}<br>
			</td>
			<td valign="top" style="border: 1px solid #111;padding: 5px 10px;">
				{{ $buku->penerbit }}<br>
			</td>
			<td valign="top" style="border: 1px solid #111;padding: 5px 10px;">
				{{ $buku->tahun }}<br>
			</td>
			<td valign="top" style="border: 1px solid #111;padding: 5px 10px;">
				{{ number_format($buku->hargaPokok) }}
			</td>
			<td valign="top" style="border: 1px solid #111;padding: 5px 10px;">
				{{ number_format($buku->hargaJual) }}
			</td>
			<td valign="top" style="border: 1px solid #111;padding: 5px 10px;">
				{{ $buku->stock }}
			</td>
		</tr>
	@endforeach
	<tr>
		<td style="border: 1px solid #111;padding: 5px 10px;" colspan="7" align="right">
			Jumlah
		</td>
		<td style="border: 1px solid #111;padding: 5px 10px;">
			{{ $jumlah }}
		</td>
	</tr>
</table>

</body>
</html>