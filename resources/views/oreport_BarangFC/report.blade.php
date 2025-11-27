@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Barang FC</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Barang FC</li>
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
								<form method="POST" action="{{ url('jasper-barangfc-report') }}" id="reportForm">
									@csrf

									<!-- Global Filters -->
									<div class="row bg-light mb-4 rounded p-3">
										<div class="col-md-3">
											<label for="global_periode">Periode</label>
											<input type="text" name="periode" id="global_periode" class="form-control" placeholder="MM/YYYY" value="{{ date('m/Y') }}" required>
											<small class="form-text text-muted">Format: MM/YYYY (contoh: 01/2024)</small>
										</div>
										<div class="col-md-3">
											<label for="global_cbg">Cabang</label>
											<select name="cbg" id="global_cbg" class="form-control" required>
												<option value="">Pilih Cabang</option>
												@foreach ($cbg as $cabang)
													<option value="{{ $cabang->CBG }}" {{ session()->get('filter_cbg') == $cabang->CBG ? 'selected' : '' }}>
														{{ $cabang->CBG }}
													</option>
												@endforeach
											</select>
										</div>
										<div class="col-md-6 d-flex align-items-end">
											<div class="btn-group" role="group">
												<button class="btn btn-info" type="button" onclick="applyGlobalFilter()">
													<i class="fas fa-filter mr-1"></i>Apply Filter
												</button>
												<button class="btn btn-secondary" type="button" onclick="resetGlobalFilter()">
													<i class="fas fa-redo mr-1"></i>Reset
												</button>
											</div>
										</div>
									</div>

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="card-tab" data-toggle="tab" href="#card" role="tab" aria-controls="card" aria-selected="true">
												<i class="fas fa-id-card mr-1"></i>Card
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="report-sub-tab" data-toggle="tab" href="#report-sub" role="tab" aria-controls="report-sub"
												aria-selected="false">
												<i class="fas fa-chart-bar mr-1"></i>Report per Sub
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="periode-tab" data-toggle="tab" href="#periode" role="tab" aria-controls="periode" aria-selected="false">
												<i class="fas fa-calendar mr-1"></i>Periode
											</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">

										<!-- Card Tab -->
										<div class="tab-pane fade show active" id="card" role="tabpanel" aria-labelledby="card-tab">
											<div class="pt-3">
												<div class="row align-items-end mb-3">
													<div class="col-md-3">
														<label for="card_code">Code</label>
														<input type="text" name="kode_barang" id="card_code" class="form-control" placeholder="Masukkan kode barang"
															onkeypress="if(event.keyCode==13) loadCardData()">
													</div>
													<div class="col-md-3">
														<button class="btn btn-primary mr-1" type="button" onclick="loadCardData()">
															<i class="fas fa-search mr-1"></i>Load Data
														</button>
														<button class="btn btn-warning mr-1" type="button" onclick="printCardReport()">
															<i class="fas fa-print mr-1"></i>Cetak
														</button>
														<button class="btn btn-success" type="button" onclick="exportCardData('excel')">
															<i class="fas fa-file-excel mr-1"></i>Export Excel
														</button>
													</div>
												</div>

												<div class="report-content col-md-12" id="card-result">
													<div class="alert alert-info">
														<i class="fas fa-info-circle mr-2"></i>
														Masukkan periode, cabang, dan kode barang untuk menampilkan kartu stock.
													</div>
												</div>
											</div>
										</div>

										<!-- Report per Sub Tab -->
										<div class="tab-pane fade" id="report-sub" role="tabpanel" aria-labelledby="report-sub-tab">
											<div class="pt-3">
												<div class="row align-items-end mb-3">
													<div class="col-md-3">
														<label for="kasir_dropdown">Kasir</label>
														<select name="kasir" id="kasir_dropdown" class="form-control">
															<option value="">Pilih Kasir</option>
															<!-- Kasir options will be loaded dynamically -->
														</select>
													</div>
													<div class="col-md-3">
														<button class="btn btn-primary mr-1" type="button" onclick="loadSubReport()">
															<i class="fas fa-search mr-1"></i>Load Data
														</button>
														<button class="btn btn-warning mr-1" type="button" onclick="printSubReport()">
															<i class="fas fa-print mr-1"></i>Cetak
														</button>
														<button class="btn btn-success" type="button" onclick="exportSubData('excel')">
															<i class="fas fa-file-excel mr-1"></i>Export Excel
														</button>
													</div>
												</div>

												<div class="report-content col-md-12" id="sub-result">
													<div class="alert alert-info">
														<i class="fas fa-info-circle mr-2"></i>
														Pilih periode, cabang, dan kasir untuk menampilkan laporan per sub.
													</div>
												</div>
											</div>
										</div>

										<!-- Periode Tab -->
										<div class="tab-pane fade" id="periode" role="tabpanel" aria-labelledby="periode-tab">
											<div class="pt-3">
												<div class="row align-items-end mb-3">
													<div class="col-md-2">
														<label for="sub_dari">Dari Sub</label>
														<input type="text" name="sub1" id="sub_dari" class="form-control" placeholder="Sub awal">
													</div>
													<div class="col-md-2">
														<label for="sub_sampai">Sampai Sub</label>
														<input type="text" name="sub2" id="sub_sampai" class="form-control" placeholder="Sub akhir" value="">
													</div>
													<div class="col-md-4">
														<button class="btn btn-primary mr-1" type="button" onclick="loadPeriodeData()">
															<i class="fas fa-search mr-1"></i>Load Data
														</button>
														<button class="btn btn-warning mr-1" type="button" onclick="printPeriodeReport()">
															<i class="fas fa-print mr-1"></i>Cetak
														</button>
														<button class="btn btn-success" type="button" onclick="exportPeriodeData('excel')">
															<i class="fas fa-file-excel mr-1"></i>Export Excel
														</button>
													</div>
												</div>

												<div class="report-content col-md-12" id="periode-result">
													<div class="alert alert-info">
														<i class="fas fa-info-circle mr-2"></i>
														Pilih periode, cabang, dan rentang sub untuk menampilkan laporan periode.
													</div>
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
@endsection

