@extends('layouts.plain')

@section('content')
	<style>
		.card {}

		.form-control:focus {
			background-color: #E0FFFF !important;
		}

		/* perubahan tab warna di form edit  */
		.nav-item .nav-link.active {
			background-color: red !important;
			/* Use !important to ensure it overrides */
			color: white !important;
			/* border-radius: 10; */
		}

		/* menghilangkan padding */
		.content-header {
			padding: 0 !important;
		}

		/* Vertical alignment for form elements */
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
			/* Match input height */
		}

		.form-group.row .form-control {
			line-height: 1.5;
			height: 38px;
		}

		.form-group.row .form-check-input {
			margin-top: 0;
		}

		/* Ensure consistent spacing */
		.col-md-2,
		.col-md-4,
		.col-md-6,
		.col-md-8,
		.col-md-10 {
			padding-top: 0;
			padding-bottom: 0;
		}

		/* Center checkbox alignment */
		.d-flex.align-items-center {
			height: 38px;
		}

		/* Column spacing */
		.col-md-6.pr-4 {
			padding-right: 2rem !important;
		}

		.col-md-6.pl-4 {
			padding-left: 2rem !important;
		}

		/* Detail table styling */
		.detail-table {
			width: 100%;
			margin-top: 15px;
		}

		.detail-table th,
		.detail-table td {
			font-size: 12px;
			padding: 5px;
			vertical-align: middle;
		}

		.detail-table input {
			height: 30px;
			font-size: 12px;
		}

		.btn-add-row {
			margin-top: 10px;
		}

		.summary-panel {
			background-color: #f8f9fa;
			padding: 15px;
			border-radius: 5px;
			margin-top: 15px;
		}

		.summary-item {
			margin-bottom: 8px;
		}

		.summary-item label {
			font-weight: bold;
			min-width: 100px;
			display: inline-block;
		}

		.summary-item input {
			width: 150px;
			font-weight: bold;
		}
	</style>

	<div class="content-wrapper">
		<!-- Content Header (Page header) -->
		<!-- /.content-header -->

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">

								<form action="{{ $tipx == 'new' ? url('/TPembayaranPiutang/store/') : url('/TPembayaranPiutang/update/' . $header->NO_ID) }}" method="POST"
									name="entri" id="entri">
									@csrf
									@if ($tipx != 'new')
										@method('POST')
									@endif

									<input type="hidden" name="tipx" id="tipx" value="{{ $tipx }}">
									<input type="hidden" name="idx" id="idx" value="{{ $idx }}">
									@if ($tipx != 'new')
										<input type="hidden" name="edit_id" value="{{ $header->NO_ID }}">
									@endif

									<div class="tab-content mt-3">
										<!-- Main Form Content - Two Column Layout -->
										<div class="row">
											<!-- Left Column -->
											<div class="col-md-6 pr-4">
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="NO_BUKTI" class="form-label">NO BUKTI</label>
													</div>
													<div class="col-md-8">
														<input type="text" class="form-control" id="NO_BUKTI" name="NO_BUKTI" value="{{ $header->NO_BUKTI ?? '+' }}"
															placeholder="Auto Generate">
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="TGL" class="form-label">TANGGAL</label>
													</div>
													<div class="col-md-8">
														<input type="date" class="form-control" id="TGL" name="TGL" value="{{ $header->TGL ?? date('Y-m-d') }}">
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="JTEMPO" class="form-label">JATUH TEMPO</label>
													</div>
													<div class="col-md-8">
														<input type="date" class="form-control" id="JTEMPO" name="JTEMPO" value="{{ $header->JTEMPO ?? date('Y-m-d') }}">
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="KODEC" class="form-label">KODE CUSTOMER</label>
													</div>
													<div class="col-md-6">
														<input type="text" class="form-control" id="KODEC" name="KODEC" value="{{ $header->KODEC ?? '' }}" placeholder="Kode Customer">
													</div>
													<div class="col-md-2">
														<button type="button" class="btn btn-info btn-sm" onclick="browseCust()">Browse</button>
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="NAMAC" class="form-label">NAMA CUSTOMER</label>
													</div>
													<div class="col-md-8">
														<input type="text" class="form-control" id="NAMAC" name="NAMAC" value="{{ $header->NAMAC ?? '' }}" readonly>
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="ALAMAT" class="form-label">ALAMAT</label>
													</div>
													<div class="col-md-8">
														<input type="text" class="form-control" id="ALAMAT" name="ALAMAT" value="{{ $header->ALAMAT ?? '' }}" readonly>
													</div>
												</div>
											</div>

											<!-- Right Column -->
											<div class="col-md-6 pl-4">
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="KOTA" class="form-label">KOTA</label>
													</div>
													<div class="col-md-8">
														<input type="text" class="form-control" id="KOTA" name="KOTA" value="{{ $header->KOTA ?? '' }}" readonly>
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="ACNO" class="form-label">NO. ACCOUNT</label>
													</div>
													<div class="col-md-6">
														<input type="text" class="form-control" id="ACNO" name="ACNO" value="{{ $header->ACNO ?? '' }}"
															placeholder="No Account">
													</div>
													<div class="col-md-2">
														<button type="button" class="btn btn-info btn-sm" onclick="browseAccount()">Browse</button>
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="TBAYAR" class="form-label">CARA BAYAR</label>
													</div>
													<div class="col-md-8">
														<select class="form-control" id="TBAYAR" name="TBAYAR">
															<option value="">Pilih Cara Bayar</option>
															<option value="CASH" {{ ($header->TBAYAR ?? '') == 'CASH' ? 'selected' : '' }}>CASH</option>
															<option value="TRANSFER" {{ ($header->TBAYAR ?? '') == 'TRANSFER' ? 'selected' : '' }}>TRANSFER</option>
															<option value="GIRO" {{ ($header->TBAYAR ?? '') == 'GIRO' ? 'selected' : '' }}>GIRO</option>
														</select>
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="NOTES" class="form-label">NOTES</label>
													</div>
													<div class="col-md-8">
														<textarea class="form-control" id="NOTES" name="NOTES" rows="3" placeholder="Catatan">{{ $header->NOTES ?? '' }}</textarea>
													</div>
												</div>
											</div>
										</div>

										<!-- Detail Section -->
										<!-- Detail & Summary Section Side by Side -->
										<div class="row mt-4">
											<div class="col-md-8">
												<h5>Detail Pembayaran Piutang</h5>
												<table class="table-striped detail-table table" id="detail-table">
													<thead class="table-dark">
														<tr>
															<th width="50px">No</th>
															<th width="120px">No. Faktur</th>
															<th width="100px">Tgl Faktur</th>
															<th width="120px">Total</th>
															<th width="120px">Bayar</th>
															<th width="120px">Lain</th>
															<th width="120px">Sisa</th>
															<th width="150px">Uraian</th>
															<th width="60px">Action</th>
														</tr>
													</thead>
													<tbody id="detail-body">
														@if (!empty($detail))
															@foreach ($detail as $index => $item)
																<tr data-index="{{ $index }}">
																	<td>{{ $index + 1 }}</td>
																	<td>
																		<input type="text" class="form-control" name="detail[{{ $index }}][NO_FAKTUR]" value="{{ $item->NO_FAKTUR }}"
																			placeholder="No Faktur" onblur="validateInvoice(this, {{ $index }})">
																	</td>
																	<td>
																		<input type="date" class="form-control" name="detail[{{ $index }}][TGL_FAKTUR]" value="{{ $item->TGL_FAKTUR }}">
																	</td>
																	<td>
																		<input type="number" class="form-control text-right" name="detail[{{ $index }}][TOTAL]" value="{{ $item->TOTAL }}"
																			step="0.01" onchange="calculateRowSisa({{ $index }})">
																	</td>
																	<td>
																		<input type="number" class="form-control text-right" name="detail[{{ $index }}][BAYAR]" value="{{ $item->BAYAR }}"
																			step="0.01" onchange="calculateRowSisa({{ $index }})">
																	</td>
																	<td>
																		<input type="number" class="form-control text-right" name="detail[{{ $index }}][LAIN]" value="{{ $item->LAIN }}"
																			step="0.01" onchange="calculateRowSisa({{ $index }})">
																	</td>
																	<td>
																		<input type="number" class="form-control text-right" name="detail[{{ $index }}][SISA]" value="{{ $item->SISA }}"
																			step="0.01" readonly>
																	</td>
																	<td>
																		<input type="text" class="form-control" name="detail[{{ $index }}][URAIAN]" value="{{ $item->URAIAN }}"
																			placeholder="Uraian">
																	</td>
																	<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Del</button></td>
																</tr>
															@endforeach
														@else
															<tr data-index="0">
																<td>1</td>
																<td>
																	<input type="text" class="form-control" name="detail[0][NO_FAKTUR]" placeholder="No Faktur" onblur="validateInvoice(this, 0)">
																</td>
																<td>
																	<input type="date" class="form-control" name="detail[0][TGL_FAKTUR]">
																</td>
																<td>
																	<input type="number" class="form-control text-right" name="detail[0][TOTAL]" step="0.01" onchange="calculateRowSisa(0)">
																</td>
																<td>
																	<input type="number" class="form-control text-right" name="detail[0][BAYAR]" step="0.01" onchange="calculateRowSisa(0)">
																</td>
																<td>
																	<input type="number" class="form-control text-right" name="detail[0][LAIN]" step="0.01" onchange="calculateRowSisa(0)">
																</td>
																<td>
																	<input type="number" class="form-control text-right" name="detail[0][SISA]" step="0.01" readonly>
																</td>
																<td>
																	<input type="text" class="form-control" name="detail[0][URAIAN]" placeholder="Uraian">
																</td>
																<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Del</button></td>
															</tr>
														@endif
													</tbody>
												</table>
												<button type="button" class="btn btn-primary btn-sm btn-add-row" onclick="addRow()">
													<i class="fa fa-plus"></i> Tambah Baris
												</button>
											</div>
											<div class="col-md-4">
												<div class="summary-panel">
													<div class="summary-item">
														<label>TOTAL:</label>
														<input type="number" class="form-control d-inline-block" id="TOTAL" name="TOTAL" value="{{ $header->TOTAL ?? 0 }}"
															step="0.01" readonly>
													</div>
													<div class="summary-item">
														<label style="color: green;">BAYAR:</label>
														<input type="number" class="form-control d-inline-block" id="BAYAR" name="BAYAR" value="{{ $header->BAYAR ?? 0 }}"
															step="0.01" readonly style="font-weight: bold; color: green;">
													</div>
													<div class="summary-item">
														<label>LAIN:</label>
														<input type="number" class="form-control d-inline-block" id="LAIN" name="LAIN" value="{{ $header->LAIN ?? 0 }}"
															step="0.01" readonly>
													</div>
													<div class="summary-item">
														<label style="color: red;">SISA:</label>
														<input type="number" class="form-control d-inline-block" id="SISA" name="SISA" value="{{ $header->SISA ?? 0 }}"
															step="0.01" readonly style="font-weight: bold; color: red;">
													</div>
												</div>
											</div>
										</div>

										<!-- Buttons -->
										<div class="col-md-12 form-group row mt-3">
											<div class="col-md-4">
												<button type="button" id='TOPX' onclick="location.href='{{ url('/TPembayaranPiutang/edit/?idx=' . $idx . '&tipx=top') }}'"
													class="btn btn-outline-primary">Top</button>
												<button type="button" id='PREVX'
													onclick="location.href='{{ url('/TPembayaranPiutang/edit/?idx=' . ($header->NO_ID ?? 0) . '&tipx=prev&kodex=' . ($header->NO_BUKTI ?? '')) }}'"
													class="btn btn-outline-primary">Prev</button>
												<button type="button" id='NEXTX'
													onclick="location.href='{{ url('/TPembayaranPiutang/edit/?idx=' . ($header->NO_ID ?? 0) . '&tipx=next&kodex=' . ($header->NO_BUKTI ?? '')) }}'"
													class="btn btn-outline-primary">Next</button>
												<button type="button" id='BOTTOMX' onclick="location.href='{{ url('/TPembayaranPiutang/edit/?idx=' . $idx . '&tipx=bottom') }}'"
													class="btn btn-outline-primary">Bottom</button>
											</div>
											<div class="col-md-5">
												<button type="button" id='NEWX' onclick="location.href='{{ url('/TPembayaranPiutang/edit/?idx=0&tipx=new') }}'"
													class="btn btn-warning">New</button>
												<button type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>
												<button type="button" id='UNDOX' onclick="location.href='{{ url('/TPembayaranPiutang/edit/?idx=' . $idx . '&tipx=undo') }}'"
													class="btn btn-info">Undo</button>
												<button type="button" id='SAVEX' onclick='simpan()' class="btn btn-success">Save</button>
											</div>
											<div class="col-md-3">
												<button type="button" id='HAPUSX' onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
												<button type="button" id='CLOSEX' onclick="closeTrans()" class="btn btn-outline-secondary">Close</button>
											</div>
										</div>
									</div>

								</form>
							</div>
						</div>
						<!-- /.card -->
					</div>
				</div>
				<!-- /.row -->
			</div>
			<!-- /.container-fluid -->
		</div>
		<!-- /.content -->
	</div>

	<!-- Customer Browse Modal -->
	<div class="modal fade" id="browseCustomerModal" tabindex="-1" role="dialog" aria-labelledby="browseCustomerModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseCustomerModalLabel">Pilih Customer</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<input type="text" id="searchCustomer" class="form-control mb-3" placeholder="Cari customer...">
					<table class="table-striped table" id="customerTable">
						<thead>
							<tr>
								<th>Kode</th>
								<th>Nama</th>
								<th>Alamat</th>
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

	<!-- Account Browse Modal -->
	<div class="modal fade" id="browseAccountModal" tabindex="-1" role="dialog" aria-labelledby="browseAccountModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseAccountModalLabel">Pilih Account</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<input type="text" id="searchAccount" class="form-control mb-3" placeholder="Cari account...">
					<table class="table-striped table" id="accountTable">
						<thead>
							<tr>
								<th>No. Account</th>
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
@endsection

