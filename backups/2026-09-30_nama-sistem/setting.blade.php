@extends('_layouts.app')

@section('title', 'Pengaturan Aplikasi')

@section('content')
	
	<div class="row">
		<div class="col-sm-5 mx-auto">

			@if (session('success'))
				<div class="alert alert-success alert-dismissible">
					{{ session('success') }}
					<button class="close" data-dismiss="alert">&times;</button>
				</div>
			@endif
			
			<div class="card">
			<form action="{{ route('setting.update') }}" method="post" enctype="multipart/form-data">
				@method('PUT')
				@csrf
				<div class="card-header">
					<h2 class="h6 mb-0 font-weight-bold card-title">Pengaturan Aplikasi</h2>
				</div>
				<div class="card-body">
					<div class="form-group">
						<label>Nama</label>
						<input type="text" class="form-control @error('nama_toko') is-invalid @enderror" name="nama_toko" placeholder="Nama" value="{{ site('nama_toko') }}" autofocus>

						@error('nama_toko')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
					<div class="form-group">
						<label>No Telepon</label>
						<input type="text" class="form-control @error('telepon_toko') is-invalid @enderror" name="telepon_toko" placeholder="No Telepon" value="{{ site('telepon_toko') }}" autofocus>

						@error('telepon_toko')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
					<div class="form-group">
						<label>PPN</label>
						<input type="text" class="form-control @error('ppn') is-invalid @enderror" name="ppn" value="{{ site('ppn') }}" placeholder="PPN">

						@error('ppn')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
					<div class="form-group">
						<label>Minimal Notifikasi Stok</label>
						<input type="number" class="form-control @error('min_stok') is-invalid @enderror" name="min_stok" value="{{ site('min_stok') }}" placeholder="Minimal Notifikasi Stok">

						@error('min_stok')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
					<div class="form-group @error('logo') is-invalid @enderror">
					    <label>Logo (Kosongkan jika tidak diubah)</label>
					    <div class="custom-file">
					        <label class="custom-file-label">Browse</label>
					        <input type="file" class="custom-file-input" name="logo">
					    </div>

					    <small class="form-text text-muted">Logo saat ini : {{ site('logo') }}</small>

						@error('logo')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
					<div class="form-group">
						<div class="custom-control custom-switch">
							<input type="checkbox" class="custom-control-input" id="using-logo" name="pakaiLogo" {{ site('pakaiLogo') ? 'checked' : '' }}>
							<label for="using-logo" class="custom-control-label">Menggunakan Logo</label>
						</div>
					</div>
					<div class="form-group">
						<label>Alamat</label>
						<textarea rows="3" class="form-control @error('alamat_toko') is-invalid @enderror" name="alamat_toko" placeholder="Alamat">{{ site('alamat_toko') }}</textarea>

						@error('alamat_toko')
							<span class="invalid-feedback">{{ $message }}</span>
						@enderror
					</div>
				</div>
				<div class="card-footer">
					<button class="btn btn-primary" type="submit">Simpan</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('js')

    <script src="{{ asset('sufee-admin/vendors/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>

    <script>
    	bsCustomFileInput.init()
    </script>

@endpush