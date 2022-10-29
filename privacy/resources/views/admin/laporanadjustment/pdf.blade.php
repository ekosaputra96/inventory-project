<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    
    <title>LAPORAN DATA ADJUSTMENT BARANG</title>
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
            <?php if ($lokasi2 != 'SEMUA') { ?>
                <?php
                    if ($kategori != 'SEMUA') {?>
                        <h1>LAPORAN ADJUSTMENT BARANG KATEGORI <?php echo $kategori; ?> LOKASI <?php echo $lokasi2; ?></h1>
                <?php } 
                    else{?>
                        <h1>LAPORAN ADJUSTMENT BARANG LOKASI <?php echo $lokasi2; ?></h1>
                <?php } ?>
            <?php }else { ?>
                <?php
                    if ($kategori != 'SEMUA') {?>
                        <h1>LAPORAN ADJUSTMENT BARANG KATEGORI <?php echo $kategori; ?></h1>
                <?php } 
                    else{?>
                        <h1>LAPORAN ADJUSTMENT BARANG</h1>
                <?php } ?>
            <?php } ?>

            <p>Periode: <?php echo ($tanggal_awal) ?> s.d <?php echo ($tanggal_akhir) ?></p>
        
    </div>

<?php
$grandtotalqty = 0;
$grandtotaljumlah = 0;
?>
    <table class="grid1" style="margin-bottom: 25px;width: 100%; font-size: 11px">
        <thead>
        <tr style="background-color: #e6f2ff">
            <th>NO</th>
            <th>No Adjustment</th>
            <th>Tanggal Transaksi</th>
            <th>Status</th>
            <th>Kode Produk</th>
            <th>Nama Produk</th>
            <th>Partnumber</th>
            <?php
                if ($kategori == 'SEMUA') {?>
                    <th>Kode Kategori</th>
            <?php } ?>
            <th>Kode Satuan</th>
            <th>Qty</th>
            @permission('read-hpp')
            <th>Harga</th>
            <th>Subtotal</th>
            @endpermission
            <?php if ($lokasi2 == 'SEMUA') { ?>
                <th>Lokasi</th>
            <?php } ?>
        </tr>
        </thead>

        
        <tbody>
            @foreach($adjustmentdetail as $key => $row)

            <tr>
                <td><?php echo $key+1 ?></td>
                <td><?php echo $row->no_penyesuaian?></td>
                <td><?php echo $row->tanggal?></td>
                <td><?php echo $row->status?></td>
                <td><?php echo $row->kode_produk?></td>
                <td><?php echo $row->nama_produk?></td>
                <td><?php echo $row->partnumber?></td>
                <?php
                    if ($kategori == 'SEMUA') {?>
                        <td>{{ $row->kode_kategori }}</td>
                <?php } ?>
                <td><?php echo $row->kode_satuan?></td>
                <td><?php echo $row->qty?></td>
                @permission('read-hpp')
                <td><?php echo number_format($row->harga,'0',',','.') ?></td>
                <td><?php echo number_format(($row->harga * $row->qty),'0',',','.') ?></td>
                @endpermission
                <?php if ($lokasi2 == 'SEMUA') { ?>
                    <td>{{ $row->kode_lokasi }}</td>
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