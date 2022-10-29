@extends('adminlte::page')

@section('title', 'Pembelian Detail')

@section('content_header')
   
@stop

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <a href="{{ $list_url }}" class="btn btn-danger btn-xs"><i class="fa fa-arrow-left"></i> Kembali</a>
    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()"><i class="fa fa-refresh"></i> Refresh</button>
    <span class="pull-right">
        <font style="font-size: 16px;"> Detail Pembelian <b>{{$pembelian->no_pembelian}}</b></font>
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
                                        {{ Form::label('No pembelian', 'No Pembelian:') }}
                                        {{ Form::text('no_pembelian',$pembelian->no_pembelian, ['class'=> 'form-control','readonly','id'=>'nobeli']) }}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('No pembelian', 'Jenis:') }}
                                        {{ Form::text('jenis_po',$pembelian->jenis_po, ['class'=> 'form-control','readonly','id'=>'jenispo']) }}
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <!--<button type="button" class="btn btn-primary btn-xs" data-toggle="modal" title="Add New Produk" data-target="#ADD_FORMPRODUK" id="button_add2"><i class="fa fa-plus"></i></button> -->
                                        {{ Form::label('kode_produk', 'Produk:') }}
                                        {{ Form::select('kode_produk',$Produk->sort(),null,
                                         ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','required'=>'required','onchange'=>'stock();',
                                         'id'=>'kode_produk']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                        <div class="form-group">
                                            {{ Form::label('qty', 'Stock:') }}
                                            {{ Form::text('qty_stock', null, ['class'=> 'form-control','readonly','id'=>'Stock']) }}
                                        </div>
                                    </div>

                                <div class="col-md-2">
                                    <div class="form-group3">
                                        <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" title="Add Satuan Konversi" data-target="#ADD_SATUANKONVERSI", id="button_add"><i class="fa fa-plus"></i></button>
                                        {{ Form::label('kode_satuan', 'Satuan:') }}
                                        {{ Form::select('kode_satuan',[],null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => '','required'=>'required','id'=>'Satuan','onchange'=>'satuan_non();']) }}
                                    </div>
                                </div> 
                                
                        
                                <div class="col-md-1">
                                    <div class="form-group">
                                        {{ Form::label('qty', 'QTY:') }}
                                        {{ Form::text('qty', null, ['class'=> 'form-control','required','id'=>'QTY','autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('harga', 'Harga Satuan:') }}
                                        <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" title="Edit Harga" onclick="editharga()" id='submit3'><i class="fa fa-edit"></i></button>
                                        {{-- {{ Form::text('kode_satuan', null, ['class'=> 'form-control']) }} --}}
                                        {{ Form::text('harga',null, ['class'=> 'form-control','required','id'=>'HPP','autocomplete'=>'off','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group2">
                                        {{ Form::label('satuan_nonproduk', 'Satuan Jasa / NonStock:') }}
                                        {{ Form::text('kode_satuan', null, ['class'=> 'form-control','required','id'=>'satuannon','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group4">
                                        {{ Form::label('keterangan', 'Keterangan:') }}
                                        {{ Form::textarea('keterangan', null, ['class'=> 'form-control','rows'=>'2','id'=>'Keterangan', 'placeholder'=>'Keterangan', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                     </div>
                                </div>
                                <div class="fade-in-fade-out">
                                    <div class="col-md-4">
                                        <div class="form-group-warning" style="border-style: inset; background-color: #949494;">
                                            {{ Form::label('warning', 'PRODUK INI SUDAH MELEWATI BATAS MAKSIMUM QTY', ['style'=>'color:red']) }}
                                            {{ Form::label('text', 'Max Qty = ', ['style'=>'color:red'])}}
                                            {{ Form::label('max_qty', null, ['style'=>'color:red','id'=>'Warning']) }}
                                        </div>
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
                                        {{ Form::label('No  Pembelian', 'No Pembelian:') }}
                                        {{ Form::text('no_pembelian',$pembelian->no_pembelian, ['class'=> 'form-control','readonly','id'=>'Penerimaan']) }}
                                    </div>
                                </div>
                                
                                {{ Form::hidden('kode_produk',null, ['class'=> 'form-control','readonly','id'=>'Produk']) }}

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('nama_produk', 'Nama Produk:') }}
                                        {{ Form::text('nama_produk',null, ['class'=> 'form-control','id'=>'Namaproduk_e','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('kode_satuan', 'Satuan:') }}
                                        <?php if($pembelian->jenis_po == 'Non-Stock'){ ?>
                                            {{ Form::text('kode_satuan',null, ['class'=> 'form-control','id'=>'Satuan2', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                        <?php }else{ ?>
                                            {{ Form::text('kode_satuan',null, ['class'=> 'form-control','id'=>'Satuan2','readonly']) }}
                                        <?php } ?>
                                    </div>
                                </div>
                    
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('qty', 'QTY:') }}
                                        {{ Form::text('qty', null, ['class'=> 'form-control','id'=>'QTY2','required'=>'required','autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('harga', 'Harga Satuan:') }}
                                        <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" title="Edit Harga" onclick="editharga2()" id='submit4'><i class="fa fa-edit"></i></button>
                                        {{-- {{ Form::text('kode_satuan', null, ['class'=> 'form-control']) }} --}}
                                        {{ Form::text('harga',null, ['class'=> 'form-control','id'=>'Harga','required','readonly','autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group4">
                                        {{ Form::label('keterangan', 'Keterangan:') }}
                                        {{ Form::textarea('keterangan', null, ['class'=> 'form-control','rows'=>'2','id'=>'Keterangan2', 'placeholder'=>'Keterangan', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                     </div>
                                </div>
                                <div class="fade-in-fade-out">
                                    <div class="col-md-4">
                                        <div class="form-group-warning" style="border-style: inset; background-color: #949494;">
                                            {{ Form::label('warning', 'PRODUK INI SUDAH MELEWATI BATAS MAKSIMUM QTY', ['style'=>'color:red']) }}
                                            {{ Form::label('text', 'Max Qty = ', ['style'=>'color:red'])}}
                                            {{ Form::label('max_qty', null, ['style'=>'color:red','id'=>'Warning']) }}
                                        </div>
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


        <div class="modal fade" id="ADD_FORMPRODUK" role="dialog">
            <div class="modal-dialog " role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title">Create Data</h4>
                </div>
                @include('errors.validation')
                {!! Form::open(['id'=>'ADD_PRODUK']) !!}
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{ Form::label('Nama Produk', 'Nama Produk:') }}
                                        {{ Form::text('nama_produk', null, ['class'=> 'form-control','id'=>'Nama1','required'=>'required', 'placeholder'=>'Nama Produk','onkeypress'=>"return pulsar(event,this)",'autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('kode_kategori', 'Kategori:') }}
                                        {{ Form::select('kode_kategori',$Kategori,null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Kategori1','required'=>'required']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('kode_merek', 'Merek:') }}
                                        {{ Form::select('kode_merek', $Merek,null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Merek1','required'=>'required']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('kode_satuan', 'Satuan:') }}
                                        {{ Form::select('kode_satuan', $Satuan,null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Satuan1','required'=>'required']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('partnumber', 'Part Number:') }}
                                        {{ Form::text('partnumber', null, ['class'=> 'form-control','id'=>'Part1', 'placeholder'=>'Part Number','required'=>'required','autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('harga_beli', 'Harga Beli:') }}
                                        {{ Form::text('harga_beli', 0, ['class'=> 'form-control','id'=>'Beli1', 'placeholder'=>'Harga Beli','required'=>'required','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('harga_jual', 'Harga Jual:') }}
                                        {{ Form::text('harga_jual', 0, ['class'=> 'form-control','id'=>'Jual1', 'placeholder'=>'Harga Jual','required'=>'required','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('hpp', 'HPP:') }}
                                        {{ Form::text('hpp', 0, ['class'=> 'form-control','id'=>'HPP1', 'placeholder'=>'HPP','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('stok', 'Stok:') }}
                                        {{ Form::text('stok', 0, ['class'=> 'form-control','id'=>'Stok1', 'placeholder'=>'Stock','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('aktif', 'Status:') }}
                                        {{Form::select('stat', ['Aktif' => 'Aktif', 'NonAktif' => 'NonAktif'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'stat1','required'=>'required'])}}
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
                </div>
            </div>
        </div>

        <div class="modal fade" id="ADD_SATUANKONVERSI" role="dialog">
            <div class="modal-dialog " role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title">Create Data</h4>
                </div>
                @include('errors.validation')
                {!! Form::open(['id'=>'ADD_KONVERSI']) !!}
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        {{ Form::label('Kode Produk', 'Produk:') }}
                                        {{ Form::select('kode_produk',$Produk,null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','onchange'=>'satuan();','id'=>'kode_produk1','required'=>'required']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('kode_satuan', 'Satuan Terbesar:') }}
                                        {{ Form::select('kode_satuan', $Satuan,null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder'=>'','onchange'=>'satuan2();','id'=>'Kode_Terbesar1','required'=>'required']) }}
                                    </div>
                                </div>

                                        {{ Form::hidden('satuan_terbesar',null, ['class'=> 'form-control','readonly','id'=>'Terbesar1']) }}

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('nilai_konversi', 'Nilai Konversi:') }}
                                        {{ Form::text('nilai_konversi', null, ['class'=> 'form-control','id'=>'Nilai1', 'placeholder'=>'Nilai Konversi','onkeyup'=>'satuan2();', 'autocomplete'=>'off','required'=>'required','onkeypress'=>"return hanyaAngka(event,this)"]) }}
                                    </div>
                                </div>

                                        {{ Form::hidden('kode_satuanterkecil', null, ['class'=> 'form-control','readonly','id'=>'Kode_Terkecil1']) }}

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('satuan_terkecil', 'Satuan Terkecil:') }}
                                        {{ Form::text('satuan_terkecil', null, ['class'=> 'form-control','readonly','id'=>'Terkecil1', 'placeholder'=>'Satuan Terkecil']) }}
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
                </div>
            </div>
        </div>


   <div class="box box-danger">
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data2-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-info">
                        <th>No Pembelian</th>
                        <th>Produk</th>
                        <?php if($pembelian->jenis_po == 'Non-Stock'){ ?>
                            <th>Keterangan</th>
                        <?php } ?>
                        <th>Satuan</th>
                        <th>Qty</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                     </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-info">
                        <?php if($pembelian->jenis_po == 'Non-Stock'){ ?>
                            <th class="text-center" colspan="4">Total</th>
                        <?php }else{ ?>
                            <th class="text-center" colspan="3">Total</th>
                        <?php } ?>
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
        var no_pembelian = $('#nobeli').val();
        var jenispo = $('#jenispo').val();  
        var link = $('#Link1').val();
        if(jenispo == 'Stock'){
            $('.form-group4').hide();
            $('.form-group2').hide();
            $('#data2-table').DataTable({
                
            processing: true,
            serverSide: true,
            ajax:link+'/gui_inventory_laravel/admin/pembeliandetail/getDatabyID?id='+no_pembelian,
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
                        .column( 3 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
        
                    // Total over this page
                    grandTotal = api
                        .column( 5 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                        
                    // Update footer
                    $( api.column( 3 ).footer() ).html(
                         total
                    );
                    $( api.column( 5 ).footer() ).html(
                        'Rp. '+formatRupiah(grandTotal)
                    );
                },

            columns: [
                { data: 'no_pembelian', name: 'no_pembelian' },
                { data: 'produk.nama_produk', name: 'produk.nama_produk' },
                { data: 'satuan.nama_satuan', name: 'satuan.nama_satuan', searchable: false },
                { data: 'qty', name: 'qty' },
                { data: 'harga', 
                    render: function( data, type, full ) {
                    return formatNumber(data); }
                },
                { data: 'total_transaksi', 
                    render: function( data, type, full ) {
                    return formatNumber(data); }
                },
                { data: 'action', name: 'action' },
            ]
            
            });
        }
        else if(jenispo == 'Jasa'){
            $('.form-group2').show();
            document.getElementById('Satuan').required = false;
            $('.form-group4').hide();
            $('.form-group3').hide();
            $('#data2-table').DataTable({
                
            processing: true,
            serverSide: true,
            // ajax:'{!! route('pemakaiandetail.dataDetail') !!}',
            ajax:link+'/gui_inventory_laravel/admin/pembeliandetail/getDatabyID?id='+no_pembelian,
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
                        .column( 3 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
        
                    // Total over this page
                    grandTotal = api
                        .column( 5 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                        
                    // Update footer
                    $( api.column( 3 ).footer() ).html(
                         total
                    );
                    $( api.column( 5 ).footer() ).html(
                        'Rp. '+formatRupiah(grandTotal)
                    );
                },

            columns: [
                { data: 'no_pembelian', name: 'no_pembelian' },
                { data: 'jasa.nama_item', name: 'jasa.nama_item' },
                { data: 'kode_satuan', name: 'kode_satuan' },
                { data: 'qty', name: 'qty' },
                { data: 'harga', 
                    render: function( data, type, full ) {
                    return formatNumber(data); }
                },
                { data: 'total_transaksi', 
                    render: function( data, type, full ) {
                    return formatNumber(data); }
                },
                { data: 'action', name: 'action' },
            ]
            
            });
        }
        else{
            $('.form-group3').hide();
            document.getElementById('Satuan').required = false;

            $('.form-group4').show();
            $('.form-group2').show();
            $('#data2-table').DataTable({
                
            processing: true,
            serverSide: true,
            // ajax:'{!! route('pemakaiandetail.dataDetail') !!}',
            ajax:link+'/gui_inventory_laravel/admin/pembeliandetail/getDatabyID?id='+no_pembelian,
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
        
                    // Total over this page
                    grandTotal = api
                        .column( 6 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                        
                    // Update footer
                    $( api.column( 4 ).footer() ).html(
                         total
                    );
                    $( api.column( 6 ).footer() ).html(
                        'Rp. '+formatRupiah(grandTotal)
                    );
                },

            columns: [
                { data: 'no_pembelian', name: 'no_pembelian' },
                { data: 'nonstock.nama_item', name: 'nonstock.nama_item' },
                { data: 'keterangan', name: 'keterangan' },
                { data: 'kode_satuan', name: 'kode_satuan' },
                { data: 'qty', name: 'qty' },
                { data: 'harga', 
                    render: function( data, type, full ) {
                    return formatNumber(data); }
                },
                { data: 'total_transaksi', 
                    render: function( data, type, full ) {
                    return formatNumber(data); }
                },
                { data: 'action', name: 'action' },
            ]
            
            });
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
            var kode_produk= $('#kode_produk').val();
            var no_pembelian= $('#nobeli').val();
            $.ajax({
                url:'{!! route('pembeliandetail.stockproduk') !!}',
                type:'POST',
                data : {
                        'id': kode_produk,
                        'no': no_pembelian
                    },
                success: function(result) {
                        console.log(result);
                        $('#Stock').val(result.stock);
                        $('#HPP').val(result.harga_beli);
                        if(result.max_qty == 0)
                        {
                            $('.form-group-warning').hide();
                        }
                        else if (result.stock > result.max_qty)
                        {
                            $('.form-group-warning').show();
                            document.getElementById('Warning').innerHTML = result.max_qty;
                        }
                        else if(result.stock < result.max_qty)
                        {
                            $('.form-group-warning').hide();
                        }
                    },
            });
        }

        function satuan_non(){
            var kode_satuan = $('#Satuan').val();
            console.log(kode_satuan);
            $('#satuannon').val(kode_satuan);
        }

        function editharga(){
            swal("Perhatian!", "Jika Anda ingin merubah harga beli, pastikan harga baru yang Anda input adalah benar.");
            $("#HPP").prop('readonly', false);
        }

        function editharga2(){
            swal("Perhatian!", "Jika Anda ingin merubah harga beli, pastikan harga baru yang Anda input adalah benar.");
            $("#Harga").prop('readonly', false);
        }

        $("select[name='kode_produk']").change(function(){
            var kode_produk = $(this).val();
            var no_pembelian= $('#nobeli').val();
            var token = $("input[name='_token']").val();
            $.ajax({
                url: "{!! route('pembeliandetail.selectAjax') !!}",
                method: 'POST',
                data: {
                        kode_produk:kode_produk, _token:token,
                        'no': no_pembelian
                    },
                success: function(data) {
                    $("#Satuan").html('');
                    // $("select[name='kode_satuan'").html(data.options);
                    $.each(data.options, function(key, value){

                        $('#Satuan').val('');
                        $("#Satuan").append('<option value="'+ key +'">' + value + '</option>');
                        $('#Satuan').val('');
                        
                    });
                }
            });
        });
        
        $("select[id='Produk']").change(function(){
            var kode_produk = $(this).val();
            console.log(kode_produk);
            var no_pembelian= $('#nobeli').val();
            var token = $("input[name='_token']").val();
            $.ajax({
                url: "{!! route('pembeliandetail.selectAjax2') !!}",
                method: 'POST',
                data: {
                        kode_produk:kode_produk, _token:token,
                        'no': no_pembelian
                    },
                success: function(data) {
                    $("#Satuan2").html('');
                    // $("select[name='kode_satuan'").html(data.options);
                    $.each(data.options, function(key, value){

                        $('#Satuan2').val('');
                        $("#Satuan2").append('<option value="'+ key +'">' + value + '</option>');
                        $('#Satuan2').val('');
                        
                    });
                }
            });
        });

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
                    url:'{!! route('pembeliandetail.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#kode_produk').val('').trigger('change');
                        $('#Satuan').val('').trigger('change');
                        $('#Stock').val('');
                        $('#QTY').val('');
                        $('#HPP').val('');
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
                    url:'{!! route('pembeliandetail.updateajax') !!}',
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
                        console.log(results);
                        $(".addform").hide();
                        $(".editform").show();
                        $('#ID').val(results.id);
                        $('#Pembelian').val(results.no_pembelian);
                        $('#Produk').val(results.kode_produk);
                        $('#Namaproduk_e').val(results.nama_produk);
                        $('#Satuan2').val(results.kode_satuan).trigger('change');
                        $('#QTY2').val(results.qty);
                        $('#Harga').val(results.harga);
                        $('#Keterangan2').val(results.keterangan);
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