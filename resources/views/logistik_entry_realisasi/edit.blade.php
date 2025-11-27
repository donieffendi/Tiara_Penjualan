@extends('layouts.plain')

@section('content')
	<style>
		.card {}

		.form-control:focus {
			background-color: #E0FFFF !important;
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

		.col-md-6.pr-4 {
			padding-right: 2rem !important;
		}

		.col-md-6.pl-4 {
			padding-left: 2rem !important;
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

		.barcode-info {
			background-color: #f8f9fa;
			padding: 10px;
			margin-bottom: 15px;
			border-radius: 5px;
			border: 1px solid #dee2e6;
		}

		.barcode-info h5 {
			margin: 0;
			color: #495057;
		}
	</style>

	<div class="content-wrapper">
		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<h3 class="card-title">Entry Realisasi - {{ $status == 'simpan' ? 'New' : 'Edit' }}</h3>
							</div>
							<div class="card-body">
								<form action="{{ route('lentryrealisasi.store') }}" method="POST" name="entri" id="entri">
									@csrf
									<input type="hidden" id="status" name="status" value="{{ $status }}">

									<div class="tab-content mt-3">
										<div class="row">
											<!-- Left Column -->
											<div class="col-md-6 pr-4">
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="no_bukti" class="form-label">No Bukti</label>
													</div>
													<div class="col-md-9">
														<input type="text" class="form-control" id="no_bukti" name="no_bukti" value="{{ $header->no_bukti ?? '+' }}"
															{{ $status == 'edit' ? 'readonly' : '' }} placeholder="No Bukti" required>
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="tgl" class="form-label">Tanggal</label>
													</div>
													<div class="col-md-9">
														<input type="date" class="form-control" id="tgl" name="tgl"
															value="{{ $header && $header->tgl ? date('Y-m-d', strtotime($header->tgl)) : date('Y-m-d') }}" required>
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="divisi" class="form-label">Dept</label>
													</div>
													<div class="col-md-9">
														<input type="text" class="form-control" id="divisi" name="divisi" value="{{ $header->divisi ?? '' }}" placeholder="Dept">
													</div>
												</div>
											</div>

											<!-- Right Column -->
											<div class="col-md-6 pl-4">
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="no_po" class="form-label">Nomor Order</label>
													</div>
													<div class="col-md-9">
														<div class="input-group">
															<input type="text" class="form-control" id="no_po" name="no_po" value="{{ $header->no_po ?? '' }}" placeholder="No PO" required
																{{ $status == 'edit' ? 'readonly' : '' }}>
															@if ($status == 'simpan')
																<button type="button" class="btn btn-info" id="loadOrder" title="Load dari Order">
																	<i class="fas fa-download"></i>
																</button>
															@endif
														</div>
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="notes" class="form-label">Notes</label>
													</div>
													<div class="col-md-9">
														<textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Keterangan">{{ $header->notes ?? '' }}</textarea>
													</div>
												</div>
											</div>
										</div>

										<!-- Barcode Scanner Section -->
										<div class="row mt-3">
											<div class="col-md-12">
												<div class="barcode-info">
													<div class="row">
														<div class="col-md-4">
															<label for="barcode_input">Barcode</label>
															<input type="text" class="form-control" id="barcode_input" placeholder="Scan atau ketik barcode...">
														</div>
														<div class="col-md-4">
															<label for="qty_input">Layani / Qty</label>
															<input type="number" class="form-control" id="qty_input" step="0.01" placeholder="0.00" style="display:none;">
														</div>
														<div class="col-md-4">
															<label>&nbsp;</label>
															<h5 id="barang_info" style="padding-top: 8px;"></h5>
														</div>
													</div>
												</div>
											</div>
										</div>

										<!-- Detail Table -->
										<div class="row mt-3">
											<div class="col-md-12">
												<div class="card">
													<div class="card-header">
														<h5>Detail Item</h5>
														<button type="button" class="btn btn-success btn-sm float-right" onclick="addDetailRow()">
															<i class="fas fa-plus"></i> Add Item
														</button>
													</div>
													<div class="card-body">
														<div class="table-responsive">
															<table class="table-bordered table" id="detailTable">
																<thead>
																	<tr>
																		<th width="5%">No</th>
																		<th width="15%">Kode</th>
																		<th width="25%">Nama Barang</th>
																		<th width="10%">Kemasan</th>
																		<th width="8%">Order</th>
																		<th width="8%">Layani</th>
																		<th width="10%">Stok Gudang</th>
																		<th width="12%">Ket</th>
																		<th width="7%">Action</th>
																	</tr>
																</thead>
																<tbody id="detailTableBody">
																	@if ($status == 'edit' && !empty($detail))
																		@foreach ($detail as $index => $item)
																			<tr>
																				<td class="text-center">{{ $index + 1 }}</td>
																				<td>
																					<input type="text" class="form-control form-control-sm kd-brg" name="details[{{ $index }}][kd_brg]"
																						value="{{ $item->kd_brg }}" onblur="getBarangDetail(this)">
																					<input type="hidden" name="details[{{ $index }}][na_brg]" value="{{ $item->na_brg }}">
																					<input type="hidden" name="details[{{ $index }}][ket_kem]" value="{{ $item->ket_kem }}">
																					<input type="hidden" name="details[{{ $index }}][kdlaku]" value="{{ $item->kdlaku ?? '' }}">
																					<input type="hidden" name="details[{{ $index }}][barcode]" value="{{ $item->barcode ?? '' }}">
																				</td>
																				<td class="na-brg">{{ $item->na_brg }}</td>
																				<td class="ket-kem">{{ $item->ket_kem }}</td>
																				<td>
																					<input type="number" class="form-control form-control-sm qto" name="details[{{ $index }}][qto]"
																						value="{{ $item->qto }}" step="0.01" readonly>
																				</td>
																				<td>
																					<input type="number" class="form-control form-control-sm qty" name="details[{{ $index }}][qty]"
																						value="{{ $item->qty }}" step="0.01" onchange="calculateTotal()" required>
																				</td>
																				<td>
																					<input type="number" class="form-control form-control-sm stock" value="{{ $item->stok_gudang ?? 0 }}" readonly>
																				</td>
																				<td>
																					<input type="text" class="form-control form-control-sm ket" name="details[{{ $index }}][ket]"
																						value="{{ $item->ket ?? '' }}">
																				</td>
																				<td class="text-center">
																					<button type="button" class="btn btn-danger btn-sm" onclick="removeDetailRow(this)" title="Hapus">
																						<i class="fas fa-trash"></i>
																					</button>
																					<button type="button" class="btn btn-info btn-sm" onclick="browseBarang(this)" title="Browse">
																						<i class="fas fa-search"></i>
																					</button>
																				</td>
																			</tr>
																		@endforeach
																	@endif
																</tbody>
																<tfoot>
																	<tr>
																		<th colspan="5" class="text-right">Total Qty:</th>
																		<th><span id="totalQty">0.00</span></th>
																		<th colspan="3"></th>
																	</tr>
																</tfoot>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>

										<!-- Action Buttons -->
										<div class="col-md-12 form-group row mt-3">
											<div class="col-md-6">
												<button type="button" id='SAVEX' onclick='simpan()' class="btn btn-success">Save</button>
												<button type="button" id='CLOSEX' onclick="closeForm()" class="btn btn-outline-secondary">Close</button>
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
						<table class="table-bordered table" id="barangTable">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Nama Barang</th>
									<th>Sub</th>
									<th>Ukuran</th>
									<th>Kemasan</th>
									<th>Harga</th>
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
		var currentRow;
		var detailRowIndex = {{ $status == 'edit' && !empty($detail) ? count($detail) : 0 }};
		var barcodeTimer;
		var barcodeBuffer = '';

		$(document).ready(function() {
			// Enter key navigation
			$('body').on('keydown', 'input, select, textarea', function(e) {
				if (e.key === "Enter") {
					var self = $(this),
						form = self.parents('form:eq(0)'),
						focusable, next;
					focusable = form.find('input,select,textarea').filter(':visible:not([readonly])');
					next = focusable.eq(focusable.index(this) + 1);
					if (next.length) {
						next.focus().select();
					}
					return false;
				}
			});

			// Search barang with delay
			$('#searchBarang').on('keyup', function() {
				var query = $(this).val();
				if (query.length > 2) {
					searchBarang(query);
				} else if (query.length === 0) {
					searchBarang('');
				}
			});

			// No Bukti validation
			$('#no_bukti').on('keyup', function(e) {
				if (e.key === "Enter") {
					var no_bukti = $(this).val().trim();
					if (no_bukti && no_bukti !== '+' && $('#status').val() === 'simpan') {
						checkBuktiExists(no_bukti);
					}
				}
			});

			// Load order button
			$('#loadOrder').on('click', function() {
				var no_po = $('#no_po').val().trim();
				if (no_po) {
					loadFromOrder(no_po);
				} else {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Nomor Order tidak boleh kosong!'
					});
				}
			});

			// Barcode scanner with timer
			$('#barcode_input').on('keyup', function(e) {
				clearTimeout(barcodeTimer);

				if (e.key === "Enter") {
					var barcode = $(this).val().trim();
					if (barcode) {
						processBarcode(barcode);
					}
				} else {
					barcodeTimer = setTimeout(function() {
						var barcode = $('#barcode_input').val().trim();
						if (barcode) {
							processBarcode(barcode);
						}
					}, 500);
				}
			});

			// Qty input for barcode
			$('#qty_input').on('keydown', function(e) {
				if (e.key === 'e' || e.key === 'E') {
					e.preventDefault();
					var qty = parseFloat($(this).val()) || 0;
					var row = $('#detailTableBody tr').filter(function() {
						return $(this).find('.kd-brg').data('current') === true;
					}).first();

					if (row.length > 0) {
						var qto = parseFloat(row.find('.qto').val()) || 0;
						var stock = parseFloat(row.find('.stock').val()) || 0;

						if (qty > qto) {
							Swal.fire({
								title: 'Konfirmasi',
								text: 'Kirim melebihi permintaan, Lanjutkan?',
								icon: 'warning',
								showCancelButton: true,
								confirmButtonText: 'Ya, Lanjutkan',
								cancelButtonText: 'Batal'
							}).then((result) => {
								if (result.isConfirmed) {
									if (qty > stock) {
										Swal.fire('Warning', 'Qty melebihi stok gudang!', 'warning');
										row.find('.qty').val(0);
									} else {
										row.find('.qty').val(qty);
									}
								} else {
									row.find('.qty').val(0);
								}
								calculateTotal();
								resetBarcodeInput();
							});
						} else if (qty > stock) {
							Swal.fire('Warning', 'Qty melebihi stok gudang!', 'warning');
							row.find('.qty').val(0);
							calculateTotal();
							resetBarcodeInput();
						} else {
							row.find('.qty').val(qty);
							calculateTotal();
							resetBarcodeInput();
						}
					}
				} else if (e.key === 'b' || e.key === 'B') {
					e.preventDefault();
					$(this).val(0).select();
				}
			});

			// Set focus based on status
			@if ($status == 'simpan')
				$('#no_bukti').focus();
			@else
				$('#no_po').focus();
			@endif

			calculateTotal();
		});

		function resetBarcodeInput() {
			$('#barcode_input').val('').focus();
			$('#qty_input').val(0).hide();
			$('#barang_info').text('');
			$('#detailTableBody tr').find('.kd-brg').data('current', false);
		}

		function processBarcode(barcode) {
			$.ajax({
				url: '{{ route('lentryrealisasi.barang-detail') }}',
				type: 'GET',
				data: {
					barcode: barcode
				},
				success: function(response) {
					if (response.exists && response.data) {
						var found = false;
						$('#detailTableBody tr').each(function() {
							if ($(this).find('.kd-brg').val() === response.data.kd_brg) {
								found = true;
								$(this).find('.kd-brg').data('current', true);
								$('#barang_info').text(response.data.na_brg + ' ' + (response.data.ket_uk || ''));
								$('#qty_input').show().focus().select();
								return false;
							}
						});

						if (!found) {
							Swal.fire({
								icon: 'warning',
								title: 'Warning',
								text: 'Barang tidak ada dalam list order!'
							});
							resetBarcodeInput();
						}
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Warning',
							text: 'Barcode tidak ditemukan!'
						});
						resetBarcodeInput();
					}
				}
			});
		}

		function loadFromOrder(no_po) {
			Swal.fire({
				title: 'Loading...',
				text: 'Mohon tunggu',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading()
				}
			});

			$.ajax({
				url: '{{ route('lentryrealisasi.load-order') }}',
				type: 'GET',
				data: {
					no_po: no_po
				},
				success: function(response) {
					Swal.close();
					if (response.success && response.data.length > 0) {
						$('#detailTableBody').empty();
						detailRowIndex = 0;

						response.data.forEach(function(item) {
							addDetailRowWithData(item);
						});

						calculateTotal();
						$('#no_po').prop('readonly', true);
						$('#loadOrder').prop('disabled', true);

						Swal.fire({
							icon: 'success',
							title: 'Success',
							text: 'Data order berhasil dimuat'
						});
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Warning',
							text: response.message || 'Data order tidak ditemukan'
						});
					}
				},
				error: function() {
					Swal.close();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Gagal memuat data order'
					});
				}
			});
		}

		function addDetailRowWithData(item) {
			var newRow = `
				<tr>
					<td class="text-center">${detailRowIndex + 1}</td>
					<td>
						<input type="text" class="form-control form-control-sm kd-brg" name="details[${detailRowIndex}][kd_brg]"
							value="${item.kd_brg}" onblur="getBarangDetail(this)">
						<input type="hidden" name="details[${detailRowIndex}][na_brg]" value="${item.na_brg}">
						<input type="hidden" name="details[${detailRowIndex}][ket_kem]" value="${item.ket_kem || ''}">
						<input type="hidden" name="details[${detailRowIndex}][kdlaku]" value="${item.kdlaku || ''}">
						<input type="hidden" name="details[${detailRowIndex}][barcode]" value="">
					</td>
					<td class="na-brg">${item.na_brg}</td>
					<td class="ket-kem">${item.ket_kem || ''}</td>
					<td>
						<input type="number" class="form-control form-control-sm qto" name="details[${detailRowIndex}][qto]"
							value="${item.qty}" step="0.01" readonly>
					</td>
					<td>
						<input type="number" class="form-control form-control-sm qty" name="details[${detailRowIndex}][qty]"
							value="0" step="0.01" onchange="calculateTotal()" required>
					</td>
					<td>
						<input type="number" class="form-control form-control-sm stock" value="${item.stok_gudang || 0}" readonly>
					</td>
					<td>
						<input type="text" class="form-control form-control-sm ket" name="details[${detailRowIndex}][ket]" value="">
					</td>
					<td class="text-center">
						<button type="button" class="btn btn-danger btn-sm" onclick="removeDetailRow(this)" title="Hapus">
							<i class="fas fa-trash"></i>
						</button>
						<button type="button" class="btn btn-info btn-sm" onclick="browseBarang(this)" title="Browse">
							<i class="fas fa-search"></i>
						</button>
					</td>
				</tr>`;

			$('#detailTableBody').append(newRow);
			detailRowIndex++;
		}

		function checkBuktiExists(no_bukti) {
			$.ajax({
				url: '{{ route('lentryrealisasi.cek') }}',
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
						$('#no_bukti').val('+').focus();
					}
				}
			});
		}

		function addDetailRow() {
			var newRow = `
				<tr>
					<td class="text-center">${detailRowIndex + 1}</td>
					<td>
						<input type="text" class="form-control form-control-sm kd-brg" name="details[${detailRowIndex}][kd_brg]" onblur="getBarangDetail(this)">
						<input type="hidden" name="details[${detailRowIndex}][na_brg]">
						<input type="hidden" name="details[${detailRowIndex}][ket_kem]">
						<input type="hidden" name="details[${detailRowIndex}][kdlaku]">
						<input type="hidden" name="details[${detailRowIndex}][barcode]">
					</td>
					<td class="na-brg"></td>
					<td class="ket-kem"></td>
					<td>
						<input type="number" class="form-control form-control-sm qto" name="details[${detailRowIndex}][qto]" value="0" step="0.01" readonly>
					</td>
					<td>
						<input type="number" class="form-control form-control-sm qty" name="details[${detailRowIndex}][qty]" value="0" step="0.01" onchange="calculateTotal()" required>
					</td>
					<td>
						<input type="number" class="form-control form-control-sm stock" value="0" readonly>
					</td>
					<td>
						<input type="text" class="form-control form-control-sm ket" name="details[${detailRowIndex}][ket]">
					</td>
					<td class="text-center">
						<button type="button" class="btn btn-danger btn-sm" onclick="removeDetailRow(this)" title="Hapus">
							<i class="fas fa-trash"></i>
						</button>
						<button type="button" class="btn btn-info btn-sm" onclick="browseBarang(this)" title="Browse">
							<i class="fas fa-search"></i>
						</button>
					</td>
				</tr>`;

			$('#detailTableBody').append(newRow);
			detailRowIndex++;
			updateRowNumbers();
		}

		function removeDetailRow(button) {
			$(button).closest('tr').remove();
			calculateTotal();
			updateDetailIndexes();
			updateRowNumbers();
		}

		function updateRowNumbers() {
			$('#detailTableBody tr').each(function(index) {
				$(this).find('td:first').text(index + 1);
			});
		}

		function updateDetailIndexes() {
			$('#detailTableBody tr').each(function(index) {
				$(this).find('input[name*="[kd_brg]"]').attr('name', `details[${index}][kd_brg]`);
				$(this).find('input[name*="[na_brg]"]').attr('name', `details[${index}][na_brg]`);
				$(this).find('input[name*="[ket_kem]"]').attr('name', `details[${index}][ket_kem]`);
				$(this).find('input[name*="[kdlaku]"]').attr('name', `details[${index}][kdlaku]`);
				$(this).find('input[name*="[barcode]"]').attr('name', `details[${index}][barcode]`);
				$(this).find('input[name*="[qto]"]').attr('name', `details[${index}][qto]`);
				$(this).find('input[name*="[qty]"]').attr('name', `details[${index}][qty]`);
				$(this).find('input[name*="[ket]"]').attr('name', `details[${index}][ket]`);
			});
			detailRowIndex = $('#detailTableBody tr').length;
		}

		function calculateTotal() {
			var total = 0;
			$('#detailTableBody .qty').each(function() {
				var qty = parseFloat($(this).val()) || 0;
				total += qty;
			});
			$('#totalQty').text(total.toFixed(2));
		}

		function getBarangDetail(input) {
			var kd_brg = $(input).val().trim();
			if (kd_brg) {
				$.ajax({
					url: '{{ route('lentryrealisasi.barang-detail') }}',
					type: 'GET',
					data: {
						kd_brg: kd_brg
					},
					success: function(response) {
						if (response.exists && response.data) {
							var row = $(input).closest('tr');
							row.find('input[name*="[na_brg]"]').val(response.data.na_brg);
							row.find('input[name*="[ket_kem]"]').val(response.data.ket_kem || '');
							row.find('input[name*="[kdlaku]"]').val(response.data.kdlaku || '');
							row.find('input[name*="[barcode]"]').val(response.data.barcode || '');
							row.find('.na-brg').text(response.data.na_brg);
							row.find('.ket-kem').text(response.data.ket_kem || '');
							row.find('.stock').val(response.data.stok_gudang || 0);
						} else {
							Swal.fire({
								icon: 'warning',
								title: 'Warning',
								text: 'Barang tidak ditemukan!'
							});
							$(input).val('').focus();
						}
					}
				});
			}
		}

		function browseBarang(button) {
			currentRow = $(button).closest('tr');
			$('#browseBarangModal').modal('show');
			searchBarang('');
		}

		function searchBarang(query) {
			$.ajax({
				url: '{{ route('lentryrealisasi.browse') }}',
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
							row += '<td>' + (item.sub || '') + '</td>';
							row += '<td>' + (item.ket_uk || '') + '</td>';
							row += '<td>' + (item.ket_kem || '') + '</td>';
							row += '<td>' + (item.hb || 0) + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectBarang(\'' +
								item.kd_brg + '\', \'' + item.na_brg.replace(/'/g, "\\'") + '\', \'' + (item.ket_kem || '').replace(/'/g,
									"\\'") +
								'\', \'' + (item.kdlaku || '') + '\', \'' + (item.barcode || '') + '\')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="7" class="text-center">No data found</td></tr>');
					}
				}
			});
		}

		function selectBarang(kd_brg, na_brg, ket_kem, kdlaku, barcode) {
			if (currentRow) {
				currentRow.find('.kd-brg').val(kd_brg);
				currentRow.find('input[name*="[na_brg]"]').val(na_brg);
				currentRow.find('input[name*="[ket_kem]"]').val(ket_kem);
				currentRow.find('input[name*="[kdlaku]"]').val(kdlaku);
				currentRow.find('input[name*="[barcode]"]').val(barcode);
				currentRow.find('.na-brg').text(na_brg);
				currentRow.find('.ket-kem').text(ket_kem);

				// Get stock info
				getBarangDetail(currentRow.find('.kd-brg')[0]);
			}
			$('#browseBarangModal').modal('hide');
		}

		function simpan() {
			// Validation
			if ($('#no_bukti').val().trim() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'No Bukti tidak boleh kosong.'
				});
				$('#no_bukti').focus();
				return;
			}

			if ($('#no_po').val().trim() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Nomor Order tidak boleh kosong.'
				});
				$('#no_po').focus();
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

			// Validate each detail row
			var valid = true;
			var hasQtyZero = false;
			var exceedStock = false;

			$('#detailTableBody tr').each(function() {
				var kd_brg = $(this).find('.kd-brg').val().trim();
				var qty = parseFloat($(this).find('.qty').val()) || 0;
				var stock = parseFloat($(this).find('.stock').val()) || 0;

				if (!kd_brg) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Kode barang tidak boleh kosong.'
					});
					$(this).find('.kd-brg').focus();
					valid = false;
					return false;
				}

				if (qty === 0) {
					hasQtyZero = true;
				}

				if (qty > stock) {
					exceedStock = true;
				}
			});

			if (!valid) return;

			if (hasQtyZero) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Qty Layani ada yang kosong!'
				});
				return;
			}

			if (exceedStock) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Qty Layani melebihi Stok Gudang!'
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
				url: '{{ route('lentryrealisasi.store') }}',
				type: 'POST',
				data: $('#entri').serialize(),
				success: function(response) {
					Swal.close();
					if (response.success) {
						Swal.fire({
							title: 'Success!',
							text: response.message,
							icon: 'success',
							confirmButtonText: 'OK'
						}).then(() => {
							@if ($status == 'simpan')
								// For new entry, clear form
								$('#no_bukti').val('+');
								$('#no_po').val('').prop('readonly', false);
								$('#divisi').val('');
								$('#notes').val('');
								$('#tgl').val('{{ date('Y-m-d') }}');
								$('#detailTableBody').empty();
								$('#totalQty').text('0.00');
								$('#loadOrder').prop('disabled', false);
								detailRowIndex = 0;
								$('#no_bukti').focus();
							@else
								// For edit, close form
								window.location.href = '{{ route('lentryrealisasi') }}';
							@endif
						});
					} else {
						Swal.fire({
							title: 'Error!',
							text: response.message,
							icon: 'error',
							confirmButtonText: 'OK'
						});

						if (response.message.includes('sudah ada')) {
							$('#no_bukti').val('+').focus();
						}
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

		function closeForm() {
			window.location.href = '{{ route('lentryrealisasi') }}';
		}
	</script>
@endsection
