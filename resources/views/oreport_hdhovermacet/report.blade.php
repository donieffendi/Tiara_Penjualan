@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Hadiah Poin Overstok / Macet</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Hadiah Poin Overstok / Macet</li>
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
								<form method="GET" action="{{ route('get-hdhovermacet-report') }}" id="macetForm">
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
											<label for="jenis">Periode</label>
											<select name="jenis" id="jenis" class="form-control" required>
												<option value="">Pilih Jenis Report</option>
												<option value="OVERSTOK" {{ session()->get('filter_jenis') == 'OVERSTOK' ? 'selected' : '' }}> OVERSTOK</option>
												<option value="MACET" {{ session()->get('filter_jenis') == 'MACET' ? 'selected' : '' }}> MACET </option>
											</select>
										</div>

										<div class="col-md-2  mb-2">
											<label for="per">Periode</label>
											<select name="per" id="per" class="form-control" required>
												<option value="">Pilih Periode</option>
												<option value="1" {{ session()->get('filter_per') == 1 ? 'selected' : '' }}> 1 - Desember s/d Juni </option>
												<option value="2" {{ session()->get('filter_per') == 2 ? 'selected' : '' }}> 2 - Juni s/d Desember </option>
											</select>
										</div>

										<div class="col-md-2  mb-2">
											<label for="yer">Tahun</label>
											<select name="yer" id="yer" class="form-control" required>
												<option value="">Pilih Tahun</option>
												<option value="2020" {{ session()->get('filter_yer') == 2020 ? 'selected' : '' }}> 2020 </option>
												<option value="2021" {{ session()->get('filter_yer') == 2021 ? 'selected' : '' }}> 2021 </option>
												<option value="2022" {{ session()->get('filter_yer') == 2022 ? 'selected' : '' }}> 2022 </option>
												<option value="2023" {{ session()->get('filter_yer') == 2023 ? 'selected' : '' }}> 2023 </option>
												<option value="2024" {{ session()->get('filter_yer') == 2024 ? 'selected' : '' }}> 2024 </option>
												<option value="2025" {{ session()->get('filter_yer') == 2025 ? 'selected' : '' }}> 2025 </option>
											</select>
										</div>
										<div class="col-md-4 mb-2 text-right">
											<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
												<i class="fas fa-search mr-1"></i>Proses
											</button>
											<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
												<i class="fas fa-undo mr-1"></i>Reset
											</button>
											{{-- <button class="btn btn-success mr-1" type="submit" name="action" value="cetak" formaction="{{ url('jasper-barangmacetkosong-report') }}"
												formmethod="POST" formtarget="_blank">
												<i class="fas fa-print mr-1"></i>Cetak
											</button> --}}
											{{-- <button class="btn btn-info" type="button" onclick="exportData('excel')">
												<i class="fas fa-file-excel mr-1"></i>Export Excel
											</button> --}}
										</div>
									</div>

									<!-- Active Filter Display -->
									@if (session()->get('filter_cbg') && session()->get('filter_jenis') && session()->get('filter_per') && session()->get('filter_yer'))
										<div class="row mb-3">
											<div class="col-12">
												<div class="alert alert-info">
													<strong>Filter Aktif:</strong>
													Cabang: {{ session()->get('filter_cbg') }} |
													Jenis Report: {{ session()->get('filter_jenis') }} |
													Periode: {{ session()->get('filter_per') }} |
													Tahun: {{ session()->get('filter_yer') }}
												</div>
											</div>
										</div>
									@endif
								</form>

								<!-- Data Table Section -->
								<div class="report-content">
									@if ($hasilMacet && count($hasilMacet) > 0)
										<?php
										\koolreport\datagrid\DataTables::create([
										    'dataSource' => $hasilMacet,
										    'name' => 'barangMacetTable',
										    'fastRender' => true,
										    'fixedHeader' => true,
										    // 'scrollX' => true,
										    'showFooter' => false,
										    'columns' => [
										        'NOITEM' => [
										            'label' => 'No Item',
										        ],
										        'NA_BRG' => [
										            'label' => 'Nama Barang',
										        ],
										        'KET_UK' => [
										            'label' => 'Ukuran',
										        ],
										        'KET_KEM' => [
										            'label' => 'Ket. Kemasan',
										        ],
										        'S_AW' => [
										            'label' => 'S Awal',
										            'type' => 'number',
										            'decimals' => 2,
										        ],
										        'S_BL' => [
										            'label' => 'Beli',
										            'type' => 'number',
										            'decimals' => 2,
										        ],
												'S_AK' => [
										            'label' => 'Stok',
										            'type' => 'number',
										            'decimals' => 2,
										        ],
										        'QTY_OVER' => [
										            'label' => '35% S Awal+Beli',
										            'type' => 'number',
										            'decimals' => 0,
										        ],
												'TGL_TRM' => [
										            'label' => 'Tgl Beli AKhir',
										        ],
												'GAK00' => [
										            'label' => 'Stok Gudang',
										            'type' => 'number',
										            'decimals' => 0,
										        ],
												'AK00' => [
										            'label' => 'Stok Toko',
										            'type' => 'number',
										            'decimals' => 0,
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
										                'targets' => [4,5,6,7,9,10], // numeric columns
										            ],
										            [
										                'className' => 'dt-center',
										                'targets' => [0,1,2,3,8], // center aligned columns
										            ],
										        ],
										        'order' => [[0, 'asc']], // Order by Sub Item
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
										                        'title' => 'Report Barang Macet',
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
											Tidak ada data hadiah overstok / macet ditemukan untuk filter yang dipilih.
										</div>
									@else
										<div class="alert alert-info text-center">
											<i class="fas fa-info-circle mr-2"></i>
											Silakan pilih cabang dan jenis report untuk menampilkan data hadiah overstok / macet.
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
				var jenis = $('#jenis').val();
				var per = $('#per').val();
				var yer = $('#yer').val();

				if (!cbg) {
					alert('Harap pilih cabang terlebih dahulu');
					e.preventDefault();
					return false;
				}

				if (!jenis) {
					alert('Harap pilih jenis report terlebih dahulu');
					e.preventDefault();
					return false;
				}

				if (!per) {
					alert('Harap pilih periode terlebih dahulu');
					e.preventDefault();
					return false;
				}

				if (!yer) {
					alert('Harap pilih tahun terlebih dahulu');
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
			$('#jenis').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					$('#macetForm').find('button[name="action"][value="filter"]').click();
				}
			});
		});

		// Reset form function
		function resetForm() {
			window.location.href = '{{ route('rhdhovermacet') }}';
		}

		// Export functions
		function exportData(format) {
			var cbg = $('#cbg').val();
			var jenis = $('#jenis').val();
			var per = $('#per').val();
			var yer = $('#yer').val();

			if (!cbg || !jenis) {
				alert('Harap pilih cabang dan jenis report terlebih dahulu');
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
							'action': '{{ route('jasper-barangmacetkosong-report') }}',
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
							'name': 'jenis',
							'value': jenis
						}));

						form.appendTo('body').submit().remove();
				}
			} else {
				// Alternative: server-side export
				var form = $('<form>', {
					'method': 'POST',
					'action': '{{ route('jasper-hdhovermacet-report') }}',
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
					'name': 'jenis',
					'value': jenis
				}));

				form.appendTo('body').submit().remove();
			}
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
