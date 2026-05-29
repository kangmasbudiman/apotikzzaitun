@extends('_layouts.app')

@section('title', 'Distributor')

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
			<h2 class="h6 font-weight-bold mb-0 card-title">Data Distributor</h2>
			<div>
				@can('isAdmin')
					<button class="btn btn-success btn-sm" data-toggle="modal" data-target="#import">Import Excel</button>
					<a class="btn btn-warning btn-sm" href="{{ route('distributor.export') }}">Export Excel</a>
				@endcan
				<a href="{{ route('distributor.create') }}" class="btn btn-primary btn-sm">Tambah Data</a>
			</div>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered" width="100%">
					<thead>
						<tr>
							<th>No</th>
							<th>Nama</th>
							<th>Alamat</th>
							<th>Telepon</th>
							<th>Action</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>

	<div class="modal" id="edit">
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
				<input type="text" class="form-control @error('namaDist') is-invalid @enderror" name="namaDist" placeholder="Nama" value="{{ old('namaDist') }}" autofocus>

				<span class="invalid-feedback"></span>
			</div>
			<div class="form-group">
				<label>Telepon</label>
				<input type="number" class="form-control @error('telepon') is-invalid @enderror" name="telepon" placeholder="Telepon" value="{{ old('telepon') }}">

				<span class="invalid-feedback"></span>
			</div>
			<div class="form-group">
				<label>Alamat</label>
				<textarea class="form-control @error('alamat') is-invalid @enderror" name="alamat" placeholder="Alamat" value="{{ old('alamat') }}"></textarea>

				<span class="invalid-feedback"></span>
			</div>
		</div>
		<div class="modal-footer">
			
			<button class="btn btn-secondary" data-dismiss="modal">Batal</button>
			<button class="btn btn-primary" type="submit">Simpan</button>
		</div>
	</form>
	</div>
	</div>
	</div>

  <div class="modal" id="import">
    <div class="modal-dialog">
    <div class="modal-content">
    <form action="{{ route('distributor.import') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title">Import Excel</h5>
            <button class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>File Excel</label>
                <div class="custom-file">
                    <label class="custom-file-label">Browse</label>
                    <input type="file" class="custom-file-input" name="file">
                <span class="invalid-feedback"></span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
  
            <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
			<button class="btn btn-primary" type="submit">Simpan</button>
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
	<script src="{{ asset('sufee-admin/vendors/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
	
	<script>

		const ajaxUrl = '{{ route('distributor.datatables') }}'
		const updateUrl = '{{ route('distributor.update', ':id') }}'
		const deleteUrl = '{{ route('distributor.destroy', ':id') }}'
		const csrf = '{{ csrf_token() }}'
	</script>

	<script src="{{ asset('js/distributor.js') }}"></script>

@endpush