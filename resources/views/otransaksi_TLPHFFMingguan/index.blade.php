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

		.btn-proses:disabled {
			background: #6c757d;
			cursor: not-allowed;
		}

		.btn-ambil {
			background: #17a2b8;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-ambil:hover {
			background: #138496;
			color: #fff;
		}

		.btn-cetak {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-cetak:hover {
			background: #218838;
			color: #fff;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 12px;
			padding: 10px 6px;
			text-align: center;
			vertical-align: middle;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.table tbody td {
			padding: 6px;
			font-size: 12px;
			vertical-align: middle;
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

		.text-right-col {
			text-align: right !important;
		}

		.text-center-col {
			text-align: center !important;
		}

		.edit-lph {
			text-align: right;
			padding: 2px 5px;
			height: 28px;
			font-size: 12px;
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

		.form-section {
			background: #f8f9fa;
			border: 1px solid #dee2e6;
			padding: 20px;
			border-radius: 5px;
			margin-bottom: 20px;
		}

		.badge-cbg {
			font-size: 16px;
			padding: 8px 15px;
			border-radius: 5px;
		}

		.table-wrapper {
			overflow-x: auto;
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
					<div class="col-sm-6 text-right">
						@if (isset($cbg))
							<span class="badge badge-cbg badge-primary">CBG: {{ is_array($cbg) ? implode(', ', $cbg) : $cbg ?? '-' }}</span>
							<span class="badge badge-cbg badge-info">Periode: {{ is_array($periode) ? implode(', ', $periode) : $periode ?? '-' }}</span>
						@endif
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
										<li>Klik <strong>AMBIL DATA</strong> untuk mengambil data LPH FF dari sistem</li>
										<li>Edit nilai <strong>LH Hasil</strong> pada kolom yang dapat diedit sesuai kebutuhan</li>
										<li>Klik <strong>PROSES</strong> untuk menyimpan dan update data ke semua database</li>
										<li>Gunakan <strong>CETAK</strong> untuk mencetak laporan LPH FF</li>
									</ul>
								</div>

								<!-- Button Actions -->
								<div class="form-section">
									<div class="row">
										<div class="col-md-12 text-right">
											<button type="button" id="btnAmbil" class="btn btn-ambil">
												<i class="fas fa-download"></i> AMBIL DATA
											</button>
											<button type="button" id="btnCetak" class="btn btn-cetak">
												<i class="fas fa-print"></i> CETAK
											</button>
											<button type="button" id="btnProses" class="btn btn-proses">
												<i class="fas fa-cogs"></i> PROSES
											</button>
										</div>
									</div>
								</div>

								<hr>

								<!-- Data Table -->
								<div class="table-wrapper">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="40px">No</th>
												<th width="60px">Sub</th>
												<th width="80px">No Item</th>
												<th width="200px">Nama Barang</th>
												<th width="80px">Ukuran</th>
												<th width="80px">Kemasan</th>
												<th width="70px">MO</th>
												<th width="70px">TMM</th>
												<th width="70px">TGZ</th>
												<th width="70px">SOP</th>
												<th width="70px">LH Lalu</th>
												<th width="90px">LH Usul</th>
												<th width="90px">LH Hasil</th>
												<th width="80px">Laku Kasir</th>
												<th width="70px">TS_TGZ</th>
												<th width="70px">TS_SOP</th>
												<th width="70px">TS_TMM</th>
												<th width="80px">Jam Kosong</th>
												<th width="120px">Keterangan</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td colspan="19" class="text-center">Klik tombol AMBIL DATA untuk menampilkan data</td>
											</tr>
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
		var tableData = [];
		var CBG = {!! json_encode($cbg ?? '') !!};

		$(document).ready(function() {
			// Initialize empty table
			initTable();

			// Button Ambil Data
			$('#btnAmbil').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi Ambil Data',
					html: 'Proses akan mengambil data LPH FF dari sistem.<br><small class="text-warning">Pastikan data belum diambil hari ini.</small>',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#17a2b8',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Ambil!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						return ambilData();
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					if (result.isConfirmed && result.value.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: result.value.message,
							timer: 2000,
							showConfirmButton: false
						}).then(() => {
							loadData();
						});
					}
				});
			});

			// Button Proses
			$('#btnProses').on('click', function() {
				var items = [];

				// Ambil semua data dari table
				table.rows().every(function() {
					var data = this.data();
					items.push(data);
				});

				if (items.length === 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Tidak ada data untuk diproses!'
					});
					return;
				}

				Swal.fire({
					title: 'Konfirmasi Proses',
					html: 'Proses akan mengupdate data ke semua database.<br><small class="text-warning">Data yang diproses tidak dapat dibatalkan.</small>',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#007bff',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						return prosesData(items);
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					if (result.isConfirmed && result.value.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							html: result.value.message,
							showConfirmButton: true
						});
					}
				});
			});

			// Button Cetak
			$('#btnCetak').on('click', function() {
				cetakData();
			});

			// Handle edit LPH
			$(document).on('change', '.edit-lph', function() {
				var kd_brg = $(this).data('kd');
				var field = $(this).data('field');
				var newValue = parseFloat($(this).val().replace(/,/g, '')) || 0;

				// Update nilai di tableData
				var rowData = table.rows().data().toArray();
				for (var i = 0; i < rowData.length; i++) {
					if (rowData[i].kd_brg == kd_brg) {
						rowData[i][field] = newValue;
						break;
					}
				}
			});
		});

		function initTable() {
			table = $('#tableData').DataTable({
				data: [],
				columns: [{
						data: null,
						render: function(data, type, row, meta) {
							return meta.row + 1;
						},
						className: 'text-center'
					},
					{
						data: 'sub',
						className: 'text-center'
					},
					{
						data: 'kdbar',
						className: 'text-center'
					},
					{
						data: 'na_brg'
					},
					{
						data: 'ket_uk',
						className: 'text-center'
					},
					{
						data: 'ket_kem',
						className: 'text-center'
					},
					{
						data: 'mo',
						className: 'text-right'
					},
					{
						data: 'jl_tmm',
						className: 'text-right'
					},
					{
						data: 'jl_gz',
						className: 'text-right'
					},
					{
						data: 'jl_kg',
						className: 'text-right'
					},
					{
						data: null,
						render: function(data, type, row) {
							if (CBG == 'TMM') return formatNumber(row.ll_tmm, 2);
							if (CBG == 'SOP') return formatNumber(row.ll_kg, 2);
							return formatNumber(row.ll_gz, 2);
						},
						className: 'text-right'
					},
					{
						data: null,
						render: function(data, type, row) {
							if (CBG == 'TMM') return formatNumber(row.jl_tmm, 2);
							if (CBG == 'SOP') return formatNumber(row.jl_kg, 2);
							return formatNumber(row.jl_gz, 2);
						},
						className: 'text-right'
					},
					{
						data: null,
						render: function(data, type, row) {
							if (CBG == 'TMM') return row.lph_tmm_edit;
							if (CBG == 'SOP') return row.lph_kg_edit;
							return row.lph_gz_edit;
						},
						className: 'text-right'
					},
					{
						data: 'laku_kasir',
						className: 'text-right'
					},
					{
						data: 'ts_gz',
						className: 'text-right'
					},
					{
						data: 'ts_kg',
						className: 'text-right'
					},
					{
						data: 'ts_mm',
						className: 'text-right'
					},
					{
						data: 'jam_kosong',
						className: 'text-center'
					},
					{
						data: 'keterangan'
					}
				],
				paging: true,
				pageLength: 50,
				searching: true,
				ordering: true,
				info: true,
				scrollX: true,
				fixedColumns: {
					left: 4
				}
			});
		}

		function loadData() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('lphffmingguan_cari') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.data && response.data.length > 0) {
						tableData = response.data;

						// Add edit fields
						response.data.forEach(function(item) {
							item.lph_tmm_edit =
								'<input type="text" class="form-control form-control-sm text-right edit-lph" data-field="lph_tmm" data-kd="' +
								item.kd_brg + '" value="' + formatNumber(item.lph_tmm, 2, '.', '') + '">';
							item.lph_gz_edit =
								'<input type="text" class="form-control form-control-sm text-right edit-lph" data-field="lph_gz" data-kd="' +
								item.kd_brg + '" value="' + formatNumber(item.lph_gz, 2, '.', '') + '">';
							item.lph_kg_edit =
								'<input type="text" class="form-control form-control-sm text-right edit-lph" data-field="lph_kg" data-kd="' +
								item.kd_brg + '" value="' + formatNumber(item.lph_kg, 2, '.', '') + '">';
						});

						table.clear();
						table.rows.add(response.data);
						table.draw();

						Swal.fire({
							icon: 'success',
							title: 'Data Dimuat',
							text: response.data.length + ' item berhasil dimuat',
							timer: 1500,
							showConfirmButton: false
						});
					} else {
						table.clear().draw();
						Swal.fire({
							icon: 'info',
							title: 'Informasi',
							text: 'Tidak ada data. Silakan AMBIL DATA terlebih dahulu.'
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					table.clear().draw();

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal memuat data'
					});
				}
			});
		}

		function ambilData() {
			return $.ajax({
				url: '{{ route('lphffmingguan') }}/ambil-data',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}'
				}
			}).then(function(response) {
				return response;
			}).catch(function(xhr) {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: xhr.responseJSON?.error || 'Gagal mengambil data'
				});
				throw new Error(xhr.responseJSON?.error || 'Gagal mengambil data');
			});
		}

		function prosesData(items) {
			$('#btnProses').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');
			$('#LOADX').show();

			return $.ajax({
				url: '{{ route('lphffmingguan_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					items: items
				}
			}).then(function(response) {
				$('#LOADX').hide();
				$('#btnProses').prop('disabled', false).html('<i class="fas fa-cogs"></i> PROSES');
				return response;
			}).catch(function(xhr) {
				$('#LOADX').hide();
				$('#btnProses').prop('disabled', false).html('<i class="fas fa-cogs"></i> PROSES');

				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: xhr.responseJSON?.error || 'Proses gagal'
				});

				throw new Error(xhr.responseJSON?.error || 'Proses gagal');
			});
		}

		function cetakData() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('lphffmingguan_detail', '') }}/print',
				type: 'GET',
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data.length > 0) {
						var printWindow = window.open('', '', 'height=600,width=1200');
						printWindow.document.write('<html><head><title>Laporan LPH FF Mingguan</title>');
						printWindow.document.write('<style>');
						printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
						printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10px; }');
						printWindow.document.write('th, td { border: 1px solid #000; padding: 4px; }');
						printWindow.document.write('th { background-color: #343a40; color: white; text-align: center; }');
						printWindow.document.write('.text-right { text-align: right; }');
						printWindow.document.write('.text-center { text-align: center; }');
						printWindow.document.write('h2 { text-align: center; margin-bottom: 5px; }');
						printWindow.document.write('h3 { text-align: center; margin-top: 0; }');
						printWindow.document.write('</style>');
						printWindow.document.write('</head><body>');

						printWindow.document.write('<h2>Laporan LPH Fresh Food Mingguan</h2>');
						printWindow.document.write('<h3>CBG: ' + response.cbg + ' | Tanggal: ' + response.tanggal + '</h3>');

						printWindow.document.write('<table>');
						printWindow.document.write('<thead><tr>');
						printWindow.document.write('<th>No</th>');
						printWindow.document.write('<th>Sub</th>');
						printWindow.document.write('<th>No Item</th>');
						printWindow.document.write('<th>Nama Barang</th>');
						printWindow.document.write('<th>Ukuran</th>');
						printWindow.document.write('<th>Kemasan</th>');
						printWindow.document.write('<th>MO</th>');
						printWindow.document.write('<th>LH Hasil</th>');
						printWindow.document.write('<th>Laku Kasir</th>');
						printWindow.document.write('<th>Jam Kosong</th>');
						printWindow.document.write('</tr></thead><tbody>');

						response.data.forEach(function(row, index) {
							var lh_hasil = CBG == 'TMM' ? row.lph_tmm : (CBG == 'SOP' ? row.lph_kg : row.lph_gz);

							printWindow.document.write('<tr>');
							printWindow.document.write('<td class="text-center">' + (index + 1) + '</td>');
							printWindow.document.write('<td class="text-center">' + row.kd_brg.substring(0, 3) + '</td>');
							printWindow.document.write('<td class="text-center">' + row.kd_brg.substring(3) + '</td>');
							printWindow.document.write('<td>' + row.na_brg + '</td>');
							printWindow.document.write('<td class="text-center">' + (row.ket_uk || '-') + '</td>');
							printWindow.document.write('<td class="text-center">' + (row.ket_kem || '-') + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.mo, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(lh_hasil, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.laku_kasir, 2) + '</td>');
							printWindow.document.write('<td class="text-center">' + (row.jam_kosong || '-') + '</td>');
							printWindow.document.write('</tr>');
						});

						printWindow.document.write('</tbody></table>');
						printWindow.document.write('</body></html>');
						printWindow.document.close();

						setTimeout(function() {
							printWindow.print();
						}, 250);
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal mencetak data'
					});
				}
			});
		}

		function formatNumber(num, decimals, decPoint, thousandsSep) {
			decPoint = decPoint || ',';
			thousandsSep = thousandsSep || '.';

			var n = parseFloat(num);
			if (isNaN(n)) return '0';

			return n.toLocaleString('id-ID', {
				minimumFractionDigits: decimals,
				maximumFractionDigits: decimals
			}).replace(/\./g, 'TEMP').replace(/,/g, decPoint).replace(/TEMP/g, thousandsSep);
		}

		// Auto load data on page load
		$(document).ready(function() {
			setTimeout(function() {
				loadData();
			}, 500);
		});
	</script>
@endsection
