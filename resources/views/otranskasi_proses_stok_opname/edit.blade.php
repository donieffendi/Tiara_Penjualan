@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $status == 'simpan' ? 'New' : 'Edit' }} Proses Stock Opname</h1>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<form id="form-stock-opname" method="POST">
					@csrf
					<input type="hidden" name="status" value="{{ $status }}">

					<div class="card">
						<div class="card-header">
							<div class="row">
								<div class="col-md-12">
									<button type="button" class="btn btn-sm btn-success" id="btn-save">
										<i class="fas fa-save"></i> Save
									</button>
									<a href="{{ route('tprosesstockopname') }}" class="btn btn-sm btn-danger">
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
										<label>Notes</label>
										<input type="text" class="form-control form-control-sm" name="notes" value="{{ $header->notes ?? '' }}" placeholder="Keterangan">
									</div>
								</div>
							</div>

							<hr>

							<div class="row">
								<div class="col-md-2">
									<div class="form-group">
										<label>Sub</label>
										<input type="text" class="form-control form-control-sm" id="sub" value="001" maxlength="3">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Kdlaku</label>
										<select class="form-control form-control-sm" id="cbkdlaku">
											<option value="ALL">ALL</option>
											<option value="0">0</option>
											<option value="1">1</option>
											<option value="2">2</option>
											<option value="3">3</option>
										</select>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>L/H Dari</label>
										<input type="number" step="0.01" class="form-control form-control-sm" id="lph1" value="0">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>L/H S/D</label>
										<input type="number" step="0.01" class="form-control form-control-sm" id="lph2" value="999">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Tidak Ada Transaksi</label>
										<input type="number" class="form-control form-control-sm" id="tat" value="0">
									</div>
								</div>
								<div class="col-md-1">
									<div class="form-group">
										<label>&nbsp;</label>
										<div class="form-check mt-2">
											<input type="checkbox" class="form-check-input" id="pertahankan">
											<label class="form-check-label" style="font-size: 11px;">Pertahankan</label>
										</div>
									</div>
								</div>
								<div class="col-md-1">
									<div class="form-group">
										<label>&nbsp;</label>
										<div class="form-check mt-2">
											<input type="checkbox" class="form-check-input" id="dataRL">
											<label class="form-check-label" style="font-size: 11px;">Data R/L</label>
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-2">
									<div class="form-group">
										<label>Item From</label>
										<input type="text" class="form-control form-control-sm" id="item1" placeholder="Item awal" maxlength="4">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Item To</label>
										<input type="text" class="form-control form-control-sm" id="item2" value="ZZZZ" placeholder="Item akhir" maxlength="4">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Supplier</label>
										<input type="text" class="form-control form-control-sm" id="supp" placeholder="Kode supplier">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>&nbsp;</label>
										<button type="button" class="btn btn-primary btn-sm btn-block" id="btn-allin">
											<i class="fas fa-download"></i> All In
										</button>
									</div>
								</div>
							</div>

							<div class="row mb-2">
								<div class="col-md-12">
									<button type="button" class="btn btn-success btn-sm" id="btn-cek-all">
										<i class="fas fa-check"></i> Cek All
									</button>
									<button type="button" class="btn btn-warning btn-sm" id="btn-uncek-all">
										<i class="fas fa-times"></i> Uncek All
									</button>
									<button id="btn-hapus-positif" type="button" class="btn btn-danger btn-sm">
										<i class="fas fa-trash"></i> Hapus Positif
									</button>
									<button id="btn-hapus-nol" type="button" class="btn btn-danger btn-sm">
										<i class="fas fa-trash"></i> Hapus Nol
									</button>
									<button id="btn-hapus-negatif" type="button" class="btn btn-danger btn-sm">
										<i class="fas fa-trash"></i> Hapus Negatif
									</button>
								</div>
							</div>

							<div class="table-responsive">
								<table class="table-bordered table-striped table-sm table" id="table-detail">
									<thead class="thead-dark">
										<tr>
											<th width="3%" class="text-center">No</th>
											<th width="10%" class="text-center">Kode</th>
											<th width="20%" class="text-center">Nama Barang</th>
											<th width="10%" class="text-center">Ukuran</th>
											<th width="8%" class="text-center">Harga</th>
											<th width="8%" class="text-center">Stok</th>
											<th width="8%" class="text-center">Supp</th>
											<th width="5%" class="text-center">Cek</th>
											<th width="3%" class="text-center">
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
														<input type="hidden" name="detail[{{ $key }}][na_brg]" value="{{ $row->na_brg }}">
														<input type="hidden" name="detail[{{ $key }}][itemsub]" value="{{ $row->itemsub ?? '' }}">
														<input type="hidden" name="detail[{{ $key }}][ket_uk]" value="{{ $row->ket_uk ?? '' }}">
														<input type="hidden" name="detail[{{ $key }}][ket_kem]" value="{{ $row->ket_kem ?? '' }}">
														<input type="hidden" name="detail[{{ $key }}][kd]" value="{{ $row->kd ?? '' }}">
														<input type="hidden" name="detail[{{ $key }}][hj]" value="{{ $row->hj ?? 0 }}">
														<input type="hidden" name="detail[{{ $key }}][saldo]" value="{{ $row->saldo ?? 0 }}">
														<input type="hidden" name="detail[{{ $key }}][lph]" value="{{ $row->lph ?? 0 }}">
													</td>
													<td><small>{{ $row->na_brg }}</small></td>
													<td><small>{{ $row->ket_uk ?? '' }}</small></td>
													<td class="text-right"><small>{{ number_format($row->hj ?? 0, 0) }}</small></td>
													<td class="saldo text-right"><small>{{ number_format($row->saldo ?? 0, 2) }}</small></td>
													<td><small>{{ $row->SUPP ?? '' }}</small></td>
													<td class="text-center">
														<input type="checkbox" class="cek-item" name="detail[{{ $key }}][cek]" value="1"
															{{ ($row->cek ?? 1) == 1 ? 'checked' : '' }}>
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
												<td colspan="9" class="text-center">Tidak ada data. Gunakan tombol "All In" untuk memuat data barang.</td>
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

			$('#btn-allin').click(function() {
				let sub = $('#sub').val().trim();
				let item1 = $('#item1').val().trim() || '';
				let item2 = $('#item2').val().trim() || 'ZZZZ';
				let supp = $('#supp').val().trim();
				let kdlaku = $('#cbkdlaku').val();
				let lph1 = $('#lph1').val();
				let lph2 = $('#lph2').val();
				let tat = $('#tat').val();
				let pertahankan = $('#pertahankan').is(':checked') ? 1 : 0;
				let dataRL = $('#dataRL').is(':checked') ? 1 : 0;

				if (!sub && !supp) {
					Swal.fire('Peringatan', 'Sub atau Supplier harus diisi', 'warning');
					return;
				}

				Swal.fire({
					title: 'Memuat Data...',
					text: 'Mohon tunggu',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				$.ajax({
					url: "{{ route('tprosesstockopname.browse') }}",
					data: {
						sub,
						item1,
						item2,
						supp,
						cbkdlaku: kdlaku,
						lph1,
						lph2,
						tat,
						pertahankan,
						dataRL
					},
					success: function(data) {
						Swal.close();
						if (data.length === 0) {
							Swal.fire('Info', 'Tidak ada data barang ditemukan', 'info');
							return;
						}

						if (!pertahankan) {
							$('#tbody-detail').empty();
							rowIndex = 0;
						}

						data.forEach(function(item) {
							let newRow = `
                                <tr data-no-id="0">
                                    <td class="text-center">${rowIndex + 1}</td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][kd_brg]" value="${item.KD_BRG}" readonly style="background-color: #e9ecef;">
                                        <input type="hidden" name="detail[${rowIndex}][no_id]" value="0">
                                        <input type="hidden" name="detail[${rowIndex}][rec]" value="${rowIndex + 1}">
                                        <input type="hidden" name="detail[${rowIndex}][na_brg]" value="${item.NA_BRG || ''}">
                                        <input type="hidden" name="detail[${rowIndex}][itemsub]" value="${item.itemsub || ''}">
                                        <input type="hidden" name="detail[${rowIndex}][ket_uk]" value="${item.KET_UK || ''}">
                                        <input type="hidden" name="detail[${rowIndex}][ket_kem]" value="${item.KET_KEM || ''}">
                                        <input type="hidden" name="detail[${rowIndex}][kd]" value="${item.kd || ''}">
                                        <input type="hidden" name="detail[${rowIndex}][hj]" value="${item.HJ || 0}">
                                        <input type="hidden" name="detail[${rowIndex}][saldo]" value="${item.saldo || 0}">
                                        <input type="hidden" name="detail[${rowIndex}][lph]" value="${item.lph || 0}">
                                    </td>
                                    <td><small>${item.NA_BRG || ''}</small></td>
                                    <td><small>${item.KET_UK || ''}</small></td>
                                    <td class="text-right"><small>${parseFloat(item.HJ || 0).toLocaleString('id-ID')}</small></td>
                                    <td class="text-right saldo"><small>${parseFloat(item.saldo || 0).toFixed(2)}</small></td>
                                    <td><small>${item.SUPP || ''}</small></td>
                                    <td class="text-center">
                                        <input type="checkbox" class="cek-item" name="detail[${rowIndex}][cek]" value="1" checked>
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

						Swal.fire('Berhasil', data.length + ' barang berhasil dimuat', 'success');
					},
					error: function(xhr) {
						Swal.close();
						Swal.fire('Error', xhr.responseJSON?.message || 'Gagal memuat data', 'error');
					}
				});
			});

			$('#btn-cek-all').click(function() {
				$('.cek-item').prop('checked', true);
				Swal.fire({
					icon: 'success',
					title: 'Berhasil',
					text: 'Semua item dicek',
					timer: 1500,
					showConfirmButton: false
				});
			});

			$('#btn-uncek-all').click(function() {
				$('.cek-item').prop('checked', false);
				Swal.fire({
					icon: 'success',
					title: 'Berhasil',
					text: 'Semua item di-uncek',
					timer: 1500,
					showConfirmButton: false
				});
			});

			$('#btn-hapus-positif').click(function() {
				$('#tbody-detail tr').each(function() {
					let saldo = parseFloat($(this).find('.saldo small').text().replace(/,/g, '')) || 0;
					if (saldo > 0) $(this).remove();
				});
				Swal.fire({
					icon: 'success',
					title: 'Berhasil',
					text: 'Baris saldo > 0 dihapus',
					timer: 1500,
					showConfirmButton: false
				});
			});

			$('#btn-hapus-nol').click(function() {
				$('#tbody-detail tr').each(function() {
					let saldo = parseFloat($(this).find('.saldo small').text().replace(/,/g, '')) || 0;
					if (saldo === 0) $(this).remove();
				});
				Swal.fire({
					icon: 'success',
					title: 'Berhasil',
					text: 'Baris saldo = 0 dihapus',
					timer: 1500,
					showConfirmButton: false
				});
			});

			$('#btn-hapus-negatif').click(function() {
				$('#tbody-detail tr').each(function() {
					let saldo = parseFloat($(this).find('.saldo small').text().replace(/,/g, '')) || 0;
					if (saldo < 0) $(this).remove();
				});
				Swal.fire({
					icon: 'success',
					title: 'Berhasil',
					text: 'Baris saldo < 0 dihapus',
					timer: 1500,
					showConfirmButton: false
				});
			});

			$(document).on('click', '.btn-delete-row', function() {
				$(this).closest('tr').remove();
				if ($('#tbody-detail tr').length === 0) {
					$('#tbody-detail').html('<tr><td colspan="9" class="text-center">Tidak ada data</td></tr>');
				}
				renumberRows();
			});

			$('#btn-clear-all').click(function() {
				Swal.fire({
					title: 'Hapus Semua?',
					text: 'Semua detail akan dihapus',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Ya, Hapus!'
				}).then((result) => {
					if (result.isConfirmed) {
						$('#tbody-detail').html('<tr><td colspan="9" class="text-center">Tidak ada data</td></tr>');
						rowIndex = 0;
					}
				});
			});

			$('#item1').on('change', function() {
				let item1 = $(this).val().trim();
				$('#item2').val(item1 || 'ZZZZ');
			});

			function renumberRows() {
				$('#tbody-detail tr').each(function(index) {
					if (!$(this).find('td').first().attr('colspan')) {
						$(this).find('td:first').text(index + 1);
						$(this).find('input[name*="[rec]"]').val(index + 1);
					}
				});
			}

			$('#btn-save').click(function(e) {
				e.preventDefault();

				let tgl = $('input[name="tgl"]').val();
				let sub = $('#sub').val();

				if (!tgl) {
					Swal.fire('Peringatan', 'Tanggal harus diisi', 'warning');
					$('input[name="tgl"]').focus();
					return;
				}

				let hasDetail = false;
				let hasCeked = false;

				$('#tbody-detail tr').each(function() {
					if (!$(this).find('td').first().attr('colspan')) {
						let kdBrg = $(this).find('input[name*="[kd_brg]"]').val();
						if (kdBrg && kdBrg.trim() !== '') {
							hasDetail = true;
							if ($(this).find('.cek-item').is(':checked')) {
								hasCeked = true;
							}
						}
					}
				});

				if (!hasDetail) {
					Swal.fire('Peringatan', 'Detail barang harus diisi', 'warning');
					return;
				}

				if (!hasCeked) {
					Swal.fire('Peringatan', 'Minimal satu item harus di-cek', 'warning');
					return;
				}

				Swal.fire({
					title: 'Simpan Data?',
					text: 'Data akan disimpan ke database',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya, Simpan!',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						let formData = new FormData($('#form-stock-opname')[0]);
						let data = {};
						formData.forEach(function(value, key) {
							if (key.indexOf('[') > -1) {
								let matches = key.match(/^(.+?)\[(\d+)\]\[(.+)\]$/);
								if (matches) {
									let arrayName = matches[1];
									let index = matches[2];
									let fieldName = matches[3];
									if (!data[arrayName]) data[arrayName] = [];
									if (!data[arrayName][index]) data[arrayName][index] = {};
									data[arrayName][index][fieldName] = value;
								} else {
									data[key] = value;
								}
							} else {
								data[key] = value;
							}
						});

						$('#tbody-detail tr').each(function(index) {
							if (!$(this).find('td').first().attr('colspan')) {
								if (!data.detail) data.detail = [];
								if (!data.detail[index]) data.detail[index] = {};
								data.detail[index].cek = $(this).find('.cek-item').is(':checked') ? 1 : 0;
							}
						});

						data.sub = sub;

						return $.ajax({
							url: "{{ route('tprosesstockopname.store') }}",
							type: 'POST',
							data: data,
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
							window.location.href = "{{ route('tprosesstockopname') }}";
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
		});
	</script>
@endsection
