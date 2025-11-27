@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
	<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0">Kartu Poin Customer</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Kartu Poin Customer</li>
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
					<form method="POST" action="{{url('jasper-poin-kartu')}}">
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
							<label class="form-label">Customer</label>
							<input type="text" class="form-control kodec" id="kodec" name="kodec" placeholder="Pilih Customer" value="{{ session()->get('filter_kodec1') }}" readonly>
						</div>  
						<div class="col-md-3">
							<label class="form-label">Nama</label>
							<input type="text" class="form-control NAMAC" id="NAMAC" name="NAMAC" placeholder="Nama" value="{{ session()->get('filter_namac1') }}" readonly>
						</div>
					</div>


                    <!-- Filter Tanggal -->
                    <div class="form-group row">
                        <div class="col-md-3">
                            <input class="form-control date tglDr" id="tglDr" name="tglDr"
                            type="text" autocomplete="off" value="{{ session()->get('filter_tglDr')}}"> 
                        </div>
                        <div>s.d.</div> 
                        <div class="col-md-3">
                            <input class="form-control date tglSmp" id="tglSmp" name="tglSmp"
                            type="text" autocomplete="off" value="{{ session()->get('filter_tglSmp')}}">
                        </div>
                    </div>
                    


					<button class="btn btn-primary" type="submit" id="filter" class="filter" name="filter">Filter</button>
					<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rkartu_poin")}}'">Reset</button>
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
								"NAMAC"=>array(
									"calculate"=>array(
										"{sumAmount}"=>array("sum","TOTAL")
									),
									"top"=>"<b> {NAMAC}</b>",
									"bottom"=>"<td></td>
											<td></td>
											<td></td>
											<td><b>Total of {NAMAC}</b></td>
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
								"KODEC" => array(
									"label" => "Customer#",
								),
								"NAMAC"=>array(
									"label"=>"-",
									"footerText"=>"<b>Grand Totals</b>"
								),
								"TOTAL"=>array(
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


<div class="modal fade" id="browseCustModal" tabindex="-1" role="dialog" aria-labelledby="browseCustModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="browseCustModalLabel">Cari Customer</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-cust">
				<thead>
					<tr>
						<th>Customer</th>
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

	
	
	var dTableCust;
	loadDataCust = function(){
	
		$.ajax(
		{
			type: 'GET', 		
			url: "{{url('cust/browse')}}",
			data: {
				'GOL': $('#gol').val(),
			},
			success: function( response )
			{
				resp = response;
				if(dTableCust){
					dTableCust.clear();
				}
				for(i=0; i<resp.length; i++){
					
					dTableCust.row.add([
						'<a href="javascript:void(0);" onclick="chooseCust(\''+resp[i].KODEC+'\',  \''+resp[i].NAMAC+'\', \''+resp[i].ALAMAT+'\',  \''+resp[i].KOTA+'\')">'+resp[i].KODEC+'</a>',
						resp[i].NAMAC,
						resp[i].ALAMAT,
						resp[i].KOTA,
					]);
				}
				dTableCust.draw();
			}
		});
	}
	
	dTableCust = $("#table-cust").DataTable({
		
	});
	
	browseCust = function(){
		loadDataCust();
		$("#browseCustModal").modal("show");
	}
	
	chooseCust = function(KODEC, NAMAC, ALAMAT, KOTA){
		$("#kodec").val(KODEC);
		$("#NAMAC").val(NAMAC);	
		$("#browseCustModal").modal("hide");
	}
	
	$("#kodec").keypress(function(e){
		if(e.keyCode == 46){
			e.preventDefault();
			browseCust();
		}
	});

</script>
@endsection