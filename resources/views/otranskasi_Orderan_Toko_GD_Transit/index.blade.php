@extends('layouts.plain')

@push('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Orderan Toko GD Transit</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="/">Home</a></li>
							<li class="breadcrumb-item active">Orderan Toko GD Transit</li>
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
										<a href="{{ route('torderantokogdtransit.edit', ['status' => 'simpan']) }}" class="btn btn-primary">
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
											<th width="15%">Cbg</th>
											<th width="15%">Periode</th>
											<th width="15%">Total Qty</th>
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
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		$(document).ready(function() {
			// Display success/error messages from session
			@if (session('success'))
				Swal.fire({
					icon: 'success',
					title: 'Berhasil!',
					text: '{{ session('success') }}',
					confirmButtonText: 'OK',
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
					title: 'Informasi',
					text: '{{ session('info') }}',
					confirmButtonText: 'OK'
				});
			@endif

			var table = $('#datatable').DataTable({
				processing: true,
				serverSide: true,
				pageLength: 25,
				ajax: {
					url: "{{ route('torderantokogdtransit.get-data') }}",
					error: function(xhr, error, code) {
						console.log('DataTables Ajax Error:', {
							xhr: xhr,
							error: error,
							code: code
						});

						let errorMessage = 'Terjadi kesalahan saat memuat data.';

						if (xhr.responseJSON && xhr.responseJSON.message) {
							errorMessage = xhr.responseJSON.message;
						} else if (xhr.status === 500) {
							errorMessage = 'Server error: ' + (xhr.responseText || 'Internal server error');
						} else if (xhr.status === 0) {
							errorMessage = 'Network error: Tidak dapat terhubung ke server';
						}

						Swal.fire({
							icon: 'error',
							title: 'Error Loading Data',
							text: errorMessage,
							footer: 'Status: ' + xhr.status + ' | Error: ' + error
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
						data: 'no_bukti',
						name: 'no_bukti'
					},
					{
						data: 'tgl',
						name: 'tgl',
						className: 'text-center'
					},
					{
						data: 'cbg',
						name: 'cbg',
						className: 'text-center'
					},
					{
						data: 'per',
						name: 'per',
						className: 'text-center'
					},
					{
						data: 'total_qty',
						name: 'total_qty',
						className: 'text-right'
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
			window.location.href = "{{ route('torderantokogdtransit.edit') }}?status=edit&no_bukti=" + noBukti;
		}

		function printData(noBukti) {
			$.ajax({
				url: "{{ route('torderantokogdtransit.print') }}",
				type: 'GET',
				data: {
					_token: '{{ csrf_token() }}',
					no_bukti: noBukti
				},
				success: function(response) {
                    window.open(
                        '{{ route('torderantokogdtransit.print') }}?no_bukti=' + encodeURIComponent(noBukti),
                        '_blank'
                    );
				},
				error: function(xhr, status, error) {
					console.error('Print error:', xhr, status, error);
					let errorMessage = 'Gagal memuat data print';

					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMessage = xhr.responseJSON.message;
					} else if (xhr.responseText) {
						errorMessage = xhr.responseText;
					}

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMessage,
						footer: 'Status: ' + xhr.status
					});
				}
			});
		}

		function deleteData(noBukti) {
			Swal.fire({
				title: 'Konfirmasi Hapus',
				text: 'Apakah Anda yakin ingin menghapus data ' + noBukti + '?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Ya, Hapus!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({
						url: "{{ route('torderantokogdtransit.delete', '') }}/" + noBukti,
						type: 'DELETE',
						data: {
							_token: '{{ csrf_token() }}'
						},
						success: function(response) {
							if (response.success) {
								Swal.fire({
									icon: 'success',
									title: 'Berhasil!',
									text: response.message || 'Data berhasil dihapus',
									confirmButtonText: 'OK'
								}).then(() => {
									$('#datatable').DataTable().ajax.reload();
								});
							} else {
								Swal.fire({
									icon: 'error',
									title: 'Gagal!',
									text: response.message || 'Gagal menghapus data',
									confirmButtonText: 'OK'
								});
							}
						},
						error: function(xhr) {
							let errorMessage = 'Gagal menghapus data';
							if (xhr.responseJSON && xhr.responseJSON.message) {
								errorMessage = xhr.responseJSON.message;
							}

							Swal.fire({
								icon: 'error',
								title: 'Error!',
								text: errorMessage,
								confirmButtonText: 'OK'
							});
						}
					});
				}
			});
		}
	</script>
@endsection
