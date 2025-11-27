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

	.selected {
		background-color: #007bff !important;
		color: white !important;
	}
</style>

@section('content')
<div class="content-wrapper">

	@if (session('success'))
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			Swal.fire({
				title: 'Success!',
				text: "{{ session('success') }}",
				icon: 'success',
				confirmButtonText: 'OK'
			});
		});
	</script>
	@endif

	@if (session('error'))
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			Swal.fire({
				title: 'Error!',
				text: "{{ session('error') }}",
				icon: 'error',
				confirmButtonText: 'OK'
			});
		});
	</script>
	@endif

	<div class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-header">
							<h3 class="card-title">Entry Penyewa Tempat</h3>
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
													<input class="form-check-input column-checkbox" type="checkbox" value="2" id="columnAreal" checked>
													<label class="form-check-label" for="columnAreal">Areal</label>
												</div>
												<div class="form-check">
													<input class="form-check-input column-checkbox" type="checkbox" value="3" id="columnNoPenyewa" checked>
													<label class="form-check-label" for="columnNoPenyewa">No Penyewa</label>
												</div>
												<div class="form-check">
													<input class="form-check-input column-checkbox" type="checkbox" value="4" id="columnSarana" checked>
													<label class="form-check-label" for="columnSarana">Sarana</label>
												</div>
												<div class="form-check">
													<input class="form-check-input column-checkbox" type="checkbox" value="5" id="columnLokasi" checked>
													<label class="form-check-label" for="columnLokasi">Lokasi</label>
												</div>
												<div class="form-check">
													<input class="form-check-input column-checkbox" type="checkbox" value="6" id="columnNoSupl" checked>
													<label class="form-check-label" for="columnNoSupl">No Supl.</label>
												</div>
												<div class="form-check">
													<input class="form-check-input column-checkbox" type="checkbox" value="7" id="columnTglMulai" checked>
													<label class="form-check-label" for="columnTglMulai">Tgl Mulai</label>
												</div>
												<div class="form-check">
													<input class="form-check-input column-checkbox" type="checkbox" value="8" id="columnTglSelesai" checked>
													<label class="form-check-label" for="columnTglSelesai">Tgl Selesai</label>
												</div>
												<div class="form-check">
													<input class="form-check-input column-checkbox" type="checkbox" value="9" id="columnCatatan" checked>
													<label class="form-check-label" for="columnCatatan">Catatan</label>
												</div>
												<div class="form-check">
													<input class="form-check-input column-checkbox" type="checkbox" value="10" id="columnUser" checked>
													<label class="form-check-label" for="columnUser">User</label>
												</div>
												<div class="form-check">
													<input class="form-check-input column-checkbox" type="checkbox" value="11" id="columnNoRinci" checked>
													<label class="form-check-label" for="columnNoRinci">No. Rinci</label>
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
										<th width="100px" style="text-align:center">Areal</th>
										<th width="150px" style="text-align:center">No Penyewa</th>
										<th width="120px" style="text-align:center">Sarana</th>
										<th width="150px" style="text-align:center">Lokasi</th>
										<th width="100px" style="text-align:center">No Supl.</th>
										<th width="100px" style="text-align:center">Tgl Mulai</th>
										<th width="100px" style="text-align:center">Tgl Selesai</th>
										<th width="200px" style="text-align:center">Catatan</th>
										<th width="100px" style="text-align:center">User</th>
										<th width="100px" style="text-align:center">No. Rinci</th>
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
				[3, "desc"]
			],
			ajax: {
				url: "{{ route('phentrypenyewatempat') }}"
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
					data: 'AREAL',
					name: 'AREAL'
				},
				{
					data: 'NO_BUKTI',
					name: 'NO_BUKTI'
				},
				{
					data: 'KD_SARANA',
					name: 'KD_SARANA'
				},
				{
					data: 'LOKASI',
					name: 'LOKASI'
				},
				{
					data: 'KODES',
					name: 'KODES'
				},
				{
					data: 'TG_MULAI',
					name: 'TG_MULAI'
				},
				{
					data: 'TG_SELESAI',
					name: 'TG_SELESAI'
				},
				{
					data: 'CATATAN',
					name: 'CATATAN'
				},
				{
					data: 'USRNM',
					name: 'USRNM'
				},
				{
					data: 'NO_BUKTI',
					name: 'NO_BUKTI'
				}
			],
			columnDefs: [{
				className: "dt-center",
				targets: [0, 1, 2, 3, 4, 6, 7, 8, 10, 11]
			}, {
				className: "dt-left",
				targets: [5, 9]
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
			'<button class="btn btn-success" onclick="createNew()" title="Tambah Data"><i class="fas fa-plus"></i> New</button>' +
			'<button class="btn btn-info ml-2" onclick="refreshData()" title="Refresh Data"><i class="fas fa-sync"></i> Refresh</button>'
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

		$(document).on('keydown', function(e) {
			if (e.keyCode === 46) {
				var selected = dataTable.$('tr.selected');
				if (selected.length > 0) {
					var data = dataTable.row(selected[0]).data();
					if (data) {
						deleteData(data.NO_BUKTI, data.AREAL);
					}
				}
			}
		});
	});

	function createNew() {
		window.location.href = "{{ route('phentrypenyewatempat') }}?status=simpan";
	}

	function editData(no_bukti) {
		window.location.href = "{{ route('phentrypenyewatempat') }}?no_bukti=" + no_bukti + "&status=edit";
	}

	function refreshData() {
		$('.datatable').DataTable().ajax.reload();
	}

	function printData(no_bukti) {
		$.ajax({
			url: "{{ route('phentrypenyewatempat.print-kontrak') }}",
			type: 'GET',
			data: {
				no_bukti: no_bukti
			},
			success: function(response) {
				window.open("{{ url('/phentrypenyewatempat/print-kontrak') }}?no_bukti=" + no_bukti, '_blank');
				location.href = "{{ url('/phentrypenyewatempat/print-kontrak') }}?no_bukti=" + no_bukti + '&tipe=persetujuan';
			},
			error: function() {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Gagal memuat data untuk print'
				});
			}
		});
	}

	function exportData(no_bukti) {
		Swal.fire({
			title: 'Export Data',
			text: 'Apakah Anda ingin export data ini?',
			icon: 'question',
			showCancelButton: true,
			confirmButtonText: 'Ya, Export',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: "{{ route('phentrypenyewatempat.export-data') }}",
					type: 'POST',
					data: {
						no_bukti: no_bukti,
						_token: '{{ csrf_token() }}'
					},
					success: function(response) {
						if (response.success) {
							Swal.fire({
								icon: 'success',
								title: 'Success',
								text: response.message
							});
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: response.message
							});
						}
					},
					error: function(xhr) {
						var errorMessage = 'Gagal export data';
						if (xhr.responseJSON && xhr.responseJSON.message) {
							errorMessage = xhr.responseJSON.message;
						}
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: errorMessage
						});
					}
				});
			}
		});
	}

	function deleteData(no_bukti, areal) {
		Swal.fire({
			title: 'Konfirmasi',
			text: 'Apakah No. Penyewa ' + no_bukti + ' ingin dihapus?',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#3085d6',
			confirmButtonText: 'Ya, Hapus',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: "{{ route('phentrypenyewatempat.check-customer') }}",
					type: 'POST',
					data: {
						no_bukti: no_bukti,
						areal: areal,
						_token: '{{ csrf_token() }}'
					},
					success: function(response) {
						if (response.success) {
							Swal.fire({
								icon: 'success',
								title: 'Success',
								text: response.message
							}).then(() => {
								refreshData();
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
</script>
@endsection