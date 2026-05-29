<div class="modal fade" id="stuff">
<div class="modal-dialog modal-xl modal-lg">
<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Pilih Obat</h5>

        <button class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Obat</th>
                        <th>Barcode</th>
                        <th>Nama</th>
                        @if (isset($type))
                            <th>Harga Jual</th>
                        @else
                            <th>Harga Beli</th>
                        @endif
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
</div>
</div>

@push('css')
    
    <link rel="stylesheet" href="{{ asset('sufee-admin/vendors/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">

@endpush

@push('js')
    
    <script src="{{ asset('sufee-admin/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('sufee-admin/vendors/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

    <script>
        const ajaxUrl = '{{ route('stuff.datatables') }}'
    </script>

@endpush