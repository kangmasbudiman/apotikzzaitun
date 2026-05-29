@extends('_layouts.app')

@section('title', 'Kas Harian Baru')

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
            <h2 class="h6 font-weight-bold mb-0 card-title">Data Kas Harian</h2>
            <div>
                <button class="btn btn-sm btn-warning" data-toggle="collapse" data-target="#filter">Filter</button>
                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#import">Import Excel</button>
                <a class="btn btn-success btn-sm" href="{{ route('cash.export') }}">Export Excel</a>
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create">Tambah Data</button>
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
                <label>Kategori</label>
                <select class="form-control" style="width: 100%;" name="category">
                    <option value="" selected="">Kategori</option>
                    @foreach ($categories as $item)
                        <option value="{{ $item->namaKategori }}">{{ $item->namaKategori }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3 d-flex align-items-end">
                <button class="btn btn-primary rounded filter mr-1">Filter</button>
                <button class="btn btn-primary rounded btn-warning print">Print</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%">
                    <thead>
                        <tr>
                            <th>ID Kas</th>
                            <th>Tanggal</th>
                            <th>Kas Harian</th>
                            
                            
                            <th>Keterangan</th>
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
    <form action="{{ route('cash.store') }}" method="post">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title">Tambah Pengeluaran</h5>
            <button class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
            <input type="hidden" class="form-control" name="namaUser" autofocus>
                <label>Kas Harian</label>
                <input type="text" class="form-control nominal" name="pengeluaran" placeholder="Pengeluaran" autofocus>

                <span class="invalid-feedback"></span>
            </div>

     
        

            <div class="form-group user-select">
                <label>Nama Karyawan</label>
                <select style="width: 100%;" class="form-control custom-select" name="idUser" placeholder="Nama Karyawan"></select>

                <span class="invalid-feedback"></span>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea class="form-control" name="keterangan" placeholder="Keterangan"></textarea>

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

    <div class="modal" id="edit">
    <div class="modal-dialog">
    <div class="modal-content">
    <form action="" method="post">
        @csrf
        @method('PUT')
        <input type="hidden" class="form-control" name="namaUser" autofocus>
        <div class="modal-header">
            <h5 class="modal-title">Edit Data</h5>
            <button class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Pengeluaran</label>
                <input type="text" class="form-control nominal" name="pengeluaran" placeholder="Pengeluaran" autofocus>

                <span class="invalid-feedback"></span>
            </div>
       
    
            <div class="form-group">
                <label>Keterangan</label>
                <textarea class="form-control" name="keterangan" placeholder="Keterangan"></textarea>

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

    <div class="modal" id="import">
    <div class="modal-dialog">
    <div class="modal-content">
    <form action="{{ route('finance.import') }}" method="post" enctype="multipart/form-data">
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
    <script src="{{ asset('sufee-admin/vendors/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('sufee-admin/vendors/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    
    <script>

        const ajaxUrl = '{{ route('cash.datatables') }}'
        const updateUrl = '{{ route('cash.update', ':id') }}'
        const deleteUrl = '{{ route('cash.destroy', ':id') }}'
        const printUrl = '{{ route('cash.printall') }}'
        const categoryUrl = '{{ route('category_finance.select') }}'
        const userUrl = '{{ route('user.select') }}'
        const csrf = '{{ csrf_token() }}'
    </script>

    <script src="{{ asset('js/cashh.js') }}"></script>

@endpush