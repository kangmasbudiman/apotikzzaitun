<table>
    <thead>
        <tr>
            <th>ID Pembelian</th>
            <th>ID Buku</th>
            <th>Tanggal</th>
            <th>Distributor</th>
            <th>Judul</th>
            <th>Harga Pokok</th>
            <th>Jumlah</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($stocks as $stock)
            <tr>
                <td>{{ $stock->idPembelian }}</td>
                <td>{{ $stock->idBuku }}</td>
                <td>{{ $stock->stock->tanggal }}</td>
                <td>{{ $stock->stock->namaDist }}</td>
                <td>{{ $stock->judul }}</td>
                <td>{{ $stock->hargaPokok }}</td>
                <td>{{ $stock->jumlah }}</td>
                <td>{{ $stock->hargaPokok * $stock->jumlah }}</td>
            </tr>
        @endforeach
    </tbody>
</table>