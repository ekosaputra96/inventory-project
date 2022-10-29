@extends('adminlte::page')

@section('title', 'Company 1')

@section('content_header')

@endsection

@section('content')

    <body onload="load()">
        <div class="box box-solid">
            <div class="box-body">
                <div class="box">
                    <div class="box-body">
                        @permission('create-company')
                            {{-- creating new company if authorized --}}
                            <div id="newcompany">
                                <button type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#addform"><i
                                    class="fa fa-plus"></i> New Company</button></button>
                            </div>
                        @endpermission
                        {{-- label --}}
                        <span class="pull-right" style="font-size: 16px"><b>COMPANY 1</b></span>
                    </div>
                </div>

                {{-- company table --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="data-table" width="100%"
                        style="font-size: 12px;">
                        <thead>
                            <tr class="bg-primary">
                                <th>Kode Company</th>
                                <th>Nama Company</th>
                                <th>Alamat</th>
                                <th>Telp</th>
                                <th>Npwp</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <button type="button" class="back2top btn btn-warning btn-xs" id="back2top"><i class="fa fa-arrow-up"
                style="color: #fff"></i> <i>{{ $nama_company }}</i> <b>({{ $nama_lokasi }})</b></button>

        {{-- modal for add a new company --}}
        <div class="modal fade" id="addform" role="dialog" tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {{-- header for add form --}}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Create New Data</h4>
                    </div>

                    {{-- body for add form --}}
                    @include('errors.validation')
                    {{ Form::open(['id' => 'ADD']) }}
                    <div class="modal-body">
                        <div class="row">
                            {{-- nama company --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('nama_company', 'Nama Company : ') }}
                                    {{ Form::text('nama_company', null, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'Nama Company..',
                                        'autocomplete' => 'off',
                                        'oninput' => 'autoCaps(this)',
                                    ]) }}
                                </div>
                            </div>

                            {{-- alamat --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('alamat', 'Alamat : ') }}
                                    {{ Form::textarea('alamat', null, [
                                        'class' => 'form-control',
                                        'rows' => '4',
                                        'required',
                                        'placeholder' => 'Alamat..',
                                        'autocomplete' => 'off',
                                        'oninput' => 'autoCaps(this)',
                                    ]) }}
                                </div>
                            </div>

                            {{-- telp --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('telp', 'Telp : ') }}
                                    {{ Form::text('telp', null, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'No. Telp..',
                                        'autocomplete' => 'off',
                                        'onkeypress' => 'onlyNumber(event)',
                                    ]) }}
                                </div>
                            </div>

                            {{-- npwp --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('npwp', 'NPWP : ') }}
                                    <input type="text" name="number" style="display:none;">
                                    {{ Form::text('npwp', null, [
                                        'class' => 'form-control',
                                        'autocomplete' => 'off',
                                        'placeholder' => 'NPWP..',
                                    ]) }}
                                </div>
                            </div>

                            {{-- status --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('status', 'Status : ') }}
                                    {{ Form::select(
                                        'status',
                                        [
                                            'Aktif' => 'Aktif',
                                            'NonAktif' => 'Non Aktif',
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

                            {{-- tipe --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('tipe', 'Tipe :') }}
                                    {{ Form::select(
                                        'tipe',
                                        [
                                            'Pusat' => 'Pusat',
                                            'Cabang' => 'Cabang',
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control select2',
                                            'style' => 'width: 100%',
                                            'required',
                                            'placeholder' => '',
                                            'onchange' => 'tipes()',
                                        ],
                                    ) }}
                                </div>
                            </div>

                            {{-- kode company --}}
                            <div class="col-md-8 form-group2">
                                <div class="form-group">
                                    {{ Form::label('company1', 'Kode Company : ') }}
                                    {{ Form::select('kode_comp', $company, null, [
                                        'class' => 'form-control select2',
                                        'style' => 'width: 100%',
                                        'id' => 'company1',
                                        'placeholder' => '',
                                        'required',
                                    ]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        {{ Form::submit('Save Data', [
                            'class' => 'btn btn-success',
                        ]) }}
                        {{ Form::button('Close', [
                            'class' => 'btn btn-danger',
                            'data-dismiss' => 'modal',
                        ]) }}
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>


        {{-- modal for add a new company --}}
        <div class="modal fade" id="editform" role="dialog" tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {{-- header for add form --}}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Create New Data</h4>
                    </div>

                    {{-- body for add form --}}
                    @include('errors.validation')
                    {{ Form::open(['id' => 'EDIT']) }}
                    <div class="modal-body">
                        <div class="row">
                            {{-- kode company --}}
                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('kode_company_edit', 'Kode Company : ') }}
                                    {{ Form::text('kode_company_edit', null, [
                                        'class' => 'form-control',
                                        'required',
                                        'readonly',
                                    ]) }}
                                </div>
                            </div>

                            {{-- nama company --}}
                            <div class="col-md-10">
                                <div class="form-group">
                                    {{ Form::label('nama_company_edit', 'Nama Company : ') }}
                                    {{ Form::text('nama_company_edit', null, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'Nama Company..',
                                        'autocomplete' => 'off',
                                        'oninput' => 'autoCaps(this)',
                                    ]) }}
                                </div>
                            </div>

                            {{-- alamat --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('alamat_edit', 'Alamat : ') }}
                                    {{ Form::textarea('alamat_edit', null, [
                                        'class' => 'form-control',
                                        'rows' => '4',
                                        'required',
                                        'placeholder' => 'Alamat..',
                                        'autocomplete' => 'off',
                                        'oninput' => 'autoCaps(this)',
                                    ]) }}
                                </div>
                            </div>

                            {{-- telp --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('telp_edit', 'Telp : ') }}
                                    {{ Form::text('telp_edit', null, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'No. Telp..',
                                        'autocomplete' => 'off',
                                        'onkeypress' => 'onlyNumber(event)',
                                    ]) }}
                                </div>
                            </div>

                            {{-- npwp --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('npwp_edit', 'NPWP : ') }}
                                    <input type="text" name="number" style="display:none;">
                                    {{ Form::text('npwp_edit', null, [
                                        'class' => 'form-control',
                                        'autocomplete' => 'off',
                                        'placeholder' => 'NPWP..',
                                    ]) }}
                                </div>
                            </div>

                            {{-- status --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('status_edit', 'Status : ') }}
                                    {{ Form::select(
                                        'status_edit',
                                        [
                                            'Aktif' => 'Aktif',
                                            'NonAktif' => 'Non Aktif',
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
                        </div>
                    </div>
                    <div class="modal-footer">
                        {{ Form::submit('Update Data', [
                            'class' => 'btn btn-success',
                        ]) }}
                        {{ Form::button('Close', [
                            'class' => 'btn btn-danger',
                            'data-dismiss' => 'modal',
                        ]) }}
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

        {{-- side menu for company page --}}
        <div id="mySidenav">

            @permission('update-company')
                {{-- editing the company --}}
                <button class="btn-warning btn sidenav-item" id="editcompany"><i class="fa fa-edit"></i> Edit </button>
            @endpermission

            {{-- deleting the compang --}}
            @permission('delete-company')
                <button class="btn btn-danger sidenav-item" id="hapuscompany"><i class="fa fa-times-circle"></i> Hapus
                </button>
            @endpermission
        </div>
    </body>
@stop

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <style type="text/css">
        #back2top {
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
            color: black;
        }

        #back2top:hover {
            color: #fff;
        }

        #mySidenav button {
            position: fixed;
            right: -20px;
            transition: 0.3s;
            padding: 4px 8px;
            width: 80px;
            text-decoration: none;
            font-size: 12px;
            color: white;
            border-radius: 5px 0 0 5px;
            opacity: 0.8;
            cursor: pointer;
            text-align: left;
            top: 400px;
        }

        #mySidenav button:hover {
            right: 0px;
        }

        .sidenav-item {
            margin-top: 30px;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <script type="text/javascript">
        // onload function when the page is loaded
        function load() {
            startTime();
            $('.form-group2').hide();
            $('.sidenav-item').hide()

            // sidenav-item margin-top
            let marginTop = 0;
            $('button.sidenav-item').each(function(i, obj) {
                $(obj).css("margin-top", marginTop + "px");
                marginTop += 30;
            });
        }

        // get company lists from database using datatables with ajax
        $(function() {
            $('#data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('company1.getcompanies') }}',
                columns: [{
                        data: 'kode_company',
                        name: 'kode_company'
                    },
                    {
                        data: 'nama_company',
                        name: 'nama_company',
                        "fnCreatedCell": function(nTd, sData, oData, iRow, iCol) {
                            $(nTd).html("<a href='{{ route('company1.index') }}/detail/" + oData
                                .kode_company + "'>" + oData
                                .nama_company + "</a>");
                        }
                    },
                    {
                        data: 'alamat',
                        name: 'alamat'
                    },
                    {
                        data: 'telp',
                        name: 'telp'
                    },
                    {
                        data: 'npwp',
                        name: 'npwp'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                ]
            })
        })

        // reload the datatables
        function refreshTable() {
            $('#data-table').DataTable().ajax.reload(null, false)
        }

        // scroll top up using #back2top
        $(document).ready(function() {
            $('#back2top').click(function() {
                $('html, body').animate({
                    scrollTop: 0
                }, "slow");
                return false;
            })

            // toggle to pop up edit and delete buttons
            const table = $('#data-table').DataTable();
            $('#data-table tbody').on('dblclick', 'tr', function() {
                if ($(this).hasClass('selected bg-gray text-bold')) {
                    $(this).removeClass('selected bg-gray text-bold');
                    $('.sidenav-item').hide()
                } else {
                    table.$('tr.selected').removeClass('selected bg-gray text-bold');
                    $(this).addClass('selected bg-gray text-bold');
                    $('.sidenav-item').show()
                }
            })

            // edit button
            $('#editcompany').click(function() {
                const select = $('.selected').closest('tr');
                const data = table.row(select).data()['kode_company'];
                $.ajax({
                    url: '{{ route('company1.index') }}/' + data + '/edit',
                    type: 'GET',
                    success: function(data) {
                        $('#kode_company_edit').val(data.kode_company)
                        $('#nama_company_edit').val(data.nama_company);
                        $('#alamat_edit').val(data.alamat);
                        $('#telp_edit').val(data.telp);
                        $('#npwp_edit').val(data.npwp);
                        $('#status_edit').val(data.status).trigger('change');
                        $('#editform').modal('show');
                    }
                })
            })

            // hapus button
            $('#hapuscompany').click(function() {
                const select = $('.selected').closest('tr');
                const kode_company = table.row(select).data()['kode_company'];
                swal({
                    title: 'Hapus ?',
                    text: 'Yakin akan menghapus data dengan kode company ' + kode_company,
                    type: 'warning',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    showCancelButton: true,
                    reverseButtons: true
                }).then(function(e) {
                    if (e.value == true) {
                        $('.sidenav-item').hide();
                        $.ajax({
                            url: '{{ route('company1.index') }}/' + kode_company,
                            type: "DELETE",
                            success: function(data) {
                                refreshTable();
                                if (data.success == true) {
                                    swal('Berhasil', data.message, 'success');
                                } else {
                                    swal('Gagal', data.message, 'error');
                                }
                            }
                        })
                    }
                })
            })
        })

        // to upper case all input
        function autoCaps(e) {
            e.value = e.value.toUpperCase();
        }

        // Telp field with only number
        function onlyNumber(evt) {
            var theEvent = evt || window.event;

            // Handle paste
            if (theEvent.type === 'paste') {
                key = evt.clipboardData.getData('text/plain');
            } else {
                // Handle key press
                var key = theEvent.keyCode || theEvent.which;
                key = String.fromCharCode(key);
            }
            var regex = /[0-9]|\./;
            if (!regex.test(key)) {
                theEvent.returnValue = false;
                if (theEvent.preventDefault) theEvent.preventDefault();
            }
        }


        // select2 from adminlte
        $('.select2').select2({
            placeholder: "Pilih",
            allowClear: true,
        });

        // getting kode_company after the logged in user change tipe field to Pusat
        function tipes() {
            const tipe = $("#tipe").val();
            if (tipe == 'Cabang') {
                $('.form-group2').show();
                document.getElementById("company1").disabled = false;
            } else {
                $('.form-group2').hide();
                document.getElementById("company1").disabled = true;
            }
        }

        // submit add from ajax to the back
        $('#ADD').submit(function(e) {
            e.preventDefault();
            const data = $('#ADD').serialize();
            console.log(data);
            $.ajax({
                url: '{{ route('company1.store') }}',
                type: 'POST',
                data: data,
                success: function(data) {
                    // console.log('From server : '+ JSON.stringify(data));
                    $('#nama_company').val('');
                    $('#alamat').val('');
                    $('#telp').val('');
                    $('#npwp').val('');
                    $('#status').val('').trigger('change');
                    $('#tipe').val('').trigger('change');
                    $('#kode_comp').val('').trigger('change');
                    $('#addform').modal('hide');
                    $('.sidenav-item').hide();
                    refreshTable();
                    $('#nav')
                    if (data.success == true) {
                        swal("Berhasil", data.message, "success")
                    } else {
                        swal("Gagal", data.message, "error")
                    }
                }
            })
        })

        // ajac setup for editing form
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // submit edit form from ajax to the server
        $('#EDIT').submit(function(e) {
            e.preventDefault();
            const data = $('#EDIT').serialize();
            const kode_company = $('#kode_company_edit').val();
            $.ajax({
                url: '{{ route('company1.index') }}/' + kode_company,
                type: 'PUT',
                data: data,
                success: function(data) {
                    $('#editform').modal('hide');
                    refreshTable();
                    $('.sidenav-item').hide();
                    if (data.success == true) {
                        swal("Berhasil", data.message, "success");
                    } else {
                        swal("Gagal", data.message, "error");
                    }
                }
            })
        })

        // creating value for input field
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();

            $("input[name='npwp']").on("keyup change", function() {
                $("input[name='number']").val(destroyMask(this.value));
                this.value = createMask($("input[name='number']").val());
            })

            function createMask(string) {
                return string.replace(/(\d{2})(\d{3})(\d{3})(\d{1})(\d{3})(\d{3})/, "$1.$2.$3.$4-$5.$6");
            }

            function destroyMask(string) {
                return string.replace(/\D/g, '').substring(0, 15);
            }
        });

        // hide sidenav-item when new company button click
        $("#newcompany").click(function() {
            $('.sidenav-item').hide();
            const select = $('.selected').closest('tr');
            if(select){
                select.removeClass('selected bg-gray text-bold')
            }
        })
    </script>
@endpush
