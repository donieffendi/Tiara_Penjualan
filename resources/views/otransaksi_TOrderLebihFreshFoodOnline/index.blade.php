@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<style>
		/* reuse styles from original view */
		.card {
			padding: 15px;
		}

		.form-control:focus {
			background-color: #b5e5f9;
		}

		.btn-action {
			padding: 8px 20px;
			font-weight: 600;
			font-size: 14px;
			margin: 0 3px;
		}

		.btn-new {
			background: #28a745;
			border: none;
			color: #fff;
		}

		.btn-new:hover {
			background: #218838;
			color: #fff;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 13px;
			padding: 12px 8px;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.table tbody td {
			padding: 8px;
			font-size: 13px;
		}

		.loader {
			position: fixed;
			top: 50%;
			left: 50%;
			width: 100px;
			aspect-ratio: 1;
			background: radial-gradient(farthest-side, #ffa516 90%, #0000) center/16px 16px, radial-gradient(farthest-side, green 90%, #0000) bottom/12px 12px;
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
	</style>
@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Order Lebih Fresh Food Online</h1>
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
								<div class="info-box">
									<p class="mb-1"><strong>Petunjuk:</strong></p>
									<ul class="mb-0">
										<li>Klik <strong>NEW</strong> untuk membuat order baru</li>
										<li>Klik <strong>Edit</strong> untuk mengubah detail order</li>
										<li>Klik <strong>Print</strong> untuk mencetak laporan</li>
										<li>Klik <strong>Kirim</strong> untuk mengirim data ke server</li>
										<li>Klik <strong>Hapus</strong> untuk menghapus order</li>
									</ul>
								</div>

								<div class="mb-3 text-right">
									<button type="button" id="btnNew" class="btn btn-action btn-new">
										<i class="fas fa-plus"></i> NEW
									</button>
								</div>

								<hr>

								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">No</th>
												<th>Nama File</th>
												<th width="120px">Tanggal</th>
												<th width="100px" class="text-center">Jml Supplier</th>
												<th width="100px" class="text-center">Jml Item</th>
												<th width="120px" class="text-right">Total Qty</th>
												<th width="280px" class="text-center">Aksi</th>
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

		$(document).ready(function() {
			// Initialize DataTable
			table = $('#tableData').DataTable({
				ajax: {
					url: '{{ route('orderlebihfreshfoodonline_cari') }}',
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}'
					}
				},
				columns: [{
						data: null,
						render: function(data, type, row, meta) {
							return meta.row + 1;
						},
						className: 'text-center'
					},
					{
						data: 'NAMAFILE'
					},
					{
						data: 'TGL'
					},
					{
						data: 'JML_SUPPLIER',
						className: 'text-center'
					},
					{
						data: 'JML_ITEM',
						className: 'text-center'
					},
					{
						data: 'TOTAL_QTY',
						className: 'text-right'
					},
					{
						data: 'action',
						className: 'text-center'
					}
				],
				order: [
					[2, 'desc']
				],
				processing: true
			});

			// Button New
			$('#btnNew').on('click', function() {
				window.location.href = '{{ route('orderlebihfreshfoodonline') }}/new';
			});

			// Edit / Print / Send / Delete handlers (use online routes)
			$(document).on('click', '.btn-edit', function() {
				var namafile = $(this).data('file');
				window.location.href = '{{ route('orderlebihfreshfoodonline') }}/edit/' + namafile;
			});
			$(document).on('click', '.btn-print', function() {
				var namafile = $(this).data('file');
				printData(namafile);
			});
			$(document).on('click', '.btn-send', function() {
				var namafile = $(this).data('file');
				if (confirm('Kirim file ' + namafile + '?')) sendData(namafile);
			});
			$(document).on('click', '.btn-delete', function() {
				var namafile = $(this).data('file');
				if (confirm('Hapus file ' + namafile + '?')) deleteData(namafile);
			});
		});

		function printData(namafile) {
			$('#LOADX').show();
			$.ajax({
				url: '{{ route('orderlebihfreshfoodonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'print',
					namafile: namafile
				},
				success: function(response) {
					$('#LOADX').hide();
					if (response.success && response.data.length > 0) {
						var printWindow = window.open('', '', 'height=600,width=800');
						printWindow.document.write('<html><head><title>Cetak Order Lebih Fresh Food Online</title>');
						printWindow.document.write('</head><body>');
						printWindow.document.write('<h2>Order Lebih Fresh Food Online</h2>');
						printWindow.document.write('<p>File: ' + namafile + '</p>');
						printWindow.document.write('</body></html>');
						printWindow.document.close();
						setTimeout(function() {
							printWindow.print();
						}, 250);
					} else {
						alert('Tidak ada data untuk dicetak');
					}
				},
				error: function() {
					$('#LOADX').hide();
					alert('Gagal mencetak');
				}
			});
		}

		function sendData(namafile) {
			$('#LOADX').show();
			$.ajax({
				url: '{{ route('orderlebihfreshfoodonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'send',
					namafile: namafile
				},
				success: function(response) {
					$('#LOADX').hide();
					if (response.success) alert(response.message);
				},
				error: function() {
					$('#LOADX').hide();
					alert('Gagal mengirim data');
				}
			});
		}

		function deleteData(namafile) {
			$('#LOADX').show();
			$.ajax({
				url: '{{ route('orderlebihfreshfoodonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'delete',
					namafile: namafile
				},
				success: function(response) {
					$('#LOADX').hide();
					if (response.success) {
						alert(response.message);
						table.ajax.reload();
					}
				},
				error: function() {
					$('#LOADX').hide();
					alert('Gagal menghapus data');
				}
			});
		}
	</script>
@endsection
