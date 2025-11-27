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
			padding: 10px 20px;
			font-size: 14px;
			margin: 5px;
		}

		.btn-proses:hover {
			background: #0056b3;
			color: #fff;
		}

		.btn-tampil {
			background: #17a2b8;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 8px 20px;
			font-size: 14px;
			margin: 5px;
		}

		.btn-tampil:hover {
			background: #138496;
			color: #fff;
		}

		.btn-cetak {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 8px 20px;
			font-size: 14px;
			margin: 5px;
		}

		.btn-cetak:hover {
			background: #218838;
			color: #fff;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 13px;
			padding: 10px;
		}

		.table tbody td {
			font-size: 12px;
			padding: 8px;
		}

		.nav-tabs .nav-link {
			font-weight: 600;
		}

		.nav-tabs .nav-link.active {
			background-color: #007bff;
			color: white;
		}

		.loader {
			position: fixed;
			top: 50%;
			left: 50%;
			width: 100px;
			aspect-ratio: 1;
			background: radial-gradient(farthest-side, #ffa516 90%, #0000) center/16px 16px,
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

		.tab-buttons {
			margin: 15px 0;
			padding: 10px;
			background: #f8f9fa;
			border-radius: 5px;
		}

		.process-buttons {
			margin-bottom: 20px;
			padding: 15px;
			background: #e9ecef;
			border-radius: 5px;
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

			<div class="content">
				<div class="container-fluid">
					@if (isset($error))
						<div class="alert alert-danger alert-dismissible fade show" role="alert">
							<strong>Error!</strong> {{ $error }}
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden-true">&times;</span>
							</button>
						</div>
					@endif

					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<!-- Toolbar Export -->
									<div class="mb-3">
										<button type="button" class="btn btn-success btn-sm" id="btnExportExcel">
											<i class="fas fa-file-excel"></i> Export Excel
										</button>
										<button type="button" class="btn btn-info btn-sm" id="btnExportWord">
											<i class="fas fa-file-word"></i> Export Word
										</button>
									</div>

									<!-- Tombol Proses -->
									<div class="process-buttons">
										<h5 class="mb-3">Proses Laporan:</h5>
										<button type="button" class="btn btn-proses" data-flag="BZ">
											<i class="fas fa-cogs"></i> Proses LBKK Harian
										</button>
										<button type="button" class="btn btn-proses" data-flag="3Z">
											<i class="fas fa-cogs"></i> Proses LBK Kode 3
										</button>
										<button type="button" class="btn btn-proses" data-flag="BT" data-jns="2">
											<i class="fas fa-cogs"></i> Proses LBTT Harian
										</button>
										<button type="button" class="btn btn-proses" data-flag="BT" data-jns="3">
											<i class="fas fa-cogs"></i> Proses LBTT > 2 Hari
										</button>
										<button type="button" class="btn btn-proses" data-flag="3T">
											<i class="fas fa-cogs"></i> Proses LBTT Kode 3
										</button>
									</div>

									<!-- Tabs Navigation -->
									<ul class="nav nav-tabs" id="myTab" role="tablist">
										<li class="nav-item">
											<a class="nav-link active" id="tab1-tab" data-toggle="tab" href="#tab1" role="tab">
												Laporan Barang Kosong Komputer
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" id="tab2-tab" data-toggle="tab" href="#tab2" role="tab">
												Laporan Barang Tidak Ada Transaksi
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" id="tab3-tab" data-toggle="tab" href="#tab3" role="tab">
												Hasil Penanganan LBTAT
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" id="tab4-tab" data-toggle="tab" href="#tab4" role="tab">
												Laporan Barang Kosong (Scan)
											</a>
										</li>
									</ul>

									<!-- Tabs Content -->
									<div class="tab-content" id="myTabContent">
										<!-- Tab 1: LBKK -->
										<div class="tab-pane fade show active" id="tab1" role="tabpanel">
											<div class="tab-buttons">
												<button type="button" class="btn btn-tampil" data-type="bz">
													<i class="fas fa-eye"></i> Tampilan
												</button>
												<button type="button" class="btn btn-tampil" data-type="3z">
													<i class="fas fa-eye"></i> Tampilkan Kode 3
												</button>
												<button type="button" class="btn btn-cetak" data-tab="tab1">
													<i class="fas fa-print"></i> Cetak
												</button>
											</div>
											<div class="table-responsive">
												<table class="table-striped table-bordered table-hover table" id="tableLBKK" style="width:100%">
													<thead>
														<tr>
															<th width="50px">No</th>
															<th width="120px">No Form</th>
															<th width="100px">Item Sub</th>
															<th>Nama Barang</th>
															<th width="100px">Ukuran</th>
															<th width="100px">Kemasan</th>
															<th width="80px">KD</th>
															<th width="100px" class="text-right">Harga Jual</th>
															<th width="80px" class="text-right">Saldo</th>
															<th width="100px">Tgl BL Akhir</th>
															<th width="80px" class="text-right">Qty BL Akhir</th>
															<th width="100px">Tgl LBK</th>
															<th width="80px">DTR</th>
														</tr>
													</thead>
													<tbody></tbody>
												</table>
											</div>
										</div>

										<!-- Tab 2: LBTAT -->
										<div class="tab-pane fade" id="tab2" role="tabpanel">
											<div class="tab-buttons">
												<button type="button" class="btn btn-tampil" data-type="bt" data-jns="2">
													<i class="fas fa-eye"></i> Tampilkan Harian
												</button>
												<button type="button" class="btn btn-tampil" data-type="bt" data-jns="3">
													<i class="fas fa-eye"></i> Tampilkan > 2 Hari
												</button>
												<button type="button" class="btn btn-tampil" data-type="3t">
													<i class="fas fa-eye"></i> Tampilkan Kode 3
												</button>
												<button type="button" class="btn btn-cetak" data-tab="tab2">
													<i class="fas fa-print"></i> Cetak
												</button>
											</div>
											<div class="table-responsive">
												<table class="table-striped table-bordered table-hover table" id="tableLBTAT" style="width:100%">
													<thead>
														<tr>
															<th width="50px">No</th>
															<th width="120px">No Form</th>
															<th width="100px">Item Sub</th>
															<th>Nama Barang</th>
															<th width="100px">Ukuran</th>
															<th width="100px">Kemasan</th>
															<th width="80px">KD</th>
															<th width="100px" class="text-right">Harga Jual</th>
															<th width="80px" class="text-right">LPH</th>
															<th width="80px" class="text-right">Saldo</th>
															<th width="100px">Tgl BL Akhir</th>
															<th width="80px" class="text-right">Qty BL Akhir</th>
															<th width="100px">Tgl LBK</th>
															<th width="100px">Tgl Kasir</th>
														</tr>
													</thead>
													<tbody></tbody>
												</table>
											</div>
										</div>

										<!-- Tab 3: Hasil -->
										<div class="tab-pane fade" id="tab3" role="tabpanel">
											<div class="tab-buttons">
												<button type="button" class="btn btn-tampil" data-type="hasil">
													<i class="fas fa-eye"></i> Tampilkan
												</button>
												<button type="button" class="btn btn-cetak" data-tab="tab3" data-report="report">
													<i class="fas fa-print"></i> Print Report
												</button>
												<button type="button" class="btn btn-cetak" data-tab="tab3" data-report="label">
													<i class="fas fa-tags"></i> Print Label
												</button>
												<button type="button" class="btn btn-tampil" id="btnOrderSela" style="background: #6c757d;">
													<i class="fas fa-box"></i> Order Sela Cabang
												</button>
											</div>
											<div class="table-responsive">
												<table class="table-striped table-bordered table-hover table" id="tableHasil" style="width:100%">
													<thead>
														<tr>
															<th width="50px">No</th>
															<th width="120px">No Form</th>
															<th width="100px">Item Sub</th>
															<th>Nama Barang</th>
															<th width="100px">Ukuran</th>
															<th width="100px">Kemasan</th>
															<th width="80px">KD</th>
															<th width="100px" class="text-right">Harga Jual</th>
															<th width="80px" class="text-right">LPH</th>
															<th width="80px" class="text-right">Stok TK</th>
															<th width="100px">Tgl BL Akhir</th>
															<th width="80px" class="text-right">Qty BL Akhir</th>
															<th width="100px">Tgl LBK</th>
															<th width="100px">Tgl Kasir</th>
															<th width="150px">Kesimpulan</th>
															<th width="80px">SPL</th>
														</tr>
													</thead>
													<tbody></tbody>
												</table>
											</div>
										</div>

										<!-- Tab 4: Scan -->
										<div class="tab-pane fade" id="tab4" role="tabpanel">
											<div class="tab-buttons">
												<button type="button" class="btn btn-tampil" data-type="scan">
													<i class="fas fa-eye"></i> Tampilkan
												</button>
												<button type="button" class="btn btn-cetak" data-tab="tab4">
													<i class="fas fa-print"></i> Cetak
												</button>
											</div>
											<div class="table-responsive">
												<table class="table-striped table-bordered table-hover table" id="tableScan" style="width:100%">
													<thead>
														<tr>
															<th width="50px">No</th>
															<th width="120px">No Form</th>
															<th width="100px">Item Sub</th>
															<th>Nama Barang</th>
															<th width="100px">Ukuran</th>
															<th width="100px">Kemasan</th>
															<th width="80px">KD</th>
															<th width="100px" class="text-right">Harga Jual</th>
															<th width="80px" class="text-right">Saldo</th>
															<th width="100px">Tgl BL Akhir</th>
															<th width="80px" class="text-right">Qty BL Akhir</th>
															<th width="100px">Tgl LBK</th>
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
			</div>
		</div>

		<div class="loader" id="LOADX"></div>
	@endsection

	@section('javascripts')
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		<script>
			var tableLBKK, tableLBTAT, tableHasil, tableScan;
			var currentTabType = 'bz';
			var currentJns = '2';

			$(document).ready(function() {
				// Initialize DataTables
				tableLBKK = $('#tableLBKK').KoolDataTable({
					processing: true,
					serverSide: true,
					ajax: {
						url: "{{ route('get-tcetaklbkklbtatbaru-data') }}",
						data: function(d) {
							d.tab_type = currentTabType;
							d.jns = currentJns;
						}
					},
					columns: [{
							data: 'DT_RowIndex',
							name: 'DT_RowIndex',
							orderable: false,
							searchable: false,
							className: 'text-center'
						},
						{
							data: 'no_bukti',
							name: 'no_bukti'
						},
						{
							data: 'itemsub',
							name: 'itemsub'
						},
						{
							data: 'na_brg',
							name: 'na_brg'
						},
						{
							data: 'ket_uk',
							name: 'ket_uk'
						},
						{
							data: 'ket_kem',
							name: 'ket_kem'
						},
						{
							data: 'kd',
							name: 'kd'
						},
						{
							data: 'hj',
							name: 'hj',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'saldo',
							name: 'saldo',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'tgl_trm',
							name: 'tgl_trm'
						},
						{
							data: 'qty_trm',
							name: 'qty_trm',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'tgl_lbk',
							name: 'tgl_lbk'
						},
						{
							data: 'dtr',
							name: 'dtr'
						}
					],
					order: [
						[1, 'asc']
					]
				});

				tableLBTAT = $('#tableLBTAT').KoolDataTable({
					processing: true,
					serverSide: true,
					ajax: {
						url: "{{ route('get-tcetaklbkklbtatbaru-data') }}",
						data: function(d) {
							d.tab_type = 'bt';
							d.jns = currentJns;
						}
					},
					columns: [{
							data: 'DT_RowIndex',
							name: 'DT_RowIndex',
							orderable: false,
							searchable: false,
							className: 'text-center'
						},
						{
							data: 'no_bukti',
							name: 'no_bukti'
						},
						{
							data: 'itemsub',
							name: 'itemsub'
						},
						{
							data: 'na_brg',
							name: 'na_brg'
						},
						{
							data: 'ket_uk',
							name: 'ket_uk'
						},
						{
							data: 'ket_kem',
							name: 'ket_kem'
						},
						{
							data: 'kd',
							name: 'kd'
						},
						{
							data: 'hj',
							name: 'hj',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'lph',
							name: 'lph',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'saldo',
							name: 'saldo',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'tgl_trm',
							name: 'tgl_trm'
						},
						{
							data: 'qty_trm',
							name: 'qty_trm',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'tgl_lbk',
							name: 'tgl_lbk'
						},
						{
							data: 'tgl_at',
							name: 'tgl_at'
						}
					],
					order: [
						[1, 'asc']
					]
				});

				tableHasil = $('#tableHasil').KoolDataTable({
					processing: true,
					serverSide: true,
					ajax: {
						url: "{{ route('get-tcetaklbkklbtatbaru-data') }}",
						data: function(d) {
							d.tab_type = 'hasil';
						}
					},
					columns: [{
							data: 'DT_RowIndex',
							name: 'DT_RowIndex',
							orderable: false,
							searchable: false,
							className: 'text-center'
						},
						{
							data: 'no_bukti',
							name: 'no_bukti'
						},
						{
							data: 'KD_BRG',
							name: 'KD_BRG'
						},
						{
							data: 'NA_BRG',
							name: 'NA_BRG'
						},
						{
							data: 'KET_UK',
							name: 'KET_UK'
						},
						{
							data: 'KET_KEM',
							name: 'KET_KEM'
						},
						{
							data: 'KD',
							name: 'KD'
						},
						{
							data: 'HJ',
							name: 'HJ',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'LPH',
							name: 'LPH',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'STOK',
							name: 'STOK',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'TGL_TRM',
							name: 'TGL_TRM'
						},
						{
							data: 'QTY_TRM',
							name: 'QTY_TRM',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'TGL_LBK',
							name: 'TGL_LBK'
						},
						{
							data: 'TGL_AT',
							name: 'TGL_AT'
						},
						{
							data: 'SIMPUL',
							name: 'SIMPUL'
						},
						{
							data: 'LABEL',
							name: 'LABEL',
							className: 'text-center'
						}
					],
					order: [
						[2, 'asc']
					]
				});

				tableScan = $('#tableScan').KoolDataTable({
					processing: true,
					serverSide: true,
					ajax: {
						url: "{{ route('get-tcetaklbkklbtatbaru-data') }}",
						data: function(d) {
							d.tab_type = 'scan';
						}
					},
					columns: [{
							data: 'DT_RowIndex',
							name: 'DT_RowIndex',
							orderable: false,
							searchable: false,
							className: 'text-center'
						},
						{
							data: 'no_bukti',
							name: 'no_bukti'
						},
						{
							data: 'itemsub',
							name: 'itemsub'
						},
						{
							data: 'na_brg',
							name: 'na_brg'
						},
						{
							data: 'ket_uk',
							name: 'ket_uk'
						},
						{
							data: 'ket_kem',
							name: 'ket_kem'
						},
						{
							data: 'kd',
							name: 'kd'
						},
						{
							data: 'hj',
							name: 'hj',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'saldo',
							name: 'saldo',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'tgl_trm',
							name: 'tgl_trm'
						},
						{
							data: 'qty_trm',
							name: 'qty_trm',
							className: 'text-right',
							render: $.fn.dataTable.render.number(',', '.', 0)
						},
						{
							data: 'tgl_lbk',
							name: 'tgl_lbk'
						}
					],
					order: [
						[1, 'asc']
					]
				});

				// Tombol Proses
				$('.btn-proses').on('click', function() {
					var flag = $(this).data('flag');
					var jns = $(this).data('jns') || '2';
					var btnText = $(this).html();
					var btnElement = $(this);

					var flagName = '';
					if (flag == 'BZ') flagName = 'LBKK Harian';
					else if (flag == '3Z') flagName = 'LBK Kode 3';
					else if (flag == 'BT' && jns == '2') flagName = 'LBTT Harian';
					else if (flag == 'BT' && jns == '3') flagName = 'LBTT > 2 Hari';
					else if (flag == '3T') flagName = 'LBTT Kode 3';

					Swal.fire({
						title: 'Konfirmasi Proses',
						html: '<p>Anda akan memproses laporan:</p>' +
							'<p><strong>' + flagName + '</strong></p>' +
							'<p>untuk hari ini. Lanjutkan?</p>',
						icon: 'question',
						showCancelButton: true,
						confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses',
						cancelButtonText: '<i class="fas fa-times"></i> Batal',
						confirmButtonColor: '#007bff',
						cancelButtonColor: '#6c757d',
						reverseButtons: true,
						showLoaderOnConfirm: true,
						allowOutsideClick: () => !Swal.isLoading()
					}).then((result) => {
						if (result.isConfirmed) {
							console.log('Memulai proses:', {
								flag: flag,
								jns: jns,
								flagName: flagName
							});

							// Show loading
							Swal.fire({
								title: 'Memproses...',
								html: '<p>Sedang memproses ' + flagName + '</p>' +
									'<p class="text-muted"><small>Mohon tunggu, jangan tutup halaman ini...</small></p>',
								icon: 'info',
								allowOutsideClick: false,
								allowEscapeKey: false,
								allowEnterKey: false,
								showConfirmButton: false,
								didOpen: () => {
									Swal.showLoading();
								}
							});

							$('#LOADX').show();
							btnElement.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

							$.ajax({
								url: "{{ route('tcetaklbkklbtatbaru_proses') }}",
								type: 'POST',
								data: {
									_token: '{{ csrf_token() }}',
									flag_type: flag,
									jns: jns
								},
								success: function(response) {
									console.log('Response sukses:', response);
									$('#LOADX').hide();
									btnElement.prop('disabled', false).html(btnText);

									if (response.success) {
										Swal.fire({
											icon: 'success',
											title: 'Proses Berhasil!',
											html: '<p><strong>' + flagName +
												'</strong> telah berhasil diproses</p>' +
												'<p class="text-muted"><small>' + response.message + '</small></p>',
											timer: 3000,
											timerProgressBar: true,
											showConfirmButton: true,
											confirmButtonText: 'OK'
										});

										// Reload table sesuai flag
										if (flag == 'BZ' || flag == '3Z') {
											currentTabType = flag.toLowerCase();
											tableLBKK.ajax.reload(null, false);
										} else if (flag == 'BT') {
											currentJns = jns;
											tableLBTAT.ajax.reload(null, false);
										} else if (flag == '3T') {
											tableLBTAT.ajax.reload(null, false);
										}
									} else {
										Swal.fire({
											icon: 'warning',
											title: 'Perhatian',
											html: '<p>' + response.message + '</p>',
											confirmButtonText: 'OK'
										});
									}
								},
								error: function(xhr) {
									console.error('Error response:', xhr);
									$('#LOADX').hide();
									btnElement.prop('disabled', false).html(btnText);

									var errorMsg = 'Terjadi kesalahan saat memproses data';
									if (xhr.responseJSON) {
										if (xhr.responseJSON.error) {
											errorMsg = xhr.responseJSON.error;
										} else if (xhr.responseJSON.message) {
											errorMsg = xhr.responseJSON.message;
										}
									}

									Swal.fire({
										icon: 'error',
										title: 'Proses Gagal',
										html: '<p><strong>Error:</strong></p><p>' + errorMsg + '</p>' +
											'<hr><p class="text-left"><small><strong>Troubleshooting:</strong><br>' +
											'1. Check browser console (F12) untuk detail<br>' +
											'2. Check Laravel log file<br>' +
											'3. Pastikan database connection aktif<br>' +
											'4. Pastikan stored procedure tersedia</small></p>',
										confirmButtonText: 'OK',
										footer: '<small>Status: ' + xhr.status + ' - ' + xhr.statusText + '</small>'
									});
								}
							});
						}
					});
				}); // Tombol Tampil
				$('.btn-tampil').on('click', function() {
					var type = $(this).data('type');
					var jns = $(this).data('jns') || '2';

					if (type == 'bz' || type == '3z') {
						currentTabType = type;
						tableLBKK.ajax.reload();
						Swal.fire({
							icon: 'info',
							title: 'Data Direfresh',
							timer: 1000,
							showConfirmButton: false
						});
					} else if (type == 'bt' || type == '3t') {
						currentJns = jns;
						tableLBTAT.ajax.url("{{ route('get-tcetaklbkklbtatbaru-data') }}?tab_type=" + type + "&jns=" + jns).load();
						Swal.fire({
							icon: 'info',
							title: 'Data Direfresh',
							timer: 1000,
							showConfirmButton: false
						});
					} else if (type == 'hasil') {
						tableHasil.ajax.reload();
						Swal.fire({
							icon: 'info',
							title: 'Data Direfresh',
							timer: 1000,
							showConfirmButton: false
						});
					} else if (type == 'scan') {
						tableScan.ajax.reload();
						Swal.fire({
							icon: 'info',
							title: 'Data Direfresh',
							timer: 1000,
							showConfirmButton: false
						});
					}
				});

				// Tombol Cetak
				$('.btn-cetak').on('click', function() {
					var tab = $(this).data('tab');
					var reportType = $(this).data('report') || 'report'; // report or label
					var tabType = '';
					var jns = currentJns;

					if (tab == 'tab1') {
						tabType = currentTabType;
					} else if (tab == 'tab2') {
						tabType = currentTabType == '3t' ? '3t' : 'bt';
					} else if (tab == 'tab3') {
						tabType = 'hasil';
					} else if (tab == 'tab4') {
						tabType = 'scan';
					}

					$('#LOADX').show();

					$.ajax({
						url: "{{ route('tcetaklbkklbtatbaru_jasper') }}",
						type: 'GET',
						data: {
							tab_type: tabType,
							jns: jns,
							report_type: reportType
						},
						success: function(response) {
							$('#LOADX').hide();

							if (response.success) {
								if (response.data.length === 0) {
									Swal.fire({
										icon: 'warning',
										title: 'Perhatian',
										text: 'Tidak ada data untuk dicetak'
									});
								} else {
									Swal.fire({
										icon: 'success',
										title: 'Berhasil',
										text: reportType == 'label' ? 'Label berhasil digenerate' :
											'Laporan berhasil digenerate',
										timer: 2000,
										showConfirmButton: false
									});
									console.log('Data Laporan:', response.data);
								}
							}
						},
						error: function(xhr) {
							$('#LOADX').hide();
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: xhr.responseJSON?.error || 'Gagal generate laporan'
							});
						}
					});
				});

				// Export Excel
				$('#btnExportExcel').on('click', function() {
					var activeTab = $('.nav-link.active').attr('id');
					var table = null;

					if (activeTab == 'tab1-tab') {
						table = tableLBKK;
					} else if (activeTab == 'tab2-tab') {
						table = tableLBTAT;
					} else if (activeTab == 'tab3-tab') {
						table = tableHasil;
					} else if (activeTab == 'tab4-tab') {
						table = tableScan;
					}

					if (table) {
						var filename = 'Laporan_LBKK_LBTAT_' + new Date().getTime() + '.xlsx';
						table.button('.buttons-excel').trigger();
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Perhatian',
							text: 'Pilih tab terlebih dahulu'
						});
					}
				});

				// Export Word
				$('#btnExportWord').on('click', function() {
					var activeTab = $('.nav-link.active').attr('id');
					var table = null;

					if (activeTab == 'tab1-tab') {
						table = tableLBKK;
					} else if (activeTab == 'tab2-tab') {
						table = tableLBTAT;
					} else if (activeTab == 'tab3-tab') {
						table = tableHasil;
					} else if (activeTab == 'tab4-tab') {
						table = tableScan;
					}

					if (table) {
						Swal.fire({
							icon: 'info',
							title: 'Export Word',
							text: 'Fitur export Word akan segera tersedia. Silakan gunakan Excel untuk saat ini.'
						});
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Perhatian',
							text: 'Pilih tab terlebih dahulu'
						});
					}
				});

				// Order Sela Cabang
				$('#btnOrderSela').on('click', function() {
					Swal.fire({
						icon: 'info',
						title: 'Order Sela Cabang',
						text: 'Fitur ini akan segera tersedia'
					});
				});

				// Event ketika tab berubah
				$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
					var target = $(e.target).attr("href");

					if (target === '#tab1') {
						tableLBKK.columns.adjust().draw();
					} else if (target === '#tab2') {
						tableLBTAT.columns.adjust().draw();
					} else if (target === '#tab3') {
						tableHasil.columns.adjust().draw();
					} else if (target === '#tab4') {
						tableScan.columns.adjust().draw();
					}
				});
			});
		</script>
	@endsection
