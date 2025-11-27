@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $status == 'simpan' ? 'New' : 'Edit' }} Input Survey Penjualan</h1>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<form id="form-input-survey" method="POST">
					@csrf
					<input type="hidden" name="status" value="{{ $status }}">

					<div class="card">
						<div class="card-header">
							<div class="row">
								<div class="col-md-6">
									<button type="button" class="btn btn-sm btn-success" id="btn-save">
										<i class="fas fa-save"></i> Save
									</button>
									<a href="{{ route('tinputsurveypenjualan') }}" class="btn btn-sm btn-danger">
										<i class="fas fa-times"></i> Exit
									</a>
								</div>
							</div>
						</div>
						<div class="card-body">
							@if (isset($error) && $error)
								<div class="alert alert-danger alert-dismissible fade show">
									<button type="button" class="close" data-dismiss="alert">&times;</button>
									<strong>Error!</strong> {{ $error }}
								</div>
							@endif

							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label>No Bukti</label>
										<input type="text" class="form-control form-control-sm" name="no_bukti" value="{{ $header->no_bukti ?? '+' }}" readonly
											style="background-color: #e9ecef; font-weight: bold;">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Tanggal <span class="text-danger">*</span></label>
										<input type="date" class="form-control form-control-sm" name="tgl" value="{{ $header->tgl ?? date('Y-m-d') }}" required>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>No Survey <span class="text-danger">*</span></label>
										<div class="input-group input-group-sm">
											<input type="text" class="form-control form-control-sm" name="no_agenda" id="no_agenda" value="{{ $header->no_agenda ?? '' }}"
												placeholder="Nomor Survey" required>
											<div class="input-group-append">
												<button type="button" class="btn btn-info btn-sm" id="btn-load-survey">
													<i class="fas fa-search"></i>
												</button>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Notes</label>
										<input type="text" class="form-control form-control-sm" name="notes" value="{{ $header->notes ?? '' }}" placeholder="Keterangan">
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label>Total Qty</label>
										<input type="text" class="form-control form-control-sm text-right" id="total_qty"
											value="{{ number_format($header->total_qty ?? 0, 2, ',', '.') }}" readonly style="background-color: #e9ecef; font-weight: bold;">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Total</label>
										<input type="text" class="form-control form-control-sm text-right" id="total"
											value="{{ number_format($header->total ?? 0, 0, ',', '.') }}" readonly style="background-color: #e9ecef; font-weight: bold;">
									</div>
								</div>
							</div>

							<hr>

							<div class="row mb-2">
								<div class="col-md-12">
									<button type="button" class="btn btn-success btn-sm" id="btn-add-row">
										<i class="fas fa-plus"></i> Tambah Baris
									</button>
									<button type="button" class="btn btn-danger btn-sm" id="btn-clear-all">
										<i class="fas fa-trash"></i> Hapus Semua
									</button>
								</div>
							</div>

							<div class="table-responsive">
								<table class="table-bordered table-striped table-sm table" id="table-detail">
									<thead class="thead-dark">
										<tr>
											<th width="3%" class="text-center">No</th>
											<th width="10%" class="text-center">Barcode</th>
											<th width="8%" class="text-center">Sub Item</th>
											<th width="8%" class="text-center">Kode</th>
											<th width="20%" class="text-center">Nama Barang</th>
											<th width="8%" class="text-center">Ukuran</th>
											<th width="8%" class="text-center">Kemasan</th>
											<th width="5%" class="text-center">Pajak</th>
											<th width="7%" class="text-center">Qty</th>
											<th width="8%" class="text-center">HJ</th>
											<th width="8%" class="text-center">HB</th>
											<th width="8%" class="text-center">HB (-PJK)</th>
											<th width="10%" class="text-center">Total HB</th>
											<th width="10%" class="text-center">Keterangan</th>
											<th width="3%" class="text-center"><i class="fas fa-cog"></i></th>
										</tr>
									</thead>
									<tbody id="tbody-detail">
										@if (!empty($detail) && count($detail) > 0)
											@foreach ($detail as $key => $row)
												<tr data-no-id="{{ $row->no_id ?? 0 }}">
													<td class="text-center">{{ $key + 1 }}</td>
													<td>
														<input type="text" class="form-control form-control-sm barcode-input" name="detail[{{ $key }}][barcode]"
															value="{{ $row->barcode ?? '' }}" placeholder="Scan barcode">
														<input type="hidden" name="detail[{{ $key }}][no_id]" value="{{ $row->no_id ?? 0 }}">
														<input type="hidden" name="detail[{{ $key }}][rec]" value="{{ $key + 1 }}">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][sub]" value="{{ $row->sub ?? '' }}"
															readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm kd-brg-input" name="detail[{{ $key }}][kd_brg]"
															value="{{ $row->kd_brg ?? '' }}" placeholder="Kode">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][na_brg]"
															value="{{ $row->na_brg ?? '' }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][ket_uk]"
															value="{{ $row->ket_uk ?? '' }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][ket_kem]"
															value="{{ $row->ket_kem ?? '' }}" readonly style="background-color: #e9ecef;">
													</td>
													<td class="text-center">
														<input type="text" class="form-control form-control-sm pjk-input text-center" name="detail[{{ $key }}][pjk]"
															value="{{ $row->pjk ?? '' }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" step="0.01" class="form-control form-control-sm jml-ord-input text-right"
															name="detail[{{ $key }}][jml_ord]" value="{{ $row->jml_ord ?? 0 }}">
													</td>
													<td>
														<input type="number" step="0.01" class="form-control form-control-sm hj-input text-right" name="detail[{{ $key }}][hj]"
															value="{{ $row->hj ?? 0 }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" step="0.01" class="form-control form-control-sm hb-input text-right" name="detail[{{ $key }}][hb]"
															value="{{ $row->hb ?? 0 }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" step="0.01" class="form-control form-control-sm hbpjk-input text-right"
															name="detail[{{ $key }}][hbpjk]" value="{{ $row->hbpjk ?? 0 }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" step="0.01" class="form-control form-control-sm total-input text-right"
															name="detail[{{ $key }}][total]" value="{{ $row->total ?? 0 }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][ket]" value="{{ $row->ket ?? '' }}"
															placeholder="Keterangan">
													</td>
													<td class="text-center">
														<button type="button" class="btn btn-xs btn-danger btn-delete-row">
															<i class="fas fa-trash"></i>
														</button>
													</td>
												</tr>
											@endforeach
										@else
											<tr>
												<td colspan="15" class="text-center">Tidak ada data. Klik "Tambah Baris" atau load dari Survey.</td>
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
	<script>
		$(document).ready(function() {
			let rowIndex = {{ count($detail ?? []) }};

			// Load Survey
			$('#btn-load-survey').click(function() {
				let noAgenda = $('#no_agenda').val().trim();

				if (!noAgenda) {
					Swal.fire('Peringatan', 'Nomor Survey harus diisi', 'warning');
					return;
				}

				Swal.fire({
					title: 'Memuat Data Survey...',
					text: 'Mohon tunggu',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				$.ajax({
					url: "{{ route('tinputsurveypenjualan.browse') }}",
					data: {
						no_agenda: noAgenda
					},
					success: function(response) {
						Swal.close();

						if (response.success && response.data.length > 0) {
							// Clear existing data
							$('#tbody-detail').empty();
							rowIndex = 0;

							// Add rows from Survey
							response.data.forEach(function(item) {
								addRowFromSurvey(item);
							});

							calculateTotal();
							Swal.fire('Berhasil', response.data.length + ' barang berhasil dimuat dari Survey', 'success');
						} else {
							Swal.fire('Info', response.message || 'Tidak ada data', 'info');
						}
					},
					error: function(xhr) {
						Swal.close();
						Swal.fire('Error', xhr.responseJSON?.message || 'Gagal memuat data', 'error');
					}
				});
			});

			function addRowFromSurvey(item) {
				let newRow = `
        <tr data-no-id="${item.no_id || 0}">
            <td class="text-center">${rowIndex + 1}</td>
            <td>
                <input type="text" class="form-control form-control-sm barcode-input" name="detail[${rowIndex}][barcode]" value="${item.barcode || ''}" placeholder="Scan barcode">
                <input type="hidden" name="detail[${rowIndex}][no_id]" value="${item.no_id || 0}">
                <input type="hidden" name="detail[${rowIndex}][rec]" value="${rowIndex + 1}">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][sub]" value="${item.sub || ''}" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm kd-brg-input" name="detail[${rowIndex}][kd_brg]" value="${item.kd_brg || ''}" placeholder="Kode">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][na_brg]" value="${item.na_brg || ''}" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][ket_uk]" value="${item.ket_uk || ''}" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][ket_kem]" value="${item.ket_kem || ''}" readonly style="background-color: #e9ecef;">
            </td>
            <td class="text-center">
                <input type="text" class="form-control form-control-sm text-center pjk-input" name="detail[${rowIndex}][pjk]" value="${item.pjk || ''}" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-right jml-ord-input" name="detail[${rowIndex}][jml_ord]" value="${item.jml_ord || 0}">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-right hj-input" name="detail[${rowIndex}][hj]" value="${item.hj || 0}" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-right hb-input" name="detail[${rowIndex}][hb]" value="${item.hb || 0}" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-right hbpjk-input" name="detail[${rowIndex}][hbpjk]" value="${item.hbpjk || 0}" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-right total-input" name="detail[${rowIndex}][total]" value="0" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][ket]" placeholder="Keterangan">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-xs btn-danger btn-delete-row">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        `;

				$('#tbody-detail').append(newRow);
				rowIndex++;
			}

			// Add Row
			$('#btn-add-row').click(function() {
				let newRow = `
        <tr data-no-id="0">
            <td class="text-center">${rowIndex + 1}</td>
            <td>
                <input type="text" class="form-control form-control-sm barcode-input" name="detail[${rowIndex}][barcode]" placeholder="Scan barcode">
                <input type="hidden" name="detail[${rowIndex}][no_id]" value="0">
                <input type="hidden" name="detail[${rowIndex}][rec]" value="${rowIndex + 1}">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][sub]" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm kd-brg-input" name="detail[${rowIndex}][kd_brg]" placeholder="Kode">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][na_brg]" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][ket_uk]" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][ket_kem]" readonly style="background-color: #e9ecef;">
            </td>
            <td class="text-center">
                <input type="text" class="form-control form-control-sm text-center pjk-input" name="detail[${rowIndex}][pjk]" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-right jml-ord-input" name="detail[${rowIndex}][jml_ord]" value="0">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-right hj-input" name="detail[${rowIndex}][hj]" value="0" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-right hb-input" name="detail[${rowIndex}][hb]" value="0" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-right hbpjk-input" name="detail[${rowIndex}][hbpjk]" value="0" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-right total-input" name="detail[${rowIndex}][total]" value="0" readonly style="background-color: #e9ecef;">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][ket]" placeholder="Keterangan">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-xs btn-danger btn-delete-row">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        `;

				$('#tbody-detail').append(newRow);
				rowIndex++;
			});

			// Search barang by kode
			$(document).on('keyup', '.kd-brg-input', function(e) {
				if (e.keyCode === 13) { // Enter
					let $row = $(this).closest('tr');
					let kdBrg = $(this).val().trim();
					let noAgenda = $('#no_agenda').val().trim();

					if (!kdBrg) return;
					if (!noAgenda) {
						Swal.fire('Peringatan', 'Nomor Survey harus diisi terlebih dahulu', 'warning');
						return;
					}

					$.ajax({
						url: "{{ route('tinputsurveypenjualan.detail') }}",
						data: {
							kd_brg: kdBrg,
							no_agenda: noAgenda
						},
						success: function(response) {
							if (response.exists) {
								populateRow($row, response.data, response.from_survey);
								$row.find('.jml-ord-input').focus();
								calculateRow($row);
							} else {
								Swal.fire('Info', 'Barang tidak ditemukan', 'info');
							}
						},
						error: function() {
							Swal.fire('Error', 'Gagal mencari barang', 'error');
						}
					});
				}
			});

			// Search barang by barcode
			$(document).on('keyup', '.barcode-input', function(e) {
				if (e.keyCode === 13) { // Enter
					let $row = $(this).closest('tr');
					let barcode = $(this).val().trim();
					let noAgenda = $('#no_agenda').val().trim();

					if (!barcode) return;
					if (!noAgenda) {
						Swal.fire('Peringatan', 'Nomor Survey harus diisi terlebih dahulu', 'warning');
						return;
					}

					$.ajax({
						url: "{{ route('tinputsurveypenjualan.detail') }}",
						data: {
							kd_brg: barcode,
							no_agenda: noAgenda
						},
						success: function(response) {
							if (response.exists) {
								populateRow($row, response.data, response.from_survey);
								$row.find('.jml-ord-input').focus();
								calculateRow($row);
							} else {
								Swal.fire('Info', 'Barang tidak ditemukan', 'info');
							}
						},
						error: function() {
							Swal.fire('Error', 'Gagal mencari barang', 'error');
						}
					});
				}
			});

			function populateRow($row, data, fromSurvey) {
				$row.find('input[name*="[kd_brg]"]').val(data.kd_brg || data.KD_BRG || '');
				$row.find('input[name*="[na_brg]"]').val(data.na_brg || data.NA_BRG || '');
				$row.find('input[name*="[barcode]"]').val(data.barcode || data.BARCODE || '');
				$row.find('input[name*="[ket_kem]"]').val(data.ket_kem || '');
				$row.find('input[name*="[ket_uk]"]').val(data.ket_uk || '');
				$row.find('input[name*="[sub]"]').val(data.sub || '');
				$row.find('input[name*="[pjk]"]').val(data.pjk || '');
				$row.find('.hj-input').val(data.hj || data.HJ || 0);
				$row.find('.hb-input').val(data.hb || data.HB || 0);
				$row.find('.hbpjk-input').val(data.hbpjk || 0);

				if (fromSurvey && data.no_id) {
					$row.find('input[name*="[no_id]"]').val(data.no_id);
					$row.attr('data-no-id', data.no_id);
					if (data.jml_ord) {
						$row.find('.jml-ord-input').val(data.jml_ord);
					}
				}
			}

			// Calculate when qty changes
			$(document).on('input change', '.jml-ord-input', function() {
				let $row = $(this).closest('tr');
				calculateRow($row);
			});

			function calculateRow($row) {
				let jmlOrd = parseFloat($row.find('.jml-ord-input').val()) || 0;
				let pjk = $row.find('.pjk-input').val();
				let hb = parseFloat($row.find('.hb-input').val()) || 0;

				let hbpjk = hb;
				if (pjk === 'Y') {
					hbpjk = Math.round(hb / 1.1 * 100) / 100;
				}

				let total = jmlOrd * hbpjk;

				$row.find('.hbpjk-input').val(hbpjk.toFixed(2));
				$row.find('.total-input').val(total.toFixed(2));

				calculateTotal();
			}

			function calculateTotal() {
				let totalQty = 0;
				let totalAmount = 0;

				$('#tbody-detail tr').each(function() {
					if (!$(this).find('td').first().attr('colspan')) {
						let qty = parseFloat($(this).find('.jml-ord-input').val()) || 0;
						let total = parseFloat($(this).find('.total-input').val()) || 0;
						totalQty += qty;
						totalAmount += total;
					}
				});

				$('#total_qty').val(totalQty.toFixed(2));
				$('#total').val(totalAmount.toFixed(0));
			}

			// Delete Row
			$(document).on('click', '.btn-delete-row', function() {
				$(this).closest('tr').remove();

				if ($('#tbody-detail tr').length === 0) {
					$('#tbody-detail').html('<tr><td colspan="15" class="text-center">Tidak ada data</td></tr>');
				}

				// Renumber rows
				$('#tbody-detail tr').each(function(index) {
					if (!$(this).find('td').first().attr('colspan')) {
						$(this).find('td:first').text(index + 1);
						$(this).find('input[name*="[rec]"]').val(index + 1);
					}
				});

				calculateTotal();
			});

			// Clear All
			$('#btn-clear-all').click(function() {
				Swal.fire({
					title: 'Hapus Semua?',
					text: 'Semua detail akan dihapus',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Ya, Hapus!',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						$('#tbody-detail').html('<tr><td colspan="15" class="text-center">Tidak ada data</td></tr>');
						rowIndex = 0;
						$('#total_qty').val('0.00');
						$('#total').val('0');
					}
				});
			});

			// Save
			$('#btn-save').click(function(e) {
				e.preventDefault();

				let tgl = $('input[name="tgl"]').val();
				let noAgenda = $('#no_agenda').val().trim();

				if (!tgl) {
					Swal.fire('Peringatan', 'Tanggal harus diisi', 'warning');
					$('input[name="tgl"]').focus();
					return;
				}

				if (!noAgenda) {
					Swal.fire('Peringatan', 'Nomor Survey harus diisi', 'warning');
					$('#no_agenda').focus();
					return;
				}

				let hasDetail = false;
				$('#tbody-detail tr').each(function() {
					if (!$(this).find('td').first().attr('colspan')) {
						let kdBrg = $(this).find('input[name*="[kd_brg]"]').val();
						if (kdBrg && kdBrg.trim() !== '') {
							hasDetail = true;
						}
					}
				});

				if (!hasDetail) {
					Swal.fire('Peringatan', 'Detail barang harus diisi', 'warning');
					return;
				}

				Swal.fire({
					title: 'Simpan Data?',
					text: 'Data akan disimpan ke database',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya, Simpan!',
					cancelButtonText: 'Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						let formData = {
							_token: '{{ csrf_token() }}',
							status: '{{ $status }}',
							no_bukti: $('input[name="no_bukti"]').val(),
							tgl: tgl,
							no_agenda: noAgenda,
							notes: $('input[name="notes"]').val(),
							details: []
						};

						$('#tbody-detail tr').each(function(index) {
							if (!$(this).find('td').first().attr('colspan')) {
								let kdBrg = $(this).find('input[name*="[kd_brg]"]').val();
								if (kdBrg && kdBrg.trim() !== '') {
									formData.details.push({
										no_id: $(this).find('input[name*="[no_id]"]').val() || 0,
										rec: index + 1,
										barcode: $(this).find('input[name*="[barcode]"]').val() || '',
										sub: $(this).find('input[name*="[sub]"]').val() || '',
										kd_brg: kdBrg,
										na_brg: $(this).find('input[name*="[na_brg]"]').val(),
										ket_uk: $(this).find('input[name*="[ket_uk]"]').val() || '',
										ket_kem: $(this).find('input[name*="[ket_kem]"]').val() || '',
										pjk: $(this).find('input[name*="[pjk]"]').val() || '',
										jml_ord: parseFloat($(this).find('input[name*="[jml_ord]"]').val()) || 0,
										hj: parseFloat($(this).find('input[name*="[hj]"]').val()) || 0,
										hb: parseFloat($(this).find('input[name*="[hb]"]').val()) || 0,
										hbpjk: parseFloat($(this).find('input[name*="[hbpjk]"]').val()) || 0,
										total: parseFloat($(this).find('input[name*="[total]"]').val()) || 0,
										ket: $(this).find('input[name*="[ket]"]').val() || ''
									});
								}
							}
						});

						return $.ajax({
							url: "{{ route('tinputsurveypenjualan.store') }}",
							type: 'POST',
							data: formData,
							dataType: 'json'
						});
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					if (result.isConfirmed) {
						Swal.fire({
							title: 'Berhasil!',
							text: result.value.message || 'Save Data Success',
							icon: 'success',
							confirmButtonText: 'OK'
						}).then(() => {
							window.location.href = "{{ route('tinputsurveypenjualan') }}";
						});
					}
				}).catch((error) => {
					if (error && error.responseJSON) {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: error.responseJSON.message || 'Terjadi kesalahan saat menyimpan data',
							confirmButtonText: 'OK'
						});
					}
				});
			});

			// Initial calculation for edit mode
			@if ($status == 'edit' && !empty($detail))
				$('#tbody-detail tr').each(function() {
					if (!$(this).find('td').first().attr('colspan')) {
						calculateRow($(this));
					}
				});
				calculateTotal();
			@endif
		});
	</script>
@endsection
