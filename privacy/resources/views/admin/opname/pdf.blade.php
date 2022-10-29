<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Monthly Stock {{ $request }} - {{ $req }} </title>

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

    <div class="title">
    <h1>Monthly Stock {{ $request }} - {{ $req }} </h1>
    </div>
  <hr>
	<table rules="rows" class="grid" style="font-size: 10pt; vertical-align: top; width: 27cm" border="1">
			 <thead>
		<tr>
			<th>Periode</th>
			<th>Kode Produk</th>
            <th>Nama Produk</th>
			<th>Begin Stock</th>
			<th>Begin Amount</th>
            <th>In Stock</th>
			<th>In Amount</th>
            <th>Out Stock</th>
            <th>Out Amount</th>
            <th>Adjustment Stock</th>
            <th>Adjustment Amount</th>
            <th>Stock Opname</th>
            <th>Amount Opname</th>
            <th>Ending Stock</th>
            <th>Ending Amount</th>
		</tr>
	</thead>
	<tbody>
		@foreach($opnamedetail_cetak as $item)
			<tr>
				<td>{{ $item->periode }}</td>
				<td>{{ $item->kode_produk }}</td>
                <td>{{ $item->produk->nama_produk }}</td>
                <td>{{ $item->begin_stock }}</td>
                <td>{{ number_format($item->begin_amount,'0','.',',') }}</td>
                <td>{{ $item->in_stock }}</td>
                <td>{{ number_format($item->in_amount,'0','.',',') }}</td>
                <td>{{ $item->out_stock }}</td>
                <td>{{ number_format($item->out_amount,'0','.',',') }}</td>
                <td>{{ $item->adjustment_stock }}</td>
                <td>{{ number_format($item->adjustment_amount,'0','.',',') }}</td>
                <td>{{ $item->stock_opname }}</td>
                <td>{{ number_format($item->amount_opname,'0','.',',') }}</td>
                <td>{{ substr($item->ending_stock,-3) }}</td>
				<td>{{ number_format($item->ending_amount,'0','.',',') }}</td>
			</tr>
		@endforeach
		</tbody>
	</table>
<hr>
</body>
</html>