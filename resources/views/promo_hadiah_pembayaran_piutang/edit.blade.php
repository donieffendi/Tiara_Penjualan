@extends('layouts.plain')

@section('content')
	<style>
		.form-control-sm {
			height: calc(1.5em + 0.5rem + 2px);
			padding: 0.25rem 0.5rem;
			font-size: 0.875rem;
		}

		.form-group.row {
			margin-bottom: 0.5rem !important;
		}

		.col-form-label {
			padding-top: calc(0.25rem + 1px);
			padding-bottom: calc(0.25rem + 1px);
			font-size: 0.875rem;
		}

		.text-right {
			text-align: right !important;
		}

		#detailTable {
			margin-bottom: 0;
			font-size: 0.875rem;
		}

		#detailTable th,
		#detailTable td {
			padding: 4px 8px;
			vertical-align: middle;
		}

		.entry-section {
			background-color: #f8f9fa;
			padding: 10px;
			border: 1px solid #dee2e6;
			margin-top: 10px;
		}

		.content-wrapper {
			padding: 10px !important;
		}

		.card {
			margin-bottom: 10px;
		}

		.card-body {
			padding: 15px;
		}

		.btn-sm {
			padding: 0.25rem 0.5rem;
			font-size: 0.875rem;
		}

		.input-group-sm .btn {
			padding: 0 8px;
		}

		.table-responsive {
			max-height: 300px;
			overflow-y: auto;
		}

		#detailTable thead {
			position: sticky;
			top: 0;
			background-color: #f8f9fa;
			z-index: 10;
		}
	</style>

	<div class="content-wrapper">
		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<h3 class="card-title">Pembayaran Piutang - {{ $status == 'simpan' ? 'New' : 'Edit' }}</h3>
							</div>
							<div class="card-body">
								<form action="{{ route('phpembayaranpiutang.store') }}" method="POST" name="entri" id="entri">
									@csrf
									<input type="hidden" id="status" name="status" value="{{ $status }}">

									<!-- Header Form -->
									<div class="row">
										<!-- Left Column -->
										<div class="col-md-6">
											<div class="form-group row">
												<label for="no_bukti" class="col-md-3 col-form-label">No Bukti</label>
												<div class="col-md-6">
													<input type="text" class="form-control form-control-sm" id="no_bukti" name="no_bukti"
														value="{{ $status == 'simpan' ? '+' : $no_bukti }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<label for="tgl" class="col-md-3 col-form-label">Tanggal</label>
												<div class="col-md-6">
													<input type="date" class="form-control form-control-sm" id="tgl" name="tgl"
														value="{{ $header && $header->tgl ? date('Y-m-d', strtotime($header->tgl)) : date('Y-m-d') }}" required>
												</div>
											</div>

											<div class="form-group row">
												<label for="jtempo" class="col-md-3 col-form-label">Jtempo</label>
												<div class="col-md-6">
													<input type="date" class="form-control form-control-sm" id="jtempo" name="jtempo"
														value="{{ $header && $header->jtempo ? date('Y-m-d', strtotime($header->jtempo)) : date('Y-m-d') }}" required>
												</div>
											</div>

											<div class="form-group row">
												<label for="tbayar" class="col-md-3 col-form-label">Tipe Bayar</label>
												<div class="col-md-6">
													<select class="form-control form-control-sm" id="tbayar" name="tbayar" required>
														<option value="">-- Pilih --</option>
														<option value="CASH" {{ ($header->tbayar ?? '') == 'CASH' ? 'selected' : '' }}>CASH</option>
														<option value="TRANSFER" {{ ($header->tbayar ?? '') == 'TRANSFER' ? 'selected' : '' }}>TRANSFER</option>
														<option value="GIRO" {{ ($header->tbayar ?? '') == 'GIRO' ? 'selected' : '' }}>GIRO</option>
														<option value="CEK" {{ ($header->tbayar ?? '') == 'CEK' ? 'selected' : '' }}>CEK</option>
													</select>
												</div>
											</div>

											<div class="form-group row">
												<label for="acno" class="col-md-3 col-form-label">Account</label>
												<div class="col-md-6">
													<div class="input-group input-group-sm">
														<input type="text" class="form-control form-control-sm" id="acno" name="acno" value="{{ $header->acno ?? '' }}" required>
														<button type="button" class="btn btn-sm btn-outline-secondary" onclick="browseAccount()">
															...
														</button>
													</div>
												</div>
											</div>

											<div class="form-group row">
												<label for="notes" class="col-md-3 col-form-label">Notes</label>
												<div class="col-md-9">
													<input type="text" class="form-control form-control-sm" id="notes" name="notes" value="{{ $header->notes ?? '' }}">
												</div>
											</div>
										</div>

										<!-- Right Column -->
										<div class="col-md-6">
											<div class="form-group row">
												<label for="kodec" class="col-md-3 col-form-label">Customer</label>
												<div class="col-md-6">
													<div class="input-group input-group-sm">
														<input type="text" class="form-control form-control-sm" id="kodec" name="kodec" value="{{ $header->kodec ?? '' }}" required>
														<button type="button" class="btn btn-sm btn-outline-secondary" onclick="browseCustomer()">
															...
														</button>
													</div>
												</div>
												<div class="col-md-3">
													@if ($status == 'edit' && ($header->posted ?? 0) == 1)
														<div class="form-check">
															<input class="form-check-input" type="checkbox" id="posted" checked disabled>
															<label class="form-check-label" for="posted">Posted</label>
														</div>
													@endif
												</div>
											</div>

											<div class="form-group row">
												<label class="col-md-3 col-form-label"></label>
												<div class="col-md-9">
													<input type="text" class="form-control form-control-sm" id="namac" name="namac" value="{{ $header->namac ?? '' }}"
														readonly>
												</div>
											</div>

											<div class="form-group row">
												<label class="col-md-3 col-form-label"></label>
												<div class="col-md-9">
													<input type="text" class="form-control form-control-sm" id="alamat" name="alamat" value="{{ $header->alamat ?? '' }}"
														readonly>
												</div>
											</div>

											<div class="form-group row">
												<label class="col-md-3 col-form-label"></label>
												<div class="col-md-9">
													<input type="text" class="form-control form-control-sm" id="kota" name="kota" value="{{ $header->kota ?? '' }}" readonly>
												</div>
											</div>
										</div>
									</div>

									<!-- Entry Invoice Section -->
									<div class="row">
										<div class="col-md-12">
											<div class="entry-section">
												<div class="row">
													<div class="col-md-2">
														<label for="no_faktur" class="form-label mb-1">No.</label>
														<div class="input-group input-group-sm">
															<input type="text" class="form-control form-control-sm" id="no_faktur" name="no_faktur">
															<button type="button" class="btn btn-sm btn-outline-secondary" onclick="browseFaktur()">
																...
															</button>
														</div>
													</div>

													<div class="col-md-2">
														<label for="tgl_faktur_entry" class="form-label mb-1">Inv#.</label>
														<input type="text" class="form-control form-control-sm" id="tgl_faktur_entry" readonly>
													</div>

													<div class="col-md-2">
														<label for="tgl_entry" class="form-label mb-1">Tgl</label>
														<input type="text" class="form-control form-control-sm" id="tgl_entry" readonly>
													</div>

													<div class="col-md-2">
														<label for="total_entry" class="form-label mb-1">Total</label>
														<input type="number" class="form-control form-control-sm text-right" id="total_entry" step="0.01" readonly>
													</div>

													<div class="col-md-4">
														<label for="uraian" class="form-label mb-1">Uraian</label>
														<input type="text" class="form-control form-control-sm" id="uraian" name="uraian">
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Detail Table -->
									<div class="row mt-2">
										<div class="col-md-12">
											<div class="table-responsive">
												<table class="table-sm table-bordered table" id="detailTable">
													<thead>
														<tr>
															<th style="width: 5%;">No</th>
															<th style="width: 15%;">Inv#.</th>
															<th style="width: 12%;">Tgl</th>
															<th style="width: 15%;" class="text-right">Total</th>
															<th style="width: 53%;">Uraian</th>
														</tr>
													</thead>
													<tbody id="detailTableBody">
														@if ($status == 'edit' && !empty($detail))
															@foreach ($detail as $index => $item)
																<tr data-no-faktur="{{ $item->no_faktur }}">
																	<td class="text-center">{{ $index + 1 }}</td>
																	<td>{{ $item->no_faktur }}</td>
																	<td>{{ $item->tgl_faktur ? date('d/m/Y', strtotime($item->tgl_faktur)) : '' }}</td>
																	<td class="text-right">{{ number_format($item->total, 2, ',', '.') }}</td>
																	<td>{{ $item->uraian ?? '' }}</td>
																	<input type="hidden" name="details[{{ $index }}][no_id]" value="{{ $item->no_id ?? 0 }}">
																	<input type="hidden" name="details[{{ $index }}][rec]" value="{{ $item->rec ?? $index + 1 }}">
																	<input type="hidden" name="details[{{ $index }}][no_faktur]" value="{{ $item->no_faktur }}">
																	<input type="hidden" name="details[{{ $index }}][tgl_faktur]" value="{{ $item->tgl_faktur }}">
																	<input type="hidden" name="details[{{ $index }}][total]" value="{{ $item->total }}">
																	<input type="hidden" name="details[{{ $index }}][bayar]" value="{{ $item->bayar }}">
																	<input type="hidden" name="details[{{ $index }}][lain]" value="{{ $item->lain }}">
																	<input type="hidden" name="details[{{ $index }}][sisa]" value="{{ $item->sisa }}">
																	<input type="hidden" name="details[{{ $index }}][uraian]" value="{{ $item->uraian ?? '' }}">
																</tr>
															@endforeach
														@endif
													</tbody>
												</table>
											</div>
										</div>
									</div>

									<!-- Action Buttons -->
									<div class="row mt-3">
										<div class="col-md-12">
											<button type="button" id="SAVEX" onclick="simpan()" class="btn btn-success btn-sm">
												<i class="fas fa-save"></i> Save
											</button>
											<button type="button" id="CLOSEX" onclick="closeForm()" class="btn btn-secondary btn-sm">
												<i class="fas fa-times"></i> Close
											</button>
											@if ($status == 'edit')
												<button type="button" onclick="printData('{{ $no_bukti }}')" class="btn btn-info btn-sm">
													<i class="fas fa-print"></i> Print
												</button>
											@endif
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Browse Customer Modal -->
	<div class="modal fade" id="browseCustomerModal" tabindex="-1" aria-labelledby="browseCustomerModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseCustomerModalLabel">Browse Customer</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<input type="text" class="form-control form-control-sm" id="searchCustomer" placeholder="Cari customer...">
					</div>
					<div class="table-responsive">
						<table class="table-bordered table-sm table" id="customerTable">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Nama Customer</th>
									<th>Alamat</th>
									<th>Kota</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="customerTableBody">
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Browse Account Modal -->
	<div class="modal fade" id="browseAccountModal" tabindex="-1" aria-labelledby="browseAccountModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseAccountModalLabel">Browse Account</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<input type="text" class="form-control form-control-sm" id="searchAccount" placeholder="Cari account...">
					</div>
					<div class="table-responsive">
						<table class="table-bordered table-sm table" id="accountTable">
							<thead>
								<tr>
									<th>Account No</th>
									<th>Nama Account</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="accountTableBody">
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Browse Faktur Modal -->
	<div class="modal fade" id="browseFakturModal" tabindex="-1" aria-labelledby="browseFakturModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseFakturModalLabel">Browse Invoice</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<input type="text" class="form-control form-control-sm" id="searchFaktur" placeholder="Cari invoice...">
					</div>
					<div class="table-responsive">
						<table class="table-bordered table-sm table" id="fakturTable">
							<thead>
								<tr>
									<th>No Invoice</th>
									<th>Tanggal</th>
									<th>Total</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="fakturTableBody">
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('footer-scripts')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		var detailRowIndex = {{ $status == 'edit' && !empty($detail) ? count($detail) : 0 }};
		var NO_FAKTUR_TEMP = '';
		var TGL_FAKTUR_TEMP = '';

		$(document).ready(function() {
			// Set focus based on status
			@if ($status == 'simpan')
				$('#tgl').focus();
			@else
				$('#no_faktur').focus();
			@endif

			// Search customer with delay
			$('#searchCustomer').on('keyup', function() {
				var query = $(this).val();
				if (query.length > 2) {
					searchCustomer(query);
				} else if (query.length === 0) {
					searchCustomer('');
				}
			});

			// Search account with delay
			$('#searchAccount').on('keyup', function() {
				var query = $(this).val();
				if (query.length > 2) {
					searchAccount(query);
				} else if (query.length === 0) {
					searchAccount('');
				}
			});

			// Search faktur with delay
			$('#searchFaktur').on('keyup', function() {
				var query = $(this).val();
				if (query.length > 2) {
					searchFaktur(query);
				} else if (query.length === 0) {
					searchFaktur('');
				}
			});

			// Enter key navigation
			$('body').on('keydown', function(e) {
				if (e.key === "Enter") {
					e.preventDefault();
					handleEnterKey(e.target);
					return false;
				}
			});
		});

		// Handle Enter key navigation
		function handleEnterKey(element) {
			var $element = $(element);
			var id = $element.attr('id');

			switch (id) {
				case 'tgl':
					$('#jtempo').focus().select();
					break;
				case 'jtempo':
					$('#kodec').focus().select();
					break;
				case 'kodec':
					handleKodecEnter($element.val().trim());
					break;
				case 'tbayar':
					$('#acno').focus().select();
					break;
				case 'acno':
					handleAcnoEnter($element.val().trim());
					break;
				case 'notes':
					$('#no_faktur').focus().select();
					break;
				case 'no_faktur':
					handleNoFakturEnter($element.val().trim());
					break;
				case 'uraian':
					handleUraianEnter();
					break;
				default:
					// Navigate to next focusable element
					var form = $element.parents('form:eq(0)');
					var focusable = form.find('input,select,textarea').filter(':visible:not([readonly])');
					var next = focusable.eq(focusable.index(element) + 1);
					if (next.length) {
						next.focus().select();
					}
					break;
			}
		}

		// Handle kodec enter
		function handleKodecEnter(kodec) {
			if (kodec) {
				$.ajax({
					url: '{{ route('phpembayaranpiutang.detail') }}',
					type: 'GET',
					data: {
						kodec: kodec,
						type: 'customer'
					},
					success: function(response) {
						if (response.exists && response.data) {
							$('#kodec').val(response.data.kodec);
							$('#namac').val(response.data.namac);
							$('#alamat').val(response.data.alamat);
							$('#kota').val(response.data.kota);
							$('#tbayar').focus().select();
						} else {
							browseCustomer();
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal mengambil data customer'
						});
					}
				});
			}
		}

		// Handle acno enter
		function handleAcnoEnter(acno) {
			if (acno) {
				$.ajax({
					url: '{{ route('phpembayaranpiutang.detail') }}',
					type: 'GET',
					data: {
						acno: acno,
						type: 'account'
					},
					success: function(response) {
						if (response.exists && response.data) {
							$('#acno').val(response.data.acno);
							$('#notes').focus().select();
						} else {
							browseAccount();
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal mengambil data account'
						});
					}
				});
			}
		}

		// Handle no_faktur enter
		function handleNoFakturEnter(no_faktur) {
			if (no_faktur) {
				var kodec = $('#kodec').val();
				if (!kodec) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Customer is Empty.'
					});
					$('#kodec').focus().select();
					return;
				}

				$.ajax({
					url: '{{ route('phpembayaranpiutang.detail') }}',
					type: 'GET',
					data: {
						no_faktur: no_faktur,
						type: 'faktur'
					},
					success: function(response) {
						if (response.success && response.exists && response.data) {
							NO_FAKTUR_TEMP = response.data.no_bukti;
							TGL_FAKTUR_TEMP = response.data.tgl;

							// Check if invoice already exists in detail
							var existingRow = findDetailRow(NO_FAKTUR_TEMP);
							if (existingRow.length > 0) {
								Swal.fire({
									icon: 'warning',
									title: 'Warning',
									text: 'Invoice sudah ada di detail!'
								});
								clearEntryForm();
								$('#no_faktur').focus().select();
							} else {
								// Set entry form values
								$('#tgl_faktur_entry').val(NO_FAKTUR_TEMP);
								$('#tgl_entry').val(formatDate(TGL_FAKTUR_TEMP));
								$('#total_entry').val(response.data.total);
								$('#uraian').focus().select();
							}
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: response.message || 'Nomor kitir tidak ditemukan'
							});
							clearEntryForm();
							$('#no_faktur').focus().select();
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal mengambil data invoice'
						});
					}
				});
			}
		}

		// Handle uraian enter
		function handleUraianEnter() {
			var no_faktur = $('#no_faktur').val().trim();
			var total = parseFloat($('#total_entry').val()) || 0;
			var uraian = $('#uraian').val().trim();

			if (!no_faktur || !NO_FAKTUR_TEMP) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Entri invoice dulu!'
				});
				$('#no_faktur').focus().select();
				return;
			}

			if (total <= 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Total harus lebih dari 0!'
				});
				return;
			}

			// Add to detail table
			addToDetailTable(NO_FAKTUR_TEMP, TGL_FAKTUR_TEMP, total, uraian);

			// Clear entry form
			clearEntryForm();
			$('#no_faktur').focus();
		}

		// Add item to detail table
		function addToDetailTable(no_faktur, tgl_faktur, total, uraian) {
			var sisa = total;
			var bayar = 0;
			var lain = 0;

			var newRow = `
				<tr data-no-faktur="${no_faktur}">
					<td class="text-center">${detailRowIndex + 1}</td>
					<td>${no_faktur}</td>
					<td>${formatDate(tgl_faktur)}</td>
					<td class="text-right">${formatNumber(total)}</td>
					<td>${uraian}</td>
					<input type="hidden" name="details[${detailRowIndex}][no_id]" value="0">
					<input type="hidden" name="details[${detailRowIndex}][rec]" value="${detailRowIndex + 1}">
					<input type="hidden" name="details[${detailRowIndex}][no_faktur]" value="${no_faktur}">
					<input type="hidden" name="details[${detailRowIndex}][tgl_faktur]" value="${tgl_faktur}">
					<input type="hidden" name="details[${detailRowIndex}][total]" value="${total}">
					<input type="hidden" name="details[${detailRowIndex}][bayar]" value="${bayar}">
					<input type="hidden" name="details[${detailRowIndex}][lain]" value="${lain}">
					<input type="hidden" name="details[${detailRowIndex}][sisa]" value="${sisa}">
					<input type="hidden" name="details[${detailRowIndex}][uraian]" value="${uraian}">
				</tr>`;

			$('#detailTableBody').append(newRow);
			detailRowIndex++;
		}

		// Find detail row by no_faktur
		function findDetailRow(no_faktur) {
			return $('#detailTableBody tr[data-no-faktur="' + no_faktur + '"]');
		}

		// Clear entry form
		function clearEntryForm() {
			$('#no_faktur').val('');
			$('#tgl_faktur_entry').val('');
			$('#tgl_entry').val('');
			$('#total_entry').val('');
			$('#uraian').val('');
			NO_FAKTUR_TEMP = '';
			TGL_FAKTUR_TEMP = '';
		}

		// Browse customer functionality
		function browseCustomer() {
			$('#browseCustomerModal').modal('show');
			searchCustomer('');
		}

		function searchCustomer(query) {
			$.ajax({
				url: '{{ route('phpembayaranpiutang.browse') }}',
				type: 'GET',
				data: {
					q: query,
					type: 'customer'
				},
				success: function(response) {
					var tbody = $('#customerTableBody');
					tbody.empty();

					if (response.length > 0) {
						response.forEach(function(item) {
							var row = '<tr>';
							row += '<td>' + item.kodec + '</td>';
							row += '<td>' + item.namac + '</td>';
							row += '<td>' + (item.alamat || '') + '</td>';
							row += '<td>' + (item.kota || '') + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectCustomer(\'' +
								item.kodec + '\', \'' + escapeHtml(item.namac) + '\', \'' + escapeHtml(item.alamat || '') + '\', \'' +
								escapeHtml(item.kota || '') + '\')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="5" class="text-center">No data found</td></tr>');
					}
				}
			});
		}

		function selectCustomer(kodec, namac, alamat, kota) {
			$('#kodec').val(kodec);
			$('#namac').val(namac);
			$('#alamat').val(alamat);
			$('#kota').val(kota);
			$('#browseCustomerModal').modal('hide');
			$('#tbayar').focus();
		}

		// Browse account functionality
		function browseAccount() {
			$('#browseAccountModal').modal('show');
			searchAccount('');
		}

		function searchAccount(query) {
			$.ajax({
				url: "{{ route('phpembayaranpiutang.browse') }}",
				type: 'GET',
				data: {
					q: query,
					type: 'account'
				},
				success: function(response) {
					var tbody = $('#accountTableBody');
					tbody.empty();

					if (response.length > 0) {
						response.forEach(function(item) {
							var row = '<tr>';
							row += '<td>' + item.acno + '</td>';
							row += '<td>' + (item.nama || '') + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectAccount(\'' +
								item.acno + '\')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="3" class="text-center">No data found</td></tr>');
					}
				}
			});
		}

		function selectAccount(acno) {
			$('#acno').val(acno);
			$('#browseAccountModal').modal('hide');
			$('#notes').focus();
		}

		// Browse faktur functionality
		function browseFaktur() {
			var kodec = $('#kodec').val();
			if (!kodec) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Customer is Empty.'
				});
				return;
			}

			$('#browseFakturModal').modal('show');
			searchFaktur('');
		}

		function searchFaktur(query) {
			var kodec = $('#kodec').val();

			$.ajax({
				url: "{{ route('phpembayaranpiutang.browse') }}",
				type: 'GET',
				data: {
					q: query,
					type: 'faktur',
					kodec: kodec
				},
				success: function(response) {
					var tbody = $('#fakturTableBody');
					tbody.empty();

					if (response.length > 0) {
						response.forEach(function(item) {
							var row = '<tr>';
							row += '<td>' + item.no_bukti + '</td>';
							row += '<td>' + formatDate(item.tgl) + '</td>';
							row += '<td class="text-right">' + formatNumber(item.total) + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectFaktur(\'' +
								item.no_bukti + '\', \'' + item.tgl + '\', ' + item.total + ')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="4" class="text-center">No data found</td></tr>');
					}
				}
			});
		}

		function selectFaktur(no_bukti, tgl, total) {
			$('#no_faktur').val(no_bukti);
			$('#browseFakturModal').modal('hide');
			handleNoFakturEnter(no_bukti);
		}

		// Save function
		function simpan() {
			@php
				$periodeValue = is_array($periode) ? $periode['periode'] ?? ($periode[0] ?? date('m.Y')) : $periode;
			@endphp
			var periode = '{{ $periodeValue }}';
			var tgl = $('#tgl').val();
			var tglDate = new Date(tgl);
			var month = String(tglDate.getMonth() + 1).padStart(2, '0');
			var year = tglDate.getFullYear();

			var periodeMonth = periode.substr(0, 2);
			var periodeYear = periode.substr(-4);

			if (month !== periodeMonth) {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Month is not the same as Periode.'
				});
				return;
			}

			if (year != periodeYear) {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Year is not the same as Periode.'
				});
				return;
			}

			// Basic validation
			if ($('#kodec').val().trim() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Customer is Empty.'
				});
				$('#kodec').focus();
				return;
			}

			if ($('#tbayar').val().trim() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Type Bayar tidak boleh kosong.'
				});
				$('#tbayar').focus();
				return;
			}

			if ($('#acno').val().trim() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Account tidak boleh kosong.'
				});
				$('#acno').focus();
				return;
			}

			var detailCount = $('#detailTableBody tr').length;
			if (detailCount === 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Detail invoice tidak boleh kosong.'
				});
				return;
			}

			// Show loading
			Swal.fire({
				title: 'Menyimpan...',
				text: 'Mohon tunggu',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading()
				}
			});

			// Submit form via AJAX
			$.ajax({
				url: "{{ route('phpembayaranpiutang.store') }}",
				type: 'POST',
				data: $('#entri').serialize(),
				success: function(response) {
					Swal.close();
					if (response.success) {
						Swal.fire({
							title: 'Success!',
							text: 'Save Data Success',
							icon: 'success',
							confirmButtonText: 'OK'
						}).then(() => {
							window.location.href = '{{ route('phpembayaranpiutang') }}';
						});
					} else {
						Swal.fire({
							title: 'Error!',
							text: response.message,
							icon: 'error',
							confirmButtonText: 'OK'
						});
					}
				},
				error: function(xhr, status, error) {
					Swal.close();
					var errorMessage = 'Terjadi kesalahan saat menyimpan data';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMessage = xhr.responseJSON.message;
					}
					Swal.fire({
						title: 'Error!',
						text: errorMessage,
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Close form
		function closeForm() {
			window.location.href = '{{ route('phpembayaranpiutang') }}';
		}

		// Print function
		function printData(no_bukti) {
			$.ajax({
				url: "{{ route('phpembayaranpiutang.print') }}",
				type: 'POST',
				data: {
					no_bukti: no_bukti,
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					if (response.data && response.data.length > 0) {
						var printWindow = window.open('', '_blank');
						var printContent = generatePrintContent(response.data, response.terbilang);
						printWindow.document.write(printContent);
						printWindow.document.close();
						printWindow.print();
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Warning',
							text: 'Tidak ada data untuk dicetak'
						});
					}
				},
				error: function() {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Gagal mencetak data'
					});
				}
			});
		}

		function generatePrintContent(data, terbilang) {
			var total = 0;
			data.forEach(function(item) {
				total += parseFloat(item.total) || 0;
			});

			var content = `
				<!DOCTYPE html>
				<html>
				<head>
					<title>Laporan Pembayaran Piutang</title>
					<style>
						body { font-family: Arial, sans-serif; font-size: 12px; }
						table { width: 100%; border-collapse: collapse; margin-top: 10px; }
						th, td { border: 1px solid #000; padding: 5px; text-align: left; }
						th { background-color: #f0f0f0; font-weight: bold; }
						.text-center { text-align: center; }
						.text-right { text-align: right; }
						.header { text-align: center; margin-bottom: 20px; }
						.info { margin-bottom: 10px; }
						.info-table { border: none; }
						.info-table td { border: none; padding: 3px; }
					</style>
				</head>
				<body>
					<div class="header">
						<h2>INSTRUKSI PENAGIHAN</h2>
					</div>
					<div class="info">
						<table class="info-table">
							<tr>
								<td style="width: 100px;"><strong>No Bukti</strong></td>
								<td style="width: 250px;">: ${data[0].no_bukti}</td>
								<td style="width: 100px;"><strong>Tanggal</strong></td>
								<td>: ${formatDate(data[0].tgl)}</td>
							</tr>
							<tr>
								<td><strong>Customer</strong></td>
								<td>: ${data[0].namac}</td>
								<td><strong>Jatuh Tempo</strong></td>
								<td>: ${formatDate(data[0].jtempo)}</td>
							</tr>
							<tr>
								<td><strong>Alamat</strong></td>
								<td>: ${data[0].alamat}</td>
								<td><strong>Type Bayar</strong></td>
								<td>: ${data[0].tbayar}</td>
							</tr>
							<tr>
								<td><strong>Kota</strong></td>
								<td>: ${data[0].kota}</td>
								<td><strong>Account</strong></td>
								<td>: ${data[0].acno}</td>
							</tr>
							<tr>
								<td><strong>Notes</strong></td>
								<td colspan="3">: ${data[0].notes || ''}</td>
							</tr>
						</table>
					</div>
					<table>
						<thead>
							<tr>
								<th class="text-center">No</th>
								<th>No Invoice</th>
								<th>Tgl Invoice</th>
								<th class="text-right">Total</th>
								<th class="text-right">Bayar</th>
								<th class="text-right">Lain</th>
								<th class="text-right">Sisa</th>
								<th>Uraian</th>
							</tr>
						</thead>
						<tbody>`;

			var totalBayar = 0;
			var totalLain = 0;

			data.forEach((item, index) => {
				totalBayar += parseFloat(item.bayar) || 0;
				totalLain += parseFloat(item.lain) || 0;

				content += `
					<tr>
						<td class="text-center">${index + 1}</td>
						<td>${item.no_faktur}</td>
						<td>${formatDate(item.tgl_faktur)}</td>
						<td class="text-right">${formatNumber(parseFloat(item.total))}</td>
						<td class="text-right">${formatNumber(parseFloat(item.bayar))}</td>
						<td class="text-right">${formatNumber(parseFloat(item.lain))}</td>
						<td class="text-right">${formatNumber(parseFloat(item.sisa))}</td>
						<td>${item.uraian || ''}</td>
					</tr>`;
			});

			content += `
							<tr>
								<td colspan="3" class="text-center"><strong>TOTAL</strong></td>
								<td class="text-right"><strong>${formatNumber(total)}</strong></td>
								<td class="text-right"><strong>${formatNumber(totalBayar)}</strong></td>
								<td class="text-right"><strong>${formatNumber(totalLain)}</strong></td>
								<td colspan="2"></td>
							</tr>
						</tbody>
					</table>
					<div style="margin-top: 15px;">
						<strong>Terbilang:</strong> ${terbilang}
					</div>
					<div style="margin-top: 30px;">
						<table class="info-table" style="width: 100%;">
							<tr>
								<td style="width: 33%; text-align: center;">
									<div style="margin-bottom: 60px;">Dibuat Oleh,</div>
									<div>___________________</div>
								</td>
								<td style="width: 33%; text-align: center;">
									<div style="margin-bottom: 60px;">Diperiksa Oleh,</div>
									<div>___________________</div>
								</td>
								<td style="width: 33%; text-align: center;">
									<div style="margin-bottom: 60px;">Disetujui Oleh,</div>
									<div>___________________</div>
								</td>
							</tr>
						</table>
					</div>
				</body>
				</html>`;

			return content;
		}

		// Utility functions
		function formatNumber(num) {
			return parseFloat(num).toLocaleString('id-ID', {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2
			});
		}

		function formatDate(dateString) {
			if (!dateString) return '';
			var date = new Date(dateString);
			var day = String(date.getDate()).padStart(2, '0');
			var month = String(date.getMonth() + 1).padStart(2, '0');
			var year = date.getFullYear();
			return day + '/' + month + '/' + year;
		}

		function escapeHtml(text) {
			var map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return text.replace(/[&<>"']/g, function(m) {
				return map[m];
			});
		}
	</script>
@endsection
