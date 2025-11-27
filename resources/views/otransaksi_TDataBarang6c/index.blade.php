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

		.btn-tampilkan {
			background: #007bff;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
			width: 100%;
		}

		.btn-tampilkan:hover {
			background: #0056b3;
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

		.detail-section {
			background: #fff;
			padding: 20px;
			border-radius: 5px;
			border: 1px solid #dee2e6;
			margin-top: 20px;
		}

		.detail-section h5 {
			border-bottom: 2px solid #007bff;
			padding-bottom: 10px;
			margin-bottom: 20px;
			color: #007bff;
		}

		.info-box {
			background: #e7f3ff;
			padding: 15px;
			border-radius: 5px;
			border-left: 4px solid #007bff;
			margin-bottom: 20px;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
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

		.detail-table {
			font-size: 14px;
		}

		.detail-table th {
			width: 200px;
			background: #f8f9fa;
			font-weight: 600;
		}

		.detail-table td {
			padding: 10px;
		}

		.badge-status {
			padding: 5px 15px;
			border-radius: 3px;
			font-size: 12px;
		}

		.badge-aktif {
			background: #28a745;
			color: white;
		}

		.badge-nonaktif {
			background: #dc3545;
			color: white;
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
									<strong>Informasi:</strong> Masukkan Kode Barang atau Barcode untuk mencari data barang. Tekan Enter atau klik tombol Tampilkan untuk
									melakukan pencarian.
								</div>

								<!-- Form Pencarian -->
								<div class="search-section">
									<form id="formCari">
										<div class="row">
											<div class="col-md-5">
												<div class="form-group">
													<label for="kd_brg">
														<i class="fas fa-barcode"></i> Kode Barang
													</label>
													<input type="text" class="form-control" id="kd_brg" name="kd_brg" placeholder="Masukkan kode barang" autocomplete="off">
												</div>
											</div>
											<div class="col-md-5">
												<div class="form-group">
													<label for="barcode">
														<i class="fas fa-qrcode"></i> Barcode
													</label>
													<input type="text" class="form-control" id="barcode" name="barcode" placeholder="Scan atau masukkan barcode" autocomplete="off">
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<label>&nbsp;</label>
													<button type="submit" class="btn btn-tampilkan">
														<i class="fas fa-search"></i> TAMPILKAN
													</button>
												</div>
											</div>
										</div>
									</form>
								</div>

								<!-- Detail Barang -->
								<div class="detail-section" id="detailSection" style="display: none;">
									<h5><i class="fas fa-box"></i> Detail Data Barang</h5>

									<!-- Master Barang -->
									<div class="row">
										<div class="col-md-6">
											<table class="table-bordered detail-table table">
												<tr>
													<th>Kode Barang</th>
													<td id="detail_kd_brg">-</td>
												</tr>
												<tr>
													<th>Nama Barang</th>
													<td id="detail_na_brg">-</td>
												</tr>
												<tr>
													<th>Barcode</th>
													<td id="detail_barcode">-</td>
												</tr>
												<tr>
													<th>Satuan</th>
													<td id="detail_satuan">-</td>
												</tr>
											</table>
										</div>
										<div class="col-md-6">
											<table class="table-bordered detail-table table">
												<tr>
													<th>Group</th>
													<td id="detail_group">-</td>
												</tr>
												<tr>
													<th>Jenis</th>
													<td id="detail_jenis">-</td>
												</tr>
												<tr>
													<th>Status</th>
													<td id="detail_status">-</td>
												</tr>
											</table>
										</div>
									</div>

									<!-- Detail Harga & Ukuran -->
									<div class="row mt-3">
										<div class="col-md-12">
											<h6><i class="fas fa-tags"></i> Detail Harga & Ukuran</h6>
											<div class="table-responsive">
												<table class="table-bordered table-striped table">
													<thead>
														<tr>
															<th width="50px" class="text-center">No</th>
															<th>Ukuran</th>
															<th class="text-right">Harga Beli</th>
															<th class="text-right">Harga Jual</th>
															<th class="text-right">PPN (%)</th>
															<th class="text-right">Diskon (%)</th>
														</tr>
													</thead>
													<tbody id="detail_transaksi_body">
														<tr>
															<td colspan="6" class="text-center">Tidak ada data</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									</div>

									<!-- Stok Cabang -->
									<div class="row mt-3">
										<div class="col-md-12">
											<h6><i class="fas fa-warehouse"></i> Data Stok Cabang</h6>
											<table class="table-bordered detail-table table">
												<tr>
													<th width="200px">Stok Awal</th>
													<td id="stok_aw00">-</td>
													<th width="200px">Masuk</th>
													<td id="stok_ma00">-</td>
												</tr>
												<tr>
													<th>Keluar</th>
													<td id="stok_ke00">-</td>
													<th>Lain-lain</th>
													<td id="stok_ln00">-</td>
												</tr>
												<tr>
													<th>Stok Akhir</th>
													<td colspan="3" id="stok_ak00" style="font-weight: bold; color: #007bff; font-size: 16px;">-</td>
												</tr>
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
	<script>
		$(document).ready(function() {
			// Handle form submit
			$('#formCari').on('submit', function(e) {
				e.preventDefault();
				cariBarang();
			});

			// Handle enter key pada kd_brg
			$('#kd_brg').on('keydown', function(e) {
				if (e.keyCode === 13) {
					e.preventDefault();
					cariBarang();
				}
			});

			// Handle enter key dan blur pada barcode
			$('#barcode').on('keydown', function(e) {
				if (e.keyCode === 13) {
					e.preventDefault();
					cariBarang();
				}
			});

			// Auto fill kd_brg saat barcode blur
			$('#barcode').on('blur', function() {
				var barcode = $.trim($(this).val());
				var kd_brg = $.trim($('#kd_brg').val());

				if (barcode !== '' && kd_brg === '') {
					// Otomatis cari kd_brg dari barcode
					$.ajax({
						url: "{{ route('tdatabarang6c_cari') }}",
						type: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							barcode: barcode,
							kd_brg: ''
						},
						success: function(response) {
							if (response.success && response.data.master) {
								$('#kd_brg').val(response.data.master.kd_brg);
							}
						}
					});
				}
			});

			// Auto fill barcode saat kd_brg blur
			$('#kd_brg').on('blur', function() {
				var kd_brg = $.trim($(this).val());
				var barcode = $.trim($('#barcode').val());

				if (kd_brg !== '' && barcode === '') {
					// Otomatis cari barcode dari kd_brg
					$.ajax({
						url: "{{ route('tdatabarang6c_cari') }}",
						type: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							kd_brg: kd_brg,
							barcode: ''
						},
						success: function(response) {
							if (response.success && response.data.master) {
								$('#barcode').val(response.data.master.barcode || '');
							}
						}
					});
				}
			});
		});

		function cariBarang() {
			var kd_brg = $.trim($('#kd_brg').val());
			var barcode = $.trim($('#barcode').val());

			if (kd_brg === '' && barcode === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Kode barang atau barcode harus diisi!'
				});
				return;
			}

			$('#LOADX').show();

			$.ajax({
				url: "{{ route('tdatabarang6c_cari') }}",
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					kd_brg: kd_brg,
					barcode: barcode
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						// Update field kd_brg dan barcode
						$('#kd_brg').val(response.data.master.kd_brg);
						$('#barcode').val(response.data.master.barcode || '');

						// Tampilkan detail
						tampilkanDetail(response.data);

						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 1500,
							showConfirmButton: false
						});
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

					// Sembunyikan detail jika error
					$('#detailSection').hide();
				}
			});
		}

		function tampilkanDetail(data) {
			var master = data.master;
			var detail_transaksi = data.detail_transaksi || [];
			var stok_cabang = data.stok_cabang[0] || null;

			// Isi data master
			$('#detail_kd_brg').text(master.kd_brg);
			$('#detail_na_brg').text(master.na_brg);
			$('#detail_barcode').text(master.barcode || '-');
			$('#detail_satuan').text((master.kd_satuan || '-') + (master.na_satuan ? ' - ' + master.na_satuan : ''));
			$('#detail_group').text((master.kd_group || '-') + (master.na_group ? ' - ' + master.na_group : ''));
			$('#detail_jenis').text((master.kd_jenis || '-') + (master.na_jenis ? ' - ' + master.na_jenis : ''));

			var statusBadge = master.aktif == 1 ?
				'<span class="badge badge-aktif">AKTIF</span>' :
				'<span class="badge badge-nonaktif">NON AKTIF</span>';
			$('#detail_status').html(statusBadge);

			// Isi detail transaksi
			var tbody = '';
			if (detail_transaksi.length > 0) {
				$.each(detail_transaksi, function(index, item) {
					tbody += '<tr>';
					tbody += '<td class="text-center">' + (index + 1) + '</td>';
					tbody += '<td>' + (item.uk || '-') + '</td>';
					tbody += '<td class="text-right">' + formatRupiah(item.hrg_beli || 0) + '</td>';
					tbody += '<td class="text-right">' + formatRupiah(item.hrg_jual || 0) + '</td>';
					tbody += '<td class="text-right">' + formatNumber(item.ppn || 0) + '</td>';
					tbody += '<td class="text-right">' + formatNumber(item.diskon || 0) + '</td>';
					tbody += '</tr>';
				});
			} else {
				tbody = '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>';
			}
			$('#detail_transaksi_body').html(tbody);

			// Isi stok cabang
			if (stok_cabang) {
				$('#stok_aw00').text(formatNumber(stok_cabang.aw00 || 0));
				$('#stok_ma00').text(formatNumber(stok_cabang.ma00 || 0));
				$('#stok_ke00').text(formatNumber(stok_cabang.ke00 || 0));
				$('#stok_ln00').text(formatNumber(stok_cabang.ln00 || 0));
				$('#stok_ak00').text(formatNumber(stok_cabang.ak00 || 0));
			} else {
				$('#stok_aw00').text('-');
				$('#stok_ma00').text('-');
				$('#stok_ke00').text('-');
				$('#stok_ln00').text('-');
				$('#stok_ak00').text('-');
			}

			// Tampilkan section detail
			$('#detailSection').slideDown();
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
