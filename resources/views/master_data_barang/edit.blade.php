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
    /* perubahan tab warna di form edit  */
	.nav-item .nav-link.active {
		background-color: red !important; /* Use !important to ensure it overrides */
		color: white !important;
        /* border-radius: 10; */
	}
</style>

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Data Barang Food Center</h1>
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
                                    action="{{ $tipx == 'new' ? url('/dbrg/store/') : url('/dbrg/update/' . $header->NO_ID) }}"
                                    method="POST" name ="entri" id="entri">
                                    @csrf
                                    <div class="tab-content mt-3">
                                        <div class="form-group row">
                                            <div class="col-md-1" align="right">
                                                <label for="SUB">Sub</label>
                                            </div>
                                            <input type="text" class="form-control NO_ID" id="NO_ID"
                                                name="NO_ID" placeholder="Masukkan NO_ID"
                                                value="{{ $header->NO_ID ?? '' }}" hidden readonly>

                                            <input name="tipx" class="form-control flagz" id="tipx"
                                                value="{{ $tipx }}" hidden>
                                            <div class="col-md-1">
                                                <input type="text" class="form-control" name="SUB" id="SUB"
                                                    value="{{ $header->SUB }}" placeholder="Masukkan Sub">
                                            </div>

											<div class="col-md-1" align="right">
                                                <label for="KDBAR">Item</label>
                                            </div>
                                            <div class="col-md-1">
                                                <input type="text" class="form-control" name="KDBAR" id="KDBAR"
                                                    value="{{ $header->KDBAR }}" placeholder="Masukkan Item">
                                            </div>

											<div class="col-md-1"></div>

											<div class="col-md-1" align="right">
												<label for="SUPP">Supplier</label>
											</div>
											<div class="col-md-3">
												<input type="text" class="form-control" name="SUPP" id="SUPP" onblur="browseSup()"
													value="{{ $header->SUPP }}" placeholder="Masukkan Kode Supplier" readonly>
											</div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-md-1" align="right">
                                                <label for="NA_BRG">Nama</label>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" name="NA_BRG" id="NA_BRG"
                                                    value="{{ $header->NA_BRG }}" placeholder="Masukkan Nama Barang" readonly>
                                            </div>

											<div class="col-md-1"></div>

											<div class="col-md-1" align="right">
												<label for="NAMAS">Nama Supplier</label>
											</div>
											<div class="col-md-3">
												<input type="text" class="form-control " name="NAMAS" id="NAMAS"
													value="{{ $header->NAMAS }}" placeholder="Masukkan Nama Supplier">
											</div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-md-1" align="right">
                                                <label for="KELOMPOK">Kelompok</label>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" name="KELOMPOK" id="KELOMPOK"
                                                    value="{{ $header->KELOMPOK }}" placeholder="Masukkan Kelompok">
                                            </div>

											<div class="col-md-1"></div>

											<div class="col-md-1" align="right">
                                                <label for="BARCODE">Barcode</label>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" name="BARCODE" id="BARCODE"
                                                    value="{{ $header->BARCODE }}" placeholder="Masukkan Barcode">
                                            </div>
                                        </div>

										<div class="form-group row">
											<div class="col-md-1" align="right">
												<label for="HB">Harga Beli</label>
											</div>
											<div class="col-md-2">
												<input type="text" class="form-control text-right" name="HB" id="HB"
													value="{{ $header->HB }}" placeholder="Masukkan Harga Beli">
											</div>
											
											<div class="col-md-2"></div>

											<div class="col-md-1" align="right">
												<label for="STAND">Stand</label>
											</div>
											<div class="col-md-1">
												<input type="text" class="form-control text-right" name="STAND" id="STAND"
													value="{{ $header->STAND }}" placeholder="Stand">
											</div>

											<div class="col-md-1" align="right">
												<label for="LOC_TG" class="form-label">Lokasi</label>
											</div>
											<div class="col-md-1">
												<select id="LOC_TG" class="form-control"  name="LOC_TG">
													<option value="-" {{ ( $header->LOK_TG== '-') ? 'selected' : '' }} disable selected hidden>--Pilih Lokasi--</option>
													<option value="C" {{ ( $header->LOK_TG== 'C') ? 'selected' : '' }}>C</option>
													<option value="D" {{ ( $header->LOK_TG== 'D') ? 'selected' : '' }}>D</option>
													<option value="X" {{ ( $header->LOK_TG== 'X') ? 'selected' : '' }}>X</option>
												</select>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-1" align="right">
												<label for="HJ">HJ FDCenter</label>
											</div>
											<div class="col-md-2">
												<input type="text" class="form-control text-right" name="HJ" id="HJ"
													value="{{ $header->HJ }}" placeholder="Masukkan Harga Jual">
											</div>
											
											<div class="col-md-2"></div>

											<div class="col-md-1" align="right">
												<label for="TYPE" class="form-label">Type</label>
											</div>
											<div class="col-md-1">
												<select id="TYPE" class="form-control"  name="TYPE">
													<option value="-" {{ ( $header->TYPE== '-') ? 'selected' : '' }} disable selected hidden>--Pilih Type--</option>
													<option value="TG" {{ ( $header->TYPE== 'TG') ? 'selected' : '' }}>TG</option>
												</select>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-1" align="right">
												<label for="TKP">Non Pajak PB1</label>
											</div>
											<div class="col-md-1">
												<input type="text" class="form-control TKP" name="TKP" id="TKP"
													value="{{ $header->TKP }}" placeholder="Masukkan TKP">
											</div>

											<div class="col-md-1" align="right">
												<label for="FLAGSTOK">Stok</label>
											</div>
											<div class="col-md-1">
												<input type="text" class="form-control FLAGSTOK" name="FLAGSTOK" id="FLAGSTOK"
													value="{{ $header->FLAGSTOK }}" placeholder="Masukkan Flagstok">
											</div>
											
											<div class="col-md-1"></div>

											<div class="col-md-1" align="right">
												<label for="MARGIN">Margin</label>
											</div>
											<div class="col-md-1">
												<input type="text" class="form-control text-right" name="MARGIN" id="MARGIN"
													value="{{ $header->MARGIN }}" placeholder="Masukkan Margin">
											</div>

											<div class="col-md-1" align="right">
												<label for="DIS">Diskon</label>
											</div>
											<div class="col-md-1">
												<input type="text" class="form-control text-right" name="DIS" id="DIS"
													value="{{ $header->DIS }}" placeholder="Masukkan Diskon">
											</div>
										</div>

										<hr></hr>

										<div class="form-group row">
											<div class="col-md-2" align="right">
												<h1>Kasir GoFood</h1>
											</div>
										</div>
                                        
										<div class="form-group row">
											<div class="col-md-1" align="right">
												<label for="MARGIN_GO">Margin</label>
											</div>
											<div class="col-md-2">
												<input type="text" class="form-control text-right" name="MARGIN_GO" id="MARGIN_GO"
													value="{{ $mask->MARGIN_GO }}" placeholder="Masukkan Margin">
											</div>

											<div class="col-md-1" align="right">
												<label for="HJGO">HJ GoFood </label>
											</div>
											<div class="col-md-2">
												<input type="text" class="form-control text-right" name="HJGO" id="HJGO"
													value="{{ $mask->HJGO }}" placeholder="Masukkan Harga">
											</div>
										</div>
                                        
                                        <div class="mt-3 col-md-12 form-group row">
                                            <div class="col-md-4">
                                                <button type="button" hidden id='TOPX'
                                                    onclick="location.href='{{ url('/dbrg/edit/?idx=' . $idx . '&tipx=top') }}'"
                                                    class="btn btn-outline-primary">Top</button>
                                                <button type="button" hidden id='PREVX'
                                                    onclick="location.href='{{ url('/dbrg/edit/?idx=' . $header->NO_ID . '&tipx=prev&kodex=' . $header->SUB) }}'"
                                                    class="btn btn-outline-primary">Prev</button>
                                                <button type="button" hidden id='NEXTX'
                                                    onclick="location.href='{{ url('/dbrg/edit/?idx=' . $header->NO_ID . '&tipx=next&kodex=' . $header->SUB) }}'"
                                                    class="btn btn-outline-primary">Next</button>
                                                <button type="button" hidden id='BOTTOMX'
                                                    onclick="location.href='{{ url('/dbrg/edit/?idx=' . $idx . '&tipx=bottom') }}'"
                                                    class="btn btn-outline-primary">Bottom</button>
                                            </div>
                                            <div class="col-md-5">
                                                <button type="button" hidden id='NEWX'
                                                    onclick="location.href='{{ url('/dbrg/edit/?idx=0&tipx=new') }}'"
                                                    class="btn btn-warning">New</button>
                                                <button type="button" hidden id='EDITX' onclick='hidup()'
                                                    class="btn btn-secondary">Edit</button>
                                                <button type="button" hidden id='UNDOX'
                                                    onclick="location.href='{{ url('/dbrg/edit/?idx=' . $idx . '&tipx=undo') }}'"
                                                    class="btn btn-info">Undo</button>
                                                <button type="button" id='SAVEX' onclick='simpan()'
                                                    class="btn btn-success" class="fa fa-save"></i>Save</button>

                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" hidden id='HAPUSX' onclick="hapusTrans()"
                                                    class="btn btn-outline-danger">Hapus</button>
                                                <button type="button" id='CLOSEX'
                                                    onclick="location.href='{{ url('/dbrg') }}'"
                                                    class="btn btn-outline-secondary">Close</button>


                                            </div>
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
		<div class="modal fade" id="browseSupModal" tabindex="-1" role="dialog" aria-labelledby="browseSupModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="browseSupModalLabel">Cari Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-stripped table-bordered" id="table-bsup">
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
    @endsection

    @section('footer-scripts')
        <script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

                $("#HJ").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
                $("#HB").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
                $("#MARGIN").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
                $("#DIS").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
                $("#MARGIN_GO").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
                $("#HJGO").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});

                $('.date').datepicker({
                    dateFormat: 'dd-mm-yy'
                });

                $(function() {
                    $('#JAM, #JAMSLS').timepicker({
                        timeFormat: 'HH:mm:ss',
                        stepHour: 1,
                        stepMinute: 1,
                        stepSecond: 1
                    });
                });
				///////////////////////////////////
				// CHOOSE Sup
                var dTableBSup;
                loadDataBSup = function(){

                    $.ajax(
                    {
                        type: 'GET',
                        url: '{{url('dbrg/browse')}}',


                        beforeSend: function(){
                            $("#LOADX").show();
                        },


                        success: function( response )
                        {
                            $("#LOADX").hide();

                            resp = response;
                            if(dTableBSup){
                                dTableBSup.clear();
                            }
                            for(i=0; i<resp.length; i++){

                                dTableBSup.row.add([
                                    '<a href="javascript:void(0);" onclick="chooseSup(\''+resp[i].KODES+'\',  \''+resp[i].NAMAS+'\')">'+resp[i].KODES+'</a>',
                                    resp[i].NAMAS,
                                ]);
                            }
                            dTableBSup.draw();
                        }
                    });
                }

                dTableBSup = $("#table-bsup").DataTable({

                });

                browseSup = function(){
                    loadDataBSup();
                    $("#browseSupModal").modal("show");
                }

                chooseSup = function(KODES,NAMAS){
                    $("#SUPP").val(KODES);
                    $("#NAMAS").val(NAMAS);
                    $("#browseSupModal").modal("hide");

                }

                $("#SUPP").keypress(function(e){

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
                $("#EDITX").attr("disabled", true);
                $("#UNDOX").attr("disabled", false);
                $("#SAVEX").attr("disabled", false);

                $("#HAPUSX").attr("disabled", true);
                $("#CLOSEX").attr("disabled", false);


                $tipx = $('#tipx').val();

                if ($tipx == 'new') {

                    $("#SUB").attr("readonly", false);
                    $("#KDBAR").attr("readonly", false);

                } else {
                    $("#SUB").attr("readonly", true);
                    $("#KDBAR").attr("readonly", true);

                }

                $("#KD_BRG").attr("readonly", false);
                $("#NA_BRG").attr("readonly", false);
                $("#KELOMPOK").attr("readonly", false);
				$("#HB").attr("readonly", false);
				$("#HJ").attr("readonly", false);
				$("#TKP").attr("readonly", false);
				$("#FLAGSTOK").attr("readonly", false);
				$("#SUPP").attr("readonly", true);
				$("#NAMAS").attr("readonly", true);
				$("#MARGIN").attr("readonly", false);
				$("#MARGIN2").attr("readonly", false);
				$("#HJ2").attr("readonly", false);
				$("#DIS").attr("readonly", false);
				$("#STAND").attr("readonly", false);
				$("#TYPE").attr("readonly", false);
				$("#BARCODE").attr("readonly", false);
				$("#LOK_TG").attr("readonly", false);
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

                $("#SUB").attr("readonly", true);
                $("#KD_BRG").attr("readonly", true);
                $("#NA_BRG").attr("readonly", true);
                $("#KELOMPOK").attr("readonly", true);
				$("#HB").attr("readonly", true);
				$("#HJ").attr("readonly", true);
				$("#TKP").attr("readonly", true);
				$("#FLAGSTOK").attr("readonly", true);
				$("#SUPP").attr("readonly", true);
				$("#NAMAS").attr("readonly", true);
				$("#MARGIN").attr("readonly", true);
				$("#MARGIN2").attr("readonly", true);
				$("#HJ2").attr("readonly", true);
				$("#DIS").attr("readonly", true);
				$("#STAND").attr("readonly", true);
				$("#TYPE").attr("readonly", true);
				$("#BARCODE").attr("readonly", true);
				$("#LOK_TG").attr("readonly", true);
            }


            function kosong() {
                $('#HJ').val("0.00");
                $('#HB').val("0.00");
                $('#DIS').val("0.00");
                $('#MARGIN').val("0.00");
                $('#HJGO').val("0");
                $('#MARGIN_GO').val("0");
            }

            function hapusTrans() {
                let text = "Hapus Master " + $('#SUB').val() + "?";
                if (confirm(text) == true) {
                    window.location = "{{ url('/dbrg/delete/' . $header->NO_ID) }}'";
                    //return true;
                }
                return false;
            }

            function CariBukti() {

                var cari = $("#CARI").val();
                var loc = "{{ url('/dbrg/edit/') }}" + '?idx={{ $header->NO_ID }}&tipx=search&kodex=' + encodeURIComponent(
                    cari);
                window.location = loc;

            }

			function simpan() {
				hasilCek=0;
				$tipx = $('#tipx').val();
				
				if ( $('#SUB').val()=='' ) 
				{				
					hasilCek = '1';
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Sub Harus Diisi.'
					});
					return; // Stop function execution
				}

				if ( $('#KDBAR').val()=='' ) 
				{				
					hasilCek = '1';
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Item Harus Diisi.'
					});
					return; // Stop function execution
				}

				if ( $('#NA_BRG').val()=='' ) 
				{				
					hasilCek = '1';
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Nama Barang Harus Diisi.'
					});
					return; // Stop function execution
				}


				document.getElementById("entri").submit()
			}
            
            document.addEventListener("DOMContentLoaded", function () {

                // --- Validasi SUB ---
                document.getElementById('SUB').addEventListener('input', function() {
                    if (this.value.length > 3) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Sub maksimal 3 karakter!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        this.value = this.value.substring(0, 3);
                    }
                });

                // --- Validasi ITEM / KDBAR ---
                document.getElementById('KDBAR').addEventListener('input', function() {
                    if (this.value.length > 4) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Item maksimal 4 karakter!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        this.value = this.value.substring(0, 4);
                    }
                });

            });
        </script>
    @endsection
