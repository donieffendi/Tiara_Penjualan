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

		.btn-ambil {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-ambil:hover {
			background: #218838;
			color: #fff;
		}

		.btn-ambil:disabled {
			background: #6c757d;
			cursor: not-allowed;
		}

		.btn-ambil-semua {
			background: #17a2b8;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-ambil-semua:hover {
			background: #138496;
			color: #fff;
		}

		.btn-ambil-semua:disabled {
			background: #6c757d;
			cursor: not-allowed;
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
										<li>Pilih <strong>Cabang</strong> yang akan dikirim data timbangannya</li>
										<li>Masukkan <strong>No Usulan</strong> untuk data spesifik, atau kosongkan untuk ambil semua data</li>
										<li>Klik <strong>AMBIL</strong> untuk menampilkan data berdasarkan No Usulan</li>
										<li>Klik <strong>AMBIL SEMUA</strong> untuk menampilkan semua data timbangan (KG)</li>
									</ul>
								</div>

								<!-- Filter Section -->
								<div class="form-section">
									<div class="row">
										<div class="col-md-3">
											<label for="txtCbg"><strong>Cabang:</strong></label>
											<select class="form-control" id="txtCbg">
												<option value="">-- Pilih Cabang --</option>
												@if (isset($cabangList))
													@foreach ($cabangList as $cbgItem)
														<option value="{{ $cbgItem->cbg }}" {{ isset($cbg) && $cbg == $cbgItem->cbg ? 'selected' : '' }}>
															{{ $cbgItem->cbg }} - {{ $cbgItem->nama }}
														</option>
													@endforeach
												@endif
											</select>
										</div>
										<div class="col-md-3">
											<label for="txtNoUsulan"><strong>No Usulan:</strong></label>
											<input type="text" class="form-control" id="txtNoUsulan" placeholder="Contoh: TRN001" maxlength="20">
											<small class="form-text text-muted">Kosongkan untuk ambil semua data</small>
										</div>
										<div class="col-md-6" style="padding-top: 32px;">
											<button type="button" id="btnAmbil" class="btn btn-ambil">
												<i class="fas fa-download"></i> AMBIL
											</button>
											<button type="button" id="btnAmbilSemua" class="btn btn-ambil-semua">
												<i class="fas fa-database"></i> AMBIL SEMUA
											</button>
										</div>
									</div>
								</div>

								<hr>

								<!-- Table Section -->
								<div class="table-wrapper mt-3">
									<table class="table-striped table-bordered table-hover table" id="tableTimbangan" style="width:100%">
										<thead>
											<tr>
												<th width="40px">No</th>
												<th width="100px">PLU Code</th>
												<th width="120px">Unit Price</th>
												<th width="100px">Item Code</th>
												<th width="80px">Flag</th>
												<th width="300px">Commodity 1</th>
												<th width="150px">Ingredient</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td colspan="7" class="text-center">Klik tombol AMBIL atau AMBIL SEMUA untuk menampilkan data</td>
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
		var tableTimbangan;
		var dataTimbangan = [];

		$(document).ready(function() {
			// Initialize DataTable
			initTable();

			// Button Ambil
			$('#btnAmbil').on('click', function() {
				ambilData();
			});

			// Button Ambil Semua
			$('#btnAmbilSemua').on('click', function() {
				ambilSemuaData();
			});

			// Enter key on No Usulan input
			$('#txtNoUsulan').on('keypress', function(e) {
				if (e.which === 13) {
					ambilData();
				}
			});
		});

		function initTable() {
			tableTimbangan = $('#tableTimbangan').DataTable({
				data: [],
				columns: [{
						data: null,
						render: function(data, type, row, meta) {
							return meta.row + 1;
						},
						className: 'text-center'
					},
					{
						data: 'plu',
						className: 'text-center',
						defaultContent: '-'
					},
					{
						data: 'HJBR',
						className: 'text-right',
						render: function(data) {
							return formatNumber(data, 0);
						},
						defaultContent: '0'
					},
					{
						data: 'KD_BRG',
						className: 'text-center',
						defaultContent: '-'
					},
					{
						data: 'FLAG',
						className: 'text-center',
						defaultContent: '-'
					},
					{
						data: 'NA_BRG',
						defaultContent: '-'
					},
					{
						data: 'ingredient',
						className: 'text-center',
						defaultContent: '-'
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

		function ambilData() {
			var cbg = $('#txtCbg').val();
			var noUsulan = $('#txtNoUsulan').val().trim();

			if (!cbg) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Pilih cabang terlebih dahulu!'
				});
				$('#txtCbg').focus();
				return;
			}

			if (!noUsulan) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'No usulan harus diisi! Atau gunakan tombol AMBIL SEMUA.'
				});
				$('#txtNoUsulan').focus();
				return;
			}

			$('#LOADX').show();
			$('#btnAmbil').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> LOADING...');

			$.ajax({
				url: '{{ route('kirimdatatimbangan_cari') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					cbg: cbg,
					no_bukti: noUsulan
				},
				success: function(response) {
					$('#LOADX').hide();
					$('#btnAmbil').prop('disabled', false).html('<i class="fas fa-download"></i> AMBIL');

					if (response.success && response.data.length > 0) {
						dataTimbangan = response.data;

						tableTimbangan.clear();
						tableTimbangan.rows.add(response.data);
						tableTimbangan.draw();

						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil dimuat: ' + response.count + ' item',
							timer: 2000,
							showConfirmButton: false
						});
					} else {
						dataTimbangan = [];
						tableTimbangan.clear().draw();
						Swal.fire({
							icon: 'info',
							title: 'Informasi',
							text: 'Tidak ada data untuk No Usulan ini'
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					$('#btnAmbil').prop('disabled', false).html('<i class="fas fa-download"></i> AMBIL');
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal mengambil data'
					});
				}
			});
		}

		function ambilSemuaData() {
			var cbg = $('#txtCbg').val();

			if (!cbg) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Pilih cabang terlebih dahulu!'
				});
				$('#txtCbg').focus();
				return;
			}

			$('#LOADX').show();
			$('#btnAmbilSemua').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> LOADING...');

			$.ajax({
				url: '{{ route('kirimdatatimbangan_cari_semua') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					cbg: cbg
				},
				success: function(response) {
					$('#LOADX').hide();
					$('#btnAmbilSemua').prop('disabled', false).html('<i class="fas fa-database"></i> AMBIL SEMUA');

					if (response.success && response.data.length > 0) {
						dataTimbangan = response.data;

						tableTimbangan.clear();
						tableTimbangan.rows.add(response.data);
						tableTimbangan.draw();

						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil dimuat: ' + response.count + ' item',
							timer: 2000,
							showConfirmButton: false
						});
					} else {
						dataTimbangan = [];
						tableTimbangan.clear().draw();
						Swal.fire({
							icon: 'info',
							title: 'Informasi',
							text: 'Tidak ada data timbangan untuk cabang ini'
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					$('#btnAmbilSemua').prop('disabled', false).html('<i class="fas fa-database"></i> AMBIL SEMUA');
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal mengambil data'
					});
				}
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
	</script>
@endsection
