@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Penerimaan Gudang</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Penerimaan Gudang</li>
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
								<form method="GET" action="{{ route('get-penerimaangudang-report') }}" id="reportForm">
									@csrf

									<ul class="nav nav-tabs" id="penerimaanGudangTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ !isset($reportType) || $reportType == 1 ? 'active' : '' }}" id="detail-report-tab" data-toggle="tab"
												href="#detail-report" role="tab" aria-controls="detail-report"
												aria-selected="{{ !isset($reportType) || $reportType == 1 ? 'true' : 'false' }}" onclick="setReportType(1)">
												<i class="fas fa-list-alt text-success mr-1"></i>Barang Datang - Tunjungsari
												@if (count($hasilDetail ?? []) > 0)
													<span class="badge badge-success ml-1">{{ count($hasilDetail) }}</span>
												@endif
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ isset($reportType) && $reportType == 2 ? 'active' : '' }}" id="tipe-report-tab" data-toggle="tab" href="#tipe-report"
												role="tab" aria-controls="tipe-report" aria-selected="{{ isset($reportType) && $reportType == 2 ? 'true' : 'false' }}"
												onclick="setReportType(2)">
												<i class="fas fa-chart-bar text-primary mr-1"></i>Harga Jual - Penerimaan
												@if (count($hasilTipe ?? []) > 0)
													<span class="badge badge-primary ml-1">{{ count($hasilTipe) }}</span>
												@endif
											</a>
										</li>
									</ul>

									<input type="hidden" name="report_type" id="report_type" value="{{ $reportType ?? 1 }}">

									<div class="tab-content" id="penerimaanGudangTabContent">
										<div class="tab-pane fade {{ !isset($reportType) || $reportType == 1 ? 'show active' : '' }}" id="detail-report" role="tabpanel"
											aria-labelledby="detail-report-tab">
											<div class="pt-3">
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
													<div class="col-2">
														<label for="per">Periode</label>
														<input type="text" name="per" id="per" class="form-control" placeholder="MM/YYYY"
															value="{{ session()->get('filter_per') }}" required>
													</div>
													<div class="col-3">
														<label for="tgl">Tanggal</label>
														<input type="date" name="tgl" id="tgl" class="form-control" value="{{ session()->get('filter_tgl') }}" required>
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

												<div class="report-content">
													@if (count($hasilDetail ?? []) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="detail-report-table">
																<thead>
																	<tr>
																		<th>No Bukti</th>
																		<th>Tanggal</th>
																		<th>Kode</th>
																		<th>Nama Barang</th>
																		<th>Ukuran</th>
																		<th>Ket_kem</th>
																		<th>LPH</th>
																		<th>Qty</th>
																		<th>Harga</th>
																		<th>Total</th>
																		<th>User</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilDetail as $item)
																		<tr>
																			<td>{{ $item->no_bukti ?? '' }}</td>
																			<td>{{ $item->tgl ?? '' }}</td>
																			<td>{{ $item->kd_brg ?? '' }}</td>
																			<td>{{ $item->na_brg ?? '' }}</td>
																			<td>{{ $item->ket_uk ?? '' }}</td>
																			<td>{{ $item->ket_kem ?? '' }}</td>
																			<td>{{ $item->lph ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->qty ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->harga ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->total ?? 0, 0, ',', '.') }}</td>
																			<td>{{ $item->usrnm ?? '' }}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg'))
																Tidak ada data barang datang untuk cabang <strong>{{ session()->get('filter_cbg') }}</strong> pada periode dan tanggal yang dipilih.
															@else
																Silakan pilih cabang, periode, dan tanggal untuk menampilkan data barang datang - tunjungsari.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>

										<div class="tab-pane fade {{ isset($reportType) && $reportType == 2 ? 'show active' : '' }}" id="tipe-report" role="tabpanel"
											aria-labelledby="tipe-report-tab">
											<div class="pt-3">
												<div class="row align-items-end mb-4">
													<div class="col-3">
														<label for="tipe">Tipe</label>
														<select name="tipe" id="tipe" class="form-control" required>
															<option value="">Pilih Tipe</option>
															<option value="BL" {{ session()->get('filter_tipe') == 'BL' ? 'selected' : '' }}>BL</option>
															<option value="B3" {{ session()->get('filter_tipe') == 'B3' ? 'selected' : '' }}>B3</option>
															<option value="B5" {{ session()->get('filter_tipe') == 'B5' ? 'selected' : '' }}>B5</option>
															<option value="B8" {{ session()->get('filter_tipe') == 'B8' ? 'selected' : '' }}>B8</option>
														</select>
													</div>
													<div class="col-4">
														<button class="btn btn-primary" type="submit" id="btnFilterTipe">
															<i class="fas fa-search mr-1"></i>Filter
														</button>
														<button class="btn btn-danger ml-2" type="button" onclick="resetTipeFilter()">
															<i class="fas fa-redo mr-1"></i>Reset
														</button>
													</div>
												</div>

												<div class="row mb-3">
													<div class="col-12">
														<button class="btn btn-success mr-2" type="button" onclick="exportTipeData('excel')">
															<i class="fas fa-file-excel mr-1"></i>Export Excel
														</button>
														<button class="btn btn-warning" type="button" onclick="cetakTipeLaporan()">
															<i class="fas fa-print mr-1"></i>Cetak Laporan
														</button>
													</div>
												</div>

												<div class="report-content">
													@if (count($hasilTipe ?? []) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="tipe-report-table">
																<thead>
																	<tr>
																		<th>No Bukti</th>
																		<th>Kode</th>
																		<th>Nama Barang</th>
																		<th>Qty</th>
																		<th>Harga Master</th>
																		<th>Harga Seharusnya</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilTipe as $item)
																		<tr>
																			<td>{{ $item->no_bukti ?? '' }}</td>
																			<td>{{ $item->KD_BRG ?? '' }}</td>
																			<td>{{ $item->NA_BRG ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->qty ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->HJX ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->HARUSX ?? 0, 0, ',', '.') }}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_tipe'))
																Tidak ada data harga jual untuk tipe <strong>{{ session()->get('filter_tipe') }}</strong> pada hari ini.
															@else
																Silakan pilih tipe untuk menampilkan data harga jual - penerimaan.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>
									</div>
								</form>

								@if (session()->get('filter_cbg') || session()->get('filter_tipe'))
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
																	<span class="info-box-text">Total Data Barang Datang</span>
																	<span class="info-box-number">{{ count($hasilDetail ?? []) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="info-box bg-primary">
																<span class="info-box-icon"><i class="fas fa-chart-bar"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Data Harga Jual</span>
																	<span class="info-box-number">{{ count($hasilTipe ?? []) }}</span>
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
																	<i class="fas fa-money-bill mr-1"></i>Total Harga:
																	<strong>Rp {{ number_format(collect($hasilDetail)->sum('harga'), 0, ',', '.') }}</strong>
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
																<i class="fas fa-store mr-1"></i>
																@if (session()->get('filter_cbg'))
																	Cabang: <strong>{{ session()->get('filter_cbg') }}</strong> |
																@endif
																@if (session()->get('filter_tipe'))
																	Tipe: <strong>{{ session()->get('filter_tipe') }}</strong> |
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
			$('#penerimaanGudangTabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');
			});

			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				localStorage.setItem('penerimaanGudangActiveTab', $(e.target).attr('href'));
			});

			var activeTab = localStorage.getItem('penerimaanGudangActiveTab');
			@if (isset($reportType))
				var reportType = {{ $reportType }};
				switch (reportType) {
					case 1:
						activeTab = '#detail-report';
						break;
					case 2:
						activeTab = '#tipe-report';
						break;
				}
			@endif

			if (activeTab) {
				$('#penerimaanGudangTabs a[href="' + activeTab + '"]').tab('show');
			}

			initializeDataTables();

			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				$.fn.dataTable.tables({
					visible: true,
					api: true
				}).columns.adjust();
			});

			$('#per').on('input', function() {
				var value = $(this).val().replace(/\D/g, '');
				if (value.length >= 2) {
					value = value.substring(0, 2) + '/' + value.substring(2, 6);
				}
				$(this).val(value);

				if (value.length === 7) {
					var parts = value.split('/');
					var month = parts[0];
					var year = parts[1];
					var startDate = year + '-' + month.padStart(2, '0') + '-01';
					$('#tgl').val(startDate);
				}
			});

			$('#reportForm').on('submit', function(e) {
				var reportType = $('#report_type').val();
				var cbg = $('#cbg').val();
				var per = $('#per').val();
				var tgl = $('#tgl').val();
				var tipe = $('#tipe').val();

				if (reportType == '1') {
					if (!cbg || !per || !tgl) {
						e.preventDefault();
						alert('Cabang, periode, dan tanggal harus diisi untuk laporan penerimaan barang');
						return false;
					}
				} else {
					if (!tipe) {
						e.preventDefault();
						alert('Tipe harus dipilih untuk laporan selisih harga');
						return false;
					}
				}

				$('.btn-primary').html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
				$('.btn-primary').prop('disabled', true);
			});
		});

		function setReportType(type) {
			$('#report_type').val(type);
		}

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

			var detailOptions = {
				...commonOptions,
				columnDefs: [{
					className: 'dt-center',
					targets: [1, 6]
				}, {
					className: 'dt-right',
					targets: [7, 8, 9]
				}]
			};

			var tipeOptions = {
				...commonOptions,
				columnDefs: [{
					className: 'dt-center',
					targets: [1]
				}, {
					className: 'dt-right',
					targets: [5, 6, 7]
				}]
			};

			if ($('#detail-report-table').length && $('#detail-report-table tbody tr').length > 0) {
				var detailTable = $('#detail-report-table').DataTable(detailOptions);
				window.detailTable = detailTable;
			}

			if ($('#tipe-report-table').length && $('#tipe-report-table tbody tr').length > 0) {
				var tipeTable = $('#tipe-report-table').DataTable(tipeOptions);
				window.tipeTable = tipeTable;
			}
		}

		function resetDetailFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter laporan penerimaan barang?')) {
				$('#cbg').val('');
				$('#per').val('');
				$('#tgl').val('');
				$('#report_type').val('1');
				$('#reportForm').submit();
			}
		}

		function resetTipeFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter laporan selisih harga?')) {
				$('#tipe').val('');
				$('#report_type').val('2');
				$('#reportForm').submit();
			}
		}

		function exportDetailData(format) {
			var cbg = $('#cbg').val();
			var per = $('#per').val();
			var tgl = $('#tgl').val();

			if (!cbg || !per || !tgl) {
				alert('Silakan lengkapi cabang, periode, dan tanggal terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 1,
				cbg: cbg,
				per: per,
				tgl: tgl,
				format: format
			});

			var url = '{{ route('jasper-penerimaangudang-report') }}?' + params.toString();
			downloadReport(url);
		}

		function exportTipeData(format) {
			var tipe = $('#tipe').val();

			if (!tipe) {
				alert('Silakan pilih tipe terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 2,
				tipe: tipe,
				format: format
			});

			var url = '{{ route('jasper-penerimaangudang-report') }}?' + params.toString();
			downloadReport(url);
		}

		function cetakDetailLaporan() {
			var cbg = $('#cbg').val();
			var per = $('#per').val();
			var tgl = $('#tgl').val();

			if (!cbg || !per || !tgl) {
				alert('Silakan lengkapi cabang, periode, dan tanggal terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 1,
				cbg: cbg,
				per: per,
				tgl: tgl
			});

			var url = '{{ route('jasper-penerimaangudang-report') }}?' + params.toString();
			printReport(url);
		}

		function cetakTipeLaporan() {
			var tipe = $('#tipe').val();

			if (!tipe) {
				alert('Silakan pilih tipe terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 2,
				tipe: tipe
			});

			var url = '{{ route('jasper-penerimaangudang-report') }}?' + params.toString();
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
				var activeTable = $('.tab-pane.active .table').DataTable();
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

		.text-right {
			text-align: right !important;
		}

		.text-center {
			text-align: center !important;
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
			.col-2,
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

		.tab-pane {
			transition: opacity 0.3s ease-in-out;
		}

		.tab-pane.fade:not(.show) {
			opacity: 0;
		}

		.tab-pane.fade.show {
			opacity: 1;
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
	</style>
@endsection
