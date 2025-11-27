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

		.btn-tampilkan {
			background: #17a2b8;
			border: none;
			color: #fff;
		}

		.btn-tampilkan:hover {
			background: #138496;
			color: #fff;
		}

		.btn-excel {
			background: #28a745;
			border: none;
			color: #fff;
		}

		.btn-excel:hover {
			background: #218838;
			color: #fff;
		}

		.btn-proses {
			background: #ffc107;
			border: none;
			color: #212529;
		}

		.btn-proses:hover {
			background: #e0a800;
			color: #212529;
		}

		.btn-danger {
			background: #dc3545;
			border: none;
			color: #fff;
		}

		.btn-danger:hover {
			background: #c82333;
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

		.row-checked {
			background-color: #343a40 !important;
			color: white;
		}

		.row-checked:hover {
			background-color: #23272b !important;
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

		.status-label {
			display: inline-block;
			padding: 5px 10px;
			border-radius: 3px;
			font-size: 12px;
			font-weight: 600;
		}

		.status-info {
			background: #d1ecf1;
			color: #0c5460;
			border: 1px solid #bee5eb;
		}

		.nav-tabs .nav-link.active {
			background-color: #007bff;
			color: white;
		}

		.nav-tabs .nav-link {
			color: #495057;
		}

		.clickable-row {
			cursor: pointer;
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
					<div class="col-sm-6">
						<div class="float-right">
							<span class="status-label status-info">
								<i class="fas fa-building"></i> {{ is_array($cbg ?? '') ? implode(', ', $cbg) : $cbg ?? '-' }}
							</span>
							<span class="status-label status-info ml-2">
								<i class="fas fa-calendar"></i> {{ is_array($periode ?? '') ? implode('/', $periode) : $periode ?? '-' }}
							</span>
						</div>
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
								<!-- Tabs Navigation -->
								<ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
									<li class="nav-item">
										<a class="nav-link active" id="usulan-tab" data-toggle="tab" href="#usulan" role="tab">
											<i class="fas fa-list"></i> Usulan Hapus
										</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" id="posting-tab" data-toggle="tab" href="#posting" role="tab">
											<i class="fas fa-check-circle"></i> Posting
										</a>
									</li>
								</ul>

								<!-- Tab Content -->
								<div class="tab-content" id="myTabContent">
									<!-- Tab Usulan Hapus -->
									<div class="tab-pane fade show active" id="usulan" role="tabpanel">
										<!-- Info Box -->
										<div class="info-box">
											<p class="mb-1"><strong>Petunjuk:</strong></p>
											<ul class="mb-0">
												<li>Klik <strong>TAMPILKAN</strong> untuk update dan refresh data usulan hapus</li>
												<li>Klik pada baris data atau tekan <strong>SPACE</strong> untuk menandai item yang akan dihapus</li>
												<li>Klik <strong>PROSES</strong> untuk memproses item yang sudah ditandai</li>
												<li>Klik <strong>EXCEL</strong> untuk export data ke Excel</li>
											</ul>
										</div>

										<!-- Action Buttons -->
										<div class="mb-3">
											<button type="button" id="btnTampilkanUsulan" class="btn btn-action btn-tampilkan">
												<i class="fas fa-sync"></i> TAMPILKAN
											</button>
											<button type="button" id="btnExcelUsulan" class="btn btn-action btn-excel">
												<i class="fas fa-file-excel"></i> EXCEL
											</button>
											<button type="button" id="btnProsesUsulan" class="btn btn-action btn-proses">
												<i class="fas fa-cog"></i> PROSES
											</button>
										</div>

										<hr>

										<!-- Data Table -->
										<div class="table-responsive">
											<table class="table-striped table-bordered table-hover table" id="tableUsulan" style="width:100%">
												<thead>
													<tr>
														<th width="50px" class="text-center">No</th>
														<th width="100px">Kode Barang</th>
														<th>Nama Barang</th>
														<th width="80px">LPH</th>
														<th width="60px">KD</th>
														<th width="80px">CAT_OD</th>
														<th width="100px">TG_OD</th>
														<th width="100px">TG_TRM</th>
														<th width="100px">TG_BK</th>
														<th width="100px">TG_KS</th>
														<th width="80px" class="text-right">Hari</th>
														<th width="80px" class="text-center">Cek</th>
													</tr>
												</thead>
												<tbody>
												</tbody>
											</table>
										</div>
									</div>

									<!-- Tab Posting -->
									<div class="tab-pane fade" id="posting" role="tabpanel">
										<!-- Info Box -->
										<div class="info-box">
											<p class="mb-1"><strong>Petunjuk:</strong></p>
											<ul class="mb-0">
												<li>Klik <strong>CEK DATA</strong> untuk menampilkan data yang siap diposting</li>
												<li>Tekan <strong>DELETE</strong> pada baris untuk membatalkan hapus item tersebut</li>
												<li>Klik <strong>PROSES</strong> untuk melakukan posting hapus ke semua outlet</li>
												<li>Klik <strong>EXCEL</strong> untuk export data ke Excel</li>
											</ul>
										</div>

										<!-- Action Buttons -->
										<div class="mb-3">
											<button type="button" id="btnCekDataPosting" class="btn btn-action btn-tampilkan">
												<i class="fas fa-search"></i> CEK DATA
											</button>
											<button type="button" id="btnExcelPosting" class="btn btn-action btn-excel">
												<i class="fas fa-file-excel"></i> EXCEL
											</button>
											<button type="button" id="btnProsesPosting" class="btn btn-action btn-danger">
												<i class="fas fa-trash-alt"></i> PROSES
											</button>
										</div>

										<hr>

										<!-- Data Table -->
										<div class="table-responsive">
											<table class="table-striped table-bordered table-hover table" id="tablePosting" style="width:100%">
												<thead>
													<tr>
														<th width="50px" class="text-center">No</th>
														<th width="100px">Kode Barang</th>
														<th>Nama Barang</th>
														<th width="100px">OD</th>
														<th width="100px">TGL_OD</th>
														<th width="100px">TGL_TRM</th>
														<th width="100px">TGL_BK</th>
														<th width="100px">TGL_AT</th>
														<th width="100px">User</th>
														<th width="100px">Tgl Usulan</th>
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
		</div>
	</div>

	<div class="loader" id="LOADX"></div>
@endsection
@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
	<script>
		var tableUsulan, tablePosting;
		var currentTab = 'usulan';
		$(document).ready(function() {
			// Initialize DataTable Usulan
			tableUsulan = $('#tableUsulan').DataTable({
				ajax: {
					url: '{{ route('usulanhapusbarang_cari') }}',
					type: 'POST',
					data: function(d) {
						d._token = '{{ csrf_token() }}';
						d.tab = 'usulan';
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
								errorMsg = 'Tabel database mungkin belum ada atau terjadi kesalahan koneksi.';
							}

							Swal.fire({
								icon: 'warning',
								title: 'Perhatian',
								html: errorMsg + '<br><br><small>Silakan hubungi administrator jika masalah berlanjut.</small>',
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
						data: 'KD_BRG'
					},
					{
						data: 'NA_BRG'
					},
					{
						data: 'LPH'
					},
					{
						data: 'KD'
					},
					{
						data: 'CAT_OD'
					},
					{
						data: 'TG_ODx'
					},
					{
						data: 'TG_TRMx'
					},
					{
						data: 'TG_BKx'
					},
					{
						data: 'TG_KSx'
					},
					{
						data: 'HARI',
						className: 'text-right'
					},
					{
						data: 'TANDA_HPS',
						className: 'text-center'
					}
				],
				order: [
					[1, 'asc']
				],
				processing: true,
				pageLength: 25,
				language: {
					processing: "Memuat data...",
					lengthMenu: "Tampilkan _MENU_ data",
					zeroRecords: "Data tidak ditemukan",
					info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
					infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
					infoFiltered: "(disaring dari _MAX_ total data)",
					search: "Cari:",
					paginate: {
						first: "Pertama",
						last: "Terakhir",
						next: "Selanjutnya",
						previous: "Sebelumnya"
					}
				},
				createdRow: function(row, data, dataIndex) {
					if (data.TANDA_HPS && data.TANDA_HPS.includes('Checked')) {
						$(row).addClass('row-checked');
					}
					$(row).addClass('clickable-row');
					$(row).attr('data-kd-brg', data.KD_BRG);
					$(row).attr('data-flag', data.TANDA_HPS && data.TANDA_HPS.includes('Checked') ? '1' : '0');
				}
			});

			// Initialize DataTable Posting
			tablePosting = $('#tablePosting').DataTable({
				ajax: {
					url: '{{ route('usulanhapusbarang_cari') }}',
					type: 'POST',
					data: function(d) {
						d._token = '{{ csrf_token() }}';
						d.tab = 'posting';
					},
					error: function(xhr, error, code) {
						console.error('DataTables Ajax error:', xhr.responseText);

						if (xhr.status === 500 || xhr.status === 400) {
							var errorMsg = 'Terjadi kesalahan saat memuat data posting.';

							try {
								var response = JSON.parse(xhr.responseText);
								if (response.error) {
									errorMsg = response.error;
								}
							} catch (e) {
								errorMsg = 'Tabel database mungkin belum ada atau terjadi kesalahan koneksi.';
							}

							Swal.fire({
								icon: 'warning',
								title: 'Perhatian',
								html: errorMsg + '<br><br><small>Silakan hubungi administrator jika masalah berlanjut.</small>',
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
						data: 'KD_BRG'
					},
					{
						data: 'NA_BRG'
					},
					{
						data: 'OD'
					},
					{
						data: 'TGL_OD'
					},
					{
						data: 'TGL_TRM'
					},
					{
						data: 'TGL_BK'
					},
					{
						data: 'TGL_AT'
					},
					{
						data: 'USERX'
					},
					{
						data: 'TGL'
					}
				],
				order: [
					[1, 'asc']
				],
				processing: true,
				pageLength: 25,
				language: {
					processing: "Memuat data...",
					lengthMenu: "Tampilkan _MENU_ data",
					zeroRecords: "Data tidak ditemukan",
					info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
					infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
					infoFiltered: "(disaring dari _MAX_ total data)",
					search: "Cari:",
					paginate: {
						first: "Pertama",
						last: "Terakhir",
						next: "Selanjutnya",
						previous: "Sebelumnya"
					}
				},
				createdRow: function(row, data, dataIndex) {
					$(row).attr('data-no-id', data.NO_ID);
					$(row).attr('data-kd-brg', data.KD_BRG);
					$(row).attr('data-na-brg', data.NA_BRG);
				}
			});

			// Track current tab
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				var target = $(e.target).attr("href");
				currentTab = target === '#usulan' ? 'usulan' : 'posting';
			});

			// ============= TAB USULAN HAPUS =============

			// Button Tampilkan Usulan
			$('#btnTampilkanUsulan').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi Update',
					text: 'Update data usulan hapus?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#17a2b8',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Update!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						prosesData('tampilkan', 'Data berhasil diupdate!');
					}
				});
			});

			// Click row to toggle check
			$('#tableUsulan tbody').on('click', 'tr.clickable-row', function() {
				var kdBrg = $(this).data('kd-brg');
				var currentFlag = $(this).data('flag');

				toggleCheck(kdBrg, currentFlag, $(this));
			});

			// Space key to toggle check
			$(document).on('keydown', function(e) {
				if (e.keyCode === 32 && currentTab === 'usulan') { // Space key
					e.preventDefault();
					var $selectedRow = $('#tableUsulan tbody tr.selected');
					if ($selectedRow.length > 0) {
						var kdBrg = $selectedRow.data('kd-brg');
						var currentFlag = $selectedRow.data('flag');
						toggleCheck(kdBrg, currentFlag, $selectedRow);
					}
				}
			});

			// Row selection
			$('#tableUsulan tbody').on('click', 'tr', function() {
				if ($(this).hasClass('selected')) {
					$(this).removeClass('selected');
				} else {
					tableUsulan.$('tr.selected').removeClass('selected');
					$(this).addClass('selected');
				}
			});

			// Button Proses Usulan
			$('#btnProsesUsulan').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi Proses',
					text: 'Proses hapus barang yang sudah ditandai?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#ffc107',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						prosesData('proses_usulan', 'Data berhasil diproses!');
					}
				});
			});

			// Button Excel Usulan
			$('#btnExcelUsulan').on('click', function() {
				exportExcel('usulan');
			});

			// ============= TAB POSTING =============

			// Button Cek Data Posting
			$('#btnCekDataPosting').on('click', function() {
				prosesData('cek_data_posting', 'Data posting berhasil dimuat!', true);
			});

			// Delete key to cancel item
			$('#tablePosting tbody').on('keydown', 'tr', function(e) {
				if (e.keyCode === 46) { // Delete key
					e.preventDefault();
					var noId = $(this).data('no-id');
					var kdBrg = $(this).data('kd-brg');
					var naBrg = $(this).data('na-brg');

					batalHapus(noId, kdBrg, naBrg);
				}
			});

			// Row selection for posting
			$('#tablePosting tbody').on('click', 'tr', function() {
				if ($(this).hasClass('selected')) {
					$(this).removeClass('selected');
				} else {
					tablePosting.$('tr.selected').removeClass('selected');
					$(this).addClass('selected');
				}
			});

			// Button Proses Posting
			$('#btnProsesPosting').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi Posting',
					html: 'Lanjutkan Posting hapus barang?<br><br><strong>PERHATIAN:</strong> Data akan dihapus dari semua outlet!',
					icon: 'error',
					showCancelButton: true,
					confirmButtonColor: '#dc3545',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Posting!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						prosesData('proses_posting', 'Posting berhasil!', true);
					}
				});
			});

			// Button Excel Posting
			$('#btnExcelPosting').on('click', function() {
				exportExcel('posting');
			});
		});

		// Function to toggle check
		function toggleCheck(kdBrg, currentFlag, row) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('usulanhapusbarang_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'toggle_check',
					kd_brg: kdBrg,
					current_flag: currentFlag
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						// Update row styling
						if (response.new_flag == 1) {
							row.addClass('row-checked');
							row.attr('data-flag', '1');
						} else {
							row.removeClass('row-checked');
							row.attr('data-flag', '0');
						}

						// Reload table to update badge
						tableUsulan.ajax.reload(null, false);
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal mengupdate data'
					});
				}
			});
		}

		// Function to cancel delete
		function batalHapus(noId, kdBrg, naBrg) {
			Swal.fire({
				title: 'Batal Hapus Item?',
				html: '<strong>' + kdBrg + '</strong><br>' + naBrg,
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#17a2b8',
				cancelButtonColor: '#6c757d',
				confirmButtonText: '<i class="fas fa-check"></i> Ya, Batalkan!',
				cancelButtonText: '<i class="fas fa-times"></i> Tidak'
			}).then((result) => {
				if (result.isConfirmed) {
					$('#LOADX').show();

					$.ajax({
						url: '{{ route('usulanhapusbarang_proses') }}',
						type: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							action: 'batal_hapus',
							no_id: noId,
							kd_brg: kdBrg
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

								tablePosting.ajax.reload();
							}
						},
						error: function(xhr) {
							$('#LOADX').hide();
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: xhr.responseJSON?.error || 'Gagal membatalkan hapus'
							});
						}
					});
				}
			});
		}

		// Function to process data
		function prosesData(action, successMessage, reloadPosting = false) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('usulanhapusbarang_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: action
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						var message = successMessage;
						if (response.total) {
							message += ' Total: ' + response.total + ' item.';
						}

						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: message,
							timer: 2000,
							showConfirmButton: false
						});

						// Reload appropriate table
						if (reloadPosting) {
							tablePosting.ajax.reload();
						} else {
							tableUsulan.ajax.reload();
						}
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal memproses data'
					});
				}
			});
		}

		// Function to export Excel
		function exportExcel(tab) {
			$('#LOADX').show();

			var action = tab === 'usulan' ? 'export_excel_usulan' : 'export_excel_posting';

			$.ajax({
				url: '{{ route('usulanhapusbarang_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: action
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data.length > 0) {
						// Create worksheet
						var ws = XLSX.utils.json_to_sheet(response.data);

						// Create workbook
						var wb = XLSX.utils.book_new();
						var sheetName = tab === 'usulan' ? 'Usulan Hapus' : 'Posting Hapus';
						XLSX.utils.book_append_sheet(wb, ws, sheetName);

						// Generate filename with timestamp
						var prefix = tab === 'usulan' ? 'Usulan_Hapus_Barang_' : 'Posting_Hapus_Barang_';
						var filename = prefix + new Date().toISOString().slice(0, 10).replace(/-/g, '') + '.xlsx';

						// Save file
						XLSX.writeFile(wb, filename);

						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil di-export ke Excel!',
							timer: 1500,
							showConfirmButton: false
						});
					} else {
						Swal.fire({
							icon: 'info',
							title: 'Informasi',
							text: 'Tidak ada data untuk di-export'
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal export data'
					});
				}
			});
		}
	</script>
@endsection
