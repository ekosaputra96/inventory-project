<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
    <meta charset="utf-8" />
    <title>LAPORAN TRANSAKSI {{$nama}}</title>
    <style>
        body {
            font-family: sans-serif;
            /*font-family: courier;*/
            /*font-weight: bold;*/
        }
        .header {
            text-align: center;
        },
        .header, h1 {
            font-size: 11pt;
            margin-bottom: 0px;
        }

        .header, p {
            font-size: 10pt;
            margin-top: 0px;
        }
        .table_content {
            color: #232323;
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 15px;
        }

        .table_content, .border {
            border: 1px solid black;
            padding: 4px;
        }
        .table_content, thead, th {
            padding: 7px;
            text-align: center;

        }
        ul li {
            display:inline;
            list-style-type:none;
        }

        table.grid1 {
          font-family: sans-serif;
          border-collapse: collapse;
          width: 100%;
        }

        table.grid1 td, table.grid1 th {
          border: 1px solid #dddddd;
          text-align: left;
          padding: 4px;
        }

        table.grid1 tr:nth-child(even) {
          background-color: #dddddd;
        }
    </style>
</head>
<body>

<div class="header">
    <img src="{{ asset('css/logo_gui.png') }}" alt="" height="25px" width="25px" align="left">
    <p id="color" style="font-size: 8pt;" align="left"><b>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo ($nama2) ?></b><br>
    &nbsp;&nbsp;&nbsp;&nbsp;Lokasi: <?php echo ($nama1) ?></p>

    <h1>LAPORAN TRANSAKSI {{$nama}}</h1>
    <p>Periode: <?php echo ($tanggal_awal);?> s/d <?php echo ($tanggal_akhir);?></p>
    <br>
	<table class="grid1" style="margin-bottom: 25px;width: 100%; font-size: 10px">
			 <thead>
		<tr style="background-color: #e6f2ff">
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
            <?php
                if ($lokasi == 'SEMUA') {?>
                    <th>Kode Lokasi</th>
            <?php } ?>
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
                <td>{{ number_format($item->harga_transaksi,'2','.',',') }}</td>
                <td>{{ number_format($item->total_transaksi,'2','.',',') }}</td>
                @endpermission
                <?php
                    if ($lokasi == 'SEMUA') {?>
                        <td>{{ $item->kode_lokasi }}</td>
                <?php } ?>
			</tr>
		@endforeach
		</tbody>
	</table>
</body>
</html>