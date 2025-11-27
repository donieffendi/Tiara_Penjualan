@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Posting Transaksi Logistik</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Posting Transaksi Logistik</li>
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
								<form id="filterForm">
									@csrf
									<div class="row align-items-end mb-3">
										<div class="col-md-3">
											<label for="cbg">Cabang</label>
											<select name="cbg" id="cbg" class="form-control" required>
												<option value="">Pilih Cabang</option>
												@foreach ($cbg as $c)
													<option value="{{ $c->kode }}" {{ session('user_cabang') == $c->kode ? 'selected' : '' }}>
														{{ $c->kode }}
													</option>
												@endforeach
											</select>
										</div>
										<div class="col-md-2">
											<label for="periode">Periode</label>
											<input type="text" name="periode" id="periode" class="form-control" value="{{ $periode }}" placeholder="MM-YYYY" required>
										</div>
										<div class="col-md-2">
											<button class="btn btn-primary" type="submit">
												<i class="fas fa-search mr-1"></i>Tampil
											</button>
										</div>
									</div>
								</form>

								<div class="row mb-3">
									<div class="col-12">
										<button class="btn btn-success" id="btnPost">
											<i class="fas fa-paper-plane mr-1"></i>Post
										</button>
										<button class="btn btn-info" id="btnCheckAll">
											<i class="fas fa-check-square mr-1"></i>Cek Semua
										</button>
										<button class="btn btn-warning" id="btnUncheckAll">
											<i class="fas fa-square mr-1"></i>Batal Cek
										</button>
									</div>
								</div>

								<div class="table-responsive">
									<table class="table-striped table-bordered table" id="postingTable">
										<thead>
											<tr>
												<th style="width: 40px;">
													<input type="checkbox" id="checkAllHeader">
												</th>
												<th>No Bukti</th>
												<th>Tanggal</th>
												<th>Notes</th>
												<th>Total</th>
												<th>Cek</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>

								<div id="loadingPanel" style="display: none;" class="p-3 text-center">
									<div class="spinner-border" role="status">
										<span class="sr-only">Loading...</span>
									</div>
									<div class="mt-2">Memproses data...</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script>
		var postingDataTable;
		var selectedBukti = [];

		$(document).ready(function() {
			postingDataTable = $('#postingTable').DataTable({
				processing: true,
				serverSide: true,
				paging: true,
				pageLength: 25,
				searching: true,
				ordering: true,
				info: true,
				autoWidth: false,
				responsive: true,
				scrollX: true,
				ajax: {
					url: "{{ route('get-lpostingtransaksilogistik') }}",
					type: "GET",
					data: function(d) {
						d.cbg = $('#cbg').val();
						d.periode = $('#periode').val();
						d.flagg = '{{ $flagg }}';
					}
				},
				columns: [{
						data: "cek_box",
						orderable: false,
						searchable: false,
						width: "40px"
					},
					{
						data: "no_bukti"
					},
					{
						data: "tgl"
					},
					{
						data: "notes"
					},
					{
						data: "total",
						className: "text-right"
					},
					{
						data: "cek",
						className: "text-center"
					}
				],
				columnDefs: [{
						className: "text-right",
						targets: [4]
					},
					{
						className: "text-center",
						targets: [0, 5]
					}
				]
			});

			$('#filterForm').on('submit', function(e) {
				e.preventDefault();
				if (!validateForm()) return;
				postingDataTable.ajax.reload();
			});

			$(document).on('change', '.cek-item', function() {
				var bukti = $(this).val();
				if ($(this).is(':checked')) {
					if (selectedBukti.indexOf(bukti) === -1) {
						selectedBukti.push(bukti);
					}
				} else {
					var index = selectedBukti.indexOf(bukti);
					if (index !== -1) {
						selectedBukti.splice(index, 1);
					}
				}
				updateCheckAllStatus();
			});

			$('#checkAllHeader').on('change', function() {
				if ($(this).is(':checked')) {
					checkAll();
				} else {
					uncheckAll();
				}
			});

			$('#btnCheckAll').on('click', function() {
				checkAll();
			});

			$('#btnUncheckAll').on('click', function() {
				uncheckAll();
			});

			$('#btnPost').on('click', function() {
				postData();
			});

			@if (session('user_cabang'))
				postingDataTable.ajax.reload();
			@endif
		});

		function validateForm() {
			var cbg = $('#cbg').val();
			var periode = $('#periode').val();

			if (!cbg) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Harap pilih cabang'
				});
				return false;
			}

			if (!periode) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Harap isi periode'
				});
				return false;
			}

			var periodePattern = /^\d{2}-\d{4}$/;
			if (!periodePattern.test(periode)) {
				Swal.fire({
					icon: 'warning',
					title: 'Format Salah',
					text: 'Format periode harus MM-YYYY (contoh: 01-2025)'
				});
				return false;
			}

			return true;
		}

		function checkAll() {
			$('.cek-item').prop('checked', true);
			selectedBukti = [];
			$('.cek-item').each(function() {
				selectedBukti.push($(this).val());
			});
			$('#checkAllHeader').prop('checked', true);
		}

		function uncheckAll() {
			$('.cek-item').prop('checked', false);
			selectedBukti = [];
			$('#checkAllHeader').prop('checked', false);
		}

		function updateCheckAllStatus() {
			var totalCheckboxes = $('.cek-item').length;
			var checkedCheckboxes = $('.cek-item:checked').length;

			if (checkedCheckboxes === 0) {
				$('#checkAllHeader').prop('indeterminate', false);
				$('#checkAllHeader').prop('checked', false);
			} else if (checkedCheckboxes === totalCheckboxes) {
				$('#checkAllHeader').prop('indeterminate', false);
				$('#checkAllHeader').prop('checked', true);
			} else {
				$('#checkAllHeader').prop('indeterminate', true);
			}
		}

		function postData() {
			if (selectedBukti.length === 0) {
				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					text: 'Harap pilih data yang akan diposting'
				});
				return;
			}

			Swal.fire({
				title: 'Konfirmasi',
				text: 'Apakah Anda yakin akan memposting ' + selectedBukti.length + ' dokumen?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#28a745',
				cancelButtonColor: '#6c757d',
				confirmButtonText: 'Ya, Post!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					$('#loadingPanel').show();
					$('#btnPost').prop('disabled', true);

					$.ajax({
						url: '{{ route('lpostingtransaksilogistik.store') }}',
						method: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							selected_bukti: selectedBukti
						},
						success: function(response) {
							$('#loadingPanel').hide();
							$('#btnPost').prop('disabled', false);

							if (response.success) {
								Swal.fire({
									icon: 'success',
									title: 'Berhasil',
									text: response.message
								});
								postingDataTable.ajax.reload();
								selectedBukti = [];
								uncheckAll();
							} else {
								Swal.fire({
									icon: 'error',
									title: 'Gagal',
									text: response.message
								});
							}
						},
						error: function(xhr) {
							$('#loadingPanel').hide();
							$('#btnPost').prop('disabled', false);
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: xhr.responseJSON?.message || 'Terjadi kesalahan'
							});
						}
					});
				}
			});
		}
	</script>
@endsection
