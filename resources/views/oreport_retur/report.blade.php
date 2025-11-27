@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Retur</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Retur</li>
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

								<!-- Form Filter (shared between tabs) -->
								<form method="POST" action="{{ url('jasper-retur-report') }}">
									@csrf
									<div class="pt-3">
										<div class="form-group">
											<div class="row align-items-baseline">

												<div class="col-2 mb-2">
													<label><strong>Periode :</strong></label>
													<select name="periode" id="periode" class="form-control periode" style="width: 200px">
														<option value="">--Pilih Periode--</option>
														@foreach ($per as $perD)
															<option value="{{ $perD->PERIO }}" {{ $selectedPeriode == $perD->PERIO ? 'selected' : '' }}>{{ $perD->PERIO }}
															</option>
														@endforeach
													</select>
												</div>

											</div>
											<div class="row align-items-baseline">
												<div class="col-2 mb-2">
													<label><strong>Supplier :</strong></label>
													<select name="suplier" id="suplier" class="form-control suplier" style="width: 200px">
														<option value="">--Pilih Supplier--</option>
														<option value="DC" {{ $selectedSuplier == 'DC' ? 'selected' : '' }}>DC Supplier</option>
														<option value="NON-DC" {{ $selectedSuplier == 'NON-DC' ? 'selected' : '' }}>Non-DC Supplier</option>
													</select>
												</div>
												<div class="col-2 mb-2">
													<label for="tglDr">Tanggal Dari</label>
													<input type="date" name="tglDr" id="tglDr" class="form-control" value="{{ $tglDr }}">
												</div>
												<div class="col-2 mb-2">
													<label for="tglSmp">Tanggal Sampai</label>
													<input type="date" name="tglSmp" id="tglSmp" class="form-control" value="{{ $tglSmp }}">
												</div>
												<div class="col-2"></div>

												<div class="col-4 mb-2 text-right">
													<input type="hidden" name="tipe" id="current_tipe" value="{{ $tipe }}">
													<button class="btn btn-primary mr-1" type="submit" id="filter" name="filter">Filter</button>
													<button class="btn btn-danger mr-1" type="button" id="resetfilter" onclick="window.location='{{ url('rretur') }}'">Reset</button>
													<button class="btn btn-warning" type="submit" id="cetak" name="cetak" formtarget="_blank">Cetak</button>
												</div>
											</div>
										</div>
									</div>

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="sudah-dipotong-tab" data-toggle="tab" href="#sudah-dipotong" role="tab" aria-controls="sudah-dipotong"
												aria-selected="true">Sudah Dipotong</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="belum-dipotong-tab" data-toggle="tab" href="#belum-dipotong" role="tab" aria-controls="belum-dipotong"
												aria-selected="false">Belum Dipotong</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Tab 1: Sudah Dipotong (Retur Sudah Bayar) -->
										<div class="tab-pane fade show active" id="sudah-dipotong" role="tabpanel" aria-labelledby="sudah-dipotong-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													// Menggunakan alias untuk menghindari konflik nama class
													use koolreport\datagrid\DataTables as KoolDataTables;
													
													if ($hasilSudahBayar) {
													    KoolDataTables::create([
													        'dataSource' => $hasilSudahBayar,
													        'name' => 'sudahDipotongTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'showFooter' => 'bottom',
													        'columns' => [
													            'NO_BUKTI' => [
													                'label' => 'No Bukti',
													            ],
													            'tgl' => [
													                'label' => 'Tanggal',
													                'type' => 'date',
													                'format' => 'd/m/Y',
													            ],
													            'NO_tagi' => [
													                'label' => 'Ref',
													            ],
													            'KODES' => [
													                'label' => 'Supplier',
													            ],
													            'NAMAS' => [
													                'label' => 'Nama',
													            ],
													            'KLB' => [
													                'label' => 'KLB',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'bruto' => [
													                'label' => 'Bruto',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'PROM' => [
													                'label' => 'Dis Promosi',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'ppn' => [
													                'label' => 'PPN',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
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
													                    'targets' => [5, 6, 7, 8], // kolom angka
													                ],
													                [
													                    'className' => 'dt-center',
													                    'targets' => [0, 1, 2, 3], // kolom tengah
													                ],
													            ],
													            'order' => [[1, 'desc']], // Order by tanggal descending
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
													}
													?>
												</div>
											</div>
										</div>

										<!-- Tab 2: Belum Dipotong (Retur Belum Bayar) -->
										<div class="tab-pane fade" id="belum-dipotong" role="tabpanel" aria-labelledby="belum-dipotong-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													if ($hasilBelumBayar) {
													    KoolDataTables::create([
													        'dataSource' => $hasilBelumBayar,
													        'name' => 'belumDipotongTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'showFooter' => 'bottom',
													        'columns' => [
													            'NO_BUKTI' => [
													                'label' => 'No Bukti',
													            ],
													            'tgl' => [
													                'label' => 'Tanggal',
													                'type' => 'date',
													                'format' => 'd/m/Y',
													            ],
													            'NO_tagi' => [
													                'label' => 'Ref',
													            ],
													            'KODES' => [
													                'label' => 'Supplier',
													            ],
													            'NAMAS' => [
													                'label' => 'Nama',
													            ],
													            'KLB' => [
													                'label' => 'KLB',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'bruto' => [
													                'label' => 'Bruto',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'PROM' => [
													                'label' => 'Dis Promosi',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'ppn' => [
													                'label' => 'PPN',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
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
													                    'targets' => [5, 6, 7, 8], // kolom angka
													                ],
													                [
													                    'className' => 'dt-center',
													                    'targets' => [0, 1, 2, 3], // kolom tengah
													                ],
													            ],
													            'order' => [[1, 'desc']], // Order by tanggal descending
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
													}
													?>
												</div>
											</div>
										</div>
									</div>
								</form>

								<script>
									$(document).ready(function() {
										// Set tipe value based on active tab
										function updateTipeValue() {
											if ($('#sudah-dipotong-tab').hasClass('active')) {
												$('#current_tipe').val('1'); // Retur Sudah Bayar
											} else if ($('#belum-dipotong-tab').hasClass('active')) {
												$('#current_tipe').val('6'); // Retur Belum Bayar
											}
										}

										// Handle tab switching
										$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
											updateTipeValue();
										});

										// Set initial tipe value based on current tab
										@if ($tipe == '6')
											// Switch to Belum Dipotong tab if tipe is 6
											$('#sudah-dipotong-tab').removeClass('active');
											$('#sudah-dipotong').removeClass('show active');
											$('#belum-dipotong-tab').addClass('active');
											$('#belum-dipotong').addClass('show active');
										@endif

										// Set initial tipe value
										updateTipeValue();
									});
								</script>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
