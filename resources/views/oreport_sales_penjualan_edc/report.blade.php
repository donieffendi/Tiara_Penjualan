@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Sales Penjualan EDC</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Laporan Sales Penjualan EDC</li>
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
								<!-- Filter Section -->
								<form method="GET" action="{{ route('get-salespenjualanedc-report') }}" id="macetForm">
									@csrf
									<div class="row align-items-end mb-3">
										<div class="col-md-2  mb-2">
											<label for="cbg">Cabang</label>
											<select name="cbg" id="cbg" class="form-control" required>
												<option value="">Pilih Cabang</option>
												@foreach ($cbg as $cabang)
													<option value="{{ $cabang->CBG }}" {{ session()->get('filter_cbg') == $cabang->CBG ? 'selected' : '' }}>
														{{ $cabang->CBG }}
													</option>
												@endforeach
											</select>
										</div>

										<div class="col-md-2  mb-2">
											<label for="per">Periode</label>
											<select name="per" id="per" class="form-control" required>
												<option value="">Pilih Periode</option>
												@foreach ($per as $periode)
													<option value="{{ $periode->PERIO }}" {{ session()->get('filter_per') == $periode->PERIO ? 'selected' : '' }}>
														{{ $periode->PERIO }}
													</option>
												@endforeach
											</select>
										</div>

										<div class="col-md-4 mb-2 text-right">
											<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
												<i class="fas fa-search mr-1"></i>Proses
											</button>
											<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
												<i class="fas fa-undo mr-1"></i>Reset
											</button>
											<button class="btn btn-success mr-1" type="submit" name="action" value="cetak" formaction="{{ url('jasper-salespenjualanedc-report') }}"
												formmethod="POST" formtarget="_blank">
												<i class="fas fa-print mr-1"></i>Cetak
											</button>
											{{-- <button class="btn btn-info" type="button" onclick="exportData('excel')">
												<i class="fas fa-file-excel mr-1"></i>Export Excel
											</button> --}}
										</div>
									</div>

									<!-- Active Filter Display -->
									@if (session()->get('filter_cbg') && session()->get('filter_tglDari') && session()->get('filter_tglSampai'))
										<div class="row mb-3">
											<div class="col-12">
												<div class="alert alert-info">
													<strong>Filter Aktif:</strong>
													Cabang: {{ session()->get('filter_cbg') }} |
													Periode: {{ session()->get('filter_per') }}
												</div>
											</div>
										</div>
									@endif
								</form>

								<!-- Data Table Section -->
								<div class="report-content">
									@if ($EDC && count($EDC) > 0)
										<?php
										\koolreport\datagrid\DataTables::create([
										    'dataSource' => $EDC,
										    'name' => 'barangMacetTable',
										    'fastRender' => true,
										    'fixedHeader' => true,
										    // 'scrollX' => true,
										    'showFooter' => false,
										    'columns' => [
										        'NO_BUKTI' => [
										            'label' => 'NO. Bukti',
										        ],
										        'TGL' => [
										            'label' => 'Tanggal',
										        ],
										        'TYPE2' => [
										            'label' => 'Type',
										        ],
												'MID' => [
										            'label' => 'EDC',
												],
												'JUMLAH' => [
										            'label' => 'Total Belanja',
										            'type' => 'number',
										            'decimals' => 2,
										        ],
												'NBANK' => [
										            'label' => 'Bank',
										        ],
										        'NKARTU' => [
										            'label' => 'Kartu',
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
										                'className' => 'dt-center',
										                'targets' => [0,1,2,3,4,5,6], // center aligned columns
										            ],
										        ],
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
										                'buttons' => [
										                    [
										                        'extend' => 'copy',
										                        'text' => 'Copy',
										                    ],
										                    [
										                        'extend' => 'excel',
										                        'text' => 'Excel',
										                        'title' => 'SO R/L',
										                    ],
										                    [
										                        'extend' => 'csv',
										                        'text' => 'CSV',
										                    ],
										                    [
										                        'extend' => 'pdf',
										                        'text' => 'PDF',
										                        'orientation' => 'landscape',
										                        'pageSize' => 'A4',
										                    ],
										                    [
										                        'extend' => 'print',
										                        'text' => 'Print',
										                    ],
										                ],
										            ],
										        ],
										        'lengthMenu' => [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
										        'language' => [
										            'lengthMenu' => 'Tampilkan _MENU_ data per halaman',
										            'zeroRecords' => 'Data tidak ditemukan',
										            'info' => 'Menampilkan halaman _PAGE_ dari _PAGES_',
										            'infoEmpty' => 'Tidak ada data tersedia',
										            'infoFiltered' => '(difilter dari _MAX_ total data)',
										            'search' => 'Cari:',
										            'paginate' => [
										                'first' => 'Pertama',
										                'last' => 'Terakhir',
										                'next' => 'Selanjutnya',
										                'previous' => 'Sebelumnya',
										            ],
										        ],
										    ],
										]);
										?>
									@elseif(request()->has('action') && request()->get('action') == 'filter')
										<div class="alert alert-warning text-center">
											<i class="fas fa-exclamation-triangle mr-2"></i>
											Tidak ada Penjualan EDC.
										</div>
									@else
										<div class="alert alert-info text-center">
											<i class="fas fa-info-circle mr-2"></i>
											Silakan pilih cabang dan Periode untuk menampilkan data Penjualan EDC.
										</div>
									@endif
								</div>
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
			// Auto-resize table on window resize
			$(window).on('resize', function() {
				if ($.fn.DataTable.isDataTable('#barangMacetTable')) {
					$('#barangMacetTable').DataTable().columns.adjust().responsive.recalc();
				}
			});

			// Form validation
			$('#macetForm').on('submit', function(e) {
				var cbg = $('#cbg').val();
				var per = $('#per').val();

				if (!cbg) {
					alert('Harap pilih cabang terlebih dahulu');
					e.preventDefault();
					return false;
				}

				if (!per) {
					alert('Harap pilih periode terlebih dahulu');
					e.preventDefault();
					return false;
				}

				// Show loading for filter action
				if ($('input[name="action"]').val() === 'filter') {
					$('button[name="action"][value="filter"]').html('<i class="fas fa-spinner fa-spin mr-1"></i>Processing...');
					$('button[name="action"][value="filter"]').prop('disabled', true);
				}
			});

			// Enter key handling
			$('#per').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					$('#macetForm').find('button[name="action"][value="filter"]').click();
				}
			});
		});

		function exportData(format) {
			var cbg = $('#cbg').val();
			var per = $('#per').val();

			if (!cbg || !per) {
				alert('Harap pilih cabang dan periode report terlebih dahulu');
				return;
			}

			if (typeof window.barangMacetTable !== 'undefined') {
				switch (format) {
					case 'excel':
						window.barangMacetTable.button('.buttons-excel').trigger();
						break;
					case 'pdf':
						window.barangMacetTable.button('.buttons-pdf').trigger();
						break;
					case 'csv':
						window.barangMacetTable.button('.buttons-csv').trigger();
						break;
					case 'print':
						window.barangMacetTable.button('.buttons-print').trigger();
						break;
					default:
						// Export via server-side
						var form = $('<form>', {
							'method': 'POST',
							'action': '{{ route('jasper-salespenjualanedc-report') }}',
							'target': '_blank'
						});

						form.append($('<input>', {
							'type': 'hidden',
							'name': '_token',
							'value': '{{ csrf_token() }}'
						}));

						form.append($('<input>', {
							'type': 'hidden',
							'name': 'cbg',
							'value': cbg
						}));

						form.append($('<input>', {
							'type': 'hidden',
							'name': 'per',
							'value': per
						}));

						form.appendTo('body').submit().remove();
				}
			} else {
				// Alternative: server-side export
				var form = $('<form>', {
					'method': 'POST',
					'action': '{{ route('jasper-salespenjualanedc-report') }}',
					'target': '_blank'
				});

				form.append($('<input>', {
					'type': 'hidden',
					'name': '_token',
					'value': '{{ csrf_token() }}'
				}));

				form.append($('<input>', {
					'type': 'hidden',
					'name': 'cbg',
					'value': cbg
				}));

				form.append($('<input>', {
					'type': 'hidden',
					'name': 'per',
					'value': per
				}));

				form.appendTo('body').submit().remove();
			}
		}

		// Reset form function
		function resetForm() {
			window.location.href = '{{ route('rsalespenjualanedc') }}';
		}

		// Utility function to format numbers
		function formatNumber(num) {
			return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		}

		// Utility function to format currency
		function formatCurrency(num) {
			return 'Rp ' + formatNumber(num);
		}
	</script>
@endsection