@extends('layouts.plain')

<?php
use koolreport\datagrid\DataTables as KoolDataTables;
?>

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Pembelian SPM</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Pembelian SPM</li>
						</ol>
					</div>
				</div>
			</div>
		</div>
		<div class="container-fluid">
			<div class="row justify-content-center">
				<div class="col-md-12">
					<div class="card border-0 shadow-sm">

						<div class="card-body">
							<form action="{{ route('rspm.jasper') }}" method="GET" id="frmSpm">
								@csrf
								<div class="row">
									<div class="col-md-2 d-flex flex-column justify-content-center">
										<div class="form-group mb-2">
											<label for="periode">Periode</label>
											<select name="periode" id="periode" class="form-control" required>
												<option value="">Periode</option>
												@foreach ($periode as $per)
													<option value="{{ $per->PERIO }}" {{ isset($selectedPeriode) && $selectedPeriode == $per->PERIO ? 'selected' : '' }}>
														{{ $per->PERIO }}
													</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-md-2 d-flex flex-column justify-content-center">
										<div class="form-group mb-2">
											<label for="tglDr">Tanggal Dari</label>
											<input type="date" name="tglDr" id="tglDr" class="form-control" value="{{ $tglDr ?? date('Y-m-d') }}" required>
										</div>
									</div>
									<div class="col-md-2 d-flex flex-column justify-content-center">
										<div class="form-group mb-2">
											<label for="tglSmp">Tanggal Sampai</label>
											<input type="date" name="tglSmp" id="tglSmp" class="form-control" value="{{ $tglSmp ?? date('Y-m-d') }}" required>
										</div>
									</div>
									<div class="col-4 d-flex flex-column justify-content-center align-items-end">
										<div>
											<button class="btn btn-primary mr-1" type="submit" id="filter" name="filter">Filter</button>
											<button class="btn btn-danger mr-1" type="button" id="resetfilter" onclick="window.location='{{ url('rcust') }}'">Reset</button>
											<button class="btn btn-warning" type="submit" id="cetak" formtarget="_blank">Cetak</button>
										</div>
									</div>
								</div>
							</form>

							<hr>

							<!-- Tab Navigation -->
							<ul class="nav nav-tabs" id="reportTab" role="tablist">
								<li class="nav-item" role="presentation">
									<a class="nav-link active" id="periode-tab" data-toggle="tab" href="#periode" role="tab" aria-controls="periode" aria-selected="true">
										<i class="fas fa-calendar-alt mr-1"></i>Periode
									</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link" id="sub-beli-tab" data-toggle="tab" href="#sub-beli" role="tab" aria-controls="sub-beli" aria-selected="false">
										<i class="fas fa-shopping-cart mr-1"></i>Sub Beli
									</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link" id="report-retur-tab" data-toggle="tab" href="#report-retur" role="tab" aria-controls="report-retur"
										aria-selected="false">
										<i class="fas fa-undo mr-1"></i>Report Retur
									</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link" id="sub-retur-tab" data-toggle="tab" href="#sub-retur" role="tab" aria-controls="sub-retur" aria-selected="false">
										<i class="fas fa-reply mr-1"></i>Sub Retur
									</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link" id="sub-hut-tab" data-toggle="tab" href="#sub-hut" role="tab" aria-controls="sub-hut" aria-selected="false">
										<i class="fas fa-credit-card mr-1"></i>Sub HUT
									</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link" id="transaksi-lain-tab" data-toggle="tab" href="#transaksi-lain" role="tab" aria-controls="transaksi-lain"
										aria-selected="false">
										<i class="fas fa-exchange-alt mr-1"></i>Transaksi Lain-Lain
									</a>
								</li>
							</ul>

							<!-- Tab Content -->
							<div class="tab-content" id="reportTabContent">
								<!-- Periode Tab -->
								<div class="tab-pane fade show active" id="periode" role="tabpanel" aria-labelledby="periode-tab">
									<div class="pt-3">
										<h5 class="mb-3"><i class="fas fa-calendar-alt mr-2"></i>Laporan Periode</h5>
										<?php
										if (!empty($hasilPeriode)) {
										    KoolDataTables::create([
										        'dataSource' => $hasilPeriode,
										        'name' => 'periodeTable',
										        'fastRender' => true,
										        'fixedHeader' => true,
										        'scrollX' => true,
										        'showFooter' => true,
										        'columns' => [
										            'NO_BUKTI' => ['label' => 'No Bukti'],
										            'TGL' => ['label' => 'Tanggal'],
										            'NO_REF' => ['label' => 'Ref'],
										            'KODES' => ['label' => 'Supplier'],
										            'NAMAS' => ['label' => 'Nama'],
										            'KLB' => [
										                'label' => 'KLB',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										            'bruto' => [
										                'label' => 'Bruto',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										            'PROM' => [
										                'label' => 'Dis Promosi',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										            'PPN' => [
										                'label' => 'PPN',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										        ],
										        'cssClass' => [
										            'table' => 'table table-hover table-striped table-bordered compact',
										        ],
										        'options' => [
										            'columnDefs' => [['className' => 'dt-right', 'targets' => [5, 6, 7, 8]]],
										            'dom' => 'Blfrtip',
										            'buttons' => [['extend' => 'collection', 'text' => 'Export', 'buttons' => ['copy', 'excel', 'csv', 'pdf', 'print']]],
										        ],
										    ]);
										} else {
										    echo '<div class="alert alert-info">Tidak ada data untuk ditampilkan.</div>';
										}
										?>
									</div>
								</div>

								<!-- Sub Beli Tab -->
								<div class="tab-pane fade" id="sub-beli" role="tabpanel" aria-labelledby="sub-beli-tab">
									<div class="pt-3">
										<h5 class="mb-3"><i class="fas fa-shopping-cart mr-2"></i>Sub Pembelian</h5>
										<?php
										if (!empty($hasilSubBeli)) {
										    KoolDataTables::create([
										        'dataSource' => $hasilSubBeli,
										        'name' => 'subBeliTable',
										        'fastRender' => true,
										        'fixedHeader' => true,
										        'scrollX' => true,
										        'showFooter' => true,
										        'columns' => [
										            'sub' => ['label' => 'Sub'],
										            'KELOMPOK' => ['label' => 'Kelompok'],
										            'bruto' => [
										                'label' => 'Bruto',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										            'prom' => [
										                'label' => 'Prom',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										        ],
										        'cssClass' => [
										            'table' => 'table table-hover table-striped table-bordered compact',
										        ],
										        'options' => [
										            'columnDefs' => [['className' => 'dt-right', 'targets' => [3, 4]]],
										            'dom' => 'Blfrtip',
										            'buttons' => [['extend' => 'collection', 'text' => 'Export', 'buttons' => ['copy', 'excel', 'csv', 'pdf', 'print']]],
										        ],
										    ]);
										} else {
										    echo '<div class="alert alert-info">Tidak ada data untuk ditampilkan.</div>';
										}
										?>
									</div>
								</div>

								<!-- Report Retur Tab -->
								<div class="tab-pane fade" id="report-retur" role="tabpanel" aria-labelledby="report-retur-tab">
									<div class="pt-3">
										<h5 class="mb-3"><i class="fas fa-undo mr-2"></i>Report Retur</h5>
										<?php
										if (!empty($hasilReportRetur)) {
										    KoolDataTables::create([
										        'dataSource' => $hasilReportRetur,
										        'name' => 'reportReturTable',
										        'fastRender' => true,
										        'fixedHeader' => true,
										        'scrollX' => true,
										        'showFooter' => true,
										        'columns' => [
										            'NO_BUKTI' => ['label' => 'No Bukti'],
										            'tgl' => ['label' => 'Tanggal'],
										            'NO_PO' => ['label' => 'Ref'],
										            'KODES' => ['label' => 'Supplier'],
										            'NAMAS' => ['label' => 'Nama'],
										            'KLB' => [
										                'label' => 'KLB',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										            'bruto' => [
										                'label' => 'Bruto',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										            'PROM' => [
										                'label' => 'Dis Promosi',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										            'PPN' => [
										                'label' => 'PPN',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										        ],
										        'cssClass' => [
										            'table' => 'table table-hover table-striped table-bordered compact',
										        ],
										        'options' => [
										            'columnDefs' => [['className' => 'dt-right', 'targets' => [6, 7, 8]]],
										            'dom' => 'Blfrtip',
										            'buttons' => [['extend' => 'collection', 'text' => 'Export', 'buttons' => ['copy', 'excel', 'csv', 'pdf', 'print']]],
										        ],
										    ]);
										} else {
										    echo '<div class="alert alert-info">Tidak ada data untuk ditampilkan.</div>';
										}
										?>
									</div>
								</div>

								<!-- Sub Retur Tab -->
								<div class="tab-pane fade" id="sub-retur" role="tabpanel" aria-labelledby="sub-retur-tab">
									<div class="pt-3">
										<h5 class="mb-3"><i class="fas fa-reply mr-2"></i>Sub Retur</h5>
										<?php
										if (!empty($hasilSubRetur)) {
										    KoolDataTables::create([
										        'dataSource' => $hasilSubRetur,
										        'name' => 'subReturTable',
										        'fastRender' => true,
										        'fixedHeader' => true,
										        'scrollX' => true,
										        'showFooter' => true,
										        'columns' => [
										            'sub' => ['label' => 'Sub'],
										            'KELOMPOK' => ['label' => 'Kelompok'],
										            'bruto' => [
										                'label' => 'Bruto',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										            'prom' => [
										                'label' => 'Prom',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										        ],
										        'cssClass' => [
										            'table' => 'table table-hover table-striped table-bordered compact',
										        ],
										        'options' => [
										            'columnDefs' => [['className' => 'dt-right', 'targets' => [3, 4]]],
										            'dom' => 'Blfrtip',
										            'buttons' => [['extend' => 'collection', 'text' => 'Export', 'buttons' => ['copy', 'excel', 'csv', 'pdf', 'print']]],
										        ],
										    ]);
										} else {
										    echo '<div class="alert alert-info">Tidak ada data untuk ditampilkan.</div>';
										}
										?>
									</div>
								</div>

								<!-- Sub HUT Tab -->
								<div class="tab-pane fade" id="sub-hut" role="tabpanel" aria-labelledby="sub-hut-tab">
									<div class="pt-3">
										<h5 class="mb-3"><i class="fas fa-credit-card mr-2"></i>Sub Hutang</h5>
										<?php
										if (!empty($hasilSubHut)) {
										    KoolDataTables::create([
										        'dataSource' => $hasilSubHut,
										        'name' => 'subHutTable',
										        'fastRender' => true,
										        'fixedHeader' => true,
										        'scrollX' => true,
										        'showFooter' => true,
										        'columns' => [
										            'sub' => ['label' => 'Sub'],
										            'KELOMPOK' => ['label' => 'Kelompok'],
										            'total' => [
										                'label' => 'Total',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										            'ppn' => [
										                'label' => 'Ppn',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										            'nett' => [
										                'label' => 'Nett',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										            'prom' => [
										                'label' => 'Prom',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										        ],
										        'cssClass' => [
										            'table' => 'table table-hover table-striped table-bordered compact',
										        ],
										        'options' => [
										            'columnDefs' => [['className' => 'dt-right', 'targets' => [2, 3, 4, 5]]],
										            'dom' => 'Blfrtip',
										            'buttons' => [['extend' => 'collection', 'text' => 'Export', 'buttons' => ['copy', 'excel', 'csv', 'pdf', 'print']]],
										        ],
										    ]);
										} else {
										    echo '<div class="alert alert-info">Tidak ada data untuk ditampilkan.</div>';
										}
										?>
									</div>
								</div>

								<!-- Transaksi Lain-lain Tab -->
								<div class="tab-pane fade" id="transaksi-lain" role="tabpanel" aria-labelledby="transaksi-lain-tab">
									<div class="col-md-2">
										<div class="form-group">
											<label for="cbcbg">CBG</label>
											<select name="cbcbg" id="cbcbg" class="form-control" required>
												<option value="">Pilih CBG</option>
												@foreach ($cbg as $c)
													<option value="{{ $c->CBG }}" {{ isset($selectedCbg) && $selectedCbg == $c->CBG ? 'selected' : '' }}>
														{{ $c->CBG }}
													</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="pt-3">
										<h5 class="mb-3"><i class="fas fa-exchange-alt mr-2"></i>Transaksi Lain-lain</h5>
										<?php
										if (!empty($hasilTransaksiLain)) {
										    KoolDataTables::create([
										        'dataSource' => $hasilTransaksiLain,
										        'name' => 'transaksiLainTable',
										        'fastRender' => true,
										        'fixedHeader' => true,
										        'scrollX' => true,
										        'showFooter' => true,
										        'columns' => [
										            'NO_BUKTI' => ['label' => 'No Bukti'],
										            'TGL' => ['label' => 'Tanggal'],
										            'nacc' => ['label' => 'Perkiraan'],
										            'KODES' => ['label' => 'Supp'],
										            'KET' => ['label' => 'Keterangan'],
										            'Debet' => [
										                'label' => 'Debet',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										            'Kredit' => [
										                'label' => 'Credit',
										                'type' => 'number',
										                'formatValue' => function ($value) {
										                    return number_format($value, 0, ',', '.');
										                },
										            ],
										            'cbg' => ['label' => 'CBG'],
										        ],
										        'cssClass' => [
										            'table' => 'table table-hover table-striped table-bordered compact',
										        ],
										        'options' => [
										            'columnDefs' => [['className' => 'dt-right', 'targets' => [5, 6]]],
										            'dom' => 'Blfrtip',
										            'buttons' => [['extend' => 'collection', 'text' => 'Export', 'buttons' => ['copy', 'excel', 'csv', 'pdf', 'print']]],
										        ],
										    ]);
										} else {
										    echo '<div class="alert alert-info">Tidak ada data untuk ditampilkan.</div>';
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script>
			$(document).ready(function() {
				$('#reportTabs a').on('click', function(e) {
					e.preventDefault();
					$(this).tab('show');
				});

				$('#reportTab a').on('click', function(e) {
					e.preventDefault();
					$(this).tab('show');
				});

				$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
					localStorage.setItem('activeTab', $(e.target).attr('href'));
				});

				var activeTab = localStorage.getItem('activeTab');
				if (activeTab) {
					$('#reportTabs a[href="' + activeTab + '"]').tab('show');
				}

				$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
					$.fn.dataTable.tables({
						visible: true,
						api: true
					}).columns.adjust();
				});
			});

			function exportData(format) {
				// Check if data table exists
				if (typeof window.dataTable !== 'undefined') {
					switch (format) {
						case 'excel':
							window.dataTable.button('.buttons-excel').trigger();
							break;
						case 'pdf':
							window.dataTable.button('.buttons-pdf').trigger();
							break;
						case 'csv':
							window.dataTable.button('.buttons-csv').trigger();
							break;
						case 'print':
							window.dataTable.button('.buttons-print').trigger();
							break;
						default:
							alert('Format export tidak dikenali');
					}
				} else {
					alert('Tidak ada data untuk di-export. Silakan filter data terlebih dahulu.');
				}
			}

			function validateFilterForm() {
				var cbcbg = $('#cbcbg').val();
				var filterType = $('input[name="filter_type"]:checked').val();

				if (!cbcbg && !filterType) {
					alert('Silakan pilih minimal satu filter');
					return false;
				}
				return true;
			}

			// Form validation for report tab
			function validateReportForm() {
				var dateFrom = $('#date_from').val();
				var dateTo = $('#date_to').val();

				if (dateFrom && dateTo && dateFrom > dateTo) {
					alert('Tanggal dari tidak boleh lebih besar dari tanggal sampai');
					return false;
				}
				return true;
			}

			// Add form validation on submit
			$('form').on('submit', function(e) {
				var activeTab = $('.nav-link.active').attr('href');

				if (activeTab === '#filter') {
					if (!validateFilterForm()) {
						e.preventDefault();
					}
				} else if (activeTab === '#report') {
					if (!validateReportForm()) {
						e.preventDefault();
					}
				}
			});

			// Auto-set today's date for date inputs
			$(document).ready(function() {
				var today = new Date().toISOString().split('T')[0];
				$('#date_to').val(today);

				// Set date_from to 30 days ago
				var thirtyDaysAgo = new Date();
				thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
				$('#date_from').val(thirtyDaysAgo.toISOString().split('T')[0]);
			});
		</script>
	@endsection
