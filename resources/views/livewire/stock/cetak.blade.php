<div class="modal fade" id="print" wire:ignore.self>
<div class="modal-dialog">
<div class="modal-content">
    @if ($stock)
    <div class="modal-header">
        <center class="w-100">
            <h5>{{ site('nama_toko') }}</h5>
            <p class="mb-0">{{ site('alamat_toko') }}</p>
            <p class="mb-0">{{ site('telepon_toko') }}</p>
        </center>
    </div>
    <div class="modal-body pb-0 border-bottom">
        <ul class="list-unstyled row">
            <li class="col-sm-8">
                <b>No Faktur</b> : {{ $stock->idPembelian }}
            </li>
            <li class="col-sm-4 text-right">
                <b>Distributor</b> : {{ $stock->namaDist }}
            </li>
            <li class="col-sm-6">
                <b>Tanggal</b> : {{ date('d/m/y H:i A', strtotime($stock->tanggal)) }}
            </li>
            <li class="col-sm-6 text-right">
                <b>Gudang</b> : {{ $stock->namaUser }}
            </li>
        </ul>
    </div>
    <div class="modal-body pb-0">
            <table class="table table-striped" width="100%">
                <tr>
                    <th>Nama Obat</th>
                    <th>Qty</th>
                    <th align="right">Total</th>
                </tr>
                @php
                    $subtotal = 0
                @endphp

                @foreach ($stock->detail as $detail)
                    @php
                        $subtotal += $detail->jumlah * $detail->hargaPokok
                    @endphp

                    <tr>
                        <td>{{ $detail->obat }}</td>
                        <td>{{ $detail->jumlah }}  x {{ number_format($detail->hargaPokok) }}</td>
                        <td align="right">{{ number_format($detail->jumlah * $detail->hargaPokok) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2">Total</td>
                    <td align="right">{{ number_format($subtotal) }}</td>
                </tr>
                <tr>
                    <td colspan="2">Bayar</td>
                    <td align="right">{{ number_format($bayar) }}</td>
                </tr>
                <tr>
                    <td colspan="2">Kembali</td>
                    <td align="right">{{ number_format($kembali) }}</td>
                </tr>
            </table>
    </div>
    <div class="modal-footer">
        <a href="{{ route('stock.print', $stock->idPembelian) }}" target="_blank" type="button" class="btn btn-primary">Print</a>
        <button type="button" wire:click="edit" class="btn btn-success">Edit</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kembali</button>
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

        window.addEventListener('close-print', function (e) {
            $('#print').modal('hide')
        })
    </script>

@endpush