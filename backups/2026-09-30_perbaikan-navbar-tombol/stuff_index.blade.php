@extends('_layouts.app')

@section('title', 'Data Obat')

@section('content')
	
	@if (session('success'))
		<div class="alert alert-success alert-dismissible">
			{{ session('success') }}
			<button class="close" data-dismiss="alert">&times;</button>
		</div>
	@endif

	<div id="alert"></div>
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h2 class="h6 font-weight-bold mb-0 card-title">Data Obat</h2>
			@can('isAdminGudang')
				<div>
					@can('isAdmin')
					    <button data-toggle="collapse" data-target="#filter" class="btn btn-warning btn-sm">Filter</button>
					@endcan
					<a href="{{ route('barcode.search') }}" class="btn btn-warning btn-sm mr-1">Print Semua Barcode</a>
	                @can('isAdmin')
	                	<button class="btn btn-success btn-sm" data-toggle="modal" data-target="#import">Import Excel</button>
		                <a class="btn btn-success btn-sm" href="{{ route('stuff.export') }}">Export Excel</a>
	                @endcan
					<a href="{{ route('stuff.create') }}" class="btn btn-primary btn-sm">Tambah Data</a>
				</div>
			@endcan
		</div>
		<div class="collapse" id="filter">
			<form action="{{ route('stuff.printall') }}" class="card-body border-bottom" target="_blank">
				<div class="form-row">
					<div class="col-sm-2">
						<div class="form-group">
							<label>Suplier</label>
							<select name="suplier" style="width: 100%;" class="form-control custom-select @error('suplier') is-invalid @enderror">
								<option value="" selected>Suplier</option>
								@forelse ($penerbit as $item)
									<option value="{{ $item->penerbit }}">{{ $item->penerbit }}</option>
								@empty
									<option disabled selected>Kosong</option>
								@endforelse
							</select>

							@error('suplier')
								<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>
					</div>
					<div class="col-sm-2">
						<div class="form-group">
							<label>Tahun</label>
							<select name="tahun" style="width: 100%;" class="form-control custom-select @error('tahun') is-invalid @enderror">
								<option value="" selected>Tahun</option>
								@forelse ($tahun as $item)
									<option value="{{ $item->tahun }}">{{ $item->tahun }}</option>
								@empty
									<option disabled selected>Kosong</option>
								@endforelse
							</select>

							@error('tahun')
								<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>
					</div>
				</div>
				<button class="btn btn-warning filter mr-1" type="button">Filter</button>
				<button class="btn btn-primary" type="submit">Print</button>
				<button class="btn btn-danger" type="button" id="remove-all">Hapus</button>
			</form>
		</div>
		{{-- <div class="collapse" id="print-barcode">
			<form action="{{ route('stuff.printallbarcode') }}" class="card-body border-bottom">
				<div class="form-row">
					<div class="col-sm-2">
						<div class="form-group">
							<label>Tampilkan</label>
							<input type="number" name="show" class="form-control" placeholder="Tampilkan">
						</div>
					</div>
				</div>
				<button class="btn btn-primary" type="submit">Print</button>
			</form>
		</div> --}}
		<div class="card-body">
			<div class="table-responsive">
				<table id="data" class="table table-bordered" width="100%">
					<thead>
						<tr>
							<th><input type="checkbox" id="select-all"></th>
							<th>No</th>
							<th>IdObat</th>
							<th>Barcode</th>
							<th>Nama Obat</th>
							<th>Suplier</th>
							<th>Tahun</th>
							<th>Stok</th>
							<th width="65px">Harga Jual</th>
							<th width="105px">Action</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>

	<div class="modal" id="edit">
	<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
	<div class="modal-content">
	<form class="post" action="" method="post">
		@csrf
		@method('PUT')
		<div class="modal-header">
			<h5 class="modal-title">Edit Data</h5>
			<button class="close" data-dismiss="modal">&times;</button>
		</div>
		<div class="modal-body">
			<input type="hidden" name="id">
			<div class="form-group">
			<label>Nama Obat*</label>
						<input type="text" class="form-control @error('obat') is-invalid @enderror" name="obat" placeholder="Obat" value="{{ old('obat') }}">

						<span class="invalid-feedback"></span>
			</div>
		

			<div class="form-row">
				<div class="col">
					<div class="form-group">
					<label>No ISBN</label>
				<input type="text" class="form-control @error('noisbn') is-invalid @enderror" name="noisbn" placeholder="No ISBN" value="{{ old('noisbn') }}" autofocus>

				<span class="invalid-feedback"></span>
					</div>
				</div>
				<div class="col">
					<div class="form-group">
					<label>Barcode*</label>
					
					<div class="input-group">
							<input type="text" class="form-control @error('barcode') is-invalid @enderror" name="barcode" placeholder="Barcode" value="{{ old('barcode') }}" autofocus>
							<div class="input-group-append">
								<button class="btn btn-outline-secondary generate-barcode" type="button">Generate</button>
							</div>

							@error('barcode')
								<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>

				<span class="invalid-feedback"></span>
					</div>
				</div>
			</div>

			
			<div class="form-row">
				<div class="col">
					<div class="form-group">
						<label>Kategori*</label>
						<select class="form-control custom-select" name="idKategori" style="width: 100%;"></select>

						<span class="invalid-feedback"></span>
					</div>
				</div>
				<div class="col">
					<div class="form-group">
						<label>Rak*</label>
						<select class="form-control custom-select" name="idRak" style="width: 100%;"></select>

						<span class="invalid-feedback"></span>
					</div>
				</div>
			</div>
			<div class="form-row">
				<div class="col">
					<div class="form-group">
					<label>Suplier*</label>
						<input type="text" class="form-control @error('suplier') is-invalid @enderror" name="suplier" placeholder="Penerbit" value="{{ old('suplier') }}">

						<span class="invalid-feedback"></span>
					</div>
				</div>

				<div class="col">
					<div class="form-group">
						<label>Tahun</label>
						<input type="text" class="form-control @error('tahun') is-invalid @enderror" name="tahun" placeholder="Tahun" value="{{ old('tahun') }}">

						<span class="invalid-feedback"></span>
					</div>
				</div>
			
			</div>
			<div class="form-row">
				<div class="col">
					<div class="form-group">
					<label>PPN (%)</label>
						<input type="text" class="form-control discount @error('ppn') is-invalid @enderror" name="ppn" placeholder="PPN" value="{{ old('ppn') }}">

						<span class="invalid-feedback"></span>
					</div>
				</div>
				<div class="col">
					<div class="form-group">
						<label>Margin(%)</label>
						<input type="numer" class="form-control @error('margin') is-invalid @enderror" name="margin" placeholder="Margin" value="{{ old('margin') }}">

						<span class="invalid-feedback"></span>
					</div>
				</div>
			</div>
			<div class="form-row">
				<div class="col">
					<div class="form-group">
						<label>Harga Pokok*</label>
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">Rp</span>
							</div>
							<input type="text" class="form-control price @error('hargaPokok') is-invalid @enderror" name="hargaPokok" placeholder="Harga Pokok" value="{{ old('hargaPokok') }}">

							<span class="invalid-feedback"></span>
						</div>
					</div>
				</div>
				<div class="col">
					<div class="form-group">
						<label>Harga Jual*</label>
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">Rp</span>
							</div>
							<input type="text" class="form-control price @error('hargaJual') is-invalid @enderror" name="hargaJual" placeholder="Harga Jual" value="{{ old('hargaJual') }}">

							<span class="invalid-feedback"></span>
						</div>
					</div>
				</div>
			</div>
		
		</div>
		<div class="modal-footer">

			<button class="btn btn-secondary" data-dismiss="modal">Batal</button>
			<button class="btn btn-primary" type="submit">Simpan</button>
		</div>
	</form>
	</div>
	</div>
	</div>



    <div class="modal" id="import">
    <div class="modal-dialog">
    <div class="modal-content">
    <form action="{{ route('stuff.import') }}" class="post" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title">Import Excel</h5>
            <button class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>File Excel</label>
                <div class="custom-file">
                    <label class="custom-file-label">Browse</label>
                    <input type="file" class="custom-file-input" name="file">
                <span class="invalid-feedback"></span>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" type="submit">Simpan</button>
            <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
    </form>
    </div>
    </div>
    </div>

	<div class="modal" id="show">
	<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
	<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title">Detail Obat</h5>
			<button class="close" data-dismiss="modal">&times;</button>
		</div>
		<div class="modal-body">
			<table class="table table-bordered table-striped">
				<tr>
					<th>ID Buku</th>
					<td data-key="idBuku"></td>
				</td>
				<tr>
					<th>No ISBN</th>
					<td data-key="noisbn"></td>
				</td>
				<tr>
					<th>Barcode</th>
					<td data-key="barcode"></td>
				</td>
				<tr>
					<th>Kategori</th>
					<td data-key="category"></td>
				</td>
				<tr>
					<th>Obat</th>
					<td data-key="obat"></td>
				</td>
				<tr>
					<th>Rak</th>
					<td data-key="rack"></td>
				</td>
				<tr>
					<th>Tahun</th>
					<td data-key="tahun"></td>
				</td>
				<tr>
					<th>Suplier</th>
					<td data-key="suplier"></td>
				</td>
				<tr>
					<th>Margin</th>
					<td data-key="margin"></td>
				</td>
				<tr>
					<th>Stok</th>
					<td data-key="stock"></td>
				</td>
				<tr>
					<th>Harga Pokok</th>
					<td data-key="purchase_price"></td>
				</td>
				<tr>
					<th>HargaJual</th>
					<td data-key="price"></td>
				</td>
				<tr>
					<th>PPN(%)</th>
					<td data-key="ppn"></td>
			</td>
			</table>
		</div>
		<div class="modal-footer">
			<button class="btn btn-secondary" data-dismiss="modal">Batal</button>
		</div>
	</div>
	</div>
	</div>

