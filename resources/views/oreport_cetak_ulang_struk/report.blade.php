@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Cetak Ulang Struk</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Cetak Ulang Struk</li>
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
								<form method="POST" action="{{ route('jasper-cetakulangstruk-report') }}" id="reportForm">
									@csrf

									<div class="form-group">
										<!-- Filter Row -->
										<div class="row align-items-end mb-3">
											<div class="col-2">
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
												<label for="periode">Periode</label>
												<input type="text" name="periode" id="periode" class="form-control" placeholder="MM-YYYY"
													value="{{ session()->get('filter_per') ?? date('m-Y') }}">
											</div>
											<div class="col-2">
												<label for="tanggal">Tanggal</label>
												<input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ request('tanggal') }}">
											</div>
											<div class="col-2">
												<label for="no_bukti">No Bukti</label>
												<input type="text" name="no_bukti" id="no_bukti" class="form-control" placeholder="No Bukti" value="{{ request('no_bukti') }}">
											</div>
											<div class="col-2">
												<label for="nama_kasir">Nama Kasir</label>
												<input type="text" name="nama_kasir" id="nama_kasir" class="form-control" placeholder="Nama Kasir" value="{{ request('nama_kasir') }}">
											</div>
											<div class="col-2">
												<label for="kod_kasir">Kode Kasir</label>
												<input type="text" name="kod_kasir" id="kod_kasir" class="form-control" placeholder="Kode Kasir" value="{{ request('kod_kasir') }}">
											</div>
										</div>

										<!-- Button Row -->
										<div class="row mb-3">
											<div class="col-12">
												<button class="btn btn-primary mr-1" type="button" id="btnFilter" onclick="filterTransaksi()">
													<i class="fas fa-search mr-1"></i>Filter
												</button>
												<button class="btn btn-danger mr-1" type="button" onclick="resetFilter()">
													<i class="fas fa-redo mr-1"></i>Reset
												</button>
												<button class="btn btn-warning mr-1" type="submit" name="cetak_laporan" formtarget="_blank">
													<i class="fas fa-print mr-1"></i>Cetak Laporan
												</button>
												<button class="btn btn-success mr-1" type="button" onclick="exportData('excel')">
													<i class="fas fa-file-excel mr-1"></i>Export Excel
												</button>
											</div>
										</div>

										<!-- Data Table -->
										<div class="report-content col-md-12">
											@if (count($hasilTransaksi) > 0)
												<div class="table-responsive">
													<table class="table-hover table-striped table-bordered compact table" id="transaksi-table">
														<thead>
															<tr>
																<th>No Bukti</th>
																<th>Tanggal</th>
																<th>Total Belanja</th>
																<th>Nama Kasir</th>
																<th>Nama Customer</th>
																<th>Waktu</th>
																<th>Shift</th>
																<th>Aksi</th>
															</tr>
														</thead>
														<tbody>
															@foreach ($hasilTransaksi as $transaksi)
																<tr>
																	<td>{{ $transaksi->no_bukti ?? '' }}</td>
																	<td>{{ $transaksi->TGL ?? '' }}</td>
																	<td class="text-right">{{ number_format($transaksi->totals ?? 0, 0, ',', '.') }}</td>
																	<td>{{ $transaksi->USRNM ?? '' }}</td>
																	<td>{{ $transaksi->namaC ?? '' }}</td>
																	<td>{{ $transaksi->Waktu ?? '' }}</td>
																	<td>{{ $transaksi->SHIFT ?? '' }}</td>
																	<td class="text-center">
																		<div class="btn-group btn-group-sm">
																			<button class="btn btn-info btn-xs" title="Detail" onclick="showDetail('{{ $transaksi->no_bukti }}')">
																				<i class="fas fa-eye"></i>
																			</button>
																			<button class="btn btn-success btn-xs" title="Cetak Struk" onclick="printStruk('{{ $transaksi->no_bukti }}')">
																				<i class="fas fa-print"></i>
																			</button>
																			<button class="btn btn-warning btn-xs" title="Thermal Print" onclick="thermalPrint('{{ $transaksi->no_bukti }}')">
																				<i class="fas fa-receipt"></i>
																			</button>
																		</div>
																	</td>
																</tr>
															@endforeach
														</tbody>
													</table>
												</div>
											@else
												<div class="alert alert-info">
													<i class="fas fa-info-circle mr-2"></i>
													Silakan pilih cabang dan filter lainnya untuk menampilkan data transaksi.
												</div>
											@endif
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

	<!-- Modal Detail Transaksi -->
	<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="detailModalLabel">Detail Transaksi</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" id="detailContent">
					<div class="text-center">
						<i class="fas fa-spinner fa-spin"></i> Loading...
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal Thermal Print -->
	<div class="modal fade" id="thermalModal" tabindex="-1" role="dialog" aria-labelledby="thermalModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="thermalModalLabel">Thermal Print Preview</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" id="thermalContent">
					<div class="text-center">
						<i class="fas fa-spinner fa-spin"></i> Loading...
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" onclick="printThermalContent()">
						<i class="fas fa-print mr-1"></i>Print
					</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('javascripts')
	<script>
		$(document).ready(function() {
			// Initialize DataTable if data exists
			@if (count($hasilTransaksi) > 0)
				$('#transaksi-table').DataTable({
					pageLength: 25,
					searching: true,
					ordering: true,
					responsive: true,
					dom: 'Blfrtip',
					buttons: [{
						extend: 'collection',
						text: 'Export',
						buttons: ['copy', 'excel', 'csv', 'pdf', 'print']
					}],
					columnDefs: [{
							className: 'dt-right',
							targets: [2]
						}, // Total Belanja column
						{
							className: 'dt-center',
							targets: [7]
						} // Aksi column
					],
					order: [
						[1, 'desc']
					] // Sort by tanggal descending
				});
			@endif

			// Auto-format periode input
			$('#periode').on('input', function() {
				var value = this.value.replace(/\D/g, ''); // Remove non-digits
				if (value.length >= 2) {
					this.value = value.substring(0, 2) + '-' + value.substring(2, 6);
				}
			});
		});

		// Filter Transaksi Function
		function filterTransaksi() {
			var cbg = $('#cbg').val();

			if (!cbg) {
				alert('Pilih cabang terlebih dahulu');
				return;
			}

			// Show loading
			$('#btnFilter').html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
			$('#btnFilter').prop('disabled', true);

			// Build query parameters
			var params = new URLSearchParams();
			params.append('cbg', cbg);

			if ($('#periode').val()) params.append('periode', $('#periode').val());
			if ($('#tanggal').val()) params.append('tanggal', $('#tanggal').val());
			if ($('#no_bukti').val()) params.append('no_bukti', $('#no_bukti').val());
			if ($('#nama_kasir').val()) params.append('nama_kasir', $('#nama_kasir').val());
			if ($('#kod_kasir').val()) params.append('kod_kasir', $('#kod_kasir').val());

			// Redirect with parameters
			window.location.href = '{{ route('get-cetakulangstruk-report') }}?' + params.toString();
		}

		// Reset Filter Function
		function resetFilter() {
			$('#cbg').val('');
			$('#periode').val('{{ date('m-Y') }}');
			$('#tanggal').val('');
			$('#no_bukti').val('');
			$('#nama_kasir').val('');
			$('#kod_kasir').val('');
			window.location.href = '{{ route('rcetakulangstruk') }}';
		}

		// Show Detail Function
		function showDetail(noBukti) {
			$('#detailModal').modal('show');
			$('#detailContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

			$.ajax({
				url: '{{ url('api-get-detail-transaksi') }}',
				method: 'GET',
				data: {
					no_bukti: noBukti,
					mm: '{{ session()->get('filter_mm') }}'
				},
				success: function(response) {
					if (response.success) {
						var html = buildDetailHtml(response.data);
						$('#detailContent').html(html);
					} else {
						$('#detailContent').html('<div class="alert alert-danger">Error: ' + response.error + '</div>');
					}
				},
				error: function(xhr) {
					$('#detailContent').html('<div class="alert alert-danger">Terjadi kesalahan saat mengambil data</div>');
				}
			});
		}

		// Build Detail HTML
		function buildDetailHtml(data) {
			var html = '<div class="row">';

			// Header info
			if (data.header && data.header.length > 0) {
				html += '<div class="col-12 mb-3">';
				html += '<h6>Informasi Transaksi</h6>';
				html += '<table class="table table-sm table-bordered">';
				html += '<tr><td><strong>No Bukti:</strong></td><td>' + (data.header[0].no_bukti || '') + '</td></tr>';
				html += '<tr><td><strong>Kasir:</strong></td><td>' + (data.header[0].USRNM || '') + '</td></tr>';
				html += '</table>';
				html += '</div>';
			}

			// Detail barang
			if (data.barang && data.barang.length > 0) {
				html += '<div class="col-12 mb-3">';
				html += '<h6>Detail Barang</h6>';
				html += '<div class="table-responsive">';
				html += '<table class="table table-sm table-striped table-bordered">';
				html += '<thead><tr><th>Kode</th><th>Nama Barang</th><th>Harga</th><th>Qty</th><th>Diskon</th><th>Total</th></tr></thead>';
				html += '<tbody>';

				data.barang.forEach(function(item) {
					html += '<tr>';
					html += '<td>' + (item.KD_BRG || '') + '</td>';
					html += '<td>' + (item.NA_BRG || '') + '</td>';
					html += '<td class="text-right">' + formatNumber(item.harga || 0) + '</td>';
					html += '<td class="text-right">' + formatNumber(item.qty || 0) + '</td>';
					html += '<td class="text-right">' + formatNumber(item.diskon || 0) + '</td>';
					html += '<td class="text-right">' + formatNumber(item.total || 0) + '</td>';
					html += '</tr>';
				});

				html += '</tbody></table>';
				html += '</div>';
				html += '</div>';
			}

			// Payment info
			if (data.bayar && data.bayar.length > 0) {
				html += '<div class="col-12 mb-3">';
				html += '<h6>Informasi Pembayaran</h6>';
				html += '<table class="table table-sm table-bordered">';

				var payment = data.bayar[0];
				html += '<tr><td><strong>Total:</strong></td><td class="text-right">' + formatNumber(payment.totals || 0) + '</td></tr>';
				html += '<tr><td><strong>Bayar:</strong></td><td class="text-right">' + formatNumber(payment.bayar || 0) + '</td></tr>';
				html += '<tr><td><strong>Kembali:</strong></td><td class="text-right">' + formatNumber(payment.kembali || 0) + '</td></tr>';

				if (data.payment_type === 'non_cash' && payment.TYPE) {
					html += '<tr><td><strong>Jenis Bayar:</strong></td><td>' + payment.TYPE + '</td></tr>';
				}

				html += '</table>';
				html += '</div>';
			}

			html += '</div>';
			return html;
		}

		// Print Struk Function
		function printStruk(noBukti) {
			var cbg = $('#cbg').val();
			var periode = $('#periode').val();

			if (!cbg) {
				alert('Pilih cabang terlebih dahulu');
				return;
			}

			// Open print window
			var url = '{{ route('jasper-cetakulangstruk-report') }}?cbg=' + cbg + '&periode=' + periode + '&no_bukti=' + noBukti;
			window.open(url, '_blank');
		}

		// Thermal Print Function
		function thermalPrint(noBukti) {
			$('#thermalModal').modal('show');
			$('#thermalContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

			$.ajax({
				url: '{{ url('api-get-thermal-print') }}',
				method: 'GET',
				data: {
					no_bukti: noBukti,
					mm: '{{ session()->get('filter_mm') }}',
					cbg: $('#cbg').val()
				},
				success: function(response) {
					if (response.success) {
						var html = buildThermalHtml(response.data);
						$('#thermalContent').html(html);
					} else {
						$('#thermalContent').html('<div class="alert alert-danger">Error: ' + response.error + '</div>');
					}
				},
				error: function(xhr) {
					$('#thermalContent').html('<div class="alert alert-danger">Terjadi kesalahan saat mengambil data thermal</div>');
				}
			});
		}

		// Build Thermal HTML
		function buildThermalHtml(data) {
			var html = '<div class="thermal-preview" style="font-family: monospace; background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6;">';

			if (data.main && data.main.length > 0) {
				var main = data.main[0];

				// Header toko
				html += '<div class="text-center mb-2">';
				html += '<strong>' + (main.NAMA_TOKO || 'NAMA TOKO') + '</strong><br>';
				html += (main.ALAMAT || 'ALAMAT TOKO') + '<br>';
				html += '================================<br>';
				html += '</div>';

				// Info transaksi
				html += '<div class="mb-2">';
				html += 'No: ' + (main.no_bukti || '') + '<br>';
				html += 'Kasir: ' + (main.USRNM || '') + '<br>';
				html += 'Customer: ' + (main.namac || '') + '<br>';
				html += 'Waktu: ' + (main.waktu || '') + '<br>';
				html += '================================<br>';
				html += '</div>';

				// Detail barang
				html += '<div class="mb-2">';
				data.main.forEach(function(item) {
					if (item.total != 0) {
						html += item.NA_BRG + '<br>';
						html += item.qty + ' x ' + formatNumber(item.harga) + ' = ' + formatNumber(item.total) + '<br>';
						if (item.diskon > 0) {
							html += 'Diskon: ' + formatNumber(item.diskon) + '<br>';
						}
					}
				});
				html += '================================<br>';
				html += '</div>';

				// Total
				html += '<div class="mb-2">';
				html += 'Subtotal: ' + formatNumber(main.totals || 0) + '<br>';
				html += 'Total: ' + formatNumber(main.totala || 0) + '<br>';
				html += 'Bayar: ' + formatNumber(main.bayar || 0) + '<br>';
				html += 'Kembali: ' + formatNumber(main.kembali || 0) + '<br>';
				html += '================================<br>';
				html += '</div>';
			}

			// Greeting
			if (data.greet && data.greet.length > 0) {
				html += '<div class="text-center">';
				data.greet.forEach(function(greet) {
					html += (greet.kata || '') + '<br>';
				});
				html += '</div>';
			}

			html += '</div>';
			return html;
		}

		// Print Thermal Content
		function printThermalContent() {
			var printContents = $('#thermalContent').html();
			var originalContents = document.body.innerHTML;
			document.body.innerHTML = printContents;
			window.print();
			document.body.innerHTML = originalContents;
		}

		// Export functions
		function exportData(format) {
			if (typeof $('#transaksi-table').DataTable !== 'undefined') {
				var table = $('#transaksi-table').DataTable();
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

		// Helper function to format numbers
		function formatNumber(num) {
			return Number(num).toLocaleString('id-ID');
		}

		// Form validation
		$('#reportForm').on('submit', function(e) {
			var cbg = $('#cbg').val();

			if (!cbg) {
				e.preventDefault();
				alert('Cabang harus dipilih');
			}
		});

		// Enter key handler for inputs
		$('#no_bukti, #nama_kasir, #kod_kasir').on('keypress', function(e) {
			if (e.which === 13) { // Enter key
				filterTransaksi();
			}
		});
	</script>
@endsection
