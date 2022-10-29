@extends('adminlte::page')

@section('title', 'Customer')

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
                    @permission('create-customeror')
                    <button type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#addform">
                        <i class="fa fa-plus"></i> New Customer</button>
                    @endpermission

                    <span class="pull-right">  
                        <font style="font-size: 16px;"><b>CUSTOMER</b></font>
                    </span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-blue">
                        <th>Kode customer</th>
                        <th>Nama customer</th>
                        <th>Alamat</th>
                        <th>Telp</th>
                        <th>Hp</th>
                        <th>Nama Kontak</th>
                        <th>Npwp</th>
                        <th style="width: 70px;">No Kode Pajak</th>
                        <th>No COA</th>
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
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('Nama customer', 'Nama Customer:') }}
                                    {{ Form::text('nama_customer', null, ['class'=> 'form-control','id'=>'Nama1','required'=>'required', 'placeholder'=>'Contoh: NAMA CUSTOMER, CV','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)",'data-toggle'=>"tooltip",'data-placement'=>"bottom",'title'=>"Contoh: NAMA CUSTOMER, CV"]) }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('Nama Customer', 'Nama Customer PO:') }}
                                    {{ Form::text('nama_customer_po', null, ['class'=> 'form-control','id'=>'Namapo1','required'=>'required', 'placeholder'=>'Contoh: CV. NAMA CUSTOMER','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)",'data-toggle'=>"tooltip",'data-placement'=>"bottom",'title'=>"Contoh: CV. NAMA CUSTOMER"]) }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('alamat', 'Alamat:') }}
                                    {{ Form::textArea('alamat', null, ['class'=> 'form-control','rows'=>'2','id'=>'Alamat1','required'=>'required', 'placeholder'=>'Alamat','autocomplete'=>'off']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('telp', 'Telp:') }}
                                    {{ Form::text('telp', null, ['class'=> 'form-control','id'=>'Telp1', 'placeholder'=>'No. Telepon','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    {{ Form::label('kota', 'Kota:') }}
                                    {{ Form::text('kota', null, ['class'=> 'form-control','id'=>'Kota1', 'placeholder'=>'Kota','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('kodepos', 'Kode Pos:') }}
                                    {{ Form::text('kode_pos', null, ['class'=> 'form-control','id'=>'Kodepos1', 'placeholder'=>'Kode Pos','autocomplete'=>'off', 'maxlength'=>'5','onkeypress'=>"return hanyaAngka(event)"]) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('Fax', 'Fax:') }}
                                    {{ Form::text('fax', null, ['class'=> 'form-control','id'=>'Fax1', 'placeholder'=>'No. FAX','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('Hp', 'Hp:') }}
                                    <input type="text" name="number1" style="display:none;">
                                    {{ Form::text('hp', null, ['class'=> 'form-control','id'=>'Hp1', 'placeholder'=>'No. HP','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)", 'name'=>'hp']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('Npwp', 'Npwp:') }}
                                    <input type="text" name="number" style="display:none;">
                                    {{ Form::text('npwp', null, ['class'=> 'form-control','id'=>'Npwp1', 'placeholder'=>'NPWP','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)", 'name'=>'npwp']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('nama_kontak', 'Nama Kontak:') }}
                                    {{ Form::text('nama_kontak', null, ['class'=> 'form-control','id'=>'Kontak1', 'placeholder'=>'Nama Kontak','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_kode_pajak', 'No Kode Pajak:') }}
                                    {{ Form::text('no_kode_pajak', null, ['class'=> 'form-control','id'=>'kodepajak1','onkeypress'=>"return hanyaAngka(event)", 'placeholder'=>'No Kode Pajak','autocomplete'=>'off']) }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('status', 'Status:') }}
                                    {{Form::select('status', ['Aktif' => 'Aktif', 'NonAktif' => 'NonAktif'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Status1','required'=>'required'])}}
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
                        
                            {{ Form::hidden('id', null, ['class'=> 'form-control','id'=>'Kode','readonly']) }}

                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('Nama customer', 'Nama Customer:') }}
                                    {{ Form::text('nama_customer', null, ['class'=> 'form-control','id'=>'Nama','required'=>'required', 'placeholder'=>'Contoh: NAMA CUSTOMER, CV','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)",'data-toggle'=>"tooltip",'data-placement'=>"bottom",'title'=>"Contoh: NAMA CUSTOMER, CV",'readonly']) }}
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('Nama Customer', 'Nama Customer PO:') }}
                                    {{ Form::text('nama_customer_po', null, ['class'=> 'form-control','id'=>'Namapo','required'=>'required', 'placeholder'=>'Contoh: CV. NAMA CUSTOMER','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)",'data-toggle'=>"tooltip",'data-placement'=>"bottom",'title'=>"Contoh: CV. NAMA CUSTOMER",'readonly']) }}
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('alamat', 'Alamat:') }}
                                    {{ Form::textArea('alamat', null, ['class'=> 'form-control','rows'=>'2','id'=>'Alamat']) }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('telp', 'Telp:') }}
                                    {{ Form::text('telp', null, ['class'=> 'form-control','id'=>'Telp', 'placeholder'=>'No. Telepon','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    {{ Form::label('kota', 'Kota:') }}
                                    {{ Form::text('kota', null, ['class'=> 'form-control','id'=>'Kota', 'placeholder'=>'Kota tinggal','autocomplete'=>'off']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('kode_pos', 'Kode Pos:') }}
                                    {{ Form::text('kode_pos', null, ['class'=> 'form-control','id'=>'Kodepos', 'placeholder'=>'Kode Pos','autocomplete'=>'off']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('Fax', 'Fax:') }}
                                    {{ Form::text('fax', null, ['class'=> 'form-control','id'=>'Fax', 'placeholder'=>'No. FAX','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('Hp', 'Hp:') }}
                                    <input type="text" name="number1" style="display:none;">
                                    {{ Form::text('hp', null, ['class'=> 'form-control','id'=>'Hp','onkeypress'=>"return hanyaAngka(event)", 'name'=>'hp']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('Npwp', 'Npwp:') }}
                                    <input type="text" name="number" style="display:none;">
                                    {{ Form::text('npwp', null, ['class'=> 'form-control','id'=>'Npwp','autocomplete'=>'off', 'name'=>'npwp','onkeypress'=>"return hanyaAngka(event)"]) }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('nama_kontak', 'Nama Kontak:') }}
                                    {{ Form::text('nama_kontak', null, ['class'=> 'form-control','id'=>'Kontak', 'placeholder'=>'Nama Kontak','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_kode_pajak', 'No Kode Pajak:') }}
                                    {{ Form::text('no_kode_pajak', null, ['class'=> 'form-control','id'=>'kodepajak','onkeypress'=>"return hanyaAngka(event)", 'placeholder'=>'No Kode Pajak','autocomplete'=>'off']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('status', 'Status:') }}
                                    {{Form::select('status', ['Aktif' => 'Aktif', 'NonAktif' => 'NonAktif'], null, ['class'=> 'form-control select2','style'=>'width: 100%','id'=>'Status','required'=>'required'])}}
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

        <style type="text/css">

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
            @permission('update-customeror')
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editcustomer" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>
            @endpermission

            @permission('delete-customeror')
            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapuscustomer" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>
            @endpermission
        </div>
</body>
@stop

@push('css')

@endpush
@push('js')
  
    <script type="text/javascript">      
        function load(){
            startTime();
            $('.hapus-button').hide();
            $('.edit-button').hide();
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
        if ((("0123456789").indexOf(keychar) > -1 || ("-").indexOf(keychar) > -1 || (".").indexOf(keychar) > -1 )) {
            return true;
        } else
        if (decimal && (keychar == ".")) {
            return true;
        } else return false;
    }   


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

        $(function() {          
            $('#data-table').DataTable({
            "bPaginate": true,
            "bFilter": true,
            "scrollY": 280,
            "scrollX": 400,
            "pageLength":100,
            ajax: '{!! route('customer.data') !!}',
            columns: [
                { data: 'id', name: 'id', visible: false },
                { data: 'nama_customer', name: 'nama_customer' },
                { data: 'alamat', name: 'alamat' },
                { data: 'telp', name: 'telp', "defaultContent": "<i>Not set</i>" },
                { data: 'hp', name: 'hp', "defaultContent": "<i>Not set</i>" },
                { data: 'nama_kontak', name: 'nama_kontak', "defaultContent": "<i>Not set</i>" },
                { data: 'npwp', name: 'npwp', "defaultContent": "<i>Not set</i>" },
                { data: 'no_kode_pajak', "defaultContent": "<i>Not set</i>" },
                { data: 'coa.account', name: 'coa.account', "defaultContent": "<i>Not set</i>" },
                { data: 'status', name: 'status' },
            ]
            });
        });

        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();   

            $("input[name='npwp']").on("keyup change", function(){
            $("input[name='number']").val(destroyMask(this.value));
                this.value = createMask($("input[name='number']").val());
            })

            $("input[name='hp']").on("keyup change", function(){
            $("input[name='number1']").val(destroyMask2(this.value));
                this.value = createMask2($("input[name='number1']").val());
            })

            function createMask(string){
                return string.replace(/(\d{2})(\d{3})(\d{3})(\d{1})(\d{3})(\d{3})/,"$1.$2.$3.$4-$5.$6");
            }

            function destroyMask(string){
                return string.replace(/\D/g,'').substring(0,15);
            }

            function createMask2(string){
                return string.replace(/(\d{4})(\d{4})(\d{4})/,"$1-$2-$3");
            }

            function destroyMask2(string){
                return string.replace(/\D/g,'').substring(0,12);
            }
            var table = $('#data-table').DataTable();

            $('#data-table tbody').on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected bg-gray text-bold') ) {
                    $(this).removeClass('selected bg-gray text-bold');
                    $('.hapus-button').hide();
                    $('.edit-button').hide();
                    $('#CreateBy1').val('');
                    $('#CreateAt1').val('');
                    $('#UpdateBy1').val('');
                    $('#UpdateAt1').val('');
                }
                else {
                    table.$('tr.selected').removeClass('selected bg-gray text-bold');
                    $(this).addClass('selected bg-gray text-bold');
                    var select = $('.selected').closest('tr');
                    closeOpenedRows(table, select);
                    var data = $('#data-table').DataTable().row(select).data();
                    $('#CreateBy1').val(data['created_by']);
                    $('#CreateAt1').val(data['created_at']);
                    $('#UpdateBy1').val(data['updated_by']);
                    $('#UpdateAt1').val(data['updated_at']);
                    $('.hapus-button').show();
                    $('.edit-button').show();
                    
                }
            });
            
            var openRows = new Array();
            
            function closeOpenedRows(table, selectedRow) {
                $.each(openRows, function (index, openRow) {
                    // not the selected row!
                    if ($.data(selectedRow) !== $.data(openRow)) {
                        var rowToCollapse = table.row(openRow);
                        rowToCollapse.child.hide();
                        openRow.removeClass('shown');
                        var index = $.inArray(selectedRow, openRows);                        
                        openRows.splice(index, 1);
                    }
                });
            }

            $('#editcustomer').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var id = data['id'];
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('customer.edit_customer') !!}',
                    type: 'POST',
                    data : {
                        'id': id
                    },
                    success: function(results) {
                        $('#Kode').val(results.id);
                        $('#Nama').val(results.nama_customer);
                        $('#Namapo').val(results.nama_customer_po);
                        $('#Alamat').val(results.alamat);
                        $('#Telp').val(results.telp);
                        $('#Kota').val(results.kota);
                        $('#Kodepos').val(results.kode_pos);
                        $('#Fax').val(results.fax);
                        $('#Hp').val(results.hp);
                        $('#Npwp').val(results.npwp);
                        $('#Kontak').val(results.nama_kontak);
                        $('#kodepajak').val(results.no_kode_pajak);
                        $('#Status').val(results.status).trigger('change');
                        $('#editform').modal('show');
                        }
         
                });
            });

            $('#hapuscustomer').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var id = data['id'];
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
                            url: '{!! route('customer.hapus_customer') !!}',
                            type: 'POST',
                            data : {
                                'id': id
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
                    url:'{!! route('customer.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        $('#Nama1').val('');
                        $('#Namapo1').val('');
                        $('#Alamat1').val('');
                        $('#Telp1').val('');
                        $('#Kota1').val('');
                        $('#Kodepos1').val('');
                        $('#Fax1').val('');
                        $('#Hp1').val('');
                        $('#Npwp1').val('');
                        $('#Kontak1').val('');
                        $('#kodepajak1').val('');
                        $('#Status1').val('').trigger('change');
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
                    url:'{!! route('customer.ajaxupdate') !!}',
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
    </script>
@endpush