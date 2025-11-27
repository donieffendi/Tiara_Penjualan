@extends('layouts.plain')

@section('content')
	<style>
		.form-control:focus {
			background-color: #E0FFFF !important;
		}

		.nav-item .nav-link.active {
			background-color: #007bff !important;
			color: white !important;
		}

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
			height: 32px;
		}

		.form-group.row .form-control {
			line-height: 1.5;
			height: 32px;
		}

		#detailTable,
		#barangTerkaitTable {
			font-size: 12px;
		}

		#detailTable th,
		#detailTable td,
		#barangTerkaitTable th,
		#barangTerkaitTable td {
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

		.tab-content {
			border: 1px solid #dee2e6;
			border-top: none;
			padding: 15px;
		}
	</style>

	<div class="content-wrapper">
		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<h3 class="card-title">Program Promosi Hadiah - {{ $status == 'simpan' ? 'New' : 'Edit' }}</h3>
							</div>
							<div class="card-body">
								<form action="{{ route('phprogrampromosihadiah.store') }}" method="POST" name="entri" id="entri">
									@csrf
									<input type="hidden" id="status" name="status" value="{{ $status }}">

									<!-- Tabs -->
									<div class="row">
										<div class="col-md-12">
											<ul class="nav nav-tabs" id="myTab" role="tablist">
												<li class="nav-item" role="presentation">
													<button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail" type="button"
														role="tab">Detail</button>
												</li>
												<li class="nav-item" role="presentation">
													<button class="nav-link" id="barang-tab" data-bs-toggle="tab" data-bs-target="#barang" type="button" role="tab">Barang
														Terkait</button>
												</li>
											</ul>
											<div class="tab-content" id="myTabContent">
												<!-- Tab Detail -->
												<div class="tab-pane fade show active" id="detail" role="tabpanel">
													<!-- Header Form Fields -->
													<!-- Row 1 -->
													<div class="row">
														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-4">
																	<label class="form-label">Bukti</label>
																</div>
																<div class="col-md-8">
																	<input type="text" class="form-control form-control-sm" id="no_bukti" name="no_bukti"
																		value="{{ $status == 'simpan' ? '+' : $no_bukti }}" readonly>
																</div>
															</div>
														</div>

														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-4">
																	<label class="form-label">Date</label>
																</div>
																<div class="col-md-8">
																	<input type="date" class="form-control form-control-sm" id="tgl" name="tgl"
																		value="{{ $header && $header->tgl ? date('Y-m-d', strtotime($header->tgl)) : date('Y-m-d') }}" required>
																</div>
															</div>
														</div>

														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-4">
																	<label class="form-label">Supplier</label>
																</div>
																<div class="col-md-8">
																	<div class="input-group input-group-sm">
																		<input type="text" class="form-control form-control-sm" id="kodes" name="kodes" value="{{ $header->kodes ?? '' }}" required>
																		<button type="button" class="btn btn-sm btn-primary" onclick="browseSupplier()">
																			<i class="fas fa-search"></i>
																		</button>
																	</div>
																</div>
															</div>
														</div>

														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-4">
																	<label class="form-label">Type</label>
																</div>
																<div class="col-md-8">
																	<select class="form-control form-control-sm" id="cbtype" name="cbtype" required>
																		<option value="">-- Pilih --</option>
																		<option value="HIJAU" {{ ($header->type ?? '') == 'H' ? 'selected' : '' }}>HIJAU</option>
																		<option value="CASH" {{ ($header->type ?? '') == 'U' ? 'selected' : '' }}>CASH</option>
																		<option value="VARIAN" {{ ($header->type ?? '') == 'V' ? 'selected' : '' }}>VARIAN</option>
																		<option value="BANK" {{ ($header->type ?? '') == 'B' ? 'selected' : '' }}>BANK</option>
																	</select>
																</div>
															</div>
														</div>
													</div>

													<!-- Row 2 -->
													<div class="row">
														<div class="col-md-6">
															<div class="form-group row">
																<div class="col-md-2">
																	<label class="form-label">Nama</label>
																</div>
																<div class="col-md-10">
																	<input type="text" class="form-control form-control-sm" id="namas" name="namas" value="{{ $header->namas ?? '' }}"
																		readonly required>
																</div>
															</div>
														</div>

														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-4">
																	<label class="form-label">Kode Promo</label>
																</div>
																<div class="col-md-8">
																	<input type="text" class="form-control form-control-sm" id="kd_prm" name="kd_prm" value="{{ $header->kd_prm ?? '' }}"
																		{{ $status == 'edit' ? 'readonly' : '' }} required>
																</div>
															</div>
														</div>

														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-4">
																	<label class="form-label">Jumlah Beli</label>
																</div>
																<div class="col-md-8">
																	<input type="number" class="form-control form-control-sm" id="txtqtybl" name="txtqtybl" value="{{ $header->qty_beli ?? 0 }}"
																		step="1">
																</div>
															</div>
														</div>
													</div>

													<!-- Row 3 -->
													<div class="row">
														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-4">
																	<label class="form-label">Total Beli</label>
																</div>
																<div class="col-md-8">
																	<input type="number" class="form-control form-control-sm" id="txttotalbl" name="txttotalbl" value="{{ $header->rp_beli ?? 0 }}"
																		step="0.01">
																</div>
															</div>
														</div>

														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-4">
																	<label class="form-label">Kondisi</label>
																</div>
																<div class="col-md-8">
																	<select class="form-control form-control-sm" id="cbkondisi" name="cbkondisi">
																		<option value="">-- Pilih --</option>
																		<option value="TOTAL SEMUA BELANJA" {{ ($header->kondisi ?? '') == 'H' ? 'selected' : '' }}>TOTAL SEMUA BELANJA
																		</option>
																		<option value="TOTAL BELANJA BRG PROMO" {{ ($header->kondisi ?? '') == 'D' ? 'selected' : '' }}>TOTAL BELANJA BRG PROMO
																		</option>
																	</select>
																</div>
															</div>
														</div>

														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-6">
																	<div class="form-check">
																		<input class="form-check-input" type="checkbox" id="cbkeli" name="cbkeli"
																			{{ ($header->kelipatan ?? 0) == 1 ? 'checked' : '' }}>
																		<label class="form-check-label" for="cbkeli">Kelipatan</label>
																	</div>
																</div>
																<div class="col-md-6">
																	<input type="number" class="form-control form-control-sm" id="txtmaxh" name="txtmaxh" value="{{ $header->maxh ?? 0 }}"
																		placeholder="Max">
																</div>
															</div>
														</div>

														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-4">
																	<label class="form-label">Bank</label>
																</div>
																<div class="col-md-8">
																	<select class="form-control form-control-sm" id="cbbank" name="cbbank">
																		<option value="">-- Pilih Bank --</option>
																		@foreach ($banks as $bank)
																			<option value="{{ $bank->acno }}" {{ ($header->nkartu ?? '') == $bank->acno ? 'selected' : '' }}>
																				{{ $bank->acno }} - {{ $bank->nacno }}
																			</option>
																		@endforeach
																	</select>
																</div>
															</div>
														</div>
													</div>

													<!-- Row 4 -->
													<div class="row">
														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-4">
																	<label class="form-label">Hubungan</label>
																</div>
																<div class="col-md-8">
																	<select class="form-control form-control-sm" id="cbjns" name="cbjns">
																		<option value="">-- Pilih --</option>
																		<option value="DAN" {{ ($header->jns ?? '') == 'AND' ? 'selected' : '' }}>DAN</option>
																		<option value="ATAU" {{ ($header->jns ?? '') == 'OR' ? 'selected' : '' }}>ATAU</option>
																	</select>
																</div>
															</div>
														</div>

														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-4">
																	<label class="form-label">Mulai Dari</label>
																</div>
																<div class="col-md-8">
																	<input type="date" class="form-control form-control-sm" id="tg_mulai" name="tg_mulai"
																		value="{{ $header && $header->tg_mulai ? date('Y-m-d', strtotime($header->tg_mulai)) : date('Y-m-d') }}" required>
																</div>
															</div>
														</div>

														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-4">
																	<label class="form-label">s/d</label>
																</div>
																<div class="col-md-8">
																	<input type="date" class="form-control form-control-sm" id="tg_akhir" name="tg_akhir"
																		value="{{ $header && $header->tg_akhir ? date('Y-m-d', strtotime($header->tg_akhir)) : date('Y-m-d') }}" required>
																</div>
															</div>
														</div>

														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-4">
																	<label class="form-label">Jam</label>
																</div>
																<div class="col-md-4">
																	<input type="time" class="form-control form-control-sm" id="jm_mulai" name="jm_mulai"
																		value="{{ $header->jm_mulai ?? '00:01' }}" required>
																</div>
																<div class="col-md-4">
																	<input type="time" class="form-control form-control-sm" id="jm_akhir" name="jm_akhir"
																		value="{{ $header->jm_akhir ?? '23:59' }}" required>
																</div>
															</div>
														</div>
													</div>

													<!-- Row 5 -->
													<div class="row">
														<div class="col-md-9">
															<div class="form-group row">
																<div class="col-md-1">
																	<label class="form-label">Keterangan</label>
																</div>
																<div class="col-md-11">
																	<input type="text" class="form-control form-control-sm" id="ket" name="ket" value="{{ $header->ket ?? '' }}">
																</div>
															</div>
														</div>

														<div class="col-md-3">
															<div class="form-group row">
																<div class="col-md-3">
																	<label class="form-label">Cabang</label>
																</div>
																<div class="col-md-9">
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="checkbox" id="ctgz" name="ctgz" {{ ($header->tgz ?? 0) == 1 ? 'checked' : '' }}>
																		<label class="form-check-label" for="ctgz">TGZ</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="checkbox" id="ctmm" name="ctmm" {{ ($header->tmm ?? 0) == 1 ? 'checked' : '' }}>
																		<label class="form-check-label" for="ctmm">TMM</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="checkbox" id="csop" name="csop" {{ ($header->sop ?? 0) == 1 ? 'checked' : '' }}>
																		<label class="form-check-label" for="csop">SOP</label>
																	</div>
																</div>
															</div>
														</div>
													</div>

													<hr>

													<!-- Detail Entry & Table -->
													<div class="row mb-2">
														<div class="col-md-2">
															<label class="form-label">Kode</label>
															<div class="input-group input-group-sm">
																<input type="text" class="form-control form-control-sm" id="kd_brgh_entry">
																<button type="button" class="btn btn-sm btn-primary" onclick="browseBarang()">
																	<i class="fas fa-search"></i>
																</button>
															</div>
														</div>
														<div class="col-md-4">
															<label class="form-label">Nama Hadiah</label>
															<input type="text" class="form-control form-control-sm" id="na_brgh_entry" readonly>
														</div>
														<div class="col-md-2">
															<label class="form-label">Qty</label>
															<input type="number" class="form-control form-control-sm" id="qty_entry" step="1">
														</div>
														<div class="col-md-2">
															<label>&nbsp;</label>
															<button type="button" class="btn btn-sm btn-success btn-block" onclick="addDetailRow()">
																<i class="fas fa-plus"></i> Tambah
															</button>
														</div>
													</div>

													<div class="table-responsive">
														<table class="table-bordered table-sm table table-fixed" id="detailTable">
															<thead>
																<tr>
																	<th width="8%">No</th>
																	<th width="12%">Qty</th>
																	<th width="20%">Kode</th>
																	<th width="50%">Nama Hadiah / Uraian</th>
																	<th width="10%">Action</th>
																</tr>
															</thead>
															<tbody id="detailTableBody">
																@if ($status == 'edit' && !empty($detail))
																	@foreach ($detail as $index => $item)
																		<tr>
																			<td class="text-center">{{ $index + 1 }}</td>
																			<td class="text-right">{{ number_format($item->qty, 0) }}</td>
																			<td>{{ $item->kd_brgh }}</td>
																			<td>{{ $item->na_brgh }}</td>
																			<td class="text-center">
																				<button type="button" class="btn btn-sm btn-danger" onclick="removeDetailRow(this)">
																					<i class="fas fa-trash"></i>
																				</button>
																			</td>
																			<input type="hidden" name="details[{{ $index }}][no_id]" value="{{ $item->no_id ?? 0 }}">
																			<input type="hidden" name="details[{{ $index }}][rec]" value="{{ $item->rec ?? $index + 1 }}">
																			<input type="hidden" name="details[{{ $index }}][kd_brgh]" value="{{ $item->kd_brgh }}">
																			<input type="hidden" name="details[{{ $index }}][na_brgh]" value="{{ $item->na_brgh }}">
																			<input type="hidden" name="details[{{ $index }}][qty]" value="{{ $item->qty }}">
																		</tr>
																	@endforeach
																@endif
															</tbody>
														</table>
													</div>
												</div>

												<!-- Tab Barang Terkait -->
												<div class="tab-pane fade" id="barang" role="tabpanel">
													<div class="row mb-2">
														<div class="col-md-2">
															<label class="form-label">Supplier</label>
															<input type="text" class="form-control form-control-sm" id="txtsup1" placeholder="Dari">
														</div>
														<div class="col-md-2">
															<label class="form-label">s/d</label>
															<input type="text" class="form-control form-control-sm" id="txtsup2" placeholder="Sampai">
														</div>
														<div class="col-md-2">
															<label class="form-label">Sub</label>
															<input type="text" class="form-control form-control-sm" id="txtsub1" placeholder="Dari">
														</div>
														<div class="col-md-2">
															<label class="form-label">s/d</label>
															<input type="text" class="form-control form-control-sm" id="txtsub2" placeholder="Sampai">
														</div>
														<div class="col-md-2">
															<label class="form-label">Sub Item</label>
															<input type="text" class="form-control form-control-sm" id="txtnoitem" placeholder="Optional">
														</div>
														<div class="col-md-2">
															<label>&nbsp;</label>
															<button type="button" class="btn btn-sm btn-primary btn-block" onclick="updateMasksDialog()">
																<i class="fas fa-plus"></i> Tambahkan
															</button>
														</div>
													</div>

													<div class="row mb-2">
														<div class="col-md-12">
															<button type="button" class="btn btn-sm btn-warning" onclick="clearMasksDialog()">
																<i class="fas fa-eraser"></i> Hapus Semua
															</button>
															<button type="button" class="btn btn-sm btn-info" onclick="loadBarangTerkait()">
																<i class="fas fa-sync"></i> Refresh
															</button>
														</div>
													</div>

													<div class="table-responsive">
														<table class="table-bordered table-sm table" id="barangTerkaitTable">
															<thead>
																<tr>
																	<th width="15%">Sub Item</th>
																	<th width="45%">Nama Barang</th>
																	<th width="20%">Ukuran</th>
																	<th width="20%">Kemasan</th>
																</tr>
															</thead>
															<tbody id="barangTerkaitBody">
																<tr>
																	<td colspan="4" class="text-center">Klik "Refresh" untuk menampilkan barang terkait</td>
																</tr>
															</tbody>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Action Buttons -->
									<div class="row mt-3">
										<div class="col-md-12">
											<button type="button" id="SAVEX" onclick="simpan()" class="btn btn-success">
												<i class="fas fa-save"></i> Save
											</button>
											<button type="button" id="CLOSEX" onclick="closeForm()" class="btn btn-outline-secondary">
												<i class="fas fa-times"></i> Close
											</button>
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

	<!-- Browse Supplier Modal -->
	<div class="modal fade" id="browseSupplierModal" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Browse Supplier</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<input type="text" class="form-control" id="searchSupplier" placeholder="Cari supplier...">
					</div>
					<div class="table-responsive">
						<table class="table-bordered table-sm table" id="supplierTable">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Nama Supplier</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="supplierTableBody"></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Browse Barang Modal -->
	<div class="modal fade" id="browseBarangModal" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Browse Barang Hadiah</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
				</div>
				<div class="modal-body">
					<div class="table-responsive">
						<table class="table-bordered table-sm table" id="barangTable">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Nama Barang</th>
									<th>Ukuran</th>
									<th>Kemasan</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="barangTableBody"></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('footer-scripts')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		var detailRowIndex = {{ $status == 'edit' && !empty($detail) ? count($detail) : 0 }};

		$(document).ready(function() {
			@if ($status == 'simpan')
				$('#tgl').focus();
			@else
				$('#kodes').focus();
			@endif

			$('#cbtype').change(function() {
				handleTypeChange();
			});

			handleTypeChange();

			$('#searchSupplier').on('keyup', function() {
				var query = $(this).val();
				if (query.length > 2 || query.length === 0) {
					searchSupplier(query);
				}
			});

			$('body').on('keydown', function(e) {
				if (e.key === "Enter") {
					e.preventDefault();
					handleEnterKey(e.target);
					return false;
				}
			});
		});

		function handleTypeChange() {
			var type = $('#cbtype').val();

			if (type === 'BANK') {
				$('#cbbank').closest('.form-group').show();
				$('#txttotalbl').closest('.form-group').show();
				$('#txtqtybl').closest('.form-group').hide();
				$('#cbkondisi').closest('.form-group').hide();
				$('#cbjns').closest('.form-group').hide();
			} else if (type === 'HIJAU') {
				$('#txtqtybl').closest('.form-group').show();
				$('#txttotalbl').closest('.form-group').hide();
				$('#cbbank').closest('.form-group').hide();
				$('#cbkondisi').closest('.form-group').hide();
				$('#cbjns').closest('.form-group').hide();
			} else if (type === 'VARIAN') {
				$('#txtqtybl').closest('.form-group').show();
				$('#txttotalbl').closest('.form-group').show();
				$('#cbkondisi').closest('.form-group').show();
				$('#cbjns').closest('.form-group').show();
				$('#cbbank').closest('.form-group').hide();
			} else if (type === 'CASH') {
				$('#txttotalbl').closest('.form-group').show();
				$('#txtqtybl').closest('.form-group').hide();
				$('#cbbank').closest('.form-group').hide();
				$('#cbkondisi').closest('.form-group').hide();
				$('#cbjns').closest('.form-group').hide();
			}
		}

		function handleEnterKey(element) {
			var $element = $(element);
			var id = $element.attr('id');

			switch (id) {
				case 'kodes':
					handleKodesEnter($element.val().trim());
					break;
				case 'kd_brgh_entry':
					handleKdBrghEnter($element.val().trim());
					break;
				case 'qty_entry':
					addDetailRow();
					break;
				default:
					var form = $element.parents('form:eq(0)');
					var focusable = form.find('input,select,textarea').filter(':visible:not([readonly])');
					var next = focusable.eq(focusable.index(element) + 1);
					if (next.length) {
						next.focus().select();
					}
					break;
			}
		}

		function handleKodesEnter(kodes) {
			if (kodes) {
				$.ajax({
					url: '{{ route('phprogrampromosihadiah.detail') }}',
					type: 'GET',
					data: {
						kodes: kodes,
						type: 'supplier'
					},
					success: function(response) {
						if (response.exists && response.data) {
							$('#kodes').val(response.data.kodes);
							$('#namas').val(response.data.namas);
							$('#kd_prm').focus();
						} else {
							browseSupplier();
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal mengambil data supplier'
						});
					}
				});
			}
		}

		function handleKdBrghEnter(kd_brgh) {
			if (kd_brgh) {
				$.ajax({
					url: '{{ route('phprogrampromosihadiah.detail') }}',
					type: 'GET',
					data: {
						kd_brgh: kd_brgh,
						type: 'barang'
					},
					success: function(response) {
						if (response.exists && response.data) {
							$('#kd_brgh_entry').val(response.data.kd_brgh);
							$('#na_brgh_entry').val(response.data.na_brgh);
							$('#qty_entry').focus();
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: 'Barang tidak ditemukan'
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal mengambil data barang'
						});
					}
				});
			}
		}

		function browseSupplier() {
			$('#browseSupplierModal').modal('show');
			searchSupplier('');
		}

		function searchSupplier(query) {
			$.ajax({
				url: '{{ route('phprogrampromosihadiah.browse') }}',
				type: 'GET',
				data: {
					q: query,
					type: 'supplier'
				},
				success: function(response) {
					var tbody = $('#supplierTableBody');
					tbody.empty();

					if (response.length > 0) {
						response.forEach(function(item) {
							var row = '<tr>';
							row += '<td>' + item.kodes + '</td>';
							row += '<td>' + item.namas + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectSupplier(\'' +
								item.kodes + '\', \'' + escapeHtml(item.namas) + '\')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="3" class="text-center">No data found</td></tr>');
					}
				},
				error: function() {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Gagal mengambil data supplier'
					});
				}
			});
		}

		function selectSupplier(kodes, namas) {
			$('#kodes').val(kodes);
			$('#namas').val(namas);
			$('#browseSupplierModal').modal('hide');
			$('#kd_prm').focus();
		}

		function browseBarang() {
			var kd_prm = $('#kd_prm').val();
			var txttype = $('#cbtype').val();

			if (!kd_prm || kd_prm === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Simpan data header terlebih dahulu'
				});
				return;
			}

			$.ajax({
				url: '{{ route('phprogrampromosihadiah.browse') }}',
				type: 'GET',
				data: {
					type: 'barang',
					kd_prm: kd_prm,
					txttype: txttype === 'VARIAN' ? 'V' : 'H'
				},
				success: function(response) {
					var tbody = $('#barangTableBody');
					tbody.empty();

					if (response.length > 0) {
						response.forEach(function(item) {
							var row = '<tr>';
							row += '<td>' + item.kd_brg + '</td>';
							row += '<td>' + item.na_brg + '</td>';
							row += '<td>' + (item.ket_uk || '') + '</td>';
							row += '<td>' + (item.ket_kem || '') + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectBarang(\'' +
								item.kd_brg + '\', \'' + escapeHtml(item.na_brg) + '\')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});

						$('#browseBarangModal').modal('show');
					} else {
						Swal.fire({
							icon: 'info',
							title: 'Info',
							text: 'Tidak ada barang terkait'
						});
					}
				},
				error: function() {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Gagal mengambil data barang'
					});
				}
			});
		}

		function selectBarang(kd_brgh, na_brgh) {
			$('#kd_brgh_entry').val(kd_brgh);
			$('#na_brgh_entry').val(na_brgh);
			$('#browseBarangModal').modal('hide');
			$('#qty_entry').focus();
		}

		function addDetailRow() {
			var kd_brgh = $('#kd_brgh_entry').val().trim();
			var na_brgh = $('#na_brgh_entry').val().trim();
			var qty = parseFloat($('#qty_entry').val()) || 0;

			if (!kd_brgh) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Kode barang tidak boleh kosong'
				});
				return;
			}

			if (qty <= 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Qty harus lebih dari 0'
				});
				return;
			}

			var newRow = `
            <tr>
                <td class="text-center">${detailRowIndex + 1}</td>
                <td class="text-right">${qty}</td>
                <td>${escapeHtml(kd_brgh)}</td>
                <td>${escapeHtml(na_brgh)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeDetailRow(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
                <input type="hidden" name="details[${detailRowIndex}][no_id]" value="0">
                <input type="hidden" name="details[${detailRowIndex}][rec]" value="${detailRowIndex + 1}">
                <input type="hidden" name="details[${detailRowIndex}][kd_brgh]" value="${escapeHtml(kd_brgh)}">
                <input type="hidden" name="details[${detailRowIndex}][na_brgh]" value="${escapeHtml(na_brgh)}">
                <input type="hidden" name="details[${detailRowIndex}][qty]" value="${qty}">
            </tr>`;

			$('#detailTableBody').append(newRow);
			detailRowIndex++;

			$('#kd_brgh_entry').val('');
			$('#na_brgh_entry').val('');
			$('#qty_entry').val('');
			$('#kd_brgh_entry').focus();
		}

		function removeDetailRow(btn) {
			$(btn).closest('tr').remove();
			renumberDetailRows();
		}

		function renumberDetailRows() {
			$('#detailTableBody tr').each(function(index) {
				$(this).find('td:first').text(index + 1);
			});
		}

		function loadBarangTerkait() {
			var kd_prm = $('#kd_prm').val();
			var txttype = $('#cbtype').val();

			if (!kd_prm || kd_prm === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Simpan data header terlebih dahulu'
				});
				return;
			}

			$('#barangTerkaitBody').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

			$.ajax({
				url: '{{ route('phprogrampromosihadiah.browse') }}',
				type: 'GET',
				data: {
					type: 'barang',
					kd_prm: kd_prm,
					txttype: txttype === 'VARIAN' ? 'V' : 'H'
				},
				success: function(response) {
					var tbody = $('#barangTerkaitBody');
					tbody.empty();

					if (response.length > 0) {
						response.forEach(function(item) {
							var row = '<tr>';
							row += '<td>' + item.kd_brg + '</td>';
							row += '<td>' + item.na_brg + '</td>';
							row += '<td>' + (item.ket_uk || '-') + '</td>';
							row += '<td>' + (item.ket_kem || '-') + '</td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="4" class="text-center">Tidak ada data barang terkait</td></tr>');
					}
				},
				error: function() {
					$('#barangTerkaitBody').html('<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>');
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Gagal memuat data barang terkait'
					});
				}
			});
		}

		function updateMasksDialog() {
			var sub1 = $('#txtsub1').val().trim();
			var sub2 = $('#txtsub2').val().trim();
			var sup1 = $('#txtsup1').val().trim();
			var sup2 = $('#txtsup2').val().trim();

			if (!sub1 || !sub2 || !sup1 || !sup2) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Sub kategori dan supplier harus diisi'
				});
				return;
			}

			Swal.fire({
				title: 'Konfirmasi',
				text: 'Apakah Anda yakin ingin menambahkan item ke masks?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: 'Ya, Tambahkan',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					updateMasks();
				}
			});
		}

		function updateMasks() {
			Swal.fire({
				title: 'Updating...',
				text: 'Mohon tunggu',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading()
				}
			});

			$.ajax({
				url: '{{ route('phprogrampromosihadiah.update-masks') }}',
				type: 'POST',
				data: {
					txtsub1: $('#txtsub1').val(),
					txtsub2: $('#txtsub2').val(),
					txtsup1: $('#txtsup1').val(),
					txtsup2: $('#txtsup2').val(),
					txtnoitem: $('#txtnoitem').val(),
					txtkode: $('#kd_prm').val(),
					txttype: $('#cbtype').val() === 'VARIAN' ? 'V' : 'H',
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					Swal.fire({
						icon: 'success',
						title: 'Success',
						text: response.message
					});
					loadBarangTerkait();
				},
				error: function(xhr) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.message || 'Gagal update masks'
					});
				}
			});
		}

		function clearMasksDialog() {
			Swal.fire({
				title: 'Konfirmasi',
				text: 'Apakah Anda yakin ingin menghapus semua kode promo dari masks?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Ya, Hapus!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					clearMasks();
				}
			});
		}

		function clearMasks() {
			Swal.fire({
				title: 'Clearing...',
				text: 'Mohon tunggu',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading()
				}
			});

			$.ajax({
				url: '{{ route('phprogrampromosihadiah.clear-masks') }}',
				type: 'POST',
				data: {
					txtkode: $('#kd_prm').val(),
					txttype: $('#cbtype').val() === 'VARIAN' ? 'V' : 'H',
					no_bukti: $('#no_bukti').val(),
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					Swal.fire({
						icon: 'success',
						title: 'Success',
						text: response.message
					});
					loadBarangTerkait();
				},
				error: function(xhr) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.message || 'Gagal clear masks'
					});
				}
			});
		}

		function simpan() {
			if ($('#kodes').val().trim() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Supplier tidak boleh kosong'
				});
				$('#kodes').focus();
				return;
			}

			if ($('#kd_prm').val().trim() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Kode Promosi tidak boleh kosong'
				});
				$('#kd_prm').focus();
				return;
			}

			if ($('#cbtype').val().trim() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Type tidak boleh kosong'
				});
				$('#cbtype').focus();
				return;
			}

			if ($('#jm_mulai').val() === '00:00' || $('#jm_mulai').val() === '00:00:00') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Filter Jam Mulai, tidak boleh kosong!!'
				});
				$('#jm_mulai').focus();
				return;
			}

			if ($('#jm_akhir').val() === '00:00' || $('#jm_akhir').val() === '00:00:00') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Filter Jam Selesai, tidak boleh kosong!!'
				});
				$('#jm_akhir').focus();
				return;
			}

			var tgMulai = new Date($('#tg_mulai').val());
			var tgAkhir = new Date($('#tg_akhir').val());

			if (tgAkhir < tgMulai) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Tanggal Selesai Harus Lebih Tinggi Tanggal Mulai!!'
				});
				return;
			}

			var type = $('#cbtype').val();

			if (type === 'BANK') {
				if ($('#cbbank').val().trim() === '') {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Bank Tidak Boleh Kosong !!'
					});
					return;
				}
				if (parseFloat($('#txttotalbl').val() || 0) <= 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Jumlah beli tidak boleh 0 !!'
					});
					return;
				}
			} else if (type === 'HIJAU') {
				if (parseFloat($('#txtqtybl').val() || 0) <= 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Jumlah beli tidak boleh 0 !!'
					});
					return;
				}
			} else if (type === 'VARIAN') {
				if (parseFloat($('#txtqtybl').val() || 0) <= 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Jumlah beli tidak boleh 0 !!'
					});
					return;
				}
				if (parseFloat($('#txttotalbl').val() || 0) <= 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Total beli tidak boleh 0 !!'
					});
					return;
				}
				if ($('#cbjns').val().trim() === '') {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Isi jenis hubungannya !!'
					});
					return;
				}
				if ($('#cbkondisi').val().trim() === '') {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Isi kondisi pembeliannya !!'
					});
					return;
				}
			} else if (type === 'CASH') {
				if (parseFloat($('#txttotalbl').val() || 0) <= 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Total beli tidak boleh 0 !!'
					});
					return;
				}
			}

			Swal.fire({
				title: 'Menyimpan...',
				text: 'Mohon tunggu',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading()
				}
			});

			$.ajax({
				url: '{{ route('phprogrampromosihadiah.store') }}',
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
							window.location.href = '{{ route('phprogrampromosihadiah') }}';
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
				error: function(xhr) {
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

		function closeForm() {
			window.location.href = '{{ route('phprogrampromosihadiah') }}';
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
