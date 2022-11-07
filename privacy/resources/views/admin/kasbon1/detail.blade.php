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
                        {{-- back to index kasbon1 --}}
                        <a href="{{ route('kasbon1.index') }}" class="btn btn-warning btn-xs"><i class="fa fa-arrow-left"
                                aria-hidden="true"></i> Back</a>

                        {{-- for refresing data table --}}
                        <button class="btn btn-default btn-xs" onclick="refreshTableData()"><i class="fa fa-refresh"></i>
                            Refresh</button>
                    </div>
                </div>

                {{-- content and form --}}
                @include('errors.validation')
                {{ Form::open(['id' => 'EDIT']) }}
                <div class="content">
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
                            <span class="read-mode">{{$tanggal_permintaan_format}}</span>
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
                                'oninput' => 'autoCaps(this)'
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
                            <span class="read-mode">Rp. {{ number_format($kasbon->nilai, '2', '.', ',') }}</span>
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
                    <p>Terbilang : <b>{{ ucwords(Terbilang::make($kasbon->nilai, ' rupiah')) }}</b></p>

                    {{-- buttons --}}
                    <div class="row">
                        <div class="col-md text-right">
                            {{ Form::submit('Update', [
                                'class' => 'btn btn-success',
                                'id' => 'kasbon-submit',
                            ]) }}
                            {{ Form::button('Edit', [
                                'class' => 'btn btn-primary',
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
            $('#kasbon-submit').hide()
            $('#kasbon-cancel').hide()
            $('.edit-mode').hide()
        }

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
                    $('#nilai_edit').val(data.nilai);
                    $('#created_at').val(data.created_at);
                    $('#created_by').val(data.created_by);
                    $('#updated_at').val(data.updated_at);
                    $('#updated_by').val(data.updated_by);

                    // if notification is true
                    if (notif) {
                        $.notify('Data is upto-date');
                    }
                }
            })
        }

        // autocaps
        function autoCaps(e){
            e.value = e.value.toUpperCase();
        }
    </script>
@endpush
