@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Barcode</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Barcode</li>
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
								<form method="GET" action="{{ route('get-barcode-report') }}" id="reportForm">
									@csrf

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="barcodeReportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ !isset($reportType) || $reportType == 1 ? 'active' : '' }}" id="duplicate-tab" data-toggle="tab" href="#duplicate"
												role="tab" aria-controls="duplicate" aria-selected="{{ !isset($reportType) || $reportType == 1 ? 'true' : 'false' }}"
												onclick="setReportType(1)">
												<i class="fas fa-clone text-warning mr-1"></i>Barcode Duplikat
												@if (count($hasilDuplicate ?? []) > 0)
													<span class="badge badge-warning ml-1">{{ count($hasilDuplicate) }}</span>
												@endif
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ isset($reportType) && $reportType == 2 ? 'active' : '' }}" id="different-tab" data-toggle="tab" href="#different"
												role="tab" aria-controls="different" aria-selected="{{ isset($reportType) && $reportType == 2 ? 'true' : 'false' }}"
												onclick="setReportType(2)">
												<i class="fas fa-exchange-alt text-primary mr-1"></i>Perbedaan Barcode
												@if (count($hasilDifferent ?? []) > 0)
													<span class="badge badge-primary ml-1">{{ count($hasilDifferent) }}</span>
												@endif
											</a>
										</li>
									</ul>

									<!-- Hidden field for report type -->
									<input type="hidden" name="report_type" id="report_type" value="{{ $reportType ?? 1 }}">

									<!-- Tab panes -->
									<div class="tab-content" id="barcodeReportTabContent">
										<!-- Duplicate Barcode Tab -->
										<div class="tab-pane fade {{ !isset($reportType) || $reportType == 1 ? 'show active' : '' }}" id="duplicate" role="tabpanel"
											aria-labelledby="duplicate-tab">
											<div class="pt-3">
												<!-- Duplicate Barcode Filter Controls -->
												<div class="row align-items-end mb-4">
													<div class="col-4">
														<label for="cbg">Cabang</label>
														<select name="cbg" id="cbg" class="form-control" required>
															<option value="">Pilih Cabang</option>
															@foreach ($cbg as $cabang)
																<option value="{{ $cabang->CBG }}" {{ session()->get('filter_cbg') == $cabang->CBG ? 'selected' : '' }}>
																	{{ $cabang->CBG }}
																</option>
															@endforeach
														</select>
													</div>
													<div class="col-2">
														<button class="btn btn-primary" type="submit" id="btnFilterDuplicate">
															<i class="fas fa-search mr-1"></i>Filter
														</button>
													</div>
												</div>

												<!-- Duplicate Barcode Actions -->
												<div class="row mb-3">
													<div class="col-12">
														<button class="btn btn-danger mr-2" type="button" onclick="resetDuplicateFilter()">
															<i class="fas fa-redo mr-1"></i>Reset
														</button>
														<button class="btn btn-success mr-2" type="button" onclick="exportDuplicateData('excel')">
															<i class="fas fa-file-excel mr-1"></i>Export Excel
														</button>
														<button class="btn btn-warning" type="button" onclick="cetakDuplicateLaporan()">
															<i class="fas fa-print mr-1"></i>Cetak Laporan
														</button>
													</div>
												</div>

												<!-- Duplicate Barcode Report Content -->
												<div class="report-content">
													@if (count($hasilDuplicate ?? []) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="duplicate-table">
																<thead>
																	<tr>
																		<th>Sub Item</th>
																		<th>Nama Barang</th>
																		<th>Ukuran</th>
																		<th>Kemasan</th>
																		<th>Barcode</th>
																		<th>JNS</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilDuplicate as $item)
																		<tr>
																			<td>{{ $item->kd_brg ?? '' }}</td>
																			<td>{{ $item->na_brg ?? '' }}</td>
																			<td>{{ $item->KET_UK ?? '' }}</td>
																			<td>{{ $item->KET_KEM ?? '' }}</td>
																			<td>{{ $item->BARCODE ?? '' }}</td>
																			<td>{{ $item->JNS ?? '' }}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg'))
																Tidak ada data barcode duplikat untuk cabang <strong>{{ session()->get('filter_cbg') }}</strong>.
															@else
																Silakan pilih cabang untuk menampilkan data barcode duplikat.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>

										<!-- Different Barcode Tab -->
										<div class="tab-pane fade {{ isset($reportType) && $reportType == 2 ? 'show active' : '' }}" id="different" role="tabpanel"
											aria-labelledby="different-tab">
											<div class="pt-3">
												<!-- Different Barcode Filter Controls -->
												<div class="row align-items-end mb-4">
													<div class="col-4">
														<label for="cbg2">Cabang</label>
														<select name="cbg" id="cbg2" class="form-control" required>
															<option value="">Pilih Cabang</option>
															@foreach ($cbg as $cabang)
																<option value="{{ $cabang->CBG }}" {{ session()->get('filter_cbg') == $cabang->CBG ? 'selected' : '' }}>
																	{{ $cabang->CBG }}
																</option>
															@endforeach
														</select>
													</div>
													<div class="col-2">
														<button class="btn btn-primary" type="submit" id="btnFilterDifferent">
															<i class="fas fa-search mr-1"></i>Filter
														</button>
													</div>
												</div>

												<!-- Different Barcode Actions -->
												<div class="row mb-3">
													<div class="col-12">
														<button class="btn btn-danger mr-2" type="button" onclick="resetDifferentFilter()">
															<i class="fas fa-redo mr-1"></i>Reset
														</button>
														<button class="btn btn-success mr-2" type="button" onclick="exportDifferentData('excel')">
															<i class="fas fa-file-excel mr-1"></i>Export Excel
														</button>
														<button class="btn btn-warning" type="button" onclick="cetakDifferentLaporan()">
															<i class="fas fa-print mr-1"></i>Cetak Laporan
														</button>
													</div>
												</div>

												<!-- Different Barcode Report Content -->
												<div class="report-content">
													@if (count($hasilDifferent ?? []) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="different-table">
																<thead>
																	<tr>
																		<th>Sub Item</th>
																		<th>Nama</th>
																		<th>Ukuran</th>
																		<th>Kemasan</th>
																		<th>Brcd Master</th>
																		<th>Brcd Kasir</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilDifferent as $item)
																		<tr>
																			<td>{{ $item->KD_BRG ?? '' }}</td>
																			<td>{{ $item->NA_BRG ?? '' }}</td>
																			<td>{{ $item->KET_UK ?? '' }}</td>
																			<td>{{ $item->KET_KEM ?? '' }}</td>
																			<td>{{ $item->barcodemaster ?? '' }}</td>
																			<td>{{ $item->barcodekasir ?? '' }}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg'))
																Tidak ada data perbedaan barcode untuk cabang <strong>{{ session()->get('filter_cbg') }}</strong>.
															@else
																Silakan pilih cabang untuk menampilkan data perbedaan barcode.
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
															<div class="info-box bg-warning">
																<span class="info-box-icon"><i class="fas fa-clone"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Barcode Duplikat</span>
																	<span class="info-box-number">{{ count($hasilDuplicate ?? []) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="info-box bg-primary">
																<span class="info-box-icon"><i class="fas fa-exchange-alt"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Perbedaan Barcode</span>
																	<span class="info-box-number">{{ count($hasilDifferent ?? []) }}</span>
																</div>
															</div>
														</div>
													</div>
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
			$('#barcodeReportTabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');
			});

			// Save active tab to localStorage
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				localStorage.setItem('barcodeReportActiveTab', $(e.target).attr('href'));
			});

			// Restore active tab from localStorage or set based on reportType
			var activeTab = localStorage.getItem('barcodeReportActiveTab');
			@if (isset($reportType))
				var reportType = {{ $reportType }};
				switch (reportType) {
					case 1:
						activeTab = '#duplicate';
						break;
					case 2:
						activeTab = '#different';
						break;
				}
			@endif

			if (activeTab) {
				$('#barcodeReportTabs a[href="' + activeTab + '"]').tab('show');
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
					// Duplicate validation
					if (!cbg) {
						e.preventDefault();
						alert('Cabang harus dipilih untuk laporan barcode duplikat');
						return false;
					}
				} else {
					// Different validation
					if (!cbg && !cbg2) {
						e.preventDefault();
						alert('Cabang harus dipilih untuk laporan perbedaan barcode');
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

			// Duplicate table options
			var duplicateOptions = {
				...commonOptions,
				columnDefs: [{
					className: 'dt-center',
					targets: [0, 4, 5] // Sub Item, Barcode, JNS
				}]
			};

			// Different table options
			var differentOptions = {
				...commonOptions,
				columnDefs: [{
					className: 'dt-center',
					targets: [0, 4, 5] // Sub Item, Brcd Master, Brcd Kasir
				}]
			};

			// Initialize tables if they have data
			if ($('#duplicate-table').length && $('#duplicate-table tbody tr').length > 0) {
				var duplicateTable = $('#duplicate-table').DataTable(duplicateOptions);
				window.duplicateTable = duplicateTable;
			}

			if ($('#different-table').length && $('#different-table tbody tr').length > 0) {
				var differentTable = $('#different-table').DataTable(differentOptions);
				window.differentTable = differentTable;
			}
		}

		// Reset duplicate filter function
		function resetDuplicateFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter barcode duplikat?')) {
				$('#cbg').val('');
				$('#report_type').val('1');

				// Submit form to clear session
				$('#reportForm').submit();
			}
		}

		// Reset different filter function
		function resetDifferentFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter perbedaan barcode?')) {
				$('#cbg2').val('');
				$('#report_type').val('2');

				// Submit form to clear session
				$('#reportForm').submit();
			}
		}

		// Export duplicate data function
		function exportDuplicateData(format) {
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

			var url = '{{ route('jasper-barcode-report') }}?' + params.toString();
			downloadReport(url);
		}

		// Export different data function
		function exportDifferentData(format) {
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

			var url = '{{ route('jasper-barcode-report') }}?' + params.toString();
			downloadReport(url);
		}

		// Print duplicate report function
		function cetakDuplicateLaporan() {
			var cbg = $('#cbg').val();

			if (!cbg) {
				alert('Silakan pilih cabang terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 1,
				cbg: cbg
			});

			var url = '{{ route('jasper-barcode-report') }}?' + params.toString();
			printReport(url);
		}

		// Print different report function
		function cetakDifferentLaporan() {
			var cbg = $('#cbg2').val() || $('#cbg').val();

			if (!cbg) {
				alert('Silakan pilih cabang terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 2,
				cbg: cbg
			});

			var url = '{{ route('jasper-barcode-report') }}?' + params.toString();
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

			.col-4,
			.col-2 {
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
	</style>
@endsection
