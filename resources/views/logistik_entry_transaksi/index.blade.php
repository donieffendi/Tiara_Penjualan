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
		background-color: #0077ff !important;
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

	.print-checkbox {
		transform: scale(1.2);
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
														<input class="form-check-input column-checkbox" type="checkbox" value="4" id="columnTotalQty" checked>
														<label class="form-check-label" for="columnTotalQty">Total Qty</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="5" id="columnNotes" checked>
														<label class="form-check-label" for="columnNotes">Notes</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="6" id="columnType" checked>
														<label class="form-check-label" for="columnType">Type</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="7" id="columnPosted" checked>
														<label class="form-check-label" for="columnPosted">Posted</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="8" id="columnPrint" checked>
														<label class="form-check-label" for="columnPrint">Print</label>
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
											<th width="100px" style="text-align:center">Total Qty</th>
											<th width="200px" style="text-align:center">Notes</th>
											<th width="80px" style="text-align:center">Type</th>
											<th width="100px" style="text-align:center">Posted</th>
											<th width="80px" style="text-align:center">Print</th>
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
					[1, "desc"]
				], // Order by No Bukti descending
				ajax: {
					url: '{{ route('lentrytransaksi.get-data') }}'
				},
				columns: [{
						data: 'DT_RowIndex',
						name: 'DT_RowIndex',
						orderable: false,
						searchable: false
					},
					{
						data: 'NO_BUKTI',
						name: 'NO_BUKTI'
					},
					{
						data: 'TGL',
						name: 'TGL'
					},
					{
						data: 'TOTAL_QTY',
						name: 'TOTAL_QTY',
						className: 'text-right'
					},
					{
						data: 'NOTES',
						name: 'NOTES'
					},
					{
						data: 'TYPE',
						name: 'TYPE',
						render: function(data, type, row, meta) {
							if (data == 'IN') {
								return '<span class="badge badge-pill badge-success">' + data + '</span>';
							} else if (data == 'OUT') {
								return '<span class="badge badge-pill badge-danger">' + data + '</span>';
							} else {
								return '<span class="badge badge-pill badge-warning">' + data + '</span>';
							}
						}
					},
					{
						data: 'POSTED',
						name: 'POSTED',
						render: function(data, type, row, meta) {
							return data == 1 ?
								'<span class="badge badge-pill badge-success">Posted</span>' :
								'<span class="badge badge-pill badge-danger">Unposted</span>';
						}
					},
					{
						data: 'print',
						name: 'print',
						orderable: false,
						searchable: false,
						render: function(data, type, row, meta) {
							var checked = data == 1 ? 'checked' : '';
							return '<input type="checkbox" class="print-checkbox" data-no-bukti="' + row.NO_BUKTI + '" ' + checked +
								'>';
						}
					},
					{
						data: 'action',
						name: 'action',
						orderable: false,
						searchable: false
					}
				],
				columnDefs: [{
					"className": "dt-center",
					"targets": [0, 1, 2, 3, 6, 7, 8]
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

			// Add new button and batch print button
			$("div.test_btn").html(
				'<a class="btn btn-success" href="{{ route('lentrytransaksi.edit') }}?status=simpan" title="Tambah Data"><i class="fas fa-plus"></i> New</a>' +
				'<button class="btn btn-info ml-2" onclick="batchPrint()" title="Batch Print"><i class="fas fa-print"></i> Batch Print</button>'
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
				if (data.POSTED == 0) {
					editData(data.NO_BUKTI);
				} else {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Data sudah terposting!'
					});
				}
			});

			// Print checkbox toggle (Space key functionality from Delphi)
			$('#datatable tbody').on('change', '.print-checkbox', function() {
				var no_bukti = $(this).data('no-bukti');
				togglePrint(no_bukti);
			});

			// Space key handler for print toggle (matching Delphi logic)
			$(document).on('keyup', function(e) {
				if (e.keyCode === 32) { // Space key
					var selectedRow = dataTable.row('.selected');
					if (selectedRow.length > 0) {
						var data = selectedRow.data();
						var checkbox = selectedRow.node().querySelector('.print-checkbox');
						checkbox.checked = !checkbox.checked;
						togglePrint(data.NO_BUKTI);
					}
				}
				// Delete key handler
				if (e.keyCode === 46) { // Delete key
					var selectedRow = dataTable.row('.selected');
					if (selectedRow.length > 0) {
						var data = selectedRow.data();
						if (data.POSTED == 0) {
							deleteData(data.NO_BUKTI);
						} else {
							Swal.fire({
								icon: 'warning',
								title: 'Warning',
								text: 'Data sudah terposting, tidak dapat dihapus!'
							});
						}
					}
				}
			});
		});

		// Edit function called from action button
		function editData(no_bukti) {
			window.location.href = '{{ route('lentrytransaksi.edit') }}?no_bukti=' + no_bukti + '&status=edit';
		}

		// Print function called from action button
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

		// Toggle print status (Space key functionality from Delphi)
		function togglePrint(no_bukti) {
			$.ajax({
				url: "{{ route('lentrytransaksi.toggle-print') }}",
				type: 'POST',
				data: {
					no_bukti: no_bukti,
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					// Refresh table to show updated print status
					$('.datatable').DataTable().ajax.reload(null, false);
				},
				error: function() {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Gagal mengubah status print'
					});
				}
			});
		}

		// Batch print function (btnPrintClick from Delphi)
		function batchPrint() {
			Swal.fire({
				title: 'Konfirmasi Batch Print',
				text: 'Cetak semua dokumen yang telah ditandai?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Ya, Print!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({
						url: "{{ route('lentrytransaksi.batch-print') }}",
						type: 'POST',
						data: {
							_token: '{{ csrf_token() }}'
						},
						success: function(response) {
							if (response.success) {
								Swal.fire({
									icon: 'success',
									title: 'Success',
									text: response.message
								});
								$('.datatable').DataTable().ajax.reload(null, false);
							} else {
								Swal.fire({
									icon: 'error',
									title: 'Error',
									text: response.message
								});
							}
						},
						error: function() {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: 'Gagal melakukan batch print'
							});
						}
					});
				}
			});
		}

		// Delete function
		function deleteData(no_bukti) {
			Swal.fire({
				title: 'Konfirmasi',
				text: 'Apakah No Bukti ' + no_bukti + ' akan dihapus?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Ya, Hapus!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					window.location.href = "{{ route('lentrytransaksi.delete', '') }}/" + no_bukti;
				}
			});
		}

		function generatePrintContent(data) {
			var content = `
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
						<p>Waktu: ${data[0].waktu}</p>
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
						<tbody>`;

			data.forEach((item, index) => {
				content += `
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
					</tr>`;
			});

			content += `
						</tbody>
					</table>
				</body>
				</html>`;

			return content;
		}
	</script>
@endsection
