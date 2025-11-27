	@extends('layouts.plain')

	@section('content')
	<div class="content-wrapper">
		<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0">Laporan Jurnal Penyesuaian</h1>
			</div>
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Laporan Jurnal Penyesuaian</li>
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
					<form method="POST" action="{{url('memo/jasper-memo-report')}}">
						@csrf
					<form id="" name="form" method="post" action="{{ url('rmemo/cetak') }}"/>
						<div class="form-group nowrap">
							<label><strong>Tanggal :</strong></label>
							
						</div>
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
						<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("memo")}}'">Reset</button>
						<button class="btn btn-warning" type="submit" id="cetak" class="cetak" formtarget="_blank">Cetak</button>
						</form>
						<div style="margin-bottom: 15px;"></div>
						{{-- <table class="table table-fixed table-striped table-border table-hover nowrap datatable">
							<thead class="table-dark">
								<tr>
									<th scope="col" style="text-align: center">#</th>
									<th scope="col" style="text-align: center">Bukti</th>
									<th scope="col" style="text-align: center">Tgl</th>
									<th scope="col" style="text-align: center">Acno#</th>
									<th scope="col" style="text-align: center">-</th>
									<th scope="col" style="text-align: center">Acnob#</th>
									<th scope="col" style="text-align: center">-</th>
									<th scope="col" style="text-align: center">Uraian</th>
									<th id="debet" scope="col" style="text-align: center">Debet</th>
									<th id="kredit" scope="col" style="text-align: center">Kredit</th>
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
								</tr>
							</tfoot>							
						</table> --}}
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
                                    "ACNO" => array(
                                        "label" => "Bank#",
                                    ),
                                    "NACNO" => array(
                                        "label" => "-",
                                    ),
                                    "ACNOB" => array(
                                        "label" => "AcnoB#",
                                    ),
                                    "NACNOB" => array(
                                        "label" => "-",
                                    ),
                                    "URAIAN" => array(
                                        "label" => "Uraian",
                                        "footerText" => "<b>Grand Total :</b>",
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
                                            "targets" => [7,8],
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
					</div>
				</div>
				</form>
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
//			fill_datatable();
// GANTI 3.1 SESUAI GANTI 2.1
			function fill_datatable( acno = '', tglDr = '', tglSmp = '')
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
// GANTI 4 SESUAI resources -routes - web - GANTI 1
						url: '{{ route('get-memo-report') }}',
						data: {
// GANTI 4.1 SESUAI GANTI 2.1
							acno: acno,
							tglDr: tglDr,
							tglSmp: tglSmp
						}
					},
					columns: 
					[
// GANTI 5 SESUAI DENGAN GANTI 3
						{data: 'DT_RowIndex', orderable: false, searchable: false },
						{data: 'NO_BUKTI', name: 'NO_BUKTI'},
						{data: 'TGL', name: 'TGL'},
						{data: 'ACNO', name: 'ACNO'},
						{data: 'NACNO', name: 'NACNO'},
						{data: 'ACNOB', name: 'ACNOB'},
						{data: 'NACNOB', name: 'NACNOB'},
						{data: 'URAIAN', name: 'URAIAN'},
						{
					     data: 'DEBET', 
					     name: 'DEBET',
					     render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				        },	
						{
					     data: 'KREDIT', 
					     name: 'KREDIT',
					     render: $.fn.dataTable.render.number( ',', '.', 2, '' )
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
                    "targets": [8,9]
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
						pageDebetTotal = api
							.column(8, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						pageKreditTotal = api
							.column(9, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
			 
						// Update footer
						$(api.column(7).footer()).html(pageDebetTotal.toLocaleString('en-US'));
					},						
					
					
				});
			}
			
			$('#filter').click(function() {
// GANTI 5.1 SESUAI GANTI 2.1
				var acno = $('#acno').val();
				var tglDr = $('#tglDr').val();
				var tglSmp = $('#tglSmp').val();
				
				if (acno != '' || (tglDr != '' && tglSmp != ''))
				{
					$('.datatable').DataTable().destroy();
					fill_datatable(acno, tglDr, tglSmp);
				}
			});
			$('#resetfilter').click(function() {
				var acno = '';
				var tglDr = '';
				var tglSmp = '';

				$('.datatable').DataTable().destroy();
				fill_datatable(acno, tglDr, tglSmp);
// BATAS GANTI 5.1
			});
			*/
		});
	</script>
	@endsection
