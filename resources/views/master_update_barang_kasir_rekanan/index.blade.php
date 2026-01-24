@extends('layouts.plain')
@section('styles')
	<link rel="stylesheet" href="{{ url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
	<link rel="stylesheet" href="{{ url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
	<!-- Select2 -->
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

<style>
	th {
		font-size: 13px;
	}

	td {
		font-size: 13px;
	}
</style>

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Master Usulan Barang Kasir Rekanan</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Master Usulan Barang Kasir Rekanan</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<!-- Status -->
		@if (session('status'))
			<div class="alert alert-success">
				{{ session('status') }}
			</div>
		@endif

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">

								<!-- Filter Sub -->
								<div class="form-group row" style="padding-left:20px">
									<label><strong>Rekanan:</strong></label>
									<div class="col-md-2">
										<select name="rekan" id="rekan" class="form-control" required>
											<option value="">Pilih Rekanan</option>
											@foreach ($rekan as $rekan)
												<option value="{{ $rekan->NAMA }}" {{ session()->get('filter_rekan') == $rekan->NAMA ? 'selected' : '' }}>
													{{ $rekan->NAMA }}
												</option>
											@endforeach
										</select>
									</div>
									<!-- CEK -->
									<div class="col-md-1" align="right">
										<input type="checkbox" class="form-check-input" id="CEK" name="CEK" value="1">
										<label for="CEK">Cek</label>
									</div>
									<div class="col-md-1">
										<input type="checkbox" class="form-check-input" id="CEK" name="CEK" value="0">
										<label for="CEK">UnChek</label>
									</div>
								</div>

								<div class="form-group row" style="padding-left:20px">
									<label><strong>Sub Item:</strong></label>
									<div class="col-md-4">
										<select class="form-control" id="SUB" name="SUB" style="width: 100%;">
											<option value="">Pilih Kode Barang...</option>
										</select>
									</div>
									<div class="col-md-1">
										<button type="button" class="btn btn-warning" id="btnTampil" style="white-space: nowrap;">
											Tampilkan
										</button>
									</div>
									<div class="col-md-1">
										<button type="button" class="btn btn-danger" id="btnProses" style="white-space: nowrap;">
											Prosses
										</button>
									</div>
								</div>
								<!--  -->

								<div class="form-group row" style="padding-left:20px">
									<label><strong>Copy Data Rekan:</strong></label>
									<div class="col-md-2">
										<select name="copy" id="copy" class="form-control" required>
											<option value="">Pilih Rekanan</option>
											@foreach ($copy as $copy)
												<option value="{{ $copy->NAMA }}" {{ session()->get('filter_copy') == $copy->NAMA ? 'selected' : '' }}>
													{{ $copy->NAMA }}
												</option>
											@endforeach
										</select>
									</div>

									<div class="col-md-1">
										<button type="button" class="btn btn-primary" id="btnCopy" style="white-space: nowrap;">
											Ambil Data
										</button>
									</div>

								</div>

								<table class="table-striped table-border table-hover nowrap datatable table table-fixed" id="datatable">
									<thead class="table-dark">
										<tr>

											<th scope="col" style="text-align: center">No</th>
											<th scope="col" style="text-align: center">-</th>
											<th scope="col" style="text-align: center">Sub Item</th>
											<th scope="col" style="text-align: center">Nama Barang</th>
											<th scope="col" style="text-align: center">Kemasan</th>
											<th scope="col" style="text-align: center">HB</th>
											<th scope="col" style="text-align: center">Jual</th>
											<th scope="col" style="text-align: center">Cek</th>
										</tr>
									</thead>

									<tbody>

									</tbody>
								</table>
							</div>
						</div>
						<!-- /.card -->
					</div>
				</div>
				<!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>
		<!-- /.content -->
	</div>
	<!-- /.content-wrapper -->
@endsection

