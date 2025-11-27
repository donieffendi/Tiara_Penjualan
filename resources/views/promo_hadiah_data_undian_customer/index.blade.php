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
										<label>PIN</label>
										<input type="text" class="form-control" id="txtpin" placeholder="Masukkan PIN" autofocus>
									</div>
								</div>

								<div class="row mb-3">
									<div class="col-md-4">
										<label>Kode Customer</label>
										<input type="text" class="form-control" id="txtkodec" placeholder="Scan Member">
									</div>
									<div class="col-md-6">
										<label>Nama Customer</label>
										<input type="text" class="form-control" id="txtnamac" readonly>
									</div>
								</div>

								<div class="row">
									<div class="col-md-12">
										<button type="button" class="btn btn-primary" id="btnProcess">
											<i class="fas fa-save mr-1"></i>Process
										</button>
										<button type="button" class="btn btn-secondary ml-2" id="btnClose">
											<i class="fas fa-times mr-1"></i>Close
										</button>
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
			var aktif = 0;

			$('#txtpin').on('blur', function() {
				var pin = $(this).val().trim();

				if (pin == '') {
					return;
				}

				$.ajax({
					url: "{{ route('phdataundiancustomer.get-config') }}",
					method: 'GET',
					data: {
						pin: pin
					},
					success: function(response) {
						if (response.success) {
							aktif = 1;
							alert('Ada kupon sejumlah: ' + response.count);
							$('#txtkodec').focus();
						} else {
							aktif = 0;
							alert(response.message);
							$('#txtpin').val('');
							$('#txtpin').focus();
						}
					},
					error: function() {
						alert('Terjadi kesalahan saat memeriksa PIN');
					}
				});
			});

			$('#txtkodec').on('blur', function() {
				var kodec = $(this).val().trim();

				if (kodec == '') {
					return;
				}

				$.ajax({
					url: "{{ route('phdataundiancustomer.check-customer') }}",
					method: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						kodec: kodec
					},
					success: function(response) {
						if (response.success) {
							$('#txtnamac').val(response.namac);
						} else {
							alert(response.message);
							$('#txtkodec').val('');
							$('#txtkodec').focus();
						}
					},
					error: function() {
						alert('Kesalahan Koneksi');
					}
				});
			});

			$('#btnProcess').on('click', function() {
				if (aktif != 1) {
					alert('Tidak bisa disimpan, terjadi kesalahan...');
					return;
				}

				var pin = $('#txtpin').val().trim();
				var kodec = $('#txtkodec').val().trim();
				var namac = $('#txtnamac').val().trim();

				if (pin == '' || kodec == '' || namac == '') {
					alert('Harap lengkapi semua field');
					return;
				}

				$.ajax({
					url: "{{ route('phdataundiancustomer.save-config') }}",
					method: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						pin: pin,
						kodec: kodec,
						namac: namac
					},
					success: function(response) {
						if (response.success) {
							alert(response.message);
							$('#txtpin').val('');
							$('#txtkodec').val('');
							$('#txtnamac').val('');
							aktif = 0;
							$('#txtpin').focus();
						} else {
							alert(response.message);
						}
					},
					error: function() {
						alert('Tidak bisa disimpan, terjadi kesalahan...');
					}
				});
			});

			$('#btnClose').on('click', function() {
				window.location.href = "{{ url('/') }}";
			});

			$(document).on('keydown', function(e) {
				if (e.which == 13) {
					var focused = $(':focus');
					if (focused.attr('id') == 'txtpin') {
						$('#txtkodec').focus();
					} else if (focused.attr('id') == 'txtkodec') {
						$('#btnProcess').focus();
					} else if (focused.attr('id') == 'btnProcess') {
						$('#btnProcess').click();
					}
					e.preventDefault();
				}
			});
		});
	</script>
@endsection
