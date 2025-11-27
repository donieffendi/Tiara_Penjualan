@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<style>
		.card {
			padding: 15px;
		}

		.form-control:focus {
			background-color: #b5e5f9;
		}

		.status-posted {
			color: #28a745;
			font-weight: bold;
			padding: 5px 10px;
			background: #d4edda;
			border-radius: 4px;
			display: inline-block;
		}

		.status-unposted {
			color: #dc3545;
			font-weight: bold;
			padding: 5px 10px;
			background: #f8d7da;
			border-radius: 4px;
			display: inline-block;
		}

		.btn-posting {
			background: #007bff;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 12px 30px;
			font-size: 16px;
		}

		.btn-posting:hover {
			background: #0056b3;
			color: #fff;
		}

		.btn-posting:disabled {
			background: #6c757d;
			cursor: not-allowed;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
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

		.posting-info {
			background: #e7f3ff;
			border-left: 4px solid #007bff;
			padding: 15px;
			margin-bottom: 20px;
			border-radius: 4px;
		}

		.posting-info h5 {
			margin: 0 0 10px 0;
			color: #004085;
		}

		.posting-info p {
			margin: 5px 0;
			color: #004085;
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
				<div class="row">
					<div class="col-12">
						@if (isset($warning))
							<div class="alert alert-warning alert-dismissible fade show" role="alert">
								<i class="fas fa-exclamation-triangle"></i> <strong>Peringatan!</strong> {{ $warning }}
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						@endif

						@if (isset($error))
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<i class="fas fa-exclamation-circle"></i> <strong>Error!</strong> {{ $error }}
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						@endif

						@if (session('error'))
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<i class="fas fa-exclamation-circle"></i> <strong>Error!</strong> {{ session('error') }}
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						@endif

						@if (session('success'))
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								<i class="fas fa-check-circle"></i> <strong>Sukses!</strong> {{ session('success') }}
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						@endif

						<div class="card">
							<div class="card-body">

								<div class="posting-info">
									<h5><i class="fas fa-info-circle"></i> Informasi Posting Kasir</h5>
									<p><strong>Proses:</strong> Posting akan memanggil stored procedure <code>postjualtgl</code> dengan parameter tanggal dan cabang (CBG)</p>
									<p><strong>Catatan:</strong> Semua transaksi penjualan pada tanggal yang dipilih dan sesuai dengan cabang Anda akan diposting</p>
								</div>

								<form id="formPosting">
									@csrf
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="tgl_posting"><strong>Tanggal Posting</strong></label>
												<input type="date" class="form-control form-control-lg" id="tgl_posting" name="tgl_posting" value="{{ date('Y-m-d') }}" required>
												<small class="form-text text-muted">Pilih tanggal untuk posting transaksi penjualan</small>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label>&nbsp;</label>
												<button type="button" id="btnPosting" class="btn btn-posting btn-lg btn-block">
													<i class="fas fa-check-circle"></i> POSTING
												</button>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label>&nbsp;</label>
												<button type="button" id="btnPrint" class="btn btn-info btn-lg btn-block">
													<i class="fas fa-print"></i> CETAK LAPORAN
												</button>
											</div>
										</div>
									</div>
								</form>

								<hr>

								<div class="row mb-3">
									<div class="col-md-12">
										<button type="button" id="btnRefresh" class="btn btn-secondary">
											<i class="fas fa-sync-alt"></i> Refresh Data
										</button>
									</div>
								</div>

								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tablePosting" style="width:100%">
										<thead>
											<tr>
												<th>No. Bukti</th>
												<th>Tanggal</th>
												<th>Kode</th>
												<th>Nama Pelanggan</th>
												<th class="text-right">Total</th>
												<th class="text-center">Status</th>
											</tr>
										</thead>
										<tbody></tbody>
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
	<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		var table;

		$(document).ready(function() {
			// Cek apakah ada warning atau error dari controller
			@if (isset($warning))
				Swal.fire({
					icon: 'warning',
					title: 'Peringatan',
					text: '{{ $warning }}',
					confirmButtonText: 'OK'
				});
			@endif

			@if (isset($error))
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: '{{ $error }}',
					confirmButtonText: 'OK'
				});
			@endif

			// Error handler untuk DataTables
			$.fn.dataTable.ext.errMode = function(settings, helpPage, message) {
				console.error('DataTables Error:', message);

				Swal.fire({
					icon: 'error',
					title: 'Error DataTables',
					html: '<div style="text-align: left;">' +
						'<p><strong>Terjadi kesalahan saat memuat data:</strong></p>' +
						'<p style="color: #dc3545; font-family: monospace; font-size: 12px;">' + message + '</p>' +
						'<p class="mt-2">Silakan periksa:</p>' +
						'<ul>' +
						'<li>Koneksi database</li>' +
						'<li>Route sudah terdaftar</li>' +
						'<li>Periode sudah diset</li>' +
						'<li>User memiliki akses CBG</li>' +
						'</ul>' +
						'</div>',
					confirmButtonText: 'OK',
					width: '600px'
				});
			};

			// Initialize DataTable
			table = $('#tablePosting').DataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: "{{ route('get-tpostingkasir-post') }}",
					type: "GET",
					error: function(xhr, error, code) {
						console.error('Ajax Error:', xhr);
						console.error('Error Code:', code);
						console.error('Error Details:', error);

						var errorMsg = 'Terjadi kesalahan saat memuat data';
						var errorDetails = '';

						if (xhr.status === 0) {
							errorMsg = 'Tidak dapat terhubung ke server';
							errorDetails = 'Periksa koneksi internet atau server aplikasi';
						} else if (xhr.status == 404) {
							errorMsg = 'Halaman tidak ditemukan (404)';
							errorDetails = 'Route "get-tpostingkasir-post" tidak ditemukan';
						} else if (xhr.status == 500) {
							errorMsg = 'Server Error (500)';
							errorDetails = xhr.responseJSON?.message || xhr.responseText || 'Internal Server Error';
						} else if (xhr.status == 400) {
							errorMsg = 'Bad Request (400)';
							errorDetails = xhr.responseJSON?.error || 'Request tidak valid';
						} else {
							errorMsg = 'Error ' + xhr.status;
							errorDetails = xhr.responseJSON?.error || xhr.responseJSON?.message || xhr.statusText;
						}

						Swal.fire({
							icon: 'error',
							title: errorMsg,
							html: '<div style="text-align: left;">' +
								'<p><strong>Status:</strong> ' + xhr.status + ' - ' + xhr.statusText + '</p>' +
								'<p><strong>Detail:</strong></p>' +
								'<p style="color: #dc3545; font-family: monospace; font-size: 12px; background: #f8f9fa; padding: 10px; border-radius: 4px;">' +
								errorDetails + '</p>' +
								'</div>',
							confirmButtonText: 'OK',
							width: '600px'
						});
					}
				},
				columns: [{
						data: 'NO_BUKTI',
						name: 'NO_BUKTI'
					},
					{
						data: 'TGL',
						name: 'TGL',
						render: function(data) {
							return moment(data).format('DD-MM-YYYY');
						}
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
						data: 'TOTAL',
						name: 'TOTAL',
						className: 'text-right',
						render: function(data) {
							return parseFloat(data).toLocaleString('id-ID', {
								minimumFractionDigits: 2,
								maximumFractionDigits: 2
							});
						}
					},
					{
						data: 'STATUS',
						name: 'STATUS',
						className: 'text-center',
						render: function(data, type, row) {
							if (row.POSTED == 1) {
								return '<span class="status-posted"><i class="fas fa-check-circle"></i> ' + data + '</span>';
							} else {
								return '<span class="status-unposted"><i class="fas fa-times-circle"></i> ' + data + '</span>';
							}
						}
					}
				],
				pageLength: 25,
				lengthMenu: [
					[10, 25, 50, 100, -1],
					[10, 25, 50, 100, "Semua"]
				],
				order: [
					[1, 'desc']
				],
				language: {
					processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
					search: "Cari:",
					lengthMenu: "Tampilkan _MENU_ data",
					info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
					infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
					infoFiltered: "(difilter dari _MAX_ total data)",
					paginate: {
						first: "Pertama",
						last: "Terakhir",
						next: "Selanjutnya",
						previous: "Sebelumnya"
					},
					zeroRecords: "Tidak ada data yang ditemukan",
					emptyTable: "Tidak ada data di tabel"
				},
				drawCallback: function(settings) {
					// Cek apakah ada error message dari server
					var json = settings.json;
					if (json && json.error) {
						Swal.fire({
							icon: 'warning',
							title: 'Perhatian',
							html: '<div style="text-align: left;">' +
								'<p><strong>Pesan dari server:</strong></p>' +
								'<p style="color: #856404; font-size: 14px;">' + json.error + '</p>' +
								'</div>',
							confirmButtonText: 'OK'
						});
					}
				}
			});

			// Button Posting - Sesuai implementasi Delphi
			// Memanggil stored procedure postjualtgl dengan parameter tanggal dan cbg
			$('#btnPosting').on('click', function() {
				var tgl = $('#tgl_posting').val();

				if (!tgl) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Tanggal posting harus diisi'
					});
					return;
				}

				Swal.fire({
					title: 'Konfirmasi Posting',
					html: 'Posting <strong>semua transaksi penjualan</strong> pada tanggal <strong>' + moment(tgl).format(
							'DD-MM-YYYY') + '</strong>?<br><br>' +
						'<small class="text-muted">Proses ini akan memanggil stored procedure <code>postjualtgl</code></small>',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Posting!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						// Disable button dan ubah text seperti di Delphi
						$('#btnPosting').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');
						$('#LOADX').show();

						return $.ajax({
							url: "{{ url('/tpostingkasir/posting-bulk') }}",
							type: 'POST',
							data: {
								_token: '{{ csrf_token() }}',
								tgl_posting: tgl
							}
						}).fail(function(xhr) {
							$('#LOADX').hide();
							$('#btnPosting').prop('disabled', false).html('<i class="fas fa-check-circle"></i> POSTING');
							Swal.showValidationMessage('Posting gagal: ' + (xhr.responseJSON?.error ||
								'Terjadi kesalahan'));
						});
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					$('#btnPosting').prop('disabled', false).html('<i class="fas fa-check-circle"></i> POSTING');
					$('#LOADX').hide();

					if (result.isConfirmed) {
						// Tampilkan pesan "Posting Selesai..." seperti di Delphi
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Posting Selesai...',
							showConfirmButton: true,
							confirmButtonText: 'OK'
						}).then(() => {
							// Reload table setelah posting
							table.ajax.reload();
						});
					}
				});
			});

			$('#btnRefresh').on('click', function() {
				table.ajax.reload();
			});

			$('#btnPrint').on('click', function() {
				window.open("{{ url('/tpostingkasir/jasper') }}", '_blank');
			});
		});
	</script>
@endsection
