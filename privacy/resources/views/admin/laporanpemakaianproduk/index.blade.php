@extends('adminlte::page')

@section('title', 'Laporan Pemakaian Produk')

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
                  <h4 class="modal-title">Laporan <b>Pemakaian Per Produk</b></h4>
                </div>
                @include('errors.validation')
                {!! Form::open(['route' => ['laporanpemakaianproduk.export'],'method' => 'get','id'=>'form', 'target'=>"_blank"]) !!}
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group">
                            <div class="col-sm-4">
                                {{ Form::label('tanggal_awal', 'Dari Tanggal:') }}
                                {{ Form::date('tanggal_awal',\Carbon\Carbon::now(), ['class'=> 'form-control','id'=>'tanggal1']) }}
                            </div>
                            <div class="col-sm-4">
                                {{ Form::label('tanggal_akhir', 'Sampai Tanggal:') }}
                                {{ Form::date('tanggal_akhir',\Carbon\Carbon::now(), ['class'=> 'form-control','id'=>'tanggal2','placeholder'=>'Periode Baru']) }}                                    
                            </div>
                            <div class="col-sm-4">  
                                {{ Form::label('pilih', 'Tipe Pemakaian:') }}              
                                {{Form::select('tipe_pemakaian', ['alat' => 'Alat', 'mobil' => 'Mobil', 'kapal' => 'Kapal', 'Other' => 'Other'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'report1','required'=>'required'])}}
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group"> 
                                    {{ Form::label('prod', 'Nama Produk:') }}              
                                    {{Form::select('kode_produk', $Produk, null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Produk1','required'=>'required'])}}
                                </div>
                            </div>
                            {{ Form::hidden('status', 'POSTED', ['class'=> 'form-control','id'=>'status1','readonly']) }}
                            <?php if (Auth()->user()->kode_lokasi == 'HO') { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('lokasi', 'Pilih Kode Lokasi:') }}
                                        {{ Form::select('lokasi',['SEMUA' => 'SEMUA','Lokasi'=>$lokasi],null, ['class'=> 'form-control select2','id'=>'lokasi1','style'=>'width: 100%','placeholder' => '','required']) }}
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="col-sm-6">
                                <br>
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
                    url:'{!! route('laporantransferout.export') !!}',
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
        }

        function panggil(){
            load();
            startTime();
        }


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