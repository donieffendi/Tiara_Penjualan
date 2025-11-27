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
									<label>Tanggal Dari</label>
									<input type="date" class="form-control" id="tglDari" value="{{ date('Y-m-d') }}">
								</div>
								<div class="col-md-3">
									<label>Tanggal Sampai</label>
									<input type="date" class="form-control" id="tglSampai" value="{{ date('Y-m-d') }}">
								</div>
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
									<label>&nbsp;</label><br>
									<button type="button" class="btn btn-primary" id="btnCetak">
										<i class="fas fa-print mr-1"></i>FILTER
									</button>
									<button type="button" class="btn btn-secondary" onclick="window.close()">
										<i class="fas fa-times mr-1"></i>TUTUP
									</button>
									<!-- HTML - Pisahkan kedua elemen -->
									<a id="btnPrintGayan" type="button" class="btn btn-secondary">
										<i class="fas fa-print mr-1"></i>PRINT
									</a>

								</div>
							</div>

							<hr>

							<div class="row">
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
	var JENIS_LAP = '{{ $jenisLap }}';
	// JavaScript
	$('#btnPrintGayan').click(function() {
		if ($('#tabelHasil tbody tr').length === 0) {
			alert("Data Tidak Ditemukan!");
			return;
		}

		let url = "{{ route('rpromoGayan.cetakPDF') }}?cbgMa=" + CBG_MA + "&jenisLap=" + JENIS_LAP;
		url += "&tglDari=" + $('#tglDari').val();
		url += "&tglSampai=" + $('#tglSampai').val();
		url += "&tipePromo=" + $('#cbPromo').val();

		window.open(url + '&reportName=Promo_Turun_Harga_Penjualan', "_blank");
		if (JENIS_LAP == 'CETAK_PER_ITEM' && $('#cbPromo').val() == 'TURUN HARGA') {
			location.href = url;

		}
	});




	$(document).ready(function() {
		$('#btnCetak').on('click', function() {
			cetakLaporan();
		});
	});

	function cetakLaporan() {
		var tglDari = $('#tglDari').val();
		var tglSampai = $('#tglSampai').val();
		var tipePromo = $('#cbPromo').val();

		if (!tipePromo) {
			alert('Cek pilihan promo');
			return;
		}

		$.ajax({
			url: "{{ route('rpromoGayan.cetak') }}",
			method: 'POST',
			data: {
				_token: '{{ csrf_token() }}',
				jenisLap: JENIS_LAP,
				tipePromo: tipePromo,
				cbgMa: CBG_MA,
				tglDari: tglDari,
				tglSampai: tglSampai
			},
			beforeSend: function() {
				$('#tabelHasil').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
			},
			success: function(response) {
				if (response.success) {
					tampilkanTabel(response.data, response.tipePromo, response.jenisLap);
				} else {
					alert(response.message);
					$('#tabelHasil').html('');
				}
			},
			error: function(xhr) {
				alert('Error: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
				$('#tabelHasil').html('');
			}
		});
	}

	function tampilkanTabel(data, tipePromo, jenisLap) {

		var html = '<div class="table-responsive"><table id="tblData" class="table table-bordered table-striped"><thead><tr>';
		html += '<th>No</th><th>Sub</th><th>Kode Barang</th><th>Nama Barang</th><th>Ukuran</th>';
		html += '</tr></thead><tbody>';

		$.each(data, function(i, row) {
			html += '<tr>';
			html += '<td>' + (i + 1) + '</td>';

				html += '<td>' + (row.SUB || '') + '</td>';
				html += '<td>' + (row.KD_BRG || '') + '</td>';
				html += '<td>' + (row.NA_BRG || '') + '</td>';
				html += '<td>' + (row.KET_UK || '') + '</td>';

			html += '</tr>';
		});

		html += '</tbody></table></div>';
		$('#tabelHasil').html(html);

		$('#tblData').KoolDataTable({
			searching: true,
			paging: true,
			ordering: true,
			pageSize: 25
		});
	}

	function formatNumber(num) {
		return parseFloat(num).toLocaleString('id-ID', {
			minimumFractionDigits: 0,
			maximumFractionDigits: 2
		});
	}
</script>
@endsection