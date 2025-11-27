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
			padding: 12px 30px;
			font-size: 16px;
			margin-bottom: 15px;
		}

		.btn-proses:hover {
			background: #0056b3;
			color: #fff;
		}

		.btn-cetak {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 8px 20px;
			font-size: 14px;
		}

		.btn-cetak:hover {
			background: #218838;
			color: #fff;
		}

		.btn-refresh {
			background: #17a2b8;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 8px 20px;
			font-size: 14px;
		}

		.btn-refresh:hover {
			background: #138496;
			color: #fff;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 13px;
		}

		.table tbody td {
			font-size: 12px;
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
			margin-bottom: 10px;
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
								<!-- Tombol Proses -->
								<div class="row mb-3">
									<div class="col-md-12">
										<button type="button" id="btnProses" class="btn btn-proses">
											<i class="fas fa-cogs"></i> Proses LBKK/LBTAT Harian
										</button>
									</div>
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
								</ul>

								<!-- Tabs Content -->
								<div class="tab-content" id="myTabContent">
									<!-- Tab 1: LBKK -->
									<div class="tab-pane fade show active" id="tab1" role="tabpanel">
										<div class="tab-buttons mt-3">
											<button type="button" class="btn btn-refresh btn-refresh-tab1">
												<i class="fas fa-sync"></i> Refresh
											</button>
											<button type="button" class="btn btn-cetak btn-cetak-tab1">
												<i class="fas fa-print"></i> Cetak
											</button>
										</div>
										<div class="table-responsive">
											<table class="table-striped table-bordered table-hover table" id="tableBZ" style="width:100%">
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

									<!-- Tab 2: LBTAT -->
									<div class="tab-pane fade" id="tab2" role="tabpanel">
										<div class="tab-buttons mt-3">
											<button type="button" class="btn btn-refresh btn-refresh-tab2">
												<i class="fas fa-sync"></i> Refresh
											</button>
											<button type="button" class="btn btn-cetak btn-cetak-tab2">
												<i class="fas fa-print"></i> Cetak
											</button>
										</div>
										<div class="table-responsive">
											<table class="table-striped table-bordered table-hover table" id="tableBT" style="width:100%">
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
										<div class="tab-buttons mt-3">
											<button type="button" class="btn btn-refresh btn-refresh-tab3">
												<i class="fas fa-sync"></i> Refresh
											</button>
											<button type="button" class="btn btn-cetak btn-cetak-tab3">
												<i class="fas fa-print"></i> Cetak
											</button>
										</div>
										<div class="table-responsive">
											<table class="table-striped table-bordered table-hover table" id="tableHasil" style="width:100%">
												<thead>
													<tr>
														<th width="50px">No</th>
														<th width="120px">No Form</th>
														<th width="100px">Sub Item</th>
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
		var tableBZ, tableBT, tableHasil;

		$(document).ready(function() {
			// Initialize DataTables
			tableBZ = $('#tableBZ').KoolDataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: "{{ route('get-tcetaklbkklbtat-data') }}",
					data: function(d) {
						d.tab_type = 'bz';
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

			tableBT = $('#tableBT').KoolDataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: "{{ route('get-tcetaklbkklbtat-data') }}",
					data: function(d) {
						d.tab_type = 'bt';
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
					url: "{{ route('get-tcetaklbkklbtat-data') }}",
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

			// Tombol Proses
			$('#btnProses').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi',
					text: 'Proses LBKK/LBTAT Harian untuk hari ini?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya, Proses',
					cancelButtonText: 'Batal',
					confirmButtonColor: '#007bff',
					cancelButtonColor: '#6c757d'
				}).then((result) => {
					if (result.isConfirmed) {
						$('#LOADX').show();
						$('#btnProses').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

						$.ajax({
							url: "{{ route('tcetaklbkklbtat_proses') }}",
							type: 'POST',
							data: {
								_token: '{{ csrf_token() }}',
								periode: '{{ date('mY') }}'
							},
							success: function(response) {
								$('#LOADX').hide();
								$('#btnProses').prop('disabled', false).html(
									'<i class="fas fa-cogs"></i> Proses LBKK/LBTAT Harian');

								if (response.success) {
									Swal.fire({
										icon: 'success',
										title: 'Berhasil',
										text: response.message,
										timer: 2000,
										showConfirmButton: false
									});

									// Reload semua tabel
									tableBZ.ajax.reload();
									tableBT.ajax.reload();
									tableHasil.ajax.reload();
								} else {
									Swal.fire({
										icon: 'warning',
										title: 'Perhatian',
										text: response.message
									});
								}
							},
							error: function(xhr) {
								$('#LOADX').hide();
								$('#btnProses').prop('disabled', false).html(
									'<i class="fas fa-cogs"></i> Proses LBKK/LBTAT Harian');

								Swal.fire({
									icon: 'error',
									title: 'Error',
									text: xhr.responseJSON?.error || 'Terjadi kesalahan saat memproses data'
								});
							}
						});
					}
				});
			});

			// Tombol Refresh Tab 1 (BZ)
			$('.btn-refresh-tab1').on('click', function() {
				tableBZ.ajax.reload();
				Swal.fire({
					icon: 'info',
					title: 'Data Direfresh',
					timer: 1000,
					showConfirmButton: false
				});
			});

			// Tombol Cetak Tab 1 (BZ)
			$('.btn-cetak-tab1').on('click', function() {
				$('#LOADX').show();

				$.ajax({
					url: "{{ route('tcetaklbkklbtat_jasper') }}",
					type: 'GET',
					data: {
						tab_type: 'bz'
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
									text: 'Laporan LBKK berhasil digenerate',
									timer: 2000,
									showConfirmButton: false
								});
								// Di sini bisa ditambahkan logic untuk membuka PDF/Excel
								console.log('Data LBKK:', response.data);
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

			// Tombol Refresh Tab 2 (BT)
			$('.btn-refresh-tab2').on('click', function() {
				tableBT.ajax.reload();
				Swal.fire({
					icon: 'info',
					title: 'Data Direfresh',
					timer: 1000,
					showConfirmButton: false
				});
			});

			// Tombol Cetak Tab 2 (BT)
			$('.btn-cetak-tab2').on('click', function() {
				$('#LOADX').show();

				$.ajax({
					url: "{{ route('tcetaklbkklbtat_jasper') }}",
					type: 'GET',
					data: {
						tab_type: 'bt'
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
									text: 'Laporan LBTAT berhasil digenerate',
									timer: 2000,
									showConfirmButton: false
								});
								// Di sini bisa ditambahkan logic untuk membuka PDF/Excel
								console.log('Data LBTAT:', response.data);
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

			// Tombol Refresh Tab 3 (Hasil)
			$('.btn-refresh-tab3').on('click', function() {
				tableHasil.ajax.reload();
				Swal.fire({
					icon: 'info',
					title: 'Data Direfresh',
					timer: 1000,
					showConfirmButton: false
				});
			});

			// Tombol Cetak Tab 3 (Hasil)
			$('.btn-cetak-tab3').on('click', function() {
				$('#LOADX').show();

				$.ajax({
					url: "{{ route('tcetaklbkklbtat_jasper') }}",
					type: 'GET',
					data: {
						tab_type: 'hasil'
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
									text: 'Laporan Hasil Penanganan berhasil digenerate',
									timer: 2000,
									showConfirmButton: false
								});
								// Di sini bisa ditambahkan logic untuk membuka PDF/Excel
								console.log('Data Hasil:', response.data);
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

			// Event ketika tab berubah
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				var target = $(e.target).attr("href");

				if (target === '#tab1') {
					tableBZ.columns.adjust().draw();
				} else if (target === '#tab2') {
					tableBT.columns.adjust().draw();
				} else if (target === '#tab3') {
					tableHasil.columns.adjust().draw();
				}
			});
		});
	</script>
@endsection
