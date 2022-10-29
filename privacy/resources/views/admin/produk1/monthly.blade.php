<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LAPORAN BULANAN {{ $get_nama_produk }}</title>
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

        .header .page:after{
            content: counter(page, decimal);
        }

        .page_break {
            page-break-after: always;
        }

        body{
            padding-top: 100px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div>
            <div class="header-logo float-left">
                <img src="{{ asset('css/logo_gui.png') }}" alt="logo_gui" height="25px" width="25px">
            </div>
            <div class="header-text float-left">
                <span><b>{{ $get_nama_company }}</b></span>
                <br>
                <span>Lokasi : {{ $nama_lokasi }}</span>
            </div>
            <div class="float-right page">
                <b>Date : </b><span>{{date("d/m/Y")}}</span>
                <b>Time : </b><span>{{date("h:i:sa")}}</span>
                <b>Page : </b>
            </div>
        </div>
        <div class="clearfix"></div>
        <h1 class="text-center header-heading">LAPORAN PRODUK BULANAN {{ $get_nama_produk }}</h1>
        <p class="text-center header-periode">Periode : {{ $awal }} s/d {{ $akhir }}</p>
    </div>

    <div class="content">
        <table class="table table-sm table-striped table-bordered">
            <thead class="thead-dark">
                <tr class="table-text-head">
                    <th scope="col">Periode</th>
                    <th scope="col">Kode Produk</th>
                    <th scope="col">Partnumber</th>
                    <th scope="col">Begin Stock</th>
                    @permission('read-hpp')
                        <th>Begin Amount</th>
                    @endpermission
                    @if ($penerimaan == true || $semua == true)
                        <th>In Stock</th>
                        @permission('read-hpp')
                            <th>In Amount</th>
                        @endpermission
                    @endif
                    @if ($pemakaian == true || $semua == true)
                        <th>Out Stock</th>
                        @permission('read-hpp')
                            <th>Out Amount</th>
                        @endpermission
                    @endif
                    @if ($penjualan == true || $semua == true)
                        <th>Sale Stock</th>
                        @permission('read-hpp')
                            <th>Sale Amount</th>
                        @endpermission
                    @endif
                    @if ($transferin == true || $semua == true)
                        <th>Trans. In</th>
                        @permission('read-hpp')
                            <th>Trans. In Amount</th>
                        @endpermission
                    @endif
                    @if ($transferout == true || $semua == true)
                        <th>Trans. Out</th>
                        @permission('read-hpp')
                            <th>Trans. Out Amount</th>
                        @endpermission
                    @endif
                    @if ($adjustment == true || $semua == true)
                        <th>Adj. Stock</th>
                        @permission('read-hpp')
                            <th>Adj. Amount</th>
                        @endpermission
                    @endif
                    @if ($opname == true || $semua == true)
                        <th>Stock Opname</th>
                        @permission('read-hpp')
                            <th>Amount Opname</th>
                        @endpermission
                    @endif
                    @if ($returbeli == true || $semua == true)
                        <th>Retur Beli Stock</th>
                        @permission('read-hpp')
                            <th>Retur Beli Amount</th>
                        @endpermission
                    @endif
                    @if ($returjual == true || $semua == true)
                        @permission('read-hpp')
                            <th>Retur Jual Amount</th>
                        @endpermission
                    @endif
                    @if ($disassembling == true || $semua == true)
                        <th>Disassembling Stock</th>
                        @permission('read-hpp')
                            <th>Disassembling Amount</th>
                        @endpermission
                    @endif
                    @if ($assembling == true || $semua == true)
                        <th>Assembling Stock</th>
                        @permission('read-hpp')
                            <th>Assembling Amount</th>
                        @endpermission
                    @endif
                    <th>End. Amount</th>
                    @permission('read-hpp')
                        <th>End. Amount</th>
                        <th>Hpp</th>
                    @endpermission
                    @if ($lokasi == 'SEMUA')
                        <th>Kode Lokasi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($monthlyreport as $item)
                    <tr class="table-text-data">
                        <td>{{ date_format(date_create($item->periode), 'F Y') }}</td>
                        <td>{{ $item->kode_produk }}</td>
                        <td>{{ $item->partnumber }}</td>
                        <td>{{ $item->begin_stock }}</td>
                        @permission('read-hpp')
                            <td>{{ number_format($item->begin_amount, '2', '.', ',') }}</td>
                        @endpermission
                        @if ($penerimaan == true || $semua == true)
                            {
                            <td>{{ $item->in_stock }}</td>
                            @permission('read-hpp')
                                <td>{{ number_format($item->in_amount, '2', '.', ',') }}</td>
                            @endpermission
                            }
                        @endif
                        @if ($pemakaian == true || $semua == true)
                            {
                            <td>{{ $item->out_stock }}</td>
                            @permission('read-hpp')
                                <td>{{ number_format($item->out_amount, '2', '.', ',') }}</td>
                            @endpermission
                            }
                        @endif
                        @if ($penjualan == true || $semua == true)
                            {
                            <td>{{ $item->sale_stock }}</td>
                            @permission('read-hpp')
                                <td>{{ number_format($item->sale_amount, '2', '.', ',') }}</td>
                            @endpermission
                            }
                        @endif
                        @if ($transferin == true || $semua == true)
                            {
                            <td>{{ $item->trf_in }}</td>
                            @permission('read-hpp')
                                <td>{{ number_format($item->trf_in_amount, '2', '.', ',') }}</td>
                            @endpermission
                            }
                        @endif
                        @if ($transferout == true || $semua == true)
                            {
                            <td>{{ $item->trf_out }}</td>
                            @permission('read-hpp')
                                <td>{{ number_format($item->trf_out_amount, '2', '.', ',') }}</td>
                            @endpermission
                            }
                        @endif
                        @if ($adjustment == true || $semua == true)
                            {
                            <td>{{ $item->adjustment_stock }}</td>
                            @permission('read-hpp')
                                <td>{{ number_format($item->adjustment_amount, '2', '.', ',') }}</td>
                            @endpermission
                            }
                        @endif
                        @if ($opname == true || $semua == true)
                            {
                            <td>{{ $item->stock_opname }}</td>
                            @permission('read-hpp')
                                <td>{{ number_format($item->amount_opname, '2', '.', ',') }}</td>
                            @endpermission
                            }
                        @endif
                        @if ($returbeli == true || $semua == true)
                            {
                            <td>{{ $item->retur_beli_stock }}</td>
                            @permission('read-hpp')
                                <td>{{ number_format($item->retur_beli_amount, '2', '.', ',') }}</td>
                            @endpermission
                            }
                        @endif
                        @if ($returjual == true || $semua == true)
                            {
                            <td>{{ $item->retur_jual_stock }}</td>
                            @permission('read-hpp')
                                <td>{{ number_format($item->retur_jual_amount, '2', '.', ',') }}</td>
                            @endpermission
                            }
                        @endif
                        @if ($disassembling == true || $semua == true)
                            {
                            <td>{{ $item->disassembling_stock }}</td>
                            @permission('read-hpp')
                                <td>{{ number_format($item->disassembling_amount, '2', '.', ',') }}</td>
                            @endpermission
                            }
                        @endif
                        @if ($assembling == true || $semua == true)
                            {
                            <td>{{ $item->assembling_stock }}</td>
                            @permission('read-hpp')
                                <td>{{ number_format($item->assembling_amount, '2', '.', ',') }}</td>
                            @endpermission
                            }
                        @endif
                        <td>{{ $item->ending_stock }}</td>
                        <td>{{ number_format($item->ending_amount, '2', '.', ',') }}</td>
                        <td>{{ number_format($item->hpp, '2', '.', ',') }}</td>
                        @if ($lokasi == 'SEMUA')
                            {
                            <td>{{ $item->kode_lokasi }}</td>
                            }
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
