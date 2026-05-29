@extends('_layouts.app')

@section('title', 'Pengguna')

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
			<h2 class="h6 font-weight-bold mb-0 card-title">Data Pengguna</h2>
			<a href="{{ route('user.create') }}" class="btn btn-primary btn-sm">Tambah Data</a>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered" width="100%">
					<thead>
						<tr>
							<th>No</th>
							<th>Username</th>
							<th>Nama</th>
							<th>Alamat</th>
							<th>Telepon</th>
							<th>Role</th>
							<th>Action</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>

	<div class="modal">
	<div class="modal-dialog">
	<div class="modal-content">
	<form action="" method="post">
		@csrf
		@method('PUT')
		<div class="modal-header">
			<h5 class="modal-title">Edit Data</h5>
			<button class="close" data-dismiss="modal">&times;</button>
		</div>
		<div class="modal-body">
			<input type="hidden" name="id">
			<div class="form-group">
				<label>Nama</label>
				<input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" placeholder="Nama" value="{{ old('nama') }}" autofocus>

				<span class="invalid-feedback"></span>
			</div>
			<div class="form-group">
				<label>Username</label>
				<input type="text" class="form-control @error('username') is-invalid @enderror" name="username" placeholder="Username" value="{{ old('username') }}">

				<span class="invalid-feedback"></span>
			</div>
			<div class="form-group">
				<label>Hak Akses</label>
				<select class="form-control custom-select @error('hakAkses') is-invalid @enderror" name="hakAkses">
					<option value="1">Admin</option>
					<option value="2">Kasir</option>
					<option value="3">Gudang</option>
				</select>

				<span class="invalid-feedback"></span>
			</div>
			<div class="form-group">
				<label>Telepon</label>
				<input type="number" class="form-control @error('telepon') is-invalid @enderror" name="telepon" placeholder="Telepon" value="{{ old('telepon') }}" autofocus>

				<span class="invalid-feedback"></span>
			</div>
			<div class="form-group">
				<label>Alamat</label>
				<textarea class="form-control @error('alamat') is-invalid @enderror" name="alamat" placeholder="Alamat" value="{{ old('alamat') }}"></textarea>

				<span class="invalid-feedback"></span>
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

@endsection

@push('css')
	
	<link rel="stylesheet" href="{{ asset('sufee-admin/vendors/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">

	<style>
		table img{
			width: 100%;
			max-height: 100px;
			object-fit: cover;
		}
	</style>

@endpush

@push('js')
	
	<script src="{{ asset('sufee-admin/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('sufee-admin/vendors/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
	
	<script>
		const ajaxUrl = '{{ route('user.datatables') }}'
		const updateUrl = '{{ route('user.update', ':id') }}'
		const deleteUrl = '{{ route('user.destroy', ':id') }}'
		const csrf = '{{ csrf_token() }}'
	</script>

	<script src="{{ asset('js/user.js') }}"></script>

@endpush