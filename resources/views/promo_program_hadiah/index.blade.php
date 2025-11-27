@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="{{ url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
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

	.badge-primary {
		background-color: #007bff !important;
		color: white !important;
	}

	.badge-info {
		background-color: #17a2b8 !important;
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
							<div class="card-header">
								<h3 class="card-title">Program Promosi Hadiah</h3>
							</div>
							<div class="card-body">

								<div class="row mb-3">
									<div class="col-md-4">
										<div class="input-group">
											<input type="text" class="form-control" id="searchKodeBarang" placeholder="Cari Kode Barang...">
											<div class="input-group-append">
												<button class="btn btn-primary" type="button" onclick="searchByKodeBarang()">
													<i class="fas fa-search"></i> Cari
												</button>
											</div>
										</div>
									</div>
								</div>

								<table class="table-striped table-bordered table-hover nowrap datatable table" id="datatable">
									<thead class="table-dark">
										<tr>
											<th width="50px" style="text-align:center">No</th>
											<th width="100px" style="text-align:center">Action</th>
											<th width="150px" style="text-align:center">No Bukti</th>
											<th width="120px" style="text-align:center">Kode Promo</th>
											<th width="200px" style="text-align:center">Catatan</th>
											<th width="120px" style="text-align:center">Supplier</th>
											<th width="150px" style="text-align:center">Nama</th>
											<th width="100px" style="text-align:center">Jumlah Beli</th>
											<th width="150px" style="text-align:center">Nama Hadiah</th>
											<th width="100px" style="text-align:center">Stok</th>
											<th width="100px" style="text-align:center">Mulai</th>
											<th width="100px" style="text-align:center">Berakhir</th>
											<th width="100px" style="text-align:center">Type</th>
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
					[2, "desc"]
				],
				ajax: {
					url: '{{ route('phprogrampromosihadiah.get-data') }}'
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
						data: 'kodes',
						name: 'kodes'
					},
					{
						data: 'namas',
						name: 'namas'
					},
					{
						data: 'qty_beli',
						name: 'qty_beli',
						className: 'text-right'
					},
					{
						data: 'nama_hadiah',
						name: 'nama_hadiah',
						defaultContent: '-'
					},
					{
						data: 'stok',
						name: 'stok',
						className: 'text-right',
						defaultContent: '0'
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
					}
				],
				columnDefs: [{
					className: "dt-center",
					targets: [0, 1, 2, 3, 5, 7, 9, 10, 11, 12]
				}],
				dom: "<'row'<'col-md-6'><'col-md-6'>>" +
					"<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
					"<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
				stateSave: false,
			});

			$("div.test_btn").html(
				'<a class="btn btn-success" href="{{ route('phprogrampromosihadiah.edit') }}?status=simpan" title="Tambah Data"><i class="fas fa-plus"></i> New</a>'
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
				editData(data.kd_prm);
			});

			$(document).on('keydown', function(e) {
				if (e.key === 'Delete') {
					var selectedRow = dataTable.row('.selected').data();
					if (selectedRow) {
						deleteData(selectedRow.no_bukti);
					}
				}
			});
		});

		function searchByKodeBarang() {
			var kodeBarang = $('#searchKodeBarang').val().trim();

			if (!kodeBarang) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Masukkan kode barang terlebih dahulu'
				});
				return;
			}

			$.ajax({
				url: '{{ route('phprogrampromosihadiah.search') }}',
				type: 'GET',
				data: {
					type: 'kode',
					search: kodeBarang
				},
				success: function(response) {
					if (response.success && response.data.length > 0) {
						displaySearchResults(response.data);
					} else {
						Swal.fire({
							icon: 'info',
							title: 'Info',
							text: 'Data tidak ditemukan'
						});
					}
				},
				error: function() {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Gagal mencari data'
					});
				}
			});
		}

		function displaySearchResults(data) {
			var html = '<div class="table-responsive"><table class="table table-bordered table-sm">';
			html += '<thead><tr><th>No Bukti</th><th>Kode Promo</th><th>Supplier</th><th>Mulai</th><th>Berakhir</th><th>Action</th></tr></thead><tbody>';

			data.forEach(function(item) {
				html += '<tr>';
				html += '<td>' + item.no_bukti + '</td>';
				html += '<td>' + item.kd_prm + '</td>';
				html += '<td>' + item.namas + '</td>';
				html += '<td>' + item.tg_mulai + '</td>';
				html += '<td>' + item.tg_akhir + '</td>';
				html += '<td><button class="btn btn-sm btn-primary" onclick="editData(\'' + item.kd_prm + '\')">Edit</button></td>';
				html += '</tr>';
			});

			html += '</tbody></table></div>';

			Swal.fire({
				title: 'Hasil Pencarian',
				html: html,
				width: '800px',
				showCloseButton: true,
				showConfirmButton: false
			});
		}

		function editData(kd_prm) {
			window.location.href = '{{ route('phprogrampromosihadiah.edit') }}?kd_prm=' + kd_prm + '&status=edit';
		}

		function deleteData(no_bukti) {
			Swal.fire({
				title: 'Konfirmasi',
				text: 'Apakah Anda yakin ingin menghapus data ini?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Ya, Hapus!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({
						url: '{{ url('phprogrampromosihadiah/delete') }}/' + no_bukti,
						type: 'DELETE',
						data: {
							_token: '{{ csrf_token() }}'
						},
						success: function(response) {
							Swal.fire({
								icon: 'success',
								title: 'Success',
								text: 'Data berhasil dihapus'
							}).then(() => {
								location.reload();
							});
						},
						error: function(xhr) {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: xhr.responseJSON?.message || 'Gagal menghapus data'
							});
						}
					});
				}
			});
		}
	</script>
@endsection
