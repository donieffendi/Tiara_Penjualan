@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<style>
		.card {
			padding: 15px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		}

		.form-control:focus {
			background-color: #e3f2fd;
			border-color: #2196F3;
		}

		.btn-action {
			padding: 8px 20px;
			font-weight: 600;
			font-size: 14px;
			margin: 0 3px;
		}

		.btn-tampil {
			background: #2196F3;
			border: none;
			color: #fff;
		}

		.btn-tampil:hover {
			background: #1976D2;
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
			position: sticky;
			top: 0;
			z-index: 10;
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

		.status-label {
			display: inline-block;
			padding: 5px 10px;
			border-radius: 3px;
			font-size: 12px;
			font-weight: 600;
		}

		.status-info {
			background: #d1ecf1;
			color: #0c5460;
			border: 1px solid #bee5eb;
		}

		.form-group label {
			font-weight: 600;
			margin-bottom: 5px;
		}

		.panel-filter {
			background: #f8f9fa;
			padding: 20px;
			border-radius: 5px;
			margin-bottom: 20px;
			border: 1px solid #dee2e6;
		}

		.badge-success {
			background-color: #28a745;
			color: white;
			padding: 5px 10px;
			border-radius: 3px;
		}

		.badge-warning {
			background-color: #ffc107;
			color: #212529;
			padding: 5px 10px;
			border-radius: 3px;
		}

		#pnlFF,
		#pnlSub {
			display: none;
		}

		.table-responsive {
			max-height: 500px;
			overflow-y: auto;
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
							<span class="status-label status-info">
								<i class="fas fa-building"></i> {{ $cbg ?? '-' }}
							</span>
							<span class="status-label status-info ml-2">
								<i class="fas fa-calendar"></i> {{ $periode ?? '-' }}
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

				<!-- Panel Filter -->
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<div class="panel-filter">
									<div class="row">
										<div class="col-md-3">
											<div class="form-group">
												<label for="cbJenis">Jenis</label>
												<select class="form-control" id="cbJenis" name="jenis">
													<option value="BIASA" selected>BIASA</option>
													<option value="FF">FF (Fast Forward)</option>
												</select>
											</div>
										</div>

										<!-- Panel BIASA -->
										<div class="col-md-9" id="pnlBiasa">
											<div class="row">
												<div class="col-md-3">
													<div class="form-group">
														<label for="txtKode">Kode</label>
														<input type="text" class="form-control" id="txtKode" name="kode" placeholder="Kode Barang / UH/UK/UD/US">
													</div>
												</div>
												<div class="col-md-2">
													<div class="form-group">
														<label for="txtKali">Kali</label>
														<input type="number" class="form-control" id="txtKali" name="kali" value="1" min="1">
													</div>
												</div>
												<div class="col-md-2">
													<div class="form-group">
														<label>&nbsp;</label>
														<button type="button" id="btnSub" class="btn btn-secondary btn-block">
															<i class="fas fa-list"></i> Per Sub
														</button>
													</div>
												</div>
												<div class="col-md-3" id="pnlSub">
													<div class="form-group">
														<label for="txtSub">Sub</label>
														<input type="text" class="form-control" id="txtSub" name="sub" placeholder="3 digit kode sub">
													</div>
												</div>
												<div class="col-md-2">
													<div class="form-group">
														<label>&nbsp;</label>
														<button type="button" id="btnTampilBiasa" class="btn btn-action btn-tampil btn-block">
															<i class="fas fa-search"></i> Tampilkan
														</button>
													</div>
												</div>
											</div>
										</div>

										<!-- Panel FF (Fast Forward) -->
										<div class="col-md-9" id="pnlFF">
											<div class="row">
												<div class="col-md-4">
													<div class="form-group">
														<label for="dtTanggal">Tanggal</label>
														<input type="date" class="form-control" id="dtTanggal" name="tanggal" value="{{ date('Y-m-d') }}">
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label>&nbsp;</label>
														<button type="button" id="btnTampilFF" class="btn btn-action btn-tampil btn-block">
															<i class="fas fa-search"></i> Tampilkan
														</button>
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

				<!-- Tabel Data -->
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-center mb-3">
									<h5><i class="fas fa-tags"></i> Data Label Harga</h5>
									<div>
										<button type="button" id="btnProses" class="btn btn-action btn-proses">
											<i class="fas fa-print"></i> CETAK
										</button>
										<button type="button" id="btnClear" class="btn btn-action btn-clear">
											<i class="fas fa-eraser"></i> CLEAR
										</button>
									</div>
								</div>

								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">No</th>
												<th width="120px">No Bukti</th>
												<th width="120px">Kode</th>
												<th>Uraian</th>
												<th width="100px" class="text-right">Harga</th>
												<th width="100px" class="text-right">LPH</th>
												<th width="100px" class="text-right">DTR</th>
												<th width="100px" class="text-center">On DC</th>
												<th width="150px">Barcode</th>
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
		var tableData;

		$(document).ready(function() {
			// Initialize DataTable
			tableData = $('#tableData').DataTable({
				ajax: {
					url: '{{ route('cetaklabelharga_cari') }}',
					type: 'POST',
					data: function(d) {
						d._token = '{{ csrf_token() }}';
						d.jenis = $('#cbJenis').val();
						d.kode = $('#txtKode').val();
						d.sub = $('#txtSub').val();
						d.tanggal = $('#dtTanggal').val();
						d.kali = $('#txtKali').val();
					},
					error: handleAjaxError
				},
				columns: [{
						data: 'DT_RowIndex',
						name: 'DT_RowIndex',
						orderable: false,
						searchable: false,
						className: 'text-center'
					},
					{
						data: 'NO_BUKTI',
						name: 'NO_BUKTI',
						defaultContent: '-'
					},
					{
						data: 'kode',
						name: 'kode'
					},
					{
						data: 'uraian',
						name: 'uraian'
					},
					{
						data: 'hjbr',
						name: 'hjbr',
						className: 'text-right'
					},
					{
						data: 'lph',
						name: 'lph',
						className: 'text-right'
					},
					{
						data: 'dtr',
						name: 'dtr',
						className: 'text-right'
					},
					{
						data: 'ON_DC',
						name: 'ON_DC',
						className: 'text-center'
					},
					{
						data: 'BARCODE',
						name: 'BARCODE',
						defaultContent: '-'
					}
				],
				order: [
					[2, 'asc']
				],
				processing: true,
				serverSide: true,
				pageLength: 25,
				lengthMenu: [
					[10, 25, 50, 100, -1],
					[10, 25, 50, 100, "Semua"]
				],
				language: getDataTableLanguage(),
				autoWidth: false
			});

			// Toggle panel berdasarkan jenis
			$('#cbJenis').on('change', function() {
				var jenis = $(this).val();
				if (jenis === 'FF') {
					$('#pnlBiasa').hide();
					$('#pnlFF').show();
				} else {
					$('#pnlBiasa').show();
					$('#pnlFF').hide();
				}
			});

			// Button Sub - Show/Hide input sub
			$('#btnSub').on('click', function() {
				$('#pnlSub').toggle();
				if ($('#pnlSub').is(':visible')) {
					$('#txtSub').focus();
				}
			});

			// Button Tampil Biasa
			$('#btnTampilBiasa').on('click', function() {
				var kode = $('#txtKode').val().trim();
				var sub = $('#txtSub').val().trim();

				if (!kode && !sub) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Masukkan Kode atau Sub terlebih dahulu!'
					});
					return;
				}

				loadData();
			});

			// Button Tampil FF
			$('#btnTampilFF').on('click', function() {
				var tanggal = $('#dtTanggal').val();

				if (!tanggal) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Pilih tanggal terlebih dahulu!'
					});
					return;
				}

				loadData();
			});

			// Enter key handler for txtKode
			$('#txtKode').on('keypress', function(e) {
				if (e.which === 13) {
					$('#btnTampilBiasa').click();
				}
			});

			// Enter key handler for txtSub
			$('#txtSub').on('keypress', function(e) {
				if (e.which === 13) {
					$('#btnTampilBiasa').click();
				}
			});

			// Button Proses (Cetak)
			$('#btnProses').on('click', function() {
				var rowCount = tableData.rows().count();

				if (rowCount === 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Tidak ada data untuk dicetak!'
					});
					return;
				}

				Swal.fire({
					title: 'Konfirmasi Cetak',
					text: 'Cetak ' + rowCount + ' label harga?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#28a745',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-print"></i> Ya, Cetak!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						prosesData('cetak');
					}
				});
			});

			// Button Clear
			$('#btnClear').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi',
					text: 'Hapus semua data dari tabel?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#dc3545',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Hapus!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						clearData();
					}
				});
			});
		});

		function loadData() {
			$('#LOADX').show();
			tableData.ajax.reload(function() {
				$('#LOADX').hide();
				var rowCount = tableData.rows().count();

				if (rowCount > 0) {
					Swal.fire({
						icon: 'success',
						title: 'Berhasil',
						text: rowCount + ' data berhasil dimuat',
						timer: 1500,
						showConfirmButton: false
					});
				} else {
					Swal.fire({
						icon: 'info',
						title: 'Informasi',
						text: 'Data tidak ditemukan'
					});
				}
			}, true);
		}

		function prosesData(action) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('cetaklabelharga_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: action,
					jenis: $('#cbJenis').val(),
					kode: $('#txtKode').val(),
					sub: $('#txtSub').val(),
					tanggal: $('#dtTanggal').val(),
					kali: $('#txtKali').val()
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 2000,
							showConfirmButton: false
						});

						if (response.redirect) {
							window.open(response.redirect, '_blank');
						}
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal memproses data'
					});
				}
			});
		}

		function clearData() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('cetaklabelharga_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'clear'
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						// Clear form inputs
						$('#txtKode').val('');
						$('#txtSub').val('');
						$('#txtKali').val('1');
						$('#pnlSub').hide();

						// Reload empty table
						tableData.clear().draw();

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
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal membersihkan data'
					});
				}
			});
		}

		function handleAjaxError(xhr, error, code) {
			console.error('DataTables Ajax error:', xhr.responseText);

			if (xhr.status === 500 || xhr.status === 400) {
				var errorMsg = 'Terjadi kesalahan saat memuat data.';

				try {
					var response = JSON.parse(xhr.responseText);
					if (response.error) {
						errorMsg = response.error;
					}
				} catch (e) {
					errorMsg = 'Tabel database mungkin belum ada atau terjadi kesalahan koneksi.';
				}

				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					html: errorMsg + '<br><br><small>Silakan hubungi administrator jika masalah berlanjut.</small>',
					confirmButtonText: 'OK'
				});
			}
		}

		function getDataTableLanguage() {
			return {
				processing: "Memuat data...",
				lengthMenu: "Tampilkan _MENU_ data",
				zeroRecords: "Data tidak ditemukan",
				info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
				infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
				infoFiltered: "(disaring dari _MAX_ total data)",
				search: "Cari:",
				paginate: {
					first: "Pertama",
					last: "Terakhir",
					next: "Selanjutnya",
					previous: "Sebelumnya"
				}
			};
		}
	</script>
@endsection
