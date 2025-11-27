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
										<label>Jenis Promo</label>
										<select class="form-control" id="cbPromo">
											<option value="">Pilih Promo</option>
											<option value="TURUN HARGA">TURUN HARGA</option>
											<option value="CASHBACK">CASHBACK</option>
											<option value="POIN">POIN</option>
										</select>
									</div>
									<div class="col-md-3">
										<label>Nama File</label>
										<div class="input-group">
											<input type="text" class="form-control" id="txtNamafile" placeholder="Nama file tanpa ekstensi">
											<div class="input-group-append">
												<span class="input-group-text" id="lblExt"></span>
											</div>
										</div>
									</div>
									<div class="col-md-3">
										<label>&nbsp;</label><br>
										<button type="button" class="btn btn-primary" id="btnAmbil">
											<i class="fas fa-upload mr-1"></i>PROSES FILE
										</button>
									</div>
									<div class="col-md-3 text-right">
										<label>&nbsp;</label><br>
										<button type="button" class="btn btn-secondary" onclick="window.close()">
											<i class="fas fa-times mr-1"></i>TUTUP
										</button>
									</div>
								</div>

								<hr>

								<div class="row">
									<div class="col-12">
										<div id="lblAmbil" class="alert alert-info" style="display:none;"></div>
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-12">
										<div id="tabelHasil"></div>
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
		var CBG_MA = '{{ $cbgMa }}';

		$(document).ready(function() {
			$('#cbPromo').on('change', function() {
				var promo = $(this).val().toUpperCase();
				$('#lblExt').text('');

				if (promo == 'TURUN HARGA') {
					$('#lblExt').text('.PGH');
				} else if (promo == 'CASHBACK') {
					$('#lblExt').text('.PGC');
				} else if (promo == 'POIN') {
					$('#lblExt').text('.PGP');
				}
			});

			$('#btnAmbil').on('click', function() {
				ambilFile();
			});
		});

		function ambilFile() {
			var namafile = $('#txtNamafile').val();
			var promo = $('#cbPromo').val();
			var exten = $('#lblExt').text().replace('.', '').toUpperCase();

			if (!namafile || !promo) {
				alert('Cek nama file/promo!');
				return;
			}

			$.ajax({
				url: "{{ route('phpengesahanfile.create-tabel') }}",
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					cbgMa: CBG_MA
				},
				success: function(response) {
					if (response.success) {
						cekFile(namafile, exten);
					} else {
						alert('Error: ' + response.message);
					}
				}
			});
		}

		function cekFile(namafile, exten) {
			$.ajax({
				url: "{{ route('phpengesahanfile.cek-file') }}",
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					namafile: namafile,
					exten: exten,
					cbgMa: CBG_MA
				},
				success: function(response) {
					if (response.success) {
						if (response.jumcek > 0) {
							if (confirm('File ' + namafile + ' (' + $('#cbPromo').val() + ') sudah pernah diproses. Lakukan import ulang?')) {
								prosesFile(namafile, exten);
							}
						} else {
							prosesFile(namafile, exten);
						}
					} else {
						alert('Error: ' + response.message);
					}
				}
			});
		}

		function prosesFile(namafile, exten) {
			$('#lblAmbil').hide().text('Sedang memproses...');
			$('#lblAmbil').show();

			$.ajax({
				url: "{{ route('phpengesahanfile.proses-file') }}",
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					namafile: namafile,
					exten: exten,
					cbgMa: CBG_MA
				},
				success: function(response) {
					if (response.success) {
						$('#lblAmbil').removeClass('alert-info').addClass('alert-success');
						$('#lblAmbil').text(response.message);
						loadCetak(namafile, exten);
					} else {
						$('#lblAmbil').removeClass('alert-info').addClass('alert-danger');
						$('#lblAmbil').text('Error: ' + response.message);
					}
				}
			});
		}

		function loadCetak(namafile, exten) {
			$.ajax({
				url: "{{ route('phpengesahanfile.get-cetak') }}",
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					namafile: namafile,
					exten: exten,
					cbgMa: CBG_MA
				},
				success: function(response) {
					if (response.success && response.data) {
						tampilkanTabel(response.data, exten);
					}
				}
			});
		}

		function tampilkanTabel(data, exten) {
			var html = '<table class="table table-bordered table-striped"><thead><tr>';
			html += '<th>No Bukti</th><th>Tanggal</th><th>Kode Barang</th><th>Nama Barang</th>';
			html += '<th>Supplier</th><th>Tgl Dari</th><th>Tgl Sampai</th><th>Qty Max</th>';

			if (exten == 'PGH') {
				html += '<th>Part Sup</th><th>Part Tiara</th>';
			} else if (exten == 'PGC') {
				html += '<th>Cashback</th>';
			} else if (exten == 'PGP') {
				html += '<th>Poin</th>';
			}

			html += '</tr></thead><tbody>';

			$.each(data, function(i, row) {
				html += '<tr>';
				html += '<td>' + row.NO_BUKTI + '</td>';
				html += '<td>' + row.TGL + '</td>';
				html += '<td>' + row.KD_BRG + '</td>';
				html += '<td>' + row.NA_BRG + '</td>';
				html += '<td>' + row.SUPP + '</td>';
				html += '<td>' + row.TGL_DARI + '</td>';
				html += '<td>' + row.TGL_SAMPAI + '</td>';
				html += '<td>' + row.QTY_MAX + '</td>';

				if (exten == 'PGH') {
					html += '<td>' + row.PART_SUP + '</td>';
					html += '<td>' + row.PART_TIARA + '</td>';
				} else if (exten == 'PGC') {
					html += '<td>' + row.RP_CASHBACK + '</td>';
				} else if (exten == 'PGP') {
					html += '<td>' + row.GET_POIN + '</td>';
				}

				html += '</tr>';
			});

			html += '</tbody></table>';

			$('#tabelHasil').html(html);
		}
	</script>
@endsection
