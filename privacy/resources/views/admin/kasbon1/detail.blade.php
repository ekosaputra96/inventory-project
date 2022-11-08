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
                <div class="box" style="margin-bottom: 0px">
                    {{-- header buttons --}}
                    <div class="box-body">
                        {{-- back to index kasbon1 --}}
                        <a href="{{ route('kasbon1.index') }}" class="btn btn-warning btn-xs"><i class="fa fa-arrow-left"
                                aria-hidden="true"></i> Back</a>

                        {{-- for refresing data table --}}
                        <button class="btn btn-default btn-xs" onclick="refreshTableData()"><i class="fa fa-refresh"></i>
                            Refresh</button>

                        {{-- right buttons --}}
                        <div class="pull-right">
                            {{-- for approving the selected kasbon --}}
                            <button class="btn btn-success btn-xs posted-mode" id="kasbon-approve"><i class="fa fa-bullhorn"></i> Approve</button>

                            {{-- for unposting selected kasbon --}}
                            <button class="btn btn-warning btn-xs posted-mode" id="kasbon-unpost"><i class="fa fa-undo"></i> Unpost</button>

                            {{-- for posting selected kasbon --}}
                            <button class="btn btn-warning btn-xs open-mode" id="kasbon-post"><i class="fa fa-bullhorn"></i> Post</button>

                            {{-- for printing the kasbon --}}
                            <a href="#" target="_blank" id="kasbon-print" class="posted-mode approved-mode"><button class="btn btn-info btn-xs" ><i class="fa fa-print"></i> Print</button></a>

                            {{-- for deleting kasbon --}}
                            <button class="btn btn-danger btn-xs open-mode" id="kasbon-delete"><i class="fa fa-times-circle"></i>
                                Hapus</button>
                        </div>
                    </div>
                </div>

                {{-- content and form --}}
                @include('errors.validation')
                {{ Form::open(['id' => 'EDIT']) }}
                <div class="content">
                    <div class="row">
                        <div class="col-md-10"></div>
                        <div class="col-md-2 text-center status-color">
                            <span>Status : </span>
                            <b id="status_edit"></b>
                        </div>
                    </div>
                    <h2 class="text-center header-heading text-bold">PERMINTAAN KASBON</h2>
                    <br>

                    {{-- row data kasbon --}}
                    <div class="row">
                        {{-- label for no_pkb --}}
                        <div class="col-md-2">
                            {{ Form::label('no_pkb_edit', 'NO PKB', [
                                'style' => 'font-weight: normal;',
                            ]) }}
                            <span class="span-double-colons span-double-colons-pkb">:</span>
                        </div>

                        {{-- input for no_pkb --}}
                        <div class="col-md-4">
                            {{ form::text('no_pkb_edit', $kasbon->no_pkb, [
                                'class' => 'form-control text-bold',
                                'readonly',
                            ]) }}
                        </div>

                        {{-- for spacing --}}
                        <div class="col-md-2"></div>

                        {{-- label for tanggal_permintaan --}}
                        <div class="col-md-2">
                            {{ Form::label('tanggal_permintaan_edit', 'Tanggal Permintaan', [
                                'style' => 'font-weight: normal;',
                            ]) }}
                            <span class="span-double-colons span-double-colons-permintaan">:</span>
                        </div>

                        {{-- input for tanggal_permintaan --}}
                        <div class="col-md-2">
                            <span class="read-mode" id='tanggal_permintaan_edit_format'></span>
                            {{ Form::date('tanggal_permintaan_edit', $kasbon->tanggal_permintaan, [
                                'class' => 'form-control edit-mode edit-mode-button',
                                'required',
                                'readonly',
                            ]) }}
                        </div>
                    </div>

                    <div class="row row-height">
                        {{-- label for nama_pemohon --}}
                        <div class="col-md-2">
                            {{ Form::label('nama_pemohon_edit', 'Nama Pemohon', [
                                'style' => 'font-weight: normal;',
                            ]) }}
                            <span class="span-double-colons span-double-colons-pemohon">:</span>
                        </div>

                        {{-- input for nama_pemohon --}}
                        <div class="col-md-4">
                            {{ form::text('nama_pemohon_edit', $kasbon->nama_pemohon, [
                                'class' => 'form-control edit-mode-button',
                                'readonly',
                                'oninput' => 'autoCaps(this)',
                            ]) }}
                        </div>
                    </div>
                    {{-- end data kasbon --}}

                    <hr>

                    {{-- keterangan --}}
                    <div class="row">
                        <div class="col-md-2">
                            {{ Form::label('keterangan_edit', 'Keterangan', [
                                'style' => 'font-weight: normal; margin-left: 10px',
                            ]) }}
                            <span class="span-double-colons span-double-colons-keterangan">:</span>
                        </div>
                        <div class="col-md-10">
                            {{ Form::textarea('keterangan_edit', $kasbon->keterangan, [
                                'class' => 'form-control edit-mode-button',
                                'rows' => 3,
                                'autocomplete' => 'off',
                                'placeholder' => 'Keterangan...',
                                'readonly',
                            ]) }}
                        </div>
                    </div>

                    <hr>
                    {{-- total --}}
                    <div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-2">
                            {{ Form::label('nilai_edit', 'Total', [
                                'style' => 'font-weight: normal;',
                            ]) }}
                            <span class="span-double-colons span-double-colons-nilai">: </span>
                        </div>
                        <div class="col-md-2">
                            <span class="read-mode" id="nilai_edit_rupiah"></span>
                            {{ Form::number('nilai_edit', $kasbon->nilai, [
                                'class' => 'form-control edit-mode edit-mode-button',
                                'required',
                                'placeholder' => 'Jumlah Nominal...',
                                'autocomplete' => 'off',
                                'readonly',
                            ]) }}
                        </div>
                    </div>
                    {{-- end keterangan --}}

                    {{-- terbilang --}}
                    <p>Terbilang : <b id="nilai_terbilang"></b></p>

                    {{-- buttons --}}
                    <div class="row">
                        <div class="col-md text-right">
                            {{ Form::submit('Update', [
                                'class' => 'btn btn-success',
                                'id' => 'kasbon-submit',
                            ]) }}
                            {{ Form::button('Edit', [
                                'class' => 'btn btn-primary open-mode',
                                'id' => 'kasbon-edit',
                            ]) }}
                            {{ Form::button('Cancel', [
                                'class' => 'btn btn-danger',
                                'id' => 'kasbon-cancel',
                            ]) }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </div>
                {{ Form::close() }}

                {{-- additional information about the selected row --}}

                <div class="row" style="margin-top: 1.5rem">
                    {{-- created by input with readonly --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('created_by', 'Created By : ') }}
                            {{ Form::text('created_by', $kasbon->created_by, [
                                'class' => 'form-control',
                                'readonly',
                            ]) }}
                        </div>
                    </div>

                    {{-- created at input with readonly --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('created_at', 'Created At : ') }}
                            {{ Form::text('created_at', $kasbon->created_at, [
                                'class' => 'form-control',
                                'readonly',
                            ]) }}
                        </div>
                    </div>

                    {{-- updated by input with readonly --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('updated_by', 'Updated By : ') }}
                            {{ Form::text('updated_by', $kasbon->updated_by, [
                                'class' => 'form-control',
                                'readonly',
                            ]) }}
                        </div>
                    </div>

                    {{-- updated at input with readonly --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('updated_at', 'Updated At : ') }}
                            {{ Form::text('updated_at', $kasbon->updated_at, [
                                'class' => 'form-control',
                                'readonly',
                            ]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
@stop

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">

    <style type="text/css">
        .margin-left {
            margin-left: 9rem;
        }

        .header-heading {
            margin-top: 0px;
        }

        .row-height {
            margin-top: 1rem;
        }

        .span-double-colons-pkb {
            margin-left: 70px;
        }

        .span-double-colons-pemohon {
            margin-left: 22px;
        }

        .span-double-colons-permintaan {
            margin-left: 15px;
        }

        .span-double-colons-keterangan {
            margin-left: 38px;
        }

        .span-double-colons-nilai {
            margin-left: 100px;
        }

        .status-color {
            padding: 10px 0px;
            margin-bottom: 0.6rem;
        }

        @media(max-width: 1191px) {
            .row-height {
                margin-top: 0px;
            }

            .span-double-colons {
                margin-left: 0px;
            }
        }
    </style>
@endpush



@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <script type="text/javascript">
        // when the body first load
        function load() {
            startTime();
            refreshTableData(false);
            // terbilang('{{ $kasbon->nilai }}');
            // $('#kasbon-submit').hide()
            // $('#kasbon-cancel').hide()
            // $('.edit-mode').hide()
            // $('#nilai_edit_rupiah').html(formatRupiah('{{ $kasbon->nilai }}'));
            // $('#tanggal_permintaan_edit_format').html(formatTanggal('{{ $kasbon->tanggal_permintaan }}'));
        }

        // ajax setup for editing form
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // when document is ready
        $(document).ready(function() {
            // do something

            // kasbon edit button
            $('#kasbon-edit').click(function() {
                $(this).hide()
                $('.edit-mode').show()
                $('.read-mode').hide()
                $('#kasbon-submit').show()
                $('#kasbon-cancel').show()
                $('.edit-mode-button').removeAttr('readonly')
            })

            // kasbon cancel button
            $('#kasbon-cancel').click(function() {
                $(this).hide();
                $('#kasbon-edit').show();
                $('#kasbon-submit').hide()
                $('.edit-mode').hide()
                $('.read-mode').show()
                $('.edit-mode-button').attr('readonly', true);
                refreshTableData(false);
            })

            // kasbon hapus button
            $('#kasbon-delete').click(function() {
                const rowId = $('#no_pkb_edit').val();

                // confirmation button for deleting selected kasbon
                swal({
                    title: 'Hapus',
                    text: 'Yakin akan menghapus ' + rowId + ' ?',
                    type: 'warning',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then(function(e) {
                    if (e.value === true) {
                        $.ajax({
                            url: '{{ route('kasbon1.index') }}/' + rowId,
                            type: 'DELETE',
                            success: function(data) {
                                if (data.success === true) {
                                    swal(data.title, data.message, 'success');
                                    setTimeout(function() {
                                        $(location).attr('href',
                                            '{{ route('kasbon1.index') }}/'
                                            );
                                    }, 1500);
                                } else {
                                    swal(data.title, data.message, 'error')
                                }

                            }
                        })
                    }
                })
            })

            // kasbon post  the kasbon
            $('#kasbon-post').click(function() {
                const rowId = $('#no_pkb_edit').val();
                
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
                                refreshTableData(false);
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

            // kasbon unpost the kasbon
            $('#kasbon-unpost').click(function() {
                const rowId = $('#no_pkb_edit').val();

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
                                refreshTableData(false);
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
            $('#kasbon-print').click(function() {
                const rowId = $('#no_pkb_edit').val();
                $('#kasbon-print').attr('href', '{{route('kasbon1.index')}}/exportpdf/' + rowId);
            })

            // approve the kasbon
            $('#kasbon-approve').click(function() {
                const rowId = $('#no_pkb_edit').val();
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
                                refreshTableData(false);
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

            // submitting for EDIT
            $('#EDIT').submit(function(e) {
                e.preventDefault()
                const data = $(this).serialize();
                const no_pkb = $('#no_pkb_edit').val();

                // submit button behavior
                $('#kasbon-submit').val('Loading..')
                $('#kasbon-submit').attr("disabled", true);
                $.ajax({
                    url: '{{ route('kasbon1.index') }}/' + no_pkb,
                    type: 'PUT',
                    data: data,
                    success: function(data) {
                        refreshTableData(false);

                        // submit button behavior
                        $('#kasbon-submit').val('Update');
                        $('#kasbon-submit').attr("disabled", false);

                        if (data.success === true) {
                            swal(data.title, data.message, 'success');
                        } else {
                            swal(data.title, data.message, 'error');
                        }
                    }
                })
            })
        })

        // for refreshing table-data
        function refreshTableData(notif = true) {
            // getting no_pkb
            const id_pkb = '{{ $kasbon->no_pkb }}';
            $.ajax({
                url: '{{ route('kasbon1.index') }}/' + id_pkb + '/edit',
                type: 'GET',
                success: function(data) {
                    $('#nama_pemohon_edit').val(data.nama_pemohon);
                    $('#tanggal_permintaan_edit').val(data.tanggal_permintaan);
                    $('#no_pkb_edit').val(data.no_pkb);
                    $('#keterangan_edit').val(data.keterangan);
                    $('#status_edit').html(statusColors(data.status));
                    $('#nilai_edit').val(data.nilai);
                    $('#created_at').val(data.created_at);
                    $('#created_by').val(data.created_by);
                    $('#updated_at').val(data.updated_at);
                    $('#updated_by').val(data.updated_by);
                    $('#nilai_edit_rupiah').html(formatRupiah(data.nilai));
                    $('#tanggal_permintaan_edit_format').html(formatTanggal(data.tanggal_permintaan));
                    $('#kasbon-submit').hide()
                    $('#kasbon-edit').show();
                    $('#kasbon-cancel').hide();
                    $('.edit-mode').hide()
                    $('.read-mode').show()
                    $('.edit-mode-button').attr('readonly', true);
                    if(data.status == 'OPEN'){
                        $('.open-mode').show();
                        $('.posted-mode').hide();
                    }else if(data.status == 'POSTED'){
                        // 
                        $('.open-mode').hide();
                        $('.posted-mode').show();
                    }else if(data.status == 'APPROVED'){
                        // 
                        $('.open-mode').hide();
                        $('.posted-mode').hide();
                        $('.approved-mode').show();

                    }
                    terbilang(data.nilai);

                    // if notification is true
                    if (notif) {
                        $.notify('Data is upto-date');
                    }
                }
            })
        }

        // autocaps
        function autoCaps(e) {
            e.value = e.value.toUpperCase();
        }

        // rupiah formatter
        function formatRupiah(number) {
            return new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR"
            }).format(number);
        }

        // tanggal formatter
        function formatTanggal(tgl) {
            const date = new Date(tgl);
            return date.toLocaleDateString('id', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            });
        }

        // terbilang getting from server
        function terbilang(angka) {
            $.ajax({
                url: '{{ route('kasbon1.index') }}/terbilang/' + angka,
                type: 'GET',
                success: function(data) {
                    $('#nilai_terbilang').html(data.terbilang);
                }
            })
        }

        // status colors
        function statusColors(status) {
            if (status == 'OPEN') {
                $('.status-color').css('background-color', 'rgb(232, 179, 1)');
            } else if (status == 'POSTED') {
                $('.status-color').css('background-color', 'rgb(228, 232, 1)');
            } else if (status == 'APPROVED') {
                $('.status-color').css('background-color', 'rgb(73, 190, 37)');
            } else {
                $('.status-color').css('background-color', '#fff');
            }
            return status
        }
    </script>
@endpush
