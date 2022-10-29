@extends('adminlte::page')

@section('title', 'Pembelian')

@section('content_header')
    
@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <link rel="icon" type="image/png" href="/gui_inventory_laravel/css/logo_gui.png" sizes="16x16">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
@include('sweet::alert')
<style>
    #canvasDiv{
        position: relative;
        border: 2px solid grey;
        height:300px;
        width: 550px;
    }
    
    @media only screen and (max-width: 640px) {
      #canvasDiv {
        position: relative;
        border: 2px solid grey;
        height:275px;
        width: 350px;
      }
    }
</style>
<body onLoad="load()">
    <div class="box box-solid">
        <div class="box-body">
            <div class="box">
                <div class="box-body">
                    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()" >
                            <i class="fa fa-refresh"></i> Refresh</button>
                    @permission('create-pembelian')
                    <button type="button" class="btn btn-danger btn-xs" id="new-button" data-toggle="modal" data-target="#addform"><i class="fa fa-plus"></i> New Pembelian</button>
                    <i>&nbsp;ALT+1 = New Pembelian (shortcut)</i>
                    @endpermission

                    @permission('post-getkode')
                    <button type="button" class="btn btn-primary btn-xs" onclick="getkode()">
                        <i class="fa fa-bullhorn"></i> Get New Kode</button>
                    @endpermission

                    <span class="pull-right"> 
                    <?php if (stripos($_SERVER['HTTP_USER_AGENT'], 'Windows') === FALSE){ ?>
                        <button type="button" class="btn bg-orange btn-xs preview-button" data-toggle="modal" data-target="#previewpo"><i class="fa fa-print"></i> Preview PO</button>
                        <button type="button" class="btn bg-black btn-xs ttdigi-button" id="addttd" data-toggle="modal" data-target="#ttdform"><i class="fa fa-edit"></i> TTD DIGITAL</button>
                    <?php } ?>
                        <font style="font-size: 16px;"><b>PEMBELIAN</b></font>
                    </span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data-table" style="font-size: 12px; width: 1200px;">
                    <thead>
                    <tr class="bg-primary">
                        <th>No Pembelian</th>
                        <th>Tanggal Pembelian</th>
                        <th style="width: 230px;">Vendor</th>
                        <th style="width: 70px;">Jenis PO</th>
                        <th>No Request</th>
                        <th>Total Item</th>
                        <th>Diskon(%)</th>
                        <th>Diskon(Rp)</th>
                        <th>PPN</th>
                        <th>PBBKB(%)</th>
                        <th>PBBKB(Rp)</th>
                        <th>Ongkos Angkut</th>
                        <th>Grand Total</th>
                        <th>No AP</th>
                        <th style="width: 150px;">Deskripsi</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                    </thead>
                </table>
            </div>
            <div class="col-md-12">
            </div>
            <div class="col-sm-2">
                {{ Form::label('texx', 'Created By:') }}
                {{ Form::text('created_by', null, ['class'=> 'form-control','id'=>'CreateBy1','readonly']) }}
            </div>
            <div class="col-md-2">
                {{ Form::label('texx', 'Created At:') }}
                {{ Form::text('created_at', null, ['class'=> 'form-control','id'=>'CreateAt1','readonly']) }}
            </div>
            <div class="col-md-2">
                {{ Form::label('texx', 'Updated By:') }}
                {{ Form::text('updated_by', null, ['class'=> 'form-control','id'=>'UpdateBy1','readonly']) }}
            </div>
            <div class="col-md-2">
                {{ Form::label('texx', 'Updated At:') }}
                {{ Form::text('updated_at', null, ['class'=> 'form-control','id'=>'UpdateAt1','readonly']) }}
            </div>
            <div class="col-md-2">
                {{ Form::label('texx', 'Posted By:') }}
                {{ Form::text('posted_by', null, ['class'=> 'form-control','id'=>'PostedBy1','readonly']) }}
            </div>
            <div class="col-md-2">
                {{ Form::label('texx', 'Unpost By:') }}
                {{ Form::text('unpost_by', null, ['class'=> 'form-control','id'=>'UnpostBy1','readonly']) }}
            </div>
        </div>
    </div>
    
{{ Form::hidden('nama_user', Auth()->user()->name, ['class'=> 'form-control','style'=>'width: 100%','id'=>'NamaUser1','readonly']) }}

{{ Form::hidden('n1', null, ['class'=> 'form-control','id'=>'nama','readonly']) }}
{{ Form::hidden('n1x', null, ['class'=> 'form-control','id'=>'nama2','readonly']) }}
{{ Form::hidden('n1z', null, ['class'=> 'form-control','id'=>'nama3','readonly']) }}

{{ Form::hidden('n2', null, ['class'=> 'form-control','id'=>'namara','readonly']) }}
{{ Form::hidden('n2x', null, ['class'=> 'form-control','id'=>'namara2','readonly']) }}
{{ Form::hidden('n2z', null, ['class'=> 'form-control','id'=>'namara3','readonly']) }}

{{ Form::hidden('n3', null, ['class'=> 'form-control','id'=>'namaga','readonly']) }}
{{ Form::hidden('n3x', null, ['class'=> 'form-control','id'=>'namaga2','readonly']) }}
{{ Form::hidden('n3z', null, ['class'=> 'form-control','id'=>'namaga3','readonly']) }}

{{ Form::hidden('n4', null, ['class'=> 'form-control','id'=>'grand1','readonly']) }}
{{ Form::hidden('n5', null, ['class'=> 'form-control','id'=>'grand2','readonly']) }}
{{ Form::hidden('n6', null, ['class'=> 'form-control','id'=>'grandara1','readonly']) }}
{{ Form::hidden('n7', null, ['class'=> 'form-control','id'=>'grandara2','readonly']) }}
{{ Form::hidden('n8', null, ['class'=> 'form-control','id'=>'grandaga1','readonly']) }}
{{ Form::hidden('n9', null, ['class'=> 'form-control','id'=>'grandaga2','readonly']) }}
    
