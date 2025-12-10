@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

	<style>
		tr.selected {
			background-color: #d1ecf1 !important;
		}

	</style>

@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $judul ?? 'Transaksi' }}</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="/">Home</a></li>
							<li class="breadcrumb-item active">{{ $judul ?? 'Transaksi' }}</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				@if (isset($error) && $error)
					<div class="alert alert-danger alert-dismissible fade show">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong>Error!</strong> {{ $error }}
					</div>
				@endif

				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<div class="row">
									<div class="col-md-6">
										@if ($route_name == 'tprosesstockopname')
											<button type="button" class="btn btn-primary" id="btn-new">
												<i class="fas fa-plus"></i> New
											</button>
										@endif

										<button type="button" class="btn btn-warning" id="btn-print-so" style="margin-left:10px;">
											<i class="fas fa-print"></i> Print SO
										</button>
										<button type="button" class="btn btn-info" id="btn-export-so">
											<i class="fas fa-file"></i> Export SO
										</button>
										<button type="button" class="btn btn-secondary" id="btn-print">
											<i class="fas fa-file"></i> Print
										</button>

									</div>
								</div>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table id="datatable" class="table-bordered table-striped table-sm table">
										<thead>
											<tr>
												<th width="5%">No</th>
												<th width="20%">No Bukti</th>
												<th width="15%">Tgl</th>
												<th width="15%">Total Qty</th>
												<th width="30%">Notes</th>
												<th width="10%">Type</th>
												<th width="5%">Action</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

