@extends('adminlte::page')

@section('title', 'Produk')

@section('content_header')

@stop
    
@section('content')
    <link rel="stylesheet" href="{{ URL::asset('css/table.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <link rel="icon" type="image/png" href="/gui_inventory_gut_laravel/css/logo_gui.png" sizes="16x16">
    <link rel="icon" type="image/png" href="/gui_inventory_gut_laravel/css/logo_gui.png" sizes="32x32">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>

    <style>
    td.details-control {
    background: url('../css/detail_open2.png') no-repeat center center;
    cursor: pointer;
    }
    tr.shown td.details-control {
    background: url('../css/detail_minus2.png') no-repeat center center;
    }       
    </style>
@include('sweet::alert')
<body onLoad="load()">
    <div class="box box-solid">
        <div class="box-body">
            <div class="box">
                <div class="box-body">
                    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()">
                        <i class="fa fa-refresh"></i> Refresh</button>

                    @permission('create-produkor')
                    <button type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#addform">
                        <i class="fa fa-plus"></i> New Produk</button>
                    @endpermission

                <?php if ($level != 'sany'){ ?>
                    <span class="pull-right">
                    <a href="http://localhost/gui_inventory_laravel/admin/produk/exportexcel?" target="_blank" id="printpembelian"><button type="button" class="btn bg-black btn-xs"><i class="fa fa-print"></i> CETAK LIST PRODUK</button></a>
                    <button type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#button3"><i class="fa fa-print"></i> DETAIL HISTORY</button>
                    <button type="button" class="btn btn-success btn-xs" id="button7"><i class="fa fa-paperclip"></i> VIEW STOCK</button>
                    <button type="button" class="btn btn-warning btn-xs" id="button6"><i class="fa fa-paperclip"></i> VIEW HISTORY</button>
                    <button type="button" class="btn btn-primary btn-xs" id="button5"><i class="fa fa-paperclip"></i> VIEW MONTHLY</button>
                    </span>
                <?php } ?>
            </div>
        </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" style="font-size: 12px;">
                    <thead>
                        <tr class="bg-blue">
                        <?php if ($level == 'sany'){ ?>
                            <th>Kode Produk</th>
                            <th>Nama Produk</th>
                            <th>Part Number</th>
                            <th>Merek</th>
                            <th>Stock</th>
                        <?php }else { ?>
                            <th>Kode Produk</th>
                            <th>Nama Produk</th>
                            <th>Tipe Produk</th>
                            <th>Part Number</th>
                            <th>Kategori</th>
                            <th>Unit</th>
                            <th>Merek</th>
                            <th>Ukuran</th>
                            <th>Satuan</th>
                            <th>Min Stock</th>
                            <th>Max Stock</th>
                            <th>Status</th>
                        <?php } ?>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
            <div class="col-md-12">
            </div>
            <div class="col-sm-3">
                {{ Form::label('texx', 'Created By:') }}
                {{ Form::text('created_by', null, ['class'=> 'form-control','id'=>'CreateBy1','readonly']) }}
            </div>
            <div class="col-md-3">
                {{ Form::label('texx', 'Created At:') }}
                {{ Form::text('created_at', null, ['class'=> 'form-control','id'=>'CreateAt1','readonly']) }}
            </div>
            <div class="col-md-3">
                {{ Form::label('texx', 'Updated By:') }}
                {{ Form::text('updated_by', null, ['class'=> 'form-control','id'=>'UpdateBy1','readonly']) }}
            </div>
            <div class="col-md-3">
                {{ Form::label('texx', 'Updated At:') }}
                {{ Form::text('updated_at', null, ['class'=> 'form-control','id'=>'UpdateAt1','readonly']) }}
            </div>
        </div>
    </div>

