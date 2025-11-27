@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Beli Dan Musnah</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Beli Dan Musnah</li>
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
								<form method="POST" action="{{ url('jasper-beli-dan-musnah-report') }}">
									@csrf
									<div class="pt-3">
										<div class="form-group">
											<div class="row align-items-baseline">
												<div class="col-2 mb-2">
													<label><strong>CBG :</strong></label>
													<select name="cbcbg" id="cbcbg" class="form-control cbcbg" style="width: 200px">
														<option value="">--Pilih CBG--</option>
														@foreach ($cbg as $cbgD)
															<option value="{{ $cbgD->CBG }}" {{ $selectedCbg == $cbgD->CBG ? 'selected' : '' }}>{{ $cbgD->CBG }}
															</option>
														@endforeach
													</select>
												</div>
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
													<label for="supplier_dari">Supplier Dari</label>
													<input type="text" name="supplier_dari" id="supplier_dari" class="form-control" value="{{ $selectedSupplierDari }}"
														placeholder="Kode supplier">
												</div>
												<div class="col-2 mb-2">
													<label for="supplier_sampai">Supplier Sampai</label>
													<input type="text" name="supplier_sampai" id="supplier_sampai" class="form-control" value="{{ $selectedSupplierSampai }}"
														placeholder="Kode supplier">
												</div>
											</div>
											<div class="row align-items-baseline">
												<div class="col-2 mb-2">
													<label for="tglDr">Tanggal Dari</label>
													<input type="date" name="tglDr" id="tglDr" class="form-control" value="{{ $tglDr }}">
												</div>
												<div class="col-2 mb-2">
													<label for="tglSmp">Tanggal Sampai</label>
													<input type="date" name="tglSmp" id="tglSmp" class="form-control" value="{{ $tglSmp }}">
												</div>
												<div class="col-4"></div>

												<div class="col-4 mb-2 text-right">
													<button class="btn btn-primary mr-1" type="submit" id="filter" name="filter">Filter</button>
													<button class="btn btn-danger mr-1" type="button" id="resetfilter" onclick="window.location='{{ url('rbdm') }}'">Reset</button>
													<button class="btn btn-warning" type="submit" id="cetak" name="cetak" formtarget="_blank">Cetak</button>
												</div>
											</div>
										</div>
									</div>

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="rekap-beli-tab" data-toggle="tab" href="#rekap-beli" role="tab" aria-controls="rekap-beli"
												aria-selected="true">Rekap Beli</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="sub-beli-tab" data-toggle="tab" href="#sub-beli" role="tab" aria-controls="sub-beli" aria-selected="false">Sub
												Beli</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="rekap-musnah-tab" data-toggle="tab" href="#rekap-musnah" role="tab" aria-controls="rekap-musnah"
												aria-selected="false">Rekap Musnah</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="sub-musnah-tab" data-toggle="tab" href="#sub-musnah" role="tab" aria-controls="sub-musnah"
												aria-selected="false">Sub Musnah</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="beli-musnah-tab" data-toggle="tab" href="#beli-musnah" role="tab" aria-controls="beli-musnah"
												aria-selected="false">Beli Musnah</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Tab 1: Rekap Beli -->
										<div class="tab-pane fade show active" id="rekap-beli" role="tabpanel" aria-labelledby="rekap-beli-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													// Menggunakan alias untuk menghindari konflik nama class
													use koolreport\datagrid\DataTables as KoolDataTables;
													
													if ($hasilTipe1) {
													    KoolDataTables::create([
													        'dataSource' => $hasilTipe1,
													        'name' => 'rekapBeliTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'showFooter' => 'bottom',
													        'columns' => [
													            'kodes' => [
													                'label' => 'Supplier',
													            ],
													            'namas' => [
													                'label' => 'Nama',
													            ],
													            'bruto' => [
													                'label' => 'Bruto',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'prom' => [
													                'label' => 'Dis Promosi',
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
													                    'targets' => [2, 3], // kolom angka
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
													            'order' => [[0, 'asc']], // Order by supplier code ascending
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

										<!-- Tab 2: Sub Beli -->
										<div class="tab-pane fade" id="sub-beli" role="tabpanel" aria-labelledby="sub-beli-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													if ($hasilTipe2) {
													    KoolDataTables::create([
													        'dataSource' => $hasilTipe2,
													        'name' => 'subBeliTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'showFooter' => 'bottom',
													        'columns' => [
													            'sub' => [
													                'label' => 'Sub',
													            ],
													            'kd_brg' => [
													                'label' => 'Kode Barang',
													            ],
													            'na_brg' => [
													                'label' => 'Nama Barang',
													            ],
													            'bruto' => [
													                'label' => 'Bruto',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'prom' => [
													                'label' => 'Prom',
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
													                    'targets' => [3, 4], // kolom angka
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
													}
													?>
												</div>
											</div>
										</div>

										<!-- Tab 3: Rekap Musnah -->
										<div class="tab-pane fade" id="rekap-musnah" role="tabpanel" aria-labelledby="rekap-musnah-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													if ($hasilTipe6) {
													    KoolDataTables::create([
													        'dataSource' => $hasilTipe6,
													        'name' => 'rekapMusnahTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'showFooter' => 'bottom',
													        'columns' => [
													            'kodes' => [
													                'label' => 'Supplier',
													            ],
													            'namas' => [
													                'label' => 'Nama',
													            ],
													            'bruto' => [
													                'label' => 'Bruto',
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
													                    'targets' => [2], // kolom angka
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
													            'order' => [[0, 'asc']], // Order by supplier code ascending
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

										<!-- Tab 4: Sub Musnah -->
										<div class="tab-pane fade" id="sub-musnah" role="tabpanel" aria-labelledby="sub-musnah-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													if ($hasilTipe3) {
													    KoolDataTables::create([
													        'dataSource' => $hasilTipe3,
													        'name' => 'subMusnahTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'showFooter' => 'bottom',
													        'columns' => [
													            'sub' => [
													                'label' => 'Sub',
													            ],
													            'kd_brg' => [
													                'label' => 'Kode Barang',
													            ],
													            'na_brg' => [
													                'label' => 'Nama Barang',
													            ],
													            'bruto' => [
													                'label' => 'Bruto',
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
													                    'targets' => [3], // kolom angka
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
													}
													?>
												</div>
											</div>
										</div>

										<!-- Tab 5: Beli Musnah -->
										<div class="tab-pane fade" id="beli-musnah" role="tabpanel" aria-labelledby="beli-musnah-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													if ($hasilTipe4) {
													    KoolDataTables::create([
													        'dataSource' => $hasilTipe4,
													        'name' => 'beliMusnahTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'showFooter' => 'bottom',
													        'columns' => [
													            'kodes' => [
													                'label' => 'Kodes',
													            ],
													            'namas' => [
													                'label' => 'Nama Supplier',
													            ],
													            'beli' => [
													                'label' => 'Beli',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'musnah' => [
													                'label' => 'Musnah',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'gtotal' => [
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
													                    'targets' => [2, 3, 4], // kolom angka
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
													            'order' => [[0, 'asc']], // Order by kodes ascending
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
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
