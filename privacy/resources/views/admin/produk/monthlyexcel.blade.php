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
            <th>Periode</th>
            <th>Kode Produk</th>
            <th>Nama Produk</th>
            <th>Part Number</th>
            <th>Kategori</th>
            <th>Begin Stock</th>
            @permission('read-hpp')
            <th>Begin Amount</th>
            @endpermission
            <th>In Stock</th>
            @permission('read-hpp')
            <th>In Amount</th>
            @endpermission
            <th>Out Stock</th>
            @permission('read-hpp')
            <th>Out Amount</th>
            @endpermission
            <th>Sale Stock</th>
            @permission('read-hpp')
            <th>Sale Amount</th>
            @endpermission
            <th>Transfer In Stock</th>
            @permission('read-hpp')
            <th>Transfer In Amount</th>
            @endpermission
            <th>Transfer Out Stock</th>
            @permission('read-hpp')
            <th>Transfer Out Amount</th>
            @endpermission
            <th>Adjustment Stock</th>
            @permission('read-hpp')
            <th>Adjustment Amount</th>
            @endpermission
            <th>Stock Opname</th>
            @permission('read-hpp')
            <th>Amount Opname</th>
            @endpermission
            <th>Retur Beli Stock</th>
            @permission('read-hpp')
            <th>Retur Beli Amount</th>
            @endpermission
            <th>Retur Jual Stock</th>
            @permission('read-hpp')
            <th>Retur Jual Amount</th>
            @endpermission
            <th>Disassembling Stock</th>
            @permission('read-hpp')
            <th>Disassembling Amount</th>
            @endpermission
            <th>Assembling Stock</th>
            @permission('read-hpp')
            <th>Assembling Amount</th>
            @endpermission
            <th>Ending Stock</th>
            @permission('read-hpp')
            <th>Ending Amount</th>
            <th>Hpp</th>
            @endpermission
            <th>Kode Lokasi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $item)
            <tr>
                <td>{{ $item->periode }}</td>
                <td>{{ $item->kode_produk }}</td>
                <td>{{ $item->produk->nama_produk }}</td>
                <td>{{ $item->partnumber }}</td>
                <td>{{ $item->produk->kode_kategori }}</td>
                <td>{{ $item->begin_stock }}</td>
                @permission('read-hpp')
                <td>{{ $item->begin_amount }}</td>
                @endpermission
                <td>{{ $item->in_stock }}</td>
                @permission('read-hpp')
                <td>{{ $item->in_amount }}</td>
                @endpermission
                <td>{{ $item->out_stock }}</td>
                @permission('read-hpp')
                <td>{{ $item->out_amount }}</td>
                @endpermission
                <td>{{ $item->sale_stock }}</td>
                @permission('read-hpp')
                <td>{{ $item->sale_amount }}</td>
                @endpermission
                <td>{{ $item->trf_in }}</td>
                @permission('read-hpp')
                <td>{{ $item->trf_in_amount }}</td>
                @endpermission
                <td>{{ $item->trf_out }}</td>
                @permission('read-hpp')
                <td>{{ $item->trf_out_amount }}</td>
                @endpermission
                <td>{{ $item->adjustment_stock }}</td>
                @permission('read-hpp')
                <td>{{ $item->adjustment_amount }}</td>
                @endpermission
                <td>{{ $item->stock_opname }}</td>
                @permission('read-hpp')
                <td>{{ $item->amount_opname }}</td>
                @endpermission
                <td>{{ $item->retur_beli_stock }}</td>
                @permission('read-hpp')
                <td>{{ $item->retur_beli_amount }}</td>
                @endpermission
                <td>{{ $item->retur_jual_stock }}</td>
                @permission('read-hpp')
                <td>{{ $item->retur_jual_amount }}</td>
                @endpermission
                <td>{{ $item->disassembling_stock }}</td>
                @permission('read-hpp')
                <td>{{ $item->disassembling_amount }}</td>
                @endpermission
                <td>{{ $item->assembling_stock }}</td>
                @permission('read-hpp')
                <td>{{ $item->assembling_amount }}</td>
                @endpermission
                <td>{{ $item->ending_stock }}</td>
                @permission('read-hpp')
                <td>{{ $item->ending_amount }}</td>
                <td>{{ $item->hpp }}</td>
                @endpermission
                <td>{{ $item->kode_lokasi }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
<hr>
</body>
</html>