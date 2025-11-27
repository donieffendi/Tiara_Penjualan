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

		.btn-posting {
			background: #007bff;
			border: none;
			color: #fff;
		}

		.btn-posting:hover {
			background: #0056b3;
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
										<li>Klik <strong>Proses Ambil Data</strong> untuk mengambil data LPH periode baru</li>
										<li>Klik <strong>Edit</strong> untuk mengubah data LPH per item</li>
										<li>Klik <strong>Print</strong> untuk mencetak laporan</li>
										<li>Centang item yang akan diproses, lalu klik <strong>Posting</strong></li>
										<li>Data yang sudah diposting tidak dapat diubah kembali</li>
									</ul>
								</div>

								<div class="mb-3 text-right">
									<button type="button" id="btnNew" class="btn btn-action btn-new">
										<i class="fas fa-plus"></i> Proses Ambil Data
									</button>
									<button type="button" id="btnPosting" class="btn btn-action btn-posting">
										<i class="fas fa-check"></i> Posting
									</button>
								</div>

								<hr>

								<!-- Data Table -->
								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="40px" class="text-center">
													<input type="checkbox" id="checkAll">
												</th>
												<th width="50px" class="text-center">No</th>
												<th width="120px">Sub</th>
												<th width="80px" class="text-center">J.Item</th>
												<th width="100px">Periode</th>
												<th width="100px">Outlet</th>
												<th>Keterangan</th>
												<th width="150px">Tanggal</th>
												<th width="100px" class="text-center">Posted</th>
												<th width="180px" class="text-center">Pilih</th>
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
					url: '{{ route('usulanlphperiode_cari') }}',
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}'
					}
				},
				columns: [{
						data: null,
						render: function(data, type, row) {
							if (row.post == 1) {
								return '';
							}
							return '<input type="checkbox" class="check-item" data-sub="' + row.sub +
								'" data-per="' + row.per + '" data-cbg="' + row.cbg +
								'" data-ket="' + row.ket + '" data-post="0">';
						},
						className: 'text-center',
						orderable: false
					},
					{
						data: null,
						render: function(data, type, row, meta) {
							return meta.row + 1;
						},
						className: 'text-center'
					},
					{
						data: 'sub'
					},
					{
						data: 'jml',
						className: 'text-center'
					},
					{
						data: 'per'
					},
					{
						data: 'cbg'
					},
					{
						data: 'ket'
					},
					{
						data: 'tg_smp'
					},
					{
						data: 'posted_label',
						className: 'text-center'
					},
					{
						data: 'action',
						className: 'text-center'
					}
				],
				order: [
					[2, 'asc']
				],
				processing: true
			});

			// Check All
			$('#checkAll').on('click', function() {
				var isChecked = $(this).prop('checked');
				$('.check-item').prop('checked', isChecked);
			});

			// Button New
			$('#btnNew').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi',
					text: 'Ingin Proses Ambil Data LPH?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#28a745',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						prosesNew();
					}
				});
			});

			// Button Posting
			$('#btnPosting').on('click', function() {
				var checkedItems = [];
				$('.check-item:checked').each(function() {
					checkedItems.push({
						sub: $(this).data('sub'),
						per: $(this).data('per'),
						cbg: $(this).data('cbg'),
						ket: $(this).data('ket'),
						post: '1'
					});
				});

				if (checkedItems.length === 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Tidak ada data yang dipilih untuk diposting'
					});
					return;
				}

				Swal.fire({
					title: 'Konfirmasi Posting',
					text: 'Apakah yakin akan memposting ' + checkedItems.length + ' item?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#007bff',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Posting!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						prosesPosting(checkedItems);
					}
				});
			});

			// Button Edit
			$(document).on('click', '.btn-edit', function() {
				var sub = $(this).data('sub');
				var per = $(this).data('per');
				var cbg = $(this).data('cbg');
				var ket = $(this).data('ket');

				var url = '{{ route('usulanlphperiode_edit', ':sub') }}';
				url = url.replace(':sub', encodeURIComponent(sub));
				url += '?per=' + encodeURIComponent(per) +
					'&cbg=' + encodeURIComponent(cbg) +
					'&ket=' + encodeURIComponent(ket);

				window.location.href = url;
			});

			// Button Print
			$(document).on('click', '.btn-print', function() {
				var sub = $(this).data('sub');
				var per = $(this).data('per');
				var ket = $(this).data('ket');

				Swal.fire({
					icon: 'info',
					title: 'Print',
					text: 'Fitur print akan dikembangkan'
				});
			});
		});

		function prosesNew() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('usulanlphperiode_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'new'
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 2000,
							showConfirmButton: false
						});

						table.ajax.reload();
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					var errorMsg = 'Gagal memproses data';
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

		function prosesPosting(items) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('usulanlphperiode_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'posting',
					items: items
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 2000,
							showConfirmButton: false
						});

						$('#checkAll').prop('checked', false);
						table.ajax.reload();
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

					var errorMsg = 'Gagal posting data';
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
