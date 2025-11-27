@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Biaya Pemasaran</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Biaya Pemasaran</li>
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
								<form method="POST" action="{{ url('jasper-biaya-pemasaran-report') }}">
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
													<label for="promosi">Biaya Pemasaran</label>
													<div class="input-group">
														<input type="number" name="promosi" id="promosi" class="form-control" value="{{ $selectedPromosi }}" step="0.01">
														<div class="input-group-append">
															<span class="input-group-text">%</span>
														</div>
													</div>
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
													<button class="btn btn-danger mr-1" type="button" id="resetfilter"
														onclick="window.location='{{ url('rbiaya-pemasaran') }}'">Reset</button>
													<button class="btn btn-warning" type="submit" id="cetak" name="cetak" formtarget="_blank">Cetak</button>
												</div>
											</div>
										</div>
									</div>

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="rekap-supplier-tab" data-toggle="tab" href="#rekap-supplier" role="tab" aria-controls="rekap-supplier"
												aria-selected="true">Rekap per Supplier</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="rekap-agenda-tab" data-toggle="tab" href="#rekap-agenda" role="tab" aria-controls="rekap-agenda"
												aria-selected="false">Rekap per Agenda</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Tab 1: Rekap per Supplier -->
										<div class="tab-pane fade show active" id="rekap-supplier" role="tabpanel" aria-labelledby="rekap-supplier-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													// Menggunakan alias untuk menghindari konflik nama class
													use koolreport\datagrid\DataTables as KoolDataTables;
													
													if ($hasilPerSupBeli) {
													    KoolDataTables::create([
													        'dataSource' => $hasilPerSupBeli,
													        'name' => 'rekapSupplierTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'showFooter' => 'bottom',
													        'columns' => [
													            'KODES' => [
													                'label' => 'No Supplier',
													            ],
													            'NAMAS' => [
													                'label' => 'Nama Supplier',
													            ],
													            'PEMBELIAN' => [
													                'label' => 'Pembelian',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'DPP' => [
													                'label' => 'DPP',
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
													            'PPH' => [
													                'label' => 'PPH (2%)',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'DIBAYAR' => [
													                'label' => 'Dibayar',
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
													                    'targets' => [2, 3, 4, 5, 6], // kolom angka
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

										<!-- Tab 2: Rekap per Agenda -->
										<div class="tab-pane fade" id="rekap-agenda" role="tabpanel" aria-labelledby="rekap-agenda-tab">
											<div class="pt-3">
												<div class="report-content" col-md-12>
													<?php
													if ($hasilPerAgendaBeli) {
													    KoolDataTables::create([
													        'dataSource' => $hasilPerAgendaBeli,
													        'name' => 'rekapAgendaTable',
													        'fastRender' => true,
													        'fixedHeader' => true,
													        'scrollX' => true,
													        'showFooter' => true,
													        'showFooter' => 'bottom',
													        'columns' => [
													            'KODES' => [
													                'label' => 'No Supplier',
													            ],
													            'NAMAS' => [
													                'label' => 'Nama Supplier',
													            ],
													            'NO_BUKTI' => [
													                'label' => 'No Bukti',
													            ],
													            'TGL' => [
													                'label' => 'Tanggal',
													                'type' => 'date',
													                'format' => 'd/m/Y',
													            ],
													            'PEMBELIAN' => [
													                'label' => 'Pembelian',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'DPP' => [
													                'label' => 'DPP',
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
													            'PPH' => [
													                'label' => 'PPH(2%)',
													                'type' => 'number',
													                'decimals' => 0,
													                'prefix' => 'Rp. ',
													            ],
													            'DIBAYAR' => [
													                'label' => 'Dibayar',
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
													                    'targets' => [4, 5, 6, 7, 8], // kolom angka
													                ],
													                [
													                    'className' => 'dt-center',
													                    'targets' => [0, 2, 3], // kolom tengah
													                ],
													                [
													                    'className' => 'dt-left',
													                    'targets' => [1], // kolom kiri
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
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
