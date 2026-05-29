<table>
    <thead>
        <tr>
            <th>ID Pengeluaran</th>
            <th>ID Kategori Pengeluaran</th>
            <th>ID Pembelian</th>
            <th>ID Opname</th>
            <th>ID Pajak</th>
            <th>ID User</th>
            <th>Nama User</th>
            <th>Tanggal</th>
            <th>Pengeluaran</th>
            <th>Kategori</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($expends as $expend)
            <tr>
                <td>{{ $expend->idPengeluaran }}</td>
                <td>{{ $expend->idKategoriPengeluaran }}</td>
                <td>{{ $expend->idPembelian }}</td>
                <td>{{ $expend->idOpname }}</td>
                <td>{{ $expend->idPajak }}</td>
                <td>{{ $expend->idUser }}</td>
                <td>{{ $expend->namaUser }}</td>
                <td>{{ $expend->tanggal }}</td>
                <td>{{ $expend->pengeluaran ?? '' }}</td>
                <td>{{ $expend->namaKategori }}</td>
                <td>{{ $expend->keterangan }}</td>
            </tr>
        @endforeach
    </tbody>
</table>