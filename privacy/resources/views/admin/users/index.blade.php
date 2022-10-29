@extends('adminlte::page')

@section('title', 'Users Data')

@section('content_header')
    
@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <link rel="icon" type="image/png" href="/gui_inventory_laravel/css/logo_gui.png" sizes="16x16" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
@include('sweet::alert')
<body onLoad="load()">
    <div class="box box-solid">
        <div class="box-body">
            <div class="box">
                <div class="box-body">
                    @permission('create-users')
                    <a href="{{ $create_url }}" class="btn btn-info btn-xs"><i class="fa fa-plus"></i> New user</a>
                    @endpermission
                    <span class="pull-right">
                        @permission('read-roles')
                        <a href="{{ route('roles.index') }}" class="btn btn-warning btn-xs">Manages Role</a>
                        @endpermission
                        @permission('read-permissions')
                        <a href="{{ route('permissions.index') }}" class="btn btn-primary btn-xs" onclick="refreshTable()">Manages Permission</a>
                        @endpermission
                    </span>
                </div>
            </div>
            <div class="table-responsive">
            {!! $dataTable->table(['class' => 'table table-bordered table-hover', 'style' => "font-size: 14px;"], true) !!}
            </div>
        </div>
    </div>

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
        </style>
</body>
@stop

@push('css')
@endpush
@push('js')
    {!! $dataTable->scripts() !!}

    <script>
        $(window).scroll(function() {
            var height = $(window).scrollTop();
            if (height > 1) {
                $('#back2Top').show();
            } else {
                $('#back2Top').show();
            }
        });
        
        $(document).ready(function() {
            $("#back2Top").click(function(event) {
                event.preventDefault();
                $("html, body").animate({ scrollTop: 0 }, "slow");
                return false;
            });

        });

        function load(){
            startTime();
            $('.back2Top').show();
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        function refreshTable() {
             $('#dataTableBuilder').DataTable().ajax.reload(null,false);;
        }

        function del(id, url) {
            swal({
            title: "Hapus?",
            text: "Pastikan dahulu user yang akan di hapus",
            type: "warning",
            showCancelButton: !0,
            confirmButtonText: "Ya, Hapus!",
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

                $.ajax({
                    type: 'DELETE',
                    url: url,
                    
                    success: function (results) {
                    console.log(results);
                        if (results.success === true) {
                            swal("Berhasil!", results.message, "success");
                        } else {
                            swal("User gagal di hapus.");
                        }
                          
                        refreshTable();
                    }
                });
            }
            });
        }
    </script>
@endpush