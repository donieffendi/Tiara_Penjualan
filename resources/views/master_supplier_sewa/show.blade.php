@extends('layouts.plain')

<style>
	.card {}

	.form-control:focus {
		background-color: #E0FFFF !important;
	}
</style>

@section('content')
	<div class="content-wrapper">
		<!-- Content Header (Page header) -->
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Data Supplier Sewa</h1>
					</div>

				</div><!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>
		<!-- /.content-header -->

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">

								<form
                                    action="{{ $tipx == 'show' ? url('/sup-sewa/store/') : url('/sup-sewa/update/' . $header->NO_ID) }}"
                                    method="POST" name ="entri" id="entri">
									
									@csrf

									<div class="tab-content mt-3">
										<div class="tab-pane active">

											<div class="form-group row">
												<div class="col-md-1">
													<label for="KODES" class="form-label">Kode</label>
												</div>

												<input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID" placeholder="Masukkan NO_ID"
													value="{{ $header->NO_ID ?? '' }}" hidden readonly>

												<input name="tipx" class="form-control tipx" id="tipx" value="{{ $tipx }}" hidden>

												<div class="col-md-2">
													<input type="text" class="form-control KODES" id="KODES" name="KODES" placeholder="Masukkan Kode"
														value="{{ $header->KODES ?? '' }}" readonly>
												</div>
												<div class="col-md-1">
													<label for="KTP" class="form-label">No.KTP</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control KTP" id="KTP" name="KTP" placeholder="Masukkan No.KTP"
														value="{{ $header->KTP ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="NAMAS" class="form-label">Nama</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control NAMAS" id="NAMAS" name="NAMAS" placeholder="Masukkan Nama"
														value="{{ $header->NAMAS ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="KD_DISTRIBUTOR" class="form-label">Kode Distributor</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control KD_DISTRIBUTOR" id="KD_DISTRIBUTOR" name="KD_DISTRIBUTOR"
														placeholder="Masukkan Kode Distributor" value="{{ $header->KD_DISTRIBUTOR ?? '' }}" readonly>
												</div>
												<div class="col-md-4">
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="PRODUK" class="form-label">Jenis Produk</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control PRODUK" id="PRODUK" name="PRODUK" placeholder="Masukkan Jenis Produk"
														value="{{ $header->PRODUK ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="AL_PRSH" class="form-label">Alamat Kantor</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control AL_PRSH" id="AL_PRSH" name="AL_PRSH" placeholder="Masukkan Alamat"
														value="{{ $header->AL_PRSH ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="AL_PRSH2" class="form-label">Alamat Rumah</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control AL_PRSH2" id="AL_PRSH2" name="AL_PRSH2" placeholder="Masukkan Alamat"
														value="{{ $header->AL_PRSH2 ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="KOTA" class="form-label">Kota</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control KOTA" id="KOTA" name="KOTA" placeholder="Masukkan Kota"
														value="{{ $header->KOTA ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="NO_TELP" class="form-label">No Telp</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control NO_TELP" id="NO_TELP" name="NO_TELP" placeholder="Masukkan No Telp"
														value="{{ $header->NO_TELP ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="S_PJK" class="form-label">Status Pajak</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control S_PJK" id="S_PJK" name="S_PJK" placeholder="Masukkan Status Pajak"
														value="{{ $header->S_PJK ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="NPWP" class="form-label">NPWP</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control NPWP" id="NPWP" name="NPWP" placeholder="Masukkan NPWP"
														value="{{ $header->NPWP ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="CARA_BYR" class="form-label">Cara Bayar 1</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control CARA_BYR" id="CARA_BYR" name="CARA_BYR" placeholder="Masukkan Cara Bayar"
														value="{{ $header->CARA_BYR ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="CARA_BYR2" class="form-label">Cara Bayar 2</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control CARA_BYR2" id="CARA_BYR2" name="CARA_BYR2" placeholder="Masukkan Cara Bayar"
														value="{{ $header->CARA_BYR2 ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="KET" class="form-label">Keterangan</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control KET" id="KET" name="KET" placeholder="Masukkan Keterangan"
														value="{{ $header->KET ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="EMAIL" class="form-label">Email</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control EMAIL" id="EMAIL" name="EMAIL" placeholder="Masukkan Email"
														value="{{ $header->EMAIL ?? '' }}" readonly>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12 form-group row mt-3">
										<div class="col-md-4">
											<button type="button" id='TOPX' onclick="location.href='{{ url('/sup-sewa/show/?idx=' . $idx . '&tipx=top') }}'"
												class="btn btn-outline-primary">Top</button>
											<button type="button" id='PREVX'
												onclick="location.href='{{ url('/sup-sewa/show/?idx=' . $header->NO_ID . '&tipx=prev&kodex=' . $header->KODES) }}'"
												class="btn btn-outline-primary">Prev</button>
											<button type="button" id='NEXTX'
												onclick="location.href='{{ url('/sup-sewa/show/?idx=' . $header->NO_ID . '&tipx=next&kodex=' . $header->KODES) }}'"
												class="btn btn-outline-primary">Next</button>
											<button type="button" id='BOTTOMX' onclick="location.href='{{ url('/sup-sewa/show/?idx=' . $idx . '&tipx=bottom') }}'"
												class="btn btn-outline-primary">Bottom</button>
										</div>
										<div class="col-md-5">

										</div>
										<div class="col-md-3">
											<button type="button" id='CLOSEX' onclick="location.href='{{ url('/sup-sewa') }}'" class="btn btn-outline-secondary">Close</button>
										</div>
									</div>

								</form>
							</div>
						</div>
						<!-- /.card -->
					</div>
				</div>
				<!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>
		<!-- /.content -->
	@endsection

	@section('footer-scripts')
		<script>
			var target;
			var idrow = 1;

			$(document).ready(function() {
				$tipx = $('#tipx').val();

				// Disable semua input dan form submission
				mati();
			});

			function mati() {
				$("#TOPX").attr("disabled", false);
				$("#PREVX").attr("disabled", false);
				$("#NEXTX").attr("disabled", false);
				$("#BOTTOMX").attr("disabled", false);
				$("#CLOSEX").attr("disabled", false);

				// Semua field readonly
				$("#KODES").attr("readonly", true);
				$("#NAMAS").attr("readonly", true);
				$("#KTP").attr("readonly", true);
				$("#KD_DISTRIBUTOR").attr("readonly", true);
				$("#PRODUK").attr("readonly", true);
				$("#AL_PRSH").attr("readonly", true);
				$("#AL_PRSH2").attr("readonly", true);
				$("#KOTA").attr("readonly", true);
				$("#NO_TELP").attr("readonly", true);
				$("#S_PJK").attr("readonly", true);
				$("#NPWP").attr("readonly", true);
				$("#CARA_BYR").attr("readonly", true);
				$("#CARA_BYR2").attr("readonly", true);
				$("#KET").attr("readonly", true);
				$("#EMAIL").attr("readonly", true);
			}

			function CariBukti() {
				var cari = $("#CARI").val();
				var loc = "{{ url('/sup-sewa/show/') }}" + '?idx={{ $header->NO_ID }}&tipx=search&kodex=' +
					encodeURIComponent(cari);
				window.location = loc;
			}
		</script>
	@endsection
