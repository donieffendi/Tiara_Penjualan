@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Sales Penjualan SPM</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Sales Penjualan SPM</li>
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
								<form method="GET" action="{{ route('get-salespenjualanspm-report') }}" id="reportForm">
									@csrf

									<!-- Global Filters -->
									<div class="row align-items-end mb-4 mt-3">
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

										<div class="col-2">
											<label for="tanggal">Tanggal</label>
											<input type="date" name="tanggal" id="tanggal" class="form-control"
												value="{{ session()->get('filter_tanggal') }}">
										</div>

									</div>
									<!-- Global filters -->

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="salesPenjualanSPMTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ !isset($reportType) || $reportType == 1 ? 'active' : '' }}" id="per-kasir-tab" data-toggle="tab" href="#per-kasir"
												role="tab" aria-controls="per-kasir" aria-selected="{{ !isset($reportType) || $reportType == 1 ? 'true' : 'false' }}"
												onclick="setReportType(1)">
												<i class="fas fa-cash-register text-success mr-1"></i>Laporan per Kasir
												@if (count($hasilPerKasir ?? []) > 0)
													<span class="badge badge-success ml-1">{{ count($hasilPerKasir) }}</span>
												@endif
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ isset($reportType) && $reportType == 2 ? 'active' : '' }}" id="per-user-tab" data-toggle="tab" href="#per-user"
												role="tab" aria-controls="per-user" aria-selected="{{ isset($reportType) && $reportType == 2 ? 'true' : 'false' }}"
												onclick="setReportType(2)">
												<i class="fas fa-user text-primary mr-1"></i>Laporan per User
												@if (count($hasilPerUser ?? []) > 0)
													<span class="badge badge-primary ml-1">{{ count($hasilPerUser) }}</span>
												@endif
											</a>
										</li>
									</ul>

									<!-- Hidden field for report type -->
									<input type="hidden" name="report_type" id="report_type" value="{{ $reportType ?? 1 }}">

									<!-- Tab panes -->
									<div class="tab-content" id="salesPenjualanSPMTabContent">
										<!-- Laporan per Kasir Tab -->
										<div class="tab-pane fade {{ !isset($reportType) || $reportType == 1 ? 'show active' : '' }}" id="per-kasir" role="tabpanel"
											aria-labelledby="per-kasir-tab">
											<div class="pt-3">
												<!-- Per Kasir Filter Controls -->
												<div class="row align-items-end mb-4">
													
													<div class="col-3">
														<label for="kasir">Kasir</label>
														<select name="kasir" id="kasir" class="form-control">
															<option value="">Pilih Kasir</option>
															@foreach ($kasirList as $kasirItem)
																<option value="{{ $kasirItem->kasir }}" {{ session()->get('filter_kasir') == $kasirItem->kasir ? 'selected' : '' }}>
																	{{ $kasirItem->kasir }}
																</option>
															@endforeach
														</select>
													</div>
													
												</div>

												<!-- Per Kasir Actions -->
												<div class="row mb-3">
													<div class="col-12">
														<button class="btn btn-primary mr-2" type="submit">
																<i class="fas fa-search mr-1"></i>Filter
														</button>
														<button class="btn btn-danger mr-2" type="button" onclick="resetPerKasirFilter()">
															<i class="fas fa-redo mr-1"></i>Reset
														</button>
														<button class="btn btn-success mr-2" type="button" onclick="exportPerKasirData('excel')">
															<i class="fas fa-file-excel mr-1"></i>Export Excel
														</button>
														<button class="btn btn-warning" type="button" onclick="cetakPerKasirLaporan()">
															<i class="fas fa-print mr-1"></i>Cetak Laporan
														</button>
													</div>
												</div>

												<!-- Per Kasir Report Content -->
												<div class="report-content">
													@if (count($hasilPerKasir ?? []) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="per-kasir-table">
																<thead>
																	<tr>
																		<th>CBG</th>
																		<th>Kasir</th>
																		<th>Shift</th>
																		<th>Sub</th>
																		<th>Item</th>
																		<th>Nama Barang</th>
																		<th>Qty</th>
																		<th>Harga Jual</th>
																		<th>PPN</th>
																		<th>PPN Keterangan</th>
																		<th>Nilai PPN</th>
																		<th>DPP</th>
																		<th>TKP</th>
																		<th>Total</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilPerKasir as $item)
																		<tr>
																			<td>{{ $item->cbg ?? '' }}</td>
																			<td>{{ $item->KSR ?? '' }}</td>
																			<td>{{ $item->SHIFT ?? '' }}</td>
																			<td>{{ $item->SUB ?? '' }}</td>
																			<td>{{ $item->KD_BRG ?? '' }}</td>
																			<td>{{ $item->NA_BRG ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->qty ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->harga ?? 0, 0, ',', '.') }}</td>
																			<td class="text-center">{{ $item->ppn ?? '' }}</td>
																			<td>{{ $item->PPN_KET ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->nppn ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->dpp ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->tkp ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->total ?? 0, 0, ',', '.') }}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg') && session()->get('filter_kasir') && session()->get('filter_tanggal'))
																Tidak ada data penjualan untuk Kasir <strong>{{ session()->get('filter_kasir') }}</strong>
																pada tanggal <strong>{{ session()->get('filter_tanggal') }}</strong>
																di cabang <strong>{{ session()->get('filter_cbg') }}</strong>.
															@else
																Silakan pilih cabang, kasir, dan tanggal untuk menampilkan data penjualan per kasir.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>

										<!-- Laporan per User Tab -->
										<div class="tab-pane fade {{ isset($reportType) && $reportType == 2 ? 'show active' : '' }}" id="per-user" role="tabpanel"
											aria-labelledby="per-user-tab">
											<div class="pt-3">
												<!-- Per User Filter Controls -->
												<div class="row align-items-end mb-4">
													
													<div class="col-2">
														<label for="user">User</label>
														<input type="text" name="user" id="user" class="form-control" placeholder="Masukkan nama user"
															value="{{ session()->get('filter_user') }}">
													</div>
													
												</div>

												<!-- Per User Actions -->
												<div class="row mb-3">
													<div class="col-12">
														<button class="btn btn-primary mr-2" type="submit">
																<i class="fas fa-search mr-1"></i>Filter
														</button>
														<button class="btn btn-danger mr-2" type="button" onclick="resetPerUserFilter()">
															<i class="fas fa-redo mr-1"></i>Reset
														</button>
														<button class="btn btn-success mr-2" type="button" onclick="exportPerUserData('excel')">
															<i class="fas fa-file-excel mr-1"></i>Export Excel
														</button>
														<button class="btn btn-warning" type="button" onclick="cetakPerUserLaporan()">
															<i class="fas fa-print mr-1"></i>Cetak Laporan
														</button>
													</div>
												</div>

												<!-- Per User Report Content -->
												<div class="report-content">
													@if (count($hasilPerUser ?? []) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="per-user-table">
																<thead>
																	<tr>
																		<th>CBG</th>
																		<th>User</th>
																		<th>Shift</th>
																		<th>Sub</th>
																		<th>Item</th>
																		<th>Nama Barang</th>
																		<th>Qty</th>
																		<th>Harga Jual</th>
																		<th>PPN</th>
																		<th>PPN Keterangan</th>
																		<th>Nilai PPN</th>
																		<th>DPP</th>
																		<th>TKP</th>
																		<th>Total</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilPerUser as $item)
																		<tr>
																			<td>{{ $item->cbg ?? '' }}</td>
																			<td>{{ $item->usrnm ?? '' }}</td>
																			<td>{{ $item->SHIFT ?? '' }}</td>
																			<td>{{ $item->SUB ?? '' }}</td>
																			<td>{{ $item->KD_BRG ?? '' }}</td>
																			<td>{{ $item->NA_BRG ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->qty ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->harga ?? 0, 0, ',', '.') }}</td>
																			<td class="text-center">{{ $item->ppn ?? '' }}</td>
																			<td>{{ $item->PPN_KET ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->nppn ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->dpp ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->tkp ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->total ?? 0, 0, ',', '.') }}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg') && session()->get('filter_user') && session()->get('filter_tanggal') && session()->get('filter_periode'))
																Tidak ada data penjualan untuk User <strong>{{ session()->get('filter_user') }}</strong>
																pada tanggal <strong>{{ session()->get('filter_tanggal') }}</strong>
																periode <strong>{{ session()->get('filter_periode') }}</strong>
																di cabang <strong>{{ session()->get('filter_cbg') }}</strong>.
															@else
																Silakan pilih cabang, user, periode, dan tanggal untuk menampilkan data penjualan per user.
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
																<span class="info-box-icon"><i class="fas fa-cash-register"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Data per Kasir</span>
																	<span class="info-box-number">{{ count($hasilPerKasir ?? []) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="info-box bg-primary">
																<span class="info-box-icon"><i class="fas fa-user"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Data per User</span>
																	<span class="info-box-number">{{ count($hasilPerUser ?? []) }}</span>
																</div>
															</div>
														</div>
													</div>
													@if (count($hasilPerKasir ?? []) > 0 && isset($reportType) && $reportType == 1)
														<div class="row mt-2">
															<div class="col-md-4">
																<small class="text-muted">
																	<i class="fas fa-calculator mr-1"></i>Total Qty:
																	<strong>{{ number_format(collect($hasilPerKasir)->sum('qty'), 0, ',', '.') }}</strong>
																</small>
															</div>
															<div class="col-md-4">
																<small class="text-muted">
																	<i class="fas fa-money-bill mr-1"></i>Total DPP:
																	<strong>Rp {{ number_format(collect($hasilPerKasir)->sum('dpp'), 0, ',', '.') }}</strong>
																</small>
															</div>
															<div class="col-md-4">
																<small class="text-muted">
																	<i class="fas fa-receipt mr-1"></i>Total Amount:
																	<strong>Rp {{ number_format(collect($hasilPerKasir)->sum('total'), 0, ',', '.') }}</strong>
																</small>
															</div>
														</div>
													@endif
													@if (count($hasilPerUser ?? []) > 0 && isset($reportType) && $reportType == 2)
														<div class="row mt-2">
															<div class="col-md-4">
																<small class="text-muted">
																	<i class="fas fa-calculator mr-1"></i>Total Qty:
																	<strong>{{ number_format(collect($hasilPerUser)->sum('qty'), 0, ',', '.') }}</strong>
																</small>
															</div>
															<div class="col-md-4">
																<small class="text-muted">
																	<i class="fas fa-money-bill mr-1"></i>Total DPP:
																	<strong>Rp {{ number_format(collect($hasilPerUser)->sum('dpp'), 0, ',', '.') }}</strong>
																</small>
															</div>
															<div class="col-md-4">
																<small class="text-muted">
																	<i class="fas fa-receipt mr-1"></i>Total Amount:
																	<strong>Rp {{ number_format(collect($hasilPerUser)->sum('total'), 0, ',', '.') }}</strong>
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
			$('#salesPenjualanSPMTabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');
			});

			// Save active tab to localStorage
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				localStorage.setItem('salesPenjualanSPMActiveTab', $(e.target).attr('href'));
			});

			// Restore active tab from localStorage or set based on reportType
			var activeTab = localStorage.getItem('salesPenjualanSPMActiveTab');
			@if (isset($reportType))
				var reportType = {{ $reportType }};
				switch (reportType) {
					case 1:
						activeTab = '#per-kasir';
						break;
					case 2:
						activeTab = '#per-user';
						break;
				}
			@endif

			if (activeTab) {
				$('#salesPenjualanSPMTabs a[href="' + activeTab + '"]').tab('show');
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
				// var cbg2 = $('#cbg2').val();

				if (reportType == '1' || reportType == '2') {
					// Per Kasir validation
					if (!cbg) {
						e.preventDefault();
						alert('Cabang harus dipilih');
						return false;
					}
					if (!$('#tanggal').val()) {
						e.preventDefault();
						alert('Tanggal harus dipilih');
						return false;
					}
					if (!$('#periode').val()) {
						e.preventDefault();
						alert('Periode harus dipilih untuk ');
						return false;
					}
				} else if (reportType == '1') {
					
					if (!$('#kasir').val()) {
						e.preventDefault();
						alert('Kasir harus dipilih untuk laporan per kasir');
						return false;
					}

				} else if (reportType == '2') {
					// Per User validation
					if (!$('#user').val()) {
						e.preventDefault();
						alert('User harus diisi untuk laporan per user');
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
					url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
				}
			};

			// Per Kasir table options
			var perKasirOptions = {
				...commonOptions,
				columnDefs: [{
					className: 'dt-center',
					targets: [0, 1, 2, 3, 8] // CBG, Kasir, Shift, Sub, PPN
				}, {
					className: 'dt-right',
					targets: [6, 7, 10, 11, 12, 13] // Qty, Harga, Nilai PPN, DPP, TKP, Total
				}]
			};

			// Per User table options
			var perUserOptions = {
				...commonOptions,
				columnDefs: [{
					className: 'dt-center',
					targets: [0, 1, 2, 3, 8] // CBG, User, Shift, Sub, PPN
				}, {
					className: 'dt-right',
					targets: [6, 7, 10, 11, 12, 13] // Qty, Harga, Nilai PPN, DPP, TKP, Total
				}]
			};

			// Initialize tables if they have data
			if ($('#per-kasir-table').length && $('#per-kasir-table tbody tr').length > 0) {
				var perKasirTable = $('#per-kasir-table').DataTable(perKasirOptions);
				window.perKasirTable = perKasirTable;
			}

			if ($('#per-user-table').length && $('#per-user-table tbody tr').length > 0) {
				var perUserTable = $('#per-user-table').DataTable(perUserOptions);
				window.perUserTable = perUserTable;
			}
		}

		// Reset per kasir filter function
		function resetPerKasirFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter laporan per kasir?')) {
				$('#cbg').val('');
				$('#kasir').val('');
				$('#periode').val('');
				$('#tanggal').val('');
				$('#report_type').val('1');

				// Submit form to clear session
				$('#reportForm').submit();
			}
		}

		// Reset per user filter function
		function resetPerUserFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter laporan per user?')) {
				$('#cbg2').val('');
				$('#user').val('');
				$('#periode2').val('');
				$('#tanggal2').val('');
				$('#report_type').val('2');

				// Submit form to clear session
				$('#reportForm').submit();
			}
		}

		// Export per kasir data function
		function exportPerKasirData(format) {
			var cbg = $('#cbg').val();
			var kasir = $('#kasir').val();
			var periode = $('#periode').val();
			var tanggal = $('#tanggal').val();

			if (!cbg) {
				alert('Silakan pilih cabang terlebih dahulu');
				return;
			}
			if (!kasir) {
				alert('Silakan pilih kasir terlebih dahulu');
				return;
			}
			if (!tanggal) {
				alert('Silakan pilih tanggal terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 1,
				cbg: cbg,
				kasir: kasir,
				periode: periode,
				tanggal: tanggal,
				format: format
			});

			var url = '{{ route('jasper-salespenjualanspm-report') }}?' + params.toString();
			downloadReport(url);
		}

		// Export per user data function
		function exportPerUserData(format) {
			var cbg = $('#cbg2').val() || $('#cbg').val();
			var user = $('#user').val();
			var periode = $('#periode2').val();
			var tanggal = $('#tanggal2').val();

			if (!cbg) {
				alert('Silakan pilih cabang terlebih dahulu');
				return;
			}
			if (!user) {
				alert('Silakan isi user terlebih dahulu');
				return;
			}
			if (!periode) {
				alert('Silakan pilih periode terlebih dahulu');
				return;
			}
			if (!tanggal) {
				alert('Silakan pilih tanggal terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 2,
				cbg: cbg,
				user: user,
				periode: periode,
				tanggal: tanggal,
				format: format
			});

			var url = '{{ route('jasper-salespenjualanspm-report') }}?' + params.toString();
			downloadReport(url);
		}

		// Print per kasir report function
		function cetakPerKasirLaporan() {
			var cbg = $('#cbg').val();
			var kasir = $('#kasir').val();
			var periode = $('#periode').val();
			var tanggal = $('#tanggal').val();

			if (!cbg || !kasir || !tanggal) {
				alert('Silakan lengkapi filter terlebih dahulu (Cabang, Kasir, dan Tanggal)');
				return;
			}

			var params = new URLSearchParams({
				report_type: 1,
				cbg: cbg,
				kasir: kasir,
				periode: periode,
				tanggal: tanggal
			});

			var url = '{{ route('jasper-salespenjualanspm-report') }}?' + params.toString();
			printReport(url);
		}

		// Print per user report function
		function cetakPerUserLaporan() {
			var cbg = $('#cbg').val();
			var user = $('#user').val();
			var periode = $('#periode').val();
			var tanggal = $('#tanggal').val();

			if (!cbg || !user || !periode || !tanggal) {
				alert('Silakan lengkapi filter terlebih dahulu (Cabang, User, Periode, dan Tanggal)');
				return;
			}

			var params = new URLSearchParams({
				report_type: 2,
				cbg: cbg,
				user: user,
				periode: periode,
				tanggal: tanggal
			});

			var url = '{{ route('jasper-salespenjualanspm-report') }}?' + params.toString();
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

		// Auto-calculate summary when data is available
		function calculateSummary(data, type) {
			if (!data || data.length === 0) return;

			var totalQty = 0;
			var totalDpp = 0;
			var totalAmount = 0;

			data.forEach(function(item) {
				totalQty += parseFloat(item.qty || 0);
				totalDpp += parseFloat(item.dpp || 0);
				totalAmount += parseFloat(item.total || 0);
			});

			// Update summary display if elements exist
			if (type === 'kasir') {
				$('#summary-kasir-qty').text(totalQty.toLocaleString('id-ID'));
				$('#summary-kasir-dpp').text('Rp ' + totalDpp.toLocaleString('id-ID'));
				$('#summary-kasir-total').text('Rp ' + totalAmount.toLocaleString('id-ID'));
			} else if (type === 'user') {
				$('#summary-user-qty').text(totalQty.toLocaleString('id-ID'));
				$('#summary-user-dpp').text('Rp ' + totalDpp.toLocaleString('id-ID'));
				$('#summary-user-total').text('Rp ' + totalAmount.toLocaleString('id-ID'));
			}
		}

		// Format currency function
		function formatCurrency(amount) {
			return 'Rp ' + parseFloat(amount || 0).toLocaleString('id-ID');
		}

		// Format number function
		function formatNumber(number) {
			return parseFloat(number || 0).toLocaleString('id-ID');
		}

		// Sync form fields between tabs
		$('#cbg').on('change', function() {
			$('#cbg2').val($(this).val());
		});

		$('#cbg2').on('change', function() {
			$('#cbg').val($(this).val());
		});

		// Auto-submit on Enter key for input fields
		$('#user').on('keypress', function(e) {
			if (e.which === 13) {
				$('#reportForm').submit();
			}
		});

		// Validate date input
		$('#tanggal, #tanggal2').on('change', function() {
			var selectedDate = new Date($(this).val());
			var today = new Date();

			if (selectedDate > today) {
				alert('Tanggal tidak boleh melebihi hari ini');
				$(this).val('');
			}
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
			$('#cbg, #cbg2, #kasir, #periode, #periode2').select2({
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
	</style>
@endsection
