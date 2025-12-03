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
						<h1 class="m-0">{{ $title }}</h1>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<form id="form-order" method="POST">
					@csrf
					<input type="hidden" name="edit" value="{{ !empty($header->NO_BUKTI) && $header->NO_BUKTI != '+' ? '1' : '' }}">

					<div class="card">
						<div class="card-header">
							<div class="row">
								<div class="col-md-6">
									<button type="button" class="btn btn-sm btn-success" id="btn-save" {{ $header->posted == 1 ? 'disabled' : '' }}>
										<i class="fas fa-save"></i> Save
									</button>
									<button type="button" class="btn btn-sm btn-warning" id="btn-print">
										<i class="fas fa-print"></i> Print
									</button>
									<a href="{{ route('TOrderKepembelian', ['jns_trans' => $jns_trans]) }}" class="btn btn-sm btn-danger">
										<i class="fas fa-times"></i> Exit
									</a>
								</div>
								<div class="col-md-6 text-right">
									@if ($type === 'TANPA_DC')
										<span class="badge badge-warning" style="font-size: 14px;">SPL - OFF</span>
									@else
										<span class="badge badge-info" style="font-size: 14px;">SPL - ON</span>
									@endif
								</div>
							</div>
						</div>

						<div class="card-body">
							<div class="row">
								<div class="col-md-4">
									<div class="form-group row">
										<label class="col-sm-4 col-form-label">No Bukti</label>
										<div class="col-sm-8">
											<input type="text" class="form-control form-control-sm" name="NO_BUKTI" value="{{ $header->no_bukti }}" readonly
												style="background-color: #e9ecef;">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group row">
										<label class="col-sm-4 col-form-label">Tanggal</label>
										<div class="col-sm-8">
											<input type="date" class="form-control form-control-sm" name="TGL" value="{{ $header->tgl }}" required
												{{ $header->posted == 1 ? 'readonly' : '' }}>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group row">
										<label class="col-sm-4 col-form-label">Notes</label>
										<div class="col-sm-8">
											<input type="text" class="form-control form-control-sm" name="NOTES" value="{{ $header->notes }}"
												{{ $header->posted == 1 ? 'readonly' : '' }}>
										</div>
									</div>
								</div>
							</div>

							<div class="row mt-2">
								<div class="col-md-6">
									<fieldset style="border: 1px solid #dee2e6; padding: 10px; border-radius: 5px;">
										<legend style="width: auto; font-size: 14px; margin-bottom: 10px; padding: 0 10px;">
											Pesan Ke Supplier
										</legend>
										<div class="form-group row mb-2">
											<label class="col-sm-3 col-form-label col-form-label-sm">Kode</label>
											<div class="col-sm-9">
												<div class="input-group input-group-sm">
													<input type="text" class="form-control form-control-sm" name="KODES" id="KODES" value="{{ $header->kodes }}" required
														{{ $header->posted == 1 ? 'readonly' : '' }}>
													@if ($header->posted != 1)
														<div class="input-group-append">
															<button type="button" class="btn btn-info btn-sm" id="btn-browse-sup">
																<i class="fas fa-search"></i>
															</button>
														</div>
													@endif
												</div>
											</div>
										</div>
										<div class="form-group row mb-2">
											<label class="col-sm-3 col-form-label col-form-label-sm">Nama</label>
											<div class="col-sm-9">
												<input type="text" class="form-control form-control-sm" name="NAMAS" id="NAMAS" value="{{ $header->namas }}" readonly
													style="background-color: #e9ecef;">
											</div>
										</div>
										<div class="form-group row mb-0">
											<label class="col-sm-3 col-form-label col-form-label-sm">s/d</label>
											<div class="col-sm-9">
												<input type="text" class="form-control form-control-sm" name="KODES2" id="KODES2" value="{{ $header->kodes }}"
													{{ $header->posted == 1 ? 'readonly' : '' }}>
											</div>
										</div>
									</fieldset>
								</div>

								<div class="col-md-6">
									<fieldset style="border: 1px solid #dee2e6; padding: 10px; border-radius: 5px;">
										<legend style="width: auto; font-size: 14px; margin-bottom: 10px; padding: 0 10px;">
											Filter
										</legend>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group row mb-2">
													<label class="col-sm-5 col-form-label col-form-label-sm">Untuk LPH</label>
													<div class="col-sm-7">
														<input type="number" class="form-control form-control-sm text-right" name="LPH1" id="LPH1" value="{{ $header->LPH1 }}"
															step="0.01" {{ $header->posted == 1 ? 'readonly' : '' }}>
													</div>
												</div>
												<div class="form-group row mb-2">
													<label class="col-sm-5 col-form-label col-form-label-sm">s/d</label>
													<div class="col-sm-7">
														<input type="number" class="form-control form-control-sm text-right" name="LPH2" id="LPH2" value="{{ $header->LPH2 }}"
															step="0.01" {{ $header->posted == 1 ? 'readonly' : '' }}>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group row mb-2">
													<label class="col-sm-5 col-form-label col-form-label-sm">Sub</label>
													<div class="col-sm-7">
														<input type="text" class="form-control form-control-sm" name="SUB1" id="SUB1" value="{{ $header->SUB1 }}"
															{{ $header->posted == 1 ? 'readonly' : '' }}>
													</div>
												</div>
												<div class="form-group row mb-0">
													<label class="col-sm-5 col-form-label col-form-label-sm">s/d</label>
													<div class="col-sm-7">
														<input type="text" class="form-control form-control-sm" name="SUB2" id="SUB2" value="{{ $header->SUB2 }}"
															{{ $header->posted == 1 ? 'readonly' : '' }}>
													</div>
												</div>
											</div>
										</div>
									</fieldset>
								</div>
							</div>

							<div class="row mt-2">
								<div class="col-md-6">
									<div class="form-group row mb-2">
										<label class="col-sm-4 col-form-label col-form-label-sm">Untuk Kebutuhan (hari)</label>
										<div class="col-sm-3">
											<input type="number" class="form-control form-control-sm text-right" name="HARI" id="HARI" value="{{ $header->HARI }}"
												{{ $header->posted == 1 ? 'readonly' : '' }}>
										</div>
										<div class="col-sm-5">
											@if ($header->posted != 1)
												<button type="button" class="btn btn-primary btn-sm btn-block" id="btn-proses">
													<i class="fas fa-cogs"></i> Proses
												</button>
											@endif
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="row">
										<div class="col-md-6">
											<div class="form-group row mb-2">
												<label class="col-sm-5 col-form-label col-form-label-sm">Total Qty</label>
												<div class="col-sm-7">
													<input type="text" class="form-control form-control-sm text-right" name="TOTAL_QTY" id="TOTAL_QTY"
														value="{{ number_format($header->total_qty, 0, ',', '.') }}" readonly style="background-color: #fff3cd; font-weight: bold;">
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group row mb-2">
												<label class="col-sm-4 col-form-label col-form-label-sm">Total</label>
												<div class="col-sm-8">
													<input type="text" class="form-control form-control-sm text-right" name="TOTAL" id="TOTAL"
														value="{{ number_format($header->total, 2, ',', '.') }}" readonly style="background-color: #fff3cd; font-weight: bold;">
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="table-responsive mt-3">
								<table class="table-bordered table-striped table-sm table" id="table-detail">
									<thead class="thead-dark">
										<tr>
											<th width="3%" class="text-center">NO</th>
											<th width="8%" class="text-center">Supplier</th>
											<th width="8%" class="text-center">Kode</th>
											<th width="18%" class="text-center">Nama Barang</th>
											<th width="8%" class="text-center">Ukuran</th>
											<th width="8%" class="text-center">Kemasan</th>
											<th width="6%" class="text-center">Qty</th>
											<th width="5%" class="text-center">Lph</th>
											<th width="5%" class="text-center">SMIN</th>
											<th width="5%" class="text-center">Sa</th>
											<th width="5%" class="text-center">Sp</th>
											<th width="7%" class="text-center">Harga</th>
											<th width="8%" class="text-center">Total</th>
											<th width="6%" class="text-center">Notes</th>
											@if ($header->posted != 1)
												<th width="3%" class="text-center">
													<button type="button" class="btn btn-xs btn-success" id="btn-add-row">
														<i class="fas fa-plus"></i>
													</button>
												</th>
											@endif
										</tr>
									</thead>
									<tbody id="tbody-detail">
										@if (!empty($detail) && count($detail) > 0)
											@foreach ($detail as $key => $row)
												<tr>
													<td class="text-center">{{ $key + 1 }}</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][KODES]" value="{{ $row->kodes }}"
															readonly style="background-color: #e9ecef;">
														<input type="hidden" name="detail[{{ $key }}][REC]" value="{{ $key + 1 }}">
														<input type="hidden" name="detail[{{ $key }}][KEMASAN]" value="{{ $row->kemasan }}">
														<input type="hidden" name="detail[{{ $key }}][SMIN]" value="{{ $row->SRMIN }}">
														<input type="hidden" name="detail[{{ $key }}][SP_L]" value="{{ $row->SP_L }}">
														<input type="hidden" name="detail[{{ $key }}][SP_LF]" value="{{ $row->SP_LF }}">
														<input type="hidden" name="detail[{{ $key }}][SP_LZ]" value="{{ $row->SP_LZ }}">
														<input type="hidden" name="detail[{{ $key }}][KODE_DC]" value="{{ $row->KODE_DC ?? '' }}">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm kd-brg" name="detail[{{ $key }}][KD_BRG]"
															value="{{ $row->KD_BRG }}" {{ $header->posted == 1 ? 'readonly' : '' }}>
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][NA_BRG]" value="{{ $row->NA_BRG }}"
															readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][KET_UK]" value="{{ $row->ket_uk }}"
															readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][KET_KEM]" value="{{ $row->ket_kem }}"
															readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" class="form-control form-control-sm qty text-right" name="detail[{{ $key }}][QTY]"
															value="{{ $row->qty }}" step="0.01" {{ $header->posted == 1 ? 'readonly' : '' }}>
													</td>
													<td>
														<input type="number" class="form-control form-control-sm text-right" name="detail[{{ $key }}][LPH]"
															value="{{ $row->lph }}" step="0.01" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" class="form-control form-control-sm text-right" value="{{ $row->SRMIN ?? 0 }}" step="0.01" readonly
															style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" class="form-control form-control-sm text-right" name="detail[{{ $key }}][QTYBRG]"
															value="{{ $row->qtybrg }}" step="0.01" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" class="form-control form-control-sm text-right" name="detail[{{ $key }}][QTYPO]"
															value="{{ $row->qtypo }}" step="0.01" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="number" class="form-control form-control-sm harga text-right" name="detail[{{ $key }}][HARGA]"
															value="{{ $row->harga }}" step="0.01" {{ $header->posted == 1 ? 'readonly' : '' }}>
													</td>
													<td>
														<input type="text" class="form-control form-control-sm total-row text-right" name="detail[{{ $key }}][TOTAL]"
															value="{{ number_format($row->TOTAL, 2, ',', '.') }}" readonly style="background-color: #e9ecef;">
													</td>
													<td>
														<input type="text" class="form-control form-control-sm" name="detail[{{ $key }}][NOTES]"
															value="{{ $row->notes ?? '' }}" {{ $header->posted == 1 ? 'readonly' : '' }}>
													</td>
													@if ($header->posted != 1)
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
												<td colspan="{{ $header->posted != 1 ? '15' : '14' }}" class="text-center">Tidak ada data</td>
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

	<!-- Modal Browse Supplier -->
	<div class="modal fade" id="modal-browse-supplier" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Browse Supplier</h5>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<input type="text" class="form-control" id="search-supplier" placeholder="Cari supplier... (min 2 karakter)" autocomplete="off">
					</div>
					<div class="table-responsive">
						<table class="table-bordered table-sm table">
							<thead>
								<tr>
									<th width="15%">Kode</th>
									<th width="35%">Nama</th>
									<th width="25%">Alamat</th>
									<th width="15%">Kota</th>
									<th width="10%">Action</th>
								</tr>
							</thead>
							<tbody id="tbody-browse-supplier">
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
			const isPosted = {{ $header->POSTED ?? 0 }};

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
				let totalAmount = 0;

				$('#tbody-detail tr').each(function() {
					if (!$(this).find('td').first().attr('colspan')) {
						let qty = parseNumber($(this).find('.qty').val());
						let harga = parseNumber($(this).find('.harga').val());
						let total = qty * harga;

						$(this).find('.total-row').val(formatNumber(total));
						totalQty += qty;
						totalAmount += total;
					}
				});

				$('#TOTAL_QTY').val(formatNumber(totalQty));
				$('#TOTAL').val(formatNumber(totalAmount));
			}

			$(document).on('input', '.qty, .harga', function() {
				if (isPosted != 1) {
					calculateTotal();
				}
			});

			$('#btn-add-row').click(function() {
				if (isPosted == 1) return;

				let newRow = `
            <tr>
                <td class="text-center">${rowIndex + 1}</td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][KODES]" readonly style="background-color: #e9ecef;">
                    <input type="hidden" name="detail[${rowIndex}][REC]" value="${rowIndex + 1}">
                    <input type="hidden" name="detail[${rowIndex}][KEMASAN]">
                    <input type="hidden" name="detail[${rowIndex}][SMIN]">
                    <input type="hidden" name="detail[${rowIndex}][SP_L]">
                    <input type="hidden" name="detail[${rowIndex}][SP_LF]">
                    <input type="hidden" name="detail[${rowIndex}][SP_LZ]">
                    <input type="hidden" name="detail[${rowIndex}][KODE_DC]">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm kd-brg" name="detail[${rowIndex}][KD_BRG]">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][NA_BRG]" readonly style="background-color: #e9ecef;">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][KET_UK]" readonly style="background-color: #e9ecef;">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][KET_KEM]" readonly style="background-color: #e9ecef;">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-right qty" name="detail[${rowIndex}][QTY]" step="0.01">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-right" name="detail[${rowIndex}][LPH]" step="0.01" readonly style="background-color: #e9ecef;">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-right" value="0" step="0.01" readonly style="background-color: #e9ecef;">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-right" name="detail[${rowIndex}][QTYBRG]" step="0.01" readonly style="background-color: #e9ecef;">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-right" name="detail[${rowIndex}][QTYPO]" step="0.01" readonly style="background-color: #e9ecef;">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-right harga" name="detail[${rowIndex}][HARGA]" step="0.01">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm text-right total-row" name="detail[${rowIndex}][TOTAL]" readonly style="background-color: #e9ecef;">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][NOTES]">
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
			});

			$(document).on('click', '.btn-delete-row', function() {
				if (isPosted == 1) return;

				$(this).closest('tr').remove();
				calculateTotal();

				if ($('#tbody-detail tr').length === 0) {
					$('#tbody-detail').html('<tr><td colspan="15" class="text-center">Tidak ada data</td></tr>');
				}

				$('#tbody-detail tr').each(function(index) {
					if (!$(this).find('td').first().attr('colspan')) {
						$(this).find('td:first').text(index + 1);
					}
				});
			});

			$(document).on('blur', '.kd-brg', function() {
				if (isPosted == 1) return;

				let kdBrg = $(this).val().trim().toUpperCase();
				let row = $(this).closest('tr');

				$(this).val(kdBrg);

				if (kdBrg) {
					$.ajax({
						url: '/TOrderKepembelian/{{ $jns_trans }}/validate-barang',
						type: 'GET',
						data: {
							kd_brg: kdBrg
						},
						success: function(response) {
							if (response.confirm) {
								Swal.fire({
									title: 'Konfirmasi',
									text: response.message,
									icon: 'warning',
									showCancelButton: true,
									confirmButtonText: 'Ya, Lanjutkan',
									cancelButtonText: 'Batal'
								}).then((result) => {
									if (result.isConfirmed) {
										fillBarangData(row, response.data);
									} else {
										clearRowData(row);
									}
								});
							} else if (response.success) {
								fillBarangData(row, response.data);
							}
						},
						error: function(xhr) {
							Swal.fire('Error!', xhr.responseJSON.error, 'error');
							clearRowData(row);
						}
					});
				}
			});

			function fillBarangData(row, data) {
				row.find('input[name*="[KODES]"]').val(data.SUPP);
				row.find('input[name*="[NA_BRG]"]').val(data.NA_BRG);
				row.find('input[name*="[KET_UK]"]').val(data.KET_UK);
				row.find('input[name*="[KET_KEM]"]').val(data.KET_KEM);
				row.find('input[name*="[KEMASAN]"]').val(data.KEMASAN);
				row.find('input[name*="[HARGA]"]').val(data.HB);
				row.find('input[name*="[LPH]"]').val(data.LPH);
				row.find('input[name*="[QTYPO]"]').val(data.TOTALPO || 0);
				row.find('input[name*="[QTYBRG]"]').val(data.SALDO || 0);
				row.find('input[name*="[SMIN]"]').val(data.SRMIN || 0);
				row.find('td:eq(8) input').val(data.SRMIN || 0);
			}

			function clearRowData(row) {
				row.find('.kd-brg').val('');
				row.find('input[name*="[KODES]"]').val('');
				row.find('input[name*="[NA_BRG]"]').val('');
				row.find('input[name*="[KET_UK]"]').val('');
				row.find('input[name*="[KET_KEM]"]').val('');
				row.find('input[name*="[KEMASAN]"]').val('');
				row.find('input[name*="[HARGA]"]').val('');
				row.find('input[name*="[LPH]"]').val('');
				row.find('input[name*="[QTYPO]"]').val('');
				row.find('input[name*="[QTYBRG]"]').val('');
				row.find('input[name*="[SMIN]"]').val('');
				row.find('td:eq(8) input').val('0');
			}

			$('#KODES').blur(function() {
				if (isPosted == 1) return;

				let kodes = $(this).val().trim();

				if (kodes) {
					$.ajax({
						url: '/TOrderKepembelian/{{ $jns_trans }}/get-select-kodes',
						type: 'GET',
						data: {
							kodes: kodes
						},
						success: function(response) {
							if (response.length > 0) {
								$('#NAMAS').val(response[0].NAMAS);
								$('#KODES2').val(kodes);
							} else {
								Swal.fire('Error!', 'Supplier tidak ditemukan', 'error');
								$('#KODES').val('');
								$('#NAMAS').val('');
								$('#KODES2').val('');
							}
						}
					});
				}
			});

			$('#btn-browse-sup').click(function() {
				if (isPosted == 1) return;

				// Reset dan buka modal
				$('#search-supplier').val('');
				$('#tbody-browse-supplier').html('<tr><td colspan="5" class="text-center">Ketik untuk mencari...</td></tr>');
				$('#modal-browse-supplier').modal('show');

				// Auto-focus ke search box setelah modal terbuka
				setTimeout(function() {
					$('#search-supplier').focus();
				}, 500);
			});

			// Search supplier dengan debounce
			let searchSupplierTimeout;
			$('#search-supplier').on('keyup', function() {
				clearTimeout(searchSupplierTimeout);
				let q = $(this).val();

				if (q.length >= 2) {
					searchSupplierTimeout = setTimeout(function() {
						searchSupplier(q);
					}, 500);
				} else if (q.length === 0) {
					// Tampilkan semua supplier jika kosong
					searchSupplier('');
				} else {
					$('#tbody-browse-supplier').html('<tr><td colspan="5" class="text-center">Ketik minimal 2 karakter...</td></tr>');
				}
			});

			function searchSupplier(q) {
				$('#tbody-browse-supplier').html(
					'<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

				$.ajax({
					url: '/TOrderKepembelian/{{ $jns_trans }}/browse',
					type: 'GET',
					data: {
						q: q
					},
					success: function(data) {
						let html = '';
						if (data.length > 0) {
							data.forEach(function(item) {
								html += '<tr>';
								html += '<td>' + item.KODES + '</td>';
								html += '<td>' + item.NAMAS + '</td>';
								html += '<td>' + (item.ALAMAT || '') + '</td>';
								html += '<td>' + (item.KOTA || '') + '</td>';
								html += '<td class="text-center">';
								html += '<button type="button" class="btn btn-xs btn-primary btn-select-supplier" ';
								html += 'data-kodes="' + item.KODES + '" ';
								html += 'data-namas="' + item.NAMAS + '" ';
								html += 'data-hari="' + (item.HARI || 0) + '">';
								html += '<i class="fas fa-check"></i> Pilih</button>';
								html += '</td>';
								html += '</tr>';
							});
						} else {
							html = '<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>';
						}
						$('#tbody-browse-supplier').html(html);
					},
					error: function(xhr) {
						$('#tbody-browse-supplier').html('<tr><td colspan="5" class="text-center text-danger">Error: ' + (xhr
							.responseJSON?.message || 'Terjadi kesalahan') + '</td></tr>');
					}
				});
			}

			// Pilih supplier dari browse
			$(document).on('click', '.btn-select-supplier', function() {
				let kodes = $(this).data('kodes');
				let namas = $(this).data('namas');
				let hari = $(this).data('hari');

				$('#KODES').val(kodes);
				$('#NAMAS').val(namas);
				$('#KODES2').val(kodes);

				// Set hari jika tersedia
				if (hari && hari > 0) {
					$('#HARI').val(hari);
				}

				$('#modal-browse-supplier').modal('hide');

				// Tampilkan notifikasi sukses
				Swal.fire({
					icon: 'success',
					title: 'Supplier Dipilih',
					text: kodes + ' - ' + namas,
					timer: 1500,
					showConfirmButton: false
				});
			});

			// Enter key pada field KODES untuk buka popup
			$('#KODES').on('keydown', function(e) {
				if (e.keyCode == 13 && isPosted != 1) {
					e.preventDefault();
					let val = $(this).val().trim();
					if (val === '') {
						// Jika kosong, buka browse popup
						$('#btn-browse-sup').click();
					} else {
						// Jika ada value, blur untuk trigger validasi
						$(this).blur();
					}
				}
			});

			$('#btn-proses').click(function() {
				if (isPosted == 1) return;

				let kodes1 = $('#KODES').val();
				let kodes2 = $('#KODES2').val() || kodes1;
				let lph1 = $('#LPH1').val();
				let lph2 = $('#LPH2').val();
				let sub1 = $('#SUB1').val() || '';
				let sub2 = $('#SUB2').val() || 'ZZZ';
				let hari = $('#HARI').val();

				if (!kodes1) {
					Swal.fire('Peringatan!', 'Supplier harus diisi', 'warning');
					$('#KODES').focus();
					return;
				}

				if (!lph1 || !lph2) {
					Swal.fire('Peringatan!', 'Untuk LPH harus diisi', 'warning');
					$('#LPH1').focus();
					return;
				}

				if (!hari || hari == 0) {
					Swal.fire('Peringatan!', 'Untuk Kebutuhan (hari) harus diisi', 'warning');
					$('#HARI').focus();
					return;
				}

				Swal.fire({
					title: 'Proses Data?',
					text: 'Sistem akan memproses data barang berdasarkan kriteria yang ditentukan',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya, Proses!',
					cancelButtonText: 'Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						return $.ajax({
							url: '/TOrderKepembelian/{{ $jns_trans }}/proses',
							type: 'POST',
							data: {
								_token: '{{ csrf_token() }}',
								kodes1: kodes1,
								kodes2: kodes2,
								lph1: lph1,
								lph2: lph2,
								sub1: sub1,
								sub2: sub2,
								hari: hari
							}
						});
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					if (result.isConfirmed) {
						let response = result.value;

						if (response.success) {
							$('#tbody-detail').html('');
							rowIndex = 0;

							if (response.data.length > 0) {
								response.data.forEach((item, index) => {
									let newRow = `
                                <tr>
                                    <td class="text-center">${index + 1}</td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="detail[${index}][KODES]" value="${item.KODES}" readonly style="background-color: #e9ecef;">
                                        <input type="hidden" name="detail[${index}][REC]" value="${index + 1}">
                                        <input type="hidden" name="detail[${index}][KEMASAN]" value="${item.KEMASAN}">
                                        <input type="hidden" name="detail[${index}][SMIN]" value="${item.SRMIN}">
                                        <input type="hidden" name="detail[${index}][SP_L]" value="${item.SP_L}">
                                        <input type="hidden" name="detail[${index}][SP_LF]" value="${item.SP_LF}">
                                        <input type="hidden" name="detail[${index}][SP_LZ]" value="${item.SP_LZ}">
                                        <input type="hidden" name="detail[${index}][KODE_DC]" value="${item.KODE_DC || ''}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm kd-brg" name="detail[${index}][KD_BRG]" value="${item.KD_BRG}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="detail[${index}][NA_BRG]" value="${item.NA_BRG}" readonly style="background-color: #e9ecef;">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="detail[${index}][KET_UK]" value="${item.KET_UK}" readonly style="background-color: #e9ecef;">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="detail[${index}][KET_KEM]" value="${item.KET_KEM}" readonly style="background-color: #e9ecef;">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-right qty" name="detail[${index}][QTY]" value="${item.QTY}" step="0.01">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-right" name="detail[${index}][LPH]" value="${item.LPH}" step="0.01" readonly style="background-color: #e9ecef;">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-right" value="${item.SRMIN || 0}" step="0.01" readonly style="background-color: #e9ecef;">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-right" name="detail[${index}][QTYBRG]" value="${item.QTYBRG || 0}" step="0.01" readonly style="background-color: #e9ecef;">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-right" name="detail[${index}][QTYPO]" value="${item.QTYPO || 0}" step="0.01" readonly style="background-color: #e9ecef;">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-right harga" name="detail[${index}][HARGA]" value="${item.HARGA}" step="0.01">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm text-right total-row" name="detail[${index}][TOTAL]" value="${formatNumber(item.TOTAL)}" readonly style="background-color: #e9ecef;">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="detail[${index}][NOTES]" value="${item.NOTES || ''}">
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

								let message = 'Proses selesai! Data berhasil diproses: ' + response.data.length + ' item';
								if (response.sup_libur) {
									message += '\n\nPerhatian! Ada Supplier yang sedang Libur:\n' + response.sup_libur;
								}

								Swal.fire({
									title: 'Berhasil!',
									text: message,
									icon: 'success',
									confirmButtonText: 'OK'
								});
							} else {
								$('#tbody-detail').html(
									'<tr><td colspan="15" class="text-center">Tidak ada data yang memenuhi kriteria</td></tr>');
								Swal.fire('Info', 'Tidak ada data yang memenuhi kriteria filter', 'info');
							}
						}
					}
				}).catch((error) => {
					if (error && error.responseJSON) {
						Swal.fire('Error!', error.responseJSON.error, 'error');
					}
				});
			});

			$('#btn-save').click(function(e) {
				e.preventDefault();

				if (isPosted == 1) {
					Swal.fire('Peringatan!', 'Data sudah diposting, tidak dapat diubah', 'warning');
					return;
				}

				let tgl = $('input[name="TGL"]').val();
				let kodes = $('#KODES').val();

				if (!tgl) {
					Swal.fire('Peringatan!', 'Tanggal harus diisi', 'warning');
					$('input[name="TGL"]').focus();
					return;
				}

				if (!kodes) {
					Swal.fire('Peringatan!', 'Supplier harus diisi', 'warning');
					$('#KODES').focus();
					return;
				}

				let hasDetail = false;
				$('#tbody-detail tr').each(function() {
					if (!$(this).find('td').first().attr('colspan')) {
						let kdBrg = $(this).find('.kd-brg').val();
						if (kdBrg && kdBrg.trim() !== '') {
							hasDetail = true;
							return false;
						}
					}
				});

				if (!hasDetail) {
					Swal.fire('Peringatan!', 'Detail barang harus diisi', 'warning');
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
						let formData = new FormData($('#form-order')[0]);

						$('#tbody-detail tr').each(function(index) {
							if (!$(this).find('td').first().attr('colspan')) {
								formData.set(`detail[${index}][TOTAL]`, parseNumber($(this).find('.total-row').val()));
							}
						});

						formData.set('TOTAL_QTY', parseNumber($('#TOTAL_QTY').val()));
						formData.set('TOTAL', parseNumber($('#TOTAL').val()));

						let url =
							'{{ $header->no_bukti != '+' && !empty($header->no_bukti)
							    ? route('TOrderKepembelian.update', ['jns_trans' => $jns_trans, 'id' => $header->no_bukti])
							    : route('TOrderKepembelian.store', ['jns_trans' => $jns_trans]) }}';

						return $.ajax({
							url: url,
							type: 'POST',
							data: formData,
							processData: false,
							contentType: false
						});
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					if (result.isConfirmed) {
						Swal.fire({
							title: 'Berhasil!',
							text: result.value.success,
							icon: 'success',
							confirmButtonText: 'OK'
						}).then(() => {
							window.location.href = '{{ route('TOrderKepembelian', ['jns_trans' => $jns_trans]) }}';
						});
					}
				}).catch((error) => {
					if (error && error.responseJSON) {
						Swal.fire('Error!', error.responseJSON.error, 'error');
					}
				});
			});

			$('#btn-print').click(function() {
				let noBukti = $('input[name="NO_BUKTI"]').val();

				if (!noBukti || noBukti === '+') {
					Swal.fire('Peringatan!', 'Simpan data terlebih dahulu sebelum print', 'warning');
					return;
				}

				window.open('/rTOrderKepembelian/{{ $jns_trans }}?no_bukti=' + noBukti, '_blank');
			});

			calculateTotal();
		});
	</script>
@endsection
