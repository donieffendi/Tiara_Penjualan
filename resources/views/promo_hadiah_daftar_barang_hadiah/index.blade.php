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
			text: "{{ session('success') }}",
			icon: 'success',
			confirmButtonText: 'OK'
		})
	</script>
	@endif

	@if (session('error'))
	<script>
		Swal.fire({
			title: 'Error!',
			text: "{{ session('error') }}",
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
						<div class="card-header">
							<h3 class="card-title">Daftar Barang Hadiah</h3>
						</div>
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
													<input class="form-check-input column-checkbox" type="checkbox" value="2" id="columnKode" checked>
													<label class="form-check-label" for="columnKode">Kode</label>
												</div>
												<div class="form-check">
													<input class="form-check-input column-checkbox" type="checkbox" value="3" id="columnNama" checked>
													<label class="form-check-label" for="columnNama">Nama</label>
												</div>
												<div class="form-check">
													<input class="form-check-input column-checkbox" type="checkbox" value="4" id="columnStockGd" checked>
													<label class="form-check-label" for="columnStockGd">Stock Gd</label>
												</div>
												<div class="form-check">
													<input class="form-check-input column-checkbox" type="checkbox" value="5" id="columnStockTk" checked>
													<label class="form-check-label" for="columnStockTk">Stock Tk</label>
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
										<th width="150px" style="text-align:center">Kode</th>
										<th width="300px" style="text-align:center">Nama</th>
										<th width="120px" style="text-align:center">Stock Gd</th>
										<th width="120px" style="text-align:center">Stock Tk</th>
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
			scrollX: true,
			order: [
				[2, "asc"]
			],
			ajax: {
				url: "{{ route('phdaftarbaranghadiah.get-data') }}"
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
					data: 'kd_brgh',
					name: 'kd_brgh'
				},
				{
					data: 'na_brgh',
					name: 'na_brgh'
				},
				{
					data: 'gak',
					name: 'gak'
				},
				{
					data: 'ak00',
					name: 'ak00'
				}
			],
			columnDefs: [{
				className: "dt-center",
				targets: [0, 1, 2, 4, 5]
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

		// Add new button (matching Delphi NewClick)
		$("div.test_btn").html(
			`<a class="btn btn-success" href="{{ route('phdaftarbaranghadiah.edit') }}?status=simpan" title="Tambah Data"><i class="fas fa-plus"></i> New</a>`
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
			editData(data.kd_brgh);
		});

		// Handle Delete key press (matching Delphi cxGrid1DBTableView1KeyUp - Key=46)
		$(document).on('keydown', function(e) {
			if (e.keyCode === 46) { // Delete key
				var selectedRow = dataTable.$('tr.selected');
				if (selectedRow.length > 0) {
					var data = dataTable.row(selectedRow).data();
					deleteData(data.kd_brgh, data.na_brgh);
				}
			}
		});
	});

	// Edit function (matching Delphi cxGrid1DBTableView1DblClick)
	function editData(kd_brgh) {
		window.location.href = '{{ route("phdaftarbaranghadiah.edit") }}?kd_brgh=' + kd_brgh + '&status=edit';
	}

	// Delete function (matching Delphi cxGrid1DBTableView1KeyUp - Key=46)
	function deleteData(kd_brgh, na_brgh) {
		Swal.fire({
			title: 'Konfirmasi Hapus',
			text: 'Apakah kode ini ' + kd_brgh + ' akan di hapus?',
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
					url: "{{ route('phdaftarbaranghadiah.delete') }}",
					type: 'DELETE',
					data: {
						kd_brgh: kd_brgh,
						_token: '{{ csrf_token() }}'
					},
					success: function(response) {
						Swal.close();
						if (response.success) {
							Swal.fire({
								icon: 'success',
								title: 'Success!',
								text: response.message,
								confirmButtonText: 'OK'
							}).then(() => {
								$('.datatable').DataTable().ajax.reload();
							});
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Error!',
								text: response.message,
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
							icon: 'error',
							title: 'Error!',
							text: errorMessage,
							confirmButtonText: 'OK'
						});
					}
				});
			}
		});
	}

	// Print function (matching Delphi p1Click)
	function printData() {
		$.ajax({
			url: "{{ route('phdaftarbaranghadiah.print') }}",
			type: 'POST',
			data: {
				_token: '{{ csrf_token() }}'
			},
			success: function(response) {
				if (response.success && response.data && response.data.length > 0) {
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
					<title>Laporan Daftar Barang Hadiah</title>
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
						<h2>LAPORAN DAFTAR BARANG HADIAH</h2>
					</div>
					<table>
						<thead>
							<tr>
								<th class="text-center" width="5%">No</th>
								<th width="15%">Kode Barang</th>
								<th width="40%">Nama Barang</th>
								<th class="text-right" width="15%">Stock Gd</th>
								<th class="text-right" width="15%">Stock Tk</th>
							</tr>
						</thead>
						<tbody>`;

		data.forEach((item, index) => {
			content += `
					<tr>
						<td class="text-center">${index + 1}</td>
						<td>${item.kd_brgh}</td>
						<td>${item.na_brgh}</td>
						<td class="text-right">${parseFloat(item.aw00 || 0).toLocaleString('id-ID')}</td>
						<td class="text-right">${parseFloat(item.ak00 || 0).toLocaleString('id-ID')}</td>
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