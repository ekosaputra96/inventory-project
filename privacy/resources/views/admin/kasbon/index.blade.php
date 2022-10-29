@extends('adminlte::page')

@section('title', 'Permintaan Kasbon')

@section('content_header')
@stop

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.all.min.js"></script>
@include('sweet::alert')
<body onLoad="load()">
    <div class="box box-solid">
        <div class="box-body">
            <div class="box">
                <div class="box-body">
                    <button type="button" class="btn btn-default btn-xs" onclick="refreshTable()"><i class="fa fa-refresh"></i> Refresh</button>

                    <button type="button" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#addform">
                        <i class="fa fa-plus"></i> New Permintaan Kasbon</button>

                    <span class="pull-right">  
                        <font style="font-size: 16px;"><b>PERMINTAAN KASBON</b></font>
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" style="font-size: 12px;">
                    <thead>
                    <tr class="bg-blue">
                        <th>No PKB</th>
                        <th>Nama Pemohon</th>
                        <th>Tanggal Permintaan</th>
                        <th>Nilai</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="addform" role="dialog">
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('Tanggal Permintaan', 'Tanggal PKB:') }}
                                    {{ Form::date('tanggal_permintaan', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'tglpkb1','required'=>'required'])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('pemohon', 'Nama Pemohon:') }}
                                    {{ Form::text('nama_pemohon', null, ['class'=>'form-control','id'=>'Nama1','style'=>'width: 100%','placeholder'=>'','onkeypress'=>"return pulsar(event,this)"]) }}
                                </div>
                            </div>
                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('no_journal', 'No. Journal:') }}
                                    {{ Form::text('no_journal', null, ['class'=> 'form-control','style'=>'width: 100%' ,'id'=>'nojurnal','required','readonly']) }}
                                </div>
                            </div> -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('nilaii', 'Jumlah Nominal:') }}
                                    {{ Form::text('nilai', null, ['class'=>'form-control','id'=>'Nilai1','style'=>'width: 100%','placeholder' => '','onkeypress'=>"return hanyaAngka(event)"]) }}
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    {{ Form::label('ket', 'Keterangan:') }}
                                    {{ Form::text('keterangan',null, ['class'=> 'form-control','style'=>'width: 100%' ,'id'=>'Ket1','required','autocomplete'=>'off','onkeypress'=>"return pulsar(event,this)"]) }}
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


    <div class="modal fade" id="editform" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Edit Data</h4>
            </div>
            @include('errors.validation')
            {!! Form::open(['id'=>'EDIT']) !!}
                <div class="modal-body">
                    <div class="row">
                        {{ Form::hidden('no_pkb',null, ['class'=> 'form-control','readonly','id'=>'nopkb']) }}
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('Tanggal', 'Tanggal PKB:') }}
                                {{ Form::date('tanggal_permintaan', \Carbon\Carbon::now(),['class'=> 'form-control','id'=>'tglpkb2','required'=>'required'])}}
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                {{ Form::label('nama', 'Nama Pemohon:') }}
                                {{ Form::text('nama_pemohon', null, ['class'=>'form-control','id'=>'Nama2','style'=>'width: 100%','placeholder'=>'']) }}
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                {{ Form::label('nilaii2', 'Jumlah Nominal:') }}
                                {{ Form::text('nilai', null, ['class'=> 'form-control','style'=>'width: 100%' ,'id'=>'Nilai2','required','autocomplete'=>'off','onkeypress'=>"return hanyaAngka(event)"]) }}
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                {{ Form::label('ket', 'Keterangan:') }}
                                {{ Form::text('keterangan',null, ['class'=> 'form-control','style'=>'width: 100%' ,'id'=>'Ket2','required','autocomplete'=>'off']) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        {{ Form::submit('Update data', ['class' => 'btn btn-success']) }}
                        {{ Form::button('Close', ['class' => 'btn btn-danger','data-dismiss'=>'modal']) }}&nbsp;
                    </div>
                 </div>
            {!! Form::close() !!}
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <button type="button" class="back2Top btn btn-warning btn-xs" id="back2Top"><i class="fa fa-map-marker" style="color: #fff"></i> <i>{{ $nama_company }}</i> <b>({{ $nama_lokasi }})</b></button>

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

            .view-button {
                background-color: #1674c7;
                bottom: 186px;
            }

            .print-button {
                background-color: #F63F3F;
                bottom: 216px;
            }

            .tombol3 {
                bottom: 246px;
            }

            .view-button2 {
                background-color: #00E0FF;
                bottom: 246px;
            }

            #mySidenav button {
              position: fixed;
              right: -30px;
              transition: 0.3s;
              padding: 4px 8px;
              width: 70px;
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

            #about {
              top: 70px;
              background-color: #4CAF50;
            }

            #blog {
              top: 130px;
              background-color: #2196F3;
            }

            #projects {
              top: 190px;
              background-color: #f44336;
            }

            #contact {
              top: 250px;
              background-color: #555
            }
        </style>
        <div id="mySidenav" class="sidenav">
            
            <button type="button" class="btn btn-warning btn-xs edit-button" id="editpkb" data-toggle="modal" data-target="">EDIT <i class="fa fa-edit"></i></button>

            <button type="button" class="btn btn-danger btn-xs hapus-button" id="hapuspkb" data-toggle="modal" data-target="">HAPUS <i class="fa fa-times-circle"></i></button>

            <button type="button" class="btn btn-info btn-xs tombol3" id="button3">APPROVE <i class="fa fa-bullhorn"></i></button>
           
            <button type="button" class="btn btn-success btn-xs tombol1" id="button1">POST <i class="fa fa-bullhorn"></i></button>

            <button type="button" class="btn btn-warning btn-xs tombol2" id="button2">UNPOST <i class="fa fa-undo"></i></button>

            <a href="#" target="_blank" id="printkasbon"><button type="button" class="btn btn-danger btn-xs print-button" id="button6">PRINT <i class="fa fa-print"></i></button></a>
        </div>
