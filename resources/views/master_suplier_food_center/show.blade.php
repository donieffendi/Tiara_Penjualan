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
						<h1 class="m-0">Data Suplier Food Center</h1>
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

								<form action="#" method="POST" name ="entri" id="entri" onsubmit="return false;">

									@csrf

									<ul class="nav nav-tabs">
										<li class="nav-item active">
											<a class="nav-link active" href="#suppInfo" data-toggle="tab">Supp Info</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" href="#bankInfo" data-toggle="tab">Bank Info</a>
										</li>
									</ul>

									<div class="tab-content mt-3">
										<div id="suppInfo" class="tab-pane active">

											<div class="form-group row">
												<div class="col-md-1">
													<label for="KODES" class="form-label">Kode</label>
												</div>

												<input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID" placeholder="Masukkan NO_ID"
													value="{{ $header->NO_ID ?? '' }}" hidden readonly>

												<input name="tipx" class="form-control flagz" id="tipx" value="{{ $tipx }}" hidden>

												<div class="col-md-2">
													<input type="text" class="form-control KODES" id="KODES" name="KODES" placeholder="Masukkan Kode Suplier"
														value="{{ $header->KODES ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="NAMAS" class="form-label">Nama</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control NAMAS" id="NAMAS" name="NAMAS" placeholder="Masukkan Nama Suplier"
														value="{{ $header->NAMAS ?? '' }}" readonly>
												</div>
												<div class="col-md-1">
													<label for="NAMAS_LM" class="form-label">Nama Lama</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control NAMAS_LM" id="NAMAS_LM" name="NAMAS_LM" placeholder="Masukkan Nama Suplier Lama"
														value="{{ $header->NAMAS_LM ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="ALAMAT" class="form-label">Alamat</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control ALAMAT" id="ALAMAT" name="ALAMAT" placeholder="Masukkan Alamat"
														value="{{ $header->ALAMAT ?? '' }}" readonly>
												</div>

												<div class="col-md-2">
													<input type="text" class="form-control KOTA" id="KOTA "name="KOTA" placeholder="Masukkan Kota" value="{{ $header->KOTA ?? '' }}"
														readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1" align="left">
													<label for="TELPON1" class="form-label">Telpon</label>
												</div>
												<div class="col-md-2">
													<input type="text" class="form-control TELPON1" id="TELPON1" name="TELPON1" placeholder="" value="{{ $header->TELPON1 ?? '' }}"
														readonly>
												</div>

												<div class="col-md-2">
													<label for="GOL" class="form-label">Golongan</label>
												</div>
												<div class="col-md-2">
													<select id="GOL" class="form-control" name="GOL" disabled>
														<option value="Y" {{ $header->GOL == 'Y' ? 'selected' : '' }}>Y</option>
														<option value="Z" {{ $header->GOL == 'Z' ? 'selected' : '' }}>Z</option>
													</select>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1" align="left">
													<label for="FAX" class="form-label">Fax</label>
												</div>
												<div class="col-md-2">
													<input type="text" class="form-control FAX" id="FAX" name="FAX" placeholder="" value="{{ $header->FAX ?? '' }}"
														readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1" align="left">
													<label for="HP" class="form-label">HP</label>
												</div>
												<div class="col-md-2">
													<input type="text" class="form-control HP" id="HP" name="HP" placeholder="" value="{{ $header->HP ?? '' }}"
														readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1" align="left">
													<label for="KONTAK" class="form-label">Kontak</label>
												</div>
												<div class="col-md-2">
													<input type="text" class="form-control KONTAK" id="KONTAK" name="KONTAK" placeholder="" value="{{ $header->KONTAK ?? '' }}"
														readonly>
												</div>

												<div class="col-md-1" align="right">
													<label for="EMAIL" class="form-label">Email</label>
												</div>
												<div class="col-md-2">
													<input type="text" class="form-control EMAIL" id="EMAIL" name="EMAIL" placeholder="" value="{{ $header->EMAIL ?? '' }}"
														readonly>
												</div>

												<div class="col-md-1" align="right">
													<label for="NPWP" class="form-label">NPWP</label>
												</div>
												<div class="col-md-2">
													<input type="text" class="form-control NPWP" id="NPWP" name="NPWP" placeholder="" value="{{ $header->NPWP ?? '' }}"
														readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1" align="left">
													<label for="KET" class="form-label">Ket</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control KET" id="KET" name="KET" placeholder="" value="{{ $header->KET ?? '' }}"
														readonly>
												</div>
											</div>
										</div>

										<div id="bankInfo" class="tab-pane">

											<div class="form-group row">
												<div class="col-md-1">
													<label for="BANK" class="form-label">Bank</label>
												</div>
												<div class="col-md-2">
													<input type="text" class="form-control BANK" id="BANK" name="BANK" placeholder="Masukkan Bank"
														value="{{ $header->BANK ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="BANK_CAB" class="form-label">Cabang</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control BANK_CAB" id="BANK_CAB" name="BANK_CAB" placeholder="Masukkan Cabang"
														value="{{ $header->BANK_CAB ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="BANK_KOTA" class="form-label">Kota</label>
												</div>
												<div class="col-md-2">
													<input type="text" class="form-control BANK_KOTA" id="BANK_KOTA" name="BANK_KOTA" placeholder="Masukkan Kota"
														value="{{ $header->BANK_KOTA ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="BANK_NAMA" class="form-label">A/N</label>
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control BANK_NAMA" id="BANK_NAMA" name="BANK_NAMA" placeholder="Masukkan Nama"
														value="{{ $header->BANK_NAMA ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="BANK_REK" class="form-label">Rek</label>
												</div>
												<div class="col-md-3">
													<input type="text" class="form-control BANK_REK" id="BANK_REK" name="BANK_REK" placeholder="Masukkan Nomor Rekening"
														value="{{ $header->BANK_REK ?? '' }}" readonly>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-1">
													<label for="HARI" class="form-label">Janji Hari</label>
												</div>
												<div class="col-md-1">
													<input type="text" class="form-control HARI" id="HARI" name="HARI" placeholder="Masukkan Jumlah Hari"
														value="{{ $header->HARI ?? '' }}" readonly>
												</div>
											</div>

										</div>
									</div>

									<div class="col-md-12 form-group row mt-3">
										<div class="col-md-4">
											<button type="button" id='TOPX' onclick="location.href='{{ url('/sup-food-center/show/?idx=' . $idx . '&tipx=top') }}'"
												class="btn btn-outline-primary">Top</button>
											<button type="button" id='PREVX'
												onclick="location.href='{{ url('/sup-food-center/show/?idx=' . $header->NO_ID . '&tipx=prev&kodex=' . $header->KODES) }}'"
												class="btn btn-outline-primary">Prev</button>
											<button type="button" id='NEXTX'
												onclick="location.href='{{ url('/sup-food-center/show/?idx=' . $header->NO_ID . '&tipx=next&kodex=' . $header->KODES) }}'"
												class="btn btn-outline-primary">Next</button>
											<button type="button" id='BOTTOMX' onclick="location.href='{{ url('/sup-food-center/show/?idx=' . $idx . '&tipx=bottom') }}'"
												class="btn btn-outline-primary">Bottom</button>
										</div>
										<div class="col-md-5">

										</div>
										<div class="col-md-3">
											<button type="button" id='CLOSEX' onclick="location.href='{{ url('/sup-food-center') }}'"
												class="btn btn-outline-secondary">Close</button>
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
				$("#NAMAS_LM").attr("readonly", true);
				$("#ALAMAT").attr("readonly", true);
				$("#KOTA").attr("readonly", true);
				$("#TELPON1").attr("readonly", true);
				$("#GOL").attr("disabled", true);
				$("#FAX").attr("readonly", true);
				$("#HP").attr("readonly", true);
				$('#KONTAK').attr("readonly", true);
				$('#EMAIL').attr("readonly", true);
				$('#NPWP').attr("readonly", true);
				$('#KET').attr("readonly", true);
				$('#BANK').attr("readonly", true);
				$('#BANK_CAB').attr("readonly", true);
				$('#BANK_KOTA').attr("readonly", true);
				$('#BANK_NAMA').attr("readonly", true);
				$('#BANK_REK').attr("readonly", true);
				$('#HARI').attr("readonly", true);
			}
		</script>
	@endsection
