@extends('layouts.plain')
@section('styles')
	<!-- <link rel="stylesheet" href="{{ url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}"> -->
	<link rel="stylesheet" href="{{ asset('foxie_js_css/jquery.dataTables.min.css') }}" />
@endsection

<style>
	.card {
		padding: 5px 10px !important;
	}

	.table the $("div.test_btn").html('<a class="btn btn-lg btn-md btn-success" href="{{ url('tformbayar/edit?flagz=' . $flagz . '&idx=0&tipx=new') }}"> <i class="fas fa-plus fa-s // Insert the detail row below the clicked row var detailRow = `<tr class="detail-row">
	<td colspan="13">$ {
			detailHtml
		}

		</td> </tr>`; -3" ></i></a>');


		d {
			background-color: #8a2be2;
			color: #ffff;
		}

		.datatable tbody td {
			padding: 5px !important;
		}

		.datatable {
			border-right: solid 2px #000;
			border-left: solid 2px #000;
		}

		.btn-secondary {
			background-color: #42047e !important;
		}

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
</style>

@section('content')
	<!-- Sweetalert delete -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<!--  -->

	<div class="content-wrapper">

		<!-- Status -->
		@if (session('status'))
			<div class="alert alert-success">
				{{ session('status') }}
			</div>

			<!-- tambahan notifikasinya untuk delete di index -->
			<script>
				Swal.fire({
					title: 'Deleted!',
					text: 'Data has been deleted. {{ session('status') }}',
					icon: 'success',
					confirmButtonText: 'OK'
				})
			</script>
			<!-- tutupannya -->
		@endif

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">

								<!-- filter kolom di index -->

								<!-- Button to open modal -->
								<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#columnModal">
									Filter Columns
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
														<input class="form-check-input column-checkbox" type="checkbox" value="0" id="columnDetail" checked>
														<label class="form-check-label" for="columnDetail">Detail</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="1" id="columnNo" checked>
														<label class="form-check-label" for="columnNo">No</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="2" id="columnAction" checked>
														<label class="form-check-label" for="columnAction">Action</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="3" id="columnCheckbox" checked>
														<label class="form-check-label" for="columnCheckbox">Checkbox</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="4" id="columnBukti" checked>
														<label class="form-check-label" for="columnBukti">No Bukti</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="5" id="columnPajak" checked>
														<label class="form-check-label" for="columnPajak">No Pajak</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="6" id="columnTgl" checked>
														<label class="form-check-label" for="columnTgl">Tgl</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="7" id="columnSup" checked>
														<label class="form-check-label" for="columnSup">Sup</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="8" id="columnNotes" checked>
														<label class="form-check-label" for="columnNotes">Notes</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="9" id="columnTotal" checked>
														<label class="form-check-label" for="columnTotal">Total User</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="10" id="columnUser" checked>
														<label class="form-check-label" for="columnUser">User</label>
													</div>
													<div class="form-check">
														<input class="form-check-input column-checkbox" type="checkbox" value="11" id="columnPrint" checked>
														<label class="form-check-label" for="columnPrint">Print</label>
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

								<!-- Print Form Section -->
								<div class="row mb-3">
									<div class="col-md-12">
										<div class="card">
											<div class="card-header">
												<h5>Print Instruksi Pembayaran Food Center</h5>
											</div>
											<div class="card-body">
												<form id="printForm" method="POST">
													@csrf
													<div class="row">
														<div class="col-md-3">
															<label for="txtbukti1">No Bukti Dari:</label>
															<input type="text" class="form-control" name="txtbukti1" id="txtbukti1" placeholder="FC2501-0001">
														</div>
														<div class="col-md-1 d-flex align-items-center justify-content-center">
															<span style="margin-top: 25px; font-weight: bold;">s/D</span>
														</div>
														<div class="col-md-3">
															<label for="txtbukti2">No Bukti Sampai:</label>
															<input type="text" class="form-control" name="txtbukti2" id="txtbukti2" placeholder="FC2501-0010">
														</div>
														<div class="col-md-2 d-flex align-items-end">
															<button type="button" class="btn btn-primary" onclick="printRange()">
																Print
															</button>
														</div>
														<div class="col-md-3 d-flex align-items-end">
															<button type="button" class="btn btn-success mr-2" onclick="printReport()">
																<i class="fas fa-file-pdf"></i> Laporan
															</button>
															<button type="button" class="btn btn-info" onclick="printAllFC()">
																<i class="fas fa-print"></i> Print All
															</button>
														</div>
													</div>
												</form>
											</div>
										</div>
									</div>
								</div>
								<!-- End Print Form Section -->

								<!-- Posting Form Section -->
								<div class="row mb-3">
									<div class="col-md-12">
										<div class="card">
											<div class="card-header">
												<h5>Posting / Unposting Food Center</h5>
											</div>
											<div class="card-body">
												<form id="postingForm" method="POST">
													@csrf
													<div class="row">
														<div class="col-md-4">
															<label for="tgl_posting">Tanggal Posting:</label>
															<input type="date" class="form-control" name="tgl_posting" id="tgl_posting" value="{{ date('Y-m-d') }}" required>
														</div>
														<div class="col-md-4 d-flex align-items-end">
															<button type="button" class="btn btn-warning mr-2" onclick="postingDataFC()">
																<i class="fas fa-check"></i> Posting FC
															</button>
															<button type="button" class="btn btn-danger" onclick="unpostingDataFC()">
																<i class="fas fa-times"></i> Unposting FC
															</button>
														</div>
														<div class="col-md-4">
															<small class="text-muted">Pilih data Food Center pada tabel dengan checkbox untuk posting/unposting</small>
														</div>
													</div>
												</form>
											</div>
										</div>
									</div>
								</div>
								<!-- End Posting Form Section -->

								<input name="flagz" class="form-control flagz" id="flagz" value="{{ $flagz }}" hidden>

								<table class="table-striped table-border table-hover nowrap datatable table table-fixed" id="datatable">
									<thead class="table-dark">
										<tr>
											<th scope="col" style="text-align: center"></th>
											<th scope="col" style="text-align: center">#</th>
											<th scope="col" style="text-align: center">-</th>
											<th scope="col" style="text-align: center">
												<input type="checkbox" id="checkAll" class="form-control" style="width: 20px; margin: 0 auto;">
											</th>
											<th scope="col" style="text-align: center">No Bukti</th>
											<th scope="col" style="text-align: center">No Pajak</th>
											<th scope="col" style="text-align: center">Tgl</th>
											<th scope="col" style="text-align: center">Sup</th>
											<th scope="col" style="text-align: center">Notes</th>
											<th scope="col" style="text-align: center">Total User</th>
											<th scope="col" style="text-align: center">Prnt</th>
										</tr>
									</thead>

									<tbody>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<!-- filter kolom di index -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
	<!-- batas filter  -->

	<script>
		// filter kolom di index
		window.addEventListener('message', (event) => {
			if (event.origin !== window.location.origin) {
				console.warn('Origin mismatch!');
				return;
			}

			const currentData = event.data;
			console.log(currentData); // Use currentData as needed
		});
		// batas filter

		$(document).ready(function() {



			var dataTable = $('.datatable').DataTable({
				processing: true,
				serverSide: true,
				autoWidth: false,
				// 'scrollX': true,
				'scrollY': '400px',
				"order": [
					[0, "asc"]
				],
				ajax: {
					url: "{{ route('get-tbayarfc') }}",
					data: {
						flagz: $('#flagz').val(),

					}
				},

				columns: [
					//add tombol +
					{
						data: null, // Column for the button
						orderable: false,
						searchable: false,
						render: function(data, type, row, meta) {

							// tanpa ada POST (posting) di atas
							return `<button class="btn btn-success btn-sm toggle-button" data-no_bukti="${row.no_bukti}" onclick="toggleButton(this)">+</button>`;
						}
					},
					// tutupannya

					{
						data: 'DT_RowIndex',
						orderable: false,
						searchable: false
					},
					{
						data: 'action',
						name: 'action'
					},
					{
						data: 'cek',
						name: 'cek',
						orderable: false,
						searchable: false
					},
					{
						data: 'no_bukti',
						name: 'no_bukti'
					},
					{
						data: 'penagih',
						name: 'penagih'
					},
					{
						data: 'tgl',
						name: 'tgl'
					},
					{
						data: 'namas',
						name: 'namas',
						render: function(data, type, row, meta) {
							return ' <h5><span class="badge badge-pill badge-warning">' + data + '</span></h5>';
						}
					},
					{
						data: 'notes',
						name: 'notes'
					},
					{
						data: 'total',
						name: 'total',
						render: $.fn.dataTable.render.number(',', '.', 0, '')
					},
					{
						data: 'usrnm',
						name: 'usrnm'
					},
					{
						data: 'PRNT',
						name: 'PRNT',
						render: function(data, type, row, meta) {
							if (row['PRNT'] == "0" || row['PRNT'] == null) {
								return '<span class="badge badge-secondary">No</span>';
							} else {
								return '<span class="badge badge-success">Yes</span>';
							}
						}
					},
				],
				columnDefs: [{
						"className": "dt-center",
						"targets": [0, 1, 2, 3, 4, 5, 6, 7, 8, 10, 11]
					},
					{
						"className": "dt-right",
						"targets": [9]
					},
					{
						targets: 6,
						render: $.fn.dataTable.render.moment('DD-MM-YYYY')
					}
				],
				lengthMenu: [
					[8, 10, 20, 50, 100, -1],
					[8, 10, 20, 50, 100, "All"]
				],
				dom: "<'row'<'col-md-6'><'col-md-6'>>" +
					"<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
					"<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",

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

			// batas filter

			$("div.test_btn").html(
				'<a class="btn btn-lg btn-md btn-success" href="{{ url('tbayarfc/edit?flagz=' . $flagz . '&idx=0&tipx=new') }}"> <i class="fas fa-plus fa-sm md-3" ></i></a'
			);

			// Check all functionality
			$('#checkAll').on('change', function() {
				var isChecked = $(this).is(':checked');
				$('.cek').prop('checked', isChecked);
			});

			// Individual checkbox change - update check all if needed
			$(document).on('change', '.cek', function() {
				var totalCheckboxes = $('.cek').length;
				var checkedCheckboxes = $('.cek:checked').length;

				if (checkedCheckboxes === totalCheckboxes) {
					$('#checkAll').prop('checked', true);
				} else {
					$('#checkAll').prop('checked', false);
				}
			});

			// function buat ganti tombol + onclick
			window.toggleButton = function(button) {
				const no_bukti = $(button).data('no_bukti'); // Get the no_bukti from data attribute

				if (button.innerText === '+') {
					button.innerText = '-';
					button.classList.remove('btn-success');
					button.classList.add('btn-danger');

					// Fetch and show detail data using no_bukti
					$.ajax({
						url: '{{ route('get-detail-tbayarfc') }}', // Define the route to fetch detail data
						method: 'GET',
						data: {
							no_bukti: no_bukti
						}, // Pass no_bukti in the request
						success: function(response) {
							console.log(response);

							let totalAmount = 0;
							let detailHtml = `
                            <div class="p-3">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Keterangan</th>
                                            <th>Total</th>
                                            <th>Total0</th>
                                            <th>Margin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

							response.forEach((item, index) => {
								totalAmount += parseFloat(item.TOTAL || 0);

								detailHtml += `
                                <tr>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: center">${index + 1}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: left">${item.KET || ''}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: right">${parseFloat(item.TOTAL || 0).toLocaleString('id-ID')}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: right">${parseFloat(item.total0 || 0).toLocaleString('id-ID')}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: right">${parseFloat(item.margin || 0).toLocaleString('id-ID')}</div></td>
                                </tr>
                            `;
							});

							detailHtml += `
                                    <tr>
                                        <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                                        <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: right"><strong>${totalAmount.toLocaleString('id-ID')}</strong></div></td>
                                        <td colspan="2"></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        `;

							// Insert the detail row below the clicked row
							var detailRow = `<tr class="detail-row">
                                <td colspan="11">${detailHtml}</td>
                              </tr>`;
							$(button).closest('tr').after(detailRow);
						}
					});
				} else {
					button.innerText = '+';
					button.classList.remove('btn-danger');
					button.classList.add('btn-success');

					// Remove the detail row if it exists
					$(button).closest('tr').next('.detail-row').remove();
				}
			};

			// tutupannya
		});


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

		// Print Range Function
		function printRange() {
			const txtbukti1 = document.getElementById('txtbukti1').value.trim();
			const txtbukti2 = document.getElementById('txtbukti2').value.trim();

			if (!txtbukti1 || !txtbukti2) {
				Swal.fire({
					title: 'Error!',
					text: 'No Bukti Dari dan Sampai harus diisi!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			// Validation: first character must match (FC prefix)
			if (!txtbukti1.startsWith('FC') || !txtbukti2.startsWith('FC')) {
				Swal.fire({
					title: 'Error!',
					text: 'No Bukti harus dimulai dengan FC!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			// Show loading
			Swal.fire({
				title: 'Memproses...',
				text: 'Sedang memproses print range Food Center',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			// Make AJAX request
			$.ajax({
				url: '{{ route('tbayarfc_print_range') }}',
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					txtbukti1: txtbukti1,
					txtbukti2: txtbukti2
				},
				xhrFields: {
					responseType: 'blob'
				},
				success: function(response) {
					Swal.close();

					// Create blob URL and open in new window
					const blob = new Blob([response], {
						type: 'application/pdf'
					});
					const url = window.URL.createObjectURL(blob);
					window.open(url, '_blank');

					// Clean up
					setTimeout(() => {
						window.URL.revokeObjectURL(url);
					}, 100);
				},
				error: function(xhr) {
					Swal.close();
					let errorMessage = 'Terjadi kesalahan saat print';

					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMessage = xhr.responseJSON.error;
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

		// Print Report Function
		function printReport() {
			Swal.fire({
				title: 'Memproses...',
				text: 'Sedang memproses laporan Food Center',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			window.open('{{ route('tbayarfc_jasper') }}', '_blank');
			Swal.close();
		}

		// Print All Food Center Function
		function printAllFC() {
			Swal.fire({
				title: 'Memproses...',
				text: 'Sedang memproses print semua data Food Center',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			window.open('{{ route('tbayarfc_print_all') }}', '_blank');
			Swal.close();
		}

		// Print Single Function (for print button in table)
		function printSingle(no_bukti) {
			Swal.fire({
				title: 'Memproses...',
				text: 'Sedang memproses print ' + no_bukti,
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			$.ajax({
				url: '{{ route('tbayarfc_print_single') }}',
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					no_bukti: no_bukti
				},
				xhrFields: {
					responseType: 'blob'
				},
				success: function(response) {
					Swal.close();

					// Create blob URL and open in new window
					const blob = new Blob([response], {
						type: 'application/pdf'
					});
					const url = window.URL.createObjectURL(blob);
					window.open(url, '_blank');

					// Clean up
					setTimeout(() => {
						window.URL.revokeObjectURL(url);
					}, 100);
				},
				error: function(xhr) {
					Swal.close();
					let errorMessage = 'Terjadi kesalahan saat print';

					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMessage = xhr.responseJSON.error;
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
	</script>

	<script>
		// Posting Function for Food Center
		function postingDataFC() {
			const checkedBoxes = $('.cek:checked');
			if (checkedBoxes.length === 0) {
				Swal.fire({
					title: 'Warning!',
					text: 'Pilih data Food Center yang akan diposting!',
					icon: 'warning',
					confirmButtonText: 'OK'
				});
				return;
			}

			const tgl_posting = document.getElementById('tgl_posting').value;
			if (!tgl_posting) {
				Swal.fire({
					title: 'Warning!',
					text: 'Tanggal posting harus diisi!',
					icon: 'warning',
					confirmButtonText: 'OK'
				});
				return;
			}

			Swal.fire({
				title: 'Konfirmasi Posting Food Center',
				text: `Apakah Anda yakin akan posting ${checkedBoxes.length} data Food Center?`,
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Ya, Posting!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					// Collect checked IDs
					const checkedIds = [];
					checkedBoxes.each(function() {
						checkedIds.push($(this).val());
					});

					$.ajax({
						url: '{{ route('tbayarfc_posting') }}',
						method: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							tgl_posting: tgl_posting,
							cek: checkedIds
						},
						success: function(response) {
							Swal.fire({
								title: 'Success!',
								text: 'Data Food Center berhasil diposting',
								icon: 'success',
								confirmButtonText: 'OK'
							}).then(() => {
								window.location.reload();
							});
						},
						error: function(xhr) {
							let errorMessage = 'Terjadi kesalahan saat posting Food Center';
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

		// Unposting Function for Food Center
		function unpostingDataFC() {
			const checkedBoxes = $('.cek:checked');
			if (checkedBoxes.length === 0) {
				Swal.fire({
					title: 'Warning!',
					text: 'Pilih data Food Center yang akan di-unposting!',
					icon: 'warning',
					confirmButtonText: 'OK'
				});
				return;
			}

			Swal.fire({
				title: 'Konfirmasi Unposting Food Center',
				text: `Apakah Anda yakin akan unposting ${checkedBoxes.length} data Food Center?`,
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Ya, Unposting!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					// Collect checked IDs
					const checkedIds = [];
					checkedBoxes.each(function() {
						checkedIds.push($(this).val());
					});

					$.ajax({
						url: '{{ route('tbayarfc_unposting') }}',
						method: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							cek: checkedIds
						},
						success: function(response) {
							Swal.fire({
								title: 'Success!',
								text: 'Data Food Center berhasil di-unposting',
								icon: 'success',
								confirmButtonText: 'OK'
							}).then(() => {
								window.location.reload();
							});
						},
						error: function(xhr) {
							let errorMessage = 'Terjadi kesalahan saat unposting Food Center';
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
	</script>
@endsection
