@extends('_layouts.app')

@section('title', 'Stok Baru')

@section('content')
    
    
    <div class="row">
        <div class="col-sm-4">
            <livewire:stock.form />
        </div>
        <div class="col-sm-8">
            <livewire:stock.data :id="request()->id" />
        </div>
    </div>

    <livewire:stock.payment :id="request()->id" />

@endsection