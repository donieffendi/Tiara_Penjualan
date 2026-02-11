@extends('layouts.plain')
@section('styles')
	<link rel="stylesheet" href="{{ url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
	<link rel="stylesheet" href="{{ url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

<style>
	th {
		font-size: 13px;
	}

	td {
		font-size: 13px;
	}

	.badge-warning {
		background-color: #06ba00 !important;
		/* Warna default badge-warning (kuning) */
		color: white !important;
		/* Warna teks putih */
	}
</style>

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Master Usulan Barang Kasir Td</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Master Usulan Barang Kasir Td</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<!-- Status -->
		@if (session('status'))
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					Swal.fire({
						icon: 'success',
						title: 'Berhasil!',
						text: '{{ session('status') }}',
						confirmButtonColor: '#3085d6'
					});
				});
			</script>
		@endif

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">

								<!-- CEK -->
								<div class="form-group row" style="padding-left:30px">
									<div class="col-md-1">
										<input type="checkbox" id="checkAll" class="form-check-input">
										<label for="checkAll">Check</label>
									</div>
									<div class="col-md-1">
										<input type="checkbox" id="uncheckAll" class="form-check-input">
										<label for="uncheckAll">Uncheck</label>
									</div>
								</div>
								<!--  -->

								<!-- Filter Sub -->
								<div class="form-group row" style="padding-left:20px">
									<label><strong>Sub Item:</strong></label>
									<div class="col-md-2">
										<select class="form-control KD_BRG" id="KD_BRG" name="KD_BRG" style="width: 100%;">
											<option value="">-- Pilih Sub --</option>
										</select>
									</div>
									<div class="col-md-4">
										<button type="button" class="btn btn-warning" id="btnFilterSub" style="white-space: nowrap;">
											Tampilkan
										</button>

										<button type="button" class="btn btn-danger" id="btnProses" style="white-space: nowrap;">
											Prosses
										</button>
									</div>
								</div>
								<!--  -->
								<table class="table-striped table-border table-hover nowrap datatable table table-fixed" id="datatable">
									<thead class="table-dark">
										<tr>

											<th scope="col" style="text-align: center">No</th>
											<th scope="col" style="text-align: center">Kode</th>
											<th scope="col" style="text-align: center">Nama</th>
											<th scope="col" style="text-align: center">Kemasan</th>
											<th scope="col" style="text-align: center">HB</th>
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
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		$(document).ready(function() {

			// Initialize Select2 for KD_BRG
			// $('#KD_BRG').select2({
			// 	placeholder: '-- Pilih Sub --',
			// 	allowClear: true,
			// 	ajax: {
			// 		url: '{{ route('get-sub-td') }}',
			// 		dataType: 'json',
			// 		delay: 250,
			// 		data: function(params) {
			// 			return {
			// 				q: params.term // search term
			// 			};
			// 		},
			// 		processResults: function(data) {
			// 			return {
			// 				results: data.map(function(item) {
			// 					return {
			// 						id: item.KD_BRG,
			// 						text: item.KD_BRG + ' - ' + item.KELOMPOK
			// 					};
			// 				})
			// 			};
			// 		},
			// 		cache: true
			// 	}
			// });

			$('#KD_BRG').select2({
				placeholder: '-- Pilih Sub --',
				allowClear: true,
				minimumInputLength: 4, // ⬅️ minimal 4 karakter
				ajax: {
					url: '{{ route('get-sub-td') }}',
					dataType: 'json',
					delay: 250,
					data: function (params) {

						// jangan kirim request kalau belum 4 karakter
						if (!params.term || params.term.length < 4) {
							return false;
						}

						return {
							q: params.term
						};
					},
					processResults: function (data, params) {

						// jika search kosong → tampilkan kosong
						if (!params.term || params.term.length < 4) {
							return { results: [] };
						}

						return {
							results: data.map(function (item) {
								return {
									id: item.KD_BRG,
									text: item.KD_BRG + ' - ' + item.KELOMPOK
								};
							})
						};
					},
					cache: true
				}
			});


			// Set default value if exists
			@if (session()->get('KD_BRG'))
				var defaultSub = '{{ session()->get('KD_BRG') }}';
				var option = new Option(defaultSub, defaultSub, true, true);
				$('#KD_BRG').append(option).trigger('change');
			@endif

			var dataTable = $('.datatable').DataTable({
				processing: true,
				serverSide: true,
				searching: true,
				autoWidth: false,
				paging: false,
				'scrollX': true,
				'scrollY': '400px',
				"order": [
					[0, "asc"]
				],
				ajax: {
					url: '{{ route('get-usl-brg-td') }}',
					data: function(d) {
						d.KD_BRG = $('#KD_BRG').val();
					}
				},
				columns: [{
						data: 'DT_RowIndex',
						orderable: false,
						searchable: false
					},
					{
						data: 'KD_BRG',
						name: 'KD_BRG'
					},
					{
						data: 'NA_BRG',
						name: 'NA_BRG',
						render: function(data, type, row, meta) {
							return ' <h5><span class="badge badge-pill badge-warning">' + data + '</span></h5>';
						}
					},
					{
						data: 'KET_KEM',
						name: 'KET_KEM'
					},
					{
						data: 'HB',
						name: 'HB',
						render: $.fn.dataTable.render.number(',', '.', 0, '')
					},
					{
						data: 'JTD',
						name: 'JTD',
						render: function(data, type, row, meta) {
							if (row['JTD'] == "0") {
								return '<input type="checkbox" style="transform: scale(2);">';
							} else {
								return '<input type="checkbox" checked style="transform: scale(2);">';
							}
						}
					},
				],

				columnDefs: [{
						"className": "dt-center",
						"targets": [0, 1, 2, 3, 5]
					},
					{
						"className": "dt-right",
						"targets": 4
					}
				],

				dom: "<'row'<'col-md-6'><'col-md-6'>>" +
					"<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
					"<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",

				stateSave: true,

			});

			$('#KD_BRG').on('keydown', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					$('#btnFilterSub').click();
				}
			});

			// === CHECK ALL ===
			$('#checkAll').on('change', function() {
				const checked = $(this).is(':checked');
				// centang semua checkbox di dalam tabel
				$('#datatable').find('input[type="checkbox"]').prop('checked', checked);
			});

			// === UNCHECK ALL ===
			$('#uncheckAll').on('change', function() {
				const unchecked = $(this).is(':checked');
				if (unchecked) {
					$('#datatable').find('input[type="checkbox"]').prop('checked', false);
					// reset juga checkbox checkAll agar tidak nyala
					$('#checkAll').prop('checked', false);
				}
			});

			// Jika kamu ingin check/uncheck tetap sinkron saat user klik manual di tabel
			$('#datatable').on('change', 'input[type="checkbox"]', function() {
				const total = $('#datatable input[type="checkbox"]').length;
				const checked = $('#datatable input[type="checkbox"]:checked').length;
				// Jika semua checkbox di tabel tercentang, maka checkAll ikut nyala
				$('#checkAll').prop('checked', total === checked);
			});

			// Trigger reload saat nilai filter berubah
			$('#btnFilterSub').on('click', function() {
				// Validasi apakah KD_BRG sudah dipilih
				if (!$('#KD_BRG').val()) {
					Swal.fire({
						icon: 'warning',
						title: 'Peringatan',
						text: 'Silakan pilih Sub Item terlebih dahulu!',
						confirmButtonColor: '#3085d6'
					});
					return;
				}

				// Tampilkan loading
				Swal.fire({
					title: 'Memuat Data...',
					text: 'Mohon tunggu sebentar',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				// Reload datatable
				var table = $('#datatable').DataTable();
				table.ajax.reload(function(json) {
					// Tutup loading dan tampilkan hasil
					Swal.close();

					if (json.data && json.data.length > 0) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil!',
							text: 'Data berhasil dimuat: ' + json.data.length + ' item ditemukan',
							timer: 1500,
							showConfirmButton: false
						});
					} else {
						Swal.fire({
							icon: 'info',
							title: 'Informasi',
							text: 'Tidak ada data untuk Sub Item yang dipilih',
							confirmButtonColor: '#3085d6'
						});
					}
				}, false);
			});

			// Proses
			$("#btnProses").on("click", function() {
				// Validasi apakah KD_BRG sudah dipilih
				if (!$('#KD_BRG').val()) {
					Swal.fire({
						icon: 'warning',
						title: 'Peringatan',
						text: 'Silakan pilih Sub Item terlebih dahulu!',
						confirmButtonColor: '#3085d6'
					});
					return;
				}

				// Hanya ambil data yang DICENTANG saja
				let dataToSend = [];
				let totalData = 0;
				$('#datatable').find('tbody tr').each(function() {
					totalData++;
					let checkbox = $(this).find('input[type="checkbox"]');

					// Hanya proses jika checkbox tercentang
					if (checkbox.is(':checked')) {
						let rowData = dataTable.row(this).data();
						dataToSend.push({
							KD_BRG: rowData.KD_BRG,
							JTD: 1
						});
					}
				});

				if (dataToSend.length === 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Peringatan',
						text: 'Tidak ada data yang dicentang untuk diproses!',
						html: 'Silakan centang minimal 1 item terlebih dahulu.<br><small>Total data: ' + totalData + ' item</small>',
						confirmButtonColor: '#3085d6'
					});
					return;
				}

				// Konfirmasi sebelum proses
				Swal.fire({
					title: 'Konfirmasi',
					text: 'Apakah Anda yakin ingin memproses ' + dataToSend.length + ' item yang dicentang?',
					html: 'Total item yang dicentang: <strong>' + dataToSend.length + '</strong> dari ' + totalData + ' item',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Ya, Proses!',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						$.ajax({
							url: '{{ route('uslBrgTd-proses') }}',
							type: "POST",
							data: {
								_token: '{{ csrf_token() }}',
								items: dataToSend,
							},
							beforeSend: function() {
								$("#btnProses").prop("disabled", true).text("Processing...");
								Swal.fire({
									title: 'Memproses...',
									text: 'Mohon tunggu sebentar',
									allowOutsideClick: false,
									didOpen: () => {
										Swal.showLoading();
									}
								});
							},
							success: function(res) {
								Swal.fire({
									icon: 'success',
									title: 'Berhasil!',
									text: res.message,
									confirmButtonColor: '#3085d6'
								});
								$("#btnProses").prop("disabled", false).text("Proses");
								$('#datatable').DataTable().ajax.reload();
							},
							error: function(xhr) {
								console.error(xhr.responseText);
								let errorMsg = 'Proses gagal!';
								if (xhr.responseJSON && xhr.responseJSON.message) {
									errorMsg = xhr.responseJSON.message;
								}
								Swal.fire({
									icon: 'error',
									title: 'Error!',
									text: errorMsg,
									confirmButtonColor: '#d33'
								});
								$("#btnProses").prop("disabled", false).text("Proses");
							},
						});
					}
				});
			});

		});
	</script>
@endsection
