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

		.btn-refresh {
			background: #17a2b8;
			border: none;
			color: #fff;
		}

		.btn-refresh:hover {
			background: #138496;
			color: #fff;
		}

		.btn-excel {
			background: #28a745;
			border: none;
			color: #fff;
		}

		.btn-excel:hover {
			background: #218838;
			color: #fff;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 13px;
			padding: 12px 8px;
			text-align: center;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.table tbody td {
			padding: 8px;
			font-size: 13px;
			vertical-align: middle;
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

		.form-filter-group {
			background: #f8f9fa;
			border: 1px solid #dee2e6;
			padding: 20px;
			border-radius: 5px;
			margin-bottom: 20px;
		}

		.form-filter-group .form-group {
			margin-bottom: 15px;
		}

		.form-filter-group label {
			font-weight: 600;
			margin-bottom: 5px;
			color: #495057;
		}

		.editable-tarik,
		.editable-masa-exp,
		.editable-tarik-tipe {
			border: 1px solid #ced4da;
			padding: 4px 8px;
		}

		.editable-tarik:focus,
		.editable-masa-exp:focus,
		.editable-tarik-tipe:focus {
			background-color: #fff3cd;
			border-color: #ffc107;
			outline: none;
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
								<!-- Info Box -->
								<div class="info-box">
									<p class="mb-1"><strong>Petunjuk:</strong></p>
									<ul class="mb-0">
										<li>Pilih <strong>Outlet</strong> dan <strong>Sub Item</strong>, lalu tekan Enter atau klik <strong>REFRESH</strong> untuk menampilkan data
										</li>
										<li>Edit langsung pada kolom <strong>Type</strong>, <strong>Tarik</strong>, atau <strong>Masa Exp</strong>, data akan otomatis tersimpan dan
											tersinkronisasi ke semua outlet</li>
										<li>Klik <strong>EXCEL</strong> untuk export data ke Excel</li>
									</ul>
								</div>

								<!-- Form Filter -->
								<div class="form-filter-group">
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label for="outlet">Outlet <span class="text-danger">*</span></label>
												<select class="form-control" id="outlet">
													<option value="">-- Pilih Outlet --</option>
													@if (isset($outlets))
														@foreach ($outlets as $o)
															<option value="{{ $o->KODE }}" {{ isset($cbg) && $cbg == $o->KODE ? 'selected' : '' }}>
																{{ $o->NAMA_LENGKAP }}
															</option>
														@endforeach
													@endif
												</select>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="sub">Sub Item <span class="text-danger">*</span></label>
												<input type="text" class="form-control" id="sub" placeholder="Masukkan Sub Item">
												<small class="form-text text-muted">Tekan Enter untuk menampilkan data</small>
											</div>
										</div>
										<div class="col-md-4">
											<label>&nbsp;</label>
											<div>
												<button type="button" id="btnRefresh" class="btn btn-action btn-refresh">
													<i class="fas fa-sync"></i> REFRESH
												</button>
												<button type="button" id="btnExcel" class="btn btn-action btn-excel">
													<i class="fas fa-file-excel"></i> EXCEL
												</button>
											</div>
										</div>
									</div>
								</div>

								<hr>

								<!-- Data Table -->
								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="50px">No</th>
												<th width="100px">Sub Item</th>
												<th width="120px">Kode Barang</th>
												<th>Nama Barang</th>
												<th width="120px">Ukuran</th>
												<th width="120px">Kemasan</th>
												<th width="100px">Type</th>
												<th width="80px">Tarik</th>
												<th width="80px">Masa Exp</th>
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
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
	<script>
		var table;
		var currentOutlet = '';
		var currentSub = '';

		$(document).ready(function() {
			// Initialize DataTable (tanpa auto load)
			table = $('#tableData').DataTable({
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
						data: 'kd_brg'
					},
					{
						data: 'na_brg'
					},
					{
						data: 'ket_uk',
						className: 'text-center'
					},
					{
						data: 'ket_kem',
						className: 'text-center'
					},
					{
						data: 'tarik_tipe',
						className: 'text-center'
					},
					{
						data: 'tarik',
						className: 'text-center'
					},
					{
						data: 'masa_exp',
						className: 'text-center'
					}
				],
				order: [
					[2, 'asc']
				],
				processing: true,
				pageLength: 25,
				serverSide: false
			});

			// Focus ke input sub
			$('#sub').focus();

			// Handle Enter key pada input sub
			$('#sub').on('keypress', function(e) {
				if (e.which === 13) {
					e.preventDefault();
					loadData();
				}
			});

			// Button Refresh
			$('#btnRefresh').on('click', function() {
				loadData();
			});

			// Button Excel
			$('#btnExcel').on('click', function() {
				exportExcel();
			});

			// Handle perubahan pada field Tarik
			$(document).on('change blur', '.editable-tarik', function() {
				var kd_brg = $(this).data('kd_brg');
				var tarik = $(this).val();
				updateField('update_tarik', kd_brg, tarik, 'tarik');
			});

			// Handle perubahan pada field Masa Exp
			$(document).on('change blur', '.editable-masa-exp', function() {
				var kd_brg = $(this).data('kd_brg');
				var masa_exp = $(this).val();
				updateField('update_masa_exp', kd_brg, masa_exp, 'masa_exp');
			});

			// Handle perubahan pada field Tarik Tipe
			$(document).on('change blur', '.editable-tarik-tipe', function() {
				var kd_brg = $(this).data('kd_brg');
				var tarik_tipe = $(this).val();
				updateField('update_tarik_tipe', kd_brg, tarik_tipe, 'tarik_tipe');
			});
		});

		function loadData() {
			var outlet = $('#outlet').val().trim();
			var sub = $('#sub').val().trim();

			if (outlet === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Outlet tidak boleh kosong!'
				});
				$('#outlet').focus();
				return;
			}

			if (sub === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Sub Item tidak boleh kosong!'
				});
				$('#sub').focus();
				return;
			}

			currentOutlet = outlet;
			currentSub = sub;

			$('#LOADX').show();

			// Reload datatable dengan ajax
			if (table) {
				table.destroy();
			}

			table = $('#tableData').DataTable({
				ajax: {
					url: '{{ route('masatarik_cari') }}',
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						sub: sub
					},
					error: function(xhr) {
						$('#LOADX').hide();
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: xhr.responseJSON?.error || 'Gagal memuat data'
						});
					},
					complete: function() {
						$('#LOADX').hide();
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
						data: 'sub'
					},
					{
						data: 'kd_brg'
					},
					{
						data: 'na_brg'
					},
					{
						data: 'ket_uk',
						className: 'text-center'
					},
					{
						data: 'ket_kem',
						className: 'text-center'
					},
					{
						data: 'tarik_tipe',
						className: 'text-center'
					},
					{
						data: 'tarik',
						className: 'text-center'
					},
					{
						data: 'masa_exp',
						className: 'text-center'
					}
				],
				order: [
					[2, 'asc']
				],
				processing: true,
				pageLength: 25,
				serverSide: true
			});
		}

		function updateField(action, kd_brg, value, fieldName) {
			if (currentOutlet === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Outlet tidak boleh kosong!'
				});
				return;
			}

			var requestData = {
				_token: '{{ csrf_token() }}',
				action: action,
				outlet: currentOutlet,
				kd_brg: kd_brg
			};

			// Set parameter sesuai action
			if (action === 'update_tarik') {
				requestData.tarik = value;
			} else if (action === 'update_masa_exp') {
				requestData.masa_exp = value;
			} else if (action === 'update_tarik_tipe') {
				requestData.tarik_tipe = value;
			}

			$.ajax({
				url: '{{ route('masatarik_proses') }}',
				type: 'POST',
				data: requestData,
				success: function(response) {
					if (response.success) {
						// Tampilkan notifikasi sukses singkat
						const Toast = Swal.mixin({
							toast: true,
							position: 'top-end',
							showConfirmButton: false,
							timer: 2000,
							timerProgressBar: true
						});

						Toast.fire({
							icon: 'success',
							title: response.message
						});
					}
				},
				error: function(xhr) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal menyimpan data'
					});
				}
			});
		}

		function exportExcel() {
			var outlet = $('#outlet').val().trim();
			var sub = $('#sub').val().trim();

			if (outlet === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Outlet tidak boleh kosong!'
				});
				return;
			}

			if (sub === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Sub Item tidak boleh kosong!'
				});
				return;
			}

			$('#LOADX').show();

			$.ajax({
				url: '{{ route('masatarik_cari_semua') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					sub: sub
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
								wch: 40
							}, // Nama Barang
							{
								wch: 15
							}, // Ukuran
							{
								wch: 15
							}, // Kemasan
							{
								wch: 10
							}, // Type
							{
								wch: 10
							}, // Tarik
							{
								wch: 10
							} // Masa Exp
						];

						// Create workbook
						var wb = XLSX.utils.book_new();
						XLSX.utils.book_append_sheet(wb, ws, "Masa Tarik");

						// Generate filename with timestamp
						var filename = 'Masa_Tarik_' + sub + '_' + new Date().toISOString().slice(0, 10) + '.xlsx';

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
	</script>
@endsection
