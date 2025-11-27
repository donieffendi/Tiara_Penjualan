@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
	<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0">Laporan Purchase Order </h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Laporan Purchase Order </li>
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
					<form method="POST" action="{{url('jasper-po-report')}}">
					@csrf
					<div class="form-group row">
						<!-- <div class="col-md-1">
							<label><strong>Gol :</strong></label>

							<select name="gol" id="gol" class="form-control gol">
								<option value="J" {{ session()->get('filter_gol')=='J' ? 'selected': ''}}>J</option>
								<option value="N" {{ session()->get('filter_gol')=='N' ? 'selected': ''}}>N</option>
								</select>
						</div> -->
						<div class="col-md-2">
							<label class="form-label">Suplier</label>
							<input type="text" class="form-control kodes" id="kodes" name="kodes" placeholder="Pilih Suplier" value="{{ session()->get('filter_kodes1') }}" readonly>
						</div>
						<div class="col-md-3">
							<label class="form-label">Nama</label>
							<input type="text" class="form-control NAMAS" id="NAMAS" name="NAMAS" placeholder="Nama" value="{{ session()->get('filter_namas1') }}" readonly>
						</div>
                        <div class="col-md-2">
							<label class="form-label">Barang</label>
							<input type="text" class="form-control brg1" id="brg1" name="brg1" placeholder="Pilih Barang# 1" value="{{ session()->get('filter_brg1') }}" readonly>
						</div>
						<div class="col-md-3">
							<label class="form-label">Nama</label>
							<input type="text" class="form-control nabrg1" id="nabrg1" name="nabrg1" placeholder="Nama" value="{{ session()->get('filter_nabrg1') }}" readonly>
						</div>
					</div>

					<!-- Filter Tanggal -->
					<div class="form-group row">
						<div class="col-md-3">
                            <label class="form-label">Tgl Transaksi</label>
							<input class="form-control date tglDr" id="tglDr" name="tglDr"
							type="text" autocomplete="off" value="{{ session()->get('filter_tglDari') }}">
						</div>
						<div class="col-md-3">
                            <label class="form-label">s.d.</label>
							<input class="form-control date tglSmp" id="tglSmp" name="tglSmp"
							type="text" autocomplete="off" value="{{ session()->get('filter_tglSampai') }}">
						</div>
                        <div class="col-md-3">
                            <label class="form-label">TKK</label>
							<input class="form-control date JTEMPO" id="JTEMPO" name="JTEMPO"
							type="date" autocomplete="off">
						</div>
					</div>

					{{-- <div class="form-group row">

						<div class="col-md-2">
							<label><strong>Cabang :</strong></label>
							<select name="cbg" id="cbg" class="form-control cbg" style="width: 200px">
								<option value="">--Pilih Cabang--</option>
								@foreach($cbg as $cbgD)
									<option value="{{$cbgD->CBG}}"  {{ (session()->get('filter_cbg') == $cbgD->CBG) ? 'selected' : '' }}>{{$cbgD->CBG}}</option>
								@endforeach
							</select>
						</div>

					</div> --}}

					<button class="btn btn-primary" type="submit" id="filter" class="filter" name="filter">Filter</button>
					<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rpo")}}'">Reset</button>
					<button class="btn btn-warning" type="submit" id="cetak" class="cetak" formtarget="_blank">Cetak</button>
					</form>
					<div style="margin-bottom: 15px;"></div>
					<!--
					<table class="table table-fixed table-striped table-border table-hover nowrap datatable">
						<thead class="table-dark">
							<tr>
								<th scope="col" style="text-align: center">#</th>
								<th scope="col" style="text-align: left">PO#</th>
								<th scope="col" style="text-align: left">Tgl</th>
								<th scope="col" style="text-align: left">Suplier#</th>
								<th scope="col" style="text-align: left">-</th>
								<th scope="col" style="text-align: left">Barang</th>
								<th scope="col" style="text-align: left">-</th>
								<th scope="col" style="text-align: right">Qty</th>
								<th scope="col" style="text-align: right">Harga</th>
								<th scope="col" style="text-align: right">Total</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
						<tfoot>
							<tr>
								<th></th>
								<th>Total :</th>
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
				<div class="report-content" col-md-12 style="max-width: 100%; overflow-x: scroll;">
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
								"NO_BUKTI" => array(
									"label" => "Bukti#",
								),
								"TGL" => array(
									"label" => "Tanggal",
								),
                                "JTEMPO" => array(
									"label" => "Jtempo",
								),
								"KODES" => array(
									"label" => "Suplier#",
								),
								"NAMAS" => array(
									"label" => "-",
								),
								"KD_BRG" => array(
									"label" => "Kode",
								),
								"NA_BRG" => array(
									"label" => "Uraian",
									"footerText" => "<b>Grand Total :</b>",
								),
								"SATUAN" => array(
									"label" => "Satuan",
									// "footerText" => "<b>Grand Total :</b>",
								),
								"QTY" => array(
									"label" => "Qty",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"footer" => "sum",
									"footerText" => "<b>@value</b>",
								),
								"HARGA" => array(
									"label" => "Harga",
									"type" => "number",
									"decimals" => 0,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"footer" => "sum",
									"footerText" => "<b>@value</b>",
								),
								"TOTAL" => array(
									"label" => "Total",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"footer" => "sum",
									"footerText" => "<b>@value</b>",
								),
								"KIRIM" => array(
									"label" => "Kirim",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"footer" => "sum",
									"footerText" => "<b>@value</b>",
								),
								"SISA" => array(
									"label" => "Sisa",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"footer" => "sum",
									"footerText" => "<b>@value</b>",
								),
								"NOTES" => array(
									"label" => "Notes",
								),
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
										"targets" => [6,7,8,9,10],
									),
								),
								"order" => [],
								"paging" => true,
								// "pageLength" => 12,
								"lengthMenu" => [[10, 25, 50,-1], [10,25,50, "All"]],
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
@endsection

