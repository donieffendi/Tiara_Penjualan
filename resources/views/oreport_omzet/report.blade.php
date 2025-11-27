@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Omzet</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Omzet</li>
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
								<!-- Nav tabs -->
								<form method="POST" action="{{ url('jasper-bayar-report') }}">
									@csrf

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Filter Tab -->
										<div class="tab-pane fade show active" id="filter" role="tabpanel" aria-labelledby="filter-tab">
											<div class="pt-3">
												<div class="form-group">
													<div class="row align-items-baseline mb-2">
														<div class="col-4 mb-2">
															<label><strong>Periode :</strong></label>
															<select name="perio" id="perio" class="form-control perio" style="width: 200px">
																<option value="">--Pilih Periode--</option>
																@foreach ($per as $perD)
																	<option value="{{ $perD->PERIO }}" {{ session()->get('filter_periode') == $perD->PERIO ? 'selected' : '' }}>{{ $perD->PERIO }}
																	</option>
																@endforeach
															</select>
														</div>
													</div>
													<div class="row align-items-baseline">
														<div class="col-4 mb-2">
															<label for="cbcbg">Cabang</label>
															<select name="cbcbg" id="cbcbg" class="form-control">
																<option value="">Pilih Cabang</option>
																@foreach ($cbg as $perD)
																	<option value="{{ $perD->CBG }}" {{ session()->get('filter_cbg') == $perD->CBG ? 'selected' : '' }}>{{ $perD->CBG }}
																	</option>
																@endforeach
															</select>
														</div>
														<div class="col-2 mb-2">
															<label for="tgl_awal">Tanggal Awal</label>
															<input type="date" name="tgl_awal" id="tgl_awal" class="form-control">
														</div>
														<div class="col-2 mb-2">
															<label for="tgl_akhir">Tanggal Akhir</label>
															<input type="date" name="tgl_akhir" id="tgl_akhir" class="form-control">
														</div>

														<div class="col-4 mb-2 text-right">
															<button class="btn btn-primary mr-1" type="submit" id="filter" name="filter">Filter</button>
															<button class="btn btn-danger mr-1" type="button" id="resetfilter" onclick="window.location='{{ url('rcust') }}'">Reset</button>
															<button class="btn btn-warning" type="submit" id="cetak" formtarget="_blank">Cetak</button>
														</div>
													</div>
													<div style="margin-bottom: 15px;"></div>
													<div class="report-content" col-md-12>
														<?php
														// Menggunakan alias untuk menghindari konflik nama class
														use koolreport\datagrid\DataTables as KoolDataTables;

														if ($hasil) {
														    KoolDataTables::create([
														        'dataSource' => $hasil,
														        'name' => 'example',
														        'fastRender' => true,
														        'fixedHeader' => true,
														        'scrollX' => true,
														        'showFooter' => true,
														        'showFooter' => 'bottom',
														        'columns' => [
														            'SUB' => [
														                'label' => 'SUB',
														            ],
														            'Kelompok' => [
														                'label' => 'KELOMPOK',
														            ],
														            'TOTAL' => [
														                'label' => 'TOTAL',
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
														                    'targets' => [2, 3, 4, 5, 6],
														                ],
														            ],
														            'order' => [],
														            'paging' => true,
														            // "pageLength" => 12,
														            'searching' => true,
														            'colReorder' => true,
														            'select' => true,
														            'dom' => 'Blfrtip', // B e dilangi
														            // "dom" => '<"row"<col-md-6"B><"col-md-6"f>> <"row"<"col-md-12"t>><"row"<"col-md-12">>',
														            'buttons' => [
														                [
														                    'extend' => 'collection',
														                    'text' => 'Export',
														                    'buttons' => ['copy', 'excel', 'csv', 'pdf', 'print'],
														                ],
														            ],
														        ],
														    ]);
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
				localStorage.setItem('activeTab', $(e.target).attr('href'));
			});

			// Restore active tab from localStorage
			var activeTab = localStorage.getItem('activeTab');
			if (activeTab) {
				$('#reportTabs a[href="' + activeTab + '"]').tab('show');
			}

			// Auto-resize table on tab change
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				$.fn.dataTable.tables({
					visible: true,
					api: true
				}).columns.adjust();
			});
		});

		// Export functions
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

		// Form validation
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
