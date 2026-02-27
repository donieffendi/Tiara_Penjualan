@extends('layouts.plain')

@section('styles')
<link rel="stylesheet" href="{{url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
{{-- <link rel="stylesheet" href="{{url('https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css') }}"> --}}

@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Report Barang DCK</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Barang DCK</li>
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
								@if (isset($error))
									<div class="alert alert-danger alert-dismissible">
										<button type="button" class="close" data-dismiss="alert">&times;</button>
										<strong>Error:</strong> {{ $error }}
									</div>
								@endif

								<form method="POST" action="{{ url('jasper-mutasi-report') }}" id="reportForm">
									@csrf
									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="detail-tab" data-toggle="tab" href="#detail" role="tab" aria-controls="detail" aria-selected="true">
												<i class="fas fa-list-alt mr-1"></i>Periode
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="summary-tab" data-toggle="tab" href="#summary" role="tab" aria-controls="summary" aria-selected="false">
												<i class="fas fa-chart-bar mr-1"></i>mutasi
											</a>
										</li>
										{{-- <li class="nav-item" role="presentation">
											<a class="nav-link" id="kasir-tab" data-toggle="tab" href="#kasir" role="tab" aria-controls="kasir" aria-selected="false">
												<i class="fas fa-user mr-1"></i>Per Nota
											</a>
										</li> --}}
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Detail Transaksi Tab -->
										<div class="tab-pane fade show active" id="detail" role="tabpanel" aria-labelledby="detail-tab">
											<div class="pt-3">
												<div class="form-group">
													<!-- Search Filter Row -->
													<div class="row align-items-end mb-3">
														<div class="col-2">
															<label for="cbg_detail">Cabang</label>
															<select name="cbg_detail" id="cbg_detail" class="form-control" required>
																<option value="">Pilih Cabang</option>
																@foreach ($cbg as $cabang)
																	<option value="{{ $cabang->KODE }}">{{ $cabang->KODE }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-2">
															<label for="per">Periode</label>
															<select name="per" id="per" class="form-control" required>
																<option value="">Pilih Periode</option>
																@foreach ($per as $periode)
																	<option value="{{ $periode->PERIO }}">{{ $periode->PERIO }}</option>
																@endforeach
															</select>
														</div>
													</div>

													<div class="row align-items-end mb-3">
														<div class="col-2">
															<label for="supp">Supp</label>
															<input type="text" name="supp" id="supp" class="form-control" placeholder="Masukkan Supp"
																value="{{ session()->get('filter_supp') }}" maxlength="10">
														</div>
														<div class="col-2">
															<label for="sub">Sub</label>
															<input type="text" name="sub" id="sub" class="form-control" placeholder="Masukkan Sub"
																value="{{ session()->get('filter_sub') }}" maxlength="10">
														</div>
														<div class="col-2">
															<label for="item">Sub Item</label>
															<input type="text" name="item" id="item" class="form-control" placeholder="Masukkan Item"
																value="{{ session()->get('filter_item') }}" maxlength="10">
														</div>
														<div class="col-2">
															<label for="bcd">Barcode</label>
															<input type="text" name="bcd" id="bcd" class="form-control" placeholder="Masukkan Barcode"
																value="{{ session()->get('filter_bcd') }}" maxlength="20">
														</div>
														<div class="col-2">
															<label for="nama">Nama Barang</label>
															<input type="text" name="nama" id="nama" class="form-control" placeholder="Masukkan Nama Barang"
																value="{{ session()->get('filter_nama') }}" maxlength="10">
														</div>
													</div>

													<div class="row align-items-end mb-3">
														<div class="col-md-10"></div>
														<div class="col-2">
															<button class="btn btn-primary mr-1" type="button" id="btnFilterDetail" onclick="filterMutasi('detail')">
																<i class="fas fa-search mr-1"></i>Filter
															</button>
															<button class="btn btn-danger mr-1" type="button" onclick="resetFilter('detail')">
																<i class="fas fa-redo mr-1"></i>Reset
															</button>
														</div>
													</div>

													<!-- Data Table Detail -->
													<div class="col-md-12 report-content" id="detail-result">
														@if (!empty($hasilMutasi))
															<div class="table-responsive">
																<table id="tabelDetail" class="table table-striped table-bordered nowrap" style="width:100%">
																	<thead>
																		<tr>
																			<th>Kode Barang</th>
																			<th>Sub</th>
																			<th>Item</th>
																			<th>Nama Barang</th>
																			<th>Ukuran</th>
																			<th>Kemasan</th>
																			<th>Supplier</th>
																			<th>Langsung</th>
																			<th>Kirim Ke</th>
																			<th>SRMIN</th>
																			<th>SRMAX</th>
																			<th>LH</th>
																			<th>KLK</th>
																			<th>Jenis Brg.</th>
																			<th>Stock</th>
																			<th>Stock Rak</th>
																			<th>Stock Gd.Trans</th>
																			<th>Stok Retur</th>
																			<th>Harga Beli</th>
																			<th>Harga Jual</th>
																			<th>Pesanan</th>
																			<th>Pesanan Terakhir</th>
																			<th>TPJ</th>
																			<th>Lambat</th>
																			<th>Barcode</th>
																			<th>Tarik</th>
																			<th>DTB/Masa EXP</th>
																			<th>Retur</th>
																			<th>KK</th>
																			<th>Tanpa DC</th>
																			<th>DTR</th>
																			<th>DTR2</th>
																			<th>DTR Khusus</th>
																		</tr>
																	</thead>
																	<tbody>
																		@foreach ($hasilMutasi as $item)
																			<tr>
																				<td>{{ $item->KD_BRG ?? '' }}</td>
																				<td>{{ $item->SUB ?? '' }}</td>
																				<td>{{ $item->KDBAR ?? '' }}</td>
																				<td>{{ $item->NA_BRG ?? '' }}</td>
																				<td>{{ $item->KET_UK ?? '' }}</td>
																				<td>{{ $item->KET_KEM ?? '' }}</td>
																				<td>{{ $item->SUPP ?? '' }}</td>
																				<td>{{ $item->STATPSN ?? '' }}</td>
																				<td>{{ $item->KIRIM_KE ?? '' }}</td>
																				<td class="text-right">{{ number_format($item->SRMIN ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->SRMAX ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->LPH ?? 0, 2, ',', '.') }}</td>
																				<td>{{ $item->KLK ?? '' }}</td>
																				<td>{{ $item->TYPE ?? '' }}</td>
																				<td class="text-right">{{ number_format($item->STOK ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->STOCKR ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->STOCKT ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->STOCKG ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->HB ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->HJ ?? 0, 0, ',', '.') }}</td>
																				<td>{{ $item->TDOD ?? '' }}</td>
																				<td>{{ $item->SP_L ?? '' }}</td>
																				<td class="text-right">{{ number_format($item->DTB ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->LAMBAT ?? 0, 0, ',', '.') }}</td>
																				<td>{{ $item->BARCODE ?? '' }}</td>
																				<td class="text-right">{{ number_format($item->TARIK ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->MASA_EXP ?? 0, 0, ',', '.') }}</td>
																				<td>{{ $item->RETUR ?? '' }}</td>
																				<td>{{ $item->KK ?? '' }}</td>
																				<td>{{ $item->ON_DC ?? '' }}</td>
																				<td class="text-right">{{ number_format($item->DTR_DC ?? 0, 2, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->DTR2 ?? 0, 2, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->DTR_MANUAL ?? 0, 2, ',', '.') }}</td>
																			</tr>
																		@endforeach
																	</tbody>
																</table>
															</div>
														@else
															<div class="alert alert-info">
																<i class="fas fa-info-circle mr-2"></i>
																Silakan Klik Filter untuk menampilkan ringkasan barang.
															</div>
														@endif
													</div>
												</div>
											</div>
										</div>

										<!-- Summary Barang Tab -->
										<div class="tab-pane fade" id="summary" role="tabpanel" aria-labelledby="summary-tab">
											<div class="pt-3">
												<div class="form-group">
													<div class="row align-items-end mb-3">
														<div class="col-3">
															<label class="form-label">Filter Lokasi</label>

															<div class="form-check">
																<input class="form-check-input" type="checkbox" name="filter_lokasi[]" value="Toko" id="lok_toko">
																<label class="form-check-label" for="lok_toko">Toko</label>
															</div>
															<div class="form-check">
																<input class="form-check-input" type="checkbox" name="filter_lokasi[]" value="Gd. Transit" id="lok_gd">
																<label class="form-check-label" for="lok_gd">Gd. Transit</label>
															</div>
															<div class="form-check">
																<input class="form-check-input" type="checkbox" name="filter_lokasi[]" value="Retur" id="lok_retur">
																<label class="form-check-label" for="lok_retur">Retur</label>
															</div>
														</div>
													</div>
													<div class="row align-items-end mb-3">
														<div class="col-2">
															<label for="cbg_summary">Cabang</label>
															<select name="cbg_summary" id="cbg_summary" class="form-control" required>
																<option value="">Pilih Cabang</option>
																@foreach ($cbg as $cabang)
																	<option value="{{ $cabang->KODE }}">{{ $cabang->KODE }}</option>
																@endforeach
															</select>
														</div>

														<div class="col-2">
															<label for="kode">Sub Item</label>
															<input type="text" name="kode" id="kode" class="form-control" placeholder="Masukkan Supp"
																value="{{ session()->get('filter_kode') }}" maxlength="10">
														</div>
														<div class="col-md-5"></div>
														<div class="col-3">
															{{-- <button class="btn btn-dark mr-1" type="button" id="btnProsesSummary" onclick="ProsesMutasi('summary')">
																<i class="fas fa-retweet mr-1"></i>Proses
															</button> --}}
															<button class="btn btn-primary mr-1" type="button" id="btnFilterSummary" onclick="filterMutasi('summary')">
																<i class="fas fa-search mr-1"></i>Filter
															</button>
															<button class="btn btn-danger mr-1" type="button" onclick="resetFilter('summary')">
																<i class="fas fa-redo mr-1"></i>Reset
															</button>
														</div>
													</div>

													<div class="col-md-12 report-content" id="summary-result">
														@if (!empty($hasilMutasi))
															<div class="table-responsive">
																<table id="tabelSummary" class="table table-striped table-bordered nowrap" style="width:100%">
																	<thead>
																		<tr>
																			<th>Kode</th>
																			<th>Nama</th>
																			<th>Tanggal</th>
																			<th>Faktur</th>
																			<th>Awal</th>
																			<th>Masuk</th>
																			<th>Keluar</th>
																			<th>Lain</th>
																			<th>Saldo</th>
																		</tr>
																	</thead>
																	<tbody>
																		@foreach ($hasilMutasi as $item)
																			<tr>
																			<td>{{ $item->kd_brg ?? '' }}</td>
<td>{{ $item->NA_BRG ?? '' }}</td>
<td>{{ $item->tgl ?? '' }}</td>
<td>{{ $item->no_bukti ?? '' }}</td>
<td class="text-right">{{ number_format($item->awal ?? 0, 0, ',', '.') }}</td>
<td class="text-right">{{ number_format($item->masuk ?? 0, 0, ',', '.') }}</td>
<td class="text-right">{{ number_format($item->keluar ?? 0, 0, ',', '.') }}</td>
<td class="text-right">{{ number_format($item->LAIN ?? 0, 0, ',', '.') }}</td>
<td class="text-right">{{ number_format($item->SALDO ?? 0, 0, ',', '.') }}</td>
																			</tr>
																		@endforeach
																	</tbody>
																</table>
															</div>
														@else
															<div class="alert alert-info">
																<i class="fas fa-info-circle mr-2"></i>
																Silakan Klik Filter untuk menampilkan ringkasan barang.
															</div>
														@endif
													</div>
												</div>
											</div>
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

	<!-- Modal Summary -->
	<div class="modal fade" id="summaryModal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Summary Kasir Bantu</h5>
					<button type="button" class="close" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<div class="modal-body" id="summaryContent">
					<div class="text-center">
						<i class="fas fa-spinner fa-spin"></i> Loading...
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
<script src="{{url('AdminLTE/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{url('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>
<script src="{{url('http://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js') }}"></script>
<script src="{{url('https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js') }}"></script>
<script src="{{url('https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js') }}"></script>
<script src="{{url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function(){

    // Tab Bootstrap
    $('#reportTabs a').on('click', function(e){
        e.preventDefault();
        $(this).tab('show');
    });

    // Simpan tab aktif
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        localStorage.setItem('activeMutasiTab', $(e.target).attr('href'));
    });

    // Restore tab aktif
    var activeTab = localStorage.getItem('activeMutasiTab');
    if(activeTab){
        $('#reportTabs a[href="'+activeTab+'"]').tab('show');
    }

    // Auto format periode input
    $('#periode_detail, #periode_summary, #periode_kasir').on('input', function(){
        var value = this.value.replace(/\D/g,'');
        if(value.length>=2) this.value = value.substring(0,2)+'-'+value.substring(2,6);
    });

    // Inisialisasi DataTable awal (Detail)
    @if(!empty($hasilMutasi))
        $('#tabelDetail').DataTable({
            pageLength: 25,
            searching: true,
            ordering: true,
            responsive: true,
            columnDefs: [{className:'dt-right', targets:[4]}],
            language:{url:'//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'}
        });
    @endif

});

