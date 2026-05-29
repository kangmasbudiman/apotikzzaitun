<table>
    <thead>
        <tr>
            <th>ID Pembelian</th>
            <th>Tanggal</th>
            <th>Total</th>
            <th>ID User</th>
            <th>ID Distributor</th>
            <th>Distributor</th>
            <th>User</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($stocks as $stock)
            <tr>
                <td>{{ $stock->idPembelian }}</td>
                <td>{{ $stock->tanggal }}</td>
                <td>{{ $stock->total }}</td>
                <td>{{ $stock->idUser }}</td>
                <td>{{ $stock->idDist }}</td>
                <td>{{ $stock->namaDist }}</td>
                <td>{{ $stock->namaUser }}</td>
            </tr>
        @endforeach
    </tbody>
</table>