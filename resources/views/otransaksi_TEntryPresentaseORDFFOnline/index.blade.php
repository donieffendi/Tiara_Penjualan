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

		.btn-action {
			padding: 8px 20px;
			font-weight: 600;
			font-size: 14px;
			margin: 0 3px;
		}

		.btn-new {
			background: #28a745;
			border: none;
			color: #fff;
		}

		.btn-new:hover {
			background: #218838;
			color: #fff;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 13px;
			padding: 12px 8px;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.table tbody td {
			padding: 8px;
			font-size: 13px;
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

		.text-right {
			text-align: right !important;
		}

		.text-center {
			text-align: center !important;
		}

		.info-box {
			background: #e7f3ff;
			border: 1px solid #b3d9ff;
			padding: 15px;
			border-radius: 5px;
			margin-bottom: 20px;
		}

		.info-box strong {
			color: #0056b3;
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
						<strong>Perhatian!</strong> {{ $warning }}
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				@endif

				@if (isset($error))
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<strong>Error!</strong> {{ $error }}
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				@endif

				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<!-- Info Box -->
								<div class="info-box">
									<p class="mb-1"><strong>Petunjuk:</strong></p>
									<ul class="mb-0">
										<li>Klik <strong>NEW</strong> untuk menambah persentase baru</li>
										<li>Klik <strong>Edit</strong> untuk mengubah data persentase</li>
										<li>Klik <strong>Hapus</strong> untuk menghapus data persentase</li>
										<li>Pastikan periode tanggal tidak tumpang tindih dengan data yang sudah ada</li>
									</ul>
								</div>

								<div class="mb-3 text-right">
									<button type="button" id="btnNew" class="btn btn-action btn-new">
										<i class="fas fa-plus"></i> NEW
									</button>
								</div>

								<hr>

								<!-- Data Table -->
								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">No</th>
												<th width="150px">Tanggal Entry</th>
												<th width="120px" class="text-center">Persen (%)</th>
												<th width="130px" class="text-center">Berlaku Mulai Tgl</th>
												<th width="130px" class="text-center">Berlaku Sampai Tgl</th>
												<th width="120px">User</th>
												<th width="200px" class="text-center">Aksi</th>
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
	</div>

	<div class="loader" id="LOADX"></div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		var table;

		$(document).ready(function() {
			// Initialize DataTable
			table = $('#tableData').DataTable({
				ajax: {
					url: '{{ route('entrypresentaseordffonline_cari') }}',
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}'
					}
				},
				columns: [{
						data: null,
						render: function(data, type, row, meta) {
							return meta.row + 1;
						},
						className: 'text-center'
					},
					{
						data: 'TGL'
					},
					{
						data: 'PERSENTASE',
						className: 'text-center'
					},
					{
						data: 'TGL_AW',
						className: 'text-center'
					},
					{
						data: 'TGL_AK',
						className: 'text-center'
					},
					{
						data: 'USRNM'
					},
					{
						data: 'action',
						className: 'text-center'
					}
				],
				order: [
					[1, 'desc']
				],
				processing: true
			});

			// Button New
			$('#btnNew').on('click', function() {
				window.location.href = '{{ route('entrypresentaseordffonline') }}/edit/new';
			});

			// Button Edit
			$(document).on('click', '.btn-edit', function() {
				var no_id = $(this).data('id');
				window.location.href = '{{ route('entrypresentaseordffonline') }}/edit/' + no_id;
			});

			// Button Delete
			$(document).on('click', '.btn-delete', function() {
				var no_id = $(this).data('id');

				Swal.fire({
					title: 'Konfirmasi Hapus',
					text: 'Apakah Data ini akan di hapus?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Hapus!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						deleteData(no_id);
					}
				});
			});
		});

		function deleteData(no_id) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('entrypresentaseordffonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'delete',
					no_id: no_id
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 1500,
							showConfirmButton: false
						});

						table.ajax.reload();
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					var errorMsg = 'Gagal menghapus data';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMsg = xhr.responseJSON.error;
					}

					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMsg
					});
				}
			});
		}
	</script>
@endsection
