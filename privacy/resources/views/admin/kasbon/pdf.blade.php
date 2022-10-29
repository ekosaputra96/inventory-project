<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <br>
    <title>PERMINTAAN KASBON</title>
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
            padding-top: 120px;
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
            <h1>PERMINTAAN KASBON</h1>
        <br>

        <div class="left">
            <table width="50%" style="float: left; font-size: 10pt" border="0">
                <tr >
                    <td style="width: 115px">No PKB</td>
                    <td style="width: 10px">:</td>
                    <td>{{ $kasbon->no_pkb }}</td>
                </tr>
                <tr >
                    <td style="width: 115px">Nama Pemohon</td>
                    <td style="width: 10px">:</td>
                    <td>{{ $kasbon->nama_pemohon }}</td>
                </tr>
            </table>
        </div>
        <div class="right">
            <table style="float: right; font-size: 10pt" border="0">
                <tr>
                    <td style="width: 130px">Tanggal Permintaan</td>
                    <td style="width: 10px">:</td>
                    <td><?php echo date_format($date,'d-m-Y');?></td>
                </tr>
            </table>
        </div>
    </div>

<br>
<div class="content">
    <hr>
        <table width="50%" style="float: left; font-size: 10pt" border="0">
            <tr >
                <td style="width: 115px">Keterangan</td>
            </tr>
        </table>
        <table style="float: right; font-size:10pt">
            <tr>
                <td>Total</td>
            </tr>
        </table>
        <br>
    <hr>
</div>

        <table width="85%" style="float: left; font-size:10pt">
            <tr>
                <td>{{ $kasbon->keterangan }}</td>
            </tr>
        </table>
        <table style="float: right; font-size:10pt">
            <tr>
                <td><?php echo number_format($kasbon->nilai,'2','.',',') ?></td>
            </tr>
        </table>

<?php
$grand_total = $kasbon->nilai;
?>

<br><br><br><br>
    <table style="float: left; font-size:10pt; width: 58%">
        <tr>
            <td>Terbilang :</td>
        </tr>
        <tr>
            <td><strong><?php echo Terbilang::make($grand_total, ' rupiah'); ?></strong></td>
        </tr>
    </table>
<br>
<div class="footer" style="font-size: 10pt;padding-top: 1.5cm">
    <div class="tgl">
        Palembang, <?php echo date_format($date,'d F Y');?>
    </div>

    <table width="100%" style="font-size:10pt; text-align:center;padding:0px; margin:0px; border-collapse:collapse" border="0">
        <tr style="padding:0px; margin:0px">
            <td width="30%">Dibuat oleh,</td>
            <td width="30%">Diperiksa oleh,</td>
            <td width="30%">Diperiksa oleh,</td>
            <td width="40%">Diterima oleh,</td>
        </tr>
        <tr style="padding:0px; margin:0px"><td colspan="3"><br><br><br></td></tr>
        <tr style="padding:0px; margin:0px">
            <td><b><u><?php echo $ttd; ?></u></b></td>
            <td>Finance</td>
            <td>Accounting</td>
            <td></td>
        </tr>
        <tr style="padding:0px; margin:0px">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
</div>

</body>
</html>