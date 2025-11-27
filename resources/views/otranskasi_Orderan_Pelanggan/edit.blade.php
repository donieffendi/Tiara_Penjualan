@extends('layouts.plain')

@push('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $status == 'simpan' ? 'New' : 'Edit' }} Orderan Pelanggan</h1>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<form id="form-orderan" method="POST">
					@csrf
					<input type="hidden" name="status" value="{{ $status }}">

					<div class="card">
						<div class="card-header">
							<div class="row">
								<div class="col-md-6">
									<button type="button" class="btn btn-sm btn-success" id="btn-save">
										<i class="fas fa-save"></i> Save
									</button>
									<a href="{{ route('torderanpelanggan') }}" class="btn btn-sm btn-danger">
										<i class="fas fa-times"></i> Exit
									</a>
								</div>
							</div>
						</div>

						<div class="card-body">
							<div class="row">
								<div class="col-md-4">
									<div class="form-group row">
										<label class="col-sm-4 col-form-label">No Bukti</label>
										<div class="col-sm-8">
											<input type="text" class="form-control form-control-sm" name="no_bukti" value="{{ $header->no_bukti ?? '+' }}" readonly
												style="background-color: #e9ecef;">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group row">
										<label class="col-sm-4 col-form-label">Tanggal</label>
										<div class="col-sm-8">
											<input type="date" class="form-control form-control-sm" name="tgl" value="{{ $header->tgl ?? date('Y-m-d') }}" required>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group row">
										<label class="col-sm-5 col-form-label">Total Qty</label>
										<div class="col-sm-7">
											<input type="text" class="form-control form-control-sm text-right" id="total_qty"
												value="{{ number_format($header->total_qty ?? 0, 2, ',', '.') }}" readonly style="background-color: #fff3cd; font-weight: bold;">
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12">
									<div class="form-group row">
										<label class="col-sm-1 col-form-label">Notes</label>
										<div class="col-sm-11">
											<input type="text" class="form-control form-control-sm" name="notes" id="txtnotes" value="{{ $header->ket ?? '' }}">
										</div>
									</div>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-sm-3 col-form-label">Kode Barang</label>
										<div class="col-sm-6">
											<input type="text" class="form-control form-control-sm" id="kd_brg" placeholder="Kode Barang">
										</div>
										<div class="col-sm-3">
											<button type="button" class="btn btn-info btn-sm btn-block" id="btn-browse">
												<i class="fas fa-search"></i>
											</button>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-sm-2 col-form-label">Qty</label>
										<div class="col-sm-4">
											<input type="number" class="form-control form-control-sm text-right" id="qty" step="0.01">
										</div>
										<div class="col-sm-6">
											<span id="info-barang" class="text-info small"></span>
										</div>
									</div>
								</div>
							</div>

							<div class="table-responsive mt-3">
								<table class="table-bordered table-striped table-sm table" id="table-detail">
									<thead class="thead-dark">
										<tr>
											<th width="5%" class="text-center">No</th>
											<th width="12%" class="text-center">Kode</th>
											<th width="30%" class="text-center">Nama Barang</th>
											<th width="15%" class="text-center">Kemasan</th>
											<th width="10%" class="text-center">Qty</th>
											<th width="10%" class="text-center">Stok</th>
											<th width="13%" class="text-center">Notes</th>
											<th width="5%" class="text-center">
												<button type="button" class="btn btn-xs btn-danger" id="btn-clear-all" title="Clear All">
													<i class="fas fa-trash"></i>
												</button>
											</th>
										</tr>
									</thead>
									<tbody id="tbody-detail">
										@if (!empty($detail) && count($detail) > 0)
											@foreach ($detail as $key => $row)
												<tr data-no-id="{{ $row->no_id ?? 0 }}">
													<td class="text-center">{{ $key + 1 }}</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][kd_brg]" value="{{ $row->kd_brg }}"
															readonly style="background-color: #e9ecef;">
														<input type="hidden" name="detail[{{ $key }}][no_id]" value="{{ $row->no_id ?? 0 }}">
														<input type="hidden" name="detail[{{ $key }}][rec]" value="{{ $key + 1 }}">
														<input type="hidden" name="detail[{{ $key }}][kdlaku]" value="{{ $row->kdlaku ?? '' }}">
														<input type="hidden" name="detail[{{ $key }}][sub]" value="{{ $row->sub ?? '' }}">
														<input type="hidden" name="detail[{{ $key }}][kdbar]" value="{{ $row->kdbar ?? '' }}">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][na_brg]" value="{{ $row->na_brg }}"
															readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][ket_kem]"
															value="{{ $row->ket_kem ?? '' }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" class="form-control form-control-sm qty text-right" name="detail[{{ $key }}][qty]"
															value="{{ $row->qty }}" step="0.01">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm stok text-right" name="detail[{{ $key }}][stok]"
															value="{{ $row->stok ?? 0 }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][notes]" value="{{ $row->ket ?? '' }}">
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
												<td colspan="8" class="text-center">Tidak ada data</td>
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

	<div class="modal fade" id="modal-browse" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Browse Barang</h5>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<input type="text" class="form-control" id="search-barang" placeholder="Cari barang... (min 2 karakter)">
					</div>
					<div class="table-responsive">
						<table class="table-bordered table-sm table">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Nama Barang</th>
									<th>Kemasan</th>
									<th>Stok</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="tbody-browse">
								<tr>
									<td colspan="5" class="text-center">Ketik untuk mencari...</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		$(document).ready(function() {
			let rowIndex = {{ count($detail) }};

			@if (isset($error) && $error)
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: '{{ $error }}',
					confirmButtonText: 'OK'
				});
			@endif

			function formatNumber(num) {
				return parseFloat(num).toLocaleString('id-ID', {
					minimumFractionDigits: 2,
					maximumFractionDigits: 2
				});
			}

			function parseNumber(str) {
				if (!str) return 0;
				return parseFloat(str.toString().replace(/\./g, '').replace(',', '.')) || 0;
			}

			function calculateTotal() {
				let totalQty = 0;

				$('#tbody-detail tr').each(function() {
					if (!$(this).find('td').first().attr('colspan')) {
						let qty = parseNumber($(this).find('.qty').val());
						totalQty += qty;
					}
				});

				$('#total_qty').val(formatNumber(totalQty));
			}

			$(document).on('input', '.qty', function() {
				calculateTotal();
			});

			$('#kd_brg').on('keydown', function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					processBarang();
				}
			});

			$('#qty').on('keydown', function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					addBarangToGrid();
				}
			});

			$('#txtnotes').on('keydown', function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					$('#kd_brg').focus();
				}
			});

			$('#btn-browse').click(function() {
				$('#search-barang').val('');
				$('#tbody-browse').html('<tr><td colspan="5" class="text-center">Ketik untuk mencari...</td></tr>');
				$('#modal-browse').modal('show');
			});

			let searchTimeout;
			$('#search-barang').on('keyup', function() {
				clearTimeout(searchTimeout);
				let q = $(this).val();

				if (q.length >= 2) {
					searchTimeout = setTimeout(function() {
						searchBarang(q);
					}, 500);
				} else {
					$('#tbody-browse').html('<tr><td colspan="5" class="text-center">Ketik minimal 2 karakter...</td></tr>');
				}
			});

			function searchBarang(q) {
				$('#tbody-browse').html(
					'<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

				$.ajax({
					url: "{{ route('torderanpelanggan.browse') }}",
					data: {
						q: q
					},
					success: function(data) {
						let html = '';
						if (data.length > 0) {
							data.forEach(function(item) {
								html += '<tr>';
								html += '<td>' + item.kd_brg + '</td>';
								html += '<td>' + item.na_brg + '</td>';
								html += '<td>' + (item.ket_kem || '') + '</td>';
								html += '<td class="text-right">' + parseFloat(item.stok || 0).toFixed(2) + '</td>';
								html += '<td><button type="button" class="btn btn-xs btn-primary btn-select" data-kd="' + item
									.kd_brg + '">Pilih</button></td>';
								html += '</tr>';
							});
						} else {
							html = '<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>';
						}
						$('#tbody-browse').html(html);
					},
					error: function(xhr) {
						$('#tbody-browse').html('<tr><td colspan="5" class="text-center text-danger">Error: ' + (xhr
							.responseJSON?.message || 'Terjadi kesalahan') + '</td></tr>');
					}
				});
			}

			$(document).on('click', '.btn-select', function() {
				let kdBrg = $(this).data('kd');
				$('#kd_brg').val(kdBrg);
				$('#modal-browse').modal('hide');
				processBarang();
			});

			function processBarang() {
				let kdBrg = $('#kd_brg').val().trim().toUpperCase();

				if (!kdBrg) return;

				$('#kd_brg').val(kdBrg);
				$('#info-barang').html('<i class="fas fa-spinner fa-spin"></i> Loading...');

				$.ajax({
					url: "{{ route('torderanpelanggan.detail') }}",
					data: {
						kd_brg: kdBrg
					},
					success: function(response) {
						if (response.success && response.exists && response.data) {
							let data = response.data;
							window.currentBarang = data;
							$('#info-barang').text(data.na_brg + ' | Stok: ' + parseFloat(data.stok || 0).toFixed(2));
							$('#qty').val('').focus();
						} else {
							Swal.fire('Error', response.message || 'Barang tidak ditemukan', 'error');
							$('#kd_brg').val('');
							$('#info-barang').text('');
						}
					},
					error: function(xhr) {
						Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
						$('#kd_brg').val('');
						$('#info-barang').text('');
					}
				});
			}

			function addBarangToGrid() {
				let kdBrg = $('#kd_brg').val().trim().toUpperCase();
				let qty = parseFloat($('#qty').val()) || 0;

				if (!kdBrg || qty <= 0 || !window.currentBarang) {
					if (!kdBrg) {
						Swal.fire('Peringatan', 'Kode barang harus diisi', 'warning');
					} else if (qty <= 0) {
						Swal.fire('Peringatan', 'Qty harus lebih dari 0', 'warning');
					}
					return;
				}

				let exists = false;
				$('#tbody-detail tr').each(function() {
					if ($(this).find('input[name*="[kd_brg]"]').val() == kdBrg) {
						$(this).find('.qty').val(qty);
						exists = true;
						return false;
					}
				});

				if (!exists) {
					let data = window.currentBarang;

					let newRow = `
                <tr data-no-id="0">
                    <td class="text-center">${rowIndex + 1}</td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][kd_brg]" value="${data.kd_brg}" readonly style="background-color: #e9ecef;">
                        <input type="hidden" name="detail[${rowIndex}][no_id]" value="0">
                        <input type="hidden" name="detail[${rowIndex}][rec]" value="${rowIndex + 1}">
                        <input type="hidden" name="detail[${rowIndex}][kdlaku]" value="${data.kdlaku || ''}">
                        <input type="hidden" name="detail[${rowIndex}][sub]" value="${data.sub || ''}">
                        <input type="hidden" name="detail[${rowIndex}][kdbar]" value="${data.kdbar || ''}">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][na_brg]" value="${data.na_brg}" readonly style="background-color: #e9ecef;">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][ket_kem]" value="${data.ket_kem || ''}" readonly style="background-color: #e9ecef;">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm qty text-right" name="detail[${rowIndex}][qty]" value="${qty}" step="0.01">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm stok text-right" name="detail[${rowIndex}][stok]" value="${parseFloat(data.stok || 0).toFixed(2)}" readonly style="background-color: #e9ecef;">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][notes]">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-danger btn-delete-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

					if ($('#tbody-detail tr td').first().attr('colspan')) {
						$('#tbody-detail').html(newRow);
					} else {
						$('#tbody-detail').append(newRow);
					}
					rowIndex++;
				}

				$('#kd_brg').val('');
				$('#qty').val('');
				$('#info-barang').text('');
				$('#kd_brg').focus();

				calculateTotal();
			}

			$(document).on('click', '.btn-delete-row', function() {
				$(this).closest('tr').remove();
				calculateTotal();

				if ($('#tbody-detail tr').length === 0) {
					$('#tbody-detail').html('<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>');
				}

				$('#tbody-detail tr').each(function(index) {
					if (!$(this).find('td').first().attr('colspan')) {
						$(this).find('td:first').text(index + 1);
						$(this).find('input[name*="[rec]"]').val(index + 1);
					}
				});
			});

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
						$('#tbody-detail').html('<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>');
						calculateTotal();
						rowIndex = 0;
					}
				});
			});

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
							return false;
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
							notes: $('input[name="notes"]').val(),
							details: []
						};

						$('#tbody-detail tr').each(function(index) {
							if (!$(this).find('td').first().attr('colspan')) {
								let qty = parseNumber($(this).find('.qty').val());
								formData.details.push({
									no_id: $(this).find('input[name*="[no_id]"]').val() || 0,
									rec: index + 1,
									kd_brg: $(this).find('input[name*="[kd_brg]"]').val(),
									na_brg: $(this).find('input[name*="[na_brg]"]').val(),
									ket_kem: $(this).find('input[name*="[ket_kem]"]').val(),
									kdlaku: $(this).find('input[name*="[kdlaku]"]').val(),
									sub: $(this).find('input[name*="[sub]"]').val(),
									kdbar: $(this).find('input[name*="[kdbar]"]').val(),
									qty: qty,
									notes: $(this).find('input[name*="[notes]"]').val()
								});
							}
						});

						return $.ajax({
							url: "{{ route('torderanpelanggan.store') }}",
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
							text: result.value.message || 'Data berhasil disimpan',
							icon: 'success',
							confirmButtonText: 'OK'
						}).then(() => {
							window.location.href = "{{ route('torderanpelanggan') }}";
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

			calculateTotal();
		});
	</script>
@endsection
