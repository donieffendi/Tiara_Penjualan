@extends('layouts.plain')

@push('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Orderan Pelanggan</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="/">Home</a></li>
							<li class="breadcrumb-item active">Orderan Pelanggan</li>
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
										<a href="{{ route('torderanpelanggan.edit', ['status' => 'simpan']) }}" class="btn btn-primary">
											<i class="fas fa-plus"></i> New
										</a>
									</div>
								</div>
							</div>
							<div class="card-body">
								<table id="datatable" class="table-bordered table-striped table-sm table">
									<thead>
										<tr>
											<th width="5%">No</th>
											<th width="20%">No Bukti</th>
											<th width="15%">Tgl</th>
											<th width="15%">Total Qty</th>
											<th width="35%">Notes</th>
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
	<!-- SweetAlert2 -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		$(document).ready(function() {
			// Display success/error messages from session
			@if (session('success'))
				Swal.fire({
					icon: 'success',
					title: 'Berhasil!',
					text: '{{ session('success') }}',
					confirmButtonText: 'OK',
					timer: 3000
				});
			@endif

			@if (session('error'))
				Swal.fire({
					icon: 'error',
					title: 'Error!',
					text: '{{ session('error') }}',
					confirmButtonText: 'OK'
				});
			@endif

			@if (session('warning'))
				Swal.fire({
					icon: 'warning',
					title: 'Peringatan!',
					text: '{{ session('warning') }}',
					confirmButtonText: 'OK'
				});
			@endif

			@if (session('info'))
				Swal.fire({
					icon: 'info',
					title: 'Informasi',
					text: '{{ session('info') }}',
					confirmButtonText: 'OK'
				});
			@endif
			var table = $('#datatable').DataTable({
				processing: true,
				serverSide: true,
				pageLength: 25,
				ajax: {
					url: "{{ route('torderanpelanggan.get-data') }}",
					error: function(xhr, error, code) {
						console.error('DataTables Error:', xhr, error, code);

						let errorMessage = 'Terjadi kesalahan saat memuat data.';

						if (xhr.responseJSON && xhr.responseJSON.error) {
							errorMessage = xhr.responseJSON.error;
						} else if (xhr.responseText) {
							try {
								const response = JSON.parse(xhr.responseText);
								errorMessage = response.message || response.error || errorMessage;
							} catch (e) {
								errorMessage = xhr.statusText || errorMessage;
							}
						}

						Swal.fire({
							icon: 'error',
							title: 'Error!',
							text: errorMessage,
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
						data: 'no_bukti',
						name: 'no_bukti'
					},
					{
						data: 'tgl',
						name: 'tgl',
						className: 'text-center'
					},
					{
						data: 'total_qty',
						name: 'total_qty',
						className: 'text-right',
						render: function(data) {
							return parseFloat(data).toLocaleString('id-ID', {
								minimumFractionDigits: 2,
								maximumFractionDigits: 2
							});
						}
					},
					{
						data: 'ket',
						name: 'ket'
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

			$(document).on('click', '[data-action="print"]', function(e) {
				e.preventDefault();
				var noBukti = $(this).data('id');

				$.ajax({
					url: "{{ route('torderanpelanggan.print') }}",
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						no_bukti: noBukti
					},
					success: function(response) {
						if (response.data && response.data.length > 0) {
							var printWindow = window.open('', '_blank');
							printWindow.document.write('<html><head><title>Print Orderan Pelanggan</title>');
							printWindow.document.write(
								'<style>body{font-family:Arial;font-size:12px;} table{width:100%;border-collapse:collapse;margin-top:20px;} th,td{border:1px solid #000;padding:5px;text-align:left;} th{background-color:#f0f0f0;} .text-center{text-align:center;} .text-right{text-align:right;}</style>'
							);
							printWindow.document.write('</head><body>');
							printWindow.document.write('<h2>Orderan Pelanggan</h2>');
							printWindow.document.write('<p>No. Bukti: ' + noBukti + '</p>');
							printWindow.document.write('<p>Tanggal: ' + (response.data[0].tgl || '') + '</p>');
							printWindow.document.write(
								'<table><thead><tr><th class="text-center">No</th><th>Sub</th><th>Kode Bar</th><th>Nama Barang</th><th>Kemasan</th><th class="text-right">Stock TK</th><th class="text-right">Stock GD</th><th class="text-right">Qty</th><th>Ket</th></tr></thead><tbody>'
							);

							response.data.forEach(function(item, index) {
								printWindow.document.write(
									'<tr>' +
									'<td class="text-center">' + (index + 1) + '</td>' +
									'<td>' + (item.sub || '') + '</td>' +
									'<td>' + (item.kdbar || '') + '</td>' +
									'<td>' + (item.na_brg || '') + '</td>' +
									'<td>' + (item.ket_kem || '') + '</td>' +
									'<td class="text-right">' + parseFloat(item.stockr_tk || 0).toFixed(2) +
									'</td>' +
									'<td class="text-right">' + parseFloat(item.stockr || 0).toFixed(2) + '</td>' +
									'<td class="text-right">' + parseFloat(item.qty || 0).toFixed(2) + '</td>' +
									'<td>' + (item.ket || '') + '</td>' +
									'</tr>'
								);
							});

							printWindow.document.write('</tbody></table>');
							printWindow.document.write('<br><p style="margin-top:50px;">Waktu: ' + (response.data[0].timo ||
								'') + '</p>');
							printWindow.document.write('</body></html>');
							printWindow.document.close();
							printWindow.print();
						} else {
							Swal.fire({
								icon: 'info',
								title: 'Info',
								text: 'Tidak ada data untuk dicetak',
								confirmButtonText: 'OK'
							});
						}
					},
					error: function(xhr) {
						let errorMessage = 'Gagal memuat data print';
						if (xhr.responseJSON && xhr.responseJSON.message) {
							errorMessage = xhr.responseJSON.message;
						}

						Swal.fire({
							icon: 'error',
							title: 'Error!',
							text: errorMessage,
							confirmButtonText: 'OK'
						});
					}
				});
			});
		});

		function editData(noBukti) {
			window.location.href = "{{ route('torderanpelanggan.edit') }}?status=edit&no_bukti=" + noBukti;
		}

		function printData(noBukti) {
			$('[data-action="print"][data-id="' + noBukti + '"]').click();
		}

		function deleteData(noBukti) {
			Swal.fire({
				title: 'Konfirmasi Hapus',
				text: 'Apakah Anda yakin ingin menghapus data ini?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Ya, Hapus!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({
						url: "{{ url('torderanpelanggan') }}/" + noBukti,
						type: 'DELETE',
						data: {
							_token: '{{ csrf_token() }}'
						},
						success: function(response) {
							if (response.success) {
								Swal.fire({
									icon: 'success',
									title: 'Berhasil!',
									text: response.message || 'Data berhasil dihapus',
									confirmButtonText: 'OK'
								}).then(() => {
									$('#datatable').DataTable().ajax.reload();
								});
							} else {
								Swal.fire({
									icon: 'error',
									title: 'Gagal!',
									text: response.message || 'Gagal menghapus data',
									confirmButtonText: 'OK'
								});
							}
						},
						error: function(xhr) {
							let errorMessage = 'Gagal menghapus data';
							if (xhr.responseJSON && xhr.responseJSON.message) {
								errorMessage = xhr.responseJSON.message;
							}

							Swal.fire({
								icon: 'error',
								title: 'Error!',
								text: errorMessage,
								confirmButtonText: 'OK'
							});
						}
					});
				}
			});
		}
	</script>
@endsection
