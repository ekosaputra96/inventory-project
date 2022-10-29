<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        use App\Models\Pemakaian;
        use App\Models\user_history;
    ?>
	<style> 
        
     @page {
            border: solid 1px #0b93d5;

        }

        .title {
            margin-top: 0.5cm;
        }
        .title h1 {
            text-align: left;
            font-size: 14pt;
            
        }
        

        .header {
            margin-left: 50px;
            margin-right: 0px;
            /*font-size: 10pt;*/
            padding-top: 10px;
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
                margin-left: 10px;
            padding-top: 10px
        }
        .catatan {
            font-size: 10pt;
        }

        footer {
                position: fixed; 
                top: 19cm; 
                left: 0cm; 
                right: 0cm;
                height: 2cm;
            }

        /* Table desain*/
        table.grid {
            width: 100%;
        }
</style>
</head>
<body>
<div class="fixed-header">
        <div style="float: left">
            <img src="{{ asset('css/logo_gui.png') }}" alt="" height="25px" width="25px" align="left">
            <p id="color" style="font-size: 8pt;" align="left"><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo ($nama2) ?></b><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lokasi: <?php echo ($nama) ?></p>
        </div>

        <div id="header">
            <p class="page" style="float: right; font-size: 9pt;"><b>Tanggal/waktu :</b> <?php echo $dt ?>
        </div>

        <br>
            <?php
                if ($kategori != 'SEMUA') {?>
                    <h1>LAPORAN PEMAKAIAN BARANG KATEGORI <?php echo $kategori; ?></h1>
            <?php } 
                else{?>
                    <h1 style="padding-left:10px;">LAPORAN PEMAKAIAN BARANG</h1>
            <?php } ?>

            <p>Periode: <?php echo ($awal) ?> s.d <?php echo ($akhir) ?></p>
        
    </div>
    <br>
    <br>
	<table class="table_content" style="margin-bottom: 25px;width: 100%;">
        <thead>
        <tr class="border" style="background-color: #e6f2ff">
            <th class="border" width="">NO</th>
            <th class="border" width="">No. Pemakaian</th>
            <th class="border" width="">No. JO</th>
            <th class="border" width="">No. WO</th>
            <th class="border" width="">Tanggal Transaksi</th>
            <th class="border" width="">Status</th>
            <th class="border" width="">Nama Alat</th>
            <th class="border" width="">No Asset Alat</th>
            <th class="border" width="">Pemakai</th>
        <?php if ($produk == 'true' || $semua == 'true') { ?>
            <th class="border" width="">Kode Produk</th>
        <?php } ?>
        <?php if ($namaproduk == 'true' || $semua == 'true') { ?>
            <th class="border" width="">Nama Produk</th>
        <?php } ?>
        <?php if ($partnumber == 'true' || $semua == 'true') { ?>
            <th class="border" width="">Partnumber</th>
        <?php } ?>
        <?php if ($satuan == 'true' || $semua == 'true') { ?>
            <th class="border"  width="">Kode Satuan</th>
        <?php } ?>
        <?php if ($kategori == 'true' || $semua == 'true') { ?>
            <th class="border"  width="">Kode Kategori</th>
        <?php } ?>
            <th class="border"  width="">Qty</th>
            @permission('read-hpp')
        <?php if ($harga == 'true' || $semua == 'true') { ?>
            <th class="border" width="">Harga</th>
        <?php } ?>
        <?php if ($subtotal == 'true' || $semua == 'true') { ?>
            <th class="border" width="">Subtotal</th>
        <?php } ?>
            @endpermission
            <th class="border" width="">Kode Lokasi</th>
            <th class="border" width="">HM</th>
            <th class="border" width="">KM</th>
            <th class="border" width="">Created By</th>
            <th class="border" width="">Posted By</th>
        </tr>
        </thead>

        <?php foreach ($data as $key => $row) : ?>
        <tbody>

        ?>
        <?php if ($row->qty_retur != $row->qty) { ?>
            <tr class="border">
                <td class="border"><?php echo $key+1 ?></td>
                <td class="border" align="left"><?php echo $row->no_pemakaian?></td>
                <td class="border" align="left"><?php echo $row->no_jo?></td>
                <td class="border" align="left"><?php echo $row->no_wo?></td>
                <td class="border" align="left"><?php echo $row->tanggal_pemakaian?></td>
                <td class="border" align="left"><?php echo $row->status?></td>
                <td class="border" align="left"><?php echo $row->nama_alat?></td>
                <td class="border" align="left"><?php echo $row->no_asset_alat?></td>
                <?php
                    if (stripos($row->pemakai, '&') !== FALSE) {
                        $ket = stripos($row->pemakai, '&');
                        $pemakai = substr_replace($row->pemakai, '&amp;', $ket, 1);
                    }else {
                        $pemakai = $row->pemakai;
                    }
                ?>
                <td class="border" align="left"><?php echo $pemakai?></td>
            <?php if ($produk == 'true' || $semua == 'true') { ?>
                <td class="border" align="left"><?php echo $row->kode_produk?></td>
            <?php } ?>
            <?php if ($namaproduk == 'true' || $semua == 'true') { ?>
                <td class="border" align="left"><?php echo $row->nama_produk?></td>
            <?php } ?>
            <?php if ($partnumber == 'true' || $semua == 'true') { ?>
                <?php
                    if (stripos($row->partnumber, '&') !== FALSE) {
                        $ket = stripos($row->partnumber, '&');
                        $parto = substr_replace($row->partnumber, '&amp;', $ket, 1);
                    }else {
                        $parto = $row->partnumber;
                    }
                ?>
                <td class="border" align="left"><?php echo $parto ?></td>
            <?php } ?>
            <?php if ($satuan == 'true' || $semua == 'true') { ?>
                <td class="border" align="center"><?php echo $row->kode_satuan?></td>
            <?php } ?>
            <?php if ($kategori == 'true' || $semua == 'true') { ?>
                <td class="border" align="center"><?php echo $row->kode_kategori?></td>
            <?php } ?>
                <td class="border" align="center"><?php echo $row->qty - $row->qty_retur ?></td>
        @permission('read-excelharga')
            <?php if ($harga == 'true' || $semua == 'true') { ?>
                <td class="border" align="right"><?php echo $row->harga ?></td>
            <?php } ?>
            <?php if ($subtotal == 'true' || $semua == 'true') { ?>
                <td class="border" align="right"><?php echo $total = $row->harga * ($row->qty - $row->qty_retur) ?></td>
            <?php } ?>
        @endpermission
                <td class="border" align="right"><?php echo $row->kode_lokasi ?></td>
                <td class="border" align="right"><?php echo $row->hmkm ?></td>
                <td class="border" align="right"><?php echo $row->km ?></td>
                <td class="border" align="right"><?php echo $row->created_by ?></td>
                <?php $post = user_history::on($konek)->where('aksi','like','%'.$row->no_pemakaian.'%')->where('aksi','like','Post No.%')->orderBy('created_at','desc')->first(); ?>
                <td class="border" align="right"><?php echo $post->nama; ?></td>
            </tr>
        <?php } ?>
            <?php
            $subqty = 0;
            $subtotal = 0;
            $subqty += (float) $row->qty;
            $subtotal += $row->harga * $row->qty;

            //$grandtotalqty += $subqty;
            //$grandtotaljumlah += $subtotal;
            ?>

        </tbody>
        <?php endforeach; ?>

        <tfoot>
        
        </tfoot>

    </table>
<hr>
</body>
</html>