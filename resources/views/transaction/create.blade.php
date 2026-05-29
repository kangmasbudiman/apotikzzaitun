@extends('_layouts.app')

@section('title', 'Tambah Data Transaksi')

@section('content')
    
        
    <livewire:transaction.create />
    
    <livewire:transaction.data />
    
    <livewire:transaction.payment />

    <livewire:transaction.cetak />
    
    @push('js')
        <script>

            jQuery(function ($) {
                $('[name=money]').on('keyup', function() {
                    const number = Number(this.value.replace(/\D/g, ''))
                    const price = new Intl.NumberFormat().format(number)
                    
                    this.value = price
                }),
             
            

            }),
         



        </script>
    @endpush

@endsection