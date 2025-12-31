@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<style>
		.card {
			padding: 15px;
		}

		.form-control:focus,
		.form-control:active {
			background-color: #b5e5f9;
		}

		.btn-action {
			padding: 8px 20px;
			font-weight: 600;
			font-size: 14px;
			margin: 0 3px;
		}

		.btn-save {
			background: #007bff;
			border: none;
			color: #fff;
		}

		.btn-save:hover {
			background: #0056b3;
			color: #fff;
		}

		.btn-back {
			background: #6c757d;
			border: none;
			color: #fff;
		}

		.btn-back:hover {
			background: #545b62;
			color: #fff;
		}

		.btn-add-item {
			background: #28a745;
			border: none;
			color: #fff;
		}

		.btn-add-item:hover {
			background: #218838;
			color: #fff;
		}

		.loader {
			position: fixed;
			top: 50%;
			left: 50%;
			width: 100px;
			aspect-ratio: 1;
			background:
				radial-gradient(farthest-side, #ffa516 90%, #0000) center/16px 16px,
				radial-gradient(farthest-side, green 90%, #0000) bottom/12px 12px;
			background-repeat: no-repeat;
			animation: l17 1s infinite linear;
			z-index: 9999;
			display: none;
		}

		.loader::before {
			content: "";
			position: absolute;
			width: 8px;
			aspect-ratio: 1;
			inset: auto 0 16px;
			margin: auto;
			background: #ccc;
			border-radius: 50%;
			transform-origin: 50% calc(100% + 10px);
			animation: inherit;
			animation-duration: 0.5s;
		}

		@keyframes l17 {
			100% {
				transform: rotate(1turn)
			}
		}

		.form-group {
			margin-bottom: 15px;
		}

		.form-group label {
			font-weight: 600;
			margin-bottom: 5px;
			display: block;
			color: #333;
		}

		.form-control {
			border: 1px solid #ced4da;
			border-radius: 4px;
			padding: 6px 12px;
			font-size: 14px;
		}

		.form-control:disabled {
			background-color: #e9ecef;
			cursor: not-allowed;
		}

		.info-box {
			background: #e7f3ff;
			border: 1px solid #b3d9ff;
			padding: 15px;
			border-radius: 5px;
			margin-bottom: 20px;
		}

		.info-box strong {
			color: #0056b3;
		}

		.text-right {
			text-align: right !important;
		}

		.text-center {
			text-align: center !important;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 13px;
			padding: 10px 8px;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.table tbody td {
			padding: 6px;
			font-size: 12px;
		}

		.label-nama-barang {
			font-weight: 600;
			color: #007bff;
			padding: 10px;
			background: #f0f8ff;
			border: 1px solid #b3d9ff;
			border-radius: 4px;
			margin-top: 10px;
			min-height: 40px;
		}

		.section-box {
			border: 1px solid #dee2e6;
			border-radius: 5px;
			padding: 15px;
			margin-bottom: 20px;
			background: #f8f9fa;
		}

		.section-title {
			font-weight: bold;
			color: #495057;
			margin-bottom: 15px;
			padding-bottom: 10px;
			border-bottom: 2px solid #007bff;
		}

		.radio-group {
			display: flex;
			gap: 20px;
			align-items: center;
		}

		.radio-group label {
			display: flex;
			align-items: center;
			gap: 5px;
			font-weight: normal;
			margin: 0;
		}

		.panel-pink {
			background: #ffe6f0;
			border: 1px solid #ffb3d9;
			border-radius: 5px;
			padding: 15px;
			margin-bottom: 10px;
		}

		.panel-yellow {
			background: #fff9e6;
			border: 1px solid #ffe6b3;
			border-radius: 5px;
			padding: 15px;
		}

		.mini-title {
			font-weight: bold;
			font-size: 13px;
			display: block;
			margin-bottom: 5px;
		}
	</style>
@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $judul }}</h1>
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
								<!-- Info Box -->
								<div class="info-box">
									<p class="mb-1"><strong>Petunjuk:</strong></p>
									<ul class="mb-0">
										<li>Pilih <strong>Jenis Pengajuan</strong> terlebih dahulu (UK/UH/UD/UJ)</li>
										<li>Masukkan <strong>Kode Barang</strong> atau gunakan browse untuk mencari</li>
										<li>Isi data perubahan sesuai jenis pengajuan yang dipilih</li>
										<li>Klik <strong>Tambah Item</strong> untuk menambahkan ke daftar</li>
										<li>Klik <strong>SAVE</strong> untuk menyimpan semua perubahan</li>
									</ul>
								</div>

								<!-- Form Header -->
								<form id="formHeader">
									@csrf
									<input type="hidden" name="status" id="status" value="{{ $status }}">
									<input type="hidden" name="no_bukti_hidden" id="no_bukti_hidden" value="{{ $no_bukti }}">

									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label for="no_bukti">No Bukti</label>
												<input type="text" class="form-control" id="no_bukti" value="{{ $no_bukti }}" readonly disabled>
											</div>
										</div>

										<div class="col-md-4">
											<div class="form-group">
												<label for="tgl">Tanggal <span class="text-danger">*</span></label>
												<input type="date" class="form-control" id="tgl" name="tgl" value="{{ $tgl }}" required
													{{ $posted == 1 || $closedPeriod ? 'readonly' : '' }}>
											</div>
										</div>

										<div class="col-md-4">
											<div class="form-group">
												<label for="flag">Jenis Pengajuan <span class="text-danger">*</span></label>
												<select class="form-control" id="flag" name="flag" required
													{{ $status === 'edit' || $posted == 1 || $closedPeriod ? 'disabled' : '' }}>
													<option value="">-- Pilih --</option>
													<option value="UK" {{ $flag == 'UK' ? 'selected' : '' }}>UK - Rubah L/H, KK</option>
													<option value="UH" {{ $flag == 'UH' ? 'selected' : '' }}>UH - Rubah Harga Jual</option>
													<option value="UD" {{ $flag == 'UD' ? 'selected' : '' }}>UD - Tandai Hapus Barang</option>
													<option value="UJ" {{ $flag == 'UJ' ? 'selected' : '' }}>UJ - Antar Outlet</option>
												</select>
											</div>
										</div>
									</div>

									<hr class="my-4">

									<!-- Form Entry Barang -->
									<div class="section-box" id="sectionEntry" style="display: {{ $flag ? 'block' : 'none' }}">
										<div class="section-title">Entry Data Barang</div>

										<div class="row">
											<div class="col-md-6">
												<!-- Kode Barang -->
												<div class="form-group">
													<label for="kd_brg">Kode Barang <span class="text-danger">*</span></label>
													<div class="input-group">
														<input type="text" class="form-control" id="kd_brg" name="kd_brg" placeholder="Masukkan kode barang atau tekan . untuk browse"
															{{ $posted == 1 || $closedPeriod ? 'readonly' : '' }}>
														<div class="input-group-append">
															<button class="btn btn-info" type="button" id="btnBrowse" {{ $posted == 1 || $closedPeriod ? 'disabled' : '' }}>
																<i class="fas fa-search"></i> Browse
															</button>
														</div>
													</div>
												</div>

												<!-- Nama Barang Display -->
												<div class="label-nama-barang" id="label_nama_barang">
													Nama Barang akan muncul di sini
												</div>

												<!-- Uraian -->
												<div class="form-group">
													<label for="uraian">Uraian</label><input type="text" class="form-control" id="uraian" readonly>
												</div>

												<!-- Ukuran -->
												<div class="form-group">
													<label for="ket_uk">Ukuran</label>
													<input type="text" class="form-control" id="ket_uk" readonly>
												</div>

												<!-- Kemasan -->
												<div class="form-group">
													<label for="ket_kem">Kemasan</label>
													<input type="text" class="form-control" id="ket_kem" readonly>
												</div>
											</div>

											<div class="col-md-6">
												<!-- LPH H.Raya Checkbox -->
												<div class="form-group">
													<div class="custom-control custom-checkbox">
														<input type="checkbox" class="custom-control-input" id="lph_hraya" disabled>
														<label class="custom-control-label" for="lph_hraya">LPH H.Raya</label>
													</div>
												</div>

												<!-- Monang Maning & Soputan -->
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<div class="custom-control custom-checkbox">
																<input type="checkbox" class="custom-control-input" id="cb_tmm" disabled>
																<label class="custom-control-label" id="label_tmm" for="cb_tmm">Monang Maning</label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<div class="custom-control custom-checkbox">
																<input type="checkbox" class="custom-control-input" id="cb_sop" disabled>
																<label class="custom-control-label" id="label_sop" for="cb_sop">Soputan</label>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<hr>

										<!-- Section UK - Ubah Kartu -->
										<div id="sectionUK" style="display: none;">
											<div class="section-title">Data Ubah Kartu</div>
											<div class="row">
												<div class="col-md-3">
													<div class="form-group">
														<label for="lph">Laku/Hari</label>
														<input type="number" class="form-control text-right" id="lph" step="0.01" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="lphbr">Laku/Hari Baru <span class="text-danger">*</span></label>
														<input type="number" class="form-control text-right" id="lphbr" step="0.01"
															{{ $posted == 1 || $closedPeriod ? 'readonly' : '' }}>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="dtr">DTR Transit</label>
														<input type="number" class="form-control text-right" id="dtr" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="dtrbr">DTR Transit Baru <span class="text-danger">*</span></label>
														<input type="number" class="form-control text-right" id="dtrbr" {{ $posted == 1 || $closedPeriod ? 'readonly' : '' }}>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-3">
													<div class="form-group">
														<label for="klk">KLK</label>
														<input type="text" class="form-control" id="klk" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="klkhit">KLK (Angka)</label>
														<input type="text" class="form-control" id="klkhit" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="kdlaku">KD Laku</label>
														<input type="text" class="form-control" id="kdlaku" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="kdlakubr">KD Laku Baru</label>
														<input type="text" class="form-control" id="kdlakubr" readonly>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<label>K. Khusus</label>
														<div class="radio-group">
															<label>
																<input type="radio" name="kk_radio" value="" id="kk1" checked {{ $posted == 1 || $closedPeriod ? 'disabled' : '' }}>
																Tanpa Tanda
															</label>
															<label>
																<input type="radio" name="kk_radio" value="*" id="kk2" {{ $posted == 1 || $closedPeriod ? 'disabled' : '' }}> Tanda
																Bintang (*)
															</label>
															<label>
																<input type="radio" name="kk_radio" value="!" id="kk3" {{ $posted == 1 || $closedPeriod ? 'disabled' : '' }}> Tanda
																Khusus (!)
															</label>
															<label>
																<input type="radio" name="kk_radio" value="!*" id="kk4" {{ $posted == 1 || $closedPeriod ? 'disabled' : '' }}> Tanda
																Seru Bintang (!*)
															</label>
														</div>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-3">
													<div class="form-group">
														<label for="sr_min">Minimal Rak</label>
														<input type="number" class="form-control text-right" id="sr_min" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="sr_minbr">Minimal Rak Baru</label>
														<input type="number" class="form-control text-right" id="sr_minbr" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="smax_tk">Maximal Rak</label>
														<input type="number" class="form-control text-right" id="smax_tk" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="smax_tkbr">Maximal Rak Baru</label>
														<input type="number" class="form-control text-right" id="smax_tkbr" readonly>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-3">
													<div class="form-group">
														<label for="smin">Stok Minimal</label>
														<input type="number" class="form-control text-right" id="smin" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="sminbr">Stok Minimal Baru</label>
														<input type="number" class="form-control text-right" id="sminbr" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="smax">Stok Maximal</label>
														<input type="number" class="form-control text-right" id="smax" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="smaxbr">Stok Maximal Baru</label>
														<input type="number" class="form-control text-right" id="smaxbr" readonly>
													</div>
												</div>
											</div>

											<div class="text-right">
												<button type="button" id="btnAddUK" class="btn btn-action btn-add-item" {{ $posted == 1 || $closedPeriod ? 'disabled' : '' }}>
													<i class="fas fa-plus"></i> Tambah Item UK
												</button>
											</div>
										</div>

										<!-- Section UH - Ubah Harga -->
										<div id="sectionUH" style="display: none;">
											<div class="section-title">Data Ubah Harga</div>
											<div class="row">
												<div class="col-md-4">
													<div class="form-group">
														<label for="hj2">H. Jual Lama</label>
														<input type="number" class="form-control text-right" id="hj2" step="0.01" readonly>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="hj">H. Jual</label>
														<input type="number" class="form-control text-right" id="hj" step="0.01" readonly>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="hjbr">H. Jual Baru <span class="text-danger">*</span></label>
														<input type="number" class="form-control text-right" id="hjbr" step="0.01"
															{{ $posted == 1 || $closedPeriod ? 'readonly' : '' }}>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<label for="cat_hj">Catatan Harga</label>
														<textarea class="form-control" id="cat_hj" rows="2" {{ $posted == 1 || $closedPeriod ? 'readonly' : '' }}></textarea>
													</div>
												</div>
											</div>

											<!-- Supplier Info (dari Delphi UH) -->
											<div class="row">
												<div class="col-md-3">
													<div class="form-group">
														<label for="diskon1">Diskon 1</label>
														<input type="number" class="form-control text-right" id="diskon1" step="0.01" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="diskon2">Diskon 2</label>
														<input type="number" class="form-control text-right" id="diskon2" step="0.01" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="diskon3">Diskon 3</label>
														<input type="number" class="form-control text-right" id="diskon3" step="0.01" readonly>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="margin">Margin</label>
														<input type="number" class="form-control text-right" id="margin" step="0.01" readonly>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-4">
													<div class="form-group">
														<label for="kodes">Kode Supplier</label>
														<input type="text" class="form-control" id="kodes" readonly>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="namas">Nama Supplier</label>
														<input type="text" class="form-control" id="namas" readonly>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="golongan">Golongan</label>
														<input type="text" class="form-control" id="golongan" readonly>
													</div>
												</div>
											</div>

											<div class="text-right">
												<button type="button" id="btnAddUH" class="btn btn-action btn-add-item" {{ $posted == 1 || $closedPeriod ? 'disabled' : '' }}>
													<i class="fas fa-plus"></i> Tambah Item UH
												</button>
											</div>
										</div>

										<!-- Section UD - Ubah Data -->
										<div id="sectionUD" style="display: none;">
											<div class="section-title">Data Ubah Data (Discontinue)</div>

											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="alasan">Alasan Discontinue <span class="text-danger">*</span></label>
														<textarea class="form-control" id="alasan" rows="5" placeholder="Masukkan alasan discontinue barang"
														 {{ $posted == 1 || $closedPeriod ? 'readonly' : '' }}></textarea>
													</div>
												</div>

												<div class="col-md-6">
													<label>Barang yang mungkin serupa:</label>
													<div class="form-group">
														<input type="text" id="KD_BRG2" class="form-control mb-2" placeholder="Kode Barang" readonly>
													</div>
													<div class="form-group">
														<input type="text" id="NA_BRG2" class="form-control mb-2" placeholder="Nama Barang" readonly>
													</div>
													<div class="form-group">
														<input type="text" id="KET_UK2" class="form-control" placeholder="Ukuran" readonly>
													</div>
												</div>
											</div>

											<div class="text-right">
												<button type="button" id="btnAddUD" class="btn btn-action btn-add-item" {{ $posted == 1 || $closedPeriod ? 'disabled' : '' }}>
													<i class="fas fa-plus"></i> Tambah Item UD
												</button>
											</div>
										</div>

										<!-- Section UJ - Ubah Jualan -->
										<div id="sectionUJ" style="display:none;">
											<div class="section-title">Data Ubah Jualan (Outlet Baru)</div>

											<div class="row">
												<!-- Panel Kiri -->
												<div class="col-md-6">
													<div class="panel-pink">
														<div class="row">
															<div class="col-md-6">
																<div class="form-group">
																	<label>Cabang <span class="text-danger">*</span></label>
																	<select class="form-control" name="cibing" id="cibing" {{ $posted == 1 || $closedPeriod ? 'disabled' : '' }}>
																		<option value="">-- Pilih Cabang --</option>
																		<option value="TMM">TMM - Monang Maning</option>
																		<option value="SOP">SOP - Soputan</option>
																		<option value="MOO">MOO</option>
																	</select>
																</div>
															</div>

															<div class="col-md-6">
																<div class="form-group">
																	<label>Status Order</label>
																	<select class="form-control" name="splbr" id="splbr" {{ $posted == 1 || $closedPeriod ? 'disabled' : '' }}>
																		<option value="">-- Pilih --</option>
																		<option value="Z">Z</option>
																		<option value="L">L</option>
																		<option value="D">D</option>
																	</select>
																</div>
															</div>
														</div>

														<div class="row">
															<div class="col-md-6">
																<div class="form-group">
																	<label>L/H</label>
																	<input type="number" class="form-control text-right" id="lph_outlet" step="0.01" readonly>
																</div>
															</div>
															<div class="col-md-6">
																<div class="form-group">
																	<label>DTR</label>
																	<input type="number" class="form-control text-right" id="dtr_outlet" readonly>
																</div>
															</div>
														</div>

														<div class="row">
															<div class="col-md-6">
																<div class="form-group">
																	<label>MOO Lama</label>
																	<input type="number" class="form-control text-right" id="moolm" step="0.01"
																		{{ $posted == 1 || $closedPeriod ? 'readonly' : '' }}>
																</div>
															</div>
															<div class="col-md-6">
																<div class="form-group">
																	<label>MOO Baru</label>
																	<input type="number" class="form-control text-right" id="moo" step="0.01"
																		{{ $posted == 1 || $closedPeriod ? 'readonly' : '' }}>
																</div>
															</div>
														</div>
													</div>
												</div>

												<!-- Panel Tengah (Lama-Baru) -->
												<div class="col-md-4">
													<div class="panel-yellow">
														<div class="row mb-2 text-center">
															<div class="col-6"><strong>Lama</strong></div>
															<div class="col-6"><strong>Baru</strong></div>
														</div>

														<div class="row mb-2">
															<div class="col-4"><label class="mini-title">KD Laku</label></div>
															<div class="col-4"><input class="form-control form-control-sm" id="kdlaku_lm" readonly></div>
															<div class="col-4"><input class="form-control form-control-sm" id="kdlaku_br" readonly></div>
														</div>

														<div class="row mb-2">
															<div class="col-4"><label class="mini-title">Min Rak</label></div>
															<div class="col-4"><input class="form-control form-control-sm" id="sr_min_lm" readonly></div>
															<div class="col-4"><input class="form-control form-control-sm" id="sr_min_br" readonly></div>
														</div>

														<div class="row mb-2">
															<div class="col-4"><label class="mini-title">Max Rak</label></div>
															<div class="col-4"><input class="form-control form-control-sm" id="smax_tk_lm" readonly></div>
															<div class="col-4"><input class="form-control form-control-sm" id="smax_tk_br" readonly></div>
														</div>

														<div class="row mb-2">
															<div class="col-4"><label class="mini-title">Stok Min</label></div>
															<div class="col-4"><input class="form-control form-control-sm" id="smin_lm" readonly></div>
															<div class="col-4"><input class="form-control form-control-sm" id="smin_br" readonly></div>
														</div>

														<div class="row mb-2">
															<div class="col-4"><label class="mini-title">Stok Max</label></div>
															<div class="col-4"><input class="form-control form-control-sm" id="smax_lm" readonly></div>
															<div class="col-4"><input class="form-control form-control-sm" id="smax_br" readonly></div>
														</div>
													</div>
												</div>

												<!-- Panel Kanan (TMM & SOP) -->
												<div class="col-md-2">
													<div class="panel-pink">
														<label class="mini-title">TMM</label>
														<div class="custom-control custom-checkbox mb-3">
															<input type="checkbox" class="custom-control-input" id="cb_tmm_uj" disabled>
															<label class="custom-control-label" id="label_tmm_uj" for="cb_tmm_uj"></label>
														</div>

														<label class="mini-title">SOP</label>
														<div class="custom-control custom-checkbox">
															<input type="checkbox" class="custom-control-input" id="cb_sop_uj" disabled>
															<label class="custom-control-label" id="label_sop_uj" for="cb_sop_uj"></label>
														</div>
													</div>
												</div>
											</div>

											<div class="mt-3 text-right">
												<button type="button" id="btnAddUJ" class="btn btn-action btn-add-item" {{ $posted == 1 || $closedPeriod ? 'disabled' : '' }}>
													<i class="fas fa-plus"></i> Tambah Item UJ
												</button>
											</div>
										</div>
									</div>

									<hr class="my-4">

									<!-- Data Table Items -->
									<div class="table-responsive">
										<table class="table-striped table-bordered table-hover table" id="tableItems" style="width:100%">
											<thead>
												<tr>
													<th width="40px" class="text-center">No</th>
													<th width="100px">Kode</th>
													<th>Uraian</th>
													<th width="80px" class="text-right">HJ Lama</th>
													<th width="80px" class="text-right">HJ</th>
													<th width="80px" class="text-right">HJ Baru</th>
													<th width="70px" class="text-right">LPH</th>
													<th width="70px" class="text-right">LPH Baru</th>
													<th width="60px" class="text-right">DTR</th>
													<th width="60px" class="text-right">DTR Baru</th>
													<th width="50px">KK</th>
													<th width="50px">KK Baru</th>
													<th width="120px">Catatan</th>
													<th width="60px" class="text-right">MOO</th>
													<th width="60px" class="text-right">MOO Baru</th>
													<th width="60px">Cabang</th>
													<th width="50px">Ordr</th>
													<th width="60px" class="text-center">Aksi</th>
												</tr>
											</thead>
											<tbody>
												@if (!empty($detail))
													@foreach ($detail as $index => $item)
														<tr>
															<td class="text-center">{{ $index + 1 }}</td>
															<td>{{ $item->KODE }}</td>
															<td>{{ $item->URAIAN }}</td>
															<td class="text-right">{{ number_format($item->HJ2, 2) }}</td>
															<td class="text-right">{{ number_format($item->HJ, 2) }}</td>
															<td class="text-right">{{ number_format($item->HJBR, 2) }}</td>
															<td class="text-right">{{ number_format($item->LPH, 2) }}</td>
															<td class="text-right">{{ number_format($item->LPHBR, 2) }}</td>
															<td class="text-right">{{ number_format($item->DTR, 0) }}</td>
															<td class="text-right">{{ number_format($item->DTRBR, 0) }}</td>
															<td>{{ $item->KK }}</td>
															<td>{{ $item->KKBR }}</td>
															<td>{{ $item->KET }}</td>
															<td class="text-right">{{ number_format($item->MOOLM ?? 0, 2) }}</td>
															<td class="text-right">{{ number_format($item->MOO ?? 0, 2) }}</td>
															<td>{{ $item->CIBING }}</td>
															<td>{{ $item->SPLBR }}</td>
															<td class="text-center">
																<button class="btn btn-xs btn-danger btn-delete-item" data-id="{{ $item->NO_ID }}"
																	{{ $posted == 1 || $closedPeriod ? 'disabled' : '' }}>
																	<i class="fas fa-trash"></i>
																</button>
															</td>
														</tr>
													@endforeach
												@endif
											</tbody>
										</table>
									</div>

									<hr class="my-4">

									<div class="text-right">
										<button type="button" id="btnBack" class="btn btn-action btn-back">
											<i class="fas fa-arrow-left"></i> BACK
										</button>
										<button type="button" id="btnSave" class="btn btn-action btn-save" {{ $posted == 1 || $closedPeriod ? 'disabled' : '' }}> <i
												class="fas fa-save"></i> SAVE
										</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div><!-- Modal Browse Barang -->
	<div class="modal fade" id="browseBarangModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-xl" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Cari Barang</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<table class="table-stripped table-bordered table" id="table-brg">
						<thead>
							<tr>
								<th>Kode</th>
								<th>Barang</th>
								<th>Kemasan</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<div class="loader" id="LOADX"></div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		var currentNoBukti = '{{ $no_bukti }}';
		var currentFlag = '{{ $flag }}';
		var currentStatus = '{{ $status }}';
		var isPosted = {{ $posted }};
		var isClosedPeriod = {{ $closedPeriod ? 'true' : 'false' }};
		var dTableBarang;

		$(document).ready(function() {
			// Show/hide sections based on flag
			$('#flag').on('change', function() {
				var flag = $(this).val();
				currentFlag = flag;

				if (flag) {
					$('#sectionEntry').show();
					showFlagSection(flag);
				} else {
					$('#sectionEntry').hide();
				}

				clearForm();
			});

			// Initial show if edit
			if (currentFlag) {
				$('#sectionEntry').show();
				showFlagSection(currentFlag);
			}

			// Button Back
			$('#btnBack').on('click', function() {
				window.location.href = '{{ route('pengajuanperubahan') }}';
			});

			// Kode Barang - Enter key or blur handler
			$('#kd_brg').on('keydown', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					var kode = $(this).val().trim();
					if (kode) {
						searchBarang(kode);
					}
				} else if (e.key === '.') {
					e.preventDefault();
					browseBrg();
				}
			});

			$('#kd_brg').on('blur', function() {
				var kode = $(this).val().trim();
				if (kode) {
					searchBarang(kode);
				}
			});

			// Button Browse
			$('#btnBrowse').on('click', function() {
				browseBrg();
			});

			// Button Add UK
			$('#btnAddUK').on('click', function() {
				addItemUK();
			});

			// Button Add UH
			$('#btnAddUH').on('click', function() {
				addItemUH();
			});

			// Button Add UD
			$('#btnAddUD').on('click', function() {
				addItemUD();
			});

			// Button Add UJ
			$('#btnAddUJ').on('click', function() {
				addItemUJ();
			});

			// Button Save
			$('#btnSave').on('click', function() {
				saveData();
			});

			// Button Delete Item
			$(document).on('click', '.btn-delete-item', function() {
				if ($(this).prop('disabled')) {
					return;
				}

				var no_id = $(this).data('id');

				Swal.fire({
					title: 'Konfirmasi Hapus',
					text: 'Apakah item ini akan dihapus?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#6c757d',
					confirmButtonText: '<i class="fas fa-check"></i> Ya, Hapus!',
					cancelButtonText: '<i class="fas fa-times"></i> Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						deleteItem(no_id);
					}
				});
			});

			// LPH change - calculate for UK
			$('#lphbr, #dtrbr').on('change', function() {
				if (currentFlag === 'UK') {
					calculateStockParams();
				}
			});

			// Cibing change for UJ
			$('#cibing').on('change', function() {
				var cibing = $(this).val();
				var kd_brg = $('#kd_brg').val().trim();

				if (cibing && kd_brg) {
					getOutletData(kd_brg, cibing);
					checkTMMSOP(kd_brg);
				}
			});

			// Splbr change for UJ
			$('#splbr').on('change', function() {
				var kd_brg = $('#kd_brg').val().trim();
				var cibing = $('#cibing').val();

				if (kd_brg && cibing) {
					getMooData(kd_brg, cibing);
				}
			});

			// Initialize DataTable for browse
			dTableBarang = $("#table-brg").DataTable({
				columns: [{
						title: "Kode"
					},
					{
						title: "Barang"
					},
					{
						title: "Kemasan"
					}
				]
			});
		});

		function showFlagSection(flag) {
			// Hide all sections first
			$('#sectionUK, #sectionUH, #sectionUD, #sectionUJ').hide();

			// Show relevant section
			switch (flag) {
				case 'UK':
					$('#sectionUK').show();
					break;
				case 'UH':
					$('#sectionUH').show();
					break;
				case 'UD':
					$('#sectionUD').show();
					break;
				case 'UJ':
					$('#sectionUJ').show();
					break;
			}
		}

		function searchBarang(kode) {
			if (isPosted || isClosedPeriod) {
				return;
			}

			$('#LOADX').show();

			$.ajax({
				url: '{{ route('pengajuanperubahan_search_barang') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					kd_brg: kode
				},
				success: function(response) {
					$('#LOADX').hide();

					if (response.success && response.data) {
						var barang = response.data;

						// Fill common fields
						$('#uraian').val(barang.NA_BRG);
						$('#ket_uk').val(barang.KET_UK);
						$('#ket_kem').val(barang.KET_KEM);

						// Display nama barang
						var namaLengkap = barang.NA_BRG + ' ' + barang.KET_UK;
						$('#label_nama_barang').text(namaLengkap);

						// LPH H.Raya
						$('#lph_hraya').prop('checked', barang.LPH_HRAYA == 1);

						// Fill flag-specific fields
						if (currentFlag === 'UK') {
							fillDataUK(barang);
						} else if (currentFlag === 'UH') {
							fillDataUH(barang);
						} else if (currentFlag === 'UD') {
							fillDataUD(barang);
						} else if (currentFlag === 'UJ') {
							fillDataUJ(barang);
						}
					} else {
						$('#label_nama_barang').text('Barang tidak ditemukan');
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Barang tidak ditemukan'
						});
						clearForm();
					}
				},
				error: function() {
					$('#LOADX').hide();
					$('#label_nama_barang').text('Barang tidak ditemukan');
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Barang tidak ditemukan'
					});
					clearForm();
				}
			});
		}

		function fillDataUK(barang) {
			$('#lph').val(barang.LPH);
			$('#lphbr').val(barang.LPH);
			$('#dtr').val(barang.DTR);
			$('#dtrbr').val(barang.DTR);
			$('#klk').val(barang.KLK);
			$('#klkhit').val(barang.AGENG);
			$('#kdlaku').val(barang.KDLAKU);
			$('#kdlakubr').val(barang.KDLAKU);
			$('#sr_min').val(barang.SRMIN);
			$('#sr_minbr').val(barang.SRMIN);
			$('#smax_tk').val(barang.SRMAX);
			$('#smax_tkbr').val(barang.SRMAX);
			$('#smin').val(barang.SMIN);
			$('#sminbr').val(barang.SMIN);
			$('#smax').val(barang.SMAX);
			$('#smaxbr').val(barang.SMAX);

			// Set KK radio
			if (barang.KK === '!') {
				$('#kk3').prop('checked', true);
			} else if (barang.KK === '*') {
				$('#kk2').prop('checked', true);
			} else if (barang.KK === '!*') {
				$('#kk4').prop('checked', true);
			} else {
				$('#kk1').prop('checked', true);
			}

			$('#lphbr').focus();
		}

		function fillDataUH(barang) {
			$('#hj2').val(barang.HJ2);
			$('#hj').val(barang.HJ);
			$('#hjbr').val(barang.HJ);
			$('#cat_hj').val(barang.CAT_OD || '');

			// Fill supplier info
			$('#diskon1').val(barang.DISKON1 || 0);
			$('#diskon2').val(barang.DISKON2 || 0);
			$('#diskon3').val(barang.DISKON3 || 0);
			$('#margin').val(barang.MARGIN || 0);
			$('#kodes').val(barang.KODES || '');
			$('#namas').val(barang.NAMAS || '');
			$('#golongan').val(barang.GOLONGAN || '');

			$('#hjbr').focus();
		}

		function fillDataUD(barang) {
			$('#alasan').val(barang.CAT_OD || '');
			$('#alasan').focus();

			// Check barang serupa
			cekBarangSerupa($('#kd_brg').val());
		}

		function fillDataUJ(barang) {
			// Reset checkboxes
			$('#cb_tmm_uj').prop('checked', false);
			$('#cb_sop_uj').prop('checked', false);
			$('#label_tmm_uj').text('');
			$('#label_sop_uj').text('');

			// Fill lama values
			$('#kdlaku_lm').val(barang.KDLAKU || '');
			$('#sr_min_lm').val(barang.SRMIN || '');
			$('#smax_tk_lm').val(barang.SRMAX || '');
			$('#smin_lm').val(barang.SMIN || '');
			$('#smax_lm').val(barang.SMAX || '');

			// Fill baru values (same as lama initially)
			$('#kdlaku_br').val(barang.KDLAKU || '');
			$('#sr_min_br').val(barang.SRMIN || '');
			$('#smax_tk_br').val(barang.SRMAX || '');
			$('#smin_br').val(barang.SMIN || '');
			$('#smax_br').val(barang.SMAX || '');

			$('#cibing').focus();
		}

		function cekBarangSerupa(kodeBarang) {
			$.ajax({
				url: "{{ route('cek-barang-serupa') }}",
				type: "GET",
				data: {
					kd_brg: kodeBarang
				},
				success: function(res) {
					if (res.length > 0) {
						$('#KD_BRG2').val(res[0].KD_BRG);
						$('#NA_BRG2').val(res[0].NA_BRG);
						$('#KET_UK2').val(res[0].KET_UK);
					} else {
						$('#KD_BRG2').val('Tidak ada barang serupa');
						$('#NA_BRG2').val('');
						$('#KET_UK2').val('');
					}
				}
			});
		}

		function getOutletData(kd_brg, cibing) {
			$.ajax({
				url: '{{ route('pengajuanperubahan_search_barang') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					kd_brg: kd_brg
				},
				success: function(response) {
					if (response.success && response.data) {
						// Get LPH & DTR for specific outlet
						// This would need additional backend support
						$('#lph_outlet').val(0);
						$('#dtr_outlet').val(0);
					}
				}
			});
		}

		function checkTMMSOP(kd_brg) {
			// Check TMM
			$.ajax({
				url: '{{ url('check-outlet-data') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					kd_brg: kd_brg,
					cabang: 'TMM'
				},
				success: function(res) {
					if (res.exists && res.lph > 0 && ['0', '1', '3', '4', '5', '6', '7', '8'].includes(res.kdlaku)) {
						if (res.td_od !== '*') {
							$('#cb_tmm_uj').prop('checked', true);
							$('#label_tmm_uj').text('Outlet TMM sudah jualan barang ini...');
						} else {
							$('#cb_tmm_uj').prop('checked', false);
							$('#label_tmm_uj').text('Tidak Diorder/*TPJ');
						}
					} else {
						$('#cb_tmm_uj').prop('checked', false);
						$('#label_tmm_uj').text('Belum ada data di cabang TMM');
					}
				}
			});

			// Check SOP
			$.ajax({
				url: '{{ url('check-outlet-data') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					kd_brg: kd_brg,
					cabang: 'SOP'
				},
				success: function(res) {
					if (res.exists && res.lph > 0 && ['0', '1', '3', '4', '5', '6', '7', '8'].includes(res.kdlaku)) {
						if (res.td_od !== '*') {
							$('#cb_sop_uj').prop('checked', true);
							$('#label_sop_uj').text('Outlet SOP sudah jualan barang ini...');
						} else {
							$('#cb_sop_uj').prop('checked', false);
							$('#label_sop_uj').text('Tidak Diorder/*TPJ');
						}
					} else {
						$('#cb_sop_uj').prop('checked', false);
						$('#label_sop_uj').text('Belum ada data di cabang SOP');
					}
				}
			});
		}

		function getMooData(kd_brg, cibing) {
			// Get MOO based on cibing
			// This would need additional backend support
		}

		function calculateStockParams() {
			var lphbr = parseFloat($('#lphbr').val()) || 0;
			var dtrbr = parseFloat($('#dtrbr').val()) || 0;
			var kd_brg = $('#kd_brg').val().trim();

			if (!kd_brg || lphbr === 0) {
				return;
			}

			// Call stored procedure
			$.ajax({
				url: '{{ url('calculate-stock-params') }}',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					kd_brg: kd_brg,
					lph: lphbr,
					dtr: dtrbr,
					flag: currentFlag
				},
				success: function(response) {
					if (response.success) {
						$('#sr_minbr').val(response.srmin);
						$('#smax_tkbr').val(response.srmax);
						$('#sminbr').val(response.smin);
						$('#smaxbr').val(response.smax);
						$('#kdlakubr').val(response.kdlaku);
					}
				}
			});
		}
	</script>
@endsection
