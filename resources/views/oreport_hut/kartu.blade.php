@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
	<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0">Kartu Hutang</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Kartu Hutang</li>
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
					<form method="POST" action="{{url('jasper-hut-kartu')}}">
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
							<label class="form-label">Suplier</label>
							<input type="text" class="form-control kodes" id="kodes" name="kodes" placeholder="Pilih Suplier" value="{{ session()->get('filter_kodes1') }}" readonly>
						</div>  
						<div class="col-md-3">
							<label class="form-label">Nama</label>
							<input type="text" class="form-control NAMAS" id="NAMAS" name="NAMAS" placeholder="Nama" value="{{ session()->get('filter_namas1') }}" readonly>
						</div>
					</div>

					

					<div class="form-group row">
						
						<div class="col-md-2">
							<label><strong>Cabang :</strong></label>
							<select name="cbg" id="cbg" class="form-control cbg" style="width: 300px">
								<option value="">--Pilih Cabang--</option>
								@foreach($cbg as $cbgD)
									<option value="{{$cbgD->EXT}}" {{ (session()->get('filter_cbg') == $cbgD->EXT) ? 'selected' : '' }}>{{$cbgD->EXT}}</option>
								@endforeach
							</select>
						</div>   
					</div>

					<div class="form-group row" hidden>
						<div class="col-md-2">
							<input class="form-control date TGL1" id="TGL1" name="TGL1"
							type="text" autocomplete="off" value="{{ session()->get('filter_tgl1') }}"> 
						</div>
					</div>
					<button class="btn btn-primary" type="submit" id="filter" class="filter" name="filter">Filter</button>
					<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rkartuh")}}'">Reset</button>
					<button class="btn btn-warning" type="submit" id="cetak" class="cetak" formtarget="_blank">Cetak</button>
					</form>
					<div style="margin-bottom: 15px;"></div>

				<!-- PASTE DIBAWAH INI -->
				<!-- DISINI BATAS AWAL KOOLREPORT-->
				<!-- <div class="report-content" col-md-12> -->
					<div class="report-content" col-md-12 style="max-width: 100%;  overflow-y: auto; overflow-x: auto;  max-height: 500px;">

					<?php
					use \koolreport\datagrid\DataTables;
					use \koolreport\widgets\koolphp\Table;

					if($hasil)
					{
						Table::create(array(
							"dataSource"=>$hasil,
							"grouping"=>array(
								"year"=>array(
									"calculate"=>array(
										"{sumAmount}"=>array("sum","TOTAL")
									),
									"top"=>"<b >Year {year}</b>",
									"bottom"=>"<td></td>
											<td></td>
											<td></td>
											<td><b>Total of year {year}</b></td>
											<td style='text-align: right' ><b>{sumAmount}</b></td>",
								),
								"NAMAS"=>array(
									"calculate"=>array(
										"{sumAmount}"=>array("sum","TOTAL")
									),
									"top"=>"<b> {NAMAS}</b>",
									"bottom"=>"<td></td>
											<td></td>
											<td></td>
											<td><b>Total of {NAMAS}</b></td>
											<td style='text-align: right' ><b>{sumAmount}</b></td>",
								),
							),
							
							"sorting"=>array(
								"urut"=>"asc"
							),
							"showFooter"=>true,
							"columns"=>array(
								"NO_BUKTI" => array(
									"label" => "Bukti#",
								),
								"TGL" => array(
									"label" => "Tanggal",
								),
								"KODES" => array(
									"label" => "Suplier#",
								),
								"NAMAS"=>array(
									"label"=>"-",
									"footerText"=>"<b>Grand Totals</b>"
								),
								"MASUK"=>array(
									"label"=>"Masuk",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"prefix"=>"Rp. ",
									"footer"=>"sum",
									"footerText"=>"<b>@value</b>",
									"cssStyle"=>"text-align: right;" // Menjadikan total rata kanan
								),
								"KELUAR"=>array(
									"label"=>"Keluar",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"prefix"=>"Rp. ",
									"footer"=>"sum",
									"footerText"=>"<b>@value</b>",
									"cssStyle"=>"text-align: right;" // Menjadikan total rata kanan
								),
								"LAIN"=>array(
									"label"=>"Lain",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"prefix"=>"Rp. ",
									"footer"=>"sum",
									"footerText"=>"<b>@value</b>",
									"cssStyle"=>"text-align: right;" // Menjadikan total rata kanan
								),
								"SALDO"=>array(
									"label"=>"Total",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"prefix"=>"Rp. ",
									"footer"=>"sum",
									"footerText"=>"<b>@value</b>",
									"cssStyle"=>"text-align: right;" // Menjadikan total rata kanan
								)
							),
							"cssClass"=>array(
								"table"=>"table-bordered",
								"tf"=>"darker",
								"th"=>"cssHeader",
							),
							"options" => array(
								"columnDefs"=>array(
									array(
										"className" => "dt-right", 
										"targets" => [4],
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
<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>
<style>
	.cssHeader
	{
		background-color:#23ff00;
	}
	.cssItem
	{
		background-color:#fdffe8;
	}
	tr.row-group
	{
		background-color:#b0b0b0;
	}
</style>
<script>
	$(document).ready(function() {
		$('.date').datepicker({  
			dateFormat: 'dd-mm-yy'
		}); 
	});

	var dTableBSuplier;
	loadDataBSuplier = function(){
	
		$.ajax(
		{
			type: 'GET', 		
			url: "{{url('sup/browse')}}",
			// data: {
			// 	'GOL': $('#gol').val(),
			// },
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


</script>
@endsection