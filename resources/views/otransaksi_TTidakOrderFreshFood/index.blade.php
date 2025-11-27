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

		.btn-save {
			background: #007bff;
			border: none;
			color: #fff;
		}

		.btn-save:hover {
			background: #0056b3;
			color: #fff;
		}

		.btn-refresh {
			background: #6c757d;
			border: none;
			color: #fff;
		}

		.btn-refresh:hover {
			background: #545b62;
			color: #fff;
		}

		.btn-print {
			background: #17a2b8;
			border: none;
			color: #fff;
		}

		.btn-print:hover {
			background: #138496;
			color: #fff;
		}

		.btn-proses {
			background: #28a745;
			border: none;
			color: #fff;
		}

		.btn-proses:hover {
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

		.edit-qty {
			text-align: right;
			padding: 2px 5px;
			height: 28px;
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

		.modal-print {
			max-width: 90%;
		}

		.form-control-sm {
			height: calc(1.5em + 0.5rem + 2px);
			padding: 0.25rem 0.5rem;
			font-size: 0.875rem;
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
										<li>Masukkan <strong>Kode Barang</strong> untuk menambah item baru ke dalam tabel (atau tekan <strong>Ctrl+Enter</strong> untuk popup daftar
											barang)</li>
										<li>Edit <strong>Qty</strong> langsung di kolom tabel untuk mengubah jumlah order</li>
										<li>Klik <strong>SAVE</strong> untuk menyimpan data ke database</li>
										<li>Klik <strong>REFRESH</strong> untuk menghapus semua data dan mulai dari awal</li>
										<li>Klik <strong>PRINT</strong> untuk mencetak laporan</li>
										<li>Klik <strong>PROSES</strong> untuk mengekspor data ke file DBF</li>
									</ul>
								</div>

								<!-- Form Input -->
								<div class="form-section">
									<div class="row">
										<div class="col-md-8">
											<div class="form-group mb-0">
												<label>Kode Barang</label>
												<div class="input-group">
													<input type="text" class="form-control" id="txtKodeBarang" placeholder="Masukkan kode barang (tekan Enter)">
													<div class="input-group-append">
														<button class="btn btn-info" type="button" id="btnCari">
															<i class="fas fa-search"></i> CARI
														</button>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-4 text-right">
											<label>&nbsp;</label><br>
											<button type="button" id="btnSave" class="btn btn-action btn-save">
												<i class="fas fa-save"></i> SAVE
											</button>
											<button type="button" id="btnRefresh" class="btn btn-action btn-refresh">
												<i class="fas fa-sync-alt"></i> REFRESH
											</button>
											<button type="button" id="btnPrint" class="btn btn-action btn-print">
												<i class="fas fa-print"></i> PRINT
											</button>
											<button type="button" id="btnProses" class="btn btn-action btn-proses">
												<i class="fas fa-cogs"></i> PROSES
											</button>
										</div>
									</div>
								</div>

								<hr>

								<!-- Data Table -->
								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">No</th>
												<th width="80px">Sub Item</th>
												<th width="80px">Sub</th>
												<th width="100px">Item</th>
												<th>Nama Barang</th>
												<th width="100px">Ukuran</th>
												<th width="100px" class="text-right">LPH</th>
												<th width="100px" class="text-right">Saldo</th>
												<th width="100px">Tanggal</th>
												<th width="100px" class="text-right">Qty</th>
												<th width="60px" class="text-center">Aksi</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td colspan="11" class="text-center">Tidak ada data. Masukkan kode barang untuk menambah item.</td>
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

		$(document).ready(function() {
			console.log('=== HALAMAN TIDAK ORDER FRESH FOOD DIMUAT ===');

			// Event handler Ctrl+Enter untuk popup lookup barang
			$('#txtKodeBarang').on('keydown', function(e) {
				if (e.ctrlKey && e.keyCode === 13) { // Ctrl + Enter
					e.preventDefault();
					lookupBarang();
				}
			});

			// Initialize DataTable
			table = $('#tableData').DataTable({
				ajax: {
					url: '{{ route('tidakorderfreshfood_cari') }}',
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
						data: 'KDBAR'
					},
					{
						data: 'KD_BRG'
					},
					{
						data: 'NA_BRG'
					},
					{
						data: 'KET_UK'
					},
					{
						data: 'LPH',
						className: 'text-right'
					},
					{
						data: 'SALDO',
						className: 'text-right'
					},
					{
						data: 'TGL'
					},
					{
						data: 'QTY',
						className: 'text-right'
					},
					{
						data: 'action',
						className: 'text-center'
					}
				],
				paging: false,
				searching: false,
				ordering: false,
				info: false,
				processing: true
			});

			// Load data awal
			loadData();

			// Button Cari / Enter pada input kode barang
			$('#btnCari, #txtKodeBarang').on('click keypress', function(e) {
				if (e.type === 'click' || (e.type === 'keypress' && e.which === 13)) {
					e.preventDefault();
					var kodeBarang = $('#txtKodeBarang').val().trim();

					if (kodeBarang === '') {
						Swal.fire({
							icon: 'warning',
							title: 'Perhatian',
							text: 'Kode barang harus diisi!'
						});
						$('#txtKodeBarang').focus();
						return;
					}

					cariBarang(kodeBarang);
				}
			});

			// Button Save
			$('#btnSave').on('click', function() {
				saveData();
			});

			// Button Refresh
			$('#btnRefresh').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi Refresh',
					text: 'Semua data akan dihapus. Lanjutkan?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#6c757d',
					cancelButtonColor: '#d33',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Refresh!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						refreshData();
					}
				});
			});

			// Button Print
			$('#btnPrint').on('click', function() {
				printData();
			});

			// Button Proses
			$('#btnProses').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi Proses',
					text: 'Data akan diproses ke file DBF. Lanjutkan?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#28a745',
					cancelButtonColor: '#d33',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						prosesDBF();
					}
				});
			});

			// Handle edit qty
			$(document).on('change', '.edit-qty', function() {
				var rec = $(this).data('rec');
				var newQty = parseFloat($(this).val()) || 0;

				// Update di tableData
				var item = tableData.find(x => x.rec == rec);
				if (item) {
					item.QTY = newQty;
				}
			});

			// Handle delete button
			$(document).on('click', '.btn-delete', function() {
				var rec = $(this).data('rec');

				Swal.fire({
					title: 'Konfirmasi Hapus',
					text: 'Hapus item ini?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Hapus!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						deleteItem(rec);
					}
				});
			});
		});

		function loadData() {
			$('#LOADX').show();

			table.ajax.reload(function(json) {
				$('#LOADX').hide();
				tableData = json.data || [];
			}, false);
		}

		function cariBarang(kodeBarang) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('tidakorderfreshfood_cari') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					kd_brg: kodeBarang
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data) {
						var item = response.data;

						// Cek duplikat
						var exists = tableData.find(x => x.KD_BRG === item.KD_BRG);
						if (exists) {
							Swal.fire({
								icon: 'warning',
								title: 'Perhatian',
								text: 'Barang sudah ada dalam daftar!'
							});
							$('#txtKodeBarang').val('').focus();
							return;
						}

						// Tambah ke tableData
						item.rec = tableData.length + 1;
						tableData.push(item);

						// Reload table
						table.clear();
						table.rows.add(tableData);
						table.draw();

						$('#txtKodeBarang').val('').focus();

						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Barang ditambahkan!',
							timer: 1000,
							showConfirmButton: false
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Barang tidak ditemukan'
					});

					$('#txtKodeBarang').focus();
				}
			});
		}

		function saveData() {
			if (tableData.length === 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Tidak ada data untuk disimpan!'
				});
				return;
			}

			$('#LOADX').show();
			$('#btnSave').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> SAVING...');

			$.ajax({
				url: '{{ route('tidakorderfreshfood_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'save',
					items: tableData
				},
				success: function(response) {
					$('#LOADX').hide();
					$('#btnSave').prop('disabled', false).html('<i class="fas fa-save"></i> SAVE');

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 1500,
							showConfirmButton: false
						});

						loadData();
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					$('#btnSave').prop('disabled', false).html('<i class="fas fa-save"></i> SAVE');

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal menyimpan data'
					});
				}
			});
		}

		function refreshData() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('tidakorderfreshfood_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'refresh'
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

						tableData = [];
						table.clear().draw();
						$('#txtKodeBarang').val('').focus();
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal refresh data'
					});
				}
			});
		}

		function printData() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('tidakorderfreshfood_detail', '') }}',
				type: 'GET',
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data.length > 0) {
						// Generate print window
						var printWindow = window.open('', '', 'height=600,width=800');
						printWindow.document.write('<html><head><title>Cetak Tidak Order Fresh Food</title>');
						printWindow.document.write('<style>');
						printWindow.document.write('body { font-family: Arial, sans-serif; }');
						printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; }');
						printWindow.document.write('th, td { border: 1px solid #000; padding: 5px; }');
						printWindow.document.write('th { background-color: #343a40; color: white; text-align: center; }');
						printWindow.document.write('.text-right { text-align: right; }');
						printWindow.document.write('.text-center { text-align: center; }');
						printWindow.document.write('h2 { text-align: center; margin-bottom: 5px; }');
						printWindow.document.write('h3 { text-align: center; margin-top: 0; }');
						printWindow.document.write('</style>');
						printWindow.document.write('</head><body>');

						printWindow.document.write('<h2>Tidak Order Fresh Food</h2>');
						printWindow.document.write('<h3>Cabang: {{ $cbg ?? '' }}</h3>');
						printWindow.document.write('<p>User: ' + response.data[0].USER + '</p>');
						printWindow.document.write('<p>Tanggal Cetak: ' + new Date().toLocaleDateString('id-ID') + '</p>');

						printWindow.document.write('<table>');
						printWindow.document.write('<thead><tr>');
						printWindow.document.write('<th>No</th>');
						printWindow.document.write('<th>Kode Barang</th>');
						printWindow.document.write('<th>Nama Barang</th>');
						printWindow.document.write('<th>Kemasan</th>');
						printWindow.document.write('<th>LPH</th>');
						printWindow.document.write('<th>Saldo</th>');
						printWindow.document.write('<th>Qty</th>');
						printWindow.document.write('<th>Tanggal</th>');
						printWindow.document.write('</tr></thead><tbody>');

						response.data.forEach(function(row, index) {
							printWindow.document.write('<tr>');
							printWindow.document.write('<td class="text-center">' + (index + 1) + '</td>');
							printWindow.document.write('<td>' + row.KD_BRG + '</td>');
							printWindow.document.write('<td>' + row.NA_BRG + '</td>');
							printWindow.document.write('<td class="text-center">' + (row.KET_KEM || '-') + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.LPH, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.SALDO, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.QTY, 2) + '</td>');
							printWindow.document.write('<td class="text-center">' + row.TGL + '</td>');
							printWindow.document.write('</tr>');
						});

						printWindow.document.write('</tbody></table>');
						printWindow.document.write('</body></html>');
						printWindow.document.close();

						setTimeout(function() {
							printWindow.print();
						}, 250);
					} else {
						Swal.fire({
							icon: 'info',
							title: 'Informasi',
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

		function prosesDBF() {
			$('#LOADX').show();
			$('#btnProses').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');

			$.ajax({
				url: '{{ route('tidakorderfreshfood_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'proses_dbf'
				},
				success: function(response) {
					$('#LOADX').hide();
					$('#btnProses').prop('disabled', false).html('<i class="fas fa-cogs"></i> PROSES');

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					$('#btnProses').prop('disabled', false).html('<i class="fas fa-cogs"></i> PROSES');

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Proses gagal'
					});
				}
			});
		}

		function deleteItem(rec) {
			tableData = tableData.filter(x => x.rec != rec);

			// Re-index rec
			tableData.forEach((item, index) => {
				item.rec = index + 1;
			});

			table.clear();
			table.rows.add(tableData);
			table.draw();

			Swal.fire({
				icon: 'success',
				title: 'Berhasil',
				text: 'Item berhasil dihapus',
				timer: 1000,
				showConfirmButton: false
			});
		}

		function formatNumber(num, decimals) {
			var n = parseFloat(num);
			if (isNaN(n)) return '0';

			return n.toLocaleString('id-ID', {
				minimumFractionDigits: decimals,
				maximumFractionDigits: decimals
			});
		}

		function lookupBarang() {
			console.log('=== LOOKUP BARANG DIBUKA ===');
			$('#LOADX').show();

			// Get daftar barang fresh food dari database
			$.ajax({
				url: '{{ route('tidakorderfreshfood_lookup_barang') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data.length > 0) {
						var html = '<div style="max-height: 400px; overflow-y: auto;">';
						html +=
							'<input type="text" class="form-control form-control-sm mb-2" id="searchBarang" placeholder="Cari barang..." style="position: sticky; top: 0; z-index: 10;">';
						html +=
							'<table class="table table-sm table-striped table-bordered table-hover" id="tableBarangLookup" style="font-size: 12px;">';
						html += '<thead style="position: sticky; top: 38px; background: #343a40; color: white; z-index: 9;">';
						html += '<tr><th>Kode</th><th>Nama Barang</th><th>Ukuran</th><th>Kemasan</th><th>Aksi</th></tr>';
						html += '</thead><tbody>';

						response.data.forEach(function(item) {
							html += '<tr class="row-barang">';
							html += '<td>' + item.kd_brg + '</td>';
							html += '<td>' + item.na_brg + '</td>';
							html += '<td>' + (item.ket_uk || '-') + '</td>';
							html += '<td>' + (item.ket_kem || '-') + '</td>';
							html += '<td class="text-center">';
							html += '<button class="btn btn-sm btn-primary btn-select-barang" data-kode="' + item.kd_brg +
								'" data-nama="' + item.na_brg + '">Pilih</button>';
							html += '</td>';
							html += '</tr>';
						});

						html += '</tbody></table></div>';

						Swal.fire({
							title: '<i class="fas fa-search"></i> Lookup Barang Fresh Food',
							html: html,
							width: '900px',
							showConfirmButton: false,
							showCancelButton: true,
							cancelButtonText: 'Tutup',
							didOpen: () => {
								// Handle search box
								$('#searchBarang').on('keyup', function() {
									var value = $(this).val().toLowerCase();
									$('#tableBarangLookup tbody tr').filter(function() {
										$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
									});
								}).focus();

								// Handle button pilih
								$('.btn-select-barang').on('click', function() {
									var kode = $(this).data('kode');
									var nama = $(this).data('nama');

									$('#txtKodeBarang').val(kode).focus();
									console.log('Barang dipilih:', kode, nama);
									Swal.close();

									// Auto cari barang setelah pilih
									setTimeout(function() {
										cariBarang(kode);
									}, 200);
								});
							}
						});
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Data Kosong',
							text: 'Tidak ada data barang fresh food yang ditemukan'
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					console.error('Error lookup barang:', xhr.responseJSON);
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal memuat data barang'
					});
				}
			});
		}
	</script>
@endsection
