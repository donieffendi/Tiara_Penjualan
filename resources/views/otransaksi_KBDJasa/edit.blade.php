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

		.detail-table textarea {
			height: 60px;
			font-size: 12px;
		}

		.btn-add-row {
			margin-top: 10px;
		}

		/* Grand Total row styling - aligned with table */
		.grand-total-row {
			margin-top: 10px;
			padding: 0;
		}

		.grand-total-row table {
			width: 100%;
			margin: 0;
		}

		.grand-total-row td {
			padding: 5px;
			font-size: 12px;
			vertical-align: middle;
		}

		.grand-total-input {
			height: 30px;
			font-size: 12px;
			font-weight: bold;
			color: blue !important;
			background-color: #f8f9fa;
			border: 2px solid #007bff;
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

								<form action="{{ $tipx == 'new' ? url('/TKBDJasa/store/') : url('/TKBDJasa/update/' . ($header->no_bukti ?? '')) }}" method="POST" name="entri"
									id="entri">
									@csrf
									@if ($tipx != 'new')
										@method('POST')
									@endif

									<input type="hidden" name="tipx" id="tipx" value="{{ $tipx }}">
									<input type="hidden" name="idx" id="idx" value="{{ $idx }}">
									@if ($tipx != 'new')
										<input type="hidden" name="edit_id" value="{{ $header->no_bukti ?? '' }}">
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
														<input type="text" class="form-control" id="NO_BUKTI" name="NO_BUKTI" value="{{ $header->no_bukti ?? '+' }}"
															placeholder="Auto Generate">
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="TGL" class="form-label">TANGGAL</label>
													</div>
													<div class="col-md-8">
														<input type="date" class="form-control" id="TGL" name="TGL" value="{{ $header->tgl ?? date('Y-m-d') }}">
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="KD_DEPT" class="form-label">KODE DEPT</label>
													</div>
													<div class="col-md-6">
														<input type="text" class="form-control" id="KD_DEPT" name="KD_DEPT" value="{{ $header->kd_dept ?? '' }}"
															placeholder="Kode Departemen">
													</div>
													<div class="col-md-2">
														<button type="button" class="btn btn-info btn-sm" onclick="browseDept()">Browse</button>
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="DEPT" class="form-label">NAMA DEPT</label>
													</div>
													<div class="col-md-8">
														<input type="text" class="form-control" id="DEPT" name="DEPT" value="{{ $header->dept ?? '' }}" readonly>
													</div>
												</div>
											</div>

											<!-- Right Column -->
											<div class="col-md-6 pl-4">
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="USRNM" class="form-label">USER</label>
													</div>
													<div class="col-md-8">
														<input type="text" class="form-control" id="USRNM" name="USRNM" value="{{ $header->usrnm ?? '' }}" readonly>
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-4">
														<label for="NOTES" class="form-label">NOTES</label>
													</div>
													<div class="col-md-8">
														<textarea class="form-control" id="NOTES" name="NOTES" rows="3" placeholder="Catatan">{{ $header->notes ?? '' }}</textarea>
													</div>
												</div>
											</div>
										</div>

										<!-- Detail Section -->
										<div class="row mt-4">
											<div class="col-md-12">
												<h5>Detail KBD Jasa</h5>
												<table class="table-striped detail-table table" id="detail-table">
													<thead class="table-dark">
														<tr>
															<th width="30px">No</th>
															<th width="150px">Nama Barang</th>
															<th width="80px">Type/Ukuran</th>
															<th width="80px">Merk</th>
															<th width="80px">Kemasan</th>
															<th width="80px">Qty</th>
															<th width="100px">Harga</th>
															<th width="100px">Total</th>
															<th width="90px">Batas Waktu</th>
															<th width="90px">Akhir Waktu</th>
															<th width="100px">POS AKT</th>
															<th width="120px">KET</th>
															<th width="60px">Action</th>
														</tr>
													</thead>
													<tbody id="detail-body">
														@if (!empty($detail) && count($detail) > 0)
															@foreach ($detail as $index => $item)
																<tr data-index="{{ $index }}">
																	<td>{{ $index + 1 }}</td>
																	<td><input type="text" class="form-control" name="detail[{{ $index }}][na_brg]" value="{{ $item->na_brg }}"
																			placeholder="Nama Barang"></td>
																	<td><input type="text" class="form-control" name="detail[{{ $index }}][ukuran]" value="{{ $item->ukuran }}"
																			placeholder="Type/Ukuran"></td>
																	<td><input type="text" class="form-control" name="detail[{{ $index }}][merk]" value="{{ $item->merk }}"
																			placeholder="Merk"></td>
																	<td><input type="text" class="form-control" name="detail[{{ $index }}][satuan]" value="{{ $item->satuan }}"
																			placeholder="Kemasan"></td>
																	<td><input type="number" class="form-control text-right" name="detail[{{ $index }}][qty]" value="{{ $item->qty }}"
																			step="0.01" onchange="calculateRowTotal({{ $index }})"></td>
																	<td><input type="number" class="form-control text-right" name="detail[{{ $index }}][harga]" value="{{ $item->harga }}"
																			step="0.01" onchange="calculateRowTotal({{ $index }})"></td>
																	<td><input type="number" class="form-control text-right" name="detail[{{ $index }}][total]" value="{{ $item->total }}"
																			step="0.01" readonly></td>
																	<td><input type="date" class="form-control" name="detail[{{ $index }}][batas1]" value="{{ $item->batas1 }}"></td>
																	<td><input type="date" class="form-control" name="detail[{{ $index }}][batas2]" value="{{ $item->batas2 }}"></td>
																	<td><input type="text" class="form-control" name="detail[{{ $index }}][akunt]" value="{{ $item->akunt }}"
																			placeholder="POS AKT"></td>
																	<td>
																		<textarea class="form-control" name="detail[{{ $index }}][ket]" placeholder="KET">{{ $item->ket }}</textarea>
																	</td>
																	<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Del</button></td>
																</tr>
															@endforeach
														@else
															<tr data-index="0">
																<td>1</td>
																<td><input type="text" class="form-control" name="detail[0][na_brg]" placeholder="Nama Barang"></td>
																<td><input type="text" class="form-control" name="detail[0][ukuran]" placeholder="Type/Ukuran"></td>
																<td><input type="text" class="form-control" name="detail[0][merk]" placeholder="Merk"></td>
																<td><input type="text" class="form-control" name="detail[0][satuan]" placeholder="Kemasan"></td>
																<td><input type="number" class="form-control text-right" name="detail[0][qty]" step="0.01" onchange="calculateRowTotal(0)">
																</td>
																<td><input type="number" class="form-control text-right" name="detail[0][harga]" step="0.01" onchange="calculateRowTotal(0)">
																</td>
																<td><input type="number" class="form-control text-right" name="detail[0][total]" step="0.01" readonly></td>
																<td><input type="date" class="form-control" name="detail[0][batas1]"></td>
																<td><input type="date" class="form-control" name="detail[0][batas2]"></td>
																<td><input type="text" class="form-control" name="detail[0][akunt]" placeholder="POS AKT"></td>
																<td>
																	<textarea class="form-control" name="detail[0][ket]" placeholder="KET"></textarea>
																</td>
																<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Del</button></td>
															</tr>
														@endif
													</tbody>
												</table>

												<!-- Grand Total Row - Aligned with Total Column -->
												<div class="grand-total-row">
													<table class="table" style="margin-bottom: 0;">
														<tbody>
															<tr>
																<td width="30px"></td> <!-- No column -->
																<td width="150px"></td> <!-- Nama Barang -->
																<td width="80px"></td> <!-- Type/Ukuran -->
																<td width="80px"></td> <!-- Merk -->
																<td width="80px"></td> <!-- Kemasan -->
																<td width="80px"></td> <!-- Qty -->
																<td width="100px" style="text-align: right; font-weight: bold; color: blue;">GRAND TOTAL:</td> <!-- Harga -->
																<td width="100px"> <!-- Total column -->
																	<input type="number" class="form-control grand-total-input text-right" id="GRAND_TOTAL" name="GRAND_TOTAL"
																		value="{{ $header->total ?? 0 }}" step="0.01" readonly>
																</td>
																<td width="90px"></td> <!-- Batas Waktu -->
																<td width="90px"></td> <!-- Akhir Waktu -->
																<td width="100px"></td> <!-- POS AKT -->
																<td width="120px"></td> <!-- KET -->
																<td width="60px"></td> <!-- Action -->
															</tr>
														</tbody>
													</table>
												</div>

												<button type="button" class="btn btn-primary btn-sm btn-add-row" onclick="addRow()">
													<i class="fa fa-plus"></i> Tambah Baris
												</button>
											</div>
										</div>

										<!-- Buttons -->
										<div class="col-md-12 form-group row mt-3">
											<div class="col-md-4">
												<button type="button" id='TOPX' onclick="location.href='{{ url('/TKBDJasa/edit/?idx=' . $idx . '&tipx=top') }}'"
													class="btn btn-outline-primary">Top</button>
												<button type="button" id='PREVX'
													onclick="location.href='{{ url('/TKBDJasa/edit/?idx=' . ($header->no_bukti ?? '') . '&tipx=prev&kodex=' . ($header->no_bukti ?? '')) }}'"
													class="btn btn-outline-primary">Prev</button>
												<button type="button" id='NEXTX'
													onclick="location.href='{{ url('/TKBDJasa/edit/?idx=' . ($header->no_bukti ?? '') . '&tipx=next&kodex=' . ($header->no_bukti ?? '')) }}'"
													class="btn btn-outline-primary">Next</button>
												<button type="button" id='BOTTOMX' onclick="location.href='{{ url('/TKBDJasa/edit/?idx=' . $idx . '&tipx=bottom') }}'"
													class="btn btn-outline-primary">Bottom</button>
											</div>
											<div class="col-md-5">
												<button type="button" id='NEWX' onclick="location.href='{{ url('/TKBDJasa/edit/?idx=0&tipx=new') }}'"
													class="btn btn-warning">New</button>
												<button type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>
												<button type="button" id='UNDOX' onclick="location.href='{{ url('/TKBDJasa/edit/?idx=' . $idx . '&tipx=undo') }}'"
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

	<!-- Department Browse Modal -->
	<div class="modal fade" id="browseDepartmentModal" tabindex="-1" role="dialog" aria-labelledby="browseDepartmentModalLabel"
		aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseDepartmentModalLabel">Pilih Departemen</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<input type="text" id="searchDepartment" class="form-control mb-3" placeholder="Cari departemen...">
					<table class="table-striped table" id="departmentTable">
						<thead>
							<tr>
								<th>Kode</th>
								<th>Nama Departemen</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody id="departmentTableBody">
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
			calculateGrandTotal();

			// Department code lookup
			$('#KD_DEPT').on('blur', function() {
				var kdDept = $(this).val();
				if (kdDept) {
					getDepartmentData(kdDept);
				}
			});

			// Department search functionality
			$('#searchDepartment').on('keyup', function() {
				var query = $(this).val();
				if (query.length > 2) {
					searchDepartments(query);
				}
			});

		});

		function addRow() {
			detailRowIndex++;
			var newRow = `
				<tr data-index="${detailRowIndex}">
					<td>${detailRowIndex + 1}</td>
					<td>
						<input type="text" class="form-control" name="detail[${detailRowIndex}][NA_BRG]" placeholder="Nama Barang">
					</td>
					<td>
						<input type="number" class="form-control text-right" name="detail[${detailRowIndex}][QTY]" step="0.01" onchange="calculateRowTotal(${detailRowIndex})">
					</td>
					<td>
						<input type="text" class="form-control" name="detail[${detailRowIndex}][SATUAN]" placeholder="Satuan">
					</td>
					<td>
						<input type="text" class="form-control" name="detail[${detailRowIndex}][UKURAN]" placeholder="Ukuran">
					</td>
					<td>
						<input type="text" class="form-control" name="detail[${detailRowIndex}][MERK]" placeholder="Merk">
					</td>
					<td>
						<input type="number" class="form-control text-right" name="detail[${detailRowIndex}][HARGA]" step="0.01" onchange="calculateRowTotal(${detailRowIndex})">
					</td>
					<td>
						<input type="number" class="form-control text-right" name="detail[${detailRowIndex}][TOTAL]" step="0.01" readonly>
					</td>
					<td>
						<input type="date" class="form-control" name="detail[${detailRowIndex}][BATAS1]">
					</td>
					<td>
						<input type="date" class="form-control" name="detail[${detailRowIndex}][BATAS2]">
					</td>
					<td>
						<input type="text" class="form-control" name="detail[${detailRowIndex}][AKUNT]" placeholder="Account">
					</td>
					<td>
						<textarea class="form-control" name="detail[${detailRowIndex}][KET]" placeholder="Keterangan"></textarea>
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
			calculateGrandTotal();
		}

		function updateRowNumbers() {
			$('#detail-body tr').each(function(index) {
				$(this).find('td:first').text(index + 1);
				$(this).attr('data-index', index);

				// Update input names
				$(this).find('input[name*="[NA_BRG]"]').attr('name', `detail[${index}][NA_BRG]`);
				$(this).find('input[name*="[QTY]"]').attr('name', `detail[${index}][QTY]`);
				$(this).find('input[name*="[SATUAN]"]').attr('name', `detail[${index}][SATUAN]`);
				$(this).find('input[name*="[UKURAN]"]').attr('name', `detail[${index}][UKURAN]`);
				$(this).find('input[name*="[MERK]"]').attr('name', `detail[${index}][MERK]`);
				$(this).find('input[name*="[HARGA]"]').attr('name', `detail[${index}][HARGA]`);
				$(this).find('input[name*="[TOTAL]"]').attr('name', `detail[${index}][TOTAL]`);
				$(this).find('input[name*="[BATAS1]"]').attr('name', `detail[${index}][BATAS1]`);
				$(this).find('input[name*="[BATAS2]"]').attr('name', `detail[${index}][BATAS2]`);
				$(this).find('input[name*="[AKUNT]"]').attr('name', `detail[${index}][AKUNT]`);
				$(this).find('textarea[name*="[KET]"]').attr('name', `detail[${index}][KET]`);

				// Update onchange events
				$(this).find('input[name*="[QTY]"]').attr('onchange', `calculateRowTotal(${index})`);
				$(this).find('input[name*="[HARGA]"]').attr('onchange', `calculateRowTotal(${index})`);
			});
			detailRowIndex = $('#detail-body tr').length - 1;
		}

		function calculateRowTotal(rowIndex) {
			var qty = parseFloat($(`input[name="detail[${rowIndex}][QTY]"]`).val()) || 0;
			var harga = parseFloat($(`input[name="detail[${rowIndex}][HARGA]"]`).val()) || 0;

			var total = qty * harga;
			$(`input[name="detail[${rowIndex}][TOTAL]"]`).val(total.toFixed(2));

			calculateGrandTotal();
		}

		function calculateGrandTotal() {
			var grandTotal = 0;

			// Calculate grand total from detail rows
			$('#detail-body input[name*="[TOTAL]"]').each(function() {
				grandTotal += parseFloat($(this).val()) || 0;
			});

			// Update grand total
			$('#GRAND_TOTAL').val(grandTotal.toFixed(2));
		}

		function getDepartmentData(kdDept) {
			$.ajax({
				type: "GET",
				url: "{{ url('TKBDJasa/get-select-kodes') }}",
				data: {
					kodes: kdDept
				},
				success: function(data) {
					if (data.length > 0) {
						var dept = data[0];
						$('#DEPT').val(dept.namas);
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Department Not Found',
							text: 'Department code not found. Please check or browse for valid department.'
						});
						$('#KD_DEPT').focus();
					}
				},
				error: function() {
					alert('Error occurred while fetching department data');
				}
			});
		}

		function browseDept() {
			$('#browseDepartmentModal').modal('show');
			searchDepartments('');
		}

		function searchDepartments(query) {
			$.ajax({
				type: "GET",
				url: "{{ url('TKBDJasa/browse') }}",
				data: {
					q: query
				},
				success: function(data) {
					var tbody = $('#departmentTableBody');
					tbody.empty();

					if (data.length > 0) {
						$.each(data, function(index, dept) {
							var row = `
								<tr>
									<td>${dept.kodes}</td>
									<td>${dept.namas}</td>
									<td><button type="button" class="btn btn-primary btn-sm" onclick="selectDepartment('${dept.kodes}', '${dept.namas}')">Select</button></td>
								</tr>
							`;
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="3" class="text-center">No departments found</td></tr>');
					}
				},
				error: function() {
					alert('Error occurred while searching departments');
				}
			});
		}

		function selectDepartment(kdDept, namaDept) {
			$('#KD_DEPT').val(kdDept);
			$('#DEPT').val(namaDept);
			$('#browseDepartmentModal').modal('hide');
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
			$("#KD_DEPT").attr("readonly", false);
			$("#NOTES").attr("readonly", false);

			// Enable detail table
			$("#detail-table input").attr("readonly", false);
			$("#detail-table textarea").attr("readonly", false);
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
			$("#KD_DEPT").attr("readonly", true);
			$("#NOTES").attr("readonly", true);

			// Disable detail table
			$("#detail-table input").attr("readonly", true);
			$("#detail-table textarea").attr("readonly", true);
			$(".btn-add-row").attr("disabled", true);
			$("#detail-table .btn-danger").attr("disabled", true);
		}

		function kosong() {
			$('#NO_BUKTI').val('+');
			$('#TGL').val('{{ date('Y-m-d') }}');
			$('#KD_DEPT').val('');
			$('#DEPT').val('');
			$('#NOTES').val('');
			$('#USRNM').val('');

			$('#GRAND_TOTAL').val('0');

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
						var loc = "{{ url('/TKBDJasa/delete/' . ($header->no_bukti ?? '')) }}";
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
					var loc = "{{ url('/TKBDJasa/') }}";
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
			var loc = "{{ url('/TKBDJasa/edit/') }}" + '?idx={{ $header->no_bukti ?? '' }}&tipx=search&kodex=' + encodeURIComponent(cari);
			window.location = loc;
		}

		var hasilCek;

		function cekDepartment(kdDept) {
			$.ajax({
				type: "GET",
				url: "{{ url('TKBDJasa/ceksup') }}",
				async: false,
				data: {
					kodes: kdDept,
				},
				success: function(data) {
					if (data.length > 0) {
						$.each(data, function(i, item) {
							hasilCek = data[i].ADA;
						});
					}
				},
				error: function() {
					alert('Error cekDepartment occured');
				}
			});
			return hasilCek;
		}

		function simpan() {
			hasilCek = 0;
			$tipx = $('#tipx').val();

			// Validation
			if ($('#KD_DEPT').val() == '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Kode Departemen harus diisi.'
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

			// Validate detail entries
			var hasValidDetail = false;
			$('#detail-body input[name*="[NA_BRG]"]').each(function() {
				if ($(this).val().trim() !== '') {
					hasValidDetail = true;
					return false; // break loop
				}
			});

			if (!hasValidDetail) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Minimal satu detail barang harus diisi.'
				});
				return;
			}

			// Check if department exists for new entries
			if ($tipx == 'new') {
				cekDepartment($('#KD_DEPT').val());
				if (hasilCek == 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Departemen ' + $('#KD_DEPT').val() + ' tidak ditemukan!'
					});
					return;
				}
			}

			// Submit form
			document.getElementById("entri").submit();
		}
	</script>
@endsection
