@extends('_layouts.app')

@section('title', 'Stok Opname Obat')

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
      <h2 class="h6 card-title font-weight-bold mb-0">Data Stok Opname Obat</h2>
      <div>
        <button class="btn btn-sm btn-warning" data-toggle="collapse" data-target="#filter">Filter</button>
        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#import">Import Excel</button>
        <a class="btn btn-success btn-sm" href="{{ route('opname.export') }}">Export Excel</a>
        <a href="{{ route('opname.create') }}" class="btn btn-sm btn-primary">Tambah Data</a>
      </div>
    </div>
    <div class="card-body border-bottom collapse form-row pb-4" id="filter">
      <div class="col-sm-3">
        <label>Dari</label>
        <input type="date" name="dari" placeholder="Tanggal" class="form-control">
      </div>
      <div class="col-sm-3">
        <label>Sampai</label>
        <input type="date" name="sampai" placeholder="Tanggal" class="form-control">
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
              <th>Id Obat</th>
              <th>Tanggal</th>
              <th>Nama Obat</th>
              <th>Stok Sistem</th>
              <th>Stok Nyata</th>
              <th>Selisih</th>
              <th>Total</th>
              <th>Keterangan</th>
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
  <form action="{{ route('opname.import') }}" method="post" enctype="multipart/form-data">
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
    const ajaxUrl = '{{ route('opname.datatables') }}'
    const deleteUrl = '{{ route('opname.destroy', ':id') }}'
    const printUrl = '{{ route('opname.print') }}'
    const csrf = '{{ csrf_token() }}'
  </script>

  <script src="{{ asset('js/opname.js') }}"></script>

@endpush