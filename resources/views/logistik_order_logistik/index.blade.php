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

	/* menghilangkan padding */
	.content-header {
		padding: 0 !important;
	}

	.badge-warning {
		background-color: #0077ff !important;
		color: white !important;
	}
</style>

@section('content')
	<div class="content-wrapper">

		<!-- Status -->
		@if (session('status'))
			<div class="alert alert-success">
				{{ session('status') }}
			</div>

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
			<div class="alert alert-danger">
				{{ session('error') }}
			</div>

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

								<!-- filter kolom di index -->
								<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#columnModal">
									Filter Columns
								</button>

								<!-- Modal -->
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
														<input class="form-check-input column-checkbox" type="checkbox" value="4" id="columnKeterangan" checked>
														<label class="form-check-label" for="columnKeterangan">Notes</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="5" id="columnTotalQty" checked>
														<label class="form-check-label" for="columnTotalQty">Total Qty</label>
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
											<th width="100px" style="text-align:center">Action</th>
											<th width="150px" style="text-align:center">No Bukti</th>
											<th width="100px" style="text-align:center">Tanggal</th>
											<th width="200px" style="text-align:center">Notes</th>
											<th width="100px" style="text-align:center">Total Qty</th>
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

	<!-- Print Modal -->
	<div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="printModalLabel">Print Order Logistik</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
				</div>
				<div class="modal-body">
					<div id="printContent">
						<!-- Print content will be loaded here -->
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="printDocument()">Print</button>
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
					url: '{{ route('get-lorderlogistik') }}'
				},
				columns: [{
						data: 'DT_RowIndex',
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
						data: 'ket',
						name: 'ket',
						render: function(data, type, row, meta) {
							return data ? '<span class="badge badge-pill badge-warning">' + data + '</span>' : '';
						}
					},
					{
						data: 'total_qty',
						name: 'total_qty',
						className: 'text-right'
					}
				],
				columnDefs: [{
					"className": "dt-center",
					"targets": [0, 1, 2, 3, 5]
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

			$('#columnToggleForm .column-checkbox').each(function() {
				var column = dataTable.column($(this).val());
				column.visible($(this).is(':checked'));
			});

			// Add new button
			$("div.test_btn").html(
				'<a class="btn btn-success" href="{{ route('lorderlogistik.edit') }}?tipx=new" title="Tambah Data"><i class="fas fa-plus"></i> Tambah</a>'
			);
		});

		// Print function
		function printOrder(no_bukti) {
			$.ajax({
				url: '{{ route('lorderlogistik.print') }}',
				type: 'POST',
				data: {
					no_bukti: no_bukti,
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					if (response.data && response.data.length > 0) {
						let printContent = '<h4>Order Logistik - ' + no_bukti + '</h4>';
						printContent += '<table class="table table-bordered">';
						printContent += '<thead><tr>';
						printContent += '<th>Kode Barang</th><th>Nama Barang</th><th>Qty</th><th>Kemasan</th><th>Divisi</th>';
						printContent += '</tr></thead><tbody>';

						response.data.forEach(function(item) {
							printContent += '<tr>';
							printContent += '<td>' + item.Kdbar + '</td>';
							printContent += '<td>' + item.na_brg + '</td>';
							printContent += '<td>' + item.qty + '</td>';
							printContent += '<td>' + item.KET_KEM + '</td>';
							printContent += '<td>' + item.divisi + '</td>';
							printContent += '</tr>';
						});

						printContent += '</tbody></table>';
						$('#printContent').html(printContent);
						$('#printModal').modal('show');
					} else {
						Swal.fire({
							title: 'Warning!',
							text: 'No data found for printing',
							icon: 'warning',
							confirmButtonText: 'OK'
						});
					}
				},
				error: function(xhr, status, error) {
					Swal.fire({
						title: 'Error!',
						text: 'Failed to load print data',
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		function printDocument() {
			var printContents = document.getElementById('printContent').innerHTML;
			var originalContents = document.body.innerHTML;
			document.body.innerHTML = printContents;
			window.print();
			document.body.innerHTML = originalContents;
			$('#printModal').modal('hide');
			location.reload();
		}

		function deleteRow(url) {
			Swal.fire({
				title: 'Are you sure?',
				text: "This action cannot be undone!",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, delete it!',
				cancelButtonText: 'Cancel'
			}).then((result) => {
				if (result.isConfirmed) {
					window.location = url;
				}
			});
		}
	</script>
@endsection
