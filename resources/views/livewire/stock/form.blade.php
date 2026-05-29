<div class="row">
	<div class="col-sm-4">
		<div class="card">
			<div class="card-header">
				<h2 class="card-title h6 mb-0 font-weight-bold">Pembelian</h2>
			</div>
			<div class="card-body">
				<div class="form-group">
					<label>No Faktur</label>
					<input type="text" class="form-control" name="name" value="{{ $latestId }}" disabled>
				</div>
				<div class="form-group">
					<label>Tanggal</label>
					<input type="text" class="form-control" name="name" value="{{ date('d/m/Y') }}" disabled>
				</div>
				<div class="form-group">
					<label>Operator</label>
					<input type="text" class="form-control" name="name" value="{{ auth()->user()->nama }}" disabled>
				</div>
			
			</div>
		</div>
	</div>

	<div class="col-sm-4">
		<form wire:submit.prevent="submit" class="buku">
			<div class="card">
				<div class="card-header">
					<h2 class="h6 card-title font-weight-bold mb-0">Input / Scan Obat</h2>
				</div>
				<div class="card-body">
					<div class="form-group" >
						<label>Barcode </label>
						<div class="input-group" >
							<input name="barcode" class="form-control @error('barcode') is-invalid @enderror" placeholder="Barcode" wire:model="barcode" autofocus></input>
							<div class="input-group-append">
								<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#stuff"><i class="fa fa-search"></i></button>
							</div>

							@error('barcode')
								<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>
					</div>
					<div class="form-group" >
						<label>Nobatch </label>
						<div class="input-group" >
							<input name="nobatch" class="form-control @error('nobatch') is-invalid @enderror" placeholder="No Batch" wire:model="nobatch"></input>
						

							@error('nobatch')
								<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>
					</div>
					<div class="form-group" >
						<label>Expired </label>
						<div class="input-group" >
							<input name="expired" type="date" class="form-control @error('expired') is-invalid @enderror"  wire:model="expired"></input>
						

							@error('expired')
								<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>
					</div>
					<div class="form-group">
						<label>Jumlah</label>
						<div class="input-group">
							<input type="number" class="form-control @error('jumlah') is-invalid @enderror" name="jumlah" placeholder="Jumlah" wire:model="jumlah">
							<div class="input-group-append" wire:ignore>
								<button class="btn btn-primary" type="submit" name="submit">Tambah</button>
							</div>

							@error('jumlah')
								<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>

	<div class="col-sm-4">
		<div class="card" id="book-data">
			<div class="card-header">
				<h2 class="card-title h6 mb-0 font-weight-bold">Data Obat</h2>
			</div>
			<div class="card-body">
				<div class="form-group">
					<label>Nama Obat</label>
					<input type="text" class="form-control" name="obat" placeholder="Obat" disabled>
				</div>
				<div class="form-group">
					<label>Harga</label>
					<input type="text" class="form-control" name="price" placeholder="Harga" disabled>
				</div>
				<div class="form-group">
					<label>Stok</label>
					<input type="text" class="form-control" name="stock" placeholder="Stok Barang" disabled>
				</div>
			
			</div>
		</div>
	</div>
	

	<div wire:ignore>
		@include('stuff.modal')
	</div>
</div>

@push('js')
	
	<script>

		Livewire.on('addData', function () {
			// $('.buku .form-control').val('')
			$('[name=barcode]').empty()
		})

		const $ = jQuery

		const stuffUrl = '{{ route('stuff.select') }}'
		
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

		$('.form-stock .form-control').val('')

		// buku.on('select2:select', function (e) {
		// 	const data = e.params.data

		// 	setStuff(data)

		// 	@this.set('barcode', data.id)
		// })

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
		    const data = table.row($(this).parents('tr')).data()

		    buku.val(data.barcode)

		    setStuff(data)
		    @this.set('barcode', data.barcode)

		    $('#stuff').modal('hide')
		})

		window.addEventListener('reset-stuff', function () {
			table.ajax.reload()
		})

		window.addEventListener('reset-data-form', function () {
			$('#book-data').find('input').val('')
		})
	</script>

@endpush