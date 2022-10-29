@extends('adminlte::page')

@section('title', 'Laporan Maintenance Record')

@section('content_header')

@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
@include('sweet::alert')
<body onLoad="panggil()">
    <div class="box box-solid">
        <div class="modal fade" id="button4"  role="dialog">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title"><b>Maintenance Record</b></h4>
                </div>
                @include('errors.validation')
                {!! Form::open(['route' => ['laporanmaintenance.export'],'method' => 'get','id'=>'form', 'target'=>"_blank"]) !!}
                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            {{ Form::label('tanggal_awal', 'Dari Tanggal:') }}
                                            {{ Form::date('tanggal_awal',\Carbon\Carbon::now(), ['class'=> 'form-control','id'=>'tanggal1']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            {{ Form::label('tanggal_akhir', 'Sampai Tanggal:') }}
                                            {{ Form::date('tanggal_akhir',\Carbon\Carbon::now(), ['class'=> 'form-control','id'=>'tanggal2','placeholder'=>'Periode Baru']) }}
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
                                            {{ Form::label('tipe', 'Tipe Pemakaian:') }}              
                                            {{Form::select('tipe', ['SEMUA' => 'SEMUA', 'Alat' => 'Alat', 'Mobil' => 'Mobil', 'Kapal' => 'Kapal', 'Other' => 'Other'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'tipe1','required'=>'required', 'onchange'=>"cektipe()"])}}
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">  
                                            {{ Form::label('pilih', 'Status:') }}              
                                            {{Form::select('status', ['SEMUA' => 'SEMUA', 'OPEN' => 'OPEN', 'POSTED' => 'POSTED'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'status1','required'=>'required'])}}
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group"> 
                                            {{ Form::label('pilih', 'Format Laporan:') }}              
                                            {{Form::select('jenis_report', ['PDF' => 'PDF', 'excel' => 'Excel'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'report1','required'=>'required'])}}
                                        </div>
                                    </div>
                                    <?php if (auth()->user()->kode_lokasi == 'HO') { ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('lokasi', 'Pilih Kode Lokasi:') }}
                                                {{ Form::select('lokasi',['SEMUA' => 'SEMUA','Lokasi'=>$lokasi],null, ['class'=> 'form-control select2','id'=>'lokasi1','style'=>'width: 100%','placeholder' => '','required']) }}
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="col-sm-4">
                                        <div class="form-group_asset"> 
                                            {{ Form::label('tipe', 'No Asset Mobil:') }}              
                                            {{Form::select('asset', ['SEMUA' => 'SEMUA','No Asset'=>$Aset], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'aset1'])}}
                                        </div>

                                        <div class="form-group_assetalat"> 
                                            {{ Form::label('tipe', 'No Asset Alat:') }}              
                                            {{Form::select('assetalat', ['SEMUA' => 'SEMUA','No Asset'=>$Asetalat], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'asetalat1'])}}
                                        </div>

                                        <div class="form-group_assetkapal"> 
                                            {{ Form::label('tipe', 'No Asset Kapal:') }}              
                                            {{Form::select('assetkapal', ['SEMUA' => 'SEMUA','No Asset'=>$Asetkapal], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'asetkapal1'])}}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">  
                                        <input type="checkbox" name="ttd" value="1"/>&nbsp;Cetak TTD di halaman baru<br>
                                    </div>
                                </div>
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
        </div><!-- /.modal -->
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

        $('.select2').select2({
            placeholder: "Pilih",
            allowClear: true,
        });

        function cetakpdf() {
            var registerForm = $("#ADD");
            var formData = registerForm.serialize();

            swal({
            title: "Cetak PDF?",
            type: "warning",
            showCancelButton: !0,
            confirmButtonText: "Ya, Cetak!",
            cancelButtonText: "Batal",
            reverseButtons: !0
        }).then(function (e) {
            if (e.value === true) {
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    url:'{!! route('laporanpemakaian.export') !!}',
                    type:'GET',
                    data:formData,
                    success:function(result) {
                            swal("Berhasil!<br><b>PDF berhasil dicetak</b>");
                    },
                error : function() {
                        swal("GAGAL!<br><b>PDF gagal dicetak</b>");
                    }
                });

            } else {
                e.dismiss;
            }

        }, function (dismiss) {
            return false;
        })

        }

        function load(){
            $('#button4').modal('show');
            $('.form-group_asset').hide();
            $('.form-group_assetalat').hide();
            $('.form-group_assetkapal').hide();
            $('.form-groupx').hide();
        }

        function panggil(){
            load();
            startTime();
        }
        
        function refreshTable() {
             $('#data-table').DataTable().ajax.reload(null,false);;
        }

        function cektipe() {
            var tipe = $('#tipe1').val();
            if (tipe == 'Mobil'){
                document.getElementById("aset1").required = true;
                document.getElementById("asetalat1").required = false;
                document.getElementById("asetkapal1").required = false;
                $('.form-group_asset').show();
                $('.form-group_assetalat').hide();
                $('.form-group_assetkapal').hide();
                $('.form-groupx').hide();
            }else if(tipe == 'Alat'){
                document.getElementById("aset1").required = false;
                document.getElementById("asetalat1").required = true;
                document.getElementById("asetkapal1").required = false;
                $('.form-group_asset').hide();
                $('.form-group_assetalat').show();
                $('.form-group_assetkapal').hide();
                $('.form-groupx').show();
            }else if(tipe == 'Kapal'){
                document.getElementById("aset1").required = false;
                document.getElementById("asetalat1").required = false;
                document.getElementById("asetkapal1").required = true;
                $('.form-group_asset').hide();
                $('.form-group_assetalat').hide();
                $('.form-group_assetkapal').show();
                $('.form-groupx').hide();
            }else {
                document.getElementById("aset1").required = false;
                document.getElementById("asetalat1").required = false;
                document.getElementById("asetkapal1").required = false;
                $('.form-group_asset').hide();
                $('.form-group_assetalat').hide();
                $('.form-group_assetkapal').hide();
                $('.form-groupx').hide();
            }
        }

        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });

        $('.modal-dialog').resizable({
    
        });
    </script>
@endpush