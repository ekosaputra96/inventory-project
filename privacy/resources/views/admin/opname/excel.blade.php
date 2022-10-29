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
			<th>Begin Amount</th>
            <th>In Stock</th>
			<th>In Amount</th>
            <th>Out Stock</th>
            <th>Out Amount</th>
            <th>Sale Stock</th>
            <th>Sale Amount</th>
            <th>Trf Out Stock</th>
            <th>Trf Out Amt</th>
            <th>Trf In Stock</th>
            <th>Trf In Amt</th>
            <th>Retur Beli Stock</th>
            <th>Retur Beli Amt</th>
            <th>Retur Jual Stock</th>
            <th>Retur Jual Amt</th>
            <th>Retur Pakai Stock</th>
            <th>Retur Pakai Amt</th>
            <th>Adjustment Stock</th>
            <th>Adjustment Amount</th>
            <th>Stock Opname</th>
            <th>Amount Opname</th>
            <th>Ending Stock</th>
            <th>Ending Amount</th>
            <th>Hpp</th>
            <th>Kode Lokasi</th>
		</tr>
	</thead>
	<tbody>
		@foreach($data as $item)
			<tr>
				<td>{{ $item->periode }}</td>
                <td>{{ $item->kode_produk }}</td>
				<td>{{ $item->produk->nama_produk }}</td>
				<?php
				    if (stripos($item->partnumber, '&') !== FALSE) {
                        $ket = stripos($item->partnumber, '&');
                        $parto = substr_replace($item->partnumber, '&amp;', $ket, 1);
                    }else {
                        $parto = $item->partnumber;
                    }
				?>
                <td>{{ $parto }}</td>
                <td>{{ $item->produk->kode_kategori }}</td>
                <td>{{ $item->begin_stock }}</td>
                <td>{{ $item->begin_amount }}</td>
                <td>{{ $item->in_stock }}</td>
                <td>{{ $item->in_amount }}</td>
                <td>{{ $item->out_stock }}</td>
                <td>{{ $item->out_amount }}</td>
                <td>{{ $item->sale_stock }}</td>
                <td>{{ $item->sale_amount }}</td>
                <td>{{ $item->trf_out }}</td>
                <td>{{ $item->trf_out_amount }}</td>
                <td>{{ $item->trf_in }}</td>
                <td>{{ $item->trf_in_amount }}</td>
                <td>{{ $item->retur_beli_stock }}</td>
                <td>{{ $item->retur_beli_amount }}</td>
                <td>{{ $item->retur_jual_stock }}</td>
                <td>{{ $item->retur_jual_amount }}</td>
                <td>{{ $item->retur_pakai_stock }}</td>
                <td>{{ $item->retur_pakai_amount }}</td>
                <td>{{ $item->adjustment_stock }}</td>
                <td>{{ $item->adjustment_amount }}</td>
                <td>{{ $item->stock_opname }}</td>
                <td>{{ $item->amount_opname }}</td>
<?php
    $endstok = $item->begin_stock + $item->in_stock + $item->trf_in + $item->retur_jual_stock + $item->retur_pakai_stock + $item->adjustment_stock + $item->stock_opname - $item->out_stock - $item->sale_stock - $item->trf_out - $item->retur_beli_stock;
    $endamount = $item->begin_amount + $item->in_amount + $item->trf_in_amount + $item->retur_jual_amount + $item->retur_pakai_amount + $item->adjustment_amount + $item->amount_opname - $item->out_amount - $item->sale_amount - $item->trf_out_amount - $item->retur_beli_amount;
    if ($endstok > 0){
        $hpps = $endamount / $endstok;
    }else {
        $hpps = $item->hpp;
    }
?>
                <td>{{ $endstok }}</td>
				<td>{{ $endamount }}</td>
				<td>{{ $hpps }}</td>
                <td>{{ $item->kode_lokasi }}</td>
			</tr>
		@endforeach
		</tbody>
	</table>
<hr>
</body>
</html>