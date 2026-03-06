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
						<h1 class="m-0">Proses Stock Opname</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="/">Home</a></li>
							<li class="breadcrumb-item active">Proses Stock Opname</li>
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
									<div class="col-md-12 d-flex align-items-start flex-wrap gap-2">
										<a href="{{ route('tprosesstockopname.edit', ['status' => 'simpan']) }}" class="btn btn-primary btn-sm">
											<i class="fas fa-plus"></i> New SO
										</a>
										<button id="print-so" type="button" class="btn btn-secondary btn-sm">
											<i class="fas fa-print"></i> Print
										</button>
										<button id="buat-so2" type="button" class="btn btn-success btn-sm">
											<i class="fas fa-file-alt"></i> Buat SO2
										</button>
										<button id="eksport-so" type="button" class="btn btn-info btn-sm text-white">
											<i class="fas fa-download"></i> Export SO
										</button>
										<button id="import-so" type="button" class="btn btn-warning btn-sm">
											<i class="fas fa-upload"></i> Import SO
										</button>
										<button id="cetak-ulang" type="button" class="btn btn-dark btn-sm">
											<i class="fas fa-redo"></i> Cetak Ulang
										</button>
									</div>
								</div>
							</div>
							<div class="card-body">
								<ul class="nav nav-tabs" id="soTab" role="tablist">
									<li class="nav-item">
										<a class="nav-link active" id="so1-tab" data-toggle="tab" href="#tab-so1" role="tab">
											Setelah Buat SO
										</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" id="so2-tab" data-toggle="tab" href="#tab-so2" role="tab">
											Setelah Koreksi
										</a>
									</li>
								</ul>

								<div class="tab-content mt-3">
									<div class="tab-pane fade show active" id="tab-so1" role="tabpanel">
										<table id="datatable-so1" class="table-bordered table-striped table-sm table">
											<thead>
												<tr>
													<th class="text-center"><input type="checkbox" id="check-all-so1"></th>
													<th>No</th>
													<th>No Bukti</th>
													<th>Tanggal</th>
													<th>Sub</th>
													<th>Username</th>
													<th>Posted</th>
													<th>Action</th>
												</tr>
											</thead>
										</table>
									</div>

									<div class="tab-pane fade" id="tab-so2" role="tabpanel">
										<div class="mb-3">
											<a href="{{ route('tprosesstockopname.koreksi', ['status' => 'simpan']) }}" class="btn btn-danger btn-sm">
												<i class="fas fa-edit"></i> Koreksi SO
											</a>
										</div>
										<table id="datatable-so2" class="table-bordered table-striped table-sm table">
											<thead>
												<tr>
													<th class="text-center"><input type="checkbox" id="check-all-so2"></th>
													<th>No</th>
													<th>No Bukti</th>
													<th>Tanggal</th>
													<th>Sub</th>
													<th>Username</th>
													<th>Posted</th>
													<th>Action</th>
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
		</div>
	</div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		$(document).ready(function() {
			let activeTab = 'SO1';

			function loadTable(tipe) {
				activeTab = tipe;
				let tableId = tipe === 'SO1' ? '#datatable-so1' : '#datatable-so2';
				let url = "{{ route('tprosesstockopname.get-data', ['tab' => '__TAB__']) }}".replace('__TAB__', tipe);

				if ($.fn.DataTable.isDataTable(tableId)) {
					$(tableId).DataTable().destroy();
				}

				$(tableId).DataTable({
					processing: true,
					serverSide: true,
					pageLength: 25,
					ajax: {
						url: url,
						error: function(xhr) {
							Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
						}
					},
					columns: [{
							data: 'NO_BUKTI',
							className: 'text-center',
							orderable: false,
							searchable: false,
							render: data => `<input type="checkbox" class="pilih-bukti" value="${data}">`
						},
						{
							data: 'DT_RowIndex',
							orderable: false,
							searchable: false,
							className: 'text-center'
						},
						{
							data: 'NO_BUKTI'
						},
						{
							data: 'TGL',
							className: 'text-center'
						},
						{
							data: 'SUB'
						},
						{
							data: 'USRNM'
						},
						{
							data: 'POSTED',
							className: 'text-center'
						},
						{
							data: 'action',
							orderable: false,
							searchable: false,
							className: 'text-center'
						}
					],
					order: [
						[2, 'desc']
					]
				});
			}

			loadTable('SO1');

			$('#so1-tab').on('click', function() {
				loadTable('SO1');
			});
			$('#so2-tab').on('click', function() {
				loadTable('SO2');
			});

			window.editData = function(noBukti) {
				if (activeTab === 'SO1') {
					window.location.href = "{{ route('tprosesstockopname.edit') }}" + "?status=edit&no_bukti=" + noBukti;
				} else {
					window.location.href = "{{ route('tprosesstockopname.koreksi') }}" + "?status=edit&no_bukti=" + noBukti;
				}
			};

			window.deleteData = function(noBukti) {
				Swal.fire({
					title: 'Hapus Data?',
					text: 'Data akan dihapus permanen',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Ya, Hapus!'
				}).then((result) => {
					if (result.isConfirmed) {
						$.ajax({
							url: "{{ route('tprosesstockopname.delete', '') }}/" + noBukti,
							type: 'DELETE',
							data: {
								_token: '{{ csrf_token() }}'
							},
							success: function(response) {
								Swal.fire('Berhasil!', response.message, 'success');
								$(activeTab === 'SO1' ? '#datatable-so1' : '#datatable-so2').DataTable().ajax.reload();
							},
							error: function(xhr) {
								Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus data', 'error');
							}
						});
					}
				});
			};

			$('#print-so').click(function() {
				let selected = $('.pilih-bukti:checked').map(function() {
					return $(this).val();
				}).get();
				if (selected.length === 0) {
					return Swal.fire('Oops!', 'Pilih minimal 1 No Bukti dulu.', 'warning');
				}
				window.open("{{ route('tprosesstockopname.print') }}" + "?nobukti=" + selected.join(','), "_blank");
			});

			$('#cetak-ulang').click(function() {
				let selected = $('.pilih-bukti:checked').map(function() {
					return $(this).val();
				}).get();
				if (selected.length === 0) {
					return Swal.fire('Oops!', 'Pilih minimal 1 No Bukti dulu.', 'warning');
				}
				window.open("{{ route('tprosesstockopname.print-berualng') }}" + "?nobukti=" + selected.join(','), "_blank");
			});

			$('#buat-so2').click(function() {
				let selected = $('.pilih-bukti:checked').val();
				if (!selected) {
					return Swal.fire('Oops!', 'Pilih 1 No Bukti dulu.', 'warning');
				}
				if (!(selected.startsWith('XO') || selected.startsWith('XG'))) {
					return Swal.fire('Tidak Valid', 'Hanya No Bukti XO atau XG yang dapat diproses.', 'error');
				}

				Swal.fire({
					title: "Yakin?",
					text: "Buat SO2 untuk nomor " + selected + " ?",
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Ya, Buat!"
				}).then((result) => {
					if (result.isConfirmed) {
						$.ajax({
							url: "{{ route('tprosesstockopname.buat-so2') }}",
							type: "POST",
							data: {
								_token: "{{ csrf_token() }}",
								no_bukti: selected
							},
							success: function(res) {
								if (res.success) {
									Swal.fire("Berhasil!", "SO2 baru dibuat: " + res.bukti_baru, "success");
									loadTable(activeTab);
								} else {
									Swal.fire("Gagal", res.message, "error");
								}
							},
							error: function() {
								Swal.fire("Error", "Terjadi kesalahan server.", "error");
							}
						});
					}
				});
			});

			$('#eksport-so').click(function() {
				let selected = $('.pilih-bukti:checked').val();
				if (!selected) {
					return Swal.fire('Oops!', 'Pilih 1 NoBukti dulu.', 'warning');
				}
				Swal.fire({
					title: 'Export SO?',
					text: 'Export data untuk ' + selected,
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya, Export!'
				}).then((result) => {
					if (result.isConfirmed) {
						$.ajax({
							url: "{{ route('tprosesstockopname.export') }}",
							type: "POST",
							data: {
								_token: "{{ csrf_token() }}",
								no_bukti: selected
							},
							success: function(res) {
								Swal.fire('Berhasil!', res.message, 'success');
							},
							error: function(xhr) {
								Swal.fire('Error', xhr.responseJSON?.message || 'Gagal export', 'error');
							}
						});
					}
				});
			});
			$('#import-so').click(function() {
				Swal.fire({
					title: 'Import SO',
					input: 'text',
					inputLabel: 'Nama file (tanpa .txt):',
					inputPlaceholder: 'SO2501-0001T',
					showCancelButton: true,
					confirmButtonText: 'Import',
					preConfirm: (namafile) => {
						if (!namafile) {
							Swal.showValidationMessage('Nama file harus diisi');
							return false;
						}
						return namafile;
					}
				}).then((result) => {
					if (result.isConfirmed) {
						$.ajax({
							url: "{{ route('tprosesstockopname.import') }}",
							type: "POST",
							data: {
								_token: "{{ csrf_token() }}",
								namafile: result.value
							},
							success: function(res) {
								if (res.confirm) {
									Swal.fire({
										title: 'Konfirmasi',
										text: res.message,
										icon: 'warning',
										showCancelButton: true,
										confirmButtonText: 'Ya, Timpa!'
									}).then((confirm) => {
										if (confirm.isConfirmed) {
											$.ajax({
												url: "{{ route('tprosesstockopname.import') }}",
												type: "POST",
												data: {
													_token: "{{ csrf_token() }}",
													namafile: result.value,
													force: true
												},
												success: function(res2) {
													Swal.fire('Berhasil!', res2.message, 'success');
													loadTable(activeTab);
												}
											});
										}
									});
								} else {
									Swal.fire(res.success ? 'Berhasil!' : 'Gagal', res.message, res.success ? 'success' :
										'error');
									if (res.success) loadTable(activeTab);
								}
							},
							error: function(xhr) {
								Swal.fire('Error', xhr.responseJSON?.message || 'Gagal import', 'error');
							}
						});
					}
				});
			});
		});
	</script>
@endsection
