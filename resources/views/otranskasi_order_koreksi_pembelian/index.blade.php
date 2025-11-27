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
														<label class="form-check-label" for="columnTanggal">Tgl</label>
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
														<input class="form-check-input column-checkbox" type="checkbox" value="6" id="columnPBI" checked>
														<label class="form-check-label" for="columnPBI">PBI</label>
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
											<th width="100px" style="text-align:center">Tgl</th>
											<th width="120px" style="text-align:center">Total Qty</th>
											<th width="200px" style="text-align:center">Notes</th>
											<th width="100px" style="text-align:center">PBI</th>
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
				scrollY: '400px',
				order: [
					[2, "desc"]
				],
				ajax: {
					url: '{{ route('torderkoreksipembelian.get-data') }}'
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
						data: 'OPERATOR',
						name: 'OPERATOR',
						className: 'text-center'
					}
				],
				columnDefs: [{
					className: "dt-center",
					targets: [0, 1, 2, 3, 6]
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
				'<a class="btn btn-success" href="{{ route('torderkoreksipembelian.edit') }}?status=simpan" title="Tambah Data"><i class="fas fa-plus"></i> New</a>'
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
				editData(data.NO_BUKTI);
			});
		});

		function editData(no_bukti) {
			window.location.href = '{{ route('torderkoreksipembelian.edit') }}?no_bukti=' + no_bukti + '&status=edit';
		}

		function printData(no_bukti) {
			Swal.fire({
				title: 'Proses Posting & Print',
				text: 'Data akan diposting dan dicetak. Lanjutkan?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: 'Ya, Lanjutkan',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({
						url: "{{ route('torderkoreksipembelian.print') }}",
						type: 'POST',
						data: {
							no_bukti: no_bukti,
							_token: '{{ csrf_token() }}'
						},
						success: function(response) {
							if (response.data && response.data.length > 0) {
								Swal.fire({
									icon: 'success',
									title: 'Success',
									text: 'POSTING SELESAI',
									timer: 2000
								});
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
			});
		}

		function deleteData(no_bukti) {
			Swal.fire({
				title: 'Konfirmasi Hapus',
				text: 'Apakah kode ini ' + no_bukti + ' akan di hapus?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Ya, Hapus!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({
						url: "{{ route('torderkoreksipembelian.delete', '') }}/" + no_bukti,
						type: 'DELETE',
						data: {
							_token: '{{ csrf_token() }}'
						},
						success: function(response) {
							if (response.success) {
								Swal.fire({
									icon: 'success',
									title: 'Berhasil',
									text: response.message
								}).then(() => {
									$('.datatable').DataTable().ajax.reload();
								});
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
								text: 'Gagal menghapus data'
							});
						}
					});
				}
			});
		}

		function generatePrintContent(data) {
			var content = `
				<!DOCTYPE html>
				<html>
				<head>
					<title>Order Koreksi Pembelian</title>
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
						<h2>ORDER KOREKSI PEMBELIAN</h2>
					</div>
					<div class="info">
						<table style="border: none;">
							<tr style="border: none;">
								<td style="border: none; width: 100px;"><strong>No Bukti</strong></td>
								<td style="border: none;">: ${data[0].NO_BUKTI}</td>
								<td style="border: none; width: 100px;"><strong>Periode</strong></td>
								<td style="border: none;">: ${data[0].per}</td>
							</tr>
							<tr style="border: none;">
								<td style="border: none;"><strong>Supplier</strong></td>
								<td style="border: none;">: ${data[0].namas}</td>
								<td style="border: none;"><strong>Hari</strong></td>
								<td style="border: none;">: ${data[0].hari}</td>
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
								<th>Kode</th>
								<th>Nama Barang</th>
								<th>Ukuran</th>
								<th>Kemasan</th>
								<th class="text-right">Qty</th>
								<th class="text-right">MO</th>
								<th class="text-right">Harga</th>
								<th class="text-right">Total</th>
							</tr>
						</thead>
						<tbody>`;

			var totalQty = 0;
			var totalAmount = 0;

			data.forEach((item, index) => {
				totalQty += parseFloat(item.qty) || 0;
				totalAmount += parseFloat(item.total) || 0;
				content += `
					<tr>
						<td class="text-center">${index + 1}</td>
						<td>${item.KD_BRG}</td>
						<td>${item.NA_BRG}</td>
						<td>${item.ket_uk || ''}</td>
						<td>${item.ket_kem}</td>
						<td class="text-right">${parseFloat(item.qty).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
						<td class="text-right">${parseFloat(item.mo).toLocaleString('id-ID', {minimumFractionDigits: 0})}</td>
						<td class="text-right">${parseFloat(item.harga).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
						<td class="text-right">${parseFloat(item.total).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
					</tr>`;
			});

			content += `
							<tr>
								<td colspan="5" class="text-center"><strong>TOTAL</strong></td>
								<td class="text-right"><strong>${totalQty.toLocaleString('id-ID', {minimumFractionDigits: 2})}</strong></td>
								<td colspan="2"></td>
								<td class="text-right"><strong>${totalAmount.toLocaleString('id-ID', {minimumFractionDigits: 2})}</strong></td>
							</tr>
						</tbody>
					</table>
				</body>
				</html>`;
			return content;
		}
	</script>
@endsection
