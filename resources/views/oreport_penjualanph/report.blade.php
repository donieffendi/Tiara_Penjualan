@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Penjualan PH</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Penjualan PH</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								@if (isset($error))
									<div class="alert alert-danger alert-dismissible">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Error:</strong> {{ $error }}
									</div>
								@endif

								<form method="GET" action="{{ route('get-penjualanph-report') }}" id="reportForm">
									@csrf
									<!-- Filter Section -->
									<div class="row align-items-end mb-3">
										<div class="col-2">
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
											<select name="periode" id="periode" class="form-control" required>
												<option value="">Pilih Periode</option>
												@foreach ($per as $period)
													<option value="{{ $period->perio }}" {{ session()->get('filter_periode') == $period->perio ? 'selected' : '' }}>
														{{ $period->perio }}
													</option>
												@endforeach
											</select>
										</div>
										<div class="col-2">
											<label for="tgl1">Tanggal Mulai</label>
											<input type="date" name="tgl1" id="tgl1" class="form-control" value="{{ session()->get('filter_tgl1') }}">
										</div>
										<div class="col-2">
											<label for="tgl2">Tanggal Akhir</label>
											<input type="date" name="tgl2" id="tgl2" class="form-control" value="{{ session()->get('filter_tgl2') }}">
										</div>
										<div class="col-4">
											<button class="btn btn-primary mr-1" type="submit" id="btnFilter">
												<i class="fas fa-search mr-1"></i>Filter
											</button>
											<button class="btn btn-danger mr-1" type="button" onclick="resetFilter()">
												<i class="fas fa-redo mr-1"></i>Reset
											</button>
											<button class="btn btn-warning mr-1" type="button" onclick="cetakReport()">
												<i class="fas fa-print mr-1"></i>Cetak
											</button>
											<button class="btn btn-success" type="button" onclick="exportExcel()">
												<i class="fas fa-file-excel mr-1"></i>Export Excel
											</button>
										</div>
									</div>

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="penjualan-tab" data-toggle="tab" href="#penjualan" role="tab" aria-controls="penjualan"
												aria-selected="true">
												<i class="fas fa-shopping-cart mr-1"></i>Report Penjualan
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="pb1-tab" data-toggle="tab" href="#pb1" role="tab" aria-controls="pb1" aria-selected="false">
												<i class="fas fa-chart-bar mr-1"></i>PB1
											</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Report Penjualan Tab -->
										<div class="tab-pane fade show active" id="penjualan" role="tabpanel" aria-labelledby="penjualan-tab">
											<div class="pt-3">
												<div class="report-content col-md-12">
													@if (!empty($hasilPenjualanPH))
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="tabelPenjualan">
																<thead>
																	<tr>
																		<th>Kode</th>
																		<th>Nama</th>
																		<th>Stand</th>
																		<th>Kodes</th>
																		<th>Nama Supplier</th>
																		<th>Qty Laku</th>
																		<th>Total Laku</th>
																		<th>Qty Normal</th>
																		<th>Total Normal</th>
																		<th>Qty Obral</th>
																		<th>Total Obral</th>
																		<th>Tgl</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilPenjualanPH as $item)
																		<tr>
																			<td>{{ $item->KD_BRG ?? '' }}</td>
																			<td>{{ $item->NA_BRG ?? '' }}</td>
																			<td>{{ $item->STAND ?? '' }}</td>
																			<td>{{ $item->KODES ?? '' }}</td>
																			<td>{{ $item->NAMAS ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->qtylaku ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->totlaku ?? 0, 2, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->qtynm ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->totnm ?? 0, 2, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->qtyob ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->totob ?? 0, 2, ',', '.') }}</td>
																			<td>{{ isset($item->tgl) ? date('d/m/Y', strtotime($item->tgl)) : '' }}</td>
																		</tr>
																	@endforeach
																</tbody>
																<tfoot>
																	<tr style="background-color: #f8f9fa; font-weight: bold;">
																		<td colspan="5" class="text-center">TOTAL</td>
																		<td class="text-right">
																			{{ number_format(collect($hasilPenjualanPH)->sum('qtylaku'), 0, ',', '.') }}
																		</td>
																		<td class="text-right">
																			{{ number_format(collect($hasilPenjualanPH)->sum('totlaku'), 2, ',', '.') }}
																		</td>
																		<td class="text-right">
																			{{ number_format(collect($hasilPenjualanPH)->sum('qtynm'), 0, ',', '.') }}
																		</td>
																		<td class="text-right">
																			{{ number_format(collect($hasilPenjualanPH)->sum('totnm'), 2, ',', '.') }}
																		</td>
																		<td class="text-right">
																			{{ number_format(collect($hasilPenjualanPH)->sum('qtyob'), 0, ',', '.') }}
																		</td>
																		<td class="text-right">
																			{{ number_format(collect($hasilPenjualanPH)->sum('totob'), 2, ',', '.') }}
																		</td>
																		<td></td>
																	</tr>
																</tfoot>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															Silakan pilih cabang dan periode untuk menampilkan data penjualan PH.
														</div>
													@endif
												</div>
											</div>
										</div>

										<!-- PB1 Tab -->
										<div class="tab-pane fade" id="pb1" role="tabpanel" aria-labelledby="pb1-tab">
											<div class="pt-3">
												<div class="report-content col-md-12">
													@if (!empty($hasilPB1))
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="tabelPB1">
																<thead>
																	<tr>
																		<th>Tanggal</th>
																		<th>Penjualan</th>
																		<th>PB1</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilPB1 as $item)
																		<tr>
																			<td>{{ isset($item->tgl) ? date('d/m/Y', strtotime($item->tgl)) : '' }}</td>
																			<td class="text-right">{{ number_format($item->penjualan ?? 0, 2, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->PB1 ?? 0, 2, ',', '.') }}</td>
																		</tr>
																	@endforeach
																</tbody>
																<tfoot>
																	<tr style="background-color: #f8f9fa; font-weight: bold;">
																		<td class="text-center">TOTAL</td>
																		<td class="text-right">
																			{{ number_format(collect($hasilPB1)->sum('penjualan'), 2, ',', '.') }}
																		</td>
																		<td class="text-right">
																			{{ number_format(collect($hasilPB1)->sum('PB1'), 2, ',', '.') }}
																		</td>
																	</tr>
																</tfoot>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															Silakan pilih cabang dan periode untuk menampilkan data PB1.
														</div>
													@endif
												</div>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal Summary -->
	<div class="modal fade" id="summaryModal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Summary Penjualan PH</h5>
					<button type="button" class="close" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<div class="modal-body" id="summaryContent">
					<div class="row">
						<div class="col-md-6">
							<div class="card">
								<div class="card-body text-center">
									<h3>{{ !empty($hasilPenjualanPH) ? count($hasilPenjualanPH) : 0 }}</h3>
									<p>Total Item</p>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card">
								<div class="card-body text-center">
									<h3>Rp {{ !empty($hasilPenjualanPH) ? number_format(collect($hasilPenjualanPH)->sum('totlaku'), 2, ',', '.') : '0,00' }}</h3>
									<p>Total Penjualan</p>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card">
								<div class="card-body text-center">
									<h3>{{ !empty($hasilPenjualanPH) ? number_format(collect($hasilPenjualanPH)->sum('qtylaku'), 0, ',', '.') : '0' }}</h3>
									<p>Total Qty Laku</p>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card">
								<div class="card-body text-center">
									<h3>Rp {{ !empty($hasilPB1) ? number_format(collect($hasilPB1)->sum('PB1'), 2, ',', '.') : '0,00' }}</h3>
									<p>Total PB1</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script>
		// Tab functionality
		$(document).ready(function() {
			// Initialize Bootstrap tabs
			$('#reportTabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');
			});

			// Save active tab to localStorage
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				localStorage.setItem('activePenjualanPHTab', $(e.target).attr('href'));
			});

			// Restore active tab from localStorage
			var activeTab = localStorage.getItem('activePenjualanPHTab');
			if (activeTab) {
				$('#reportTabs a[href="' + activeTab + '"]').tab('show');
			}

			// Auto-resize table on tab change
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				$.fn.dataTable.tables({
					visible: true,
					api: true
				}).columns.adjust();
			});

			// Initialize DataTable for Penjualan if data exists
			@if (!empty($hasilPenjualanPH))
				$('#tabelPenjualan').DataTable({
					pageLength: 25,
					searching: true,
					ordering: true,
					responsive: true,
					scrollX: true,
					dom: 'Blfrtip',
					buttons: [{
						extend: 'collection',
						text: 'Export',
						buttons: ['copy', 'excel', 'csv', 'pdf', 'print']
					}],
					columnDefs: [{
						className: 'dt-right',
						targets: [5, 6, 7, 8, 9, 10] // Numeric columns
					}],
					language: {
						url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
					},
					footerCallback: function(row, data, start, end, display) {
						var api = this.api();

						// Calculate column totals
						var qtylaku = api.column(5, {
							page: 'current'
						}).data().reduce(function(a, b) {
							return parseFloat(a) + parseFloat(b.replace(/[,.]/g, ''));
						}, 0);

						var totlaku = api.column(6, {
							page: 'current'
						}).data().reduce(function(a, b) {
							return parseFloat(a) + parseFloat(b.replace(/[,.]/g, ''));
						}, 0);

						// Update footer
						$(api.column(5).footer()).html(qtylaku.toLocaleString('id-ID'));
						$(api.column(6).footer()).html(totlaku.toLocaleString('id-ID', {
							minimumFractionDigits: 2
						}));
					}
				});
			@endif

			// Initialize DataTable for PB1 if data exists
			@if (!empty($hasilPB1))
				$('#tabelPB1').DataTable({
					pageLength: 25,
					searching: true,
					ordering: true,
					responsive: true,
					scrollX: true,
					dom: 'Blfrtip',
					buttons: [{
						extend: 'collection',
						text: 'Export',
						buttons: ['copy', 'excel', 'csv', 'pdf', 'print']
					}],
					columnDefs: [{
						className: 'dt-right',
						targets: [1, 2] // Numeric columns
					}],
					language: {
						url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
					}
				});
			@endif
		});

		// Reset Filter Function
		function resetFilter() {
			$('#cbg').val('');
			$('#periode').val('');
			$('#tgl1').val('');
			$('#tgl2').val('');

			window.location.href = '{{ route('rpenjualanph') }}';
		}

		// Print Report Function
		function cetakReport() {
			var cbg = $('#cbg').val();
			var periode = $('#periode').val();
			var tgl1 = $('#tgl1').val();
			var tgl2 = $('#tgl2').val();

			if (!cbg || !periode) {
				alert('Cabang dan periode harus diisi untuk cetak laporan');
				return;
			}

			var url = '{{ route('jasper-penjualanph-report') }}';
			var form = $('<form>', {
				'method': 'POST',
				'action': url,
				'target': '_blank'
			});

			// Add CSRF token
			form.append($('<input>', {
				'type': 'hidden',
				'name': '_token',
				'value': '{{ csrf_token() }}'
			}));

			// Add parameters
			form.append($('<input>', {
				'type': 'hidden',
				'name': 'cbg',
				'value': cbg
			}));
			form.append($('<input>', {
				'type': 'hidden',
				'name': 'periode',
				'value': periode
			}));
			form.append($('<input>', {
				'type': 'hidden',
				'name': 'tgl1',
				'value': tgl1
			}));
			form.append($('<input>', {
				'type': 'hidden',
				'name': 'tgl2',
				'value': tgl2
			}));

			$('body').append(form);
			form.submit();
			form.remove();
		}

		// Export to Excel Function
		function exportExcel() {
			var cbg = $('#cbg').val();
			var periode = $('#periode').val();
			var tgl1 = $('#tgl1').val();
			var tgl2 = $('#tgl2').val();

			if (!cbg || !periode) {
				alert('Cabang dan periode harus diisi untuk export excel');
				return;
			}

			// Show loading
			$('#btnFilter').html('<i class="fas fa-spinner fa-spin mr-1"></i>Exporting...');
			$('#btnFilter').prop('disabled', true);

			$.ajax({
				url: '{{ url('export-penjualanph-excel') }}',
				method: 'GET',
				data: {
					cbg: cbg,
					periode: periode,
					tgl1: tgl1,
					tgl2: tgl2
				},
				success: function(response) {
					if (response.success) {
						alert('Export berhasil! File: ' + response.filename);
					} else {
						alert(response.message || 'Export gagal');
					}
				},
				error: function(xhr) {
					console.error('Error:', xhr);
					alert('Terjadi kesalahan saat export');
				},
				complete: function() {
					$('#btnFilter').html('<i class="fas fa-search mr-1"></i>Filter');
					$('#btnFilter').prop('disabled', false);
				}
			});
		}

		// Show Summary Modal
		function showSummary() {
			$('#summaryModal').modal('show');
		}

		// Form validation
		$('#reportForm').on('submit', function(e) {
			var cbg = $('#cbg').val();
			var periode = $('#periode').val();

			if (!cbg || !periode) {
				e.preventDefault();
				alert('Cabang dan periode harus diisi');
				return false;
			}

			// Show loading
			$('#btnFilter').html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
			$('#btnFilter').prop('disabled', true);
		});

		// Auto-load data when cbg and periode are selected
		$('#cbg, #periode').on('change', function() {
			var cbg = $('#cbg').val();
			var periode = $('#periode').val();

			if (cbg && periode) {
				// Set default dates based on periode if not manually set
				if (!$('#tgl1').val() || !$('#tgl2').val()) {
					var periodParts = periode.split('/');
					if (periodParts.length === 2) {
						var month = periodParts[0];
						var year = periodParts[1];
						var startDate = year + '-' + month.padStart(2, '0') + '-01';
						var endDate = new Date(year, month, 0).toISOString().split('T')[0];

						$('#tgl1').val(startDate);
						$('#tgl2').val(endDate);
					}
				}
			}
		});

		// Helper function to format numbers
		function formatNumber(num) {
			return Number(num).toLocaleString('id-ID');
		}

		// Helper function to format currency
		function formatCurrency(num) {
			return Number(num).toLocaleString('id-ID', {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2
			});
		}
	</script>
@endsection
