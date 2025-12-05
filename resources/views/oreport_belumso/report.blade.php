@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Barang SO</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Laporan Barang SO</li>
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
								<form method="GET" action="{{ route('get-belumso-report') }}" id="macetForm">
									@csrf
									<div class="row align-items-end mb-3">
										<div class="col-md-2">
											<label for="sub">Sub <span class="text-danger">*</span></label>
											<div class="input-group">
												<input type="text" name="sub" id="sub" class="form-control" placeholder="Sub"
													value="{{ session()->get('filter_sub') }}" required>
											</div>
										</div>

										<div class="col-md-2  mb-2">
											<label for="tglDr">Tanggal</label>
											<input class="form-control date tglDr" id="tglDr" name="tglDr"
											type="text" autocomplete="off" value="{{ session()->get('filter_tglDari') }}"> 
										</div>
										<div class="col-md-2  mb-2">
											<label for="tglSmp">s.d.</label>
											<input class="form-control date tglSmp" id="tglSmp" name="tglSmp"
											type="text" autocomplete="off" value="{{ session()->get('filter_tglSampai') }}">
										</div>

										<div class="col-md-2 mb-2">
											<input type="checkbox" name="belum" id="belum" value="1" {{ session()->get('filter_belum') == 1 ? 'checked' : '' }}>
											<label for="belum">Belum SO</label>
										</div>

										<div class="col-md-4 mb-2 text-right">
											<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
												<i class="fas fa-search mr-1"></i>Proses
											</button>
											<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
												<i class="fas fa-undo mr-1"></i>Reset
											</button>
											<button class="btn btn-success mr-1" type="submit" name="action" value="cetak" formaction="{{ url('jasper-belumso-report') }}"
												formmethod="POST" formtarget="_blank">
												<i class="fas fa-print mr-1"></i>Cetak
											</button>
										</div>
									</div>

									<!-- Active Filter Display -->
									@if (session()->get('filter_sub') && session()->get('filter_tglDari') && session()->get('filter_tglSampai') && session()->get('filter_belum'))
										<div class="row mb-3">
											<div class="col-12">
												<div class="alert alert-info">
													<strong>Filter Aktif:</strong>
													Sub: {{ session()->get('filter_sub') }} |
													Tanggal: {{ session()->get('filter_tglDari') }} - {{ session()->get('filter_tglSampai') }} |
													Belum SO: {{ session()->get('filter_belum') == 1 ? 'Belum SO' : 'Sudah SO' }}
												</div>
											</div>
										</div>
									@endif
								</form>

								<!-- Data Table Section -->
								<div class="report-content">
									@if ($belumSO && count($belumSO) > 0)
										<?php
										\koolreport\datagrid\DataTables::create([
										    'dataSource' => $belumSO,
										    'name' => 'barangMacetTable',
										    'fastRender' => true,
										    'fixedHeader' => true,
										    // 'scrollX' => true,
										    'showFooter' => false,
										    'columns' => [
										        'KD_BRG' => [
										            'label' => 'Sub Item',
										        ],
												'NA_BRG' => [
										            'label' => 'Nama Barang',
												],
												'KET_UK' => [
										            'label' => 'Ukuran',
										        ],
												'KET_KEM' => [
										            'label' => 'Kemasan',
										        ],
												'BARCODE' => [
										            'label' => 'Barcode',
										        ],
										        'STOK' => [
										            'label' => 'Stok',
										            'type' => 'number',
										            'decimals' => 2,
										        ],
												'NO_BUKTI' => [
										            'label' => 'No. SO',
										        ],
												'TGL_SO' => [
										            'label' => 'Tanggal SO',
										        ],
												'QTY_SO' => [
										            'label' => 'Qty SO',
										            'type' => 'number',
										            'decimals' => 2,
										        ],
												'KET_SO' => [
										            'label' => 'Keterangan SO',
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
										                'targets' => [5,8], // numeric columns
										            ],
										            [
										                'className' => 'dt-center',
										                'targets' => [0,1,2,3,4,6,7,9], // center aligned columns
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
										                        'title' => 'Report Belum SO',
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
											Tidak ada data SO.
										</div>
									@else
										<div class="alert alert-info text-center">
											<i class="fas fa-info-circle mr-2"></i>
											Silakan Masukkan Sub dan Tanggal untuk menampilkan data SO.
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
			$('.date').datepicker({
				dateFormat: 'dd-mm-yy'
			});

			// Auto-resize table on window resize
			$(window).on('resize', function() {
				if ($.fn.DataTable.isDataTable('#barangMacetTable')) {
					$('#barangMacetTable').DataTable().columns.adjust().responsive.recalc();
				}
			});

			// Form validation
			$('#macetForm').on('submit', function(e) {
				var sub = $('#sub').val();
				var tglDr = $('#tglDr').val();
				var tglSmp = $('#tglSmp').val();

				if (!sub) {
					alert('Harap pilih Sub terlebih dahulu');
					e.preventDefault();
					return false;
				}

				if (!tglDr) {
					alert('Harap pilih Tanggal terlebih dahulu');
					e.preventDefault();
					return false;
				}

				if (!tglSmp) {
					alert('Harap pilih Tanggal Sampai terlebih dahulu');
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
			$('#tglSmp').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					$('#macetForm').find('button[name="action"][value="filter"]').click();
				}
			});
		});

		// Reset form function
		function resetForm() {
			window.location.href = '{{ route('rbelumso') }}';
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
