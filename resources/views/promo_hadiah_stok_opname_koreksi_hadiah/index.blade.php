@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="{{ url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
	<link rel="stylesheet" href="{{ url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
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

		.selected {
			background-color: #007bff !important;
			color: white !important;
		}
	</style>
@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<h3 class="card-title">Stok Opname Koreksi Hadiah</h3>
							</div>
							<div class="card-body">
								<button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#columnModal">
									<i class="fas fa-filter"></i> Filter Columns
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
														<input class="form-check-input column-checkbox" type="checkbox" value="4" id="columnTotalQty" checked>
														<label class="form-check-label" for="columnTotalQty">Total Qty</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="5" id="columnNotes" checked>
														<label class="form-check-label" for="columnNotes">Notes</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="6" id="columnPosted" checked>
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
											<th width="150px" style="text-align:center">Action</th>
											<th width="150px" style="text-align:center">No Bukti</th>
											<th width="100px" style="text-align:center">Tanggal</th>
											<th width="100px" style="text-align:center">Total Qty</th>
											<th width="200px" style="text-align:center">Notes</th>
											<th width="100px" style="text-align:center">Posted</th>
										</tr>
									</thead>
									<tbody></tbody>
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
			@if (session('success'))
				Swal.fire({
					title: 'Success!',
					text: '{{ session('success') }}',
					icon: 'success',
					confirmButtonText: 'OK'
				});
			@endif

			@if (session('error'))
				Swal.fire({
					title: 'Error!',
					text: '{{ session('error') }}',
					icon: 'error',
					confirmButtonText: 'OK'
				});
			@endif

			var dataTable = $('.datatable').DataTable({
				processing: true,
				serverSide: true,
				scrollY: '400px',
				scrollX: true,
				order: [
					[2, "desc"]
				],
				ajax: {
					url: '{{ route('phstokopnamekoreksihadiah.get-data') }}'
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
						data: 'NO_BUKTI',
						name: 'NO_BUKTI'
					},
					{
						data: 'tgl',
						name: 'TGL'
					},
					{
						data: 'TOTAL_QTY',
						name: 'TOTAL_QTY'
					},
					{
						data: 'NOTES',
						name: 'NOTES'
					},
					{
						data: 'posted_status',
						name: 'POSTED'
					}
				],
				columnDefs: [{
					className: "dt-center",
					targets: [0, 1, 2, 3, 4, 6]
				}],
				dom: "<'row'<'col-md-6'><'col-md-6'>>" +
					"<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
					"<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
				stateSave: false,
			});

			$('#applyColumnToggle').on('click', function() {
				$('#columnToggleForm .column-checkbox').each(function() {
					var column = dataTable.column($(this).val());
					column.visible($(this).is(':checked'));
				});
				$('#columnModal').modal('hide');
			});

			$("div.test_btn").html(
				'<a class="btn btn-success" href="{{ route('phstokopnamekoreksihadiah') }}?status=simpan" title="Tambah Data"><i class="fas fa-plus"></i> New</a>'
			);

			$('#datatable tbody').on('click', 'tr', function() {
				if ($(this).hasClass('selected')) {
					$(this).removeClass('selected');
				} else {
					dataTable.$('tr.selected').removeClass('selected');
					$(this).addClass('selected');
				}
			});

			$('#datatable tbody').on('dblclick', 'tr', function() {
				var data = dataTable.row(this).data();
				if (data) {
					editData(data.NO_BUKTI);
				}
			});
		});

		function editData(no_bukti) {
			window.location.href = '{{ route('phstokopnamekoreksihadiah') }}?no_bukti=' + no_bukti + '&status=edit';
		}

		function printData(no_bukti) {
			$.ajax({
				url: "{{ route('phstokopnamekoreksihadiah.print') }}",
				type: 'POST',
				data: {
					no_bukti: no_bukti,
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					if (response.success && response.detail && response.detail.length > 0) {
						var printWindow = window.open('', '_blank');
						var printContent = generatePrintContent(response.header, response.detail, response.toko);
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

		function generatePrintContent(header, detail, toko) {
			var content = `
		<!DOCTYPE html>
		<html>
		<head>
			<title>Laporan Stok Opname Koreksi Hadiah</title>
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
				<h2>LAPORAN STOK OPNAME KOREKSI HADIAH</h2>
				<h4>${toko || ''}</h4>
			</div>
			<div class="info">
				<table class="info-table">
					<tr>
						<td style="width: 100px;"><strong>No Bukti</strong></td>
						<td>: ${header.no_bukti || ''}</td>
					</tr>
					<tr>
						<td><strong>Tanggal</strong></td>
						<td>: ${header.tgl || ''}</td>
					</tr>
					<tr>
						<td><strong>Notes</strong></td>
						<td>: ${header.notes || ''}</td>
					</tr>
				</table>
			</div>
			<table>
				<thead>
					<tr>
						<th class="text-center" width="5%">No</th>
						<th width="15%">Kode Barang</th>
						<th width="35%">Nama Barang</th>
						<th class="text-right" width="15%">Qty</th>
						<th width="30%">Keterangan</th>
					</tr>
				</thead>
				<tbody>`;

			detail.forEach((item, index) => {
				content += `
			<tr>
				<td class="text-center">${index + 1}</td>
				<td>${item.kd_brg}</td>
				<td>${item.na_brg}</td>
				<td class="text-right">${formatNumber(parseFloat(item.qty || 0))}</td>
				<td>${item.ket || ''}</td>
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
	</script>
@endsection
