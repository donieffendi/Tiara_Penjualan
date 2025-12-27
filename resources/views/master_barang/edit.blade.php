@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="{{ url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<style>
		.form-control:focus {
			background-color: #E0FFFF !important;
		}

		.header-section {
			background-color: #f8f9fa;
			padding: 15px;
			border: 1px solid #dee2e6;
			border-radius: 5px;
			margin-bottom: 15px;
		}

		.header-section label {
			font-weight: bold;
			margin-bottom: 5px;
		}

		.readonly-field {
			background-color: #e9ecef !important;
		}

		.tab-main {
			border: 1px solid #dee2e6;
			border-top: none;
			padding: 20px;
		}
	</style>
@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Master Barang - {{ $mode == 'add' ? 'Tambah Baru' : 'Edit' }}</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="{{ url('/brg') }}">Master Barang</a></li>
							<li class="breadcrumb-item active">{{ $mode == 'add' ? 'Tambah' : 'Edit' }}</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form id="formBrg" method="POST">
									@csrf
									<input type="hidden" name="mode" value="{{ $mode }}">
									@if ($mode == 'edit')
										<input type="hidden" name="no_id" value="{{ $brg->NO_ID ?? '' }}">
										<input type="hidden" name="kd_brg" value="{{ $brg->kd_brg ?? '' }}">
									@endif

									{{-- HEADER SECTION --}}
									<div class="header-section">
										<div class="row">
											<div class="col-md-3">
												<label>Kelompok Barang</label>
												<input type="text" id="txtSub" name="sub" class="form-control" value="{{ $brg->sub ?? '' }}"
													{{ $mode == 'edit' ? 'readonly' : '' }}>
												<input type="text" id="txtKelompok" name="kelompok" class="form-control readonly-field mt-1" value="{{ $brg->kelompok ?? '' }}"
													readonly>
											</div>

											<div class="col-md-3">
												<label>Kode Barang</label>
												<div class="input-group">
													<input type="text" id="txtSubnd" name="subnd" class="form-control" placeholder="Sub*" value="{{ $brg->subnd ?? '' }}"
														{{ $mode == 'edit' ? 'readonly' : '' }} maxlength="3" style="width: 40%;">
													<input type="text" id="txtKdbar" name="kdbar" class="form-control" placeholder="No Item*" value="{{ $brg->kdbar ?? '' }}"
														{{ $mode == 'edit' ? 'readonly' : '' }} maxlength="4" style="width: 60%;">
												</div>
											</div>

											<div class="col-md-3">
												<label>Item Sup</label>
												<input type="text" id="txtItemSup" name="item_sup" class="form-control" value="{{ $brg->item_sup ?? '' }}">
											</div>

											<div class="col-md-3">
												<label>Barcode</label>
												<input type="text" id="txtBarcode" name="barcode" class="form-control" value="{{ $brg->barcode ?? '' }}">
											</div>
										</div>

										<div class="row mt-2">
											<div class="col-md-6">
												<label>Nama</label>
												<input type="text" id="txtNaBrg" name="na_brg" class="form-control" value="{{ $brg->na_brg ?? '' }}" required>
											</div>

											<div class="col-md-3">
												<label>Type</label>
												<select id="cbType" name="type" class="form-control">
													<option value="FO" {{ ($brg->type ?? '') == 'FO' ? 'selected' : '' }}>FO</option>
													<option value="FF" {{ ($brg->type ?? '') == 'FF' ? 'selected' : '' }}>FF</option>
													<option value="NF" {{ ($brg->type ?? '') == 'NF' ? 'selected' : '' }}>NF</option>
													<option value="BSN" {{ ($brg->type ?? '') == 'BSN' ? 'selected' : '' }}>BSN</option>
												</select>
											</div>

											<div class="col-md-3">
												@if ($flag == 'DCK')
													<label class="text-danger">* Cuma Bisa Edit LPH</label>
												@else
													<label class="text-info">* Cuma Bisa Edit DC & Margin</label>
												@endif
											</div>
										</div>
									</div>

									{{-- TAB MAIN --}}
									<ul class="nav nav-tabs" id="mainTab" role="tablist">
										<li class="nav-item">
											<a class="nav-link active" id="main-tab" data-toggle="tab" href="#tabMain" role="tab">
												<strong>Main</strong>
											</a>
										</li>
									</ul>

									<div class="tab-content">
										<div class="tab-pane fade show active tab-main" id="tabMain" role="tabpanel">
											<div class="row">
												<div class="col-md-3">
													<label>Kemasan</label>
													<input type="text" id="txtKemasan" name="ket_kem" class="form-control" value="{{ $brg->ket_kem ?? '' }}">
												</div>

												<div class="col-md-3">
													<label>Minimal Order</label>
													<input type="number" id="txtMo" name="mo" class="form-control" value="{{ $brg->mo ?? 0 }}" step="0.01">
												</div>

												<div class="col-md-3">
													<label>Tanda Retur</label>
													<select id="cbRetur" name="retur" class="form-control">
														<option value="T" {{ ($brg->retur ?? 'T') == 'T' ? 'selected' : '' }}>T</option>
														<option value="Y" {{ ($brg->retur ?? '') == 'Y' ? 'selected' : '' }}>Y</option>
													</select>
												</div>

												<div class="col-md-3">
													<label>DC</label>
													<div>
														<input type="checkbox" id="chkDC" name="dc" value="1" {{ ($brg->dc ?? 0) == 1 ? 'checked' : '' }}
															{{ $flag == 'DCK' ? 'disabled' : '' }}>
														@if ($flag == 'DCK')
															<input type="hidden" name="dc" value="{{ $brg->dc ?? 0 }}">
														@endif
													</div>
												</div>
											</div>

											<div class="row mt-2">
												<div class="col-md-3">
													<label>Ukuran</label>
													<input type="text" id="txtUkuran" name="ket_uk" class="form-control" value="{{ $brg->ket_uk ?? '' }}">
												</div>

												<div class="col-md-3">
													<label>MO Outlet</label>
													<input type="number" id="txtMoo" name="moo" class="form-control" value="{{ $brg->moo ?? 0 }}" step="0.01">
												</div>

												<div class="col-md-3">
													<label>PPN</label>
													<select id="cbPpn" name="ppn" class="form-control">
														<option value="0" {{ ($brg->ppn ?? 0) == 0 ? 'selected' : '' }}>0</option>
														<option value="1" {{ ($brg->ppn ?? 0) == 1 ? 'selected' : '' }}>1</option>
													</select>
												</div>
											</div>

											<div class="row mt-2">
												<div class="col-md-9">
													<label>Suplier</label>
													<div class="input-group">
														<input type="text" id="txtSupp" name="supp" class="form-control" placeholder="Kode Supplier"
															value="{{ $brg->supp ?? '' }}" style="width: 20%;">
														<input type="text" id="txtNamas" name="namas" class="form-control readonly-field" value="{{ $brg->nsup ?? '' }}" readonly
															style="width: 40%;">
														<input type="text" id="txtAlamat" name="alamat" class="form-control readonly-field" value="{{ $brg->alamat ?? '' }}" readonly
															style="width: 30%;">
														<input type="text" id="txtKota" name="kota" class="form-control readonly-field" value="{{ $brg->kota ?? '' }}" readonly
															style="width: 10%;">
													</div>
												</div>

												<div class="col-md-3"></div>
											</div>

											<div class="row mt-3">
												<div class="col-md-2">
													<label>KLK</label>
													<input type="text" id="txtKlk" name="klk" class="form-control" value="{{ $brg->klk ?? '' }}" maxlength="1">
												</div>

												<div class="col-md-2">
													<label>LPH</label>
													<input type="number" id="txtLph" name="lph" class="form-control" value="{{ $brg->lph ?? 0 }}" step="0.01"
														{{ $flag != 'DCK' ? 'readonly' : '' }}>
												</div>

												<div class="col-md-2">
													<label>DTR</label>
													<input type="number" id="txtDtr" name="dtr" class="form-control" value="{{ $brg->dtr ?? 0 }}" step="0.01">
												</div>

												<div class="col-md-2">
													<label>Margin</label>
													<input type="number" id="txtMargin" name="margin" class="form-control" value="{{ $brg->margin ?? 0 }}" step="0.01"
														{{ $flag == 'DCK' ? 'readonly' : '' }}>
												</div>

												<div class="col-md-2">
													<label>Kd Laku</label>
													<input type="text" id="txtKdLaku" name="kdlaku" class="form-control" value="{{ $brg->kdlaku ?? '' }}" maxlength="1">
												</div>
											</div>

											{{-- DATA TABLE PER CABANG --}}
											<div class="row mt-4">
												<div class="col-12">
													<h5>Data Per Cabang</h5>
													<div class="table-responsive">
														<table id="tableCabang" class="table-bordered table-striped table-sm table">
															<thead>
																<tr>
																	<th>CBG</th>
																	<th>LPH</th>
																	<th>DTR</th>
																	<th>KLK</th>
																	<th>kdlaku</th>
																	<th>srmax</th>
																	<th>srmin</th>
																	<th>smax</th>
																	<th>smin</th>
																	<th>margin</th>
																	<th>H.Jual</th>
																	<th>H.Beli</th>
																</tr>
															</thead>
															<tbody id="tbodyCabang">
																<tr>
																	<td colspan="12" class="text-center">
																		<em>Tidak ada data</em>
																	</td>
																</tr>
															</tbody>
														</table>
													</div>
												</div>
											</div>

											{{-- ACTION BUTTONS --}}
											<div class="row mt-4">
												<div class="col-12">
													<button type="button" id="btnSave" class="btn btn-primary">
														<i class="fas fa-save"></i> Save
													</button>
													<a href="{{ url('/brg') }}" class="btn btn-secondary">
														<i class="fas fa-times"></i> Exit
													</a>
												</div>
											</div>
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
@endsection

