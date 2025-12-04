@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Stok KD</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Stok KD</li>
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
								<form method="GET" action="{{ route('get-stokkd-report') }}" id="macetForm">
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
											<label for="kode">Kode</label>
											<select name="kode" id="kode" class="form-control" required>
												<option value="">Pilih Kode</option>
												<option value="1" {{ session()->get('filter_kode') == '1' ? 'selected' : '' }}> SEMUA </option>
												<option value="2" {{ session()->get('filter_kode') == '2' ? 'selected' : '' }}> 0,1</option>
												<option value="3" {{ session()->get('filter_kode') == '3' ? 'selected' : '' }}> 4 </option>
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
									@if (session()->get('filter_cbg') && session()->get('filter_kode'))
										<div class="row mb-3">
											<div class="col-12">
												<div class="alert alert-info">
													<strong>Filter Aktif:</strong>
													Cabang: {{ session()->get('filter_cbg') }} |
													KDLAKU: {{ session()->get('filter_kode') }} 
												</div>
											</div>
										</div>
									@endif
								</form>

								<!-- Data Table Section -->
								<div class="report-content">
									@if ($stokKD && count($stokKD) > 0)
										<?php
										\koolreport\datagrid\DataTables::create([
										    'dataSource' => $stokKD,
										    'name' => 'barangMacetTable',
										    'fastRender' => true,
										    'fixedHeader' => true,
										    // 'scrollX' => true,
										    'showFooter' => false,
										    'columns' => [
										        'TD_OD' => [
										            'label' => '*',
										        ],
										        'KDLAKU' => [
										            'label' => 'KD',
										        ],
										        'SUB' => [
										            'label' => 'Sub',
										        ],
										        'KD_BRG' => [
										            'label' => 'Sub Item',
										        ],
												'NA_BRG' => [
										            'label' => 'Nama Barang',
												],
												'KET_UK' => [
										            'label' => 'Ket. Ukuran',
										        ],
												'KET_KEM' => [
										            'label' => 'Ket. Kemasan',
										        ],
												'BARCODE' => [
										            'label' => 'Barcode',
										        ],
										        'AK00' => [
										            'label' => 'Stok',
										            'type' => 'number',
										            'decimals' => 2,
										        ],
										        'GAK00' => [
										            'label' => 'Gd. Transit',
										            'type' => 'number',
										            'decimals' => 2,
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
										                'targets' => [8,9], // numeric columns
										            ],
										            [
										                'className' => 'dt-center',
										                'targets' => [0,1,2,3,4,5,6,7], // center aligned columns
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
											Tidak ada data Stok Barang.
										</div>
									@else
										<div class="alert alert-info text-center">
											<i class="fas fa-info-circle mr-2"></i>
											Silakan pilih cabang dan kode untuk menampilkan data Stok Barang.
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
				var kode = $('#kode').val();

				if (!cbg) {
					alert('Harap pilih cabang terlebih dahulu');
					e.preventDefault();
					return false;
				}

				if (!kode) {
					alert('Harap pilih kode terlebih dahulu');
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
			$('#kode').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					$('#macetForm').find('button[name="action"][value="filter"]').click();
				}
			});
		});

		// Reset form function
		function resetForm() {
			window.location.href = '{{ route('rstokkd') }}';
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
