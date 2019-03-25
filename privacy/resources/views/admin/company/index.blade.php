@extends('adminlte::page')

@section('title', 'Company')

@section('content_header')
    <h1>Company</h1>
@stop

@section('content')
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Manages Company</h3>
        </div>
        <div class="box-body">
            <div class="box ">
                <div class="box-body">
                    {{-- <a href="{{ $create_url }}" class="btn btn-info btn-sm">New Company</a> --}}
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addform">
                        <i class="fa fa-plus"></i> New Company</button>
                </div>
            </div>
             <table class="table table-bordered table-hover" id="data-table" width="100%">
                <thead>
                <tr class="bg-purple">
                    <th>Kode Company</th>
                    <th>Nama Company</th>
                    <th>Alamat</th>
                    <th>Telp</th>
                    <th>Npwp</th>
                    <th>Status</th>
                    <th>Created At</th>
                    {{-- <th>Updated At</th> --}}
                    {{-- <th>Created By</th> --}}
                    {{-- <th>Updated By</th> --}}
                    <th>Action</th>
                </tr>
                </thead>
    </table>

        </div>
    </div>

    <div class="modal fade" id="addform" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Create Data</h4>
            </div>
            @include('errors.validation')
            {{-- {!! Form::open(['route' => ['company.store'],'method' => 'post','id'=>'form']) !!} --}}
            {!! Form::open(['id'=>'ADD']) !!}
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('kode Company', 'Kode Company:') }}
                                    {{ Form::text('kode_company', null, ['class'=> 'form-control','id'=>'Kode1']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('Nama Company', 'Nama Company:') }}
                                    {{ Form::text('nama_company', null, ['class'=> 'form-control','id'=>'Nama1','required'=>'required']) }}
                                    <span class="text-danger">
                                        <strong class="name-error" id="name-error"></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('alamat', 'Alamat:') }}
                                    {{ Form::textArea('alamat', null, ['class'=> 'form-control','rows'=>'4','id'=>'Alamat1','required'=>'required']) }}
                                    <span class="text-danger">
                                        <strong class="alamat-error" id="alamat-error"></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('telp', 'Telp:') }}
                                    {{ Form::text('telp', null, ['class'=> 'form-control','id'=>'Telp1','required'=>'required']) }}
                                    <span class="text-danger">
                                        <strong class="telp-error" id="kode-error"></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('Npwp', 'Npwp:') }}
                                    {{ Form::text('npwp', null, ['class'=> 'form-control','id'=>'Npwp1','required'=>'required']) }}
                                    <span class="text-danger">
                                        <strong class="npwp-error" id="npwp-error"></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('status', 'Status:') }}<br>
                                    {{Form::select('status', ['1' => 'Aktif', '0' => 'Non Aktif'], '1', ['class'=> 'form-control','id'=>'Status1'])}}
                                    <span class="text-danger">
                                        <strong class="status-error" id="status-error"></strong>
                                    </span>
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

    <div class="modal fade" id="editform" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Edit Data</h4>
            </div>
            @include('errors.validation')
            {{-- {!! Form::open( ['route' => ['company.ajaxupdate'],'method' => 'post','id'=>'Update']) !!} --}}
            {!! Form::open(['id'=>'EDIT']) !!}
            <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('kode Company', 'Kode Company:') }}
                                {{ Form::text('kode_company', null, ['class'=> 'form-control','id'=>'Kode']) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('Nama Company', 'Nama Company:') }}
                                {{ Form::text('nama_company', null, ['class'=> 'form-control','id'=>'Nama']) }}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('alamat', 'Alamat:') }}
                                {{ Form::textArea('alamat', null, ['class'=> 'form-control','rows'=>'4','id'=>'Alamat']) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('telp', 'Telp:') }}
                                {{ Form::text('telp', null, ['class'=> 'form-control','id'=>'Telp']) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('Npwp', 'Npwp:') }}
                                {{ Form::text('npwp', null, ['class'=> 'form-control','id'=>'Npwp']) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('status', 'Status:') }}<br>
                                {{Form::select('status', ['1' => 'Aktif', '0' => 'Non Aktif'], '1', ['class'=> 'form-control','id'=>'Status'])}}
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
@stop

