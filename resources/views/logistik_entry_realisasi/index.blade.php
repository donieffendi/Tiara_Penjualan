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
														<input class="form-check-input column-checkbox" type="checkbox" value="1" id="columnKode" checked>
														<label class="form-check-label" for="columnKode">Kode</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="2" id="columnNamaBarang" checked>
														<label class="form-check-label" for="columnNamaBarang">Nama Barang</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="3" id="columnStn" checked>
														<label class="form-check-label" for="columnStn">Stn</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="4" id="columnQty" checked>
														<label class="form-check-label" for="columnQty">Qty</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="5" id="columnKet" checked>
														<label class="form-check-label" for="columnKet">Ket</label>
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
											<th width="120px" style="text-align:center">Kode</th>
											<th width="250px" style="text-align:center">Nama Barang</th>
											<th width="80px" style="text-align:center">Stn</th>
											<th width="100px" style="text-align:center">Qty</th>
											<th width="200px" style="text-align:center">Ket</th>
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
					[1, "asc"]
				],
				ajax: {
					url: '{{ route('get-lentryrealisasi') }}'
				},
				columns: [{
						data: 'DT_RowIndex',
						orderable: false,
						searchable: false
					},
					{
						data: 'kd_brg',
						name: 'kd_brg'
					},
					{
						data: 'na_brg',
						name: 'na_brg'
					},
					{
						data: 'ket_uk',
						name: 'ket_uk'
					},
					{
						data: 'qty',
						name: 'qty',
						className: 'text-right'
					},
					{
						data: 'ket_kem',
						name: 'ket_kem'
					}
				],
				columnDefs: [{
					"className": "dt-center",
					"targets": [0, 1, 3, 4]
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

			$('#columnToggleForm .column-checkbox').each(function() {
				var column = dataTable.column($(this).val());
				column.visible($(this).is(':checked'));
			});

			$("div.test_btn").html(
				'<a class="btn btn-success" href="{{ route('lentryrealisasi.edit') }}?status=simpan" title="Tambah Data"><i class="fas fa-plus"></i> New</a>'
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
				if (data && data.no_bukti) {
					editData(data.no_bukti);
				}
			});

			$(document).on('keyup', function(e) {
				if (e.keyCode === 46) {
					var selectedRow = dataTable.row('.selected');
					if (selectedRow.length > 0) {
						var data = selectedRow.data();
						if (data && data.no_bukti) {
							deleteData(data.no_bukti);
						}
					}
				}
			});
		});

		function editData(no_bukti) {
			window.location.href = '{{ route('lentryrealisasi.edit') }}?no_bukti=' + no_bukti + '&status=edit';
		}

		function deleteData(no_bukti) {
			Swal.fire({
				title: 'Konfirmasi',
				text: 'Apakah data dengan No Bukti ' + no_bukti + ' akan dihapus?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Ya, Hapus!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					window.location.href = "{{ route('lentryrealisasi.delete', '') }}/" + no_bukti;
				}
			});
		}
	</script>
@endsection
