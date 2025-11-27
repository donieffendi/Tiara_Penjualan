@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
	<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0">Laporan Koreksi Stock A</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
			<li class="breadcrumb-item active">Laporan Koreksi Stock A</li>
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
					<form method="POST" action="{{url('jasper-stocka-report')}}">
					@csrf
					<div class="form-group nowrap">
						<label><strong>Tanggal :</strong></label>
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

					

					<div class="form-group row">
						<div class="col-md-1">
							<label><strong>Cabang :</strong></label>
							<select name="cbg" id="cbg" class="form-control cbg" style="width: 200px">
								<option value="">--Pilih Cabang--</option>
								@foreach($cbg as $cbgD)
									<option value="{{$cbgD->CBG}}"  {{ (session()->get('filter_cbg') == $cbgD->CBG) ? 'selected' : '' }}>{{$cbgD->CBG}}</option>
								@endforeach
							</select>
						</div>
					</div>
					
					<!-- <div class="form-group row">
						<div class="col-md-2">
							<label><strong>Type :</strong></label>
							
							<select name="type" id="type" class="form-control type">
								<option value="">Semua</option>
								<option value="N" {{ session()->get('filter_type')=='N' ? 'selected': ''}}>Normal</option>
								<option value="S" {{ session()->get('filter_type')=='S' ? 'selected': ''}}>Surat Jalan</option>
							</select>
						</div>
					</div> -->
					
                    <button class="btn btn-primary" type="submit" id="filter" class="filter" name="filter">Filter</button>
                    <button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rstocka")}}'">Reset</button>
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
								<th scope="col" style="text-align: left">Barang</th>
								<th scope="col" style="text-align: left">-</th>
								<th scope="col" style="text-align: right">Kg</th>
								<th scope="col" style="text-align: left">Notes</th>
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
                                    "KD_BHN" => array(
                                        "label" => "Barang#",
                                    ),
                                    "NA_BHN" => array(
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
                                            "targets" => [4],
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
@endsection

@section('javascripts')
<script>
	$(document).ready(function() {
		
		$('.date').datepicker({  
			dateFormat: 'dd-mm-yy'
		}); 
		/*
		function fill_datatable( kd_brg = '' , tglDr = '', tglSmp = '' )
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
					url: '{{ route('get-stocka-report') }}',
					data: {
						kd_brg: kd_brg,
						tglDr: tglDr,
						tglSmp: tglSmp
					}
				},
				columns: 
				[
					{data: 'DT_RowIndex', orderable: false, searchable: false },
					{data: 'NO_BUKTI', name: 'NO_BUKTI'},
					{data: 'TGL', name: 'TGL'},
					{data: 'KD_BRG', name: 'KD_BRG'},
					{data: 'NA_BRG', name: 'NA_BRG'},
					{
						data: 'KG',
						name: 'KG',
						render: $.fn.dataTable.render.number( ',', '.', 0, '' )
					},
					{data: 'NOTES', name: 'NOTES'}
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
				"targets": 5
				}
				
				],

			});
		}
		
		$('#filter').click(function() {
			var kd_brg = $('#kd_brg').val();
			var tglDr = $('#tglDr').val();
			var tglSmp = $('#tglSmp').val();
			
			if (kd_brg != '' || (tglDr != '' && tglSmp != ''))
			{
				$('.datatable').DataTable().destroy();
				fill_datatable(kd_brg, tglDr, tglSmp);
			}
		});

		$('#resetfilter').click(function() {
			var kd_brg = '';
			var tglDr = '';
			var tglSmp = '';

			$('.datatable').DataTable().destroy();
			fill_datatable(kd_brg, tglDr, tglSmp);
		});
		*/
	});
</script>
@endsection
