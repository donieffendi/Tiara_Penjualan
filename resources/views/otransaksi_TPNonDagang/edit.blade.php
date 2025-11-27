@extends('layouts.plain')

@section('content')

	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">
							Transaksi Pembayaran Non Dagang -
							@if ($tipx == 'edit')
								Edit {{ $header->NO_BUKTI ?? '' }}
								@if ($readonly_mode)
									<span class="badge badge-warning">POSTED - READ ONLY</span>
								@endif
							@else
								Create
							@endif
						</h1>
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
								<h3 class="card-title">
									@if ($tipx == 'edit')
										Edit Form Non Dagang
									@else
										Form Input Non Dagang
									@endif
								</h3>
							</div>

							<form action="{{ $tipx == 'edit' ? route('tpnondagang.update', $header->NO_ID) : route('tpnondagang.store') }}" method="POST" id="entryForm">
								@csrf
								@if ($tipx == 'edit')
									@method('POST')
								@endif

								<div class="card-body">

									<!-- Header Information -->
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label for="NO_BUKTI">No. Bukti</label>
												<input type="text" class="form-control" name="NO_BUKTI" id="NO_BUKTI" value="{{ $header->NO_BUKTI ?? '' }}" readonly>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="TGL">Tanggal *</label>
												<input type="date" class="form-control" name="TGL" id="TGL" value="{{ old('TGL', $header->TGL ?? date('Y-m-d')) }}"
													{{ $readonly_mode ? 'readonly' : '' }} required>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="JTEMPO">Jatuh Tempo *</label>
												<input type="date" class="form-control" name="JTEMPO" id="JTEMPO" value="{{ old('JTEMPO', $header->JTEMPO ?? date('Y-m-d')) }}"
													{{ $readonly_mode ? 'readonly' : '' }} required>
											</div>
										</div>
									</div>

									<!-- Supplier Information -->
									<div class="row">
										<div class="col-md-3">
											<div class="form-group">
												<label for="KODES">Kode Supplier *</label>
												<input type="text" class="form-control" name="KODES" id="KODES" value="{{ old('KODES', $header->KODES ?? '') }}"
													placeholder="Kode Supplier" {{ $readonly_mode ? 'readonly' : '' }} required>
												@if (!$readonly_mode)
													<button type="button" class="btn btn-info btn-sm mt-1" onclick="browseSupplier()">
														<i class="fas fa-search"></i> Browse
													</button>
												@endif
											</div>
										</div>
										<div class="col-md-9">
											<div class="form-group">
												<label for="NAMAS">Nama Supplier *</label>
												<input type="text" class="form-control" name="NAMAS" id="NAMAS" value="{{ old('NAMAS', $header->NAMAS ?? '') }}"
													placeholder="Nama Supplier" required readonly>
											</div>
										</div>
									</div>

									<!-- Bank Information (Auto-filled from supplier) -->
									<div class="row">
										<div class="col-md-3">
											<div class="form-group">
												<label for="NO_REK">No. Rekening</label>
												<input type="text" class="form-control" name="NO_REK" id="NO_REK" value="{{ old('NO_REK', $supplier_data->no_rek ?? '') }}"
													readonly>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="AN_B">Atas Nama</label>
												<input type="text" class="form-control" name="AN_B" id="AN_B" value="{{ old('AN_B', $supplier_data->an_b ?? '') }}" readonly>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="NAMA_B">Nama Bank</label>
												<input type="text" class="form-control" name="NAMA_B" id="NAMA_B" value="{{ old('NAMA_B', $supplier_data->nama_b ?? '') }}"
													readonly>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<label for="KOTA_B">Kota</label>
												<input type="text" class="form-control" name="KOTA_B" id="KOTA_B" value="{{ old('KOTA_B', $supplier_data->kota_b ?? '') }}"
													readonly>
											</div>
										</div>
									</div>

									<!-- Notes and GOL -->
									<div class="row">
										<div class="col-md-8">
											<div class="form-group">
												<label for="NOTES">Notes</label>
												<textarea class="form-control" name="NOTES" id="NOTES" rows="2" placeholder="Catatan" {{ $readonly_mode ? 'readonly' : '' }}>{{ old('NOTES', $header->NOTES ?? '') }}</textarea>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="GOL">Golongan</label>
												<input type="text" class="form-control" name="GOL" id="GOL" value="{{ old('GOL', $header->GOL ?? '') }}"
													placeholder="Golongan" {{ $readonly_mode ? 'readonly' : '' }}>
											</div>
										</div>
									</div>

									<!-- Detail Items Section -->
									<hr>
									<div class="row">
										<div class="col-12">
											<h5>Detail Items</h5>
											@if (!$readonly_mode)
												<button type="button" class="btn btn-success btn-sm mb-2" onclick="addDetailRow()">
													<i class="fas fa-plus"></i> Add Row
												</button>
											@endif

											<div class="table-responsive">
												<table class="table-bordered table-sm table" id="detailTable">
													<thead class="table-dark">
														<tr>
															<th width="5%">No</th>
															<th width="15%">Akun</th>
															<th width="40%">Keterangan</th>
															<th width="15%">Total</th>
															<th width="15%">Agenda</th>
															@if (!$readonly_mode)
																<th width="10%">Action</th>
															@endif
														</tr>
													</thead>
													<tbody id="detailBody">
														@if ($detail && $detail->count() > 0)
															@foreach ($detail as $index => $item)
																<tr id="detail_{{ $index + 1 }}">
																	<td class="text-center">{{ $index + 1 }}</td>
																	<td>
																		<input type="text" class="form-control form-control-sm" name="detail[{{ $index + 1 }}][acno]"
																			id="acno_{{ $index + 1 }}" value="{{ $item->acno }}" placeholder="Kode Akun" {{ $readonly_mode ? 'readonly' : '' }}
																			@if (!$readonly_mode) onblur="validateAccount({{ $index + 1 }})" @endif>
																		@if (!$readonly_mode)
																			<button type="button" class="btn btn-info btn-sm mt-1" onclick="browseAccount({{ $index + 1 }})">
																				<i class="fas fa-search"></i>
																			</button>
																		@endif
																	</td>
																	<td>
																		<input type="text" class="form-control form-control-sm" name="detail[{{ $index + 1 }}][ket]" id="ket_{{ $index + 1 }}"
																			value="{{ $item->ket }}" placeholder="Keterangan" {{ $readonly_mode ? 'readonly' : '' }}>
																	</td>
																	<td>
																		<input type="number" step="0.01" class="form-control form-control-sm" name="detail[{{ $index + 1 }}][total]"
																			id="total_{{ $index + 1 }}" value="{{ $item->total }}" placeholder="0.00" {{ $readonly_mode ? 'readonly' : '' }}
																			@if (!$readonly_mode) onchange="calculateTotals()" @endif>
																	</td>
																	<td>
																		<input type="date" class="form-control form-control-sm" name="detail[{{ $index + 1 }}][agenda]"
																			id="agenda_{{ $index + 1 }}" value="{{ $item->agenda ? date('Y-m-d', strtotime($item->agenda)) : date('Y-m-d') }}"
																			{{ $readonly_mode ? 'readonly' : '' }}>
																	</td>
																	@if (!$readonly_mode)
																		<td>
																			<button type="button" class="btn btn-danger btn-sm" onclick="removeDetailRow({{ $index + 1 }})">
																				<i class="fas fa-trash"></i>
																			</button>
																		</td>
																	@endif
																</tr>
															@endforeach
														@endif
													</tbody>
												</table>
											</div>
										</div>
									</div>

									<!-- Tax and Total Calculation -->
									<hr>
									<div class="row">
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="PPH1">PPH 1 (Rp)</label>
														<input type="number" step="0.01" class="form-control" name="PPH1" id="PPH1"
															value="{{ old('PPH1', $header->PPH1 ?? 0) }}" {{ $readonly_mode ? 'readonly' : '' }}
															@if (!$readonly_mode) onchange="calculateTotals()" @endif>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="PPH2">PPH 2 (Rp)</label>
														<input type="number" step="0.01" class="form-control" name="PPH2" id="PPH2"
															value="{{ old('PPH2', $header->PPH2 ?? 0) }}" {{ $readonly_mode ? 'readonly' : '' }}
															@if (!$readonly_mode) onchange="calculateTotals()" @endif>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="PPH3">PPH 3 (Rp)</label>
														<input type="number" step="0.01" class="form-control" name="PPH3" id="PPH3"
															value="{{ old('PPH3', $header->PPH3 ?? 0) }}" {{ $readonly_mode ? 'readonly' : '' }}
															@if (!$readonly_mode) onchange="calculateTotals()" @endif>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="PPN">PPN (Rp)</label>
														<input type="number" step="0.01" class="form-control" name="PPN" id="PPN"
															value="{{ old('PPN', $header->PPN ?? 0) }}" {{ $readonly_mode ? 'readonly' : '' }}
															@if (!$readonly_mode) onchange="calculateTotals()" @endif>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="MATERAI">Materai</label>
														<input type="number" step="0.01" class="form-control" name="MATERAI" id="MATERAI"
															value="{{ old('MATERAI', $header->MATERAI ?? 0) }}" {{ $readonly_mode ? 'readonly' : '' }}
															@if (!$readonly_mode) onchange="calculateTotals()" @endif>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="LAIN">Lain-lain</label>
														<input type="number" step="0.01" class="form-control" name="LAIN" id="LAIN"
															value="{{ old('LAIN', $header->LAIN ?? 0) }}" {{ $readonly_mode ? 'readonly' : '' }}
															@if (!$readonly_mode) onchange="calculateTotals()" @endif>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="DISKON">Diskon</label>
														<input type="number" step="0.01" class="form-control" name="DISKON" id="DISKON"
															value="{{ old('DISKON', $header->DISKON ?? 0) }}" {{ $readonly_mode ? 'readonly' : '' }}
															@if (!$readonly_mode) onchange="calculateTotals()" @endif>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Total Display -->
									<div class="row">
										<div class="col-md-8"></div>
										<div class="col-md-4">
											<div class="table-responsive">
												<table class="table-bordered table-sm table">
													<tr>
														<td><strong>Total</strong></td>
														<td class="text-right">
															<input type="number" step="0.01" class="form-control" name="TOTAL" id="TOTAL"
																value="{{ old('TOTAL', $header->TOTAL ?? 0) }}" readonly>
														</td>
													</tr>
													<tr>
														<td><strong>Nett</strong></td>
														<td class="text-right">
															<input type="number" step="0.01" class="form-control" name="NETT" id="NETT"
																value="{{ old('NETT', $header->NETT ?? 0) }}" readonly>
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>

								</div>

								<div class="card-footer">
									@if (!$readonly_mode)
										<button type="submit" class="btn btn-success">
											<i class="fas fa-save"></i>
											@if ($tipx == 'edit')
												Update
											@else
												Save
											@endif
										</button>
									@endif

									@if ($tipx == 'edit' && isset($header->NO_BUKTI))
										<button type="button" class="btn btn-info" onclick="printSingle('{{ $header->NO_BUKTI }}')">
											<i class="fas fa-print"></i> Print
										</button>
									@endif

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
	@if (!$readonly_mode)
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
	@endif

