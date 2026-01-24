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

		/* Make all inputs readonly appearance */
		input[readonly],
		select[disabled],
		textarea[readonly] {
			background-color: #e9ecef !important;
			cursor: not-allowed;
		}
	</style>
@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Master Barang - View</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="{{ url('/brg') }}">Master Barang</a></li>
							<li class="breadcrumb-item active">View</li>
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
								<form id="formBrg">
									{{-- HEADER SECTION --}}
									<div class="header-section">
										<div class="row">
											<div class="col-md-3">
												<label>Kelompok Barang</label>
												<input type="text" id="txtSub" name="sub" class="form-control readonly-field" value="{{ $brg->sub ?? '' }}" readonly>
												<input type="text" id="txtKelompok" name="kelompok" class="form-control readonly-field mt-1" value="{{ $brg->kelompok ?? '' }}"
													readonly>
											</div>

											<div class="col-md-3">
												<label>Kode Barang</label>
												<div class="input-group">
													<input type="text" id="txtSubnd" name="subnd" class="form-control readonly-field" placeholder="Sub*" value="{{ $brg->subnd ?? '' }}"
														readonly maxlength="3" style="width: 40%;">
													<input type="text" id="txtKdbar" name="kdbar" class="form-control readonly-field" placeholder="No Item*"
														value="{{ $brg->kdbar ?? '' }}" readonly maxlength="4" style="width: 60%;">
												</div>
											</div>

											<div class="col-md-3">
												<label>Item Sup</label>
												<input type="text" id="txtItemSup" name="item_sup" class="form-control readonly-field" value="{{ $brg->item_sup ?? '' }}" readonly>
											</div>

											<div class="col-md-3">
												<label>Barcode</label>
												<input type="text" id="txtBarcode" name="barcode" class="form-control readonly-field" value="{{ $brg->barcode ?? '' }}" readonly>
											</div>
										</div>

										<div class="row mt-2">
											<div class="col-md-6">
												<label>Nama</label>
												<input type="text" id="txtNaBrg" name="na_brg" class="form-control readonly-field" value="{{ $brg->na_brg ?? '' }}" readonly>
											</div>

											<div class="col-md-3">
												<label>Type</label>
												<select id="cbType" name="type" class="form-control" disabled>
													<option value="FO" {{ ($brg->type ?? '') == 'FO' ? 'selected' : '' }}>FO</option>
													<option value="FF" {{ ($brg->type ?? '') == 'FF' ? 'selected' : '' }}>FF</option>
													<option value="NF" {{ ($brg->type ?? '') == 'NF' ? 'selected' : '' }}>NF</option>
													<option value="BSN" {{ ($brg->type ?? '') == 'BSN' ? 'selected' : '' }}>BSN</option>
												</select>
											</div>

											<div class="col-md-3">
												<label class="text-info">* Mode View Only</label>
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
													<input type="text" id="txtKemasan" name="ket_kem" class="form-control readonly-field" value="{{ $brg->ket_kem ?? '' }}" readonly>
												</div>

												<div class="col-md-3">
													<label>Minimal Order</label>
													<input type="number" id="txtMo" name="mo" class="form-control readonly-field" value="{{ $brg->mo ?? 0 }}" step="0.01"
														readonly>
												</div>

												<div class="col-md-3">
													<label>Tanda Retur</label>
													<select id="cbRetur" name="retur" class="form-control" disabled>
														<option value="T" {{ ($brg->retur ?? 'T') == 'T' ? 'selected' : '' }}>T</option>
														<option value="Y" {{ ($brg->retur ?? '') == 'Y' ? 'selected' : '' }}>Y</option>
													</select>
												</div>

												<div class="col-md-3">
													<label>DC</label>
													<div>
														<input type="checkbox" id="chkDC" name="dc" value="1" {{ ($brg->dc ?? 0) == 1 ? 'checked' : '' }} disabled>
													</div>
												</div>
											</div>

											<div class="row mt-2">
												<div class="col-md-3">
													<label>Ukuran</label>
													<input type="text" id="txtUkuran" name="ket_uk" class="form-control readonly-field" value="{{ $brg->ket_uk ?? '' }}" readonly>
												</div>

												<div class="col-md-3">
													<label>MO Outlet</label>
													<input type="number" id="txtMoo" name="moo" class="form-control readonly-field" value="{{ $brg->moo ?? 0 }}" step="0.01"
														readonly>
												</div>

												<div class="col-md-3">
													<label>PPN</label>
													<select id="cbPpn" name="ppn" class="form-control" disabled>
														<option value="0" {{ ($brg->ppn ?? 0) == 0 ? 'selected' : '' }}>0</option>
														<option value="1" {{ ($brg->ppn ?? 0) == 1 ? 'selected' : '' }}>1</option>
													</select>
												</div>
											</div>

											<div class="row mt-2">
												<div class="col-md-9">
													<label>Suplier</label>
													<div class="input-group">
														<input type="text" id="txtSupp" name="supp" class="form-control readonly-field" placeholder="Kode Supplier"
															value="{{ $brg->supp ?? '' }}" readonly style="width: 20%;">
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
													<input type="text" id="txtKlk" name="klk" class="form-control readonly-field" value="{{ $brg->klk ?? '' }}"
														maxlength="1" readonly>
												</div>

												<div class="col-md-2">
													<label>LPH</label>
													<input type="number" id="txtLph" name="lph" class="form-control readonly-field" value="{{ $brg->lph ?? 0 }}" step="0.01"
														readonly>
												</div>

												<div class="col-md-2">
													<label>DTR</label>
													<input type="number" id="txtDtr" name="dtr" class="form-control readonly-field" value="{{ $brg->dtr ?? 0 }}" step="0.01"
														readonly>
												</div>

												<div class="col-md-2">
													<label>Margin</label>
													<input type="number" id="txtMargin" name="margin" class="form-control readonly-field" value="{{ $brg->margin ?? 0 }}"
														step="0.01" readonly>
												</div>

												<div class="col-md-2">
													<label>Kd Laku</label>
													<input type="text" id="txtKdLaku" name="kdlaku" class="form-control readonly-field" value="{{ $brg->kdlaku ?? '' }}"
														maxlength="1" readonly>
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
																		<em>Loading...</em>
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
													<a href="{{ url('/brg') }}" class="btn btn-secondary">
														<i class="fas fa-arrow-left"></i> Kembali
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
			const kdBrg = '{{ $brg->kd_brg ?? '' }}';

			// Load data cabang on page load
			loadCabangData();

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
							renderCabangTable(response.data);
						}
					},
					error: function(xhr) {
						console.error('Error loading cabang data:', xhr.responseText);
						$('#tbodyCabang').html(
							'<tr><td colspan="12" class="text-center text-danger"><em>Error loading data</em></td></tr>');
					}
				});
			}

			function renderCabangTable(cabangData) {
				let html = '';
				if (cabangData.length > 0) {
					cabangData.forEach(function(item) {
						html += `<tr>
                    <td>${item.cbg}</td>
                    <td>${parseFloat(item.lph || 0).toFixed(2)}</td>
                    <td>${parseFloat(item.dtr || 0).toFixed(2)}</td>
                    <td>${item.klk || ''}</td>
                    <td>${item.kdlaku || ''}</td>
                    <td>${parseFloat(item.srmax || 0).toFixed(2)}</td>
                    <td>${parseFloat(item.srmin || 0).toFixed(2)}</td>
                    <td>${parseFloat(item.smax || 0).toFixed(2)}</td>
                    <td>${parseFloat(item.smin || 0).toFixed(2)}</td>
                    <td>${parseFloat(item.margin || 0).toFixed(2)}</td>
                    <td>${parseFloat(item.hj || 0).toFixed(2)}</td>
                    <td>${parseFloat(item.hb || 0).toFixed(2)}</td>
                </tr>`;
					});
				} else {
					html = '<tr><td colspan="12" class="text-center"><em>Tidak ada data</em></td></tr>';
				}
				$('#tbodyCabang').html(html);
			}

			// Show error message from session
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
