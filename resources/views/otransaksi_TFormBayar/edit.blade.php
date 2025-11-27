@extends('layouts.plain')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
	.card {
		padding: 5px 10px !important;
	}

	.form-control:focus {
		background-color: #b5e5f9 !important;
	}

	/* Input field styling */
	.form-control {
		border: 1px solid #ccc;
		border-radius: 4px;
		padding: 8px;
		font-size: 14px;
	}

	/* Header section styling */
	.header-section {
		background-color: #f8b4b4;
		padding: 15px;
		border-radius: 8px;
		margin-bottom: 15px;
	}

	.header-section .form-group {
		margin-bottom: 10px;
	}

	.header-section label {
		font-weight: bold;
		color: #333;
		margin-bottom: 5px;
		display: block;
	}

	/* Table styling to match the image */
	.detail-table {
		margin-top: 15px;
	}

	.detail-table th {
		background-color: #e8e8e8;
		text-align: center;
		font-weight: bold;
		font-size: 12px;
		padding: 8px 4px;
		border: 1px solid #ccc;
	}

	.detail-table td {
		padding: 4px;
		border: 1px solid #ccc;
		font-size: 12px;
	}

	.detail-table input {
		border: none;
		padding: 4px;
		font-size: 12px;
		width: 100%;
		text-align: center;
	}

	.detail-table input[readonly] {
		background-color: transparent;
	}

	/* Button styling */
	.btn-action {
		margin: 2px;
		padding: 8px 16px;
		font-size: 14px;
		border-radius: 4px;
	}

	/* Loader styling */
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

	/* Remove padding */
	.content-header {
		padding: 0 !important;
	}
