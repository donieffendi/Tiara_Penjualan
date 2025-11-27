@extends('layouts.main')
<style>
    .bigdrop {
        width: 410px !important;
    }
</style>
@section('content')
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">Laporan Penjualan </h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item active">Laporan Penjualan </li>
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
					<form method="POST" action="{{url('jasper-jual-report')}}">
					@csrf
					<div class="form-group row">


						<div class="col-md-2">						
							<label class="form-label">Kode</label>
							<input type="text" class="form-control kodec" id="kodec" name="kodec" placeholder="Pilih Customer" value="{{ session()->get('filter_kodec1') }}" readonly>
						</div>  
						<div class="col-md-3">
							<label class="form-label">Nama</label>
							<input type="text" class="form-control NAMAC" id="NAMAC" name="NAMAC" placeholder="Nama" value="{{ session()->get('filter_namac1') }}" readonly>
						</div>
					</div>
					
					
					<div class="form-group row">
                        <div class="col-md-2">
                            <input type="text" class="form-control brg1" id="brg1" name="brg1" placeholder="Pilih Barang# 1" value="{{ session()->get('filter_brg1') }}" readonly>
                        </div>  
                        <div class="col-md-3">
                            <input type="text" class="form-control nabrg1" id="nabrg1" name="nabrg1" placeholder="Nama" value="{{ session()->get('filter_nabrg1') }}" readonly>
                        </div>
					</div>

					<div class="form-group row">
                        <div class="col-md-2">
                            <input type="text" class="form-control kdgd1" id="kdgd1" name="kdgd1" placeholder="Pilih Gudang# 1" value="{{ session()->get('filter_kdgd1') }}" readonly>
                        </div>  
					</div>


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
                    <button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rjual")}}'">Reset</button>
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
								<th scope="col" style="text-align: left">SO#</th>
								<th scope="col" style="text-align: left">Customer#</th>
								<th scope="col" style="text-align: left">-</th>
								<th scope="col" style="text-align: left">Barang</th>
								<th scope="col" style="text-align: left">-</th>
								<th scope="col" style="text-align: right">Kg1</th>
								<th scope="col" style="text-align: right">Kg2</th>
								<th scope="col" style="text-align: right">Harga</th>
								<th scope="col" style="text-align: right">Total</th>
							</tr>
						</thead>
						<tbody>
						</tbody> 
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
								"NO_BUKTI" => array(
									"label" => "Bukti#",
								),
								"TGL" => array(
									"label" => "Tanggal",
								),
								"NO_SO" => array(
									"label" => "SO#",
								),
								"KODEC" => array(
									"label" => "Customer#",
								),
								"NAMAC" => array(
									"label" => "-",
								),
								"TRUCK" => array(
									"label" => "Truck#",
								),
								"KD_BRG" => array(
									"label" => "Barang#",
								),
								"NA_BRG" => array(
									"label" => "-",
									"footerText" => "<b>Grand Total :</b>",
								),
								
								"KG" => array(
									"label" => "Kg",
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
								"QTY" => array(
									"label" => "Bag",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"footer" => "sum",
									"footerText" => "<b>@value</b>",
								),
								"GDG" => array(
									"label" => "GDG",
								),

								"GUDANG" => array(
									"label" => "Gudang",
								),
								
								"NOTES" => array(
									"label" => "Notes",
								),
								"DPP" => array(
									"label" => "DPP",
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
								
								/* "KIRIM" => array(
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
								), */
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

<div class="modal fade" id="browseGudangModal" tabindex="-1" role="dialog" aria-labelledby="browseGudangModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
	<div class="modal-content">
		<div class="modal-header">
		<h5 class="modal-title" id="browseGudangModalLabel">Cari Gudang</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
		</div>
		<div class="modal-body">
		<table class="table table-stripped table-bordered" id="table-bgudang">
			<thead>
				<tr>
					<th>Kode</th>
					<th>Nama</th>
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
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script>
	$(document).ready(function() {
		//select2_no_so();

		$('.date').datepicker({  
			dateFormat: 'dd-mm-yy'
		}); 
		/*
		function fill_datatable( kodec = '' , gol='',tglDr = '', tglSmp = '' )
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
					url: '{{ route('get-jual-report') }}',
					data: {
						kodec: kodec,
						gol: gol,
						tglDr: tglDr,
						tglSmp: tglSmp
					}
				},
				columns: 
				[
					{data: 'DT_RowIndex', orderable: false, searchable: false },
					{data: 'NO_BUKTI', name: 'NO_BUKTI'},
					{data: 'TGL', name: 'tgl'},
					{data: 'NO_SO', name: 'NO_SO'},
					{data: 'KODEC', name: 'KODEC'},
					{data: 'NAMAC', name: 'NAMAC'},
					{data: 'KD_BRG', name: 'KD_BRG'},
					{data: 'NA_BRG', name: 'NA_BRG'},
					{
						data: 'KG1',
						name: 'KG1',
						render: $.fn.dataTable.render.number( ',', '.', 0, '' )
					},
					{
						data: 'KG',
						name: 'KG',
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
				
				columnDefs: [
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
				"targets": [8,9,10,11]
				}
				
				],
				
				
			});
		}
		
		$('#filter').click(function() {
			var kodec = $('#kodec').val();
			var gol = $('#gol').val();
			var tglDr = $('#tglDr').val();
			var tglSmp = $('#tglSmp').val();
			
			if (kodec != '' || (tglDr != '' && tglSmp != ''))
			{
				$('.datatable').DataTable().destroy();
				fill_datatable(kodec, gol,tglDr, tglSmp);
			}
		});

		$('#resetfilter').click(function() {
			var kodec = '';
			var gol = '';
			var tglDr = '';
			var tglSmp = '';

			$('.datatable').DataTable().destroy();
			fill_datatable(kodec, gol,tglDr, tglSmp);
		});
		*/
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

	
	var dTableBGudang;
	var rowidGudang;
	loadDataBGudang = function(){
		$.ajax(
		{
			type: 'GET',    
			url: "{{url('gdg/browse')}}",
			success: function(resp)
			{
				if(dTableBGudang){
					dTableBGudang.clear();
				}
				for(i=0; i<resp.length; i++){
					
					dTableBGudang.row.add([
						'<a href="javascript:void(0);" onclick="chooseGudang(\''+resp[i].KODE+'\',  \''+resp[i].NAMA+'\' )">'+resp[i].KODE+'</a>',
						resp[i].NAMA,
						
					]);
				}
				dTableBGudang.draw();
			}
		});
	}
	
	dTableBGudang = $("#table-bgudang").DataTable({
		
	});
	
	browseGudang = function(){
		loadDataBGudang();
		$("#browseGudangModal").modal("show");
	}
	
	chooseGudang = function(KODE,NAMA ){
		$("#kdgd1").val(KODE);		
		$("#browseGudangModal").modal("hide");
	}
	
	$("#kdgd1").keypress(function(e){
		if(e.keyCode == 46){
			e.preventDefault();
			browseGudang();
		}
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
    
    $("#brg1").keypress(function(e){
        if(e.keyCode == 46){
            e.preventDefault();
            browseBrg(1);
        }
    });


    function select2_no_so() {
        $('#no_so1').select2({
            ajax: {
                url: "{{ url('so/get-select-so') }}",
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
            placeholder: 'Pilih SO# ...',
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

        $container.find(".select2-result-repository__title").text(repo.text);
        return $container;
    }

    function formatSelection(repo) {
        return repo.text;
    }
</script>
@endsection