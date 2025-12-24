@extends('layouts.plain')

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

		.status-label {
			display: inline-block;
			padding: 5px 10px;
			border-radius: 3px;
			font-size: 12px;
			font-weight: 600;
		}

		.status-info {
			background: #d1ecf1;
			color: #0c5460;
			border: 1px solid #bee5eb;
		}

		.panel-detail {
			display: none;
			margin-top: 15px;
			padding: 15px;
			background: #f8f9fa;
			border: 1px solid #dee2e6;
			border-radius: 5px;
		}

		.panel-monitor {
			display: none;
			margin-top: 15px;
			padding: 15px;
			background: #fff3cd;
			border: 1px solid #ffc107;
			border-radius: 5px;
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
					<div class="col-sm-6">
						<div class="float-right">
							<span class="status-label status-info">
								<i class="fas fa-building"></i> {{ $cbg ?? '-' }}
							</span>
							<span class="status-label status-info ml-2">
								<i class="fas fa-calendar"></i> {{ $periode ?? '-' }}
							</span>
						</div>
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

				<!-- Tabel Utama: Daftar No Bukti -->
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<h5 class="mb-3"><i class="fas fa-list"></i> Daftar Usulan Turun Harga</h5>
								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableMain" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">No</th>
												<th width="120px">No Bukti</th>
												<th width="100px">Tgl Mulai</th>
												<th width="100px">Tgl Selesai</th>
												<th width="80px">Supplier</th>
												<th>Nama</th>
												<th width="100px" class="text-center">Posted</th>
												<th width="100px" class="text-center">Action</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Panel Detail: Item Barang -->
				<div class="row">
					<div class="col-12">
						<div class="panel-detail" id="panelDetail">
							<div class="d-flex justify-content-between align-items-center mb-3">
								<h5><i class="fas fa-box"></i> Detail Item Barang - <span id="lblNoBukti"></span></h5>
								<div>
									<button type="button" id="btnOK" class="btn btn-action btn-ok">
										<i class="fas fa-check"></i> OK
									</button>
									<button type="button" id="btnHapus" class="btn btn-action btn-hapus">
										<i class="fas fa-trash"></i> HAPUS
									</button>
									<button type="button" id="btnExcel" class="btn btn-action btn-excel">
										<i class="fas fa-file-excel"></i> EXCEL
									</button>
								</div>
							</div>

							<div class="table-responsive">
								<table class="table-striped table-bordered table-hover table" id="tableDetail" style="width:100%">
									<thead>
										<tr>
											<th width="50px" class="text-center">No</th>
											<th width="120px">SubItem</th>
											<th>Nama</th>
											<th width="100px">Ukuran</th>
											<th width="100px" class="text-right">Harga</th>
											<th width="100px" class="text-right">Turun Harga</th>
											<th width="100px">Tgl Mulai</th>
											<th width="100px">Tgl Selesai</th>
											<th width="80px" class="text-center">Hapus</th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

				<!-- Panel Monitor: Status di Semua Outlet -->
				<div class="row">
					<div class="col-12">
						<div class="panel-monitor" id="panelMonitor">
							<h5 class="mb-3"><i class="fas fa-eye"></i> Monitor Status - <span id="lblKdBrg"></span></h5>
							<div class="table-responsive">
								<table class="table-striped table-bordered table-hover table" id="tableMonitor" style="width:100%">
									<thead>
										<tr>
											<th width="50px" class="text-center">No</th>
											<th width="80px">Cbg</th>
											<th width="120px">Barang</th>
											<th width="100px" class="text-right">TGZ</th>
											<th width="100px" class="text-right">TMM</th>
											<th width="100px" class="text-right">SOP</th>
											<th width="100px">Jam Mulai</th>
											<th width="100px">Jam Selesai</th>
											<th width="100px">Tgl Mulai</th>
											<th width="100px">Tgl Selesai</th>
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

	<div class="loader" id="LOADX"></div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
	<script>
		var tableMain, tableDetail, tableMonitor;
		var selectedNoBukti = '';
		var selectedKdBrg = '';

		$(document).ready(function() {
			// Initialize Main Table
			tableMain = $('#tableMain').DataTable({
				ajax: {
					url: '{{ route('tpelaksanaanturunharga.cari_data') }}',
					type: 'POST',
					data: function(d) {
						d._token = '{{ csrf_token() }}';
						d.table = 'main';
					},
					error: handleAjaxError
				},
				columns: [{
						data: null,
						render: function(data, type, row, meta) {
							return meta.row + 1;
						},
						className: 'text-center'
					},
					{
						data: 'NO_BUKTI'
					},
					{
						data: 'TGL_MULAI'
					},
					{
						data: 'TGL_SLS'
					},
					{
						data: 'KODES'
					},
					{
						data: 'NAMAS'
					},
					{
						data: 'posted',
						className: 'text-center'
					},
					{
						data: 'action',
						name: 'action',
						orderable: false,
						searchable: false
					}
				],
				order: [
					[1, 'desc']
				],
				processing: true,
				pageLength: 15,
				language: getDataTableLanguage(),
				createdRow: function(row, data, dataIndex) {
					$(row).addClass('clickable-row');
					$(row).attr('data-no-bukti', data.NO_BUKTI);
					$(row).attr('data-posted', data.posted);
				}
			});

			// Initialize Detail Table
			tableDetail = $('#tableDetail').DataTable({
				ajax: {
					url: '{{ route('tpelaksanaanturunharga.cari_data') }}',
					type: 'POST',
					data: function(d) {
						d._token = '{{ csrf_token() }}';
						d.table = 'detail';
						d.no_bukti = selectedNoBukti;
					},
					error: handleAjaxError
				},
				columns: [{
						data: null,
						render: function(data, type, row, meta) {
							return meta.row + 1;
						},
						className: 'text-center'
					},
					{
						data: 'KD_BRG'
					},
					{
						data: 'NA_BRG'
					},
					{
						data: 'KET_UK'
					},
					{
						data: 'harga',
						className: 'text-right'
					},
					{
						data: 'th',
						className: 'text-right'
					},
					{
						data: 'TGDIS_M'
					},
					{
						data: 'TGDIS_A'
					},
					{
						data: 'hps',
						className: 'text-center'
					}
				],
				order: [
					[1, 'asc']
				],
				processing: true,
				pageLength: 25,
				language: getDataTableLanguage(),
				createdRow: function(row, data, dataIndex) {
					$(row).addClass('clickable-row');
					$(row).attr('data-kd-brg', data.KD_BRG);
				}
			});

			// Initialize Monitor Table
			tableMonitor = $('#tableMonitor').DataTable({
				ajax: {
					url: '{{ route('tpelaksanaanturunharga.cari_data') }}',
					type: 'POST',
					data: function(d) {
						d._token = '{{ csrf_token() }}';
						d.table = 'monitor';
						d.kd_brg = selectedKdBrg;
					},
					error: handleAjaxError
				},
				columns: [{
						data: null,
						render: function(data, type, row, meta) {
							return meta.row + 1;
						},
						className: 'text-center'
					},
					{
						data: 'CBG'
					},
					{
						data: 'KD_BRG'
					},
					{
						data: 'THGZ',
						className: 'text-right'
					},
					{
						data: 'THMM',
						className: 'text-right'
					},
					{
						data: 'THSP',
						className: 'text-right'
					},
					{
						data: 'JAM'
					},
					{
						data: 'JAMSLS'
					},
					{
						data: 'TGDIS_M'
					},
					{
						data: 'TGDIS_A'
					}
				],
				processing: true,
				paging: false,
				searching: false,
				language: getDataTableLanguage()
			});

			// Click main table row to load detail
			$('#tableMain tbody').on('click', 'tr.clickable-row', function() {
				tableMain.$('tr.row-selected').removeClass('row-selected');
				$(this).addClass('row-selected');

				selectedNoBukti = $(this).data('no-bukti');
				$('#lblNoBukti').text(selectedNoBukti);
				$('#panelDetail').show();
				$('#panelMonitor').hide();

				tableDetail.ajax.reload();
			});

			// Click detail table row to load monitor
			$('#tableDetail tbody').on('click', 'tr.clickable-row', function() {
				tableDetail.$('tr.row-selected').removeClass('row-selected');
				$(this).addClass('row-selected');

				selectedKdBrg = $(this).data('kd-brg');
				$('#lblKdBrg').text(selectedKdBrg);
				$('#panelMonitor').show();

				tableMonitor.ajax.reload();
			});

			// Button OK - Update Posted
			$('#btnOK').on('click', function() {
				var posted = tableMain.$('tr.row-selected').data('posted');

				if (posted == 1) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Data sudah diposting sebelumnya!'
					});
					return;
				}

				Swal.fire({
					title: 'Konfirmasi Update',
					text: 'Update harga turun dan posting ke semua outlet?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#28a745',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Update!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						updatePosted();
					}
				});
			});

			// Button Hapus
			$('#btnHapus').on('click', function() {
				var selectedItems = [];
				$('.chk-hapus:checked').each(function() {
					selectedItems.push($(this).data('kd-brg'));
				});

				if (selectedItems.length === 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Tidak ada item yang dipilih!'
					});
					return;
				}

				tableDetail.rows().every(function() {
					var row = $(this.node());
					var kdBrg = row.data('kd-brg');
					if (selectedItems.includes(kdBrg)) {
						this.remove();
					}
				});
				tableDetail.draw();

				Swal.fire({
					icon: 'success',
					title: 'Berhasil',
					text: selectedItems.length + ' item dihapus dari daftar',
					timer: 1500,
					showConfirmButton: false
				});
			});

			// Button Excel
			$('#btnExcel').on('click', function() {
				exportExcel();
			});

			$('#btnPrint').on('click', function() {

				let periode = "{{ session('periode.bulan') }}/{{ session('periode.tahun') }}";

				window.open('/print?no_bukti=' + no_bukti + '&periode=' + encodeURIComponent(periode),
					'_blank');
			});

		});

		function updatePosted() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('tpelaksanaanturunharga.proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'update_posted',
					no_bukti: selectedNoBukti
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

						tableMain.ajax.reload();
						$('#panelDetail').hide();
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal memproses data'
					});
				}
			});
		}

		function exportExcel() {
			$('#LOADX').show();

			$.ajax({
				url: '{{ route('tpelaksanaanturunharga.proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'export_excel',
					no_bukti: selectedNoBukti
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data.length > 0) {
						var ws = XLSX.utils.json_to_sheet(response.data);
						var wb = XLSX.utils.book_new();
						XLSX.utils.book_append_sheet(wb, ws, 'Turun Harga');

						var filename = 'Turun_Harga_' + selectedNoBukti + '.xlsx';
						XLSX.writeFile(wb, filename);

						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'Data berhasil di-export!',
							timer: 1500,
							showConfirmButton: false
						});
					} else {
						Swal.fire({
							icon: 'info',
							title: 'Informasi',
							text: 'Tidak ada data untuk di-export'
						});
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: xhr.responseJSON?.error || 'Gagal export data'
					});
				}
			});
		}

		function handleAjaxError(xhr, error, code) {
			console.error('DataTables Ajax error:', xhr.responseText);

			if (xhr.status === 500 || xhr.status === 400) {
				var errorMsg = 'Terjadi kesalahan saat memuat data.';

				try {
					var response = JSON.parse(xhr.responseText);
					if (response.error) {
						errorMsg = response.error;
					}
				} catch (e) {
					errorMsg = 'Tabel database mungkin belum ada atau terjadi kesalahan koneksi.';
				}

				Swal.fire({
					icon: 'warning',
					title: 'Perhatian',
					html: errorMsg + '<br><br><small>Silakan hubungi administrator jika masalah berlanjut.</small>',
					confirmButtonText: 'OK'
				});
			}
		}

		function getDataTableLanguage() {
			return {
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
			};
		}
	</script>
@endsection
