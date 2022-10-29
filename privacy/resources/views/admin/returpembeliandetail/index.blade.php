@extends('adminlte::page')

@section('title', 'Retur Pembelian Detail')

@section('content_header')
    
@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <a href="{{ $list_url }}" class="btn btn-warning btn-xs"><i class="fa fa-arrow-left"></i> Kembali</a>
    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()"><i class="fa fa-refresh"></i> Refresh</button>

    <span class="pull-right">
        <font style="font-size: 16px;"> Detail Retur Pembelian <b>{{$returpembelian->no_returpembelian}}</b></font>
    </span>
@include('sweet::alert')
<body onLoad="load()">
    <div class="box box-warning">
        <div class="box-body"> 
                <div class="addform">
                    @include('errors.validation')
                    {!! Form::open(['id'=>'ADD_DETAIL']) !!}
                    <center><kbd>ADD FORM</kbd></center><br>
                        <div class="row">   
                        {{ Form::hidden('Link',request()->getSchemeAndHttpHost(), ['class'=> 'form-control','readonly','id'=>'Link1']) }}
                                <div class="col-md-2">
                                    <div class="form-group1">
                                        {{ Form::label('no_returpembelian', 'No Retur Pembelian:') }}
                                        {{ Form::text('no_returpembelian',$returpembelian->no_returpembelian, ['class'=> 'form-control','readonly','id'=>'noretur']) }}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group2">
                                        {{ Form::label('no_penerimaan', 'No Penerimaan:') }}
                                        {{ Form::text('no_penerimaan',$returpembelian->no_penerimaan, ['class'=> 'form-control','readonly','id'=>'noterima']) }}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group3">
                                        {{ Form::label('kode_produk', 'Produk:') }}
                                        {{ Form::select('kode_produk',$Produk->sort(),null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','required'=>'required','id'=>'kode_produk','onchange'=>'getinfo();']) }}

                                        {{ Form::hidden('tipe_produk', null, ['class'=> 'form-control','readonly','id'=>'tipe_produk']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group4">
                                        {{ Form::label('kode_satuan', 'Satuan:') }}
                                        {{ Form::text('kode_satuan',null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => '','readonly','id'=>'Satuan']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group5">
                                        {{ Form::label('partnumber', 'Part Number:') }}
                                        {{ Form::select('partnumber',$Parts,null, ['class'=> 'form-control select2','style'=>'width: 100%','id'=>'Partserial','autocomplete'=>'off','placeholder' => '','onchange'=>'getstock();']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group6">
                                        {{ Form::label('partnumber', 'Part Number:') }}
                                        {{ Form::text('partnumber',null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'Part','autocomplete'=>'off','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group7">
                                        {{ Form::label('no_mesin', 'No Mesin:') }}
                                        {{ Form::text('no_mesin',null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'Mesin','autocomplete'=>'off','readonly']) }}
                                    </div>
                                </div>
                    
                                <div class="col-md-2">
                                    <div class="form-group8">
                                        {{ Form::label('qty_terima', 'QTY Penerimaan:') }}
                                        {{ Form::text('qty_terima', null, ['class'=> 'form-control','required'=>'required',
                                         'id'=>'Qty','readonly']) }}
                                    </div>
                                </div>
                    
                                <div class="col-md-2">
                                    <div class="form-group9">
                                        {{ Form::label('qty', 'QTY Retur:') }}
                                        {{ Form::text('qty', null, ['class'=> 'form-control','required'=>'required',
                                         'id'=>'Qtyretur','onkeyup'=>'check();','autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group10">
                                        {{ Form::label('harga', 'Harga Satuan:') }}
                                        {{ Form::text('harga',null, ['class'=> 'form-control','required'=>'required','readonly',
                                         'id'=>'Harga']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group11">
                                        {{ Form::label('landedcost', 'Landed Cost Satuan:') }}
                                        {{ Form::text('landedcost',null, ['class'=> 'form-control','required','readonly',
                                         'id'=>'Landed','autocomplete'=>'off']) }}
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
                                        {{ Form::label('no_returpembelian', 'No Retur Pembelian:') }}
                                        {{ Form::text('no_returpembelian',$returpembelian->no_returpembelian, ['class'=> 'form-control','readonly','id'=>'Noretur']) }}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::hidden('id',null, ['class'=> 'form-control','readonly','id'=>'ID']) }}
                                        {{ Form::label('No Penerimaan', 'No Penerimaan:') }}
                                        {{ Form::text('no_penerimaan',$returpembelian->no_penerimaan, ['class'=> 'form-control','readonly','id'=>'Penerimaan']) }}
                                    </div>
                                </div>
                                
                                {{ Form::hidden('kode_produk', null, ['class'=> 'form-control','readonly','id'=>'Produk']) }}

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
                                        {{ Form::label('qty', 'QTY Retur:') }}
                                        {{ Form::text('qty', null, ['class'=> 'form-control','id'=>'Qty2','required'=>'required','onkeyup'=>"check2()",'autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('harga', 'Harga Satuan:') }}
                                        {{ Form::text('harga',null, ['class'=> 'form-control','id'=>'Harga2','required'=>'required','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('landedcost', 'Landed Cost Satuan:') }}
                                        {{ Form::text('landedcost',null, ['class'=> 'form-control','id'=>'Landed2','required','readonly','autocomplete'=>'off']) }}
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

    <div class="box box-warning">
        <div class="box-body">
            <div class="table-responsive"> 
                <table class="table table-bordered table-striped table-hover" id="data2-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-info">
                        <th>No Retur Pembelian</th>
                        <th>No Penerimaan</th>
                        <th>Produk</th>
                        <th>Satuan</th>
                        <th>Part Number</th>
                        <th>No Mesin</th>
                        <th>Qty</th>
                        <th>Harga Satuan</th>
                        <th>Landed Cost Satuan</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                     </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-info">
                            <th class="text-center" colspan="6">Total</th>
                            <th id="totalqty">-</th>
                            <th></th>
                            <th></th>
                            <th id="grandtotal">-</th>
                            <th></th>
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
            $('.editform').hide();
            $('.addform').show();
            $('.back2Top').show();
            $('.form-group5').hide();
            $('.form-group6').hide();
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
        var no_returpembelian = $('#noretur').val();
        var link = $('#Link1').val();
            $('#data2-table').DataTable({
            processing: true,
            serverSide: true,
            ajax:link+'/gui_inventory_laravel/admin/returpembeliandetail/getDatabyID?id='+no_returpembelian,
            data:{'no_returpembelian':no_returpembelian},
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
                        .column( 6 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
        
                    // Total over this page
                    grandTotal = api
                        .column( 9 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                        
                    // Update footer
                    $( api.column( 6 ).footer() ).html(
                        total
                    );
                    $( api.column( 9 ).footer() ).html(
                        formatRupiah(grandTotal) 
                    );
                },
            columns: [
                { data: 'no_returpembelian', name: 'no_returpembelian' },
                { data: 'no_penerimaan', name: 'no_penerimaan' },
                { data: 'produk.nama_produk', name: 'produk.nama_produk' },
                { data: 'satuan.nama_satuan', name: 'satuan.nama_satuan', searchable : false },
                { data: 'partnumber', name: 'partnumber' },
                { data: 'no_mesin', name: 'no_mesin' },
                { data: 'qty', name: 'qty' },
                { data: 'harga', 
                    render: function( data, type, full ) {
                    return formatNumber(data); }
                },
                { data: 'landedcost', 
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

        $(function() {
            $('#data-table').DataTable({
                scrollY: true,
                scrollX: true,
            });
        });

        function getinfo(){
            var no_penerimaan = $('#noterima').val();
            var kode_produk = $('#kode_produk').val();
            $.ajax({
                url:'{!! route('returpembeliandetail.getinfo') !!}',
                type:'POST',
                data : {
                        'no_penerimaan': no_penerimaan,
                        'kode_produk': kode_produk,
                    },
                success: function(result) {
                        console.log(result);
                        if (result.tipe == "Serial" && result.kategori == "BAN"){
                            document.getElementById('Part').disabled = true;
                            document.getElementById('Partserial').disabled = false;
                            $('.form-group5').show();
                            $('.form-group6').hide();
                            $('#Satuan').val(result.kode_satuan);
                            $('#Mesin').val(result.no_mesin);
                            $('#Qty').val(result.qty);
                            $('#Harga').val(result.harga);
                            $('#Landed').val(result.landedcost);
                            $('#Qtyretur').val('');
                        }
                        else{
                            document.getElementById('Part').disabled = false;
                            document.getElementById('Partserial').disabled = true;
                            $('.form-group5').hide();
                            $('.form-group6').show();
                            $('#Satuan').val(result.kode_satuan);
                            $('#Part').val(result.partnumber);
                            $('#Mesin').val(result.no_mesin);
                            $('#Qty').val(result.qty);
                            $('#Harga').val(result.harga);
                            $('#Landed').val(result.landedcost);
                            $('#Qtyretur').val('');
                        }
                    },
            });
        }

        function getstock(){
            var no_penerimaan= $('#noterima').val();
            var kode_produk= $('#kode_produk').val();
            var partnumber= $('#Partserial').val();

             $.ajax({
                url:'{!! route('returpembeliandetail.getstock') !!}',
                type:'POST',
                data : {
                        'no_penerimaan': no_penerimaan,
                        'kode_produk': kode_produk,
                        'partnumber': partnumber,
                    },

                success: function(result) {
                        console.log(result);
                        $('#Qty').val(result.qty);
                        $('#Qtyretur').val('');
                    },
            });
        }
        
        function check(){
            var qtyretur = $('#Qtyretur').val();
            var qty = parseInt($('#Qty').val());
            if(qtyretur > qty){
                submit.disabled = true;
                swal("Gagal!", "Qty retur melebihi qty penerimaan");
            }
            else{
                submit.disabled = false;
            }
        }
        
        function check2(){
            var no_penerimaan= $('#Penerimaan').val();
            var kode_produk= $('#Produk').val();
            var qty = $('#Qty2').val();

             $.ajax({
                url:'{!! route('returpembeliandetail.qtyproduk') !!}',
                type:'POST',
                data : {
                        'no_penerimaan': no_penerimaan,
                        'kode_produk': kode_produk,
                        'qty': qty,
                    },

                success: function(result) {
                        console.log(result);
                        if(result == 'false'){
                            submit2.disabled = true;
                            swal("Gagal!", "Qty retur melebihi qty penerimaan");
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
                    url:'{!! route('returpembeliandetail.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#kode_produk').val('').trigger('change');
                        $('#Satuan').val('');
                        $('#Part').val('');
                        $('#Partserial').val('').trigger('change');
                        $('#Mesin').val('');
                        $('#Qty').val('');
                        $('#Qtyretur').val('');
                        $('#Harga').val('');
                        $('#Landed').val('');   
                        $('.form-group5').hide();
                        $('.form-group6').hide();
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
                $.ajax({
                    url:'{!! route('returpembeliandetail.updateajax') !!}',
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
                        $('#ID').val(results.id);
                        $('#Penerimaan').val(results.no_penerimaan);
                        $('#Produk').val(results.kode_produk);
                        $('#Namaproduk_e').val(results.nama_produk);
                        $('#Satuan2').val(results.kode_satuan);
                        $('#Qty2').val(results.qty);
                        $('#Harga2').val(results.harga);
                        $('#Landed2').val(results.landedcost);
                        $(".addform").hide();
                        $(".editform").show();
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