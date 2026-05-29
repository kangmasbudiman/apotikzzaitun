@extends('_layouts.app')

@section('title', 'Tambah Kas Harian')

@section('content')
	
	<div class="row justify-content-center">
		<div class="col-sm-6">
			<div class="card">
			<form action="{{ route('daylicash.store') }}" method="post" enctype="multipart/form-data">
				@csrf
				<div class="card-header">
					<h2 class="h6 font-weight-bold mb-0 card-title">Tambah Kas Harian</h2>
				</div>
				<div class="card-body">
					<div class="form-group">
						<label>Tanggal</label>
						<input type="date" class="form-control @error('tanggal') is-invalid @enderror" name="tanggal" placeholder="Tanggal" value="{{ old('tanggal') }}" autofocus>

						@error('tanggal')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
					<div class="form-group">
						<label>Total</label>
						<input type="number" class="form-control @error('total') is-invalid @enderror" name="total" placeholder="Total Kas" value="{{ old('total') }}">

						@error('total')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
		
				</div>
				<div class="card-footer">
					
					<a class="btn btn-secondary" href="{{ route('daylicash.index') }}">Kembali</a>
					<button class="btn btn-primary" type="submit">Simpan</button>
				</div>
			</form>
			</div>
		</div>
	</div>

@endsection