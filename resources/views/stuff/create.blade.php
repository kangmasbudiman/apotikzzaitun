@extends('_layouts.app')

@section('title', 'Tambah Obat')

@section('content')
	
	<div class="mb-4">
		<div>
			<div class="card">
			<form action="{{ route('stuff.store') }}" method="post">
				@csrf
				<div class="card-header">
					<h2 class="h6 font-weight-bold mb-0 card-title">Tambah Obat</h2>
				</div>
				<div class="card-body">
					
					<div class="form-group">
					<label>Obat*</label>
								<input type="text" class="form-control @error('obat') is-invalid @enderror" name="obat" placeholder="Obat" value="{{ old('obat') }}" autofocus>

								@error('obat')
									<span class="invalid-feedback">{{ $message }}</span>
								@enderror
					</div>

					<div class="form-row">
						<div class="col">
							<div class="form-group">
							<label>No ISBN</label>
						<input type="text" class="form-control @error('noisbn') is-invalid @enderror" name="noisbn" placeholder="No ISBN" value="{{ old('noisbn') }}">

						@error('noisbn')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
							</div>
						</div>
						<div class="col">
							<div class="form-group">
							<label>Barcode*</label>
						<div class="input-group">
							<input type="text" class="form-control @error('barcode') is-invalid @enderror" name="barcode" placeholder="Barcode" value="{{ old('barcode') }}" >
							<div class="input-group-append">
								<button class="btn btn-outline-secondary generate-barcode" type="button">Generate</button>
							</div>

							@error('barcode')
								<span class="invalid-feedback">{{ $message }}</span>
							@enderror
						</div>
							</div>
						</div>
					</div>

					<div class="form-row">
						<div class="col">
							<div class="form-group">
								<label>Kategori*</label>
								<select class="form-control custom-select @error('idKategori') is-invalid @enderror" name="idKategori"></select>

								@error('idKategori')
									<span class="invalid-feedback">{{ $message }}</span>
								@enderror
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label>Rak*</label>
								<select class="form-control custom-select @error('idRak') is-invalid @enderror" name="idRak"></select>

								@error('idRak')
									<span class="invalid-feedback">{{ $message }}</span>
								@enderror
							</div>
						</div>
					</div>
					<div class="form-row">
						<div class="col">
							<div class="form-group">
							<label>Suplier*</label>
								<input type="text" class="form-control @error('suplier') is-invalid @enderror" name="suplier" placeholder="Suplier" value="{{ old('suplier') }}">

								@error('suplier')
									<span class="invalid-feedback">{{ $message }}</span>
								@enderror
							</div>
						</div>
						
						<div class="col">
							<div class="form-group">
								<label>Tahun*</label>
								<input type="text" class="form-control @error('tahun') is-invalid @enderror" name="tahun" placeholder="Tahun" value="{{ old('tahun') }}">

								@error('tahun')
									<span class="invalid-feedback">{{ $message }}</span>
								@enderror
							</div>
						</div>
				
					</div>
					<div class="form-row">
						<div class="col">
							<div class="form-group">
								
								<label>PPN (%)</label>
								<input type="text" id="ppn" class="form-control discount @error('disc') is-invalid @enderror" name="ppn" placeholder="PPN" value="{{ old('ppn', 0) }}">

								@error('ppn')
									<span class="invalid-feedback">{{ $message }}</span>
								@enderror
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label>Margin (%)</label>
								<input type="number" id="margin" class="form-control @error('margin') is-invalid @enderror" name="margin" placeholder="Margin" value="{{ old('margin') }}">

								@error('margin')
									<span class="invalid-feedback">{{ $message }}</span>
								@enderror
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
									<input type="text" id="hargaPokok" class="form-control price @error('hargaPokok') is-invalid @enderror" name="hargaPokok" placeholder="Harga Pokok" value="{{ old('hargaPokok') }}">

									@error('hargaPokok')
										<span class="invalid-feedback">{{ $message }}</span>
									@enderror
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
									<input type="text" id="hargaJual" readonly class="form-control price1 @error('hargaJual') is-invalid @enderror" name="hargaJual" placeholder="Harga Jual" value="{{ old('hargaJual') }}">

									@error('hargaJual')
										<span class="invalid-feedback">{{ $message }}</span>
									@enderror
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card-footer">
					
					<a class="btn btn-secondary" href="{{ route('stuff.index') }}">Kembali</a>
					<button class="btn btn-primary" type="submit">Simpan</button>
				</div>
			</form>
			</div>
		</div>
	</div>

@endsection

@push('css')
	
	<link rel="stylesheet" href="{{ asset('sufee-admin/vendors/select2/css/select2.min.css') }}">
	<link rel="stylesheet" href="{{ asset('sufee-admin/vendors/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

@endpush

@push('js')
	
	<script src="{{ asset('sufee-admin/vendors/select2/js/select2.min.js') }}"></script>

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




			$('.discount').on('keyup', function() {
				const number = this.value.replace(/[^0-9\.]/g, '')

				console.log(number)

				if (number.toString().length >= 4) {
					this.value = number.toString().substr(0, 4)
				} else {
					this.value = number
				}

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
		})
	</script>

@endpush