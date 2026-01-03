@extends('layouts.plain')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
	.card {}


	.form-control:focus {
		background-color: #b5e5f9 !important;
	}

	.table-scrollable {
		margin: 0;
		padding: 0;
	}

	table {
		table-layout: fixed !important;
	}

	.ukhususercase {
		text-transform: ukhususercase;
	}

	/* query LOADX */

	.loader {
		position: fixed;
		top: 50%;
		left: 50%;
		width: 100px;
		aspect-ratio: 1;
		background:
			radial-gradient(farthest-side, #ffa516 90%, #0000) center/16px 16px,
			radial-gradient(farthest-side, green 90%, #0000) bottom/12px 12px;
		background-repeat: no-repeat;
		animation: l17 1s infinite linear;
		position: relative;
	}

	.loader::before {
		content: "";
		position: absolute;
		width: 8px;
		aspect-ratio: 1;
		inset: auto 0 16px;
		margin: auto;
		background: #ccc;
		border-radius: 50%;
		transform-origin: 50% calc(100% + 10px);
		animation: inherit;
		animation-duration: 0.5s;
	}

	@keyframes l17 {
		100% {
			transform: rotate(1turn)
		}
	}

	/* penutup LOADX */


	/* style tambahan baru */
	.form-control:disabled,
	.form-control[readonly] {
		background-color: #f7d8b4 !important;
		opacity: 1;
	}

	.row {
		margin-bottom: 8px !important;
	}

	/* menghilangkan padding */
	.content-header {
		padding: 0 !important;
	}
</style>

@section('content')



<div class="content-wrakhususer">
	<div class="content-header">
		<div class="container-fluid">

		</div>
	</div>

	<div class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-body">

							<form action="{{($tipx=='new')? url('/khusus/store?flagz='.$flagz.'') : url('/khusus/update/'.$header->NO_ID.'&flagz='.$flagz.'' ) }}" method="POST" name="entri" id="entri">

								@csrf
								<div class="tab-content mt-3">

									<div class="form-group row">
										<div class="col-md-1" align="right">
											<label for="NO_BUKTI" class="form-label">Bukti#</label>
										</div>


										<input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
											placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

										<input name="tipx" class="form-control tipx" id="tipx" value="{{$tipx}}" hidden>
										<input name="flagz" class="form-control flagz" id="flagz" value="{{$flagz}}" hidden>



										<div class="col-md-2">
											<input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI"
												placeholder="Masukkan Bukti#" value="{{$header->NO_BUKTI}}" readonly>
										</div>

										<div class="col-md-1" align="right">
											<label for="KIRIM" class="form-label">Kirim Ke :</label>
										</div>
										<div class="col-md-1">
											<input type="text" class="form-control KIRIM" id="KIRIM" name="KIRIM"
												placeholder="Masukkan Type" value="{{$header->KIRIM}}" readonly>
										</div>

										<div class="col-md-1"></div>

										<div class="col-md-1" align="right">
											<label for="JENIS" class="form-label">Pesan Ke</label>
										</div>
										<div class="col-md-1">
											<select class="form-control JENIS" id="JENIS" name="JENIS">
												<option value="-" disable selected hidden>--Pilih Jenis--</option>
												<option value="DC1">DC1</option>
												<option value="DC2">DC2</option>
											</select>
										</div>
									</div>

									<div class="form-group row">
										<div class="col-md-1" align="right">
											<label for="TGL" class="form-label">Tgl</label>
										</div>
										<div class="col-md-2">
											<input class="form-control date" on id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}">
										</div>

										<div class="col-md-1" align="right">
											<label for="TYPE" class="form-label">Type</label>
										</div>
										<div class="col-md-1">
											<input type="text" class="form-control TYPE" id="TYPE" name="TYPE"
												placeholder="Masukkan Type" value="{{$header->TYPE}}" readonly>
										</div>

										<div class="col-md-1"></div>

										<div class="col-md-1" align="right">
											<label for="LPH1" class="form-label">Lph</label>
										</div>
										<div class="col-md-1">
											<input type="text" class="form-control LPH1" id="LPH1" name="LPH1"
												placeholder="Masukkan Lph">
										</div>

										<div class="col-md-1" align="right">
											<label for="LPH2" class="form-label">S/d</label>
										</div>
										<div class="col-md-1">
											<input type="text" class="form-control LPH2" id="LPH2" name="LPH2"
												placeholder="Masukkan Lph">
										</div>
									</div>

									<div class="form-group row">
										<div class="col-md-1" align="right">
											<label for="NOTES" class="form-label">Notes</label>
										</div>
										<div class="col-md-4">
											<input type="text" class="form-control NOTES" id="NOTES" name="NOTES"
												placeholder="Masukkan Notes" value="{{$header->NOTES}}" readonly>
										</div>

										<div class="col-md-1"></div>

										<div class="col-md-1" align="right">
											<label for="BUTUH" class="form-label">Kebutuhan</label>
										</div>
										<div class="col-md-1">
											<input type="text" class="form-control BUTUH" id="BUTUH" name="BUTUH"
												placeholder="Masukkan Kebutuhan">
										</div>
										<div class="col-md-1" align="left">
											<label for="HARI" class="form-label">Hari</label>
										</div>
									</div>

									<div class="form-group row">
										<div class="col-md-6"></div>
										<div class="col-md-1" align="right">
											<label for="SUB1" class="form-label">Sub</label>
										</div>
										<div class="col-md-1">
											<input type="text" class="form-control SUB1" id="SUB1" name="SUB1"
												placeholder="Masukkan Sub">
										</div>

										<div class="col-md-1" align="right">
											<label for="SUB2" class="form-label">S/d</label>
										</div>
										<div class="col-md-1">
											<input type="text" class="form-control SUB2" id="SUB2" name="SUB2"
												placeholder="Masukkan Sub">
										</div>
									</div>
									
									<div class="form-group row">
										<div class="col-md-6"></div>
										<div class="col-md-1" align="right">
											<label for="SUPP" class="form-label">Supplier</label>
										</div>
										<div class="col-md-2">
											<input type="text" class="form-control SUPP" id="SUPP" name="SUPP"
												placeholder="Masukkan Sup">
										</div>
									</div>
									<div class="form-group row">
										<div class="col-md-8"></div>
										<div class="col-md-3">
											<button class="btn btn-primary" type="button" id="AMBIL" class="AMBIL" name="AMBIL" onclick="getSod()">Ambil Data</button>
										</div>
									</div>
									<!-- loader tampil di modal  -->
									<div class="loader" style="z-index: 1055;" id='LOADX'></div>
									<!-- batas load -->

									<table id="datatable" class="table table-striped table-border table-responsive">

										<thead>
											<tr>
												<th width="50px" style="text-align:center">No.</th>

												<th width="200px" style="text-align:center">Barang</th>

												<th width="550px" style="text-align:center">Nama</th>

												<th width="200px" style="text-align:center">Kemasan</th>
												<th width="200px" style="text-align:right">Qty</th>
												<th width="200px" style="text-align:right">Harga</th>
												<th width="200px" style="text-align:right">Total</th>
												<th width="200px" style="text-align:center">LPH</th>
												<th width="200px" style="text-align:right">Stock Outlet</th>
												<th width="200px" style="text-align:right">Stock DC</th>
												<th width="200px" style="text-align:center">Notes</th>
												<th></th>

											</tr>
										</thead>

										<tbody id="detailSod">
											<?php $no = 0 ?>
											@foreach ($detail as $detail)
											<tr>
												<td>
													<input type="hidden" name="NO_ID[]{{$no}}" id="NO_ID" type="text" value="{{$detail->NO_ID}}"
														class="form-control NO_ID" onkeypress="return tabE(this,event)" readonly>

													<input name="REC[]" id="REC{{$no}}" type="text" value="{{$detail->REC}}" class="form-control REC" onkeypress="return tabE(this,event)" readonly style="text-align:center">
												</td>


												<td>
													<input name="KD_BRG[]" id="KD_BRG{{$no}}" type="text" class="form-control KD_BRG "
														value="{{$detail->KD_BRG}}" onblur="browseBarang('{{$no}}')">
												</td>

												<td>
													<input name="NA_BRG[]" id="NA_BRG{{$no}}" type="text" class="form-control NA_BRG " value="{{$detail->NA_BRG}}">
												</td>
												<td>
													<input name="LPH[]" id="KET_KEM{{$no}}" type="text" class="form-control KET_KEM" value="{{$detail->ket_kem}}" readonly required>
												</td>
												<td>
													<input name=" QTY[]" onclick="select()" onblur="hitung()" value="{{$detail->qty}}" id="QTY{{$no}}" type="text" style="text-align: right" class="form-control QTY">
												</td>
												<td>
													<input name=" HARGA[]" onclick="select()" onblur="hitung()" value="{{$detail->harga}}" id="HARGA{{$no}}" type="text" style="text-align: right" class="form-control HARGA">
												</td>
												<td>
													<input name=" TOTAL[]" onclick="select()" onblur="hitung()" value="{{$detail->total}}" id="TOTAL{{$no}}" type="text" style="text-align: right" class="form-control TOTAL">
												</td>
												<td>
													<input name="LPH[]" id="LPH{{$no}}" type="text" class="form-control LPH" value="{{$detail->LPH}}" readonly required>
												</td>
												<td>
													<input name=" STOK[]" onclick="select()" onblur="hitung()" value="{{$detail->stok}}" id="STOK{{$no}}" type="text" style="text-align: right" class="form-control STOK">
												</td>

												<td>
													<input name=" STOKZ[]" onclick="select()" onblur="hitung()" value="{{$detail->stokz}}" id="STOKZ{{$no}}" type="text" style="text-align: right" class="form-control STOKZ">
												</td>

												<td>
													<input name="KET[]" id="KET{{$no}}" type="text" class="form-control KET " value="{{$detail->KET}}">
												</td>

												<td>
													<button type='button' id='DELETEX{{$no}}' class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
												</td>

											</tr>

											<?php $no++; ?>
											@endforeach
										</tbody>

										<tfoot>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td><input class="form-control TTOTAL_QTY  text-primary" style="text-align: right" id="TTOTAL_QTY" name="TTOTAL_QTY" value="{{$header->TOTAL_QTY}}" readonly></td>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
										</tfoot>
									</table>
									<!-- scroll -->

									<!--</div> -->

									<!-- batas -->

								</div>
						</div>

						<div class="tab-content mt-6">

							<div class="form-group row">
								<div class="col-md-1" align="center">
									<a type="button" id='PLUSX' onclick="tambah()" class="fas fa-plus fa-sm md-3" style="font-size: 20px"></a>
								</div>
							</div>
						</div>

						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button hidden type="button" id='TOPX' onclick=`location.href='{{url('/khusus/edit/?idx=' .$idx. '&tipx=top&flagz='.$flagz.'' )}}' ` class="btn btn-outline-primary">Top</button>
								<button hidden type="button" id='PREVX' onclick=`location.href='{{url('/khusus/edit/?idx='.$header->NO_ID.'&tipx=prev&flagz='.$flagz.'&buktix='.$header->NO_BUKTI )}}' ` class="btn btn-outline-primary">Prev</button>
								<button hidden type="button" id='NEXTX' onclick=`location.href='{{url('/khusus/edit/?idx='.$header->NO_ID.'&tipx=next&flagz='.$flagz.'&buktix='.$header->NO_BUKTI )}}' ` class="btn btn-outline-primary">Next</button>
								<button hidden type="button" id='BOTTOMX' onclick=`location.href='{{url('/khusus/edit/?idx=' .$idx. '&tipx=bottom&flagz='.$flagz.'' )}}' ` class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button hidden type="button" id='NEWX' onclick=`location.href='{{url('/khusus/edit/?idx=0&tipx=new&flagz='.$flagz.'' )}}' ` class="btn btn-warning">New</button>
								<button hidden type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>
								<button hidden type="button" id='UNDOX' onclick=`location.href='{{url('/khusus/edit/?idx=' .$idx. '&tipx=undo&flagz='.$flagz.'' )}}' ` class="btn btn-info">Undo</button>
								<button type="button" id='SAVEX' onclick='simpan()' class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button hidden type="button" id='HAPUSX' onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>

								<!-- <button type="button" id='CLOSEX'  onclick=`location.href='{{url('/khusus?flagz='.$flagz.'' )}}'` class="btn btn-outline-secondary">Close</button>  -->

								<!-- tombol close sweet alert -->
								<button type="button" id='CLOSEX' onclick="closeTrans()" class="btn btn-outline-secondary">Close</button>
							</div>
						</div>
					</div>


					</form>
				</div>
			</div>
		</div>
	</div>
</div>
</div>


<div class="modal fade" id="browseSuplierModal" tabindex="-1" role="dialog" aria-labelledby="browseSuplierModalLabel" aria-hidden="true">
	<div class="modal-dialog mw-100 w-75" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="browseSuplierModalLabel">Cari Suplier</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-strikhusused table-bordered" id="table-bsuplier">
					<thead>
						<tr>
							<th>Suplier</th>
							<th>Nama</th>
							<th>Alamat</th>
							<th>Kota</th>
							<th>Status PKP</th>
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


<div class="modal fade" id="browseBarangModal" tabindex="-1" role="dialog" aria-labelledby="browseBarangModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="browseBarangModalLabel">Cari Item</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-strikhusused table-bordered" id="table-bbarang">
					<thead>
						<tr>
							<th>Item#</th>
							<th>Nama</th>
							<th>Satuan</th>
							<th>Suplier</th>

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

<div class="modal fade" id="browseSoModal" tabindex="-1" role="dialog" aria-labelledby="browseSoModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="browseSoModalLabel">Cari SO</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-strikhusused table-bordered" id="table-bso">
					<thead>
						<tr>
							<th>No Bukti</th>
							<th>Tgl</th>
							<th>Customer</th>
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


<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script> -->

<!-- tambahan untuk sweetalert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- tutupannya -->

<script>
	var idrow = 1;
	var baris = 1;

	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}

	$(document).ready(function() {


		setTimeout(function() {

			$("#LOADX").hide();

		}, 500);

		idrow = <?= $no ?>;
		baris = <?= $no ?>;


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
					tambah();
					// var nomer = idrow-1;
					// console.log("REC"+nomor);
					// document.getElementById("REC"+nomor).focus();
					// form.submit();
				}
				return false;
			}
		});


		$tipx = $('#tipx').val();
		$searchx = $('#CARI').val();


		if (($tipx == 'new')) {
			baru();
			// tambah();
		}


		if ($tipx != 'new') {
			ganti();
		}

		$("#TTOTAL_QTY").autoNumeric('init', {
			aSign: '<?php echo ''; ?>',
			vMin: '-999999999.99'
		});


		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#QTY" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});

			$("#HARGA" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});

			$("#TOTAL" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});
		}



		$('#COMPAN').select2({

			placeholder: 'Pilih Cabang',
			allowClear: true,
			ajax: {
				url: "{{url('compan/browse')}}",
				dataType: 'json',
				delay: 250,
				data: function(params) {
					return {
						q: params.term // Search term
					};
				},
				processResults: function(data) {
					return {
						results: data.map(item => ({
							id: item.KODE, // The ID of the user
							text: item.NAMA // The text to display
						}))
					};
				},
				cache: true
			},
		});
		$('#COMPAN').on('select2:select', function(e) {
			var data = e.params.data;
			// console.log(data);
			$("#NAMA").val(data.text);
		});

		$('#COMPAN').on('select2:unselect', function(e) {
			$("#NAMA").val('');
			$("#COMPAN").val('');
		});




		$('body').on('click', '.btn-delete', function() {
			var val = $(this).parents("tr").remove();
			baris--;
			hitung();
			nomor();

		});

		$('.date').datepicker({
			dateFormat: 'dd-mm-yy'
		});




		//		CHOOSE Sukhususlier
		var dTableBSuplier;
		loadDataBSuplier = function(id) {
			$.ajax({
				type: 'GET',
				url: "{{url('khusus/browse_sukhususlier')}}",
				data: {
					"KD_BRG": $("#KD_BRG" + id).val(),
				},


				beforeSend: function() {
					$("#LOADX").show();
				},


				success: function(response) {
					$("#LOADX").hide();

					resp = response;



					for (i = 0; i < resp.length; i++) {

						dTableBSuplier.row.add([
							'<a href="javascript:void(0);" onclick="chooseSuplier(\'' + resp[i].KODES + '\', \'' + resp[i].NAMAS + '\',  \'' + resp[i].ALAMAT + '\', \'' + resp[i].KOTA + '\', \'' + resp[i].PKP + '\')">' + resp[i].KODES + '</a>',
							resp[i].NAMAS,
							resp[i].ALAMAT,
							resp[i].KOTA,
							resp[i].PKP2,
						]);
					}
					dTableBSuplier.draw();



				}
			});
		}

		dTableBSuplier = $("#table-bsuplier").DataTable({

		});

		browseSuplier = function(rid) {
			if ($("#KD_BRG" + rid).val() == '') {
				alert('Item masih kosong');
				$("#KODES" + rid).val('');
				return;
			}
			rowidBarang = rid;
			if (dTableBSuplier) {
				dTableBSuplier.clear();
			}
			loadDataBSuplier(rowidBarang);
			$("#browseSuplierModal").modal("show");
		}

		chooseSuplier = function(KODES, NAMAS, ALAMAT, KOTA, PKP) {
			var kodeNamas = (KODES == '' && NAMAS == '') ? "" : KODES + '-' + NAMAS;
			$("#KODES" + rowidBarang).val(kodeNamas);
			$("#NAMAS" + rowidBarang).val(NAMAS);
			$("#ALAMAT" + rowidBarang).val(ALAMAT);
			$("#KOTA" + rowidBarang).val(KOTA);
			$("#PKP" + rowidBarang).val(PKP);
			$("#browseSuplierModal").modal("hide");

		}



		$("#KODES").keypress(function(e) {

			if (e.keyCode == 46) {
				e.preventDefault();
				browseSuplier();
			}
		});

		//////////////////////////////////////////////////////

		var dTableBBarang;
		var rowidBarang;
		loadDataBBarang = function() {

			$.ajax({
				type: 'GET',
				url: "{{url('vbrg/browse_beli')}}",
				async: false,
				data: {
					'KD_BRG': $("#KD_BRG" + rowidBarang).val(),

				},
				success: function(response)

				{
					resp = response;


					if (resp.length > 1) {
						if (dTableBBarang) {
							dTableBBarang.clear();
						}
						for (i = 0; i < resp.length; i++) {

							var kodeNamas = (resp[i].KODES != '' && resp[i].NAMAS != '') ? resp[i].KODES + '-' + resp[i].NAMAS : '';

							dTableBBarang.row.add([
								'<a href="javascript:void(0);" onclick="chooseBarang(\'' + resp[i].KD_BRG + '\', \'' + resp[i].NA_BRG + '\' , \'' + resp[i].SATUAN + '\', \'' + resp[i].KODES + '\', \'' + resp[i].NAMAS + '\' )">' + resp[i].KD_BRG + '</a>',
								resp[i].NA_BRG,
								resp[i].SATUAN,
								kodeNamas,

							]);
						}
						dTableBBarang.draw();

					} else {

						var kodeNamas = (resp[0].KODES != '' && resp[0].NAMAS != '') ? resp[0].KODES + '-' + resp[0].NAMAS : '';


						$("#KD_BRG" + rowidBarang).val(resp[0].KD_BRG);
						$("#NA_BRG" + rowidBarang).val(resp[0].NA_BRG);
						$("#SATUAN" + rowidBarang).val(resp[0].SATUAN);
						$("#KODES" + rowidBarang).val(kodeNamas);
					}
				}
			});
		}

		dTableBBarang = $("#table-bbarang").DataTable({

		});

		browseBarang = function(rid) {
			rowidBarang = rid;
			$("#NA_BRG" + rowidBarang).val("");
			loadDataBBarang();


			if ($("#NA_BRG" + rowidBarang).val() == '') {
				$("#browseBarangModal").modal("show");
			}
		}

		chooseBarang = function(KD_BRG, NA_BRG, SATUAN, KODES, NAMAS) {
			var kodeNamas = (KODES != '' && NAMAS != '') ? KODES + '-' + NAMAS : '';

			$("#KD_BRG" + rowidBarang).val(KD_BRG);
			$("#NA_BRG" + rowidBarang).val(NA_BRG);
			$("#SATUAN" + rowidBarang).val(SATUAN);
			$("#KODES" + rowidBarang).val(kodeNamas);
			$("#NAMAS" + rowidBarang).val(NAMAS);
			$("#browseBarangModal").modal("hide");
		}

	});

	function getSod() {

			var mulai = (idrow == baris) ? idrow - 1 : idrow;

			$.ajax({
				type: 'GET',
				url: "{{url('khusus/ambil-detail')}}",
				data: {
					SUB1: $("#SUB1").val(),
					SUB2: $("#SUB2").val(),
					LPH1: $("#LPH1").val(),
					LPH2: $("#LPH2").val(),
					SUPP: $("#SUPP").val(),
				},
				success: function(resp) {
					var html = '';
					for (i = 0; i < resp.length; i++) {
						html += `<tr>
                                    <td><input name='REC[]' id='REC${i}' value=${resp[i].REC+1} type='text' class='REC form-control' onkeypress='return tabE(this,event)' readonly></td>
                                    <td><input name='KD_BRG[]' data-rowid=${i} id='KD_BRG${i}' value="${resp[i].KD_BRG}" type='text' class='form-control KD_BRG' readonly></td>
                                    <td><input name='NA_BRG[]' data-rowid=${i} id='NA_BRG${i}' value="${resp[i].NA_BRG}" type='text' class='form-control  NA_BRG' readonly></td>
                                    <td><input name='KET_KEM[]' data-rowid=${i} id='KET_KEM${i}' value="${resp[i].KET_KEM}" type='text' class='form-control  KET_KEM' placeholder="Satuan"  readonly></td>
                                    <td>
										<input name='QTY[]' onclick='select()' onkeyup='hitung()' id='QTY${i}' value="0" type='text' style='text-align: right' class='form-control QTY text-primary'>
									</td>

									<td>
										<input name='HARGA[]' onclick='select()' onkeyup='hitung()' id='HARGA${i}' value="${resp[i].HARGA}" type='text' style='text-align: right' class='form-control HARGA text-primary' readonly >
									</td>

									<td>
										<input name='TOTAL[]' onclick='select()' onkeyup='hitung()' id='TOTAL${i}' type='text' style='text-align: right' class='form-control TOTAL text-primary' readonly >
									</td>

									<td><input name='LPH[]' data-rowid=${i} id='LPH${i}' value="${resp[i].LPH}" type='text' class='form-control  LPH' placeholder="Satuan"  readonly></td>

									<td>
										<input name='STOK[]' onclick='select()' onkeyup='hitung()' id='STOK${i}' value="${resp[i].STOK}" type='text' style='text-align: right' class='form-control STOK text-primary' readonly >
									</td>

									<td>
										<input name='STOKZ[]' onclick='select()' onkeyup='hitung()' id='STOKZ${i}' value="${resp[i].STOKZ}" type='text' style='text-align: right' class='form-control STOKZ text-primary' readonly >
									</td>
									
                                    <td>
										<input name='KET[]' id='KET${i}' value="" type='text' class='form-control  KET'>
									</td>
                                    <td><button type='button' class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button></td>
                                </tr>`;
					}
					$('#detailSod').html(html);

					$(".QTY").autoNumeric('init', {
						aSign: '<?php echo ''; ?>',
						vMin: '-999999999.99'
					});
					$(".QTY").autoNumeric('update');

					$(".HARGA").autoNumeric('init', {
						aSign: '<?php echo ''; ?>',
						vMin: '-999999999.99'
					});
					$(".HARGA").autoNumeric('update');

					$(".TOTAL").autoNumeric('init', {
						aSign: '<?php echo ''; ?>',
						vMin: '-999999999.99'
					});
					$(".TOTAL").autoNumeric('update');

					$(".STOK").autoNumeric('init', {
						aSign: '<?php echo ''; ?>',
						vMin: '-999999999.99'
					});
					$(".STOK").autoNumeric('update');

					$(".STOKZ").autoNumeric('init', {
						aSign: '<?php echo ''; ?>',
						vMin: '-999999999.99'
					});
					$(".STOKZ").autoNumeric('update');


					idrow = resp.length;
					baris = resp.length;

					nomor();
					hitung();
				}
			});
		}



	///////////////////////////////////////




	function cekDetail() {
		var cekBarang = '';
		$(".KD_BRG").each(function() {

			let z = $(this).closest('tr');
			var KD_BRGX = z.find('.KD_BRG').val();

			if (KD_BRGX == "") {
				cekBarang = '1';
				// return false;

			}
		});

		return cekBarang;
	}


	function simpan() {
		// hitung();

		var tgl = $('#TGL').val();

		// var bulanPer = <?php echo session()->get('periode')['bulan']; ?>;
		// var tahunPer = <?php echo session()->get('periode')['tahun']; ?>;
		var bulanPer = {{session()->get('periode')['bulan']}};
		var tahunPer = {{session()->get('periode')['tahun']}};	



		var check = '0';

		if (cekDetail()) {
			check = '1';
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'Barang# Harus Diisi.'
			});
			return; // Stop function execution
		}


		if (tgl.substring(3, 5) != bulanPer) {
			check = '1';
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'Bulan tidak sama dengan Periode'
			});
			return; // Stop function execution
		}

		if (tgl.substring(tgl.length - 4) != tahunPer) {
			check = '1';
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'Tahun tidak sama dengan Periode'
			});
			return; // Stop function execution
		}

		if (baris == 0) {
			check = '1';
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'Data detail kosong (Tambahkan 1 baris kosong jika ingin mengosongi detail)'
			});
			return; // Stop function execution
		}

		if (check == '0') {
			Swal.fire({
				title: 'Are you sure?',
				text: 'Are you sure you want to save?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: 'Yes, save it!',
				cancelButtonText: 'No, cancel',
			}).then((result) => {
				if (result.isConfirmed) {
					document.getElementById("entri").submit();
				} else {
					Swal.fire({
						icon: 'info',
						title: 'Cancelled',
						text: 'Your data was not saved'
					});
				}
			});
		} else {
			Swal.fire({
				icon: 'error',
				title: 'Error',
				text: 'Masih ada kesalahan'
			});
		}

		// tutupannya

		$("#LOADX").hide();

	}

	function nomor() {
		var i = 1;
		$(".REC").each(function() {
			$(this).val(i++);
		});

		//	hitung();

	}

	function hitung() {
		var TTOTAL_QTY = 0;
		var TOTALX = 0;


		$(".QTY").each(function() {

			let z = $(this).closest('tr');
			var QTYX = parseFloat(z.find('.QTY').val().replace(/,/g, ''));
			var HARGAX = parseFloat(z.find('.HARGA').val().replace(/,/g, ''));

			TOTALX = QTYX * HARGAX;
			z.find('.TOTAL').val(numberWithCommas(TOTALX));
			z.find('.TOTAL').autoNumeric('update');

			z.find('.QTY').autoNumeric('update');
			z.find('.HARGA').autoNumeric('update');

			TTOTAL_QTY += QTYX;

		});

		// if (isNaN(TOTALX)) TOTALX = 0;
		// $('#TOTAL').val(numberWithCommas(TOTALX));
		// $("#TOTAL").autoNumeric('update');


		if (isNaN(TTOTAL_QTY)) TTOTAL_QTY = 0;

		$('#TTOTAL_QTY').val(numberWithCommas(TTOTAL_QTY));
		$("#TTOTAL_QTY").autoNumeric('update');
	}




	function baru() {

		kosong();
		hidup();

	}

	function ganti() {

		//  mati();
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

		$("#CARI").attr("readonly", true);
		$("#SEARCHX").attr("disabled", true);

		$("#PLUSX").attr("hidden", false)

		$("#NO_BUKTI").attr("readonly", true);
		$("#TGL").attr("readonly", false);
		$("#JTEMPO").attr("readonly", false);
		$("#KODES").attr("disabled", false);
		$("#NOTES").attr("readonly", false);
		$("#NOTES").attr("disabled", false);

		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#KD_BRG" + i.toString()).attr("readonly", false);
			$("#NA_BHN" + i.toString()).attr("readonly", true);
			$("#NA_BRG" + i.toString()).attr("readonly", true);
			$("#SATUAN" + i.toString()).attr("readonly", true);
			$("#QTY" + i.toString()).attr("readonly", false);
			$("#HARGA" + i.toString()).attr("readonly", false);
			$("#TOTAL" + i.toString()).attr("readonly", true);
			$("#DISK" + i.toString()).attr("readonly", false);
			$("#KET" + i.toString()).attr("readonly", false);
			$("#DELETEX" + i.toString()).attr("hidden", false);

			$tipx = $('#tipx').val();


			if ($tipx != 'new') {
				$("#KD_BHN" + i.toString()).attr("readonly", true);
				$("#KD_BHN" + i.toString()).removeAttr('onblur');

				$("#KD_BRG" + i.toString()).attr("readonly", true);
				$("#KD_BRG" + i.toString()).removeAttr('onblur');
			}
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

		$("#CARI").attr("readonly", false);
		$("#SEARCHX").attr("disabled", false);


		$("#PLUSX").attr("hidden", false)

		$("#NO_BUKTI").attr("readonly", true);

		$("#TGL").attr("readonly", true);
		$("#JTEMPO").attr("readonly", true);
		$("#KODES").attr("disabled", true);
		$("#NOTES").attr("readonly", true);
		$("#NOTES").attr("disabled", true);

		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#KD_BHN" + i.toString()).attr("readonly", true);
			$("#NA_BHN" + i.toString()).attr("readonly", true);
			$("#KD_BRG" + i.toString()).attr("readonly", true);
			$("#NA_BRG" + i.toString()).attr("readonly", true);
			$("#SATUAN" + i.toString()).attr("readonly", true);
			$("#QTY" + i.toString()).attr("readonly", true);
			$("#HARGA" + i.toString()).attr("readonly", true);
			$("#TOTAL" + i.toString()).attr("readonly", true);
			$("#DISK" + i.toString()).attr("readonly", true);
			$("#KET" + i.toString()).attr("readonly", true);

			$("#DELETEX" + i.toString()).attr("hidden", true);
		}



	}


	function kosong() {

		$('#NO_BUKTI').val("+");
		$('#KODES').val("");
		$('#NAMAS').val("");
		$('#TYPE').val("DC");
		$('#KIRIM').val("{{ Auth::user()->CBG }}");
		$('#ALAMAT').val("");
		$('#KOTA').val("");
		$('#NOTES').val("");
		$('#TTOTAL_QTY').val("0.00");
		$('#TTOTAL').val("0.00");
		$('#TDISK').val("0.00");
		$('#TPPN').val("0.00");
		$('#TDPP').val("0.00");
		$('#NETT').val("0.00");
		$('#TAHAP1').val("0.00");
		$('#TAHAP2').val("0.00");
		$('#TAHAP3').val("0.00");

		$('#PKP').val("0")
		$('#HARI').val("0")

		var html = '';
		$('#detailx').html(html);

	}


	// sweetalert untuk tombol hapus dan close

	function hapusTrans() {
		let text = "Hapus Transaksi " + $('#NO_BUKTI').val() + "?";

		var loc = '';
		var flagz = "{{ $flagz }}";

		Swal.fire({
			title: 'Are you sure?',
			text: text,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, delete it!',
			cancelButtonText: 'Cancel'
		}).then((result) => {
			if (result.isConfirmed) {
				// Show a success message before redirecting to delete the data
				Swal.fire({
					title: 'Deleted!',
					text: 'Data has been deleted.',
					icon: 'success',
					confirmButtonText: 'OK'
				}).then(() => {
					// Redirect to delete the data after user confirms the success message
					loc = "{{ url('/khusus/delete/'.$header->NO_ID) }}" + '?flagz=' + encodeURIComponent(flagz);

					// alert(loc);
					window.location = loc;

				});
			}
		});
	}

	function closeTrans() {
		console.log("masuk");
		var loc = '';
		var flagz = "{{ $flagz }}";

		Swal.fire({
			title: 'Are you sure?',
			text: 'Do you really want to close this page? Unsaved changes will be lost.',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes, close it',
			cancelButtonText: 'No, stay here'
		}).then((result) => {
			if (result.isConfirmed) {
				loc = "{{ url('/khusus/') }}" + '?flagz=' + encodeURIComponent(flagz);
				window.location = loc;
			} else {
				Swal.fire({
					icon: 'info',
					title: 'Cancelled',
					text: 'You stayed on the page'
				});
			}
		});
	}

	// tutupannya


	function CariBukti() {

		var flagz = "{{ $flagz }}";
		var cari = $("#CARI").val();
		var loc = "{{ url('/khusus/edit/') }}" + '?idx={{ $header->NO_ID}}&tipx=search&flagz=' + encodeURIComponent(flagz) + '&buktix=' + encodeURIComponent(cari);
		window.location = loc;

	}


	function tambah() {

		var x = document.getElementById('datatable').insertRow(baris + 1);

		html = `<tr>

                <td>
 					<input name='NO_ID[]' id='NO_ID${idrow}' type='hidden' class='form-control NO_ID' value='new' readonly>
					<input name='REC[]' id='REC${idrow}' type='text' class='REC form-control' onkeypress='return tabE(this,event)' readonly>
	            </td>

				<td>
				    <input name='KD_BRG[]' data-rowid=${idrow} onblur='browseBarang(${idrow})' id='KD_BRG${idrow}' type='text' class='form-control  KD_BRG' >
				</td>

                <td>
				    <input name='NA_BRG[]'   id='NA_BRG${idrow}' type='text' class='form-control  NA_BRG' required readonly>
                </td>

                <td>
				    <input name='KET_KEM[]'   id='KET_KEM${idrow}' type='text' class='form-control  KET_KEM' readonly required>
                </td>

				<td>
		            <input name='QTY[]' onclick='select()' onblur='hitung()' value='0' id='QTY${idrow}' type='text' style='text-align: right' class='form-control QTY text-primary' required >
                </td>

				<td>
		            <input name='HARGA[]' onclick='select()' onblur='hitung()' value='0' id='HARGA${idrow}' type='text' style='text-align: right' class='form-control HARGA text-primary' required >
                </td>

				<td>
		            <input name='TOTAL[]' onclick='select()' onblur='hitung()' value='0' id='TOTAL${idrow}' type='text' style='text-align: right' class='form-control TOTAL text-primary' required >
                </td>

				<td>
				    <input name='LPH[]'   id='LPH${idrow}' type='text' class='form-control  LPH' required readonly>
                </td>

				<td>
		            <input name='GAK00[]' onclick='select()' onblur='hitung()' value='0' id='GAK00${idrow}' type='text' style='text-align: right' class='form-control GAK00 text-primary' required >
                </td>

				<td>
		            <input name='AK00[]' onclick='select()' onblur='hitung()' value='0' id='AK00${idrow}' type='text' style='text-align: right' class='form-control AK00 text-primary' required >
                </td>

				<td>
				    <input name='KET[]'   id='KET${idrow}' type='text' class='form-control  KET' required>
                </td>

                <td>
					<button type='button' id='DELETEX${idrow}'  class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
                </td>
         </tr>`;

		x.innerHTML = html;
		var html = '';



		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#QTY" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});
			$("#HARGA" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});
			$("#TOTAL" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});

		}


		idrow++;
		baris++;
		hitung();
		nomor();

		$(".ronly").on('keydown paste', function(e) {
			e.preventDefault();
			e.currentTarget.blur();
		});
	}
</script>
<!--
<script src="autonumeric.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.5.4"></script>
<script src="https://unpkg.com/autonumeric"></script> -->
@endsection