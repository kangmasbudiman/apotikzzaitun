<table>
    <thead>
        <tr>
            <th>ID Penjualan</th>
            <th>ID Buku</th>
            <th>Tanggal</th>
            <th>Nama Obat</th>
            <th>Status</th>
            <th>Harga</th>
            <th>Jumlah</th>
            <th>Disc</th>
            <th>PPN</th>
            <th>Total</th>
            <th>Kasir</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($success as $transaction)
            <tr>
                <td>{{ $transaction->idPenjualan }}</td>
                <td>{{ $transaction->idBuku }}</td>
                <td>{{ $transaction->transaction->tanggal }}</td>
                <td>{{ $transaction->obat }}</td>
                <td>{{ $transaction->transaction->status ? 'berhasil' : 'batal' }}</td>
                <td>{{ $transaction->hargaJual }}</td>
                <td>{{ $transaction->jumlah }}</td>
                <td>{{ round($transaction->disc) }}</td>
                <td>{{ $transaction->ppn }}</td>
                <td>{{ $transaction->jumlah * $transaction->hargaJual - round($transaction->disc) + $transaction->ppn }}</td>
                <td>{{ $transaction->transaction->namaUser }}</td>
            </tr>
        @endforeach
        <tr></tr>
        @foreach ($cancel as $transaction)
            <tr>
                <td>{{ $transaction->idPenjualan }}</td>
                <td>{{ $transaction->idBuku }}</td>
                <td>{{ $transaction->transaction->tanggal }}</td>
                <td>{{ $transaction->judul }}</td>
                <td>{{ $transaction->transaction->status ? 'berhasil' : 'batal' }}</td>
                <td>{{ $transaction->hargaJual }}</td>
                <td>{{ $transaction->jumlah }}</td>
                <td>{{ round($transaction->disc) }}</td>
                <td>{{ $transaction->ppn }}</td>
                <td>{{ $transaction->jumlah * $transaction->hargaJual - round($transaction->disc) + $transaction->ppn }}</td>
                <td>{{ $transaction->transaction->namaUser }}</td>
            </tr>
        @endforeach
    </tbody>
</table>