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
	</style>

	<div class="content-wrapper">
		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<h3 class="card-title">Hadiah Cashback - {{ $status == 'simpan' ? 'New' : 'Edit' }}</h3>
							</div>
							<div class="card-body">
								<form action="{{ route('phhadiahcashback.save-config') }}" method="POST" name="entri" id="entri">
									@csrf
									<input type="hidden" id="status" name="status" value="{{ $status }}">

									<div class="row">
										<div class="col-md-6">
											<div class="form-group row">
												<div class="col-md-3">
													<label for="no_bukti" class="form-label">Bukti#</label>
												</div>
												<div class="col-md-9">
													<input type="text" class="form-control form-control-sm" id="no_bukti" name="no_bukti"
														value="{{ $status == 'simpan' ? '+' : $header->no_bukti ?? '' }}" readonly placeholder="No Bukti" required>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-3">
													<label for="tgl" class="form-label">Date</label>
												</div>
												<div class="col-md-9">
													<input type="date" class="form-control form-control-sm" id="tgl" name="tgl"
														value="{{ $header ? date('Y-m-d', strtotime($header->TGL)) : date('Y-m-d') }}" required>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-3">
													<label for="kd_prm" class="form-label">Kode Promosi</label>
												</div>
												<div class="col-md-9">
													<input type="text" class="form-control form-control-sm" id="kd_prm" name="kd_prm" value="{{ $header->kd_prm ?? '' }}"
														placeholder="Kode Promosi" {{ $status == 'edit' ? 'readonly' : '' }} required>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-3">
													<label for="jns_dis" class="form-label">Jenis Promo</label>
												</div>
												<div class="col-md-9">
													<select class="form-control form-control-sm" id="jns_dis" name="jns_dis" required>
														<option value="">-- Pilih Jenis --</option>
														<option value="RUPIAH" {{ ($header->jns_dis ?? '') == 'RUPIAH' ? 'selected' : '' }}>RUPIAH</option>
														<option value="PERSEN" {{ ($header->jns_dis ?? '') == 'PERSEN' ? 'selected' : '' }}>PERSEN</option>
													</select>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-3">
													<label for="nkartu" class="form-label">No Kartu</label>
												</div>
												<div class="col-md-9">
													<input type="text" class="form-control form-control-sm" id="nkartu" name="nkartu" value="{{ $header->NKARTU ?? '' }}"
														placeholder="No Kartu">
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-3">
													<label for="rp_beli" class="form-label">Total Beli</label>
												</div>
												<div class="col-md-9">
													<input type="number" step="0.01" class="form-control form-control-sm" id="rp_beli" name="rp_beli"
														value="{{ $header->rp_beli ?? 0 }}" placeholder="0.00">
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-3">
													<label for="disc" class="form-label">Disc</label>
												</div>
												<div class="col-md-9">
													<input type="number" step="0.01" class="form-control form-control-sm" id="disc" name="disc" value="{{ $header->disc ?? 0 }}"
														placeholder="0.00">
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-3">
													<label for="rp_disc_max" class="form-label">Rp Disc Max</label>
												</div>
												<div class="col-md-9">
													<input type="number" step="0.01" class="form-control form-control-sm" id="rp_disc_max" name="rp_disc_max"
														value="{{ $header->rp_disc_max ?? 0 }}" placeholder="0.00">
												</div>
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group row">
												<div class="col-md-3">
													<label for="jns" class="form-label">Type</label>
												</div>
												<div class="col-md-9">
													<select class="form-control form-control-sm" id="jns" name="jns" required>
														<option value="">-- Pilih Type --</option>
														<option value="ITEM" {{ ($header->jns ?? '') == 'ITEM' ? 'selected' : '' }}>ITEM</option>
														<option value="KELOMPOK" {{ ($header->jns ?? '') == 'KELOMPOK' ? 'selected' : '' }}>KELOMPOK</option>
														<option value="SEMUA" {{ ($header->jns ?? '') == 'SEMUA' ? 'selected' : '' }}>SEMUA</option>
													</select>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-3">
													<label for="tg_mulai" class="form-label">Mulai Dari</label>
												</div>
												<div class="col-md-9">
													<input type="date" class="form-control form-control-sm" id="tg_mulai" name="tg_mulai"
														value="{{ $header ? date('Y-m-d', strtotime($header->tg_mulai)) : date('Y-m-d') }}" required>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-3">
													<label for="jm_mulai" class="form-label">Jam Mulai</label>
												</div>
												<div class="col-md-4">
													<input type="time" class="form-control form-control-sm" id="jm_mulai" name="jm_mulai"
														value="{{ $header ? date('H:i', strtotime($header->jm_mulai)) : '00:00' }}" required>
												</div>
												<div class="col-md-1 text-center">
													<label class="form-label">s/d</label>
												</div>
												<div class="col-md-4">
													<input type="time" class="form-control form-control-sm" id="jm_akhir" name="jm_akhir"
														value="{{ $header ? date('H:i', strtotime($header->jm_akhir)) : '23:59' }}" required>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-3">
													<label for="tg_akhir" class="form-label">Tgl Berakhir</label>
												</div>
												<div class="col-md-9">
													<input type="date" class="form-control form-control-sm" id="tg_akhir" name="tg_akhir"
														value="{{ $header ? date('Y-m-d', strtotime($header->tg_akhir)) : date('Y-m-d') }}" required>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-3">
													<label for="tg_dis_akhir" class="form-label">Tgl Berakhir Disc</label>
												</div>
												<div class="col-md-9">
													<input type="date" class="form-control form-control-sm" id="tg_dis_akhir" name="tg_dis_akhir"
														value="{{ $header ? date('Y-m-d', strtotime($header->tg_dis_akhir)) : date('Y-m-d') }}" required>
												</div>
											</div>

											<div class="form-group row">
												<div class="col-md-3">
													<label for="maxh" class="form-label">Max Hadiah</label>
												</div>
												<div class="col-md-9">
													<input type="number" step="1" class="form-control form-control-sm" id="maxh" name="maxh"
														value="{{ $header->maxh ?? 0 }}" placeholder="0">
												</div>
											</div>
										</div>
									</div>

									<div class="row mt-2">
										<div class="col-md-6">
											<div class="card">
												<div class="card-header bg-info text-white">
													<h6 class="mb-0">Barang Untuk Mendapatkan Diskon</h6>
												</div>
												<div class="card-body p-2">
													<div class="form-group">
														<label id="lblBarang1" class="text-info"></label>
													</div>
													<div class="input-group input-group-sm mb-2">
														<input type="text" class="form-control form-control-sm" id="kd_brg1" placeholder="Kode Barang">
														<button type="button" class="btn btn-sm btn-primary" onclick="addBarang(1)">
															<i class="fas fa-plus"></i> Add
														</button>
													</div>
													<textarea class="form-control form-control-sm" id="brg" name="brg" rows="8" placeholder="Kode barang dipisah koma (,)">{{ $header->brg ?? '' }}</textarea>
												</div>
											</div>
										</div>

										<div class="col-md-6">
											<div class="card">
												<div class="card-header bg-success text-white">
													<h6 class="mb-0">Barang Yang Didiskon</h6>
												</div>
												<div class="card-body p-2">
													<div class="form-group">
														<label id="lblBarang2" class="text-success"></label>
													</div>
													<div class="input-group input-group-sm mb-2">
														<input type="text" class="form-control form-control-sm" id="kd_brg2" placeholder="Kode Barang">
														<button type="button" class="btn btn-sm btn-success" onclick="addBarang(2)">
															<i class="fas fa-plus"></i> Add
														</button>
													</div>
													<textarea class="form-control form-control-sm" id="brg_disc" name="brg_disc" rows="8" placeholder="Kode barang dipisah koma (,)">{{ $header->brg_disc ?? '' }}</textarea>
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
				$('#tgl').focus();
			} else {
				$('#kd_prm').attr('readonly', true);
			}

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
			var id = $element.attr('id');

			var form = $element.parents('form:eq(0)');
			var focusable = form.find('input,select,textarea,button').filter(':visible:not([readonly]):not([disabled])');
			var next = focusable.eq(focusable.index(element) + 1);
			if (next.length) {
				next.focus().select();
			}
		}

		function addBarang(type) {
			var kd_brg = type == 1 ? $('#kd_brg1').val().trim() : $('#kd_brg2').val().trim();
			var jns = $('#jns').val();

			if (!kd_brg) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Kode barang tidak boleh kosong!'
				});
				return;
			}

			$.ajax({
				url: '{{ route('phhadiahcashback.get-config') }}',
				type: 'GET',
				data: {
					kd_brg: kd_brg,
					jns: jns,
					type: type
				},
				success: function(response) {
					if (response.success && response.data) {
						if (response.exists_in_promo) {
							Swal.fire({
								title: 'Konfirmasi',
								text: 'Barang sudah ada di kode promo ' + response.kd_prm + ' lanjutkan?',
								icon: 'question',
								showCancelButton: true,
								confirmButtonText: 'Ya',
								cancelButtonText: 'Tidak'
							}).then((result) => {
								if (result.isConfirmed) {
									appendBarang(type, response.data);
								}
							});
						} else {
							appendBarang(type, response.data);
						}
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.message || 'Barang tidak ditemukan'
						});
					}
				},
				error: function() {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Gagal mengambil data barang'
					});
				}
			});
		}

		function appendBarang(type, data) {
			var textarea = type == 1 ? $('#brg') : $('#brg_disc');
			var label = type == 1 ? $('#lblBarang1') : $('#lblBarang2');
			var input = type == 1 ? $('#kd_brg1') : $('#kd_brg2');

			var currentValue = textarea.val().trim();
			var kd_brg = data.kd_brgh || data.kd_brg;

			label.text(kd_brg + ' - ' + data.na_brgh);

			if (currentValue === '') {
				textarea.val(kd_brg);
			} else {
				var items = currentValue.split(',');
				var lastItem = items[items.length - 1].trim();
				if (lastItem !== kd_brg) {
					textarea.val(currentValue + ',' + kd_brg);
				}
			}

			input.val('').focus();
		}

		function simpan() {
			var tgl = $('#tgl').val();
			var kd_prm = $('#kd_prm').val();
			var jns_dis = $('#jns_dis').val();
			var jns = $('#jns').val();
			var tg_mulai = $('#tg_mulai').val();
			var tg_akhir = $('#tg_akhir').val();
			var jm_mulai = $('#jm_mulai').val();
			var jm_akhir = $('#jm_akhir').val();

			if (!tgl) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Tanggal harus diisi!'
				});
				$('#tgl').focus();
				return;
			}

			if (!kd_prm) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Kode Promosi harus diisi!'
				});
				$('#kd_prm').focus();
				return;
			}

			if (!jns_dis) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Jenis Promo harus dipilih!'
				});
				$('#jns_dis').focus();
				return;
			}

			if (!jns) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Type harus dipilih!'
				});
				$('#jns').focus();
				return;
			}

			if (!tg_mulai) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Tanggal Mulai harus diisi!'
				});
				$('#tg_mulai').focus();
				return;
			}

			if (!tg_akhir) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Tanggal Berakhir harus diisi!'
				});
				$('#tg_akhir').focus();
				return;
			}

			if (jm_mulai == '00:00' || jm_mulai == '00:00:00') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Filter Jam Mulai, tidak boleh kosong!!'
				});
				$('#jm_mulai').focus();
				return;
			}

			if (jm_akhir == '00:00' || jm_akhir == '00:00:00') {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Filter Jam Selesai, tidak boleh kosong!!'
				});
				$('#jm_akhir').focus();
				return;
			}

			if (new Date(tg_akhir) < new Date(tg_mulai)) {
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Tanggal Selesai Harus Lebih Tinggi Tanggal Mulai!!'
				});
				$('#tg_akhir').focus();
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
						url: '{{ route('phhadiahcashback.save-config') }}',
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
									window.location.href = '{{ route('phhadiahcashback') }}';
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
						error: function(xhr, status, error) {
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
			window.location.href = '{{ route('phhadiahcashback') }}';
		}
	</script>
@endsection
