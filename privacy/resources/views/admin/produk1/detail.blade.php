@extends('adminlte::page')

@section('title', 'Produk 1')

@section('content_header')

@endsection

@section('content')

    <body onload="load()">
        {{-- box --}}

        <div class="box box-solid">
            <div class="box-body">
                {{-- header button --}}
                <div class="box">
                    <div class="box-body">
                        {{-- to back to produk index --}}
                        <a href="{{ route('produk1.index') }}" class="btn btn-warning btn-xs"><i class="fa fa-arrow-left"
                                aria-hidden="true"></i> Back</a>

                        {{-- refresh table --}}
                        <button class="btn btn-default btn-xs" onclick="refreshTableProduct()"><i class="fa fa-refresh"></i>
                            Refresh</button>
                    </div>
                </div>

                {{-- responsive table --}}
                
                <a href="#" id="show-produk-link"><h3 class="text-center" id="nama_produk_show">{{ $produk->nama_produk }}</h3></a>
                <table class="table table-bordered table-striped table-hover" width="100%" style="font-size: 12px;">
                    <tr>
                        <th style="width: 30%">Kode Produk</th>
                        <td>
                            <span id="kode_produk_show">{{ $produk->id }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Tipe Produk</th>
                        <td>
                            <span id="tipe_produk_show">{{ $produk->tipe_produk }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td>
                            <span id="kategori_show">{{ $produk->kategoriproduk->nama_kategori }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Unit</th>
                        <td>
                            <span id="unit_show">{{ $produk->kode_unit }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Merek</th>
                        <td>
                            <span id="merek_show">{{ $produk->kode_merek }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Ukuran</th>
                        <td>
                            <span id="ukuran_show">{{ $produk->kode_ukuran }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Satuan</th>
                        <td>
                            <span id="satuan_show">{{ $produk->kode_satuan }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Part Number</th>
                        <td>
                            <span id="partnumber_show">{{ $produk->partnumber }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Stock</th>
                        <td>
                            <span id="stock_show">{{ $ending_stock }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Harga Beli</th>
                        <td>
                            <span id="beli_show">Rp. {{ $produk->harga_beli }}</span>
                        </td>
                    </tr>
                    @permission('read-hpp')
                        <tr>
                            <th>Harga Jual</th>
                            <td>
                                <span id="jual_show">Rp. {{ $produk->harga_jual }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Hpp</th>
                            <td>
                                <span id="hpp_show">Rp. {{ $detail->hpp }}</span>
                            </td>
                        </tr>
                    @endpermission
                    <tr>
                        <th>Company</th>
                        {{-- <td>
                            <span id="company">{{ $produk->company->nama_company }}</span>
                        </td> --}}
                        <td>
                            <span id="company"> <?= isset($produk->company->nama_company) ? $produk->company->nama_company : 'Not Set'; ?> </span>
                        </td>
                    </tr>
                </table>

                {{-- show button to edit and delete --}}
                <div class="show-button pull-right" style="margin-top: 1.5rem;">
                    <button id="edit-show" class="btn btn-xs btn-success" value="{{ $produk->id }}"><i
                            class="fa fa-edit"></i> Edit Produk</button>
                    <button id="hapus-show" class="btn btn-xs btn-danger" value="{{ $produk->id }}"><i
                            class="fa fa-times-circle"></i> Hapus</button>
                </div>

                {{-- additional information about the selected row --}}
                <div class="row" style="margin-top: 5rem">
                    {{-- created by input with readonly --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('created_by', 'Created By : ') }}
                            {{ Form::text('created_by', $produk->created_by, [
                                'class' => 'form-control',
                                'readonly',
                            ]) }}
                        </div>
                    </div>

                    {{-- created at input with readonly --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('created_at', 'Created At : ') }}
                            {{ Form::text('created_at', $produk->created_at, [
                                'class' => 'form-control',
                                'readonly',
                            ]) }}
                        </div>
                    </div>

                    {{-- updated by input with readonly --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('updated_by', 'Updated By : ') }}
                            {{ Form::text('updated_by', $produk->updated_by, [
                                'class' => 'form-control',
                                'readonly',
                            ]) }}
                        </div>
                    </div>

                    {{-- updated at input with readonly --}}
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('updated_at', 'Updated At : ') }}
                            {{ Form::text('updated_at', $produk->updated_at, [
                                'class' => 'form-control',
                                'readonly',
                            ]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- creting random qrcode/no-library --}}
        {{-- <div>
            {!! QrCode::size(100)->generate('eko saputra') !!}
        </div> --}}
    </body>

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

    {{-- modal for show selected row --}}
    <div class="modal fade" id="show-produk" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md " role="document">
            <div class="modal-content">
                {{-- modal header --}}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Show Detail Produk</h4>
                </div>

                {{-- modal body --}}
                <div class="modal-body">
                    <table class="table table-bordered table-striped" width="100%" style="font-size: 12px;">
                        <tr>
                            <th style="width: 30%">Status Produk</th>
                            <td>
                                <p id="status_show_detail"></p>
                            </td>
                        </tr>
                        <tr>
                            <th>Min Qty</th>
                            <td>
                                <p id="min_qty_show_detail"></p>
                            </td>
                        </tr>
                        <tr>
                            <th>Max Qty</th>
                            <td>
                                <p id="max_qty_show_detail"></p>
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
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <link rel="icon" type="image/png" href="/gui_inventory_gut_laravel/css/logo_gui.png" sizes="16x16">
    <link rel="icon" type="image/png" href="/gui_inventory_gut_laravel/css/logo_gui.png" sizes="32x32">

    <style type="text/css">
    </style>
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>

    <script type="text/javascript">
        // when page is loaded for the first time and call load() function to call startTime() function to run the timer
        function load() {
            startTime();
        }

        // ajac setup for editing form
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ajax for getting data for produk1
        $(function() {
            // first function
        })

        // auto caplocks
        function autoCaps(e) {
            e.value = e.value.toUpperCase();
        }

        // select2 form to make it more beautiful
        $('.select2').select2({
            placeholder: "  Pilih...",
            allowClear: true,
        });

        // edit show button
        $('#edit-show').click(function(e) {
            // set the product id
            const productId = e.target.value;

            // request to server to get the information about the current of the product using productId
            $.ajax({
                url: '{{ route('produk1.index') }}/' + productId + '/edit',
                type: 'GET',
                success: function(data) {
                    $('#kode_produk_edit').val(productId);
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
                    // show the modal
                    $('#editform').modal('show');
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
                    // refresh produk page
                    refreshTableProduct(false);

                    if (data.success === true) {
                        swal('Berhasil', data.message, 'success');
                    } else {
                        swal('Gagal', data.message, 'error');
                    }
                }
            })
        })


        // hapus show button
        $('#hapus-show').click(function(e) {
            // set the product id
            const productId = e.target.value;
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
                        url: '{{ route('produk1.index') }}/' + productId,
                        type: 'DELETE',
                        success: function(data) {
                            if (data.success === true) {
                                swal('Berhasil', data.message, 'success');
                                // redirecting to the produk1 index
                                setTimeout(function() {
                                    $(location).attr('href',
                                        '{{ route('produk1.index') }}/');
                                }, 1500);
                            } else {
                                swal('Gagal', data.message, 'error');
                            }
                        }
                    })
                }
            })
        })

        // function for refresing current product information
        function refreshTableProduct(notify = true) {
            const productId = $('#edit-show').val();
            $.ajax({
                url: '{{ route('produk1.index') }}/' + productId,
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
                    if (notify) {
                        $.notify('Data is upto-date');
                    }
                }
            })
        }

        // show produk link
        $('#show-produk-link').click(function() {
            // get product id
            const productId = $('#edit-show').val();

            // get product information using ajax
            $.ajax({
                url: '{{ route('produk1.index') }}/' + productId + '/edit',
                type: 'GET',
                success: function(data) {
                    $('#status_show_detail').html(data.stat);
                    $('#min_qty_show_detail').html(data.min_qty);
                    $('#max_qty_show_detail').html(data.max_qty);
                    // showing the modal
                    $('#show-produk').modal('show');
                }
            })
        })

        // when document is ready
        $(document).ready(function() {

        })


        // rupiah formatter
        function formatRupiah(angka, prefix = 'Rp ') {
            return prefix + angka.toLocaleString(undefined, {
                minimumFractionDigits: 0
            });
        }
    </script>
@endpush
