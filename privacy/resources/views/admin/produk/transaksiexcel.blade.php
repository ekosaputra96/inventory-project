<!DOCTYPE html>
<html lang="en">
<head>
    <style> 
        
     @page {
            border: solid 1px #0b93d5;

        }

        .title {
            margin-top: 0.5cm;
        }
        .title h1 {
            text-align: left;
            font-size: 14pt;
            
        }
        

        .header {
            margin-left: 50px;
            margin-right: 0px;
            /*font-size: 10pt;*/
            padding-top: 10px;
            /*border: solid 1px #0b93d5;*/
        }

        .left {
            float: left;
        }

        .right {
            float: right;
        }

        .clearfix {
            overflow: auto;
        }

        .content {
                margin-left: 10px;
            padding-top: 10px
        }
        .catatan {
            font-size: 10pt;
        }

        footer {
                position: fixed; 
                top: 19cm; 
                left: 0cm; 
                right: 0cm;
                height: 2cm;
            }

        /* Table desain*/
        table.grid {
            width: 100%;
        }
</style>
</head>
<body>

    <table rules="rows" class="grid" style="font-size: 10pt; vertical-align: top; width: 27cm" border="1">
             <thead>
        <tr>
            <th>Tanggal Transaksi</th>
            <th>No Transaksi</th>
            <th>Kode Produk</th>
            <th>Nama Produk</th>
            <th>Qty Transaksi</th>
            <th>Satuan</th>
            @permission('read-hpp')
            <th>Harga Transaksi</th>
            <th>Total Transaksi</th>
            @endpermission
            <th>Kode Lokasi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $item)
            <tr>
                <td>{{ $item->tanggal_transaksi }}</td>
                <td>{{ $item->no_transaksi }}</td>
                <td>{{ $item->kode_produk }}</td>
                <td>{{ $item->produk->nama_produk }}</td>
                <td>{{ $item->qty_transaksi }}</td>
                <td>{{ $item->produk->kode_satuan }}</td>
                @permission('read-hpp')
                <td>{{ number_format($item->harga_transaksi,'0','.',',') }}</td>
                <td>{{ number_format($item->total_transaksi,'0','.',',') }}</td>
                @endpermission
                <td>{{ $item->kode_lokasi }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
<hr>
</body>
</html>