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

		.btn-allin {
			background: #17a2b8;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-allin:hover {
			background: #138496;
			color: #fff;
		}

		.btn-proses {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-proses:hover {
			background: #218838;
			color: #fff;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.form-check-input {
			width: 20px;
			height: 20px;
			cursor: pointer;
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

		.filter-section {
			background: #f8f9fa;
			padding: 15px;
			border-radius: 5px;
			margin-bottom: 20px;
		}

		.info-alert {
			background: #fff3cd;
			border: 1px solid #ffc107;
			padding: 10px;
			border-radius: 5px;
			margin-bottom: 15px;
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
								<div class="info-alert">
									<i class="fas fa-info-circle"></i>
									<strong>Informasi:</strong> Proses posting akan mengupdate stok barang berdasarkan hasil survey penjualan. Proses ini tidak dapat dibatalkan.
								</div>

								<div class="filter-section">
									<div class="row">
										<div class="col-md-12 text-right">
											<button type="button" id="btnAllIn" class="btn btn-allin">
												<i class="fas fa-check-double"></i> ALL IN
											</button>
											<button type="button" id="btnProses" class="btn btn-proses">
												<i class="fas fa-cog"></i> PROSES
											</button>
										</div>
									</div>
								</div>

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
												<th width="50px" class="text-center">
													<input type="checkbox" id="checkAll" class="form-check-input">
												</th>
												<th width="80px" class="text-center">No</th>
												<th>No Bukti</th>
												<th>Tanggal</th>
												<th>Notes</th>
												<th class="text-right">Qty</th>
												<th class="text-right">Total</th>
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
			loadData();

			// Page length change
			$('#pageLength').on('change', function() {
				table.page.len($(this).val()).draw();
			});

			// Search box
			$('#searchBox').on('keyup', function() {
				table.search($(this).val()).draw();
			});

			// Check all checkbox
			$('#checkAll').on('change', function() {
				$('.cek-item').prop('checked', $(this).is(':checked'));
			});

			// Button All In - centang semua checkbox
			$('#btnAllIn').on('click', function() {
				$('.cek-item').prop('checked', true);
				$('#checkAll').prop('checked', true);
				Swal.fire({
					icon: 'success',
					title: 'Berhasil',
					text: 'Semua data telah dipilih',
					timer: 1500,
					showConfirmButton: false
				});
			});

			// Button Proses
			$('#btnProses').on('click', function() {
				var selectedItems = [];
				$('.cek-item:checked').each(function() {
					selectedItems.push($(this).val());
				});

				if (selectedItems.length === 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Tidak ada data yang dipilih untuk diproses'
					});
					return;
				}

				Swal.fire({
					title: 'Konfirmasi Posting',
					html: 'Posting <strong>' + selectedItems.length +
						'</strong> dokumen Survey Penjualan?<br><small class="text-danger">Proses ini akan mengupdate stok dan tidak dapat dibatalkan</small>',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#28a745',
					cancelButtonColor: '#d33',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal',
					showLoaderOnConfirm: true,
					preConfirm: () => {
						$('#btnProses').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');
						$('#LOADX').show();

						return $.ajax({
							url: "{{ route('tpostingsurveypenjualan_detail', '') }}/" + selectedItems.join(','),
							type: 'GET',
							data: {
								flagz: 'PS'
							}
						}).fail(function(xhr) {
							$('#LOADX').hide();
							Swal.showValidationMessage('Posting gagal: ' + (xhr.responseJSON?.error ||
								'Terjadi kesalahan'));
						});
					},
					allowOutsideClick: () => !Swal.isLoading()
				}).then((result) => {
					$('#btnProses').prop('disabled', false).html('<i class="fas fa-cog"></i> PROSES');
					$('#LOADX').hide();

					if (result.isConfirmed) {
						var message = result.value.message;
						var hasErrors = result.value.errors && result.value.errors.length > 0;

						if (hasErrors) {
							message += '<br><br><small class="text-warning">Beberapa dokumen gagal diproses:<br>' +
								result.value.errors.join('<br>') + '</small>';
						}

						Swal.fire({
							icon: hasErrors ? 'warning' : 'success',
							title: hasErrors ? 'Selesai dengan Peringatan' : 'Berhasil',
							html: message + '<br><small>Data stok telah diupdate</small>',
							timer: 3000,
							showConfirmButton: true
						});
						table.ajax.reload();
						$('#checkAll').prop('checked', false);
					}
				});
			});
		});

		function loadData() {
			table = $('#tablePosting').KoolDataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: "{{ route('tpostingsurveypenjualan_cari') }}",
					type: 'POST',
					data: function(d) {
						d._token = '{{ csrf_token() }}';
						d.flagz = 'PS';
					}
				},
				columns: [{
						data: 'cek_checkbox',
						name: 'cek_checkbox',
						orderable: false,
						searchable: false,
						className: 'text-center'
					},
					{
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
						data: 'notes',
						name: 'notes'
					},
					{
						data: 'qty',
						name: 'qty',
						className: 'text-right'
					},
					{
						data: 'total',
						name: 'total',
						className: 'text-right'
					}
				],
				pageLength: 25,
				lengthMenu: [
					[10, 25, 50, 100, -1],
					[10, 25, 50, 100, "Semua"]
				],
				order: [
					[2, 'asc']
				],
				dom: 'rt<"row mt-3"<"col-md-6"i><"col-md-6"p>>'
			});
		}
	</script>
@endsection
