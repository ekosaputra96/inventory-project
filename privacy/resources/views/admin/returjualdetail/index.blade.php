@extends('adminlte::page')

@section('title', 'Retur Penjualan Detail')

@section('content_header')
    
@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <a href="{{ $list_url }}" class="btn btn-success btn-xs"><i class="fa fa-arrow-left"></i> Kembali</a>
    <button type="button" class="btn btn-default btn-xs" onclick="tablefresh()"><i class="fa fa-refresh"></i> Refresh</button>

    <span class="pull-right">
        <font style="font-size: 16px;"> Detail Retur Penjualan <b>{{$returpenjualan->no_retur_jual}}</b></font>
    </span>
@include('sweet::alert')
<body onLoad="load()">
    <div class="box box-success">
        <div class="box-body"> 
                <div class="addform">
                    @include('errors.validation')
                    {!! Form::open(['id'=>'ADD_DETAIL']) !!}
                    <center><kbd>ADD FORM</kbd></center><br>
                        <div class="row">
                            <div class="col-md-2">
                                    <div class="form-group1">
                                        {{ Form::label('No Penjualan', 'No Retur:') }}
                                        {{ Form::text('no_retur_jual',$returpenjualan->no_retur_jual, ['class'=> 'form-control','readonly','id'=>'noretur1']) }}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group2">
                                        {{ Form::label('no_penjualan', 'No Penjualan:') }}
                                        {{ Form::text('no_penjualan',$returpenjualan->no_penjualan, ['class'=> 'form-control','readonly','id'=>'nojual1']) }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group2">
                                        {{ Form::label('kode_produk', 'Produk:') }}
                                        {{ Form::select('kode_produk',$Produk->sort(),null,
                                         ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','required'=>'required','onchange'=>'stock();',
                                         'id'=>'kode_produks']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group5">
                                        {{ Form::label('kode_satuan', 'Satuan:') }}
                                        {{ Form::text('kode_satuan', null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => '','required'=>'required','id'=>'Satuan','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group3">
                                        {{ Form::label('partnumber', 'Part Number:') }}
                                        {{ Form::text('partnumber', null, ['class'=> 'form-control','id'=>'Parts','required'=>'required','style'=>'width: 100%','autocomplete'=>'off','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group4">
                                        {{ Form::label('partnumber', 'Part Number:') }}
                                        {{ Form::select('partnumber',$Parts,null, ['class'=> 'form-control select2','style'=>'width: 100%','id'=>'Partserial','autocomplete'=>'off','placeholder' => '','onchange'=>'getstock();']) }}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                        <div class="form-group6">
                                            {{ Form::label('qty_stock', 'Qty Penjualan:') }}
                                            {{ Form::text('qty', null, ['class'=> 'form-control','readonly','id'=>'Stock']) }}
                                        </div>
                                </div>
                    
                                <div class="col-md-2">
                                    <div class="form-group7">
                                        {{ Form::label('qty', 'Qty Retur:') }}
                                        {{ Form::text('qty_retur', null, ['class'=>'form-control','required'=>'required','id'=>'QTY','onkeyup'=>'check();','autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                        {{ Form::hidden('harga',null, ['class'=> 'form-control','id'=>'Harga','readonly']) }}
                                   
                                <div class="col-md-2">
                                    <div class="form-group8">
                                        {{ Form::label('harga_jual', 'Harga Jual:') }}
                                        {{ Form::number('harga_jual',null, ['class'=> 'form-control','id'=>'Jual','readonly']) }}
                                    </div>
                                </div>
                            </div> 
                                <span class="pull-right">
                                        {{ Form::submit('Add Item', ['class' => 'btn btn-success btn-sm','id'=>'submit']) }}
                                </span>
                    {!! Form::close() !!}
            </div>

            <div class="editform">
                @include('errors.validation')
                {!! Form::open(['id'=>'UPDATE_DETAIL']) !!}
                 <center><kbd>EDIT FORM</kbd></center><br>
                    <div class="row">   
                        <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('No Retur', 'No Retur:') }}
                                    {{ Form::hidden('id', null, ['class'=> 'form-control','id'=>'ID']) }}
                                    {{ Form::text('no_retur_jual',$returpenjualan->no_retur_jual, ['class'=> 'form-control','readonly','id'=>'noretur2']) }}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('no_penjualan', 'No Penjualan:') }}
                                    {{ Form::text('no_penjualan',$returpenjualan->returpenjualan, ['class'=> 'form-control','readonly','id'=>'nopakai2']) }}
                                </div>
                            </div>

                            {{ Form::hidden('kode_produk',null, ['class'=> 'form-control','id'=>'Produk2','required'=>'required','readonly']) }}

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('nama_produk', 'Nama Produk:') }}
                                    {{ Form::text('nama_produk',null, ['class'=> 'form-control','id'=>'Namaproduk_e','readonly']) }}
                                </div>
                            </div>
                
                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('kode_satuan', 'Satuan:') }}
                                    {{ Form::text('kode_satuan',null, ['class'=> 'form-control','id'=>'Satuan2','readonly']) }}
                                </div>
                            </div>
                
                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('qty', 'Qty Retur:') }}
                                    {{ Form::text('qty_retur', null, ['class'=> 'form-control','id'=>'Qty2','required'=>'required','onkeyup'=>"check2()",'autocomplete'=>'off']) }}
                                </div>
                            </div>

                                    {{ Form::hidden('harga',null, ['class'=> 'form-control','id'=>'Harga2','required'=>'required','readonly']) }}

                                <div class="col-md-2">
                                    <div class="form-group8">
                                        {{ Form::label('harga_jual', 'Harga Jual:') }}
                                        {{ Form::number('harga_jual',null, ['class'=> 'form-control','id'=>'Jual2','readonly']) }}
                                    </div>
                                </div>
                        </div> 
                            <div class="row-md-2">
                                <span class="pull-right"> 
                                    {{ Form::submit('Update', ['class' => 'btn btn-success btn-sm','id'=>'submit2']) }}
                                    <button type="button" class="btn btn-danger btn-sm" onclick="cancel_edit()">Cancel</button>&nbsp;
                                </span>
                            </div>
                {!! Form::close() !!}  
      </div>
      
    </div>
</div>

    <div class="box box-success">
        <div class="box-body"> 
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data2-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-info">
                        <th>No Retur</th>
                        <th>No Penjualan</th>
                        <th>Produk</th>
                        <th>Part Number</th>
                        <th>Satuan</th>
                        <th>Qty Retur</th>
                        <th>Harga Jual</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                     </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-info">
                            <th class="text-center" colspan="5">Total</th>
                            <th id="totalqty">-</th>
                            <th>-</th>
                            <th id="grandtotal">-</th>
                            <th>-</th>
                        </tr>
                    </tfoot>
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
        </style>
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

        });

        function load(){
            startTime();
            $('.form-group3').hide();
            $('.form-group4').hide();
            $('.editform').hide();
            $('.addform').show();
            $('.back2Top').show();
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
        var no_retur = $('#noretur1').val();
            $('#data2-table').DataTable({
            processing: true,
            serverSide: true,
            ajax:'http://localhost/gui_inventory_laravel/admin/returjualdetail/getDatabyID?id='+no_retur,
            data:{'no_retur_jual':no_retur},
            footerCallback: function ( row, data, start, end, display ) {
                    var api = this.api(), data;
        
                    var intVal = function ( i ) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '')*1 :
                            typeof i === 'number' ?
                                i : 0;
                    };
        
                    // Total over all pages
                    total = api
                        .column( 5 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
        
                    // Total over this page
                    grandTotal = api
                        .column( 7 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                        
                    // Update footer
                    $( api.column( 5 ).footer() ).html(
                         total
                    );
                    $( api.column( 7 ).footer() ).html(
                        'Rp. '+formatRupiah(grandTotal) 
                    );
                },
            columns: [
                { data: 'no_retur_jual', name: 'no_retur_jual' },
                { data: 'no_penjualan', name: 'no_penjualan' },
                { data: 'produk.nama_produk', name: 'produk.nama_produk' },
                { data: 'partnumber', name: 'partnumber' },
                { data: 'satuan.nama_satuan', name: 'satuan.nama_satuan', searchable : false },
                { data: 'qty_retur', name: 'qty_retur' },
                { data: 'harga_jual', 
                    render: function( data, type, full ) {
                    return formatNumber(data); }
                },
                { data: 'subtotal', 
                    render: function( data, type, full ) {
                    return formatNumber(data); }
                },
                { data: 'action', name: 'action' }
            ]
            
            });
        });

        function formatNumber(n) {
            if(n == 0){
                return 0;
            }else{
                return n.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
        }

  </script>
  <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var table=$('#data-table').DataTable({
                scrollY: true,
                scrollX: true,
            
            });

        function stock(){
            var no_jual = $('#nojual1').val();
            var kode_produk= $('#kode_produks').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('returjualdetail.stockproduk') !!}',
                type:'POST',
                data : {
                        'kode_produk': kode_produk,
                        'nojual': no_jual,
                    },
                success: function(result) {
                        console.log(result);
                        if (result.tipe == "Serial" && result.kategori == "UNIT"){
                            document.getElementById('Parts').disabled = true;
                            document.getElementById('Partserial').disabled = false;
                            $('.form-group4').show();
                            $('.form-group3').hide();
                            $('#QTY').val('');   
                            $('#Harga').val(result.harga);
                            $('#Jual').val(result.harga_jual);
                            $('#Stock').val('');
                            $('#Satuan').val(result.satuan);
                            // $('#Parts').val(result.partnumber);
                        }
                        else{
                            document.getElementById('Parts').disabled = false;
                            document.getElementById('Partserial').disabled = true;
                            $('.form-group4').hide();
                            $('.form-group3').show();
                            $('#QTY').val('');   
                            $('#Harga').val(result.harga);
                            $('#Jual').val(result.harga_jual);
                            $('#Stock').val(result.qty);
                            $('#Satuan').val(result.satuan);
                            $('#Parts').val(result.partnumber);
                        }
                    },
            });
        }

        function getstock(){
            var no_penjualan= $('#nojual1').val();
            var kode_produk= $('#kode_produks').val();
            var partnumber= $('#Partserial').val();

             $.ajax({
                url:'{!! route('returjualdetail.getstock') !!}',
                type:'POST',
                data : {
                        'no_penjualan': no_penjualan,
                        'kode_produk': kode_produk,
                        'partnumber': partnumber,
                    },

                success: function(result) {
                        console.log(result);
                        $('#Stock').val(result.qty);
                        $('#QTY').val('');
                    },
            });
        }

        function check(){
            var kode_produk= $('#kode_produks').val();
            var satuan = $('#Satuan').val();
            var stok = $('#Stock').val();
            var qty = $('#QTY').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('returjualdetail.qtycheck') !!}',
                type:'POST',
                data : {
                        'id': kode_produk,
                        'satuan': satuan,
                        'stok': stok,
                        'qty': qty
                    },
                success: function(result) {
                        console.log(result);
                        if(result > stok){
                            submit.disabled = true;
                            swal("Gagal!", "Qty retur melebihi qty penjualan");
                        }else if (result == 0) {
                            submit.disabled = true;
                            swal("Gagal!", "Qty tak boleh Nol");
                        }else {
                            submit.disabled = false;
                        }
                    },
            });
        }

        function check2(){
            var no_pakai = $('#nopakai2').val();
            var kode_produk= $('#Produk2').val();
            var qty = $('#Qty2').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('returjualdetail.qtycheck2') !!}',
                type:'POST',
                data : {
                        'id': kode_produk,
                        'no_penjualan': no_pakai,
                        'qty': qty
                    },
                success: function(result) {
                        console.log(result);
                        if(result == 'false'){
                            submit2.disabled = true;
                            swal("Gagal!", "Qty retur melebihi qty penjualan");
                        }else if (result == 'rusak'){
                            submit2.disabled = true;
                            swal("Gagal!", "Qty tak boleh Nol");
                        }else{
                            submit2.disabled = false;
                        }
                    },
            });
        }

        $('.select2').select2({
            placeholder: "Pilih",
            allowClear: true,
        });

        function refreshTable() {
          $('#data2-table').DataTable().ajax.reload(null,false);
            // table.draw();
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

        $('#ADD_DETAIL').submit(function (e) {
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false
            })
            e.preventDefault();
            var registerForm = $("#ADD_DETAIL");
            var formData = registerForm.serialize();

            // Check if empty of not
            $.ajax({
                    url:'{!! route('returjualdetail.check') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#kode_produks').val('').trigger('change');
                        $('#Satuan').val('').trigger('change');
                        $('#QTY').val('');
                        $('#Harga').val('');
                        $('#Jual').val('');
                        $('#Stock').val('');
                        $('#Parts').val('');
                        $('#Partserial').val('').trigger('change');
                        $('#Satuan_produk').val('');
                        $('.form-group3').hide();
                        $('.form-group4').hide();
                        
                        refreshTable();
                        // window.location.reload();
                        if (data.success === true) {
                            swal("Berhasil!", data.message, "success");
                            
                        } else {
                            swal("Gagal!", data.message, "error");
                        }
                    },
                });
        });


        $('#UPDATE_DETAIL').submit(function (e) {
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false
            })
            e.preventDefault();
            
            var registerForm = $("#UPDATE_DETAIL");
            var formData = registerForm.serialize();
            var id = $('#id').val();
            // Check if empty of not
                $.ajax({
                    url:'{!! route('returjualdetail.updateajax') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {

                        if(data.success === true) {
                            swal("Berhasil!", data.message, "success");
                        }else{
                            swal("Gagal!", data.message, "error");
                        }
                        refreshTable();
                        $(".addform").show();
                        $(".editform").hide();
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
                        $('#ID').val(results.id);
                        $('#nopakai2').val(results.no_penjualan);
                        $('#Produk2').val(results.kode_produk);
                        $('#Namaproduk_e').val(results.nama_produk);
                        $('#Satuan2').val(results.kode_satuan);
                        $('#Qty2').val(results.qty_retur);
                        $('#Harga2').val(results.harga);
                        $('#Jual2').val(results.harga_jual);
                        $(".addform").hide();
                        $("#Satuan2").html('');
                        $(".editform").show();
                        },
                        error : function() {
                        alert("Nothing Data");
                    }
                });
               
        }

        function cancel_edit(){
            $(".addform").show();
            $("#Satuan_e").html('');
            $(".editform").hide();
        }

        function del(id, url) {
            swal({
            title: "Hapus?",
            text: "Pastikan dahulu item yang akan di hapus",
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