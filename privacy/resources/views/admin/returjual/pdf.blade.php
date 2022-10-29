<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <br>
    <title>Laporan Retur Penjualan ~ {{ $no_retur_jual }}</title>
    <style>
        @page {
            border: solid 1px #0b93d5;
            width: 24.13cm;
            height: 27.94cm;
            font-family: 'Courier';
            font-weight: bold;
            margin-right: 2cm;
        }

        .title {
            margin-top: 1.2cm;
        }
        .title h1 {
            text-align: center;
            font-size: 14pt;

        }
        }

        .header {
            margin-left: 0px;
            margin-right: 0px;
            /*font-size: 10pt;*/
            padding-top: 30px;
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
            padding-top: 105px
        }
        .catatan {
            font-size: 10pt;
        }

        /* Table desain*/
        table.grid {
            width: 100%;
        }
        table.grid th{
            background: #FFF;
            text-align:center;
            /*padding-left:0.2cm;*/
            /*padding-right:0.2cm;*/
            /*border:1px solid #fff;*/
            padding-top:3mm;
            padding-bottom:3mm;
        }

        table.grid tr td{
            /*padding-top:0.5mm;*/
            /*padding-bottom:0.5mm;*/
            padding-left:2mm;
            padding-right:2mm;
            /*border:1px solid #fff;*/
        }
        .list-item {
            height: 2.1in;
            margin: 0px;
        }

    </style>

</head>
<body>

<div class="left">
    <table style="padding-left:1mm; font-size:10pt">
                        <tr>
                            <td><?=$nama_company?></td>
                        </tr>
    </table>
</div>

<div class="right">
    <table style="padding-right:1mm; font-size:10pt">
                        <tr>
                            <td>Tanggal Cetak</td>
                            <td width="15%">:</td>
                            <td><?=$date_now?></td>
                        </tr>
    </table>
</div>
<br>

<div class="title">
    <h1>RETUR PENJUALAN BARANG</h1>
</div>

<div class="header">
    <div class="left">
        <table width="50%" style="font-size: 10pt" border="0">
            <tr>
                <td style="width: 180px">Nama Customer</td>
                <td style="width: 10px">:</td>
                <td>{{ $nama_customer }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td>{{ $alamat }}</td>
            </tr>
        </table>
    </div>
    <div class="right">
        <table width="50%" style="padding-left:8em; font-size: 10pt" border="0">
            <tr>
                <td style="width: 180px">No. Retur Penjualan</td>
                <td style="width: 10px">:</td>
                <td>{{ $no_retur_jual }}</td>
            </tr>
            <tr>
                <td>Tanggal Retur</td>
                <td>:</td>
                <td>{{ $tgl }}</td>
            </tr>
            <tr>
                <td>No. Penjualan</td>
                <td>:</td>
                <td>{{ $no_penjualan }}</td>
            </tr>
        </table>
    </div>
</div>
<br><br><br>
<div class="content">
    <section class="list-item">
        <table style="font-size: 10pt; width: 19cm;" border="0" >
            <thead>
            <tr >
                <th width="5%">No.</th>
                <th width="50%" >Nama Item</th>
                <th width="35%" >Partnumber</th>
                <th width="10%">Qty</th>
                <th width="15%">Satuan</th>
                <th width="30%">Harga Jual</th>
                <th width="10%">Total</th>
            </tr>
            </thead>
            <tbody>
            <?php $subtotal = 0 ; $limit_row = 0?>
            <?php foreach ($returpenjualandetail as $key => $value): ?>
                <tr >
                    <td ><?php echo $key+1 ?></td>
                    <td ><?php echo $value->produk->nama_produk ?></td>
                    <td ><?php echo $value->partnumber ?></td>
                    <td >
                        <?php
                            $qty_retur =substr($value->qty_retur,-3);
                            if ($qty_retur > 0 )
                                echo $value->qty_retur;
                            else
                                echo (int) $value->qty_retur
                        ?>
                    </td>
                    <td>{{ $value->kode_satuan }}</td>
                    <td><?php echo number_format($value->harga_jual,'0','.',',') ?></td>
                    <td>
                        <?php
                        $total = $value->qty_retur * $value->harga_jual ;
                        echo number_format($total,'0','.',',');
                        ?>
                    </td>
                    <?php $subtotal = $subtotal + floor($total); ?>
                    <?php
                    $item_length = strlen($value->produk->nama_produk) ;
                    if ($item_length > 26){
                        $limit_row += 1;
                    }
                    ?>

                </tr>
            <?php endforeach ?>
            
            <?php

            $total_row = count($returpenjualandetail);
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

        </table>
    </section>
</div>
<?php
$total_diskon = ($subtotal * ($penjualan->diskon_persen / 100) + $penjualan->diskon_rp);
$total_ppn = (($subtotal - $total_diskon) * ($penjualan->ppn / 100));
$grand_total = round(($subtotal - $total_diskon) + $total_ppn);
?>
<!--<br><br><br><br><br><br><br><br><br><br><br><br><br><br>-->
<table class="grid" style="font-size: 10pt; width: 19cm; padding-top: 30px" border="0" >
    <tr >
        <td  colspan="4" rowspan="4" style="vertical-align: top; width: 58%">
            <strong>Terbilang : <br>
                <?php echo Terbilang::make($grand_total, ' rupiah'); ?>
            </strong>
        </td>
        <td width="21%" align="right">Subtotal </td>
        <td width="5%">Rp </td>
        <td align="right">
            <?php echo number_format($subtotal,'0','.',',') ?>
        </td>
    </tr>
    <tr>
        <td align="right">Disc. </td>
        <td>Rp</td>
        <td align="right">
            <?php echo number_format($total_diskon,'0','.',',') ;?>
        </td>
    </tr>
    <tr>
        <td align="right">PPN
            <?php
            if ($penjualan->ppn) {?>
            (<?php echo $penjualan->ppn; ?>%)
            <?php } ?>
        </td>
        <td>Rp</td>
        <td align="right">
            <?php echo number_format($total_ppn,'0','.',',') ; ?>
        </td>
    </tr>
    <tr>
        <td align="right">Grand Total</td>
        <td>Rp</td>
        <td align="right">
            <?php echo number_format($grand_total,'0','.',',') ;?>
        </td>
    </tr>

</table>

</body>
</html>