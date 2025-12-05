@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Masa Tarik Kode 8</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Laporan Masa Tarik Kode 8</li>
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
								<form method="GET" action="{{ route('get-rcnorder8-report') }}" id="macetForm">
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

										<div class="col-md-2 mb-2" id="wrapper-nobukti">
											<label for="sub">Sub <span class="text-danger">*</span></label>
											<div class="input-group">
												<input type="text" name="sub" id="sub" class="form-control" placeholder="Masukkan SUB"
													value="{{ session()->get('filter_sub') }}" required>
											</div>
										</div>

										<div class="col-md-2 mb-2" id="wrapper-posting">
											<input type="checkbox" name="ulang" id="ulang" value="1" {{ session()->get('filter_ulang') == 1 ? 'checked' : '' }}>
											<label for="ulang">Ulang</label>
										</div>
									</div>

									<div class="row align-items-end mb-3">
										<div class="col-md-2 mb-2">
											<label for="nobukti">No. Rencana Order <span class="text-danger">*</span></label>
											<div class="input-group">
												<input type="text" name="nobukti" id="nobukti" class="form-control" placeholder="No. Rencana Order"
													value="{{ session()->get('filter_nobukti') }}">
											</div>
										</div>
										<div class="col-md-2 mb-2">
											<button class="btn btn-dark mr-1" type="submit" name="action" value="posting">
												<i class="fas fa-search mr-1"></i>Posting
											</button>
										</div>
									</div>

									<div class="row align-items-end mb-3">
										<div class="col-md-12 mb-2 text-right">
											<input type="hidden" name="action" value="filter">
											<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
												<i class="fas fa-search mr-1"></i>Proses
											</button>
											<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
												<i class="fas fa-undo mr-1"></i>Reset
											</button>
										</div>
									</div>

									<!-- Active Filter Display -->
									@if (session()->get('filter_ulang') == 0)
										@if (session()->get('filter_cbg') && session()->get('filter_sub') && session()->get('filter_ulang'))
											<div class="row mb-3">
												<div class="col-12">
													<div class="alert alert-info">
														<strong>Filter Aktif:</strong>
														Cabang: {{ session()->get('filter_cbg') }} |
														SUB: {{ session()->get('filter_sub') }} |
														{{ session()->get('filter_ulang') == 1 ? 'Bukan Tampil Ulang' : 'Tampil Ulang' }}
													</div>
												</div>
											</div>
										@endif
									@elseif (session()->get('filter_ulang') == 1)
										@if (session()->get('filter_nobukti') && session()->get('filter_ulang'))
											<div class="row mb-3">
												<div class="col-12">
													<div class="alert alert-info">
														<strong>Filter Aktif:</strong>
														No. Rencana Order: {{ session()->get('filter_nobukti') }} |
														{{ session()->get('filter_ulang') == 1 ? 'Bukan Tampil Ulang' : 'Tampil Ulang' }}
													</div>
												</div>	
											</div>
										@endif
									@endif
								</form>
								
								<!-- Data Table Section -->
								<div class="report-content">
									@if ($rcnorder8 && count($rcnorder8) > 0)
										<?php
										\koolreport\datagrid\DataTables::create([
										    'dataSource' => $rcnorder8,
										    'name' => 'barangMacetTable',
										    'fastRender' => true,
										    'fixedHeader' => true,
										    // 'scrollX' => true,
										    'showFooter' => false,
										    'columns' => [
												'NAMAFILE' => [
										            'label' => 'Nama File',
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
												'SUPP' => [
										            'label' => 'Barcode',
										        ],
												'STOK' => [
										            'label' => 'Stok',
													'type' => 'number',
													'decimals' => 0,
										        ],
												'TARIK' => [
										            'label' => 'Tarik',
													'type' => 'number',
													'decimals' => 0,
												],
												'TGL_TRM' => [
										            'label' => 'Tgl. Beli AKhir',
										        ],
												'TGL_PRODUKSI' => [
										            'label' => 'Tgl. Produksi',
										        ],
												'TGL_MASA_TARIK' => [
										            'label' => 'Tgl. Masa Tarik',
										        ],
												'TGL_KSR' => [
										            'label' => 'Tgl. Jual Akhir',
										        ],
												'TG_POST' => [
										            'label' => 'Tgl. Posting',
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
										                'targets' => [6,7], // numeric columns
										            ],
										            [
										                'className' => 'dt-center',
										                'targets' => [0,1,2,3,4,5,8,9,10,11,12], // center aligned columns
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
											Tidak ada data Rencana Order Kode 8.
										</div>
									@else
										<div class="alert alert-info text-center">
											<i class="fas fa-info-circle mr-2"></i>
											Silakan Masukkan Cabang dan Sub untuk menampilkan data Rencana Order Kode 8.
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

			function toggleUlangFields() {
				var ulangChecked = $('#ulang').is(':checked');

				// Jika ulang dicentang -> show nobukti & posting button
				if (ulangChecked) {
					$('#nobukti').closest('.col-md-2').show();
					$('button[name="action"][value="posting"]').closest('.col-md-2').show();
				} else {
					// Jika tidak dicentang -> hide
					$('#nobukti').closest('.col-md-2').hide();
					$('button[name="action"][value="posting"]').closest('.col-md-2').hide();
				}
			}

			// Saat halaman load
			toggleUlangFields();

			// Saat checkbox berubah
			$('#ulang').on('change', function() {
				toggleUlangFields();
			});

			// Auto-resize table on window resize
			$(window).on('resize', function() {
				if ($.fn.DataTable.isDataTable('#barangMacetTable')) {
					$('#barangMacetTable').DataTable().columns.adjust().responsive.recalc();
				}
			});

			let clickedAction = null;

			// deteksi tombol mana yang diklik
			$('button[name="action"]').on('click', function() {
				clickedAction = $(this).val();
			});

			$('#macetForm').on('submit', function(e) {
				var cbg = $('#cbg').val();
				var sub = $('#sub').val();
				var ulang = $('#ulang').is(':checked');
				var nobukti = $('#nobukti').val();

				if (!cbg) { alert('Harap pilih Cabang terlebih dahulu'); e.preventDefault(); return false; }
				if (!sub) { alert('Harap pilih Sub terlebih dahulu'); e.preventDefault(); return false; }

				if (ulang && !nobukti) { 
					alert('Harap pilih Bukti terlebih dahulu'); 
					e.preventDefault(); 
					return false; 
				}

				// hanya disable dan ubah text kalau tombol Proses yang ditekan
				if (clickedAction === 'filter') {
					$('button[name="action"][value="filter"]')
						.html('<i class="fas fa-spinner fa-spin mr-1"></i>Processing...')
						.prop('disabled', true);
				}
			});

			// Enter key handling
			$('#sub').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					$('#macetForm').find('button[name="action"][value="filter"]').click();
				}
			});
		});

		// Reset form function
		function resetForm() {
			window.location.href = '{{ route('rrcnorder8') }}';
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
