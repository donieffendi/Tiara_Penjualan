@extends('layouts.plain')

@section('content')
<style>
	.card {}

	.content-header {
		padding: 0 !important;
	}

	.form-group.row {
		align-items: center !important;
		margin-bottom: 0.8rem !important;
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

	.btn-sm {
		padding: 0.25rem 0.5rem;
		font-size: 0.875rem;
	}
</style>

<div class="content-wrapper">
	<div class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-header">
							<h3 class="card-title">Daftar Barang Hadiah - {{ $status == 'simpan' ? 'New' : 'Edit' }}</h3>
						</div>
						<div class="card-body">
							<form action="{{ route('phdaftarbaranghadiah.store') }}" method="POST" name="entri" id="entri">
								@csrf
								<input type="hidden" id="status" name="status" value="{{ $status }}">

								<!-- Form Input -->
								<div class="card mb-3">
									<div class="card-header bg-light">
										<h6 class="mb-0">Data Barang Hadiah</h6>
									</div>
									<div class="card-body">
										<div class="row">
											<!-- Kode Barang -->
											<div class="col-md-6">
												<div class="form-group row">
													<div class="col-md-3">
														<label for="kd_brgh" class="form-label">Kode Barang <span class="text-danger">*</span></label>
													</div>
													<div class="col-md-9">
														<input type="text" class="form-control form-control-sm" id="kd_brgh" name="kd_brgh" value="{{ $barang->kd_brgh ?? '' }}"
															placeholder="Kode Barang (7 digit)" maxlength="7" required {{ $status == 'edit' ? 'readonly' : '' }}>
														<small class="form-text text-muted">Kode barang harus tepat 7 karakter</small>
													</div>
												</div>
											</div>

											<!-- Nama Barang -->
											<div class="col-md-6">
												<div class="form-group row">
													<div class="col-md-3">
														<label for="na_brgh" class="form-label">Nama Barang <span class="text-danger">*</span></label>
													</div>
													<div class="col-md-9">
														<input type="text" class="form-control form-control-sm" id="na_brgh" name="na_brgh" value="{{ $barang->na_brgh ?? '' }}"
															placeholder="Nama Barang" required>
													</div>
												</div>
											</div>
										</div>

										<div class="row">
											<!-- Kode Supplier -->
											<div class="col-md-6">
												<div class="form-group row">
													<div class="col-md-3">
														<label for="kodes" class="form-label">Kode Supplier</label>
													</div>
													<div class="col-md-9">
														<div class="input-group input-group-sm">
															<input type="text" class="form-control form-control-sm" id="kodes" name="kodes" value="{{ $barang->kodes ?? '' }}"
																placeholder="Kode Supplier">
															<button type="button" class="btn btn-sm btn-primary" onclick="browseSupplier()">
																<i class="fas fa-search"></i>
															</button>
														</div>
													</div>
												</div>
											</div>

											<!-- Nama Supplier -->
											<div class="col-md-6">
												<div class="form-group row">
													<div class="col-md-3">
														<label for="namas" class="form-label">Nama Supplier</label>
													</div>
													<div class="col-md-9">
														<input type="text" class="form-control form-control-sm" id="namas" name="namas" value="{{ $barang->namas ?? '' }}"
															placeholder="Nama Supplier" readonly>
													</div>
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
<div class="modal fade" id="browseSupplierModal" tabindex="-1" aria-labelledby="browseSupplierModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="browseSupplierModalLabel">Browse Supplier</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
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
						<tbody id="supplierTableBody">
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
	$(document).ready(function() {
		// Set focus based on status (matching Delphi FormShow)
		@if($status == 'simpan')
		$('#kd_brgh').focus();
		@else
		$('#na_brgh').focus();
		@endif

		// Enter key navigation (matching Delphi FormKeyDown)
		$('body').on('keydown', function(e) {
			if (e.key === "Enter") {
				e.preventDefault();
				handleEnterKey(e.target);
				return false;
			}
		});

		// Kode barang validation on input (matching Delphi txtkd_brghPropertiesChange)
		$('#kd_brgh').on('input', function() {
			var value = $(this).val();
			if (value.length > 7) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Kode Barang melebihi batas!',
					timer: 2000,
					showConfirmButton: false
				});
				$(this).val(value.substring(0, 7));
			}
		});

		// Kode barang exit validation (matching Delphi txtkd_brghExit)
		$('#kd_brgh').on('blur', function() {
			var kd_brgh = $(this).val().trim();

			if (kd_brgh.length > 0 && kd_brgh.length < 7) {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Kode Barang harus 7 digit!',
					confirmButtonText: 'OK'
				});
				$(this).val('').focus();
				return;
			}

			if (kd_brgh.length === 7 && '{{ $status }}' === 'simpan') {
				// Check if code already exists
				checkKodeBarang(kd_brgh);
			}
		});

		// Kode supplier exit (matching Delphi txtkodesExit)
		$('#kodes').on('blur', function() {
			var kodes = $(this).val().trim();
			if (kodes) {
				loadSupplierData(kodes);
			}
		});
	});

	function handleEnterKey(element) {
		var $element = $(element);
		var id = $element.attr('id');

		switch (id) {
			case 'kd_brgh':
				$('#na_brgh').focus().select();
				break;
			case 'na_brgh':
				$('#kodes').focus().select();
				break;
			case 'kodes':
				var kodes = $element.val().trim();
				if (kodes) {
					loadSupplierData(kodes);
				} else {
					// Move to next field
					$('#na_brgh').focus().select();
				}
				break;
			default:
				// Navigate to next focusable element
				var form = $element.parents('form:eq(0)');
				var focusable = form.find('input,select,textarea,button').filter(':visible:not([readonly]):not([disabled])');
				var next = focusable.eq(focusable.index(element) + 1);
				if (next.length) {
					next.focus().select();
				}
				break;
		}
	}

	// Check if kode barang already exists (matching Delphi txtkd_brghExit validation)
	function checkKodeBarang(kd_brgh) {
		$.ajax({
			url: "{{ route('phdaftarbaranghadiah.detail') }}",
			type: 'GET',
			data: {
				kd_brgh: kd_brgh
			},
			success: function(response) {
				if (response.exists) {
					Swal.fire({
						icon: 'warning',
						title: 'Warning',
						text: 'Kode ini udah dipakai!',
						confirmButtonText: 'OK'
					});
					$('#kd_brgh').val('').focus();
				}
			}
		});
	}

	// Load supplier data (matching Delphi txtkodesExit)
	function loadSupplierData(kodes) {

		$.ajax({
			url: "{{ route('phdaftarbaranghadiah.supplier') }}",
			type: 'GET',
			data: {
				kodes: kodes
			},
			success: function(response) {

				if (response.success && response.data) {
					$('#kodes').val(response.data.kodes);
					$('#namas').val(response.data.namas);
					$('#na_brgh').focus();
				} else {
					var tbody = $('#supplierTableBody');
					tbody.empty();
					response.forEach(function(item) {
						var row = '<tr>';
						row += '<td>' + item.kodes + '</td>';
						row += '<td>' + (item.namas || '') + '</td>';
						row += '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectSupplier(\'' +
							item.kodes + '\', \'' + (item.namas || '') + '\')">Select</button></td>';
						row += '</tr>';
						tbody.append(row);
					});
				}
			},
			error: function(e) {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Gagal mengambil data supplier: ' + e.responseText
				});
			}
		});
	}

	// Browse supplier functionality
	function browseSupplier() {
		loadSupplierData();
		$('#browseSupplierModal').modal('show');
		// You can implement supplier search here if needed
		// For now, just show the modal
	}

	function selectSupplier(kodes, namas) {
		$('#kodes').val(kodes);
		$('#namas').val(namas);
		$('#browseSupplierModal').modal('hide');
		$('#na_brgh').focus();
	}

	// Save function (matching Delphi MSaveClick)
	function simpan() {
		// Validation (matching Delphi ceck procedure)
		var kd_brgh = $('#kd_brgh').val().trim();
		var na_brgh = $('#na_brgh').val().trim();

		if (!kd_brgh) {
			Swal.fire({
				icon: 'warning',
				title: 'Peringatan',
				text: 'Data tidak boleh kosong!!',
				confirmButtonText: 'OK'
			});
			$('#kd_brgh').focus();
			return;
		}

		if (kd_brgh.length !== 7) {
			Swal.fire({
				icon: 'warning',
				title: 'Peringatan',
				text: 'Kode Barang harus 7 digit!',
				confirmButtonText: 'OK'
			});
			$('#kd_brgh').focus();
			return;
		}

		if (!na_brgh) {
			Swal.fire({
				icon: 'warning',
				title: 'Peringatan',
				text: 'Data tidak boleh kosong!!',
				confirmButtonText: 'OK'
			});
			$('#na_brgh').focus();
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
			url: "{{ route('phdaftarbaranghadiah.store') }}",
			type: 'POST',
			data: $('#entri').serialize(),
			success: function(response) {
				Swal.close();
				if (response.success) {
					// Matching Delphi success message
					Swal.fire({
						title: 'Informasi',
						text: 'Save Data Success',
						icon: 'success',
						confirmButtonText: 'OK'
					}).then(() => {
						window.location.href = "{{ route('phdaftarbaranghadiah') }}";
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

	// Close form (matching Delphi MExitClick)
	function closeForm() {
		window.location.href = "{{ route('phdaftarbaranghadiah') }}";
	}

	// Utility function
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