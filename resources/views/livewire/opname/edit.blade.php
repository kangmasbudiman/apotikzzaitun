<div class="col-sm-4">
    <form class="card" wire:submit.prevent="save">
        <div class="card-header">
            <h2 class="card-title h6 mb-0 font-weight-bold">Edit Data</h2>
        </div>
        <div class="card-body">
            {{-- <div class="form-group">
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
            </div> --}}
            <div class="form-group">
                <label>Nama Buku</label>
                <input type="text" class="form-control" placeholder="Nama Buku" name="judul" value="{{ $opname->judul }}" disabled>
            </div>
            <div class="form-group">
                <label>Stok Sistem</label>
                <input type="text" class="form-control" placeholder="Stok" name="stock" value="{{ $opname->stokSistem }}" disabled>
            </div>
            <div class="form-group">
                <label>Harga Pokok</label>
                <input type="text" class="form-control" placeholder="Harga Pokok" name="hargaPokok" value="{{ $opname->hargaPokok }}" disabled>
            </div>
            <div class="form-group">
                <label>Stok Nyata</label>
                <input type="text" class="form-control @error('opname.stokNyata') is-invalid @enderror" placeholder="Stok Nyata" wire:model.defer="opname.stokNyata">

                @error('opname.stokNyata')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea class="form-control @error('opname.keterangan') is-invalid @enderror" placeholder="Keterangan" wire:model.defer="opname.keterangan"></textarea>

                @error('opname.keterangan')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('opname.index') }}" class="btn btn-secondary">Batal</a>
            <button class="btn btn-primary" type="submit" wire:loadint.attr="disabled" wire:target="save">Simpan</button>
        </div>
    </form>

{{--     <div wire:ignore>
        @include('stuff.modal')
    </div> --}}
</div>

{{-- @push('js')

    <script>

        const $ = jQuery
        
        const csrf = '{{ csrf_token() }}'
        const buku = $('[name=barcode]')

        const setStuff = data => {
            for (let [name, value] of Object.entries(data)) {
                if (name === 'ppn') {
                    $(`[name=${name}]`).val(value + ' %')
                } else {
                    $(`[name=${name}]`).val(value)
                }
            }
        }

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
                { data: 'judul' },
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
            const data = table.row($(this).parents('tr')).data()

            buku.val(data.barcode)

            setStuff(data)
            @this.set('barcode', data.barcode)
            @this.set('stokSistem', data.stock)

            $('#stuff').modal('hide')
        })
    </script>

@endpush --}}