@extends('adminlte::page')

@section('title', 'Retur Pembelian')

@section('content_header')

@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
@include('sweet::alert')
<body onLoad="load()">
    <div class="box box-solid">
        <div class="box-body">
            <div class="box">
                <div class="box-body">
                    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()" >
                            <i class="fa fa-refresh"></i> Refresh</button>

                    @permission('create-returpembelian')
                    <button type="button" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#addform">
                        <i class="fa fa-plus"></i> New Retur Pembelian</button>
                    @endpermission

                    @permission('post-getkode')
                    <button type="button" class="btn btn-primary btn-xs" onclick="getkode()">
                        <i class="fa fa-bullhorn"></i> Get New Kode</button>
                    @endpermission

                    <span class="pull-right"> 
                        <font style="font-size: 16px;"><b>RETUR PEMBELIAN</b></font>
                    </span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-primary">
                        <th>No Retur Pembelian</th>
                        <th>Tanggal Retur Pembelian</th>
                        <th>No Penerimaan</th>
                        <th>No Pembelian</th>
                        <th>Vendor</th>
                        <th>Keterangan</th>
                        <th>Total Item</th>
                        <th>Status</th>
                        <th>No Journal</th>
                    </tr>
                    </thead>
                </table>
            </div>
            <div class="col-md-12">
            </div>
            <div class="col-sm-2">
                {{ Form::label('texx', 'Created By:') }}
                {{ Form::text('created_by', null, ['class'=> 'form-control','id'=>'CreateBy1','readonly']) }}
            </div>
            <div class="col-md-2">
                {{ Form::label('texx', 'Created At:') }}
                {{ Form::text('created_at', null, ['class'=> 'form-control','id'=>'CreateAt1','readonly']) }}
            </div>
            <div class="col-md-2">
                {{ Form::label('texx', 'Updated By:') }}
                {{ Form::text('updated_by', null, ['class'=> 'form-control','id'=>'UpdateBy1','readonly']) }}
            </div>
            <div class="col-md-2">
                {{ Form::label('texx', 'Updated At:') }}
                {{ Form::text('updated_at', null, ['class'=> 'form-control','id'=>'UpdateAt1','readonly']) }}
            </div>
            <div class="col-md-2">
                {{ Form::label('texx', 'Posted By:') }}
                {{ Form::text('posted_by', null, ['class'=> 'form-control','id'=>'PostedBy1','readonly']) }}
            </div>
            <div class="col-md-2">
                {{ Form::label('texx', 'Unpost By:') }}
                {{ Form::text('unpost_by', null, ['class'=> 'form-control','id'=>'UnpostBy1','readonly']) }}
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
                                    {{ Form::label('tanggal_returpembelian', 'Tanggal Retur Pembelian:') }}
                                    {{ Form::date('tanggal_returpembelian', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'Tanggal','required'=>'required'])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_penerimaan', 'No Penerimaan:') }}
                                    {{ Form::select('no_penerimaan',$Penerimaan,null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Penerimaan','required'=>'required','onchange'=>'getpo();']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('no_pembelian', 'No Pembelian:') }}
                                    {{ Form::text('no_pembelian',null, ['class'=> 'form-control','id'=>'Pembelian','readonly']) }}
                                </div>
                            </div>
                            {{ Form::hidden('kode_vendor',null, ['class'=> 'form-control','id'=>'Vendor','readonly']) }}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <br>
                                    {{ Form::label('keterangan', 'Keterangan:') }}
                                    {{ Form::textArea('keterangan', null, ['class'=> 'form-control','rows'=>'4','id'=>'Keterangan', 'placeholder'=>'Keterangan', 'autocomplete'=>'off','required', 'onkeypress'=>"return pulsar(event,this)"]) }}
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
                                        {{ Form::label('no_returpembelian', 'No Retur Pembelian:') }}
                                        {{ Form::text('no_returpembelian', null, ['class'=> 'form-control','id'=>'Returbeli1','readonly']) }}
                                    </div>
                                </div> 
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('tanggal_returpembelian', 'Tanggal Retur Pembelian:') }}
                                        {{ Form::date('tanggal_returpembelian',null,['class'=> 'form-control','id'=>'Tanggal1','required'=>'required'])}}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('no_penerimaan', 'No Penerimaan:') }}
                                        {{ Form::select('no_penerimaan',$Penerimaan,null, ['class'=> 'form-control select2','id'=>'Penerimaan1','required','placeholder'=>'','onchange'=>'getpo1();','style'=>'width: 100%']) }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('no_pembelian', 'No Pembelian:') }}
                                        {{ Form::text('no_pembelian',null, ['class'=> 'form-control','id'=>'Pembelian1','readonly']) }}
                                    </div>
                                </div>
                                {{ Form::hidden('kode_vendor',null, ['class'=> 'form-control','id'=>'Vendor1','readonly']) }}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <br>
                                        {{ Form::label('keterangan', 'Keterangan:') }}
                                        {{ Form::textArea('keterangan', null, ['class'=> 'form-control','rows'=>'4','id'=>'Keterangan1', 'placeholder'=>'Keterangan', 'autocomplete'=>'off','required', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="row">
                                {{ Form::submit('Update data', ['class' => 'btn btn-success crud-submit']) }}
                                {{ Form::button('Close', ['class' => 'btn btn-danger','data-dismiss'=>'modal']) }}&nbsp;
                            </div>
                        </div>
                    {!! Form::close() !!}
              </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="addjurnalform2" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header" style=" height: 1%; border: none">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="box-body"> 
                    <div class="addform">
                        @include('errors.validation')
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="no_journal">No Journal:</label>
                                <input name="no_journal" id="nojournal2" style='width: 100%; border: none; background: transparent' readonly="true">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="journal_date">Journal Date:</label>
                                <input name="journal_date" id="journaldate2" style='width: 100%; border: none; background: transparent' readonly="true">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="reference">Reference:</label>
                                <input name="reference" id="reference2" style='width: 100%; border: none; background: transparent' readonly="true">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="entry_date">Entry Date:</label>
                                <input name="entry_date" id="entry2" style='width: 100%; border: none; background: transparent' readonly="true">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="updated_by">Updated By:</label>
                                <input name="updated_by" id="updated2" style='width: 100%; border: none; background: transparent' readonly="true">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status:</label>
                                <input name="status" id="status2" style='width: 100%; border: none; background: transparent' readonly="true">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <span class="pull-right"> 
                                <a href="#" target="_blank" id="printzoom3"><button type="button" class="btn btn-danger btn-xs print-button2" id="button7">PRINT <i class="fa fa-print"></i></button></a>
                            </span> 
                        </div> 
                    </div>
                </div>

                <div class="container-fluid">
                    <table class="table table-bordered table-striped table-hover" id="addjurnal-table2" width="100%" style="font-size: 12px;">
                        <thead>
                            <tr class="bg-warning">
                                <th>Account</th>
                                <th>Account Description</th>
                                <th>Cost Center</th>
                                <th>DB/CR</th>
                                <th>Debet</th>
                                <th>Credit</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-warning">
                                <th class="text-center" colspan="4">Total</th>
                                <th id="granddebit2">-</th>
                                <th id="grandkredit2">-</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            
                <div class="modal-footer">
                        
                </div>
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

            .zoom-button {
                background-color: #00E0FF;
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
            @permission('update-returpembelian')
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editreturpembelian" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>
            @endpermission

            @permission('delete-returpembelian')
            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapusreturpembelian" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>
            @endpermission

            @permission('add-returpembelian')
            <a href="#" id="addreturpembelian"><button type="button" class="btn btn-info btn-xs add-button" data-toggle="modal" data-target="">ADD <i class="fa fa-plus"></i></button></a>
            @endpermission

            @permission('post-returpembelian')
            <button type="button" class="btn btn-success btn-xs tombol1" id="button1">POST <i class="fa fa-bullhorn"></i></button>
            @endpermission

            @permission('unpost-returpembelian')
            <button type="button" class="btn btn-warning btn-xs tombol2" id="button2">UNPOST <i class="fa fa-undo"></i></button>
            @endpermission

            @permission('view-returpembelian')
            <button type="button" class="btn btn-primary btn-xs view-button" id="button4">VIEW <i class="fa fa-eye"></i></button>
            @endpermission

            @permission('print-returpembelian')
            <a href="#" target="_blank" id="printpenerimaan"><button type="button" class="btn btn-danger btn-xs print-button" id="button6">PRINT <i class="fa fa-print"></i></button></a>
            @endpermission

            @permission('view-zoom')
            <button type="button" class="btn btn-info btn-xs zoom-button" id="detailjurnal2" data-toggle="modal" data-target="#addjurnalform2">
            <i class="fa fa-eye"></i> ZOOM JURNAL</button>
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
            $('.zoom-button').hide();
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
                        url:'{!! route('returpembelian.getkode') !!}',
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
            ajax: '{!! route('returpembelian.data') !!}',
            fnRowCallback: function (row, data, iDisplayIndex, iDisplayIndexFull) {
                if (data['status'] == "OPEN") {
                    $('td', row).css('background-color', '#ffdbd3');
                }
            },
            
            columns: [
                { data: 'no_returpembelian', name: 'no_returpembelian' },
                { data: 'tanggal_returpembelian', name: 'tanggal_returpembelian' },
                { data: 'no_penerimaan', name: 'no_penerimaan' },
                { data: 'no_pembelian', name: 'no_pembelian' },
                { data: 'vendor.nama_vendor', name: 'kode_vendor' },
                { data: 'keterangan', name: 'keterangan' },
                { data: 'total_item', name: 'total_item' },
                { data: 'status', 
                    render: function( data, type, full ) {
                    return formatStatus(data); }
                },
                { data: 'no_journal', name: 'no_journal' },
            ]
            });
        });

        Table4 = $("#addjurnal-table2").DataTable({
            "bPaginate": false,
            "bFilter": false,
            "order": [[ 3, "asc" ]],
            data:[],
            footerCallback: function ( row, data, start, end, display ) {
                var api = this.api(), data;
            
                // Remove the formatting to get integer data for summation
                var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                    i : 0;
                };
            
                // Total over this page
                granddebit = api
                    .column( 4 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );

                grandkredit = api
                    .column( 5 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                            
                // Update footer
                $( api.column( 4 ).footer() ).html(
                    formatRupiah(granddebit)
                );

                $( api.column( 5 ).footer() ).html(
                    formatRupiah(grandkredit)
                );
            },
            
            columns: [
                { data: 'account', name: 'account' },
                { data: 'ac_description', name: 'ac_description' },
                { data: 'costcenter.kode_costcenter', "defaultContent": "<i>-</i>" },
                { data: 'db_cr', 
                    render: function( data, type, full ) {
                    return format_dk(data); } 
                },
                { data: 'debit', 
                    render: function( data, type, full ) {
                    return formatNumber2(data); } 
                },
                { data: 'kredit', 
                    render: function( data, type, full ) {
                    return formatNumber2(data); } 
                },       
            ],
        });

        function format_dk(n) {
            if(n == 'D'){
                var stat = "<span style='color:#0eab25'><b>DEBIT</b></span>";
            }else{
                var stat = "<span style='color:#c91a1a'><b>KREDIT</b></span>";
            }
            return stat;
        }

        function formatNumber2(m) {
            if(m == null){
                return '';
            }else{
                return m.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
        }

        function formatStatus(n) {
            if(n != 'POSTED'){
                return n;
            }else{
                var stat = "<span style='color:#0eab25'><b>POSTED</b></span>";
                return n.replace(/POSTED/, stat);
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

        function formatRupiah(angka, prefix='Rp'){
           
            var rupiah = angka.toLocaleString(
                undefined, // leave undefined to use the browser's locale,
                // or use a string like 'en-US' to override it.
                { minimumFractionDigits: 0 }
            );
            return rupiah;
           
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

        function getpo(){
            var no_penerimaan = $('#Penerimaan').val();
            $.ajax({
                url:'{!! route('returpembelian.getpo') !!}',
                type:'POST',
                data : {
                        'no_penerimaan': no_penerimaan,
                    },
                success: function(result) {
                        $('#Pembelian').val(result.no_pembelian);
                        $('#Vendor').val(result.kode_vendor);
                    },
            });
        }

        function getpo1(){
            var no_penerimaan = $('#Penerimaan1').val();
            $.ajax({
                url:'{!! route('returpembelian.getpo1') !!}',
                type:'POST',
                data : {
                        'no_penerimaan': no_penerimaan,
                    },
                success: function(result) {
                        $('#Pembelian1').val(result.no_pembelian);
                        $('#Vendor1').val(result.kode_vendor);
                    },
            });
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
            landedcost = row.landedcost;
            total_pakai = (harga * qty) + (landedcost*qty);
            total_harga += total_pakai;
            grand_total = formatRupiah(total_harga);

        });

        var my_table = "";

        $.each( result, function( key, row ) {
                    my_table += "<tr>";
                    my_table += "<td>"+row.produk+"</td>";
                    my_table += "<td>"+row.partnumber+"</td>";
                    my_table += "<td>"+row.no_mesin+"</td>";
                    my_table += "<td>"+row.satuan+"</td>";
                    my_table += "<td>"+row.qty+"</td>";
                    my_table += "<td>Rp "+formatRupiah(row.harga)+"</td>";
                    my_table += "<td>Rp "+formatRupiah(row.landedcost)+"</td>";
                    my_table += "<td>Rp "+row.subtotal+"</td>";
                    my_table += "</tr>";
            });

            my_table = '<table id="table-fixed" class="table table-bordered table-hover" cellpadding="5" cellspacing="0" border="1" style="padding-left:50px; font-size:12px">'+ 
                        '<thead>'+
                           ' <tr class="bg-info">'+
                                '<th>Produk</th>'+
                                '<th>Partnumber</th>'+
                                '<th>No Mesin</th>'+
                                '<th>Satuan</th>'+
                                '<th>Qty Retur</th>'+
                                '<th>Harga Satuan</th>'+
                                '<th>Landed Cost Satuan</th>'+
                                '<th>Subtotal</th>'+
                            '</tr>'+
                        '</thead>'+
                        '<tbody>' + my_table + '</tbody>'+
                       ' <tfoot>'+
                            '<tr class="bg-info">'+
                                '<th class="text-center" colspan="4">Total</th>'+
                                '<th>'+total_qty+'</th>'+
                                '<th></th>'+
                                '<th></th>'+
                                '<th>Rp '+grand_total+'</th>'+
                            '</tr>'+
                            '</tfoot>'+
                        '</table>';

                    // $(document).append(my_table);
            
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
                    $('.zoom-button').hide();
                    
                    $('#CreateBy1').val('');
                    $('#CreateAt1').val('');
                    $('#UpdateBy1').val('');
                    $('#UpdateAt1').val('');
                    $('#PostedBy1').val('');
                    $('#UnpostBy1').val('');
                }
                else {
                    table.$('tr.selected').removeClass('selected bg-gray text-bold');
                    $(this).addClass('selected bg-gray text-bold');
                    var select = $('.selected').closest('tr');
                    
                    closeOpenedRows(table, select);
                    
                    var data = $('#data-table').DataTable().row(select).data();
                    var no_returpembelian = data['no_returpembelian'];
                    var no_journal = data['no_journal'];
                    
                    var add = $("#addreturpembelian").attr("href",window.location.href+"/"+no_returpembelian+"/detail");
                    var print = $("#printpenerimaan").attr("href",window.location.href+"/exportpdf?no_returpembelian="+no_returpembelian);
                    var print2 = $("#printzoom3").attr("href",window.location.href+"/exportpdf2?no_journal="+no_journal + "&no_returpembelian="+no_returpembelian);
                    var status = data['status'];
                    var item = data['total_item'];
                    
                    $('#CreateBy1').val(data['created_by']);
                    $('#CreateAt1').val(data['created_at']);
                    $('#UpdateBy1').val(data['updated_by']);
                    $('#UpdateAt1').val(data['updated_at']);
                    $.ajax({
                        url: '{!! route('returpembelian.historia') !!}',
                        type: 'GET',
                        data : {
                            'id': no_returpembelian
                        },
                        success: function(result) {
                            $('#PostedBy1').val(result.post);
                            $('#UnpostBy1').val(result.unpost);
                        }
                    });
                    
                    if(status == 'POSTED' && item > 0){
                        $('.tombol1').hide();
                        $('.tombol2').show();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').show();
                        $('.view-button').show();
                        $('.zoom-button').show();
                    }else if(status =='OPEN' && item > 0){
                        $('.tombol1').show();
                        $('.tombol2').hide();
                        $('.add-button').show();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').hide();
                        $('.view-button').show();
                        $('.zoom-button').hide();
                    }else if(status =='OPEN' && item == 0){
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.add-button').show();
                        $('.hapus-button').show();
                        $('.edit-button').show();
                        $('.print-button').hide();
                        $('.view-button').hide();
                        $('.zoom-button').hide();
                    }
                }
            } );
        
            $('#button1').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_returpembelian = data['no_returpembelian'];
                var no_penerimaan = data['no_penerimaan'];
                swal({
                    title: "Post?",
                    text: no_returpembelian,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, post it!",
                    cancelButtonText: "No, cancel!",
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
                            url: '{!! route('returpembelian.posting') !!}',
                            type: 'POST',
                            data : {
                                'id': no_returpembelian,
                                'no_penerimaan': no_penerimaan
                            },
                            success: function(result) {
                                if (result.success === true) {
                                    swal(
                                    'Posted!',
                                    'Your file has been posted.',
                                    'success'
                                    )
                                    refreshTable();
                                }
                                else{
                                  swal({
                                      title: 'Error',
                                      text: result,
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
                var data = $('#data-table').DataTable().row(select).data();
                var no_returpembelian = data['no_returpembelian'];
                var no_penerimaan = data['no_penerimaan'];
                swal({
                    title: "Unpost?",
                    text: no_returpembelian,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, unpost it!",
                    cancelButtonText: "No, cancel!",
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
                    url: '{!! route('returpembelian.unposting') !!}',
                    type: 'POST',
                    data : {
                        'id': no_returpembelian,
                        'no_penerimaan': no_penerimaan
                    },
                    success: function(result) {
                
                                if (result.success === true) {
                                    swal(
                                    'Unposted!',
                                    'Your file has been unposted.',
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
                                      text: 'UNPOST gagal! Stok barang sudah dipakai.',
                                      type: 'error',
                                  })
                                    refreshTable();
                                }
                            },
                            error : function () {
                              swal({
                                  title: 'Oops...',
                                  text: 'Gagal Unpost!',
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


            $('#addjurnalform2').on('show.bs.modal', function () {
                cekjurnal2();
            })

            $('#detailjurnal2').click( function () {
                var select = $('.selected').closest('tr');
                var no_journal = select.find('td:eq(8)').text();
                $.ajax({
                    url: '{!! route('returpembelian.getDatajurnal2') !!}',
                    type: 'GET',
                    data : {
                        'id': no_journal,
                    },
                    success: function(result) {
                    
                        Table4.clear().draw();
                        Table4.rows.add(result).draw();
                        $('#nojournal2').val(no_journal);
                        $('#addjurnalform2').modal('show');
                    }
                });
            });

            
            var openRows = new Array();

            function cekjurnal2(){
                var no_jurnal = $('#nojournal2').val();
                $.ajax({
                    url:'{!! route('returpembelian.cekjurnal2') !!}',
                    type:'POST',
                    data : {
                            'no_journal':no_jurnal,
                        },
                    success: function(result) {
                        $('#journaldate2').val(result.journal_date);
                        $('#reference2').val(result.reference);
                        $('#entry2').val(result.created_at);
                        $('#updated2').val(result.updated_by);
                        $('#status2').val(result.status);
                    },
                });
            }

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
            
            $('#button4').click( function () {
                var select = $('.selected').closest('tr');
                var no_returpembelian = select.find('td:eq(0)').text();
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('returpembelian.showdetail') !!}',
                    type: 'POST',
                    data : {
                        'id': no_returpembelian
                    },
                    success: function(result) {
                    
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

            $('#editreturpembelian').click( function () {
                var select = $('.selected').closest('tr');
                var no_returpembelian = select.find('td:eq(0)').text();
                var row = table.row( select );
                
                $.ajax({
                    url: '{!! route('returpembelian.edit_returpembelian') !!}',
                    type: 'POST',
                    data : {
                        'id': no_returpembelian
                    },
                    success: function(results) {
                        
                        $('#editform').modal('show');
                        $('#Returbeli1').val(results.no_returpembelian);
                        $('#Tanggal1').val(results.tanggal_returpembelian);
                        $('#Penerimaan1').val(results.no_penerimaan).trigger('change');
                        $('#Pembelian1').val(results.no_pembelian);
                        $('#Vendor1').val(results.kode_vendor);
                        $('#Keterangan1').val(results.keterangan);
                        }
         
                });
            });

            $('#hapusreturpembelian').click( function () {
                var select = $('.selected').closest('tr');
                var no_returpembelian = select.find('td:eq(0)').text();
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
                            url: '{!! route('returpembelian.hapus_returpembelian') !!}',
                            type: 'POST',
                            data : {
                                'id': no_returpembelian
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
            $('.zoom-button').hide();
        }

        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });

        $('.modal-dialog').resizable({
    
        });

        $('#ADD').submit(function (e) {
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false
            })
            e.preventDefault();
            var registerForm = $("#ADD");
            var formData = registerForm.serialize();

                $.ajax({
                    url:'{!! route('returpembelian.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        
                        $('#Penerimaan').val('').trigger('change');
                        $('#Pembelian').val('');
                        $('#Vendor').val('');
                        $('#Tanggal').val('');
                        $('#Keterangan').val('');
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
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false
            })
            e.preventDefault();
            var registerForm = $("#EDIT");
            var formData = registerForm.serialize();

                $.ajax({
                    url:'{!! route('returpembelian.updateajax') !!}',
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
            }
        );
    </script>
@endpush