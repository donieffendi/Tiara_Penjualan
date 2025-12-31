@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
	<style>
		.card {
			padding: 15px;
		}

		.form-control:focus {
			background-color: #b5e5f9;
		}

		.btn-save {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 12px 30px;
			font-size: 16px;
		}

		.btn-save:hover {
			background: #218838;
			color: #fff;
		}

		.btn-proses {
			background: #007bff;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 12px 30px;
			font-size: 16px;
		}

		.btn-proses:hover {
			background: #0056b3;
			color: #fff;
		}

		.btn-print {
			background: #17a2b8;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 12px 30px;
			font-size: 16px;
		}

		.btn-print:hover {
			background: #138496;
			color: #fff;
		}

		.btn-folder {
			background: #ffc107;
			border: none;
			color: #212529;
			font-weight: 600;
			padding: 12px 30px;
			font-size: 16px;
		}

		.btn-folder:hover {
			background: #e0a800;
			color: #212529;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
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

		.input-group-sm {
			margin-bottom: 10px;
		}

		.editable-cell {
			cursor: pointer;
			min-height: 30px;
			padding: 5px;
		}

		.editable-cell:hover {
			background-color: #f0f0f0;
		}

		.form-inline label {
			margin-right: 10px;
		}

		.dataTables_wrapper .dataTables_paginate {
			margin-top: 15px;
		}

		.table.dataTable tbody td {
			vertical-align: middle;
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
								<!-- Form Input Section -->
								<div class="row mb-3">
									<div class="col-md-6">
										<div class="input-group input-group-sm">
											<div class="input-group-prepend">
												<span class="input-group-text" style="width: 150px;"><strong>Ambil Data</strong></span>
											</div>
											<input type="text" class="form-control" id="txtBukti" placeholder="Masukkan No Bukti JL/BL" maxlength="20">
										</div>
									</div>
									<div class="col-md-6">
										<div class="input-group input-group-sm">
											<div class="input-group-prepend">
												<span class="input-group-text" style="width: 150px;"><strong>Cetak Ulang</strong></span>
											</div>
											<input type="text" class="form-control" id="txtSP" placeholder="Masukkan No SP" maxlength="20">
										</div>
									</div>
								</div>

								<hr>

								<!-- DataTable Section -->
								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table-sm table" id="tableCetakSP" style="width:100%">
										<thead>
											<tr>
												<th width="40px" class="text-center">No</th>
												<th width="100px">No Bukti</th>
												<th width="80px" class="text-center">Tanggal</th>
												<th width="80px">Kode Brg</th>
												<th>Nama Barang</th>
												<th width="100px">Kemasan</th>
												<th width="60px" class="text-center">Qty</th>
												<th width="80px">Kode Supp</th>
												<th width="150px">Supplier</th>
												<th width="60px" class="text-center">Hapus</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>

								<hr>

								<!-- Button Section -->
								<div class="row mb-3">
									<div class="col-md-12">
										<button type="button" id="btnSave" class="btn btn-save">
											<i class="fas fa-save"></i> SIMPAN
										</button>
										<button type="button" id="btnProses" class="btn btn-proses">
											<i class="fas fa-cogs"></i> PROSES
										</button>
										<button type="button" id="btnPrint" class="btn btn-print">
											<i class="fas fa-print"></i> PRINT
										</button>
										<button type="button" id="btnFolder" class="btn btn-folder">
											<i class="fas fa-folder-open"></i> FOLDER
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

	<div class="loader" id="LOADX"></div>

	<!-- Modal Browse Bukti -->
	<div class="modal fade" id="modalBrowseBukti" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Browse No Bukti JL/BL</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<table class="table-striped table-bordered table-hover table-sm table" id="tableBrowseBukti">
						<thead>
							<tr>
								<th>No Bukti</th>
								<th>Tanggal</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

	<script>
		var table;

		$(document).ready(function() {
			// Initialize DataTable
			table = $('#tableCetakSP').DataTable({
				processing: true,
				serverSide: true,
				ajax: "{{ route('get-tcetakspkode5-data') }}",
				columns: [{
						data: 'DT_RowIndex',
						name: 'DT_RowIndex',
						orderable: false,
						searchable: false,
						className: 'text-center'
					},
					{
						data: 'no_bukti',
						name: 'no_bukti',
						className: 'editable-cell editable-no-bukti'
					},
					{
						data: 'tgl_format',
						name: 'tgl',
						className: 'text-center editable-cell editable-tgl'
					},
					{
						data: 'kd_brg',
						name: 'kd_brg',
						className: 'editable-cell editable-kd-brg'
					},
					{
						data: 'na_brg',
						name: 'na_brg'
					},
					{
						data: 'ket_kem',
						name: 'ket_kem'
					},
					{
						data: 'qty',
						name: 'qty',
						className: 'text-center editable-cell editable-qty',
						render: function(data) {
							return parseFloat(data).toFixed(0);
						}
					},
					{
						data: 'kodes',
						name: 'kodes',
						className: 'editable-cell editable-kodes'
					},
					{
						data: 'namas',
						name: 'namas'
					},
					{
						data: 'action',
						name: 'action',
						orderable: false,
						searchable: false,
						className: 'text-center'
					}
				],
				pageLength: 25,
				lengthMenu: [
					[10, 25, 50, 100, -1],
					[10, 25, 50, 100, "Semua"]
				],
				order: [
					[3, 'asc']
				],
				language: {
					processing: "Memuat data...",
					lengthMenu: "Tampilkan _MENU_ data",
					zeroRecords: "Data tidak ditemukan",
					info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
					infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
					infoFiltered: "(disaring dari _MAX_ total data)",
					search: "Cari:",
					paginate: {
						first: "Pertama",
						last: "Terakhir",
						next: "Selanjutnya",
						previous: "Sebelumnya"
					}
				}
			});

			// Handle input No Bukti (Ambil Data)
			$('#txtBukti').on('keypress', function(e) {
				if (e.which === 13) {
					e.preventDefault();
					var noBukti = $(this).val().trim();

					if (!noBukti) {
						Swal.fire({
							icon: 'warning',
							title: 'Perhatian',
							text: 'No Bukti tidak boleh kosong'
						});
						return;
					}

					$('#LOADX').show();

					$.ajax({
						url: "{{ route('tcetakspkode5.proses_bukti') }}",
						type: 'POST',
						data: {
							bukti: noBukti,
							_token: "{{ csrf_token() }}"
						},
						success: function(response) {
							$('#LOADX').hide();

							if (response.status === 'not_found') {
								Swal.fire({
									title: 'Data tidak ditemukan',
									text: 'Lihat list datanya?',
									icon: 'question',
									showCancelButton: true,
									confirmButtonText: 'Ya',
									cancelButtonText: 'Tidak'
								}).then((result) => {
									if (result.isConfirmed) {
										showBrowseBukti();
									}
								});
							} else if (response.status === 'success') {
								Swal.fire({
									icon: 'success',
									title: 'Berhasil',
									text: response.message,
									timer: 2000,
									showConfirmButton: false
								});
								$('#txtBukti').val('');
								table.ajax.reload();
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
				}
			});

			// Handle input No SP (Cetak Ulang)
			$('#txtSP').on('keypress', function(e) {
				if (e.which === 13) {
					e.preventDefault();
					var noSP = $(this).val().trim();

					if (!noSP) {
						Swal.fire({
							icon: 'warning',
							title: 'Perhatian',
							text: 'No SP tidak boleh kosong'
						});
						return;
					}

					printSP(noSP);
				}
			});

			// Handle Save Button
			$('#btnSave').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi',
					text: 'Simpan data?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya',
					cancelButtonText: 'Tidak'
				}).then((result) => {
					if (result.isConfirmed) {
						saveData();
					}
				});
			});

			// Handle Proses Button
			$('#btnProses').on('click', function() {
				Swal.fire({
					title: 'Konfirmasi',
					text: 'Lanjut proses? Data akan dikonversi menjadi PO.',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Ya',
					cancelButtonText: 'Tidak'
				}).then((result) => {
					if (result.isConfirmed) {
						prosesData();
					}
				});
			});

			// Handle Print Button
			$('#btnPrint').on('click', function() {
				Swal.fire({
					icon: 'info',
					title: 'Info',
					text: 'Fitur cetak akan mencetak semua PO yang ada dalam daftar',
					confirmButtonText: 'OK'
				});
			});

			// Handle Folder Button
			$('#btnFolder').on('click', function() {
				Swal.fire({
					icon: 'info',
					title: 'Info',
					text: 'Buka folder di: D:\\Surat Pesanan\\[tanggal]\\',
					confirmButtonText: 'OK'
				});
			});

			// Handle editable cells - Kode Barang
			$('#tableCetakSP').on('dblclick', '.editable-kd-brg', function() {
				var cell = $(this);
				var currentValue = cell.text();

				var input = $('<input type="text" class="form-control form-control-sm">')
					.val(currentValue)
					.css('width', '100%');

				cell.html(input);
				input.focus();

				input.on('blur keypress', function(e) {
					if (e.type === 'blur' || e.which === 13) {
						var newValue = $(this).val();
						cell.text(newValue);
						// TODO: Update row data
					}
				});
			});

			// Handle editable cells - Kode Supplier
			$('#tableCetakSP').on('dblclick', '.editable-kodes', function() {
				var cell = $(this);
				var currentValue = cell.text();

				var input = $('<input type="text" class="form-control form-control-sm">')
					.val(currentValue)
					.css('width', '100%');

				cell.html(input);
				input.focus();

				input.on('blur keypress', function(e) {
					if (e.type === 'blur' || e.which === 13) {
						var newValue = $(this).val();
						cell.text(newValue);
						// TODO: Update row data
					}
				});
			});

			// Handle editable cells - Qty
			$('#tableCetakSP').on('dblclick', '.editable-qty', function() {
				var cell = $(this);
				var currentValue = cell.text();

				var input = $('<input type="number" class="form-control form-control-sm">')
					.val(parseFloat(currentValue))
					.css('width', '100%');

				cell.html(input);
				input.focus();

				input.on('blur keypress', function(e) {
					if (e.type === 'blur' || e.which === 13) {
						var newValue = $(this).val();
						cell.text(parseFloat(newValue).toFixed(0));
						// TODO: Update row data
					}
				});
			});

			// Handle checkbox hapus
			$('#tableCetakSP').on('change', '.chk-hapus', function() {
				var checked = $(this).is(':checked');
				var id = $(this).data('id');
				// TODO: Mark row for deletion
			});

			// Browse Bukti Modal - Open
			function showBrowseBukti() {
				$('#LOADX').show();

				$.ajax({
					url: "{{ route('tcetakspkode5_browse') }}",
					type: 'GET',
					data: {
						type: 'bukti'
					},
					success: function(response) {
						$('#LOADX').hide();

						var tbody = $('#tableBrowseBukti tbody');
						tbody.empty();

						if (response.length === 0) {
							tbody.append('<tr><td colspan="2" class="text-center">Data tidak ditemukan</td></tr>');
						} else {
							response.forEach(function(item) {
								var row = '<tr class="browse-row" data-bukti="' + item.no_bukti + '">' +
									'<td>' + item.no_bukti + '</td>' +
									'<td>' + item.tgl + '</td>' +
									'</tr>';
								tbody.append(row);
							});
						}

						$('#modalBrowseBukti').modal('show');
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

			// Browse Bukti Modal - Select
			$('#tableBrowseBukti').on('click', '.browse-row', function() {
				var noBukti = $(this).data('bukti');
				$('#txtBukti').val(noBukti);
				$('#modalBrowseBukti').modal('hide');
				$('#txtBukti').trigger($.Event('keypress', {
					which: 13
				}));
			});
		});

		function printSP(noSP) {
			window.open("{{ route('tcetakspkode5_jasper') }}?no_bukti=" + noSP, '_blank');
			$('#txtSP').val('');
		}

		function saveData() {
			var tableData = [];

			table.rows().every(function() {
				var data = this.data();
				tableData.push({
					no_id: data.no_id,
					no_bukti: data.no_bukti,
					tgl: data.tgl,
					kd_brg: data.kd_brg,
					na_brg: data.na_brg,
					qty: data.qty,
					harga: data.harga || 0,
					total: data.total || 0,
					kodes: data.kodes,
					namas: data.namas,
					sub: data.sub,
					kdbar: data.kdbar,
					klaku: data.klaku,
					ket_kem: data.ket_kem,
					kemasan: data.kemasan,
					type: data.type,
					hps: data.hps || 0
				});
			});

			if (tableData.length === 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Tidak ada data untuk disimpan'
				});
				return;
			}

			$('#LOADX').show();

			$.ajax({
				url: "{{ route('tcetakspkode5_store') }}",
				type: 'POST',
				data: {
					data: tableData,
					_token: "{{ csrf_token() }}"
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success) {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 2000,
							showConfirmButton: false
						});
						table.ajax.reload();
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.error || 'Gagal menyimpan data'
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
		}

		function prosesData() {
			$('#LOADX').show();
			$('#btnProses').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');

			$.ajax({
				url: "{{ route('tcetakspkode5_proses') }}",
				type: 'POST',
				data: {
					_token: "{{ csrf_token() }}"
				},
				success: function(response) {
					$('#LOADX').hide();
					$('#btnProses').prop('disabled', false).html('<i class="fas fa-cogs"></i> PROSES');

					if (response.success) {
						var poList = response.po_list.join(', ');
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							html: response.message + '<br><br><strong>No PO:</strong><br>' + poList,
							confirmButtonText: 'OK'
						});
						table.ajax.reload();
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.error || 'Proses gagal'
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					$('#btnProses').prop('disabled', false).html('<i class="fas fa-cogs"></i> PROSES');
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Terjadi kesalahan'
					});
				}
			});
		}
	</script>
@endsection