@section('javascripts')
	<!-- Select2 JS -->
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

	<script>
		$(document).ready(function() {
			// Initialize Select2 untuk Sub Item (Kode Barang)
			$('#SUB').select2({
				theme: 'bootstrap-5',
				placeholder: 'Ketik untuk mencari kode barang...',
				allowClear: true,
				ajax: {
					url: '{{ route('search-barang') }}',
					dataType: 'json',
					delay: 250,
					data: function(params) {
						return {
							q: params.term,
							page: params.page || 1
						};
					},
					processResults: function(data) {
						return {
							results: data.results,
							pagination: {
								more: data.pagination.more
							}
						};
					},
					cache: true
				},
				minimumInputLength: 0,
				language: {
					inputTooShort: function() {
						return 'Ketik minimal 1 karakter untuk mencari';
					},
					searching: function() {
						return 'Mencari...';
					},
					noResults: function() {
						return 'Tidak ada hasil ditemukan';
					}
				}
			});

			var dataTable = $('.datatable').DataTable({
				processing: true,
				serverSide: true,
				autoWidth: true,
				'scrollY': '400px',
				"order": [
					[0, "asc"]
				],
				ajax: {
					url: '{{ route('get-usl-brg-rekanan') }}',
					data: function(d) {
						d.SUB = $('#SUB').val();
						d.rekanan = $('#rekan').val();
					}
				},
				columns: [{
						data: 'DT_RowIndex',
						orderable: false,
						searchable: false
					},
					{
						data: 'action',
						name: 'action'
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
						data: 'KET_KEM',
						name: 'KET_KEM'
					},
					{
						data: 'HB',
						name: 'HB'
					},
					{
						data: 'JUAL',
						name: 'JUAL'
					},
					{
						data: 'KD_BRG',
						name: 'CEK',
						orderable: false,
						searchable: false,
						render: function(data, type, row) {
							return '<input type="checkbox" class="form-check-input item-checkbox" name="cek_item[]" value="' + data +
								'"' + (row.JUAL == 1 ? ' checked' : '') + '>';
						}
					},
				],

				columnDefs: [{
					"className": "dt-center",
					"targets": 0
				}],

				dom: "<'row'<'col-md-6'><'col-md-6'>>" +
					"<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
					"<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",

				stateSave: true,

			});

			// Button Tampilkan - Reload DataTable
			$('#btnTampil').on('click', function() {
				var rekanan = $('#rekan').val();
				var sub = $('#SUB').val();

				if (!rekanan) {
					Swal.fire({
						icon: 'warning',
						title: 'Peringatan',
						text: 'Silakan pilih rekanan terlebih dahulu!'
					});
					return;
				}

				if (!sub) {
					Swal.fire({
						icon: 'warning',
						title: 'Peringatan',
						text: 'Silakan masukkan kode barang terlebih dahulu!'
					});
					return;
				}

				Swal.fire({
					title: 'Memuat data...',
					text: 'Mohon tunggu sebentar',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				dataTable.ajax.reload(function() {
					Swal.close();
				});
			});

			// Button Proses
			$('#btnProses').on('click', function() {
				var rekanan = $('#rekan').val();
				var checkedItems = [];

				$('.item-checkbox:checked').each(function() {
					checkedItems.push($(this).val());
				});

				if (!rekanan) {
					Swal.fire({
						icon: 'warning',
						title: 'Peringatan',
						text: 'Silakan pilih rekanan terlebih dahulu!'
					});
					return;
				}

				if (checkedItems.length === 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Peringatan',
						text: 'Silakan pilih minimal 1 barang untuk diproses!'
					});
					return;
				}

				Swal.fire({
					title: 'Konfirmasi',
					text: 'Proses ' + checkedItems.length + ' barang untuk rekanan ' + rekanan + '?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Ya, Proses!',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						Swal.fire({
							title: 'Memproses data...',
							text: 'Mohon tunggu, sedang memproses ' + checkedItems.length + ' item',
							allowOutsideClick: false,
							allowEscapeKey: false,
							didOpen: () => {
								Swal.showLoading();
							}
						});

						$.ajax({
							url: '{{ route('usl-brg-rekanan.proses') }}',
							type: 'POST',
							data: {
								_token: '{{ csrf_token() }}',
								rekanan: rekanan,
								kd_brg_array: checkedItems
							},
							success: function(response) {
								Swal.fire({
									icon: 'success',
									title: 'Berhasil!',
									text: response.message,
									timer: 3000
								}).then(() => {
									dataTable.ajax.reload();
								});
							},
							error: function(xhr) {
								var errorMsg = 'Terjadi kesalahan saat memproses data';
								if (xhr.responseJSON && xhr.responseJSON.message) {
									errorMsg = xhr.responseJSON.message;
								}
								Swal.fire({
									icon: 'error',
									title: 'Error!',
									text: errorMsg
								});
							}
						});
					}
				});
			});

			// Button Ambil Data (Copy)
			$('#btnCopy').on('click', function() {
				var fromRekanan = $('#copy').val();
				var toRekanan = $('#rekan').val();

				if (!fromRekanan) {
					Swal.fire({
						icon: 'warning',
						title: 'Peringatan',
						text: 'Silakan pilih rekanan sumber data!'
					});
					return;
				}

				if (!toRekanan) {
					Swal.fire({
						icon: 'warning',
						title: 'Peringatan',
						text: 'Silakan pilih rekanan tujuan!'
					});
					return;
				}

				if (fromRekanan === toRekanan) {
					Swal.fire({
						icon: 'warning',
						title: 'Peringatan',
						text: 'Rekanan sumber dan tujuan tidak boleh sama!'
					});
					return;
				}

				Swal.fire({
					title: 'Konfirmasi',
					html: 'Copy data dari <strong>' + fromRekanan + '</strong> ke <strong>' + toRekanan + '</strong>?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Ya, Copy!',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						Swal.fire({
							title: 'Meng-copy data...',
							text: 'Mohon tunggu, sedang menyalin data',
							allowOutsideClick: false,
							allowEscapeKey: false,
							didOpen: () => {
								Swal.showLoading();
							}
						});

						$.ajax({
							url: '{{ route('usl-brg-rekanan.copy') }}',
							type: 'POST',
							data: {
								_token: '{{ csrf_token() }}',
								from_rekanan: fromRekanan,
								to_rekanan: toRekanan
							},
							success: function(response) {
								Swal.fire({
									icon: response.status === 'warning' ? 'warning' : 'success',
									title: response.status === 'warning' ? 'Perhatian!' : 'Berhasil!',
									text: response.message,
									timer: 3000
								}).then(() => {
									dataTable.ajax.reload();
								});
							},
							error: function(xhr) {
								var errorMsg = 'Terjadi kesalahan saat meng-copy data';
								if (xhr.responseJSON && xhr.responseJSON.message) {
									errorMsg = xhr.responseJSON.message;
								}
								Swal.fire({
									icon: 'error',
									title: 'Error!',
									text: errorMsg
								});
							}
						});
					}
				});
			});

			// Checkbox Cek Semua
			$('#CEK[value="1"]').on('change', function() {
				if ($(this).is(':checked')) {
					$('.item-checkbox').prop('checked', true);
					$('#CEK[value="0"]').prop('checked', false);
				}
			});

			// Checkbox UnCek Semua
			$('#CEK[value="0"]').on('change', function() {
				if ($(this).is(':checked')) {
					$('.item-checkbox').prop('checked', false);
					$('#CEK[value="1"]').prop('checked', false);
				}
			});

			// Session message notification
			@if (session('status'))
				Swal.fire({
					icon: 'success',
					title: 'Berhasil!',
					text: '{{ session('status') }}',
					timer: 3000,
					showConfirmButton: false
				});
			@endif

			@if (session('statusInsert'))
				Swal.fire({
					icon: 'success',
					title: 'Berhasil!',
					text: '{{ session('statusInsert') }}',
					timer: 3000,
					showConfirmButton: false
				});
			@endif

		});
	</script>
@endsection
