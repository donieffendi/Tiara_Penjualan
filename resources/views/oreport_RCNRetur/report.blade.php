@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Rencana Retur</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Rencana Retur</li>
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
								<form method="POST" action="{{ url('jasper-rrcnretur-report') }}">
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
													<button class="btn btn-primary mr-1" type="submit" id="filter" name="filter">Filter</button>
													<button class="btn btn-danger mr-1" type="button" id="resetfilter" onclick="window.location='{{ url('rrcnretur') }}'">Reset</button>
													<button class="btn btn-warning" type="submit" id="cetak" name="cetak" formtarget="_blank">Cetak</button>
												</div>
											</div>
										</div>
									</div>

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="rr-belum-rb-tab" data-toggle="tab" href="#rr-belum-rb" role="tab" aria-controls="rr-belum-rb"
												aria-selected="true">RR Belum RB</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="rr-menjadi-rb-tab" data-toggle="tab" href="#rr-menjadi-rb" role="tab" aria-controls="rr-menjadi-rb"
												aria-selected="false">RR Menjadi RB</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Tab 1: RR Belum RB -->
										<div class="tab-pane fade show active" id="rr-belum-rb" role="tabpanel" aria-labelledby="rr-belum-rb-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													// Menggunakan alias untuk menghindari konflik nama class
													use koolreport\datagrid\DataTables as KoolDataTables;
													
													if (!empty($hasilDataTable1)) {
													    KoolDataTables::create([
													        'dataSource' => $hasilDataTable1,
													        'name' => 'rrBelumRBTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'columns' => [
													            'NO_BUKTI' => [
													                'label' => 'No Bukti',
													            ],
													            'tgl' => [
													                'label' => 'Tanggal',
													                'type' => 'date',
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
													            'nett' => [
													                'label' => 'Nilai Nett',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'DPP' => [
													                'label' => 'Total Bayar',
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
													                    'targets' => [5, 6, 7, 8, 9, 10], // kolom angka
													                ],
													                [
													                    'className' => 'dt-center',
													                    'targets' => [0, 1, 2, 3], // kolom tengah
													                ],
													                [
													                    'className' => 'dt-left',
													                    'targets' => [4], // kolom kiri
													                ],
													            ],
													            'order' => [[0, 'asc']], // Order by NO_BUKTI ascending
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
													} else {
													    echo '<div class="alert alert-info">Tidak ada data RR Belum RB untuk periode yang dipilih.</div>';
													}
													?>
												</div>
											</div>
										</div>

										<!-- Tab 2: RR Menjadi RB -->
										<div class="tab-pane fade" id="rr-menjadi-rb" role="tabpanel" aria-labelledby="rr-menjadi-rb-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													if (!empty($hasilDataTable2)) {
													    KoolDataTables::create([
													        'dataSource' => $hasilDataTable2,
													        'name' => 'rrMenjadiRBTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'columns' => [
													            'NO_BUKTI' => [
													                'label' => 'No Bukti',
													            ],
													            'tgl' => [
													                'label' => 'Tanggal',
													                'type' => 'date',
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
													            'nett' => [
													                'label' => 'Nilai Nett',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'DPP' => [
													                'label' => 'Total Bayar',
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
													                    'targets' => [5, 6, 7, 8, 9, 10], // kolom angka
													                ],
													                [
													                    'className' => 'dt-center',
													                    'targets' => [0, 1, 2, 3], // kolom tengah
													                ],
													                [
													                    'className' => 'dt-left',
													                    'targets' => [4], // kolom kiri
													                ],
													            ],
													            'order' => [[0, 'asc']], // Order by NO_BUKTI ascending
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
													} else {
													    echo '<div class="alert alert-info">Tidak ada data RR Menjadi RB untuk periode yang dipilih.</div>';
													}
													?>
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
