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

		.btn-simpan {
			background: #007bff;
			border: none;
			color: #fff;
		}

		.btn-simpan:hover {
			background: #0056b3;
			color: #fff;
		}

		.btn-catatan {
			background: #6c757d;
			border: none;
			color: #fff;
		}

		.btn-catatan:hover {
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
								<i class="fas fa-building"></i> {{ is_array($cbg) ? implode(', ', $cbg) : $cbg ?? '-' }}
							</span>
							<span class="status-label status-info ml-2">
								<i class="fas fa-calendar"></i> {{ is_array($periode) ? implode(', ', $periode) : $periode ?? '-' }}
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
								<!-- Info Box -->
								<div class="info-box">
									<p class="mb-1"><strong>Petunjuk:</strong></p>
									<ul class="mb-0">
										<li>Klik <strong>TAMPILKAN</strong> untuk menampilkan dan refresh data</li>
										<li>Klik <strong>PROSES</strong> untuk menghitung harga beli dan harga jual otomatis</li>
										<li>Klik <strong>PROSES CATATAN</strong> untuk mengisi kolom alasan/notes dari histori</li>
										<li>Klik <strong>SIMPAN</strong> untuk menyimpan data ke histori transaksi</li>
										<li>Klik <strong>EXCEL</strong> untuk export data ke Excel</li>
									</ul>
								</div>

								<!-- Action Buttons -->
								<div class="mb-3">
									<button type="button" id="btnTampilkan" class="btn btn-action btn-tampilkan">
										<i class="fas fa-sync"></i> TAMPILKAN
									</button>
									<button type="button" id="btnExcel" class="btn btn-action btn-excel">
										<i class="fas fa-file-excel"></i> EXCEL
									</button>
									<button type="button" id="btnProses" class="btn btn-action btn-proses">
										<i class="fas fa-calculator"></i> PROSES
									</button>
									<button type="button" id="btnSimpan" class="btn btn-action btn-simpan">
										<i class="fas fa-save"></i> SIMPAN
									</button>
									<button type="button" id="btnProsesCatatan" class="btn btn-action btn-catatan">
										<i class="fas fa-sticky-note"></i> PROSES CATATAN
									</button>
								</div>

								<hr>

								<!-- Data Table -->
								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">No</th>
												<th width="100px">Sub Item</th>
												<th>Nama Barang</th>
												<th width="100px" class="text-right">HRG Usul</th>
												<th width="80px" class="text-right">D1</th>
												<th width="80px" class="text-right">D2</th>
												<th width="80px" class="text-right">D3</th>
												<th width="80px" class="text-right">PPN</th>
												<th width="80px" class="text-right">MRG</th>
												<th width="100px">Supplier</th>
												<th width="100px" class="text-right">Harga Lalu</th>
												<th width="100px" class="text-right">Harga Jual</th>
												<th width="150px">Alasan</th>
												<th width="150px">Notes</th>
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
	<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
	<script>
		var table;

		$(document).ready(function() {
			// Initialize DataTable
			table = $('#tableData').DataTable({
				ajax: {
					url: '{{ route('betebete_cari') }}',
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}'
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
						data: 'SUB'
					},
					{
						data: 'NA_BRG'
					},
					{
						data: 'HJUSUL',
						className: 'text-right'
					},
					{
						data: 'D1',
						className: 'text-right'
					},
					{
						data: 'D2',
						className: 'text-right'
					},
					{
						data: 'D3',
						className: 'text-right'
					},
					{
						data: 'PPN',
						className: 'text-right'
					},
					{
						data: 'MRG',
						className: 'text-right'
					},
					{
						data: 'SUPP'
					},
					{
						data: 'HRG_LALU',
						className: 'text-right'
					},
					{
						data: 'HRG_JUAL',
						className: 'text-right'
					},
					{
						data: 'ALASAN'
					},
					{
						data: 'NOTES'
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
				}
			});

			// Button Tampilkan
			$('#btnTampilkan').on('click', function() {
				prosesData('tampilkan', 'Data berhasil ditampilkan!');
			});

			// Button Proses
			$('#btnProses').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi Proses',
					text: 'Apakah yakin ingin melakukan proses hitung harga?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#ffc107',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						prosesData('proses', 'Proses hitung selesai!');
					}
				});
			});

			// Button Simpan
			$('#btnSimpan').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi Simpan',
					text: 'Apakah yakin ingin menyimpan data ke histori?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#007bff',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Simpan!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						prosesData('simpan', 'Data berhasil disimpan!');
					}
				});
			});

			// Button Proses Catatan
			$('#btnProsesCatatan').on('click', function() {
				prosesData('proses_catatan', 'Proses catatan selesai!');
			});

			// Button Excel
			$('#btnExcel').on('click', function() {
				exportExcel();
			});
		});

		function prosesData(action, successMessage) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('betebete_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: action
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: successMessage,
							timer: 2000,
							showConfirmButton: false
						});

						// Reload table
						table.ajax.reload();
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

		function exportExcel() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('betebete_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'export_excel'
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data.length > 0) {
						// Create worksheet
						var ws = XLSX.utils.json_to_sheet(response.data);

						// Set column widths
						ws['!cols'] = [{
								wch: 12
							}, // Sub Item
							{
								wch: 40
							}, // Nama Barang
							{
								wch: 12
							}, // HRG Usul
							{
								wch: 8
							}, // D1
							{
								wch: 8
							}, // D2
							{
								wch: 8
							}, // D3
							{
								wch: 8
							}, // PPN
							{
								wch: 8
							}, // MRG
							{
								wch: 15
							}, // Supplier
							{
								wch: 12
							}, // Harga Lalu
							{
								wch: 12
							}, // Harga Jual
							{
								wch: 25
							}, // Alasan
							{
								wch: 25
							} // Notes
						];

						// Create workbook
						var wb = XLSX.utils.book_new();
						XLSX.utils.book_append_sheet(wb, ws, "Bete Bete");

						// Generate filename with timestamp
						var filename = 'Bete_Bete_' + new Date().toISOString().slice(0, 10) + '.xlsx';

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
