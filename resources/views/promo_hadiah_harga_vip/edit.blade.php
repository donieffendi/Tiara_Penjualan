@extends('layouts.plain')

@section('content')
	<style>
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
								<h3 class="card-title">Harga VIP - {{ $status == 'simpan' ? 'New' : 'Edit' }}</h3>
							</div>
							<div class="card-body">
								<form action="{{ route('phhargavip.store') }}" method="POST" name="entri" id="entri">
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
												<!-- Notes -->
												<div class="col-md-12">
													<div class="form-group row">
														<div class="col-md-1">
															<label for="notes" class="form-label">Notes</label>
														</div>
														<div class="col-md-11">
															<input type="text" class="form-control form-control-sm" id="notes" name="notes" value="{{ $header->notes ?? '' }}"
																placeholder="Keterangan">
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
														<div class="col-md-4">
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

														<!-- Harga VIP -->
														<div class="col-md-3">
															<div class="form-group">
																<label for="hjvip_entry" class="form-label">Harga VIP</label>
																<input type="number" class="form-control form-control-sm" id="hjvip_entry" name="hjvip_entry" step="0.01" placeholder="0.00">
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
																	<th width="5%">No</th>
																	<th width="15%">Kode Barang</th>
																	<th width="30%">Nama Barang</th>
																	<th width="12%">Kemasan</th>
																	<th width="12%">Satuan</th>
																	<th width="12%">HJ Reguler</th>
																	<th width="12%">HJ VIP</th>
																	<th width="10%">Action</th>
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
																			<td class="text-right">{{ number_format($item->HJ ?? 0, 2) }}</td>
																			<td class="text-right">{{ number_format($item->HJVIP, 2) }}</td>
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
																			<input type="hidden" name="details[{{ $index }}][hjvip]" value="{{ $item->HJVIP }}">
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

		// Handle Enter key navigation
		function handleEnterKey(element) {
			var $element = $(element);
			var id = $element.attr('id');

			switch (id) {
				case 'tgl_mulai':
					$('#tgl_sls').focus().select();
					break;
				case 'tgl_sls':
					$('#notes').focus().select();
					break;
				case 'notes':
					$('#kd_brg').focus().select();
					break;
				case 'kd_brg':
					handleKdBrgEnter($element.val().trim());
					break;
				case 'hjvip_entry':
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

		// Handle kd_brg enter (matching Delphi cxGrid1DBTableView1EditKeyDown)
		function handleKdBrgEnter(kd_brg) {
			if (kd_brg) {
				// Check if product already exists in detail
				var existingRow = findDetailRow(kd_brg);
				if (existingRow.length > 0) {
					$('#hjvip_entry').focus().select();
					return;
				}

				$.ajax({
					url: '{{ route('phhargavip.detail') }}',
					type: 'GET',
					data: {
						kd_brg: kd_brg,
						type: 'product'
					},
					success: function(response) {
						if (response.success && response.exists && response.data) {
							TEMP_PRODUCT = response.data;
							$('#lblBarang').text(response.data.NA_BRG + ' | HJ: ' + formatNumber(response.data.HJ));

							// Set to entry form
							$('#hjvip_entry').val('').focus().select();
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

		// Add to detail table (matching Delphi btnOKClick)
		function addToDetail() {
			var kd_brg = $('#kd_brg').val().trim();
			var hjvip = parseFloat($('#hjvip_entry').val()) || 0;

			if (!kd_brg || !TEMP_PRODUCT) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Pilih produk terlebih dahulu!'
				});
				$('#kd_brg').focus();
				return;
			}

			if (hjvip <= 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Harga VIP harus lebih dari 0!'
				});
				$('#hjvip_entry').focus();
				return;
			}

			// Check if already exists
			var existingRow = findDetailRow(kd_brg);
			if (existingRow.length > 0) {
				// Update existing row
				existingRow.find('td:eq(6)').text(formatNumber(hjvip));

				var index = existingRow.index();
				$('input[name="details[' + index + '][hjvip]"]').val(hjvip);
			} else {
				// Add new row
				addNewRow(TEMP_PRODUCT, hjvip);
			}

			clearEntryForm();
			calculateTotal();
			$('#kd_brg').focus();
		}

		// Add new row to detail table
		function addNewRow(product, hjvip) {
			var hj = parseFloat(product.HJ) || 0;

			var newRow = `
				<tr data-kd-brg="${product.KD_BRG}" data-no-id="0">
					<td class="text-center">${detailRowIndex + 1}</td>
					<td>${product.KD_BRG}</td>
					<td>${product.NA_BRG}</td>
					<td>${product.KET_KEM || ''}</td>
					<td>${product.KET_UK || ''}</td>
					<td class="text-right">${formatNumber(hj)}</td>
					<td class="text-right">${formatNumber(hjvip)}</td>
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
					<input type="hidden" name="details[${detailRowIndex}][hjvip]" value="${hjvip}">
					<input type="hidden" name="details[${detailRowIndex}][ket]" value="">
				</tr>`;

			$('#detailTableBody').append(newRow);
			detailRowIndex++;
		}

		// Edit row (matching Delphi cxGrid1DBTableView1CellClick)
		function editRow(btn) {
			var row = $(btn).closest('tr');
			var kd_brg = row.data('kd-brg');

			$('#kd_brg').val(kd_brg);
			handleKdBrgEnter(kd_brg);
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
			$('#hjvip_entry').val('');
			$('#lblBarang').text('');
			TEMP_PRODUCT = null;
		}

		// Calculate total
		function calculateTotal() {
			var totalItems = $('#detailTableBody tr').length;
			$('#totalItems').text(totalItems);
		}

		// Browse product functionality
		function browseProduct() {
			$('#browseProductModal').modal('show');
			searchProduct('');
		}

		function searchProduct(query) {
			$.ajax({
				url: '{{ route('phhargavip.browse') }}',
				type: 'GET',
				data: {
					q: query,
					type: 'product'
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

		// Save function (matching Delphi MSaveClick)
		function simpan() {
			// Check period is closed
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
				url: '{{ route('phhargavip.store') }}',
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
							window.location.href = '{{ route('phhargavip') }}';
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
			window.location.href = '{{ route('phhargavip') }}';
		}

		// Print function
		function printData(no_bukti) {
			$.ajax({
				url: "{{ route('phhargavip.print') }}",
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
					<title>Laporan Harga VIP</title>
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
						<h2>LAPORAN HARGA VIP</h2>
					</div>
					<div class="info">
						<table class="info-table">
							<tr>
								<td style="width: 100px;"><strong>No Bukti</strong></td>
								<td>: ${data[0].NO_BUKTI}</td>
								<td style="width: 100px;"><strong>Tanggal</strong></td>
								<td>: ${formatDate(data[0].TGL)}</td>
							</tr>
						</table>
					</div>
					<table>
						<thead>
							<tr>
								<th class="text-center" width="5%">No</th>
								<th width="15%">Kode Barang</th>
								<th width="30%">Nama Barang</th>
								<th width="12%">Kemasan</th>
								<th width="12%">Satuan</th>
								<th class="text-right" width="13%">HJ Reguler</th>
								<th class="text-right" width="13%">HJ VIP</th>
							</tr>
						</thead>
						<tbody>`;

			data.forEach((item, index) => {
				content += `
					<tr>
						<td class="text-center">${index + 1}</td>
						<td>${item.KD_BRG}</td>
						<td>${item.NA_BRG}</td>
						<td>${item.ket_kem || ''}</td>
						<td>${item.ket_uk || ''}</td>
						<td class="text-right">${formatNumber(parseFloat(item.hj))}</td>
						<td class="text-right">${formatNumber(parseFloat(item.HJVIP))}</td>
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