@push('css')

@endpush
@push('js')
  
    <script>
        $(function() {
            $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('company.data') !!}',
            columns: [
                { data: 'kode_company', name: 'kode_company' },
                { data: 'nama_company', name: 'nama_company' },
                { data: 'alamat', name: 'alamat' },
                { data: 'telp', name: 'telp' },
                { data: 'npwp', name: 'npwp' },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                // { data: 'updated_at', name: 'updated_at' },
                // { data: 'created_by', name: 'created_by' },
                // { data: 'updated_by', name: 'updated_by' },
                { data: 'action', name: 'action' }
            ]
            });
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
            // Get the Login Name value and trim it
            var kode = $.trim($('#Kode1').val());
            var name = $.trim($('#Nama1').val());
            var alamat = $.trim($('#Alamat1').val());
            var telp = $.trim($('#Telp1').val());
            var npwp = $.trim($('#Npwp1').val());
            var status = $.trim($('#Status1').val());
            var registerForm = $("#ADD");
            var formData = registerForm.serialize();

            // Check if empty of not
            if (kode === '' || name === ''|| alamat === ''|| telp === ''|| npwp === ''|| status === '') {
                    if(kode === ''){
                        $( '.kode-error' ).html('Mohon di Isi');
                    }
                    if(name === ''){
                        $( '.name-error' ).html('Mohon di Isi');
                    }
                    if(alamat === ''){
                        $( '.alamat-error' ).html('Mohon di Isi');
                    }
                    if(telp === ''){
                        $( '.telp-error' ).html('Mohon di Isi');
                    }
                    if(npwp === ''){
                        $( '.npwp-error' ).html('Mohon di Isi');
                    }
                    if(status === ''){
                        $( '.status-error' ).html('Mohon di Isi');
                    }

                // alert('Mohon Lengkapi Form Isian');
                // return false;
            }else{
                $.ajax({
                    url:'{!! route('company.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#Kode1').val('');
                        $('#Nama1').val('');
                        $('#Alamat1').val('');
                        $('#Telp1').val('');
                        $('#Npwp1').val('');
                        $('#Status1').val('');
                        $('#addform').modal('hide');
                        refreshTable();
                        $.notify(data.message, "success");
                    },
                });
            }
        });

        $('#EDIT').submit(function (e) {
            e.preventDefault();
            // Get the Login Name value and trim it
            var kode = $.trim($('#Kode').val());
            var name = $.trim($('#Nama').val());
            var alamat = $.trim($('#Alamat').val());
            var telp = $.trim($('#Telp').val());
            var npwp = $.trim($('#Npwp').val());
            var status = $.trim($('#Status').val());
            var registerForm = $("#EDIT");
            var formData = registerForm.serialize();

            // Check if empty of not
            if (kode === '' || name === ''|| alamat === ''|| telp === ''|| npwp === ''|| status === '') {
                alert('Mohon Lengkapi Form Isian');
                return false;
            }else{
                $.ajax({
                    url:'{!! route('company.ajaxupdate') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#editform').modal('hide');
                        refreshTable();
                        $.notify(data.message, "success");
                    },
                });
            }
        });

        function edit(id, url) {
            var result = confirm("Want to Edit?");
            if (result) {
                // console.log(id)
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(result) {
                        console.log(result);
                        $('#Kode').val(result.kode_company);
                        $('#Nama').val(result.nama_company);
                        $('#Alamat').val(result.alamat);
                        $('#Telp').val(result.telp);
                        $('#Npwp').val(result.npwp);
                        $('#Status').val(result.status);
                        $('#editform').modal('show');
                    }
                });
            }
        }

        function del(id, url) {
            var result = confirm("Want to delete?");
            if (result) {
                // console.log(id)
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    success: function(result) {
                        console.log(result);
                        $.notify(result.message, "success");
                        refreshTable();
                    }
                });
            }

        }
    </script>
@endpush