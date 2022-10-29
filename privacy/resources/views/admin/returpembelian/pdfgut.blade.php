<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        use App\Models\PembelianDetail;
    ?>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <br>
    <title>Retur Pembelian ~ {{ $no_returpembelian }}</title>
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
            padding-top: 160px
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
            padding-left:4mm;
            padding-right:4mm;
            /*border:1px solid #fff;*/
        }
        .list-item {
            height: 2.1in;
            margin: 0px;
        }

    </style>

</head>
<body>

<!-- <div class="left">
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
<br> -->

<div class="title">
    <h1>RETUR PEMBELIAN BARANG</h1>
</div>

<div class="header">
    <div class="left">
        <table width="50%" style="font-size: 10pt" border="0">
            <tr >
                <td style="width: 100px">Vendor</td>
                <td style="width: 10px">:</td>
                <td>{{ $pembelian->vendor->nama_vendor }}</td>
            </tr>
            <tr>
                <td >Alamat</td>
                <td>:</td>
                <td>{{ $pembelian->vendor->alamat }}</td>
            </tr>
            <tr>
                <td>Kontak</td>
                <td>:</td>
                <td>{{ $pembelian->vendor->telp }}</td>
            </tr>

            <tr>
                <td>NPWP</td>
                <td>:</td>
                <td>{{ $pembelian->vendor->npwp }}</td>
            </tr>
        </table>
    </div>
    <div class="right">
        <table width="50%" style="font-size: 10pt" border="0">
            <tr>
                <td style="width: 180px">No. Retur Pembelian</td>
                <td style="width: 10px">:</td>
                <td>{{ $no_returpembelian }}</td>
            </tr>
            <tr>
                <td>Tanggal Retur</td>
                <td>:</td>
                <td>{{ $tgl }}</td>
            </tr>
            <tr>
                <td>No. Pembelian</td>
                <td>:</td>
                <td>{{ $no_pembelian }}</td>
            </tr>
            <tr>
                <td>No. Penerimaan</td>
                <td>:</td>
                <td>{{ $no_penerimaan }}</td>
            </tr>

        </table>
    </div>
</div>
<div class="content">
    <section class="list-item">
        <table class="grid" style="font-size: 10pt; width: 19cm;" border="0" >
            <thead>
            <tr >
                <th width="5%"></th>
                <th width="35%" ></th>
                <th width="8%"></th>
                <th width="10%"></th>
                <th colspan="2" width=""></th>
                <th colspan="2" width=""></th>
            </tr>
            </thead>
            <tbody>
            <?php $subtotal = 0 ; $limit_row = 0?>
            <?php foreach ($returpembeliandetail as $key => $value): ?>
                <tr >
                    <td ><?php echo $key+1 ?></td>
                    <td ><?php echo $value->produk->nama_produk ?></td>
                    <td align="center">
                        <?php
                            $qty =substr($value->qty,-3);
                            if ($qty > 0 )
                                echo $value->qty;
                            else
                                echo (int) $value->qty
                        ?>
                    </td>
                    <td align="center">{{ $value->kode_satuan }}</td>
                    <td align="center">Rp</td>
                    <?php 
                        $getharga = PembelianDetail::on($konek)->where('no_pembelian',$no_pembelian)->where('kode_produk',$value->kode_produk)->first();
                    ?>
                    <td align="right"><?php echo number_format($getharga->harga,'0','.',',') ?></td>
                    <td align="center">Rp</td>
                    <td style="text-align: right">
                        <?php
                        $total = $value->qty * $getharga->harga ;
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

            $total_row = count($returpembeliandetail);
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
$total_diskon = ($subtotal * ($pembelian->diskon_persen / 100) + $pembelian->diskon_rp);
$total_pbbkb = (($subtotal - $total_diskon) * ($pembelian->pbbkb / 100) + $pembelian->pbbkb_rp);
$total_ppn = ($subtotal - $total_diskon)*($pembelian->ppn / 100);
$grand_total = round(($subtotal - $total_diskon) + $total_pbbkb + $total_ppn + $pembelian->ongkos_angkut + $pembelian->ongkos_kirim);
?>

<table class="grid" style="font-size: 10pt; width: 19cm; padding-top: 30px" border="0" >
    <tr >
        <td  colspan="7" rowspan="7" style="font-size: 10pt; vertical-align: top; width: 58%">
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
    <?php
        if ($total_diskon != 0) {?>
        <tr>
            <td align="right">Disc. </td>
            <td>Rp</td>
            <td align="right">
                <?php echo number_format($total_diskon,'0','.',',') ;?>
            </td>
        </tr>
    <?php } ?>

    <?php
        if ($total_pbbkb != 0) {?>
        <tr>
            <td align="right">PBBKB </td>
            <td>Rp</td>
            <td align="right">
                <?php echo number_format($total_pbbkb,'0','.',',') ;?>
            </td>
        </tr>
    <?php } ?>

    <?php
        if ($pembelian->ongkos_angkut != 0) {?>
        <tr>
            <td align="right">Ongkos Angkut</td>
            <td>Rp</td>
            <td align="right">
                <?php echo number_format($pembelian->ongkos_angkut,'0','.',',') ;?>
            </td>
        </tr>
    <?php } ?>

    <?php
        if ($pembelian->ppn != 0) {?>
        <tr>
            <td align="right">PPN
                <?php
                if ($pembelian->ppn) {?>
                (<?php echo $pembelian->ppn; ?>%)
                <?php } ?>
            </td>
            <td>Rp</td>
            <td align="right">
                <?php echo number_format($total_ppn,'0','.',',') ; ?>
            </td>
        </tr>
    <?php } ?>
    
    <?php
        if ($pembelian->ongkos_kirim != 0) {?>
        <tr>
            <td align="right">Ongkos Kirim</td>
            <td>Rp</td>
            <td align="right">
                <?php echo number_format($pembelian->ongkos_kirim,'0','.',',') ;?>
            </td>
        </tr>
    <?php } ?>
    <?php
        if ($total_diskon != 0 || $pembelian->ppn != 0 || $pembelian->ongkos_kirim != 0 || $pembelian->ongkos_angkut != 0 || $total_pbbkb != 0) {?>
        <tr>
            <td align="right">Grand Total</td>
            <td>Rp</td>
            <td align="right">
                <?php echo number_format($grand_total,'0','.',',') ;?>
            </td>
        </tr>
    <?php } ?>

</table>



</body>
</html>