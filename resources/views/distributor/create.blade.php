@extends('_layouts.app')

@section('title', 'Tambah Distributor')

@section('content')
	
	<div class="row justify-content-center">
		<div class="col-sm-6">
			<div class="card">
			<form action="{{ route('distributor.store') }}" method="post" enctype="multipart/form-data">
				@csrf
				<div class="card-header">
					<h2 class="h6 font-weight-bold mb-0 card-title">Tambah Distributor</h2>
				</div>
				<div class="card-body">
					<div class="form-group">
						<label>Nama</label>
						<input type="text" class="form-control @error('namaDist') is-invalid @enderror" name="namaDist" placeholder="Nama" value="{{ old('namaDist') }}" autofocus>

						@error('namaDist')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
					<div class="form-group">
						<label>Telepon</label>
						<input type="number" class="form-control @error('telepon') is-invalid @enderror" name="telepon" placeholder="Telepon" value="{{ old('telepon') }}">

						@error('telepon')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
					<div class="form-group">
						<label>Alamat</label>
						<textarea class="form-control @error('alamat') is-invalid @enderror" name="alamat" placeholder="Alamat" value="{{ old('alamat') }}"></textarea>

						@error('alamat')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
				</div>
				<div class="card-footer">
					
					<a class="btn btn-secondary" href="{{ route('distributor.index') }}">Kembali</a>
					<button class="btn btn-primary" type="submit">Simpan</button>
				</div>
			</form>
			</div>
		</div>
	</div>

@endsection