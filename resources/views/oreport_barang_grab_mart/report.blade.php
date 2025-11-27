@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Barang GrabMart</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Barang GrabMart</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				@if (isset($error))
					<div class="alert alert-danger">
						<i class="fas fa-exclamation-triangle mr-2"></i>{{ $error }}
					</div>
				@endif

				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form method="GET" action="{{ route('get-baranggrabmart-report') }}" id="reportForm">
									@csrf

									<div class="row align-items-end mb-4">
										<div class="col-3">
											<div class="form-group">
												<div class="custom-control custom-checkbox">
													<input type="checkbox" class="custom-control-input" id="active_only" name="active_only" value="1"
														{{ session()->get('filter_active_only', true) ? 'checked' : '' }}>
													<label class="custom-control-label" for="active_only">
														<i class="fas fa-filter mr-1"></i>Hanya Tampilkan Barang Aktif
													</label>
												</div>
											</div>
										</div>
										<div class="col-4">
											<button class="btn btn-primary" type="submit" id="btnFilter">
												<i class="fas fa-search mr-1"></i>Filter Data
											</button>
											<button class="btn btn-danger ml-2" type="button" onclick="resetFilter()">
												<i class="fas fa-redo mr-1"></i>Reset
											</button>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-12">
											<button class="btn btn-success mr-2" type="button" onclick="exportData('excel')">
												<i class="fas fa-file-excel mr-1"></i>Export Excel
											</button>
											<button class="btn btn-warning" type="button" onclick="cetakLaporan()">
												<i class="fas fa-print mr-1"></i>Cetak Laporan
											</button>
										</div>
									</div>
								</form>

								<div class="report-content">
									@if (count($hasilData ?? []) > 0)
										<div class="table-responsive">
											<table class="table-hover table-striped table-bordered compact table" id="barang-grabmart-table">
												<thead>
													<tr>
														<th>Store Number</th>
														<th>Barcode</th>
														<th>SKU</th>
														<th>Brand</th>
														<th>Product Description</th>
														<th>Category</th>
														<th>Normal Price</th>
														<th>Status</th>
													</tr>
												</thead>
												<tbody>
													@foreach ($hasilData as $item)
														<tr>
															<td>{{ $item->StoreNumber ?? '' }}</td>
															<td>{{ $item->Barcode ?? '' }}</td>
															<td>{{ $item->SKU ?? '' }}</td>
															<td>{{ $item->Brand ?? '' }}</td>
															<td>{{ $item->Product_Description ?? '' }}</td>
															<td>{{ $item->Category ?? '' }}</td>
															<td class="text-right">{{ number_format($item->Normal_Price ?? 0, 0, ',', '.') }}</td>
															<td class="text-center">
																<span class="badge badge-{{ ($item->Status ?? '') == 'Active' ? 'success' : 'secondary' }}">
																	{{ $item->Status ?? 'Unknown' }}
																</span>
															</td>
														</tr>
													@endforeach
												</tbody>
											</table>
										</div>
									@else
										<div class="alert alert-info">
											<i class="fas fa-info-circle mr-2"></i>
											@if (session()->get('filter_active_only'))
												Tidak ada data barang aktif GrabMart yang ditemukan.
											@else
												Silakan klik tombol "Filter Data" untuk menampilkan data barang GrabMart.
											@endif
										</div>
									@endif
								</div>

								@if (count($hasilData ?? []) > 0)
									<div class="row mt-4">
										<div class="col-12">
											<div class="card card-outline card-info">
												<div class="card-header">
													<h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Ringkasan Data</h3>
												</div>
												<div class="card-body">
													<div class="row">
														<div class="col-md-4">
															<div class="info-box bg-success">
																<span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Barang</span>
																	<span class="info-box-number">{{ count($hasilData) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-4">
															<div class="info-box bg-primary">
																<span class="info-box-icon"><i class="fas fa-tags"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Kategori Unik</span>
																	<span class="info-box-number">{{ count(collect($hasilData)->unique('Category')) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-4">
															<div class="info-box bg-warning">
																<span class="info-box-icon"><i class="fas fa-copyright"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Brand Unik</span>
																	<span class="info-box-number">{{ count(collect($hasilData)->unique('Brand')) }}</span>
																</div>
															</div>
														</div>
													</div>
													<div class="row mt-2">
														<div class="col-12">
															<small class="text-muted">
																<i class="fas fa-filter mr-1"></i>Filter:
																<strong>{{ session()->get('filter_active_only') ? 'Barang Aktif Saja' : 'Semua Barang' }}</strong> |
																<i class="fas fa-clock mr-1"></i>Generated: {{ date('d/m/Y H:i:s') }}
															</small>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script>
		$(document).ready(function() {
			initializeDataTables();

			$('#reportForm').on('submit', function(e) {
				$('.btn-primary').html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
				$('.btn-primary').prop('disabled', true);
			});

			$('#active_only').on('change', function() {
				$('#reportForm').submit();
			});
		});

		function initializeDataTables() {
			var commonOptions = {
				pageLength: 25,
				searching: true,
				ordering: true,
				responsive: true,
				scrollX: true,
				fixedHeader: true,
				dom: 'Blfrtip',
				buttons: [{
					extend: 'collection',
					text: 'Export',
					buttons: ['copy', 'excel', 'csv', 'pdf', 'print']
				}],
				language: {
					url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
				}
			};

			var tableOptions = {
				...commonOptions,
				columnDefs: [{
					className: 'dt-center',
					targets: [0, 1, 2, 7]
				}, {
					className: 'dt-right',
					targets: [6]
				}]
			};

			if ($('#barang-grabmart-table').length && $('#barang-grabmart-table tbody tr').length > 0) {
				var table = $('#barang-grabmart-table').DataTable(tableOptions);
				window.barangGrabMartTable = table;
			}
		}

		function resetFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter?')) {
				$('#active_only').prop('checked', true);
				$('#reportForm').submit();
			}
		}

		function exportData(format) {
			var params = new URLSearchParams({
				active_only: $('#active_only').is(':checked') ? 1 : 0,
				format: format
			});

			var url = '{{ route('jasper-baranggrabmart-report') }}?' + params.toString();
			downloadReport(url);
		}

		function cetakLaporan() {
			var params = new URLSearchParams({
				active_only: $('#active_only').is(':checked') ? 1 : 0
			});

			var url = '{{ route('jasper-baranggrabmart-report') }}?' + params.toString();
			printReport(url);
		}

		function downloadReport(url) {
			$('.btn-success').html('<i class="fas fa-spinner fa-spin mr-1"></i>Exporting...');
			$('.btn-success').prop('disabled', true);

			var form = $('<form>', {
				'method': 'POST',
				'action': url
			});

			form.append($('<input>', {
				'type': 'hidden',
				'name': '_token',
				'value': $('meta[name="csrf-token"]').attr('content')
			}));

			form.appendTo('body').submit().remove();

			setTimeout(function() {
				$('.btn-success').html('<i class="fas fa-file-excel mr-1"></i>Export Excel');
				$('.btn-success').prop('disabled', false);
			}, 3000);
		}

		function printReport(url) {
			var form = $('<form>', {
				'method': 'POST',
				'action': url,
				'target': '_blank'
			});

			form.append($('<input>', {
				'type': 'hidden',
				'name': '_token',
				'value': $('meta[name="csrf-token"]').attr('content')
			}));

			form.appendTo('body').submit().remove();
		}

		$(function() {
			$('[data-toggle="tooltip"]').tooltip();
		});

		$(document).keydown(function(e) {
			if (e.ctrlKey && e.keyCode === 70) {
				e.preventDefault();
				var activeTable = $('#barang-grabmart-table').DataTable();
				if (activeTable) {
					activeTable.search('').draw();
					$('.dataTables_filter input').focus();
				}
			}
		});

		$(window).on('resize', function() {
			$.fn.dataTable.tables({
				visible: true,
				api: true
			}).columns.adjust().responsive.recalc();
		});

		$(document).ajaxError(function(event, jqXHR, settings, thrownError) {
			console.error('AJAX Error:', thrownError);
			alert('Terjadi kesalahan saat memuat data. Silakan coba lagi.');
		});
	</script>

	<style>
		.table-responsive {
			border: 1px solid #dee2e6;
			border-radius: 0.25rem;
		}

		.info-box {
			border-radius: 0.5rem;
			box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
		}

		.badge {
			font-size: 0.7em;
		}

		.compact.table td {
			padding: 0.3rem;
			font-size: 0.875rem;
		}

		.compact.table th {
			padding: 0.5rem 0.3rem;
			font-size: 0.875rem;
			font-weight: 600;
		}

		.text-right {
			text-align: right !important;
		}

		.text-center {
			text-align: center !important;
		}

		.alert {
			margin: 1rem;
		}

		.form-control:focus {
			border-color: #80bdff;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
		}

		.btn:focus {
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
		}

		.dataTables_wrapper .dataTables_length select {
			width: 75px;
		}

		.dataTables_wrapper .dataTables_filter input {
			border-radius: 0.25rem;
		}

		@media (max-width: 768px) {

			.col-3,
			.col-4 {
				margin-bottom: 1rem;
			}

			.btn-group .btn {
				margin-bottom: 0.5rem;
			}
		}

		@media (max-width: 576px) {
			.card-body {
				padding: 0.5rem;
			}

			.btn {
				font-size: 0.875rem;
				padding: 0.375rem 0.5rem;
			}

			.table-responsive {
				font-size: 0.8rem;
			}
		}

		@media print {

			.btn,
			.card-header,
			.breadcrumb {
				display: none !important;
			}

			.table {
				font-size: 10px;
			}

			.info-box {
				break-inside: avoid;
			}
		}

		.table-responsive {
			max-height: 70vh;
			overflow-y: auto;
		}

		.table thead th {
			position: sticky;
			top: 0;
			background-color: #f8f9fa;
			z-index: 10;
		}

		.card {
			box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
		}

		.card-outline.card-info {
			border-top: 3px solid #17a2b8;
		}

		.table-responsive::-webkit-scrollbar {
			width: 8px;
			height: 8px;
		}

		.table-responsive::-webkit-scrollbar-track {
			background: #f1f1f1;
		}

		.table-responsive::-webkit-scrollbar-thumb {
			background: #c1c1c1;
			border-radius: 4px;
		}

		.table-responsive::-webkit-scrollbar-thumb:hover {
			background: #a8a8a8;
		}

		.custom-control-label {
			font-weight: 500;
		}
	</style>
@endsection
