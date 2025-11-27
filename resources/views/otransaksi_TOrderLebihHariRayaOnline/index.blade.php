@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<style>
		.card {
			padding: 15px;
		}

		.form-control:focus {
			background-color: #b5e5f9;
		}

		.btn-action {
			padding: 8px 20px;
			font-weight: 600;
			font-size: 14px;
			margin: 0 3px;
		}

		.btn-new {
			background: #28a745;
			border: none;
			color: #fff;
		}

		.btn-new:hover {
			background: #218838;
			color: #fff;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 13px;
			padding: 12px 8px;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.table tbody td {
			padding: 8px;
			font-size: 13px;
		}

		.loader {
			position: fixed;
			top: 50%;
			left: 50%;
			width: 100px;
			aspect-ratio: 1;
			background:
				radial-gradient(farthest-side, #ffa516 90%, #0000) center/16px 16px,
				radial-gradient(farthest-side, green 90%, #0000) bottom/12px 12px;
			background-repeat: no-repeat;
			animation: l17 1s infinite linear;
			z-index: 9999;
			display: none;
		}

		.loader::before {
			content: "";
			position: absolute;
			width: 8px;
			aspect-ratio: 1;
			inset: auto 0 16px;
			margin: auto;
			background: #ccc;
			border-radius: 50%;
			transform-origin: 50% calc(100% + 10px);
			animation: inherit;
			animation-duration: 0.5s;
		}

		@keyframes l17 {
			100% {
				transform: rotate(1turn)
			}
		}

		.text-right {
			text-align: right !important;
		}

		.text-center {
			text-align: center !important;
		}

		.info-box {
			background: #e7f3ff;
			border: 1px solid #b3d9ff;
			padding: 15px;
			border-radius: 5px;
			margin-bottom: 20px;
		}

		.info-box strong {
			color: #0056b3;
		}
	</style>
@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $judul }}</h1>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				@if (isset($warning))
					<div class="alert alert-warning alert-dismissible fade show" role="alert">
						<strong>Perhatian!</strong> {{ $warning }}
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				@endif

				@if (isset($error))
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<strong>Error!</strong> {{ $error }}
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				@endif

				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<!-- Info Box -->
								<div class="info-box">
									<p class="mb-1"><strong>Petunjuk:</strong></p>
									<ul class="mb-0">
										<li>Klik <strong>NEW</strong> untuk menambah order baru</li>
										<li>Klik <strong>Edit</strong> untuk mengubah data order</li>
										<li>Klik <strong>Detail</strong> untuk melihat detail item order</li>
										<li>Klik <strong>Hapus</strong> untuk menghapus data order</li>
										<li>Data order lebih hari raya untuk fresh food dengan persentase tertentu</li>
									</ul>
								</div>

								<div class="mb-3">
									<button type="button" id="btnNew" class="btn btn-action btn-new">
										<i class="fas fa-plus"></i> NEW
									</button>
									<button type="button" id="btnPrint" class="btn btn-action" style="background: #17a2b8; color: white;">
										<i class="fas fa-print"></i> PRINT
									</button>
									<button type="button" id="btnPrintEvaluasi" class="btn btn-action" style="background: #6610f2; color: white;">
										<i class="fas fa-file-alt"></i> PRINT EVALUASI
									</button>
									<button type="button" id="btnKirimFile" class="btn btn-action" style="background: #fd7e14; color: white;">
										<i class="fas fa-paper-plane"></i> KIRIM FILE
									</button>
								</div>

								<hr>

								<!-- Data Table -->
								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">No</th>
												<th width="150px">Nama File</th>
												<th width="100px" class="text-center">Kode HR</th>
												<th width="120px" class="text-center">Tgl Mulai</th>
												<th width="120px" class="text-center">Tgl Akhir</th>
												<th width="100px" class="text-center">Outlet</th>
												<th width="150px" class="text-center">Tgl Simpan</th>
												<th width="250px" class="text-center">Aksi</th>
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
	</div>

	<div class="loader" id="LOADX"></div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		var table;

		$(document).ready(function() {
			// Initialize DataTable
			table = $('#tableData').DataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: '{{ route('orderlebihharirayaonline_cari') }}',
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}'
					},
					error: function(xhr, error, thrown) {
						console.error('DataTables AJAX Error:', xhr.responseText);
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal memuat data: ' + (xhr.responseJSON?.error || 'Terjadi kesalahan pada server'),
							footer: 'Silakan periksa koneksi atau hubungi administrator'
						});
					}
				},
				columns: [{
						data: 'DT_RowIndex',
						name: 'DT_RowIndex',
						orderable: false,
						searchable: false,
						className: 'text-center'
					},
					{
						data: 'NAMAFILE',
						name: 'NAMAFILE'
					},
					{
						data: 'KODE_HR',
						name: 'KODE_HR',
						className: 'text-center'
					},
					{
						data: 'TGL_AWAL',
						name: 'TGL_AWAL',
						className: 'text-center'
					},
					{
						data: 'TGL_AKHIR',
						name: 'TGL_AKHIR',
						className: 'text-center'
					},
					{
						data: 'OUTLET',
						name: 'OUTLET',
						className: 'text-center'
					},
					{
						data: 'TGL',
						name: 'TGL',
						className: 'text-center'
					},
					{
						data: 'action',
						name: 'action',
						orderable: false,
						searchable: false,
						className: 'text-center'
					}
				],
				order: [
					[6, 'desc']
				],
				language: {
					processing: "Memuat data...",
					lengthMenu: "Tampilkan _MENU_ data per halaman",
					zeroRecords: "Data tidak ditemukan",
					info: "Menampilkan halaman _PAGE_ dari _PAGES_",
					infoEmpty: "Tidak ada data tersedia",
					infoFiltered: "(difilter dari _MAX_ total data)",
					search: "Cari:",
					paginate: {
						first: "Pertama",
						last: "Terakhir",
						next: "Selanjutnya",
						previous: "Sebelumnya"
					},
					emptyTable: "Tidak ada data order. Klik tombol <strong>NEW</strong> untuk menambah data."
				}
			});

			// Button New
			$('#btnNew').on('click', function() {
				window.location.href = '{{ route('orderlebihharirayaonline_edit', 'new') }}';
			});

			// Button Print
			$('#btnPrint').on('click', function() {
				Swal.fire({
					icon: 'info',
					title: 'Print',
					text: 'Fitur print dalam pengembangan'
				});
			});

			// Button Print Evaluasi
			$('#btnPrintEvaluasi').on('click', function() {
				Swal.fire({
					icon: 'info',
					title: 'Print Evaluasi',
					text: 'Fitur print evaluasi dalam pengembangan'
				});
			});

			// Button Kirim File
			$('#btnKirimFile').on('click', function() {
				Swal.fire({
					icon: 'info',
					title: 'Kirim File',
					text: 'Fitur kirim file dalam pengembangan'
				});
			});

			// Button Edit
			$(document).on('click', '.btn-edit', function() {
				var namafile = $(this).data('namafile');
				window.location.href = '{{ route('orderlebihharirayaonline') }}/edit/' + namafile;
			});

			// Button Detail
			$(document).on('click', '.btn-detail', function() {
				var namafile = $(this).data('namafile');
				showDetail(namafile);
			});

			// Button Delete
			$(document).on('click', '.btn-delete', function() {
				var namafile = $(this).data('namafile');

				Swal.fire({
					title: 'Konfirmasi Hapus',
					text: 'Apakah kode ' + namafile + ' akan di hapus?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Hapus!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						deleteData(namafile);
					}
				});
			});
		});

		function deleteData(namafile) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('orderlebihharirayaonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'delete',
					namafile: namafile
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 1500,
							showConfirmButton: false
						});

						table.ajax.reload();
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					var errorMsg = 'Gagal menghapus data';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMsg = xhr.responseJSON.error;
					}

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMsg
					});
				}
			});
		}

		function showDetail(namafile) {
			Swal.fire({
				title: 'Detail Order: ' + namafile,
				html: '<div id="detailContent">Loading...</div>',
				width: '900px',
				showCloseButton: true,
				showConfirmButton: false,
				didOpen: function() {
					loadDetailData(namafile);
				}
			});
		}

		function loadDetailData(namafile) {
			$.ajax({
				url: '{{ route('orderlebihharirayaonline_detail', '') }}/' + namafile,
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					var html = '<table class="table table-sm table-bordered">';
					html += '<thead><tr>';
					html += '<th>No</th><th>Kode</th><th>Nama Barang</th><th>Ket UK</th><th>L/H</th><th>Order Lebih (%)</th>';
					html += '</tr></thead><tbody>';

					if (response.data && response.data.length > 0) {
						response.data.forEach(function(item, index) {
							html += '<tr>';
							html += '<td>' + (index + 1) + '</td>';
							html += '<td>' + item.KD_BRG + '</td>';
							html += '<td>' + item.NA_BRG + '</td>';
							html += '<td>' + item.KET_UK + '</td>';
							html += '<td>' + item.LPH + '</td>';
							html += '<td class="text-right">' + item.PER_ORD + '</td>';
							html += '</tr>';
						});
					} else {
						html += '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>';
					}

					html += '</tbody></table>';
					$('#detailContent').html(html);
				},
				error: function() {
					$('#detailContent').html('<p class="text-danger">Gagal memuat data</p>');
				}
			});
		}
	</script>
@endsection
