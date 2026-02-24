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

	.btn-ambil {
		background: #28a745;
		border: none;
		color: #fff;
		font-weight: 600;
		padding: 10px 25px;
		font-size: 15px;
	}

	.btn-ambil:hover {
		background: #218838;
		color: #fff;
	}

	.btn-ambil:disabled {
		background: #6c757d;
		cursor: not-allowed;
	}

	.btn-proses {
		background: #007bff;
		border: none;
		color: #fff;
		font-weight: 600;
		padding: 10px 25px;
		font-size: 15px;
	}

	.btn-proses:hover {
		background: #0056b3;
		color: #fff;
	}

	.table thead th {
		background: #343a40;
		color: white;
		border: none;
		font-size: 12px;
		padding: 10px 6px;
		text-align: center;
		vertical-align: middle;
	}

	.table tbody tr:hover {
		background-color: #f8f9fa;
	}

	.table tbody td {
		padding: 6px;
		font-size: 12px;
		vertical-align: middle;
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

	.text-right-col {
		text-align: right !important;
	}

	.text-center-col {
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

	.form-section {
		background: #f8f9fa;
		border: 1px solid #dee2e6;
		padding: 20px;
		border-radius: 5px;
		margin-bottom: 20px;
	}

	.badge-cbg {
		font-size: 16px;
		padding: 8px 15px;
		border-radius: 5px;
	}

	.table-wrapper {
		overflow-x: auto;
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
				<div class="col-sm-6 text-right">
					@if (isset($cbg))
					<span class="badge badge-cbg badge-primary">CBG: {{ $cbg ?? '-' }}</span>
					<span class="badge badge-cbg badge-info">Periode: {{ $periode ?? '-' }}</span>
					@endif
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
									<li>Pilih <strong>Cabang</strong> yang akan diambil ordernya</li>
									<li>Masukkan <strong>Sub Barang</strong> (3 digit kode barang)</li>
									<li>Klik <strong>AMBIL</strong> untuk menampilkan data order</li>
									<li>Klik <strong>PROSES</strong> untuk memproses pemesanan</li>
								</ul>
							</div>

							<!-- Filter Section -->
							<div class="form-section">
								<div class="row">
									<div class="col-md-3">
										<label for="txtCbg"><strong>Cabang:</strong></label>
										<select class="form-control" id="txtCbg">
											<option value="">-- Pilih Cabang --</option>
											@if (isset($cabangList))
											@foreach ($cabangList as $cbgItem)
											<option value="{{ $cbgItem->KODE }}" {{ isset($cbg) && $cbg == $cbgItem->KODE ? 'selected' : '' }}>
												{{ $cbgItem->NA_TOKO }}
											</option>
											@endforeach
											@endif
										</select>
									</div>
									<div class="col-md-3">
										<label for="txtSub">SUB</label>
										<select name="txtSub" id="txtSub" class="form-control" required>
											<option value="">Pilih SUB</option>
											<!-- Options akan dimuat via AJAX berdasarkan cabang -->
										</select>
									</div>
									<div class="col-md-6" style="padding-top: 32px;">
										<button type="button" id="btnAmbil" class="btn btn-ambil">
											<i class="fas fa-download"></i> AMBIL
										</button>
										<button type="button" id="btnProses" class="btn btn-proses">
											<i class="fas fa-cogs"></i> PROSES
										</button>
										<a onclick="openPrintPage()">
											<button type="button" id="btnCetak" class="btn btn-cetak">
												<i class="fas fa-print"></i> Print
											</button>
										</a>
										<button type="button" id="" class="btn btn-ambil" onclick="tableOrder.button( '.buttons-excel' ).trigger()">
											<i class="fas fa-print"></i> Excel
										</button>

									</div>
								</div>
							</div>

							<hr>

							<!-- Table Section -->
							<div class="table-wrapper mt-3">
								<table class="table-striped table-bordered table-hover table" id="tableOrder" style="width:100%">
									<thead>
										<tr>
											<th width="40px">No</th>
											<th width="100px">Tgl Order</th>
											<th width="100px">Kd Barang</th>
											<th width="250px">Nama Barang</th>
											<th width="80px">Ukuran</th>
											<th width="100px">Kemasan</th>
											<th width="80px">Qty</th>
											<th width="80px">Supp</th>
											<th width="80px">CBG</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td colspan="9" class="text-center">Klik tombol AMBIL untuk menampilkan data order</td>
										</tr>
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
	var tableOrder;
	var dataOrder = [];
	$(document).ready(function() {
		// Load SUB options when cabang is selected
		$('#txtCbg').on('change', function() {
			var cbg = $(this).val();
			var subSelect = $('#txtSub');

			subSelect.empty().append('<option value="">Loading...</option>');

			if (cbg) {
				$.ajax({
					url: "{{ url('/get-sub-list') }}/" + cbg,
					type: 'GET',
					success: function(response) {
						subSelect.empty().append('<option value="">Pilih SUB</option>');
						if (response && response.length > 0) {
							$.each(response, function(index, item) {
								var selected = "{{ session()->get('filter_sub') }}" == item.SUB ? 'selected' : '';
								subSelect.append('<option value="' + item.SUB + '" ' + selected + '>' + item.SUB +
									'</option>');
							});
						} else {
							subSelect.append('<option value="">Tidak ada SUB tersedia</option>');
						}
					},
					error: function(xhr, status, error) {
						subSelect.empty().append('<option value="">Error loading SUB</option>');
						console.error('Error loading SUB:', error);
					}
				});
			} else {
				subSelect.empty().append('<option value="">Pilih SUB</option>');
			}
		});

		// Trigger change event if cbg has value on page load
		if ($('#txtCbg').val()) {
			$('#txtCbg').trigger('change');
		}

		// Auto-focus on SUB when cabang is selected
		$('#txtCbg').on('change', function() {
			if ($(this).val()) {
				setTimeout(function() {
					$('#txtSub').focus();
				}, 500);
			}
		});



		// Enter key handling
		$('#txtSub').on('keypress', function(e) {
			if (e.which == 13) { // Enter key
				e.preventDefault();
				if (validateForm()) {
					$(this).closest('form').find('button[name="action"][value="filter"]').click();
				}
			}
		});

		// Initialize DataTable
		initTable();

		// Button Ambil
		$('#btnAmbil').on('click', function() {
			ambilData();
		});

		// Button Proses
		$('#btnProses').on('click', function() {
			prosesData();
		});

		// Enter key on Sub input
		$('#txtSub').on('keypress', function(e) {
			if (e.which === 13) {
				ambilData();
			}
		});
	});

	function openPrintPage() {
		var cbg = $('#txtCbg').val();
		var sub = $('#txtSub').val();

		if (!cbg || !sub) {
			Swal.fire({
				icon: 'warning',
				title: 'Perhatian',
				text: 'Cabang dan Sub harus diisi!'
			});
			return;
		}

		var base = "{{ route('ambilorderkode3_print') }}";
		var url = base + "?cbg=" + encodeURIComponent(cbg) + "&sub=" + encodeURIComponent(sub);

		window.open(url, "_blank");
	}

	function initTable() {
		tableOrder = $('#tableOrder').DataTable({
			data: [],
			columns: [{
					data: null,
					render: function(data, type, row, meta) {
						return meta.row + 1;
					},
					className: 'text-center'
				},
				{
					data: 'tgl_order',
					className: 'text-center',
					render: function(data) {
						return data ? formatDate(data) : '-';
					}
				},
				{
					data: 'kd_brg',
					className: 'text-center'
				},
				{
					data: 'na_brg'
				},
				{
					data: 'ket_uk',
					className: 'text-center'
				},
				{
					data: 'ket_kem',
					className: 'text-center'
				},
				{
					data: 'qty',
					className: 'text-right',
					render: function(data) {
						return formatNumber(data, 2);
					}
				},
				{
					data: 'kodes',
					className: 'text-center'
				},
				{
					data: 'CBG',
					className: 'text-center'
				}
			],
			paging: true,
			pageLength: 50,
			searching: true,
			ordering: true,
			info: true,
			scrollX: true,
			dom: 'Blfrtip',
			buttons: [],

		});

	}

	function ambilData() {
		var cbg = $('#txtCbg').val();
		var sub = $('#txtSub').val().trim();

		if (!cbg) {
			Swal.fire({
				icon: 'warning',
				title: 'Perhatian',
				text: 'Pilih cabang terlebih dahulu!'
			});
			$('#txtCbg').focus();
			return;
		}

		if (!sub) {
			Swal.fire({
				icon: 'warning',
				title: 'Perhatian',
				text: 'Sub barang harus diisi!'
			});
			$('#txtSub').focus();
			return;
		}

		if (sub.length !== 3) {
			Swal.fire({
				icon: 'warning',
				title: 'Perhatian',
				text: 'Sub barang harus 3 digit!'
			});
			$('#txtSub').focus();
			return;
		}

		$('#LOADX').show();
		$('#btnAmbil').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> LOADING...');

		$.ajax({
			url: "{{ route('ambilorderkode3_cari') }}",
			type: 'POST',
			data: {
				_token: '{{ csrf_token() }}',
				cbg: cbg,
				sub: sub
			},
			success: function(response) {
				$('#LOADX').hide();
				$('#btnAmbil').prop('disabled', false).html('<i class="fas fa-download"></i> AMBIL');

				if (response.success && response.data.length > 0) {
					dataOrder = response.data;

					tableOrder.clear();
					tableOrder.rows.add(response.data);
					tableOrder.buttons().destroy();
					tableOrder.button().add(0, {
						extend: 'collection',
						text: 'Export',
						buttons: ['copy', 'excel', 'csv', 'pdf', 'print']
					});



					tableOrder.draw();

					Swal.fire({
						icon: 'success',
						title: 'Berhasil',
						text: 'Data berhasil dimuat: ' + response.count + ' item',
						timer: 2000,
						showConfirmButton: false
					});
				} else {
					dataOrder = [];
					tableOrder.clear().draw();
					Swal.fire({
						icon: 'info',
						title: 'Informasi',
						text: 'Tidak ada data order untuk sub barang ini'
					});
				}
			},
			error: function(xhr) {
				$('#LOADX').hide();
				$('#btnAmbil').prop('disabled', false).html('<i class="fas fa-download"></i> AMBIL');
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: xhr.responseJSON?.error || 'Gagal mengambil data'
				});
			}
		});
	}

	function prosesData() {
		var cbg = $('#txtCbg').val();
		var sub = $('#txtSub').val().trim();

		if (!cbg) {
			Swal.fire({
				icon: 'warning',
				title: 'Perhatian',
				text: 'Pilih cabang terlebih dahulu!'
			});
			$('#txtCbg').focus();
			return;
		}

		if (!sub) {
			Swal.fire({
				icon: 'warning',
				title: 'Perhatian',
				text: 'Sub barang harus diisi!'
			});
			$('#txtSub').focus();
			return;
		}

		Swal.fire({
			title: 'Konfirmasi Proses',
			html: 'Apakah anda ingin memproses pemesanan untuk sub <strong>' + sub + '</strong> cabang <strong>' + cbg + '</strong>?',
			icon: 'question',
			showCancelButton: true,
			confirmButtonColor: '#007bff',
			cancelButtonColor: '#6c757d',
			confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses!',
			cancelButtonText: '<i class="fas fa-times"></i> Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				$('#LOADX').show();
				$('#btnProses').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');

				$.ajax({
					url: "{{ route('ambilorderkode3_proses') }}",
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						cbg: cbg,
						sub: sub
					},
					success: function(response) {
						$('#LOADX').hide();
						$('#btnProses').prop('disabled', false).html('<i class="fas fa-cogs"></i> PROSES');

						if (response.success) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								html: response.message,
								showConfirmButton: true
							}).then(() => {
								// Refresh data
								ambilData();
							});
						}
					},
					error: function(xhr) {
						$('#LOADX').hide();
						$('#btnProses').prop('disabled', false).html('<i class="fas fa-cogs"></i> PROSES');
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: xhr.responseJSON?.error || 'Proses gagal'
						});
					}
				});
			}
		});
	}

	function formatNumber(num, decimals) {
		var n = parseFloat(num);
		if (isNaN(n)) return '0';

		return n.toLocaleString('id-ID', {
			minimumFractionDigits: decimals,
			maximumFractionDigits: decimals
		});
	}

	function formatDate(dateStr) {
		if (!dateStr) return '-';

		try {
			var date = new Date(dateStr);
			var day = ('0' + date.getDate()).slice(-2);
			var month = ('0' + (date.getMonth() + 1)).slice(-2);
			var year = date.getFullYear();
			return day + '/' + month + '/' + year;
		} catch (e) {
			return dateStr;
		}
	}
</script>
@endsection