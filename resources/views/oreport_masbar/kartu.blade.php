@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
	<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0">Kartu Stok Barang</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Kartu Stok Barang</li>
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
					<form method="POST" action="{{url('jasper-stok-kartu_vbrg')}}">
					@csrf
					<div class="form-group row">
                        <div class="col-md-1" align="right"><strong>Barang :</strong></div>
                        <div class="col-md-2">
                            <select class="form-control brg1" name="brg1" id="brg1" onchange="fillBrg(this.id)">
									<option value="{{ session()->get('filter_brg1') }}" {{ (session()->get('filter_brg1') != '') ? 'selected' : '' }}>{{ session()->get('filter_brg1') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control nabrg1" id="nabrg1" name="nabrg1" placeholder="Nama" value="{{ session()->get('filter_nabrg1') }}" readonly>
                        </div>
					</div>

					
                    <div class="form-group row">
                        <div class="col-md-1" align="right"><strong>s/d</strong></div>
                        <div class="col-md-2">
                            <select class="form-control brg2" name="brg2" id="brg2" onchange="fillBrg(this.id)">
									<option value="{{ session()->get('filter_brg2') }}" {{ (session()->get('filter_brg2') != '') ? 'selected' : '' }}>{{ session()->get('filter_brg2') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control nabrg2" id="nabrg2" name="nabrg2" placeholder="Nama" value="{{ session()->get('filter_nabrg2') }}" readonly>
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
					<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rkarstk")}}'">Reset</button>
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
										"{sumAmount}"=>array("sum","AWAL"),
										"{sumAmount2}"=>array("sum","MASUK"),
										"{sumAmount3}"=>array("sum","KELUAR"),
										"{sumAmount4}"=>array("sum","LAIN")
									),
									"top"=>"<b >YEAR {year}</b>",
									// "bottom"=>"<tr style='background:#aaa;'><td></td>
									"bottom"=>"<td></td>
												<td></td>
												<td></td>
												<td><b>TOTAL OF YEAR {year}</b></td>
												<td style='text-align: right' ><b>{sumAmount}</b></td>,
												<td style='text-align: right' ><b>{sumAmount2}</b></td>,
												<td style='text-align: right' ><b>{sumAmount3}</b></td>,
												<td style='text-align: right' ><b>{sumAmount4}</b></td>
											",
											// </tr>",
								),
								"NA_BRG"=>array(
									"calculate"=>array(
										"{sumAmount}"=>array("sum","AWAL"),
										"{sumAmount2}"=>array("sum","MASUK"),
										"{sumAmount3}"=>array("sum","KELUAR"),
										"{sumAmount4}"=>array("sum","LAIN")
									),
									"top"=>"<b> {NA_BRG}</b>",
									"bottom"=>"<td></td>
											<td></td>
											<td></td>
											<td><b>TOTAL OF {NA_BRG}</b></td>
											<td style='text-align: right' ><b>{sumAmount}</b></td>,
											<td style='text-align: right' ><b>{sumAmount2}</b></td>,
											<td style='text-align: right' ><b>{sumAmount3}</b></td>,
											<td style='text-align: right' ><b>{sumAmount4}</b></td>",
								),
							),
							
							"sorting"=>array(
								"urut"=>"asc"
							),
							"showFooter"=>true,
							"columns"=>array(
								"NO_BUKTI" => array(
									"label" => "NO BUKTI",
								),
								"TGL" => array(
									"label" => "TANGGAL",
								),
								"KD_BRG" => array(
									"label" => "BARANG",
								),
								"NA_BRG"=>array(
									"label"=>"-",
									"footerText"=>"<b>GRAND TOTALS</b>"
								),
								"AWAL"=>array(
									"label"=>"AWAL",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									// "prefix"=>"Rp. ",
									"footer"=>"sum",
									"footerText"=>"<b>@value</b>",
									"cssStyle"=>"text-align: right;" // Menjadikan total rata kanan
								),
								"MASUK"=>array(
									"label"=>"MASUK",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									// "prefix"=>"Rp. ",
									"footer"=>"sum",
									"footerText"=>"<b>@value</b>",
									"cssStyle"=>"text-align: right;" // Menjadikan total rata kanan
								),
								"KELUAR"=>array(
									"label"=>"KELUAR",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									// "prefix"=>"Rp. ",
									"footer"=>"sum",
									"footerText"=>"<b>@value</b>",
									"cssStyle"=>"text-align: right;" // Menjadikan total rata kanan
								),
								"LAIN"=>array(
									"label"=>"LAIN",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									// "prefix"=>"Rp. ",
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
										"targets" => [4,5,6,7],
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
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


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

		select2_kd_brg();
	});

	var dTableBrg;
    loadDataBrg = function(indeks){
    
        $.ajax(
        {
            type: 'GET', 		
            url: "{{url('brg/browse')}}",
            data: {
                'GOL': 'Y',
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
    /*
    $("#brg1").keypress(function(e){
        if(e.keyCode == 46){
            e.preventDefault();
            browseBrg(1);
        }
    });

    $("#brg2").keypress(function(e){
        if(e.keyCode == 46){
            e.preventDefault();
            browseBrg(2);
        }
    }); */
	
    function select2_kd_brg() {
        $('#brg1').select2({
            ajax: {
                url: "{{ url('vbrg/get-select-kdbrg') }}",
                dataType: "json",
                type: "GET",
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page
                    }
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: data.total_count
                        }
                    };
                },
                cache: true
            },
			allowClear: true,
            dropdownCssClass: "bigdrop",
            // dropdownAutoWidth: true,
            placeholder: 'Pilih Barang ...',
            minimumInputLength: 0,
            templateResult: format,
            templateSelection: formatSelection,
            theme: "classic",
        });
        
        $('#brg2').select2({
            ajax: {
                url: "{{ url('vbrg/get-select-kdbrg') }}",
                dataType: "json",
                type: "GET",
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page
                    }
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: data.total_count
                        }
                    };
                },
                cache: true
            },
			allowClear: true,
            dropdownCssClass: "bigdrop",
            // dropdownAutoWidth: true,
            placeholder: 'Pilih Barang ...',
            minimumInputLength: 0,
            templateResult: format,
            templateSelection: formatSelection,
            theme: "classic",
        });
    }

    function format(repo) {
        if (repo.loading) {
            return repo.text;
        }

        var $container = $(
            "<div class='select2-result-repository clearfix text_input'>" +
            "<div class='select2-result-repository__title text_input'></div>" +
            "</div>"
        );

        $container.find(".select2-result-repository__title").text(repo.text+' - '+repo.na_brg);
        return $container;
    }

	var kdbarang = '';
	var namabarang = '';

    function formatSelection(repo) {
        kdbarang = repo.id;
        namabarang = repo.na_brg;
        return repo.text;
    }
    
	function fillBrg(id) {
        $('#na'+id).val(namabarang);
        if(kdbarang=="{{ session()->get('filter_brg1') }}" && id=='brg1') 
        {
            $('#na'+id).val("{{ session()->get('filter_nabrg1') }}");
        }
        if(kdbarang=="{{ session()->get('filter_brg2') }}" && id=='brg2') 
        {
            $('#na'+id).val("{{ session()->get('filter_nabrg2') }}");
        }
	}
</script>
@endsection