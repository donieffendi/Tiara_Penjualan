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

		.btn-allin {
			background: #17a2b8;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-allin:hover {
			background: #138496;
			color: #fff;
		}

		.btn-proses {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-proses:hover {
			background: #218838;
			color: #fff;
		}

		.btn-cetak {
			background: #5bc0de;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-cetak:hover {
			background: #31b0d5;
			color: #fff;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.form-check-input {
			width: 20px;
			height: 20px;
			cursor: pointer;
		}

		.badge-posted {
			background: #28a745;
			color: white;
			padding: 5px 10px;
			border-radius: 4px;
		}

		.badge-unposted {
			background: #dc3545;
			color: white;
			padding: 5px 10px;
			border-radius: 4px;
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

		.filter-section {
			background: #f8f9fa;
			padding: 15px;
			border-radius: 5px;
			margin-bottom: 20px;
		}

		/* DataTable Loading & Empty State */
		.dataTables_processing {
			position: absolute;
			top: 50%;
			left: 50%;
			width: 200px;
			margin-left: -100px;
			margin-top: -26px;
			text-align: center;
			padding: 1em 0;
			background: #ffffff;
			border: 1px solid #ddd;
			border-radius: 4px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
			z-index: 1000;
		}

		.dataTables_processing::after {
			content: "Memuat data...";
			display: block;
			margin-top: 10px;
			font-size: 14px;
			color: #666;
		}

		.dataTables_empty {
			text-align: center;
			padding: 40px 20px !important;
			color: #666;
			font-size: 16px;
			background: #f8f9fa;
		}

		.dataTables_empty::before {
			content: "📋";
			display: block;
			font-size: 48px;
			margin-bottom: 15px;
		}

		table.dataTable tbody tr.odd {
			background-color: #ffffff;
		}

		table.dataTable tbody tr.even {
			background-color: #f9f9f9;
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
								<div class="filter-section">
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label for="flagz"><strong>Jenis Koreksi</strong></label>
												<select class="form-control form-control-lg" id="flagz" name="flagz">
													<option value="MT">Material Toko</option>
													<option value="MH">Material Hadiah (Bulanan)</option>
													<option value="HS">Hadiah Standar</option>
													<option value="HZ">Hadiah (G-Prefix)</option>
												</select>
											</div>
										</div>
										<div class="col-md-8 text-right">
											<label>&nbsp;</label><br>
											<button type="button" id="btnAllIn" class="btn btn-allin">
												<i class="fas fa-check-double"></i> ALL IN
											</button>
											<button type="button" id="btnProses" class="btn btn-proses">
												<i class="fas fa-cog"></i> PROSES
											</button>
											<button type="button" id="btnPrint" class="btn btn-cetak">
												<i class="fas fa-print"></i> CETAK
											</button>
										</div>
									</div>
								</div>

								<hr>

								<div class="row mb-3">
									<div class="col-md-6">
										<div class="form-inline">
											<label>Tampilkan</label>
											<select class="form-control form-control-sm mx-2" id="pageLength">
												<option value="10">10</option>
												<option value="25" selected>25</option>
												<option value="50">50</option>
												<option value="100">100</option>
												<option value="-1">Semua</option>
											</select>
											<label>data</label>
										</div>
									</div>
									<div class="col-md-6 text-right">
										<div class="form-inline float-right">
											<label>Cari:</label>
											<input type="text" class="form-control form-control-sm ml-2" id="searchBox">
										</div>
									</div>
								</div>

								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tablePosting" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">
													<input type="checkbox" id="checkAll" class="form-check-input">
												</th>
												<th width="60px" class="text-center">No</th>
												<th width="120px">No Bukti</th>
												<th width="100px" class="text-center">Tanggal</th>
												<th>Notes</th>
												<th width="200px">Supplier</th>
												<th width="100px" class="text-right">Total</th>
												<th width="80px" class="text-center">Cek</th>
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
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		var table;

		$(document).ready(function() {
			loadData();

			// Event ketika jenis koreksi berubah
			$('#flagz').on('change', function() {
				console.log('Jenis koreksi berubah ke:', $(this).val());

				// Tampilkan loading toast
				Swal.fire({
					title: 'Memuat Data',
					text: 'Mengambil data ' + $(this).find('option:selected').text() + '...',
					timer: 1000,
					timerProgressBar: true,
					toast: true,
					position: 'top-end',
					showConfirmButton: false,
					icon: 'info',
					didOpen: () => {
						Swal.showLoading();
					}
				});

				table.ajax.reload();
			});

			// Page length change
			$('#pageLength').on('change', function() {
				console.log('Page length berubah ke:', $(this).val());
				table.page.len($(this).val()).draw();
			});

			// Search box
			$('#searchBox').on('keyup', function() {
				console.log('Search:', $(this).val());
				table.search($(this).val()).draw();
			});

			// Check all checkbox
			$('#checkAll').on('change', function() {
				var isChecked = $(this).is(':checked');
				console.log('Check all:', isChecked);
				$('.cek-item').prop('checked', isChecked);
			});

			// Button All In - centang semua checkbox
			$('#btnAllIn').on('click', function() {
				console.log('Tombol All In diklik');
				var totalItems = $('.cek-item').length;
				$('.cek-item').prop('checked', true);
				$('#checkAll').prop('checked', true);
				console.log('Total item dicentang:', totalItems);
				Swal.fire({
					icon: 'success',
					title: 'Berhasil',
					text: totalItems + ' data telah dipilih',
					timer: 1500,
					showConfirmButton: false
				});
			});

			// Button Proses
			$('#btnProses').on('click', function() {
				console.log('Tombol Proses diklik');

				var selectedItems = [];
				$('.cek-item:checked').each(function() {
					selectedItems.push($(this).val());
				});

				console.log('Item yang dipilih:', selectedItems);

				if (selectedItems.length === 0) {
					console.warn('Tidak ada item yang dipilih');
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian!',
						text: 'Tidak ada data yang dipilih untuk diproses',
						confirmButtonText: 'OK',
						confirmButtonColor: '#f39c12'
					});
					return;
				}

				var flagz = $('#flagz').val();
				var flagzText = $('#flagz option:selected').text();

				console.log('Flagz:', flagz, 'Text:', flagzText);

				Swal.fire({
					title: 'Konfirmasi Posting',
					html: '<div class="text-center">' +
						'<p>Anda akan memposting <strong class="text-primary">' + selectedItems.length + '</strong> dokumen</p>' +
						'<p>Jenis Koreksi: <strong class="text-info">' + flagzText + '</strong></p>' +
						'<hr>' +
						'<p class="text-danger"><small><i class="fas fa-exclamation-triangle"></i> Proses ini akan mengupdate stok dan tidak dapat dibatalkan!</small></p>' +
						'</div>',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#28a745',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses Sekarang!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal',
					showLoaderOnConfirm: true,
					allowOutsideClick: false,
					allowEscapeKey: false,
					preConfirm: () => {
						console.log('Memulai proses posting...');
						$('#btnProses').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> MEMPROSES...');
						$('#LOADX').show();

						// Tampilkan loading alert
						Swal.fire({
							title: 'Memproses...',
							html: '<div class="text-center">' +
								'<div class="spinner-border text-primary mb-3" role="status"><span class="sr-only">Loading...</span></div>' +
								'<p>Sedang memposting <strong>' + selectedItems.length + '</strong> dokumen</p>' +
								'<p class="text-muted"><small>Mohon tunggu, jangan tutup halaman ini...</small></p>' +
								'</div>',
							allowOutsideClick: false,
							allowEscapeKey: false,
							showConfirmButton: false,
							didOpen: () => {
								Swal.showLoading();
							}
						});

						return $.ajax({
							url: "{{ route('tpostingkoreksitoko_posting_bulk') }}",
							type: 'POST',
							data: {
								_token: '{{ csrf_token() }}',
								no_bukti_list: selectedItems,
								flagz: flagz
							},
							timeout: 60000 // 60 detik timeout
						}).done(function(response) {
							console.log('Posting berhasil:', response);
							$('#btnProses').prop('disabled', false).html('<i class="fas fa-cog"></i> PROSES');
							$('#LOADX').hide();

							// Success alert
							Swal.fire({
								icon: 'success',
								title: 'Berhasil!',
								html: '<div class="text-center">' +
									'<p><strong>' + response.message + '</strong></p>' +
									'<p class="text-muted"><small>' + selectedItems.length +
									' dokumen telah diposting</small></p>' +
									'</div>',
								timer: 3000,
								timerProgressBar: true,
								showConfirmButton: true,
								confirmButtonText: 'OK',
								confirmButtonColor: '#28a745'
							}).then(() => {
								// Reload tabel dan reset checkbox
								table.ajax.reload(null, false); // false = tidak reset ke halaman 1
								$('#checkAll').prop('checked', false);
							});
						}).fail(function(xhr) {
							console.error('Posting gagal:', xhr);
							$('#btnProses').prop('disabled', false).html('<i class="fas fa-cog"></i> PROSES');
							$('#LOADX').hide();

							var errorMsg = 'Terjadi kesalahan yang tidak diketahui';
							if (xhr.responseJSON && xhr.responseJSON.error) {
								errorMsg = xhr.responseJSON.error;
							} else if (xhr.statusText) {
								errorMsg = xhr.statusText;
							}

							// Error alert
							Swal.fire({
								icon: 'error',
								title: 'Posting Gagal!',
								html: '<div class="text-left">' +
									'<p><strong>Error:</strong></p>' +
									'<p class="text-danger">' + errorMsg + '</p>' +
									'<hr>' +
									'<p><small><strong>Troubleshooting:</strong><br>' +
									'1. Check browser console (F12) untuk detail<br>' +
									'2. Check Laravel log file (storage/logs/laravel.log)<br>' +
									'3. Pastikan database connection aktif<br>' +
									'4. Pastikan stored procedure tersedia<br>' +
									'5. Hubungi administrator jika masalah berlanjut</small></p>' +
									'</div>',
								footer: '<small>Status: ' + xhr.status + ' - ' + xhr.statusText + '</small>',
								confirmButtonText: 'OK',
								confirmButtonColor: '#d33'
							});

							return false; // Prevent closing the confirmation dialog
						});
					}
				}).then((result) => {
					if (result.isDismissed) {
						console.log('Posting dibatalkan oleh user');
						Swal.fire({
							icon: 'info',
							title: 'Dibatalkan',
							text: 'Proses posting dibatalkan',
							timer: 2000,
							showConfirmButton: false,
							toast: true,
							position: 'top-end'
						});
					}
				});
			});

			// Button Print
			$('#btnPrint').on('click', function() {
				var flagz = $('#flagz').val();
				console.log('Cetak laporan flagz:', flagz);
				window.open("{{ route('tpostingkoreksitoko_jasper') }}?flagz=" + flagz, '_blank');
			});
		});

		function loadData() {
			console.log('=== MEMUAT DATATABLE ===');
			console.log('Flagz saat init:', $('#flagz').val());

			table = $('#tablePosting').KoolDataTable({
				processing: true,
				serverSide: true,
				deferLoading: 0, // Force load data immediately
				language: {
					processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div><br>Memuat data...',
					emptyTable: "Tidak ada data koreksi yang belum diposting",
					zeroRecords: "Tidak ada data yang sesuai dengan pencarian",
					info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
					infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
					infoFiltered: "(disaring dari _MAX_ total data)",
					lengthMenu: "Tampilkan _MENU_ data",
					search: "Cari:",
					paginate: {
						first: "Pertama",
						last: "Terakhir",
						next: "Berikutnya",
						previous: "Sebelumnya"
					}
				},
				ajax: {
					url: "{{ route('get-tpostingkoreksitoko-post') }}",
					data: function(d) {
						d.flagz = $('#flagz').val();
						console.log('=== AJAX REQUEST ===');
						console.log('URL:', "{{ route('get-tpostingkoreksitoko-post') }}");
						console.log('Flagz dikirim:', d.flagz);
						console.log('Full request data:', d);
					},
					error: function(xhr, error, code) {
						console.error('=== DATATABLE ERROR ===');
						console.error('XHR:', xhr);
						console.error('Error:', error);
						console.error('Code:', code);
						console.error('Response:', xhr.responseJSON);
						console.error('Status:', xhr.status);
						console.error('Status Text:', xhr.statusText);

						Swal.fire({
							icon: 'error',
							title: 'Error Load Data',
							html: '<div class="text-left">' +
								'<p><strong>Terjadi kesalahan saat memuat data:</strong></p>' +
								'<p class="text-danger">' + (xhr.responseJSON?.error || xhr.statusText || 'Gagal memuat data') + '</p>' +
								'<hr>' +
								'<p><small><strong>Troubleshooting:</strong><br>' +
								'1. Pastikan Anda sudah login dengan user yang memiliki CBG<br>' +
								'2. Check browser console (F12) untuk detail error<br>' +
								'3. Check Laravel log di storage/logs/laravel.log<br>' +
								'4. Pastikan database connection aktif</small></p>' +
								'</div>',
							footer: '<small>Status: ' + xhr.status + ' - ' + xhr.statusText + '</small>',
							confirmButtonText: 'OK',
							confirmButtonColor: '#3085d6'
						});
					},
					success: function(response) {
						console.log('=== AJAX SUCCESS ===');
						console.log('Total data:', response.recordsTotal);
						console.log('Data filtered:', response.recordsFiltered);
						console.log('Response:', response);

						if (response.data && response.data.length > 0) {
							console.log('Sample data pertama:', response.data[0]);
						} else {
							console.warn('TIDAK ADA DATA di response');
							// Tampilkan notifikasi info jika tidak ada data
							setTimeout(function() {
								Swal.fire({
									icon: 'info',
									title: 'Informasi',
									text: 'Tidak ada data koreksi yang belum diposting untuk jenis ' + $(
										'#flagz option:selected').text(),
									timer: 3000,
									timerProgressBar: true,
									toast: true,
									position: 'top-end',
									showConfirmButton: false
								});
							}, 500);
						}
					}
				},
				columns: [{
						data: 'cek_checkbox',
						name: 'cek_checkbox',
						orderable: false,
						searchable: false,
						className: 'text-center'
					},
					{
						data: 'DT_RowIndex',
						name: 'DT_RowIndex',
						orderable: false,
						searchable: false,
						className: 'text-center'
					},
					{
						data: 'no_bukti',
						name: 'no_bukti'
					},
					{
						data: 'tgl',
						name: 'tgl',
						className: 'text-center'
					},
					{
						data: 'notes',
						name: 'notes'
					},
					{
						data: 'namas',
						name: 'namas'
					},
					{
						data: 'total',
						name: 'total',
						className: 'text-right'
					},
					{
						data: 'status',
						name: 'status',
						orderable: false,
						searchable: false,
						className: 'text-center'
					}
				],
				pageLength: 25,
				lengthMenu: [
					[10, 25, 50, 100, -1],
					[10, 25, 50, 100, "Semua"]
				],
				order: [
					[2, 'asc']
				],
				dom: 'rt<"row mt-3"<"col-md-6"i><"col-md-6"p>>'
			});

			console.log('DataTable initialized');

			// Force load data setelah inisialisasi
			setTimeout(function() {
				console.log('Forcing table reload...');
				table.ajax.reload();
			}, 300);
		}
	</script>
@endsection
