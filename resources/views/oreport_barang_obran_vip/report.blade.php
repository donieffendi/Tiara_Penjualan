@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Barang Obral VIP</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Barang Obral VIP</li>
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
								<form method="GET" action="{{ route('get-barangobralvip-report') }}" id="reportForm">
									@csrf

									<!-- Report Type Selection -->
									<div class="row mb-3">
										<div class="col-12">
											<label>Tipe Laporan</label>
											<div class="btn-group btn-group-toggle" data-toggle="buttons">
												<label class="btn btn-outline-primary {{ ($reportType ?? 1) == 1 ? 'active' : '' }}">
													<input type="radio" name="report_type" value="1" {{ ($reportType ?? 1) == 1 ? 'checked' : '' }}> Kode 3&8
												</label>
												<label class="btn btn-outline-primary {{ ($reportType ?? 1) == 2 ? 'active' : '' }}">
													<input type="radio" name="report_type" value="2" {{ ($reportType ?? 1) == 2 ? 'checked' : '' }}> Food Center
												</label>
												<label class="btn btn-outline-primary {{ ($reportType ?? 1) == 3 ? 'active' : '' }}">
													<input type="radio" name="report_type" value="3" {{ ($reportType ?? 1) == 3 ? 'checked' : '' }}> VIP
												</label>
												<label class="btn btn-outline-primary {{ ($reportType ?? 1) == 4 ? 'active' : '' }}">
													<input type="radio" name="report_type" value="4" {{ ($reportType ?? 1) == 4 ? 'checked' : '' }}> Borong
												</label>
											</div>
										</div>
									</div>

									<!-- Filter Controls -->
									<div class="row align-items-end mb-4">
										<div class="col-3">
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
										<div class="col-3" id="tanggal-field">
											<label for="tanggal">Tanggal</label>
											<input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ session()->get('filter_tanggal') }}">
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
										<div class="col-3" id="jam-field" style="display: none;">
											<label for="jam">Jam</label>
											<select name="jam" id="jam" class="form-control">
												<option value="">Pilih Jam</option>
												@for ($i = 1; $i <= 24; $i++)
													<option value="{{ $i }}" {{ session()->get('filter_jam') == $i ? 'selected' : '' }}>{{ sprintf('%02d:00', $i) }}</option>
												@endfor
											</select>
										</div>
									</div>

									<!-- Additional Options for Borong -->
									<div class="row mb-3" id="all-option" style="display: none;">
										<div class="col-12">
											<div class="form-check">
												<input class="form-check-input" type="checkbox" name="all" id="all" value="1"
													{{ session()->get('filter_all') ? 'checked' : '' }}>
												<label class="form-check-label" for="all">
													Tampilkan Semua Waktu
												</label>
											</div>
										</div>
									</div>

									<!-- Action Buttons -->
									<div class="row mb-3">
										<div class="col-12">
											<button class="btn btn-primary mr-2" type="submit" id="btnFilter">
												<i class="fas fa-search mr-1"></i>Filter
											</button>
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
									@php
										$currentData = [];
										$reportTitle = '';

										switch ($reportType ?? 1) {
										    case 1:
										        $currentData = $hasilKode38 ?? [];
										        $reportTitle = 'Report Obral Kode 3&8';
										        break;
										    case 2:
										        $currentData = $hasilFoodCenter ?? [];
										        $reportTitle = 'Report Obral Food Center';
										        break;
										    case 3:
										        $currentData = isset($hasilVip['detail']) ? $hasilVip['detail'] : [];
										        $reportTitle = 'Report Sales VIP';
										        break;
										    case 4:
										        $currentData = $hasilBorong ?? [];
										        $reportTitle = 'Report Borong Kode 3';
										        break;
										}
									@endphp

									@if (count($currentData) > 0)
										<div class="table-responsive">
											@if (($reportType ?? 1) == 1 || ($reportType ?? 1) == 2)
												<!-- Table for Kode 3&8 and Food Center -->
												<table class="table-hover table-striped table-bordered compact table" id="obral-table">
													<thead>
														<tr>
															<th>Sub</th>
															<th>Kode Barang</th>
															<th>Nama Barang</th>
															<th>Qty</th>
															<th>Harga</th>
															<th>Total</th>
														</tr>
													</thead>
													<tbody>
														@foreach ($currentData as $item)
															<tr>
																<td>{{ $item->SUB ?? '' }}</td>
																<td>{{ $item->KD_BRG ?? '' }}</td>
																<td>{{ $item->NA_BRG ?? '' }}</td>
																<td class="text-right">{{ number_format($item->qty ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->harga ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->total ?? 0, 0, ',', '.') }}</td>
															</tr>
														@endforeach
													</tbody>
												</table>
											@elseif (($reportType ?? 1) == 3)
												<!-- Table for VIP -->
												<table class="table-hover table-striped table-bordered compact table" id="vip-table">
													<thead>
														<tr>
															<th>Sub</th>
															<th>Kode Barang</th>
															<th>Nama Barang</th>
															<th>Qty</th>
															<th>Harga</th>
															<th>Harga VIP</th>
															<th>Total</th>
															<th>Total VIP</th>
														</tr>
													</thead>
													<tbody>
														@foreach ($currentData as $item)
															<tr>
																<td>{{ $item->sub ?? '' }}</td>
																<td>{{ $item->KD_BRG ?? '' }}</td>
																<td>{{ $item->NA_BRG ?? '' }}</td>
																<td class="text-right">{{ number_format($item->qty ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->harga ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->hargavip ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->total ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->TOTAL_VIP ?? 0, 0, ',', '.') }}</td>
															</tr>
														@endforeach
													</tbody>
												</table>
											@elseif (($reportType ?? 1) == 4)
												<!-- Table for Borong -->
												<table class="table-hover table-striped table-bordered compact table" id="borong-table">
													<thead>
														<tr>
															<th>Sub</th>
															<th>Kode Barang</th>
															<th>Nama Barang</th>
															<th>Ket Kem</th>
															<th>LPH</th>
															<th>Qty</th>
															<th>Harga</th>
															<th>Total</th>
															<th>Waktu</th>
														</tr>
													</thead>
													<tbody>
														@foreach ($currentData as $item)
															<tr>
																<td>{{ $item->sub ?? '' }}</td>
																<td>{{ $item->KD_BRG ?? '' }}</td>
																<td>{{ $item->NA_BRG ?? '' }}</td>
																<td>{{ $item->ket_kem ?? '' }}</td>
																<td>{{ $item->lph ?? '' }}</td>
																<td class="text-right">{{ number_format($item->qty ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->harga ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->total ?? 0, 0, ',', '.') }}</td>
																<td class="text-center">{{ $item->TIME ?? '' }}</td>
															</tr>
														@endforeach
													</tbody>
												</table>
											@endif
										</div>
									@else
										<div class="alert alert-info">
											<i class="fas fa-info-circle mr-2"></i>
											@if (session()->get('filter_cbg'))
												Tidak ada data {{ $reportTitle }} untuk cabang <strong>{{ session()->get('filter_cbg') }}</strong>
												@if (session()->get('filter_periode'))
													periode <strong>{{ session()->get('filter_periode') }}</strong>
												@endif
												@if (session()->get('filter_tanggal'))
													tanggal <strong>{{ session()->get('filter_tanggal') }}</strong>
												@endif
												.
											@else
												Silakan pilih cabang dan filter lainnya untuk menampilkan data {{ $reportTitle }}.
											@endif
										</div>
									@endif
								</div>

								<!-- Summary Information -->
								@if (session()->get('filter_cbg') && count($currentData) > 0)
									<div class="row mt-4">
										<div class="col-12">
											<div class="card card-outline card-info">
												<div class="card-header">
													<h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Ringkasan {{ $reportTitle }}</h3>
												</div>
												<div class="card-body">
													<div class="row">
														<div class="col-md-3">
															<div class="info-box bg-success">
																<span class="info-box-icon"><i class="fas fa-boxes"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Item</span>
																	<span class="info-box-number">{{ count($currentData) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-primary">
																<span class="info-box-icon"><i class="fas fa-cubes"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Qty</span>
																	<span class="info-box-number">
																		{{ number_format(collect($currentData)->sum('qty'), 0, ',', '.') }}
																	</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-warning">
																<span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Amount</span>
																	<span class="info-box-number">
																		Rp {{ number_format(collect($currentData)->sum('total'), 0, ',', '.') }}
																	</span>
																</div>
															</div>
														</div>
														@if (($reportType ?? 1) == 3 && isset($hasilVip['detail']))
															<div class="col-md-3">
																<div class="info-box bg-info">
																	<span class="info-box-icon"><i class="fas fa-star"></i></span>
																	<div class="info-box-content">
																		<span class="info-box-text">Total VIP</span>
																		<span class="info-box-number">
																			Rp {{ number_format(collect($hasilVip['detail'])->sum('TOTAL_VIP'), 0, ',', '.') }}
																		</span>
																	</div>
																</div>
															</div>
														@else
															<div class="col-md-3">
																<div class="info-box bg-info">
																	<span class="info-box-icon"><i class="fas fa-calculator"></i></span>
																	<div class="info-box-content">
																		<span class="info-box-text">Avg Price</span>
																		<span class="info-box-number">
																			Rp {{ number_format(collect($currentData)->avg('harga'), 0, ',', '.') }}
																		</span>
																	</div>
																</div>
															</div>
														@endif
													</div>
													<div class="row mt-2">
														<div class="col-12">
															<small class="text-muted">
																<i class="fas fa-store mr-1"></i>Cabang:
																<strong>{{ session()->get('filter_cbg') }}</strong> |
																@if (session()->get('filter_periode'))
																	<i class="fas fa-calendar mr-1"></i>Periode:
																	<strong>{{ session()->get('filter_periode') }}</strong> |
																@endif
																@if (session()->get('filter_tanggal'))
																	<i class="fas fa-calendar-day mr-1"></i>Tanggal:
																	<strong>{{ session()->get('filter_tanggal') }}</strong> |
																@endif
																<i class="fas fa-clock mr-1"></i>Generated: {{ date('d/m/Y H:i:s') }}
															</small>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								@endif

								<!-- VIP Summary by SUB -->
								@if (($reportType ?? 1) == 3 && isset($hasilVip['summary']) && count($hasilVip['summary']) > 0)
									<div class="row mt-4">
										<div class="col-12">
											<div class="card card-outline card-secondary">
												<div class="card-header">
													<h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i>Summary VIP per SUB</h3>
												</div>
												<div class="card-body">
													<div class="table-responsive">
														<table class="table-sm table-striped table">
															<thead>
																<tr>
																	<th>SUB</th>
																	<th class="text-right">Total Qty</th>
																</tr>
															</thead>
															<tbody>
																@foreach ($hasilVip['summary'] as $summary)
																	<tr>
																		<td>{{ $summary['sub'] }}</td>
																		<td class="text-right">{{ number_format($summary['qty'], 0, ',', '.') }}</td>
																	</tr>
																@endforeach
															</tbody>
														</table>
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

			// Handle report type change
			$('input[name="report_type"]').change(function() {
				toggleFields();
			});

			// Initialize field visibility
			toggleFields();

			// Form validation
			$('#reportForm').on('submit', function(e) {
				var cbg = $('#cbg').val();
				var reportType = $('input[name="report_type"]:checked').val();

				if (!cbg) {
					e.preventDefault();
					alert('Cabang harus dipilih');
					return false;
				}

				// Validation based on report type
				if (reportType == 1 || reportType == 2) { // Kode 3&8 or Food Center
					if (!$('#tanggal').val() || !$('#periode').val()) {
						e.preventDefault();
						alert('Tanggal dan Periode harus dipilih untuk laporan ini');
						return false;
					}
				} else if (reportType == 3) { // VIP
					if (!$('#periode').val()) {
						e.preventDefault();
						alert('Periode harus dipilih untuk laporan VIP');
						return false;
					}
				} else if (reportType == 4) { // Borong
					if (!$('#tanggal').val()) {
						e.preventDefault();
						alert('Tanggal harus dipilih untuk laporan Borong');
						return false;
					}
				}

				// Show loading
				$('#btnFilter').html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
				$('#btnFilter').prop('disabled', true);
			});
		});

		// Toggle fields based on report type
		function toggleFields() {
			var reportType = $('input[name="report_type"]:checked').val();

			// Reset all fields
			$('#tanggal-field').show();
			$('#periode').closest('.col-3').show();
			$('#jam-field').hide();
			$('#all-option').hide();

			// Clear required attributes
			$('#tanggal, #periode, #jam').removeAttr('required');

			switch (reportType) {
				case '1': // Kode 3&8
				case '2': // Food Center
					$('#tanggal, #periode').attr('required', true);
					break;
				case '3': // VIP
					$('#tanggal-field').hide();
					$('#periode').attr('required', true);
					break;
				case '4': // Borong
					$('#periode').closest('.col-3').hide();
					$('#jam-field').show();
					$('#all-option').show();
					$('#tanggal').attr('required', true);
					break;
			}
		}

		// Initialize DataTable
		function initializeDataTable() {
			var tableSelector = '';
			var reportType = {{ $reportType ?? 1 }};

			switch (reportType) {
				case 1:
				case 2:
					tableSelector = '#obral-table';
					break;
				case 3:
					tableSelector = '#vip-table';
					break;
				case 4:
					tableSelector = '#borong-table';
					break;
			}

			if ($(tableSelector).length && $(tableSelector + ' tbody tr').length > 0) {
				var table = $(tableSelector).DataTable({
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
						targets: [0, 1] // Sub, Kode Barang
					}, {
						className: 'dt-right',
						targets: reportType == 4 ? [5, 6, 7] : reportType == 3 ? [3, 4, 5, 6, 7] : [3, 4, 5] // Numeric columns
					}]
				});
				window.obralTable = table;
			}
		}

		// Reset filter function
		function resetFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter?')) {
				$('#cbg').val('');
				$('#tanggal').val('');
				$('#periode').val('');
				$('#jam').val('');
				$('#all').prop('checked', false);
				$('input[name="report_type"][value="1"]').prop('checked', true);
				$('.btn-group-toggle label').removeClass('active');
				$('.btn-group-toggle label:first').addClass('active');
				toggleFields();
				$('#reportForm').submit();
			}
		}

		// Export data function
		function exportData(format) {
			var cbg = $('#cbg').val();
			var reportType = $('input[name="report_type"]:checked').val();

			if (!cbg) {
				alert('Silakan pilih cabang terlebih dahulu');
				return;
			}

			// Validate required fields based on report type
			if ((reportType == 1 || reportType == 2) && (!$('#tanggal').val() || !$('#periode').val())) {
				alert('Silakan lengkapi tanggal dan periode terlebih dahulu');
				return;
			}
			if (reportType == 3 && !$('#periode').val()) {
				alert('Silakan pilih periode terlebih dahulu');
				return;
			}
			if (reportType == 4 && !$('#tanggal').val()) {
				alert('Silakan pilih tanggal terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				cbg: cbg,
				tanggal: $('#tanggal').val(),
				periode: $('#periode').val(),
				jam: $('#jam').val(),
				report_type: reportType,
				all: $('#all').is(':checked') ? 1 : 0,
				format: format
			});

			var url = '{{ route('jasper-barangobralvip-report') }}?' + params.toString();
			downloadReport(url);
		}

		// Print report function
		function cetakLaporan() {
			var cbg = $('#cbg').val();
			var reportType = $('input[name="report_type"]:checked').val();

			if (!cbg) {
				alert('Silakan pilih cabang terlebih dahulu');
				return;
			}

			// Validate required fields based on report type
			if ((reportType == 1 || reportType == 2) && (!$('#tanggal').val() || !$('#periode').val())) {
				alert('Silakan lengkapi tanggal dan periode terlebih dahulu');
				return;
			}
			if (reportType == 3 && !$('#periode').val()) {
				alert('Silakan pilih periode terlebih dahulu');
				return;
			}
			if (reportType == 4 && !$('#tanggal').val()) {
				alert('Silakan pilih tanggal terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				cbg: cbg,
				tanggal: $('#tanggal').val(),
				periode: $('#periode').val(),
				jam: $('#jam').val(),
				report_type: reportType,
				all: $('#all').is(':checked') ? 1 : 0
			});

			var url = '{{ route('jasper-barangobralvip-report') }}?' + params.toString();
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
				if (window.obralTable) {
					window.obralTable.search('').draw();
					$('.dataTables_filter input').focus();
				}
			}
		});

		// Handle responsive table adjustments
		$(window).on('resize', function() {
			if (window.obralTable) {
				window.obralTable.columns.adjust().responsive.recalc();
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
			$('#cbg, #periode, #jam').select2({
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

		.btn-group-toggle .btn {
			margin-bottom: 0.5rem;
		}

		.btn-group-toggle .btn.active {
			background-color: #007bff;
			border-color: #007bff;
			color: #fff;
		}

		@media (max-width: 768px) {
			.col-3 {
				margin-bottom: 1rem;
			}

			.btn-group .btn {
				margin-bottom: 0.5rem;
			}

			.btn-group-toggle .btn {
				font-size: 0.875rem;
				padding: 0.375rem 0.5rem;
			}
		}

		/* Custom styling for numeric columns */
		.text-right {
			text-align: right !important;
		}

		.text-center {
			text-align: center !important;
		}

		/* Highlight Total columns */
		.table thead th:last-child {
			background-color: #e8f5e8;
			font-weight: bold;
		}

		.table tbody td:last-child {
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

		/* Report type buttons */
		.btn-group-toggle {
			display: flex;
			flex-wrap: wrap;
		}

		.btn-group-toggle .btn {
			border-radius: 0.375rem;
			margin: 0.25rem;
		}

		.btn-group-toggle .btn:first-child {
			border-top-left-radius: 0.375rem;
			border-bottom-left-radius: 0.375rem;
		}

		.btn-group-toggle .btn:last-child {
			border-top-right-radius: 0.375rem;
			border-bottom-right-radius: 0.375rem;
		}

		/* VIP specific styling */
		.vip-highlight {
			background-color: #fff3cd;
			border-left: 4px solid #ffc107;
			padding: 0.75rem;
			margin-bottom: 1rem;
		}

		/* Borong specific styling */
		.borong-time {
			font-weight: bold;
			color: #007bff;
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

		/* Sub column styling */
		.sub-column {
			font-weight: bold;
			color: #495057;
		}

		/* Quantity and price formatting */
		.qty-cell {
			font-weight: 600;
			color: #28a745;
		}

		.price-cell {
			font-weight: 600;
			color: #dc3545;
		}

		/* Summary card enhancements */
		.summary-card {
			background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
		}

		/* Report type specific colors */
		.report-kode38 {
			border-left: 4px solid #dc3545;
		}

		.report-foodcenter {
			border-left: 4px solid #28a745;
		}

		.report-vip {
			border-left: 4px solid #ffc107;
		}

		.report-borong {
			border-left: 4px solid #007bff;
		}

		/* Time column for Borong report */
		.time-column {
			font-family: 'Courier New', monospace;
			background-color: #f8f9fa;
		}

		/* VIP price comparison */
		.vip-price-regular {
			color: #6c757d;
			text-decoration: line-through;
		}

		.vip-price-special {
			color: #28a745;
			font-weight: bold;
		}

		/* Checkbox styling */
		.form-check {
			padding-left: 1.5rem;
		}

		.form-check-input {
			margin-top: 0.3rem;
		}

		.form-check-label {
			font-weight: 500;
		}

		/* Additional responsive fixes */
		@media (max-width: 768px) {
			.btn-group-toggle {
				justify-content: center;
			}

			.info-box-number {
				font-size: 1.2rem;
			}
		}

		/* Accessibility improvements */
		.btn:focus,
		.form-control:focus,
		.form-check-input:focus {
			outline: 0;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
		}

		/* Error state styling */
		.is-invalid {
			border-color: #dc3545;
		}

		.invalid-feedback {
			display: block;
			width: 100%;
			margin-top: 0.25rem;
			font-size: 0.875rem;
			color: #dc3545;
		}
	</style>
@endsection