</style>

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<h4>{{ $judul ?? 'Instruksi Pembayaran' }}</h4>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form
									action="{{ $tipx == 'new' ? url('/tformbayar/store?flagz=' . $flagz . '') : url('/tformbayar/update/' . $header->NO_ID . '&flagz=' . $flagz . '') }}"
									method="POST" name="entri" id="entri">
									@csrf

									<!-- Header Section -->
									<div class="header-section">
										<!-- Hidden Fields -->
										<input type="hidden" class="form-control NO_ID" id="NO_ID" name="NO_ID" value="{{ $header->NO_ID ?? '' }}" readonly>
										<input name="tipx" class="form-control tipx" id="tipx" value="{{ $tipx ?? 'edit' }}" hidden>
										<input name="flagz" class="form-control flagz" id="flagz" value="{{ $flagz ?? 'TB' }}" hidden> <!-- First Row -->
										<div class="row">
											<!-- No Bukti -->
											<div class="col-md-2">
												<label for="NO_BUKTI">No Bukti</label>
												<input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI" value="{{ $header->NO_BUKTI ?? '' }}" readonly
													style="background-color: #e9ecef;">
											</div>

											<!-- Transfer Ke -->
											<div class="col-md-3">
												<label for="TRANSFER_KE">Transfer Ke :</label>
												<input type="text" class="form-control" id="TRANSFER_KE" name="TRANSFER_KE" value="{{ $header->TRANSFER_KE ?? '' }}"
													placeholder="Transfer ke">
											</div>

											<!-- Supplier -->
											<div class="col-md-2">
												<label for="KODES">Supplier</label>
												<select id="KODES" name="KODES" class="form-control" style="width: 100%"></select>
												<input type="hidden" class="form-control NAMAS" id="NAMAS" name="NAMAS" value="{{ $header->NAMAS ?? '' }}">
											</div>

											<!-- Posted Checkbox -->
											<div class="col-md-1">
												<div class="form-check" style="margin-top: 25px;">
													<input type="checkbox" class="form-check-input" id="POSTED" name="POSTED" value="1"
														{{ ($header->POSTED ?? 0) == 1 ? 'checked' : '' }} readonly>
													<label class="form-check-label" for="POSTED">Posted</label>
												</div>
											</div>
										</div>

										<!-- Second Row -->
										<div class="row mt-3">
											<!-- Tanggal -->
											<div class="col-md-2">
												<label for="TGL">Tanggal</label>
												<input class="form-control date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off"
													value="{{ date('d-m-Y', strtotime($header->TGL ?? date('Y-m-d'))) }}">
											</div>

											<!-- Type -->
											<div class="col-md-2">
												<label for="TYPE">Type</label>
												<select class="form-control" id="TYPE" name="TYPE">
													<option value="ALL" {{ ($header->TYPE ?? '') == 'ALL' ? 'selected' : '' }}>ALL</option>
													<option value="TRF" {{ ($header->TYPE ?? '') == 'TRF' ? 'selected' : '' }}>Transfer</option>
													<option value="CASH" {{ ($header->TYPE ?? '') == 'CASH' ? 'selected' : '' }}>Cash</option>
												</select>
											</div>

											<!-- Bank -->
											<div class="col-md-2">
												<label for="BANK">Bank :</label>
												<input type="text" class="form-control" id="BANK" name="BANK" value="{{ $header->BANK ?? '' }}" placeholder="Bank">
											</div>
										</div>

										<!-- Third Row -->
										<div class="row mt-3">
											<!-- Pembayaran -->
											<div class="col-md-3">
												<label for="PEMBAYARAN">Pembayaran :</label>
												<input type="text" class="form-control" id="PEMBAYARAN" name="PEMBAYARAN" value="{{ $header->PEMBAYARAN ?? '' }}"
													placeholder="Pembayaran">
											</div>

											<!-- Cara Bayar -->
											<div class="col-md-2">
												<label for="CARA_BAYAR">Cara Bayar</label>
												<input type="text" class="form-control" id="CARA_BAYAR" name="CARA_BAYAR" value="{{ $header->CARA_BAYAR ?? '' }}"
													placeholder="Cara Bayar">
											</div>

											<!-- KLB -->
											<div class="col-md-2">
												<label for="KLB">KLB</label>
												<input type="number" class="form-control" id="KLB" name="KLB" value="{{ $header->KLB ?? 0 }}" placeholder="0">
											</div>
										</div>

										<!-- Fourth Row -->
										<div class="row mt-3">
											<!-- Notes -->
											<div class="col-md-6">
												<label for="NOTES">Notes</label>
												<textarea class="form-control" id="NOTES" name="NOTES" rows="3" placeholder="Notes">{{ $header->NOTES ?? '' }}</textarea>
											</div>

											<!-- No Tanda Terima -->
											<div class="col-md-3">
												<label for="NO_TANDA_TERIMA">No.Tanda Terima</label>
												<input type="text" class="form-control" id="NO_TANDA_TERIMA" name="NO_TANDA_TERIMA" value="{{ $header->NO_TANDA_TERIMA ?? '' }}"
													placeholder="No. Tanda Terima">
											</div>
										</div>
									</div>

									<!-- Detail Table -->
									<div class="detail-table">
										<table id="datatable" class="table-striped table-bordered table">
											<thead>
												<tr>
													<th width="40px">Nu</th>
													<th width="80px">No. Ag</th>
													<th width="100px">Tgl</th>
													<th width="100px">No. Sp</th>
													<th width="120px">Nilai Nota</th>
													<th width="120px">Faktur Pajak</th>
													<th width="100px">Pot / Disc</th>
													<th width="120px">Nilai Terima</th>
													<th width="150px">Keterangan</th>
													<th width="40px"></th>
												</tr>
											</thead>
											<tbody>
												<?php $no = 0; ?>
												@if (isset($detail) && count($detail) > 0)
													@foreach ($detail as $detailItem)
														<tr>
															<td>
																<input type="hidden" name="NO_ID_DETAIL[]" value="{{ $detailItem->NO_ID }}">
																<input name="REC[]" id="REC{{ $no }}" type="text" value="{{ $detailItem->REC ?? $no + 1 }}"
																	class="form-control REC" readonly style="text-align:center; background-color: transparent;">
															</td>
															<td>
																<input name="NO_AG[]" id="NO_AG{{ $no }}" type="text" value="{{ $detailItem->NO_AG ?? '' }}"
																	class="form-control NO_AG" placeholder="No. Ag">
															</td>
															<td>
																<input name="TGL_DETAIL[]" id="TGL_DETAIL{{ $no }}" type="date" value="{{ $detailItem->TGL ?? '' }}"
																	class="form-control TGL_DETAIL">
															</td>
															<td>
																<input name="NO_SP[]" id="NO_SP{{ $no }}" type="text" value="{{ $detailItem->NO_SP ?? '' }}"
																	class="form-control NO_SP" placeholder="No. Sp">
															</td>
															<td>
																<input name="NILAI_NOTA[]" id="NILAI_NOTA{{ $no }}" type="text" value="{{ $detailItem->NILAI_NOTA ?? 0 }}"
																	class="form-control NILAI_NOTA" style="text-align: right" onblur="hitung()">
															</td>
															<td>
																<input name="FAKTUR_PAJAK[]" id="FAKTUR_PAJAK{{ $no }}" type="text" value="{{ $detailItem->FAKTUR_PAJAK ?? 0 }}"
																	class="form-control FAKTUR_PAJAK" style="text-align: right" onblur="hitung()">
															</td>
															<td>
																<input name="POT_DISC[]" id="POT_DISC{{ $no }}" type="text" value="{{ $detailItem->POT_DISC ?? 0 }}"
																	class="form-control POT_DISC" style="text-align: right" onblur="hitung()">
															</td>
															<td>
																<input name="NILAI_TERIMA[]" id="NILAI_TERIMA{{ $no }}" type="text" value="{{ $detailItem->NILAI_TERIMA ?? 0 }}"
																	class="form-control NILAI_TERIMA" style="text-align: right" readonly>
															</td>
															<td>
																<input name="KETERANGAN[]" id="KETERANGAN{{ $no }}" type="text" value="{{ $detailItem->KETERANGAN ?? '' }}"
																	class="form-control KETERANGAN" placeholder="Keterangan">
															</td>
															<td>
																<button type='button' id='DELETEX{{ $no }}' class='btn btn-sm btn-outline-danger btn-delete'>
																	<i class='fa fa-fw fa-trash'></i>
																</button>
															</td>
														</tr>
														<?php $no++; ?>
													@endforeach
												@else
													<!-- Empty row for new records -->
													<tr>
														<td>
															<input type="hidden" name="NO_ID_DETAIL[]" value="new">
															<input name="REC[]" id="REC0" type="text" value="1" class="form-control REC" readonly
																style="text-align:center; background-color: transparent;">
														</td>
														<td>
															<input name="NO_AG[]" id="NO_AG0" type="text" value="" class="form-control NO_AG" placeholder="No. Ag">
														</td>
														<td>
															<input name="TGL_DETAIL[]" id="TGL_DETAIL0" type="date" value="" class="form-control TGL_DETAIL">
														</td>
														<td>
															<input name="NO_SP[]" id="NO_SP0" type="text" value="" class="form-control NO_SP" placeholder="No. Sp">
														</td>
														<td>
															<input name="NILAI_NOTA[]" id="NILAI_NOTA0" type="text" value="0" class="form-control NILAI_NOTA"
																style="text-align: right" onblur="hitung()">
														</td>
														<td>
															<input name="FAKTUR_PAJAK[]" id="FAKTUR_PAJAK0" type="text" value="0" class="form-control FAKTUR_PAJAK"
																style="text-align: right" onblur="hitung()">
														</td>
														<td>
															<input name="POT_DISC[]" id="POT_DISC0" type="text" value="0" class="form-control POT_DISC" style="text-align: right"
																onblur="hitung()">
														</td>
														<td>
															<input name="NILAI_TERIMA[]" id="NILAI_TERIMA0" type="text" value="0" class="form-control NILAI_TERIMA"
																style="text-align: right" readonly>
														</td>
														<td>
															<input name="KETERANGAN[]" id="KETERANGAN0" type="text" value="" class="form-control KETERANGAN"
																placeholder="Keterangan">
														</td>
														<td>
															<button type='button' id='DELETEX0' class='btn btn-sm btn-outline-danger btn-delete'>
																<i class='fa fa-fw fa-trash'></i>
															</button>
														</td>
													</tr>
													<?php $no = 1; ?>
												@endif
											</tbody>
											<tfoot>
												<tr>
													<td colspan="4" style="text-align: right; font-weight: bold;">Total:</td>
													<td><input class="form-control" style="text-align: right; font-weight: bold;" id="TOTAL_NILAI_NOTA" readonly></td>
													<td><input class="form-control" style="text-align: right; font-weight: bold;" id="TOTAL_FAKTUR_PAJAK" readonly></td>
													<td><input class="form-control" style="text-align: right; font-weight: bold;" id="TOTAL_POT_DISC" readonly></td>
													<td><input class="form-control" style="text-align: right; font-weight: bold;" id="TOTAL_NILAI_TERIMA" readonly></td>
													<td colspan="2"></td>
												</tr>
											</tfoot>
										</table>

										<div class="col-md-2 row mt-2">
											<button type="button" id='PLUSX' onclick="tambah()" class="btn btn-primary btn-sm">
												<i class="fas fa-plus"></i> Add Row
											</button>
										</div>
									</div>

									<!-- Action Buttons -->
									<hr style="margin-top: 30px; margin-bottom: 30px">
									<div class="col-md-12 form-group row mt-3">
										<div class="col-md-4">
											<button type="button" id='TOPX'
												onclick="location.href='{{ url('/tformbayar/edit/?idx=' . ($idx ?? 0) . '&tipx=top&flagz=' . ($flagz ?? 'TB') . '') }}'"
												class="btn btn-outline-primary btn-action" style="display:none;">Top</button>
											<button type="button" id='PREVX'
												onclick="location.href='{{ url('/tformbayar/edit/?idx=' . ($header->NO_ID ?? 0) . '&tipx=prev&flagz=' . ($flagz ?? 'TB') . '&buktix=' . ($header->NO_BUKTI ?? '')) }}'"
												class="btn btn-outline-primary btn-action" style="display:none;">Prev</button>
											<button type="button" id='NEXTX'
												onclick="location.href='{{ url('/tformbayar/edit/?idx=' . ($header->NO_ID ?? 0) . '&tipx=next&flagz=' . ($flagz ?? 'TB') . '&buktix=' . ($header->NO_BUKTI ?? '')) }}'"
												class="btn btn-outline-primary btn-action" style="display:none;">Next</button>
											<button type="button" id='BOTTOMX'
												onclick="location.href='{{ url('/tformbayar/edit/?idx=' . ($idx ?? 0) . '&tipx=bottom&flagz=' . ($flagz ?? 'TB') . '') }}'"
												class="btn btn-outline-primary btn-action" style="display:none;">Bottom</button>
										</div>
										<div class="col-md-5">
											<button type="button" id='NEWX'
												onclick="location.href='{{ url('/tformbayar/edit/?idx=0&tipx=new&flagz=' . ($flagz ?? 'TB') . '') }}'"
												class="btn btn-warning btn-action" style="display:none;">New</button>
											<button type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary btn-action" style="display:none;">Edit</button>
											<button type="button" id='UNDOX'
												onclick="location.href='{{ url('/tformbayar/edit/?idx=' . ($idx ?? 0) . '&tipx=undo&flagz=' . ($flagz ?? 'TB') . '') }}'"
												class="btn btn-info btn-action" style="display:none;">Undo</button>
											<button type="button" id='SAVEX' onclick='simpan()' class="btn btn-success btn-action">
												<i class="fa fa-save"></i> Save
											</button>
										</div>
										<div class="col-md-3">
											<button type="button" id='HAPUSX' onclick="hapusTrans()" class="btn btn-outline-danger btn-action"
												style="display:none;">Hapus</button>
											<button type="button" id='CLOSEX' onclick="closeTrans()" class="btn btn-outline-secondary btn-action">Close</button>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Loader -->
		<div class="loader" style="z-index: 1055; display: none;" id='LOADX'></div>
	</div>

