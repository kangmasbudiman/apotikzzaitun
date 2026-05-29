<table>
    <thead>
        <tr>
            <th>ID Pajak</th>
            <th>ID Penjualan</th>
            <th>ID User</th>
            <th>Jenis</th>
            <th>Nominal</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $ppn)
            <tr>
                <td>{{ $ppn->idPajak }}</td>
                <td>{{ $ppn->idPenjualan }}</td>
                <td>{{ $ppn->idUser }}</td>
                <td>{{ $ppn->jenis }}</td>
                <td>{{ $ppn->nominal }}</td>
                <td>{{ $ppn->tanggal }}</td>
                <td>{{ $ppn->keterangan }}</td>
            </tr>
        @endforeach
    </tbody>
</table>