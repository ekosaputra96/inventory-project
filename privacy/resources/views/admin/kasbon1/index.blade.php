@extends('adminlte::page')

@section('title', 'Permintaan Kasbon')

@section('content_header')

@stop

@include('sweet::alert')

@section('content')

    <body onload="load()">
        <div class="box box-solid">
            <div class="box-body">
                {{-- header --}}
                <div class="box">
                    {{-- header buttons --}}
                    <div class="box-body">
                        {{-- for refresing data table --}}
                        <button class="btn btn-default btn-xs" onclick="refreshTable()"><i class="fa fa-refresh"></i> Refresh</button>

                        {{-- permintaan kasbon --}}
                        <button class="btn btn-primary btn-xs" data-toggle="modal" data-target="#kasbon-addform"><i class="fa fa-plus"></i> Permintaan Kasbon Baru</button>

                        {{-- table header name --}}
                        <div class="pull-right" style="font-size: 17px"><b>PERMINTAAN KASBON</b></div>
                    </div>
                </div>

                {{-- table for kasbon --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" style="font-size: 12px">
                        <thead>
                            <tr class="bg-info">
                                <th>NO PKB</th>
                                <th>Nama Pemohon</th>
                                <th>Tanggal Permintaan</th>
                                <th>Nilai</th>
                                <th>Keterangan</th>
                                <th>Status</th>
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
        <div id="mySidenav">
            <button class="btn btn-info btn-xs sidenav-item sidenav-item-open" id="kasbon-edit-button"><i class="fa fa-edit"></i> Edit</button>
            <button class="btn btn-warning btn-xs sidenav-item sidenav-item-open" id="kasbon-post-button"><i class="fa fa-bullhorn"></i> Post</button>
            <button class="btn btn-danger btn-xs sidenav-item sidenav-item-open" id="kasbon-hapus-button"><i class="fa fa-times-circle"></i> Hapus</button>
            <button class="btn btn-success btn-xs sidenav-item sidenav-item-posted" id="kasbon-approve-button"><i class="fa fa-bullhorn"></i> Approve</button>
            <button class="btn btn-warning btn-xs sidenav-item sidenav-item-posted" id="kasbon-unpost-button"><i class="fa fa-undo"></i> Unpost</button>
            <a href="#printkasbon" target="_blank" id="kasbon-print-button"><button class="btn btn-danger btn-xs sidenav-item sidenav-item-posted sidenav-item-approve" ><i class="fa fa-print"></i> Print</button></a>
        </div>
    </body>

    {{-- modal for kasbon addform --}}
    <div class="modal fade" id="kasbon-addform" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {{-- the header of the modal --}}
                <div class="modal-header">
                    {{-- button for closing the modal --}}
                    <button class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Create New Permintaan Kasbon</h4>
                </div>
                @include('errors.validation')

                {{-- Form for adding new permintaan kasbon --}}
                {{Form::open(['id' => 'ADD'])}}

                {{-- the body of the modal --}}
                <div class="modal-body">
                    <div class="row">
                        {{-- tanggal permintaan --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{Form::label('tanggal_permintaan_add', 'Tanggal PKB : ')}}
                                {{Form::date('tanggal_permintaan_add', \Carbon\Carbon::now(), [
                                    'class' => 'form-control',
                                    'required',
                                    'autocomplete' => 'off'
                                ])}}
                            </div>
                        </div>

                        {{-- nama pemohon --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{Form::label('nama_pemohon_add', 'Nama Pemohon :')}}
                                {{Form::text('nama_pemohon_add', null, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Nama Pemohon...',
                                    'oninput' => 'autoCaps(this)',
                                    'autocomplete' => 'off'
                                ])}}
                            </div>
                        </div>

                        {{-- jumlah nominal --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{Form::label('nilai_add', 'Jumlah Nominal : ')}}
                                {{Form::number('nilai_add', null, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Nominal Kasbon...',
                                    'autocomplete' => 'off'
                                ])}}
                            </div>
                        </div>

                        {{-- keterangan --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                {{Form::label('keterangan_add', 'Keterangan : ')}}
                                {{Form::textarea('keterangan_add', null, [
                                    'class' => 'form-control',
                                    'rows' => 3,
                                    'autocomplete' => 'off',
                                    'placeholder' => 'Keterangan...'
                                ])}}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- the footer of the modal --}}
                <div class="modal-footer">
                    <div class="row">
                        {{Form::submit('Create', [
                            'class' => 'btn btn-success'
                        ])}}
                        {{Form::button('Close', [
                            'class' => 'btn btn-danger',
                            'data-dismiss' => 'modal'
                        ])}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                </div>
                {{Form::close()}}
                </div>
            </div>
        </div>
    </div>

    {{-- modal for kasbon editform --}}
    <div class="modal fade" id="kasbon-editform" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {{-- the header of the modal --}}
                <div class="modal-header">
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Edit Data Kasbon</h4>
                </div>

                @include('errors.validation')
                {{-- the body of the modal --}}
                {{Form::open(['id' => 'EDIT'])}}
                <div class="modal-body">
                    <div class="row">
                        {{-- hidden no_pkb edit --}}
                        {{Form::hidden('no_pkb_edit', null, [
                            'id' => 'no_pkb_edit'
                        ])}}

                        {{-- tanggal permintaan edit --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{Form::label('tanggal_permintaan_edit', 'Tanggal PKB :')}}
                                {{Form::date('tanggal_permintaan_edit', null, [
                                    'class' => 'form-control',
                                    'required'
                                ])}}
                            </div>
                        </div>

                        {{-- nama pemohon edit --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{Form::label('nama_pemohon_edit', 'Nama Pemohon :')}}
                                {{Form::text('nama_pemohon_edit', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => 'Nama Pemohon...',
                                    'oninput' => 'autoCaps(this)',
                                    'autocomplete' => 'off'
                                ])}}
                            </div>
                        </div>

                        {{-- nilai edit --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{Form::label('nilai_edit', 'Jumlah Nominal :')}}
                                {{Form::number('nilai_edit', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => 'Jumlah Nominal...',
                                    'autocomplete' => 'off'
                                ])}}
                            </div>
                        </div>

                        {{-- keterangan --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                {{Form::label('keterangan_edit', 'Keterangan : ')}}
                                {{Form::textarea('keterangan_edit', null, [
                                    'class' => 'form-control',
                                    'rows' => 3,
                                    'autocomplete' => 'off',
                                    'placeholder' => 'Keterangan...'
                                ])}}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="row">
                        {{Form::submit('Update', [
                            'class' => 'btn btn-success'
                        ])}}
                        {{Form::button('Close', [
                            'class' => 'btn btn-danger',
                            'data-dismiss' => 'modal'
                        ])}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                </div>
                {{Form::close()}}
            </div>
        </div>
    </div>
@stop

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <style type="text/css">
        #mySidenav button {
            position: fixed;
            right: -12px;
            top: 400px;
            transition: 0.3s;
            width: 85px;
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
            width: 90px;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <script type="text/javascript">
        function load() {
            startTime();

            // hide all sidenav-item
            $('.sidenav-item').hide();
        }

        // ajax setup for editing form
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // getting kasbon from server
        $(function() {
            $('#data-table').DataTable({
                processing: true,
                serverSide: true,
                'scrollY': 270,
                'scrollX': 400,
                ajax: '{{ route('kasbon1.getkasbon') }}',
                fnRowCallback: function(row, data, index){
                    // console.log(.text())
                    if(data['status'] == 'OPEN'){
                        $(row).find("td:last").css('background-color', '#E8b301');
                    }else if(data['status'] == 'APPROVED'){
                        $(row).find("td:last").css('background-color', '#49be25');
                    }else if(data['status'] == 'POSTED'){
                        $(row).find("td:last").css('background-color', '#E4E801');
                    }
                },
                columns: [
                    {
                        data: 'no_pkb',
                        "fnCreatedCell": function(nTd, sData, oData, iRow, iCol) {
                            $(nTd).html("<a href='{{ route('kasbon1.index') }}/" + oData
                                .no_pkb + "'>" + oData
                                .no_pkb + "</a>")
                        }
                    },
                    {
                        data: 'nama_pemohon'
                    },
                    {
                        data: 'tanggal_permintaan'
                    },
                    {
                        data: 'nilai',
                        render: function(data, type, full){
                            return formatRupiah(parseInt(data));
                        }
                    },
                    {
                        data: 'keterangan'
                    },
                    {
                        data: 'status'
                    }
                ]
            })
        })

        // when document is ready
        $(document).ready(function() {
            const table = $('#data-table').DataTable();

            $('#data-table tbody').on('dblclick', 'tr', function() {
                // if the selected row has the class, remove it
                if($(this).hasClass('selected bg-gray text-bold')){
                    $(this).removeClass('selected bg-gray text-bold')
                    $('.sidenav-item').hide();
                    $('#created_by').val('');
                    $('#created_at').val('');
                    $('#updated_by').val('');
                    $('#updated_at').val('');
                }else{
                    // if not, remove selected class from table then add selected class
                    table.$('tr').removeClass('selected bg-gray text-bold');
                    $('.sidenav-item').hide();
                    $(this).addClass('selected bg-gray text-bold')

                    // action buttons according to the status value
                    if($(this).find('td:last').text() == 'OPEN'){
                        // sidenav-item margin-top
                        let marginTop = 0;
                        $('button.sidenav-item-open').each(function(i, obj) {
                            $(obj).css('margin-top', marginTop + 'px');
                            marginTop += 30;
                        })
                        $('.sidenav-item-open').show();
                    }else if($(this).find('td:last').text() == 'POSTED'){
                        // sidenav-item margin-top
                        let marginTop = 0;
                        $('button.sidenav-item-posted').each(function(i, obj) {
                            $(obj).css('margin-top', marginTop + 'px');
                            marginTop += 30;
                        })
                        $('.sidenav-item-posted').show();
                    }else if($(this).find('td:last').text() == 'APPROVED'){
                        // sidenav-item margin-top
                        let marginTop = 0;
                        $('button.sidenav-item-approve').each(function(i, obj) {
                            $(obj).css('margin-top', marginTop + 'px');
                            marginTop += 30;
                        })
                        $('.sidenav-item-approve').show();
                    }

                    // getting data from table to fill additional information (created_at, created_by etc)
                    // set the selected row
                    const selectedRow = $('.selected').closest('tr');

                    const data = table.row(selectedRow).data();
                    $('#created_by').val(data['created_by'])
                    $('#created_at').val(data['created_at'])
                    $('#updated_by').val(data['updated_by'])
                    $('#updated_at').val(data['updated_at'])
                }
            })

            // edit data kasbon
            $('#kasbon-edit-button').click(function() {
                const rowId = getRowId(table);
                $.ajax({
                    url: '{{route('kasbon1.index')}}/' + rowId + '/edit',
                    type: 'GET',
                    success: function(data) {
                        $('#no_pkb_edit').val(data.no_pkb);
                        $('#tanggal_permintaan_edit').val(data.tanggal_permintaan);
                        $('#nama_pemohon_edit').val(data.nama_pemohon);
                        $('#nilai_edit').val(data.nilai);
                        $('#keterangan_edit').val(data.keterangan);
                        $('#kasbon-editform').modal('show');
                    }
                })
            })

            // hapus data kasbon
            $('#kasbon-hapus-button').click(function() {
                // getting rowId
                const rowId = getRowId(table);

                // confirmation button for deleting selected kasbon
                swal({
                    title: 'Hapus',
                    text: 'Yakin akan menghapus '+ rowId +' ?',
                    type: 'warning',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then(function(e) {
                    if(e.value === true){
                        $.ajax({
                            url: '{{route('kasbon1.index')}}/' + rowId,
                            type: 'DELETE',
                            success: function(data) {
                                if(data.success === true){
                                    swal(data.title, data.message, 'success')
                                }else{
                                    swal(data.title, data.message, 'error')
                                }
                                refreshTable(false);
                                $('.sidenav-item').hide();
                            }
                        })
                    }
                })
            })

            // post data kasbon
            $('#kasbon-post-button').click(function() {
                const rowId = getRowId(table);
                swal({
                    title: 'Post',
                    text: 'Yakin akan post ' + rowId + ' ?',
                    type: 'warning',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: 'Ya, Posting',
                    cancelButtonText: 'Batal'
                }).then(function(e) {
                    if(e.value === true){
                        // loading notification
                        swal({
                            title: 'Loading',
                            text: 'Please, wait for a moment...',
                            type: 'warning',
                            showCancelButton: false,
                            showConfirmButton: false
                        })

                        // sending request to the server
                        $.ajax({
                            url: '{{route('kasbon1.postkasbon')}}',
                            type: 'POST',
                            data: {
                                'no_pkb': rowId,
                            },
                            success: function(data){
                                if(data.success === true){
                                    swal(data.title, data.message, 'success');
                                }else{
                                    swal(data.title, data.message, 'error');
                                }
                                refreshTable(false);
                                $('.sidenav-item').hide();
                            },
                            error: function() {
                                swal({
                                    title: 'Opss... something wrong',
                                    type: 'error',
                                    timer: '1000'
                                })
                            }
                        })
                    }
                })
            })

            // unpost data kasbon
            $('#kasbon-unpost-button').click(function() {
                const rowId = getRowId(table);
                swal({
                    title: 'Unpost',
                    text: 'Yakin akan unpost ' + rowId + ' ?',
                    type: 'warning',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: 'Ya, Unposting',
                    cancelButtonText: 'Batal'
                }).then(function(e) {
                    if(e.value === true){
                        // loading notification
                        swal({
                            title: 'Loading',
                            text: 'Please, wait for a moment...',
                            type: 'warning',
                            showCancelButton: false,
                            showConfirmButton: false
                        })

                        // sending request to the server
                        $.ajax({
                            url: '{{route('kasbon1.unpostkasbon')}}',
                            type: 'POST',
                            data: {
                                'no_pkb': rowId,
                            },
                            success: function(data){
                                if(data.success === true){
                                    swal(data.title, data.message, 'success');
                                }else{
                                    swal(data.title, data.message, 'error');
                                }
                                refreshTable(false);
                                $('.sidenav-item').hide();
                            },
                            error: function() {
                                swal({
                                    title: 'Opss... something wrong',
                                    type: 'error',
                                    timer: '1000'
                                })
                            }
                        })
                    }
                })
            })

            // approve data kasbon
            $('#kasbon-approve-button').click(function() {
                const rowId = getRowId(table);
                swal({
                    title: 'Approved',
                    text: 'Yakin akan approved ' + rowId + ' ?',
                    type: 'warning',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: 'Ya, Approved',
                    cancelButtonText: 'Batal'
                }).then(function(e) {
                    if(e.value === true){
                        // loading notification
                        swal({
                            title: 'Loading',
                            text: 'Please, wait for a moment...',
                            type: 'warning',
                            showCancelButton: false,
                            showConfirmButton: false
                        })

                        // sending request to the server
                        $.ajax({
                            url: '{{route('kasbon1.approvedkasbon')}}',
                            type: 'POST',
                            data: {
                                'no_pkb': rowId,
                            },
                            success: function(data){
                                if(data.success === true){
                                    swal(data.title, data.message, 'success');
                                }else{
                                    swal(data.title, data.message, 'error');
                                }
                                refreshTable(false);
                                $('.sidenav-item').hide();
                            },
                            error: function() {
                                swal({
                                    title: 'Opss... something wrong',
                                    type: 'error',
                                    timer: '1000'
                                })
                            }
                        })
                    }
                })
            })

            // print data kasbon
            $('#kasbon-print-button').click(function() {
                const rowId = getRowId(table);
                $('#kasbon-print-button').attr('href', '{{route('kasbon1.index')}}/exportpdf/' + rowId);
            })
        })

        // getting row id
        function getRowId(table) {
            const selectedRow = $('.selected').closest('tr');
            return table.row(selectedRow).data()['no_pkb'];
        }

        // submitting the ADD form
        $('#ADD').submit(function(e) {
            e.preventDefault();
            const data = $('#ADD').serialize();
            $.ajax({
                url: '{{route('kasbon1.store')}}',
                type: 'POST',
                data: data,
                success: function(data) {
                    const date = new Date().toLocaleDateString('en-CA');
                    $('#tanggal_permintaan_add').val(date);
                    $('#nama_pemohon_add').val('');
                    $('#nilai_add').val('');
                    $('#keterangan_add').val('');
                    $('#kasbon-addform').modal('hide');
                    refreshTable();
                    if(data.success === true){
                        swal(data.title, data.message, 'success');
                    }else{
                        swal(data.title, data.message, 'error');
                    }
                }
            })
        })

        // submitting the EDIT form
        $('#EDIT').submit(function(e) {
            e.preventDefault();
            // set no_pkb
            const no_pkb = $('#no_pkb_edit').val();
            const data = $('#EDIT').serialize();
            $.ajax({
                url: '{{route('kasbon1.index')}}/' + no_pkb,
                type: 'PUT',
                data: data,
                success: function(data) {
                    refreshTable(false);
                    $('#kasbon-editform').modal('hide');
                    $('#no_pkb_edit').val('');
                    $('#tanggal_permintaan_edit').val('');
                    $('#nama_pemohon_edit').val('');
                    $('#nilai_edit').val('');
                    $('#keterangan_edit').val('');
                    $('.sidenav-item').hide();
                    if(data.success === true){
                        swal(data.title, data.message, 'success');
                    }else{
                        swal(data.title, data.message, 'error');
                    }
                }
            })
        })

        // reload the datatables
        function refreshTable(notif = true) {
            $('#data-table').DataTable().ajax.reload(null, false)
            if(notif){
                $.notify('Data is upto-date')
            }
            $('.sidenav-item').hide();
        }

        // rupiah formatter
        function formatRupiah(angka, prefix = 'Rp ') {
            return prefix + angka.toLocaleString(undefined, {
                minimumFractionDigits: 0
            });
        }

        // autocaps
        function autoCaps(e){
            e.value = e.value.toUpperCase();
        }
    </script>
@endpush
