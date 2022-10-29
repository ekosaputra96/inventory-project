@extends('adminlte::page')

@section('title', 'Work Order')

@section('content_header')
    
@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <link rel="icon" type="image/png" href="/gui_inventory_laravel/css/logo_gui.png" sizes="16x16">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
@include('sweet::alert')
<body onLoad="load()">
    <div class="box box-solid">
        <div class="box-body">
            <div class="box">
                <div class="box-body">
                    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()" >
                            <i class="fa fa-refresh"></i> Refresh</button>
                    @permission('create-workorder')
                    <button type="button" class="btn btn-danger btn-xs" id="new-button" data-toggle="modal" data-target="#addform"><i class="fa fa-plus"></i> New Work Order</button>
                    <i>&nbsp;ALT+1 = New WO (shortcut)</i>
                    @endpermission

                    @permission('post-getkode')
                    <button type="button" class="btn btn-primary btn-xs" onclick="getkode()">
                        <i class="fa fa-bullhorn"></i> Get New Kode</button>
                    @endpermission

                    <span class="pull-right">
                        <font style="font-size: 16px;"><b>WORK ORDER</b></font>
                    </span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data-table" style="font-size: 12px; width: 1200px;">
                    <thead>
                        <tr class="bg-primary">
                            <th>No WO</th>
                            <th>No Reff</th>
                            <th>Kode Tagging</th>
                            <th>Tipe</th>
                            <th>Date In</th>
                            <th>Date Finish</th>
                            <th>Lokasi</th>
                            <th>Total Item</th>
                            <th>Keterangan</th>
                            <th>Status</th>
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
    
{{ Form::hidden('nama_user', Auth()->user()->name, ['class'=> 'form-control','style'=>'width: 100%','id'=>'NamaUser1','readonly']) }}

{{ Form::hidden('n1', null, ['class'=> 'form-control','id'=>'nama','readonly']) }}
{{ Form::hidden('n1x', null, ['class'=> 'form-control','id'=>'nama2','readonly']) }}
{{ Form::hidden('n1z', null, ['class'=> 'form-control','id'=>'nama3','readonly']) }}

{{ Form::hidden('n2', null, ['class'=> 'form-control','id'=>'namara','readonly']) }}
{{ Form::hidden('n2x', null, ['class'=> 'form-control','id'=>'namara2','readonly']) }}
{{ Form::hidden('n2z', null, ['class'=> 'form-control','id'=>'namara3','readonly']) }}

{{ Form::hidden('n3', null, ['class'=> 'form-control','id'=>'namaga','readonly']) }}
{{ Form::hidden('n3x', null, ['class'=> 'form-control','id'=>'namaga2','readonly']) }}
{{ Form::hidden('n3z', null, ['class'=> 'form-control','id'=>'namaga3','readonly']) }}

{{ Form::hidden('n4', null, ['class'=> 'form-control','id'=>'grand1','readonly']) }}
{{ Form::hidden('n5', null, ['class'=> 'form-control','id'=>'grand2','readonly']) }}
{{ Form::hidden('n6', null, ['class'=> 'form-control','id'=>'grandara1','readonly']) }}
{{ Form::hidden('n7', null, ['class'=> 'form-control','id'=>'grandara2','readonly']) }}
{{ Form::hidden('n8', null, ['class'=> 'form-control','id'=>'grandaga1','readonly']) }}
{{ Form::hidden('n9', null, ['class'=> 'form-control','id'=>'grandaga2','readonly']) }}

