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

		.btn-tampil {
			background: #17a2b8;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-tampil:hover {
			background: #138496;
			color: #fff;
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

		.btn-export {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-export:hover {
			background: #218838;
			color: #fff;
		}

		.btn-import {
			background: #ffc107;
			border: none;
			color: #212529;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-import:hover {
			background: #e0a800;
			color: #212529;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 12px;
			padding: 10px 6px;
			text-align: center;
			vertical-align: middle;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.table tbody td {
			padding: 6px;
			font-size: 12px;
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

		.text-right-col {
			text-align: right !important;
		}

		.text-center-col {
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

		.form-section {
			background: #f8f9fa;
			border: 1px solid #dee2e6;
			padding: 20px;
			border-radius: 5px;
			margin-bottom: 20px;
		}

		.badge-cbg {
			font-size: 16px;
			padding: 8px 15px;
			border-radius: 5px;
		}

		.table-wrapper {
			overflow-x: auto;
		}

		.input-dtb {
			width: 80px;
			text-align: right;
			padding: 4px 6px;
			font-size: 12px;
		}

		.checkbox-cek {
			width: 18px;
			height: 18px;
			cursor: pointer;
		}

		.changed-row {
			background-color: #fff3cd !important;
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
					<div class="col-sm-6 text-right">
						@if (isset($cbg))
							<span class="badge badge-cbg badge-primary">CBG: {{ $cbg ?? '-' }}</span>
							<span class="badge badge-cbg badge-info">Periode: {{ $periode ?? '-' }}</span>
						@endif
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
										<li>Pilih kriteria <strong>DTB</strong> (Ada/Kosong) dan <strong>Sub Item</strong></li>
										<li>Klik <strong>TAMPILKAN</strong> untuk menampilkan data barang</li>
										<li>Edit kolom <strong>DTB Baru</strong> sesuai kebutuhan</li>
										<li>Centang checkbox untuk memilih item yang akan diupdate</li>
										<li>Klik <strong>PROSES</strong> untuk menyimpan perubahan DTB</li>
										<li>Gunakan <strong>IMPORT EXCEL</strong> untuk import DTB dari file Excel (format: SUBITEM | DTB)</li>
										<li>Gunakan <strong>EXPORT EXCEL</strong> untuk export data ke file Excel</li>
									</ul>
								</div>

								<!-- Filter Section -->
								<div class="form-section">
									<div class="row">
										<div class="col-md-3">
											<label for="cbDTB"><strong>Filter DTB:</strong></label>
											<select class="form-control" id="cbDTB">
												<option value="ADA" selected>Ada</option>
												<option value="KOSONG">Kosong</option>
												<option value="SEMUA">Semua</option>
											</select>
										</div>
										<div class="col-md-3">
											<label for="txtSub"><strong>Sub Item:</strong></label>
											<input type="text" class="form-control" id="txtSub" placeholder="Contoh: 151" value="151">
											<small class="form-text text-muted">Kosongkan untuk tampilkan semua sub</small>
										</div>
									</div>
								</div>

								<!-- Action Buttons -->
								<div class="mb-3">
									<button type="button" id="btnTampil" class="btn btn-tampil">
										<i class="fas fa-eye"></i> TAMPILKAN
									</button>
									<button type="button" id="btnProses" class="btn btn-proses">
										<i class="fas fa-save"></i> PROSES
									</button>
									<button type="button" id="btnExport" class="btn btn-export">
										<i class="fas fa-file-excel"></i> EXPORT EXCEL
									</button>
									<button type="button" id="btnImport" class="btn btn-import">
										<i class="fas fa-file-upload"></i> IMPORT EXCEL
									</button>
									<input type="file" id="fileImport" accept=".xls,.xlsx" style="display: none;">
								</div>

								<hr>

								<!-- DataTable -->
								<div class="table-wrapper">
									<table class="table-striped table-bordered table-hover table" id="tableDTB" style="width:100%">
										<thead>
											<tr>
												<th width="30px">
													<input type="checkbox" id="checkAll" class="checkbox-cek">
												</th>
												<th width="40px">No</th>
												<th width="60px">Sub</th>
												<th width="80px">Item</th>
												<th width="200px">Nama Barang</th>
												<th width="80px">Kemasan</th>
												<th width="80px">Ukuran</th>
												<th width="80px">LPH</th>
												<th width="80px">DTB</th>
												<th width="80px">DTB Baru</th>
												<th width="80px">DTR</th>
												<th width="80px">DTR Ideal</th>
												<th width="80px">DTR2</th>
												<th width="100px">TD OD</th>
												<th width="100px">Cat OD</th>
												<th width="100px">Tgl OD</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td colspan="16" class="text-center">Klik tombol TAMPILKAN untuk menampilkan data</td>
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
		var tableDTB;
		var dataDTB = [];

		$(document).ready(function() {
			// Initialize DataTable
			initTable();

			// Button Tampil
			$('#btnTampil').on('click', function() {
				tampilData();
			});

			// Button Proses
			$('#btnProses').on('click', function() {
				prosesData();
			});

			// Button Export
			$('#btnExport').on('click', function() {
				exportExcel();
			});

			// Button Import
			$('#btnImport').on('click', function() {
				$('#fileImport').click();
			});

			// File Import Change
			$('#fileImport').on('change', function() {
				if (this.files.length > 0) {
					importExcel(this.files[0]);
				}
			});

			// Check All
			$('#checkAll').on('change', function() {
				$('.checkbox-item').prop('checked', $(this).is(':checked'));
				updateCheckStatus();
			});

			// Check Item Individual
			$(document).on('change', '.checkbox-item', function() {
				var index = $(this).data('index');
				if (dataDTB[index]) {
					dataDTB[index].cek = $(this).is(':checked') ? 1 : 0;
				}
				updateCheckAllStatus();
			});

			// Input DTB Baru Change
			$(document).on('change', '.input-dtb-baru', function() {
				var index = $(this).data('index');
				var newValue = parseFloat($(this).val()) || 0;
				var oldValue = dataDTB[index].dtb;

				dataDTB[index].dtb_baru = newValue;

				// Auto check jika nilai berubah
				if (newValue != oldValue) {
					dataDTB[index].cek = 1;
					$('.checkbox-item[data-index="' + index + '"]').prop('checked', true);
					$(this).closest('tr').addClass('changed-row');
				} else {
					dataDTB[index].cek = 0;
					$('.checkbox-item[data-index="' + index + '"]').prop('checked', false);
					$(this).closest('tr').removeClass('changed-row');
				}

				updateCheckAllStatus();
			});
		});

		function initTable() {
			tableDTB = $('#tableDTB').DataTable({
				data: [],
				columns: [{
						data: null,
						orderable: false,
						className: 'text-center',
						render: function(data, type, row, meta) {
							return '<input type="checkbox" class="checkbox-item checkbox-cek" data-index="' + meta.row + '">';
						}
					},
					{
						data: null,
						render: function(data, type, row, meta) {
							return meta.row + 1;
						},
						className: 'text-center'
					},
					{
						data: 'sub',
						className: 'text-center'
					},
					{
						data: 'item',
						className: 'text-center'
					},
					{
						data: 'na_brg'
					},
					{
						data: 'ket_kem',
						className: 'text-center'
					},
					{
						data: 'ket_uk',
						className: 'text-center'
					},
					{
						data: 'lph',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: 'dtb',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: null,
						className: 'text-center',
						render: function(data, type, row, meta) {
							return '<input type="number" class="form-control input-dtb input-dtb-baru" data-index="' + meta.row +
								'" value="' + (row.dtb_baru || 0) + '" step="0.01">';
						}
					},
					{
						data: 'dtr',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: 'dtr_ideal',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: 'dtr2',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: 'td_od',
						className: 'text-center'
					},
					{
						data: 'cat_od',
						className: 'text-center'
					},
					{
						data: 'tgl_od',
						className: 'text-center'
					}
				],
				paging: true,
				pageLength: 50,
				lengthMenu: [
					[25, 50, 100, -1],
					[25, 50, 100, "Semua"]
				],
				searching: true,
				ordering: true,
				info: true,
				scrollX: true
			});
		}

		function tampilData() {
			var filterDTB = $('#cbDTB').val();
			var filterSub = $('#txtSub').val();

			$('#LOADX').show();

			$.ajax({
				url: '{{ route('updatedtb_cari') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					filter_dtb: filterDTB,
					filter_sub: filterSub
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data.length > 0) {
						dataDTB = response.data;

						tableDTB.clear();
						tableDTB.rows.add(dataDTB);
						tableDTB.draw();

						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil dimuat. Total: ' + response.count + ' item.',
							timer: 2000,
							showConfirmButton: false
						});
					} else {
						dataDTB = [];
						tableDTB.clear().draw();
						Swal.fire({
							icon: 'info',
							title: 'Informasi',
							text: 'Tidak ada data untuk ditampilkan'
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal mengambil data'
					});
				}
			});
		}

		function prosesData() {
			if (dataDTB.length === 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Tidak ada data untuk diproses! Klik TAMPILKAN terlebih dahulu.'
				});
				return;
			}

			// Hitung berapa item yang tercentang
			var checkedCount = dataDTB.filter(item => item.cek == 1).length;

			if (checkedCount === 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Tidak ada item yang dipilih untuk diupdate!'
				});
				return;
			}

			Swal.fire({
				title: 'Konfirmasi Update DTB',
				html: 'Yakin mengubah DTB untuk <strong>' + checkedCount + '</strong> item yang terpilih?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#007bff',
				cancelButtonColor: '#6c757d',
				confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses!',
				cancelButtonText: '<i class="fas fa-times"></i> Batal',
				showLoaderOnConfirm: true,
				preConfirm: () => {
					return prosesUpdate();
				},
				allowOutsideClick: () => !Swal.isLoading()
			}).then((result) => {
				if (result.isConfirmed && result.value.success) {
					Swal.fire({
						icon: 'success',
						title: 'Berhasil',
						html: result.value.message,
						showConfirmButton: true
					}).then(() => {
						// Refresh data setelah proses
						tampilData();
					});
				}
			});
		}

		function prosesUpdate() {
			$('#btnProses').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');
			$('#LOADX').show();

			return $.ajax({
				url: '{{ route('updatedtb_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					items: dataDTB
				}
			}).then(function(response) {
				$('#LOADX').hide();
				$('#btnProses').prop('disabled', false).html('<i class="fas fa-save"></i> PROSES');
				return response;
			}).catch(function(xhr) {
				$('#LOADX').hide();
				$('#btnProses').prop('disabled', false).html('<i class="fas fa-save"></i> PROSES');

				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: xhr.responseJSON?.error || 'Proses update gagal'
				});

				throw new Error(xhr.responseJSON?.error || 'Proses update gagal');
			});
		}

		function importExcel(file) {
			Swal.fire({
				title: 'Konfirmasi Import',
				html: 'Pastikan format kolom Excel sudah benar:<br><strong>SUBITEM | DTB</strong><br><small class="text-muted">(atau BARCODE | DTB)</small>',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#ffc107',
				cancelButtonColor: '#6c757d',
				confirmButtonText: '<i class="fas fa-check"></i> Ya, Import!',
				cancelButtonText: '<i class="fas fa-times"></i> Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					var formData = new FormData();
					formData.append('file', file);
					formData.append('_token', '{{ csrf_token() }}');

					$('#LOADX').show();

					$.ajax({
						url: '{{ url('/tupdatedtb/import') }}',
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#LOADX').hide();
							$('#fileImport').val('');

							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								html: response.message,
								showConfirmButton: true
							}).then(() => {
								tampilData();
							});
						},
						error: function(xhr) {
							$('#LOADX').hide();
							$('#fileImport').val('');

							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: xhr.responseJSON?.error || 'Import gagal'
							});
						}
					});
				} else {
					$('#fileImport').val('');
				}
			});
		}

		function exportExcel() {
			var filterDTB = $('#cbDTB').val();
			var filterSub = $('#txtSub').val();

			Swal.fire({
				title: 'Export ke Excel',
				text: 'Mengunduh file Excel...',
				icon: 'info',
				showConfirmButton: false,
				allowOutsideClick: false,
				willOpen: () => {
					Swal.showLoading();
				}
			});

			var params = new URLSearchParams({
				filter_dtb: filterDTB,
				filter_sub: filterSub
			});

			window.location.href = '{{ url('/tupdatedtb/export') }}?' + params.toString();

			setTimeout(() => {
				Swal.close();
			}, 2000);
		}

		function updateCheckStatus() {
			$('.checkbox-item:checked').each(function() {
				var index = $(this).data('index');
				if (dataDTB[index]) {
					dataDTB[index].cek = 1;
				}
			});

			$('.checkbox-item:not(:checked)').each(function() {
				var index = $(this).data('index');
				if (dataDTB[index]) {
					dataDTB[index].cek = 0;
				}
			});
		}

		function updateCheckAllStatus() {
			var totalCheckbox = $('.checkbox-item').length;
			var checkedCheckbox = $('.checkbox-item:checked').length;

			$('#checkAll').prop('checked', totalCheckbox > 0 && totalCheckbox === checkedCheckbox);
		}

		function formatNumber(num, decimals) {
			var n = parseFloat(num);
			if (isNaN(n)) return '0';

			return n.toLocaleString('id-ID', {
				minimumFractionDigits: decimals,
				maximumFractionDigits: decimals
			});
		}
	</script>
@endsection
