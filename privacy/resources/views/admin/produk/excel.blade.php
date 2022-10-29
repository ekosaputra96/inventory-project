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
            <th>Kode Produk</th>
            <th>Nama Produk</th>
            <th>Part Number</th>
            <th>Kode Kategori</th>
            <th>Kode Satuan</th>
            <th>Stock</th>
            @permission('read-hpp')
            <th>Hpp</th>
            <th>Total</th>
            @endpermission
            <th>Kode Lokasi</th>
        </tr>
    </thead>

    <tbody>
        @foreach($data as $item)
            <tr>
                <td>{{ $item->kode_produk }}</td>
                <td>{{ $item->produk->nama_produk }}</td>
                <td>{{ "'".$item->partnumber }}</td>
                <td>{{ $item->produk->kode_kategori }}</td>
                <td>{{ $item->produk->kode_satuan }}</td>
                <td>{{ $item->ending_stock }}</td>
                @permission('read-hpp')
                <td>{{ $item->hpp }}</td>
                <td>{{ $item->hpp * $item->ending_stock }}</td>
                @endpermission
                <td>{{ $item->kode_lokasi }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
<hr>
</body>
</html>