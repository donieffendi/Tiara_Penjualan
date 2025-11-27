@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Diskon Hadiah Berjalan</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Diskon Hadiah Berjalan</li>
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
								<form method="GET" action="{{ route('get-diskonhadiahberjalan-report') }}" id="reportForm">
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
										<div class="col-2">
											<label for="sub1">Sub1</label>
											<input type="text" name="sub1" id="sub1" class="form-control" value="{{ session()->get('filter_sub1') }}" placeholder="Sub1">
										</div>
										<div class="col-2">
											<label for="sub2">Sub2</label>
											<input type="text" name="sub2" id="sub2" class="form-control" value="{{ session()->get('filter_sub2') }}" placeholder="Sub2">
										</div>
										<div class="col-2">
											<label for="periode_hdh">Periode HDH</label>
											<input type="text" name="periode_hdh" id="periode_hdh" class="form-control" value="{{ session()->get('filter_periode_hdh') }}"
												placeholder="mm-YYYY">
										</div>
										<div class="col-3">
											<label for="kode_hadiah">Kode Hadiah</label>
											<input type="text" name="kode_hadiah" id="kode_hadiah" class="form-control" value="{{ session()->get('filter_kode_hadiah') }}"
												placeholder="Kode Hadiah">
										</div>
									</div>

									<div class="row align-items-end mb-4">
										<div class="col-3">
											<label for="tipe_report">Tipe Report</label>
											<select name="tipe_report" id="tipe_report" class="form-control">
												<option value="1" {{ isset($tipeReport) && $tipeReport == 1 ? 'selected' : '' }}>Diskon Turun Harga Berjalan</option>
												<option value="2" {{ isset($tipeReport) && $tipeReport == 2 ? 'selected' : '' }}>Hadiah Supplier Berjalan</option>
												<option value="3" {{ isset($tipeReport) && $tipeReport == 3 ? 'selected' : '' }}>Masa Berakhir Diskon</option>
												<option value="4" {{ isset($tipeReport) && $tipeReport == 4 ? 'selected' : '' }}>Hadiah Per Supplier</option>
											</select>
										</div>
										<div class="col-9">
											<button class="btn btn-primary mr-2" type="submit" id="btnFilter">
												<i class="fas fa-search mr-1"></i>Filter
											</button>
											<button class="btn btn-danger mr-2" type="button" onclick="resetFilter()">
												<i class="fas fa-redo mr-1"></i>Reset
											</button>
											<button class="btn btn-success mr-2" type="button" onclick="exportAllData('excel')">
												<i class="fas fa-file-excel mr-1"></i>Export Excel
											</button>
											<div class="btn-group" role="group">
												<button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
													<i class="fas fa-print mr-1"></i>Cetak
												</button>
												<div class="dropdown-menu">
													<a class="dropdown-item" href="#" onclick="cetakLaporan('1')">Cetak Diskon Turun Harga</a>
													<a class="dropdown-item" href="#" onclick="cetakLaporan('2')">Cetak Hadiah Berjalan</a>
													<a class="dropdown-item" href="#" onclick="cetakLaporan('3')">Cetak Hadiah Berakhir</a>
													<a class="dropdown-item" href="#" onclick="cetakLaporan('4')">Cetak Hadiah Keluar</a>
													<div class="dropdown-divider"></div>
													<a class="dropdown-item" href="#" onclick="cetakLaporan('ALL')">Cetak Semua</a>
												</div>
											</div>
										</div>
									</div>

									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="diskonHadiahTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ !isset($tipeReport) || $tipeReport == 1 ? 'active' : '' }}" id="diskon-berjalan-tab" data-toggle="tab"
												href="#diskon-berjalan" role="tab" aria-controls="diskon-berjalan"
												aria-selected="{{ !isset($tipeReport) || $tipeReport == 1 ? 'true' : 'false' }}">
												<i class="fas fa-percentage text-success mr-1"></i>Diskon Turun Harga Berjalan
												@if (count($hasilDiskonBerjalan) > 0)
													<span class="badge badge-success ml-1">{{ count($hasilDiskonBerjalan) }}</span>
												@endif
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ isset($tipeReport) && $tipeReport == 2 ? 'active' : '' }}" id="hadiah-berjalan-tab" data-toggle="tab"
												href="#hadiah-berjalan" role="tab" aria-controls="hadiah-berjalan"
												aria-selected="{{ isset($tipeReport) && $tipeReport == 2 ? 'true' : 'false' }}">
												<i class="fas fa-gift text-primary mr-1"></i>Hadiah Supplier Berjalan
												@if (count($hasilHadiahBerjalan) > 0)
													<span class="badge badge-primary ml-1">{{ count($hasilHadiahBerjalan) }}</span>
												@endif
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ isset($tipeReport) && $tipeReport == 3 ? 'active' : '' }}" id="hadiah-berakhir-tab" data-toggle="tab"
												href="#hadiah-berakhir" role="tab" aria-controls="hadiah-berakhir"
												aria-selected="{{ isset($tipeReport) && $tipeReport == 3 ? 'true' : 'false' }}">
												<i class="fas fa-clock text-warning mr-1"></i>Masa Berakhir Diskon
												@if (count($hasilHadiahBerakhir) > 0)
													<span class="badge badge-warning ml-1">{{ count($hasilHadiahBerakhir) }}</span>
												@endif
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link {{ isset($tipeReport) && $tipeReport == 4 ? 'active' : '' }}" id="hadiah-keluar-tab" data-toggle="tab"
												href="#hadiah-keluar" role="tab" aria-controls="hadiah-keluar"
												aria-selected="{{ isset($tipeReport) && $tipeReport == 4 ? 'true' : 'false' }}">
												<i class="fas fa-truck text-info mr-1"></i>Hadiah Per Supplier
												@if (count($hasilHadiahKeluar) > 0)
													<span class="badge badge-info ml-1">{{ count($hasilHadiahKeluar) }}</span>
												@endif
											</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="diskonHadiahTabContent">
										<!-- Diskon Turun Harga Berjalan Tab -->
										<div class="tab-pane fade {{ !isset($tipeReport) || $tipeReport == 1 ? 'show active' : '' }}" id="diskon-berjalan" role="tabpanel"
											aria-labelledby="diskon-berjalan-tab">
											<div class="pt-3">
												<div class="report-content">
													@if (count($hasilDiskonBerjalan) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="diskon-berjalan-table">
																<thead>
																	<tr>
																		<th>Sub Item</th>
																		<th>Nama Barang</th>
																		<th>TH</th>
																		<th>Harga Jual</th>
																		<th>Harga Diskon</th>
																		<th>No Bukti</th>
																		<th>Tgl Mulai</th>
																		<th>Tgl Selesai</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilDiskonBerjalan as $item)
																		<tr>
																			<td>{{ $item->KD_BRG ?? '' }}</td>
																			<td>{{ $item->NA_BRG ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->TH ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->HJ ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->HJX ?? 0, 0, ',', '.') }}</td>
																			<td>{{ $item->no_bukti ?? '' }}</td>
																			<td>{{ $item->tgl_mulai ? date('d/m/Y', strtotime($item->tgl_mulai)) : '-' }}</td>
																			<td>{{ $item->tgl_sls ? date('d/m/Y', strtotime($item->tgl_sls)) : '-' }}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg'))
																Tidak ada data diskon turun harga berjalan untuk cabang <strong>{{ session()->get('filter_cbg') }}</strong>.
															@else
																Silakan pilih cabang untuk menampilkan data diskon turun harga berjalan.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>

										<!-- Hadiah Supplier Berjalan Tab -->
										<div class="tab-pane fade {{ isset($tipeReport) && $tipeReport == 2 ? 'show active' : '' }}" id="hadiah-berjalan" role="tabpanel"
											aria-labelledby="hadiah-berjalan-tab">
											<div class="pt-3">
												<div class="report-content">
													@if (count($hasilHadiahBerjalan) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="hadiah-berjalan-table">
																<thead>
																	<tr>
																		<th>No Bukti</th>
																		<th>Jenis</th>
																		<th>Kondisi</th>
																		<th>Qty Beli</th>
																		<th>RP Beli</th>
																		<th>Kelipatan</th>
																		<th>Tgl Mulai</th>
																		<th>Tgl Akhir</th>
																		<th>Keterangan</th>
																		<th>Sub Item</th>
																		<th>Nama Barang</th>
																		<th>Ukuran</th>
																		<th>Kode Hdh</th>
																		<th>Nama Hadiah</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilHadiahBerjalan as $item)
																		<tr>
																			<td>{{ $item->NO_BUKTI ?? '' }}</td>
																			<td>{{ $item->jenis ?? '' }}</td>
																			<td>{{ $item->kondisi ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->qty_beli ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->rp_beli ?? 0, 0, ',', '.') }}</td>
																			<td>{{ $item->keli ?? '' }}</td>
																			<td>{{ $item->tg_mulai ? date('d/m/Y', strtotime($item->tg_mulai)) : '-' }}</td>
																			<td>{{ $item->tg_akhir ? date('d/m/Y', strtotime($item->tg_akhir)) : '-' }}</td>
																			<td>{{ $item->ket ?? '' }}</td>
																			<td>{{ $item->kd_brg ?? '' }}</td>
																			<td>{{ $item->na_brg ?? '' }}</td>
																			<td>{{ $item->ket_uk ?? '' }}</td>
																			<td>{{ $item->KD_BRGH ?? '' }}</td>
																			<td>{{ $item->NA_BRGH ?? '' }}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg'))
																Tidak ada data hadiah supplier berjalan untuk cabang <strong>{{ session()->get('filter_cbg') }}</strong>.
															@else
																Silakan pilih cabang dan masukkan sub1 dan sub2 untuk menampilkan data hadiah supplier berjalan.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>

										<!-- Masa Berakhir Diskon Tab -->
										<div class="tab-pane fade {{ isset($tipeReport) && $tipeReport == 3 ? 'show active' : '' }}" id="hadiah-berakhir" role="tabpanel"
											aria-labelledby="hadiah-berakhir-tab">
											<div class="pt-3">
												<div class="report-content">
													@if (count($hasilHadiahBerakhir) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="hadiah-berakhir-table">
																<thead>
																	<tr>
																		<th>No Bukti</th>
																		<th>Jenis</th>
																		<th>Kondisi</th>
																		<th>Qty Beli</th>
																		<th>RP Beli</th>
																		<th>Kelipatan</th>
																		<th>Tgl Mulai</th>
																		<th>Tgl Akhir</th>
																		<th>Keterangan</th>
																		<th>Sub Item</th>
																		<th>Nama Barang</th>
																		<th>Ukuran</th>
																		<th>Kode Hdh</th>
																		<th>Nama Hadiah</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilHadiahBerakhir as $item)
																		<tr>
																			<td>{{ $item->NO_BUKTI ?? '' }}</td>
																			<td>{{ $item->jenis ?? '' }}</td>
																			<td>{{ $item->kondisi ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->qty_beli ?? 0, 0, ',', '.') }}</td>
																			<td class="text-right">{{ number_format($item->rp_beli ?? 0, 0, ',', '.') }}</td>
																			<td>{{ $item->keli ?? '' }}</td>
																			<td>{{ $item->tg_mulai ? date('d/m/Y', strtotime($item->tg_mulai)) : '-' }}</td>
																			<td>{{ $item->tg_akhir ? date('d/m/Y', strtotime($item->tg_akhir)) : '-' }}</td>
																			<td>{{ $item->ket ?? '' }}</td>
																			<td>{{ $item->kd_brg ?? '' }}</td>
																			<td>{{ $item->na_brg ?? '' }}</td>
																			<td>{{ $item->ket_uk ?? '' }}</td>
																			<td>{{ $item->KD_BRGH ?? '' }}</td>
																			<td>{{ $item->NA_BRGH ?? '' }}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_cbg'))
																Tidak ada data masa berakhir diskon untuk cabang <strong>{{ session()->get('filter_cbg') }}</strong>.
															@else
																Silakan pilih cabang untuk menampilkan data masa berakhir diskon.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>

										<!-- Hadiah Per Supplier Tab -->
										<div class="tab-pane fade {{ isset($tipeReport) && $tipeReport == 4 ? 'show active' : '' }}" id="hadiah-keluar" role="tabpanel"
											aria-labelledby="hadiah-keluar-tab">
											<div class="pt-3">
												<div class="report-content">
													@if (count($hasilHadiahKeluar) > 0)
														<div class="table-responsive">
															<table class="table-hover table-striped table-bordered compact table" id="hadiah-keluar-table">
																<thead>
																	<tr>
																		<th>No Bukti</th>
																		<th>Tanggal</th>
																		<th>No PO</th>
																		<th>Kode</th>
																		<th>Nama Barang</th>
																		<th>Kode Hadiah</th>
																		<th>Nama Hadiah</th>
																		<th>Qty</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach ($hasilHadiahKeluar as $item)
																		<tr>
																			<td>{{ $item->NO_BUKTI ?? '' }}</td>
																			<td>{{ $item->TGL ? date('d/m/Y', strtotime($item->TGL)) : '-' }}</td>
																			<td>{{ $item->NO_PO ?? '' }}</td>
																			<td>{{ $item->KODES ?? '' }}</td>
																			<td>{{ $item->NAMAS ?? '' }}</td>
																			<td>{{ $item->KD_BRGH ?? '' }}</td>
																			<td>{{ $item->NA_BRGH ?? '' }}</td>
																			<td class="text-right">{{ number_format($item->QTY ?? 0, 0, ',', '.') }}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														</div>
													@else
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															@if (session()->get('filter_periode_hdh') && session()->get('filter_kode_hadiah'))
																Tidak ada data hadiah per supplier untuk periode <strong>{{ session()->get('filter_periode_hdh') }}</strong> dan kode hadiah
																<strong>{{ session()->get('filter_kode_hadiah') }}</strong>.
															@else
																Silakan masukkan periode HDH dan kode hadiah untuk menampilkan data hadiah per supplier.
															@endif
														</div>
													@endif
												</div>
											</div>
										</div>
									</div>
								</form>

								<!-- Summary Information -->
								@if (session()->get('filter_cbg'))
									<div class="row mt-4">
										<div class="col-12">
											<div class="card card-outline card-info">
												<div class="card-header">
													<h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Ringkasan Laporan</h3>
												</div>
												<div class="card-body">
													<div class="row">
														<div class="col-md-3">
															<div class="info-box bg-success">
																<span class="info-box-icon"><i class="fas fa-percentage"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Diskon Berjalan</span>
																	<span class="info-box-number">{{ count($hasilDiskonBerjalan) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-primary">
																<span class="info-box-icon"><i class="fas fa-gift"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Hadiah Berjalan</span>
																	<span class="info-box-number">{{ count($hasilHadiahBerjalan) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-warning">
																<span class="info-box-icon"><i class="fas fa-clock"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Hadiah Berakhir</span>
																	<span class="info-box-number">{{ count($hasilHadiahBerakhir) }}</span>
																</div>
															</div>
														</div>
														<div class="col-md-3">
															<div class="info-box bg-info">
																<span class="info-box-icon"><i class="fas fa-truck"></i></span>
																<div class="info-box-content">
																	<span class="info-box-text">Hadiah Keluar</span>
																	<span class="info-box-number">{{ count($hasilHadiahKeluar) }}</span>
																</div>
															</div>
														</div>
													</div>
													<div class="row mt-2">
														<div class="col-12">
															<small class="text-muted">
																<i class="fas fa-store mr-1"></i>Cabang: <strong>{{ session()->get('filter_cbg') }}</strong> |
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
			$('#diskonHadiahTabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');
			});

			// Save active tab to localStorage
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				localStorage.setItem('diskonHadiahActiveTab', $(e.target).attr('href'));
			});

			// Restore active tab from localStorage or set based on tipeReport
			var activeTab = localStorage.getItem('diskonHadiahActiveTab');
			@if (isset($tipeReport))
				var tipeReport = {{ $tipeReport }};
				switch (tipeReport) {
					case 1:
						activeTab = '#diskon-berjalan';
						break;
					case 2:
						activeTab = '#hadiah-berjalan';
						break;
					case 3:
						activeTab = '#hadiah-berakhir';
						break;
					case 4:
						activeTab = '#hadiah-keluar';
						break;
				}
			@endif

			if (activeTab) {
				$('#diskonHadiahTabs a[href="' + activeTab + '"]').tab('show');
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
				var tipeReport = $('#tipe_report').val();

				if (!cbg) {
					e.preventDefault();
					alert('Cabang harus dipilih');
					return false;
				}

				// Validation for specific report types
				if (tipeReport == '4') {
					var periode = $('#periode_hdh').val();
					var kodeHadiah = $('#kode_hadiah').val();
					if (!periode || !kodeHadiah) {
						e.preventDefault();
						alert('Untuk laporan Hadiah Per Supplier, periode HDH dan kode hadiah harus diisi');
						return false;
					}
				}

				// Show loading
				$('#btnFilter').html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
				$('#btnFilter').prop('disabled', true);
			});

			// Change tab when tipe_report changes
			$('#tipe_report').on('change', function() {
				var tipeReport = $(this).val();
				switch (tipeReport) {
					case '1':
						$('#diskon-berjalan-tab').tab('show');
						break;
					case '2':
						$('#hadiah-berjalan-tab').tab('show');
						break;
					case '3':
						$('#hadiah-berakhir-tab').tab('show');
						break;
					case '4':
						$('#hadiah-keluar-tab').tab('show');
						break;
				}
			});
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
					targets: [2, 3, 4] // Right align numeric columns for diskon table
				}, {
					className: 'dt-center',
					targets: [5, 6, 7] // Center align date columns
				}],
				language: {
					url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
				}
			};

			// Specific options for hadiah tables
			var hadiahOptions = {
				...commonOptions,
				columnDefs: [{
					className: 'dt-right',
					targets: [3, 4, 7] // Qty Beli, RP Beli, Qty
				}, {
					className: 'dt-center',
					targets: [1, 5, 6, 7] // Jenis, Kelipatan, Tanggal columns
				}]
			};

			// Initialize tables if they have data
			if ($('#diskon-berjalan-table').length && $('#diskon-berjalan-table tbody tr').length > 0) {
				var diskonTable = $('#diskon-berjalan-table').DataTable(commonOptions);
				window.diskonTable = diskonTable;
			}

			if ($('#hadiah-berjalan-table').length && $('#hadiah-berjalan-table tbody tr').length > 0) {
				var hadiahBerjalanTable = $('#hadiah-berjalan-table').DataTable(hadiahOptions);
				window.hadiahBerjalanTable = hadiahBerjalanTable;
			}

			// Complete the remaining JavaScript section
			if ($('#hadiah-berakhir-table').length && $('#hadiah-berakhir-table tbody tr').length > 0) {
				var hadiahBerakhirTable = $('#hadiah-berakhir-table').DataTable(hadiahOptions);
				window.hadiahBerakhirTable = hadiahBerakhirTable;
			}

			if ($('#hadiah-keluar-table').length && $('#hadiah-keluar-table tbody tr').length > 0) {
				var hadiahKeluarOptions = {
					...commonOptions,
					columnDefs: [{
						className: 'dt-right',
						targets: [7] // Qty column
					}, {
						className: 'dt-center',
						targets: [1] // Tanggal column
					}]
				};
				var hadiahKeluarTable = $('#hadiah-keluar-table').DataTable(hadiahKeluarOptions);
				window.hadiahKeluarTable = hadiahKeluarTable;
			}
		}

		// Reset filter function
		function resetFilter() {
			if (confirm('Apakah Anda yakin ingin mereset semua filter?')) {
				$('#cbg').val('');
				$('#sub1').val('');
				$('#sub2').val('');
				$('#periode_hdh').val('');
				$('#kode_hadiah').val('');
				$('#tipe_report').val('1');

				// Clear session filters
				window.location.href = '{{ route('get-diskonhadiahberjalan-report') }}?reset=1';
			}
		}

		// Export all data function
		function exportAllData(format) {
			var cbg = $('#cbg').val();
			if (!cbg) {
				alert('Silakan pilih cabang terlebih dahulu');
				return;
			}

			var url = '{{ route('jasper-diskonhadiahberjalan-report') }}?' + $('#reportForm').serialize() + '&format=' + format;

			// Show loading
			$('.btn-success').html('<i class="fas fa-spinner fa-spin mr-1"></i>Exporting...');
			$('.btn-success').prop('disabled', true);

			// Create hidden form for download
			var form = $('<form>', {
				'method': 'GET',
				'action': url
			});

			form.appendTo('body').submit().remove();

			// Reset button after 3 seconds
			setTimeout(function() {
				$('.btn-success').html('<i class="fas fa-file-excel mr-1"></i>Export Excel');
				$('.btn-success').prop('disabled', false);
			}, 3000);
		}

		// Print report function
		function cetakLaporan(tipe) {
    var cbg = $('#cbg').val();
    if (!cbg) {
        alert('Silakan pilih cabang terlebih dahulu');
        return;
    }

    // ambil semua data form asli
    var formData = $('#reportForm').serializeArray();

    // tambahkan print_type
    formData.push({ name: 'print_type', value: tipe });

    // BUAT FORM DINAMIS
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('jasper-diskonhadiahberjalan-report') }}";
    form.target = "_blank"; // ⬅️ buka TAB BARU

    // CSRF token Laravel
    var token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = '{{ csrf_token() }}';
    form.appendChild(token);

    // masukkan input form lainnya
    formData.forEach(function(item) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = item.name;
        input.value = item.value;
        form.appendChild(input);
    });

    // tambahkan form ke body + submit
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}


		// Format number function
		function formatNumber(num) {
			return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
		}

		// Format date function
		function formatDate(dateString) {
			if (!dateString) return '-';
			var date = new Date(dateString);
			return date.toLocaleDateString('id-ID');
		}

		// Auto-submit form when filters change (optional)
		$('#cbg, #tipe_report').on('change', function() {
			// Auto-submit can be enabled here if needed
			// $('#reportForm').submit();
		});

		// Tooltip initialization
		$(function() {
			$('[data-toggle="tooltip"]').tooltip();
		});

		// Handle keyboard shortcuts
		$(document).keydown(function(e) {
			// Ctrl+F for search
			if (e.ctrlKey && e.keyCode === 70) {
				e.preventDefault();
				var activeTable = $('.tab-pane.active .table').DataTable();
				if (activeTable) {
					activeTable.search('').draw();
					$('.dataTables_filter input').focus();
				}
			}

			// Ctrl+R for reset
			if (e.ctrlKey && e.keyCode === 82) {
				e.preventDefault();
				resetFilter();
			}
		});

		// Handle responsive table adjustments
		$(window).on('resize', function() {
			$.fn.dataTable.tables({
				visible: true,
				api: true
			}).columns.adjust().responsive.recalc();
		});

		// Add loading overlay for better UX
		function showLoading() {
			$('<div class="loading-overlay"><div class="spinner"></div></div>').appendTo('body');
		}

		function hideLoading() {
			$('.loading-overlay').remove();
		}

		// Error handling for AJAX requests
		//$(document).ajaxError(function(event, jqXHR, settings, thrownError) {
			//hideLoading();
			//console.error('AJAX Error:', thrownError);
			//alert('Terjadi kesalahan saat memuat data. Silakan coba lagi.');
		//});

		// Success handling for AJAX requests
		$(document).ajaxSuccess(function(event, jqXHR, settings) {
			hideLoading();
		});
	</script>

	<style>
		.loading-overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.5);
			display: flex;
			justify-content: center;
			align-items: center;
			z-index: 9999;
		}

		.spinner {
			width: 50px;
			height: 50px;
			border: 5px solid #f3f3f3;
			border-top: 5px solid #3498db;
			border-radius: 50%;
			animation: spin 1s linear infinite;
		}

		@keyframes spin {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		.table-responsive {
			border: 1px solid #dee2e6;
			border-radius: 0.25rem;
		}

		.info-box {
			border-radius: 0.5rem;
			box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
		}

		.nav-tabs .nav-link {
			border: 1px solid transparent;
			border-top-left-radius: 0.25rem;
			border-top-right-radius: 0.25rem;
		}

		.nav-tabs .nav-link:hover {
			border-color: #e9ecef #e9ecef #dee2e6;
		}

		.nav-tabs .nav-link.active {
			background-color: #fff;
			border-color: #dee2e6 #dee2e6 #fff;
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

		@media (max-width: 768px) {

			.col-3,
			.col-2 {
				margin-bottom: 1rem;
			}

			.btn-group .btn {
				margin-bottom: 0.5rem;
			}
		}
	</style>
@endsection
