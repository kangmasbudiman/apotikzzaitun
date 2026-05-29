<table>
    <thead>
        <tr>
            <th>ID Opname</th>
            <th>ID Buku</th>
            <th>Tanggal</th>
            <th>Nama Buku</th>
            <th>Harga Pokok</th>
            <th>Stok Sistem</th>
            <th>Stok Nyata</th>
            <th>Selisih</th>
            <th>Total</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $opname)
            <tr>
                <td>{{ $opname->idOpname }}</td>
                <td>{{ $opname->idBuku }}</td>
                <td>{{ $opname->tanggal }}</td>
                <td>{{ $opname->judul }}</td>
                <td>{{ $opname->hargaPokok }}</td>
                <td>{{ $opname->stokSistem }}</td>
                <td>{{ $opname->stokNyata }}</td>
                <td>{{ 0 - $opname->selisih }}</td>
                <td>{{ 0 - $opname->total }}</td>
                <td>{{ $opname->keterangan ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>