@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $status == 'simpan' ? 'New' : 'Edit' }} Orderan Toko GD Transit</h1>
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
									<a href="{{ route('torderantokogdtransit') }}" class="btn btn-sm btn-danger">
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
								<div class="col-md-2">
									<div class="form-group">
										<label>Total Qty</label>
										<input type="text" class="form-control form-control-sm text-right" id="total_qty"
											value="{{ number_format($header->total_qty ?? 0, 2, ',', '.') }}" readonly style="background-color: #fff3cd; font-weight: bold;">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Total Harga</label>
										<input type="text" class="form-control form-control-sm text-right" id="total_harga"
											value="{{ number_format($header->total_harga ?? 0, 0, ',', '.') }}" readonly style="background-color: #fff3cd; font-weight: bold;">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Nett</label>
										<input type="text" class="form-control form-control-sm text-right" id="total_nett"
											value="{{ number_format($header->total_nett ?? 0, 0, ',', '.') }}" readonly style="background-color: #fff3cd; font-weight: bold;">
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12">
									<hr>
									<h5 class="mb-3"><i class="fas fa-download"></i> Ambil Data Otomatis</h5>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Sub (Dari)</label>
										<input type="text" class="form-control form-control-sm" id="sub1" placeholder="Contoh: 001">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Sub (Sampai)</label>
										<input type="text" class="form-control form-control-sm" id="sub2" placeholder="Contoh: 999">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>&nbsp;</label>
										<button type="button" class="btn btn-info btn-sm btn-block" id="btn-ambil-data">
											<i class="fas fa-sync-alt"></i> Ambil Data
										</button>
									</div>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-md-12">
									<hr>
									<h5 class="mb-3"><i class="fas fa-edit"></i> Update Qty Cepat (by Sub Item)</h5>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Sub Item</label>
										<input type="text" class="form-control form-control-sm" id="sub_item_update" placeholder="Contoh: 001">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Qty Baru</label>
										<input type="number" class="form-control form-control-sm text-right" id="qty_update" step="0.01" placeholder="0.00">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>&nbsp;</label>
										<button type="button" class="btn btn-warning btn-sm btn-block" id="btn-update-qty">
											<i class="fas fa-pen"></i> Update Qty
										</button>
									</div>
								</div>
							</div>

							<hr>

							<div class="row mt-3">
								<div class="col-md-5">
									<div class="form-group">
										<label>Kode Barang</label>
										<div class="input-group input-group-sm">
											<input type="text" class="form-control" id="kd_brg" placeholder="Kode Barang">
											<div class="input-group-append">
												<button type="button" class="btn btn-info" id="btn-browse">
													<i class="fas fa-search"></i>
												</button>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Qty</label>
										<input type="number" class="form-control form-control-sm text-right" id="qty" step="0.01" placeholder="0.00">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Stok DC</label>
										<input type="text" class="form-control form-control-sm text-right" id="stok_dc" readonly style="background-color: #e9ecef;">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>&nbsp;</label>
										<button type="button" class="btn btn-success btn-sm btn-block" id="btn-add">
											<i class="fas fa-plus"></i> Add
										</button>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12">
									<div id="info-barang" class="alert alert-info py-2" style="display:none;">
										<small><i class="fas fa-info-circle"></i> <span id="info-text"></span></small>
									</div>
								</div>
							</div>

							<div class="table-responsive mt-3">
								<table class="table-bordered table-striped table-sm table" id="table-detail">
									<thead class="thead-dark">
										<tr>
											<th width="4%" class="text-center">No</th>
											<th width="10%" class="text-center">Kode</th>
											<th width="28%" class="text-center">Nama Barang</th>
											<th width="13%" class="text-center">Kemasan</th>
											<th width="9%" class="text-center">Qty</th>
											<th width="10%" class="text-center">Harga</th>
											<th width="10%" class="text-center">Total</th>
											<th width="8%" class="text-center">Stok DC</th>
											<th width="8%" class="text-center">
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
														<input type="hidden" name="detail[{{ $key }}][ket_uk]" value="{{ $row->ket_uk ?? '' }}">
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
														<input type="number" class="form-control form-control-sm harga text-right" name="detail[{{ $key }}][harga]"
															value="{{ $row->harga }}" step="0.01" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm total text-right" readonly style="background-color: #e9ecef;"
															value="{{ number_format($row->qty * $row->harga, 0, ',', '.') }}">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm stok text-right" value="{{ number_format($row->stok ?? 0, 2, '.', '') }}"
															readonly style="background-color: #e9ecef;">
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

	<!-- Modal Browse -->
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
									<th>Stok DC</th>
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
	<script>
		$(document).ready(function() {
			let rowIndex = {{ count($detail ?? []) }};

			function formatNumber(num) {
				return parseFloat(num).toLocaleString('id-ID', {
					minimumFractionDigits: 2,
					maximumFractionDigits: 2
				});
			}

			function formatRupiah(num) {
				return parseFloat(num).toLocaleString('id-ID', {
					minimumFractionDigits: 0,
					maximumFractionDigits: 0
				});
			}

			function parseNumber(str) {
				if (!str) return 0;
				return parseFloat(str.toString().replace(/\./g, '').replace(',', '.')) || 0;
			}

			function calculateTotal() {
				let totalQty = 0;
				let totalHarga = 0;

				$('#tbody-detail tr').each(function() {
					if (!$(this).find('td').first().attr('colspan')) {
						let qty = parseNumber($(this).find('.qty').val());
						let harga = parseNumber($(this).find('.harga').val());
						let total = qty * harga;

						$(this).find('.total').val(formatRupiah(total));

						totalQty += qty;
						totalHarga += total;
					}
				});

				$('#total_qty').val(formatNumber(totalQty));
				$('#total_harga').val(formatRupiah(totalHarga));
				$('#total_nett').val(formatRupiah(totalHarga));
			}

			$(document).on('input', '.qty', function() {
				calculateTotal();
			});

			// Enter navigation
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

			// Enter navigation for Quick Update
			$('#sub_item_update').on('keydown', function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					$('#qty_update').focus();
				}
			});

			$('#qty_update').on('keydown', function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					$('#btn-update-qty').click();
				}
			});

			$('#btn-add').click(function() {
				addBarangToGrid();
			});

			// Browse
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
				$('#tbody-browse').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

				$.ajax({
					url: "{{ route('torderantokogdtransit.browse') }}",
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
						$('#tbody-browse').html('<tr><td colspan="5" class="text-center text-danger">Error: ' + (xhr.responseJSON
							?.message || 'Terjadi kesalahan') + '</td></tr>');
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
				$('#info-barang').hide();
				$('#stok_dc').val('');

				$.ajax({
					url: "{{ route('torderantokogdtransit.detail') }}",
					data: {
						kd_brg: kdBrg
					},
					success: function(response) {
						if (response.success && response.exists && response.data) {
							let data = response.data;
							window.currentBarang = data;

							$('#info-text').text(data.na_brg + ' | Stok DC: ' + parseFloat(data.stok || 0).toFixed(2));
							$('#info-barang').show();
							$('#stok_dc').val(parseFloat(data.stok || 0).toFixed(2));

							$('#qty').val('').focus();
						} else {
							Swal.fire('Error', response.message || 'Barang tidak ditemukan', 'error');
							$('#kd_brg').val('');
							window.currentBarang = null;
						}
					},
					error: function(xhr) {
						Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
						$('#kd_brg').val('');
						window.currentBarang = null;
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
                        <input type="hidden" name="detail[${rowIndex}][ket_uk]" value="${data.ket_uk || ''}">
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
                        <input type="number" class="form-control form-control-sm harga text-right" name="detail[${rowIndex}][harga]" value="${data.harga}" step="0.01" readonly style="background-color: #e9ecef;">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm total text-right" readonly style="background-color: #e9ecef;" value="${formatRupiah(qty * data.harga)}">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm stok text-right" value="${parseFloat(data.stok || 0).toFixed(2)}" readonly style="background-color: #e9ecef;">
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
				$('#stok_dc').val('');
				$('#info-barang').hide();
				$('#kd_brg').focus();

				calculateTotal();
			}

			// Ambil Data Otomatis
			$('#btn-ambil-data').click(function() {
				let sub1 = $('#sub1').val().trim();
				let sub2 = $('#sub2').val().trim();

				if (!sub1 || !sub2) {
					Swal.fire('Peringatan', 'Sub (Dari) dan Sub (Sampai) harus diisi', 'warning');
					return;
				}

				Swal.fire({
					title: 'Loading...',
					text: 'Sedang mengambil data...',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				$.ajax({
					url: "{{ route('torderantokogdtransit.ambilData') }}",
					method: 'GET',
					data: {
						sub1: sub1,
						sub2: sub2
					},
					success: function(response) {
						Swal.close();

						// ambilData() returns direct array OR {success, message, data}
						let dataArray = Array.isArray(response) ? response : (response.data || []);

						if (dataArray.length > 0) {
							// Clear existing "no data" row
							if ($('#tbody-detail tr td').first().attr('colspan')) {
								$('#tbody-detail').html('');
							}

							dataArray.forEach(function(item) {
								let newRow = `
                            <tr data-no-id="0">
                                <td class="text-center">${rowIndex + 1}</td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][kd_brg]" value="${item.kd_brg}" readonly style="background-color: #e9ecef;">
                                    <input type="hidden" name="detail[${rowIndex}][no_id]" value="0">
                                    <input type="hidden" name="detail[${rowIndex}][rec]" value="${rowIndex + 1}">
                                    <input type="hidden" name="detail[${rowIndex}][kdlaku]" value="${item.kdlaku || ''}">
                                    <input type="hidden" name="detail[${rowIndex}][sub]" value="${item.sub || ''}">
                                    <input type="hidden" name="detail[${rowIndex}][kdbar]" value="${item.kdbar || ''}">
                                    <input type="hidden" name="detail[${rowIndex}][ket_uk]" value="${item.ket_uk || ''}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][na_brg]" value="${item.na_brg}" readonly style="background-color: #e9ecef;">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][ket_kem]" value="${item.ket_kem || ''}" readonly style="background-color: #e9ecef;">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm qty text-right" name="detail[${rowIndex}][qty]" value="${item.qty}" step="0.01">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm harga text-right" name="detail[${rowIndex}][harga]" value="${item.harga}" step="0.01" readonly style="background-color: #e9ecef;">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm total text-right" readonly style="background-color: #e9ecef;" value="${formatRupiah(item.qty * item.harga)}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm stok text-right" value="${parseFloat(item.stok || 0).toFixed(2)}" readonly style="background-color: #e9ecef;">
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

							calculateTotal();
							Swal.fire('Berhasil', 'Data berhasil dimuat: ' + dataArray.length + ' item', 'success');
						} else {
							Swal.fire('Info', 'Tidak ada data yang memenuhi kriteria', 'info');
						}
					},
					error: function(xhr) {
						Swal.close();
						Swal.fire('Error', xhr.responseJSON?.message || 'Gagal mengambil data', 'error');
					}
				});
			});

			// Update Qty by Sub Item
			$('#btn-update-qty').click(function() {
				let subItem = $('#sub_item_update').val().trim();
				let qtyBaru = parseFloat($('#qty_update').val());

				if (!subItem) {
					Swal.fire('Peringatan', 'Sub Item harus diisi', 'warning');
					$('#sub_item_update').focus();
					return;
				}

				if (isNaN(qtyBaru) || qtyBaru <= 0) {
					Swal.fire('Peringatan', 'Qty harus lebih dari 0', 'warning');
					$('#qty_update').focus();
					return;
				}

				// Find row with matching SUB
				let found = false;
				let updatedCount = 0;

				$('#tbody-detail tr').each(function() {
					if (!$(this).find('td').first().attr('colspan')) {
						let rowSub = $(this).find('input[name*="[sub]"]').val();

						if (rowSub && rowSub.trim() === subItem) {
							$(this).find('.qty').val(qtyBaru);
							found = true;
							updatedCount++;
						}
					}
				});

				if (found) {
					calculateTotal();

					Swal.fire({
						icon: 'success',
						title: 'Berhasil!',
						text: 'Qty berhasil diupdate untuk ' + updatedCount + ' item dengan Sub: ' + subItem,
						timer: 2000,
						showConfirmButton: false
					});

					// Clear inputs
					$('#sub_item_update').val('');
					$('#qty_update').val('');
					$('#sub_item_update').focus();
				} else {
					Swal.fire('Info', 'Tidak ada item dengan Sub: ' + subItem + ' di grid', 'info');
				}
			});

			// Delete Row
			$(document).on('click', '.btn-delete-row', function() {
				$(this).closest('tr').remove();
				calculateTotal();

				if ($('#tbody-detail tr').length === 0) {
					$('#tbody-detail').html('<tr><td colspan="9" class="text-center">Tidak ada data</td></tr>');
				}

				// Renumber rows
				$('#tbody-detail tr').each(function(index) {
					if (!$(this).find('td').first().attr('colspan')) {
						$(this).find('td:first').text(index + 1);
						$(this).find('input[name*="[rec]"]').val(index + 1);
					}
				});
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
						$('#tbody-detail').html('<tr><td colspan="9" class="text-center">Tidak ada data</td></tr>');
						calculateTotal();
						rowIndex = 0;
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
							details: []
						};

						$('#tbody-detail tr').each(function(index) {
							if (!$(this).find('td').first().attr('colspan')) {
								let qty = parseNumber($(this).find('.qty').val());
								let harga = parseNumber($(this).find('.harga').val());

								if (qty > 0) {
									formData.details.push({
										no_id: $(this).find('input[name*="[no_id]"]').val() || 0,
										rec: index + 1,
										kd_brg: $(this).find('input[name*="[kd_brg]"]').val(),
										na_brg: $(this).find('input[name*="[na_brg]"]').val(),
										ket_kem: $(this).find('input[name*="[ket_kem]"]').val(),
										kdlaku: $(this).find('input[name*="[kdlaku]"]').val(),
										sub: $(this).find('input[name*="[sub]"]').val(),
										kdbar: $(this).find('input[name*="[kdbar]"]').val(),
										ket_uk: $(this).find('input[name*="[ket_uk]"]').val(),
										qty: qty,
										harga: harga,
										notes: $(this).find('input[name*="[notes]"]').val() || ''
									});
								}
							}
						});

						return $.ajax({
							url: "{{ route('torderantokogdtransit.store') }}",
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
							window.location.href = "{{ route('torderantokogdtransit') }}";
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

			// Initial calculation
			calculateTotal();
		});
	</script>
@endsection
