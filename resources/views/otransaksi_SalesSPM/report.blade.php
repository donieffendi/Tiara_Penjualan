@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Penjualan Sales SPM</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Laporan Penjualan Sales SPM</li>
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
								<!-- Display Errors -->
								@if ($errors->any())
									<div class="alert alert-danger">
										<ul class="mb-0">
											@foreach ($errors->all() as $error)
												<li>{{ $error }}</li>
											@endforeach
										</ul>
									</div>
								@endif

								<!-- Nav tabs -->
								<form method="POST" action="{{ url('jasper-tsalesspm-report') }}" id="reportForm">
									@csrf
									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Filter Tab -->
										<div class="tab-pane fade show active" id="filter" role="tabpanel" aria-labelledby="filter-tab">
											<div class="pt-3">
												<div class="form-group">
													<div class="row align-items-baseline mb-2">
														{{-- <div class="col-4 mb-2">
															<label><strong>CBG :</strong></label>
															<select name="cbg" id="cbg" class="form-control" style="width: 200px">
																<option value="">--Pilih CBG--</option>
																@foreach ($cbg as $item)
																	<option value="{{ $item->CBG }}" {{ session('filter_cbg') == $item->CBG ? 'selected' : '' }}>
																		{{ $item->CBG }}
																	</option>
																@endforeach
															</select>
														</div> --}}
														<div class="col-4 mb-2">
															<label><strong>Kasir :</strong></label>
															<select name="ksr" id="ksr" class="form-control kasir" style="width: 200px" required>
																<option value="">--Pilih Kasir--</option>
																@if (isset($kasir))
																	@foreach ($kasir as $item)
																		<option value="{{ $item->kasir }}" {{ session('filter_ksr') == $item->kasir ? 'selected' : '' }}>
																			{{ $item->kasir }}
																		</option>
																	@endforeach
																@endif
															</select>
														</div>
														<div class="col-4 mb-2">

														</div>
													</div>
													<div class="row align-items-baseline">
														<div class="col-4 mb-2">
															<label for="tgl1"><strong>Tanggal</strong></label>
															<input type="date" name="tgl1" id="tgl1" class="form-control" required
																value="{{ session('filter_tgl1') ? date('Y-m-d', strtotime(session('filter_tgl1'))) : date('Y-m-d') }}">
														</div>
														{{-- <div class="col-4 mb-2">
															<label for="bulan"><strong>Bulan (Period)</strong></label>
															<select name="bulan" id="bulan" class="form-control">
																@for ($i = 1; $i <= 12; $i++)
																	<option value="{{ sprintf('%02d', $i) }}" {{ sprintf('%02d', $i) == date('m') ? 'selected' : '' }}>
																		{{ sprintf('%02d', $i) }} - {{ date('F', mktime(0, 0, 0, $i, 1)) }}
																	</option>
																@endfor
															</select>
														</div> --}}

														<div class="col-4 mb-2 text-right">
															<button class="btn btn-primary mr-1" type="button" id="filterBtn" onclick="loadData()">Filter</button>
															<button class="btn btn-danger mr-1" type="button" id="resetfilter" onclick="window.location='{{ url('rtsalesspm') }}'">Reset</button>
															<button class="btn btn-warning" type="submit" id="cetak" formtarget="_blank">Cetak</button>
														</div>
													</div>

													<!-- Hidden fields for report parameters -->
													<input type="hidden" name="no_form" value="RT-SALES-SPM">
													<input type="hidden" name="na_toko" value="{{ config('app.name') }}">
													<input type="hidden" name="typ_pers" value="PERUSAHAAN">
													<input type="hidden" name="alamat_pers" value="Alamat Perusahaan">

													<div style="margin-bottom: 15px;"></div>

													<!-- Loading indicator -->
													<div id="loadingIndicator" class="text-center" style="display: none;">
														<div class="spinner-border text-primary" role="status">
															<span class="sr-only">Loading...</span>
														</div>
														<p>Memuat data...</p>
													</div>

													<div class="report-content" id="reportContent" col-md-12>
														@if (isset($hasil) && count($hasil) > 0)
															<?php
															// Menggunakan alias untuk menghindari konflik nama class
															// use statement dihapus, gunakan nama class secara langsung
															koolreport\datagrid\DataTables::create([
															    'dataSource' => $hasil,
															    'name' => 'salesSPMTable',
															    'fastRender' => true,
															    'fixedHeader' => true,
															    'scrollX' => true,
															    'showFooter' => true,
															    'showFooter' => 'bottom',
															    'columns' => [
															        'cbg' => [
															            'label' => 'CBG',
															            'type' => 'string',
															        ],
															        'KSR' => [
															            'label' => 'Kasir',
															            'type' => 'string',
															        ],
															        'SHIFT' => [
															            'label' => 'Shift',
															            'type' => 'string',
															        ],
															        'tgl' => [
															            'label' => 'Tanggal',
															            'type' => 'date',
															            'format' => 'd/m/Y',
															        ],
															        'SUB' => [
															            'label' => 'Sub',
															            'type' => 'string',
															        ],
															        'KD_BRG' => [
															            'label' => 'Kode Barang',
															            'type' => 'string',
															        ],
															        'NA_BRG' => [
															            'label' => 'Nama Barang',
															            'type' => 'string',
															        ],
															        'qty' => [
															            'label' => 'Qty',
															            'type' => 'number',
															            'decimals' => 0,
															        ],
															        'harga' => [
															            'label' => 'Harga Jual',
															            'type' => 'number',
															            'decimals' => 0,
															            'prefix' => 'Rp ',
															        ],
															        'ppn' => [
															            'label' => 'PPN',
															            'type' => 'string',
															        ],
															        'PPN_KET' => [
															            'label' => 'Keterangan PPN',
															            'type' => 'string',
															        ],
															        'nppn' => [
															            'label' => 'Nilai PPN',
															            'type' => 'number',
															            'decimals' => 0,
															            'prefix' => 'Rp ',
															        ],
															        'dpp' => [
															            'label' => 'DPP',
															            'type' => 'number',
															            'decimals' => 0,
															            'prefix' => 'Rp ',
															        ],
															        'tkp' => [
															            'label' => 'TKP',
															            'type' => 'number',
															            'decimals' => 0,
															            'prefix' => 'Rp ',
															        ],
															        'total' => [
															            'label' => 'Total',
															            'type' => 'number',
															            'decimals' => 0,
															            'prefix' => 'Rp ',
															            'footer' => 'sum',
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
															                'targets' => [7, 8, 10, 11, 12, 13], // qty, harga, nppn, dpp, tkp, total
															            ],
															            [
															                'className' => 'dt-center',
															                'targets' => [0, 1, 2, 3, 4, 9], // cbg, ksr, shift, tgl, sub, ppn
															            ],
															        ],
															        'order' => [[0, 'asc'], [1, 'asc'], [2, 'asc']], // Order by CBG, KSR, SHIFT
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
															                        'title' => 'Laporan Penjualan Sales SPM - ' . date('d/m/Y'),
															                    ],
															                    [
															                        'extend' => 'excel',
															                        'title' => 'Laporan Penjualan Sales SPM - ' . date('d/m/Y'),
															                    ],
															                    [
															                        'extend' => 'csv',
															                        'title' => 'Laporan Penjualan Sales SPM - ' . date('d/m/Y'),
															                    ],
															                    [
															                        'extend' => 'pdf',
															                        'title' => 'Laporan Penjualan Sales SPM - ' . date('d/m/Y'),
															                        'orientation' => 'landscape',
															                        'pageSize' => 'A4',
															                    ],
															                    [
															                        'extend' => 'print',
															                        'title' => 'Laporan Penjualan Sales SPM - ' . date('d/m/Y'),
															                    ],
															                ],
															            ],
															        ],
															        'language' => [
															            'url' => '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json',
															        ],
															    ],
															]);
															?>
														@elseif(isset($hasil))
															<div class="alert alert-info text-center">
																<i class="fas fa-info-circle"></i>
																Tidak ada data yang ditemukan. Silakan ubah filter dan coba lagi.
															</div>
														@else
															<div class="alert alert-secondary text-center">
																<i class="fas fa-search"></i>
																Silakan pilih filter dan klik tombol "Filter" untuk menampilkan data.
															</div>
														@endif
													</div>
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