<!-- Modal Print SO -->
<div class="modal fade" id="modalPrintSO" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cetak Ulang SO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row mb-3">
                    <div class="col-md-10">
                        <input type="text" id="searchSO" class="form-control" placeholder="Enter text to search...">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" id="btn-find-so">Find</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tableModalSO" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>No Bukti</th>
                                <th>Tanggal</th>
                                <th>Sub</th>
                                <th>User</th>
                                <th>Posted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
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

		let selected_nobukti = "";

		// Safety check for SweetAlert2
		if (typeof Swal === 'undefined') {
			console.error('SweetAlert2 not loaded! Using alert fallback.');
			window.Swal = {
				fire: function(options) {
					if (typeof options === 'string') {
						alert(arguments[0] + '\n' + (arguments[1] || ''));
						return Promise.resolve({
							isConfirmed: true,
							value: true
						});
					}
					var msg = options.title ? options.title + '\n' : '';
					msg += options.text ? options.text : '';

					if (options.showCancelButton) {
						var result = confirm(msg);
						return Promise.resolve({
							isConfirmed: result,
							value: result
						});
					} else {
						alert(msg);
						return Promise.resolve({
							isConfirmed: true,
							value: true
						});
					}
				},
				showValidationMessage: function(msg) {
					console.log('Validation: ' + msg);
				},
				isLoading: function() {
					return false;
				}
			};
		}

		$(document).ready(function() {
			var routeName = '{{ $route_name }}';
			var getDataUrl = routeName == 'tprosesstockopname' ?
				"{{ url('/tprosesstockopname/get-data') }}" :
				"{{ route('get-tpenangananlbtat-data') }}";

			var table = $('#datatable').DataTable({
				processing: true,
				serverSide: true,
				pageLength: 25,
				ajax: {
					url: getDataUrl
				},
				columns: [{
						data: 'DT_RowIndex',
						name: 'DT_RowIndex',
						orderable: false,
						searchable: false,
						className: 'text-center'
					},
					{
						data: 'NO_BUKTI',
						name: 'NO_BUKTI'
					},
					{
						data: 'TGL',
						name: 'TGL',
						className: 'text-center'
					},
					{
						data: 'total_qty',
						name: 'total_qty',
						className: 'text-right',
						render: function(data) {
							return parseFloat(data || 0).toFixed(2);
						}
					},
					{
						data: 'NOTES',
						name: 'NOTES'
					},
					{
						data: 'TYPE',
						name: 'TYPE',
						className: 'text-center'
					},
					{
						data: 'action',
						name: 'action',
						orderable: false,
						searchable: false,
						className: 'text-center'
					}
				],
				order: [
					[1, 'desc']
				],
				language: {
					lengthMenu: "Show _MENU_",
					search: "Search:",
					zeroRecords: "No matching records found",
					info: "Showing _START_ to _END_ of _TOTAL_ entries",
					infoEmpty: "Showing 0 to 0 of 0 entries",
					infoFiltered: "(filtered from _MAX_ total entries)",
					paginate: {
						first: "First",
						last: "Last",
						next: "Next",
						previous: "Previous"
					}
				}
			});

			// Edit button
			$(document).on('click', '.btn-edit', function() {
				var noBukti = $(this).data('bukti');
				var routeName = '{{ $route_name }}';
				var baseUrl = routeName == 'tprosesstockopname' ?
					"{{ route('tprosesstockopname') }}" :
					"{{ route('tpenangananlbtat') }}";

				window.location.href = baseUrl + '?status=edit&no_bukti=' + encodeURIComponent(noBukti);
			});

			// New button (only for stock opname) - MODIFIED: Direct redirect without Swal
			$('#btn-new').click(function() {
				// Show loading indicator
				$(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');

				$.ajax({
					url: "{{ route('tprosesstockopname.create') }}",
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}'
					},
					dataType: 'json',
					success: function(response) {
						if (response.success && response.redirect_url) {
							// Direct redirect to edit page
							window.location.href = response.redirect_url;
						} else {
							// Restore button state
							$('#btn-new').prop('disabled', false).html('<i class="fas fa-plus"></i> New');
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: response.message || 'Terjadi kesalahan',
								confirmButtonText: 'OK'
							});
						}
					},
					error: function(xhr) {
						// Restore button state
						$('#btn-new').prop('disabled', false).html('<i class="fas fa-plus"></i> New');

						var errorMsg = 'Terjadi kesalahan';
						if (xhr.responseJSON) {
							errorMsg = xhr.responseJSON.message || xhr.responseJSON.error || errorMsg;
						}

						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: errorMsg,
							confirmButtonText: 'OK'
						});
					}
				});
			});

			$("#btn-print-so").on("click", function () {
				$("#modalPrintSO").modal("show");

				// load datatable hanya sekali
				if (!$.fn.DataTable.isDataTable('#tableModalSO')) {
					loadModalSO();
				}
			});

			// tombol find
			$("#btn-find-so").on("click", function () {
				$('#tableModalSO').DataTable().ajax.reload();
			});

			// print
			$("#btn-print").on("click", function () {

				window.open(
					"tpenangananlbtat/jasper",
					"_blank"
				);

			});

			// pioih record
			$('#datatable tbody').on('click', 'tr', function () {
				let data = table.row(this).data();

				if (!data) return;

				selected_nobukti = data.NO_BUKTI ?? "";

				$('#datatable tbody tr').removeClass('selected');
				$(this).addClass('selected');
			});

		});

		function loadModalSO() {
			$('#tableModalSO').DataTable({
				processing: true,
				serverSide: true,
				searching: false,
				ajax: {
					url: "{{ route('tpenangananlbtat_print-so') }}",
					type: "GET"
				},
				columns: [
					{ data: "no_bukti", name: "no_bukti" },
					{ data: "tgl", name: "tgl" },
					{ data: "sub", name: "sub" },
					{ data: "usrnm", name: "usrnm" },
					{ 
						data: "posted",
						render: function (data) {
							return data == 1 
								? '<input type="checkbox" checked disabled>'
								: '<input type="checkbox" disabled>';
						}
					},
					{ 
						data: "no_bukti",
						render: function(no_bukti){
							return `
								<button class="btn btn-sm btn-success btn-print" data-id="${no_bukti}">
									Print
								</button>

								<button class="btn btn-sm btn-primary btn-buat-so" data-id="${no_bukti}">
									Buat SO 2
								</button>
							`;
						}
					}
				]
			});
		}

		// tombol Print SO
		$(document).on("click", ".btn-print", function () {
			let nobukti = $(this).data("id");

			window.open(
				"tpenangananlbtat/print-so/" + nobukti,
				"_blank"
			);
		});

		// tombol Buat SO 2
		$(document).on("click", ".btn-buat-so", function () {

			let nobukti = $(this).data("id");
			let prefix = nobukti.substring(0, 2); 

			if (prefix !== "XO" && prefix !== "XG") {
				Swal.fire("Tidak Bisa", "Nomor bukti ini tidak valid untuk pembuatan SO 2.", "warning");
				return; 
			}

			Swal.fire({
				title: "Processing...",
				text: "Sedang membuat SO. Mohon tunggu...",
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			$.ajax({
				url: "tpenangananlbtat/buat-so-2/" + nobukti,
				type: "POST",
				data: { 
					_token: "{{ csrf_token() }}"
				},
				success: function (res) {
					Swal.close(); 

					if(res.status){
						Swal.fire("Berhasil", "SO baru berhasil dibuat: " + res.bukti_baru, "success");
					} else {
						Swal.fire("Gagal", res.message, "error");
					}
				},
				error: function(xhr){
					Swal.close();

					Swal.fire("Error", xhr.responseJSON?.message ?? "Terjadi kesalahan.", "error");
				}
			});

		});


		$(document).on("click", "#btn-export-so", function () {
			// if (!selected_nobukti) {
			// 	Swal.fire("Pilih Data", "Klik salah satu baris SO terlebih dahulu.", "warning");
			// 	return;
			// }
			if (!selected_nobukti) {
				selected_nobukti = '';
			}
			Swal.fire({
				title: "Export SO?",
				text: "Yakin export SO nomor " + (selected_nobukti || "(SEMUA)") + "?",
				icon: "question",
				showCancelButton: true,
				confirmButtonText: "Ya, export",
				cancelButtonText: "Batal"
			}).then((result) => {
				if (result.isConfirmed) {

					Swal.fire({
						title: "Processing...",
						text: "Sedang export SO...",
						allowOutsideClick: false,
						didOpen: () => { Swal.showLoading(); }
					});

					$.ajax({
						url: "/tpenangananlbtat/export-so/" + selected_nobukti,
						type: "POST",
						data: { _token: "{{ csrf_token() }}" },
						success: function(res) {
							Swal.close();

							if (res.status) {
								Swal.fire("Berhasil", "File berhasil dibuat:<br><b>" + res.filepath + "</b>", "success");
							} else {
								Swal.fire("Gagal", res.message, "error");
							}
						},
						error: function(xhr) {
							Swal.close();
							Swal.fire("Error", xhr.responseJSON?.message ?? "Terjadi kesalahan.", "error");
						}
					});

				}
			});

		});

	</script>
@endsection
