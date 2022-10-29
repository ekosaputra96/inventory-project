@extends('adminlte::page')

@section('title', 'Endofmonth Parts')

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
                  <h4 class="modal-title">End of Month <b>Part</b></h4>
                </div>
                @include('errors.validation')
                {!! Form::open(['id'=>'ADD']) !!}
                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group">
                                    <div class="col-sm-4">
                                        {{ Form::label('tanggal_awal', 'Tutup Periode:') }}
                                        {{ Form::select('tanggal_awal',$tanggal,null, ['class'=> 'form-control','id'=>'tanggal1','onchange'=>'load();']) }}
                                    </div>
                                    <div class="col-sm-4">
                                        {{ Form::label('tanggal_akhir', 'Buka Periode:') }}
                                        {{ Form::text('tanggal_akhir',null, ['class'=> 'form-control','id'=>'tanggal2','placeholder'=>'Periode Baru','readonly']) }}
                                    </div>
                                </div>
                                <div class="col-md-12">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                      <br>
                                        {{ Form::label('parrt', 'Pilih Part:') }}
                                        {{ Form::select('part', $part,null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Parto','required']) }}
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
            placeholder: "",
            allowClear: true,
        });

        function load(){
            $('#button4').modal('show');

            var open = $("#tanggal1").val();
            
            if(open == null){
                swal("Gagal!", "Silahkan Re-Open Close Terlebih Dahulu");
                button1.disabled = true;
            }
            else{
                 var newdate = new Date(open);
                 var dd = newdate.getDate();
                 var mm = newdate.getMonth() + 1;
                 var y = newdate.getFullYear();

                 var dd_new = '01';

                 if(mm<12){
                     var mm_new = mm+1;
                     
                     if (mm_new < 10){
                         mm_new = "0" + mm_new;
                     }

                     var someFormattedDate = y + '-' + mm_new + '-' + dd_new;
                     document.getElementById('tanggal2').value = someFormattedDate;
                 }
                 else if(mm==12){
                     var mm_new = '01';
                     var y_new = y+1;

                     var someFormattedDate = y_new + '-' + mm_new + '-' + dd_new;
                     document.getElementById('tanggal2').value = someFormattedDate;
                 }
            }  
        }

        function panggil(){
            load();
            startTime();
        }


        $('#button1').click( function () {
            var registerForm = $("#ADD");
            var formData = registerForm.serialize();
            swal({
                title: "<b>Proses Sedang Berlangsung</b>",
                type: "warning",
                showCancelButton: false,
                showConfirmButton: false,
                allowOutsideClick: false
            })
            $.ajax({
                url: '{!! route('endofmonthpart.change') !!}',
                type: 'POST',
                data:formData,
                success: function(data) {
                    if (data.success === true) {
                      swal("Done!", data.message, "success");
                    } else {
                      swal("Error!", data.message, "error");
                    }
                },
            });
        });

        function refreshTable() {
             $('#data-table').DataTable().ajax.reload(null,false);
        }

        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });

        $('.modal-dialog').resizable({
    
        });
    </script>
@endpush