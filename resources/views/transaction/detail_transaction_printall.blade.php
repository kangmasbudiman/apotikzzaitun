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
        tr:last-child, .black {
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
        <p class="sub">Data Penjualan</p>
        <p style="color: blue">
            @php
                if ($status) {
                    $date = count($transactions) ? $transactions[count($transactions) - 1] ? $transactions[0]->transaction->tanggal : date('d M Y') : date('d M Y');
                } else {
                    $date = date('Y-m-d');
                }
            @endphp
            Periode {{ request()->dari ? localDate(request()->dari) : localDate($date) }} - {{ request()->sampai ? localDate(request()->sampai) : date('d M Y') }}
        </p>
    </div>

    @if ($status)
        <table width="100%" style="border-collapse: collapse;">
            <thead style="background-color: #111; color: #fff">
                <tr>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" width="1%">No</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">ID</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Tanggal</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Judul Buku</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Status</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Harga</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" width="1%">Jumlah</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Disc</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">PPN</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Total</td>
                </tr>
            </thead>
            <tbody>
                @php
                    $total = 0;
                    $harga = 0;
                    $disc = 0;
                    $ppn = 0;
                    $jumlah = 0;
                @endphp
                    @foreach($transactions as $transaction)
                    <tr>
                        @php
                            $subtotal = $transaction->jumlah * $transaction->hargaJual - round($transaction->disc) + $transaction->ppn;
                            $harga += $transaction->hargaJual;
                            $jumlah += $transaction->jumlah;
                            $disc += round($transaction->disc);
                            $ppn += $transaction->ppn;
                            $total += $subtotal;
                        @endphp
                        <td style="border: 1px solid #111; padding: 5px 10px">
                            {{ $loop->iteration }}
                        </td>
                        <td style="border: 1px solid #111; padding: 5px 10px">
                            {{ substr($transaction->idPenjualan, 0, 5) }}
                        </td>
                        <td style="border: 1px solid #111; padding: 5px 10px">
                            {{ timeDate($transaction->transaction->tanggal) }}
                        </td>
                        <td style="border: 1px solid #111; padding: 5px 10px">
                            {{ $transaction->judul }}
                        </td>
                        <td style="border: 1px solid #111; padding: 5px 10px">
                            {{ $transaction->transaction->status ? 'berhasil' : 'batal' }}
                        </td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">
                            {{ number_format($transaction->hargaJual) }}
                        </td>
                        <td style="border: 1px solid #111; padding: 5px 10px">
                            {{ $transaction->jumlah }}
                        </td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">
                            {{ number_format(round($transaction->disc)) }}
                        </td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">
                            {{ number_format($transaction->ppn) }}
                        </td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">
                            {{ number_format($subtotal) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="black">
                        <td style="border: 1px solid #111; padding: 5px 10px" align="center" colspan="5">Jumlah</td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">{{ number_format($harga) }}</td>
                        <td style="border: 1px solid #111; padding: 5px 10px">{{ number_format($jumlah) }}</td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">{{ number_format($disc) }}</td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">{{ number_format($ppn) }}</td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">{{ number_format($total) }}</td>
                    </tr>
            </tbody>
        </table>
    @else
        <table width="100%" style="border-collapse: collapse; margin-bottom: 40px;">
            <thead style="background-color: #111; color: #fff">
                <tr>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" width="1%">No</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">ID</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Tanggal</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Judul Buku</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Status</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Harga</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" width="1%">Jumlah</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Disc</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">PPN</td>
                    <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Total</td>
                </tr>
            </thead>
            <tbody>
                @php
                    $total = 0;
                    $harga = 0;
                    $disc = 0;
                    $ppn = 0;
                    $jumlah = 0;
                @endphp
                    @foreach($transactions['success'] as $transaction)
                    <tr>
                        @php
                            $subtotal = $transaction->jumlah * $transaction->hargaJual - round($transaction->disc) + $transaction->ppn;
                            $harga += $transaction->hargaJual;
                            $jumlah += $transaction->jumlah;
                            $disc += round($transaction->disc);
                            $ppn += $transaction->ppn;
                            $total += $subtotal;
                        @endphp
                        <td style="border: 1px solid #111; padding: 5px 10px">
                            {{ $loop->iteration }}
                        </td>
                        <td style="border: 1px solid #111; padding: 5px 10px">
                            {{ substr($transaction->idPenjualan, 0, 5) }}
                        </td>
                        <td style="border: 1px solid #111; padding: 5px 10px">
                            {{ timeDate($transaction->transaction->tanggal) }}
                        </td>
                        <td style="border: 1px solid #111; padding: 5px 10px">
                            {{ $transaction->judul }}
                        </td>
                        <td style="border: 1px solid #111; padding: 5px 10px">
                            {{ $transaction->transaction->status ? 'berhasil' : 'batal' }}
                        </td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">
                            {{ number_format($transaction->hargaJual) }}
                        </td>
                        <td style="border: 1px solid #111; padding: 5px 10px">
                            {{ $transaction->jumlah }}
                        </td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">
                            {{ number_format(round($transaction->disc)) }}
                        </td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">
                            {{ number_format($transaction->ppn) }}
                        </td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">
                            {{ number_format($subtotal) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="black">
                        <td style="border: 1px solid #111; padding: 5px 10px" align="center" colspan="5">Jumlah</td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">{{ number_format($harga) }}</td>
                        <td style="border: 1px solid #111; padding: 5px 10px">{{ number_format($jumlah) }}</td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">{{ number_format($disc) }}</td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">{{ number_format($ppn) }}</td>
                        <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                        <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">{{ number_format($total) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 20px;"></td>
                    </tr>
                    <tr>
                        <td style="border: none; border-top: 1px solid #111" colspan="14" style="padding: 20px;"></td>
                    </tr>
                    <tr>
                        <td style="padding: 20px;"></td>
                    </tr>
                    <tr class="black">
                        <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">No</td>
                        <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">ID</td>
                        <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Tanggal</td>
                        <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Judul Buku</td>
                        <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Status</td>
                        <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Harga</td>
                        <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Jumlah</td>
                        <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Disc</td>
                        <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">PPN</td>
                        <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Total</td>
                    </tr>
                    @php
                        $total = 0;
                        $harga = 0;
                        $disc = 0;
                        $ppn = 0;
                        $jumlah = 0;
                    @endphp
                        @foreach($transactions['cancel'] as $transaction)
                        <tr>
                            @php
                                $subtotal = $transaction->jumlah * $transaction->hargaJual - round($transaction->disc) + $transaction->ppn;
                                $harga += $transaction->hargaJual;
                                $jumlah += $transaction->jumlah;
                                $disc += round($transaction->disc);
                                $ppn += $transaction->ppn;
                                $total += $subtotal;
                            @endphp
                            <td style="border: 1px solid #111; padding: 5px 10px">
                                {{ $loop->iteration }}
                            </td>
                            <td style="border: 1px solid #111; padding: 5px 10px">
                                {{ substr($transaction->idPenjualan, 0, 5) }}
                            </td>
                            <td style="border: 1px solid #111; padding: 5px 10px">
                                {{ timeDate($transaction->transaction->tanggal) }}
                            </td>
                            <td style="border: 1px solid #111; padding: 5px 10px">
                                {{ $transaction->judul }}
                            </td>
                            <td style="border: 1px solid #111; padding: 5px 10px">
                                {{ $transaction->transaction->status ? 'berhasil' : 'batal' }}
                            </td>
                            <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                            <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">
                                {{ number_format($transaction->hargaJual) }}
                            </td>
                            <td style="border: 1px solid #111; padding: 5px 10px">
                                {{ $transaction->jumlah }}
                            </td>
                            <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                            <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">
                                {{ number_format(round($transaction->disc)) }}
                            </td>
                            <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                            <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">
                                {{ number_format($transaction->ppn) }}
                            </td>
                            <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                            <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">
                                {{ number_format($subtotal) }}
                            </td>
                        </tr>
                        @endforeach
                        <tr>
                            <td style="border: 1px solid #111; padding: 5px 10px" align="center" colspan="5">Jumlah</td>
                            <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                            <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">{{ number_format($harga) }}</td>
                            <td style="border: 1px solid #111; padding: 5px 10px">{{ number_format($jumlah) }}</td>
                            <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                            <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">{{ number_format($disc) }}</td>
                            <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                            <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">{{ number_format($ppn) }}</td>
                            <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp.</td>
                            <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" width="1%" align="right">{{ number_format($total) }}</td>
                        </tr>
            </tbody>
        </table>
    @endif

</body>
</html>