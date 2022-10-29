@extends('adminlte::page')

@section('title', 'Pemakaian Ban Detail')

@section('content_header')
    
@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <a href="{{ $list_url }}" class="btn btn-success btn-xs"><i class="fa fa-arrow-left"></i> Kembali</a>
    <button type="button" class="btn btn-default btn-xs" onclick="tablefresh()"><i class="fa fa-refresh"></i> Refresh</button>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <button type="button" class="btn btn-warning btn-xs" onclick="gethpp()"><i class="fa fa-search"></i> Update HPP</button>
    <span class="pull-right">
        <font style="font-size: 16px;"> Detail Pemakaian Ban<b>{{$pemakaianban->no_pemakaianban}}</b></font>
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
                        {{ Form::hidden('Link',request()->getSchemeAndHttpHost(), ['class'=> 'form-control','readonly','id'=>'Link1']) }}
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('No Pemakaian Ban', 'No Pemakaian Ban:') }}
                                        {{ Form::text('no_pemakaianban',$pemakaianban->no_pemakaianban, ['class'=> 'form-control','readonly','id'=>'nopakaiban']) }}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('kode_produk', 'Produk:') }}
                                        {{ Form::select('kode_produk',$Produk->sort(),null,
                                         ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','required'=>'required','onchange'=>'stock();',
                                         'id'=>'kode_produks']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('partnumber', 'Serial Number Lama:') }}
                                        {{ Form::text('partnumber', null, ['class'=> 'form-control','id'=>'Parts','required'=>'required','style'=>'width: 100%','autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('partnumberbaru', 'Serial Number Baru:') }}
                                        {{ Form::select('partnumberbaru',[], null, ['class'=> 'form-control','id'=>'Partbarus','required'=>'required','style'=>'width: 100%','autocomplete'=>'off','onchange'=>'getharga();']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('kode_satuan', 'Satuan:') }}
                                        {{ Form::select('kode_satuan', ['-'],null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','required'=>'required','id'=>'Satuan']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                        <div class="form-group">
                                            {{ Form::label('qty_stock', 'Stock Tersedia:') }}
                                            {{ Form::text('qty_stock', null, ['class'=> 'form-control','readonly','id'=>'Stock']) }}
                                        </div>
                                </div>
                    
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('qty', 'QTY:') }}
                                        {{ Form::number('qty', null, ['class'=>'form-control','required'=>'required','id'=>'QTY','onkeyup'=>'check();','autocomplete'=>'off','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('harga', 'HPP:') }}
                                        {{ Form::number('harga',null, ['class'=> 'form-control','id'=>'Harga','readonly']) }}
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
                                    {{ Form::label('No Pemakaian Ban', 'No Pemakaian Ban:') }}
                                    {{ Form::hidden('id', null, ['class'=> 'form-control','id'=>'ID']) }}
                                    {{ Form::text('no_pemakaianban',$pemakaianban->no_pemakaianban, ['class'=> 'form-control','readonly','id'=>'Pemakaian_e']) }}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('kode_produk', 'Produk:') }}
                                    {{ Form::text('kode_produk',null, ['class'=> 'form-control','id'=>'Produk_e','required'=>'required','readonly']) }}
                                </div>
                            </div>
                
                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('kode_satuan', 'Satuan:') }}
                                    {{ Form::text('kode_satuan',null, ['class'=> 'form-control','id'=>'Satuan_e','readonly']) }}
                                </div>
                            </div>
                
                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('qty', 'QTY:') }}
                                    {{ Form::text('qty', null, ['class'=> 'form-control','id'=>'QTY_e','required'=>'required','onkeyup'=>"cek_qty2()",'autocomplete'=>'off']) }}
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('harga', 'HPP:') }}
                                    {{ Form::text('harga',null, ['class'=> 'form-control','id'=>'Harga_e','required'=>'required','readonly']) }}
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
                        <th>No Pemakaian Ban</th>
                        <th>Produk</th>
                        <th>Serial Number Lama</th>
                        <th>Serial Number Baru</th>
                        <th>Satuan</th>
                        <th>Qty</th>
                        <th>Harga</th>
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

    <button type="button" class="back2Top btn btn-warning btn-xs" id="back2Top"><i class="fa fa-map-marker" style="color: #fff"></i> <i>{{ $nama_company }}</i> <b>({{ $nama_lokasi }})</b></button>

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
            $('.editform').hide();
            $('.addform').show();
            $('.back2Top').show();
            $('.form-group4').hide();
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
        var no_pemakaianban = $('#nopakaiban').val();
        var link = $('#Link1').val();
            $('#data2-table').DataTable({
                
            processing: true,
            serverSide: true,
            ajax:link+'/gui_inventory_laravel/admin/pemakaianbandetail/getDatabyID?id='+no_pemakaianban,
            data:{'no_pemakaianban':no_pemakaianban},
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
                { data: 'no_pemakaianban', name: 'no_pemakaianban' },
                { data: 'produk.nama_produk', name: 'produk.nama_produk' },
                { data: 'partnumber', name: 'partnumber' },
                { data: 'partnumberbaru', name: 'partnumberbaru' },
                { data: 'satuan.nama_satuan', name: 'satuan.nama_satuan', searchable : false },
                { data: 'qty', name: 'qty' },
                { data: 'harga', 
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
        
        function gethpp(){
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false
            })
            var no_pemakaianban = $('#nopakaiban').val();

            $.ajax({
                url:'{!! route('pemakaianbandetail.gethpp') !!}',
                type:'POST',
                data : {
                        'id': no_pemakaianban
                    },
                success: function(result) {
                        console.log(result);

                        if (result.success === true) {
                            swal("Berhasil!", result.message, "success");
                        } else {
                            swal("Gagal!", result.message, "error");
                        }
                        // window.location.reload();
                        refreshTable();
                    },
            });
        }
        
        function stock(){
            editpart();
            editpart2();
            editpart3();
            var kode_produk= $('#kode_produks').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('pemakaianbandetail.stockproduk') !!}',
                type:'POST',
                data : {
                        'id': kode_produk,
                    },
                success: function(result) {
                        console.log(result);
                        if(result.stok < 1){
                            submit.disabled = true;
                        }else{
                            submit.disabled = false;
                        }
                        if (result.tipe == 'Serial' && result.kategori == 'BAN'){
                            $('#QTY').val('');
                            $('#Harga').val('');
                            $('#Stock').val('');
                            document.getElementById('QTY').readOnly = true;
                            document.getElementById('Parts').required = true;
                        }else {
                            document.getElementById('QTY').readOnly = false;
                            document.getElementById('Parts').required = false;
                            $('#QTY').val('');   
                            $('#Harga').val(result.hpp);
                            $('#Stock').val(result.stok);
                        }
                    },
            });
        }

        function editpart(){
                    $('#Parts').prop("disabled", false);
                    var kode_produk = $("#kode_produks").val();
                    console.log(kode_produk);
                    
                    var token = $("input[name='_token']").val();
                    $.ajax({
                    url: "{!! route('pemakaianbandetail.selectpart') !!}",
                    method: 'POST',
                    data: {kode_produk:kode_produk, _token:token},
                    success: function(data) {
                        $("#Parts").html('');
                        // $("select[name='kode_satuan'").html(data.options);
                            $.each(data.options, function(key, value){

                                $('#Parts').val('');
                                $('#Parts').append('<option value="'+ key +'">' + value + '</option>');
                                $('#Parts').val('');

                            });
                        }
                    });
        }

        function editpart2(){
                    $('#Partbarus').prop("disabled", false);
                    var kode_produk = $("#kode_produks").val();
                    console.log(kode_produk);
                    
                    var token = $("input[name='_token']").val();
                    $.ajax({
                    url: "{!! route('pemakaianbandetail.selectpart2') !!}",
                    method: 'POST',
                    data: {kode_produk:kode_produk, _token:token},
                    success: function(data) {
                        $("#Partbarus").html('');
                        // $("select[name='kode_satuan'").html(data.options);
                            $.each(data.options, function(key, value){

                                $('#Partbarus').val('');
                                $('#Partbarus').append('<option value="'+ key +'">' + value + '</option>');
                                $('#Partbarus').val('');
                            });
                        }
                    });
        }

        function editpart3(){
                    $('#Partbarus2').prop("disabled", false);
                    var kode_produk = $("#kode_produks").val();
                    console.log(kode_produk);
                    
                    var token = $("input[name='_token']").val();
                    $.ajax({
                    url: "{!! route('pemakaianbandetail.selectpart3') !!}",
                    method: 'POST',
                    data: {kode_produk:kode_produk, _token:token},
                    success: function(data) {
                        $("#Partbarus2").html('');
                        // $("select[name='kode_satuan'").html(data.options);
                            $.each(data.options, function(key, value){

                                $('#Partbarus2').val('');
                                $('#Partbarus2').append('<option value="'+ key +'">' + value + '</option>');
                                $('#Partbarus2').val('');
                            });
                        }
                    });
        }

        function getharga(){
            var partnumber= $('#Partbarus').val();
            var kode_produk= $('#kode_produks').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('pemakaianbandetail.getharga') !!}',
                type:'POST',
                data : {
                        'id': kode_produk,
                        'part': partnumber,
                    },
                success: function(result) {
                        console.log(result);
                        $('#QTY').val('1');
                        $('#Harga').val(result.hpp);
                        $('#Stock').val(result.stok);
                        if(result.stok < 1){
                            submit.disabled = true;
                        }else{
                            submit.disabled = false;
                        }
                    },
            });
        }

        function getharga2(){
            var partnumber= $('#Partbarus2').val();
            var kode_produk= $('#kode_produks').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('pemakaianbandetail.getharga2') !!}',
                type:'POST',
                data : {
                        'id': kode_produk,
                        'part': partnumber,
                    },
                success: function(result) {
                        console.log(partnumber);
                        $('#QTY').val('1');
                        $('#Harga').val(result.hpp);
                        $('#Stock').val(result.stok);
                        if(result.stok < 1){
                            submit.disabled = true;
                        }else{
                            submit.disabled = false;
                        }
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
                url:'{!! route('pemakaianbandetail.qtycheck') !!}',
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
                            swal("Gagal!", "Stok Tidak Cukup");
                        }else{
                            submit.disabled = false;
                        }
                    },
            });
        }


        function cek_qty2(){
            var kode_produk= $('#Produk_e').val();
            var qty = $('#QTY_e').val();

             $.ajax({
                url:'{!! route('pemakaianbandetail.qtyproduk2') !!}',
                type:'POST',
                data : {
                        'kode_produk': kode_produk,
                    },

                success: function(result) {
                        console.log(result);
                        var qty = $('#QTY_e').val();
                        if(result < qty){
                            submit2.disabled = true;
                            swal("Gagal!", "Stok Tidak Cukup");
                        }else{
                            submit2.disabled = false;
                        }
                    },
            });
        }


        function editsatuan(){
                    var kode_produk = $("#Produk_e").val();
                    console.log(kode_produk);
                    
                    var token = $("input[name='_token']").val();
                $.ajax({
                    url: "{!! route('pemakaianbandetail.selectAjax') !!}",
                    method: 'POST',
                    data: {kode_produk:kode_produk, _token:token},
                    success: function(data) {
                        $("#Satuan_e").html('');
                        // $("select[name='kode_satuan'").html(data.options);
                            $.each(data.options, function(key, value){

                                $('#Satuan_e').append('<option value="'+ key +'">' + value + '</option>');

                            });
                        }
                    });
        }
        

        $("select[name='kode_produk']").change(function(){
            var kode_produk = $(this).val();
            // console.log(kode_produk);
            var token = $("input[name='_token']").val();
            $.ajax({
                url: "{!! route('pemakaianbandetail.selectAjax') !!}",
                method: 'POST',
                data: {kode_produk:kode_produk, _token:token},
                success: function(data) {
                    $("#Satuan").html('');
                    // $("select[name='kode_satuan'").html(data.options);
                    $.each(data.options, function(key, value){

                        $("#Satuan").append('<option value="'+ key +'">' + value + '</option>');

                    });
                }
            });
        });

        $('.select2').select2({
            placeholder: "Pilih",
            allowClear: true,
        });

        function tablefresh(){
                window.table.draw();
            } 

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
                    url:'{!! route('pemakaianbandetail.check') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#kode_produks').val('').trigger('change');
                        $('#Satuan').val('').trigger('change');
                        $('#Parts').val('');
                        $('#Partbarus').val('');
                        $('#Partbarus2').val('');
                        $('#QTY').val('');
                        $('#Harga').val('');
                        $('#Stock').val('');
                        $('#Satuan_produk').val('');
                        
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
                    url:'{!! route('pemakaianbandetail.updateajax') !!}',
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
                        $('#Pemakaian_e').val(results.no_pemakaianban);
                        $('#Produk_e').val(results.kode_produk);
                        $('#Satuan_e').val(results.kode_satuan);
                        $('#QTY_e').val(results.qty);
                        $('#Harga_e').val(results.harga);
                        $(".addform").hide();
                        $("#Satuan_e").html('');
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