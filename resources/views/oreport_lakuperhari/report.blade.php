@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-5">
						<h1 class="m-0">Form Laporan Laku Per Hari</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Form Laporan Laku Per Hari</li>
						</ol>

                        <ol class="col-sm-9">
							<li>LPH_FLAT : QTY_LAKU/HARI</li>
							<li>DTR_MAX : QTY_LAKU tertinggi</li>
							<li>LH_USUL : qty_laku/hari + (hari-hari_laku/hari*qty_total/hari)</li>
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
								<form method="GET" action="{{ route('get-lakuperhari-report') }}" id="lakuPerHariForm">
									@csrf
									<div class="form-group">
										<div class="row align-items-end">
											<div class="col-4 mb-2">
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

											<div class="col-4 mb-2">
												<label for="hari">Analisis Laku (Hari)</label>
												<input type="number" name="hari" id="hari" class="form-control" placeholder="Masukkan jumlah hari (misal: 30)"
													value="{{ session()->get('filter_hari') ?? 30 }}" required min="1" max="365">
											</div>

											<div class="col-4 mb-2 text-right">
												<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
													<i class="fas fa-search mr-1"></i>Proses
												</button>
												<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
													<i class="fas fa-undo mr-1"></i>Reset
												</button>
												<button class="btn btn-success mr-1" type="submit" name="action" value="cetak" formaction="{{ route('jasper-lakuperhari-report') }}"
													formmethod="POST" formtarget="_blank">
													<i class="fas fa-print mr-1"></i>Cetak
												</button>
												<button class="btn btn-info" type="button" onclick="exportData()">
													<i class="fas fa-download mr-1"></i>Export
												</button>
											</div>
										</div>

										@if (session()->get('filter_cbg') && session()->get('filter_hari'))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-info">
														<strong>Filter Aktif:</strong>
														Cabang: {{ session()->get('filter_cbg') }} |
														Analisis: {{ session()->get('filter_hari') }} hari terakhir
													</div>
												</div>
											</div>
										@endif

										@if (isset($error))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-danger">
														<i class="fas fa-exclamation-triangle mr-2"></i>
														<strong>Error:</strong> {{ $error }}
													</div>
												</div>
											</div>
										@endif

										<div style="margin-bottom: 15px;"></div>
										<div class="report-content" col-md-12>
											@if ($hasilLakuPerHari && count($hasilLakuPerHari) > 0)
												@php
													// Create custom data for KoolDataTables
													$tableData = [];
													foreach ($hasilLakuPerHari as $item) {
													    $tableData[] = [
													        'KD_BRG' => $item['KD_BRG'] ?? '',
													        'NA_BRG' => $item['NA_BRG'] ?? '',
													        'HARI' => $item['HARI'] ?? 0,
													        'TOTAL_LK' => $item['TOTAL_LK'] ?? 0,
													        'HARI_LK' => $item['HARI_LK'] ?? 0,
													        'LPH' => $item['LPH'] ?? 0,
													        'LHUSUL' => $item['LHUSUL'] ?? 0,
													        'USULANINI' => $item['USULANINI'] ?? 0,
													        'DTRMAX' => $item['DTRMAX'] ?? 0,
													    ];
													}

													// Prepare Excel title with safe string concatenation
													$excelTitle = 'Laporan_Laku_Per_Hari_' . session()->get('filter_cbg') . '_' . session()->get('filter_hari') . 'Hari';

													// Render KoolDataTables
													// KoolDataTables::create([
													\koolreport\datagrid\DataTables::create([
													    'dataSource' => $tableData,
													    'name' => 'lakuPerHariTable',
													    'fastRender' => true,
													    'fixedHeader' => true,
													    'scrollX' => true,
													    'showFooter' => true,
													    'showFooter' => 'bottom',
													    'columns' => [
													        'KD_BRG' => [
													            'label' => 'Sub Item',
													            'type' => 'string',
													        ],
													        'NA_BRG' => [
													            'label' => 'Nama Barang',
													            'type' => 'string',
													        ],
													        'HARI' => [
													            'label' => 'Hari',
													            'type' => 'number',
													            'formatType' => 'number',
													            'format' => '0',
													        ],
													        'TOTAL_LK' => [
													            'label' => 'Total Laku',
													            'type' => 'number',
													            'formatType' => 'number',
													            'format' => '#,##0',
													        ],
													        'HARI_LK' => [
													            'label' => 'Hari Laku',
													            'type' => 'number',
													            'formatType' => 'number',
													            'format' => '0',
													        ],
													        'LPH' => [
													            'label' => 'LH Rat',
													            'type' => 'number',
													            'formatType' => 'number',
													            'format' => '#,##0.00',
													        ],
													        'LHUSUL' => [
													            'label' => 'LH Usul',
													            'type' => 'number',
													            'formatType' => 'number',
													            'format' => '#,##0.00',
													        ],
													        'USULANINI' => [
													            'label' => 'LH Sekarang',
													            'type' => 'number',
													            'formatType' => 'number',
													            'format' => '#,##0.00',
													        ],
													        'DTRMAX' => [
													            'label' => 'DTRmax',
													            'type' => 'number',
													            'formatType' => 'number',
													            'format' => '#,##0',
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
													                'className' => 'dt-center',
													                'targets' => [0, 2, 3, 4, 8], // Sub Item, Hari, Total Laku, Hari Laku, DTRmax
													            ],
													            [
													                'className' => 'dt-left',
													                'targets' => [1], // Nama Barang
													            ],
													            [
													                'className' => 'dt-right',
													                'targets' => [5, 6, 7], // LH Rat, LH Usul, LH Sekarang
													            ],
													            [
													                'width' => '12%',
													                'targets' => [0], // Sub Item
													            ],
													            [
													                'width' => '25%',
													                'targets' => [1], // Nama Barang
													            ],
													            [
													                'width' => '8%',
													                'targets' => [2], // Hari
													            ],
													            [
													                'width' => '10%',
													                'targets' => [3], // Total Laku
													            ],
													            [
													                'width' => '8%',
													                'targets' => [4], // Hari Laku
													            ],
													            [
													                'width' => '10%',
													                'targets' => [5], // LH Rat
													            ],
													            [
													                'width' => '10%',
													                'targets' => [6], // LH Usul
													            ],
													            [
													                'width' => '10%',
													                'targets' => [7], // LH Sekarang
													            ],
													            [
													                'width' => '7%',
													                'targets' => [8], // DTRmax
													            ],
													        ],
													        'order' => [[0, 'asc']], // Order by Sub Item (KD_BRG)
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
													                'buttons' => [
													                    [
													                        'extend' => 'copy',
													                        'text' => 'Copy to Clipboard',
													                    ],
													                    [
													                        'extend' => 'excel',
													                        'text' => 'Export to Excel',
													                        'title' => $excelTitle,
													                    ],
													                    [
													                        'extend' => 'csv',
													                        'text' => 'Export to CSV',
													                    ],
													                    [
													                        'extend' => 'pdf',
													                        'text' => 'Export to PDF',
													                        'title' => 'Laporan Laku Per Hari',
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
													        'language' => [
													            'url' => '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json',
													        ],
													    ],
													]);
												@endphp

												{{-- Summary Statistics --}}
												<div class="row mt-2">
													<div class="col-md-3">
														<div class="small-box bg-info">
															<div class="inner">
																<h3>{{ count($hasilLakuPerHari) }}</h3>
																<p>Total Items</p>
															</div>
															<div class="icon">
																<i class="fas fa-cube"></i>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="small-box bg-success">
															<div class="inner">
																<h3>{{ number_format(array_sum(array_column($hasilLakuPerHari, 'TOTAL_LK')), 0) }}</h3>
																<p>Total Qty Laku</p>
															</div>
															<div class="icon">
																<i class="fas fa-chart-line"></i>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="small-box bg-warning">
															<div class="inner">
																<h3>
																	{{ number_format(count($hasilLakuPerHari) > 0 ? array_sum(array_column($hasilLakuPerHari, 'LHUSUL')) / count($hasilLakuPerHari) : 0, 2) }}
																</h3>
																<p>Avg LH Usul</p>
															</div>
															<div class="icon">
																<i class="fas fa-calculator"></i>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="small-box bg-danger">
															<div class="inner">
																<h3>{{ number_format(count($hasilLakuPerHari) > 0 ? max(array_column($hasilLakuPerHari, 'DTRMAX')) : 0, 0) }}</h3>
																<p>Max DTR</p>
															</div>
															<div class="icon">
																<i class="fas fa-trophy"></i>
															</div>
														</div>
													</div>
												</div>
											@elseif(request()->has('action') && request()->get('action') == 'filter')
												<div class="alert alert-warning text-center">
													<i class="fas fa-exclamation-triangle mr-2"></i>
													Tidak ada data laku ditemukan untuk cabang <strong>{{ request()->get('cbg') }}</strong>
													dalam periode <strong>{{ request()->get('hari') }}</strong> hari terakhir.
													<br><small class="text-muted mt-2">Pastikan cabang dan periode analisis sudah benar.</small>
												</div>
											@else
												<div class="alert alert-info text-center">
													<i class="fas fa-info-circle mr-2"></i>
													Silakan pilih cabang dan masukkan jumlah hari untuk analisis data laku per hari.

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

	<!-- Modal untuk Informasi Analisis -->
	<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="infoModalLabel">Informasi Analisis Laku Per Hari</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="alert alert-info">
						<h6><i class="fas fa-info-circle mr-2"></i>Penjelasan Metode Analisis:</h6>
						<ul>
							<li><strong>Periode Analisis:</strong> Menganalisis data penjualan dari 3 bulan terakhir</li>
							<li><strong>Total Laku:</strong> Jumlah total qty yang terjual dalam periode yang ditentukan</li>
							<li><strong>Hari Laku:</strong> Berapa hari dalam periode tersebut barang tersebut terjual</li>
							<li><strong>LH Usul:</strong> Rata-rata laku per hari (Total Laku / Jumlah Hari Analisis)</li>
							<li><strong>LH Sekarang:</strong> Proyeksi laku per hari dengan mempertimbangkan hari tidak laku</li>
							<li><strong>DTRmax:</strong> Penjualan tertinggi dalam 1 hari untuk barang tersebut</li>
						</ul>
					</div>
					<div class="alert alert-warning">
						<h6><i class="fas fa-exclamation-triangle mr-2"></i>Catatan Penting:</h6>
						<ul>
							<li>Data diambil dari transaksi penjualan (bukan transfer atau promosi)</li>
							<li>Perhitungan menggunakan periode maksimal 365 hari</li>
							<li>Hasil analisis dapat digunakan untuk perencanaan stok dan ordering</li>
						</ul>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal untuk Preview Print -->
	<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="previewModalLabel">Preview Laporan Laku Per Hari</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="previewContent">
						<!-- Content akan dimuat via JavaScript -->
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
					<button type="button" class="btn btn-primary" onclick="printPreview()">
						<i class="fas fa-print mr-1"></i>Print
					</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script>
		$(document).ready(function() {
			// Auto-focus on hari input when cabang is selected
			$('#cbg').on('change', function() {
				if ($(this).val()) {
					setTimeout(function() {
						$('#hari').focus();
					}, 100);
				}
			});

			// Form validation
			$('#lakuPerHariForm').on('submit', function(e) {
				if (!validateForm()) {
					e.preventDefault();
				}
			});

			// Enter key handling untuk hari input
			$('#hari').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					if (validateForm()) {
						$(this).closest('form').find('button[name="action"][value="filter"]').click();
					}
				}
			});

			// Number only input untuk hari
			$('#hari').on('input', function() {
				this.value = this.value.replace(/[^0-9]/g, '');
				if (parseInt(this.value) > 365) {
					this.value = 365;
				}
			});

			// Show info modal by default if first time visit
			@if (!session()->has('lakuperhari_info_shown'))
				setTimeout(function() {
					$('#infoModal').modal('show');
				}, 1000);
				@php session(['lakuperhari_info_shown' => true]); @endphp
			@endif
		});

		// Reset form function
		function resetForm() {
			window.location.href = '{{ route('rlakuperhari') }}';
		}

		// Form validation
		function validateForm() {
			var cbg = $('#cbg').val();
			var hari = parseInt($('#hari').val());

			if (!cbg) {
				alert('Harap pilih cabang');
				$('#cbg').focus();
				return false;
			}

			if (!hari || hari <= 0) {
				alert('Harap masukkan jumlah hari yang valid (minimal 1)');
				$('#hari').focus();
				return false;
			}

			if (hari > 365) {
				alert('Maksimal analisis 365 hari');
				$('#hari').val(365).focus();
				return false;
			}

			return true;
		}

		// Export functions
		function exportData() {
			if (typeof window.lakuPerHariTable !== 'undefined') {
				// Show export options
				var format = prompt('Pilih format export:\n1. Excel\n2. CSV\n3. PDF\n4. Print\nMasukkan nomor pilihan (1-4):');

				switch (format) {
					case '1':
						window.lakuPerHariTable.button('.buttons-excel').trigger();
						break;
					case '2':
						window.lakuPerHariTable.button('.buttons-csv').trigger();
						break;
					case '3':
						window.lakuPerHariTable.button('.buttons-pdf').trigger();
						break;
					case '4':
						window.lakuPerHariTable.button('.buttons-print').trigger();
						break;
					default:
						if (format !== null) {
							alert('Pilihan tidak valid');
						}
				}
			} else {
				alert('Tidak ada data untuk di-export. Silakan proses data terlebih dahulu.');
			}
		}

		// Loading indicator functions
		function showLoading() {
			$('button[type="submit"]').prop('disabled', true);
			$('button[name="action"][value="filter"]').html('<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...');
		}

		function hideLoading() {
			$('button[type="submit"]').prop('disabled', false);
			$('button[name="action"][value="filter"]').html('<i class="fas fa-search mr-1"></i>Proses');
			$('button[name="action"][value="cetak"]').html('<i class="fas fa-print mr-1"></i>Cetak');
		}

		// Show loading on form submit
		$('#lakuPerHariForm').on('submit', function() {
			if (validateForm()) {
				showLoading();
			}
		});

		// Hide loading when page loads
		$(window).on('load', function() {
			hideLoading();
		});

		// Preview print function
		function previewPrint() {
			var cbg = $('#cbg').val();
			var hari = $('#hari').val();

			if (!cbg || !hari) {
				alert('Pilih cabang dan masukkan jumlah hari terlebih dahulu');
				return;
			}

			$('#previewModal').modal('show');
			$('#previewContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat preview...</div>');

			setTimeout(function() {
				var content = '<div class="text-center">';
				content += '<h4>Preview Laporan Laku Per Hari</h4>';
				content += '<p>Cabang: ' + cbg + ' | Periode: ' + hari + ' hari</p>';
				content += '<p class="text-muted">Preview akan menampilkan format cetak laporan</p>';
				content += '</div>';
				$('#previewContent').html(content);
			}, 1000);
		}

		function printPreview() {
			window.print();
		}

		// Custom styling untuk tabel
		$(document).ready(function() {
			setTimeout(function() {
				$('<style>')
					.prop('type', 'text/css')
					.html(`
						.dt-center { text-align: center !important; }
						.dt-left { text-align: left !important; }
						.dt-right { text-align: right !important; }
						.table td {
							font-size: 0.875rem;
							vertical-align: middle;
						}
						.table th {
							font-weight: 600;
							background-color: #f8f9fa;
						}
						#lakuPerHariTable_wrapper .dataTables_filter {
							margin-bottom: 10px;
						}
						#lakuPerHariTable_wrapper .dataTables_length {
							margin-bottom: 10px;
						}
						.small-box .inner h3 {
							font-size: 1.8rem;
							font-weight: bold;
						}
						.alert-success {
							border-left: 4px solid #28a745;
						}
					`)
					.appendTo('head');
			}, 1000);
		});

		// Keyboard shortcuts
		$(document).on('keydown', function(e) {
			// Ctrl + Enter untuk submit form
			if (e.ctrlKey && e.which === 13) {
				if (validateForm()) {
					$('#lakuPerHariForm').submit();
				}
			}

			// Escape untuk reset
			if (e.which === 27) {
				if (confirm('Reset form?')) {
					resetForm();
				}
			}

			// F1 untuk help/info
			if (e.which === 112) {
				e.preventDefault();
				$('#infoModal').modal('show');
			}
		});

		// Auto-save form state to localStorage
		function saveFormState() {
			if (typeof(Storage) !== "undefined") {
				localStorage.setItem('laku_perhari_cbg', $('#cbg').val());
				localStorage.setItem('laku_perhari_hari', $('#hari').val());
			}
		}

		function loadFormState() {
			if (typeof(Storage) !== "undefined") {
				var savedCbg = localStorage.getItem('laku_perhari_cbg');
				var savedHari = localStorage.getItem('laku_perhari_hari');

				if (savedCbg && !$('#cbg').val()) {
					$('#cbg').val(savedCbg);
				}
				if (savedHari && !$('#hari').val()) {
					$('#hari').val(savedHari);
				}
			}
		}

		// Save form state on change
		$('#cbg, #hari').on('change blur', function() {
			saveFormState();
		});

		// Load form state on page load
		$(document).ready(function() {
			loadFormState();
		});

		// Tooltip initialization
		$(document).ready(function() {
			$('[data-toggle="tooltip"]').tooltip();
		});

		// Add info button next to title
		$(document).ready(function() {
			setTimeout(function() {
				$('.content-header h1').append(
					' <button type="button" class="btn btn-sm btn-outline-info ml-2" onclick="$(\'#infoModal\').modal(\'show\')" title="Informasi Analisis"><i class="fas fa-info-circle"></i></button>'
				);
			}, 500);
		});

		// Format number display in summary boxes
		function formatNumber(num) {
			return new Intl.NumberFormat('id-ID').format(num);
		}

		// Update summary boxes with animations
		function updateSummaryBoxes(data) {
			if (data && data.length > 0) {
				// Animate counter effect
				$('.small-box .inner h3').each(function() {
					var $this = $(this);
					var countTo = parseInt($this.text().replace(/,/g, ''));
					$({
						countNum: 0
					}).animate({
						countNum: countTo
					}, {
						duration: 2000,
						easing: 'linear',
						step: function() {
							$this.text(formatNumber(Math.floor(this.countNum)));
						},
						complete: function() {
							$this.text(formatNumber(this.countNum));
						}
					});
				});
			}
		}

		// Enhanced error handling
		function handleAjaxError(xhr, status, error) {
			console.error('AJAX Error:', error);
			hideLoading();

			var errorMessage = 'Terjadi kesalahan saat memproses data.';

			if (xhr.status === 404) {
				errorMessage = 'Halaman tidak ditemukan.';
			} else if (xhr.status === 500) {
				errorMessage = 'Kesalahan server internal.';
			} else if (xhr.status === 403) {
				errorMessage = 'Akses ditolak.';
			}

			alert(errorMessage + ' Silakan coba lagi atau hubungi administrator.');
		}

		// Auto-refresh data functionality
		var autoRefreshInterval;

		function startAutoRefresh() {
			if (autoRefreshInterval) {
				clearInterval(autoRefreshInterval);
			}

			// Auto refresh every 30 minutes if data is displayed
			if ($('#lakuPerHariTable').length > 0) {
				autoRefreshInterval = setInterval(function() {
					if (confirm('Refresh data otomatis?\nKlik OK untuk memperbarui data atau Cancel untuk membatalkan.')) {
						$('#lakuPerHariForm').submit();
					}
				}, 30 * 60 * 1000); // 30 minutes
			}
		}

		function stopAutoRefresh() {
			if (autoRefreshInterval) {
				clearInterval(autoRefreshInterval);
				autoRefreshInterval = null;
			}
		}

		// Start auto refresh when data is loaded
		$(document).ready(function() {
			setTimeout(startAutoRefresh, 5000);
		});

		// Stop auto refresh when user is inactive
		var inactivityTimer;

		function resetInactivityTimer() {
			clearTimeout(inactivityTimer);
			inactivityTimer = setTimeout(function() {
				stopAutoRefresh();
				console.log('Auto refresh stopped due to inactivity');
			}, 60 * 60 * 1000); // 1 hour
		}

		// Track user activity
		$(document).on('mousemove keypress click scroll', function() {
			resetInactivityTimer();
		});

		// Initialize inactivity timer
		$(document).ready(function() {
			resetInactivityTimer();
		});

		// Enhanced validation with visual feedback
		function enhancedValidation() {
			var isValid = true;

			// Remove existing validation classes
			$('.is-invalid').removeClass('is-invalid');
			$('.invalid-feedback').remove();

			// Validate cabang
			if (!$('#cbg').val()) {
				$('#cbg').addClass('is-invalid');
				$('#cbg').after('<div class="invalid-feedback">Harap pilih cabang</div>');
				isValid = false;
			}

			// Validate hari
			var hari = parseInt($('#hari').val());
			if (!hari || hari <= 0) {
				$('#hari').addClass('is-invalid');
				$('#hari').after('<div class="invalid-feedback">Harap masukkan jumlah hari yang valid (minimal 1)</div>');
				isValid = false;
			} else if (hari > 365) {
				$('#hari').addClass('is-invalid');
				$('#hari').after('<div class="invalid-feedback">Maksimal 365 hari untuk analisis</div>');
				isValid = false;
			}

			return isValid;
		}

		// Replace validateForm with enhanced version
		function validateForm() {
			return enhancedValidation();
		}

		// Data export with progress tracking
		function exportDataWithProgress(format) {
			if (typeof window.lakuPerHariTable === 'undefined') {
				alert('Tidak ada data untuk di-export. Silakan proses data terlebih dahulu.');
				return;
			}

			// Show progress modal
			var progressHtml = `
				<div class="modal fade" id="exportProgressModal" tabindex="-1" role="dialog">
					<div class="modal-dialog modal-sm" role="document">
						<div class="modal-content">
							<div class="modal-body text-center">
								<div class="spinner-border text-primary" role="status">
									<span class="sr-only">Loading...</span>
								</div>
								<p class="mt-3">Mengekspor data...</p>
								<div class="progress mt-2">
									<div class="progress-bar" role="progressbar" style="width: 0%"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			`;

			$('body').append(progressHtml);
			$('#exportProgressModal').modal({
				backdrop: 'static',
				keyboard: false
			});

			// Simulate progress
			var progress = 0;
			var progressInterval = setInterval(function() {
				progress += Math.random() * 30;
				if (progress > 90) progress = 90;
				$('.progress-bar').css('width', progress + '%');
			}, 200);

			// Trigger export based on format
			setTimeout(function() {
				switch (format) {
					case 'excel':
						window.lakuPerHariTable.button('.buttons-excel').trigger();
						break;
					case 'csv':
						window.lakuPerHariTable.button('.buttons-csv').trigger();
						break;
					case 'pdf':
						window.lakuPerHariTable.button('.buttons-pdf').trigger();
						break;
					case 'print':
						window.lakuPerHariTable.button('.buttons-print').trigger();
						break;
				}

				// Complete progress
				clearInterval(progressInterval);
				$('.progress-bar').css('width', '100%');

				setTimeout(function() {
					$('#exportProgressModal').modal('hide');
					setTimeout(function() {
						$('#exportProgressModal').remove();
					}, 300);
				}, 1000);
			}, 2000);
		}

		// Enhanced export function
		function exportData() {
			var options = [{
					value: 'excel',
					text: 'Export ke Excel (.xlsx)',
					icon: 'fas fa-file-excel text-success'
				},
				{
					value: 'csv',
					text: 'Export ke CSV (.csv)',
					icon: 'fas fa-file-csv text-info'
				},
				{
					value: 'pdf',
					text: 'Export ke PDF (.pdf)',
					icon: 'fas fa-file-pdf text-danger'
				},
				{
					value: 'print',
					text: 'Print Langsung',
					icon: 'fas fa-print text-secondary'
				}
			];

			var optionsHtml = options.map(function(option) {
				return `
					<button type="button" class="btn btn-outline-secondary btn-block mb-2"
							onclick="exportDataWithProgress('${option.value}'); $('#exportModal').modal('hide');">
						<i class="${option.icon} mr-2"></i>${option.text}
					</button>
				`;
			}).join('');

			var modalHtml = `
				<div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">
									<i class="fas fa-download mr-2"></i>Pilih Format Export
								</h5>
								<button type="button" class="close" data-dismiss="modal">
									<span>&times;</span>
								</button>
							</div>
							<div class="modal-body">
								${optionsHtml}
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
							</div>
						</div>
					</div>
				</div>
			`;

			// Remove existing modal and add new one
			$('#exportModal').remove();
			$('body').append(modalHtml);
			$('#exportModal').modal('show');
		}

		// Print functionality with custom styling
		function customPrint() {
			if (typeof window.lakuPerHariTable === 'undefined') {
				alert('Tidak ada data untuk dicetak. Silakan proses data terlebih dahulu.');
				return;
			}

			var printWindow = window.open('', '_blank');
			var printContent = `
				<!DOCTYPE html>
				<html>
				<head>
					<title>Laporan Laku Per Hari</title>
					<style>
						body { font-family: Arial, sans-serif; font-size: 12px; }
						.header { text-align: center; margin-bottom: 20px; }
						.company-name { font-size: 18px; font-weight: bold; }
						.report-title { font-size: 16px; margin-top: 5px; }
						.report-info { font-size: 12px; margin-top: 10px; }
						table { width: 100%; border-collapse: collapse; margin-top: 20px; }
						th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
						th { background-color: #f8f9fa; font-weight: bold; }
						.text-center { text-align: center; }
						.text-right { text-align: right; }
						.footer { margin-top: 30px; font-size: 10px; }
						@media print { body { margin: 0; } }
					</style>
				</head>
				<body>
					<div class="header">
						<div class="company-name">PT. SUMBER ALFARIA TRIJAYA</div>
						<div class="report-title">LAPORAN LAKU PER HARI</div>
						<div class="report-info">
							Cabang: ${$('#cbg').val()} |
							Periode: ${$('#hari').val()} hari |
							Tanggal: ${new Date().toLocaleDateString('id-ID')}
						</div>
					</div>
					<div id="table-content">
						${$('#lakuPerHariTable').parent().html()}
					</div>
					<div class="footer">
						<p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
						<p>Halaman: <span id="pageNum"></span></p>
					</div>
				</body>
				</html>
			`;

			printWindow.document.write(printContent);
			printWindow.document.close();
			printWindow.focus();

			setTimeout(function() {
				printWindow.print();
				printWindow.close();
			}, 1000);
		}

		// Data refresh with confirmation
		function refreshData() {
			if (confirm('Refresh data akan memuat ulang laporan dengan filter yang sama. Lanjutkan?')) {
				showLoading();
				$('#lakuPerHariForm').submit();
			}
		}

		// Cleanup when leaving page
		$(window).on('beforeunload', function() {
			stopAutoRefresh();
			clearTimeout(inactivityTimer);
		});

		// Additional utility functions
		function showNotification(message, type = 'info') {
			var alertClass = 'alert-' + type;
			var iconClass = type === 'success' ? 'fa-check-circle' :
				type === 'warning' ? 'fa-exclamation-triangle' :
				type === 'danger' ? 'fa-times-circle' : 'fa-info-circle';

			var notification = `
				<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
					<i class="fas ${iconClass} mr-2"></i>
					${message}
					<button type="button" class="close" data-dismiss="alert">
						<span>&times;</span>
					</button>
				</div>
			`;

			$('.content .container-fluid .row:first').after(notification);

			// Auto dismiss after 5 seconds
			setTimeout(function() {
				$('.alert').fadeOut('slow', function() {
					$(this).remove();
				});
			}, 5000);
		}

		// Initialize tooltips for all elements with title attribute
		$(document).ready(function() {
			setTimeout(function() {
				$('[title]').tooltip();

				// Add helpful tooltips to form elements
				$('#cbg').attr('title', 'Pilih cabang untuk analisis data laku');
				$('#hari').attr('title', 'Masukkan jumlah hari untuk periode analisis (1-365)');
				$('button[name="action"][value="filter"]').attr('title', 'Proses data dengan filter yang dipilih (Ctrl+Enter)');
				$('button[name="action"][value="cetak"]').attr('title', 'Cetak laporan dalam format PDF');

				// Refresh tooltips
				// $('[title]').tooltip('dispose').tooltip();
				$('[title]').each(function () {
					var $el = $(this);

					// Jika sudah ada instance tooltip, hancurkan
					if ($el.tooltip("instance")) {
						$el.tooltip("destroy");
					}

					// Inisialisasi ulang tooltip
					$el.tooltip();
				});
			}, 1000);
		});

		// Debug mode toggle (for development)
		var debugMode = false;

		$(document).on('keydown', function(e) {
			// Ctrl+Shift+D to toggle debug mode
			if (e.ctrlKey && e.shiftKey && e.which === 68) {
				debugMode = !debugMode;
				console.log('Debug mode:', debugMode ? 'ON' : 'OFF');

				if (debugMode) {
					$('body').addClass('debug-mode');
					showNotification('Debug mode diaktifkan', 'info');
				} else {
					$('body').removeClass('debug-mode');
				}
			}
		});

		// Add debug styles
		$('<style>')
			.prop('type', 'text/css')
			.html(`
				.debug-mode * {
					outline: 1px solid rgba(255, 0, 0, 0.2) !important;
				}
				.debug-mode .form-control:focus {
					outline: 2px solid rgba(0, 123, 255, 0.8) !important;
				}
			`)
			.appendTo('head');

		// Performance monitoring
		$(document).ready(function() {
			if (performance && performance.timing) {
				var loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
				console.log('Page load time:', loadTime + 'ms');

				if (debugMode && loadTime > 3000) {
					console.warn('Slow page load detected:', loadTime + 'ms');
				}
			}
		});

		// Memory usage monitoring (if available)
		function monitorMemoryUsage() {
			if (performance && performance.memory) {
				var memInfo = performance.memory;
				if (debugMode) {
					console.log('Memory usage:', {
						used: Math.round(memInfo.usedJSHeapSize / 1048576) + 'MB',
						total: Math.round(memInfo.totalJSHeapSize / 1048576) + 'MB',
						limit: Math.round(memInfo.jsHeapSizeLimit / 1048576) + 'MB'
					});
				}

				// Warn if memory usage is high
				if (memInfo.usedJSHeapSize / memInfo.jsHeapSizeLimit > 0.9) {
					console.warn('High memory usage detected');
				}
			}
		}

		// Monitor memory usage every 30 seconds in debug mode
		setInterval(function() {
			if (debugMode) {
				monitorMemoryUsage();
			}
		}, 30000);
	</script>
@endsection
