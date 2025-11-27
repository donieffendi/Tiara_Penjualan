@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $pageTitle }}</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="/">Home</a></li>
							<li class="breadcrumb-item active">{{ $pageTitle }}</li>
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
										<a href="{{ route($menuType == 'FS' ? 'tentryflashsale.edit' : 'tobralsupermarket.edit', ['status' => 'simpan']) }}" class="btn btn-primary">
											<i class="fas fa-plus"></i> New
										</a>
									</div>
									<div class="col-md-6 text-right">
										<div class="form-group mb-0">
											<label class="mr-2">Sub Item:</label>
											<input type="text" id="txt_dis" class="form-control form-control-sm d-inline-block" style="width: 150px;" placeholder="Kode Barang">
										</div>
									</div>
								</div>
							</div>
							<div class="card-body">
								<!-- Info Diskon -->
								<div class="row mb-3" id="dis-info-container" style="display: none;">
									<div class="col-md-12">
										<div class="alert alert-info">
											<div class="row">
												<div class="col-md-3">
													<strong>Dis TGZ:</strong> <span id="dis_tgz">-</span>
												</div>
												<div class="col-md-3">
													<strong>Dis TMM:</strong> <span id="dis_tmm">-</span>
												</div>
												<div class="col-md-3">
													<strong>Dis SOP:</strong> <span id="dis_sop">-</span>
												</div>
											</div>
											<div class="row mt-2">
												<div class="col-md-3">
													<strong>Jam Mulai:</strong> <span id="jam_mulai">-</span>
												</div>
												<div class="col-md-3">
													<strong>Jam Akhir:</strong> <span id="jam_akhir">-</span>
												</div>
												<div class="col-md-3">
													<strong>Tgl Mulai:</strong> <span id="tgl_mulai">-</span>
												</div>
												<div class="col-md-3">
													<strong>Tgl Akhir:</strong> <span id="tgl_akhir">-</span>
												</div>
											</div>
										</div>
									</div>
								</div>

								<table id="datatable" class="table-bordered table-striped table-sm table">
									<thead>
										<tr>
											<th width="5%">No</th>
											<th width="20%">No Bukti</th>
											<th width="15%">Tgl Selesai</th>
											<th width="10%">Supplier</th>
											<th width="20%">Nama</th>
											<th width="20%">Catatan</th>
											<th width="10%">Posted</th>
											<th width="15%">Action</th>
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
			var menuType = '{{ $menuType }}';
			var routePrefix = menuType == 'FS' ? 'tentryflashsale' : 'tobralsupermarket';

			var table = $('#datatable').DataTable({
				processing: true,
				serverSide: true,
				pageLength: 25,
				ajax: {
					url: "{{ route($menuType == 'FS' ? 'tentryflashsale.get-data' : 'tobralsupermarket.get-data') }}",
					error: function(xhr, error, code) {
						console.error('DataTables AJAX error:', xhr.responseJSON);
						Swal.fire({
							icon: 'error',
							title: 'Error Loading Data',
							text: xhr.responseJSON?.message || xhr.responseJSON?.error || 'Terjadi kesalahan saat memuat data',
							footer: 'Silakan periksa log atau hubungi administrator'
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
						data: 'TGL_SLS',
						name: 'TGL_SLS',
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
						data: 'POSTED',
						name: 'POSTED',
						className: 'text-center'
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
				]
			});

			// Session messages
			@if (session('success'))
				Swal.fire({
					icon: 'success',
					title: 'Berhasil!',
					text: '{{ session('success') }}',
					timer: 3000,
					showConfirmButton: false
				});
			@endif

			@if (session('error'))
				Swal.fire({
					icon: 'error',
					title: 'Error!',
					text: '{{ session('error') }}'
				});
			@endif

			// Cek info diskon barang saat Enter ditekan
			$('#txt_dis').on('keypress', function(e) {
				if (e.which === 13 || e.keyCode === 13) {
					e.preventDefault();

					var kdBrg = $(this).val().trim();

					if (kdBrg === '') {
						$('#dis-info-container').hide();
						Swal.fire({
							icon: 'warning',
							title: 'Peringatan',
							text: 'Silakan masukkan kode barang terlebih dahulu'
						});
						return;
					}

					// Show loading
					Swal.fire({
						title: 'Memuat Info Diskon...',
						text: 'Mohon tunggu',
						allowOutsideClick: false,
						didOpen: () => {
							Swal.showLoading();
						}
					});

					$.ajax({
						url: "{{ route('tobralsupermarket.get-diskon-info') }}",
						method: 'GET',
						data: {
							kd_brg: kdBrg
						},
						success: function(response) {
							Swal.close();

							if (response.success && response.data) {
								// Tampilkan info diskon
								$('#dis_tgz').text(response.data.disgz || '0');
								$('#dis_tmm').text(response.data.dismm || '0');
								$('#dis_sop').text(response.data.dissp || '0');
								$('#jam_mulai').text(response.data.jam || '-');
								$('#jam_akhir').text(response.data.jamsls || '-');
								$('#tgl_mulai').text(response.data.tgdis_m || '-');
								$('#tgl_akhir').text(response.data.tgdis_a || '-');
								$('#dis-info-container').show();

								// Success notification
								Swal.fire({
									icon: 'success',
									title: 'Data Ditemukan',
									text: 'Kode: ' + kdBrg + ' - ' + (response.data.na_brg || ''),
									timer: 2000,
									showConfirmButton: false
								});
							} else {
								$('#dis-info-container').hide();
								Swal.fire({
									icon: 'info',
									title: 'Tidak Ditemukan',
									text: response.message || 'Kode Barang tidak ditemukan'
								});
							}
						},
						error: function(xhr) {
							Swal.close();
							$('#dis-info-container').hide();

							var errorMsg = 'Gagal mengambil info diskon';
							if (xhr.responseJSON && xhr.responseJSON.message) {
								errorMsg = xhr.responseJSON.message;
							}

							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: errorMsg
							});
						}
					});
				}
			});
		});

		function editData(noBukti) {
			var menuType = '{{ $menuType }}';
			var routeName = menuType == 'FS' ? 'tentryflashsale.edit' : 'tobralsupermarket.edit';
			window.location.href = "{{ url('/') }}/" + (menuType == 'FS' ? 'tentryflashsale' : 'tobralsupermarket') + "/edit?status=edit&no_bukti=" +
				noBukti;
		}

		function deleteData(noBukti) {
			var menuType = '{{ $menuType }}';
			var routeName = menuType == 'FS' ? 'tentryflashsale.delete' : 'tobralsupermarket.delete';

			Swal.fire({
				title: 'Hapus Data?',
				text: 'Apakah kode ini ' + noBukti + ' akan di hapus?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Ya, Hapus!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({
						url: "{{ url('/') }}/" + (menuType == 'FS' ? 'tentryflashsale' : 'tobralsupermarket') + "/delete/" + noBukti,
						type: 'DELETE',
						data: {
							_token: '{{ csrf_token() }}'
						},
						success: function(response) {
							Swal.fire('Berhasil!', response.message, 'success');
							$('#datatable').DataTable().ajax.reload();
						},
						error: function(xhr) {
							Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus data', 'error');
						}
					});
				}
			});
		}

		function printData(noBukti) {
			var menuType = '{{ $menuType }}';

			$.ajax({
				url: "{{ url('/') }}/" + (menuType == 'FS' ? 'tentryflashsale' : 'tobralsupermarket') + "/print",
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					no_bukti: noBukti
				},
				success: function(response) {
					if (response.success && response.data && response.data.length > 0) {
						var printWindow = window.open('', '_blank');
						printWindow.document.write('<html><head><title>Print ' + '{{ $pageTitle }}' + '</title>');
						printWindow.document.write(
							'<style>body{font-family:Arial;font-size:12px;} table{width:100%;border-collapse:collapse;margin-top:20px;} th,td{border:1px solid #000;padding:5px;} th{background-color:#f0f0f0;} .text-center{text-align:center;} .text-right{text-align:right;}</style>'
						);
						printWindow.document.write('</head><body>');
						printWindow.document.write('<h2>' + '{{ $pageTitle }}' + '</h2>');
						printWindow.document.write('<p><strong>No. Bukti:</strong> ' + noBukti + '</p>');
						printWindow.document.write('<p><strong>Supplier:</strong> ' + (response.data[0].kodes || '') + ' - ' + (response.data[0]
							.namas || '') + '</p>');
						printWindow.document.write(
							'<table><thead><tr><th>No</th><th>Kode</th><th>Nama Barang</th><th>Ukuran</th><th>Diskon</th><th>Nilai</th><th>Ket</th></tr></thead><tbody>'
						);

						response.data.forEach(function(item, index) {
							printWindow.document.write(
								'<tr>' +
								'<td class="text-center">' + (index + 1) + '</td>' +
								'<td>' + (item.kd_brg || '') + '</td>' +
								'<td>' + (item.na_brg || '') + '</td>' +
								'<td>' + (item.ket_uk || '') + '</td>' +
								'<td class="text-right">' + parseFloat(item.dis || 0).toFixed(2) + '%</td>' +
								'<td class="text-right">' + parseFloat(item.th || 0).toFixed(0) + '</td>' +
								'<td>' + (item.ket || '') + '</td>' +
								'</tr>'
							);
						});

						printWindow.document.write('</tbody></table></body></html>');
						printWindow.document.close();
						printWindow.print();
					} else {
						Swal.fire('Info', 'Tidak ada data untuk dicetak', 'info');
					}
				},
				error: function(xhr) {
					Swal.fire('Error', xhr.responseJSON?.message || 'Gagal memuat data print', 'error');
				}
			});
		}
	</script>
@endsection