@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

	<script>
		$(document).ready(function() {
			let detailRowCount = {{ $detail ? $detail->count() : 0 }};
			let currentAccountInput = null;
			const readonlyMode = {{ $readonly_mode ? 'true' : 'false' }};

			@if ($tipx == 'new' || (!$readonly_mode && $detail && $detail->count() == 0))
				// Add initial detail row for new entries
				addDetailRow();
			@endif

			// Calculate initial totals
			if (!readonlyMode) {
				calculateTotals();
			}

			// Supplier code validation
			@if (!$readonly_mode)
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
			@endif

			// Add detail row function
			window.addDetailRow = function() {
				if (readonlyMode) return;

				detailRowCount++;
				const newRow = `
            <tr id="detail_${detailRowCount}">
                <td class="text-center">${detailRowCount}</td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="detail[${detailRowCount}][acno]"
                           id="acno_${detailRowCount}" placeholder="Kode Akun" onblur="validateAccount(${detailRowCount})">
                    <button type="button" class="btn btn-info btn-sm mt-1" onclick="browseAccount(${detailRowCount})">
                        <i class="fas fa-search"></i>
                    </button>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="detail[${detailRowCount}][ket]"
                           id="ket_${detailRowCount}" placeholder="Keterangan">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="detail[${detailRowCount}][total]"
                           id="total_${detailRowCount}" placeholder="0.00" onchange="calculateTotals()">
                </td>
                <td>
                    <input type="date" class="form-control form-control-sm" name="detail[${detailRowCount}][agenda]"
                           id="agenda_${detailRowCount}" value="{{ date('Y-m-d') }}">
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
				if (readonlyMode) return;

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

			@if (!$readonly_mode)
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
			@endif

			// Calculate totals function
			window.calculateTotals = function() {
				if (readonlyMode) return;

				let totalDetail = 0;

				// Sum all detail totals
				$('#detailBody input[name$="[total]"]').each(function() {
					const value = parseFloat($(this).val()) || 0;
					totalDetail += value;
				});

				// Get tax and other amounts (as absolute values, not percentages)
				const pph1 = parseFloat($('#PPH1').val()) || 0;
				const pph2 = parseFloat($('#PPH2').val()) || 0;
				const pph3 = parseFloat($('#PPH3').val()) || 0;
				const ppn = parseFloat($('#PPN').val()) || 0;
				const materai = parseFloat($('#MATERAI').val()) || 0;
				const lain = parseFloat($('#LAIN').val()) || 0;
				const diskon = parseFloat($('#DISKON').val()) || 0;

				// Calculate total and nett
				const total = totalDetail + ppn + materai + lain - diskon;
				const nett = total - pph1 - pph2 - pph3;

				// Update display
				$('#TOTAL').val(total.toFixed(2));
				$('#NETT').val(nett.toFixed(2));
			}

			// Print single function
			window.printSingle = function(no_bukti) {
				if (!no_bukti) return;

				Swal.fire({
					title: 'Memproses...',
					text: 'Sedang memproses print ' + no_bukti,
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				$.ajax({
					url: '{{ route('tpnondagang.print_single') }}',
					method: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						no_bukti: no_bukti
					},
					xhrFields: {
						responseType: 'blob'
					},
					success: function(response) {
						Swal.close();

						// Create blob URL and open in new window
						const blob = new Blob([response], {
							type: 'application/pdf'
						});
						const url = window.URL.createObjectURL(blob);
						window.open(url, '_blank');

						// Clean up
						setTimeout(() => {
							window.URL.revokeObjectURL(url);
						}, 100);
					},
					error: function(xhr) {
						Swal.close();
						let errorMessage = 'Terjadi kesalahan saat print';

						if (xhr.responseJSON && xhr.responseJSON.error) {
							errorMessage = xhr.responseJSON.error;
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

			@if (!$readonly_mode)
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

					// Show loading while submitting
					Swal.fire({
						title: 'Menyimpan...',
						text: 'Sedang menyimpan data',
						allowOutsideClick: false,
						didOpen: () => {
							Swal.showLoading();
						}
					});
				});
			@endif
		});
	</script>
@endsection
