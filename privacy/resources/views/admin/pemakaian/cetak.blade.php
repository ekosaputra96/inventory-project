<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <br>
    <title>Laporan Pemakaian ~ {{ $no_pemakaian }}</title>
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

<div class="right" style="padding-right:1mm; font-size:10pt">
    <table>
        <tr>
            <td>Tanggal Cetak</td>
            <td width="15%">:</td>
            <td><?=$date_now?></td>
        </tr>
    </table>
    <p style="padding-top:-15px; padding-left:3px;">Printed By &nbsp;&nbsp;&nbsp; {{ auth()->user()->name}}</p>
</div>
<br>

<div class="title">
    <h1>PEMAKAIAN BARANG</h1>
</div>

<div class="header">
    <div class="left">
        <table width="50%" style="font-size: 10pt" border="0">
            <tr>
                <td style="width: 180px">Tipe Pemakaian</td>
                <td style="width: 10px">:</td>
                <td>{{ $type }}</td>
            </tr>

            <?php
                if ($nama != '') {?>
                <tr>
                    <td>Nama {{ $type }}</td>
                <td>:</td>
                <td>{{ $nama }}</td>
                </tr>
            <?php } ?>

            <?php
                if ($aset != '') {?>
                <tr>
                    <td>No. Asset</td>
                    <td>:</td>
                    <td>{{ $aset }}</td>
                </tr>
            <?php } ?>

            <?php
                if ($pemakaian->no_jo != 0) {?>
                <tr>
                    <td>No. JO</td>
                    <td>:</td>
                    <td>{{ $pemakaian->no_jo }}</td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <div class="right">
        <table width="50%" style="padding-left:8em; font-size: 10pt" border="0">
            <tr>
                <td style="width: 180px">No. Pemakaian</td>
                <td style="width: 10px">:</td>
                <td>{{ $no_pemakaian }}</td>
            </tr>
            <tr>
                <td>Pemakai</td>
                <td>:</td>
                <td>{{ $pemakai }}</td>
            </tr>
            <tr>
                <td>Tanggal Pemakaian</td>
                <td>:</td>
                <td>{{ $tgl }}</td>
            </tr>

            <?php
                if ($pemakaian->hmkm != '') {?>
                <tr>
                    <td>HMKM</td>
                    <td>:</td>
                    <td>{{ $pemakaian->hmkm }}</td>
                </tr>
            <?php } ?>
            
            <?php
                if ($pemakaian->no_wo != null) {?>
                <tr>
                    <td>No. WO</td>
                    <td>:</td>
                    <td>{{ $pemakaian->no_wo }}</td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
<br><br>
<div class="content">
    <section class="list-item">
        <table style="font-size: 10pt; width: 22cm;" border="0" >
            <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="50%" >Nama Item</th>
                <th width="25%" >Partnumber</th>
                <th width="10%">Qty</th>
                <th width="15%">Qty Retur</th>
                <th width="15%">Satuan</th>
            </tr>
            </thead>
            <tbody>
            <?php $subtotal = 0 ; $limit_row = 0?>
            <?php foreach ($pemakaiandetail as $key => $value): ?>
                <tr >
                    <td ><?php echo $key+1 ?></td>
                    <td ><?php echo $value->produk->nama_produk ?></td>
                    <td ><?php echo $value->partnumber ?></td>
                    <td >
                        <?php
                            $qty =substr($value->qty,-3);
                            if ($qty > 0 )
                                echo $value->qty;
                            else
                                echo (int) $value->qty
                        ?>
                    </td>
                    <?php
                        if($value->qty_retur == null){
                            $qty_retur = "-";
                        }else{
                            $qty_retur = $value->qty_retur;
                        }
                    ?>
                    <td>{{ $qty_retur }}</td>
                    <td>{{ $value->kode_satuan }}</td>
                    <?php
                    $item_length = strlen($value->produk->nama_produk) ;
                    if ($item_length > 26){
                        $limit_row += 1;
                    }
                    ?>

                </tr>
            <?php endforeach ?>
            
            <?php

            $total_row = count($pemakaiandetail);
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
    <p style="font-size: 10pt;">*) Note: <?=$pemakaian->deskripsi?></p>
    <br>
    <table width="100%" style="font-size:10pt; text-align:center;padding:0px; margin:0px; border-collapse:collapse" border="0">
        <tr style="padding:0px; margin:0px">
            <td width="30%">Dibuat oleh,</td>
            <td width="30%">Pemakai,</td>
        </tr>
        <tr style="padding:0px; margin:0px">
            <?php
                $dibuat = $no_pemakaian.'-dibuat'.'.png';
                $diterima = $no_pemakaian.'-diterima'.'.png';

                $cekdibuat = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pemakaian/'.$dibuat;
                $cekditerima = 'https://103.109.168.194:8283/apps/files_sharing/publicpreview/owHaqpGsxoL7FaR?file=/02NPK0122000018-diterima.png&fileId=5961&x=1366&y=768&a=true';
            ?>
            <?php if (file_exists($cekdibuat)) { ?>
                <td><img src="{{ $cekdibuat }}" alt="" height="70px" width="90px" align="center"></td>
            <?php }else { ?>
                <td><br><br><br></td>
            <?php } ?>
            
            <?php if (file_exists($cekditerima)) { ?>
                <td><img src="{{ $cekditerima }}" alt="" height="70px" width="90px" align="center"></td>
            <?php }else { ?>
                <td><br><br><br></td>
            <?php } ?>
        </tr>
        <tr style="padding:0px; margin:0px">
            <td><?php echo $user; ?></td>
            <td><?php echo $pemakai; ?></td>
        </tr>
    </table>
    </section>
</div>


</body>
</html>