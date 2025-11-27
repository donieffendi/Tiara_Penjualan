@extends('layouts.plain')

@section('content')
	<style>
		.card {}

		.content-header {
			padding: 0 !important;
		}

		.form-group.row {
			align-items: center !important;
			margin-bottom: 0.5rem !important;
		}

		.form-group.row .form-label {
			margin-bottom: 0 !important;
			line-height: 1.5;
			vertical-align: middle;
			display: flex;
			align-items: center;
			height: 38px;
		}

		.form-group.row .form-control {
			line-height: 1.5;
			height: 38px;
		}

		#detailTable {
			font-size: 12px;
		}

		#detailTable th,
		#detailTable td {
			padding: 5px;
			vertical-align: middle;
		}

		.btn-sm {
			padding: 0.25rem 0.5rem;
			font-size: 0.875rem;
		}

		.input-group-sm .form-control {
			height: calc(1.5em + 0.5rem + 2px);
			padding: 0.25rem 0.5rem;
			font-size: 0.875rem;
		}

		.table-fixed {
			table-layout: fixed;
		}
	</style>

	<div class="content-wrapper">
		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<h3 class="card-title">Stok Opname Hadiah - {{ $status == 'simpan' ? 'New' : 'Edit' }}</h3>
							</div>
							<div class="card-body">
								<form action="{{ route('phstokopnamehadiah.store') }}" method="POST" name="entri" id="entri">
									@csrf
									<input type="hidden" id="status" name="status" value="{{ $status }}">

									<div class="card mb-3">
										<div class="card-header bg-light">
											<h6 class="mb-0">Header Information</h6>
										</div>
										<div class="card-body">
											<div class="row">
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="no_bukti" class="form-label">No Bukti</label>
														</div>
														<div class="col-md-8">
															<input type="text" class="form-control form-control-sm" id="no_bukti" name="no_bukti"
																value="{{ $status == 'simpan' ? '+' : $no_bukti }}" readonly placeholder="No Bukti" required>
														</div>
													</div>
												</div>

												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="tgl" class="form-label">Tanggal</label>
														</div>
														<div class="col-md-8">
															<input type="date" class="form-control form-control-sm" id="tgl" name="tgl"
																value="{{ $header ? date('Y-m-d', strtotime($header->tgl)) : date('Y-m-d') }}" required readonly>
														</div>
													</div>
												</div>

												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="gol" class="form-label">Gol</label>
														</div>
														<div class="col-md-8">
															<input type="number" class="form-control form-control-sm" id="gol" name="gol" value="{{ $header->gol ?? 0 }}"
																placeholder="0">
														</div>
													</div>
												</div>

												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="sub" class="form-label">Sub</label>
														</div>
														<div class="col-md-8">
															<input type="text" class="form-control form-control-sm" id="sub" name="sub" value="{{ $header->sub ?? '' }}"
																placeholder="Sub">
														</div>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="bulan" class="form-label">Bulan</label>
														</div>
														<div class="col-md-8">
															<select class="form-control form-control-sm" id="bulan" name="bulan">
																<option value="01">Januari</option>
																<option value="02" selected>Februari</option>
																<option value="03">Maret</option>
																<option value="04">April</option>
																<option value="05">Mei</option>
																<option value="06">Juni</option>
																<option value="07">Juli</option>
																<option value="08">Agustus</option>
																<option value="09">September</option>
																<option value="10">Oktober</option>
																<option value="11">November</option>
																<option value="12">Desember</option>
															</select>
														</div>
													</div>
												</div>

												<div class="col-md-3">
													<div class="form-group row">
														<div class="col-md-4">
															<label for="tipe" class="form-label">Tipe</label>
														</div>
														<div class="col-md-8">
															<select class="form-control form-control-sm" id="tipe" name="tipe">
																<option value="TOKO" selected>TOKO</option>
																<option value="GUDANG">GUDANG</option>
															</select>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									@if ($status == 'simpan')
										<div class="row mt-2">
											<div class="col-md-12">
												<div class="card">
													<div class="card-header bg-secondary">
														<h6 class="mb-0">Entry Barang</h6>
													</div>
													<div class="card-body p-2">
														<div class="row">
															<div class="col-md-12">
																<label id="lblBarang" class="text-info"></label>
															</div>
														</div>
														<div class="row">
															<div class="col-md-4">
																<div class="form-group">
																	<label for="kd_brg" class="form-label">Kode Barang</label>
																	<div class="input-group input-group-sm">
																		<input type="text" class="form-control form-control-sm" id="kd_brg" name="kd_brg" placeholder="Kode Barang">
																		<button type="button" class="btn btn-sm btn-primary" onclick="browseProduct()">
																			<i class="fas fa-search"></i>
																		</button>
																	</div>
																</div>
															</div>

															<div class="col-md-3">
																<div class="form-group">
																	<label for="ket_entry" class="form-label">Ket</label>
																	<input type="text" class="form-control form-control-sm" id="ket_entry" name="ket_entry" placeholder="Keterangan">
																</div>
															</div>

															<div class="col-md-2">
																<div class="form-group">
																	<label class="form-label">&nbsp;</label>
																	<div>
																		<button type="button" class="btn btn-success btn-sm" onclick="addToDetail()">
																			<i class="fas fa-plus"></i> OK
																		</button>
																		<button type="button" class="btn btn-warning btn-sm" onclick="clearEntryForm()">
																			<i class="fas fa-eraser"></i> Clear
																		</button>
																	</div>
																</div>
															</div>

															<div class="col-md-3">
																<div class="form-group">
																	<label class="form-label">&nbsp;</label>
																	<div>
																		<button type="button" class="btn btn-info btn-sm" onclick="loadAllStok()">
																			<i class="fas fa-sync"></i> Load All Stok
																		</button>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									@endif

									<div class="row mt-2">
										<div class="col-md-12">
											<div class="card">
												<div class="card-header d-flex justify-content-between">
													<h6 class="mb-0">Detail Barang</h6>
													<div>
														<span class="badge badge-primary">Total Items: <span id="totalItems">0</span></span>
													</div>
												</div>
												<div class="card-body p-2">
													<div class="table-responsive">
														<table class="table-bordered table-sm table-hover table" id="detailTable">
															<thead class="table-dark">
																<tr>
																	<th width="5%">No</th>
																	<th width="15%">Kode</th>
																	<th width="35%">Nama Barang</th>
																	<th width="10%">Stok</th>
																	<th width="20%">Ket</th>
																	<th width="10%">Cek</th>
																	@if ($status == 'simpan')
																		<th width="5%">Action</th>
																	@endif
																</tr>
															</thead>
															<tbody id="detailTableBody">
																@if ($status == 'edit' && !empty($detail))
																	@foreach ($detail as $index => $item)
																		<tr data-kd-brg="{{ $item->kd_brg }}">
																			<td class="text-center">{{ $index + 1 }}</td>
																			<td>{{ $item->kd_brg }}</td>
																			<td>{{ $item->na_brg }}</td>
																			<td class="text-right">{{ number_format($item->saldo, 2) }}</td>
																			<td>{{ $item->ket_uk ?? '' }}</td>
																			<td class="text-center">
																				<input type="checkbox" class="form-check-input" {{ $item->lph == 1 ? 'checked' : '' }} disabled>
																			</td>
																			<input type="hidden" name="details[{{ $index }}][kd_brg]" value="{{ $item->kd_brg }}">
																			<input type="hidden" name="details[{{ $index }}][itemsub]" value="{{ $item->itemsub ?? '' }}">
																			<input type="hidden" name="details[{{ $index }}][na_brg]" value="{{ $item->na_brg }}">
																			<input type="hidden" name="details[{{ $index }}][ket_uk]" value="{{ $item->ket_uk ?? '' }}">
																			<input type="hidden" name="details[{{ $index }}][ket_kem]" value="{{ $item->ket_kem ?? '' }}">
																			<input type="hidden" name="details[{{ $index }}][kd]" value="{{ $item->kd ?? '' }}">
																			<input type="hidden" name="details[{{ $index }}][hj]" value="{{ $item->hj ?? 0 }}">
																			<input type="hidden" name="details[{{ $index }}][saldo]" value="{{ $item->saldo }}">
																			<input type="hidden" name="details[{{ $index }}][lph]" value="{{ $item->lph ?? 0 }}">
																			<input type="hidden" name="details[{{ $index }}][cek]" value="{{ $item->lph ?? 0 }}">
																		</tr>
																	@endforeach
																@endif
															</tbody>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>

									<div class="row mt-3">
										<div class="col-md-12">
											@if ($status == 'simpan')
												<button type="button" id="SAVEX" onclick="simpan()" class="btn btn-success">
													<i class="fas fa-save"></i> Save
												</button>
											@endif
											<button type="button" id="CLOSEX" onclick="closeForm()" class="btn btn-outline-secondary">
												<i class="fas fa-times"></i> Close
											</button>
											@if ($status == 'edit')
												<button type="button" onclick="printData('{{ $no_bukti }}')" class="btn btn-info">
													<i class="fas fa-print"></i> Print
												</button>
											@endif
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="browseProductModal" tabindex="-1" aria-labelledby="browseProductModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseProductModalLabel">Browse Produk Hadiah</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<input type="text" class="form-control" id="searchProduct" placeholder="Cari produk...">
					</div>
					<div class="table-responsive">
						<table class="table-bordered table-sm table" id="productTable">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Nama Produk</th>
									<th>Stok</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="productTableBody">
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('footer-scripts')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		var detailRowIndex = {{ $status == 'edit' && !empty($detail) ? count($detail) : 0 }};
		var TEMP_PRODUCT = null;

		$(document).ready(function() {
			@if ($status == 'simpan')
				$('#gol').focus();
			@else
				$('#gol').focus();
			@endif

			calculateTotal();

			$('#searchProduct').on('keyup', function() {
				var query = $(this).val();
				setTimeout(function() {
					searchProduct(query);
				}, 300);
			});

			$('body').on('keydown', function(e) {
				if (e.key === "Enter") {
					e.preventDefault();
					handleEnterKey(e.target);
					return false;
				}
			});
		});

		function handleEnterKey(element) {
			var $element = $(element);
			var id = $element.attr('id');

			switch (id) {
				case 'gol':
					$('#sub').focus().select();
					break;
				case 'sub':
					$('#bulan').focus();
					break;
				case 'bulan':
					$('#tipe').focus();
					break;
				case 'tipe':
					$('#kd_brg').focus().select();
					break;
				case 'kd_brg':
					handleKdBrgEnter($element.val().trim());
					break;
				case 'ket_entry':
					addToDetail();
					break;
				default:
					var form = $element.parents('form:eq(0)');
					var focusable = form.find('input,select,textarea,button').filter(':visible:not([readonly]):not([disabled])');
					var next = focusable.eq(focusable.index(element) + 1);
					if (next.length) {
						next.focus().select();
					}
					break;
			}
		}

		function handleKdBrgEnter(kd_brg) {
			if (kd_brg) {
				var bulan = $('#bulan').val();
				var tipe = $('#tipe').val();

				$.ajax({
					url: '{{ route('phstokopnamehadiah.detail') }}',
					type: 'GET',
					data: {
						kd_brgh: kd_brg,
						bulan: bulan,
						tipe: tipe
					},
					success: function(response) {
						if (response.success && response.exists && response.data) {
							TEMP_PRODUCT = response.data;
							$('#lblBarang').text(response.data.kd_brg + ' - ' + response.data.na_brg + ' (Stok: ' + formatNumber(response.data
								.saldo) + ')');
							$('#ket_entry').val('').focus().select();
						} else {
							browseProduct();
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal mengambil data produk'
						});
					}
				});
			}
		}

		function addToDetail() {
			var kd_brg = $('#kd_brg').val().trim();
			var ket = $('#ket_entry').val().trim();

			if (!kd_brg || !TEMP_PRODUCT) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Pilih produk terlebih dahulu!'
				});
				$('#kd_brg').focus();
				return;
			}

			var existingRow = findDetailRow(kd_brg);
			if (existingRow.length > 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Produk sudah ada dalam list!'
				});
				clearEntryForm();
				return;
			}

			addNewRow(TEMP_PRODUCT, ket);
			clearEntryForm();
			calculateTotal();
			$('#kd_brg').focus();
		}

		function addNewRow(product, ket) {
			var newRow = `
				<tr data-kd-brg="${product.kd_brg}">
					<td class="text-center">${detailRowIndex + 1}</td>
					<td>${product.kd_brg}</td>
					<td>${product.na_brg}</td>
					<td class="text-right">${formatNumber(product.saldo)}</td>
					<td><input type="text" class="form-control form-control-sm" name="details[${detailRowIndex}][ket_uk]" value="${ket}"></td>
					<td class="text-center">
						<input type="checkbox" class="form-check-input cek-checkbox" name="details[${detailRowIndex}][cek]" value="1">
					</td>
					<td class="text-center">
						<button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Hapus">
							<i class="fas fa-trash"></i>
						</button>
					</td>
					<input type="hidden" name="details[${detailRowIndex}][kd_brg]" value="${product.kd_brg}">
					<input type="hidden" name="details[${detailRowIndex}][itemsub]" value="">
					<input type="hidden" name="details[${detailRowIndex}][na_brg]" value="${product.na_brg}">
					<input type="hidden" name="details[${detailRowIndex}][ket_kem]" value="">
					<input type="hidden" name="details[${detailRowIndex}][kd]" value="">
					<input type="hidden" name="details[${detailRowIndex}][hj]" value="0">
					<input type="hidden" name="details[${detailRowIndex}][saldo]" value="${product.saldo}">
					<input type="hidden" name="details[${detailRowIndex}][lph]" value="0">
				</tr>`;

			$('#detailTableBody').append(newRow);
			detailRowIndex++;
		}

		function removeRow(btn) {
			Swal.fire({
				title: 'Konfirmasi',
				text: 'Hapus item ini?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Ya, Hapus',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					$(btn).closest('tr').remove();
					renumberRows();
					calculateTotal();
				}
			});
		}

		function renumberRows() {
			$('#detailTableBody tr').each(function(index) {
				$(this).find('td:first').text(index + 1);
			});
		}

		function findDetailRow(kd_brg) {
			return $('#detailTableBody tr[data-kd-brg="' + kd_brg + '"]');
		}

		function clearEntryForm() {
			$('#kd_brg').val('');
			$('#ket_entry').val('');
			$('#lblBarang').text('');
			TEMP_PRODUCT = null;
		}

		function calculateTotal() {
			var totalItems = $('#detailTableBody tr').length;
			$('#totalItems').text(totalItems);
		}

		function loadAllStok() {
			var bulan = $('#bulan').val();
			var tipe = $('#tipe').val();

			Swal.fire({
				title: 'Konfirmasi',
				text: 'Load semua stok hadiah? Data detail yang ada akan dihapus.',
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#28a745',
				cancelButtonColor: '#6c757d',
				confirmButtonText: 'Ya, Load',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					Swal.fire({
						title: 'Loading...',
						text: 'Mengambil data stok',
						allowOutsideClick: false,
						didOpen: () => {
							Swal.showLoading()
						}
					});

					$.ajax({
						url: '{{ route('phstokopnamehadiah.browse') }}',
						type: 'GET',
						data: {
							q: '',
							bulan: bulan,
							tipe: tipe
						},
						success: function(response) {
							Swal.close();

							$('#detailTableBody').empty();
							detailRowIndex = 0;

							if (response && response.length > 0) {
								response.forEach(function(item) {
									var product = {
										kd_brg: item.kd_brgh,
										na_brg: item.na_brgh,
										saldo: item.saldo
									};
									addNewRow(product, '');
								});
								calculateTotal();

								Swal.fire({
									icon: 'success',
									title: 'Success',
									text: 'Berhasil load ' + response.length + ' item',
									timer: 2000
								});
							} else {
								Swal.fire({
									icon: 'warning',
									title: 'Warning',
									text: 'Tidak ada data stok'
								});
							}
						},
						error: function() {
							Swal.close();
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: 'Gagal mengambil data stok'
							});
						}
					});
				}
			});
		}

		function browseProduct() {
			$('#browseProductModal').modal('show');
			searchProduct('');
			$('#searchProduct').focus();
		}

		function searchProduct(query) {
			var bulan = $('#bulan').val();
			var tipe = $('#tipe').val();

			$.ajax({
				url: '{{ route('phstokopnamehadiah.browse') }}',
				type: 'GET',
				data: {
					q: query,
					bulan: bulan,
					tipe: tipe
				},
				success: function(response) {
					var tbody = $('#productTableBody');
					tbody.empty();

					if (response.length > 0) {
						response.forEach(function(item) {
							var row = '<tr>';
							row += '<td>' + item.kd_brgh + '</td>';
							row += '<td>' + item.na_brgh + '</td>';
							row += '<td class="text-right">' + formatNumber(item.saldo) + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectProduct(\'' +
								item.kd_brgh + '\', \'' + escapeHtml(item.na_brgh) + '\', ' + item.saldo + ')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="4" class="text-center">No data found</td></tr>');
					}
				}
			});
		}

		function selectProduct(kd_brg, na_brg, saldo) {
			TEMP_PRODUCT = {
				kd_brg: kd_brg,
				na_brg: na_brg,
				saldo: saldo
			};
			$('#kd_brg').val(kd_brg);
			$('#lblBarang').text(kd_brg + ' - ' + na_brg + ' (Stok: ' + formatNumber(saldo) + ')');
			$('#browseProductModal').modal('hide');
			$('#ket_entry').focus();
		}

		function simpan() {
			var tgl = $('#tgl').val();

			if (!tgl) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Tanggal harus diisi!'
				});
				$('#tgl').focus();
				return;
			}

			var detailCount = $('#detailTableBody tr').length;
			if (detailCount === 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Detail barang masih kosong!'
				});
				return;
			}

			calculateTotal();

			Swal.fire({
				title: 'Konfirmasi',
				text: 'Apakah data sudah benar?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#28a745',
				cancelButtonColor: '#6c757d',
				confirmButtonText: 'Ya, Simpan',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					Swal.fire({
						title: 'Menyimpan...',
						text: 'Mohon tunggu',
						allowOutsideClick: false,
						didOpen: () => {
							Swal.showLoading()
						}
					});

					$.ajax({
						url: '{{ route('phstokopnamehadiah.store') }}',
						type: 'POST',
						data: $('#entri').serialize(),
						success: function(response) {
							Swal.close();
							if (response.success) {
								Swal.fire({
									title: 'Success!',
									text: 'Save Data Success',
									icon: 'success',
									confirmButtonText: 'OK'
								}).then(() => {
									window.location.href = '{{ route('phstokopnamehadiah') }}';
								});
							} else {
								Swal.fire({
									title: 'Error!',
									text: response.message,
									icon: 'error',
									confirmButtonText: 'OK'
								});
							}
						},
						error: function(xhr, status, error) {
							Swal.close();
							var errorMessage = 'Terjadi kesalahan saat menyimpan data';
							if (xhr.responseJSON && xhr.responseJSON.message) {
								errorMessage = xhr.responseJSON.message;
							}
							Swal.fire({
								title: 'Error!',
								text: errorMessage,
								icon: 'error',
								confirmButtonText: 'OK'
							});
						}
					});
				}
			});
		}

		function closeForm() {
			window.location.href = '{{ route('phstokopnamehadiah') }}';
		}

		function printData(no_bukti) {
			$.ajax({
				url: "{{ route('phstokopnamehadiah.print') }}",
				type: 'POST',
				data: {
					no_bukti: no_bukti,
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					if (response.success && response.data && response.data.length > 0) {
						var printWindow = window.open('', '_blank');
						var printContent = generatePrintContent(response.data, response.toko);
						printWindow.document.write(printContent);
						printWindow.document.close();
						printWindow.print();
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Warning',
							text: 'Tidak ada data untuk dicetak'
						});
					}
				},
				error: function() {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Gagal mencetak data'
					});
				}
			});
		}

		function generatePrintContent(data, toko) {
			var content = `
				<!DOCTYPE html>
				<html>
				<head>
					<title>Laporan Stok Opname Hadiah</title>
					<style>
						body { font-family: Arial, sans-serif; font-size: 12px; }
						table { width: 100%; border-collapse: collapse; margin-top: 10px; }
						th, td { border: 1px solid #000; padding: 5px; text-align: left; }
						th { background-color: #f0f0f0; font-weight: bold; }
						.text-center { text-align: center; }
						.text-right { text-align: right; }
						.header { text-align: center; margin-bottom: 20px; }
						.info { margin-bottom: 10px; }
						.info-table { border: none; }
						.info-table td { border: none; padding: 3px; }
					</style>
				</head>
				<body>
					<div class="header">
						<h2>LAPORAN STOK OPNAME HADIAH</h2>
						<h4>${toko || ''}</h4>
					</div>
					<div class="info">
						<table class="info-table">
							<tr>
								<td style="width: 100px;"><strong>No Bukti</strong></td>
								<td>: ${data[0].bukt || data[0].no_bukti}</td>
							</tr>
						</table>
					</div>
					<table>
						<thead>
							<tr>
								<th class="text-center" width="10%">No</th>
								<th width="20%">Kode Barang</th>
								<th width="40%">Nama Barang</th>
								<th class="text-right" width="15%">Saldo</th>
								<th width="15%">Keterangan</th>
							</tr>
						</thead>
						<tbody>`;

			data.forEach((item, index) => {
				content += `
					<tr>
						<td class="text-center">${index + 1}</td>
						<td>${item.kd_brg}</td>
						<td>${item.na_brg}</td>
						<td class="text-right">${formatNumber(parseFloat(item.saldo || 0))}</td>
						<td>${item.ket_uk || ''}</td>
					</tr>`;
			});

			content += `
						</tbody>
					</table>
					<div style="margin-top: 30px;">
						<table class="info-table" style="width: 100%;">
							<tr>
								<td style="width: 50%; text-align: center;">
									<div style="margin-bottom: 60px;">Dibuat Oleh,</div>
									<div>___________________</div>
								</td>
								<td style="width: 50%; text-align: center;">
									<div style="margin-bottom: 60px;">Disetujui Oleh,</div>
									<div>___________________</div>
								</td>
							</tr>
						</table>
					</div>
				</body>
				</html>`;

			return content;
		}

		function formatNumber(num) {
			return parseFloat(num).toLocaleString('id-ID', {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2
			});
		}

		function escapeHtml(text) {
			var map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return text.replace(/[&<>"']/g, function(m) {
				return map[m];
			});
		}
	</script>
@endsection
