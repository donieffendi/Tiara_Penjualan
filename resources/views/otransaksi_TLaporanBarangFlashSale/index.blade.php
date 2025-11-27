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

		.btn-proses {
			background: #007bff;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-proses:hover {
			background: #0056b3;
			color: #fff;
		}

		.btn-tampil {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-tampil:hover {
			background: #218838;
			color: #fff;
		}

		.btn-excel {
			background: #17a2b8;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-excel:hover {
			background: #138496;
			color: #fff;
		}

		.btn-print {
			background: #6c757d;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-print:hover {
			background: #5a6268;
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

		.info-section {
			background: #e7f3ff;
			border: 1px solid #007bff;
			padding: 15px;
			border-radius: 5px;
			margin-bottom: 20px;
		}

		.text-right-col {
			text-align: right !important;
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
								<div class="info-section">
									<i class="fas fa-info-circle"></i>
									<strong>Informasi:</strong>
									Laporan ini menampilkan data barang flash sale yang perlu ditinjau. Klik tombol <strong>Proses Data</strong> untuk memuat ulang data terbaru
									dari stored procedure.
								</div>

								<div class="row mb-3">
									<div class="col-md-12 text-right">
										<button type="button" id="btnProses" class="btn btn-proses">
											<i class="fas fa-cogs"></i> PROSES DATA
										</button>
										<button type="button" id="btnTampil" class="btn btn-tampil">
											<i class="fas fa-sync-alt"></i> TAMPILKAN
										</button>
										<button type="button" id="btnExcel" class="btn btn-excel">
											<i class="fas fa-file-excel"></i> EXCEL
										</button>
										<button type="button" id="btnPrint" class="btn btn-print">
											<i class="fas fa-print"></i> PRINT
										</button>
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
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">No</th>
												<th>Sub Item</th>
												<th>Nama Barang</th>
												<th>Ukuran</th>
												<th>Kemasan</th>
												<th>KD Program</th>
												<th class="text-right">Dis Sebelumnya</th>
												<th class="text-right">Dis Baru</th>
												<th class="text-right">Stok</th>
												<th class="text-center">Tgl Jual</th>
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
	<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/tableExport.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/libs/jsPDF/jspdf.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/libs/jsPDF-AutoTable/jspdf.plugin.autotable.js"></script>
	<script>
		var table;

		$(document).ready(function() {
			console.log('=== HALAMAN LAPORAN FLASH SALE DIMUAT ===');
			loadData();

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

			// Button Proses Data
			$('#btnProses').on('click', function() {
				console.log('=== TOMBOL PROSES DATA DIKLIK ===');

				Swal.fire({
					title: 'Konfirmasi Proses',
					html: 'Proses data akan memanggil stored procedure untuk memperbarui laporan.<br><small class="text-info">Data akan dimuat ulang dari database.</small>',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#007bff',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal',
					showLoaderOnConfirm: true,
					allowOutsideClick: false,
					allowEscapeKey: false,
					preConfirm: () => {
						console.log('Memulai proses data...');
						$('#btnProses').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');
						$('#LOADX').show();

						return $.ajax({
							url: '{{ route('laporanbarangflashsale_detail', '') }}/0',
							type: 'GET'
						}).done(function(response) {
							console.log('=== PROSES DATA BERHASIL ===');
							console.log('Response:', response);
						}).fail(function(xhr) {
							console.error('=== PROSES DATA GAGAL ===');
							console.error('Status:', xhr.status);
							console.error('Response:', xhr.responseJSON);

							$('#LOADX').hide();
							Swal.showValidationMessage('Proses gagal: ' + (xhr.responseJSON?.error ||
								'Terjadi kesalahan'));
						});
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					$('#btnProses').prop('disabled', false).html('<i class="fas fa-cogs"></i> PROSES DATA');
					$('#LOADX').hide();

					if (result.isConfirmed) {
						console.log('Proses data dikonfirmasi, reload tabel...');
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							html: result.value.message,
							timer: 2000,
							showConfirmButton: true
						});
						table.ajax.reload();
					} else if (result.isDismissed) {
						console.log('Proses data dibatalkan');
					}
				});
			});

			// Button Tampilkan - Refresh data
			$('#btnTampil').on('click', function() {
				console.log('=== TOMBOL TAMPILKAN DIKLIK ===');
				$('#LOADX').show();
				table.ajax.reload(function() {
					console.log('Data berhasil dimuat ulang');
					$('#LOADX').hide();
					Swal.fire({
						icon: 'success',
						title: 'Data Dimuat Ulang',
						text: 'Data telah berhasil diperbarui',
						timer: 1500,
						showConfirmButton: false
					});
				});
			});

			// Button Excel
			$('#btnExcel').on('click', function() {
				console.log('=== TOMBOL EXCEL DIKLIK ===');

				if (table.data().count() === 0) {
					console.warn('Tidak ada data untuk diekspor');
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Tidak ada data untuk diekspor'
					});
					return;
				}

				console.log('Mengekspor ' + table.data().count() + ' data ke Excel');

				var filename = 'Laporan_Flash_Sale_' + new Date().getTime();

				// Export menggunakan DataTables buttons
				table.button('.buttons-excel').trigger();

				Swal.fire({
					icon: 'success',
					title: 'Export Excel',
					text: 'Data berhasil diekspor ke Excel',
					timer: 1500,
					showConfirmButton: false
				});
			});

			// Button Print
			$('#btnPrint').on('click', function() {
				console.log('=== TOMBOL PRINT DIKLIK ===');

				if (table.data().count() === 0) {
					console.warn('Tidak ada data untuk dicetak');
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Tidak ada data untuk dicetak'
					});
					return;
				}

				console.log('Mencetak ' + table.data().count() + ' data');

				// Trigger print dengan window.print()
				var printWindow = window.open('', '', 'height=600,width=800');
				printWindow.document.write('<html><head><title>Laporan Barang Flash Sale</title>');
				printWindow.document.write('<style>');
				printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
				printWindow.document.write('th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 11px; }');
				printWindow.document.write('th { background-color: #343a40; color: white; }');
				printWindow.document.write('.text-right { text-align: right; }');
				printWindow.document.write('.text-center { text-align: center; }');
				printWindow.document.write('h2 { text-align: center; }');
				printWindow.document.write('</style>');
				printWindow.document.write('</head><body>');
				printWindow.document.write('<h2>Laporan Barang Flash Sale</h2>');
				printWindow.document.write('<p>Tanggal Cetak: ' + new Date().toLocaleDateString('id-ID') + '</p>');

				var tableClone = $('#tableData').clone();
				tableClone.find('tbody tr').each(function() {
					$(this).find('td:first').remove(); // Remove checkbox/numbering column if needed
				});

				printWindow.document.write(tableClone[0].outerHTML);
				printWindow.document.write('</body></html>');
				printWindow.document.close();

				setTimeout(function() {
					printWindow.print();
					printWindow.close();
					console.log('Print dialog ditampilkan');
				}, 250);
			});
		});

		function loadData() {
			console.log('=== MEMUAT DATATABLE ===');

			table = $('#tableData').DataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: '{{ route('laporanbarangflashsale_cari') }}',
					type: 'POST',
					data: function(d) {
						d._token = '{{ csrf_token() }}';
						console.log('=== AJAX REQUEST ===');
						console.log('Request data:', d);
					},
					error: function(xhr, error, code) {
						console.error('=== DATATABLE ERROR ===');
						console.error('XHR:', xhr);
						console.error('Error:', error);
						console.error('Code:', code);
						console.error('Response:', xhr.responseJSON);

						$('#LOADX').hide();

						Swal.fire({
							icon: 'error',
							title: 'Error Loading Data',
							html: '<div class="text-left">' +
								'<p><strong>Terjadi kesalahan saat memuat data:</strong></p>' +
								'<p class="text-danger">' + (xhr.responseJSON?.error || xhr.responseJSON?.message ||
								'Gagal memuat data') + '</p>' +
								'<hr>' +
								'<p><small><strong>Troubleshooting:</strong><br>' +
								'1. Pastikan Anda sudah login dengan user yang memiliki CBG<br>' +
								'2. Check browser console (F12) untuk detail error<br>' +
								'3. Check Laravel log di storage/logs/laravel.log<br>' +
								'4. Pastikan database connection aktif<br>' +
								'5. Pastikan tabel laporan_brg_macet ada</small></p>' +
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

						if (response.data && response.data.length > 0) {
							console.log('Sample data pertama:', response.data[0]);
						} else {
							console.warn('TIDAK ADA DATA di response');
						}
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
						data: 'sub_item',
						name: 'sub_item'
					},
					{
						data: 'nama_barang',
						name: 'nama_barang'
					},
					{
						data: 'ukuran',
						name: 'ukuran',
						className: 'text-center'
					},
					{
						data: 'kemasan',
						name: 'kemasan',
						className: 'text-center'
					},
					{
						data: 'kd_program',
						name: 'kd_program'
					},
					{
						data: 'dis_sebelumnya',
						name: 'dis_sebelumnya',
						className: 'text-right'
					},
					{
						data: 'dis_baru',
						name: 'dis_baru',
						className: 'text-right'
					},
					{
						data: 'stok',
						name: 'stok',
						className: 'text-right'
					},
					{
						data: 'tgl_jual',
						name: 'tgl_jual',
						className: 'text-center'
					}
				],
				pageLength: 25,
				lengthMenu: [
					[10, 25, 50, 100, -1],
					[10, 25, 50, 100, "Semua"]
				],
				order: [
					[1, 'asc']
				],
				dom: 'Bfrtip',
				buttons: [{
					extend: 'excel',
					className: 'buttons-excel d-none',
					filename: 'Laporan_Flash_Sale_' + new Date().getTime(),
					title: 'Laporan Barang Flash Sale',
					exportOptions: {
						columns: ':visible'
					}
				}],
				language: {
					processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
					emptyTable: "Tidak ada data tersedia. Klik tombol <strong>PROSES DATA</strong> untuk memuat data.",
					info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
					infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
					infoFiltered: "(disaring dari _MAX_ total data)",
					lengthMenu: "Tampilkan _MENU_ data",
					loadingRecords: "Memuat data...",
					processing: "Memproses...",
					search: "Cari:",
					zeroRecords: "Data tidak ditemukan",
					paginate: {
						first: "Pertama",
						last: "Terakhir",
						next: "Selanjutnya",
						previous: "Sebelumnya"
					}
				}
			});
		}
	</script>
@endsection
