@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Distribusi Outlet</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Distribusi Outlet</li>
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
								<form method="POST" action="{{ url('jasper-tfbrg-report') }}">
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
											</div>
											<div class="row align-items-baseline">
												<div class="col-2 mb-2">
													<label><strong>Outlet :</strong></label>
													<select name="cbcbg" id="cbcbg" class="form-control cbcbg" style="width: 200px">
														<option value="">--Pilih Outlet--</option>
														@foreach ($cbg as $cbgD)
															<option value="{{ $cbgD->CBG }}" {{ $selectedCbg == $cbgD->CBG ? 'selected' : '' }}>{{ $cbgD->CBG }}
															</option>
														@endforeach
													</select>
												</div>

												<div class="col-6 mb-2"></div>

												<div class="col-4 mb-2 text-right">
													<button class="btn btn-primary mr-1" type="submit" id="filter" name="filter">Filter</button>
													<button class="btn btn-danger mr-1" type="button" id="resetfilter" onclick="window.location='{{ url('rtfbrg') }}'">Reset</button>
													<button class="btn btn-warning" type="submit" id="cetak" name="cetak" formtarget="_blank">Cetak</button>
												</div>
											</div>
										</div>
									</div>

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="report-tab" data-toggle="tab" href="#report" role="tab" aria-controls="report"
												aria-selected="true">Report</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="details-tab" data-toggle="tab" href="#details" role="tab" aria-controls="details"
												aria-selected="false">Details</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="report-dck-tab" data-toggle="tab" href="#report-dck" role="tab" aria-controls="report-dck"
												aria-selected="false">Report DCK</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="detail-dck-tab" data-toggle="tab" href="#detail-dck" role="tab" aria-controls="detail-dck"
												aria-selected="false">Detail DCK</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Tab 1: Report -->
										<div class="tab-pane fade show active" id="report" role="tabpanel" aria-labelledby="report-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													// Menggunakan alias untuk menghindari konflik nama class
													use koolreport\datagrid\DataTables as KoolDataTables;
													
													if (!empty($hasilDataTable1)) {
													    KoolDataTables::create([
													        'dataSource' => $hasilDataTable1,
													        'name' => 'reportTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'columns' => [
													            'SUB' => [
													                'label' => 'Sub',
													            ],
													            'KELOMPOK' => [
													                'label' => 'Kelompok',
													            ],
													            'TOTAL_OO' => [
													                'label' => 'Total Kirim',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'TOTAL_BZ' => [
													                'label' => 'Total Terima',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'TOTAL_OX' => [
													                'label' => 'Total Tolak',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'QTY_OO' => [
													                'label' => 'Qty Kirim',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'QTY_BZ' => [
													                'label' => 'Qty Terima',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'QTY_OZ' => [
													                'label' => 'Qty Tolak',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'SELISIH' => [
													                'label' => 'Selisih',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'TOLAK' => [
													                'label' => 'Keterangan',
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
													                    'targets' => [2, 3, 4, 5, 6, 7, 8], // kolom angka
													                ],
													                [
													                    'className' => 'dt-center',
													                    'targets' => [0], // kolom tengah
													                ],
													                [
													                    'className' => 'dt-left',
													                    'targets' => [1, 9], // kolom kiri
													                ],
													            ],
													            'order' => [[0, 'asc']], // Order by sub ascending
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
													    echo '<div class="alert alert-info">Tidak ada data untuk periode yang dipilih.</div>';
													}
													?>
												</div>
											</div>
										</div>

										<!-- Tab 2: Details -->
										<div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													if (!empty($hasilDataTable4)) {
													    KoolDataTables::create([
													        'dataSource' => $hasilDataTable4,
													        'name' => 'detailsTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'columns' => [
													            'KD_BRG' => [
													                'label' => 'SubItem',
													            ],
													            'NA_BRG' => [
													                'label' => 'Nama Barang',
													            ],
													            'TOTAL_OO' => [
													                'label' => 'Total Kirim',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'TOTAL_BZ' => [
													                'label' => 'Total Terima',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'TOTAL_OX' => [
													                'label' => 'Total Tolak',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'QTY_OO' => [
													                'label' => 'Qty Kirim',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'QTY_BZ' => [
													                'label' => 'Qty Terima',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'QTY_OZ' => [
													                'label' => 'Qty Tolak Outlet',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'SELISIH' => [
													                'label' => 'Selisih',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'TOLAK' => [
													                'label' => 'Keterangan',
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
													                    'targets' => [2, 3, 4, 5, 6, 7, 8], // kolom angka
													                ],
													                [
													                    'className' => 'dt-center',
													                    'targets' => [0], // kolom tengah
													                ],
													                [
													                    'className' => 'dt-left',
													                    'targets' => [1, 9], // kolom kiri
													                ],
													            ],
													            'order' => [[0, 'asc']], // Order by kode barang ascending
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
													    echo '<div class="alert alert-info">Tidak ada data untuk periode yang dipilih.</div>';
													}
													?>
												</div>
											</div>
										</div>

										<!-- Tab 3: Report DCK -->
										<div class="tab-pane fade" id="report-dck" role="tabpanel" aria-labelledby="report-dck-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													if (!empty($hasilDataTable2)) {
													    KoolDataTables::create([
													        'dataSource' => $hasilDataTable2,
													        'name' => 'reportDCKTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'columns' => [
													            'sub' => [
													                'label' => 'Sub',
													            ],
													            'kelompok' => [
													                'label' => 'Kelompok',
													            ],
													            'TOTAL_OO' => [
													                'label' => 'Total Kirim',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'TOTAL_BZ' => [
													                'label' => 'Total Terima',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'QTY_OO' => [
													                'label' => 'Qty Kirim',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'QTY_BZ' => [
													                'label' => 'Qty Terima',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'S_QTY' => [
													                'label' => 'Selisih Qty',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'S_TOTAL' => [
													                'label' => 'Selisih Total',
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
													                    'targets' => [2, 3, 4, 5, 6, 7], // kolom angka
													                ],
													                [
													                    'className' => 'dt-center',
													                    'targets' => [0], // kolom tengah
													                ],
													                [
													                    'className' => 'dt-left',
													                    'targets' => [1], // kolom kiri
													                ],
													            ],
													            'order' => [[0, 'asc']], // Order by sub ascending
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
													    echo '<div class="alert alert-info">Tidak ada data untuk periode yang dipilih.</div>';
													}
													?>
												</div>
											</div>
										</div>

										<!-- Tab 4: Detail DCK -->
										<div class="tab-pane fade" id="detail-dck" role="tabpanel" aria-labelledby="detail-dck-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													if (!empty($hasilDataTable3)) {
													    KoolDataTables::create([
													        'dataSource' => $hasilDataTable3,
													        'name' => 'detailDCKTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'columns' => [
													            'no_bukti' => [
													                'label' => 'No Bukti',
													            ],
													            'kd_brg' => [
													                'label' => 'SubItem',
													            ],
													            'na_brg' => [
													                'label' => 'Nama Barang',
													            ],
													            'TOTAL_OO' => [
													                'label' => 'Total Kirim',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'TOTAL_BZ' => [
													                'label' => 'Total Terima',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'QTY_OO' => [
													                'label' => 'Qty Kirim',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'QTY_BZ' => [
													                'label' => 'Qty Terima',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'S_QTY' => [
													                'label' => 'Selisih Qty',
													                'type' => 'number',
													                'decimals' => 0,
													            ],
													            'S_TOTAL' => [
													                'label' => 'Selisih Total',
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
													                    'targets' => [3, 4, 5, 6, 7, 8], // kolom angka
													                ],
													                [
													                    'className' => 'dt-center',
													                    'targets' => [0, 1], // kolom tengah
													                ],
													                [
													                    'className' => 'dt-left',
													                    'targets' => [2], // kolom kiri
													                ],
													            ],
													            'order' => [[0, 'asc']], // Order by no bukti ascending
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
													    echo '<div class="alert alert-info">Tidak ada data untuk periode yang dipilih.</div>';
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
