@extends('layouts.plain')

@section('content')

	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Transaksi Pembayaran Non Dagang - Create</h1>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				@if ($errors->any())
					<div class="alert alert-danger">
						<ul>
							@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
				@endif

				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<h3 class="card-title">Form Input Non Dagang</h3>
							</div>

							<form action="{{ route('tpnondagang.store') }}" method="POST" id="entryForm">
								@csrf
								<div class="card-body">

									<!-- Header Information -->
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="TGL">Tanggal *</label>
												<input type="date" class="form-control" name="TGL" id="TGL" value="{{ old('TGL', date('Y-m-d')) }}" required>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="JTEMPO">Jatuh Tempo *</label>
												<input type="date" class="form-control" name="JTEMPO" id="JTEMPO" value="{{ old('JTEMPO', date('Y-m-d')) }}" required>
											</div>
										</div>
									</div>

									<!-- Supplier Information -->
									<div class="row">
										<div class="col-md-3">
											<div class="form-group">
												<label for="KODES">Kode Supplier *</label>
												<input type="text" class="form-control" name="KODES" id="KODES" value="{{ old('KODES') }}" placeholder="Kode Supplier" required>
												<button type="button" class="btn btn-info btn-sm mt-1" onclick="browseSupplier()">
													<i class="fas fa-search"></i> Browse
												</button>
											</div>
										</div>
										<div class="col-md-9">
											<div class="form-group">
												<label for="NAMAS">Nama Supplier *</label>
												<input type="text" class="form-control" name="NAMAS" id="NAMAS" value="{{ old('NAMAS') }}" placeholder="Nama Supplier" required
													readonly>
											</div>
										</div>
									</div>

									<!-- Bank Information (Auto-filled from supplier) -->
									<div class="row">
										<div class="col-md-3">
											<div class="form-group">
												<label for="NO_REK">No. Rekening</label>
												<input type="text" class="form-control" name="NO_REK" id="NO_REK" value="{{ old('NO_REK') }}" readonly>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="AN_B">Atas Nama</label>
												<input type="text" class="form-control" name="AN_B" id="AN_B" value="{{ old('AN_B') }}" readonly>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="NAMA_B">Nama Bank</label>
												<input type="text" class="form-control" name="NAMA_B" id="NAMA_B" value="{{ old('NAMA_B') }}" readonly>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<label for="KOTA_B">Kota</label>
												<input type="text" class="form-control" name="KOTA_B" id="KOTA_B" value="{{ old('KOTA_B') }}" readonly>
											</div>
										</div>
									</div>

									<!-- Notes and GOL -->
									<div class="row">
										<div class="col-md-8">
											<div class="form-group">
												<label for="NOTES">Notes</label>
												<textarea class="form-control" name="NOTES" id="NOTES" rows="2" placeholder="Catatan">{{ old('NOTES') }}</textarea>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="GOL">Golongan</label>
												<input type="text" class="form-control" name="GOL" id="GOL" value="{{ old('GOL') }}" placeholder="Golongan">
											</div>
										</div>
									</div>

									<!-- Detail Items Section -->
									<hr>
									<div class="row">
										<div class="col-md-7">
											<!-- Detail Items Section (datatable) -->
											<h5>Detail Items</h5>
											<button type="button" class="btn btn-success btn-sm mb-2" onclick="addDetailRow()">
												<i class="fas fa-plus"></i> Add Row
											</button>
											<div class="table-responsive">
												<table class="table-bordered table-sm table" id="detailTable">
													<thead class="table-dark">
														<tr>
															<th width="5%">No</th>
															<th width="15%">Acno</th>
															<th width="15%">Tgl</th>
															<th width="15%">Agenda</th>
															<th width="35%">Uraian</th>
															<th width="15%">Total</th>
															<th width="10%">Action</th>
														</tr>
													</thead>
													<tbody id="detailBody">
														<!-- Dynamic rows will be added here -->
													</tbody>
												</table>
											</div>
										</div>
										<div class="col-md-5">
											<!-- Tax and Total Calculation -->
											<table class="table-bordered table">
												<tr>
													<td><strong>Total</strong></td>
													<td><input type="number" step="0.01" class="form-control" name="TOTAL" id="TOTAL" value="{{ old('TOTAL', 0) }}" readonly>
													</td>
												</tr>
												<tr>
													<td>PPH 21</td>
													<td><input type="number" step="0.01" class="form-control" name="PPH21" id="PPH21" value="{{ old('PPH21', 0) }}"
															onchange="calculateTotals()"></td>
												</tr>
												<tr>
													<td>PPH 23</td>
													<td><input type="number" step="0.01" class="form-control" name="PPH23" id="PPH23" value="{{ old('PPH23', 0) }}"
															onchange="calculateTotals()"></td>
												</tr>
												<tr>
													<td>PPH 4 (2)</td>
													<td><input type="number" step="0.01" class="form-control" name="PPH4_2" id="PPH4_2" value="{{ old('PPH4_2', 0) }}"
															onchange="calculateTotals()"></td>
												</tr>
												<tr>
													<td>PPN Masukan</td>
													<td><input type="number" step="0.01" class="form-control" name="PPN_MASUKAN" id="PPN_MASUKAN"
															value="{{ old('PPN_MASUKAN', 0) }}" onchange="calculateTotals()"></td>
												</tr>
												<tr>
													<td>Diskon</td>
													<td><input type="number" step="0.01" class="form-control" name="DISKON" id="DISKON" value="{{ old('DISKON', 0) }}"
															onchange="calculateTotals()"></td>
												</tr>
												<tr>
													<td>Lain</td>
													<td><input type="number" step="0.01" class="form-control" name="LAIN" id="LAIN" value="{{ old('LAIN', 0) }}"
															onchange="calculateTotals()"></td>
												</tr>
												<tr>
													<td>Bea Materai</td>
													<td><input type="number" step="0.01" class="form-control" name="MATERAI" id="MATERAI" value="{{ old('MATERAI', 0) }}"
															onchange="calculateTotals()"></td>
												</tr>
												<tr>
													<td><strong>NETT</strong></td>
													<td><input type="number" step="0.01" class="form-control" name="NETT" id="NETT" value="{{ old('NETT', 0) }}" readonly>
													</td>
												</tr>
											</table>
										</div>
									</div>

								</div>

								<div class="card-footer">
									<button type="submit" class="btn btn-success">
										<i class="fas fa-save"></i> Save
									</button>
									<a href="{{ route('tpnondagang') }}" class="btn btn-secondary">
										<i class="fas fa-arrow-left"></i> Back
									</a>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Browse Supplier Modal -->
	<div class="modal fade" id="supplierModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Browse Supplier</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
				</div>
				<div class="modal-body">
					<input type="text" class="form-control mb-2" id="supplierSearch" placeholder="Search supplier...">
					<div class="table-responsive">
						<table class="table-bordered table-sm table" id="supplierTable">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Nama</th>
									<th>No. Rek</th>
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

	<!-- Browse Account Modal -->
	<div class="modal fade" id="accountModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Browse Account</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
				</div>
				<div class="modal-body">
					<input type="text" class="form-control mb-2" id="accountSearch" placeholder="Search account...">
					<div class="table-responsive">
						<table class="table-bordered table-sm table" id="accountTable">
							<thead>
								<tr>
									<th>Akun</th>
									<th>Nama</th>
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

