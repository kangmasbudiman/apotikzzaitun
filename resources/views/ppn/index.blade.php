@extends('_layouts.app')

@section('title', 'Data PPN')

@section('content')
  
  @if (session('success'))
    <div class="alert alert-success alert-dismissible">
      {{ session('success') }}
      <button class="close" data-dismiss="alert">&times;</button>
    </div>
  @endif
  
  <div class="card">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between">
        <h2>Perolehan PPN</h2>
        <h2 id="perolehan-ppn">Rp {{ $ppn }}</h2>
      </div>
    </div>
  </div>

  <div id="alert"></div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <button data-toggle="modal" data-target="#create" class="btn btn-primary btn-sm">Setor Pajak PPN</button>

      <div>
        <button class="btn btn-sm btn-warning" data-toggle="collapse" data-target="#filter">Filter</button>
        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#import">Import Excel</button>
        <a class="btn btn-success btn-sm" href="{{ route('ppn.export') }}">Export Excel</a>
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
      <div class="col-sm-3">
        <label>Jenis</label>
            @php
              $jenis = ['PPN Dikeluarkan', 'PPN Disetorkan'];
            @endphp
          <select class="form-control" style="width: 100%;" name="jenis">
              <option value="" selected="">Jenis</option>
              @foreach ($jenis as $item)
                <option value="{{ $item }}">{{ $item }}</option>
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
        <table id="data" class="table table-bordered" width="100%">
          <thead>
            <tr>
              <th>Kode Pajak</th>
              <th>Jenis</th>
              <th>Nominal</th>
              <th>Tanggal</th>
              <th>Keterangan</th>
              <th>User</th>
              <th>Action</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>

  <div class="modal" id="create">
  <div class="modal-dialog">
  <div class="modal-content">
  <form action="{{ route('ppn.store') }}" method="post">
      @csrf
      <div class="modal-header">
          <h5 class="modal-title">Setor Pajak PPN</h5>
          <button class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
          <div class="form-group">
              <label>Jenis</label>
              <input type="text" class="form-control" name="jenis" placeholder="Jenis" value="PPN Disetorkan" readonly>

              <span class="invalid-feedback"></span>
          </div>
          <div class="form-group">
              <label>Nominal</label>
              <input type="text" class="form-control" name="nominal" placeholder="Nominal">

              <span class="invalid-feedback"></span>
          </div>
          <div class="form-group">
              <label>Keterangan</label>
              <textarea class="form-control" name="keterangan" placeholder="Keterangan"></textarea>

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
  <form action="{{ route('ppn.import') }}" method="post" enctype="multipart/form-data">
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
    const ajaxUrl = '{{ route('ppn.datatables') }}'
    const getPPNUrl = '{{ route('ppn.count') }}'
    const deleteUrl = '{{ route('ppn.destroy', ':id') }}'
    const printUrl = '{{ route('ppn.print') }}'
    const csrf = '{{ csrf_token() }}'
  </script>

  <script src="{{ asset('js/ppn.js') }}"></script>

@endpush