// -------------------------------
// Fungsi Filter per Tab
// -------------------------------
// function filterMutasi(tabType){
//     var cbg='', btnId='', per='', supp='', sub='', item='', bcd='', nama='', kode='', transit='', toko='', subonly='';
//     switch(tabType){
//         case 'detail':
//             cbg = $('#cbg_detail').val(); // pakai session/auth
// 			per = $('#per').val();
// 			supp = $('#supp').val();
// 			sub = $('#sub').val();
// 			item = $('#item').val();
// 			bcd = $('#bcd').val();
// 			nama = $('#nama').val();

//             btnId = '#btnFilterDetail';
// 			if(!cbg){ alert('Pilih cabang terlebih dahulu'); return; }
//             break;
//         case 'summary':
//             cbg = $('#cbg_summary').val();
// 			kode = $('#kode').val();
//             btnId = '#btnFilterSummary';
// 			if(!cbg){ alert('Pilih cabang terlebih dahulu'); return; }

// 			var transit = $('#lok_gd').is(':checked') ? 1 : 0;
// 			var toko = $('#lok_toko').is(':checked') ? 1 : 0;
// 			var subonly = $('#lok_retur').is(':checked') ? 1 : 0;
//             break;
//     }

//     $(btnId).html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...').prop('disabled',true);

