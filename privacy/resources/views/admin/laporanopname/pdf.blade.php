<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    
    <title>LAPORAN DATA OPNAME</title>
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
            <h1>LAPORAN OPNAME</h1>

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
            <th>No Opname</th>
            <th>Tanggal Opname</th>
            <th>Kode Produk</th>
            <th>Nama Produk</th>
            <th>Partnumber</th>
            <th>Kode Satuan</th>
            <th>Kode Kategori</th>
            <th>Stok Awal</th>
            <th>Qty Checker1</th>
            <th>Qty Checker2</th>
            <th>Qty Final</th>
            <th>Qty Selisih</th>
            <th>Amount Selisih</th>
            <th>Hpp</th>
        </tr>
        </thead>

        <tbody>
            @foreach($opnamedetail as $key => $row)

            <tr class="border">
                <td><?php echo $key+1 ?></td>
                <td><?php echo $row->no_opname?></td>
                <td><?php echo $row->tanggal_opname?></td>
                <td><?php echo $row->kode_produk?></td>
                <td><?php echo $row->nama_produk?></td>
                <td><?php echo $row->partnumber?></td>
                <td><?php echo $row->kode_satuan?></td>
                <td><?php echo $row->kode_kategori?></td>
                <td><?php echo $row->stok?></td>
                <td><?php echo $row->qty_checker1?></td>
                <td><?php echo $row->qty_checker2?></td>
                <td><?php echo $row->qty_checker3?></td>
                <td><?php echo number_format($row->stock_opname,'0',',','.') ?></td>
                <td><?php echo number_format($row->amount_opname,'0',',','.') ?></td>
                <td><?php echo number_format($row->hpp,'0',',','.') ?></td>
            </tr>

            <?php
            $subqty = 0;
            $subtotal = 0;
            $subqty += (float) $row->stock_opname;
            $subtotal += ($row->amount_opname);

            $grandtotalqty += $subqty;
            $grandtotaljumlah += $subtotal;
            ?>

            @endforeach
        </tbody>

        <tfoot>
        <tr style="background-color: #F5D2D2">
            <td colspan="12" style="font-weight: bold; text-align: center">Total</td>
            <td><?php echo number_format((float)$grandtotalqty, 0, ',', '.');;?></td>
            <td>&nbsp;<?php echo number_format($grandtotaljumlah,'0',',','.');?></td>
            <td></td>
        </tr>
        </tfoot>

    </table>

    <?php
        if ($format_ttd != 1) {?>
            <br><br>
            <table width="100%" style="font-size:10pt; text-align: center; bottom: 0">
                <tr>
                    <td width="30%">Dibuat,</td>
                    <td width="30%">Team Opname,</td>
                    <td width="30%">Disetujui,</td>
                    <td width="30%">Diketahui,</td>
                    <td width="30%">Dikonfirmasi,</td>
                </tr>
                <tr><td colspan="3"><br><br><br></td></tr>
                <tr>
                    <td><?php echo $ttd; ?></td>
                    <td><?php echo " "; ?></td>
                    <td><?php echo $limit3->mengetahui; ?></td>
                    <td><?php echo $limit2->mengetahui; ?></td>
                    <td>Logistik</td>
                </tr>
            </table>
        <?php } 
        else{?>
            @permission('read-hpp')
            <div class="page_break"></div>
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
                    <td width="30%">Team Opname,</td>
                    <td width="30%">Disetujui,</td>
                    <td width="30%">Diketahui,</td>
                    <td width="30%">Dikonfirmasi,</td>
                </tr>
                <tr><td colspan="3"><br><br><br></td></tr>
                <tr>
                    <td><?php echo $ttd; ?></td>
                    <td><?php echo " "; ?></td>
                    <td><?php echo $limit3->mengetahui; ?></td>
                    <td><?php echo $limit2->mengetahui; ?></td>
                    <td>Logistik</td>
                </tr>
            </table>
    <?php } ?>
</body>
</html>