@section('javascripts')
	<script>
		// AJAX function to load data
		function loadData() {
			const form = document.getElementById('reportForm');
			const formData = new FormData(form);

			// Validate required fields
			const ksr = document.getElementById('ksr').value;
			const tgl1 = document.getElementById('tgl1').value;

			if (!ksr) {
				alert('Silakan pilih Kasir terlebih dahulu');
				document.getElementById('ksr').focus();
				return;
			}

			if (!tgl1) {
				alert('Silakan pilih Tanggal terlebih dahulu');
				document.getElementById('tgl1').focus();
				return;
			}

			// Show loading indicator
			document.getElementById('loadingIndicator').style.display = 'block';
			document.getElementById('reportContent').style.display = 'none';

			// Prepare data for AJAX
			const data = {
				ksr: ksr,
				tgl1: tgl1,
				bulan: document.getElementById('bulan').value,
				cbg: document.getElementById('cbg').value,
				_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
			};

			// Make AJAX request
			fetch('{{ route('get-tsalesspm-report') }}', {
					method: 'GET',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': data._token,
						'X-Requested-With': 'XMLHttpRequest'
					},
					body: JSON.stringify(data)
				})
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok');
					}
					return response.json();
				})
				.then(data => {
					document.getElementById('loadingIndicator').style.display = 'none';

					if (data.success) {
						// Reload page with new data
						const url = new URL(window.location);
						url.searchParams.set('ksr', ksr);
						url.searchParams.set('tgl1', tgl1);
						url.searchParams.set('bulan', document.getElementById('bulan').value);
						url.searchParams.set('cbg', document.getElementById('cbg').value);
						url.searchParams.set('filter', '1');

						window.location.href = url.toString();
					} else {
						document.getElementById('reportContent').style.display = 'block';
						document.getElementById('reportContent').innerHTML =
							'<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' +
							(data.message || 'Terjadi kesalahan saat memuat data') + '</div>';
					}
				})
				.catch(error => {
					console.error('Error:', error);
					document.getElementById('loadingIndicator').style.display = 'none';
					document.getElementById('reportContent').style.display = 'block';
					document.getElementById('reportContent').innerHTML =
						'<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan saat memuat data. Silakan coba lagi.</div>';
				});
		}

		// Tab functionality
		$(document).ready(function() {
			// Initialize Bootstrap tabs
			$('#reportTabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');
			});

			// Save active tab to localStorage
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				localStorage.setItem('activeTab', $(e.target).attr('href'));
			});

			// Restore active tab from localStorage
			var activeTab = localStorage.getItem('activeTab');
			if (activeTab) {
				$('#reportTabs a[href="' + activeTab + '"]').tab('show');
			}

			// Auto-resize table on tab change
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				$.fn.dataTable.tables({
					visible: true,
					api: true
				}).columns.adjust();
			});

			// Auto load data if filter parameters exist
			@if (request()->has('filter'))
				// Data already loaded from server
			@endif
		});

		// Export functions
		function exportData(format) {
			// Check if data table exists
			if (typeof window.dataTable !== 'undefined') {
				switch (format) {
					case 'excel':
						window.dataTable.button('.buttons-excel').trigger();
						break;
					case 'pdf':
						window.dataTable.button('.buttons-pdf').trigger();
						break;
					case 'csv':
						window.dataTable.button('.buttons-csv').trigger();
						break;
					case 'print':
						window.dataTable.button('.buttons-print').trigger();
						break;
					default:
						alert('Format export tidak dikenali');
				}
			} else {
				alert('Tidak ada data untuk di-export. Silakan filter data terlebih dahulu.');
			}
		}

		// Form validation for print
		document.getElementById('reportForm').addEventListener('submit', function(e) {
			const ksr = document.getElementById('ksr').value;
			const tgl1 = document.getElementById('tgl1').value;

			if (!ksr || !tgl1) {
				e.preventDefault();
				alert('Silakan lengkapi filter Kasir dan Tanggal untuk mencetak laporan');
				return false;
			}
		});

		// Enter key support for filter
		document.addEventListener('keypress', function(e) {
			if (e.key === 'Enter' && (e.target.id === 'ksr' || e.target.id === 'tgl1' || e.target.id === 'bulan' || e.target.id === 'cbg')) {
				e.preventDefault();
				loadData();
			}
		});

		// Initialize select2 for better dropdown experience (if available)
		$(document).ready(function() {
			if ($.fn.select2) {
				$('#ksr, #cbg, #bulan').select2({
					theme: 'bootstrap4',
					width: 'resolve'
				});
			}
		});
	</script>
@endsection
