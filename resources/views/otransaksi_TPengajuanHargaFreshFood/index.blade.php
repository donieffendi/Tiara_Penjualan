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

		.text-right-col {
			text-align: right !important;
		}

		.text-center-col {
			text-align: center !important;
		}

		.chk-oke {
			width: 18px;
			height: 18px;
			cursor: pointer;
		}

		.edit-harga {
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

		.label-bukti {
			font-size: 18px;
			font-weight: 700;
			color: #007bff;
			background: #e7f3ff;
			padding: 10px 15px;
			border-radius: 5px;
			display: inline-block;
			margin-top: 10px;
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
										<li>Masukkan nama file (contoh: UH2501 atau nama file .HRG) kemudian klik <strong>TAMPILKAN</strong></li>
										<li>Centang kolom <strong>Oke</strong> pada item yang akan diproses (harga baru > harga sekarang)</li>
										<li>Klik <strong>PROSES</strong> untuk menyimpan data ke sistem</li>
										<li>Gunakan <strong>Cetak Ulang</strong> untuk mencetak dokumen yang sudah diproses</li>
									</ul>
								</div>

								<!-- Form Input -->
								<div class="form-section">
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label>Nama File / No. Bukti</label>
												<input type="text" class="form-control" id="txtFilename" placeholder="Contoh: UH2501 atau nama file">
												<small class="form-text text-muted">Format: UH/U3 + Tahun(2 digit) + Bulan(2 digit) atau nama file .HRG</small>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label>Tanggal</label>
												<input type="date" class="form-control" id="txtTanggal" value="{{ date('Y-m-d') }}">
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label>&nbsp;</label>
												<button type="button" id="btnTampil" class="btn btn-tampil btn-block">
													<i class="fas fa-sync-alt"></i> TAMPILKAN
												</button>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group mb-0">
												<label>Cetak Ulang (No. Bukti)</label>
												<div class="input-group">
													<input type="text" class="form-control" id="txtCetakUlang" placeholder="Masukkan nomor bukti untuk cetak ulang">
													<div class="input-group-append">
														<button class="btn btn-info" type="button" id="btnCetakUlang">
															<i class="fas fa-print"></i> CETAK
														</button>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-6 text-right">
											<div id="labelBukti" class="label-bukti" style="display:none;"></div>
										</div>
									</div>
								</div>

								<!-- Button Proses -->
								<div class="row mb-3">
									<div class="col-md-12 text-right">
										<button type="button" id="btnProses" class="btn btn-proses" style="display:none;">
											<i class="fas fa-cogs"></i> PROSES
										</button>
									</div>
								</div>

								<hr>

								<!-- Data Table -->
								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">No</th>
												<th width="80px">Sub</th>
												<th width="100px">NoItem</th>
												<th>Nama Barang</th>
												<th width="120px" class="text-right">Harga Sekarang</th>
												<th width="120px" class="text-right">Harga</th>
												<th width="80px" class="text-center">Oke</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td colspan="7" class="text-center">Klik tombol TAMPILKAN untuk menampilkan data</td>
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
		var isPosted = false;
		var currentBukti = '';

		$(document).ready(function() {
			// Initialize empty table
			initTable();

			// Button Tampilkan
			$('#btnTampil').on('click', function() {
				var filename = $('#txtFilename').val().trim();

				if (filename === '') {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Nama file harus diisi!'
					});
					$('#txtFilename').focus();
					return;
				}

				loadData(filename);
			});

			// Button Proses
			$('#btnProses').on('click', function() {
				if (isPosted) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Data sudah diproses sebelumnya!'
					});
					return;
				}

				// Cek item yang dicentang
				var checkedItems = $('.chk-oke:checked').length;

				if (checkedItems === 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Tidak ada item yang dipilih untuk diproses!'
					});
					return;
				}

				Swal.fire({
					title: 'Konfirmasi Proses',
					html: 'Proses akan menyimpan <strong>' + checkedItems +
						'</strong> item yang dipilih.<br><small class="text-info">Data yang diproses tidak dapat dibatalkan.</small>',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#007bff',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						return prosesData();
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					if (result.isConfirmed && result.value.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							html: result.value.message + '<br><br><strong>Nomor Bukti:</strong><br>' + result.value.no_bukti,
							showConfirmButton: true
						}).then(() => {
							$('#btnProses').hide();
							$('#labelBukti').text(result.value.no_bukti).show();
							isPosted = true;

							// Disable semua checkbox
							$('.chk-oke').prop('disabled', true);
						});
					}
				});
			});

			// Button Cetak Ulang
			$('#btnCetakUlang').on('click', function() {
				var noBukti = $('#txtCetakUlang').val().trim();

				if (noBukti === '') {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Nomor bukti harus diisi!'
					});
					$('#txtCetakUlang').focus();
					return;
				}

				cetakUlang(noBukti);
			});

			// Handle edit harga
			$(document).on('change', '.edit-harga', function() {
				var rec = $(this).data('rec');
				var newValue = parseFloat($(this).val().replace(/,/g, ''));
				var oldValue = parseFloat($(this).data('oldvalue'));

				if (newValue < oldValue) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Harga yang dirubah turun, Mohon check lagi!'
					});
				}

				// Update nilai di tableData
				var item = tableData.find(x => x.rec == rec);
				if (item) {
					item.harga = newValue;
				}
			});

			// Enter key handler untuk txtFilename
			$('#txtFilename').on('keypress', function(e) {
				if (e.which === 13) {
					$('#btnTampil').click();
				}
			});

			// Enter key handler untuk txtCetakUlang
			$('#txtCetakUlang').on('keypress', function(e) {
				if (e.which === 13) {
					$('#btnCetakUlang').click();
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
						data: 'sub'
					},
					{
						data: 'kdbar'
					},
					{
						data: 'na_brg'
					},
					{
						data: 'hj',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: 'harga',
						className: 'text-right'
					},
					{
						data: 'oke',
						className: 'text-center'
					}
				],
				paging: false,
				searching: false,
				ordering: false,
				info: false
			});
		}

		function loadData(filename) {
			$('#LOADX').show();
			$('#btnProses').hide();
			$('#labelBukti').hide();

			$.ajax({
				url: '{{ route('pengajuanhargafreshfood_cari') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					filename: filename
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.data && response.data.length > 0) {
						tableData = response.data;
						isPosted = response.posted || false;
						currentBukti = response.no_bukti || '';

						// Render harga column based on posted status
						response.data.forEach(function(item) {
							if (isPosted) {
								item.harga = formatNumber(item.harga, 2);
							} else {
								item.harga = '<input type="text" class="form-control form-control-sm text-right edit-harga" data-rec="' +
									item.rec + '" data-oldvalue="' + item.harga + '" value="' + formatNumber(item.harga, 2, '.', '') +
									'">';
							}
						});

						table.clear();
						table.rows.add(response.data);
						table.draw();

						if (!isPosted) {
							$('#btnProses').show();
						} else {
							$('#labelBukti').text(currentBukti).show();
						}

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
							text: 'Tidak ada data'
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

		function prosesData() {
			$('#btnProses').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');
			$('#LOADX').show();

			// Ambil data yang dicentang
			var items = [];
			$('.chk-oke:checked').each(function() {
				var rec = $(this).data('rec');
				var item = tableData.find(x => x.rec == rec);
				if (item) {
					// Update harga dari input jika ada
					var inputHarga = $('.edit-harga[data-rec="' + rec + '"]');
					if (inputHarga.length > 0) {
						item.harga = parseFloat(inputHarga.val().replace(/,/g, ''));
					}
					item.pst = 1;
					items.push(item);
				}
			});

			return $.ajax({
				url: '{{ route('pengajuanhargafreshfood') }}/proses',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					filename: $('#txtFilename').val().trim(),
					tanggal: $('#txtTanggal').val(),
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

		function cetakUlang(noBukti) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('pengajuanhargafreshfood_detail', '') }}/' + noBukti,
				type: 'GET',
				data: {
					no_bukti: noBukti
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data.length > 0) {
						// Generate print window
						var printWindow = window.open('', '', 'height=600,width=800');
						printWindow.document.write('<html><head><title>Cetak Pengajuan Harga Fresh Food</title>');
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

						printWindow.document.write('<h2>Pengajuan Harga Fresh Food</h2>');
						printWindow.document.write('<h3>No. Bukti: ' + noBukti + '</h3>');
						printWindow.document.write('<p>File: ' + (response.data[0].file || '-') + '</p>');
						printWindow.document.write('<p>Tanggal Cetak: ' + new Date().toLocaleDateString('id-ID') + '</p>');

						printWindow.document.write('<table>');
						printWindow.document.write('<thead><tr>');
						printWindow.document.write('<th>No</th>');
						printWindow.document.write('<th>Kode Barang</th>');
						printWindow.document.write('<th>Nama Barang</th>');
						printWindow.document.write('<th>Kemasan</th>');
						printWindow.document.write('<th>Harga Lama (HJ)</th>');
						printWindow.document.write('<th>Harga Baru (HJBR)</th>');
						printWindow.document.write('</tr></thead><tbody>');

						response.data.forEach(function(row, index) {
							printWindow.document.write('<tr>');
							printWindow.document.write('<td class="text-center">' + (index + 1) + '</td>');
							printWindow.document.write('<td>' + row.KODE + '</td>');
							printWindow.document.write('<td>' + row.NA_BRG + '</td>');
							printWindow.document.write('<td class="text-center">' + (row.KET_KEM || '-') + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.HJ, 2) + '</td>');
							printWindow.document.write('<td class="text-right">' + formatNumber(row.HJBR, 2) + '</td>');
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
	</script>
@endsection
