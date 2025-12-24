@extends('layouts.main')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
	<style>
		.page-header {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			padding: 20px;
			border-radius: 8px;
			margin-bottom: 20px;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		}

		.page-header h3 {
			margin: 0;
			font-weight: 600;
		}

		.info-badges {
			margin-top: 10px;
		}

		.info-badges .badge {
			font-size: 13px;
			padding: 6px 12px;
			margin-right: 10px;
		}

		.action-buttons {
			background: #f8f9fa;
			padding: 15px;
			border-radius: 8px;
			margin-bottom: 20px;
			border: 1px solid #dee2e6;
		}

		.btn-custom {
			padding: 10px 20px;
			font-weight: 600;
			font-size: 13px;
			margin: 5px;
			border-radius: 6px;
			transition: all 0.3s;
			border: none;
		}

		.btn-new {
			background: #28a745;
			color: white;
		}

		.btn-new:hover {
			background: #218838;
			color: white;
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
		}

		.btn-print {
			background: #007bff;
			color: white;
		}

		.btn-print:hover {
			background: #0056b3;
			color: white;
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
		}

		.btn-tgz {
			background: #17a2b8;
			color: white;
		}

		.btn-tgz:hover {
			background: #138496;
			color: white;
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
		}

		.btn-sop {
			background: #6f42c1;
			color: white;
		}

		.btn-sop:hover {
			background: #5a32a3;
			color: white;
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(111, 66, 193, 0.3);
		}

		.btn-tmm {
			background: #fd7e14;
			color: white;
		}

		.btn-tmm:hover {
			background: #e8590c;
			color: white;
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(253, 126, 20, 0.3);
		}

		.btn-kwitansi {
			background: #20c997;
			color: white;
		}

		.btn-kwitansi:hover {
			background: #1aa179;
			color: white;
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(32, 201, 151, 0.3);
		}

		.btn-potongan {
			background: #e83e8c;
			color: white;
		}

		.btn-potongan:hover {
			background: #d21f77;
			color: white;
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(232, 62, 140, 0.3);
		}

		.btn-excel {
			background: #28a745;
			color: white;
		}

		.btn-excel:hover {
			background: #218838;
			color: white;
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
		}

		.btn-print-bersama {
			background: #6c757d;
			color: white;
		}

		.btn-print-bersama:hover {
			background: #5a6268;
			color: white;
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
		}

		.card {
			border: none;
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		}

		.card-header {
			background: white;
			border-bottom: 2px solid #f0f0f0;
			padding: 15px 20px;
		}

		.card-header h5 {
			margin: 0;
			font-weight: 600;
			color: #333;
		}

		.table-responsive {
			border-radius: 8px;
			overflow: hidden;
		}

		.table {
			margin-bottom: 0;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 12px;
			padding: 12px 8px;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		.table tbody tr {
			transition: all 0.2s;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
			cursor: pointer;
		}

		.table tbody td {
			padding: 10px 8px;
			font-size: 13px;
			vertical-align: middle;
		}

		.row-selected {
			background-color: #cce5ff !important;
		}

		.search-box {
			margin-bottom: 15px;
		}

		.search-box input {
			border-radius: 6px;
			border: 1px solid #ced4da;
			padding: 8px 15px;
		}

		.search-box input:focus {
			background-color: #e7f3ff;
			border-color: #80bdff;
		}
	</style>
@endsection

@section('content')
	<div class="container-fluid">
		<!-- Page Header -->
		<div class="page-header">
			<h3><i class="fas fa-tags"></i> {{ $judul ?? 'Transaksi Pelaksanaan Turun Harga' }}</h3>
			<div class="info-badges">
				<span class="badge badge-light"><i class="fas fa-building"></i> Cabang: {{ $cbg ?? '-' }}</span>
				<span class="badge badge-light"><i class="fas fa-calendar"></i> Periode: {{ $periode ?? '-' }}</span>
				<span class="badge badge-light"><i class="fas fa-user"></i> User: {{ $username ?? '-' }}</span>
			</div>
		</div>

		@if (isset($error))
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<i class="fas fa-exclamation-triangle"></i> {{ $error }}
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		@endif

		@if (isset($warning))
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<i class="fas fa-exclamation-circle"></i> {{ $warning }}
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		@endif

		<!-- Action Buttons -->
		<div class="action-buttons">
			<div class="row">
				<div class="col-md-12">
					<button type="button" class="btn btn-custom btn-new" id="btnNew">
						<i class="fas fa-plus"></i> NEW
					</button>
					<button type="button" class="btn btn-custom btn-print" id="btnPrint" disabled>
						<i class="fas fa-print"></i> PRINT
					</button>
					<button type="button" class="btn btn-custom btn-tgz" id="btnTGZ" disabled>
						<i class="fas fa-file-alt"></i> TGZ
					</button>
					<button type="button" class="btn btn-custom btn-sop" id="btnSOP" disabled>
						<i class="fas fa-file-alt"></i> SOP
					</button>
					<button type="button" class="btn btn-custom btn-tmm" id="btnTMM" disabled>
						<i class="fas fa-file-alt"></i> TMM
					</button>
					<button type="button" class="btn btn-custom btn-kwitansi" id="btnKwitansi" disabled>
						<i class="fas fa-receipt"></i> KWITANSI
					</button>
					<button type="button" class="btn btn-custom btn-potongan" id="btnPotongan" disabled>
						<i class="fas fa-cut"></i> POTONGAN
					</button>
					<button type="button" class="btn btn-custom btn-excel" id="btnExcel" disabled>
						<i class="fas fa-file-excel"></i> EXCEL
					</button>
					<button type="button" class="btn btn-custom btn-print-bersama" id="btnPrintBersama">
						<i class="fas fa-print"></i> PRINT BERSAMA
					</button>
				</div>
			</div>
		</div>

		<!-- Notes -->
		<div class="alert alert-info" role="alert">
			<strong>Notes:</strong> Jika ada data/report selisih bisa diupdate<br>
			Report TR selisih: Klik POTONGAN -> Update Data pilih 'YES'
		</div>

		<!-- Main Data Table -->
		<div class="card">
			<div class="card-header">
				<h5><i class="fas fa-list"></i> Daftar Turun Harga</h5>
			</div>
			<div class="card-body">
				<div class="search-box">
					<input type="text" id="searchBox" class="form-control" placeholder="Cari data...">
				</div>
				<div class="table-responsive">
					<table class="table-bordered table-striped table-hover table" id="mainTable">
						<thead>
							<tr>
								<th width="5%">No</th>
								<th width="12%">No. Bukti</th>
								<th width="10%">Tgl Mulai</th>
								<th width="10%">Tgl Selesai</th>
								<th width="8%">Supplier</th>
								<th width="15%">Nama</th>
								<th width="15%">Catatan</th>
								<th width="8%">Posted</th>
								<th width="5%">OK</th>
								<th width="10%">Cara Bayar</th>
								<th width="12%">Nama Penerima</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<!-- Hidden field for selected row -->
	<input type="hidden" id="selectedNoBukti" value="">
@endsection

@section('scripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
	<script>
		let table;
		let selectedRow = null;

		$(document).ready(function() {
			// Initialize DataTable
			table = $('#mainTable').DataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: '{{ route('tpelaksanaanturunharga.cari_data') }}',
					type: 'POST',
					data: function(d) {
						d._token = '{{ csrf_token() }}';
						d.table = 'main';
					}
				},
				columns: [{
						data: 'DT_RowIndex',
						name: 'DT_RowIndex',
						orderable: false,
						searchable: false
					},
					{
						data: 'NO_BUKTI',
						name: 'NO_BUKTI'
					},
					{
						data: 'TGL_MULAI',
						name: 'TGL_MULAI'
					},
					{
						data: 'TGL_SLS',
						name: 'TGL_SLS'
					},
					{
						data: 'KODES',
						name: 'KODES'
					},
					{
						data: 'NAMAS',
						name: 'NAMAS'
					},
					{
						data: 'NOTES',
						name: 'NOTES'
					},
					{
						data: 'posted',
						name: 'posted',
						orderable: false
					},
					{
						data: null,
						orderable: false,
						searchable: false,
						render: function(data, type, row) {
							if (row.posted == 1) {
								return '<span class="badge badge-success">✓</span>';
							} else {
								return '<button class="btn btn-sm btn-primary btn-ok-post" data-nobukti="' +
									row.NO_BUKTI + '">OK</button>';
							}
						}
					},
					{
						data: 'CARA_BAYAR',
						name: 'CARA_BAYAR',
						defaultContent: '-'
					},
					{
						data: 'NA_KWI',
						name: 'NA_KWI',
						defaultContent: '-'
					}
				],
				order: [
					[1, 'desc']
				],
				pageLength: 25,
				responsive: true,
				language: {
					processing: "Memuat data...",
					search: "Cari:",
					lengthMenu: "Tampilkan _MENU_ data",
					info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
					infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
					infoFiltered: "(disaring dari _MAX_ total data)",
					loadingRecords: "Memuat...",
					zeroRecords: "Tidak ada data yang ditemukan",
					emptyTable: "Tidak ada data tersedia",
					paginate: {
						first: "Pertama",
						previous: "Sebelumnya",
						next: "Selanjutnya",
						last: "Terakhir"
					}
				}
			});

			// Row click event
			$('#mainTable tbody').on('click', 'tr', function() {
				if ($(this).hasClass('row-selected')) {
					$(this).removeClass('row-selected');
					selectedRow = null;
					$('#selectedNoBukti').val('');
					disableButtons();
				} else {
					table.$('tr.row-selected').removeClass('row-selected');
					$(this).addClass('row-selected');
					selectedRow = table.row(this).data();
					$('#selectedNoBukti').val(selectedRow.NO_BUKTI);
					enableButtons();
				}
			});

			// Double click to edit
			$('#mainTable tbody').on('dblclick', 'tr', function() {
				const data = table.row(this).data();
				if (data) {
					window.location.href = '{{ url('tpelaksanaanturunharga') }}/' + data.NO_BUKTI + '/edit';
				}
			});

			// Button NEW
			$('#btnNew').click(function() {
				window.location.href = '{{ route('tpelaksanaanturunharga.create') }}';
			});

			// Button PRINT
			$('#btnPrint').click(function() {
				if (!selectedRow) return;
				const url = '{{ route('tpelaksanaanturunharga.print', ':no_bukti') }}'.replace(':no_bukti',
					selectedRow.NO_BUKTI);
				window.open(url, '_blank');
			});

			// Button TGZ
			$('#btnTGZ').click(function() {
				if (!selectedRow) return;
				printPenjualan('TGZ');
			});

			// Button SOP
			$('#btnSOP').click(function() {
				if (!selectedRow) return;
				printPenjualan('SOP');
			});

			// Button TMM
			$('#btnTMM').click(function() {
				if (!selectedRow) return;
				printPenjualan('TMM');
			});

			// Button KWITANSI
			$('#btnKwitansi').click(function() {
				if (!selectedRow) return;
				printKwitansi();
			});

			// Button POTONGAN
			$('#btnPotongan').click(function() {
				if (!selectedRow) return;
				printPotongan();
			});

			// Button EXCEL
			$('#btnExcel').click(function() {
				if (!selectedRow) return;
				exportExcel();
			});

			// Button PRINT BERSAMA
			$('#btnPrintBersama').click(function() {
				window.location.href = '{{ url('tpelaksanaanturunharga/print-bersama') }}';
			});

			// Button OK Post
			$(document).on('click', '.btn-ok-post', function(e) {
				e.stopPropagation();
				const noBukti = $(this).data('nobukti');
				updatePosted(noBukti);
			});

			// Search box
			$('#searchBox').on('keyup', function() {
				table.search(this.value).draw();
			});

			// Delete key handler
			$(document).on('keydown', function(e) {
				if (e.keyCode === 46 && selectedRow) { // Delete key
					deleteRecord(selectedRow.NO_BUKTI);
				}
			});
		});

		function enableButtons() {
			$('#btnPrint, #btnTGZ, #btnSOP, #btnTMM, #btnKwitansi, #btnPotongan, #btnExcel').prop('disabled',
				false);
		}

		function disableButtons() {
			$('#btnPrint, #btnTGZ, #btnSOP, #btnTMM, #btnKwitansi, #btnPotongan, #btnExcel').prop('disabled', true);
		}

		function printPenjualan(cbg) {
			Swal.fire({
				title: 'Cetak Report ' + cbg,
				text: 'Apakah Anda ingin mencetak report ' + cbg + '?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: 'Ya, Cetak',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					const url = '{{ url('tpelaksanaanturunharga/print-penjualan') }}/' + selectedRow.NO_BUKTI +
						'/' + cbg;
					window.open(url, '_blank');
				}
			});
		}

		function printKwitansi() {
			Swal.fire({
				title: 'Cetak Kwitansi',
				text: 'Apakah Anda ingin mencetak kwitansi?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: 'Ya, Cetak',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					const url = '{{ url('tpelaksanaanturunharga/print-kwitansi') }}/' + selectedRow.NO_BUKTI;
					window.open(url, '_blank');
				}
			});
		}

		function printPotongan() {
			Swal.fire({
				title: 'Cetak Potongan',
				text: 'Apakah Anda ingin mencetak potongan?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: 'Ya, Cetak',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					const url = '{{ url('tpelaksanaanturunharga/print-potongan') }}/' + selectedRow.NO_BUKTI;
					window.open(url, '_blank');
				}
			});
		}

		function exportExcel() {
			$.ajax({
				url: '{{ route('tpelaksanaanturunharga.proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'export_excel',
					no_bukti: selectedRow.NO_BUKTI
				},
				success: function(response) {
					if (response.success) {
						// Convert data to Excel format and download
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil diekspor'
						});
					}
				},
				error: function(xhr) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Terjadi kesalahan'
					});
				}
			});
		}

		function updatePosted(noBukti) {
			Swal.fire({
				title: 'Konfirmasi Posting',
				text: 'Apakah Anda yakin ingin memposting data ini?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Ya, Posting',
				cancelButtonText: 'Batal',
				confirmButtonColor: '#28a745',
				cancelButtonColor: '#dc3545'
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({
						url: '{{ route('tpelaksanaanturunharga.proses') }}',
						type: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							action: 'update_posted',
							no_bukti: noBukti
						},
						success: function(response) {
							if (response.success) {
								Swal.fire({
									icon: 'success',
									title: 'Berhasil',
									text: response.message
								});
								table.ajax.reload();
							}
						},
						error: function(xhr) {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: xhr.responseJSON?.error || 'Terjadi kesalahan'
							});
						}
					});
				}
			});
		}

		function deleteRecord(noBukti) {
			Swal.fire({
				title: 'Konfirmasi Hapus',
				text: 'Apakah Turun Harga ' + noBukti + ' ingin dihapus?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Ya, Hapus',
				cancelButtonText: 'Batal',
				confirmButtonColor: '#dc3545',
				cancelButtonColor: '#6c757d',
				input: 'checkbox',
				inputValue: 0,
				inputPlaceholder: 'Termasuk reset data Turun Harga?'
			}).then((result) => {
				if (result.isConfirmed) {
					const resetData = result.value ? 1 : 0;
					$.ajax({
						url: '{{ url('tpelaksanaanturunharga') }}/' + noBukti,
						type: 'DELETE',
						data: {
							_token: '{{ csrf_token() }}',
							reset_data: resetData
						},
						success: function(response) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: 'Turun Harga ' + noBukti + ' telah terhapus.'
							});
							table.ajax.reload();
							selectedRow = null;
							$('#selectedNoBukti').val('');
							disableButtons();
						},
						error: function(xhr) {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: xhr.responseJSON?.error || 'Terjadi kesalahan'
							});
						}
					});
				}
			});
		}
	</script>
@endsection
