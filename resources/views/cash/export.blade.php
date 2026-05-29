<table>
    <thead>
        <tr>
            <th>Nama</th>
            <th>Alamat</th>
            <th>Telepon</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($distributors as $distributor)
            <tr>
                <td>{{ $distributor->namaDist }}</td>
                <td>{{ $distributor->alamat }}</td>
                <td>{{ $distributor->telepon }}</td>
            </tr>
        @endforeach
    </tbody>
</table>