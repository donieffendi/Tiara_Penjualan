@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
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
			padding: 10px 20px;
		}

		.btn-posting:hover {
			background: #0056b3;
			color: #fff;
		}

		.btn-action {
			margin: 5px;
			padding: 8px 16px;
			font-weight: 500;
		}

		.action-buttons {
			background: #f8f9fa;
			padding: 15px;
			border-radius: 8px;
			margin-top: 20px;
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
						<i class="fas fa-exclamation-triangle"></i> <strong>Peringatan!</strong> {{ $warning }}
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				@endif

				@if (isset($error))
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<i class="fas fa-exclamation-circle"></i> <strong>Error!</strong> {{ $error }}
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
										<div class="col-md-3">
											<div class="form-group">
												<label>&nbsp;</label>
												<button type="button" id="btnPosting" class="btn btn-posting btn-lg btn-block">
													<i class="fas fa-check-circle"></i> POSTING
												</button>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label>&nbsp;</label>
												<button type="button" id="btnPrint" class="btn btn-info btn-lg btn-block">
													<i class="fas fa-print"></i> CETAK LAPORAN
												</button>
											</div>
										</div>
									</div>
								</form>

								<hr>

								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tablePosting" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">
													<input type="checkbox" id="checkAll">
												</th>
												<th>Tanggal</th>
												<th class="text-center">Jumlah Transaksi</th>
												<th class="text-right">Total Amount</th>
												<th class="text-center">Status</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>

								<div class="action-buttons">
									<div class="row">
										<div class="col-md-12">
											<button type="button" id="btnUnpostingSelected" class="btn btn-warning btn-action">
												<i class="fas fa-undo"></i> Unposting Terpilih
											</button>
											<button type="button" id="btnRefresh" class="btn btn-secondary btn-action">
												<i class="fas fa-sync-alt"></i> Refresh
											</button>
										</div>
									</div>
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
	<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
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

			@if (isset($error))
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: '{{ $error }}',
					confirmButtonText: 'OK'
				});
			@endif

			$.fn.dataTable.ext.errMode = function(settings, helpPage, message) {
				console.error('DataTables Error:', message);
				Swal.fire({
					icon: 'error',
					title: 'Error DataTables',
					text: 'Terjadi kesalahan saat memuat data: ' + message,
					confirmButtonText: 'OK'
				});
			};

			table = $('#tablePosting').DataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: "{{ route('get-tpostingreport-post') }}",
					type: "GET",
					error: function(xhr) {
						var errorMsg = 'Terjadi kesalahan saat memuat data';
						if (xhr.responseJSON?.error) {
							errorMsg = xhr.responseJSON.error;
						}
						Swal.fire({
							icon: 'error',
							title: 'Error ' + xhr.status,
							text: errorMsg,
							confirmButtonText: 'OK'
						});
					}
				},
				columns: [{
						data: 'cek',
						name: 'cek',
						orderable: false,
						searchable: false,
						className: 'text-center'
					},
					{
						data: 'tgl_format',
						name: 'tgl_format'
					},
					{
						data: 'jml_transaksi',
						name: 'jml_transaksi',
						className: 'text-center'
					},
					{
						data: 'total_amount',
						name: 'total_amount',
						className: 'text-right',
						render: function(data) {
							return parseFloat(data).toLocaleString('id-ID', {
								minimumFractionDigits: 2,
								maximumFractionDigits: 2
							});
						}
					},
					{
						data: 'status',
						name: 'status',
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
				language: {
					processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
					search: "Cari:",
					lengthMenu: "Tampilkan _MENU_ data",
					info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
					infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
					infoFiltered: "(difilter dari _MAX_ total data)",
					paginate: {
						first: "Pertama",
						last: "Terakhir",
						next: "Selanjutnya",
						previous: "Sebelumnya"
					},
					zeroRecords: "Tidak ada data yang ditemukan",
					emptyTable: "Tidak ada data di tabel"
				},
				drawCallback: function(settings) {
					var json = settings.json;
					if (json && json.error) {
						Swal.fire({
							icon: 'warning',
							title: 'Perhatian',
							text: json.error,
							confirmButtonText: 'OK'
						});
					}
				}
			});

			$('#checkAll').on('click', function() {
				$('.cek:not(:disabled)').prop('checked', this.checked);
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
					html: 'Posting transaksi pada tanggal <strong>' + moment(tgl).format('DD-MM-YYYY') + '</strong>?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Posting!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						$('#btnPosting').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');
						$('#LOADX').show();

						return $.ajax({
							url: "{{ route('tpostingreport_posting') }}",
							type: 'POST',
							data: {
								_token: '{{ csrf_token() }}',
								tgl_posting: tgl
							}
						}).fail(function(xhr) {
							$('#LOADX').hide();
							Swal.showValidationMessage('Posting gagal: ' + (xhr.responseJSON?.error ||
								'Terjadi kesalahan'));
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
							text: 'Posting Selesai',
							timer: 2000,
							showConfirmButton: false
						});
						table.ajax.reload();
					}
				});
			});

			$('#btnUnpostingSelected').on('click', function() {
				var selected = [];
				$('.cek:checked').each(function() {
					selected.push($(this).val());
				});

				if (selected.length === 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Peringatan',
						text: 'Pilih minimal 1 tanggal untuk unposting'
					});
					return;
				}

				Swal.fire({
					title: 'Konfirmasi',
					html: 'Unposting <strong>' + selected.length + ' tanggal</strong> terpilih?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#ffc107',
					cancelButtonColor: '#d33',
					confirmButtonText: '<i class="fas fa-undo"></i> Ya, Unposting!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						$('#LOADX').show();
						$.ajax({
							url: "{{ route('tpostingreport_unposting') }}",
							type: 'POST',
							data: {
								_token: '{{ csrf_token() }}',
								cek: selected
							},
							success: function(response) {
								$('#LOADX').hide();
								Swal.fire({
									icon: 'success',
									title: 'Berhasil',
									text: 'Unposting berhasil',
									timer: 2000,
									showConfirmButton: false
								});
								table.ajax.reload();
								$('#checkAll').prop('checked', false);
							},
							error: function(xhr) {
								$('#LOADX').hide();
								Swal.fire({
									icon: 'error',
									title: 'Error',
									text: xhr.responseJSON?.error || 'Terjadi kesalahan'
								});
							}
						});
					}
				});
			});

			$('#btnRefresh').on('click', function() {
				table.ajax.reload();
				$('#checkAll').prop('checked', false);
			});

			$('#btnPrint').on('click', function() {
				window.open("{{ route('tpostingreport_jasper') }}", '_blank');
			});
		});
	</script>
@endsection
