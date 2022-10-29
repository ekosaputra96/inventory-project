@extends('adminlte::page')

@section('title', 'Penjualan')

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
                    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()" >
                            <i class="fa fa-refresh"></i> Refresh</button>

                    @permission('create-penjualan')
                    <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#addform">
                        <i class="fa fa-plus"></i> New Penjualan</button>
                    @endpermission

                    <button type="button" class="btn btn-primary btn-xs" onclick="getkode()">
                        <i class="fa fa-bullhorn"></i> Get New Kode</button>

                    <span class="pull-right"> 
                        <font style="font-size: 16px;"><b>PENJUALAN</b></font>
                    </span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-primary">
                        <th>No Penj</th>
                        <th>Tgl Penj</th>
                        <th>Nama Customer</th>
                        <th>No JO/SP</th>
                        <th>No SPJB</th>
                        <th>No BAST</th>
                        <th>Type Ar</th>
                        <th>Total Item</th>
                        <th>PPN</th>
                        <th>Disc</th>
                        <th>Disc(Rp)</th>
                        <th>Grand Total</th>
                        <th>Kode Lokasi</th>
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
                                    {{ Form::label('tanggal_penjualan', 'Tanggal Penjualan:') }}
                                    {{ Form::date('tanggal_penjualan', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'Tanggal' ,'required'=>'required'])}}
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    {{ Form::label('kode_customer', 'Kode Customer:') }}
                                    {{ Form::select('kode_customer',$Customer->sort(),null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Customer','required'=>'required']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_josp', 'No JO/SP:') }}
                                    {{ Form::text('no_josp', null, ['class'=> 'form-control','id'=>'Josp', 'placeholder'=>'No. JO/SP', 'autocomplete'=>'off']) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_spjb', 'No SPJB:') }}
                                    {{ Form::text('no_spjb', null, ['class'=> 'form-control','id'=>'Spjb', 'placeholder'=>'No. SPJB', 'autocomplete'=>'off']) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('tgl_spjb', 'Tanggal SPJB:') }}
                                    {{ Form::date('tgl_spjb', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'Tanggalspjb' ,'required'=>'required'])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_bast', 'No BAST:') }}
                                    {{ Form::text('no_bast', null, ['class'=> 'form-control','id'=>'Bast', 'placeholder'=>'No. BAST', 'autocomplete'=>'off']) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('tgl_bast', 'Tanggal BAST:') }}
                                    {{ Form::date('tgl_bast', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'Tanggalbast' ,'required'=>'required'])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('type_ar', 'Type AR:') }}
                                    {{ Form::select('type_ar', ['Sparepart' => 'Sparepart','Unit' => 'Unit','Jasa' => 'Jasa'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Typear','required'=>'required'])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('seri_faktur', 'Seri Faktur:') }}
                                    {{ Form::text('seri_faktur', null, ['class'=> 'form-control','id'=>'Seri', 'placeholder'=>'Seri Faktur', 'autocomplete'=>'off']) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('top', 'TOP:') }}
                                    {{ Form::text('top', null, ['class'=> 'form-control','id'=>'Top','onchange'=>"hitung();",'required'=>'required', 'placeholder'=>'Term Of Payment', 'autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('due_date', 'Due Date:') }}
                                    {{ Form::text('due_date', null, ['class'=> 'form-control','id'=>'Due','required'=>'required', 'placeholder'=>'Tanggal Tenggat','readonly']) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_sertifikat', 'Sertifikat:') }}
                                    {{ Form::text('no_sertifikat', null, ['class'=> 'form-control','id'=>'Sertifikat', 'placeholder'=>'No Sertifikat', 'autocomplete'=>'off']) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('tgl_sertifikat', 'Tanggal Sertifikat:') }}
                                    {{ Form::date('tgl_sertifikat', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'Tanggalsertifikat' ,'required'=>'required'])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('PPN', 'PPN(%):') }}
                                    {{ Form::text('ppn', 0, ['class'=> 'form-control','id'=>'PPN1','placeholder'=>'%', 'autocomplete'=>'off','required']) }}
                                 </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('Diskon', 'Diskon(%):') }}
                                    {{ Form::text('diskon_persen', 0, ['class'=> 'form-control','id'=>'Diskon1','placeholder'=>'%', 'autocomplete'=>'off','required']) }}
                                 </div>
                            </div> 
                            <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('Diskon', 'Diskon(Rp):') }}
                                        {{ Form::text('diskon_rp', 0, ['class'=> 'form-control','id'=>'Diskonrp1', 'autocomplete'=>'off','required']) }}
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
                            <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('no_penjualan', 'No Penjualan:') }}
                                        {{ Form::text('no_penjualan', null, ['class'=> 'form-control','id'=>'Penjualan1','readonly']) }}
                                    </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('tanggal_penjualan', 'Tanggal Penjualan:') }}
                                    {{ Form::date('tanggal_penjualan', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'Tanggal1' ,'required'=>'required'])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('kode_customer', 'Kode Customer:') }}
                                    {{ Form::select('kode_customer',$Customer->sort(),null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'Customer1','required'=>'required']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_josp', 'No JO/SP:') }}
                                    {{ Form::text('no_josp', null, ['class'=> 'form-control','id'=>'Josp1', 'placeholder'=>'No. JO/SP', 'autocomplete'=>'off']) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_spjb', 'No SPJB:') }}
                                    {{ Form::text('no_spjb', null, ['class'=> 'form-control','id'=>'Spjb1', 'placeholder'=>'No. SPJB', 'autocomplete'=>'off']) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('tgl_spjb', 'Tanggal SPJB:') }}
                                    {{ Form::date('tgl_spjb', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'Tanggalspjb1' ,'required'=>'required'])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_bast', 'No BAST:') }}
                                    {{ Form::text('no_bast', null, ['class'=> 'form-control','id'=>'Bast1', 'placeholder'=>'No. BAST', 'autocomplete'=>'off']) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('tgl_bast', 'Tanggal BAST:') }}
                                    {{ Form::date('tgl_bast', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'Tanggalbast1' ,'required'=>'required'])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('type_ar', 'Type AR:') }}
                                    {{ Form::text('type_ar', null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'Typear1','required'=>'required','readonly'])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('seri_faktur', 'Seri Faktur:') }}
                                    {{ Form::text('seri_faktur', null, ['class'=> 'form-control','id'=>'Seri1', 'placeholder'=>'Seri Faktur', 'autocomplete'=>'off']) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('top', 'TOP:') }}
                                    {{ Form::text('top', null, ['class'=> 'form-control','id'=>'Top1','onchange'=>"hitung2();",'required'=>'required', 'placeholder'=>'Term Of Payment', 'autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('due_date', 'Due Date:') }}
                                    {{ Form::text('due_date', null, ['class'=> 'form-control','id'=>'Due1','required'=>'required', 'placeholder'=>'Tanggal Tenggat','readonly']) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_sertifikat', 'Sertifikat:') }}
                                    {{ Form::text('no_sertifikat', null, ['class'=> 'form-control','id'=>'Sertifikat1', 'placeholder'=>'No Sertifikat', 'autocomplete'=>'off']) }}
                                 </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('tgl_sertifikat', 'Tanggal Sertifikat:') }}
                                    {{ Form::date('tgl_sertifikat', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'Tanggalsertifikat1' ,'required'=>'required'])}}
                                </div>
                            </div>
                             <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('PPN', 'PPN(%):') }}
                                        {{ Form::text('ppn', null, ['class'=> 'form-control','id'=>'PPN', 'autocomplete'=>'off','required']) }}
                                     </div>
                            </div> 
                            <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('Diskon', 'Diskon(%):') }}
                                        {{ Form::text('diskon_persen', null, ['class'=> 'form-control','id'=>'Diskon', 'autocomplete'=>'off','required']) }}
                                     </div>
                            </div> 
                            <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('Diskon', 'Diskon(Rp):') }}
                                        {{ Form::text('diskon_rp', null, ['class'=> 'form-control','id'=>'Diskonrp', 'autocomplete'=>'off','required']) }}
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
            .add-button {
                background-color: #00E0FF;
                bottom: 56px;
            }

            .hapus-button {
                background-color: #F63F3F;
                bottom: 86px;
            }

            .edit-button {
                background-color: #FDA900;
                bottom: 116px;
            }

            .tombol1 {
                background-color: #149933;
                bottom: 156px;
            }

            .tombol2 {
                background-color: #ff9900;
                bottom: 156px;
            }

            .view-button {
                background-color: #1674c7;
                bottom: 186px;
            }

            .print-button {
                background-color: #F63F3F;
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
            @permission('update-penjualan')
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editpenjualan" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>
            @endpermission

            @permission('delete-penjualan')
            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapuspenjualan" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>
            @endpermission

            @permission('add-penjualan')
            <a href="#" id="addpenjualan"><button type="button" class="btn btn-info btn-xs add-button" data-toggle="modal" data-target="">ADD <i class="fa fa-plus"></i></button></a>
            @endpermission

            @permission('post-penjualan')
            <button type="button" class="btn btn-success btn-xs tombol1" id="button1">POST <i class="fa fa-bullhorn"></i></button>
            @endpermission

            @permission('unpost-penjualan')
            <button type="button" class="btn btn-warning btn-xs tombol2" id="button2">UNPOST <i class="fa fa-undo"></i></button>
            @endpermission

            @permission('view-penjualan')
            <button type="button" class="btn btn-primary btn-xs view-button" id="button5">VIEW <i class="fa fa-eye"></i></button>
            @endpermission

            @permission('print-penjualan')
            <a href="#" target="_blank" id="printpenjualan"><button type="button" class="btn btn-danger btn-xs print-button" id="button6">PRINT <i class="fa fa-print"></i></button></a>
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
        
        var x = 0;
        function showTime(){
            x = x + 1;
            if (x >= 10000){
                x = 0;
                refreshTable();
            }
        }
        setInterval(showTime, 1);
        
        $(document).ready(function() {
            $("#back2Top").click(function(event) {
                event.preventDefault();
                $("html, body").animate({ scrollTop: 0 }, "slow");
                return false;
            });

        });

        function load(){
            startTime();
            $('.tombol1').hide();
            $('.tombol2').hide();
            $('.add-button').hide();
            $('.hapus-button').hide();
            $('.edit-button').hide();
            $('.print-button').hide();
            $('.view-button').hide();
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
                        url:'{!! route('penjualan.getkode') !!}',
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

    function formatRupiah(angka, prefix='Rp'){
           
            var rupiah = angka.toLocaleString(
                undefined, // leave undefined to use the browser's locale,
                // or use a string like 'en-US' to override it.
                { minimumFractionDigits: 0 }
            );
            return rupiah;
           
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

    $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();   
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
    
        function hitung() {
             var tgl_awal = $('#Tanggal').val();
             var hari = $('#Top').val();

             var hasil = new Date(new Date().getTime()+(hari*24*60*60*1000)); 

             var newdate = new Date(hasil);
             var dd = newdate.getDate();
             var mm = newdate.getMonth() + 1;
             var y = newdate.getFullYear();

             var someFormattedDate = y + '-' + mm + '-' + dd;
             document.getElementById('Due').value = someFormattedDate;
        }

        function hitung2() {
             var tgl_awal = $('#Tanggal1').val();
             var hari = $('#Top1').val();

             var hasil = new Date(new Date().getTime()+(hari*24*60*60*1000)); 

             var newdate = new Date(hasil);
             var dd = newdate.getDate();
             var mm = newdate.getMonth() + 1;
             var y = newdate.getFullYear();

             var someFormattedDate = y + '-' + mm + '-' + dd;
             document.getElementById('Due1').value = someFormattedDate;
        }

        $(function() {          
            $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('penjualan.data') !!}',
            fnRowCallback: function (row, data, iDisplayIndex, iDisplayIndexFull) {
                if (data['status'] == "OPEN") {
                    $('td', row).css('background-color', '#ffdbd3');
                }else if(data['total_retur'] > 0){
                    $('td', row).css('background-color', '#FFF3B0');
                }
            },
            columns: [
                { data: 'no_penjualan', name: 'no_penjualan' },
                { data: 'tanggal_penjualan', name: 'tanggal_penjualan' },
                { data: 'customer.nama_customer', name: 'customer.nama_customer' },
                { data: 'no_josp', name: 'no_josp' },
                { data: 'no_spjb', name: 'no_spjb' },
                { data: 'no_bast', name: 'no_bast' },
                { data: 'type_ar', name: 'type_ar' },
                { data: 'total_item', name: 'total_item' },
                { data: 'ppn', name: 'ppn' },
                { data: 'diskon_persen', name: 'diskon_persen' },
                { data: 'diskon_rp', name: 'diskon_rp' },
                { data: 'grand_total', 
                    render: function( data, type, full ) {
                    return formatNumber(data); }
                },
                { data: 'kode_lokasi', 
                    render: function( data, type, full ) {
                    return formatNomor(data); }
                },
                { data: 'status', 
                    render: function( data, type, full ) {
                    return formatStatus(data); }
                },
            ]
            });
        });

        function formatStatus(n) {
            console.log(n);
            if(n == 'OPEN'){
                return n;
            }else if(n == 'RETUR'){
                var stat = "<span style='color:#c91a1a'><b>RETUR</b></span>";
                return n.replace(/RETUR/, stat);
            }else{
                var stat = "<span style='color:#0eab25'><b>POSTED</b></span>";
                return n.replace(/POSTED/, stat);
            }
        }

        function formatNumber(n) {
            if(n == 0){
                return 0;
            }else{
                return n.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
        }

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

        function createTable(result){

        var total_qty = 0;
        var total_pakai = 0;
        var total_harga = 0;
        var grand_total = 0;

        $.each( result, function( key, row ) {
            total_qty += row.qty;
            harga = row.harga;
            qty = row.qty;
            total_pakai = harga * qty;
            total_harga += total_pakai;
            grand_total = formatRupiah(total_harga);

        });

        var my_table = "";

        $.each( result, function( key, row ) {
                    my_table += "<tr>";
                    my_table += "<td>"+row.produk+"</td>";
                    my_table += "<td>"+row.partnumber+"</td>";
                    my_table += "<td>"+row.satuan+"</td>";
                    my_table += "<td>"+row.qty+"</td>";
                    my_table += "<td>"+row.qty_retur+"</td>";
                    my_table += "<td>Rp "+formatRupiah(row.harga)+"</td>";
                    my_table += "<td>Rp "+row.subtotal+"</td>";
                    my_table += "</tr>";
            });

            my_table = '<table id="table-fixed" class="table table-bordered table-hover" cellpadding="5" cellspacing="0" border="1" style="padding-left:50px; font-size:12px">'+ 
                        '<thead>'+
                           ' <tr class="bg-info">'+
                                '<th>Produk</th>'+
                                '<th>Partnumber</th>'+
                                '<th>Satuan</th>'+
                                '<th>Qty</th>'+
                                '<th>Qty Retur</th>'+
                                '<th>Harga</th>'+
                                '<th>Subtotal</th>'+
                            '</tr>'+
                        '</thead>'+
                        '<tbody>' + my_table + '</tbody>'+
                       ' <tfoot>'+
                            '<tr class="bg-info">'+
                                '<th class="text-center" colspan="3">Total</th>'+
                                '<th>'+total_qty+'</th>'+
                                '<th></th>'+
                                '<th></th>'+
                                '<th>Rp '+grand_total+'</th>'+
                            '</tr>'+
                            '</tfoot>'+
                        '</table>';

                    // $(document).append(my_table);
            
            console.log(my_table);
            return my_table;
            // mytable.appendTo("#box");           
        
        }

        $(document).ready(function() {
            var table = $('#data-table').DataTable();
            var post = document.getElementById("button1");
            var unpost = document.getElementById("button2");

            $('#data-table tbody').on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected bg-gray text-bold') ) {
                    $(this).removeClass('selected bg-gray text-bold');
                    $('.tombol1').hide();
                    $('.tombol2').hide();
                    $('.add-button').hide();
                    $('.hapus-button').hide();
                    $('.edit-button').hide();
                    $('.print-button').hide();
                    $('.view-button').hide();
                }
                else {
                    table.$('tr.selected').removeClass('selected bg-gray text-bold');
                    $(this).addClass('selected bg-gray text-bold');
                    var select = $('.selected').closest('tr');
                    
                    closeOpenedRows(table, select);
                    
                    var data = $('#data-table').DataTable().row(select).data();
                    
                    var no_penjualan = data['no_penjualan']);
                    var add = $("#addpenjualan").attr("href","http://localhost/gui_inventory_laravel/admin/penjualan/"+no_penjualan+"/detail");
                    var print = $("#printpenjualan").attr("href","http://localhost/gui_inventory_laravel/admin/penjualan/exportpdf?no_penjualan="+no_penjualan);
                    var status = data['status'];
                    var item = data['total_item'];
                    if(status == 'POSTED' && item > 0){
                        $('.tombol1').hide();
                        $('.tombol2').show();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').show();
                        $('.view-button').show();
                    }else if(status =='OPEN' && item > 0){
                        $('.tombol1').show();
                        $('.tombol2').hide();
                        $('.add-button').show();
                        $('.hapus-button').hide();
                        $('.edit-button').show();
                        $('.print-button').hide();
                        $('.view-button').show();
                    }else if(status =='OPEN' && item == 0){
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.add-button').show();
                        $('.hapus-button').show();
                        $('.edit-button').show();
                        $('.print-button').hide();
                        $('.view-button').hide();
                    }else if(status == 'RETUR'){
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').hide();
                        $('.view-button').show();
                    }
                }
            } );            
        
           $('#button1').click( function () {
                var select = $('.selected').closest('tr');
                var colom = select.find('td:eq(0)').text();
                var no_penjualan = colom;
                console.log(no_penjualan);
                swal({
                    title: "Post?",
                    text: colom,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Posting!",
                    cancelButtonText: "Batal",
                    reverseButtons: !0
                    }).then(function (e) {
                        
                        if (e.value === true) {
                            swal({
                            title: "<b>Proses Sedang Berlangsung</b>",
                            type: "warning",
                            showCancelButton: false,
                            showConfirmButton: false,
                            allowOutsideClick: false
                            })
                            
                        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                // alert( table.rows('.selected').data().length +' row(s) selected' );
                        $.ajax({
                            url: '{!! route('penjualan.posting') !!}',
                            type: 'POST',
                            data : {
                                'id': no_penjualan
                            },
                            success: function(result) {
                                console.log(result);
                                if (result.success === true) {
                                    swal(
                                    'Posted!',
                                    'Your file has been posted.',
                                    'success'
                                    )
                                    refreshTable();
                                }
                                else if (result.success === false) {
                                  swal({
                                      title: 'Error',
                                      text: result.message,
                                      type: 'error',
                                  })
                                    refreshTable();
                                }
                                else{
                                  swal({
                                      title: 'Error',
                                      text: 'POSTING gagal! Stok tidak cukup.',
                                      type: 'error',
                                  })
                                    refreshTable();
                                }
                            },
                            error : function () {
                              swal({
                                  title: 'Oops...',
                                  text: 'Gagal',
                                  type: 'error',
                                  timer: '1500'
                              })
                            }
                        });
                    } else {
                        e.dismiss;
                    }

                }, function (dismiss) {
                    return false;
                })
            });

            $('#button2').click( function () {
                var select = $('.selected').closest('tr');
                var colom = select.find('td:eq(0)').text();
                var no_penjualan = colom;
                console.log(no_penjualan);
                swal({
                    title: "Unpost?",
                    text: colom,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Unpost!",
                    cancelButtonText: "Batal",
                    reverseButtons: !0
                    }).then(function (e) {
                        if (e.value === true) {
                            swal({
                            title: "<b>Proses Sedang Berlangsung</b>",
                            type: "warning",
                            showCancelButton: false,
                            showConfirmButton: false,
                            allowOutsideClick: false
                            })
                            
                        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                        $.ajax({
                            url: '{!! route('penjualan.unposting') !!}',
                            type: 'POST',
                            data : {
                                'id': no_penjualan
                            },
                            success: function(result) {
                                console.log(result);
                                if (result.success === true) {
                                    swal(
                                    'Unposted!',
                                    'Data berhasil di Unpost.',
                                    'success'
                                    )
                                    refreshTable();
                                }
                                else{
                                  swal({
                                      title: 'Error',
                                      text: result.message,
                                      type: 'error',
                                  })
                                    refreshTable();
                                }
                            },
                            error : function () {
                              swal({
                                  title: 'Oops...',
                                  text: data.message,
                                  type: 'error',
                                  timer: '1500'
                              })
                            }
                        });
                    } else {
                        e.dismiss;
                    }

                }, function (dismiss) {
                    return false;
                })
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

            $('#button5').click( function () {
                var select = $('.selected').closest('tr');
                var no_penjualan = select.find('td:eq(0)').text();
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('penjualan.showdetail') !!}',
                    type: 'POST',
                    data : {
                        'id': no_penjualan
                    },
                    success: function(result) {
                        console.log(result);
                        if(result.title == 'Gagal'){
                            $.notify(result.message);
                        }else{
                            if ( row.child.isShown() ) {
                                row.child.hide();
                                select.removeClass('shown');
                            }
                            else {
                                closeOpenedRows(table, select);

                                row.child( createTable(result) ).show();
                                select.addClass('shown');

                                openRows.push(select);
                            }
                        }
                    }
                });
            });

            $('#editpenjualan').click( function () {
                var select = $('.selected').closest('tr');
                var no_penjualan = select.find('td:eq(0)').text();
                var row = table.row( select );
                console.log(no_penjualan);
                $.ajax({
                    url: '{!! route('penjualan.edit_penjualan') !!}',
                    type: 'POST',
                    data : {
                        'id': no_penjualan
                    },
                    success: function(results) {
                        console.log(results);
                        $('#editform').modal('show');
                        $('#Penjualan1').val(results.no_penjualan);
                        $('#Tanggal1').val(results.tanggal_penjualan);
                        $('#Customer1').val(results.kode_customer);
                        $('#Josp1').val(results.no_josp);
                        $('#Spjb1').val(results.no_spjb);
                        $('#Tanggalspjb1').val(results.tgl_spjb);
                        $('#Bast1').val(results.no_bast);
                        $('#Tanggalbast1').val(results.tgl_bast);
                        $('#Typear1').val(results.type_ar);
                        $('#Seri1').val(results.seri_faktur);
                        $('#Top1').val(results.top);
                        $('#Due1').val(results.due_date);
                        $('#Sertifikat1').val(results.no_sertifikat);
                        $('#Tanggalsertifikat1').val(results.tgl_sertifikat);
                        $('#PPN').val(results.ppn);
                        $('#Diskon').val(results.diskon_persen);
                        $('#Diskonrp').val(results.diskon_rp);
                        }
         
                });
            });

            $('#hapuspenjualan').click( function () {
                var select = $('.selected').closest('tr');
                var no_penjualan = select.find('td:eq(0)').text();
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
                            url: '{!! route('penjualan.hapus_penjualan') !!}',
                            type: 'POST',
                            data : {
                                'id': no_penjualan
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
            $('#data-table').DataTable().ajax.reload(null,false);
            $('.tombol1').hide();
            $('.tombol2').hide();
            $('.add-button').hide();
            $('.hapus-button').hide();
            $('.edit-button').hide();
            $('.print-button').hide();
            $('.view-button').hide();
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
                    url:'{!! route('penjualan.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#Tanggal').val('');
                        $('#Customer').val('').trigger('change');
                        $('#Josp').val('');
                        $('#Spjb').val('');
                        $('#Tanggalspjb').val('');
                        $('#Bast').val('');
                        $('#Tanggalbast').val('');
                        $('#Typear').val('').trigger('change');
                        $('#Seri').val('');
                        $('#Top').val('');
                        $('#Due').val('');
                        $('#Sertifikat').val('');
                        $('#Tanggalsertifikat').val('');
                        $('#PPN1').val('0');
                        $('#Diskon1').val('0');
                        $('#Diskonrp1').val('0');
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
                    url:'{!! route('penjualan.ajaxupdate') !!}',
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

        // function edit(id, url) {
            
        //         var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        //        $.ajax({
        //             type: 'GET',
        //             url: url,
        //             data: {_token: CSRF_TOKEN},
        //             dataType: 'JSON',
        //             success: function (results) {
        //                 console.log(results);
        //                 $('#editform').modal('show');
        //                 $('#Penjualan1').val(results.no_penjualan);
        //                 $('#Tanggal1').val(results.tanggal_penjualan);
        //                 $('#Customer1').val(results.kode_customer);
        //                 $('#Josp1').val(results.no_josp);
        //                 $('#Spjb1').val(results.no_spjb);
        //                 $('#Tanggalspjb1').val(results.tgl_spjb);
        //                 $('#Bast1').val(results.no_bast);
        //                 $('#Tanggalbast1').val(results.tgl_bast);
        //                 $('#Typear1').val(results.type_ar);
        //                 $('#Seri1').val(results.seri_faktur);
        //                 $('#Top1').val(results.top);
        //                 $('#Due1').val(results.due_date);
        //                 $('#Sertifikat1').val(results.no_sertifikat);
        //                 $('#Tanggalsertifikat1').val(results.tgl_sertifikat);
        //                 $('#PPN').val(results.ppn);
        //                 $('#Diskon').val(results.diskon_persen);
        //                 $('#Diskonrp').val(results.diskon_rp);
        //                 },
        //                 error : function() {
        //                 swal("GAGAL!<br><b>Status POSTED/RECEIVED/CLOSED</b>");
        //             }
        //         });           
        // }
        
        function update() {
         e.preventDefault();
         alert('test update');
         var form_action = $("#editform").find("form").attr("action");
                $.ajax({
                    
                    url: form_action,
                    type: 'POST',
                    data:$('#Update').serialize(),
                    success: function(data) {
                        console.log(data);
                        $('#editform').modal('hide');
                        $.notify(data.message, "success");
                        refreshTable();
                    }
                });
        }

        // function del(id, url) {
        //     swal({
        //     title: "Hapus?",
        //     text: "Pastikan dahulu item yang akan di hapus",
        //     type: "warning",
        //     showCancelButton: !0,
        //     confirmButtonText: "Ya, Hapus!",
        //     cancelButtonText: "Batal!",
        //     reverseButtons: !0
        //     }).then(function (e) {
        //     if (e.value === true) {
                

        //         $.ajax({
        //             type: 'DELETE',
        //             url: url,
                    
        //             success: function (results) {
        //             // console.log(results);
        //                 if (results.success === true) {
        //                     swal("Berhasil!", results.message, "success");
        //                 } else {
        //                     swal("Gagal!", results.message, "error");
        //                 }
        //                 refreshTable();
        //             }
        //         });
        //     }
        //     });
        // }
    </script>
@endpush