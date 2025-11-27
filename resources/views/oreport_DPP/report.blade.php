@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Agenda Per Tanggal (DPP)</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Agenda Per Tanggal</li>
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

								<!-- Form Filter -->
								<form method="POST" action="{{ url('jasper-dpp-report') }}">
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
													<label><strong>Supplier :</strong></label>
													<select name="suplier" id="suplier" class="form-control suplier" style="width: 200px">
														<option value="">--Pilih Supplier--</option>
														<option value="DC" {{ $selectedSuplier == 'DC' ? 'selected' : '' }}>DC</option>
														<option value="NON-DC" {{ $selectedSuplier == 'NON-DC' ? 'selected' : '' }}>NON-DC</option>
													</select>
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
													<button class="btn btn-danger mr-1" type="button" id="resetfilter" onclick="window.location='{{ url('rdpp') }}'">Reset</button>
													<button class="btn btn-warning" type="submit" id="cetak" name="cetak" formtarget="_blank">Cetak</button>
												</div>
											</div>
										</div>
									</div>

									<!-- DataTable Agenda Per Tanggal -->
									<div class="pt-3">
										<div class="report-content" col-md-12>
											<?php
											use koolreport\datagrid\DataTables;
											
											if (!empty($hasilAgendaPerTanggal)) {
											    $nu = 1; // Initialize counter for NU column
											
											    DataTables::create([
											        'dataSource' => array_map(function ($row) use (&$nu) {
											            $row = (array) $row; // Convert to array if object
											            $row['nu'] = $nu++; // Add NU (numbering) column
											            return $row;
											        }, $hasilAgendaPerTanggal),
											        'name' => 'agendaPerTanggalTable',
											        'fastRender' => true,
											        'fixedHeader' => true,
											        'scrollX' => true,
											        'showFooter' => true,
											        'columns' => [
											            'nu' => [
											                'label' => 'NU',
											                'type' => 'number',
											            ],
											            'tgl_posted' => [
											                'label' => 'Tanggal',
											                'type' => 'date',
											                'format' => 'd-m-Y',
											            ],
											            'bruto' => [
											                'label' => 'Brutto',
											                'type' => 'number',
											                'decimals' => 0,
											                'prefix' => 'Rp. ',
											            ],
											            'prom' => [
											                'label' => 'Promosi',
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
											            'DPP' => [
											                'label' => 'Nil Net(DPP)',
											                'type' => 'number',
											                'decimals' => 0,
											                'prefix' => 'Rp. ',
											            ],
											            'nett' => [
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
											                    'targets' => [2, 3, 4, 5, 6], // kolom angka
											                ],
											                [
											                    'className' => 'dt-center',
											                    'targets' => [0, 1], // kolom NU dan tanggal
											                ],
											            ],
											            'order' => [[1, 'asc']], // Order by tanggal ascending
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
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
