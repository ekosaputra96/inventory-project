<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Permintaan_Kasbon_{{ $kasbon->no_pkb }}.pdf</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <style type="text/css">
        .header {
            width: 100%;
            position: fixed;
            top: 0;
        }

        .header-logo {
            margin-top: 8px;
        }

        .header-text {
            font-size: 11px;
            margin-left: 10px;
        }

        .header-heading {
            font-size: 19px;
            margin-top: 12px;
            margin-bottom: 0px;
        }

        .header-periode {
            font-size: 13px;
            margin-top: -4px;
        }

        .table-text-head {
            font-size: 11px;
        }

        .table-text-data {
            font-size: 11px;
        }

        .page {
            font-size: 12px;
        }

        .header .page:after {
            content: counter(page, decimal);
        }

        .page_break {
            page-break-after: always;
        }

        body {
            padding-top: 100px;
        }

        .content {
            font-size: 13px;
        }
    </style>
</head>

<body>
    {{-- the header of the pdf file --}}
    <div class="header">
        <div>
            <div class="header-logo float-left">
                <img src="{{ asset('css/logo_gui.png') }}" alt="logo_gui" height="25px" width="25px">
            </div>
            <div class="header-text float-left">
                <span><b>{{ $get_nama_company }}</b></span>
                <br>
                <span>Lokasi : {{ $get_nama_lokasi }}</span>
            </div>
            <div class="float-right page">
                <b>Date : </b><span>{{ date('d/m/Y') }}</span>
                <b>Time : </b><span>{{ date('h:i:sa') }}</span>
                <b>Page : </b>
            </div>
        </div>
        <div class="clearfix"></div>
        <h1 class="text-center header-heading text-bold">PERMINTAAN KASBON</h1>
    </div>

    {{-- the content of pdf file --}}
    <div class="content">
        <table width="100%">
            <tr>
                <td width="20%">No PKB</td>
                <td width="45%">: <b>{{ $kasbon->no_pkb }}</b></td>
                <td width="20%">Tanggal Permintaan</td>
                <td>: {{ $tanggal_permintaan_format }}</td>
            </tr>
            <tr>
                <td width="20%">Nama Pemohon</td>
                <td width="45%">: {{ $kasbon->nama_pemohon }}</td>
            </tr>
        </table>

        {{-- keterangan --}}
        <table class="table mt-3">
            <tbody>
                <tr>
                    <td width="23%">Keterangan</td>
                    <td>:</td>
                    <td width="100%" class="text-right">{{ $kasbon->keterangan }}</td>
                </tr>
                <tr>
                    <td colspan="2">Total</td>
                    <td class="text-right">Rp. {{ number_format($kasbon->nilai, '2', '.', ',') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- terbilang --}}
        <p>Terbilang : <b>{{ ucwords(Terbilang::make($kasbon->nilai, ' rupiah')) }}</b></p>

        {{-- signature --}}
        <table class="table table-borderless table-sm mt-3">
            <tbody>
                <tr>
                    <td colspan="4">Palembang, {{ $tanggal_permintaan_format }}</td>
                </tr>
                <tr class="text-center">
                    <td width="23%">Dibuat oleh,</td>
                    <td>Diperiksa oleh,</td>
                    <td>Diperiksa oleh,</td>
                    <td>Diterima oleh,</td>
                </tr>
                <tr>
                    <td><br><br></td>
                </tr>
                <tr class="text-center">
                    <td width="23%"><b><u>{{$kasbon->created_by}}</u></b></td>
                    <td>Finance</td>
                    <td>Accounting</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
