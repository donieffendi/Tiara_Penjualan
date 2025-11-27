	@extends('layouts.main')

	@section('content')
	<div class="content-wrapper">
		<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0">Laporan Account</h1>
			</div>
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Laporan Account</li>
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
					    <form method="POST" action="{{url('jasper-account-report')}}">
					    @csrf
						<div class="form-group nowrap">
							<label><strong>Periode :</strong></label>
							<select name="perio" id="perio" class="form-control perio" style="width: 200px">
								<option value="">--Pilih Periode--</option>
								@foreach($per as $perD)
									<option value="{{$perD->PERIO}}" {{ (session()->get('filter_periode') == $perD->PERIO) ? 'selected' : '' }}>{{$perD->PERIO}}</option>
								@endforeach
							</select>
<!-- GANTI 2.1 -->
							<!--
							<select name="acno" id="acno" class="form-control acno" style="width: 200px">
								<option value="">--Pilih Bahan--</option>
								<option value="1000">Kas</option>
								<option value="1100">Bank</option>
							</select>
							-->
						</div>
						<button class="btn btn-primary" type="submit" id="filter" class="filter" name="filter">Filter</button>
						<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("raccount")}}'">Reset</button>
						<button class="btn btn-warning" type="submit" id="cetak" class="cetak" formtarget="_blank">Cetak</button>
						</form>
						<div style="margin-bottom: 15px;"></div>
{{-- 						
						<table class="table table-fixed table-striped table-border table-hover nowrap datatable">
							<thead class="table-dark">
								<tr>
<!-- GANTI 3 -->
									<th scope="col" style="text-align: center">#</th>
									<th scope="col" style="text-align: center">Account</th>
									<th scope="col" style="text-align: center">-</th>
									<th scope="col" style="text-align: center">Awal</th>
									<th scope="col" style="text-align: center">K-Debet</th>
									<th scope="col" style="text-align: center">K-Kredit</th>
									<th scope="col" style="text-align: center">B-Debet</th>
									<th scope="col" style="text-align: center">B-Kredit</th>
									<th scope="col" style="text-align: center">M-Debet</th>
									<th scope="col" style="text-align: center">M-Kredit</th>
									<th scope="col" style="text-align: center">Akhir</th>
									<th scope="col" style="text-align: center">Pos</th>
									<th scope="col" style="text-align: center">Kel</th>
									<th scope="col" style="text-align: center">-</th>
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
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tr>
							</tfoot>							
						</table> --}}

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
                                    "ACNO" => array(
                                        "label" => "Account#",
                                    ),
                                    "NAMA" => array(
                                        "label" => "-",
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
                                    "KD" => array(
                                        "label" => "K-Debet",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
                                    "KK" => array(
                                        "label" => "K-Kredit",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
									"BD" => array(
                                        "label" => "B-Debet",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
									"BK" => array(
                                        "label" => "B-Kredit",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
									"MD" => array(
                                        "label" => "M-Debet",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
									"MK" => array(
                                        "label" => "M-Kredit",
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
									"POS2" => array(
                                        "label" => "Pos",
                                    ),
									"KEL" => array(
                                        "label" => "Kel",
                                    ),
									"NAMA_KEL" => array(
                                        "label" => "-",
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
                                            "targets" => [2,3,4,5,6,7,8,9],
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
	@endsection

	@section('javascripts')
	<script>
		$(document).ready(function() {
			// fill_datatable();
// GANTI 3.1 SESUAI GANTI 2.1
		//	function fill_datatable( acno = '')
// 		function fill_datatable(per='')	
// 		{
// 				var dataTable = $('.datatable').DataTable({
// 					dom: '<"row"<"col-4"B>>ltip',
// 					lengthMenu: [
// 						[ 10, 25, 50, -1 ],
// 						[ '10 rows', '25 rows', '50 rows', 'Show all' ]
// 					],
// 					processing: true,
// 					serverSide: true,
// 					autoWidth: true,
// 					'scrollX': true,
// 					'scrollY': '400px',
// 					"order": [[ 0, "asc" ]],
// 					ajax: 
// 					{
// // GANTI 4 SESUAI resources -routes - web - GANTI 1
// 						url: "{{ route('get-account-report') }}",
// 						data: {
// 							perio: per,
// 						},
// 					},
// 					columns: 
// 					[
// // GANTI 5 SESUAI DENGAN GANTI 3
// 						{data: 'DT_RowIndex', orderable: false, searchable: false },
// 						{data: 'ACNO', name: 'ACNO'},
// 						{data: 'NAMA', name: 'NAMA'},
//                         {
// 					      data: 'AW', 
// 					      name: 'AW',
// 					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
// 				        },
// 						{
// 					      data: 'KD', 
// 					      name: 'KD',
// 					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
// 				        },						
// 						{
// 					      data: 'KK', 
// 					      name: 'KK',
// 					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
// 				        },
// 						{
// 					      data: 'BD', 
// 					      name: 'BD',
// 					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
// 				        },
// 						{
// 					      data: 'BK', 
// 					      name: 'BK',
// 					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
// 				        },	
// 						{
// 					      data: 'MD', 
// 					      name: 'MD',
// 					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
// 				        },
// 						{
// 					      data: 'MK', 
// 					      name: 'MK',
// 					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
// 				        },						
// 						{
// 					      data: 'AK', 
// 					      name: 'AK',
// 					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
// 				        }
// 					],
					
// 					///////////////////////////////////////////////////
// 					 columnDefs: [
// 					{
//                     "className": "dt-center", 
//                     "targets": 0
// 					},			
// 					{
//                     "className": "dt-right", 
//                     "targets": [3,4,5,6,7,8,9,10]
// 					}
				
// 					],
// 					///////////////////////////////////////////////////
					
// 					footerCallback: function (row, data, start, end, display) {
// 						var api = this.api();
			 
// 						// Remove the formatting to get integer data for summation
// 						var intVal = function (i) {
// 							return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
// 						};
			 
// 						// Total over this page
// 						pageAwalTotal = api
// 							.column(3, { page: 'current' })
// 							.data()
// 							.reduce(function (a, b) {
// 								return intVal(a) + intVal(b);
// 							}, 0);
// 						pageKDebetTotal = api
// 							.column(4, { page: 'current' })
// 							.data()
// 							.reduce(function (a, b) {
// 								return intVal(a) + intVal(b);
// 							}, 0);
// 						pageKKreditTotal = api
// 							.column(5, { page: 'current' })
// 							.data()
// 							.reduce(function (a, b) {
// 								return intVal(a) + intVal(b);
// 							}, 0);
// 						pageBDebetTotal = api
// 							.column(6, { page: 'current' })
// 							.data()
// 							.reduce(function (a, b) {
// 								return intVal(a) + intVal(b);
// 							}, 0);
// 						pageBKreditTotal = api
// 							.column(7, { page: 'current' })
// 							.data()
// 							.reduce(function (a, b) {
// 								return intVal(a) + intVal(b);
// 							}, 0);	
// 						pageMDebetTotal = api
// 							.column(8, { page: 'current' })
// 							.data()
// 							.reduce(function (a, b) {
// 								return intVal(a) + intVal(b);
// 							}, 0);
// 						pageMKreditTotal = api
// 							.column(9, { page: 'current' })
// 							.data()
// 							.reduce(function (a, b) {
// 								return intVal(a) + intVal(b);
// 							}, 0);
// 						pageAkhirTotal = api
// 							.column(10, { page: 'current' })
// 							.data()
// 							.reduce(function (a, b) {
// 								return intVal(a) + intVal(b);
// 							}, 0);

	
			 
// 						// Update footer
// 						$(api.column(3).footer()).html(pageAwalTotal.toLocaleString('en-US'));
// 						$(api.column(4).footer()).html(pageKDebetTotal.toLocaleString('en-US'));
// 						$(api.column(5).footer()).html(pageKKreditTotal.toLocaleString('en-US'));
// 						$(api.column(6).footer()).html(pageBDebetTotal.toLocaleString('en-US'));
// 						$(api.column(7).footer()).html(pageBKreditTotal.toLocaleString('en-US'));
// 						$(api.column(8).footer()).html(pageMDebetTotal.toLocaleString('en-US'));
// 						$(api.column(9).footer()).html(pageMKreditTotal.toLocaleString('en-US'));
// 						$(api.column(10).footer()).html(pageAkhirTotal.toLocaleString('en-US'));

						
// 					},
					
					
// 				});
// 			}
			
// 			$('#filter').click(function() {
// // GANTI 5.1 SESUAI GANTI 2.1
// 				//var acno = $('#acno').val();
// 				//if (acno != '')
// 				//{
// 					$('.datatable').DataTable().destroy();
// 					var periode = $('#perio').val();
// 					fill_datatable(periode);
// 				//}
// 			});
// 			$('#resetfilter').click(function() {
// 				var periode = '';

// 				$('.datatable').DataTable().destroy();
// 				fill_datatable(periode);
// // BATAS GANTI 5.1
// 			});
		});
	</script>
	@endsection
