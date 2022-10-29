<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    
    <title>LAPORAN DATA PRODUK BULANAN</title>
    <style>
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

        body{        
            padding-top: 110px;
            font-family: sans-serif;
        }
        .fixed-header, .fixed-footer{
            width: 100%;
            position: fixed;       
            padding: 10px 0;
            text-align: center;
        }
        .fixed-header{
            top: 0;
        }
        .fixed-footer{
            bottom: 0;
        }

        #header .page:after {
          content: counter(page, decimal);
        }

        .page_break { page-break-after: always; }
    </style>
</head>
<body>

<div class="fixed-header">
        <div style="float: left">
            <img src="{{ asset('css/logo_gui.png') }}" alt="" height="25px" width="25px" align="left">
            <p id="color" style="font-size: 8pt;" align="left"><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo ($nama2) ?></b><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lokasi: <?php echo ($nama) ?></p>
        </div>

        <div id="header">
            <p class="page" style="float: right; font-size: 9pt;"><b>Date :</b> <?php echo date_format($dt,"d/m/Y") ?>&nbsp;&nbsp;&nbsp;
            <b>Time :</b> <?php echo date_format($dt,"H:i:s") ?>&nbsp;&nbsp;&nbsp;
            <b>Page :</b> </p>
        </div>

        <br><br>
            <?php
                if ($kategori != 'SEMUA') {?>
                    <h1>LAPORAN PRODUK BULANAN KATEGORI <?php echo $kategori; ?></h1>
            <?php } 
                else{?>
                    <h1>LAPORAN PRODUK BULANAN</h1>
            <?php } ?>

            <p>Periode: <?php echo ($nama_bulan) ?> <?php echo ($req) ?></p>
        
    </div>


    <?php
        $grandtotaljumlah = 0;
        $grandtotaljumlah2 = 0;
        $grandtotaljumlah3 = 0;
        $grandtotaljumlah4 = 0;
        $grandtotaljumlah5 = 0;
        $grandtotaljumlah6 = 0;
        $grandtotaljumlah7 = 0;
        $grandtotaljumlah8 = 0;
        $grandtotaljumlah9 = 0;
        $grandtotaljumlah10 = 0;
        $grandtotaljumlah11 = 0;
    ?>

    <table class="grid1" style="margin-bottom: 25px;width: 100%; font-size: 10px">
        <thead>
            <tr style="background-color: #e6f2ff">
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Partnumber</th>
                <?php
                    if ($kategori == 'SEMUA') {?>
                        <th>Kode Kategori</th>
                <?php } ?>

                <?php
                    if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                        <th>Begin Stock</th>
                <?php } ?>

                <?php
                    if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                        @permission('read-hpp')
                        <th>Begin Amount</th>
                        @endpermission
                <?php } ?>

                <?php
                    if ($penerimaan == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <th>In Stock</th>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <th>In Amount</th>
                                @endpermission
                        <?php } ?>
                <?php } ?>

                <?php
                    if ($pemakaian == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <th>Out Stock</th>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <th>Out Amount</th>
                                @endpermission
                        <?php } ?>
                <?php } ?>

                <?php
                    if ($penjualan == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <th>Sale Stock</th>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <th>Sale Amount</th>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($transferin == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <th>Tran. In Stock</th>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <th>Tran. In Amount</th>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($transferout == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <th>Tran. Out Stock</th>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <th>Tran. Out Amount</th>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($adjustment == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <th>Adjustment Stock</th>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <th>Adjustment Amount</th>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($opname == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <th>Stock Opname</th>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <th>Amount Opname</th>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($returbeli == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <th>Retur Beli Stock</th>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <th>Retur Beli Amount</th>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($returjual == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <th>Retur Jual Stock</th>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <th>Retur Jual Amount</th>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                        <th>Ending Stock</th>
                <?php } ?>
                <?php
                    if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                        @permission('read-hpp')
                        <th>Ending Amount</th>
                        @endpermission
                <?php } ?>
                @permission('read-hpp')
                <th>Hpp</th>
                @endpermission
                <?php if ($lokasi2 == 'SEMUA'){ ?>
                    <th>Lokasi</th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
        @foreach($opnamedetail_cetak as $item)
            <tr>
                <td>{{ $item->kode_produk }}</td>
                <td>{{ $item->nama_produk }}</td>
                <td>{{ $item->partnumber }}</td>
                <?php
                    if ($kategori == 'SEMUA') {?>
                        <td>{{ $item->kode_kategori }}</td>
                <?php } ?>
                <?php
                    if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                        <td>{{ $item->begin_stock }}</td>
                <?php } ?>
                <?php
                    if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                        @permission('read-hpp')
                        <td>{{ number_format($item->begin_amount,'0','.',',') }}</td>
                        @endpermission
                <?php } ?>
                <?php
                    if ($penerimaan == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <td>{{ $item->in_stock }}</td>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <td>{{ number_format($item->in_amount,'0','.',',') }}</td>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($pemakaian == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <td>{{ $item->out_stock }}</td>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <td>{{ number_format($item->out_amount,'0','.',',') }}</td>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($penjualan == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <td>{{ $item->sale_stock }}</td>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <td>{{ number_format($item->sale_amount,'0','.',',') }}</td>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($transferin == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <td>{{ $item->trf_in }}</td>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <td>{{ number_format($item->trf_in_amount,'0','.',',') }}</td>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($transferout == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <td>{{ $item->trf_out }}</td>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <td>{{ number_format($item->trf_out_amount,'0','.',',') }}</td>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($adjustment == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <td>{{ $item->adjustment_stock }}</td>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <td>{{ number_format($item->adjustment_amount,'0','.',',') }}</td>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($opname == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <td>{{ $item->stock_opname }}</td>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <td>{{ number_format($item->amount_opname,'0','.',',') }}</td>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($returbeli == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <td>{{ $item->retur_beli_stock }}</td>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <td>{{ number_format($item->retur_beli_amount,'0','.',',') }}</td>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($returjual == 'true' || $semua == 'true') {?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                                <td>{{ $item->retur_jual_stock }}</td>
                        <?php } ?>
                        <?php
                            if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                                @permission('read-hpp')
                                <td>{{ number_format($item->retur_jual_amount,'0','.',',') }}</td>
                                @endpermission
                        <?php } ?>
                <?php } ?>
                <?php
                    if ($field1 == 'SEMUA' || $field1 == 'Stock') {?>
                        <td>{{ $item->ending_stock }}</td>
                <?php } ?>
                <?php
                    if ($field1 == 'SEMUA' || $field1 == 'Amount') {?>
                        @permission('read-hpp')
                        <td>{{ number_format($item->ending_amount,'0','.',',') }}</td>
                        @endpermission
                <?php } ?>
                @permission('read-hpp')
                <td>{{ number_format($item->hpp,'0','.',',') }}</td>
                @endpermission
                <?php if ($lokasi2 == 'SEMUA'){ ?>
                    <td>{{ $item->kode_lokasi }}</td>
                <?php } ?>
            </tr>
            
            <?php
            $subtotal = 0;
            $subtotal2 = 0;
            $subtotal3 = 0;
            $subtotal4 = 0;
            $subtotal5 = 0;
            $subtotal6 = 0;
            $subtotal7 = 0;
            $subtotal8 = 0;
            $subtotal9 = 0;
            $subtotal10 = 0;
            $subtotal11 = 0;
            $subtotal += $item->ending_amount;
            $subtotal2 += $item->begin_amount;
            $subtotal3 += $item->in_amount;
            $subtotal4 += $item->out_amount;
            $subtotal5 += $item->adjustment_amount;
            $subtotal6 += $item->sale_amount;
            $subtotal7 += $item->trf_in_amount;
            $subtotal8 += $item->trf_out_amount;
            $subtotal9 += $item->amount_opname;
            $subtotal10 += $item->retur_beli_amount;
            $subtotal11 += $item->retur_jual_amount;
            $grandtotaljumlah += $subtotal;
            $grandtotaljumlah2 += $subtotal2;
            $grandtotaljumlah3 += $subtotal3;
            $grandtotaljumlah4 += $subtotal4;
            $grandtotaljumlah5 += $subtotal5;
            $grandtotaljumlah6 += $subtotal6;
            $grandtotaljumlah7 += $subtotal7;
            $grandtotaljumlah8 += $subtotal8;
            $grandtotaljumlah9 += $subtotal9;
            $grandtotaljumlah10 += $subtotal10;
            $grandtotaljumlah11 += $subtotal11;
            ?>
            
        @endforeach
        </tbody>
        
        <tfoot>
        @permission('read-hpp')
        <tr style="background-color: #F5D2D2">
            <?php
                if ($field1 == 'Stock') {?>
                    <?php
                        if ($kategori != 'SEMUA') {?>
                            <td colspan="4" style="font-weight: bold; text-align: center"></td>
                    <?php } 
                        else{?>
                            <td colspan="5" style="font-weight: bold; text-align: center"></td>
                    <?php } ?>
                    <?php
                        if ($penerimaan == 'true' || $semua == 'true') {?>
                            <td></td>
                    <?php } ?>  
                    <?php
                        if ($pemakaian == 'true' || $semua == 'true') {?>
                            <td></td>
                    <?php } ?>   
                    <?php
                        if ($penjualan == 'true' || $semua == 'true') {?>
                            <td></td>
                    <?php } ?>  
                    <?php
                        if ($transferin == 'true' || $semua == 'true') {?>
                            <td></td>
                    <?php } ?>  
                    <?php
                        if ($transferout == 'true' || $semua == 'true') {?>
                            <td></td>
                    <?php } ?> 
                    <?php
                        if ($adjustment == 'true' || $semua == 'true') {?>
                            <td></td>
                    <?php } ?> 
                    <?php
                        if ($opname == 'true' || $semua == 'true') {?>
                            <td></td>
                    <?php } ?> 
                    <?php
                        if ($returbeli == 'true' || $semua == 'true') {?>
                            <td></td>
                    <?php } ?> 
                    <?php
                        if ($returjual == 'true' || $semua == 'true') {?>
                            <td></td>
                    <?php } ?> 
                    <td></td>
                    <td></td>
                    <?php if ($lokasi2 == 'SEMUA'){ ?>
                        <td></td>
                    <?php } ?>
                <?php } 
                else if($field1 == 'Amount'){?>
                    <?php
                        if ($kategori != 'SEMUA') {?>
                            <td colspan="3" style="font-weight: bold; text-align: center">Total</td>
                    <?php } 
                        else{?>
                            <td colspan="4" style="font-weight: bold; text-align: center">Total</td>
                    <?php } ?>
                    
                    <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah2,'0',',','.');?></td>
                    <?php
                        if ($penerimaan == 'true' || $semua == 'true') {?>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah3,'0',',','.');?></td>
                    <?php } ?>  
                    <?php
                        if ($pemakaian == 'true' || $semua == 'true') {?>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah4,'0',',','.');?></td>
                    <?php } ?>   
                    <?php
                        if ($penjualan == 'true' || $semua == 'true') {?>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah6,'0',',','.');?></td>
                    <?php } ?>  
                    <?php
                        if ($transferin == 'true' || $semua == 'true') {?>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah7,'0',',','.');?></td>
                    <?php } ?>  
                    <?php
                        if ($transferout == 'true' || $semua == 'true') {?>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah8,'0',',','.');?></td>
                    <?php } ?> 
                    <?php
                        if ($adjustment == 'true' || $semua == 'true') {?>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah5,'0',',','.');?></td>
                    <?php } ?> 
                    <?php
                        if ($opname == 'true' || $semua == 'true') {?>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah9,'0',',','.');?></td>
                    <?php } ?> 
                    <?php
                        if ($returbeli == 'true' || $semua == 'true') {?>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah10,'0',',','.');?></td>
                    <?php } ?> 
                    <?php
                        if ($returjual == 'true' || $semua == 'true') {?>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah11,'0',',','.');?></td>
                    <?php } ?> 
                    <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah,'0',',','.');?></td>
                    <td></td>
                    <?php if ($lokasi2 == 'SEMUA'){ ?>
                        <td></td>
                    <?php } ?>
                <?php } 
                else if($field1 == 'SEMUA'){?>
                    <?php
                        if ($kategori != 'SEMUA' && $field1 == 'SEMUA') {?>
                            <td colspan="4" style="font-weight: bold; text-align: center">Total</td>
                    <?php } 
                        else{?>
                            <td colspan="5" style="font-weight: bold; text-align: center">Total</td>
                    <?php } ?>
                    
                    <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah2,'0',',','.');?></td>
                    <?php
                        if ($penerimaan == 'true' || $semua == 'true') {?>
                            <td></td>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah3,'0',',','.');?></td>
                    <?php } ?>  
                    <?php
                        if ($pemakaian == 'true' || $semua == 'true') {?>
                            <td></td>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah4,'0',',','.');?></td>
                    <?php } ?>   
                    <?php
                        if ($penjualan == 'true' || $semua == 'true') {?>
                            <td></td>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah6,'0',',','.');?></td>
                    <?php } ?>  
                    <?php
                        if ($transferin == 'true' || $semua == 'true') {?>
                            <td></td>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah7,'0',',','.');?></td>
                    <?php } ?>  
                    <?php
                        if ($transferout == 'true' || $semua == 'true') {?>
                            <td></td>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah8,'0',',','.');?></td>
                    <?php } ?> 
                    <?php
                        if ($adjustment == 'true' || $semua == 'true') {?>
                            <td></td>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah5,'0',',','.');?></td>
                    <?php } ?> 
                    <?php
                        if ($opname == 'true' || $semua == 'true') {?>
                            <td></td>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah9,'0',',','.');?></td>
                    <?php } ?> 
                    <?php
                        if ($returbeli == 'true' || $semua == 'true') {?>
                            <td></td>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah10,'0',',','.');?></td>
                    <?php } ?> 
                    <?php
                        if ($returjual == 'true' || $semua == 'true') {?>
                            <td></td>
                            <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah11,'0',',','.');?></td>
                    <?php } ?> 
                    <td></td>
                    <td align="center">&nbsp;<?php echo number_format($grandtotaljumlah,'0',',','.');?></td>
                    <td></td>
                    <?php if ($lokasi2 == 'SEMUA'){ ?>
                        <td></td>
                    <?php } ?>
            <?php } ?> 
        </tr>
        @endpermission
        </tfoot>
    </table>

    <?php
        if ($format_ttd != 1) {?>
            <br><br>
            <table width="100%" style="font-size:10pt; text-align: center; bottom: 0">
                <tr>
                    <td width="30%">Dibuat,</td>
                    <td width="30%">Disetujui,</td>
                </tr>
                <tr><td colspan="3"><br><br><br></td></tr>
                <tr>
                    <td><?php echo $ttd; ?></td>
                    <td><?php echo $limithse->mengetahui; ?></td>
                </tr>
            </table>
        <?php } 
        else{?>
            <div class="page_break"></div>
            @permission('read-hpp')
            <?php
                if ($field1 != 'Stock') {?>
                    <table class="grid1" style="margin-left: auto; margin-right: auto; width: 25%; font-size: 11px;">
                        <tfoot>
                            <tr style="background-color: #e6f2ff">
                                <td style="font-weight: bold; text-align: center">Grand Total</td>
                            </tr>
                            <tr style="background-color: #F5D2D2">
                                <td style="font-weight: bold; text-align: center">&nbsp;<?php echo number_format($grandtotaljumlah,'0',',','.');?></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php } ?>
            @endpermission
            <br><br>
            <table width="100%" style="font-size:10pt; text-align: center; bottom: 0">
                <tr>
                    <td width="30%">Dibuat,</td>
                    <td width="30%">Disetujui,</td>
                </tr>
                <tr><td colspan="3"><br><br><br></td></tr>
                <tr>
                    <td><?php echo $ttd; ?></td>
                    <td><?php echo $limithse->mengetahui; ?></td>
                </tr>
            </table>
    <?php } ?>
</body>
</html>