@section('footer-scripts')
	<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
	<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> -->
	<script src="{{ asset('foxie_js_css/bootstrap.bundle.min.js') }}"></script>

	<!-- tambahan untuk sweetalert -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<!-- tutupannya -->

	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

			idrow = {{ count($detail) > 0 ? count($detail) : 1 }};
			baris = {{ count($detail) > 0 ? count($detail) : 1 }};

			$('#KODES').select2({

				placeholder: 'Pilih Suplier',
				allowClear: true,
				ajax: {
					url: '{{ url('zsup/browse') }}',
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
								id: item.KODES, // The ID of the user
								text: item.NAMAS2 // The text to display
							}))
						};
					},
					cache: true
				},



			});

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
						// Remove remaining commented code references
						// console.log("KD_BRG"+nomor);
						// document.getElementById("KD_BRG"+nomor).focus();
						// form.submit();
					}
					return false;
				}
			});


			$tipx = $('#tipx').val();
			$searchx = $('#CARI').val();


			if ($tipx == 'new') {
				baru();
				tambah();
			}

			if ($tipx != 'new') {
				ganti();

				var initkode1 = "{{ $header->KODES }}";
				var initcombo1 = "{{ $header->NAMAS }}";
				var defaultOption1 = {
					id: initkode1,
					text: initcombo1
				}; // Set your default option ID and text
				var newOption1 = new Option(defaultOption1.text, defaultOption1.id, true, true);
				$('#KODES').append(newOption1).trigger('change');
			}

			// Initialize autoNumeric for payment instruction totals
			$("#TOTAL_NILAI_NOTA").autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});

			$("#TOTAL_FAKTUR_PAJAK").autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});

			$("#TOTAL_POT_DISC").autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});

			$("#TOTAL_NILAI_TERIMA").autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});

			// Initialize autoNumeric for detail fields when they are created
			$(document).on('focus', '.NILAI_NOTA', function() {
				if (!$(this).hasClass('autonumeric')) {
					$(this).autoNumeric('init', {
						aSign: '<?php echo ''; ?>',
						vMin: '-999999999.99'
					});
					$(this).addClass('autonumeric');
				}
			});

			$(document).on('focus', '.FAKTUR_PAJAK', function() {
				if (!$(this).hasClass('autonumeric')) {
					$(this).autoNumeric('init', {
						aSign: '<?php echo ''; ?>',
						vMin: '-999999999.99'
					});
					$(this).addClass('autonumeric');
				}
			});

			$(document).on('focus', '.POT_DISC', function() {
				if (!$(this).hasClass('autonumeric')) {
					$(this).autoNumeric('init', {
						aSign: '<?php echo ''; ?>',
						vMin: '-999999999.99'
					});
					$(this).addClass('autonumeric');
				}
			});

			$(document).on('focus', '.NILAI_TERIMA', function() {
				if (!$(this).hasClass('autonumeric')) {
					$(this).autoNumeric('init', {
						aSign: '<?php echo ''; ?>',
						vMin: '-999999999.99'
					});
					$(this).addClass('autonumeric');
				}
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

			// Initialize select2 for supplier dropdown
			$('#KODES').select2({
				placeholder: 'Pilih Supplier',
				allowClear: true
			});

			//		CHOOSE Supplier - Simplified for payment instruction
			var dTableBSuplier;
			loadDataBSuplier = function() {
				$.ajax({
					type: 'GET',
					url: '{{ url('sup/browse') }}',
					beforeSend: function() {
						$("#LOADX").show();
					},
					success: function(response) {
						$("#LOADX").hide();
						resp = response;
						if (dTableBSuplier) {
							dTableBSuplier.clear();
						}
						for (i = 0; i < resp.length; i++) {
							dTableBSuplier.row.add([
								'<a href="javascript:void(0);" onclick="chooseSuplier(\'' + resp[i].KODES + '\',  \'' + resp[
									i].NAMAS + '\', \'' + resp[i].HARI + '\',  \'' + resp[i].ALAMAT + '\', \'' + resp[i]
								.KOTA + '\', \'' + resp[i].PKP + '\')">' + resp[i].KODES + '</a>',
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

			dTableBSuplier = $("#table-bsuplier").DataTable({});

			browseSuplier = function() {
				loadDataBSuplier();
				$("#browseSuplierModal").modal("show");
			}

			chooseSuplier = function(KODES, NAMAS, HARI, ALAMAT, KOTA, PKP) {
				$("#KODES").val(KODES);
				$("#NAMAS").val(NAMAS);
				$("#HARI").val(HARI);
				$("#ALAMAT").val(ALAMAT);
				$("#KOTA").val(KOTA);
				$("#PKP").val(PKP);
				$("#browseSuplierModal").modal("hide");
			}

			$("#KODES").keypress(function(e) {
				if (e.keyCode == 46) {
					e.preventDefault();
					browseSuplier();
				}
			});

		});



		///////////////////////////////////////




		function cekDetail() {
			var cekDetail = '';
			$(".NO_AG").each(function() {
				let z = $(this).closest('tr');
				var NO_AGX = z.find('.NO_AG').val();

				if (NO_AGX == "") {
					cekDetail = '1';
				}
			});

			return cekDetail;
		}


		function simpan() {
			hitung();

			var tgl = $('#TGL').val();
			var bulanPer = {{ session()->get('periode')['bulan'] ?? date('n') }};
			var tahunPer = {{ session()->get('periode')['tahun'] ?? date('Y') }};

			var check = '0';

			if (baris == 0) {
				check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Data detail kosong (Tambahkan 1 baris kosong jika ingin mengosongi detail)'
				});
				return; // Stop function execution
			}

			if (tgl.substring(3, 5) != bulanPer.toString().padStart(2, '0')) {
				check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Bulan tidak sama dengan Periode'
				});
				return; // Stop function execution
			}

			if (tgl.substring(tgl.length - 4) != tahunPer.toString()) {
				check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Tahun tidak sama dengan Periode'
				});
				return; // Stop function execution
			}

			if ($('#KODES').val() == '') {
				check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Supplier Harus Diisi.'
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
			var totalNilaiNota = 0;
			var totalFakturPajak = 0;
			var totalPotDisc = 0;
			var totalNilaiTerima = 0;

			$(".NILAI_NOTA").each(function() {
				let z = $(this).closest('tr');
				var nilaiNota = parseFloat(z.find('.NILAI_NOTA').val().replace(/,/g, '')) || 0;
				var fakturPajak = parseFloat(z.find('.FAKTUR_PAJAK').val().replace(/,/g, '')) || 0;
				var potDisc = parseFloat(z.find('.POT_DISC').val().replace(/,/g, '')) || 0;

				var nilaiTerima = nilaiNota + fakturPajak - potDisc;
				z.find('.NILAI_TERIMA').val(numberWithCommas(nilaiTerima.toFixed(2)));

				totalNilaiNota += nilaiNota;
				totalFakturPajak += fakturPajak;
				totalPotDisc += potDisc;
				totalNilaiTerima += nilaiTerima;
			});

			$('#TOTAL_NILAI_NOTA').val(numberWithCommas(totalNilaiNota.toFixed(2)));
			$('#TOTAL_FAKTUR_PAJAK').val(numberWithCommas(totalFakturPajak.toFixed(2)));
			$('#TOTAL_POT_DISC').val(numberWithCommas(totalPotDisc.toFixed(2)));
			$('#TOTAL_NILAI_TERIMA').val(numberWithCommas(totalNilaiTerima.toFixed(2)));
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

			// alert($header[0]->NO_BUKTI);

			//$('#NO_BUKTI').val($header[0]->NO_BUKTI);
			mati();

		}



		function ambil_hari() {


			$.ajax({
				type: 'GET',
				url: "{{ url('sup/browse_hari') }}",
				data: {
					'KODES': $("#KODES").val(),
				},

				success: function(response)

				{
					resp = response;
					$("#NAMAS").val(resp[0].NAMAS);
					$("#PKP").val(resp[0].PKP);
					$("#HARI").val(resp[0].HARI);

					if ($("#PKP").val() == '1') {

						document.getElementById("PKP").checked = true;

					} else {
						document.getElementById("PKP").checked = false;

					}

				}
			});



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

			$("#PLUSX").attr("hidden", false);

			$("#NO_BUKTI").attr("readonly", true);
			$("#TGL").attr("readonly", false);
			$("#NOTES").attr("readonly", false);
			$("#KODES").attr("disabled", false);

			// Enable payment instruction fields
			$("#TRANSFER_KE").attr("readonly", false);
			$("#BANK").attr("readonly", false);
			$("#PEMBAYARAN").attr("readonly", false);
			$("#CARA_BAYAR").attr("readonly", false);
			$("#KLB").attr("readonly", false);
			$("#NO_TANDA_TERIMA").attr("readonly", false);
			$("#TYPE").attr("disabled", false);

			// Enable detail fields
			$(".NO_AG").attr("readonly", false);
			$(".TGL_DETAIL").attr("readonly", false);
			$(".NO_SP").attr("readonly", false);
			$(".NILAI_NOTA").attr("readonly", false);
			$(".FAKTUR_PAJAK").attr("readonly", false);
			$(".POT_DISC").attr("readonly", false);
			$(".KETERANGAN").attr("readonly", false);
			$(".btn-delete").attr("hidden", false);
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

			$("#PLUSX").attr("hidden", true);

			$(".NO_BUKTI").attr("readonly", true);
			$("#TGL").attr("readonly", true);
			$("#NOTES").attr("readonly", true);
			$("#KODES").attr("disabled", true);

			// Disable payment instruction fields
			$("#TRANSFER_KE").attr("readonly", true);
			$("#BANK").attr("readonly", true);
			$("#PEMBAYARAN").attr("readonly", true);
			$("#CARA_BAYAR").attr("readonly", true);
			$("#KLB").attr("readonly", true);
			$("#NO_TANDA_TERIMA").attr("readonly", true);
			$("#TYPE").attr("disabled", true);

			// Disable detail fields
			$(".NO_AG").attr("readonly", true);
			$(".TGL_DETAIL").attr("readonly", true);
			$(".NO_SP").attr("readonly", true);
			$(".NILAI_NOTA").attr("readonly", true);
			$(".FAKTUR_PAJAK").attr("readonly", true);
			$(".POT_DISC").attr("readonly", true);
			$(".KETERANGAN").attr("readonly", true);
			$(".btn-delete").attr("hidden", true);
		}


		function kosong() {
			$('#CARI').val("");
			$('#NO_BUKTI').val("");
			$('#TGL').val("{{ date('Y-m-d') }}");
			$('#PERIODE').val("");
			$('#NOTES').val("");
			$('#TYPE').val("K");

			// Clear payment instruction header fields
			$('#TRANSFER_KE').val("");
			$('#BANK').val("");
			$('#PEMBAYARAN').val("");
			$('#CARA_BAYAR').val("");
			$('#KLB').val("");
			$('#NO_TANDA_TERIMA').val("");

			$('#TOTALX').val("0.00");
			$('#TOTAL_PAJAK').val("0.00");
			$('#TOTAL_TERIMA').val("0.00");

			var html = '<tbody>';
			html += '<tr>';
			html +=
				'<td style="border-left: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;" align="center">';
			html += '<input name="REC[]" id="REC0" value="1" type="text" style="text-align: center; width: 40px;" readonly>';
			html += '</td>';
			html += '<td>';
			html += '<input name="NO_AG[]" id="NO_AG0" type="text" style="width: 120px;" placeholder="No Ag" />';
			html += '</td>';
			html += '<td>';
			html += '<input name="TGL_DETAIL[]" id="TGL_DETAIL0" type="date" style="width: 120px;" />';
			html += '</td>';
			html += '<td>';
			html += '<input name="NO_SP[]" id="NO_SP0" type="text" style="width: 120px;" placeholder="No SP" />';
			html += '</td>';
			html += '<td>';
			html += '<input name="NILAI_NOTA[]" id="NILAI_NOTA0" type="text" style="width: 120px; text-align: right;" value="0.00" onblur="hitung();" />';
			html += '</td>';
			html += '<td>';
			html +=
				'<input name="FAKTUR_PAJAK[]" id="FAKTUR_PAJAK0" type="text" style="width: 120px; text-align: right;" value="0.00" onblur="hitung();" />';
			html += '</td>';
			html += '<td>';
			html += '<input name="POT_DISC[]" id="POT_DISC0" type="text" style="width: 120px; text-align: right;" value="0.00" onblur="hitung();" />';
			html += '</td>';
			html += '<td>';
			html += '<input name="NILAI_TERIMA[]" id="NILAI_TERIMA0" type="text" style="width: 120px; text-align: right;" value="0.00" readonly />';
			html += '</td>';
			html += '<td>';
			html += '<input name="KETERANGAN[]" id="KETERANGAN0" type="text" style="width: 150px;" placeholder="Keterangan" />';
			html += '</td>';
			html += '<td>';
			html += '<button type="button" class="btn btn-sm btn-circle btn-outline-danger btn-delete" id="DELETEX0" onclick="hapusDetail(0);">';
			html += '<i class="fa fa-fw fa-trash"></i>';
			html += '</button>';
			html += '</td>';
			html += '</tr>';
			html += '</tbody>';
			$('#detailGrid tbody').html(html);

			idrow = 1;
		}

		function hapusDetail(row) {
			$('#detailGrid tbody tr:eq(' + row + ')').remove();
			nomor();
			hitung();
		}

		function nomor() {
			var i = 1;
			$("#detailGrid tbody tr").each(function() {
				$(this).find('td:first-child input').val(i);
				i++;
			});
		}

		// function hapusTrans() {
		// 	let text = "Hapus Transaksi "+$('#NO_BUKTI').val()+"?";
		// 	if (confirm(text) == true)
		// 	{
		// 		window.location ="{{ url('/hdh/delete/' . $header->NO_ID . '/?flagz=' . $flagz . '') }}";
		// 		//return true;
		// 	}
		// 	return false;
		// }

		// sweetalert untuk tombol hapus dan close

		function hapusTrans() {
			let text = "Hapus Transaksi " + $('#NO_BUKTI').val() + "?";

			var loc = '';
			var flagz = "{{ $flagz ?? 'TB' }}";

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
						loc = "{{ url('/tformbayar/delete/' . $header->NO_ID) }}" + '?flagz=' + encodeURIComponent(flagz);

						// alert(loc);
						window.location = loc;

					});
				}
			});
		}

		function closeTrans() {
			console.log("masuk");
			var loc = '';
			var flagz = "{{ $flagz ?? 'TB' }}";

			Swal.fire({
				title: 'Are you sure?',
				text: 'Do you really want to close this page? Unsaved changes will be lost.',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Yes, close it',
				cancelButtonText: 'No, stay here'
			}).then((result) => {
				if (result.isConfirmed) {
					loc = "{{ url('/tformbayar/') }}" + '?flagz=' + encodeURIComponent(flagz);
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

			var flagz = "{{ $flagz ?? 'TB' }}";
			var cari = $("#CARI").val();
			var loc = "{{ url('/tformbayar/edit/') }}" + '?idx={{ $header->NO_ID ?? 0 }}&tipx=search&flagz=' + encodeURIComponent(flagz) + '&buktix=' +
				encodeURIComponent(cari);
			window.location = loc;

		}


		function tambah() {

			var x = document.getElementById('datatable').insertRow(baris + 1);

			html = `<tr>
                <td>
 					<input name='NO_ID_DETAIL[]' id='NO_ID_DETAIL${idrow}' type='hidden' class='form-control NO_ID_DETAIL' value='new' readonly>
					<input name='REC[]' id='REC${idrow}' type='text' class='REC form-control' value='${idrow + 1}' readonly style='text-align:center; background-color: transparent;'>
	            </td>
                <td>
				    <input name='NO_AG[]' id='NO_AG${idrow}' type='text' class='form-control NO_AG' placeholder='No. Ag'>
                </td>
                <td>
				    <input name='TGL_DETAIL[]' id='TGL_DETAIL${idrow}' type='date' class='form-control TGL_DETAIL'>
                </td>
                <td>
				    <input name='NO_SP[]' id='NO_SP${idrow}' type='text' class='form-control NO_SP' placeholder='No. Sp'>
                </td>
				<td>
		            <input name='NILAI_NOTA[]' onclick='select()' onblur='hitung()' value='0' id='NILAI_NOTA${idrow}' type='text' style='text-align: right' class='form-control NILAI_NOTA text-primary'>
                </td>
 				<td>
 	            	<input name='FAKTUR_PAJAK[]' onclick='select()' onblur='hitung()' value='0' id='FAKTUR_PAJAK${idrow}' type='text' style='text-align: right' class='form-control FAKTUR_PAJAK text-primary'>
             	</td>
				<td>
					<input name='POT_DISC[]' onblur='hitung()' value='0' id='POT_DISC${idrow}' type='text' style='text-align: right' class='form-control POT_DISC text-primary'>
				</td>
				<td>
					<input name='NILAI_TERIMA[]' onblur='hitung()' value='0' id='NILAI_TERIMA${idrow}' type='text' style='text-align: right' class='form-control NILAI_TERIMA text-primary' readonly>
				</td>
                <td>
				    <input name='KETERANGAN[]' id='KETERANGAN${idrow}' type='text' class='form-control KETERANGAN' placeholder='Keterangan'>
                </td>
                <td>
					<button type='button' id='DELETEX${idrow}' class='btn btn-sm btn-outline-danger btn-delete'><i class='fa fa-fw fa-trash'></i></button>
                </td>
         </tr>`;

			x.innerHTML = html;

			idrow++;
			baris++;
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
	<!-- Modal for Supplier Selection -->
	<div class="modal fade" id="browseSupplierModal" tabindex="-1" role="dialog" aria-labelledby="browseSupplierModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseSupplierModalLabel">Cari Supplier</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<table class="table-stripped table-bordered table" id="table-bsupplier">
						<thead>
							<tr>
								<th>Kode</th>
								<th>Nama</th>
								<th>Alamat</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/accounting@0.4.1/accounting.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

	<script>
		$(document).ready(function() {
			// Initialize Date Picker
			$('.date').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true,
				todayHighlight: true
			});

			// Initialize Select2 for Supplier
			$('#KODES').select2({
				placeholder: 'Pilih Supplier',
				ajax: {
					url: "{{ url('sup/browse') }}",
					dataType: 'json',
					delay: 250,
					data: function(params) {
						return {
							term: params.term
						};
					},
					processResults: function(data) {
						return {
							results: $.map(data, function(item) {
								return {
									text: item.KODES + ' - ' + item.NAMAS,
									id: item.KODES,
									namas: item.NAMAS
								}
							})
						};
					},
					cache: true
				}
			});

			// Set initial supplier value
			@if (isset($header) && $header->KODES)
				var supplierOption = new Option('{{ $header->KODES }} - {{ $header->NAMAS }}', '{{ $header->KODES }}', true, true);
				$('#KODES').append(supplierOption).trigger('change');
			@endif

			// Handle supplier selection
			$('#KODES').on('select2:select', function(e) {
				var data = e.params.data;
				$('#NAMAS').val(data.namas);
			});

			// Auto-calculate totals
			hitung();

			// Initialize button states
			@if ($tipx == 'new')
				$('#SAVEX').show();
				$('#EDITX, #UNDOX, #HAPUSX').hide();
			@else
				$('#SAVEX').show();
				$('#EDITX, #UNDOX, #HAPUSX').show();
			@endif

			// Show navigation buttons if not new
			@if ($tipx != 'new')
				$('#TOPX, #PREVX, #NEXTX, #BOTTOMX, #NEWX').show();
			@endif

			// Delete row functionality
			$(document).on('click', '.btn-delete', function() {
				var row = $(this).closest('tr');
				if ($('#datatable tbody tr').length > 1) {
					row.remove();
					reIndex();
					hitung();
				} else {
					Swal.fire('Error', 'Tidak dapat menghapus baris terakhir!', 'error');
				}
			});
		});

		var idrow = {{ count($detail) > 0 ? count($detail) : 1 }};

		function tambah() {
			var x = document.getElementById('datatable').insertRow(idrow + 1);

			var td1 = x.insertCell(0);
			td1.innerHTML = "<input name='REC[]' id='REC" + idrow +
				"' type='text' class='form-control REC' readonly style='text-align:center; background-color: transparent;' value='" + (idrow + 1) + "'>";

			var td2 = x.insertCell(1);
			td2.innerHTML = "<input name='NO_AG[]' id='NO_AG" + idrow + "' type='text' class='form-control NO_AG' placeholder='No. Ag'>";

			var td3 = x.insertCell(2);
			td3.innerHTML = "<input name='TGL_DETAIL[]' id='TGL_DETAIL" + idrow + "' type='date' class='form-control TGL_DETAIL'>";

			var td4 = x.insertCell(3);
			td4.innerHTML = "<input name='NO_SP[]' id='NO_SP" + idrow + "' type='text' class='form-control NO_SP' placeholder='No. Sp'>";

			var td5 = x.insertCell(4);
			td5.innerHTML = "<input name='NILAI_NOTA[]' id='NILAI_NOTA" + idrow +
				"' type='text' class='form-control NILAI_NOTA' style='text-align: right' onblur='hitung()' value='0'>";

			var td6 = x.insertCell(5);
			td6.innerHTML = "<input name='FAKTUR_PAJAK[]' id='FAKTUR_PAJAK" + idrow +
				"' type='text' class='form-control FAKTUR_PAJAK' style='text-align: right' onblur='hitung()' value='0'>";

			var td7 = x.insertCell(6);
			td7.innerHTML = "<input name='POT_DISC[]' id='POT_DISC" + idrow +
				"' type='text' class='form-control POT_DISC' style='text-align: right' onblur='hitung()' value='0'>";

			var td8 = x.insertCell(7);
			td8.innerHTML = "<input name='NILAI_TERIMA[]' id='NILAI_TERIMA" + idrow +
				"' type='text' class='form-control NILAI_TERIMA' style='text-align: right' readonly value='0'>";

			var td9 = x.insertCell(8);
			td9.innerHTML = "<input name='KETERANGAN[]' id='KETERANGAN" + idrow + "' type='text' class='form-control KETERANGAN' placeholder='Keterangan'>";

			var td10 = x.insertCell(9);
			td10.innerHTML = "<button type='button' id='DELETEX" + idrow +
				"' class='btn btn-sm btn-outline-danger btn-delete'><i class='fa fa-fw fa-trash'></i></button>";

			idrow++;
		}

		function reIndex() {
			var table = document.getElementById('datatable');
			var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

			for (var i = 0; i < rows.length; i++) {
				var recInput = rows[i].getElementsByClassName('REC')[0];
				if (recInput) {
					recInput.value = i + 1;
				}
			}

			idrow = rows.length;
		}

		function hitung() {
			var totalNilaiNota = 0;
			var totalFakturPajak = 0;
			var totalPotDisc = 0;
			var totalNilaiTerima = 0;

			$('.NILAI_NOTA').each(function(index) {
				var nilaiNota = parseFloat($(this).val().replace(/,/g, '') || 0);
				var fakturPajak = parseFloat($('.FAKTUR_PAJAK').eq(index).val().replace(/,/g, '') || 0);
				var potDisc = parseFloat($('.POT_DISC').eq(index).val().replace(/,/g, '') || 0);

				var nilaiTerima = nilaiNota + fakturPajak - potDisc;
				$('.NILAI_TERIMA').eq(index).val(accounting.formatNumber(nilaiTerima, 2));

				totalNilaiNota += nilaiNota;
				totalFakturPajak += fakturPajak;
				totalPotDisc += potDisc;
				totalNilaiTerima += nilaiTerima;
			});

			$('#TOTAL_NILAI_NOTA').val(accounting.formatNumber(totalNilaiNota, 2));
			$('#TOTAL_FAKTUR_PAJAK').val(accounting.formatNumber(totalFakturPajak, 2));
			$('#TOTAL_POT_DISC').val(accounting.formatNumber(totalPotDisc, 2));
			$('#TOTAL_NILAI_TERIMA').val(accounting.formatNumber(totalNilaiTerima, 2));
		}

		function numberfmt(obj) {
			obj.value = accounting.formatNumber(obj.value, 2);
		}

		function numberonly(evt) {
			var charCode = (evt.which) ? evt.which : evt.keyCode;
			if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57))
				return false;
			return true;
		}

		function simpan() {
			// Show loader
			$('#LOADX').show();

			// Validate required fields
			if ($('#KODES').val() == '') {
				Swal.fire('Error', 'Supplier harus dipilih!', 'error');
				$('#LOADX').hide();
				return;
			}

			// Submit form
			document.getElementById("entri").submit();
		}

		function hidup() {
			$('input').prop('disabled', false);
			$('select').prop('disabled', false);
			$('textarea').prop('disabled', false);
		}

		function mati() {
			$('input').prop('disabled', true);
			$('select').prop('disabled', true);
			$('textarea').prop('disabled', true);
		}

		function hapusTrans() {
			Swal.fire({
				title: 'Konfirmasi',
				text: 'Yakin ingin menghapus data ini?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Ya, Hapus!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					window.location = "{{ url('/tformbayar/delete/' . ($header->NO_ID ?? 0)) }}";
				}
			});
		}

		function closeTrans() {
			Swal.fire({
				title: 'Konfirmasi',
				text: 'Yakin ingin menutup form ini?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Ya, Tutup!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					window.location = "{{ url('/tformbayar?flagz=' . ($flagz ?? 'TB')) }}";
				}
			});
		}

		// Format number inputs on blur
		$(document).on('blur', '.NILAI_NOTA, .FAKTUR_PAJAK, .POT_DISC', function() {
			var value = parseFloat($(this).val().replace(/,/g, '') || 0);
			$(this).val(accounting.formatNumber(value, 2));
			hitung();
		});

		// Handle Enter key navigation
		$(document).on('keydown', 'input', function(e) {
			if (e.keyCode == 13) {
				e.preventDefault();
				var inputs = $('input:visible:enabled');
				var index = inputs.index(this);
				if (index < inputs.length - 1) {
					inputs.eq(index + 1).focus();
				}
			}
		});
	</script>
@endsection
