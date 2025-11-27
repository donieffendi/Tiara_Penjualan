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
						<h1 class="m-0">Laporan Non-Pembelian</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Laporan Non-Pembelian</li>
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
							<form action="{{ url('jasper-nond-report') }}" method="GET" id="frmNond">
								@csrf
								<div class="row">
									<div class="col-md-3">
										<div class="form-group mb-2">
											<label for="periode">Periode</label>
											<select name="periode" id="periode" class="form-control" required>
												<option value="">Periode</option>
												@foreach ($periode as $per)
													<option value="{{ $per->PERIODE }}" {{ isset($selectedPeriode) && $selectedPeriode == $per->PERIODE ? 'selected' : '' }}>
														{{ $per->PERIO }}
													</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group mb-2">
											<label for="tglDr">Tanggal Dari</label>
											<input type="date" name="tglDr" id="tglDr" class="form-control" value="{{ $tglDr ?? date('Y-m-d') }}">
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group mb-2">
											<label for="tglSmp">Tanggal Sampai</label>
											<input type="date" name="tglSmp" id="tglSmp" class="form-control" value="{{ $tglSmp ?? date('Y-m-d') }}">
										</div>
									</div>
									<div class="col-2"></div>
									<div class="col-3">
										<div>
											<button class="btn btn-primary mr-1" type="submit" id="filter" name="filter">Filter</button>
											<button class="btn btn-danger mr-1" type="button" id="resetfilter" onclick="window.location='{{ url('rnond') }}'">Reset</button>
											<button class="btn btn-warning" type="submit" id="cetak" formtarget="_blank">Cetak</button>
										</div>
									</div>
								</div>
							</form>

							<hr>
							<!-- Report Content -->
							<div class="report-content">
								<div class="pt-3">
									<h5 class="mb-3"><i class="fas fa-file-invoice mr-2"></i>Laporan Agenda Non-Dagang</h5>
									<?php
									if (!empty($hasilNonBeli)) {
									    KoolDataTables::create([
									        'dataSource' => $hasilNonBeli,
									        'name' => 'nonBeliTable',
									        'fastRender' => true,
									        'fixedHeader' => true,
									        'scrollX' => true,
									        'showFooter' => true,
									        'columns' => [
									            'bukti_pembayaran' => ['label' => 'Bukti Pembayaran'],
									            'agenda' => ['label' => 'Agenda'],
									            'tgl' => [
									                'label' => 'Tgl',
									                'type' => 'date',
									                'formatValue' => function ($value) {
									                    return date('d/m/Y', strtotime($value));
									                },
									            ],
									            'acno' => ['label' => 'Acno'],
									            'uraian' => ['label' => 'Uraian'],
									            'reff' => ['label' => 'Reff'],
									            'total' => [
									                'label' => 'Total',
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
									            'columnDefs' => [['className' => 'dt-right', 'targets' => [6]]], // Hanya kolom Total (index 6)
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
	@endsection

	@section('javascripts')
		<script>
			$(document).ready(function() {
				// Fungsi validasi form
				function validateForm() {
					var cbg = $('select[name="cbcbg"]').val();
					var periode = $('select[name="periode"]').val();

					if (!cbg) {
						alert('Harap pilih CBG');
						return false;
					}

					if (!periode) {
						alert('Harap pilih Periode');
						return false;
					}

					return true;
				}

				// Form submission dengan validasi
				$('form').on('submit', function(e) {
					if (!validateForm()) {
						e.preventDefault();
					}
				});

				// Auto-set today's date for date inputs
				var today = new Date().toISOString().split('T')[0];
				$('#tglSmp').val(today);

				// Set date_from to 30 days ago
				var thirtyDaysAgo = new Date();
				thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
				$('#tglDr').val(thirtyDaysAgo.toISOString().split('T')[0]);
			});
		</script>
	@endsection
