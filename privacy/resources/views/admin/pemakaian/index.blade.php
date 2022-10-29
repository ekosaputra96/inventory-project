'
@extends('adminlte::page')

@section('title', 'Pemakaian')

@section('content_header')
    
@stop


@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
@include('sweet::alert')
<style>
    #canvasDiv{
        position: relative;
        border: 2px solid grey;
        height:300px;
        width: 550px;
    }
    
    @media only screen and (max-width: 700px) {
          #canvasDiv {
            position: relative;
            border: 2px solid grey;
            height:300px;
            width: 340px;
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
                    
                        @permission('create-pemakaian')
                        <button type="button" class="btn btn-success btn-xs" id="new-button" data-toggle="modal" data-target="#addform">
                            <i class="fa fa-plus"></i> Pemakaian Baru</button>
                            <i>&nbsp;ALT+1 = New Pemakaian (shortcut)</i>
                        @endpermission
                    
                    
                    <!--@permission('post-getkode')-->
                    <!--<button type="button" class="btn btn-primary btn-xs" onclick="getkode()">-->
                    <!--    <i class="fa fa-bullhorn"></i> Get New Kode</button>-->
                    <!--@endpermission-->

                    <!--<?php if (auth()->user()->level == 'superadministrator') { ?>-->
                    <!--    <button type="button" class="btn btn-primary btn-xs" onclick="hitungjurnal()">-->
                    <!--        <i class="fa fa-bullhorn"></i> Hitung Jurnal</button>-->
                    <!--<?php } ?>-->

                    <span class="pull-right"> 
                    <?php if (auth()->user()->level == 'superadministrator') { ?>
                        <button type="button" class="btn bg-black btn-xs" id="calc-button" data-toggle="modal" data-target="#calcform">
                        <i class="fa fa-podcast">&nbsp;</i> Calculate Jurnal&nbsp;&nbsp;</button>
                    <?php } ?>
                    <?php if (stripos($_SERVER['HTTP_USER_AGENT'], 'Windows') === FALSE){ ?>
                        <button type="button" class="btn bg-orange btn-xs preview-button" data-toggle="modal" data-target="#previewpo"><i class="fa fa-print"></i> Preview Detail</button>
                        <button type="button" class="btn bg-black btn-xs ttdigi-button" id="addttd" data-toggle="modal" data-target="#ttdform"><i class="fa fa-edit"></i> TTD DIGITAL</button>
                    <?php } ?>
                        <font style="font-size: 16px;"><b>PEMAKAIAN</b></font>
                    </span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data-table" style="font-size: 12px; width: 1200px;">
                    <thead>
                    <tr class="bg-primary" style="font-size: 11px;">
                        <th>No Pemakaian</th>
                        <th>Tanggal Pemakaian</th>
                        <th style="width: 40px;">No Polisi</th>
                        <th style="width: 60px;">No Aset Mobil</th>
                        <th style="width: 75px;">Nama Alat</th>
                        <th style="width: 80px;">No Aset Alat</th>           
                        <th style="width: 70px;">Nama Kapal</th>
                        <th style="width: 70px;">No Aset Kapal</th>
                        <th style="width: 250px;">Keterangan</th>
                        <th style="width: 70px;">Pemakai</th>
                        <th>Total Item</th>
                        <th>Tipe Pemakaian</th>
                        <th>HM</th>
                        <th>KM</th>
                        <th>No JO</th>
                        <th style="width: 60px;">Kode Lokasi</th>
                        <th>Status</th>
                        <th>No WO</th>
                        <th>No Journal</th>
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

{{ Form::hidden('n10', null, ['class'=> 'form-control','id'=>'Grandios1','readonly']) }}

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
                        <button type="button" class="btn bg-green" id="btn-terima">Simpan (Pemakai)</button>
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
              <h4 class="modal-title" style="text-align: center;">Preview Detail</h4>
            </div>
            <div class="modal-body">
                <div class="row" style="font-size: 8pt;">
                    <b>
                    <div class="col-md-8">
                        <div class="form-group">
                            Tipe : {{ Form::label('nomoor', null,['id'=>'PreviewTipe']) }}<br>
                            Nama : {{ Form::label('nomoor', null,['id'=>'PreviewAlat']) }}<br>
                            No. Asset : {{ Form::label('nomoor', null,['id'=>'PreviewAsset']) }}<br>
                            No. JO : {{ Form::label('nomoor', null,['id'=>'PreviewJo']) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            No. PK : {{ Form::label('nomoor', null,['id'=>'PreviewNomor']) }}<br>
                            Tgl. PK : {{ Form::label('nomoor', null,['id'=>'PreviewTanggal']) }}<br>
                            Pemakai : {{ Form::label('nomoor', null,['id'=>'PreviewPemakai']) }}<br>
                            No. WO : {{ Form::label('nomoor', null,['id'=>'PreviewWo']) }}
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
                            <th>Partnumber</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                        </tr>
                    </thead>
                </table>
                <div class="modal-footer">
                    <span class="pull-left">
                        Note : {{ Form::label('nomoor', null,['id'=>'PreviewNote']) }}
                    </span>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="calcform"  role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            @include('errors.validation')
            {!! Form::open(['id'=>'CALC_JURNAL']) !!}
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            {{ Form::label('jenis', 'Bulan:') }} 
                            {{ Form::selectMonth('bulan', null, ['class'=> 'form-control select2','id'=>'namabulan','placeholder'=>'','style'=>'width: 100%','required'])}}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {{ Form::label('jenis', 'Tahun:') }} 
                            {{ Form::selectYear('tahun', 2021, 2040, null, ['class'=> 'form-control select2','id'=>'namatahun','placeholder'=>'','style'=>'width: 100%','required'])}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    {{ Form::submit('Calculate', ['class'=>'btn btn-success crud-submit' , 'id'=>'create-button']) }}
                    {{ Form::button('Close', ['class' => 'btn btn-danger','data-dismiss'=>'modal']) }}&nbsp;
                </div>
            </div>
            {!! Form::close() !!}
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="addform"  role="dialog">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <div class="row">
                    <div class="col-md-5">
                        <h4>Create Data <i>&nbsp;ENTER = Simpan</i></h4>   
                    </div>
                  </div>
                </div>
                
                @include('errors.validation')
                {!! Form::open(['id'=>'ADD']) !!}
                        <div class="modal-body">
                            <div class="row">
                                {{ Form::hidden('company_user', auth()->user()->kode_company, ['class'=> 'form-control','id'=>'company1','readonly']) }}
                            <?php if (auth()->user()->kode_company == '02') { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('Type', 'Tipe Pemakaian:',['class'=>'control-label']) }}
                                        {{ Form::select('type', ['Mobil'=>'Mobil','Alat'=>'Alat','Kapal'=>'Kapal','Other'=>'Other','Mekanik'=>'Mekanik PBM','Foreman'=>'Foreman PBM','Operasional'=>'Operasional PBM'],null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'type','onchange'=>"pakai();",'required']) }}
                                    </div>
                                </div>
                            <?php }else { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('Type', 'Tipe Pemakaian:',['class'=>'control-label']) }}
                                        {{ Form::select('type', ['Mobil'=>'Mobil','Alat'=>'Alat','Kapal'=>'Kapal','Other'=>'Other'],null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'type','onchange'=>"pakai();",'required']) }}
                                    </div>
                                </div>
                            <?php } ?>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('Tanggal Pemakaian', 'Tanggal Pemakaian:') }}
                                        {{ Form::date('tanggal_pemakaian', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'Tanggal1','required'=>'required'])}}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('pemakai', 'Pemakai:') }}
                                        {{ Form::text('pemakai', null, ['class'=> 'form-control','id'=>'Pemakai1','required'=>'required', 'placeholder'=>'Pemakai', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)",'onkeypress'=>"return hanyaHuruf(event)"]) }}
                                    </div>
                                </div>
                                
                                    <div class="form-group1 col-md-6">
                                        <div id="nopols">
                                            {{ Form::label('nopol', 'Nomor Polisi:') }}
                                            {{ Form::select('kode_mobil',$Mobil,null, ['class'=>'form-control select2','id'=>'Nopol1','style'=>'width: 100%','placeholder' => '','disabled','onchange'=>'getasetmobil();']) }}
                                        </div>             
                                    </div>

                                    <div class="form-group13 col-md-6">                     
                                        <div id="alats">
                                            {{ Form::label('kode_alat', 'Kode Alat:') }}
                                            {{ Form::select('kode_alat',$Alat,null, ['class'=>'form-control select2','id'=>'Alat1','style'=>'width: 100%','placeholder' => '','disabled','onchange'=>'getasetalat();']) }}
                                        </div>
                                    </div>

                                    <div class="form-group5 col-md-6">
                                        <div id="kapals">
                                            {{ Form::label('kode_kapal', 'Kode Kapal:') }}
                                            {{ Form::select('kode_kapal',$Kapal,null, ['class'=>'form-control select2','id'=>'Kapal1','style'=>'width: 100%','placeholder' => '','disabled','onchange'=>'getasetkapal();']) }}
                                        </div>
                                    </div>   

                                    <div class="form-group4 col-md-6">
                                        <div id="noasetx1">
                                            {{ Form::label('Aset', 'No Aset Mobil:') }}
                                            {{ Form::select('aset',$Asmobil, null, ['class'=> 'form-control select2','id'=>'noaset1','style'=>'width: 100%','placeholder' => ''])}}
                                        </div>
                                    </div>

                                    <div class="form-group8 col-md-6">
                                        <div id="noasetx2">
                                            {{ Form::label('Aset', 'No Aset Alat:') }}
                                            {{ Form::select('aset',$Asalat, null, ['class'=> 'form-control select2','id'=>'noaset2','style'=>'width: 100%','placeholder' => ''])}}
                                        </div>
                                    </div>

                                    <div class="form-group9 col-md-6">
                                        <div id="noasetx3">
                                            {{ Form::label('Aset', 'No Aset Kapal:') }}
                                            {{ Form::select('aset',$Askapal, null, ['class'=> 'form-control select2','id'=>'noaset3','style'=>'width: 100%','placeholder' => ''])}}
                                        </div>
                                    </div>
                                                                 
                                    <div class="form-group3 col-md-6">
                                        <div id="nopols_as">
                                            {{ Form::label('nopol', 'Nomor Asset Mobil:') }}
                                            {{ Form::select('no_asset_mobil',$Asmobil,null, ['class'=>'form-control','id'=>'Aset1','style'=>'width: 100%','placeholder' => '','disabled','onchange'=>'getnopol();']) }}
                                        </div>
                                        <div id="alats_as">
                                            {{ Form::label('kode_alat', 'Nomor Asset Alat:') }}
                                            {{ Form::select('no_asset_alat',$Asalat,null, ['class'=>'form-control','id'=>'Aset2','style'=>'width: 100%','placeholder' => '','disabled']) }}
                                        </div>
                                        <div id="kapals_as">
                                            {{ Form::label('kode_kapal', 'Nomor Asset Kapal:') }}
                                            {{ Form::select('no_asset_kapal',$Askapal,null, ['class'=>'form-control','id'=>'Aset3','style'=>'width: 100%','placeholder' => '','disabled']) }}
                                        </div>                                   
                                    </div>
                                    
                                <?php if (Auth()->user()->kode_company == '02') { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <br>
                                            {{ Form::label('Nomoo', 'No WO:') }}
                                            {{ Form::select('no_wo', $Workorder->sort(),null, ['class'=> 'form-control select2','id'=>'Wo1','placeholder'=>'','','style'=>'width: 100%', 'onchange'=>'getwoalat();']) }}
                                        </div>
                                    </div>
                                    <!--<div class="col-md-4">-->
                                    <!--    <div class="form-group">-->
                                    <!--        <br>-->
                                    <!--        {{ Form::label('Nomoo', 'No WO:') }}-->
                                    <!--        {{ Form::text('no_wo',null, ['class'=> 'form-control','id'=>'Wo1','placeholder'=>'','','style'=>'width: 100%']) }}-->
                                    <!--    </div>-->
                                    <!--</div>-->
                                <?php } ?>
                                    
                                
                                <?php if (Auth()->user()->kode_company == '03') { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <br>
                                            {{ Form::label('cosot', 'Cost Center:') }}
                                            {{ Form::select('cost_center',$Costcenter,null, ['class'=>'form-control select2','id'=>'Cost1','style'=>'width: 100%','placeholder'=>'']) }}
                                        </div>
                                    </div>
                                <?php } ?>
                                    
                            <?php if (auth()->user()->kode_company == '05') { ?>
                                <div class="col-md-4">
                                    <div class="form-group23">
                                        <br>
                                        {{ Form::label('no_jo', 'No JO:') }}
                                        {{ Form::select('no_jo', $Joborder->sort(), null, ['class'=> 'form-control select2','id'=>'Jo1','style'=>'width: 100%','placeholder' => '']) }}
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="col-md-4">
                                    <div class="form-group23">
                                        <br>
                                        {{ Form::label('no_jo', 'No JO:') }}
                                        {{ Form::text('no_jo', 0, ['class'=> 'form-control','id'=>'Jo1','onkeypress'=>"return pulsar(event,this)"]) }}
                                    </div>
                                </div>
                            <?php } ?>
                                
                                <div class="col-md-4">
                                    <div class="form-group20">
                                        <br>
                                        {{ Form::label('hmkm', 'HM:') }}
                                        {{ Form::text('hmkm', null, ['class'=> 'form-control','id'=>'hmkm1', 'placeholder'=>'HourMeter', 'autocomplete'=>'off']) }}
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group20">
                                        <br>
                                        {{ Form::label('kkkk', 'KM:') }}
                                        {{ Form::text('km', null, ['class'=> 'form-control','id'=>'km1', 'placeholder'=>'KiloMeter', 'autocomplete'=>'off']) }}
                                    </div>
                                </div>
                                
                                
                                
                                <div class="col-md-12">
                                    <div class="form-group7">
                                        <br>
                                        {{ Form::label('Deskripsi', 'Keterangan:') }}
                                        {{ Form::textArea('deskripsi', null, ['class'=> 'form-control','rows'=>'4','id'=>'Deskripsi1', 'placeholder'=>'Deskripsi', 'autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                

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
                                {{ Form::hidden('company_user', auth()->user()->kode_company, ['class'=> 'form-control','id'=>'company2','readonly']) }}
                                
                                <div class="row">                                
                                            {{ Form::hidden('no_pemakaian', null, ['class'=> 'form-control','id'=>'Pemakaian','readonly']) }}
                                                                                                                                  
                                    <!-- <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('Type', 'Tipe Pemakaian:') }}
                                            {{ Form::text('type', null, ['class'=> 'form-control','id'=>'typeedit','readonly'])}}
                                        </div>
                                    </div> -->
                                    <?php if (auth()->user()->kode_company == '02') { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('Type', 'Tipe Pemakaian:',['class'=>'control-label']) }}
                                            {{ Form::select('type', ['Mobil'=>'Mobil','Alat'=>'Alat','Kapal'=>'Kapal','Other'=>'Other','Mekanik'=>'Mekanik PBM','Foreman'=>'Foreman PBM','Operasional'=>'Operasional PBM'],null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'typeedit','onchange'=>"pakai2();",'required']) }}
                                        </div>
                                    </div>
                                    <?php }else { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('Type', 'Tipe Pemakaian:',['class'=>'control-label']) }}
                                            {{ Form::select('type', ['Mobil'=>'Mobil','Alat'=>'Alat','Kapal'=>'Kapal','Other'=>'Other'],null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'typeedit','onchange'=>"pakai2();",'required']) }}
                                        </div>
                                    </div>
                                    <?php } ?>
            
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('Tanggal Pemakaian', 'Tanggal Pemakaian:') }}
                                            {{ Form::date('tanggal_pemakaian', null,['class'=> 'form-control','id'=>'Tanggal'])}}                                        
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('pemakai', 'Pemakai:') }}
                                            {{ Form::text('pemakai', null, ['class'=> 'form-control','id'=>'Pemakai', 'autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)"]) }}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group2">
                                            <div id="nopols1">
                                                {{ Form::label('nopol', 'Nomor Polisi:') }}
                                                {{ Form::select('kode_mobil',$Mobil,null, ['class'=>'form-control select2','id'=>'Nopol','style'=>'width: 100%','onchange'=>'getasetmobil2();']) }}
                                            </div>
                                      
                                            <div id="alats1">
                                                {{ Form::label('kode_alat', 'Nama Alat:') }}
                                                {{ Form::select('kode_alat',$Alat,null, ['class'=>'form-control select2','id'=>'Alat','style'=>'width: 100%','onchange'=>'getasetalat2();']) }}                                      
                                            </div>

                                            <div id="kapals1">
                                                {{ Form::label('kode_kapal', 'Nama Kapal:') }}
                                                {{ Form::select('kode_kapal',$Kapal,null, ['class'=>'form-control select2','id'=>'Kapal','style'=>'width: 100%','onchange'=>'getasetkapal2();']) }}                                      
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group11">
                                            <div id="asetnopols1">
                                                {{ Form::label('no_asset_mobil', 'Nomor Aset:') }}
                                                {{ Form::select('no_asset_mobil',$Asmobil,null, ['class'=>'form-control select2','id'=>'Asetmobil','style'=>'width: 100%']) }}
                                            </div>

                                            <div id="asetalats1">
                                                {{ Form::label('no_asset_alat', 'Nomor Aset:') }}
                                                {{ Form::select('no_asset_alat',$Asalat,null, ['class'=>'form-control select2','id'=>'Asetalat','style'=>'width: 100%']) }}
                                            </div>

                                            <div id="asetkapals1">
                                                {{ Form::label('no_asset_kapal', 'Nomor Aset:') }}
                                                {{ Form::select('no_asset_kapal',$Askapal,null, ['class'=>'form-control select2','id'=>'Asetkapal','style'=>'width: 100%']) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <br>
                                        <div class="form-group22">
                                            {{ Form::label('no_jo', 'No JO:') }}
                                            {{ Form::text('no_jo', null, ['class'=> 'form-control','id'=>'Jo','onkeypress'=>"return pulsar(event,this)"]) }}
                                        </div>
                                    </div>
                                    
                                <?php if (Auth()->user()->kode_company == '02') { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <br>
                                            {{ Form::label('Nomoo', 'No WO:') }}
                                            {{ Form::select('no_wo', $Workorder->sort(),null, ['class'=> 'form-control select2','id'=>'Wo2','placeholder'=>'','','style'=>'width: 100%']) }}
                                        </div>
                                    </div>
                                    <!--<div class="col-md-4">-->
                                    <!--    <div class="form-group">-->
                                    <!--        <br>-->
                                    <!--        {{ Form::label('Nomoo', 'No WO:') }}-->
                                    <!--        {{ Form::text('no_wo', null, ['class'=> 'form-control','id'=>'Wo2','placeholder'=>'','','style'=>'width: 100%']) }}-->
                                    <!--    </div>-->
                                    <!--</div>-->
                                <?php } ?>
                                    
                                <?php if (Auth()->user()->kode_company == '03') { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <br>
                                            {{ Form::label('cosot', 'Cost Center:') }}
                                            {{ Form::select('cost_center',$Costcenter,null, ['class'=>'form-control select2','id'=>'Cost2','style'=>'width: 100%','placeholder'=>'']) }}
                                        </div>
                                    </div>
                                <?php } ?>
                                    
                                    <div class="col-md-4">
                                        <br>
                                        <div class="form-group21">
                                        {{ Form::label('hmkm', 'HM/KM:') }}
                                        {{ Form::text('hmkm', null, ['class'=> 'form-control','id'=>'hmkm2', 'placeholder'=>'HourMeter', 'autocomplete'=>'off']) }}
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <br>
                                        <div class="form-group21">
                                        {{ Form::label('hhhh', 'KM:') }}
                                        {{ Form::text('km', null, ['class'=> 'form-control','id'=>'km2', 'placeholder'=>'KiloMeter', 'autocomplete'=>'off']) }}
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <br>
                                            {{ Form::label('Deskripsi', 'Keterangan:') }}
                                            {{ Form::textArea('deskripsi', null, ['class'=> 'form-control','rows'=>'4','id'=>'Deskripsi', 'autocomplete'=>'off']) }}
                                        </div>
                                    </div>
                                
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="row">
                                    {{ Form::submit('Update data', ['class' => 'btn btn-success crud-submit']) }}
                                    {{ Form::button('Close', ['class' => 'btn btn-danger','data-dismiss'=>'modal']) }}&nbsp;
                                </div>
                            </div>                          
                        {!! Form::close() !!}
                    </div>
                  </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->

    <div class="modal fade" id="addjurnalform2" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header" style=" height: 1%; border: none">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="box-body"> 
                    <div class="addform">
                        @include('errors.validation')
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="no_journal">No Journal:</label>
                                <input name="no_journal" id="nojournal2" style='width: 100%; border: none; background: transparent' readonly="true">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="journal_date">Journal Date:</label>
                                <input name="journal_date" id="journaldate2" style='width: 100%; border: none; background: transparent' readonly="true">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="reference">Reference:</label>
                                <input name="reference" id="reference2" style='width: 100%; border: none; background: transparent' readonly="true">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="entry_date">Entry Date:</label>
                                <input name="entry_date" id="entry2" style='width: 100%; border: none; background: transparent' readonly="true">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="updated_by">Updated By:</label>
                                <input name="updated_by" id="updated2" style='width: 100%; border: none; background: transparent' readonly="true">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status:</label>
                                <input name="status" id="status2" style='width: 100%; border: none; background: transparent' readonly="true">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <span class="pull-right"> 
                                <a href="#" target="_blank" id="printzoom3"><button type="button" class="btn btn-danger btn-xs print-button2" id="button7">PRINT <i class="fa fa-print"></i></button></a>
                            </span> 
                        </div> 
                    </div>
                </div>

                <div class="container-fluid">
                    <table class="table table-bordered table-striped table-hover" id="addjurnal-table2" width="100%" style="font-size: 12px;">
                        <thead>
                            <tr class="bg-warning">
                                <th>Account</th>
                                <th>Account Description</th>
                                <th>Cost Center</th>
                                <th>DB/CR</th>
                                <th>Debet</th>
                                <th>Credit</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-warning">
                                <th class="text-center" colspan="4">Total</th>
                                <th id="granddebit2">-</th>
                                <th id="grandkredit2">-</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            
                <div class="modal-footer">
                        
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

        <style type="text/css">

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

            .tombol4 {
                background-color: #149933;
                bottom: 116px;
            }

            .view-button {
                background-color: #1674c7;
                bottom: 186px;
            }

            .print-button {
                background-color: #F63F3F;
                bottom: 216px;
            }

            .zoom-button {
                background-color: #00E0FF;
                bottom: 246px;
            }
            
            /*.ttdigi-button {*/
            /*    bottom: 276px;*/
            /*}*/

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
            
            #mySidenav .ttdigi-button {
              right: -30px;
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
            @permission('update-pemakaian')
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editpemakaian" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>
            @endpermission

            @permission('delete-pemakaian')
            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapuspemakaian" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>
            @endpermission

            @permission('add-pemakaian')
            <a href="#" id="addpemakaian"><button type="button" class="btn btn-info btn-xs add-button" data-toggle="modal" data-target="">ADD <i class="fa fa-plus"></i></button></a>
            @endpermission

            @permission('post-pemakaian')
            <button type="button" class="btn btn-success btn-xs tombol1" id="button1">POST <i class="fa fa-bullhorn"></i></button>
            @endpermission

            @permission('unpost-pemakaian')
            <button type="button" class="btn btn-warning btn-xs tombol2" id="button2">UNPOST <i class="fa fa-undo"></i></button>
            @endpermission

            @permission('view-pemakaian')
            <button type="button" class="btn btn-primary btn-xs view-button" id="button3">VIEW <i class="fa fa-eye"></i></button>
            @endpermission

            @permission('print-pemakaian')
            <a href="#" target="_blank" id="printpemakaian"><button type="button" class="btn btn-danger btn-xs print-button" id="button6">PRINT <i class="fa fa-print"></i></button></a>
            @endpermission

            @permission('view-zoom')
            <button type="button" class="btn btn-info btn-xs zoom-button" id="detailjurnal2" data-toggle="modal" data-target="#addjurnalform2">
            <i class="fa fa-eye"></i> ZOOM JURNAL</button>
            @endpermission
        </div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.js"></script>
@stop

@push('css')

@endpush
@push('js')
    
    <script type="text/javascript">
        
        var x = 0;
        function showTime(){
            x = x + 1;
            if (x >= 10000){
                x = 0;
                refreshTable();
            }
        }
        
        document.onkeyup = function () {
          var e = e || window.event; // for IE to cover IEs window event-object
          if(e.altKey && e.which == 49) {
            $("#new-button").click();
          }
          
          if(e.which == 13) {
            $("#create-button").click();
          }
        }
        // setInterval(showTime, 1);

        function load(){
            limiter();
            startTime();
            comp1();
            $('.form-group1').hide();
            $('.tombol1').hide();
            $('.tombol2').hide();
            $('.add-button').hide();
            $('.hapus-button').hide();
            $('.edit-button').hide();
            $('.print-button').hide();
            $('.preview-button').hide();
            $('.ttdigi-button').hide();
            $('.view-button').hide();
            $('.zoom-button').hide();
            $('.form-group2').hide();
            $('.form-group3').hide();
            $('.form-group4').hide();
            $('.form-group5').hide();
            $('.form-group6').hide();
            $('.form-group8').hide();
            $('.form-group9').hide();
            $('.form-group13').hide();
            $('.form-group20').hide();
        }

        function hitungjurnal(){
            swal({
                title: "Update Jurnal?",
                text: "Ledger",
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
                        url:'{!! route('pemakaian.hitungjurnal') !!}',
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
                        url:'{!! route('pemakaian.getkode') !!}',
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
        
        function comp1(){
            var compan = $("#company1").val();
            var compan2 = $("#company2").val();
            if (compan == '02' || compan2 == '02' || compan == '05' || compan2 == '05'){
                $('.form-group22').show();
                $('.form-group23').show();
            }else{
                $('.form-group22').hide();
                $('.form-group23').hide();
            }
        }

        $(function() {
            $('#data-table').DataTable({
            "bPaginate": true,
            "bFilter": true,
            "scrollY": 280,
            "scrollX": 400,
            "pageLength":100,
            processing: true,
            serverSide: true,
            ajax: '{!! route('pemakaian.data') !!}',
            fnRowCallback: function (row, data, iDisplayIndex, iDisplayIndexFull) {
                if (data['status'] == "OPEN") {
                    $('td', row).css('background-color', '#ffdbd3');
                }
                else if (data['status'] == "ONGOING1" || data['status'] == "ONGOING") {
                    $('td', row).css('background-color', '#6c11d4');
                }
            },
           
            columns: [
                { data: 'no_pemakaian', name: 'no_pemakaian' },
                { data: 'tanggal_pemakaian', name: 'tanggal_pemakaian' },
                { data: 'mobil.nopol', name: 'mobil.nopol',defaultContent:''},
                { data: 'no_asset_mobil', name: 'no_asset_mobil' },
                { data: 'alat.nama_alat', name: 'alat.nama_alat',defaultContent:''},
                { data: 'no_asset_alat', name: 'no_asset_alat' },
                { data: 'kapal.nama_kapal', name: 'kapal.nama_kapal',defaultContent:''},
                { data: 'no_asset_kapal', name: 'no_asset_kapal' },
                { data: 'deskripsi', name: 'deskripsi' },
                { data: 'pemakai', name: 'pemakai' },
                { data: 'total_item', name: 'total_item'},
                { data: 'type', name: 'type' },
                { data: 'hmkm', 
                    render: function( data, type, full ) {
                    return formatNumber(data); }
                },
                { data: 'km', 
                    name: 'km'
                },
                { data: 'no_jo', name: 'no_jo' },
                { data: 'kode_lokasi', 
                    render: function( data, type, full ) {
                    return formatNomor(data); }
                },
                { data: 'status', 
                    render: function( data, type, full ) {
                    return formatStatus(data); }
                },
                { data: 'no_wo', name: 'no_wo' },
                { data: 'no_journal', name: 'no_journal' },
            ]
            });
        });
        
        TablePreview = $("#preview-table").DataTable({
            "bPaginate": false,
            "bInfo": false,
            "bFilter": false,
            data:[],
            columns: [
                { data: 'produk.nama_produk', name: 'produk.nama_produk' },
                { data: 'partnumber', name: 'partnumber' },
                { data: 'qty', name: 'qty' },
                { data: 'kode_satuan', name: 'kode_satuan' },
            ],
        });

        function tablepreview(kode){
            $.ajax({
                url: '{!! route('pemakaian.getDatapreview') !!}',
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

        Table4 = $("#addjurnal-table2").DataTable({
            "bPaginate": false,
            "bFilter": false,
            "order": [[ 3, "asc" ]],
            data:[],
            footerCallback: function ( row, data, start, end, display ) {
                var api = this.api(), data;
            
                // Remove the formatting to get integer data for summation
                var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                    i : 0;
                };
            
                // Total over this page
                granddebit = api
                    .column( 4 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );

                grandkredit = api
                    .column( 5 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                            
                // Update footer
                $( api.column( 4 ).footer() ).html(
                    formatRupiah(granddebit)
                );

                $( api.column( 5 ).footer() ).html(
                    formatRupiah(grandkredit)
                );
            },
            
            columns: [
                { data: 'account', name: 'account' },
                { data: 'ac_description', name: 'ac_description' },
                { data: 'costcenter.kode_costcenter', defaultContent:'-' },
                { data: 'db_cr', 
                    render: function( data, type, full ) {
                    return format_dk(data); } 
                },
                { data: 'debit', 
                    render: function( data, type, full ) {
                    return formatNumber2(data); } 
                },
                { data: 'kredit', 
                    render: function( data, type, full ) {
                    return formatNumber2(data); } 
                },       
            ],
        });

        function format_dk(n) {
            if(n == 'D'){
                var stat = "<span style='color:#0eab25'><b>DEBIT</b></span>";
            }else{
                var stat = "<span style='color:#c91a1a'><b>KREDIT</b></span>";
            }
            return stat;
        }

        function formatNumber2(m) {
            if(m == null){
                return '';
            }else{
                return m.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
        }
        
        function formatNumber(n) {
            if(n == null){
                return 0;
            }else{
                return n.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
        }   

        function formatStatus(n) {
            if(n != 'POSTED'){
                return n;
            }else{
                var stat = "<span style='color:#0eab25'><b>POSTED</b></span>";
                return n.replace(/POSTED/, stat);
            }
        }

        function formatNomor(n) {
            if(n == 'HO'){
                var stat = "<span style='color:#0275d8'><b>HO</b></span>";
                return n.replace(/HO/, stat);
            }else{
                var str = n;
                var result = str.fontcolor( "#eb4034" );
                return result;
            }
        }

        function hanyaHuruf(e, decimal) {
            
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
            if (!(("&").indexOf(keychar) > -1)) {
                return true;
            } else
            if (decimal && (keychar == ".")) {
                return true;
            } else return false;
        }
        

        function pakai() {
            var type = $("#type").val();
            if (type == 0) {
            }else if(type == 'Mobil'){
                $('.form-group3').hide();
                $('.form-group1').show();
                $('.form-group4').show();
                $('.form-group5').hide();
                $('.form-group13').hide();
                $('.form-group6').hide();
                $('.form-group7').show();
                $('.form-group8').hide();
                $('.form-group9').hide();
                $('.form-group20').show();
                $("#hmkm1").val('');
                $("#Alat1").val('').trigger('change');
                $("#noaset2").val('').trigger('change');
                $('#Aset2').val('').trigger('change');
                $("#Kapal1").val('').trigger('change');
                $("#noaset3").val('').trigger('change');
                $('#Aset3').val('').trigger('change');
                $('#ADD :input').attr('disabled', false);  
                document.getElementById("hmkm1").required = true;
                document.getElementById("Nopol1").required = true; 
                document.getElementById("Aset1").required = true;    
                document.getElementById("Alat1").required = false; 
                document.getElementById("Aset2").required = false;    
                document.getElementById("Kapal1").required = false; 
                document.getElementById("Aset3").required = false;     
                document.getElementById("Pemakai1").disabled = false;
                document.getElementById("Deskripsi1").disabled = false;
                document.getElementById("Deskripsi1").readonly = false;
            }else if(type == 'Alat'){
                $('.form-group3').hide();
                $('.form-group13').show();
                $('.form-group4').hide();
                $('.form-group5').hide();
                $('.form-group1').hide();
                $('.form-group6').hide();
                $('.form-group7').show();
                $('.form-group8').show();
                $('.form-group9').hide();
                $('.form-group20').show();
                $("#hmkm1").val('');
                $("#Nopol1").val('').trigger('change');
                $("#noaset1").val('').trigger('change');
                $("#Kapal1").val('').trigger('change');
                $("#noaset3").val('').trigger('change');
                $('#Aset1').val('').trigger('change');
                $('#Aset3').val('').trigger('change');
                $('#ADD :input').attr('disabled', false);        
                document.getElementById("hmkm1").required = true;
                document.getElementById("Nopol1").required = false; 
                document.getElementById("Aset1").required = false;    
                document.getElementById("Alat1").required = true; 
                document.getElementById("Aset2").required = true;    
                document.getElementById("Kapal1").required = false; 
                document.getElementById("Aset3").required = false;          
                document.getElementById("Pemakai1").disabled = false;
                document.getElementById("Deskripsi1").disabled = false;
                document.getElementById("Deskripsi1").readonly = false;
            }
            else if(type == 'Kapal'){
                $('.form-group3').hide();
                $('.form-group5').show();
                $('.form-group4').hide();
                $('.form-group1').hide();
                $('.form-group13').hide();
                $('.form-group6').hide();
                $('.form-group7').show();
                $('.form-group8').hide();
                $('.form-group9').show();
                $('.form-group20').show();
                $("#hmkm1").val('');
                $("#Alat1").val('').trigger('change');
                $("#noaset2").val('').trigger('change');
                $("#Nopol1").val('').trigger('change');
                $("#noaset1").val('').trigger('change');
                $('#Aset2').val('').trigger('change');
                $('#Aset1').val('').trigger('change');
                $('#ADD :input').attr('disabled', false);
                document.getElementById("hmkm1").required = true;
                document.getElementById("Nopol1").required = false; 
                document.getElementById("Aset1").required = false;    
                document.getElementById("Alat1").required = false; 
                document.getElementById("Aset2").required = false;    
                document.getElementById("Kapal1").required = true; 
                document.getElementById("Aset3").required = true; 
                document.getElementById("Tanggal1").disabled = false;                                    
                document.getElementById("Pemakai1").disabled = false;
                document.getElementById("Deskripsi1").disabled = false;
                document.getElementById("Deskripsi1").readonly = false;
            }
            else if(type == 'Other'){
                $('.form-group3').hide();
                $('.form-group5').hide();
                $('.form-group4').hide();
                $('.form-group1').hide();
                $('.form-group13').hide();
                $('.form-group6').hide();
                $('.form-group7').show();
                $('.form-group8').hide();
                $('.form-group9').hide();
                $('.form-group20').hide();
                $("#hmkm1").val('');
                $("#Nopol1").val('').trigger('change');
                $("#noaset1").val('').trigger('change');
                $("#Alat1").val('').trigger('change');
                $("#noaset2").val('').trigger('change');
                $("#Kapal1").val('').trigger('change');
                $("#noaset3").val('').trigger('change');
                $('#Aset1').val('').trigger('change');
                $('#Aset2').val('').trigger('change');
                $('#Aset3').val('').trigger('change');
                $('#ADD :input').attr('disabled', false);
                document.getElementById("hmkm1").required = false;
                document.getElementById("Tanggal1").disabled = false;              
                document.getElementById("Pemakai1").disabled = false;   
                document.getElementById("Nopol1").required = false; 
                document.getElementById("Aset1").required = false;    
                document.getElementById("Alat1").required = false; 
                document.getElementById("Aset2").required = false;    
                document.getElementById("Kapal1").required = false; 
                document.getElementById("Aset3").required = false;    
            }else {
                $('.form-group3').hide();
                $('.form-group5').hide();
                $('.form-group4').hide();
                $('.form-group1').hide();
                $('.form-group13').hide();
                $('.form-group6').hide();
                $('.form-group7').show();
                $('.form-group8').hide();
                $('.form-group9').hide();
                $('.form-group20').hide();
                $("#hmkm1").val('');
                $("#Nopol1").val('').trigger('change');
                $("#noaset1").val('').trigger('change');
                $("#Alat1").val('').trigger('change');
                $("#noaset2").val('').trigger('change');
                $("#Kapal1").val('').trigger('change');
                $("#noaset3").val('').trigger('change');
                $('#Aset1').val('').trigger('change');
                $('#Aset2').val('').trigger('change');
                $('#Aset3').val('').trigger('change');
                $('#ADD :input').attr('disabled', false);
                document.getElementById("hmkm1").required = false;
                document.getElementById("Tanggal1").disabled = false;              
                document.getElementById("Pemakai1").disabled = false;   
                document.getElementById("Nopol1").required = false; 
                document.getElementById("Aset1").required = false;    
                document.getElementById("Alat1").required = false; 
                document.getElementById("Aset2").required = false;    
                document.getElementById("Kapal1").required = false; 
                document.getElementById("Aset3").required = false;
            }
        }

        function pakai2()
        {
            var optionVal = $("#typeedit").val();
            var optionValmobil = $("#Nopol").val();
            var optionValalat = $("#Alat").val();
            if(optionVal == 'Mobil'){
                $('.form-group2').show();
                document.getElementById("Tanggal").disabled = false;
                document.getElementById("nopols1").style.display="block";
                document.getElementById("asetnopols1").style.display="block";
                document.getElementById("alats1").style.display="none";
                document.getElementById("asetalats1").style.display="none";
                document.getElementById("kapals1").style.display="none";
                document.getElementById("asetkapals1").style.display="none";
                document.getElementById("Pemakai").disabled = false;
                document.getElementById("Deskripsi").disabled = false;
                $('.form-group21').show();
            }else if(optionVal == 'Alat') {
                  $('.form-group2').show();
                  document.getElementById("Tanggal").disabled = false;
                  document.getElementById("nopols1").style.display="none";
                  document.getElementById("asetnopols1").style.display="none";
                  document.getElementById("alats1").style.display="block";
                  document.getElementById("asetalats1").style.display="block";
                  document.getElementById("kapals1").style.display="none";
                  document.getElementById("asetkapals1").style.display="none";
                  document.getElementById("Pemakai").disabled = false;
                  document.getElementById("Deskripsi").disabled = false;
                  $('.form-group21').show();
            }else if(optionVal == 'Kapal') {
                    $('.form-group2').show();
                  document.getElementById("Tanggal").disabled = false;
                  document.getElementById("nopols1").style.display="none";
                  document.getElementById("asetnopols1").style.display="none";
                  document.getElementById("alats1").style.display="none";
                  document.getElementById("asetalats1").style.display="none";
                  document.getElementById("kapals1").style.display="block";
                  document.getElementById("asetkapals1").style.display="block";
                  document.getElementById("Pemakai").disabled = false;
                  document.getElementById("Deskripsi").disabled = false;
                  $('.form-group21').show();
            }else{
                  $('.form-group2').show();
                  document.getElementById("Tanggal").disabled = false;
                  document.getElementById("Tanggal").disabled = false;
                  document.getElementById("nopols1").style.display="none";
                  document.getElementById("asetnopols1").style.display="none";
                  document.getElementById("alats1").style.display="none";
                  document.getElementById("asetalats1").style.display="none";
                  document.getElementById("kapals1").style.display="none";
                  document.getElementById("asetkapals1").style.display="none";
                  document.getElementById("Pemakai").disabled = false;
                  document.getElementById("Deskripsi").disabled = false;
                  $('.form-group21').hide();
            }    
        }

        $(function() {
            $('#child-table').DataTable({
                scrollY: 200,
                scrollX: true
            
            });
        });
        
        function limiter() {
            $.ajax({
                        url: '{!! route('pemakaian.limitos') !!}',
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


        function formatRupiah(angka, prefix='Rp'){
           
            var rupiah = angka.toLocaleString(
                undefined, // leave undefined to use the browser's locale,
                // or use a string like 'en-US' to override it.
                { minimumFractionDigits: 0 }
            );
            return rupiah;
           
        }

        function createTable(result){

        var total_qty = 0;
        var total_pakai = 0;
        var total_harga = 0;
        var grand_total = 0;

        $.each( result, function( key, row ) {
            total_qty += row.qty;
            harga = row.harga;
            qty = row.qty;
            qty_retur = 0;
            total_pakai = harga * qty;
            total_harga += total_pakai;
            grand_total = formatRupiah(total_harga);

        });

        var my_table = "";

        $.each( result, function( key, row ) {
                    my_table += "<tr>";
                    my_table += "<td>"+row.produk+"</td>";
                    my_table += "<td>"+row.partnumber+"</td>";
                    my_table += "<td>"+row.satuan+"</td>";
                    my_table += "<td>"+row.qty+"</td>";
                    if (row.qty_retur == null)
                    {
                        qty_retur = 0;
                    }
                    else 
                    {
                        qty_retur = row.qty_retur;
                    }
                    my_table += "<td>"+qty_retur+"</td>";
                    @permission('read-hpp')
                    my_table += "<td>Rp "+formatRupiah(row.harga)+"</td>";
                    my_table += "<td>Rp "+row.subtotal+"</td>";
                    @endpermission
                    my_table += "<td>"+row.keterangan+"</td>";
                    my_table += "</tr>";
            });

            my_table = '<table id="table-fixed" class="table table-bordered table-hover" cellpadding="5" cellspacing="0" border="1" style="padding-left:50px; font-size:12px">'+ 
                        '<thead>'+
                           ' <tr class="bg-info">'+
                                '<th>Produk</th>'+
                                '<th>Partnumber</th>'+
                                '<th>Satuan</th>'+
                                '<th>Qty</th>'+
                                '<th>Qty Retur</th>'+
                                @permission('read-hpp')
                                '<th>Harga</th>'+
                                '<th>Subtotal</th>'+
                                @endpermission
                                '<th>Keterangan</th>'+
                            '</tr>'+
                        '</thead>'+
                        '<tbody>' + my_table + '</tbody>'+
                       ' <tfoot>'+
                            '<tr class="bg-info">'+
                                '<th class="text-center" colspan="3">Total</th>'+
                                '<th>'+total_qty+'</th>'+
                                @permission('read-hpp')
                                '<th class="text-center" colspan="2"></th>'+
                                '<th>Rp '+grand_total+'</th>'+
                                @endpermission
                                '<th></th>'+
                            '</tr>'+
                            '</tfoot>'+
                        '</table>';
            return my_table;          
        
        }

 //FUNGSI BREAKDOWN
        $(document).ready(function() {
                    var table = $('#data-table').DataTable();
                
                    $('#data-table tbody').on( 'click', 'td.details-control', function () {
                        var tr = $(this).closest('tr');
                        var kode = tr.find('td:eq(1)').text();
                        var row = table.row( tr );
                        if ( $(this).hasClass('selected bg-gray') ) {
                            $(this).removeClass('selected bg-gray');
                        } else {
                            table.$('tr.selected').removeClass('selected bg-gray');
                            $(this).addClass('selected');
                        }
                        $.ajax({
                            url: '{!! route('pemakaian.showdetail') !!}',
                            type: 'POST',
                            data : {
                                'id': kode
                            },
                            success: function(result) {
                                if(result.title == 'Gagal'){
                                    $.notify(result.message);
                                }else{
                                    if ( row.child.isShown() ) {
                                    row.child.hide();
                                    tr.removeClass('shown');
                                }
                                else {
                                    var len = result.length;
                                    for (var i = 0; i < len; i++) {
                                        // alert(result[i].produk);
                                    }

                                    row.child( createTable(result) ).show();
                                    // row.child( format(result) ).show();
                                    tr.addClass('shown');
                                }
                            }
                            }
                        });

                        
                    } );
        } );


        $('#editform').on('show.bs.modal', function () {
              var optionVal = $("#typeedit").val();
              var optionValmobil = $("#Nopol").val();
              var optionValalat = $("#Alat").val();
               if(optionVal == 'Mobil')
               {
                  $('.form-group2').show();
                  document.getElementById("Tanggal").disabled = false;
                  document.getElementById("nopols1").style.display="block";
                  document.getElementById("asetnopols1").style.display="block";
                  document.getElementById("alats1").style.display="none";
                  document.getElementById("asetalats1").style.display="none";
                  document.getElementById("kapals1").style.display="none";
                  document.getElementById("asetkapals1").style.display="none";
                  document.getElementById("Pemakai").disabled = false;
                  document.getElementById("Deskripsi").disabled = false;
                  $('.form-group21').show();
               } 
               else if(optionVal == 'Alat') 
               {
                  $('.form-group2').show();
                  document.getElementById("Tanggal").disabled = false;
                  document.getElementById("nopols1").style.display="none";
                  document.getElementById("asetnopols1").style.display="none";
                  document.getElementById("alats1").style.display="block";
                  document.getElementById("asetalats1").style.display="block";
                  document.getElementById("kapals1").style.display="none";
                  document.getElementById("asetkapals1").style.display="none";
                  document.getElementById("Pemakai").disabled = false;
                  document.getElementById("Deskripsi").disabled = false;
                  $('.form-group21').show();
               }
               else if(optionVal == 'Kapal') 
               {
                    $('.form-group2').show();
                  document.getElementById("Tanggal").disabled = false;
                  document.getElementById("nopols1").style.display="none";
                  document.getElementById("asetnopols1").style.display="none";
                  document.getElementById("alats1").style.display="none";
                  document.getElementById("asetalats1").style.display="none";
                  document.getElementById("kapals1").style.display="block";
                  document.getElementById("asetkapals1").style.display="block";
                  document.getElementById("Pemakai").disabled = false;
                  document.getElementById("Deskripsi").disabled = false;
                  $('.form-group21').show();
               }
               else
               {
                  $('.form-group2').show();
                  document.getElementById("Tanggal").disabled = false;
                  document.getElementById("Tanggal").disabled = false;
                  document.getElementById("nopols1").style.display="none";
                  document.getElementById("asetnopols1").style.display="none";
                  document.getElementById("alats1").style.display="none";
                  document.getElementById("asetalats1").style.display="none";
                  document.getElementById("kapals1").style.display="none";
                  document.getElementById("asetkapals1").style.display="none";
                  document.getElementById("Pemakai").disabled = false;
                  document.getElementById("Deskripsi").disabled = false;
                  $('.form-group21').hide();
               }    
        })

        // Fungsi POST-UNPOST,VIEW DETAIL,DAN PRINT
        $(document).ready(function() {
            var table = $('#data-table').DataTable();
            var post = document.getElementById("button1");
            var unpost = document.getElementById("button2");

            $('#data-table tbody').on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected bg-gray text-bold') ) {
                    $(this).removeClass('selected bg-gray text-bold');
                    $('.tombol1').hide();
                    $('.tombol2').hide();
                    $('.add-button').hide();
                    $('.hapus-button').hide();
                    $('.edit-button').hide();
                    $('.print-button').hide();
                    $('.preview-button').hide();
                    $('.ttdigi-button').hide();
                    $('.view-button').hide();   
                    $('.zoom-button').hide();  
                    
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
                    var no_pemakaian = data['no_pemakaian'];
                    var no_journal = data['no_journal'];
                    
                    var add = $("#addpemakaian").attr("href",window.location.href+"/"+no_pemakaian+"/detail");
                    var print = $("#printpemakaian").attr("href",window.location.href+"/export2?no_pemakaian="+no_pemakaian);
                    var print2 = $("#printzoom3").attr("href",window.location.href+"/exportpdf3?no_journal="+no_journal + "&no_pemakaian="+no_pemakaian);
                    var status = data['status'];
                    var item = data['total_item'];
                    
                    document.getElementById("NomorTTD").innerHTML = no_pemakaian;
                    
                    $.ajax({
                        url: '{!! route('pemakaian.previewpo') !!}',
                        type: 'GET',
                        data : {
                            'id': no_pemakaian
                        },
                        success: function(result) {
                            document.getElementById("PreviewNomor").innerHTML = result.no_pemakaian;
                            document.getElementById("PreviewTanggal").innerHTML = result.tanggal_pemakaian;
                            document.getElementById("PreviewTipe").innerHTML = result.type;
                            document.getElementById("PreviewAlat").innerHTML = result.alat;
                            document.getElementById("PreviewAsset").innerHTML = result.no_asset;
                            document.getElementById("PreviewPemakai").innerHTML = result.pemakai;
                            document.getElementById("PreviewJo").innerHTML = result.nojo;
                            document.getElementById("PreviewWo").innerHTML = result.nowo;
                            document.getElementById("PreviewNote").innerHTML = result.note;
                            tablepreview(no_pemakaian);
                        }
                    });
                    
                    $('#CreateBy1').val(data['created_by']);
                    $('#CreateAt1').val(data['created_at']);
                    $('#UpdateBy1').val(data['updated_by']);
                    $('#UpdateAt1').val(data['updated_at']);
                    $.ajax({
                        url: '{!! route('pemakaian.historia') !!}',
                        type: 'GET',
                        data : {
                            'id': no_pemakaian
                        },
                        success: function(result) {
                            $('#PostedBy1').val(result.post);
                            $('#UnpostBy1').val(result.unpost);
                        }
                    });
                    
                    $.ajax({
                        url: '{!! route('pemakaian.grandios') !!}',
                        type: 'GET',
                        data : {
                            'no_pemakaian': no_pemakaian
                        },
                        success: function(results) {
                            $('#Grandios1').val(results.grand_total);
                        }
                    });

                    var pengguna = $('#NamaUser1').val();
                    var grand_total = parseInt($('#Grandios1').val());
                    
                    if(status == 'POSTED' && item > 0){
                        $('.tombol1').hide();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').show();
                        $('.preview-button').show();
                        $('.ttdigi-button').show();
                        $('.view-button').show();
                        $('.zoom-button').show();
                        
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
                    }else if(status =='OPEN' && item > 0){
                        $('.tombol1').show();
                        $('.tombol2').hide();
                        $('.add-button').show();
                        $('.hapus-button').hide();
                        $('.edit-button').show();
                        $('.print-button').hide();
                        $('.preview-button').hide();
                        $('.ttdigi-button').hide();
                        $('.view-button').show();
                        $('.zoom-button').hide();
                    }else if(status =='OPEN' && item == null || item == 0){
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.add-button').show();
                        $('.hapus-button').show();
                        $('.edit-button').show();
                        $('.print-button').hide();
                        $('.preview-button').hide();
                        $('.ttdigi-button').hide();
                        $('.view-button').hide();
                        $('.zoom-button').hide();
                    }else if (status == 'RETUR') {
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.preview-button').hide();
                        $('.print-button').hide();
                        $('.ttdigi-button').hide();
                        $('.view-button').show();
                        $('.zoom-button').hide();
                    }
                }
            } );
            
            $('#button4').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_pemakaian = data['no_pemakaian'];
                swal({
                    title: "Post Ulang?",
                    text: no_pemakaian,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, post it!",
                    cancelButtonText: "No, cancel!",
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
                            
                    // alert( table.rows('.selected').data().length +' row(s) selected' );
                        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                        $.ajax({
                            url: '{!! route('pemakaian.postingulang') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pemakaian
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
                                else if (result.success === false) {
                                  swal({
                                      title: 'Error',
                                      text: result.message,
                                      type: 'error',
                                  })
                                    refreshTable();
                                }
                                else{
                                  swal({
                                      title: 'Error',
                                      text: 'POSTING gagal! Stok tidak cukup.',
                                      type: 'error',
                                  })
                                    refreshTable();
                                }
                            },
                            error : function () {
                              swal({
                                  title: 'Oops...',
                                  text: 'Post Gagal!',
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
           
        
            $('#button1').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_pemakaian = data['no_pemakaian'];
                swal({
                    title: "Post?",
                    text: no_pemakaian,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, post it!",
                    cancelButtonText: "No, cancel!",
                    reverseButtons: !0
                    }).then(function (e) {
                        if (e.value === true) {
                            swal({
                            title: "<b>Proses Sedang Berlangsung !! Harap Menunggu...</b>",
                            type: "warning",
                            showCancelButton: false,
                            showConfirmButton: false,
                            allowOutsideClick: false
                            })
                            
                    // alert( table.rows('.selected').data().length +' row(s) selected' );
                        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                        $.ajax({
                            url: '{!! route('pemakaian.posting') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pemakaian
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
                                else if (result.success === false) {
                                  swal({
                                      title: 'Error',
                                      text: result.message,
                                      type: 'error',
                                  })
                                    refreshTable();
                                }
                                else{
                                  swal({
                                      title: 'Error',
                                      text: 'POSTING gagal! Stok tidak cukup.',
                                      type: 'error',
                                  })
                                    refreshTable();
                                }
                            },
                            error : function () {
                              swal({
                                  title: 'Oops...',
                                  text: 'Post Gagal!',
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
                var data = $('#data-table').DataTable().row(select).data();
                var no_pemakaian = data['no_pemakaian'];
                swal({
                    title: "Unpost?",
                    text: no_pemakaian,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, unpost it!",
                    cancelButtonText: "No, cancel!",
                    reverseButtons: !0
                    }).then(function (e) {
                        if (e.value === true) {
                            swal({
                            title: "<b>Proses Sedang Berlangsung !! Harap Menunggu...</b>",
                            type: "warning",
                            showCancelButton: false,
                            showConfirmButton: false,
                            allowOutsideClick: false
                            })
                        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                        $.ajax({
                            url: '{!! route('pemakaian.unposting') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pemakaian
                            },
                            success: function(result) {
                                if (result.success === true) {
                                    swal(
                                    'Unposted!',
                                    'Your file has been unposted.',
                                    'success'
                                    )
                                    refreshTable();
                                }
                                else{
                                  swal({
                                      title: 'ERROR!!',
                                      text: result,
                                      type: 'error',
                                  })
                                    refreshTable();
                                }
                            },
                            error : function () {
                              swal({
                                  title: 'Oops...',
                                  text: 'Unpost Gagal!',
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

            $('#addjurnalform2').on('show.bs.modal', function () {
                cekjurnal2();
            })

            $('#detailjurnal2').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_journal = data['no_journal'];
                $.ajax({
                    url: '{!! route('pemakaian.getDatajurnal2') !!}',
                    type: 'GET',
                    data : {
                        'id': no_journal,
                    },
                    success: function(result) {
                        Table4.clear().draw();
                        Table4.rows.add(result).draw();
                        $('#nojournal2').val(no_journal);
                        $('#addjurnalform2').modal('show');
                    }
                });
            });
            
            var openRows = new Array();

            function cekjurnal2(){
                var no_jurnal = $('#nojournal2').val();
                $.ajax({
                    url:'{!! route('pemakaian.cekjurnal2') !!}',
                    type:'POST',
                    data : {
                            'no_journal':no_jurnal,
                        },
                    success: function(result) {
                        $('#journaldate2').val(result.journal_date);
                        $('#reference2').val(result.reference);
                        $('#entry2').val(result.created_at);
                        $('#updated2').val(result.updated_by);
                        $('#status2').val(result.status);
                    },
                });
            }
            
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
                var no_pemakaian = data['no_pemakaian'];
                $.ajax({
                    url: '{!! route('pemakaian.ttd_buat') !!}',
                    type: 'POST',
                    data : {
                        'no': no_pemakaian,
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

            $(document).on('click', '#btn-terima', function() {
                var mycanvas = document.getElementById('canvas');
                var img = mycanvas.toDataURL("image/png");

                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_pemakaian = data['no_pemakaian'];
                $.ajax({
                    url: '{!! route('pemakaian.ttd_terima') !!}',
                    type: 'POST',
                    data : {
                        'no': no_pemakaian,
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

            $('#button3').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_pemakaian = data['no_pemakaian'];
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('pemakaian.showdetail') !!}',
                    type: 'POST',
                    data : {
                        'id': no_pemakaian
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
            } );
            
            $('#editpemakaian').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_pemakaian = data['no_pemakaian'];
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('pemakaian.edit_pemakaian') !!}',
                    type: 'POST',
                    data : {
                        'id': no_pemakaian
                    },
                    success: function(results) {
                        $('#Pemakaian').val(results.no_pemakaian);
                        $('#Pemakai').val(results.pemakai);
                        $('#typeedit').val(results.type).trigger('change');
                        $('#Tanggal').val(results.tanggal_pemakaian);
                        $('#Alat').val(results.kode_alat).trigger('change');
                        $('#Asetalat').val(results.no_asset_alat).trigger('change');
                        $('#Nopol').val(results.kode_mobil).trigger('change');
                        $('#Asetmobil').val(results.no_asset_mobil).trigger('change');
                        $('#Kapal').val(results.kode_kapal).trigger('change');
                        $('#Asetkapal').val(results.no_asset_kapal).trigger('change');
                        $('#Status').val(results.status);
                        $('#Type').val(results.type);
                        $('#Deskripsi').val(results.deskripsi);
                        $('#hmkm2').val(results.hmkm);
                        $('#km2').val(results.km);
                        $('#Jo').val(results.no_jo);
                        $('#Wo2').val(results.no_wo).trigger('change');
                        $('#Cost2').val(results.cost_center).trigger('change');
                        $('#editform').modal('show');
                    }
                });
            });

            $('#hapuspemakaian').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_pemakaian = data['no_pemakaian'];
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
                            url: '{!! route('pemakaian.hapus_pemakaian') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pemakaian
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
        } );

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
            $('.add-button').hide();
            $('.hapus-button').hide();
            $('.edit-button').hide();
            $('.print-button').hide();
            $('.preview-button').hide();
            $('.ttdigi-button').hide();
            $('.view-button').hide();
            $('.zoom-button').hide();
        }

        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });

        $('.modal-dialog').resizable({
    
        });
        
        function getwoalat(){
            var work_order = $('#Wo1').val();
             $.ajax({
                url:'{!! route('pemakaian.getwoalat') !!}',
                type:'POST',
                data : {
                        'no_wo': work_order,
                    },
                success: function(result) {
                        console.log(result);
                        $('#Alat1').val(result.kode_alat).trigger('change');
                        $('#noaset2').val(result.no_asset_alat).trigger('change');
                    },
            });
        }

        function getasetmobil(){
            var nopol= $('#Nopol1').val();

            $.ajax({
                url:'{!! route('pemakaian.getmobil') !!}',
                type:'POST',
                data : {
                        'id': nopol,
                    },
                success: function(result) {
                        $('#noaset1').val(result.no_asset_mobil).trigger('change');
                        $('#Aset1').val(result.no_asset_mobil);
                    },
            });
        }

        function getasetalat(){
            var noalat= $('#Alat1').val();

            $.ajax({
                url:'{!! route('pemakaian.getalat') !!}',
                type:'POST',
                data : {
                        'id': noalat,
                    },
                success: function(result) {
                        $('#noaset2').val(result.no_asset_alat).trigger('change');
                        $('#Aset2').val(result.no_asset_alat);
                    },
            });
        }

        function getasetkapal(){
            var nokapal= $('#Kapal1').val();

            $.ajax({
                url:'{!! route('pemakaian.getkapal') !!}',
                type:'POST',
                data : {
                        'id': nokapal,
                    },
                success: function(result) {
                        $('#noaset3').val(result.no_asset_kapal).trigger('change');
                        $('#Aset3').val(result.no_asset_kapal);
                    },
            });
        }

        function getasetmobil2(){
            var nopol= $('#Nopol').val();

            $.ajax({
                url:'{!! route('pemakaian.getmobil2') !!}',
                type:'POST',
                data : {
                        'id': nopol,
                    },
                success: function(result) {
                        $('#Asetmobil').val(result.no_asset_mobil).trigger('change');
                    },
            });
        }

        function getasetalat2(){
            var noalat= $('#Alat').val();

            $.ajax({
                url:'{!! route('pemakaian.getalat2') !!}',
                type:'POST',
                data : {
                        'id': noalat,
                    },
                success: function(result) {
                        $('#Asetalat').val(result.no_asset_alat).trigger('change');
                    },
            });
        }

        function getasetkapal2(){
            var nokapal= $('#Kapal').val();

            $.ajax({
                url:'{!! route('pemakaian.getkapal2') !!}',
                type:'POST',
                data : {
                        'id': nokapal,
                    },
                success: function(result) {
                        $('#Asetkapal').val(result.no_asset_kapal).trigger('change');
                    },
            });
        }
        
        function getkodealat2(){
            var asetalat= $('#Asetalat').val();

            $.ajax({
                url:'{!! route('pemakaian.getkodealat2') !!}',
                type:'POST',
                data : {
                        'id': asetalat,
                    },
                success: function(result) {
                        $('#Alat').val(result.kode_alat).trigger('change');
                    },
            });
        }
        
        function getkodemobil2(){
            var asetmobil= $('#Asetmobil').val();

            $.ajax({
                url:'{!! route('pemakaian.getkodemobil2') !!}',
                type:'POST',
                data : {
                        'id': asetmobil,
                    },
                success: function(result) {
                        $('#Nopol').val(result.nopol).trigger('change');
                    },
            });
        }
        
        function getkodekapal2(){
            var asetkapal= $('#Asetkapal').val();

            $.ajax({
                url:'{!! route('pemakaian.getkodekapal2') !!}',
                type:'POST',
                data : {
                        'id': asetkapal,
                    },
                success: function(result) {
                        $('#Kapal').val(result.kode_kapal).trigger('change');
                    },
            });
        }

        function getnopol(){
            var asetmobil= $('#Aset1').val();

            $.ajax({
                url:'{!! route('pemakaian.getnopol') !!}',
                type:'POST',
                data : {
                        'id': asetmobil,
                    },
                success: function(result) {
                        $('#Nopol1').val(result.nopol);
                    },
            });
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
            
        $('#CALC_JURNAL').submit(function (e) {
            swal({
                title: "<b>Proses Sedang Berlangsung</b>",
                type: "warning",
                showCancelButton: false,
                showConfirmButton: false,
                allowOutsideClick: false
            })
            e.preventDefault();
            var registerForm = $("#CALC_JURNAL");
            var formData = registerForm.serialize();
            $.ajax({
                url:'{!! route('pemakaian.kalkulasi_jurnal') !!}',
                type:'POST',
                data:formData,
                success:function(data) {
                    if (data.success === true) {
                        swal("Berhasil!", data.message, "success");
                    } else {
                        swal("Gagal!", data.message, "error");                        
                    }
                },
            });
        });

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
                    url:'{!! route('pemakaian.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        $('#Tanggal1').val(today);
                        $('#Pemakai1').val('');
                        $('#Nopol1').val('').trigger('change');
                        $('#Alat1').val('').trigger('change');
                        $('#Aset1').val('');
                        $('#Aset2').val('');
                        $('#Aset3').val('');
                        $('#noaset1').val('').trigger('change');
                        $('#noaset2').val('').trigger('change');
                        $('#noaset3').val('').trigger('change');
                        $('#Kapal1').val('').trigger('change');
                        $('#type').val('').trigger('change');
                        $('#Jo1').val(0);
                        $('#Deskripsi1').val('');
                        $('#other1').val('');
                        $('#addform').modal('hide');
                        $('.form-group1').hide();
                        $('.form-group2').hide();
                        $('.form-group3').hide();
                        $('.form-group4').hide();
                        $('.form-group5').hide();
                        $('.form-group6').hide();
                        $('.form-group8').hide();
                        $('.form-group9').hide();
                        $('.form-group13').hide();
                        $('.form-group20').hide();
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
                    url:'{!! route('pemakaian.updateajax') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        $('.form-group1').hide();
                        $('.form-group2').hide();
                        $('.form-group3').hide();
                        $('.form-group4').hide();
                        $('.form-group5').hide();
                        $('.form-group6').hide();
                        $('.form-group8').hide();
                        $('.form-group9').hide();
                        $('#editform').modal('hide');
                        refreshTable();
                        if (data.success === true) {
                            swal("Berhasil!", data.message, "success");
                        } else {
                            swal("Gagal!", data.message, "error");
                        }
                    },
                });
            }
        );

    </script>
@endpush