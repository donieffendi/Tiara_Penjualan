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
						<h1 class="m-0">Report Stock Opname Barang</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Report Stock Opname Barang</li>
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

								<form method="POST" action="{{ url('jasper-stokakhir-report') }}" id="reportForm">
									@csrf
									<!-- Nav tabs -->
									<ul class="nav nav-tabs" id="reportTabs" role="tablist">
										<li class="nav-item" role="presentation">
											<a class="nav-link active" id="periode-tab" data-toggle="tab" href="#periode" role="tab" aria-controls="periode" aria-selected="true">
												<i class="fas fa-cubes mr-1"></i>Periode
											</a>
										</li>
										<li class="nav-item" role="presentation">
											<a class="nav-link" id="stok-tab" data-toggle="tab" href="#stok" role="tab" aria-controls="stok" aria-selected="false">
												<i class="fas fa-cube mr-1"></i>Stok
											</a>
										</li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content" id="reportTabContent">
										<!-- Detail Transaksi Tab -->
										<div class="tab-pane fade show active" id="periode" role="tabpanel" aria-labelledby="periode-tab">
											<div class="pt-3">
												<div class="form-group">
													<!-- Search Filter Row -->
													<div class="row align-items-end mb-3">
														<div class="col-2">
															<label for="cbg_periode">Cabang</label>
															<select name="cbg_periode" id="cbg_periode" class="form-control" required>
																<option value="">Pilih Cabang</option>
																@foreach ($cbg as $cabang)
																	<option value="{{ $cabang->KODE }}">{{ $cabang->KODE }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-2">
															<label for="per_periode">Periode</label>
															<select name="per_periode" id="per_periode" class="form-control" required>
																<option value="">Pilih Periode</option>
																@foreach ($per as $periode)
																	<option value="{{ $periode->PERIO }}">{{ $periode->PERIO }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-6">
															<button class="btn btn-primary mr-1" type="button" id="btnFilterPeriode" onclick="filterStokakhir('periode')">
																<i class="fas fa-search mr-1"></i>Filter
															</button>
															<button class="btn btn-danger mr-1" type="button" onclick="resetFilter('periode')">
																<i class="fas fa-redo mr-1"></i>Reset
															</button>
															<button class="btn btn-warning mr-1" type="button"
																onclick="cetakStokakhir('periode')">
																<i class="fas fa-print mr-1"></i>Cetak
															</button>
														</div>
													</div>

													<!-- Data Periode -->
													<div class="col-md-12 report-content" id="periode-result">
														@if (!empty($hasilStokakhir))
															<div class="table-responsive">
																<table id="tabelPeriode" class="table table-striped table-bordered nowrap" style="width:100%">
																	<thead>
																		<tr>
																			<th>Cabang</th>
																			<th>Sub</th>
																			<th>Kelompok Barang</th>
																			<th>Qty</th>
																			<th>HB</th>
																			<th>HB Periode</th>
																			<th>Total HB</th>
																			<th>Total Toko</th>
																			<th>Total Gudang</th>
																			<th>Total</th>
																		</tr>
																	</thead>
																	<tbody>
																		@foreach ($hasilStokakhir as $item)
																			<tr>
																				<td>{{ $item->CBG ?? '' }}</td>
																				<td>{{ $item->SUB ?? '' }}</td>
																				<td>{{ $item->KELOMPOK ?? '' }}</td>
																				<td class="text-right">{{ number_format($item->qty ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->hb ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->hbb ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->jumhb ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->TK ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->GD ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->TOTALX ?? 0, 0, ',', '.') }}</td>
																			</tr>
																		@endforeach
																	</tbody>
																</table>
															</div>
														@else
															<div class="alert alert-info">
																<i class="fas fa-info-circle mr-2"></i>
																Silakan Klik Filter untuk menampilkan Data.
															</div>
														@endif
													</div>
												</div>
											</div>
										</div>

										<!-- Stok Tab -->
										<div class="tab-pane fade" id="stok" role="tabpanel" aria-labelledby="stok-tab">
											<div class="pt-3">
												<div class="form-group">
													<div class="row align-items-end mb-3">
														<div class="col-2">
															<label for="cbg_stok">Cabang</label>
															<select name="cbg_stok" id="cbg_stok" class="form-control" required>
																<option value="">Pilih Cabang</option>
																@foreach ($cbg as $cabang)
																	<option value="{{ $cabang->KODE }}">{{ $cabang->KODE }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-2">
															<label for="per_stok">Periode</label>
															<select name="per_stok" id="per_stok" class="form-control" required>
																<option value="">Pilih Periode</option>
																@foreach ($per as $periode)
																	<option value="{{ $periode->PERIO }}">{{ $periode->PERIO }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-6">
															<button class="btn btn-primary mr-1" type="button" id="btnFilterNon" onclick="filterStokakhir('stok')">
																<i class="fas fa-search mr-1"></i>Filter
															</button>
															<button class="btn btn-danger mr-1" type="button" onclick="resetFilter('stok')">
																<i class="fas fa-redo mr-1"></i>Reset
															</button>
															<button class="btn btn-warning mr-1" type="button"
																onclick="cetakStokakhir('stok')">
																<i class="fas fa-print mr-1"></i>Cetak
															</button>
														</div>
													</div>

													<div class="col-md-12 report-content" id="stok-result">
														@if (!empty($hasilStokakhir))
															<div class="table-responsive">
																<table id="tabelStok" class="table table-striped table-bordered nowrap" style="width:100%">
																	<thead>
																		<tr>
																			<th>CBG</th>
																			<th>Sub</th>
																			<th>Kelompok Barang</th>
																			<th>Saldo Awal</th>
																			<th>Masuk</th>
																			<th>Keluar</th>
																			<th>Lain</th>
																			<th>Akhir</th>
																		</tr>
																	</thead>
																	<tbody>
																		@foreach ($hasilStokakhir as $item)
																			<tr>
																				<td>{{ $item->CBG ?? '' }}</td>
																				<td>{{ $item->SUB ?? '' }}</td>
																				<td>{{ $item->KELOMPOK ?? '' }}</td>
																				<td class="text-right">{{ number_format($item->aw ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->ma ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->ke ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->ln ?? 0, 0, ',', '.') }}</td>
																				<td class="text-right">{{ number_format($item->ak ?? 0, 0, ',', '.') }}</td>
																			</tr>
																		@endforeach
																	</tbody>
																</table>
															</div>
														@else
															<div class="alert alert-info">
																<i class="fas fa-info-circle mr-2"></i>
																Silakan Klik Filter untuk menampilkan Data.
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
        localStorage.setItem('activeKasirBantuTab', $(e.target).attr('href'));
    });

    // Restore tab aktif
    var activeTab = localStorage.getItem('activeKasirBantuTab');
    if(activeTab){
        $('#reportTabs a[href="'+activeTab+'"]').tab('show');
    }

    // Auto format periode input
    $('#periode_detail, #periode_summary, #periode_kasir').on('input', function(){
        var value = this.value.replace(/\D/g,'');
        if(value.length>=2) this.value = value.substring(0,2)+'-'+value.substring(2,6);
    });

    // Inisialisasi DataTable awal (Detail)
    @if(!empty($hasilStokakhir))
        $('#tabelKodetg').DataTable({
            pageLength: 25,
            searching: true,
            ordering: true,
            responsive: true,
            columnDefs: [{className:'dt-right', targets:[4,5]}],
            language:{url:'//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'}
        });
    @endif

});

