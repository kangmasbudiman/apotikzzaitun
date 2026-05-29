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
        <p class="sub">Laporan Pengeluaran</p>
        <p style="color: blue">
            Periode {{ request()->dari ? date('d M Y', strtotime(request()->dari)) : date('d M Y') }} sampai {{ request()->sampai ? date('d M Y', strtotime(request()->sampai)) : date('d M Y') }}
        </p>
    </div>

    <table width="100%" style="border-collapse: collapse;">
        <thead style="background-color: #111; color: #fff">
            <tr>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">No</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Tanggal</td>
                <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Total</td>
            </tr>
        </thead>
        <tbody>
            @php
                $total = 0;
                $qty = 0;
            @endphp
                @foreach($reports as $report)
                <tr>
                    @php
                        $total += $report->total;
                    @endphp
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ $loop->iteration }}
                    </td>
                    <td style="border: 1px solid #111; padding: 5px 10px">
                        {{ date('d M Y', strtotime($report->tanggal)) }}
                    </td>
                    <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                    <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="15%" align="right">
                        {{ number_format($report->total) }}
                    </td>
                </tr>
                @endforeach
                <tr>
                    <td style="border: 1px solid #111; padding: 5px 10px" align="right" colspan="2">Total Semua Pengeluaran</td>
                    <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                    <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="10%" align="right">{{ number_format($total) }}</td>
                </tr>
        </tbody>
    </table>

</body>
</html>