<div class="row">
	<div class="col-sm-4">
		<div class="card">
			<div class="card-header">
				<h2 class="card-title h6 mb-0 font-weight-bold">Transaksi</h2>
			</div>
			<div class="card-body">
				<div class="form-group">
					<label>No Faktur</label>
					<input type="text" class="form-control" name="id" value="{{ $latestId }}" disabled>
				</div>
				<div class="form-group">
					<label>Tanggal</label>
					<input type="text" class="form-control" name="tanggal" value="{{ date('d/m/Y') }}" >
				</div>
				<div class="form-group">
					<label>Operator</label>
					<input type="text" class="form-control" name="operator" value="{{ auth()->user()->nama }}" disabled>
				</div>
			</div>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="card">
		<form wire:submit.prevent="store">
			<div class="card-header">
				<h2 class="card-title h6 mb-0 font-weight-bold">Input / Scan Obat</h2>
			</div>
			<div class="card-body">
				@if (session('error'))
				    <div class="alert alert-danger alert-dismissible">
				        {{ session('error') }}
				        <button class="close" data-dismiss="alert">&times;</button>
				    </div>
				@endif
				<div class="form-group">
					<label>Barcode</label>
					<div class="input-group">
						<input placeholder="Barcode" class="form-control @error('barcode') is-invalid @enderror" wire:model="barcode" autofocus/>
						<div class="input-group-append">
							<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#stuff"><i class="fa fa-search"></i></button>
						</div>
						
						@error('barcode')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
				</div>
				<hr>
				<div class="form-group">
					<label>Jumlah</label>
					<div class="input-group">
						<input type="number" class="form-control @error('total') is-invalid @enderror" placeholder="Jumlah" wire:model="total">
						<div class="input-group-append">
							<button class="btn btn-primary submit" wire:loading.attr="disabled" wire:target="store">Tambah</button>
						</div>
						
						@error('total')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>					
				</div>
			</div>
			{{-- <div class="card-footer">
				<button class="btn btn-primary submit" wire:loading.attr="disabled" wire:target="store">Tambah</button>
			</div> --}}
		</form>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="card">
			<div class="card-header">
				<h2 class="card-title h6 mb-0 font-weight-bold">Data Obat</h2>
			</div>
			<div class="card-body">
				<div class="form-group">
					<label>Nama Obat</label>
					<input type="text" class="form-control" name="obat" value="{{ $stuff->obat ?? 'Obat' }}" disabled>
				</div>
				<div class="form-group">
					<label>Harga</label>
					<input type="text" class="form-control" name="price" value="{{ isset($stuff->hargaJual) ? 'Rp '.number_format($stuff->hargaJual) : 'Harga' }}" disabled>
				</div>
				<div class="form-group">
					<label>Stok</label>
					<input type="text" class="form-control" name="stock" value="{{ $stuff->stock ?? 'Stok' }}" placeholder="Stok Barang" disabled>
				</div>
			</div>
		</div>
	</div>

	<div wire:ignore>
		@include('stuff.modal', ['type' => 'sell'])
	</div>
</div>

@push('css')
	<link rel="stylesheet" href="{{ asset('sufee-admin/vendors/select2/css/select2.min.css') }}">
	<link rel="stylesheet" href="{{ asset('sufee-admin/vendors/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@push('js')
	<script src="{{ asset('sufee-admin/vendors/select2/js/select2.min.js') }}"></script>

	<script>
		const $ = jQuery
		const csrf = '{{ csrf_token() }}'

		const book = $('[name=barcode]')

		window.addEventListener('reset', function () {
			book.empty()
		})

		$(document).ready(function () {
			// book.select2({
			// 	placeholder: 'Barcode',
			// 	theme: 'bootstrap4',
			// 	ajax: {
			// 		url: '{{ route('stuff.select') }}',
			// 		type: 'post',
			// 		data: params => ({
			// 			name: params.term,
			// 			_token: '{{ csrf_token() }}'
			// 		}),
			// 		processResults: res => {
			// 			res.map(stuff => stuff.text = `${stuff.barcode} - ${stuff.text}`);
			// 			return {
			// 				results: res
			// 			}
			// 		},
			// 		cache: true
			// 	}
			// })

			// book.on('select2:select', e => {
			// 	@this.set('barcode', e.params.data.id)
			// 	@this.search()
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
			        { data: 'price' },
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

			    book.val(data.barcode)

			    @this.set('barcode', data.barcode)
			    @this.search()

			    $('#stuff').modal('hide')
			})

			window.addEventListener('reset-stuff', function () {
				table.ajax.reload()
			})

		})
	</script>
@endpush