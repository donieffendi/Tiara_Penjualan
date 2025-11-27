	@extends('layouts.main')

	@section('content')
	<div class="content-wrapper">
		<!-- Content Header (Page header) -->
		<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
			<div class="col-sm-6">
<!-- GANTI 1 -->
				<h1 class="m-0">Laporan Pembelian </h1>
			</div>
			<!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
<!-- GANTI 2 -->
				<li class="breadcrumb-item active">Laporan Pembelian </li>
				</ol>
			</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.container-fluid -->
		</div>
		<!-- /.content-header -->
		<!-- Main content -->
		<div class="content">
			<div class="container-fluid">
			<div class="row">
				<div class="col-12">
				<div class="card">
					<div class="card-body">
					
					
					
						<div class="form-group nowrap">
							<label><strong>Suplier :</strong></label>
<!-- GANTI 2.1 -->
							<select name="kodes" id="kodes" class="form-control kodes" style="width: 200px">
								<option value="">--Pilih Suplier--</option>
								@foreach($kodes as $kodesD)
									<option value="{{$kodesD->KODES}}">{{$kodesD->KODES}} {{$kodesD->NAMAS}}</option>
								@endforeach
							</select>
						</div>
						
						<!-- Filter Tanggal -->
						<div class="form-group row">
							<div class="col-md-3">
								<input class="form-control date tglDr" id="tglDr" name="tglDr"
								type="text" autocomplete="off" value="{{ now()->format('d-m-Y') }}"> 
							</div>
							<div>s.d.</div> 
							<div class="col-md-3">
								<input class="form-control date tglSmp" id="tglSmp" name="tglSmp"
								type="text" autocomplete="off" value="{{ now()->format('d-m-Y') }}">
							</div>
						</div>
						
						
						<button class="btn btn-primary" type="button" id="filter" class="filter">Filter</button>
						<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter">Reset</button>
						<div style="margin-bottom: 15px;"></div>
						<table class="table table-fixed table-striped table-border table-hover nowrap datatable">
							<thead class="table-dark">
								<tr>
<!-- GANTI 3 -->
									<th scope="col" style="text-align: center">#</th>
									<th scope="col" style="text-align: center">Bukti#</th>
									<th scope="col" style="text-align: center">Tgl</th>
									<th scope="col" style="text-align: center">PO#</th>
									<th scope="col" style="text-align: center">Suplier#</th>
									<th scope="col" style="text-align: center">-</th>
									<th scope="col" style="text-align: center">Barang</th>
									<th scope="col" style="text-align: center">-</th>
									<th scope="col" style="text-align: center">Kg</th>
									<th scope="col" style="text-align: center">Harga</th>
									<th scope="col" style="text-align: center">Total</th>
								</tr>
							</thead>
							<tbody>
							</tbody> 
						</table>
					</div>
				</div>
				<!-- /.card -->
				</div>
			</div>
			<!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>
		<!-- /.content -->
	</div>
	<!-- /.content-wrapper -->
	@endsection

	@section('javascripts')
	<script>
		$(document).ready(function() {
			
			$('.date').datepicker({  
				dateFormat: 'dd-mm-yy'
			}); 
			
//			fill_datatable();
// GANTI 3.1 SESUAI GANTI 2.1
			function fill_datatable( kodes = '' , tglDr = '', tglSmp = '' )
			{
				var dataTable = $('.datatable').DataTable({
					dom: '<"row"<"col-4"B>>ltip',
					processing: true,
					serverSide: true,
					autoWidth: true,
					'scrollY': '400px',
					"order": [[ 0, "asc" ]],
					ajax: 
					{
// GANTI 4 SESUAI resources -routes - web - GANTI 1
						url: '{{ route('get-beli-report') }}',
						data: {
// GANTI 4.1 SESUAI GANTI 2.1
							kodes: kodes,
							tglDr: tglDr,
							tglSmp: tglSmp
						}
					},
					columns: 
					[
// GANTI 5 SESUAI DENGAN GANTI 3
						{data: 'DT_RowIndex', orderable: false, searchable: false },
						{data: 'no_bukti', name: 'no_bukti'},
						{data: 'tgl', name: 'tgl'},
						{data: 'no_po', name: 'no_po'},
						{data: 'kodes', name: 'kodes'},
						{data: 'namas', name: 'namas'},
						{data: 'kd_brg', name: 'kd_brg'},
						{data: 'na_brg', name: 'na_brg'},
						{
						 data: 'kg',
					     name: 'kg',
					     render: $.fn.dataTable.render.number( ',', '.', 0, '' )
				        },
						{
						 data: 'harga',
					     name: 'harga',
					     render: $.fn.dataTable.render.number( ',', '.', 0, '' )
				        },
				        {
					     data: 'total',
					     name: 'total',
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
                    "targets": [7,8,9]
                    }
					
					],
					
					
				});
			}
			
			$('#filter').click(function() {
// GANTI 5.1 SESUAI GANTI 2.1
				var kodes = $('#kodes').val();
				var tglDr = $('#tglDr').val();
				var tglSmp = $('#tglSmp').val();
				
				if (kodes != '' || (tglDr != '' && tglSmp != ''))
				{
					$('.datatable').DataTable().destroy();
					fill_datatable(kodes, tglDr, tglSmp);
				}
			});
			$('#resetfilter').click(function() {
				var kodes = '';
				var tglDr = '';
				var tglSmp = '';

				$('.datatable').DataTable().destroy();
				fill_datatable(kodes, tglDr, tglSmp);
// BATAS GANTI 5.1
			});
		});
	</script>
	@endsection