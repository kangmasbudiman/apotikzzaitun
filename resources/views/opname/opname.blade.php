<table>
    <thead>
        <tr>
            <th>ID Buku</th>
            <th>Judul</th>
            <th>Tanggal</th>
            <th>Stok Sistem</th>
            <th>Stok Nyata</th>
            <th>Harga Pokok</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $opname)
            <tr>
                <td>{{ $opname->idBuku }}</td>
                <td>{{ $opname->judul }}</td>
                <td>{{ $opname->tanggal }}</td>
                <td>{{ $opname->stokSistem }}</td>
                <td>{{ $opname->stokNyata }}</td>
                <td>{{ $opname->hargaPokok }}</td>
                <td>{{ $opname->keterangan }}</td>
            </tr>
        @endforeach
    </tbody>
</table>