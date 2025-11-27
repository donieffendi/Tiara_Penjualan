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
			margin-bottom: 20px;
		}

		.form-group label {
			font-weight: 600;
			margin-bottom: 8px;
			display: block;
			color: #333;
		}

		.form-control {
			border: 1px solid #ced4da;
			border-radius: 4px;
			padding: 8px 12px;
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
			padding: 12px 8px;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.table tbody td {
			padding: 8px;
			font-size: 13px;
		}

		.label-nama-barang {
			font-weight: 600;
			color: #007bff;
			padding: 10px;
			background: #f0f8ff;
			border: 1px solid #b3d9ff;
			border-radius: 4px;
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
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<!-- Info Box -->
								<div class="info-box">
									<p class="mb-1"><strong>Petunjuk:</strong></p>
									<ul class="mb-0">
										<li>Isi <strong>Kode HR</strong> untuk identifikasi hari raya</li>
										<li>Pilih <strong>Dari Tanggal</strong> dan <strong>Sampai Tanggal</strong> untuk periode berlakunya order</li>
										<li>Masukkan <strong>Sub Item</strong> (kode barang) atau klik browse jika kurang dari 7 digit</li>
										<li>Isi <strong>Order Lebih (%)</strong> untuk persentase kelebihan order</li>
										<li>Tekan ENTER untuk menambah item ke daftar</li>
										<li>Klik <strong>SAVE</strong> untuk menyimpan data</li>
									</ul>
								</div>

								<!-- Form Header -->
								<form id="formHeader">
									@csrf
									<input type="hidden" name="status" id="status" value="{{ $status }}">
									<input type="hidden" name="namafile" id="namafile" value="{{ $namafile }}">

									<div class="row">
										<div class="col-md-6">
											<!-- No Bukti -->
											<div class="form-group">
												<label for="no_bukti">No Bukti</label>
												<input type="text" class="form-control" id="no_bukti" value="{{ $no_bukti }}" readonly disabled>
											</div>

											<!-- Tanggal -->
											<div class="form-group">
												<label for="tanggal">Tanggal</label>
												<input type="text" class="form-control" id="tanggal" value="{{ date('d-m-Y') }}" readonly disabled>
											</div>

											<!-- Outlet -->
											<div class="form-group">
												<label for="outlet">Outlet / Cabang</label>
												<input type="text" class="form-control" id="outlet" value="{{ $cbg }}" readonly disabled>
											</div>
										</div>

										<div class="col-md-6">
											<!-- Kode HR -->
											<div class="form-group">
												<label for="kode_hr">Kode HR <span class="text-danger">*</span></label>
												<input type="text" class="form-control" id="kode_hr" name="kode_hr" value="{{ $kode_hr }}" required
													{{ $status === 'edit' ? 'readonly' : '' }}>
											</div>

											<!-- Dari Tanggal -->
											<div class="form-group">
												<label for="tgl_awal">Dari Tanggal (Berlaku Mulai) <span class="text-danger">*</span></label>
												<input type="date" class="form-control" id="tgl_awal" name="tgl_awal" value="{{ $tgl_awal }}" required>
											</div>

											<!-- Sampai Tanggal -->
											<div class="form-group">
												<label for="tgl_akhir">Sampai Tanggal (Berlaku Sampai) <span class="text-danger">*</span></label>
												<input type="date" class="form-control" id="tgl_akhir" name="tgl_akhir" value="{{ $tgl_akhir }}" required>
											</div>
										</div>
									</div>

									<hr class="my-4">

									<!-- Form Add Item -->
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label for="kd_brg">Sub Item (Kode Barang)</label>
												<input type="text" class="form-control" id="kd_brg" name="kd_brg" placeholder="Masukkan kode barang">
												<small class="form-text text-muted">Tekan Enter untuk cari (atau Ctrl+Enter untuk popup daftar)</small>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="per_ord">Order Lebih (%)</label>
												<input type="number" class="form-control text-right" id="per_ord" name="per_ord" step="0.01" min="0" max="100"
													value="0">
											</div>
										</div>
										<div class="col-md-5">
											<div class="form-group">
												<label>&nbsp;</label>
												<div>
													<button type="button" id="btnAddItem" class="btn btn-action btn-add-item">
														<i class="fas fa-plus"></i> Tambah Item
													</button>
												</div>
											</div>
										</div>
									</div>

									<!-- Nama Barang Display -->
									<div class="row">
										<div class="col-12">
											<div class="label-nama-barang" id="label_nama_barang">
												Nama Barang
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
													<th width="120px">Ket UK</th>
													<th width="80px" class="text-center">L/H</th>
													<th width="120px" class="text-right">Order Lebih (%)</th>
													<th width="80px" class="text-center">Aksi</th>
												</tr>
											</thead>
											<tbody>
												@if (!empty($detail))
													@foreach ($detail as $index => $item)
														<tr>
															<td class="text-center">{{ $index + 1 }}</td>
															<td>{{ $item->KD_BRG }}</td>
															<td>{{ $item->NMBAR }}</td>
															<td>{{ $item->KET_UK }}</td>
															<td class="text-center">{{ number_format($item->LPH, 2) }}</td>
															<td class="text-right">{{ number_format($item->PER_ORD, 2) }} %</td>
															<td class="text-center">
																<button class="btn btn-xs btn-danger btn-delete-item" data-id="{{ $item->NO_ID }}">
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
										<button type="button" id="btnSave" class="btn btn-action btn-save">
											<i class="fas fa-save"></i> SIMPAN
										</button>
										<button type="button" id="btnKirim" class="btn btn-action" style="background: #fd7e14; color: white;">
											<i class="fas fa-paper-plane"></i> KIRIM
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
		var table;
		var currentNamafile = '{{ $namafile }}';

		$(document).ready(function() {
			// Initialize DataTable
			table = $('#tableItems').DataTable({
				paging: false,
				searching: false,
				info: false,
				ordering: false
			});

			// Focus to kode_hr field if new
			@if ($status === 'simpan')
				$('#kode_hr').focus();
			@endif

			// Button Back
			$('#btnBack').on('click', function() {
				window.location.href = '{{ route('orderlebihharirayaonline') }}';
			});

			// Kode Barang - Ctrl+Enter for popup lookup
			$('#kd_brg').on('keydown', function(e) {
				if (e.ctrlKey && e.keyCode === 13) {
					e.preventDefault();
					lookupBarang();
					return;
				}

				if (e.key === 'Enter') {
					e.preventDefault();
					var kode = $(this).val().trim();

					if (kode.length < 7) {
						Swal.fire({
							icon: 'info',
							title: 'Informasi',
							text: 'Silakan gunakan fitur popup (Ctrl+Enter) untuk memilih barang'
						});
						return;
					}

					searchBarang(kode);
				}
			});

			// Per Order - Enter key handler
			$('#per_ord').on('keydown', function(e) {
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

			// Button Kirim
			$('#btnKirim').on('click', function() {
				Swal.fire({
					icon: 'info',
					title: 'Kirim File',
					text: 'Fitur kirim file dalam pengembangan'
				});
			});

			// Button Delete Item
			$(document).on('click', '.btn-delete-item', function() {
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

		function searchBarang(kode) {
			$('#LOADX').show();

			$.ajax({
				url: '/api/search-barang',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					kd_brg: kode
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data) {
						var barang = response.data;
						var namaLengkap = barang.NA_BRG + ' ' + barang.KET_UK + '  ';
						$('#label_nama_barang').text(namaLengkap);
						$('#per_ord').focus();
					} else {
						$('#label_nama_barang').text('SubItem Tidak Ditemukan.');
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'SubItem Tidak Ditemukan'
						});
					}
				},
				error: function() {
					$('#LOADX').hide();
					$('#label_nama_barang').text('SubItem Tidak Ditemukan.');
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'SubItem Tidak Ditemukan'
					});
				}
			});
		}

		function addItem() {
			var kd_brg = $('#kd_brg').val().trim();
			var per_ord = parseFloat($('#per_ord').val()) || 0;
			var kode_hr = $('#kode_hr').val().trim();
			var tgl_awal = $('#tgl_awal').val();
			var tgl_akhir = $('#tgl_akhir').val();

			// Validasi
			if (!kode_hr) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Kode HR harus diisi terlebih dahulu'
				});
				return;
			}

			if (!kd_brg) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Kode barang harus diisi'
				});
				return;
			}

			if (per_ord <= 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Order Lebih (%) harus lebih dari 0'
				});
				return;
			}

			if (!tgl_awal || !tgl_akhir) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Tanggal mulai dan sampai harus diisi'
				});
				return;
			}

			$('#LOADX').show();
			$('#btnAddItem').prop('disabled', true);

			$.ajax({
				url: '{{ route('orderlebihharirayaonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'add_item',
					namafile: currentNamafile,
					kd_brg: kd_brg,
					per_ord: per_ord,
					kode_hr: kode_hr,
					tgl_awal: tgl_awal,
					tgl_akhir: tgl_akhir
				},
				success: function(response) {
					$('#LOADX').hide();
					$('#btnAddItem').prop('disabled', false);

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 1000,
							showConfirmButton: false
						});

						// Update namafile jika baru dibuat
						if (response.namafile) {
							currentNamafile = response.namafile;
							$('#namafile').val(response.namafile);
							$('#no_bukti').val(response.namafile);
							$('#kode_hr').prop('readonly', true);
						}

						// Clear form
						$('#kd_brg').val('');
						$('#per_ord').val('0');
						$('#label_nama_barang').text('Nama Barang');
						$('#kd_brg').focus();

						// Reload page to refresh table
						setTimeout(function() {
							location.reload();
						}, 1000);
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					$('#btnAddItem').prop('disabled', false);

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

		function deleteItem(no_id) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('orderlebihharirayaonline_proses') }}',
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
			var kode_hr = $('#kode_hr').val().trim();
			var tgl_awal = $('#tgl_awal').val();
			var tgl_akhir = $('#tgl_akhir').val();

			// Validasi
			if (!kode_hr) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Kode HR harus diisi'
				});
				return;
			}

			if (!tgl_awal || !tgl_akhir) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Tanggal mulai dan sampai harus diisi'
				});
				return;
			}

			if (new Date(tgl_akhir) < new Date(tgl_awal)) {
				Swal.fire({
					icon: 'warning',
					title: 'Validasi',
					text: 'Tanggal sampai tidak boleh lebih kecil dari tanggal mulai'
				});
				return;
			}

			$('#LOADX').show();
			$('#btnSave').prop('disabled', true);

			$.ajax({
				url: '{{ route('orderlebihharirayaonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'save',
					status: $('#status').val(),
					namafile: $('#namafile').val(),
					kode_hr: kode_hr,
					tgl_awal: tgl_awal,
					tgl_akhir: tgl_akhir
				},
				success: function(response) {
					$('#LOADX').hide();
					$('#btnSave').prop('disabled', false);

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							showConfirmButton: true,
							confirmButtonText: 'OK'
						}).then((result) => {
							if (result.isConfirmed) {
								window.location.href = '{{ route('orderlebihharirayaonline') }}';
							}
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					$('#btnSave').prop('disabled', false);

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

		function lookupBarang() {
			console.log('lookupBarang() called');
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('orderlebihharirayaonline_lookup_barang') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					$('#LOADX').hide();
					console.log('Lookup response:', response);

					if (response.success && response.data.length > 0) {
						var tableRows = '';
						response.data.forEach(function(item) {
							tableRows += '<tr class="barang-row" data-kode="' + item.kd_brg + '">';
							tableRows += '<td>' + item.kd_brg + '</td>';
							tableRows += '<td>' + item.na_brg + '</td>';
							tableRows += '<td class="text-center">' + (item.ket_uk || '-') + '</td>';
							tableRows += '<td class="text-center">' + (item.ket_kem || '-') + '</td>';
							tableRows += '<td class="text-right">' + (item.LPH ? parseFloat(item.LPH).toFixed(2) : '0.00') + '</td>';
							tableRows += '<td class="text-center"><button class="btn btn-sm btn-primary btn-pilih" data-kode="' + item
								.kd_brg + '">Pilih</button></td>';
							tableRows += '</tr>';
						});

						Swal.fire({
							title: 'Daftar Barang Fresh Food (Kode 3)',
							html: `
								<div style="margin-bottom: 10px; position: sticky; top: 0; background: white; z-index: 10; padding: 10px 0;">
									<input type="text" id="searchBarang" class="form-control" placeholder="Cari barang..." style="width: 100%;">
								</div>
								<div style="max-height: 400px; overflow-y: auto;">
									<table class="table table-bordered table-striped table-hover" style="width: 100%; font-size: 12px;">
										<thead style="position: sticky; top: 0; background: #343a40; z-index: 5;">
											<tr>
												<th style="color: white;">Kode</th>
												<th style="color: white;">Nama Barang</th>
												<th style="color: white;">Ukuran</th>
												<th style="color: white;">Kemasan</th>
												<th style="color: white;">L/H</th>
												<th style="color: white;">Aksi</th>
											</tr>
										</thead>
										<tbody>
											` + tableRows + `
										</tbody>
									</table>
								</div>
							`,
							width: '1000px',
							showConfirmButton: false,
							showCloseButton: true,
							didOpen: () => {
								// Search functionality
								$('#searchBarang').on('keyup', function() {
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

								// Button pilih handler
								$('.btn-pilih').on('click', function() {
									var kode = $(this).data('kode');
									console.log('Selected kode:', kode);
									$('#kd_brg').val(kode);
									Swal.close();
									// Auto search barang info
									searchBarang(kode);
								});

								// Focus search box
								$('#searchBarang').focus();
							}
						});
					} else {
						Swal.fire({
							icon: 'info',
							title: 'Informasi',
							text: 'Tidak ada data barang fresh food'
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					console.error('Lookup error:', xhr);
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal mengambil data barang'
					});
				}
			});
		}
	</script>
@endsection
