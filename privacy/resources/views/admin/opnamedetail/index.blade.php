@extends('adminlte::page')

@section('title', 'Opname Detail')

@section('content_header')
    
@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
    <a href="{{ $list_url }}" class="btn btn-primary btn-xs"><i class="fa fa-arrow-left"></i> Kembali</a>
    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()"><i class="fa fa-refresh"></i> Refresh</button>
    <button type="button" class="btn btn-success btn-xs" onclick="hapus()"><i class="fa fa-times-circle"></i> Delete All</button>
    <button type="button" class="btn btn-warning btn-xs" onclick="selisih()"><i class="fa fa-edit"></i> Hitung Selisih</button>
    <span class="pull-right">
        <font style="font-size: 16px;"> Detail Opname <b>{{$opname->no_opname}}</b></font>
        <a href="http://localhost/gui_inventory_laravel/admin/opnamedetail/exportpdf?no_opname={{$opname->no_opname}}" target="_blank" id="getform"><button type="button" class="btn btn-warning btn-xs print-button" id="button6">GET FORM OPNAME <i class="fa fa-print"></i></button></a>
        <button type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#import"><i class="fa fa-plus"></i> Import Excel</button>
        <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#add"><i class="fa fa-plus"></i> Add Manual</button>
        <button type="button" class="btn btn-primary btn-xs" onclick="create_all()"><i class="fa fa-plus"></i> Add Otomatis</button>
    </span>