@section('javascripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script>
	$(document).ready(function() {

		$('.date').datepicker({
			dateFormat: 'dd-mm-yy'
		});
		/*
		function fill_datatable( kodes = '' ,  gol='', tglDr = '', tglSmp = '' )
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
				'scrollX': true,
				'scrollY': '400px',
				"order": [[ 0, "asc" ]],
				ajax:
				{
					url: "{{ route('get-po-report') }}",
					data: {
						kodes: kodes,
						gol: gol,
						tglDr: tglDr,
						tglSmp: tglSmp,
					}
				},
				columns:
				[
					{data: 'DT_RowIndex', orderable: false, searchable: false },
					{data: 'NO_BUKTI', name: 'NO_BUKTI'},
					{data: 'TGL', name: 'TGL'},
					{data: 'KODES', name: 'KODES'},
					{data: 'NAMAS', name: 'NAMAS'},
					{data: 'KD_BHN', name: 'KD_BHN'},
					{data: 'NA_BHN', name: 'NA_BHN'},
					{
						data: 'QTY',
						name: 'QTY',
						render: $.fn.dataTable.render.number( ',', '.', 0, '' )
					},
					{
						data: 'HARGA',
						name: 'HARGA',
						render: $.fn.dataTable.render.number( ',', '.', 0, '' )
					},
					{
						data: 'TOTAL',
						name: 'TOTAL',
						render: $.fn.dataTable.render.number( ',', '.', 0, '' )
					}
				],

				columnDefs:
				[
					{
					"className": "dt-center",
					"targets": 0
					},
					{
					targets: 2,
					render: $.fn.dataTable.render.moment( 'DD-MM-YYYY' )
					},
					{
					"className": "dt-right",
					"targets": [7,8,9]
					},
				],

				footerCallback: function (row, data, start, end, display) {
						var api = this.api();

						// Remove the formatting to get integer data for summation
						var intVal = function (i) {
							return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
						};

						// Total over all pages
						totalQty = api
							.column(7)
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);

						// Total over this page
						pageQtyTotal = api
							.column(7, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						pageSubTotal = api
							.column(9, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);

						// Update footer
						$(api.column(7).footer()).html(pageQtyTotal.toLocaleString('en-US'));
						$(api.column(9).footer()).html(pageSubTotal.toLocaleString('en-US'));
					},

			});
		}

		$('#filter').click(function() {
			var kodes = $('#kodes').val();
			var gol = $('#gol').val();
			var tglDr = $('#tglDr').val();
			var tglSmp = $('#tglSmp').val();

			if (kodes != '' || (tglDr != '' && tglSmp != ''))
			{
				$('.datatable').DataTable().destroy();
				fill_datatable(kodes, gol, tglDr, tglSmp);
			}
		});

		$('#resetfilter').click(function() {
			var kodes = '';
			var gol = '';
			var tglDr = '';
			var tglSmp = '';

			$('.datatable').DataTable().destroy();
			fill_datatable(kodes, gol, tglDr, tglSmp);
		});
		*/
	});

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
		$("#kodes").val(KODES);
		$("#NAMAS").val(NAMAS);
		$("#browseSuplierModal").modal("hide");
	}

	$("#kodes").keypress(function(e){
		if(e.keyCode == 46){
			e.preventDefault();
			browseSuplier();
		}
	});

	//////////////////////////////////////////////////////////////////////

	var dTableBBarang;
	loadDataBBarang = function(){
		$.ajax(
		{
			type: 'GET',
			url: "{{url('vbrg/browse_koreksi')}}",
			data: {
				'GOL': $('#gol').val(),
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
		$("#brg1").val(KD_BRG);
		$("#nabrg1").val(NA_BRG);
		$("#browseBarangModal").modal("hide");
	}


	$("#brg1").keypress(function(e){
		if(e.keyCode == 46){
			e.preventDefault();
			browseBarang();
		}
	});

//////////////////////////////////////////////


</script>
@endsection
