<table>
    <thead>
        <tr>
             <th>Barcode</th>
            <th>ISBN</th>
            <th>Nama Obat</th>
            <th>Suplier</th>
            <th>Harga Jual</th>
            <th>Stock</th>
            <th>Kategori</th>
            <th>Tahun</th>
            <th>PPN</th>
            <th>Rak</th>
            <th>Harga Pokok</th>
            <th>Disc</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($stuffs as $stuff)
            <tr>
                <td>{{ $stuff->barcode }}</td>
                <td>{{ $stuff->noisbn }}</td>
                <td>{{ $stuff->obat }}</td>
                <td>{{ $stuff->suplier }}</td>
                <td>{{ $stuff->hargaJual }}</td>
                <td>{{ $stuff->stock }}</td>
                <td>{{ $stuff->idKategori }}</td>
                <td>{{ $stuff->tahun }}</td>
                <td>{{ $stuff->ppn }}</td>
                <td>{{ $stuff->idRak }}</td>
                <td>{{ $stuff->hargaPokok }}</td>
                <td>{{ $stuff->disc }}%</td>
            </tr>
        @endforeach
    </tbody>
</table>