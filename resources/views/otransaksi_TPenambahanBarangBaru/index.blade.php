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

		.btn-center {
			background: #17a2b8;
			border: none;
			color: #fff;
		}

		.btn-center:hover {
			background: #138496;
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

		.search-box {
			border: 1px solid #dee2e6;
			border-radius: 5px;
			padding: 15px;
			margin-bottom: 20px;
			background: #f8f9fa;
		}

		.search-box label {
			font-weight: 600;
			margin-bottom: 5px;
			display: block;
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
										<li>Klik <strong>NEW</strong> untuk menambah data penambahan barang baru</li>
										<li>Klik <strong>Daftar Barang Center</strong> untuk sinkronisasi data barang dari Data Center</li>
										<li>Klik <strong>Edit</strong> untuk mengubah data</li>
										<li>Klik <strong>Posting</strong> untuk menerbitkan barang ke master (tekan P pada keyboard)</li>
										<li>Klik <strong>Hapus</strong> untuk menghapus data (tekan Delete pada keyboard)</li>
									</ul>
								</div>

								<!-- Search Box -->
								<div class="search-box">
									<div class="row">
										<div class="col-md-3">
											<label for="txtBukti">No Bukti</label>
											<input type="text" class="form-control" id="txtBukti" placeholder="Cari berdasarkan No Bukti">
										</div>
										<div class="col-md-3">
											<label for="txtPeriode">Periode</label>
											<input type="text" class="form-control" id="txtPeriode"
												value="{{ is_array($periode ?? '') ? implode('/', $periode) : $periode ?? '-' }}" readonly disabled>
										</div>
										<div class="col-md-3">
											<label for="txtUser">User</label>
											<input type="text" class="form-control" id="txtUser" placeholder="Cari berdasarkan User">
										</div>
										<div class="col-md-3">
											<label>&nbsp;</label>
											<button type="button" id="btnRefresh" class="btn btn-secondary btn-block">
												<i class="fas fa-sync"></i> Refresh
											</button>
										</div>
									</div>
								</div>

								<div class="mb-3 text-right">
									<button type="button" id="btnCenter" class="btn btn-action btn-center">
										<i class="fas fa-database"></i> Daftar Barang Center
									</button>
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
												<th width="200px">No Bukti</th>
												<th width="150px" class="text-center">Tanggal</th>
												<th width="150px">User</th>
												<th width="100px" class="text-center">Posted</th>
												<th width="300px" class="text-center">Aksi</th>
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
					url: '{{ route('penambahanbarangbaru_cari') }}',
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}'
					},
					error: function(xhr, error, code) {
						console.error('DataTables Ajax error:', xhr.responseText);

						if (xhr.status === 500 || xhr.status === 400) {
							var errorMsg = 'Terjadi kesalahan saat memuat data.';

							try {
								var response = JSON.parse(xhr.responseText);
								if (response.error) {
									errorMsg = response.error;
								}
							} catch (e) {
								errorMsg = 'Tabel database belum dibuat. Hubungi administrator.';
							}

							Swal.fire({
								icon: 'warning',
								title: 'Perhatian',
								text: errorMsg,
								confirmButtonText: 'OK'
							});
						}
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
						data: 'NO_BUKTI'
					},
					{
						data: 'TGL',
						className: 'text-center'
					},
					{
						data: 'USRNM'
					},
					{
						data: 'POSTED',
						className: 'text-center'
					},
					{
						data: 'action',
						className: 'text-center'
					}
				],
				order: [
					[2, 'desc']
				],
				processing: true
			});

			// Button New
			$('#btnNew').on('click', function(e) {
				e.preventDefault();
				window.location.href = '{{ url('tpenambahanbarangbaru/edit/new') }}';
			});

			// Button Center - Daftar Barang dari DC
			$('#btnCenter').on('click', function() {
				Swal.fire({
					icon: 'info',
					title: 'Daftar Barang Center',
					text: 'Fitur sinkronisasi barang dari Data Center akan dikembangkan'
				});
			});

			// Button Refresh
			$('#btnRefresh').on('click', function() {
				$('#txtBukti').val('');
				$('#txtUser').val('');
				table.ajax.reload();
			});

			// Search No Bukti
			$('#txtBukti').on('keydown', function(e) {
				if (e.key === 'Enter') {
					var nobukti = $(this).val().trim();
					if (nobukti) {
						table.search(nobukti).draw();
					}
				}
			});

			// Search User
			$('#txtUser').on('keydown', function(e) {
				if (e.key === 'Enter') {
					var user = $(this).val().trim();
					if (user) {
						table.search(user).draw();
					}
				}
			});

			// Button Edit
			$(document).on('click', '.btn-edit', function() {
				var nobukti = $(this).data('nobukti');
				window.location.href = '{{ route('penambahanbarangbaru') }}/edit/' + encodeURIComponent(nobukti);
			});

			// Button Delete
			$(document).on('click', '.btn-delete', function() {
				if ($(this).prop('disabled')) {
					return;
				}

				var nobukti = $(this).data('nobukti');

				Swal.fire({
					title: 'Konfirmasi Hapus',
					text: 'Apakah nomor ' + nobukti + ' ingin dihapus?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Hapus!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						deleteData(nobukti);
					}
				});
			});

			// Button Post
			$(document).on('click', '.btn-post', function() {
				if ($(this).prop('disabled')) {
					return;
				}

				var nobukti = $(this).data('nobukti');

				Swal.fire({
					title: 'Konfirmasi Posting',
					text: 'Pastikan data sudah benar. Posting No. ' + nobukti + '?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#28a745',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Posting!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						postingData(nobukti);
					}
				});
			});

			// Keyboard shortcuts
			$(document).on('keydown', function(e) {
				// Delete key
				if (e.key === 'Delete') {
					var selectedRow = table.row('.selected').data();
					if (selectedRow && selectedRow.POSTED == 0) {
						$('.btn-delete[data-nobukti="' + selectedRow.NO_BUKTI + '"]').click();
					}
				}

				// P key for posting
				if (e.key === 'p' || e.key === 'P') {
					var selectedRow = table.row('.selected').data();
					if (selectedRow && selectedRow.POSTED == 0) {
						$('.btn-post[data-nobukti="' + selectedRow.NO_BUKTI + '"]').click();
					}
				}
			});

			// Row selection
			$('#tableData tbody').on('click', 'tr', function() {
				if ($(this).hasClass('selected')) {
					$(this).removeClass('selected');
				} else {
					table.$('tr.selected').removeClass('selected');
					$(this).addClass('selected');
				}
			});
		});

		function deleteData(nobukti) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('penambahanbarangbaru_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'delete',
					no_bukti: nobukti
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

		function postingData(nobukti) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('penambahanbarangbaru_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'posting',
					no_bukti: nobukti
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							showConfirmButton: true
						}).then(() => {
							table.ajax.reload();
						});
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
