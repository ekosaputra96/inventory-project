<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <?php
        use App\Models\Pemakaian;
        use App\Models\Alat;
    ?>
    <?php if ($asetalat != 'SEMUA') { ?>
        <title>MAINTENANCE REPORT {{ $asetalat }}</title>
    <?php } else { ?>
        <title>MAINTENANCE REPORT</title>
    <?php } ?>
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
                <?php if ($asetalat != 'SEMUA') { ?>
                    <h1>MAINTENANCE REPORT</h1>
                    <p>Ketegori Produk <?php echo $kategori; ?> | Periode: <?php echo ($tanggal_awal) ?> s.d <?php echo ($tanggal_akhir) ?></p>
                <?php }else { ?>
                    <h1>MAINTENANCE REPORT</h1>
                    <p>Ketegori Produk <?php echo $kategori; ?> | Periode: <?php echo ($tanggal_awal) ?> s.d <?php echo ($tanggal_akhir) ?></p>
                <?php } ?>
            <?php } 
                else{?>
                <?php if ($asetalat != 'SEMUA'){ ?>
                    <h1>MAINTENANCE REPORT</h1>
                    <p>Periode: <?php echo ($tanggal_awal) ?> s.d <?php echo ($tanggal_akhir) ?></p>
                <?php } else { ?>
                    <h1>MAINTENANCE REPORT</h1>
                    <p>Periode: <?php echo ($tanggal_awal) ?> s.d <?php echo ($tanggal_akhir) ?></p>
                <?php } ?>
            <?php } ?>
    </div>
<div class="fixed-title" style="float: left; font-size: 6pt;">
<p align="left">
    <?php $alat = Alat::on($konek)->where('no_asset_alat', $asetalat)->first(); ?>
    Nama Alat&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;<?php echo $alat->nama_alat; ?><br>
    Type Alat&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;<?php echo $alat->type; ?><br>
    Kode Aset&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;<?php echo $asetalat; ?>
</p>
</div>

<?php
$grandtotalqty = 0;
$grandtotaljumlah = 0;
?>
<br><br><br>
    <table class="grid1" style="margin-bottom: 25px;width: 100%; font-size: 11px">
        <thead>
        <tr style="background-color: #e6f2ff">
            <!-- <th>No</th>
            <th>No. Pemakaian</th> -->
            <th style="width: 50px">Tanggal</th>
            <th style="width: 300px">Description</th>
            <th style="width: 50px">HM</th>
            <th style="width: 50px">KM</th>
            <!-- <th>Status</th>
            <th>Nama Alat</th> -->
        <?php if ($asetalat == 'SEMUA') { ?>
            <th style="width: 120px">No Asset Alat</th>
        <?php } ?>
            <th>Partnumber</th>
            <th style="width: 30px">Qty</th>
            <th style="width: 50px">Satuan</th>
            <th style="width: 200px">Remark</th>
        </tr>
        </thead>
<?php $tgl1 = null; ?>
<?php $tgl2 = null; ?>
<?php $alat1 = null; ?>
<?php $alat2 = null; ?>
        
        <tbody>
            @foreach($pemakaiandetail as $key => $row)
            <tr class="border">
            <?php 
                $tgl2 = $row->tanggal_pemakaian;
            ?>
        <?php if ($tgl1 == null) { ?>
                <?php 
                    $tgl1 = $row->tanggal_pemakaian;
                ?>
                <!-- <td><?php echo $key+1 ?></td>
                <td><?php echo $row->no_pemakaian?></td> -->
                <td style="width: 100px"><?php echo $tgl1 ?></td>
                <td><?php echo $row->nama_produk?></td>
                <td style="width: 50px"><?php echo $row->hmkm?></td>
                <td style="width: 50px"><?php echo $row->km?></td>
                <!-- <td><?php echo $row->status?></td>
                <td><?php echo $row->nama_alat?></td> -->
            <?php if ($asetalat == 'SEMUA') { ?>
                <td style="width: 120px"><?php echo $row->no_asset_alat?></td>
            <?php } ?>
                <td style="width: 80px"><?php echo $row->partnumber?></td>
                <!-- <td><?php echo $row->keterangan?></td> -->
                <td><?php echo $row->qty?></td>
                <td><?php echo $row->kode_satuan?></td> 
                <td style="width: 50px"><?php echo $row->keterangan?></td>
        <?php }else if ($tgl1 == $tgl2) { ?>
                <td style="width: 100px"></td>
                <td><?php echo $row->nama_produk?></td>
                <td style="width: 50px"><?php echo $row->hmkm?></td>
                <td style="width: 50px"><?php echo $row->km?></td>
                <!-- <td><?php echo $row->status?></td>
                <td><?php echo $row->nama_alat?></td> -->
            <?php if ($asetalat == 'SEMUA') { ?>
                <td style="width: 120px"><?php echo $row->no_asset_alat?></td>
            <?php } ?>
                <td style="width: 80px"><?php echo $row->partnumber?></td>
                <td><?php echo $row->qty?></td>
                <td><?php echo $row->kode_satuan?></td>
                <td style="width: 50px"><?php echo $row->keterangan?></td>
        <?php } else { ?>
            <?php 
                $tgl1 = $row->tanggal_pemakaian;
            ?>
                <td style="width: 100px"><?php echo $tgl2 ?></td>
                <td><?php echo $row->nama_produk?></td>
                <td style="width: 50px"><?php echo $row->hmkm?></td>
                <td style="width: 50px"><?php echo $row->km?></td>
            <?php if ($asetalat == 'SEMUA') { ?>
                <td style="width: 120px"><?php echo $row->no_asset_alat?></td>
            <?php } ?>
                <td style="width: 80px"><?php echo $row->partnumber?></td>
                <td><?php echo $row->qty?></td>
                <td><?php echo $row->kode_satuan?></td>
                <td style="width: 50px"><?php echo $row->keterangan?></td>
        <?php } ?>
            </tr>

            <?php
            $subqty = 0;
            $subtotal = 0;
            $subqty += (float) $row->qty;
            // $subtotal += $row->harga * $row->qty;

            $grandtotalqty += $subqty;
            // $grandtotaljumlah += $subtotal;
            ?>

            @endforeach
        </tbody>
    </table>

</body>
</html>