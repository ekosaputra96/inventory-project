@extends('adminlte::page')

@section('title', 'Mobil')

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
                    <?php if(auth()->user()->kode_company == 02) {?>
                    @permission('create-mobilor')
                    <button type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#addform">
                        <i class="fa fa-plus"></i> New Mobil</button>
                    @endpermission
                    <?php }else{ ?>
                    @permission('create-mobilor')
                    <button type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#addform">
                        <i class="fa fa-plus"></i> New Mobil</button>
                    @endpermission
                    <?php } ?>
                    
                    <!--<button type="button" class="btn btn-warning btn-xs" onclick="getkode()">-->
                    <!--    <i class="fa fa-bullhorn"></i> Get New Kode</button>-->
                    

                    <span class="pull-right">  
                        <a href="http://localhost/gui_inventory_laravel/admin/mobil/exportexcel?" target="_blank" id="printpembelian"><button type="button" class="btn bg-black btn-xs"><i class="fa fa-print"></i> CETAK LIST MOBIL</button></a>
                        <font style="font-size: 16px;"><b>MOBIL</b></font>
                    </span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-blue">
                        <th>Kode Mobil</th>
                        <th>Nopol</th>
                        <th>Jenis Mobil</th>
                        <th>Tahun</th>
                        <th>No Asset</th>
                        <th>Status</th>
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
                                    {{ Form::label('nopol', 'Nopol:') }}
                                    {{ Form::text('nopol', null, ['class'=> 'form-control','id'=>'Nopol1', 'placeholder'=>'No. Polisi','required'=>'required','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('kode_jenis_mobil', 'Jenis Mobil:') }}
                                    {{ Form::select('kode_jenis_mobil', $JenisMobil, null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Jenis1','required'=>'required']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('tahun', 'Tahun:') }}
                                    {{ Form::selectYear('tahun', 2000, 2020, null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Nama4','required'=>'required', 'autocomplete'=>''])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_asset', 'No Asset:') }}
                                    {{ Form::text('no_asset_mobil', null, ['class'=> 'form-control','id'=>'Asset1', 'placeholder'=>'No. Asset','required'=>'required','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('status_mobil', 'Status:') }}
                                    {{Form::select('status_mobil', ['Aktif' => 'Aktif', 'NonAktif' => 'NonAktif'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Stat1','required'=>'required'])}}
                                </div>
                            </div>
                            <div class="col-md-12">
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
                            
                            {{ Form::hidden('kode_mobil', null, ['class'=> 'form-control','id'=>'Kode','readonly']) }}
                               
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('nopol', 'Nopol:') }}
                                    {{ Form::text('nopol', null, ['class'=> 'form-control','id'=>'Nopol','required'=>'required','autocomplete'=>'off']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('kode_jenis_mobil', 'Jenis Mobil:') }}
                                    {{ Form::select('kode_jenis_mobil',$JenisMobil,null, ['class'=> 'form-control select2','id'=>'Jenis','required','placeholder'=>'','style'=>'width: 100%']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('tahun', 'Tahun:') }}
                                    {{ Form::selectYear('tahun', 2000, 2020, null, ['class'=> 'form-control','id'=>'Tahun','required'=>'required', 'autocomplete'=>'off'])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_asset', 'No Asset:') }}
                                    {{ Form::text('no_asset_mobil', null, ['class'=> 'form-control','id'=>'Asset','required'=>'required','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)", 'readonly']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('status_mobil', 'Status:') }}
                                    {{Form::select('status_mobil', ['Aktif' => 'Aktif', 'NonAktif' => 'NonAktif'], null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'Stat','required'=>'required'])}}
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
            
            <?php if(auth()->user()->kode_company == 02) {?>
            
            @permission('update-mobilora')
            <a href="#" id="addlokasi"><button type="button" class="btn bg-black btn-xs add-button" data-toggle="modal" data-target="">EDIT LOKASI<i class="fa fa-plus"></i></button></a>
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editmobil" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>
            @endpermission

            @permission('delete-mobilora')
            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapusmobil" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>
            @endpermission
            
            <?php }else{ ?>
            
            @permission('update-mobilor')
            <a href="#" id="addlokasi"><button type="button" class="btn bg-black btn-xs add-button" data-toggle="modal" data-target="">EDIT LOKASI<i class="fa fa-plus"></i></button></a>
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editmobil" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>
            @endpermission

            @permission('delete-mobilor')
            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapusmobil" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>
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
            $('#editmobil').hide();
            $('#hapusmobil').hide();
            $('.back2Top').show();
        }

        function getkode(){
            swal({
                title: "Get New Kode?",
                text: "New Kode",
                type: "warning",
                showCancelButton: !0,
                confirmButtonText: "Ya, Update!",
                cancelButtonText: "Batal",
                reverseButtons: !0
            }).then(function (e) {
                if (e.value === true) {
                    swal({
                        title: "<b>Proses Sedang Berlangsung</b>",
                        type: "warning",
                        showCancelButton: false,
                        showConfirmButton: false
                    })
                                
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url:'{!! route('mobil.getkode') !!}',
                        type:'POST',
                        success: function(result) {
                            swal("Berhasil!", result.message, "success");
                            refreshTable();
                        },
                    });
                } else {
                    e.dismiss;
                }
            }, function (dismiss) {
                return false;
            })
        }
        
        $(function() {
            $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('mobil.data') !!}',
            columns: [
                { data: 'kode_mobil', name: 'kode_mobil', visible: false },
                { data: 'nopol', name: 'nopol' },
                { data: 'jenismobil.nama_jenis_mobil', name: 'jenismobil.nama_jenis_mobil' },
                { data: 'tahun', name: 'tahun' },
                { data: 'no_asset_mobil', name: 'no_asset_mobil' },
                { data: 'status_mobil', name: 'status_mobil' },
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

            var table = $('#data-table').DataTable();

            $('#data-table tbody').on( 'click', 'tr', function () {
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
                    var data = $('#data-table').DataTable().row(select).data();
                    var kode_mobil = data['kode_mobil'];
                    var addmt = $("#addlokasi").attr("href",window.location.href+"/"+kode_mobil+"/detaillokasi");
                    $('#addlokasi').show();
                    $('.hapus-button').show();
                    $('.edit-button').show();
                    
                }
            });

            $('#editmobil').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var kode_mobil = data['kode_mobil'];
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('mobil.edit_mobil') !!}',
                    type: 'POST',
                    data : {
                        'id': kode_mobil
                    },
                    success: function(results) {
                        console.log(results);
                        $('#Kode').val(results.kode_mobil);
                        $('#Nopol').val(results.nopol);
                        $('#Jenis').val(results.kode_jenis_mobil).trigger('change');
                        $('#Tahun').val(results.tahun);
                        $('#Asset').val(results.no_asset_mobil);
                        $('#Lokasi').val(results.kode_lokasi);
                        $('#Stat').val(results.status_mobil);
                        $('#editform').modal('show');
                        }
         
                });
            });

            $('#hapusmobil').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var kode_mobil = data['kode_mobil'];
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
                            url: '{!! route('mobil.hapus_mobil') !!}',
                            type: 'POST',
                            data : {
                                'id': kode_mobil
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

        $('.select2').select2({
            placeholder: "Silahkan Pilih",
            allowClear: true,
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        function refreshTable() {
             $('#data-table').DataTable().ajax.reload(null,false);;
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
                    url:'{!! route('mobil.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#Nopol1').val('');
                        $('#Jenis1').val('').trigger('change');
                        $('#Nama4').val('').trigger('change');
                        $('#Asset1').val('');
                        $('#Lokasi1').val('').trigger('change');
                        $('#Stat1').val('').trigger('change');
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

            // Check if empty of not            
                $.ajax({
                    url:'{!! route('mobil.ajaxupdate') !!}',
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