//     $.ajax({
// 		url: '{{ route("get-mutasi-report-ajax") }}',
// 		method: 'GET',
// 		data: { tab: tabType, cbg: cbg, per: per, supp: supp, sub: sub, item: item, bcd: bcd, nama: nama, kode: kode, transit: transit, toko: toko, subonly: subonly },
// 		success: function(res){
// 			if(res.success){
// 				displayTabData(tabType, res.data);
// 			} else {
// 				alert(res.message || 'Gagal memuat data');
// 			}
// 		},
// 		error: function(xhr){
// 			console.error(xhr);
// 			alert('Terjadi kesalahan saat memuat data');
// 		},
// 		complete: function(){
// 			$(btnId).html('<i class="fas fa-search mr-1"></i>Filter').prop('disabled', false);
// 		}
// 	});
// }
function filterMutasi(tabType){

    var cbg='', btnId='', per='', supp='', sub='', item='', bcd='', nama='', kode='', transit=0, toko=0, subonly=0;

    switch(tabType){

        case 'detail':
            cbg = $('#cbg_detail').val();
            per = $('#per').val();
            supp = $('#supp').val();
            sub = $('#sub').val();
            item = $('#item').val();
            bcd = $('#bcd').val();
            nama = $('#nama').val();
            btnId = '#btnFilterDetail';

            if(!cbg){ alert('Pilih cabang terlebih dahulu'); return; }
        break;

        case 'summary':
            cbg = $('#cbg_summary').val();
            kode = $('#kode').val();
            btnId = '#btnFilterSummary';

            if(!cbg){ alert('Pilih cabang terlebih dahulu'); return; }

            transit = $('#lok_gd').is(':checked') ? 1 : 0;
            toko = $('#lok_toko').is(':checked') ? 1 : 0;
            subonly = $('#lok_retur').is(':checked') ? 1 : 0;
        break;
    }

    $(btnId).html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...')
            .prop('disabled',true);

    if(tabType === 'summary'){

        var jasperUrl = '{{ route("jasper-mutasi-report") }}'
            + '?tab=' + tabType
            + '&cbg=' + cbg
            + '&per=' + per
            + '&supp=' + supp
            + '&sub=' + sub
            + '&item=' + item
            + '&bcd=' + bcd
            + '&nama=' + nama
            + '&kode=' + kode;

        window.open(jasperUrl, '_blank');
    }

    $.ajax({
        url: '{{ route("get-mutasi-report-ajax") }}',
        method: 'GET',
        data: {
            tab: tabType,
            cbg: cbg,
            per: per,
            supp: supp,
            sub: sub,
            item: item,
            bcd: bcd,
            nama: nama,
            kode: kode,
            transit: transit,
            toko: toko,
            subonly: subonly
        },
        success: function(res){
            if(res.success){
				console.log(res.data);
                displayTabData(tabType, res.data);

            } else {
                alert(res.message || 'Gagal memuat data');
            }
        },
        error: function(xhr){
            console.error(xhr);
            alert('Terjadi kesalahan saat memuat data');
        },
        complete: function(){
            $(btnId).html('<i class="fas fa-search mr-1"></i>Filter')
                    .prop('disabled', false);
        }
    });
}