@include('sweet::alert')
<body onLoad="load()">   
    <div class="box box-primary">
        <div class="box-body"> 

    <div class="modal fade" id="add" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Create Data</h4>
            </div>
            @include('errors.validation')
            {!! Form::open(['id'=>'ADD']) !!}
                    <div class="modal-body">
                        <div class="row">
                            {{ Form::hidden('Link',request()->getSchemeAndHttpHost(), ['class'=> 'form-control','readonly','id'=>'Link1']) }}
                            <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('No Opname', 'No Opname:') }}
                                        {{ Form::text('no_opname',$opname->no_opname, ['class'=> 'form-control','readonly','id'=>'Opname1']) }}
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="form-group">
                                        {{ Form::label('kode_produk', 'Kode Produk:') }}
                                        {{ Form::select('kode_produk',$Produk, null,
                                         ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Produk1','onchange'=>'stock();']) }}
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('partnumber', 'Part Number:') }}
                                        {{ Form::select('partnumber', [], null, ['class'=> 'form-control select2','style'=>'width: 100%','placeholder' => '','id'=>'Part1','onchange'=>'getharga();']) }}
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('no_mesin', 'No Mesin:') }}
                                        {{ Form::text('no_mesin', null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => 'No Mesin','id'=>'Mesin1','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('kode_satuan', 'Kode Satuan:') }}
                                        {{ Form::text('kode_satuan', null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => 'Satuan','id'=>'Satuan1','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('hpp', 'Hpp:') }}
                                        {{ Form::text('hpp', null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => 'Harga','id'=>'HPP1','autocomplete'=>'off','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('stok', 'Stock:') }}
                                        {{ Form::text('stok', null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => 'Stock','id'=>'Stock1','readonly']) }}
                                    </div>
                                </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            {{ Form::submit('Create data', ['class' => 'btn btn-success crud-submit']) }}
                            {{ Form::button('Close', ['class' => 'btn btn-danger','data-dismiss'=>'modal']) }}&nbsp;
                        </div>
                    </div>
                {!! Form::close() !!}
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    

    <div class="modal fade" id="import" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Import Excel</h4>
            </div>
            @include('errors.validation')
            {!! Form::open(['id'=>'IMPORT']) !!}
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    @csrf
                                    <input type="file" name="file" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            {{ Form::submit('Import', ['class' => 'btn btn-success crud-submit']) }}
                            {{ Form::button('Close', ['class' => 'btn btn-danger','data-dismiss'=>'modal']) }}&nbsp;
                        </div>
                    </div>
                {!! Form::close() !!}
          </div>
        </div>
    </div>

            <div class="addform">
                    @include('errors.validation')
                    {!! Form::open(['id'=>'EDIT']) !!}
                    <center><kbd>EDIT FORM</kbd></center><br>
                        <div class="row">   
                                {{ Form::hidden('id',null, ['class'=> 'form-control','readonly','id'=>'id_opname']) }}

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('No Opname', 'No Opname:') }}
                                        {{ Form::text('no_opname',$opname->no_opname, ['class'=> 'form-control','readonly','id'=>'Opname']) }}
                                    </div>
                                </div>
                                        
                                {{ Form::hidden('kode_produk',null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => 'Produk', 'id'=>'Produk','readonly']) }}

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('nama_produk', 'Nama Produk:') }}
                                        {{ Form::text('nama_produk',null,
                                         ['class'=> 'form-control','style'=>'width: 100%','placeholder' => 'Nama Produk',
                                         'id'=>'Namaproduk','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('partnumber', 'Part Number:') }}
                                        {{ Form::text('partnumber',null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => 'Part Number','id'=>'Part','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('no_mesin', 'No Mesin:') }}
                                        {{ Form::text('no_mesin', null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => 'No Mesin','id'=>'Mesin','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('kode_satuan', 'Kode Satuan:') }}
                                        {{ Form::text('kode_satuan', null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => 'Satuan','readonly','id'=>'Satuan']) }}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('hpp', 'Hpp:') }}
                                        {{ Form::text('hpp', null, ['class'=> 'form-control','style'=>'width: 100%','placeholder' => 'HPP','id'=>'Harga','autocomplete'=>'off','readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-1">
                                    <div class="form-group">
                                        {{ Form::label('stok', 'Stock:') }}
                                        {{ Form::text('stok', null, ['class'=> 'form-control','style'=>'width: 100%','readonly','placeholder' => 'Stock','id'=>'Stock']) }}
                                    </div>
                                </div>
                    
                                <div class="col-md-1">
                                    <div class="form-group">
                                        {{ Form::label('qty_checker1', 'Q.C 1:') }}
                                        {{ Form::text('qty_checker1', null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'QTY1','autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                <div class="col-md-1">
                                    <div class="form-group">
                                        {{ Form::label('qty_checker2', 'Q.C 2:') }}
                                        {{ Form::text('qty_checker2', null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'QTY2','autocomplete'=>'off']) }}
                                    </div>
                                </div>

                                <div class="col-md-1">
                                    <div class="form-group">
                                        {{ Form::label('qty_checker3', 'Q.C 3:') }}
                                        {{ Form::text('qty_checker3', null, ['class'=> 'form-control','style'=>'width: 100%','id'=>'QTY3','autocomplete'=>'off']) }}
                                    </div>
                                </div>
                            </div> 
                                <span class="pull-right">
                                        {{ Form::submit('Update', ['class' => 'btn btn-success btn-sm']) }}
                                </span>
                    {!! Form::close() !!}
            </div>
    </div>
</div>
        
    <div class="box box-primary">
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data2-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-info">
                        <th>No Opname</th>
                        <th>Kode Produk</th>
                        <th>Part Number</th>
                        <th>No Mesin</th>
                        <th>Kode Satuan</th>
                        <th>Hpp</th>
                        <th>Stock</th>
                        <th>Checker 1</th>
                        <th>Checker 2</th>
                        <th>Checker Final</th>
                        <th>Selisih Stock</th>
                        <th>Selisih Nilai</th>
                        <th>Action</th>
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
        </style>
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

        });

        function load(){
            startTime();
            $('.tombol1').hide();
            $('.tombol2').hide();
            $('.back2Top').show();
        }

        $(function(){
            var no_opname = $('#Opname').val();
            var link = $('#Link1').val();
            console.log(no_opname);
            $('#data2-table').DataTable({
                
            processing: true,
            serverSide: true,
            ajax:link+'/gui_inventory_laravel/admin/opnamedetail/getDatabyID?id='+no_opname,
            data:{'no_opname':no_opname},
            columns: [
                { data: 'no_opname', name: 'no_opname' },
                { data: 'produk.nama_produk', name: 'produk.nama_produk' },
                { data: 'partnumber', name: 'partnumber' },
                { data: 'no_mesin', name: 'no_mesin' },
                { data: 'satuan.nama_satuan', name: 'satuan.nama_satuan', "defaultContent": "<i>Not set</i>", searchable : false },
                { data: 'hpp', 
                    render: function( data, type, full ) {
                    return formatNumber(data); }
                },
                { data: 'stok', name: 'stok' },
                { data: 'qty_checker1', name: 'qty_checker1' },
                { data: 'qty_checker2', name: 'qty_checker2' },
                { data: 'qty_checker3', name: 'qty_checker3' },
                { data: 'stock_opname', name: 'stock_opname' },
                { data: 'amount_opname', 
                    render: function( data, type, full ) {
                    return formatNumber2(data); }
                },
                { data: 'action', name: 'action' }
            ]
            
            });
        });

        function formatNumber(n) {
            if(n == 0 || n == null){
                return 0;
            }else{
                return n.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
        }

        function formatNumber2(m) {
            if(m == null){
                return '';
            }else{
                return m.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
        }
  </script>
  <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function refreshTable() {
            $('#data2-table').DataTable().ajax.reload(null,false);
            $('#Produk').val('');
            $('#Part').val('');
            $('#Mesin').val('');
            $('#Merek').val('');
            $('#Ukuran').val('');
            $('#Harga').val('');
            $('#Stock').val('');
            $('#QTY1').val('');
            $('#QTY2').val('');
            $('#QTY3').val('');
        }

        $('#ADD').submit(function (e) {
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false
            })
            e.preventDefault();
           
            var registerForm = $("#ADD");
            var formData = registerForm.serialize();

            $.ajax({
                    url:'{!! route('opnamedetail.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        console.log(data);
                        $('#Produk1').val('').trigger('change');
                        $('#Part1').val('');
                        $('#Mesin1').val('');
                        $('#Satuan1').val('');
                        $('#HPP1').val('');
                        $('#Stock1').val('');
                        $('#add').modal('hide');
                        refreshTable();
                        if (data.success === true) {
                            swal("Berhasil!", data.message, "success");
                            
                        } else {
                            swal("Gagal!", data.message, "error");
                        }
                    },
                });
            
        });

        function satuan_produk(){
            var kode_produk= $('#Produk1').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('opnamedetail.satuanproduk') !!}',
                type:'POST',
                data : {
                        'id': kode_produk,
                    },
                success: function(result) {
                        console.log(result);
                        $('#Stock1').val(result.stok);
                        $('#Part1').val(result.partnumber);
                        $('#Mesin1').val(result.no_mesin);
                        $('#Satuan1').val(result.satuan);
                        $('#HPP1').val(result.hpp);
                        if(result.tipe_produk == 'Serial' && result.kode_kategori == 'UNIT'){
                            document.getElementById('Part1').readOnly = false;
                            document.getElementById('Mesin1').readOnly = false;
                        }else{
                            document.getElementById('Part1').readOnly = true;
                            document.getElementById('Mesin1').readOnly = true;
                        }
                    },
            });
        }


        $(document).ready(function() {
            var table = $('#data2-table').DataTable();

            $('#data2-table tbody').on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected bg-gray') ) {
                    $(this).removeClass('selected bg-gray');
                            
                    $('#Produk').val('');
                    $('#Namaproduk').val('');
                    $('#Part').val('');
                    $('#Mesin').val('');
                    $('#Merek').val('');
                    $('#Ukuran').val('');
                    $('#Satuan').val('');
                    $('#Harga').val('');
                    $('#Stock').val('');
                    $('#QTY1').val('');
                    $('#QTY2').val('');
                    $('#QTY3').val('');
                }
                else {
                    table.$('tr.selected').removeClass('selected bg-gray');
                    $(this).addClass('selected bg-gray');
                    var select = $('.selected').closest('tr');
                    var data = $('#data2-table').DataTable().row(select).data();
                    var kode_produk = data['kode_produk'];
                    var kode_satuan = data['kode_satuan'];

                    var no_opname = select.find('td:eq(0)').text();
                    var namaproduk = select.find('td:eq(1)').text();
                    var partnumber = select.find('td:eq(2)').text();
                    var mesin = select.find('td:eq(3)').text();
                    var hpp = select.find('td:eq(5)').text();
                    var stok = select.find('td:eq(6)').text();
                    var qty_checker1 = select.find('td:eq(7)').text();
                    var qty_checker2 = select.find('td:eq(8)').text();
                    var qty_checker3 = select.find('td:eq(9)').text();
                    // console.log(no_opname);
                        $.ajax({
                        type: 'POST',
                        url: '{!! route('opnamedetail.getdata') !!}',
                        data: {
                            'no_opname':no_opname,
                            'kode_produk':kode_produk,
                            'partnumber':partnumber,
                        },
                        // dataType: 'JSON',
                        success: function (results) {
                            console.log(results);
                            $('#Produk').val(results.kode_produk);
                            $('#Namaproduk').val(namaproduk);
                            $('#Part').val(results.partnumber);
                            $('#Mesin').val(results.no_mesin);
                            $('#Satuan').val(results.kode_satuan);
                            $('#Harga').val(results.hpp);
                            $('#Stock').val(results.stok);
                            $('#QTY1').val(results.qty_checker1);
                            $('#QTY2').val(results.qty_checker2);
                            $('#QTY3').val(results.qty_checker3);
                            },
                            error : function() {
                            alert("Nothing Data");
                        }
                    });
                }
            } );
        } );


        function selisih(){
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false
            })
            var no_opname= $('#Opname').val();

            $.ajax({
                url:'{!! route('opnamedetail.hitungselisih') !!}',
                type:'POST',
                data : {
                        'id': no_opname
                    },
                success: function(result) {
                        console.log(result);

                        if (result.success === true) {
                            swal("Berhasil!", result.message, "success");
                        } else {
                            swal("Gagal!", result.message, "error");
                        }
                        // window.location.reload();
                        refreshTable();
                    },
            });
        }

        function stock(){
            editpart();
            var kode_produk= $('#Produk1').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('opnamedetail.stockproduk') !!}',
                type:'POST',
                data : {
                        'id': kode_produk
                    },
                success: function(result) {
                        console.log(result);
                        $('#HPP1').val(result.hpp);
                        $('#Stock1').val(result.stok);
                        $('#Satuan1').val(result.kode_satuan);
                        $('#Mesin1').val(result.no_mesin);
                    },
            });
        }

        function editpart(){
                    $('#Part1').prop("disabled", false);
                    var kode_produk = $("#Produk1").val();
                    console.log(kode_produk);
                    
                    var token = $("input[name='_token']").val();
                    $.ajax({
                    url: "{!! route('opnamedetail.selectpart') !!}",
                    method: 'POST',
                    data: {kode_produk:kode_produk, _token:token},
                    success: function(data) {
                        $("#Part1").html('');
                            $.each(data.options, function(key, value){

                                $('#Part1').append('<option value="'+ key +'">' + value + '</option>');

                            });
                        }
                    });
        }

        function getharga(){
            var partnumber= $('#Part1').val();
            var kode_produk= $('#Produk1').val();
            var submit = document.getElementById("submit");
            $.ajax({
                url:'{!! route('opnamedetail.getharga') !!}',
                type:'POST',
                data : {
                        'id': kode_produk,
                        'part': partnumber,
                    },
                success: function(result) {
                        console.log(result);
                        $('#HPP1').val(result.hpp);
                        $('#Stock1').val(result.stok);
                        $('#Mesin1').val(result.no_mesin);
                    },
            });
        }


        function create_all() {
            swal({
                    title: "<b>Proses Sedang Berlangsung</b>",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: false
            })
            var no_opname= $('#Opname').val();

            $.ajax({
                url:'{!! route('opnamedetail.createall') !!}',
                type:'POST',
                data : {
                        'id': no_opname
                    },
                success: function(result) {
                        console.log(result);

                        if (result.success === true) {
                            swal("Berhasil!", result.message, "success");
                        } else {
                            swal("Gagal!", result.message, "error");
                        }
                        // window.location.reload();
                        refreshTable();
                    },
            });
        }

        function hapus() {
            var no_opname= $('#Opname').val();

            swal({
            title: "Hapus?",
            text: "Pastikan dahulu item yang akan di hapus",
            type: "warning",
            showCancelButton: !0,
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal",
            reverseButtons: !0
        }).then(function (e) {
            if (e.value === true) {
                $.ajax({
                    url:'{!! route('opnamedetail.hapusdetail') !!}',
                    type:'POST',
                    data : {
                            'id': no_opname
                        },
                    success: function(result) {
                            console.log(result);

                            if (result.success === true) {
                                swal("Berhasil!", result.message, "success");
                            } else {
                                swal("Gagal!", result.message, "error");
                            }
                            // window.location.reload();
                            refreshTable();
                         }
                });
            }
            });
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

        

        $('#EDIT').submit(function (e) {
            e.preventDefault();
            var registerForm = $("#EDIT");
            var formData = registerForm.serialize();

                $.ajax({
                    url:'{!! route('opnamedetail.updateajax') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data){
                        console.log(data);
                        $('#Produk').val('');
                        $('#Part').val('');
                        $('#Mesin').val('');
                        $('#Merek').val('');
                        $('#Ukuran').val('');
                        $('#Harga').val('');
                        $('#Stock').val('');
                        $('#QTY1').val('');
                        $('#QTY2').val('');
                        $('#QTY3').val('');
                        if (data.success === true) {
                            swal("Berhasil!", data.message, "success");
                        } else {
                            swal("Gagal!", data.message, "error");
                        }
                        refreshTable();

                    },
                });
        });

        function edit(id, url) {
                $.ajax({
                    type: 'GET',
                    url: url,
                    data: {_token: CSRF_TOKEN},
                    dataType: 'JSON',
                    success: function (results) {
                        $('#Opname').val(results.no_opname);
                        $('#Produk').val(results.kode_produk);
                        $('#Satuan').val(results.kode_satuan);
                        $('#QTY').val(results.qty);
                        $('#Harga').val(results.harga);
                        $('#ID').val(results.id);
                        $(".addform").hide();
                        $(".editform").show();
                        },
                        error : function() {
                        alert("Nothing Data");
                    }
                });
            } 


        function del(id, url) {
            var no_opname= $('#Opname').val();
            var kode_produk= $('#Produk').val();
            var partnumber= $('#Part').val();

            swal({
            title: "Hapus?",
            text: "Pastikan dahulu item yang akan di hapus",
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
                    url:'{!! route('opnamedetail.hapusitem') !!}',
                    type:'POST',
                    data : {
                            'id': no_opname,
                            'kode': kode_produk,
                            'partnumber': partnumber
                        },
                    success: function(result) {
                            console.log(result);

                            if (result.success === true) {
                                swal("Berhasil!", result.message, "success");
                            } else {
                                swal("Gagal!", result.message, "error");
                            }
                            // window.location.reload();
                            refreshTable();
                         }
                });
            }
            });
        }
    </script>
@endpush
