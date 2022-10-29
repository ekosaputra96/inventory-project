<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
    <meta charset="utf-8" />
    <title>LAPORAN KARTU STOK BARANG</title>
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
            
        <h1>LAPORAN KARTU STOK BARANG <?php echo $request.' / '.$nama_produk ?></h1>
        <p>Periode: <?php echo ($nama_bulan) ?> <?php echo ($tahun) ?></p>
        <p>Dari Tgl: <?php echo $tahun.'-'.$bulan.'-01' ?>  Sampai Tgl: <?php echo ($request2) ?></p>
    </div>
<br><br>
    <table class="grid1" style="font-size: 11px; vertical-align: top; width: 27cm">
        <thead>
            <tr style="background-color: #e6f2ff">
                <th>Tanggal Transaksi</th>
                <th>Jam Transaksi</th>
                <th>Nomor Transaksi</th>
                <th>Qty Transaksi</th>
                <th>Satuan</th>
                @permission('read-hpp')
                <th>Harga</th>
                <th>Total Transaksi</th>
                @endpermission
                <?php
                    if ($lokasi == 'SEMUA') {?>
                        <th>Kode Lokasi</th>
                <?php } ?>
            </tr>
        </thead>

        
                <tr align="left" style="background-color: #F5D2D2">
                    <td align="left"><b>BEGIN</b></td>
                    <td></td>
                    <td></td>
                    <?php if($tipe_produk == 'Serial' && $kode_kategori == 'BAN') { ?>
                        <td align="right"><b>{{ $total_begin }}</b></td>
                    <?php } 
                    else if($tipe_produk == 'Serial' && $kode_kategori == 'UNIT'){ ?>
                        <td align="right"><b>{{ $total_begin }}</b></td>
                    <?php }
                    else{ ?>
                        <td align="right"><b>{{ $kartustok_saldo->begin_stock }}</b></td>
                    <?php } ?>
                    <td></td>
                    @permission('read-hpp')
                    <td></td>
                    <?php if($tipe_produk == 'Serial' && $kode_kategori == 'BAN') { ?>
                        <td align="right"><b>{{ number_format($total_amount_begin,'2','.',',') }}</b></td>
                    <?php } 
                    else if($tipe_produk == 'Serial' && $kode_kategori == 'UNIT'){ ?>
                        <td align="right"><b>{{ number_format($total_amount_begin,'2','.',',') }}</b></td>
                    <?php }
                    else{ ?>
                        <td align="right"><b>{{ number_format($kartustok_saldo->begin_amount,'2','.',',') }}</b></td>
                    <?php } ?>
                    @endpermission
                    <?php
                        if ($lokasi == 'SEMUA') {?>
                            <td></td>
                    <?php } ?>
                </tr>
                

        <tbody>
            {{ $subqty = 0 }}
            {{ $subtotal = 0 }}
            @foreach($kartustok_cetak as $item)
                <tr>
                    <td>{{ $item->tanggal_transaksi }}</td>
                    <td>{{ $item->jam_transaksi }}</td> 
                    <td>{{ $item->no_transaksi }}</td>
                    <td align="right">{{ $item->qty_transaksi }}</td>
                    <td align="right">{{ $kode_satuan }}</td>
                    @permission('read-hpp')
                    <td align="right">{{ number_format($item->harga_transaksi,'2','.',',') }}</td>
                    <td align="right">{{ number_format($item->total_transaksi,'2','.',',') }}</td>
                    @endpermission
                    {{ $subqty += $item->qty_transaksi }}
                    {{ $subtotal += $item->total_transaksi }}
                    <?php
                        if ($lokasi == 'SEMUA') {?>
                            <td>{{ $item->kode_lokasi }}</td>
                    <?php } ?>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
                <tr align="left" style="background-color: #F5D2D2">
                    <td align="left"><b>ENDING</b></td>
                    <td></td>
                    <td></td>
                    <?php if($tipe_produk == 'Serial' && $kode_kategori == 'BAN') { ?>
                        <td align="right"><b>{{ $total_ending }}</b></td>
                    <?php } 
                    else if($tipe_produk == 'Serial' && $kode_kategori == 'UNIT'){ ?>
                        <td align="right"><b>{{ $total_ending }}</b></td>
                    <?php }
                    else{ ?>
                        <td align="right"><b>{{ $kartustok_saldo->begin_stock + $subqty }}</b></td>
                    <?php } ?>
                    <td></td>
                    @permission('read-hpp')
                    <td></td>
                    <?php if($tipe_produk == 'Serial' && $kode_kategori == 'BAN') { ?>
                        <td align="right"><b>{{ number_format($total_amount_ending,'2','.',',') }}</b></td>
                    <?php } 
                    else if($tipe_produk == 'Serial' && $kode_kategori == 'UNIT'){ ?>
                        <td align="right"><b>{{ number_format($total_amount_ending,'2','.',',') }}</b></td>
                    <?php }
                    else{ ?>
                        <td align="right"><b>{{ number_format($kartustok_saldo->begin_amount + $subtotal,'2','.',',') }}</b></td>
                    <?php } ?>
                    @endpermission
                    <?php
                        if ($lokasi == 'SEMUA') {?>
                            <td></td>
                    <?php } ?>
                </tr>
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
                    <td><?php echo $limit3->mengetahui; ?></td>
                </tr>
            </table>
        <?php } 
        else{?>
            <div class="page_break"></div>
            <table class="grid1" style="margin-left: auto; margin-right: auto; width: 25%; font-size: 11px;">
                <tfoot>
                    <tr style="background-color: #e6f2ff">
                        <td style="font-weight: bold; text-align: center">Grand Total Qty</td>
                        @permission('read-hpp')
                        <td style="font-weight: bold; text-align: center">Grand Total Nilai</td>
                        @endpermission
                    </tr>
                    <tr style="background-color: #F5D2D2">
                        <td style="font-weight: bold; text-align: center">&nbsp;{{ $kartustok_saldo->begin_stock + $subqty }}</td>
                        @permission('read-hpp')
                        <td style="font-weight: bold; text-align: center">&nbsp;{{ number_format($kartustok_saldo->begin_amount + $subtotal,'2','.',',') }}</td>
                        @endpermission
                    </tr>
                </tfoot>
            </table>
            <br><br>
            <table width="100%" style="font-size:10pt; text-align: center; bottom: 0">
                <tr>
                    <td width="30%">Dibuat,</td>
                    <td width="30%">Disetujui,</td>
                </tr>
                <tr><td colspan="3"><br><br><br></td></tr>
                <tr>
                    <td><?php echo $ttd; ?></td>
                    <td><?php echo $limit3->mengetahui; ?></td>
                </tr>
            </table>
    <?php } ?>
</body>
</html>