<div class="modal fade" id="ttdform" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <br>
                        <h2>TTD Digital: {{ Form::label('nomoor', null,['id'=>'NomorTTD']) }}</h2>
                        <hr>
                        <div id="canvasDiv"></div>
                        <br>
                        <button type="button" class="btn btn-danger" id="reset-btn">Clear</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <button type="button" class="btn bg-blue" id="btn-save">Simpan (Dibuat Oleh)</button>
                        &nbsp;&nbsp;
                        <button type="button" class="btn bg-green" id="btn-periksa">Simpan (Diperiksa Oleh)</button>
                        <br><br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" class="btn bg-yellow" id="btn-setuju">Simpan (Disetujui Oleh)</button>
                        &nbsp;&nbsp;
                        <button type="button" class="btn bg-purple" id="btn-tahu">Simpan (Diketahui Oleh)</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="previewpo" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" style="text-align: center;">Preview Detail PO</h4>
            </div>
            <div class="modal-body">
                <div class="row" style="font-size: 8pt;">
                    <b>
                    <div class="col-md-8">
                        <div class="form-group">
                            Vendor : {{ Form::label('nomoor', null,['id'=>'PreviewVendor']) }}<br>
                            Alamat : {{ Form::label('nomoor', null,['id'=>'PreviewAlamat']) }}<br>
                            Kontak : {{ Form::label('nomoor', null,['id'=>'PreviewKontak']) }}<br>
                            NPWP&nbsp;&nbsp;&nbsp;: {{ Form::label('nomoor', null,['id'=>'PreviewNpwp']) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            No. PO : {{ Form::label('nomoor', null,['id'=>'PreviewNomor']) }}<br>
                            Tgl. PO : {{ Form::label('nomoor', null,['id'=>'PreviewTanggal']) }}<br>
                            No. Penawaran : {{ Form::label('nomoor', null,['id'=>'PreviewPenawaran']) }}
                        </div>
                    </div>
                    </b>
                </div>
            </div>
            <div class="container-fluid table-responsive">
                <table class="table table-bordered table-striped table-hover" id="preview-table" width="100%" style="font-size: 12px;">
                    <thead>
                        <tr class="bg-warning">
                            <th>Produk</th>
                            <th>Keterangan</th>
                            <th>Satuan</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                </table>
                <div class="modal-footer">
                    <div class="col-md-6">
                        <span class="pull-left">
                            Note: {{ Form::label('0', null,['id'=>'PreviewNote']) }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right">
                            <table width="100%" style="font-size:10pt; font-weight: bold; text-align:right;padding:0px; margin:0px; border-collapse:collapse" border="0">
                                <tr>
                                    <td>Subtotal</td>
                                    <td>&nbsp;:&nbsp;</td>
                                    <td>{{ Form::label('0', null,['id'=>'PreviewSubtotal']) }}</td>
                                </tr>
                                <tr>
                                    <td>Diskon</td>
                                    <td>&nbsp;:&nbsp;</td>
                                    <td>{{ Form::label('0', null,['id'=>'PreviewDiskon']) }}</td>
                                </tr>
                                <tr>
                                    <td>PPN</td>
                                    <td>&nbsp;:&nbsp;</td>
                                    <td>{{ Form::label('0', null,['id'=>'PreviewPPN']) }}</td>
                                </tr>
                            <?php if (Auth()->user()->kode_company == '02') { ?>
                                <tr>
                                    <td>PBBKB</td>
                                    <td>&nbsp;:&nbsp;</td>
                                    <td>{{ Form::label('0', null,['id'=>'PreviewPBBKB']) }}</td>
                                </tr>
                            <?php } ?>
                                <tr>
                                    <td>Grand Total</td>
                                    <td>&nbsp;:&nbsp;</td>
                                    <td>{{ Form::label('0', null,['id'=>'PreviewGrandtotal']) }}</td>
                                </tr>
                            </table>
                        </span>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="addform" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Create Data <i>&nbsp;ENTER = Simpan</i></h4>
            </div>
            @include('errors.validation')
            {!! Form::open(['id'=>'ADD']) !!}
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('Tanggal Pembelian', 'Tanggal Pembelian:') }}
                                    {{ Form::date('tanggal_pembelian', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'Tanggal1' ,'required'=>'required'])}}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('jenis_po', 'Jenis PO:') }}
                                    {{ Form::select('jenis_po', ['Stock' => 'Stock','Jasa' => 'Jasa','Non-Stock' => 'Non-Stock'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Jenis1','required'=>'required','onchange'=>"getnama();"])}}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('No Request', 'No Request:') }}
                                    {{ Form::select('no_request', $Norequest->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Noreq1']) }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('Kode Vendor', 'Vendor:') }}
                                    {{ Form::select('kode_vendor',$Vendor->sort(),null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Vendor1','required'=>'required','onchange'=>"get_ppn();"]) }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('No Penawaran', 'No Ref. Penawaran:') }}
                                    {{ Form::text('no_penawaran', null, ['class'=> 'form-control','id'=>'Ref1', 'placeholder'=>'No. Penawaran', 'autocomplete'=>'off','required']) }}
                                 </div>
                            </div> 

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('TOP', 'TOP:') }}
                                    {{ Form::text('top', null, ['class'=> 'form-control','id'=>'TOP1','onchange'=>"hitung();",'required'=>'required', 'placeholder'=>'Term Of Payment', 'autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                                 </div>
                            </div> 

                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('Due Date', 'Due Date:') }}
                                    {{ Form::text('due_date', null, ['class'=> 'form-control','id'=>'Due1','required'=>'required', 'placeholder'=>'Tanggal Tenggat','readonly']) }}
                                 </div>
                            </div> 

                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('Diskon', 'Diskon(%):') }}
                                    {{ Form::text('diskon_persen', 0, ['class'=> 'form-control','id'=>'Diskon1','placeholder'=>'%', 'autocomplete'=>'off','required','onkeyup'=>'cekdiskon()']) }}
                                 </div>
                            </div> 

                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('Diskons', 'Diskon(Rp):') }}
                                    {{ Form::text('diskon_rp', 0, ['class'=> 'form-control','id'=>'Diskonrp1','placeholder'=>'Rp.', 'autocomplete'=>'off','required','onkeyup'=>'cekdiskon2()']) }}
                                 </div>
                            </div> 

                             <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('PPN', 'PPN(%):') }}
                                    {{ Form::text('ppn', 0, ['class'=> 'form-control','id'=>'PPN1','placeholder'=>'%', 'autocomplete'=>'off','required']) }}
                                 </div>
                            </div> 

                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('pbbkb', 'PBBKB(%):') }}
                                    {{ Form::text('pbbkb', 0, ['class'=> 'form-control','id'=>'pbbkb1', 'autocomplete'=>'off','required','onkeyup'=>'cekdiskon3()']) }}
                                </div>
                            </div> 

                            <!--<div class="col-md-3">-->
                            <!--    <div class="form-group">-->
                            <!--        {{ Form::label('pbbkb_rp', 'PBBKB(Rp):') }}-->
                            <!--        {{ Form::text('pbbkb_rp', 0, ['class'=> 'form-control','id'=>'pbbkbrp1', 'autocomplete'=>'off','required','onkeyup'=>'cekdiskon4()']) }}-->
                            <!--    </div>-->
                            <!--</div> -->

                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('ongkos_angkut', 'Ongkos Angkut:') }}
                                    {{ Form::text('ongkos_angkut', 0, ['class'=> 'form-control','id'=>'ongkosangkut1', 'autocomplete'=>'off','required']) }}
                                </div>
                            </div> 
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('No Alat', 'No Alat:') }}
                                    {{ Form::text('no_alat', null, ['class'=> 'form-control','id'=>'Alat1','autocomplete'=>'off','readonly']) }}
                                 </div>
                            </div> 
                            
                            <?php if (Auth()->user()->kode_company == '03' || Auth()->user()->kode_company == '04' || Auth()->user()->kode_company == '0401'){ ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('Costor', 'Cost Center:') }}
                                        {{ Form::select('cost_center',$Costcenter->sort(),null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Cost1']) }}
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('Deskripsi', 'Deskripsi:') }}
                                    {{ Form::textarea('deskripsi', null, ['class'=> 'form-control','rows'=>'2','id'=>'Deskripsi1', 'placeholder'=>'Deskripsi', 'autocomplete'=>'off', 'required','onkeypress'=>"return pulsar(event,this)"]) }}
                                 </div>
                            </div> 

                            <!--<div class="col-md-12">-->
                            <!--    <div class="form-group">-->
                            <!--        {{ Form::label('keterangan_cetak', 'Keterangan Detail PO (isi apabila total item PO tidak cukup 1 halaman):') }}-->
                            <!--        {{ Form::textarea('keterangan_cetak', null, ['class'=> 'form-control','rows'=>'2','id'=>'Keterangan1', 'placeholder'=>'Keterangan', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}-->
                            <!--     </div>-->
                            <!--</div> -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            {{ Form::submit('Create data', ['class' => 'btn btn-success crud-submit','id'=>'create-button']) }}
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

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('No Pembelian', 'No Pembelian:') }}
                                        {{ Form::text('no_pembelian', null, ['class'=> 'form-control','id'=>'Pembelian','readonly']) }}
                                    </div>
                                </div> 

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('Tanggal Pembelian', 'Tanggal Pembelian:') }}
                                        {{ Form::date('tanggal_pembelian', null,['class'=> 'form-control','id'=>'Tanggal'])}}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('jenis_po', 'Jenis PO:') }}
                                        {{ Form::select('jenis_po', ['Stock' => 'Stock','Jasa' => 'Jasa','Non-Stock' => 'Non-Stock'], null, ['class'=> 'form-control select2','style'=>'width: 100%','id'=>'Jenis'])}}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('Kode Vendor', 'Vendor:') }}
                                        {{ Form::select('kode_vendor',$Vendor,null, ['class'=> 'form-control select2','style'=>'width: 100%','id'=>'Vendor','onchange'=>'get_ppn2()']) }}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('No Penawaran', 'No Ref. Penawaran:') }}
                                        {{ Form::text('no_penawaran', null, ['class'=> 'form-control','id'=>'Ref', 'autocomplete'=>'off','required']) }}
                                     </div>
                                </div> 

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('TOP', 'TOP:') }}
                                        {{ Form::text('top', null, ['class'=> 'form-control','id'=>'TOP','onchange'=>"hitung2();", 'autocomplete'=>'off']) }}
                                     </div>
                                </div> 

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('Due Date', 'Due Date:') }}
                                        {{ Form::text('due_date', null, ['class'=> 'form-control','id'=>'Due','readonly']) }}
                                     </div>
                                </div> 

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('Diskon', 'Diskon(%):') }}
                                        {{ Form::text('diskon_persen', null, ['class'=> 'form-control','id'=>'Diskon', 'autocomplete'=>'off','required','onkeyup'=>'cekdiskone()']) }}
                                     </div>
                                </div> 

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('Diskons', 'Diskon(Rp):') }}
                                        {{ Form::text('diskon_rp', null, ['class'=> 'form-control','id'=>'Diskonrp', 'autocomplete'=>'off','required','onkeyup'=>'cekdiskon2e()']) }}
                                     </div>
                                </div> 

                                 <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('PPN', 'PPN(%):') }}
                                        {{ Form::text('ppn', null, ['class'=> 'form-control','id'=>'PPN', 'autocomplete'=>'off','required']) }}
                                     </div>
                                </div> 

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('pbbkb', 'PBBKB(%):') }}
                                        {{ Form::text('pbbkb', null, ['class'=> 'form-control','id'=>'pbbkb', 'autocomplete'=>'off','required','onkeyup'=>'cekdiskon3e()']) }}
                                     </div>
                                </div> 

                                <!--<div class="col-md-3">-->
                                <!--    <div class="form-group">-->
                                <!--        {{ Form::label('pbbkb_rp', 'PBBKB(Rp):') }}-->
                                <!--        {{ Form::text('pbbkb_rp', null, ['class'=> 'form-control','id'=>'pbbkbrp', 'autocomplete'=>'off','required','onkeyup'=>'cekdiskon4e()']) }}-->
                                <!--    </div>-->
                                <!--</div> -->

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('ongkos_angkut', 'Ongkos Angkut:') }}
                                        {{ Form::text('ongkos_angkut', null, ['class'=> 'form-control','id'=>'ongkosangkut', 'autocomplete'=>'off','required']) }}
                                     </div>
                                </div> 
                                
                            <?php if (Auth()->user()->kode_company == '03' || Auth()->user()->kode_company == '04' || Auth()->user()->kode_company == '0401'){ ?>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('Costor', 'Cost Center:') }}
                                        {{ Form::select('cost_center',$Costcenter->sort(),null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Cost2']) }}
                                    </div>
                                </div>
                            <?php } ?>
                            
                            <?php if (auth()->user()->level == 'ap') { ?>
                                <div class="col-md-7">
                                    <div class="form-group">
                                        {{ Form::label('no_ap', 'No AP:') }}
                                        {{ Form::text('no_ap', null, ['class'=> 'form-control','id'=>'Ap','onkeypress'=>"return pulsar(event,this)"]) }}
                                    </div>
                                </div>
                            <?php } ?>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{ Form::label('Deskripsi', 'Deskripsi:') }}
                                        {{ Form::textarea('deskripsi', null, ['class'=> 'form-control','rows'=>'4','id'=>'Deskripsi', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                     </div>
                                </div> 

                                <!--<div class="col-md-12">-->
                                <!--    <div class="form-group">-->
                                <!--        {{ Form::label('keterangan_cetak', 'Keterangan Detail PO (isi apabila total item PO tidak cukup 1 halaman):') }}-->
                                <!--        {{ Form::textarea('keterangan_cetak', null, ['class'=> 'form-control','rows'=>'2','id'=>'Keterangan', 'placeholder'=>'Keterangan', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}-->
                                <!--     </div>-->
                                <!--</div> -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="row">
                                {{ Form::submit('Update data', ['class' => 'btn btn-success crud-submit']) }}
                                {{ Form::button('Close', ['class' => 'btn btn-danger','data-dismiss'=>'modal']) }}&nbsp;
                            </div>
                        </div>
                    {!! Form::close() !!}
              </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!--<button type="button" class="back2Top btn btn-warning btn-xs" id="back2Top"><i class="fa fa-arrow-up" style="color: #fff"></i> <i>{{ $nama_company }}</i> <b>({{ $nama_lokasi }})</b></button>-->

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

            /* Button used to open the contact form - fixed at the bottom of the page */
            .add-button {
                background-color: #00E0FF;
                bottom: 56px;
            }

            .hapus-button {
                background-color: #F63F3F;
                bottom: 86px;
            }

            .edit-button {
                background-color: #FDA900;
                bottom: 116px;
            }

            .tombol1 {
                background-color: #149933;
                bottom: 156px;
            }

            .tombol2 {
                background-color: #ff9900;
                bottom: 156px;
            }

            .view-button {
                background-color: #1674c7;
                bottom: 186px;
            }

            .tombol7 {
                background-color: #149933;
                bottom: 156px;
            }

            .tombol8 {
                background-color: #ff9900;
                bottom: 156px;
            }

            .print-button {
                background-color: #F63F3F;
                bottom: 216px;
            }
            
            .void-button {
                bottom: 246px;
            }
            
            /*.ttdigi-button {*/
            /*    bottom: 276px;*/
            /*}*/

            #mySidenav button {
              position: fixed;
              right: -60px;
              transition: 0.3s;
              padding: 4px 8px;
              width: 110px;
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
            @permission('update-pembelian')
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editpembelian" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>
            @endpermission

            @permission('delete-pembelian')
            <button type="button" class="btn bg-purple btn-xs void-button" id="voidpembelian" data-toggle="modal" data-target="">VOID <i class="fa fa-times-circle"></i></button>
            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapuspembelian" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>
            @endpermission

            @permission('add-pembelian')
            <a href="#" id="addpembelian"><button type="button" class="btn btn-info btn-xs add-button" data-toggle="modal" data-target="">ADD <i class="fa fa-plus"></i></button></a>
            @endpermission

            @permission('post-pembelian')
            <button type="button" class="btn btn-success btn-xs tombol1" id="button1">POST <i class="fa fa-bullhorn"></i></button>
            @endpermission

            @permission('unpost-pembelian')
            <button type="button" class="btn btn-warning btn-xs tombol2" id="button2">UNPOST <i class="fa fa-undo"></i></button>
            @endpermission

            @permission('view-pembelian')
            <button type="button" class="btn btn-primary btn-xs view-button" id="button5">VIEW <i class="fa fa-eye"></i></button>
            @endpermission

            @permission('approve-pembelian')
            <button type="button" class="btn btn-success btn-xs tombol7" id="button7">APPROVE <i class="fa fa-check"></i></button>
            @endpermission

            @permission('approve-pembelian')
            <button type="button" class="btn btn-warning btn-xs tombol8" id="button8">DISAPPROVE <i class="fa fa-check"></i></button>
            @endpermission

            @permission('print-pembelian')
            <a href="#" target="_blank" id="printpembelian"><button type="button" class="btn btn-danger btn-xs print-button" id="button6">PRINT <i class="fa fa-print"></i></button></a>
            
            @endpermission
        </div>
</body>
@stop

@push('css')

@endpush
@push('js')
  
    <script type="text/javascript">
        $(window).scroll(function() {
            var height = $(window).scrollTop();
            if (height > 1) {
                $('#back2Top').show();
            } else {
                $('#back2Top').show();
            }
        });
        
        var x = 0;
        function showTime(){
            x = x + 1;
            if (x >= 60000){
                x = 0;
                refreshTable();
            }
        }
        // setInterval(showTime, 1);
        
        document.onkeyup = function () {
              var e = e || window.event; // for IE to cover IEs window event-object
              if(e.altKey && e.which == 49) {
                $("#new-button").click();
              }
              
              if(e.which == 13) {
                $("#create-button").click();
              }
        }

        function load(){
            limiter();
            startTime();
            $('.tombol1').hide();
            $('.tombol2').hide();
            $('.tombol7').hide();
            $('.tombol8').hide();
            $('.add-button').hide();
            $('.hapus-button').hide();
            $('.edit-button').hide();
            $('.preview-button').hide();
            $('.print-button').hide();
            $('.ttdigi-button').hide();
            $('.view-button').hide();
            $('.void-button').hide();
            $('.back2Top').show();
        }
        
        function getnama(){
            var jenis = $("#Jenis1").val();
            if (jenis == 'Non-Stock') {
                document.getElementById("Alat1").readOnly = false;
            }else {
                document.getElementById("Alat1").readOnly = true;
            }
        }

        $(function() {
            $("#data-table").DataTable({
                "bPaginate": true,
                "bFilter": true,
                "scrollY": 280,
                "scrollX": 400,
                "pageLength":100,
                "order": [[16, "desc"]],
                ajax: '{!! route('pembelian.data') !!}',
                data:[],
                fnRowCallback: function (row, data, iDisplayIndex, iDisplayIndexFull) {
                    if (data['status'] == "OPEN") {
                        $('td', row).css('background-color', '#ffdbd3');
                    }
                },

                columns: [
                    { data: 'no_pembelian', 
                        render: function( data, type, full ) {
                        return formatNomor(data); }
                    },
                    { data: 'tanggal_pembelian', name: 'tanggal_pembelian' },
                    { data: 'nama_vendor', name: 'nama_vendor' },
                    { data: 'jenis_po', name: 'jenis_po' },
                    { data: 'no_request', name: 'no_request' },
                    { data: 'total_item', name: 'total_item' },
                    { data: 'diskon_persen', name: 'diskon_persen' },
                    { data: 'diskon_rp', 
                        render: function( data, type, full ) {
                        return formatNumber(data); }
                    },
                    { data: 'ppn', name: 'ppn' },
                    { data: 'pbbkb', name: 'pbbkb' },
                    { data: 'pbbkb_rp', 
                        render: function( data, type, full ) {
                        return formatNumber(data); }
                    },
                    { data: 'ongkos_angkut', 
                        render: function( data, type, full ) {
                        return formatNumber(data); }
                    },
                    { data: 'grand_total', 
                        render: function( data, type, full ) {
                        return formatNumber(data); }
                    },
                    { data: 'no_ap', name: 'no_ap' },
                    { data: 'deskripsi', name: 'deskripsi' },
                    { data: 'status', 
                        render: function( data, type, full ) {
                        return formatStatus(data); }
                    },
                    { data: 'created_at', name: 'created_at', visible: false },
                ]
            });
        });
        
        TablePreview = $("#preview-table").DataTable({
            "bPaginate": false,
            "bInfo": false,
            "bFilter": false,
            data:[],
            columns: [
                { data: 'kode_produk', name: 'kode_produk' },
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
            ],
        });
        
        function tablepreview(kode){
            $.ajax({
                url: '{!! route('pembelian.getDatapreview') !!}',
                type: 'GET',
                data : {
                    'id': kode
                },
                success: function(result) {
                    TablePreview.clear().draw();
                    TablePreview.rows.add(result).draw();
                }
            });
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
                        url:'{!! route('pembelian.getkode') !!}',
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
        
        function limiter() {
            $.ajax({
                url: '{!! route('pembelian.limitos') !!}',
                type: 'GET',
                data : {
                },
                success: function(results) {
                    $('#nama').val(results.nama);
                    $('#nama2').val(results.nama2);
                    $('#nama3').val(results.nama3);
                    $('#grand1').val(results.grand1);
                    $('#grand2').val(results.grand2);
                    $('#namara').val(results.namara);
                    $('#namara2').val(results.namara2);
                    $('#namara3').val(results.namara3);
                    $('#grandara1').val(results.grandara1);
                    $('#grandara2').val(results.grandara2);
                    $('#namaga').val(results.namaga);
                    $('#namaga2').val(results.namaga2);
                    $('#namaga3').val(results.namaga3);
                    $('#grandaga1').val(results.grandaga1);
                    $('#grandaga2').val(results.grandaga2);
                }
            });
        }

        function formatNumber(m) {
            if(m == null){
                return '';
            }else{
                return m.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
        }

        function formatStatus(n) {
            if(n == 'OPEN'){
                return n;
            }else if(n == 'POSTED'){
                var stat = "<span style='color:#0eab25'><b>POSTED</b></span>";
                return n.replace(/POSTED/, stat);
            }else if(n == 'CLOSED'){
                var stat = "<span style='color:#c91a1a'><b>CLOSED</b></span>";
                return n.replace(/CLOSED/, stat);
            }else if(n == 'APPROVED'){
                var stat = "<span style='color:#FF5733'><b>APPROVED</b></span>";
                return n.replace(/APPROVED/, stat);
            }else if(n == 'INVOICED'){
                var stat = "<span style='color:#2a59a3'><b>INVOICED</b></span>";
                return n.replace(/INVOICED/, stat);
            }else if(n == 'VOID'){
                var stat = "<span style='color:#9439e3'><b>VOID</b></span>";
                return n.replace(/VOID/, stat);
            }else{
                var stat = "<span style='color:#1a80c9'><b>RECEIVED</b></span>";
                return n.replace(/RECEIVED/, stat);
            }
        }

        function formatNomor(n) {
            var res = n.substr(2, 4);
            if(res != 'POGA'){
                return n;
            }else{
                var str = n;
                var result = str.fontcolor( "#0eab25" );
                return result;
            }
        }

        function formatRupiah(angka, prefix='Rp'){
           
            var rupiah = angka.toLocaleString(
                undefined, // leave undefined to use the browser's locale,
                // or use a string like 'en-US' to override it.
                { minimumFractionDigits: 0 }
            );
            return rupiah;
           
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

        function createTable(result){

        var total_qty = 0;
        var total_qty_received = 0;
        var total_pakai = 0;
        var total_harga = 0;
        var grand_total = 0;

        $.each( result, function( key, row ) {
            total_qty += row.qty;
            total_qty_received += row.qty_received;
            harga = row.harga;
            qty = row.qty;
            total_pakai = harga * qty;
            total_harga += total_pakai;
            grand_total = formatRupiah(Math.round(total_harga));

        });

        var my_table = "";

        $.each( result, function( key, row ) {
                    my_table += "<tr>";
                    my_table += "<td>"+row.produk+"</td>";
                    my_table += "<td>"+row.keterangan+"</td>";
                    my_table += "<td>"+row.satuan+"</td>";
                    my_table += "<td>"+row.qty+"</td>";
                    my_table += "<td>Rp "+formatRupiah(row.harga)+"</td>";
                    my_table += "<td>Rp "+row.subtotal+"</td>";
                    my_table += "<td>"+row.qty_received+"</td>";
                    my_table += "</tr>";
            });

            my_table = '<table id="table-fixed" class="table table-bordered table-hover" cellpadding="5" cellspacing="0" border="1" style="padding-left:50px; font-size:12px">'+ 
                        '<thead>'+
                           ' <tr class="bg-info">'+
                                '<th>Produk</th>'+
                                '<th>Keterangan</th>'+
                                '<th>Satuan</th>'+
                                '<th>Qty</th>'+
                                '<th>Harga Satuan</th>'+
                                '<th>Subtotal</th>'+
                                '<th>Qty Received</th>'+
                            '</tr>'+
                        '</thead>'+
                        '<tbody>' + my_table + '</tbody>'+
                       ' <tfoot>'+
                            '<tr class="bg-info">'+
                                '<th class="text-center" colspan="3">Total</th>'+
                                '<th></th>'+
                                '<th></th>'+
                                '<th>Rp '+grand_total+'</th>'+
                                '<th></th>'+
                            '</tr>'+
                            '</tfoot>'+
                        '</table>';

                    // $(document).append(my_table);
            
            return my_table;
            // mytable.appendTo("#box");           
        
        }

        $(document).ready(function() {
            var table = $('#data-table').DataTable();
            var post = document.getElementById("button1");
            var unpost = document.getElementById("button2");

            $('#data-table tbody').on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected bg-gray text-bold') ) {
                    $(this).removeClass('selected bg-gray text-bold');
                    $('.tombol1').hide();
                    $('.tombol2').hide();
                    $('.tombol7').hide();
                    $('.tombol8').hide();
                    $('.add-button').hide();
                    $('.hapus-button').hide();
                    $('.edit-button').hide();
                    $('.print-button').hide();
                    $('.preview-button').hide();
                    $('.ttdigi-button').hide();
                    $('.view-button').hide();
                    $('.void-button').hide();
                    
                    $('#CreateBy1').val('');
                    $('#CreateAt1').val('');
                    $('#UpdateBy1').val('');
                    $('#UpdateAt1').val('');
                    $('#PostedBy1').val('');
                    $('#UnpostBy1').val('');
                }
                else {
                    table.$('tr.selected').removeClass('selected bg-gray text-bold');
                    $(this).addClass('selected bg-gray text-bold');
                    var select = $('.selected').closest('tr');
                    
                    closeOpenedRows(table, select);
                    
                    var data = $('#data-table').DataTable().row(select).data();
                    var grand_total = data['grand_total'];
                    var status = data['status'];
                    var item = data['total_item'];
                    var no_pembelian = data['no_pembelian'];
                    
                    var add = $("#addpembelian").attr("href",window.location.href+"/"+no_pembelian+"/detail");
                    var print = $("#printpembelian").attr("href",window.location.href+"/exportpdf?no_pembelian="+no_pembelian);
                    // var print = $("#printpreview").attr("href",window.location.href+"/printpreview?no_pembelian="+no_pembelian);

                    document.getElementById("NomorTTD").innerHTML = no_pembelian;
                    
                    $.ajax({
                        url: '{!! route('pembelian.previewpo') !!}',
                        type: 'GET',
                        data : {
                            'id': no_pembelian
                        },
                        success: function(result) {
                            document.getElementById("PreviewVendor").innerHTML = result.vendor;
                            document.getElementById("PreviewNomor").innerHTML = result.no_po;
                            document.getElementById("PreviewAlamat").innerHTML = result.alamat;
                            document.getElementById("PreviewTanggal").innerHTML = result.tgl_po;
                            document.getElementById("PreviewKontak").innerHTML = result.kontak;
                            document.getElementById("PreviewPenawaran").innerHTML = result.no_penawaran;
                            document.getElementById("PreviewNpwp").innerHTML = result.npwp;

                            document.getElementById("PreviewSubtotal").innerHTML = result.subtotal;
                            document.getElementById("PreviewDiskon").innerHTML = result.diskon;
                            document.getElementById("PreviewPPN").innerHTML = result.ppn;

                            if (result.kode_company == '02') {
                                document.getElementById("PreviewPBBKB").innerHTML = result.pbbkb;
                            }
                            
                            document.getElementById("PreviewGrandtotal").innerHTML = result.grand_total;
                            document.getElementById("PreviewNote").innerHTML = result.note;

                            tablepreview(no_pembelian);
                        }
                    });
                    
                    $('#CreateBy1').val(data['created_by']);
                    $('#CreateAt1').val(data['created_at']);
                    $('#UpdateBy1').val(data['updated_by']);
                    $('#UpdateAt1').val(data['updated_at']);
                    $.ajax({
                        url: '{!! route('pembelian.historia') !!}',
                        type: 'GET',
                        data : {
                            'id': no_pembelian
                        },
                        success: function(result) {
                            $('#PostedBy1').val(result.post);
                            $('#UnpostBy1').val(result.unpost);
                        }
                    });
                    
                    var pengguna = $('#NamaUser1').val();
                    
                    if(status == 'POSTED' && item > 0){
                        $('.tombol1').hide();
                        $('.tombol7').hide();
                        $('.tombol8').hide();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').show();
                        $('.preview-button').show();
                        $('.ttdigi-button').show();
                        $('.view-button').show();
                        $('.void-button').show();
                        
                        if (no_pembelian.substr(2,4) == "POGA"){
                            $('.tombol2').show();
                        }else{
                            if (grand_total > parseInt($('#grandaga1').val())) {
                                if (pengguna == $('#namaga').val() || pengguna == $('#namaga2').val() || pengguna == $('#namaga3').val()) {
                                    $('.tombol2').show();
                                }else {
                                    $('.tombol2').hide();
                                }
                            }
    
                            if (grand_total > parseInt($('#grandara1').val()) && grand_total <= parseInt($('#grandara2').val())) {
                                if (pengguna == $('#namara').val() || pengguna == $('#namara2').val() || pengguna == $('#namara3').val()) {
                                    $('.tombol2').show();
                                }else {
                                    $('.tombol2').hide();
                                }
                            }
    
                            if (grand_total > parseInt($('#grand1').val()) && grand_total <= parseInt($('#grand2').val())) {
                                if (pengguna == $('#nama').val() || pengguna == $('#nama2').val() || pengguna == $('#nama3').val()) {
                                    $('.tombol2').show();
                                }else {
                                    $('.tombol2').hide();
                                }
                            }
                        }
                    }else if(status =='OPEN' && item > 0){
                        $('.tombol1').show();
                        $('.tombol2').hide();
                        $('.tombol7').hide();
                        $('.tombol8').hide();
                        $('.add-button').show();
                        $('.hapus-button').hide();
                        $('.edit-button').show();
                        $('.print-button').hide();
                        $('.preview-button').hide();
                        $('.ttdigi-button').hide();
                        $('.view-button').show();
                        $('.void-button').show();
                    }else if(status =='OPEN' && item == 0){
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.tombol7').hide();
                        $('.tombol8').hide();
                        $('.add-button').show();
                        $('.hapus-button').show();
                        $('.edit-button').show();
                        $('.print-button').hide();
                        $('.preview-button').hide();
                        $('.ttdigi-button').hide();
                        $('.view-button').hide();
                        $('.void-button').show();
                    }else if(status =='CLOSED' || status == 'INVOICED'){
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.tombol7').hide();
                        $('.tombol8').hide();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').show();
                        $('.preview-button').show();
                        $('.ttdigi-button').show();
                        $('.view-button').show();
                        $('.void-button').hide();
                    }else if(status == 'RECEIVED'){
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.tombol7').show();
                        $('.tombol8').hide();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').show();
                        $('.preview-button').show();
                        $('.ttdigi-button').show();
                        $('.view-button').show();
                        $('.void-button').hide();
                    }else if(status == 'APPROVED'){
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.tombol7').hide();
                        $('.tombol8').show();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').show();
                        $('.preview-button').show();
                        $('.ttdigi-button').show();
                        $('.view-button').show();
                        $('.void-button').hide();
                    }                
                }
            } );            
        
            $('#button1').click( function () {
                var select = $('.selected').closest('tr');
                var colom = select.find('td:eq(0)').text();
                var no_pembelian = colom;
                swal({
                    title: "Post?",
                    text: colom,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Posting!",
                    cancelButtonText: "Batal",
                    reverseButtons: !0
                    }).then(function (e) {
                        
                        if (e.value === true) {
                            swal({
                            title: "<b>Proses Sedang Berlangsung</b>",
                            type: "warning",
                            showCancelButton: false,
                            showConfirmButton: false,
                            allowOutsideClick: false
                            })
                            
                        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                // alert( table.rows('.selected').data().length +' row(s) selected' );
                        $.ajax({
                            url: '{!! route('pembelian.post') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pembelian
                            },
                            success: function(result) {
                                if (result.success === true) {
                                    swal(
                                    'Posted!',
                                    'Your file has been posted.',
                                    'success'
                                    )
                                    refreshTable();
                                }
                                else{
                                  swal({
                                      title: 'Error',
                                      text: result.message,
                                      type: 'error',
                                  })
                                    refreshTable();
                                }
                            },
                            error : function () {
                              swal({
                                  title: 'Oops...',
                                  text: 'Gagal',
                                  type: 'error',
                                  timer: '1500'
                              })
                            }
                        });
                    } else {
                        e.dismiss;
                    }

                }, function (dismiss) {
                    return false;
                })
            });

            $('#button2').click( function () {
                var select = $('.selected').closest('tr');
                var colom = select.find('td:eq(0)').text();
                var no_pembelian = colom;
                swal({
                    title: "Unpost?",
                    text: colom,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Unpost!",
                    cancelButtonText: "Batal",
                    reverseButtons: !0
                    }).then(function (e) {
                        if (e.value === true) {
                            swal({
                            title: "<b>Proses Sedang Berlangsung</b>",
                            type: "warning",
                            showCancelButton: false,
                            showConfirmButton: false,
                            allowOutsideClick: false
                            })
                            
                        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                        $.ajax({
                            url: '{!! route('pembelian.unpost') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pembelian
                            },
                            success: function(result) {
                                if (result.success === true) {
                                    swal(
                                    'Unposted!',
                                    'Data berhasil di Unpost.',
                                    'success'
                                    )
                                    refreshTable();
                                }
                                else{
                                  swal({
                                      title: 'Error',
                                      text: result.message,
                                      type: 'error',
                                  })
                                    refreshTable();
                                }
                            },
                            error : function () {
                              swal({
                                  title: 'Oops...',
                                  text: data.message,
                                  type: 'error',
                                  timer: '1500'
                              })
                            }
                        });
                    } else {
                        e.dismiss;
                    }

                }, function (dismiss) {
                    return false;
                })
            });

            $('#button7').click( function () {
                var select = $('.selected').closest('tr');
                var colom = select.find('td:eq(0)').text();
                var no_pembelian = colom;
                swal({
                    title: "Approve?",
                    text: colom,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Approve!",
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
                // alert( table.rows('.selected').data().length +' row(s) selected' );
                        $.ajax({
                            url: '{!! route('pembelian.approve') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pembelian
                            },
                            success: function(result) {
                                if (result.success === true) {
                                    swal(
                                    'Approved!',
                                    'Your file has been approve.',
                                    'success'
                                    )
                                    refreshTable();
                                }
                                else{
                                  swal({
                                      title: 'Error',
                                      text: result.message,
                                      type: 'error',
                                  })
                                    refreshTable();
                                }
                            },
                            error : function () {
                              swal({
                                  title: 'Oops...',
                                  text: 'Gagal',
                                  type: 'error',
                                  timer: '1500'
                              })
                            }
                        });
                    } else {
                        e.dismiss;
                    }

                }, function (dismiss) {
                    return false;
                })
            });

            $('#button8').click( function () {
                var select = $('.selected').closest('tr');
                var colom = select.find('td:eq(0)').text();
                var no_pembelian = colom;
                swal({
                    title: "Disapprove?",
                    text: colom,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Disapprove!",
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
                // alert( table.rows('.selected').data().length +' row(s) selected' );
                        $.ajax({
                            url: '{!! route('pembelian.disapprove') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pembelian
                            },
                            success: function(result) {
                                if (result.success === true) {
                                    swal(
                                    'Disapproved!',
                                    'Your file has been disapprove.',
                                    'success'
                                    )
                                    refreshTable();
                                }
                                else{
                                  swal({
                                      title: 'Error',
                                      text: result.message,
                                      type: 'error',
                                  })
                                    refreshTable();
                                }
                            },
                            error : function () {
                              swal({
                                  title: 'Oops...',
                                  text: 'Gagal',
                                  type: 'error',
                                  timer: '1500'
                              })
                            }
                        });
                    } else {
                        e.dismiss;
                    }

                }, function (dismiss) {
                    return false;
                })
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
            
            //TTD DIGITAL
            var canvasDiv = document.getElementById('canvasDiv');
            var canvas = document.createElement('canvas');
            canvas.setAttribute('id', 'canvas');

            canvasDiv.appendChild(canvas);
            $("#canvas").attr('height', $("#canvasDiv").outerHeight());
            $("#canvas").attr('width', $("#canvasDiv").width());
            if (typeof G_vmlCanvasManager != 'undefined') {
                canvas = G_vmlCanvasManager.initElement(canvas);
            }

            context = canvas.getContext("2d");
            $('#canvas').mousedown(function(e) {
                var offset = $(this).offset()
                var mouseX = e.pageX - this.offsetLeft;
                var mouseY = e.pageY - this.offsetTop;

                paint = true;
                addClick(e.pageX - offset.left, e.pageY - offset.top);
                redraw();
            });

            $('#canvas').mousemove(function(e) {
                if (paint) {
                    var offset = $(this).offset()
                    //addClick(e.pageX - this.offsetLeft, e.pageY - this.offsetTop, true);
                    addClick(e.pageX - offset.left, e.pageY - offset.top, true);
                    console.log(e.pageX, offset.left, e.pageY, offset.top);
                    redraw();
                }
            });

            $('#canvas').mouseup(function(e) {
                paint = false;
            });

            $('#canvas').mouseleave(function(e) {
                paint = false;
            });

            var clickX = new Array();
            var clickY = new Array();
            var clickDrag = new Array();
            var paint;

            function addClick(x, y, dragging) {
                clickX.push(x);
                clickY.push(y);
                clickDrag.push(dragging);
            }

            $("#reset-btn").click(function() {
                context.clearRect(0, 0, window.innerWidth, window.innerWidth);
                clickX = [];
                clickY = [];
                clickDrag = [];
            });

            $(document).on('click', '#btn-save', function() {
                var mycanvas = document.getElementById('canvas');
                var img = mycanvas.toDataURL("image/png");

                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_pembelian = data['no_pembelian'];
                $.ajax({
                    url: '{!! route('pembelian.ttd_buat') !!}',
                    type: 'POST',
                    data : {
                        'no': no_pembelian,
                        'img': img,
                    },
                    success: function(results) {
                        context.clearRect(0, 0, window.innerWidth, window.innerWidth);
                        clickX = [];
                        clickY = [];
                        clickDrag = [];
                        if (results.success == true) {
                            swal("Berhasil!", results.message, "success");
                        }
                    }
                });
            });

            $(document).on('click', '#btn-periksa', function() {
                var mycanvas = document.getElementById('canvas');
                var img = mycanvas.toDataURL("image/png");

                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_pembelian = data['no_pembelian'];
                $.ajax({
                    url: '{!! route('pembelian.ttd_periksa') !!}',
                    type: 'POST',
                    data : {
                        'no': no_pembelian,
                        'img': img,
                    },
                    success: function(results) {
                        context.clearRect(0, 0, window.innerWidth, window.innerWidth);
                        clickX = [];
                        clickY = [];
                        clickDrag = [];
                        if (results.success == true) {
                            swal("Berhasil!", results.message, "success");
                        }
                    }
                });
            });
            
            $(document).on('click', '#btn-setuju', function() {
                var mycanvas = document.getElementById('canvas');
                var img = mycanvas.toDataURL("image/png");

                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_pembelian = data['no_pembelian'];
                $.ajax({
                    url: '{!! route('pembelian.ttd_setuju') !!}',
                    type: 'POST',
                    data : {
                        'no': no_pembelian,
                        'img': img,
                    },
                    success: function(results) {
                        context.clearRect(0, 0, window.innerWidth, window.innerWidth);
                        clickX = [];
                        clickY = [];
                        clickDrag = [];
                        if (results.success == true) {
                            swal("Berhasil!", results.message, "success");
                        }
                    }
                });
            });
            
            $(document).on('click', '#btn-tahu', function() {
                var mycanvas = document.getElementById('canvas');
                var img = mycanvas.toDataURL("image/png");

                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_pembelian = data['no_pembelian'];
                $.ajax({
                    url: '{!! route('pembelian.ttd_tahu') !!}',
                    type: 'POST',
                    data : {
                        'no': no_pembelian,
                        'img': img,
                    },
                    success: function(results) {
                        context.clearRect(0, 0, window.innerWidth, window.innerWidth);
                        clickX = [];
                        clickY = [];
                        clickDrag = [];
                        if (results.success == true) {
                            swal("Berhasil!", results.message, "success");
                        }
                    }
                });
            });

            var drawing = false;
            var mousePos = {
                x: 0,
                y: 0
            };
            var lastPos = mousePos;

            canvas.addEventListener("touchstart", function(e) {
                mousePos = getTouchPos(canvas, e);
                var touch = e.touches[0];
                var mouseEvent = new MouseEvent("mousedown", {
                    clientX: touch.clientX,
                    clientY: touch.clientY
                });
                canvas.dispatchEvent(mouseEvent);
            }, false);

            canvas.addEventListener("touchend", function(e) {
                var mouseEvent = new MouseEvent("mouseup", {});
                canvas.dispatchEvent(mouseEvent);
            }, false);

            canvas.addEventListener("touchmove", function(e) {
                var touch = e.touches[0];
                var offset = $('#canvas').offset();
                var mouseEvent = new MouseEvent("mousemove", {
                    clientX: touch.clientX,
                    clientY: touch.clientY
                });
                canvas.dispatchEvent(mouseEvent);
            }, false);

            // Get the position of a touch relative to the canvas
            function getTouchPos(canvasDiv, touchEvent) {
                var rect = canvasDiv.getBoundingClientRect();
                return {
                    x: touchEvent.touches[0].clientX - rect.left,
                    y: touchEvent.touches[0].clientY - rect.top
                };
            }

            var elem = document.getElementById("canvas");

            var defaultPrevent = function(e) {
                e.preventDefault();
            }
            elem.addEventListener("touchstart", defaultPrevent);
            elem.addEventListener("touchmove", defaultPrevent);

            function redraw() {
                //
                lastPos = mousePos;
                for (var i = 0; i < clickX.length; i++) {
                    context.beginPath();
                    if (clickDrag[i] && i) {
                        context.moveTo(clickX[i - 1], clickY[i - 1]);
                    } else {
                        context.moveTo(clickX[i] - 1, clickY[i]);
                    }
                    context.lineTo(clickX[i], clickY[i]);
                    context.lineWidth = 5;
                    context.closePath();
                    context.stroke();
                }
            }

            $('#button5').click( function () {
                var select = $('.selected').closest('tr');
                var no_pembelian = select.find('td:eq(0)').text();
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('pembelian.showdetail') !!}',
                    type: 'POST',
                    data : {
                        'id': no_pembelian
                    },
                    success: function(result) {
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

            $('#editpembelian').click( function () {
                var select = $('.selected').closest('tr');
                var no_pembelian = select.find('td:eq(0)').text();
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('pembelian.edit_pembelian') !!}',
                    type: 'POST',
                    data : {
                        'id': no_pembelian
                    },
                    success: function(results) {
                        $('#editform').modal('show');
                        $('#Pembelian').val(results.no_pembelian);
                        $('#Vendor').val(results.kode_vendor).trigger('change');
                        $('#Tanggal').val(results.tanggal_pembelian);
                        $('#Ref').val(results.no_penawaran);
                        $('#TOP').val(results.top);
                        $('#Due').val(results.due_date);
                        $('#Diskon').val(results.diskon_persen);
                        $('#Diskonrp').val(results.diskon_rp);
                        $('#PPN').val(results.ppn);
                        $('#pbbkb').val(results.pbbkb);
                        $('#pbbkbrp').val(results.pbbkb_rp);
                        $('#ongkosangkut').val(results.ongkos_angkut);
                        $('#Deskripsi').val(results.deskripsi);
                        $('#Cost2').val(results.cost_center).trigger('change');
                        $('#Status').val(results.status);
                        $('#Jenis').val(results.jenis_po).trigger('change');
                    }
                });
            });

            $('#hapuspembelian').click( function () {
                var select = $('.selected').closest('tr');
                var no_pembelian = select.find('td:eq(0)').text();
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
                            url: '{!! route('pembelian.hapus_pembelian') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pembelian
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
            
            $('#voidpembelian').click( function () {
                var select = $('.selected').closest('tr');
                var no_pembelian = select.find('td:eq(0)').text();
                var row = table.row( select );
                swal({
                    title: "Hapus?",
                    text: "Pastikan dahulu item yang akan di void",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Void!",
                    cancelButtonText: "Batal!",
                    reverseButtons: !0
                }).then(function (e) {
                    if (e.value === true) {
                        $.ajax({
                            url: '{!! route('pembelian.void_pembelian') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pembelian
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
            $('#data-table').DataTable().ajax.reload(null,false);
            $('.tombol1').hide();
            $('.tombol2').hide();
            $('.tombol7').hide();
            $('.tombol8').hide();
            $('.add-button').hide();
            $('.ttdigi-button').hide();
            $('.hapus-button').hide();
            $('.edit-button').hide();
            $('.print-button').hide();
            $('.preview-button').hide();
            $('.view-button').hide();
        }

        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });

        $('.modal-dialog').resizable({
    
        });

        function hitung() {
             var tgl_awal = $('#Tanggal1').val();
             var hari = $('#TOP1').val();

             var hasil = new Date(new Date().getTime()+(hari*24*60*60*1000)); 

             var newdate = new Date(hasil);
             var dd = newdate.getDate();
             var mm = newdate.getMonth() + 1;
             var y = newdate.getFullYear();

             var someFormattedDate = y + '-' + mm + '-' + dd;
             document.getElementById('Due1').value = someFormattedDate;
        }

        function hitung2() {
             var tgl_awal = $('#Tanggal').val();
             var hari = $('#TOP').val();

             var hasil = new Date(new Date().getTime()+(hari*24*60*60*1000)); 

             var newdate = new Date(hasil);
             var dd = newdate.getDate();
             var mm = newdate.getMonth() + 1;
             var y = newdate.getFullYear();

             var someFormattedDate = y + '-' + mm + '-' + dd;
             document.getElementById('Due').value = someFormattedDate;
        }
        
        function get_ppn(){
            var kode_vendor = $('#Vendor1').val();
            $.ajax({
                url:'{!! route('pembelian.get_ppn') !!}',
                type:'POST',
                data : {
                    'kode_vendor': kode_vendor,
                },
                success: function(result) {
                    $('#PPN1').val(result.ppn);
                },
            });
        }

        function get_ppn2(){
            var kode_vendor = $('#Vendor').val();
            $.ajax({
                url:'{!! route('pembelian.get_ppn') !!}',
                type:'POST',
                data : {
                    'kode_vendor': kode_vendor,
                },
                success: function(result) {
                    $('#PPN').val(result.ppn);
                },
            });
        }

        function cekdiskon(){
            var diskonpersen = $('#Diskon1').val();
            var diskonrp = $('#Diskonrp1').val();
            if (diskonpersen > 0){
                $('#Diskonrp1').val('0');
                // document.getElementById('Diskonrp1').disabled = true;
            }
        }

        function cekdiskon2(){
            var diskonpersen = $('#Diskon1').val();
            var diskonrp = $('#Diskonrp1').val();
            if (diskonrp > 0){
                $('#Diskon1').val('0');
                // document.getElementById('Diskon1').disabled = true;
            }
        }

        function cekdiskon3(){
            var pbbkbpersen = $('#pbbkb1').val();
            var pbbkbrp = $('#pbbkbrp1').val();
            if (pbbkbrp > 0){
                $('#pbbkbrp1').val('0');
                // document.getElementById('Diskon1').disabled = true;
            }
        }

        function cekdiskon4(){
            var pbbkbpersen = $('#pbbkb1').val();
            var pbbkbrp = $('#pbbkbrp1').val();
            if (pbbkbpersen > 0){
                $('#pbbkb1').val('0');
                // document.getElementById('Diskon1').disabled = true;
            }
        }


        function cekdiskone(){
            var diskonpersen = $('#Diskon').val();
            var diskonrp = $('#Diskonrp').val();
            if (diskonpersen > 0){
                $('#Diskonrp').val('0');
                // document.getElementById('Diskonrp1').disabled = true;
            }
        }

        function cekdiskon2e(){
            var diskonpersen = $('#Diskon').val();
            var diskonrp = $('#Diskonrp').val();
            if (diskonrp > 0){
                $('#Diskon').val('0');
                // document.getElementById('Diskon1').disabled = true;
            }
        }

        function cekdiskon3e(){
            var pbbkbpersen = $('#pbbkb').val();
            var pbbkbrp = $('#pbbkbrp').val();
            if (pbbkbrp > 0){
                $('#pbbkbrp').val('0');
                // document.getElementById('Diskon1').disabled = true;
            }
        }

        function cekdiskon4e(){
            var pbbkbpersen = $('#pbbkb').val();
            var pbbkbrp = $('#pbbkbrp').val();
            if (pbbkbpersen > 0){
                $('#pbbkb').val('0');
                // document.getElementById('Diskon1').disabled = true;
            }
        }
             

        $('#ADD').submit(function (e) {
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false
            })
            e.preventDefault();
            var registerForm = $("#ADD");
            var formData = registerForm.serialize();
            
            var now = new Date();
            var day = ("0" + now.getDate()).slice(-2);
            var month = ("0" + (now.getMonth() + 1)).slice(-2);
            var today = now.getFullYear()+"-"+(month)+"-"+(day) ;

                $.ajax({
                    url:'{!! route('pembelian.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        // $('#Kode1').val('');
                        $('#addform').modal('show');
                        $('#Tanggal1').val(today);
                        $('#Jenis1').val('').trigger('change');
                        $('#Vendor1').val('').trigger('change');
                        $('#Ref1').val('');
                        $('#TOP1').val('');
                        $('#Due1').val('');
                        $('#Diskon1').val('0');
                        $('#Diskonrp1').val('0');
                        $('#PPN1').val('0');
                        $('#pbbkb1').val('0');
                        $('#pbbkbrp1').val('0');
                        $('#ongkosangkut1').val('0');
                        $('#Deskripsi1').val('');
                        $('#Cost1').val('').trigger('change');
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
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false
            })
            e.preventDefault();
            var registerForm = $("#EDIT");
            var formData = registerForm.serialize();

                $.ajax({
                    url:'{!! route('pembelian.updateajax') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
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

                                