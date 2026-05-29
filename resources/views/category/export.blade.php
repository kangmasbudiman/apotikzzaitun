<table>
    <thead>
        <tr>
            <th>Nama</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($categories as $category)
            <tr>
                <td>{{ $category->nama_kategori }}</td>
            </tr>
        @endforeach
    </tbody>
</table>