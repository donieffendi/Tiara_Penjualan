@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">SPKO ke DC Tunjungsari</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="/">Home</a></li>
							<li class="breadcrumb-item active">SPKO ke DC Tunjungsari</li>
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
							<div class="card-header">
								<div class="row">
									<div class="col-md-6">
										<a href="{{ route('tspkokedctunjungsari.edit', ['status' => 'simpan']) }}" class="btn btn-primary">
											<i class="fas fa-plus"></i> New
										</a>
									</div>
									<div class="col-md-6">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group row mb-0">
													<label class="col-sm-4 col-form-label text-right">Cabang:</label>
													<div class="col-sm-8">
														<select class="form-control form-control-sm" id="filter-cabang">
															@foreach ($cabang as $cbg)
																<option value="{{ $cbg->KODE ?? ($cbg->kode ?? '') }}" {{ ($cbg->KODE ?? ($cbg->kode ?? '')) == session('cbg') ? 'selected' : '' }}>
																	{{ $cbg->KODE ?? ($cbg->kode ?? '') }} - {{ $cbg->NA_TOKO ?? ($cbg->na_toko ?? ($cbg->nama ?? '')) }}
																</option>
															@endforeach
														</select>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group row mb-0">
													<label class="col-sm-3 col-form-label text-right">Dep:</label>
													<div class="col-sm-9">
														<select class="form-control form-control-sm" id="filter-dep">
															<option value="01-FOOD">01-FOOD</option>
															<option value="02-NON FOOD">02-NON FOOD</option>
														</select>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="card-body">
								<table id="datatable" class="table-bordered table-striped table-sm table">
									<thead>
										<tr>
											<th width="5%">No</th>
											<th width="12%">No Bukti</th>
											<th width="10%">Tgl</th>
											<th width="8%">Kode</th>
											<th width="20%">Supplier</th>
											<th width="20%">Notes</th>
											<th width="8%">Cbg</th>
											<th width="12%">Nama File</th>
											<th width="10%">Action</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		$(document).ready(function() {
			var table = $('#datatable').DataTable({
				processing: true,
				serverSide: true,
				pageLength: 25,
				ajax: {
					url: "{{ route('tspkokedctunjungsari.get-data') }}",
					data: function(d) {
						d.cbg = $('#filter-cabang').val();
						d.golongan = $('#filter-dep').val();
					},
					error: function(xhr, error, code) {
						console.error('DataTable AJAX Error:', xhr, error, code);
						var errorMsg = 'Unknown error occurred';
						if (xhr.responseJSON && xhr.responseJSON.error) {
							errorMsg = xhr.responseJSON.error;
						} else if (xhr.responseText) {
							errorMsg = xhr.responseText.substring(0, 200);
						}
						Swal.fire({
							icon: 'error',
							title: 'DataTable Error',
							html: '<p><strong>Error loading data:</strong></p><p>' + errorMsg + '</p>' +
								'<hr><p class="text-left"><small><strong>Troubleshooting:</strong><br>' +
								'1. Check browser console (F12) for detailed errors<br>' +
								'2. Check Laravel log: storage/logs/laravel.log<br>' +
								'3. Verify database table exists: ' + $('#filter-cabang').val() + '.po_dc_ts<br>' +
								'4. Verify database connection</small></p>',
							confirmButtonText: 'OK'
						});
					}
				},
				columns: [{
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
						data: 'TGL',
						name: 'TGL',
						className: 'text-center'
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
						data: 'CBG',
						name: 'CBG',
						className: 'text-center'
					},
					{
						data: 'na_file',
						name: 'na_file'
					},
					{
						data: 'action',
						name: 'action',
						orderable: false,
						searchable: false,
						className: 'text-center'
					}
				],
				order: [
					[1, 'desc']
				],
				language: {
					lengthMenu: "Show _MENU_",
					search: "Search:",
					zeroRecords: "No matching records found",
					info: "Showing _START_ to _END_ of _TOTAL_ entries",
					infoEmpty: "Showing 0 to 0 of 0 entries",
					infoFiltered: "(filtered from _MAX_ total entries)",
					paginate: {
						first: "First",
						last: "Last",
						next: "Next",
						previous: "Previous"
					}
				}
			});

			$('#filter-cabang, #filter-dep').change(function() {
				table.ajax.reload();
			});

			$(document).on('click', '[data-action="print"]', function(e) {
				e.preventDefault();
				var noBukti = $(this).data('id');
				var cbg = $('#filter-cabang').val();

				$.ajax({
					url: "{{ route('tspkokedctunjungsari.print') }}",
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						no_bukti: noBukti,
						cbg: cbg
					},
					success: function(response) {
						if (response.data && response.data.length > 0) {
							var printWindow = window.open('', '_blank');
							printWindow.document.write('<html><head><title>Print SPKO DC TS</title>');
							printWindow.document.write(
								'<style>body{font-family:Arial;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #000;padding:5px;}</style>'
							);
							printWindow.document.write('</head><body>');
							printWindow.document.write('<h2>SPKO ke DC Tunjungsari</h2>');
							printWindow.document.write('<p>No Bukti: ' + response.data[0].no_bukti + '</p>');
							printWindow.document.write('<p>Tanggal: ' + response.data[0].TGL + '</p>');
							printWindow.document.write(
								'<table><thead><tr><th>No</th><th>Sub</th><th>Item</th><th>Nama Barang</th><th>TK</th><th>GD</th><th>Qty</th><th>Kemasan</th></tr></thead><tbody>'
							);

							response.data.forEach(function(item, index) {
								printWindow.document.write('<tr><td>' + (index + 1) + '</td><td>' + item.NO_SUB +
									'</td><td>' + item.NO_ITEM + '</td><td>' + item.na_brg + '</td><td>' + item
									.pmin +
									'</td><td>' + item.pmax + '</td><td>' + item.qty + '</td><td>' + item.ket_kem +
									'</td></tr>');
							});

							printWindow.document.write('</tbody></table></body></html>');
							printWindow.document.close();
							printWindow.print();
						} else {
							Swal.fire('Info', 'Tidak ada data untuk dicetak', 'info');
						}
					},
					error: function(xhr) {
						Swal.fire('Error', 'Gagal memuat data print', 'error');
					}
				});
			});
		});

		function editData(noBukti, cbg) {
			window.location.href = "{{ route('tspkokedctunjungsari.edit') }}?status=edit&no_bukti=" + noBukti + "&cbg=" +
				cbg;
		}

		function printData(noBukti, cbg) {
			$('[data-action="print"][data-id="' + noBukti + '"]').trigger('click');
		}

		function exportData(noBukti, cbg) {
			Swal.fire({
				title: 'Export Orderan?',
				text: 'File akan digenerate dan dikirim ke DC Tunjungsari',
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: 'Ya, Export!',
				cancelButtonText: 'Batal',
				showLoaderOnConfirm: true,
				preConfirm: () => {
					return $.ajax({
						url: "{{ route('tspkokedctunjungsari.export') }}",
						type: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							no_bukti: noBukti,
							cbg: cbg
						},
						dataType: 'json'
					});
				},
				allowOutsideClick: () => !Swal.isLoading()
			}).then((result) => {
				if (result.isConfirmed) {
					Swal.fire({
						title: 'Berhasil!',
						text: result.value.message || 'Export berhasil',
						icon: 'success',
						confirmButtonText: 'OK'
					}).then(() => {
						$('#datatable').DataTable().ajax.reload();
					});
				}
			}).catch((error) => {
				if (error && error.responseJSON) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: error.responseJSON.message || 'Terjadi kesalahan saat export',
						confirmButtonText: 'OK'
					});
				}
			});
		}
	</script>
@endsection
