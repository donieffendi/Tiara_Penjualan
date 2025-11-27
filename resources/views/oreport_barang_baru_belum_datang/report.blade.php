@extends('layouts.plain')

@section('styles')
<link rel="stylesheet" href="{{url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
{{-- <link rel="stylesheet" href="{{url('https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css') }}"> --}}

@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Barang Baru Belum Datang</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Barang Baru Belum Datang</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				@if (isset($error))
					<div class="alert alert-danger">
						<i class="fas fa-exclamation-triangle mr-2"></i>{{ $error }}
					</div>
				@endif

				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form method="GET" action="{{ route('get-barangbarubelumdatang-report') }}" id="reportForm">
									@csrf

									<div class="row align-items-end mb-4">
										<div class="col-3">
											<div class="form-group">
												<label for="cbg">Cabang:</label>
												<select class="form-control" id="cbg" name="cbg">
													@foreach ($cabangList as $cabang)
														<option value="{{ $cabang->KODE }}" {{ ($selectedCbg ?? '') == $cabang->KODE ? 'selected' : '' }}>
															{{ $cabang->KODE }}
														</option>
													@endforeach
												</select>
											</div>
										</div>
										<div class="col-2">
											<div class="form-group">
												<label for="sub1">Sub Dari:</label>
												<input type="text" class="form-control" id="sub1" name="sub1" value="{{ session()->get('filter_sub1', '') }}"
													placeholder="Kosong">
											</div>
										</div>
										<div class="col-2">
											<div class="form-group">
												<label for="sub2">Sub Sampai:</label>
												<input type="text" class="form-control" id="sub2" name="sub2" value="{{ session()->get('filter_sub2', 'ZZZ') }}"
													placeholder="ZZZ">
											</div>
										</div>
										<div class="col-4">
											<button class="btn btn-primary" type="submit" name="process" value="1" id="btnFilter">
												<i class="fas fa-search mr-1"></i>Proses
											</button>
											<button class="btn btn-danger ml-2" type="button" onclick="resetFilter()">
												<i class="fas fa-redo mr-1"></i>Reset
											</button>
											<button class="btn btn-warning" type="button" onclick="cetakLaporan()">
												<i class="fas fa-print mr-1"></i>Cetak Laporan
											</button>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-12">
											{{-- <button class="btn btn-success mr-2" type="button" onclick="exportData('excel')">
												<i class="fas fa-file-excel mr-1"></i>Export Excel
											</button> --}}
											
										</div>
									</div>
								</form>

								<div class="report-content">
									@if (count($hasilData ?? []) > 0)
										<div class="table-responsive">
											<table class="table-hover table-striped table-bordered compact table" id="barang-baru-belum-datang-table">
												<thead>
													<tr>
														<th>Outlet</th>
														<th>Jenis</th>
														<th>Sub Item</th>
														<th>Nama Barang</th>
														<th>Ukuran</th>
														<th>Supp</th>
														<th>Tgl Ada</th>
														<th>Stok</th>
														<th>Tgl Beli AKhir</th>
														<th>No SP</th>
														<th>Order</th>
													</tr>
												</thead>
												<tbody>
													@foreach ($hasilData as $item)
														<tr>
															<td>{{ $item->ondc ?? '' }}</td>
															<td>{{ $item->sub ?? '' }}</td>
															<td>{{ $item->kd_brg ?? '' }}</td>
															<td>{{ $item->na_brg ?? '' }}</td>
															<td>{{ $item->ket_kem ?? '' }}</td>
															<td>{{ $item->supp ?? '' }}</td>
															<td>{{ $item->tgl_ada ? date('d/m/Y', strtotime($item->tgl_ada)) : '' }}</td>
															<td class="text-right">{{ number_format($item->stok ?? 0, 0, ',', '.') }}</td>
															<td>{{ $item->tgl_po ? date('d/m/Y', strtotime($item->tgl_po)) : '' }}</td>
															<td>{{ $item->no_bukti ?? '' }}</td>
															<td class="text-right">{{ number_format($item->rop ?? 0, 0, ',', '.') }}</td>
														</tr>
													@endforeach
												</tbody>
											</table>
										</div>
									@else
										<div class="alert alert-info">
											<i class="fas fa-info-circle mr-2"></i>
											Silakan pilih cabang dan parameter pencarian, kemudian klik tombol "Proses" untuk menampilkan data barang baru belum datang.
										</div>
									@endif
								</div>

								@if (count($hasilData ?? []) > 0)
									<div class="row mt-4">
										<div class="col-12">
											<div class="card card-outline card-info">
												<div class="card-header">
													<h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Ringkasan Data</h3>
												</div>
												<div class="card-body">
													<div class="row">
														<div class="col-md-3">
															<div class="info-box bg-success">
																<span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Barang</span>
																	<span class="info-box-number">{{ count($hasilData) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-primary">
																<span class="info-box-icon"><i class="fas fa-store"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Supplier Unik</span>
																	<span class="info-box-number">{{ count(collect($hasilData)->unique('supp')) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-warning">
																<span class="info-box-icon"><i class="fas fa-boxes"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total ROP</span>
																	<span class="info-box-number">{{ number_format(collect($hasilData)->sum('rop'), 0, ',', '.') }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-info">
																<span class="info-box-icon"><i class="fas fa-warehouse"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Stok</span>
																	<span class="info-box-number">{{ number_format(collect($hasilData)->sum('stok'), 0, ',', '.') }}</span>
																</div>
															</div>
														</div>
													</div>
													<div class="row mt-2">
														<div class="col-12">
															<small class="text-muted">
																<i class="fas fa-building mr-1"></i>Cabang:
																<strong>{{ $selectedCbg ?? '' }}</strong> |
																<i class="fas fa-filter mr-1"></i>Sub:
																<strong>{{ $sub1 ?? '' ?: 'Kosong' }} - {{ $sub2 ?? 'ZZZ' }}</strong> |
																<i class="fas fa-clock mr-1"></i>Generated: {{ date('d/m/Y H:i:s') }}
															</small>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
<script src="{{url('AdminLTE/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{url('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>
<script src="{{url('http://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js') }}"></script>
<!-- Buttons dan Export -->
<script src="{{url('https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js') }}"></script>
<script src="{{url('https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js') }}"></script>
<script src="{{url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js') }}"></script>

	<script>
		$(document).ready(function() {
			initializeDataTables();

			// $('#reportForm').on('submit', function(e) {
			// 	$('.btn-primary').html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
			// 	$('.btn-primary').prop('disabled', true);
			// });

			$('#sub1').on('blur', function() {
				if ($(this).val() !== '') {
					$('#sub2').val($(this).val());
				} else {
					$('#sub2').val('ZZZ');
				}
			});

			// $('#cbg').on('change', function() {
			// 	$('#reportForm').submit();
			// });
		});

		function initializeDataTables() {
			var commonOptions = {
				pageLength: 25,
				searching: true,
				ordering: true,
				responsive: true,
				// scrollX: true,
				fixedHeader: true,
				dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                    "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                    "<'row'<'col-md-12'B>>" +
                    "<'row'<'col-md-12'tr>>" +
                    "<'row'<'col-md-5'i><'col-md-7'p>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Data Galeri'
                    }
                ],
			};

			var tableOptions = {
				...commonOptions,
				columnDefs: [{
					className: 'dt-center',
					targets: [0, 1, 2, 3, 4, 5, 6, 8,9]
				}, {
					className: 'dt-right',
					targets: [7,10]
				}]
			};

			if ($('#barang-baru-belum-datang-table').length && $('#barang-baru-belum-datang-table tbody tr').length > 0) {
				var table = $('#barang-baru-belum-datang-table').DataTable(tableOptions);
				window.barangBaruBelumDatangTable = table;
			}
		}

		function resetFilter() {
			if (confirm('Apakah Anda yakin ingin mereset filter?')) {
				$('#sub1').val('');
				$('#sub2').val('ZZZ');
				$('#reportForm').submit();
			}
		}

		function exportData(format) {
			var params = new URLSearchParams({
				cbg: $('#cbg').val(),
				sub1: $('#sub1').val(),
				sub2: $('#sub2').val(),
				format: format
			});

			var url = '{{ route('jasper-barangbarubelumdatang-report') }}?' + params.toString();
			downloadReport(url);
		}

		function cetakLaporan() {
			var params = new URLSearchParams({
				cbg: $('#cbg').val(),
				sub1: $('#sub1').val(),
				sub2: $('#sub2').val()
			});

			var url = '{{ route('jasper-barangbarubelumdatang-report') }}?' + params.toString();
			printReport(url);
		}

		function downloadReport(url) {
			$('.btn-success').html('<i class="fas fa-spinner fa-spin mr-1"></i>Exporting...');
			$('.btn-success').prop('disabled', true);

			var form = $('<form>', {
				'method': 'POST',
				'action': url
			});

			form.append($('<input>', {
				'type': 'hidden',
				'name': '_token',
				'value': $('meta[name="csrf-token"]').attr('content')
			}));

			form.appendTo('body').submit().remove();

			setTimeout(function() {
				$('.btn-success').html('<i class="fas fa-file-excel mr-1"></i>Export Excel');
				$('.btn-success').prop('disabled', false);
			}, 3000);
		}

		function printReport(url) {
			var form = $('<form>', {
				'method': 'POST',
				'action': url,
				'target': '_blank'
			});

			form.append($('<input>', {
				'type': 'hidden',
				'name': '_token',
				'value': $('meta[name="csrf-token"]').attr('content')
			}));

			form.appendTo('body').submit().remove();
		}

		$(function() {
			$('[data-toggle="tooltip"]').tooltip();
		});

		$(document).keydown(function(e) {
			if (e.ctrlKey && e.keyCode === 70) {
				e.preventDefault();
				var activeTable = $('#barang-baru-belum-datang-table').DataTable();
				if (activeTable) {
					activeTable.search('').draw();
					$('.dataTables_filter input').focus();
				}
			}
		});

		$(window).on('resize', function() {
			$.fn.dataTable.tables({
				visible: true,
				api: true
			}).columns.adjust().responsive.recalc();
		});
	</script>

	<style>
		.table-responsive {
			border: 1px solid #dee2e6;
			border-radius: 0.25rem;
		}

		.info-box {
			border-radius: 0.5rem;
			box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
		}

		.badge {
			font-size: 0.7em;
		}

		.compact.table td {
			padding: 0.3rem;
			font-size: 0.875rem;
		}

		.compact.table th {
			padding: 0.5rem 0.3rem;
			font-size: 0.875rem;
			font-weight: 600;
		}

		.text-right {
			text-align: right !important;
		}

		.text-center {
			text-align: center !important;
		}

		.alert {
			margin: 1rem;
		}

		.form-control:focus {
			border-color: #80bdff;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
		}

		.btn:focus {
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
		}

		.dataTables_wrapper .dataTables_length select {
			width: 75px;
		}

		.dataTables_wrapper .dataTables_filter input {
			border-radius: 0.25rem;
		}

		@media (max-width: 768px) {

			.col-3,
			.col-2,
			.col-4 {
				margin-bottom: 1rem;
			}

			.btn-group .btn {
				margin-bottom: 0.5rem;
			}
		}

		@media (max-width: 576px) {
			.card-body {
				padding: 0.5rem;
			}

			.btn {
				font-size: 0.875rem;
				padding: 0.375rem 0.5rem;
			}

			.table-responsive {
				font-size: 0.8rem;
			}
		}

		@media print {

			.btn,
			.card-header,
			.breadcrumb {
				display: none !important;
			}

			.table {
				font-size: 10px;
			}

			.info-box {
				break-inside: avoid;
			}
		}

		.table-responsive {
			max-height: 70vh;
			overflow-y: auto;
		}

		.table thead th {
			position: sticky;
			top: 0;
			background-color: #f8f9fa;
			z-index: 10;
		}

		.card {
			box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
		}

		.card-outline.card-info {
			border-top: 3px solid #17a2b8;
		}

		.table-responsive::-webkit-scrollbar {
			width: 8px;
			height: 8px;
		}

		.table-responsive::-webkit-scrollbar-track {
			background: #f1f1f1;
		}

		.table-responsive::-webkit-scrollbar-thumb {
			background: #c1c1c1;
			border-radius: 4px;
		}

		.table-responsive::-webkit-scrollbar-thumb:hover {
			background: #a8a8a8;
		}

		.form-group label {
			font-weight: 500;
			margin-bottom: 0.25rem;
		}
	</style>
@endsection
