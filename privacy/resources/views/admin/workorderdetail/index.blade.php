@extends('adminlte::page')

@section('title', 'WO Detail')

@section('content_header')
   
@stop

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <a href="{{ $list_url }}" class="btn btn-danger btn-xs"><i class="fa fa-arrow-left"></i> Kembali</a>
    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()"><i class="fa fa-refresh"></i> Refresh</button>
    <span class="pull-right">
        <font style="font-size: 16px;"> Detail WO <b>{{$work->no_wo}}</b></font>
    </span>
@include('sweet::alert')
<body onLoad="load()">
    {{ Form::hidden('Link',request()->getSchemeAndHttpHost(), ['class'=> 'form-control','readonly','id'=>'Link1']) }}
    <div class="box box-danger">
        <div class="box-body"> 
            <div class="addform">
            @include('errors.validation')
            {!! Form::open(['id'=>'ADD_DETAIL']) !!}
            <center><kbd>ADD FORM</kbd></center><br>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            {{ Form::label('No', 'No WO:') }}
                            {{ Form::text('no_wo',$work->no_wo, ['class'=> 'form-control','readonly','id'=>'NoWO1']) }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            {{ Form::label('Tipes', 'Type:') }}
                            {{ Form::select('type', ['Stock'=>'Stock','NonStock'=>'NonStock','Lainnya'=>'Lainnya'],null, ['class'=> 'form-control select2','style'=>'width:100%','id'=>'Type1','placeholder'=>'','required','onchange'=>'ambil()']) }}
                        </div>
                    </div>
                    <!--{{ Form::hidden('type','Stock', ['class'=> 'form-control','readonly','id'=>'Type1']) }}-->
                    <div class="col-md-5">
                        <div class="form-group">
                            {{ Form::label('Kodes', 'Produk:') }}
                            {{ Form::select('kode_produk',$Produk->sort(),null,['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Produk1','onchange'=>'stock();','required']) }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            {{ Form::label('partok', 'Part Number:') }}
                            {{ Form::select('partnumber',[], "-", ['class'=> 'form-control select2','id'=>'Parts','style'=>'width: 100%','autocomplete'=>'off','required']) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('Kodesss', 'Non Stock:') }}
                            {{ Form::text('nonstock',null,['class'=> 'form-control','id'=>'NonStock1']) }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('Kodesss', 'Item Lain:') }}
                            {{ Form::text('item_lain',null,['class'=> 'form-control','id'=>'Lainnya1']) }}
                        </div>
                    </div>
                                
                    <div class="col-md-1">
                        <div class="form-group">
                            {{ Form::label('qty', 'QTY:') }}
                            {{ Form::text('qty', null, ['class'=> 'form-control','required','id'=>'Qty1','autocomplete'=>'off','required']) }}
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group">
                            {{ Form::label('stock', 'Stock:') }}
                            {{ Form::text('qty_stock', null, ['class'=> 'form-control','readonly','id'=>'QtyStock1']) }}
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
                    {{ Form::hidden('id', null, ['class'=> 'form-control','readonly','id'=>'ID2']) }}
                    <div class="col-md-2">
                        <div class="form-group">
                            {{ Form::label('No', 'No WO:') }}
                            {{ Form::text('no_wo',$work->no_wo, ['class'=> 'form-control','readonly','id'=>'NoWO2']) }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            {{ Form::label('Tipes', 'Type:') }}
                            {{ Form::text('type',null, ['class'=> 'form-control','style'=>'width:100%','readonly','id'=>'Type2','placeholder'=>'','required']) }}
                        </div>
                    </div>
                    <!--{{ Form::hidden('type','Stock', ['class'=> 'form-control','readonly','id'=>'Type2']) }}-->
                    <div class="col-md-5">
                        <div class="form-group">
                            {{ Form::label('kode_produk', 'Produk:') }}
                            {{ Form::select('kode_produk',$Produk->sort(),null,['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Produk2','onchange'=>'stock2()']) }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            {{ Form::label('partnumber', 'Part Number:') }}
                            {{ Form::select('partnumber',[], null, ['class'=> 'form-control select2','id'=>'Parts2','required','style'=>'width: 100%','autocomplete'=>'off']) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('Kodesss', 'Non Stock:') }}
                            {{ Form::text('nonstock',null,['class'=> 'form-control','id'=>'NonStock2']) }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('Kodesss', 'Item Lain:') }}
                            {{ Form::text('item_lain',null,['class'=> 'form-control','id'=>'Lainnya2']) }}
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            {{ Form::label('qty', 'QTY:') }}
                            {{ Form::text('qty', null, ['class'=> 'form-control','required','id'=>'Qty2','autocomplete'=>'off']) }}
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            {{ Form::label('stock', 'Stock:') }}
                            {{ Form::text('qty_stock', null, ['class'=> 'form-control','readonly','id'=>'QtyStock2']) }}
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

   <div class="box box-danger">
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data2-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-info">
                        <th>No WO</th>
                        <th>Tipe</th>
                        <th>Produk</th>
                        <th>Partnumber</th>
                        <th>Qty</th>
                        <th>Qty Pakai</th>
                        <th>Action</th>
                     </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-info">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th id="totalqty">-</th>
                            <th id="totalqty">-</th>
                            <th>-</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

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
            @keyframes fade-inout {
              0%{ opacity: 1;}
              100%{ opacity: 0;}
            }
            /* support for opera */
            @-o-keyframes fade-inout{
              0%{ opacity: 1;}
              100%{ opacity: 0;}
            }
            /* support for mozila */
            @-moz-keyframes fade-inout{
              0%{ opacity: 1;}
              100%{ opacity: 0;}
            }
            /* support for safari and chrome */
            @-webkit-keyframes fade-inout{
              0%{ opacity: 1;}
              100%{ opacity: 0;}
            }
    </style>
</body>
@stop

@push('css')

@endpush
@push('js')
    <script>
        function load(){
            startTime();
            $('.form-group-warning').hide();
            $('.editform').hide();
            $('.tombol1').hide();
            $('.tombol2').hide();
            document.getElementById('Produk1').setAttribute("disabled","disabled");
            document.getElementById('Parts').setAttribute("disabled","disabled");
            document.getElementById('NonStock1').setAttribute("disabled","disabled");
            document.getElementById('Lainnya1').setAttribute("disabled","disabled");
        }
        
        function formatRupiah(angka, prefix='Rp'){
            var rupiah = angka.toLocaleString(
                undefined, // leave undefined to use the browser's locale,
                // or use a string like 'en-US' to override it.
                { minimumFractionDigits: 0 }
            );
            return rupiah;
        }
        
        function ambil(){
            var tipe = $('#Type1').val();
            console.log(tipe);
            if (tipe == 'Stock'){
                $('#NonStock1').val('').trigger('change');
                $('#Lainnya1').val('').trigger('change');
                document.getElementById('Produk1').removeAttribute("disabled");
                document.getElementById('Parts').removeAttribute("disabled");
                document.getElementById('NonStock1').setAttribute("disabled","disabled");
                document.getElementById('Lainnya1').setAttribute("disabled","disabled");
            }else if (tipe == 'NonStock') {
                $('#Produk1').val('').trigger('change');
                $('#Lainnya1').val('').trigger('change');
                $('#Parts').val('').trigger('change');
                document.getElementById('Lainnya1').setAttribute("disabled","disabled");
                document.getElementById('NonStock1').removeAttribute("disabled");
                document.getElementById('Produk1').setAttribute("disabled","disabled");
                document.getElementById('Parts').setAttribute("disabled","disabled");
            }else if (tipe == 'Lainnya') {
                $('#Produk1').val('').trigger('change');
                $('#Parts').val('').trigger('change');
                $('#Lainnya1').val('').trigger('change');
                $('#NonStock1').val('').trigger('change');
                document.getElementById('Lainnya1').removeAttribute("disabled");
                document.getElementById('NonStock1').setAttribute("disabled","disabled");
                document.getElementById('Produk1').setAttribute("disabled","disabled");
                document.getElementById('Parts').setAttribute("disabled","disabled");
            }else {
                $('#Produk1').val('').trigger('change');
                $('#Parts').val('').trigger('change');
                $('#Lainnya1').val('').trigger('change');
                $('#NonStock1').val('').trigger('change');
                document.getElementById('Lainnya1').setAttribute("disabled","disabled");
                document.getElementById('NonStock1').setAttribute("disabled","disabled");
                document.getElementById('Produk1').setAttribute("disabled","disabled");
                document.getElementById('Parts').setAttribute("disabled","disabled");
            }
        }
        
    $(function(){
        var no_pembelian = $('#NoWO1').val(); 
        var link = $('#Link1').val();
        $('#data2-table').DataTable({ 
            processing: true,
            serverSide: true,
            ajax:link+'/gui_inventory_laravel/admin/workorderdetail/getDatabyID?id='+no_pembelian,
            data:{'no_pembelian':no_pembelian},
            footerCallback: function ( row, data, start, end, display ) {
                    var api = this.api(), data;
        
                    // Remove the formatting to get integer data for summation
                    var intVal = function ( i ) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '')*1 :
                            typeof i === 'number' ?
                                i : 0;
                    };
        
                    // Total over all pages
                    total = api
                        .column( 4 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
        
                    // Update footer
                    $( api.column( 4 ).footer() ).html(
                         total
                    );
                    
            },
            columns: [
                { data: 'no_wo', name: 'no_wo' },
                { data: 'type', name: 'type' },
                { data: 'nama_produk', name: 'nama_produk', "defaultContent": "<i> - </i>"},
                { data: 'partnumber', name: 'partnumber', "defaultContent": "<i> - </i>"},
                { data: 'qty', name: 'qty' },
                { data: 'qty_pakai', name: 'qty_pakai', "defaultContent": "<i> - </i>" },
                { data: 'action', name: 'action' },
            ]
            
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
        
        function editpart(){
            $('#Parts').prop("disabled", false);
            var kode_produk = $("#Produk1").val();
            var token = $("input[name='_token']").val();
            $.ajax({
                url: "{!! route('workorderdetail.selectpart') !!}",
                method: 'POST',
                data: {kode_produk:kode_produk, _token:token},
                success: function(data) {
                    $("#Parts").html('');
                    $.each(data.options, function(key, value){
                        $('#Parts').val('');
                        $('#Parts').append('<option value="'+ key +'">' + value + '</option>');
                        $('#Parts').val('');
                    });
                }
            });
        }

        function editpart2(){
            $('#Parts2').prop("disabled", false);
            var kode_produk = $("#Produk2").val();
            var token = $("input[name='_token']").val();
            $.ajax({
                url: "{!! route('workorderdetail.selectpart') !!}",
                method: 'POST',
                data: {kode_produk:kode_produk, _token:token},
                success: function(data) {
                    $("#Parts2").html('');
                    $.each(data.options, function(key, value){
                        $('#Parts2').val('');
                        $('#Parts2').append('<option value="'+ key +'">' + value + '</option>');
                        $('#Parts2').val('');
                    });
                }
            });
        }
        
        function stock(){
            editpart();
            var kode_produk= $('#Produk1').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('workorderdetail.stockproduk') !!}',
                type:'POST',
                data : {
                        'id': kode_produk,
                    },
                success: function(result) {
                        
                        $('#QtyStock1').val(result.stok);
                    },
            });
        }

        function stock2(){
            editpart2();
            var kode_produk= $('#Produk2').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('workorderdetail.stockproduk') !!}',
                type:'POST',
                data : {
                        'id': kode_produk,
                    },
                success: function(result) {
                        
                        $('#QtyStock2').val(result.stok);
                    },
            });
        }

        function satuan_non(){
            var kode_satuan = $('#Satuan').val();
            console.log(kode_satuan);
            $('#satuannon').val(kode_satuan);
        }

        function pilih1() {
             $('#Satuan2').val('');
        }

        function pilih2() {
             $('#Satuan1').val('');
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

        function satuan(){
            var kode_produk = $('#kode_produk1').val();
            var kode = $('#Kode_Terbesar1').val();
            var nilai = $('#Nilai1').val();
            $.ajax({
                url:'{!! route('konversi.satuan_produk') !!}',
                type:'POST',
                data : {
                        'id': kode_produk,
                        'kode' : kode,
                    },
                success: function(result) {
                        console.log(result);
                        $('#Kode_Terkecil1').val(result.kode_satuan);
                        $('#Terkecil1').val(result.satuan);
                        $('#Terbesar1').val(result.satuan_terbesar);
                    },
            });
        }

        function satuan2(){
            var kode = $('#Kode_Terbesar1').val();
            var kode2 = $('#Kode_Terkecil1').val();
            console.log(kode);
            $.ajax({
                url:'{!! route('konversi.satuan_produk2') !!}',
                type:'POST',
                data : {
                        'kode' : kode,
                    },
                success: function(result) {
                        console.log(result);
                        $('#Terbesar1').val(result.satuan_terbesar);

                        if(kode == kode2){
                            $('#Nilai1').val('1');
                            swal("Nilai Konversi Harus 1");
                        }
                    },
            });
        }



        $('#ADD_PRODUK').submit(function (e) {
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false
            })
            e.preventDefault();
            var registerForm = $("#ADD_PRODUK");
            var formData = registerForm.serialize();

            // Check if empty of not
            
                $.ajax({
                    url:'{!! route('produk.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#Nama_produk').val('');
                        $('#Kategori_produk').val('').trigger('change');
                        $('#Merek_produk').val('').trigger('change');
                        $('#Satuan_produk').val('').trigger('change');
                        $('#Part_produk').val('');
                        $('#Beli_produk').val('');
                        $('#Jual_produk').val('');
                        $('#HPP_produk').val('');
                        $('#Stok_produk').val('');
                        $('#Aktif_produk').val('');
                        $('#ADD_FORMPRODUK').modal('hide');
                        refreshTable();
                        if (data.success === true) {
                            swal("Berhasil!", data.message, "success");
                            location.reload();
                        } else {
                            swal("Gagal!", data.message, "error");
                        }
                    },
                });
        });

        $('#ADD_JASA').submit(function (e) {
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false
            })
            e.preventDefault();
            var registerForm = $("#ADD_JASA");
            var formData = registerForm.serialize();

                $.ajax({
                    url:'{!! route('jasa.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#Jenis1').val('').trigger('change');
                        $('#Nama1').val('');
                        $('#Nama2').val('');
                        $('#Satuan1').val('').trigger('change');
                        $( '.kode-error' ).html('');
                        $( '.name-error' ).html('');
                        $('#ADD_FORMJASA').modal('hide');
                        refreshTable();
                        if (data.success === true) {
                            swal("Berhasil!", data.message, "success");
                            location.reload();
                        } else {
                            swal("Gagal!", data.message, "error");
                        }
                    },
                });
        });

        $('#ADD_KONVERSI').submit(function (e) {
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false
            })
            e.preventDefault();
            var registerForm = $("#ADD_KONVERSI");
            var formData = registerForm.serialize();

            // Check if empty of not
                $.ajax({
                    url:'{!! route('konversi.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#kode_produk1').val('').trigger('change');
                        $('#Kode_Terbesar1').val('').trigger('change');
                        $('#Nilai1').val('');
                        $('#Terkecil1').val('');
                        $('#ADD_SATUANKONVERSI').modal('hide');
                        refreshTable();
                        if (data.success === true) {
                            swal("Berhasil!", data.message, "success");
                            location.reload();
                        } else {
                            swal("Gagal!", data.message, "error");
                        }   
                    },
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
             $('#data2-table').DataTable().ajax.reload(null,false);;
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
                url:'{!! route('workorderdetail.store') !!}',
                type:'POST',
                data:formData,
                success:function(data) {
                    console.log(data);
                    $('#Produk1').val('').trigger('change');
                    $('#Parts').val('').trigger('change');
                    $('#Type1').val('').trigger('change');
                    $('#NonStock1').val('').trigger('change');
                    $('#Lainnya1').val('').trigger('change');
                    $('#Qty1').val('');
                        
                    refreshTable();
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
            $.ajax({
                url:'{!! route('workorderdetail.updateajax') !!}',
                type:'POST',
                data:formData,
                success:function(data) {
                    $('#Qty1').val('');
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
                    $(".editform").show();
                    $('#NoWO2').val(results.no_wo);
                    $('#Type2').val(results.type).trigger('change');
                        
                    if (results.type == 'Stock'){
                        document.getElementById("Parts2").removeAttribute("disabled");
                        document.getElementById('Produk2').removeAttribute("disabled");
                        document.getElementById('NonStock2').setAttribute("disabled","disabled");
                        document.getElementById("Lainnya2").setAttribute("disabled","disabled");
                        $('#Lainnya2').val('');
                        $('#NonStock2').val('');
                        $('#Produk2').val(results.kode_produk).trigger('change');
                        $('#Parts2').val(results.partnumber).trigger('change');
                    }else if(results.type == 'NonStock'){
                        document.getElementById("NonStock2").removeAttribute("disabled");
                        document.getElementById("Produk2").setAttribute("disabled","disabled");
                        document.getElementById("Parts2").setAttribute("disabled","disabled");
                        document.getElementById("Lainnya2").setAttribute("disabled","disabled");
                        $('#Lainnya2').val('');
                        $('#NonStock2').val(results.nama_produk);
                        $('#Produk2').val(results.kode_produk).trigger('change');
                        $('#Parts2').val(results.partnumber).trigger('change');
                    }else if(results.type == 'Lainnya'){
                        document.getElementById("Lainnya2").removeAttribute("disabled");
                        document.getElementById("Produk2").setAttribute("disabled","disabled");
                        document.getElementById("Parts2").setAttribute("disabled","disabled");
                        document.getElementById("NonStock2").setAttribute("disabled","disabled");
                        $('#Lainnya2').val(results.nama_produk);
                        $('#NonStock2').val('');
                        $('#Produk2').val(results.kode_produk).trigger('change');
                        $('#Parts2').val(results.partnumber).trigger('change');
                    }
                    
                    $('#Qty2').val(results.qty);
                    $('#ID2').val(results.id);
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