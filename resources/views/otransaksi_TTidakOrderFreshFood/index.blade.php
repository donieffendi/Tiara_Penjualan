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
										<li>Tekan <strong>Enter</strong> pada field Kode Barang atau klik tombol <strong>CARI</strong> untuk membuka popup daftar barang</li>
										<li>Di popup akan tampil seluruh barang fresh food dengan fitur <strong>pencarian dan pagination</strong></li>
										<li>Gunakan kolom "Cari" di popup untuk mencari barang berdasarkan kode atau nama</li>
										<li>Klik <strong>Pilih</strong> atau <strong>double-click</strong> pada row barang untuk memilih</li>
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
						data: 'KD_BRG'
					},
					{
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
					// {
					// 	data: 'QTY',
					// 	className: 'text-right'
					// },
					{
						data: 'QTY',
						className: 'text-right',
						render: function (data, type, row) {

							if (type !== 'display') {
								return data;
							}

							let value = parseFloat(data ?? 0);

							// paksa 2 digit decimal
							value = value.toFixed(2);

							return '<input type="number" ' +
								'class="form-control form-control-sm edit-qty text-right" ' +
								'data-rec="'+row.rec+'" ' +
								'value="'+value+'" ' +
								'min="0" step="0.01" ' +
								'style="width:90px;">';
						}
					},
					{
						data: null,
						className: 'text-center',
						render: function(data, type, row) {
							return `
								<button class="btn btn-sm btn-danger btn-delete" data-rec="${row.rec}">
									<i class="fas fa-trash"></i>
								</button>
							`;
						}
					}
					// {
					// 	data: 'action',
					// 	className: 'text-center'
					// }
				],
				paging: false,
				searching: false,
				ordering: false,
				info: false,
				processing: true
			});

			// Load data awal
			loadData();

			// Button Cari - Munculkan popup modal lookup barang
			$('#btnCari').on('click', function(e) {
				e.preventDefault();
				lookupBarang();
			});

			// Enter pada input kode barang - Munculkan popup modal lookup barang
			$('#txtKodeBarang').on('keypress', function(e) {
				if (e.which === 13) { // Enter
					e.preventDefault();
					lookupBarang();
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
						table.draw(false);

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
			// Open Jasper PDF in new window
			var form = document.createElement('form');
			form.method = 'POST';
			form.action = '{{ route('tidakorderfreshfood_jasper') }}';
			form.target = '_blank';

			var csrfToken = document.createElement('input');
			csrfToken.type = 'hidden';
			csrfToken.name = '_token';
			csrfToken.value = '{{ csrf_token() }}';
			form.appendChild(csrfToken);

			document.body.appendChild(form);
			form.submit();
			document.body.removeChild(form);
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

			// Buat HTML untuk modal dengan DataTable
			var html = '<div id="lookupBarangContainer">';
			html += '<div class="text-center py-4">';
			html += '<i class="fas fa-spinner fa-spin fa-3x text-primary"></i>';
			html += '<p class="mt-2">Memuat data barang...</p>';
			html += '</div>';
			html += '</div>';

			Swal.fire({
				title: '<i class="fas fa-search"></i> Lookup Barang Fresh Food',
				html: html,
				width: '1000px',
				showConfirmButton: false,
				showCancelButton: true,
				cancelButtonText: '<i class="fas fa-times"></i> Tutup',
				didOpen: () => {
					loadBarangDataTable();
				},
				willClose: () => {
					// Destroy DataTable saat modal ditutup
					if ($.fn.DataTable.isDataTable('#tableBarangLookup')) {
						$('#tableBarangLookup').DataTable().destroy();
					}
				}
			});
		}

		function loadBarangDataTable() {
			// Get semua barang fresh food dari database
			$.ajax({
				url: '{{ route('tidakorderfreshfood_lookup_barang') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					if (response.success && response.data) {
						var html = '<div class="alert alert-success">';
						html += '<i class="fas fa-check-circle"></i> ' + response.message;
						html += '</div>';
						html += '<div class="table-responsive">';
						html +=
							'<table class="table table-sm table-striped table-bordered table-hover" id="tableBarangLookup" style="width:100%; font-size: 12px;">';
						html += '<thead>';
						html += '<tr>';
						html += '<th width="15%">Kode</th>';
						html += '<th width="35%">Nama Barang</th>';
						html += '<th width="15%">Ukuran</th>';
						html += '<th width="15%">Kemasan</th>';
						html += '<th width="10%">Satuan</th>';
						html += '<th width="10%" class="text-center">Aksi</th>';
						html += '</tr>';
						html += '</thead>';
						html += '<tbody>';
						html += '</tbody>';
						html += '</table>';
						html += '</div>';

						$('#lookupBarangContainer').html(html);

						// Initialize DataTable dengan data dari server
						var tableBarangLookup = $('#tableBarangLookup').DataTable({
							data: response.data,
							columns: [{
									data: 'kd_brg'
								},
								{
									data: 'na_brg'
								},
								{
									data: 'ket_uk',
									render: function(data, type, row) {
										return data || '-';
									}
								},
								{
									data: 'ket_kem',
									render: function(data, type, row) {
										return data || '-';
									}
								},
								{
									data: 'satuan',
									render: function(data, type, row) {
										return data || '-';
									}
								},
								{
									data: null,
									orderable: false,
									className: 'text-center',
									render: function(data, type, row) {
										return '<button class="btn btn-sm btn-success btn-select-barang" data-kode="' + row
											.kd_brg + '" data-nama="' + row.na_brg + '">' +
											'<i class="fas fa-check"></i> Pilih' +
											'</button>';
									}
								}
							],
							pageLength: 10,
							lengthMenu: [
								[10, 25, 50, 100, -1],
								[10, 25, 50, 100, "Semua"]
							],
							language: {
								search: "Cari:",
								lengthMenu: "Tampilkan _MENU_ data per halaman",
								zeroRecords: "Tidak ada data yang ditemukan",
								info: "Menampilkan halaman _PAGE_ dari _PAGES_",
								infoEmpty: "Tidak ada data tersedia",
								infoFiltered: "(difilter dari _MAX_ total data)",
								paginate: {
									first: "Pertama",
									last: "Terakhir",
									next: "Selanjutnya",
									previous: "Sebelumnya"
								}
							},
							order: [
								[0, 'asc']
							],
							scrollY: '400px',
							scrollCollapse: true,
							responsive: true
						});

						// Handle button pilih
						// $('#tableBarangLookup').on('click', '.btn-select-barang', function() {
						// 	var kode = $(this).data('kode');
						// 	var nama = $(this).data('nama');

						// 	console.log('Barang dipilih:', kode, nama);

						// 	// Tutup modal
						// 	Swal.close();

						// 	// Auto cari barang setelah pilih
						// 	cariBarang(kode);
						// });

						$('#tableBarangLookup').on('click', '.btn-select-barang', function() {

							var row = tableBarangLookup.row($(this).closest('tr')).data();

							console.log('Barang dipilih:', row);

							// Cek duplikat
							var exists = tableData.find(x => x.KD_BRG === row.kd_brg);
							if (exists) {
								Swal.fire({
									icon: 'warning',
									title: 'Perhatian',
									text: 'Barang sudah ada dalam daftar!'
								});
								return;
							}
							
							var kd = row.kd_brg;

							var item = {
								rec: tableData.length + 1,
								SUB: kd.substring(0, 3),      // LEFT 3
								KDBAR: kd.slice(-4),          // RIGHT 4
								KD_BRG: kd,
								NA_BRG: row.na_brg,
								KET_UK: row.ket_uk || '',
								KET_KEM: row.ket_kem || '',
								KLK: row.klk || '',
								LPH: 0,
								SALDO: 0,
								TGL: new Date().toISOString().split('T')[0],
								QTY: 0
							};

							tableData.push(item);

							table.clear();
							table.rows.add(tableData);
							table.draw();

							Swal.close();
						});

						// Handle double click pada row
						$('#tableBarangLookup tbody').on('dblclick', 'tr', function() {
							$(this).find('.btn-select-barang').click();
						});

						// Focus ke search box
						$('#tableBarangLookup_filter input').focus();
					} else {
						$('#lookupBarangContainer').html(
							'<div class="alert alert-warning text-center">' +
							'<i class="fas fa-exclamation-circle"></i> Tidak ada data barang yang tersedia' +
							'</div>'
						);
					}
				},
				error: function(xhr) {
					console.error('Error lookup barang:', xhr.responseJSON);
					$('#lookupBarangContainer').html(
						'<div class="alert alert-danger text-center">' +
						'<i class="fas fa-times-circle"></i> ' +
						(xhr.responseJSON?.error || 'Gagal memuat data barang') +
						'</div>'
					);
				}
			});
		}
	</script>
@endsection
