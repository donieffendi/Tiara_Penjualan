@extends('layouts.plain')

@section('content')
@php
    use \koolreport\datagrid\DataTables;
@endphp
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Form Laporan Barang Supplier</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Form Laporan Barang Supplier</li>
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
								<form method="GET" action="{{ route('get-barangsupplier-report') }}" id="barangSupplierForm">
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
												<label for="kodes">Kode Supplier</label>
												<div class="input-group">
													<input type="text" name="kodes" id="kodes" class="form-control" placeholder="Masukkan kode supplier"
														value="{{ session()->get('filter_kodes') }}" required maxlength="20">
													<div class="input-group-append">
														<button class="btn btn-outline-secondary" type="button" onclick="showSupplierList()" title="Daftar Supplier">
															<i class="fas fa-list"></i>
														</button>
													</div>
												</div>
											</div>

											<div class="col-4 mb-2 text-right">
												<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
													<i class="fas fa-search mr-1"></i>Proses
												</button>
												<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
													<i class="fas fa-undo mr-1"></i>Reset
												</button>
												<button class="btn btn-success mr-1" type="submit" name="action" value="cetak" formaction="{{ route('jasper-barangsupplier-report') }}"
													formmethod="POST" formtarget="_blank">
													<i class="fas fa-print mr-1"></i>Cetak
												</button>
												<button class="btn btn-info" type="button" onclick="exportData()">
													<i class="fas fa-download mr-1"></i>Export
												</button>
											</div>
										</div>

										@if (session()->get('filter_cbg') && session()->get('filter_kodes'))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-info">
														<strong>Filter Aktif:</strong>
														Cabang: {{ session()->get('filter_cbg') }} |
														Kode Supplier: {{ session()->get('filter_kodes') }}
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
											@if ($hasilBarangSupplier && count($hasilBarangSupplier) > 0)
												@php
													// Create custom data for KoolDataTables
													$tableData = [];
													foreach ($hasilBarangSupplier as $item) {
													    $tableData[] = [
													        'KODES' => $item['KODES'] ?? '',
													        'KD_BRG' => $item['KD_BRG'] ?? '',
													        'NA_BRG' => $item['NA_BRG'] ?? '',
													        'KET_UK' => $item['KET_UK'] ?? '',
													        'SUPP' => $item['SUPP'] ?? '',
													    ];
													}

													// Prepare Excel title with safe string concatenation
													$excelTitle = 'Laporan_Barang_Supplier_' . session()->get('filter_cbg') . '_' . session()->get('filter_kodes');

													DataTables::create([
													    'dataSource' => $tableData,
													    'name' => 'barangSupplierTable',
													    'fastRender' => true,
													    'fixedHeader' => true,
													    'scrollX' => true,
													    'showFooter' => true,
													    'showFooter' => 'bottom',
													    'columns' => [
													        'SUPP' => [
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
													        'KD_BRG' => [
													            'label' => 'Kode Barang',
													            'type' => 'string',
													        ],
													        'KODES' => [
													            'label' => 'Kode Supplier',
													            'type' => 'string',
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
													                'targets' => [3, 4], // KD_BRG, KODES columns
													            ],
													            [
													                'className' => 'dt-left',
													                'targets' => [0, 1, 2], // SUPP, NA_BRG, KET_UK columns
													            ],
													            [
													                'width' => '15%',
													                'targets' => [0], // SUPP (Sub Item)
													            ],
													            [
													                'width' => '40%',
													                'targets' => [1], // NA_BRG (Nama Barang)
													            ],
													            [
													                'width' => '15%',
													                'targets' => [2], // KET_UK (Ukuran)
													            ],
													            [
													                'width' => '15%',
													                'targets' => [3], // KD_BRG (Kode Barang)
													            ],
													            [
													                'width' => '15%',
													                'targets' => [4], // KODES (Kode Supplier)
													            ],
													        ],
													        'order' => [[3, 'asc']], // Order by kode barang
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
													                        'title' => 'Laporan Barang Supplier',
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
															• Kode Supplier: <strong>{{ session()->get('filter_kodes') }}</strong><br>
															• Total barang ditemukan: <strong>{{ count($hasilBarangSupplier) }}</strong> item<br>
															• Data diurutkan berdasarkan kode barang secara ascending
														</div>
													</div>
												</div>
											@elseif(request()->has('action') && request()->get('action') == 'filter')
												<div class="alert alert-warning text-center">
													<i class="fas fa-exclamation-triangle mr-2"></i>
													Tidak ada barang ditemukan untuk supplier <strong>{{ request()->get('kodes') }}</strong> di cabang
													<strong>{{ request()->get('cbg') }}</strong>.
													<br><small class="text-muted mt-2">Pastikan kode supplier sudah benar dan terdapat barang untuk supplier tersebut.</small>
												</div>
											@else
												<div class="alert alert-info text-center">
													<i class="fas fa-info-circle mr-2"></i>
													Silakan pilih cabang dan masukkan kode supplier untuk menampilkan data barang.
													<br><small class="text-muted mt-2">Klik tombol daftar <i class="fas fa-list"></i> untuk melihat supplier yang tersedia.</small>
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

	<!-- Modal untuk Daftar Supplier -->
	<div class="modal fade" id="supplierModal" tabindex="-1" role="dialog" aria-labelledby="supplierModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="supplierModalLabel">Daftar Supplier</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="alert alert-info">
						<i class="fas fa-info-circle mr-2"></i>
						Pilih cabang terlebih dahulu untuk melihat daftar supplier yang tersedia.
					</div>
					<div id="supplierList">
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
					<h5 class="modal-title" id="previewModalLabel">Preview Laporan Barang Supplier</h5>
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
			// Auto-focus on kodes input when cabang is selected
			$('#cbg').on('change', function() {
				if ($(this).val()) {
					setTimeout(function() {
						$('#kodes').focus();
					}, 100);
				}
			});

			// Form validation
			$('#barangSupplierForm').on('submit', function(e) {
				if (!validateForm()) {
					e.preventDefault();
				}
			});

			// Enter key handling untuk kodes input
			$('#kodes').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					if (validateForm()) {
						$(this).closest('form').find('button[name="action"][value="filter"]').click();
					}
				}
			});

			// Auto-uppercase untuk kodes input
			$('#kodes').on('input', function() {
				$(this).val($(this).val().toUpperCase());
			});

			// Trim whitespace on blur
			$('#kodes').on('blur', function() {
				$(this).val($.trim($(this).val()));
			});
		});

		// Reset form function - mengimplementasikan logika reset dari Delphi
		function resetForm() {
			window.location.href = '{{ route('rbarangsupplier') }}';
		}

		// Form validation - mengimplementasikan validasi dari txtKODESExit pada Delphi
		function validateForm() {
			var cbg = $('#cbg').val();
			var kodes = $.trim($('#kodes').val());

			if (!cbg) {
				alert('Harap pilih cabang');
				$('#cbg').focus();
				return false;
			}

			if (!kodes) {
				alert('Harap masukkan kode supplier');
				$('#kodes').focus();
				return false;
			}

			// Validasi format kode supplier (alphanumeric, dash, underscore, slash, space)
			if (!/^[A-Z0-9\-_\/\s]+$/i.test(kodes)) {
				alert('Format kode supplier tidak valid. Gunakan huruf, angka, dan karakter -_/ saja.');
				$('#kodes').focus();
				return false;
			}

			return true;
		}

		// Show supplier list modal
		function showSupplierList() {
			var cbg = $('#cbg').val();

			if (!cbg) {
				alert('Pilih cabang terlebih dahulu');
				$('#cbg').focus();
				return;
			}

			$('#supplierModalLabel').text('Daftar Supplier - Cabang: ' + cbg);
			$('#supplierList').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
			$('#supplierModal').modal('show');

			// Load supplier list via AJAX
			$.ajax({
				url: '{{ url('/get-supplier-list') }}/' + cbg,
				type: 'GET',
				success: function(response) {
					var content = '';
					if (response && response.length > 0) {
						content = '<div class="table-responsive">';
						content += '<table class="table table-sm table-hover">';
						content += '<thead><tr><th>Kode Supplier</th><th>Aksi</th></tr></thead>';
						content += '<tbody>';

						$.each(response, function(index, item) {
							content += '<tr>';
							content += '<td><code>' + item.SUPP + '</code></td>';
							content += '<td><button class="btn btn-sm btn-primary" onclick="selectSupplier(\'' + item.SUPP +
								'\')">Pilih</button></td>';
							content += '</tr>';
						});

						content += '</tbody></table></div>';
					} else {
						content = '<div class="alert alert-warning">Tidak ada supplier ditemukan untuk cabang ini.</div>';
					}
					$('#supplierList').html(content);
				},
				error: function(xhr, status, error) {
					$('#supplierList').html('<div class="alert alert-danger">Error memuat data supplier: ' + error + '</div>');
					console.error('Error loading supplier list:', error);
				}
			});
		}

		// Select supplier from modal
		function selectSupplier(suppCode) {
			$('#kodes').val(suppCode);
			$('#supplierModal').modal('hide');
			// Auto-focus ke tombol proses atau trigger form submit
			setTimeout(function() {
				$('button[name="action"][value="filter"]').focus();
			}, 300);
		}

		// Export functions
		function exportData() {
			if (typeof window.barangSupplierTable !== 'undefined') {
				// Show export options
				var format = prompt('Pilih format export:\n1. Excel\n2. CSV\n3. PDF\n4. Print\nMasukkan nomor pilihan (1-4):');

				switch (format) {
					case '1':
						window.barangSupplierTable.button('.buttons-excel').trigger();
						break;
					case '2':
						window.barangSupplierTable.button('.buttons-csv').trigger();
						break;
					case '3':
						window.barangSupplierTable.button('.buttons-pdf').trigger();
						break;
					case '4':
						window.barangSupplierTable.button('.buttons-print').trigger();
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
		$('#barangSupplierForm').on('submit', function() {
			if (validateForm()) {
				showLoading();
			}
		});

		// Hide loading when page loads
		$(window).on('load', function() {
			hideLoading();
		});

		// Preview print function (optional enhancement)
		function previewPrint() {
			var cbg = $('#cbg').val();
			var kodes = $('#kodes').val();

			if (!cbg || !kodes) {
				alert('Pilih cabang dan masukkan kode supplier terlebih dahulu');
				return;
			}

			$('#previewModal').modal('show');
			$('#previewContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat preview...</div>');

			// Load preview content (implement as needed)
			setTimeout(function() {
				var content = '<div class="text-center">';
				content += '<h4>Preview Laporan Barang Supplier</h4>';
				content += '<p>Cabang: ' + cbg + ' | Supplier: ' + kodes + '</p>';
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
						#barangSupplierTable_wrapper .dataTables_filter {
							margin-bottom: 10px;
						}
						#barangSupplierTable_wrapper .dataTables_length {
							margin-bottom: 10px;
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
					$('#barangSupplierForm').submit();
				}
			}

			// Escape untuk reset
			if (e.which === 27) {
				if (confirm('Reset form?')) {
					resetForm();
				}
			}
		});

		// Auto-save form state to localStorage (optional enhancement)
		function saveFormState() {
			if (typeof(Storage) !== "undefined") {
				localStorage.setItem('barang_supplier_cbg', $('#cbg').val());
				localStorage.setItem('barang_supplier_kodes', $('#kodes').val());
			}
		}

		function loadFormState() {
			if (typeof(Storage) !== "undefined") {
				var savedCbg = localStorage.getItem('barang_supplier_cbg');
				var savedKodes = localStorage.getItem('barang_supplier_kodes');

				if (savedCbg && !$('#cbg').val()) {
					$('#cbg').val(savedCbg);
				}
				if (savedKodes && !$('#kodes').val()) {
					$('#kodes').val(savedKodes);
				}
			}
		}

		// Save form state on change
		$('#cbg, #kodes').on('change blur', function() {
			saveFormState();
		});

		// Load form state on page load
		$(document).ready(function() {
			loadFormState();
		});
	</script>
@endsection
