
@extends('adminlte::page')

@section('title', 'Pemakaian')

@section('content_header')
    
@stop


@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
@include('sweet::alert')
<body onLoad="load()">
    <div class="box box-solid">
        <div class="box-body">
            <div class="box">
                <div class="box-body">
                    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()" >
                        <i class="fa fa-refresh"></i> Refresh</button>

                    @permission('create-pemakaian')
                    <button type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#addform">
                        <i class="fa fa-plus"></i> Pemakaian Baru</button>
                    @endpermission

                    @permission('post-getkode')
                    <button type="button" class="btn btn-primary btn-xs" onclick="getkode()">
                        <i class="fa fa-bullhorn"></i> Get New Kode</button>
                    @endpermission

                    <?php if (auth()->user()->level == 'superadministrator') { ?>
                        <button type="button" class="btn btn-primary btn-xs" onclick="hitungjurnal()">
                            <i class="fa fa-bullhorn"></i> Hitung Jurnal</button>
                    <?php } ?>

                    <span class="pull-right"> 
                        <font style="font-size: 16px;"><b>PEMAKAIAN</b></font>
                    </span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-primary" style="font-size: 11px;">
                        <th>No Pemakaian</th>
                        <th>Tanggal Pemakaian</th>
                        <th>No Polisi</th>
                        <th>No Aset Mobil</th>
                        <th>Nama Alat</th>
                        <th>No Aset Alat</th>           
                        <th>Nama Kapal</th>
                        <th>No Aset Kapal</th>
                        <th>Keterangan</th>
                        <th>Pemakai</th>
                        <th>Total Item</th>
                        <th>Tipe Pemakaian</th>
                        <th>HM</th>
                        <th>KM</th>
                        <th>No JO</th>
                        <th>Kode Lokasi</th>
                        <th>Status</th>
                        <th>No Journal</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

