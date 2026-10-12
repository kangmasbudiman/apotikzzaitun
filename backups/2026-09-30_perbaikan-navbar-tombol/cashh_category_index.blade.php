@extends('_layouts.app')

@section('title', 'Kategori Pengeluaran')

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
            <h2 class="h6 font-weight-bold mb-0 card-title">Data Kategori Pengeluaran</h2>
            <div>
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create">Tambah Data</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
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
    <form action="{{ route('category_finance.store') }}" method="post">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title">Tambah Kategori</h5>
            <button class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Nama Kategori</label>
                <input type="text" class="form-control" name="nama" placeholder="Nama Kategori" autofocus>

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
            <div class="form-group">
                <label>Nama Kategori</label>
                <input type="text" class="form-control" name="nama" placeholder="Nama Kategori" autofocus>

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

        const ajaxUrl = '{{ route('category_finance.datatables') }}'
        const updateUrl = '{{ route('category_finance.update', ':id') }}'
        const deleteUrl = '{{ route('category_finance.destroy', ':id') }}'
        const csrf = '{{ csrf_token() }}'
    </script>

    <script src="{{ asset('js/category_finance.js') }}"></script>

@endpush