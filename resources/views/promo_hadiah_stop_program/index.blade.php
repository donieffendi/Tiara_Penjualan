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

	.badge-secondary {
		background-color: #6c757d !important;
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
						text: '{{ session('success') }}',
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
						text: '{{ session('error') }}',
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
								<h3 class="card-title">Stop Program Hadiah</h3>
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
														<input class="form-check-input column-checkbox" type="checkbox" value="3" id="columnKode" checked>
														<label class="form-check-label" for="columnKode">Kode</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="4" id="columnNamaHadiah" checked>
														<label class="form-check-label" for="columnNamaHadiah">Nama Hadiah</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="5" id="columnCatatan" checked>
														<label class="form-check-label" for="columnCatatan">Catatan</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="6" id="columnSupplier" checked>
														<label class="form-check-label" for="columnSupplier">Supplier</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="7" id="columnNama" checked>
														<label class="form-check-label" for="columnNama">Nama</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="8" id="columnMulai" checked>
														<label class="form-check-label" for="columnMulai">Mulai</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="9" id="columnBerakhir" checked>
														<label class="form-check-label" for="columnBerakhir">Berakhir</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="10" id="columnType" checked>
														<label class="form-check-label" for="columnType">Type</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="11" id="columnTGZ" checked>
														<label class="form-check-label" for="columnTGZ">TGZ</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="12" id="columnTMM" checked>
														<label class="form-check-label" for="columnTMM">TMM</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="13" id="columnSOP" checked>
														<label class="form-check-label" for="columnSOP">SOP</label>
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

								<div class="row mb-3">
									<div class="col-md-4">
										<label for="search_kd_brg">Cari Berdasarkan Kode Barang:</label>
										<div class="input-group">
											<input type="text" class="form-control" id="search_kd_brg" placeholder="Masukkan Kode Barang">
											<button type="button" class="btn btn-primary" onclick="searchByKdBrg()">
												<i class="fas fa-search"></i> Cari
											</button>
										</div>
									</div>
								</div>

								<table class="table-striped table-border table-hover nowrap datatable table table-fixed" id="datatable">
									<thead class="table-dark">
										<tr>
											<th width="50px" style="text-align:center">No</th>
											<th width="150px" style="text-align:center">Action</th>
											<th width="120px" style="text-align:center">No Bukti</th>
											<th width="100px" style="text-align:center">Kode</th>
											<th width="200px" style="text-align:center">Nama Hadiah</th>
											<th width="150px" style="text-align:center">Catatan</th>
											<th width="100px" style="text-align:center">Supplier</th>
											<th width="200px" style="text-align:center">Nama</th>
											<th width="100px" style="text-align:center">Mulai</th>
											<th width="100px" style="text-align:center">Berakhir</th>
											<th width="80px" style="text-align:center">Type</th>
											<th width="80px" style="text-align:center">TGZ</th>
											<th width="80px" style="text-align:center">TMM</th>
											<th width="80px" style="text-align:center">SOP</th>
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

	<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="detailModalLabel">Detail Program Hadiah</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
				</div>
				<div class="modal-body">
					<div id="detailContent"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
		var dataTable;

		$(document).ready(function() {
			dataTable = $('.datatable').DataTable({
				processing: true,
				serverSide: true,
				scrollY: '400px',
				scrollX: true,
				order: [
					[2, "desc"]
				],
				ajax: {
					url: '{{ route('phstopprogramhadiah.get-data') }}'
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
						data: 'kd_prm',
						name: 'kd_prm'
					},
					{
						data: 'ket',
						name: 'ket'
					},
					{
						data: 'ket',
						name: 'ket'
					},
					{
						data: 'kodes',
						name: 'kodes'
					},
					{
						data: 'namas',
						name: 'namas'
					},
					{
						data: 'tg_mulai',
						name: 'tg_mulai'
					},
					{
						data: 'tg_akhir',
						name: 'tg_akhir'
					},
					{
						data: 'type',
						name: 'type'
					},
					{
						data: 'TGZ',
						name: 'TGZ'
					},
					{
						data: 'TMM',
						name: 'TMM'
					},
					{
						data: 'SOP',
						name: 'SOP'
					}
				],
				columnDefs: [{
					className: "dt-center",
					targets: [0, 1, 2, 3, 6, 8, 9, 10, 11, 12, 13]
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
					viewDetail(data.kd_prm);
				}
			});

			$('#search_kd_brg').on('keypress', function(e) {
				if (e.which === 13) {
					searchByKdBrg();
				}
			});
		});

		function searchByKdBrg() {
			var kd_brgh = $('#search_kd_brg').val().trim();

			if (!kd_brgh) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Kode barang tidak boleh kosong!'
				});
				return;
			}

			Swal.fire({
				title: 'Loading...',
				text: 'Mencari data...',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading()
				}
			});

			$.ajax({
				url: '{{ route('phstopprogramhadiah.browse') }}',
				type: 'GET',
				data: {
					kd_brgh: kd_brgh
				},
				success: function(response) {
					Swal.close();
					if (response.success && response.data && response.data.length > 0) {
						showBrowseResults(response.data);
					} else {
						Swal.fire({
							icon: 'info',
							title: 'Info',
							text: 'Data tidak ditemukan'
						});
					}
				},
				error: function() {
					Swal.close();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Gagal mencari data'
					});
				}
			});
		}

		function showBrowseResults(data) {
			var content = '<div class="table-responsive"><table class="table table-bordered table-sm">';
			content +=
				'<thead><tr><th>No Bukti</th><th>Kode</th><th>Nama Hadiah</th><th>Supplier</th><th>Kode Barang</th><th>Nama Barang</th><th>Action</th></tr></thead><tbody>';

			data.forEach(function(item) {
				content += '<tr>';
				content += '<td>' + item.no_bukti + '</td>';
				content += '<td>' + item.kd_prm + '</td>';
				content += '<td>' + (item.ket || '') + '</td>';
				content += '<td>' + item.kodes + '</td>';
				content += '<td>' + item.KD_BRGH + '</td>';
				content += '<td>' + item.NA_BRGH + '</td>';
				content += '<td><button class="btn btn-sm btn-primary" onclick="selectFromBrowse(\'' + item.kd_prm + '\')">Select</button></td>';
				content += '</tr>';
			});

			content += '</tbody></table></div>';

			$('#detailContent').html(content);
			$('#detailModal').modal('show');
		}

		function selectFromBrowse(kd_prm) {
			$('#detailModal').modal('hide');
			viewDetail(kd_prm);
		}

		function stopProgram(kd_prm) {
			Swal.fire({
				title: 'Konfirmasi',
				text: 'Apakah Anda yakin ingin menghentikan program hadiah ini?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Ya, Stop!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					Swal.fire({
						title: 'Processing...',
						text: 'Menghentikan program hadiah...',
						allowOutsideClick: false,
						didOpen: () => {
							Swal.showLoading()
						}
					});

					$.ajax({
						url: '{{ route('phstopprogramhadiah.store') }}',
						type: 'POST',
						data: {
							kd_prm: kd_prm,
							_token: '{{ csrf_token() }}'
						},
						success: function(response) {
							Swal.close();
							if (response.success) {
								Swal.fire({
									title: 'Success!',
									text: response.message,
									icon: 'success',
									confirmButtonText: 'OK'
								}).then(() => {
									dataTable.ajax.reload();
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
							var errorMessage = 'Terjadi kesalahan';
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

		function viewDetail(kd_prm) {
			Swal.fire({
				title: 'Loading...',
				text: 'Mengambil detail...',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading()
				}
			});

			$.ajax({
				url: '{{ route('phstopprogramhadiah.detail') }}',
				type: 'GET',
				data: {
					kd_prm: kd_prm
				},
				success: function(response) {
					Swal.close();
					if (response.success) {
						showDetailModal(response.header, response.detail);
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.message
						});
					}
				},
				error: function() {
					Swal.close();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Gagal mengambil detail'
					});
				}
			});
		}

		function showDetailModal(header, detail) {
			var content = '<div class="row mb-3">';
			content += '<div class="col-md-12"><h6 class="border-bottom pb-2">Header Information</h6></div>';
			content += '<div class="col-md-6"><strong>No Bukti:</strong> ' + header.no_bukti + '</div>';
			content += '<div class="col-md-6"><strong>Kode Program:</strong> ' + header.kd_prm + '</div>';
			content += '<div class="col-md-6"><strong>Supplier:</strong> ' + header.kodes + '</div>';
			content += '<div class="col-md-6"><strong>Nama Supplier:</strong> ' + header.namas + '</div>';
			content += '<div class="col-md-6"><strong>Mulai:</strong> ' + formatDate(header.tg_mulai) + '</div>';
			content += '<div class="col-md-6"><strong>Berakhir:</strong> ' + formatDate(header.tg_akhir) + '</div>';
			content += '<div class="col-md-12"><strong>Catatan:</strong> ' + (header.ket || '-') + '</div>';
			content += '</div>';

			if (detail && detail.length > 0) {
				content += '<div class="row mt-3">';
				content += '<div class="col-md-12"><h6 class="border-bottom pb-2">Detail Barang</h6></div>';
				content += '<div class="col-md-12"><div class="table-responsive"><table class="table table-bordered table-sm">';
				content += '<thead><tr><th>No</th><th>Kode Barang</th><th>Nama Barang</th></tr></thead><tbody>';

				detail.forEach(function(item, index) {
					content += '<tr>';
					content += '<td>' + (index + 1) + '</td>';
					content += '<td>' + (item.kd_brgh || '') + '</td>';
					content += '<td>' + (item.na_brgh || '') + '</td>';
					content += '</tr>';
				});

				content += '</tbody></table></div></div></div>';
			}

			$('#detailContent').html(content);
			$('#detailModal').modal('show');
		}

		function formatDate(dateString) {
			if (!dateString) return '-';
			var date = new Date(dateString);
			var day = String(date.getDate()).padStart(2, '0');
			var month = String(date.getMonth() + 1).padStart(2, '0');
			var year = date.getFullYear();
			return day + '/' + month + '/' + year;
		}
	</script>
@endsection
