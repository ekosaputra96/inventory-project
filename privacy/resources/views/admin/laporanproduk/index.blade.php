@extends('adminlte::page')

@section('title', 'Laporan Data Produk')

@section('content_header')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
@stop

@section('content')
@include('sweet::alert')
<body onLoad="panggil()">
        <div class="modal fade" id="button4"  role="dialog">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title">Laporan <b>Data Produk</b></h4>
                </div>
                @include('errors.validation')
                {!! Form::open(['route' => ['laporanproduk.export'],'method' => 'get','id'=>'form', 'target'=>"_blank"]) !!}
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('format_cetak', 'Format Cetak:') }}
                                            {{Form::select('format_cetak', ['PDF' => 'PDF', 'Excel' => 'Excel'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'format1','required'=>'required'])}}
                                        </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group"> 
                                        {{ Form::label('kategori', 'Kategori Produk:') }}              
                                        {{Form::select('kategori', ['SEMUA'=>'SEMUA',$kategori], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'kategori1','required'=>'required'])}}
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        {{ Form::label('merr', 'Merek:') }}
                                        {{Form::select('merek', ['SEMUA'=>'SEMUA',$merek], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'merek1','required'=>'required'])}}
                                    </div>
                                </div>
                            <?php if (Auth()->user()->kode_lokasi == 'HO') { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('lokasi', 'Pilih Kode Lokasi:') }}
                                        {{ Form::select('lokasi',['SEMUA' => 'SEMUA','Lokasi'=>$lokasi],null, ['class'=> 'form-control select2','id'=>'lokasi1','style'=>'width: 100%','placeholder' => '','required']) }}
                                    </div>
                                </div>
                            <?php } ?>
                                    <!--<div class="col-sm-6">-->
                                    <!--    <input type="checkbox" name="ttd" value="1"/>&nbsp;Cetak TTD di halaman baru<br>-->
                                    <!--</div>-->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="row">
                                {{ Form::submit('Cetak', ['class' => 'btn btn-success crud-submit']) }}
                                {{ Form::button('Close', ['class' => 'btn btn-danger','data-dismiss'=>'modal']) }}&nbsp;
                            </div>
                        </div>
                    {!! Form::close() !!}
              </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>

    </div>
</body>
@stop

@push('css')

@endpush
@push('js')
  
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function load(){
            $('#button4').modal('show');
        }

        function panggil(){
            load();
            startTime();
        }

        $('.select2').select2({
            placeholder: "Pilih",
            allowClear: true,
        });

        function refreshTable() {
             $('#data-table').DataTable().ajax.reload(null,false);;
        }

        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });

        $('.modal-dialog').resizable({
    
        });
    </script>
@endpush