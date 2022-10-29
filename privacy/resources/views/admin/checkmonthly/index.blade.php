@extends('adminlte::page')

@section('title', 'Calculate Monthly')

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
                  <h4 class="modal-title">Calculate <b>Monthly</b></h4>
                </div>
                @include('errors.validation')
                {!! Form::open(['id'=>'ADD']) !!}
                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group">
                                    <div class="col-sm-6">
                                       {{ Form::selectMonth('month', null, ['class'=> 'form-control select2','id'=>'bulan_akhir','placeholder'=>'','style'=>'width: 100%'])}}
                                    </div>
                                    <div class="col-sm-6">
                                       {{ Form::selectYear('year', 2019, 2040, null, ['class'=> 'form-control select3','id'=>'tahun_awal','placeholder'=>'','style'=>'width: 100%'])}}
                                    </div>
                                    <div class="col-md-3">
                                        {{ Form::label('Lokasis', 'Lokasi:') }}
                                        {{ Form::select('kode_lokasi',$Lokasi,null, ['class'=> 'form-control select4','style'=>'width: 100%','placeholder' => '','id'=>'Lokasi1','required'=>'required']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="row">
                                <button type="button" class="tombol1 btn btn-success btn-md" id="button1">Submit</button>
                                {{ Form::button('Close', ['class' => 'btn btn-danger','data-dismiss'=>'modal']) }}&nbsp;
                            </div>
                        </div>
                    {!! Form::close() !!}
              </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

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
            placeholder: "Pilih Bulan",
            allowClear: true,
        });

        $('.select3').select2({
            placeholder: "Pilih Tahun",
            allowClear: true,
        });
        
        $('.select4').select2({
            placeholder: "Pilih Lokasi",
            allowClear: true,
        });

        function load(){
            $('#button4').modal('show');
        }

        function panggil(){
            load();
            startTime();
        }


        $('#button1').click( function () {
            var registerForm = $("#ADD");
            var month= $('#bulan_akhir').val();
            var year= $('#tahun_awal').val();
            var lokasi = $('#Lokasi1').val();
            var formData = registerForm.serialize();
            swal({
                title: "<b>Proses Sedang Berlangsung</b>",
                type: "warning",
                showCancelButton: false,
                showConfirmButton: false,
                allowOutsideClick: false
            })
            $.ajax({
              url: '{!! route('checkmonthly.change') !!}',
              type: 'POST',
              data:{
                  'month': month,
                  'year': year,
                  'lokasi': lokasi,
              },
              success: function(data) {
                if (data.success === true) {
                  swal("Done!", data.message, "success");
                }else {
                  swal("Error!", data.message, "error");
                }
              },
            });
        });

        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });
</script>
@endpush