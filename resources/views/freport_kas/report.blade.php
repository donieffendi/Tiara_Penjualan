@extends('layouts.plain')
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
			<h1 class="m-0">Laporan Jurnal Kas</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
			<li class="breadcrumb-item active">Laporan Jurnal Kas</li>
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
					<form method="POST" action="{{url('rkas/jasper-kas-report')}}">
					@csrf

					<div class="form-group row">
						<div class="col-md-1"><label><strong>Account:</strong></label></div>
                        <div class="col-md-2">
							<select name="acno" id="acno" class="form-control acno" style="width: 200px">
								<option value="">--Pilih Account--</option>
								@foreach($acno as $acnoD)
									<option value="{{$acnoD->ACNO}}" {{ (session()->get('filter_acno1') == $acnoD->ACNO) ? 'selected' : '' }}>{{$acnoD->ACNO}} - {{$acnoD->NAMA}}</option>
								@endforeach
							</select>
                        </div>
					</div>
					

					
						<!-- Filter Tanggal -->
					<div class="form-group row">
						{{-- <div class="col-md-2" align="left"><strong style="font-size: 16px;">Tanggal</strong></div>  --}}
						<div class="col-md-2">
							<input class="form-control date tglDr" id="tglDr" name="tglDr"
							type="text" autocomplete="off" value="{{ session()->get('filter_tglDari') }}"> 
						</div>
						<div>s.d.</div> 
						<div class="col-md-2">
							<input class="form-control date tglSmp" id="tglSmp" name="tglSmp"
							type="text" autocomplete="off" value="{{ session()->get('filter_tglSampai') }}">
						</div>
					</div>
					
					 
						<button class="btn btn-primary" type="submit" id="filter" class="filter" name="filter">Filter</button>
						<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rkas")}}'">Reset</button>
						<button class="btn btn-warning" type="submit" id="cetak" class="cetak" formtarget="_blank">Cetak</button>
						</form>
						<div style="margin-bottom: 15px;"></div>
					
					<div class="report-content" col-md-12 style="max-width: 100%; overflow-x: scroll;">
					<?php
				//	use \koolreport\datagrid\DataTables;

					if($hasil)
					{
					 \koolreport\datagrid\DataTables::create([
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
								"BACNO" => array(
									"label" => "cash#",
								),
								"BNAMA" => array(
									"label" => "-",
								),
								"ACNO" => array(
									"label" => "ACNO#",
								),
								"NACNO" => array(
									"label" => "-",
								),
								"URAIAN" => array(
									"label" => "Uraian",
								),
								"DEBET" => array(
									"label" => "Debet",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"footer" => "sum",
									"footerText" => "<b>@value</b>",
								),
								"KREDIT" => array(
									"label" => "Kredit",
									"type" => "number",
									"decimals" => 2,
									"decimalPoint" => ".",
									"thousandSeparator" => ",",
									"footer" => "sum",
									"footerText" => "<b>@value</b>",
								),
								"SALDO" => array(
									"label" => "Saldo",
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
                            ]);
                        }
                        ?>
                    <!-- DISINI BATAS AKHIR KOOLREPORT-->

				</div>
			</div>
			</div>
		</div>
		</div>
	</div>
</div>





@endsection

@section('javascripts')

<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>
<script>
	$(document).ready(function() {
		
		$('.date').datepicker({  
			dateFormat: 'dd-mm-yy'
		}); 
		
	
	});

	



</script>
@endsection