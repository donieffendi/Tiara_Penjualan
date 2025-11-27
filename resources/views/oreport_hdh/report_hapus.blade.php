@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
	<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0">Laporan Penghapusan Barang Hadiah</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Laporan Penghapusan Barang Hadiah</li>
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
				
					<form method="POST" action="{{url('jasper-hdh-report-hapus')}}">
					@csrf
					<div class="form-group row">

						{{-- <div class="col-md-2">						
							<label class="form-label">Suplier</label>
							<input type="text" class="form-control kodes" id="kodes" name="kodes" placeholder="Pilih Suplier" value="{{ session()->get('filter_kodes1') }}" readonly>
						</div>  
						<!-- <div class="col-md-3">
							<label class="form-label">Nama</label>
							<input type="text" class="form-control NAMAS" id="NAMAS" name="NAMAS" placeholder="Nama" value="{{ session()->get('filter_namas1') }}" readonly>
						</div> -->
						<div class="col-md-1">
							<label class="form-label"> s.d </label>
						</div> 
						<div class="col-md-2">						
							<label class="form-label">Suplier 2</label>
							<input type="text" class="form-control kodes2" id="kodes2" name="kodes2" placeholder="ZZZ" value="{{ session()->get('filter_kodes2') }}" readonly>
						</div>   --}}

						<!-- <div class="col-md-2">
							<label><strong>Gol :</strong></label>
							
							<select name="gol" id="gol" class="form-control gol">
								<option value="J" {{ session()->get('filter_gol')=='J' ? 'selected': ''}}>J</option>
								<option value="B" {{ session()->get('filter_gol')=='B' ? 'selected': ''}}>B</option>
							</select>
						</div> -->
						
					</div>
					
					<!-- <div class="form-group row">

                        <div class="col-md-2">
							<label class="form-label">Barang</label>
                            <input type="text" class="form-control brg1" id="brg1" name="brg1" placeholder="Pilih Barang# 1" value="{{ session()->get('filter_brg1') }}" readonly>
                        </div>  
                        <div class="col-md-3">
							<label class="form-label">s/d</label>
                            <input type="text" class="form-control nabrg1" id="nabrg1" name="nabrg1" placeholder="Nama" value="{{ session()->get('filter_nabrg1') }}" readonly>
                        </div>
					</div> -->
					
					<!-- Filter Tanggal -->
					<div class="form-group row">
						<div class="col-md-3">
							<input class="form-control date tglDr" id="tglDr" name="tglDr"
							type="text" autocomplete="off" value="{{ session()->get('filter_tglDari') }}"> 
						</div>
						<div>s.d.</div> 
						<div class="col-md-3">
							<input class="form-control date tglSmp" id="tglSmp" name="tglSmp"
							type="text" autocomplete="off" value="{{ session()->get('filter_tglSampai') }}">
						</div>
					</div>

                    <button class="btn btn-primary" type="submit" id="filter" class="filter" name="filter">Filter</button>
                    <button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rmasuk")}}'">Reset</button>
					<button class="btn btn-warning" type="submit" id="cetak" class="cetak" formtarget="_blank">Cetak</button>
					</form>
					<div style="margin-bottom: 15px;"></div>
					<!--
					<table class="table table-fixed table-striped table-border table-hover nowrap datatable">
						<thead class="table-dark">
							<tr>
								<th scope="col" style="text-align: center">#</th>
								<th scope="col" style="text-align: left">Bukti#</th>
								<th scope="col" style="text-align: left">Tgl</th>
								<th scope="col" style="text-align: left">PO#</th>
								<th scope="col" style="text-align: left">Suplier#</th>
								<th scope="col" style="text-align: left">-</th>
								<th scope="col" style="text-align: left">Barang#</th>
								<th scope="col" style="text-align: left">-</th>
								<th scope="col" style="text-align: right">Kg</th>
								<th scope="col" style="text-align: right">Harga</th>
								<th scope="col" style="text-align: right">Total</th>
							</tr>
						</thead>
						<tbody>
						</tbody> 
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
                                    "NO_PO" => array(
                                        "label" => "PO#",
                                    ),
                                    "KODES" => array(
                                        "label" => "Suplier#",
                                    ),
                                    "NAMAS" => array(
                                        "label" => "-",
                                    ),
                                    "KD_BRG" => array(
                                        "label" => "Kode#",
                                    ),
                                    "NA_BRG" => array(
                                        "label" => "Uraian",
                                        "footerText" => "<b>Grand Total :</b>",
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
                                        "decimals" => 5,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        //"footer" => "avg",
                                        //"footerText" => "<b>@value</b>",
                                    ),
                                    "TOTAL_QTY" => array(
                                        "label" => "Total",
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
										"targets" => [7,8,9],
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

<div class="modal fade" id="browseSuplier2Modal" tabindex="-1" role="dialog" aria-labelledby="browseSuplier2ModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="browseSuplier2ModalLabel">Cari Suplier2</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bsuplier2">
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
<div class="modal fade" id="browseBrgModal" tabindex="-1" role="dialog" aria-labelledby="browseBrgModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="browseBrgModalLabel">Cari Barang</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-brg">
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

var dTableBSuplier2;
	loadDataBSuplier2 = function(){
	
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
				if(dTableBSuplier2){
					dTableBSuplier2.clear();
				}
				for(i=0; i<resp.length; i++){
					
					dTableBSuplier2.row.add([
						'<a href="javascript:void(0);" onclick="chooseSuplier2(\''+resp[i].KODES+'\')">'+resp[i].KODES+'</a>',
						resp[i].NAMAS,
						resp[i].ALAMAT,
						resp[i].KOTA,
					]);
				}
				dTableBSuplier2.draw();
			}
		});
	}
	
	dTableBSuplier2 = $("#table-bsuplier2").DataTable({
		
	});
	
	browseSuplier2 = function(){
		loadDataBSuplier2();
		$("#browseSuplier2Modal").modal("show");
	}
	
	chooseSuplier2 = function(KODES){
		$("#kodes2").val(KODES);
		// $("#NAMAS").val(NAMAS);	
		$("#browseSuplier2Modal").modal("hide");
	}
	
	$("#kodes2").keypress(function(e){
		if(e.keyCode == 46){
			e.preventDefault();
			browseSuplier2();
		}
	}); 

