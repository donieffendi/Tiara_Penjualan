@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $judul ?? 'Transaksi' }} - Edit</h1>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<form id="form-lbtat">
					@csrf
					<input type="hidden" name="no_bukti" value="{{ $header->no_bukti ?? '' }}">
					<input type="hidden" name="route_name" value="{{ $route_name ?? '' }}">

					<div class="card">
						<div class="card-header">
							<div class="row">
								<div class="col-md-12">
									<button type="button" class="btn btn-sm btn-success" id="btn-proses">
										<i class="fas fa-check"></i> Proses
									</button>
									<button type="button" class="btn btn-sm btn-warning" id="btn-hapus-positif">
										<i class="fas fa-trash"></i> Hapus Positif
									</button>
									<button type="button" class="btn btn-sm btn-info" id="btn-hapus-nol">
										<i class="fas fa-trash"></i> Hapus Nol
									</button>
									<button type="button" class="btn btn-sm btn-secondary" id="btn-hapus-negatif">
										<i class="fas fa-trash"></i> Hapus Negatif
									</button>
									<button type="button" class="btn btn-sm btn-primary" id="btn-check-all">
										<i class="fas fa-check-square"></i> Check All
									</button>
									<button type="button" class="btn btn-sm btn-dark" id="btn-uncheck-all">
										<i class="fas fa-square"></i> Uncheck All
									</button>
									@if ($route_name == 'tprosesstockopname')
										<button type="button" class="btn btn-sm btn-success" id="btn-export">
											<i class="fas fa-download"></i> Export SO
										</button>
										<button type="button" class="btn btn-sm btn-primary" id="btn-import">
											<i class="fas fa-upload"></i> Import SO
										</button>
									@endif
									<button type="button" class="btn btn-sm btn-info" id="btn-print">
										<i class="fas fa-print"></i> Print
									</button>
									<a href="#" id="btn-exit" class="btn btn-sm btn-danger">
										<i class="fas fa-times"></i> Exit
									</a>
								</div>
							</div>
						</div>

						<div class="card-body">
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label>No Bukti</label>
										<input type="text" class="form-control form-control-sm" value="{{ $header->no_bukti ?? '' }}" readonly
											style="background-color: #e9ecef; font-weight: bold;">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Tanggal</label>
										<input type="date" class="form-control form-control-sm" name="tgl" value="{{ $header->tgl ?? date('Y-m-d') }}">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label>Notes</label>
										<input type="text" class="form-control form-control-sm" name="notes" value="{{ $header->notes ?? '' }}" placeholder="Keterangan">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>
											<input type="checkbox" id="chk-pertahankan"> Pertahankan Data
										</label>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-2">
									<div class="form-group">
										<label>Sub</label>
										<input type="text" class="form-control form-control-sm" id="filter-sub" placeholder="Filter Sub">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Item s/d</label>
										<div class="input-group input-group-sm">
											<input type="text" class="form-control" id="filter-item-dari" placeholder="Dari">
											<input type="text" class="form-control" id="filter-item-sd" placeholder="s/d">
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Kdlaku</label>
										<select class="form-control form-control-sm" id="filter-kdlaku">
											<option value="">Semua</option>
											<option value="1">1</option>
											<option value="2">2</option>
											<option value="3">3</option>
											<option value="4">4</option>
											<option value="5">5</option>
										</select>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>L/H dari s/d</label>
										<div class="input-group input-group-sm">
											<input type="number" class="form-control" id="filter-lh-dari" placeholder="Dari">
											<input type="number" class="form-control" id="filter-lh-sd" placeholder="s/d">
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Tidak Ada Trans (hari)</label>
										<input type="number" class="form-control form-control-sm" id="filter-hari" placeholder="Hari">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>&nbsp;</label>
										<button type="button" class="btn btn-primary btn-sm btn-block" id="btn-filter">
											<i class="fas fa-filter"></i> Filter
										</button>
									</div>
								</div>
							</div>

							<hr>

							<div class="table-responsive">
								<table class="table-bordered table-striped table-sm table" id="table-detail">
									<thead class="thead-dark">
										<tr>
											<th width="4%" class="text-center">No</th>
											<th width="10%" class="text-center">Kode</th>
											<th width="25%" class="text-center">Nama Barang</th>
											<th width="10%" class="text-center">Ukuran</th>
											<th width="8%" class="text-center">Stok</th>
											<th width="8%" class="text-center">Qty</th>
											<th width="8%" class="text-center">Selisih</th>
											<th width="20%" class="text-center">Ket</th>
											<th width="7%" class="text-center">Cek</th>
										</tr>
									</thead>
									<tbody id="tbody-detail">
										@if (!empty($detail) && count($detail) > 0)
											@foreach ($detail as $key => $row)
												<tr data-kd="{{ $row->kd_brg }}" data-sub="{{ substr($row->kd_brg, 0, 3) }}" data-kdlaku="{{ $row->kdlaku ?? '' }}">
													<td class="text-center">{{ $key + 1 }}</td>
													<td>
														<input type="hidden" name="details[{{ $key }}][kd_brg]" value="{{ $row->kd_brg }}">
														<input type="hidden" name="details[{{ $key }}][na_brg]" value="{{ $row->na_brg }}">
														{{ $row->kd_brg }}
													</td>
													<td>{{ $row->na_brg }}</td>
													<td class="text-center">{{ $row->ket_uk ?? '' }}</td>
													<td class="stok-system text-right">{{ number_format($row->stok_system ?? 0, 2) }}</td>
													<td>
														<input type="number" step="0.01" class="form-control form-control-sm qty-input text-right"
															name="details[{{ $key }}][qty]" value="{{ $row->qty ?? '' }}" data-stok="{{ $row->stok_system ?? 0 }}">
													</td>
													<td class="selisih text-right">
														{{ number_format(($row->qty ?? 0) - ($row->stok_system ?? 0), 2) }}
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="details[{{ $key }}][ket]" value="{{ $row->ket ?? '' }}">
													</td>
													<td class="text-center">
														<input type="checkbox" class="chk-item" name="details[{{ $key }}][cek]" value="1"
															{{ ($row->cek_status ?? 0) == 1 ? 'checked' : '' }}>
													</td>
												</tr>
											@endforeach
										@else
											<tr>
												<td colspan="9" class="text-center">Tidak ada data</td>
											</tr>
										@endif
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		// Safety check for SweetAlert2
		if (typeof Swal === 'undefined') {
			console.error('SweetAlert2 not loaded! Using alert fallback.');
			window.Swal = {
				fire: function(options) {
					if (typeof options === 'string') {
						alert(arguments[0] + '\n' + (arguments[1] || ''));
						return Promise.resolve({
							isConfirmed: true,
							value: true
						});
					}
					var msg = options.title ? options.title + '\n' : '';
					msg += options.text ? options.text : '';
					msg += options.html ? options.html : '';

					if (options.showCancelButton) {
						var result = confirm(msg);
						if (options.preConfirm && result) {
							return options.preConfirm().then(function(data) {
								return {
									isConfirmed: true,
									value: data
								};
							});
						}
						return Promise.resolve({
							isConfirmed: result,
							value: result
						});
					} else {
						alert(msg);
						return Promise.resolve({
							isConfirmed: true,
							value: true
						});
					}
				},
				showValidationMessage: function(msg) {
					console.log('Validation: ' + msg);
				},
				isLoading: function() {
					return false;
				}
			};
		}

		$(document).ready(function() {
			// Exit button
			$('#btn-exit').click(function(e) {
				e.preventDefault();
				var routeName = $('input[name="route_name"]').val();
				var backUrl = routeName == 'tprosesstockopname' ?
					"{{ route('tprosesstockopname') }}" :
					"{{ route('tpenangananlbtat') }}";
				window.location.href = backUrl;
			});

			// Calculate selisih on qty change
			$(document).on('input', '.qty-input', function() {
				var $row = $(this).closest('tr');
				var qty = parseFloat($(this).val()) || 0;
				var stok = parseFloat($(this).data('stok')) || 0;
				var selisih = qty - stok;

				$row.find('.selisih').text(formatNumber(selisih));

				// Auto check if qty is filled
				if (qty > 0) {
					$row.find('.chk-item').prop('checked', true);
				}
			});

			function formatNumber(num) {
				return parseFloat(num).toLocaleString('id-ID', {
					minimumFractionDigits: 2,
					maximumFractionDigits: 2
				});
			}

			// Check All
			$('#btn-check-all').click(function() {
				$('.chk-item').prop('checked', true);
			});

			// Uncheck All
			$('#btn-uncheck-all').click(function() {
				$('.chk-item').prop('checked', false);
			});

			// Filter
			$('#btn-filter').click(function() {
				var sub = $('#filter-sub').val().toUpperCase();
				var itemDari = $('#filter-item-dari').val().toUpperCase();
				var itemSd = $('#filter-item-sd').val().toUpperCase();
				var kdlaku = $('#filter-kdlaku').val();
				var lhDari = $('#filter-lh-dari').val();
				var lhSd = $('#filter-lh-sd').val();
				var hari = $('#filter-hari').val();

				$('#tbody-detail tr').each(function() {
					var $row = $(this);

					// Skip header row
					if ($row.find('td').first().attr('colspan')) {
						return;
					}

					var showRow = true;

					// Filter by sub
					if (sub && $row.data('sub') != sub) {
						showRow = false;
					}

					// Filter by item range
					if (itemDari && $row.data('kd') < itemDari) {
						showRow = false;
					}
					if (itemSd && $row.data('kd') > itemSd) {
						showRow = false;
					}

					// Filter by kdlaku
					if (kdlaku && $row.data('kdlaku') != kdlaku) {
						showRow = false;
					}

					// Show/hide row
					if (showRow) {
						$row.show();
					} else {
						$row.hide();
					}
				});

				// Renumber visible rows
				var no = 1;
				$('#tbody-detail tr:visible').each(function() {
					if (!$(this).find('td').first().attr('colspan')) {
						$(this).find('td:first').text(no++);
					}
				});
			});

			// Proses
			$('#btn-proses').click(function(e) {
				e.preventDefault();

				Swal.fire({
					title: 'Proses Data?',
					text: 'Data yang dicentang akan diproses',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya, Proses!',
					cancelButtonText: 'Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						return submitForm('proses');
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					if (result.isConfirmed) {
						Swal.fire({
							title: 'Berhasil!',
							text: result.value.message || 'Data berhasil diproses',
							icon: 'success'
						}).then(() => {
							if (!$('#chk-pertahankan').is(':checked')) {
								location.reload();
							}
						});
					}
				}).catch((error) => {
					handleError(error);
				});
			});

			// Hapus Positif
			$('#btn-hapus-positif').click(function() {
				confirmAction('hapus_positif', 'Hapus data dengan selisih positif?');
			});

			// Hapus Nol
			$('#btn-hapus-nol').click(function() {
				confirmAction('hapus_nol', 'Hapus data dengan qty nol?');
			});

			// Hapus Negatif
			$('#btn-hapus-negatif').click(function() {
				confirmAction('hapus_negatif', 'Hapus data dengan selisih negatif?');
			});

			function confirmAction(action, message) {
				Swal.fire({
					title: 'Konfirmasi',
					text: message,
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Ya, Hapus!',
					cancelButtonText: 'Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						return submitForm(action);
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					if (result.isConfirmed) {
						Swal.fire({
							title: 'Berhasil!',
							text: result.value.message || 'Data berhasil dihapus',
							icon: 'success'
						}).then(() => {
							location.reload();
						});
					}
				}).catch((error) => {
					handleError(error);
				});
			}

			function submitForm(action) {
				var formData = $('#form-lbtat').serializeArray();
				var data = {
					_token: '{{ csrf_token() }}',
					action: action
				};

				formData.forEach(function(item) {
					data[item.name] = item.value;
				});

				// Collect checked items
				var details = [];
				$('#tbody-detail tr:visible').each(function() {
					if (!$(this).find('td').first().attr('colspan')) {
						var isChecked = $(this).find('.chk-item').is(':checked');
						var kdBrg = $(this).find('input[name*="[kd_brg]"]').val();
						var qty = $(this).find('.qty-input').val();
						var ket = $(this).find('input[name*="[ket]"]').val();

						if (isChecked || action != 'proses') {
							details.push({
								kd_brg: kdBrg,
								qty: qty,
								ket: ket,
								cek: isChecked ? '1' : '0'
							});
						}
					}
				});

				data.details = details;

				var routeName = $('input[name="route_name"]').val();
				var url = routeName == 'tprosesstockopname' ?
					"{{ route('tprosesstockopname_proses') }}" :
					"{{ route('tpenangananlbtat_proses') }}";

				return $.ajax({
					url: url,
					type: 'POST',
					data: data,
					dataType: 'json'
				});
			}

			function handleError(error) {
				if (error && error.responseJSON) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: error.responseJSON.message || error.responseJSON.error || 'Terjadi kesalahan',
						confirmButtonText: 'OK'
					});
				}
			}

			// Print
			$('#btn-print').click(function() {
				var noBukti = $('input[name="no_bukti"]').val();
				var routeName = $('input[name="route_name"]').val();

				var url = routeName == 'tprosesstockopname' ?
					"{{ route('tprosesstockopname_jasper') }}" :
					"{{ route('tpenangananlbtat_jasper') }}";

				$.ajax({
					url: url,
					type: 'GET',
					data: {
						no_bukti: noBukti,
						route_name: routeName
					},
					success: function(response) {
						if (response.success && response.data && response.data.length > 0) {
							var printWindow = window.open('', '_blank');
							printWindow.document.write('<html><head><title>Print</title>');
							printWindow.document.write(
								'<style>body{font-family:Arial;font-size:11px;} table{width:100%;border-collapse:collapse;margin-top:20px;} th,td{border:1px solid #000;padding:4px;text-align:left;font-size:10px;} th{background-color:#f0f0f0;} .text-center{text-align:center;} .text-right{text-align:right;}</style>'
								);
							printWindow.document.write('</head><body>');
							printWindow.document.write('<h3>' + (response.header.notes || '') + '</h3>');
							printWindow.document.write('<p><strong>No. Bukti:</strong> ' + noBukti + '</p>');
							printWindow.document.write(
								'<table><thead><tr><th>No</th><th>Kode</th><th>Nama Barang</th><th>Ukuran</th><th class="text-right">Stok</th><th class="text-right">Qty</th><th class="text-right">Selisih</th><th>Ket</th></tr></thead><tbody>'
								);

							response.data.forEach(function(item, index) {
								var selisih = parseFloat(item.qty || 0) - parseFloat(item.stockt || 0);
								printWindow.document.write(
									'<tr>' +
									'<td class="text-center">' + (index + 1) + '</td>' +
									'<td>' + (item.KD_BRG || '') + '</td>' +
									'<td>' + (item.NA_BRG || '') + '</td>' +
									'<td>' + (item.KET_UK || '') + '</td>' +
									'<td class="text-right">' + parseFloat(item.stockt || 0).toFixed(2) + '</td>' +
									'<td class="text-right">' + parseFloat(item.qty || 0).toFixed(2) + '</td>' +
									'<td class="text-right">' + selisih.toFixed(2) + '</td>' +
									'<td>' + (item.ket || '') + '</td>' +
									'</tr>'
								);
							});

							printWindow.document.write('</tbody></table></body></html>');
							printWindow.document.close();
							printWindow.print();
						} else {
							Swal.fire('Info', 'Tidak ada data untuk dicetak', 'info');
						}
					},
					error: function(xhr) {
						Swal.fire('Error', xhr.responseJSON?.message || 'Gagal memuat data print', 'error');
					}
				});
			});

			// Export SO
			$('#btn-export').click(function() {
				var noBukti = $('input[name="no_bukti"]').val();

				Swal.fire({
					title: 'Export Stock Opname?',
					text: 'Data akan diexport ke file .txt',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya, Export!',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						$.ajax({
							url: "{{ route('tprosesstockopname.export') }}",
							type: 'GET',
							data: {
								no_bukti: noBukti
							},
							success: function(response) {
								if (response.success && response.data) {
									// Create download link
									var content = response.data.join('\n');
									var blob = new Blob([content], {
										type: 'text/plain'
									});
									var url = window.URL.createObjectURL(blob);
									var a = document.createElement('a');
									a.href = url;
									a.download = response.filename || 'export.txt';
									document.body.appendChild(a);
									a.click();
									document.body.removeChild(a);
									window.URL.revokeObjectURL(url);

									Swal.fire('Berhasil!', 'File berhasil diexport', 'success');
								} else {
									Swal.fire('Error', 'Tidak ada data untuk diexport', 'error');
								}
							},
							error: function(xhr) {
								Swal.fire('Error', xhr.responseJSON?.message || 'Gagal export data', 'error');
							}
						});
					}
				});
			});

			// Import SO
			$('#btn-import').click(function() {
				Swal.fire({
					title: 'Import Stock Opname',
					html: `
                <div class="form-group">
                    <label>Pilih File .txt</label>
                    <input type="file" id="file-import" class="form-control" accept=".txt">
                </div>
            `,
					showCancelButton: true,
					confirmButtonText: 'Import',
					cancelButtonText: 'Batal',
					preConfirm: () => {
						const file = document.getElementById('file-import').files[0];
						if (!file) {
							Swal.showValidationMessage('File harus dipilih');
							return false;
						}
						return file;
					}
				}).then((result) => {
					if (result.isConfirmed) {
						var file = result.value;
						var reader = new FileReader();

						reader.onload = function(e) {
							var content = e.target.result;
							var noBukti = $('input[name="no_bukti"]').val();

							$.ajax({
								url: "{{ route('tprosesstockopname.import') }}",
								type: 'POST',
								data: {
									_token: '{{ csrf_token() }}',
									no_bukti: noBukti,
									file_content: content
								},
								success: function(response) {
									if (response.success) {
										Swal.fire('Berhasil!', response.message, 'success').then(() => {
											location.reload();
										});
									} else if (response.ask_overwrite) {
										Swal.fire({
											title: 'Data Sudah Ada',
											text: response.message + ' Timpa data lama?',
											icon: 'warning',
											showCancelButton: true,
											confirmButtonText: 'Ya, Timpa!',
											cancelButtonText: 'Batal'
										}).then((overwrite) => {
											if (overwrite.isConfirmed) {
												// Call import with overwrite flag
												$.ajax({
													url: "{{ route('tprosesstockopname.import') }}",
													type: 'POST',
													data: {
														_token: '{{ csrf_token() }}',
														no_bukti: noBukti,
														file_content: content,
														overwrite: true
													},
													success: function(resp) {
														Swal.fire('Berhasil!', resp.message,
															'success').then(() => {
															location.reload();
														});
													},
													error: function(xhr) {
														Swal.fire('Error', xhr.responseJSON
															?.message || 'Gagal import',
															'error');
													}
												});
											}
										});
									}
								},
								error: function(xhr) {
									Swal.fire('Error', xhr.responseJSON?.message || 'Gagal import data', 'error');
								}
							});
						};

						reader.readAsText(file);
					}
				});
			});
		});
	</script>
@endsection
