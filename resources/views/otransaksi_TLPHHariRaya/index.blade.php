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
										<li>Klik <strong>NEW</strong> untuk menambah usulan LPH Hari Raya baru</li>
										<li>Klik <strong>Edit</strong> untuk mengubah data usulan</li>
										<li>Klik <strong>Mulai</strong> untuk memulai event Hari Raya (mengubah LPH barang)</li>
										<li>Klik <strong>Hentikan</strong> untuk menghentikan event dan mengembalikan LPH</li>
										<li>Klik <strong>Rekap</strong> untuk melihat rekap penjualan selama periode event</li>
										<li>Klik <strong>Hapus</strong> untuk menghapus data usulan (hanya data yang belum diposting)</li>
									</ul>
								</div>

								<div class="mb-3 text-right">
									<button type="button" id="btnNew" class="btn btn-action btn-new">
										<i class="fas fa-plus"></i> NEW
									</button>
								</div>

								<hr>

								<!-- Data Table -->
								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">No</th>
												<th width="180px">No Bukti</th>
												<th>Hari Raya</th>
												<th width="120px" class="text-center">Tgl Mulai</th>
												<th width="120px" class="text-center">Tgl Akhir</th>
												<th width="140px" class="text-center">Tgl Simpan</th>
												<th width="100px" class="text-center">Post</th>
												<th width="380px" class="text-center">Aksi</th>
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
				ajax: {
					url: '{{ route('lphhariraya_cari') }}',
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}'
					},
					error: function(xhr, error, code) {
						console.error('DataTables Ajax error:', xhr.responseText);

						if (xhr.status === 500 || xhr.status === 400) {
							var errorMsg = 'Terjadi kesalahan saat memuat data.';

							try {
								var response = JSON.parse(xhr.responseText);
								if (response.error) {
									errorMsg = response.error;
								}
							} catch (e) {
								errorMsg = 'Tabel database belum dibuat. Silakan jalankan file SQL: sql/create_table_usul_hraya.sql';
							}

							Swal.fire({
								icon: 'warning',
								title: 'Perhatian',
								html: errorMsg +
									'<br><br><small>Untuk membuat tabel, jalankan query SQL di:<br><code>sql/create_table_usul_hraya.sql</code></small>',
								confirmButtonText: 'OK'
							});
						}
					}
				},
				columns: [{
						data: null,
						render: function(data, type, row, meta) {
							return meta.row + 1;
						},
						className: 'text-center'
					},
					{
						data: 'NO_BUKTI'
					},
					{
						data: 'NAMA_EVENT'
					},
					{
						data: 'TGL_AWAL',
						className: 'text-center'
					},
					{
						data: 'TGL_AKHIR',
						className: 'text-center'
					},
					{
						data: 'TGL_SIMPAN',
						className: 'text-center'
					},
					{
						data: 'POSTED',
						className: 'text-center'
					},
					{
						data: 'action',
						className: 'text-center'
					}
				],
				order: [
					[5, 'desc']
				],
				processing: true
			});

			// Button New - navigate to edit page with 'new' as no_bukti
			$('#btnNew').on('click', function(e) {
				e.preventDefault();
				var url = '{{ url('tlphhariraya/edit/new') }}';
				window.location.href = url;
			});

			// Button Edit
			$(document).on('click', '.btn-edit', function() {
				var nobukti = $(this).data('nobukti');
				window.location.href = '{{ route('lphhariraya') }}/edit/' + encodeURIComponent(nobukti);
			});

			// Button Delete
			$(document).on('click', '.btn-delete', function() {
				if ($(this).prop('disabled')) {
					return;
				}

				var nobukti = $(this).data('nobukti');

				Swal.fire({
					title: 'Konfirmasi Hapus',
					text: 'Apakah usulan ' + nobukti + ' akan dihapus?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Hapus!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						deleteData(nobukti);
					}
				});
			});

			// Button Start
			$(document).on('click', '.btn-start', function() {
				if ($(this).prop('disabled')) {
					return;
				}

				var nobukti = $(this).data('nobukti');

				Swal.fire({
					title: 'Konfirmasi Mulai',
					text: 'Apakah memulai usulan ' + nobukti + '?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#28a745',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-play"></i> Ya, Mulai!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						startEvent(nobukti);
					}
				});
			});

			// Button Stop
			$(document).on('click', '.btn-stop', function() {
				var nobukti = $(this).data('nobukti');

				Swal.fire({
					title: 'Konfirmasi Hentikan',
					text: 'Apakah menghentikan usulan ' + nobukti + '?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#dc3545',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-stop"></i> Ya, Hentikan!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						stopEvent(nobukti);
					}
				});
			});

			// Button Rekap
			$(document).on('click', '.btn-rekap', function() {
				var nobukti = $(this).data('nobukti');
				rekapJual(nobukti);
			});

			// Button Print
			$(document).on('click', '.btn-print', function() {
				var nobukti = $(this).data('nobukti');
				printData(nobukti);
			});
		});

		function deleteData(nobukti) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('lphhariraya_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'delete',
					no_bukti: nobukti
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

		function startEvent(nobukti) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('lphhariraya_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'start',
					no_bukti: nobukti
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

					var errorMsg = 'Gagal memulai event';
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

		function stopEvent(nobukti) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('lphhariraya_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'stop',
					no_bukti: nobukti
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

					var errorMsg = 'Gagal menghentikan event';
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

		function rekapJual(nobukti) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('lphhariraya_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'rekap',
					no_bukti: nobukti,
					ulang: 'N'
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							showConfirmButton: true
						}).then(() => {
							if (response.show_report) {
								// Open print report in new window
								// window.open('{{ route('lphhariraya') }}/print/' + nobukti, '_blank');
								alert('Fitur print report akan dikembangkan');
							}
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					if (xhr.responseJSON && xhr.responseJSON.confirm_ulang) {
						Swal.fire({
							title: 'Konfirmasi',
							text: 'Data rekap sudah pernah dibuat. Ingin proses ulang?',
							icon: 'question',
							showCancelButton: true,
							confirmButtonColor: '#ffc107',
							cancelButtonColor: '#6c757d',
							confirmButtonText: '<i class="fas fa-redo"></i> Ya, Proses Ulang!',
							cancelButtonText: '<i class="fas fa-times"></i> Batal'
						}).then((result) => {
							if (result.isConfirmed) {
								rekapJualUlang(nobukti);
							}
						});
					} else {
						var errorMsg = 'Gagal membuat rekap';
						if (xhr.responseJSON && xhr.responseJSON.error) {
							errorMsg = xhr.responseJSON.error;
						}

						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: errorMsg
						});
					}
				}
			});
		}

		function rekapJualUlang(nobukti) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('lphhariraya_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'rekap',
					no_bukti: nobukti,
					ulang: 'Y'
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							showConfirmButton: true
						}).then(() => {
							if (response.show_report) {
								alert('Fitur print report akan dikembangkan');
							}
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					var errorMsg = 'Gagal membuat rekap';
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

		function printData(nobukti) {
			alert('Fitur print akan dikembangkan untuk NO_BUKTI: ' + nobukti);
			// Implementation untuk print report
		}
	</script>
@endsection
