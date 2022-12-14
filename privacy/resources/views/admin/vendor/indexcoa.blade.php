@extends('adminlte::page')

@section('title', 'Vendor COA')

@section('content_header')
   
@stop

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <a href="{{ $list_url }}" class="btn btn-danger btn-xs"><i class="fa fa-arrow-left"></i> Kembali</a>
    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()"><i class="fa fa-refresh"></i> Refresh</button>
    <span class="pull-right">
        <font style="font-size: 16px;"> Vendor <b>{{$vendor->nama_vendor}}</b></font>
    </span>
@include('sweet::alert')
<body onLoad="load()">
    <div class="box box-danger">
        <div class="box-body"> 
            <div class="addform">
            @include('errors.validation')
            {!! Form::open(['id'=>'ADD_DETAIL']) !!}
            <center><kbd>ADD FORM</kbd></center><br>
                <div class="row">
                {{ Form::hidden('Link',request()->getSchemeAndHttpHost(), ['class'=> 'form-control','readonly','id'=>'Link1']) }}
                {{ Form::hidden('kode_vendor',$vendor->id, ['class'=> 'form-control','readonly','id'=>'KodeCust1']) }}
                {{ Form::hidden('id', null, ['class'=> 'form-control','readonly','id'=>'Id1']) }}
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('Nomors', 'Vendor:') }}
                            {{ Form::text('noomor', $vendor->nama_vendor, ['class'=> 'form-control','id'=>'Nama1','readonly']) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('npwps', 'Kode Coa:') }}
                            {{ Form::select('kode_coa', $coa->sort(),null, ['class'=> 'form-control select2','id'=>'KodeCoa1','style'=>'width: 100%','placeholder' => '']) }}
                        </div>
                    </div>
                </div>
                <span class="pull-right"> 
                    {{ Form::submit('TAMBAH', ['class' => 'btn btn-success btn-sm simpan','id'=>'submit']) }}
                    <button type="button" class="btn btn-info btn-sm editbutton" id="editmt" data-toggle="modal" data-target="">
                    <i class="fa fa-edit"></i> EDIT
                    </button>   
                    <button type="button" class="btn btn-danger btn-sm hapusbutton" id="hapusmt">
                    <i class="fa fa-times-circle"></i> HAPUS
                    </button>
                </span>
            </div>
        {!! Form::close() !!}
    </div>
