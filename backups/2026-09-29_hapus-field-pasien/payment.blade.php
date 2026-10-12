<div class="modal fade" id="payment" wire:ignore.self>
<div class="modal-dialog">
<div class="modal-content">
	<form wire:submit.prevent="store">
		<div class="modal-header">
			<h2 class="h5 modal-title">Pembayaran</h2>
			<button class="close" data-dismiss="modal">&times;</button>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<label>Kasir</label>
				<div class="input-group">
					<input type="text" class="form-control" value="{{ $user->nama }}" disabled>
				</div>
			</div>
			
			<div class="form-group">
				<label>Nama Pasien</label>
				<div class="input-group">
					<input type="text" name="namapasien" wire:model="namapasien" class="form-control" >
				</div>
			</div>
			
			<div class="form-group">
				<label>Tanggal Lahir</label>
				<div class="input-group">
					<input type="text" name="tgl_lahir" wire:model="tgl_lahir" class="form-control" >
				</div>
			</div>
			
			
			<div class="form-group">
				<label>Subtotal</label>
				<div class="input-group">
					<input type="text" id="txt1" class="form-control" value="Rp {{ number_format($subtotal) }}" disabled>
				</div>
			</div>
			
			<div class="form-group">
				<label>Diskon</label>
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text">Rp</span>
					</div>
					<input  type="text"    name="diskon" class="form-control @error('diskon') is-invalid @enderror" wire:model="diskon" placeholder="0">
					
					@error('diskon')
						<span class="invalid-feedback">{{ $message }}</span>
					@enderror
				</div>
			</div>
			<div class="form-row">
				<div class="col">
					<div class="form-group">
						<label>Grand Total</label>
						<div class="input-group">
							<input type="text" id="txt3" class="form-control" value="Rp {{ number_format($grandtotal) }}" disabled>
						</div>
					</div>
				</div>

			</div>
		
			
			<div class="form-group">
				<label>Bayar</label>
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text">Rp</span>
					</div>
					<input type="text" name="money" class="form-control @error('money') is-invalid @enderror" wire:model="money" placeholder="0">
					
					@error('money')
						<span class="invalid-feedback">{{ $message }}</span>
					@enderror
				</div>
			</div>
			<div class="form-group">
				<label>Kembali</label>
				<div class="input-group">
					<input type="text" class="form-control" value="Rp {{ number_format($return) }}" disabled>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button class="btn btn-primary" type="submit" {{ $total < 1 ? 'disabled' : '' }}>Bayar</button>
			<button type="button" data-dismiss="modal" class="btn btn-secondary">Kembali</a>
		</div>
	</form>

</div>
</div>
</div>

@push('js')

	<script>
		
	


		
		window.addEventListener('open-payment', function (e) {
			$('#payment').modal('show')
			document.getElementById('txt3').value = document.getElementById('txt1').value;
			$('#payment').find('form')[0].reset()
		})

		window.addEventListener('close-payment', function (e) {
			$('#payment').modal('hide')
		})
	</script>

@endpush