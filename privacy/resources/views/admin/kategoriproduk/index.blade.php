@extends('adminlte::page')

@section('title', 'Kategori')

@section('content_header')
    
@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
@include('sweet::alert')

<body onLoad="load()">
    <div class="box box-solid">
        <div class="box-body">
            <div class="box ">
                <div class="box-body">
                    @permission('create-kategorior')
                    <button type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#addform">
                        <i class="fa fa-plus"></i> New Kategori</button>
                    @endpermission

                    <span class="pull-right">  
                        <font style="font-size: 16px;"><b>KATEGORI</b></font>
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="kategori-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-blue">
                        <th>Kode Kategori</th>
                        <th>Nama Kategori</th>
                        <th>Status</th>
                        <th>COA Persediaan</th>
                        <th>Costcenter Persediaan</th>
                        <th>COA COGS</th>
                        <th>Costcenter COGS</th>
                     </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addform" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Create Data</h4>
            </div>
            @include('errors.validation')
                {!! Form::open(['id'=>'ADD']) !!}
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('kode', 'Kode:') }}
                                    {{ Form::text('kode_kategori', null, ['class'=> 'form-control','id'=>'Kode1', 'placeholder'=>'Kode Kategori','required'=>'required','autocomplete'=>'off','data-toggle'=>"tooltip",'data-placement'=>"bottom",'title'=>"Maksimal 6 Karakter", 'maxlength'=>'6', 'onkeypress'=>"return pulsar(event,this)"] )}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('Nama Kategori', 'Nama Kategori:') }}
                                    {{ Form::text('nama_kategori', null, ['class'=> 'form-control','id'=>'Nama1','required'=>'required', 'placeholder'=>'Nama Kategori', 'onkeypress'=>"return pulsar(event,this)",'autocomplete'=>'off']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('status', 'Status:') }}
                                    {{Form::select('status', ['Aktif' => 'Aktif', 'NonAktif' => 'NonAktif'], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Status1','required'=>'required'])}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('coa', 'COA Persediaan:') }}
                                    {{Form::select('coa', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Coa1','onchange'=>"getcoa()"])}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('coabiaya', 'COA COGS:') }}
                                    {{Form::select('coabiaya', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Coabiaya1','onchange'=>"getcoabiaya()"])}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('CC', 'Cost Center Persediaan:') }}
                                    {{Form::select('cost_center', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Costpersediaan1','onchange'=>"getcc2()"])}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('CC', 'Cost Center COGS:') }}
                                    {{Form::select('cost_center', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Cost1','onchange'=>"getcc()"])}}
                                </div>
                            </div>
                            
                            {{ Form::hidden('user', Auth()->user()->kode_company, ['class'=> 'form-control','id'=>'User1','readonly']) }}

                            {{ Form::hidden('coa_gut', null, ['class'=> 'form-control','id'=>'coagut1','readonly']) }}
                            {{ Form::hidden('coa_emkl', null, ['class'=> 'form-control','id'=>'coaemkl1','readonly']) }}
                            {{ Form::hidden('coa_pbm', null, ['class'=> 'form-control','id'=>'coapbm1','readonly']) }}
                            {{ Form::hidden('coa_infra', null, ['class'=> 'form-control','id'=>'coainfra1','readonly']) }}
                            {{ Form::hidden('coa_depo', null, ['class'=> 'form-control','id'=>'coadepo1','readonly']) }}
                            {{ Form::hidden('coa_sub', null, ['class'=> 'form-control','id'=>'coasub1','readonly']) }}
                            {{ Form::hidden('coa_gutjkt', null, ['class'=> 'form-control','id'=>'coagutjkt1','readonly']) }}
                            {{ Form::hidden('coabiaya_infra', null, ['class'=> 'form-control','id'=>'coabiayainfra1','readonly']) }}
                            {{ Form::hidden('coabiaya_gut', null, ['class'=> 'form-control','id'=>'coabiayagut1','readonly']) }}
                            {{ Form::hidden('coabiaya_emkl', null, ['class'=> 'form-control','id'=>'coabiayaemkl1','readonly']) }}
                            {{ Form::hidden('coabiaya_pbm', null, ['class'=> 'form-control','id'=>'coabiayapbm1','readonly']) }}
                            {{ Form::hidden('coabiaya_depo', null, ['class'=> 'form-control','id'=>'coabiayadepo1','readonly']) }}
                            {{ Form::hidden('coabiaya_sub', null, ['class'=> 'form-control','id'=>'coabiayasub1','readonly']) }}
                            {{ Form::hidden('coabiaya_gutjkt', null, ['class'=> 'form-control','id'=>'coabiayagutjkt1','readonly']) }}
                            
                            {{ Form::hidden('cc_gut', null, ['class'=> 'form-control','id'=>'ccgut1','readonly']) }}
                            {{ Form::hidden('cc_gutjkt', null, ['class'=> 'form-control','id'=>'ccgutjkt1','readonly']) }}
                            {{ Form::hidden('cc_emkl', null, ['class'=> 'form-control','id'=>'ccemkl1','readonly']) }}
                            {{ Form::hidden('cc_pbm', null, ['class'=> 'form-control','id'=>'ccpbm1','readonly']) }}
                            {{ Form::hidden('cc_infra', null, ['class'=> 'form-control','id'=>'ccinfra1','readonly']) }}
                            {{ Form::hidden('cc_sub', null, ['class'=> 'form-control','id'=>'ccsub1','readonly']) }}
                            {{ Form::hidden('cc_depo', null, ['class'=> 'form-control','id'=>'ccdepo1','readonly']) }}
                            
                            {{ Form::hidden('cc_gut_persediaan', null, ['class'=> 'form-control','id'=>'ccgutpersediaan1','readonly']) }}
                            {{ Form::hidden('cc_gutjkt_persediaan', null, ['class'=> 'form-control','id'=>'ccgutjktpersediaan1','readonly']) }}
                            {{ Form::hidden('cc_emkl_persediaan', null, ['class'=> 'form-control','id'=>'ccemklpersediaan1','readonly']) }}
                            {{ Form::hidden('cc_pbm_persediaan', null, ['class'=> 'form-control','id'=>'ccpbmpersediaan1','readonly']) }}
                            {{ Form::hidden('cc_infra_persediaan', null, ['class'=> 'form-control','id'=>'ccinfrapersediaan1','readonly']) }}
                            {{ Form::hidden('cc_sub_persediaan', null, ['class'=> 'form-control','id'=>'ccsubpersediaan1','readonly']) }}
                            {{ Form::hidden('cc_depo_persediaan', null, ['class'=> 'form-control','id'=>'ccdepopersediaan1','readonly']) }}
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
                        {{ Form::hidden('company_user', auth()->user()->kode_company, ['class'=> 'form-control','id'=>'company1','readonly']) }}
                        {{ Form::hidden('kode_kategori', null, ['class'=> 'form-control','id'=>'Kode2','readonly']) }}
                        
                        <div class="col-md-9">
                            <div class="form-group">
                                {{ Form::label('Nama Kategori', 'Nama Kategori:') }}
                                {{ Form::text('nama_kategori', null, ['class'=> 'form-control','id'=>'Nama2','autocomplete'=>'off', 'onkeypress'=>"return pulsar(event,this)",'readonly']) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('status', 'Status:') }}
                                {{Form::select('status', ['Aktif' => 'Aktif', 'NonAktif' => 'NonAktif'], null, ['class'=> 'form-control select2','style'=>'width: 100%','id'=>'Status2'])}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group22">
                                {{ Form::label('coa_gut', 'COA Persediaan:') }}
                                {{Form::select('coa_gut', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coagut2'])}}
                            </div>

                            <div class="form-group23">
                                {{ Form::label('coa_emkl', 'COA Persediaan:') }}
                                {{Form::select('coa_emkl', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coaemkl2'])}}
                            </div>

                            <div class="form-group24">
                                {{ Form::label('coa_pbm', 'COA Persediaan:') }}
                                {{Form::select('coa_pbm', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coapbm2'])}}
                            </div>
                            
                            <div class="form-group38">
                                {{ Form::label('coa_infra', 'COA Persediaan:') }}
                                {{Form::select('coa_infra', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coainfra2'])}}
                            </div>

                            <div class="form-group25">
                                {{ Form::label('coa_depo', 'COA Persediaan:') }}
                                {{Form::select('coa_depo', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coadepo2'])}}
                            </div>

                            <div class="form-group26">
                                {{ Form::label('coa_sub', 'COA Persediaan:') }}
                                {{Form::select('coa_sub', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coasub2'])}}
                            </div>

                            <div class="form-group27">
                                {{ Form::label('coa_gutjkt', 'COA Persediaan:') }}
                                {{Form::select('coa_gutjkt', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coagutjkt2'])}}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group32">
                                {{ Form::label('coabiaya_gut', 'COA COGS:') }}
                                {{Form::select('coabiaya_gut', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coabiayagut2'])}}
                            </div>

                            <div class="form-group33">
                                {{ Form::label('coabiaya_emkl', 'COA COGS:') }}
                                {{Form::select('coabiaya_emkl', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coabiayaemkl2'])}}
                            </div>

                            <div class="form-group34">
                                {{ Form::label('coabiaya_pbm', 'COA COGS:') }}
                                {{Form::select('coabiaya_pbm', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coabiayapbm2'])}}
                            </div>

                            <div class="form-group35">
                                {{ Form::label('coabiaya_depo', 'COA COGS:') }}
                                {{Form::select('coabiaya_depo', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coabiayadepo2'])}}
                            </div>

                            <div class="form-group36">
                                {{ Form::label('coabiaya_sub', 'COA COGS:') }}
                                {{Form::select('coabiaya_sub', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coabiayasub2'])}}
                            </div>

                            <div class="form-group37">
                                {{ Form::label('coabiaya_gutjkt', 'COA COGS:') }}
                                {{Form::select('coabiaya_gutjkt', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coabiayagutjkt2'])}}
                            </div>
                            
                            <div class="form-group39">
                                {{ Form::label('coabiaya_infra', 'COA COGS:') }}
                                {{Form::select('coabiaya_infra', $Coa->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'coabiayainfra2'])}}
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group47">
                                {{ Form::label('CC', 'Cost Center Persediaan:') }}
                                {{Form::select('cc_gut_persediaan', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costgutpersediaan2'])}}
                            </div>
                            <div class="form-group48">
                                {{ Form::label('CC', 'Cost Center Persediaan:') }}
                                {{Form::select('cc_gutjkt_persediaan', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costgutjktpersediaan2'])}}
                            </div>
                            <div class="form-group49">
                                {{ Form::label('CC', 'Cost Center Persediaan:') }}
                                {{Form::select('cc_emkl_persediaan', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costemklpersediaan2'])}}
                            </div>
                            <div class="form-group50">
                                {{ Form::label('CC', 'Cost Center Persediaan:') }}
                                {{Form::select('cc_pbm_persediaan', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costpbmpersediaan2'])}}
                            </div>
                            <div class="form-group51">
                                {{ Form::label('CC', 'Cost Center Persediaan:') }}
                                {{Form::select('cc_infra_persediaan', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costinfrapersediaan2'])}}
                            </div>
                            <div class="form-group52">
                                {{ Form::label('CC', 'Cost Center Persediaan:') }}
                                {{Form::select('cc_sub_persediaan', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costsubpersediaan2'])}}
                            </div>
                            <div class="form-group53">
                                {{ Form::label('CC', 'Cost Center Persediaan:') }}
                                {{Form::select('cc_depo_persediaan', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costdepopersediaan2'])}}
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group40">
                                {{ Form::label('CC', 'Cost Center COGS:') }}
                                {{Form::select('cc_gut', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costgut2'])}}
                            </div>
                            <div class="form-group41">
                                {{ Form::label('CC', 'Cost Center COGS:') }}
                                {{Form::select('cc_gutjkt', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costgutjkt2'])}}
                            </div>
                            <div class="form-group42">
                                {{ Form::label('CC', 'Cost Center COGS:') }}
                                {{Form::select('cc_emkl', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costemkl2'])}}
                            </div>
                            <div class="form-group43">
                                {{ Form::label('CC', 'Cost Center COGS:') }}
                                {{Form::select('cc_pbm', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costpbm2'])}}
                            </div>
                            <div class="form-group44">
                                {{ Form::label('CC', 'Cost Center COGS:') }}
                                {{Form::select('cc_infra', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costinfra2'])}}
                            </div>
                            <div class="form-group45">
                                {{ Form::label('CC', 'Cost Center COGS:') }}
                                {{Form::select('cc_sub', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costsub2'])}}
                            </div>
                            <div class="form-group46">
                                {{ Form::label('CC', 'Cost Center COGS:') }}
                                {{Form::select('cc_depo', $Costcenter->sort(), null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'costdepo2'])}}
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
            @permission('update-kategorior')
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editkategori" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>
            @endpermission

            @permission('delete-kategorior')
            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapuskategori" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>
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

        function load(){
            startTime();
            comp1();
            $('.hapus-button').hide();
            $('.edit-button').hide();
            $('.back2Top').show();
        }

        function comp1(){
            var compan = $("#company1").val();
            if (compan == '04'){
                $('.form-group22').show();
                $('.form-group23').hide();
                $('.form-group24').hide();
                $('.form-group25').hide();
                $('.form-group26').hide();
                $('.form-group27').hide();
                $('.form-group38').hide();

                $('.form-group32').show();
                $('.form-group33').hide();
                $('.form-group34').hide();
                $('.form-group35').hide();
                $('.form-group36').hide();
                $('.form-group37').hide();
                $('.form-group39').hide();
                
                $('.form-group40').show();
                $('.form-group41').hide();
                $('.form-group42').hide();
                $('.form-group43').hide();
                $('.form-group44').hide();
                $('.form-group45').hide();
                $('.form-group46').hide();
                
                $('.form-group47').show();
                $('.form-group48').hide();
                $('.form-group49').hide();
                $('.form-group50').hide();
                $('.form-group51').hide();
                $('.form-group52').hide();
                $('.form-group53').hide();
            }else if(compan == '03'){
                $('.form-group22').hide();
                $('.form-group23').show();
                $('.form-group24').hide();
                $('.form-group25').hide();
                $('.form-group26').hide();
                $('.form-group27').hide();
                $('.form-group38').hide();

                $('.form-group32').hide();
                $('.form-group33').show();
                $('.form-group34').hide();
                $('.form-group35').hide();
                $('.form-group36').hide();
                $('.form-group37').hide();
                $('.form-group39').hide();
                
                $('.form-group40').hide();
                $('.form-group41').hide();
                $('.form-group42').show();
                $('.form-group43').hide();
                $('.form-group44').hide();
                $('.form-group45').hide();
                $('.form-group46').hide();
                
                $('.form-group47').hide();
                $('.form-group48').hide();
                $('.form-group49').show();
                $('.form-group50').hide();
                $('.form-group51').hide();
                $('.form-group52').hide();
                $('.form-group53').hide();
            }else if(compan == '02'){
                $('.form-group22').hide();
                $('.form-group23').hide();
                $('.form-group24').show();
                $('.form-group25').hide();
                $('.form-group26').hide();
                $('.form-group27').hide();
                $('.form-group38').hide();

                $('.form-group32').hide();
                $('.form-group33').hide();
                $('.form-group34').show();
                $('.form-group35').hide();
                $('.form-group36').hide();
                $('.form-group37').hide();
                $('.form-group39').hide();
                
                $('.form-group40').hide();
                $('.form-group41').hide();
                $('.form-group42').hide();
                $('.form-group43').show();
                $('.form-group44').hide();
                $('.form-group45').hide();
                $('.form-group46').hide();
                
                $('.form-group47').hide();
                $('.form-group48').hide();
                $('.form-group49').hide();
                $('.form-group50').show();
                $('.form-group51').hide();
                $('.form-group52').hide();
                $('.form-group53').hide();
            }else if(compan == '01'){
                $('.form-group22').hide();
                $('.form-group23').hide();
                $('.form-group24').hide();
                $('.form-group25').show();
                $('.form-group26').hide();
                $('.form-group27').hide();
                $('.form-group38').hide();

                $('.form-group32').hide();
                $('.form-group33').hide();
                $('.form-group34').hide();
                $('.form-group35').show();
                $('.form-group36').hide();
                $('.form-group37').hide();
                $('.form-group39').hide();
                
                $('.form-group40').hide();
                $('.form-group41').hide();
                $('.form-group42').hide();
                $('.form-group43').hide();
                $('.form-group44').hide();
                $('.form-group45').hide();
                $('.form-group46').show();
                
                $('.form-group47').hide();
                $('.form-group48').hide();
                $('.form-group49').hide();
                $('.form-group50').hide();
                $('.form-group51').hide();
                $('.form-group52').hide();
                $('.form-group53').show();
            }else if(compan == '05'){
                $('.form-group22').hide();
                $('.form-group23').hide();
                $('.form-group24').hide();
                $('.form-group25').hide();
                $('.form-group26').show();
                $('.form-group27').hide();
                $('.form-group38').hide();

                $('.form-group32').hide();
                $('.form-group33').hide();
                $('.form-group34').hide();
                $('.form-group35').hide();
                $('.form-group36').show();
                $('.form-group37').hide();
                $('.form-group39').hide();
                
                $('.form-group40').hide();
                $('.form-group41').hide();
                $('.form-group42').hide();
                $('.form-group43').hide();
                $('.form-group44').hide();
                $('.form-group45').show();
                $('.form-group46').hide();
                
                $('.form-group47').hide();
                $('.form-group48').hide();
                $('.form-group49').hide();
                $('.form-group50').hide();
                $('.form-group51').hide();
                $('.form-group52').show();
                $('.form-group53').hide();
            }else if(compan == '0401'){
                $('.form-group22').hide();
                $('.form-group23').hide();
                $('.form-group24').hide();
                $('.form-group25').hide();
                $('.form-group26').hide();
                $('.form-group27').show();
                $('.form-group38').hide();

                $('.form-group32').hide();
                $('.form-group33').hide();
                $('.form-group34').hide();
                $('.form-group35').hide();
                $('.form-group36').hide();
                $('.form-group37').show();
                $('.form-group39').hide();
                
                $('.form-group40').hide();
                $('.form-group41').show();
                $('.form-group42').hide();
                $('.form-group43').hide();
                $('.form-group44').hide();
                $('.form-group45').hide();
                $('.form-group46').hide();
                
                $('.form-group47').hide();
                $('.form-group48').show();
                $('.form-group49').hide();
                $('.form-group50').hide();
                $('.form-group51').hide();
                $('.form-group52').hide();
                $('.form-group53').hide();
            }else if(compan == '06'){
                $('.form-group22').hide();
                $('.form-group23').hide();
                $('.form-group24').hide();
                $('.form-group25').hide();
                $('.form-group26').hide();
                $('.form-group27').hide();
                $('.form-group38').show();

                $('.form-group32').hide();
                $('.form-group33').hide();
                $('.form-group34').hide();
                $('.form-group35').hide();
                $('.form-group36').hide();
                $('.form-group37').hide();
                $('.form-group39').show();
                
                $('.form-group40').hide();
                $('.form-group41').hide();
                $('.form-group42').hide();
                $('.form-group43').hide();
                $('.form-group44').show();
                $('.form-group45').hide();
                $('.form-group46').hide();
                
                $('.form-group47').hide();
                $('.form-group48').hide();
                $('.form-group49').hide();
                $('.form-group50').hide();
                $('.form-group51').show();
                $('.form-group52').hide();
                $('.form-group53').hide();
            }
        }

        function getcoa() {
            var coa = $("#Coa1").val();
            var coabiaya = $("#Coabiaya1").val();
            var user = $("#User1").val();
            if (user == '03') {
                $('#coaemkl1').val(coa);
                $('#coabiayaemkl1').val(coabiaya);
            }else if (user == '04'){
                $('#coagut1').val(coa);
                $('#coabiayagut1').val(coabiaya);
            }else if (user == '02'){
                $('#coapbm1').val(coa);
                $('#coabiayapbm1').val(coabiaya);
            }else if (user == '01'){
                $('#coadepo1').val(coa);
                $('#coabiayadepo1').val(coabiaya);
            }else if (user == '05'){
                $('#coasub1').val(coa);
                $('#coabiayasub1').val(coabiaya);
            }else if (user == '0401'){
                $('#coagutjkt1').val(coa);
                $('#coabiayagutjkt1').val(coabiaya);
            }else if (user == '06'){
                $('#coainfra1').val(coa);
                $('#coabiayainfra1').val(coabiaya);
            }
        }
        
        function getcc() {
            var cost = $("#Cost1").val();
            var user = $("#User1").val();
            if (user == '03') {
                $('#ccemkl1').val(cost);
            }else if (user == '04'){
                $('#ccgut1').val(cost);
            }else if (user == '02'){
                $('#ccpbm1').val(cost);
            }else if (user == '01'){
                $('#ccdepo1').val(cost);
            }else if (user == '05'){
                $('#ccsub1').val(cost);
            }else if (user == '0401'){
                $('#ccgutjkt1').val(cost);
            }else if (user == '06'){
                $('#ccinfra1').val(cost);
            }
        }
        
        function getcc2() {
            var cost = $("#Costpersediaan1").val();
            var user = $("#User1").val();
            if (user == '03') {
                $('#ccemklpersediaan1').val(cost);
            }else if (user == '04'){
                $('#ccgutpersediaan1').val(cost);
            }else if (user == '02'){
                $('#ccpbmpersediaan1').val(cost);
            }else if (user == '01'){
                $('#ccdepopersediaan1').val(cost);
            }else if (user == '05'){
                $('#ccsubpersediaan1').val(cost);
            }else if (user == '0401'){
                $('#ccgutjktpersediaan1').val(cost);
            }else if (user == '06'){
                $('#ccinfrapersediaan1').val(cost);
            }
        }

        $(function() {
            $('#kategori-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('kategoriproduk.data') !!}',
            columns: [
                { data: 'kode_kategori', name: 'kode_kategori', visible: false },
                { data: 'nama_kategori', name: 'nama_kategori' },
                { data: 'status', name: 'status' },
            <?php if (auth()->user()->kode_company == '04'){ ?>
                { data: 'coa.account', name: 'coa.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '03'){ ?>
                { data: 'coa1.account', name: 'coa1.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '02'){ ?>
                { data: 'coa2.account', name: 'coa2.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '01'){ ?>
                { data: 'coa3.account', name: 'coa3.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '05'){ ?>
                { data: 'coa4.account', name: 'coa4.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '0401'){ ?>
                { data: 'coa5.account', name: 'coa5.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '06'){ ?>
                { data: 'coa12.account', name: 'coa12.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } ?>
            
            <?php if (auth()->user()->kode_company == '04'){ ?>
                { data: 'cost_gut_persediaan.desc', name: 'cost_gut_persediaan.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '03'){ ?>
                { data: 'cost_emkl_persediaan.desc', name: 'cost_emkl_persediaan.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '02'){ ?>
                { data: 'cost_pbm_persediaan.desc', name: 'cost_pbm_persediaan.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '01'){ ?>
                { data: 'cost_depo_persediaan.desc', name: 'cost_depo_persediaan.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '05'){ ?>
                { data: 'cost_sub_persediaan.desc', name: 'cost_sub_persediaan.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '0401'){ ?>
                { data: 'cost_gutjkt_persediaan.desc', name: 'cost_gutjkt_persediaan.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '06'){ ?>
                { data: 'cost_infra_persediaan.desc', name: 'cost_infra_persediaan.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } ?>

            <?php if (auth()->user()->kode_company == '04'){ ?>
                { data: 'coa6.account', name: 'coa6.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '03'){ ?>
                { data: 'coa7.account', name: 'coa7.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '02'){ ?>
                { data: 'coa8.account', name: 'coa8.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '01'){ ?>
                { data: 'coa9.account', name: 'coa9.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '05'){ ?>
                { data: 'coa10.account', name: 'coa10.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '0401'){ ?>
                { data: 'coa11.account', name: 'coa11.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '06'){ ?>
                { data: 'coa13.account', name: 'coa13.account', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } ?>
            
            <?php if (auth()->user()->kode_company == '04'){ ?>
                { data: 'cost_gut.desc', name: 'cost_gut.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '03'){ ?>
                { data: 'cost_emkl.desc', name: 'cost_emkl.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '02'){ ?>
                { data: 'cost_pbm.desc', name: 'cost_pbm.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '01'){ ?>
                { data: 'cost_depo.desc', name: 'cost_depo.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '05'){ ?>
                { data: 'cost_sub.desc', name: 'cost_sub.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '0401'){ ?>
                { data: 'cost_gutjkt.desc', name: 'cost_gutjkt.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } else if (auth()->user()->kode_company == '06'){ ?>
                { data: 'cost_infra.desc', name: 'cost_infra.desc', "defaultContent": "<i>Not set</i>", searchable: false },
            <?php } ?>
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

        $(document).ready(function(){
            $("#back2Top").click(function(event) {
                event.preventDefault();
                $("html, body").animate({ scrollTop: 0 }, "slow");
                return false;
            });

            $('[data-toggle="tooltip"]').tooltip();   
            var table = $('#kategori-table').DataTable();
            $('#kategori-table tbody').on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected bg-gray text-bold') ) {
                    $(this).removeClass('selected bg-gray text-bold');
                    $('.hapus-button').hide();
                    $('.edit-button').hide();
                }
                else {
                    table.$('tr.selected').removeClass('selected bg-gray text-bold');
                    $(this).addClass('selected bg-gray text-bold');
                    var select = $('.selected').closest('tr');
                    var kode_kategori = select.find('td:eq(0)').text();
                    $('.hapus-button').show();
                    $('.edit-button').show();
                }
            });

            $('#editkategori').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#kategori-table').DataTable().row(select).data();
                var kode_kategori = data['kode_kategori'];
                var row = table.row( select );
                $.ajax({
                    url: '{!! route('kategoriproduk.edit_kategori') !!}',
                    type: 'POST',
                    data : {
                        'id': kode_kategori
                    },
                    success: function(results) {
                            $('#Kode2').val(results.kode_kategori);
                            $('#Nama2').val(results.nama_kategori);
                            $('#coagut2').val(results.coa_gut).trigger('change');
                            $('#coaemkl2').val(results.coa_emkl).trigger('change');
                            $('#coapbm2').val(results.coa_pbm).trigger('change');
                            $('#coadepo2').val(results.coa_depo).trigger('change');
                            $('#coasub2').val(results.coa_sub).trigger('change');
                            $('#coagutjkt2').val(results.coa_gutjkt).trigger('change');
                            $('#coainfra2').val(results.coa_infra).trigger('change');

                            $('#coabiayagut2').val(results.coabiaya_gut).trigger('change');
                            $('#coabiayaemkl2').val(results.coabiaya_emkl).trigger('change');
                            $('#coabiayapbm2').val(results.coabiaya_pbm).trigger('change');
                            $('#coabiayadepo2').val(results.coabiaya_depo).trigger('change');
                            $('#coabiayasub2').val(results.coabiaya_sub).trigger('change');
                            $('#coabiayagutjkt2').val(results.coabiaya_gutjkt).trigger('change');
                            $('#coabiayainfra2').val(results.coabiaya_infra).trigger('change');
                            
                            $('#costgut2').val(results.cc_gut).trigger('change');
                            $('#costgutjkt2').val(results.cc_gutjkt).trigger('change');
                            $('#costpbm2').val(results.cc_pbm).trigger('change');
                            $('#costemkl2').val(results.cc_emkl).trigger('change');
                            $('#costinfra2').val(results.cc_infra).trigger('change');
                            $('#costsub2').val(results.cc_sub).trigger('change');
                            $('#costdepo2').val(results.cc_depo).trigger('change');
                            
                            $('#costgutpersediaan2').val(results.cc_gut_persediaan).trigger('change');
                            $('#costgutjktpersediaan2').val(results.cc_gutjkt_persediaan).trigger('change');
                            $('#costpbmpersediaan2').val(results.cc_pbm_persediaan).trigger('change');
                            $('#costemklpersediaan2').val(results.cc_emkl_persediaan).trigger('change');
                            $('#costinfrapersediaan2').val(results.cc_infra_persediaan).trigger('change');
                            $('#costsubpersediaan2').val(results.cc_sub_persediaan).trigger('change');
                            $('#costdepopersediaan2').val(results.cc_depo_persediaan).trigger('change');
                            
                            $('#Status2').val(results.status).trigger('change');
                            $('#editform').modal('show');
                    }
                });
            });

            $('#hapuskategori').click( function () {
                var select = $('.selected').closest('tr');
                var data = $('#kategori-table').DataTable().row(select).data();
                var kode_kategori = data['kode_kategori'];
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
                            url: '{!! route('kategoriproduk.hapus_kategori') !!}',
                            type: 'POST',
                            data : {
                                'id': kode_kategori
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

        function refreshTable() {
             $('#kategori-table').DataTable().ajax.reload(null,false);;
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
                    url:'{!! route('kategoriproduk.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        $('#Kode1').val('');
                        $('#Nama1').val('');
                        $('#Status1').val('').trigger('change');
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
                    url:'{!! route('kategoriproduk.ajaxupdate') !!}',
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