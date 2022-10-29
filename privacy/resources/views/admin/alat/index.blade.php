@extends('adminlte::page')

@section('title', 'Alat')

@section('content_header')

@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <link rel="icon" type="image/png" href="/gui_inventory_laravel/css/logo_gui.png" sizes="16x16">
    <link rel="icon" type="image/png" href="/gui_inventory_laravel/css/logo_gui.png" sizes="32x32">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
@include('sweet::alert')
<body onLoad="load()">
    <div class="box box-solid">
        <div class="box-body">
            <div class="box ">
                <div class="box-body">
                    <?php if(auth()->user()->kode_company != 02) {?>
                    @permission('create-alator')
                    <button type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#addform">
                        <i class="fa fa-plus"></i> New Alat</button>
                    @endpermission
                    <?php }else{ ?>
                    @permission('create-alator')
                    <button type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#addform">
                        <i class="fa fa-plus"></i> New Alat</button>
                    @endpermission
                    <?php } ?>

                    <span class="pull-right">  
                        <a href="http://localhost/gui_inventory_laravel/admin/alat/exportexcel?" target="_blank" id="printpembelian"><button type="button" class="btn bg-black btn-xs"><i class="fa fa-print"></i> CETAK LIST ALAT</button></a>
                        <font style="font-size: 16px;"><b>ALAT</b></font>
                    </span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="alat-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-blue">
                        <th>Kode Alat</th>
                        <th>Nama Alat</th>
                        <th>Merk</th>
                        <th>Type</th>
                        <th>Kapasitas (TON)</th>
                        <th>Tahun</th>
                        <th>No Asset</th>
                        <th>Status</th>
                     </tr>
                    </thead>
                </table>
            </div>
            <div class="col-md-12">
            </div>
            <div class="col-sm-3">
                {{ Form::label('texx', 'Created By:') }}
                {{ Form::text('created_by', null, ['class'=> 'form-control','id'=>'CreateBy1','readonly']) }}
            </div>
            <div class="col-md-3">
                {{ Form::label('texx', 'Created At:') }}
                {{ Form::text('created_at', null, ['class'=> 'form-control','id'=>'CreateAt1','readonly']) }}
            </div>
            <div class="col-md-3">
                {{ Form::label('texx', 'Updated By:') }}
                {{ Form::text('updated_by', null, ['class'=> 'form-control','id'=>'UpdateBy1','readonly']) }}
            </div>
            <div class="col-md-3">
                {{ Form::label('texx', 'Updated At:') }}
                {{ Form::text('updated_at', null, ['class'=> 'form-control','id'=>'UpdateAt1','readonly']) }}
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
                                    {{ Form::label('nama_alat', 'Nama Alat:') }}
                                    {{ Form::text('nama_alat', null, ['class'=> 'form-control','id'=>'Nama1', 'placeholder'=>'Nama Alat','required'=>'required', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('merk', 'Merk:') }}
                                    {{ Form::text('merk', null, ['class'=> 'form-control','id'=>'Nama2','required'=>'required', 'placeholder'=>'Merk', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('type', 'Type:') }}
                                    {{ Form::text('type', null, ['class'=> 'form-control','id'=>'Nama3','required'=>'required', 'placeholder'=>'Type', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('tahun', 'Tahun:') }}
                                    {{ Form::selectYear('tahun', 2000, 2040, null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Nama4','required'=>'required', 'autocomplete'=>''])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('kapasitas', 'Kapasitas:') }}
                                    {{ Form::text('kapasitas', null, ['class'=> 'form-control','id'=>'kapasitas1', 'placeholder'=>'Kapasitas (Ton)', 'autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_asset', 'No Asset:') }}
                                    {{ Form::text('no_asset_alat', null, ['class'=> 'form-control','id'=>'Asset1', 'placeholder'=>'No. Asset', 'autocomplete'=>'off','data-toggle'=>"tooltip",'data-placement'=>"bottom",'title'=>"Maksimal 15 Karakter", 'maxlength'=>'15','required'=>'required', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('aktif', 'Status:') }}
                                    {{Form::select('status', ['Aktif' => 'Aktif', 'NonAktif' => 'NonAktif'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'stat1','required'=>'required'])}}
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group" style="color: red;">
                                    {{ Form::label('LabelInfo', 'Warning: Pemberian No Aset tidak boleh menggunakan spasi.') }}
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


    <div class="modal fade" id="editform" tabindex="-1" role="dialog">
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
                    
                    {{ Form::hidden('kode_alat', null, ['class'=> 'form-control','id'=>'Kode','readonly']) }}
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('nama_alat', 'Nama Alat:') }}
                            {{ Form::text('nama_alat', null, ['class'=> 'form-control','id'=>'Nama','required'=>'required', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('merk', 'Merk:') }}
                            {{ Form::text('merk', null, ['class'=> 'form-control','id'=>'Merk','required'=>'required', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('type', 'Type:') }}
                            {{ Form::text('type', null, ['class'=> 'form-control','id'=>'Type','required'=>'required', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('kapasitas', 'Kapasitas:') }}
                            {{ Form::text('kapasitas', null, ['class'=> 'form-control','id'=>'kapasitas2', 'placeholder'=>'Kapasitas (Ton)', 'autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                            </div>
                        </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('tahun', 'Tahun:') }}
                            {{ Form::selectYear('tahun', 2000, 2040, null, ['class'=> 'form-control','id'=>'Tahun','required'=>'required', 'autocomplete'=>'off'])}}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('no_asset', 'No Asset:') }}
                            {{ Form::text('no_asset_alat', null, ['class'=> 'form-control','id'=>'Asset', 'autocomplete'=>'off','data-toggle'=>"tooltip",'data-placement'=>"bottom",'title'=>"Maksimal 15 Karakter", 'maxlength'=>'15', 'onkeypress'=>"return pulsar(event,this)"]) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('aktif', 'Status:') }}
                            {{Form::select('status', ['Aktif' => 'Aktif', 'NonAktif' => 'NonAktif'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'stat2','required'=>'required'])}}
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

      <button type="button" class="back2Top btn btn-warning btn-xs" id="back2Top"><i class="fa fa-arrow-up" style="color: #fff"></i> <i>{{$nama_company}}</i> <b>({{ $nama_lokasi }})</b></button>

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
            
            .add-button {
                bottom: 246px;
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
            
            
            <?php if(auth()->user()->kode_company != 02) {?>
            
            @permission('update-alator')
            <a href="#" id="addlokasi"><button type="button" class="btn bg-black btn-xs add-button" data-toggle="modal" data-target="">DETAIL LOKASI<i class="fa fa-plus"></i></button></a>
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editalat" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>
            @endpermission
            
            @permission('delete-alator')
            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapusalat" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>
            @endpermission
            
            <?php }else{ ?>
            
            @permission('update-alator')
            <a href="#" id="addlokasi"><button type="button" class="btn bg-black btn-xs add-button" data-toggle="modal" data-target="">DETAIL LOKASI<i class="fa fa-plus"></i></button></a>
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editalat" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>
            @endpermission
            
            @permission('delete-alator')
            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapusalat" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>
            @endpermission
            
            <?php } ?>
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
            $('#addlokasi').hide();
            $('#editalat').hide();
            $('#hapusalat').hide();
            $('.back2Top').show();
        }

        $(function() {
            $('#alat-table').DataTable({
            "bPaginate": true,
            "bFilter": true,
            "scrollY": 280,
            "scrollX": 400,
            "pageLength":100,
            ajax: '{!! route('alat.data') !!}',
            columns: [
                { data: 'kode_alat', name: 'kode_alat', visible: false },
                { data: 'nama_alat', name: 'nama_alat' },
                { data: 'merk', name: 'merk' },
                { data: 'type', name: 'type' },
                { data: 'kapasitas', name: 'kapasitas' },
                { data: 'tahun', name: 'tahun' },
                { data: 'no_asset_alat', name: 'no_asset_alat' },
                { data: 'status', name: 'status' },
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

        $(document).ready(function(){
            $("#back2Top").click(function(event) {
                event.preventDefault();
                $("html, body").animate({ scrollTop: 0 }, "slow");
                return false;
            });
            
            $('[data-toggle="tooltip"]').tooltip();   

            var table = $('#alat-table').DataTable();

            $('#alat-table tbody').on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected bg-gray') ) {
                    $(this).removeClass('selected bg-gray');
                    $('#addlokasi').hide();
                    $('.hapus-button').hide();
                    $('.edit-button').hide();
                }
                else {
                    table.$('tr.selected').removeClass('selected bg-gray');
                    $(this).addClass('selected bg-gray');
                    var select = $('.selected').closest('tr');
                    var data = $('#alat-table').DataTable().row(select).data();
                    $('#CreateBy1').val(data['created_by']);
                    $('#CreateAt1').val(data['created_at']);
                    $('#UpdateBy1').val(data['updated_by']);
                    $('#UpdateAt1').val(data['updated_at']);
                    var kode_alat = data['kode_alat'];
                    var addmt = $("#addlokasi").attr("href",window.location.href+"/"+kode_alat+"/detaillokasi");
                    $('#addlokasi').show();
                    $('.hapus-button').show();
                    $('.edit-button').show();
                    
                }
            });

            $('#editalat').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#alat-table').DataTable().row(select).data();
                var kode_alat = data['kode_alat'];
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('alat.edit_alat') !!}',
                    type: 'POST',
                    data : {
                        'id': kode_alat
                    },
                    success: function(results) {
                        $('#Kode').val(results.kode_alat);
                        $('#Nama').val(results.nama_alat);
                        $('#Merk').val(results.merk);
                        $('#Type').val(results.type);
                        $('#kapasitas2').val(results.kapasitas);
                        $('#Tahun').val(results.tahun);
                        $('#Asset').val(results.no_asset_alat);
                        $('#Lokasi').val(results.kode_lokasi);
                        $('#stat2').val(results.status).trigger('change');
                        $('#editform').modal('show');
                    }
         
                });
            });

            $('#hapusalat').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#alat-table').DataTable().row(select).data();
                var kode_alat = data['kode_alat'];
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
                            url: '{!! route('alat.hapus_alat') !!}',
                            type: 'POST',
                            data : {
                                'id': kode_alat
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

        function pulsar(e,obj) {            
              tecla = (document.all) ? e.keyCode : e.which;
              //alert(tecla);
              if (tecla!="8" && tecla!="0"){
                obj.value += String.fromCharCode(tecla).toUpperCase();
                return false;
              }else{
                return true;
              }
        }

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

        $('.select2').select2({
            placeholder: "Pilih",
            allowClear: true,
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        function refreshTable() {
             $('#alat-table').DataTable().ajax.reload(null,false);;
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
                    url:'{!! route('alat.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        $('#Nama1').val('');
                        $('#Nama2').val('');
                        $('#Nama3').val('');
                        $('#Nama4').val('').trigger('change');
                        $('#Asset1').val('');
                        $('#kapasitas1').val('');
                        $('#Lokasi1').val('').trigger('change');
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
                    url:'{!! route('alat.ajaxupdate') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
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

        function update() {
         e.preventDefault();
         var form_action = $("#editform").find("form").attr("action");
                $.ajax({
                    
                    url: form_action,
                    type: 'POST',
                    data:$('#Update').serialize(),
                    success: function(data) {
                        $('#editform').modal('hide');
                        $.notify(data.message, "success");
                        refreshTable();
                    }
                });
        }
    </script>
@endpush