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
									<div class="col-md-4">
										<label>Upload File CSV</label>
										<input type="file" class="form-control" id="csv_file" accept=".csv">
									</div>
									<div class="col-md-3">
										<label>Periode</label>
										<input type="text" class="form-control" id="txtperiode" value="{{ date('m/Y') }}" readonly>
									</div>
								</div>

								<div class="row mb-3">
									<div class="col-md-12">
										<button type="button" class="btn btn-primary" id="btnProcess">
											<i class="fas fa-cog mr-1"></i>Process
										</button>
										<button type="button" class="btn btn-secondary ml-2" id="btnClose">
											<i class="fas fa-times mr-1"></i>Close
										</button>
									</div>
								</div>

								<div class="row">
									<div class="col-md-12">
										<table id="tblStock" class="table-bordered table-striped table">
											<thead>
												<tr>
													<th>Kode Barang</th>
													<th>Stok</th>
												</tr>
											</thead>
											<tbody>
												@foreach ($stocks as $stock)
													<tr>
														<td>{{ $stock->kd_brg }}</td>
														<td>{{ $stock->stok }}</td>
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
			$('#tblStock').DataTable({
				responsive: true,
				autoWidth: false,
				pageLength: 25
			});

			$('#btnProcess').on('click', function() {
				var fileInput = $('#csv_file')[0];
				var periode = $('#txtperiode').val().trim();

				if (!fileInput.files.length) {
					alert('Pilih file CSV terlebih dahulu');
					return;
				}

				var formData = new FormData();
				formData.append('csv_file', fileInput.files[0]);
				formData.append('periode', periode);
				formData.append('_token', '{{ csrf_token() }}');

				$.ajax({
					url: "{{ route('phkasir13.save-config') }}",
					method: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						if (response.success) {
							alert(response.message + '\nNo Bukti: ' + response.bukti);
							location.reload();
						} else {
							alert(response.message);
						}
					},
					error: function() {
						alert('Terjadi kesalahan saat memproses data');
					}
				});
			});

			$('#btnClose').on('click', function() {
				window.location.href = "{{ url('/') }}";
			});
		});
	</script>
@endsection
