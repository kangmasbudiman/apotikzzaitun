<div class="modal fade" tabindex="1" role="dialog" id="print" wire:ignore.self>
<div class="modal-dialog">
<div class="modal-content">
    @if ($transaction)
    <div class="modal-header">
        <center class="w-100">
            <h5>{{ site('nama_toko') }}</h5>
            <p class="mb-0">{{ site('alamat_toko') }}</p>
            <p class="mb-0">{{ site('telepon_toko') }}</p>
        </center>
    </div>
    <div class="modal-body pb-0 border-bottom">
        <ul class="list-unstyled row">
            <li class="col-sm-6">
                <b>No Faktur</b><br>{{ $transaction->idPenjualan }}
            </li>
            <li class="col-sm-6 text-right">
                <b>Kasir</b><br>{{ $transaction->namaUser }}
            </li>
            <li class="col-sm-6">
                <b>Tanggal</b><br>{{ date('d/m/y H:i A', strtotime($transaction->tanggal)) }}
            </li>
        </ul>
          <ul class="list-unstyled row">
            <li class="col-sm-6">
                <b>Pasien/Customer</b><br>{{ $transaction->namapasien }}
            </li>
           
        </ul>
    </div>
    <div class="modal-body pb-0">
        <table class="table table-striped" width="100%">
            <tr>
                <th>Nama Obat</th>
                <th>Qty</th>
                <th>Disc</th>
                <th align="right">Total</th>
            </tr>
            @php
                $total = 0;
                $disc = 0
            @endphp

            @foreach ($transaction->detail as $detail)
                @php
                    $total += $detail->jumlah * $detail->hargaJual - $detail->disc;
                    $disc += $detail->disc
                @endphp

                <tr>
                    <td>{{ $detail->obat }}</td>
                    <td>{{ $detail->jumlah }} x {{ number_format($detail->hargaJual) }}</td>
                    <td>{{ number_format($detail->disc) }}</td>
                    <td align="right">{{ number_format($detail->jumlah * $detail->hargaJual - $detail->disc) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3" align="right">Total</td>
                <td align="right">{{ number_format($total) }}</td>
            </tr>
            <tr>
                <td colspan="3" align="right">Diskon</td>
                <td align="right">{{ number_format($diskon) }}</td>
            </tr>
            <tr>
                <td colspan="3" align="right">Grand Total</td>
                <td align="right">{{ number_format($transaction->total) }}</td>
            </tr>
            <tr>
                <td colspan="3" align="right">Bayar</td>
                <td align="right">{{ number_format(is_int($bayar) ? $bayar : 0) }}</td>
            </tr>
            <tr>
                <td colspan="3" align="right">Kembali</td>
                <td align="right">{{ number_format($kembali) }}</td>
            </tr>
        </table>
    </div>
    <div class="modal-footer">
        <a href="{{ route('transaction.print', ['id' => $transaction->idPenjualan,  'bayar' => $bayar]) }}" type="button" class="btn btn-primary" target="_blank">Print</a>
        <button type="button" data-dismiss="modal" class="btn btn-secondary">Kembali</button>
    </div>
    @endif
</div>
</div>
</div>

@push('js')

    <script>
        window.addEventListener('open-print', function (e) {
            $('#print').modal('show')
        })
    </script>

@endpush