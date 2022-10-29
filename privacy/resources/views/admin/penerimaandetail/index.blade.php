@extends('adminlte::page')

@section('title', 'Penerimaan Detail')

@section('content_header')
    
@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <a href="{{ $list_url }}" class="btn btn-warning btn-xs"><i class="fa fa-arrow-left"></i> Kembali</a>
    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()"><i class="fa fa-refresh"></i> Refresh</button>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <button type="button" class="btn btn-success btn-xs" onclick="getharga()"><i class="fa fa-search"></i> Update Harga</button>

    <span class="pull-right">
        <font style="font-size: 16px;"> Detail Penerimaan <b>{{$penerimaan->no_penerimaan}}</b></font>
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
                            {{ Form::hidden('no_pembelian',$penerimaan->no_pembelian, ['class'=> 'form-control','readonly','id'=>'pembelian']) }}
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('No Penerimaan', 'No Penerimaan:') }}
                                        {{ Form::text('no_penerimaan',$penerimaan->no_penerimaan, ['class'=> 'form-control','readonly','id'=>'noterima']) }}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('kode_produk', 'Produk:') }}
                                        {{ Form::select('kode_produk',$Produk->sort(),null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','required'=>'required','onchange'=>'stock();','id'=>'kode_produk']) }}

                                        {{ Form::hidden('tipe_produk', null, ['class'=> 'form-control','readonly','id'=>'tipe_produk']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('kode_satuan', 'Satuan:') }}
                                        {{ Form::text('kode_satuan',null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => '','readonly','id'=>'Satuan']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('partnumber', 'Part Number:') }}
                                        <button type="button" class="btn btn-primary btn-xs" title="Generate Kode" onclick="isipart()" id='submit3'>Gen. Kode</button>
                                        {{ Form::text('partnumber',null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'Part','autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('no_mesin', 'No Mesin:') }}
                                        {{ Form::text('no_mesin',null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'Mesin','autocomplete'=>'off']) }}
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
                                        {{ Form::label('harga', 'Harga Satuan:') }}
                                        {{-- {{ Form::text('kode_satuan', null, ['class'=> 'form-control']) }} --}}
                                        {{ Form::text('harga',null, ['class'=> 'form-control','required'=>'required','readonly',
                                         'id'=>'Harga']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('landedcost', 'Landed Cost Satuan:') }}
                                        {{-- {{ Form::text('kode_satuan', null, ['class'=> 'form-control']) }} --}}
                                        {{ Form::text('landedcost',0, ['class'=> 'form-control','required','readonly',
                                         'id'=>'Landed','autocomplete'=>'off']) }}
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group8">
                                        {{ Form::label('tipepo', 'Jenis PO Ban:') }}
                                        {{Form::select('tipepo', ['1' => 'Beli Original', '2' => 'Ban Lama di Vulkanisir'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'tipepo','onchange'=>'pilih();'])}}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group4">
                                        {{ Form::label('tipeban', 'Tipe Ban:') }}
                                        {{Form::select('tipeban', ['1' => 'HALUS', '2' => 'KASAR'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'ban1'])}}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group5">
                                        {{ Form::label('kate', 'Kategori Ban:') }}
                                        {{ Form::text('katban', null, ['class'=> 'form-control',
                                         'id'=>'ban2','autocomplete'=>'off','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group7">
                                        {{ Form::label('partnumberlama', 'Serial Ban Lama:') }}
                                        <button type="button" class="btn btn-primary btn-xs" title="Check Serial Lama" onclick="checkpart()" id='submit4'>Check</button>
                                        {{ Form::text('katban', null, ['class'=> 'form-control',
                                         'id'=>'Parts3','autocomplete'=>'off','onkeypress'=>"return pulsar(event,this)"]) }}
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

                                {{ Form::hidden('no_pembelian',$penerimaan->no_pembelian, ['class'=> 'form-control','readonly','id'=>'pembelian1']) }}

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::hidden('id',null, ['class'=> 'form-control','readonly','id'=>'ID']) }}
                                        {{ Form::label('No Penerimaan', 'No Penerimaan:') }}
                                        {{ Form::text('no_penerimaan',$penerimaan->no_penerimaan, ['class'=> 'form-control','readonly','id'=>'Penerimaan']) }}
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
                                        {{ Form::select('kode_satuan',$Satuan,null, ['class'=> 'form-control','id'=>'Satuan2','disabled','readonly']) }}
                                    </div>
                                </div>
                    
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('qty', 'QTY:') }}
                                        {{ Form::text('qty', null, ['class'=> 'form-control','id'=>'QTY','required'=>'required','onkeyup'=>"cek_qty2()",'autocomplete'=>'off']) }}
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
                                        <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" title="Edit Harga" onclick="editharga2()" id='submit4'><i class="fa fa-edit"></i></button>
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
                        <th>No Penerimaan</th>
                        <th>Produk</th>
                        <th>Tipe</th>
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
                            <th>-</th>
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
            $('.editform').hide();
            $('.addform').show();
            $('.back2Top').show();
            $('.form-group4').hide();
            $('.form-group5').hide();
            $('.form-group7').hide();
            $('.form-group8').hide();
            document.getElementById('submit3').disabled = true;
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
            var no_penerimaan = $('#noterima').val();
            var link = $('#Link1').val();
            $('#data2-table').DataTable({
            processing: true,
            serverSide: true,
            ajax:link+'/gui_inventory_laravel/admin/penerimaandetail/getDatabyID?id='+no_penerimaan,
            data:{'no_penerimaan':no_penerimaan},
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
                        'Rp. '+formatRupiah(grandTotal) 
                    );
                },
            columns: [
                { data: 'no_penerimaan', name: 'no_penerimaan' },
                { data: 'produk.nama_produk', name: 'produk.nama_produk' },
                { data: 'produk.tipe_produk', name: 'produk.tipe_produk' },
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
        
        function editharga2(){
            var no_pembelian = $('#pembelian1').val();

            $.ajax({
                url:'{!! route('penerimaandetail.getlanded') !!}',
                type:'POST',
                data : {
                        'id': no_pembelian
                    },
                success: function(result) {
                        console.log(result);
                        if (result.success === true) {
                            $("#Landed2").prop('readonly', false);
                        } else {
                            swal("Gagal!", result.message, "error");
                        }
                        // window.location.reload();
                        refreshTable();
                    },
            });
        }

        function getharga(){
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false
            })
            var no_penerimaan = $('#noterima').val();

            $.ajax({
                url:'{!! route('penerimaandetail.getharga') !!}',
                type:'POST',
                data : {
                        'id': no_penerimaan
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
        
        function isipart(){
            var tipeban= $('#ban1').val();
            var kategoriban= $('#ban2').val();
            var kategoriban2= $('#kats').val();
            var produk= $('#kode_produk').val();
            var pembelian= $('#pembelian').val();

            if(tipeban == ''){
                swal("Gagal!", "Tipe dan kategori ban harus diisi.");
                exit();
            }

             $.ajax({
                url:'{!! route('penerimaandetail.isipart') !!}',
                type:'POST',
                data : {
                        'tipe': tipeban,
                        'kategori': kategoriban,
                        'kategori2': kategoriban2,
                        'kode_produk': produk,
                        'id': pembelian,
                    },
                success: function(result) {
                        console.log(result);
                        if (result.tipe == '' || result.kategori == ''){
                            swal("Gagal!", "Tipe dan kategori ban harus dipilih.");
                        }else {
                            $('#Part').val(result.hasil);
                            submit.disabled = false;
                        }
                    },
            });
        }

        function checkpart(){
            var partnumberlama= $('#Parts3').val();
            var produk= $('#kode_produk').val();
            
            if (partnumberlama == '' || produk == ''){
                swal("Gagal!", "Serial Ban Lama Harus Diisi.");
            }else {
                $.ajax({
                    url:'{!! route('penerimaandetail.checkpart') !!}',
                    type:'POST',
                    data : {
                            'part': partnumberlama,
                            'kode_produk': produk,
                        },
                    success: function(result) {
                            console.log(result);
                            if (result.success == false) {
                                swal("Gagal!", result.message, "error");
                                submit.disabled = true;
                            }else{
                                swal("Gagal!", "Nomor Serial Lama Terdaftar", "success");
                                submit.disabled = false;
                            }
                        },
                });
            }
        }

        function stock(){
            var pembelian= $('#pembelian').val();
            var kode_produk= $('#kode_produk').val();

            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('penerimaandetail.qtyproduk') !!}',
                type:'POST',
                data : {
                        'id': pembelian,
                        'kode_produk': kode_produk,
                    },

                success: function(result) {
                        console.log(result);
                    if (result.company == "04") {
                        if (result.tipe == "Serial"){
                            document.getElementById('Qty').readOnly = true;
                            document.getElementById('Mesin').readOnly = false;
                            document.getElementById('Part').readOnly = false;
                            $('#Qty').val(1);
                            $('#Harga').val(result.harga);
                            $('#Satuan').val(result.satuan);
                            if(result.landedcost != 0){
                                $('#Landed').val(result.landedcost);
                            }
                            else{
                                document.getElementById('Landed').readOnly = false;
                            }
                            $('#Mesin').val('');
                            $('#Part').val('');
                            $('.form-group4').hide();
                            $('.form-group5').hide();
                            $('.form-group7').hide();
                            $('.form-group8').hide();
                            submit.disabled = false;
                            document.getElementById('ban1').required = false;
                            document.getElementById('ban2').required = false;
                            document.getElementById('Part').required = true;
                            document.getElementById('Mesin').required = true;
                            document.getElementById('submit3').disabled = true;
                            document.getElementById('Parts3').required = false;
                        }else if (result.tipe == "Serial" && result.kategori == "BAN" && result.cek_nama == null){
                            document.getElementById('Qty').readOnly = true;
                            document.getElementById('Mesin').readOnly = true;
                            document.getElementById('Part').readOnly = true;
                            $('#Qty').val(1);
                            $('#Harga').val(result.harga);
                            $('#Satuan').val(result.satuan);
                            if(result.landedcost != 0){
                                $('#Landed').val(result.landedcost);
                            }
                            else{
                                document.getElementById('Landed').readOnly = false;
                            }
                            $('#Part').val('');
                            $('#ban2').val('ORIGINAL');
                            $('#Mesin').val('-');
                            $('.form-group4').hide();
                            $('.form-group5').hide();
                            $('.form-group7').hide();
                            $('.form-group8').show();
                            submit.disabled = true;
                            document.getElementById('Parts3').required = true;
                        }else if (result.tipe == "Serial" && result.kategori == "BAN" && result.cek_nama != null){
                            document.getElementById('Qty').readOnly = true;
                            document.getElementById('Mesin').readOnly = true;
                            document.getElementById('Part').readOnly = true;
                            $('#Qty').val(1);
                            $('#Harga').val(result.harga);
                            $('#Satuan').val(result.satuan);
                            if(result.landedcost != 0){
                                $('#Landed').val(result.landedcost);
                            }
                            else{
                                document.getElementById('Landed').readOnly = false;
                            }
                            $('#Part').val('');
                            $('#ban2').val('VULKANISIR');
                            $('#Parts3').val('');
                            $('#Mesin').val('-');
                            $('.form-group4').show();
                            $('.form-group5').show();
                            $('.form-group7').hide();
                            $('.form-group8').show();
                            submit.disabled = true;
                            document.getElementById('submit3').disabled = false;
                            document.getElementById('Parts3').required = false;
                        }else {
                            document.getElementById('Qty').readOnly = false;
                            document.getElementById('Mesin').readOnly = true;
                            document.getElementById('Part').readOnly = true;
                            $('#Qty').val(result.qty);
                            $('#Harga').val(result.harga);
                            $('#Satuan').val(result.satuan);
                            if(result.landedcost != 0){
                                $('#Landed').val(result.landedcost);
                            }
                            else{
                                document.getElementById('Landed').readOnly = false;
                            }
                            $('#Part').val(result.partnumber);  
                            $('#Mesin').val('-');  
                            $('.form-group4').hide();
                            $('.form-group5').hide();
                            $('.form-group7').hide();
                            $('.form-group8').hide();
                            submit.disabled = false;
                            document.getElementById('ban1').required = false;
                            document.getElementById('ban2').required = false;
                            document.getElementById('submit3').disabled = true;
                            document.getElementById('Parts3').required = false;
                        }
                    }else {
                        if (result.tipe == "Serial" && result.kategori == "UNIT"){
                            document.getElementById('Qty').readOnly = true;
                            document.getElementById('Mesin').readOnly = false;
                            document.getElementById('Part').readOnly = false;
                            $('#Qty').val(1);
                            $('#Harga').val(result.harga);
                            $('#Satuan').val(result.satuan);
                            if(result.landedcost != 0){
                                $('#Landed').val(result.landedcost);
                            }
                            else{
                                document.getElementById('Landed').readOnly = false;
                            }
                            $('#Mesin').val('');
                            $('#Part').val('');
                            $('.form-group4').hide();
                            $('.form-group5').hide();
                            $('.form-group7').hide();
                            $('.form-group8').hide();
                            submit.disabled = false;
                            document.getElementById('ban1').required = false;
                            document.getElementById('ban2').required = false;
                            document.getElementById('Part').required = true;
                            document.getElementById('Mesin').required = true;
                            document.getElementById('submit3').disabled = true;
                            document.getElementById('Parts3').required = false;
                        }else if (result.tipe == "Serial" && result.kategori == "BAN" && result.cek_nama == null){
                            document.getElementById('Qty').readOnly = true;
                            document.getElementById('Mesin').readOnly = true;
                            document.getElementById('Part').readOnly = true;
                            $('#Qty').val(1);
                            $('#Harga').val(result.harga);
                            $('#Satuan').val(result.satuan);
                            if(result.landedcost != 0){
                                $('#Landed').val(result.landedcost);
                            }
                            else{
                                document.getElementById('Landed').readOnly = false;
                            }
                            $('#Part').val('');
                            $('#ban2').val('ORIGINAL');
                            $('#Mesin').val('-');
                            $('.form-group4').hide();
                            $('.form-group5').hide();
                            $('.form-group7').hide();
                            $('.form-group8').show();
                            submit.disabled = true;
                            document.getElementById('Parts3').required = true;
                        }else if (result.tipe == "Serial" && result.kategori == "BAN" && result.cek_nama != null){
                            document.getElementById('Qty').readOnly = true;
                            document.getElementById('Mesin').readOnly = true;
                            document.getElementById('Part').readOnly = true;
                            $('#Qty').val(1);
                            $('#Harga').val(result.harga);
                            $('#Satuan').val(result.satuan);
                            if(result.landedcost != 0){
                                $('#Landed').val(result.landedcost);
                            }
                            else{
                                document.getElementById('Landed').readOnly = false;
                            }
                            $('#Part').val('');
                            $('#ban2').val('VULKANISIR');
                            $('#Parts3').val('');
                            $('#Mesin').val('-');
                            $('.form-group4').show();
                            $('.form-group5').show();
                            $('.form-group7').hide();
                            $('.form-group8').show();
                            submit.disabled = true;
                            document.getElementById('submit3').disabled = false;
                            document.getElementById('Parts3').required = false;
                        }else {
                            document.getElementById('Qty').readOnly = false;
                            document.getElementById('Mesin').readOnly = true;
                            document.getElementById('Part').readOnly = true;
                            $('#Qty').val(result.qty);
                            $('#Harga').val(result.harga);
                            $('#Satuan').val(result.satuan);
                            if(result.landedcost != 0){
                                $('#Landed').val(result.landedcost);
                            }
                            else{
                                document.getElementById('Landed').readOnly = false;
                            }
                            $('#Part').val(result.partnumber);  
                            $('#Mesin').val('-');  
                            $('.form-group4').hide();
                            $('.form-group5').hide();
                            $('.form-group7').hide();
                            $('.form-group8').hide();
                            submit.disabled = false;
                            document.getElementById('ban1').required = false;
                            document.getElementById('ban2').required = false;
                            document.getElementById('submit3').disabled = true;
                            document.getElementById('Parts3').required = false;
                        }
                    }
                },
            });
        }
        
        function pilih(){
            var tipepo= $('#tipepo').val();
            console.log(tipepo);

            if (tipepo == "1"){
                $('.form-group4').show();
                $('.form-group5').show();
                $('.form-group7').hide();
                $('#ban2').val('ORIGINAL');
                document.getElementById('Part').required = true;
                document.getElementById('ban1').required = true;
                document.getElementById('ban2').required = true;
                document.getElementById('Parts3').required = false;
                document.getElementById('submit3').disabled = false;
            }
            else if (tipepo == "2"){
                $('.form-group4').show();
                $('.form-group5').show();
                $('.form-group7').show();
                $('#ban2').val('VULKANISIR');
                document.getElementById('Part').required = true;
                document.getElementById('ban1').required = true;
                document.getElementById('ban2').required = false;
                document.getElementById('Parts3').required = true;
                document.getElementById('submit3').disabled = false;
            }
        }

        function cek_qty2(){
            var pembelian= $('#pembelian1').val();
            var kode_produk= $('#Produk').val();
            var qty2 = $('#QTY').val();

             $.ajax({
                url:'{!! route('penerimaandetail.qtyproduk2') !!}',
                type:'POST',
                data : {
                        'id': pembelian,
                        'kode_produk': kode_produk,
                    },

                success: function(result) {
                        console.log(result);
                        var qty2 = $('#QTY').val();

                        if(result.qty < qty2){
                            submit2.disabled = true;
                            swal("Gagal!", "Qty Penerimaan Tidak Boleh Melebihi Qty Pembelian");
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
                url: "{!! route('penerimaandetail.selectAjax') !!}",
                method: 'POST',
                data: {kode_produk:kode_produk, _token:token},
                success: function(data) {
                    $("#Satuan").html('');
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
                    url:'{!! route('penerimaandetail.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#kode_produk').val('').trigger('change');
                        $('#Satuan').val('').trigger('change');
                        $('#Part').val('');
                        $('#Parts3').val('');
                        $('#Mesin').val('');
                        $('#Qty').val('').trigger('change');
                        $('#Harga').val('');
                        $('#Landed').val(0);   
                        $('#ban1').val('').trigger('change');
                        $('#tipepo').val('').trigger('change');  
                        $('.form-group4').hide();
                        $('.form-group5').hide();
                        $('.form-group7').hide();   
                        $('.form-group8').hide(); 
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
                    url:'{!! route('penerimaandetail.updateajax') !!}',
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
            var no_pembelian= $('#pembelian').val();
            var kode_produk= $('#kode_produk').val();
            var satuan = $('#Satuan').val();
            var qty = $('#Qty').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('penerimaandetail.qtycheck') !!}',
                type:'POST',
                data : {
                        'no': no_pembelian,
                        'id': kode_produk,
                        'satuan': satuan,
                        'qty': qty
                    },
                success: function(result) {
                        console.log(result);
                        if(result < qty){
                            submit.disabled = true;
                            swal("Gagal!", "Qty Penerimaan Tidak Boleh Melebihi Qty Pembelian");
                        }else{
                            submit.disabled = false;
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
                        $('#Penerimaan').val(results.no_penerimaan);
                        $('#Produk').val(results.kode_produk);
                        $('#Namaproduk_e').val(results.nama_produk);
                        $('#Satuan2').val(results.kode_satuan);
                        $('#QTY').val(results.qty);
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