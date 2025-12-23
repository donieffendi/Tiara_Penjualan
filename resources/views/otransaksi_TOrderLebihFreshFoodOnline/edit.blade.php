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

		.btn-save {
			background: #28a745;
			border: none;
			color: #fff;
		}

		.btn-refresh {
			background: #17a2b8;
			border: none;
			color: #fff;
		}

		.btn-print {
			background: #007bff;
			border: none;
			color: #fff;
		}

		.btn-process {
			background: #ffc107;
			border: none;
			color: #000;
		}

		.btn-back {
			background: #6c757d;
			border: none;
			color: #fff;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 13px;
			padding: 12px 8px;
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
						<h1 class="m-0">Order Lebih Fresh Food Online - Edit</h1>
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
								<div class="info-box">
									<div class="row">
										<div class="col-md-3"><strong>Tanggal:</strong> {{ $header->TGL ?? '-' }}</div>
										<div class="col-md-3"><strong>Supplier:</strong> {{ $header->NAMA_SUPP ?? '-' }}</div>
										<div class="col-md-3"><strong>Kode Supplier:</strong> {{ $header->KODES ?? '-' }}</div>
										<div class="col-md-3"><strong>Jumlah Item:</strong> {{ $header->JML_ITEM ?? 0 }}</div>
									</div>
								</div>

								<div class="mb-3">
									<button type="button" id="btnSave" class="btn btn-action btn-save">
										<i class="fas fa-save"></i> SAVE
									</button>
									<button type="button" id="btnRefresh" class="btn btn-action btn-refresh">
										<i class="fas fa-sync"></i> REFRESH
									</button>
									<button type="button" id="btnPrint" class="btn btn-action btn-print">
										<i class="fas fa-print"></i> PRINT
									</button>
									<button type="button" id="btnProcess" class="btn btn-action btn-process">
										<i class="fas fa-cogs"></i> PROCESS
									</button>
									<button type="button" id="btnBack" class="btn btn-action btn-back">
										<i class="fas fa-arrow-left"></i> BACK
									</button>
								</div>

								<hr>

								<div class="report-content" style="max-width: 100%; overflow-x: auto;">
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
									            'SUB_ITEM' => [
									                'label' => 'Sub Item',
									            ],
									            'KD_BRG' => [
									                'label' => 'Kode Barang',
									            ],
									            'NAMA_BARANG' => [
									                'label' => 'Nama Barang',
									            ],
									            'KEMASAN' => [
									                'label' => 'Kemasan',
									            ],
									            'QTY' => [
									                'label' => 'Qty',
									                'type' => 'number',
									                'decimals' => 2,
									                'decimalPoint' => '.',
									                'thousandSeparator' => ',',
									                'cssStyle' => 'text-align: right;',
									                'headerCssStyle' => 'text-align: right;',
									            ],
									            'SUPP' => [
									                'label' => 'Supp',
									            ],
									            'NAMA_SUPP' => [
									                'label' => 'Nama Supplier',
									            ],
									            'TGL_KIRIM' => [
									                'label' => 'Tgl Kirim',
									            ],
									        ],
									        'options' => [
									            'paging' => true,
									            'searching' => true,
									            'ordering' => true,
									            'info' => true,
									            'pageLength' => 25,
									            'dom' => 'Blfrtip',
									            'buttons' => [
									                [
									                    'extend' => 'collection',
									                    'text' => 'Export',
									                    'buttons' => ['copy', 'excel', 'csv', 'pdf', 'print'],
									                ],
									            ],
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
		var identifier = '{{ $identifier ?? '' }}';

		$(document).ready(function() {
			$('#btnBack').on('click', function() {
				window.location.href = '{{ route('orderlebihfreshfoodonline') }}';
			});

			$('#btnRefresh').on('click', function() {
				location.reload();
			});

			$('#btnSave').on('click', function() {
				Swal.fire({
					title: 'Save Data?',
					text: 'Apakah Anda yakin ingin menyimpan perubahan?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#28a745',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Ya, Simpan!',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						saveData();
					}
				});
			});

			$('#btnPrint').on('click', function() {
				printData(identifier);
			});

			$('#btnProcess').on('click', function() {
				Swal.fire({
					title: 'Process Data?',
					text: 'Kirim data ke server untuk diproses?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#ffc107',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Ya, Process!',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						processData(identifier);
					}
				});
			});
		});

		function saveData() {
			$('#LOADX').show();
			$.ajax({
				url: '{{ route('orderlebihfreshfoodonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'save',
					identifier: identifier
				},
				success: function(response) {
					$('#LOADX').hide();
					if (response.success) {
						Swal.fire('Berhasil!', response.message, 'success');
					} else {
						Swal.fire('Gagal!', response.error || 'Gagal menyimpan data', 'error');
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					var error = 'Gagal menyimpan data';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						error = xhr.responseJSON.error;
					}
					Swal.fire('Error!', error, 'error');
				}
			});
		}

		function printData(identifier) {
			var url = '{{ route('orderlebihfreshfoodonline_jasper') }}?namafile=' + encodeURIComponent(identifier);
			window.open(url, '_blank');
		}

		function processData(identifier) {
			$('#LOADX').show();
			$.ajax({
				url: '{{ route('orderlebihfreshfoodonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'export_dbf',
					namafile: identifier
				},
				success: function(response) {
					$('#LOADX').hide();
					if (response.success) {
						Swal.fire('Berhasil!', response.message, 'success').then(() => {
							window.location.href = '{{ route('orderlebihfreshfoodonline') }}';
						});
					} else {
						Swal.fire('Gagal!', response.error || 'Gagal memproses data', 'error');
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					var error = 'Gagal memproses data';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						error = xhr.responseJSON.error;
					}
					Swal.fire('Error!', error, 'error');
				}
			});
		}
	</script>
@endsection
