@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
	<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0">Laporan Suplier</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Laporan Suplier</li>
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
					<form method="POST" action="{{url('jasper-sup-report')}}">
					@csrf
					<div class="form-group row">
						<!-- <div class="col-md-1">
							<label><strong>Gol :</strong></label>
							
							<select name="gol" id="gol" class="form-control gol">
								<option value="Y" {{ session()->get('filter_gol')=='Y' ? 'selected': ''}}>Y</option>
								<option value="Z" {{ session()->get('filter_gol')=='Z' ? 'selected': ''}}>Z</option>
							</select>
						</div> -->
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

					</div>
					<div class="form-group row">
						<div class="col-md-2">						
							<label class="form-label">Suplier</label>
							<input type="text" class="form-control KODES" id="KODES" name="KODES" placeholder="Pilih Suplier" value="{{ session()->get('filter_kodes1') }}" readonly>
						</div>  
						<div class="col-md-3">
							<label class="form-label">Nama</label>
							<input type="text" class="form-control NAMAS" id="NAMAS" name="NAMAS" placeholder="Nama" value="{{ session()->get('filter_namas1') }}" readonly>
						</div>
					</div>

					
					<!-- <div class="form-group row">
						
						<div class="col-md-2">
							<label><strong>Cabang :</strong></label>
							<select name="cbg" id="cbg" class="form-control cbg" style="width: 200px">
								<option value="">--Pilih Cabang--</option>
								@foreach($cbg as $cbgD)
									<option value="{{$cbgD->CBG}}"  {{ (session()->get('filter_cbg') == $cbgD->CBG) ? 'selected' : '' }}>{{$cbgD->CBG}}</option>
								@endforeach
							</select>
						</div>
						
					</div> -->
					
					<button class="btn btn-primary" type="submit" id="filter" class="filter" name="filter">Filter</button>
					<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rsup")}}'">Reset</button>
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
								<th scope="col" style="text-align: center">Beli</th>
								<th scope="col" style="text-align: center">Bayar</th>
								<th scope="col" style="text-align: center">Lain</th>
								<th scope="col" style="text-align: center">Akhir</th>
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
								"KODES" => array(
									"label" => "Suplier#",
								),
								"NAMAS" => array(
									"label" => "-",
									"footerText" => "<b>Grand Total :</b>",
								),
								"AW" => array(
									"label" => "Awal",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"footer" => "sum",
									"footerText" => "<b>@value</b>",
								),
								"MA" => array(
									"label" => "Beli",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"footer" => "sum",
									"footerText" => "<b>@value</b>",
								),
								"KE" => array(
									"label" => "Bayar",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"footer" => "sum",
									"footerText" => "<b>@value</b>",
								),
								"LN" => array(
									"label" => "Lain",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"footer" => "sum",
									"footerText" => "<b>@value</b>",
								),
								"AK" => array(
									"label" => "Akhir",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"footer" => "sum",
									"footerText" => "<b>@value</b>",
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
										"targets" => [2,3,4,5,6],
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
		$("#KODES").val(KODES);
		$("#NAMAS").val(NAMAS);	
		$("#browseSuplierModal").modal("hide");
	}
	
	$("#KODES").keypress(function(e){
		if(e.keyCode == 46){
			e.preventDefault();
			browseSuplier();
		}
	});
	/*
	$(document).ready(function() {
	fill_datatable();
		
	function fill_datatable(per='')	
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
				//scrollX: true,
				//'scrollY': '400px',
				"order": [[ 0, "asc" ]],
				ajax: 
				{
					url: "{{ route('get-sup-report') }}",
					data: {
						'perio': per,
					},
				},
				columns: 
				[
					{data: 'DT_RowIndex', orderable: false, searchable: false },
					{data: 'KODES', name: 'KODES'},
					{data: 'NAMAS', name: 'NAMAS'},
					{
						data: 'AW', 
						name: 'AW',
						render: $.fn.dataTable.render.number( ',', '.', 2, '' )
					},
					{
						data: 'MA', 
						name: 'MA',
						render: $.fn.dataTable.render.number( ',', '.', 2, '' )
					},						
					{
						data: 'KE', 
						name: 'KE',
						render: $.fn.dataTable.render.number( ',', '.', 2, '' )
					},
					{
						data: 'LN', 
						name: 'LN',
						render: $.fn.dataTable.render.number( ',', '.', 2, '' )
					},
					{
						data: 'AK', 
						name: 'AK',
						render: $.fn.dataTable.render.number( ',', '.', 2, '' )
					}
				],
				
			///////////////////////////////////////////////////
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
					pageJualTotal = api
						.column(4, { page: 'current' })
						.data()
						.reduce(function (a, b) {
							return intVal(a) + intVal(b);
						}, 0);
					pageBayarTotal = api
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
					
			
					// Update footer
					$(api.column(3).footer()).html(pageAwalTotal.toLocaleString('en-US'));
					$(api.column(4).footer()).html(pageJualTotal.toLocaleString('en-US'));
					$(api.column(5).footer()).html(pageBayarTotal.toLocaleString('en-US'));
					$(api.column(6).footer()).html(pageLainTotal.toLocaleString('en-US'));
					$(api.column(7).footer()).html(pageAkhirTotal.toLocaleString('en-US'));
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