@extends('adminlte::page')

@section('title', 'Memo Detail')

@section('content_header')
   
@stop

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <a href="{{ $list_url }}" class="btn btn-danger btn-xs"><i class="fa fa-arrow-left"></i> Kembali</a>
    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()"><i class="fa fa-refresh"></i> Refresh</button>
    <span class="pull-right">
        <font style="font-size: 16px;"> Detail Memo <b>{{$memo->no_memo}}</b></font>
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
                                        {{ Form::label('No Memo', 'No Memo:') }}
                                        {{ Form::text('no_memo',$memo->no_memo, ['class'=> 'form-control','readonly','id'=>'nomemo']) }}
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        {{ Form::label('kode_produk', 'Produk:') }}
                                        {{ Form::select('kode_produk',$Produk->sort(),null,
                                         ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','required'=>'required',
                                         'id'=>'produk']) }}
                                    </div>
                                </div>
                  
                                <div class="col-md-1">
                                    <div class="form-group">
                                        {{ Form::label('qty', 'QTY:') }}
                                        {{ Form::text('qty', null, ['class'=> 'form-control','required'=>'required','id'=>'QTY','autocomplete'=>'off']) }}
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
                                        {{ Form::hidden('id',null, ['class'=> 'form-control','readonly','id'=>'ID']) }}
                                        {{ Form::label('No Memo', 'No Memo:') }}
                                        {{ Form::text('no_memo',$memo->no_memo, ['class'=> 'form-control','readonly','id'=>'nomemo2']) }}
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        {{ Form::label('kode_produk', 'Nama Produk:') }}
                                        {{ Form::select('kode_produk',$Produk->sort(),null,['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'produk2']) }}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('qty', 'QTY:') }}
                                        {{ Form::text('qty', null, ['class'=> 'form-control','id'=>'QTY2','required'=>'required','autocomplete'=>'off']) }}
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
                        <th>No Memo</th>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Qty TO</th>
                        <th>Action</th>
                     </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-info">
                            <th></th>
                            <th></th>
                            <th id="totalqty">-</th>
                            <th id="totalqtyto">-</th>
                            <th></th>
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
            .fade-in-fade-out {
               -webkit-animation: fade-inout 0.1s infinite alternate;
               -moz-animation: fade-inout 0.1s infinite alternate;
               -o-animation: fade-inout 0.1s infinite alternate;
                animation: fade-inout 0.1s infinite alternate;
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
            $('.form-group-warning').hide();

            $('.editform').hide();

            var jenispo = $('#jenispo').val();  
            if(jenispo != 'Stock'){
                $("#button_add").hide();
                $("#button_add3").show();
                $("#button_add2").hide();
            }else{
                $("#button_add3").hide();
            }
            $('.tombol1').hide();
            $('.tombol2').hide();
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
        var no_memo = $('#nomemo').val();
        var link = $('#Link1').val();
        $('#data2-table').DataTable({      
            processing: true,
            serverSide: true,
            ajax:link+'/gui_inventory_laravel/admin/memodetail/getDatabyID?id='+no_memo,
            data:{'no_memo':no_memo},
            columns: [
                { data: 'no_memo', name: 'no_memo' },
                { data: 'produk.nama_produk', name: 'produk.nama_produk'},
                { data: 'qty', name: 'qty' },
                { data: 'qty_to', name: 'qty_to' },
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
        
        
        // function stock(){
        //     var kode_produk= $('#kode_produk').val();
        //     var no_pembelian= $('#nobeli').val();
        //     $.ajax({
        //         url:'{!! route('memodetail.stockproduk') !!}',
        //         type:'POST',
        //         data : {
        //                 'id': kode_produk,
        //                 'no': no_pembelian
        //             },
        //         success: function(result) {
        //                 console.log(result);
        //                 $('#Stock').val(result.stock);
        //                 $('#HPP').val(result.harga_beli);
        //                 if(result.max_qty == 0)
        //                 {
        //                     $('.form-group-warning').hide();
        //                 }
        //                 else if (result.stock > result.max_qty)
        //                 {
        //                     $('.form-group-warning').show();
        //                     document.getElementById('Warning').innerHTML = result.max_qty;
        //                 }
        //                 else if(result.stock < result.max_qty)
        //                 {
        //                     $('.form-group-warning').hide();
        //                 }
        //             },
        //     });
        // }

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
                    url:'{!! route('memodetail.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#produk').val('').trigger('change')
                        $('#QTY').val('');
                        $('#satuannon').val('');
                        $('#Keterangan').val('');
                        
                        refreshTable();
                        // window.location.reload();
                        if (data.success === true) {
                            swal("Berhasil!", data.message, "success");
                            $("#HPP").prop('readonly', true);
                        } else {
                            swal("Gagal!", data.message, "error");
                            $("#HPP").prop('readonly', true);
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
                    url:'{!! route('memodetail.updateajax') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        $('#QTY2').val('');
                        $('#QTY').val('');

                        if(data.success === true) {
                            swal("Berhasil!", data.message, "success");
                            $("#Harga").prop('readonly', true);
                        }else{
                            swal("Gagal!", data.message, "error");
                            $("#Harga").prop('readonly', true);
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
                        $('#nomemo2').val(results.no_memo);
                        $('#produk2').val(results.kode_produk).trigger('change');
                        $('#QTY2').val(results.qty);
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