@endsection

@push('css')
	
	<link rel="stylesheet" href="{{ asset('sufee-admin/vendors/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
	<link rel="stylesheet" href="{{ asset('sufee-admin/vendors/select2/css/select2.min.css') }}">
	<link rel="stylesheet" href="{{ asset('sufee-admin/vendors/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

@endpush

@push('js')
	
	<script src="{{ asset('sufee-admin/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('sufee-admin/vendors/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
	<script src="{{ asset('sufee-admin/vendors/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('sufee-admin/vendors/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
	<script>
		jQuery(function ($) {
			const generate = () => {
				const val = Math.floor(1000 + Math.random() * 9999)

				let barcode = ('97862300'+val).substr(0,12)

				if (barcode.length < 12) {
					barcode += Math.floor(Math.random() * 9 + 1)
				}

				const digit = barcode.split('').reduce((total, val, index) => {
					const num = (index + 1) % 2 === 0 ? 3 : 1
					const sum = num * val

					return total + sum
				}, 0)

				return { barcode, digit }
			}

			const getDigit = () => {
				const { barcode, digit } = generate()
				const sisa = 10 - (digit % 10)				

				if (sisa < 10) {
					return barcode + '' + sisa
				}
				getDigit()
			}

			$('.price').on('keyup', function() {
				const number = Number(this.value.replace(/\D/g, ''))
				const price = new Intl.NumberFormat().format(number)
				const ppn= $('[name=ppn]').val()
				const margin= $('[name=margin]').val()
				var hargajualq=(ppn/100)*number + number;
				var hargakonsumen=(margin/100)*hargajualq + hargajualq;				
				$('[name=hargaJual]').val(hargakonsumen)
				this.value = price
			})
			$('.generate-barcode').on('click', function () {
				const barcode = getDigit()

				$('[name=barcode]').val(barcode)
			})
			$('[name=idKategori]').select2({
				placeholder: 'Kategori',
				theme: 'bootstrap4',
				ajax: {
					url: '{{ route('category.select') }}',
					type: 'post',
					data: params => ({
						name: params.term,
						_token: '{{ csrf_token() }}'
					}),
					processResults: res => ({
						results: res
					}),
					cache: true
				}
			})
			$('[name=idRak]').select2({
				placeholder: 'Rak',
				theme: 'bootstrap4',
				ajax: {
					url: '{{ route('rack.select') }}',
					type: 'post',
					data: params => ({
						name: params.term,
						_token: '{{ csrf_token() }}'
					}),
					processResults: res => ({
						results: res
					}),
					cache: true
				}
			})
			$('.discount').on('keyup', function() {
				const number = this.value.replace(/[^0-9\.]/g, '')

				console.log(number)

				if (number.toString().length >= 4) {
					this.value = number.toString().substr(0, 4)
				} else {
					this.value = number
				}

			})
		})
	</script>
	<script>
		const ajaxUrl = '{{ route('stuff.datatables') }}'
		const updateUrl = '{{ route('stuff.update', ':id') }}'
		const deleteUrl = '{{ route('stuff.destroy', ':id') }}'
		const destroyBatchUrl = '{{ route('stuff.destroy-batch') }}'
		const categoryUrl = '{{ route('category.select') }}'
		const rackUrl = '{{ route('rack.select') }}'
		const csrf = '{{ csrf_token() }}'
	</script>

	<script src="{{ asset('js/stuff.js') }}"></script>

@endpush