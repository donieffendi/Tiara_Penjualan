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
			background: #28a745;
			border: none;
			color: #fff;
		}

		.btn-save:hover {
			background: #218838;
			color: #fff;
		}

		.btn-refresh {
			background: #17a2b8;
			border: none;
			color: #fff;
		}

		.btn-refresh:hover {
			background: #138496;
			color: #fff;
		}

		.btn-clear {
			background: #dc3545;
			border: none;
			color: #fff;
		}

		.btn-clear:hover {
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

		.form-input-group {
			background: #f8f9fa;
			border: 1px solid #dee2e6;
			padding: 20px;
			border-radius: 5px;
			margin-bottom: 20px;
		}

		.form-input-group .form-group {
			margin-bottom: 15px;
		}

		.form-input-group label {
			font-weight: 600;
			margin-bottom: 5px;
			color: #495057;
		}

		/* Modal Browse Barang Styles */
		#modalBrowseBarang .modal-header {
			border-bottom: 2px solid #dee2e6;
		}

		#modalBrowseBarang #searchBarang {
			border: 2px solid #007bff;
			font-size: 14px;
		}

		#modalBrowseBarang #searchBarang:focus {
			border-color: #0056b3;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
		}

		#modalBrowseBarang #tableBarang thead {
			position: sticky;
			top: 0;
			z-index: 10;
		}

		#modalBrowseBarang .barang-row:hover {
			background-color: #e9ecef !important;
			cursor: pointer;
		}

		#modalBrowseBarang .btn-pilih-barang {
			padding: 4px 12px;
			font-size: 12px;
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
										<li>Tekan <strong>Enter</strong> di kolom Kode Barang untuk membuka daftar barang kode 3 (Fresh Food)</li>
										<li>Pilih barang dari popup atau ketik kode barang langsung</li>
										<li>Masukkan <strong>Qty</strong>, lalu klik <strong>SAVE</strong> untuk menambah item</li>
										<li>Klik <strong>REFRESH</strong> untuk memperbarui data tabel</li>
										<li>Klik <strong>PRINT</strong> untuk mencetak laporan</li>
										<li>Klik <strong>EXCEL</strong> untuk export ke Excel</li>
										<li>Klik <strong>CLEAR ALL</strong> untuk menghapus semua data</li>
										<li>Klik tombol <strong>Hapus</strong> di tabel untuk menghapus item tertentu</li>
									</ul>
								</div>

								<!-- Form Input -->
								<div class="form-input-group">
									<div class="row">
										<div class="col-md-5">
											<div class="form-group">
												<label for="kd_brg">Kode Barang <span class="text-danger">*</span></label>
												<input type="text" class="form-control" id="kd_brg" placeholder="Tekan Enter untuk browse barang">
												<small class="form-text text-muted">Tekan Enter untuk membuka daftar barang</small>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="qty">Qty <span class="text-danger">*</span></label>
												<input type="number" class="form-control" id="qty" placeholder="0.00" step="0.01" min="0" value="0">
											</div>
										</div>
										<div class="col-md-4">
											<label>&nbsp;</label>
											<div>
												<button type="button" id="btnSave" class="btn btn-action btn-save">
													<i class="fas fa-save"></i> SAVE
												</button>
												<button type="button" id="btnRefresh" class="btn btn-action btn-refresh">
													<i class="fas fa-sync"></i> REFRESH
												</button>
											</div>
										</div>
									</div>
								</div>

								<!-- Action Buttons -->
								<div class="mb-3">
									<button type="button" id="btnPrint" class="btn btn-action btn-info">
										<i class="fas fa-print"></i> PRINT
									</button>
									<button type="button" id="btnExcel" class="btn btn-action btn-success">
										<i class="fas fa-file-excel"></i> EXCEL
									</button>
									<button type="button" id="btnClear" class="btn btn-action btn-clear">
										<i class="fas fa-trash-alt"></i> CLEAR ALL
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
												<th width="120px">Kode Barang</th>
												<th>Nama Barang</th>
												<th width="120px">Kemasan</th>
												<th width="100px" class="text-right">Qty</th>
												<th width="100px">SUPP</th>
												<th width="120px" class="text-center">Tgl Kirim</th>
												<th width="80px" class="text-center">Aksi</th>
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

	<!-- Modal Browse Barang -->
	<div class="modal fade" id="modalBrowseBarang" tabindex="-1" role="dialog" aria-labelledby="modalBrowseBarangLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl" role="document">
			<div class="modal-content">
				<div class="modal-header bg-dark text-white">
					<h5 class="modal-title" id="modalBrowseBarangLabel">
						<i class="fas fa-search"></i> Daftar Barang Fresh Food (Kode 3)
					</h5>
					<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<input type="text" id="searchBarang" class="form-control" placeholder="Cari barang (kode/nama)..." autocomplete="off">
					</div>
					<div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
						<table class="table-striped table-bordered table-hover table" id="tableBarang" style="width: 100%; font-size: 13px;">
							<thead class="bg-dark text-white" style="position: sticky; top: 0; z-index: 1;">
								<tr>
									<th width="15%">Kode Barang</th>
									<th width="40%">Nama Barang</th>
									<th width="15%" class="text-center">Ukuran</th>
									<th width="15%" class="text-center">Kemasan</th>
									<th width="15%" class="text-center">Aksi</th>
								</tr>
							</thead>
							<tbody id="tbodyBarang">
								<tr>
									<td colspan="5" class="text-center">Loading...</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">
						<i class="fas fa-times"></i> Tutup
					</button>
				</div>
			</div>
		</div>
	</div>
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
					url: '{{ route('orderlebihfreshfood_cari') }}',
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
						data: 'KD_BRG'
					},
					{
						data: 'NA_BRG'
					},
					{
						data: 'KET_KEM',
						className: 'text-center'
					},
					{
						data: 'QTY',
						className: 'text-right'
					},
					{
						data: 'SUPP'
					},
					{
						data: 'TGL_KIRIM',
						className: 'text-center'
					},
					{
						data: 'action',
						className: 'text-center'
					}
				],
				order: [
					[2, 'asc']
				],
				processing: true,
				pageLength: 25
			});

			// Focus ke input kode barang
			$('#kd_brg').focus();

			// Handle Enter key pada input kode barang - buka modal browse
			$('#kd_brg').on('keypress', function(e) {
				if (e.which === 13) {
					e.preventDefault();
					openModalBrowseBarang();
				}
			});

			// Handle Ctrl+Enter tetap bisa untuk langsung buka popup
			$('#kd_brg').on('keydown', function(e) {
				if (e.ctrlKey && e.keyCode === 13) {
					e.preventDefault();
					openModalBrowseBarang();
					return;
				}
			});

			// Handle Enter key pada input qty
			$('#qty').on('keypress', function(e) {
				if (e.which === 13) {
					e.preventDefault();
					$('#btnSave').click();
				}
			});

			// Button Save
			$('#btnSave').on('click', function() {
				saveData();
			});

			// Button Refresh
			$('#btnRefresh').on('click', function() {
				table.ajax.reload();
				Swal.fire({
					icon: 'success',
					title: 'Berhasil',
					text: 'Data berhasil direfresh!',
					timer: 1500,
					showConfirmButton: false
				});
			});

			// Button Print
			$('#btnPrint').on('click', function() {
				printData();
			});

			// Button Excel
			$('#btnExcel').on('click', function() {
				exportExcel();
			});

			// Button Clear All
			$('#btnClear').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi Hapus',
					text: 'Apakah yakin ingin menghapus SEMUA data?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Hapus Semua!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						clearAll();
					}
				});
			});

			// Button Delete Item
			$(document).on('click', '.btn-delete-item', function() {
				var rec = $(this).data('rec');

				Swal.fire({
					title: 'Konfirmasi Hapus',
					text: 'Apakah yakin ingin menghapus item ini?',
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

		function saveData() {
			var kd_brg = $('#kd_brg').val().trim();
			var qty = $('#qty').val();

			if (kd_brg === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Kode barang tidak boleh kosong!'
				});
				$('#kd_brg').focus();
				return;
			}

			if (parseFloat(qty) <= 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Qty harus lebih dari 0!'
				});
				$('#qty').focus();
				return;
			}

			$('#LOADX').show();

			$.ajax({
				url: '{{ route('orderlebihfreshfood_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'save',
					kd_brg: kd_brg,
					qty: qty
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

						// Clear form
						$('#kd_brg').val('');
						$('#qty').val('0');
						$('#kd_brg').focus();

						// Reload table
						table.ajax.reload();
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal menyimpan data'
					});
				}
			});
		}

		function deleteItem(rec) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('orderlebihfreshfood_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'delete_item',
					rec: rec
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

						table.ajax.reload();
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal menghapus item'
					});
				}
			});
		}

		function clearAll() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('orderlebihfreshfood_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'delete_all'
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

						table.ajax.reload();
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal menghapus data'
					});
				}
			});
		}

		function printData() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('orderlebihfreshfood_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'print'
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data.length > 0) {
						var printWindow = window.open('', '', 'height=600,width=800');
						printWindow.document.write('<html><head><title>Cetak Order Lebih Fresh Food</title>');
						printWindow.document.write('<style>');
						printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
						printWindow.document.write('table { width: 100%; border-collapse: collapse; margin');
						printWindow.document.write('<h2>Order Lebih Fresh Food</h2>');
						printWindow.document.write('<h3>Cabang: {{ $cbg ?? '' }}</h3>');
						printWindow.document.write('<div class="info">');
						printWindow.document.write('<p><strong>User:</strong> ' + response.data[0].USER + '</p>');
						printWindow.document.write('<p><strong>Tanggal Cetak:</strong> ' + new Date().toLocaleDateString('id-ID') + '</p>');
						printWindow.document.write('</div>');

						printWindow.document.write('<table>');
						printWindow.document.write('<thead><tr>');
						printWindow.document.write('<th>No</th>');
						printWindow.document.write('<th>Sub Item</th>');
						printWindow.document.write('<th>Kode Barang</th>');
						printWindow.document.write('<th>Nama Barang</th>');
						printWindow.document.write('<th>Kemasan</th>');
						printWindow.document.write('<th>Qty</th>');
						printWindow.document.write('<th>SUPP</th>');
						printWindow.document.write('<th>Tgl Kirim</th>');
						printWindow.document.write('</tr></thead><tbody>');

						var totalQty = 0;
						response.data.forEach(function(row, index) {
							printWindow.document.write('<tr>');
							printWindow.document.write('<td class="text-center">' + (index + 1) + '</td>');
							printWindow.document.write('<td>' + (row.SUB || '-') + '</td>');
							printWindow.document.write('<td>' + row.KD_BRG + '</td>');
							printWindow.document.write('<td>' + row.NA_BRG + '</td>');
							printWindow.document.write('<td class="text-center">' + (row.KET_KEM || '-') + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.QTY, 2) + '</td>');
							printWindow.document.write('<td>' + (row.SUPP || '-') + '</td>');
							printWindow.document.write('<td class="text-center">' + row.TGL_KIRIM + '</td>');
							printWindow.document.write('</tr>');
							totalQty += parseFloat(row.QTY || 0);
						});

						printWindow.document.write('<tr>');
						printWindow.document.write('<td colspan="5" class="text-right"><strong>TOTAL</strong></td>');
						printWindow.document.write('<td class="text-right"><strong>' + formatNumber(totalQty, 2) + '</strong></td>');
						printWindow.document.write('<td colspan="2"></td>');
						printWindow.document.write('</tr>');

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

		function exportExcel() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('orderlebihfreshfood_proses') }}',
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
								wch: 15
							}, // Kode Barang
							{
								wch: 15
							}, // Kode BRG
							{
								wch: 40
							}, // Nama Barang
							{
								wch: 15
							}, // Kemasan
							{
								wch: 10
							}, // Qty
							{
								wch: 12
							}, // SUPP
							{
								wch: 15
							} // Tgl Kirim
						];

						// Create workbook
						var wb = XLSX.utils.book_new();
						XLSX.utils.book_append_sheet(wb, ws, "Order Lebih");

						// Generate filename with timestamp
						var filename = 'Order_Lebih_Fresh_Food_' + new Date().toISOString().slice(0, 10) + '.xlsx';

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

		function formatNumber(num, decimals) {
			var n = parseFloat(num);
			if (isNaN(n)) return '0';
			return n.toLocaleString('id-ID', {
				minimumFractionDigits: decimals,
				maximumFractionDigits: decimals
			});
		}

		// Buka modal browse barang
		function openModalBrowseBarang() {
			$('#modalBrowseBarang').modal('show');
			loadDataBarang();
		}

		// Load data barang ke modal
		function loadDataBarang() {
			$('#tbodyBarang').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

			$.ajax({
				url: '{{ route('orderlebihfreshfood_lookup_barang') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					if (response.success && response.data.length > 0) {
						var tableRows = '';
						response.data.forEach(function(item) {
							tableRows += '<tr class="barang-row" style="cursor: pointer;">';
							tableRows += '<td>' + item.kd_brg + '</td>';
							tableRows += '<td>' + item.na_brg + '</td>';
							tableRows += '<td class="text-center">' + (item.ket_uk || '-') + '</td>';
							tableRows += '<td class="text-center">' + (item.ket_kem || '-') + '</td>';
							tableRows += '<td class="text-center">';
							tableRows += '<button type="button" class="btn btn-sm btn-primary btn-pilih-barang" data-kode="' + item
								.kd_brg + '">';
							tableRows += '<i class="fas fa-check"></i> Pilih';
							tableRows += '</button>';
							tableRows += '</td>';
							tableRows += '</tr>';
						});

						$('#tbodyBarang').html(tableRows);

						// Event handler untuk button pilih
						$('.btn-pilih-barang').on('click', function() {
							var kode = $(this).data('kode');
							selectBarang(kode);
						});

						// Event handler untuk double click pada row
						$('.barang-row').on('dblclick', function() {
							var kode = $(this).find('.btn-pilih-barang').data('kode');
							selectBarang(kode);
						});

						// Focus ke search box
						setTimeout(function() {
							$('#searchBarang').focus();
						}, 300);
					} else {
						$('#tbodyBarang').html(
							'<tr><td colspan="5" class="text-center text-muted">Tidak ada data barang fresh food (kode 3)</td></tr>');
					}
				},
				error: function(xhr) {
					console.error('Error loading barang:', xhr);
					$('#tbodyBarang').html(
						'<tr><td colspan="5" class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> Error: ' + (xhr
							.responseJSON?.error || 'Gagal memuat data') + '</td></tr>');
				}
			});
		}

		// Pilih barang dari modal
		function selectBarang(kode) {
			$('#kd_brg').val(kode);
			$('#modalBrowseBarang').modal('hide');
			$('#qty').focus();
		}

		// Search/filter barang di modal
		$(document).on('keyup', '#searchBarang', function() {
			var searchText = $(this).val().toLowerCase();
			$('.barang-row').each(function() {
				var rowText = $(this).text().toLowerCase();
				if (rowText.indexOf(searchText) === -1) {
					$(this).hide();
				} else {
					$(this).show();
				}
			});
		});

		// Handle Enter di search box modal - pilih item pertama yang terlihat
		$(document).on('keypress', '#searchBarang', function(e) {
			if (e.which === 13) {
				e.preventDefault();
				var firstVisible = $('.barang-row:visible:first .btn-pilih-barang');
				if (firstVisible.length > 0) {
					var kode = firstVisible.data('kode');
					selectBarang(kode);
				}
			}
		});

		// Legacy function - keep for backward compatibility
		function lookupBarang() {
			openModalBrowseBarang();
		}
	</script>
