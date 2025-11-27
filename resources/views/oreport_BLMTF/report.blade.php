@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Bayar Transfer (BLMTF)</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Bayar Transfer</li>
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
								<form method="POST" action="{{ url('rblmtf') }}">
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
													<label><strong>Status Transfer :</strong></label>
													<select name="status_transfer" id="status_transfer" class="form-control" style="width: 200px">
														<option value="">--Pilih Status--</option>
														<option value="Belum" {{ $selectedStatus == 'Belum' ? 'selected' : '' }}>Belum</option>
														<option value="Sudah" {{ $selectedStatus == 'Sudah' ? 'selected' : '' }}>Sudah</option>
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
													<button class="btn btn-danger mr-1" type="button" id="resetfilter" onclick="window.location='{{ url('rblmtf') }}'">Reset</button>
													<button class="btn btn-warning" type="submit" id="cetak" name="cetak" formtarget="_blank">Cetak</button>
												</div>
											</div>
										</div>
									</div>

									<!-- DataTable Bayar Transfer -->
									<div class="pt-3">
										<div class="report-content" col-md-12>
											<?php
											use koolreport\datagrid\DataTables;
											
											if (!empty($hasilBayarTransfer)) {
											    $nu = 1; // Initialize counter for NU column
											
											    DataTables::create([
											        'dataSource' => array_map(function ($row) use (&$nu) {
											            $row = (array) $row; // Convert to array if object
											            $row['nu'] = $nu++; // Add NU (numbering) column
											            return $row;
											        }, $hasilBayarTransfer),
											        'name' => 'bayarTransferTable',
											        'fastRender' => true,
											        'fixedHeader' => true,
											        'scrollX' => true,
											        'showFooter' => true,
											        'columns' => [
											            'nu' => [
											                'label' => 'NU',
											                'type' => 'number',
											            ],
											            'no_bukti' => [
											                'label' => 'No Bayar',
											                'type' => 'string',
											            ],
											            'kodes' => [
											                'label' => 'Supp',
											                'type' => 'string',
											            ],
											            'namas' => [
											                'label' => 'Nama Supplier',
											                'type' => 'string',
											            ],
											            'jtempo' => [
											                'label' => 'J.Tempo',
											                'type' => 'date',
											                'format' => 'd-m-Y',
											            ],
											            'total' => [
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
											                    'targets' => [5], // kolom Total
											                ],
											                [
											                    'className' => 'dt-center',
											                    'targets' => [0, 4], // kolom NU dan J.Tempo
											                ],
											                [
											                    'className' => 'dt-left',
											                    'targets' => [1, 2, 3], // kolom No Bayar, Supp, Nama Supplier
											                ],
											            ],
											            'order' => [[1, 'asc']], // Order by No Bayar ascending
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
