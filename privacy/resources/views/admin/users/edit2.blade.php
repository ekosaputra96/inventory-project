@extends('adminlte::page')

@section('title', 'User Edit')

@section('content_header')
@stop

@section('content')
<body onLoad="load()">
    <div class="box">
        <div class="box-header with-border">
            <a href="{{ $list_url }}" class="btn btn-light btn-xs pull-left"> <i class="fa fa-arrow-circle-left"></i> Kembali</a>
        </div>

        <div class="box-body">

            {!! Form::model($user, ['route' => ['users.update', $user->id],'method' => 'put']) !!}

                <div class="form-group">
                    {{ Form::label('name', 'Full Name:') }}
                    {{ Form::text('name', null, ['class'=> 'form-control']) }}
                </div>

                <div class="form-group">
                    {{ Form::label('username', 'Username:') }}
                    {{ Form::text('username', null, ['class'=> 'form-control','readonly']) }}
                </div>

                <!--<div class="form-group">-->
                <!--    {{ Form::label('email', 'Email:') }}-->
                <!--    {{ Form::text('email', null, ['class'=> 'form-control']) }}-->
                <!--</div>-->

                <div class="form-group">
                    {{ Form::label('password', 'Password:') }}
                    {{ Form::password('password', ['class'=> 'form-control']) }}
                </div>

                <div class="form-group">
                    {{ Form::label('password_confirmation', 'Password Confirmation:') }}
                    {{ Form::password('password_confirmation', ['class'=> 'form-control']) }}
                </div>

                <div class="box-footer">
                    <div class="row pull-right">
                        <div class="col-md-12 ">
                            {{ Form::submit('Update', ['class' => 'btn btn-success btn-sm']) }}
                        </div>
                    </div>
                </div>

            {!! Form::close() !!}

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
    </script>
@endpush
