@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Barang SPM</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Barang SPM</li>
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
								<form method="POST" action="{{ url('jasper-barangspm-report') }}" id="reportForm">
									@csrf

					                <div class="form-group row">
						                <div class="col-3">

                                            <button class="btn btn-primary" type="submit" name="filter">Data
                                                Barang</button>

                                            <button class="btn btn-primary" name="bintang" value="bintang" id="bintang">Bintang
                                                DCK</button>
					                    </div>

					                </div>

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="periode-tab" data-toggle="tab" href="#periode" role="tab" aria-controls="periode" aria-selected="true">
												<i class="fas fa-calendar mr-1"></i>Periode
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="mutasi-tab" data-toggle="tab" href="#mutasi" role="tab" aria-controls="mutasi" aria-selected="false">
												<i class="fas fa-exchange-alt mr-1"></i>Mutasi
											</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Periode Tab -->
										<div class="tab-pane fade show active" id="periode" role="tabpanel" aria-labelledby="periode-tab">
											<div class="pt-3">
												<div class="form-group">
													<!-- Search Filter Row -->
													<div class="row align-items-end mb-3">
														<div class="col-2">
															<label>Filter Pencarian</label>
															<select name="filter_type" id="filter_type" class="form-control">
																<option value="supp" {{ session()->get('filter_type') == 'supp' ? 'selected' : '' }}>Supp</option>
																<option value="sub" {{ session()->get('filter_type') == 'sub' ? 'selected' : '' }}>Sub</option>
																<option value="kd_brg" {{ session()->get('filter_type') == 'kd_brg' ? 'selected' : '' }}>Sub Item</option>
																<option value="barcode" {{ session()->get('filter_type') == 'barcode' ? 'selected' : '' }}>Barcode</option>
																<option value="na_brg" {{ session()->get('filter_type') == 'na_brg' ? 'selected' : '' }}>Nama Barang</option>
															</select>
														</div>
														<div class="col-3">
															<label for="filter_value">Nilai Filter</label>
															<input type="text" name="filter_value" id="filter_value" class="form-control" value="{{ session()->get('filter_value') }}"
																placeholder="Masukkan nilai pencarian dan tekan Enter..." onkeypress="if(event.keyCode==13) filterBarang()">
														</div>
														<div class="col-2">
															<label for="cbg_periode">Cabang</label>
															<select name="cbg" id="cbg_periode" class="form-control" required>
																<option value="">Pilih Cabang</option>
																@foreach ($cbg as $cabang)
																	<option value="{{ $cabang->CBG }}" {{ session()->get('filter_cbg') == $cabang->CBG ? 'selected' : '' }}>
																		{{ $cabang->CBG }}
																	</option>
																@endforeach
															</select>
														</div>
														<div class="col-2">
															<label for="periode_filter">Periode</label>
															<input type="text" name="periode_filter" id="periode_filter" class="form-control" placeholder="MM-YYYY" value="{{ date('m-Y') }}">
														</div>
														<div class="col-3">
															<button class="btn btn-primary mr-1" type="button" id="btnFilterPeriode" onclick="filterBarang()">
																<i class="fas fa-search mr-1"></i>Filter
															</button>
															<button class="btn btn-danger mr-1" type="button" onclick="resetFilter()">
																<i class="fas fa-redo mr-1"></i>Reset
															</button>
															<button class="btn btn-warning mr-1" type="submit" name="cetak_barang" formtarget="_blank">
																<i class="fas fa-print mr-1"></i>Cetak
															</button>
															<button class="btn btn-success" type="button" onclick="exportData('excel')">
																<i class="fas fa-file-excel mr-1"></i>Export Excel
															</button>
														</div>
													</div>

													<!-- Data Table -->
													<div class="report-content col-md-12" style="max-width: 100%; overflow-x: scroll;">
														<?php
														use koolreport\datagrid\DataTables as KoolDataTables;

														if ($hasilBarang) {
														    KoolDataTables::create([
														        'dataSource' => $hasilBarang,
														        'name' => 'tabelPeriode',
														        'fastRender' => true,
														        'fixedHeader' => true,
														        'scrollX' => true,
														        'showFooter' => true,
														        'columns' => [
														            'KD_BRG' => ['label' => 'Kode Barang'],
														            'sub' => ['label' => 'Sub'],
														            'kdbar' => ['label' => 'Item'],
														            'NA_BRG' => ['label' => 'Nama Barang'],
														            'KET_UK' => ['label' => 'Ukuran'],
														            'KET_KEM' => ['label' => 'Kemasan'],
														            'supp' => ['label' => 'Supplier'],
														            'sp_l' => ['label' => 'Langsung'],
														            'kirim_ke' => ['label' => 'Kirim ke'],
														            'SRMIN' => [
														                'label' => 'SRMIN',
														                'type' => 'number',
														                'decimals' => 0,
														                'thousandSeparator' => ',',
														            ],
														            'SRMAX' => [
														                'label' => 'SRMAX',
														                'type' => 'number',
														                'decimals' => 0,
														                'thousandSeparator' => ',',
														            ],
														            'lph' => [
														                'label' => 'LH',
														                'type' => 'number',
														                'decimals' => 2,
														                'thousandSeparator' => ',',
														            ],
														            'KLK' => ['label' => 'KLK'],
														            'KDLAKU' => ['label' => 'Jenis Barang'],
														            'stok' => [
														                'label' => 'Stok',
														                'type' => 'number',
														                'decimals' => 0,
														                'thousandSeparator' => ',',
														            ],
																	'stockt' => [
														                'label' => 'Stock',
														                'type' => 'number',
														                'decimals' => 0,
														                'thousandSeparator' => ',',
														            ],
														            'stockt' => [
														                'label' => 'Stock Rak',
														                'type' => 'number',
														                'decimals' => 0,
														                'thousandSeparator' => ',',
														            ],
														            'stockg' => [
														                'label' => 'Stock GD.Trans',
														                'type' => 'number',
														                'decimals' => 0,
														                'thousandSeparator' => ',',
														            ],
														            'stockr' => [
														                'label' => 'Stock Retur',
														                'type' => 'number',
														                'decimals' => 0,
														                'thousandSeparator' => ',',
														            ],
														            'HB' => [
														                'label' => 'Harga Beli',
														                'type' => 'number',
														                'decimals' => 2,
														                'thousandSeparator' => ',',
														            ],
														            'hj' => [
														                'label' => 'Harga Jual',
														                'type' => 'number',
														                'decimals' => 2,
														                'thousandSeparator' => ',',
														            ],
														            'statpsn' => ['label' => 'Pesanan'],
														            'DTB' => ['label' => 'Pesan Terakhir'],
														            'tdod' => ['label' => 'TPJ'], // Assuming same as hj
														            'lambat' => [
														                'label' => 'Lambat',
														                'type' => 'number',
														                'decimals' => 0,
														            ],
														            'Barcode' => ['label' => 'Barcode'],
														            'TARIK' => ['label' => 'Tarik'],
														            'MASA_EXP' => ['label' => 'DTB / Masa EXP'],
														            'RETUR' => ['label' => 'Retur'],
														            'KK' => ['label' => 'KK'],
														            'ON_DC' => ['label' => 'Tanpa DC'],
														            'DTR' => ['label' => 'DTR'],
														            'DTR2' => ['label' => 'DTR2'],
														            'DTR_MANUAL' => ['label' => 'DTR Khusus'],
														        ],
														        'cssClass' => [
														            'table' => 'table table-hover table-striped table-bordered compact',
														            'th' => 'label-title',
														            'td' => 'detail',
														        ],
														        'options' => [
														            'columnDefs' => [['className' => 'dt-right', 'targets' => [9, 10, 11, 13, 14, 15, 16, 17, 18, 21]]],
														            'order' => [],
														            'paging' => true,
														            'pageLength' => 25,
														            'searching' => true,
														            'colReorder' => true,
														            'select' => true,
														            'dom' => 'Blfrtip',
														            'buttons' => [['extend' => 'collection', 'text' => 'Export', 'buttons' => ['copy', 'excel', 'csv', 'pdf', 'print']]],
														        ],
														    ]);
														}
														?>
													</div>
												</div>
											</div>
										</div>

										<!-- Mutasi Tab -->
										<div class="tab-pane fade" id="mutasi" role="tabpanel" aria-labelledby="mutasi-tab">
											<div class="pt-3">
												<div class="form-group">
													<div class="row align-items-end mb-3">
														<div class="col-2">
															<label for="cbg_mutasi">Cabang</label>
															<select name="cbg_mutasi" id="cbg_mutasi" class="form-control" required>
																<option value="">Pilih Cabang</option>
																@foreach ($cbg as $cabang)
																	<option value="{{ $cabang->CBG }}">{{ $cabang->CBG }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-2">
															<label for="periode_mutasi">Periode</label>
															<input type="text" name="periode_mutasi" id="periode_mutasi" class="form-control" placeholder="MM-YYYY"
																value="{{ date('m-Y') }}" required>
														</div>
														<div class="col-2">
															<label>Jenis Stock</label>
															<div class="form-check">
																<input class="form-check-input" type="radio" name="jenis_stock" id="stock_toko_mutasi" value="toko" checked>
																<label class="form-check-label" for="stock_toko_mutasi">Toko</label>
															</div>
															<div class="form-check">
																<input class="form-check-input" type="radio" name="jenis_stock" id="stock_gudang_mutasi" value="gudang">
																<label class="form-check-label" for="stock_gudang_mutasi">GD.Transit</label>
															</div>
															<div class="form-check">
																<input class="form-check-input" type="radio" name="jenis_stock" id="stock_retur_mutasi" value="retur">
																<label class="form-check-label" for="stock_retur_mutasi">Retur</label>
															</div>
														</div>
														<div class="col-2">
															<label for="sub_item">Sub Item</label>
															<input type="text" name="sub_item" id="sub_item" class="form-control" placeholder="Sub Item">
														</div>
														<div class="col-2">
															<label for="kd_brg_mutasi">Kode Barang</label>
															<input type="text" name="kd_brg_mutasi" id="kd_brg_mutasi" class="form-control" placeholder="Kode Barang" required>
														</div>
														<div class="col-2">
															<button class="btn btn-primary mr-1" type="button" onclick="getKartuStock()">
																<i class="fas fa-search mr-1"></i>Proses
															</button>
															<button class="btn btn-warning" type="button" onclick="cetakKartuStock()">
																<i class="fas fa-print mr-1"></i>Cetak
															</button>
														</div>
													</div>

													<div class="report-content col-md-12" id="mutasi-result">
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															Silakan pilih cabang, periode, jenis stock, dan kode barang untuk menampilkan kartu mutasi.
														</div>
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

			// Auto-format periode input
			$('#periode_filter, #periode_mutasi').on('input', function() {
				var value = this.value.replace(/\D/g, ''); // Remove non-digits
				if (value.length >= 2) {
					this.value = value.substring(0, 2) + '-' + value.substring(2, 6);
				}
			});
		});

		// Filter Barang Function for Periode Tab
		function filterBarang() {
			var cbg = $('#cbg_periode').val();
			var filterType = $('#filter_type').val();
			var filterValue = $('#filter_value').val();
			var periode = $('#periode_filter').val();

			if (!cbg) {
				alert('Pilih cabang terlebih dahulu');
				return;
			}

			if (!filterValue) {
				alert('Masukkan nilai pencarian');
				return;
			}

			// Show loading
			$('#btnFilterPeriode').html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
			$('#btnFilterPeriode').prop('disabled', true);

			// Redirect to get report
			window.location.href = '{{ route('get-barangspm-report') }}?' +
				'cbg=' + encodeURIComponent(cbg) +
				'&filter_type=' + encodeURIComponent(filterType) +
				'&filter_value=' + encodeURIComponent(filterValue) +
				'&periode=' + encodeURIComponent(periode);
		}

		// Reset Filter Function
		function resetFilter() {
			$('#cbg_periode').val('');
			$('#filter_type').val('supp');
			$('#filter_value').val('');
			$('#periode_filter').val('{{ date('m-Y') }}');
			window.location.href = '{{ route('rbarangspm') }}';
		}

		// Get Kartu Stock Function for Mutasi Tab
		function getKartuStock() {
			var cbg = $('#cbg_mutasi').val();
			var periode = $('#periode_mutasi').val();
			var kdBrg = $('#kd_brg_mutasi').val();
			var subItem = $('#sub_item').val();
			var jenis = $('input[name="jenis_stock"]:checked').val();

			if (!cbg || !periode || !kdBrg) {
				alert('Cabang, periode, dan kode barang harus diisi');
				return;
			}

			$.ajax({
				url: '{{ route('get-barangspm-report') }}',
				method: 'GET',
				data: {
					cbg: cbg,
					periode: periode,
					kd_brg: kdBrg,
					sub_item: subItem,
					jenis: jenis
				},
				beforeSend: function() {
					$('#mutasi-result').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
				},
				success: function(response) {
					if (response.length > 0) {
						var html =
							'<div class="table-responsive"><table class="table table-striped table-bordered" id="mutasi-table"><thead><tr>';
						html +=
							'<th>Kode</th><th>Nama</th><th>Tanggal</th><th>Faktur</th><th>Awal</th><th>Masuk</th><th>Keluar</th><th>Saldo</th><th>Lain</th>';
						html += '</tr></thead><tbody>';

						$.each(response, function(i, item) {
							html += '<tr>';
							html += '<td>' + (item.kd_brg || '') + '</td>';
							html += '<td>' + (item.NA_BRG || '') + '</td>';
							html += '<td>' + (item.tgl || '') + '</td>';
							html += '<td>' + (item.no_bukti || '') + '</td>';
							html += '<td class="text-right">' + formatNumber(item.AWAL || 0) + '</td>';
							html += '<td class="text-right">' + formatNumber(item.MASUK || 0) + '</td>';
							html += '<td class="text-right">' + formatNumber(item.KELUAR || 0) + '</td>';
							html += '<td class="text-right"><strong>' + formatNumber(item.SALDO || 0) + '</strong></td>';
							html += '<td class="text-right">' + formatNumber(item.LAIN || 0) + '</td>';
							html += '</tr>';
						});

						html += '</tbody></table></div>';
						$('#mutasi-result').html(html);

						// Initialize DataTable
						$('#mutasi-table').DataTable({
							pageLength: 25,
							searching: true,
							ordering: true,
							responsive: true,
							dom: 'Blfrtip',
							buttons: ['copy', 'excel', 'csv', 'pdf', 'print']
						});
					} else {
						$('#mutasi-result').html(
							'<div class="alert alert-warning">Tidak ada data kartu mutasi untuk parameter yang dipilih</div>');
					}
				},
				error: function() {
					$('#mutasi-result').html('<div class="alert alert-danger">Error loading data</div>');
				}
			});
		}

		// Export functions for different formats
		function exportData(format) {
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

		// Print functions
		function cetakKartuStock() {
			var cbg = $('#cbg_mutasi').val();
			var periode = $('#periode_mutasi').val();
			var kdBrg = $('#kd_brg_mutasi').val();
			var jenis = $('input[name="jenis_stock"]:checked').val();

			if (!cbg || !periode || !kdBrg) {
				alert('Semua field harus diisi untuk cetak kartu');
				return;
			}

			// Open in new window for printing
			var url = '{{ url('jasper-barangspm-report') }}?cetak_kartu=1&cbg=' + cbg + '&periode=' + periode + '&kd_brg=' + kdBrg + '&jenis=' + jenis;
			window.open(url, '_blank');
		}

		// Helper function to format numbers
		function formatNumber(num) {
			return Number(num).toLocaleString('id-ID');
		}

		// Form validation
		$('#reportForm').on('submit', function(e) {
			var activeTab = $('.nav-link.active').attr('href');

			if (activeTab === '#periode') {
				var cbg = $('#cbg_periode').val();
				var filterValue = $('#filter_value').val();

				if (!cbg || !filterValue) {
					e.preventDefault();
					alert('Cabang dan nilai filter harus diisi');
				}
			}
		});

		// Enter key handler for filter input
		$('#filter_value').on('keypress', function(e) {
			if (e.which === 13) { // Enter key
				filterBarang();
			}
		});


        //////////////////////////////////////////////


        var dTableBData;

        function loadDataBData() {
            $.ajax({
                type: 'GET',
                url: "{{ url('brg/browse_dck') }}", // Pastikan endpoint ini mengembalikan data JSON
                data: {
                    'CBG': $('#CBG').val()
                },
                success: function(response) {
                    if (dTableBData) {
                        dTableBData.clear();
                    }

                    response.forEach(item => {
                        dTableBData.row.add([
                            item.kdbar || '',
                            item.NA_BRG || '',
                            item.sub || '',
                            item.supp || '',
                            item.TARIK || '',
                            item.MASA_EXP || '',
                            item.KET_UK || '',
                            item.KET_KEM || '',
                            item.SRMIN || '',
                            item.SRMAX || ''
                        ]);
                    });

                    dTableBData.draw();
                },
                error: function(xhr) {
                    console.error("Gagal memuat data:", xhr.responseText);
                    alert("Gagal memuat data.");
                }
            });
        }

        function browseData() {
            loadDataBData();
            $("#dataModal").modal("show");
        }

        $(document).ready(function() {
            // Inisialisasi DataTable saat halaman siap
            dTableBData = $('#table-bdata').DataTable({
                paging: true,
                searching: true,
                info: true
            });

            // Shortcut: jika tombol delete ditekan di input #brg1, tampilkan modal
            // $("#brg1").keypress(function(e) {
            //     if (e.keyCode === 46) {
            //         e.preventDefault();
            //         browseData();
            //     }
            // });
        });
	</script>
@endsection
