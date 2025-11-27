@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<style>
		/* reuse styles from original edit view */
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

		.btn-save {
			background: #007bff;
			border: none;
			color: #fff;
		}

		.btn-back {
			background: #6c757d;
			border: none;
			color: #fff;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 13px;
			padding: 12px 8px;
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

		.edit-qty {
			text-align: right;
			padding: 2px 5px;
			height: 28px;
		}
	</style>
@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Order Lebih Fresh Food Online - Edit</h1>
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
								<div class="header-info">
									<div class="row">
										<div class="col-md-3"><strong>Nama File:</strong><br>{{ $namafile }}</div>
										<div class="col-md-3"><strong>Tanggal:</strong><br>{{ isset($header->TGL) ? date('d-m-Y', strtotime($header->TGL)) : '-' }}</div>
										<div class="col-md-3"><strong>Jumlah Supplier:</strong><br>{{ $header->JML_SUPPLIER ?? '-' }}</div>
										<div class="col-md-3"><strong>Jumlah Item:</strong><br>{{ $header->JML_ITEM ?? '-' }}</div>
									</div>
								</div>

								<div class="mb-3 text-right">
									<button type="button" id="btnBack" class="btn btn-action btn-back"><i class="fas fa-arrow-left"></i> BACK</button>
								</div>

								<hr>

								<div class="table-responsive">
									<table class="table-striped table-bordered table-hover table" id="tableData" style="width:100%">
										<thead>
											<tr>
												<th width="50px" class="text-center">No</th>
												<th width="100px">Supplier</th>
												<th width="100px">Kode Barang</th>
												<th>Nama Barang</th>
												<th width="100px">Ukuran</th>
												<th width="100px">Kemasan</th>
												<th width="100px" class="text-right">LPH</th>
												<th width="100px" class="text-right">Saldo</th>
												<th width="100px" class="text-right">Qty</th>
												<th width="100px">Tanggal</th>
												<th width="60px" class="text-center">Aksi</th>
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
		var namafile = '{{ $namafile }}';

		$(document).ready(function() {
			table = $('#tableData').DataTable({
				ajax: {
					url: '{{ route('orderlebihfreshfoodonline_detail', $namafile) }}',
					type: 'GET'
				},
				columns: [{
						data: null,
						render: function(data, type, row, meta) {
							return meta.row + 1;
						},
						className: 'text-center'
					},
					{
						data: 'SUPP'
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
						data: 'KET_KEM'
					},
					{
						data: 'LPH',
						className: 'text-right'
					},
					{
						data: 'SALDO',
						className: 'text-right'
					},
					{
						data: 'QTY',
						className: 'text-right'
					},
					{
						data: 'TGL'
					},
					{
						data: 'action',
						className: 'text-center'
					}
				],
				paging: false,
				searching: false,
				ordering: false,
				info: false,
				processing: true
			});

			$('#btnBack').on('click', function() {
				window.location.href = '{{ route('orderlebihfreshfoodonline') }}';
			});

			$(document).on('blur', '.edit-qty', function() {
				var rec = $(this).data('rec');
				var newQty = parseFloat($(this).val()) || 0;
				var originalQty = parseFloat($(this).attr('data-original')) || 0;
				if (newQty === originalQty) return;
				updateQty(rec, newQty, $(this));
			});

			$(document).on('focus', '.edit-qty', function() {
				$(this).attr('data-original', $(this).val());
			});

			$(document).on('click', '.btn-delete-item', function() {
				var rec = $(this).data('rec');
				if (confirm('Hapus item ini?')) deleteItem(rec);
			});
		});

		function updateQty(rec, qty, element) {
			$('#LOADX').show();
			$.ajax({
				url: '{{ route('orderlebihfreshfoodonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'update_qty',
					rec: rec,
					qty: qty
				},
				success: function(response) {
					$('#LOADX').hide();
					if (response.success) {
						element.attr('data-original', qty);
						alert(response.message);
					}
				},
				error: function() {
					$('#LOADX').hide();
					alert('Gagal mengupdate qty');
					element.val(element.attr('data-original') || 0);
				}
			});
		}

		function deleteItem(rec) {
			$('#LOADX').show();
			$.ajax({
				url: '{{ route('orderlebihfreshfoodonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'delete_item',
					rec: rec
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
					alert('Gagal menghapus item');
				}
			});
		}
	</script>
@endsection
