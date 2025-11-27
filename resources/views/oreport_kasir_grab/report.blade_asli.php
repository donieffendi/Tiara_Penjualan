@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Sales Kasir Grab</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Sales Kasir Grab</li>
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
								<form method="GET" action="{{ route('get-kasirgrab-report') }}" id="reportForm">
									@csrf

									<!-- Filter Controls -->
									<div class="row align-items-end mb-4">
										<div class="col-3">
											<label for="cbg">Cabang</label>
											<select name="cbg" id="cbg" class="form-control" required>
												<option value="">Pilih Cabang</option>
												@foreach ($cbg as $cabang)
													<option value="{{ $cabang->KODE }}" {{ session()->get('filter_cbg') == $cabang->KODE ? 'selected' : '' }}>
														{{ $cabang->KODE }}
													</option>
												@endforeach
											</select>
										</div>
										<div class="col-3">
											<label for="kasir">Kasir</label>
											<select name="kasir" id="kasir" class="form-control" required>
												<option value="">Pilih Kasir</option>
												@foreach ($kasir as $cabang)
													<option value="{{ $cabang->KASIR }}" {{ session()->get('filter_kasir') == $cabang->KASIR ? 'selected' : '' }}>
														{{ $cabang->KASIR }}
													</option>
												@endforeach
											</select>
										</div>
										<div class="col-3">
											<label for="periode">Periode</label>
											<select name="periode" id="periode" class="form-control">
												<option value="">Pilih Periode</option>
												@foreach ($periods as $period)
													<option value="{{ $period->PERIO }}" {{ session()->get('filter_periode') == $period->PERIO ? 'selected' : '' }}>
														{{ $period->PERIO }}
													</option>
												@endforeach
											</select>
										</div>
										<div class="col-3">
											<button class="btn btn-primary btn-block" type="submit" id="btnFilter">
												<i class="fas fa-search mr-1"></i>Filter
											</button>
										</div>
									</div>

									<!-- Action Buttons -->
									<div class="row mb-3">
										<div class="col-12">
											<button class="btn btn-danger mr-2" type="button" onclick="resetFilter()">
												<i class="fas fa-redo mr-1"></i>Reset
											</button>
											<button class="btn btn-success mr-2" type="button" onclick="exportData('excel')">
												<i class="fas fa-file-excel mr-1"></i>Export Excel
											</button>
											<button class="btn btn-warning" type="button" onclick="cetakLaporan()">
												<i class="fas fa-print mr-1"></i>Cetak Laporan
											</button>
										</div>
									</div>
								</form>

								<!-- Report Content -->
								<div class="report-content">
									@if (count($hasilData ?? []) > 0)
										<div class="table-responsive">
											<table class="table-hover table-striped table-bordered compact table" id="kasir-grab-table">
												<thead>
													<tr>
														<th>Cabang</th>
														<th>Kasir</th>
														<th>Tanggal</th>
														<th>Shift</th>
														<th>No Bukti</th>
														<th>Kode Barang</th>
														<th>Nama Barang</th>
														<th>Qty</th>
														<th>Harga</th>
														<th>Total</th>
														<th>DPP</th>
														<th>PPN</th>
													</tr>
												</thead>
												<tbody>
													@foreach ($hasilData as $item)
														<tr>
															<td>{{ $item->cbg ?? '' }}</td>
															<td>{{ $item->ksr ?? '' }}</td>
															<td>{{ $item->tgl ?? '' }}</td>
															<td class="text-center">{{ $item->shift ?? '' }}</td>
															<td>{{ $item->no_bukti ?? '' }}</td>
															<td>{{ $item->kd_brg ?? '' }}</td>
															<td>{{ $item->na_brg ?? '' }}</td>
															<td class="text-right">{{ number_format($item->qty ?? 0, 0, ',', '.') }}</td>
															<td class="text-right">{{ number_format($item->harga ?? 0, 0, ',', '.') }}</td>
															<td class="text-right">{{ number_format($item->total ?? 0, 0, ',', '.') }}</td>
															<td class="text-right">{{ number_format($item->dpp ?? 0, 0, ',', '.') }}</td>
															<td class="text-right">{{ number_format($item->nppn ?? 0, 0, ',', '.') }}</td>
														</tr>
													@endforeach
												</tbody>
											</table>
										</div>
									@else
										<div class="alert alert-info">
											<i class="fas fa-info-circle mr-2"></i>
											@if (session()->get('filter_cbg') && session()->get('filter_periode'))
												Tidak ada data kasir grab untuk periode <strong>{{ session()->get('filter_periode') }}</strong>
												di cabang <strong>{{ session()->get('filter_cbg') }}</strong>
												@if (session()->get('filter_kasir'))
													dengan kasir <strong>{{ session()->get('filter_kasir') }}</strong>
												@endif.
											@else
												Silakan pilih cabang dan periode untuk menampilkan data kasir grab.
											@endif
										</div>
									@endif
								</div>

								<!-- Summary Information -->
								@if (session()->get('filter_cbg') && session()->get('filter_periode'))
									<div class="row mt-4">
										<div class="col-12">
											<div class="card card-outline card-info">
												<div class="card-header">
													<h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Ringkasan Laporan Kasir Grab</h3>
												</div>
												<div class="card-body">
													<div class="row">
														<div class="col-md-3">
															<div class="info-box bg-success">
																<span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Transaksi</span>
																	<span class="info-box-number">{{ collect($hasilData ?? [])->unique('no_bukti')->count() }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-primary">
																<span class="info-box-icon"><i class="fas fa-cube"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Item</span>
																	<span class="info-box-number">{{ collect($hasilData ?? [])->unique('kd_brg')->count() }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-warning">
																<span class="info-box-icon"><i class="fas fa-sort-numeric-up"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Qty</span>
																	<span class="info-box-number">{{ number_format(collect($hasilData ?? [])->sum('qty'), 0, ',', '.') }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-info">
																<span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Amount</span>
																	<span class="info-box-number">Rp {{ number_format(collect($hasilData ?? [])->sum('total'), 0, ',', '.') }}</span>
																</div>
															</div>
														</div>
													</div>
													<div class="row mt-2">
														<div class="col-12">
															<small class="text-muted">
																<i class="fas fa-store mr-1"></i>Cabang:
																<strong>{{ session()->get('filter_cbg') }}</strong> |
																<i class="fas fa-user mr-1"></i>Kasir:
																<strong>{{ session()->get('filter_kasir') ?: 'Semua Kasir' }}</strong> |
																<i class="fas fa-calendar mr-1"></i>Periode:
																<strong>{{ session()->get('filter_periode') }}</strong> |
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
			// Initialize DataTables
			initializeDataTable();

			// Form validation
			$('#reportForm').on('submit', function(e) {
				var cbg = $('#cbg').val();
				var periode = $('#periode').val();

				if (!cbg) {
					e.preventDefault();
					alert('Cabang harus dipilih');
					return false;
				}
				if (!periode) {
					e.preventDefault();
					alert('Periode harus dipilih');
					return false;
				}

				// Show loading
				$('#btnFilter').html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
				$('#btnFilter').prop('disabled', true);
			});

			// Handle cbg change untuk update kasir dropdown
			// $('#cbg').change(function() {
			// 	var cbg = $(this).val();
			// 	var kasirSelect = $('#kasir');

			// 	// Clear kasir options
			// 	kasirSelect.html('<option value="">Semua Kasir</option>');

			// 	if (cbg) {
			// 		// Load kasir berdasarkan cabang
			// 		$.ajax({
			// 			url: '/api/get-dropdown-data',
			// 			method: 'GET',
			// 			data: {
			// 				type: 'kasir',
			// 				cbg: cbg
			// 			},
			// 			success: function(response) {
			// 				if (response.success && response.data) {
			// 					response.data.forEach(function(kasir) {
			// 						kasirSelect.append('<option value="' + kasir.kasir + '">' + kasir.kasir +
			// 							'</option>');
			// 					});
			// 				}
			// 			},
			// 			error: function() {
			// 				console.log('Error loading kasir data');
			// 			}
			// 		});
			// 	}
			// });
		});

		// Initialize DataTable
		function initializeDataTable() {
			if ($('#kasir-grab-table').length && $('#kasir-grab-table tbody tr').length > 0) {
				var table = $('#kasir-grab-table').DataTable({
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
					},
					columnDefs: [{
						className: 'dt-center',
						targets: [0, 1, 2, 3, 4, 5] // Cabang, Kasir, Tanggal, Shift, No Bukti, Kode Barang
					}, {
						className: 'dt-right',
						targets: [7, 8, 9, 10, 11] // Qty, Harga, Total, DPP, PPN
					}],
					footerCallback: function(row, data, start, end, display) {
						var api = this.api();

						// Calculate totals
						var totalQty = api.column(7, {
							page: 'current'
						}).data().reduce(function(a, b) {
							return (parseFloat(a) || 0) + (parseFloat(b.replace(/[,.]/g, '')) || 0);
						}, 0);

						var totalAmount = api.column(9, {
							page: 'current'
						}).data().reduce(function(a, b) {
							return (parseFloat(a) || 0) + (parseFloat(b.replace(/[,.]/g, '')) || 0);
						}, 0);

						var totalDpp = api.column(10, {
							page: 'current'
						}).data().reduce(function(a, b) {
							return (parseFloat(a) || 0) + (parseFloat(b.replace(/[,.]/g, '')) || 0);
						}, 0);

						var totalPpn = api.column(11, {
							page: 'current'
						}).data().reduce(function(a, b) {
							return (parseFloat(a) || 0) + (parseFloat(b.replace(/[,.]/g, '')) || 0);
						}, 0);

						// Update footer if exists
						if ($(api.column(7).footer()).length) {
							$(api.column(7).footer()).html(formatNumber(totalQty));
							$(api.column(9).footer()).html(formatNumber(totalAmount));
							$(api.column(10).footer()).html(formatNumber(totalDpp));
							$(api.column(11).footer()).html(formatNumber(totalPpn));
						}
					}
				});
				window.kasirGrabTable = table;
			}
		}

		// Reset filter function
		function resetFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter?')) {
				$('#cbg').val('');
				$('#kasir').val('');
				$('#periode').val('');
				$('#reportForm').submit();
			}
		}

		// Export data function
		function exportData(format) {
			var cbg = $('#cbg').val();
			var kasir = $('#kasir').val();
			var periode = $('#periode').val();

			if (!cbg) {
				alert('Silakan pilih cabang terlebih dahulu');
				return;
			}
			if (!periode) {
				alert('Silakan pilih periode terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				cbg: cbg,
				kasir: kasir,
				periode: periode,
				format: format
			});

			var url = '{{ route('jasper-kasirgrab-report') }}?' + params.toString();
			downloadReport(url);
		}

		// Print report function
		function cetakLaporan() {
			var cbg = $('#cbg').val();
			var kasir = $('#kasir').val();
			var periode = $('#periode').val();

			if (!cbg || !periode) {
				alert('Silakan lengkapi filter terlebih dahulu (Cabang dan Periode)');
				return;
			}

			var params = new URLSearchParams({
				cbg: cbg,
				kasir: kasir,
				periode: periode
			});

			var url = '{{ route('jasper-kasirgrab-report') }}?' + params.toString();
			printReport(url);
		}

		// Download report helper
		function downloadReport(url) {
			// Show loading
			$('.btn-success').html('<i class="fas fa-spinner fa-spin mr-1"></i>Exporting...');
			$('.btn-success').prop('disabled', true);

			// Create hidden form for download
			var form = $('<form>', {
				'method': 'POST',
				'action': url
			});

			// Add CSRF token
			form.append($('<input>', {
				'type': 'hidden',
				'name': '_token',
				'value': $('meta[name="csrf-token"]').attr('content')
			}));

			form.appendTo('body').submit().remove();

			// Reset button after 3 seconds
			setTimeout(function() {
				$('.btn-success').html('<i class="fas fa-file-excel mr-1"></i>Export Excel');
				$('.btn-success').prop('disabled', false);
			}, 3000);
		}

		// Print report helper
		function printReport(url) {
			// Create form for POST request
			var form = $('<form>', {
				'method': 'POST',
				'action': url,
				'target': '_blank'
			});

			// Add CSRF token
			form.append($('<input>', {
				'type': 'hidden',
				'name': '_token',
				'value': $('meta[name="csrf-token"]').attr('content')
			}));

			form.appendTo('body').submit().remove();
		}

		// Handle keyboard shortcuts
		$(document).keydown(function(e) {
			// Ctrl+F for search
			if (e.ctrlKey && e.keyCode === 70) {
				e.preventDefault();
				if (window.kasirGrabTable) {
					window.kasirGrabTable.search('').draw();
					$('.dataTables_filter input').focus();
				}
			}
		});

		// Handle responsive table adjustments
		$(window).on('resize', function() {
			if (window.kasirGrabTable) {
				window.kasirGrabTable.columns.adjust().responsive.recalc();
			}
		});

		// Error handling for AJAX requests
		$(document).ajaxError(function(event, jqXHR, settings, thrownError) {
			console.error('AJAX Error:', thrownError);
			alert('Terjadi kesalahan saat memuat data. Silakan coba lagi.');
		});

		// Initialize tooltips
		$('[data-toggle="tooltip"]').tooltip({
			placement: 'top',
			trigger: 'hover'
		});

		// Add search functionality to select elements
		if (typeof $.fn.select2 !== 'undefined') {
			$('#cbg, #kasir, #periode').select2({
				theme: 'bootstrap4',
				width: '100%'
			});
		}

		// Format currency function
		function formatCurrency(amount) {
			return 'Rp ' + parseFloat(amount || 0).toLocaleString('id-ID');
		}

		// Format number function
		function formatNumber(number) {
			return parseFloat(number || 0).toLocaleString('id-ID');
		}
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

		.compact.table td {
			padding: 0.3rem;
			font-size: 0.875rem;
		}

		.compact.table th {
			padding: 0.5rem 0.3rem;
			font-size: 0.875rem;
			font-weight: 600;
		}

		@media (max-width: 768px) {
			.col-3 {
				margin-bottom: 1rem;
			}

			.btn-group .btn {
				margin-bottom: 0.5rem;
			}
		}

		/* Custom styling for numeric columns */
		.text-right {
			text-align: right !important;
		}

		.text-center {
			text-align: center !important;
		}

		/* Highlight numeric columns */
		.table thead th:nth-child(8),
		.table thead th:nth-child(9),
		.table thead th:nth-child(10),
		.table thead th:nth-child(11),
		.table thead th:nth-child(12) {
			background-color: #e8f5e8;
			font-weight: bold;
		}

		.table tbody td:nth-child(8),
		.table tbody td:nth-child(9),
		.table tbody td:nth-child(10),
		.table tbody td:nth-child(11),
		.table tbody td:nth-child(12) {
			background-color: #f8f9fa;
			font-weight: 600;
		}

		/* Form enhancements */
		.form-control:focus {
			border-color: #80bdff;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
		}

		.btn:focus {
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
		}

		/* DataTables customization */
		.dataTables_wrapper .dataTables_length select {
			width: 75px;
		}

		.dataTables_wrapper .dataTables_filter input {
			border-radius: 0.25rem;
		}

		/* Responsive improvements */
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

		/* Print styles */
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

		/* Sticky header for tables */
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

		/* Alert styling */
		.alert {
			margin: 1rem 0;
		}

		/* Card styling */
		.card {
			box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
		}

		.card-header {
			background-color: #f8f9fa;
			border-bottom: 1px solid #dee2e6;
		}

		/* Button group spacing */
		.btn+.btn {
			margin-left: 0.5rem;
		}

		/* Loading overlay */
		.loading-overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.5);
			display: flex;
			justify-content: center;
			align-items: center;
			z-index: 9999;
		}

		.spinner {
			width: 50px;
			height: 50px;
			border: 5px solid #f3f3f3;
			border-top: 5px solid #3498db;
			border-radius: 50%;
			animation: spin 1s linear infinite;
		}

		@keyframes spin {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		/* Kasir field styling */
		#kasir {
			border-left: 3px solid #17a2b8;
		}

		/* Table hover effects */
		.table-hover tbody tr:hover {
			background-color: rgba(0, 123, 255, 0.075);
		}
	</style>
@endsection
