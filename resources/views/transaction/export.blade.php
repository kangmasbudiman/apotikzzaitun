<table>
    <thead>
        <tr>
            <th>No Faktur</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>PPN</th>
            <th>Total</th>
            <th>ID Kasir</th>
            <th>Kasir</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($success as $transaction)
            <tr>
                <td>{{ $transaction->idPenjualan }}</td>
                <td>{{ $transaction->tanggal }}</td>
                <td>{{ $transaction->status ? 'berhasil' : 'batal' }}</td>
                <td>{{ $transaction->ppn }}</td>
                <td>{{ $transaction->total }}</td>
                <td>{{ $transaction->idUser }}</td>
                <td>{{ $transaction->namaUser }}</td>
            </tr>
        @endforeach
        <tr></tr>
        @foreach ($cancel as $transaction)
            <tr>
                <td>{{ $transaction->idPenjualan }}</td>
                <td>{{ $transaction->tanggal }}</td>
                <td>{{ $transaction->status ? 'berhasil' : 'batal' }}</td>
                <td>{{ $transaction->ppn }}</td>
                <td>{{ $transaction->total }}</td>
                <td>{{ $transaction->idUser }}</td>
                <td>{{ $transaction->namaUser }}</td>
            </tr>
        @endforeach
    </tbody>
</table>