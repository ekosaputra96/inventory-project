<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <br>
    <title>Laporan Transfer Out ~ {{ $transfer->no_transfer }}</title>
    <style>
        @page {
            border: solid 1px #0b93d5;
            width: 24.13cm;
            height: 27.94cm;
            font-family: 'Courier';
            font-weight: bold;
            margin-right: 0.5cm;
            margin-left: 0.5cm;
            
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
            padding-top:3mm;
            padding-bottom:3mm;
        }

        table.grid tr td{
            padding-left:2mm;
            padding-right:2mm;
        }
        .list-item {
            height: 2.1in;
            margin: 0px;
        }

    </style>

</head>
<body>

<div class="left">
	<table style="padding-left:7px; font-size:10pt">
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
    <h1>TRANSFER OUT (TO) BARANG</h1>
</div>

<div class="header">
    <div class="left" style="padding-left:10px;">
        <table width="50%" style="font-size: 10pt" border="0">
            <tr>
                <td style="width: 110px">No. TO</td>
                <td style="width: 10px">:</td>
                <td>{{ $transfer->no_transfer }}</td>
            </tr>
            <tr>
                <td>Pengirim</td>
                <td>:</td>
                <td>{{ $nama_company }}</td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td>:</td>
                <td>{{ $keterangan }}</td>
            </tr>
        </table>
    </div>
    <div class="right">
        <table width="50%" style="padding-left:8em; font-size: 10pt" border="0">
            <tr>
                <td style="width: 110px">Penerima</td>
                <td style="width: 10px">:</td>
                <td>{{ $transfer->transfer_tujuan }}</td>
            </tr>
            <tr>
                <td>Tanggal TO</td>
                <td>:</td>
                <td>{{ $tgl }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>:</td>
                <td>{{ $transfer->status }}</td>
            </tr>
            <tr>
                <td>No NPPB</td>
                <td>:</td>
                <td>{{ $transfer->no_memo }}</td>
            </tr>

        </table>
    </div>
</div>
<br><br>
<div class="content">
    <section class="list-item">
        <table class="grid" style="font-size: 10pt; width: 880px;" border="0" >
            <thead>
            <tr><td colspan="8"><hr></th></tr>
            <tr >
                <td style="text-align: left; padding-left: 15px;">No.</th>
                <td style="text-align: left;">Nama Item</th>
                <td style="text-align: right;">Partnumber</th>
                <td style="text-align: right;">Qty</th>
                <td style="text-align: right;">Satuan</th>
            </tr>
            <tr><td colspan="8"><hr></th></tr>
            </thead>
            <hr style="width:890px; padding-right: -57px; margin-right: 18px;">
            <tbody>
            <?php $subtotal = 0 ; $limit_row = 0?>
            <?php foreach ($transferdetail as $key => $value): ?>
                <tr >
                    <td style="text-align: left; padding-left: 18px;"><?php echo $key+1 ?></td>
                    <td style="text-align: left;"><?php echo $value->produk->nama_produk ?></td>
                    <td style="text-align: right; padding-right: 12px;"><?php echo $value->partnumber ?></td>
                    <td style="text-align: right; padding-right: 12px;">
                        <?php
                            $qty =substr($value->qty,-3);
                            if ($qty > 0 )
                                echo $value->qty;
                            else
                                echo (int) $value->qty
                        ?>
                    </td>
                    <td style="text-align: right;">{{ $value->kode_satuan }}</td>
                    <?php
                    $item_length = strlen($value->produk->nama_produk) ;
                    if ($item_length > 26){
                        $limit_row += 1;
                    }
                    ?>

                </tr>
            <?php endforeach ?>
            
            <?php

            $total_row = count($transferdetail);
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
        
    <br><br>
    <table width="100%" style="font-size:10pt; text-align:center;padding:0px; margin:0px; border-collapse:collapse" border="0">
        <tr style="padding:0px; margin:0px">
            <td width="30%">Dibuat oleh,</td>
        </tr>
        <tr style="padding:0px; margin:0px"><td colspan="3"><br><br><br></td></tr>
        <tr style="padding:0px; margin:0px">
            <td><?php echo $user; ?></td>
        </tr>
    </table>

    </section>
</div>




</body>
</html>