<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LAPORAN TRANSAKSI {{ $get_nama_produk }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <style type="text/css">
        .header-logo {
            margin-top: 7px;
        }

        .header-text {
            font-size: 11px;
            margin-left: 10px;
        }

        .header-text span.header-text-company{
            display: flex;
            margin-top: -3px;
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
            font-size: 9px;
        }

        .table-text-data {
            font-size: 10px;
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
                <span class="header-text-company"><b>{{ $get_nama_company }}</b></span>
                <span>Lokasi : {{ $nama_lokasi }}</span>
            </div>
        </div>
        <div class="clearfix"></div>
        <h1 class="text-center header-heading">LAPORAN TRANSAKSI {{ $get_nama_produk }}</h1>
        <p class="text-center header-periode">Periode : {{ $tanggal_awal_detail }} s/d {{ $tanggal_akhir_detail }}</p>
    </div>

    <div class="content">
        <table class="table table-sm table-striped table-bordered">
            <thead class="thead-dark">
                <tr class="table-text-head">
                    <th scope="col">Tanggal Transaksi</th>
                    <th scope="col">No Transaksi</th>
                    <th scope="col">Kode Produk</th>
                    <th scope="col">Nama Produk</th>
                    <th scope="col">Qty Transaksi</th>
                    <th scope="col">Satuan</th>
                    @permission('read-hpp')
                        <th>Harga Transaksi</th>
                        <th>Total Transaksi</th>
                    @endpermission
                    @if ($lokasi == 'SEMUA')
                        <th>Kode Lokasi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($transaksi as $item)
                    <tr class="table-text-data">
                        <td>{{$item->tanggal_transaksi}}</td>
                        <td>{{$item->no_transaksi}}</td>
                        <td>{{$item->kode_produk}}</td>
                        <td>{{$item->produk->nama_produk}}</td>
                        <td>{{$item->qty_transaksi}}</td>
                        <td>{{$item->produk->kode_satuan}}</td>
                        @permission('read-hpp')
                        <td>{{number_format($item->harga_transaksi, '2', '.', ',')}}</td>
                        <td>{{number_format($item->total_transaksi, '2', '.', ',')}}</td>
                        @endpermission
                        @if ($lokasi == 'SEMUA')
                            <td>{{$item->kode_lokasi}}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
