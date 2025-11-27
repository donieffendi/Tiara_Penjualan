@extends('layouts.plain')

@section('content')
	<style>
		.card {}

		.form-control:focus {
			background-color: #E0FFFF !important;
		}

		.nav-item .nav-link.active {
			background-color: red !important;
			color: white !important;
		}

		.content-header {
			padding: 0 !important;
		}

		.form-group.row {
			align-items: center !important;
			margin-bottom: 1rem !important;
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
								<h3 class="card-title">Entry Transaksi - {{ $status == 'simpan' ? 'New' : 'Edit' }}</h3>
							</div>
							<div class="card-body">
								<form action="{{ route('lentrytransaksi.store') }}" method="POST" name="entri" id="entri">
									@csrf
									<input type="hidden" id="status" name="status" value="{{ $status }}">

									<!-- Header Form -->
									<div class="row">
										<!-- No Bukti -->
										<div class="col-md-4">
											<div class="form-group row">
												<div class="col-md-4">
													<label for="no_bukti" class="form-label">No Bukti</label>
												</div>
												<div class="col-md-8">
													<input type="text" class="form-control form-control-sm" id="no_bukti" name="no_bukti"
														value="{{ $status == 'simpan' ? '+' : $header->no_bukti ?? '' }}" {{ $status == 'edit' ? 'readonly' : '' }} placeholder="No Bukti"
														required>
												</div>
											</div>
										</div>

										<!-- Notes -->
										<div class="col-md-8">
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
									</div>

									<div class="row">
										<!-- Tanggal -->
										<div class="col-md-4">
											<div class="form-group row">
												<div class="col-md-4">
													<label for="tgl" class="form-label">Tanggal</label>
												</div>
												<div class="col-md-8">
													<input type="date" class="form-control form-control-sm" id="tgl" name="tgl"
														value="{{ $header && $header->tgl ? date('Y-m-d', strtotime($header->tgl)) : date('Y-m-d') }}" required>
												</div>
											</div>
										</div>

										<!-- Entry Fields Row -->
										<div class="col-md-8">
											<div class="row">
												<!-- Sub Item/Barcode -->
												<div class="col-md-3">
													<div class="form-group">
														<label for="kd_brg" class="form-label">Sub Item/Barcode</label>
														<input type="text" class="form-control form-control-sm" id="kd_brg" name="kd_brg" placeholder="Kode/Barcode">
													</div>
												</div>

												<!-- Qty -->
												<div class="col-md-2">
													<div class="form-group">
														<label for="qty" class="form-label">Qty</label>
														<input type="number" class="form-control form-control-sm" id="qty" name="qty" step="0.01" placeholder="0">
													</div>
												</div>

												<!-- Catatan -->
												<div class="col-md-7">
													<div class="form-group">
														<label for="ket" class="form-label">Catatan</label>
														<input type="text" class="form-control form-control-sm" id="ket" name="ket" placeholder="Catatan">
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Detail Table -->
									<div class="row mt-3">
										<div class="col-md-12">
											<div class="card">
												<div class="card-header d-flex justify-content-between">
													<h6 class="mb-0">Detail Item - Total Qty: <span id="totalQty">0.00</span></h6>
												</div>
												<div class="card-body p-2">
													<div class="table-responsive">
														<table class="table-bordered table-sm table table-fixed" id="detailTable">
															<thead>
																<tr>
																	<th width="5%">No</th>
																	<th width="15%">Kode</th>
																	<th width="30%">Nama Barang</th>
																	<th width="15%">Kemasan</th>
																	<th width="10%">Qty</th>
																	<th width="10%">Type</th>
																	<th width="15%">Ket</th>
																</tr>
															</thead>
															<tbody id="detailTableBody">
																@if ($status == 'edit' && !empty($detail))
																	@foreach ($detail as $index => $item)
																		<tr>
																			<td class="text-center">{{ $index + 1 }}</td>
																			<td>{{ $item->kd_brg }}</td>
																			<td>{{ $item->na_brg }}</td>
																			<td>{{ $item->ket_kem ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->qty, 2) }}</td>
																			<td>{{ $item->type ?? 'IN' }}</td>
																			<td>{{ $item->ket ?? '' }}</td>
																			<input type="hidden" name="details[{{ $index }}][no_id]" value="{{ $item->no_id ?? 0 }}">
																			<input type="hidden" name="details[{{ $index }}][rec]" value="{{ $item->rec ?? $index + 1 }}">
																			<input type="hidden" name="details[{{ $index }}][kd_brg]" value="{{ $item->kd_brg }}">
																			<input type="hidden" name="details[{{ $index }}][na_brg]" value="{{ $item->na_brg }}">
																			<input type="hidden" name="details[{{ $index }}][ket_kem]" value="{{ $item->ket_kem ?? '' }}">
																			<input type="hidden" name="details[{{ $index }}][qty]" value="{{ $item->qty }}">
																			<input type="hidden" name="details[{{ $index }}][ket]" value="{{ $item->ket ?? '' }}">
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
											<button type="button" id="SAVEX" onclick="simpan()" class="btn btn-success">Save</button>
											<button type="button" id="CLOSEX" onclick="closeForm()" class="btn btn-outline-secondary">Close</button>
											@if ($status == 'edit')
												<button type="button" onclick="printData('{{ $header->no_bukti ?? '' }}')" class="btn btn-info">Print</button>
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

	<!-- Browse Barang Modal -->
	<div class="modal fade" id="browseBarangModal" tabindex="-1" aria-labelledby="browseBarangModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseBarangModalLabel">Browse Barang</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<input type="text" class="form-control" id="searchBarang" placeholder="Cari barang...">
					</div>
					<div class="table-responsive">
						<table class="table-bordered table-sm table" id="barangTable">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Nama Barang</th>
									<th>Ukuran</th>
									<th>Kemasan</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="barangTableBody">
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
		var KD_BRGX = '';

		$(document).ready(function() {
			// Set focus based on status (matching Delphi FormShow)
			@if ($status == 'simpan')
				$('#no_bukti').focus();
			@else
				$('#kd_brg').focus();
			@endif

			// Calculate total on page load
			calculateTotal();

			// Search barang with delay
			$('#searchBarang').on('keyup', function() {
				var query = $(this).val();
				if (query.length > 2) {
					searchBarang(query);
				} else if (query.length === 0) {
					searchBarang('');
				}
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

		// Handle Enter key navigation (matching Delphi logic)
		function handleEnterKey(element) {
			var $element = $(element);
			var id = $element.attr('id');

			switch (id) {
				case 'no_bukti':
					if ($('#status').val() === 'simpan' && $element.val().trim()) {
						checkBuktiExists($element.val().trim());
					} else {
						$('#notes').focus().select();
					}
					break;
				case 'notes':
					$('#kd_brg').focus().select();
					break;
				case 'kd_brg':
					handleKdBrgEnter($element.val().trim());
					break;
				case 'qty':
					handleQtyEnter();
					break;
				case 'ket':
					handleKetEnter();
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

		// Handle kd_brg enter (matching Delphi txtkd_brgKeyDown)
		function handleKdBrgEnter(kodex) {
			if (kodex) {
				$.ajax({
					url: '{{ route('lentrytransaksi.barang-detail') }}',
					type: 'GET',
					data: {
						kd_brg: kodex
					},
					success: function(response) {
						if (response.exists && response.data) {
							KD_BRGX = response.data.kd_brg;

							// Check if item already exists in detail
							var existingRow = findDetailRow(KD_BRGX);
							if (existingRow) {
								$('#qty').focus().select();
							} else {
								// Add new row to memory (matching Delphi dxMemData1.Append)
								addToDetailMemory(response.data);
								$('#qty').focus().select();
							}
						} else {
							Swal.fire({
								icon: 'warning',
								title: 'Warning',
								text: 'Salah sub-item!'
							});
							$('#kd_brg').focus().select();
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal mengambil data barang'
						});
					}
				});
			}
		}

		// Handle qty enter (matching Delphi txtQTYKeyDown)
		function handleQtyEnter() {
			var qty = parseFloat($('#qty').val()) || 0;

			if (qty > 0 && KD_BRGX) {
				var existingRow = findDetailRow(KD_BRGX);
				if (existingRow) {
					// Update existing qty (matching Delphi logic)
					var currentQty = parseFloat(existingRow.find('input[name*="[qty]"]').val()) || 0;
					var newQty = currentQty + qty;
					existingRow.find('input[name*="[qty]"]').val(newQty);
					updateDetailRow(existingRow, newQty);
					calculateTotal();
					$('#ket').focus().select();
				} else {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Entri sub item dulu!'
					});
					$('#kd_brg').focus().select();
				}
			} else {
				$('#ket').focus().select();
			}
		}

		// Handle ket enter (matching Delphi txtKetKeyDown)
		function handleKetEnter() {
			var ket = $('#ket').val().trim();

			if (KD_BRGX) {
				var existingRow = findDetailRow(KD_BRGX);
				if (existingRow) {
					// Update ket
					existingRow.find('input[name*="[ket]"]').val(ket);
					updateDetailRowDisplay(existingRow, null, null, ket);

					// Reset form (matching Delphi reset logic)
					$('#kd_brg').val('');
					$('#qty').val('');
					$('#ket').val('');
					KD_BRGX = '';
					$('#kd_brg').focus();
				} else {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Entri sub item dulu!'
					});
					$('#kd_brg').focus().select();
				}
			}
		}

		// Add item to detail memory (matching Delphi dxMemData1.Append logic)
		function addToDetailMemory(barangData) {
			var newRow = `
				<tr data-kd-brg="${barangData.kd_brg}">
					<td class="text-center">${detailRowIndex + 1}</td>
					<td>${barangData.kd_brg}</td>
					<td>${barangData.na_brg}</td>
					<td>${barangData.ket_kem || ''}</td>
					<td class="text-right">0.00</td>
					<td>IN</td>
					<td></td>
					<input type="hidden" name="details[${detailRowIndex}][no_id]" value="0">
					<input type="hidden" name="details[${detailRowIndex}][rec]" value="${detailRowIndex + 1}">
					<input type="hidden" name="details[${detailRowIndex}][kd_brg]" value="${barangData.kd_brg}">
					<input type="hidden" name="details[${detailRowIndex}][na_brg]" value="${barangData.na_brg}">
					<input type="hidden" name="details[${detailRowIndex}][ket_kem]" value="${barangData.ket_kem || ''}">
					<input type="hidden" name="details[${detailRowIndex}][qty]" value="0">
					<input type="hidden" name="details[${detailRowIndex}][ket]" value="">
				</tr>`;

			$('#detailTableBody').append(newRow);
			detailRowIndex++;
		}

		// Find detail row by kd_brg
		function findDetailRow(kd_brg) {
			return $('#detailTableBody tr[data-kd-brg="' + kd_brg + '"]');
		}

		// Update detail row data
		function updateDetailRow(row, qty) {
			row.find('input[name*="[qty]"]').val(qty);
			row.find('td:nth-child(5)').text(qty.toFixed(2));
		}

		// Update detail row display
		function updateDetailRowDisplay(row, qty, type, ket) {
			if (qty !== null) {
				row.find('td:nth-child(5)').text(qty.toFixed(2));
			}
			if (type !== null) {
				row.find('td:nth-child(6)').text(type);
			}
			if (ket !== null) {
				row.find('td:nth-child(7)').text(ket);
			}
		}

		// Calculate total qty (matching Delphi hitung procedure)
		function calculateTotal() {
			var total = 0;
			$('#detailTableBody input[name*="[qty]"]').each(function() {
				var qty = parseFloat($(this).val()) || 0;
				total += qty;
			});
			$('#totalQty').text(total.toFixed(2));
		}

		// Check if no_bukti exists
		function checkBuktiExists(no_bukti) {
			$.ajax({
				url: '{{ route('lentrytransaksi.cek') }}',
				type: 'GET',
				data: {
					no_bukti: no_bukti
				},
				success: function(response) {
					if (response.exists) {
						Swal.fire({
							title: 'Warning!',
							text: 'No Bukti sudah ada!',
							icon: 'warning',
							confirmButtonText: 'OK'
						});
						$('#no_bukti').val('').focus();
					}
				}
			});
		}

		// Browse barang functionality
		function browseBarang() {
			$('#browseBarangModal').modal('show');
			searchBarang('');
		}

		function searchBarang(query) {
			$.ajax({
				url: '{{ route('lentrytransaksi.browse') }}',
				type: 'GET',
				data: {
					q: query
				},
				success: function(response) {
					var tbody = $('#barangTableBody');
					tbody.empty();

					if (response.length > 0) {
						response.forEach(function(item) {
							var row = '<tr>';
							row += '<td>' + item.kd_brg + '</td>';
							row += '<td>' + item.na_brg + '</td>';
							row += '<td>' + (item.ket_uk || '') + '</td>';
							row += '<td>' + (item.ket_kem || '') + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectBarang(\'' +
								item.kd_brg + '\', \'' + item.na_brg + '\', \'' + (item.ket_kem || '') + '\')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="5" class="text-center">No data found</td></tr>');
					}
				}
			});
		}

		function selectBarang(kd_brg, na_brg, ket_kem) {
			$('#kd_brg').val(kd_brg);
			$('#browseBarangModal').modal('hide');
			handleKdBrgEnter(kd_brg);
		}

		// Save function (matching Delphi MSaveClick)
		function simpan() {
			// Validation matching Delphi checkx procedure
			var periode = '{{ is_array(session('periode')) ? session('periode')['periode'] ?? date('Y-m') : session('periode', date('Y-m')) }}';
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
					text: 'Periode tidak sama!'
				});
				return;
			}

			if (year != periodeYear) {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Periode tidak sama!'
				});
				return;
			}

			// Basic validation
			if ($('#no_bukti').val().trim() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'No Bukti tidak boleh kosong.'
				});
				$('#no_bukti').focus();
				return;
			}

			var detailCount = $('#detailTableBody tr').length;
			if (detailCount === 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Detail item tidak boleh kosong.'
				});
				return;
			}

			// Validate each detail row has qty > 0
			var valid = true;
			$('#detailTableBody input[name*="[qty]"]').each(function() {
				var qty = parseFloat($(this).val()) || 0;
				if (qty <= 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Semua item harus memiliki qty > 0.'
					});
					valid = false;
					return false;
				}
			});

			if (!valid) return;

			// Show loading
			Swal.fire({
				title: 'Menyimpan...',
				text: 'Mohon tunggu',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading()
				}
			});

			// Calculate total and update form
			calculateTotal();

			// Submit form via AJAX
			$.ajax({
				url: '{{ route('lentrytransaksi.store') }}',
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
							@if ($status == 'simpan')
								// Clear form for new entry
								clearForm();
							@else
								// Close form for edit
								window.location.href = '{{ route('lentrytransaksi') }}';
							@endif
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

		// Clear form for new entry
		function clearForm() {
			$('#no_bukti').val('+');
			$('#notes').val('');
			$('#tgl').val('{{ date('Y-m-d') }}');
			$('#kd_brg').val('');
			$('#qty').val('');
			$('#ket').val('');
			$('#detailTableBody').empty();
			$('#totalQty').text('0.00');
			detailRowIndex = 0;
			KD_BRGX = '';
			$('#no_bukti').focus();
		}

		// Close form
		function closeForm() {
			window.location.href = '{{ route('lentrytransaksi') }}';
		}

		// Print function
		function printData(no_bukti) {
			$.ajax({
				url: "{{ route('lentrytransaksi.print') }}",
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
			return `
				<!DOCTYPE html>
				<html>
				<head>
					<title>Laporan Entry Transaksi Logistik</title>
					<style>
						body { font-family: Arial, sans-serif; font-size: 12px; }
						table { width: 100%; border-collapse: collapse; margin-top: 10px; }
						th, td { border: 1px solid #000; padding: 5px; text-align: left; }
						th { background-color: #f0f0f0; font-weight: bold; }
						.text-center { text-align: center; }
						.text-right { text-align: right; }
						.header { text-align: center; margin-bottom: 20px; }
					</style>
				</head>
				<body>
					<div class="header">
						<h2>TRANSAKSI HARIAN LOGISTIK</h2>
						<p>No Bukti: ${data[0].no_bukti}</p>
						<p>Tanggal: ${data[0].tgl}</p>
						<p>User: ${data[0].usrnm}</p>
						<p>Notes: ${data[0].notes}</p>
					</div>
					<table>
						<thead>
							<tr>
								<th>No</th>
								<th>Sub</th>
								<th>Supp</th>
								<th>Kode</th>
								<th>Nama Barang</th>
								<th>Ukuran</th>
								<th>Kemasan</th>
								<th>Harga</th>
								<th>Qty</th>
								<th>Type</th>
								<th>Ket</th>
							</tr>
						</thead>
						<tbody>
							${data.map((item, index) => `
											<tr>
												<td class="text-center">${index + 1}</td>
												<td>${item.sub || ''}</td>
												<td>${item.supp || ''}</td>
												<td>${item.kd || ''}</td>
												<td>${item.na_brg}</td>
												<td>${item.ket_uk || ''}</td>
												<td>${item.ket_kem || ''}</td>
												<td class="text-right">${item.hj || 0}</td>
												<td class="text-right">${item.qty}</td>
												<td>${item.type || ''}</td>
												<td>${item.ket || ''}</td>
											</tr>
										`).join('')}
						</tbody>
					</table>
				</body>
				</html>`;
		}
	</script>
@endsection
