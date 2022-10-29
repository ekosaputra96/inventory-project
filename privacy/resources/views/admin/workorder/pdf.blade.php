<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <br>
<?php use App\Models\Produk; ?>
    <title>WORK ORDER (WO) ~ {{ $request }}</title>
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
            padding-top: 100px;
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
    <h1>WORK ORDER (WO)</h1>
</div>

<div class="header">
    <div class="left">
        <table width="40%" style="  font-size: 10pt" border="0">
            <tr >
                <td style="width: 100px">No WO</td>
                <td style="width: 10px">:</td>
                <td>{{ $work->no_wo }}</td>
            </tr>
            <tr >
                <td style="width: 100px">Date In</td>
                <td style="width: 10px">:</td>
                <td>{{ $work->date_in }}</td>
            </tr>
            <tr>
                <td >Lokasi</td>
                <td>:</td>
                <td style="font-size: 9pt">{{ $work->kode_lokasi }}</td>
            </tr>
        </table>
    </div>
    <div class="right">
        <table width="51%" style="font-size: 10pt; padding-left: 150px" border="0">
            <tr >
                <td style="width: 100px">No Reff</td>
                <td style="width: 10px">:</td>
                <td>{{ $work->no_reff}}</td>
            </tr>
            <tr>
                <td style="width: 100px">Date Finish</td>
                <td style="width: 10px">:</td>
                <td>{{ $work->date_finish }}</td>
            </tr>
            <tr>
                <td>Kode Tagging</td>
                <td>:</td>
                <td>{{ $work->no_asset_alat }}</td>
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
                    <td style="text-align: left;">Type</th>
                    <td style="text-align: left;">Produk</th>
                    <td style="text-align: left;">Partnumber</th>
                    <td style="text-align: right;">Qty</th>
                </tr>
                <tr><td colspan="8"><hr></th></tr>
            </thead>
            <hr style="width:890px; padding-right: -57px; margin-right: 20px;">
            <tbody>
            <?php $key = 0; ?>
            <?php foreach ($workdetail as $key => $value): ?>
                <tr >
                    <td style="text-align: left; padding-left: 18px;"><?php echo $key+1 ?></td>
                    <?php $prod = Produk::on($konek)->find($value->kode_produk); ?>
                    <td style="text-align: left;"><?php echo $value->type ?></td>
                    <?php if ($prod == "0"){ ?>
                        <td style="text-align: left;"><?php echo $prod->nama_produk ?></td>
                        <td style="text-align: left;"><?php echo $prod->partnumber ?></td>
                    <?php } else{?>
                        <td style="text-align: left;"><?php echo $value->nama_produk ?></td>
                        <td style="text-align: left;"><?php echo $value->partnumber ?></td>
                    <?php } ?>
                    <td style="text-align: right; padding-right: 12px;"><?php echo $value->qty ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </section>
</div>

<br>
<div class="footer" style="font-size: 10pt;padding-top: 2cm; padding-left: 12px">
    <div class="tgl">
        Palembang, <?php echo date_format($date,'d F Y');?>
    </div>
        <table width="100%" style="font-size:10pt; text-align:center;padding:0px; margin:0px; border-collapse:collapse" border="0">
            <tr style="padding:0px; margin:0px">
                <td width="20%">Dibuat oleh,</td>
                <td width="20%"></td>
                <td width="20%"></td>
                <td width="40%">Diketahui,</td>
            </tr>
            <tr style="padding:0px; margin:0px">
                <td><br><br><br></td>
                <td><br><br><br></td>
                <td><br><br><br></td>
                <td><br><br><br></td>
            </tr>
            <tr style="padding:0px; margin:0px">
                <td><b><u><?php echo $ttd; ?></u></b></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr style="padding:0px; margin:0px">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>
    <div class="catatan" style="float: left">
        <p>*) Note: <?=$work->keterangan?></p>
    </div>
</div>

</body>
</html>