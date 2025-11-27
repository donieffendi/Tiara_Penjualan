@extends('layouts.plain')
@section('styles')
    <link rel="stylesheet" href="{{ url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="{{ url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
@endsection

<style>
    .card {}

    .form-control:focus {
        background-color: #E0FFFF !important;
    }
</style>

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Data Daftar Komisi</h1>
                    </div>

                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <form
                                    action="{{ $tipx == 'new' ? url('/komisi/store/') : url('/komisi/update/' . $header->NO_ID) }}"
                                    method="POST" name ="entri" id="entri">
                                    @csrf
                                    <div class="tab-content mt-3">
                                        <div class="tab-pane active">
                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="NO_BUKTI" class="form-label">No.Bukti</label>
                                                </div>

                                                <input type="text" class="form-control NO_ID" id="NO_ID"
                                                    name="NO_ID" placeholder="Masukkan NO_ID"
                                                    value="{{ $header->NO_ID ?? '' }}" hidden readonly>

                                                <input name="tipx" class="form-control flagz" id="tipx"
                                                    value="{{ $tipx }}" hidden>


                                                <div class="col-md-2">
                                                    <input type="text" class="form-control NO_BUKTI" id="NO_BUKTI"
                                                        placeholder="Masukkan kode" value="{{ $header->NO_BUKTI }}" readonly>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="TGL" class="form-label">Tanggal</label>
                                                </div>
                                                <div class="col-md-2">
                                                <input class="form-control date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}">
                                                </div>
                                                <div class="col-md-1">
                                                    <label for="KODE" class="form-label">Kode</label>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text" class="form-control KODE" id="KODE"
                                                        name="KODE" placeholder="Masukkan kode" onblur="browseKode()"
                                                        value="{{ $header->KODE }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control NAMA" id="NAMA"
                                                        name="NAMA" placeholder="Masukkan NAMA"
                                                        value="{{ $header->NAMA }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="TGLM" class="form-label">Tanggal Berlaku</label>
                                                </div>
                                                <div class="col-md-2">
                                                    <input class="form-control date" id="TGLM" name="TGLM" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGLM))}}">
                                                </div>
                                                <div class="col-md-1">
                                                    <label for="TGLS" class="form-label">s/d</label>
                                                </div>
                                                <div class="col-md-2">
                                                    <input class="form-control date" id="TGLS" name="TGLS" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGLS))}}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="NOTES" class="form-label">Notes</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control NOTES" id="NOTES"
                                                        name="NOTES" placeholder="Masukkan nama"
                                                        value="{{ $header->NOTES }}">
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table id="datatable"
                                                    class="table table-striped table-bordered table-hover">
                                                    <thead>
                                                        <tr>

                                                            <th style="text-align: center;">No.</th>
                                                            <th style="text-align: center;">Sub</th>
                                                            <th style="text-align: center;">Nama</th>
                                                            <th style="text-align: center;">Komisi</th>
                                                            <th style="text-align: center;">Margin</th>
                                                            <th style="text-align: center;">-</th>

                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        <?php $no = 0; ?>
                                                        @foreach ($detail as $detail)
                                                            <tr>
                                                                <td>
                                                                    <input type="hidden"
                                                                        name="NO_ID[]{{ $no }}" id="NO_ID"
                                                                        type="text" value="{{ $detail->NO_ID }}"
                                                                        class="form-control NO_ID"
                                                                        onkeypress="return tabE(this,event)" readonly>

                                                                    <input name="REC[]"
                                                                        value="{{ $detail->REC ?? $no + 1 }}"
                                                                        type="text" class="form-control" readonly>
                                                                </td>
                                                                <td>
                                                                    <input name="SUB[]" id="SUB{{ $no }}"
                                                                        type="text" value="{{ $detail->SUB }}"
                                                                        onblur="browseSub()"
                                                                        class="form-control SUB" required>
                                                                </td>
                                                                <td>
                                                                    <input name="KELOMPOK[]"
                                                                        id="KELOMPOK{{ $no }}" type="text"
                                                                        value="{{ $detail->KELOMPOK }}"
                                                                        class="form-control KELOMPOK" required>
                                                                </td>


                                                                <!------------------------------------------------------------------------------------------->

                                                                <td>
                                                                    <input name="KOMISI[]" value="{{ $detail->KOMISI }}"
                                                                        id="KOMISI{{ $no }}" type="text"
                                                                        style="text-align: right"
                                                                        class="form-control KOMISI text-primary">
                                                                </td>
                                                                <td>
                                                                    <input name="MARGIN[]" value="{{ $detail->MARGIN }}"
                                                                        id="MARGIN{{ $no }}" type="text"
                                                                        style="text-align: right"
                                                                        class="form-control MARGIN text-primary">
                                                                </td>
                                                                <td>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-circle btn-outline-danger btn-delete"
                                                                        onclick="">
                                                                        <i class="fa fa-fw fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>

                                                            <?php $no++; ?>
                                                        @endforeach
                                                    </tbody>

                                                </table>
                                            </div>
                                            <div class="col-md-2 row">
                                                <button id="tambah_det" type="button" onclick="tambah()"
                                                    class="btn btn-sm btn-success"><i class="fas fa-plus fa-sm md-3"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mt-3 col-md-12 form-group row">
                                            <div class="col-md-4">
                                                <button type="button" hidden id='TOPX'
                                                    onclick="location.href='{{ url('/komisi/edit/?idx=' . $idx . '&tipx=top') }}'"
                                                    class="btn btn-outline-primary">Top</button>
                                                <button type="button" hidden id='PREVX'
                                                    onclick="location.href='{{ url('/komisi/edit/?idx=' . $header->NO_ID . '&tipx=prev&kodex=' . $header->NO_BUKTI) }}'"
                                                    class="btn btn-outline-primary">Prev</button>
                                                <button type="button" hidden id='NEXTX'
                                                    onclick="location.href='{{ url('/komisi/edit/?idx=' . $header->NO_ID . '&tipx=next&kodex=' . $header->NO_BUKTI) }}'"
                                                    class="btn btn-outline-primary">Next</button>
                                                <button type="button" hidden id='BOTTOMX'
                                                    onclick="location.href='{{ url('/komisi/edit/?idx=' . $idx . '&tipx=bottom') }}'"
                                                    class="btn btn-outline-primary">Bottom</button>
                                            </div>
                                            <div class="col-md-5">
                                                <button type="button" hidden id='NEWX'
                                                    onclick="location.href='{{ url('/komisi/edit/?idx=0&tipx=new') }}'"
                                                    class="btn btn-warning">New</button>
                                                <button type="button" hidden id='EDITX' onclick='hidup()'
                                                    class="btn btn-secondary">Edit</button>
                                                <button type="button" hidden id='UNDOX'
                                                    onclick="location.href='{{ url('/komisi/edit/?idx=' . $idx . '&tipx=undo') }}'"
                                                    class="btn btn-info">Undo</button>
                                                <button type="button" id='SAVEX' onclick='simpan()'
                                                    class="btn btn-success" class="fa fa-save"></i>Save</button>

                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" hidden id='HAPUSX' onclick="hapusTrans()"
                                                    class="btn btn-outline-danger">Hapus</button>
                                                <button type="button" id='CLOSEX'
                                                    onclick="location.href='{{ url('/komisi') }}'"
                                                    class="btn btn-outline-secondary">Close</button>


                                            </div>
                                        </div>


                                </form>
                            </div>
                        </div>
                        <!-- /.card -->
                    </div>
                </div>
                <!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->

        <div class="modal fade" id="browseKodeModal" tabindex="-1" role="dialog" aria-labelledby="browseKodeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="browseKodeModalLabel">Cari Rekanan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-stripped table-bordered" id="table-bkode">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="browseSubModal" tabindex="-1" role="dialog" aria-labelledby="browseSubModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="browseSubModalLabel">Cari Sub</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-stripped table-bordered" id="table-bsub">
                        <thead>
                            <tr>
                                <th>Sub</th>
                                <th>Nama</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
                </div>
            </div>
        </div>

    @endsection

    @section('footer-scripts')
        <script>
            var target;
            var idrow = 1;

            $(document).ready(function() {

                $tipx = $('#tipx').val();

                $('body').on('keydown', 'input, select', function(e) {
                    if (e.key === "Enter") {
                        var self = $(this),
                            form = self.parents('form:eq(0)'),
                            focusable, next;
                        focusable = form.find('input,select,textarea').filter(':visible');
                        next = focusable.eq(focusable.index(this) + 1);
                        console.log(next);
                        if (next.length) {
                            next.focus().select();
                        } else {
                            document.getElementById("KODE").focus();
                        }
                        return false;
                    }
                });

                if ($tipx == 'new') {
                    baru();
                    tambah();
                }

                if ($tipx != 'new') {
                    ganti();
                }

                $('.date').datepicker({
                    dateFormat: 'dd-mm-yy'
                });
                ///////////////////////////////

                // CHOOSE Kode
                var dTableBKode;
                loadDataBKode = function(){

                    $.ajax(
                    {
                        type: 'GET',
                        url: '{{url('komisi/browse_kode')}}',


                        beforeSend: function(){
                            $("#LOADX").show();
                        },


                        success: function( response )
                        {
                            $("#LOADX").hide();

                            resp = response;
                            if(dTableBKode){
                                dTableBKode.clear();
                            }
                            for(i=0; i<resp.length; i++){

                                dTableBKode.row.add([
                                    '<a href="javascript:void(0);" onclick="chooseKode(\''+resp[i].KODE+'\',  \''+resp[i].NAMA+'\')">'+resp[i].KODE+'</a>',
                                    resp[i].NAMA,
                                ]);
                            }
                            dTableBKode.draw();
                        }
                    });
                }

                dTableBKode = $("#table-bkode").DataTable({

                });

                browseKode = function(){
                    loadDataBKode();
                    $("#browseKodeModal").modal("show");
                }

                chooseKode = function(KODE,NAMA){
                    $("#KODE").val(KODE);
                    $("#NAMA").val(NAMA);
                    $("#browseKodeModal").modal("hide");

                }

                $("#KODE").keypress(function(e){

                    if(e.keyCode == 13){
                        e.preventDefault();
                        browseKode();
                    }
                });
                ////////////////////////////////////////

                // Choose Sub
                var dTableBSub;
                var rowidSub;
                loadDataBSub = function(){

                    $.ajax(
                    {
                        type: 'GET',
                        url: "{{url('komisi/browse_sub')}}",
                        async : false,
                        data: {
                                'SUB': $("#SUB"+rowidSub).val()
                        },
                        success: function( response )

                        {
                            resp = response;


                            if ( resp.length > 1 )
                            {
                                    if(dTableBSub){
                                        dTableBSub.clear();
                                    }
                                    for(i=0; i<resp.length; i++){

                                        dTableBSub.row.add([
                                            '<a href="javascript:void(0);" onclick="chooseSub(\''+resp[i].SUB+'\', \''+resp[i].KELOMPOK+'\')">'+resp[i].SUB+'</a>',
                                            resp[i].KELOMPOK,

                                        ]);
                                    }
                                    dTableBSub.draw();

                            }
                            else
                            {
                                $("#SUB"+rowidSub).val(resp[0].SUB);
                                $("#KELOMPOK"+rowidSub).val(resp[0].KELOMPOK);
                            }
                        }
                    });
                }

                dTableBSub = $("#table-bsub").DataTable({

                });

                browseSub = function(rid){
                    rowidSub = rid;
                    $("#KELOMPOK"+rowidSub).val("");
                    loadDataBSub();


                    if ( $("#KELOMPOK"+rowidSub).val() == '' ) {
                            $("#browseSubModal").modal("show");
                    }
                }

                chooseSub = function(SUB,KELOMPOK){
                    $("#SUB"+rowidSub).val(SUB);
                    $("#KELOMPOK"+rowidSub).val(KELOMPOK);
                    $("#browseSubModal").modal("hide");
                }

                $("#SUB").keypress(function(e){

                    if(e.keyCode == 13){
                        e.preventDefault();
                        browseSub();
                    }
                });

            });


            function baru() {

                kosong();
                hidup();

            }

            function ganti() {

                // mati();
                hidup();

            }


            function batal() {

                mati();

            }


            function hidup() {

                $("#TOPX").attr("disabled", true);
                $("#PREVX").attr("disabled", true);
                $("#NEXTX").attr("disabled", true);
                $("#BOTTOMX").attr("disabled", true);

                $("#NEWX").attr("disabled", true);
                $("#EDITX").attr("disabled", false);
                $("#UNDOX").attr("disabled", false);
                $("#SAVEX").attr("disabled", false);

                $("#HAPUSX").attr("disabled", true);
                $("#CLOSEX").attr("disabled", false);

                $("#NO_BUKTI").attr("readonly", true);

                $("#NAMA").attr("readonly", false);
                $("#TGL").attr("readonly", false);
                $("#TGL_SLS").attr("readonly", false);

				jumlahdata = 100;
                for (i = 0; i <= jumlahdata; i++) {
                    $("#REC" + i.toString()).attr("readonly", true);
                    $("#SUB" + i.toString()).attr("readonly", false);
                    $("#KELOMPOK" + i.toString()).attr("readonly", false);
                    $("#MARGIN" + i.toString()).attr("readonly", false);
                    $("#KOMISI" + i.toString()).attr("readonly", false);
                }
            }


            function mati() {

                $("#TOPX").attr("disabled", false);
                $("#PREVX").attr("disabled", false);
                $("#NEXTX").attr("disabled", false);
                $("#BOTTOMX").attr("disabled", false);

                $("#NEWX").attr("disabled", false);
                $("#EDITX").attr("disabled", false);
                $("#UNDOX").attr("disabled", true);
                $("#SAVEX").attr("disabled", true);
                $("#HAPUSX").attr("disabled", false);
                $("#CLOSEX").attr("disabled", false);

                $("#KODE").attr("readonly", true);
                $("#NAMA").attr("readonly", true);
                $("#TGL").attr("readonly", true);
                $("#TGL_SLS").attr("readonly", true);

				jumlahdata = 100;
                for (i = 0; i <= jumlahdata; i++) {
                    $("#REC" + i.toString()).attr("readonly", true);
                    $("#SUB" + i.toString()).attr("readonly", true);
                    $("#KELOMPOK" + i.toString()).attr("readonly", true);
                    $("#MARGIN" + i.toString()).attr("readonly", true);
                    $("#KOMISI" + i.toString()).attr("readonly", true);
                }
            }


            function kosong() {

                $('#NO_BUKTI').val("+");
                $('#NAMA').val("");
            }

            function hapusTrans() {
                let text = "Hapus Master " + $('#NO_BUKTI').val() + "?";
                if (confirm(text) == true) {
                    window.location = "{{ url('/komisi/delete/' . $header->NO_ID) }}'";
                    //return true;
                }
                return false;
            }

            function CariBukti() {

                var cari = $("#CARI").val();
                var loc = "{{ url('/komisi/edit/') }}" + '?idx={{ $header->NO_ID }}&tipx=search&kodex=' + encodeURIComponent(
                    cari);
                window.location = loc;

            }

            function simpan() {
                document.getElementById("entri").submit()
            }


            function tambah() {
                var table = document.getElementById('datatable').getElementsByTagName('tbody')[0];
                var row = table.insertRow();
                var html = `
					<td>
						<input name='NO_ID[]' type='hidden' value='new'>
						<input name='REC[]' id='REC${idrow}' type='text' class='form-control' value='${idrow}' readonly>
					</td>
					<td>
						<input name='SUB[]' onblur='browseSub(${idrow})' id='SUB${idrow}' type='text' class='form-control' required>
					</td>
					<td>
						<input name='KELOMPOK[]' id='KELOMPOK${idrow}' type='text' class='form-control' required>
					</td>
					<td>
						<input name='KOMISI[]' id='KOMISI${idrow}' value='0' type='text' style='text-align:right' class='form-control text-primary' required>
					</td>
					<td>
						<input name='MARGIN[]' id='MARGIN${idrow}' value='0' type='text' style='text-align:right' class='form-control text-primary' required>
					</td>
					<td>
						<button type='button' class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick='hapusRow(this)'>
							<i class='fa fa-fw fa-trash'></i>
						</button>
					</td>
					`;
                row.innerHTML = html;
                idrow++;
            }

            function hapusRow(btn) {
                var row = btn.closest('tr');
                row.parentNode.removeChild(row);
            }
        </script>
    @endsection
