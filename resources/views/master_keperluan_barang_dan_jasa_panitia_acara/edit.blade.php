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
                        <h1 class="m-0">Data Keperluan Barang Dan Jasa Panitia Acara</h1>
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
                                    action="{{ $tipx == 'new' ? url('/brg-jasa-pa/store/') : url('/brg-jasa-pa/update/' . $header->NO_ID) }}"
                                    method="POST" name ="entri" id="entri">
                                    @csrf
                                    <div class="tab-content mt-3">
                                        <div class="tab-pane active">
                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="no_bukti" class="form-label">No. Urut</label>
                                                </div>

                                                <input type="text" class="form-control NO_ID" id="NO_ID"
                                                    name="NO_ID" placeholder="Masukkan NO_ID"
                                                    value="{{ $header->NO_ID ?? '' }}" hidden readonly>

                                                <input name="tipx" class="form-control flagz" id="tipx"
                                                    value="{{ $tipx }}" hidden>


                                                <div class="col-md-2">
                                                    <input type="text" class="form-control no_bukti" id="no_bukti"
                                                        placeholder="Masukkan kode" value="{{ $header->no_bukti }}"
                                                        readonly>
                                                </div>

                                                <div class="col-md-1">
                                                    <label for="kd_dept" class="form-label">Kode Dept</label>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text" class="form-control kd_dept" id="kd_dept" onblur="browseDept()"
                                                        name="kd_dept" placeholder="Masukkan nama"
                                                        value="{{ $header->kd_dept }}" readonly>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="tgl" class="form-label">Tanggal</label>
                                                </div>
                                                <div class="col-md-2">
                                                <input class="form-control date" id="tgl" name="tgl" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->tgl))}}">
                                                </div>
                                                <div class="col-md-1">
                                                    <label for="dept" class="form-label"></label>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text" class="form-control dept" id="dept"
                                                        name="dept" placeholder="Masukkan dept"
                                                        value="{{ $header->dept }}" readonly>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="notes" class="form-label">Notes</label>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text" class="form-control notes" id="notes"
                                                        name="notes" placeholder="Masukkan nama"
                                                        value="{{ $header->notes }}">
                                                </div>
                                                <div class="col-md-1">
                                                    <label for="seksi" class="form-label">Seksi</label>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text" class="form-control seksi" id="seksi"
                                                        name="seksi" placeholder="Masukkan nama"
                                                        value="{{ $header->seksi }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="panitia" class="form-label">Kepanitian</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control panitia" id="panitia"
                                                        name="panitia" placeholder="Masukkan nama"
                                                        value="{{ $header->panitia }}">
                                                </div>
                                                <div class="col-md-1">
                                                    <label for="kepl" class="form-label">Keperluan</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control kepl" id="kepl"
                                                        name="kepl" placeholder="Masukkan nama"
                                                        value="{{ $header->kepl }}">
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table id="datatable"
                                                    class="table table-striped table-bordered table-hover">
                                                    <thead>
                                                        <tr>

                                                            <th style="text-align: center;">No.</th>
                                                            <th style="text-align: center;">Nama Barang</th>
                                                            <th style="text-align: center;">Type/Ukuran</th>
                                                            <th style="text-align: center;">Merk</th>
                                                            <th style="text-align: center;">Kemasan</th>
                                                            <th style="text-align: center;">Qty</th>
                                                            <th style="text-align: center;">Harga</th>
                                                            <th style="text-align: center;">Total</th>
                                                            <th style="text-align: center;">Batas</th>
                                                            <th style="text-align: center;">POS</th>
                                                            <th style="text-align: center;">-</th>

                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        <?php $no = 0; ?>
                                                        {{-- @foreach ($header as $header) --}}
                                                        <tr>
                                                            <td>
                                                                <input type="hidden" name="NO_ID[]{{ $no }}"
                                                                    id="NO_ID" type="text"
                                                                    value="{{ $header->NO_ID }}"
                                                                    class="form-control NO_ID"
                                                                    onkeypress="return tabE(this,event)" readonly>

                                                                <input name="REC[]"
                                                                    value="{{ $header->REC ?? $no + 1 }}" type="text"
                                                                    class="form-control" readonly>
                                                            </td>
                                                            <td>
                                                                <input name="na_brg[]" id="na_brg{{ $no }}"
                                                                    type="text" value="{{ $header->na_brg }}"
                                                                    class="form-control na_brg" readonly required>
                                                            </td>
                                                            <td>
                                                                <input name="ukuran[]" id="ukuran{{ $no }}"
                                                                    type="text" value="{{ $header->ukuran }}"
                                                                    class="form-control ukuran" required readonly>
                                                            </td>


                                                            <!------------------------------------------------------------------------------------------->

                                                            <td>
                                                                <input name="merk[]" value="{{ $header->merk }}"
                                                                    id="merk{{ $no }}" type="text"
                                                                    style="text-align: left"
                                                                    class="form-control merk text-primary" readonly>
                                                            </td>
                                                            <td>
                                                                <input name="SATUAN[]" value="{{ $header->SATUAN }}"
                                                                    id="SATUAN{{ $no }}" type="text"
                                                                    style="text-align: left"
                                                                    class="form-control SATUAN text-primary" readonly>
                                                            </td>
                                                            <td>
                                                                <input name="qty[]" value="{{ $header->qty }}"
                                                                    id="qty{{ $no }}" type="text"
                                                                    style="text-align: right"
                                                                    class="form-control qty text-primary" readonly>
                                                            </td>
                                                            <td>
                                                                <input name="harga[]" value="{{ $header->harga }}"
                                                                    id="harga{{ $no }}" type="text"
                                                                    style="text-align: right"
                                                                    class="form-control harga text-primary" readonly>
                                                            </td>
                                                            <td>
                                                                <input name="total[]" value="{{ $header->total }}"
                                                                    id="total{{ $no }}" type="text"
                                                                    style="text-align: right"
                                                                    class="form-control total text-primary" readonly>
                                                            </td>
                                                            {{-- <td>
                                                                <input name="batas1[]" value="{{ $header->batas1 }}"
                                                                    id="batas1{{ $no }}" type="text"
                                                                    style="text-align: right"
                                                                    class="form-control batas1 text-primary" readonly>
                                                            </td> --}}
                                                            <td>
                                                                <input name="batas1[]" id="batas1{{ $no }}" type="text"
                                                                    class="form-control date text-primary" data-date-format="dd-mm-yyyy" style="text-align: center"
                                                                    autocomplete="off" value="{{ date('d-m-Y', strtotime($header->batas1)) }}">
                                                            </td>
                                                            <td>
                                                                <input name="POS[]" value="{{ $header->POS }}"
                                                                    id="POS{{ $no }}" type="text"
                                                                    style="text-align: right"
                                                                    class="form-control POS text-primary" readonly>
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
                                                        {{-- @endforeach --}}
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
                                                    onclick="location.href='{{ url('/brg-jasa-pa/edit/?idx=' . $idx . '&tipx=top') }}'"
                                                    class="btn btn-outline-primary">Top</button>
                                                <button type="button" hidden id='PREVX'
                                                    onclick="location.href='{{ url('/brg-jasa-pa/edit/?idx=' . $header->NO_ID . '&tipx=prev&kodex=' . $header->ACNO) }}'"
                                                    class="btn btn-outline-primary">Prev</button>
                                                <button type="button" hidden id='NEXTX'
                                                    onclick="location.href='{{ url('/brg-jasa-pa/edit/?idx=' . $header->NO_ID . '&tipx=next&kodex=' . $header->ACNO) }}'"
                                                    class="btn btn-outline-primary">Next</button>
                                                <button type="button" hidden id='BOTTOMX'
                                                    onclick="location.href='{{ url('/brg-jasa-pa/edit/?idx=' . $idx . '&tipx=bottom') }}'"
                                                    class="btn btn-outline-primary">Bottom</button>
                                            </div>
                                            <div class="col-md-5">
                                                <button type="button" hidden id='NEWX'
                                                    onclick="location.href='{{ url('/brg-jasa-pa/edit/?idx=0&tipx=new') }}'"
                                                    class="btn btn-warning">New</button>
                                                <button type="button" id='EDITX' onclick='hidup()'
                                                    class="btn btn-secondary">Edit</button>
                                                <button type="button" hidden id='UNDOX'
                                                    onclick="location.href='{{ url('/brg-jasa-pa/edit/?idx=' . $idx . '&tipx=undo') }}'"
                                                    class="btn btn-info">Undo</button>
                                                <button type="button" id='SAVEX' onclick='simpan()'
                                                    class="btn btn-success" class="fa fa-save"></i>Save</button>

                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" hidden id='HAPUSX' onclick="hapusTrans()"
                                                    class="btn btn-outline-danger">Hapus</button>
                                                <button type="button" id='CLOSEX'
                                                    onclick="location.href='{{ url('/brg-jasa-pa') }}'"
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

        <div class="modal fade" id="browseDeptModal" tabindex="-1" role="dialog" aria-labelledby="browseDeptModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl" role="document">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="browseDeptModalLabel">Cari Dept</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-stripped table-bordered" id="table-bdept">
					<thead>
						<tr>
							<th>Kode</th>
							<th>Department</th>
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
                }

                if ($tipx != 'new') {
                    ganti();
                }

                $('.date').datepicker({
                    dateFormat: 'dd-mm-yy'
                });

                // 14-11-2025 wahyu
                var dTableBDept;
                loadDataBDept = function(){

                    $.ajax(
                    {
                        type: 'GET',
                        url: '{{url('brg-jasa/browse_dept_pa')}}',


                        beforeSend: function(){
                            $("#LOADX").show();
                        },


                        success: function( response )
                        {
                            $("#LOADX").hide();

                            resp = response;
                            if(dTableBDept){
                                dTableBDept.clear();
                            }
                            for(i=0; i<resp.length; i++){

                                dTableBDept.row.add([
                                    '<a href="javascript:void(0);" onclick="chooseDept(\''+resp[i].KD+'\',  \''+resp[i].DEP+'\')">'+resp[i].KD+'</a>',
                                    resp[i].DEP,
                                ]);
                            }
                            dTableBDept.draw();
                        }
                    });
                }

                dTableBDept = $("#table-bdept").DataTable({

                });

                browseDept = function(){
                    loadDataBDept();
                    $("#browseDeptModal").modal("show");
                }

                chooseDept = function(KD,DEP){
                    $("#kd_dept").val(KD);
                    $("#dept").val(DEP);
                    $("#browseDeptModal").modal("hide");

                }

                $("#kd_dept").keypress(function(e){

                    if(e.keyCode == 13){
                        e.preventDefault();
                        browseKode();
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


                $tipx = $('#tipx').val();

                if ($tipx == 'new') {

                    $("#BUKTI").attr("readonly", true);

                } else {
                    $("#BUKTI").attr("readonly", true);

                }

                jumlahdata = 100;
                for (i = 0; i <= jumlahdata; i++) {
                    $("#na_brg" + i.toString()).attr("readonly", false);
                    $("#ukuran" + i.toString()).attr("readonly", false);
                    $("#merk" + i.toString()).attr("readonly", false);
                    $("#SATUAN" + i.toString()).attr("readonly", false);
                    $("#qty" + i.toString()).attr("readonly", false);
                    $("#harga" + i.toString()).attr("readonly", false);
                    $("#total" + i.toString()).attr("readonly", false);
                    $("#batas1" + i.toString()).attr("readonly", false);
                    $("#POS" + i.toString()).attr("readonly", false);
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

                jumlahdata = 100;
                for (i = 0; i <= jumlahdata; i++) {
                    $("#REC" + i.toString()).attr("readonly", true);
                    $("#na_brg" + i.toString()).attr("readonly", true);
                    $("#ukuran" + i.toString()).attr("readonly", true);
                    $("#merk" + i.toString()).attr("readonly", true);
                    $("#SATUAN" + i.toString()).attr("readonly", true);
                    $("#qty" + i.toString()).attr("readonly", true);
                    $("#harga" + i.toString()).attr("readonly", true);
                    $("#total" + i.toString()).attr("readonly", true);
                    $("#batas1" + i.toString()).attr("readonly", true);
                    $("#POS" + i.toString()).attr("readonly", true);
                }
            }


            function kosong() {

                $('#BUKTI').val("");
                $('#NAMA').val("");
                $('#TGL').val("");
                $('#TGL_SLS').val("");
            }

            function hapusTrans() {
                let text = "Hapus Master " + $('#BUKTI').val() + "?";
                if (confirm(text) == true) {
                    window.location = "{{ url('/brg-jasa-pa/delete/' . $header->NO_ID) }}'";
                    //return true;
                }
                return false;
            }

            function CariBukti() {

                var cari = $("#CARI").val();
                var loc = "{{ url('/brg-jasa-pa/edit/') }}" + '?idx={{ $header->NO_ID }}&tipx=search&kodex=' +
                    encodeURIComponent(
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
						<input name='na_brg[]' id='na_brg${idrow}' type='text' class='form-control' readonly required>
					</td>
					<td>
						<input name='ukuran[]' id='ukuran${idrow}' type='text' class='form-control' readonly required>
					</td>
                    <td>
						<input name='merk[]' id='merk${idrow}' type='text' class='form-control' readonly required>
					</td>
                    <td>
						<input name='SATUAN[]' id='SATUAN${idrow}' type='text' class='form-control' readonly required>
					</td>
					<td>
						<input name='qty[]' id='qty${idrow}' value='0' type='text' style='text-align:right' class='form-control text-primary' readonly required>
					</td>
					<td>
						<input name='harga[]' id='harga${idrow}' value='0' type='text' style='text-align:right' class='form-control text-primary' readonly required>
					</td>
                    <td>
						<input name='total[]' id='total${idrow}' value='0' type='text' style='text-align:right' class='form-control text-primary' readonly required>
					</td>
                   <td>
                        <input name="batas1[]" id="batas1${idrow}" type="text" class="form-control date text-primary" data-date-format="dd-mm-yyyy" autocomplete="off" required>
                    </td>
                    <td>
						<input name='POS[]' id='POS${idrow}' type='text' class='form-control' readonly required>
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

            $(document).on("blur", ".qty, .harga", function() {
                let row = $(this).closest("tr");
                let qty = parseFloat(row.find(".qty").val()) || 0;
                let harga = parseFloat(row.find(".harga").val()) || 0;
                row.find(".total").val(qty * harga);
            });
        </script>
    @endsection
