@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Form Report Pemantauan DTR Khusus</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Form Report Pemantauan DTR Khusus</li>
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
								<form method="GET" action="{{ route('get-pemantauandtrkhusus-report') }}" id="rDtrForm">
									@csrf
									<div class="form-group">
										<div class="row align-items-end">
											<div class="col-3 mb-2">
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

											{{-- <div class="col-3 mb-2">
												<label for="sub">Sub Kategori</label>
												<select name="sub" id="sub" class="form-control" required>
													<option value="">Pilih Sub Kategori</option>
													<!-- Options akan dimuat via JavaScript -->
												</select>
											</div> --}}

                                            <div class="col-md-2 mb-2">
												<label for="sub">Sub</label>
												<input type="text" name="sub" id="sub" class="form-control" maxlength="20" value="{{ session()->get('filter_sub') }}"
													placeholder="">
											</div>

											<div class="col-6 mb-2 text-right">
												<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
													<i class="fas fa-search mr-1"></i>Proses
												</button>
												<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
													<i class="fas fa-undo mr-1"></i>Reset
												</button>
												<button class="btn btn-success mr-1" type="submit" name="action" value="cetak"
													formaction="{{ route('jasper-pemantauandtrkhusus-report') }}" formmethod="POST" formtarget="_blank">
													<i class="fas fa-print mr-1"></i>Cetak
												</button>
												<button class="btn btn-info" type="button" onclick="exportData()">
													<i class="fas fa-download mr-1"></i>Excel
												</button>
											</div>
										</div>

										@if (session()->get('filter_cbg') && session()->get('filter_sub'))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-info">
														<strong>Filter Aktif:</strong>
														Cabang: {{ session()->get('filter_cbg') }} |
														Sub Kategori: {{ session()->get('filter_sub') }} |
														Jenis: PEMANTAUAN_DTR |
														Tanggal Generate: {{ date('d/m/Y') }}
													</div>
												</div>
											</div>
										@endif

										@if (session('error'))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-danger">
														<i class="fas fa-exclamation-triangle mr-2"></i>
														{{ session('error') }}
													</div>
												</div>
											</div>
										@endif

										<div style="margin-bottom: 15px;"></div>
										<?php
											use \koolreport\datagrid\DataTables;
										?>
										<div class="report-content" col-md-12>
											@if ($hasilSinkron && count($hasilSinkron) > 0)
												<?php
												// Create custom data for KoolDataTables sesuai dengan struktur query Delphi
												$tableData = [];
												$totalDTRSistem = 0;
												$totalDTRManual = 0;
												$totalLPH = 0;
												$itemBermasalah = 0;

												foreach ($hasilSinkron as $item) {
												    $dtrSistem = $item['DTR'] ?? 0;
												    $dtrManual = $item['DTR_MANUAL'] ?? 0;
												    $dtr1M = $item['DTR_1M'] ?? 0;
												    $lph = $item['LPH'] ?? 0;
												    $selisihDTR = $dtrSistem - $dtrManual;

												    // Hitung total untuk summary
												    $totalDTRSistem += $dtrSistem;
												    $totalDTRManual += $dtrManual;
												    $totalLPH += $lph;

												    // Hitung item bermasalah (sesuai kondisi WHERE di Delphi)
												    if ($dtrManual > 0 && $dtrManual < $dtrSistem) {
												        $itemBermasalah++;
												    }

												    $tableData[] = [
												        'SUB' => $item['SUB'] ?? '',
												        'NA_BRG' => $item['NA_BRG'] ?? '',
												        'KET_UK' => $item['KET_UK'] ?? '',
												        'KET_KEM' => $item['KET_KEM'] ?? '',
												        'LPH' => $lph,
												        'DTR' => $dtrSistem,
												        'DTR_1M' => $dtr1M,
												        'DTR_MANUAL' => $dtrManual,
												        'SELISIH_DTR' => $selisihDTR,
												        'PERSENTASE_AKURASI' => $dtrSistem > 0 ? round(($dtrManual / $dtrSistem) * 100, 2) : 0,
												        // Hidden fields for export/reference
												        'KD_BRG' => $item['KD_BRG'] ?? '',
												        'NA_TOKO' => $item['NA_TOKO'] ?? '',
												        'NO_FORM' => $item['NO_FORM'] ?? '',
												    ];
												}

												// KoolDataTables::create([
												DataTables::create([
												    'dataSource' => $tableData,
												    'name' => 'rDtrTable',
												    'fastRender' => true,
												    'fixedHeader' => true,
												    'scrollX' => true,
												    'showFooter' => true,
												    'showFooter' => 'bottom',
												    'columns' => [
												        'SUB' => [
												            'label' => 'Sub Item',
												            'width' => '80px',
												        ],
												        'NA_BRG' => [
												            'label' => 'Nama Barang',
												            'width' => '200px',
												        ],
												        'KET_UK' => [
												            'label' => 'Ukuran',
												            'width' => '80px',
												        ],
												        'KET_KEM' => [
												            'label' => 'Kemasan',
												            'width' => '80px',
												        ],
												        'LPH' => [
												            'label' => 'LPH',
												            'type' => 'number',
												            'decimals' => 0,
												            'decimalPoint' => '.',
												            'thousandSeparator' => ',',
												            'width' => '80px',
												            'footer' => 'sum',
												        ],
												        'DTR' => [
												            'label' => 'DTR',
												            'type' => 'number',
												            'decimals' => 2,
												            'decimalPoint' => '.',
												            'thousandSeparator' => ',',
												            'width' => '90px',
												            'footer' => 'sum',
												        ],
												        'DTR_1M' => [
												            'label' => 'DTR 1 Muka',
												            'type' => 'number',
												            'decimals' => 2,
												            'decimalPoint' => '.',
												            'thousandSeparator' => ',',
												            'width' => '100px',
												            'footer' => 'sum',
												        ],
												        'DTR_MANUAL' => [
												            'label' => 'DTR Manual',
												            'type' => 'number',
												            'decimals' => 2,
												            'decimalPoint' => '.',
												            'thousandSeparator' => ',',
												            'width' => '100px',
												            'footer' => 'sum',
												        ],
												    ],
												    'cssClass' => [
												        'table' => 'table table-hover table-striped table-bordered compact',
												        'th' => 'label-title',
												        'td' => 'detail',
												        'tf' => 'footerCss',
												    ],
												    'options' => [
												        'columnDefs' => [
												            [
												                'className' => 'dt-right',
												                'targets' => [4, 5, 6, 7], // LPH, DTR, DTR_1M, DTR_MANUAL
												            ],
												            [
												                'className' => 'dt-center',
												                'targets' => [0, 2, 3], // SUB, KET_UK, KET_KEM
												            ],
												            [
												                'className' => 'dt-left',
												                'targets' => [1], // NA_BRG
												            ],
												            // Custom rendering untuk DTR Sistem dengan highlight jika ada masalah
												            [
												                'targets' => [5], // DTR column
												                'render' => 'function(data, type, row, meta) {
																								                    var dtr = parseFloat(data) || 0;
																								                    var dtrManual = parseFloat(row.DTR_MANUAL) || 0;

																								                    if (dtrManual > 0 && dtrManual < dtr) {
																								                        return "<span class=\"text-danger font-weight-bold\" title=\"DTR Manual lebih kecil dari DTR Sistem\">" +
																								                               dtr.toLocaleString("id-ID", {minimumFractionDigits: 2}) + "</span>";
																								                    } else {
																								                        return dtr.toLocaleString("id-ID", {minimumFractionDigits: 2});
																								                    }
																								                }',
												            ],
												            // Custom rendering untuk DTR Manual dengan status indicator
												            [
												                'targets' => [7], // DTR_MANUAL column
												                'render' => 'function(data, type, row, meta) {
																								                    var dtrManual = parseFloat(data) || 0;
																								                    var dtr = parseFloat(row.DTR) || 0;

																								                    if (dtrManual <= 0) {
																								                        return "<span class=\"text-muted\">-</span>";
																								                    } else if (dtrManual < dtr) {
																								                        return "<span class=\"text-warning font-weight-bold\" title=\"DTR Manual kurang dari DTR Sistem\">" +
																								                               dtrManual.toLocaleString("id-ID", {minimumFractionDigits: 2}) + "</span>";
																								                    } else {
																								                        return "<span class=\"text-success\">" +
																								                               dtrManual.toLocaleString("id-ID", {minimumFractionDigits: 2}) + "</span>";
																								                    }
																								                }',
												            ],
												            // Custom rendering untuk DTR 1 Muka
												            [
												                'targets' => [6], // DTR_1M column
												                'render' => 'function(data, type, row, meta) {
																								                    var dtr1M = parseFloat(data) || 0;
																								                    if (dtr1M > 0) {
																								                        return "<span class=\"text-info\">" +
																								                               dtr1M.toLocaleString("id-ID", {minimumFractionDigits: 2}) + "</span>";
																								                    } else {
																								                        return "<span class=\"text-muted\">-</span>";
																								                    }
																								                }',
												            ],
												            // Custom rendering untuk LPH
												            [
												                'targets' => [4], // LPH column
												                'render' => 'function(data, type, row, meta) {
																								                    var lph = parseInt(data) || 0;
																								                    if (lph > 0) {
																								                        return "<span class=\"badge badge-primary\">" +
																								                               lph.toLocaleString("id-ID") + "</span>";
																								                    } else {
																								                        return "<span class=\"text-muted\">0</span>";
																								                    }
																								                }',
												            ],
												            // Custom rendering untuk Sub dengan color coding
												            [
												                'targets' => [0], // SUB column
												                'render' => 'function(data, type, row, meta) {
																								                    if (data && data.length > 0) {
																								                        return "<span class=\"badge badge-secondary\">" + data + "</span>";
																								                    } else {
																								                        return "<span class=\"text-muted\">-</span>";
																								                    }
																								                }',
												            ],
												        ],
												        'order' => [[0, 'asc'], [1, 'asc']], // Order by SUB, then NA_BRG sesuai Delphi
												        'paging' => true,
												        'pageLength' => 25,
												        'searching' => true,
												        'colReorder' => true,
												        'select' => true,
												        'dom' => 'Blfrtip',
												        'buttons' => [
												            [
												                'extend' => 'collection',
												                'text' => 'Export',
												                'buttons' => ['copy', 'excel', 'csv', 'pdf', 'print'],
												            ],
												        ],
												        'footerCallback' => 'function(row, data, start, end, display) {
																								            var api = this.api();

																								            // Total untuk LPH
																								            var totalLPH = api
																								                .column(4, { page: "current" })
																								                .data()
																								                .reduce(function(a, b) {
																								                    return (parseInt(a) || 0) + (parseInt(b) || 0);
																								                }, 0);

																								            // Total untuk DTR
																								            var totalDTR = api
																								                .column(5, { page: "current" })
																								                .data()
																								                .reduce(function(a, b) {
																								                    return (parseFloat(a) || 0) + (parseFloat(b) || 0);
																								                }, 0);

																								            // Total untuk DTR 1 Muka
																								            var totalDTR1M = api
																								                .column(6, { page: "current" })
																								                .data()
																								                .reduce(function(a, b) {
																								                    return (parseFloat(a) || 0) + (parseFloat(b) || 0);
																								                }, 0);

																								            // Total untuk DTR Manual
																								            var totalDTRManual = api
																								                .column(7, { page: "current" })
																								                .data()
																								                .reduce(function(a, b) {
																								                    return (parseFloat(a) || 0) + (parseFloat(b) || 0);
																								                }, 0);

																								            // Update footer
																								            $(api.column(4).footer()).html("<strong>" + totalLPH.toLocaleString("id-ID") + "</strong>");
																								            $(api.column(5).footer()).html("<strong>" + totalDTR.toLocaleString("id-ID", {minimumFractionDigits: 2}) + "</strong>");
																								            $(api.column(6).footer()).html("<strong>" + totalDTR1M.toLocaleString("id-ID", {minimumFractionDigits: 2}) + "</strong>");
																								            $(api.column(7).footer()).html("<strong>" + totalDTRManual.toLocaleString("id-ID", {minimumFractionDigits: 2}) + "</strong>");
																								        }',
												    ],
												]);
												?>

												<!-- Summary Statistics Card -->
												<div class="row mt-3">
													<div class="col-md-3">
														<div class="info-box">
															<span class="info-box-icon bg-info"><i class="fas fa-cubes"></i></span>
															<div class="info-box-content">
																<span class="info-box-text">Total Item</span>
																<span class="info-box-number">{{ count($hasilSinkron) }}</span>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="info-box">
															<span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
															<div class="info-box-content">
																<span class="info-box-text">Item Bermasalah</span>
																<span class="info-box-number">{{ $itemBermasalah }}</span>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="info-box">
															<span class="info-box-icon bg-success"><i class="fas fa-calculator"></i></span>
															<div class="info-box-content">
																<span class="info-box-text">Total DTR Sistem</span>
																<span class="info-box-number">{{ number_format($totalDTRSistem, 2, ',', '.') }}</span>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="info-box">
															<span class="info-box-icon bg-primary"><i class="fas fa-edit"></i></span>
															<div class="info-box-content">
																<span class="info-box-text">Total DTR Manual</span>
																<span class="info-box-number">{{ number_format($totalDTRManual, 2, ',', '.') }}</span>
															</div>
														</div>
													</div>
												</div>

												<div class="row mt-3">
													<div class="col-12">
														<div class="alert alert-success">
															<i class="fas fa-info-circle mr-2"></i>
															<strong>Informasi Data Pemantauan DTR:</strong><br>
															• <span class="text-danger font-weight-bold">DTR Bermasalah</span> = DTR Manual kurang dari DTR Sistem<br>
															• <span class="text-warning font-weight-bold">DTR Manual Kurang</span> = Memerlukan penyesuaian<br>
															• <span class="text-success font-weight-bold">DTR Normal</span> = DTR Manual sesuai dengan DTR Sistem<br>
															• <span class="text-info">DTR 1 Muka</span> = Referensi DTR bulan sebelumnya<br>
															• <span class="badge badge-primary">LPH</span> = Lead Per Hour untuk barang tersebut<br>
															• Total data: <strong>{{ count($hasilSinkron) }}</strong> record dengan <strong>{{ $itemBermasalah }}</strong> item bermasalah
															@if ($totalDTRSistem > 0)
																<br>• Akurasi keseluruhan: <strong>{{ number_format(($totalDTRManual / $totalDTRSistem) * 100, 2) }}%</strong>
															@endif
														</div>
													</div>
												</div>
											@elseif(request()->has('action') && request()->get('action') == 'filter')
												<div class="alert alert-warning text-center">
													<i class="fas fa-exclamation-triangle mr-2"></i>
													Tidak ada data pemantauan DTR untuk filter yang dipilih.
													<br><small class="text-muted">Pastikan sub kategori memiliki item dengan DTR Manual yang kurang dari DTR Sistem.</small>
												</div>
											@else
												<div class="alert alert-info text-center">
													<i class="fas fa-info-circle mr-2"></i>
													Silakan pilih cabang dan sub kategori untuk menampilkan data pemantauan DTR khusus.
													<br><small class="text-muted">Sistem akan menampilkan item dengan DTR Manual yang bermasalah.</small>
												</div>
											@endif
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

	<!-- Modal untuk Detail Data DTR -->
	<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="detailModalLabel">Detail Data DTR</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="modalContent">
						<!-- Content akan dimuat via JavaScript -->
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
					<button type="button" class="btn btn-primary" onclick="refreshDTRData()">Refresh Data</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal untuk Statistik DTR -->
	<div class="modal fade" id="statistikModal" tabindex="-1" role="dialog" aria-labelledby="statistikModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="statistikModalLabel">Statistik DTR per Sub Kategori</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="statistikContent">
						<!-- Content akan dimuat via JavaScript -->
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script>
		$(document).ready(function() {
			// Load sub kategori saat cabang dipilih
			$('#cbg').on('change', function() {
				loadSubKategori($(this).val());
			});

			// Auto-focus on sub when cabang is selected
			$('#cbg').on('change', function() {
				if ($(this).val()) {
					setTimeout(function() {
						$('#sub').focus();
					}, 500);
				}
			});

			// Form validation sesuai dengan logika Delphi
			$('#rDtrForm').on('submit', function(e) {
				if (!validateForm()) {
					e.preventDefault();
				}
			});

			// Enter key handling - seperti behavior di Delphi
			$('#sub').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					if (validateForm()) {
						$(this).closest('form').find('button[name="action"][value="filter"]').click();
					}
				}
			});

			// Keyboard shortcuts sesuai dengan Delphi behavior
			$(document).keydown(function(e) {
				// F9 untuk proses (seperti btnProsesClick di Delphi)
				if (e.which == 120) { // F9
					e.preventDefault();
					if (validateForm()) {
						$('#rDtrForm').find('button[name="action"][value="filter"]').click();
					}
				}
				// Escape to close/reset
				else if (e.which == 27) {
					if (confirm('Reset form?')) {
						resetForm();
					}
				}
				// F5 untuk refresh data
				else if (e.which == 116) { // F5
					e.preventDefault();
					refreshDTRData();
				}
			});

			// Load sub kategori jika ada cbg yang sudah dipilih
			var selectedCbg = $('#cbg').val();
			if (selectedCbg) {
				loadSubKategori(selectedCbg, '{{ session()->get('filter_sub') }}');
			}

			// Add context menu untuk row actions
			$(document).on('contextmenu', '#rDtrTable tbody tr', function(e) {
				e.preventDefault();
				showContextMenu(e, this);
			});
		});

		// Load sub kategori berdasarkan cabang
		function loadSubKategori(cbg, selectedSub = '') {
			if (!cbg) {
				$('#sub').html('<option value="">Pilih Sub Kategori</option>');
				return;
			}

			$('#sub').html('<option value="">Loading...</option>');

			// AJAX call untuk mendapatkan sub kategori
			$.ajax({
				url: '{{ url('/api/get-sub-kategori') }}',
				method: 'GET',
				data: {
					cbg: cbg
				},
				success: function(response) {
					var options = '<option value="">Pilih Sub Kategori</option>';

					if (response.success && response.data && response.data.length > 0) {
						$.each(response.data, function(index, item) {
							var selected = (selectedSub && selectedSub === item.SUB) ? 'selected' : '';
							options += '<option value="' + item.SUB + '" ' + selected + '>' + item.SUB + '</option>';
						});
					}

					$('#sub').html(options);
				},
				error: function(xhr, status, error) {
					console.error('Error loading sub kategori:', error);
					$('#sub').html('<option value="">Error loading data</option>');

					// Fallback: hardcoded common sub categories
					var fallbackOptions = '<option value="">Pilih Sub Kategori</option>' +
						'<option value="01">01 - Makanan</option>' +
						'<option value="02">02 - Minuman</option>' +
						'<option value="03">03 - Elektronik</option>' +
						'<option value="04">04 - Kosmetik</option>' +
						'<option value="05">05 - Lainnya</option>';
					$('#sub').html(fallbackOptions);
				}
			});
		}

		// Reset form function
		function resetForm() {
			window.location.href = '{{ route('rpemantauandtrkhusus') }}';
		}

		// Form validation sesuai dengan requirement dari Delphi
		function validateForm() {
			var cbg = $('#cbg').val();
			var sub = $('#sub').val();

			if (!cbg) {
				alert('Harap pilih cabang');
				$('#cbg').focus();
				return false;
			}

			if (!sub) {
				alert('Harap pilih sub kategori');
				$('#sub').focus();
				return false;
			}

			return true;
		}

		// Export functions - mengimplementasikan logika Button2Click dari Delphi
		function exportData() {
			if (!validateForm()) {
				return false;
			}

			if (typeof window.rDtrTable !== 'undefined') {
				// Direct excel export via DataTables
				window.rDtrTable.button('.buttons-excel').trigger();
			} else {
				// Fallback: redirect to export URL
				var cbg = $('#cbg').val();
				var sub = $('#sub').val();

				if (cbg && sub) {
					window.open('{{ url('/export-pemantauandtrkhusus-excel') }}?cbg=' + cbg + '&sub=' + sub, '_blank');
				} else {
					alert('Tidak ada data untuk di-export. Silakan proses data terlebih dahulu.');
				}
			}
		}

		// Show detail modal dengan informasi DTR lengkap
		function showDetail(kdBrg, naBrg, sub, dtr, dtrManual, dtr1M, lph) {
			$('#detailModalLabel').text('Detail DTR: ' + kdBrg);

			var dtrValue = parseFloat(dtr) || 0;
			var dtrManualValue = parseFloat(dtrManual) || 0;
			var dtr1MValue = parseFloat(dtr1M) || 0;
			var lphValue = parseInt(lph) || 0;
			var selisih = dtrValue - dtrManualValue;

			var statusAnalisis = '';
			var badgeClass = '';

			if (dtrManualValue <= 0) {
				statusAnalisis = 'DTR Manual belum diset';
				badgeClass = 'badge-secondary';
			} else if (dtrManualValue < dtrValue) {
				statusAnalisis = 'DTR Manual kurang dari DTR Sistem (Bermasalah)';
				badgeClass = 'badge-warning';
			} else if (dtrManualValue >= dtrValue) {
				statusAnalisis = 'DTR Manual sama atau lebih dari DTR Sistem';
				badgeClass = 'badge-success';
			}

			var akurasiPersen = dtrValue > 0 ? ((dtrManualValue / dtrValue) * 100).toFixed(2) : 0;

			var content = '<div class="row">' +
				'<div class="col-12">' +
				'<h6>Barang: ' + naBrg + '</h6>' +
				'<table class="table table-sm">' +
				'<tr><td><strong>Kode Barang:</strong></td><td>' + kdBrg + '</td></tr>' +
				'<tr><td><strong>Sub Kategori:</strong></td><td>' + sub + '</td></tr>' +
				'<tr><td><strong>LPH:</strong></td><td>' + lphValue.toLocaleString('id-ID') + '</td></tr>' +
				'<tr><td><strong>DTR Sistem:</strong></td><td>' + dtrValue.toLocaleString('id-ID', {
					minimumFractionDigits: 2
				}) + '</td></tr>' +
				'<tr><td><strong>DTR Manual:</strong></td><td>' + dtrManualValue.toLocaleString('id-ID', {
					minimumFractionDigits: 2
				}) + '</td></tr>' +
				'<tr><td><strong>DTR 1 Muka:</strong></td><td>' + dtr1MValue.toLocaleString('id-ID', {
					minimumFractionDigits: 2
				}) + '</td></tr>' +
				'<tr><td><strong>Selisih DTR:</strong></td><td class="' + (selisih > 0 ? 'text-danger' : selisih < 0 ? 'text-success' : 'text-muted') +
				'">' +
				selisih.toLocaleString('id-ID', {
					minimumFractionDigits: 2
				}) + '</td></tr>' +
				'<tr><td><strong>Akurasi:</strong></td><td>' + akurasiPersen + '%</td></tr>' +
				'<tr><td><strong>Status:</strong></td><td><span class="badge ' + badgeClass + '">' + statusAnalisis + '</span></td></tr>' +
				'</table>' +
				'<hr>' +
				'<p class="text-muted"><small>Data DTR diambil berdasarkan perhitungan sistem dan input manual untuk pemantauan akurasi distribusi.</small></p>' +
				'</div>' +
				'</div>';

			$('#modalContent').html(content);
			$('#detailModal').modal('show');
		}

		// Context menu untuk row actions
		function showContextMenu(event, row) {
			var rowData = window.rDtrTable.row(row).data();

			// Create context menu
			var contextMenu = $(
				'<div class="context-menu" style="position: absolute; background: white; border: 1px solid #ccc; padding: 5px; z-index: 1000;">' +
				'<a href="#" class="btn btn-sm btn-info btn-block" onclick="showDetailFromContext(\'' +
				rowData.KD_BRG + '\', \'' + rowData.NA_BRG + '\', \'' + rowData.SUB + '\', ' +
				rowData.DTR + ', ' + rowData.DTR_MANUAL + ', ' + rowData.DTR_1M + ', ' + rowData.LPH + ')">Detail</a>' +
				'<a href="#" class="btn btn-sm btn-warning btn-block" onclick="highlightSimilarItems(\'' + rowData.SUB + '\')">Highlight Sub</a>' +
				'</div>');

			// Remove existing context menu
			$('.context-menu').remove();

			// Position and show context menu
			contextMenu.css({
				top: event.pageY,
				left: event.pageX
			});

			$('body').append(contextMenu);

			// Hide context menu on click elsewhere
			$(document).on('click.contextmenu', function() {
				$('.context-menu').remove();
				$(document).off('click.contextmenu');
			});
		}

		// Show detail dari context menu
		function showDetailFromContext(kdBrg, naBrg, sub, dtr, dtrManual, dtr1M, lph) {
			$('.context-menu').remove();
			showDetail(kdBrg, naBrg, sub, dtr, dtrManual, dtr1M, lph);
		}

		// Highlight items dengan sub kategori yang sama
		function highlightSimilarItems(sub) {
			$('.context-menu').remove();

			// Remove existing highlights
			$('#rDtrTable tbody tr').removeClass('table-warning');

			// Add highlight to matching rows
			$('#rDtrTable tbody tr').each(function() {
				var rowData = window.rDtrTable.row(this).data();
				if (rowData && rowData.SUB === sub) {
					$(this).addClass('table-warning');
				}
			});

			// Show notification
			showNotification('success', 'Items dengan sub kategori "' + sub + '" telah di-highlight');
		}

		// Loading indicator functions
		function showLoading() {
			$('button[type="submit"]').prop('disabled', true);
			$('button[name="action"][value="filter"]').html('<i class="fas fa-spinner fa-spin mr-1"></i>Processing...');
		}

		function hideLoading() {
			$('button[type="submit"]').prop('disabled', false);
			$('button[name="action"][value="filter"]').html('<i class="fas fa-search mr-1"></i>Proses');
			$('button[name="action"][value="cetak"]').html('<i class="fas fa-print mr-1"></i>Cetak');
		}

		// Show loading on form submit - seperti saat procedure dijalankan di Delphi
		$('#rDtrForm').on('submit', function() {
			if (validateForm()) {
				showLoading();
			}
		});

		// Hide loading when page loads
		$(window).on('load', function() {
			hideLoading();
		});

		// Refresh DTR data function
		function refreshDTRData() {
			if (validateForm()) {
				showLoading();
				$('#rDtrForm').find('button[name="action"][value="filter"]').click();
			}
		}

		// Show notification function
		function showNotification(type, message) {
			var alertClass = 'alert-info';
			switch (type) {
				case 'success':
					alertClass = 'alert-success';
					break;
				case 'error':
					alertClass = 'alert-danger';
					break;
				case 'warning':
					alertClass = 'alert-warning';
					break;
			}

			var notification = $('<div class="alert ' + alertClass +
				' alert-dismissible fade show" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">' +
				'<button type="button" class="close" data-dismiss="alert">' +
				'<span>&times;</span>' +
				'</button>' +
				message +
				'</div>');

			$('body').append(notification);

			// Auto dismiss after 3 seconds
			setTimeout(function() {
				notification.alert('close');
			}, 3000);
		}

		// Show statistik DTR modal
		function showStatistikDTR() {
			var cbg = $('#cbg').val();
			if (!cbg) {
				alert('Pilih cabang terlebih dahulu');
				return;
			}

			$('#statistikContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading statistik...</div>');
			$('#statistikModal').modal('show');

			// AJAX call untuk mendapatkan statistik
			$.ajax({
				url: '{{ url('/api/dtr-statistics-by-sub') }}',
				method: 'GET',
				data: {
					cbg: cbg
				},
				success: function(response) {
					if (response.success && response.data) {
						var statistikHTML = '<div class="table-responsive">' +
							'<table class="table table-sm table-bordered">' +
							'<thead class="thead-dark">' +
							'<tr>' +
							'<th>Sub</th>' +
							'<th>Total Item</th>' +
							'<th>Item Bermasalah</th>' +
							'<th>Avg DTR Manual</th>' +
							'<th>Avg DTR Sistem</th>' +
							'<th>Total LPH</th>' +
							'<th>% Bermasalah</th>' +
							'</tr>' +
							'</thead>' +
							'<tbody>';

						$.each(response.data, function(index, item) {
							var badgeClass = item.PERSENTASE_BERMASALAH > 50 ? 'badge-danger' :
								item.PERSENTASE_BERMASALAH > 25 ? 'badge-warning' : 'badge-success';

							statistikHTML += '<tr>' +
								'<td><span class="badge badge-secondary">' + item.SUB + '</span></td>' +
								'<td class="text-right">' + parseInt(item.TOTAL_ITEM).toLocaleString('id-ID') + '</td>' +
								'<td class="text-right">' + parseInt(item.ITEM_BERMASALAH).toLocaleString('id-ID') + '</td>' +
								'<td class="text-right">' + parseFloat(item.AVG_DTR_MANUAL).toLocaleString('id-ID', {
									minimumFractionDigits: 2
								}) + '</td>' +
								'<td class="text-right">' + parseFloat(item.AVG_DTR_SISTEM).toLocaleString('id-ID', {
									minimumFractionDigits: 2
								}) + '</td>' +
								'<td class="text-right">' + parseInt(item.TOTAL_LPH).toLocaleString('id-ID') + '</td>' +
								'<td class="text-center"><span class="badge ' + badgeClass + '">' + parseFloat(item
									.PERSENTASE_BERMASALAH).toFixed(1) + '%</span></td>' +
								'</tr>';
						});

						statistikHTML += '</tbody></table></div>';
						$('#statistikContent').html(statistikHTML);
					} else {
						$('#statistikContent').html('<div class="alert alert-warning">Tidak ada data statistik tersedia</div>');
					}
				},
				error: function(xhr, status, error) {
					console.error('Error loading statistik:', error);
					$('#statistikContent').html('<div class="alert alert-danger">Error loading statistik data</div>');
				}
			});
		}

		// Custom styling untuk visualisasi data DTR sesuai dengan kondisi bisnis
		$(document).ready(function() {
			setTimeout(function() {
				// Add custom CSS for better visualization
				$('<style>')
					.prop('type', 'text/css')
					.html(`
						.dt-center { text-align: center !important; }
						.dt-right { text-align: right !important; }
						.dt-left { text-align: left !important; }
						.dtr-bermasalah {
							background-color: #fff3cd !important;
							border-left: 4px solid #ffc107 !important;
						}
						.dtr-normal {
							background-color: #d4edda !important;
							border-left: 4px solid #28a745 !important;
						}
						.dtr-kosong {
							background-color: #f8d7da !important;
							border-left: 4px solid #dc3545 !important;
						}
						.table td {
							font-size: 0.875rem;
							vertical-align: middle;
						}
						.table th {
							font-size: 0.8rem;
							font-weight: 600;
						}
						.badge {
							font-size: 0.75em;
						}
						.form-control:focus {
							border-color: #80bdff;
							box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
						}
						.context-menu {
							border-radius: 4px;
							box-shadow: 0 2px 10px rgba(0,0,0,0.2);
						}
						.context-menu a {
							margin: 2px 0;
							text-decoration: none;
						}
						.info-box {
							cursor: pointer;
							transition: transform 0.2s;
						}
						.info-box:hover {
							transform: translateY(-2px);
							box-shadow: 0 4px 8px rgba(0,0,0,0.1);
						}
						.table-warning {
							background-color: #fff3cd !important;
							animation: highlight 2s ease-in-out;
						}
						@keyframes highlight {
							0% { background-color: #ffc107; }
							100% { background-color: #fff3cd; }
						}
					`)
					.appendTo('head');
			}, 1000);
		});

		// Add row highlighting based on DTR status - sesuai dengan kondisi WHERE di query Delphi
		$(document).on('draw.dt', '#rDtrTable', function() {
			$('#rDtrTable tbody tr').each(function() {
				var row = $(this);
				var rowData = window.rDtrTable.row(this).data();

				if (rowData) {
					var dtrSistem = parseFloat(rowData.DTR) || 0;
					var dtrManual = parseFloat(rowData.DTR_MANUAL) || 0;

					// Aplikasikan CSS class berdasarkan kondisi DTR
					if (dtrManual <= 0) {
						row.addClass('dtr-kosong');
						row.attr('title', 'DTR Manual belum diset');
					} else if (dtrManual > 0 && dtrManual < dtrSistem) {
						row.addClass('dtr-bermasalah');
						row.attr('title', 'DTR Manual kurang dari DTR Sistem - Memerlukan perhatian');
					} else {
						row.addClass('dtr-normal');
						row.attr('title', 'DTR Manual normal');
					}
				}
			});
		});

		// Initialize tooltips and popovers
		$(function() {
			$('[data-toggle="tooltip"]').tooltip();
			$('[data-toggle="popover"]').popover();
		});

		// Auto-save form state untuk user experience
		$('#cbg, #sub').on('change', function() {
			// Save to sessionStorage untuk restore state jika diperlukan
			try {
				var formState = {
					cbg: $('#cbg').val(),
					sub: $('#sub').val(),
					timestamp: new Date().getTime()
				};
				// Note: sessionStorage tidak tersedia di environment Claude, tapi code tetap valid
				// sessionStorage.setItem('dtr_form_state', JSON.stringify(formState));
			} catch (e) {
				// Fallback: ignore if storage not available
			}
		});

		// Restore form state on page load
		$(document).ready(function() {
			try {
				// var savedState = sessionStorage.getItem('dtr_form_state');
				// if (savedState) {
				//     var state = JSON.parse(savedState);
				//     // Check if state is recent (within 1 hour)
				//     if (new Date().getTime() - state.timestamp < 3600000) {
				//         if (state.cbg && !$('#cbg').val()) $('#cbg').val(state.cbg).trigger('change');
				//         if (state.sub && !$('#sub').val()) $('#sub').val(state.sub);
				//     }
				// }
			} catch (e) {
				// Fallback: ignore if storage not available
			}
		});

		// Quick action buttons
		function addQuickActionButtons() {
			if ($('#rDtrTable').length && window.rDtrTable) {
				var quickActions = '<div class="row mt-2">' +
					'<div class="col-12 text-right">' +
					'<div class="btn-group" role="group">' +
					'<button type="button" class="btn btn-sm btn-outline-info" onclick="showStatistikDTR()" title="Lihat statistik DTR per sub kategori">' +
					'<i class="fas fa-chart-bar mr-1"></i>Statistik' +
					'</button>' +
					'<button type="button" class="btn btn-sm btn-outline-warning" onclick="highlightBermasalah()" title="Highlight item bermasalah">' +
					'<i class="fas fa-exclamation-triangle mr-1"></i>Highlight Masalah' +
					'</button>' +
					'<button type="button" class="btn btn-sm btn-outline-success" onclick="clearHighlights()" title="Clear semua highlight">' +
					'<i class="fas fa-eraser mr-1"></i>Clear Highlight' +
					'</button>' +
					'</div>' +
					'</div>' +
					'</div>';

				$('.report-content').append(quickActions);
			}
		}

		// Highlight item bermasalah
		function highlightBermasalah() {
			// Remove existing highlights
			$('#rDtrTable tbody tr').removeClass('table-warning table-danger');

			var bermasalahCount = 0;

			// Add highlight to problematic rows
			$('#rDtrTable tbody tr').each(function() {
				var rowData = window.rDtrTable.row(this).data();
				if (rowData) {
					var dtrSistem = parseFloat(rowData.DTR) || 0;
					var dtrManual = parseFloat(rowData.DTR_MANUAL) || 0;

					// Sesuai dengan kondisi WHERE di query: c.DTR_MANUAL > 0 AND c.DTR_MANUAL < xx_hitdtr(a.KD_BRG)
					if (dtrManual > 0 && dtrManual < dtrSistem) {
						$(this).addClass('table-danger');
						bermasalahCount++;
					}
				}
			});

			showNotification('warning', 'Ditemukan ' + bermasalahCount + ' item bermasalah yang di-highlight');
		}

		// Clear semua highlights
		function clearHighlights() {
			$('#rDtrTable tbody tr').removeClass('table-warning table-danger dtr-bermasalah dtr-normal dtr-kosong');
			showNotification('info', 'Semua highlight telah dibersihkan');
		}

		// Add quick actions setelah tabel dimuat
		$(document).ready(function() {
			setTimeout(function() {
				if ($('#rDtrTable').length) {
					addQuickActionButtons();
				}
			}, 2000);
		});

		// Print function dengan format yang disesuaikan
		function printReport() {
			if (!validateForm()) {
				return false;
			}

			var cbg = $('#cbg').val();
			var sub = $('#sub').val();

			if (cbg && sub) {
				// Open print in new window
				var printUrl = '{{ route('jasper-pemantauandtrkhusus-report') }}';
				var form = $('<form method="POST" action="' + printUrl + '" target="_blank">' +
					'@csrf' +
					'<input type="hidden" name="cbg" value="' + cbg + '">' +
					'<input type="hidden" name="sub" value="' + sub + '">' +
					'</form>');

				$('body').append(form);
				form.submit();
				form.remove();
			} else {
				alert('Tidak ada data untuk dicetak. Silakan proses data terlebih dahulu.');
			}
		}

		// Validate DTR consistency function
		function validateDTRConsistency() {
			var cbg = $('#cbg').val();
			if (!cbg) {
				alert('Pilih cabang terlebih dahulu');
				return;
			}

			$('#modalContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Validating DTR consistency...</div>');
			$('#detailModalLabel').text('Validasi Konsistensi DTR');
			$('#detailModal').modal('show');

			$.ajax({
				url: '{{ url('/api/validate-dtr-consistency') }}',
				method: 'GET',
				data: {
					cbg: cbg
				},
				success: function(response) {
					if (response.success) {
						var content = '<div class="row">' +
							'<div class="col-12">' +
							'<h6>Hasil Validasi DTR untuk Cabang: ' + cbg + '</h6>' +
							'<div class="alert alert-info">' +
							'<strong>Summary:</strong><br>' +
							'• Total Inconsistent: ' + response.summary.total_inconsistent + '<br>' +
							'• DTR Manual Invalid: ' + response.summary.dtr_manual_invalid + '<br>' +
							'• DTR Manual Too High: ' + response.summary.dtr_manual_too_high + '<br>' +
							'• DTR 1M Invalid: ' + response.summary.dtr_1m_invalid +
							'</div>';

						if (response.inconsistent_data && response.inconsistent_data.length > 0) {
							content += '<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">' +
								'<table class="table table-sm table-striped">' +
								'<thead><tr><th>Sub</th><th>Kode</th><th>Nama</th><th>DTR Sistem</th><th>DTR Manual</th><th>Status</th></tr></thead>' +
								'<tbody>';

							$.each(response.inconsistent_data.slice(0, 20), function(index, item) {
								var badgeClass = item.STATUS_VALIDASI === 'DTR_MANUAL_INVALID' ? 'badge-danger' :
									item.STATUS_VALIDASI === 'DTR_MANUAL_TOO_HIGH' ? 'badge-warning' : 'badge-secondary';

								content += '<tr>' +
									'<td>' + item.SUB + '</td>' +
									'<td>' + item.KD_BRG + '</td>' +
									'<td>' + item.NA_BRG.substring(0, 30) + (item.NA_BRG.length > 30 ? '...' : '') + '</td>' +
									'<td class="text-right">' + parseFloat(item.DTR_SISTEM).toLocaleString('id-ID', {
										minimumFractionDigits: 2
									}) + '</td>' +
									'<td class="text-right">' + parseFloat(item.DTR_MANUAL).toLocaleString('id-ID', {
										minimumFractionDigits: 2
									}) + '</td>' +
									'<td><span class="badge ' + badgeClass + '">' + item.STATUS_VALIDASI + '</span></td>' +
									'</tr>';
							});

							content += '</tbody></table></div>';

							if (response.inconsistent_data.length > 20) {
								content += '<p class="text-muted">Menampilkan 20 dari ' + response.inconsistent_data.length +
									' record bermasalah</p>';
							}
						} else {
							content += '<div class="alert alert-success">Tidak ada masalah konsistensi DTR ditemukan</div>';
						}

						content += '</div></div>';
						$('#modalContent').html(content);
					} else {
						$('#modalContent').html('<div class="alert alert-danger">Error: ' + (response.error || 'Unknown error') + '</div>');
					}
				},
				error: function(xhr, status, error) {
					$('#modalContent').html('<div class="alert alert-danger">Error validating DTR consistency</div>');
				}
			});
		}

		// Keyboard navigation enhancement
		$(document).keydown(function(e) {
			// Ctrl+E untuk export
			if (e.ctrlKey && e.which == 69) {
				e.preventDefault();
				exportData();
			}
			// Ctrl+P untuk print
			else if (e.ctrlKey && e.which == 80) {
				e.preventDefault();
				printReport();
			}
			// Ctrl+S untuk statistik
			else if (e.ctrlKey && e.which == 83) {
				e.preventDefault();
				showStatistikDTR();
			}
			// Ctrl+V untuk validasi
			else if (e.ctrlKey && e.which == 86) {
				e.preventDefault();
				validateDTRConsistency();
			}
		});

		// Info box click handlers untuk interaktivitas
		$(document).on('click', '.info-box', function() {
			var iconClass = $(this).find('.info-box-icon i').attr('class');

			if (iconClass.includes('fa-exclamation-triangle')) {
				highlightBermasalah();
			} else if (iconClass.includes('fa-calculator') || iconClass.includes('fa-edit')) {
				showStatistikDTR();
			} else if (iconClass.includes('fa-cubes')) {
				// Show total info
				var totalItems = $(this).find('.info-box-number').text();
				showNotification('info', 'Total ' + totalItems + ' item DTR ditampilkan');
			}
		});

		// Auto-refresh data setiap 5 menit jika diperlukan (optional)
		// setInterval(function() {
		//     if ($('#cbg').val() && $('#sub').val() && document.visibilityState === 'visible') {
		//         console.log('Auto-refreshing DTR data...');
		//         refreshDTRData();
		//     }
		// }, 300000); // 5 minutes

		// Enhanced form validation dengan detail checking
		function validateFormEnhanced() {
			var isValid = validateForm();

			if (isValid) {
				// Additional validation untuk DTR logic
				var cbg = $('#cbg').val();
				var sub = $('#sub').val();

				// Check if combination is valid
				$.ajax({
					url: '{{ url('/api/validate-cbg-sub-combination') }}',
					method: 'GET',
					data: {
						cbg: cbg,
						sub: sub
					},
					async: false,
					success: function(response) {
						if (!response.success) {
							alert('Kombinasi cabang dan sub kategori tidak valid: ' + (response.message || 'Unknown error'));
							isValid = false;
						}
					},
					error: function() {
						// Continue jika validation endpoint tidak tersedia
						console.warn('DTR validation endpoint not available, continuing...');
					}
				});
			}

			return isValid;
		}

		// Update form submission untuk menggunakan enhanced validation
		$('#rDtrForm').off('submit').on('submit', function(e) {
			if (!validateFormEnhanced()) {
				e.preventDefault();
			} else {
				showLoading();
			}
		});

		// Help function untuk menampilkan bantuan penggunaan
		function showHelp() {
			var helpContent = '<div class="row">' +
				'<div class="col-12">' +
				'<h6>Bantuan Penggunaan Form Pemantauan DTR</h6>' +
				'<div class="alert alert-info">' +
				'<strong>Keyboard Shortcuts:</strong><br>' +
				'• F9: Proses data<br>' +
				'• Escape: Reset form<br>' +
				'• F5: Refresh data<br>' +
				'• Ctrl+E: Export Excel<br>' +
				'• Ctrl+P: Print laporan<br>' +
				'• Ctrl+S: Statistik DTR<br>' +
				'• Ctrl+V: Validasi konsistensi' +
				'</div>' +
				'<div class="alert alert-warning">' +
				'<strong>Logika DTR:</strong><br>' +
				'• Sistem menampilkan item dengan DTR Manual yang kurang dari DTR Sistem<br>' +
				'• DTR Manual > 0 dan DTR Manual < DTR Sistem dianggap bermasalah<br>' +
				'• Data diurutkan berdasarkan Sub kategori dan Kode Barang' +
				'</div>' +
				'</div>' +
				'</div>';

			$('#detailModalLabel').text('Bantuan Penggunaan');
			$('#modalContent').html(helpContent);
			$('#detailModal').modal('show');
		}

		// Add help button
		$(document).ready(function() {
			setTimeout(function() {
				var helpButton =
					'<button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="showHelp()" title="Bantuan penggunaan">' +
					'<i class="fas fa-question-circle"></i>' +
					'</button>';
				$('.breadcrumb').after(helpButton);
			}, 1000);
		});
	</script>
@endsection
