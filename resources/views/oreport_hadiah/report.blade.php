@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">Report Hadiah</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item active">Report Hadiah</li>
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
							<form method="POST" action="{{ url('jasper-hadiah-report') }}" id="reportForm">
								@csrf

								<!-- Filter Utama: Periode dan Cabang -->
								<div class="row mb-3">
									<div class="col-3">
										<label for="periode">Periode</label>
										<input type="text" name="periode" id="periode" class="form-control" placeholder="MM/YYYY" value="{{ date('m/Y') }}" required>
									</div>
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
								</div>

								<!-- Nav tabs -->
								<ul class="nav nav-tabs" id="reportTabs" role="tablist">
									<li class="nav-item" role="presentation">
										<a class="nav-link active" id="card-tab" data-toggle="tab" href="#card" role="tab" aria-controls="card" aria-selected="true">
											<i class="fas fa-credit-card mr-1"></i>Card
										</a>
									</li>
									<li class="nav-item" role="presentation">
										<a class="nav-link" id="perincian-tab" data-toggle="tab" href="#perincian" role="tab" aria-controls="perincian" aria-selected="false">
											<i class="fas fa-list-alt mr-1"></i>Perincian
										</a>
									</li>
									<li class="nav-item" role="presentation">
										<a class="nav-link" id="stok-gudang-tab" data-toggle="tab" href="#stok-gudang" role="tab" aria-controls="stok-gudang"
											aria-selected="false">
											<i class="fas fa-warehouse mr-1"></i>Stok Gudang
										</a>
									</li>
								</ul>

								<!-- Tab panes -->
								<div class="tab-content" id="reportTabContent">
									<!-- Card Tab -->
									<div class="tab-pane fade show active" id="card" role="tabpanel" aria-labelledby="card-tab">
										<div class="pt-3">
											<!-- Filter Tambahan untuk Card -->
											<div class="row align-items-end mb-3">
												<div class="col-2">
													<label for="tgl_dari">Dari Tanggal</label>
													<input type="date" name="tgl_dari" id="tgl_dari" class="form-control"
														value="{{ session()->get('filter_tglDari') ?? date('Y-m-d') }}">
												</div>
												<div class="col-2">
													<label for="tgl_sampai">Sampai Tanggal</label>
													<input type="date" name="tgl_sampai" id="tgl_sampai" class="form-control"
														value="{{ session()->get('filter_tglSampai') ?? date('Y-m-d') }}">
												</div>
												<div class="col-2">
													<label for="kode_dari">Dari Kode</label>
													<input type="text" name="kode_dari" id="kode_dari" class="form-control" placeholder="Kode Awal"
														value="{{ session()->get('filter_kodes1') }}">
												</div>
												<div class="col-2">
													<label for="kode_sampai">Sampai Kode</label>
													<input type="text" name="kode_sampai" id="kode_sampai" class="form-control" placeholder="Kode Akhir" value="ZZZ"
														value="{{ session()->get('filter_kodes2') }}">
												</div>
												<div class="col-4">
													<button class="btn btn-primary mr-1" type="button" onclick="filterCard()">
														<i class="fas fa-search mr-1"></i>Filter
													</button>
													<button class="btn btn-danger mr-1" type="button" onclick="resetFilterCard()">
														<i class="fas fa-redo mr-1"></i>Reset
													</button>
													<button class="btn btn-warning mr-1" type="submit" name="cetak_card" formtarget="_blank">
														<i class="fas fa-print mr-1"></i>Cetak
													</button>
													<button class="btn btn-success" type="button" onclick="exportData('excel', 'card')">
														<i class="fas fa-file-excel mr-1"></i>Export Excel
													</button>
												</div>
											</div>

											<!-- Data Table Card -->
											<div class="report-content col-md-12" id="card-result">
												@if (!empty($hasilCard))
												<div class="table-responsive">
													<table class="table-hover table-striped table-bordered table" id="card-table">
														<thead>
															<tr>
																<th>No. Tagi</th>
																<th>No. Bukti</th>
																<th>Flag</th>
																<th>Tanggal</th>
																<th>Kode Supplier</th>
																<th>Nama Supplier</th>
																<th>No. PO</th>
																<th>Total Qty</th>
																<th>Total</th>
																<th>Nett</th>
																<th>User</th>
																<th>Cabang</th>
															</tr>
														</thead>
														<tbody>
															@foreach ($hasilCard as $item)
															<tr>
																<td>{{ $item->NO_TAGI ?? '' }}</td>
																<td>{{ $item->no_bukti ?? '' }}</td>
																<td>{{ $item->flag ?? '' }}</td>
																<td>{{ $item->tgl ?? '' }}</td>
																<td>{{ $item->kodes ?? '' }}</td>
																<td>{{ $item->namas ?? '' }}</td>
																<td>{{ $item->no_po ?? '' }}</td>
																<td class="text-right">{{ number_format($item->total_qty ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->total ?? 0, 2, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->nett ?? 0, 2, ',', '.') }}</td>
																<td>{{ $item->usrnm ?? '' }}</td>
																<td>{{ $item->beliz ?? '' }}</td>
															</tr>
															@endforeach
														</tbody>
													</table>
												</div>
												@else
												<div class="alert alert-info">
													<i class="fas fa-info-circle mr-2"></i>
													Silakan set filter dan klik tombol Filter untuk menampilkan data.
												</div>
												@endif
											</div>
										</div>
									</div>

									<!-- Perincian Tab -->
									<div class="tab-pane fade" id="perincian" role="tabpanel" aria-labelledby="perincian-tab">
										<div class="pt-3">
											<div class="row mb-3">
												<div class="col-12">
													<button class="btn btn-primary mr-1" type="button" onclick="loadPerincian()">
														<i class="fas fa-search mr-1"></i>Load Data
													</button>
													<button class="btn btn-warning mr-1" type="submit" name="cetak_perincian" formtarget="_blank">
														<i class="fas fa-print mr-1"></i>Cetak
													</button>
													<button class="btn btn-success" type="button" onclick="exportData('excel', 'perincian')">
														<i class="fas fa-file-excel mr-1"></i>Export Excel
													</button>
												</div>
											</div>

											<div class="report-content col-md-12" id="perincian-result">
												@if (!empty($hasilPerincian))
												<div class="table-responsive">
													<table class="table-hover table-striped table-bordered table" id="perincian-table">
														<thead>
															<tr>
																<th>Sub Item</th>
																<th>Nama Barang</th>
																<th>Awal</th>
																<th>Masuk</th>
																<th>Keluar</th>
																<th>Lain2</th>
																<th>Saldo</th>
															</tr>
														</thead>
														<tbody>
															@foreach ($hasilPerincian as $item)
															<tr>
																<td>{{ $item->sub_item ?? '' }}</td>
																<td>{{ $item->nama_barang ?? '' }}</td>
																<td class="text-right">{{ number_format($item->awal ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->masuk ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->keluar ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->lain2 ?? 0, 0, ',', '.') }}</td>
																<td class="text-right"><strong>{{ number_format($item->saldo ?? 0, 0, ',', '.') }}</strong></td>
															</tr>
															@endforeach
														</tbody>
													</table>
												</div>
												@else
												<div class="alert alert-info">
													<i class="fas fa-info-circle mr-2"></i>
													Klik tombol Load Data untuk menampilkan data perincian.
												</div>
												@endif
											</div>
										</div>
									</div>

									<!-- Stok Gudang Tab -->
									<div class="tab-pane fade" id="stok-gudang" role="tabpanel" aria-labelledby="stok-gudang-tab">
										<div class="pt-3">
											<div class="row mb-3">
												<div class="col-12">
													<button class="btn btn-primary mr-1" type="button" onclick="loadStokGudang()">
														<i class="fas fa-search mr-1"></i>Load Data
													</button>
													<button class="btn btn-warning mr-1" type="submit" name="cetak_stok_gudang" formtarget="_blank">
														<i class="fas fa-print mr-1"></i>Cetak
													</button>
													<button class="btn btn-success" type="button" onclick="exportData('excel', 'stok-gudang')">
														<i class="fas fa-file-excel mr-1"></i>Export Excel
													</button>
												</div>
											</div>

											<div class="report-content col-md-12" id="stok-gudang-result">
												@if (!empty($hasilStokGudang))
												<div class="table-responsive">
													<table class="table-hover table-striped table-bordered table" id="stok-gudang-table">
														<thead>
															<tr>
																<th>Sub Item</th>
																<th>Nama Barang</th>
																<th>Awal</th>
																<th>Masuk</th>
																<th>Keluar</th>
																<th>Lain2</th>
																<th>Saldo</th>
															</tr>
														</thead>
														<tbody>
															@foreach ($hasilStokGudang as $item)
															<tr>
																<td>{{ $item->sub_item ?? '' }}</td>
																<td>{{ $item->nama_barang ?? '' }}</td>
																<td class="text-right">{{ number_format($item->awal ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->masuk ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->keluar ?? 0, 0, ',', '.') }}</td>
																<td class="text-right">{{ number_format($item->lain2 ?? 0, 0, ',', '.') }}</td>
																<td class="text-right"><strong>{{ number_format($item->saldo ?? 0, 0, ',', '.') }}</strong></td>
															</tr>
															@endforeach
														</tbody>
													</table>
												</div>
												@else
												<div class="alert alert-info">
													<i class="fas fa-info-circle mr-2"></i>
													Klik tombol Load Data untuk menampilkan data stok gudang.
												</div>
												@endif
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
			localStorage.setItem('activeHadiahTab', $(e.target).attr('href'));
		});

		// Restore active tab from localStorage
		var activeTab = localStorage.getItem('activeHadiahTab');
		if (activeTab) {
			$('#reportTabs a[href="' + activeTab + '"]').tab('show');
		}

		// Initialize DataTables
		initializeDataTables();

		// Auto-format periode input
		$('#periode').on('input', function() {
			var value = this.value.replace(/\D/g, ''); // Remove non-digits
			if (value.length >= 2) {
				this.value = value.substring(0, 2) + '/' + value.substring(2, 6);
			}
		});
	});

	// Initialize DataTables
	function initializeDataTables() {
		if ($.fn.DataTable.isDataTable('#card-table')) {
			$('#card-table').DataTable().destroy();
		}
		if ($.fn.DataTable.isDataTable('#perincian-table')) {
			$('#perincian-table').DataTable().destroy();
		}
		if ($.fn.DataTable.isDataTable('#stok-gudang-table')) {
			$('#stok-gudang-table').DataTable().destroy();
		}

		// Card table
		if ($('#card-table').length) {
			$('#card-table').DataTable({
				pageLength: 25,
				searching: true,
				ordering: true,
				responsive: true
			});
		}

		// Perincian table
		if ($('#perincian-table').length) {
			$('#perincian-table').DataTable({
				pageLength: 25,
				searching: true,
				ordering: true,
				responsive: true
			});
		}

		// Stok Gudang table
		if ($('#stok-gudang-table').length) {
			$('#stok-gudang-table').DataTable({
				pageLength: 25,
				searching: true,
				ordering: true,
				responsive: true
			});
		}

	}

	// Filter Card Function
	function filterCard() {
		var cbg = $('#cbg').val();
		var periode = $('#periode').val();
		var tglDari = $('#tgl_dari').val();
		var tglSampai = $('#tgl_sampai').val();
		var kodeDari = $('#kode_dari').val();
		var kodeSampai = $('#kode_sampai').val();

		if (!cbg || !periode) {
			alert('Cabang dan periode harus diisi');
			return;
		}

		$('#card .btn-primary').html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
		$('#card .btn-primary').prop('disabled', true);
		$.ajax({
			url: "{{ route('get-hadiah-report') }}",
			method: 'GET',
			data: {
				type: 'card',
				cbg: cbg,
				periode: periode,
				tglDari: tglDari,
				tglSampai: tglSampai,
				kodeDari: kodeDari,
				kodeSampai: kodeSampai
			},
			beforeSend: function() {
				$('#card-result').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
			},
			success: function(response) {
				if (response.length > 0) {
					var html =
						'<div class="table-responsive"><table class="table table-striped table-bordered" id="card-table-ajax"><thead><tr>';
					html += '<th>No.</th><th>No Bukti</th><th>Tanggal</th><th>Kode Barang</th><th>Nama Barang</th><th>Awal</th><th>Masuk</th><th>Keluar</th><th>Lain2</th><th>Akhir</th>';
					html += '</tr></thead><tbody>';

					$.each(response, function(i, item) {
						const date = new Date(item.tgl);
						const tglFormat = date.toLocaleDateString('id-ID');
						html += '<tr>';
						html += '<td>' + (i + 1) + '</td>';
						html += '<td>' + (item.no_bukti || '') + '</td>';
						html += '<td>' + (tglFormat || '') + '</td>';
						html += '<td>' + (item.kd_brgh || '') + '</td>';
						html += '<td>' + (item.na_brgh || '') + '</td>';
						html += '<td class="text-right">' + formatNumber(item.awal || 0) + '</td>';
						html += '<td class="text-right">' + formatNumber(item.masuk || 0) + '</td>';
						html += '<td class="text-right">' + formatNumber(item.keluar || 0) + '</td>';
						html += '<td class="text-right">' + formatNumber(item.lain || 0) + '</td>';
						html += '<td class="text-right"><strong>' + formatNumber(item.AKHIR || 0) + '</strong></td>';
						html += '</tr>';
					});

					html += '</tbody></table></div>';
					$('#card-result').html(html);

					// Initialize DataTable
					$('#card-table-ajax').DataTable({
						pageLength: 25,
						searching: true,
						ordering: true,
						responsive: true,
						columnDefs: [{
							className: 'dt-right',
							targets: [5, 6, 7, 8, 9]
						}]
					});
				} else {
					$('#card-result').html(
						'<div class="alert alert-warning">Tidak ada data card untuk parameter yang dipilih</div>');
				}
			},
			error: function() {
				$('#card-result').html('<div class="alert alert-danger">Error loading data</div>');
			}
		});
		$('#card .btn-primary').html('<i class="fas fa-search mr-1"></i>Filter');
		$('#card .btn-primary').prop('disabled', false);
	}

	// Reset Filter Card Function
	function resetFilterCard() {
		$('#tgl_dari').val("{{ date('Y-m-d') }}");
		$('#tgl_sampai').val("{{ date('Y-m-d') }}");
		$('#kode_dari').val('');
		$('#kode_sampai').val('');
		window.location.href = "{{ route('rhadiah') }}";
	}

	// Load Perincian Function
	function loadPerincian() {
		var cbg = $('#cbg').val();
		var periode = $('#periode').val();

		if (!cbg || !periode) {
			alert('Cabang dan periode harus diisi');
			return;
		}
		$.ajax({
			url: "{{ route('get-hadiah-report') }}",
			method: 'GET',
			data: {
				type: 'perincian',
				cbg: cbg,
				periode: periode
			},
			beforeSend: function() {
				$('#perincian-result').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
			},
			success: function(response) {
				if (response.length > 0) {
					var html =
						'<div class="table-responsive"><table class="table table-striped table-bordered" id="perincian-table-ajax"><thead><tr>';
					html += '<th>Sub Item</th><th>Nama Barang</th><th>Awal</th><th>Masuk</th><th>Keluar</th><th>Lain2</th><th>Saldo</th>';
					html += '</tr></thead><tbody>';

					$.each(response, function(i, item) {
						html += '<tr>';
						html += '<td>' + (item.KD_BRGh || '') + '</td>';
						html += '<td>' + (item.NA_BRGh || '') + '</td>';
						html += '<td class="text-right">' + formatNumber(item.AW || 0) + '</td>';
						html += '<td class="text-right">' + formatNumber(item.MA || 0) + '</td>';
						html += '<td class="text-right">' + formatNumber(item.KE || 0) + '</td>';
						html += '<td class="text-right">' + formatNumber(item.LN || 0) + '</td>';
						html += '<td class="text-right"><strong>' + formatNumber(item.AK || 0) + '</strong></td>';
						html += '</tr>';
					});

					html += '</tbody></table></div>';
					$('#perincian-result').html(html);

					// Initialize DataTable
					$('#perincian-table-ajax').DataTable({
						pageLength: 25,
						searching: true,
						ordering: true,
						responsive: true
					});
				} else {
					$('#perincian-result').html(
						'<div class="alert alert-warning">Tidak ada data perincian untuk parameter yang dipilih</div>');
				}
			},
			error: function() {
				$('#perincian-result').html('<div class="alert alert-danger">Error loading data</div>');
			}
		});
	}

	// Load Stok Gudang Function
	function loadStokGudang() {
		var cbg = $('#cbg').val();
		var periode = $('#periode').val();

		if (!cbg || !periode) {
			alert('Cabang dan periode harus diisi');
			return;
		}

		$.ajax({
			url: "{{ route('get-hadiah-report') }}",
			method: 'GET',
			data: {
				type: 'stok_gudang',
				cbg: cbg,
				periode: periode
			},
			beforeSend: function() {
				$('#stok-gudang-result').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
			},
			success: function(response) {
				if (response.length > 0) {
					var html =
						'<div class="table-responsive"><table class="table table-striped table-bordered" id="stok-gudang-table-ajax"><thead><tr>';
					html += '<th>Sub Item</th><th>Nama Barang</th><th>Awal</th><th>Masuk</th><th>Keluar</th><th>Lain2</th><th>Saldo</th>';
					html += '</tr></thead><tbody>';

					$.each(response, function(i, item) {
						html += '<tr>';
						html += '<td>' + (item.KD_BRGh || '') + '</td>';
						html += '<td>' + (item.NA_BRGh || '') + '</td>';
						html += '<td class="text-right">' + formatNumber(item.AW || 0) + '</td>';
						html += '<td class="text-right">' + formatNumber(item.MA || 0) + '</td>';
						html += '<td class="text-right">' + formatNumber(item.KE || 0) + '</td>';
						html += '<td class="text-right">' + formatNumber(item.LN || 0) + '</td>';
						html += '<td class="text-right"><strong>' + formatNumber(item.AK || 0) + '</strong></td>';
						html += '</tr>';
					});

					html += '</tbody></table></div>';
					$('#stok-gudang-result').html(html);

					// Initialize DataTable
					$('#stok-gudang-table-ajax').DataTable({
						pageLength: 25,
						searching: true,
						ordering: true,
						responsive: true,
					});
				} else {
					$('#stok-gudang-result').html(
						'<div class="alert alert-warning">Tidak ada data stok gudang untuk parameter yang dipilih</div>');
				}
			},
			error: function() {
				$('#stok-gudang-result').html('<div class="alert alert-danger">Error loading data</div>');
			}
		});
	}

	// Export functions
	function exportData(format, tabType) {
		var tableSelector;
		switch (tabType) {
			case 'card':
				tableSelector = '#card-table';
				break;
			case 'perincian':
				tableSelector = '#perincian-table, #perincian-table-ajax';
				break;
			case 'stok-gudang':
				tableSelector = '#stok-gudang-table, #stok-gudang-table-ajax';
				break;
			default:
				alert('Tipe tab tidak dikenali');
				return;
		}

		var dataTable = $(tableSelector).DataTable();
		if (dataTable && dataTable.button) {
			switch (format) {
				case 'excel':
					dataTable.button('.buttons-excel').trigger();
					break;
				case 'pdf':
					dataTable.button('.buttons-pdf').trigger();
					break;
				case 'csv':
					dataTable.button('.buttons-csv').trigger();
					break;
				case 'print':
					dataTable.button('.buttons-print').trigger();
					break;
				default:
					alert('Format export tidak dikenali');
			}
		} else {
			alert('Tidak ada data untuk di-export. Silakan load data terlebih dahulu.');
		}
	}

	// Helper function to format numbers
	function formatNumber(num) {
		return Number(num).toLocaleString('id-ID');
	}

	// Form validation
	$('#reportForm').on('submit', function(e) {
		var cbg = $('#cbg').val();
		var periode = $('#periode').val();

		if (!cbg || !periode) {
			e.preventDefault();
			alert('Cabang dan periode harus diisi');
		}
	});
</script>
@endsection