// -------------------------------
// Fungsi Render Data di Tab
// -------------------------------
function displayTabData(tabType, data){
    var targetDiv = '#' + tabType + '-result';
    var html = '';

    if(data.length===0){
        html = '<div class="alert alert-warning">Tidak ada data untuk parameter yang dipilih</div>';
    } else {
        html = '<div class="table-responsive"><table class="table table-striped table-bordered" id="table-'+tabType+'"><thead><tr>';

        if(tabType==='detail'){
            html += '<th>Kode Barang</th><th>Sub</th><th>Item</th><th>Nama Barang</th><th>Ukuran</th><th>Kemasan</th><th>Supplier</th><th>Langsung</th><th>Kirim Ke</th><th>SRMIN</th><th>SRMAX</th><th>LH</th><th>KLK</th><th>Jenis Brg.</th><th>Stock</th><th>Stock Rak</th><th>Stock Gd.Trans</th><th>Stock Retur</th><th>Harga Beli</th><th>Harga Jual</th><th>Pesanan</th><th>Pesanan Terakhir</th><th>TPJ</th><th>Lambat</th><th>Barcode</th><th>Tarik</th><th>DTB/Masa EXP</th><th>Retur</th><th>KK</th><th>Tanpa DC</th><th>DTR</th><th>DTR2</th><th>DTR Khusus</th>';
        } else if(tabType==='summary'){
            html += '<th>Kode</th><th>Nama</th><th>Tanggal</th><th>Faktur</th><th>Awal</th><th>Masuk</th><th>Keluar</th><th>Lain</th><th>Saldo</th>';
        }
        html += '</tr></thead><tbody>';

        $.each(data,function(i,item){
            html += '<tr>';
            if(tabType==='detail'){
                html += '<td>'+item.KD_BRG+'</td><td>'+item.SUB+'</td><td>'+item.KDBAR+'</td><td>'+item.NA_BRG+'</td><td>'+item.KET_UK+'</td><td>'+item.KET_KEM+'</td><td>'+item.SUPP+'</td><td>'+item.STATPSN+'</td><td>'+item.KIRIM_KE+'</td><td class="text-right">'+formatNumber(item.SRMIN)+'</td><td class="text-right">'+formatNumber(item.SRMAX)+'</td><td class="text-right">'+formatNumber(item.LPH)+'</td><td>'+item.KLK+'</td><td>'+item.TYPE+'</td><td class="text-right">'+formatNumber(item.STOK)+'</td><td class="text-right">'+formatNumber(item.STOCKR)+'</td><td class="text-right">'+formatNumber(item.STOCKT)+'</td><td class="text-right">'+formatNumber(item.STOCKG)+'</td><td class="text-right">'+formatNumber(item.HB)+'</td><td class="text-right">'+formatNumber(item.HJ)+'</td><td>'+item.TDOD+'</td><td>'+item.SP_L+'</td><td class="text-right">'+formatNumber(item.DTB)+'</td><td class="text-right">'+formatNumber(item.LAMBAT)+'</td><td>'+item.BARCODE+'</td><td class="text-right">'+formatNumber(item.TARIK)+'</td><td class="text-right">'+formatNumber(item.MASA_EXP)+'</td><td>'+item.RETUR+'</td><td>'+item.KK+'</td><td>'+item.ON_DC+'</td><td class="text-right">'+formatNumber(item.DTR_DC)+'</td><td class="text-right">'+formatNumber(item.DTR2)+'</td><td class="text-right">'+formatNumber(item.DTR_MANUAL)+'</td>';
            } else if(tabType==='summary'){
                html += '<td>'+item.kd_brg+'</td><td>'+item.NA_BRG+'</td><td>'+item.tgl+'</td><td>'+item.no_bukti+'</td><td class="text-right">'+formatNumber(item.awal)+'</td><td class="text-right">'+formatNumber(item.masuk)+'</td><td class="text-right">'+formatNumber(item.keluar)+'</td><td class="text-right">'+formatNumber(item.LAIN)+'</td><td class="text-right">'+formatNumber(item.SALDO)+'</td>';
            }
            html += '</tr>';
        });

        html += '</tbody></table></div>';
    }

    $(targetDiv).html(html);

    if(data.length>0){
        $('#table-'+tabType).DataTable({
            pageLength:25,
            searching:true,
            ordering:true,
            responsive:true,
            // scrollX:true,
            dom:'Blfrtip',
            buttons:['copy','excel','csv','pdf','print'],
            language:{url:'//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'}
        });
    }
}

