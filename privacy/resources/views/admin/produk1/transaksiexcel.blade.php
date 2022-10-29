<table>
    <thead>
        <tr style="text-align: center;">
            <th colspan="9">Laporan Transaksi {{ $nama_produk }} TEST</th>
        </tr>
        <tr>
            <th width="100">Tanggal Transaksi</th>
            <th width="90">No. Transaksi</th>
            <th width="90">Kode Produk</th>
            <th width="90">Nama Produk</th>
            <th width="90">Qty Produk</th>
            <th width="90">Satuan</th>
            @permission('read-hpp')
            <th width="90">Harga Transaksi</th>
            <th width="90">Total Transaksi</th>
            @endpermission
            <th width="90">Kode Lokasi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
            <tr>
                <td>{{$item->tanggal_transaksi}}</td>
                <td>{{$item->no_transaksi}}</td>
                <td>{{$item->kode_produk}}</td>
                <td>{{$item->produk->nama_produk}}</td>
                <td>{{$item->qty_transaksi}}</td>
                <td>{{$item->produk->kode_satuan}}</td>
                @permission('read-hpp')
                <td>{{number_format($item->harga_transaksi, '0', '.', ',')}}</td>
                <td>{{number_format($item->total_transaksi, '0', '.', ',')}}</td>
                @endpermission
                <td>{{$item->kode_lokasi}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
