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

		.btn-tampil {
			background: #007bff;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 12px 30px;
			font-size: 16px;
		}

		.btn-tampil:hover {
			background: #0056b3;
			color: #fff;
		}

		.btn-cetak {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 12px 30px;
			font-size: 16px;
		}

		.btn-cetak:hover {
			background: #218838;
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
			background: radial-gradient(farthest-side, #ffa516 90%, #0000) center/16px 16px,
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

		.input-group-sm {
			margin-bottom: 10px;
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
								<div class="row mb-3">
									<div class="col-md-3">
										<div class="input-group input-group-sm">
											<div class="input-group-prepend">
												<span class="input-group-text"><strong>Order Ke</strong></span>
											</div>
											<select class="form-control" id="cbOrder">
												<option value="">-- Pilih Lokasi --</option>
												<option value="FSA">FR.SANGLAH</option>
												<option value="FYA">FR.YEHAYA</option>
											</select>
										</div>
									</div>
									<div class="col-md-3">
										<div class="input-group input-group-sm">
											<div class="input-group-prepend">
												<span class="input-group-text"><strong>Sub Item</strong></span>
											</div>
											<input type="text" class="form-control" id="txtKdbrg" placeholder="Masukkan Kode Barang">
										</div>
									</div>
									<div class="col-md-2">
										<button type="button" id="btnTampil" class="btn btn-tampil">
											<i class="fas fa-eye"></i> TAMPILKAN
										</button>
									</div>
								</div>

								<div class="row mb-3">
									<div class="col-md-12">
										<button type="button" id="btnCetak" class="btn btn-cetak">
											<i class="fas fa-print"></i> CETAK
										</button>
									</div>
								</div>

								<hr>

								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableOrderKB" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">No</th>
												<th width="100px">Order Ke</th>
												<th width="100px">Sub Item</th>
												<th>Nama Barang</th>
												<th width="100px">Ukuran</th>
												<th width="100px">Satuan</th>
												<th width="80px" class="text-center">Min Kirim</th>
												<th width="80px" class="text-center">Max Kirim</th>
												<th width="80px" class="text-center">LPH</th>
												<th width="100px">Supp</th>
												<th width="80px" class="text-center">Stok</th>
												<th width="80px" class="text-center">Aksi</th>
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
		var orderKe = '';

		$(document).ready(function() {
			table = $('#tableOrderKB').KoolDataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: "{{ route('get-tbuatorderankuebasah-data') }}",
					data: function(d) {
						d.order_ke = orderKe;
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
						data: 'PESAN_KE',
						name: 'PESAN_KE'
					},
					{
						data: 'KD_BRG',
						name: 'KD_BRG'
					},
					{
						data: 'NA_BRG',
						name: 'NA_BRG'
					},
					{
						data: 'KET_UK',
						name: 'KET_UK'
					},
					{
						data: 'KET_KEM',
						name: 'KET_KEM'
					},
					{
						data: 'PMIN',
						name: 'PMIN',
						className: 'text-center'
					},
					{
						data: 'PMAX',
						name: 'PMAX',
						className: 'text-center'
					},
					{
						data: 'LPH',
						name: 'LPH',
						className: 'text-center'
					},
					{
						data: 'SUPP',
						name: 'SUPP'
					},
					{
						data: 'STOK',
						name: 'STOK',
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
					[2, 'asc']
				]
			});

			$('#btnTampil').on('click', function() {
				var selectedOrder = $('#cbOrder').val();

				if (!selectedOrder) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Lokasi orderan belum dipilih..'
					});
					return;
				}

				orderKe = selectedOrder;
				table.ajax.reload();
			});

			$('#txtKdbrg').on('keypress', function(e) {
				if (e.which === 13) {
					var kdBrg = $(this).val().trim();
					var selectedOrder = $('#cbOrder').val();

					if (!selectedOrder) {
						Swal.fire({
							icon: 'warning',
							title: 'Perhatian',
							text: 'Lokasi orderan belum dipilih..'
						});
						return;
					}

					if (!kdBrg) {
						return;
					}

					$('#LOADX').show();

					$.ajax({
						url: "{{ route('tbuatorderankuebasah_browse') }}",
						type: 'GET',
						data: {
							type: 'barang',
							kd_brg: kdBrg,
							order_ke: selectedOrder
						},
						success: function(response) {
							$('#LOADX').hide();

							if (!response.success) {
								Swal.fire({
									icon: 'error',
									title: 'Error',
									text: response.message
								});
								$('#txtKdbrg').val('').focus();
								return;
							}

							if (response.ada_sp > 0) {
								Swal.fire({
									icon: 'warning',
									title: 'Perhatian',
									text: 'Brg Sedang Diorder Ke Supplier!'
								});
								$('#txtKdbrg').val('').focus();
								return;
							}

							$.ajax({
								url: "{{ route('tbuatorderankuebasah_store') }}",
								type: 'POST',
								data: {
									_token: '{{ csrf_token() }}',
									kd_brg: kdBrg,
									order_ke: selectedOrder
								},
								success: function(result) {
									Swal.fire({
										icon: 'success',
										title: 'Berhasil',
										text: result.message,
										timer: 1500,
										showConfirmButton: false
									});
									$('#txtKdbrg').val('').focus();
									table.ajax.reload();
								},
								error: function(xhr) {
									Swal.fire({
										icon: 'error',
										title: 'Error',
										text: xhr.responseJSON?.error || 'Terjadi kesalahan'
									});
								}
							});
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

			$('#tableOrderKB').on('click', '.btn-hapus', function() {
				var kdBrg = $(this).data('kd');
				var pesanKe = $(this).data('ke');
				var row = $(this).closest('tr');
				var naBrg = row.find('td:eq(3)').text();

				Swal.fire({
					title: 'Konfirmasi',
					text: 'Batalkan order barang ' + naBrg + ' (' + kdBrg + ') ke ' + pesanKe + '?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya',
					cancelButtonText: 'Tidak'
				}).then((result) => {
					if (result.isConfirmed) {
						$('#LOADX').show();

						$.ajax({
							url: "{{ route('tbuatorderankuebasah_proses') }}",
							type: 'POST',
							data: {
								_token: '{{ csrf_token() }}',
								kd_brg: kdBrg,
								order_ke: pesanKe
							},
							success: function(response) {
								$('#LOADX').hide();
								Swal.fire({
									icon: 'success',
									title: 'Berhasil',
									text: response.message,
									timer: 1500,
									showConfirmButton: false
								});
								table.ajax.reload();
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

			$('#btnCetak').on('click', function() {
				var selectedOrder = $('#cbOrder').val();

				if (!selectedOrder) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Lokasi orderan belum dipilih..'
					});
					return;
				}

				$('#LOADX').show();

				$.ajax({
					url: "{{ route('tbuatorderankuebasah_jasper') }}",
					type: 'GET',
					data: {
						order_ke: selectedOrder
					},
					success: function(response) {
						$('#LOADX').hide();

						if (response.success) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: 'Laporan berhasil digenerate',
								timer: 2000,
								showConfirmButton: false
							});
						}
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
			});

			$('#cbOrder').on('change', function() {
				$('#txtKdbrg').val('').focus();
			});
		});
	</script>
@endsection
