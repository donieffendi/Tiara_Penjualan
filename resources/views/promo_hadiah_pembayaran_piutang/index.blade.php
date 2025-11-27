@extends('layouts.plain')
@section('styles')
	<link rel="stylesheet" href="{{ url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
	<link rel="stylesheet" href="{{ url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
@endsection

<style>
	th {
		font-size: 13px;
	}

	td {
		font-size: 13px;
	}

	.content-header {
		padding: 0 !important;
	}

	.badge-warning {
		background-color: #ffc107 !important;
		color: white !important;
	}

	.badge-success {
		background-color: #28a745 !important;
		color: white !important;
	}

	.badge-danger {
		background-color: #dc3545 !important;
		color: white !important;
	}

	.selected {
		background-color: #007bff !important;
		color: white !important;
	}
</style>

@section('content')
	<div class="content-wrapper">

		<!-- Status Messages -->
		@if (session('status'))
			<script>
				Swal.fire({
					title: 'Success!',
					text: '{{ session('status') }}',
					icon: 'success',
					confirmButtonText: 'OK'
				})
			</script>
		@endif

		@if (session('error'))
			<script>
				Swal.fire({
					title: 'Error!',
					text: '{{ session('error') }}',
					icon: 'error',
					confirmButtonText: 'OK'
				})
			</script>
		@endif

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">

								<!-- Filter Columns Modal -->
								<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#columnModal">
									Filter Columns
								</button>

								<div class="modal fade" id="columnModal" tabindex="-1" aria-labelledby="columnModalLabel" aria-hidden="true">
									<div class="modal-dialog">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="modal-title" id="columnModalLabel">Toggle Columns</h5>
												<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
											</div>
											<div class="modal-body">
												<form id="columnToggleForm">
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="0" id="columnNo" checked>
														<label class="form-check-label" for="columnNo">No</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="1" id="columnAction" checked>
														<label class="form-check-label" for="columnAction">Action</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="2" id="columnNoBukti" checked>
														<label class="form-check-label" for="columnNoBukti">No Bukti</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="3" id="columnTanggal" checked>
														<label class="form-check-label" for="columnTanggal">Tanggal</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="4" id="columnKode" checked>
														<label class="form-check-label" for="columnKode">Kode</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="5" id="columnCustomer" checked>
														<label class="form-check-label" for="columnCustomer">Customer</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="6" id="columnBG" checked>
														<label class="form-check-label" for="columnBG">BG</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="7" id="columnTotal" checked>
														<label class="form-check-label" for="columnTotal">Total</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="8" id="columnBayar" checked>
														<label class="form-check-label" for="columnBayar">Bayar</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="9" id="columnLain" checked>
														<label class="form-check-label" for="columnLain">Lain</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="10" id="columnPosted" checked>
														<label class="form-check-label" for="columnPosted">Posted</label>
													</div>
												</form>
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
												<button type="button" class="btn btn-primary" id="applyColumnToggle">Apply</button>
											</div>
										</div>
									</div>
								</div>

								<table class="table-striped table-border table-hover nowrap datatable table table-fixed" id="datatable">
									<thead class="table-dark">
										<tr>
											<th width="50px" style="text-align:center">No</th>
											<th width="120px" style="text-align:center">Action</th>
											<th width="150px" style="text-align:center">No Bukti</th>
											<th width="100px" style="text-align:center">Tanggal</th>
											<th width="100px" style="text-align:center">Kode</th>
											<th width="200px" style="text-align:center">Customer</th>
											<th width="100px" style="text-align:center">BG</th>
											<th width="120px" style="text-align:center">Total</th>
											<th width="120px" style="text-align:center">Bayar</th>
											<th width="120px" style="text-align:center">Lain</th>
											<th width="100px" style="text-align:center">Posted</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script src="{{ url('AdminLTE/plugins/datatables/jquery.dataTables.js') }}"></script>
	<script src="{{ url('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>
	<script src="{{ url('http://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		$(document).ready(function() {
			var dataTable = $('.datatable').DataTable({
				processing: true,
				serverSide: true,
				'scrollY': '400px',
				"order": [
					[2, "desc"]
				], // Order by No Bukti descending
				ajax: {
					url: "{{ route('phpembayaranpiutang/get-data') }}"
				},
				columns: [{	
						data: 'DT_RowIndex',
						name: 'DT_RowIndex',
						orderable: false,
						searchable: false
					},
					{
						data: 'action',
						name: 'action',
						orderable: false,
						searchable: false
					},
					{
						data: 'no_bukti',
						name: 'no_bukti'
					},
					{
						data: 'tgl',
						name: 'tgl'
					},
					{
						data: 'kodec',
						name: 'kodec'
					},
					{
						data: 'namac',
						name: 'namac'
					},
					{
						data: 'tbayar',
						name: 'tbayar'
					},
					{
						data: 'total',
						name: 'total',
						className: 'text-right'
					},
					{
						data: 'bayar',
						name: 'bayar',
						className: 'text-right'
					},
					{
						data: 'lain',
						name: 'lain',
						className: 'text-right'
					},
					{
						data: 'posted',
						name: 'posted',
						render: function(data, type, row, meta) {
							return data == 1 ?
								'<span class="badge badge-pill badge-success">Posted</span>' :
								'<span class="badge badge-pill badge-warning">Open</span>';
						}
					}
				],
				columnDefs: [{
					"className": "dt-center",
					"targets": [0, 1, 2, 3, 4, 6, 10]
				}],
				dom: "<'row'<'col-md-6'><'col-md-6'>>" +
					"<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
					"<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
				stateSave: false,
			});

			// Handle column visibility toggle
			$('#applyColumnToggle').on('click', function() {
				$('#columnToggleForm .column-checkbox').each(function() {
					var column = dataTable.column($(this).val());
					column.visible($(this).is(':checked'));
				});
				$('#columnModal').modal('hide');
			});

			// Add new button
			$("div.test_btn").html(
				'<a class="btn btn-success" href="{{ route('phpembayaranpiutang.edit') }}?status=simpan" title="Tambah Data"><i class="fas fa-plus"></i> New</a>'
			);

			// Row selection
			$('#datatable tbody').on('click', 'tr', function() {
				if ($(this).hasClass('selected')) {
					$(this).removeClass('selected');
				} else {
					dataTable.$('tr.selected').removeClass('selected');
					$(this).addClass('selected');
				}
			});

			// Double click to edit (matching Delphi logic)
			$('#datatable tbody').on('dblclick', 'tr', function() {
				var data = dataTable.row(this).data();
				if (data.posted == 0) {
					editData(data.no_bukti);
				} else {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Nota Sudah Terposting !!'
					});
				}
			});
		});

		// Edit function called from action button
		function editData(no_bukti) {
			window.location.href = '{{ route('phpembayaranpiutang.edit') }}?no_bukti=' + no_bukti + '&status=edit';
		}

		// Print function called from action button
		

		function generatePrintContent(data, terbilang) {
			var total = 0;
			data.forEach(function(item) {
				total += parseFloat(item.total) || 0;
			});

			var content = `
				<!DOCTYPE html>
				<html>
				<head>
					<title>Laporan Pembayaran Piutang</title>
					<style>
						body { font-family: Arial, sans-serif; font-size: 12px; }
						table { width: 100%; border-collapse: collapse; margin-top: 10px; }
						th, td { border: 1px solid #000; padding: 5px; text-align: left; }
						th { background-color: #f0f0f0; font-weight: bold; }
						.text-center { text-align: center; }
						.text-right { text-align: right; }
						.header { text-align: center; margin-bottom: 20px; }
						.info { margin-bottom: 10px; }
					</style>
				</head>
				<body>
					<div class="header">
						<h2>INSTRUKSI PENAGIHAN</h2>
					</div>
					<div class="info">
						<table style="border: none;">
							<tr style="border: none;">
								<td style="border: none; width: 100px;"><strong>No Bukti</strong></td>
								<td style="border: none;">: ${data[0].no_bukti}</td>
								<td style="border: none; width: 100px;"><strong>Tanggal</strong></td>
								<td style="border: none;">: ${data[0].tgl}</td>
							</tr>
							<tr style="border: none;">
								<td style="border: none;"><strong>Customer</strong></td>
								<td style="border: none;">: ${data[0].namac}</td>
								<td style="border: none;"><strong>Jatuh Tempo</strong></td>
								<td style="border: none;">: ${data[0].jtempo}</td>
							</tr>
							<tr style="border: none;">
								<td style="border: none;"><strong>Alamat</strong></td>
								<td style="border: none;">: ${data[0].alamat}</td>
								<td style="border: none;"><strong>Type Bayar</strong></td>
								<td style="border: none;">: ${data[0].tbayar}</td>
							</tr>
							<tr style="border: none;">
								<td style="border: none;"><strong>Kota</strong></td>
								<td style="border: none;">: ${data[0].kota}</td>
								<td style="border: none;"><strong>Account</strong></td>
								<td style="border: none;">: ${data[0].acno}</td>
							</tr>
							<tr style="border: none;">
								<td style="border: none;"><strong>Notes</strong></td>
								<td colspan="3" style="border: none;">: ${data[0].notes || ''}</td>
							</tr>
						</table>
					</div>
					<table>
						<thead>
							<tr>
								<th class="text-center">No</th>
								<th>No Invoice</th>
								<th>Tgl Invoice</th>
								<th class="text-right">Total</th>
								<th class="text-right">Bayar</th>
								<th class="text-right">Lain</th>
								<th class="text-right">Sisa</th>
								<th>Uraian</th>
							</tr>
						</thead>
						<tbody>`;

			data.forEach((item, index) => {
				content += `
					<tr>
						<td class="text-center">${index + 1}</td>
						<td>${item.no_faktur}</td>
						<td>${item.tgl_faktur}</td>
						<td class="text-right">${parseFloat(item.total).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
						<td class="text-right">${parseFloat(item.bayar).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
						<td class="text-right">${parseFloat(item.lain).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
						<td class="text-right">${parseFloat(item.sisa).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
						<td>${item.uraian || ''}</td>
					</tr>`;
			});

			content += `
							<tr>
								<td colspan="3" class="text-center"><strong>TOTAL</strong></td>
								<td class="text-right"><strong>${total.toLocaleString('id-ID', {minimumFractionDigits: 2})}</strong></td>
								<td colspan="4"></td>
							</tr>
						</tbody>
					</table>
					<div style="margin-top: 10px;">
						<strong>Terbilang:</strong> ${terbilang}
					</div>
				</body>
				</html>`;

			return content;
		}
	</script>
@endsection
