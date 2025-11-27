@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Pajak</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Pajak</li>
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
								<form method="POST" action="{{ url('jasper-pajak-report') }}">
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
													<button class="btn btn-danger mr-1" type="button" id="resetfilter" onclick="window.location='{{ url('rpajak') }}'">Reset</button>
													<button class="btn btn-warning" type="submit" id="cetak" name="cetak" formtarget="_blank">Cetak</button>
												</div>
											</div>
										</div>
									</div>
									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="piutang-tab" data-toggle="tab" href="#piutang" role="tab" aria-controls="piutang"
												aria-selected="true">Dari Piutang</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="nonbeli-tab" data-toggle="tab" href="#nonbeli" role="tab" aria-controls="nonbeli" aria-selected="false">Dari
												Pembelian Non</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Tab 1: Dari Piutang (PIU) -->
										<div class="tab-pane fade show active" id="piutang" role="tabpanel" aria-labelledby="piutang-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													// Menggunakan alias untuk menghindari konflik nama class
													use koolreport\datagrid\DataTables as KoolDataTables;

													if ($hasilPIU) {
													    KoolDataTables::create([
													        'dataSource' => $hasilPIU,
													        'name' => 'piutangTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'showFooter' => 'bottom',
													        'columns' => [
													            'no_bukti' => [
													                'label' => 'Faktur',
													            ],
													            'kodec' => [
													                'label' => 'Cust',
													            ],
													            'namac' => [
													                'label' => 'Nama',
													            ],
													            'tgl' => [
													                'label' => 'Tgl',
													                'type' => 'date',
													                'format' => 'd/m/Y',
													            ],
													            'dpp' => [
													                'label' => 'Pendapatan',
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
													            'total' => [
													                'label' => 'Total',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'pph1' => [
													                'label' => 'PPH 23',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'pph2' => [
													                'label' => 'PPH 4(2)',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'notes' => [
													                'label' => 'Notes',
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
													                    'targets' => [4, 5, 6, 7, 8], // kolom angka
													                ],
													                [
													                    'className' => 'dt-center',
													                    'targets' => [0, 1, 3], // kolom tengah
													                ],
													            ],
													            'order' => [[3, 'desc']], // Order by tanggal descending
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

										<!-- Tab 2: Dari Pembelian Non (NonBeli) -->
										<div class="tab-pane fade" id="nonbeli" role="tabpanel" aria-labelledby="nonbeli-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													if ($hasilNonBeli) {
													    KoolDataTables::create([
													        'dataSource' => $hasilNonBeli,
													        'name' => 'nonbeliTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'showFooter' => 'bottom',
													        'columns' => [
													            'NO_BUKTI' => [
													                'label' => 'Faktur',
													            ],
													            'KODES' => [
													                'label' => 'Supp',
													            ],
													            'namas' => [
													                'label' => 'Nama',
													            ],
													            'TGL' => [
													                'label' => 'Tgl',
													                'type' => 'date',
													                'format' => 'd/m/Y',
													            ],
													            'total' => [
													                'label' => 'Pendapatan',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'PPH1' => [
													                'label' => 'PPH 21',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'PPH2' => [
													                'label' => 'PPH 23',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'PPH3' => [
													                'label' => 'PPH 4(2)',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'PPN' => [
													                'label' => 'PPN',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'MATERAI' => [
													                'label' => 'Materai',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'DISKON' => [
													                'label' => 'Diskon',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'NETT' => [
													                'label' => 'Total',
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
													                    'targets' => [4, 5, 6, 7, 8, 9, 10, 11], // kolom angka
													                ],
													                [
													                    'className' => 'dt-center',
													                    'targets' => [0, 1, 3], // kolom tengah
													                ],
													            ],
													            'order' => [[3, 'desc']], // Order by tanggal descending
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
											if ($('#piutang-tab').hasClass('active')) {
												$('#current_tipe').val('1'); // PIU
											} else if ($('#nonbeli-tab').hasClass('active')) {
												$('#current_tipe').val('2'); // NonBeli
											}
										}

										// Handle tab switching
										$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
											updateTipeValue();
										});

										// Set initial tipe value based on current tab
										@if ($tipe == '2')
											// Switch to NonBeli tab if tipe is 2
											$('#piutang-tab').removeClass('active');
											$('#piutang').removeClass('show active');
											$('#nonbeli-tab').addClass('active');
											$('#nonbeli').addClass('show active');
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
