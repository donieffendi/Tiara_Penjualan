@extends('layouts.plain')

@section('styles')
<link rel="stylesheet" href="{{url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
{{-- <link rel="stylesheet" href="{{url('https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css') }}"> --}}

@endsection

<script src="{{url('AdminLTE/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{url('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>
<script src="{{url('http://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js') }}"></script>
<!-- Buttons dan Export -->
<script src="{{url('https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js') }}"></script>
<script src="{{url('https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js') }}"></script>
<script src="{{url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js') }}"></script>

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Kartu Stok</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="#">Report</a></li>
							<li class="breadcrumb-item active">Kartu Stok</li>
						</ol>
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
								<form method="GET" action="{{ route('get-kartustok-report') }}" id="kartuForm">
									@csrf
									<div class="row mb-3">
										<div class="col-md-3">
											<label for="cbg">Cabang <span class="text-danger">*</span></label>
											<select name="cbg" id="cbg" class="form-control" required>
												<option value="">-- Pilih Cabang --</option>
												@foreach ($cbg as $cabang)
													<option value="{{ $cabang->CBG }}" {{ session()->get('filter_cbg') == $cabang->CBG ? 'selected' : '' }}>
														{{ $cabang->CBG }}
													</option>
												@endforeach
											</select>
										</div>

										<div class="col-md-3">
											<label for="periode">Periode (MM-YYYY) <span class="text-danger">*</span></label>
											<input type="text" name="periode" id="periode" class="form-control" placeholder="MM-YYYY" pattern="\d{2}-\d{4}"
												value="{{ session()->get('filter_per', date('m-Y')) }}" required>
										</div>

										<div class="col-md-3">
											<label for="kd_brg">Kode Barang <span class="text-danger">*</span></label>
											<div class="input-group">
												<input type="text" name="kd_brg" id="kd_brg" class="form-control" placeholder="Kode Barang"
													value="{{ session()->get('filter_kd_brg') }}" required>
												<div class="input-group-append">
													<button type="button" class="btn btn-outline-secondary" id="btnBrowse" title="Browse Barang">
														<i class="fas fa-search"></i>
													</button>
												</div>
											</div>
										</div>

										<div class="col-md-3">
											<label>&nbsp;</label>
											<div>
												<button type="submit" class="btn btn-primary mr-1" id="btnProses">
													<i class="fas fa-search"></i> Proses
												</button>
												<button type="button" class="btn btn-danger mr-1" onclick="resetForm()">
													<i class="fas fa-undo"></i> Reset
												</button>
												<button type="submit" class="btn btn-success" formaction="{{ url('jasper-kartustok-report') }}" formmethod="POST" formtarget="_blank">
													<i class="fas fa-print"></i> Cetak
												</button>
											</div>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-md-12">
											<label>Jenis Stock:</label>
											<div class="form-check form-check-inline ml-3">
												<input class="form-check-input" type="radio" name="jenis" id="radioToko" value="toko"
													{{ session()->get('filter_jenis', 'toko') == 'toko' ? 'checked' : '' }}>
												<label class="form-check-label" for="radioToko">
													Toko
												</label>
											</div>
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="jenis" id="radioGudang" value="gudang"
													{{ session()->get('filter_jenis') == 'gudang' ? 'checked' : '' }}>
												<label class="form-check-label" for="radioGudang">
													Gudang
												</label>
											</div>
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="jenis" id="radioRetur" value="retur"
													{{ session()->get('filter_jenis') == 'retur' ? 'checked' : '' }}>
												<label class="form-check-label" for="radioRetur">
													Retur
												</label>
											</div>
										</div>
									</div>

									@if (session()->get('filter_cbg') && session()->get('filter_per') && session()->get('filter_kd_brg'))
										<div class="alert alert-info">
											<strong><i class="fas fa-info-circle"></i> Filter Aktif:</strong>
											Cabang: <strong>{{ session()->get('filter_cbg') }}</strong> |
											Periode: <strong>{{ session()->get('filter_per') }}</strong> |
											Kode Barang: <strong>{{ session()->get('filter_kd_brg') }}</strong> |
											Jenis: <strong>{{ ucfirst(session()->get('filter_jenis', 'toko')) }}</strong>
										</div>
									@endif
								</form>

								<hr>

								<div class="table-responsive">
									@if (isset($hasilKartu) && count($hasilKartu) > 0)
										<table class="table-bordered table-striped table-hover table" id="kartuStokTable">
											<thead>
												<tr>
													<th>No Bukti</th>
													<th>Tanggal</th>
													<th>Kode</th>
													<th>Nama</th>
													<th class="text-right">Awal</th>
													<th class="text-right">Masuk</th>
													<th class="text-right">Keluar</th>
													<th class="text-right">Lain</th>
													<th class="text-right">Saldo</th>
													<th class="text-center">Flag</th>
												</tr>
											</thead>
											<tbody>
												@php
													$totalAwal = 0;
													$totalMasuk = 0;
													$totalKeluar = 0;
													$totalLain = 0;
													$saldoAkhir = 0;
												@endphp
												@foreach ($hasilKartu as $item)
													@php
														$totalAwal += $item->awal ?? 0;
														$totalMasuk += $item->masuk ?? 0;
														$totalKeluar += $item->keluar ?? 0;
														$totalLain += $item->LAIN ?? 0;
														$saldoAkhir = $item->SALDO ?? 0;
													@endphp
													<tr>
														<td>{{ $item->no_bukti }}</td>
														<td class="text-center">{{ $item->tgl ? date('d/m/Y', strtotime($item->tgl)) : '' }}</td>
														<td>{{ $item->kd_brg }}</td>
														<td>{{ $item->NA_BRG }}</td>
														<td class="text-right">{{ number_format($item->awal ?? 0, 2, '.', ',') }}</td>
														<td class="text-right">{{ number_format($item->masuk ?? 0, 2, '.', ',') }}</td>
														<td class="text-right">{{ number_format($item->keluar ?? 0, 2, '.', ',') }}</td>
														<td class="text-right">{{ number_format($item->LAIN ?? 0, 2, '.', ',') }}</td>
														<td class="text-right"><strong>{{ number_format($item->SALDO ?? 0, 2, '.', ',') }}</strong></td>
														<td class="text-center">{{ $item->FLAG }}</td>
													</tr>
												@endforeach
											</tbody>
											<tfoot>
												<tr class="bg-light font-weight-bold">
													<td colspan="4" class="text-right">TOTAL:</td>
													<td class="text-right">{{ number_format($totalAwal, 2, '.', ',') }}</td>
													<td class="text-right">{{ number_format($totalMasuk, 2, '.', ',') }}</td>
													<td class="text-right">{{ number_format($totalKeluar, 2, '.', ',') }}</td>
													<td class="text-right">{{ number_format($totalLain, 2, '.', ',') }}</td>
													<td class="text-right"><strong>{{ number_format($saldoAkhir, 2, '.', ',') }}</strong></td>
													<td></td>
												</tr>
											</tfoot>
										</table>
									@elseif(request()->has('cbg'))
										<div class="alert alert-warning text-center">
											<i class="fas fa-exclamation-triangle"></i>
											Tidak ada data kartu stok ditemukan untuk filter yang dipilih.
										</div>
									@else
										<div class="alert alert-info text-center">
											<i class="fas fa-info-circle"></i>
											Silakan pilih <strong>Cabang</strong>, <strong>Periode</strong>, dan <strong>Kode Barang</strong> untuk menampilkan data kartu stok.
										</div>
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal Browse Barang -->
	<div class="modal fade" id="browseBarangModal" tabindex="-1" role="dialog" aria-labelledby="browseBarangModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header bg-primary text-white">
					<h5 class="modal-title" id="browseBarangModalLabel">
						<i class="fas fa-search"></i> Browse Barang
					</h5>
					<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<input type="text" id="searchBarang" class="form-control" placeholder="Cari kode atau nama barang...">
					</div>
					<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
						<table class="table-striped table-bordered table-hover table-sm table" id="barangTable">
							<thead class="thead-light sticky-top">
								<tr>
									<th width="30%">Kode Barang</th>
									<th width="50%">Nama Barang</th>
									<th width="20%" class="text-center">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<!-- Data will be loaded via AJAX -->
							</tbody>
						</table>
					</div>
					<div id="loadingBarang" class="text-center" style="display: none;">
						<i class="fas fa-spinner fa-spin fa-2x"></i>
						<p>Memuat data barang...</p>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script src="{{url('AdminLTE/plugins/datatables/jquery.dataTables.js') }}"></script>
	<script src="{{url('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>
	<script src="{{url('http://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js') }}"></script>
	<!-- Buttons dan Export -->
	<script src="{{url('https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js') }}"></script>
	<script src="{{url('https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js') }}"></script>
	<script src="{{url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		$(document).ready(function() {
			// Initialize DataTable if data exists
			@if (isset($hasilKartu) && count($hasilKartu) > 0)
				$('#kartuStokTable').DataTable({
					responsive: true,
					pageLength: 25,
					lengthMenu: [
						[10, 25, 50, 100, -1],
						[10, 25, 50, 100, "Semua"]
					],
                    searching: true,
					order: [
						[1, 'asc']
					],
					dom: "<'row'<'col-md-6'><'col-md-6'>>" +
						"<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
						"<'row'<'col-md-12'B>>" +
						"<'row'<'col-md-12'tr>>" +
						"<'row'<'col-md-5'i><'col-md-7'p>>",
					buttons: [
						{
							extend: 'excelHtml5',
							title: 'Data Galeri'
						}
					],
					language: {
						url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
					}
				});
			@endif

			// Format periode input
			$('#periode').on('input', function() {
				let value = $(this).val().replace(/[^0-9]/g, '');
				if (value.length >= 2) {
					value = value.substring(0, 2) + '-' + value.substring(2, 6);
				}
				$(this).val(value);
			});

			// Validate periode format
			$('#periode').on('blur', function() {
				let value = $(this).val();
				let pattern = /^\d{2}-\d{4}$/;

				if (value && !pattern.test(value)) {
					Swal.fire({
						icon: 'error',
						title: 'Format Salah',
						text: 'Format periode harus MM-YYYY (contoh: 01-2024)',
						confirmButtonColor: '#3085d6'
					});
					$(this).val('');
				}
			});

			// Form validation
			$('#kartuForm').on('submit', function(e) {
				let cbg = $('#cbg').val();
				let periode = $('#periode').val();
				let kdBrg = $('#kd_brg').val();

				if (!cbg) {
					e.preventDefault();
					Swal.fire({
						icon: 'warning',
						title: 'Validasi',
						text: 'Harap pilih cabang terlebih dahulu',
						confirmButtonColor: '#3085d6'
					});
					$('#cbg').focus();
					return false;
				}

				if (!periode) {
					e.preventDefault();
					Swal.fire({
						icon: 'warning',
						title: 'Validasi',
						text: 'Harap masukkan periode dengan format MM-YYYY',
						confirmButtonColor: '#3085d6'
					});
					$('#periode').focus();
					return false;
				}

				if (!kdBrg) {
					e.preventDefault();
					Swal.fire({
						icon: 'warning',
						title: 'Validasi',
						text: 'Harap masukkan kode barang',
						confirmButtonColor: '#3085d6'
					});
					$('#kd_brg').focus();
					return false;
				}
			});

			// Enter key to submit on kode barang
			$('#kd_brg').on('keypress', function(e) {
				if (e.which == 13) {
					e.preventDefault();
					$('#btnProses').click();
				}
			});

			// Auto-focus flow
			$('#cbg').on('change', function() {
				if ($(this).val()) {
					$('#periode').focus();
				}
			});

			$('#periode').on('change', function() {
				if ($(this).val() && $('#cbg').val()) {
					$('#kd_brg').focus();
				}
			});

			// Show success/error messages
			@if (session('success'))
				Swal.fire({
					icon: 'success',
					title: 'Berhasil',
					text: '{{ session('success') }}',
					timer: 3000,
					showConfirmButton: false
				});
			@endif

			@if (session('error'))
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: '{{ session('error') }}',
					confirmButtonColor: '#3085d6'
				});
			@endif

			@if ($errors->any())
				Swal.fire({
					icon: 'error',
					title: 'Validasi Error',
					html: '{!! implode('<br>', $errors->all()) !!}',
					confirmButtonColor: '#3085d6'
				});
			@endif
		});

		// Reset form function
		function resetForm() {
			Swal.fire({
				title: 'Reset Form?',
				text: "Semua filter akan dikosongkan",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Ya, Reset!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					window.location.href = '{{ route("rkartustok.reset") }}';
				}
			});
		}
	</script>
@endsection
