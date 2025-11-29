@extends('layouts.plain')

@section('content')
<style>
	.card {}

	.content-header {
		padding: 0 !important;
	}

	.form-group.row {
		align-items: center !important;
		margin-bottom: 0.5rem !important;
	}

	.form-group.row .form-label {
		margin-bottom: 0 !important;
		line-height: 1.5;
		vertical-align: middle;
		display: flex;
		align-items: center;
		height: 38px;
	}

	.form-group.row .form-control {
		line-height: 1.5;
		height: 38px;
	}

	.btn-sm {
		padding: 0.25rem 0.5rem;
		font-size: 0.875rem;
	}

	.input-group-sm .form-control {
		height: calc(1.5em + 0.5rem + 2px);
		padding: 0.25rem 0.5rem;
		font-size: 0.875rem;
	}

	textarea.form-control {
		height: auto !important;
	}
</style>

<div class="content-wrapper">
	<div class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-header">
							<h3 class="card-title">Entry Penyewa Tempat - {{ $status == 'simpan' ? 'New' : 'Edit' }}</h3>
						</div>
						<div class="card-body">
							<form action="{{ route('phentrypenyewatempat.save-config') }}" method="POST" name="entri" id="entri">
								@csrf
								<input type="hidden" id="status" name="status" value="{{ $status }}">

								<div class="row">
									<div class="col-md-6">
										<div class="form-group row">
											<div class="col-md-7">

											</div>
											<div class="col-md-2">
												<label for="no_bukti" class="form-label">No Penyewa</label>
											</div>
											<div class="col-md-3">
												<input type="text" class="form-control form-control-sm" id="no_bukti" name="no_bukti"
													value="{{ $status == 'simpan' ? '+' : $header->NO_BUKTI ?? '' }}" {{ $status == 'edit' ? 'readonly' : '' }} placeholder="No Penyewa"
													required>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="kodes" class="form-label">No Supplier</label>
											</div>
											<div class="col-md-2">
												<input type="text" class="form-control form-control-sm" id="kodes" name="kodes" value="{{ $header->KODES ?? '' }}"
													placeholder="No Supplier" {{ $status == 'edit' ? 'readonly' : '' }} required>
											</div>
											<div class="col-md-2">
												<label for="ktp" class="form-label">No Ktp</label>
											</div>
											<div class="col-md-5">
												<input type="text" class="form-control form-control-sm" id="ktp" name="ktp" value="{{ $supplier->KTP ?? '' }}"
													placeholder="No KTP" readonly>
											</div>
										</div>



										<div class="form-group row">
											<div class="col-md-3">
												<label for="namas" class="form-label">Nama</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="namas" name="namas" value="{{ $supplier->NAMAS ?? '' }}"
													placeholder="Nama" readonly>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="al_prsh" class="form-label">Alamat</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="al_prsh" name="al_prsh" value="{{ $supplier->Al_prsh ?? '' }}"
													placeholder="Alamat" readonly>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="al_prsh2" class="form-label"></label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="al_prsh2" name="al_prsh2" value="{{ $supplier->Al_prsh2 ?? '' }}"
													placeholder="Alamat 2" readonly>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="kota" class="form-label">Kota</label>
											</div>
											<div class="col-md-3">
												<input type="text" class="form-control form-control-sm" id="kota" name="kota" value="{{ $supplier->KOTA ?? '' }}"
													placeholder="Kota" readonly>
											</div>
											<div class="col-md-2">
												<label for="no_telp" class="form-label">No Telepon</label>
											</div>
											<div class="col-md-4">
												<input type="text" class="form-control form-control-sm" id="no_telp" name="no_telp" value="{{ $supplier->NO_TELP ?? '' }}"
													placeholder="No Telepon" readonly>
											</div>
										</div>



										<div class="form-group row">
											<div class="col-md-3">
												<label for="kd_dist" class="form-label">Distributor</label>
											</div>
											<div class="col-md-4">
												<input type="text" class="form-control form-control-sm" id="kd_dist" name="kd_dist"
													value="{{ $supplier->KD_DISTRIBUTOR ?? '' }}" placeholder="Kode Distributor" readonly>
											</div>
											<div class="col-md-5">
												<input type="text" class="form-control form-control-sm" id="nama_dist" name="nama_dist" value="{{ $distributor->NM_NPWP ?? '' }}"
													placeholder="Nama Distributor" readonly>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="s_pjk" class="form-label">Status Pajak</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="s_pjk" name="s_pjk" value="{{ $supplier->S_PJK ?? '' }}"
													placeholder="Status Pajak" readonly>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="npwp" class="form-label">NPWP</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="npwp" name="npwp" value="{{ $supplier->NPWP ?? '' }}"
													placeholder="NPWP" readonly>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="cara_byr" class="form-label">Sistem Pembayaran</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="cara_byr" name="cara_byr" value="{{ $supplier->CARA_BYR ?? '' }}"
													placeholder="Sistem Pembayaran" readonly>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="cara_byr2" class="form-label">Ket Bayar</label>
											</div>
											<div class="col-md-5">
												<input type="text" class="form-control form-control-sm" id="cara_byr2" name="cara_byr2" value="{{ $supplier->CARA_BYR2 ?? '' }}"
													placeholder="Keterangan Bayar" readonly>
											</div><div class="col-md-4">
												<input type="text" class="form-control form-control-sm" 
													placeholder="Keterangan Bayar" readonly>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="nm_mohon" class="form-label">Pemohon</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="nm_mohon" name="nm_mohon" value="{{ $header->NM_MOHON ?? '' }}"
													placeholder="Nama Pemohon">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="jab" class="form-label">Jabatan</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="jab" name="jab" value="{{ $header->JAB ?? '' }}"
													placeholder="Jabatan">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="email" class="form-label">Alamat Email</label>
											</div>
											<div class="col-md-9">
												<input type="email" class="form-control form-control-sm" id="email" name="email"
													value="{{ $supplier->EMAIL ?? ($header->EMAIL ?? '') }}" placeholder="Email">
											</div>
										</div>
										<div class="form-group row">
											<div class="col-md-3">
												<label for="areal" class="form-label">Areal</label>
											</div>
											<div class="col-md-9">
												<select class="form-control form-control-sm" id="areal" name="areal" {{ $status == 'edit' ? 'disabled' : '' }} required>
													<option value="">-- Pilih Areal --</option>
													@foreach ($areal as $ar)
													<option value="{{ $ar->KODE }}" {{ ($header->AREAL ?? '') == $ar->KODE ? 'selected' : '' }}>
														{{ $ar->KODE }} - {{ $ar->NAMA_TOKO }}
													</option>
													@endforeach
												</select>
												@if ($status == 'edit')
												<input type="hidden" name="areal" value="{{ $header->AREAL ?? '' }}">
												@endif
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="no_rek" class="form-label">Dibayar ke Rekening</label>
											</div>
											<div class="col-md-9">
												<select class="form-control form-control-sm" id="no_rek" name="no_rek" required>
													<option value="">-- Pilih Rekening --</option>
													@foreach ($rekening as $rek)
													<option value="{{ $rek->NO_REK }}" {{ ($header->NO_REK ?? '') == $rek->NO_REK ? 'selected' : '' }}>
														{{ $rek->NO_REK }} - {{ $rek->NAMA_REK }}
													</option>
													@endforeach
												</select>
												<small class="form-text text-muted" id="cabang_rek">
													{{ $rekening_info->CABANG_REK ?? '' }}
												</small>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-2">
												<label for="catatan" class="form-label">Catatan</label>
											</div>
											<div class="col-md-1">
												<input type="checkbox" id="chk_catatan" name="chk_catatan">
											</div>
											<div class="col-md-9">
												<textarea class="form-control form-control-sm" id="catatan" name="catatan" rows="3" placeholder="Catatan">{{ $header->CATATAN ?? '' }}</textarea>
											</div>

										</div>
									</div>

									<div class="col-md-6">


										<div class="form-group row">
											<div class="col-md-3">
												<label for="kd_sarana" class="form-label">Bentuk Sarana</label>
											</div>
											<div class="col-md-9">
												<select class="form-control form-control-sm" id="kd_sarana" name="kd_sarana" required>
													<option value="">-- Pilih Sarana --</option>
													@foreach ($sarana as $sar)
													<option value="{{ $sar->KODE }}" {{ ($header->KD_SARANA ?? '') == $sar->KODE ? 'selected' : '' }}>
														{{ $sar->KODE }} - {{ $sar->SARANA }}
													</option>
													@endforeach
												</select>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="lokasi" class="form-label">Lokasi</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="lokasi" name="lokasi" value="{{ $header->LOKASI ?? '' }}"
													placeholder="Lokasi">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="luas" class="form-label">Luas area</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="luas" name="luas" value="{{ $header->LUAS ?? '' }}"
													placeholder="Luas Area">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="tg_mulai" class="form-label">Periode</label>
											</div>
											<div class="col-md-4">
												<input type="date" class="form-control form-control-sm" id="tg_mulai" name="tg_mulai"
													value="{{ $header ? date('Y-m-d', strtotime($header->TG_MULAI)) : date('Y-m-d') }}" required>
											</div>
											<div class="col-md-1 text-center">
												<label class="form-label">s/d</label>
											</div>
											<div class="col-md-4">
												<input type="date" class="form-control form-control-sm" id="tg_selesai" name="tg_selesai"
													value="{{ $header ? date('Y-m-d', strtotime($header->TG_SELESAI)) : date('Y-m-d') }}" required>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="merek" class="form-label">Merk Produk</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="merek" name="merek" value="{{ $header->MEREK ?? '' }}"
													placeholder="Merk Produk">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="jns_produk" class="form-label">Jenis Produk</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="jns_produk" name="jns_produk" value="{{ $header->jns_produk ?? '' }}"
													placeholder="Jenis Produk">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="kegiatan" class="form-label">Ket Kegiatan</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="kegiatan" name="kegiatan" value="{{ $header->KEGIATAN ?? '' }}"
													placeholder="Keterangan Kegiatan">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="daya" class="form-label">Daya Listrik</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control form-control-sm" id="daya" name="daya" value="{{ $header->DAYA ?? '' }}"
													placeholder="Daya Listrik">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="tarif" class="form-label">Tarif/lokasi</label>
											</div>
											<div class="col-md-9">
												<input type="number" step="0.01" class="form-control form-control-sm" id="tarif" name="tarif"
													value="{{ $header->TARIF ?? 0 }}" placeholder="0.00">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="ppn" class="form-label">{{ $ppn_label }}</label>
											</div>
											<div class="col-md-9">
												<input type="number" step="0.01" class="form-control form-control-sm" id="ppn" name="ppn"
													value="{{ $header->PPN ?? 0 }}" placeholder="0.00" readonly>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="pph" class="form-label">PPH Pasal 4 Ayat 2</label>
											</div>
											<div class="col-md-9">
												<input type="number" step="0.01" class="form-control form-control-sm" id="pph" name="pph"
													value="{{ $header->PPH ?? 0 }}" placeholder="0.00" readonly>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="bayar" class="form-label">Total yang dibayar</label>
											</div>
											<div class="col-md-9">
												<input type="number" step="0.01" class="form-control form-control-sm" id="bayar" name="bayar"
													value="{{ $header->BAYAR ?? 0 }}" placeholder="0.00" readonly>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="deposit" class="form-label">Deposit</label>
											</div>
											<div class="col-md-9">
												<input type="number" step="0.01" class="form-control form-control-sm" id="deposit" name="deposit"
													value="{{ $header->DEPOSIT ?? 0 }}" placeholder="0.00">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="dp1" class="form-label">DP1</label>
											</div>
											<div class="col-md-9">
												<input type="number" step="0.01" class="form-control form-control-sm" id="dp1" name="dp1"
													value="{{ $header->DP1 ?? 0 }}" placeholder="0.00">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="dp2" class="form-label">DP2</label>
											</div>
											<div class="col-md-9">
												<input type="number" step="0.01" class="form-control form-control-sm" id="dp2" name="dp2"
													value="{{ $header->DP2 ?? 0 }}" placeholder="0.00">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="masa" class="form-label">Masa</label>
											</div>
											<div class="col-md-9">
												<input type="date" class="form-control form-control-sm" id="masa" name="masa"
													value="{{ $header && $header->MASA ? date('Y-m-d', strtotime($header->MASA)) : '' }}">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="jum_kwi" class="form-label">Jumlah Kwitansi</label>
											</div>
											<div class="col-md-9">
												<input type="number" step="0.01" class="form-control form-control-sm" id="jum_kwi" name="jum_kwi"
													value="{{ $header->JUM_KWI ?? 0 }}" placeholder="0.00">
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-3">
												<label for="tanggung" class="form-label">PPN Tanggung Pemerintah(Y/N)</label>
											</div>
											<div class="col-md-9">
												<select class="form-control form-control-sm" id="tanggung" name="tanggung">
													<option value="">-- Pilih --</option>
													<option value="Y" {{ ($header->TANGGUNG ?? '') == 'Y' ? 'selected' : '' }}>Y</option>
													<option value="N" {{ ($header->TANGGUNG ?? '') == 'N' ? 'selected' : '' }}>N</option>
												</select>
											</div>
										</div>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-md-12">
										<button type="button" id="SAVEX" onclick="simpan()" class="btn btn-success">
											<i class="fas fa-save"></i> Save
										</button>
										<button type="button" id="CLOSEX" onclick="closeForm()" class="btn btn-outline-secondary">
											<i class="fas fa-times"></i> Close
										</button>
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

