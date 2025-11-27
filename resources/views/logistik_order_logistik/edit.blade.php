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

		.form-group.row .form-check-input {
			margin-top: 0;
		}

		.col-md-2,
		.col-md-4,
		.col-md-6,
		.col-md-8,
		.col-md-10 {
			padding-top: 0;
			padding-bottom: 0;
		}

		.d-flex.align-items-center {
			height: 38px;
		}

		.col-md-6.pr-4 {
			padding-right: 2rem !important;
		}

		.col-md-6.pl-4 {
			padding-left: 2rem !important;
		}

		/* Grid styles */
		#detailGrid {
			height: 300px;
			overflow-y: auto;
		}

		.grid-row {
			border-bottom: 1px solid #dee2e6;
			padding: 5px 0;
		}

		.grid-row:hover {
			background-color: #f8f9fa;
		}

		.grid-input {
			border: none;
			background: transparent;
			width: 100%;
		}

		.grid-input:focus {
			background-color: #E0FFFF !important;
			border: 1px solid #007bff;
		}

		.btn-grid {
			padding: 2px 8px;
			font-size: 12px;
		}
	</style>

	<div class="content-wrapper">
		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form action="{{ route('lorderlogistik.store') }}" method="POST" name="entri" id="entri">
									@csrf
									<input type="hidden" id="tipx" name="tipx" value="{{ $tipx }}">
									<input type="hidden" id="idx" name="idx" value="{{ $idx }}">
									<input type="hidden" id="no_bukti_hidden" name="no_bukti_hidden" value="{{ $no_bukti }}">

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
															{{ $tipx == 'new' ? '' : 'readonly' }} placeholder="Auto Generate">
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="tgl" class="form-label">Tanggal</label>
													</div>
													<div class="col-md-9">
														<input type="date" class="form-control" id="tgl" name="tgl"
															value="{{ $header ? date('Y-m-d', strtotime($header->tgl)) : date('Y-m-d') }}" required>
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="ket" class="form-label">Keterangan</label>
													</div>
													<div class="col-md-9">
														<input type="text" class="form-control" id="ket" name="ket" value="{{ $header->ket ?? '' }}"
															placeholder="Masukkan Keterangan" required>
													</div>
												</div>
											</div>
											<!-- Right Column -->
											<div class="col-md-6 pl-4">
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="divisi" class="form-label">Divisi</label>
													</div>
													<div class="col-md-9">
														<input type="text" class="form-control" id="divisi" name="divisi" value="{{ $header->divisi ?? '' }}"
															placeholder="Masukkan Divisi">
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="total_qty" class="form-label">Total Qty</label>
													</div>
													<div class="col-md-9">
														<input type="number" class="form-control" id="total_qty" name="total_qty" value="{{ $header->total_qty ?? 0 }}" readonly>
													</div>
												</div>
											</div>
										</div>

										<!-- Detail Grid -->
										<div class="row mt-3">
											<div class="col-12">
												<h5>Detail Barang</h5>
												<div class="table-responsive">
													<table class="table-bordered table" id="detailTable">
														<thead class="table-dark">
															<tr>
																<th width="50px">No</th>
																<th width="120px">Kode Barang</th>
																<th width="250px">Nama Barang</th>
																<th width="100px">Qty</th>
																<th width="100px">Kemasan</th>
																<th width="80px">Stok</th>
																<th width="80px">Action</th>
															</tr>
														</thead>
														<tbody id="detailBody">
															@if (!empty($details))
																@foreach ($details as $index => $detail)
																	<tr class="detail-row" data-index="{{ $index }}">
																		<td>{{ $index + 1 }}</td>
																		<td>
																			<input type="text" class="form-control grid-input kd-brg" name="details[{{ $index }}][kd_brg]"
																				value="{{ $detail->KD_BRG }}" onchange="getBarangDetail(this, {{ $index }})">
																			<input type="hidden" name="details[{{ $index }}][no_id]" value="{{ $detail->NO_ID }}">
																		</td>
																		<td>
																			<input type="text" class="form-control grid-input na-brg" name="details[{{ $index }}][na_brg]"
																				value="{{ $detail->NA_BRG }}" readonly>
																		</td>
																		<td>
																			<input type="number" class="form-control grid-input qty" name="details[{{ $index }}][qty]" value="{{ $detail->qty }}"
																				onchange="calculateTotal()" min="0" step="0.01">
																		</td>
																		<td>
																			<input type="text" class="form-control grid-input kemasan" name="details[{{ $index }}][kemasan]"
																				value="{{ $detail->ket_kem }}" readonly>
																		</td>
																		<td>
																			<input type="text" class="form-control grid-input stok" readonly>
																			<input type="hidden" name="details[{{ $index }}][klaku]" value="{{ $detail->kdlaku }}">
																			<input type="hidden" name="details[{{ $index }}][sub]" value="{{ $detail->sub }}">
																			<input type="hidden" name="details[{{ $index }}][kdbar]" value="{{ $detail->kdbar }}">
																		</td>
																		<td>
																			<button type="button" class="btn btn-danger btn-grid" onclick="deleteRow(this)">Del</button>
																		</td>
																	</tr>
																@endforeach
															@else
																@for ($i = 0; $i < 10; $i++)
																	<tr class="detail-row" data-index="{{ $i }}">
																		<td>{{ $i + 1 }}</td>
																		<td>
																			<input type="text" class="form-control grid-input kd-brg" name="details[{{ $i }}][kd_brg]"
																				onchange="getBarangDetail(this, {{ $i }})">
																			<input type="hidden" name="details[{{ $i }}][no_id]" value="0">
																		</td>
																		<td>
																			<input type="text" class="form-control grid-input na-brg" name="details[{{ $i }}][na_brg]" readonly>
																		</td>
																		<td>
																			<input type="number" class="form-control grid-input qty" name="details[{{ $i }}][qty]" onchange="calculateTotal()"
																				min="0" step="0.01" value="0">
																		</td>
																		<td>
																			<input type="text" class="form-control grid-input kemasan" name="details[{{ $i }}][kemasan]" readonly>
																		</td>
																		<td>
																			<input type="text" class="form-control grid-input stok" readonly>
																			<input type="hidden" name="details[{{ $i }}][klaku]">
																			<input type="hidden" name="details[{{ $i }}][sub]">
																			<input type="hidden" name="details[{{ $i }}][kdbar]">
																		</td>
																		<td>
																			<button type="button" class="btn btn-danger btn-grid" onclick="deleteRow(this)">Del</button>
																		</td>
																	</tr>
																@endfor
															@endif
														</tbody>
													</table>
												</div>
												<button type="button" class="btn btn-info" onclick="addRow()">Add Row</button>
											</div>
										</div>

										<!-- Navigation Buttons -->
										<div class="col-md-12 form-group row mt-3">
											<div class="col-md-4">
												<button type="button" id='TOPX' onclick="location.href='{{ route('lorderlogistik.edit') }}?tipx=top'"
													class="btn btn-outline-primary" {{ $tipx == 'new' ? 'hidden' : '' }}>Top</button>
												<button type="button" id='PREVX' onclick="location.href='{{ route('lorderlogistik.edit') }}?kodex={{ $no_bukti }}&tipx=prev'"
													class="btn btn-outline-primary" {{ $tipx == 'new' ? 'hidden' : '' }}>Prev</button>
												<button type="button" id='NEXTX' onclick="location.href='{{ route('lorderlogistik.edit') }}?kodex={{ $no_bukti }}&tipx=next'"
													class="btn btn-outline-primary" {{ $tipx == 'new' ? 'hidden' : '' }}>Next</button>
												<button type="button" id='BOTTOMX' onclick="location.href='{{ route('lorderlogistik.edit') }}?tipx=bottom'"
													class="btn btn-outline-primary" {{ $tipx == 'new' ? 'hidden' : '' }}>Bottom</button>
											</div>
											<div class="col-md-5">
												<button type="button" id='NEWX' onclick="location.href='{{ route('lorderlogistik.edit') }}?tipx=new'" class="btn btn-warning"
													{{ $tipx == 'new' ? 'hidden' : '' }}>New</button>
												<button type="button" id='EDITX' onclick='enableEdit()' class="btn btn-secondary"
													{{ $tipx == 'new' ? 'hidden' : '' }}>Edit</button>
												<button type="button" id='UNDOX'
													onclick="location.href='{{ route('lorderlogistik.edit') }}?no_bukti={{ $no_bukti }}&tipx=undo'" class="btn btn-info"
													{{ $tipx == 'new' ? 'hidden' : '' }}>Undo</button>
												<button type="button" id='SAVEX' onclick='simpan()' class="btn btn-success">Save</button>
											</div>
											<div class="col-md-3">
												@if ($tipx != 'new' && $no_bukti)
													<button type="button" id='HAPUSX' onclick="hapusOrder()" class="btn btn-outline-danger">Hapus</button>
												@endif
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
									<th>Kemasan</th>
									<th>Stok</th>
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
		var currentRowIndex = 0;
		var rowCounter = {{ !empty($details) ? count($details) : 10 }};

		$(document).ready(function() {
			// Enter key navigation
			$('body').on('keydown', 'input, select', function(e) {
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

			// Calculate total on page load
			calculateTotal();

			// Search barang with delay
			$('#searchBarang').on('keyup', function() {
				var query = $(this).val();
				if (query.length > 2) {
					searchBarang(query);
				}
			});
		});

		// Get barang detail when code is entered
		function getBarangDetail(element, index) {
			var kd_brg = $(element).val().trim();
			if (kd_brg === '') return;

			$.ajax({
				url: '{{ route('lorderlogistik.barang-detail') }}',
				type: 'GET',
				data: {
					kd_brg: kd_brg
				},
				success: function(response) {
					if (response.success) {
						var row = $(element).closest('tr');
						row.find('[name="details[' + index + '][na_brg]"]').val(response.data.na_brg);
						row.find('[name="details[' + index + '][kemasan]"]').val(response.data.ket_kem);
						row.find('.stok').val(response.data.ak00);
						row.find('[name="details[' + index + '][klaku]"]').val(response.data.kdlaku);
						row.find('[name="details[' + index + '][sub]"]').val(response.data.sub);
						row.find('[name="details[' + index + '][kdbar]"]').val(response.data.kdbar);

						// Move to qty field
						row.find('.qty').focus();
					} else {
						Swal.fire({
							title: 'Warning!',
							text: response.message,
							icon: 'warning',
							confirmButtonText: 'OK'
						});
						// Open browse modal if not found
						currentRowIndex = index;
						$('#browseBarangModal').modal('show');
						searchBarang('');
					}
				},
				error: function() {
					Swal.fire({
						title: 'Error!',
						text: 'Terjadi kesalahan saat mengambil data barang',
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Search barang function
		function searchBarang(query) {
			$.ajax({
				url: '{{ route('lorderlogistik.browse') }}',
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
							row += '<td>' + item.ket_kem + '</td>';
							row += '<td>' + item.ak00 + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectBarang(\'' +
								item.kd_brg + '\', \'' + item.na_brg + '\', \'' + item.ket_kem + '\', \'' +
								item.ak00 + '\', \'' + item.kdlaku + '\', \'' + item.sub + '\', \'' +
								item.kdbar + '\')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="5" class="text-center">No data found</td></tr>');
					}
				}
			});
		}

		// Select barang from browse modal
		function selectBarang(kd_brg, na_brg, ket_kem, stok, kdlaku, sub, kdbar) {
			if (kdlaku == '4') {
				Swal.fire({
					title: 'Warning!',
					text: 'Barang Kode 4 tidak bisa dipesan ke gudang!',
					icon: 'warning',
					confirmButtonText: 'OK'
				});
				return;
			}

			if (parseFloat(stok) <= 0) {
				Swal.fire({
					title: 'Warning!',
					text: 'Stok Gudang Masih Belum Tersedia!',
					icon: 'warning',
					confirmButtonText: 'OK'
				});
				return;
			}

			var row = $('.detail-row[data-index="' + currentRowIndex + '"]');
			row.find('[name="details[' + currentRowIndex + '][kd_brg]"]').val(kd_brg);
			row.find('[name="details[' + currentRowIndex + '][na_brg]"]').val(na_brg);
			row.find('[name="details[' + currentRowIndex + '][kemasan]"]').val(ket_kem);
			row.find('.stok').val(stok);
			row.find('[name="details[' + currentRowIndex + '][klaku]"]').val(kdlaku);
			row.find('[name="details[' + currentRowIndex + '][sub]"]').val(sub);
			row.find('[name="details[' + currentRowIndex + '][kdbar]"]').val(kdbar);

			$('#browseBarangModal').modal('hide');
			row.find('.qty').focus();
		}

		// Calculate total quantity
		function calculateTotal() {
			var total = 0;
			$('.qty').each(function() {
				var qty = parseFloat($(this).val()) || 0;
				total += qty;
			});
			$('#total_qty').val(total);
		}

		// Add new row
		function addRow() {
			var newIndex = rowCounter++;
			var newRow = '<tr class="detail-row" data-index="' + newIndex + '">';
			newRow += '<td>' + (newIndex + 1) + '</td>';
			newRow += '<td>';
			newRow += '<input type="text" class="form-control grid-input kd-brg" name="details[' + newIndex + '][kd_brg]" onchange="getBarangDetail(this, ' +
				newIndex + ')">';
			newRow += '<input type="hidden" name="details[' + newIndex + '][no_id]" value="0">';
			newRow += '</td>';
			newRow += '<td><input type="text" class="form-control grid-input na-brg" name="details[' + newIndex + '][na_brg]" readonly></td>';
			newRow += '<td><input type="number" class="form-control grid-input qty" name="details[' + newIndex +
				'][qty]" onchange="calculateTotal()" min="0" step="0.01" value="0"></td>';
			newRow += '<td><input type="text" class="form-control grid-input kemasan" name="details[' + newIndex + '][kemasan]" readonly></td>';
			newRow += '<td>';
			newRow += '<input type="text" class="form-control grid-input stok" readonly>';
			newRow += '<input type="hidden" name="details[' + newIndex + '][klaku]">';
			newRow += '<input type="hidden" name="details[' + newIndex + '][sub]">';
			newRow += '<input type="hidden" name="details[' + newIndex + '][kdbar]">';
			newRow += '</td>';
			newRow += '<td><button type="button" class="btn btn-danger btn-grid" onclick="deleteRow(this)">Del</button></td>';
			newRow += '</tr>';

			$('#detailBody').append(newRow);
		}

		// Delete row
		function deleteRow(button) {
			$(button).closest('tr').remove();
			calculateTotal();
		}

		// Enable edit mode
		function enableEdit() {
			$('input:not([readonly])').prop('readonly', false);
			$('#EDITX').hide();
			$('#SAVEX').show();
		}

		// Save function
		function simpan() {
			// Validation
			if ($('#no_bukti').val() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'No Bukti harus diisi.'
				});
				return;
			}

			if ($('#tgl').val() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Tanggal harus diisi.'
				});
				return;
			}

			if ($('#ket').val() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Keterangan harus diisi.'
				});
				return;
			}

			// Check if there's at least one detail with barang
			var hasDetail = false;
			$('.kd-brg').each(function() {
				if ($(this).val().trim() !== '') {
					hasDetail = true;
					return false;
				}
			});

			if (!hasDetail) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Minimal harus ada satu barang yang dipilih.'
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
				url: '{{ route('lorderlogistik.store') }}',
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
							window.location.href = '{{ route('lorderlogistik') }}';
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

		// Delete order function
		function hapusOrder() {
			var no_bukti = $('#no_bukti').val();

			Swal.fire({
				title: 'Are you sure?',
				text: 'Hapus Order Logistik ' + no_bukti + '?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, delete it!',
				cancelButtonText: 'Cancel'
			}).then((result) => {
				if (result.isConfirmed) {
					window.location.href = '{{ route('lorderlogistik.delete', '') }}/' + no_bukti;
				}
			});
		}

		// Close form function
		function closeForm() {
			Swal.fire({
				title: 'Are you sure?',
				text: 'Do you really want to close this form? Unsaved changes will be lost.',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Yes, close it',
				cancelButtonText: 'No, stay here'
			}).then((result) => {
				if (result.isConfirmed) {
					window.location.href = '{{ route('lorderlogistik') }}';
				}
			});
		}
	</script>
@endsection
