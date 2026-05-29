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
    <p class="sub">Data PPN</p>
    <p style="color: blue">
      @if ($jenis)
        Periode {{ request()->dari ? localDate(request()->dari) : (count($data) ? localDate($data[count($data) - 1]->tanggal) : date('d M Y')) }} - {{ request()->sampai ? localDate(request()->sampai) : date('d M Y') }}
      @else
        Periode {{ request()->dari ? localDate(request()->dari)  : date('d M Y') }} - {{ request()->sampai ? localDate(request()->sampai) : date('d M Y') }}
      @endif
        </p>

  </div>

  @if ($jenis)
    <table width="100%" style="border-collapse: collapse;">
      <thead style="background-color: #111; color: #fff">
        <tr>
          <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Kode Pajak</td>
          <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Jenis</td>
          <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Tanggal</td>
          <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Keterangan</td>
          <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Nominal</td>
        </tr>
      </thead>
      <tbody>
        @php
          $total = 0;
        @endphp
          @foreach($data as $ppn)
          <tr>
            @php
              $total += $ppn->nominal;
            @endphp
            <td style="border: 1px solid #111; padding: 5px 10px">
              {{ $ppn->idPajak }}
            </td>
            <td style="border: 1px solid #111; padding: 5px 10px">
              {{ $ppn->jenis }}
            </td>
            <td style="border: 1px solid #111; padding: 5px 10px">
              {{ timeDate($ppn->tanggal) }}
            </td>
            <td style="border: 1px solid #111; padding: 5px 10px">
              {{ $ppn->keterangan ?? '-' }}
            </td>
            <td style="border-bottom: 1px solid #111; padding: 5px 10px">Rp.</td>
            <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" align="right">
              {{ number_format($ppn->nominal) }}
            </td>
          </tr>
          @endforeach
          <tr>
            <td style="border-bottom: 1px solid #111; padding: 5px 10px" colspan="2"></td>
            <td style="border: 1px solid #111; padding: 5px 10px" align="center" colspan="2">Jumlah</td>
            <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp. </td>
            <td style="border: 1px solid #111; padding: 5px 10px" align="right">{{ number_format($total) }}</td>
          </tr>
      </tbody>
    </table>
  @else
    @foreach ($data as $jenis)
      <table width="100%" style="border-collapse: collapse; margin-bottom: 40px;">
        <thead style="background-color: #111; color: #fff">
          <tr>
            <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Kode Pajak</td>
            <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Jenis</td>
            <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Tanggal</td>
            <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px">Keterangan</td>
            <td style="font-weight: bold; border: 1px solid #111; padding: 5px 10px" colspan="2">Nominal</td>
          </tr>
        </thead>
        <tbody>
          @php
            $total = 0;
          @endphp
            @foreach($jenis as $ppn)
            <tr>
              @php
                $total += $ppn->nominal;
              @endphp
              <td style="border: 1px solid #111; padding: 5px 10px">
                {{ $ppn->idPajak }}
              </td>
              <td style="border: 1px solid #111; padding: 5px 10px">
                {{ $ppn->jenis }}
              </td>
              <td style="border: 1px solid #111; padding: 5px 10px">
                {{ timeDate($ppn->tanggal) }}
              </td>
              <td style="border: 1px solid #111; padding: 5px 10px">
                {{ $ppn->keterangan ?? '-' }}
              </td>
              <td style="border-bottom: 1px solid #111; padding: 5px 10px">Rp.</td>
              <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" align="right">
                {{ number_format($ppn->nominal) }}
              </td>
            </tr>
            @endforeach
            <tr>
              <td style="border-bottom: 1px solid #111; padding: 5px 10px" colspan="2"></td>
              <td style="border: 1px solid #111; padding: 5px 10px" align="center" colspan="2">Jumlah</td>
              <td style="border-bottom: 1px solid #111; padding: 5px 10px" width="1%">Rp. </td>
              <td style="border: 1px solid #111; border-left: none; padding: 5px 10px" align="right" width="10%">{{ number_format($total) }}</td>
            </tr>
        </tbody>
      </table>
    @endforeach
  @endif

</body>
</html>