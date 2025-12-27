@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="{{ url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
	<link rel="stylesheet" href="{{ url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
	{{-- <link rel="stylesheet" href="{{url('https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css') }}"> --}}
@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Rekap Hasil Inventarisasi</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Rekap Hasil Inventarisasi</li>
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
								<form method="POST" action="{{ url('jasper-inventaris-report') }}" id="reportForm">
									@csrf
									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="kodetg-tab" data-toggle="tab" href="#kodetg" role="tab" aria-controls="kodetg" aria-selected="true">
												<i class="fas fa-cubes mr-1"></i>Kode 3
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="non-tab" data-toggle="tab" href="#non" role="tab" aria-controls="non" aria-selected="false">
												<i class="fas fa-cube mr-1"></i>Non Kode 3
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="busana-tab" data-toggle="tab" href="#busana" role="tab" aria-controls="busana" aria-selected="false">
												<i class="fas fa-user mr-1"></i>Busana
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="pusat-tab" data-toggle="tab" href="#pusat" role="tab" aria-controls="pusat" aria-selected="false">
												<i class="fas fa-warehouse mr-1"></i>Pusat Hidangan
											</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Detail Transaksi Tab -->
										<div class="tab-pane fade show active" id="kodetg" role="tabpanel" aria-labelledby="kodetg-tab">
											<div class="pt-3">
												<div class="form-group">
													<!-- Search Filter Row -->
													<div class="row align-items-end mb-3">
														<div class="col-2">
															<label for="cbg_kodetg">Cabang</label>
															<select name="cbg_kodetg" id="cbg_kodetg" class="form-control" required>
																<option value="">Pilih Cabang</option>
																@foreach ($cbg as $cabang)
																	<option value="{{ $cabang->KODE }}">{{ $cabang->KODE }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-2">
															<label for="per_kodetg">Periode</label>
															<select name="per_kodetg" id="per_kodetg" class="form-control" required>
																<option value="">Pilih Periode</option>
																@foreach ($per as $periode)
																	<option value="{{ $periode->PERIO }}">{{ $periode->PERIO }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-md-2">
															<input type="checkbox" id="cek_kodetg" name="cek_kodetg" value="1"> <label for="cek_kodetg">AKHIR</label>
														</div>
														<div class="col-6">
															<button class="btn btn-primary mr-1" type="button" id="btnFilterKodetg" onclick="filterInventaris('kodetg')">
																<i class="fas fa-search mr-1"></i>Filter
															</button>
															<button class="btn btn-danger mr-1" type="button" onclick="resetFilter('kodetg')">
																<i class="fas fa-redo mr-1"></i>Reset
															</button>
															<button class="btn btn-warning mr-1" type="button" onclick="cetakInventaris('kodetg')">
																<i class="fas fa-print mr-1"></i>Cetak
															</button>
														</div>
													</div>

													<!-- Data Kodetg -->
													<div class="col-md-12 report-content" id="kodetg-result">
														@if (!empty($hasilInventaris))
															<div class="table-responsive">
																<table id="tabelKodetg" class="table-striped table-bordered nowrap table" style="width:100%">
																	<thead>
																		<tr>
																			<th>No Form</th>
																			<th>Tanggal</th>
																			<th>Sub</th>
																			<th>Kelompok</th>
																			<th>Saldo</th>
																			<th>Total</th>
																		</tr>
																	</thead>
																	<tbody>
																		@foreach ($hasilInventaris as $item)
																			<tr>
																				<td>{{ $item->NO_FORM ?? '' }}</td>
																				<td>{{ isset($item->TGL) ? date('d/m/Y', strtotime($item->TGL)) : '' }}</td>
																				<td>{{ $item->SUB ?? '' }}</td>
																				<td>{{ $item->KELOMPOK ?? '' }}</td>
																				<td class="text-right">{{ number_format($item->SALDO ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->TOTAL ?? 0, 0, ',', '.') }}</td>
																			</tr>
																		@endforeach
																	</tbody>
																</table>
															</div>
														@else
															<div class="alert alert-info">
																<i class="fas fa-info-circle mr-2"></i>
																Silakan Klik Filter untuk menampilkan Data.
															</div>
														@endif
													</div>
												</div>
											</div>
										</div>

										<!-- Non Kode 3 Barang Tab -->
										<div class="tab-pane fade" id="non" role="tabpanel" aria-labelledby="non-tab">
											<div class="pt-3">
												<div class="form-group">
													<div class="row align-items-end mb-3">
														<div class="col-2">
															<label for="cbg_non">Cabang</label>
															<select name="cbg_non" id="cbg_non" class="form-control" required>
																<option value="">Pilih Cabang</option>
																@foreach ($cbg as $cabang)
																	<option value="{{ $cabang->KODE }}">{{ $cabang->KODE }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-2">
															<label for="per_non">Periode</label>
															<select name="per_non" id="per_non" class="form-control" required>
																<option value="">Pilih Periode</option>
																@foreach ($per as $periode)
																	<option value="{{ $periode->PERIO }}">{{ $periode->PERIO }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-md-2">
															<input type="checkbox" id="cek_non" name="cek_non" value="1"> <label for="cek_non">AKHIR</label>
														</div>
														<div class="col-6">
															<button class="btn btn-primary mr-1" type="button" id="btnFilterNon" onclick="filterInventaris('non')">
																<i class="fas fa-search mr-1"></i>Filter
															</button>
															<button class="btn btn-danger mr-1" type="button" onclick="resetFilter('non')">
																<i class="fas fa-redo mr-1"></i>Reset
															</button>
															<button class="btn btn-warning mr-1" type="button" onclick="cetakInventaris('non')">
																<i class="fas fa-print mr-1"></i>Cetak
															</button>
														</div>
													</div>

													<div class="col-md-12 report-content" id="non-result">
														@if (!empty($hasilInventaris))
															<div class="table-responsive">
																<table id="tabelNon" class="table-striped table-bordered nowrap table" style="width:100%">
																	<thead>
																		<tr>
																			<th>No Form</th>
																			<th>Tanggal</th>
																			<th>Sub</th>
																			<th>Kelompok</th>
																			<th>Saldo</th>
																			<th>Total</th>
																		</tr>
																	</thead>
																	<tbody>
																		@foreach ($hasilInventaris as $item)
																			<tr>
																				<td>{{ $item->NO_FORM ?? '' }}</td>
																				<td>{{ isset($item->TGL) ? date('d/m/Y', strtotime($item->TGL)) : '' }}</td>
																				<td>{{ $item->SUB ?? '' }}</td>
																				<td>{{ $item->KELOMPOK ?? '' }}</td>
																				<td class="text-right">{{ number_format($item->SALDO ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->TOTAL ?? 0, 0, ',', '.') }}</td>
																			</tr>
																		@endforeach
																	</tbody>
																</table>
															</div>
														@else
															<div class="alert alert-info">
																<i class="fas fa-info-circle mr-2"></i>
																Silakan Klik Filter untuk menampilkan Data.
															</div>
														@endif
													</div>
												</div>
											</div>
										</div>

										<!-- Busana Tab -->
										<div class="tab-pane fade" id="busana" role="tabpanel" aria-labelledby="busana-tab">
											<div class="pt-3">
												<div class="form-group">
													<div class="row align-items-end mb-3">
														<div class="col-2">
															<label for="cbg_busana">Cabang</label>
															<select name="cbg_busana" id="cbg_busana" class="form-control" required>
																<option value="">Pilih Cabang</option>
																@foreach ($cbg as $cabang)
																	<option value="{{ $cabang->KODE }}">{{ $cabang->KODE }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-2">
															<label for="per_busana">Periode</label>
															<select name="per_busana" id="per_busana" class="form-control" required>
																<option value="">Pilih Periode</option>
																@foreach ($per as $periode)
																	<option value="{{ $periode->PERIO }}">{{ $periode->PERIO }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-md-2">
															<input type="checkbox" id="cek_busana" name="cek_busana" value="1"> <label for="cek_busana">AKHIR</label>
														</div>
														<div class="col-6">
															<button class="btn btn-primary mr-1" type="button" id="btnFilterBusana" onclick="filterInventaris('busana')">
																<i class="fas fa-search mr-1"></i>Filter
															</button>
															<button class="btn btn-danger mr-1" type="button" onclick="resetFilter('busana')">
																<i class="fas fa-redo mr-1"></i>Reset
															</button>
															<button class="btn btn-warning mr-1" type="button" onclick="cetakInventaris('busana')">
																<i class="fas fa-print mr-1"></i>Cetak
															</button>
														</div>
													</div>

													<div class="col-md-12 report-content" id="busana-result">
														@if (!empty($hasilInventaris))
															<div class="table-responsive">
																<table id="tabelBusana" class="table-striped table-bordered nowrap table" style="width:100%">
																	<thead>
																		<tr>
																			<th>No Form</th>
																			<th>Tanggal</th>
																			<th>Sub</th>
																			<th>Kelompok</th>
																			<th>Saldo</th>
																			<th>Total</th>
																		</tr>
																	</thead>
																	<tbody>
																		@foreach ($hasilInventaris as $item)
																			<tr>
																				<td>{{ $item->NO_FORM ?? '' }}</td>
																				<td>{{ isset($item->TGL) ? date('d/m/Y', strtotime($item->TGL)) : '' }}</td>
																				<td>{{ $item->SUB ?? '' }}</td>
																				<td>{{ $item->KELOMPOK ?? '' }}</td>
																				<td class="text-right">{{ number_format($item->SALDO ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->TOTAL ?? 0, 0, ',', '.') }}</td>
																			</tr>
																		@endforeach
																	</tbody>
																</table>
															</div>
														@else
															<div class="alert alert-info">
																<i class="fas fa-info-circle mr-2"></i>
																Silakan Klik Filter untuk menampilkan Data.
															</div>
														@endif
													</div>
												</div>
											</div>
										</div>

										<!-- Data Kasir Tab -->
										<div class="tab-pane fade" id="pusat" role="tabpanel" aria-labelledby="pusat-tab">
											<div class="pt-3">
												<div class="form-group">
													<div class="row align-items-end mb-3">
														<div class="col-2">
															<label for="cbg_pusat">Cabang</label>
															<select name="cbg_pusat" id="cbg_pusat" class="form-control" required>
																<option value="">Pilih Cabang</option>
																@foreach ($cbg as $cabang)
																	<option value="{{ $cabang->KODE }}">{{ $cabang->KODE }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-2">
															<label for="per_pusat">Periode</label>
															<select name="per_pusat" id="per_pusat" class="form-control" required>
																<option value="">Pilih Periode</option>
																@foreach ($per as $periode)
																	<option value="{{ $periode->PERIO }}">{{ $periode->PERIO }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-md-2">
															<input type="checkbox" id="cek_pusat" name="cek_pusat" value="1"> <label for="cek_pusat">AKHIR</label>
														</div>
														<div class="col-6">
															<button class="btn btn-primary mr-1" type="button" id="btnFilterPusat" onclick="filterInventaris('pusat')">
																<i class="fas fa-search mr-1"></i>Filter
															</button>
															<button class="btn btn-danger mr-1" type="button" onclick="resetFilter('pusat')">
																<i class="fas fa-redo mr-1"></i>Reset
															</button>
															<button class="btn btn-warning mr-1" type="button" onclick="cetakInventaris('pusat')">
																<i class="fas fa-print mr-1"></i>Cetak
															</button>
														</div>
													</div>

													<div class="col-md-12 report-content" id="pusat-result">
														@if (!empty($hasilInventaris))
															<div class="table-responsive">
																<table id="tabelPusat" class="table-striped table-bordered nowrap table" style="width:100%">
																	<thead>
																		<tr>
																			<th>No Form</th>
																			<th>Tanggal</th>
																			<th>Sub</th>
																			<th>Kelompok</th>
																			<th>Saldo</th>
																			<th>Total</th>
																		</tr>
																	</thead>
																	<tbody>
																		@foreach ($hasilInventaris as $item)
																			<tr>
																				<td>{{ $item->NO_FORM ?? '' }}</td>
																				<td>{{ isset($item->TGL) ? date('d/m/Y', strtotime($item->TGL)) : '' }}</td>
																				<td>{{ $item->SUB ?? '' }}</td>
																				<td>{{ $item->KELOMPOK ?? '' }}</td>
																				<td class="text-right">{{ number_format($item->SALDO ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->TOTAL ?? 0, 0, ',', '.') }}</td>
																			</tr>
																		@endforeach
																	</tbody>
																</table>
															</div>
														@else
															<div class="alert alert-info">
																<i class="fas fa-info-circle mr-2"></i>
																Silakan Klik Filter untuk menampilkan Data.
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
	<script src="{{ url('AdminLTE/plugins/datatables/jquery.dataTables.js') }}"></script>
	<script src="{{ url('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>
	<script src="{{ url('http://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ url('https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js') }}"></script>
	<script src="{{ url('https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js') }}"></script>
	<script src="{{ url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		$(document).ready(function() {

			// Tab Bootstrap
			$('#reportTabs a').on('click', function(e) {
				e.preventDefault();
				$(this).tab('show');
			});

			// Simpan tab aktif
			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				localStorage.setItem('activeKasirBantuTab', $(e.target).attr('href'));
			});

			// Restore tab aktif
			var activeTab = localStorage.getItem('activeKasirBantuTab');
			if (activeTab) {
				$('#reportTabs a[href="' + activeTab + '"]').tab('show');
			}

			// Auto format periode input
			$('#periode_detail, #periode_summary, #periode_kasir').on('input', function() {
				var value = this.value.replace(/\D/g, '');
				if (value.length >= 2) this.value = value.substring(0, 2) + '-' + value.substring(2, 6);
			});

			// Inisialisasi DataTable awal (Detail)
			@if (!empty($hasilInventaris))
				$('#tabelKodetg').DataTable({
					pageLength: 25,
					searching: true,
					ordering: true,
					responsive: true,
					columnDefs: [{
						className: 'dt-right',
						targets: [4, 5]
					}],
					language: {
						url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
					}
				});
			@endif

			// Tampilkan SweetAlert jika ada error atau success dari controller
			@if (isset($error))
				Swal.fire({
					icon: 'error',
					title: 'Oops...',
					text: '{{ $error }}',
					confirmButtonColor: '#3085d6'
				});
			@endif

			@if (isset($success))
				Swal.fire({
					icon: 'success',
					title: 'Berhasil!',
					text: '{{ $success }}',
					timer: 2000,
					showConfirmButton: false
				});
			@endif

		});

		// -------------------------------
		// Fungsi Filter per Tab
		// -------------------------------
		function filterInventaris(tabType) {
			var cbg = '',
				per = '',
				cek = '',
				btnId = '';
			switch (tabType) {
				case 'kodetg':
					cbg = $('#cbg_kodetg').val();
					per = $('#per_kodetg').val();
					cek = $('#cek_kodetg').is(':checked') ? 1 : 0;
					btnId = '#btnFilterKodetg';
					break;
				case 'non':
					cbg = $('#cbg_non').val();
					per = $('#per_non').val();
					cek = $('#cek_non').is(':checked') ? 1 : 0;
					btnId = '#btnFilterNon';
					break;
				case 'busana':
					cbg = $('#cbg_busana').val();
					per = $('#per_busana').val();
					cek = $('#cek_busana').is(':checked') ? 1 : 0;
					btnId = '#btnFilterBusana';
					break;
				case 'pusat':
					cbg = $('#cbg_pusat').val();
					per = $('#per_pusat').val();
					cek = $('#cek_pusat').is(':checked') ? 1 : 0;
					btnId = '#btnFilterPusat';
					break;
			}

			// Tampilkan loading
			$(btnId).html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...').prop('disabled', true);

			$.ajax({
				url: '{{ route('get-inventaris-report-ajax') }}',
				method: 'GET',
				data: {
					tab: tabType,
					cbg: cbg,
					per: per,
					cek: cek
				},
				success: function(res) {
					if (res.success) {
						displayTabData(tabType, res.data);

						// Tampilkan SweetAlert success
						Swal.fire({
							icon: 'success',
							title: 'Berhasil!',
							text: res.message + ' (' + res.count + ' data ditemukan)',
							timer: 2000,
							showConfirmButton: false
						});
					} else {
						// Tampilkan SweetAlert error
						Swal.fire({
							icon: 'error',
							title: 'Gagal!',
							text: res.message || 'Gagal memuat data',
							confirmButtonColor: '#3085d6'
						});
					}
				},
				error: function(xhr) {
					console.error(xhr);

					// Tampilkan SweetAlert error
					let errorMsg = 'Terjadi kesalahan saat memuat data';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMsg = xhr.responseJSON.message;
					}

					Swal.fire({
						icon: 'error',
						title: 'Error!',
						text: errorMsg,
						confirmButtonColor: '#d33'
					});
				},
				complete: function() {
					$(btnId).html('<i class="fas fa-search mr-1"></i>Filter').prop('disabled', false);
				}
			});
		}

		// -------------------------------
		// Fungsi Render Data di Tab
		// -------------------------------
		function displayTabData(tabType, data) {
			var targetDiv = '#' + tabType + '-result';
			var html = '';

			if (data.length === 0) {
				html = '<div class="alert alert-warning">Tidak ada data untuk parameter yang dipilih</div>';
			} else {
				html = '<div class="table-responsive"><table class="table table-striped table-bordered" id="table-' + tabType + '"><thead><tr>';

				if (tabType === 'kodetg') {
					html += '<th>No Form</th><th>Tanggal</th><th>Sub</th><th>Kelompok</th><th>Saldo</th><th>Total</th>';
				} else if (tabType === 'non') {
					html += '<th>No Form</th><th>Tanggal</th><th>Sub</th><th>Kelompok</th><th>Saldo</th><th>Total</th>';
				} else if (tabType === 'busana') {
					html += '<th>No Form</th><th>Tanggal</th><th>Sub</th><th>Kelompok</th><th>Saldo</th><th>Total</th>';
				} else if (tabType === 'pusat') {
					html += '<th>No Form</th><th>Tanggal</th><th>Sub</th><th>Kelompok</th><th>Saldo</th><th>Total</th>';
				}
				html += '</tr></thead><tbody>';

				$.each(data, function(i, item) {
					html += '<tr>';
					if (tabType === 'kodetg') {
						html += '<td>' + item.NO_FORM + '</td><td>' + formatDate(item.TGL) + '</td><td>' + item.SUB + '</td><td>' + item.KELOMPOK +
							'</td><td class="text-right">' + formatNumber(item.SALDO) + '</td><td class="text-right">' + formatNumber(item.TOTAL) +
							'</td>';
					} else if (tabType === 'non') {
						html += '<td>' + item.NO_FORM + '</td><td>' + formatDate(item.TGL) + '</td><td>' + item.SUB + '</td><td>' + item.KELOMPOK +
							'</td><td class="text-right">' + formatNumber(item.SALDO) + '</td><td class="text-right">' + formatNumber(item.TOTAL) +
							'</td>';
					} else if (tabType === 'busana') {
						html += '<td>' + item.NO_FORM + '</td><td>' + formatDate(item.TGL) + '</td><td>' + item.SUB + '</td><td>' + item.KELOMPOK +
							'</td><td class="text-right">' + formatNumber(item.SALDO) + '</td><td class="text-right">' + formatNumber(item.TOTAL) +
							'</td>';
					} else if (tabType === 'pusat') {
						html += '<td>' + item.NO_FORM + '</td><td>' + formatDate(item.TGL) + '</td><td>' + item.SUB + '</td><td>' + item.KELOMPOK +
							'</td><td class="text-right">' + formatNumber(item.SALDO) + '</td><td class="text-right">' + formatNumber(item.TOTAL) +
							'</td>';
					}
					html += '</tr>';
				});

				html += '</tbody></table></div>';
			}

			$(targetDiv).html(html);

			if (data.length > 0) {
				$('#table-' + tabType).DataTable({
					pageLength: 25,
					searching: true,
					ordering: true,
					responsive: true,
					// scrollX:true,
					dom: 'Blfrtip',
					buttons: ['copy', 'excel', 'csv', 'pdf', 'print'],
					language: {
						url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
					}
				});
			}
		}

		// -------------------------------
		// Helper Format
		// -------------------------------
		function formatNumber(num) {
			return Number(num).toLocaleString('id-ID');
		}

		function formatDate(dateStr) {
			return dateStr ? new Date(dateStr).toLocaleDateString('id-ID') : '';
		}

		function resetFilter(tabType) {
			switch (tabType) {
				case 'kodetg':
					$('#cbg_kodetg').val('');
					$('#per_kodetg').val('');
					$('#cek_kodetg').prop('checked', false);
					break;
				case 'non':
					$('#cbg_non').val('');
					$('#per_non').val('');
					$('#cek_non').prop('checked', false);
					break;
				case 'busana':
					$('#cbg_busana').val('');
					$('#per_busana').val('');
					$('#cek_busana').prop('checked', false);
					break;
				case 'pusat':
					$('#cbg_pusat').val('');
					$('#per_pusat').val('');
					$('#cek_pusat').prop('checked', false);
					break;
			}

			// Kosongkan hasil tabel
			$('#' + tabType + '-result').html(
				'<div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>Silakan Klik Filter untuk menampilkan data.</div>');

			// Jika tabel DataTable sebelumnya sudah diinisialisasi, destroy dulu
			var tableId = '#table-' + tabType;
			if ($.fn.DataTable.isDataTable(tableId)) {
				$(tableId).DataTable().destroy();
			}

			// Tampilkan SweetAlert info
			Swal.fire({
				icon: 'info',
				title: 'Filter Direset',
				text: 'Filter telah dikosongkan',
				timer: 1500,
				showConfirmButton: false
			});
		}

		function cetakInventaris(tab) {
			let cbg = '',
				per = '',
				cek = '';

			switch (tab) {
				case 'kodetg':
					cbg = $('#cbg_kodetg').val();
					per = $('#per_kodetg').val();
					cek = $('#cek_kodetg').is(':checked') ? 1 : 0;
					break;
				case 'non':
					cbg = $('#cbg_non').val();
					per = $('#per_non').val();
					cek = $('#cek_non').is(':checked') ? 1 : 0;
					break;
				case 'busana':
					cbg = $('#cbg_busana').val();
					per = $('#per_busana').val();
					cek = $('#cek_busana').is(':checked') ? 1 : 0;
					break;
				case 'pusat':
					cbg = $('#cbg_pusat').val();
					per = $('#per_pusat').val();
					cek = $('#cek_pusat').is(':checked') ? 1 : 0;
					break;
			}

			// Validasi: Jika tidak ada data yang dipilih, beri peringatan
			if (!cbg && !per) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian!',
					text: 'Anda belum memilih filter. Cetak akan menampilkan semua data. Lanjutkan?',
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Ya, Lanjutkan',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						submitCetakForm(tab, cbg, per, cek);
					}
				});
			} else {
				submitCetakForm(tab, cbg, per, cek);
			}
		}

		function submitCetakForm(tab, cbg, per, cek) {
			// POST ke Jasper (new tab)
			let form = $('<form>', {
				method: 'POST',
				action: '{{ route('jasper-inventaris-report') }}',
				target: '_blank'
			});

			form.append($('<input>', {
				name: '_token',
				value: '{{ csrf_token() }}',
				type: 'hidden'
			}));
			form.append($('<input>', {
				name: 'tab',
				value: tab,
				type: 'hidden'
			}));
			form.append($('<input>', {
				name: 'cbg',
				value: cbg,
				type: 'hidden'
			}));
			form.append($('<input>', {
				name: 'per',
				value: per,
				type: 'hidden'
			}));
			form.append($('<input>', {
				name: 'cek',
				value: cek,
				type: 'hidden'
			}));

			$('body').append(form);
			form.submit();
			form.remove();

			// Tampilkan loading toast
			Swal.fire({
				icon: 'info',
				title: 'Memproses...',
				text: 'Sedang membuat laporan PDF',
				timer: 2000,
				showConfirmButton: false,
				timerProgressBar: true
			});
		}
	</script>
@endsection
