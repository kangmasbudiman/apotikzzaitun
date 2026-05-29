<div>
	<div class="card">
		<div class="card-header d-flex align-items-center justify-content-between">
			<h2 class="h6 font-weight-bold mb-0 card-title">Data Pembelian</h2>
			<div>
			<a href="{{ route('stock.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
				<button class="btn btn-sm btn-danger" {{ count($data) ? '' : 'disabled' }} wire:click="clear">Reset</button>
				<button class="btn btn-sm btn-primary" {{ count($data) ? '' : 'disabled' }} id="payment-btn" wire:click="$emit('open-payment')">Bayar</button>
			</div>
		</div>
		<div class="card-body">
			@if (session()->has('danger'))
				<div class="alert alert-danger alert-dismissible">
					{{ session('danger') }}
					<button class="close" data-dismiss="alert">&times;</button>
				</div>
			@endif
			<div class="table-responsive">
				<table class="table table-bordered" width="100%">
					<thead>
						<tr>
							<th>No</th>
							<th>Barcode</th>
							<th>Nama Obat</th>
							<th width="75px">Harga</th>
							<th>Qty</th>
							
							<th width="120px">Nobatch</th>
							<th width="120px">Expired</th>
							<th width="120px">Total</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody class="stock-table">
						@php
							$total = 0
						@endphp

						@forelse ($data as $buku)
							@php
								$subtotal = $buku['hargaPokok'] * $buku['jumlah'];
								$total += $subtotal
							@endphp

							<tr>
								<td>{{ $loop->iteration }}</td>
								<td>{{ $buku['barcode'] }}</td>
								<td>{{ $buku['obat'] }}</td>
								<td>{{ number_format($buku['hargaPokok']) }}</td>
								
								<td>
									<div class="input-group">
										<div class="input-group-prepend">
											<button class="btn btn-outline-secondary" wire:click="decrement({{ $loop->index }})">-</button>
										</div>
										<input type="text" class="form-control qty" data-id="{{ $loop->index }}" data-val="{{ $buku['jumlah'] }}" value="{{ $buku['jumlah'] }}" width="200px">
										<div class="input-group-append">
											<button class="btn btn-outline-secondary" wire:click="increment({{ $loop->index }})">+</button>
										</div>
										
										<span class="invalid-feedback"></span>
									</div>
								</td>
								
								<td>{{ $buku['nobatch'] }}</td>
								<td>{{ $buku['expired'] }}</td>
								<td>Rp {{ number_format($subtotal) }}</td>
								<td>
									<button class="btn btn-sm btn-danger" wire:click="remove({{ $loop->index }})"><i class="fa fa-trash"></i></button>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="9" align="center">Kosong</td>
							</tr>
						@endforelse
					</tbody>
					@if ($data)
						<tfoot>
							<tr class="bg-success text-white">
								<td style="border: none;" align="center" colspan="7"><b>Subtotal</b></td>
								<td style="border: none;">Rp {{ number_format($total) }}</td>
								<td style="border: none;"></td>
							</tr>
						</tfoot>
					@endif
				</table>
			</div>
		</div>
	</div>
</div>

@push('js')
	<script>
		$(function () {
			$('.stock-table').on('keyup', '.qty', function () {
				const number = this.value.replace(/\D/gi, '')
				const { id } = $(this).data()

				if (number) {
					if (number <= 0) {
						$(this).addClass('is-invalid')
						$(this).siblings('.invalid-feedback').html('Jumlah terlalu sedikit')
					} else {
						$(this).removeClass('is-invalid')
						Livewire.emit('updateQty', id, number)

						this.value = number
					}
				}
			})

			$('.stock-table').on('focusout', '.qty', function () {
				if ($(this).hasClass('is-invalid')) {
					this.value = $(this).data('val')
				}

				if (!this.value) {
					this.value = $(this).data('val')
				}

				$(this).removeClass('is-invalid')
			})

			if (!@this.edit) {
				$('#payment-btn').attr('disabled', 'disabled')
			}
		})
	</script>
@endpush