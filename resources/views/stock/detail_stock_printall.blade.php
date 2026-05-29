<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print</title>
    <style>
        @font-face {
        font-family: 'Roboto';
        src: url({{ storage_path('fonts/Roboto/Roboto-Bold.ttf') }}) format("truetype");
        font-weight: 700; // use the matching font-weight here ( 100, 200, 300, 400, etc).
        font-style: bold; // use the matching font-style here
        }

        body {
            color: #333;
            font-size: 18px;
        }
        .heading {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }
        .title {
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
            top: -0px;
            left: -0px;
        }
        tr:nth-child(even) {
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

    <div class="heading" style="margin-bottom: 20px">
        <p class="sub">Data Pembelian Buku</p>
        <p style="color: blue">
            @php
                $date = count($stocks) ? ($stocks[count($stocks) - 1] ? $stocks[0]->stock->tanggal : date('d M Y')) : date('d M Y');
            @endphp
            Periode {{ request()->dari ? localDate(request()->dari) : localDate($date) }} - {{ request()->sampai ? localDate(request()->sampai) : date('d M Y') }}
        </p>
    </div>

    <table width="100%" style="border-collapse: collapse;" border="0">
        <thead style="background-color: #111; color: #fff">
            <tr>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">No</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">ID Pembelian</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Tanggal</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Nama Dist</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Judul Buku</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Harga</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Jumlah</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Total</td>
            </tr>
        </thead>
        <tbody>
            @php
                $total = 0;
                $harga = 0;
                $jumlah = 0;
            @endphp
                @foreach($stocks as $stock)
                <tr>
                    @php
                        $subtotal = $stock->hargaPokok * $stock->jumlah;
                        $harga += $stock->hargaPokok;
                        $jumlah += $stock->jumlah;
                        $total += $subtotal;
                    @endphp
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ $loop->iteration }}
                    </td>
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ substr($stock->idPembelian, 0, 5) }}
                    </td>
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ timeDate($stock->stock->tanggal) }}
                    </td>
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ $stock->stock->namaDist }}
                    </td>
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ $stock->judul }}
                    </td>
                    <td style="padding: 5px 10px; border-bottom: 1px solid #111;" width="1%">Rp.</td>
                    <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="5%" align="right">
                        {{ number_format($stock->hargaPokok) }}
                    </td>
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ $stock->jumlah }}
                    </td>
                    <td style="padding: 5px 10px; border-bottom: 1px solid #111;" width="1%">Rp.</td>
                    <td style="border: 1px solid #111; padding: 5px 10px; border-left: none;" align="right" width="5%">
                        {{ number_format($subtotal) }}
                    </td>
                </tr>
                @endforeach
                <tr>
                    <td style="border: 1px solid #111; padding: 5px 10px" align="center" colspan="5">Jumlah</td>
                    <td style="padding: 5px 10px; border-bottom: 1px solid #111">Rp.</td>
                    <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" align="right">{{ number_format($harga) }}</td>
                    <td style="border: 1px solid #111; padding: 5px 10px">{{ number_format($jumlah) }}</td>
                    <td style="padding: 5px 10px; border-bottom: 1px solid #111">Rp.</td>
                    <td style="border: 1px solid #111; border-left: none; padding: 5px 10px">{{ number_format($total) }}</td>
                </tr>
        </tbody>
    </table>

</body>
</html>