@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Koreksi Stock Opname</h1>
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
						<div class="card-header"> <button type="button" class="btn btn-sm btn-success" id="btn-save">
								<i class="fas fa-save"></i> Save
							</button>
							<a href="{{ route('tprosesstockopname') }}" class="btn btn-sm btn-danger">
								<i class="fas fa-times"></i> Exit
							</a>
						</div>
						<div class="card-body">
							@if (isset($error) && $error)
								<div class="alert alert-danger">{{ $error }}</div>
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
										<label>Type</label>
										<input type="text" class="form-control form-control-sm" name="type" value="{{ $header->type ?? 'BSO' }}" readonly
											style="background-color: #e9ecef;">
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label>No SO</label>
										<input type="text" class="form-control form-control-sm" id="no_so" name="no_so" value="{{ $header->nolap ?? '' }}"
											placeholder="SO2501-0001">
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label>Sub</label>
										<input type="text" class="form-control form-control-sm" name="sub" value="{{ $header->sub ?? '' }}" readonly
											style="background-color: #e9ecef;">
									</div>
								</div>
								<div class="col-md-3">
									<button type="button" class="btn btn-primary btn-sm mt-4" id="btn-browse-so">
										<i class="fas fa-search"></i> Browse SO
									</button>
								</div>
							</div>

							<hr>

							<div class="table-responsive">
								<table class="table-bordered table-striped table-sm table" id="table-detail">
									<thead class="thead-dark">
										<tr>
											<th width="3%">No</th>
											<th width="10%">Kode</th>
											<th width="20%">Nama Barang</th>
											<th width="8%">Ukuran</th>
											<th width="8%">Saldo</th>
											<th width="8%">QTY Indikator</th>
											<th width="8%">QTY Apps</th>
											<th width="8%">Riil</th>
											<th width="8%">Selisih</th>
											<th width="15%">Ket</th>
										</tr>
									</thead>
									<tbody id="tbody-detail">
										@if (!empty($detail) && count($detail) > 0)
											@foreach ($detail as $key => $row)
												<tr>
													<td class="text-center">{{ $key + 1 }}</td>
													<td>
														<input type="hidden" name="detail[{{ $key }}][no_id]" value="{{ $row->no_id }}">
														<input type="hidden" name="detail[{{ $key }}][kd_brg]" value="{{ $row->kd_brg }}">
														<input type="hidden" name="detail[{{ $key }}][na_brg]" value="{{ $row->na_brg }}">
														<small>{{ $row->kd_brg }}</small>
													</td>
													<td><small>{{ $row->na_brg }}</small></td>
													<td><small>{{ $row->STAND }}</small></td>
													<td class="text-right"><small>{{ number_format($row->saldo, 2) }}</small></td>
													<td class="text-right"><small class="qty-indi">{{ number_format($row->qty, 2) }}</small>
														<input type="hidden" name="detail[{{ $key }}][qty]" class="qty-value" value="{{ $row->qty }}">
													</td>
													<td class="text-right"><small>{{ number_format($row->qty_apps ?? 0, 2) }}</small></td>
													<td>
														<input type="number" step="0.01" class="form-control form-control-sm riil-input text-right"
															name="detail[{{ $key }}][riil]" value="{{ $row->riil }}" data-index="{{ $key }}">
													</td>
													<td class="total-cell text-right"><small>{{ number_format($row->total, 2) }}</small>
														<input type="hidden" name="detail[{{ $key }}][total]" class="total-value" value="{{ $row->total }}">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][ket]" value="{{ $row->ket }}">
													</td>
												</tr>
											@endforeach
										@else
											<tr>
												<td colspan="10" class="text-center">Tidak ada data. Gunakan "Browse SO" untuk memuat data.</td>
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
			$(document).on('input', '.riil-input', function() {
				let index = $(this).data('index');
				let qty = parseFloat($(input[name = "detail[${index}][qty]"]).val()) || 0;
				let riil = parseFloat($(this).val()) || 0;
				let total = riil - qty;
				$(`tr:eq(${index + 1}) .total-cell small`).text(total.toFixed(2));
				$(`input[name="detail[${index}][total]"]`).val(total);
			});

			$('#btn-browse-so').click(function() {
				let no_so = $('#no_so').val().trim();
				if (!no_so) {
					Swal.fire('Peringatan', 'No SO harus diisi', 'warning');
					return;
				}

				Swal.fire({
					title: 'Memuat Data...',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				$.ajax({
					url: "{{ route('tprosesstockopname.browse-koreksi-so') }}",
					type: 'POST',
					data: {
						_token: "{{ csrf_token() }}",
						no_so: no_so
					},
					success: function(res) {
						Swal.close();
						if (!res.status) {
							Swal.fire('Error', res.msg, 'error');
							return;
						}

						$('input[name="no_so"]').val(res.no_bukti);
						$('input[name="sub"]').val(res.sub);
						$('input[name="type"]').val(res.type);

						$('#tbody-detail').empty();
						res.data.forEach((item, index) => {
							let showQtyApps = res.show_qty_apps ?
								`<td class="text-right"><small>${parseFloat(item.qty_apps).toFixed(2)}</small></td>` :
								`<td class="text-right"><small>-</small></td>`;

							$('#tbody-detail').append(`
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>
                                    <input type="hidden" name="detail[${index}][no_id]" value="0">
                                    <input type="hidden" name="detail[${index}][kd_brg]" value="${item.kd_brg}">
                                    <input type="hidden" name="detail[${index}][na_brg]" value="${item.na_brg}">
                                    <input type="hidden" name="detail[${index}][qty_trans]" value="${item.qty_trans}">
                                    <small>${item.kd_brg}</small>
                                </td>
                                <td><small>${item.na_brg}</small></td>
                                <td><small>${item.ket_uk}</small></td>
                                <td class="text-right"><small>${parseFloat(item.saldo).toFixed(2)}</small></td>
                                <td class="text-right"><small class="qty-indi">${parseFloat(item.qty_indi).toFixed(2)}</small>
                                    <input type="hidden" name="detail[${index}][qty]" class="qty-value" value="${item.qty_indi}">
                                </td>
                                ${showQtyApps}
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-right riil-input"
                                           name="detail[${index}][riil]" value="${item.riil}" data-index="${index}">
                                </td>
                                <td class="text-right total-cell"><small>${(item.riil - item.qty_indi).toFixed(2)}</small>
                                    <input type="hidden" name="detail[${index}][total]" class="total-value" value="${item.riil - item.qty_indi}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="detail[${index}][ket]" value="">
                                </td>
                            </tr>
                        `);
						});

						Swal.fire('Berhasil', 'Data berhasil dimuat', 'success');
					},
					error: function(xhr) {
						Swal.close();
						Swal.fire('Error', xhr.responseJSON?.msg || 'Gagal memuat data', 'error');
					}
				});
			});

			$('#btn-save').click(function(e) {
				e.preventDefault();

				let formData = $('#form-koreksi-so').serializeArray();
				let data = {};

				formData.forEach(item => {
					if (item.name.indexOf('[') > -1) {
						let matches = item.name.match(/^(.+?)\[(\d+)\]\[(.+)\]$/);
						if (matches) {
							if (!data[matches[1]]) data[matches[1]] = [];
							if (!data[matches[1]][matches[2]]) data[matches[1]][matches[2]] = {};
							data[matches[1]][matches[2]][matches[3]] = item.value;
						}
					} else {
						data[item.name] = item.value;
					}
				});

				Swal.fire({
					title: 'Simpan Data?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya, Simpan!'
				}).then((result) => {
					if (result.isConfirmed) {
						$.ajax({
							url: "{{ route('tprosesstockopname.store-koreksi_so') }}",
							type: 'POST',
							data: data,
							success: function(res) {
								if (res.status) {
									Swal.fire('Berhasil!', res.message, 'success').then(() => {
										window.location.href = "{{ route('tprosesstockopname') }}";
									});
								} else {
									Swal.fire('Gagal', res.message, 'error');
								}
							},
							error: function(xhr) {
								Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menyimpan', 'error');
							}
						});
					}
				});
			});
		});
	</script>
@endsection
