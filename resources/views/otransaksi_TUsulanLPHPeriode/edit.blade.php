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

		.btn-back {
			background: #6c757d;
			border: none;
			color: #fff;
		}

		.btn-back:hover {
			background: #545b62;
			color: #fff;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 12px;
			padding: 10px 6px;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.table tbody td {
			padding: 6px;
			font-size: 11px;
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

		.form-group {
			margin-bottom: 15px;
		}

		.form-group label {
			font-weight: 600;
			margin-bottom: 5px;
			display: block;
			color: #333;
		}

		.editable-cell {
			background-color: #fff3cd !important;
			cursor: pointer;
		}

		.editable-cell:hover {
			background-color: #ffe69c !important;
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
										<li>Data ditampilkan berdasarkan Sub dan Keterangan yang dipilih</li>
										<li>Klik pada kolom <strong>LPH</strong> atau <strong>DTR</strong> untuk mengubah nilai</li>
										<li>Sistem akan otomatis menghitung parameter lain (SMIN, SMAX, SRMIN, SRMAX, KDLAKU)</li>
										<li>Perubahan akan tersimpan secara otomatis</li>
									</ul>
								</div>

								<!-- Form Header -->
								<div class="row">
									<div class="col-md-3">
										<div class="form-group">
											<label>Sub</label>
											<input type="text" class="form-control" id="txtSub" value="{{ $sub }}" readonly>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label>Periode</label>
											<input type="text" class="form-control" id="txtPeriode" value="{{ $periode }}" readonly>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label>Outlet</label>
											<input type="text" class="form-control" id="txtOutlet" value="{{ $outlet }}" readonly>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label>Keterangan</label>
											<input type="text" class="form-control" id="txtKet" value="{{ $ket }}" readonly>
										</div>
									</div>
								</div>

								<div class="mb-3 text-right">
									<button type="button" id="btnBack" class="btn btn-action btn-back">
										<i class="fas fa-arrow-left"></i> BACK
									</button>
								</div>

								<hr>

								<!-- Data Table -->
								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%; font-size: 11px;">
										<thead>
											<tr>
												<th width="40px" class="text-center">No</th>
												<th width="100px">Sub Item</th>
												<th>Nama Barang</th>
												<th width="120px">Ukuran</th>
												<th width="100px">Kemasan</th>
												<th width="60px" class="text-right">DTR</th>
												<th width="60px" class="text-right">LPH</th>
												<th width="60px" class="text-right">LPH Saran</th>
												<th width="50px">KD*</th>
												<th width="50px">KD</th>
												<th width="60px" class="text-right">Smin*</th>
												<th width="60px" class="text-right">Smax*</th>
												<th width="60px" class="text-right">Smin</th>
												<th width="60px" class="text-right">Smax</th>
												<th width="60px" class="text-right">Srmin*</th>
												<th width="60px" class="text-right">Srmax*</th>
												<th width="60px" class="text-right">Srmin</th>
												<th width="60px" class="text-right">Srmax</th>
												<th width="60px" class="text-right">H.Ksg</th>
												<th width="60px" class="text-right">Stok</th>
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

	<!-- Modal Edit LPH/DTR -->
	<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Edit LPH / DTR</h5>
					<button type="button" class="close" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<input type="hidden" id="edit_kd_brg">

					<div class="form-group">
						<label>Kode Barang</label>
						<input type="text" class="form-control" id="edit_kd_brg_display" readonly>
					</div>

					<div class="form-group">
						<label>Nama Barang</label>
						<input type="text" class="form-control" id="edit_na_brg" readonly>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>LPH <span class="text-danger">*</span></label>
								<input type="number" class="form-control text-right" id="edit_lph" step="0.01" min="0">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>DTR</label>
								<input type="number" class="form-control text-right" id="edit_dtr" step="1" min="0">
							</div>
						</div>
					</div>

					<div id="calculated_values" style="display:none;">
						<hr>
						<h6>Hasil Perhitungan:</h6>
						<div class="row">
							<div class="col-md-6">
								<small><strong>SMIN:</strong> <span id="calc_smin">0</span></small><br>
								<small><strong>SMAX:</strong> <span id="calc_smax">0</span></small><br>
								<small><strong>KDLAKU:</strong> <span id="calc_kdlaku">-</span></small>
							</div>
							<div class="col-md-6">
								<small><strong>SRMIN:</strong> <span id="calc_srmin">0</span></small><br>
								<small><strong>SRMAX:</strong> <span id="calc_srmax">0</span></small>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">
						<i class="fas fa-times"></i> Batal
					</button>
					<button type="button" class="btn btn-primary" id="btnSaveEdit">
						<i class="fas fa-save"></i> Simpan
					</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		var table;
		var currentSub = '{{ $sub }}';
		var currentPer = '{{ $periode }}';
		var currentKet = '{{ $ket }}';
		var currentCbg = '{{ $outlet }}';

		$(document).ready(function() {
			// Initialize DataTable
			table = $('#tableData').DataTable({
				ajax: {
					url: '{{ route('usulanlphperiode_detail', ':sub') }}'.replace(':sub', currentSub),
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						per: currentPer,
						ket: currentKet,
						cbg: currentCbg
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
						data: 'sub'
					},
					{
						data: 'na_brg'
					},
					{
						data: 'ket_uk'
					},
					{
						data: 'ket_kem'
					},
					{
						data: 'dtr',
						className: 'text-right editable-cell',
						render: function(data) {
							return data ? parseFloat(data).toFixed(0) : '0';
						}
					},
					{
						data: 'lph',
						className: 'text-right editable-cell',
						render: function(data) {
							return data ? parseFloat(data).toFixed(2) : '0.00';
						}
					},
					{
						data: 'lph_saran',
						className: 'text-right',
						render: function(data) {
							return data ? parseFloat(data).toFixed(2) : '0.00';
						}
					},
					{
						data: 'kdlakulm',
						className: 'text-center'
					},
					{
						data: 'kdlaku',
						className: 'text-center'
					},
					{
						data: 'sminlm',
						className: 'text-right',
						render: function(data) {
							return data ? parseFloat(data).toFixed(0) : '0';
						}
					},
					{
						data: 'smaxlm',
						className: 'text-right',
						render: function(data) {
							return data ? parseFloat(data).toFixed(0) : '0';
						}
					},
					{
						data: 'smin',
						className: 'text-right',
						render: function(data) {
							return data ? parseFloat(data).toFixed(0) : '0';
						}
					},
					{
						data: 'smax',
						className: 'text-right',
						render: function(data) {
							return data ? parseFloat(data).toFixed(0) : '0';
						}
					},
					{
						data: 'srminlm',
						className: 'text-right',
						render: function(data) {
							return data ? parseFloat(data).toFixed(0) : '0';
						}
					},
					{
						data: 'srmaxlm',
						className: 'text-right',
						render: function(data) {
							return data ? parseFloat(data).toFixed(0) : '0';
						}
					},
					{
						data: 'srmin',
						className: 'text-right',
						render: function(data) {
							return data ? parseFloat(data).toFixed(0) : '0';
						}
					},
					{
						data: 'srmax',
						className: 'text-right',
						render: function(data) {
							return data ? parseFloat(data).toFixed(0) : '0';
						}
					},
					{
						data: 'kosong',
						className: 'text-right',
						render: function(data) {
							return data ? parseFloat(data).toFixed(0) : '0';
						}
					},
					{
						data: 'stock',
						className: 'text-right',
						render: function(data) {
							return data ? parseFloat(data).toFixed(0) : '0';
						}
					}
				],
				order: [
					[1, 'asc']
				],
				processing: true,
				pageLength: 25
			});

			// Click on editable cell (LPH or DTR)
			$('#tableData').on('click', '.editable-cell', function() {
				var cell = table.cell(this);
				var data = table.row($(this).closest('tr')).data();

				$('#edit_kd_brg').val(data.kd_brg);
				$('#edit_kd_brg_display').val(data.kd_brg);
				$('#edit_na_brg').val(data.na_brg);
				$('#edit_lph').val(parseFloat(data.lph || 0).toFixed(2));
				$('#edit_dtr').val(parseInt(data.dtr || 0));
				$('#calculated_values').hide();

				$('#modalEdit').modal('show');
			});

			// Button Back
			$('#btnBack').on('click', function() {
				window.location.href = '{{ route('usulanlphperiode') }}';
			});

			// Button Save Edit
			$('#btnSaveEdit').on('click', function() {
				var kd_brg = $('#edit_kd_brg').val();
				var lph = parseFloat($('#edit_lph').val()) || 0;
				var dtr = parseInt($('#edit_dtr').val()) || 0;

				if (lph <= 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Validasi',
						text: 'LPH harus lebih dari 0'
					});
					return;
				}

				saveEdit(kd_brg, lph, dtr);
			});
		});

		function saveEdit(kd_brg, lph, dtr) {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('usulanlphperiode_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'update_lph',
					kd_brg: kd_brg,
					lph: lph,
					dtr: dtr,
					sub: currentSub,
					per: currentPer,
					ket: currentKet,
					cbg: currentCbg
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						// Show calculated values
						if (response.data) {
							$('#calc_smin').text(response.data.smin || '0');
							$('#calc_smax').text(response.data.smax || '0');
							$('#calc_srmin').text(response.data.srmin || '0');
							$('#calc_srmax').text(response.data.srmax || '0');
							$('#calc_kdlaku').text(response.data.kdlaku || '-');
							$('#calculated_values').show();
						}

						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 1500,
							showConfirmButton: false
						});

						setTimeout(function() {
							$('#modalEdit').modal('hide');
							table.ajax.reload();
						}, 1500);
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();

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
