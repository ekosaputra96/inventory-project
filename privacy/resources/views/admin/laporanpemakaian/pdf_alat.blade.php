<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <?php
        use App\Models\Pemakaian;
    ?>
    <?php if ($asetalat != 'SEMUA') { ?>
        <title>LAPORAN DATA PERAWATAN ASET {{ $asetalat }}</title>
    <?php } else { ?>
        <title>LAPORAN DATA PERAWATAN SEMUA ASET</title>
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
                    <h1>LAPORAN PERAWATAN ALAT NO ASET <?php echo $asetalat; ?></h1>
                    <p>Ketegori Produk <?php echo $kategori; ?> | Periode: <?php echo ($tanggal_awal) ?> s.d <?php echo ($tanggal_akhir) ?></p>
                <?php }else { ?>
                    <h1>LAPORAN PERAWATAN ALAT</h1>
                    <p>Ketegori Produk <?php echo $kategori; ?> | Periode: <?php echo ($tanggal_awal) ?> s.d <?php echo ($tanggal_akhir) ?></p>
                <?php } ?>
            <?php } 
                else{?>
                <?php if ($asetalat != 'SEMUA'){ ?>
                    <h1>LAPORAN PERAWATAN ALAT NO ASET <?php echo $asetalat; ?></h1>
                    <p>Periode: <?php echo ($tanggal_awal) ?> s.d <?php echo ($tanggal_akhir) ?></p>
                <?php } else { ?>
                    <h1>LAPORAN PERAWATAN ALAT</h1>
                    <p>Periode: <?php echo ($tanggal_awal) ?> s.d <?php echo ($tanggal_akhir) ?></p>
                <?php } ?>
            <?php } ?>

        
    </div>

