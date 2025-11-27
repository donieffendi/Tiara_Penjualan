@extends('layouts.plain')

<meta name="csrf-token" content="{{ csrf_token() }}">

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

	/* Header section styling - Pink background matching image */
	.header-section {
		background-color: #ffb3ba;
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

	/* Table styling to match the Food Center image */
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

	/* Summary section styling - Pink background for totals */
	.summary-section {
		background-color: #ffb3ba;
		padding: 15px;
		border-radius: 8px;
		margin-top: 15px;
	}

	.summary-section label {
		font-weight: bold;
		color: #333;
		margin-bottom: 5px;
		display: block;
	}

	/* Button styling */
	.btn-action {
		margin: 2px;
		padding: 8px 16px;
		font-size: 14px;
		border-radius: 4px;
		font-weight: bold;
		text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		transition: all 0.3s ease;
		border-width: 2px !important;
	}

	.btn-action:hover {
		transform: translateY(-1px);
		box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
	}

	.btn-action.btn-outline-primary {
		background-color: #007bff;
		color: white;
		border-color: #007bff;
	}

	.btn-action.btn-outline-primary:hover {
		background-color: #0056b3;
		border-color: #0056b3;
		color: white;
	}

	.btn-action.btn-warning {
		background-color: #ffc107;
		color: #212529;
		border-color: #ffc107;
	}

	.btn-action.btn-warning:hover {
		background-color: #e0a800;
		border-color: #d39e00;
	}

	.btn-action.btn-secondary {
		background-color: #6c757d;
		color: white;
		border-color: #6c757d;
	}

	.btn-action.btn-secondary:hover {
		background-color: #545b62;
		border-color: #4e555b;
	}

	.btn-action.btn-info {
		background-color: #17a2b8;
		color: white;
		border-color: #17a2b8;
	}

	.btn-action.btn-info:hover {
		background-color: #138496;
		border-color: #117a8b;
	}

	.btn-action.btn-success {
		background-color: #28a745;
		color: white;
		border-color: #28a745;
	}

	.btn-action.btn-success:hover {
		background-color: #218838;
		border-color: #1e7e34;
	}

	.btn-action.btn-outline-danger {
		background-color: #dc3545;
		color: white;
		border-color: #dc3545;
	}

	.btn-action.btn-outline-danger:hover {
		background-color: #c82333;
		border-color: #bd2130;
	}

	.btn-action.btn-outline-secondary {
		background-color: #6c757d;
		color: white;
		border-color: #6c757d;
	}

	.btn-action.btn-outline-secondary:hover {
		background-color: #545b62;
		border-color: #4e555b;
	}

	.btn-proses {
		background-color: #28a745 !important;
		border-color: #28a745 !important;
		color: white !important;
		font-weight: bold;
		text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		transition: all 0.3s ease;
		border-width: 2px;
	}

	.btn-proses:hover {
		background-color: #218838 !important;
		border-color: #1e7e34 !important;
		transform: translateY(-1px);
		box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
	}

	.btn-cek-hitung {
		background-color: #007bff !important;
		border-color: #007bff !important;
		color: white !important;
		font-weight: bold;
		text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		transition: all 0.3s ease;
		border-width: 2px;
	}

	.btn-cek-hitung:hover {
		background-color: #0056b3 !important;
		border-color: #004085 !important;
		transform: translateY(-1px);
		box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
	}

	/* Yellow highlight for calculation fields */
	.field-calculate {
		background-color: #fff3cd !important;
		border: 2px solid #ffeaa7 !important;
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
				<h4>{{ $judul ?? 'Instruksi Pembayaran Food Center' }}</h4>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form
									action="{{ $tipx == 'new' ? url('/tbayarfc/store?flagz=' . $flagz . '') : url('/tbayarfc/update/' . $header->NO_ID . '&flagz=' . $flagz . '') }}"
									method="POST" name="entri" id="entri">
									@csrf

									<!-- Header Section -->
									<div class="header-section">
										<!-- Hidden Fields -->
										<input type="hidden" class="form-control NO_ID" id="NO_ID" name="NO_ID" value="{{ $header->NO_ID ?? '' }}" readonly>
										<input name="tipx" class="form-control tipx" id="tipx" value="{{ $tipx ?? 'edit' }}" hidden>
										<input name="flagz" class="form-control flagz" id="flagz" value="{{ $flagz ?? 'FC' }}" hidden>

										<!-- First Row -->
										<div class="row">
											<!-- No Bukti -->
											<div class="col-md-2">
												<label for="NO_BUKTI">Bukti</label>
												<input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI" value="{{ $header->NO_BUKTI ?? '' }}" readonly
													style="background-color: #e9ecef;">
											</div>

											<!-- Stand (No Kasir) -->
											<div class="col-md-2">
												<label for="NO_KASIR">Stand</label>
												<input type="text" class="form-control NO_KASIR" id="NO_KASIR" name="NO_KASIR" value="{{ $header->NO_KASIR ?? '' }}"
													placeholder="Stand" onblur="getSupplierData()">
											</div>

											<!-- Tanggal Periode -->
											<div class="col-md-2">
												<label for="TGL1">Tgl</label>
												<input class="form-control date" id="TGL1" name="TGL1" data-date-format="dd-mm-yyyy" type="text" autocomplete="off"
													value="{{ date('d-m-Y', strtotime($header->TGL1 ?? date('Y-m-d'))) }}">
											</div>

											<div class="col-md-2">
												<label for="TGL2">S/d</label>
												<input class="form-control date" id="TGL2" name="TGL2" data-date-format="dd-mm-yyyy" type="text" autocomplete="off"
													value="{{ date('d-m-Y', strtotime($header->TGL2 ?? date('Y-m-d'))) }}">
											</div>

											<!-- Process Button -->
											<div class="col-md-2">
												<label>&nbsp;</label>
												<button type="button" id="btnProses" class="btn btn-proses form-control" onclick="prosesSalesData()">
													Proses
												</button>
											</div>
										</div>

										<!-- Second Row - Supplier Information -->
										<div class="row mt-3">
											<!-- Supplier Code -->
											<div class="col-md-2">
												<label for="KODES">Supplier</label>
												<input type="text" class="form-control KODES" id="KODES" name="KODES" value="{{ $header->KODES ?? '' }}" readonly
													style="background-color: #e9ecef;">
											</div>

											<!-- Supplier Name -->
											<div class="col-md-3">
												<label for="NAMAS">Nama Supplier</label>
												<input type="text" class="form-control NAMAS" id="NAMAS" name="NAMAS" value="{{ $header->NAMAS ?? '' }}" readonly
													style="background-color: #e9ecef;">
											</div>

											<!-- Bank Name -->
											<div class="col-md-2">
												<label for="NAMA_B">Bank</label>
												<input type="text" class="form-control NAMA_B" id="NAMA_B" name="NAMA_B" value="{{ $header->NAMA_B ?? '' }}" readonly
													style="background-color: #e9ecef;">
											</div>

											<!-- Bank Account -->
											<div class="col-md-2">
												<label for="NOREK">No Rekening</label>
												<input type="text" class="form-control NOREK" id="NOREK" name="NOREK" value="{{ $header->NOREK ?? '' }}" readonly
													style="background-color: #e9ecef;">
											</div>

											<!-- Account Name -->
											<div class="col-md-3">
												<label for="ANB">Nama Rekening</label>
												<input type="text" class="form-control ANB" id="ANB" name="ANB" value="{{ $header->ANB ?? '' }}" readonly
													style="background-color: #e9ecef;">
											</div>
										</div>
									</div>

									<!-- Detail Table - Sales Data Table -->
									<div class="detail-table">
										<table id="datatable" class="table-striped table-bordered table">
											<thead>
												<tr>
													<th width="40px">No</th>
													<th width="100px">Ket</th>
													<th width="120px">Total</th>
													<th width="120px">Mencapai Target</th>
													<th width="120px">Dibawah Target</th>
													<th width="120px">Margin</th>
												</tr>
											</thead>
											<tbody id="detail-tbody">
												<?php $no = 0; ?>
												@if (isset($detail) && count($detail) > 0)
													@foreach ($detail as $detailItem)
														<tr>
															<td>
																<input type="hidden" name="NO_ID_DETAIL[]" value="{{ $detailItem->no_id ?? 'new' }}">
																<input type="hidden" name="REC[]" value="{{ $detailItem->rec ?? $no + 1 }}">
																<input type="hidden" name="JNS[]" value="{{ $detailItem->jns ?? '' }}">
																<input name="NO_ROW[]" id="NO_ROW{{ $no }}" type="text" value="{{ $no + 1 }}" class="form-control NO_ROW"
																	readonly style="text-align:center; background-color: transparent;">
															</td>
															<td>
																<input name="KET[]" id="KET{{ $no }}" type="text" value="{{ $detailItem->ket ?? '' }}" class="form-control KET"
																	readonly style="background-color: transparent;">
															</td>
															<td>
																<input name="TOTAL[]" id="TOTAL{{ $no }}" type="text"
																	value="{{ number_format($detailItem->total ?? 0, 0, ',', '.') }}" class="form-control TOTAL"
																	style="text-align: right; background-color: transparent;" readonly>
															</td>
															<td>
																<input name="TOTAL_UP[]" id="TOTAL_UP{{ $no }}" type="text"
																	value="{{ number_format($detailItem->total_up ?? 0, 0, ',', '.') }}" class="form-control TOTAL_UP"
																	style="text-align: right; background-color: transparent;" readonly>
															</td>
															<td>
																<input name="TOTAL_DOWN[]" id="TOTAL_DOWN{{ $no }}" type="text"
																	value="{{ number_format($detailItem->total_down ?? 0, 0, ',', '.') }}" class="form-control TOTAL_DOWN"
																	style="text-align: right; background-color: transparent;" readonly>
															</td>
															<td>
																<input name="MARGIN_DETAIL[]" id="MARGIN_DETAIL{{ $no }}" type="text"
																	value="{{ number_format($detailItem->margin ?? 0, 0, ',', '.') }}" class="form-control MARGIN_DETAIL"
																	style="text-align: right; background-color: transparent;" readonly>
															</td>
														</tr>
														<?php $no++; ?>
													@endforeach
												@else
													<!-- No data message -->
													<tr id="no-data-row">
														<td colspan="6" class="text-center" style="padding: 20px;">
															&lt;No data to display&gt;
														</td>
													</tr>
												@endif
											</tbody>
										</table>
									</div>

									<!-- Summary Section - Calculation Results -->
									<div class="summary-section">
										<div class="row">
											<!-- Total Nota -->
											<div class="col-md-2">
												<label for="TOTAL_NOTA">Total Nota</label>
												<input type="text" class="form-control TOTAL_NOTA" id="TOTAL_NOTA" name="TOTAL"
													value="{{ number_format($header->TOTAL ?? 0, 0, ',', '.') }}" readonly style="text-align: right;">
											</div>

											<!-- Margin -->
											<div class="col-md-2">
												<label for="MARGIN">Margin</label>
												<input type="text" class="form-control MARGIN" id="MARGIN" name="MARGIN"
													value="{{ number_format($header->MARGIN ?? 0, 0, ',', '.') }}" readonly style="text-align: right;">
											</div>

											<!-- Promosi -->
											<div class="col-md-2">
												<label for="PROM">Promosi</label>
												<input type="text" class="form-control PROM field-calculate" id="PROM" name="PROM"
													value="{{ number_format($header->PROM ?? 0, 0, ',', '.') }}" style="text-align: right;">
											</div>

											<!-- Total TR -->
											<div class="col-md-2">
												<label for="TOTAL_TR">Total TR</label>
												<input type="text" class="form-control TOTAL_TR" id="TOTAL_TR" name="LAIN"
													value="{{ number_format($header->LAIN ?? 0, 0, ',', '.') }}" readonly style="text-align: right;">
											</div>

											<!-- Total Nett -->
											<div class="col-md-2">
												<label for="TOTAL_NETT">Total Nett</label>
												<input type="text" class="form-control TOTAL_NETT" id="TOTAL_NETT" name="JUMLAH"
													value="{{ number_format($header->JUMLAH ?? 0, 0, ',', '.') }}" readonly style="text-align: right;">
											</div>

											<!-- Cek Hitung Button -->
											<div class="col-md-2">
												<label>&nbsp;</label>
												<button type="button" id="btnCekHitung" class="btn btn-cek-hitung form-control" onclick="cekHitung()">
													Cek Hitung
												</button>
											</div>
										</div>

										<!-- Hidden fields for calculations -->
										<div class="row mt-3" style="display: none;">
											<div class="col-md-2">
												<input type="hidden" class="form-control" id="NMARGIN" name="NMARGIN" value="{{ $header->NMARGIN ?? 0 }}">
												<input type="hidden" class="form-control" id="TARGET" name="TARGET" value="{{ $header->TARGET ?? 0 }}">
												<input type="hidden" class="form-control" id="TGL" name="TGL" value="{{ $header->TGL ?? date('Y-m-d') }}">
												<input type="hidden" class="form-control" id="NOTES" name="NOTES" value="{{ $header->NOTES ?? '' }}">
												<input type="hidden" class="form-control" id="POSTED" name="POSTED" value="{{ $header->POSTED ?? 0 }}">
											</div>
										</div>
									</div>

									<!-- Action Buttons -->
									<hr style="margin-top: 30px; margin-bottom: 30px">
									<div class="col-md-12 form-group row mt-3">
										<div class="col-md-4">
											<button type="button" id='TOPX'
												onclick="location.href='{{ url('/tbayarfc/edit/?idx=' . ($idx ?? 0) . '&tipx=top&flagz=' . ($flagz ?? 'FC') . '') }}'"
												class="btn btn-outline-primary btn-action" {{ $tipx == 'new' ? 'style=display:none;' : '' }}>Top</button>
											<button type="button" id='PREVX'
												onclick="location.href='{{ url('/tbayarfc/edit/?idx=' . ($header->NO_ID ?? 0) . '&tipx=prev&flagz=' . ($flagz ?? 'FC') . '&buktix=' . ($header->NO_BUKTI ?? '')) }}'"
												class="btn btn-outline-primary btn-action" {{ $tipx == 'new' ? 'style=display:none;' : '' }}>Prev</button>
											<button type="button" id='NEXTX'
												onclick="location.href='{{ url('/tbayarfc/edit/?idx=' . ($header->NO_ID ?? 0) . '&tipx=next&flagz=' . ($flagz ?? 'FC') . '&buktix=' . ($header->NO_BUKTI ?? '')) }}'"
												class="btn btn-outline-primary btn-action" {{ $tipx == 'new' ? 'style=display:none;' : '' }}>Next</button>
											<button type="button" id='BOTTOMX'
												onclick="location.href='{{ url('/tbayarfc/edit/?idx=' . ($idx ?? 0) . '&tipx=bottom&flagz=' . ($flagz ?? 'FC') . '') }}'"
												class="btn btn-outline-primary btn-action" {{ $tipx == 'new' ? 'style=display:none;' : '' }}>Bottom</button>
										</div>
										<div class="col-md-5">
											<button type="button" id='NEWX'
												onclick="location.href='{{ url('/tbayarfc/edit/?idx=0&tipx=new&flagz=' . ($flagz ?? 'FC') . '') }}'" class="btn btn-warning btn-action"
												{{ $tipx == 'new' ? 'style=display:none;' : '' }}>New</button>
											<button type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary btn-action"
												{{ $tipx == 'new' ? 'style=display:none;' : '' }}>Edit</button>
											<button type="button" id='UNDOX'
												onclick="location.href='{{ url('/tbayarfc/edit/?idx=' . ($idx ?? 0) . '&tipx=undo&flagz=' . ($flagz ?? 'FC') . '') }}'"
												class="btn btn-info btn-action" {{ $tipx == 'new' ? 'style=display:none;' : '' }}>Undo</button>
											<button type="button" id='SAVEX' onclick='simpan()' class="btn btn-success btn-action">
												<i class="fa fa-save"></i> Save
											</button>
										</div>
										<div class="col-md-3">
											<button type="button" id='HAPUSX' onclick="hapusTrans()" class="btn btn-outline-danger btn-action"
												{{ $tipx == 'new' ? 'style=display:none;' : '' }}>Hapus</button>
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
	<script src="{{ asset('foxie_js_css/bootstrap.bundle.min.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

			$tipx = $('#tipx').val();

			// Initialize Date Picker
			$('.date').datepicker({
				dateFormat: 'dd-mm-yy',
				autoclose: true,
				todayHighlight: true
			});

			// Initialize autoNumeric for Food Center fields
			$("#TOTAL_NOTA, #MARGIN, #PROM, #TOTAL_TR, #TOTAL_NETT").autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});

			// Handle focus for detail numeric fields
			$(document).on('focus', '.TOTAL, .TOTAL_UP, .TOTAL_DOWN, .MARGIN_DETAIL', function() {
				if (!$(this).hasClass('autonumeric')) {
					$(this).autoNumeric('init', {
						aSign: '<?php echo ''; ?>',
						vMin: '-999999999.99'
					});
					$(this).addClass('autonumeric');
				}
			});

			// Initialize based on tipx
			if ($tipx == 'new') {
				baru();
			} else {
				ganti();
			}
		});

		// Get supplier data when stand (NO_KASIR) is entered - equivalent to txtstandExit
		function getSupplierData() {
			var stand = $('#NO_KASIR').val().trim();

			if (stand === '') {
				clearSupplierData();
				return;
			}

			$("#LOADX").show();

			$.ajax({
				type: 'POST',
				url: '{{ url('tbayarfc/get-supplier') }}',
				data: {
					'_token': $('meta[name="csrf-token"]').attr('content'),
					'stand': stand
				},
				success: function(response) {
					$("#LOADX").hide();
					if (response.error) {
						Swal.fire('Error', response.error, 'error');
						clearSupplierData();
					} else {
						$('#KODES').val(response.kodes || '');
						$('#NAMAS').val(response.namas || '');
						$('#NAMA_B').val(response.nama_b || '');
						$('#NOREK').val(response.norek || '');
						$('#ANB').val(response.anb || '');
						$('#NMARGIN').val(response.nmargin || 0);
						$('#TARGET').val(response.target || 0);
					}
				},
				error: function(xhr, status, error) {
					$("#LOADX").hide();
					Swal.fire('Error', 'Gagal mengambil data supplier: ' + error, 'error');
					clearSupplierData();
				}
			});
		}

		function clearSupplierData() {
			$('#KODES').val('');
			$('#NAMAS').val('');
			$('#NAMA_B').val('');
			$('#NOREK').val('');
			$('#ANB').val('');
			$('#NMARGIN').val(0);
			$('#TARGET').val(0);
		}

		// Process sales data - equivalent to Delphi Tampil procedure
		function prosesSalesData() {
			var tgl1 = $('#TGL1').val();
			var tgl2 = $('#TGL2').val();
			var stand = $('#NO_KASIR').val().trim();

			if (stand === '') {
				Swal.fire('Warning', 'Stand harus diisi!', 'warning');
				$('#NO_KASIR').focus();
				return;
			}

			if (tgl1 === '' || tgl2 === '') {
				Swal.fire('Warning', 'Tanggal periode harus diisi!', 'warning');
				return;
			}

			// Convert date format from dd-mm-yyyy to yyyy-mm-dd
			var tgl1_formatted = convertDateFormat(tgl1);
			var tgl2_formatted = convertDateFormat(tgl2);

			$("#LOADX").show();

			// First get supplier data
			getSupplierData();

			// Then generate sales data
			setTimeout(function() {
				$.ajax({
					type: 'POST',
					url: '{{ url('tbayarfc/generate-sales') }}',
					data: {
						'_token': $('meta[name="csrf-token"]').attr('content'),
						'tgl1': tgl1_formatted,
						'tgl2': tgl2_formatted,
						'stand': stand,
						'target': $('#TARGET').val() || 0,
						'nmargin': $('#NMARGIN').val() || 0
					},
					success: function(response) {
						$("#LOADX").hide();
						if (response.error) {
							Swal.fire('Error', response.error, 'error');
						} else {
							populateDetailTable(response);
							cekHitung(); // Auto calculate after loading data
						}
					},
					error: function(xhr, status, error) {
						$("#LOADX").hide();
						Swal.fire('Error', 'Gagal memproses data: ' + error, 'error');
					}
				});
			}, 500);
		}

		function convertDateFormat(dateStr) {
			// Convert dd-mm-yyyy to yyyy-mm-dd
			var parts = dateStr.split('-');
			if (parts.length === 3) {
				return parts[2] + '-' + parts[1] + '-' + parts[0];
			}
			return dateStr;
		}

		function populateDetailTable(salesData) {
			var tbody = $('#detail-tbody');
			tbody.empty();

			if (salesData.length === 0) {
				tbody.append(`
					<tr id="no-data-row">
						<td colspan="6" class="text-center" style="padding: 20px;">
							&lt;No data to display&gt;
						</td>
					</tr>
				`);
				return;
			}

			$.each(salesData, function(index, item) {
				var row = `
					<tr>
						<td>
							<input type="hidden" name="NO_ID_DETAIL[]" value="${item.no_id || 'new'}">
							<input type="hidden" name="REC[]" value="${item.rec || (index + 1)}">
							<input type="hidden" name="JNS[]" value="${item.jns || ''}">
							<input name="NO_ROW[]" id="NO_ROW${index}" type="text" value="${index + 1}"
								class="form-control NO_ROW" readonly style="text-align:center; background-color: transparent;">
						</td>
						<td>
							<input name="KET[]" id="KET${index}" type="text" value="${item.ket || ''}"
								class="form-control KET" readonly style="background-color: transparent;">
						</td>
						<td>
							<input name="TOTAL[]" id="TOTAL${index}" type="text" value="${numberWithCommas(item.total || 0)}"
								class="form-control TOTAL" style="text-align: right; background-color: transparent;" readonly>
						</td>
						<td>
							<input name="TOTAL_UP[]" id="TOTAL_UP${index}" type="text" value="${numberWithCommas(item.total_up || 0)}"
								class="form-control TOTAL_UP" style="text-align: right; background-color: transparent;" readonly>
						</td>
						<td>
							<input name="TOTAL_DOWN[]" id="TOTAL_DOWN${index}" type="text" value="${numberWithCommas(item.total_down || 0)}"
								class="form-control TOTAL_DOWN" style="text-align: right; background-color: transparent;" readonly>
						</td>
						<td>
							<input name="MARGIN_DETAIL[]" id="MARGIN_DETAIL${index}" type="text" value="${numberWithCommas(item.margin || 0)}"
								class="form-control MARGIN_DETAIL" style="text-align: right; background-color: transparent;" readonly>
						</td>
					</tr>
				`;
				tbody.append(row);
			});

			baris = salesData.length;
		}

		// Calculate totals - equivalent to Delphi Hitung procedure
		function cekHitung() {
			var details = [];
			var nmargin = parseFloat($('#NMARGIN').val() || 0);

			// Collect detail data
			$('.KET').each(function(index) {
				var row = $(this).closest('tr');
				var jns = row.find('input[name="JNS[]"]').val();
				var total = parseFloat(row.find('.TOTAL').val().replace(/,/g, '') || 0);
				var total_up = parseFloat(row.find('.TOTAL_UP').val().replace(/,/g, '') || 0);
				var total_down = parseFloat(row.find('.TOTAL_DOWN').val().replace(/,/g, '') || 0);

				details.push({
					jns: jns,
					total: total,
					total_up: total_up,
					total_down: total_down
				});
			});

			$.ajax({
				type: 'POST',
				url: '{{ url('tbayarfc/calculate-totals') }}',
				data: {
					'_token': $('meta[name="csrf-token"]').attr('content'),
					'details': details,
					'nmargin': nmargin
				},
				success: function(response) {
					$('#TOTAL_NOTA').val(numberWithCommas(response.total || 0));
					$('#MARGIN').val(numberWithCommas(response.margin || 0));
					$('#PROM').val(numberWithCommas(response.prom || 0));
					$('#TOTAL_TR').val(numberWithCommas(response.total_tr || 0));
					$('#TOTAL_NETT').val(numberWithCommas(response.nett || 0));
				},
				error: function(xhr, status, error) {
					console.error('Error calculating totals:', error);
				}
			});
		}

		function simpan() {
			var stand = $('#NO_KASIR').val().trim();
			var tgl1 = $('#TGL1').val();
			var tgl2 = $('#TGL2').val();

			if (stand === '') {
				Swal.fire('Error', 'Stand harus diisi!', 'error');
				return;
			}

			if (tgl1 === '' || tgl2 === '') {
				Swal.fire('Error', 'Tanggal periode harus diisi!', 'error');
				return;
			}

			// Validate period
			var bulanPer = {{ session()->get('periode')['bulan'] ?? date('n') }};
			var tahunPer = {{ session()->get('periode')['tahun'] ?? date('Y') }};

			var check = '0';

			if (tgl1.substring(3, 5) != bulanPer.toString().padStart(2, '0')) {
				check = '1';
				Swal.fire('Warning', 'Bulan tidak sama dengan Periode', 'warning');
				return;
			}

			if (tgl1.substring(tgl1.length - 4) != tahunPer.toString()) {
				check = '1';
				Swal.fire('Warning', 'Tahun tidak sama dengan Periode', 'warning');
				return;
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
						$("#LOADX").show();
						document.getElementById("entri").submit();
					}
				});
			}
		}

		function baru() {
			kosong();
			hidup();
		}

		function ganti() {
			hidup();
		}

		function hidup() {
			$("#TOPX, #PREVX, #NEXTX, #BOTTOMX").attr("disabled", true);
			$("#NEWX, #EDITX").attr("disabled", true);
			$("#UNDOX, #SAVEX").attr("disabled", false);
			$("#HAPUSX").attr("disabled", true);
			$("#CLOSEX").attr("disabled", false);

			// Enable form fields
			$("#NO_KASIR").attr("readonly", false);
			$("#TGL1, #TGL2").attr("readonly", false);
			$("#PROM").attr("readonly", false);
			$("#btnProses, #btnCekHitung").attr("disabled", false);
		}

		function mati() {
			$("#TOPX, #PREVX, #NEXTX, #BOTTOMX").attr("disabled", false);
			$("#NEWX, #EDITX").attr("disabled", false);
			$("#UNDOX, #SAVEX").attr("disabled", true);
			$("#HAPUSX").attr("disabled", false);
			$("#CLOSEX").attr("disabled", false);

			// Disable form fields
			$("#NO_KASIR").attr("readonly", true);
			$("#TGL1, #TGL2").attr("readonly", true);
			$("#PROM").attr("readonly", true);
			$("#btnProses, #btnCekHitung").attr("disabled", true);
		}

		function kosong() {
			$('#NO_BUKTI').val("");
			$('#NO_KASIR').val("");
			$('#TGL1').val("{{ date('d-m-Y') }}");
			$('#TGL2').val("{{ date('d-m-Y') }}");

			// Clear supplier data
			clearSupplierData();

			// Clear summary fields
			$('#TOTAL_NOTA').val("0");
			$('#MARGIN').val("0");
			$('#PROM').val("0");
			$('#TOTAL_TR').val("0");
			$('#TOTAL_NETT').val("0");

			// Clear detail table
			var tbody = $('#detail-tbody');
			tbody.empty();
			tbody.append(`
				<tr id="no-data-row">
					<td colspan="6" class="text-center" style="padding: 20px;">
						&lt;No data to display&gt;
					</td>
				</tr>
			`);

			baris = 0;
		}

		function hapusTrans() {
			let text = "Hapus Transaksi " + $('#NO_BUKTI').val() + "?";
			var flagz = "{{ $flagz ?? 'FC' }}";

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
					Swal.fire({
						title: 'Deleted!',
						text: 'Data has been deleted.',
						icon: 'success',
						confirmButtonText: 'OK'
					}).then(() => {
						var loc = "{{ url('/tbayarfc/delete/' . ($header->NO_ID ?? 0)) }}" + '?flagz=' + encodeURIComponent(flagz);
						window.location = loc;
					});
				}
			});
		}

		function closeTrans() {
			var flagz = "{{ $flagz ?? 'FC' }}";

			Swal.fire({
				title: 'Are you sure?',
				text: 'Do you really want to close this page? Unsaved changes will be lost.',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Yes, close it',
				cancelButtonText: 'No, stay here'
			}).then((result) => {
				if (result.isConfirmed) {
					var loc = "{{ url('/tbayarfc/') }}" + '?flagz=' + encodeURIComponent(flagz);
					window.location = loc;
				}
			});
		}

		// Handle Promosi field change to recalculate
		$(document).on('blur', '#PROM', function() {
			var value = parseFloat($(this).val().replace(/,/g, '') || 0);
			$(this).val(numberWithCommas(value));
			// Optionally recalculate nett after manual promosi change
		});

		// Handle Enter key navigation
		$(document).on('keydown', 'input', function(e) {
			if (e.keyCode == 13) {
				e.preventDefault();
				var inputs = $('input:visible:enabled:not([readonly])');
				var index = inputs.index(this);
				if (index < inputs.length - 1) {
					inputs.eq(index + 1).focus();
				}
			}
		});
	</script>
@endsection
