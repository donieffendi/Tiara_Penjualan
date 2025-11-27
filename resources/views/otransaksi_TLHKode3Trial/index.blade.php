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

		.btn-ambil {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-ambil:hover {
			background: #218838;
			color: #fff;
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

		.btn-cetak {
			background: #17a2b8;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-cetak:hover {
			background: #138496;
			color: #fff;
		}

		.btn-proses:disabled,
		.btn-ambil:disabled,
		.btn-cetak:disabled {
			background: #6c757d;
			cursor: not-allowed;
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
			background: radial-gradient(farthest-side, #ffa516 90%, #0000) center/16px 16px, radial-gradient(farthest-side, green 90%, #0000) bottom/12px 12px;
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

		.highlight-edited {
			background-color: #fff3cd !important;
		}

		.col-sub {
			width: 60px;
		}

		.col-noitem {
			width: 80px;
		}

		.col-ukuran {
			width: 100px;
		}

		.col-kemasan {
			width: 100px;
		}

		.col-mo {
			width: 60px;
			text-align: center;
		}

		.col-number {
			width: 90px;
			text-align: right;
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
							<span class="badge badge-info" style="font-size: 14px; padding: 8px 12px;">
								CBG: <strong>{{ $cbg ?? '-' }}</strong> | Periode: <strong>{{ $periode ?? '-' }}</strong>
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
								<div class="info-box">
									<p class="mb-1"><strong>Petunjuk Penggunaan:</strong></p>
									<ul class="mb-0">
										<li><strong>AMBIL DATA:</strong> Generate data LH dari penjualan, penerimaan, dan musnah semua cabang</li>
										<li><strong>Edit Kolom:</strong> Ubah nilai LH Usul (TMM, TGZ, SOP) sesuai kebutuhan</li>
										<li><strong>PROSES:</strong> Update data LH ke database cabang (brgdt dan brg)</li>
										<li><strong>CETAK:</strong> Cetak laporan data LH Kode 3</li>
									</ul>
								</div>

								<div class="row mb-3">
									<div class="col-md-12">
										<button type="button" id="btnAmbilData" class="btn btn-ambil">
											<i class="fas fa-download"></i> AMBIL DATA
										</button>
										<button type="button" id="btnProses" class="btn btn-proses" style="display:none;">
											<i class="fas fa-cogs"></i> PROSES
										</button>
										<button type="button" id="btnCetak" class="btn btn-cetak">
											<i class="fas fa-print"></i> CETAK
										</button>
										<button type="button" id="btnRefresh" class="btn btn-secondary">
											<i class="fas fa-sync-alt"></i> REFRESH
										</button>
									</div>
								</div>

								<hr>

								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%; font-size:12px;">
										<thead>
											<tr>
												<th class="col-sub">Sub</th>
												<th class="col-noitem">Item</th>
												<th>Nama Barang</th>
												<th class="col-ukuran">Ukuran</th>
												<th class="col-kemasan">Kemasan</th>
												<th class="col-mo">MO</th>
												<th class="col-number">TMM</th>
												<th class="col-number">TGZ</th>
												<th class="col-number">SOP</th>
												<th class="col-number">LH Lalu TMM</th>
												<th class="col-number">LH Lalu TGZ</th>
												<th class="col-number">LH Lalu SOP</th>
												<th class="col-number">LH Usul TMM</th>
												<th class="col-number">LH Usul TGZ</th>
												<th class="col-number">LH Usul SOP</th>
												<th class="col-number">Laku Kasir TMM</th>
												<th class="col-number">Laku Kasir TGZ</th>
												<th class="col-number">Laku Kasir SOP</th>
												<th class="col-number">TS TMM</th>
												<th class="col-number">TS TGZ</th>
												<th class="col-number">TS SOP</th>
												<th>Keterangan</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td colspan="22" class="text-center">Klik tombol <strong>AMBIL DATA</strong> atau <strong>REFRESH</strong> untuk menampilkan data</td>
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
		var table, tableData = [],
			editedData = {};

		$(document).ready(function() {
			initTable();
			loadData();

			$('#btnAmbilData').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi Ambil Data',
					html: '<strong>Proses ini akan:</strong><br><ul style="text-align:left; padding-left:20px;"><li>Generate data LH dari semua cabang</li><li>Menghitung berdasarkan penjualan, penerimaan, dan musnah</li><li>Data hari ini akan di-reset jika sudah ada</li></ul><small class="text-warning">Proses membutuhkan waktu beberapa saat...</small>',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#28a745',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Ambil Data!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => ambilData(),
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					if (result.isConfirmed && result.value.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: result.value.message
						}).then(() => loadData());
					}
				});
			});

			$('#btnProses').on('click', function() {
				if (Object.keys(editedData).length === 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Tidak ada perubahan data untuk diproses!'
					});
					return;
				}

				Swal.fire({
					title: 'Konfirmasi Proses',
					html: '<strong>Proses akan:</strong><br><ul style="text-align:left; padding-left:20px;"><li>Update data LH ke database brgdt (semua cabang)</li><li>Update data LH ke tabel brg (TGZ, TMM, SOP)</li><li>Recalculate DTR2 berdasarkan LH baru</li></ul><small class="text-danger">Data tidak dapat dibatalkan!</small>',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#007bff',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => prosesData(),
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					if (result.isConfirmed && result.value.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							html: result.value.message
						}).then(() => {
							editedData = {};
							loadData();
						});
					}
				});
			});

			$('#btnCetak').on('click', cetakLaporan);
			$('#btnRefresh').on('click', loadData);

			$(document).on('change', '.edit-lph', function() {
				var field = $(this).data('field');
				var kdBrg = $(this).data('kd');
				var newValue = parseFloat($(this).val().replace(/,/g, ''));

				if (isNaN(newValue) || newValue < 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Nilai harus berupa angka positif!',
						timer: 2000
					});
					$(this).val('0.00');
					return;
				}

				if (!editedData[kdBrg]) editedData[kdBrg] = {};
				editedData[kdBrg][field] = newValue;
				editedData[kdBrg]['KD_BRG'] = kdBrg;

				$(this).closest('tr').addClass('highlight-edited');
				$('#btnProses').show();
			});

			$(document).on('blur', '.edit-lph', function() {
				var value = parseFloat($(this).val().replace(/,/g, ''));
				if (!isNaN(value)) $(this).val(formatNumber(value, 2, '.', ''));
			});
		});

		function initTable() {
			table = $('#tableData').DataTable({
				data: [],
				columns: [{
						data: 'SUB'
					},
					{
						data: 'KDBAR'
					},
					{
						data: 'NA_BRG'
					},
					{
						data: 'KET_UK'
					},
					{
						data: 'KET_KEM'
					},
					{
						data: 'MO',
						className: 'text-center'
					},
					{
						data: 'LPH_TMM',
						className: 'text-right',
						render: (data) => formatNumber(data || 0, 2)
					},
					{
						data: 'LPH_GZ',
						className: 'text-right',
						render: (data) => formatNumber(data || 0, 2)
					},
					{
						data: 'LPH_KG',
						className: 'text-right',
						render: (data) => formatNumber(data || 0, 2)
					},
					{
						data: 'LPH_TMM_LL',
						className: 'text-right',
						render: (data) => formatNumber(data || 0, 2)
					},
					{
						data: 'LPH_GZ_LL',
						className: 'text-right',
						render: (data) => formatNumber(data || 0, 2)
					},
					{
						data: 'LPH_KG_LL',
						className: 'text-right',
						render: (data) => formatNumber(data || 0, 2)
					},
					{
						data: 'LPHTMM',
						className: 'text-right',
						render: (data, type, row) => '<input type="text" class="form-control edit-lph" data-field="LPHTMM" data-kd="' + row
							.KD_BRG + '" value="' + formatNumber(data || 0, 2, '.', '') + '">'
					},
					{
						data: 'LPHGZ',
						className: 'text-right',
						render: (data, type, row) => '<input type="text" class="form-control edit-lph" data-field="LPHGZ" data-kd="' + row
							.KD_BRG + '" value="' + formatNumber(data || 0, 2, '.', '') + '">'
					},
					{
						data: 'LPHKG',
						className: 'text-right',
						render: (data, type, row) => '<input type="text" class="form-control edit-lph" data-field="LPHKG" data-kd="' + row
							.KD_BRG + '" value="' + formatNumber(data || 0, 2, '.', '') + '">'
					},
					{
						data: 'JLMM',
						className: 'text-right',
						render: (data) => formatNumber(data || 0, 2)
					},
					{
						data: 'JLGZ',
						className: 'text-right',
						render: (data) => formatNumber(data || 0, 2)
					},
					{
						data: 'JLKG',
						className: 'text-right',
						render: (data) => formatNumber(data || 0, 2)
					},
					{
						data: 'TS_MM',
						className: 'text-right',
						render: (data) => formatNumber(data || 0, 2)
					},
					{
						data: 'TS_GZ',
						className: 'text-right',
						render: (data) => formatNumber(data || 0, 2)
					},
					{
						data: 'TS_KG',
						className: 'text-right',
						render: (data) => formatNumber(data || 0, 2)
					},
					{
						data: 'KETERANGAN'
					}
				],
				pageLength: 25,
				lengthMenu: [
					[10, 25, 50, 100, -1],
					[10, 25, 50, 100, "Semua"]
				],
				order: [
					[0, 'asc'],
					[1, 'asc']
				],
				scrollX: true,
				language: {
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
					}
				}
			});
		}

		function loadData() {
			$('#LOADX').show();
			$('#btnProses').hide();
			editedData = {};

			$.ajax({
				url: '{{ route('lhkkode3trial_cari') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					$('#LOADX').hide();
					if (response.data && response.data.length > 0) {
						tableData = response.data;
						table.clear().rows.add(response.data).draw();
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
							text: 'Tidak ada data. Klik AMBIL DATA terlebih dahulu.'
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					table.clear().draw();
					var errorMsg = xhr.responseJSON?.error || 'Gagal memuat data';
					Swal.fire({
						icon: 'info',
						title: 'Informasi',
						text: errorMsg
					});
				}
			});
		}

		function ambilData() {
			$('#btnAmbilData').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');
			$('#LOADX').show();

			return $.ajax({
				url: '{{ route('lhkkode3trial_ambil') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}'
				}
			}).then(function(response) {
				$('#LOADX').hide();
				$('#btnAmbilData').prop('disabled', false).html('<i class="fas fa-download"></i> AMBIL DATA');
				return response;
			}).catch(function(xhr) {
				$('#LOADX').hide();
				$('#btnAmbilData').prop('disabled', false).html('<i class="fas fa-download"></i> AMBIL DATA');
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: xhr.responseJSON?.error || 'Gagal mengambil data'
				});
				throw new Error(xhr.responseJSON?.error || 'Gagal mengambil data');
			});
		}

		function prosesData() {
			$('#btnProses').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');
			$('#LOADX').show();

			var items = Object.values(editedData);

			return $.ajax({
				url: '{{ route('lhkkode3trial_proses') }}',
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

		function cetakLaporan() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('lhkkode3trial_detail') }}',
				type: 'GET',
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data.length > 0) {
						var printWindow = window.open('', '', 'height=800,width=1200');
						printWindow.document.write('<html><head><title>Laporan LH Kode 3 Trial</title>');
						printWindow.document.write(
							'<style>body{font-family:Arial;margin:20px}table{width:100%;border-collapse:collapse;margin-top:20px;font-size:10px}th,td{border:1px solid #000;padding:4px}th{background-color:#343a40;color:white;text-align:center;font-size:9px}.text-right{text-align:right}.text-center{text-align:center}h2{text-align:center;margin-bottom:5px}h3{text-align:center;margin-top:0}@media print{@page{size:landscape;margin:10mm}}</style>'
							);
						printWindow.document.write('</head><body>');
						printWindow.document.write('<h2>LAPORAN LH KODE 3 TRIAL</h2>');
						printWindow.document.write('<h3>CBG: ' + (response.cbg || '-') + '</h3>');
						printWindow.document.write('<p>Tanggal: ' + new Date().toLocaleDateString('id-ID') + '</p>');
						printWindow.document.write(
							'<table><thead><tr><th>Sub</th><th>Item</th><th>Nama Barang</th><th>Ukuran</th><th>Kemasan</th><th>MO</th><th>TMM</th><th>TGZ</th><th>SOP</th><th>LL TMM</th><th>LL TGZ</th><th>LL SOP</th><th>Usul TMM</th><th>Usul TGZ</th><th>Usul SOP</th><th>Kasir TMM</th><th>Kasir TGZ</th><th>Kasir SOP</th><th>TS TMM</th><th>TS TGZ</th><th>TS SOP</th><th>Keterangan</th></tr></thead><tbody>'
							);

						response.data.forEach(function(row) {
							printWindow.document.write('<tr>');
							printWindow.document.write('<td>' + (row.SUB || '') + '</td>');
							printWindow.document.write('<td>' + (row.KDBAR || '') + '</td>');
							printWindow.document.write('<td>' + (row.NA_BRG || '') + '</td>');
							printWindow.document.write('<td class="text-center">' + (row.KET_UK || '') + '</td>');
							printWindow.document.write('<td class="text-center">' + (row.KET_KEM || '') + '</td>');
							printWindow.document.write('<td class="text-center">' + (row.MO || '') + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.LPH_TMM || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.LPH_GZ || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.LPH_KG || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.LPH_TMM_LL || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.LPH_GZ_LL || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.LPH_KG_LL || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.LPHTMM || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.LPHGZ || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.LPHKG || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.JLMM || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.JLGZ || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.JLKG || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.TS_MM || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.TS_GZ || 0, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.TS_KG || 0, 2) + '</td>');
							printWindow.document.write('<td>' + (row.KETERANGAN || '') + '</td>');
							printWindow.document.write('</tr>');
						});

						printWindow.document.write('</tbody></table></body></html>');
						printWindow.document.close();
						setTimeout(function() {
							printWindow.print();
						}, 250);
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Perhatian',
							text: 'Tidak ada data untuk dicetak'
						});
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
	</script>
@endsection