<div class="modal fade" id="addform" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Create Data <i>&nbsp;ENTER = Simpan</i></h4>
            </div>
            @include('errors.validation')
            {!! Form::open(['id'=>'ADD']) !!}
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('No Alat', 'No Reff:') }}
                            {{ Form::text('no_reff', null, ['class'=> 'form-control','id'=>'NoReff1','autocomplete'=>'off']) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('Tanggal', 'Date In :') }}
                            <!--<input type='datetime-local' class='form-control' id='DateIn1' name='date_in' value='{{ \Carbon\Carbon::now() }}' required/>-->
                            {{ Form::datetimelocal('date_in', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'DateIn1' ,'required','onchange'=>"hitungdate()"])}}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('Tanggal', 'Date Finish :') }}
                            {{ Form::datetimelocal('date_finish', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'DateFinish1' ,'required','onchange'=>"hitungdate()"])}}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('Tipe', 'Tipe Pemakaian:') }}
                            {{ Form::select('tipe', ['Alat' => 'Alat', 'Other' => 'Other'] , null, ['class'=> 'form-control select2','id'=>'Tipe1','autocomplete'=>'off','required','style'=>'width: 100%','placeholder'=>'','onchange'=>"tipe_wo();"])}}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('No Alat', 'Kode Tagging:') }}
                            {{ Form::select('no_asset_alat', $Alat->sort(), null, ['class'=> 'form-control select2','id'=>'Alat1','autocomplete'=>'off','style'=>'width: 100%','placeholder'=>'']) }}
                        </div>
                    </div>
                    <!--<div class="col-md-4">-->
                    <!--    <div class="form-group">-->
                    <!--        {{ Form::label('Lokasi', 'Lokasi:') }}-->
                    <!--        {{ Form::select('kode_lokasi', $Lokasi->sort(), null, ['class'=> 'form-control select2','id'=>'Lokasi1','autocomplete'=>'off','style'=>'width: 100%','placeholder'=>'']) }}-->
                    <!--    </div>-->
                    <!--</div>-->
                    <div class="col-md-12">
                        <div class="form-group">
                            {{ Form::label('Deskripsi', 'Keterangan:') }}
                            {{ Form::textarea('keterangan', null, ['class'=> 'form-control','rows'=>'2','id'=>'Ket1', 'placeholder'=>'', 'autocomplete'=>'off','onkeypress'=>"return pulsar(event,this)"]) }}
                        </div>
                    </div> 
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    {{ Form::submit('Create data', ['class' => 'btn btn-success crud-submit','id'=>'create-button']) }}
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
                {{ Form::hidden('no_wo', null, ['class'=> 'form-control','id'=>'NoWo2','autocomplete'=>'off']) }}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('No Alat', 'No Reff:') }}
                            {{ Form::text('no_reff', null, ['class'=> 'form-control','id'=>'NoReff2','autocomplete'=>'off']) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('Tanggal', 'Date In :') }}
                            {{ Form::datetimelocal('date_in', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'DateIn2' ,'required','onchange'=>"hitungdate2()"])}}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('Tanggal', 'Date Finish :') }}
                            {{ Form::datetimelocal('date_finish', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'DateFinish2' ,'required','onchange'=>"hitungdate2()"])}}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('Tipe', 'Tipe:') }}
                            {{ Form::select('tipe', ['Alat' => 'Alat', 'Other' => 'Other'] ,null, ['class'=> 'form-control select2','id'=>'Tipe2','autocomplete'=>'off','style'=>'width: 100%','placeholder'=>'','onchange'=>"tipe_wo2()"]) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('No Alat', 'Kode Tagging:') }}
                            {{ Form::select('no_asset_alat', $Alat->sort(),null, ['class'=> 'form-control select2','id'=>'Alat2','autocomplete'=>'off','style'=>'width: 100%','placeholder'=>'']) }}
                        </div>
                    </div>
                    <!--<div class="col-md-4">-->
                    <!--    <div class="form-group">-->
                    <!--        {{ Form::label('No Alat', 'Lokasi:') }}-->
                    <!--        {{ Form::select('kode_lokasi', $Lokasi->sort(), null, ['class'=> 'form-control select2','id'=>'Lokasi2','autocomplete'=>'off','style'=>'width: 100%','placeholder'=>'']) }}-->
                    <!--    </div>-->
                    <!--</div>-->
                    <div class="col-md-12">
                        <div class="form-group">
                            {{ Form::label('Deskripsi', 'Keterangan:') }}
                            {{ Form::textarea('keterangan', null, ['class'=> 'form-control','rows'=>'2','id'=>'Ket2', 'placeholder'=>'', 'autocomplete'=>'off','onkeypress'=>"return pulsar(event,this)"]) }}
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

            .tombol7 {
                background-color: #149933;
                bottom: 156px;
            }

            .tombol8 {
                background-color: #ff9900;
                bottom: 156px;
            }

            .print-button {
                background-color: #F63F3F;
                bottom: 216px;
            }
            
            .void-button {
                bottom: 246px;
            }
            
            /*.ttdigi-button {*/
            /*    bottom: 276px;*/
            /*}*/

            #mySidenav button {
              position: fixed;
              right: -60px;
              transition: 0.3s;
              padding: 4px 8px;
              width: 110px;
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
            @permission('update-workorder')
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editpembelian" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>
            @endpermission
            
            <button type="button" class="btn bg-purple btn-xs void-button" id="voidpembelian" data-toggle="modal" data-target="">VOID <i class="fa fa-times-circle"></i></button>

            @permission('delete-workorder')
            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapuspembelian" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>
            @endpermission

            @permission('add-workorder')
            <a href="#" id="addpembelian"><button type="button" class="btn btn-info btn-xs add-button" data-toggle="modal" data-target="">ADD <i class="fa fa-plus"></i></button></a>
            @endpermission
            
            @permission('post-workorder')
            <button type="button" class="btn btn-success btn-xs tombol1" id="button1">CLOSE <i class="fa fa-bullhorn"></i></button>
            @endpermission
            
            @permission('unpost-workorder')
            <button type="button" class="btn btn-warning btn-xs tombol2" id="button2">UNCLOSE <i class="fa fa-undo"></i></button>
            @endpermission
            
            @permission('view-workorder')
            <button type="button" class="btn btn-primary btn-xs view-button" id="button5">VIEW <i class="fa fa-eye"></i></button>
            @endpermission
            
            <button type="button" class="btn btn-success btn-xs tombol7" id="button7">APPROVE <i class="fa fa-check"></i></button>
            <button type="button" class="btn btn-warning btn-xs tombol8" id="button8">DISAPPROVE <i class="fa fa-check"></i></button>

            @permission('print-workorder')
            <a href="#" target="_blank" id="printpembelian"><button type="button" class="btn btn-danger btn-xs print-button" id="button6">PRINT <i class="fa fa-print"></i></button></a>
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
            if (x >= 60000){
                x = 0;
                refreshTable();
            }
        }
        // setInterval(showTime, 1);
        
        document.onkeyup = function () {
              var e = e || window.event; // for IE to cover IEs window event-object
              if(e.altKey && e.which == 49) {
                $("#new-button").click();
              }
              
              if(e.which == 13) {
                $("#create-button").click();
              }
        }

        function load(){
            limiter();
            startTime();
            $('.tombol1').hide();
            $('.tombol2').hide();
            $('.tombol7').hide();
            $('.tombol8').hide();
            $('.add-button').hide();
            $('.hapus-button').hide();
            $('.edit-button').hide();
            $('.preview-button').hide();
            $('.print-button').hide();
            $('.ttdigi-button').hide();
            $('.view-button').hide();
            $('.void-button').hide();
            $('.back2Top').show();
        }
        
        function hitungdate()
        {
            var tglin = $('#DateIn1').val();
            var tglfinish = $('#DateFinish1').val();
            $.ajax({
                url: '{!! route('workorder.hitungdate') !!}',
                    type: 'POST',
                    data : {
                        'tglin': tglin,
                        'tglfinish': tglfinish
                    },
                    success: function(results) {
                        // $('#NoWO2').val(results.no_wo);
                    }
            });
        }
        
        function getnama(){
            var jenis = $("#Jenis1").val();
            if (jenis == 'Non-Stock') {
                document.getElementById("Alat1").readOnly = false;
            }else {
                document.getElementById("Alat1").readOnly = true;
            }
        }

        $(function() {
            $("#data-table").DataTable({
                "bPaginate": true,
                "bFilter": true,
                "scrollY": 280,
                "pageLength":100,
                "order": [[0, "desc"]],
                ajax: '{!! route('workorder.data') !!}',
                data:[],
                fnRowCallback: function (row, data, iDisplayIndex, iDisplayIndexFull) {
                    if (data['status'] == "OPEN") {
                        $('td', row).css('background-color', '#ffdbd3');
                    }
                },
                columns: [
                    { data: 'no_wo',  name: 'no_wo' },
                    { data: 'no_reff',  name: 'no_reff' },
                    { data: 'no_asset_alat', name: 'no_asset_alat' },
                    { data: 'tipe', name: 'tipe', "defaultContent": "<i> - </i>" },
                    { data: 'date_in', name: 'date_in' },
                    { data: 'date_finish', name: 'date_finish' },
                    { data: 'kode_lokasi', name: 'kode_lokasi' },
                    { data: 'total_item', name: 'total_item' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'status', 
                        render: function( data, type, full ) {
                        return formatStatus(data); }
                    },
                    { data: 'created_at', name: 'created_at',visible:false },
                ]
            });
        });

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
                        url:'{!! route('workorder.getkode') !!}',
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
        
        function limiter() {
            $.ajax({
                url: '{!! route('workorder.limitos') !!}',
                type: 'GET',
                data : {
                },
                success: function(results) {
                    $('#nama').val(results.nama);
                    $('#nama2').val(results.nama2);
                    $('#nama3').val(results.nama3);
                    $('#grand1').val(results.grand1);
                    $('#grand2').val(results.grand2);
                    $('#namara').val(results.namara);
                    $('#namara2').val(results.namara2);
                    $('#namara3').val(results.namara3);
                    $('#grandara1').val(results.grandara1);
                    $('#grandara2').val(results.grandara2);
                    $('#namaga').val(results.namaga);
                    $('#namaga2').val(results.namaga2);
                    $('#namaga3').val(results.namaga3);
                    $('#grandaga1').val(results.grandaga1);
                    $('#grandaga2').val(results.grandaga2);
                }
            });
        }

        function formatNumber(m) {
            if(m == null){
                return '';
            }else{
                return m.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
        }

        function formatStatus(n) {
            if(n == 'OPEN'){
                return n;
            }else if(n == 'POSTED'){
                var stat = "<span style='color:#0eab25'><b>POSTED</b></span>";
                return n.replace(/POSTED/, stat);
            }else if(n == 'CLOSED'){
                var stat = "<span style='color:#c91a1a'><b>CLOSED</b></span>";
                return n.replace(/CLOSED/, stat);
            }else if(n == 'APPROVED'){
                var stat = "<span style='color:#FF5733'><b>APPROVED</b></span>";
                return n.replace(/APPROVED/, stat);
            }else if(n == 'INVOICED'){
                var stat = "<span style='color:#2a59a3'><b>INVOICED</b></span>";
                return n.replace(/INVOICED/, stat);
            }else if(n == 'VOID'){
                var stat = "<span style='color:#9439e3'><b>VOID</b></span>";
                return n.replace(/VOID/, stat);
            }else{
                var stat = "<span style='color:#1a80c9'><b>RECEIVED</b></span>";
                return n.replace(/RECEIVED/, stat);
            }
        }

        function formatNomor(n) {
            var res = n.substr(2, 4);
            if(res != 'POGA'){
                return n;
            }else{
                var str = n;
                var result = str.fontcolor( "#0eab25" );
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

        function createTable(result){

        var total_qty = 0;
        var total_qty_received = 0;
        var total_pakai = 0;
        var total_harga = 0;
        var grand_total = 0;

        $.each( result, function( key, row ) {
            total_qty += row.qty;
            total_qty_received += row.qty_received;
            harga = row.harga;
            qty = row.qty;
            total_pakai = harga * qty;
            total_harga += total_pakai;
            grand_total = formatRupiah(Math.round(total_harga));

        });

        var my_table = "";

        $.each( result, function( key, row ) {
                    my_table += "<tr>";
                    my_table += "<td>"+row.produk+"</td>";
                    my_table += "<td>"+row.type+"</td>";
                    my_table += "<td>"+row.partnumber+"</td>";
                    my_table += "<td>"+row.qty+"</td>";
                    my_table += "<td>"+row.qty_pakai+"</td>";
                    my_table += "</tr>";
            });

            my_table = '<table id="table-fixed" class="table table-bordered table-hover" cellpadding="5" cellspacing="0" border="1" style="padding-left:50px; font-size:12px">'+ 
                        '<thead>'+
                           ' <tr class="bg-info">'+
                                '<th>Produk</th>'+
                                '<th>Tipe</th>'+
                                '<th>Partnumber</th>'+
                                '<th>Qty</th>'+
                                '<th>Qty Pakai</th>'+
                            '</tr>'+
                        '</thead>'+
                        '<tbody>' + my_table + '</tbody>'+
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
                    $('.tombol7').hide();
                    $('.tombol8').hide();
                    $('.add-button').hide();
                    $('.hapus-button').hide();
                    $('.edit-button').hide();
                    $('.print-button').hide();
                    $('.preview-button').hide();
                    $('.ttdigi-button').hide();
                    $('.view-button').hide();
                    $('.void-button').hide();
                    
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
                    var status = data['status'];
                    var item = data['total_item'];
                    var tipe = data['tipe'];
                    var no_pembelian = data['no_wo'];
                    
                    var add = $("#addpembelian").attr("href",window.location.href+"/"+no_pembelian+"/detail");
                    var print = $("#printpembelian").attr("href",window.location.href+"/exportpdf?no_pembelian="+no_pembelian);

                    $('#CreateBy1').val(data['created_by']);
                    $('#CreateAt1').val(data['created_at']);
                    $('#UpdateBy1').val(data['updated_by']);
                    $('#UpdateAt1').val(data['updated_at']);
                    $.ajax({
                        url: '{!! route('workorder.historia') !!}',
                        type: 'GET',
                        data : {
                            'id': no_pembelian
                        },
                        success: function(result) {
                            $('#PostedBy1').val(result.post);
                            $('#UnpostBy1').val(result.unpost);
                        }
                    });
                    
                    var pengguna = $('#NamaUser1').val();
                    
                    if(status == 'CLOSED' && item > 0){
                        $('.tombol1').hide();
                        $('.tombol2').show();
                        $('.tombol7').hide();
                        $('.tombol8').hide();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').show();
                        $('.preview-button').show();
                        $('.ttdigi-button').show();
                        $('.view-button').show();
                        $('.void-button').hide();
                    }else if(status =='OPEN' && item > 0){
                        $('.tombol1').show();
                        $('.tombol2').hide();
                        $('.tombol7').hide();
                        $('.tombol8').hide();
                        $('.add-button').show();
                        $('.hapus-button').hide();
                        $('.edit-button').show();
                        $('.print-button').hide();
                        $('.preview-button').hide();
                        $('.ttdigi-button').hide();
                        $('.view-button').show();
                        $('.void-button').hide();
                    }else if(status =='OPEN' && item == 0){
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.tombol7').hide();
                        $('.tombol8').hide();
                        $('.add-button').show();
                        $('.hapus-button').show();
                        $('.edit-button').show();
                        $('.print-button').hide();
                        $('.preview-button').hide();
                        $('.ttdigi-button').hide();
                        $('.view-button').show();
                        $('.void-button').hide();
                    }                
                }
            } );            
        
            $('#button1').click( function () {
                var select = $('.selected').closest('tr');
                var colom = select.find('td:eq(0)').text();
                var no_pembelian = colom;
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
                            url: '{!! route('workorder.post') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pembelian
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
                                      text: result.message,
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
                var no_pembelian = colom;
                swal({
                    title: "Unclose?",
                    text: colom,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Unclose!",
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
                            url: '{!! route('workorder.unpost') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pembelian
                            },
                            success: function(result) {
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

            $('#button7').click( function () {
                var select = $('.selected').closest('tr');
                var colom = select.find('td:eq(0)').text();
                var no_pembelian = colom;
                swal({
                    title: "Approve?",
                    text: colom,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Approve!",
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
                // alert( table.rows('.selected').data().length +' row(s) selected' );
                        $.ajax({
                            url: '{!! route('workorder.approve') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pembelian
                            },
                            success: function(result) {
                                if (result.success === true) {
                                    swal(
                                    'Approved!',
                                    'Your file has been approve.',
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

            $('#button8').click( function () {
                var select = $('.selected').closest('tr');
                var colom = select.find('td:eq(0)').text();
                var no_pembelian = colom;
                swal({
                    title: "Disapprove?",
                    text: colom,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Disapprove!",
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
                // alert( table.rows('.selected').data().length +' row(s) selected' );
                        $.ajax({
                            url: '{!! route('workorder.disapprove') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pembelian
                            },
                            success: function(result) {
                                if (result.success === true) {
                                    swal(
                                    'Disapproved!',
                                    'Your file has been disapprove.',
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
                var no_pembelian = select.find('td:eq(0)').text();
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('workorder.showdetail') !!}',
                    type: 'POST',
                    data : {
                        'id': no_pembelian
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

            $('#editpembelian').click( function () {
                var select = $('.selected').closest('tr');
                var no_pembelian = select.find('td:eq(0)').text();
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('workorder.edit_pembelian') !!}',
                    type: 'POST',
                    data : {
                        'id': no_pembelian
                    },
                    success: function(results) {
                        $('#editform').modal('show');
                        $('#NoWo2').val(results.no_wo);
                        $('#NoReff2').val(results.no_reff);
                        $('#Alat2').val(results.no_asset_alat).trigger('change');
                        $('#Tipe2').val(results.tipe).trigger('change');
                        $('#DateIn2').val(results.date_in);
                        $('#DateFinish2').val(results.date_finish);
                        $('#Lokasi2').val(results.kode_lokasi).trigger('change');
                        $('#Ket2').val(results.keterangan);
                    }
                });
            });

            $('#hapuspembelian').click( function () {
                var select = $('.selected').closest('tr');
                var no_pembelian = select.find('td:eq(0)').text();
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
                            url: '{!! route('workorder.hapus_pembelian') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pembelian
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
            
            $('#voidpembelian').click( function () {
                var select = $('.selected').closest('tr');
                var no_pembelian = select.find('td:eq(0)').text();
                var row = table.row( select );
                swal({
                    title: "Hapus?",
                    text: "Pastikan dahulu item yang akan di void",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Void!",
                    cancelButtonText: "Batal!",
                    reverseButtons: !0
                }).then(function (e) {
                    if (e.value === true) {
                        $.ajax({
                            url: '{!! route('workorder.void_pembelian') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pembelian
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
            $('.tombol7').hide();
            $('.tombol8').hide();
            $('.add-button').hide();
            $('.ttdigi-button').hide();
            $('.hapus-button').hide();
            $('.edit-button').hide();
            $('.print-button').hide();
            $('.preview-button').hide();
            $('.view-button').hide();
        }

        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });

        $('.modal-dialog').resizable({
    
        });

        function hitung() {
             var tgl_awal = $('#Tanggal1').val();
             var hari = $('#TOP1').val();

             var hasil = new Date(new Date().getTime()+(hari*24*60*60*1000)); 

             var newdate = new Date(hasil);
             var dd = newdate.getDate();
             var mm = newdate.getMonth() + 1;
             var y = newdate.getFullYear();

             var someFormattedDate = y + '-' + mm + '-' + dd;
             document.getElementById('Due1').value = someFormattedDate;
        }

        function hitung2() {
             var tgl_awal = $('#Tanggal').val();
             var hari = $('#TOP').val();

             var hasil = new Date(new Date().getTime()+(hari*24*60*60*1000)); 

             var newdate = new Date(hasil);
             var dd = newdate.getDate();
             var mm = newdate.getMonth() + 1;
             var y = newdate.getFullYear();

             var someFormattedDate = y + '-' + mm + '-' + dd;
             document.getElementById('Due').value = someFormattedDate;
        }
        
        function get_ppn(){
            var kode_vendor = $('#Vendor1').val();
            $.ajax({
                url:'{!! route('workorder.get_ppn') !!}',
                type:'POST',
                data : {
                    'kode_vendor': kode_vendor,
                },
                success: function(result) {
                    $('#PPN1').val(result.ppn);
                },
            });
        }
        
        function tipe_wo(){
            var tipe_wo = $('#Tipe1').val();
            if (tipe_wo == 'Other'){
                $('#Alat1').val('').trigger('change');
                document.getElementById('Alat1').setAttribute("disabled","disabled");
            }else{
                document.getElementById('Alat1').removeAttribute("disabled");
            }
        }
        
        function tipe_wo2(){
            var tipe_wo = $('#Tipe2').val();
            if (tipe_wo == 'Other'){
                $('#Alat2').val('').trigger('change');
                document.getElementById('Alat2').setAttribute("disabled","disabled");
            }else{
                document.getElementById('Alat2').removeAttribute("disabled");
            }
        }

        function get_ppn2(){
            var kode_vendor = $('#Vendor').val();
            $.ajax({
                url:'{!! route('workorder.get_ppn') !!}',
                type:'POST',
                data : {
                    'kode_vendor': kode_vendor,
                },
                success: function(result) {
                    $('#PPN').val(result.ppn);
                },
            });
        }

        function cekdiskon(){
            var diskonpersen = $('#Diskon1').val();
            var diskonrp = $('#Diskonrp1').val();
            if (diskonpersen > 0){
                $('#Diskonrp1').val('0');
                // document.getElementById('Diskonrp1').disabled = true;
            }
        }

        function cekdiskon2(){
            var diskonpersen = $('#Diskon1').val();
            var diskonrp = $('#Diskonrp1').val();
            if (diskonrp > 0){
                $('#Diskon1').val('0');
                // document.getElementById('Diskon1').disabled = true;
            }
        }

        function cekdiskon3(){
            var pbbkbpersen = $('#pbbkb1').val();
            var pbbkbrp = $('#pbbkbrp1').val();
            if (pbbkbrp > 0){
                $('#pbbkbrp1').val('0');
                // document.getElementById('Diskon1').disabled = true;
            }
        }

        function cekdiskon4(){
            var pbbkbpersen = $('#pbbkb1').val();
            var pbbkbrp = $('#pbbkbrp1').val();
            if (pbbkbpersen > 0){
                $('#pbbkb1').val('0');
                // document.getElementById('Diskon1').disabled = true;
            }
        }


        function cekdiskone(){
            var diskonpersen = $('#Diskon').val();
            var diskonrp = $('#Diskonrp').val();
            if (diskonpersen > 0){
                $('#Diskonrp').val('0');
                // document.getElementById('Diskonrp1').disabled = true;
            }
        }

        function cekdiskon2e(){
            var diskonpersen = $('#Diskon').val();
            var diskonrp = $('#Diskonrp').val();
            if (diskonrp > 0){
                $('#Diskon').val('0');
                // document.getElementById('Diskon1').disabled = true;
            }
        }

        function cekdiskon3e(){
            var pbbkbpersen = $('#pbbkb').val();
            var pbbkbrp = $('#pbbkbrp').val();
            if (pbbkbrp > 0){
                $('#pbbkbrp').val('0');
                // document.getElementById('Diskon1').disabled = true;
            }
        }

        function cekdiskon4e(){
            var pbbkbpersen = $('#pbbkb').val();
            var pbbkbrp = $('#pbbkbrp').val();
            if (pbbkbpersen > 0){
                $('#pbbkb').val('0');
                // document.getElementById('Diskon1').disabled = true;
            }
        }
             

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
            
            var now = new Date();
            var day = ("0" + now.getDate()).slice(-2);
            var month = ("0" + (now.getMonth() + 1)).slice(-2);
            var today = now.getFullYear()+"-"+(month)+"-"+(day) ;

                $.ajax({
                    url:'{!! route('workorder.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        // $('#Kode1').val('');
                        $('#addform').modal('show');
                        $('#Tanggal1').val(today);
                        $('#Jenis1').val('').trigger('change');
                        $('#Vendor1').val('').trigger('change');
                        $('#Ref1').val('');
                        $('#TOP1').val('');
                        $('#Due1').val('');
                        $('#Diskon1').val('0');
                        $('#Diskonrp1').val('0');
                        $('#PPN1').val('0');
                        $('#pbbkb1').val('0');
                        $('#pbbkbrp1').val('0');
                        $('#ongkosangkut1').val('0');
                        $('#Deskripsi1').val('');
                        $('#Cost1').val('').trigger('change');
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
                    url:'{!! route('workorder.updateajax') !!}',
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

                                