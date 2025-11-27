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
									<div class="col-md-3">
										<label>Program</label>
										<input type="text" class="form-control" id="txtbukti" value="{{ $kode }}" readonly>
									</div>
									<div class="col-md-3">
										<label>&nbsp;</label><br>
										<button type="button" class="btn {{ $aktif == 1 ? 'btn-success' : 'btn-secondary' }}" id="btaktif">
											{{ $aktif == 1 ? 'AKTIF' : 'NON-AKTIF' }}
										</button>
									</div>
									<div class="col-md-6 text-right">
										<label>&nbsp;</label><br>
										<button type="button" class="btn btn-danger" id="btnClearNomor">
											<i class="fas fa-trash mr-1"></i>Hapus Data Permanen Kasir
										</button>
									</div>
								</div>

								<hr>

								<div class="row mb-3">
									<div class="col-md-2">
										<label>Supp</label>
										<input type="text" class="form-control" id="txtsup1" placeholder="Dari">
									</div>
									<div class="col-md-2">
										<label>&nbsp;</label>
										<input type="text" class="form-control" id="txtsup2" placeholder="Sampai">
									</div>
									<div class="col-md-2">
										<label>Sub</label>
										<input type="text" class="form-control" id="txtsub1" placeholder="Dari">
									</div>
									<div class="col-md-2">
										<label>&nbsp;</label>
										<input type="text" class="form-control" id="txtsub2" placeholder="Sampai">
									</div>
									<div class="col-md-2">
										<label>No Item</label>
										<input type="text" class="form-control" id="txtnoitem">
									</div>
									<div class="col-md-2">
										<label>&nbsp;</label><br>
										<button type="button" class="btn btn-primary btn-block" id="btnTambah">
											<i class="fas fa-plus mr-1"></i>Tambahkan
										</button>
									</div>
								</div>

								<div class="row mb-3">
									<div class="col-md-12 text-right">
										<button type="button" class="btn btn-warning" id="btnHapusSemua">
											<i class="fas fa-times mr-1"></i>Hapus Semua
										</button>
									</div>
								</div>

								<hr>

								<div class="row">
									<div class="col-12">
										<table id="tableMasks" class="table-bordered table-striped table">
											<thead>
												<tr>
													<th>Kode Barang</th>
													<th>Nama Barang</th>
												</tr>
											</thead>
											<tbody>
												@foreach ($masks as $mask)
													<tr>
														<td>{{ $mask->kd_brg }}</td>
														<td>{{ $mask->na_brg }}</td>
													</tr>
												@endforeach
											</tbody>
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
	<script>
		$(document).ready(function() {
			var table = $('#tableMasks').KoolDataTable({
				responsive: true,
				pageLength: 25,
				order: [
					[0, 'asc']
				]
			});

			$('#btaktif').on('click', function() {
				var aktif = $(this).text().trim() == 'AKTIF' ? 0 : 1;
				var msg = aktif == 1 ? 'meng"AKTIF"kan' : 'me"NON-AKTIF"kan';

				if (confirm('Apakah anda yakin ingin ' + msg + '?')) {
					$.ajax({
						url: "{{ route('phundiansupplier.save-config') }}",
						method: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							action: 'aktif',
							kode: $('#txtbukti').val(),
							aktif: aktif
						},
						success: function(response) {
							if (response.success) {
								alert(response.message);
								location.reload();
							} else {
								alert('Error: ' + response.message);
							}
						}
					});
				}
			});

			$('#btnClearNomor').on('click', function() {
				if (confirm('Apakah anda yakin ingin membersihkan penomoran hadiah di kasir?')) {
					$.ajax({
						url: "{{ route('phundiansupplier.save-config') }}",
						method: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							action: 'clear_nomor'
						},
						success: function(response) {
							if (response.success) {
								alert(response.message);
							} else {
								alert('Error: ' + response.message);
							}
						}
					});
				}
			});

			$('#btnTambah').on('click', function() {
				var sub1 = $('#txtsub1').val();
				var sub2 = $('#txtsub2').val();
				var sup1 = $('#txtsup1').val();
				var sup2 = $('#txtsup2').val();

				if (!sub1 || !sub2 || !sup1 || !sup2) {
					alert('Harap lengkapi field Supp dan Sub');
					return;
				}

				$.ajax({
					url: "{{ route('phundiansupplier.save-config') }}",
					method: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						action: 'tambah',
						sub1: sub1,
						sub2: sub2,
						sup1: sup1,
						sup2: sup2,
						noitem: $('#txtnoitem').val()
					},
					success: function(response) {
						if (response.success) {
							alert(response.message);
							location.reload();
						} else {
							alert('Error: ' + response.message);
						}
					}
				});
			});

			$('#btnHapusSemua').on('click', function() {
				if (confirm('Apakah anda yakin ingin menghapus semua data?')) {
					$.ajax({
						url: "{{ route('phundiansupplier.save-config') }}",
						method: 'POST',
						data: {
							_token: '{{ csrf_token() }}',
							action: 'hapus_semua'
						},
						success: function(response) {
							if (response.success) {
								alert(response.message);
								location.reload();
							} else {
								alert('Error: ' + response.message);
							}
						}
					});
				}
			});
		});
	</script>
@endsection
