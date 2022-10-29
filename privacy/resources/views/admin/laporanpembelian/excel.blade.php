<!DOCTYPE html>
<html lang="en">
<head>
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

	<table class="table_content" style="margin-bottom: 25px;width: 100%;">
        <thead>
        <tr class="border" style="background-color: #e6f2ff">
            <th class="border" width="">NO</th>
            <th class="border" width="">No Transaksi</th>
            <th class="border" width="">Tanggal Transaksi</th>
            <th class="border" width="">Status</th>
            <th class="border" width="">Nama Vendor</th>
            <th class="border" width="">Kode Produk</th>
            <th class="border" width="">Nama Produk</th>
            <?php if($jenis == 'Stock'){ ?>
                <th class="border"  width="">Kode Kategori</th>
            <?php } ?>
            <th class="border"  width="">Kode Satuan</th>
            <th class="border"  width="">Qty</th>
            <th class="border" width="">Harga</th>
            <th class="border" width="">Subtotal</th>
            <th class="border" width="">NO AP</th>
            <th class="border" width="">Kode Lokasi</th>
        </tr>
        </thead>

        <?php foreach ($data as $key => $row) : ?>
        <tbody>

        ?>

            <tr class="border">
                <td class="border"><?php echo $key+1 ?></td>
                <td class="border" align="left"><?php echo $row->no_pembelian?></td>
                <td class="border" align="left"><?php echo $row->tanggal_pembelian?></td>
                <td class="border" align="left"><?php echo $row->status?></td>
                <?php
                    if (stripos($row->vendor->nama_vendor, '&') !== FALSE) {
                        $ket = stripos($row->vendor->nama_vendor, '&');
                        $namavendor = substr_replace($row->vendor->nama_vendor, '&amp;', $ket, 1);
                    }else {
                        $namavendor = $row->vendor->nama_vendor;
                    }
                ?>
                <td class="border" align="left"><?php echo $namavendor ?></td>
                <td class="border" align="left"><?php echo $row->kode_produk?></td>
                <?php if($jenis == 'Stock'){ ?>
                    <?php
                        if (stripos($row->nama_produk, '&') !== FALSE) {
                            $ket = stripos($row->nama_produk, '&');
                            $namaproduk = substr_replace($row->nama_produk, '&amp;', $ket, 1);
                        }else {
                            $namaproduk = $row->nama_produk;
                        }
                    ?>
                    <td class="border" align="left"><?php echo $namaproduk ?></td>
                <?php }else if($jenis == 'Non-Stock'){ ?>
                <?php
                    if (stripos($row->keterangan, '&') !== FALSE) {
                        $ket = stripos($row->keterangan, '&');
                        $keterangan = substr_replace($row->keterangan, '&amp;', $ket, 1);
                    }else {
                        $keterangan = $row->keterangan;
                    }
                ?>
                    <td class="border" align="left"><?php echo $keterangan?></td>
                <?php }else { ?>
                    <td class="border" align="left"><?php echo $row->jasa->nama_item?></td>
                <?php } ?>
                <?php if($jenis == 'Stock'){ ?>
                    <td class="border" align="center"><?php echo $row->kode_kategori?></td>
                <?php } ?>
                <td class="border" align="center"><?php echo $row->kode_satuan?></td>
                <td class="border" align="center"><?php echo $row->qty?></td>
                <td class="border" align="right"><?php echo $row->harga ?></td>
                <td class="border" align="right"><?php echo $total = $row->harga * $row->qty ?></td>
                <td class="border" align="center"><?php echo $row->no_ap?></td>
                <td class="border" align="center"><?php echo $row->kode_lokasi?></td>
            </tr>

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