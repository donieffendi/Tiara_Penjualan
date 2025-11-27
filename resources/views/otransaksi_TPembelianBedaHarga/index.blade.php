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

		.filter-section {
			background: #f8f9fa;
			border: 1px solid #dee2e6;
			padding: 20px;
			border-radius: 5px;
			margin-bottom: 20px;
		}

		.text-right-col {
			text-align: right !important;
		}

		.text-center-col {
			text-align: center !important;
		}

		.chk-proses {
			width: 18px;
			height: 18px;
			cursor: pointer;
		}

		.form-inline .form-group {
			margin-right: 15px;
			margin-bottom: 10px;
		}

		.form-inline label {
			margin-right: 5px;
			font-weight: 500;
		}

		.btn-lookup {
			padding: 5px 10px;
			font-size: 12px;
			margin-left: 5px;
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
								<!-- Filter Section -->
								<div class="filter-section">
									<h5 class="mb-3"><i class="fas fa-filter"></i> Filter Data</h5>
									<form id="formFilter" class="form-inline">
										<div class="form-group">
											<label>Supplier</label>
											<input type="text" class="form-control form-control-sm" id="supDari" placeholder="Dari" style="width: 100px;">
											<span class="mx-1">s/d</span>
											<input type="text" class="form-control form-control-sm" id="supSampai" placeholder="Sampai" value="ZZZ" style="width: 100px;">
											<button type="button" class="btn btn-sm btn-info btn-lookup" onclick="lookupSupplier('supDari')">
												<i class="fas fa-search"></i>
											</button>
										</div>

										<div class="form-group">
											<label>Barang</label>
											<input type="text" class="form-control form-control-sm" id="brgDari" placeholder="Dari" style="width: 100px;">
											<span class="mx-1">s/d</span>
											<input type="text" class="form-control form-control-sm" id="brgSampai" placeholder="Sampai" value="ZZZ" style="width: 100px;">
											<button type="button" class="btn btn-sm btn-info btn-lookup" onclick="lookupBarang('brgDari')">
												<i class="fas fa-search"></i>
											</button>
										</div>

										<div class="form-group">
											<label>Tanggal</label>
											<input type="date" class="form-control form-control-sm" id="tanggal" value="{{ date('Y-m-d') }}" style="width: 150px;">
										</div>

										<div class="form-group">
											<label>Sort By</label>
											<select class="form-control form-control-sm" id="sortBy" style="width: 120px;">
												<option value="supplier">Supplier</option>
												<option value="barang">Barang</option>
												<option value="selisih">Selisih</option>
											</select>
										</div>
									</form>
								</div>

								<!-- Button Actions -->
								<div class="row mb-3">
									<div class="col-md-12 text-right">
										<button type="button" id="btnTampil" class="btn btn-tampil">
											<i class="fas fa-sync-alt"></i> TAMPILKAN
										</button>
										<button type="button" id="btnCetak" class="btn btn-cetak">
											<i class="fas fa-print"></i> CETAK
										</button>
									</div>
								</div>

								<!-- Info Proses -->
								<div class="alert alert-info" role="alert">
									<i class="fas fa-info-circle"></i> <strong>Cara Proses Data:</strong> Centang checkbox pada kolom "Proses" untuk data yang ingin diproses,
									kemudian klik tombol TAMPILKAN untuk membuat dokumen TH.
								</div>

								<hr>

								<!-- Table Controls -->
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

								<!-- Data Table -->
								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">No</th>
												<th class="text-center">Tgl Beli</th>
												<th>Supp</th>
												<th>Nama</th>
												<th>Notes</th>
												<th>Barang</th>
												<th>Nama</th>
												<th class="text-right">Qty</th>
												<th class="text-right">Harga Beli</th>
												<th class="text-right">Harga Sup</th>
												<th class="text-right">Selisih Total</th>
												<th class="text-center">Proses</th>
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
			console.log('=== HALAMAN PEMBELIAN BEDA HARGA DIMUAT ===');
			loadData();

			// Page length change
			$('#pageLength').on('change', function() {
				table.page.len($(this).val()).draw();
			});

			// Search box
			$('#searchBox').on('keyup', function() {
				table.search($(this).val()).draw();
			});

			// Supplier field - Ctrl+Enter untuk popup lookup
			$('#supDari, #supSampai').on('keydown', function(e) {
				if (e.ctrlKey && e.keyCode === 13) { // Ctrl + Enter
					e.preventDefault();
					var targetId = $(this).attr('id');
					lookupSupplier(targetId);
				}
			});

			// Barang field - Ctrl+Enter untuk popup lookup
			$('#brgDari, #brgSampai').on('keydown', function(e) {
				if (e.ctrlKey && e.keyCode === 13) { // Ctrl + Enter
					e.preventDefault();
					var targetId = $(this).attr('id');
					lookupBarang(targetId);
				}
			});

			// Button Cetak
			$('#btnCetak').on('click', function() {
				if (table.data().count() === 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Tidak ada data untuk dicetak'
					});
					return;
				}

				$('#LOADX').show();

				$.ajax({
					url: '{{ route('pembelianbedaharga_cetak') }}',
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						sup_dari: $('#supDari').val(),
						sup_sampai: $('#supSampai').val(),
						brg_dari: $('#brgDari').val(),
						brg_sampai: $('#brgSampai').val(),
						tanggal: $('#tanggal').val()
					},
					success: function(response) {
						$('#LOADX').hide();

						if (response.success) {
							// Generate print window
							var printWindow = window.open('', '', 'height=600,width=800');
							printWindow.document.write('<html><head><title>Laporan Pembelian Beda Harga</title>');
							printWindow.document.write('<style>');
							printWindow.document.write('body { font-family: Arial, sans-serif; }');
							printWindow.document.write(
								'table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10px; }');
							printWindow.document.write('th, td { border: 1px solid #000; padding: 5px; }');
							printWindow.document.write('th { background-color: #343a40; color: white; text-align: center; }');
							printWindow.document.write('.text-right { text-align: right; }');
							printWindow.document.write('.text-center { text-align: center; }');
							printWindow.document.write('h2 { text-align: center; margin-bottom: 5px; }');
							printWindow.document.write('h3 { text-align: center; margin-top: 0; }');
							printWindow.document.write('</style>');
							printWindow.document.write('</head><body>');

							if (response.data.length > 0) {
								printWindow.document.write('<h2>' + response.data[0].nama_toko + '</h2>');
							}
							printWindow.document.write('<h3>Laporan Pembelian Beda Harga</h3>');
							printWindow.document.write('<p>Tanggal Cetak: ' + new Date().toLocaleDateString('id-ID') + '</p>');

							printWindow.document.write('<table>');
							printWindow.document.write('<thead><tr>');
							printWindow.document.write('<th>No</th>');
							printWindow.document.write('<th>Tgl Beli</th>');
							printWindow.document.write('<th>Supplier</th>');
							printWindow.document.write('<th>Nama</th>');
							printWindow.document.write('<th>Barang</th>');
							printWindow.document.write('<th>Nama Barang</th>');
							printWindow.document.write('<th>Qty</th>');
							printWindow.document.write('<th>Harga Beli</th>');
							printWindow.document.write('<th>Harga Sup</th>');
							printWindow.document.write('<th>Selisih</th>');
							printWindow.document.write('</tr></thead><tbody>');

							var totalSelisih = 0;
							response.data.forEach(function(row, index) {
								totalSelisih += parseFloat(row.selisih_total);
								printWindow.document.write('<tr>');
								printWindow.document.write('<td class="text-center">' + (index + 1) + '</td>');
								printWindow.document.write('<td class="text-center">' + formatDate(row.tgl_beli) +
									'</td>');
								printWindow.document.write('<td>' + row.kd_supplier + '</td>');
								printWindow.document.write('<td>' + row.nama_supplier + '</td>');
								printWindow.document.write('<td>' + row.kd_brg + '</td>');
								printWindow.document.write('<td>' + row.nama_barang + '</td>');
								printWindow.document.write('<td class="text-right">' + formatNumber(row.qty, 0) +
									'</td>');
								printWindow.document.write('<td class="text-right">' + formatNumber(row.harga_beli, 2) +
									'</td>');
								printWindow.document.write('<td class="text-right">' + formatNumber(row.harga_supplier,
									2) + '</td>');
								printWindow.document.write('<td class="text-right">' + formatNumber(row.selisih_total,
									2) + '</td>');
								printWindow.document.write('</tr>');
							});

							printWindow.document.write('<tr>');
							printWindow.document.write(
								'<td colspan="9" class="text-right"><strong>Total Selisih:</strong></td>');
							printWindow.document.write('<td class="text-right"><strong>' + formatNumber(totalSelisih, 2) +
								'</strong></td>');
							printWindow.document.write('</tr>');

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
			});

			// Button Tampilkan - dengan auto proses jika ada checkbox yang dicentang
			$('#btnTampil').on('click', function() {
				var checkedItems = $('.chk-proses:checked').length;

				// Jika ada checkbox yang dicentang, proses dulu
				if (checkedItems > 0) {
					Swal.fire({
						title: 'Konfirmasi Proses',
						html: 'Ditemukan <strong>' + checkedItems +
							'</strong> item yang dipilih.<br>Proses akan membuat dokumen Transaksi Hutang (TH) baru.<br><small class="text-info">Lanjutkan proses?</small>',
						icon: 'question',
						showCancelButton: true,
						confirmButtonColor: '#007bff',
						cancelButtonColor: '#6c757d',
						confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses!',
						cancelButtonText: '<i class="fas fa-times"></i> Batal',
						showLoaderOnConfirm: true,
						preConfirm: () => {
							$('#btnTampil').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');
							$('#LOADX').show();

							return $.ajax({
								url: '{{ route('pembelianbedaharga_proses') }}',
								type: 'POST',
								data: {
									_token: '{{ csrf_token() }}'
								}
							}).fail(function(xhr) {
								$('#LOADX').hide();
								Swal.showValidationMessage('Proses gagal: ' + (xhr.responseJSON?.error ||
									'Terjadi kesalahan'));
							});
						},
						allowOutsideClick: () => !Swal.isLoading()
					}).then((result) => {
						$('#btnTampil').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> TAMPILKAN');
						$('#LOADX').hide();

						if (result.isConfirmed) {
							var noBuktiList = result.value.data.no_bukti_list.join('<br>');
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								html: result.value.message + '<br><br><strong>Nomor Dokumen:</strong><br>' + noBuktiList,
								showConfirmButton: true
							});
							table.ajax.reload();
						}
					});
				} else {
					// Tidak ada checkbox, reload data saja
					$('#LOADX').show();
					table.ajax.reload(function() {
						$('#LOADX').hide();
						Swal.fire({
							icon: 'success',
							title: 'Data Dimuat',
							text: 'Data telah berhasil diperbarui',
							timer: 1500,
							showConfirmButton: false
						});
					});
				}
			});

			// Handle checkbox change untuk update gol
			$(document).on('change', '.chk-proses', function() {
				var checkbox = $(this);
				var noBukti = checkbox.data('nobukti');
				var kdBrg = checkbox.data('kdbrg');
				var gol = checkbox.is(':checked') ? '1' : '0';

				$.ajax({
					url: '{{ route('pembelianbedaharga_update_gol') }}',
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						no_bukti: noBukti,
						kd_brg: kdBrg,
						gol: gol
					},
					success: function(response) {
						// Silent update
					},
					error: function(xhr) {
						checkbox.prop('checked', !checkbox.is(':checked'));
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal mengupdate status: ' + (xhr.responseJSON?.error || 'Terjadi kesalahan')
						});
					}
				});
			});
		});

		function loadData() {
			table = $('#tableData').KoolDataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: '{{ route('pembelianbedaharga_cari') }}',
					type: 'POST',
					data: function(d) {
						d._token = '{{ csrf_token() }}';
						d.sup_dari = $('#supDari').val();
						d.sup_sampai = $('#supSampai').val();
						d.brg_dari = $('#brgDari').val();
						d.brg_sampai = $('#brgSampai').val();
						d.tanggal = $('#tanggal').val();
						d.sort_by = $('#sortBy').val();
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
						data: 'tgl_beli',
						name: 'tgl_beli',
						className: 'text-center'
					},
					{
						data: 'kd_supplier',
						name: 'kd_supplier'
					},
					{
						data: 'nama_supplier',
						name: 'nama_supplier'
					},
					{
						data: 'notes',
						name: 'notes'
					},
					{
						data: 'kd_brg',
						name: 'kd_brg'
					},
					{
						data: 'nama_barang',
						name: 'nama_barang'
					},
					{
						data: 'qty',
						name: 'qty',
						className: 'text-right'
					},
					{
						data: 'harga_beli',
						name: 'harga_beli',
						className: 'text-right'
					},
					{
						data: 'harga_supplier',
						name: 'harga_supplier',
						className: 'text-right'
					},
					{
						data: 'selisih_total',
						name: 'selisih_total',
						className: 'text-right'
					},
					{
						data: 'proses',
						name: 'proses',
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
				]
			});
		}

		function formatNumber(num, decimals) {
			return parseFloat(num).toLocaleString('id-ID', {
				minimumFractionDigits: decimals,
				maximumFractionDigits: decimals
			});
		}

		function formatDate(dateStr) {
			var date = new Date(dateStr);
			var day = String(date.getDate()).padStart(2, '0');
			var month = String(date.getMonth() + 1).padStart(2, '0');
			var year = date.getFullYear();
			return day + '-' + month + '-' + year;
		}

		function lookupSupplier(targetId) {
			console.log('=== LOOKUP SUPPLIER DIBUKA ===', 'Target:', targetId);
			$('#LOADX').show();

			// Get daftar supplier dari database
			$.ajax({
				url: '{{ route('pembelianbedaharga_lookup_supplier') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data.length > 0) {
						var html = '<div style="max-height: 400px; overflow-y: auto;">';
						html += '<table class="table table-sm table-striped table-bordered table-hover" style="font-size: 12px;">';
						html += '<thead style="position: sticky; top: 0; background: #343a40; color: white;">';
						html += '<tr><th>Kode</th><th>Nama Supplier</th><th>Telepon</th><th>Aksi</th></tr>';
						html += '</thead><tbody>';

						response.data.forEach(function(item) {
							html += '<tr>';
							html += '<td>' + item.kodes + '</td>';
							html += '<td>' + item.namas + '</td>';
							html += '<td>' + (item.tlp_k || '-') + '</td>';
							html += '<td class="text-center">';
							html += '<button class="btn btn-sm btn-primary btn-select-supplier" data-kode="' + item.kodes +
								'" data-nama="' + item.namas + '">Pilih</button>';
							html += '</td>';
							html += '</tr>';
						});

						html += '</tbody></table></div>';

						Swal.fire({
							title: '<i class="fas fa-search"></i> Lookup Supplier',
							html: html,
							width: '700px',
							showConfirmButton: false,
							showCancelButton: true,
							cancelButtonText: 'Tutup',
							didOpen: () => {
								// Handle button pilih
								$('.btn-select-supplier').on('click', function() {
									var kode = $(this).data('kode');
									var nama = $(this).data('nama');

									$('#' + targetId).val(kode).focus();
									console.log('Supplier dipilih:', kode, nama);
									Swal.close();
								});
							}
						});
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Data Kosong',
							text: 'Tidak ada data supplier yang ditemukan'
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					console.error('Error lookup supplier:', xhr.responseJSON);
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal memuat data supplier'
					});
				}
			});
		}

		function lookupBarang(targetId) {
			console.log('=== LOOKUP BARANG DIBUKA ===', 'Target:', targetId);
			$('#LOADX').show();

			// Get daftar barang dari database
			$.ajax({
				url: '{{ route('pembelianbedaharga_lookup_barang') }}',
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
						html += '<tr><th>Kode</th><th>Nama Barang</th><th>Ukuran</th><th>Aksi</th></tr>';
						html += '</thead><tbody>';

						response.data.forEach(function(item) {
							html += '<tr class="row-barang">';
							html += '<td>' + item.kd_brg + '</td>';
							html += '<td>' + item.na_brg + '</td>';
							html += '<td>' + (item.ket_uk || '-') + '</td>';
							html += '<td class="text-center">';
							html += '<button class="btn btn-sm btn-primary btn-select-barang" data-kode="' + item.kd_brg +
								'" data-nama="' + item.na_brg + '">Pilih</button>';
							html += '</td>';
							html += '</tr>';
						});

						html += '</tbody></table></div>';

						Swal.fire({
							title: '<i class="fas fa-search"></i> Lookup Barang',
							html: html,
							width: '800px',
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

									$('#' + targetId).val(kode).focus();
									console.log('Barang dipilih:', kode, nama);
									Swal.close();
								});
							}
						});
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Data Kosong',
							text: 'Tidak ada data barang yang ditemukan'
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
