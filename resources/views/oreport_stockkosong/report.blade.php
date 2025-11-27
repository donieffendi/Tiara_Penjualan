@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Form Laporan Stock Kosong</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Form Laporan Stock Kosong</li>
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
								<form method="GET" action="{{ route('get-stockkosong-report') }}" id="stockKosongForm">
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
												<label for="sub1">Sub Dari</label>
												<input type="text" name="sub1" id="sub1" class="form-control" placeholder="Sub awal" value="{{ session()->get('filter_sub1') }}"
													maxlength="10">
											</div>

											<div class="col-2 mb-2">
												<label for="sub2">Sub Sampai</label>
												<input type="text" name="sub2" id="sub2" class="form-control" placeholder="Sub akhir"
													value="{{ session()->get('filter_sub2', 'ZZZ') }}" maxlength="10">
											</div>

											<div class="col-2 mb-2">
												<label for="report_type">Tipe Laporan</label>
												<select name="report_type" id="report_type" class="form-control">
													<option value="normal" {{ request()->get('report_type') == 'normal' ? 'selected' : '' }}>Normal</option>
													<option value="kosong" {{ request()->get('report_type') == 'kosong' ? 'selected' : '' }}>Stock Kosong</option>
													<option value="minus" {{ request()->get('report_type') == 'minus' ? 'selected' : '' }}>Stock Minus</option>
													<option value="retur" {{ request()->get('report_type') == 'retur' ? 'selected' : '' }}>Retur</option>
												</select>
											</div>

											<div class="col-3 mb-2 text-right">
												<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
													<i class="fas fa-search mr-1"></i>Proses
												</button>
												<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
													<i class="fas fa-undo mr-1"></i>Reset
												</button>
												<button class="btn btn-success mr-1" type="submit" name="action" value="cetak" formaction="{{ route('jasper-stockkosong-report') }}"
													formmethod="POST" formtarget="_blank">
													<i class="fas fa-print mr-1"></i>Cetak
												</button>
												{{-- <button class="btn btn-info" type="button" onclick="exportData()">
													<i class="fas fa-download mr-1"></i>Export
												</button> --}}
											</div>
										</div>

										@if (session()->get('filter_cbg'))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-info">
														<strong>Filter Aktif:</strong>
														Cabang: {{ session()->get('filter_cbg') }} |
														Sub: {{ session()->get('filter_sub1') ?: 'Semua' }} - {{ session()->get('filter_sub2') }} |
														Tipe: {{ ucfirst(request()->get('report_type', 'normal')) }}
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
										<?php
											use \koolreport\datagrid\DataTables as KoolDataTables;
										?>
										<div class="report-content" col-md-12>
											@if ($hasilStock && count($hasilStock) > 0)
												@php
													// Create custom data for KoolDataTables
													$tableData = [];
													foreach ($hasilStock as $item) {
													    $tableData[] = [
													        'cbg' => $item['cbg'] ?? '',
													        'sub' => $item['sub'] ?? '',
													        'kd_brg' => $item['kd_brg'] ?? '',
													        'na_brg' => $item['na_brg'] ?? '',
													        'td_od' => $item['td_od'] ?? '',
													        'kdlaku' => $item['kdlaku'] ?? '',
													        'ak00' => $item['ak00'] ?? 0,
													    ];
													}

													// Prepare Excel title
													$reportTypeText = ucfirst(request()->get('report_type', 'normal'));
													$excelTitle = 'Laporan_Stock_' . $reportTypeText . '_' . session()->get('filter_cbg');

													KoolDataTables::create([
													    'dataSource' => $tableData,
													    'name' => 'stockKosongTable',
													    'fastRender' => true,
													    'fixedHeader' => true,
													    'scrollX' => true,
													    'showFooter' => true,
													    'showFooter' => 'bottom',
													    'columns' => [
													        'cbg' => [
													            'label' => 'Cabang',
													            'type' => 'string',
													        ],
													        'sub' => [
													            'label' => 'Sub',
													            'type' => 'string',
													        ],
													        'kd_brg' => [
													            'label' => 'Kode',
													            'type' => 'string',
													        ],
													        'na_brg' => [
													            'label' => 'Nama Barang',
													            'type' => 'string',
													        ],
													        'td_od' => [
													            'label' => 'Tidak Order',
													            'type' => 'string',
													        ],
													        'kdlaku' => [
													            'label' => 'Kode Laku',
													            'type' => 'string',
													        ],
													        'ak00' => [
													            'label' => 'Saldo',
													            'type' => 'number',
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
													                'targets' => [0, 1, 2, 4, 5, 6], // Cabang, Sub, Kode, Tidak Order, Kode Laku, Saldo
													            ],
													            [
													                'className' => 'dt-left',
													                'targets' => [3], // Nama Barang
													            ],
													        ],
													        'order' => [[2, 'asc']], // Order by kode barang
													        'paging' => true,
													        'pageLength' => 10,
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
													                        'title' => 'Laporan Stock ' . $reportTypeText,
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

												<div class="row mt-3">
													<div class="col-12">
														<div class="alert alert-success">
															<i class="fas fa-info-circle mr-2"></i>
															<strong>Informasi Data:</strong><br>
															• Cabang: <strong>{{ session()->get('filter_cbg') }}</strong><br>
															• Sub: <strong>{{ session()->get('filter_sub1') ?: 'Semua' }}</strong> - <strong>{{ session()->get('filter_sub2') }}</strong><br>
															• Tipe Laporan: <strong>{{ ucfirst(request()->get('report_type', 'normal')) }}</strong><br>
															• Total barang ditemukan: <strong>{{ count($hasilStock) }}</strong> item<br>
															• Data diurutkan berdasarkan kode barang secara ascending
														</div>
													</div>
												</div>
											@elseif(request()->has('action') && request()->get('action') == 'filter')
												<div class="alert alert-warning text-center">
													<i class="fas fa-exclamation-triangle mr-2"></i>
													Tidak ada data ditemukan untuk cabang <strong>{{ request()->get('cbg') }}</strong>
													dengan kriteria SUB <strong>{{ request()->get('sub1') ?: 'Semua' }}</strong> - <strong>{{ request()->get('sub2') }}</strong>
													dan tipe laporan <strong>{{ ucfirst(request()->get('report_type', 'normal')) }}</strong>.
													<br><small class="text-muted mt-2">Pastikan parameter pencarian sudah benar atau ubah kriteria pencarian.</small>
												</div>
											@else
												<div class="alert alert-info text-center">
													<i class="fas fa-info-circle mr-2"></i>
													Silakan pilih cabang dan tentukan range SUB untuk menampilkan data stock.
													<br><small class="text-muted mt-2">
														<strong>Tipe Laporan:</strong><br>
														• Normal: Menampilkan semua stock normal<br>
														• Stock Kosong: Stock dengan saldo = 0<br>
														• Stock Minus: Stock dengan saldo < 0<br>
															• Retur: Menampilkan data retur
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

	<!-- Modal untuk SUB List -->
	<div class="modal fade" id="subModal" tabindex="-1" role="dialog" aria-labelledby="subModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="subModalLabel">Daftar SUB</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="alert alert-info">
						<i class="fas fa-info-circle mr-2"></i>
						Pilih cabang terlebih dahulu untuk melihat daftar SUB yang tersedia.
					</div>
					<div id="subList">
						<!-- Content akan dimuat via JavaScript -->
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
					<h5 class="modal-title" id="previewModalLabel">Preview Laporan Stock</h5>
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
			// Auto-focus on sub1 input when cabang is selected
			$('#cbg').on('change', function() {
				if ($(this).val()) {
					setTimeout(function() {
						$('#sub1').focus();
					}, 100);
				}
			});

			// Form validation
			$('#stockKosongForm').on('submit', function(e) {
				if (!validateForm()) {
					e.preventDefault();
				}
			});

			// SUB validation mengikuti logika dari Delphi
			$('#sub1').on('blur', function() {
				validateSub('sub1');
			});

			$('#sub2').on('blur', function() {
				validateSub('sub2');
			});

			// Auto-uppercase untuk SUB input
			$('#sub1, #sub2').on('input', function() {
				$(this).val($(this).val().toUpperCase());
			});

			// Enter key handling
			$('#sub1, #sub2').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					if (validateForm()) {
						$(this).closest('form').find('button[name="action"][value="filter"]').click();
					}
				}
			});

			// Set default SUB2 value jika kosong
			$('#sub1').on('change', function() {
				var sub1Value = $(this).val().trim();
				if (sub1Value && !$('#sub2').val()) {
					$('#sub2').val(sub1Value);
				}
			});

			// Report type change handler
			$('#report_type').on('change', function() {
				var reportType = $(this).val();
				updateFormDescription(reportType);
			});

			// Initialize form description
			updateFormDescription($('#report_type').val());
		});

		// Reset form function
		function resetForm() {
			// Clear session filters
			window.location.href = '{{ route('rstockkosong') }}';
		}

		// Form validation
		function validateForm() {
			var cbg = $('#cbg').val();
			var sub1 = $.trim($('#sub1').val());
			var sub2 = $.trim($('#sub2').val());

			if (!cbg) {
				alert('Harap pilih cabang');
				$('#cbg').focus();
				return false;
			}

			// Validasi SUB range
			if (sub1 && sub2 && sub1 > sub2) {
				alert('SUB awal tidak boleh lebih besar dari SUB akhir');
				$('#sub1').focus();
				return false;
			}

			return true;
		}

		// SUB validation mengikuti logika txtsub1Exit dari Delphi
		function validateSub(fieldId) {
			var cbg = $('#cbg').val();
			var subValue = $.trim($('#' + fieldId).val());

			if (!cbg || !subValue) {
				return;
			}

			$.ajax({
				url: '{{ url('/validate-sub') }}',
				type: 'GET',
				data: {
					cbg: cbg,
					sub: subValue
				},
				success: function(response) {
					if (!response.success) {
						// SUB tidak ditemukan, bisa redirect ke form pencarian atau tampilkan pesan
						console.log('SUB tidak ditemukan:', response.message);
						// Implementasi sesuai kebutuhan
					}
				},
				error: function(xhr, status, error) {
					console.error('Error validating SUB:', error);
				}
			});
		}

		// Update form description based on report type
		function updateFormDescription(reportType) {
			var descriptions = {
				'normal': 'Menampilkan semua stock normal dengan status aktif',
				'kosong': 'Menampilkan barang dengan stock kosong (saldo = 0)',
				'minus': 'Menampilkan barang dengan stock minus (saldo < 0)',
				'retur': 'Menampilkan data stock retur berdasarkan rak00'
			};

			// Update info text if exists
			var infoText = descriptions[reportType] || 'Pilih tipe laporan';
			// Bisa ditambahkan elemen untuk menampilkan deskripsi ini
		}

		// Show SUB list modal (optional enhancement)
		function showSubList() {
			var cbg = $('#cbg').val();

			if (!cbg) {
				alert('Pilih cabang terlebih dahulu');
				$('#cbg').focus();
				return;
			}

			$('#subModal').modal('show');
			$('#subList').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');

			// Implementation untuk load SUB list
			setTimeout(function() {
				var content = '<div class="alert alert-info">Fitur daftar SUB akan segera tersedia</div>';
				$('#subList').html(content);
			}, 1000);
		}

		// Export functions
		function exportData() {
			if (typeof window.stockKosongTable !== 'undefined') {
				// Show export options
				var format = prompt('Pilih format export:\n1. Excel\n2. CSV\n3. PDF\n4. Print\nMasukkan nomor pilihan (1-4):');

				switch (format) {
					case '1':
						window.stockKosongTable.button('.buttons-excel').trigger();
						break;
					case '2':
						window.stockKosongTable.button('.buttons-csv').trigger();
						break;
					case '3':
						window.stockKosongTable.button('.buttons-pdf').trigger();
						break;
					case '4':
						window.stockKosongTable.button('.buttons-print').trigger();
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
		$('#stockKosongForm').on('submit', function() {
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
			var sub1 = $('#sub1').val();
			var sub2 = $('#sub2').val();
			var reportType = $('#report_type').val();

			if (!cbg) {
				alert('Pilih cabang terlebih dahulu');
				return;
			}

			$('#previewModal').modal('show');
			$('#previewContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat preview...</div>');

			// Load preview content
			setTimeout(function() {
				var content = '<div class="text-center">';
				content += '<h4>Preview Laporan Stock ' + $('#report_type option:selected').text() + '</h4>';
				content += '<p>Cabang: ' + cbg + ' | SUB: ' + (sub1 || 'Semua') + ' - ' + (sub2 || 'ZZZ') + '</p>';
				content += '<p class="text-muted">Fitur preview akan segera tersedia</p>';
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
						#stockKosongTable_wrapper .dataTables_filter {
							margin-bottom: 10px;
						}
						#stockKosongTable_wrapper .dataTables_length {
							margin-bottom: 10px;
						}
						.table td.dt-center.number-cell {
							text-align: right !important;
							font-family: monospace;
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
					$('#stockKosongForm').submit();
				}
			}

			// Escape untuk reset
			if (e.which === 27) {
				if (confirm('Reset form?')) {
					resetForm();
				}
			}

			// F5 untuk refresh dengan parameter yang sama
			if (e.which === 116) { // F5
				e.preventDefault();
				if (validateForm()) {
					$('#stockKosongForm').submit();
				}
			}
		});

		// Auto-save form state
		function saveFormState() {
			if (typeof(Storage) !== "undefined") {
				localStorage.setItem('stock_kosong_cbg', $('#cbg').val());
				localStorage.setItem('stock_kosong_sub1', $('#sub1').val());
				localStorage.setItem('stock_kosong_sub2', $('#sub2').val());
				localStorage.setItem('stock_kosong_report_type', $('#report_type').val());
			}
		}

		function loadFormState() {
			if (typeof(Storage) !== "undefined") {
				var savedCbg = localStorage.getItem('stock_kosong_cbg');
				var savedSub1 = localStorage.getItem('stock_kosong_sub1');
				var savedSub2 = localStorage.getItem('stock_kosong_sub2');
				var savedReportType = localStorage.getItem('stock_kosong_report_type');

				if (savedCbg && !$('#cbg').val()) {
					$('#cbg').val(savedCbg);
				}
				if (savedSub1 && !$('#sub1').val()) {
					$('#sub1').val(savedSub1);
				}
				if (savedSub2 && !$('#sub2').val()) {
					$('#sub2').val(savedSub2);
				}
				if (savedReportType && !$('#report_type').val()) {
					$('#report_type').val(savedReportType);
				}
			}
		}

		// Save form state on change
		$('#cbg, #sub1, #sub2, #report_type').on('change blur', function() {
			saveFormState();
		});

		// Load form state on page load
		$(document).ready(function() {
			loadFormState();
		});

		// AJAX search function (optional enhancement)
		function ajaxSearch() {
			var formData = {
				cbg: $('#cbg').val(),
				sub1: $('#sub1').val(),
				sub2: $('#sub2').val(),
				report_type: $('#report_type').val()
			};

			if (!validateForm()) {
				return;
			}

			$.ajax({
				url: '{{ url('/ajax-get-stock') }}',
				type: 'GET',
				data: formData,
				beforeSend: function() {
					showLoading();
				},
				success: function(response) {
					if (response.success) {
						// Update table dengan data baru
						console.log('Data loaded:', response.data.length, 'records');
						// Implementasi update table real-time jika diperlukan
					} else {
						alert('Error: ' + response.message);
					}
				},
				error: function(xhr, status, error) {
					alert('Error loading data: ' + error);
				},
				complete: function() {
					hideLoading();
				}
			});
		}
	</script>
@endsection
