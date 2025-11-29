@extends('layouts.plain')

@push('styles')
	<!-- SweetAlert2 CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Koreksi Toko Manual</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="/">Home</a></li>
							<li class="breadcrumb-item active">Koreksi Toko Manual</li>
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
							<div class="card-header">
								<div class="row">
									<div class="col-md-6">
										<a href="{{ route('tkoreksitokomanual.edit', ['status' => 'simpan']) }}" class="btn btn-primary">
											<i class="fas fa-plus"></i> New
										</a>
									</div>
								</div>
							</div>
							<div class="card-body">
								<table id="datatable" class="table-bordered table-striped table-sm table">
									<thead>
										<tr>
											<th width="5%">No</th>
											<th width="25%">No Bukti</th>
											<th width="15%">Tgl</th>
											<th width="15%">Total Qty</th>
											<th width="25%">Notes</th>
											<th width="10%">Posted</th>
											<th width="10%">Action</th>
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
@endsection

@section('javascripts')
	<!-- SweetAlert2 JS -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		// Display session messages
		@if (session('success'))
			Swal.fire({
				icon: 'success',
				title: 'Berhasil!',
				text: '{{ session('success') }}',
				showConfirmButton: true,
				timer: 3000
			});
		@endif

		@if (session('error'))
			Swal.fire({
				icon: 'error',
				title: 'Error!',
				text: '{{ session('error') }}',
				confirmButtonText: 'OK'
			});
		@endif

		@if (session('warning'))
			Swal.fire({
				icon: 'warning',
				title: 'Peringatan!',
				text: '{{ session('warning') }}',
				confirmButtonText: 'OK'
			});
		@endif

		@if (session('info'))
			Swal.fire({
				icon: 'info',
				title: 'Info',
				text: '{{ session('info') }}',
				confirmButtonText: 'OK'
			});
		@endif

		$(document).ready(function() {
			var table = $('#datatable').DataTable({
				processing: true,
				serverSide: true,
				pageLength: 25,
				ajax: {
					url: "{{ route('tkoreksitokomanual.get-data') }}",
					error: function(xhr, error, code) {
						console.log('DataTables Ajax Error:', {
							status: xhr.status,
							statusText: xhr.statusText,
							responseText: xhr.responseText,
							error: error,
							code: code
						});

						let errorMessage = 'Terjadi kesalahan saat memuat data.';

						if (xhr.status === 500) {
							try {
								const response = JSON.parse(xhr.responseText);
								errorMessage = response.message || 'Internal Server Error';
							} catch (e) {
								errorMessage = 'Internal Server Error: ' + xhr.statusText;
							}
						} else if (xhr.status === 0) {
							errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
						} else {
							errorMessage = 'Error ' + xhr.status + ': ' + xhr.statusText;
						}

						Swal.fire({
							icon: 'error',
							title: 'Error Loading Data',
							text: errorMessage,
							footer: '<a href="http://datatables.net/tn/7" target="_blank">Informasi lebih lanjut tentang error ini</a>',
							confirmButtonText: 'OK'
						});
					}
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
						data: 'tgl',
						name: 'TGL',
						className: 'text-center'
					},
					{
						data: 'total_qty',
						name: 'TOTAL_QTY',
						className: 'text-right'
					},
					{
						data: 'NOTES',
						name: 'NOTES'
					},
					{
						data: 'posted',
						name: 'POSTED',
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
		});

		function editData(noBukti) {
			window.location.href = "{{ route('tkoreksitokomanual.edit') }}?status=edit&no_bukti=" + noBukti;
		}

		function printData(noBukti) {
			$.ajax({
				url: "{{ route('tkoreksitokomanual.print') }}",
				type: 'GET',
				data: {
					_token: '{{ csrf_token() }}',
					no_bukti: noBukti
				},
				success: function(response) {
					window.open(
                        '{{ route('tkoreksitokomanual.print') }}?no_bukti=' + encodeURIComponent(noBukti),
                        '_blank'
                    );
				},
				error: function(xhr) {
					console.error('Print error:', xhr);

					let errorMessage = 'Gagal memuat data print';

					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMessage = xhr.responseJSON.message;
					} else if (xhr.status === 500) {
						errorMessage = 'Internal Server Error. Periksa log untuk detail.';
					} else if (xhr.status === 404) {
						errorMessage = 'Data tidak ditemukan';
					}

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMessage,
						footer: xhr.status ? 'HTTP Status: ' + xhr.status : '',
						confirmButtonText: 'OK'
					});
				}
			});
		}
	</script>
@endsection
