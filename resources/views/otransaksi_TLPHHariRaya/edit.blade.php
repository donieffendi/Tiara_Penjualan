@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<style>
		.card {
			padding: 15px;
		}

		.form-control:focus,
		.form-control:active {
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

		.btn-back {
			background: #6c757d;
			border: none;
			color: #fff;
		}

		.btn-back:hover {
			background: #545b62;
			color: #fff;
		}

		.btn-add-item {
			background: #28a745;
			border: none;
			color: #fff;
		}

		.btn-add-item:hover {
			background: #218838;
			color: #fff;
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

		.form-group {
			margin-bottom: 15px;
		}

		.form-group label {
			font-weight: 600;
			margin-bottom: 5px;
			display: block;
			color: #333;
		}

		.form-control {
			border: 1px solid #ced4da;
			border-radius: 4px;
			padding: 6px 12px;
			font-size: 14px;
		}

		.form-control:disabled {
			background-color: #e9ecef;
			cursor: not-allowed;
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

		.text-right {
			text-align: right !important;
		}

		.text-center {
			text-align: center !important;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 13px;
			padding: 10px 8px;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.table tbody td {
			padding: 6px;
			font-size: 12px;
		}

		.label-nama-barang {
			font-weight: 600;
			color: #007bff;
			padding: 10px;
			background: #f0f8ff;
			border: 1px solid #b3d9ff;
			border-radius: 4px;
			margin-top: 10px;
			min-height: 40px;
		}

		.section-box {
			border: 1px solid #dee2e6;
			border-radius: 5px;
			padding: 15px;
			margin-bottom: 20px;
			background: #f8f9fa;
		}

		.section-title {
			font-weight: bold;
			color: #495057;
			margin-bottom: 15px;
			padding-bottom: 10px;
			border-bottom: 2px solid #007bff;
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
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<!-- Info Box -->
								<div class="info-box">
									<p class="mb-1"><strong>Petunjuk:</strong></p>
									<ul class="mb-0">
										<li>Pilih <strong>Kode HR</strong> untuk memilih event Hari Raya</li>
										<li>Tentukan <strong>Tgl H.Raya</strong>, <strong>Tgl Dr</strong>, dan <strong>Sampai Tgl</strong></li>
										<li>Masukkan <strong>Sub Item</strong> (Kode Barang) atau gunakan browse untuk mencari</li>
										<li>Isi <strong>LPH H.Raya</strong> untuk menentukan laku per hari selama event</li>
										<li>Klik <strong>Tambah Item</strong> untuk menambahkan ke daftar</li>
										<li>Klik <strong>SAVE</strong> untuk menyimpan semua perubahan</li>
									</ul>
								</div>

								<!-- Form Header -->
								<form id="formHeader">
									@csrf
									<input type="hidden" name="status" id="status" value="{{ $status }}">
									<input type="hidden" name="no_bukti_hidden" id="no_bukti_hidden" value="{{ $no_bukti }}">

									<div class="row">
										<div class="col-md-3">
											<!-- No Bukti -->
											<div class="form-group">
												<label for="no_bukti">No Bukti</label>
												<input type="text" class="form-control" id="no_bukti" value="{{ $no_bukti }}" readonly disabled>
											</div>
										</div>

										<div class="col-md-3">
											<!-- Tgl H.Raya -->
											<div class="form-group">
												<label for="tgl_raya">Tgl H.Raya <span class="text-danger">*</span></label>
												<input type="date" class="form-control" id="tgl_raya" name="tgl_raya" value="{{ $tgl_raya }}" required
													{{ $posted == 1 ? 'readonly' : '' }}>
											</div>
										</div>

										<div class="col-md-3">
											<!-- Outlet -->
											<div class="form-group">
												<label for="outlet">Outlet</label>
												<input type="text" class="form-control" id="outlet" value="{{ $cbg }}" readonly disabled>
											</div>
										</div>

										<div class="col-md-3">
											<!-- Kode HR -->
											<div class="form-group">
												<label for="kd_event">Kode HR <span class="text-danger">*</span></label>
												<select class="form-control" id="kd_event" name="kd_event" required {{ $posted == 1 ? 'disabled' : '' }}>
													<option value="">-- Pilih Hari Raya --</option>
													@foreach ($listHariRaya as $hr)
														<option value="{{ $hr->kode }}" {{ $kd_event == $hr->kode ? 'selected' : '' }}>
															{{ $hr->kode }} - {{ $hr->nama }}
														</option>
													@endforeach
												</select>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-3">
											<!-- Tgl Dr -->
											<div class="form-group">
												<label for="tgl_awal">Tgl Dr <span class="text-danger">*</span></label>
												<input type="date" class="form-control" id="tgl_awal" name="tgl_awal" value="{{ $tgl_awal }}" required
													{{ $posted == 1 ? 'readonly' : '' }}>
											</div>
										</div>

										<div class="col-md-3">
											<!-- Sampai Tgl -->
											<div class="form-group">
												<label for="tgl_akhir">Sampai Tgl <span class="text-danger">*</span></label>
												<input type="date" class="form-control" id="tgl_akhir" name="tgl_akhir" value="{{ $tgl_akhir }}" required
													{{ $posted == 1 ? 'readonly' : '' }}>
											</div>
										</div>

										<div class="col-md-6">
											<!-- Nama HR -->
											<div class="form-group">
												<label for="nama_event">Nama HR <span class="text-danger">*</span></label>
												<input type="text" class="form-control" id="nama_event" name="nama_event" value="{{ $nama_event }}" readonly>
											</div>
										</div>
									</div>

									<hr class="my-4">

									<!-- Form Entry Barang -->
									<div class="section-box">
										<div class="section-title">Entry Sub Item</div>

										<div class="row">
											<div class="col-md-6">
												<!-- Sub Item (Kode Barang) -->
												<div class="form-group">
													<label for="kd_brg">Sub Item <span class="text-danger">*</span></label>
													<div class="input-group">
														<input type="text" class="form-control" id="kd_brg" name="kd_brg" placeholder="Masukkan kode barang"
															{{ $posted == 1 ? 'readonly' : '' }}>
														<div class="input-group-append">
															<button class="btn btn-info" type="button" id="btnBrowse" {{ $posted == 1 ? 'disabled' : '' }}>
																<i class="fas fa-search"></i> Browse
															</button>
														</div>
													</div>
												</div>

												<!-- Nama Barang Display -->
												<div class="label-nama-barang" id="label_nama_barang">
													Nama Barang akan muncul di sini
												</div>
											</div>

											<div class="col-md-6">
												<!-- LPH H.Raya -->
												<div class="form-group">
													<label for="lph_raya">LPH H.Raya <span class="text-danger">*</span></label>
													<input type="number" class="form-control text-right" id="lph_raya" step="0.01" placeholder="0.00"
														{{ $posted == 1 ? 'readonly' : '' }}>
												</div>

												<div class="mt-4 text-right">
													<button type="button" id="btnAddItem" class="btn btn-action btn-add-item" {{ $posted == 1 ? 'disabled' : '' }}>
														<i class="fas fa-plus"></i> Tambah Item
													</button>
												</div>
											</div>
										</div>
									</div>

									<hr class="my-4">

									<!-- Data Table Items -->
									<div class="table-responsive">
										<table class="table-striped table-bordered table-hover table" id="tableItems" style="width:100%">
											<thead>
												<tr>
													<th width="50px" class="text-center">No</th>
													<th width="120px">Kode</th>
													<th>Nama Barang</th>
													<th width="150px">Ket UK</th>
													<th width="100px" class="text-right">L/H</th>
													<th width="100px" class="text-right">L/H H.Raya</th>
													<th width="80px" class="text-center">Aksi</th>
												</tr>
											</thead>
											<tbody>
												@if (!empty($detail))
													@foreach ($detail as $index => $item)
														<tr data-id="{{ $item->NO_ID }}">
															<td class="text-center">{{ $index + 1 }}</td>
															<td>{{ $item->KD_BRG }}</td>
															<td>{{ $item->NA_BRG }}</td>
															<td>{{ $item->KET_UK }}</td>
															<td class="text-right">{{ number_format($item->LPH_LAMA, 2) }}</td>
															<td class="text-right">{{ number_format($item->LPH_RAYA, 2) }}</td>
															<td class="text-center">
																<button class="btn btn-xs btn-danger btn-delete-item" data-id="{{ $item->NO_ID }}" {{ $posted == 1 ? 'disabled' : '' }}>
																	<i class="fas fa-trash"></i>
																</button>
															</td>
														</tr>
													@endforeach
												@endif
											</tbody>
										</table>
									</div>

									<hr class="my-4">

									<div class="text-right">
										<button type="button" id="btnBack" class="btn btn-action btn-back">
											<i class="fas fa-arrow-left"></i> BACK
										</button>
										<button type="button" id="btnSave" class="btn btn-action btn-save" {{ $posted == 1 ? 'disabled' : '' }}>
											<i class="fas fa-save"></i> SAVE
										</button>
									</div>
								</form>
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
		var currentNoBukti = '{{ $no_bukti }}';
		var currentStatus = '{{ $status }}';
		var isPosted = {{ $posted }};

		$(document).ready(function() {
			// Auto calculate tanggal awal dan akhir based on tgl_raya
			$('#tgl_raya').on('change', function() {
				if (currentStatus === 'simpan') {
					var tglRaya = new Date($(this).val());

					// Tgl Awal = Tgl Raya - 18 hari
					var tglAwal = new Date(tglRaya);
					tglAwal.setDate(tglAwal.getDate() - 18);

					// Tgl Akhir = Tgl Raya - 5 hari
					var tglAkhir = new Date(tglRaya);
					tglAkhir.setDate(tglAkhir.getDate() - 5);

					$('#tgl_awal').val(formatDate(tglAwal));
					$('#tgl_akhir').val(formatDate(tglAkhir));
				}
			});

			// Kode HR change
			$('#kd_event').on('change', function() {
				var selectedOption = $(this).find('option:selected');
				var text = selectedOption.text();

				if (text && text.indexOf(' - ') > -1) {
					var nama = text.split(' - ')[1];
					$('#nama_event').val(nama);
				} else {
					$('#nama_event').val('');
				}
			});

			// Button Back
			$('#btnBack').on('click', function() {
				window.location.href = '{{ route('lphhariraya') }}';
			});

			// Kode Barang - Enter key or blur handler
			$('#kd_brg').on('keydown', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					var kode = $(this).val().trim();
					if (kode) {
						if (kode.length < 7) {
							Swal.fire({
								icon: 'info',
								title: 'Info',
								text: 'Gunakan tombol Browse untuk mencari barang'
							});
						} else {
							searchBarang(kode);
						}
					}
				}
			});

			// Button Browse
			$('#btnBrowse').on('click', function() {
				Swal.fire({
					icon: 'info',
					title: 'Browse Barang',
					text: 'Fitur browse barang akan dikembangkan'
				});
			});

			// LPH Raya - Enter key
			$('#lph_raya').on('keydown', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					$('#btnAddItem').click();
				}
			});

			// Button Add Item
			$('#btnAddItem').on('click', function() {
				addItem();
			});

			// Button Save
			$('#btnSave').on('click', function() {
				saveData();
			});

			// Button Delete Item
			$(document).on('click', '.btn-delete-item', function() {
				if ($(this).prop('disabled')) {
					return;
				}

				var no_id = $(this).data('id');

				Swal.fire({
					title: 'Konfirmasi Hapus',
					text: 'Apakah item ini akan dihapus?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Hapus!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						deleteItem(no_id);
					}
				});
			});
		});

		function formatDate(date) {
			var year = date.getFullYear();
			var month = String(date.getMonth() + 1).padStart(2, '0');
			var day = String(date.getDate()).padStart(2, '0');
			return year + '-' + month + '-' + day;
		}

		function searchBarang(kode) {
			if (isPosted) {
				return;
			}

			$('#LOADX').show();

			$.ajax({
				url: '{{ route('lphhariraya_search_barang') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					kd_brg: kode
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data) {
						var barang = response.data;

						// Display nama barang
						var namaLengkap = barang.XX || (barang.NA_BRG + ' ' + barang.KET_UK);
						$('#label_nama_barang').text(namaLengkap);

						// Set LPH Raya (LPH x 2)
						$('#lph_raya').val(barang.LPH_RAYA || (barang.LPH * 2));

						$('#lph_raya').focus();
					} else {
						$('#label_nama_barang').text('Barang tidak ditemukan / SubItem bertanda *');
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.message || 'Barang tidak ditemukan'
						});
						clearForm();
					}
				},
				error: function() {
					$('#LOADX').hide();
					$('#label_nama_barang').text('Barang tidak ditemukan');
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Barang tidak ditemukan'
					});
					clearForm();
				}
			});
		}

		function addItem() {
			var kd_brg = $('#kd_brg').val().trim();
			var lph_raya = parseFloat($('#lph_raya').val()) || 0;

			if (!kd_brg) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Kode barang harus diisi'
				});
				return;
			}

			if (lph_raya <= 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'LPH H.Raya harus lebih dari 0'
				});
				return;
			}

			// Check if item already exists in table
			var exists = false;
			$('#tableItems tbody tr').each(function() {
				var rowKode = $(this).find('td:eq(1)').text().trim();
				if (rowKode === kd_brg) {
					exists = true;
					return false;
				}
			});

			if (exists) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Barang sudah ada dalam daftar'
				});
				return;
			}

			$('#LOADX').show();

			// First ensure header is saved
			if (currentNoBukti === '+') {
				saveHeader(function(no_bukti) {
					currentNoBukti = no_bukti;
					$('#no_bukti').val(no_bukti);
					$('#no_bukti_hidden').val(no_bukti);
					proceedAddItem(kd_brg, lph_raya);
				});
			} else {
				proceedAddItem(kd_brg, lph_raya);
			}
		}

		function proceedAddItem(kd_brg, lph_raya) {
			$.ajax({
				url: '{{ route('lphhariraya_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'add_item',
					no_bukti: currentNoBukti,
					kd_brg: kd_brg,
					lph_raya: lph_raya
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 1000,
							showConfirmButton: false
						});

						if (response.no_bukti) {
							currentNoBukti = response.no_bukti;
							$('#no_bukti').val(response.no_bukti);
							$('#no_bukti_hidden').val(response.no_bukti);
						}

						clearForm();
						$('#kd_brg').focus();

						setTimeout(function() {
							location.reload();
						}, 1000);
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					var errorMsg = 'Gagal menambah item';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMsg = xhr.responseJSON.error;
					}

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMsg
					});
				}
			});
		}

		function saveHeader(callback) {
			var tgl_raya = $('#tgl_raya').val();
			var tgl_awal = $('#tgl_awal').val();
			var tgl_akhir = $('#tgl_akhir').val();
			var kd_event = $('#kd_event').val();
			var nama_event = $('#nama_event').val();

			if (!tgl_raya || !tgl_awal || !tgl_akhir || !kd_event || !nama_event) {
				$('#LOADX').hide();
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Semua field header harus diisi'
				});
				return;
			}

			$.ajax({
				url: '{{ route('lphhariraya_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'save',
					status: 'simpan',
					no_bukti: '+',
					tgl_raya: tgl_raya,
					tgl_awal: tgl_awal,
					tgl_akhir: tgl_akhir,
					kd_event: kd_event,
					nama_event: nama_event,
					items: []
				},
				success: function(response) {
					if (response.success && response.no_bukti) {
						callback(response.no_bukti);
					} else {
						$('#LOADX').hide();
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal membuat header'
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					var errorMsg = 'Gagal membuat header';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMsg = xhr.responseJSON.error;
					}
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMsg
					});
				}
			});
		}

		function deleteItem(no_id) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('lphhariraya_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'delete_item',
					no_id: no_id
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 1000,
							showConfirmButton: false
						});

						setTimeout(function() {
							location.reload();
						}, 1000);
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					var errorMsg = 'Gagal menghapus item';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMsg = xhr.responseJSON.error;
					}

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMsg
					});
				}
			});
		}

		function saveData() {
			var tgl_raya = $('#tgl_raya').val();
			var tgl_awal = $('#tgl_awal').val();
			var tgl_akhir = $('#tgl_akhir').val();
			var kd_event = $('#kd_event').val();
			var nama_event = $('#nama_event').val();
			var no_bukti = currentNoBukti;

			// Validasi
			if (!tgl_raya) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Tanggal Hari Raya harus diisi'
				});
				return;
			}

			if (!tgl_awal || !tgl_akhir) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Tanggal periode harus diisi'
				});
				return;
			}

			if (!kd_event || !nama_event) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Kode dan Nama Hari Raya harus dipilih'
				});
				return;
			}

			// Check if has items
			var itemCount = $('#tableItems tbody tr').length;
			if (itemCount === 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Minimal harus ada 1 item'
				});
				return;
			}

			// Collect items data
			var items = [];
			$('#tableItems tbody tr').each(function() {
				var no_id = $(this).data('id') || 0;
				var kd_brg = $(this).find('td:eq(1)').text().trim();
				var na_brg = $(this).find('td:eq(2)').text().trim();
				var ket_uk = $(this).find('td:eq(3)').text().trim();
				var lph_lama = parseFloat($(this).find('td:eq(4)').text().replace(/,/g, '')) || 0;
				var lph_raya = parseFloat($(this).find('td:eq(5)').text().replace(/,/g, '')) || 0;

				items.push({
					no_id: no_id,
					kd_brg: kd_brg,
					na_brg: na_brg,
					ket_uk: ket_uk,
					ket_kem: '', // Will be filled by backend
					lph_lama: lph_lama,
					lph_raya: lph_raya
				});
			});

			$('#LOADX').show();

			$.ajax({
				url: '{{ route('lphhariraya_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'save',
					status: currentStatus,
					no_bukti: no_bukti,
					tgl_raya: tgl_raya,
					tgl_awal: tgl_awal,
					tgl_akhir: tgl_akhir,
					kd_event: kd_event,
					nama_event: nama_event,
					items: items
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							showConfirmButton: true,
							confirmButtonText: 'OK'
						}).then((result) => {
							if (result.isConfirmed) {
								window.location.href = '{{ route('lphhariraya') }}';
							}
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					var errorMsg = 'Gagal menyimpan data';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMsg = xhr.responseJSON.error;
					}

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMsg
					});
				}
			});
		}

		function clearForm() {
			$('#kd_brg').val('');
			$('#lph_raya').val('');
			$('#label_nama_barang').text('Nama Barang akan muncul di sini');
		}
	</script>
@endsection
