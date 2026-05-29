@extends('_layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
	
	<div class="row justify-content-center">
		<div class="col-sm-6">
			<div class="card">
			<form action="{{ route('user.store') }}" method="post" enctype="multipart/form-data">
				@csrf
				<div class="card-header">
					<h2 class="h6 font-weight-bold mb-0 card-title">Tambah Pengguna</h2>
				</div>
				<div class="card-body">
					<div class="form-group">
						<label>Nama</label>
						<input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" placeholder="Nama" value="{{ old('nama') }}" autofocus>

						@error('nama')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
					<div class="form-group">
						<label>Username</label>
						<input type="text" class="form-control @error('username') is-invalid @enderror" name="username" placeholder="Username" value="{{ old('username') }}">

						@error('username')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
					<div class="form-group">
						<label>Hak Akses</label>
						<select class="form-control custom-select @error('hakAkses') is-invalid @enderror" name="hakAkses">
							<option value="1">Admin</option>
							<option value="2">Kasir</option>
							<option value="3">Gudang</option>
						</select>

						@error('hakAkses')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
					<div class="form-group">
						<label>Telepon</label>
						<input type="number" class="form-control @error('telepon') is-invalid @enderror" name="telepon" placeholder="Telepon" value="{{ old('telepon') }}" autofocus>

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
				<div class="card-footer">
					<button class="btn btn-primary" type="submit">Tambah</button>
					<a class="btn btn-secondary" href="{{ route('user.index') }}">Kembali</a>
				</div>
			</form>
			</div>
		</div>
	</div>

@endsection