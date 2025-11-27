@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Jurnal Bank</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Laporan Jurnal Bank/li>
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
								<form method="POST" action="{{ url('rbank/jasper-bank-report') }}">
									@csrf
									<div class="form-group nowrap">
										<label><strong>Acno :</strong></label>
										<select name="acno" id="acno" class="form-control acno" style="width: 200px">
											<option value="">--Pilih Account--</option>
											@foreach ($acno as $acnoD)
												<option value="{{ $acnoD->ACNO }}" {{ session()->get('filter_acno1') == $acnoD->ACNO ? 'selected' : '' }}>{{ $acnoD->ACNO }} -
													{{ $acnoD->NAMA }}</option>
											@endforeach
										</select>
									</div>

									<!-- Filter Tanggal -->
									<div class="form-group row">
										{{-- <div class="col-md-2" align="left"><strong style="font-size: 16px;">Tanggal</strong></div>  --}}
										<div class="col-md-2">
											<input class="form-control date tglDr" id="tglDr" name="tglDr" type="text" autocomplete="off"
												value="{{ session()->get('filter_tglDari') }}">
										</div>
										<div>s.d.</div>
										<div class="col-md-2">
											<input class="form-control date tglSmp" id="tglSmp" name="tglSmp" type="text" autocomplete="off"
												value="{{ session()->get('filter_tglSampai') }}">
										</div>
									</div>

									<button class="btn btn-primary" type="submit" id="filter" class="filter" name="filter">Filter</button>
									<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter"
										onclick="window.location='{{ url('rbank') }}'">Reset</button>
									<button class="btn btn-warning" type="submit" id="cetak" class="cetak" formtarget="_blank">Cetak</button>
								</form>
								<div style="margin-bottom: 15px;"></div>
								<!--
																									<table class="table-striped table-border table-hover nowrap datatable table table-fixed">
																													<thead class="table-dark">
																																	<tr>
																																					<th scope="col" style="text-align: center">#</th>
																																					<th scope="col" style="text-align: center">Bukti</th>
																																					<th scope="col" style="text-align: center">Tgl</th>
																																					<th scope="col" style="text-align: center">Bank#</th>
																																					<th scope="col" style="text-align: center">-</th>
																																					<th scope="col" style="text-align: center">Acno#</th>
																																					<th scope="col" style="text-align: center">-</th>
																																					<th scope="col" style="text-align: center">Uraian</th>
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
																																	</tr>
																													</tfoot>
																									</table> -->

								<!-- PASTE DIBAWAH INI -->
								<!-- DISINI BATAS AWAL KOOLREPORT-->
								<div class="report-content" col-md-12 style="max-width: 100%; overflow-x: scroll;">
									<?php

									use koolreport\datagrid\DataTables;

									if ($hasil) {
									    DataTables::create([
									        'dataSource' => $hasil,
									        'name' => 'example',
									        'fastRender' => true,
									        'fixedHeader' => true,
									        'scrollX' => true,
									        'showFooter' => true,
									        'showFooter' => 'bottom',
									        'columns' => [
									            'NO_BUKTI' => [
									                'label' => 'Bukti#',
									            ],
									            'TGL' => [
									                'label' => 'Tanggal',
									            ],
									            'BACNO' => [
									                'label' => 'Bank#',
									            ],
									            'BNAMA' => [
									                'label' => '-',
									            ],
									            'ACNO' => [
									                'label' => 'Acno#',
									            ],
									            'NACNO' => [
									                'label' => '-',
									            ],
									            'URAIAN' => [
									                'label' => 'Uraian',
									                'footerText' => '<b>Grand Total :</b>',
									            ],
									            'DEBET' => [
									                'label' => 'Debet',
									                'type' => 'number',
									                'decimals' => 2,
									                'decimalPoint' => '.',
									                'thousandSeparator' => ',',
									                'footer' => 'sum',
									                'footerText' => '<b>@value</b>',
									            ],
									            'KREDIT' => [
									                'label' => 'Kredit',
									                'type' => 'number',
									                'decimals' => 2,
									                'decimalPoint' => '.',
									                'thousandSeparator' => ',',
									                'footer' => 'sum',
									                'footerText' => '<b>@value</b>',
									            ],
									            'SALDO' => [
									                'label' => 'Saldo',
									                'type' => 'number',
									                'decimals' => 2,
									                'decimalPoint' => '.',
									                'thousandSeparator' => ',',
									                'footer' => 'sum',
									                'footerText' => '<b>@value</b>',
									            ],
									        ],
									        'cssClass' => [
									            'table' => 'table table-hover table-striped table-bordered compact',
									            'th' => 'label-title',
									            'td' => 'detail',
									            'tf' => 'footerCss',
									        ],
									        'options' => [
									            'columnDefs' => [
									                [
									                    'className' => 'dt-right',
									                    'targets' => [7, 8, 9],
									                ],
									            ],
									            'order' => [],
									            'paging' => true,
									            // "pageLength" => 12,
									            'lengthMenu' => [[10, 25, 50, -1], [10, 25, 50, 'All']],
									            'searching' => true,
									            'colReorder' => true,
									            'select' => true,
									            'dom' => 'Blfrtip', // B e dilangi
									            // "dom" => '<"row"<col-md-6"B><"col-md-6"f>> <"row"<"col-md-12"t>><"row"<"col-md-12">>',
									            'buttons' => [
									                [
									                    'extend' => 'collection',
									                    'text' => 'Export',
									                    'buttons' => ['copy', 'excel', 'csv', 'pdf', 'print'],
									                ],
									            ],
									        ],
									    ]);
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
			                url: '{{ route('get-bank-report') }}',
			                data: {
			                    acno: acno,
			                    tglDr: tglDr,
			                    tglSmp: tglSmp
			                }
			            },
			            columns:
			            [
			                {data: 'DT_RowIndex', orderable: false, searchable: false },
			                {data: 'NO_BUKTI', name: 'NO_BUKTI'},
			                {data: 'TGL', name: 'TGL'},
			                {data: 'BACNO', name: 'BACNO'},
			                {data: 'BNAMA', name: 'BNAMA'},
			                {data: 'ACNO', name: 'ACNO'},
			                {data: 'NACNO', name: 'NACNO'},
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
			                "targets": [8,9,10]
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
			                $(api.column(8).footer()).html(pageDebetTotal.toLocaleString('en-US'));
			                $(api.column(9).footer()).html(pageKreditTotal.toLocaleString('en-US'));
			            },


			        });
			    }

			    $('#filter').click(function() {
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
			    });
			    */
		});
	</script>
@endsection
