@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Form Report Stok Nol</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Form Report Stok Nol</li>
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
								<form method="GET" action="{{ route('get-stoknol-report') }}" id="rStokNolForm">
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

											<div class="col-2 mb-2">
												<label for="hari">Selisih Hari</label>
												<input type="number" name="hari" id="hari" class="form-control" value="{{ session()->get('filter_hari', 9999) }}" min="0"
													max="9999">
											</div>

											<div class="col-2 mb-2">
												<label for="filter_tgl">Filter Tanggal</label>
												<div class="form-check mt-2">
													<input type="checkbox" name="filter_tgl" id="filter_tgl" class="form-check-input" value="1"
														{{ session()->get('filter_tgl') ? 'checked' : '' }}>
													<label class="form-check-label" for="filter_tgl">Aktifkan</label>
												</div>
											</div>

											<div class="col-2 mb-2" id="tgl1_container" style="{{ session()->get('filter_tgl') ? '' : 'display:none' }}">
												<label for="tgl1">Tanggal Mulai</label>
												<input type="date" name="tgl1" id="tgl1" class="form-control"
													value="{{ session()->get('tgl1', \Carbon\Carbon::today()->format('Y-m-d')) }}">
											</div>

											<div class="col-2 mb-2" id="tgl2_container" style="{{ session()->get('filter_tgl') ? '' : 'display:none' }}">
												<label for="tgl2">Tanggal Akhir</label>
												<input type="date" name="tgl2" id="tgl2" class="form-control"
													value="{{ session()->get('tgl2', \Carbon\Carbon::today()->format('Y-m-d')) }}">
											</div>

											<div class="col-1 mb-2 text-right">
												<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
													<i class="fas fa-search mr-1"></i>Proses
												</button>
											</div>
										</div>

										<div class="row">
											<div class="col-12 text-right">
												<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
													<i class="fas fa-undo mr-1"></i>Reset
												</button>
												<button class="btn btn-success mr-1" type="submit" name="action" value="cetak" formaction="{{ route('jasper-stoknol-report') }}"
													formmethod="POST" formtarget="_blank">
													<i class="fas fa-print mr-1"></i>Cetak
												</button>
												<button class="btn btn-info" type="button" onclick="exportData()">
													<i class="fas fa-download mr-1"></i>Excel
												</button>
											</div>
										</div>

										@if (session()->get('filter_cbg'))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-info">
														<strong>Filter Aktif:</strong>
														Cabang: {{ session()->get('filter_cbg') }} |
														Selisih Hari: {{ session()->get('filter_hari', 9999) }}
														@if (session()->get('filter_tgl'))
															| Filter Tanggal: {{ \Carbon\Carbon::parse(session()->get('tgl1'))->format('d/m/Y') }} s/d
															{{ \Carbon\Carbon::parse(session()->get('tgl2'))->format('d/m/Y') }}
														@endif
														| Tanggal Generate: {{ date('d/m/Y H:i:s') }}
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

										@if (session('success'))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-success alert-dismissible fade show">
														<i class="fas fa-check-circle mr-2"></i>
														{{ session('success') }}
														<button type="button" class="close" data-dismiss="alert">
															<span aria-hidden="true">&times;</span>
														</button>
													</div>
												</div>
											</div>
										@endif

										<div style="margin-bottom: 15px;"></div>
										<?php
											use \koolreport\datagrid\DataTables;
										?>
										<div class="report-content" col-md-12>
											@if ($hasilStokNol && count($hasilStokNol) > 0)
												<?php
												// Create custom data for KoolDataTables sesuai dengan struktur stok nol
												$tableData = [];
												$totalStokNegatif = 0;
												$totalStokNol = 0;
												$totalHari = 0;
												$itemTerlama = 0;

												foreach ($hasilStokNol as $item) {
												    $stok = $item->STOK ?? 0;
												    $hari = $item->HARI ?? 0;

												    // Hitung statistik
												    if ($stok < 0) {
												        $totalStokNegatif++;
												    }
												    if ($stok == 0) {
												        $totalStokNol++;
												    }
												    $totalHari += $hari;
												    if ($hari > 30) {
												        $itemTerlama++;
												    }

												    $tableData[] = [
												        'SUB' => substr($item->KD_BRG ?? '', 0, 2), // Sub item dari 2 digit pertama kode barang
												        'NA_BRG' => $item->NA_BRG ?? '',
												        'KET_UK' => $item->KET_UK ?? '',
												        'KET_KEM' => $item->KET_KEM ?? '',
												        'BARCODE' => $item->BARCODE ?? '',
												        'STOK' => $stok,
												        'TD_OD' => $item->TD_OD ?? '',
												        'TGL_OD' => $item->TGL_OD ?? '',
												        'TGL_KSR' => $item->TGL_KSR ?? '',
												        'HARI' => $hari,
												        // Hidden fields for export/reference
												        'KD_BRG' => $item->KD_BRG ?? '',
												        'CAT_OD' => $item->CAT_OD ?? '',
												    ];
												}

												$avgHari = count($hasilStokNol) > 0 ? round($totalHari / count($hasilStokNol), 1) : 0;

												// KoolDataTables::create([
												DataTables::create([
												    'dataSource' => $tableData,
												    'name' => 'rStokNolTable',
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
												        'BARCODE' => [
												            'label' => 'Barcode',
												            'width' => '120px',
												        ],
												        'STOK' => [
												            'label' => 'Stok',
												            'type' => 'number',
												            'decimals' => 0,
												            'decimalPoint' => '.',
												            'thousandSeparator' => ',',
												            'width' => '80px',
												        ],
												        'TD_OD' => [
												            'label' => 'Absen',
												            'width' => '60px',
												        ],
												        'TGL_OD' => [
												            'label' => 'Tgl Tanda',
												            'width' => '100px',
												        ],
												        'TGL_KSR' => [
												            'label' => 'Tgl Jual Kasir',
												            'width' => '120px',
												        ],
												        'HARI' => [
												            'label' => 'Hari',
												            'type' => 'number',
												            'decimals' => 0,
												            'width' => '60px',
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
												                'targets' => [5, 9], // STOK, HARI
												            ],
												            [
												                'className' => 'dt-center',
												                'targets' => [0, 2, 3, 6, 7, 8], // SUB, KET_UK, KET_KEM, TD_OD, TGL_OD, TGL_KSR
												            ],
												            [
												                'className' => 'dt-left',
												                'targets' => [1, 4], // NA_BRG, BARCODE
												            ],
												            // Custom rendering untuk STOK dengan color coding
												            [
												                'targets' => [5], // STOK column
												                'render' => 'function(data, type, row, meta) {
																								                    var stok = parseFloat(data) || 0;

																								                    if (stok < 0) {
																								                        return "<span class=\"text-danger font-weight-bold\" title=\"Stok Negatif\">" +
																								                               stok.toLocaleString("id-ID") + "</span>";
																								                    } else if (stok == 0) {
																								                        return "<span class=\"text-warning font-weight-bold\" title=\"Stok Kosong\">0</span>";
																								                    } else {
																								                        return "<span class=\"text-muted\">" + stok.toLocaleString("id-ID") + "</span>";
																								                    }
																								                }',
												            ],
												            // Custom rendering untuk HARI dengan status indicator
												            [
												                'targets' => [9], // HARI column
												                'render' => 'function(data, type, row, meta) {
																								                    var hari = parseInt(data) || 0;

																								                    if (hari > 30) {
																								                        return "<span class=\"badge badge-danger\" title=\"Lebih dari 30 hari\">" + hari + "</span>";
																								                    } else if (hari > 14) {
																								                        return "<span class=\"badge badge-warning\" title=\"Lebih dari 14 hari\">" + hari + "</span>";
																								                    } else if (hari > 7) {
																								                        return "<span class=\"badge badge-info\" title=\"Lebih dari 7 hari\">" + hari + "</span>";
																								                    } else {
																								                        return "<span class=\"badge badge-success\" title=\"Kurang dari 7 hari\">" + hari + "</span>";
																								                    }
																								                }',
												            ],
												            // Custom rendering untuk TD_OD (Absen)
												            [
												                'targets' => [6], // TD_OD column
												                'render' => 'function(data, type, row, meta) {
																								                    if (data && data === "*") {
																								                        return "<span class=\"badge badge-primary\">*</span>";
																								                    } else {
																								                        return "<span class=\"text-muted\">-</span>";
																								                    }
																								                }',
												            ],
												            // Custom rendering untuk tanggal dengan format Indonesia
												            [
												                'targets' => [7, 8], // TGL_OD, TGL_KSR columns
												                'render' => 'function(data, type, row, meta) {
																								                    if (data && data !== "" && data !== null) {
																								                        try {
																								                            var date = new Date(data);
																								                            if (!isNaN(date.getTime())) {
																								                                return date.toLocaleDateString("id-ID", {
																								                                    day: "2-digit",
																								                                    month: "2-digit",
																								                                    year: "numeric"
																								                                });
																								                            }
																								                        } catch(e) {}
																								                    }
																								                    return "<span class=\"text-muted\">-</span>";
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
												            // Custom rendering untuk BARCODE
												            [
												                'targets' => [4], // BARCODE column
												                'render' => 'function(data, type, row, meta) {
																								                    if (data && data.length > 0) {
																								                        return "<span class=\"font-monospace text-primary\">" + data + "</span>";
																								                    } else {
																								                        return "<span class=\"text-muted\">-</span>";
																								                    }
																								                }',
												            ],
												        ],
												        'order' => [[7, 'desc'], [0, 'asc']], // Order by TGL_OD desc, then SUB asc
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

																								            // Total items
																								            var totalItems = api.rows({ page: "current" }).count();

																								            // Count stok negatif dan nol
																								            var stokNegatif = 0;
																								            var stokNol = 0;

																								            api.column(5, { page: "current" }).data().each(function(value) {
																								                var stok = parseFloat(value) || 0;
																								                if (stok < 0) stokNegatif++;
																								                if (stok === 0) stokNol++;
																								            });

																								            // Update footer untuk kolom yang relevan
																								            $(api.column(0).footer()).html("<strong>Total: " + totalItems + "</strong>");
																								            $(api.column(5).footer()).html("<strong>Negatif: " + stokNegatif + " | Nol: " + stokNol + "</strong>");
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
																<span class="info-box-number">{{ count($hasilStokNol) }}</span>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="info-box">
															<span class="info-box-icon bg-danger"><i class="fas fa-minus-circle"></i></span>
															<div class="info-box-content">
																<span class="info-box-text">Stok Negatif</span>
																<span class="info-box-number">{{ $totalStokNegatif }}</span>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="info-box">
															<span class="info-box-icon bg-warning"><i class="fas fa-exclamation-circle"></i></span>
															<div class="info-box-content">
																<span class="info-box-text">Stok Nol</span>
																<span class="info-box-number">{{ $totalStokNol }}</span>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="info-box">
															<span class="info-box-icon bg-secondary"><i class="fas fa-clock"></i></span>
															<div class="info-box-content">
																<span class="info-box-text">Rata-rata Hari</span>
																<span class="info-box-number">{{ $avgHari }}</span>
															</div>
														</div>
													</div>
												</div>

												<div class="row mt-3">
													<div class="col-12">
														<div class="alert alert-success">
															<i class="fas fa-info-circle mr-2"></i>
															<strong>Informasi Data Stok Nol:</strong><br>
															• <span class="text-danger font-weight-bold">Stok Negatif</span> = Stok kurang dari 0 (ada masalah sistem)<br>
															• <span class="text-warning font-weight-bold">Stok Nol</span> = Stok habis (perlu restock)<br>
															• <span class="badge badge-danger">Hari > 30</span> = Item sudah lama kosong (prioritas tinggi)<br>
															• <span class="badge badge-warning">Hari > 14</span> = Item kosong cukup lama (perlu perhatian)<br>
															• <span class="badge badge-primary">Absen (*)</span> = Item tidak tersedia untuk dijual<br>
															• Total data: <strong>{{ count($hasilStokNol) }}</strong> record dengan <strong>{{ $itemTerlama }}</strong> item terlama
															@if (session()->get('filter_tgl'))
																<br>• Periode: <strong>{{ \Carbon\Carbon::parse(session()->get('tgl1'))->format('d/m/Y') }} -
																	{{ \Carbon\Carbon::parse(session()->get('tgl2'))->format('d/m/Y') }}</strong>
															@endif
														</div>
													</div>
												</div>
											@elseif(request()->has('action') && request()->get('action') == 'filter')
												<div class="alert alert-warning text-center">
													<i class="fas fa-exclamation-triangle mr-2"></i>
													Tidak ada data stok nol untuk filter yang dipilih.
													<br><small class="text-muted">
														Cabang: {{ session()->get('filter_cbg') }},
														Selisih Hari: {{ session()->get('filter_hari', 9999) }}
														@if (session()->get('filter_tgl'))
															, Periode: {{ \Carbon\Carbon::parse(session()->get('tgl1'))->format('d/m/Y') }} -
															{{ \Carbon\Carbon::parse(session()->get('tgl2'))->format('d/m/Y') }}
														@endif
													</small>
												</div>
											@else
												<div class="alert alert-info text-center">
													<i class="fas fa-info-circle mr-2"></i>
													Silakan pilih cabang untuk menampilkan data stok nol.
													<br><small class="text-muted">
														Sistem akan menampilkan item dengan stok kosong atau negatif dalam rentang hari yang ditentukan.
														@if (session()->get('filter_tgl'))
															<br>Filter tanggal aktif untuk periode: {{ \Carbon\Carbon::parse(session()->get('tgl1'))->format('d/m/Y') }} -
															{{ \Carbon\Carbon::parse(session()->get('tgl2'))->format('d/m/Y') }}
														@endif
													</small>
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

	<!-- Modal untuk Detail Data Stok -->
	<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="detailModalLabel">Detail Data Stok</h5>
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
					<button type="button" class="btn btn-primary" onclick="refreshStokData()">Refresh Data</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script>
		$(document).ready(function() {
			// Toggle filter tanggal visibility
			$('#filter_tgl').on('change', function() {
				if ($(this).is(':checked')) {
					$('#tgl1_container, #tgl2_container').show();
					// Set default dates if empty
					if (!$('#tgl1').val()) {
						$('#tgl1').val('{{ \Carbon\Carbon::today()->format('Y-m-d') }}');
					}
					if (!$('#tgl2').val()) {
						$('#tgl2').val('{{ \Carbon\Carbon::today()->format('Y-m-d') }}');
					}
				} else {
					$('#tgl1_container, #tgl2_container').hide();
				}
			});

			// Auto-focus on hari when cabang is selected
			$('#cbg').on('change', function() {
				if ($(this).val()) {
					setTimeout(function() {
						$('#hari').focus();
					}, 100);
				}
			});

			// Form validation sesuai dengan logika Delphi
			$('#rStokNolForm').on('submit', function(e) {
				if (!validateForm()) {
					e.preventDefault();
				}
			});

			// Enter key handling
			$('#hari').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					if (validateForm()) {
						$(this).closest('form').find('button[name="action"][value="filter"]').click();
					}
				}
			});

			// Date range validation
			$('#tgl1, #tgl2').on('change', function() {
				validateDateRange();
			});

			// Keyboard shortcuts sesuai dengan Delphi behavior
			$(document).keydown(function(e) {
				// F9 untuk proses
				if (e.which == 120) { // F9
					e.preventDefault();
					if (validateForm()) {
						$('#rStokNolForm').find('button[name="action"][value="filter"]').click();
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
					refreshStokData();
				}
			});
		});

		// Reset form function
		function resetForm() {
			window.location.href = '{{ route('rstoknol') }}';
		}

		// Form validation sesuai dengan requirement dari controller
		function validateForm() {
			var cbg = $('#cbg').val();
			var hari = $('#hari').val();

			if (!cbg) {
				alert('Harap pilih cabang');
				$('#cbg').focus();
				return false;
			}

			if (hari < 0) {
				alert('Selisih hari tidak boleh kurang dari 0');
				$('#hari').focus();
				return false;
			}

			// Validasi tanggal jika filter tanggal aktif
			if ($('#filter_tgl').is(':checked')) {
				return validateDateRange();
			}

			return true;
		}

		// Date range validation function
		function validateDateRange() {
			var tgl1 = $('#tgl1').val();
			var tgl2 = $('#tgl2').val();
			var filterTglChecked = $('#filter_tgl').is(':checked');

			if (!filterTglChecked) {
				return true;
			}

			if (!tgl1 || !tgl2) {
				alert('Tanggal mulai dan tanggal akhir harus diisi');
				if (!tgl1) $('#tgl1').focus();
				else $('#tgl2').focus();
				return false;
			}

			var startDate = new Date(tgl1);
			var endDate = new Date(tgl2);

			if (startDate > endDate) {
				alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
				$('#tgl1').focus();
				return false;
			}

			// Check if date range is too large (optional - prevent performance issues)
			var diffTime = Math.abs(endDate - startDate);
			var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

			if (diffDays > 365) {
				if (!confirm('Rentang tanggal lebih dari 1 tahun. Proses mungkin memakan waktu lama. Lanjutkan?')) {
					return false;
				}
			}

			return true;
		}

		// Export functions
		function exportData() {
			if (!validateForm()) {
				return false;
			}

			if (typeof window.rStokNolTable !== 'undefined') {
				// Direct excel export via DataTables
				window.rStokNolTable.button('.buttons-excel').trigger();
			} else {
				// Fallback: redirect to export URL
				var cbg = $('#cbg').val();
				var hari = $('#hari').val();
				var filterTgl = $('#filter_tgl').is(':checked') ? 1 : 0;
				var tgl1 = $('#tgl1').val();
				var tgl2 = $('#tgl2').val();

				if (cbg) {
					var exportUrl = '{{ url('/export-stoknol-excel') }}?cbg=' + cbg + '&hari=' + hari;
					if (filterTgl && tgl1 && tgl2) {
						exportUrl += '&filter_tgl=' + filterTgl + '&tgl1=' + tgl1 + '&tgl2=' + tgl2;
					}
					window.open(exportUrl, '_blank');
				} else {
					alert('Tidak ada data untuk di-export. Silakan proses data terlebih dahulu.');
				}
			}
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

		// Show loading on form submit
		$('#rStokNolForm').on('submit', function() {
			if (validateForm()) {
				showLoading();
			}
		});

		// Hide loading when page loads
		$(window).on('load', function() {
			hideLoading();
		});

		// Refresh stok data function
		function refreshStokData() {
			if (validateForm()) {
				showLoading();
				$('#rStokNolForm').find('button[name="action"][value="filter"]').click();
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

		// Date helper functions
		function formatDateForDisplay(dateString) {
			if (!dateString) return '-';
			try {
				var date = new Date(dateString);
				return date.toLocaleDateString('id-ID', {
					day: '2-digit',
					month: '2-digit',
					year: 'numeric'
				});
			} catch (e) {
				return dateString;
			}
		}

		function getDateDifference(date1, date2) {
			var d1 = new Date(date1);
			var d2 = new Date(date2);
			var diffTime = Math.abs(d2 - d1);
			return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
		}

		// Auto-save form state with date filter
		function autoSaveFormState() {
			var formState = {
				cbg: $('#cbg').val(),
				hari: $('#hari').val(),
				filter_tgl: $('#filter_tgl').is(':checked'),
				tgl1: $('#tgl1').val(),
				tgl2: $('#tgl2').val(),
				timestamp: new Date().toISOString()
			};

			localStorage.setItem('stoknol_form_state', JSON.stringify(formState));
		}

		function restoreFormState() {
			var savedState = localStorage.getItem('stoknol_form_state');
			if (savedState) {
				try {
					var state = JSON.parse(savedState);
					var savedTime = new Date(state.timestamp);
					var now = new Date();
					var hoursDiff = (now - savedTime) / (1000 * 60 * 60);

					// Only restore if saved within last 24 hours
					if (hoursDiff < 24) {
						if (state.cbg && !$('#cbg').val()) $('#cbg').val(state.cbg);
						if (state.hari && !$('#hari').val()) $('#hari').val(state.hari);
						if (state.filter_tgl && !$('#filter_tgl').is(':checked')) {
							$('#filter_tgl').prop('checked', true).trigger('change');
						}
						if (state.tgl1 && !$('#tgl1').val()) $('#tgl1').val(state.tgl1);
						if (state.tgl2 && !$('#tgl2').val()) $('#tgl2').val(state.tgl2);

						showNotification('info', 'Form state dipulihkan dari session sebelumnya');
					}
				} catch (e) {
					console.error('Error restoring form state:', e);
				}
			}
		}

		// Quick date range presets
		function setDateRange(preset) {
			var today = new Date();
			var startDate, endDate;

			switch (preset) {
				case 'today':
					startDate = endDate = today;
					break;
				case 'yesterday':
					startDate = endDate = new Date(today.setDate(today.getDate() - 1));
					break;
				case 'week':
					endDate = new Date();
					startDate = new Date(endDate.getTime() - 7 * 24 * 60 * 60 * 1000);
					break;
				case 'month':
					endDate = new Date();
					startDate = new Date(endDate.getFullYear(), endDate.getMonth(), 1);
					break;
				case 'quarter':
					endDate = new Date();
					var quarter = Math.floor(endDate.getMonth() / 3);
					startDate = new Date(endDate.getFullYear(), quarter * 3, 1);
					break;
				default:
					return;
			}

			// Enable date filter if not already enabled
			if (!$('#filter_tgl').is(':checked')) {
				$('#filter_tgl').prop('checked', true).trigger('change');
			}

			// Set the dates
			$('#tgl1').val(formatDateToInput(startDate));
			$('#tgl2').val(formatDateToInput(endDate));

			showNotification('success', 'Date range set to: ' + preset);
		}

		function formatDateToInput(date) {
			return date.getFullYear() + '-' +
				String(date.getMonth() + 1).padStart(2, '0') + '-' +
				String(date.getDate()).padStart(2, '0');
		}

		// Add date range presets to UI
		function addDateRangePresets() {
			if ($('#filter_tgl').length && !$('.date-presets').length) {
				var presets = '<div class="date-presets mt-2" style="' +
					($('#filter_tgl').is(':checked') ? '' : 'display:none') + '">' +
					'<small class="text-muted">Quick presets: </small>' +
					'<div class="btn-group btn-group-sm" role="group">' +
					'<button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange(\'today\')">Hari ini</button>' +
					'<button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange(\'yesterday\')">Kemarin</button>' +
					'<button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange(\'week\')">7 Hari</button>' +
					'<button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange(\'month\')">Bulan ini</button>' +
					'<button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange(\'quarter\')">Quarter</button>' +
					'</div>' +
					'</div>';

				$('#tgl2_container').after(presets);

				// Show/hide presets based on filter checkbox
				$('#filter_tgl').on('change', function() {
					if ($(this).is(':checked')) {
						$('.date-presets').show();
					} else {
						$('.date-presets').hide();
					}
				});
			}
		}

		// Enhanced print function with date range info
		function printReport() {
			if (!validateForm()) {
				return false;
			}

			var cbg = $('#cbg').val();
			var hari = $('#hari').val();
			var filterTgl = $('#filter_tgl').is(':checked') ? 1 : 0;
			var tgl1 = $('#tgl1').val();
			var tgl2 = $('#tgl2').val();

			if (cbg) {
				showNotification('info', 'Preparing print report...');

				var form = $('<form method="POST" action="{{ route('jasper-stoknol-report') }}" target="_blank">' +
					'@csrf' +
					'<input type="hidden" name="cbg" value="' + cbg + '">' +
					'<input type="hidden" name="hari" value="' + hari + '">' +
					'<input type="hidden" name="filter_tgl" value="' + filterTgl + '">' +
					'<input type="hidden" name="tgl1" value="' + tgl1 + '">' +
					'<input type="hidden" name="tgl2" value="' + tgl2 + '">' +
					'</form>');

				$('body').append(form);
				form.submit();
				form.remove();
			} else {
				alert('Tidak ada data untuk dicetak. Silakan proses data terlebih dahulu.');
			}
		}

		// Initialize enhanced features
		$(document).ready(function() {
			// Auto-save on form changes
			$('#rStokNolForm input, #rStokNolForm select').on('change', function() {
				autoSaveFormState();
			});

			// Restore form state on page load
			restoreFormState();

			// Add date range presets
			setTimeout(function() {
				addDateRangePresets();
			}, 1000);

			// Initialize tooltips
			$('[data-toggle="tooltip"]').tooltip();

			// Add help tooltips to form elements
			$('#cbg').attr('title', 'Pilih cabang untuk filter data stok nol');
			$('#hari').attr('title', 'Masukkan maksimal selisih hari antara tanggal tanda dan kasir');
			$('#filter_tgl').attr('title', 'Aktifkan untuk filter berdasarkan rentang tanggal');
			$('#tgl1').attr('title', 'Tanggal mulai periode pencarian');
			$('#tgl2').attr('title', 'Tanggal akhir periode pencarian');

			// Add enhanced form styling
			$('<style>')
				.prop('type', 'text/css')
				.html(`
					.form-control:focus {
						border-color: #80bdff;
						box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
					}
					.date-presets .btn-group {
						flex-wrap: wrap;
					}
					.date-presets .btn-sm {
						font-size: 0.7rem;
						padding: 0.2rem 0.4rem;
						margin: 1px;
					}
					.alert-fixed {
						position: fixed;
						top: 20px;
						right: 20px;
						z-index: 9999;
						min-width: 300px;
						box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
					}
					.info-box {
						cursor: pointer;
						transition: transform 0.2s, box-shadow 0.2s;
					}
					.info-box:hover {
						transform: translateY(-2px);
						box-shadow: 0 4px 8px rgba(0,0,0,0.1);
					}
					#tgl1_container, #tgl2_container, .date-presets {
						transition: opacity 0.3s ease, height 0.3s ease;
					}
					.badge {
						font-size: 0.75em;
					}
					.table th {
						position: sticky;
						top: 0;
						background-color: #f8f9fa;
						z-index: 10;
					}
					@media (max-width: 768px) {
						.date-presets .btn-group {
							flex-direction: column;
						}
						.info-box {
							margin-bottom: 1rem;
						}
					}
				`)
				.appendTo('head');

			// Enhanced keyboard shortcuts
			$(document).keydown(function(e) {
				// Ctrl+T for toggle date filter
				if (e.ctrlKey && e.which == 84) {
					e.preventDefault();
					$('#filter_tgl').trigger('click');
				}
				// Ctrl+D for set today date range
				else if (e.ctrlKey && e.which == 68) {
					e.preventDefault();
					setDateRange('today');
				}
				// Ctrl+W for set week date range
				else if (e.ctrlKey && e.which == 87) {
					e.preventDefault();
					setDateRange('week');
				}
				// Ctrl+M for set month date range
				else if (e.ctrlKey && e.which == 77) {
					e.preventDefault();
					setDateRange('month');
				}
			});

			// Add keyboard shortcut info to existing help
			setTimeout(function() {
				if ($('.alert-success').length) {
					var shortcutInfo = '<br><strong>Date Filter Shortcuts:</strong><br>' +
						'• Ctrl+T: Toggle date filter<br>' +
						'• Ctrl+D: Set today<br>' +
						'• Ctrl+W: Set 7 days<br>' +
						'• Ctrl+M: Set this month';
					$('.alert-success').append(shortcutInfo);
				}
			}, 2000);
		});

		// Error handling for date operations
		function handleDateError(error, context) {
			console.error('Date Error (' + context + '):', error);
			showNotification('error', 'Error pada operasi tanggal: ' + context);
		}

		// Date validation helpers
		function isValidDateString(dateString) {
			if (!dateString) return false;
			var date = new Date(dateString);
			return date instanceof Date && !isNaN(date.getTime());
		}

		// ---- taruh di sini, DI LUAR ready ----
		function parseDate(dateString) {
			const parts = dateString.split('-'); // YYYY-MM-DD
			return new Date(parts[0], parts[1] - 1, parts[2]);
		}

		// function isDateInFuture(dateString) {
		// 	var date = new Date(dateString);
		// 	var today = new Date();
		// 	today.setHours(0, 0, 0, 0);
		// 	return date > today;
		// }

		function isDateInFuture(dateString) {
			var date = new Date(dateString);
			date.setHours(0, 0, 0, 0);

			var today = new Date();
			today.setHours(0, 0, 0, 0);

			return date > today;
		}


		// Enhanced date validation
		// function validateDateRange() {
		// 	var tgl1 = $('#tgl1').val();
		// 	var tgl2 = $('#tgl2').val();
		// 	var filterTglChecked = $('#filter_tgl').is(':checked');

		// 	console.log("tgl1:", tgl1, "tgl2:", tgl2);
		// 	console.log("startDate:", startDate);
		// 	console.log("endDate:", endDate);
		// 	console.log("isFuture(tgl1):", isDateInFuture(tgl1));
		// 	console.log("isFuture(tgl2):", isDateInFuture(tgl2));
			
		// 	if (!filterTglChecked) {
		// 		return true;
		// 	}

		// 	if (!tgl1 || !tgl2) {
		// 		showNotification('error', 'Tanggal mulai dan tanggal akhir harus diisi');
		// 		if (!tgl1) $('#tgl1').focus();
		// 		else $('#tgl2').focus();
		// 		return false;
		// 	}

		// 	if (!isValidDateString(tgl1) || !isValidDateString(tgl2)) {
		// 		showNotification('error', 'Format tanggal tidak valid');
		// 		return false;
		// 	}

		// 	var startDate = new Date(tgl1);
		// 	var endDate = new Date(tgl2);

		// 	if (startDate > endDate) {
		// 		showNotification('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
		// 		$('#tgl1').focus();
		// 		return false;
		// 	}

		// 	// Warning for future dates
		// 	if (isDateInFuture(tgl1) || isDateInFuture(tgl2)) {
		// 		if (!confirm('Tanggal yang dipilih berada di masa depan. Data mungkin tidak tersedia. Lanjutkan?')) {
		// 			return false;
		// 		}
		// 	}

		// 	// Check if date range is too large
		// 	var diffTime = Math.abs(endDate - startDate);
		// 	var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

		// 	if (diffDays > 365) {
		// 		if (!confirm('Rentang tanggal lebih dari 1 tahun (' + diffDays + ' hari). Proses mungkin memakan waktu lama. Lanjutkan?')) {
		// 			return false;
		// 		}
		// 	} else if (diffDays > 90) {
		// 		showNotification('warning', 'Rentang tanggal cukup besar (' + diffDays + ' hari). Proses mungkin membutuhkan waktu.');
		// 	}

		// 	return true;
		// }

		function validateDateRange() {
			var tgl1 = $('#tgl1').val();
			var tgl2 = $('#tgl2').val();
			var filterTglChecked = $('#filter_tgl').is(':checked');

			// Gunakan parseDate() yang kita buat
			var startDate = parseDate(tgl1);
			var endDate = parseDate(tgl2);

			console.log("tgl1:", tgl1, "tgl2:", tgl2);
			console.log("startDate:", startDate);
			console.log("endDate:", endDate);
			console.log("isFuture(tgl1):", isDateInFuture(tgl1));
			console.log("isFuture(tgl2):", isDateInFuture(tgl2));

			if (!filterTglChecked) {
				return true;
			}

			if (!tgl1 || !tgl2) {
				showNotification('error', 'Tanggal mulai dan tanggal akhir harus diisi');
				if (!tgl1) $('#tgl1').focus();
				else $('#tgl2').focus();
				return false;
			}

			if (!isValidDateString(tgl1) || !isValidDateString(tgl2)) {
				showNotification('error', 'Format tanggal tidak valid');
				return false;
			}

			// tanggal mundur OK
			if (startDate > endDate) {
				showNotification('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
				$('#tgl1').focus();
				return false;
			}

			// Warning ketika FUTURE date
			if (isDateInFuture(tgl1) || isDateInFuture(tgl2)) {
				if (!confirm('Tanggal yang dipilih berada di masa depan. Data mungkin tidak tersedia. Lanjutkan?')) {
					return false;
				}
			}

			var diffTime = Math.abs(endDate - startDate);
			var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

			if (diffDays > 365) {
				if (!confirm('Rentang tanggal lebih dari 1 tahun (' + diffDays + ' hari). Proses mungkin memakan waktu lama. Lanjutkan?')) {
					return false;
				}
			} else if (diffDays > 90) {
				showNotification('warning', 'Rentang tanggal cukup besar (' + diffDays + ' hari). Proses mungkin membutuhkan waktu.');
			}

			return true;
		}

		// Clean up and final initialization
		$(document).ready(function() {
			// Add version info for debugging
			console.log('Stok Nol Report with Enhanced Date Filter v2.0 - Initialized');

			// Add final touches to form
			setTimeout(function() {
				// Add border to date filter section when active
				$('#filter_tgl').on('change', function() {
					if ($(this).is(':checked')) {
						$('#tgl1_container, #tgl2_container').addClass('border rounded p-2 bg-light');
					} else {
						$('#tgl1_container, #tgl2_container').removeClass('border rounded p-2 bg-light');
					}
				});

				// Trigger change event to set initial state
				$('#filter_tgl').trigger('change');

				// Focus management for better UX
				$('#tgl1').on('change', function() {
					if ($(this).val() && !$('#tgl2').val()) {
						$('#tgl2').focus();
					}
				});

			}, 1500);
		});
	</script>

	<style>
		/* Enhanced styling for date filter */
		.date-filter-section {
			background-color: #f8f9fa;
			border-radius: 0.375rem;
			padding: 0.5rem;
			margin: 0.25rem 0;
		}

		.form-check-input:checked {
			background-color: #007bff;
			border-color: #007bff;
		}

		.btn-group-sm>.btn,
		.btn-sm {
			font-size: 0.775rem;
		}

		/* Responsive adjustments */
		@media (max-width: 1200px) {

			.col-3,
			.col-2,
			.col-1 {
				margin-bottom: 0.5rem;
			}
		}

		@media (max-width: 768px) {
			.row.align-items-end>[class*="col-"] {
				margin-bottom: 1rem;
			}

			.date-presets .btn-group {
				width: 100%;
			}

			.date-presets .btn-sm {
				flex: 1;
			}
		}

		/* Print optimization */
		@media print {

			.date-presets,
			.btn,
			.form-check,
			.alert {
				display: none !important;
			}
		}

		/* Animation for smooth transitions */
		.fade-in {
			animation: fadeIn 0.3s ease-in;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(-10px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}
	</style>
@endsection
