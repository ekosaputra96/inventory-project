@extends('adminlte::page')

@section('title', 'Users Create')

@section('content_header')
@stop

@section('content')
<body onLoad="load()">
   <div class="box">
        <div class="box-header with-border">
            <a href="{{ $list_url }}" class="btn btn-light btn-xs pull-left"> <i class="fa fa-arrow-circle-left"></i> Kembali</a>
        </div>
        <div class="box-body">
            <form class="form-horizontal" role="form" method="POST" action="{{ route('users.store') }}">
                        {{ csrf_field() }}
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label">Full Name</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus autocomplete="off">

                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="name" class="col-md-4 control-label">Username</label>

                                <div class="col-md-6">
                                    <input id="username" type="text" class="form-control" name="username" required autofocus autocomplete="off">
                                </div>
                            </div>

                            <!--<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">-->
                            <!--    <label for="email" class="col-md-4 control-label">Email</label>-->

                            <!--    <div class="col-md-6">-->
                            <!--        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="off">-->

                            <!--        @if ($errors->has('email'))-->
                            <!--            <span class="help-block">-->
                            <!--                <strong>{{ $errors->first('email') }}</strong>-->
                            <!--            </span>-->
                            <!--        @endif-->
                            <!--    </div>-->
                            <!--</div>-->

                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label for="password" class="col-md-4 control-label">Password</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control" name="password" required autocomplete="off">

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password-confirm" class="col-md-4 control-label">Confirm Password</label>

                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="off">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="roles" class="col-md-4 control-label">Roles:</label>
                                <div class="col-md-6">
                                    {{ Form::select('roles[]', $Roles, null, ['class'=> 'form-control select2','required'=>'required','style'=>'width: 100%','placeholder' => '']) }}
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('kode_company') ? ' has-error' : '' }}">
                                <label for="Company" class="col-md-4 control-label">Company</label>
                                <div class="col-md-6">
                                   
                                    {{ Form::select('kode_company', $Company, null, ['class'=> 'form-control select2','required'=>'required','style'=>'width: 100%','placeholder' => '']) }}
                                    @if ($errors->has('kode_company'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('kode_company') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="Lokasi" class="col-md-4 control-label">Lokasi</label>
                                <div class="col-md-6">
                                    {{ Form::select('kode_lokasi', $Lokasi, null, ['class'=> 'form-control select2','required'=>'required','style'=>'width: 100%','placeholder' => '']) }}
                                </div>
                            </div>
        

                            <div class="box-footer">
                                <div class="row pull-right">
                                    <div class="col-md-12 ">
                                        <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-floppy-o"></i> Simpan</button>
                                    </div>
                                </div>
                            </div>
            </form>
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

        $('.select2').select2({
            placeholder: "Pilih",
            allowClear: true,

        });
    </script>
@endpush