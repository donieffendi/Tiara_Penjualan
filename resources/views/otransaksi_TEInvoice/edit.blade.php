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

	/* Table styling to match the TransLain image */
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

	/* Yellow highlight for calculation fields */
	.field-calculate {
		background-color: #fff3cd !important;
		border: 2px solid #ffeaa7 !important;
	}

	/* Checkbox styling for Posted */
	.posted-checkbox {
		transform: scale(1.2);
		margin-left: 10px;
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
				<h4>{{ $judul ?? 'Transaksi Lain-lain' }}</h4>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form
									action="{{ ($tipx ?? 'edit') == 'new' ? url('/translain/store?flagz=' . ($flagz ?? 'TL')) : url('/translain/update/' . ($header->NO_ID ?? 0) . '?flagz=' . ($flagz ?? 'TL')) }}"
									method="POST" name="entri" id="entri">
									@csrf

									<!-- Header Section -->
									<div class="header-section">
										<!-- Hidden Fields -->
										<input type="hidden" class="form-control NO_ID" id="NO_ID" name="NO_ID" value="{{ $header->NO_ID ?? '' }}" readonly>
										<input name="tipx" class="form-control tipx" id="tipx" value="{{ $tipx ?? 'edit' }}" hidden>
										<input name="flagz" class="form-control flagz" id="flagz" value="{{ $flagz ?? 'TL' }}" hidden>

										<!-- First Row -->
										<div class="row">
											<!-- No Urut (No Bukti) -->
											<div class="col-md-2">
												<label for="NO_BUKTI">No. Urut</label>
												<input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI" value="{{ $header->NO_BUKTI ?? '' }}" readonly
													style="background-color: #e9ecef;">
											</div>

											<!-- User -->
											<div class="col-md-2">
												<label for="USRNM">User</label>
												<input type="text" class="form-control USRNM" id="USRNM" name="USRNM" value="{{ $header->USRNM ?? Auth::user()->username }}"
													readonly style="background-color: #e9ecef;">
											</div>

											<!-- Supplier -->
											<div class="col-md-3">
												<label for="KODEC">Supplier</label>
												<input type="text" class="form-control KODEC" id="KODEC" name="KODEC" value="{{ $header->KODEC ?? '' }}"
													placeholder="Kode Supplier" onblur="getSupplierByCode()">
											</div>

											<!-- Posted Checkbox -->
											<div class="col-md-2">
												<label>&nbsp;</label>
												<div class="form-check">
													<input type="checkbox" class="form-check-input posted-checkbox" id="POSTED" name="POSTED" value="1"
														{{ ($header->POSTED ?? 0) == 1 ? 'checked' : '' }} disabled>
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

											<!-- Supplier Name (from lookup) -->
											<div class="col-md-4">
												<label for="NAMAC">Nama Supplier</label>
												<input type="text" class="form-control NAMAC" id="NAMAC" name="NAMAC" value="{{ $header->NAMAC ?? '' }}" readonly
													style="background-color: #e9ecef;">
											</div>
										</div>

										<!-- Third Row -->
										<div class="row mt-3">
											<!-- Notes (H/P) -->
											<div class="col-md-6">
												<label for="KET">Notes (H/P)</label>
												<input type="text" class="form-control KET" id="KET" name="KET" value="{{ $header->KET ?? '' }}" placeholder="Keterangan">
											</div>

											<!-- No Agenda -->
											<div class="col-md-2">
												<label for="NO_AGENDA">No Agenda</label>
												<input type="text" class="form-control NO_AGENDA" id="NO_AGENDA" name="NO_AGENDA" value="{{ $header->NO_AGENDA ?? '' }}"
													placeholder="No Agenda" onblur="getAgendaData()">
											</div>

											<!-- Tgl Agenda -->
											<div class="col-md-2">
												<label for="TGL_AGENDA">Tgl Agenda</label>
												<select class="form-control TGL_AGENDA" id="TGL_AGENDA" name="TGL_AGENDA">
													<option value="">Pilih Tanggal</option>
													<!-- Options will be populated by JavaScript -->
												</select>
											</div>
										</div>

										<!-- Fourth Row -->
										<div class="row mt-3">
											<!-- No SP -->
											<div class="col-md-2">
												<label for="NO_SP">No SP</label>
												<input type="text" class="form-control NO_SP" id="NO_SP" name="NO_SP" value="{{ $header->NO_SP ?? '' }}"
													placeholder="No SP">
											</div>

											<!-- Form Bayar -->
											<div class="col-md-2">
												<label for="NODOK">Form Bayar</label>
												<input type="text" class="form-control NODOK" id="NODOK" name="NODOK" value="{{ $header->NODOK ?? '' }}"
													placeholder="No Dokumen">
											</div>
										</div>
									</div>

									<!-- Detail Table - Account Detail Table -->
									<div class="detail-table">
										<table id="datatable" class="table-striped table-bordered table">
											<thead>
												<tr>
													<th width="40px">No.</th>
													<th width="100px">No. Account</th>
													<th width="300px">Keterangan Lengkap</th>
													<th width="120px">Nilai</th>
													<th width="80px">Action</th>
												</tr>
											</thead>
											<tbody id="detail-tbody">
												<?php $no = 0; ?>
												@if (isset($detail) && count($detail) > 0)
													@foreach ($detail as $detailItem)
														<tr>
															<td>
																<input type="hidden" name="NO_ID_DETAIL[]" value="{{ $detailItem->NO_ID ?? '' }}">
																<input type="hidden" name="REC[]" value="{{ $detailItem->REC ?? $no + 1 }}">
																<input name="NO_ROW[]" id="NO_ROW{{ $no }}" type="text" value="{{ $no + 1 }}" class="form-control NO_ROW"
																	readonly style="text-align:center; background-color: transparent;">
															</td>
															<td>
																<input name="ACNO[]" id="ACNO{{ $no }}" type="text" value="{{ $detailItem->ACNO ?? '' }}"
																	class="form-control ACNO" placeholder="Account" onblur="validateAccount({{ $no }})">
															</td>
															<td>
																<input name="KET_DETAIL[]" id="KET_DETAIL{{ $no }}" type="text" value="{{ $detailItem->KET ?? '' }}"
																	class="form-control KET_DETAIL" placeholder="Keterangan">
															</td>
															<td>
																<input name="NILAI[]" id="NILAI{{ $no }}" type="text"
																	value="{{ number_format($detailItem->DEBET ?? ($detailItem->KREDIT ?? 0), 2, ',', '.') }}" class="form-control NILAI"
																	style="text-align: right;" onblur="calculateTotals()">
															</td>
															<td>
																<button type="button" class="btn btn-sm btn-danger" onclick="deleteRow({{ $no }})">
																	<i class="fas fa-trash"></i>
																</button>
															</td>
														</tr>
														<?php $no++; ?>
													@endforeach
												@else
													<!-- Default empty row -->
													<tr id="row-0">
														<td>
															<input type="hidden" name="NO_ID_DETAIL[]" value="">
															<input type="hidden" name="REC[]" value="1">
															<input name="NO_ROW[]" id="NO_ROW0" type="text" value="1" class="form-control NO_ROW" readonly
																style="text-align:center; background-color: transparent;">
														</td>
														<td>
															<input name="ACNO[]" id="ACNO0" type="text" value="" class="form-control ACNO" placeholder="Account"
																onblur="validateAccount(0)">
														</td>
														<td>
															<input name="KET_DETAIL[]" id="KET_DETAIL0" type="text" value="" class="form-control KET_DETAIL"
																placeholder="Keterangan">
														</td>
														<td>
															<input name="NILAI[]" id="NILAI0" type="text" value="0.00" class="form-control NILAI" style="text-align: right;"
																onblur="calculateTotals()">
														</td>
														<td>
															<button type="button" class="btn btn-sm btn-success" onclick="addRow()">
																<i class="fas fa-plus"></i>
															</button>
														</td>
													</tr>
												@endif
											</tbody>
										</table>

										<!-- Add Row Button -->
										<div class="row mt-2">
											<div class="col-md-12">
												<button type="button" id="btnAddRow" class="btn btn-success btn-sm" onclick="addRow()">
													<i class="fas fa-plus"></i> Tambah Baris
												</button>
											</div>
										</div>
									</div>

									<!-- Summary Section - Total -->
									<div class="summary-section">
										<div class="row">
											<!-- Total -->
											<div class="col-md-3">
												<label for="TTOTAL">Total</label>
												<input type="text" class="form-control TTOTAL" id="TTOTAL" name="TTOTAL"
													value="{{ number_format($header->TTOTAL ?? 0, 2, ',', '.') }}" readonly style="text-align: right; background-color: #e9ecef;">
											</div>

											<!-- PPN -->
											<div class="col-md-2">
												<label for="PPN">PPN %</label>
												<input type="text" class="form-control PPN" id="PPN" name="PPN" value="{{ $header->PPN ?? 0 }}"
													style="text-align: right;" onblur="calculatePPN()">
											</div>

											<!-- DPP -->
											<div class="col-md-2">
												<label for="DPP">DPP</label>
												<input type="text" class="form-control DPP" id="DPP" name="DPP"
													value="{{ number_format($header->DPP ?? 0, 2, ',', '.') }}" readonly style="text-align: right; background-color: #e9ecef;">
											</div>

											<!-- PPN Amount -->
											<div class="col-md-2">
												<label for="PPNX">PPN</label>
												<input type="text" class="form-control PPNX" id="PPNX" name="PPNX"
													value="{{ number_format($header->PPNX ?? 0, 2, ',', '.') }}" readonly style="text-align: right; background-color: #e9ecef;">
											</div>

											<!-- Grand Total -->
											<div class="col-md-3">
												<label for="NETT">Grand Total</label>
												<input type="text" class="form-control NETT" id="NETT" name="NETT"
													value="{{ number_format($header->NETT ?? 0, 2, ',', '.') }}" readonly style="text-align: right; background-color: #e9ecef;">
											</div>
										</div>
									</div>

									<!-- Action Buttons -->
									<hr style="margin-top: 30px; margin-bottom: 30px">
									<div class="col-md-12 form-group row mt-3">
										<div class="col-md-4">
											<button type="button" id='TOPX'
												onclick="location.href='{{ url('/translain/edit/?idx=' . ($idx ?? 0) . '&tipx=top&flagz=' . ($flagz ?? 'TL') . '') }}'"
												class="btn btn-outline-primary btn-action" {{ ($tipx ?? 'edit') == 'new' ? 'style=display:none;' : '' }}>Top</button>
											<button type="button" id='PREVX'
												onclick="location.href='{{ url('/translain/edit/?idx=' . ($header->NO_ID ?? 0) . '&tipx=prev&flagz=' . ($flagz ?? 'TL') . '&buktix=' . ($header->NO_BUKTI ?? '')) }}'"
												class="btn btn-outline-primary btn-action" {{ ($tipx ?? 'edit') == 'new' ? 'style=display:none;' : '' }}>Prev</button>
											<button type="button" id='NEXTX'
												onclick="location.href='{{ url('/translain/edit/?idx=' . ($header->NO_ID ?? 0) . '&tipx=next&flagz=' . ($flagz ?? 'TL') . '&buktix=' . ($header->NO_BUKTI ?? '')) }}'"
												class="btn btn-outline-primary btn-action" {{ ($tipx ?? 'edit') == 'new' ? 'style=display:none;' : '' }}>Next</button>
											<button type="button" id='BOTTOMX'
												onclick="location.href='{{ url('/translain/edit/?idx=' . ($idx ?? 0) . '&tipx=bottom&flagz=' . ($flagz ?? 'TL') . '') }}'"
												class="btn btn-outline-primary btn-action" {{ ($tipx ?? 'edit') == 'new' ? 'style=display:none;' : '' }}>Bottom</button>
										</div>
										<div class="col-md-5">
											<button type="button" id='NEWX'
												onclick="location.href='{{ url('/translain/edit/?idx=0&tipx=new&flagz=' . ($flagz ?? 'TL') . '') }}'" class="btn btn-warning btn-action"
												{{ ($tipx ?? 'edit') == 'new' ? 'style=display:none;' : '' }}>New</button>
											<button type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary btn-action"
												{{ ($tipx ?? 'edit') == 'new' ? 'style=display:none;' : '' }}>Edit</button>
											<button type="button" id='UNDOX'
												onclick="location.href='{{ url('/translain/edit/?idx=' . ($idx ?? 0) . '&tipx=undo&flagz=' . ($flagz ?? 'TL') . '') }}'"
												class="btn btn-info btn-action" {{ ($tipx ?? 'edit') == 'new' ? 'style=display:none;' : '' }}>Undo</button>
											<button type="button" id='SAVEX' onclick='simpan()' class="btn btn-success btn-action">
												<i class="fa fa-save"></i> Save
											</button>
										</div>
										<div class="col-md-3">
											<button type="button" id='HAPUSX' onclick="hapusTrans()" class="btn btn-outline-danger btn-action"
												{{ ($tipx ?? 'edit') == 'new' ? 'style=display:none;' : '' }}>Hapus</button>
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
@endsection

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

			// Initialize autoNumeric for TransLain fields
			$("#TTOTAL, #DPP, #PPNX, #NETT").autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});

			$("#PPN").autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '0',
				vMax: '100',
				mDec: 2
			});

			// Handle focus for detail numeric fields
			$(document).on('focus', '.NILAI', function() {
				if (!$(this).hasClass('autonumeric')) {
					$(this).autoNumeric('init', {
						aSign: '<?php echo ''; ?>',
						vMin: '-999999999.99'
					});
					$(this).addClass('autonumeric');
				}
			});

			// Count initial rows
			baris = $('#detail-tbody tr').length;
			idrow = baris;

			// Initialize based on tipx
			if ('{{ $tipx ?? 'edit' }}' == 'new') {
				baru();
			} else {
				ganti();
			}
		});

		// Get supplier data when KODEC is entered - equivalent to Delphi SupplierProses
		function getSupplierByCode() {
			var kodec = $('#KODEC').val().trim();

			if (kodec === '') {
				clearSupplierData();
				return;
			}

			$("#LOADX").show();

			$.ajax({
				type: 'POST',
				url: '{{ url('translain/get-supplier') }}',
				data: {
					'_token': $('meta[name="csrf-token"]').attr('content'),
					'kodec': kodec
				},
				success: function(response) {
					$("#LOADX").hide();
					if (response.error) {
						Swal.fire('Error', response.error, 'error');
						clearSupplierData();
					} else {
						$('#NAMAC').val(response.namac || '');
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
			$('#NAMAC').val('');
		}

		// Get agenda data when NO_AGENDA is entered - equivalent to Delphi AgendaProses
		function getAgendaData() {
			var noAgenda = $('#NO_AGENDA').val().trim();

			if (noAgenda === '') {
				clearAgendaData();
				return;
			}

			$("#LOADX").show();

			$.ajax({
				type: 'POST',
				url: '{{ url('translain/get-agenda') }}',
				data: {
					'_token': $('meta[name="csrf-token"]').attr('content'),
					'no_agenda': noAgenda
				},
				success: function(response) {
					$("#LOADX").hide();
					if (response.error) {
						Swal.fire('Error', response.error, 'error');
						clearAgendaData();
					} else {
						// Populate TGL_AGENDA dropdown
						var select = $('#TGL_AGENDA');
						select.empty();
						select.append('<option value="">Pilih Tanggal</option>');

						if (response.dates && response.dates.length > 0) {
							response.dates.forEach(function(date) {
								select.append('<option value="' + date.value + '">' + date.text + '</option>');
							});
						}
					}
				},
				error: function(xhr, status, error) {
					$("#LOADX").hide();
					Swal.fire('Error', 'Gagal mengambil data agenda: ' + error, 'error');
					clearAgendaData();
				}
			});
		}

		function clearAgendaData() {
			var select = $('#TGL_AGENDA');
			select.empty();
			select.append('<option value="">Pilih Tanggal</option>');
		}

		// Validate account when ACNO is entered - equivalent to Delphi Account validation
		function validateAccount(row) {
			var acno = $('#ACNO' + row).val().trim();

			if (acno === '') {
				return;
			}

			$("#LOADX").show();

			$.ajax({
				type: 'POST',
				url: '{{ url('translain/validate-account') }}',
				data: {
					'_token': $('meta[name="csrf-token"]').attr('content'),
					'acno': acno
				},
				success: function(response) {
					$("#LOADX").hide();
					if (response.error) {
						Swal.fire('Error', response.error, 'error');
						$('#ACNO' + row).val('');
						$('#ACNO' + row).focus();
					} else {
						// Account is valid, optionally set description
						if (response.nacno) {
							$('#KET_DETAIL' + row).val(response.nacno);
						}
					}
				},
				error: function(xhr, status, error) {
					$("#LOADX").hide();
					Swal.fire('Error', 'Gagal validasi account: ' + error, 'error');
					$('#ACNO' + row).val('');
					$('#ACNO' + row).focus();
				}
			});
		}

		// Calculate totals - equivalent to Delphi Hitung procedure
		function calculateTotals() {
			var total = 0;

			// Sum all detail values
			$('.NILAI').each(function() {
				var value = parseFloat($(this).val().replace(/,/g, '') || 0);
				total += value;
			});

			// Set total
			$('#TTOTAL').val(numberWithCommas(total.toFixed(2)));

			// Calculate PPN if percentage is set
			calculatePPN();
		}

		function calculatePPN() {
			var total = parseFloat($('#TTOTAL').val().replace(/,/g, '') || 0);
			var ppnPercent = parseFloat($('#PPN').val() || 0);

			var dpp = total / (1 + (ppnPercent / 100));
			var ppnAmount = total - dpp;
			var nett = total;

			$('#DPP').val(numberWithCommas(dpp.toFixed(2)));
			$('#PPNX').val(numberWithCommas(ppnAmount.toFixed(2)));
			$('#NETT').val(numberWithCommas(nett.toFixed(2)));
		}

		// Add new row to detail table
		function addRow() {
			var newRow = `
				<tr id="row-${idrow}">
					<td>
						<input type="hidden" name="NO_ID_DETAIL[]" value="">
						<input type="hidden" name="REC[]" value="${idrow + 1}">
						<input name="NO_ROW[]" id="NO_ROW${idrow}" type="text" value="${idrow + 1}" class="form-control NO_ROW"
							readonly style="text-align:center; background-color: transparent;">
					</td>
					<td>
						<input name="ACNO[]" id="ACNO${idrow}" type="text" value="" class="form-control ACNO"
							placeholder="Account" onblur="validateAccount(${idrow})">
					</td>
					<td>
						<input name="KET_DETAIL[]" id="KET_DETAIL${idrow}" type="text" value="" class="form-control KET_DETAIL"
							placeholder="Keterangan">
					</td>
					<td>
						<input name="NILAI[]" id="NILAI${idrow}" type="text" value="0.00" class="form-control NILAI"
							style="text-align: right;" onblur="calculateTotals()">
					</td>
					<td>
						<button type="button" class="btn btn-sm btn-danger" onclick="deleteRow(${idrow})">
							<i class="fas fa-trash"></i>
						</button>
					</td>
				</tr>
			`;

			$('#detail-tbody').append(newRow);
			baris++;
			idrow++;

			// Initialize autoNumeric for new row
			$('#NILAI' + (idrow - 1)).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});

			// Focus on new account field
			$('#ACNO' + (idrow - 1)).focus();
		}

		// Delete row from detail table
		function deleteRow(row) {
			if (baris <= 1) {
				Swal.fire('Warning', 'Minimal harus ada 1 baris!', 'warning');
				return;
			}

			Swal.fire({
				title: 'Are you sure?',
				text: 'Delete this row?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: 'Yes, delete it!',
				cancelButtonText: 'Cancel'
			}).then((result) => {
				if (result.isConfirmed) {
					$('#row-' + row).remove();
					baris--;
					renumberRows();
					calculateTotals();
				}
			});
		}

		// Renumber rows after deletion
		function renumberRows() {
			var newNumber = 1;
			$('#detail-tbody tr').each(function() {
				$(this).find('.NO_ROW').val(newNumber);
				$(this).find('input[name="REC[]"]').val(newNumber);
				newNumber++;
			});
		}

		function simpan() {
			var kodec = $('#KODEC').val().trim();
			var tgl = $('#TGL').val();

			if (kodec === '') {
				Swal.fire('Error', 'Supplier harus diisi!', 'error');
				return;
			}

			if (tgl === '') {
				Swal.fire('Error', 'Tanggal harus diisi!', 'error');
				return;
			}

			// Validate period
			var bulanPer = {{ session()->get('periode')['bulan'] ?? date('n') }};
			var tahunPer = {{ session()->get('periode')['tahun'] ?? date('Y') }};

			var check = '0';

			if (tgl.substring(3, 5) != bulanPer.toString().padStart(2, '0')) {
				check = '1';
				Swal.fire('Warning', 'Bulan tidak sama dengan Periode', 'warning');
				return;
			}

			if (tgl.substring(tgl.length - 4) != tahunPer.toString()) {
				check = '1';
				Swal.fire('Warning', 'Tahun tidak sama dengan Periode', 'warning');
				return;
			}

			// Validate detail data
			var hasValidDetail = false;
			$('.ACNO').each(function() {
				if ($(this).val().trim() !== '') {
					hasValidDetail = true;
					return false; // break
				}
			});

			if (!hasValidDetail) {
				Swal.fire('Error', 'Detail transaksi harus diisi minimal 1 baris!', 'error');
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
			$("#KODEC, #TGL, #KET, #NO_AGENDA, #TGL_AGENDA, #NO_SP, #NODOK").attr("readonly", false);
			$("#PPN").attr("readonly", false);
			$(".ACNO, .KET_DETAIL, .NILAI").attr("readonly", false);
			$("#btnAddRow").attr("disabled", false);
		}

		function mati() {
			$("#TOPX, #PREVX, #NEXTX, #BOTTOMX").attr("disabled", false);
			$("#NEWX, #EDITX").attr("disabled", false);
			$("#UNDOX, #SAVEX").attr("disabled", true);
			$("#HAPUSX").attr("disabled", false);
			$("#CLOSEX").attr("disabled", false);

			// Disable form fields
			$("#KODEC, #TGL, #KET, #NO_AGENDA, #TGL_AGENDA, #NO_SP, #NODOK").attr("readonly", true);
			$("#PPN").attr("readonly", true);
			$(".ACNO, .KET_DETAIL, .NILAI").attr("readonly", true);
			$("#btnAddRow").attr("disabled", true);
		}

		function kosong() {
			$('#NO_BUKTI').val("");
			$('#KODEC').val("");
			$('#NAMAC').val("");
			$('#TGL').val("{{ date('d-m-Y') }}");
			$('#KET').val("");
			$('#NO_AGENDA').val("");
			$('#NO_SP').val("");
			$('#NODOK').val("");

			// Clear summary fields
			$('#TTOTAL').val("0.00");
			$('#PPN').val("0");
			$('#DPP').val("0.00");
			$('#PPNX').val("0.00");
			$('#NETT').val("0.00");

			// Clear agenda dropdown
			clearAgendaData();

			// Clear detail table - keep one empty row
			var tbody = $('#detail-tbody');
			tbody.empty();

			var emptyRow = `
				<tr id="row-0">
					<td>
						<input type="hidden" name="NO_ID_DETAIL[]" value="">
						<input type="hidden" name="REC[]" value="1">
						<input name="NO_ROW[]" id="NO_ROW0" type="text" value="1" class="form-control NO_ROW"
							readonly style="text-align:center; background-color: transparent;">
					</td>
					<td>
						<input name="ACNO[]" id="ACNO0" type="text" value="" class="form-control ACNO"
							placeholder="Account" onblur="validateAccount(0)">
					</td>
					<td>
						<input name="KET_DETAIL[]" id="KET_DETAIL0" type="text" value="" class="form-control KET_DETAIL"
							placeholder="Keterangan">
					</td>
					<td>
						<input name="NILAI[]" id="NILAI0" type="text" value="0.00" class="form-control NILAI"
							style="text-align: right;" onblur="calculateTotals()">
					</td>
					<td>
						<button type="button" class="btn btn-sm btn-success" onclick="addRow()">
							<i class="fas fa-plus"></i>
						</button>
					</td>
				</tr>
			`;

			tbody.append(emptyRow);

			baris = 1;
			idrow = 1;
		}

		function hapusTrans() {
			let text = "Hapus Transaksi " + $('#NO_BUKTI').val() + "?";
			var flagz = "{{ $flagz ?? 'TL' }}";

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
						var loc = "{{ url('/translain/delete/' . ($header->NO_ID ?? 0)) }}" + '?flagz=' + encodeURIComponent(flagz);
						window.location = loc;
					});
				}
			});
		}

		function closeTrans() {
			var flagz = "{{ $flagz ?? 'TL' }}";

			Swal.fire({
				title: 'Are you sure?',
				text: 'Do you really want to close this page? Unsaved changes will be lost.',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Yes, close it',
				cancelButtonText: 'No, stay here'
			}).then((result) => {
				if (result.isConfirmed) {
					var loc = "{{ url('/translain/') }}" + '?flagz=' + encodeURIComponent(flagz);
					window.location = loc;
				}
			});
		}

		// Handle PPN field change to recalculate
		$(document).on('blur', '#PPN', function() {
			var value = parseFloat($(this).val() || 0);
			$(this).val(value.toFixed(2));
			calculatePPN();
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

		// Auto calculate when detail values change
		$(document).on('blur', '.NILAI', function() {
			var value = parseFloat($(this).val().replace(/,/g, '') || 0);
			$(this).val(numberWithCommas(value.toFixed(2)));
			calculateTotals();
		});
	</script>
@endsection
