@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<style>
		.card {
			padding: 15px;
		}

		.form-control:focus {
			background-color: #b5e5f9;
		}

		.status-posted {
			color: #28a745;
			font-weight: bold;
			padding: 5px 10px;
			background: #d4edda;
			border-radius: 4px;
			display: inline-block;
		}

		.status-unposted {
			color: #dc3545;
			font-weight: bold;
			padding: 5px 10px;
			background: #f8d7da;
			border-radius: 4px;
			display: inline-block;
		}

		.btn-posting {
			background: #007bff;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 12px 30px;
			font-size: 16px;
		}

		.btn-posting:hover {
			background: #0056b3;
			color: #fff;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
		}

		.loader {
			position: fixed;
			top: 50%;
			left: 50%;
			width: 100px;
			aspect-ratio: 1;
			background:
				radial-gradient(farthest-side, #ffa516 90%, #0000) center/16px 16px,
				radial-gradient(farthest-side, green 90%, #0000) bottom/12px 12px;
			background-repeat: no-repeat;
			animation: l17 1s infinite linear;
			z-index: 9999;
			display: none;
		}

		.loader::before {
			content: "";
			position: absolute;
			width: 8px;
			aspect-ratio: 1;
			inset: auto 0 16px;
			margin: auto;
			background: #ccc;
			border-radius: 50%;
			transform-origin: 50% calc(100% + 10px);
			animation: inherit;
			animation-duration: 0.5s;
		}

		@keyframes l17 {
			100% {
				transform: rotate(1turn)
			}
		}
	</style>
@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $judul }}</h1>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				@if (isset($warning))
					<div class="alert alert-warning alert-dismissible fade show" role="alert">
						<i class="fas fa-exclamation-triangle"></i> <strong>Perhatian!</strong> {{ $warning }}
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				@endif

				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form id="formPosting">
									@csrf
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label for="tgl_posting"><strong>Tanggal Posting</strong></label>
												<input type="date" class="form-control form-control-lg" id="tgl_posting" name="tgl_posting" value="{{ date('Y-m-d') }}" required>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<label>&nbsp;</label>
												<button type="button" id="btnPosting" class="btn btn-posting btn-block">
													<i class="fas fa-check-circle"></i> POSTING
												</button>
											</div>
										</div>
									</div>
								</form>

								<hr>

								<div class="row mb-3">
									<div class="col-md-6">
										<div class="form-inline">
											<label>Tampilkan</label>
											<select class="form-control form-control-sm mx-2" id="pageLength">
												<option value="10">10</option>
												<option value="25" selected>25</option>
												<option value="50">50</option>
												<option value="100">100</option>
												<option value="-1">Semua</option>
											</select>
											<label>data</label>
										</div>
									</div>
									<div class="col-md-6 text-right">
										<div class="form-inline float-right">
											<label>Cari:</label>
											<input type="text" class="form-control form-control-sm ml-2" id="searchBox">
										</div>
									</div>
								</div>

								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tablePosting" style="width:100%">
										<thead>
											<tr>
												<th width="80px" class="text-center">No</th>
												<th>Periode</th>
												<th class="text-center" width="200px">Status</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="loader" id="LOADX"></div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		var table;

		$(document).ready(function() {
			@if (isset($warning))
				Swal.fire({
					icon: 'warning',
					title: 'Peringatan',
					text: '{{ $warning }}',
					confirmButtonText: 'OK'
				});
			@endif

			$.fn.dataTable.ext.errMode = function(settings, helpPage, message) {
				console.error('DataTables Error:', message);
				Swal.fire({
					icon: 'error',
					title: 'Error DataTables',
					text: message,
					confirmButtonText: 'OK'
				});
			};

			table = $('#tablePosting').KoolDataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: "{{ route('get-tpostingakhirbulan-post') }}",
					type: "GET",
					error: function(xhr, error, code) {
						var errorMsg = 'Terjadi kesalahan saat memuat data';
						if (xhr.status === 0) {
							errorMsg = 'Tidak dapat terhubung ke server';
						} else if (xhr.status == 404) {
							errorMsg = 'Halaman tidak ditemukan (404)';
						} else if (xhr.status == 500) {
							errorMsg = xhr.responseJSON?.message || 'Internal Server Error';
						}
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: errorMsg
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
						data: 'periode_format',
						name: 'periode_format'
					},
					{
						data: 'status_text',
						name: 'status_text',
						className: 'text-center'
					}
				],
				pageLength: 25,
				lengthMenu: [
					[10, 25, 50, 100, -1],
					[10, 25, 50, 100, "Semua"]
				],
				order: [
					[1, 'desc']
				],
				dom: 'rt<"row mt-3"<"col-md-6"i><"col-md-6"p>>'
			});

			$('#pageLength').on('change', function() {
				table.page.len($(this).val()).draw();
			});

			$('#searchBox').on('keyup', function() {
				table.search($(this).val()).draw();
			});

			$('#btnPosting').on('click', function() {
				var tgl = $('#tgl_posting').val();

				if (!tgl) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Tanggal posting harus diisi'
					});
					return;
				}

				Swal.fire({
					title: 'Konfirmasi Posting',
					html: 'Posting akhir bulan untuk tanggal <strong>' + moment(tgl).format('DD-MM-YYYY') + '</strong>?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Posting!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						$('#btnPosting').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING');
						$('#LOADX').show();

						return $.ajax({
							url: "{{ route('tpostingakhirbulan_posting') }}",
							type: 'POST',
							data: {
								_token: '{{ csrf_token() }}',
								tgl_posting: tgl
							}
						}).fail(function(xhr) {
							$('#LOADX').hide();
							Swal.showValidationMessage(xhr.responseJSON?.error || 'Terjadi kesalahan');
						});
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					$('#btnPosting').prop('disabled', false).html('<i class="fas fa-check-circle"></i> POSTING');
					$('#LOADX').hide();

					if (result.isConfirmed) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: result.value.message,
							timer: 2000,
							showConfirmButton: false
						});
						table.ajax.reload();
					}
				});
			});
		});
	</script>
@endsection
