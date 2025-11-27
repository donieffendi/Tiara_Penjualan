@extends('layouts.plain')

@push('styles')
	<!-- SweetAlert2 CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $status == 'simpan' ? 'New' : 'Edit' }} Koreksi Toko Manual</h1>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<form id="form-koreksi" method="POST">
					@csrf
					<input type="hidden" name="status" value="{{ $status }}">

					<div class="card">
						<div class="card-header">
							<div class="row">
								<div class="col-md-6">
									<button type="button" class="btn btn-sm btn-success" id="btn-save">
										<i class="fas fa-save"></i> Save
									</button>
									<a href="{{ route('tkoreksitokomanual') }}" class="btn btn-sm btn-danger">
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
								<div class="col-md-4">
									<div class="form-group">
										<label>Notes</label>
										<input type="text" class="form-control form-control-sm" name="notes" value="{{ $header->notes ?? '' }}" placeholder="Keterangan">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Total Qty</label>
										<input type="text" class="form-control form-control-sm text-right" id="total_qty"
											value="{{ number_format($header->total_qty ?? 0, 2, ',', '.') }}" readonly style="background-color: #fff3cd; font-weight: bold;">
									</div>
								</div>
							</div>

							<hr>

							<div class="row mt-3">
								<div class="col-md-3">
									<div class="form-group">
										<label>Barcode / Kode Barang</label>
										<div class="input-group input-group-sm">
											<input type="text" class="form-control" id="kd_brg" placeholder="Scan barcode atau ketik kode">
											<div class="input-group-append">
												<button type="button" class="btn btn-info" id="btn-browse">
													<i class="fas fa-search"></i>
												</button>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Qty</label>
										<input type="number" class="form-control form-control-sm text-right" id="qty" step="0.01" placeholder="0.00">
									</div>
								</div>
								<div class="col-md-5">
									<div class="form-group">
										<label>Keterangan</label>
										<input type="text" class="form-control form-control-sm" id="ket" placeholder="Keterangan barang">
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
											<th width="12%" class="text-center">Barcode</th>
											<th width="10%" class="text-center">Kode</th>
											<th width="24%" class="text-center">Nama Barang</th>
											<th width="12%" class="text-center">Kemasan</th>
											<th width="8%" class="text-center">Qty</th>
											<th width="10%" class="text-center">Harga</th>
											<th width="10%" class="text-center">Total</th>
											<th width="15%" class="text-center">Ket</th>
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
														<input type="text" class="form-control form-control-sm" value="{{ $row->barcode ?? '' }}" readonly
															style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][kd_brg]" value="{{ $row->kd_brg }}"
															readonly style="background-color: #e9ecef;">
														<input type="hidden" name="detail[{{ $key }}][no_id]" value="{{ $row->no_id ?? 0 }}">
														<input type="hidden" name="detail[{{ $key }}][rec]" value="{{ $key + 1 }}">
														<input type="hidden" name="detail[{{ $key }}][barcode]" value="{{ $row->barcode ?? '' }}">
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
														<input type="number" class="form-control form-control-sm hb text-right" name="detail[{{ $key }}][hb]"
															value="{{ $row->hb }}" step="0.01" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm total text-right" readonly style="background-color: #e9ecef;"
															value="{{ number_format($row->qty * $row->hb, 0, ',', '.') }}">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][ket]" value="{{ $row->ket ?? '' }}">
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
												<td colspan="10" class="text-center">Tidak ada data</td>
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
									<th>Barcode</th>
									<th>Kode</th>
									<th>Nama Barang</th>
									<th>Kemasan</th>
									<th>HB</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="tbody-browse">
								<tr>
									<td colspan="6" class="text-center">Ketik untuk mencari...</td>
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
	<!-- SweetAlert2 JS -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

				$('#tbody-detail tr').each(function() {
					if (!$(this).find('td').first().attr('colspan')) {
						let qty = parseNumber($(this).find('.qty').val());
						let hb = parseNumber($(this).find('.hb').val());
						let total = qty * hb;

						$(this).find('.total').val(formatRupiah(total));
						totalQty += qty;
					}
				});

				$('#total_qty').val(formatNumber(totalQty));
			}

			$(document).on('input', '.qty', function() {
				calculateTotal();
			});

			// Enter navigation untuk barcode/kode barang
			$('#kd_brg').on('keydown', function(e) {
				if (e.keyCode == 13) { // Enter key
					e.preventDefault();
					let kdBrg = $(this).val().trim();

					if (kdBrg) {
						// Jika ada input, langsung process barang
						processBarang();
					} else {
						// Jika kosong, buka popup browse
						$('#btn-browse').click();
					}
				}
			});

			$('#qty').on('keydown', function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					$('#ket').focus();
				}
			});

			$('#ket').on('keydown', function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					addBarangToGrid();
				}
			});

			$('#btn-add').click(function() {
				addBarangToGrid();
			});

			// Browse - buka popup untuk memilih barang
			$('#btn-browse').click(function() {
				$('#search-barang').val('');
				$('#tbody-browse').html(
					'<tr><td colspan="6" class="text-center"><i class="fas fa-search"></i> Ketik untuk mencari barang...</td></tr>');
				$('#modal-browse').modal('show');

				// Auto focus ke search box setelah modal terbuka
				setTimeout(function() {
					$('#search-barang').focus();
				}, 500);
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
					$('#tbody-browse').html('<tr><td colspan="6" class="text-center">Ketik minimal 2 karakter...</td></tr>');
				}
			});

			function searchBarang(q) {
				$('#tbody-browse').html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

				$.ajax({
					url: "{{ route('tkoreksitokomanual.browse') }}",
					data: {
						q: q
					},
					success: function(data) {
						let html = '';
						if (data.length > 0) {
							data.forEach(function(item) {
								html += '<tr>';
								html += '<td>' + (item.barcode || '') + '</td>';
								html += '<td>' + item.kd_brg + '</td>';
								html += '<td>' + item.na_brg + '</td>';
								html += '<td>' + (item.ket_kem || '') + '</td>';
								html += '<td class="text-right">' + parseFloat(item.hb || 0).toFixed(0) + '</td>';
								html += '<td><button type="button" class="btn btn-xs btn-primary btn-select" data-kd="' + item
									.kd_brg + '">Pilih</button></td>';
								html += '</tr>';
							});
						} else {
							html = '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>';
						}
						$('#tbody-browse').html(html);
					},
					error: function(xhr) {
						$('#tbody-browse').html('<tr><td colspan="6" class="text-center text-danger">Error: ' + (xhr.responseJSON
							?.message || 'Terjadi kesalahan') + '</td></tr>');
					}
				});
			}

			// Select barang dari popup browse
			$(document).on('click', '.btn-select', function() {
				let kdBrg = $(this).data('kd');
				$('#kd_brg').val(kdBrg);
				$('#modal-browse').modal('hide');

				// Delay sedikit agar modal sempat tertutup
				setTimeout(function() {
					processBarang();
				}, 300);
			});

			// Process barcode/kode barang (manual entry + Enter)
			function processBarang() {
				let kdBrg = $('#kd_brg').val().trim().toUpperCase();

				if (!kdBrg) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Silakan masukkan barcode atau kode barang',
						confirmButtonText: 'OK'
					});
					return;
				}

				$('#kd_brg').val(kdBrg);
				$('#info-barang').hide();

				// Show loading indicator
				Swal.fire({
					title: 'Mencari barang...',
					text: 'Mohon tunggu',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				$.ajax({
					url: "{{ route('tkoreksitokomanual.detail') }}",
					data: {
						kd_brg: kdBrg
					},
					success: function(response) {
						Swal.close(); // Close loading

						if (response.success && response.exists && response.data) {
							let data = response.data;
							window.currentBarang = data;

							// Show info barang
							$('#info-text').html(
								'<strong>' + data.na_brg + '</strong> | ' +
								'Kemasan: ' + (data.ket_kem || '-') + ' | ' +
								'HB: Rp ' + parseFloat(data.hb || 0).toLocaleString('id-ID')
							);
							$('#info-barang').removeClass('alert-info alert-danger').addClass('alert-success').show();

							// Auto focus ke qty
							$('#qty').val('').focus();

							// Play success sound (optional)
							// new Audio('/sounds/beep.mp3').play();
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Barang Tidak Ditemukan',
								text: 'Barcode/Kode: ' + kdBrg + ' tidak ditemukan dalam database',
								confirmButtonText: 'OK'
							}).then(() => {
								$('#kd_brg').val('').focus();
							});
							window.currentBarang = null;
						}
					},
					error: function(xhr) {
						Swal.close(); // Close loading

						let errorMsg = 'Terjadi kesalahan saat mencari barang';
						if (xhr.responseJSON && xhr.responseJSON.message) {
							errorMsg = xhr.responseJSON.message;
						}

						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: errorMsg,
							footer: 'Silakan coba lagi atau hubungi administrator',
							confirmButtonText: 'OK'
						}).then(() => {
							$('#kd_brg').val('').focus();
						});

						window.currentBarang = null;
					}
				});
			}

			function addBarangToGrid() {
				let kdBrg = $('#kd_brg').val().trim().toUpperCase();
				let qty = parseFloat($('#qty').val()) || 0;
				let ket = $('#ket').val().trim();

				// Validasi
				if (!kdBrg || qty <= 0 || !window.currentBarang) {
					if (!kdBrg) {
						Swal.fire({
							icon: 'warning',
							title: 'Peringatan',
							text: 'Silakan scan/pilih barang terlebih dahulu',
							confirmButtonText: 'OK'
						}).then(() => {
							$('#kd_brg').focus();
						});
					} else if (qty <= 0) {
						Swal.fire({
							icon: 'warning',
							title: 'Peringatan',
							text: 'Qty harus lebih dari 0',
							confirmButtonText: 'OK'
						}).then(() => {
							$('#qty').focus();
						});
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Peringatan',
							text: 'Data barang tidak lengkap. Silakan scan ulang.',
							confirmButtonText: 'OK'
						}).then(() => {
							$('#kd_brg').val('').focus();
						});
					}
					return;
				}

				let exists = false;
				$('#tbody-detail tr').each(function() {
					if ($(this).find('input[name*="[kd_brg]"]').val() == kdBrg) {
						$(this).find('.qty').val(qty);
						$(this).find('input[name*="[ket]"]').val(ket);
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
                            <input type="text" class="form-control form-control-sm" value="${data.barcode || ''}" readonly style="background-color: #e9ecef;">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][kd_brg]" value="${data.kd_brg}" readonly style="background-color: #e9ecef;">
                            <input type="hidden" name="detail[${rowIndex}][no_id]" value="0">
                            <input type="hidden" name="detail[${rowIndex}][rec]" value="${rowIndex + 1}">
                            <input type="hidden" name="detail[${rowIndex}][barcode]" value="${data.barcode || ''}">
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
                            <input type="number" class="form-control form-control-sm hb text-right" name="detail[${rowIndex}][hb]" value="${data.hb}" step="0.01" readonly style="background-color: #e9ecef;">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm total text-right" readonly style="background-color: #e9ecef;" value="${formatRupiah(qty * data.hb)}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][ket]" value="${ket}">
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

				// Clear form dan kembali ke barcode
				$('#kd_brg').val('');
				$('#qty').val('');
				$('#ket').val('');
				$('#info-barang').hide();
				window.currentBarang = null;

				// Focus kembali ke kd_brg untuk scan berikutnya
				$('#kd_brg').focus();

				calculateTotal();

				// Show success notification (toast style)
				Swal.fire({
					icon: 'success',
					title: 'Item ditambahkan',
					toast: true,
					position: 'top-end',
					showConfirmButton: false,
					timer: 1500,
					timerProgressBar: true
				});
			}

			// Delete Row
			$(document).on('click', '.btn-delete-row', function() {
				$(this).closest('tr').remove();
				calculateTotal();

				if ($('#tbody-detail tr').length === 0) {
					$('#tbody-detail').html('<tr><td colspan="10" class="text-center">Tidak ada data</td></tr>');
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
						$('#tbody-detail').html('<tr><td colspan="10" class="text-center">Tidak ada data</td></tr>');
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
							notes: $('input[name="notes"]').val(),
							details: []
						};

						$('#tbody-detail tr').each(function(index) {
							if (!$(this).find('td').first().attr('colspan')) {
								let qty = parseNumber($(this).find('.qty').val());
								let hb = parseNumber($(this).find('.hb').val());

								if (qty > 0) {
									formData.details.push({
										no_id: $(this).find('input[name*="[no_id]"]').val() || 0,
										rec: index + 1,
										kd_brg: $(this).find('input[name*="[kd_brg]"]').val(),
										na_brg: $(this).find('input[name*="[na_brg]"]').val(),
										ket_kem: $(this).find('input[name*="[ket_kem]"]').val(),
										barcode: $(this).find('input[name*="[barcode]"]').val(),
										qty: qty,
										hb: hb,
										ket: $(this).find('input[name*="[ket]"]').val() || '',
										bktk: $(this).data('bktk') || ''
									});
								}
							}
						});

						return $.ajax({
							url: "{{ route('tkoreksitokomanual.store') }}",
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
							window.location.href = "{{ route('tkoreksitokomanual') }}";
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

			// Auto focus ke kd_brg saat halaman load
			$('#kd_brg').focus();
		});
	</script>
@endsection