@section('footer-scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
	$(document).ready(function() {
		var status = $('#status').val();
		if (status == 'simpan') {
			$('#no_bukti').focus();
		}

		$('#chk_catatan').on('change', function() {
			if ($(this).is(':checked')) {
				$('#catatan').val(
					'Bukti Potong PPH 4(2) diserahkan paling lambat 2 bulan sejak awal periode sewa. Jika tidak sesuai dengan batas waktu yang ditentukan, uang titipan PPH tidak dapat ditarik kembali (PKS psl III point D).');
			} else {
				$('#catatan').val('');
			}
		});

		$('#areal').on('change', function() {
			var areal = $(this).val();
			if (areal) {
				$.ajax({
					url: "{{ route('phentrypenyewatempat.get-rekening') }}",
					type: 'GET',
					data: {
						areal: areal
					},
					success: function(response) {
						if (response.success) {
							$('#no_rek').empty();
							$('#no_rek').append('<option value="">-- Pilih Rekening --</option>');
							$.each(response.rekening, function(index, rek) {
								$('#no_rek').append('<option value="' + rek.NO_REK + '">' + rek.NO_REK + ' - ' + rek
									.NAMA_REK + '</option>');
							});

							if (response.prior) {
								$('#no_rek').val(response.prior.NO_REK);
								$('#cabang_rek').text(response.prior.CABANG_REK);
							}
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal mengambil data rekening'
						});
					}
				});
			}
		});

		$('#no_rek').on('change', function() {
			var areal = $('#areal').val();
			var norek = $(this).val();
			if (areal && norek) {
				$.ajax({
					url: "{{ route('phentrypenyewatempat.get-rekening') }}",
					type: 'GET',
					data: {
						areal: areal
					},
					success: function(response) {
						if (response.success) {
							var selectedRek = response.rekening.find(function(rek) {
								return rek.NO_REK === norek;
							});
							if (selectedRek) {
								$('#cabang_rek').text(selectedRek.CABANG_REK);
							}
						}
					}
				});
			}
		});

		$('#kodes').on('blur', function() {
			var kodes = $(this).val().trim();
			if (kodes && status == 'simpan') {
				$.ajax({
					url: "{{ route('phentrypenyewatempat.get-config') }}",
					type: 'GET',
					data: {
						kodes: kodes
					},
					success: function(response) {
						if (response.success && response.data) {
							$('#ktp').val(response.data.KTP);
							$('#namas').val(response.data.NAMAS);
							$('#al_prsh').val(response.data.Al_prsh);
							$('#al_prsh2').val(response.data.Al_prsh2);
							$('#kota').val(response.data.KOTA);
							$('#no_telp').val(response.data.NO_TELP);
							$('#s_pjk').val(response.data.S_PJK);
							$('#npwp').val(response.data.NPWP);
							$('#cara_byr').val(response.data.CARA_BYR);
							$('#cara_byr2').val(response.data.CARA_BYR2);
							$('#email').val(response.data.EMAIL);
							$('#kd_dist').val(response.data.KD_DISTRIBUTOR);

							if (response.distributor) {
								$('#nama_dist').val(response.distributor.NM_NPWP);
							}
						} else {
							Swal.fire({
								icon: 'warning',
								title: 'Warning',
								text: 'Supplier tidak ditemukan. Silakan tambahkan data supplier terlebih dahulu.',
								confirmButtonText: 'OK'
							}).then(() => {
								$('#kodes').val('').focus();
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal mengambil data supplier'
						});
					}
				});
			}
		});

		$('#tarif').on('input change', function() {
			hitungPajak();
		});

		$('#tg_selesai').on('blur', function() {
			var tgMulai = new Date($('#tg_mulai').val());
			var tgSelesai = new Date($('#tg_selesai').val());

			if (tgMulai > tgSelesai) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Periode mulai salah!'
				});
				$('#tg_mulai').val($('#tg_selesai').val());
				$('#tg_mulai').focus();
			}
		});

		$('body').on('keydown', function(e) {
			if (e.key === "Enter") {
				e.preventDefault();
				handleEnterKey(e.target);
				return false;
			}
		});
	});

	function handleEnterKey(element) {
		var $element = $(element);
		var form = $element.parents('form:eq(0)');
		var focusable = form.find('input,select,textarea,button').filter(':visible:not([readonly]):not([disabled])');
		var next = focusable.eq(focusable.index(element) + 1);
		if (next.length) {
			next.focus().select();
		}
	}

	function hitungPajak() {
		var tarif = parseFloat($('#tarif').val()) || 0;
		var ppnPersen = 11;
		var pphPersen = 10;

		var ppn = Math.floor(tarif * ppnPersen / 100);
		var pph = Math.floor(tarif * pphPersen / 100);
		var bayar = tarif + ppn;

		$('#ppn').val(ppn);
		$('#pph').val(pph);
		$('#bayar').val(bayar);
	}

	function simpan() {
		var no_bukti = $('#no_bukti').val().trim();
		var kodes = $('#kodes').val().trim();
		var kd_sarana = $('#kd_sarana').val();
		var areal = $('#areal').val();
		var no_rek = $('#no_rek').val();
		var tg_mulai = $('#tg_mulai').val();
		var tg_selesai = $('#tg_selesai').val();

		if (!kodes) {
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'No.Suplier masih kosong..'
			});
			$('#kodes').focus();
			return;
		}

		if (!kd_sarana) {
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'Bentuk sarana masih kosong..'
			});
			$('#kd_sarana').focus();
			return;
		}

		if (!areal) {
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'Pilih areal..'
			});
			$('#areal').focus();
			return;
		}

		if (!no_rek) {
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'Pilih rekening pembayaran..'
			});
			$('#no_rek').focus();
			return;
		}

		if (no_bukti == '+') {
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'No. Penyewa masih kosong..'
			});
			$('#no_bukti').focus();
			return;
		}

		if (!tg_mulai || !tg_selesai) {
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'Periode harus diisi!'
			});
			return;
		}

		var tgMulai = new Date(tg_mulai);
		var tgSelesai = new Date(tg_selesai);

		if (tgMulai > tgSelesai) {
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'Periode mulai salah!'
			});
			$('#tg_mulai').focus();
			return;
		}

		Swal.fire({
			title: 'Konfirmasi',
			text: 'Apakah data sudah benar?',
			icon: 'question',
			showCancelButton: true,
			confirmButtonColor: '#28a745',
			cancelButtonColor: '#6c757d',
			confirmButtonText: 'Ya, Simpan',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				Swal.fire({
					title: 'Menyimpan...',
					text: 'Mohon tunggu',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading()
					}
				});

				$.ajax({
					url: "{{ route('phentrypenyewatempat.save-config') }}",
					type: 'POST',
					data: $('#entri').serialize(),
					success: function(response) {
						Swal.close();
						if (response.success) {
							Swal.fire({
								title: 'Success!',
								text: 'Save Data Success',
								icon: 'success',
								confirmButtonText: 'OK'
							}).then(() => {
								window.location.href = "{{ route('phentrypenyewatempat') }}";
							});
						} else {
							Swal.fire({
								title: 'Error!',
								text: response.message,
								icon: 'error',
								confirmButtonText: 'OK'
							});
						}
					},
					error: function(xhr) {
						Swal.close();
						var errorMessage = 'Terjadi kesalahan saat menyimpan data';
						if (xhr.responseJSON && xhr.responseJSON.message) {
							errorMessage = xhr.responseJSON.message;
						}
						Swal.fire({
							title: 'Error!',
							text: errorMessage,
							icon: 'error',
							confirmButtonText: 'OK'
						});
					}
				});
			}
		});
	}

	function closeForm() {
		window.location.href = "{{ route('phentrypenyewatempat') }}";
	}
</script>
@endsection