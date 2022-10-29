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
            <th class="border" width="">No Opname</th>
            <th class="border" width="">Tanggal Opname</th>
            <th class="border" width="">Kode Produk</th>
            <th class="border" width="">Nama Produk</th>
            <th class="border" width="">Partnumber</th>
            <th class="border"  width="">Kode Satuan</th>
            <th class="border"  width="">Kode Kategori</th>
            <th class="border"  width="">Stok Awal</th>
            <th class="border"  width="">Qty Check 1</th>
            <th class="border"  width="">Qty Check 2</th>
            <th class="border"  width="">Qty Final</th>
            <th class="border"  width="">Qty Selisih</th>
            <th class="border" width="">Amount Selisih</th>
            <th class="border" width="">Hpp</th>
        </tr>
        </thead>

        <?php foreach ($data as $key => $row) : ?>
        <tbody>

        ?>

            <tr class="border">
                <td class="border"><?php echo $key+1 ?></td>
                <td class="border" align="left"><?php echo $row->no_opname?></td>
                <td class="border" align="left"><?php echo $row->tanggal_opname?></td>
                <td class="border" align="left"><?php echo $row->kode_produk?></td>
                <td class="border" align="left"><?php echo $row->nama_produk?></td>
                <td class="border" align="left"><?php echo $row->partnumber?></td>
                <td class="border" align="center"><?php echo $row->kode_satuan?></td>
                <td class="border" align="center"><?php echo $row->kode_kategori?></td>
                <td class="border" align="center"><?php echo $row->stok?></td>
                <td class="border" align="center"><?php echo $row->qty_checker1?></td>
                <td class="border" align="center"><?php echo $row->qty_checker2?></td>
                <td class="border" align="center"><?php echo $row->qty_checker3?></td>
                <td class="border" align="center"><?php echo $row->stock_opname?></td>
                <td class="border" align="right"><?php echo $row->amount_opname ?></td>
                <td class="border" align="right"><?php echo $row->hpp ?></td>
            </tr>

        </tbody>
        <?php endforeach; ?>

        <tfoot>
        
        </tfoot>

    </table>
<hr>
</body>
</html>