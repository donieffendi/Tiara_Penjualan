@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $status == 'simpan' ? 'New' : 'Edit' }} Koreksi Stock Opname</h1>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<form id="form-koreksi-so" method="POST">
					@csrf
					<input type="hidden" name="status" value="{{ $status }}">

					<div class="card">
						<div class="card-header">
							<div class="row">
								<div class="col-md-6">
									<button type="button" class="btn btn-sm btn-success" id="btn-save">
										<i class="fas fa-save"></i> Save
									</button>
									<a href="{{ route('tkoreksistokopname') }}" class="btn btn-sm btn-danger">
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
										<label>Nomor Stok Opname</label>
										<div class="input-group input-group-sm">
											<input type="text" class="form-control form-control-sm" name="bktk" id="bktk" value="{{ $header->bktk ?? '' }}"
												placeholder="Nomor SO">
											<div class="input-group-append">
												<button type="button" class="btn btn-info btn-sm" id="btn-load-so">
													<i class="fas fa-search"></i>
												</button>
											</div>
										</div>
										<small class="form-text text-muted">Kosongkan jika input manual</small>
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
							</div>

							<hr>

							<div class="alert alert-info">
								<strong>Keterangan:</strong>
								<ul class="mb-0">
									<li>Nilai <strong>(-)</strong> mengurangi stok barang</li>
									<li>Nilai <strong>(+)</strong> menambah stok barang</li>
									<li><strong>Qty</strong> = Riil - Stok</li>
								</ul>
							</div>

							{{-- <div class="row mb-2">
								<div class="col-md-12">
									<button type="button" class="btn btn-success btn-sm" id="btn-add-row">
										<i class="fas fa-plus"></i> Tambah Baris
									</button>
									<button type="button" class="btn btn-danger btn-sm" id="btn-clear-all">
										<i class="fas fa-trash"></i> Hapus Semua
									</button>
								</div>
							</div> --}}

							<div class="table-responsive">
								<table class="table-bordered table-striped table-sm table" id="table-detail">
									<thead class="thead-dark">
										<tr>
											<th width="3%" class="text-center">No</th>
											<th width="10%" class="text-center">Barcode</th>
											<th width="10%" class="text-center">Kode</th>
											<th width="22%" class="text-center">Nama Barang</th>
											<th width="8%" class="text-center">Stok</th>
											<th width="8%" class="text-center">Riil</th>
											<th width="8%" class="text-center">Qty</th>
											<th width="8%" class="text-center">Harga</th>
											<th width="10%" class="text-center">Total</th>
											<th width="10%" class="text-center">Ket</th>
											<th width="3%" class="text-center">
												<i class="fas fa-cog"></i>
											</th>
										</tr>
									</thead>
									<tbody id="tbody-detail">
										@if (!empty($detail) && count($detail) > 0)
											@foreach ($detail as $key => $row)
												<tr data-no-id="{{ $row->no_id ?? 0 }}">
													<td class="text-center">{{ $key + 1 }}</td>
													<td>
														<input type="text" class="form-control form-control-sm barcode-input" name="detail[{{ $key }}][barcode]"
															value="{{ $row->BARCODE ?? '' }}" placeholder="Scan barcode">
														<input type="hidden" name="detail[{{ $key }}][no_id]" value="{{ $row->no_id ?? 0 }}">
														<input type="hidden" name="detail[{{ $key }}][rec]" value="{{ $key + 1 }}">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm kd-brg-input" name="detail[{{ $key }}][kd_brg]"
															value="{{ $row->kd_brg ?? '' }}" placeholder="Kode barang">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][na_brg]"
															value="{{ $row->na_brg ?? '' }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" step="0.01" class="form-control form-control-sm stok-input text-right"
															name="detail[{{ $key }}][stok]" value="{{ $row->saldo ?? 0 }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" step="0.01" class="form-control form-control-sm riil-input text-right"
															name="detail[{{ $key }}][riil]" value="{{ $row->riil ?? 0 }}">
													</td>
													<td>
														<input type="number" step="0.01" class="form-control form-control-sm qty-input text-right"
															name="detail[{{ $key }}][qty]" value="{{ $row->qty ?? 0 }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" step="0.01" class="form-control form-control-sm hb-input text-right" name="detail[{{ $key }}][hb]"
															value="{{ $row->hb ?? 0 }}" readonly style="background-color: #e9ecef;">
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
												<td colspan="11" class="text-center">Tidak ada data. Klik "Tambah Baris" atau load dari SO.</td>
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
		$(document).ready(function() {
			let rowIndex = {{ count($detail ?? []) }};

			// Load SO
			$('#btn-load-so').click(function() {
				let noBukti = $('#bktk').val().trim();

				if (!noBukti) {
					Swal.fire('Peringatan', 'Nomor SO harus diisi', 'warning');
					return;
				}

				Swal.fire({
					title: 'Memuat Data SO...',
					text: 'Mohon tunggu',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				$.ajax({
					url: "{{ route('tkoreksistokopname.browse') }}",
					data: {
						no_bukti: noBukti
					},
					success: function(response) {
						Swal.close();

						if (response.success && response.data.length > 0) {
							// Clear existing data
							$('#tbody-detail').empty();
							rowIndex = 0;

							// Add rows from SO
							response.data.forEach(function(item) {
								addRowFromSO(item);
							});

							Swal.fire('Berhasil', response.data.length + ' barang berhasil dimuat dari SO', 'success');
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

			function addRowFromSO(item) {
				let newRow = `
            <tr data-no-id="0">
                <td class="text-center">${rowIndex + 1}</td>
                <td>
                    <input type="text" class="form-control form-control-sm barcode-input" name="detail[${rowIndex}][barcode]" value="${item.BARCODE || ''}" placeholder="Scan barcode">
                    <input type="hidden" name="detail[${rowIndex}][no_id]" value="0">
                    <input type="hidden" name="detail[${rowIndex}][rec]" value="${rowIndex + 1}">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm kd-brg-input" name="detail[${rowIndex}][kd_brg]" value="${item.KD_BRG || ''}" placeholder="Kode barang">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][na_brg]" value="${item.NA_BRG || ''}"  >
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-right stok-input" name="detail[${rowIndex}][stok]" value="${item.stok || 0}"  >
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-right riil-input" name="detail[${rowIndex}][riil]" value="0">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-right qty-input" name="detail[${rowIndex}][qty]" value="0"  >
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-right hb-input" name="detail[${rowIndex}][hb]" value="${item.HB || 0}"  >
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-right total-input" name="detail[${rowIndex}][total]" value="0"  >
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
                    <input type="text" class="form-control form-control-sm kd-brg-input" name="detail[${rowIndex}][kd_brg]" placeholder="Kode barang">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][na_brg]" readonly style="background-color: #e9ecef;">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-right stok-input" name="detail[${rowIndex}][stok]" value="0" readonly style="background-color: #e9ecef;">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-right riil-input" name="detail[${rowIndex}][riil]" value="0">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-right qty-input" name="detail[${rowIndex}][qty]" value="0" readonly style="background-color: #e9ecef;">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-right hb-input" name="detail[${rowIndex}][hb]" value="0" readonly style="background-color: #e9ecef;">
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

					if (!kdBrg) return;

					$.ajax({
						url: "{{ route('tkoreksistokopname.detail') }}",
						data: {
							kd_brg: kdBrg
						},
						success: function(response) {
							if (response.exists) {
								$row.find('input[name*="[na_brg]"]').val(response.data.NA_BRG || '');
								$row.find('input[name*="[stok]"]').val(response.data.stok || 0);
								$row.find('input[name*="[hb]"]').val(response.data.HB || 0);
								$row.find('input[name*="[barcode]"]').val(response.data.BARCODE || '');
								$row.find('.riil-input').focus();
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

					if (!barcode) return;

					// Search by barcode (using browse with barcode parameter)
					$.ajax({
						url: "{{ route('tkoreksistokopname.detail') }}",
						data: {
							kd_brg: barcode
						}, // API will handle barcode search
						success: function(response) {
							if (response.exists) {
								$row.find('input[name*="[kd_brg]"]').val(response.data.KD_BRG || '');
								$row.find('input[name*="[na_brg]"]').val(response.data.NA_BRG || '');
								$row.find('input[name*="[stok]"]').val(response.data.stok || 0);
								$row.find('input[name*="[hb]"]').val(response.data.HB || 0);
								$row.find('.riil-input').focus();
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

			// Calculate when riil changes
			$(document).on('input change', '.riil-input', function() {
				let $row = $(this).closest('tr');
				calculateRow($row);
			});

			function calculateRow($row) {
				let stok = parseFloat($row.find('.stok-input').val()) || 0;
				let riil = parseFloat($row.find('.riil-input').val()) || 0;
				let hb = parseFloat($row.find('.hb-input').val()) || 0;

				// Logika perhitungan qty sesuai Delphi
				let qty = 0;

				if (stok >= 0) {
					if (riil == 0) {
						qty = stok * -1;
					} else {
						qty = riil - stok;
					}
				} else {
					if (riil == 0) {
						qty = riil - stok;
					} else {
						qty = riil + (stok * -1);
					}
				}

				let total = qty * hb;

				$row.find('.qty-input').val(qty.toFixed(2));
				$row.find('.total-input').val(total.toFixed(2));

				calculateTotal();
			}

			function calculateTotal() {
				let totalQty = 0;

				$('#tbody-detail tr').each(function() {
					if (!$(this).find('td').first().attr('colspan')) {
						let qty = parseFloat($(this).find('.qty-input').val()) || 0;
						totalQty += qty;
					}
				});

				$('#total_qty').val(totalQty.toFixed(2));
			}

			// Delete Row
			$(document).on('click', '.btn-delete-row', function() {
				$(this).closest('tr').remove();

				if ($('#tbody-detail tr').length === 0) {
					$('#tbody-detail').html('<tr><td colspan="11" class="text-center">Tidak ada data</td></tr>');
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
						$('#tbody-detail').html('<tr><td colspan="11" class="text-center">Tidak ada data</td></tr>');
						rowIndex = 0;
						$('#total_qty').val('0.00');
					}
				});
			});

			// Save
			$('#btn-save').click(function(e) {
				e.preventDefault();

				let tgl = $('input[name="tgl"]').val();

				if (!tgl) {
					Swal.fire('Peringatan', 'Tanggal harus diisi', 'warning');
					$('input[name="tgl"]').focus();
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
							bktk: $('#bktk').val().trim(),
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
										kd_brg: kdBrg,
										na_brg: $(this).find('input[name*="[na_brg]"]').val(),
										stok: parseFloat($(this).find('input[name*="[stok]"]').val()) || 0,
										riil: parseFloat($(this).find('input[name*="[riil]"]').val()) || 0,
										qty: parseFloat($(this).find('input[name*="[qty]"]').val()) || 0,
										hb: parseFloat($(this).find('input[name*="[hb]"]').val()) || 0,
										total: parseFloat($(this).find('input[name*="[total]"]').val()) || 0,
										ket: $(this).find('input[name*="[ket]"]').val() || ''
									});
								}
							}
						});

						return $.ajax({
							url: "{{ route('tkoreksistokopname.store') }}",
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
							window.location.href = "{{ route('tkoreksistokopname') }}";
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
				calculateTotal();
			@endif
		});
	</script>
@endsection
