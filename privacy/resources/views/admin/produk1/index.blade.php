@extends('adminlte::page')

@section('title', 'Produk 1')

@section('content_header')

@endsection

@section('content')

    <body onload="load()">
        {{-- box --}}

        <div class="box box-solid">
            <div class="box-body">
                <div class="box">
                    <div class="box-body">
                        {{-- refresh table --}}
                        <button class="btn btn-default btn-xs" onclick="refreshTable()"><i class="fa fa-refresh"></i>
                            Refresh</button>

                        {{-- create new product --}}
                        @permission('create-produkor')
                            <button class="btn btn-success btn-xs" data-toggle="modal" data-target="#addform"><i
                                    class="fa fa-plus"></i> New Product</button>
                        @endpermission

                        {{-- additional tools --}}
                        @if (auth()->user()->level != 'sany')
                            <div class="pull-right">
                                {{-- view stock button --}}
                                <button id="view-stock" class="btn btn-success btn-xs view-button"><i
                                        class="fa fa-paperclip"></i> View
                                    Stock</button>

                                {{-- view history button --}}
                                <button id="view-history" class="btn btn-warning btn-xs view-button"><i
                                        class="fa fa-paperclip"></i> View History</button>

                                {{-- view monthly button --}}
                                <button id="view-monthly" class="btn btn-primary btn-xs view-button"><i class="fa fa-paperclip"></i> View Monthly</button>
                                
                                {{-- Print list produk --}}
                                <a href='{{route('produk1.index')}}/exportexcel' id="print-list-produk" class="btn btn-info btn-xs"><i
                                    class="fa fa-print"></i> Print List Produk</a>

                                {{-- detail history button --}}
                                <button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#detailform"><i
                                        class="fa fa-print"></i> Detail History</button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- table product --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="data-table" style="font-size: 12px">
                        {{-- table head --}}
                        <thead>
                            <tr class="bg-blue">
                                @if ($level == 'sany')
                                    <th>Kode Produk</th>
                                    <th>Nama Produk</th>
                                    <th>Part Number</th>
                                    <th>Merek</th>
                                    <th>Stock</th>
                                @else
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
                                @endif
                            </tr>
                        </thead>
                    </table>
                </div>

                {{-- additional information about the selected row --}}

                <div class="row" style="margin-top: 1.5rem">
                    {{-- created by input with readonly --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('created_by', 'Created By : ') }}
                            {{ Form::text('created_by', null, [
                                'class' => 'form-control',
                                'readonly',
                            ]) }}
                        </div>
                    </div>

                    {{-- created at input with readonly --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('created_at', 'Created At : ') }}
                            {{ Form::text('created_at', null, [
                                'class' => 'form-control',
                                'readonly',
                            ]) }}
                        </div>
                    </div>

                    {{-- updated by input with readonly --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('updated_by', 'Updated By : ') }}
                            {{ Form::text('updated_by', null, [
                                'class' => 'form-control',
                                'readonly',
                            ]) }}
                        </div>
                    </div>

                    {{-- updated at input with readonly --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('updated_at', 'Updated At : ') }}
                            {{ Form::text('updated_at', null, [
                                'class' => 'form-control',
                                'readonly',
                            ]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- creting random qrcode (no-library) --}}
        {{-- <div>
            {!! QrCode::size(100)->generate('eko saputra') !!}
        </div> --}}

        {{-- creating mysidenav --}}
        <div id="mySidenav">
            {{-- view produk --}}
            @permission('view-produk')
                <button class="btn btn-primary btn-xs sidenav-item" id="view-produk">
                    <i class="fa fa-eye"></i> View
                </button>
            @endpermission

            {{-- edit produk --}}
            {{-- @permission('update-produkor')
                <button class="btn btn-warning btn-xs sidenav-item" id="edit-produk" data-toggle="modal" data-target=""><i
                        class="fa fa-edit"></i> Edit</button>
            @endpermission --}}

            {{-- delete produk --}}
            <button class="btn btn-danger btn-xs sidenav-item" id="hapus-produk" data-target="" data-toggle="modal"><i
                    class="fa fa-times-circle"></i> Hapus</button>
        </div>
    </body>

    {{-- modal for add new product --}}
    <div class="modal fade" id="addform" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Create New Product</h4>
                </div>

                @include('errors.validation')
                {{ Form::open(['id' => 'ADD']) }}
                <div class="modal-body">
                    <div class="row">
                        {{-- Nama produk --}}
                        <div class="col-md-8">
                            <div class="form-group">
                                {{ Form::label('nama_produk', 'Nama Produk :') }}
                                {{ Form::text('nama_produk', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => 'Nama Produk',
                                    'oninput' => 'autoCaps(this)',
                                    'autocomplete' => 'off',
                                ]) }}
                            </div>
                        </div>

                        {{-- Tipe produk --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('tipe_produk', 'Tipe Produk :') }}
                                {{ Form::select(
                                    'tipe_produk',
                                    [
                                        'Serial' => 'Serial',
                                        'Nonserial' => 'NonSerial',
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control select2',
                                        'style' => 'width: 100%',
                                        'placeholder' => '',
                                    ],
                                ) }}
                            </div>
                        </div>

                        {{-- Kode Kategori --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('kode_kategori', 'Kategori :') }}
                                {{ Form::select('kode_kategori', $kategori, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width: 100%',
                                    'required',
                                    'placeholder' => '',
                                ]) }}
                            </div>
                        </div>

                        {{-- Kode Kategori --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('kode_unit', 'Unit :') }}
                                {{ Form::select('kode_unit', $unit, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width: 100%',
                                    'required',
                                    'placeholder' => '',
                                ]) }}
                            </div>
                        </div>

                        {{-- Kode Merek --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('kode_merek', 'Merek :') }}
                                {{ Form::select('kode_merek', $merek, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width: 100%',
                                    'required',
                                    'placeholder' => '',
                                ]) }}
                            </div>
                        </div>

                        {{-- Kode Ukuran add --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('kode_ukuran', 'Ukuran :') }}
                                {{ Form::select('kode_ukuran', $ukuran, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width: 100%',
                                    'required',
                                    'placeholder' => '',
                                ]) }}
                            </div>
                        </div>

                        {{-- Kode Satuan add --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('kode_satuan', 'Satuan :') }}
                                {{ Form::select('kode_satuan', $satuan, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width: 100%',
                                    'required',
                                    'placeholder' => '',
                                ]) }}
                            </div>
                        </div>

                        {{-- Part Number --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('partnumber', 'Part Number :') }}
                                {{ Form::text('partnumber', null, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Part Number',
                                    'required',
                                    'autocomplete' => 'off',
                                    'oninput' => 'autoCaps(this)',
                                    'data-toggle' => 'tooltip',
                                    'data-placement' => 'bottom',
                                    'title' => 'Warning: Jangan gunakan tanda kutip pada partnumber',
                                ]) }}
                            </div>
                        </div>

                        {{-- Harga Beli --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('harga_beli', 'Harga Beli :') }}
                                {{ Form::text('harga_beli', 0, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Harga beli',
                                    'required',
                                    'autocomplete' => 'off',
                                    'readonly',
                                ]) }}
                            </div>
                        </div>

                        {{-- Harga Jual --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('harga_jual', 'Harga Jual :') }}
                                {{ Form::text('harga_jual', 0, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Harga jual',
                                    'required',
                                    'autocomplete' => 'off',
                                    'readonly',
                                ]) }}
                            </div>
                        </div>


                        {{-- Min Stok --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('min_qty', 'Min Stock :') }}
                                {{ Form::number('min_qty', 0, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Min Stock',
                                    'required',
                                    'autocomplete' => 'off',
                                ]) }}
                            </div>
                        </div>

                        {{-- Max Stok --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('max_qty', 'Max Stock :') }}
                                {{ Form::number('max_qty', 0, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Max Stock',
                                    'required',
                                    'autocomplete' => 'off',
                                ]) }}
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('stat', 'Status : ') }}
                                {{ Form::select(
                                    'stat',
                                    [
                                        'Aktif' => 'Aktif',
                                        'NonAktif' => 'NonAktif',
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control select2',
                                        'style' => 'width: 100%',
                                        'placeholder' => '',
                                        'required',
                                    ],
                                ) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- modal footer --}}
                <div class="modal-footer">
                    <div class="row">
                        {{ Form::submit('Create data', [
                            'class' => 'btn btn-success crud-submit',
                        ]) }}
                        {{ Form::button('Close', [
                            'class' => 'btn btn-danger',
                            'data-dismiss' => 'modal',
                        ]) }}
                        &nbsp;&nbsp;
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    {{-- modal for show monthly selected row --}}
    <div class="modal fade" id="showmonthly" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md " role="document">
            <div class="modal-content">
                {{-- modal header --}}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">View Monthly Detail</h4>
                </div>

                {{-- modal body --}}
                <div class="modal-body">
                    <table class="table table-bordered table-striped" width="100%" style="font-size: 12px;">
                        <tr>
                            <th style="width: 30%">Periode</th>
                            <td>
                                <span id="periode_show_monthly"></span>
                            </td>
                        </tr>
                        <tr>
                            <th>Partnumber</th>
                            <td>
                                <span id="partnumber_show_monthly"></span>
                            </td>
                        </tr>
                        <tr>
                            <th>No Mesin</th>
                            <td>
                                <span id="no_mesin_show_mothly"></span>
                            </td>
                        </tr>
                        <tr>
                            <th>Begin Stock</th>
                            <td>
                                <span id="begin_stock_show_mothly"></span>
                            </td>
                        </tr>
                        @permission('read-hpp')
                        <tr>
                            <th>Begin Amount</th>
                            <td>
                                <span id="begin_amount_show_mothly"></span>
                            </td>
                        </tr>
                        @endpermission
                        <tr>
                            <th>In Stock</th>
                            <td>
                                <span id="in_stock_show_mothly"></span>
                            </td>
                        </tr>
                        @permission('read-hpp')
                        <tr>
                            <th>In Amount</th>
                            <td>
                                <span id="in_amount_show_mothly"></span>
                            </td>
                        </tr>
                        @endpermission
                        <tr>
                            <th>Out Stock</th>
                            <td>
                                <span id="out_stock_show_mothly"></span>
                            </td>
                        </tr>
                        @permission('read-hpp')
                        <tr>
                            <th>Out Amount</th>
                            <td>
                                <span id="out_amount_show_mothly"></span>
                            </td>
                        </tr>
                        @endpermission
                        <tr>
                            <th>Sale Stock</th>
                            <td>
                                <span id="sale_stock_show_mothly"></span>
                            </td>
                        </tr>
                        @permission('read-hpp')
                        <tr>
                            <th>Sale Amount</th>
                            <td>
                                <span id="sale_amount_show_mothly"></span>
                            </td>
                        </tr>
                        @endpermission
                        <tr>
                            <th>Transfer In Stock</th>
                            <td>
                                <span id="trf_in_stock_show_mothly"></span>
                            </td>
                        </tr>
                        @permission('read-hpp')
                        <tr>
                            <th>Transfer In Amount</th>
                            <td>
                                <span id="trf_in_amount_show_mothly"></span>
                            </td>
                        </tr>
                        @endpermission
                        <tr>
                            <th>Transfer Out Stock</th>
                            <td>
                                <span id="trf_out_stock_show_mothly"></span>
                            </td>
                        </tr>
                        @permission('read-hpp')
                        <tr>
                            <th>Transfer Out Amount</th>
                            <td>
                                <span id="trf_out_amount_show_mothly"></span>
                            </td>
                        </tr>
                        @endpermission
                        <tr>
                            <th>Adjustment Stock</th>
                            <td>
                                <span id="adjustment_stock_show_mothly"></span>
                            </td>
                        </tr>
                        @permission('read-hpp')
                        <tr>
                            <th>Adjustment Amount</th>
                            <td>
                                <span id="adjustment_amount_show_mothly"></span>
                            </td>
                        </tr>
                        @endpermission
                        <tr>
                            <th>Opname Stock</th>
                            <td>
                                <span id="opname_stock_show_mothly"></span>
                            </td>
                        </tr>
                        @permission('read-hpp')
                        <tr>
                            <th>Opname Amount</th>
                            <td>
                                <span id="opname_amount_show_mothly"></span>
                            </td>
                        </tr>
                        @endpermission
                        <tr>
                            <th>Retur Beli Stock</th>
                            <td>
                                <span id="retur_beli_stock_show_mothly"></span>
                            </td>
                        </tr>
                        @permission('read-hpp')
                        <tr>
                            <th>Retur Beli Amount</th>
                            <td>
                                <span id="retur_beli_amount_show_mothly"></span>
                            </td>
                        </tr>
                        @endpermission
                        <tr>
                            <th>Retur Jual Stock</th>
                            <td>
                                <span id="retur_jual_stock_show_mothly"></span>
                            </td>
                        </tr>
                        @permission('read-hpp')
                        <tr>
                            <th>Retur Jual Amount</th>
                            <td>
                                <span id="retur_jual_amount_show_mothly"></span>
                            </td>
                        </tr>
                        @endpermission
                        <tr>
                            <th>Disassembling Stock</th>
                            <td>
                                <span id="disassembling_stock_show_mothly"></span>
                            </td>
                        </tr>
                        @permission('read-hpp')
                        <tr>
                            <th>Disassembling Amount</th>
                            <td>
                                <span id="disassembling_amount_show_mothly"></span>
                            </td>
                        </tr>
                        @endpermission
                        <tr>
                            <th>Assembling Stock</th>
                            <td>
                                <span id="assembling_stock_show_mothly"></span>
                            </td>
                        </tr>
                        @permission('read-hpp')
                        <tr>
                            <th>Assembling Amount</th>
                            <td>
                                <span id="assembling_amount_show_mothly"></span>
                            </td>
                        </tr>
                        @endpermission
                        @permission('read-hpp')
                        <tr>
                            <th>Ending Amount</th>
                            <td>
                                <span id="ending_amount_show_mothly"></span>
                            </td>
                        </tr>
                        <tr>
                            <th>Hpp</th>
                            <td>
                                <span id="hpp_show_mothly"></span>
                            </td>
                        </tr>
                        @endpermission
                        <tr>
                            <th>Kode Lokasi</th>
                            <td>
                                <span id="kode_lokasi_show_mothly"></span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="modal-footer">
                    <div class="row">
                        {{ Form::button('Close', [
                            'class' => 'btn btn-danger',
                            'data-dismiss' => 'modal',
                        ]) }}&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- modal for show selected row --}}
    <div class="modal fade" id="showform" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md " role="document">
            <div class="modal-content">
                {{-- modal header --}}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Show Detail</h4>
                </div>

                {{-- modal body --}}
                <div class="modal-body">
                    <table class="table table-bordered table-striped" width="100%" style="font-size: 12px;">
                        <tr>
                            <th style="width: 30%">Kode Produk</th>
                            <td>
                                <p id="kode_produk_show"></p>
                            </td>
                        </tr>
                        <tr>
                            <th>Nama Produk</th>
                            <td>
                                <p id="nama_produk_show"></p>
                            </td>
                        </tr>
                        <tr>
                            <th>Tipe Produk</th>
                            <td>
                                <p id="tipe_produk_show"></p>
                            </td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td>
                                <p id="kategori_show"></p>
                            </td>
                        </tr>
                        <tr>
                            <th>Unit</th>
                            <td>
                                <p id="unit_show"></p>
                            </td>
                        </tr>
                        <tr>
                            <th>Merek</th>
                            <td>
                                <p id="merek_show"></p>
                            </td>
                        </tr>
                        <tr>
                            <th>Ukuran</th>
                            <td>
                                <p id="ukuran_show"></p>
                            </td>
                        </tr>
                        <tr>
                            <th>Satuan</th>
                            <td>
                                <p id="satuan_show"></p>
                            </td>
                        </tr>
                        <tr>
                            <th>Part Number</th>
                            <td>
                                <p id="partnumber_show"></p>
                            </td>
                        </tr>
                        <tr>
                            <th>Stock</th>
                            <td>
                                <p id="stock_show"></p>
                            </td>
                        </tr>
                        <tr>
                            <th>Harga Beli</th>
                            <td>
                                <p id="beli_show"></p>
                            </td>
                        </tr>
                        @permission('read-hpp')
                            <tr>
                                <th>Harga Jual</th>
                                <td>
                                    <p id="jual_show"></p>
                                </td>
                            </tr>
                            <tr>
                                <th>Hpp</th>
                                <td>
                                    <p id="hpp_show"></p>
                                </td>
                            </tr>
                        @endpermission
                        <tr>
                            <th>Company</th>
                            <td>
                                <p id="company"></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="modal-footer">
                    <div class="row">
                        {{ Form::button('Edit', [
                            'class' => 'btn btn-success',
                            'data-dismiss' => 'modal',
                            'id' => 'showform-edit',
                        ]) }}
                        {{ Form::button('Close', [
                            'class' => 'btn btn-danger',
                            'data-dismiss' => 'modal',
                        ]) }}&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- modal for edit form from selected row --}}
    <div class="modal fade" id="editform" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Edit Data</h4>
                </div>
                @include('errors.validation')
                {{ Form::open(['id' => 'EDIT']) }}
                <div class="modal-body">
                    <div class="row">
                        {{-- produk id --}}
                        {{ Form::hidden('kode_produk_edit', null, ['readonly', 'id' => 'kode_produk_edit']) }}

                        {{-- nama produk --}}
                        <div class="col-md-8">
                            <div class="form-group">
                                {{ Form::label('nama_produk_edit', 'Nama Produk : ') }}
                                {{ Form::text('nama_produk_edit', null, [
                                    'class' => 'form-control',
                                    'autocomplete' => 'off',
                                    'oninput' => 'autoCaps(this)',
                                    'placeholder' => 'Nama Produk',
                                ]) }}
                            </div>
                        </div>

                        {{-- tipe produk --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('tipe_produk_edit', 'Tipe Produk : ') }}
                                {{ Form::select(
                                    'tipe_produk_edit',
                                    [
                                        'Serial' => 'Serial',
                                        'NonSerial' => 'NonSerial',
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control select2',
                                        'style' => 'width: 100%',
                                        'placeholder' => '',
                                        'required',
                                    ],
                                ) }}
                            </div>
                        </div>

                        {{-- kategori produk --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('kode_kategori_edit', 'Kategori Produk : ') }}
                                {{ Form::select('kode_kategori_edit', $kategori, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width: 100%',
                                    'placeholder' => '',
                                    'required',
                                ]) }}
                            </div>
                        </div>

                        {{-- kode unit --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('kode_unit_edit', 'Unit : ') }}
                                {{ Form::select('kode_unit_edit', $unit, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width: 100%',
                                    'placeholder' => '',
                                    'required',
                                ]) }}
                            </div>
                        </div>

                        {{-- kode merek --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('kode_merek_edit', 'Merek : ') }}
                                {{ Form::select('kode_merek_edit', $merek, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width: 100%',
                                    'placeholder' => '',
                                    'required',
                                ]) }}
                            </div>
                        </div>

                        {{-- kode ukuran --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('kode_ukuran_edit', 'Ukuran : ') }}
                                {{ Form::select('kode_ukuran_edit', $ukuran, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width: 100%',
                                    'placeholder' => '',
                                    'required',
                                ]) }}
                            </div>
                        </div>

                        {{-- kode satuan --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('kode_satuan_edit', 'Satuan : ') }}
                                {{ Form::select('kode_satuan_edit', $satuan, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width: 100%',
                                    'placeholder' => '',
                                    'required',
                                ]) }}
                            </div>
                        </div>

                        {{-- Partnumber --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('partnumber_edit', 'Part Number : ') }}
                                {{ Form::text('partnumber_edit', null, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Part Number',
                                    'required',
                                    'readonly',
                                    'oninput' => 'autoCaps(this)',
                                    'autocomplete' => 'off',
                                ]) }}
                            </div>
                        </div>

                        {{-- harga beli --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('harga_beli_edit', 'Harga Beli : ') }}
                                {{ Form::number('harga_beli_edit', null, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Harga Beli',
                                    'required',
                                    'readonly',
                                    'autocomplete' => 'off',
                                ]) }}
                            </div>
                        </div>

                        {{-- harga jual --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('harga_jual_edit', 'Harga Jual : ') }}
                                {{ Form::number('harga_jual_edit', null, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Harga Jual',
                                    'required',
                                    'readonly',
                                    'autocomplete' => 'off',
                                ]) }}
                            </div>
                        </div>

                        {{-- Min stock --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('min_qty_edit', 'Min Jual : ') }}
                                {{ Form::number('min_qty_edit', null, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Min Jual',
                                    'required',
                                    'autocomplete' => 'off',
                                ]) }}
                            </div>
                        </div>

                        {{-- Max stock --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('max_qty_edit', 'Max Jual : ') }}
                                {{ Form::number('max_qty_edit', null, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Max Jual',
                                    'required',
                                    'autocomplete' => 'off',
                                ]) }}
                            </div>
                        </div>

                        {{-- status --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('stat_edit', 'Status :') }}
                                {{ Form::select(
                                    'stat_edit',
                                    [
                                        'Aktif' => 'Aktif',
                                        'NonAktif' => 'NonAktif',
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control select2',
                                        'style' => 'width: 100%',
                                        'required',
                                        'placeholder' => '',
                                    ],
                                ) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="row">
                        {{ Form::submit('Update Data', [
                            'class' => 'btn btn-success',
                        ]) }}
                        {{ Form::button('Close', [
                            'class' => 'btn btn-danger',
                            'data-dismiss' => 'modal',
                        ]) }}&nbsp;&nbsp;&nbsp;
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    {{-- modal for detail form --}}
    <div class="modal fade" id="detailform" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {{-- the header of the modal --}}
                <div class="modal-header">
                    <button class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">History Detail</h4>
                </div>

                {{-- the body of the modal --}}
                @include('errors.validation')
                {{ Form::open([
                    'route' => ['produk1.export'],
                    'method' => 'GET',
                    'id' => 'detailform_get',
                    'target' => '_blank',
                ]) }}
                <div class="modal-body">
                    <div class="row">
                        {{-- kode produk --}}
                        <div class="col-md-8">
                            <div class="form-group">
                                {{ Form::label('kode_produk_detail', 'Produk :') }}
                                {{ Form::select('kode_produk_detail', $produk, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width: 100%',
                                    'placeholder' => '',
                                    'required',
                                ]) }}
                            </div>
                        </div>

                        {{-- tanggal awal --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('tanggal_awal_detail', 'Dari Tanggal : ') }}
                                {{ Form::date('tanggal_awal_detail', \Carbon\Carbon::now(), [
                                    'class' => 'form-control',
                                ]) }}
                            </div>
                        </div>

                        {{-- tanggal akhir --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('tanggal_akhir_detail', 'Sampai Tanggal : ') }}
                                {{ Form::date('tanggal_akhir_detail', \Carbon\Carbon::now(), [
                                    'class' => 'form-control',
                                ]) }}
                            </div>
                        </div>

                        {{-- History detail --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('history_detail', 'History : ') }}
                                {{ Form::select(
                                    'history_detail',
                                    [
                                        'Monthly' => 'Monthly',
                                        'Transaksi' => 'Transaksi',
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control select2',
                                        'style' => 'width: 100%',
                                        'placeholder' => '',
                                        'required',
                                        'onchange' => 'pilihHistory()',
                                    ],
                                ) }}
                            </div>
                        </div>

                        {{-- Format detail --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('format_detail', 'Format :') }}
                                {{ Form::select(
                                    'format_detail',
                                    [
                                        'PDF' => 'PDF',
                                        'Excel' => 'Excel',
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control select2',
                                        'style' => 'width: 100%',
                                        'placeholder' => '',
                                        'required',
                                    ],
                                ) }}
                            </div>
                        </div>

                        {{-- kode lokasi detail --}}
                        @if (auth()->user()->kode_lokasi == 'HO')
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('kode_lokasi_detail', 'Pilih Kode Lokasi :') }}
                                    {{ Form::select(
                                        'kode_lokasi_detail',
                                        [
                                            'SEMUA' => 'SEMUA',
                                            'Lokasi' => $lokasi,
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control select2',
                                            'style' => 'width: 100%',
                                            'placeholder' => '',
                                            'required',
                                        ],
                                    ) }}
                                </div>
                            </div>
                        @endif

                        {{-- Field detail --}}
                        <div class="col-md-8  field_detail_form">
                            <div class="form-group">
                                {{ Form::label('field_detail[]', 'Pilih Field : ') }}
                                {{ Form::select(
                                    'field_detail[]',
                                    [
                                        'Pemakaian' => 'Pemakaian (OUT)',
                                        'Penerimaan' => 'Penerimaan (IN)',
                                        'Penjualan' => 'Penjualan (SALE)',
                                        'Adjustment' => 'Adjustment',
                                        'Opname' => 'Opname',
                                        'Transfer_In' => 'Transfer In',
                                        'Transfer_Out' => 'Transfer Out',
                                        'Retur_Beli' => 'Retur Beli',
                                        'Retur_Jual' => 'Retur Jual',
                                        'Disassembling' => 'Disassembling',
                                        'Assembling' => 'Assembling',
                                        'SEMUA' => 'SEMUA',
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control select2',
                                        'style' => 'width: 100%',
                                        'required',
                                        'multiple' => 'multiple',
                                    ],
                                ) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        {{ Form::submit('Cetak', [
                            'class' => 'btn btn-success',
                        ]) }}
                        {{ Form::button('Close', [
                            'class' => 'btn btn-danger',
                            'data-dismiss' => 'modal',
                        ]) }}&nbsp;&nbsp;&nbsp;
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@stop

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <link rel="icon" type="image/png" href="/gui_inventory_gut_laravel/css/logo_gui.png" sizes="16x16">
    <link rel="icon" type="image/png" href="/gui_inventory_gut_laravel/css/logo_gui.png" sizes="32x32">

    <style type="text/css">
        #mySidenav button {
            position: fixed;
            right: -20px;
            top: 400px;
            transition: 0.3s;
            width: 80px;
            padding: 4px 8px;
            text-decoration: none;
            font-size: 12px;
            color: white;
            border-radius: 5px 0 0 5px;
            opacity: 0.9;
            cursor: pointer;
            text-align: left;
        }

        #mySidenav button:hover {
            right: 0px;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>

    <script type="text/javascript">
        // for closing child row
        var openRows = new Array();

        // when page is loaded for the first time and call load() function to call startTime() function to run the timer
        function load() {
            startTime();
            $('.sidenav-item').hide();

            // sidenav-item margin-top
            let marginTop = 0;
            $('button.sidenav-item').each(function(i, obj) {
                $(obj).css('margin-top', marginTop + 'px');
                marginTop += 30;
            })

            // hide field detail form
            $('.field_detail_form').hide()
            document.getElementById('history_detail').required = false;

            // hide view-button
            $('.view-button').hide();

        }

        // getting tooltip
        $('[data-toggle="tooltip"]').tooltip();

        // ajac setup for editing form
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ajax for getting data for produk1
        $(function() {
            $('#data-table').DataTable({
                "scrollY": 260,
                "scrollX": 400,
                "pageLength": 100,
                ajax: '{!! route('produk1.getproducts') !!}',
                @if ($level == 'sany')
                    columns: [{
                            data: 'id',
                            name: 'id',
                            visible: false
                        },
                        {
                            data: 'nama_produk',
                            name: 'nama_produk'
                            "fnCreatedCell": function(nTd, sData, oData, iRow, iCol) {
                            $(nTd).html("<a href='{{ route('produk1.index') }}/detail/" + oData
                                .id + "'>" + oData
                                .nama_produk + "</a>");
                            }
                        },
                        {
                            data: 'partnumber',
                            name: 'partnumber'
                        },
                        {
                            data: 'nama_merek',
                            name: 'nama_merek',
                            'defaultContent': '<i>Not set</i>'
                        },
                        {
                            data: 'totalstock',
                            name: 'totalstock'
                        },
                    ]
                @else
                    columns: [{
                            data: 'id',
                            name: 'id',
                            visible: false
                        },
                        {
                            data: 'nama_produk',
                            name: 'nama_produk',
                            "fnCreatedCell": function(nTd, sData, oData, iRow, iCol) {
                            $(nTd).html("<a href='{{ route('produk1.index') }}/detail/" + oData
                                .id + "'>" + oData
                                .nama_produk + "</a>");
                            }
                        },
                        {
                            data: 'tipe_produk',
                            name: 'tipe_produk',
                        },
                        {
                            data: 'partnumber',
                            name: 'partnumber',
                        },
                        {
                            data: 'kategoriproduk.nama_kategori',
                            name: 'kategoriproduk.nama_kategori',
                            searchable: false,
                        },
                        {
                            data: 'kode_unit',
                            name: 'kode_unit',
                            "defaultContent": '<i>Not set</i>'
                        },
                        {
                            data: 'merek.nama_merek',
                            name: 'merek.nama_merek',
                            "defaultContent": '<i>Not set</i>'
                        },
                        {
                            data: 'ukuran.nama_ukuran',
                            name: 'ukuran.nama_ukuran',
                            "defaultContent": '<i>Not set</i>'
                        },
                        {
                            data: 'satuan.nama_satuan',
                            name: 'satuan.nama_satuan',
                            searchable: false,
                        },
                        {
                            data: 'min_qty',
                            name: 'min_qty',
                        },
                        {
                            data: 'max_qty',
                            name: 'max_qty',
                            "defaultContent": '<i>Not set</i>'
                        },
                        {
                            data: 'stat',
                            name: 'stat',
                        },
                    ]
                @endif
            })
        })

        // refresh table using datatable
        function refreshTable() {
            $('#data-table').DataTable().ajax.reload(null, false);
            $('.sidenav-item').hide();
            $('.view-button').hide();
        }

        // auto caplocks
        function autoCaps(e) {
            e.value = e.value.toUpperCase();
        }

        // select2 form to make it more beautiful
        $('.select2').select2({
            placeholder: "  Pilih...",
            allowClear: true,
        });

        // to submit ADD form
        $('#ADD').submit(function(e) {
            e.preventDefault();
            const data = $('#ADD').serialize();
            $.ajax({
                url: '{{ route('produk1.store') }}',
                type: 'POST',
                data: data,
                success: function(data) {
                    $('#nama_produk').val('');
                    $('#tipe_produk').val('').trigger('change');
                    $('#kode_kategori').val('').trigger('change');
                    $('#kode_unit').val('').trigger('change');
                    $('#kode_merek').val('').trigger('change');
                    $('#kode_ukuran').val('').trigger('change');
                    $('#kode_satuan').val('').trigger('change');
                    $('#partnumber').val('');
                    $('#harga_beli').val('0');
                    $('#harga_jual').val('0');
                    $('#min_qty').val('0');
                    $('#max_qty').val('0');
                    $('#stat').val('').trigger('change');
                    $('#addform').modal('hide');
                    refreshTable();
                    if (data.success == true) {
                        swal(data.title, data.message, 'success');
                    } else {
                        swal(data.title, data.message, 'error');
                    }
                }
            })
        })

        // to submit Edit form
        $('#EDIT').submit(function(e) {
            e.preventDefault();
            const data = $('#EDIT').serialize();
            const kode_produk = $('#kode_produk_edit').val();
            $.ajax({
                url: '{{ route('produk1.index') }}/' + kode_produk,
                type: 'PUT',
                data: data,
                success: function(data) {
                    $('#editform').modal('hide');
                    refreshTable();
                    $('.sidenav-item').hide();

                    // hide view button
                    $('.view-button').hide();
                    if (data.success === true) {
                        swal('Berhasil', data.message, 'success');
                    } else {
                        swal('Gagal', data.message, 'error');
                    }
                }
            })
        })

        // when document is ready
        $(document).ready(function() {
            // getting selected table row
            const table = $('#data-table').DataTable();

            $('#data-table tbody').on('dblclick', 'tr', function() {
                // check if has selected bg-gray text-bold class
                if ($(this).hasClass('selected bg-gray text-bold')) {
                    // if it has, remove it
                    $(this).removeClass('selected bg-gray text-bold');
                    $('.sidenav-item').hide();
                    $('#created_by').val('');
                    $('#created_at').val('');
                    $('#updated_by').val('');
                    $('#updated_at').val('');

                    // hide view  button
                    $('.view-button').hide();
                    // closing all opened rows
                    closeOpenedRows(table, this);
                } else {
                    // if not, add it but first delete all the previous selected
                    table.$('tr.selected').removeClass('selected bg-gray text-bold');
                    $(this).addClass('selected bg-gray text-bold');

                    // get the data from selected row
                    const selectedRow = $('.selected').closest('tr');
                    const data = table.row(selectedRow).data();
                    $('.sidenav-item').show();
                    $('#created_by').val(data['created_by'])
                    $('#created_at').val(data['created_at'])
                    $('#updated_by').val(data['updated_by'])
                    $('#updated_at').val(data['updated_at'])

                    // show view button
                    $('.view-button').show();

                    // closing all opened rows
                    closeOpenedRows(table, selectedRow);
                }
            })
        })
        // show view produk
        $('#view-produk').click(function() {
            const rowId = getProdukId();
            $.ajax({
                url: '{{ route('produk1.index') }}/' + rowId,
                type: 'GET',
                success: function(data) {
                    $('#kode_produk_show').html(data.kode_produk);
                    $('#nama_produk_show').html(data.nama_produk);
                    $('#tipe_produk_show').html(data.tipe_produk);
                    $('#kategori_show').html(data.kode_kategori);
                    $('#unit_show').html(data.kode_unit);
                    $('#merek_show').html(data.kode_merek);
                    $('#ukuran_show').html(data.kode_ukuran);
                    $('#satuan_show').html(data.kode_satuan);
                    $('#partnumber_show').html(data.partnumber);
                    $('#stock_show').html(data.stok);
                    $('#beli_show').html(formatRupiah(data.harga_beli));
                    @permission('read-hpp')
                        $('#jual_show').html(formatRupiah(data.harga_jual));
                        $('#hpp_show').html(formatRupiah(data.hpp));
                    @endpermission
                    $('#company').html(data.kode_company);
                    $('#showform').modal('show');
                }
            })
        })

        // showform-edit -> edit produk
        // $('#showform-edit').click(function() {
        //     $('#editform').modal('show');
        // })

        // edit produk
        $('#showform-edit').click(function() {
            const rowId = getProdukId();
            $.ajax({
                url: '{{ route('produk1.index') }}/' + rowId + '/edit',
                type: 'GET',
                success: function(data) {
                    $('#kode_produk_edit').val(rowId);
                    $('#nama_produk_edit').val(data.nama_produk);
                    $('#tipe_produk_edit').val(data.tipe_produk).trigger('change');
                    $('#kode_kategori_edit').val(data.kode_kategori).trigger('change');
                    $('#kode_unit_edit').val(data.kode_unit).trigger('change');
                    $('#kode_merek_edit').val(data.kode_merek).trigger('change');
                    $('#kode_ukuran_edit').val(data.kode_ukuran).trigger('change');
                    $('#kode_satuan_edit').val(data.kode_satuan).trigger('change');
                    $('#partnumber_edit').val(data.partnumber);
                    $('#harga_beli_edit').val(data.harga_beli);
                    $('#harga_jual_edit').val(data.harga_jual);
                    $('#min_qty_edit').val(data.min_qty);
                    $('#max_qty_edit').val(data.max_qty);
                    $('#stat_edit').val(data.stat).trigger('change');
                    $('#editform').modal('show');
                }
            })
        })

        // Hapus produk
        $('#hapus-produk').click(function() {
            const rowId = getProdukId();
            swal({
                title: 'Hapus ?',
                text: 'Pastikan dahulu item yang akan dihapus',
                type: 'warning',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal!'
            }).then(function(e) {
                if (e.value === true) {
                    $.ajax({
                        url: '{{ route('produk1.index') }}/' + rowId,
                        type: 'DELETE',
                        success: function(data) {
                            if (data.success === true) {
                                swal('Berhasil', data.message, 'success');
                            } else {
                                swal('Gagal', data.message, 'error');
                            }
                            refreshTable();
                            $('.sidenav-item').hide();
                        }
                    })
                }
            })
        })

        // view stock button
        $('#view-stock').click(function() {
            // getting the row id of the produk
            const selectedRow = $('.selected').closest('tr');
            const row = $('#data-table').DataTable().row(selectedRow);

            $.ajax({
                url: '{{ route('produk1.index') }}/showstock/' + row.data()['id'],
                type: 'GET',
                success: function(data) {

                    // if fail
                    if (data.success == false) {
                        $.notify(data.message);
                    } else {
                        // if success

                        // check if the row child is shown
                        if (row.child.isShown()) {
                            row.child.hide();
                            selectedRow.removeClass('shown');
                        } else {
                            // closing all child rows
                            closeOpenedRows(row, selectedRow);

                            // if not show the child
                            row.child(formatShowStock(data)).show();
                            selectedRow.addClass('shown');

                            // adding to openrows
                            openRows.push(selectedRow);
                        }
                    }
                }
            })
        })

        // view history button
        $('#view-history').click(function() {
            // getting the row id of the produk
            const selectedRow = $('.selected').closest('tr');
            const row = $('#data-table').DataTable().row(selectedRow);

            $.ajax({
                url: '{{ route('produk1.index') }}/showhistory/' + row.data()['id'],
                type: 'GET',
                success: function(data) {

                    // if there is a row child
                    if (row.child.isShown()) {
                        row.child.hide();
                        selectedRow.removeClass('shown');
                    } else {
                        // closing all child rows
                        closeOpenedRows(row, selectedRow);

                        // if there is not row child, show it
                        row.child(formatShowHistory(data)).show();
                        selectedRow.addClass('shown');

                        // adding it to openedrow
                        openRows.push(selectedRow);
                    }
                }
            })
        })

        // view history button
        $('#view-monthly').click(function() {
            // getting produk id
            const rowId = getProdukId();
            $.ajax({
                url: '{{route('produk1.index')}}/showmonthly/' + rowId,
                type: 'GET',
                success: function(data) {
                    $('#periode_show_monthly').html(data.periode);
                    $('#partnumber_show_monthly').html(data.partnumber);
                    $('#no_mesin_show_mothly').html(data.no_mesin);
                    $('#begin_stock_show_mothly').html(data.begin_stock);
                    @permission('read-hpp')
                    $('#begin_amount_show_mothly').html(formatRupiah(data.begin_amount));
                    @endpermission
                    $('#in_stock_show_mothly').html(data.in_stock);
                    @permission('read-hpp')
                    $('#in_amount_show_mothly').html(formatRupiah(data.in_amount));
                    @endpermission
                    $('#out_stock_show_mothly').html(data.out_stock);
                    @permission('read-hpp')
                    $('#out_amount_show_mothly').html(formatRupiah(data.out_amount));
                    @endpermission
                    $('#sale_stock_show_mothly').html(data.sale_stock);
                    @permission('read-hpp')
                    $('#sale_amount_show_mothly').html(formatRupiah(data.sale_amount));
                    @endpermission
                    $('#trf_in_stock_show_mothly').html(data.trf_in);
                    @permission('read-hpp')
                    $('#trf_in_amount_show_mothly').html(formatRupiah(data.trf_in_amount));
                    @endpermission
                    $('#trf_out_stock_show_mothly').html(data.trf_out);
                    @permission('read-hpp')
                    $('#trf_out_amount_show_mothly').html(formatRupiah(data.trf_out_amount));
                    @endpermission
                    $('#adjustment_stock_show_mothly').html(data.adjustment_stock);
                    @permission('read-hpp')
                    $('#adjustment_amount_show_mothly').html(formatRupiah(data.adjustment_amount));
                    @endpermission
                    $('#opname_stock_show_mothly').html(data.stock_opname);
                    @permission('read-hpp')
                    $('#opname_amount_show_mothly').html(formatRupiah(data.amount_opname));
                    @endpermission
                    $('#retur_beli_stock_show_mothly').html(data.retur_beli_stock);
                    @permission('read-hpp')
                    $('#retur_beli_amount_show_mothly').html(formatRupiah(data.retur_beli_amount));
                    @endpermission
                    $('#retur_jual_stock_show_mothly').html(data.retur_jual_stock);
                    @permission('read-hpp')
                    $('#retur_jual_amount_show_mothly').html(formatRupiah(data.retur_jual_amount));
                    @endpermission
                    $('#disassembling_stock_show_mothly').html(data.disassembling_stock);
                    @permission('read-hpp')
                    $('#disassembling_amount_show_mothly').html(formatRupiah(data.disassembling_amount));
                    @endpermission
                    $('#assembling_stock_show_mothly').html(data.assembling_stock);
                    @permission('read-hpp')
                    $('#assembling_amount_show_mothly').html(formatRupiah(data.assembling_amount));
                    @endpermission
                    $('#ending_stock_show_mothly').html(data.ending_stock);
                    @permission('read-hpp')
                    $('#ending_amount_show_mothly').html(formatRupiah(data.ending_amount));
                    $('#hpp_show_mothly').html(formatRupiah(data.hpp));
                    @endpermission
                    $('#kode_lokasi_show_mothly').html(data.kode_lokasi);
                    $('#showmonthly').modal('show');
                }
            })
        })

        // closing the opened rows
        function closeOpenedRows(table, selectedRow) {
            $.each(openRows, function(index, openRow) {
                // not the selected row!
                if ($.data(selectedRow) !== $.data(openRow)) {
                    var rowToCollapse = table.row(openRow);
                    rowToCollapse.child.hide();
                    openRow.removeClass('shown');
                    // remove from list
                    var index = $.inArray(selectedRow, openRows);
                    openRows.splice(index, 1);
                }
            });
        }

        // formatting function for view history details
        function formatShowHistory(d) {
            // print show history if success 'true'
            let tableData = '';
            if (d.success == true) {
                tableData = '<td>' + d.tanggal_transaksi + '</td>';
                tableData += '<td>' + d.no_transaksi + '</td>';
                tableData += '<td>' + d.qty_transaksi + '</td>';
                @permission('read-hpp')
                    tableData += '<td>' + formatRupiah(d.total_transaksi, '') + '</td>';
                @endpermission
                tableData += '<td>' + d.created_by + '</td>';
            } else {
                // if no data
                tableData = '<td class="text-center" colspan="6">' + d.message + '</td>';
            }
            return (
                '<table class="table table-hover" style="font-size: 12px;">' +
                '<thead>' +
                '<tr class="bg-primary">' +
                '<th scope="col">Tanggal Transaksi</th>' +
                '<th scope="col">No Transaksi</th>' +
                '<th scope="col">Qty Transaksi</th>' +
                @permission('read-hpp')
                    '<th scope="col">Total</th>' +
                @endpermission
                '<th scope="col">Dibuat oleh</th>' +
                '</tr>' +
                '</thead>' +
                '<tbody>' +
                '<tr>' +
                tableData +
                '</tr>' +
                '</tbody>' +
                '<tfoot>' +
                '<tr class="bg-info">' +
                '<th colspan= "4">Transaksi Periode ' + d.periode + '</th>' +
                '<th></th>' +
                '</tr>' +
                '</tfoot>' +
                '</table>'
            );
        }
        // formatting function for view stock details
        function formatShowStock(d) {
            return (
                '<table class="table table-hover" style="font-size: 12px;">' +
                '<thead>' +
                '<tr class="bg-primary">' +
                '<th scope="col">Kode Produk</th>' +
                '<th scope="col">Partnumber</th>' +
                '<th scope="col">No Mesin</th>' +
                '<th scope="col">Kode lokasi</th>' +
                '<th scope="col">Stok</th>' +
                @permission('read-hpp')
                    '<th scope="col">Hpp</th>' +
                @endpermission
                '</tr>' +
                '</thead>' +
                '<tbody>' +
                '<tr>' +
                '<td>' + d.kode_produk + '</td>' +
                '<td>' + d.partnumber + '</td>' +
                '<td>' + d.no_mesin + '</td>' +
                '<td>' + d.kode_lokasi + '</td>' +
                '<td>' + d.ending_stock + '</td>' +
                @permission('read-hpp')
                    '<td>' + formatRupiah(d.hpp) + '</td>' +
                @endpermission
                '</tr>' +
                '</tbody>' +
                '<tfoot>' +
                '<tr class="bg-info">' +
                '<th colspan= "4">Total Stock</th>' +
                '<th>' + d.ending_stock + '</th>' +
                '<th></th>' +
                '</tr>' +
                '</tfoot>' +
                '</table>'
            );
        }

        // print list produk
        $('#print-list-produk').click(function(x) {
            const $this = $(this);
            x.target.innerHTML = '<i class="fa fa-print"></i> Loading...';
            $this.attr('disabled', true);
            setTimeout(() => {
                $this.attr('disabled', false);
                $this.html('<i class="fa fa-print"></i> Cetak List Produk')
            }, 1000);
        })

        // rupiah formatter
        function formatRupiah(angka, prefix = 'Rp ') {
            return prefix + angka.toLocaleString(undefined, {
                minimumFractionDigits: 0
            });
        }

        // getting row id
        function getProdukId() {
            const selectedRow = $('.selected').closest('tr');
            const rowId = $('#data-table').DataTable().row(selectedRow).data()['id'];
            return rowId;
        }

        // function pilih history
        function pilihHistory() {
            const detailHistory = $('#history_detail').val();
            if (detailHistory == 'Monthly') {
                $('.field_detail_form').show()
                document.getElementById('history_detail').required = true;
            } else {
                $('.field_detail_form').hide()
                document.getElementById('history_detail').required = false;
            }
        }
    </script>
@endpush
