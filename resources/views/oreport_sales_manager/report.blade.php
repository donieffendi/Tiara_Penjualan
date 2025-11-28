@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Sales Manager</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Sales Manager</li>
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
								<form method="GET" action="{{ route('get-salesmanager-report') }}" id="reportForm">
									@csrf

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="salesManagerTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ !isset($reportType) || $reportType == 1 ? 'active' : '' }}" id="detail-report-tab" data-toggle="tab"
												href="#detail-report" role="tab" aria-controls="detail-report"
												aria-selected="{{ !isset($reportType) || $reportType == 1 ? 'true' : 'false' }}" onclick="setReportType(1)">
												<i class="fas fa-list-alt text-success mr-1"></i>Detail Report
												@if (count($hasilDetail ?? []) > 0)
													<span class="badge badge-success ml-1">{{ count($hasilDetail) }}</span>
												@endif
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ isset($reportType) && $reportType == 2 ? 'active' : '' }}" id="summary-report-tab" data-toggle="tab"
												href="#summary-report" role="tab" aria-controls="summary-report"
												aria-selected="{{ isset($reportType) && $reportType == 2 ? 'true' : 'false' }}" onclick="setReportType(2)">
												<i class="fas fa-chart-bar text-primary mr-1"></i>Per Minggu
												@if (count($hasilSummary ?? []) > 0)
													<span class="badge badge-primary ml-1">{{ count($hasilSummary) }}</span>
												@endif
											</a>
										</li>
									</ul>

									<!-- Hidden field for report type -->
									<input type="hidden" name="report_type" id="report_type" value="{{ $reportType ?? 1 }}">

									<!-- Tab panes -->
									<div class="tab-content" id="salesManagerTabContent">
										<!-- Detail Report Tab -->
										<div class="tab-pane fade {{ !isset($reportType) || $reportType == 1 ? 'show active' : '' }}" id="detail-report" role="tabpanel"
											aria-labelledby="detail-report-tab">
											<div class="pt-3">
												<!-- Detail Report Filter Controls -->
												<div class="row align-items-end mb-4">
													<div class="col-4">
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
													<div class="col-4">
														<button class="btn btn-primary" type="submit" id="btnFilterDetail">
															<i class="fas fa-search mr-1"></i>Filter
														</button>
														<button class="btn btn-danger ml-2" type="button" onclick="resetDetailFilter()">
															<i class="fas fa-redo mr-1"></i>Reset
														</button>
													</div>
												</div>

												<!-- Detail Report Actions -->
												<div class="row mb-3">
													<div class="col-12">
														<button class="btn btn-success mr-2" type="button" onclick="exportDetailData('excel')">
															<i class="fas fa-file-excel mr-1"></i>Export Excel
														</button>
														<button class="btn btn-warning" type="button" onclick="cetakDetailLaporan()">
															<i class="fas fa-print mr-1"></i>Cetak Laporan
														</button>
													</div>
												</div>

												<!-- Detail Report Content -->
												<div class="report-content">
													@if (count($hasilDetail ?? []) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="detail-report-table">
																<thead>
																	<tr>
																		<th>SUB</th>
																		<th>SUB2</th>
																		<th>Kode Barang</th>
																		<th>Nama Barang</th>
																		<th>Barcode</th>
																		<th>Qty</th>
																		<th>Harga</th>
																		<th>Diskon</th>
																		<th>Disc</th>
																		<th>PPN</th>
																		<th>Nilai PPN</th>
																		<th>DPP</th>
																		<th>TKP</th>
																		<th>Total</th>
																		<th>Flag</th>
																		<th>Type</th>
																		<th>Periode</th>
																		<th>Kodes</th>
																		<th>Tanggal</th>
																		<th>CBG</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilDetail as $item)
																		<tr>
																			<td>{{ $item->SUB ?? '' }}</td>
																			<td>{{ $item->SUB2 ?? '' }}</td>
																			<td>{{ $item->KD_BRG ?? '' }}</td>
																			<td>{{ $item->NA_BRG ?? '' }}</td>
																			<td>{{ $item->BARCODE ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->qty ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->harga ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->diskon ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->disc ?? 0, 0, ',', '.') }}</td>
																			<td class="text-center">{{ $item->ppn ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->nppn ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->dpp ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->tkp ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->total ?? 0, 0, ',', '.') }}</td>
																			<td class="text-center">{{ $item->flag ?? '' }}</td>
																			<td>{{ $item->type ?? '' }}</td>
																			<td>{{ $item->per ?? '' }}</td>
																			<td>{{ $item->kodes ?? '' }}</td>
																			<td>{{ $item->TGL ?? '' }}</td>
																			<td>{{ $item->CBG ?? '' }}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg'))
																Tidak ada data detail sales untuk cabang <strong>{{ session()->get('filter_cbg') }}</strong>.
															@else
																Silakan pilih cabang untuk menampilkan data detail sales manager.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>

										<!-- Summary Report Tab -->
										<div class="tab-pane fade {{ isset($reportType) && $reportType == 2 ? 'show active' : '' }}" id="summary-report" role="tabpanel"
											aria-labelledby="summary-report-tab">
											<div class="pt-3">
												<!-- Summary Report Filter Controls -->
												<div class="row align-items-end mb-4">
													<div class="col-4">
														<label for="cbg2">Cabang</label>
														<select name="cbg2" id="cbg2" class="form-control" required>
															<option value="">Pilih Cabang</option>
															@foreach ($cbg as $cabang)
																<option value="{{ $cabang->KODE }}" {{ session()->get('filter_cbg') == $cabang->KODE ? 'selected' : '' }}>
																	{{ $cabang->KODE }}
																</option>
															@endforeach
														</select>
													</div>
													<div class="col-4">
														<button class="btn btn-primary" type="submit" id="btnFilterSummary">
															<i class="fas fa-search mr-1"></i>Filter
														</button>
														<button class="btn btn-danger ml-2" type="button" onclick="resetSummaryFilter()">
															<i class="fas fa-redo mr-1"></i>Reset
														</button>
													</div>
												</div>

												<!-- Summary Report Actions -->
												<div class="row mb-3">
													<div class="col-12">
														<button class="btn btn-success mr-2" type="button" onclick="exportSummaryData('excel')">
															<i class="fas fa-file-excel mr-1"></i>Export Excel
														</button>
														<button class="btn btn-warning" type="button" onclick="cetakSummaryLaporan()">
															<i class="fas fa-print mr-1"></i>Cetak Laporan
														</button>
													</div>
												</div>

												<!-- Summary Report Content -->
												<div class="report-content">
													@if (count($hasilSummary ?? []) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="summary-report-table">
																<table class="table-hover table-striped table-bordered compact table" id="detail-report-table">
																<thead>
																	<tr>
																		<th>Minggu</th>
																		<th>Thn</th>
																		<th>SUB</th>
																		<th>SUB2</th>
																		<th>Kode Barang</th>
																		<th>Nama Barang</th>
																		<th>Barcode</th>
																		<th>Qty</th>
																		<th>Harga</th>
																		<th>Diskon</th>
																		<th>Disc</th>
																		<th>PPN</th>
																		<th>Nilai PPN</th>
																		<th>DPP</th>
																		<th>TKP</th>
																		<th>Total</th>
																		<th>Flag</th>
																		<th>Type</th>
																		<th>Kodes</th>
																		<th>CBG</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilSummary as $item)
																		<tr>
																			<td>{{ $item->MINGGU ?? '' }}</td>
																			<td>{{ $item->YER ?? '' }}</td>
																			<td>{{ $item->SUB ?? '' }}</td>
																			<td>{{ $item->SUB2 ?? '' }}</td>
																			<td>{{ $item->KD_BRG ?? '' }}</td>
																			<td>{{ $item->NA_BRG ?? '' }}</td>
																			<td>{{ $item->BARCODE ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->qty ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->harga ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->diskon ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->disc ?? 0, 0, ',', '.') }}</td>
																			<td class="text-center">{{ $item->ppn ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->nppn ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->dpp ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->tkp ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->total ?? 0, 0, ',', '.') }}</td>
																			<td class="text-center">{{ $item->flag ?? '' }}</td>
																			<td>{{ $item->type ?? '' }}</td>
																			<td>{{ $item->kodes ?? '' }}</td>
																			<td>{{ $item->CBG ?? '' }}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg'))
																Tidak ada data summary sales untuk cabang <strong>{{ session()->get('filter_cbg') }}</strong>.
															@else
																Silakan pilih cabang untuk menampilkan data summary sales manager.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>
									</div>
								</form>

								<!-- Summary Information -->
								@if (session()->get('filter_cbg'))
									<div class="row mt-4">
										<div class="col-12">
											<div class="card card-outline card-info">
												<div class="card-header">
													<h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Ringkasan Laporan</h3>
												</div>
												<div class="card-body">
													<div class="row">
														<div class="col-md-6">
															<div class="info-box bg-success">
																<span class="info-box-icon"><i class="fas fa-list-alt"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Data Detail</span>
																	<span class="info-box-number">{{ count($hasilDetail ?? []) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="info-box bg-primary">
																<span class="info-box-icon"><i class="fas fa-chart-bar"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Data Summary</span>
																	<span class="info-box-number">{{ count($hasilSummary ?? []) }}</span>
																</div>
															</div>
														</div>
													</div>

													@if (count($hasilDetail ?? []) > 0 && (!isset($reportType) || $reportType == 1))
														<div class="row mt-2">
															<div class="col-md-4">
																<small class="text-muted">
																	<i class="fas fa-calculator mr-1"></i>Total Qty:
																	<strong>{{ number_format(collect($hasilDetail)->sum('qty'), 0, ',', '.') }}</strong>
																</small>
															</div>
															<div class="col-md-4">
																<small class="text-muted">
																	<i class="fas fa-money-bill mr-1"></i>Total DPP:
																	<strong>Rp {{ number_format(collect($hasilDetail)->sum('dpp'), 0, ',', '.') }}</strong>
																</small>
															</div>
															<div class="col-md-4">
																<small class="text-muted">
																	<i class="fas fa-receipt mr-1"></i>Total Amount:
																	<strong>Rp {{ number_format(collect($hasilDetail)->sum('total'), 0, ',', '.') }}</strong>
																</small>
															</div>
														</div>
													@endif
													<div class="row mt-2">
														<div class="col-12">
															<small class="text-muted">
																<i class="fas fa-store mr-1"></i>Cabang:
																<strong>{{ session()->get('filter_cbg') }}</strong> |
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
			// Initialize Bootstrap tabs
			$('#salesManagerTabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');
			});

			// Save active tab to localStorage
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				localStorage.setItem('salesManagerActiveTab', $(e.target).attr('href'));
			});

			// Restore active tab from localStorage or set based on reportType
			var activeTab = localStorage.getItem('salesManagerActiveTab');
			@if (isset($reportType))
				var reportType = {{ $reportType }};
				switch (reportType) {
					case 1:
						activeTab = '#detail-report';
						break;
					case 2:
						activeTab = '#summary-report';
						break;
				}
			@endif

			if (activeTab) {
				$('#salesManagerTabs a[href="' + activeTab + '"]').tab('show');
			}

			// Initialize DataTables for each tab
			initializeDataTables();

			// Auto-resize table on tab change
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				$.fn.dataTable.tables({
					visible: true,
					api: true
				}).columns.adjust();
			});

			// Form validation
			$('#reportForm').on('submit', function(e) {
				var reportType = $('#report_type').val();
				var cbg = $('#cbg').val();
				var cbg2 = $('#cbg2').val();

				if (reportType == '1') {
					// Detail Report validation
					if (!cbg) {
						e.preventDefault();
						alert('Cabang harus dipilih untuk detail report');
						return false;
					}
				} else {
					// Summary Report validation
					if (!cbg && !cbg2) {
						e.preventDefault();
						alert('Cabang harus dipilih untuk summary report');
						return false;
					}
				}

				// Show loading
				$('.btn-primary').html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
				$('.btn-primary').prop('disabled', true);
			});
		});

		// Set report type when tab is clicked
		function setReportType(type) {
			$('#report_type').val(type);
		}

		// Initialize DataTables for all tabs
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

			// Detail Report table options
			var detailOptions = {
				...commonOptions,
				columnDefs: [{
					className: 'dt-center',
					targets: [0, 1, 9, 14, 15, 19] // SUB, SUB2, PPN, Flag, Type, CBG
				}, {
					className: 'dt-right',
					targets: [5, 6, 7, 8, 10, 11, 12, 13] // Qty, Harga, Diskon, Disc, Nilai PPN, DPP, TKP, Total
				}]
			};

			// Summary Report table options
			var summaryOptions = {
				...commonOptions,
				columnDefs: [{
					className: 'dt-center',
					targets: []
				}, {
					className: 'dt-right',
					targets: []
				}]
			};

			// Initialize tables if they have data
			if ($('#detail-report-table').length && $('#detail-report-table tbody tr').length > 0) {
				var detailTable = $('#detail-report-table').DataTable(detailOptions);
				window.detailTable = detailTable;
			}

			if ($('#summary-report-table').length && $('#summary-report-table tbody tr').length > 0) {
				var summaryTable = $('#summary-report-table').DataTable(summaryOptions);
				window.summaryTable = summaryTable;
			}
		}

		// Reset detail filter function
		function resetDetailFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter detail report?')) {
				$('#cbg').val('');
				$('#report_type').val('1');

				// Submit form to clear session
				$('#reportForm').submit();
			}
		}

		// Reset summary filter function
		function resetSummaryFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter summary report?')) {
				$('#cbg2').val('');
				$('#report_type').val('2');

				// Submit form to clear session
				$('#reportForm').submit();
			}
		}

		// Export detail data function
		function exportDetailData(format) {
			var cbg = $('#cbg').val();

			if (!cbg) {
				alert('Silakan pilih cabang terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 1,
				cbg: cbg,
				format: format
			});

			var url = '{{ route('jasper-salesmanager-report') }}?' + params.toString();
			downloadReport(url);
		}

		// Export summary data function
		function exportSummaryData(format) {
			var cbg = $('#cbg2').val() || $('#cbg').val();

			if (!cbg) {
				alert('Silakan pilih cabang terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 2,
				cbg: cbg,
				format: format
			});

			var url = '{{ route('jasper-salesmanager-report') }}?' + params.toString();
			downloadReport(url);
		}

		// Print detail report function
		function cetakDetailLaporan() {
			var cbg = $('#cbg').val();

			if (!cbg) {
				alert('Silakan pilih cabang terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 1,
				cbg: cbg
			});

			var url = '{{ route('jasper-salesmanager-report') }}?' + params.toString();
			printReport(url);
		}

		// Print summary report function
		function cetakSummaryLaporan() {
			var cbg = $('#cbg2').val() || $('#cbg').val();

			if (!cbg) {
				alert('Silakan pilih cabang terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 2,
				cbg: cbg
			});

			var url = '{{ route('jasper-salesmanager-report') }}?' + params.toString();
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

		// Tooltip initialization
		$(function() {
			$('[data-toggle="tooltip"]').tooltip();
		});

		// Handle keyboard shortcuts
		$(document).keydown(function(e) {
			// Ctrl+F for search
			if (e.ctrlKey && e.keyCode === 70) {
				e.preventDefault();
				var activeTable = $('.tab-pane.active .table').DataTable();
				if (activeTable) {
					activeTable.search('').draw();
					$('.dataTables_filter input').focus();
				}
			}
		});

		// Handle responsive table adjustments
		$(window).on('resize', function() {
			$.fn.dataTable.tables({
				visible: true,
				api: true
			}).columns.adjust().responsive.recalc();
		});

		// Error handling for AJAX requests
		$(document).ajaxError(function(event, jqXHR, settings, thrownError) {
			console.error('AJAX Error:', thrownError);
			alert('Terjadi kesalahan saat memuat data. Silakan coba lagi.');
		});

		// Sync form fields between tabs
		$('#cbg').on('change', function() {
			$('#cbg2').val($(this).val());
		});

		$('#cbg2').on('change', function() {
			$('#cbg').val($(this).val());
		});

		// Show/hide loading overlay
		function showLoading() {
			$('body').append('<div class="loading-overlay"><div class="spinner"></div></div>');
		}

		function hideLoading() {
			$('.loading-overlay').remove();
		}

		// Initialize tooltips for better UX
		$('[data-toggle="tooltip"]').tooltip({
			placement: 'top',
			trigger: 'hover'
		});

		// Add search functionality to select elements
		if (typeof $.fn.select2 !== 'undefined') {
			$('#cbg, #cbg2').select2({
				theme: 'bootstrap4',
				width: '100%'
			});
		}

		// Auto-refresh data every 5 minutes if enabled
		var autoRefresh = false;
		if (autoRefresh) {
			setInterval(function() {
				if ($('#cbg').val() || $('#cbg2').val()) {
					$('#reportForm').submit();
				}
			}, 300000); // 5 minutes
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

		.nav-tabs .nav-link {
			border: 1px solid transparent;
			border-top-left-radius: 0.25rem;
			border-top-right-radius: 0.25rem;
		}

		.nav-tabs .nav-link:hover {
			border-color: #e9ecef #e9ecef #dee2e6;
		}

		.nav-tabs .nav-link.active {
			background-color: #fff;
			border-color: #dee2e6 #dee2e6 #fff;
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

		@media (max-width: 768px) {
			.col-4 {
				margin-bottom: 1rem;
			}

			.btn-group .btn {
				margin-bottom: 0.5rem;
			}
		}

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

		.tab-content {
			border: 1px solid #dee2e6;
			border-top: none;
			border-radius: 0 0 0.25rem 0.25rem;
			padding: 0;
		}

		.alert {
			margin: 1rem;
		}

		.btn-group .dropdown-menu {
			min-width: 200px;
		}

		/* Custom styling for numeric columns */
		.text-right {
			text-align: right !important;
		}

		.text-center {
			text-align: center !important;
		}

		/* Highlight important columns */
		.table thead th:nth-child(14) {
			background-color: #e3f2fd;
			font-weight: bold;
		}

		.table tbody td:nth-child(14) {
			background-color: #f8f9fa;
			font-weight: 600;
		}

		/* Status indicators */
		.status-success {
			color: #28a745;
			font-weight: bold;
		}

		.status-warning {
			color: #ffc107;
			font-weight: bold;
		}

		.status-danger {
			color: #dc3545;
			font-weight: bold;
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
			.nav-tabs,
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

		/* Animation for tab transitions */
		.tab-pane {
			transition: opacity 0.3s ease-in-out;
		}

		.tab-pane.fade:not(.show) {
			opacity: 0;
		}

		.tab-pane.fade.show {
			opacity: 1;
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

		/* Row hover effects */
		/* Column width optimization */
		#detail-report-table th:nth-child(3),
		#detail-report-table td:nth-child(3) {
			min-width: 100px;
		}

		#detail-report-table th:nth-child(4),
		#detail-report-table td:nth-child(4) {
			min-width: 200px;
		}

		#detail-report-table th:nth-child(5),
		#detail-report-table td:nth-child(5) {
			min-width: 120px;
		}

		/* Status column styling */
		.flag-active {
			color: #28a745;
			font-weight: bold;
		}

		.flag-inactive {
			color: #dc3545;
			font-weight: bold;
		}

		/* Enhanced button styling */
		.btn-group-sm>.btn,
		.btn-sm {
			padding: 0.25rem 0.5rem;
			font-size: 0.875rem;
			line-height: 1.5;
			border-radius: 0.2rem;
		}

		/* Card styling enhancements */
		.card {
			box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
		}

		.card-outline.card-info {
			border-top: 3px solid #17a2b8;
		}

		/* Loading states */
		.btn.loading {
			position: relative;
			color: transparent;
		}

		.btn.loading::after {
			content: "";
			position: absolute;
			width: 16px;
			height: 16px;
			top: 50%;
			left: 50%;
			margin-left: -8px;
			margin-top: -8px;
			border: 2px solid #ffffff;
			border-radius: 50%;
			border-top-color: transparent;
			animation: spin 1s linear infinite;
		}

		/* Summary stats styling */
		.summary-stat {
			background: linear-gradient(45deg, #f8f9fa, #e9ecef);
			border-left: 4px solid #007bff;
			padding: 1rem;
			margin-bottom: 1rem;
		}

		.summary-stat h4 {
			margin-bottom: 0.5rem;
		}

		.summary-stat .stat-value {
			font-size: 1.5rem;
			font-weight: bold;
			color: #007bff;
		}

		/* Custom scrollbar */
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

		/* Mobile responsive improvements */
		@media (max-width: 768px) {
			.nav-tabs .nav-link {
				font-size: 0.875rem;
				padding: 0.5rem 0.75rem;
			}

			.info-box-content {
				padding: 0.5rem;
			}

			.info-box-number {
				font-size: 1.5rem;
			}

			.btn-toolbar .btn-group {
				margin-bottom: 0.5rem;
			}
		}
	</style>
@endsection