<div class="modal fade" id="addform"  role="dialog">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <div class="row">
                    <div class="col-md-3">
                        <h4>Create Data</h4>   
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
                                {{ Form::hidden('company_user', auth()->user()->kode_company, ['class'=> 'form-control','id'=>'company2','readonly']) }}
                                
                                <div class="row">                                
                                            {{ Form::hidden('no_pemakaian', null, ['class'=> 'form-control','id'=>'Pemakaian','readonly']) }}
                                                                                                                                  
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('Type', 'Tipe Pemakaian:') }}
                                            {{ Form::text('type', null, ['class'=> 'form-control','id'=>'typeedit','readonly'])}}
                                        </div>
                                    </div>
            
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
                                <th>DB/CR</th>
                                <th>Debet</th>
                                <th>Credit</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-warning">
                                <th class="text-center" colspan="3">Total</th>
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
            if (x >= 10000){
                x = 0;
                refreshTable();
            }
        }
        setInterval(showTime, 1);
        
        $(document).ready(function() {
            $("#back2Top").click(function(event) {
                event.preventDefault();
                $("html, body").animate({ scrollTop: 0 }, "slow");
                return false;
            });

        });

        function load(){
            startTime();
            comp1();
            $('.form-group1').hide();
            $('.tombol1').hide();
            $('.tombol2').hide();
            $('.add-button').hide();
            $('.hapus-button').hide();
            $('.edit-button').hide();
            $('.print-button').hide();
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
            $('.back2Top').show();
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
            console.log(compan);
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
            processing: true,
            serverSide: true,
            ajax: '{!! route('pemakaian.data') !!}',
            fnRowCallback: function (row, data, iDisplayIndex, iDisplayIndexFull) {
                if (data['status'] == "OPEN") {
                    $('td', row).css('background-color', '#ffdbd3');
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
                { data: 'no_journal', name: 'no_journal' },
            ]
            });
        });

        Table4 = $("#addjurnal-table2").DataTable({
            "bPaginate": false,
            "bFilter": false,
            "order": [[ 2, "asc" ]],
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
                    .column( 3 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );

                grandkredit = api
                    .column( 4 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                            
                // Update footer
                $( api.column( 3 ).footer() ).html(
                    formatRupiah(granddebit)
                );

                $( api.column( 4 ).footer() ).html(
                    formatRupiah(grandkredit)
                );
            },
            
            columns: [
                { data: 'account', name: 'account' },
                { data: 'ac_description', name: 'ac_description' },
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
            console.log(n);
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
            // console.log(type)
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

        $(function() {
            $('#child-table').DataTable({
                scrollY: 200,
                scrollX: true
            
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

        function createTable(result){

        var total_qty = 0;
        var total_pakai = 0;
        var total_harga = 0;
        var grand_total = 0;

        $.each( result, function( key, row ) {
            total_qty += row.qty;
            harga = row.harga;
            qty = row.qty;
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
                                '<th></th>'+
                                '<th>Rp '+grand_total+'</th>'+
                                @endpermission
                                '<th></th>'+
                            '</tr>'+
                            '</tfoot>'+
                        '</table>';
            
            console.log(my_table);
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
                                console.log(result);
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
                                        console.log(result[i].produk,result[i].satuan,result[i].qty,result[i].harga);
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
               // console.log(typeedit)
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
                    $('.view-button').hide();   
                    $('.zoom-button').hide();  
                }
                else {
                    table.$('tr.selected').removeClass('selected bg-gray text-bold');
                    $(this).addClass('selected bg-gray text-bold');
                    var select = $('.selected').closest('tr');
                    
                    closeOpenedRows(table, select);
                    
                    var data = $('#data-table').DataTable().row(select).data();
                    var no_pemakaian = data['no_pemakaian'];
                    var no_journal = data['no_journal'];
                    var add = $("#addpemakaian").attr("href","http://localhost/gui_inventory_laravel/admin/pemakaian/"+no_pemakaian+"/detail");
                    var print = $("#printpemakaian").attr("href","http://localhost/gui_inventory_laravel/admin/pemakaian/export2?no_pemakaian="+no_pemakaian);
                    var print2 = $("#printzoom3").attr("href","http://localhost/gui_inventory_laravel/admin/pemakaian/exportpdf3?no_journal="+no_journal + "&no_pemakaian="+no_pemakaian);
                    var status = data['status'];
                    var item = data['total_item'];
                    if(status == 'POSTED' && item > 0){
                        $('.tombol1').hide();
                        $('.tombol2').show();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').show();
                        $('.view-button').show();
                        $('.zoom-button').show();
                    }else if(status =='OPEN' && item > 0){
                        $('.tombol1').show();
                        $('.tombol2').hide();
                        $('.add-button').show();
                        $('.hapus-button').hide();
                        $('.edit-button').show();
                        $('.print-button').hide();
                        $('.view-button').show();
                        $('.zoom-button').hide();
                    }else if(status =='OPEN' && item == null || item == 0){
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.add-button').show();
                        $('.hapus-button').show();
                        $('.edit-button').show();
                        $('.print-button').hide();
                        $('.view-button').hide();
                        $('.zoom-button').hide();
                    }else if (status == 'RETUR') {
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').hide();
                        $('.view-button').show();
                        $('.zoom-button').hide();
                    }
                }
            } );
            
            $('#button4').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_pemakaian = data['no_pemakaian'];
                console.log(no_pemakaian);
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
                                console.log(result);
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
                console.log(no_pemakaian);
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
                            title: "<b>Proses Sedang Berlangsung</b>",
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
                                console.log(result);
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
                console.log(no_pemakaian);
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
                            title: "<b>Proses Sedang Berlangsung</b>",
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
                                console.log(result);
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
                                      title: 'Error',
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
                        console.log(result);
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

            $('#button3').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_pemakaian = data['no_pemakaian'];
                var row = table.row( select );
                console.log(no_pemakaian);
                $.ajax({
                    url: '{!! route('pemakaian.showdetail') !!}',
                    type: 'POST',
                    data : {
                        'id': no_pemakaian
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
            } );
            
            $('#editpemakaian').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#data-table').DataTable().row(select).data();
                var no_pemakaian = data['no_pemakaian'];
                var row = table.row( select );
                console.log(no_pemakaian);
                $.ajax({
                    url: '{!! route('pemakaian.edit_pemakaian') !!}',
                    type: 'POST',
                    data : {
                        'id': no_pemakaian
                    },
                    success: function(results) {
                        console.log(results);
                        $('#Pemakaian').val(results.no_pemakaian);
                        $('#Pemakai').val(results.pemakai);
                        $('#typeedit').val(results.type);
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
            $('.view-button').hide();
            $('.zoom-button').hide();
        }

        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });

        $('.modal-dialog').resizable({
    
        });

        function getasetmobil(){
            var nopol= $('#Nopol1').val();

            $.ajax({
                url:'{!! route('pemakaian.getmobil') !!}',
                type:'POST',
                data : {
                        'id': nopol,
                    },
                success: function(result) {
                        console.log(result);
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
                        console.log(result);
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
                        console.log(result);
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
                        console.log(result);
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
                        console.log(result);
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
                        console.log(result);
                        $('#Asetkapal').val(result.no_asset_kapal).trigger('change');
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
                        console.log(result);
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
                        console.log(data);
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
                        console.log(data);
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