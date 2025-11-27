@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan RODC Belum Dilayani</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Laporan RODC Belum Dilayani</li>
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
								<form method="GET" action="{{ route('get-odcbelum-report') }}" id="rodcBelumForm">
									@csrf
									<div class="form-group">
										<div class="row align-items-end">
											<div class="col-6 mb-2">
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

											<div class="col-6 mb-2 text-right">
												<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
													<i class="fas fa-search mr-1"></i>Proses
												</button>
												<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
													<i class="fas fa-undo mr-1"></i>Reset
												</button>
												<button class="btn btn-success mr-1" type="submit" name="action" value="cetak" formaction="{{ route('jasper-odcbelum-report') }}"
													formmethod="POST" formtarget="_blank">
													<i class="fas fa-print mr-1"></i>Cetak
												</button>
												<button class="btn btn-info" type="button" onclick="exportData()">
													<i class="fas fa-download mr-1"></i>Export
												</button>
											</div>
										</div>

										@if (session()->get('filter_cbg'))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-info">
														<strong>Filter Aktif:</strong>
														Cabang: {{ session()->get('filter_cbg') }} |
														Data RODC yang belum dilayani (2 hari dari tanggal pesan DC)
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
											@if ($hasilRODCBelumLayan && count($hasilRODCBelumLayan) > 0)
												@php
													// Create custom data for KoolDataTables
													$tableData = [];
													foreach ($hasilRODCBelumLayan as $item) {
													    $tableData[] = [
													        'TGL_PSN_DC' => $item['TGL_PSN_DC'] ?? '',
													        'KD_BRG' => $item['KD_BRG'] ?? '',
													        'NA_BRG' => $item['NA_BRG'] ?? '',
													        'KET_UK' => $item['KET_UK'] ?? '',
													        'KET_KEM' => $item['KET_KEM'] ?? '',
													        'PSN_DC' => $item['PSN_DC'] ?? '',
													        'CAT_OD' => $item['CAT_OD'] ?? '',
													        'TGL_OD' => $item['TGL_OD'] ?? '',
													    ];
													}

													// Prepare Excel title
													$excelTitle = 'Laporan_RODC_Belum_Dilayani_' . session()->get('filter_cbg') . '_' . date('Ymd');

													KoolDataTables::create([
													    'dataSource' => $tableData,
													    'name' => 'rodcBelumTable',
													    'fastRender' => true,
													    'fixedHeader' => true,
													    'scrollX' => true,
													    'showFooter' => true,
													    'showFooter' => 'bottom',
													    'columns' => [
													        'TGL_PSN_DC' => [
													            'label' => 'Tanggal',
													            'type' => 'date',
													            'formatType' => 'date',
													            'format' => 'dd/mm/yyyy',
													        ],
													        'KD_BRG' => [
													            'label' => 'Sub Item',
													            'type' => 'string',
													        ],
													        'NA_BRG' => [
													            'label' => 'Nama Barang',
													            'type' => 'string',
													        ],
													        'KET_UK' => [
													            'label' => 'Ukuran',
													            'type' => 'string',
													        ],
													        'KET_KEM' => [
													            'label' => 'Kemasan',
													            'type' => 'string',
													        ],
													        'PSN_DC' => [
													            'label' => 'Tanda',
													            'type' => 'string',
													        ],
													        'CAT_OD' => [
													            'label' => 'Alasan',
													            'type' => 'string',
													        ],
													        'TGL_OD' => [
													            'label' => 'Tanggal',
													            'type' => 'date',
													            'formatType' => 'date',
													            'format' => 'dd/mm/yyyy',
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
													                'targets' => [0, 1, 5, 7], // Tanggal, Sub Item, Tanda, Tanggal
													            ],
													            [
													                'className' => 'dt-left',
													                'targets' => [2, 3, 4, 6], // Nama Barang, Ukuran, Kemasan, Alasan
													            ],
													            [
													                'width' => '12%',
													                'targets' => [0], // Tanggal
													            ],
													            [
													                'width' => '12%',
													                'targets' => [1], // Sub Item
													            ],
													            [
													                'width' => '25%',
													                'targets' => [2], // Nama Barang
													            ],
													            [
													                'width' => '10%',
													                'targets' => [3], // Ukuran
													            ],
													            [
													                'width' => '10%',
													                'targets' => [4], // Kemasan
													            ],
													            [
													                'width' => '8%',
													                'targets' => [5], // Tanda
													            ],
													            [
													                'width' => '15%',
													                'targets' => [6], // Alasan
													            ],
													            [
													                'width' => '12%',
													                'targets' => [7], // Tanggal
													            ],
													        ],
													        'order' => [[0, 'desc']], // Order by Tanggal Pesan DC (TGL_PSN_DC) desc
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
													                        'title' => 'Laporan RODC Belum Dilayani',
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
																<h3>{{ count($hasilRODCBelumLayan) }}</h3>
																<p>Total Items</p>
															</div>
															<div class="icon">
																<i class="fas fa-cube"></i>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="small-box bg-warning">
															<div class="inner">
																<h3>{{ $hasilRODCBelumLayan[0]['NA_TOKO'] ?? 'N/A' }}</h3>
																<p>Nama Toko</p>
															</div>
															<div class="icon">
																<i class="fas fa-store"></i>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="small-box bg-success">
															<div class="inner">
																<h3>2</h3>
																<p>Hari Tunggakan</p>
															</div>
															<div class="icon">
																<i class="fas fa-calendar-times"></i>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="small-box bg-danger">
															<div class="inner">
																<h3>{{ date('Y') }}</h3>
																<p>Tahun Analisis</p>
															</div>
															<div class="icon">
																<i class="fas fa-calendar-alt"></i>
															</div>
														</div>
													</div>
												</div>
											@elseif(request()->has('action') && request()->get('action') == 'filter')
												<div class="alert alert-warning text-center">
													<i class="fas fa-exclamation-triangle mr-2"></i>
													Tidak ada data RODC belum dilayani untuk cabang <strong>{{ request()->get('cbg') }}</strong>
													yang sudah 2 hari dari tanggal pesan DC.
													<br><small class="text-muted mt-2">Pastikan cabang sudah benar atau tidak ada tunggakan pesanan DC.</small>
												</div>
											@else
												<div class="alert alert-info text-center">
													<i class="fas fa-info-circle mr-2"></i>
													Silakan pilih cabang untuk melihat data RODC yang belum dilayani.
													<br><small class="text-muted mt-2">Laporan menampilkan pesanan DC yang sudah 2 hari belum dilayani.</small>
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

	<!-- Modal untuk Informasi RODC -->
	<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="infoModalLabel">Informasi Laporan RODC Belum Dilayani</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="alert alert-info">
						<h6><i class="fas fa-info-circle mr-2"></i>Penjelasan Laporan:</h6>
						<ul>
							<li><strong>RODC (Request Order Distribution Center):</strong> Permintaan barang ke Distribution Center</li>
							<li><strong>Status "*":</strong> Menandakan barang sudah dipesan ke DC</li>
							<li><strong>2 Hari Tunggakan:</strong> Pesanan yang sudah 2 hari dari tanggal pesan DC belum dilayani</li>
							<li><strong>Tahun Berjalan:</strong> Data diambil dari tahun {{ date('Y') }}</li>
							<li><strong>Filter Otomatis:</strong> Sistem otomatis memfilter berdasarkan kriteria di atas</li>
						</ul>
					</div>
					<div class="alert alert-warning">
						<h6><i class="fas fa-exclamation-triangle mr-2"></i>Catatan Penting:</h6>
						<ul>
							<li>Data real-time berdasarkan transaksi terkini</li>
							<li>Hanya menampilkan item dengan PSN_DC = "*" (sudah dipesan DC)</li>
							<li>Laporan membantu identifikasi bottleneck di supply chain</li>
							<li>Data diurutkan berdasarkan kode barang (Sub Item)</li>
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
					<h5 class="modal-title" id="previewModalLabel">Preview Laporan RODC Belum Dilayani</h5>
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
			// Auto-submit when cabang is selected
			$('#cbg').on('change', function() {
				if ($(this).val()) {
					setTimeout(function() {
						if (validateForm()) {
							$('#rodcBelumForm').find('button[name="action"][value="filter"]').click();
						}
					}, 100);
				}
			});

			// Form validation
			$('#rodcBelumForm').on('submit', function(e) {
				if (!validateForm()) {
					e.preventDefault();
				}
			});

			// Show info modal on first visit
			@if (!session()->has('rodcbelum_info_shown'))
				setTimeout(function() {
					$('#infoModal').modal('show');
				}, 1000);
				@php session(['rodcbelum_info_shown' => true]); @endphp
			@endif
		});

		// Reset form function
		function resetForm() {
			window.location.href = '{{ route('rodcbelum') }}';
		}

		// Form validation
		function validateForm() {
			var cbg = $('#cbg').val();

			if (!cbg) {
				alert('Harap pilih cabang');
				$('#cbg').focus();
				return false;
			}

			return true;
		}

		// Export functions
		function exportData() {
			if (typeof window.rodcBelumTable !== 'undefined') {
				// Show export options
				var format = prompt('Pilih format export:\n1. Excel\n2. CSV\n3. PDF\n4. Print\nMasukkan nomor pilihan (1-4):');

				switch (format) {
					case '1':
						window.rodcBelumTable.button('.buttons-excel').trigger();
						break;
					case '2':
						window.rodcBelumTable.button('.buttons-csv').trigger();
						break;
					case '3':
						window.rodcBelumTable.button('.buttons-pdf').trigger();
						break;
					case '4':
						window.rodcBelumTable.button('.buttons-print').trigger();
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
		$('#rodcBelumForm').on('submit', function() {
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

			if (!cbg) {
				alert('Pilih cabang terlebih dahulu');
				return;
			}

			$('#previewModal').modal('show');
			$('#previewContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat preview...</div>');

			setTimeout(function() {
				var content = '<div class="text-center">';
				content += '<h4>Preview Laporan RODC Belum Dilayani</h4>';
				content += '<p>Cabang: ' + cbg + '</p>';
				content += '<p class="text-muted">Data pesanan DC yang belum dilayani (2 hari tunggakan)</p>';
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
						.table td {
							font-size: 0.875rem;
							vertical-align: middle;
						}
						.table th {
							font-weight: 600;
							background-color: #f8f9fa;
						}
						#rodcBelumTable_wrapper .dataTables_filter {
							margin-bottom: 10px;
						}
						#rodcBelumTable_wrapper .dataTables_length {
							margin-bottom: 10px;
						}
						.small-box .inner h3 {
							font-size: 1.5rem;
							font-weight: bold;
						}
						.alert-success {
							border-left: 4px solid #28a745;
						}
						/* Style khusus untuk status tanda */
						td[data-field="PSN_DC"] {
							font-weight: bold;
							color: #dc3545;
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
					$('#rodcBelumForm').submit();
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

		// Auto-save form state
		function saveFormState() {
			if (typeof(Storage) !== "undefined") {
				localStorage.setItem('rodc_belum_cbg', $('#cbg').val());
			}
		}

		function loadFormState() {
			if (typeof(Storage) !== "undefined") {
				var savedCbg = localStorage.getItem('rodc_belum_cbg');
				if (savedCbg && !$('#cbg').val()) {
					$('#cbg').val(savedCbg);
				}
			}
		}

		// Save form state on change
		$('#cbg').on('change', function() {
			saveFormState();
		});

		// Load form state on page load
		$(document).ready(function() {
			loadFormState();
		});

		// Add info button next to title
		$(document).ready(function() {
			setTimeout(function() {
				$('.content-header h1').append(
					' <button type="button" class="btn btn-sm btn-outline-info ml-2" onclick="$(\'#infoModal\').modal(\'show\')" title="Informasi RODC"><i class="fas fa-info-circle"></i></button>'
				);
			}, 500);
		});

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

		// Tooltip initialization
		$(document).ready(function() {
			$('[data-toggle="tooltip"]').tooltip();

			// Add helpful tooltips
			$('#cbg').attr('title', 'Pilih cabang untuk melihat RODC belum dilayani');
			$('button[name="action"][value="filter"]').attr('title', 'Proses data RODC belum dilayani');
			$('button[name="action"][value="cetak"]').attr('title', 'Cetak laporan RODC belum dilayani');
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

			return isValid;
		}

		// Replace validateForm with enhanced version
		function validateForm() {
			return enhancedValidation();
		}

		// Show notification helper
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

		// Auto refresh data setiap 5 menit jika ada data
		var autoRefreshInterval;

		function startAutoRefresh() {
			if (autoRefreshInterval) {
				clearInterval(autoRefreshInterval);
			}

			if ($('#rodcBelumTable').length > 0) {
				autoRefreshInterval = setInterval(function() {
					if (confirm('Auto refresh: Update data RODC belum dilayani?')) {
						$('#rodcBelumForm').submit();
					}
				}, 5 * 60 * 1000); // 5 minutes
			}
		}

		// Start auto refresh when data is loaded
		$(document).ready(function() {
			setTimeout(startAutoRefresh, 3000);
		});

		// Custom print functionality
		function customPrint() {
			if (typeof window.rodcBelumTable === 'undefined') {
				alert('Tidak ada data untuk dicetak. Silakan proses data terlebih dahulu.');
				return;
			}

			var printWindow = window.open('', '_blank');
			var printContent = `
				<!DOCTYPE html>
				<html>
				<head>
					<title>Laporan RODC Belum Dilayani</title>
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
						.footer { margin-top: 30px; font-size: 10px; }
						@media print { body { margin: 0; } }
					</style>
				</head>
				<body>
					<div class="header">
						<div class="company-name">PT. SUMBER ALFARIA TRIJAYA</div>
						<div class="report-title">LAPORAN RODC BELUM DILAYANI</div>
						<div class="report-info">
							Cabang: ${$('#cbg').val()} |
							Tanggal: ${new Date().toLocaleDateString('id-ID')} |
							Kriteria: 2 Hari Tunggakan
						</div>
					</div>
					<div id="table-content">
						${$('#rodcBelumTable').parent().html()}
					</div>
					<div class="footer">
						<p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
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
	</script>
@endsection
