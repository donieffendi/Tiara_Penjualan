@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Pemantauan Barang</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Pemantauan Barang</li>
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
								<form method="GET" action="{{ route('get-pemantauanbarang-report') }}" id="reportForm">
									@csrf

									<!-- Filter Controls -->
									<div class="row align-items-end mb-4">
										<div class="col-3">
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
										<div class="col-3">
											<label for="hari">Filter Hari</label>
											<input type="number" name="hari" id="hari" class="form-control" value="{{ session()->get('filter_hari') }}"
												placeholder="Masukkan jumlah hari" min="1" required>
										</div>
										<div class="col-6">
											<button class="btn btn-primary mr-2" type="submit" id="btnFilter">
												<i class="fas fa-search mr-1"></i>Filter
											</button>
											<button class="btn btn-danger mr-2" type="button" onclick="resetFilter()">
												<i class="fas fa-redo mr-1"></i>Reset
											</button>
											<button class="btn btn-success mr-2" type="button" onclick="exportAllData('excel')">
												<i class="fas fa-file-excel mr-1"></i>Export Excel
											</button>
											<!-- <div class="btn-group" role="group">
												<button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
													<i class="fas fa-print mr-1"></i>Cetak
												</button>
												<div class="dropdown-menu">
													<a class="dropdown-item" href="#" onclick="cetakLaporan('MACET')">Cetak Barang Macet</a>
													<a class="dropdown-item" href="#" onclick="cetakLaporan('SM')">Cetak Stok Maksimal</a>
													<a class="dropdown-item" href="#" onclick="cetakLaporan('LK')">Cetak Lama Kosong</a>
													<div class="dropdown-divider"></div>
													<a class="dropdown-item" href="#" onclick="cetakLaporan('ALL')">Cetak Semua</a>
												</div>
											</div> -->
										</div>
									</div>

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="pemantauanTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="macet-tab" data-toggle="tab" href="#macet" role="tab" aria-controls="macet" aria-selected="true">
												<i class="fas fa-exclamation-triangle text-danger mr-1"></i>Barang Macet
												@if (count($hasilBarangMacet) > 0)
													<span class="badge badge-danger ml-1">{{ count($hasilBarangMacet) }}</span>
												@endif
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="slow-moving-tab" data-toggle="tab" href="#slow-moving" role="tab" aria-controls="slow-moving"
												aria-selected="false">
												<i class="fas fa-clock text-warning mr-1"></i>Stok Maksimal
												@if (count($hasilBarangSlowMoving) > 0)
													<span class="badge badge-warning ml-1">{{ count($hasilBarangSlowMoving) }}</span>
												@endif
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="lama-kosong-tab" data-toggle="tab" href="#lama-kosong" role="tab" aria-controls="lama-kosong"
												aria-selected="false">
												<i class="fas fa-archive text-info mr-1"></i>Lama Kosong
												@if (count($hasilBarangLamaKosong) > 0)
													<span class="badge badge-info ml-1">{{ count($hasilBarangLamaKosong) }}</span>
												@endif
											</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="pemantauanTabContent">
										<!-- Barang Macet Tab -->
										<div class="tab-pane fade show active" id="macet" role="tabpanel" aria-labelledby="macet-tab">
											<div class="pt-3">
												<div class="report-content">
													@if (count($hasilBarangMacet) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="macet-table">
																<thead>
																	<tr>
																		<th>Sub Item</th>
																		<th>Nama Barang</th>
																		<th>Ukuran</th>
																		<th>Kemasan</th>
																		<th>Supplier</th>
																		<th>KD</th>
																		<th>KLK</th>
																		<th>Stok Toko</th>
																		<th>Stok GD</th>
																		<th>TD OD</th>
																		<th>Qty Beli</th>
																		<th>Tgl Beli</th>
																		<th>Tgl Kasir</th>
																		{{-- <th>Selisih Hari</th>
																		<th>Nilai Stock</th> --}}
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilBarangMacet as $item)
																		<tr>
																			<td>{{ $item['KD_BRG'] }}</td>
																			<td>{{ $item['NA_BRG'] }}</td>
																			<td>{{ $item['KET_UK'] }}</td>
																			<td>{{ $item['KET_KEM'] }}</td>
																			<td>{{ $item['SUPP'] }}</td>
																			<td>{{ $item['KDLAKU'] }}</td>
																			<td>{{ $item['KLK'] }}</td>
																			<td class="text-right STOCKT">{{ number_format($item['STOCKT'], 0, ',', '.') }}</td>
																			<td class="text-right STOCKG">{{ number_format($item['STOCKG'], 0, ',', '.') }}</td>
																			<td>{{ $item['TDX'] }}</td>
																			<td class="text-right">{{ number_format($item['QTY_TRM'], 0, ',', '.') }}</td>
																			<td>{{ $item['TRM'] ? date('d/m/Y', strtotime($item['TRM'])) : '-' }}</td>
																			<td>{{ $item['KSR'] ? date('d/m/Y', strtotime($item['KSR'])) : '-' }}</td>
																			{{-- <td class="text-center">{{ $item['SELISIH_HARI'] }}</td>
																			<td class="text-right">{{ number_format($item['NILAI_STOCK'], 0, ',', '.') }}</td> --}}
																		</tr>
																	@endforeach
																</tbody>

																<!-- FOOTER TOTAL -->
																<tfoot>
																	<tr>
																		<th colspan="7" class="text-right">TOTAL :</th>
																		<th class="text-right" id="totalStockTMacet"></th> <!-- total stok toko -->
																		<th class="text-right" id="totalStockGMacet"></th> <!-- total stok GD -->
																		<th></th>
																		<th></th>
																		<th></th>
																		<th></th>
																	</tr>
																</tfoot>

															</table>

															

														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg') && session()->get('filter_hari'))
																Tidak ada data barang macet untuk cabang <strong>{{ session()->get('filter_cbg') }}</strong> dengan filter
																{{ session()->get('filter_hari') }} hari.
															@else
																Silakan pilih cabang dan masukkan filter hari untuk menampilkan data barang macet.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>

										<!-- Stok Maksimal Tab -->
										<div class="tab-pane fade" id="slow-moving" role="tabpanel" aria-labelledby="slow-moving-tab">
											<div class="pt-3">
												<div class="report-content">
													@if (count($hasilBarangSlowMoving) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="slow-moving-table">
																<thead>
																	<tr>
																		<th>Sub Item</th>
																		<th>Nama Barang</th>
																		<th>Ukuran</th>
																		<th>Supp</th>
																		<th>Kemasan</th>
																		<th>KD</th>
																		<th>KLK</th>
																		<th>Stok Toko</th>
																		<th>Stok GD</th>
																		<th>Stok Maks</th>
																		<th>Qty Beli</th>
																		<th>Tgl Beli</th>
																		<th>Tgl Kasir</th>
																		{{-- <th>Selisih Hari</th>
																		<th>Nilai Stock</th> --}}
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilBarangSlowMoving as $item)
																		<tr>
																			<td>{{ $item['KD_BRG'] }}</td>
																			<td>{{ $item['NA_BRG'] }}</td>
																			<td>{{ $item['KET_UK'] }}</td>
																			<td>{{ $item['SUPP'] }}</td>
																			<td>{{ $item['KET_KEM'] }}</td>
																			<td>{{ $item['KDLAKU'] }}</td>
																			<td>{{ $item['KLK'] }}</td>
																			<td class="text-right STOCKT">{{ number_format($item['STOCKT'], 0, ',', '.') }}</td>
																			<td class="text-right STOCKG">{{ number_format($item['STOCKG'], 0, ',', '.') }}</td>
																			<td class="text-right SRMAX">{{ number_format($item['SRMAX'], 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item['QTY_TRM'], 0, ',', '.') }}</td>
																			<td>{{ $item['TGL_TRM'] ? date('d/m/Y', strtotime($item['TGL_TRM'])) : '-' }}</td>
																			<td>{{ $item['TGL_AT'] ? date('d/m/Y', strtotime($item['TGL_AT'])) : '-' }}</td>
																			{{-- <td class="text-center">{{ $item['SELISIH_HARI'] }}</td>
																			<td class="text-right">{{ number_format($item['NILAI_STOCK'], 0, ',', '.') }}</td> --}}
																		</tr>
																	@endforeach
																</tbody>

																<!-- FOOTER TOTAL -->
																<tfoot>
																	<tr>
																		<th colspan="7" class="text-right">TOTAL :</th>
																		<th class="text-right" id="totalStockTSlow"></th> <!-- total stok toko -->
																		<th class="text-right" id="totalStockGSlow"></th> <!-- total stok GD -->
																		<th class="text-right" id="totalStockMSlow"></th> <!-- total stok Maks -->
																		<th></th>
																		<th></th>
																		<th></th>
																	</tr>
																</tfoot>

															</table>

															

														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg') && session()->get('filter_hari'))
																Tidak ada data barang Stok Maksimal untuk cabang <strong>{{ session()->get('filter_cbg') }}</strong> dengan filter
																{{ session()->get('filter_hari') }} hari.
															@else
																Silakan pilih cabang dan masukkan filter hari untuk menampilkan data barang Stok Maksimal.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>

										<!-- Lama Kosong Tab -->
										<div class="tab-pane fade" id="lama-kosong" role="tabpanel" aria-labelledby="lama-kosong-tab">
											<div class="pt-3">
												<div class="report-content">
													@if (count($hasilBarangLamaKosong) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="lama-kosong-table">
																<thead>
																	<tr>
																		<th>Sub Item</th>
																		<th>Nama Barang</th>
																		<th>Ukuran</th>
																		<th>Kemasan</th>
																		{{-- <th>Supplier</th> --}}
																		<th>KD</th>
																		<th>KLK</th>
																		<th>Stok Toko</th>
																		<th>Stok GD</th>
																		{{-- <th>TD OD</th> --}}
																		<th>Qty Beli</th>
																		<th>Tgl Beli</th>
																		<th>Tgl Kosong</th>
																		<th>Tgl Kasir</th>
																		{{-- <th>Selisih Hari</th>
																		<th>Nilai Stock</th> --}}
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilBarangLamaKosong as $item)
																		<tr>
																			<td>{{ $item['KD_BRG'] }}</td>
																			<td>{{ $item['NA_BRG'] }}</td>
																			<td>{{ $item['KET_UK'] }}</td>
																			<td>{{ $item['KET_KEM'] }}</td>
																			<!-- <td>{{ $item['SUPP'] }}</td> -->
																			<td>{{ $item['KDLAKU'] }}</td>
																			<td>{{ $item['KLK'] }}</td>
																			<td class="text-right STOCKT">{{ number_format($item['STOCKT'], 0, ',', '.') }}</td>
																			<td class="text-right STOCKG">{{ number_format($item['STOCKG'], 0, ',', '.') }}</td>
																			{{-- <td>{{ $item['TGL_TK'] ? date('d/m/Y', strtotime($item['TGL_TK'])) : '-' }}</td> --}}
																			<td class="text-right">{{ number_format($item['QTY_TRM'], 0, ',', '.') }}</td>
																			<td>{{ $item['TGL_TRM'] ? date('d/m/Y', strtotime($item['TGL_TRM'])) : '-' }}</td>
																			<td>{{ $item['TGL_BK'] ? date('d/m/Y', strtotime($item['TGL_BK'])) : '-' }}</td>
																			<td>{{ $item['TGL_AT'] ? date('d/m/Y', strtotime($item['TGL_AT'])) : '-' }}</td>
																			{{-- <td class="text-center">{{ $item['SELISIH_HARI'] }}</td>
																			<td class="text-right">{{ number_format($item['NILAI_STOCK'], 0, ',', '.') }}</td> --}}
																		</tr>
																	@endforeach
																</tbody>

																<!-- FOOTER TOTAL -->
																<tfoot>
																	<tr>
																		<th colspan="7" class="text-right">TOTAL :</th>
																		<th class="text-right" id="totalStockTLK"></th> <!-- total stok toko -->
																		<th class="text-right" id="totalStockGLK"></th> <!-- total stok GD -->
																		<th></th> 
																		<th></th>
																		<th></th>
																		<th></th>
																	</tr>
																</tfoot>

															</table>

															

														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg') && session()->get('filter_hari'))
																Tidak ada data barang lama kosong untuk cabang <strong>{{ session()->get('filter_cbg') }}</strong> dengan filter
																{{ session()->get('filter_hari') }} hari.
															@else
																Silakan pilih cabang dan masukkan filter hari untuk menampilkan data barang lama kosong.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>
									</div>
								</form>

								<!-- Summary Information -->
								@if (session()->get('filter_cbg') && session()->get('filter_hari'))
									<div class="row mt-4">
										<div class="col-12">
											<div class="card card-outline card-info">
												<div class="card-header">
													<h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Ringkasan Laporan</h3>
												</div>
												<div class="card-body">
													<div class="row">
														<div class="col-md-3">
															<div class="info-box bg-danger">
																<span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Barang Macet</span>
																	<span class="info-box-number">{{ count($hasilBarangMacet) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-warning">
																<span class="info-box-icon"><i class="fas fa-clock"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Stok Maksimal</span>
																	<span class="info-box-number">{{ count($hasilBarangSlowMoving) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-info">
																<span class="info-box-icon"><i class="fas fa-archive"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Lama Kosong</span>
																	<span class="info-box-number">{{ count($hasilBarangLamaKosong) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-success">
																<span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Total Items</span>
																	<span class="info-box-number">{{ count($hasilBarangMacet) + count($hasilBarangSlowMoving) + count($hasilBarangLamaKosong) }}</span>
																</div>
															</div>
														</div>
													</div>
													<div class="row mt-2">
														<div class="col-12">
															<small class="text-muted">
																<i class="fas fa-store mr-1"></i>Cabang: <strong>{{ session()->get('filter_cbg') }}</strong> |
																<i class="fas fa-calendar mr-1"></i>Filter: <strong>{{ session()->get('filter_hari') }} hari</strong> |
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

	<script>

		$(document).ready(function() {
			// Initialize Bootstrap tabs
			$('#pemantauanTabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');
			});

			// Save active tab to localStorage
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				localStorage.setItem('pemantauanActiveTab', $(e.target).attr('href'));
			});

			// Restore active tab from localStorage
			var activeTab = localStorage.getItem('pemantauanActiveTab');
			if (activeTab) {
				$('#pemantauanTabs a[href="' + activeTab + '"]').tab('show');
			}

			// Initialize DataTables for each tab
			initializeDataTables();

			// Auto-resize table on tab change
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				$.fn.dataTable.tables({
					visible: true,
					api: true
				}).columns.adjust();
			});

			// Form validation
			$('#reportForm').on('submit', function(e) {
				var cbg = $('#cbg').val();
				var hari = $('#hari').val();

				if (!cbg || !hari || hari <= 0) {
					e.preventDefault();
					alert('Cabang dan filter hari harus diisi dengan nilai yang valid');
					return false;
				}

				// Show loading
				$('#btnFilter').html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
				$('#btnFilter').prop('disabled', true);
			});
		
			// Barang Macet → total stok toko (7), stok GD (8), qty beli (10)
			initTableWithFooter("macet-table", [7, 8, 10]);

			// Stok Maksimal → total stok toko (7), stok GD (8), stok max (9), qty beli (10)
			initTableWithFooter("slow-moving-table", [7, 8, 9, 10]);

			// Lama Kosong → total stok toko (6), stok GD (7), qty beli (8)
			initTableWithFooter("lama-kosong-table", [6, 7, 8]);

		});

		// Initialize DataTables for all tabs
		function initializeDataTables() {
			var commonOptions = {
				pageLength: 25,
				searching: true,
				ordering: true,
				responsive: true,
				scrollX: true,
				fixedHeader: true,
				dom: 'Blfrtip',
				buttons: [{
					extend: 'collection',
					text: 'Export',
					buttons: ['copy', 'excel', 'csv', 'pdf', 'print']
				}],
				columnDefs: [{
						className: 'dt-right',
						targets: [7, 8, 9, 10, 13, 14]
					}, // Right align numeric columns
					{
						className: 'dt-center',
						targets: [5, 6, 11, 12, 13]
					} // Center align some columns
				],
				language: {
					url: 'https:://cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
				}
			};

			// Initialize tables if they have data
			if ($('#macet-table').length && $('#macet-table tbody tr').length > 0) {
				var macetTable = $('#macet-table').DataTable(commonOptions);
				window.macetTable = macetTable;
			}

			if ($('#slow-moving-table').length && $('#slow-moving-table tbody tr').length > 0) {
				var slowMovingTable = $('#slow-moving-table').DataTable(commonOptions);
				window.slowMovingTable = slowMovingTable;
			}

			if ($('#lama-kosong-table').length && $('#lama-kosong-table tbody tr').length > 0) {
				var lamaKosongTable = $('#lama-kosong-table').DataTable(commonOptions);
				window.lamaKosongTable = lamaKosongTable;
			}
		}

		// Reset Filter Function
		function resetFilter() {
			$('#cbg').val('');
			$('#hari').val('');
			window.location.href = '{{ route('rpemantauanbarang') }}';
		}

		// Export Data Function
		function exportAllData(format) {
			var activeTab = $('.nav-link.active').attr('href');
			var table = null;

			switch (activeTab) {
				case '#macet':
					table = window.macetTable;
					break;
				case '#slow-moving':
					table = window.slowMovingTable;
					break;
				case '#lama-kosong':
					table = window.lamaKosongTable;
					break;
			}

			if (table) {
				switch (format) {
					case 'excel':
						table.button('.buttons-excel').trigger();
						break;
					case 'pdf':
						table.button('.buttons-pdf').trigger();
						break;
					case 'csv':
						table.button('.buttons-csv').trigger();
						break;
					case 'print':
						table.button('.buttons-print').trigger();
						break;
					default:
						alert('Format export tidak dikenali');
				}
			} else {
				alert('Tidak ada data untuk di-export. Silakan filter data terlebih dahulu.');
			}
		}

		// Cetak Laporan Function
		function cetakLaporan(jenis) {
			var cbg = $('#cbg').val();
			var hari = $('#hari').val();

			if (!cbg || !hari || hari <= 0) {
				alert('Pilih cabang dan masukkan filter hari terlebih dahulu');
				return;
			}

			// Create form for jasper report
			var form = $('<form></form>');
			form.attr('method', 'POST');
			form.attr('action', '{{ route('jasper-pemantauanbarang-report') }}');
			form.attr('target', '_blank');

			// Add CSRF token
			form.append($('<input>').attr('type', 'hidden').attr('name', '_token').attr('value', '{{ csrf_token() }}'));

			// Add parameters
			form.append($('<input>').attr('type', 'hidden').attr('name', 'cbg').attr('value', cbg));
			form.append($('<input>').attr('type', 'hidden').attr('name', 'hari').attr('value', hari));
			form.append($('<input>').attr('type', 'hidden').attr('name', 'jenis').attr('value', jenis));

			// Append form to body and submit
			$('body').append(form);
			form.submit();
			form.remove();
		}

		// Helper function to format numbers
		function formatNumber(num) {
			return Number(num).toLocaleString('id-ID');
		}

		function initTableWithFooter(id, colIndexes) {
			$('#' + id).DataTable({
				paging: true,
				searching: true,
				ordering: true,
				pageLength: 10,

				footerCallback: function ( row, data, start, end, display ) {
					var api = this.api();

					colIndexes.forEach(function(col){
						var total = api
							.column(col, { page: 'current'} )
							.data()
							.reduce(function(a, b) {
								a = a === null || a === "" ? 0 : a;
								b = b === null || b === "" ? 0 : b;
								return Number(a) + Number(String(b).replace(/\./g,"").replace(",","."));
							}, 0);

						// Format ke ribuan
						$(api.column(col).footer()).html(
							total.toLocaleString('id-ID')
						);
					});
				}
			});
		}

		// Auto-submit on Enter key for hari input
		$('#hari').on('keypress', function(e) {
			if (e.which === 13) { // Enter key
				$('#reportForm').submit();
			}
		});

		// Numeric input validation for hari
		$('#hari').on('input', function() {
			var value = this.value;
			if (value < 0) {
				this.value = '';
			}
		});

		// Auto-adjust columns when window resizes
		$(window).on('resize', function() {
			setTimeout(function() {
				$.fn.dataTable.tables({
					visible: true,
					api: true
				}).columns.adjust();
			}, 100);
		});

		// Show/hide loading indicators
		function showLoading(element) {
			$(element).html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
			$(element).prop('disabled', true);
		}

		function hideLoading(element, originalText) {
			$(element).html(originalText);
			$(element).prop('disabled', false);
		}

		// Tab change handler for table adjustments
		$('#pemantauanTabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
			setTimeout(function() {
				$.fn.dataTable.tables({
					visible: true,
					api: true
				}).columns.adjust();
			}, 200);
		});

		// Update tab badges dynamically
		function updateTabBadges() {
			var macetCount = $('#macet-table tbody tr').length;
			var slowMovingCount = $('#slow-moving-table tbody tr').length;
			var lamaKosongCount = $('#lama-kosong-table tbody tr').length;

			// Update badges if tables exist
			if (macetCount > 0) {
				$('#macet-tab .badge').text(macetCount);
			}
			if (slowMovingCount > 0) {
				$('#slow-moving-tab .badge').text(slowMovingCount);
			}
			if (lamaKosongCount > 0) {
				$('#lama-kosong-tab .badge').text(lamaKosongCount);
			}
		}

		// Print specific tab data
		function printTabData() {
			var activeTab = $('.nav-link.active').attr('href');
			var table = null;

			switch (activeTab) {
				case '#macet':
					table = window.macetTable;
					break;
				case '#slow-moving':
					table = window.slowMovingTable;
					break;
				case '#lama-kosong':
					table = window.lamaKosongTable;
					break;
			}

			if (table) {
				table.button('.buttons-print').trigger();
			} else {
				alert('Tidak ada data untuk dicetak pada tab yang aktif');
			}
		}

		// Add keyboard shortcuts
		$(document).on('keydown', function(e) {
			// Ctrl + Enter to submit form
			if (e.ctrlKey && e.which === 13) {
				$('#reportForm').submit();
			}
			// Ctrl + R to reset
			if (e.ctrlKey && e.which === 82) {
				e.preventDefault();
				resetFilter();
			}
			// Ctrl + P to print active tab
			if (e.ctrlKey && e.which === 80) {
				e.preventDefault();
				printTabData();
			}
		});

		function hitungTotal(tableId, stokTSelector, stokGSelector, totalT, totalG, srmaxSelector = null, totalSRMAX = null) {
			let totalStokT = 0;
			let totalStokG = 0;
			let totalSR = 0;

			document.querySelectorAll(`#${tableId} ${stokTSelector}`).forEach(td => {
				totalStokT += parseInt(td.innerText.replace(/\./g, '')) || 0;
			});

			document.querySelectorAll(`#${tableId} ${stokGSelector}`).forEach(td => {
				totalStokG += parseInt(td.innerText.replace(/\./g, '')) || 0;
			});

			// Khusus tab slow yang memiliki SRMAX
			if (srmaxSelector && totalSRMAX) {
				document.querySelectorAll(`#${tableId} ${srmaxSelector}`).forEach(td => {
					totalSR += parseInt(td.innerText.replace(/\./g, '')) || 0;
				});
				document.getElementById(totalSRMAX).innerText = totalSR.toLocaleString('id-ID');
			}

			document.getElementById(totalT).innerText = totalStokT.toLocaleString('id-ID');
			document.getElementById(totalG).innerText = totalStokG.toLocaleString('id-ID');
		}

		document.addEventListener('DOMContentLoaded', function() {
			hitungTotal('macet-table', '.STOCKT', '.STOCKG', 'totalStockTMacet', 'totalStockGMacet');
			hitungTotal('slow-moving-table', '.STOCKT', '.STOCKG', 'totalStockTSlow', 'totalStockGSlow', '.SRMAX', 'totalStockMSlow');
			hitungTotal('lama-kosong-table', '.STOCKT', '.STOCKG', 'totalStockTLK', 'totalStockGLK');
		});

	</script>

	<!-- Additional CSS for better styling -->
	<style>
		.info-box {
			margin-bottom: 0;
		}

		.nav-tabs .nav-link {
			border-radius: 5px 5px 0 0;
		}

		.nav-tabs .nav-link.active {
			background-color: #f8f9fa;
			border-bottom: 2px solid #007bff;
		}

		.table thead th {
			background-color: #f8f9fa;
			border-top: 1px solid #dee2e6;
			font-weight: 600;
			position: sticky;
			top: 0;
			z-index: 10;
		}

		.report-content .table {
			font-size: 0.9rem;
		}

		.compact th,
		.compact td {
			padding: 0.5rem 0.75rem;
			vertical-align: middle;
		}

		.badge {
			font-size: 0.7rem;
		}

		.info-box-icon {
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.dataTables_wrapper .dataTables_paginate .paginate_button {
			padding: 0.375rem 0.75rem;
			margin-left: 0.125rem;
		}

		.dataTables_wrapper .dataTables_filter input {
			border-radius: 0.25rem;
			border: 1px solid #ced4da;
		}

		.card-outline.card-info {
			border-top: 3px solid #17a2b8;
		}

		.btn-group .dropdown-menu {
			border-radius: 0.25rem;
			box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
		}

		.alert {
			border-radius: 0.375rem;
		}

		/* Responsive adjustments */
		@media (max-width: 768px) {
			.info-box {
				margin-bottom: 1rem;
			}

			.btn-group .btn {
				font-size: 0.875rem;
			}
		}

		/* Loading animation */
		.fa-spin {
			animation: fa-spin 1s infinite linear;
		}

		@keyframes fa-spin {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		/* Custom scrollbar for tables */
		.dataTables_scrollBody::-webkit-scrollbar {
			height: 8px;
		}

		.dataTables_scrollBody::-webkit-scrollbar-track {
			background: #f1f1f1;
			border-radius: 4px;
		}

		.dataTables_scrollBody::-webkit-scrollbar-thumb {
			background: #c1c1c1;
			border-radius: 4px;
		}

		.dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
			background: #a8a8a8;
		}
	</style>
@endsection
