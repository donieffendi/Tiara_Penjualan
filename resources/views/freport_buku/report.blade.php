	@extends('layouts.plain')

	@section('content')
	<div class="content-wrapper">
		<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0">Laporan Buku Besar</h1>
			</div>
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Laporan Buku Besar</li>
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
						<form method="POST" action="{{url('rbuku/jasper-buku-report')}}" >
						@csrf
						<!--
						<div class="form-group nowrap">
							<label><strong>Acno :</strong></label>
							<select name="acno" id="acno" class="form-control acno" style="width: 200px">
								<option value="">--Pilih Account--</option>
								@foreach($acno as $acnoD)
									<option value="{{$acnoD->ACNO}}">{{$acnoD->ACNO}} - {{$acnoD->NAMA}}</option>
								@endforeach
							</select>
						</div>  -->

						<div class="form-group row">
							<div class="col-md-1" align="right"><strong>Account :</strong></div> 
							<div class="col-md-2">
								<input type="text" class="form-control acno1" id="acno1" name="acno1" placeholder="Pilih Acc# 1" value="{{ session()->get('filter_acno1') }}"readonly>
							</div>  
							<div class="col-md-3">
								<input type="text" class="form-control nacc1" id="nacc1" name="nacc1" placeholder="Nama" value="{{ session()->get('filter_nacc1') }}" readonly>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-1" align="right"><strong>s/d</strong></div> 
							<div class="col-md-2">
								<input type="text" class="form-control acno2"  id="acno2" name="acno2" placeholder="Pilih Acc# 2" value="{{ session()->get('filter_acno2') }}" readonly>
							</div>  
							<div class="col-md-3">
								<input type="text" class="form-control nacc2" id="nacc2" name="nacc2" placeholder="Nama" value="{{ session()->get('filter_nacc2') }}" readonly>
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
						<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rbuku")}}'">Reset</button>
						<button class="btn btn-warning" type="submit" id="cetak" class="cetak" formtarget="_blank">Cetak</button>
						</form>
						<div style="margin-bottom: 15px;"></div>
						
						<!-- <table class="table table-fixed table-striped table-border table-hover nowrap datatable">
							<thead class="table-dark">
								<tr>
									<th scope="col" style="text-align: center">#</th>
									<th scope="col" style="text-align: left">Bukti</th>
									<th scope="col" style="text-align: center">Tgl</th>
									<th scope="col" style="text-align: left">Account#</th>
									<th scope="col" style="text-align: left">-</th>
									<th scope="col" style="text-align: center">Acno#</th>
									<th scope="col" style="text-align: center">-</th>
									<th scope="col" style="text-align: center">Kode#</th>
									<th scope="col" style="text-align: center">-</th>
									<th scope="col" style="text-align: center">Uraian</th>
									<th scope="col" style="text-align: center">Awal</th>
									<th scope="col" style="text-align: center">Debet</th>
									<th scope="col" style="text-align: center">Kredit</th>
									<th scope="col" style="text-align: center">Saldo</th>

								</tr>
							</thead>
							<tbody>
							</tbody> 
							<tfoot>
								<tr>
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
						</table> -->
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
                                    "BACNO" => array(
                                        "label" => "Account#",
                                    ),
                                    "BNAMA" => array(
                                        "label" => "-",
                                    ),
                                    "ACNO" => array(
                                        "label" => "Acno#",
                                    ),
                                    "NACNO" => array(
                                        "label" => "-",
                                    ),
                                    "KODE" => array(
                                        "label" => "Kode#",
                                    ),
                                    "NAMA" => array(
                                        "label" => "Nama",
                                    ),
                                    "URAIAN" => array(
                                        "label" => "Uraian",
                                        "footerText" => "<b>Grand Total :</b>",
                                    ),
									"AWAL" => array(
                                        "label" => "Awal",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
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
                                        //"footer" => "sum",
                                        //"footerText" => "<b>@value</b>",
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
                                            "targets" => [9,10,11,12],
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
	<!-- /.content-wrapper -->
	<div class="modal fade" id="browseAccModal" tabindex="-1" role="dialog" aria-labelledby="browseAccModalLabel" aria-hidden="true">
	  	<div class="modal-dialog" role="document">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="browseAccModalLabel">Cari Account</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-stripped table-bordered" id="table-acc">
					<thead>
						<tr>
							<th>Account#</th>
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
	@endsection

	@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	<script>
		$(document).ready(function() {
			$('.date').datepicker({  
				dateFormat: 'dd-mm-yy'
			}); 
			/*
//			fill_datatable();
// GANTI 3.1 SESUAI GANTI 2.1
			function fill_datatable( acno = '', acno2 = '', tglDr = '', tglSmp = '')
			{
				var dataTable = $('.datatable').DataTable({
					dom: '<"row"<"col-4">>fltip',
					lengthMenu: [
						[ 10, 25, 50, -1 ],
						[ '10 rows', '25 rows', '50 rows', 'Show all' ]
					],
					processing: true,
					serverSide: true,
					autoWidth: true,
					//'scrollX': true,
					'scrollY': '400px',
					"order": [[ 0, "asc" ]],
					ajax: 
					{
// GANTI 4 SESUAI resources -routes - web - GANTI 1
						url: "{{ url('get-buku-report') }}",
						data: {
// GANTI 4.1 SESUAI GANTI 2.1
							acno: acno,
							acno2: acno2,
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
						{data: 'BACNO', name: 'BACNO'},
						{data: 'BNAMA', name: 'BNAMA'},
						{data: 'ACNO', name: 'ACNO'},
						{data: 'NACNO', name: 'NACNO'},
						{data: 'URAIAN', name: 'URAIAN'},
						{
					     data: 'AWAL', 
					     name: 'AWAL',
					     render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				        },
						{
					     data: 'DEBET', 
					     name: 'DEBET',
					     render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				        },	
						{
					     data: 'KREDIT', 
					     name: 'KREDIT',
					     render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				        },	
						{
					     data: 'SALDO', 
					     name: 'SALDO',
					     render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				        },	
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
					
					///////////////////////////////////////////////////
					
					footerCallback: function (row, data, start, end, display) {
						var api = this.api();
			 
						// Remove the formatting to get integer data for summation
						var intVal = function (i) {
							return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
						};
			 
						// Total over this page
						pageDebetTotal = api
							.column(9, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
						pageKreditTotal = api
							.column(10, { page: 'current' })
							.data()
							.reduce(function (a, b) {
								return intVal(a) + intVal(b);
							}, 0);
			 
						// Update footer
						$(api.column(9).footer()).html(pageDebetTotal.toLocaleString('en-US'));
						$(api.column(10).footer()).html(pageKreditTotal.toLocaleString('en-US'));
						//$(api.column(9).footer()).html(Intl.NumberFormat('en-US').format(pageDebetTotal));
						//$(api.column(9).footer()).html(Intl.NumberFormat('en-US').format(pageKreditTotal));
					},
					
					
				});
			}
			
			$('#filter').click(function() {
// GANTI 5.1 SESUAI GANTI 2.1
				var acno = $('#acno1').val();
				var acno2 = $('#acno2').val();
				var tglDr = $('#tglDr').val();
				var tglSmp = $('#tglSmp').val();
				
				if (acno != '' || (tglDr != '' && tglSmp != ''))
				{
					$('.datatable').DataTable().destroy();
					fill_datatable(acno, acno2, tglDr, tglSmp);
				}
			});
			$('#resetfilter').click(function() {
				var acno = '';
				var acno2 = '';
				var tglDr = '';
				var tglSmp = '';

				$('.datatable').DataTable().destroy();
				fill_datatable(acno, acno2, tglDr, tglSmp);
// BATAS GANTI 5.1
			});
			*/
		});
		
		var dTableAcc;
		loadDataAcc = function(indeks){
		
			$.ajax(
			{
				type: 'GET', 		
				url: "{{url('account/browseallacc')}}",
				// data: {
				// 	'GOL': 'Y',
				// },
				success: function( response )
				{
					resp = response;
					if(dTableAcc){
						dTableAcc.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableAcc.row.add([
							'<a href="javascript:void(0);" onclick="chooseAcc(\''+resp[i].ACNO+'\',  \''+resp[i].NAMA+'\', \''+indeks+'\')">'+resp[i].ACNO+'</a>',
							resp[i].NAMA,
						]);
					}
					dTableAcc.draw();
				}
			});
		}
		
		dTableAcc = $("#table-acc").DataTable({
			
		});
		
		browseAcc = function(indeks){
			loadDataAcc(indeks);
			$("#browseAccModal").modal("show");
		}
		
		chooseAcc = function(acno, nacno, indeks){
			$("#acno"+indeks).val(acno);
			$("#nacc"+indeks).val(nacno);	
			$("#browseAccModal").modal("hide");
		}
		
		$("#acno1").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseAcc(1);
			}
		});

		$("#acno2").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseAcc(2);
			}
		});
	</script>
	@endsection
