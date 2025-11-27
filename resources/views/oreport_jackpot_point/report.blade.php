@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Jackpot & Point</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Jackpot & Point</li>
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
								<form method="GET" action="{{ route('get-jackpopoint-report') }}" id="reportForm">
									@csrf

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="jackpotPointTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ !isset($reportType) || $reportType == 1 ? 'active' : '' }}" id="jackpot-tab" data-toggle="tab" href="#jackpot"
												role="tab" aria-controls="jackpot" aria-selected="{{ !isset($reportType) || $reportType == 1 ? 'true' : 'false' }}"
												onclick="setReportType(1)">
												<i class="fas fa-trophy text-warning mr-1"></i>Hadiah Jackpot
												@if (count($hasilJackpot) > 0)
													<span class="badge badge-warning ml-1">{{ count($hasilJackpot) }}</span>
												@endif
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ isset($reportType) && $reportType == 2 ? 'active' : '' }}" id="point-tab" data-toggle="tab" href="#point"
												role="tab" aria-controls="point" aria-selected="{{ isset($reportType) && $reportType == 2 ? 'true' : 'false' }}"
												onclick="setReportType(2)">
												<i class="fas fa-coins text-primary mr-1"></i>Double Poin Bank
												@if (count($hasilPoint) > 0)
													<span class="badge badge-primary ml-1">{{ count($hasilPoint) }}</span>
												@endif
											</a>
										</li>
									</ul>

									<!-- Hidden field for report type -->
									<input type="hidden" name="report_type" id="report_type" value="{{ $reportType ?? 1 }}">

									<!-- Tab panes -->
									<div class="tab-content" id="jackpotPointTabContent">
										<!-- Jackpot Tab -->
										<div class="tab-pane fade {{ !isset($reportType) || $reportType == 1 ? 'show active' : '' }}" id="jackpot" role="tabpanel"
											aria-labelledby="jackpot-tab">
											<div class="pt-3">
												<!-- Jackpot Filter Controls -->
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
													<div class="col-2">
														<label for="per">Periode</label>
														<input type="text" name="per" id="per" class="form-control" value="{{ session()->get('filter_per') }}"
															placeholder="mm-YYYY" required>
													</div>
													<div class="col-2">
														<label for="tgl1">Tanggal Mulai</label>
														<input type="date" name="tgl1" id="tgl1" class="form-control" value="{{ session()->get('filter_tgl1') }}" required>
													</div>
													<div class="col-2">
														<label for="tgl2">Tanggal Selesai</label>
														<input type="date" name="tgl2" id="tgl2" class="form-control" value="{{ session()->get('filter_tgl2') }}" required>
													</div>
													<div class="col-2">
														<label for="posted">Posted</label>
														<select name="posted" id="posted" class="form-control">
															<option value="">Semua</option>
															<option value="yes" {{ session()->get('filter_posted') == 'yes' ? 'selected' : '' }}>Ya</option>
															<option value="no" {{ session()->get('filter_posted') == 'no' ? 'selected' : '' }}>Tidak</option>
														</select>
													</div>
													<div class="col-1">
														<button class="btn btn-primary" type="submit" id="btnFilterJackpot">
															<i class="fas fa-search mr-1"></i>Filter
														</button>
													</div>
												</div>

												<!-- Jackpot Actions -->
												<div class="row mb-3">
													<div class="col-12">
														<button class="btn btn-danger mr-2" type="button" onclick="resetJackpotFilter()">
															<i class="fas fa-redo mr-1"></i>Reset
														</button>
														<button class="btn btn-success mr-2" type="button" onclick="exportJackpotData('excel')">
															<i class="fas fa-file-excel mr-1"></i>Export Excel
														</button>
														<button class="btn btn-warning" type="button" onclick="cetakJackpotLaporan()">
															<i class="fas fa-print mr-1"></i>Cetak Laporan
														</button>
													</div>
												</div>

												<!-- Jackpot Report Content -->
												<div class="report-content">
													@if (count($hasilJackpot) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="jackpot-table">
																<thead>
																	<tr>
																		<th>No Bukti</th>
																		<th>Tanggal</th>
																		<th>Kode Cust</th>
																		<th>Nama Cust</th>
																		<th>Hadiah</th>
																		<th>Total Belanja</th>
																		<th>KSR</th>
																		<th>Posted</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilJackpot as $item)
																		<tr>
																			<td>{{ $item->NO_BUKTI ?? '' }}</td>
																			<td>{{ $item->TGL ? date('d/m/Y', strtotime($item->TGL)) : '-' }}</td>
																			<td>{{ $item->KODEC ?? '' }}</td>
																			<td>{{ $item->NAMAC ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->JACKPOT ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->STIKER ?? 0, 0, ',', '.') }}</td>
																			<td>{{ $item->KSR ?? '' }}</td>
																			<td class="text-center">
																				@if ($item->POSTED == 1)
																					<span class="badge badge-success">Ya</span>
																				@else
																					<span class="badge badge-danger">Tidak</span>
																				@endif
																			</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg'))
																Tidak ada data jackpot untuk cabang <strong>{{ session()->get('filter_cbg') }}</strong>
																pada periode <strong>{{ session()->get('filter_per') }}</strong>.
															@else
																Silakan pilih cabang dan periode untuk menampilkan data jackpot.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>

										<!-- Point Tab -->
										<div class="tab-pane fade {{ isset($reportType) && $reportType == 2 ? 'show active' : '' }}" id="point" role="tabpanel"
											aria-labelledby="point-tab">
											<div class="pt-3">
												<!-- Point Filter Controls -->
												<div class="row align-items-end mb-4">
													<div class="col-3">
														<label for="cbg2">Cabang</label>
														<select name="cbg2" id="cbg2" class="form-control" required>
															<option value="">Pilih Cabang</option>
															@foreach ($cbg as $cabang)
																<option value="{{ $cabang->CBG }}" {{ session()->get('filter_cbg2') == $cabang->CBG ? 'selected' : '' }}>
																	{{ $cabang->CBG }}
																</option>
															@endforeach
														</select>
													</div>
													<div class="col-2">
														<label for="tgl_poin1">Tanggal Mulai</label>
														<input type="date" name="tgl_poin1" id="tgl_poin1" class="form-control" value="{{ session()->get('filter_tgl_poin1') }}"
															required>
													</div>
													<div class="col-2">
														<label for="tgl_poin2">Tanggal Selesai</label>
														<input type="date" name="tgl_poin2" id="tgl_poin2" class="form-control" value="{{ session()->get('filter_tgl_poin2') }}"
															required>
													</div>
													<div class="col-2">
														<label for="bank">Bank</label>
														<select name="bank" id="bank" class="form-control" required>
															<option value="">Pilih Bank</option>
															@foreach ($banks as $bank)
																<option value="{{ $bank->bank_code }}" {{ session()->get('filter_bank') == $bank->bank_code ? 'selected' : '' }}>
																	{{ $bank->bank_name }}
																</option>
															@endforeach
														</select>
													</div>
													<div class="col-2">
														<label for="member_type">Tipe Member</label>
														<select name="member_type" id="member_type" class="form-control">
															<option value="member" {{ session()->get('filter_member_type') == 'member' ? 'selected' : '' }}>Member</option>
															<option value="semua" {{ session()->get('filter_member_type') == 'semua' ? 'selected' : '' }}>Semua</option>
														</select>
													</div>
													<div class="col-1">
														<button class="btn btn-primary" type="submit" id="btnFilterPoint">
															<i class="fas fa-search mr-1"></i>Filter
														</button>
													</div>
												</div>

												<!-- Point Actions -->
												<div class="row mb-3">
													<div class="col-12">
														<button class="btn btn-danger mr-2" type="button" onclick="resetPointFilter()">
															<i class="fas fa-redo mr-1"></i>Reset
														</button>
														<button class="btn btn-success mr-2" type="button" onclick="exportPointData('excel')">
															<i class="fas fa-file-excel mr-1"></i>Export Excel
														</button>
														<button class="btn btn-warning" type="button" onclick="cetakPointLaporan()">
															<i class="fas fa-print mr-1"></i>Cetak Laporan
														</button>
													</div>
												</div>

												<!-- Point Report Content -->
												<div class="report-content">
													@if (count($hasilPoint) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="point-table">
																<thead>
																	<tr>
																		<th>Member</th>
																		<th>Nama Member</th>
																		<th>KSR</th>
																		<th>TGL</th>
																		<th>STIKER</th>
																		<th>TYPE</th>
																		<th>JUMLAH</th>
																		<th>NKARTU</th>
																		<th>NBANK</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilPoint as $item)
																		<tr>
																			<td>{{ $item->KODEC ?? '' }}</td>
																			<td>{{ $item->NAMAC ?? '' }}</td>
																			<td>{{ $item->KSR ?? '' }}</td>
																			<td>{{ $item->TGL ? date('d/m/Y H:i:s', strtotime($item->TGL)) : '-' }}</td>
																			<td class="text-right">{{ number_format($item->STIKER ?? 0, 0, ',', '.') }}</td>
																			<td>{{ $item->TYPE ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->JUMLAH ?? 0, 0, ',', '.') }}</td>
																			<td>{{ $item->NKARTU ?? '' }}</td>
																			<td>{{ $item->NBANK ?? '' }}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg2') && session()->get('filter_bank'))
																Tidak ada data point untuk cabang <strong>{{ session()->get('filter_cbg2') }}</strong>
																dan bank <strong>{{ session()->get('filter_bank') }}</strong>.
															@else
																Silakan pilih cabang, tanggal, dan bank untuk menampilkan data point.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>
									</div>
								</form>

								<!-- Summary Information -->
								@if (session()->get('filter_cbg') || session()->get('filter_cbg2'))
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
																<span class="info-box-icon"><i class="fas fa-trophy"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Jackpot</span>
																	<span class="info-box-number">{{ count($hasilJackpot) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="info-box bg-primary">
																<span class="info-box-icon"><i class="fas fa-coins"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Point</span>
																	<span class="info-box-number">{{ count($hasilPoint) }}</span>
																</div>
															</div>
														</div>
													</div>
													<div class="row mt-2">
														<div class="col-12">
															<small class="text-muted">
																<i class="fas fa-store mr-1"></i>Cabang:
																<strong>{{ session()->get('filter_cbg') ?: session()->get('filter_cbg2') }}</strong> |
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
			$('#jackpotPointTabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');
			});

			// Save active tab to localStorage
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				localStorage.setItem('jackpotPointActiveTab', $(e.target).attr('href'));
			});

			// Restore active tab from localStorage or set based on reportType
			var activeTab = localStorage.getItem('jackpotPointActiveTab');
			@if (isset($reportType))
				var reportType = {{ $reportType }};
				switch (reportType) {
					case 1:
						activeTab = '#jackpot';
						break;
					case 2:
						activeTab = '#point';
						break;
				}
			@endif

			if (activeTab) {
				$('#jackpotPointTabs a[href="' + activeTab + '"]').tab('show');
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

				if (reportType == '1') {
					// Jackpot validation
					var cbg = $('#cbg').val();
					var per = $('#per').val();
					var tgl1 = $('#tgl1').val();
					var tgl2 = $('#tgl2').val();

					if (!cbg || !per || !tgl1 || !tgl2) {
						e.preventDefault();
						alert('Semua field untuk jackpot harus diisi');
						return false;
					}
				} else {
					// Point validation
					var cbg2 = $('#cbg2').val();
					var tglPoin1 = $('#tgl_poin1').val();
					var tglPoin2 = $('#tgl_poin2').val();
					var bank = $('#bank').val();

					if (!cbg2 || !tglPoin1 || !tglPoin2 || !bank) {
						e.preventDefault();
						alert('Cabang, tanggal, dan bank harus diisi untuk laporan point');
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

			// Jackpot table options
			var jackpotOptions = {
				...commonOptions,
				columnDefs: [{
					className: 'dt-right',
					targets: [4, 5] // Hadiah, Total Belanja
				}, {
					className: 'dt-center',
					targets: [1, 7] // Tanggal, Posted
				}]
			};

			// Point table options
			var pointOptions = {
				...commonOptions,
				columnDefs: [{
					className: 'dt-right',
					targets: [4, 6] // STIKER, JUMLAH
				}, {
					className: 'dt-center',
					targets: [3] // TGL
				}]
			};

			// Initialize tables if they have data
			if ($('#jackpot-table').length && $('#jackpot-table tbody tr').length > 0) {
				var jackpotTable = $('#jackpot-table').DataTable(jackpotOptions);
				window.jackpotTable = jackpotTable;
			}

			if ($('#point-table').length && $('#point-table tbody tr').length > 0) {
				var pointTable = $('#point-table').DataTable(pointOptions);
				window.pointTable = pointTable;
			}
		}

		// Reset jackpot filter function
		function resetJackpotFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter jackpot?')) {
				$('#cbg').val('');
				$('#per').val('');
				$('#tgl1').val('');
				$('#tgl2').val('');
				$('#posted').val('');
				$('#report_type').val('1');

				// Submit form to clear session
				$('#reportForm').submit();
			}
		}

		// Reset point filter function
		function resetPointFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter point?')) {
				$('#cbg2').val('');
				$('#tgl_poin1').val('');
				$('#tgl_poin2').val('');
				$('#bank').val('');
				$('#member_type').val('member');
				$('#report_type').val('2');

				// Submit form to clear session
				$('#reportForm').submit();
			}
		}

		// Export jackpot data function
		function exportJackpotData(format) {
			var cbg = $('#cbg').val();
			var per = $('#per').val();
			var tgl1 = $('#tgl1').val();
			var tgl2 = $('#tgl2').val();

			if (!cbg || !per || !tgl1 || !tgl2) {
				alert('Silakan isi semua filter jackpot terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 1,
				cbg: cbg,
				per: per,
				tgl1: tgl1,
				tgl2: tgl2,
				posted: $('#posted').val(),
				format: format
			});

			var url = '{{ route('jasper-jackpopoint-report') }}?' + params.toString();
			downloadReport(url);
		}

		// Export point data function
		function exportPointData(format) {
			var cbg2 = $('#cbg2').val();
			var tglPoin1 = $('#tgl_poin1').val();
			var tglPoin2 = $('#tgl_poin2').val();
			var bank = $('#bank').val();

			if (!cbg2 || !tglPoin1 || !tglPoin2 || !bank) {
				alert('Silakan isi semua filter point terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 2,
				cbg2: cbg2,
				tgl_poin1: tglPoin1,
				tgl_poin2: tglPoin2,
				bank: bank,
				member_type: $('#member_type').val(),
				format: format
			});

			var url = '{{ route('jasper-jackpopoint-report') }}?' + params.toString();
			downloadReport(url);
		}

		// Print jackpot report function
		function cetakJackpotLaporan() {
			var cbg = $('#cbg').val();
			var per = $('#per').val();
			var tgl1 = $('#tgl1').val();
			var tgl2 = $('#tgl2').val();

			if (!cbg || !per || !tgl1 || !tgl2) {
				alert('Silakan isi semua filter jackpot terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 1,
				cbg: cbg,
				per: per,
				tgl1: tgl1,
				tgl2: tgl2,
				posted: $('#posted').val()
			});

			var url = '{{ route('jasper-jackpopoint-report') }}?' + params.toString();
			printReport(url);
		}

		// Print point report function
		function cetakPointLaporan() {
			var cbg2 = $('#cbg2').val();
			var tglPoin1 = $('#tgl_poin1').val();
			var tglPoin2 = $('#tgl_poin2').val();
			var bank = $('#bank').val();

			if (!cbg2 || !tglPoin1 || !tglPoin2 || !bank) {
				alert('Silakan isi semua filter point terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 2,
				cbg2: cbg2,
				tgl_poin1: tglPoin1,
				tgl_poin2: tglPoin2,
				bank: bank,
				member_type: $('#member_type').val()
			});

			var url = '{{ route('jasper-jackpopoint-report') }}?' + params.toString();
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

			.col-3,
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
