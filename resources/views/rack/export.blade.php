<table>
    <thead>
        <tr>
            <th>Nama</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($racks as $rack)
            <tr>
                <td>{{ $rack->nama_rak }}</td>
            </tr>
        @endforeach
    </tbody>
</table>