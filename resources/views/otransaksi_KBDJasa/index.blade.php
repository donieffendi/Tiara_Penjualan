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
		/* Warna default badge-warning (kuning) */
		color: white !important;
		/* Warna teks putih */
	}

	.badge-success {
		background-color: #28a745 !important;
		color: white !important;
	}

	.badge-danger {
		background-color: #dc3545 !important;
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

			<!-- tambahan notifikasinya untuk delete di index -->
			<script>
				Swal.fire({
					title: 'Deleted!',
					text: 'Data has been deleted. {{ session('status') }}',
					icon: 'success',
					confirmButtonText: 'OK'
				})
			</script>
			<!-- tutupannya -->
		@endif

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">

								<!-- filter kolom di index -->

								<!-- Button to open modal -->
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
												<!-- Column visibility checkboxes -->
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
														<input class="form-check-input column-checkbox" type="checkbox" value="2" id="columnBukti" checked>
														<label class="form-check-label" for="columnBukti">No Bukti</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="3" id="columnTgl" checked>
														<label class="form-check-label" for="columnTgl">Tanggal</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="4" id="columnDept" checked>
														<label class="form-check-label" for="columnDept">Departemen</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="5" id="columnNamaBarang" checked>
														<label class="form-check-label" for="columnNamaBarang">Nama Barang</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="6" id="columnUser" checked>
														<label class="form-check-label" for="columnUser">User</label>
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

								<!-- batas filter -->

								<table class="table-striped table-border table-hover nowrap datatable table table-fixed" id="datatable">
									<thead class="table-dark">
										<tr>
											<th width="50px" style="text-align:center">No</th>
											<th width="60px" style="text-align:center">-</th>
											<th width="120px" style="text-align:center">No Bukti</th>
											<th width="80px" style="text-align:center">Tanggal</th>
											<th width="120px" style="text-align:center">Dept</th>
											<th width="200px" style="text-align:center">Nama Barang</th>
											<th width="120px" style="text-align:center">User</th>
										</tr>
									</thead>

									<tbody>

									</tbody>
								</table>
							</div>
						</div>
						<!-- /.card -->
					</div>
				</div>
				<!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>
		<!-- /.content -->
	</div>
	<!-- /.content-wrapper -->
@endsection

@section('javascripts')
	<script src="{{ url('AdminLTE/plugins/datatables/jquery.dataTables.js') }}"></script>
	<script src="{{ url('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>
	<script src="{{ url('http://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js') }}"></script>

	<!-- filter kolom di index -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
	<!-- batas filter  -->

	<script>
		window.addEventListener('message', (event) => {
			if (event.origin !== window.location.origin) {
				console.warn('Origin mismatch!');
				return;
			}

			const currentData = event.data;
			console.log(currentData); // Use currentData as needed
		});
		// batas filter

		$(document).ready(function() {
			var dataTable = $('.datatable').DataTable({
				processing: true,
				serverSide: true,
				'scrollY': '400px',
				"order": [
					[2, "desc"]
				],
				ajax: {
					url: '{{ route('get-TKBDJasa') }}'
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
						data: 'dept',
						name: 'dept'
					},
					{
						data: 'na_brg',
						name: 'na_brg'
					},
					{
						data: 'usrnm',
						name: 'usrnm'
					}
				],

				columnDefs: [{
					"className": "dt-center",
					"targets": [0, 1, 3, 6]
				}],

				dom: "<'row'<'col-md-6'><'col-md-6'>>" +
					"<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
					"<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",

				stateSave: false,
			});

			// filter kolom di index

			// Handle column visibility toggle
			$('#applyColumnToggle').on('click', function() {
				$('#columnToggleForm .column-checkbox').each(function() {
					var column = dataTable.column($(this).val());
					column.visible($(this).is(':checked'));
				});
				$('#columnModal').modal('hide'); // Close the modal
			});

			$('#columnToggleForm .column-checkbox').each(function() {
				var column = dataTable.column($(this).val());
				column.visible($(this).is(':checked'));
			});

			// batas filter

			$("div.test_btn").html(
				'<a class="btn btn-success" href="{{ url('TKBDJasa/edit?idx=0&tipx=new') }}" title="Tambah Data"><i class="fas fa-plus"></i> Tambah</a>'
			);
		});

		function deleteRow(link) {
			console.log('Masuk');
			Swal.fire({
				title: 'Are you sure?',
				text: "Are you sure?",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, delete it!',
				cancelButtonText: 'Cancel'
			}).then((result) => {
				if (result.isConfirmed) {
					window.location = link;
				}
			});
		}
	</script>
@endsection
