@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $status == 'simpan' ? 'New' : 'Edit' }} {{ $pageTitle }}</h1>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<form id="form-obral" method="POST">
					@csrf
					<input type="hidden" name="status" value="{{ $status }}">
					<input type="hidden" name="menu_type" value="{{ $menuType }}">

					<div class="card">
						<div class="card-header">
							<div class="row">
								<div class="col-md-6">
									<button type="button" class="btn btn-sm btn-success" id="btn-save">
										<i class="fas fa-save"></i> Save
									</button>
									<a href="{{ route($menuType == 'FS' ? 'tentryflashsale' : 'tobralsupermarket') }}" class="btn btn-sm btn-danger">
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
								<div class="col-md-2">
									<div class="form-group">
										<label>No Bukti</label>
										<input type="text" class="form-control form-control-sm" name="no_bukti" value="{{ $header->no_bukti ?? '+' }}" readonly
											style="background-color: #e9ecef; font-weight: bold;">
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Tanggal <span class="text-danger">*</span></label>
										<input type="date" class="form-control form-control-sm" name="tgl" value="{{ $header->tgl ?? date('Y-m-d') }}" required>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Jam Mulai <span class="text-danger">*</span></label>
										<input type="time" class="form-control form-control-sm" name="jam_mulai" value="{{ $header->jam_mulai ?? '00:00' }}" required>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>s/d <span class="text-danger">*</span></label>
										<input type="time" class="form-control form-control-sm" name="jam_sls" value="{{ $header->jam_sls ?? '23:59' }}" required>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>Tgl Mulai <span class="text-danger">*</span></label>
										<input type="date" class="form-control form-control-sm" name="tgl_mulai" value="{{ $header->tgl_mulai ?? date('Y-m-d') }}" required>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label>s/d <span class="text-danger">*</span></label>
										<input type="date" class="form-control form-control-sm" name="tgl_sls" value="{{ $header->tgl_sls ?? date('Y-m-d') }}" required>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-2">
									<div class="form-group">
										<label>Kode Supplier</label>
										<input type="text" class="form-control form-control-sm" name="kodes" value="{{ $header->kodes ?? '' }}" placeholder="Kode">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Nama Supplier</label>
										<input type="text" class="form-control form-control-sm" name="namas" value="{{ $header->namas ?? '' }}" placeholder="Nama">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>Notes</label>
										<input type="text" class="form-control form-control-sm" name="notes" value="{{ $header->notes ?? '' }}" placeholder="Catatan">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label>No FlashSale</label>
										<div class="input-group input-group-sm">
											<input type="text" class="form-control form-control-sm" name="no_file" id="no_file" value="{{ $header->no_file ?? '' }}"
												placeholder="Nomor File">
											<div class="input-group-append">
												<button type="button" class="btn btn-info btn-sm" id="btn-load-file">
													<i class="fas fa-search"></i>
												</button>
											</div>
										</div>
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
											<th width="8%" class="text-center">Sub Item</th>
											<th width="12%" class="text-center">Kode Barang</th>
											<th width="30%" class="text-center">Nama Barang</th>
											<th width="12%" class="text-center">Ukuran</th>
											<th width="10%" class="text-center">Diskon (%)</th>
											<th width="12%" class="text-center">Nilai Obral</th>
											<th width="15%" class="text-center">Keterangan</th>
											<th width="3%" class="text-center"><i class="fas fa-cog"></i></th>
										</tr>
									</thead>
									<tbody id="tbody-detail">
										@if (!empty($detail) && count($detail) > 0)
											@foreach ($detail as $key => $row)
												<tr data-no-id="{{ $row->no_id ?? 0 }}">
													<td class="text-center">{{ $key + 1 }}</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][sub]" value="{{ $row->sub ?? '' }}"
															readonly style="background-color: #e9ecef;">
														<input type="hidden" name="detail[{{ $key }}][no_id]" value="{{ $row->no_id ?? 0 }}">
														<input type="hidden" name="detail[{{ $key }}][rec]" value="{{ $key + 1 }}">
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
														<input type="number" step="0.01" class="form-control form-control-sm dis-input text-right"
															name="detail[{{ $key }}][dis]" value="{{ $row->dis ?? 0 }}">
													</td>
													<td>
														<input type="number" step="0.01" class="form-control form-control-sm th-input text-right" name="detail[{{ $key }}][th]"
															value="{{ $row->th ?? 0 }}">
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
												<td colspan="9" class="text-center">Tidak ada data. Klik "Tambah Baris" atau load dari File.</td>
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
			var menuType = '{{ $menuType }}';
			var routePrefix = menuType == 'FS' ? 'tentryflashsale' : 'tobralsupermarket';

			// Load File
			$('#btn-load-file').click(function() {
				let noFile = $('#no_file').val().trim();

				if (!noFile) {
					Swal.fire('Peringatan', 'Nomor File harus diisi', 'warning');
					return;
				}

				Swal.fire({
					title: 'Memuat Data File...',
					text: 'Mohon tunggu',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				$.ajax({
					url: "{{ url('/') }}/" + routePrefix + "/browse",
					data: {
						no_file: noFile
					},
					success: function(response) {
						Swal.close();

						if (response.success && response.data.length > 0) {
							// Clear existing data
							$('#tbody-detail').empty();
							rowIndex = 0;

							// Set tanggal akhir jika ada
							if (response.tgl_akhir) {
								$('input[name="tgl_sls"]').val(response.tgl_akhir);
							}

							// Add rows from File
							response.data.forEach(function(item) {
								addRowFromFile(item);
							});

							Swal.fire('Berhasil', response.data.length + ' barang berhasil dimuat', 'success');
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

			function addRowFromFile(item) {
				let newRow = `
		<tr data-no-id="0">
			<td class="text-center">${rowIndex + 1}</td>
			<td>
				<input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][sub]" value="" readonly style="background-color: #e9ecef;">
				<input type="hidden" name="detail[${rowIndex}][no_id]" value="0">
				<input type="hidden" name="detail[${rowIndex}][rec]" value="${rowIndex + 1}">
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
				<input type="number" step="0.01" class="form-control form-control-sm dis-input text-right" name="detail[${rowIndex}][dis]" value="${item.diskon || 0}">
			</td>
			<td>
				<input type="number" step="0.01" class="form-control form-control-sm th-input text-right" name="detail[${rowIndex}][th]" value="${item.th || 0}">
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
				<input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][sub]" readonly style="background-color: #e9ecef;">
				<input type="hidden" name="detail[${rowIndex}][no_id]" value="0">
				<input type="hidden" name="detail[${rowIndex}][rec]" value="${rowIndex + 1}">
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
				<input type="number" step="0.01" class="form-control form-control-sm dis-input text-right" name="detail[${rowIndex}][dis]" value="0">
			</td>
			<td>
				<input type="number" step="0.01" class="form-control form-control-sm th-input text-right" name="detail[${rowIndex}][th]" value="0">
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
						url: "{{ url('/') }}/" + routePrefix + "/detail",
						data: {
							kd_brg: kdBrg
						},
						success: function(response) {
							if (response.exists) {
								populateRow($row, response.data);
								$row.find('.dis-input').focus();
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

			function populateRow($row, data) {
				$row.find('input[name*="[kd_brg]"]').val(data.kd_brg || '');
				$row.find('input[name*="[na_brg]"]').val(data.na_brg || '');
				$row.find('input[name*="[ket_uk]"]').val(data.ket_uk || '');
				$row.find('input[name*="[sub]"]').val(data.sub || '');
			}

			// Delete Row
			$(document).on('click', '.btn-delete-row', function() {
				$(this).closest('tr').remove();

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
						rowIndex = 0;
					}
				});
			});

			// Save
			$('#btn-save').click(function(e) {
				e.preventDefault();

				let tgl = $('input[name="tgl"]').val();
				let tglMulai = $('input[name="tgl_mulai"]').val();
				let tglSls = $('input[name="tgl_sls"]').val();
				let jamMulai = $('input[name="jam_mulai"]').val();
				let jamSls = $('input[name="jam_sls"]').val();

				if (!tgl) {
					Swal.fire('Peringatan', 'Tanggal harus diisi', 'warning');
					$('input[name="tgl"]').focus();
					return;
				}

				if (!tglMulai) {
					Swal.fire('Peringatan', 'Tanggal Mulai harus diisi', 'warning');
					$('input[name="tgl_mulai"]').focus();
					return;
				}

				if (!tglSls) {
					Swal.fire('Peringatan', 'Tanggal Selesai harus diisi', 'warning');
					$('input[name="tgl_sls"]').focus();
					return;
				}

				if (!jamMulai) {
					Swal.fire('Peringatan', 'Jam Mulai harus diisi', 'warning');
					$('input[name="jam_mulai"]').focus();
					return;
				}

				if (!jamSls) {
					Swal.fire('Peringatan', 'Jam Selesai harus diisi', 'warning');
					$('input[name="jam_sls"]').focus();
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
							menu_type: menuType,
							no_bukti: $('input[name="no_bukti"]').val(),
							tgl: tgl,
							tgl_mulai: tglMulai,
							tgl_sls: tglSls,
							jam_mulai: jamMulai,
							jam_sls: jamSls,
							kodes: $('input[name="kodes"]').val(),
							namas: $('input[name="namas"]').val(),
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
										sub: $(this).find('input[name*="[sub]"]').val() || '',
										kd_brg: kdBrg,
										na_brg: $(this).find('input[name*="[na_brg]"]').val(),
										ket_uk: $(this).find('input[name*="[ket_uk]"]').val() || '',
										dis: parseFloat($(this).find('input[name*="[dis]"]').val()) || 0,
										th: parseFloat($(this).find('input[name*="[th]"]').val()) || 0,
										ket: $(this).find('input[name*="[ket]"]').val() || ''
									});
								}
							}
						});

						return $.ajax({
							url: "{{ url('/') }}/" + routePrefix + "/store",
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
							window.location.href = "{{ url('/') }}/" + routePrefix;
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
