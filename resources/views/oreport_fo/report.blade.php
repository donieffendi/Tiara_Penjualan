	@extends('layouts.main')

	@section('content')
	<div class="content-wrapper">
		<!-- Content Header (Page header) -->
		<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
			<div class="col-sm-6">
<!-- GANTI 1 -->
				<h1 class="m-0">Laporan Jurnal Penyesuaian</h1>
			</div>
			<!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
<!-- GANTI 2 -->
				<li class="breadcrumb-item active">Laporan Jurnal Penyesuaian</li>
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
							<label><strong>Acno :</strong></label>
<!-- GANTI 2.1 -->
							<select name="acno" id="acno" class="form-control acno" style="width: 200px">
								<option value="">--Pilih Account--</option>
								<option value="1000">Kas</option>
								<option value="1100">Bank</option>
							</select>
						</div>
						<button class="btn btn-primary" type="button" id="filter" class="filter">Filter</button>
						<button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter">Reset</button>
						<div style="margin-bottom: 15px;"></div>
						<table class="table table-fixed table-striped table-border table-hover nowrap datatable">
							<thead class="table-dark">
								<tr>
<!-- GANTI 3 -->
									<th scope="col" style="text-align: center">#</th>
									<th scope="col" style="text-align: center">Bukti</th>
									<th scope="col" style="text-align: center">Tgl</th>
									<th scope="col" style="text-align: center">Acno</th>
									<th scope="col" style="text-align: center">-</th>
									<th scope="col" style="text-align: center">AcnoB</th>
									<th scope="col" style="text-align: center">-</th>
									<th scope="col" style="text-align: center">Uraian</th>
									<th scope="col" style="text-align: center">Jumlah</th>
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
//			fill_datatable();
// GANTI 3.1 SESUAI GANTI 2.1
			function fill_datatable( acno = '')
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
						url: '{{ route('get-memo-report') }}',
						data: {
// GANTI 4.1 SESUAI GANTI 2.1
							acno: acno
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
						{data: 'DEBET', name: 'DEBET'},
						{data: 'KREDIT', name: 'KREDIT'}
					]
				});
			}
			
			$('#filter').click(function() {
// GANTI 5.1 SESUAI GANTI 2.1
				var acno = $('#acno').val();
				if (acno != '')
				{
					$('.datatable').DataTable().destroy();
					fill_datatable(acno);
				}
			});
			$('#resetfilter').click(function() {
				var acno = '';

				$('.datatable').DataTable().destroy();
				fill_datatable(acno);
// BATAS GANTI 5.1
			});
		});
	</script>
	@endsection