@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

	<script>
		$(document).ready(function() {
			let detailRowCount = 0;
			let currentAccountInput = null;

			// Add initial detail row
			addDetailRow();

			// Supplier code validation
			$('#KODES').on('blur', function() {
				const kodes = $(this).val().trim();
				if (kodes) {
					validateSupplier(kodes);
				}
			});

			function validateSupplier(kodes) {
				$.ajax({
					url: '{{ route('tpnondagang.get-supplier') }}',
					method: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						kodes: kodes
					},
					success: function(response) {
						$('#NAMAS').val(response.namas);
						$('#NO_REK').val(response.no_rek || '');
						$('#AN_B').val(response.an_b || '');
						$('#NAMA_B').val(response.nama_b || '');
						$('#KOTA_B').val(response.kota_b || '');
					},
					error: function(xhr) {
						if (xhr.status === 404) {
							alert('Supplier tidak ditemukan');
							$('#KODES').focus();
						}
					}
				});
			}

			// Browse supplier function
			window.browseSupplier = function() {
				$('#supplierModal').modal('show');
				loadSuppliers();
			}

			function loadSuppliers(search = '') {
				$.ajax({
					url: '{{ route('tpnondagang.browse_supplier') }}',
					method: 'GET',
					data: {
						search: search
					},
					success: function(response) {
						let tbody = $('#supplierTableBody');
						tbody.empty();

						response.forEach(supplier => {
							tbody.append(`
                        <tr>
                            <td>${supplier.kodes}</td>
                            <td>${supplier.namas}</td>
                            <td>${supplier.no_rek || ''}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary"
                                        onclick="selectSupplier('${supplier.kodes}', '${supplier.namas}', '${supplier.no_rek || ''}', '${supplier.an_b || ''}', '${supplier.nama_b || ''}', '${supplier.kota_b || ''}')">
                                    Select
                                </button>
                            </td>
                        </tr>
                    `);
						});
					}
				});
			}

			// Search suppliers
			$('#supplierSearch').on('keyup', function() {
				loadSuppliers($(this).val());
			});

			// Select supplier function
			window.selectSupplier = function(kodes, namas, no_rek, an_b, nama_b, kota_b) {
				$('#KODES').val(kodes);
				$('#NAMAS').val(namas);
				$('#NO_REK').val(no_rek);
				$('#AN_B').val(an_b);
				$('#NAMA_B').val(nama_b);
				$('#KOTA_B').val(kota_b);
				$('#supplierModal').modal('hide');
			}

			// Add detail row function
			window.addDetailRow = function() {
				detailRowCount++;
				const newRow = `
			<tr id="detail_${detailRowCount}">
				<td class="text-center">${detailRowCount}</td>
				<td>
					<input type="text" class="form-control form-control-sm" name="detail[${detailRowCount}][acno]"
						   id="acno_${detailRowCount}" placeholder="Acno" onblur="validateAccount(${detailRowCount})">
					<button type="button" class="btn btn-info btn-sm mt-1" onclick="browseAccount(${detailRowCount})">
						<i class="fas fa-search"></i>
					</button>
				</td>
				<td>
					<input type="date" class="form-control form-control-sm" name="detail[${detailRowCount}][tgl]"
						   id="tgl_${detailRowCount}" value="{{ date('Y-m-d') }}">
				</td>
				<td>
					<input type="text" class="form-control form-control-sm" name="detail[${detailRowCount}][agenda]"
						   id="agenda_${detailRowCount}" placeholder="Agenda">
				</td>
				<td>
					<input type="text" class="form-control form-control-sm" name="detail[${detailRowCount}][ket]"
						   id="ket_${detailRowCount}" placeholder="Uraian">
				</td>
				<td>
					<input type="number" step="0.01" class="form-control form-control-sm" name="detail[${detailRowCount}][total]"
						   id="total_${detailRowCount}" placeholder="0.00" onchange="calculateTotals()">
				</td>
				<td>
					<button type="button" class="btn btn-danger btn-sm" onclick="removeDetailRow(${detailRowCount})">
						<i class="fas fa-trash"></i>
					</button>
				</td>
			</tr>
		`;
				$('#detailBody').append(newRow);
			}

			// Remove detail row function
			window.removeDetailRow = function(rowId) {
				$(`#detail_${rowId}`).remove();
				calculateTotals();
				updateRowNumbers();
			}

			// Update row numbers after deletion
			function updateRowNumbers() {
				$('#detailBody tr').each(function(index) {
					$(this).find('td:first').text(index + 1);
				});
			}

			// Validate account function
			window.validateAccount = function(rowId) {
				const acno = $(`#acno_${rowId}`).val().trim();
				if (acno) {
					$.ajax({
						url: '{{ route('tpnondagang.validate-account') }}',
						method: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							acno: acno
						},
						success: function(response) {
							$(`#ket_${rowId}`).val(response.nama);
						},
						error: function(xhr) {
							if (xhr.status === 404) {
								alert('Akun tidak ditemukan');
								$(`#acno_${rowId}`).focus();
							}
						}
					});
				}
			}

			// Browse account function
			window.browseAccount = function(rowId) {
				currentAccountInput = rowId;
				$('#accountModal').modal('show');
				loadAccounts();
			}

			function loadAccounts(search = '') {
				$.ajax({
					url: '{{ route('tpnondagang.browse_account') }}',
					method: 'GET',
					data: {
						search: search
					},
					success: function(response) {
						let tbody = $('#accountTableBody');
						tbody.empty();

						response.forEach(account => {
							tbody.append(`
                        <tr>
                            <td>${account.acno}</td>
                            <td>${account.nama}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary"
                                        onclick="selectAccount('${account.acno}', '${account.nama}')">
                                    Select
                                </button>
                            </td>
                        </tr>
                    `);
						});
					}
				});
			}

			// Search accounts
			$('#accountSearch').on('keyup', function() {
				loadAccounts($(this).val());
			});

			// Select account function
			window.selectAccount = function(acno, nama) {
				if (currentAccountInput) {
					$(`#acno_${currentAccountInput}`).val(acno);
					$(`#ket_${currentAccountInput}`).val(nama);
					$('#accountModal').modal('hide');
				}
			}

			// Calculate totals function
			window.calculateTotals = function() {
				let totalDetail = 0;

				// Sum all detail totals
				$('#detailBody input[name$="[total]"]').each(function() {
					const value = parseFloat($(this).val()) || 0;
					totalDetail += value;
				});

				// Get tax percentages
				const pph1Rate = parseFloat($('#PPH1').val()) || 0;
				const pph2Rate = parseFloat($('#PPH2').val()) || 0;
				const pph3Rate = parseFloat($('#PPH3').val()) || 0;
				const ppnRate = parseFloat($('#PPN').val()) || 0;

				// Get other amounts
				const materai = parseFloat($('#MATERAI').val()) || 0;
				const lain = parseFloat($('#LAIN').val()) || 0;
				const diskon = parseFloat($('#DISKON').val()) || 0;

				// Calculate taxes
				const pph1 = totalDetail * (pph1Rate / 100);
				const pph2 = totalDetail * (pph2Rate / 100);
				const pph3 = totalDetail * (pph3Rate / 100);
				const ppn = totalDetail * (ppnRate / 100);

				// Calculate total and nett
				const total = totalDetail + ppn + materai + lain - diskon;
				const nett = total - pph1 - pph2 - pph3;

				// Update display
				$('#TOTAL').val(total.toFixed(2));
				$('#NETT').val(nett.toFixed(2));
			}

			// Form validation before submit
			$('#entryForm').on('submit', function(e) {
				const detailRows = $('#detailBody tr').length;
				if (detailRows === 0) {
					e.preventDefault();
					alert('Minimal harus ada 1 item detail');
					return false;
				}

				// Validate all required fields in detail
				let isValid = true;
				$('#detailBody input[name$="[acno]"]').each(function() {
					if (!$(this).val().trim()) {
						isValid = false;
						$(this).focus();
						alert('Akun tidak boleh kosong');
						return false;
					}
				});

				if (!isValid) {
					e.preventDefault();
					return false;
				}
			});
		});
	</script>
@endsection