//////////////////////////////////////////////////////////////////////	
	
    var dTableBrg;
    loadDataBrg = function(indeks){
    
        $.ajax(
        {
            type: 'GET', 		
            url: "{{url('brg/browse')}}",
            data: {
               'GOL': $('#gol').val(),
            },
            success: function( response )
            {
                resp = response;
                if(dTableBrg){
                    dTableBrg.clear();
                }
                for(i=0; i<resp.length; i++){
                    
                    dTableBrg.row.add([
                        '<a href="javascript:void(0);" onclick="chooseBrg(\''+resp[i].KD_BRG+'\',  \''+resp[i].NA_BRG+'\', \''+indeks+'\')">'+resp[i].KD_BRG+'</a>',
                        resp[i].NA_BRG,
                        resp[i].SATUAN,
                    ]);
                }
                dTableBrg.draw();
            }
        });
    }
    
    dTableBrg = $("#table-brg").DataTable({
        
    });
    
    browseBrg = function(indeks){
        loadDataBrg(indeks);
        $("#browseBrgModal").modal("show");
    }
    
    chooseBrg = function(KD_BRG, NA_BRG, indeks){
        $("#brg"+indeks).val(KD_BRG);
        $("#nabrg"+indeks).val(NA_BRG);	
        $("#browseBrgModal").modal("hide");
    }
    
    $("#brg1").keypress(function(e){
        if(e.keyCode == 46){
            e.preventDefault();
            browseBrg(1);
        }
    });
</script>
@endsection