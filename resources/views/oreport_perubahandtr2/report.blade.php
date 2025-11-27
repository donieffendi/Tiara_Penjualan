@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Form Report Perubahan DTR2</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Form Report Perubahan DTR2</li>
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
								<form method="GET" action="{{ route('get-perubahandtr2-report') }}" id="rDtrForm">
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

											<div class="col-3 mb-2">
												<label for="tanggal">Tanggal</label>
												<input type="date" name="tanggal" id="tanggal" class="form-control"
													value="{{ session()->get('filter_tanggal') ?: $defaultDate ?? date('Y-m-d') }}" required>
											</div>

											<div class="col-6 mb-2 text-right">
												<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
													<i class="fas fa-search mr-1"></i>Proses
												</button>
												<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
													<i class="fas fa-undo mr-1"></i>Reset
												</button>
												<button class="btn btn-success mr-1" type="submit" name="action" value="cetak" formaction="{{ route('jasper-perubahandtr2-report') }}"
													formmethod="POST" formtarget="_blank">
													<i class="fas fa-print mr-1"></i>Cetak
												</button>
												<button class="btn btn-info" type="button" onclick="exportData()">
													<i class="fas fa-download mr-1"></i>Excel
												</button>
											</div>
										</div>

										@if (session()->get('filter_cbg') && session()->get('filter_tanggal'))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-info">
														<strong>Filter Aktif:</strong>
														Cabang: {{ session()->get('filter_cbg') }} |
														Jenis: HAPUS_DTR2 |
														Tanggal: {{ date('d/m/Y', strtotime(session()->get('filter_tanggal'))) }}
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
										<div class="report-content" col-md-12>
											@if ($hasilSinkron && count($hasilSinkron) > 0)
												<?php
												// Create custom data for KoolDataTables sesuai dengan struktur R DTR
												$tableData = [];
												foreach ($hasilSinkron as $item) {
												    $tableData[] = [
												        'SUB_ITEM' => $item['SUB_ITEM'] ?? ($item['SUB'] ?? ''),
												        'NA_BRG' => $item['NA_BRG'] ?? '',
												        'KET_UK' => $item['KET_UK'] ?? ($item['UKURAN'] ?? ''),
												        'GOL' => $item['GOL'] ?? ($item['GOLONGAN'] ?? ''),
												        'KET_KEM' => $item['KET_KEM'] ?? ($item['KEMASAN'] ?? ''),
												        'DTR' => $item['DTR'] ?? ($item['DTR_LAMA'] ?? 0),
												        'DTR2' => $item['DTR2'] ?? ($item['DTR_BARU'] ?? 0),
												        'SUPP' => $item['SUPP'] ?? ($item['SUPPLIER'] ?? ''),
												        'TGL_MULAI' => $item['TGL_MULAI'] ?? ($item['TANGGAL_MULAI'] ?? ''),
												        'TGL_AKHIR' => $item['TGL_AKHIR'] ?? ($item['TANGGAL_AKHIR'] ?? ''),
												        // Hidden fields for export/reference
												        'KD_BRG' => $item['KD_BRG'] ?? '',
												        'CBG' => $item['CBG'] ?? '',
												        'STATUS' => $item['STATUS'] ?? '',
												        'KETERANGAN' => $item['KETERANGAN'] ?? '',
												        'USER_INPUT' => $item['USER_INPUT'] ?? '',
												        'TGL_INPUT' => $item['TGL_INPUT'] ?? '',
												    ];
												}

												KoolDataTables::create([
												    'dataSource' => $tableData,
												    'name' => 'rDtrTable',
												    'fastRender' => true,
												    'fixedHeader' => true,
												    'scrollX' => true,
												    'showFooter' => true,
												    'showFooter' => 'bottom',
												    'columns' => [
												        'SUB_ITEM' => [
												            'label' => 'Sub Item',
												        ],
												        'NA_BRG' => [
												            'label' => 'Nama Barang',
												        ],
												        'KET_UK' => [
												            'label' => 'Ukuran',
												        ],
												        'GOL' => [
												            'label' => 'Gol',
												        ],
												        'KET_KEM' => [
												            'label' => 'Kemasan',
												        ],
												        'DTR' => [
												            'label' => 'DTR',
												            'type' => 'number',
												            'decimals' => 2,
												            'decimalPoint' => '.',
												            'thousandSeparator' => ',',
												        ],
												        'DTR2' => [
												            'label' => 'DTR2',
												            'type' => 'number',
												            'decimals' => 2,
												            'decimalPoint' => '.',
												            'thousandSeparator' => ',',
												        ],
												        'SUPP' => [
												            'label' => 'Supp',
												        ],
												        'TGL_MULAI' => [
												            'label' => 'Tgl Mulai',
												        ],
												        'TGL_AKHIR' => [
												            'label' => 'Tgl Akhir',
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
												                'targets' => [5, 6], // DTR, DTR2
												            ],
												            [
												                'className' => 'dt-center',
												                'targets' => [0, 3, 7, 8, 9], // SUB_ITEM, GOL, SUPP, TGL_MULAI, TGL_AKHIR
												            ],
												            // Custom rendering for DTR comparison
												            [
												                'targets' => [5], // DTR column
												                'render' => 'function(data, type, row, meta) {
																																																												                    var dtr = parseFloat(data) || 0;
																																																												                    var dtr2 = parseFloat(row.DTR2) || 0;

																																																												                    if (dtr !== dtr2) {
																																																												                        return "<span class=\"text-warning font-weight-bold\">" + dtr.toLocaleString("id-ID", {minimumFractionDigits: 2}) + "</span>";
																																																												                    } else {
																																																												                        return dtr.toLocaleString("id-ID", {minimumFractionDigits: 2});
																																																												                    }
																																																												                }',
												            ],
												            // Custom rendering for DTR2
												            [
												                'targets' => [6], // DTR2 column
												                'render' => 'function(data, type, row, meta) {
																																																												                    var dtr = parseFloat(row.DTR) || 0;
																																																												                    var dtr2 = parseFloat(data) || 0;

																																																												                    if (dtr !== dtr2) {
																																																												                        if (dtr2 > dtr) {
																																																												                            return "<span class=\"text-success font-weight-bold\">" + dtr2.toLocaleString("id-ID", {minimumFractionDigits: 2}) + "</span>";
																																																												                        } else {
																																																												                            return "<span class=\"text-danger font-weight-bold\">" + dtr2.toLocaleString("id-ID", {minimumFractionDigits: 2}) + "</span>";
																																																												                        }
																																																												                    } else {
																																																												                        return dtr2.toLocaleString("id-ID", {minimumFractionDigits: 2});
																																																												                    }
																																																												                }',
												            ],
												            // Custom rendering for Supplier
												            [
												                'targets' => [7], // SUPP column
												                'render' => 'function(data, type, row, meta) {
																																																												                    if (data && data.length > 0) {
																																																												                        return "<span class=\"badge badge-secondary\">" + data + "</span>";
																																																												                    } else {
																																																												                        return "<span class=\"text-muted\">-</span>";
																																																												                    }
																																																												                }',
												            ],
												            // Custom rendering for Tanggal Mulai
												            [
												                'targets' => [8], // TGL_MULAI column
												                'render' => 'function(data, type, row, meta) {
																																																												                    if (data && data.length > 0) {
																																																												                        return "<span class=\"text-primary\">" + data + "</span>";
																																																												                    } else {
																																																												                        return "<span class=\"text-muted\">-</span>";
																																																												                    }
																																																												                }',
												            ],
												            // Custom rendering for Tanggal Akhir
												            [
												                'targets' => [9], // TGL_AKHIR column
												                'render' => 'function(data, type, row, meta) {
																								                    if (data && data.length > 0) {
																								                        var today = new Date();
																								                        var endDate = new Date(data);

																								                        if (endDate < today) {
																								                            return "<span class=\"text-danger\">" + data + "</span>";
																								                        } else {
																								                            return "<span class=\"text-success\">" + data + "</span>";
																								                        }
																								                    } else {
																								                        return "<span class=\"text-muted\">-</span>";
																								                    }
																								                }',
												            ],
												        ],
												        'order' => [[0, 'asc']], // Order by Sub Item
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
												    ],
												]);
												?>

												<div class="row mt-3">
													<div class="col-12">
														<div class="alert alert-success">
															<i class="fas fa-info-circle mr-2"></i>
															<strong>Informasi Data:</strong><br>
															• <span class="text-success font-weight-bold">DTR2 Naik</span> = Nilai DTR2 lebih besar dari DTR<br>
															• <span class="text-danger font-weight-bold">DTR2 Turun</span> = Nilai DTR2 lebih kecil dari DTR<br>
															• <span class="text-warning font-weight-bold">Berubah</span> = Ada perubahan DTR<br>
															• <span class="text-primary">Aktif</span> = Masih dalam periode berlaku<br>
															• <span class="text-danger">Expired</span> = Sudah melewati tanggal akhir<br>
															• Total data: <strong>{{ count($hasilSinkron) }}</strong> record perubahan DTR2
														</div>
													</div>
												</div>
											@elseif(request()->has('action') && request()->get('action') == 'filter')
												<div class="alert alert-warning text-center">
													<i class="fas fa-exclamation-triangle mr-2"></i>
													Tidak ada data perubahan DTR2 untuk filter yang dipilih. Silakan coba dengan parameter yang berbeda.
												</div>
											@else
												<div class="alert alert-info text-center">
													<i class="fas fa-info-circle mr-2"></i>
													Silakan pilih cabang dan tanggal untuk menampilkan data perubahan DTR2.
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

	<!-- Modal untuk Detail Data -->
	<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="detailModalLabel">Detail Data Perubahan DTR2</h5>
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
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script>
		$(document).ready(function() {
			// Set default date if empty - sesuai dengan DtTgl.date:=date di Delphi
			if (!$('#tanggal').val()) {
				$('#tanggal').val(new Date().toISOString().split('T')[0]);
			}

			// Auto-focus on tanggal when cabang is selected
			$('#cbg').on('change', function() {
				if ($(this).val()) {
					$('#tanggal').focus();
				}
			});

			// Form validation sesuai dengan logika Delphi
			$('#rDtrForm').on('submit', function(e) {
				if (!validateForm()) {
					e.preventDefault();
				}
			});

			// Enter key handling - seperti behavior di Delphi
			$('#tanggal').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					if (validateForm()) {
						$(this).closest('form').find('button[name="action"][value="filter"]').click();
					}
				}
			});

			// Keyboard shortcuts sesuai dengan Delphi behavior
			$(document).keydown(function(e) {
				// F9 untuk proses (seperti btnProsesClick)
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
			});
		});

		// Reset form function
		function resetForm() {
			window.location.href = '{{ route('rperubahandtr2') }}';
		}

		// Form validation sesuai dengan requirement R DTR
		function validateForm() {
			var cbg = $('#cbg').val();
			var tanggal = $('#tanggal').val();

			if (!cbg) {
				alert('Harap pilih cabang');
				$('#cbg').focus();
				return false;
			}

			if (!tanggal) {
				alert('Harap masukkan tanggal');
				$('#tanggal').focus();
				return false;
			}

			// Validate date format
			var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
			if (!dateRegex.test(tanggal)) {
				alert('Format tanggal tidak valid');
				$('#tanggal').focus();
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
				var tanggal = $('#tanggal').val();

				if (cbg && tanggal) {
					window.open('{{ url('/export-perubahandtr2-excel') }}?cbg=' + cbg + '&tanggal=' + tanggal, '_blank');
				} else {
					alert('Tidak ada data untuk di-export. Silakan proses data terlebih dahulu.');
				}
			}
		}

		// Show detail modal dengan informasi DTR
		function showDetail(kdBrg, naBrg, dtr, dtr2, status, keterangan) {
			$('#detailModalLabel').text('Detail Perubahan DTR: ' + kdBrg);

			var perubahan = '';
			var dtrValue = parseFloat(dtr) || 0;
			var dtr2Value = parseFloat(dtr2) || 0;

			if (dtr2Value > dtrValue) {
				perubahan = '<span class="text-success">DTR Naik dari ' + dtrValue + ' ke ' + dtr2Value + '</span>';
			} else if (dtr2Value < dtrValue) {
				perubahan = '<span class="text-danger">DTR Turun dari ' + dtrValue + ' ke ' + dtr2Value + '</span>';
			} else {
				perubahan = '<span class="text-muted">DTR tidak berubah (' + dtrValue + ')</span>';
			}

			var content = '<div class="row">' +
				'<div class="col-12">' +
				'<h6>Nama Barang: ' + naBrg + '</h6>' +
				'<p><strong>Perubahan:</strong> ' + perubahan + '</p>' +
				'<p><strong>Status:</strong> ' + status + '</p>' +
				'<p><strong>Keterangan:</strong> ' + keterangan + '</p>' +
				'<hr>' +
				'<p class="text-muted">Detail lengkap perubahan DTR2 untuk barang ' + kdBrg + '</p>' +
				'</div>' +
				'</div>';

			$('#modalContent').html(content);
			$('#detailModal').modal('show');
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

		// Custom styling untuk visualisasi data DTR
		$(document).ready(function() {
			setTimeout(function() {
				// Add custom CSS for better visualization
				$('<style>')
					.prop('type', 'text/css')
					.html(`
						.dt-center { text-align: center !important; }
						.dt-right { text-align: right !important; }
						.dtr-changed {
							background-color: #fff3cd !important;
							border-left: 4px solid #ffc107 !important;
						}
						.dtr-increased {
							background-color: #d4edda !important;
							border-left: 4px solid #28a745 !important;
						}
						.dtr-decreased {
							background-color: #f8d7da !important;
							border-left: 4px solid #dc3545 !important;
						}
						.table td {
							font-size: 0.875rem;
						}
						.badge {
							font-size: 0.75em;
						}
						.form-control:focus {
							border-color: #80bdff;
							box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
						}
					`)
					.appendTo('head');
			}, 1000);
		});

		// Add row highlighting based on DTR changes
		$(document).on('draw.dt', '#rDtrTable', function() {
			$('#rDtrTable tbody tr').each(function() {
				var row = $(this);
				var dtrCell = row.find('td').eq(5); // DTR column
				var dtr2Cell = row.find('td').eq(6); // DTR2 column

				var dtrText = dtrCell.text().replace(/[^0-9.-]/g, '');
				var dtr2Text = dtr2Cell.text().replace(/[^0-9.-]/g, '');

				var dtrValue = parseFloat(dtrText) || 0;
				var dtr2Value = parseFloat(dtr2Text) || 0;

				if (dtr2Value > dtrValue) {
					row.addClass('dtr-increased');
				} else if (dtr2Value < dtrValue) {
					row.addClass('dtr-decreased');
				} else if (dtrValue !== 0 && dtr2Value !== 0) {
					row.addClass('dtr-changed');
				}
			});
		});

		// Initialize tooltips and popovers
		$(function() {
			$('[data-toggle="tooltip"]').tooltip();
			$('[data-toggle="popover"]').popover();
		});

		// Auto-save form state untuk user experience
		$('#cbg, #tanggal').on('change', function() {
			// Save form state for better UX (implementation would depend on requirements)
		});

		// Refresh data function jika diperlukan
		function refreshData() {
			if (validateForm()) {
				$('#rDtrForm').find('button[name="action"][value="filter"]').click();
			}
		}
	</script>
@endsection
