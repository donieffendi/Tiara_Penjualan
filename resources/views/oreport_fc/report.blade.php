@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Penjualan FC</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Penjualan FC</li>
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
								<!-- Form -->
								<form method="POST" action="{{ url('jasper-fc-report') }}">
									@csrf

									<div class="row align-items-end">
										<div class="col-3 mb-2">
											<label for="cbcbg">Pilih Cabang</label>
											<select name="cbcbg" id="cbcbg" class="form-control" required>
												<option value="">Pilih Cabang</option>
												@foreach ($cbg as $perD)
													<option value="{{ $perD->CBG }}" {{ isset($selectedCbg) && $selectedCbg == $perD->CBG ? 'selected' : '' }}>
														{{ $perD->CBG }}
													</option>
												@endforeach
											</select>
										</div>
										<div class="col-2 mb-2">
											<label for="tglDr">Tanggal Awal</label>
											<input type="date" name="tglDr" id="tglDr" class="form-control" value="{{ $tglDr ?? date('Y-m-d') }}" required>
										</div>
										<div class="col-2 mb-2">
											<label for="tglSmp">Tanggal Akhir</label>
											<input type="date" name="tglSmp" id="tglSmp" class="form-control" value="{{ $tglSmp ?? date('Y-m-d') }}" required>
										</div>

										<div class="col-5 mb-2 text-right">
											<button class="btn btn-primary mr-1" type="submit" id="filter" name="filter">Filter</button>
											<button class="btn btn-danger mr-1" type="button" id="resetfilter" onclick="window.location='{{ url('rfc') }}'">Reset</button>
											<button class="btn btn-warning mr-1" type="submit" id="cetak" name="cetak" formtarget="_blank">Cetak Penjualan</button>
											<button class="btn btn-success mr-1" type="submit" id="cetak_tr" name="cetak_tr" formtarget="_blank">Cetak PB1</button>
										</div>
									</div>

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="agenda-tab" data-toggle="tab" href="#agenda" role="tab" aria-controls="agenda" aria-selected="true">
												<i class="fas fa-chart-line mr-1"></i>Report Penjualan
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="tr-tab" data-toggle="tab" href="#tr" role="tab" aria-controls="tr" aria-selected="false">
												<i class="fas fa-file-invoice mr-1"></i>PB1
											</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Report Penjualan Tab -->
										<div class="tab-pane fade show active" id="agenda" role="tabpanel" aria-labelledby="agenda-tab">
											<div class="pt-3">
												<div class="form-group">
													<h5 class="mb-3">Report FC (Food Court)</h5>
													@if (!empty($na_toko))
														<div class="alert alert-info">
															<strong>Toko:</strong> {{ $na_toko }}<br>
															<strong>No Form:</strong> {{ $no_form }}<br>
															<strong>Periode:</strong> {{ $isSameMonth ? 'Bulan Berjalan' : 'Bulan Berbeda' }}
														</div>
													@endif
													<div style="margin-bottom: 15px;"></div>
													<div class="report-content" col-md-12>
														<?php
														// Menggunakan alias untuk menghindari konflik nama class
														use koolreport\datagrid\DataTables as KoolDataTables;

														if (isset($hasilFC) && $hasilFC) {
														    KoolDataTables::create([
														        'dataSource' => $hasilFC,
														        'name' => 'tableFC',
														        'fastRender' => true,
														        'fixedHeader' => true,
														        'scrollX' => true,
														        'showFooter' => true,
														        'showFooter' => 'bottom',
														        'columns' => [
														            'KD_BRG' => [
														                'label' => 'Kode Barang',
														            ],
														            'NA_BRG' => [
														                'label' => 'Nama Barang',
														            ],
														            'TYPE' => [
														                'label' => 'Type',
														            ],
														            'STAND' => [
														                'label' => 'Stand',
														            ],
														            'tgl' => [
														                'label' => 'Tanggal',
														            ],
														            'qtylaku' => [
														                'label' => 'Qty Laku',
														                'type' => 'number',
														                'decimals' => 2,
														                'decimalPoint' => '.',
														                'thousandSeparator' => ',',
														                'footer' => 'sum',
														                'footerText' => '<b>@value</b>',
														            ],
														            'totlaku' => [
														                'label' => 'Total Laku',
														                'type' => 'number',
														                'decimals' => 2,
														                'decimalPoint' => '.',
														                'thousandSeparator' => ',',
														                'footer' => 'sum',
														                'footerText' => '<b>@value</b>',
														            ],
														            'qtynm' => [
														                'label' => 'Qty Normal',
														                'type' => 'number',
														                'decimals' => 2,
														                'decimalPoint' => '.',
														                'thousandSeparator' => ',',
														                'footer' => 'sum',
														                'footerText' => '<b>@value</b>',
														            ],
														            'totnm' => [
														                'label' => 'Total Normal',
														                'type' => 'number',
														                'decimals' => 2,
														                'decimalPoint' => '.',
														                'thousandSeparator' => ',',
														                'footer' => 'sum',
														                'footerText' => '<b>@value</b>',
														            ],
														            'qtyob' => [
														                'label' => 'Qty Obral',
														                'type' => 'number',
														                'decimals' => 2,
														                'decimalPoint' => '.',
														                'thousandSeparator' => ',',
														                'footer' => 'sum',
														                'footerText' => '<b>@value</b>',
														            ],
														            'totob' => [
														                'label' => 'Total Obral',
														                'type' => 'number',
														                'decimals' => 2,
														                'decimalPoint' => '.',
														                'thousandSeparator' => ',',
														                'footer' => 'sum',
														                'footerText' => '<b>@value</b>',
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
														                    'targets' => [5, 6, 7, 8, 9, 10],
														                ],
														            ],
														            'order' => [],
														            'paging' => true,
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
														} else {
														    echo '<div class="alert alert-warning">';
														    echo '<p>Silahkan pilih cabang dan periode untuk menampilkan laporan FC.</p>';
														    echo '</div>';
														}
														?>
													</div>
												</div>
											</div>
										</div>

										<!-- PB1 Tab -->
										<div class="tab-pane fade" id="tr" role="tabpanel" aria-labelledby="tr-tab">
											<div class="pt-3">
												<div class="form-group">
													<h5 class="mb-3">PB1 (Pembelian)</h5>
													<div style="margin-bottom: 15px;"></div>
													<div class="report-content" col-md-12>
														<?php
														if (isset($hasilTR) && $hasilTR) {
														    KoolDataTables::create([
														        'dataSource' => $hasilTR,
														        'name' => 'tableTR',
														        'fastRender' => true,
														        'fixedHeader' => true,
														        'scrollX' => true,
														        'showFooter' => true,
														        'showFooter' => 'bottom',
														        'columns' => [
														            'no_form' => [
														                'label' => 'No Form',
														            ],
														            'na_toko' => [
														                'label' => 'Nama Toko',
														            ],
														            'periode' => [
														                'label' => 'Periode',
														            ],
														            'tgl' => [
														                'label' => 'Tanggal',
														            ],
														            'penjualan' => [
														                'label' => 'Penjualan',
														                'type' => 'number',
														                'decimals' => 2,
														                'decimalPoint' => '.',
														                'thousandSeparator' => ',',
														                'footer' => 'sum',
														                'footerText' => '<b>@value</b>',
														            ],
														            'PB1' => [
														                'label' => 'PB1',
														                'type' => 'number',
														                'decimals' => 2,
														                'decimalPoint' => '.',
														                'thousandSeparator' => ',',
														                'footer' => 'sum',
														                'footerText' => '<b>@value</b>',
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
														                    'targets' => [4, 5], // penjualan dan PB1
														                ],
														            ],
														            'order' => [],
														            'paging' => true,
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
														} else {
														    echo '<div class="alert alert-warning">';
														    echo '<p>Silahkan pilih cabang dan periode untuk menampilkan laporan PB1.</p>';
														    echo '</div>';
														}
														?>
													</div>
												</div>
											</div>
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
@endsection

@section('stylesheets')
	<style>
		.nav-tabs {
			border-bottom: 2px solid #dee2e6;
			margin-bottom: 0;
		}

		.nav-tabs .nav-link {
			border: 1px solid transparent;
			border-top-left-radius: 0.25rem;
			border-top-right-radius: 0.25rem;
			color: #495057;
			background-color: #f8f9fa;
			margin-bottom: -2px;
			transition: all 0.3s ease;
		}

		.nav-tabs .nav-link:hover {
			border-color: #e9ecef #e9ecef #dee2e6;
			background-color: #e9ecef;
			color: #495057;
		}

		.nav-tabs .nav-link.active {
			color: #495057;
			background-color: #fff;
			border-color: #dee2e6 #dee2e6 #fff;
			border-bottom: 2px solid #fff;
			font-weight: 600;
		}

		.tab-content {
			border: 1px solid #dee2e6;
			border-top: none;
			padding: 1rem;
			background-color: #fff;
			min-height: 400px;
		}

		.tab-pane {
			opacity: 0;
			transition: opacity 0.3s ease-in-out;
		}

		.tab-pane.active {
			opacity: 1;
		}

		.nav-tabs .nav-item {
			margin-bottom: 0;
		}

		.report-content {
			margin-top: 1rem;
		}

		/* Button styling for tabs */
		#cetak,
		#cetak_tr {
			transition: all 0.3s ease;
		}

		/* Table responsive wrapper */
		.table-responsive {
			border-radius: 0.375rem;
			box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
		}

		/* DataTables Custom Styles */
		.dataTable th {
			background-color: #f8f9fa;
			border-bottom: 2px solid #dee2e6;
			font-weight: 600;
			text-align: center;
		}

		.dataTable td {
			border-bottom: 1px solid #dee2e6;
			vertical-align: middle;
		}

		.dt-right {
			text-align: right !important;
		}

		.footerCss {
			background-color: #e9ecef;
			font-weight: bold;
			border-top: 2px solid #adb5bd;
		}

		.label-title {
			background-color: #f8f9fa;
			font-weight: 600;
			text-align: center;
		}

		.detail {
			padding: 8px;
		}
	</style>
@endsection

@section('javascripts')
	<script>
		// Tab functionality
		$(document).ready(function() {
			// Initialize Bootstrap tabs
			$('#reportTabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');
			});

			// Save active tab to localStorage
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				var targetTab = $(e.target).attr('href');
				localStorage.setItem('activeTab', targetTab);

				// Show/hide appropriate buttons based on active tab
				if (targetTab === '#agenda') {
					$('#cetak').show();
					$('#cetak_tr').hide();
				} else if (targetTab === '#tr') {
					$('#cetak').hide();
					$('#cetak_tr').show();
				}
			});

			// Restore active tab from localStorage
			var activeTab = localStorage.getItem('activeTab');
			if (activeTab) {
				$('#reportTabs a[href="' + activeTab + '"]').tab('show');
			} else {
				// Default to penjualan tab
				$('#agenda-tab').tab('show');
				$('#cetak_tr').hide();
			}

			// Auto-resize table on tab change
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				setTimeout(function() {
					$.fn.dataTable.tables({
						visible: true,
						api: true
					}).columns.adjust();
				}, 100);
			});

			// Initially hide TR button
			$('#cetak_tr').hide();

			// Form validation
			$('form').on('submit', function(e) {
				var cbcbg = $('#cbcbg').val();
				var tglDr = $('#tglDr').val();
				var tglSmp = $('#tglSmp').val();

				if (!cbcbg || !tglDr || !tglSmp) {
					e.preventDefault();
					alert('Mohon lengkapi semua field yang diperlukan!');
					return false;
				}

				// Validate date range
				if (new Date(tglDr) > new Date(tglSmp)) {
					e.preventDefault();
					alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir!');
					return false;
				}
			});
		});
	</script>
@endsection
