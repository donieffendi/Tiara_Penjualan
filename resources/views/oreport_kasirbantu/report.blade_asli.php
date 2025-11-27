@extends('layouts.plain')

@section('styles')
<link rel="stylesheet" href="{{url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
{{-- <link rel="stylesheet" href="{{url('https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css') }}"> --}}

@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Kasir Bantu</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Kasir Bantu</li>
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
								@if (isset($error))
									<div class="alert alert-danger alert-dismissible">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Error:</strong> {{ $error }}
									</div>
								@endif

								<form method="POST" action="{{ url('jasper-kasirbantu-report') }}" id="reportForm">
									@csrf
									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="detail-tab" data-toggle="tab" href="#detail" role="tab" aria-controls="detail" aria-selected="true">
												<i class="fas fa-list-alt mr-1"></i>All
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="summary-tab" data-toggle="tab" href="#summary" role="tab" aria-controls="summary" aria-selected="false">
												<i class="fas fa-chart-bar mr-1"></i>Per Barang
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="kasir-tab" data-toggle="tab" href="#kasir" role="tab" aria-controls="kasir" aria-selected="false">
												<i class="fas fa-user mr-1"></i>Per Nota
											</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Detail Transaksi Tab -->
										<div class="tab-pane fade show active" id="detail" role="tabpanel" aria-labelledby="detail-tab">
											<div class="pt-3">
												<div class="form-group">
													<!-- Search Filter Row -->
													<div class="row align-items-end mb-3">
														<div class="col-6">
															<button class="btn btn-primary mr-1" type="button" id="btnFilterDetail" onclick="filterKasirBantu('detail')">
																<i class="fas fa-search mr-1"></i>Filter
															</button>
															<button class="btn btn-danger mr-1" type="button" onclick="resetFilter('detail')">
																<i class="fas fa-redo mr-1"></i>Reset
															</button>
															<button class="btn btn-warning mr-1" type="submit" name="cetak_detail" formtarget="_blank">
																<i class="fas fa-print mr-1"></i>Cetak
															</button>
														</div>
													</div>

													<!-- Data Table Detail -->
													<div class="col-md-12 report-content">
														@if (!empty($hasilKasirBantu))
															<div class="table-responsive">
																<table id="tabelDetail" class="table table-striped table-bordered nowrap" style="width:100%">
																	<thead>
																		<tr>
																			<th>No Kitir</th>
																			<th>Tanggal</th>
																			<th>Subitem</th>
																			<th>Nama Barang</th>
																			<th>Qty</th>
																			<th>Cabang</th>
																		</tr>
																	</thead>
																	<tbody>
																		@foreach ($hasilKasirBantu as $item)
																			<tr>
																				<td>{{ $item->NO_BUKTI ?? '' }}</td>
																				<td>{{ isset($item->TGL) ? date('d/m/Y', strtotime($item->TGL)) : '' }}</td>
																				<td>{{ $item->KD_BRG ?? '' }}</td>
																				<td>{{ $item->NA_BRG ?? '' }}</td>
																				<td class="text-right">{{ number_format($item->QTY ?? 0, 0, ',', '.') }}</td>
																				<td>{{ $item->CBG ?? '' }}</td>
																			</tr>
																		@endforeach
																	</tbody>
																</table>
															</div>
														@else
															<div class="alert alert-info">
																<i class="fas fa-info-circle mr-2"></i>
																Silakan Klik Filter untuk menampilkan ringkasan barang.
															</div>
														@endif
													</div>
												</div>
											</div>
										</div>

										<!-- Summary Barang Tab -->
										<div class="tab-pane fade" id="summary" role="tabpanel" aria-labelledby="summary-tab">
											<div class="pt-3">
												<div class="form-group">
													<div class="row align-items-end mb-3">
														<div class="col-6">
															<button class="btn btn-primary mr-1" type="button" id="btnFilterSummary" onclick="filterKasirBantu('summary')">
																<i class="fas fa-search mr-1"></i>Filter
															</button>
															<button class="btn btn-danger mr-1" type="button" onclick="resetFilter('summary')">
																<i class="fas fa-redo mr-1"></i>Reset
															</button>
															<button class="btn btn-warning mr-1" type="submit" name="cetak_summary" formtarget="_blank">
																<i class="fas fa-print mr-1"></i>Cetak
															</button>
														</div>
													</div>

													<div class="col-md-12 report-content">
														@if (!empty($hasilKasirBantu))
															<div class="table-responsive">
																<table id="tabelSummary" class="table table-striped table-bordered nowrap" style="width:100%">
																	<thead>
																		<tr>
																			<th>Sub item</th>
																			<th>Nama Barang</th>
																			<th>Qty</th>
																			<th>Cabang</th>
																		</tr>
																	</thead>
																	<tbody>
																		@foreach ($hasilKasirBantu as $item)
																			<tr>
																				<td>{{ $item->KD_BRG ?? '' }}</td>
																				<td>{{ $item->NA_BRG ?? '' }}</td>
																				<td class="text-right">{{ number_format($item->QTY ?? 0, 0, ',', '.') }}</td>
																				<td>{{ $item->CBG ?? '' }}</td>
																			</tr>
																		@endforeach
																	</tbody>
																</table>
															</div>
														@else
															<div class="alert alert-info">
																<i class="fas fa-info-circle mr-2"></i>
																Silakan Klik Filter untuk menampilkan ringkasan barang.
															</div>
														@endif
													</div>
												</div>
											</div>
										</div>

										<!-- Data Kasir Tab -->
										<div class="tab-pane fade" id="kasir" role="tabpanel" aria-labelledby="kasir-tab">
											<div class="pt-3">
												<div class="form-group">
													<div class="row align-items-end mb-3">
														<div class="col-2">
															<label for="cbg_kasir">Cabang</label>
															<select name="cbg_kasir" id="cbg_kasir" class="form-control" required>
																<option value="">Pilih Cabang</option>
																@foreach ($cbg as $cabang)
																	<option value="{{ $cabang->KODE }}">{{ $cabang->KODE }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-8">
															<button class="btn btn-primary mr-1" type="button" id="btnFilterKasir" onclick="filterKasirBantu('kasir')">
																<i class="fas fa-search mr-1"></i>Filter
															</button>
															<button class="btn btn-danger mr-1" type="button" onclick="resetFilter('kasir')">
																<i class="fas fa-redo mr-1"></i>Reset
															</button>
															<button class="btn btn-warning mr-1" type="button" onclick="cetakKasir()">
																<i class="fas fa-print mr-1"></i>Cetak
															</button>
														</div>
													</div>

													<div class="report-content col-md-12" id="kasir-result">
														<div class="alert alert-info">
															<i class="fas fa-info-circle mr-2"></i>
															Silakan pilih cabang dan periode untuk menampilkan data kasir.
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

	<!-- Modal Summary -->
	<div class="modal fade" id="summaryModal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Summary Kasir Bantu</h5>
					<button type="button" class="close" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<div class="modal-body" id="summaryContent">
					<div class="text-center">
						<i class="fas fa-spinner fa-spin"></i> Loading...
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script src="{{url('AdminLTE/plugins/datatables/jquery.dataTables.js') }}"></script>
	<script src="{{url('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>
	<script src="{{url('http://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js') }}"></script>
	<!-- Buttons dan Export -->
	<script src="{{url('https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js') }}"></script>
	<script src="{{url('https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js') }}"></script>
	<script src="{{url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
				localStorage.setItem('activeKasirBantuTab', $(e.target).attr('href'));
			});

			// Restore active tab from localStorage
			var activeTab = localStorage.getItem('activeKasirBantuTab');
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
			$('#periode_detail, #periode_summary, #periode_kasir').on('input', function() {
				var value = this.value.replace(/\D/g, ''); // Remove non-digits
				if (value.length >= 2) {
					this.value = value.substring(0, 2) + '-' + value.substring(2, 6);
				}
			});

			// Initialize DataTable if data exists
			@if (!empty($hasilKasirBantu))
				$('#tabelDetail').DataTable({
					pageLength: 25,
					searching: true,
					ordering: true,
					responsive: true,
					// scrollX: true,
					dom: "<'row'<'col-md-6'><'col-md-6'>>" +
						"<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
						"<'row'<'col-md-12'B>>" +
						"<'row'<'col-md-12'tr>>" +
						"<'row'<'col-md-5'i><'col-md-7'p>>",
					buttons: [
						{
							extend: 'excelHtml5',
							title: 'Data Galeri'
						}
					],
					columnDefs: [{
							className: 'dt-right',
							targets: [4]
						} // Qty column
					],
					language: {
						url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
					}
				});
			@endif
		});

		// Filter Kasir Bantu Function
		function filterKasirBantu(tabType) {
			var cbg, btnId;

			switch (tabType) {
				case 'detail':
					cbg = "";
					btnId = '#btnFilterDetail';
					break;
				case 'summary':
					cbg = "";
					btnId = '#btnFilterSummary'; // Use same button for loading
					break;
				case 'kasir':
					cbg = $('#cbg_kasir').val();
					btnId = '#btnFilterKasir';
					break;
				default:
					return;
			}

			// if (!cbg) {
			// 	alert('Pilih cabang terlebih dahulu');
			// 	return;
			// }

			if (tabType === 'kasir' && !cbg) {
				alert('Pilih cabang terlebih dahulu');
				return;
			}

			// Show loading
			$(btnId).html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
			$(btnId).prop('disabled', true);

			if (tabType === 'detail') {
				// Redirect to get report for detail tab
				window.location.href = '{{ route('get-kasirbantu-report') }}?' +
					'cbg=' + encodeURIComponent(cbg);
			} else {
				// AJAX request for other tabs
				const tabType = $('.nav-link.active').data('tab'); 

				$.ajax({
					url: '{{ route('get-kasirbantu-report') }}/ajax',
					method: 'GET',
					data: {
						cbg: cbg,
						tab: tabType
					},
					success: function(response) {
						if (response.success) {
							displayTabData(tabType, response.data);
						} else {
							alert(response.message || 'Gagal memuat data');
						}
					},
					error: function(xhr) {
						console.error('Error:', xhr);
						alert('Terjadi kesalahan saat memuat data');
					},
					complete: function() {
						$(btnId).html('<i class="fas fa-search mr-1"></i>Filter');
						$(btnId).prop('disabled', false);
					}
				});
			}
		}

		// Display data for different tabs
		function displayTabData(tabType, data) {
			var targetDiv = '#' + tabType + '-result';
			var html = '';

			if (data.length === 0) {
				html = '<div class="alert alert-warning">Tidak ada data untuk parameter yang dipilih</div>';
			} else {
				html = '<div class="table-responsive"><table class="table table-striped table-bordered compact" id="table-' + tabType + '"><thead><tr>';

				if (tabType === 'summary') {
					html += '<th>Subitem</th><th>Nama Barang</th><th>Qty</th><th>Cabang</th>';
					html += '</tr></thead><tbody>';

					$.each(data, function(i, item) {
						html += '<tr>';
						html += '<td>' + (item.KODES || '') + '</td>';
						html += '<td>' + (item.NAMA_KASIR || '') + '</td>';
						html += '<td class="text-right">' + formatNumber(item.TOTAL_TRANSAKSI || 0) + '</td>';
						html += '<td>' + (item.CBG || '') + '</td>';
						html += '</tr>';
					});
				} else if (tabType === 'kasir') {
					html += '<th>Kitir</th><th>Tanggal</th><th>Cabang</th>';
					html += '</tr></thead><tbody>';

					$.each(data, function(i, item) {
						html += '<tr>';
						html += '<td>' + (item.KODES || '') + '</td>';
						html += '<td>' + (item.TGL_INPUT ? formatDate(item.TGL_INPUT) : '') + '</td>';
						html += '<td>' + (item.CBG || '') + '</td>';
						html += '</tr>';
					});
				}

				html += '</tbody></table></div>';
			}

			$(targetDiv).html(html);

			// Initialize DataTable for the new table
			if (data.length > 0) {
				$('#table-' + tabType).DataTable({
					pageLength: 25,
					searching: true,
					ordering: true,
					responsive: true,
					scrollX: true,
					dom: 'Blfrtip',
					buttons: ['copy', 'excel', 'csv', 'pdf', 'print'],
					language: {
						url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
					}
				});
			}
		}

		// Reset Filter Function
		function resetFilter(tabType) {
			switch (tabType) {
				case 'detail':
					// $('#cbg_detail').val('');
					
					// $('#periode_detail').val('{{ date('m-Y') }}');
					break;
				case 'summary':
					// $('#cbg_summary').val('');
					
					// $('#periode_summary').val('{{ date('m-Y') }}');
					$('#summary-result').html(
						'<div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>Silakan pilih cabang dan periode untuk menampilkan ringkasan barang.</div>'
						);
					break;
				case 'kasir':
					$('#cbg_kasir').val('');
					// $('#periode_kasir').val('{{ date('m-Y') }}');
					$('#kasir-result').html(
						'<div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>Silakan pilih cabang dan periode untuk menampilkan data kasir.</div>'
						);
					break;
			}

			if (tabType === 'detail') {
				window.location.href = '{{ route('rkasirbantu') }}';
			}
		}

		// Export functions for different formats
		function exportData(format, tabType) {
			var tableId = '';

			if (tabType === 'detail' && typeof window.dataTable !== 'undefined') {
				tableId = '#tabelDetail';
			} else {
				tableId = '#table-' + tabType;
			}

			if ($(tableId).length && $.fn.DataTable.isDataTable(tableId)) {
				var table = $(tableId).DataTable();
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

		// Print functions
		function cetakSummary() {
			var cbg = $('#cbg_summary').val();
			// var periode = $('#periode_summary').val();
			

			if (!cbg || !periode) {
				alert('Cabang dan periode harus diisi untuk cetak summary');
				return;
			}

			var url = '{{ url('jasper-kasirbantu-report') }}?cetak_summary=1&cbg=' + cbg;
			window.open(url, '_blank');
		}

		function cetakKasir() {
			var cbg = $('#cbg_kasir').val();
			var periode = $('#periode_kasir').val();

			if (!cbg || !periode) {
				alert('Cabang dan periode harus diisi untuk cetak data kasir');
				return;
			}

			var url = '{{ url('jasper-kasirbantu-report') }}?cetak_kasir=1&cbg=' + cbg + '&periode=' + periode;
			window.open(url, '_blank');
		}

		// Get Summary Function
		function getSummary() {
			var cbg = $('#cbg_detail').val();
			var periode = $('#periode_detail').val();

			if (!cbg) {
				alert('Pilih cabang terlebih dahulu');
				return;
			}

			$('#summaryModal').modal('show');
			$('#summaryContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading summary...</div>');

			$.ajax({
				url: '{{ route('get-kasirbantu-report') }}/summary',
				method: 'GET',
				data: {
					cbg: cbg,
					periode: periode || ''
				},
				success: function(response) {
					var html = '<div class="row">';
					html += '<div class="col-md-6"><div class="card"><div class="card-body text-center"><h3>' + formatNumber(response
						.TOTAL_KASIR) + '</h3><p>Total Kasir</p></div></div></div>';
					html += '<div class="col-md-6"><div class="card"><div class="card-body text-center"><h3>' + formatNumber(response
						.TOTAL_TRANSAKSI) + '</h3><p>Total Transaksi</p></div></div></div>';
					html += '<div class="col-md-6"><div class="card"><div class="card-body text-center"><h3>Rp ' + formatNumber(response
						.TOTAL_AMOUNT) + '</h3><p>Total Amount</p></div></div></div>';
					html += '<div class="col-md-6"><div class="card"><div class="card-body text-center"><h3>Rp ' + formatNumber(response
						.AVG_AMOUNT) + '</h3><p>Rata-rata Amount</p></div></div></div>';
					html += '</div>';

					$('#summaryContent').html(html);
				},
				error: function() {
					$('#summaryContent').html('<div class="alert alert-danger">Error loading summary data</div>');
				}
			});
		}

		// Helper function to format numbers
		function formatNumber(num) {
			return Number(num).toLocaleString('id-ID');
		}

		// Helper function to format dates
		function formatDate(dateString) {
			if (!dateString) return '';
			var date = new Date(dateString);
			return date.toLocaleDateString('id-ID');
		}

		// Form validation
		$('#reportForm').on('submit', function(e) {
			var activeTab = $('.nav-link.active').attr('href');

			if (activeTab === '#detail') {
				var cbg = $('#cbg_detail').val();

				if (!cbg) {
					e.preventDefault();
					alert('Cabang harus diisi');
				}
			}
		});
	</script>
@endsection
