<style>
    table{
        padding: 0;
        border-collapse: collapse;
    }
    td {
        padding: 0;
    }
    .break {
        page-break-after: always;
    }
</style>

@foreach ($barcodes as $row)
    <div style="width: 25%; float: left;">
        <div style="border: 1px solid #555; padding: 30px 0;">
            <div style="width: 190px; margin: auto; text-align: center;">
                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($row->barcode, 'EAN13', 2, 65) }}" alt="">
                <br>
                <table width="100%">
                    <tr>
                        @foreach (str_split($row->barcode) as $barcode)
                            <td style="font-size: 16px;" align="center">
                                {{ $barcode }}
                            </td>
                        @endforeach
                    </tr>
                </table>
                <div style="background-color: #111; color: #fff; margin-top: 5px;">
                    <span style="font-size: 16px">Rp {{ number_format($row->hargaJual) }}</span>
                </div>
            </div>
        </div>
    </div>
    @if ($loop->iteration > 1 && $loop->iteration % 4 === 0)
        <div style="clear: both;"></div>
    @endif
    @if ($loop->iteration > 1 && $loop->iteration % 32 === 0)
        <div class="break"></div>
    @endif
@endforeach