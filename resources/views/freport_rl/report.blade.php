	@extends('layouts.plain')

	@section('content')
	<div class="content-wrapper">
		<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0">Laporan Rugi Laba</h1>
			</div>
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Laporan Rugi Laba</li>
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
					    <form method="POST" action="{{url('jasper-rl-report')}}" >
					    @csrf
						<div class="form-group nowrap">
							<label><strong>Periode :</strong></label>
							<select name="perio" id="perio" class="form-control perio" style="width: 200px">
								<option value="">--Pilih Periode--</option>
								@foreach($per as $perD)
									<option value="{{$perD->PERIO}}" {{ (session()->get('filter_periode') == $perD->PERIO) ? 'selected' : '' }}>{{$perD->PERIO}}</option>
								@endforeach
							</select>
							<!--
							<select name="acno" id="acno" class="form-control acno" style="width: 200px">
								<option value="">--Pilih Bahan--</option>
								<option value="1000">Kas</option>
								<option value="1100">Bank</option>
							</select>
							-->
						</div>
						<button class="btn btn-primary" type="submit" id="filter" class="filter" name="filter">Filter</button>
						<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rrl")}}'">Reset</button>
						<button class="btn btn-warning" type="submit" id="cetak" class="cetak" formtarget="_blank">Cetak</button>
						</form>
						<div style="margin-bottom: 15px;"></div>
						{{-- <table class="table table-fixed table-striped table-border table-hover nowrap datatable">
							<thead class="table-dark">
								<tr>
									<th scope="col" style="text-align: center">#</th>
									<th scope="col" style="text-align: center">Kode</th>
									<th scope="col" style="text-align: center">-</th>
									<th scope="col" style="text-align: center">Bulan Ini</th>
									<th scope="col" style="text-align: center">Akumulasi</th>
								</tr>
							</thead>
							<tbody>
							</tbody> 
						</table> --}}
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
                                    "KODE" => array(
                                        "label" => "Kode#",
                                    ),
                                    "NAMA" => array(
                                        "label" => "-",
										 "type" => "checkbox",
                                    ),
                                    "JUM" => array(
                                        "label" => "Bulan Ini",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footerText" => "<b>@value</b>",
                                    ),
                                    "AK" => array(
                                        "label" => "Akumulasi",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
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
                                            "targets" => [2,3],
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
			// fill_datatable();
// GANTI 3.1 SESUAI GANTI 2.1
		//	function fill_datatable( acno = '')
// 		function fill_datatable(per='')	
// 		{
// 				var dataTable = $('.datatable').DataTable({
// 					dom: '<"row"<"col-4"B>>ltip',
// 					processing: true,
// 					serverSide: true,
// 					autoWidth: true,
// 					'scrollY': '400px',
// 					"order": [[ 0, "asc" ]],
// 					ajax: 
// 					{
// // GANTI 4 SESUAI resources -routes - web - GANTI 1
// 						url: "{{ route('get-rl-report') }}",
// 						data: {
// 							perio: per,
// 						},
// 					},
// 					columns: 
// 					[
// // GANTI 5 SESUAI DENGAN GANTI 3
// 						{data: 'DT_RowIndex', orderable: false, searchable: false },
// 						{data: 'KODE', name: 'KODE'},
// 						{data: 'NAMA', name: 'NAMA'},
//                         {
// 					      data: 'JUM', 
// 					      name: 'JUM',
// 					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
// 				        },
// 						{
// 					      data: 'AK', 
// 					      name: 'AK',
// 					      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
// 				        }
// 					]
					
					
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
