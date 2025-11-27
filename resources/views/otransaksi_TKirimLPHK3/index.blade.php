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

		.btn-cetak {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-cetak:hover {
			background: #218838;
			color: #fff;
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

		.status-badge {
			padding: 5px 10px;
			border-radius: 4px;
			font-size: 11px;
			font-weight: 600;
		}

		.status-success {
			background: #d4edda;
			color: #155724;
		}

		.status-info {
			background: #d1ecf1;
			color: #0c5460;
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
							<span class="badge badge-cbg badge-primary">CBG: {{ is_array($cbg) ? implode(', ', $cbg) : $cbg ?? '-' }}</span>
							<span class="badge badge-cbg badge-info">Periode: {{ is_array($periode) ? implode(', ', $periode) : $periode ?? '-' }}</span>
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
										<li>Klik <strong>PROSES</strong> untuk generate file DBF LPH K3 dari data <code>lphkode3_ff</code></li>
										<li>Data akan diekspor ke format DBF untuk integrasi dengan sistem legacy</li>
										<li>File yang digenerate akan disimpan di folder <code>D:\dbf\kode 3 ts\</code></li>
										<li>Gunakan <strong>CETAK</strong> untuk melihat preview data sebelum diproses</li>
									</ul>
								</div>

								<!-- Button Actions -->
								<div class="form-section">
									<div class="row">
										<div class="col-md-12 text-right">
											<button type="button" id="btnCetak" class="btn btn-cetak">
												<i class="fas fa-print"></i> CETAK PREVIEW
											</button>
											<button type="button" id="btnProses" class="btn btn-proses">
												<i class="fas fa-cogs"></i> PROSES
											</button>
										</div>
									</div>
								</div>

								<hr>

								<!-- Data Table -->
								<div class="table-wrapper">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="40px">No</th>
												<th width="40px">UR</th>
												<th width="80px">Tanggal</th>
												<th width="60px">Sub</th>
												<th width="80px">KD Bar</th>
												<th width="150px">Kode Barang</th>
												<th width="80px">LPH TMM</th>
												<th width="80px">LPH GZ</th>
												<th width="80px">LPH KG</th>
												<th width="60px">M_TD</th>
												<th width="60px">M_TG</th>
												<th width="60px">M_TM</th>
												<th width="60px">M_MZ</th>
												<th width="60px">M_KG</th>
												<th width="150px">Keterangan</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td colspan="15" class="text-center">Klik tombol CETAK PREVIEW untuk menampilkan data</td>
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
		var CBG = {!! json_encode($cbg ?? '') !!};

		$(document).ready(function() {
			// Initialize empty table
			initTable();

			// Button Proses
			$('#btnProses').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi Proses',
					html: 'Proses akan generate file DBF LPH K3.<br><small class="text-warning">File akan disimpan di D:\\dbf\\kode 3 ts\\</small>',
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
							html: '<strong>' + result.value.message + '</strong><br><br>' +
								'<small>Filename: <code>' + result.value.filename + '</code></small><br>' +
								'<small>Total Records: <strong>' + result.value.total_records + '</strong></small>',
							confirmButtonText: 'OK'
						});
					}
				});
			});

			// Button Cetak Preview
			$('#btnCetak').on('click', function() {
				loadData();
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
						data: 'UR',
						className: 'text-center'
					},
					{
						data: 'TGL',
						render: function(data) {
							return data ? formatDate(data) : '-';
						},
						className: 'text-center'
					},
					{
						data: 'SUB',
						className: 'text-center'
					},
					{
						data: 'KDBAR',
						className: 'text-center'
					},
					{
						data: 'KD_BRG',
						className: 'text-center'
					},
					{
						data: 'LPH_TMM',
						render: function(data) {
							return formatNumber(data, 2);
						},
						className: 'text-right'
					},
					{
						data: 'LPH_GZ',
						render: function(data) {
							return formatNumber(data, 2);
						},
						className: 'text-right'
					},
					{
						data: 'LPH_KG',
						render: function(data) {
							return formatNumber(data, 2);
						},
						className: 'text-right'
					},
					{
						data: 'M_TD',
						className: 'text-center'
					},
					{
						data: 'M_TG',
						className: 'text-center'
					},
					{
						data: 'M_TM',
						className: 'text-center'
					},
					{
						data: 'M_MZ',
						className: 'text-center'
					},
					{
						data: 'M_KG',
						className: 'text-center'
					},
					{
						data: 'KETERANGAN'
					}
				],
				paging: true,
				pageLength: 25,
				searching: true,
				ordering: true,
				info: true,
				scrollX: true,
				fixedColumns: {
					left: 3
				}
			});
		}

		function loadData() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('kirimlphk3_cari') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data && response.data.length > 0) {
						tableData = response.data;

						table.clear();
						table.rows.add(response.data);
						table.draw();

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
							text: 'Tidak ada data ditemukan'
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

			return $.ajax({
				url: '{{ route('kirimlphk3_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}'
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

		function formatNumber(num, decimals) {
			var n = parseFloat(num);
			if (isNaN(n)) return '0.00';

			return n.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		}

		function formatDate(dateStr) {
			if (!dateStr) return '-';

			var date = new Date(dateStr);
			var day = String(date.getDate()).padStart(2, '0');
			var month = String(date.getMonth() + 1).padStart(2, '0');
			var year = date.getFullYear();

			return day + '-' + month + '-' + year;
		}

		// Auto load data on page load
		$(document).ready(function() {
			setTimeout(function() {
				loadData();
			}, 500);
		});
	</script>
@endsection