@section('javascripts')
	<script>
		// Global variables
		var currentGlobalPeriode = '';
		var currentGlobalCbg = '';

		// Initialize on document ready
		$(document).ready(function() {
			// Initialize Bootstrap tabs
			$('#reportTabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');
			});

			// Save active tab to localStorage
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				localStorage.setItem('activeTabBarangFC', $(e.target).attr('href'));
			});

			// Restore active tab from localStorage
			var activeTab = localStorage.getItem('activeTabBarangFC');
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

			// Auto-format periode input
			$('#global_periode').on('input', function() {
				var value = this.value.replace(/\D/g, ''); // Remove non-digits
				if (value.length >= 2) {
					this.value = value.substring(0, 2) + '/' + value.substring(2, 6);
				}
			});

			// Load kasir options when tab is shown
			$('#report-sub-tab').on('shown.bs.tab', function() {
				loadKasirOptions();
			});
		});

		// Global filter functions
		function applyGlobalFilter() {
			currentGlobalPeriode = $('#global_periode').val();
			currentGlobalCbg = $('#global_cbg').val();

			if (!currentGlobalPeriode || !currentGlobalCbg) {
				alert('Periode dan Cabang harus diisi');
				return;
			}

			// Update all tab forms with global values
			updateTabFilters();

			// Show success message
			toastr.success('Filter global berhasil diterapkan');
		}

		function resetGlobalFilter() {
			$('#global_periode').val('{{ date('m/Y') }}');
			$('#global_cbg').val('');
			currentGlobalPeriode = '';
			currentGlobalCbg = '';

			// Clear all tab results
			$('#card-result, #sub-result, #periode-result').html(
				'<div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>Silakan terapkan filter global terlebih dahulu.</div>'
			);

			toastr.info('Filter global telah direset');
		}

		function updateTabFilters() {
			// This function updates all tabs with global filter values
			// Individual tabs will use these global values
		}

		// Card Tab Functions
		function loadCardData() {
			if (!currentGlobalPeriode || !currentGlobalCbg) {
				alert('Silakan terapkan filter global (periode dan cabang) terlebih dahulu');
				return;
			}

			var code = $('#card_code').val();
			if (!code) {
				alert('Code harus diisi');
				return;
			}

			$.ajax({
				url: '{{ route('get-barangfc-report') }}',
				method: 'GET',
				data: {
					report_type: 'kartu_stock',
					periode: currentGlobalPeriode,
					cbg: currentGlobalCbg,
					kode_barang: code
				},
				beforeSend: function() {
					$('#card-result').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
				},
				success: function(response) {
					if (response.hasilKartu && response.hasilKartu.length > 0) {
						var html = buildCardTable(response.hasilKartu);
						$('#card-result').html(html);
						initializeDataTable('#card-table');
					} else {
						$('#card-result').html(
							'<div class="alert alert-warning">Tidak ada data kartu stock untuk parameter yang dipilih</div>'
						);
					}
				},
				error: function(xhr) {
					console.error('Error:', xhr);
					$('#card-result').html('<div class="alert alert-danger">Error loading data</div>');
				}
			});
		}

		function buildCardTable(data) {
			var html = '<div class="table-responsive">';
			html += '<table class="table table-striped table-bordered" id="card-table">';
			html += '<thead><tr>';
			html += '<th>Kode</th><th>Nama</th><th>Tanggal</th><th>Faktur</th>';
			html += '<th class="text-right">Awal</th><th class="text-right">Masuk</th><th class="text-right">Keluar</th>';
			html += '<th class="text-right">Lain</th><th class="text-right">Saldo</th>';
			html += '</tr></thead><tbody>';

			$.each(data, function(i, item) {
				html += '<tr>';
				html += '<td>' + (item.KD_BRG || '') + '</td>';
				html += '<td>' + (item.NA_BRG || '') + '</td>';
				html += '<td>' + (item.TGL || '') + '</td>';
				html += '<td>' + (item.no_bukti || '') + '</td>';
				html += '<td class="text-right">' + formatNumber(item.awal || 0) + '</td>';
				html += '<td class="text-right">' + formatNumber(item.MASUK || 0) + '</td>';
				html += '<td class="text-right">' + formatNumber(item.KELUAR || 0) + '</td>';
				html += '<td class="text-right">' + formatNumber(item.LAIN || 0) + '</td>';
				html += '<td class="text-right"><strong>' + formatNumber(item.SALDO || 0) + '</strong></td>';
				html += '</tr>';
			});

			html += '</tbody></table></div>';
			return html;
		}

		// Report per Sub Functions
		function loadKasirOptions() {
			if (!currentGlobalPeriode || !currentGlobalCbg) {
				return;
			}

			// Load kasir options based on current filters
			// This would typically come from an API endpoint
			// For now, adding some sample options
			var kasirOptions = '<option value="">Pilih Kasir</option>';
			kasirOptions += '<option value="001">Kasir 001</option>';
			kasirOptions += '<option value="002">Kasir 002</option>';
			kasirOptions += '<option value="003">Kasir 003</option>';

			$('#kasir_dropdown').html(kasirOptions);
		}

		function loadSubReport() {
			if (!currentGlobalPeriode || !currentGlobalCbg) {
				alert('Silakan terapkan filter global (periode dan cabang) terlebih dahulu');
				return;
			}

			var kasir = $('#kasir_dropdown').val();
			if (!kasir) {
				alert('Kasir harus dipilih');
				return;
			}

			$.ajax({
				url: '{{ route('get-barangfc-report') }}',
				method: 'GET',
				data: {
					report_type: 'penjualan_kasir',
					periode: currentGlobalPeriode,
					cbg: currentGlobalCbg,
					kasir: kasir
				},
				beforeSend: function() {
					$('#sub-result').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
				},
				success: function(response) {
					if (response.hasilSub && response.hasilSub.length > 0) {
						var html = buildSubTable(response.hasilSub);
						$('#sub-result').html(html);
						initializeDataTable('#sub-table');
					} else {
						$('#sub-result').html(
							'<div class="alert alert-warning">Tidak ada data penjualan kasir untuk parameter yang dipilih</div>'
						);
					}
				},
				error: function(xhr) {
					console.error('Error:', xhr);
					$('#sub-result').html('<div class="alert alert-danger">Error loading data</div>');
				}
			});
		}

		function buildSubTable(data) {
			var html = '<div class="table-responsive">';
			html += '<table class="table table-striped table-bordered" id="sub-table">';
			html += '<thead><tr>';
			html += '<th>Sub</th><th>Kelompok</th><th>Stand</th><th>Type</th>';
			html += '<th class="text-right">Qty</th><th class="text-right">Total</th>';
			html += '</tr></thead><tbody>';

			$.each(data, function(i, item) {
				html += '<tr>';
				html += '<td>' + (item.SUB2 || '') + '</td>';
				html += '<td>' + (item.KELOMPOK || '') + '</td>';
				html += '<td>' + (item.STAND || '') + '</td>';
				html += '<td>' + (item.TYPE || '') + '</td>';
				html += '<td class="text-right">' + formatNumber(item.qty || 0) + '</td>';
				html += '<td class="text-right">' + formatCurrency(item.total || 0) + '</td>';
				html += '</tr>';
			});

			html += '</tbody></table></div>';
			return html;
		}

		// Periode Tab Functions
		function loadPeriodeData() {
			if (!currentGlobalPeriode || !currentGlobalCbg) {
				alert('Silakan terapkan filter global (periode dan cabang) terlebih dahulu');
				return;
			}

			var sub1 = $('#sub_dari').val() || '';
			var sub2 = $('#sub_sampai').val() || 'ZZZ';

			$.ajax({
				url: '{{ route('get-barangfc-report') }}',
				method: 'GET',
				data: {
					report_type: 'stock_barang',
					cbg: currentGlobalCbg,
					sub1: sub1,
					sub2: sub2
				},
				beforeSend: function() {
					$('#periode-result').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
				},
				success: function(response) {
					if (response.hasilStock && response.hasilStock.length > 0) {
						var html = buildPeriodeTable(response.hasilStock);
						$('#periode-result').html(html);
						initializeDataTable('#periode-table');
					} else {
						$('#periode-result').html(
							'<div class="alert alert-warning">Tidak ada data stock barang untuk parameter yang dipilih</div>'
						);
					}
				},
				error: function(xhr) {
					console.error('Error:', xhr);
					$('#periode-result').html('<div class="alert alert-danger">Error loading data</div>');
				}
			});
		}

		function buildPeriodeTable(data) {
			var html = '<div class="table-responsive">';
			html += '<table class="table table-striped table-bordered" id="periode-table">';
			html += '<thead><tr>';
			html += '<th>Sub</th><th>Item</th><th>Kode</th><th>Nama Barang</th><th>Kemasan</th>';
			html += '<th>Supplier</th><th class="text-right">Harga Beli</th><th class="text-right">Harga Jual</th>';
			html += '<th>Dis</th><th>TKP</th><th>Barcode</th>';
			html += '<th class="text-right">Awal</th><th class="text-right">Masuk</th><th class="text-right">Keluar</th>';
			html += '<th class="text-right">Lain2</th><th class="text-right">Saldo</th>';
			html += '</tr></thead><tbody>';

			$.each(data, function(i, item) {
				html += '<tr>';
				html += '<td>' + (item.sub || '') + '</td>';
				html += '<td>' + (item.KDBAR || '') + '</td>';
				html += '<td>' + (item.KD_BRG || '') + '</td>';
				html += '<td>' + (item.NA_BRG || '') + '</td>';
				html += '<td>' + (item.KET_UK || '') + '</td>';
				html += '<td>' + (item.SUPP || '') + '</td>';
				html += '<td class="text-right">' + formatCurrency(item.HB || 0) + '</td>';
				html += '<td class="text-right">' + formatCurrency(item.HJ || 0) + '</td>';
				html += '<td>' + (item.DIS || '') + '</td>';
				html += '<td>' + (item.TKP || '') + '</td>';
				html += '<td>' + (item.BARCODE || '') + '</td>';
				html += '<td class="text-right">' + formatNumber(item.AW || 0) + '</td>';
				html += '<td class="text-right">' + formatNumber(item.MA || 0) + '</td>';
				html += '<td class="text-right">' + formatNumber(item.KE || 0) + '</td>';
				html += '<td class="text-right">' + formatNumber(item.LN || 0) + '</td>';
				html += '<td class="text-right"><strong>' + formatNumber(item.saldo || 0) + '</strong></td>';
				html += '</tr>';
			});

			html += '</tbody></table></div>';
			return html;
		}

		// Print Functions
		function printCardReport() {
			if (!currentGlobalPeriode || !currentGlobalCbg) {
				alert('Terapkan filter global terlebih dahulu');
				return;
			}

			var code = $('#card_code').val();
			if (!code) {
				alert('Code harus diisi');
				return;
			}

			var url = '{{ url('jasper-barangfc-report') }}?report_type=kartu_stock&periode=' + currentGlobalPeriode +
				'&cbg=' + currentGlobalCbg + '&kode_barang=' + code + '&cetak=1';
			window.open(url, '_blank');
		}

		function printSubReport() {
			if (!currentGlobalPeriode || !currentGlobalCbg) {
				alert('Terapkan filter global terlebih dahulu');
				return;
			}

			var kasir = $('#kasir_dropdown').val();
			if (!kasir) {
				alert('Kasir harus dipilih');
				return;
			}

			var url = '{{ url('jasper-barangfc-report') }}?report_type=penjualan_kasir&periode=' + currentGlobalPeriode +
				'&cbg=' + currentGlobalCbg + '&kasir=' + kasir + '&cetak=1';
			window.open(url, '_blank');
		}

		function printPeriodeReport() {
			if (!currentGlobalPeriode || !currentGlobalCbg) {
				alert('Terapkan filter global terlebih dahulu');
				return;
			}

			var sub1 = $('#sub_dari').val() || '';
			var sub2 = $('#sub_sampai').val() || 'ZZZ';

			var url = '{{ url('jasper-barangfc-report') }}?report_type=stock_barang&cbg=' + currentGlobalCbg +
				'&sub1=' + sub1 + '&sub2=' + sub2 + '&cetak=1';
			window.open(url, '_blank');
		}

		// Export Functions
		function exportCardData(format) {
			exportDataTable('#card-table', format);
		}

		function exportSubData(format) {
			exportDataTable('#sub-table', format);
		}

		function exportPeriodeData(format) {
			exportDataTable('#periode-table', format);
		}

		function exportDataTable(tableSelector, format) {
			var table = $(tableSelector).DataTable();
			if (table) {
				switch (format) {
					case 'excel':
						table.button('.buttons-excel').trigger();
						break;
					case 'pdf':
						table.button('.buttons-pdf').trigger();
						break;
					case 'csv':
						table.button('.buttons-csv').trigger();
						break;
					default:
						alert('Format export tidak dikenali');
				}
			} else {
				alert('Tidak ada data untuk di-export');
			}
		}

		// Helper Functions
		function formatNumber(num) {
			return Number(num).toLocaleString('id-ID');
		}

		function formatCurrency(num) {
			return 'Rp ' + Number(num).toLocaleString('id-ID', {
				minimumFractionDigits: 2
			});
		}

		function initializeDataTable(selector) {
			$(selector).DataTable({
				pageLength: 25,
				searching: true,
				ordering: true,
				responsive: true,
				dom: 'Blfrtip',
				buttons: ['copy', 'excel', 'csv', 'pdf', 'print'],
				columnDefs: [{
					className: 'text-right',
					targets: '_all'
				}]
			});
		}

		// Enter key handlers
		$('#card_code').on('keypress', function(e) {
			if (e.which === 13) {
				loadCardData();
			}
		});

		// Form validation
		$('#reportForm').on('submit', function(e) {
			if (!currentGlobalPeriode || !currentGlobalCbg) {
				e.preventDefault();
				alert('Filter global (periode dan cabang) harus diterapkan terlebih dahulu');
			}
		});
	</script>
@endsection
