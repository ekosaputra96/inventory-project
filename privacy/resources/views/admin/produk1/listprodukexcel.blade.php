<table>
    <thead>
        <tr style="text-align: center;">
            <th colspan="9">List Produk {{$nama_company}}</th>
        </tr>
        <tr>
            <th>Kode Produk</th>
            <th>Nama Produk</th>
            <th>Part Number</th>
            <th>Kode Kategori</th>
            <th>Kode Satuan</th>
            <th>Tipe Produk</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
            <tr>
                <td>{{$item->id}}</td>
                <td>{{$item->nama_produk}}</td>
                <td>{{$item->partnumber}}</td>
                <td>{{$item->kode_kategori}}</td>
                <td>{{$item->kode_satuan}}</td>
                <td>{{$item->tipe_produk}}</td>
                <td>{{$item->stat}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
