@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
	<style>
		.card {
			padding: 15px;
		}

		.form-control:focus {
			background-color: #b5e5f9;
		}

		.btn-tampilkan {
			background: #007bff;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-tampilkan:hover {
			background: #0056b3;
			color: #fff;
		}

		.btn-excel {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-excel:hover {
			background: #218838;
			color: #fff;
		}

		.form-group label {
			font-weight: 600;
			margin-bottom: 8px;
			color: #333;
		}

		.search-section {
			background: #f8f9fa;
			padding: 20px;
			border-radius: 5px;
			margin-bottom: 20px;
			border: 1px solid #dee2e6;
		}

		.table-section {
			background: #fff;
			padding: 20px;
			border-radius: 5px;
			border: 1px solid #dee2e6;
			margin-top: 20px;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 13px;
			text-align: center;
			vertical-align: middle;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.table tbody td {
			font-size: 13px;
			vertical-align: middle;
		}

		.info-box {
			background: #e7f3ff;
			padding: 15px;
			border-radius: 5px;
			border-left: 4px solid #007bff;
			margin-bottom: 20px;
		}

		.summary-box {
			background: #f0f8ff;
			padding: 15px;
			border-radius: 5px;
			border: 1px solid #007bff;
			margin-top: 20px;
		}

		.summary-box .row {
			margin-bottom: 10px;
		}

		.summary-box label {
			font-weight: 600;
			color: #007bff;
		}

		.summary-box .value {
			font-size: 18px;
			font-weight: 700;
			color: #333;
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
			text-align: right;
		}

		.text-center {
			text-align: center;
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
								<div class="info-box">
									<i class="fas fa-info-circle"></i>
									<strong>Informasi:</strong> Pilih tanggal survey dan masukkan nomor survey untuk menampilkan data. Klik tombol Tampilkan untuk memuat data
									survey penjualan.
								</div>

								<!-- Form Pencarian -->
								<div class="search-section">
									<form id="formCari">
										<div class="row">
											<div class="col-md-3">
												<div class="form-group">
													<label for="tanggal">
														<i class="fas fa-calendar"></i> Tanggal Survey
													</label>
													<input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ $today ?? '' }}">
												</div>
											</div>
											<div class="col-md-5">
												<div class="form-group">
													<label for="no_survey">
														<i class="fas fa-file-alt"></i> No Survey
													</label>
													<input type="text" class="form-control" id="no_survey" name="no_survey" placeholder="Masukkan nomor survey"
														value="{{ $no_survey ?? '' }}" autocomplete="off">
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<label>&nbsp;</label>
													<button type="submit" class="btn btn-tampilkan btn-block">
														<i class="fas fa-search"></i> TAMPILKAN
													</button>
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<label>&nbsp;</label>
													<button type="button" id="btnExcel" class="btn btn-excel btn-block" disabled>
														<i class="fas fa-file-excel"></i> EXCEL
													</button>
												</div>
											</div>
										</div>
									</form>
								</div>

								<!-- Tabel Data Survey -->
								<div class="table-section" id="tableSection" style="display: none;">
									<h5><i class="fas fa-table"></i> Data Survey Penjualan</h5>
									<div class="table-responsive mt-3">
										<table class="table-bordered table-striped table" id="tableSurvey">
											<thead>
												<tr>
													<th width="50px">No</th>
													<th width="100px">Sub Item</th>
													<th>Nama Barang</th>
													<th width="80px">KD</th>
													<th width="80px">LPH</th>
													<th width="80px">Lose Sale</th>
													<th width="120px">Barcode</th>
													<th width="100px">Kemasan</th>
													<th width="100px">HB</th>
													<th width="100px">HJ</th>
													<th width="80px">Import</th>
													<th width="100px">HJ MAX</th>
													<th>Keterangan</th>
												</tr>
											</thead>
											<tbody id="tableSurveyBody">
												<tr>
													<td colspan="13" class="text-center">Tidak ada data</td>
												</tr>
											</tbody>
										</table>
									</div>

									<!-- Summary -->
									<div class="summary-box">
										<div class="row">
											<div class="col-md-3">
												<label>Jumlah Item:</label>
												<div class="value" id="jumlah_item">0</div>
											</div>
											<div class="col-md-3">
												<label>Total Qty:</label>
												<div class="value" id="total_qty">0</div>
											</div>
											<div class="col-md-6">
												<label>Total Amount:</label>
												<div class="value" id="total_amount">Rp 0</div>
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
	</div>

	<div class="loader" id="LOADX"></div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
	<script>
		var dataTable;
		var currentData = [];

		$(document).ready(function() {
			// Handle tanggal change - auto update no_survey
			$('#tanggal').on('change', function() {
				updateNoSurvey();
			});

			// Handle form submit
			$('#formCari').on('submit', function(e) {
				e.preventDefault();
				cariData();
			});

			// Handle Excel export
			$('#btnExcel').on('click', function() {
				exportToExcel();
			});
		});

		function updateNoSurvey() {
			var tanggal = $('#tanggal').val();

			if (tanggal === '') {
				return;
			}

			$('#LOADX').show();

			$.ajax({
				url: "{{ route('tambildatasurveypenjualan') }}",
				type: 'GET',
				data: {
					tanggal: tanggal
				},
				success: function(response) {
					$('#LOADX').hide();
					// Response akan berisi HTML, kita perlu extract no_survey dari response
					// Untuk sederhananya, kita bisa menggunakan endpoint terpisah atau
					// menggunakan cara lain untuk update no_survey secara dinamis
				},
				error: function(xhr) {
					$('#LOADX').hide();
				}
			});
		}

		function cariData() {
			var tanggal = $.trim($('#tanggal').val());
			var no_survey = $.trim($('#no_survey').val());

			if (tanggal === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Tanggal survey harus diisi!'
				});
				$('#tanggal').focus();
				return;
			}

			if (no_survey === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'No survey harus diisi!'
				});
				$('#no_survey').focus();
				return;
			}

			$('#LOADX').show();

			$.ajax({
				url: "{{ route('tambildatasurveypenjualan_cari') }}",
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					tanggal: tanggal,
					no_survey: no_survey
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						currentData = response.data;
						tampilkanData(response.data, response.summary);

						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 1500,
							showConfirmButton: false
						});

						// Enable Excel button
						$('#btnExcel').prop('disabled', false);
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					var errorMsg = 'Terjadi kesalahan';

					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMsg = xhr.responseJSON.error;
					}

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMsg
					});

					// Hide table and disable Excel button
					$('#tableSection').hide();
					$('#btnExcel').prop('disabled', true);
					currentData = [];
				}
			});
		}

		function tampilkanData(data, summary) {
			// Destroy existing datatable if exists
			if (dataTable) {
				dataTable.destroy();
			}

			var tbody = '';
			if (data.length > 0) {
				$.each(data, function(index, item) {
					tbody += '<tr>';
					tbody += '<td class="text-center">' + item.rec + '</td>';
					tbody += '<td>' + (item.kdlaku || '-') + '</td>';
					tbody += '<td>' + item.na_brg + '</td>';
					tbody += '<td>' + item.kd_brg + '</td>';
					tbody += '<td class="text-right">' + formatNumber(item.lph || 0) + '</td>';
					tbody += '<td class="text-right">' + formatNumber(item.sale || 0) + '</td>';
					tbody += '<td>' + (item.barcode || '-') + '</td>';
					tbody += '<td>' + (item.ket_kem || '-') + '</td>';
					tbody += '<td class="text-right">' + formatRupiah(item.hb || 0) + '</td>';
					tbody += '<td class="text-right">' + formatRupiah(item.hj || 0) + '</td>';
					tbody += '<td class="text-center">' + (item.import || '-') + '</td>';
					tbody += '<td class="text-right">' + formatRupiah(item.hj_max || 0) + '</td>';
					tbody += '<td>' + formatNumber(item.jml_ord || 0) + '</td>';
					tbody += '</tr>';
				});
			} else {
				tbody = '<tr><td colspan="13" class="text-center">Tidak ada data</td></tr>';
			}

			$('#tableSurveyBody').html(tbody);

			// Update summary
			$('#jumlah_item').text(summary.jumlah_item || 0);
			$('#total_qty').text(formatNumber(summary.total_qty || 0));
			$('#total_amount').text(formatRupiah(summary.total_amount || 0));

			// Show table section
			$('#tableSection').slideDown();

			// Initialize datatable
			if (data.length > 0) {
				dataTable = $('#tableSurvey').DataTable({
					"paging": true,
					"lengthChange": true,
					"searching": true,
					"ordering": true,
					"info": true,
					"autoWidth": false,
					"responsive": true,
					"pageLength": 25,
					"language": {
						"url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
					}
				});
			}
		}

		function exportToExcel() {
			if (currentData.length === 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Tidak ada data untuk diekspor!'
				});
				return;
			}

			try {
				// Prepare data for Excel
				var excelData = [];

				// Add header
				excelData.push([
					'No',
					'Sub Item',
					'Nama Barang',
					'KD',
					'LPH',
					'Lose Sale',
					'Barcode',
					'Kemasan',
					'HB',
					'HJ',
					'Import',
					'HJ MAX',
					'Keterangan'
				]);

				// Add data rows
				$.each(currentData, function(index, item) {
					excelData.push([
						item.rec,
						item.kdlaku || '-',
						item.na_brg,
						item.kd_brg,
						item.lph || 0,
						item.sale || 0,
						item.barcode || '-',
						item.ket_kem || '-',
						item.hb || 0,
						item.hj || 0,
						item.import || '-',
						item.hj_max || 0,
						item.jml_ord || 0
					]);
				});

				// Create workbook and worksheet
				var wb = XLSX.utils.book_new();
				var ws = XLSX.utils.aoa_to_sheet(excelData);

				// Set column widths
				ws['!cols'] = [{
						wch: 5
					}, // No
					{
						wch: 12
					}, // Sub Item
					{
						wch: 35
					}, // Nama Barang
					{
						wch: 12
					}, // KD
					{
						wch: 10
					}, // LPH
					{
						wch: 12
					}, // Lose Sale
					{
						wch: 15
					}, // Barcode
					{
						wch: 12
					}, // Kemasan
					{
						wch: 12
					}, // HB
					{
						wch: 12
					}, // HJ
					{
						wch: 8
					}, // Import
					{
						wch: 12
					}, // HJ MAX
					{
						wch: 12
					} // Keterangan
				];

				// Add worksheet to workbook
				XLSX.utils.book_append_sheet(wb, ws, "Survey Penjualan");

				// Generate filename
				var tanggal = $('#tanggal').val().replace(/-/g, '');
				var no_survey = $('#no_survey').val().replace(/[^a-zA-Z0-9]/g, '');
				var filename = 'Survey_Penjualan_' + no_survey + '_' + tanggal + '.xlsx';

				// Save file
				XLSX.writeFile(wb, filename);

				Swal.fire({
					icon: 'success',
					title: 'Berhasil',
					text: 'Data berhasil diekspor ke Excel!',
					timer: 1500,
					showConfirmButton: false
				});
			} catch (error) {
				console.error('Export error:', error);
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Gagal mengekspor data: ' + error.message
				});
			}
		}

		function formatRupiah(angka) {
			return 'Rp ' + formatNumber(angka);
		}

		function formatNumber(angka) {
			return parseFloat(angka).toLocaleString('id-ID', {
				minimumFractionDigits: 0,
				maximumFractionDigits: 2
			});
		}
	</script>
@endsection
