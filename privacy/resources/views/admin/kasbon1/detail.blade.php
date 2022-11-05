@extends('adminlte::page')

@section('title', 'Permintaan Kasbon')

@section('content_header')

@stop

@include('sweet::alert')

@section('content')
    <body onload="load()">
        <div class="box box-solid">
            <div class="box-body">
                {{-- header --}}
                <div class="box">
                    {{-- header buttons --}}
                    <div class="box-body">
                        <a href="{{route('kasbon1.index')}}" class="btn btn-warning btn-xs"><i class="fa fa-arrow-left"
                            aria-hidden="true"></i> Back</a>
                    </div>
                </div>

                {{-- content --}}
                <div class="content">
                    <h2 class="text-center header-heading text-bold">PERMINTAAN KASBON</h2>

                    <table width="100%">
                        <tr>
                            <td width="20%">No PKB</td>
                            <td width="45%">: <b>{{ $kasbon->no_pkb }}</b></td>
                            <td width="20%">Tanggal Permintaan</td>
                            <td class="text-right">: {{ $tanggal_permintaan_format }}</td>
                        </tr>
                        <tr>
                            <td width="20%">Nama Pemohon</td>
                            <td width="45%">: {{ $kasbon->nama_pemohon }}</td>
                        </tr>
                    </table>
            
                    {{-- keterangan --}}
                    <table class="table" style="margin-top: 1.5rem">
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
                </div>
            </div>
        </div>
    </body>
@stop

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <script type="text/javascript">
        function load(){
            startTime();
        }
    </script>
@endpush