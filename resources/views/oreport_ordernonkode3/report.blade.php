@extends('layouts.plain')

@section('content')
@php
    use \koolreport\datagrid\DataTables as KoolDataTables;
@endphp
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Order Non-Kode 3</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Laporan Order Non-Kode 3</li>
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
								<form method="GET" action="{{ route('get-ordernonkode3-report') }}" id="orderNonKode3Form">
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

											<div class="col-3 mb-2">
												<label for="sub1">Sub (Dari)</label>
												<input type="text" name="sub1" id="sub1" class="form-control" value="{{ session()->get('filter_sub1') }}" placeholder="000"
													maxlength="3" pattern="[0-9]{3}" required>
											</div>

											<div class="col-3 mb-2">
												<label for="sub2">Sub (Sampai)</label>
												<input type="text" name="sub2" id="sub2" class="form-control" value="{{ session()->get('filter_sub2') }}" placeholder="999"
													maxlength="3" pattern="[0-9]{3}" required>
											</div>

											<div class="col-2 mb-2 text-right">
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
												<button class="btn btn-success mr-1" type="submit" name="action" value="cetak" formaction="{{ route('jasper-ordernonkode3-report') }}"
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
														Sub: {{ session()->get('filter_sub1') }} - {{ session()->get('filter_sub2') }} |
														Data Order Non-Kode dengan stok <= threshold </div>
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
											@if ($hasilOrderNonKode3 && count($hasilOrderNonKode3) > 0)
												@php
													// Create custom data for KoolDataTables
													$tableData = [];
													foreach ($hasilOrderNonKode3 as $item) {
													    $tableData[] = [
													        'KD_BRG' => $item['KD_BRG'] ?? '',
													        'NA_BRG' => $item['NA_BRG'] ?? '',
													        'KET_UK' => $item['KET_UK'] ?? '',
													        'LPH' => $item['LPH'] ?? '0.00',
													        'DTR' => $item['DTR'] ?? '0',
													        'SRMIN' => $item['SRMIN'] ?? '0.00',
													        'STOK' => $item['STOK'] ?? '0.00',
													        'QTY_SP' => $item['QTY_SP'] ?? '0',
													        'KETERANGAN' => $item['KETERANGAN'] ?? '',
													    ];
													}

													// Prepare Excel title
													$excelTitle =
													    'Laporan_Order_NonKode3_' .
													    session()->get('filter_cbg') .
													    '_' .
													    session()->get('filter_sub1') .
													    '-' .
													    session()->get('filter_sub2') .
													    '_' .
													    date('Ymd');

													KoolDataTables::create([
													    'dataSource' => $tableData,
													    'name' => 'orderNonKode3Table',
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
													        'KET_UK' => [
													            'label' => 'Ukuran',
													            'type' => 'string',
													        ],
													        'LPH' => [
													            'label' => 'LPH',
													            'type' => 'number',
																'formatValue' => function ($value) {
																	return number_format($value, 2, ',', '.');
																},
													        ],
													        'DTR' => [
													            'label' => 'DTR',
													            'type' => 'number',
													            'format' => '#,##0',
													        ],
													        'SRMIN' => [
													            'label' => 'SRMIN',
													            'type' => 'number',
													            'format' => '#,##0.00',
													        ],
													        'STOK' => [
													            'label' => 'Stok',
													            'type' => 'number',
																'formatValue' => function ($value) {
																	return number_format($value, 2, ',', '.');
																},
													        ],
													        'QTY_SP' => [
													            'label' => 'On SP',
													            'type' => 'number',
													            'format' => '#,##0',
													        ],
													        'KETERANGAN' => [
													            'label' => 'Keterangan',
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
													                'targets' => [0, 3, 4, 5, 6, 7], // Sub Item, LPH, DTR, SRMIN, Stok, On SP
													            ],
													            [
													                'className' => 'dt-left',
													                'targets' => [1, 2, 8], // Nama Barang, Ukuran, Keterangan
													            ],
													            [
													                'width' => '12%',
													                'targets' => [0], // Sub Item
													            ],
													            [
													                'width' => '30%',
													                'targets' => [1], // Nama Barang
													            ],
													            [
													                'width' => '8%',
													                'targets' => [2], // Ukuran
													            ],
													            [
													                'width' => '8%',
													                'targets' => [3], // LPH
													            ],
													            [
													                'width' => '8%',
													                'targets' => [4], // DTR
													            ],
													            [
													                'width' => '10%',
													                'targets' => [5], // SRMIN
													            ],
													            [
													                'width' => '8%',
													                'targets' => [6], // Stok
													            ],
													            [
													                'width' => '8%',
													                'targets' => [7], // On SP
													            ],
													            [
													                'width' => '12%',
													                'targets' => [8], // Keterangan
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
													                        'title' => 'Laporan Order Non-Kode 3',
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
												@php
													$stats = app('App\Http\Controllers\OReport\ROrderNonKode3Controller')->getSummaryStats($hasilOrderNonKode3);
												@endphp

												<div class="row mt-2">
													<div class="col-md-3">
														<div class="small-box bg-info">
															<div class="inner">
																<h3>{{ $stats['total_items'] }}</h3>
																<p>Total Items</p>
															</div>
															<div class="icon">
																<i class="fas fa-cube"></i>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="small-box bg-warning">
															<div class="col-md-3">
																<div class="small-box bg-warning">
																	<div class="inner">
																		<h3>{{ number_format($stats['total_stok']) }}</h3>
																		<p>Total Stok</p>
																	</div>
																	<div class="icon">
																		<i class="fas fa-boxes"></i>
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="small-box bg-success">
																<div class="inner">
																	<h3>{{ number_format($stats['total_on_sp']) }}</h3>
																	<p>Total On SP</p>
																</div>
																<div class="icon">
																	<i class="fas fa-truck"></i>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="small-box bg-danger">
																<div class="inner">
																	<h3>{{ $stats['items_tmo'] }}</h3>
																	<p>Items TMO</p>
																</div>
																<div class="icon">
																	<i class="fas fa-exclamation-triangle"></i>
																</div>
															</div>
														</div>
													</div>

													<div class="row">
														<div class="col-md-4">
															<div class="info-box">
																<span class="info-box-icon bg-info"><i class="fas fa-check-circle"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Items OKE</span>
																	<span class="info-box-number">{{ $stats['items_oke'] }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-4">
															<div class="info-box">
																<span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Items ON ORD</span>
																	<span class="info-box-number">{{ $stats['items_on_ord'] }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-4">
															<div class="info-box">
																<span class="info-box-icon bg-secondary"><i class="fas fa-percentage"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">% TMO dari Total</span>
																	<span class="info-box-number">
																		{{ $stats['total_items'] > 0 ? number_format(($stats['items_tmo'] / $stats['total_items']) * 100, 1) : 0 }}%
																	</span>
																</div>
															</div>
														</div>
													</div>
												@else
													<div class="row">
														<div class="col-12">
															<div class="alert alert-warning text-center">
																<i class="fas fa-info-circle mr-2"></i>
																<strong>Informasi:</strong>
																@if (session()->get('filter_cbg'))
																	Tidak ada data Order Non-Kode 3 yang memenuhi kriteria untuk filter yang dipilih.
																@else
																	Silakan pilih cabang dan range sub item untuk menampilkan laporan.
																@endif
															</div>
														</div>
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

	{{-- Modal Loading --}}
	<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true"
		data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-body text-center">
					<div class="spinner-border text-primary" role="status">
						<span class="sr-only">Loading...</span>
					</div>
					<p class="mt-2">Sedang memproses data...</p>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
	<script>
		$(document).ready(function() {
			// Format input sub dengan leading zeros
			$('#sub1, #sub2').on('input', function() {
				let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
				if (value.length > 3) {
					value = value.substring(0, 3);
				}
				$(this).val(value.padStart(3, '0'));
			});

			// Auto-fill sub2 when sub1 changes
			$('#sub1').on('change', function() {
				let sub1 = $(this).val();
				let sub2 = $('#sub2').val();

				if (sub1 && !sub2) {
					$('#sub2').val('999'); // Default to 999 if sub2 is empty
				}
			});

			// Show loading modal on form submit
			$('#orderNonKode3Form').on('submit', function(e) {
				let action = $('input[name="action"]:checked').val() || $('button[type="submit"]:focus').val();

				if (action === 'filter' || action === 'cetak') {
					// Validate required fields
					let cbg = $('#cbg').val();
					let sub1 = $('#sub1').val();
					let sub2 = $('#sub2').val();

					if (!cbg || !sub1 || !sub2) {
						e.preventDefault();
						Swal.fire({
							icon: 'error',
							title: 'Validasi Error',
							text: 'Semua field harus diisi!',
							confirmButtonText: 'OK'
						});
						return false;
					}

					// Validate sub range
					if (parseInt(sub1) > parseInt(sub2)) {
						e.preventDefault();
						Swal.fire({
							icon: 'error',
							title: 'Validasi Error',
							text: 'Sub (Dari) tidak boleh lebih besar dari Sub (Sampai)!',
							confirmButtonText: 'OK'
						});
						return false;
					}

					if (action === 'filter') {
						$('#loadingModal').modal('show');
					}
				}
			});

			// Hide loading modal when page loads (in case of redirect back)
			$('#loadingModal').modal('hide');
		});

		// Reset form function
		function resetForm() {
			Swal.fire({
				title: 'Reset Form',
				text: 'Apakah Anda yakin ingin mereset semua filter?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: 'Ya, Reset',
				cancelButtonText: 'Batal',
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6'
			}).then((result) => {
				if (result.isConfirmed) {
					// Clear form
					$('#cbg').val('');
					$('#sub1').val('');
					$('#sub2').val('');

					// Clear session and reload page
					window.location.href = "{{ route('get-ordernonkode3-report') }}?reset=1";
				}
			});
		}

		// Export data function
		function exportData() {
			let cbg = $('#cbg').val();
			let sub1 = $('#sub1').val();
			let sub2 = $('#sub2').val();

			if (!cbg || !sub1 || !sub2) {
				Swal.fire({
					icon: 'error',
					title: 'Export Error',
					text: 'Silakan filter data terlebih dahulu sebelum export!',
					confirmButtonText: 'OK'
				});
				return false;
			}

			// Check if there's data to export
			@if (isset($hasilOrderNonKode3) && count($hasilOrderNonKode3) > 0)
				// Show export options
				Swal.fire({
					title: 'Export Data',
					text: 'Pilih format export:',
					icon: 'question',
					showCancelButton: true,
					showDenyButton: true,
					confirmButtonText: 'Excel',
					denyButtonText: 'CSV',
					cancelButtonText: 'Batal',
					confirmButtonColor: '#28a745',
					denyButtonColor: '#17a2b8',
					cancelButtonColor: '#6c757d'
				}).then((result) => {
					if (result.isConfirmed) {
						// Export to Excel
						let table = $('#orderNonKode3Table').DataTable();
						table.button('.buttons-excel').trigger();
					} else if (result.isDenied) {
						// Export to CSV
						let table = $('#orderNonKode3Table').DataTable();
						table.button('.buttons-csv').trigger();
					}
				});
			@else
				Swal.fire({
					icon: 'warning',
					title: 'Export Warning',
					text: 'Tidak ada data untuk di-export!',
					confirmButtonText: 'OK'
				});
			@endif
		}

		// Handle success/error messages from controller
		@if (session('success'))
			Swal.fire({
				icon: 'success',
				title: 'Berhasil',
				text: '{{ session('success') }}',
				confirmButtonText: 'OK'
			});
		@endif

		@if (session('error'))
			Swal.fire({
				icon: 'error',
				title: 'Error',
				text: '{{ session('error') }}',
				confirmButtonText: 'OK'
			});
		@endif

		// Auto-refresh data every 5 minutes if filter is active
		@if (session()->get('filter_cbg'))
			setInterval(function() {
				// Only auto-refresh if no modal is open and no user interaction
				if (!$('.modal.show').length && !$(':focus').length) {
					console.log('Auto-refreshing data...');
					$('#orderNonKode3Form button[value="filter"]').click();
				}
			}, 300000); // 5 minutes = 300000ms
		@endif

		// Add hover effects and tooltips for better UX
		$(function() {
			$('[data-toggle="tooltip"]').tooltip();

			// Add tooltips to summary stats
			$('.small-box, .info-box').attr('data-toggle', 'tooltip').attr('data-placement', 'top');
			$('.small-box:contains("Total Items")').attr('title', 'Jumlah total item yang memenuhi kriteria Order Non-Kode 3');
			$('.small-box:contains("Total Stok")').attr('title', 'Jumlah total stok dari semua item');
			$('.small-box:contains("Total On SP")').attr('title', 'Jumlah total item yang sedang dalam status SP (Surat Pesanan)');
			$('.small-box:contains("Items TMO")').attr('title', 'Item yang perlu segera di-order (Tidak Masuk Order)');
			$('.info-box:contains("Items OKE")').attr('title', 'Item yang stok SP-nya sudah mencukupi DTR');
			$('.info-box:contains("Items ON ORD")').attr('title', 'Item yang sedang dalam proses order');
		});

		// Print function for summary
		function printSummary() {
			let printContent = `
		<div style="text-align: center; margin-bottom: 20px;">
			<h2>LAPORAN ORDER NON-KODE 3</h2>
			<h3>RINGKASAN DATA</h3>
			<p>Cabang: {{ session()->get('filter_cbg') }} | Sub: {{ session()->get('filter_sub1') }} - {{ session()->get('filter_sub2') }}</p>
			<p>Tanggal Cetak: ${new Date().toLocaleString('id-ID')}</p>
		</div>
		<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
			<tr style="border: 1px solid #ddd; background-color: #f2f2f2;">
				<th style="padding: 10px; text-align: left;">Keterangan</th>
				<th style="padding: 10px; text-align: right;">Jumlah</th>
			</tr>
			<tr style="border: 1px solid #ddd;">
				<td style="padding: 8px;">Total Items</td>
				<td style="padding: 8px; text-align: right;">{{ $stats['total_items'] ?? 0 }}</td>
			</tr>
			<tr style="border: 1px solid #ddd;">
				<td style="padding: 8px;">Total Stok</td>
				<td style="padding: 8px; text-align: right;">{{ number_format($stats['total_stok'] ?? 0) }}</td>
			</tr>
			<tr style="border: 1px solid #ddd;">
				<td style="padding: 8px;">Total On SP</td>
				<td style="padding: 8px; text-align: right;">{{ number_format($stats['total_on_sp'] ?? 0) }}</td>
			</tr>
			<tr style="border: 1px solid #ddd;">
				<td style="padding: 8px;">Items TMO</td>
				<td style="padding: 8px; text-align: right;">{{ $stats['items_tmo'] ?? 0 }}</td>
			</tr>
			<tr style="border: 1px solid #ddd;">
				<td style="padding: 8px;">Items OKE</td>
				<td style="padding: 8px; text-align: right;">{{ $stats['items_oke'] ?? 0 }}</td>
			</tr>
			<tr style="border: 1px solid #ddd;">
				<td style="padding: 8px;">Items ON ORD</td>
				<td style="padding: 8px; text-align: right;">{{ $stats['items_on_ord'] ?? 0 }}</td>
			</tr>
		</table>
	`;

			let printWindow = window.open('', '_blank');
			printWindow.document.write(`
		<html>
			<head>
				<title>Ringkasan Laporan Order Non-Kode 3</title>
				<style>
					body { font-family: Arial, sans-serif; margin: 20px; }
					table { border-collapse: collapse; width: 100%; }
					th, td { border: 1px solid #ddd; padding: 8px; }
					th { background-color: #f2f2f2; }
					@media print { body { margin: 0; } }
				</style>
			</head>
			<body>
				${printContent}
			</body>
		</html>
	`);
			printWindow.document.close();
			printWindow.print();
		}
	</script>
@endpush

@push('styles')
	<style>
		.report-content {
			margin-top: 20px;
		}

		.small-box {
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
			transition: transform 0.2s ease;
		}

		.small-box:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
		}

		.info-box {
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
			transition: transform 0.2s ease;
		}

		.info-box:hover {
			transform: translateY(-1px);
			box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
		}

		.alert {
			border-radius: 8px;
		}

		.card {
			border-radius: 10px;
			box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
		}

		.card-body {
			padding: 25px;
		}

		/* DataTable custom styles */
		.dataTables_wrapper .dataTables_filter input {
			border-radius: 20px;
			padding: 5px 15px;
			border: 1px solid #ddd;
		}

		.dataTables_wrapper .dataTables_length select {
			border-radius: 5px;
			border: 1px solid #ddd;
		}

		/* Button styles */
		.btn {
			border-radius: 6px;
			transition: all 0.2s ease;
		}

		.btn:hover {
			transform: translateY(-1px);
			box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
		}

		/* Form controls */
		.form-control {
			border-radius: 6px;
			border: 1px solid #ddd;
			transition: border-color 0.2s ease;
		}

		.form-control:focus {
			border-color: #007bff;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
		}

		/* Loading spinner */
		.spinner-border {
			width: 3rem;
			height: 3rem;
		}

		/* Table responsive improvements */
		@media (max-width: 768px) {
			.table-responsive {
				font-size: 12px;
			}

			.small-box h3 {
				font-size: 1.5rem;
			}

			.info-box-number {
				font-size: 1.2rem;
			}
		}

		/* Print styles */
		@media print {
			.no-print {
				display: none !important;
			}

			.card {
				box-shadow: none;
				border: 1px solid #ddd;
			}

			.table {
				font-size: 12px;
			}
		}
	</style>
@endpush
