@extends('adminlte::page')

@section('title', $info['title'])

@section('content_header')
@stop

@section('content')
<body onLoad="load()">
    <div class="box box-solid">
        <div class="box-body">
            <div class="box box-info">
                <div class="box-body">
                    <a href="{{ $info['list_url'] }}" class="btn btn-light btn-xs pull-left"> <i class="fa fa-arrow-circle-left"></i> Kembali</a>
                </div>
            </div>
            @include('errors.validation')

            {!! Form::model($permission, ['route' => ['permissions.update', $permission->id],'method' => 'put']) !!}

                <div class="form-group">
                    {{ Form::label('name', 'Name:') }}
                    {{ Form::text('name', null, ['class'=> 'form-control','readonly']) }}
                </div>
                <div class="form-group">
                    {{ Form::label('display_name', 'Display Name:') }}
                    {{ Form::text('display_name', null, ['class'=> 'form-control']) }}
                </div>

                <div class="form-group">
                    {{ Form::label('description', 'Description:') }}
                    {{ Form::text('description', null, ['class'=> 'form-control']) }}
                </div>

                <div class="form-group">
                    {{ Form::submit('Update', ['class' => 'btn btn-success btn-sm']) }}
                </div>

            {!! Form::close() !!}

        </div>

    </div>
    <!-- /.box -->

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
    </script>
@endpush
