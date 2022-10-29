<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
use App\Models\Produk;
use App\Models\Alat;
use App\Models\Mobil;
use App\Models\Kapal;
?>
    <title>LAPORAN PEMAKAIAN PRODUK</title>
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
        <?php $prod = Produk::on($konek)->find($kode_produk); ?>
            <h1>LAPORAN PEMAKAIAN PRODUK [ <?php echo $prod->nama_produk; ?> ]</h1>
            <p>Periode: <?php echo ($tanggal_awal) ?> s.d <?php echo ($tanggal_akhir) ?></p>
    </div>
    
<?php
$grandtotalqty = 0;
$grandtotaljumlah = 0;
?>
    <table class="grid1" style="margin-bottom: 25px;width: 100%; font-size: 11px">
        <thead>
        <tr style="background-color: #e6f2ff">
            <th>Tanggal Pemakaian</th>
            <th>Nama - No.Asset Alat/Mobil/Kapal/Keterangan</th>
            <th>No Pemakaian</th>
            <th>Qty</th>
        </tr>
        </thead>
<?php
$tgl1 = null;
$tgl2 = null;
?>
        <tbody>
            @foreach($pemakaianproduk as $key => $row)
            <?php
                if ($tipe_pemakaian == 'alat'){
                    $alat = Alat::on($konek)->find($row->kode_alat);
                }else if ($tipe_pemakaian == 'mobil'){
                    $mobil = Mobil::on($konek)->find($row->kode_mobil);
                }else if ($tipe_pemakaian == 'kapal'){
                    $kapal = Kapal::on($konek)->find($row->kode_kapal);
                }else if ($tipe_pemakaian == 'Other'){
                    
                }
            ?>
            <tr>
                <?php $tgl2 = $row->tanggal_pemakaian; ?>
                <?php if ($tgl1 == null) { ?>
                    <?php $tgl1 = $row->tanggal_pemakaian; ?>
                    <td><?php echo $tgl1 ?></td>
                <?php } else if ($tgl1 == $tgl2) { ?>
                    <td></td>
                <?php }else { ?>
                    <?php $tgl1 = $row->tanggal_pemakaian; ?>
                    <td><?php echo $tgl2 ?></td>
                <?php } ?>
                
                <?php if ($tipe_pemakaian == 'alat'){ ?>
                    <td><?php echo $alat->nama_alat.' / '.$alat->no_asset_alat ?></td>
                <?php }else if ($tipe_pemakaian == 'mobil'){ ?>
                    <td><?php echo $mobil->nopol.' / '.$mobil->no_asset_mobil ?></td>
                <?php }else if ($tipe_pemakaian == 'kapal'){ ?>
                    <td><?php echo $kapal->nama_kapal.' / '.$kapal->no_asset_kapal ?></td>
                <?php }else if ($tipe_pemakaian == 'Other'){ ?>
                    <td><?php echo $row->deskripsi ?></td>
                <?php } ?>
                
                <td><?php echo $row->no_pemakaian ?></td>
                <td><?php echo $row->qty ?></td>
            </tr>
            <?php $grandtotalqty += $row->qty; ?>
            @endforeach
        </tbody>

        <tfoot>
        <tr style="background-color: #F5D2D2">
            <td colspan="3" style="font-weight: bold; text-align: center">&nbsp;</td>
            <td><?php echo number_format($grandtotalqty); ?></td>
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