</div>
<div class="box box-danger">
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="data2-table" width="100%" style="font-size: 12px;">
                <thead>
                    <tr class="bg-info">
                        <th>id</th>
                        <th>COA</th>
                        <th>Ac Desc</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

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

    .view-button {
        bottom: 116px;
    }

    .post-button {
        bottom: 86px;
    }

    .unpost-button {
        bottom: 86px;
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

    .print-button {
        background-color: #F63F3F;
        bottom: 216px;
    }

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
</style>
<div id="mySidenav" class="sidenav">
    <button type="button" class="btn btn-info btn-xs add-button" id="addmtdetail" data-toggle="modal" data-target="#addmtdetailform"><i class="fa fa-plus"></i> ADD DETAIL</button>
    <button type="button" class="btn btn-success btn-xs post-button" id="postbutton">POST <i class="fa fa-bullhorn"></i></button>
    <button type="button" class="btn btn-warning btn-xs unpost-button" id="unpostbutton">UNPOST <i class="fa fa-bullhorn"></i></button>
    <button type="button" class="btn btn-primary btn-xs view-button" id="viewbutton">VIEW <i class="fa fa-eye"></i></button>
</div>
</body>
@stop

@push('css')

@endpush
@push('js')
<script>
        $(window).scroll(function() {
            var height = $(window).scrollTop();
            if (height > 1) {
                $('#back2Top').show();
            } else {
                $('#back2Top').show();
            }
        });
        
        $(document).ready(function() {
            $("#back2Top").click(function(event) {
                event.preventDefault();
                $("html, body").animate({ scrollTop: 0 }, "slow");
                return false;
            });

            $("input[name='npwp']").on("keyup change", function(){
            $("input[name='number']").val(destroyMask(this.value));
                this.value = createMask($("input[name='number']").val());
            })

            function createMask(string){
                return string.replace(/(\d{2})(\d{3})(\d{3})(\d{1})(\d{3})(\d{3})/,"$1.$2.$3.$4-$5.$6");
            }

            function destroyMask(string){
                return string.replace(/\D/g,'').substring(0,15);
            }
        });

        function load(){
            startTime();
            $('.editform').hide();
            $('.add-button').hide();
            $('.post-button').hide();
            $('.unpost-button').hide();
            $('.tombol2').hide();
            $('.back2Top').show();
            $('.simpan').show();
            $('.editbutton').hide();
            $('.hapusbutton').hide();
            $('.editbutton2').hide();
            $('.hapusbutton2').hide();
            $('.view-button').hide();
            $('.groupx1').show();
            $('.groupx2').hide();
        }
        
        function formatRupiah(angka, prefix='Rp'){
           
            var rupiah = angka.toLocaleString(
                undefined, // leave undefined to use the browser's locale,
                // or use a string like 'en-US' to override it.
                { minimumFractionDigits: 0 }
            );
            return rupiah;
           
        }
        
    $(function(){
        var kode = $('#KodeCust1').val();
        var link = $('#Link1').val();
        console.log(kode);
        $('.form-group4').hide();
        $('.form-group2').hide();
        $('#data2-table').DataTable({
            processing: true,
            serverSide: true,
            ajax:link+'/gui_inventory_laravel/admin/vendor/getDatabyID?kode_vendor='+kode,
            data:{'kode':kode},
            columns: [
                { data: 'id', name: 'id', visible: false },
                { data: 'coa.account', name: 'coa.account' },
                { data: 'coa.ac_description', name: 'coa.ac_description' },
            ]
        });
    });

    

    

    function formatStatus(n) {
        console.log(n);
        if(n != 'POSTED'){
            return n;
        }else{
            var stat = "<span style='color:#0eab25'><b>POSTED</b></span>";
            return n.replace(/POSTED/, stat);
        }
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

        function formatNumber(n) {
            if(n == 0){
                return 0;
            }else{
                return n.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
        }
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
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

        function createTable(result){

            var total_qty = 0;
            var total_qty_received = 0;
            var total_harga = 0;
            var grand_total = 0;

            $.each( result, function( key, row ) {
                total_qty += row.qty;
                // total_qty_received += row.qty_received;
                harga = row.harga;
                qty = row.qty;
                subtotal = harga * qty;
                total_harga += subtotal;
                grand_total = formatRupiah(total_harga);
            });

            var my_table = "";

            $.each( result, function( key, row ) {
                my_table += "<tr>";
                my_table += "<td>"+row.produk+"</td>";
                my_table += "<td>"+row.partnumber+"</td>";
                my_table += "<td>"+row.keterangan+"</td>";
                my_table += "<td>"+row.satuan+"</td>";
                my_table += "<td>"+row.qty+"</td>";
                my_table += "<td>Rp "+formatRupiah(row.harga)+"</td>";
                my_table += "<td>Rp "+row.subtotal+"</td>";
                my_table += "</tr>";
            });

            my_table = '<table id="table-fixed" class="table table-bordered table-hover" cellpadding="5" cellspacing="0" border="1" style="padding-left:50px; font-size:12px">'+ 
                        '<thead>'+
                           ' <tr class="bg-black">'+
                                '<th>Produk</th>'+
                                '<th>Partnumber</th>'+
                                '<th>Keterangan</th>'+
                                '<th>Satuan</th>'+
                                '<th>Qty</th>'+
                                '<th>Harga Satuan</th>'+
                                '<th>Subtotal</th>'+
                            '</tr>'+
                        '</thead>'+
                        '<tbody>' + my_table + '</tbody>'+
                       ' <tfoot>'+
                            '<tr class="bg-black">'+
                                '<th class="text-center" colspan="4">Total</th>'+
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

        $(document).ready(function(){
            $("#back2Top").click(function(event) {
                event.preventDefault();
                $("html, body").animate({ scrollTop: 0 }, "slow");
                return false;
            });
            
            $('[data-toggle="tooltip"]').tooltip();
            var table = $('#data2-table').DataTable();

            $('#data2-table tbody').on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected bg-gray text-bold') ) {
                    $(this).removeClass('selected bg-gray text-bold');
                    $('.editbutton').hide();
                    $('.hapusbutton').hide();
                    $('.simpan').show();
                    
                    $('#KodeCoa1').val('').trigger('change');
                }
                else {
                    table.$('tr.selected').removeClass('selected bg-gray text-bold');
                    $(this).addClass('selected bg-gray text-bold');
                    var select = $('.selected').closest('tr');
                    var data = $('#data2-table').DataTable().row(select).data();

                    closeOpenedRows(table, select);

                    var coa = data['kode_coa'];
                    var id = data['id'];
                    
                    $('.simpan').hide();
                    $('.editbutton').show();
                    $('.hapusbutton').hide();

                    $('#KodeCoa1').val(coa).trigger('change');
                    $('#Id1').val(id);
                }
            });

            $('#editmt').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data2-table').DataTable().row(select).data();

                var coa = $('#KodeCoa1').val();
                var id = $('#Id1').val();

                $.ajax({
                    url: '{!! route('vendor.edit_coa') !!}',
                    type: 'POST',
                    data : {
                        'kode_coa': coa,
                        'id': id,
                    },
                    success: function(results) {
                        $('#KodeCoa1').val('').trigger('change');
                        $('.editbutton').hide();
                        $('.hapusbutton').hide();
                        $('.simpan').show();
                        refreshTable();
                        if (results.success === true) {
                            swal("Updated",results.message, "success");
                        }else {
                            swal("????", results.message);
                        }
                    }
                });
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
             $('#data2-table').DataTable().ajax.reload(null,false);;
        }

        $('#ADD_DETAIL').submit(function (e) {
            e.preventDefault();
            var registerForm = $("#ADD_DETAIL");
            var formData = registerForm.serialize();
            // Check if empty of not
            $.ajax({
                url:'{!! route('vendor.store_coa') !!}',
                type:'POST',
                data:formData,
                success:function(data) {
                    console.log(data);
                    $('#KodeCoa1').val('').trigger('change');
                    refreshTable();
                    if (data.success === true) {
                        swal("Berhasil!", data.message, "success");
                    } else {
                        swal("Gagal!", data.message, "error");
                    }
                },
            });
        });
               
        function edit(id, url) {
           
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

               $.ajax({
                    type: 'GET',
                    url: url,
                    data: {_token: CSRF_TOKEN},
                    dataType: 'JSON',
                    success: function (results) {
                        // console.log(result);
                        $(".editform").show();
                        $('#Pembelian').val(results.no_pembelian);
                        $('#Produk').val(results.kode_produk);
                        $('#Namaproduk_e').val(results.nama_produk);
                        $('#Satuan2').val(results.kode_satuan);
                        $('#QTY2').val(results.qty);
                        $('#Harga').val(results.harga);
                        $('#Keterangan2').val(results.keterangan);
                        $('#ID').val(results.id);
                        $(".addform").hide();
                       },
                        error : function() {
                        alert("Nothing Data");
                    }
                });
                     
        }

        function cancel_edit(){
            $(".addform").show();
            $(".editform").hide();
        }

        function del(id, url) {
            swal({
            title: "Hapus?",
            text: "Pastikan dulu data yang akan dihapus!",
            type: "warning",
            showCancelButton: !0,
            confirmButtonText: "Ya, Hapus!",
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

                $.ajax({
                    type: 'DELETE',
                    url: url,
                    
                    success: function (results) {
                    console.log(results);
                        refreshTable();
                            if (results.success === true) {
                                swal("Berhasil!", results.message, "success");
                                
                            } else {
                                swal("Gagal!", results.message, "error");
                            }
                        }
                });
            }
            });
        }
</script>
@endpush