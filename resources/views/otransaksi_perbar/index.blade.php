@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
	<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0">Perubahan Barcode</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Perubahan Barcode</li>
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
						<div class="card-body d-flex justify-content-center">
						<form method="POST" action="{{url('perbar/proses')}}" class="w-50"> {{-- w-50 = lebar 50% --}}
							@csrf
							<div class="form-group row">
								<div class="col-md-12">
									<input type="text" class="form-control" id="KD_BRG" name="KD_BRG" placeholder="Masukkan Kode Barang">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-12">
									<input type="text" class="form-control" id="NA_BRG" name="NA_BRG" placeholder="Nama Barang" readonly>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-12">
									<input type="text" class="form-control" id="BARCODE" name="BARCODE" placeholder="Barcode Lama" readonly>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-12">
									<input type="text" class="form-control" id="BARCODE2" name="BARCODE2" placeholder="Barcode Baru">
								</div>
							</div>
							<div class="form-group row justify-content-center">
								<div class="col-md-auto">
									<button type="button" id="btnProses" class="btn btn-primary btn-lg">Proses</button>
								</div>
							</div>
						</form>
					</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('javascripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script>
	$(document).ready(function () {
		$('#KD_BRG').keypress(function (e) {
			if (e.which == 13) { // Enter ditekan
				e.preventDefault();
				var kode = $(this).val();

				if (kode == '') return;

				$.ajax({
					url: "{{ route('get-barang') }}",
					type: "GET",
					data: { KD_BRG: kode },
					success: function (response) {
						if (response.success) {
							$('#NA_BRG').val(response.data.NA_BRG);
							$('#BARCODE').val(response.data.BARCODE);
						} else {
							Swal.fire({
								icon: 'warning',
								title: 'Tidak ditemukan',
								text: response.message,
								timer: 1500,
								showConfirmButton: false
							});
							$('#NA_BRG').val('');
							$('#BARCODE').val('');
						}
					},
					error: function () {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Terjadi kesalahan saat mengambil data barang.',
						});
					}
				});
			}
		});

		// 🔹 Tombol Proses ditekan
		$('#btnProses').click(function (e) {
			e.preventDefault();

			let kd = $('#KD_BRG').val();
			let barcode2 = $('#BARCODE2').val();

			if (kd === '' || barcode2 === '') {
				Swal.fire({
					icon: 'warning',
					title: 'Data belum lengkap!',
					text: 'Isi Kode Barang dan Barcode Baru terlebih dahulu.',
				});
				return;
			}

			Swal.fire({
				title: 'Apakah anda yakin?',
				text: "Barcode barang ini akan diperbarui!",
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Ya, update!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					// Submit form manual jika user pilih "Ya"
					$('form').submit();
				} else {
					// Jika batal, kembali ke halaman perbar
					window.location.href = "{{ url('perbar') }}";
				}
			});
		});
	});
</script>
<script>
	@if(session('success'))
	Swal.fire({
		icon: 'success',
		title: 'BARCODE TELAH DIPERBAHARUI',
		text: "{{ session('success') }}",
		timer: 1500,
		showConfirmButton: false
	});
	@endif

	@if(session('error'))
	Swal.fire({
		icon: 'error',
		title: 'Gagal!',
		text: "{{ session('error') }}",
	});
	@endif
	</script>
@endsection