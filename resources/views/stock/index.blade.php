@extends('_layouts.app')

@section('title', 'Pembelian Obat')

@section('content')

	<div id="alert"></div>

	@if (session('success'))
		<div class="alert alert-success alert-dismissible">
			{{ session('success') }}
			<button class="close" data-dismiss="alert">&times;</button>
		</div>
	@endif

	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h2 class="h6 card-title font-weight-bold mb-0">Data Pembelian</h2>
			<div>
				@can('isAdmin')
					<button class="btn btn-sm btn-warning" data-toggle="collapse" data-target="#filtter">Filter</button>
	                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#import">Import Excel</button>
	                <a class="btn btn-success btn-sm" href="{{ route('stock.export') }}">Export Excel</a>
				@endcan
				<a href="{{ route('stock.create') }}" class="btn btn-sm btn-primary">Tambah Data</a>
			</div>
		</div>
		<div class="card-body border-bottom collapse form-row pb-4" id="filtter">
			<div class="col-sm-3">
				<label>Dari</label>
				<input type="date" name="dari" placeholder="Tanggal" class="form-control">
			</div>
			<div class="col-sm-3">
				<label>Sampai</label>
				<input type="date" name="sampai" placeholder="Tanggal" class="form-control">
			</div>
			<div class="col-sm-3">
				<label>Distributor</label>
			    <select class="form-control" style="width: 100%;" name="distributor">
			        <option value="" selected="">Distributor</option>
			        @foreach ($distributor as $item)
                        <option value="{{ $item->namaDist }}">{{ $item->namaDist }}</option>
                    @endforeach
			    </select>
			</div>
			<div class="col-sm-3 d-flex align-items-end">
				<button class="btn btn-primary rounded filter">Filter</button>
				<button class="btn btn-success ml-1 rounded print">Print</button>
			</div>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered" width="100%">
					<thead>
						<tr>
							<th>IdPembelian</th>
							<th>Tanggal</th>
							<th>Tanggal Jatuh Tempo</th>
							<th>Nama Dist</th>
							<th>No Faktur</th>
							<th>Total</th>
							
							<th>Aksi</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>

    <div class="modal" id="import">
    <div class="modal-dialog">
    <div class="modal-content">
    <form action="{{ route('stock.import') }}" method="post" enctype="multipart/form-data">
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
	<link rel="stylesheet" href="{{ asset('sufee-admin/vendors/select2/css/select2.min.css') }}">
	<link rel="stylesheet" href="{{ asset('sufee-admin/vendors/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

@endpush

@push('js')
	
	<script src="{{ asset('sufee-admin/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('sufee-admin/vendors/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('sufee-admin/vendors/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script src="{{ asset('sufee-admin/vendors/select2/js/select2.full.min.js') }}"></script>

	<script>
		const ajaxUrl = '{{ route('stock.datatables') }}'
		const deleteUrl = '{{ route('stock.destroy', ':id') }}'
		const printUrl = '{{ route('stock.printall') }}'
		const csrf = '{{ csrf_token() }}'
	</script>

	<script src="{{ asset('js/stock.js') }}"></script>

@endpush