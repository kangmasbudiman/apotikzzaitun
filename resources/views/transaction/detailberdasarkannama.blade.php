@extends('_layouts.app')

@section('title', 'Search Riwayat Obat Berdasarkan Nama Pasien ')

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-sm-8">
					<h1 class="h3">Nama Pelanggan</h1>
					<form action="{{ route('detail_transaction.aksisearchnama') }}" method="post">
						@csrf
						<input type="text" class="form-control" name="namaobat"></input>
						<br>

						<button type="submit" class="btn btn-danger">Cari Riwayat</button>
					</form>
				</div>

				
			
			</div>
			
		</div>
		
	</div>
@endsection