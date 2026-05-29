<div>
	<div class="modal fade" id="payment" wire:ignore.self>
	<div class="modal-dialog">
	<div class="modal-content">
		<form wire:submit.prevent="submit">
			<div class="modal-header">
				<h2 class="h5 modal-title">Pembayaran Distributor</h2>
				<button class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
		
				<div class="form-group">
					<label>Faktur</label>
					<div class="input-group">
					
					<input type="text" class="form-control @error('faktur') is-invalid @enderror" placeholder="Faktur" wire:model="faktur" name="faktur">

					@error('faktur')
						<span class="invalid-feedback">{{ $message }}</span>
					@enderror
					</div>
				</div>

				<div class="form-group">
					<label>Jatuh Tempo</label>
					<div class="input-group">
					
					<input type="date" class="form-control @error('jatuhtempo') is-invalid @enderror" placeholder="Faktur" wire:model="jatuhtempo" name="jatuhtempo">

					@error('jatuhtempo')
						<span class="invalid-feedback">{{ $message }}</span>
					@enderror
					</div>
				</div>
				<div class="form-group">
					<label>Tempo (Hari)</label>
					<div class="input-group">
					
					<input type="text" class="form-control @error('tempo') is-invalid @enderror" placeholder="Tempo Pembayaran (Hari)" wire:model="tempo" name="tempo">

					@error('tempo')
						<span class="invalid-feedback">{{ $message }}</span>
					@enderror
					</div>
				</div>


				<div class="form-group @error('distributor') is-invalid @enderror">
					<label>Distributor</label>
					<div wire:ignore>
						<select name="distributor" class="form-control custom-select" style="width: 100%;"></select>
					</div>

					@error('distributor')
						<span class="invalid-feedback">{{ $message }}</span>
					@enderror
				</div>
				<div class="form-group">
					<label>Subtotal</label>
					<input type="text" class="form-control" placeholder="Subtotal" name="subtotal" value="Rp {{ number_format($subtotal) }}" disabled>
				</div>
				<div class="form-group">
					<label>Bayar</label>
					<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text">Rp</span>
					</div>
					<input type="text" class="form-control @error('bayar') is-invalid @enderror" placeholder="Bayar" wire:model="bayar" name="bayar">

					@error('bayar')
						<span class="invalid-feedback">{{ $message }}</span>
					@enderror
					</div>
				</div>
				<div class="form-group">
					<label>Kembali</label>
					<input type="text" class="form-control" placeholder="Kembali" name="kembali" value="{{ number_format($kembali) }}" disabled>
				</div>

			</div>
			<div class="modal-footer">			
				<button type="button" data-dismiss="modal" class="btn btn-secondary">Batal</button>
				<button type="submit" class="btn btn-primary">Bayar</button>
			</div>
		</form>
	</div>
	</div>
	</div>
	
	
</div>

@push('css')
	
	<link rel="stylesheet" href="{{ asset('sufee-admin/vendors/select2/css/select2.min.css') }}">
	<link rel="stylesheet" href="{{ asset('sufee-admin/vendors/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

@endpush

@push('js')
	
	<script src="{{ asset('sufee-admin/vendors/select2/js/select2.min.js') }}"></script>

	<script>
		const distUrl = '{{ route('distributor.select') }}'
		const distributor = $('[name=distributor]')

		// const setDistributor = data => {
		// 	for (let [name, value] of Object.entries(data)) {
		// 		$(`[name=${name}]`).val(value)
		// 	}
		// }

		window.addEventListener('open-payment', function (e) {
			$('#payment').modal('show')

			$('#payment').find('form')[0].reset()

			if (!e.detail.edit) {
				distributor.empty('')
			}
		})

		window.addEventListener('close-payment', function (e) {

			$('#payment').modal('hide')
		})

		document.addEventListener('livewire:load', function () {
			const distributorId = @this.distributor
			const distributorName = @this.distributorName

			if (distributorId) {
				$('[name=distributor]').append(`<option value="${distributorId}">${distributorName}</option>`)
			}
		})

		document.querySelector('[name=bayar]').onkeyup = function () {
			const number = this.value.replace(/\D/gi, '')
			const price = new Intl.NumberFormat().format(+number)

			this.value = price
		}

		distributor.select2({
			placeholder: 'Distributor',
			theme: 'bootstrap4',
			ajax: {
				url: distUrl,
				type: 'post',
				data: params => ({
					name: params.term,
					_token: csrf
				}),
				processResults: res => ({
					results: res
				}),
				cache: true
			}
		})

		distributor.on('select2:select', function (e) {
			const data = e.params.data
			
			@this.set('distributor', data.id)
		})
	</script>
@endpush