@section('footer-scripts')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
	<!-- tambahan untuk sweetalert -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<!-- tutupannya -->
	<script>
		var target;
		var idrow = 1;
		var detailRowIndex = {{ count($detail ?? []) }};

		$(document).ready(function() {

			$('body').on('keydown', 'input, select', function(e) {
				if (e.key === "Enter") {
					var self = $(this),
						form = self.parents('form:eq(0)'),
						focusable, next;
					focusable = form.find('input,select,textarea').filter(':visible');
					next = focusable.eq(focusable.index(this) + 1);
					console.log(next);
					if (next.length) {
						next.focus().select();
					} else {
						// Do nothing or handle as needed
					}
					return false;
				}
			});

			$tipx = $('#tipx').val();

			if ($tipx == 'new') {
				baru();
			}

			if ($tipx != 'new') {
				ganti();
			}

			$('.date').datepicker({
				dateFormat: 'dd-mm-yy'
			});

			// Initialize calculations
			calculateTotal();

			// Customer code lookup
			$('#KODEC').on('blur', function() {
				var kodec = $(this).val();
				if (kodec) {
					getCustomerData(kodec);
				}
			});

			// Customer search functionality
			$('#searchCustomer').on('keyup', function() {
				var query = $(this).val();
				if (query.length > 2) {
					searchCustomers(query);
				}
			});

			// Account search functionality
			$('#searchAccount').on('keyup', function() {
				var query = $(this).val();
				if (query.length > 2) {
					searchAccounts(query);
				}
			});

		});

		function addRow() {
			detailRowIndex++;
			var newRow = `
				<tr data-index="${detailRowIndex}">
					<td>${detailRowIndex + 1}</td>
					<td>
						<input type="text" class="form-control" name="detail[${detailRowIndex}][NO_FAKTUR]" placeholder="No Faktur" onblur="validateInvoice(this, ${detailRowIndex})">
					</td>
					<td>
						<input type="date" class="form-control" name="detail[${detailRowIndex}][TGL_FAKTUR]">
					</td>
					<td>
						<input type="number" class="form-control text-right" name="detail[${detailRowIndex}][TOTAL]" step="0.01" onchange="calculateRowSisa(${detailRowIndex})">
					</td>
					<td>
						<input type="number" class="form-control text-right" name="detail[${detailRowIndex}][BAYAR]" step="0.01" onchange="calculateRowSisa(${detailRowIndex})">
					</td>
					<td>
						<input type="number" class="form-control text-right" name="detail[${detailRowIndex}][LAIN]" step="0.01" onchange="calculateRowSisa(${detailRowIndex})">
					</td>
					<td>
						<input type="number" class="form-control text-right" name="detail[${detailRowIndex}][SISA]" step="0.01" readonly>
					</td>
					<td>
						<input type="text" class="form-control" name="detail[${detailRowIndex}][URAIAN]" placeholder="Uraian">
					</td>
					<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Del</button></td>
				</tr>
			`;
			$('#detail-body').append(newRow);
			updateRowNumbers();
		}

		function removeRow(button) {
			$(button).closest('tr').remove();
			updateRowNumbers();
			calculateTotal();
		}

		function updateRowNumbers() {
			$('#detail-body tr').each(function(index) {
				$(this).find('td:first').text(index + 1);
				$(this).attr('data-index', index);

				// Update input names
				$(this).find('input[name*="[NO_FAKTUR]"]').attr('name', `detail[${index}][NO_FAKTUR]`);
				$(this).find('input[name*="[TGL_FAKTUR]"]').attr('name', `detail[${index}][TGL_FAKTUR]`);
				$(this).find('input[name*="[TOTAL]"]').attr('name', `detail[${index}][TOTAL]`);
				$(this).find('input[name*="[BAYAR]"]').attr('name', `detail[${index}][BAYAR]`);
				$(this).find('input[name*="[LAIN]"]').attr('name', `detail[${index}][LAIN]`);
				$(this).find('input[name*="[SISA]"]').attr('name', `detail[${index}][SISA]`);
				$(this).find('input[name*="[URAIAN]"]').attr('name', `detail[${index}][URAIAN]`);

				// Update onchange events
				$(this).find('input[name*="[TOTAL]"]').attr('onchange', `calculateRowSisa(${index})`);
				$(this).find('input[name*="[BAYAR]"]').attr('onchange', `calculateRowSisa(${index})`);
				$(this).find('input[name*="[LAIN]"]').attr('onchange', `calculateRowSisa(${index})`);
				$(this).find('input[name*="[NO_FAKTUR]"]').attr('onblur', `validateInvoice(this, ${index})`);
			});
			detailRowIndex = $('#detail-body tr').length - 1;
		}

		function calculateRowSisa(rowIndex) {
			var total = parseFloat($(`input[name="detail[${rowIndex}][TOTAL]"]`).val()) || 0;
			var bayar = parseFloat($(`input[name="detail[${rowIndex}][BAYAR]"]`).val()) || 0;
			var lain = parseFloat($(`input[name="detail[${rowIndex}][LAIN]"]`).val()) || 0;

			// SISA = TOTAL - BAYAR + LAIN (berdasarkan logika controller)
			var sisa = total - bayar + lain;
			$(`input[name="detail[${rowIndex}][SISA]"]`).val(sisa.toFixed(2));

			calculateTotal();
		}

		function calculateTotal() {
			var totalSum = 0;
			var bayarSum = 0;
			var lainSum = 0;
			var sisaSum = 0;

			// Calculate totals from detail rows
			$('#detail-body input[name*="[TOTAL]"]').each(function() {
				totalSum += parseFloat($(this).val()) || 0;
			});

			$('#detail-body input[name*="[BAYAR]"]').each(function() {
				bayarSum += parseFloat($(this).val()) || 0;
			});

			$('#detail-body input[name*="[LAIN]"]').each(function() {
				lainSum += parseFloat($(this).val()) || 0;
			});

			$('#detail-body input[name*="[SISA]"]').each(function() {
				sisaSum += parseFloat($(this).val()) || 0;
			});

			// Update summary panel
			$('#TOTAL').val(totalSum.toFixed(2));
			$('#BAYAR').val(bayarSum.toFixed(2));
			$('#LAIN').val(lainSum.toFixed(2));
			$('#SISA').val(sisaSum.toFixed(2));
		}

		function validateInvoice(input, rowIndex) {
			var noFaktur = $(input).val();
			if (!noFaktur) return;

			$.ajax({
				type: "POST",
				url: "{{ url('TPembayaranPiutang/validate-invoice') }}",
				data: {
					_token: '{{ csrf_token() }}',
					no_faktur: noFaktur
				},
				success: function(response) {
					if (response.success) {
						// Fill in invoice data
						$(`input[name="detail[${rowIndex}][TGL_FAKTUR]"]`).val(response.data.tgl_faktur);
						$(`input[name="detail[${rowIndex}][TOTAL]"]`).val(response.data.total);
						$(`input[name="detail[${rowIndex}][SISA]"]`).val(response.data.sisa);
						calculateRowSisa(rowIndex);
					}
				},
				error: function(xhr) {
					var response = xhr.responseJSON;
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: response.error || 'Error validating invoice'
					});
					$(input).focus();
				}
			});
		}

		function getCustomerData(kodec) {
			$.ajax({
				type: "GET",
				url: "{{ url('TPembayaranPiutang/get-select-kodes') }}",
				data: {
					kodec: kodec
				},
				success: function(data) {
					if (data.length > 0) {
						var customer = data[0];
						$('#NAMAC').val(customer.namac);
						$('#ALAMAT').val(customer.alamat || '');
						$('#KOTA').val(customer.kota || '');
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Customer Not Found',
							text: 'Customer code not found. Please check or browse for valid customer.'
						});
						$('#KODEC').focus();
					}
				},
				error: function() {
					alert('Error occurred while fetching customer data');
				}
			});
		}

		function browseCust() {
			$('#browseCustomerModal').modal('show');
			searchCustomers('');
		}

		function searchCustomers(query) {
			$.ajax({
				type: "GET",
				url: "{{ url('TPembayaranPiutang/browsesupz') }}",
				data: {
					q: query
				},
				success: function(data) {
					var tbody = $('#customerTableBody');
					tbody.empty();

					if (data.length > 0) {
						$.each(data, function(index, customer) {
							var row = `
								<tr>
									<td>${customer.kodes}</td>
									<td>${customer.namas}</td>
									<td>-</td>
									<td><button type="button" class="btn btn-primary btn-sm" onclick="selectCustomer('${customer.kodes}')">Select</button></td>
								</tr>
							`;
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="4" class="text-center">No customers found</td></tr>');
					}
				},
				error: function() {
					alert('Error occurred while searching customers');
				}
			});
		}

		function selectCustomer(kodec) {
			$('#KODEC').val(kodec);
			$('#browseCustomerModal').modal('hide');
			getCustomerData(kodec);
		}

		function browseAccount() {
			$('#browseAccountModal').modal('show');
			searchAccounts('');
		}

		function searchAccounts(query) {
			$.ajax({
				type: "GET",
				url: "{{ url('TPembayaranPiutang/browseAccount') }}",
				data: {
					search: query
				},
				success: function(data) {
					var tbody = $('#accountTableBody');
					tbody.empty();

					if (data.length > 0) {
						$.each(data, function(index, account) {
							var row = `
								<tr>
									<td>${account.acno}</td>
									<td>${account.nama}</td>
									<td><button type="button" class="btn btn-primary btn-sm" onclick="selectAccount('${account.acno}')">Select</button></td>
								</tr>
							`;
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="3" class="text-center">No accounts found</td></tr>');
					}
				},
				error: function() {
					alert('Error occurred while searching accounts');
				}
			});
		}

		function selectAccount(acno) {
			$('#ACNO').val(acno);
			$('#browseAccountModal').modal('hide');
		}

		function baru() {
			kosong();
			hidup();
		}

		function ganti() {
			hidup();
		}

		function batal() {
			mati();
		}

		function hidup() {
			$("#TOPX").attr("disabled", true);
			$("#PREVX").attr("disabled", true);
			$("#NEXTX").attr("disabled", true);
			$("#BOTTOMX").attr("disabled", true);

			$("#NEWX").attr("disabled", true);
			$("#EDITX").attr("disabled", true);
			$("#UNDOX").attr("disabled", false);
			$("#SAVEX").attr("disabled", false);

			$("#HAPUSX").attr("disabled", true);
			$("#CLOSEX").attr("disabled", false);

			// Enable form fields
			$("#NO_BUKTI").attr("readonly", $('#tipx').val() !== 'new');
			$("#TGL").attr("readonly", false);
			$("#JTEMPO").attr("readonly", false);
			$("#KODEC").attr("readonly", false);
			$("#ACNO").attr("readonly", false);
			$("#TBAYAR").attr("disabled", false);
			$("#NOTES").attr("readonly", false);

			// Enable detail table
			$("#detail-table input").attr("readonly", false);
			$(".btn-add-row").attr("disabled", false);
			$("#detail-table .btn-danger").attr("disabled", false);
		}

		function mati() {
			$("#TOPX").attr("disabled", false);
			$("#PREVX").attr("disabled", false);
			$("#NEXTX").attr("disabled", false);
			$("#BOTTOMX").attr("disabled", false);

			$("#NEWX").attr("disabled", false);
			$("#EDITX").attr("disabled", false);
			$("#UNDOX").attr("disabled", true);
			$("#SAVEX").attr("disabled", true);
			$("#HAPUSX").attr("disabled", false);
			$("#CLOSEX").attr("disabled", false);

			// Disable form fields
			$("#NO_BUKTI").attr("readonly", true);
			$("#TGL").attr("readonly", true);
			$("#JTEMPO").attr("readonly", true);
			$("#KODEC").attr("readonly", true);
			$("#ACNO").attr("readonly", true);
			$("#TBAYAR").attr("disabled", true);
			$("#NOTES").attr("readonly", true);

			// Disable detail table
			$("#detail-table input").attr("readonly", true);
			$(".btn-add-row").attr("disabled", true);
			$("#detail-table .btn-danger").attr("disabled", true);
		}

		function kosong() {
			$('#NO_BUKTI').val('+');
			$('#TGL').val('{{ date('Y-m-d') }}');
			$('#JTEMPO').val('{{ date('Y-m-d') }}');
			$('#KODEC').val('');
			$('#NAMAC').val('');
			$('#ALAMAT').val('');
			$('#KOTA').val('');
			$('#ACNO').val('');
			$('#TBAYAR').val('');
			$('#NOTES').val('');

			$('#TOTAL').val('0');
			$('#BAYAR').val('0');
			$('#LAIN').val('0');
			$('#SISA').val('0');

			// Clear detail table
			$('#detail-body').empty();
			addRow();
		}

		function hapusTrans() {
			let text = "Hapus Transaksi " + $('#NO_BUKTI').val() + "?";

			Swal.fire({
				title: 'Are you sure?',
				text: text,
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, delete it!',
				cancelButtonText: 'Cancel'
			}).then((result) => {
				if (result.isConfirmed) {
					Swal.fire({
						title: 'Deleted!',
						text: 'Data has been deleted.',
						icon: 'success',
						confirmButtonText: 'OK'
					}).then(() => {
						var loc = "{{ url('/TPembayaranPiutang/delete/' . ($header->NO_ID ?? 0)) }}";
						window.location = loc;
					});
				}
			});
		}

		function closeTrans() {
			Swal.fire({
				title: 'Are you sure?',
				text: 'Do you really want to close this page? Unsaved changes will be lost.',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Yes, close it',
				cancelButtonText: 'No, stay here'
			}).then((result) => {
				if (result.isConfirmed) {
					var loc = "{{ url('/TPembayaranPiutang/') }}";
					window.location = loc;
				} else {
					Swal.fire({
						icon: 'info',
						title: 'Cancelled',
						text: 'You stayed on the page'
					});
				}
			});
		}

		function CariBukti() {
			var cari = $("#CARI").val();
			var loc = "{{ url('/TPembayaranPiutang/edit/') }}" + '?idx={{ $header->NO_ID ?? 0 }}&tipx=search&kodex=' + encodeURIComponent(cari);
			window.location = loc;
		}

		var hasilCek;

		function cekCustomer(kodec) {
			$.ajax({
				type: "GET",
				url: "{{ url('TPembayaranPiutang/ceksup') }}",
				async: false,
				data: {
					kodec: kodec,
				},
				success: function(data) {
					if (data.length > 0) {
						$.each(data, function(i, item) {
							hasilCek = data[i].ADA;
						});
					}
				},
				error: function() {
					alert('Error cekCustomer occured');
				}
			});
			return hasilCek;
		}

		function simpan() {
			hasilCek = 0;
			$tipx = $('#tipx').val();

			// Validation
			if ($('#KODEC').val() == '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Customer Code harus diisi.'
				});
				return;
			}

			if ($('#TGL').val() == '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Tanggal harus diisi.'
				});
				return;
			}

			if ($('#JTEMPO').val() == '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Jatuh Tempo harus diisi.'
				});
				return;
			}

			// Validate detail entries
			var hasValidDetail = false;
			$('#detail-body input[name*="[NO_FAKTUR]"]').each(function() {
				if ($(this).val().trim() !== '') {
					hasValidDetail = true;
					return false; // break loop
				}
			});

			if (!hasValidDetail) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Minimal satu detail faktur harus diisi.'
				});
				return;
			}

			// Check if customer exists for new entries
			if ($tipx == 'new') {
				cekCustomer($('#KODEC').val());
				if (hasilCek == 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Customer ' + $('#KODEC').val() + ' tidak ditemukan!'
					});
					return;
				}
			}

			// Submit form
			document.getElementById("entri").submit();
		}
	</script>
@endsection