// -------------------------------
// Helper Format
// -------------------------------
function formatNumber(num){ return Number(num).toLocaleString('id-ID'); }
function formatDate(dateStr){ return dateStr ? new Date(dateStr).toLocaleDateString('id-ID') : ''; }

function resetFilter(tabType){
    switch(tabType){
        case 'detail':
            // reset input filter di tab detail jika ada
			$('#cbg_detail').val('');
			$('#per').val('');
			$('#supp').val('');
			$('#sub').val('');
			$('#item').val('');
			$('#bcd').val('');
			$('#nama').val('');
            break;
        case 'summary':
			$('#cbg_summary').val('');
			$('#kode').val('');
			$('#lok_gd').prop('checked',false);
			$('#lok_toko').prop('checked',false);
			$('#lok_retur').prop('checked',false);
            break;
    }

    // Kosongkan hasil tabel
    $('#' + tabType + '-result').html('<div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>Silakan Klik Filter untuk menampilkan data.</div>');

    // Jika tabel DataTable sebelumnya sudah diinisialisasi, destroy dulu
    var tableId = '#table-' + tabType;
    if($.fn.DataTable.isDataTable(tableId)){
        $(tableId).DataTable().destroy();
    }
}

function printReport(url) {
			var form = $('<form>', {
				'method': 'POST',
				'action': url,
				'target': '_blank'
			});

			form.append($('<input>', {
				'type': 'hidden',
				'name': '_token',
				'value': $('meta[name="csrf-token"]').attr('content')
			}));

			form.appendTo('body').submit().remove();
}

// Print function
function cetakKasir() {
			var cbg = $('#cbg_detail').val();

			if (!cbg) {
				alert('Silakan lengkapi Cabang terlebih dahulu');
				return;
			}

			var params = new URLSearchParams({
				report_type: 1,
				cbg: cbg,
			});

			var url = '{{ route('jasper-mutasi-report') }}?' + params.toString();
			printReport(url);
}


</script>
@endsection
