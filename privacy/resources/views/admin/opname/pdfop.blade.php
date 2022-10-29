<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <br>
    <title>Laporan Opname ~ {{ $no_opname }}</title>
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
            <h1>LAPORAN OPNAME {{ $no_opname }}</h1>
            <p>Tanggal Opname: <?php echo $tgl ?> | Status: {{ $status }}</p>
        
    </div>

        <table id="yay" class="grid1" width="100%" style="margin-left:0px; font-size: 11px">
            <tr >
                <th width="5%">No.</th>
                <th width="22%" >Kode Produk</th>
                <th width="35%" >Nama Produk</th>
                <th width="35%" >Partnumber</th>
                <th width="15%">Satuan</th>
                <th width="15%">Qty</th>
                @permission('read-hpp')
                <th width="25%">Amount</th>
                <th width="15%">Hpp</th>
                @endpermission
            </tr>
            </thead>
            
            <tbody>
            <?php $subtotal = 0 ; $limit_row = 0?>
            <?php foreach ($opnamedetail as $key => $value): ?>
                <tr >
                    <td ><?php echo $key+1 ?></td>
                    <td ><?php echo $value->kode_produk ?></td>
                    <td ><?php echo $value->produk->nama_produk ?></td>
                    <td ><?php echo $value->partnumber ?></td>
                    <td>{{ $value->kode_satuan }}</td>
                    <td>{{ number_format($value->stock_opname,'0','.',',') }}</td>
                    @permission('read-hpp')
                    <td>{{ number_format($value->amount_opname,'0','.',',') }}</td>
                    <td>{{ number_format($value->hpp,'0','.',',') }}</td>
                    @endpermission
                    <?php
                    $item_length = strlen($value->produk->nama_produk) ;
                    if ($item_length > 26){
                        $limit_row += 1;
                    }
                    ?>
                    
                <?php
                    $grandtotalqty = 0;
                    $grandtotaljumlah = 0;
                    $subqty = 0;
                    $subqty += $value->stock_opname;
                    $subtotal += $value->amount_opname * $value->stock_opname;
        
                    $grandtotalqty += $subqty;
                    $grandtotaljumlah += $subtotal;
                ?>

                </tr>
            <?php endforeach ?>
            
            <?php

            $total_row = count($opnamedetail);
            $max_row = (9 - $limit_row) ;
            $end = $max_row - $total_row;
            ?>
            <?php
            for ($x = 1  ; $x <= $end; $x++) {
                ?>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            <?php } ?>
            </tbody>
            
            <tfoot>
                <tr style="background-color: #F5D2D2">
                    
                    <td colspan="5" style="font-weight: bold; text-align: center">Total</td>
                
                    <td><?php echo number_format((float)$grandtotalqty, 0, ',', '.');;?></td>
                    @permission('read-hpp')
                    <td></td>
                    <td><?php echo number_format($grandtotaljumlah,'0',',','.');?></td>
                    @endpermission
                </tr>
            </tfoot>
        </table>


        <br><br>
            <table width="100%" style="font-size:10pt; text-align: center; bottom: 0">
                <tr>
                    <td width="30%">Dibuat,</td>
                </tr>
                <tr><td colspan="3"><br><br><br></td></tr>
                <tr>
                    <td><?php echo $ttd; ?></td>
                </tr>
            </table>


</body>
</html>