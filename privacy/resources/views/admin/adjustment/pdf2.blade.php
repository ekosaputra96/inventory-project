<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <br>
    <title>JURNAL UMUM</title>
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
            <h1>JURNAL UMUM</h1>
        <br>

        <div class="left">
            <table width="50%" style="float: left; font-size: 10pt" border="0">
                <tr >
                    <td style="width: 115px">Journal No</td>
                    <td style="width: 10px">:</td>
                    <td>{{ $ledger2->no_journal }}</td>
                </tr>
                <tr>
                    <td >Journal Date</td>
                    <td>:</td>
                    <td>{{ $journal_date }}</td>
                </tr>
                <tr>
                    <td >Remark</td>
                    <td>:</td>
                    <td></td>
                </tr>
            </table>
        </div>
        <div class="right">
            <table style="float: right; font-size: 10pt" border="0">
                <tr>
                    <td style="width: 130px">Entry Date</td>
                    <td style="width: 10px">:</td>
                    <td><?php echo date_format($adjustment->created_at,"d/m/Y") ?></td>
                </tr>
                <tr>
                    <td >Reference</td>
                    <td>:</td>
                    <td>{{ $ledger2->reference }}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>{{ $adjustment->status }}</td>
                </tr>
            </table>
        </div>
    </div>
<br>
<div class="content">
    <hr>
    <div class="left">
        <table width="50%" style="  font-size: 10pt" border="0">
            <tr >
                <td style="width: 25px">No.</td>
                <td style="width: 33px">D/K</td>
                <td style="width: 164px">Account No.</td>
                <td style="width: 280px">Account Name</td>
                <td style="width: 130px; text-align: right">Debit</td>
                <td style="width: 130px; text-align: right">Credit</td>
            </tr>
        </table>
    </div>
    <hr>
</div>

    <div class="left">
        <table style="padding-left:0mm; font-size:10pt">
            <?php $subtotal = 0 ; $limit_row = 0?>
            <?php foreach ($ledger as $key => $value): ?>
                <tr >
                    <td style="width: 25px"><?php echo $key+1 ?></td>
                    <td style="width: 33px"><?php echo $value->db_cr ?></td>
                    <td style="width: 164px"><?php echo $value->account ?></td>
                    <td style="width: 280px"><?php echo $value->ac_description ?></td>
                    <td style="width: 130px; text-align: right"><?php echo number_format($value->debit,'0','.',',') ?></td>
                    <td style="width: 130px; text-align: right"><?php echo number_format($value->kredit,'0','.',',') ?></td>
                </tr>
            <?php endforeach ?>
        </table>
    </div>

    <br><br><br>
    <hr>
    <div class="left">
        <table width="100%" style="  font-size: 10pt" border="0">
            <tr >
                <td style="width: 537px">TOTAL JOURNAL</td>
                <td style="width: 130px; text-align: right"><?php echo number_format($grand_total,'0','.',',') ?></td>
                <td style="width: 130px; text-align: right"><?php echo number_format($grand_total,'0','.',',') ?></td>
            </tr>
        </table>
    </div>
    <hr>

    <div class="left">
        <table style="padding-left:1mm; font-size:10pt; width: 58%">
            <tr>
                <td style="width: 30%">Terbilang</td>
                <td>#<?php echo Terbilang::make($grand_total, ' rupiah'); ?>#</td>
            </tr>
            <tr>
                <td>Printed by : </td>
                <td><?php echo date_format($dt,"d/m/Y H:i:s") ?>&nbsp; {{ $user }}</td>
            </tr>
        </table>
    </div>
</body>
</html>