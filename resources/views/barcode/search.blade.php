@extends('_layouts.app')

@section('title', 'Print Semua Barcode Obat')

@section('content')
  
  <div class="row">
    <div class="col-sm-4">
      <livewire:barcode.search /> 
    </div>
    <div class="col-sm-8">
      <livewire:barcode.item />
    </div>
  </div>

@endsection

@push('css')
  
  <link rel="stylesheet" href="{{ asset('sufee-admin/vendors/select2/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('sufee-admin/vendors/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

@endpush

@push('js')
  
  <script src="{{ asset('sufee-admin/vendors/select2/js/select2.min.js') }}"></script>

@endpush