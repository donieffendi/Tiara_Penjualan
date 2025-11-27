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
								<h3 class="card-title">Penukaran Hadiah - {{ $status == 'simpan' ? 'New' : 'Edit' }}</h3>
							</div>
							<div class="card-body">
								<form action="{{ route('phpenukaranhadiah.store') }}" method="POST" name="entri" id="entri">
									@csrf
									<input type="hidden" id="status" name="status" value="{{ $status }}">

									<!-- Header Form -->
									<div class="card mb-3">
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

												<!-- No Resi -->
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="no_resi" class="form-label">No Resi</label>
														</div>
														<div class="col-md-8">
															<input type="text" class="form-control form-control-sm" id="no_resi" name="no_resi" value="{{ $header->NO_PO ?? '' }}"
																placeholder="No Resi" required {{ $status == 'edit' ? 'readonly' : '' }}>
														</div>
													</div>
												</div>

												<!-- Kode Customer -->
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="kodec" class="form-label">Kode</label>
														</div>
														<div class="col-md-8">
															<input type="text" class="form-control form-control-sm" id="kodec" name="kodec" value="{{ $header->kodes ?? '' }}"
																placeholder="Kode" readonly>
														</div>
													</div>
												</div>
											</div>

											<div class="row">
												<!-- Nama Customer -->
												<div class="col-md-6">
													<div class="form-group row">
														<div class="col-md-2">
															<label for="namac" class="form-label">Nama Customer</label>
														</div>
														<div class="col-md-10">
															<input type="text" class="form-control form-control-sm" id="namac" name="namac" value="{{ $header->namas ?? '' }}"
																placeholder="Nama Customer" readonly>
														</div>
													</div>
												</div>

												<!-- Total Qty -->
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="total_qty" class="form-label">Total Qty</label>
														</div>
														<div class="col-md-8">
															<input type="number" class="form-control form-control-sm" id="total_qty" name="total_qty" step="0.01"
																value="{{ $header->TOTAL_QTY ?? 0 }}" readonly>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Entry Fields Row (Only for new entry) -->
									@if ($status == 'simpan')
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
															<div class="col-md-4">
																<div class="form-group">
																	<label for="kd_brgh" class="form-label">Kode Barang</label>
																	<div class="input-group input-group-sm">
																		<input type="text" class="form-control form-control-sm" id="kd_brgh" name="kd_brgh" placeholder="Kode Barang">
																		<button type="button" class="btn btn-sm btn-primary" onclick="browseProduct()">
																			<i class="fas fa-search"></i>
																		</button>
																	</div>
																</div>
															</div>

															<!-- Qty -->
															<div class="col-md-3">
																<div class="form-group">
																	<label for="qty_entry" class="form-label">Qty</label>
																	<input type="number" class="form-control form-control-sm" id="qty_entry" name="qty_entry" step="0.01" placeholder="0.00">
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
									@endif

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
																	<th width="5%">No</th>
																	<th width="15%">Kode Barang</th>
																	<th width="40%">Nama Barang</th>
																	<th width="15%">Qty</th>
																	@if ($status == 'simpan')
																		<th width="10%">Action</th>
																	@endif
																</tr>
															</thead>
															<tbody id="detailTableBody">
																@if ($status == 'edit' && !empty($detail))
																	@foreach ($detail as $index => $item)
																		<tr data-kd-brgh="{{ $item->KD_BRGH }}" data-no-id="{{ $item->NO_ID ?? 0 }}">
																			<td class="text-center">{{ $index + 1 }}</td>
																			<td>{{ $item->KD_BRGH }}</td>
																			<td>{{ $item->NA_BRGH }}</td>
																			<td class="text-right">{{ number_format($item->QTY, 2) }}</td>
																			<input type="hidden" name="details[{{ $index }}][no_id]" value="{{ $item->NO_ID ?? 0 }}">
																			<input type="hidden" name="details[{{ $index }}][rec]" value="{{ $item->REC ?? $index + 1 }}">
																			<input type="hidden" name="details[{{ $index }}][kd_brgh]" value="{{ $item->KD_BRGH }}">
																			<input type="hidden" name="details[{{ $index }}][na_brgh]" value="{{ $item->NA_BRGH }}">
																			<input type="hidden" name="details[{{ $index }}][qty]" value="{{ $item->QTY }}">
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
											@if ($status == 'simpan' || ($header && $header->posted == 0))
												<button type="button" id="SAVEX" onclick="simpan()" class="btn btn-success">
													<i class="fas fa-save"></i> Save
												</button>
											@endif
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

	<!-- Browse Product Modal -->
	<div class="modal fade" id="browseProductModal" tabindex="-1" aria-labelledby="browseProductModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseProductModalLabel">Browse Produk Hadiah</h5>
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
				$('#tgl').focus();
			@else
				$('#tgl').focus();
			@endif

			// Calculate total on page load
			calculateTotal();

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

			// No Resi exit handler
			$('#no_resi').on('blur', function() {
				handleNoResiExit($(this).val().trim());
			});
		});

		handleEnterKey(element) {
			var $element = $(element);
			var id = $element.attr('id');

			switch (id) {
				case 'tgl':
					$('#no_resi').focus().select();
					break;
				case 'no_resi':
					handleNoResiExit($element.val().trim());
					break;
				case 'kd_brgh':
					handleKdBrghEnter($element.val().trim());
					break;
				case 'qty_entry':
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

		// Handle no_resi exit (matching Delphi txtno_resiExit)
		function handleNoResiExit(no_resi) {
			if (!no_resi) {
				return;
			}

			// Show loading
			Swal.fire({
				title: 'Loading...',
				text: 'Mengambil data dari nota penjualan',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading()
				}
			});

			$.ajax({
				url: '{{ route('phpenukaranhadiah.sales-data') }}',
				type: 'GET',
				data: {
					no_resi: no_resi
				},
				success: function(response) {
					Swal.close();

					if (response.success) {
						// Set header data
						$('#kodec').val(response.header.kodeC);
						$('#namac').val(response.header.namaC);

						// Clear existing detail
						$('#detailTableBody').empty();
						detailRowIndex = 0;

						// Add detail items
						if (response.detail && response.detail.length > 0) {
							response.detail.forEach(function(item) {
								addNewRowFromSales(item);
							});
							calculateTotal();
							$('#kd_brgh').focus();
						} else {
							Swal.fire({
								icon: 'warning',
								title: 'Warning',
								text: 'Tidak ada detail barang pada nota ini'
							});
						}
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.message
						});
						$('#no_resi').val('').focus();
					}
				},
				error: function(xhr) {
					Swal.close();
					var errorMessage = 'Gagal mengambil data';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMessage = xhr.responseJSON.message;
					}
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMessage
					});
					$('#no_resi').val('').focus();
				}
			});
		}

		// Handle kd_brgh enter (matching Delphi cxGrid1DBTableView1EditKeyDown)
		function handleKdBrghEnter(kd_brgh) {
			if (kd_brgh) {
				$.ajax({
					url: '{{ route('phpenukaranhadiah.detail') }}',
					type: 'GET',
					data: {
						kd_brgh: kd_brgh
					},
					success: function(response) {
						if (response.success && response.exists && response.data) {
							TEMP_PRODUCT = response.data;
							$('#lblBarang').text(response.data.kd_brgh + ' - ' + response.data.na_brgh);
							$('#qty_entry').val('').focus().select();
						} else {
							// Show browse modal
							browseProduct();
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

		// Add to detail table
		function addToDetail() {
			var kd_brgh = $('#kd_brgh').val().trim();
			var qty = parseFloat($('#qty_entry').val()) || 0;

			if (!kd_brgh || !TEMP_PRODUCT) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Pilih produk terlebih dahulu!'
				});
				$('#kd_brgh').focus();
				return;
			}

			if (qty <= 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Qty harus lebih dari 0!'
				});
				$('#qty_entry').focus();
				return;
			}

			// Check if already exists
			var existingRow = findDetailRow(kd_brgh);
			if (existingRow.length > 0) {
				// Update existing row
				existingRow.find('td:eq(3)').text(formatNumber(qty));
				var index = existingRow.index();
				$('input[name="details[' + index + '][qty]"]').val(qty);
			} else {
				// Add new row
				addNewRow(TEMP_PRODUCT, qty);
			}

			clearEntryForm();
			calculateTotal();
			$('#kd_brgh').focus();
		}

		// Add new row from sales data
		function addNewRowFromSales(item) {
			var newRow = `
				<tr data-kd-brgh="${item.KD_BRGH}" data-no-id="0">
					<td class="text-center">${detailRowIndex + 1}</td>
					<td>${item.KD_BRGH}</td>
					<td>${item.NA_BRGH}</td>
					<td class="text-right">${formatNumber(item.qty)}</td>
					<td class="text-center">
						<button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Hapus">
							<i class="fas fa-trash"></i>
						</button>
					</td>
					<input type="hidden" name="details[${detailRowIndex}][no_id]" value="0">
					<input type="hidden" name="details[${detailRowIndex}][rec]" value="${detailRowIndex + 1}">
					<input type="hidden" name="details[${detailRowIndex}][kd_brgh]" value="${item.KD_BRGH}">
					<input type="hidden" name="details[${detailRowIndex}][na_brgh]" value="${item.NA_BRGH}">
					<input type="hidden" name="details[${detailRowIndex}][qty]" value="${item.qty}">
				</tr>`;

			$('#detailTableBody').append(newRow);
			detailRowIndex++;
		}

		// Add new row to detail table
		function addNewRow(product, qty) {
			var newRow = `
				<tr data-kd-brgh="${product.kd_brgh}" data-no-id="0">
					<td class="text-center">${detailRowIndex + 1}</td>
					<td>${product.kd_brgh}</td>
					<td>${product.na_brgh}</td>
					<td class="text-right">${formatNumber(qty)}</td>
					<td class="text-center">
						<button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Hapus">
							<i class="fas fa-trash"></i>
						</button>
					</td>
					<input type="hidden" name="details[${detailRowIndex}][no_id]" value="0">
					<input type="hidden" name="details[${detailRowIndex}][rec]" value="${detailRowIndex + 1}">
					<input type="hidden" name="details[${detailRowIndex}][kd_brgh]" value="${product.kd_brgh}">
					<input type="hidden" name="details[${detailRowIndex}][na_brgh]" value="${product.na_brgh}">
					<input type="hidden" name="details[${detailRowIndex}][qty]" value="${qty}">
				</tr>`;

			$('#detailTableBody').append(newRow);
			detailRowIndex++;
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

		// Find detail row by kd_brgh
		function findDetailRow(kd_brgh) {
			return $('#detailTableBody tr[data-kd-brgh="' + kd_brgh + '"]');
		}

		// Clear entry form
		function clearEntryForm() {
			$('#kd_brgh').val('');
			$('#qty_entry').val('');
			$('#lblBarang').text('');
			TEMP_PRODUCT = null;
		}

		// Calculate total
		function calculateTotal() {
			var totalItems = $('#detailTableBody tr').length;
			var totalQty = 0;

			$('#detailTableBody tr').each(function() {
				var qty = parseFloat($(this).find('input[name$="[qty]"]').val()) || 0;
				totalQty += qty;
			});

			$('#totalItems').text(totalItems);
			$('#total_qty').val(totalQty.toFixed(2));
		}

		// Browse product functionality
		function browseProduct() {
			$('#browseProductModal').modal('show');
			searchProduct('');
		}

		function searchProduct(query) {
			$.ajax({
				url: '{{ route('phpenukaranhadiah.browse') }}',
				type: 'GET',
				data: {
					q: query
				},
				success: function(response) {
					var tbody = $('#productTableBody');
					tbody.empty();

					if (response.length > 0) {
						response.forEach(function(item) {
							var row = '<tr>';
							row += '<td>' + item.kd_brgh + '</td>';
							row += '<td>' + item.na_brgh + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectProduct(\'' +
								item.kd_brgh + '\', \'' + escapeHtml(item.na_brgh) + '\')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="3" class="text-center">No data found</td></tr>');
					}
				}
			});
		}

		function selectProduct(kd_brgh, na_brgh) {
			TEMP_PRODUCT = {
				kd_brgh: kd_brgh,
				na_brgh: na_brgh
			};
			$('#kd_brgh').val(kd_brgh);
			$('#lblBarang').text(kd_brgh + ' - ' + na_brgh);
			$('#browseProductModal').modal('hide');
			$('#qty_entry').focus();
		}

		// Save function (matching Delphi MSaveClick)
		function simpan() {
			// Validation
			var tgl = $('#tgl').val();
			var no_resi = $('#no_resi').val().trim();
			var kodec = $('#kodec').val().trim();

			if (!tgl) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Tanggal kosong!'
				});
				$('#tgl').focus();
				return;
			}

			if (!no_resi) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'No Resi kosong!'
				});
				$('#no_resi').focus();
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

			// Calculate total before submit
			calculateTotal();

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
				url: '{{ route('phpenukaranhadiah.store') }}',
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
							window.location.href = '{{ route('phpenukaranhadiah') }}';
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
			window.location.href = '{{ route('phpenukaranhadiah') }}';
		}

		// Print function
		function printData(no_bukti) {
			$.ajax({
				url: "{{ route('phpenukaranhadiah.print') }}",
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
					<title>Laporan Penukaran Hadiah</title>
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
						<h2>LAPORAN PENUKARAN HADIAH</h2>
					</div>
					<div class="info">
						<table class="info-table">
							<tr>
								<td style="width: 100px;"><strong>No Bukti</strong></td>
								<td style="width: 250px;">: ${data[0].NO_BUKTI}</td>
								<td style="width: 100px;"><strong>Tanggal</strong></td>
								<td>: ${formatDate(data[0].TGL)}</td>
							</tr>
							<tr>
								<td><strong>No Resi</strong></td>
								<td>: ${data[0].NO_PO}</td>
								<td><strong>Customer</strong></td>
								<td>: ${data[0].namas}</td>
							</tr>
						</table>
					</div>
					<table>
						<thead>
							<tr>
								<th class="text-center" width="10%">No</th>
								<th width="20%">Kode Barang</th>
								<th width="50%">Nama Barang</th>
								<th class="text-right" width="20%">Qty</th>
							</tr>
						</thead>
						<tbody>`;

			data.forEach((item, index) => {
				content += `
					<tr>
						<td class="text-center">${index + 1}</td>
						<td>${item.KD_BRGH}</td>
						<td>${item.NA_BRGH}</td>
						<td class="text-right">${formatNumber(parseFloat(item.QTY))}</td>
					</tr>`;
			});

			content += `
						</tbody>
					</table>
					<div style="margin-top: 30px;">
						<table class="info-table" style="width: 100%;">
							<tr>
								<td style="width: 50%; text-align: center;">
									<div style="margin-bottom: 60px;">Dibuat Oleh,</div>
									<div>___________________</div>
								</td>
								<td style="width: 50%; text-align: center;">
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
