@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $title }}</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">{{ $title }}</li>
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
								<div class="row mb-3">
									<div class="col-12">
										<button class="btn btn-success" id="btnProses">
											<i class="fas fa-paper-plane mr-1"></i>Proses
										</button>
										<button class="btn btn-info" id="btnAllIn">
											<i class="fas fa-check-square mr-1"></i>All In
										</button>
									</div>
								</div>

								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="koreksiTable">
										<thead>
											<tr>
												<th style="width: 40px;">
													<input type="checkbox" id="checkAllHeader">
												</th>
												<th>No Bukti</th>
												<th>Tanggal</th>
												<th>Notes</th>
												<th>Supplier</th>
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
		var koreksiTable;
		var selectedBukti = [];
		var flagg = '{{ $flagg }}';

		$(document).ready(function() {
			koreksiTable = $('#koreksiTable').DataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: '{{ route('posting-koreksi.data') }}',
					type: 'GET',
					data: function(d) {
						d.flagg = flagg;
					}
				},
				columns: [{
						data: null,
						orderable: false,
						searchable: false,
						width: '40px',
						render: function(data, type, row) {
							return '<input type="checkbox" class="cek-item" value="' + row.no_bukti + '">';
						}
					},
					{
						data: 'no_bukti',
						name: 'no_bukti'
					},
					{
						data: 'tgl',
						name: 'tgl',
						render: function(data) {
							if (!data) return '';
							var date = new Date(data);
							var day = ('0' + date.getDate()).slice(-2);
							var month = ('0' + (date.getMonth() + 1)).slice(-2);
							var year = date.getFullYear();
							return day + '/' + month + '/' + year;
						}
					},
					{
						data: 'notes',
						name: 'notes'
					},
					{
						data: 'supplier',
						name: 'supplier'
					},
					{
						data: 'total',
						name: 'total',
						className: 'text-right',
						render: function(data) {
							return parseFloat(data || 0).toLocaleString('id-ID', {
								minimumFractionDigits: 0,
								maximumFractionDigits: 0
							});
						}
					},
					{
						data: 'cek',
						name: 'cek',
						className: 'text-center'
					}
				],
				pageLength: 25,
				lengthMenu: [
					[10, 25, 50, 100, -1],
					[10, 25, 50, 100, "Semua"]
				],
				order: [
					[1, 'asc']
				],
				language: {
					processing: "Memproses...",
					lengthMenu: "Tampilkan _MENU_ data",
					zeroRecords: "Data tidak ditemukan",
					info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
					infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
					infoFiltered: "(difilter dari _MAX_ total data)",
					search: "Cari:",
					paginate: {
						first: "Pertama",
						last: "Terakhir",
						next: "Selanjutnya",
						previous: "Sebelumnya"
					}
				}
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
					allIn();
				} else {
					uncheckAll();
				}
			});

			$('#btnAllIn').on('click', function() {
				allIn();
			});

			$('#btnProses').on('click', function() {
				prosesData();
			});
		});

		function allIn() {
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

		function prosesData() {
			if (selectedBukti.length === 0) {
				alert('Harap pilih data yang akan diproses');
				return;
			}

			if (!confirm('Apakah Anda yakin akan memproses ' + selectedBukti.length + ' dokumen?')) {
				return;
			}

			$('#loadingPanel').show();
			$('#btnProses').prop('disabled', true);

			$.ajax({
				url: '{{ route('posting-koreksi.store') }}',
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					selected_bukti: selectedBukti,
					flagg: flagg
				},
				success: function(response) {
					$('#loadingPanel').hide();
					$('#btnProses').prop('disabled', false);

					if (response.success) {
						alert(response.message);
						koreksiTable.ajax.reload();
						selectedBukti = [];
						uncheckAll();
					} else {
						alert('Error: ' + response.message);
					}
				},
				error: function(xhr) {
					$('#loadingPanel').hide();
					$('#btnProses').prop('disabled', false);
					alert('Terjadi kesalahan: ' + (xhr.responseJSON?.message || 'Unknown error'));
				}
			});
		}
	</script>
@endsection
