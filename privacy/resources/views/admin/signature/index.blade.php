@extends('adminlte::page')

@section('title', 'Signature')

@section('content_header')

    

@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
@include('sweet::alert')

<body onLoad="load()">
    <div class="box box-solid">
        <div class="box-body">
            <div class="box ">
                <div class="box-body">
                    @permission('create-signature')
                    <button type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#addform">
                        <i class="fa fa-plus"></i> New Signature</button>
                    @endpermission

                    <span class="pull-right"> 
                        <font style="font-size: 16px;"><b>SIGNATURE</b></font>
                    </span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="signature-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-info">
                        <th>Kode Signature</th>
                        <th>Mengetahui</th>
                        <th>Jabatan</th>
                        <th>Limit Dari</th>
                        <th>Limit Sampai</th>
                        <th>Kode Lokasi</th>
                     </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addform" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Create Data</h4>
            </div>
            @include('errors.validation')
            {!! Form::open(['id'=>'ADD']) !!}
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('mengetahui', 'Mengetahui:') }}
                                    {{ Form::text('mengetahui', null, ['class'=> 'form-control','id'=>'Nama1','required'=>'required', 'placeholder'=>'Mengetahui', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('jabatan', 'Jabatan:') }}
                                    {{ Form::text('jabatan', null, ['class'=> 'form-control','id'=>'Nama2', 'placeholder'=>'Jabatan', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('limit_dari', 'Limit Dari:') }}
                                    {{ Form::text('limit_dari', 0, ['class'=> 'form-control','id'=>'dari1','required'=>'required', 'placeholder'=>'Limit Dari', 'autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                                 </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('limit_sampai', 'Limit Sampai:') }}
                                    {{ Form::text('limit_sampai', 0, ['class'=> 'form-control','id'=>'sampai1','required'=>'required', 'placeholder'=>'Limit Sampai', 'autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                                 </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group7">
                                    {{ Form::label('kode_lokasi', 'Kode Lokasi:') }}
                                    {{ Form::select('kode_lokasi', $Lokasi->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'lokasi1'])}}
                                </div>
                            </div>
                    </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            {{ Form::submit('Create data', ['class' => 'btn btn-success crud-submit']) }}
                            {{ Form::button('Close', ['class' => 'btn btn-danger','data-dismiss'=>'modal']) }}&nbsp;
                        </div>
                    </div>
                {!! Form::close() !!}
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->


    <div class="modal fade" id="editform" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Edit Data</h4>
            </div>
            @include('errors.validation')
            {!! Form::open(['id'=>'EDIT']) !!}
            <div class="modal-body">
                <div class="row">
                    {{ Form::hidden('kode_signature', null, ['class'=> 'form-control','id'=>'Kode','readonly']) }}
                        
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('mengetahui', 'Mengetahui:') }}
                            {{ Form::text('mengetahui', null, ['class'=> 'form-control','id'=>'Mengetahui', 'autocomplete'=>'off']) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('jabatan', 'Jabatan:') }}
                            {{ Form::text('jabatan', null, ['class'=> 'form-control','id'=>'Jabatan', 'autocomplete'=>'off','readonly']) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('limit_dari', 'Limit Dari:') }}
                            {{ Form::text('limit_dari', null, ['class'=> 'form-control','id'=>'dari','required'=>'required', 'placeholder'=>'Limit Dari', 'autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                            </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('limit_sampai', 'Limit Sampai:') }}
                            {{ Form::text('limit_sampai', null, ['class'=> 'form-control','id'=>'sampai','required'=>'required', 'placeholder'=>'Limit Sampai', 'autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                            </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group7">
                            {{ Form::label('kode_lokasi', 'Kode Lokasi:') }}
                            {{ Form::select('kode_lokasi', $Lokasi->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'lokasi'])}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    {{ Form::submit('Update data', ['class' => 'btn btn-success']) }}
                    {{ Form::button('Close', ['class' => 'btn btn-danger','data-dismiss'=>'modal']) }}&nbsp;
                </div>
            </div>
            {!! Form::close() !!}
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
      </div><!-- /.modal -->

      <button type="button" class="back2Top btn btn-warning btn-xs" id="back2Top"><i class="fa fa-arrow-up" style="color: #fff"></i> <i>{{ $nama_company }}</i> <b>({{ $nama_lokasi }})</b></button>

        <style type="text/css">
            #back2Top {
                width: 400px;
                line-height: 27px;
                overflow: hidden;
                z-index: 999;
                display: none;
                cursor: pointer;
                position: fixed;
                bottom: 0;
                text-align: left;
                font-size: 15px;
                color: #000000;
                text-decoration: none;
            }
            #back2Top:hover {
                color: #fff;
            }

            /* Button used to open the contact form - fixed at the bottom of the page */
            .hapus-button {
                background-color: #F63F3F;
                bottom: 186px;
            }

            .edit-button {
                background-color: #FDA900;
                bottom: 216px;
            }

            #mySidenav button {
              position: fixed;
              right: -30px;
              transition: 0.3s;
              padding: 4px 8px;
              width: 70px;
              text-decoration: none;
              font-size: 12px;
              color: white;
              border-radius: 5px 0 0 5px ;
              opacity: 0.8;
              cursor: pointer;
              text-align: left;
            }

            #mySidenav button:hover {
              right: 0;
            }

            #about {
              top: 70px;
              background-color: #4CAF50;
            }

            #blog {
              top: 130px;
              background-color: #2196F3;
            }

            #projects {
              top: 190px;
              background-color: #f44336;
            }

            #contact {
              top: 250px;
              background-color: #555
            }
        </style>

        <div id="mySidenav" class="sidenav">
            @permission('update-signature')
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editsignature" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>
            @endpermission

            @permission('delete-signature')
            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapussignature" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>
            @endpermission
        </div>
</body>
@stop

@push('css')

@endpush
@push('js')
  
    <script type="text/javascript">
        $(window).scroll(function() {
            var height = $(window).scrollTop();
            if (height > 1) {
                $('#back2Top').show();
            } else {
                $('#back2Top').show();
            }
        });

        function load(){
            startTime();
            $('.tombol1').hide();
            $('.tombol2').hide();
            $('.back2Top').show();
        }

        $(function() {
            $('#signature-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('signature.data') !!}',
            columns: [
                { data: 'kode_signature', name: 'kode_signature', visible: false },
                { data: 'mengetahui', name: 'mengetahui' },
                { data: 'jabatan', name: 'jabatan' },
                { data: 'limit_dari', name: 'limit_dari' },
                { data: 'limit_sampai', name: 'limit_sampai' },
                { data: 'kode_lokasi',
                    render: function( data, type, full ) {
                    return formatNomor(data); }
                },
            ]
            });
        });

        function formatNomor(n) {
            if(n == 'HO'){
                var stat = "<span style='color:#0275d8'><b>HO</b></span>";
                return n.replace(/HO/, stat);
            }else{
                var str = n;
                var result = str.fontcolor( "#eb4034" );
                return result;
            }
        }

        $(document).ready(function() {
            $("#back2Top").click(function(event) {
                event.preventDefault();
                $("html, body").animate({ scrollTop: 0 }, "slow");
                return false;
            });

            var table = $('#signature-table').DataTable();

            $('#signature-table tbody').on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected bg-gray') ) {
                    $(this).removeClass('selected bg-gray');
                }
                else {
                    table.$('tr.selected').removeClass('selected bg-gray');
                    $(this).addClass('selected bg-gray');
                    var select = $('.selected').closest('tr');
                }
            });

            $('#editsignature').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#signature-table').DataTable().row(select).data();
                var kode_signature = data['kode_signature'];
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('signature.edit_signature') !!}',
                    type: 'POST',
                    data : {
                        'id': kode_signature
                    },
                    success: function(results) {
                        console.log(results);
                        $('#Kode').val(results.kode_signature);
                        $('#Mengetahui').val(results.mengetahui);
                        $('#Jabatan').val(results.jabatan);
                        $('#dari').val(results.limit_dari);
                        $('#sampai').val(results.limit_sampai);
                        $('#lokasi').val(results.kode_lokasi).trigger('change');
                        $('#editform').modal('show');
                        }
         
                });
            });

            $('#hapussignature').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#signature-table').DataTable().row(select).data();
                var kode_signature = data['kode_signature'];
                var row = table.row( select );
                swal({
                    title: "Hapus?",
                    text: "Pastikan dahulu item yang akan di hapus",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal!",
                    reverseButtons: !0
                }).then(function (e) {
                    if (e.value === true) {
                        $.ajax({
                            url: '{!! route('signature.hapus_signature') !!}',
                            type: 'POST',
                            data : {
                                'id': kode_signature
                            },

                            success: function (results) {
                                if (results.success === true) {
                                    swal("Berhasil!", results.message, "success");
                                } else {
                                    swal("Gagal!", results.message, "error");
                                }
                                refreshTable();
                            }
                        });
                    }
                });
            });
        });

        $('.select2').select2({
            placeholder: "Pilih",
            allowClear: true,

        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function hanyaAngka(e, decimal) {
            var key;
            var keychar;
             if (window.event) {
                 key = window.event.keyCode;
             } else
             if (e) {
                 key = e.which;
             } else return true;
          
            keychar = String.fromCharCode(key);
            if ((key==null) || (key==0) || (key==8) ||  (key==9) || (key==13) || (key==27) ) {
                return true;
            } else
            if ((("0123456789").indexOf(keychar) > -1)) {
                return true;
            } else
            if (decimal && (keychar == ".")) {
                return true;
            } else return false;
        }

        function refreshTable() {
             $('#signature-table').DataTable().ajax.reload(null,false);;
        }

        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });

        $('.modal-dialog').resizable({
    
        });

        $('#ADD').submit(function (e) {
            e.preventDefault();
            var registerForm = $("#ADD");
            var formData = registerForm.serialize();

                $.ajax({
                    url:'{!! route('signature.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#Nama1').val('');
                        $('#Nama2').val('');
                        $('#dari1').val('0');
                        $('#sampai1').val('0');
                        $('#lokasi1').val('').trigger('change');
                        $('#addform').modal('hide');
                        refreshTable();
                        if (data.success === true) {
                            swal("Berhasil!", data.message, "success");
                        } else {
                            swal("Gagal!", data.message, "error");
                        }   
                    },
                });
        });

        $('#EDIT').submit(function (e) {
            e.preventDefault();
            var registerForm = $("#EDIT");
            var formData = registerForm.serialize();

                $.ajax({
                    url:'{!! route('signature.ajaxupdate') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#editform').modal('hide');
                        refreshTable();
                        if (data.success === true) {
                            swal("Berhasil!", data.message, "success");
                        } else {
                            swal("Gagal!", data.message, "error");
                        }
                    },
                });
        });
    </script>
@endpush