</body>
@stop

@push('css')

@endpush
@push('js')
  
    <script type="text/javascript">
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
            header();
            startTime();
            $('.tombol1').hide();
            $('.tombol2').hide();
            $('.tombol3').hide();
            $('.add-button').hide();
            $('.hapus-button').hide();
            $('.edit-button').hide();
            $('.print-button').hide();
            $('.view-button2').hide();
            $('.back2Top').show();
        }

        function header(){
            $.ajax({
                url: '{!! route('kasbon.data') !!}',
                type: 'GET',
                success: function(result) {
                    Table1.clear().draw();
                    Table1.rows.add(result).draw();
                }
            });
        }

        $('#addjurnalform').on('show.bs.modal', function () {
            
        })
        
        Table1 = $("#data-table").DataTable({
            "bPaginate": true,
            "bFilter": true,
            "order": [[0, "desc"]],
            data:[],
            fnRowCallback: function (row, data, iDisplayIndex, iDisplayIndexFull) {
                if (data['status'] == "OPEN") {
                    $('td', row).css('background-color', '#ffdbd3');
                }
            },
            
            columns: [
                { data: 'no_pkb', name: 'no_pkb' },
                { data: 'nama_pemohon', name: 'nama_pemohon' },
                { data: 'tanggal_permintaan', name: 'tanggal_permintaan' },
                { data: 'nilai', 
                    render: function( data, type, full ) {
                    return formatNumber2(data); }
                },
                { data: 'keterangan', name: 'keterangan' },
                { data: 'status', 
                    render: function( data, type, full ) {
                    return formatStatus(data); }
                },
            ]
        });

        function formatRupiah(angka, prefix='Rp'){
           
            var rupiah = angka.toLocaleString(
                undefined, // leave undefined to use the browser's locale,
                // or use a string like 'en-US' to override it.
                { minimumFractionDigits: 0 }
            );
            return rupiah;
           
        }

        function formatNumber2(m) {
            if(m == null){
                return '';
            }else{
                return m.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
        }

        function formatNumber(n) {
            if(n == 0){
                return 0;
            }else{
                return n.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
        }

        function format_type(n) {
            if(n == 'D'){
                var stat = "<span style='color:#0eab25'><b>DEBIT</b></span>";
            }else{
                var stat = "<span style='color:#c91a1a'><b>KREDIT</b></span>";
            }
            return stat;
        }

        function formatStatus(n) {
            if(n != 'POSTED'){
                return n;
            }else{
                var stat = "<span style='color:#0eab25'><b>POSTED</b></span>";
                return n.replace(/POSTED/, stat);
            }
        }

        $(document).ready(function() {
            var table = $('#data-table').DataTable();
            var post = document.getElementById("button1");
            var unpost = document.getElementById("button2");

            $('#data-table tbody').on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected bg-gray text-bold') ) {
                    $(this).removeClass('selected bg-gray text-bold');
                    $('.tombol1').hide();
                    $('.tombol2').hide();
                    $('.tombol3').hide();
                    $('.hapus-button').hide();
                    $('.edit-button').hide();
                    $('.print-button').hide();
                    $('.view-button').hide();
                    $('.view-button2').hide();
                }
                else {
                    table.$('tr.selected').removeClass('selected bg-gray text-bold');
                    $(this).addClass('selected bg-gray text-bold');
                    var select = $('.selected').closest('tr');

                    closeOpenedRows(table, select);

                    var status = select.find('td:eq(5)').text();
                    var no_pkb = select.find('td:eq(0)').text();

                    var print = $("#printkasbon").attr("href","http://localhost/gui_inventory_laravel/admin/kasbon/exportpdf?no_pkb="+no_pkb);
                    if(status == 'POSTED'){
                        $('.tombol1').hide();
                        $('.tombol2').show();
                        $('.tombol3').show();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').show();
                        $('.view-button').show();
                        $('.view-button2').show();
                    }else if(status =='OPEN'){
                        $('.tombol1').show();
                        $('.tombol2').hide();
                        $('.tombol3').hide();
                        $('.add-button').hide();
                        $('.hapus-button').show();
                        $('.edit-button').show();
                        $('.print-button').hide();
                        $('.view-button').show();
                        $('.view-button2').hide();
                    }else if (status == 'APPROVED') {
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.tombol3').hide();
                        $('.add-button').hide();
                        $('.hapus-button').hide();
                        $('.edit-button').hide();
                        $('.print-button').show();
                        $('.view-button').hide();
                        $('.view-button2').hide();
                    }else{
                        $('.tombol1').hide();
                        $('.tombol2').hide();
                        $('.tombol3').hide();
                        $('.add-button').show();
                        $('.hapus-button').show();
                        $('.edit-button').show();
                        $('.print-button').hide();
                        $('.view-button').hide();
                        $('.view-button2').hide();
                    }
                }
            } );

            var openRows = new Array();

            function closeOpenedRows(table, selectedRow) {
                $.each(openRows, function (index, openRow) {
                    // not the selected row!
                    if ($.data(selectedRow) !== $.data(openRow)) {
                        var rowToCollapse = table.row(openRow);
                        rowToCollapse.child.hide();
                        openRow.removeClass('shown');
                        var index = $.inArray(selectedRow, openRows);                        
                        openRows.splice(index, 1);
                    }
                });
            }         

            $('#button1').click( function () {
                var select = $('.selected').closest('tr');
                var colom = select.find('td:eq(0)').text();
                var no_pkb = colom;
                swal({
                    title: "Post?",
                    text: colom,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Posting!",
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
                            
                        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                // alert( table.rows('.selected').data().length +' row(s) selected' );
                        $.ajax({
                            url: '{!! route('kasbon.post') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pkb
                            },
                            success: function(result) {
                                if (result.success === true) {
                                    swal(
                                    'Posted!',
                                    'Your file has been posted.',
                                    'success'
                                    )
                                    refreshTable();
                                }
                                else{
                                  swal({
                                      title: 'Error',
                                      text: result.message,
                                      type: 'error',
                                  })
                                }
                            },
                            error : function () {
                              swal({
                                  title: 'Oops...',
                                  text: 'Gagal',
                                  type: 'error',
                                  timer: '1500'
                              })
                            }
                        });
                    } else {
                        e.dismiss;
                    }

                }, function (dismiss) {
                    return false;
                })
            });

            $('#button2').click( function () {
                var select = $('.selected').closest('tr');
                var colom = select.find('td:eq(0)').text();
                var no_pkb = colom;
                swal({
                    title: "Unpost?",
                    text: colom,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Unpost!",
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
                            
                        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                        $.ajax({
                            url: '{!! route('kasbon.unpost') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pkb
                            },
                            success: function(result) {
                                if (result.success === true) {
                                    swal(
                                    'Unposted!',
                                    'Data berhasil di Unpost.',
                                    'success'
                                    )
                                    refreshTable();
                                }
                                else{
                                  swal({
                                      title: 'Error',
                                      text: result.message,
                                      type: 'error',
                                  })
                                }
                            },
                            error : function () {
                              swal({
                                  title: 'Oops...',
                                  text: data.message,
                                  type: 'error',
                                  timer: '1500'
                              })
                            }
                        });
                    } else {
                        e.dismiss;
                    }

                }, function (dismiss) {
                    return false;
                })
            });

            $('#button3').click( function () {
                var select = $('.selected').closest('tr');
                var colom = select.find('td:eq(0)').text();
                var no_pkb = colom;
                swal({
                    title: "Approve?",
                    text: colom,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Ya, Setujui!",
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
                            
                        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                // alert( table.rows('.selected').data().length +' row(s) selected' );
                        $.ajax({
                            url: '{!! route('kasbon.approve') !!}',
                            type: 'POST',
                            data : {
                                'id': no_pkb
                            },
                            success: function(result) {
                                if (result.success === true) {
                                    swal(
                                    'Approved!',
                                    'Permintaan telah disetujui.',
                                    'success'
                                    )
                                    refreshTable();
                                }
                                else{
                                  swal({
                                      title: 'Error',
                                      text: result.message,
                                      type: 'error',
                                  })
                                }
                            },
                            error : function () {
                              swal({
                                  title: 'Oops...',
                                  text: 'Gagal',
                                  type: 'error',
                                  timer: '1500'
                              })
                            }
                        });
                    } else {
                        e.dismiss;
                    }

                }, function (dismiss) {
                    return false;
                })
            });

            $('#editpkb').click( function () {
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                var select = $('.selected').closest('tr');
                var no_pkb = select.find('td:eq(0)').text();
                var row = table.row( select );
                $.ajax({
                    type: 'POST',
                    url: '{!! route('kasbon.edit_kasbon') !!}',
                    data: {'id': no_pkb },
                    dataType: 'JSON',
                    success: function (results) {
                        $('#nopkb').val(results.no_pkb);
                        $('#tglpkb2').val(results.tanggal_permintaan);
                        $('#Nama2').val(results.nama_pemohon);
                        $('#Nilai2').val(results.nilai);
                        $('#Ket2').val(results.keterangan);
                        $('#editform').modal('show');
                    },
                    error : function() {
                        swal("GAGAL!<br><b>Status POSTED/RECEIVED/CLOSED</b>");
                    }
                });
            });

            $('#hapuspkb').click( function () {
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                var select = $('.selected').closest('tr');
                var no_pkb = select.find('td:eq(0)').text();
                var row = table.row( select );
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
                        type: 'POST',
                        url: '{!! route('kasbon.hapus_kasbon') !!}',
                        data: {'id': no_pkb },
                        dataType: 'JSON',
                        success: function (results) {
                            if (results.success === true) {
                            swal("Berhasil!", results.message, "success");
                            } else {
                            swal("Gagal!", results.message, "error");
                            }
                        // $.notify(result.message, "success");
                        refreshTable();
                        }
                    });
                }
                });
            });
            
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
            if ((("0123456789.-").indexOf(keychar) > -1)) {
                return true;
            } else
            if (decimal || (keychar == ".")) {
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
            header();
            $('.tombol1').hide();
            $('.tombol2').hide();
            $('.add-button').hide();
            $('.hapus-button').hide();
            $('.edit-button').hide();
            $('.print-button').hide();
            $('.view-button2').hide();
        }

        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });

        $('.modal-dialog').resizable({
    
        });

        $('#ADD').submit(function (e) {
            e.preventDefault();
            var registerForm = $("#ADD");
            var formData = registerForm.serialize();
            
                $.ajax({
                    url:'{!! route('kasbon.store') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        $('#addform').modal('show');
                        $('#tglpkb1').val('');
                        $('#Nama1').val('');
                        $('#Nilai1').val('');
                        $('#Ket1').val('');
                        $('#addform').modal('hide');
                        refreshTable();
                        if (data.success === true) {
                            swal("Berhasil!", data.message, "success");
                        } else {
                            swal("Gagal!", data.message, "error");
                        }   
                    },
                });
        });

        $('#EDIT').submit(function (e) {
            e.preventDefault();
            //var nama = $.trim($('#Nama2').val());
            var registerForm = $("#EDIT");
            var formData = registerForm.serialize();
                $.ajax({
                    url:'{!! route('kasbon.ajaxupdate') !!}',
                    type:'POST',
                    data:formData,
                    success:function(data) {
                        $('#editform').modal('hide');
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