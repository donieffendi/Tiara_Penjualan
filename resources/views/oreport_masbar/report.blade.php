	@extends('layouts.plain')

	@section('content')
	<div class="content-wrapper">
		<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0">Laporan Master Barang</h1>
			</div>
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item active">Laporan Master Barang </li>
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
					    <form method="POST" action="{{url('jasper-masbar-report')}}">
					    @csrf


						<div class="form-group row">
							<div class="col-md-2">
								<label><strong>Periode :</strong></label>
								<select name="perio" id="perio" class="form-control perio" style="width: 200px">
									<option value="">--Pilih Periode--</option>
									@foreach($per as $perD)
										<option value="{{$perD->PERIO}}" {{ session()->get('filter_per')== $perD->PERIO ? 'selected' : '' }}>{{$perD->PERIO}}</option>
									@endforeach
								</select>
							</div>
							
							<!--
							<select name="acno" id="acno" class="form-control acno" style="width: 200px">
								<option value="">--Pilih Bahan--</option>
								<option value="1000">Kas</option>
								<option value="1100">Bank</option>
							</select>
							-->

							<div class="col-md-2">
								<label><strong>Cabang :</strong></label>
								<select name="cbg" id="cbg" class="form-control cbg" style="width: 200px">
									<option value="">--Pilih Cabang--</option>
									@foreach($cbg as $cbgD)
										<option value="{{$cbgD->CBG}}"  {{ (session()->get('filter_cbg') == $cbgD->CBG) ? 'selected' : '' }}>{{$cbgD->CBG}}</option>
									@endforeach
								</select>
							</div>

						</div>

						<div class="form-group row">
							<div class="col-md-2">						
								<label class="form-label">Barang 1</label>
								<input type="text" class="form-control KD_BRG" id="KD_BRG" name="KD_BRG" placeholder="Pilih Barang" value="{{ session()->get('filter_kode1') }}" readonly>
							</div>  
							<div class="col-md-3">
								<label class="form-label">Nama</label>
								<input type="text" class="form-control NA_BRG" id="NA_BRG" name="NA_BRG" placeholder="Nama Barang" value="{{ session()->get('filter_nama1') }}" readonly>
							</div>
							<div class="col-md-1">
								<label class="form-label"> s.d </label>
							</div>
							<div class="col-md-2">						
								<label class="form-label">Barang 2</label>
								<input type="text" class="form-control KD_BRG2" id="KD_BRG2" name="KD_BRG2" placeholder="Pilih Barang" value="{{ session()->get('filter_kode2') }}" readonly>
							</div>  
							<div class="col-md-3">
								<label class="form-label">Nama</label>
								<input type="text" class="form-control NA_BRG2" id="NA_BRG2" name="NA_BRG2" placeholder="Nama Barang" value="{{ session()->get('filter_nama2') }}" readonly>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-2">						
								<label class="form-label">Suplier</label>
								<input type="text" class="form-control SUPP" id="SUPP" name="SUPP" placeholder="Pilih Suplier" value="{{ session()->get('filter_supp') }}" readonly>
							</div>  
							{{-- <div class="col-md-3">
								<label class="form-label">Nama</label>
								<input type="text" class="form-control NAMAS" id="NAMAS" name="NAMAS" placeholder="Nama" value="{{ session()->get('filter_namas1') }}" readonly>
							</div> --}}

							<div class="col-md-2">
								<label><strong>Golongan :</strong></label>
								<select name="GOL" id="GOL" class="form-control GOL" style="width: 200px">
									<option value="">--Pilih Golongan--</option>
									<option value="J">Barang</option>
									<option value="N">Non Barang</option>
								</select>
							</div>

							<div class="col-md-2">
								<label><strong>Kelompok :</strong></label>
								<select name="KELOMPOK" id="KELOMPOK" class="form-control KELOMPOK" style="width: 200px">
									<option value="">--Pilih Kelompok--</option>
									<option value="0">0</option>
									<option value="1">1</option>
								</select>
							</div>

							<div class="col-md-2">
								<label><strong>Tanda :</strong></label>
								<select name="TANDA" id="TANDA" class="form-control TANDA" style="width: 200px">
									<option value="">--Pilih Tanda--</option>
									<option value="B">BINTANG</option>
									<option value="T">TIDAK BINTANG</option>
								</select>
							</div>
						</div>
						
						<button class="btn btn-primary" type="submit" id="filter" class="filter" name="filter">Filter</button>
						<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rmasbar")}}'">Reset</button>
						<button class="btn btn-warning" type="submit" id="cetak" class="cetak" formtarget="_blank">Cetak</button>
						</form>
						<div style="margin-bottom: 15px;"></div>
						<!--
						<table class="table table-fixed table-striped table-border table-hover nowrap datatable">
							<thead class="table-dark">
								<tr>
									<th scope="col" style="text-align: center">#</th>
									<th scope="col" style="text-align: center">Kode</th>
									<th scope="col" style="text-align: center">-</th>
									<th scope="col" style="text-align: center">Awal</th>
									<th scope="col" style="text-align: center">Masuk</th>
									<th scope="col" style="text-align: center">Keluar</th>
									<th scope="col" style="text-align: center">Lain</th>
									<th scope="col" style="text-align: center">Akhir</th>
									<th scope="col" style="text-align: center">H-rata</th>
									<th scope="col" style="text-align: center">N-Awal</th>
									<th scope="col" style="text-align: center">N-Masuk</th>
									<th scope="col" style="text-align: center">N-Keluar</th>
									<th scope="col" style="text-align: center">N-Lain</th>
									<th scope="col" style="text-align: center">N-Akhir</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
							<tfoot>
								<tr>
									<th></th>
									<th>Total</th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tr>
							</tfoot>							
						</table> -->
						
                    <!-- PASTE DIBAWAH INI -->
                    <!-- DISINI BATAS AWAL KOOLREPORT-->
                    <div class="report-content" col-md-12>
                        <?php
                        use \koolreport\datagrid\DataTables;

                        if($hasil)
                        {
                            DataTables::create(array(
                                "dataSource" => $hasil,
                                "name" => "example",
                                "fastRender" => true,
                                "fixedHeader" => true,
                                'scrollX' => true,
                                "showFooter" => true,
                                "showFooter" => "bottom",
                                "columns" => array(
                                    "KD_BRG" => array(
                                        "label" => "Barang#",
                                    ),
                                    "NA_BRG" => array(
                                        "label" => "-",
                                        "footerText" => "<b>Grand Total :</b>",
                                    ),
									"SUPP" => array(
                                        "label" => "Suplier#",
                                    ),
                                    "KLK" => array(
                                        "label" => "KLK",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
                                    "PPN" => array(
                                        "label" => "PPN",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
									"GOL" => array(
                                        "label" => "Golongan",
                                    ),
									"KELOMPOK" => array(
                                        "label" => "Kelompok",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                    ),
									"TANDA" => array(
                                        "label" => "Tanda",
                                    ),
                                    // "KE" => array(
                                    //     "label" => "Keluar",
                                    //     "type" => "number",
                                    //     "decimals" => 2,
                                    //     "decimalPoint" => ".",
                                    //     "thousandSeparator" => ",",
                                    //     "footer" => "sum",
                                    //     "footerText" => "<b>@value</b>",
                                    // ),
                                    // "LN" => array(
                                    //     "label" => "Lain",
                                    //     "type" => "number",
                                    //     "decimals" => 2,
                                    //     "decimalPoint" => ".",
                                    //     "thousandSeparator" => ",",
                                    //     "footer" => "sum",
                                    //     "footerText" => "<b>@value</b>",
                                    // ),
                                    // "AK" => array(
                                    //     "label" => "Akhir",
                                    //     "type" => "number",
                                    //     "decimals" => 2,
                                    //     "decimalPoint" => ".",
                                    //     "thousandSeparator" => ",",
                                    //     "footer" => "sum",
                                    //     "footerText" => "<b>@value</b>",
                                    // ),
                                    // "HRT" => array(
                                    //     "label" => "H-Rata",
                                    //     "type" => "number",
                                    //     "decimals" => 2,
                                    //     "decimalPoint" => ".",
                                    //     "thousandSeparator" => ",",
                                    //     "footer" => "sum",
                                    //     "footerText" => "<b>@value</b>",
                                    // ),
                                    // "HRT_2" => array(
                                    //     "label" => "H-Rata 2",
                                    //     "type" => "number",
                                    //     "decimals" => 2,
                                    //     "decimalPoint" => ".",
                                    //     "thousandSeparator" => ",",
                                    //     "footer" => "sum",
                                    //     "footerText" => "<b>@value</b>",
                                    // ),
                                    // "NIW" => array(
                                    //     "label" => "N-Awal",
                                    //     "type" => "number",
                                    //     "decimals" => 2,
                                    //     "decimalPoint" => ".",
                                    //     "thousandSeparator" => ",",
                                    //     "footer" => "sum",
                                    //     "footerText" => "<b>@value</b>",
                                    // ),
                                    // "NIM" => array(
                                    //     "label" => "N-Masuk",
                                    //     "type" => "number",
                                    //     "decimals" => 2,
                                    //     "decimalPoint" => ".",
                                    //     "thousandSeparator" => ",",
                                    //     "footer" => "sum",
                                    //     "footerText" => "<b>@value</b>",
                                    // ),
                                    // "NIK" => array(
                                    //     "label" => "N-Keluar",
                                    //     "type" => "number",
                                    //     "decimals" => 2,
                                    //     "decimalPoint" => ".",
                                    //     "thousandSeparator" => ",",
                                    //     "footer" => "sum",
                                    //     "footerText" => "<b>@value</b>",
                                    // ),
                                    // "NIL" => array(
                                    //     "label" => "N-Lain",
                                    //     "type" => "number",
                                    //     "decimals" => 2,
                                    //     "decimalPoint" => ".",
                                    //     "thousandSeparator" => ",",
                                    //     "footer" => "sum",
                                    //     "footerText" => "<b>@value</b>",
                                    // ),
                                    // "NIR" => array(
                                    //     "label" => "N-Akhir",
                                    //     "type" => "number",
                                    //     "decimals" => 2,
                                    //     "decimalPoint" => ".",
                                    //     "thousandSeparator" => ",",
                                    //     "footer" => "sum",
                                    //     "footerText" => "<b>@value</b>",
                                    // ),
                                ),
                                "cssClass" => array(
                                    "table" => "table table-hover table-striped table-bordered compact",
                                    "th" => "label-title",
                                    "td" => "detail",
                                    "tf" => "footerCss"
                                ),
                                "options" => array(
                                    "columnDefs"=>array(
                                        array(
                                            "className" => "dt-right", 
                                            "targets" => [3,4],
                                        ),
                                    ),
                                    "order" => [],
                                    "paging" => true,
                                    // "pageLength" => 12,
                                    "searching" => true,
                                    "colReorder" => true,
                                    "select" => true,
                                    "dom" => 'Blfrtip', // B e dilangi
                                    // "dom" => '<"row"<col-md-6"B><"col-md-6"f>> <"row"<"col-md-12"t>><"row"<"col-md-12">>',
                                    "buttons" => array(
                                        array(
                                            "extend" => 'collection',
                                            "text" => 'Export',
                                            "buttons" => [
                                                'copy',
                                                'excel',
                                                'csv',
                                                'pdf',
                                                'print'
                                            ],
                                        ),
                                    ),
                                ),
                            ));
                        }
                        ?>
                    </div>
                    <!-- DISINI BATAS AKHIR KOOLREPORT-->

					</div>
				</div>
				</div>
			</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="browseBarangModal" tabindex="-1" role="dialog" aria-labelledby="browseBarangModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="browseBarangModalLabel">Cari Barang</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-stripped table-bordered" id="table-bbarang">
					<thead>
						<tr>
							<th>Barang#</th>
							<th>Nama</th>
							<th>Satuan</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="browseBarangModal2" tabindex="-1" role="dialog" aria-labelledby="browseBarangModalLabel2" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="browseBarangModalLabel2">Cari Barang</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-stripped table-bordered" id="table-bbarang2">
					<thead>
						<tr>
							<th>Barang#</th>
							<th>Nama</th>
							<th>Satuan</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="browseSuplierModal" tabindex="-1" role="dialog" aria-labelledby="browseSuplierModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="browseSuplierModalLabel">Cari Suplier</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-stripped table-bordered" id="table-bsuplier">
					<thead>
						<tr>
							<th>Suplier</th>
							<th>Nama</th>
							<th>Alamat</th>
							<th>Kota</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
			</div>
		</div>
	</div>
	@endsection

	@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	<script>

	var dTableBBarang;
	loadDataBBarang = function(){
		$.ajax(
		{
			type: 'GET',    
			url: "{{url('vbrg/browse_koreksi')}}",
			data: {
				// 'GOL': $('#gol').val(),
			},
			success: function( response )
			{
				resp = response;
				if(dTableBBarang){
					dTableBBarang.clear();
				}
				for(i=0; i<resp.length; i++){
					
				dTableBBarang.row.add([
						'<a href="javascript:void(0);" onclick="chooseBarang(\''+resp[i].KD_BRG+'\',  \''+resp[i].NA_BRG+'\',   \''+resp[i].SATUAN+'\')">'+resp[i].KD_BRG+'</a>',
						resp[i].NA_BRG,
						resp[i].SATUAN,
					]);
					
				}
				dTableBBarang.draw();
			}
		});
	}
	
	dTableBBarang = $("#table-bbarang").DataTable({
		
	});
	
	browseBarang = function(){
		loadDataBBarang();
		$("#browseBarangModal").modal("show");
	}
	
	chooseBarang = function(KD_BRG,NA_BRG){
		$("#KD_BRG").val(KD_BRG);
		$("#NA_BRG").val(NA_BRG);			
		$("#browseBarangModal").modal("hide");
	}
	
	
	$("#KD_BRG").keypress(function(e){
		if(e.keyCode == 46){
			e.preventDefault();
			browseBarang();
		}
	});
	
	
	//////////////////////////////////////////////

	var dTableBBarang2;
	loadDataBBarang2 = function(){
		$.ajax(
		{
			type: 'GET',    
			url: "{{url('vbrg/browse_koreksi')}}",
			data: {
				// 'GOL': $('#gol').val(),
			},
			success: function( response )
			{
				resp = response;
				if(dTableBBarang2){
					dTableBBarang2.clear();
				}
				for(i=0; i<resp.length; i++){
					
				dTableBBarang2.row.add([
						'<a href="javascript:void(0);" onclick="chooseBarang2(\''+resp[i].KD_BRG+'\',  \''+resp[i].NA_BRG+'\',   \''+resp[i].SATUAN+'\')">'+resp[i].KD_BRG+'</a>',
						resp[i].NA_BRG,
						resp[i].SATUAN,
					]);
					
				}
				dTableBBarang2.draw();
			}
		});
	}
	
	dTableBBarang2 = $("#table-bbarang2").DataTable({
		
	});
	
	browseBarang2 = function(){
		loadDataBBarang2();
		$("#browseBarangModal2").modal("show");
	}
	
	chooseBarang2 = function(KD_BRG,NA_BRG){
		$("#KD_BRG2").val(KD_BRG);
		$("#NA_BRG2").val(NA_BRG);			
		$("#browseBarangModal2").modal("hide");
	}
	
	
	$("#KD_BRG2").keypress(function(e){
		if(e.keyCode == 46){
			e.preventDefault();
			browseBarang2();
		}
	});
	
	
	//////////////////////////////////////////////

	var dTableBSuplier;
	loadDataBSuplier = function(){
	
		$.ajax(
		{
			type: 'GET', 		
			url: "{{url('sup/browse')}}",
			data: {
				'GOL': $('#gol').val(),
			},
			success: function( response )
			{
				resp = response;
				if(dTableBSuplier){
					dTableBSuplier.clear();
				}
				for(i=0; i<resp.length; i++){
					
					dTableBSuplier.row.add([
						'<a href="javascript:void(0);" onclick="chooseSuplier(\''+resp[i].KODES+'\',  \''+resp[i].NAMAS+'\', \''+resp[i].ALAMAT+'\',  \''+resp[i].KOTA+'\')">'+resp[i].KODES+'</a>',
						resp[i].NAMAS,
						resp[i].ALAMAT,
						resp[i].KOTA,
					]);
				}
				dTableBSuplier.draw();
			}
		});
	}
	
	dTableBSuplier = $("#table-bsuplier").DataTable({
		
	});
	
	browseSuplier = function(){
		loadDataBSuplier();
		$("#browseSuplierModal").modal("show");
	}
	
	chooseSuplier = function(KODES,NAMAS, ALAMAT, KOTA){
		$("#SUPP").val(KODES);
		$("#NAMAS").val(NAMAS);	
		$("#browseSuplierModal").modal("hide");
	}
	
	$("#SUPP").keypress(function(e){
		if(e.keyCode == 46){
			e.preventDefault();
			browseSuplier();
		}
	});

		/*
		$(document).ready(function() {
		fill_datatable('');
			
		function fill_datatable(per)	
		{
				var dataTable = $('.datatable').DataTable({
					dom: '<"row"<"col-4"B>>fltip',
					lengthMenu: [
						[ 10, 25, 50, -1 ],
						[ '10 rows', '25 rows', '50 rows', 'Show all' ]
					],
					processing: true,
					serverSide: true,
					autoWidth: true,
					scrollX: true,
					//'scrollY': '400px',
					"order": [[ 0, "asc" ]],
					ajax: 
					{
						url: "{{ route('get-brg-report') }}",
						//data: {
						//	acno: acno
						//}
						data: {
							'perio': per,
						},
					},
					columns: 
					[
						{data: 'DT_RowIndex', orderable: false, searchable: false },
						{data: 'KD_BRG', name: 'KD_BRG'},
						{data: 'NA_BRG', name: 'NA_BRG'},
                        {
					      data: 'AW', 
					      name: 'AW',
					      render: $.fn.dataTable.render.number( ',', '.', 0, '' )
				        },
						{
					      data: 'MA', 
					      name: 'MA',
					      render: $.fn.dataTable.render.number( ',', '.', 0, '' )
				        },						
						{
					      data: 'KE', 
					      name: 'KE',
					      render: $.fn.dataTable.render.number( ',', '.', 0, '' )
				        },
						{
					      data: 'LN', 
					      name: 'LN',
					      render: $.fn.dataTable.render.number( ',', '.', 0, '' )
				        },
						{
					      data: 'AK', 
					      name: 'AK',
					      render: $.fn.dataTable.render.number( ',', '.', 0, '' )
				        },						
						{
					      data: 'HRT', 
					      name: 'HRT',
					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				        },
						{
					      data: 'NIW', 
					      name: 'NIW',
					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				        },
						{
					      data: 'NIM', 
					      name: 'NIM',
					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				        },						
						{
					      data: 'NIK', 
					      name: 'NIK',
					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				        },
						{
					      data: 'NIL', 
					      name: 'NIL',
					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				        },
						{
					      data: 'NIR', 
					      name: 'NIR',
					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				        }
					],
					
					
				columnDefs: [
                  {
                    "className": "dt-center", 
                    "targets": 0
                  },

                  {
                    "className": "dt-right", 
                    "targets": [3,4,5,6,7,8,9,10,11,12,13]
                  }
               
                 ],					
					footerCallback: function (row, data, start, end, display) {
						var api = this.api();
			 
						// Remove the formatting to get integer data for summation
						var intVal = function (i) {
							return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
						};
			 
						// Total over this page
						pageAwalTotal = api
							.column(3, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						pageMasukTotal = api
							.column(4, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						pageKeluarTotal = api
							.column(5, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						pageLainTotal = api
							.column(6, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						pageAkhirTotal = api
							.column(7, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);	
						pageHRataTotal = api
							.column(8, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						pageNAwalTotal = api
							.column(9, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						pageNMasukTotal = api
							.column(10, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						pageNKeluarTotal = api
							.column(11, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						pageNLainTotal = api
							.column(12, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						pageNAkhirTotal = api
							.column(13, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);	
			 
						// Update footer
						$(api.column(3).footer()).html(pageAwalTotal.toLocaleString('en-US'));
						$(api.column(4).footer()).html(pageMasukTotal.toLocaleString('en-US'));
						$(api.column(5).footer()).html(pageKeluarTotal.toLocaleString('en-US'));
						$(api.column(6).footer()).html(pageLainTotal.toLocaleString('en-US'));
						$(api.column(7).footer()).html(pageAkhirTotal.toLocaleString('en-US'));
						$(api.column(8).footer()).html(pageHRataTotal.toLocaleString('en-US'));
						$(api.column(9).footer()).html(pageNAwalTotal.toLocaleString('en-US'));
						$(api.column(10).footer()).html(pageNMasukTotal.toLocaleString('en-US'));
						$(api.column(11).footer()).html(pageNKeluarTotal.toLocaleString('en-US'));
						$(api.column(12).footer()).html(pageNLainTotal.toLocaleString('en-US'));
						$(api.column(13).footer()).html(pageNLainTotal.toLocaleString('en-US'));
						
					},
					
				});
			}
			
			$('#filter').click(function() {
				//var acno = $('#acno').val();
				//if (acno != '')
				//{
				$('.datatable').DataTable().destroy();
				var periode = $('#perio').val();
				fill_datatable(periode);
				//}
			});

			$('#resetfilter').click(function() {
				var periode = '';

				$('.datatable').DataTable().destroy();
				fill_datatable(periode);
			});

		});
		*/
	</script>
	@endsection

