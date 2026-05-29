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
            width: 140px;
            height: 140px;
            position: absolute;
            top: -0px;
            left: -0px;
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
        <p class="sub">Laporan Laba</p>
    </div>

    <p>Periode : {{ request()->dari ? date('d M Y', strtotime(request()->dari)) : date('d M Y') }} sd {{ request()->sampai ? date('d M Y', strtotime(request()->sampai)) : date('d M Y') }}</p>

    <br>

    <table width="100%">
        <tbody>
            <tr>
                <td colspan="2">
                    Pendapatan
                </td>
                <td>:</td>
                <td></td>
                <td></td>
                <td></td>
                <td width="1%">Rp.</td>
                <td width="2%" align="right">
                    {{ number_format($reports->pemasukan ?? 0) }}
                </td>
            </tr>
            {{-- <tr>
                <td colspan="2">
                    Laba Kotor
                </td>
                <td>:</td>
                <td colspan="2"></td>
                <td>Rp.</td>
                <td align="right">
                    {{ number_format($reports->labaKotorOne ?? 0) }}
                </td>
            </tr> --}}
                <tr>
                    <td colspan="2">
                        Pengeluaran
                    </td>
                    <td>:</td>
                </tr>
                <tr>
                    <td width=".5%"></td>
                    <td width="4%">
                        Harga Pokok Penjualan
                    </td>
                    <td width="1.5%">:</td>
                    <td width="1%">Rp.</td>
                    <td align="right" width="1%">
                        {{ number_format($reports->hargaPokok ?? 0) }}
                    </td>
                    <td width="3%"></td>
                    <td width=".5%"></td>
                    <td></td>
                    <td></td>
                </tr>
                @foreach ($expend as $pengeluaran)
                    <tr>
                        <td></td>
                        <td>
                            {{ $pengeluaran->nama ?? '-' }}
                        </td>
                        <td>:</td>
                        <td>Rp.</td>
                        <td align="right">
                            {{ number_format($pengeluaran->pengeluaran ?? 0) }}
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3"></td>
                    <td colspan="2" style="position: relative;">
                        <hr style="border-style: dashed;">
                    </td>
                    <td>+</td>
                </tr>
            <tr>
                <td colspan="2">Total Pengeluaran</td>
                <td>:</td>
                <td colspan="3"></td>
                <td>Rp.</td>
                <td align="right">{{ number_format(($reports->pengeluaranOne ?? 0) + ($reports->hargaPokok ?? 0)) }}</td>
            </tr>
            <tr>
                <td colspan="6"></td>
                <td colspan="2" style="position: relative;">
                    <hr style="border-style: dashed;">
                </td>
                <td width=".3%" style="position: relative;" align="right">
                    <span style="position: absolute; top: -10px; right: 0;">-</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    Laba (Rugi)
                </td>
                <td>:</td>
                <td colspan="3"></td>
                <td>Rp.</td>
                <td align="right">
                    {{ number_format($reports->labaOne ?? 0) }}
                </td>
            </tr>
        </tbody>
    </table>

</body>
</html>