<div class="modal fade" id="addform" role="dialog">
        <div class="modal-dialog " role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Create Data</h4>
            </div>
            @include('errors.validation')
            {!! Form::open(['id'=>'ADD']) !!}
                    <div class="modal-body">
                        <div class="row">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        {{ Form::label('Nama Produk', 'Nama Produk:') }}
                                        {{ Form::text('nama_produk', null, ['class'=> 'form-control','id'=>'Nama1','required'=>'required', 'placeholder'=>'Nama Produk', 'onkeypress'=>"return pulsar(event,this)",'autocomplete'=>'off']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('tipe_produk', 'Tipe Produk:') }}
                                        {{Form::select('tipe_produk', ['Serial' => 'Serial', 'NonSerial' => 'NonSerial'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'tipe1','required'=>'required'])}}
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
                                        {{ Form::label('kode_unit', 'Unit:') }}
                                        {{ Form::select('kode_unit',$Unit,null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Unit1']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('kode_merek', 'Merek:') }}
                                        {{ Form::select('kode_merek', $Merek,null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Merek1']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('kode_ukuran', 'Ukuran:') }}
                                        {{ Form::select('kode_ukuran', $Ukuran,null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Ukuran1','required'=>'required']) }}
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
                                        {{ Form::text('partnumber', null, ['class'=> 'form-control','id'=>'Part1', 'placeholder'=>'Part Number','required'=>'required','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)",'data-toggle'=>"tooltip",'data-placement'=>"bottom",'title'=>"Warning: Jangan gunakan tanda kutip pada partnumber."]) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('harga_beli', 'Harga Beli:') }}
                                        {{ Form::text('harga_beli', 0, ['class'=> 'form-control','id'=>'Beli1', 'placeholder'=>'Harga Beli','required'=>'required','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)",'readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('harga_jual', 'Harga Jual:') }}
                                        {{ Form::text('harga_jual', 0, ['class'=> 'form-control','id'=>'Jual1', 'placeholder'=>'Harga Jual','required'=>'required','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)",'readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('min_qty', 'Min Stock:') }}
                                        {{ Form::text('min_qty', 0, ['class'=> 'form-control','id'=>'Min1', 'placeholder'=>'Min Stock','required'=>'required','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('max_qty', 'Max Stock:') }}
                                        {{ Form::text('max_qty', 0, ['class'=> 'form-control','id'=>'Max1', 'placeholder'=>'Max Stock','required'=>'required','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
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
                <div class="row">
                    {{ Form::hidden('kode_produk', null, ['class'=> 'form-control','id'=>'Kode2','readonly']) }}   

                    <div class="col-md-9">
                        <div class="form-group">
                            {{ Form::label('Nama Produk', 'Nama Produk:') }}
                            {{ Form::text('nama_produk', null, ['class'=> 'form-control','id'=>'Nama2','autofocus'=>'autofocus','required'=>'required','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('tipe_produk', 'Tipe Produk:') }}
                            {{Form::select('tipe_produk', ['Serial' => 'Serial', 'NonSerial' => 'NonSerial'], null, ['class'=> 'form-control select2','style'=>'width: 100%','id'=>'tipe2','required'=>'required'])}}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('kode_kategori', 'Kategori:') }}
                            {{ Form::select('kode_kategori',$Kategori,null, ['class'=> 'form-control select2','style'=>'width: 100%','id'=>'Kategori2','required'=>'required']) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('kode_unit', 'Unit:') }}
                            {{ Form::select('kode_unit',$Unit,null, ['class'=> 'form-control select2','style'=>'width: 100%','id'=>'Unit2']) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('kode_merek', 'Merek:') }}
                            {{ Form::select('kode_merek', $Merek,null, ['class'=> 'form-control select2','style'=>'width: 100%','id'=>'Merek2']) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('kode_ukuran', 'Ukuran:') }}
                            {{ Form::select('kode_ukuran', $Ukuran,null, ['class'=> 'form-control select2','style'=>'width: 100%','id'=>'Ukuran','required'=>'required']) }}
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('kode_satuan', 'Satuan:') }}
                            {{ Form::select('kode_satuan', $Satuan,null, ['class'=> 'form-control select2','style'=>'width: 100%','id'=>'Satuan2','required'=>'required']) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('partnumber', 'Part Number:') }}
                            {{ Form::text('partnumber',null, ['class'=> 'form-control','id'=>'Part2','required'=>'required','autocomplete'=>'off','readonly', 'onkeypress'=>"return pulsar(event,this)"]) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('harga_beli', 'Harga Beli:') }}
                            {{ Form::text('harga_beli', null, ['class'=> 'form-control','id'=>'Beli2','required'=>'required','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)",'readonly']) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('harga_jual', 'Harga Jual:') }}
                            {{ Form::text('harga_jual', null, ['class'=> 'form-control','id'=>'Jual2','required'=>'required','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)",'readonly']) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('min_qty', 'Min Stock:') }}
                            {{ Form::text('min_qty', null, ['class'=> 'form-control','id'=>'Min', 'placeholder'=>'Min Stock','required'=>'required','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('max_qty', 'Max Stock:') }}
                            {{ Form::text('max_qty', 0, ['class'=> 'form-control','id'=>'Max', 'placeholder'=>'Max Stock','required'=>'required','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('stat', 'Status:') }}
                            {{Form::select('stat', ['Aktif' => 'Aktif', 'NonAktif' => 'NonAktif'], null, ['class'=> 'form-control select2','style'=>'width: 100%','id'=>'stat2','required'=>'required'])}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    {{ Form::submit('Update data', ['class' => 'btn btn-success']) }}
                    {{ Form::button('Close', ['class' => 'btn btn-danger','data-dismiss'=>'modal']) }}&nbsp;
                </div>
            </div>
            {!! Form::close() !!}
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="showform" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
          <div class="modal-content">
           
            <div class="modal-body">
                   <table class="table table-bordered table-striped" width="100%" style="font-size: 12px;">
                    <tr>
                        <th width="40%">Kode Produk</th>
                        <td><p id="kode"></p></td>
                    </tr>
                    <tr>
                        <th>Nama Produk</th>
                        <td><p id="nama"></p></td>
                    </tr>
                    <tr>
                        <th>Tipe Produk</th>
                        <td><p id="tipes"></p></td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td><p id="kategori"></p></td>
                    </tr>
                    <tr>
                        <th>Unit</th>
                        <td><p id="unit"></p></td>
                    </tr>
                    <tr>
                        <th>Merek</th>
                        <td><p id="merek"></p></td>
                    </tr>
                    <tr>
                        <th>Ukuran</th>
                        <td><p id="ukuran"></p></td>
                    </tr>
                    <tr>
                        <th>Satuan</th>
                        <td><p id="satuan"></p></td>
                    </tr>
                    <tr>
                        <th>Part Number</th>
                        <td><p id="partnumber"></p></td>
                    </tr>
                    <tr>
                        <th>Stok</th>
                        <td><p id="stock"></p></td>
                    </tr>
                    <tr>
                        <th>Harga Beli</th>
                        <td><p id="beli"></p></td>
                    </tr>
                    @permission('read-hpp')
                    <tr>
                        <th>Harga Jual</th>
                        <td><p id="jual"></p></td>
                    </tr>
                    <tr>
                        <th>Hpp</th>
                        <td><p id="hpps"></p></td>
                    </tr>
                    @endpermission
                    <tr>
                        <th>Company</th>
                        <td><p id="company"></p></td>
                    </tr>
                   </table>
            </div>
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    <div class="modal fade" id="button3"  role="dialog">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title">History Detail</h4>
                </div>
                @include('errors.validation')
                {!! Form::open(['route' => ['produk.export'],'method' => 'get','id'=>'form', 'target'=>"_blank"]) !!}
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        {{ Form::label('kode_produk', 'Produk:') }}
                                        {{ Form::select('kode_produk',$Produk->sort(),null,
                                         ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','required',
                                         'id'=>'kodeproduk']) }}
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        {{ Form::label('tanggal_awal', 'Dari Tanggal:') }}
                                        {{ Form::date('tanggal_awal',\Carbon\Carbon::now(), ['class'=> 'form-control','id'=>'tanggal1']) }}
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        {{ Form::label('tanggal_akhir', 'Sampai Tanggal:') }}
                                        {{ Form::date('tanggal_akhir',\Carbon\Carbon::now(), ['class'=> 'form-control','id'=>'tanggal2','placeholder'=>'Periode Baru']) }}                                    
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('show', 'History:') }}
                                        {{Form::select('show', ['Monthly' => 'Monthly', 'Transaksi' => 'Transaksi'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'history','required'=>'required','onchange'=>"pilih();"])}}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('format_cetak', 'Format:') }}
                                        {{Form::select('format_cetak', ['PDF' => 'PDF', 'Excel' => 'Excel'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'format1','required'=>'required'])}}
                                    </div>
                                </div>
                            <?php if (Auth()->user()->kode_lokasi == 'HO') { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('lokasi', 'Pilih Kode Lokasi:') }}
                                        {{ Form::select('lokasi',['SEMUA' => 'SEMUA','Lokasi'=>$lokasi],null, ['class'=> 'form-control select2','id'=>'lokasi1','style'=>'width: 100%','placeholder' => '','required']) }}
                                    </div>
                                </div>
                            <?php } ?>
                                <div class="col-md-8">
                                    <div class="form-group1">
                                        {{ Form::label('item2', 'Pilih Field:') }}
                                        {{ Form::select('item2[]', ['Pemakaian' => 'Pemakaian (OUT)', 'Penerimaan' => 'Penerimaan (IN)', 'Penjualan' => 'Penjualan (SALE)', 'Adjustment' => 'Adjustment', 'Opname' => 'Opname', 'Transfer_In' => 'Transfer In', 'Transfer_Out' => 'Transfer Out', 'Retur_Beli' => 'Retur Beli', 'Retur_Jual' => 'Retur Jual', 'Disassembling' => 'Disassembling', 'Assembling' => 'Assembling', 'SEMUA' => 'SEMUA'], null, ['class'=> 'form-control select2','id'=>'item','style'=>'width: 100%', 'multiple' => 'multiple','required'])}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="row">
                                {{ Form::submit('Cetak PDF', ['class' => 'btn btn-success crud-submit']) }}
                                {{ Form::button('Close', ['class' => 'btn btn-danger','data-dismiss'=>'modal']) }}&nbsp;
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <style type="text/css">

            /* Button used to open the contact form - fixed at the bottom of the page */
            .view-button {
                background-color: #1674c7;
                bottom: 156px;
            }

            .hapus-button {
                background-color: #F63F3F;
                bottom: 186px;
            }

            .edit-button {
                background-color: #FDA900;
                bottom: 216px;
            }

            #mySidenav button {
              position: fixed;
              right: -30px;
              transition: 0.3s;
              padding: 4px 8px;
              width: 70px;
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
            @permission('view-produk')
            <button type="button" class="btn btn-primary btn-xs view-button" id="viewproduk">VIEW <i class="fa fa-eye"></i></button>
            @endpermission

            @permission('update-produkor')
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editproduk" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>
            @endpermission

            @permission('delete-produkor')
            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapusproduk" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>
            @endpermission
        </div>
</body>
@stop

@push('css')

@endpush
@push('js')
  
<script type="text/javascript">

        function load(){
            startTime();
            $('.tombol1').hide();
            $('.view-button').hide();
            $('.edit-button').hide();
            $('.hapus-button').hide();
            $('.form-group1').hide();
        }

        $(function() {
            $('#data-table').DataTable({
            "bPaginate": true,
            "bFilter": true,
            "scrollY": 280,
            "scrollX": 400,
            "pageLength":100,
            ajax: '{!! route('produk.data') !!}',
            <?php if ($level == 'sany'){ ?>
                columns: [
                    { data: 'id', name: 'id', visible: false },
                    { data: 'nama_produk', name: 'nama_produk' },
                    { data: 'partnumber', name: 'partnumber' },
                    { data: 'nama_merek', "defaultContent": "<i>Not set</i>" },
                    { data: 'totalstock', name: 'totalstock' },
                ]
            <?php }else { ?>
                columns: [
                    { data: 'id', name: 'id', visible: false },
                    { data: 'nama_produk', name: 'nama_produk' },
                    { data: 'tipe_produk', name: 'tipe_produk' },
                    { data: 'partnumber', name: 'partnumber' },
                    { data: 'kategoriproduk.nama_kategori', name: 'kategoriproduk.nama_kategori', searchable: false },
                    { data: 'kode_unit', "defaultContent": "<i>Not set</i>" },
                    { data: 'merek.nama_merek', "defaultContent": "<i>Not set</i>" },
                    { data: 'ukuran.nama_ukuran', "defaultContent": "<i>Not set</i>" },
                    { data: 'satuan.nama_satuan', name: 'satuan.nama_satuan', searchable: false },
                    { data: 'min_qty', name: 'min_qty' },
                    { data: 'max_qty', "defaultContent": "<i>Not set</i>" },
                    { data: 'stat', name : 'stat'},
                ]
            <?php } ?>
            });
        });

        function formatRupiah(angka, prefix='Rp'){
           
            var rupiah = angka.toLocaleString(
                undefined, // leave undefined to use the browser's locale,
                // or use a string like 'en-US' to override it.
                { minimumFractionDigits: 0 }
            );
            return rupiah;
           
        }

        function pilih() {
            var pilih = $("#history").val();

            if (pilih == 'Monthly') {
                $('.form-group1').show();
                document.getElementById("item").required = true; 
                $('item option').removeProp('selected');
            }else{
                $('.form-group1').hide();
                document.getElementById("item").required = false; 
                $('item option').removeProp('selected');
            }
        }

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
                        url:'{!! route('produk.getkode') !!}',
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

        function getkode2(){
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
                        url:'{!! route('produk.getkode2') !!}',
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
        
        function createTable(result){

            var my_table = "";


        $.each( result, function( key, row ) {
                    my_table += "<tr>";
                    my_table += "<td>"+row.periode+"</td>";
                    my_table += "<td>"+row.partnumber+"</td>";
                    my_table += "<td>"+row.no_mesin+"</td>";
                    my_table += "<td>"+row.begin_stock+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(row.begin_amount)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.in_stock+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(row.in_amount)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.out_stock+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(row.out_amount)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.sale_stock+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(row.sale_amount)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.trf_in+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(row.trf_in_amount)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.trf_out+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(row.trf_out_amount)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.adjustment_stock+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(row.adjustment_amount)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.stock_opname+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(+row.amount_opname)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.retur_beli_stock+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(+row.retur_beli_amount)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.retur_jual_stock+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(+row.retur_jual_amount)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.disassembling_stock+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(+row.disassembling_amount)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.assembling_stock+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(+row.assembling_amount)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.ending_stock+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(+row.ending_amount)+"</td>";
                    my_table += "<td>"+formatRupiah(+row.hpp)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.kode_lokasi+"</td>";
                    my_table += "</tr>";
            });

            my_table = '<table id="table-fixed" class="table table-bordered table-hover" style="padding-left:50px; font-size:12px">'+ 
                        '<thead style="font-size:10px">'+
                           ' <tr class="bg-info">'+
                                '<th>Periode</th>'+
                                '<th>Partnumber</th>'+
                                '<th>No Mesin</th>'+
                                '<th>Begin Qty</th>'+
                                @permission('read-hpp')
                                '<th>Begin Amount</th>'+
                                @endpermission
                                '<th>In Qty</th>'+
                                @permission('read-hpp')
                                '<th>In Amount</th>'+
                                @endpermission
                                '<th>Out Qty</th>'+
                                @permission('read-hpp')
                                '<th>Out Amount</th>'+
                                @endpermission
                                '<th>Sale Qty</th>'+
                                @permission('read-hpp')
                                '<th>Sale Amount</th>'+
                                @endpermission
                                '<th>Transfer In</th>'+
                                @permission('read-hpp')
                                '<th>Trnsf. In Amount</th>'+
                                @endpermission
                                '<th>Transfer Out</th>'+
                                @permission('read-hpp')
                                '<th>Trnsf. Out Amount</th>'+
                                @endpermission
                                '<th>Adjustment Qty</th>'+
                                @permission('read-hpp')
                                '<th>Adjustment Amount</th>'+
                                @endpermission
                                '<th>Opname Qty </th>'+
                                @permission('read-hpp')
                                '<th>Opname Amount</th>'+
                                @endpermission
                                '<th>Retur Beli Qty </th>'+
                                @permission('read-hpp')
                                '<th>Retur Beli Amount</th>'+
                                @endpermission
                                '<th>Retur Jual Qty </th>'+
                                @permission('read-hpp')
                                '<th>Retur Jual Amount</th>'+
                                @endpermission
                                '<th>Disassembling Qty </th>'+
                                @permission('read-hpp')
                                '<th>Disassembling Amount</th>'+
                                @endpermission
                                '<th>Assembling Qty </th>'+
                                @permission('read-hpp')
                                '<th>Assembling Amount</th>'+
                                @endpermission
                                '<th>Ending Qty</th>'+
                                @permission('read-hpp')
                                '<th>Ending Amount</th>'+
                                '<th>HPP</th>'+
                                @endpermission
                                '<th>Kode Lokasi</th>'+
                            '</tr>'+
                        '</thead>'+
                        '<tbody>' + my_table + '</tbody>'+
                        '</table>';

                    // $(document).append(my_table);
            
            console.log(my_table);
            return my_table;
            // mytable.appendTo("#box");           
        
        }

        function createTable2(result){

            var my_table = "";

        $.each( result, function( key, row ) {
                    my_table += "<tr>";
                    my_table += "<td>"+row.kode_produk+"</td>";
                    my_table += "<td>"+row.partnumber+"</td>";
                    my_table += "<td>"+row.qty+"</td>";
                    my_table += "<td>"+formatRupiah(row.hpp)+"</td>";
                    my_table += "</tr>";
            });

            my_table = '<table id="table-fixed" class="table table-bordered table-hover" cellpadding="5" cellspacing="0" border="1" style="padding-left:50px; font-size:12px">'+ 
                        '<thead>'+
                           ' <tr class="bg-primary">'+
                                '<th>Kode Produk</th>'+
                                '<th>Part Number</th>'+
                                '<th>QTY</th>'+
                                '<th>HPP</th>'+
                            '</tr>'+
                        '</thead>'+
                        '<tbody>' + my_table + '</tbody>'+
                        '</table>';

                    // $(document).append(my_table);
            
            console.log(my_table);
            return my_table;
            // mytable.appendTo("#box");           
        
        }

        function createTable3(result){

            var my_table = "";

        $.each( result, function( key, row ) {
                    my_table += "<tr>";
                    my_table += "<td>"+row.tanggal_transaksi+"</td>";
                    my_table += "<td>"+row.no_transaksi+"</td>";
                    my_table += "<td>"+row.qty_transaksi+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(row.total_transaksi)+"</td>";
                    @endpermission
                    my_table += "<td>"+row.created_by+"</td>";
                    my_table += "</tr>";
            });

            my_table = '<table id="table-fixed" class="table table-bordered table-hover" cellpadding="5" cellspacing="0" border="1" style="padding-left:50px; font-size:12px">'+ 
                        '<thead>'+
                           ' <tr class="bg-primary">'+
                                '<th>Tanggal Transaksi</th>'+
                                '<th>No Transaksi</th>'+
                                '<th>Qty Transaksi</th>'+
                                @permission('read-hpp')
                                '<th>Total</th>'+
                                @endpermission
                                '<th>Dibuat Oleh</th>'+
                            '</tr>'+
                        '</thead>'+
                        '<tbody>' + my_table + '</tbody>'+
                        '</table>';

                    // $(document).append(my_table);
            
            console.log(my_table);
            return my_table;
            // mytable.appendTo("#box");           
        
        }

        function createTable4(result){

            var my_table = "";
            var total = 0;

        $.each( result, function( key, row ) {
                    my_table += "<tr>";
                    my_table += "<td>"+row.kode_produk+"</td>";
                    my_table += "<td>"+row.partnumber+"</td>";
                    my_table += "<td>"+row.no_mesin+"</td>";
                    my_table += "<td>"+row.kode_lokasi+"</td>";
                    my_table += "<td>"+row.ending_stock+"</td>";     
                    @permission('read-hpp')
                    my_table += "<td>"+formatRupiah(row.hpp)+"</td>";   
                    @endpermission
                    my_table += "</tr>";

                    total += row.ending_stock;
            });

            my_table = '<table id="table-fixed" class="table table-bordered table-hover" cellpadding="5" cellspacing="0" border="1" style="padding-left:50px; font-size:12px">'+ 
                        '<thead>'+
                           ' <tr class="bg-primary">'+
                                '<th>Kode Produk</th>'+
                                '<th>Partnumber</th>'+
                                '<th>No Mesin</th>'+
                                '<th>Kode Lokasi</th>'+
                                '<th>Stok</th>'+
                                @permission('read-hpp')
                                '<th>HPP</th>'+
                                @endpermission
                            '</tr>'+
                        '</thead>'+
                        '<tbody>' + my_table + '</tbody>'+
                            ' <tfoot>'+
                            '<tr class="bg-info">'+
                                '<th class="text-center" colspan="1">Total Stok</th>'+
                                '<th></th>'+
                                '<th></th>'+
                                '<th></th>'+             
                                @permission('read-hpp')
                                '<th>'+total+'</th>'+
                                @endpermission
                                '<th></th>'+           
                            '</tr>'+
                            '</tfoot>'+
                        '</table>';

                    // $(document).append(my_table);
            
            console.log(my_table);
            return my_table;
            // mytable.appendTo("#box");           
        
        }

        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip(); 
            
            var table = $('#data-table').DataTable();

            $('#data-table tbody').on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected bg-gray') ) {
                    $(this).removeClass('selected bg-gray');
                    $('.view-button').hide();
                    $('.edit-button').hide();
                    $('.hapus-button').hide();
                    $('#CreateBy1').val('');
                    $('#CreateAt1').val('');
                    $('#UpdateBy1').val('');
                    $('#UpdateAt1').val('');
                }
                else {
                    table.$('tr.selected').removeClass('selected bg-gray');
                    $(this).addClass('selected bg-gray');
                    var select = $('.selected').closest('tr');
                    
                    closeOpenedRows(table, select);
                    
                    var data = $('#data-table').DataTable().row(select).data();
                    $('.view-button').show();
                    $('.edit-button').show();
                    $('.hapus-button').show();
                    $('#CreateBy1').val(data['created_by']);
                    $('#CreateAt1').val(data['created_at']);
                    $('#UpdateBy1').val(data['updated_by']);
                    $('#UpdateAt1').val(data['updated_at']);
                    
                    var tipe = data['tipe_produk'];
                    var kategori = data['kode_kategori'];
                    if(tipe == 'Serial' && kategori == 'UNIT'){
                        $('.tombol1').show();
                    }else{
                        $('.tombol1').hide();
                    }
                }
            } );

            $('#viewproduk').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var kode_produk = data['id'];
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('produk.show_produk') !!}',
                    type: 'POST',
                    data : {
                        'id': kode_produk
                    },
                    success: function(result) {
                        $('#kode').html(result.kode_produk);
                        $('#nama').html(result.nama_produk);
                        $('#tipes').html(result.tipe_produk);
                        $('#kategori').html(result.kode_kategori);
                        $('#unit').html(result.kode_unit);
                        $('#merek').html(result.kode_merek);
                        $('#ukuran').html(result.kode_ukuran);
                        $('#satuan').html(result.kode_satuan);
                        $('#company').html(result.kode_company);
                        $('#partnumber').html(result.partnumber);
                        $('#beli').html(formatRupiah(result.harga_beli));
                        @permission('read-hpp')
                        $('#jual').html(formatRupiah(result.harga_jual));
                        $('#hpps').html(formatRupiah(result.hpp));
                        @endpermission
                        $('#stock').html(result.stok);
                        $('#stats').html(result.stat);
                        $('#url').html(result.url);
                        $('#showform').modal('show');
                        }
         
                });
            });

            $('#editproduk').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var kode_produk = data['id'];
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('produk.edit_produk') !!}',
                    type: 'POST',
                    data : {
                        'id': kode_produk
                    },
                    success: function(results) {
                        console.log(results);
                        $('#Kode2').val(results.kode_produk);
                        $('#Nama2').val(results.nama_produk);
                        $('#tipe2').val(results.tipe_produk).trigger('change');
                        $('#Kategori2').val(results.kode_kategori).trigger('change');
                        $('#Unit2').val(results.kode_unit).trigger('change');
                        $('#Merek2').val(results.kode_merek).trigger('change');
                        $('#Ukuran2').val(results.kode_ukuran).trigger('change');
                        $('#Satuan2').val(results.kode_satuan).trigger('change');
                        $('#Company2').val(results.kode_company);
                        $('#Part2').val(results.partnumber);
                        $('#Beli2').val(results.harga_beli);
                        $('#Jual2').val(results.harga_jual);
                        $('#stat2').val(results.stat).trigger('change');
                        $('#Min').val(results.min_qty);
                        $('#Max').val(results.max_qty);
                        $('#editform').modal('show');
                        }
         
                });
            });

            $('#hapusproduk').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var kode_produk = data['id'];
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
                            url: '{!! route('produk.hapus_produk') !!}',
                            type: 'POST',
                            data : {
                                'id': kode_produk
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
                var data = $('#data-table').DataTable().row(select).data();
                var kode_produk = data['id'];
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('produk.showmonthly') !!}',
                    type: 'POST',
                    data : {
                        'id': kode_produk
                    },
                    success: function(result) {
                        console.log(result);
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

            $('#button6').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var kode_produk = data['id'];
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('produk.showhistory') !!}',
                    type: 'POST',
                    data : {
                        'id': kode_produk
                    },
                    success: function(result) {
                        console.log(result);
                        if(result.title == 'Gagal'){
                            $.notify(result.message);
                        }else{
                            if ( row.child.isShown() ) {
                                row.child.hide();
                                select.removeClass('shown');
                            }
                            else {
                                closeOpenedRows(table, select);

                                row.child( createTable3(result) ).show();
                                select.addClass('shown');

                                openRows.push(select);
                            }
                        }
                    }
                });
            });

            $('#button7').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var kode_produk = data['id'];
                var row = table.row( select );
                // console.log(no_pemakaian);               
                $.ajax({
                    url: '{!! route('produk.showstock') !!}',
                    type: 'POST',
                    data : {
                        'id': kode_produk
                    },
                    success: function(result) {
                        console.log(result);
                        if(result.title == 'Gagal'){
                            $.notify(result.message);
                        }else{
                            if ( row.child.isShown() ) {
                                row.child.hide();
                                select.removeClass('shown');
                            }
                            else {
                                closeOpenedRows(table, select);

                                row.child( createTable4(result) ).show();
                                select.addClass('shown');

                                openRows.push(select);
                            }
                        }
                    }
                });
            });

        } );


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

        function format ( d ) {
            // `d` is the original data object for the row
            return '<div class="col-md-12">'+
                           ' <!-- Custom Tabs -->'+
                            '<div class="nav-tabs-custom">'+
                              '<ul class="nav nav-tabs">'+
                                '<li class="active"><a href="#tab_1" data-toggle="tab">View Monthly</a></li>'+
                               ' <li><a href="#tab_2" data-toggle="tab">Tab 2</a></li>'+
                                '<li><a href="#tab_3" data-toggle="tab">Tab 3</a></li>'+
                              '</ul>'+
                              '<div class="tab-content">'+
                                '<div class="tab-pane active" id="tab_1">'+
                                    '<table class="table  table-bordered table-hover" cellpadding="5" cellspacing="0" border="1" style="padding-left:50px;">'+
                                    '<thead>'+
                                    '<tr class="bg-purple">'+
                                        '<th>Periode:</th>'+
                                        '<th>Kode Produk:</th>'+
                                        '<th>Satuan:</th>'+
                                        '<th>Begin Stock:</th>'+
                                        '<th>Begin Amount:</th>'+
                                        '<th>In Stock:</th>'+
                                        '<th>In Amount:</th>'+
                                    '</tr>'+
                                    '</thead>'+
                                    '<tbody>'+
                                    '<tr>'+
                                        '<td>'+d.periode+'</td>'+
                                        '<td>'+d.kode_produk+'</td>'+
                                        '<td>'+d.satuan+'</td>'+
                                        '<td>'+d.begin_stock+'</td>'+
                                        '<td>'+d.begin_amount+'</td>'+
                                        '<td>'+d.in_stock+'</td>'+
                                        '<td>'+d.in_amount+'</td>'+
                                    '</tr>'+
                                    '</tbody>'+
                                '</table>'+
                                '</div>'+
                                '<!-- /.tab-pane -->'+
                                '<div class="tab-pane" id="tab_2">'+
                                'ok2'+
                                '</div>'+
                               ' <!-- /.tab-pane -->'+
                                '<div class="tab-pane" id="tab_3">'+
                                'ok3'+
                               ' </div>'+
                                '<!-- /.tab-pane -->'+
                              '</div>'+
                            '  <!-- /.tab-content -->'
                            '</div>'+
                            '<!-- nav-tabs-custom -->'+
                         ' <!-- /.col -->'+
                    ' </div>'
            ;
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
             $('#data-table').DataTable().ajax.reload(null,false);;
        }

        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });

        $('.modal-dialog').resizable({
    
        });

        $('#ADD').submit(function (e) {
            e.preventDefault();
            var registerForm = $("#ADD");
            var formData = registerForm.serialize();

                $.ajax({
                    url:'{!! route('produk.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#Nama1').val('');
                        $('#tipe1').val('').trigger('change');
                        $('#Kategori1').val('').trigger('change');
                        $('#Unit1').val('').trigger('change');
                        $('#Merek1').val('').trigger('change');
                        $('#Ukuran1').val('').trigger('change');
                        $('#Satuan1').val('').trigger('change');
                        $('#Part1').val('');
                        $('#Beli1').val('0');
                        $('#Jual1').val('0');
                        $('#stat1').val('').trigger('change');
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
            e.preventDefault();
            var registerForm = $("#EDIT");
            var formData = registerForm.serialize();

                $.ajax({
                    url:'{!! route('produk.ajaxupdate') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
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