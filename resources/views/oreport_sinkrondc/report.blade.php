@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Form Report Sinkronisasi DC</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Form Report Sinkronisasi DC</li>
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
								<form method="GET" action="{{ route('get-sinkrondc-report') }}" id="sinkronDcForm">
									@csrf
									<div class="form-group">
										<div class="row align-items-end">
											<div class="col-2 mb-2">
												<label for="jenis">Jenis Report</label>
												<select id="jenis" class="form-control"  name="jenis">
													<option value="-" {{ session('filter_jenis') == '-' ? 'selected' : '' }} disable selected hidden>--Pilih Jenis Report--</option>
													<option value="TANDA_DC" {{ session('filter_jenis') == 'TANDA_DC' ? 'selected' : '' }}>TANDA * DC</option>
												</select>
											</div>

											<div class="col-2 mb-2">
												<label for="tanggal">Dari Tanggal</label>
												<input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ session()->get('filter_tanggal') ?: date('Y-m-d') }}"
													required>
											</div>

											<div class="col-2 mb-2">
												<label for="tanggal2">Sampai Tanggal</label>
												<input type="date" name="tanggal2" id="tanggal2" class="form-control" value="{{ session()->get('filter_tanggal2') ?: date('Y-m-d') }}"
													required>
											</div>

											<div class="col-6 mb-2 text-right">
												<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
													<i class="fas fa-search mr-1"></i>Tampil
												</button>
												<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
													<i class="fas fa-undo mr-1"></i>Reset
												</button>
												<button class="btn btn-success mr-1" type="submit" name="action" value="cetak" formaction="{{ route('jasper-sinkrondc-report') }}"
													formmethod="POST" formtarget="_blank">
													<i class="fas fa-print mr-1"></i>Cetak
												</button>
												<button class="btn btn-info" type="button" onclick="exportData()">
													<i class="fas fa-download mr-1"></i>Excel
												</button>
											</div>
										</div>

										@if (session()->get('filter_cbg') && session()->get('filter_jenis') && session()->get('filter_tanggal'))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-info">
														<strong>Filter Aktif:</strong>
														{{-- Cabang: {{ session()->get('filter_cbg') }} | --}}
														Jenis: {{ session()->get('filter_jenis') }} |
														Tanggal: {{ date('d/m/Y', strtotime(session()->get('filter_tanggal'))) }}
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
												// Create custom data for KoolDataTables
												$tableData = [];
												foreach ($hasilSinkron as $item) {
												    $tableData[] = [
												        'SUB_ITEM' => $item['SUB'] ?? '',
												        'NA_BRG' => $item['NA_BRG'] ?? '',
												        'KET_UK' => $item['KET_UK'] ?? '',
												        'KET_KEM' => $item['KET_KEM'] ?? '',
												        'LPH' => $item['LPH'] ?? 0,
												        'STOK_TOKO' => $item['STOK_TOKO'] ?? ($item['STOCK_TOKO'] ?? 0),
												        'STOK_GUDANG' => $item['STOK_GUDANG'] ?? ($item['STOCK_DC'] ?? ($item['STOK_DC'] ?? 0)),
												        'ALASAN' => $item['ALASAN'] ?? ($item['KETERANGAN'] ?? ''),
												        'TANGGAL' => $item['TANGGAL'] ?? date('d/m/Y', strtotime(session()->get('filter_tanggal'))),
												        'TGL_SINKRON_DC' => $item['TGL_SINKRON_DC'] ?? ($item['TANGGAL_SINKRON'] ?? ''),
												        'ORDER_KE_DC' => $item['ORDER_KE_DC'] ?? ($item['ORDER_DC'] ?? 0),
												        // Hidden fields for export/reference
												        'KD_BRG' => $item['KD_BRG'] ?? '',
												        'CBG' => $item['CBG'] ?? '',
												    ];
												}

												DataTables::create([
												    'dataSource' => $tableData,
												    'name' => 'sinkronDcTable',
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
												        'KET_KEM' => [
												            'label' => 'Kemasan',
												        ],
												        'LPH' => [
												            'label' => 'LPH',
												            'type' => 'number',
												            'decimals' => 2,
												            'decimalPoint' => '.',
												            'thousandSeparator' => ',',
												        ],
												        'STOK_TOKO' => [
												            'label' => 'Stok Toko',
												            'type' => 'number',
												            'decimals' => 0,
												            'decimalPoint' => '.',
												            'thousandSeparator' => ',',
												        ],
												        'STOK_GUDANG' => [
												            'label' => 'Stok Gudang',
												            'type' => 'number',
												            'decimals' => 0,
												            'decimalPoint' => '.',
												            'thousandSeparator' => ',',
												        ],
												        'ALASAN' => [
												            'label' => 'Alasan',
												        ],
												        'TANGGAL' => [
												            'label' => 'Tanggal',
												        ],
												        'TGL_SINKRON_DC' => [
												            'label' => 'Tanggal Sinkron DC',
												        ],
												        'ORDER_KE_DC' => [
												            'label' => 'Order Ke DC',
												            'type' => 'number',
												            'decimals' => 0,
												            'decimalPoint' => '.',
												            'thousandSeparator' => ',',
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
												                'targets' => [4, 5, 6, 10], // LPH, STOK_TOKO, STOK_GUDANG, ORDER_KE_DC
												            ],
												            [
												                'className' => 'dt-center',
												                'targets' => [0, 8, 9], // SUB_ITEM, TANGGAL, TGL_SINKRON_DC
												            ],
												            // Custom rendering for Alasan
												            [
												                'targets' => [7], // ALASAN column
												                'render' => 'function(data, type, row, meta) {
																																																                    if (data && data.length > 0) {
																																																                        if (data.toLowerCase().includes("error") || data.toLowerCase().includes("gagal")) {
																																																                            return "<span class=\"badge badge-danger\">" + data + "</span>";
																																																                        } else if (data.toLowerCase().includes("warning") || data.toLowerCase().includes("peringatan")) {
																																																                            return "<span class=\"badge badge-warning\">" + data + "</span>";
																																																                        } else {
																																																                            return "<span class=\"badge badge-info\">" + data + "</span>";
																																																                        }
																																																                    }
																																																                    return data;
																																																                }',
												            ],
												            // Custom rendering for TGL_SINKRON_DC
												            [
												                'targets' => [9], // TGL_SINKRON_DC column
												                'render' => 'function(data, type, row, meta) {
																																																                    if (data && data.length > 0) {
																																																                        return "<span class=\"text-success\">" + data + "</span>";
																																																                    } else {
																																																                        return "<span class=\"text-muted\">Belum sinkron</span>";
																																																                    }
																																																                }',
												            ],
												            // Custom rendering for ORDER_KE_DC
												            [
												                'targets' => [10], // ORDER_KE_DC column
												                'render' => 'function(data, type, row, meta) {
																																																                    if (data > 0) {
																																																                        return "<span class=\"text-primary font-weight-bold\">" + parseFloat(data).toLocaleString("id-ID", {minimumFractionDigits: 0}) + "</span>";
																																																                    } else {
																																																                        return "<span class=\"text-muted\">0</span>";
																																																                    }
																																																                }',
												            ],
												            // Custom rendering for STOK_GUDANG
												            [
												                'targets' => [6], // STOK_GUDANG column
												                'render' => 'function(data, type, row, meta) {
																																																                    var stokToko = parseFloat(row.STOK_TOKO) || 0;
																																																                    var stokGudang = parseFloat(data) || 0;

																																																                    if (stokGudang > stokToko) {
																																																                        return "<span class=\"text-success font-weight-bold\">" + stokGudang.toLocaleString("id-ID", {minimumFractionDigits: 0}) + "</span>";
																																																                    } else if (stokGudang < stokToko) {
																																																                        return "<span class=\"text-warning font-weight-bold\">" + stokGudang.toLocaleString("id-ID", {minimumFractionDigits: 0}) + "</span>";
																																																                    } else {
																																																                        return stokGudang.toLocaleString("id-ID", {minimumFractionDigits: 0});
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
															• <span class="badge badge-success">MATCH/OK</span> = Data sudah sesuai<br>
															• <span class="badge badge-warning">DIFF</span> = Ada perbedaan data<br>
															• <span class="badge badge-danger">ERROR</span> = Terjadi kesalahan<br>
															• <span class="text-success font-weight-bold">Positif (+)</span> = Nilai lebih besar<br>
															• <span class="text-danger font-weight-bold">Negatif (-)</span> = Nilai lebih kecil<br>
															• Total data: <strong>{{ count($hasilSinkron) }}</strong> record
														</div>
													</div>
												</div>
											@elseif(request()->has('action') && request()->get('action') == 'filter')
												<div class="alert alert-warning text-center">
													<i class="fas fa-exclamation-triangle mr-2"></i>
													Tidak ada data untuk filter yang dipilih. Silakan coba dengan parameter yang berbeda.
												</div>
											@else
												<div class="alert alert-info text-center">
													<i class="fas fa-info-circle mr-2"></i>
													Silakan pilih jenis report, dan tanggal untuk menampilkan data sinkronisasi DC.
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
					<h5 class="modal-title" id="detailModalLabel">Detail Data Sinkronisasi</h5>
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
			// Load jenis options when cabang is selected
			$('#cbg').on('change', function() {
				var cbg = $(this).val();
				var jenisSelect = $('#jenis');

				jenisSelect.empty().append('<option value="">Loading...</option>');

				if (cbg) {
					$.ajax({
						url: '{{ url('/get-jenis-options-by-cabang') }}',
						type: 'GET',
						data: {
							cbg: cbg
						},
						success: function(response) {
							jenisSelect.empty().append('<option value="">Pilih Jenis Report</option>');
							if (response.success && response.data && response.data.length > 0) {
								$.each(response.data, function(index, item) {
									var selected = '{{ session()->get('filter_jenis') }}' == item ? 'selected' : '';
									jenisSelect.append('<option value="' + item + '" ' + selected + '>' + item +
										'</option>');
								});
							} else {
								jenisSelect.append('<option value="">Tidak ada jenis report tersedia</option>');
							}
						},
						error: function(xhr, status, error) {
							jenisSelect.empty().append('<option value="">Error loading jenis report</option>');
							console.error('Error loading jenis report:', error);
						}
					});
				} else {
					jenisSelect.empty().append('<option value="">Pilih Jenis Report</option>');
				}
			});

			// Trigger change event if cbg has value on page load
			if ($('#cbg').val()) {
				$('#cbg').trigger('change');
			}

			// Auto-focus on jenis when cabang is selected
			$('#cbg').on('change', function() {
				if ($(this).val()) {
					setTimeout(function() {
						$('#jenis').focus();
					}, 500);
				}
			});

			// Auto-focus on tanggal when jenis is selected
			$('#jenis').on('change', function() {
				if ($(this).val()) {
					$('#tanggal').focus();
				}
			});

			// Form validation
			$('#sinkronDcForm').on('submit', function(e) {
				if (!validateForm()) {
					e.preventDefault();
				}
			});

			// Enter key handling
			$('#tanggal').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					if (validateForm()) {
						$(this).closest('form').find('button[name="action"][value="filter"]').click();
					}
				}
			});

			// Set default date if empty
			if (!$('#tanggal').val()) {
				$('#tanggal').val(new Date().toISOString().split('T')[0]);
			}
		});

		// Reset form function
		function resetForm() {
			window.location.href = '{{ route('rsinkrondc') }}';
		}

		// Form validation
		function validateForm() {
			var cbg = $('#cbg').val();
			var jenis = $('#jenis').val();
			var tanggal = $('#tanggal').val();

			// if (!cbg) {
			// 	alert('Harap pilih cabang');
			// 	$('#cbg').focus();
			// 	return false;
			// }

			if (!jenis) {
				alert('Harap pilih jenis report');
				$('#jenis').focus();
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

		// Export functions
		function exportData() {
			if (!validateForm()) {
				return false;
			}

			if (typeof window.sinkronDcTable !== 'undefined') {
				// Direct excel export via DataTables
				window.sinkronDcTable.button('.buttons-excel').trigger();
			} else {
				// Fallback: redirect to export URL
				// var cbg = $('#cbg').val();
				var jenis = $('#jenis').val();
				var tanggal = $('#tanggal').val();

				if (cbg && jenis && tanggal) {
					window.open('{{ url('/export-sinkrondc-excel') }}?jenis=' + jenis + '&tanggal=' + tanggal, '_blank');
				} else {
					alert('Tidak ada data untuk di-export. Silakan proses data terlebih dahulu.');
				}
			}
		}

		// Show detail modal
		function showDetail(kdBrg, naBrg, status, keterangan) {
			$('#detailModalLabel').text('Detail Data: ' + kdBrg);

			var content = '<div class="row">' +
				'<div class="col-12">' +
				'<h6>Nama Barang: ' + naBrg + '</h6>' +
				'<p><strong>Status:</strong> ' + status + '</p>' +
				'<p><strong>Keterangan:</strong> ' + keterangan + '</p>' +
				'<hr>' +
				'<p class="text-muted">Detail lengkap data sinkronisasi untuk barang ' + kdBrg + '</p>' +
				'</div>' +
				'</div>';

			$('#modalContent').html(content);
			$('#detailModal').modal('show');
		}

		// Custom styling for data visualization
		$(document).ready(function() {
			setTimeout(function() {
				// Add custom CSS for better visualization
				$('<style>')
					.prop('type', 'text/css')
					.html(`
						.dt-center { text-align: center !important; }
						.dt-right { text-align: right !important; }
						.status-match {
							background-color: #d4edda !important;
							border-left: 4px solid #28a745 !important;
						}
						.status-diff {
							background-color: #fff3cd !important;
							border-left: 4px solid #ffc107 !important;
						}
						.status-error {
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

		// Add row highlighting based on status
		$(document).on('draw.dt', '#sinkronDcTable', function() {
			$('#sinkronDcTable tbody tr').each(function() {
				var row = $(this);
				var statusCell = row.find('td').eq(13); // STATUS column

				if (statusCell.text().includes('MATCH') || statusCell.text().includes('OK')) {
					row.addClass('status-match');
				} else if (statusCell.text().includes('DIFF')) {
					row.addClass('status-diff');
				} else if (statusCell.text().includes('ERROR')) {
					row.addClass('status-error');
				}
			});
		});

		// Loading indicator functions
		function showLoading() {
			$('button[type="submit"]').prop('disabled', true);
			$('button[name="action"][value="filter"]').html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
		}

		function hideLoading() {
			$('button[type="submit"]').prop('disabled', false);
			$('button[name="action"][value="filter"]').html('<i class="fas fa-search mr-1"></i>Tampil');
			$('button[name="action"][value="cetak"]').html('<i class="fas fa-print mr-1"></i>Cetak');
		}

		// Show loading on form submit
		$('#sinkronDcForm').on('submit', function() {
			if (validateForm()) {
				showLoading();
			}
		});

		// Hide loading when page loads
		$(window).on('load', function() {
			hideLoading();
		});

		// Auto-save form state in localStorage (if needed)
		function saveFormState() {
			var formData = {
				cbg: $('#cbg').val(),
				jenis: $('#jenis').val(),
				tanggal: $('#tanggal').val()
			};
			// Note: localStorage tidak tersedia di artifacts, ini hanya contoh
			// localStorage.setItem('sinkronDcForm', JSON.stringify(formData));
		}

		// Keyboard shortcuts
		$(document).keydown(function(e) {
			// Ctrl+Enter to submit form
			if (e.ctrlKey && e.which == 13) {
				e.preventDefault();
				if (validateForm()) {
					$('#sinkronDcForm').find('button[name="action"][value="filter"]').click();
				}
			}
			// Escape to reset form
			else if (e.which == 27) {
				if (confirm('Reset form?')) {
					resetForm();
				}
			}
		});

		// Initialize tooltips and popovers if needed
		$(function() {
			$('[data-toggle="tooltip"]').tooltip();
			$('[data-toggle="popover"]').popover();
		});
	</script>
@endsection
