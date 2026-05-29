<style>
    table{
        padding: 0;
        border-collapse: collapse;
    }
    td {
        padding: 0;
    }
</style>
<div style="width: 190px; text-align: center;">
<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($stuff->barcode, 'EAN13', 2, 65) }}" alt="">
<br>

    <table width="100%">
        <tr>
            @foreach (str_split($stuff->barcode) as $barcode)
                <td style="font-size: 16px;" align="center">
                    {{ $barcode }}
                </td>
            @endforeach
        </tr>
    </table>
    <div style="background-color: #111; color: #fff; margin-top: 5px;">
        <span style="font-size: 16px">Rp {{ number_format($stuff->hargaJual) }}</span>
    </div>
</div>