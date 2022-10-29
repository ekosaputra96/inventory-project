<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <br>
<?php use App\Models\Produk; ?>
    <title>Request Pembelian ~ {{ $reqpembelian->no_request }}</title>
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
        

        .header {
            padding-left: 12px;
            margin-right: 0px;
        }

        .left {
            padding-left: 7px;
            float: left;
        }

        .right {
            padding-right: 16px;
            float: right;
        }

        .clearfix {
            overflow: auto;
        }

        .content {
            padding-top: 50px;
            padding-left: 12px
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
    <h1>Request Pembelian</h1>
</div>

<div class="header">
    <div class="left">
        <table width="40%" style="  font-size: 10pt" border="0">
            <tr >
                <td style="width: 100px">No Request</td>
                <td style="width: 10px">:</td>
                <td>{{ $reqpembelian->no_request }}</td>
            </tr>
            <tr>
                <td >Lokasi</td>
                <td>:</td>
                <td style="font-size: 9pt">{{ $reqpembelian->kode_lokasi }}</td>
            </tr>
        </table>
    </div>
    <div class="right">
        <table width="51%" style="font-size: 10pt; padding-left: 220px" border="0">
            <tr >
                <td style="width: 100px">Tgl Request</td>
                <td style="width: 10px">:</td>
                <td>{{ $reqpembelian->tgl_request}}</td>
            </tr>

        </table>
    </div>
</div>

<div class="content">
    <section class="list-item">
        <table class="grid" style="font-size: 10pt; width: 850px;" border="0" >
            <thead>
                <tr><td colspan="8"><hr></th></tr>
                <tr >
                    <td style="text-align: left; padding-left: 15px;">No</th>
                    <td style="text-align: left;">Produk</th>
                    <td style="text-align: right;">Qty</th>
                    <td style="text-align: right;">Qty PO</th>
                    <td style="text-align: right;">Selisih</th>
                </tr>
                <tr><td colspan="8"><hr></th></tr>
            </thead>
            <hr style="width:890px; padding-right: -57px; margin-right: 20px;">
            <tbody>
            <?php $key = 0; ?>
            <?php foreach ($reqdetail as $key => $value): ?>
                <tr>
                    <td style="text-align: left; padding-left: 18px;"><?php echo $key+1 ?></td>
                    <td style="text-align: left;"><?php echo $value->produk->nama_produk ?></td>
                    <td style="text-align: right; padding-right: 12px;"><?php echo $value->qty ?></td>
                    <td style="text-align: right; padding-right: 12px;"><?php echo $value->qty_po ?></td>
                    <td style="text-align: right; padding-right: 12px;"><?php echo ($value->qty_po - $value->qty)?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        <div class="catatan" style="float: left; padding-top:5px; padding-left: 20px">
            <p>*) Note: <?=$reqpembelian->keterangan?></p>
        </div>
    </section>
</div>

    
<!--<div class="footer" style="font-size: 10pt;padding-top: 2cm; padding-left: 20px">-->
<!--    <div class="tgl">-->
<!--        Palembang, <?php echo $date_now;?>-->
<!--    </div>-->
<!--        <table width="100%" style="font-size:10pt; text-align:center;padding:0px; margin:0px; border-collapse:collapse" border="0">-->
<!--            <tr style="padding:0px; margin:0px">-->
<!--                <td width="20%">Dibuat oleh,</td>-->
<!--                <td width="20%"></td>-->
<!--                <td width="20%"></td>-->
<!--                <td width="40%">Diketahui,</td>-->
<!--            </tr>-->
<!--            <tr style="padding:0px; margin:0px">-->
<!--                <td><br><br><br></td>-->
<!--                <td><br><br><br></td>-->
<!--                <td><br><br><br></td>-->
<!--                <td><br><br><br></td>-->
<!--            </tr>-->
<!--            <tr style="padding:0px; margin:0px">-->
<!--                <td><b><u><?php echo $ttd; ?></u></b></td>-->
<!--                <td></td>-->
<!--                <td></td>-->
<!--                <td></td>-->
<!--            </tr>-->
<!--            <tr style="padding:0px; margin:0px">-->
<!--                <td></td>-->
<!--                <td></td>-->
<!--                <td></td>-->
<!--                <td></td>-->
<!--            </tr>-->
<!--        </table>-->
<!--    <div class="catatan" style="float: left">-->
<!--        <p>*) Note: <?=$reqpembelian->keterangan?></p>-->
<!--    </div>-->
<!--</div>-->

</body>
</html>