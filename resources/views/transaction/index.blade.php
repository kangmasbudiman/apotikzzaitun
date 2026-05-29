@extends('_layouts.app')

@section('title', 'Transaksi')

@section('content')
	
	@if (session('success'))
		<div class="alert alert-success alert-dismissible">
			{{ session('success') }}
			<button class="close" data-dismiss="alert">&times;</button>
		</div>
	@endif

	<div id="alert"></div>
	<div class="card">
		<div class="card-header d-flex align-items-center justify-content-between">
			<h2 class="h6 font-weight-bold mb-0 card-title">Data Transaksi</h2>
			<div>
				@can('isAdmin')
					<button class="btn btn-sm btn-warning" data-toggle="collapse" data-target="#filter">Filter</button>
					<button class="btn btn-success btn-sm" data-toggle="modal" data-target="#import">Import Excel</button>
	                <a class="btn btn-success btn-sm" href="{{ route('transaction.export') }}">Export Excel</a>
				@endcan
				<a href="{{ route('transaction.create') }}" class="btn btn-sm btn-primary">Tambah Data</a>
			</div>
		</div>
		<div class="card-body border-bottom collapse pb-4" id="filter">
			<div class="form-row mb-3">
				<div class="col-sm-3">
					<label>Dari</label>
					<input type="date" name="dari" placeholder="Tanggal" class="form-control">
				</div>
				<div class="col-sm-3">
					<label>Sampai</label>
					<input type="date" name="sampai" placeholder="Tanggal" class="form-control">
				</div>
				<div class="col-sm-3">
					<label>Status</label>
				    <select class="form-control" name="status">
				        <option selected disabled>Status</option>
			            <option value="1">Berhasil</option>
			            <option value="0">Batal</option>
				    </select>
				</div>
				<div class="col-sm-3">
					<label>Kasir</label>
				    <select class="form-control" name="user">
				        <option selected disabled>Kasir</option>
				        @foreach ($user as $item)
				            <option value="{{ $item->idUser }}">{{ $item->nama }}</option>
				        @endforeach
				    </select>
				</div>
			</div>
			<button class="btn btn-primary rounded filter mr-1">Filter</button>
			<button class="btn btn-success rounded print">Print</button>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered" width="100%">
					<thead>
						<tr>
							<th>No</th>
							<th>IdTransaksi</th>
							<th>Tanggal</th>
							<th>Status</th>
							<th>Total</th>
							<th>Action</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>

    <div class="modal" id="import">
    <div class="modal-dialog">
    <div class="modal-content">
    <form action="{{ route('transaction.import') }}" method="post" enctype="multipart/form-data">
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

@endpush

@push('js')
	
	<script src="{{ asset('sufee-admin/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('sufee-admin/vendors/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('sufee-admin/vendors/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>

	<script>
		const ajaxUrl = '{{ route('transaction.datatables') }}'
		const deleteUrl = '{{ route('transaction.destroy', ':id') }}'
		const cancelUrl = '{{ route('transaction.cancel', ':id') }}'
		const printUrl = '{{ route('transaction.printall') }}'
		const csrf = '{{ csrf_token() }}'
	</script>

	<script src="{{ asset('js/report.js') }}"></script>

@endpush