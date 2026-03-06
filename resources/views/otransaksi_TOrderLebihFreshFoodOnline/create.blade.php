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

		.btn-action {
			padding: 8px 20px;
			font-weight: 600;
			font-size: 14px;
			margin: 0 3px;
		}

		.btn-save {
			background: #28a745;
			border: none;
			color: #fff;
		}

		.btn-refresh {
			background: #17a2b8;
			border: none;
			color: #fff;
		}

		.btn-print {
			background: #007bff;
			border: none;
			color: #fff;
		}

		.btn-process {
			background: #ffc107;
			border: none;
			color: #000;
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
						<h1 class="m-0">Order Lebih Fresh Food Online - New</h1>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
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
									<div class="row">
										<div class="col-md-3"><strong>Tanggal:</strong> {{ $header->TGL ?? '-' }}</div>
										<div class="col-md-3"><strong>Supplier:</strong> {{ $header->NAMA_SUPP ?? '-' }}</div>
										<div class="col-md-3"><strong>Kode Supplier:</strong> {{ $header->KODES ?? '-' }}</div>
										<div class="col-md-3"><strong>Jumlah Item:</strong> {{ $header->JML_ITEM ?? 0 }}</div>
									</div>
								</div>

								<div class="mb-3">
									<button type="button" id="btnSave" class="btn btn-action btn-save">
										<i class="fas fa-save"></i> SAVE
									</button>
									<button type="button" id="btnRefresh" class="btn btn-action btn-refresh">
										<i class="fas fa-sync"></i> REFRESH
									</button>
									<button type="button" id="btnPrint" class="btn btn-action btn-print">
										<i class="fas fa-print"></i> PRINT
									</button>
									<button type="button" id="btnProcess" class="btn btn-action btn-process">
										<i class="fas fa-cogs"></i> PROCESS
									</button>
									<button type="button" id="btnBack" class="btn btn-action btn-back">
										<i class="fas fa-arrow-left"></i> BACK
									</button>
								</div>

								<hr>

								<!-- Form entryan Barang -->
								<div class="row mb-3">

									<div class="col-md-3">
										<label>Kode Barang</label>
										<input type="text" id="txtKD_BRG" class="form-control">
									</div>

									<div class="col-md-4">
										<label>Nama Barang</label>
										<input type="text" id="txtNamaBarang" class="form-control" readonly>
									</div>

									<div class="col-md-2">
										<label>Stok</label>
										<input type="text" id="txtStok" class="form-control text-right" readonly>
									</div>

									<div class="col-md-2">
										<label>Qty</label>
										<input type="number" id="txtQTY" class="form-control text-right">
									</div>

								</div>
								<!-- end -->

								<table class="table table-bordered" id="tableBarangResult">
									<thead>
										<tr>
											<th>No</th>
											<th>Kode Barang</th>
											<th>Nama Barang</th>
											<th>Supp</th>
											<th>Ket. Uk</th>
											<th>L/H</th>
											<th>PPN</th>
											<th>Stok</th>
											<th>Qty Order</th>
										</tr>
									</thead>
									<tbody></tbody>
									<tfoot>
										<tr style="font-weight:bold;background:#f5f5f5">
											<td colspan="7" class="text-right">GRAND TOTAL</td>
											<td class="text-right" id="totalStok">0</td>
											<td class="text-right" id="totalQty">0</td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="loader" id="LOADX"></div>

	<div class="modal fade" id="modalBarang">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">

				<div class="modal-header">
					<h5 class="modal-title">Browse Barang</h5>
					<button type="button" class="close" data-dismiss="modal">
						&times;
					</button>
				</div>

				<div class="modal-body">

					<table class="table table-bordered" id="tableBarang">
						<thead>
							<tr>
								<th>Kode</th>
								<th>Nama Barang</th>
								<th>Kemasan</th>
								<th>Barcode</th>
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
	<script>
		var identifier = '{{ $identifier ?? '' }}';

		$('#txtKD_BRG').keydown(function(e){
			if(e.keyCode == 13){

				e.preventDefault();
				let kd = $(this).val().trim();

				if(kd.length < 7){
					$('#modalBarang').modal('show');
					loadBarang();
				}else{
					getBarang(kd);
				}

			}
		});

		function getBarang(kd){
			$.ajax({

				url : "{{ route('orderlebihfreshfoodonline_get_barang') }}",
				type : "POST",
				data : {
					_token : "{{ csrf_token() }}",
					kd_brg : kd
				},
				success:function(res){

					if(!res){
						Swal.fire(
							'Error',
							'SubItem Tidak Ditemukan',
							'error'
						);

						$('#txtKD_BRG').focus();
						return;
					}

					$('#txtNamaBarang').val(res.data.NA_BRG);
					$('#txtStok').val(res.data.TOKO);

					// append ke tabel
            		appendBarang(res.data);

					$('#txtQTY').focus();

				}

			});
		}

		// function loadBarang(){
		// 	$.ajax({

		// 		url : "{{ route('orderlebihfreshfoodonline_get_barang') }}",
		// 		type : "POST",
		// 		data : {
		// 			_token : "{{ csrf_token() }}"
		// 		},

		// 		success:function(data){

		// 			var html='';

		// 			data.forEach(function(row){

		// 				html += `
		// 				<tr onclick="pilihBarang('${row.KD_BRG}','${row.NA_BRG}')">
		// 					<td>${row.KD_BRG}</td>
		// 					<td>${row.NA_BRG}</td>
		// 					<td>${row.KET_KEM}</td>
		// 				</tr>`;

		// 			});

		// 			$('#tableBarang tbody').html(html);

		// 		}

		// 	});
		// }

		function loadBarang(){
			$.get("{{ route('orderlebihfreshfoodonline_browseBarang') }}", function(res){

				let html='';

				res.data.forEach(function(row){

					html += `
					<tr onclick="pilihBarang('${row.kd_brg}')">
						<td>${row.kd_brg}</td>
						<td>${row.na_brg}</td>
						<td>${row.ket_kem}</td>
						<td>${row.barcode}</td>
					</tr>`;

				});

				$('#tableBarang tbody').html(html);

			});
		}

		function pilihBarang(kode,nama){
			$('#txtKD_BRG').val(kode);
			$('#txtNamaBarang').val(nama);

			$('#modalBarang').modal('hide');

			getBarang(kode);
		}

		function appendBarang(data){
			let table = $('#tableBarangResult tbody');

			// cek jika sudah ada 
			if($('#row_'+data.KD_BRG).length){
				return;
			}

			let no = table.find('tr').length + 1;

			let row = `
				<tr id="row_${data.KD_BRG}">
					<td class="text-center">${no}</td>
					<td class="kd">${data.KD_BRG}</td>
					<td>${data.NA_BRG}</td>
					<td>${data.SUPP}</td>
					<td>${data.KET_UK ?? ''}</td>
					<td>${data.LPH ?? ''}</td>
					<td class="text-right">${data.PPN ?? 0}</td>
					<td class="text-right stok">${data.TOKO ?? ''}</td>
					<td class="text-right qty">0</td>
					<td></td>
				</tr>
			`;

			table.append(row);
			hitungTotal();
		}

		$('#txtKD_BRG').keydown(function(e){
			if(e.keyCode == 13){
				let kd = $(this).val().trim();
				if(kd.length == 0) return;
				getBarang(kd);
			}
		});

		$('#txtQTY').keydown(function(e){
			if(e.keyCode == 13){

				let qty = parseFloat($(this).val());
				let kd  = $('#txtKD_BRG').val().trim();

				if(qty > 0){

					// update qty di tabel
					let row = $('#row_'+kd);

					if(row.length){
						row.find('.qty').text(qty);
					}

					hitungTotal();

					// reset 
					$('#txtKD_BRG').val('');
					$('#txtQTY').val(0);
					$('#txtStok').val(0);
					$('#txtNamaBarang').val('');

					$('#txtKD_BRG').focus();
				}

			}
		});

		function hitungTotal(){
			let totalStok = 0;
			let totalQty  = 0;

			$('#tableBarangResult tbody tr').each(function(){

				let stok = parseFloat($(this).find('.stok').text()) || 0;
				let qty  = parseFloat($(this).find('.qty').text()) || 0;

				totalStok += stok;
				totalQty  += qty;

			});

			$('#totalStok').text(totalStok.toFixed(2));
			$('#totalQty').text(totalQty.toFixed(2));
		}

		function getTableData(){
			let items = [];

			$('#tableBarangResult tbody tr').each(function(){

				let row = $(this);

				let item = {
					KD_BRG : row.find('.kd').text(),
					NA_BRG : row.find('td:eq(2)').text(),
					SUPP : row.find('td:eq(3)').text(),
					KET_UK : row.find('td:eq(4)').text(),
					LPH    : row.find('td:eq(5)').text(),
					PPN    : row.find('td:eq(6)').text(),
					STOK   : row.find('.stok').text(),
					QTY    : row.find('.qty').text()
				};

				items.push(item);

			});

			return items;
		}

		$(document).ready(function() {
			$('#btnBack').on('click', function() {
				window.location.href = '{{ route('orderlebihfreshfoodonline') }}';
			});

			$('#btnRefresh').on('click', function() {
				location.reload();
			});

			$('#btnSave').on('click', function() {
				Swal.fire({
					title: 'Save Data?',
					text: 'Apakah Anda yakin ingin menyimpan perubahan?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#28a745',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Ya, Simpan!',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						saveData();
					}
				});
			});

			$('#btnPrint').on('click', function() {
				printData(identifier);
			});

			$('#btnProcess').on('click', function() {
				Swal.fire({
					title: 'Process Data?',
					text: 'Kirim data ke server untuk diproses?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#ffc107',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Ya, Process!',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						processData(identifier);
					}
				});
			});
		});

		function saveData() {

			let items = getTableData();

			if(items.length === 0){
				Swal.fire('Warning','Tidak ada data barang','warning');
				return;
			}

			$('#LOADX').show();

			$.ajax({
				url: '{{ route('orderlebihfreshfoodonline_save') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					status: 'simpan',
					// namafile: $('#namafile').val(),
					// tgl: $('#tgl').val(),
					items: items
				},
				success: function(response) {
					$('#LOADX').hide();
					if (response.success) {
						Swal.fire('Berhasil!', response.message, 'success');
					} else {
						Swal.fire('Gagal!', response.error || 'Gagal menyimpan data', 'error');
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					var error = 'Gagal menyimpan data';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						error = xhr.responseJSON.error;
					}
					Swal.fire('Error!', error, 'error');
				}
			});
		}

		function printData(identifier) {
			var url = '{{ route('orderlebihfreshfoodonline_jasper') }}?namafile=' + encodeURIComponent(identifier);
			window.open(url, '_blank');
		}

		function processData(identifier) {
			$('#LOADX').show();
			$.ajax({
				url: '{{ route('orderlebihfreshfoodonline_proses') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					action: 'export_dbf',
					namafile: identifier
				},
				success: function(response) {
					$('#LOADX').hide();
					if (response.success) {
						Swal.fire('Berhasil!', response.message, 'success').then(() => {
							window.location.href = '{{ route('orderlebihfreshfoodonline') }}';
						});
					} else {
						Swal.fire('Gagal!', response.error || 'Gagal memproses data', 'error');
					}
				},
				error: function(xhr) {
					$('#LOADX').hide();
					var error = 'Gagal memproses data';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						error = xhr.responseJSON.error;
					}
					Swal.fire('Error!', error, 'error');
				}
			});
		}
	</script>
@endsection
