@extends('adminlte::page')

@section('title', 'Transfer In Detail')

@section('content_header')
    
@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <a href="{{ $list_url }}" class="btn btn-warning btn-xs"><i class="fa fa-arrow-left"></i> Kembali</a>
    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()"><i class="fa fa-refresh"></i> Refresh</button>
    <span class="pull-right">
        <font style="font-size: 16px;"> Detail Transfer In <b>{{$transferin->no_trf_in}}</b></font>
    </span>
@include('sweet::alert')
<body onLoad="panggil()">
    {{ Form::hidden('Link',request()->getSchemeAndHttpHost(), ['class'=> 'form-control','readonly','id'=>'Link1']) }}
    <div class="box box-warning">
        <div class="box-body"> 
                <div class="addform">
                    @include('errors.validation')                   
                    {!! Form::open(['id'=>'ADD_DETAIL']) !!}
                    <center><kbd>ADD FORM</kbd></center><br>
                        <div class="row">   

                                        {{ Form::hidden('no_transfer',$transferin->no_transfer, ['class'=> 'form-control','readonly','id'=>'transfer']) }}
                                    
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('No Trf In', 'No Trf In:') }}
                                        {{ Form::text('no_trf_in',$transferin->no_trf_in, ['class'=> 'form-control','readonly','id'=>'notrfin']) }}
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        {{ Form::label('kode_produk', 'Produk:') }}
                                        {{ Form::select('kode_produk',$Produk->sort(),null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','required'=>'required','onchange'=>'stock();','id'=>'kode_produks']) }}
                                    </div>
                                </div>                        

                                <div class="col-md-1">
                                    <div class="form-group">
                                        {{ Form::label('kode_satuan', 'Satuan:') }}
                                        {{ Form::text('kode_satuan',null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => '','readonly','id'=>'Satuan']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('partnumber', 'Part Number:') }}
                                        {{ Form::select('partnumber',[],null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'Part','autocomplete'=>'off','onchange'=>'getharga();']) }}
                                    </div>
                                </div>    

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('no_mesin', 'No Mesin:') }}
                                        {{ Form::text('no_mesin',null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'Mesin','autocomplete'=>'off','readonly']) }}
                                    </div>
                                </div>                       
                    
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('qty', 'QTY:') }}
                                        {{ Form::text('qty', null, ['class'=> 'form-control','required'=>'required',
                                         'id'=>'Qty','onkeyup'=>'check();','autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('hpp', 'HPP:') }}
                                        {{ Form::text('hpp',null, ['class'=> 'form-control','required'=>'required','readonly',
                                         'id'=>'Harga']) }}
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
                                        {{ Form::label('No Trf In', 'No Trf In:') }}
                                        {{ Form::text('no_trf_in',$transferin->no_trf_in, ['class'=> 'form-control','readonly','id'=>'Trfin2']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('kode_produk', 'Produk:') }}
                                        {{ Form::text('kode_produk', null, ['class'=> 'form-control','readonly','id'=>'Produk2']) }}
                                    </div>
                                </div>

                                <div class="col-md-1">
                                    <div class="form-group">
                                        {{ Form::label('kode_satuan', 'Satuan:') }}
                                        {{ Form::text('kode_satuan',null, ['class'=> 'form-control','id'=>'Satuan2','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('partnumber', 'Part Number:') }}
                                        {{ Form::text('partnumber',null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'Part2','autocomplete'=>'off','readonly']) }}
                                    </div>
                                </div>    

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('no_mesin', 'No Mesin:') }}
                                        {{ Form::text('no_mesin',null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'Mesin2','autocomplete'=>'off','readonly']) }}
                                    </div>
                                </div>  
                    
                                <div class="col-md-1">
                                    <div class="form-group">
                                        {{ Form::label('qty', 'QTY:') }}
                                        {{ Form::text('qty', null, ['class'=> 'form-control','id'=>'QTY2','required'=>'required','onkeyup'=>"cek_qty2()",'autocomplete'=>'off','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('hpp', 'Harga:') }}
                                        {{ Form::text('hpp',null, ['class'=> 'form-control','id'=>'Harga2','required'=>'required','readonly']) }}
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
            <table class="table table-bordered table-striped table-hover" id="data2-table" width="100%" style="font-size: 12px;">
                <thead>
                <tr class="bg-info">
                    <th>No Transfer In</th>
                    <th>Kode Produk</th>
                    <th>Part Number</th>
                    <th>No Mesin</th>
                    <th>Kode Satuan</th>
                    <th>Qty</th>
                    <th>HPP</th>
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

        function formatRupiah(angka, prefix='Rp'){
            var number_string = angka.toString(),
                sisa    = number_string.length % 3,
                rupiah  = number_string.substr(0, sisa),
                ribuan  = number_string.substr(sisa).match(/\d{3}/g);
                    
                if(number_string % 1 != 0){
                    var sisa    = number_string.length % 4,
                    rupiah  = number_string.substr(0, sisa),
                    ribuan  = number_string.substr(sisa).match(/\d{3}/g);
                }

                if (ribuan) {
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                // console.log(number_string);
                }

            return rupiah;
        }

        function rupiah(angka, prefix='Rp'){
            var value = angka.toLocaleString(
                undefined, // leave undefined to use the browser's locale,
                // or use a string like 'en-US' to override it.
                { minimumFractionDigits: 0 }
            );
            return value;
        }
        
        $(function(){
        var no_trf_in = $('#notrfin').val();
        var link = $('#Link1').val();
            $('#data2-table').DataTable({
                
            processing: true,
            serverSide: true,
            // ajax:'{!! route('pemakaiandetail.dataDetail') !!}',
            ajax:link+'/gui_inventory_laravel/admin/transferindetail/getDatabyID?id='+no_trf_in,
            data:{'no_trf_in':no_trf_in},
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
                    qty = api
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
                         qty
                    );
                    $( api.column( 7 ).footer() ).html(
                        'Rp. '+rupiah(grandTotal) 
                    );
                },
            columns: [
                { data: 'no_trf_in', name: 'no_trf_in' },
                { data: 'produk.nama_produk', name: 'produk.nama_produk' },
                { data: 'partnumber', name: 'partnumber' },
                { data: 'no_mesin', name: 'no_mesin' },
                { data: 'satuan.nama_satuan', name: 'satuan.nama_satuan', searchable : false },
                { data: 'qty', name: 'qty' },
                { data: 'hpp',
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

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function load(){
            $('.editform').hide();
            $('.addform').show();
        }

        function panggil(){
            load();
            startTime();
            $('.back2Top').show();
        }

        $(function() {
            $('#data-table').DataTable({
                scrollY: true,
                scrollX: true,
            });
        });

        function stock(){
            editpart();
            var trfin= $('#transfer').val();
            var kode_produk= $('#kode_produks').val();

            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('transferindetail.qtyproduk') !!}',
                type:'POST',
                data : {
                        'id': trfin,
                        'kode_produk': kode_produk,
                    },

                success: function(result) {
                        console.log(result);
                        if (result.tipe == "Serial"){
                            if(result.kategori == "BAN" || result.kategori == "UNIT"){
                                document.getElementById('Qty').required = true;
                                document.getElementById('Part').required = true;
                                $('#Qty').val(1);
                                $('#Harga').val('');
                                $('#Satuan').val(result.satuan);
                                $('#Part').val('');
                                $('#Mesin').val('');
                            }
                            else{
                                if (result.success === false){
                                    $('#Qty').val('');
                                    document.getElementById('Part').disabled = true;
                                    document.getElementById('Qty').disabled = true;
                                    alert("Jumlah Stok di lokasi asal tidak cukup !!!");
                                }else {
                                    document.getElementById('Part').disabled = false;
                                    document.getElementById('Qty').disabled = false;
                                    document.getElementById('Qty').required = true;
                                    document.getElementById('Part').required = true;
                                    $('#Qty').val(result.qty);
                                    $('#Harga').val('');
                                    $('#Satuan').val(result.satuan); 
                                    $('#Mesin').val(result.no_mesin);
                                }
                            }
                        }else {
                            if (result.success === false) {
                                $('#Qty').val('');
                                document.getElementById('Part').disabled = true;
                                document.getElementById('Qty').disabled = true;
                                alert("Jumlah Stok di lokasi asal tidak cukup !!!");
                            }else {
                                document.getElementById('Part').disabled = false;
                                document.getElementById('Qty').disabled = false;
                                document.getElementById('Qty').required = true;
                                document.getElementById('Part').required = true;
                                $('#Qty').val(result.qty);
                                $('#Harga').val(result.harga);
                                $('#Satuan').val(result.satuan); 
                                $('#Mesin').val(result.no_mesin);
                            }
                        }

                    },
            });
        }

        function editpart(){
                    $('#Part').prop("disabled", false);
                    var kode_produk = $("#kode_produks").val();
                    var no_transfer = $('#transfer').val();
                    console.log(kode_produk);
                    
                    var token = $("input[name='_token']").val();
                    $.ajax({
                    url: "{!! route('transferindetail.selectpart') !!}",
                    method: 'POST',
                    data: {kode_produk:kode_produk,no_transfer:no_transfer, _token:token},
                    success: function(data) {
                        $("#Part").html('');
                        // $("select[name='kode_satuan'").html(data.options);
                            $.each(data.options, function(key, value){
                                $('#Part').val('');  
                                $('#Part').append('<option value="'+ key +'">' + value + '</option>');
                                $('#Part').val('');  
                            });
                        }
                    });
        }

        function getharga(){
            var partnumber= $('#Part').val();
            var kode_produk= $('#kode_produks').val();
            var no_transfer= $('#transfer').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('transferindetail.getharga') !!}',
                type:'POST',
                data : {
                        'id': kode_produk,
                        'part': partnumber,
                        'no_transfer': no_transfer,
                    },
                success: function(result) {
                        console.log(result);
                        if (result.tipe == "Serial"){
                            if(result.kategori == "BAN" && result.kategori == "UNIT"){
                                $('#Harga').val(result.hpp);
                                $('#Stock').val(result.stok);
                                $('#Mesin').val(result.no_mesin);
                                if(result.stok < 1){
                                    submit.disabled = true;
                                }else{
                                    submit.disabled = false;
                                }
                            }else{
                                $('#Harga').val(result.hpp);
                                $('#Stock').val(result.stok);
                                $('#Mesin').val(result.no_mesin);
                                if(result.stok < 1){
                                    submit.disabled = true;
                                }else{
                                    submit.disabled = false;
                                }
                            }
                        }else{
                            $('#Stock').val(result.stok);
                            $('#Mesin').val(result.no_mesin);
                            if(result.stok < 1){
                                submit.disabled = true;
                            }else{
                                submit.disabled = false;
                            }
                        }
                    },
            });
        }

        function cek_qty2(){
            var no_trf_in= $('#Trfin2').val();
            var kode_produk= $('#Produk2').val();
            var qty = $('#QTY2').val();
            var harga = $('#Harga2').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('transferindetail.qtyproduk2') !!}',
                type:'POST',
                data : {
                        'no': no_trf_in,
                        'id': kode_produk,
                        'qty': qty
                    },
                success: function(result) {
                        console.log(result);
                        $('#Amount').val(qty*hpp);
                        if(result < qty2){
                            submit2.disabled = true;
                            swal("Gagal!", "Qty Trf In Tidak Boleh Melebihi Qty Transfer.");
                        }else{
                            submit2.disabled = false;
                        }
                    },
            });
        }

        $("select[name='kode_produk']").change(function(){
            var kode_produk = $(this).val();
            var token = $("input[name='_token']").val();
            $.ajax({
                url: "{!! route('transferindetail.selectAjax') !!}",
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
                    url:'{!! route('transferindetail.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#kode_produks').val('').trigger('change');
                        $('#Satuan').val('').trigger('change');
                        $('#Part').val('');                  
                        $('#Qty').val('').trigger('change');
                        $('#Harga').val('');
                        $('#Mesin').val('');
                        
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
                    url:'{!! route('transferindetail.updateajax') !!}',
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

        function check(){
            var no_trf= $('#transfer').val();
            var kode_produk= $('#kode_produks').val();
            var qty = $('#Qty').val();
            var harga = $('#Harga').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('transferindetail.qtycheck') !!}',
                type:'POST',
                data : {
                        'no': no_trf,
                        'id': kode_produk,
                        'qty': qty
                    },
                success: function(result) {
                        console.log(result);
                        if(result < qty){
                            submit.disabled = true;
                            document.getElementById("Part").disabled = true;
                            swal("Gagal!", "Qty Trf In Tidak Boleh Melebihi Qty Transfer.");
                        }else{
                            submit.disabled = false;
                            document.getElementById("Part").disabled = false;
                        }
                    },
            });
        }


        function edit(id, url) {
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

               $.ajax({
                    type: 'GET',
                    url: url,
                    data: {_token: CSRF_TOKEN},
                    dataType: 'JSON',
                    success: function (results) {
                        $('#ID').val(results.id);
                        $('#Trfin2').val(results.no_trf_in);
                        $('#Produk2').val(results.kode_produk);
                        $('#Satuan2').val(results.kode_satuan);
                        $('#Part2').val(results.partnumber);
                        $('#Mesin2').val(results.no_mesin);
                        $('#QTY2').val(results.qty);
                        $('#Harga2').val(results.hpp);
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