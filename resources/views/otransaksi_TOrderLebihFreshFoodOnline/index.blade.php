@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<style>
		/* reuse styles from original view */
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

		.btn-new {
			background: #28a745;
			border: none;
			color: #fff;
		}

		.btn-new:hover {
			background: #218838;
			color: #fff;
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

		.loader {
			position: fixed;
			top: 50%;
			left: 50%;
			width: 100px;
			aspect-ratio: 1;
			background: radial-gradient(farthest-side, #ffa516 90%, #0000) center/16px 16px, radial-gradient(farthest-side, green 90%, #0000) bottom/12px 12px;
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
	</style>
@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Order Lebih Fresh Food Online</h1>
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
								<div class="info-box">
									<p class="mb-1"><strong>Petunjuk:</strong></p>
									<ul class="mb-0">
										<li>Klik <strong>NEW</strong> untuk membuat order baru</li>
										<li>Klik <strong>Edit</strong> untuk mengubah detail order</li>
										<li>Klik <strong>Print</strong> untuk mencetak laporan</li>
										<li>Klik <strong>Kirim</strong> untuk mengirim data ke server</li>
										<li>Klik <strong>Hapus</strong> untuk menghapus order</li>
									</ul>
								</div>

								<div class="mb-3 text-right">
									<button type="button" id="btnNew" class="btn btn-action btn-new">
										<i class="fas fa-plus"></i> NEW
									</button>
								</div>

								<hr>

								<div class="report-content" col-md-12 style="max-width: 100%; overflow-x: auto;">
									<?php
									use koolreport\datagrid\DataTables;
									
									if (isset($hasil) && $hasil) {
									    DataTables::create([
									        'dataSource' => $hasil,
									        'name' => 'tableData',
									        'fastRender' => true,
									        'fixedHeader' => true,
									        'scrollX' => true,
									        'showFooter' => false,
									        'columns' => [
									            'NO' => [
									                'label' => 'No',
									                'cssStyle' => 'text-align: center;',
									                'headerCssStyle' => 'text-align: center;',
									            ],
									            'NAMA' => [
									                'label' => 'Nama File',
									            ],
									            'TGL' => [
									                'label' => 'Tanggal',
									            ],
									            'KODE' => [
									                'label' => 'Kode',
									                'cssStyle' => 'text-align: center;',
									                'headerCssStyle' => 'text-align: center;',
									            ],
									            'SUPLIER' => [
									                'label' => 'Suplier',
									            ],
									            'OUTLET' => [
									                'label' => 'Outlet',
									            ],
									            'AKSI' => [
									                'label' => 'Aksi',
									                'cssStyle' => 'text-align: center;',
									                'headerCssStyle' => 'text-align: center;',
									                'type' => 'string',
									            ],
									        ],
									        'options' => [
									            'paging' => true,
									            'searching' => true,
									            'ordering' => true,
									            'info' => true,
									            'order' => [[2, 'desc']],
									        ],
									    ]);
									}
									?>
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
		$(document).ready(function() {
			// Button New
			$('#btnNew').on('click', function() {
				window.location.href = '{{ route('orderlebihfreshfoodonline') }}/new';
			});

			// Edit / Print / Send / Delete handlers (use online routes)
			$(document).on('click', '.btn-edit', function() {
				var namafile = $(this).data('file');
				window.location.href = '{{ route('orderlebihfreshfoodonline_edit') }}?namafile=' + encodeURIComponent(namafile);
			});
			$(document).on('click', '.btn-print', function() {
				var namafile = $(this).data('file');
				printData(namafile);
			});
			$(document).on('click', '.btn-send', function() {
				var namafile = $(this).data('file');
				if (confirm('Kirim file ' + namafile + '?')) sendData(namafile);
			});
			$(document).on('click', '.btn-delete', function() {
				var namafile = $(this).data('file');
				if (confirm('Hapus file ' + namafile + '?')) deleteData(namafile);
			});
		});

		function printData(namafile) {
			// Open jasper print in new window
			var url = '{{ route('orderlebihfreshfoodonline_jasper') }}?namafile=' + encodeURIComponent(namafile);
			window.open(url, '_blank');
		}

		function sendData(namafile) {
			$('#LOADX').show();
			$.ajax({
				url: '{{ route('orderlebihfreshfoodonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'export_dbf',
					namafile: namafile
				},
				success: function(response) {
					$('#LOADX').hide();
					if (response.success) {
						alert(response.message);
						location.reload();
					} else {
						alert(response.error || 'Gagal mengirim data');
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					var error = 'Gagal mengirim data';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						error = xhr.responseJSON.error;
					}
					alert(error);
				}
			});
		}

		function deleteData(namafile) {
			$('#LOADX').show();
			$.ajax({
				url: '{{ route('orderlebihfreshfoodonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'delete',
					namafile: namafile
				},
				success: function(response) {
					$('#LOADX').hide();
					if (response.success) {
						alert(response.message);
						location.reload();
					} else {
						alert(response.error || 'Gagal menghapus data');
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					var error = 'Gagal menghapus data';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						error = xhr.responseJSON.error;
					}
					alert(error);
				}
			});
		}
	</script>
@endsection
