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
        <p class="sub">Data Stok Opname</p>
        <p style="color: blue">
            Periode {{ request()->dari ? localDate(request()->dari) : (count($data) ? localDate($data[0]->tanggal) : date('d M Y')) }} - {{ request()->sampai ? localDate(request()->sampai) : date('d M Y') }}
        </p>
    </div>

    <table width="100%" style="border-collapse: collapse;">
        <thead style="background-color: #111; color: #fff">
            <tr>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">ID Buku</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Tanggal</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Nama Buku</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Stok Sistem</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Stok Nyata</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Selisih</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Total</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Keterangan</td>
            </tr>
        </thead>
        <tbody>
            @php
                $total = 0;
            @endphp
                @foreach($data as $opname)
                <tr>
                    @php
                        $total += 0 - $opname->total;
                    @endphp
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ $opname->idBuku }}
                    </td>
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ timeDate($opname->tanggal) }}
                    </td>
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ $opname->judul }}
                    </td>
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ $opname->stokSistem }}
                    </td>
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ $opname->stokNyata }}
                    </td>
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ 0 - $opname->selisih }}
                    </td>
                    <td width="5%" style="border: none; border-bottom: 1px solid #111; padding: 5px 10px">Rp.</td>
                    <td width="11%" style="border: 1px solid #111; padding: 5px 10px; border-left: none;" align="right">
                        {{ number_format(0 - $opname->total) }}
                    </td>
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ $opname->keterangan ?? '-' }}
                    </td>
                </tr>
                @endforeach
                <tr>
                    <td style="border: 1px solid #111; padding: 5px 10px" align="center" colspan="6">Jumlah</td>
                    <td width="5%" style="border: none; border-bottom: 1px solid #111; padding: 5px 10px; border-color: #000;">Rp.</td>
                    <td width="11%" style="border: 1px solid #111; padding: 5px 10px; border-left: none;" align="right">{{ number_format($total) }}</td>
                    <td style="border: 1px solid #111; padding: 5px 10px" align="center"></td>
                </tr>
        </tbody>
    </table>

</body>
</html>