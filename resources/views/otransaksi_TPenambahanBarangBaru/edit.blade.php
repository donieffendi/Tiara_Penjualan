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

		.label-display {
			font-weight: 600;
			color: #007bff;
			padding: 10px;
			background: #f0f8ff;
			border: 1px solid #b3d9ff;
			border-radius: 4px;
			margin-top: 10px;
			min-height: 40px;
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
										<li>Masukkan <strong>Cek File KSP</strong> untuk mengambil data dari file KSP</li>
										<li>Atau masukkan <strong>Barcode/Sub Item</strong> secara manual</li>
										<li>Isi <strong>LPH</strong> dan <strong>DTR</strong> untuk setiap barang</li>
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
										<div class="col-md-4">
											<!-- No Bukti -->
											<div class="form-group">
												<label for="no_bukti">No Bukti</label>
												<input type="text" class="form-control" id="no_bukti" value="{{ $no_bukti }}" readonly disabled>
											</div>
										</div>

										<div class="col-md-4">
											<!-- Tanggal -->
											<div class="form-group">
												<label for="tgl">Tanggal</label>
												<input type="date" class="form-control" id="tgl" name="tgl" value="{{ $tgl }}" readonly disabled>
											</div>
										</div>

										<div class="col-md-4">
											<!-- Cek File KSP -->
											<div class="form-group">
												<label for="txt_file">Cek File KSP</label>
												<div class="input-group">
													<input type="text" class="form-control" id="txt_file" name="txt_file" placeholder="Masukkan nama file KSP"
														{{ $posted == 1 ? 'readonly' : '' }}>
													<div class="input-group-append">
														<button class="btn btn-info" type="button" id="btnCekFile" {{ $posted == 1 ? 'disabled' : '' }}>
															<i class="fas fa-search"></i> Cek
														</button>
													</div>
												</div>
											</div>
										</div>
									</div>

									<hr class="my-4">

									<!-- Form Entry Barang -->
									<div class="section-box">
										<div class="section-title">Entry Barang</div>

										<div class="row">
											<div class="col-md-6">
												<!-- Barcode -->
												<div class="form-group">
													<label for="barcode">Barcode</label>
													<input type="text" class="form-control" id="barcode" name="barcode" placeholder="Scan atau masukkan barcode"
														{{ $posted == 1 ? 'readonly' : '' }}>
												</div>

												<!-- Sub Item (Kode Barang) -->
												<div class="form-group">
													<label for="kd_brg">Sub Item</label>
													<input type="text" class="form-control" id="kd_brg" name="kd_brg" placeholder="Masukkan kode barang"
														{{ $posted == 1 ? 'readonly' : '' }}>
												</div>

												<!-- Nama Barang Display -->
												<div class="label-display" id="label_nama_barang">
													Nama Barang akan muncul di sini
												</div>
											</div>

											<div class="col-md-6">
												<!-- LPH -->
												<div class="form-group">
													<label for="lph">LPH <span class="text-danger">*</span></label>
													<input type="number" class="form-control text-right" id="lph" step="0.01" placeholder="0.00"
														{{ $posted == 1 ? 'readonly' : '' }}>
												</div>

												<!-- DTR -->
												<div class="form-group">
													<label for="dtr">DTR <span class="text-danger">*</span></label>
													<input type="number" class="form-control text-right" id="dtr" placeholder="0" {{ $posted == 1 ? 'readonly' : '' }}>
												</div>

												<!-- No SP -->
												<div class="form-group">
													<label for="no_sp">No SP</label>
													<input type="text" class="form-control" id="no_sp" placeholder="Nomor SP (opsional)" {{ $posted == 1 ? 'readonly' : '' }}>
												</div>

												<div class="mt-3 text-right">
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
													<th width="120px">Barcode</th>
													<th width="100px">Sub Item</th>
													<th>Nama Barang</th>
													<th width="120px">Ukuran</th>
													<th width="120px">Kemasan</th>
													<th width="80px" class="text-right">LPH</th>
													<th width="80px" class="text-right">DTR</th>
													<th width="100px">No SP</th>
													<th width="80px" class="text-center">Aksi</th>
												</tr>
											</thead>
											<tbody>
												@if (!empty($detail))
													@foreach ($detail as $index => $item)
														<tr data-id="{{ $item->NO_ID }}">
															<td class="text-center">{{ $index + 1 }}</td>
															<td>{{ $item->BARCODE }}</td>
															<td>{{ $item->KD_BRG }}</td>
															<td>{{ $item->NA_BRG }}</td>
															<td>{{ $item->KET_UK }}</td>
															<td>{{ $item->KET_KEM }}</td>
															<td class="text-right">{{ number_format($item->LPH, 2) }}</td>
															<td class="text-right">{{ $item->DTR }}</td>
															<td>{{ $item->NO_SP }}</td>
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
			// Button Back
			$('#btnBack').on('click', function() {
				window.location.href = '{{ route('penambahanbarangbaru') }}';
			});

			// Barcode - Enter key handler
			$('#barcode').on('keydown', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					var barcode = $(this).val().trim();
					if (barcode) {
						searchBarang(barcode, 'barcode');
					}
				}
			});

			// Kode Barang - Enter key handler
			$('#kd_brg').on('keydown', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					var kode = $(this).val().trim();
					if (kode) {
						searchBarang(kode, 'kd_brg');
					}
				}
			});

			// LPH - Enter key
			$('#lph').on('keydown', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					$('#dtr').focus();
				}
			});

			// DTR - Enter key
			$('#dtr').on('keydown', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					$('#no_sp').focus();
				}
			});

			// No SP - Enter key
			$('#no_sp').on('keydown', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					$('#btnAddItem').click();
				}
			});

			// Button Cek File KSP
			$('#btnCekFile').on('click', function() {
				var namaFile = $('#txt_file').val().trim();
				if (!namaFile) {
					Swal.fire({
						icon: 'warning',
						title: 'Validasi',
						text: 'Nama file KSP harus diisi'
					});
					return;
				}

				getKspFile(namaFile);
			});

			// Txt File - Enter key
			$('#txt_file').on('keydown', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					$('#btnCekFile').click();
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

		function searchBarang(value, type) {
			if (isPosted) {
				return;
			}

			$('#LOADX').show();

			var postData = {
				_token: '{{ csrf_token() }}'
			};

			if (type === 'barcode') {
				postData.barcode = value;
			} else {
				postData.kd_brg = value;
			}

			$.ajax({
				url: '{{ route('penambahanbarangbaru_search_barang') }}',
				type: 'POST',
				data: postData,
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data) {
						var barang = response.data;

						// Display info barang
						$('#barcode').val(barang.BARCODE);
						$('#kd_brg').val(barang.KD_BRG);
						$('#label_nama_barang').text(barang.NA_BRG + ' ' + barang.KET_UK);

						// Set default values
						$('#lph').val(0);
						$('#dtr').val(0);
						$('#no_sp').val('');

						$('#lph').focus();
					} else {
						$('#label_nama_barang').text('Barang tidak ditemukan atau belum disinkronkan');
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.message || 'Barang tidak ditemukan'
						});
						clearForm();
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					$('#label_nama_barang').text('Barang tidak ditemukan');

					var errorMsg = 'Barang tidak ditemukan';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMsg = xhr.responseJSON.message;
					}

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMsg
					});
					clearForm();
				}
			});
		}

		function getKspFile(namaFile) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('penambahanbarangbaru_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'get_ksp',
					nama_file: namaFile
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data) {
						Swal.fire({
							title: 'Konfirmasi',
							text: response.message + '. Tambahkan semua ke daftar?',
							icon: 'question',
							showCancelButton: true,
							confirmButtonColor: '#28a745',
							cancelButtonColor: '#6c757d',
							confirmButtonText: '<i class="fas fa-check"></i> Ya, Tambahkan!',
							cancelButtonText: '<i class="fas fa-times"></i> Batal'
						}).then((result) => {
							if (result.isConfirmed) {
								addItemsFromKsp(response.data);
							}
						});
					} else {
						Swal.fire({
							icon: 'info',
							title: 'Info',
							text: response.message || 'Tidak ada barang baru'
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					var errorMsg = 'Gagal mengambil data file KSP';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMsg = xhr.responseJSON.message;
					}

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMsg
					});
				}
			});
		}

		function addItemsFromKsp(items) {
			// First ensure header is saved
			if (currentNoBukti === '+') {
				saveHeader(function(no_bukti) {
					currentNoBukti = no_bukti;
					$('#no_bukti').val(no_bukti);
					$('#no_bukti_hidden').val(no_bukti);
					proceedAddItemsFromKsp(items);
				});
			} else {
				proceedAddItemsFromKsp(items);
			}
		}

		function proceedAddItemsFromKsp(items) {
			$('#LOADX').show();

			var processedItems = [];

			items.forEach(function(item) {
				processedItems.push({
					no_id: 0,
					barcode: item.BARCODE,
					kd_brg: item.KD_BRG,
					na_brg: item.NA_BRG,
					ket_uk: item.KET_UK,
					ket_kem: item.KET_KEM,
					lph: 0,
					dtr: 0,
					no_sp: item.NO_BUKTI || ''
				});
			});

			$.ajax({
				url: '{{ route('penambahanbarangbaru_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'save',
					status: currentStatus,
					no_bukti: currentNoBukti,
					items: processedItems
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Barang dari file KSP berhasil ditambahkan!',
							timer: 1500,
							showConfirmButton: false
						});

						if (response.no_bukti) {
							currentNoBukti = response.no_bukti;
							$('#no_bukti').val(response.no_bukti);
							$('#no_bukti_hidden').val(response.no_bukti);
						}

						setTimeout(function() {
							location.reload();
						}, 1500);
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					var errorMsg = 'Gagal menambah item dari KSP';
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

		function addItem() {
			var barcode = $('#barcode').val().trim();
			var kd_brg = $('#kd_brg').val().trim();
			var lph = parseFloat($('#lph').val()) || 0;
			var dtr = parseInt($('#dtr').val()) || 0;
			var no_sp = $('#no_sp').val().trim();

			if (!barcode && !kd_brg) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Barcode atau Kode barang harus diisi'
				});
				return;
			}

			if (lph <= 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'LPH harus lebih dari 0'
				});
				return;
			}

			if (dtr < 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'DTR tidak boleh negatif'
				});
				return;
			}

			// Check if item already exists in table
			var exists = false;
			$('#tableItems tbody tr').each(function() {
				var rowKode = $(this).find('td:eq(2)').text().trim();
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
					proceedAddItem(barcode, kd_brg, lph, dtr, no_sp);
				});
			} else {
				proceedAddItem(barcode, kd_brg, lph, dtr, no_sp);
			}
		}

		function proceedAddItem(barcode, kd_brg, lph, dtr, no_sp) {
			$.ajax({
				url: '{{ route('penambahanbarangbaru_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'add_item',
					no_bukti: currentNoBukti,
					barcode: barcode,
					kd_brg: kd_brg,
					lph: lph,
					dtr: dtr,
					no_sp: no_sp
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
						$('#barcode').focus();

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
			$.ajax({
				url: '{{ route('penambahanbarangbaru_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'save',
					status: 'simpan',
					no_bukti: '+',
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
				url: '{{ route('penambahanbarangbaru_proses') }}',
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
			var no_bukti = currentNoBukti;

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
				var barcode = $(this).find('td:eq(1)').text().trim();
				var kd_brg = $(this).find('td:eq(2)').text().trim();
				var na_brg = $(this).find('td:eq(3)').text().trim();
				var ket_uk = $(this).find('td:eq(4)').text().trim();
				var ket_kem = $(this).find('td:eq(5)').text().trim();
				var lph = parseFloat($(this).find('td:eq(6)').text().replace(/,/g, '')) || 0;
				var dtr = parseInt($(this).find('td:eq(7)').text().replace(/,/g, '')) || 0;
				var no_sp = $(this).find('td:eq(8)').text().trim();

				items.push({
					no_id: no_id,
					barcode: barcode,
					kd_brg: kd_brg,
					na_brg: na_brg,
					ket_uk: ket_uk,
					ket_kem: ket_kem,
					lph: lph,
					dtr: dtr,
					no_sp: no_sp
				});
			});

			$('#LOADX').show();

			$.ajax({
				url: '{{ route('penambahanbarangbaru_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'save',
					status: currentStatus,
					no_bukti: no_bukti,
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
								window.location.href = '{{ route('penambahanbarangbaru') }}';
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
			$('#barcode').val('');
			$('#kd_brg').val('');
			$('#lph').val('');
			$('#dtr').val('');
			$('#no_sp').val('');
			$('#label_nama_barang').text('Nama Barang akan muncul di sini');
		}
	</script>
@endsection