<?php
$grandtotalqty = 0;
$grandtotaljumlah = 0;
?>
    <table class="grid1" style="margin-bottom: 25px;width: 100%; font-size: 11px">
        <thead>
        <tr style="background-color: #e6f2ff">
            <!-- <th>No</th>
            <th>No. Pemakaian</th> -->
            <th style="width: 100px">Tanggal Transaksi</th>
            <th style="width: 50px">Pemakai</th>
            <th style="width: 70px">No Pemakaian</th>
            <th style="width: 50px">HM/KM</th>
            <!-- <th>Status</th>
            <th>Nama Alat</th> -->
        <?php if ($asetalat == 'SEMUA') { ?>
            <th style="width: 120px">No Asset Alat</th>
        <?php } ?>
            <th>Item Desc</th>
            <th>Alasan Pakai</th>
            <?php if ($kategori == 'SEMUA') {?>
                <th>Kategori</th>
            <?php } ?>
            <th>Qty</th>
            @permission('read-hpp')
            <th>Harga</th>
            <th>Subtotal</th>
            @endpermission
            <?php
                if ($lokasi == 'SEMUA') {?>
                    <th style="width: 60px">Kode Lokasi</th>
            <?php } ?>
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
                <td style="width: 50px"><?php echo $row->pemakai?></td>
                <td style="width: 70px"><?php echo $row->no_pemakaian?></td>
                <td style="width: 50px"><?php echo $row->hmkm?></td>
                <!-- <td><?php echo $row->status?></td>
                <td><?php echo $row->nama_alat?></td> -->
            <?php if ($asetalat == 'SEMUA') { ?>
                <td style="width: 120px"><?php echo $row->no_asset_alat?></td>
            <?php } ?>
                <!-- <td><?php echo $row->pemakai?></td> -->
                <!-- <td><?php echo $row->kode_produk?></td> -->
                <td><?php echo $row->nama_produk?></td>
                <!--<td style="width: 80px"><?php echo $row->partnumber?></td>-->
                <td><?php echo $row->keterangan?></td>
                <!-- <td><?php echo $row->kode_satuan?></td> -->
                <?php if ($kategori == 'SEMUA') {?>
                    <td><?php echo $row->kode_kategori ?></td>
                <?php } ?>
                <td><?php echo $row->qty?></td>
                @permission('read-hpp')
                <td><?php echo number_format($row->harga,'0',',','.') ?></td>
                <td><?php echo number_format($row->harga * $row->qty,'0',',','.') ?></td>
                @endpermission
                <?php
                    if ($lokasi == 'SEMUA') {?>
                        <td style="width: 60px">{{ $row->kode_lokasi }}</td>
                <?php } ?>
        <?php }else if ($tgl1 == $tgl2) { ?>
                <td style="width: 100px"></td>
                <td style="width: 50px"><?php echo $row->pemakai?></td>
                <td style="width: 70px"><?php echo $row->no_pemakaian?></td>
                <td style="width: 50px"><?php echo $row->hmkm?></td>
                <!-- <td><?php echo $row->status?></td>
                <td><?php echo $row->nama_alat?></td> -->
            <?php if ($asetalat == 'SEMUA') { ?>
                <td style="width: 120px"><?php echo $row->no_asset_alat?></td>
            <?php } ?>
                <!-- <td><?php echo $row->pemakai?></td> -->
                <!-- <td><?php echo $row->kode_produk?></td> -->
                <td><?php echo $row->nama_produk?></td>
                <!--<td style="width: 80px"><?php echo $row->partnumber?></td>-->
                <td><?php echo $row->keterangan?></td>
                <!-- <td><?php echo $row->kode_satuan?></td> -->
                <?php if ($kategori == 'SEMUA') {?>
                    <td><?php echo $row->kode_kategori ?></td>
                <?php } ?>
                <td><?php echo $row->qty?></td>
                @permission('read-hpp')
                <td><?php echo number_format($row->harga,'0',',','.') ?></td>
                <td><?php echo number_format($row->harga * $row->qty,'0',',','.') ?></td>
                @endpermission
                <?php
                    if ($lokasi == 'SEMUA') {?>
                        <td style="width: 60px">{{ $row->kode_lokasi }}</td>
                <?php } ?>
        <?php } else { ?>
            <?php 
                $tgl1 = $row->tanggal_pemakaian;
            ?>
                <td style="width: 100px"><?php echo $tgl2 ?></td>
                <td style="width: 50px"><?php echo $row->pemakai?></td>
                <td style="width: 70px"><?php echo $row->no_pemakaian?></td>
                <td style="width: 50px"><?php echo $row->hmkm?></td>
                
            <?php if ($asetalat == 'SEMUA') { ?>
                <td style="width: 120px"><?php echo $row->no_asset_alat?></td>
            <?php } ?>
                
                <td><?php echo $row->nama_produk?></td>
                <!--<td style="width: 80px"><?php echo $row->partnumber?></td>-->
                <td><?php echo $row->keterangan?></td>
                <!-- <td><?php echo $row->kode_satuan?></td> -->
                <?php if ($kategori == 'SEMUA') {?>
                    <td><?php echo $row->kode_kategori ?></td>
                <?php } ?>
                <td><?php echo $row->qty?></td>
                @permission('read-hpp')
                <td><?php echo number_format($row->harga,'0',',','.') ?></td>
                <td><?php echo number_format($row->harga * $row->qty,'0',',','.') ?></td>
                @endpermission
                <?php
                    if ($lokasi == 'SEMUA') {?>
                        <td style="width: 60px">{{ $row->kode_lokasi }}</td>
                <?php } ?>
        <?php } ?>
            </tr>

            <?php
            $subqty = 0;
            $subtotal = 0;
            $subqty += (float) $row->qty;
            $subtotal += $row->harga * $row->qty;

            $grandtotalqty += $subqty;
            $grandtotaljumlah += $subtotal;
            ?>

            @endforeach
        </tbody>
            <tfoot>
            <tr style="background-color: #F5D2D2">
                <?php
                    if ($kategori != 'SEMUA') {?>
                    <?php if ($asetalat != 'SEMUA' && $lokasi != 'SEMUA') { ?>
                        <td colspan="6" style="font-weight: bold; text-align: center">Total</td>
                    <?php }else if ($asetalat != 'SEMUA' && $lokasi == 'SEMUA') { ?>
                        <td colspan="6" style="font-weight: bold; text-align: center">Total</td>
                    <?php }else if ($asetalat == 'SEMUA' && $lokasi != 'SEMUA') { ?>
                        <td colspan="7" style="font-weight: bold; text-align: center">Total</td>
                    <?php }else { ?>
                        <td colspan="7" style="font-weight: bold; text-align: center">Total</td>
                    <?php } ?>
                <?php }else { ?>
                        <?php 
                        if ($asetalat != 'SEMUA' && $lokasi != 'SEMUA') { ?>
                            <td colspan="7" style="font-weight: bold; text-align: center">Total</td>
                        <?php } 
                        if ($asetalat != 'SEMUA' && $lokasi == 'SEMUA') { ?>
                            <td colspan="7" style="font-weight: bold; text-align: center">Total</td>
                        <?php }
                        else if ($asetalat == 'SEMUA' && $lokasi != 'SEMUA') { ?>
                            <td colspan="8" style="font-weight: bold; text-align: center">Total</td>
                        <?php }
                        else if ($asetalat == 'SEMUA' && $lokasi == 'SEMUA'){ ?>
                            <td colspan="8" style="font-weight: bold; text-align: center">Total</td>
                        <?php } ?>
                <?php } ?>
            
                <td class="border"><?php echo number_format((float)$grandtotalqty, 0, ',', '.');;?></td>
                @permission('read-hpp')
                <td></td>
                <td class="border" align="right"><?php echo number_format($grandtotaljumlah,'0',',','.');?></td>
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
            @permission('read-hpp')
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
                    <td><?php echo $limit3->mengetahui; ?></td>
                </tr>
            </table>
    <?php } ?>
</body>
</html>