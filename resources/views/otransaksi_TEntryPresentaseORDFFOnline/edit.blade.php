@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<style>
		.card {
			padding: 15px;
		}

		.form-control:focus,
		.form-control:active {
			background-color: #b5e5f9;
		}

		.btn-action {
			padding: 8px 20px;
			font-weight: 600;
			font-size: 14px;
			margin: 0 3px;
		}

		.btn-save {
			background: #007bff;
			border: none;
			color: #fff;
		}

		.btn-save:hover {
			background: #0056b3;
			color: #fff;
		}

		.btn-back {
			background: #6c757d;
			border: none;
			color: #fff;
		}

		.btn-back:hover {
			background: #545b62;
			color: #fff;
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

		.form-group {
			margin-bottom: 20px;
		}

		.form-group label {
			font-weight: 600;
			margin-bottom: 8px;
			display: block;
			color: #333;
		}

		.form-control {
			border: 1px solid #ced4da;
			border-radius: 4px;
			padding: 8px 12px;
			font-size: 14px;
		}

		.form-control:disabled {
			background-color: #e9ecef;
			cursor: not-allowed;
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

		.input-group-text {
			background-color: #e9ecef;
			border: 1px solid #ced4da;
			padding: 8px 12px;
			font-weight: 600;
		}

		.text-right {
			text-align: right !important;
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
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<!-- Info Box -->
								<div class="info-box">
									<p class="mb-1"><strong>Petunjuk:</strong></p>
									<ul class="mb-0">
										<li>Isi <strong>Persentase</strong> yang akan diterapkan untuk Order Fresh Food</li>
										<li>Pilih <strong>Dari Tanggal</strong> dan <strong>Sampai Tanggal</strong> untuk periode berlakunya persentase</li>
										<li>Pastikan periode tanggal tidak tumpang tindih dengan data yang sudah ada</li>
										<li>Klik <strong>SAVE</strong> untuk menyimpan data</li>
									</ul>
								</div>

								<form id="formPersentase">
									@csrf
									<input type="hidden" name="status" id="status" value="{{ $status }}">
									<input type="hidden" name="no_id" id="no_id" value="{{ $no_id }}">

									<div class="row">
										<div class="col-md-6">
											<!-- Outlet -->
											<div class="form-group">
												<label for="outlet">Outlet / Cabang</label>
												<input type="text" class="form-control" id="outlet" value="{{ $cbg }}" readonly disabled>
											</div>

											<!-- Persentase -->
											<div class="form-group">
												<label for="persentase">Persentase <span class="text-danger">*</span></label>
												<div class="input-group">
													<input type="number" class="form-control text-right" id="persentase" name="persentase" step="0.01" min="0" max="100"
														value="{{ $data ? $data->PERSENTASE : 0 }}" required autofocus>
													<div class="input-group-append">
														<span class="input-group-text">%</span>
													</div>
												</div>
											</div>
										</div>

										<div class="col-md-6">
											<!-- Dari Tanggal -->
											<div class="form-group">
												<label for="tgl_aw">Dari Tanggal (Berlaku Mulai) <span class="text-danger">*</span></label>
												<input type="date" class="form-control" id="tgl_aw" name="tgl_aw"
													value="{{ $data ? date('Y-m-d', strtotime($data->TGL_AW)) : date('Y-m-d') }}" required>
											</div>

											<!-- Sampai Tanggal -->
											<div class="form-group">
												<label for="tgl_ak">Sampai Tanggal (Berlaku Sampai) <span class="text-danger">*</span></label>
												<input type="date" class="form-control" id="tgl_ak" name="tgl_ak"
													value="{{ $data ? date('Y-m-d', strtotime($data->TGL_AK)) : date('Y-m-d') }}" required>
											</div>
										</div>
									</div>

									<hr class="my-4">

									<div class="text-right">
										<button type="button" id="btnBack" class="btn btn-action btn-back">
											<i class="fas fa-arrow-left"></i> BACK
										</button>
										<button type="submit" id="btnSave" class="btn btn-action btn-save">
											<i class="fas fa-save"></i> SAVE
										</button>
									</div>
								</form>
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
		$(document).ready(function() {
			// Focus to persentase field
			$('#persentase').focus();

			// Button Back
			$('#btnBack').on('click', function() {
				window.location.href = '{{ route('entrypresentaseordffonline') }}';
			});

			// Form Submit
			$('#formPersentase').on('submit', function(e) {
				e.preventDefault();

				// Validasi
				var persentase = parseFloat($('#persentase').val());
				var tgl_aw = $('#tgl_aw').val();
				var tgl_ak = $('#tgl_ak').val();

				if (!persentase || persentase <= 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Validasi',
						text: 'Persentase harus lebih dari 0'
					});
					return;
				}

				if (!tgl_aw || !tgl_ak) {
					Swal.fire({
						icon: 'warning',
						title: 'Validasi',
						text: 'Tanggal mulai dan sampai harus diisi'
					});
					return;
				}

				if (new Date(tgl_ak) < new Date(tgl_aw)) {
					Swal.fire({
						icon: 'warning',
						title: 'Validasi',
						text: 'Tanggal sampai tidak boleh lebih kecil dari tanggal mulai'
					});
					return;
				}

				// Save data
				saveData();
			});
		});

		function saveData() {
			$('#LOADX').show();
			$('#btnSave').prop('disabled', true);

			var formData = {
				_token: '{{ csrf_token() }}',
				action: 'save',
				status: $('#status').val(),
				no_id: $('#no_id').val(),
				persentase: $('#persentase').val(),
				tgl_aw: $('#tgl_aw').val(),
				tgl_ak: $('#tgl_ak').val()
			};

			$.ajax({
				url: '{{ route('entrypresentaseordffonline_proses') }}',
				type: 'POST',
				data: formData,
				success: function(response) {
					$('#LOADX').hide();
					$('#btnSave').prop('disabled', false);

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							showConfirmButton: true,
							confirmButtonText: 'OK'
						}).then((result) => {
							if (result.isConfirmed) {
								window.location.href = '{{ route('entrypresentaseordffonline') }}';
							}
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					$('#btnSave').prop('disabled', false);

					var errorMsg = 'Gagal menyimpan data';
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
