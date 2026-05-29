<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center py-2">
		<h2 class="card-title h6 mb-0 font-weight-bold">Data Transaksi</h2>
		<div>
			<a href="{{ route('transaction.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
			<button class="btn btn-danger btn-sm" {{ count($transactions) ? '' : 'disabled' }} wire:click="clear">Reset</button>
			<button class="btn btn-sm btn-primary" {{ count($transactions) ? '' : 'disabled' }} id="payment-btn" wire:click="$emit('open-payment')">Bayar</button>
		</div>
	</div>
	<div class="card-body">
		@if(session()->has('stock-error'))
			<div class="alert alert-danger alert-dismissible">
				{{ session('stock-error') }}
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
						<th width="105px">Harga</th>
						<th width="150px">Qty</th>
						<th width="120px"> Discount</th>
						<th width="120px">Total</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody class="transaction-table">
				@forelse ($transactions as $transaction)
					@php
						$disc = $transaction['total'] * ($transaction['hargaJual'] * $transaction['disc'] / 100);
						$total = $transaction['total'] * $transaction['hargaJual'] - round($disc);
					@endphp
					<tr>
						<td>{{ $loop->iteration }}</td>
						<td>{{ $transaction['stuff']['barcode'] }}</td>
						<td>{{ $transaction['obat'] }}</td>
						<td>Rp {{ number_format($transaction['hargaJual']) }}</td>
						<td>
							<div class="input-group">
								<div class="input-group-prepend">
									<button class="btn btn-outline-secondary" wire:click="decrement('{{ $transaction['id'] }}')">-</button>
								</div>
								<input type="text" class="form-control qty" data-action="update" data-id="{{ $transaction['id'] }}" data-total="{{ $transaction['total'] }}" data-stock="{{ $transaction['stuff']['stock'] }}" value="{{ $transaction['total'] }}">
								<div class="input-group-append">
									<button class="btn btn-outline-secondary" wire:click="increment('{{ $transaction['id'] }}')">+</button>
								</div>

								<span class="invalid-feedback"></span>
							</div>
						</td>
						<td align="right">Rp {{ number_format($disc) }}</td>
						<td align="right">Rp {{ number_format($total) }}</td>
						<td><button class="btn btn-sm btn-danger" wire:click="delete('{{ $transaction['id'] }}')"><i class="fa fa-trash"></i></button></td>
					</tr>
				@empty
					<tr>
						<td colspan="8" align="center">Kosong</td>
					</tr>
				@endforelse
				</tbody>
				@if ($transactions)
					<tfoot>
						<tr class="bg-success text-white">
							<td style="border: none;" align="center" colspan="6"><strong>Subtotal</strong></td>
							<td style="border: none;" align="right">Rp {{ number_format($subtotal) }}</td>
							<td style="border: none;"></td>
						</tr>
					</tfoot>
				@endif
			</table>
		</div>
	</div>
</div>

@push('js')
<script>
	$(function () {
		$('.transaction-table').on('keyup', '.qty', function () {
			const number = this.value.replace(/\D/gi, '')
			const { stock, id } = $(this).data()

			if (number) {
				if (number > stock) {
					$(this).addClass('is-invalid')
					$(this).siblings('.invalid-feedback').html('Stok melampau batas')
				} else if (number <= 0) {
					$(this).addClass('is-invalid')
					$(this).siblings('.invalid-feedback').html('Stok terlalu sedikit')
				} else {
					Livewire.emit('update-stock', id, number)

					this.value = number
				}
			}

		})

		$('.transaction-table').on('focusout', '.qty', function () {
			if ($(this).hasClass('is-invalid')) {
				this.value = $(this).data('total')
			}

			if (!this.value) {
				this.value = $(this).data('total')
			}

			$(this).removeClass('is-invalid')
		})
	})
</script>
@endpush