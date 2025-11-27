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

		.btn-update {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-update:hover {
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

		.nav-tabs .nav-link {
			font-weight: 600;
			color: #495057;
		}

		.nav-tabs .nav-link.active {
			background-color: #007bff;
			color: #fff;
			border-color: #007bff;
		}

		.progress {
			height: 25px;
			margin-top: 10px;
			display: none;
		}

		.progress-bar {
			font-size: 14px;
			line-height: 25px;
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
										<li>Pilih <strong>Outlet/Cabang</strong> yang akan diupdate</li>
										<li>Klik <strong>TAMPILKAN</strong> untuk menampilkan data barang dari synchron.brg</li>
										<li>Klik <strong>UPDATE</strong> untuk memproses update data ke database outlet yang dipilih</li>
										<li>Tab <strong>Kode 3</strong>: Update barang dengan kode awalan "3"</li>
										<li>Tab <strong>Kode 5</strong>: Update barang dengan kode awalan "5"</li>
									</ul>
								</div>

								<!-- Filter Section -->
								<div class="form-section">
									<div class="row">
										<div class="col-md-4">
											<label for="txtCbg"><strong>Filter Outlet/Cabang:</strong></label>
											<select class="form-control" id="txtCbg">
												<option value="">-- Pilih Cabang --</option>
												@if (isset($cabangList))
													@foreach ($cabangList as $cbgItem)
														<option value="{{ $cbgItem->cbg }}" {{ isset($cbg) && $cbg == $cbgItem->cbg ? 'selected' : '' }}>
															{{ $cbgItem->cbg }}
														</option>
													@endforeach
												@endif
											</select>
										</div>
									</div>
								</div>

								<hr>

								<!-- Tabs Navigation -->
								<ul class="nav nav-tabs" id="masterBarangTabs" role="tablist">
									<li class="nav-item">
										<a class="nav-link active" id="kode3-tab" data-toggle="tab" href="#kode3" role="tab">
											<i class="fas fa-box"></i> Kode 3
										</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" id="kode5-tab" data-toggle="tab" href="#kode5" role="tab">
											<i class="fas fa-boxes"></i> Kode 5
										</a>
									</li>
								</ul>

								<!-- Tab Content -->
								<div class="tab-content" id="masterBarangTabContent">
									<!-- Tab Kode 3 -->
									<div class="tab-pane fade show active" id="kode3" role="tabpanel">
										<div class="mb-3 mt-3">
											<button type="button" id="btnTampil3" class="btn btn-tampil">
												<i class="fas fa-eye"></i> TAMPILKAN
											</button>
											<button type="button" id="btnUpdate3" class="btn btn-update">
												<i class="fas fa-sync-alt"></i> UPDATE
											</button>
										</div>

										<div class="progress" id="progress3">
											<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
										</div>

										<div class="table-wrapper mt-3">
											<table class="table-striped table-bordered table-hover table" id="tableKode3" style="width:100%">
												<thead>
													<tr>
														<th width="40px">No</th>
														<th width="60px">Sub</th>
														<th width="80px">Item</th>
														<th width="200px">Nama Barang</th>
														<th width="80px">Ukuran</th>
														<th width="100px">Ket Kem</th>
														<th width="60px">KLK</th>
														<th width="80px">Sup</th>
														<th width="100px">Tgl Saran</th>
														<th width="90px">H Saran</th>
														<th width="80px">F Panen</th>
														<th width="80px">F Ada</th>
														<th width="60px">PPN</th>
														<th width="70px">DTB</th>
														<th width="70px">QB</th>
														<th width="70px">QJ</th>
														<th width="80px">Valid</th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td colspan="17" class="text-center">Klik tombol TAMPILKAN untuk menampilkan data</td>
													</tr>
												</tbody>
											</table>
										</div>
									</div>

									<!-- Tab Kode 5 -->
									<div class="tab-pane fade" id="kode5" role="tabpanel">
										<div class="mb-3 mt-3">
											<button type="button" id="btnTampil5" class="btn btn-tampil">
												<i class="fas fa-eye"></i> TAMPILKAN
											</button>
											<button type="button" id="btnUpdate5" class="btn btn-update">
												<i class="fas fa-sync-alt"></i> UPDATE
											</button>
										</div>

										<div class="progress" id="progress5">
											<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
										</div>

										<div class="table-wrapper mt-3">
											<table class="table-striped table-bordered table-hover table" id="tableKode5" style="width:100%">
												<thead>
													<tr>
														<th width="40px">No</th>
														<th width="60px">Sub</th>
														<th width="80px">Item</th>
														<th width="200px">Nama Barang</th>
														<th width="80px">Ukuran</th>
														<th width="100px">Ket Kem</th>
														<th width="60px">KLK</th>
														<th width="80px">Sup</th>
														<th width="100px">Tgl Saran</th>
														<th width="90px">H Saran</th>
														<th width="80px">F Panen</th>
														<th width="80px">F Ada</th>
														<th width="60px">PPN</th>
														<th width="70px">DTB</th>
														<th width="70px">QB</th>
														<th width="70px">QJ</th>
														<th width="80px">Valid</th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td colspan="17" class="text-center">Klik tombol TAMPILKAN untuk menampilkan data</td>
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
		</div>
	</div>

	<div class="loader" id="LOADX"></div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		var tableKode3, tableKode5;
		var dataKode3 = [];
		var dataKode5 = [];

		$(document).ready(function() {
			// Initialize DataTables
			initTables();

			// Button Tampil Kode 3
			$('#btnTampil3').on('click', function() {
				tampilData('3');
			});

			// Button Tampil Kode 5
			$('#btnTampil5').on('click', function() {
				tampilData('5');
			});

			// Button Update Kode 3
			$('#btnUpdate3').on('click', function() {
				updateData('3');
			});

			// Button Update Kode 5
			$('#btnUpdate5').on('click', function() {
				updateData('5');
			});
		});

		function initTables() {
			// Initialize Table Kode 3
			tableKode3 = $('#tableKode3').DataTable({
				data: [],
				columns: [{
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
						data: 'kdbar',
						className: 'text-center'
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
						data: 'klk',
						className: 'text-center'
					},
					{
						data: 'kodes',
						className: 'text-center'
					},
					{
						data: 'tgl_saran',
						className: 'text-center',
						render: function(data) {
							return data ? formatDate(data) : '-';
						}
					},
					{
						data: 'h_saran',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: 'f_panen',
						className: 'text-center'
					},
					{
						data: 'f_ada',
						className: 'text-center'
					},
					{
						data: 'ppn',
						className: 'text-center'
					},
					{
						data: 'dtb',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: 'qb',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: 'qj',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: 'valid',
						className: 'text-center'
					}
				],
				paging: true,
				pageLength: 50,
				searching: true,
				ordering: true,
				info: true,
				scrollX: true
			});

			// Initialize Table Kode 5
			tableKode5 = $('#tableKode5').DataTable({
				data: [],
				columns: [{
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
						data: 'kdbar',
						className: 'text-center'
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
						data: 'klk',
						className: 'text-center'
					},
					{
						data: 'kodes',
						className: 'text-center'
					},
					{
						data: 'tgl_saran',
						className: 'text-center',
						render: function(data) {
							return data ? formatDate(data) : '-';
						}
					},
					{
						data: 'h_saran',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: 'f_panen',
						className: 'text-center'
					},
					{
						data: 'f_ada',
						className: 'text-center'
					},
					{
						data: 'ppn',
						className: 'text-center'
					},
					{
						data: 'dtb',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: 'qb',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: 'qj',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 2);
						}
					},
					{
						data: 'valid',
						className: 'text-center'
					}
				],
				paging: true,
				pageLength: 50,
				searching: true,
				ordering: true,
				info: true,
				scrollX: true
			});
		}

		function tampilData(kodeType) {
			var cbg = $('#txtCbg').val();

			if (!cbg) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Pilih outlet/cabang terlebih dahulu!'
				});
				$('#txtCbg').focus();
				return;
			}

			$('#LOADX').show();
			var table = kodeType == '3' ? tableKode3 : tableKode5;

			$.ajax({
				url: '{{ route('updatemasterbarang_cari') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					kode_type: kodeType,
					cbg: cbg
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data.length > 0) {
						if (kodeType == '3') {
							dataKode3 = response.data;
						} else {
							dataKode5 = response.data;
						}

						table.clear();
						table.rows.add(response.data);
						table.draw();

						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Proses Selesai! ' + response.count + ' item dimuat.',
							timer: 2000,
							showConfirmButton: false
						});
					} else {
						table.clear().draw();
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

		function updateData(kodeType) {
			var cbg = $('#txtCbg').val();
			var table = kodeType == '3' ? tableKode3 : tableKode5;
			var data = kodeType == '3' ? dataKode3 : dataKode5;

			if (!cbg) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Pilih outlet/cabang terlebih dahulu!'
				});
				$('#txtCbg').focus();
				return;
			}

			if (data.length === 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Tidak ada data untuk diupdate! Klik TAMPILKAN terlebih dahulu.'
				});
				return;
			}

			Swal.fire({
				title: 'Konfirmasi Update',
				html: 'Yakin Update Barang Kode ' + kodeType + ' untuk outlet <strong>' + cbg +
					'</strong>?<br><small class="text-warning">Data akan diupdate ke database outlet yang dipilih.</small>',
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#28a745',
				cancelButtonColor: '#6c757d',
				confirmButtonText: '<i class="fas fa-check"></i> Ya, Update!',
				cancelButtonText: '<i class="fas fa-times"></i> Batal',
				showLoaderOnConfirm: true,
				preConfirm: () => {
					return prosesUpdate(kodeType, cbg, data);
				},
				allowOutsideClick: () => !Swal.isLoading()
			}).then((result) => {
				if (result.isConfirmed && result.value.success) {
					Swal.fire({
						icon: 'success',
						title: 'Berhasil',
						html: result.value.message,
						showConfirmButton: true
					});
				}
			});
		}

		function prosesUpdate(kodeType, cbg, items) {
			var btnUpdate = kodeType == '3' ? '#btnUpdate3' : '#btnUpdate5';
			var progress = kodeType == '3' ? '#progress3' : '#progress5';

			$(btnUpdate).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');
			$(progress).show();
			$('#LOADX').show();

			return $.ajax({
				url: '{{ route('updatemasterbarang_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					kode_type: kodeType,
					cbg: cbg,
					items: items
				},
				xhr: function() {
					var xhr = new window.XMLHttpRequest();
					xhr.upload.addEventListener("progress", function(evt) {
						if (evt.lengthComputable) {
							var percentComplete = evt.loaded / evt.total * 100;
							$(progress + ' .progress-bar').css('width', percentComplete + '%').text(Math.round(percentComplete) + '%');
						}
					}, false);
					return xhr;
				}
			}).then(function(response) {
				$('#LOADX').hide();
				$(btnUpdate).prop('disabled', false).html('<i class="fas fa-sync-alt"></i> UPDATE');
				$(progress).hide();
				$(progress + ' .progress-bar').css('width', '0%').text('0%');
				return response;
			}).catch(function(xhr) {
				$('#LOADX').hide();
				$(btnUpdate).prop('disabled', false).html('<i class="fas fa-sync-alt"></i> UPDATE');
				$(progress).hide();
				$(progress + ' .progress-bar').css('width', '0%').text('0%');

				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: xhr.responseJSON?.error || 'Proses update gagal'
				});

				throw new Error(xhr.responseJSON?.error || 'Proses update gagal');
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

		function formatDate(dateStr) {
			if (!dateStr) return '-';

			try {
				var date = new Date(dateStr);
				var day = ('0' + date.getDate()).slice(-2);
				var month = ('0' + (date.getMonth() + 1)).slice(-2);
				var year = date.getFullYear();
				return day + '/' + month + '/' + year;
			} catch (e) {
				return dateStr;
			}
		}
	</script>
@endsection