@section('scripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="{{ url('AdminLTE/plugins/datatables/jquery.dataTables.js') }}"></script>
	<script src="{{ url('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>

	<script>
		$(document).ready(function() {
			const mode = '{{ $mode }}';
			const kdBrg = '{{ $brg->kd_brg ?? '' }}';
			let cabangData = [];

			// Load data cabang on page load
			loadCabangData();

			// Auto-generate kode barang saat subnd diisi (mode add)
			$('#txtSubnd').on('blur', function() {
				if (mode == 'add' && $(this).val().length == 3) {
					$.ajax({
						url: '{{ url('/brg/get-next-kdbar') }}',
						type: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							subnd: $(this).val()
						},
						success: function(response) {
							if (response.success) {
								$('#txtKdbar').val(response.kdbar);
							}
						},
						error: function(xhr) {
							console.error('Error getting next kdbar:', xhr.responseText);
						}
					});
				}
			});

			// Lookup SUB/Kelompok
			$('#txtSub').on('blur', function() {
				const sub = $(this).val();
				if (sub.length >= 3) {
					$.ajax({
						url: '{{ url('/brg/lookup-sub') }}',
						type: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							sub: sub
						},
						success: function(response) {
							if (response.success) {
								$('#txtSub').val(response.data.sub);
								$('#txtKelompok').val(response.data.kelompok);
								$('#txtMargin').val(response.data.persen);
								updateAllCabangMargin(response.data.persen);
							} else {
								Swal.fire({
									icon: 'warning',
									title: 'Sub Tidak Ditemukan',
									text: response.message
								});
							}
						},
						error: function(xhr) {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: 'Terjadi kesalahan saat lookup sub'
							});
						}
					});
				}
			});

			// Lookup Supplier
			$('#txtSupp').on('blur', function() {
				const kodes = $(this).val();
				if (kodes) {
					$.ajax({
						url: '{{ url('/brg/lookup-supplier') }}',
						type: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							kodes: kodes
						},
						success: function(response) {
							if (response.success) {
								$('#txtNamas').val(response.data.namas);
								$('#txtAlamat').val(response.data.almt_k);
								$('#txtKota').val(response.data.kota);
							} else {
								Swal.fire({
									icon: 'warning',
									title: 'Supplier Tidak Ditemukan',
									text: response.message
								});
								$('#txtNamas').val('');
								$('#txtAlamat').val('');
								$('#txtKota').val('');
							}
						},
						error: function(xhr) {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: 'Terjadi kesalahan saat lookup supplier'
							});
						}
					});
				}
			});

			// Update semua cabang ketika field master berubah
			$('#txtKlk, #txtLph, #txtDtr, #txtMargin, #txtKdLaku').on('blur', function() {
				const field = $(this).attr('name');
				const value = $(this).val();
				updateAllCabang(field, value);
			});

			function loadCabangData() {
				$.ajax({
					url: '{{ url('/brg/get-brgdt-cabang') }}',
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						kd_brg: kdBrg
					},
					success: function(response) {
						if (response.success) {
							cabangData = response.data;
							renderCabangTable();
						}
					},
					error: function(xhr) {
						console.error('Error loading cabang data:', xhr.responseText);
					}
				});
			}

			function renderCabangTable() {
				let html = '';
				if (cabangData.length > 0) {
					cabangData.forEach(function(item, index) {
						html += `<tr>
                    <td>${item.cbg}</td>
                    <td><input type="number" class="form-control form-control-sm" name="cabang[${index}][lph]" value="${item.lph || 0}" step="0.01"></td>
                    <td><input type="number" class="form-control form-control-sm" name="cabang[${index}][dtr]" value="${item.dtr || 0}" step="0.01"></td>
                    <td><input type="text" class="form-control form-control-sm" name="cabang[${index}][klk]" value="${item.klk || ''}" maxlength="1"></td>
                    <td><input type="text" class="form-control form-control-sm" name="cabang[${index}][kdlaku]" value="${item.kdlaku || ''}" maxlength="1"></td>
                    <td><input type="number" class="form-control form-control-sm" name="cabang[${index}][srmax]" value="${item.srmax || 0}" step="0.01" readonly></td>
                    <td><input type="number" class="form-control form-control-sm" name="cabang[${index}][srmin]" value="${item.srmin || 0}" step="0.01" readonly></td>
                    <td><input type="number" class="form-control form-control-sm" name="cabang[${index}][smax]" value="${item.smax || 0}" step="0.01" readonly></td>
                    <td><input type="number" class="form-control form-control-sm" name="cabang[${index}][smin]" value="${item.smin || 0}" step="0.01" readonly></td>
                    <td><input type="number" class="form-control form-control-sm" name="cabang[${index}][margin]" value="${item.margin || 0}" step="0.01" readonly></td>
                    <td><input type="number" class="form-control form-control-sm" name="cabang[${index}][hj]" value="${item.hj || 0}" step="0.01" readonly></td>
                    <td><input type="number" class="form-control form-control-sm" name="cabang[${index}][hb]" value="${item.hb || 0}" step="0.01" readonly></td>
                    <input type="hidden" name="cabang[${index}][cbg]" value="${item.cbg}">
                </tr>`;
					});
				} else {
					html = '<tr><td colspan="12" class="text-center"><em>Tidak ada data</em></td></tr>';
				}
				$('#tbodyCabang').html(html);
			}

			function updateAllCabang(field, value) {
				cabangData.forEach(function(item) {
					item[field] = value;
				});
				renderCabangTable();
			}

			function updateAllCabangMargin(margin) {
				cabangData.forEach(function(item) {
					item.margin = margin;
				});
				renderCabangTable();
			}

			// Save button
			$('#btnSave').on('click', function() {
				// Validasi
				if (!$('#txtSubnd').val() || !$('#txtKdbar').val()) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Kode barang (Sub & No Item) harus diisi!'
					});
					return;
				}

				if (!$('#txtNaBrg').val()) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Nama barang harus diisi!'
					});
					return;
				}

				if (!$('#txtSub').val()) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Sub/Kelompok harus diisi!'
					});
					return;
				}

				// Collect cabang data
				const cabangDataToSend = [];
				$('#tbodyCabang tr').each(function() {
					const cbg = $(this).find('input[name*="[cbg]"]').val();
					if (cbg) {
						cabangDataToSend.push({
							cbg: cbg,
							lph: $(this).find('input[name*="[lph]"]').val() || 0,
							dtr: $(this).find('input[name*="[dtr]"]').val() || 0,
							klk: $(this).find('input[name*="[klk]"]').val() || '',
							kdlaku: $(this).find('input[name*="[kdlaku]"]').val() || '',
							srmax: $(this).find('input[name*="[srmax]"]').val() || 0,
							srmin: $(this).find('input[name*="[srmin]"]').val() || 0,
							smax: $(this).find('input[name*="[smax]"]').val() || 0,
							smin: $(this).find('input[name*="[smin]"]').val() || 0,
							margin: $(this).find('input[name*="[margin]"]').val() || 0,
							hj: $(this).find('input[name*="[hj]"]').val() || 0,
							hb: $(this).find('input[name*="[hb]"]').val() || 0
						});
					}
				});

				// Prepare form data
				const formData = $('#formBrg').serializeArray();
				formData.push({
					name: 'cabang_data',
					value: JSON.stringify(cabangDataToSend)
				});

				// Show loading
				Swal.fire({
					title: 'Menyimpan...',
					text: 'Mohon tunggu',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				// Submit
				$.ajax({
					url: '{{ url('/brg/store') }}',
					type: 'POST',
					data: $.param(formData),
					success: function(response) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							showConfirmButton: true
						}).then(() => {
							window.location.href = '{{ url('/brg') }}';
						});
					},
					error: function(xhr) {
						let errorMsg = 'Terjadi kesalahan saat menyimpan data';
						if (xhr.responseJSON && xhr.responseJSON.message) {
							errorMsg = xhr.responseJSON.message;
						}
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: errorMsg
						});
					}
				});
			});

			// Show success/error message from session
			@if (session('success'))
				Swal.fire({
					icon: 'success',
					title: 'Berhasil',
					text: '{{ session('success') }}'
				});
			@endif

			@if (session('error'))
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: '{{ session('error') }}'
				});
			@endif
		});
	</script>
@endsection