// -------------------------------
// Fungsi Filter per Tab
// -------------------------------
function filterStokakhir(tabType){
    var cbg='', per='', btnId='';
    switch(tabType){
        case 'periode':
            cbg = $('#cbg_periode').val();
			per = $('#per_periode').val();
			btnId = '#btnFilterPeriode';
			if(!cbg){ alert('Pilih cabang terlebih dahulu'); return; }
            break;
        case 'stok':
			cbg = $('#cbg_stok').val();
			per = $('#per_stok').val();
			btnId = '#btnFilterStok';
			if(!cbg){ alert('Pilih cabang terlebih dahulu'); return; }
            break;
    }

    $(btnId).html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...').prop('disabled',true);

    $.ajax({
		url: '{{ route("get-stokakhir-report-ajax") }}',
		method: 'GET',
		data: { tab: tabType, cbg: cbg, per: per },
		success: function(res){
			if(res.success){
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
			$(btnId).html('<i class="fas fa-search mr-1"></i>Filter').prop('disabled', false);
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

        if(tabType==='periode'){
            html += '<th>Cabang</th><th>Sub</th><th>Kelompok Barang</th><th>Qty</th><th>HB</th><th>HB Periode</th><th>Total HB</th><th>Total Toko</th><th>Total Gudang</th><th>Total</th>';
        } else if(tabType==='stok'){
            html += '<th>Cabang</th><th>Sub</th><th>Kelompok Barang</th><th>Saldo Awal</th><th>Masuk</th><th>Keluar</th><th>Lain</th><th>Akhir</th>';
        }
        html += '</tr></thead><tbody>';

        $.each(data,function(i,item){
            html += '<tr>';
            if(tabType==='periode'){
                html += '<td>'+item.CBG+'</td><td>'+item.SUB+'</td><td>'+item.KELOMPOK+'</td><td class="text-right">'+formatNumber(item.qty)+'</td><td class="text-right">'+formatNumber(item.hb)+'</td><td class="text-right">'+formatNumber(item.hbb)+'</td><td class="text-right">'+formatNumber(item.jumhb)+'</td><td class="text-right">'+formatNumber(item.TK)+'</td><td class="text-right">'+formatNumber(item.GD)+'</td><td class="text-right">'+formatNumber(item.TOTALX)+'</td>';
            } else if(tabType==='stok'){
                html += '<td>'+item.CBG+'</td><td>'+item.SUB+'</td><td>'+item.KELOMPOK+'</td><td class="text-right">'+formatNumber(item.aw)+'</td><td class="text-right">'+formatNumber(item.ma)+'</td><td class="text-right">'+formatNumber(item.ke)+'</td><td class="text-right">'+formatNumber(item.ln)+'</td><td class="text-right">'+formatNumber(item.ak)+'</td>';
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
        case 'periode':
			$('#cbg_periode').val('');
			$('#per_periode').val('');
            break;
        case 'stok':
			$('#cbg_stok').val('');
			$('#per_stok').val('');
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

function cetakStokakhir(tab){
    let cbg='', per='';

    switch(tab){
        case 'periode':
            cbg = $('#cbg_periode').val();
            per = $('#per_periode').val();
            break;
        case 'stok':
            cbg = $('#cbg_stok').val();
            per = $('#per_stok').val();
            break;
    }

    if(!cbg || !per){
        alert('Cabang dan Periode wajib diisi');
        return;
    }

    // POST ke Jasper (new tab)
    let form = $('<form>', {
        method: 'POST',
        action: '{{ route("jasper-stokakhir-report") }}',
        target: '_blank'
    });

    form.append('@csrf');
    form.append($('<input>', {name:'tab', value:tab, type:'hidden'}));
    form.append($('<input>', {name:'cbg', value:cbg, type:'hidden'}));
    form.append($('<input>', {name:'per', value:per, type:'hidden'}));

    $('body').append(form);
    form.submit();
    form.remove();
}


</script>
@endsection
