@extends('layouts.plain')

@section('content')
	<style>
		.card {}

		.form-control:focus {
			background-color: #E0FFFF !important;
		}

		.nav-item .nav-link.active {
			background-color: red !important;
			color: white !important;
		}

		.content-header {
			padding: 0 !important;
		}

		.form-group.row {
			align-items: center !important;
			margin-bottom: 1rem !important;
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

		.form-group.row .form-check-input {
			margin-top: 0;
		}

		.col-md-2,
		.col-md-4,
		.col-md-6,
		.col-md-8,
		.col-md-10 {
			padding-top: 0;
			padding-bottom: 0;
		}

		.d-flex.align-items-center {
			height: 38px;
		}

		.col-md-6.pr-4 {
			padding-right: 2rem !important;
		}

		.col-md-6.pl-4 {
			padding-left: 2rem !important;
		}
	</style>

	<div class="content-wrapper">
		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form action="{{ route('lmemberitandabintang.store') }}" method="POST" name="entri" id="entri">
									@csrf
									<input type="hidden" id="status" name="status" value="{{ $status }}">

									<div class="tab-content mt-3">
										<div class="row">
											<!-- Left Column -->
											<div class="col-md-6 pr-4">
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="kd_brg" class="form-label">Kode Barang</label>
													</div>
													<div class="col-md-9">
														<input type="text" class="form-control" id="kd_brg" name="kd_brg" value="{{ $barang->kd_brg ?? '' }}"
															{{ $status == 'edit' ? 'readonly' : '' }} placeholder="Masukkan Kode Barang" required>
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="na_brg" class="form-label">Nama Barang</label>
													</div>
													<div class="col-md-9">
														<input type="text" class="form-control" id="na_brg" name="na_brg" value="{{ $barang->na_brg ?? '' }}"
															placeholder="Masukkan Nama Barang" required>
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="txttype" class="form-label">Tanda Hapus</label>
													</div>
													<div class="col-md-9">
														<input type="text" class="form-control" id="txttype" name="txttype" value="{{ $barang->td_od ?? '' }}" placeholder="Masukkan Tipe">
													</div>
												</div>
											</div>
											<!-- Right Column -->
											<div class="col-md-6 pl-4">
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="txtalasan" class="form-label">Alasan</label>
													</div>
													<div class="col-md-9">
														<input type="text" class="form-control" id="txtalasan" name="txtalasan" value="{{ $barang->cat_od ?? '' }}"
															placeholder="Masukkan Alasan">
													</div>
												</div>
												<div class="form-group row align-items-center">
													<div class="col-md-3">
														<label for="tgl_od" class="form-label">Tanggal</label>
													</div>
													<div class="col-md-9">
														<input type="date" class="form-control" id="tgl_od" name="tgl_od"
															value="{{ $barang && $barang->tgl_od ? date('Y-m-d', strtotime($barang->tgl_od)) : date('Y-m-d') }}" required>
													</div>
												</div>
											</div>
										</div>

										<!-- Action Buttons -->
										<div class="col-md-12 form-group row mt-3">
											<div class="col-md-6">
												<button type="button" id='SAVEX' onclick='simpan()' class="btn btn-success">Save</button>
												<button type="button" id='CLOSEX' onclick="closeForm()" class="btn btn-outline-secondary">Close</button>
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

	<!-- Browse Barang Modal -->
	<div class="modal fade" id="browseBarangModal" tabindex="-1" aria-labelledby="browseBarangModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="browseBarangModalLabel">Browse Barang</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<input type="text" class="form-control" id="searchBarang" placeholder="Cari barang...">
					</div>
					<div class="table-responsive">
						<table class="table-bordered table" id="barangTable">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Nama Barang</th>
									<th>Tipe</th>
									<th>Alasan</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="barangTableBody">
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('footer-scripts')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		$(document).ready(function() {
			// Enter key navigation
			$('body').on('keydown', 'input, select', function(e) {
				if (e.key === "Enter") {
					var self = $(this),
						form = self.parents('form:eq(0)'),
						focusable, next;
					focusable = form.find('input,select,textarea').filter(':visible:not([readonly])');
					next = focusable.eq(focusable.index(this) + 1);
					if (next.length) {
						next.focus().select();
					}
					return false;
				}
			});

			// Search barang with delay
			$('#searchBarang').on('keyup', function() {
				var query = $(this).val();
				if (query.length > 2) {
					searchBarang(query);
				} else if (query.length === 0) {
					searchBarang('');
				}
			});

			// Kode Barang validation on key up (equivalent to txtkdbrgKeyUp in Delphi)
			$('#kd_brg').on('keyup', function(e) {
				if (e.key === "Enter") {
					var kd_brg = $(this).val().trim();
					if (kd_brg && $('#status').val() === 'simpan') {
						checkBarangExists(kd_brg);
					}
				}
			});

			// Set focus based on status
			@if ($status == 'simpan')
				$('#kd_brg').focus();
			@else
				$('#txttype').focus();
			@endif
		});

		// Check if barang code already exists (equivalent to txtkdbrgKeyUp validation in Delphi)
		function checkBarangExists(kd_brg) {
			$.ajax({
				url: '{{ route('lmemberitandabintang.barang-detail') }}',
				type: 'GET',
				data: {
					kd_brg: kd_brg
				},
				success: function(response) {
					if (response.exists) {
						Swal.fire({
							title: 'Warning!',
							text: 'Kode Barang sudah ada!',
							icon: 'warning',
							confirmButtonText: 'OK'
						});
						$('#kd_brg').val('+');
						$('#kd_brg').focus();
					}
				}
			});
		}

		// Search barang function
		function searchBarang(query) {
			$.ajax({
				url: '{{ route('lmemberitandabintang.browse') }}',
				type: 'GET',
				data: {
					q: query
				},
				success: function(response) {
					var tbody = $('#barangTableBody');
					tbody.empty();

					if (response.length > 0) {
						response.forEach(function(item) {
							var row = '<tr>';
							row += '<td>' + item.kd_brg + '</td>';
							row += '<td>' + item.na_brg + '</td>';
							row += '<td>' + (item.td_od || '') + '</td>';
							row += '<td>' + (item.cat_od || '') + '</td>';
							row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectBarang(\'' +
								item.kd_brg + '\', \'' + item.na_brg + '\', \'' + (item.td_od || '') + '\', \'' + (item.cat_od || '') +
								'\')">Select</button></td>';
							row += '</tr>';
							tbody.append(row);
						});
					} else {
						tbody.append('<tr><td colspan="5" class="text-center">No data found</td></tr>');
					}
				}
			});
		}

		// Select barang from browse modal
		function selectBarang(kd_brg, na_brg, td_od, cat_od) {
			$('#kd_brg').val(kd_brg);
			$('#na_brg').val(na_brg);
			$('#txttype').val(td_od);
			$('#txtalasan').val(cat_od);
			$('#browseBarangModal').modal('hide');
			$('#na_brg').focus();
		}

		// Show browse modal
		function showBrowseModal() {
			$('#browseBarangModal').modal('show');
			searchBarang('');
		}

		// Save function (equivalent to MSaveClick in Delphi)
		function simpan() {
			// Validation equivalent to checkx procedure in Delphi
			if ($('#kd_brg').val().trim() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Kode Barang Kosong.'
				});
				$('#kd_brg').focus();
				return;
			}

			if ($('#na_brg').val().trim() === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Nama Barang Kosong.'
				});
				$('#na_brg').focus();
				return;
			}

			// Show loading
			Swal.fire({
				title: 'Menyimpan...',
				text: 'Mohon tunggu',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading()
				}
			});

			// Submit form via AJAX
			$.ajax({
				url: '{{ route('lmemberitandabintang.store') }}',
				type: 'POST',
				data: $('#entri').serialize(),
				success: function(response) {
					Swal.close();
					if (response.success) {
						Swal.fire({
							title: 'Success!',
							text: response.message,
							icon: 'success',
							confirmButtonText: 'OK'
						}).then(() => {
							// If save success, check if it's new entry or edit
							@if ($status == 'simpan')
								// For new entry, clear form and set focus to kd_brg
								$('#kd_brg').val('');
								$('#na_brg').val('');
								$('#txttype').val('');
								$('#txtalasan').val('');
								$('#tgl_od').val('{{ date('Y-m-d') }}');
								$('#kd_brg').focus();
							@else
								// For edit, close form
								window.location.href = '{{ route('lmemberitandabintang') }}';
							@endif
						});
					} else {
						Swal.fire({
							title: 'Error!',
							text: response.message,
							icon: 'error',
							confirmButtonText: 'OK'
						});

						// If duplicate kd_brg, clear and focus
						if (response.message.includes('sudah ada')) {
							$('#kd_brg').val('');
							$('#kd_brg').focus();
						}
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

		// Close form function (equivalent to MExitClick in Delphi)
		function closeForm() {
			window.location.href = '{{ route('lmemberitandabintang') }}';
		}
	</script>
@endsection
