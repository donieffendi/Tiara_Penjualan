@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="{{ url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
	<link rel="stylesheet" href="{{ url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
@endsection


{{-- agar bisa langsung di excelkan di index --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
{{-- batas --}}


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
		@if (session('success'))
			<script>
				Swal.fire({
					title: 'Success!',
					text: '{{ session('success') }}',
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
														<input class="form-check-input column-checkbox" type="checkbox" value="4" id="columnKode" checked>
														<label class="form-check-label" for="columnKode">Kode Supplier</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="5" id="columnSupplier" checked>
														<label class="form-check-label" for="columnSupplier">Nama Supplier</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="6" id="columnNotes" checked>
														<label class="form-check-label" for="columnNotes">Notes</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="7" id="columnCaraBayar" checked>
														<label class="form-check-label" for="columnCaraBayar">Cara Bayar</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="8" id="columnPosted" checked>
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
											<th width="150px" style="text-align:center">Tgl Mulai</th>
											<th width="150px" style="text-align:center">Tgl Selesai</th>
											<th width="200px" style="text-align:center">Supplier</th>
											<th width="200px" style="text-align:center">Nama</th>
											<th width="200px" style="text-align:center">Catatan</th>
											<th width="100px" style="text-align:center">Posted</th>
											<th width="100px" style="text-align:center">Beli#</th>
											<th width="100px" style="text-align:center">OK</th>
											<th width="120px" style="text-align:center">Cara Bayar</th>
											<th width="120px" style="text-align:center">Nama Penerima</th>
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

	
{{-- agar bisa di excelkan langsung di index --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
{{-- batas --}}


<script>
	$(document).ready(function() {
		var dataTable = $('.datatable').DataTable({
			processing: true,
			serverSide: true,
			scrollY: '400px',
			scrollX: true,
			order: [
				[2, "desc"]
			], // Order by No Bukti descending
			ajax: {
				url: "{{ route('phturanharga.get-data') }}"
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
					data: 'TGL_MULAI',
					name: 'TGL_MULAI'
				},
				{
					data: 'TGL_SLS',
					name: 'TGL_SLS'
				},
				{
					data: 'KODES',
					name: 'KODES'
				},
				{
					data: 'NAMAS',
					name: 'NAMAS'
				},
				{
					data: 'notes',
					name: 'notes'
				},
				{
					data: 'posted',
					name: 'posted',
					render: function(data, type, row, meta) {
						return data == 1 ?
							'<span class="badge badge-pill badge-success">Posted</span>' :
							'<span class="badge badge-pill badge-warning">Open</span>';
					}
				},
				{
					data: 'NO_BELI',
					name: 'NO_BELI'
				},
				{
					data: 'cek',
					name: 'cek',
					render: function(data, type, row, meta) {
						return data == 1 ?
							'<span class="badge badge-pill badge-success">Posted</span>' :
							'<span class="badge badge-pill badge-warning">Open</span>';
					}
				},
				{
					data: 'CARA_BAYAR',
					name: 'CARA_BAYAR'
				},
				{
					data: 'NA_KWI',
					name: 'NA_KWI'
				}
			],
			columnDefs: [{
				className: "dt-center",
				targets: [0, 1, 2, 4, 7, 8]
			}],
			dom: "<'row'<'col-md-6'><'col-md-6'>>" +
				"<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
				"<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",

			buttons: [
				{
					extend: 'excelHtml5',
					title: 'Data Retur',
					text: 'Excel',
					className: 'btn btn-outline-success btn-sm',
					exportOptions: {
						columns: ':visible:not(:first-child)' // Jangan export kolom checkbox/action
					}
				},
				// {
				//     extend: 'print',
				//     title: 'Data Beliz',
				//     text: 'Cetak',
				//     className: 'btn btn-outline-primary btn-sm'
				// }
			],

			
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
			'<a class="btn btn-success" href="{{ route('phturanharga.edit') }}?status=simpan" title="Tambah Data"><i class="fas fa-plus"></i> New</a>'
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

		// Double click to edit (matching Delphi cxGrid1DBTableView1DblClick)
		$('#datatable tbody').on('dblclick', 'tr', function() {
			var data = dataTable.row(this).data();
			if (data.posted == 0) {
				editData(data.NO_BUKTI);
			} else {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Data Sudah Terposting !!'
				});
			}
		});

		// Delete key handler (matching Delphi cxGrid1DBTableView1KeyUp)
		$(document).on('keyup', function(e) {
			if (e.keyCode === 46) { // Delete key
				var selectedRow = dataTable.$('tr.selected');
				if (selectedRow.length > 0) {
					var data = dataTable.row(selectedRow[0]).data();
					deleteData(data.NO_BUKTI);
				}
			}
		});
	});

	// Edit function called from action button
	function editData(no_bukti) {
		window.location.href = '{{ route('phturanharga.edit') }}?no_bukti=' + no_bukti + '&status=edit';
	}

	// Delete function (matching Delphi cxGrid1DBTableView1KeyUp)
	function deleteData(no_bukti) {
		Swal.fire({
			title: 'Konfirmasi Hapus',
			text: 'Apakah Turun Harga ' + no_bukti + ' ingin dihapus?',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#3085d6',
			confirmButtonText: 'Ya, Hapus!',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				// Show loading
				Swal.fire({
					title: 'Menghapus...',
					text: 'Mohon tunggu',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading()
					}
				});

				$.ajax({
					url: '{{ route('phturanharga.delete', '') }}/' + no_bukti,
					type: 'DELETE',
					data: {
						_token: '{{ csrf_token() }}'
					},
					success: function(response) {
						Swal.close();
						if (response.success) {
							Swal.fire({
								title: 'Berhasil!',
								text: 'Turun Harga ' + no_bukti + ' telah terhapus.',
								icon: 'success',
								confirmButtonText: 'OK'
							}).then(() => {
								$('.datatable').DataTable().ajax.reload();
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
					error: function(xhr) {
						Swal.close();
						var errorMessage = 'Gagal menghapus data';
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
		});
	}

	// Print function called from action button
	function printData(no_bukti) {
		$.ajax({
			url: "{{ route('phturanharga.print') }}",
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

	function generatePrintContent(data) {
		var content = `
			<!DOCTYPE html>
			<html>
			<head>
				<title>Laporan Turun Harga</title>
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
					<h2>LAPORAN TURUN HARGA</h2>
				</div>
				<div class="info">
					<table style="border: none;">
						<tr style="border: none;">
							<td style="border: none; width: 100px;"><strong>No Bukti</strong></td>
							<td style="border: none;">: ${data[0].NO_BUKTI}</td>
							<td style="border: none; width: 100px;"><strong>Periode</strong></td>
							<td style="border: none;">: ${data[0].TGL_MULAI} s/d ${data[0].TGL_SLS}</td>
						</tr>
						<tr style="border: none;">
							<td style="border: none;"><strong>Supplier</strong></td>
							<td style="border: none;">: ${data[0].NAMAS}</td>
							<td style="border: none;"><strong>Kode</strong></td>
							<td style="border: none;">: ${data[0].KODES}</td>
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
							<th>Kode Barang</th>
							<th>Nama Barang</th>
							<th>Kemasan</th>
							<th class="text-right">HJ</th>
							<th class="text-right">Turun Harga</th>
							<th class="text-right">Harga Baru</th>
							<th class="text-right">Partisipasi</th>
						</tr>
					</thead>
					<tbody>`;

		data.forEach((item, index) => {
			var harga_baru = parseFloat(item.HJ) - parseFloat(item.TH);
			content += `
				<tr>
					<td class="text-center">${index + 1}</td>
					<td>${item.KD_BRG}</td>
					<td>${item.NA_BRG}</td>
					<td>${item.ket_kem || ''}</td>
					<td class="text-right">${parseFloat(item.HJ).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
					<td class="text-right">${parseFloat(item.TH).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
					<td class="text-right">${harga_baru.toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
					<td class="text-right">${parseFloat(item.partsp).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
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
