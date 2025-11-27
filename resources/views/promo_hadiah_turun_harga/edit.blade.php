@extends('layouts.plain')

@section('content')
	<style>
		.card {}

		.content-header {
			padding: 0 !important;
		}

		.form-group.row {
			align-items: center !important;
			margin-bottom: 0.5rem !important;
		}

		.form-group.row .form-label {
			margin-bottom: 0 !important;
			line-height: 1.5;
			vertical-align: middle;
			display: flex;
			align-items: center;
			height: 38px;
		}

		.form-group.row .form-control {
			line-height: 1.5;
			height: 38px;
		}

		#detailTable {
			font-size: 12px;
		}

		#detailTable th,
		#detailTable td {
			padding: 5px;
			vertical-align: middle;
		}

		.btn-sm {
			padding: 0.25rem 0.5rem;
			font-size: 0.875rem;
		}

		.input-group-sm .form-control {
			height: calc(1.5em + 0.5rem + 2px);
			padding: 0.25rem 0.5rem;
			font-size: 0.875rem;
		}

		.table-fixed {
			table-layout: fixed;
		}
	</style>

	<div class="content-wrapper">
		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<h3 class="card-title">Turun Harga - {{ $status == 'simpan' ? 'New' : 'Edit' }}</h3>
							</div>
							<div class="card-body">
								<form action="{{ route('phturanharga.store') }}" method="POST" name="entri" id="entri">
									@csrf
									<input type="hidden" id="status" name="status" value="{{ $status }}">

									<!-- Header Form -->
									<div class="card mb-3" id="pTop">
										<div class="card-header bg-light">
											<h6 class="mb-0">Header Information</h6>
										</div>
										<div class="card-body">
											<div class="row">
												<!-- No Bukti -->
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="no_bukti" class="form-label">No Bukti</label>
														</div>
														<div class="col-md-8">
															<input type="text" class="form-control form-control-sm" id="no_bukti" name="no_bukti"
																value="{{ $status == 'simpan' ? '+' : $no_bukti }}" readonly placeholder="No Bukti" required>
														</div>
													</div>
												</div>

												<!-- Tanggal -->
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="tgl" class="form-label">Tanggal</label>
														</div>
														<div class="col-md-8">
															<input type="date" class="form-control form-control-sm" id="tgl" name="tgl"
																value="{{ $header && $header->TGL ? date('Y-m-d', strtotime($header->TGL)) : date('Y-m-d') }}" required>
														</div>
													</div>
												</div>

												<!-- Tgl Mulai -->
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="tgl_mulai" class="form-label">Tgl Mulai</label>
														</div>
														<div class="col-md-8">
															<input type="date" class="form-control form-control-sm" id="tgl_mulai" name="tgl_mulai"
																value="{{ $header && $header->TGL_MULAI ? date('Y-m-d', strtotime($header->TGL_MULAI)) : date('Y-m-d') }}" required>
														</div>
													</div>
												</div>

												<!-- Tgl Selesai -->
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="tgl_sls" class="form-label">Tgl Selesai</label>
														</div>
														<div class="col-md-8">
															<input type="date" class="form-control form-control-sm" id="tgl_sls" name="tgl_sls"
																value="{{ $header && $header->TGL_SLS ? date('Y-m-d', strtotime($header->TGL_SLS)) : date('Y-m-d') }}" required>
														</div>
													</div>
												</div>
											</div>

											<div class="row">
												<!-- Jam Mulai -->
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="jam_mulai" class="form-label">Jam Mulai</label>
														</div>
														<div class="col-md-8">
															<input type="time" class="form-control form-control-sm" id="jam_mulai" name="jam_mulai"
																value="{{ $header && $header->JAM_MULAI ? $header->JAM_MULAI : '' }}" required>
														</div>
													</div>
												</div>

												<!-- Jam Selesai -->
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="jam_sls" class="form-label">Jam Selesai</label>
														</div>
														<div class="col-md-8">
															<input type="time" class="form-control form-control-sm" id="jam_sls" name="jam_sls"
																value="{{ $header && $header->JAM_SLS ? $header->JAM_SLS : '' }}" required>
														</div>
													</div>
												</div>

												<!-- Kode Supplier -->
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="kodes" class="form-label">Kode Supplier</label>
														</div>
														<div class="col-md-8">
															<div class="input-group input-group-sm">
																<input type="text" class="form-control form-control-sm" id="kodes" name="kodes" value="{{ $header->KODES ?? '' }}"
																	placeholder="Kode" required>
																<button type="button" class="btn btn-sm btn-primary" onclick="browseSupplier()">
																	<i class="fas fa-search"></i>
																</button>
															</div>
														</div>
													</div>
												</div>

												<!-- Nama Supplier -->
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="namas" class="form-label">Nama</label>
														</div>
														<div class="col-md-8">
															<input type="text" class="form-control form-control-sm" id="namas" name="namas" value="{{ $header->NAMAS ?? '' }}"
																placeholder="Nama Supplier" readonly>
														</div>
													</div>
												</div>
											</div>

											<div class="row">
												<!-- Notes -->
												<div class="col-md-6">
													<div class="form-group row">
														<div class="col-md-2">
															<label for="notes" class="form-label">Notes</label>
														</div>
														<div class="col-md-10">
															<input type="text" class="form-control form-control-sm" id="notes" name="notes" value="{{ $header->notes ?? '' }}"
																placeholder="Keterangan">
														</div>
													</div>
												</div>

												<!-- Cara Bayar -->
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="cara_bayar" class="form-label">Cara Bayar</label>
														</div>
														<div class="col-md-8">
															<select class="form-control form-control-sm" id="cara_bayar" name="cara_bayar">
																<option value="">-- Pilih --</option>
																<option value="CASH" {{ ($header->CARA_BAYAR ?? '') == 'CASH' ? 'selected' : '' }}>CASH</option>
																<option value="TRANSFER" {{ ($header->CARA_BAYAR ?? '') == 'TRANSFER' ? 'selected' : '' }}>TRANSFER</option>
															</select>
														</div>
													</div>
												</div>

												<!-- Nama Kwintansi -->
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="na_kwi" class="form-label">Nama Kwt</label>
														</div>
														<div class="col-md-8">
															<input type="text" class="form-control form-control-sm" id="na_kwi" name="na_kwi" value="{{ $header->NA_KWI ?? '' }}"
																placeholder="Nama Kwintansi">
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Entry Fields Row -->
									<div class="row mt-2">
										<div class="col-md-12">
											<div class="card">
												<div class="card-header bg-secondary">
													<h6 class="mb-0">Entry Barang</h6>
												</div>
												<div class="card-body p-2">
													<div class="row">
														<div class="col-md-12">
															<label id="lblBarang" class="text-info"></label>
														</div>
													</div>
													<div class="row">
														<!-- Kode Barang -->
														<div class="col-md-3">
															<div class="form-group">
																<label for="kd_brg" class="form-label">Kode Barang</label>
																<div class="input-group input-group-sm">
																	<input type="text" class="form-control form-control-sm" id="kd_brg" name="kd_brg" placeholder="Kode Barang">
																	<button type="button" class="btn btn-sm btn-primary" onclick="browseProduct()">
																		<i class="fas fa-search"></i>
																	</button>
																</div>
															</div>
														</div>

														<!-- Turun Harga (A) -->
														<div class="col-md-2">
															<div class="form-group">
																<label for="th_entry" class="form-label">Turun Harga</label>
																<input type="number" class="form-control form-control-sm" id="th_entry" name="th_entry" step="0.01" placeholder="0.00">
															</div>
														</div>

														<!-- Harga Baru (B) -->
														<div class="col-md-2">
															<div class="form-group">
																<label for="hgd_entry" class="form-label">Harga Baru</label>
																<input type="number" class="form-control form-control-sm" id="hgd_entry" name="hgd_entry" step="0.01" placeholder="0.00"
																	readonly>
															</div>
														</div>

														<!-- Partisipasi (C) -->
														<div class="col-md-2">
															<div class="form-group">
																<label for="partsp_entry" class="form-label">Partisipasi</label>
																<input type="number" class="form-control form-control-sm" id="partsp_entry" name="partsp_entry" step="0.01"
																	placeholder="0.00">
															</div>
														</div>

														<!-- Action Buttons -->
														<div class="col-md-3">
															<div class="form-group">
																<label class="form-label">&nbsp;</label>
																<div>
																	<button type="button" class="btn btn-success btn-sm" onclick="addToDetail()">
																		<i class="fas fa-plus"></i> OK
																	</button>
																	<button type="button" class="btn btn-warning btn-sm" onclick="clearEntryForm()">
																		<i class="fas fa-eraser"></i> Clear
																	</button>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Detail Table -->
									<div class="row mt-2">
										<div class="col-md-12">
											<div class="card">
												<div class="card-header d-flex justify-content-between">
													<h6 class="mb-0">Detail Barang</h6>
													<div>
														<span class="badge badge-primary">Total Items: <span id="totalItems">0</span></span>
													</div>
												</div>
												<div class="card-body p-2">
													<div class="table-responsive">
														<table class="table-bordered table-sm table-hover table" id="detailTable">
															<thead class="table-dark">
																<tr>
																	<th width="3%">No</th>
																	<th width="10%">Kode Barang</th>
																	<th width="22%">Nama Barang</th>
																	<th width="8%">Kemasan</th>
																	<th width="8%">Satuan</th>
																	<th width="10%">HJ</th>
																	<th width="10%">Turun Harga</th>
																	<th width="10%">Harga Baru</th>
																	<th width="10%">Partisipasi</th>
																	<th width="9%">Action</th>
																</tr>
															</thead>
															<tbody id="detailTableBody">
																@if ($status == 'edit' && !empty($detail))
																	@foreach ($detail as $index => $item)
																		<tr data-kd-brg="{{ $item->KD_BRG }}" data-no-id="{{ $item->NO_ID ?? 0 }}">
																			<td class="text-center">{{ $index + 1 }}</td>
																			<td>{{ $item->KD_BRG }}</td>
																			<td>{{ $item->NA_BRG }}</td>
																			<td>{{ $item->KET_KEM ?? '' }}</td>
																			<td>{{ $item->KET_UK ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->HJ, 2) }}</td>
																			<td class="text-right">{{ number_format($item->TH, 2) }}</td>
																			<td class="text-right">{{ number_format($item->HJ - $item->TH, 2) }}</td>
																			<td class="text-right">{{ number_format($item->PARTSP, 2) }}</td>
																			<td class="text-center">
																				<button type="button" class="btn btn-sm btn-info" onclick="editRow(this)" title="Edit">
																					<i class="fas fa-edit"></i>
																				</button>
																				<button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Hapus">
																					<i class="fas fa-trash"></i>
																				</button>
																			</td>
																			<input type="hidden" name="details[{{ $index }}][no_id]" value="{{ $item->NO_ID ?? 0 }}">
																			<input type="hidden" name="details[{{ $index }}][rec]" value="{{ $item->REC ?? $index + 1 }}">
																			<input type="hidden" name="details[{{ $index }}][kd_brg]" value="{{ $item->KD_BRG }}">
																			<input type="hidden" name="details[{{ $index }}][na_brg]" value="{{ $item->NA_BRG }}">
																			<input type="hidden" name="details[{{ $index }}][ket_uk]" value="{{ $item->KET_UK ?? '' }}">
																			<input type="hidden" name="details[{{ $index }}][ket_kem]" value="{{ $item->KET_KEM ?? '' }}">
																			<input type="hidden" name="details[{{ $index }}][hj]" value="{{ $item->HJ }}">
																			<input type="hidden" name="details[{{ $index }}][hb]" value="{{ $item->HB ?? 0 }}">
																			<input type="hidden" name="details[{{ $index }}][th]" value="{{ $item->TH }}">
																			<input type="hidden" name="details[{{ $index }}][partsp]" value="{{ $item->PARTSP }}">
																			<input type="hidden" name="details[{{ $index }}][ket]" value="{{ $item->KET ?? '' }}">
																		</tr>
																	@endforeach
																@endif
															</tbody>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Action Buttons -->
									<div class="row mt-3">
										<div class="col-md-12">
											<button type="button" id="SAVEX" onclick="simpan()" class="btn btn-success">
												<i class="fas fa-save"></i> Save
											</button>
											<button type="button" id="CLOSEX" onclick="closeForm()" class="btn btn-outline-secondary">
												<i class="fas fa-times"></i> Close
											</button>
											@if ($status == 'edit')
												<button type="button" onclick="printData('{{ $no_bukti }}')" class="btn btn-info">
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

	<!-- Browse Supplier Modal -->
	<div class="modal fade" id="browseSupplierModal" tabindex="-1" aria-labelledby="browseSupplierModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseSupplierModalLabel">Browse Supplier</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<input type="text" class="form-control" id="searchSupplier" placeholder="Cari supplier...">
					</div>
					<div class="table-responsive">
						<table class="table-bordered table-sm table" id="supplierTable">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Nama Supplier</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="supplierTableBody">
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Browse Product Modal -->
	<div class="modal fade" id="browseProductModal" tabindex="-1" aria-labelledby="browseProductModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseProductModalLabel">Browse Produk</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<input type="text" class="form-control" id="searchProduct" placeholder="Cari produk...">
					</div>
					<div class="table-responsive">
						<table class="table-bordered table-sm table" id="productTable">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Nama Produk</th>
									<th>Kemasan</th>
									<th>Satuan</th>
									<th>HJ</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="productTableBody">
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('footer-scripts')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		var detailRowIndex = {{ $status == 'edit' && !empty($detail) ? count($detail) : 0 }};
		var TEMP_PRODUCT = null;

		$(document).ready(function() {
			// Set focus based on status (matching Delphi FormShow)
			@if ($status == 'simpan')
				$('#tgl_mulai').focus();
			@else
				$('#kd_brg').focus();
			@endif

			// Calculate total on page load
			calculateTotal();

			// Validate dates on change (matching Delphi pTopMouseLeave)
			$('#tgl_mulai, #tgl_sls').on('change blur', function() {
				validateDates();
			});

			// Search supplier with delay
			$('#searchSupplier').on('keyup', function() {
				var query = $(this).val();
				setTimeout(function() {
					searchSupplier(query);
				}, 300);
			});

			// Search product with delay
			$('#searchProduct').on('keyup', function() {
				var query = $(this).val();
				setTimeout(function() {
					searchProduct(query);
				}, 300);
			});

			// Enter key navigation (matching Delphi FormKeyDown)
			$('body').on('keydown', function(e) {
				if (e.key === "Enter") {
					e.preventDefault();
					handleEnterKey(e.target);
					return false;
				}
			});
		});

		// Validate dates (matching Delphi pTopMouseLeave and logic validation)
		function validateDates() {
			var pTop = document.getElementById('pTop');
			var tgl_mulai = new Date($('#tgl_mulai').val());
			var tgl_sls = new Date($('#tgl_sls').val());
			var today = new Date();
			today.setHours(0, 0, 0, 0);

			var status = '{{ $status }}';
			var isValid = true;

			// Reset color
			pTop.classList.remove('date-alert');

			if (status == 'simpan') {
				// Check if tgl_mulai < today
				if (tgl_mulai < today) {
					isValid = false;
				}
				// Check if tgl_sls < today
				if (tgl_sls < today) {
					isValid = false;
				}
			}

			// Check if dates are equal
			if (tgl_mulai.getTime() === tgl_sls.getTime()) {
				isValid = false;
			}

			// Check if tgl_mulai > tgl_sls
			if (tgl_mulai > tgl_sls) {
				isValid = false;
			}

			if (!isValid) {
				pTop.classList.add('date-alert');
			}

			return isValid;
		}

		// Handle Enter key navigation (matching Delphi logic)
		function handleEnterKey(element) {
			var $element = $(element);
			var id = $element.attr('id');

			switch (id) {
				case 'tgl_mulai':
					$('#tgl_sls').focus().select();
					break;
				case 'tgl_sls':
					validateDates();
					$('#jam_mulai').focus().select();
					break;
				case 'jam_mulai':
					$('#jam_sls').focus().select();
					break;
				case 'jam_sls':
					$('#kodes').focus().select();
					break;
				case 'kodes':
					handleKodesEnter($element.val().trim());
					break;
				case 'notes':
					$('#kd_brg').focus().select();
					break;
				case 'kd_brg':
					handleKdBrgEnter($element.val().trim());
					break;
				case 'th_entry':
					handleThEntryChange();
					$('#hgd_entry').focus().select();
					break;
				case 'hgd_entry':
					$('#partsp_entry').focus().select();
					break;
				case 'partsp_entry':
					addToDetail();
					break;
				default:
					// Navigate to next focusable element
					var form = $element.parents('form:eq(0)');
					var focusable = form.find('input,select,textarea,button').filter(':visible:not([readonly]):not([disabled])');
					var next = focusable.eq(focusable.index(element) + 1);
					if (next.length) {
						next.focus().select();
					}
					break;
			}
		}

		// Handle kodes enter (matching Delphi txtkodesExit)
		function handleKodesEnter(kodes) {
			if (kodes) {
				$.ajax({
					url: '{{ route('phturanharga.detail') }}',
					type: 'GET',
					data: {
						kodes: kodes,
						type: 'supplier'
					},
					success: function(response) {
						if (response.exists && response.data) {
							$('#kodes').val(response.data.kodes);
							$('#namas').val(response.data.namas);
							$('#cara_bayar').focus();
						} else {
							browseSupplier();
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal mengambil data supplier'
						});
					}
				});
			}
		}

		// Handle kd_brg enter (matching Delphi txtkd_brgKeyDown)
		function handleKdBrgEnter(kd_brg) {
			if (!validateDates()) {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Filter tanggal masih belum sesuai!'
				});
				$('#tgl_mulai').focus();
				return;
			}

			var kodes = $('#kodes').val();
			if (!kodes) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Supplier kosong!'
				});
				$('#kodes').focus();
				return;
			}

			if (kd_brg) {
				// Check if product already exists in detail
				var existingRow = findDetailRow(kd_brg);
				if (existingRow.length > 0) {
					$('#th_entry').focus().select();
					return;
				}

				var tgl_mulai = $('#tgl_mulai').val();

				$.ajax({
					url: '{{ route('phturanharga.detail') }}',
					type: 'GET',
					data: {
						kd_brg: kd_brg,
						kodes: kodes,
						tgl_mulai: tgl_mulai,
						type: 'product'
					},
					success: function(response) {
						if (response.success && response.exists && response.data) {
							TEMP_PRODUCT = response.data;
							$('#lblBarang').text(response.data.XX || '');

							// Set to entry form
							$('#th_entry').val('').focus().select();
							$('#hgd_entry').val('');
							$('#partsp_entry').val('');
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: response.message || 'Produk tidak ditemukan'
							});
							clearEntryForm();
							$('#kd_brg').focus().select();
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal mengambil data produk'
						});
					}
				});
			}
		}

		// Handle th_entry change (matching Delphi txtAKeyDown)
		function handleThEntryChange() {
			if (TEMP_PRODUCT && $('#th_entry').val()) {
				var hj = parseFloat(TEMP_PRODUCT.HJ) || 0;
				var th = parseFloat($('#th_entry').val()) || 0;
				var hgd = hj - th;
				$('#hgd_entry').val(hgd.toFixed(2));
			}
		}

		// Add to detail table (matching Delphi btnOKClick)
		function addToDetail() {
			var kd_brg = $('#kd_brg').val().trim();
			var th = parseFloat($('#th_entry').val()) || 0;
			var hgd = parseFloat($('#hgd_entry').val()) || 0;
			var partsp = parseFloat($('#partsp_entry').val()) || 0;

			if (!kd_brg || !TEMP_PRODUCT) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Pilih produk terlebih dahulu!'
				});
				$('#kd_brg').focus();
				return;
			}

			if (th <= 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Turun harga harus lebih dari 0!'
				});
				$('#th_entry').focus();
				return;
			}

			if (partsp <= 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Partisipasi harus lebih dari 0!'
				});
				$('#partsp_entry').focus();
				return;
			}

			// Check if already exists
			var existingRow = findDetailRow(kd_brg);
			if (existingRow.length > 0) {
				// Update existing row
				var noId = existingRow.data('no-id');
				existingRow.find('td:eq(6)').text(formatNumber(th));
				existingRow.find('td:eq(7)').text(formatNumber(hgd));
				existingRow.find('td:eq(8)').text(formatNumber(partsp));

				var index = existingRow.index();
				$('input[name="details[' + index + '][th]"]').val(th);
				$('input[name="details[' + index + '][partsp]"]').val(partsp);
			} else {
				// Add new row
				addNewRow(TEMP_PRODUCT, th, partsp);
			}

			clearEntryForm();
			calculateTotal();
			$('#kd_brg').focus();
		}

		// Add new row to detail table
		function addNewRow(product, th, partsp) {
			var hj = parseFloat(product.HJ) || 0;
			var hb = parseFloat(product.HB) || 0;
			var hgd = hj - th;

			var newRow = `
				<tr data-kd-brg="${product.KD_BRG}" data-no-id="0">
					<td class="text-center">${detailRowIndex + 1}</td>
					<td>${product.KD_BRG}</td>
					<td>${product.NA_BRG}</td>
					<td>${product.KET_KEM || ''}</td>
					<td>${product.KET_UK || ''}</td>
					<td class="text-right">${formatNumber(hj)}</td>
					<td class="text-right">${formatNumber(th)}</td>
					<td class="text-right">${formatNumber(hgd)}</td>
					<td class="text-right">${formatNumber(partsp)}</td>
					<td class="text-center">
						<button type="button" class="btn btn-sm btn-info" onclick="editRow(this)" title="Edit">
							<i class="fas fa-edit"></i>
						</button>
						<button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Hapus">
							<i class="fas fa-trash"></i>
						</button>
					</td>
					<input type="hidden" name="details[${detailRowIndex}][no_id]" value="0">
					<input type="hidden" name="details[${detailRowIndex}][rec]" value="${detailRowIndex + 1}">
					<input type="hidden" name="details[${detailRowIndex}][kd_brg]" value="${product.KD_BRG}">
					<input type="hidden" name="details[${detailRowIndex}][na_brg]" value="${product.NA_BRG}">
					<input type="hidden" name="details[${detailRowIndex}][ket_uk]" value="${product.KET_UK || ''}">
					<input type="hidden" name="details[${detailRowIndex}][ket_kem]" value="${product.KET_KEM || ''}">
					<input type="hidden" name="details[${detailRowIndex}][hj]" value="${hj}">
					<input type="hidden" name="details[${detailRowIndex}][hb]" value="${hb}">
					<input type="hidden" name="details[${detailRowIndex}][th]" value="${th}">
					<input type="hidden" name="details[${detailRowIndex}][partsp]" value="${partsp}">
					<input type="hidden" name="details[${detailRowIndex}][ket]" value="">
				</tr>`;

			$('#detailTableBody').append(newRow);
			detailRowIndex++;
		}

		// Edit row (matching Delphi cxGrid1DBTableView1DblClick)
		function editRow(btn) {
			var row = $(btn).closest('tr');
			var kd_brg = row.data('kd-brg');

			$('#kd_brg').val(kd_brg);
			$('#kd_brg').focus();
		}

		// Remove row from detail table
		function removeRow(btn) {
			Swal.fire({
				title: 'Konfirmasi',
				text: 'Hapus item ini?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Ya',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					$(btn).closest('tr').remove();
					renumberRows();
					calculateTotal();
				}
			});
		}

		// Renumber rows after deletion
		function renumberRows() {
			$('#detailTableBody tr').each(function(index) {
				$(this).find('td:first').text(index + 1);
				$(this).find('input[name$="[rec]"]').val(index + 1);
			});
		}

		// Find detail row by kd_brg
		function findDetailRow(kd_brg) {
			return $('#detailTableBody tr[data-kd-brg="' + kd_brg + '"]');
		}

		// Clear entry form
		function clearEntryForm() {
			$('#kd_brg').val('');
			$('#th_entry').val('');
			$('#hgd_entry').val('');
			$('#partsp_entry').val('');
			$('#lblBarang').text('');
			TEMP_PRODUCT = null;
		}

		// Calculate total
		function calculateTotal() {
			var totalItems = $('#detailTableBody tr').length;
			$('#totalItems').text(totalItems);
		}

		// Browse supplier functionality
		function browseSupplier() {
			$('#browseSupplierModal').modal('show');
			searchSupplier('');
		}

		function searchSupplier(query) {
			$.ajax({
				url: '{{ route('phturanharga.browse') }}',
				type: 'GET',
				data: {
					q: query,
					type: 'supplier'
				},
				success: function(response) {
					var tbody = $('#supplierTableBody');
					tbody.empty();

					if (response.length > 0) {
						response.forEach(function(item) {
							var row = '<tr>';
							row += '<td>' + item.kodes + '</td>';
							row += '<td>' + item.namas + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectSupplier(\'' +
								item.kodes + '\', \'' + item.namas + '\')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="3" class="text-center">No data found</td></tr>');
					}
				}
			});
		}

		function selectSupplier(kodes, namas) {
			$('#kodes').val(kodes);
			$('#namas').val(namas);
			$('#browseSupplierModal').modal('hide');
			$('#cara_bayar').focus();
		}

		// Browse product functionality
		function browseProduct() {
			var kodes = $('#kodes').val();
			if (!kodes) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Supplier kosong!'
				});
				return;
			}

			$('#browseProductModal').modal('show');
			searchProduct('');
		}

		function searchProduct(query) {
			var kodes = $('#kodes').val();
			var tgl_mulai = $('#tgl_mulai').val();

			$.ajax({
				url: '{{ route("phturanharga.browse") }}',
				type: 'GET',
				data: {
					q: query,
					type: 'product',
					kodes: kodes,
					tgl_mulai: tgl_mulai
				},
				success: function(response) {
					var tbody = $('#productTableBody');
					tbody.empty();

					if (response.length > 0) {
						response.forEach(function(item) {
							var row = '<tr>';
							row += '<td>' + item.KD_BRG + '</td>';
							row += '<td>' + item.NA_BRG + '</td>';
							row += '<td>' + (item.KET_KEM || '') + '</td>';
							row += '<td>' + (item.KET_UK || '') + '</td>';
							row += '<td class="text-right">' + formatNumber(item.HJ) + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectProduct(\'' +
								item.KD_BRG + '\')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="6" class="text-center">No data found</td></tr>');
					}
				}
			});
		}

		function selectProduct(kd_brg) {
			$('#kd_brg').val(kd_brg);
			$('#browseProductModal').modal('hide');
			handleKdBrgEnter(kd_brg);
		}

		// Save function (matching Delphi xsave)
		function simpan() {
			// Validation
			if (!validateDates()) {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Filter tanggal tidak sesuai!'
				});
				return;
			}

			var kodes = $('#kodes').val().trim();
			if (!kodes) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Supplier kosong!'
				});
				$('#kodes').focus();
				return;
			}

			var jam_mulai = $('#jam_mulai').val();
			var jam_sls = $('#jam_sls').val();

			if (!jam_mulai || jam_mulai == '00:00') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Jam Mulai : kosong!'
				});
				$('#jam_mulai').focus();
				return;
			}

			if (!jam_sls || jam_sls == '00:00') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Jam Selesai : kosong!'
				});
				$('#jam_sls').focus();
				return;
			}

			if (jam_mulai >= jam_sls) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Jam tidak sesuai!'
				});
				$('#jam_mulai').focus();
				return;
			}

			var detailCount = $('#detailTableBody tr').length;
			if (detailCount === 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Data barang masih kosong!'
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
				url: '{{ route("phturanharga.store") }}',
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
							window.location.href = '{{ route('phturanharga') }}';
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
			window.location.href = '{{ route("phturanharga") }}';
		}

		// Print function
		function printData(no_bukti) {
			$.ajax({
				url: "{{ route('phturanharga.print') }}",
				type: 'POST',
				data: {
					no_bukti: no_bukti,
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					if (response.data && response.data.length > 0) {
						var printWindow = window.open('', '_blank');
						var printContent = generatePrintContent(response.data);
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

		function generatePrintContent(data) {
			var content = `
				<!DOCTYPE html>
				<html>
				<head>
					<title>Laporan Turun Harga</title>
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
						<h2>LAPORAN TURUN HARGA</h2>
					</div>
					<div class="info">
						<table class="info-table">
							<tr>
								<td style="width: 100px;"><strong>No Bukti</strong></td>
								<td style="width: 250px;">: ${data[0].NO_BUKTI}</td>
								<td style="width: 100px;"><strong>Periode</strong></td>
								<td>: ${formatDate(data[0].TGL_MULAI)} s/d ${formatDate(data[0].TGL_SLS)}</td>
							</tr>
							<tr>
								<td><strong>Supplier</strong></td>
								<td>: ${data[0].NAMAS}</td>
								<td><strong>Kode</strong></td>
								<td>: ${data[0].KODES}</td>
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
								<th class="text-center" width="5%">No</th>
								<th width="10%">Kode Barang</th>
								<th width="25%">Nama Barang</th>
								<th width="10%">Kemasan</th>
								<th width="10%">Satuan</th>
								<th class="text-right" width="10%">HJ</th>
								<th class="text-right" width="10%">Turun Harga</th>
								<th class="text-right" width="10%">Harga Baru</th>
								<th class="text-right" width="10%">Partisipasi</th>
							</tr>
						</thead>
						<tbody>`;

			data.forEach((item, index) => {
				var harga_baru = parseFloat(item.HJ) - parseFloat(item.TH);
				content += `
					<tr>
						<td class="text-center">${index + 1}</td>
						<td>${item.KD_BRG}</td>
						<td>${item.NA_BRG}</td>
						<td>${item.ket_kem || ''}</td>
						<td>${item.ket_uk || ''}</td>
						<td class="text-right">${formatNumber(parseFloat(item.HJ))}</td>
						<td class="text-right">${formatNumber(parseFloat(item.TH))}</td>
						<td class="text-right">${formatNumber(harga_baru)}</td>
						<td class="text-right">${formatNumber(parseFloat(item.partsp))}</td>
					</tr>`;
			});

			content += `
						</tbody>
					</table>
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
	</script>
@endsection
