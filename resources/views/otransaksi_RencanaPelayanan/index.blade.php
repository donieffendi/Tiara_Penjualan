@extends('layouts.plain')
@section('styles')
	<link rel="stylesheet" href="{{ url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
	<link rel="stylesheet" href="{{ url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endsection

<style>
	th {
		font-size: 13px;
	}

	td {
		font-size: 13px;
	}

	/* menghilangkan padding */
	.content-header {
		padding: 0 !important;
	}

	.badge-warning {
		background-color: #0077ff !important;
		/* Warna default badge-warning (kuning) */
		color: white !important;
		/* Warna teks putih */
	}

	.badge-success {
		background-color: #28a745 !important;
		color: white !important;
	}

	.badge-danger {
		background-color: #dc3545 !important;
		color: white !important;
	}

	/* Customer Status Card Styling */
	.customer-status-card {
		background: linear-gradient(145deg, #f8f9fa, #e9ecef);
		border-left: 4px solid #007bff;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
	}

	.status-label {
		font-size: 16px;
		font-weight: 600;
		color: #495057;
		margin-bottom: 5px;
	}

	.customer-search {
		min-width: 300px;
	}

	.btn-check-status {
		background: linear-gradient(45deg, #007bff, #0056b3);
		border: none;
		box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
	}

	.btn-check-status:hover {
		background: linear-gradient(45deg, #0056b3, #004085);
		transform: translateY(-1px);
	}

	.btn-reset {
		background: linear-gradient(45deg, #6c757d, #545b62);
		border: none;
	}

	.btn-reset:hover {
		background: linear-gradient(45deg, #545b62, #3a3f44);
		transform: translateY(-1px);
	}
</style>

@section('content')
	<div class="content-wrapper">

		<!-- Status -->
		@if (session('status'))
			<div class="alert alert-success">
				{{ session('status') }}
			</div>

			<!-- tambahan notifikasinya untuk delete di index -->
			<script>
				Swal.fire({
					title: 'Success!',
					text: '{{ session('status') }}',
					icon: 'success',
					confirmButtonText: 'OK'
				})
			</script>
			<!-- tutupannya -->
		@endif

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<!-- Customer Status Check Card -->
					<div class="col-12 mb-4">
						<div class="card customer-status-card">
							<div class="card-header">
								<h4 class="card-title mb-0">
									<i class="fas fa-user-check mr-2"></i>Status Customer
								</h4>
							</div>
							<div class="card-body">
								<div class="row align-items-end">
									<div class="col-md-4">
										<label for="customer-search" class="form-label">Pilih Customer:</label>
										<select id="customer-search" class="form-control customer-search" style="width: 100%;">
											<option value="">-- Pilih Customer --</option>
										</select>
									</div>
									<div class="col-md-2">
										<button type="button" id="btn-check-status" class="btn btn-primary btn-check-status">
											<i class="fas fa-search mr-1"></i>Cek Status
										</button>
									</div>
									<div class="col-md-2">
										<button type="button" id="btn-reset" class="btn btn-secondary btn-reset">
											<i class="fas fa-refresh mr-1"></i>Reset
										</button>
									</div>
								</div>

								<div class="row mt-4">
									<div class="col-md-4">
										<div class="status-label" id="label-plafon">Plafon :</div>
									</div>
									<div class="col-md-4">
										<div class="status-label" id="label-terhutang">Terhutang :</div>
									</div>
									<div class="col-md-4">
										<div class="status-label" id="label-sisa">Sisa :</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Data Table Card -->
					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Data Rencana Pelayanan</h4>
							</div>
							<div class="card-body">

								<!-- filter kolom di index -->
								<!-- Button to open modal -->
								<button type="button" class="btn btn-info mb-3" data-bs-toggle="modal" data-bs-target="#columnModal">
									<i class="fas fa-filter mr-1"></i>Filter Columns
								</button>

								<!-- Modal -->
								<div class="modal fade" id="columnModal" tabindex="-1" aria-labelledby="columnModalLabel" aria-hidden="true">
									<div class="modal-dialog">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="modal-title" id="columnModalLabel">Toggle Columns</h5>
												<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
											</div>
											<div class="modal-body">
												<!-- Column visibility checkboxes -->
												<form id="columnToggleForm">
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="0" id="columnNo" checked>
														<label class="form-check-label" for="columnNo">No</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="1" id="columnAction" checked>
														<label class="form-check-label" for="columnAction">Action</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="2" id="columnKodes" checked>
														<label class="form-check-label" for="columnKodes">Kode Customer</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="3" id="columnNamas" checked>
														<label class="form-check-label" for="columnNamas">Nama Customer</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="4" id="columnAlamat" checked>
														<label class="form-check-label" for="columnAlamat">Alamat</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="5" id="columnKota" checked>
														<label class="form-check-label" for="columnKota">Kota</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="6" id="columnKontak" checked>
														<label class="form-check-label" for="columnKontak">Kontak</label>
													</div>
												</form>
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
												<button type="button" class="btn btn-primary" id="applyColumnToggle">Apply</button>
											</div>
										</div>
									</div>
								</div>

								<!-- batas filter -->

								<table class="table-striped table-border table-hover nowrap datatable table table-fixed" id="datatable">
									<thead class="table-dark">
										<tr>
											<th width="50px" style="text-align:center">No</th>
											<th width="60px" style="text-align:center">-</th>
											<th width="120px" style="text-align:center">Kode Customer</th>
											<th width="200px" style="text-align:center">Nama Customer</th>
											<th width="250px" style="text-align:center">Alamat</th>
											<th width="120px" style="text-align:center">Kota</th>
											<th width="120px" style="text-align:center">Kontak</th>
										</tr>
									</thead>

									<tbody>

									</tbody>
								</table>
							</div>
						</div>
						<!-- /.card -->
					</div>
				</div>
				<!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>
		<!-- /.content -->
	</div>
	<!-- /.content-wrapper -->
@endsection

@section('javascripts')
	<script src="{{ url('AdminLTE/plugins/datatables/jquery.dataTables.js') }}"></script>
	<script src="{{ url('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>
	<script src="{{ url('http://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

	<!-- filter kolom di index -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
	<!-- batas filter  -->

	<script>
		let dataTable;

		$(document).ready(function() {
			// Initialize Select2 for customer search
			$('#customer-search').select2({
				placeholder: 'Ketik untuk mencari customer...',
				allowClear: true,
				ajax: {
					url: '{{ route('TRencanaPelayanan/browse') }}',
					dataType: 'json',
					delay: 250,
					data: function(params) {
						return {
							q: params.term
						};
					},
					processResults: function(data) {
						return {
							results: data.map(function(item) {
								return {
									id: item.kodec,
									text: item.display_name
								};
							})
						};
					},
					cache: true
				}
			});

			// Initialize DataTable for customer list display
			dataTable = $('.datatable').DataTable({
				processing: true,
				serverSide: false,
				'scrollY': '400px',
				"order": [
					[2, "asc"]
				],
				ajax: {
					url: '{{ route('TRencanaPelayanan/browse') }}',
					dataSrc: ''
				},
				columns: [{
						data: null,
						render: function(data, type, row, meta) {
							return meta.row + meta.settings._iDisplayStart + 1;
						},
						orderable: false,
						searchable: false
					},
					{
						data: null,
						render: function(data, type, row) {
							return '<button class="btn btn-sm btn-info btn-select-customer" data-kodec="' + row.kodec +
								'" data-namac="' + row.namac + '"><i class="fas fa-hand-pointer"></i> Pilih</button>';
						},
						orderable: false,
						searchable: false
					},
					{
						data: 'kodec',
						name: 'kodec'
					},
					{
						data: 'namac',
						name: 'namac',
						render: function(data, type, row) {
							return '<span class="badge badge-pill badge-warning">' + data + '</span>';
						}
					},
					{
						data: 'alamat',
						name: 'alamat',
						defaultContent: '-'
					},
					{
						data: 'kota',
						name: 'kota',
						defaultContent: '-'
					},
					{
						data: 'kontak',
						name: 'kontak',
						defaultContent: '-'
					}
				],

				columnDefs: [{
					"className": "dt-center",
					"targets": [0, 1]
				}],

				dom: "<'row'<'col-md-6'><'col-md-6'>>" +
					"<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
					"<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",

				stateSave: false,
			});

			// Reset status labels on page load (implementing Delphi FormShow logic)
			resetStatusLabels();

			// Handle customer selection from table
			$(document).on('click', '.btn-select-customer', function() {
				var kodec = $(this).data('kodec');
				var namac = $(this).data('namac');

				// Set selected customer in select2
				var newOption = new Option(kodec + ' - ' + namac, kodec, true, true);
				$('#customer-search').append(newOption).trigger('change');

				// Auto check status
				checkCustomerStatus(kodec);
			});

			// Handle check status button (implementing Delphi Button1Click logic)
			$('#btn-check-status').on('click', function() {
				var selectedCustomer = $('#customer-search').val();

				if (!selectedCustomer) {
					Swal.fire({
						title: 'Peringatan!',
						text: 'Silakan pilih customer terlebih dahulu.',
						icon: 'warning',
						confirmButtonText: 'OK'
					});
					return;
				}

				checkCustomerStatus(selectedCustomer);
			});

			// Handle reset button (implementing Delphi FormShow logic)
			$('#btn-reset').on('click', function() {
				$('#customer-search').val(null).trigger('change');
				resetStatusLabels();
			});

			// filter kolom di index
			// Handle column visibility toggle
			$('#applyColumnToggle').on('click', function() {
				$('#columnToggleForm .column-checkbox').each(function() {
					var column = dataTable.column($(this).val());
					column.visible($(this).is(':checked'));
				});
				$('#columnModal').modal('hide'); // Close the modal
			});

			$('#columnToggleForm .column-checkbox').each(function() {
				var column = dataTable.column($(this).val());
				column.visible($(this).is(':checked'));
			});

			// Add custom button to DataTable
			$("div.test_btn").html(
				'<button class="btn btn-success" onclick="refreshTable()" title="Refresh Data"><i class="fas fa-sync"></i> Refresh</button>'
			);
		});

		// Function to check customer status (implementing Delphi Button1Click logic)
		function checkCustomerStatus(kodec) {
			if (!kodec) {
				resetStatusLabels();
				return;
			}

			$.ajax({
				url: '{{ url('TRencanaPelayanan/customer-status') }}',
				type: 'GET',
				data: {
					kodec: kodec
				},
				beforeSend: function() {
					$('#btn-check-status').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
				},
				success: function(response) {
					if (response.success) {
						$('#label-plafon').text(response.plafon);
						$('#label-terhutang').text(response.terhutang);
						$('#label-sisa').text(response.sisa);

						// Add visual feedback based on saldo
						if (response.raw_data && response.raw_data.saldo < 0) {
							$('#label-sisa').addClass('text-danger font-weight-bold');
						} else {
							$('#label-sisa').removeClass('text-danger font-weight-bold').addClass('text-success');
						}
					} else {
						resetStatusLabels();
						if (response.message) {
							Swal.fire({
								title: 'Info',
								text: response.message,
								icon: 'info',
								confirmButtonText: 'OK'
							});
						}
					}
				},
				error: function(xhr, status, error) {
					resetStatusLabels();
					Swal.fire({
						title: 'Error!',
						text: 'Terjadi kesalahan saat mengambil data status customer.',
						icon: 'error',
						confirmButtonText: 'OK'
					});
				},
				complete: function() {
					$('#btn-check-status').prop('disabled', false).html('<i class="fas fa-search mr-1"></i>Cek Status');
				}
			});
		}

		// Function to reset status labels (implementing Delphi FormShow logic)
		function resetStatusLabels() {
			$('#label-plafon').text('Plafon :').removeClass('text-danger text-success font-weight-bold');
			$('#label-terhutang').text('Terhutang :').removeClass('text-danger text-success font-weight-bold');
			$('#label-sisa').text('Sisa :').removeClass('text-danger text-success font-weight-bold');
		}

		// Function to refresh table
		function refreshTable() {
			dataTable.ajax.reload(null, false);
			Swal.fire({
				title: 'Refreshed!',
				text: 'Data telah di-refresh.',
				icon: 'success',
				timer: 1500,
				showConfirmButton: false
			});
		}

		function deleteRow(link) {
			console.log('Masuk');
			Swal.fire({
				title: 'Are you sure?',
				text: "Are you sure?",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, delete it!',
				cancelButtonText: 'Cancel'
			}).then((result) => {
				if (result.isConfirmed) {
					window.location = link;
				}
			});
		}
	</script>
@endsection
