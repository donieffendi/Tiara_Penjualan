@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $status == 'simpan' ? 'New' : 'Edit' }} SPKO ke DC Tunjungsari</h1>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<form id="form-spko" method="POST">
					@csrf
					<input type="hidden" name="status" value="{{ $status }}">
					<input type="hidden" name="no_bukti_old" value="{{ $no_bukti }}">

					<div class="card">
						<div class="card-header">
							<div class="row">
								<div class="col-md-6">
									<button type="button" class="btn btn-sm btn-success" id="btn-save" {{ $posted == 1 ? 'disabled' : '' }}>
										<i class="fas fa-save"></i> Save
									</button>
									<a href="{{ route('tspkokedctunjungsari') }}" class="btn btn-sm btn-danger">
										<i class="fas fa-times"></i> Exit
									</a>
								</div>
							</div>
						</div>

						<div class="card-body">
							<div class="row">
								<div class="col-md-3">
									<div class="form-group row">
										<label class="col-sm-4 col-form-label">No Bukti</label>
										<div class="col-sm-8">
											<input type="text" class="form-control form-control-sm" name="no_bukti" value="{{ $no_bukti }}" readonly
												style="background-color: #e9ecef;">
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group row">
										<label class="col-sm-4 col-form-label">Tanggal</label>
										<div class="col-sm-8">
											<input type="date" class="form-control form-control-sm" name="tgl" value="{{ $header->TGL ?? date('Y-m-d') }}" required
												{{ $posted == 1 ? 'readonly' : '' }}>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-sm-3 col-form-label">Notes</label>
										<div class="col-sm-9">
											<input type="text" class="form-control form-control-sm" name="notes" value="{{ $header->NOTES ?? '' }}"
												{{ $posted == 1 ? 'readonly' : '' }}>
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-3">
									<div class="form-group row">
										<label class="col-sm-4 col-form-label">Kirim ke</label>
										<div class="col-sm-8">
											<select class="form-control form-control-sm" name="cbg" id="cbg" required {{ $posted == 1 ? 'disabled' : '' }}>
												<option value="">-- Pilih Cabang --</option>
												@foreach ($cabang as $c)
													@if ($c->kode != $ma)
														<option value="{{ $c->kode }}" {{ ($header->CBG ?? $cbg) == $c->kode ? 'selected' : '' }}>
															{{ $c->kode }} - {{ $c->nama }}
														</option>
													@endif
												@endforeach
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group row">
										<label class="col-sm-4 col-form-label">Type</label>
										<div class="col-sm-8">
											<select class="form-control form-control-sm" name="golongan" id="golongan" required {{ $posted == 1 ? 'disabled' : '' }}>
												<option value="01" {{ ($header->golongan ?? '') == '01' ? 'selected' : '' }}>01-FOOD</option>
												<option value="02" {{ ($header->golongan ?? '') == '02' ? 'selected' : '' }}>02-NON FOOD</option>
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group row">
										<label class="col-sm-4 col-form-label">Pesan ke</label>
										<div class="col-sm-8">
											<select class="form-control form-control-sm" id="pesan_ke" {{ $posted == 1 ? 'disabled' : '' }}>
												<option value="">-- Pilih DC --</option>
												@foreach ($dc_list as $dc)
													<option value="{{ $dc->KODES }}">{{ $dc->KODES }} - {{ $dc->NAMAS }}</option>
												@endforeach
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group row">
										<label class="col-sm-5 col-form-label">Total Qty</label>
										<div class="col-sm-7">
											<input type="text" class="form-control form-control-sm text-right" id="total_qty"
												value="{{ number_format($header->TOTAL_QTY ?? 0, 2, ',', '.') }}" readonly style="background-color: #fff3cd; font-weight: bold;">
										</div>
									</div>
								</div>
							</div>

							<div class="row mt-2">
								<div class="col-md-12">
									<div class="card card-primary card-outline">
										<div class="card-header">
											<h5 class="card-title m-0">Proses Otomatis</h5>
										</div>
										<div class="card-body p-2">
											<div class="row">
												<div class="col-md-2">
													<div class="form-group row mb-1">
														<label class="col-sm-5 col-form-label">Untuk Lh</label>
														<div class="col-sm-7">
															<input type="number" class="form-control form-control-sm text-right" id="lph1" step="0.01" value="0"
																{{ $posted == 1 ? 'disabled' : '' }}>
														</div>
													</div>
												</div>
												<div class="col-md-2">
													<div class="form-group row mb-1">
														<label class="col-sm-3 col-form-label text-center">s/d</label>
														<div class="col-sm-9">
															<input type="number" class="form-control form-control-sm text-right" id="lph2" step="0.01" value="999"
																{{ $posted == 1 ? 'disabled' : '' }}>
														</div>
													</div>
												</div>
												<div class="col-md-2">
													<div class="form-group row mb-1">
														<label class="col-sm-7 col-form-label">Kebutuhan</label>
														<div class="col-sm-5">
															<input type="number" class="form-control form-control-sm text-right" id="butuh" value="7"
																{{ $posted == 1 ? 'disabled' : '' }}>
														</div>
													</div>
												</div>
												<div class="col-md-2">
													<div class="form-group row mb-1">
														<label class="col-sm-3 col-form-label">Sub</label>
														<div class="col-sm-9">
															<input type="text" class="form-control form-control-sm" id="sub1" value="001" {{ $posted == 1 ? 'disabled' : '' }}>
														</div>
													</div>
												</div>
												<div class="col-md-2">
													<div class="form-group row mb-1">
														<label class="col-sm-3 col-form-label text-center">s/d</label>
														<div class="col-sm-9">
															<input type="text" class="form-control form-control-sm" id="sub2" value="999" {{ $posted == 1 ? 'disabled' : '' }}>
														</div>
													</div>
												</div>
												<div class="col-md-2">
													@if ($posted != 1)
														<button type="button" class="btn btn-primary btn-sm btn-block" id="btn-proses">
															<i class="fas fa-cogs"></i> Proses
														</button>
													@endif
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-sm-3 col-form-label">Sub Item</label>
										<div class="col-sm-6">
											<input type="text" class="form-control form-control-sm" id="kd_brg" placeholder="Kode Barang"
												{{ $posted == 1 ? 'readonly' : '' }}>
										</div>
										<div class="col-sm-3">
											@if ($posted != 1)
												<button type="button" class="btn btn-info btn-sm btn-block" id="btn-browse">
													<i class="fas fa-search"></i>
												</button>
											@endif
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group row">
										<label class="col-sm-3 col-form-label">Qty</label>
										<div class="col-sm-9">
											<input type="number" class="form-control form-control-sm text-right" id="qty" step="0.01"
												{{ $posted == 1 ? 'readonly' : '' }}>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group row">
										<label class="col-sm-3 col-form-label">Stok</label>
										<div class="col-sm-9">
											<input type="text" class="form-control form-control-sm text-right" id="stok" readonly style="background-color: #e9ecef;">
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12">
									<span id="info-barang" class="text-info small"></span>
								</div>
							</div>

							<div class="table-responsive mt-3">
								<table class="table-bordered table-striped table-sm table" id="table-detail">
									<thead class="thead-dark">
										<tr>
											<th width="4%" class="text-center">No</th>
											<th width="10%" class="text-center">Kode</th>
											<th width="25%" class="text-center">Nama Barang</th>
											<th width="13%" class="text-center">Kemasan</th>
											<th width="8%" class="text-center">Qty</th>
											<th width="10%" class="text-center">Harga</th>
											<th width="10%" class="text-center">Total</th>
											<th width="8%" class="text-center">Stok</th>
											<th width="10%" class="text-center">Notes</th>
											@if ($posted != 1)
												<th width="5%" class="text-center">
													<button type="button" class="btn btn-xs btn-danger" id="btn-clear-all" title="Clear All">
														<i class="fas fa-trash"></i>
													</button>
												</th>
											@endif
										</tr>
									</thead>
									<tbody id="tbody-detail">
										@if (!empty($detail) && count($detail) > 0)
											@foreach ($detail as $key => $row)
												<tr data-no-id="{{ $row->NO_ID ?? 0 }}">
													<td class="text-center">{{ $key + 1 }}</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][kd_brg]" value="{{ $row->KD_BRG }}"
															readonly style="background-color: #e9ecef;">
														<input type="hidden" name="detail[{{ $key }}][no_id]" value="{{ $row->NO_ID ?? 0 }}">
														<input type="hidden" name="detail[{{ $key }}][rec]" value="{{ $key + 1 }}">
														<input type="hidden" name="detail[{{ $key }}][ket_uk]" value="{{ $row->KET_UK ?? '' }}">
														<input type="hidden" name="detail[{{ $key }}][kdlaku]" value="{{ $row->KDLAKU ?? '' }}">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][na_brg]" value="{{ $row->NA_BRG }}"
															readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][ket_kem]"
															value="{{ $row->ket_kem ?? '' }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" class="form-control form-control-sm qty text-right" name="detail[{{ $key }}][qty]"
															value="{{ $row->QTY }}" step="0.01" {{ $posted == 1 ? 'readonly' : '' }}>
													</td>
													<td>
														<input type="number" class="form-control form-control-sm harga text-right" name="detail[{{ $key }}][harga]"
															value="{{ $row->HARGA }}" step="0.01" {{ $posted == 1 ? 'readonly' : '' }}>
													</td>
													<td>
														<input type="text" class="form-control form-control-sm total-row text-right" value="{{ number_format($row->TOTAL, 2, ',', '.') }}"
															readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm text-right"
															value="{{ number_format($row->PMIN + $row->PMAX, 2, ',', '.') }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][notes]" value="{{ $row->KET ?? '' }}"
															{{ $posted == 1 ? 'readonly' : '' }}>
													</td>
													@if ($posted != 1)
														<td class="text-center">
															<button type="button" class="btn btn-xs btn-danger btn-delete-row">
																<i class="fas fa-trash"></i>
															</button>
														</td>
													@endif
												</tr>
											@endforeach
										@else
											<tr>
												<td colspan="{{ $posted != 1 ? '10' : '9' }}" class="text-center">Tidak ada data</td>
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
			let rowIndex = {{ count($detail) }};
			const isPosted = {{ $posted ?? 0 }};

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
						let harga = parseNumber($(this).find('.harga').val());
						let total = qty * harga;

						$(this).find('.total-row').val(formatNumber(total));
						totalQty += qty;
					}
				});

				$('#total_qty').val(formatNumber(totalQty));
			}

			$(document).on('input', '.qty, .harga', function() {
				if (isPosted != 1) {
					calculateTotal();
				}
			});

			$('#cbg').change(function() {
				if ($(this).val() == '' || $(this).val() == '{{ $ma }}') {
					Swal.fire('Peringatan', 'Cabang harus diisi dengan benar!', 'warning');
					$(this).val('');
				}
			});

			$('#kd_brg').on('keydown', function(e) {
				if (e.keyCode == 13 && !isPosted) {
					e.preventDefault();
					processBarang();
				}
			});

			$('#qty').on('keydown', function(e) {
				if (e.keyCode == 13 && !isPosted) {
					e.preventDefault();
					addBarangToGrid();
				}
			});

			$('#btn-proses').click(function() {
				if (isPosted == 1) return;

				let cbg = $('#cbg').val();
				let golongan = $('#golongan').val();
				let pesan_ke = $('#pesan_ke').val();

				if (!cbg) {
					Swal.fire('Peringatan', 'Pilih cabang terlebih dahulu!', 'warning');
					return;
				}

				if (!golongan) {
					Swal.fire('Peringatan', 'Pilih type terlebih dahulu!', 'warning');
					return;
				}

				if (!pesan_ke) {
					Swal.fire('Peringatan', 'Pilih pesan ke terlebih dahulu!', 'warning');
					return;
				}

				Swal.fire({
					title: 'Proses Data?',
					text: 'Generate orderan otomatis berdasarkan kriteria',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya, Proses!',
					cancelButtonText: 'Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						return $.ajax({
							url: "{{ route('tspkokedctunjungsari.proses') }}",
							type: 'POST',
							data: {
								_token: '{{ csrf_token() }}',
								cbg: cbg,
								tipe: golongan,
								kodes: pesan_ke,
								lph1: $('#lph1').val(),
								lph2: $('#lph2').val(),
								sub1: $('#sub1').val(),
								sub2: $('#sub2').val(),
								butuh: $('#butuh').val()
							},
							dataType: 'json'
						});
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					if (result.isConfirmed && result.value.success) {
						let data = result.value.data;

						if (data.length > 0) {
							$('#tbody-detail').html('');

							data.forEach(function(item, index) {
								let newRow = `
									<tr data-no-id="0">
										<td class="text-center">${index + 1}</td>
										<td>
											<input type="text" class="form-control form-control-sm" name="detail[${index}][kd_brg]" value="${item.kd_brg}" readonly style="background-color: #e9ecef;">
											<input type="hidden" name="detail[${index}][no_id]" value="0">
											<input type="hidden" name="detail[${index}][rec]" value="${index + 1}">
											<input type="hidden" name="detail[${index}][ket_uk]" value="${item.ket_uk || ''}">
											<input type="hidden" name="detail[${index}][kdlaku]" value="${item.kdlaku || ''}">
										</td>
										<td>
											<input type="text" class="form-control form-control-sm" name="detail[${index}][na_brg]" value="${item.na_brg}" readonly style="background-color: #e9ecef;">
										</td>
										<td>
											<input type="text" class="form-control form-control-sm" name="detail[${index}][ket_kem]" value="${item.ket_kem || ''}" readonly style="background-color: #e9ecef;">
										</td>
										<td>
											<input type="number" class="form-control form-control-sm qty text-right" name="detail[${index}][qty]" value="${item.qty}" step="0.01">
										</td>
										<td>
											<input type="number" class="form-control form-control-sm harga text-right" name="detail[${index}][harga]" value="${item.harga}" step="0.01">
										</td>
										<td>
											<input type="text" class="form-control form-control-sm total-row text-right" value="${formatNumber(item.total)}" readonly style="background-color: #e9ecef;">
										</td>
										<td>
											<input type="text" class="form-control form-control-sm text-right" value="${formatNumber(item.stok)}" readonly style="background-color: #e9ecef;">
										</td>
										<td>
											<input type="text" class="form-control form-control-sm" name="detail[${index}][notes]">
										</td>
										<td class="text-center">
											<button type="button" class="btn btn-xs btn-danger btn-delete-row">
												<i class="fas fa-trash"></i>
											</button>
										</td>
									</tr>
								`;
								$('#tbody-detail').append(newRow);
							});

							rowIndex = data.length;
							calculateTotal();

							Swal.fire('Berhasil', 'Data berhasil diproses: ' + data.length + ' item', 'success');
						} else {
							Swal.fire('Info', 'Tidak ada data yang memenuhi kriteria', 'info');
						}
					}
				}).catch((error) => {
					if (error && error.responseJSON) {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: error.responseJSON.message || 'Terjadi kesalahan saat proses',
							confirmButtonText: 'OK'
						});
					}
				});
			});

			$('#btn-browse').click(function() {
				let cbg = $('#cbg').val();
				let golongan = $('#golongan').val();

				if (!cbg) {
					Swal.fire('Peringatan', 'Pilih cabang terlebih dahulu!', 'warning');
					return;
				}

				if (!golongan) {
					Swal.fire('Peringatan', 'Pilih type terlebih dahulu!', 'warning');
					return;
				}

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
				let cbg = $('#cbg').val();
				let golongan = $('#golongan').val();

				if (!cbg) {
					Swal.fire('Peringatan', 'Pilih cabang terlebih dahulu!', 'warning');
					return;
				}

				if (!golongan) {
					Swal.fire('Peringatan', 'Pilih type terlebih dahulu!', 'warning');
					return;
				}

				$('#tbody-browse').html(
					'<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

				$.ajax({
					url: "{{ route('tspkokedctunjungsari.browse') }}",
					data: {
						type: 'barang',
						q: q,
						cbg: cbg,
						tipe: golongan
					},
					success: function(data) {
						let html = '';
						if (data.length > 0) {
							data.forEach(function(item) {
								let stokDC = item.STOK_DC || 0;
								let rowClass = item.ADA == 'YES' ? '' : 'table-warning';
								html += '<tr class="' + rowClass + '">';
								html += '<td>' + item.kd_brg + '</td>';
								html += '<td>' + item.NA_BRG + '</td>';
								html += '<td>' + item.ket_kem + '</td>';
								html += '<td class="text-right">' + formatNumber(stokDC) + '</td>';
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
				let cbg = $('#cbg').val();
				let golongan = $('#golongan').val();

				if (!cbg) {
					Swal.fire('Peringatan', 'Pilih cabang terlebih dahulu!', 'warning');
					$('#cbg').focus();
					return;
				}

				if (!golongan) {
					Swal.fire('Peringatan', 'Pilih type terlebih dahulu!', 'warning');
					$('#golongan').focus();
					return;
				}

				if (!kdBrg) return;

				$('#kd_brg').val(kdBrg);
				$('#info-barang').html('<i class="fas fa-spinner fa-spin"></i> Loading...');

				$.ajax({
					url: "{{ route('tspkokedctunjungsari.detail') }}",
					data: {
						type: 'barang',
						kd_brg: kdBrg,
						cbg: cbg,
						tipe: golongan
					},
					success: function(response) {
						if (response.exists && response.data) {
							let data = response.data;

							if (data.on_dc == 0) {
								Swal.fire({
									title: 'Peringatan',
									text: 'Barang bukan On DC. Apakah tetap ingin order?',
									icon: 'warning',
									showCancelButton: true,
									confirmButtonText: 'Ya, Lanjut',
									cancelButtonText: 'Batal'
								}).then((result) => {
									if (result.isConfirmed) {
										setBarangData(data);
									} else {
										$('#kd_brg').val('');
										$('#info-barang').text('');
									}
								});
							} else if (data.ADA == 'YES') {
								setBarangData(data);
							} else {
								Swal.fire('Info', 'Stok DC Tunjungsari Kosong!', 'info');
								$('#kd_brg').val('');
								$('#info-barang').text('');
							}
						} else {
							Swal.fire('Error', 'Barang tidak ditemukan (Type: ' + golongan + ')', 'error');
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

			function setBarangData(data) {
				window.currentBarang = data;
				$('#info-barang').text(data.XX);
				$('#stok').val(formatNumber(data.STOK_DC || 0));
				$('#qty').val('').focus();
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
					let total = qty * parseFloat(data.harga || 0);
					let stokDC = data.STOK_DC || 0;

					let newRow = `
						<tr data-no-id="0">
							<td class="text-center">${rowIndex + 1}</td>
							<td>
								<input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][kd_brg]" value="${data.kd_brg}" readonly style="background-color: #e9ecef;">
								<input type="hidden" name="detail[${rowIndex}][no_id]" value="0">
								<input type="hidden" name="detail[${rowIndex}][rec]" value="${rowIndex + 1}">
								<input type="hidden" name="detail[${rowIndex}][ket_uk]" value="${data.ket_uk || ''}">
								<input type="hidden" name="detail[${rowIndex}][kdlaku]" value="${data.kdlaku || ''}">
							</td>
							<td>
								<input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][na_brg]" value="${data.NA_BRG}" readonly style="background-color: #e9ecef;">
							</td>
							<td>
								<input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][ket_kem]" value="${data.ket_kem || ''}" readonly style="background-color: #e9ecef;">
							</td>
							<td>
								<input type="number" class="form-control form-control-sm qty text-right" name="detail[${rowIndex}][qty]" value="${qty}" step="0.01">
							</td>
							<td>
								<input type="number" class="form-control form-control-sm harga text-right" name="detail[${rowIndex}][harga]" value="${data.harga}" step="0.01">
							</td>
							<td>
								<input type="text" class="form-control form-control-sm total-row text-right" value="${formatNumber(total)}" readonly style="background-color: #e9ecef;">
							</td>
							<td>
								<input type="text" class="form-control form-control-sm text-right" value="${formatNumber(stokDC)}" readonly style="background-color: #e9ecef;">
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
				$('#stok').val('');
				$('#info-barang').text('');
				$('#kd_brg').focus();

				calculateTotal();
			}

			$(document).on('click', '.btn-delete-row', function() {
				if (isPosted == 1) return;

				$(this).closest('tr').remove();
				calculateTotal();

				if ($('#tbody-detail tr').length === 0) {
					$('#tbody-detail').html('<tr><td colspan="10" class="text-center">Tidak ada data</td></tr>');
				}

				$('#tbody-detail tr').each(function(index) {
					if (!$(this).find('td').first().attr('colspan')) {
						$(this).find('td:first').text(index + 1);
						$(this).find('input[name*="[rec]"]').val(index + 1);
					}
				});
			});

			$('#btn-clear-all').click(function() {
				if (isPosted == 1) return;

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

			$('#btn-save').click(function(e) {
				e.preventDefault();

				if (isPosted == 1) {
					Swal.fire('Peringatan', 'Data sudah diposting, tidak dapat diubah', 'warning');
					return;
				}

				let tgl = $('input[name="tgl"]').val();
				let cbg = $('#cbg').val();
				let golongan = $('#golongan').val();

				if (!tgl) {
					Swal.fire('Peringatan', 'Tanggal harus diisi', 'warning');
					$('input[name="tgl"]').focus();
					return;
				}

				if (!cbg || cbg == '{{ $ma }}') {
					Swal.fire('Peringatan', 'Cabang harus diisi dengan benar!', 'warning');
					$('#cbg').focus();
					return;
				}

				if (!golongan) {
					Swal.fire('Peringatan', 'Type harus diisi', 'warning');
					$('#golongan').focus();
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
							cbg: cbg,
							golongan: golongan,
							notes: $('input[name="notes"]').val(),
							details: []
						};

						$('#tbody-detail tr').each(function(index) {
							if (!$(this).find('td').first().attr('colspan')) {
								let qty = parseNumber($(this).find('.qty').val());
								let harga = parseNumber($(this).find('.harga').val());
								let total = qty * harga;

								formData.details.push({
									no_id: $(this).find('input[name*="[no_id]"]').val() || 0,
									rec: index + 1,
									kd_brg: $(this).find('input[name*="[kd_brg]"]').val(),
									na_brg: $(this).find('input[name*="[na_brg]"]').val(),
									ket_uk: $(this).find('input[name*="[ket_uk]"]').val(),
									ket_kem: $(this).find('input[name*="[ket_kem]"]').val(),
									kdlaku: $(this).find('input[name*="[kdlaku]"]').val(),
									qty: qty,
									harga: harga,
									total: total,
									notes: $(this).find('input[name*="[notes]"]').val()
								});
							}
						});

						return $.ajax({
							url: "{{ route('tspkokedctunjungsari.store') }}",
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
							window.location.href = "{{ route('tspkokedctunjungsari') }}";
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
