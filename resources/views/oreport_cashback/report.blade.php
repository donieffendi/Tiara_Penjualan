@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Cetak Ulang Cashback</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Cetak Ulang Cashback</li>
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
								<!-- Filter Section -->
								<form method="GET" action="{{ route('get-cetakulangcashback-report') }}" id="cashbackForm">
									@csrf
									<div class="row align-items-end mb-3">
										<div class="col-md-2 mb-2">
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

										<div class="col-md-2 mb-2">
											<label for="tgl1">Tanggal Dari</label>
											<input type="date" name="tgl1" id="tgl1" class="form-control" value="{{ session()->get('filter_tgl1', date('Y-m-d')) }}"
												required>
										</div>

										<div class="col-md-2 mb-2">
											<label for="tgl2">Tanggal Sampai</label>
											<input type="date" name="tgl2" id="tgl2" class="form-control" value="{{ session()->get('filter_tgl2', date('Y-m-d')) }}"
												required>
										</div>

										<div class="col-md-2 mb-2">
											<label for="kodec">Kode Member</label>
											<input type="text" name="kodec" id="kodec" class="form-control" placeholder="Cari member..."
												value="{{ session()->get('filter_kodec') }}">
										</div>

										<div class="col-md-2 mb-2">
											<label for="no_bukti">No Bukti</label>
											<input type="text" name="no_bukti" id="no_bukti" class="form-control" placeholder="Cari no bukti..."
												value="{{ session()->get('filter_no_bukti') }}">
										</div>

										<div class="col-md-2 mb-2 text-right">
											<button class="btn btn-primary btn-block mb-1" type="submit" name="action" value="filter">
												<i class="fas fa-search mr-1"></i>Proses
											</button>
											<button class="btn btn-danger btn-block" type="button" onclick="resetForm()">
												<i class="fas fa-undo mr-1"></i>Reset
											</button>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-md-12 text-center">
											<button class="btn btn-success mr-1" type="submit" name="action" value="cetak" formaction="{{ url('jasper-cetakulangcashback-report') }}"
												formmethod="POST" formtarget="_blank">
												<i class="fas fa-print mr-1"></i>Cetak Voucher
											</button>
											<button class="btn btn-info mr-1" type="button" onclick="exportData('excel')">
												<i class="fas fa-file-excel mr-1"></i>Export Excel
											</button>
											<button class="btn btn-warning mr-1" type="button" onclick="showThermalPrint()">
												<i class="fas fa-receipt mr-1"></i>Thermal Print
											</button>
											<button class="btn btn-secondary" type="button" onclick="showSummary()">
												<i class="fas fa-chart-bar mr-1"></i>Summary
											</button>
										</div>
									</div>

									<!-- Active Filter Display -->
									@if (session()->get('filter_cbg'))
										<div class="row mb-3">
											<div class="col-12">
												<div class="alert alert-info">
													<strong>Filter Aktif:</strong>
													Cabang: {{ session()->get('filter_cbg') }} |
													Periode: {{ session()->get('filter_tgl1') }} s/d {{ session()->get('filter_tgl2') }}
													@if (session()->get('filter_kodec'))
														| Member: {{ session()->get('filter_kodec') }}
													@endif
													@if (session()->get('filter_no_bukti'))
														| No Bukti: {{ session()->get('filter_no_bukti') }}
													@endif
												</div>
											</div>
										</div>
									@endif
								</form>

								<!-- Data Table Section -->
								<div class="report-content">
									@if ($hasilDuplicate && count($hasilDuplicate) > 0)
										<?php
										KoolDataTables::create([
										    'dataSource' => $hasilDuplicate,
										    'name' => 'cashbackTable',
										    'fastRender' => true,
										    'fixedHeader' => true,
										    'scrollX' => true,
										    'showFooter' => true,
										    'columns' => [
										        'KODEC' => [
										            'label' => 'Member',
										            'width' => '100px',
										        ],
										        'NAMAC' => [
										            'label' => 'Nama Member',
										            'width' => '200px',
										        ],
										        'KSR' => [
										            'label' => 'KSR',
										            'width' => '60px',
										        ],
										        'TGL' => [
										            'label' => 'TGL',
										            'type' => 'datetime',
										            'format' => 'd/m/Y H:i',
										            'width' => '120px',
										        ],
										        'STIKER' => [
										            'label' => 'STIKER',
										            'width' => '80px',
										        ],
										        'TYPE' => [
										            'label' => 'TYPE',
										            'width' => '80px',
										        ],
										        'JUMLAH' => [
										            'label' => 'JUMLAH',
										            'type' => 'number',
										            'decimals' => 0,
										            'decimalPoint' => '.',
										            'thousandSeparator' => ',',
										            'width' => '120px',
										        ],
										        'CASHBACK' => [
										            'label' => 'CASHBACK',
										            'type' => 'number',
										            'decimals' => 0,
										            'decimalPoint' => '.',
										            'thousandSeparator' => ',',
										            'width' => '120px',
										        ],
										        'NO_BUKTI' => [
										            'label' => 'No Bukti',
										            'width' => '120px',
										        ],
										    ],
										    'cssClass' => [
										        'table' => 'table table-hover table-striped table-bordered compact',
										        'th' => 'label-title',
										        'td' => 'detail',
										    ],
										    'options' => [
										        'columnDefs' => [
										            [
										                'className' => 'dt-right',
										                'targets' => [6, 7], // JUMLAH, CASHBACK columns
										            ],
										            [
										                'className' => 'dt-center',
										                'targets' => [2, 3, 4, 5, 8], // KSR, TGL, STIKER, TYPE, NO_BUKTI columns
										            ],
										        ],
										        'order' => [[0, 'asc']], // Order by Member
										        'paging' => true,
										        'pageLength' => 25,
										        'searching' => true,
										        'colReorder' => true,
										        'select' => true,
										        'responsive' => true,
										        'dom' => 'Blfrtip',
										        'buttons' => [
										            [
										                'extend' => 'collection',
										                'text' => 'Export',
										                'buttons' => [
										                    [
										                        'extend' => 'copy',
										                        'text' => 'Copy',
										                    ],
										                    [
										                        'extend' => 'excel',
										                        'text' => 'Excel',
										                        'title' => 'Report Cetak Ulang Cashback',
										                    ],
										                    [
										                        'extend' => 'csv',
										                        'text' => 'CSV',
										                    ],
										                    [
										                        'extend' => 'pdf',
										                        'text' => 'PDF',
										                        'orientation' => 'landscape',
										                        'pageSize' => 'A4',
										                    ],
										                    [
										                        'extend' => 'print',
										                        'text' => 'Print',
										                    ],
										                ],
										            ],
										        ],
										        'lengthMenu' => [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
										        'language' => [
										            'lengthMenu' => 'Tampilkan _MENU_ data per halaman',
										            'zeroRecords' => 'Data tidak ditemukan',
										            'info' => 'Menampilkan halaman _PAGE_ dari _PAGES_',
										            'infoEmpty' => 'Tidak ada data tersedia',
										            'infoFiltered' => '(difilter dari _MAX_ total data)',
										            'search' => 'Cari:',
										            'paginate' => [
										                'first' => 'Pertama',
										                'last' => 'Terakhir',
										                'next' => 'Selanjutnya',
										                'previous' => 'Sebelumnya',
										            ],
										        ],
										        'footerCallback' => 'function ( row, data, start, end, display ) {
																														            var api = this.api(), data;

																														            // Total JUMLAH
																														            var totalJumlah = api.column(6).data().reduce(function (a, b) {
																														                return parseFloat(a) + parseFloat(b);
																														            }, 0);

																														            // Total CASHBACK
																														            var totalCashback = api.column(7).data().reduce(function (a, b) {
																														                return parseFloat(a) + parseFloat(b);
																														            }, 0);

																														            // Update footer
																														            $(api.column(5).footer()).html("Total:");
																														            $(api.column(6).footer()).html(totalJumlah.toLocaleString("id-ID"));
																														            $(api.column(7).footer()).html(totalCashback.toLocaleString("id-ID"));
																														        }',
										    ],
										]);
										?>
									@elseif(request()->has('action') && request()->get('action') == 'filter')
										<div class="alert alert-warning text-center">
											<i class="fas fa-exclamation-triangle mr-2"></i>
											Tidak ada data cashback ditemukan untuk filter yang dipilih.
										</div>
									@else
										<div class="alert alert-info text-center">
											<i class="fas fa-info-circle mr-2"></i>
											Silakan pilih cabang dan periode tanggal untuk menampilkan data cashback.
										</div>
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Summary Modal -->
	<div class="modal fade" id="summaryModal" tabindex="-1" role="dialog" aria-labelledby="summaryModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="summaryModalLabel">Summary Cashback</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" id="summaryContent">
					<div class="text-center">
						<i class="fas fa-spinner fa-spin"></i> Loading...
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Thermal Print Modal -->
	<div class="modal fade" id="thermalModal" tabindex="-1" role="dialog" aria-labelledby="thermalModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-md" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="thermalModalLabel">Thermal Print Voucher</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form id="thermalForm">
						<div class="form-group">
							<label for="thermalNoBukti">No Bukti:</label>
							<input type="text" class="form-control" id="thermalNoBukti" placeholder="Masukkan no bukti..." required>
						</div>
					</form>
					<div id="thermalContent" style="display: none;">
						<div class="thermal-preview border p-3" style="font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.2;">
							<!-- Thermal print content will be loaded here -->
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" onclick="loadThermalData()">
						<i class="fas fa-search mr-1"></i>Preview
					</button>
					<button type="button" class="btn btn-success" onclick="printThermal()" style="display: none;" id="btnPrintThermal">
						<i class="fas fa-print mr-1"></i>Print
					</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('javascripts')
	<script>
		$(document).ready(function() {
			// Auto-resize table on window resize
			$(window).on('resize', function() {
				if ($.fn.DataTable.isDataTable('#cashbackTable')) {
					$('#cashbackTable').DataTable().columns.adjust().responsive.recalc();
				}
			});

			// Form validation
			$('#cashbackForm').on('submit', function(e) {
				var cbg = $('#cbg').val();
				var tgl1 = $('#tgl1').val();
				var tgl2 = $('#tgl2').val();

				if (!cbg) {
					alert('Harap pilih cabang terlebih dahulu');
					e.preventDefault();
					return false;
				}

				if (!tgl1 || !tgl2) {
					alert('Harap isi periode tanggal');
					e.preventDefault();
					return false;
				}

				// Show loading for filter action
				if ($('input[name="action"]').val() === 'filter') {
					$('button[name="action"][value="filter"]').html('<i class="fas fa-spinner fa-spin mr-1"></i>Processing...');
					$('button[name="action"][value="filter"]').prop('disabled', true);
				}
			});

			// Enter key handling for kodec
			$('#kodec').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					$('#cashbackForm').find('button[name="action"][value="filter"]').click();
				}
			});

			// Enter key handling for no_bukti
			$('#no_bukti').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					$('#cashbackForm').find('button[name="action"][value="filter"]').click();
				}
			});

			// Clear kodec when focusing on no_bukti
			$('#no_bukti').on('focus', function() {
				if ($(this).val() === '') {
					$('#kodec').val('');
				}
			});

			// Clear no_bukti when focusing on kodec
			$('#kodec').on('focus', function() {
				if ($(this).val() === '') {
					$('#no_bukti').val('');
				}
			});
		});

		// Reset form function
		function resetForm() {
			window.location.href = '{{ route('rcetakulangcashback') }}';
		}

		// Export functions
		function exportData(format) {
			var cbg = $('#cbg').val();
			var tgl1 = $('#tgl1').val();
			var tgl2 = $('#tgl2').val();

			if (!cbg || !tgl1 || !tgl2) {
				alert('Harap pilih cabang dan periode tanggal terlebih dahulu');
				return;
			}

			if (typeof window.cashbackTable !== 'undefined') {
				switch (format) {
					case 'excel':
						window.cashbackTable.button('.buttons-excel').trigger();
						break;
					case 'pdf':
						window.cashbackTable.button('.buttons-pdf').trigger();
						break;
					case 'csv':
						window.cashbackTable.button('.buttons-csv').trigger();
						break;
					case 'print':
						window.cashbackTable.button('.buttons-print').trigger();
						break;
					default:
						// Export via server-side
						exportViaServer(format);
				}
			} else {
				exportViaServer(format);
			}
		}

		// Export via server-side
		function exportViaServer(format) {
			var form = $('<form>', {
				'method': 'POST',
				'action': '{{ route('jasper-cetakulangcashback-report') }}',
				'target': '_blank'
			});

			form.append($('<input>', {
				'type': 'hidden',
				'name': '_token',
				'value': '{{ csrf_token() }}'
			}));

			form.append($('<input>', {
				'type': 'hidden',
				'name': 'cbg',
				'value': $('#cbg').val()
			}));

			form.append($('<input>', {
				'type': 'hidden',
				'name': 'tgl1',
				'value': $('#tgl1').val()
			}));

			form.append($('<input>', {
				'type': 'hidden',
				'name': 'tgl2',
				'value': $('#tgl2').val()
			}));

			form.append($('<input>', {
				'type': 'hidden',
				'name': 'kodec',
				'value': $('#kodec').val()
			}));

			form.append($('<input>', {
				'type': 'hidden',
				'name': 'no_bukti',
				'value': $('#no_bukti').val()
			}));

			form.appendTo('body').submit().remove();
		}

		// Show summary function
		function showSummary() {
			var cbg = $('#cbg').val();
			var tgl1 = $('#tgl1').val();
			var tgl2 = $('#tgl2').val();

			if (!cbg || !tgl1 || !tgl2) {
				alert('Harap pilih cabang dan periode tanggal terlebih dahulu');
				return;
			}

			$('#summaryModal').modal('show');
			$('#summaryContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

			// AJAX call to get summary
			$.ajax({
				url: '{{ url('api-get-cetakulangcashback') }}',
				type: 'GET',
				data: {
					cbg: cbg,
					tgl1: tgl1,
					tgl2: tgl2,
					kodec: $('#kodec').val(),
					no_bukti: $('#no_bukti').val()
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						var summaryHtml = '<div class="row">';
						summaryHtml +=
							'<div class="col-md-6"><div class="info-box"><div class="info-box-icon bg-info"><i class="fas fa-receipt"></i></div>';
						summaryHtml += '<div class="info-box-content"><span class="info-box-text">Total Transaksi</span>';
						summaryHtml += '<span class="info-box-number">' + response.data.length.toLocaleString('id-ID') +
							'</span></div></div></div>';

						var totalCashback = response.data.reduce((sum, item) => sum + parseFloat(item.CASHBACK || 0), 0);
						var totalJumlah = response.data.reduce((sum, item) => sum + parseFloat(item.JUMLAH || 0), 0);

						summaryHtml +=
							'<div class="col-md-6"><div class="info-box"><div class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></div>';
						summaryHtml += '<div class="info-box-content"><span class="info-box-text">Total Cashback</span>';
						summaryHtml += '<span class="info-box-number">Rp ' + totalCashback.toLocaleString('id-ID') + '</span></div></div></div>';

						summaryHtml +=
							'<div class="col-md-12"><div class="info-box"><div class="info-box-icon bg-warning"><i class="fas fa-shopping-cart"></i></div>';
						summaryHtml += '<div class="info-box-content"><span class="info-box-text">Total Penjualan</span>';
						summaryHtml += '<span class="info-box-number">Rp ' + totalJumlah.toLocaleString('id-ID') + '</span></div></div></div>';
						summaryHtml += '</div>';

						$('#summaryContent').html(summaryHtml);
					} else {
						$('#summaryContent').html('<div class="alert alert-danger">' + response.error + '</div>');
					}
				},
				error: function(xhr, status, error) {
					$('#summaryContent').html('<div class="alert alert-danger">Error loading summary data</div>');
				}
			});
		}

		// Show thermal print function
		function showThermalPrint() {
			var cbg = $('#cbg').val();

			if (!cbg) {
				alert('Harap pilih cabang terlebih dahulu');
				return;
			}

			$('#thermalModal').modal('show');
			$('#thermalContent').hide();
			$('#btnPrintThermal').hide();
			$('#thermalNoBukti').val('');
		}

		// Load thermal data function
		function loadThermalData() {
			var cbg = $('#cbg').val();
			var noBukti = $('#thermalNoBukti').val();

			if (!noBukti) {
				alert('Harap masukkan no bukti');
				return;
			}

			// AJAX call to get thermal data
			$.ajax({
				url: '{{ url('api-get-thermal-print-cetakulangcashback') }}',
				type: 'GET',
				data: {
					cbg: cbg,
					no_bukti: noBukti
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						var thermalHtml = '';
						thermalHtml += '<div class="text-center">';
						thermalHtml += '<strong>' + response.thermal_data.nama_toko + '</strong><br>';
						thermalHtml += response.thermal_data.alamat_toko + '<br>';
						thermalHtml += '================================<br>';
						thermalHtml += 'VOUCHER CASHBACK<br>';
						thermalHtml += '================================<br>';
						thermalHtml += 'No Bukti : ' + response.thermal_data.no_bukti + '<br>';
						thermalHtml += 'Tanggal  : ' + response.thermal_data.tanggal + '<br>';
						thermalHtml += 'Jam      : ' + response.thermal_data.jam + '<br>';
						thermalHtml += 'Kasir    : ' + response.thermal_data.kasir + '<br>';
						thermalHtml += '--------------------------------<br>';
						thermalHtml += 'Customer : ' + response.thermal_data.customer + '<br>';
						thermalHtml += 'Kode     : ' + response.thermal_data.kode_customer + '<br>';
						thermalHtml += '--------------------------------<br>';
						thermalHtml += 'Total Belanja : Rp ' + parseInt(response.thermal_data.total_belanja).toLocaleString('id-ID') + '<br>';
						thermalHtml += 'CASHBACK      : Rp ' + parseInt(response.thermal_data.cashback_amount).toLocaleString('id-ID') + '<br>';
						thermalHtml += '================================<br>';
						thermalHtml += response.thermal_data.footer_text + '<br>';
						thermalHtml += '================================<br>';
						thermalHtml += '</div>';

						$('.thermal-preview').html(thermalHtml);
						$('#thermalContent').show();
						$('#btnPrintThermal').show();
					} else {
						alert('Error: ' + response.error);
					}
				},
				error: function(xhr, status, error) {
					alert('Error loading thermal data');
				}
			});
		}

		// Print thermal function
		function printThermal() {
			var printContents = $('.thermal-preview').html();
			var printWindow = window.open('', '_blank');
			printWindow.document.write('<html><head><title>Print Voucher Cashback</title>');
			printWindow.document.write('<style>body{font-family:"Courier New",monospace;font-size:12px;line-height:1.2;}</style>');
			printWindow.document.write('</head><body>');
			printWindow.document.write(printContents);
			printWindow.document.write('</body></html>');
			printWindow.document.close();
			printWindow.print();
		}

		// Utility function to format numbers
		function formatNumber(num) {
			return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		}

		// Utility function to format currency
		function formatCurrency(num) {
			return 'Rp ' + formatNumber(num);
		}
	</script>
@endsection
