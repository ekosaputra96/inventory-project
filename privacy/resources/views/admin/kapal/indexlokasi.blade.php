@extends('adminlte::page')

@section('title', 'Lokasi Kapal')

@section('content_header')
   
@stop

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <a href="{{ $list_url }}" class="btn btn-danger btn-xs"><i class="fa fa-arrow-left"></i> Kembali</a>
    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()"><i class="fa fa-refresh"></i> Refresh</button>
    <span class="pull-right">
        <font style="font-size: 16px;"> Lokasi Kapal <b>{{$cust->nama_kapal}}</b></font>
    </span>
@include('sweet::alert')
<body onLoad="load()">
    <div class="box box-danger">
        <div class="box-body"> 
            <div class="addform">
            @include('errors.validation')
            {!! Form::open(['id'=>'ADD_DETAIL']) !!}
            <center><kbd>ADD FORM</kbd></center><br>
                <div class="row">
                {{ Form::hidden('kode_kapal',$cust->kode_kapal, ['class'=> 'form-control','readonly','id'=>'KodeCust1']) }}
                {{ Form::hidden('id', null, ['class'=> 'form-control','readonly','id'=>'Id1']) }}
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('Nomors', 'Nama Kapal:') }}
                            {{ Form::text('noomor', $cust->nama_kapal, ['class'=> 'form-control','id'=>'Nama1','readonly']) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('Nomors', 'Pilih Lokasi:') }}
                            {{ Form::select('kode_lokasi',$Lokasi->sort(), null,
                            ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','required'=>'required','id'=>'Lokasi1']) }}
                        </div>
                    </div>
                </div> 
                <span class="pull-right"> 
                    {{ Form::submit('TAMBAH', ['class' => 'btn btn-success btn-sm simpan','id'=>'submit']) }}
                    <button type="button" class="btn btn-info btn-sm editbutton" id="editmt" data-toggle="modal" data-target="">
                    <i class="fa fa-edit"></i> EDIT
                    </button>
                    <button type="button" class="btn btn-danger btn-sm hapusbutton" id="hapusmt">
                    <i class="fa fa-times-circle"></i> HAPUS
                    </button>
                </span>
            </div>
        {!! Form::close() !!}
    </div>
</div>
<div class="box box-danger">
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="data2-table" width="100%" style="font-size: 12px;">
                <thead>
                    <tr class="bg-info">
                        <th>id</th>
                        <th>Kode Lokasi</th>
                        <th>Updated Date</th>
                        <th>Updated By</th>
                    </tr>
                </thead>
            </table>
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

    /* Button used to open the contact form - fixed at the bottom of the page */
    .add-button {
        background-color: #00E0FF;
        bottom: 56px;
    }

    .view-button {
        bottom: 116px;
    }

    .post-button {
        bottom: 86px;
    }

    .unpost-button {
        bottom: 86px;
    }

    .hapus-button {
        background-color: #F63F3F;
        bottom: 86px;
    }

    .edit-button {
        background-color: #FDA900;
        bottom: 116px;
    }

    .tombol1 {
        background-color: #149933;
        bottom: 156px;
    }

    .tombol2 {
        background-color: #ff9900;
        bottom: 156px;
    }

    .print-button {
        background-color: #F63F3F;
        bottom: 216px;
    }

    #mySidenav button {
      position: fixed;
      right: -60px;
      transition: 0.3s;
      padding: 4px 8px;
      width: 110px;
      text-decoration: none;
      font-size: 12px;
      color: white;
      border-radius: 5px 0 0 5px ;
      opacity: 0.8;
      cursor: pointer;
      text-align: left;
  }

  #mySidenav button:hover {
      right: 0;
  }
</style>
<div id="mySidenav" class="sidenav">
    <button type="button" class="btn btn-info btn-xs add-button" id="addmtdetail" data-toggle="modal" data-target="#addmtdetailform"><i class="fa fa-plus"></i> ADD DETAIL</button>
    <button type="button" class="btn btn-success btn-xs post-button" id="postbutton">POST <i class="fa fa-bullhorn"></i></button>
    <button type="button" class="btn btn-warning btn-xs unpost-button" id="unpostbutton">UNPOST <i class="fa fa-bullhorn"></i></button>
    <button type="button" class="btn btn-primary btn-xs view-button" id="viewbutton">VIEW <i class="fa fa-eye"></i></button>
</div>
</body>
@stop

@push('css')

@endpush
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

            $("input[name='npwp']").on("keyup change", function(){
            $("input[name='number']").val(destroyMask(this.value));
                this.value = createMask($("input[name='number']").val());
            })

            function createMask(string){
                return string.replace(/(\d{2})(\d{3})(\d{3})(\d{1})(\d{3})(\d{3})/,"$1.$2.$3.$4-$5.$6");
            }

            function destroyMask(string){
                return string.replace(/\D/g,'').substring(0,15);
            }
        });

        function load(){
            startTime();
            $('.editform').hide();
            $('.add-button').hide();
            $('.post-button').hide();
            $('.unpost-button').hide();
            $('.tombol2').hide();
            $('.back2Top').show();
            $('.simpan').show();
            $('.editbutton').hide();
            $('.hapusbutton').hide();
            $('.editbutton2').hide();
            $('.hapusbutton2').hide();
            $('.view-button').hide();
            $('.groupx1').show();
            $('.groupx2').hide();
        }
        
        function formatRupiah(angka, prefix='Rp'){
           
            var rupiah = angka.toLocaleString(
                undefined, // leave undefined to use the browser's locale,
                // or use a string like 'en-US' to override it.
                { minimumFractionDigits: 0 }
            );
            return rupiah;
           
        }
        
    $(function(){
        var kode = $('#KodeCust1').val();
        console.log(kode);
        $('.form-group4').hide();
        $('.form-group2').hide();
        $('#data2-table').DataTable({
            processing: true,
            serverSide: true,
            ajax:'http://localhost/gui_inventory_laravel/admin/kapal/getDatabyID?kode_customer='+kode,
            data:{'kode':kode},
            columns: [
                { data: 'id', name: 'id', visible: false },
                { data: 'kode_lokasi', name: 'kode_lokasi' },
                { data: 'updated_at', name: 'updated_at' },
                { data: 'updated_by', name: 'updated_by' },
            ]
        });
    });

    

    

    function formatStatus(n) {
        console.log(n);
        if(n != 'POSTED'){
            return n;
        }else{
            var stat = "<span style='color:#0eab25'><b>POSTED</b></span>";
            return n.replace(/POSTED/, stat);
        }
    }

    

        function pulsar(e,obj) {            
              tecla = (document.all) ? e.keyCode : e.which;
              //alert(tecla);
              if (tecla!="8" && tecla!="0"){
                obj.value += String.fromCharCode(tecla).toUpperCase();
                return false;
              }else{
                return true;
              }
        }

        function formatNumber(n) {
            if(n == 0){
                return 0;
            }else{
                return n.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
        }
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        function pulsar(e,obj) {
              tecla = (document.all) ? e.keyCode : e.which;
              //alert(tecla);
              if (tecla!="8" && tecla!="0"){
                obj.value += String.fromCharCode(tecla).toUpperCase();
                return false;
              }else{
                return true;
              }
        }

        function hanyaAngka(e, decimal) {
            var key;
            var keychar;
             if (window.event) {
                 key = window.event.keyCode;
             } else
             if (e) {
                 key = e.which;
             } else return true;
          
            keychar = String.fromCharCode(key);
            if ((key==null) || (key==0) || (key==8) ||  (key==9) || (key==13) || (key==27) ) {
                return true;
            } else
            if ((("0123456789").indexOf(keychar) > -1)) {
                return true;
            } else
            if (decimal && (keychar == ".")) {
                return true;
            } else return false;
        }

        $('.select2').select2({
            placeholder: "Pilih",
            allowClear: true,
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function refreshTable() {
             $('#data2-table').DataTable().ajax.reload(null,false);;
        }

        $('#ADD_DETAIL').submit(function (e) {
            e.preventDefault();
            var registerForm = $("#ADD_DETAIL");
            var formData = registerForm.serialize();
            // Check if empty of not
            $.ajax({
                url:'{!! route('kapal.store_lokasi') !!}',
                type:'POST',
                data:formData,
                success:function(data) {
                    console.log(data);
                    $('#Npwp1').val('');
                    $('#Alamat1').val('');
                    refreshTable();
                    if (data.success === true) {
                        swal("Berhasil!", data.message, "success");
                    } else {
                        swal("Gagal!", data.message, "error");
                    }
                },
            });
        });
               
</script>
@endpush