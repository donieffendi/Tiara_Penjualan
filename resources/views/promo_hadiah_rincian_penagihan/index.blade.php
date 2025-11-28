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
									<label>Periode</label>
									<select class="form-control" id="cbPeriode">
										<option value="">- Pilih Periode -</option>
										@foreach ($periodeList as $periode)
										<option value="{{ $periode }}" {{ $periode == $currentPeriode ? 'selected' : '' }}>{{ $periode }}</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-3">
									<label>Status Pajak</label>
									<select class="form-control" id="cbPajak">
										<option value="">- Pilih Status Pajak -</option>
										<option value="NPWP BP">NPWP BP</option>
										<option value="NPWP NON BP">NPWP NON BP</option>
										<option value="NON NPWP">NON NPWP</option>
										<option value="SEMUA">SEMUA</option>
									</select>
								</div>
								<div class="col-md-3">
									<label>&nbsp;</label>
									<div class="form-check">
										<input class="form-check-input" type="checkbox" id="chkPPN">
										<label class="form-check-label" for="chkPPN">
											PPN Ditanggung
										</label>
									</div>
								</div>
							</div>

							<div class="row mb-3">
								<div class="col-md-12">
									<button type="button" class="btn btn-primary" id="btnTampil" onclick="btnTampilkan()">
										<i class="fas fa-search mr-1"></i>Tampil
									</button>
									<button type="button" class="btn btn-success ml-2" id="btnExcel" onclick="btnExcel()">
										<i class="fas fa-file-excel mr-1"></i>Excel
									</button>
									<button type="button" class="btn btn-info ml-2" id="btnPrint" onclick="btnPrint()">
										<i class="fas fa-print mr-1"></i>Print
									</button>
									<button type="button" class="btn btn-secondary ml-2" id="btnClose" onclick="btnClosed()">
										<i class="fas fa-times mr-1"></i>Close
									</button>
								</div>
							</div>

							<div class="row mb-3">
								<div class="col-md-4">
									<label>Cetak Ulang (No. Tagihan)</label>
									<div class="input-group">
										<input type="text" class="form-control" id="txtNoTagih" placeholder="Masukkan nomor tagihan">
										<div class="input-group-append">
											<button class="btn btn-warning" type="button" id="btnCetakUlang" onclick="cetakUlang()">
												<i class="fas fa-redo mr-1"></i>Cetak Ulang
											</button>
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12">
									<table id="tblRincianPenagihan" class="table-bordered table-striped table">
										<thead>
											<tr>
												<th>No Rincian</th>
												<th>Sarana</th>
												<th>Nama Supplier</th>
												<th>Merek</th>
												<th>Periode</th>
												<th>Tarif Per Sarana</th>
												<th>PPN</th>
												<th>PPH</th>
												<th>Yang Harus Ditagih</th>
												<th>Sistem Pembayaran</th>
												<th>Distributor</th>
												<th>Keterangan/Pajak</th>
											</tr>
										</thead>
										<tbody></tbody>
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
	var table;
	var cbgMst = "{{ $cbgMst }}";

	function btnTampilkan() {
		var periode = $('#cbPeriode').val().trim();
		var statusPajak = $('#cbPajak').val().trim();
		var tanggungPPN = $('#chkPPN').is(':checked') ? 1 : 0;

		if (periode == '') {
			alert('Cek Periode!');
			$('#cbPeriode').focus();
			return;
		}

		if (statusPajak == '') {
			alert('Pilih Status Pajak!');
			$('#cbPajak').focus();
			return;
		}
		$.ajax({
			url: "{{ route('phrincianpenagihan.get-data') }}",
			method: 'POST',
			data: {
				_token: '{{ csrf_token() }}',
				periode: periode,
				cbg: cbgMst,
				status_pajak: statusPajak,
				tanggung_ppn: tanggungPPN
			},
			success: function(response) {
				if (response.success) {
					table.clear();
					if (response.data && response.data.length > 0) {
						table.rows.add(response.data);
					}
					table.draw();
				} else {
					alert(response.message);
				}
			},
			error: function() {
				alert('Terjadi kesalahan saat mengambil data');
			}
		});
	}
	$(document).ready(function() {
		table = $('#tblRincianPenagihan').KoolDataTable({
			processing: true,
			serverSide: false,
			paging: true,
			searching: true,
			ordering: true,
			columns: [{
					data: 'no_rincian'
				},
				{
					data: 'sarana'
				},
				{
					data: 'nama_supplier'
				},
				{
					data: 'merek'
				},
				{
					data: 'periode'
				},
				{
					data: 'tarif_per_sarana'
				},
				{
					data: 'ppn'
				},
				{
					data: 'pph'
				},
				{
					data: 'yang_harus_ditagih'
				},
				{
					data: 'sistem_pembayaran'
				},
				{
					data: 'distributor'
				},
				{
					data: 'keterangan_pajak'
				}
			]
		});
	});


	function btnExcel() {
		var periode = $('#cbPeriode').val().trim();
		var statusPajak = $('#cbPajak').val().trim();
		var tanggungPPN = $('#chkPPN').is(':checked') ? 1 : 0;

		if (periode == '') {
			alert('Cek Periode!');
			$('#cbPeriode').focus();
			return;
		}

		if (statusPajak == '') {
			alert('Pilih Status Pajak!');
			$('#cbPajak').focus();
			return;
		}

		$.ajax({
			url: "{{ route('phrincianpenagihan.export-excel') }}",
			method: 'POST',
			data: {
				_token: '{{ csrf_token() }}',
				periode: periode,
				cbg: cbgMst,
				status_pajak: statusPajak,
				tanggung_ppn: tanggungPPN
			},
			success: function(response) {
				if (response.success) {
					var ws_data = [
						['No Rincian', 'Sarana', 'Nama Supplier', 'Merek', 'Periode', 'Tarif Per Sarana', 'PPN',
							'PPH', 'Yang Harus Ditagih', 'Sistem Pembayaran', 'Distributor', 'Keterangan/Pajak'
						]
					];

					response.data.forEach(function(row) {
						ws_data.push([
							row.no_rincian,
							row.sarana,
							row.nama_supplier,
							row.merek,
							row.periode,
							row.tarif_per_sarana,
							row.ppn,
							row.pph,
							row.yang_harus_ditagih,
							row.sistem_pembayaran,
							row.distributor,
							row.keterangan_pajak
						]);
					});

					var ws = XLSX.utils.aoa_to_sheet(ws_data);
					var wb = XLSX.utils.book_new();
					XLSX.utils.book_append_sheet(wb, ws, 'Rincian Penagihan');
					XLSX.writeFile(wb, 'Rincian_Penagihan_Sewa_' + periode.replace('/', '_') + '.xlsx');
				} else {
					alert(response.message);
				}
			},
			error: function() {
				alert('Terjadi kesalahan saat export excel');
			}
		});
	};

	function btnPrint() {
		var dataTable = table.rows().data();
		if (dataTable.length === 0) {
			alert('Tidak ada data untuk dicetak!');
			return;
		}
		window.print();
	};

	function cetakUlang() {
		var noTagih = $('#txtNoTagih').val().trim();

		if (noTagih == '') {
			alert('Masukkan nomor tagihan!');
			$('#txtNoTagih').focus();
			return;
		}

		$.ajax({
			url: "{{ route('phrincianpenagihan.cetak-ulang') }}",
			method: 'POST',
			data: {
				_token: '{{ csrf_token() }}',
				no_tagih: noTagih,
				cbg: cbgMst
			},
			success: function(response) {
				if (response.success) {
					table.clear();
					if (response.data && response.data.length > 0) {
						table.rows.add(response.data);
					}
					table.draw();
					alert('Data berhasil dimuat!');
				} else {
					alert(response.message);
				}
			},
			error: function() {
				alert('Terjadi kesalahan saat cetak ulang');
			}
		});
	};

	function btnClosed() {
		window.location.href = "{{ url('/') }}";
	};
</script>
@endsection