@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Logistik Kartu Stock</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Logistik Kartu Stock</li>
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
								<ul class="nav nav-tabs" id="reportTabs" role="tablist">
									<li class="nav-item" role="presentation">
										<a class="nav-link active" id="periode-tab" data-toggle="tab" href="#periode" role="tab" aria-controls="periode" aria-selected="true">
											<i class="fas fa-calendar mr-1"></i>Data Periode
										</a>
									</li>
									<li class="nav-item" role="presentation">
										<a class="nav-link" id="card-tab" data-toggle="tab" href="#card" role="tab" aria-controls="card" aria-selected="false">
											<i class="fas fa-file-alt mr-1"></i>Kartu Stock
										</a>
									</li>
								</ul>

								<div class="tab-content" id="reportTabContent">
									<!-- Data Periode Tab -->
									<div class="tab-pane fade show active" id="periode" role="tabpanel" aria-labelledby="periode-tab">
										<div class="pt-3">
											<form id="periodeForm" method="GET" action="{{ route('get-lkartustock') }}">
												@csrf
												<input type="hidden" name="tipe_toko" value="toko">
												<div class="row align-items-end mb-3">
													<div class="col-2">
														<label for="cbg_periode">Cabang</label>
														<select name="cbg" id="cbg_periode" class="form-control" required>
															<option value="">Pilih Cabang</option>
															@foreach ($cbg as $cabang)
																<option value="{{ $cabang->kode }}" {{ session('filter_cbg') == $cabang->kode ? 'selected' : '' }}>
																	{{ $cabang->kode }}
																</option>
															@endforeach
														</select>
													</div>
													<div class="col-2">
														<label for="periode_data">Periode</label>
														<input type="text" name="periode" id="periode_data" class="form-control" placeholder="MM-YYYY" pattern="\d{2}-\d{4}"
															value="{{ $filter_periode ?? date('m-Y') }}" required>
													</div>
													<div class="col-2">
														<label for="supp">Supplier</label>
														<input type="text" name="supp" id="supp" class="form-control" placeholder="Supplier">
													</div>
													<div class="col-2">
														<label for="sub">Sub</label>
														<input type="text" name="sub" id="sub" class="form-control" placeholder="Sub">
													</div>
													<div class="col-2">
														<label for="kode_brg_periode">Kode Barang</label>
														<input type="text" name="kode_brg" id="kode_brg_periode" class="form-control" placeholder="Kode Barang">
													</div>
													<div class="col-2">
														<button class="btn btn-primary" type="submit" name="action" value="filter">
															<i class="fas fa-search mr-1"></i>Proses
														</button>
													</div>
												</div>
												<div class="row align-items-end mb-3">
													<div class="col-2">
														<label for="barcode">Barcode</label>
														<input type="text" name="barcode" id="barcode" class="form-control" placeholder="Barcode">
													</div>
													<div class="col-4">
														<label for="na_brg">Nama Barang</label>
														<input type="text" name="na_brg" id="na_brg" class="form-control" placeholder="Nama Barang">
													</div>
													<div class="col-2">
														<button class="btn btn-danger" type="button" onclick="resetPeriode()">
															<i class="fas fa-undo mr-1"></i>Reset
														</button>
													</div>
												</div>
											</form>

											<div class="report-content mt-3">
												<div id="periodeMessage"></div>
												<div class="table-responsive" id="periodeTableContainer">
													<table class="table-striped table-bordered table-hover table" id="periodeTable">
														<thead class="thead-light">
															<tr>
																<th>Kode Barang</th>
																<th>Sub</th>
																<th>Item</th>
																<th>Nama Barang</th>
																<th>Ukuran</th>
																<th>Kemasan</th>
																<th>Supplier</th>
																<th class="text-right">SRMIN</th>
																<th class="text-right">SRMAX</th>
																<th class="text-right">LH</th>
																<th>KLK</th>
																<th>DTR</th>
																<th class="text-center">Kode Laku</th>
																<th class="text-right">Stok Gdg</th>
																<th class="text-right">Stok Toko</th>
																<th class="text-right">Stok Retur</th>
																<th class="text-right">Harga Beli</th>
																<th class="text-right">Harga Jual</th>
																<th>Pesanan</th>
																<th>TPJ</th>
																<th class="text-center">Lambat</th>
																<th>Barcode</th>
																<th>Tarik</th>
																<th>Masa Exp</th>
																<th>Retur</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td colspan="25" class="text-muted text-center">
																	<i class="fas fa-info-circle mr-2"></i>Silakan isi filter dan klik tombol Proses untuk menampilkan data
																</td>
															</tr>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>

									<!-- Kartu Stock Tab -->
									<div class="tab-pane fade" id="card" role="tabpanel" aria-labelledby="card-tab">
										<div class="pt-3">
											<form id="cardForm" method="GET" action="{{ route('get-lkartustock') }}">
												@csrf
												<div class="row align-items-end mb-3">
													<div class="col-2">
														<label for="cbg_card">Cabang</label>
														<select name="cbg" id="cbg_card" class="form-control" required>
															<option value="">Pilih Cabang</option>
															@foreach ($cbg as $cabang)
																<option value="{{ $cabang->kode }}" {{ session('filter_cbg') == $cabang->kode ? 'selected' : '' }}>
																	{{ $cabang->kode }}
																</option>
															@endforeach
														</select>
													</div>
													<div class="col-2">
														<label for="periode_card">Periode</label>
														<input type="text" name="periode" id="periode_card" class="form-control" placeholder="MM-YYYY" pattern="\d{2}-\d{4}"
															value="{{ $filter_periode ?? date('m-Y') }}" required>
													</div>
													<div class="col-2">
														<label>Tipe</label>
														<div>
															<input type="radio" name="tipe_toko" id="rtoko" value="toko" checked>
															<label for="rtoko">Toko</label>
														</div>
														<div>
															<input type="radio" name="tipe_toko" id="rgudang" value="gudang">
															<label for="rgudang">Gudang</label>
														</div>
														<div>
															<input type="radio" name="tipe_toko" id="rretur" value="retur">
															<label for="rretur">Retur</label>
														</div>
													</div>
													<div class="col-3">
														<label for="kode_brg_card">Kode Barang</label>
														<div class="input-group">
															<input type="text" name="kode_brg" id="kode_brg_card" class="form-control" placeholder="Kode Barang"
																value="{{ $filter_kode_brg }}" required>
															<div class="input-group-append">
																<button type="button" class="btn btn-outline-secondary" onclick="browseBarang()">
																	<i class="fas fa-search"></i>
																</button>
															</div>
														</div>
													</div>
													<div class="col-3">
														<button class="btn btn-primary mr-2" type="submit" name="action" value="filter">
															<i class="fas fa-search mr-1"></i>Proses
														</button>
														<button class="btn btn-danger" type="button" onclick="resetCard()">
															<i class="fas fa-undo mr-1"></i>Reset
														</button>
													</div>
												</div>
											</form>

											@if (session()->get('filter_cbg') && session()->get('filter_periode') && session()->get('filter_kode_brg'))
												<div class="row mt-2">
													<div class="col-12">
														<div class="alert alert-info">
															<strong>Filter Aktif:</strong>
															Cabang: {{ session()->get('filter_cbg') }} |
															Periode: {{ session()->get('filter_periode') }} |
															Kode Barang: {{ session()->get('filter_kode_brg') }}
														</div>
													</div>
												</div>
											@endif

											<div class="report-content mt-3">
												<div id="cardMessage"></div>
												<div class="table-responsive" id="cardTableContainer">
													<table class="table-striped table-bordered table-hover table" id="cardTable">
														<thead class="thead-light">
															<tr>
																<th>Kode</th>
																<th>Nama</th>
																<th class="text-center">Tanggal</th>
																<th class="text-center">Retur</th>
																<th>Faktur</th>
																<th class="text-right">Awal</th>
																<th class="text-right">Masuk</th>
																<th class="text-right">Keluar</th>
																<th class="text-right">Lain</th>
																<th class="text-right">Saldo</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td colspan="10" class="text-muted text-center">
																	<i class="fas fa-info-circle mr-2"></i>Silakan isi filter dan klik tombol Proses untuk menampilkan data kartu stock
																</td>
															</tr>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal untuk Browse Barang -->
	<div class="modal fade" id="browseBarangModal" tabindex="-1" role="dialog" aria-labelledby="browseBarangModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseBarangModalLabel">Pilih Barang</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<input type="text" id="searchBarang" class="form-control" placeholder="Cari barang...">
					</div>
					<div class="table-responsive" style="max-height: 400px;">
						<table class="table-striped table-sm table" id="barangTable">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Nama Barang</th>
									<th>Barcode</th>
									<th>Aksi</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script>
		var periodeDataTable, cardDataTable;

		$(document).ready(function() {
			// Initialize DataTables dengan konfigurasi KoolReport style
			periodeDataTable = $('#periodeTable').DataTable({
				"processing": true,
				"serverSide": false,
				"deferRender": true,
				"paging": true,
				"pageLength": 25,
				"lengthMenu": [
					[10, 25, 50, 100, -1],
					[10, 25, 50, 100, "Semua"]
				],
				"searching": true,
				"ordering": true,
				"info": true,
				"autoWidth": false,
				"responsive": true,
				"scrollX": true,
				"language": {
					"lengthMenu": "Tampilkan _MENU_ data per halaman",
					"zeroRecords": "Data tidak ditemukan",
					"info": "Menampilkan halaman _PAGE_ dari _PAGES_",
					"infoEmpty": "Tidak ada data tersedia",
					"infoFiltered": "(difilter dari _MAX_ total data)",
					"search": "Cari:",
					"paginate": {
						"first": "Pertama",
						"last": "Terakhir",
						"next": "Selanjutnya",
						"previous": "Sebelumnya"
					},
					"processing": '<i class="fas fa-spinner fa-spin"></i> Memproses data...'
				},
				"dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
					'<"row"<"col-sm-12 col-md-6"B>>' +
					'<"row"<"col-sm-12"tr>>' +
					'<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
				"buttons": [{
						extend: 'copy',
						text: '<i class="fas fa-copy"></i> Copy',
						className: 'btn btn-sm btn-secondary',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'excel',
						text: '<i class="fas fa-file-excel"></i> Excel',
						className: 'btn btn-sm btn-success',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'csv',
						text: '<i class="fas fa-file-csv"></i> CSV',
						className: 'btn btn-sm btn-info',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'pdf',
						text: '<i class="fas fa-file-pdf"></i> PDF',
						className: 'btn btn-sm btn-danger',
						orientation: 'landscape',
						pageSize: 'A4',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'print',
						text: '<i class="fas fa-print"></i> Print',
						className: 'btn btn-sm btn-primary',
						exportOptions: {
							columns: ':visible'
						}
					}
				],
				"columnDefs": [{
						"className": "text-right",
						"targets": [7, 8, 9, 13, 14, 15, 16, 17]
					},
					{
						"className": "text-center",
						"targets": [12, 20]
					}
				],
				"drawCallback": function(settings) {
					// Callback setelah tabel digambar
					$('[data-toggle="tooltip"]').tooltip();
				}
			});

			cardDataTable = $('#cardTable').DataTable({
				"processing": true,
				"serverSide": false,
				"deferRender": true,
				"paging": true,
				"pageLength": 25,
				"lengthMenu": [
					[10, 25, 50, 100, -1],
					[10, 25, 50, 100, "Semua"]
				],
				"searching": true,
				"ordering": true,
				"info": true,
				"autoWidth": false,
				"responsive": true,
				"scrollX": true,
				"language": {
					"lengthMenu": "Tampilkan _MENU_ data per halaman",
					"zeroRecords": "Data tidak ditemukan",
					"info": "Menampilkan halaman _PAGE_ dari _PAGES_",
					"infoEmpty": "Tidak ada data tersedia",
					"infoFiltered": "(difilter dari _MAX_ total data)",
					"search": "Cari:",
					"paginate": {
						"first": "Pertama",
						"last": "Terakhir",
						"next": "Selanjutnya",
						"previous": "Sebelumnya"
					},
					"processing": '<i class="fas fa-spinner fa-spin"></i> Memproses data...'
				},
				"dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
					'<"row"<"col-sm-12 col-md-6"B>>' +
					'<"row"<"col-sm-12"tr>>' +
					'<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
				"buttons": [{
						extend: 'copy',
						text: '<i class="fas fa-copy"></i> Copy',
						className: 'btn btn-sm btn-secondary',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'excel',
						text: '<i class="fas fa-file-excel"></i> Excel',
						className: 'btn btn-sm btn-success',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'csv',
						text: '<i class="fas fa-file-csv"></i> CSV',
						className: 'btn btn-sm btn-info',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'pdf',
						text: '<i class="fas fa-file-pdf"></i> PDF',
						className: 'btn btn-sm btn-danger',
						orientation: 'landscape',
						pageSize: 'A4',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'print',
						text: '<i class="fas fa-print"></i> Print',
						className: 'btn btn-sm btn-primary',
						exportOptions: {
							columns: ':visible'
						}
					}
				],
				"columnDefs": [{
						"className": "text-right",
						"targets": [5, 6, 7, 8, 9]
					},
					{
						"className": "text-center",
						"targets": [2, 3]
					}
				],
				"order": [
					[2, 'asc']
				],
				"drawCallback": function(settings) {
					$('[data-toggle="tooltip"]').tooltip();
				}
			});

			// Tab functionality
			$('#reportTabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');

				// Adjust column width saat tab berubah
				setTimeout(function() {
					periodeDataTable.columns.adjust().draw();
					cardDataTable.columns.adjust().draw();
				}, 200);
			});

			// Format input periode
			$('#periode_data, #periode_card').on('input', function() {
				var value = $(this).val().replace(/[^0-9]/g, '');
				if (value.length >= 2) {
					value = value.substring(0, 2) + '-' + value.substring(2, 6);
				}
				$(this).val(value);
			});

			// Form submissions
			$('#periodeForm').on('submit', function(e) {
				e.preventDefault();
				loadPeriodeData();
			});

			$('#cardForm').on('submit', function(e) {
				e.preventDefault();
				loadCardData();
			});
		});

		function loadPeriodeData() {
			if (!validatePeriode()) return;

			var formData = $('#periodeForm').serialize();

			$.ajax({
				url: '{{ route('get-lkartustock') }}',
				method: 'GET',
				data: formData,
				beforeSend: function() {
					// Show loading message
					$('#periodeMessage').html(
						'<div class="alert alert-info">' +
						'<i class="fas fa-spinner fa-spin mr-2"></i>Memuat data periode...' +
						'</div>'
					);

					// Clear table tetapi tetap tampilkan header
					periodeDataTable.clear();
					periodeDataTable.row.add([
						'<td colspan="25" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td>'
					]);
					periodeDataTable.draw();
				},
				success: function(response) {
					$('#periodeMessage').empty();

					if (response.data && response.data.length > 0) {
						// Clear existing data
						periodeDataTable.clear();

						// Add new data
						response.data.forEach(function(row) {
							periodeDataTable.row.add([
								row.KD_BRG || '',
								row.sub || '',
								row.kdbar || '',
								row.NA_BRG || '',
								row.KET_UK || '',
								row.KET_KEM || '',
								row.supp || '',
								formatNumber(row.SRMIN) || '0',
								formatNumber(row.SRMAX) || '0',
								formatNumber(row.lph) || '0',
								row.KLK || '',
								row.DTR || '',
								row.KDLAKU || '',
								formatNumber(row.stockg) || '0',
								formatNumber(row.stockt) || '0',
								formatNumber(row.stockr) || '0',
								formatNumber(row.HB) || '0',
								formatNumber(row.hj) || '0',
								row.statpsn || '',
								row.tdod || '',
								row.lambat || '',
								row.Barcode || '',
								row.TARIK || '',
								row.MASA_EXP || '',
								row.RETUR || ''
							]);
						});

						// Draw the table
						periodeDataTable.draw();

						// Show success message
						$('#periodeMessage').html(
							'<div class="alert alert-success alert-dismissible fade show">' +
							'<button type="button" class="close" data-dismiss="alert">&times;</button>' +
							'<i class="fas fa-check-circle mr-2"></i>' +
							'Berhasil memuat ' + response.data.length + ' data periode.' +
							'</div>'
						);
					} else {
						// Clear table dan tampilkan pesan
						periodeDataTable.clear().draw();

						$('#periodeMessage').html(
							'<div class="alert alert-warning text-center">' +
							'<i class="fas fa-exclamation-triangle mr-2"></i>' +
							'Tidak ada data ditemukan untuk filter yang dipilih.' +
							'</div>'
						);
					}
				},
				error: function(xhr) {
					periodeDataTable.clear().draw();

					$('#periodeMessage').html(
						'<div class="alert alert-danger text-center">' +
						'<i class="fas fa-times mr-2"></i>' +
						'Terjadi kesalahan saat memuat data: ' + (xhr.responseText || 'Unknown error') +
						'</div>'
					);
				}
			});
		}

		function loadCardData() {
			if (!validateCard()) return;

			var formData = $('#cardForm').serialize();

			$.ajax({
				url: '{{ route('get-lkartustock') }}',
				method: 'GET',
				data: formData,
				beforeSend: function() {
					$('#cardMessage').html(
						'<div class="alert alert-info">' +
						'<i class="fas fa-spinner fa-spin mr-2"></i>Memuat data kartu stock...' +
						'</div>'
					);

					// Clear table tetapi tetap tampilkan header
					cardDataTable.clear();
					cardDataTable.row.add([
						'<td colspan="10" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td>'
					]);
					cardDataTable.draw();
				},
				success: function(response) {
					$('#cardMessage').empty();

					if (response.data && response.data.length > 0) {
						// Clear existing data
						cardDataTable.clear();

						// Add new data
						response.data.forEach(function(row) {
							cardDataTable.row.add([
								row.kd_brg || row.KD_BRG || '',
								row.NA_BRG || '',
								formatDate(row.tgl) || '',
								row.FLAG || '',
								row.no_bukti || '',
								formatNumber(row.awal, 2) || '0.00',
								formatNumber(row.masuk, 2) || '0.00',
								formatNumber(row.keluar, 2) || '0.00',
								formatNumber(row.LAIN, 2) || '0.00',
								formatNumber(row.SALDO, 2) || '0.00'
							]);
						});

						// Draw the table
						cardDataTable.draw();

						// Show success message
						$('#cardMessage').html(
							'<div class="alert alert-success alert-dismissible fade show">' +
							'<button type="button" class="close" data-dismiss="alert">&times;</button>' +
							'<i class="fas fa-check-circle mr-2"></i>' +
							'Berhasil memuat ' + response.data.length + ' data kartu stock.' +
							'</div>'
						);
					} else {
						cardDataTable.clear().draw();

						$('#cardMessage').html(
							'<div class="alert alert-warning text-center">' +
							'<i class="fas fa-exclamation-triangle mr-2"></i>' +
							'Tidak ada data kartu stock ditemukan untuk filter yang dipilih.' +
							'</div>'
						);
					}
				},
				error: function(xhr) {
					cardDataTable.clear().draw();

					$('#cardMessage').html(
						'<div class="alert alert-danger text-center">' +
						'<i class="fas fa-times mr-2"></i>' +
						'Terjadi kesalahan saat memuat data kartu stock: ' + (xhr.responseText || 'Unknown error') +
						'</div>'
					);
				}
			});
		}

		function browseBarang() {
			var cbg = $('#cbg_card').val();
			if (!cbg) {
				alert('Harap pilih cabang terlebih dahulu');
				return;
			}
			$('#browseBarangModal').modal('show');
			loadBarangData('');
		}

		function loadBarangData(q) {
			var cbg = $('#cbg_card').val();

			$.ajax({
				url: '{{ route('lkartustock.browse') }}',
				method: 'GET',
				data: {
					q: q,
					cbg: cbg
				},
				success: function(data) {
					var tbody = $('#barangTable tbody');
					tbody.empty();

					if (data.length > 0) {
						data.forEach(function(item) {
							var row = '<tr>' +
								'<td>' + item.kd_brg + '</td>' +
								'<td>' + item.na_brg + '</td>' +
								'<td>' + (item.barcode || '') + '</td>' +
								'<td><button type="button" class="btn btn-sm btn-primary" onclick="selectBarang(\'' + item.kd_brg +
								'\')">Pilih</button></td>' +
								'</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="4" class="text-center">Tidak ada data barang</td></tr>');
					}
				},
				error: function() {
					$('#barangTable tbody').html('<tr><td colspan="4" class="text-center text-danger">Error memuat data</td></tr>');
				}
			});
		}

		function selectBarang(kode) {
			$('#kode_brg_card').val(kode);
			$('#browseBarangModal').modal('hide');
		}

		// Search functionality in modal
		$('#searchBarang').on('keyup', function() {
			var searchText = $(this).val();
			loadBarangData(searchText);
		});

		function resetPeriode() {
			$('#periodeForm')[0].reset();
			$('#periode_data').val('{{ date('m-Y') }}');
			periodeDataTable.clear();
			periodeDataTable.row.add([
				'<td colspan="25" class="text-center text-muted"><i class="fas fa-info-circle mr-2"></i>Silakan isi filter dan klik tombol Proses untuk menampilkan data</td>'
			]);
			periodeDataTable.draw();
			$('#periodeMessage').empty();
		}

		function resetCard() {
			$('#cardForm')[0].reset();
			$('#periode_card').val('{{ date('m-Y') }}');
			$('#rtoko').prop('checked', true);
			cardDataTable.clear();
			cardDataTable.row.add([
				'<td colspan="10" class="text-center text-muted"><i class="fas fa-info-circle mr-2"></i>Silakan isi filter dan klik tombol Proses untuk menampilkan data kartu stock</td>'
			]);
			cardDataTable.draw();
			$('#cardMessage').empty();
		}

		// Validation
		function validatePeriode() {
			var cbg = $('#cbg_periode').val();
			var periode = $('#periode_data').val();

			if (!cbg) {
				alert('Harap pilih cabang');
				$('#cbg_periode').focus();
				return false;
			}

			if (!periode || !/^\d{2}-\d{4}$/.test(periode)) {
				alert('Harap masukkan periode dengan format MM-YYYY');
				$('#periode_data').focus();
				return false;
			}

			return true;
		}

		function validateCard() {
			var cbg = $('#cbg_card').val();
			var periode = $('#periode_card').val();
			var kodeBrg = $('#kode_brg_card').val();

			if (!cbg) {
				alert('Harap pilih cabang');
				$('#cbg_card').focus();
				return false;
			}

			if (!periode || !/^\d{2}-\d{4}$/.test(periode)) {
				alert('Harap masukkan periode dengan format MM-YYYY');
				$('#periode_card').focus();
				return false;
			}

			if (!kodeBrg) {
				alert('Harap masukkan kode barang');
				$('#kode_brg_card').focus();
				return false;
			}

			return true;
		}

		// Utility functions
		function formatNumber(num, decimals = 0) {
			if (!num && num !== 0) return '0' + (decimals > 0 ? '.' + '0'.repeat(decimals) : '');
			return parseFloat(num).toLocaleString('id-ID', {
				minimumFractionDigits: decimals,
				maximumFractionDigits: decimals
			});
		}

		function formatDate(dateStr) {
			if (!dateStr) return '';
			var date = new Date(dateStr);
			if (isNaN(date.getTime())) return dateStr;
			return date.toLocaleDateString('id-ID', {
				day: '2-digit',
				month: '2-digit',
				year: 'numeric'
			});
		}

		// Enter key handling
		$('#kode_brg_card').on('keypress', function(e) {
			if (e.which == 13) {
				e.preventDefault();
				$('#cardForm').submit();
			}
		});

		// Auto-dismiss alerts after 5 seconds
		$(document).on('click', '[data-dismiss="alert"]', function() {
			$(this).closest('.alert').fadeOut();
		});
	</script>
@endsection
