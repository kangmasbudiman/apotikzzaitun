<div class="card">
    <div class="card-header">
        <h2 class="h6 font-weight-bold mb-0 card-title">Cari Barcode</h2>
    </div>
    <div class="card-body">
        <form wire:submit.prevent="submit" id="search-barcode">
            <div class="form-group">
                <label>Barcode</label>
                <div class="input-group">
                    <input name="barcode" class="form-control @error('barcode') is-invalid @enderror" placeholder="Barcode" wire:model="barcode"></input>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#stuff"><i class="fa fa-search"></i></button>
                    </div>

                    @error('barcode')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <button class="btn btn-primary" wire:loading.attr="disabled" wire:target="submit">Tambah</button>
        </form>
    </div>
    <div wire:ignore>
        @include('stuff.modal')
    </div>
</div>

@push('js')
    <script>
        jQuery(function ($) {
            const csrf = '{{ csrf_token() }}'
            const buku = $('[name=barcode]')

            const table = $('#stuff table').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: ajaxUrl,
                    type: 'post',
                    data: {
                        _token: csrf
                    }
                },
                columns: [
                    { data: 'DT_RowIndex' },
                    { data: 'idBuku' },
                    { data: 'barcode' },
                    { data: 'obat' },
                    { data: 'purchase_price' },
                    { data: 'stock' },
                    {
                        data: null,
                        render: () => {
                            return `<button class="btn btn-sm btn-primary">Pilih</button>`
                        }
                    }
                ],
            })

            $('#stuff tbody').on('click', 'button', function () {
                const { barcode, obat, idBuku } = table.row($(this).parents('tr')).data()

                buku.val(barcode)

                @this.setData({ barcode, obat, idBuku })

                $('#stuff').modal('hide')
            })

            Livewire.on('add-barcode', function () {
                $('#search-barcode')[0].reset()
            })
        })
    </script>
@endpush