@extends('adminlte::page')

@section('title', 'Manages Permissions')

@section('content_header')
@stop

@section('content')
<body onLoad="load()">
    <div class="box box-solid">
        <div class="box-body">
            <div class="box box-info">
                <div class="box-body">
                    @permission('read-permissions')
                    <a href="{{ $create_url }}" class="btn btn-info btn-xs"><i class="fa fa-plus"></i> New Permission</a>
                    @endpermission
                    <span class="pull-right">
                        @permission('read-users')
                        <a href="{{ route('users.index') }}" class="btn btn-success btn-xs">Manages User</a>
                        @endpermission
                        @permission('read-roles')
                        <a href="{{ route('roles.index') }}" class="btn btn-warning btn-xs">Manages Role</a>
                        @endpermission
                    </span>
                </div>
            </div>
            <table class="table">
                @foreach($permissions->groupBy('tab') as $key => $value)
                    <thead style="font-size: 14px">
                    <tr style="font-size: 14px">
                        <th colspan="3"><i class="fa fa-arrow-right"></i> {{ $key }}</th>
                    </tr>
                    <tr style="font-size: 14px">
                        <th style="padding-left: 30px">Name (<i>System Only</i>)</th>
                        <th style="padding-left: 30px">Display Name (<i>User frendly</i>)</th>
                        <th>Description</th>
                    </tr>
                    </thead>
                    <tbody style="font-size: 14px; color: green">
                    @foreach($value as $row)
                        <tr>
                            <td style="padding-left: 40px">{{ $row->name }}</td>
                            <td style="padding-left: 40px">{{ $row->display_name }}</td>
                            <td>{{ $row->description }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                @endforeach
            </table>
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


@push('js')


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
             $('#dataTableBuilder').DataTable().ajax.reload();;
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