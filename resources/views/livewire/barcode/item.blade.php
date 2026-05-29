<div class="card">
    <div class="card-header">
        <h2 class="h6 font-weight-bold mb-0 card-title">Data Barcode</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" id="items-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Barcode</th>
                        <th>Obat</th>
                        <th colspan="2">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item['barcode'] }}</td>
                            <td>{{ $item['obat'] }}</td>
                            <td>
                                <input type="text" class="form-control item-qty" data-barcode="{{ $item['barcode'] }}" value="{{ $item['jumlah'] }}">
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger" wire:click="remove({{ $loop->index }})">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" align="center">Kosong</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer" id="action-button">
        <button class="btn btn-primary" wire:click="print" {{ count($items) ? '' : 'disabled' }}>Print</button>
        <button class="btn btn-danger" wire:click="clear" {{ count($items) ? '' : 'disabled' }}>Reset</button>
        <a href="{{ route('stuff.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

@push('js')
    <script>
        jQuery(function ($) {
            $('#items-table').on('keyup', '.item-qty', function () {
                const input = $(this)
                const val = input.val().replace(/\D/gi, '')

                input.val(val)
            })

            $('#items-table').on('focusout', '.item-qty', function () {
                const input = $(this)
                let val = +input.val().replace(/\D/gi, '')

                val = val < 1 ? 1 : val

                console.log(input.data('barcode'))

                @this.update(input.data('barcode'), val)

                input.val(val)
            })

            $('#action-button button').attr('disabled', 'disabled')
        })
    </script>
@endpush