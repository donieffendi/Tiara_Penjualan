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
						<h1 class="m-0">Laporan Barang Datang</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Laporan Barang Datang</li>
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
							<form action="{{ route('rbrgdtg.jasper') }}" method="GET" id="frmBrgdtg">
								@csrf
								<div class="row">

									<div class="col-md-2">
										<div class="form-group mb-2">
											<label for="kdbrg">Sub</label>
											<div class="input-group">
												<input type="text" name="min" id="min" class="form-control" value="{{ $min ?? '' }}" placeholder="Dari">
												<div class="input-group-text">s/d</div>
												<input type="text" name="max" id="max" class="form-control" value="{{ $max ?? '' }}" placeholder="Sampai">
											</div>
										</div>
									</div>

									<div class="col-md-2">
										<div class="form-group mb-2">
											<label for="time">Diatas Jam</label>
											<input type="time" name="time" id="time" class="form-control" value="{{ $time ?? '07:00' }}">
										</div>
									</div>

								</div>
								<div class="row">
									<div class="col-md-2">
										<div class="form-group mb-2">
											<label for="sub_item">Sub Item</label>
											<input type="text" name="sub_item" id="sub_item" class="form-control" value="{{ $sub_item ?? '' }}" placeholder="Sub Item">
										</div>
									</div>
								</div>
								<div class="row">

									<div class="col-md-2">
										<div class="form-group mb-2">
											<label for="tglDr">Tanggal</label>
											<input type="date" name="tglDr" id="tglDr" class="form-control" value="{{ $tglDr ?? date('Y-m-d') }}">
										</div>
									</div>

									<div class="col-7"></div>
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
									<h5 class="mb-3"><i class="fas fa-boxes mr-2"></i>Laporan Barang Datang</h5>
									<?php
									if (!empty($hasil)) {
									    KoolDataTables::create([
									        'dataSource' => $hasil,
									        'name' => 'brgdtgTable',
									        'fastRender' => true,
									        'fixedHeader' => true,
									        'scrollX' => true,
									        'showFooter' => true,
									        'columns' => [
									            'tanggal' => [
									                'label' => 'Tanggal',
									                'type' => 'date',
									                'formatValue' => function ($value) {
									                    return date('d/m/Y', strtotime($value));
									                },
									            ],
									            'jam' => ['label' => 'Jam'],
									            'sub' => ['label' => 'Sub'],
									            'kode' => ['label' => 'Kode'],
									            'nama_barang' => ['label' => 'Nama Barang'],
									            'kemasan' => ['label' => 'Kemasan'],
									            'kd' => ['label' => 'KD'],
									            'dtr' => ['label' => 'DTR'],
									            'harga_pcs' => [
									                'label' => 'Harga/Pcs',
									                'type' => 'number',
									                'formatValue' => function ($value) {
									                    return number_format($value, 0, ',', '.');
									                },
									            ],
									            'qty' => [
									                'label' => 'Qty',
									                'type' => 'number',
									                'formatValue' => function ($value) {
									                    return number_format($value, 0, ',', '.');
									                },
									            ],
									            'no_faktur' => ['label' => 'No Faktur'],
									            'no_bukti' => ['label' => 'No Bukti'],
									            'keterangan' => ['label' => 'Keterangan'],
									            'operator' => ['label' => 'Operator'],
									            'no_po' => ['label' => 'No PO'],
									        ],
									        'cssClass' => [
									            'table' => 'table table-hover table-striped table-bordered compact',
									        ],
									        'options' => [
									            'columnDefs' => [['className' => 'dt-right', 'targets' => [8, 9]]], // Harga/Pcs dan Qty
									            'dom' => 'Blfrtip',
									            'buttons' => [['extend' => 'collection', 'text' => 'Export', 'buttons' => ['copy', 'excel', 'csv', 'pdf', 'print']]],
									        ],
									    ]);
									} else {
									    echo '<div class="alert alert-info">Tidak ada data untuk ditampilkan. Silakan pilih filter dan klik tombol Filter.</div>';
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

					if (!cbg) {
						alert('Harap pilih CBG');
						return false;
					}

					return true;
				}

				// Form submission dengan validasi
				$('#frmBrgdtg').on('submit', function(e) {
					if (!validateForm()) {
						e.preventDefault();
					}
				});

				// Auto-set today's date for date inputs
				var today = new Date().toISOString().split('T')[0];
				$('#tglDr').val(today);
			});
		</script>
	@endsection
