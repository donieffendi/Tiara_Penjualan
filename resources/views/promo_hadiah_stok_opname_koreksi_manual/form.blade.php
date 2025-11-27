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
								<h3 class="card-title">Stok Opname Koreksi Manual - {{ $status == 'simpan' ? 'New' : 'Edit' }}</h3>
							</div>
							<div class="card-body">
								<form action="{{ route('phsokoreksimanual.store') }}" method="POST" name="entri" id="entri">
									@csrf
									<input type="hidden" id="status" name="status" value="{{ $status }}">

									<div class="card mb-3">
										<div class="card-header bg-light">
											<h6 class="mb-0">Header Information</h6>
										</div>
										<div class="card-body">
											<div class="row">
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

												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="tgl" class="form-label">Tanggal</label>
														</div>
														<div class="col-md-8">
															<input type="date" class="form-control form-control-sm" id="tgl" name="tgl"
																value="{{ $header ? date('Y-m-d', strtotime($header->tgl)) : date('Y-m-d') }}" required>
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group row">
														<div class="col-md-2">
															<label for="notes" class="form-label">Notes</label>
														</div>
														<div class="col-md-10">
															<input type="text" class="form-control form-control-sm" id="notes" name="notes" value="{{ $header->notes ?? '' }}"
																placeholder="Notes">
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

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

															<div class="col-md-2">
																<div class="form-group">
																	<label for="qty_entry" class="form-label">Qty</label>
																	<input type="number" step="0.01" class="form-control form-control-sm" id="qty_entry" name="qty_entry" placeholder="0.00">
																</div>
															</div>

															<div class="col-md-3">
																<div class="form-group">
																	<label for="ket_entry" class="form-label">Ket</label>
																	<input type="text" class="form-control form-control-sm" id="ket_entry" name="ket_entry" placeholder="Keterangan">
																</div>
															</div>

															<div class="col-md-3">
																<div class="form-group">
																	<label class="form-label">&nbsp;</label>
																	<div>
																		<button type="button" class="btn btn-success btn-sm" onclick="addToDetail()">
																			<i class="fas fa-plus"></i> Add
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

									<div class="row mt-2">
										<div class="col-md-12">
											<div class="card">
												<div class="card-header d-flex justify-content-between">
													<h6 class="mb-0">Detail Barang</h6>
													<div>
														<span class="badge badge-primary">Total Items: <span id="totalItems">0</span></span>
														<span class="badge badge-info">Total Qty: <span id="totalQty">0.00</span></span>
													</div>
												</div>
												<div class="card-body p-2">
													<div class="table-responsive">
														<table class="table-bordered table-sm table-hover table" id="detailTable">
															<thead class="table-dark">
																<tr>
																	<th width="5%">No</th>
																	<th width="15%">Kode</th>
																	<th width="35%">Nama Barang</th>
																	<th width="12%">Qty</th>
																	<th width="25%">Ket</th>
																	@if ($status == 'simpan')
																		<th width="8%">Action</th>
																	@endif
																</tr>
															</thead>
															<tbody id="detailTableBody">
																@if ($status == 'edit' && !empty($detail))
																	@foreach ($detail as $index => $item)
																		<tr data-kd-brg="{{ $item->kd_brg }}">
																			<td class="text-center">{{ $index + 1 }}</td>
																			<td>{{ $item->kd_brg }}</td>
																			<td>{{ $item->na_brg }}</td>
																			<td class="text-right">{{ number_format($item->qty, 2) }}</td>
																			<td>{{ $item->ket ?? '' }}</td>
																			<input type="hidden" name="details[{{ $index }}][no_id]" value="{{ $item->no_id ?? 0 }}">
																			<input type="hidden" name="details[{{ $index }}][rec]" value="{{ $index + 1 }}">
																			<input type="hidden" name="details[{{ $index }}][kd_brg]" value="{{ $item->kd_brg }}">
																			<input type="hidden" name="details[{{ $index }}][na_brg]" value="{{ $item->na_brg }}">
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

									<div class="row mt-3">
										<div class="col-md-12">
											@if ($status == 'simpan')
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

	<div class="modal fade" id="browseProductModal" tabindex="-1" aria-labelledby="browseProductModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
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
									<th>Qty</th>
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
			@if ($status == 'simpan')
				$('#tgl').focus();
			@else
				$('#notes').focus();
			@endif

			calculateTotal();

			$('#searchProduct').on('keyup', function() {
				var query = $(this).val();
				setTimeout(function() {
					searchProduct(query);
				}, 300);
			});

			$('body').on('keydown', function(e) {
				if (e.key === "Enter") {
					e.preventDefault();
					handleEnterKey(e.target);
					return false;
				}
			});
		});

		function handleEnterKey(element) {
			var $element = $(element);
			var id = $element.attr('id');

			switch (id) {
				case 'tgl':
					$('#notes').focus().select();
					break;
				case 'notes':
					$('#kd_brg').focus().select();
					break;
				case 'kd_brg':
					handleKdBrgEnter($element.val().trim());
					break;
				case 'qty_entry':
					$('#ket_entry').focus().select();
					break;
				case 'ket_entry':
					addToDetail();
					break;
				default:
					var form = $element.parents('form:eq(0)');
					var focusable = form.find('input,select,textarea,button').filter(':visible:not([readonly]):not([disabled])');
					var next = focusable.eq(focusable.index(element) + 1);
					if (next.length) {
						next.focus().select();
					}
					break;
			}
		}

		function handleKdBrgEnter(kd_brg) {
			if (kd_brg) {
				$.ajax({
					url: '{{ route('phsokoreksimanual.detail') }}',
					type: 'GET',
					data: {
						kd_brgh: kd_brg
					},
					success: function(response) {
						if (response.success && response.exists && response.data) {
							TEMP_PRODUCT = response.data;
							$('#lblBarang').text(response.data.kd_brgh + ' - ' + response.data.na_brgh + ' (Qty: ' + formatNumber(response.data
								.qty) + ')');
							$('#qty_entry').val('').focus().select();
						} else {
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

		function addToDetail() {
			var kd_brg = $('#kd_brg').val().trim();
			var qty = parseFloat($('#qty_entry').val()) || 0;
			var ket = $('#ket_entry').val().trim();

			if (!kd_brg || !TEMP_PRODUCT) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Pilih produk terlebih dahulu!'
				});
				$('#kd_brg').focus();
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

			var existingRow = findDetailRow(kd_brg);
			if (existingRow.length > 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Produk sudah ada dalam list!'
				});
				clearEntryForm();
				return;
			}

			addNewRow(TEMP_PRODUCT, qty, ket);
			clearEntryForm();
			calculateTotal();
			$('#kd_brg').focus();
		}

		function addNewRow(product, qty, ket) {
			var newRow = `
			<tr data-kd-brg="${product.kd_brgh}">
				<td class="text-center">${detailRowIndex + 1}</td>
				<td>${product.kd_brgh}</td>
				<td>${product.na_brgh}</td>
				<td class="text-right">
					<input type="number" step="0.01" class="form-control form-control-sm qty-input" name="details[${detailRowIndex}][qty]" value="${qty}" onchange="calculateTotal()">
				</td>
				<td>
					<input type="text" class="form-control form-control-sm" name="details[${detailRowIndex}][ket]" value="${ket}">
				</td>
				<td class="text-center">
					<button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Hapus">
						<i class="fas fa-trash"></i>
					</button>
				</td>
				<input type="hidden" name="details[${detailRowIndex}][no_id]" value="0">
				<input type="hidden" name="details[${detailRowIndex}][rec]" value="${detailRowIndex + 1}">
				<input type="hidden" name="details[${detailRowIndex}][kd_brg]" value="${product.kd_brgh}">
				<input type="hidden" name="details[${detailRowIndex}][na_brg]" value="${product.na_brgh}">
			</tr>`;

			$('#detailTableBody').append(newRow);
			detailRowIndex++;
		}

		function removeRow(btn) {
			Swal.fire({
				title: 'Konfirmasi',
				text: 'Hapus item ini?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Ya, Hapus',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					$(btn).closest('tr').remove();
					renumberRows();
					calculateTotal();
				}
			});
		}

		function renumberRows() {
			$('#detailTableBody tr').each(function(index) {
				$(this).find('td:first').text(index + 1);
				$(this).find('input[name*="[rec]"]').val(index + 1);
			});
		}

		function findDetailRow(kd_brg) {
			return $('#detailTableBody tr[data-kd-brg="' + kd_brg + '"]');
		}

		function clearEntryForm() {
			$('#kd_brg').val('');
			$('#qty_entry').val('');
			$('#ket_entry').val('');
			$('#lblBarang').text('');
			TEMP_PRODUCT = null;
		}

		function calculateTotal() {
			var totalItems = $('#detailTableBody tr').length;
			var totalQty = 0;

			$('#detailTableBody tr').each(function() {
				var qty = parseFloat($(this).find('.qty-input').val()) || parseFloat($(this).find('input[name*="[qty]"]').val()) || 0;
				totalQty += qty;
			});

			$('#totalItems').text(totalItems);
			$('#totalQty').text(formatNumber(totalQty));
		}

		function browseProduct() {
			$('#browseProductModal').modal('show');
			searchProduct('');
			$('#searchProduct').focus();
		}

		function searchProduct(query) {
			$.ajax({
				url: '{{ route('phsokoreksimanual.browse') }}',
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
							row += '<td class="text-right">' + formatNumber(item.qty) + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectProduct(\'' +
								item.kd_brgh + '\', \'' + escapeHtml(item.na_brgh) + '\', ' + item.qty + ')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="4" class="text-center">No data found</td></tr>');
					}
				}
			});
		}

		function selectProduct(kd_brg, na_brg, qty) {
			TEMP_PRODUCT = {
				kd_brgh: kd_brg,
				na_brgh: na_brg,
				qty: qty
			};
			$('#kd_brg').val(kd_brg);
			$('#lblBarang').text(kd_brg + ' - ' + na_brg + ' (Qty: ' + formatNumber(qty) + ')');
			$('#browseProductModal').modal('hide');
			$('#qty_entry').focus();
		}

		function simpan() {
			var tgl = $('#tgl').val();

			if (!tgl) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Tanggal harus diisi!'
				});
				$('#tgl').focus();
				return;
			}

			var detailCount = $('#detailTableBody tr').length;
			if (detailCount === 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Detail barang masih kosong!'
				});
				return;
			}

			var totalQty = 0;
			$('#detailTableBody tr').each(function() {
				var qty = parseFloat($(this).find('.qty-input').val()) || 0;
				totalQty += qty;
			});

			$('<input>').attr({
				type: 'hidden',
				name: 'total_qty',
				value: totalQty
			}).appendTo('#entri');

			Swal.fire({
				title: 'Konfirmasi',
				text: 'Apakah data sudah benar?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#28a745',
				cancelButtonColor: '#6c757d',
				confirmButtonText: 'Ya, Simpan',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					Swal.fire({
						title: 'Menyimpan...',
						text: 'Mohon tunggu',
						allowOutsideClick: false,
						didOpen: () => {
							Swal.showLoading()
						}
					});

					$.ajax({
						url: '{{ route('phsokoreksimanual.store') }}',
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
									window.location.href = '{{ route('phsokoreksimanual') }}';
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
			});
		}

		function closeForm() {
			window.location.href = '{{ route('phsokoreksimanual') }}';
		}

		function printData(no_bukti) {
			$.ajax({
				url: "{{ route('phsokoreksimanual.print') }}",
				type: 'POST',
				data: {
					no_bukti: no_bukti,
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					if (response.success && response.data && response.data.length > 0) {
						var printWindow = window.open('', '_blank');
						var printContent = generatePrintContent(response.data, response.toko);
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

		function generatePrintContent(data, toko) {
			var content = `
			<!DOCTYPE html>
			<html>
			<head>
				<title>Laporan Stok Opname Koreksi Manual</title>
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
					<h2>LAPORAN STOK OPNAME KOREKSI MANUAL</h2>
					<h4>${toko || ''}</h4>
				</div>
				<div class="info">
					<table class="info-table">
						<tr>
							<td style="width: 100px;"><strong>No Bukti</strong></td>
							<td>: ${data[0].NO_BUKTI}</td>
						</tr>
					</table>
				</div>
				<table>
					<thead>
						<tr>
							<th class="text-center" width="10%">No</th>
							<th width="20%">Kode Barang</th>
							<th width="40%">Nama Barang</th>
							<th class="text-right" width="15%">Qty</th>
							<th class="text-right" width="15%">Saldo</th>
						</tr>
					</thead>
					<tbody>`;

			data.forEach((item, index) => {
				content += `
				<tr>
					<td class="text-center">${index + 1}</td>
					<td>${item.KD_BRG}</td>
					<td>${item.NA_BRG}</td>
					<td class="text-right">${formatNumber(parseFloat(item.QTY || 0))}</td>
					<td class="text-right">${formatNumber(parseFloat(item.SALDO || 0))}</td>
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

		function formatNumber(num) {
			return parseFloat(num).toLocaleString('id-ID', {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2
			});
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
