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

		.btn-posting {
			background: #28a745;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-posting:hover {
			background: #218838;
			color: #fff;
		}

		.btn-batal {
			background: #dc3545;
			border: none;
			color: #fff;
			font-weight: 600;
			padding: 10px 25px;
			font-size: 15px;
		}

		.btn-batal:hover {
			background: #c82333;
			color: #fff;
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

		.form-check-input:disabled {
			cursor: not-allowed;
			opacity: 0.5;
		}

		.badge {
			padding: 5px 10px;
			font-size: 12px;
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
									<strong>Informasi:</strong>
									@if ($flagz === 'FS')
										Proses posting flash sale akan mengupdate diskon pada master barang. Proses ini tidak dapat dibatalkan kecuali dengan tombol Batal Posting.
									@else
										Proses posting obral akan mengupdate diskon pada master barang. Proses ini tidak dapat dibatalkan kecuali dengan tombol Batal Posting.
									@endif
								</div>

								<div class="filter-section">
									<div class="row">
										<div class="col-md-12 text-right">
											<button type="button" id="btnAllIn" class="btn btn-allin">
												<i class="fas fa-check-double"></i> ALL IN
											</button>
											<button type="button" id="btnPosting" class="btn btn-posting">
												<i class="fas fa-paper-plane"></i> POSTING
											</button>
											<button type="button" id="btnBatal" class="btn btn-batal">
												<i class="fas fa-times-circle"></i> BATAL POSTING
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
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">
													<input hidden type="checkbox" id="checkAll" class="form-check-input">
												</th>
												<th width="80px" class="text-center">No</th>
												<th>No Bukti</th>
												<th>Tgl Mulai</th>
												<th>Tgl Selesai</th>
												<th>Outlet</th>
												<th class="text-center">Posted</th>
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
		var flagz = '{{ $flagz }}';

		// Tentukan route berdasarkan flagz
		var routeCari = flagz === 'FS' ? '{{ route('postingflashsale_cari') }}' : '{{ route('tpelaksanaanobralsuper_cari') }}';
		var routeDetail = flagz === 'FS' ? '{{ route('postingflashsale_detail', '') }}' : '{{ route('tpelaksanaanobralsuper_detail', '') }}';

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
				$('.cek-item:not(:disabled)').prop('checked', $(this).is(':checked'));
			});

			// Uncheck "check all" when individual checkbox is unchecked
			$(document).on('change', '.cek-item', function() {
				if (!$(this).is(':checked')) {
					$('#checkAll').prop('checked', false);
				} else {
					var allChecked = $('.cek-item:not(:disabled)').length === $('.cek-item:checked').length;
					$('#checkAll').prop('checked', allChecked);
				}
			});

			// Button All In - centang semua checkbox yang belum posted
			$('#btnAllIn').on('click', function() {
				var count = $('.cek-item:not(:disabled)').length;
				if (count === 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Tidak ada data yang dapat dipilih (semua sudah di-posting)'
					});
					return;
				}

				$('.cek-item:not(:disabled)').prop('checked', true);
				$('#checkAll').prop('checked', true);
				Swal.fire({
					icon: 'success',
					title: 'Berhasil',
					text: count + ' data telah dipilih',
					timer: 1500,
					showConfirmButton: false
				});
			});

			// Button Posting
			$('#btnPosting').on('click', function() {
				processAction('posting');
			});

			// Button Batal Posting
			$('#btnBatal').on('click', function() {
				processAction('batal');
			});
		});

		function processAction(action) {
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

			// Cek apakah ada item yang sudah di-posting untuk aksi posting
			if (action === 'posting') {
				var hasPosted = false;
				$('.cek-item:checked').each(function() {
					if ($(this).data('posted') == 1) {
						hasPosted = true;
					}
				});

				if (hasPosted) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Ada dokumen yang sudah di-posting. Hanya dokumen yang belum di-posting yang akan diproses.'
					});
				}
			}

			var title = action === 'batal' ? 'Konfirmasi Batal Posting' : 'Konfirmasi Posting';
			var message = action === 'batal' ?
				'Batal posting <strong>' + selectedItems.length + '</strong> dokumen?' :
				'Posting <strong>' + selectedItems.length + '</strong> dokumen?';
			var warning = action === 'batal' ?
				'<small class="text-danger">Diskon akan direset ke 0</small>' :
				'<small class="text-danger">Diskon akan diterapkan dan tidak dapat dibatalkan kecuali dengan tombol Batal Posting</small>';

			Swal.fire({
				title: title,
				html: message + '<br>' + warning,
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: action === 'batal' ? '#dc3545' : '#28a745',
				cancelButtonColor: '#6c757d',
				confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses!',
				cancelButtonText: '<i class="fas fa-times"></i> Batal',
				showLoaderOnConfirm: true,
				preConfirm: () => {
					var btnSelector = action === 'batal' ? '#btnBatal' : '#btnPosting';
					$(btnSelector).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');
					$('#LOADX').show();

					return $.ajax({
						url: routeDetail + '/' + selectedItems.join(','),
						type: 'GET',
						data: {
							flagz: flagz,
							action: action
						}
					}).fail(function(xhr) {
						$('#LOADX').hide();
						Swal.showValidationMessage('Proses gagal: ' + (xhr.responseJSON?.error || 'Terjadi kesalahan'));
					});
				},
				allowOutsideClick: () => !Swal.isLoading()
			}).then((result) => {
				var btnSelector = action === 'batal' ? '#btnBatal' : '#btnPosting';
				var btnText = action === 'batal' ? '<i class="fas fa-times-circle"></i> BATAL POSTING' :
				'<i class="fas fa-paper-plane"></i> POSTING';
				$(btnSelector).prop('disabled', false).html(btnText);
				$('#LOADX').hide();

				if (result.isConfirmed) {
					var message = result.value.message;
					var hasErrors = result.value.errors && result.value.errors.length > 0;

					if (hasErrors) {
						message += '<br><br><small class="text-warning">Beberapa dokumen gagal diproses:<br>' +
							result.value.errors.join('<br>') + '</small>';
					}

					var successText = action === 'batal' ? 'Pembatalan berhasil' : 'Update diskon selesai!';

					Swal.fire({
						icon: hasErrors ? 'warning' : 'success',
						title: hasErrors ? 'Selesai dengan Peringatan' : 'Berhasil',
						html: message + '<br><small>' + successText + '</small>',
						timer: 3000,
						showConfirmButton: true
					});
					table.ajax.reload();
					$('#checkAll').prop('checked', false);
				}
			});
		}

		function loadData() {
			console.log("hai");
			table = $('#tableData').DataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: routeCari,
					type: 'POST',
					data: function(d) {
						d._token = '{{ csrf_token() }}';
						d.flagz = flagz;
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
						data: 'NO_BUKTI',
						name: 'NO_BUKTI'
					},
					{
						data: 'TGL_MULAI',
						name: 'TGL_MULAI',
						className: 'text-center'
					},
					{
						data: 'TGL_SLS',
						name: 'TGL_SLS',
						className: 'text-center'
					},
					{
						data: 'NAMAS',
						name: 'NAMAS'
					},
					{
						data: 'posted',
						name: 'posted',
						className: 'text-center'
					}
				],
				pageLength: 25,
				lengthMenu: [
					[10, 25, 50, 100, -1],
					[10, 25, 50, 100, "Semua"]
				],
				order: [
					[2, 'desc']
				],
				dom: 'rt<"row mt-3"<"col-md-6"i><"col-md-6"p>>'
			});
		}
	</script>
@endsection
