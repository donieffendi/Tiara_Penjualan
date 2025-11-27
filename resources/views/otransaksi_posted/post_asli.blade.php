@extends('layouts.main')
@section('styles')
<!-- DataTables -->
<link rel="stylesheet" href="{{url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
@endsection

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Transaksi Surat Jalan</h1>
          </div>
          <!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Transaksi Surat Jalan</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Status -->
    @if (session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>
    @endif

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
			  
		
              <form method="POST" action="{{url('surats/posting')}}">
                @csrf	  
                <button class="btn btn-danger" type="submit" id="cetak" class="cetak">Posting</button>
                
                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
											
                            <th scope="col" style="text-align: center">#</th>							
                            <th scope="col" style="text-align: center">Bukti#</th>
                            <th scope="col" style="text-align: center">Tgl</th>
							<th scope="col" style="text-align: center">So#</th>
							<th scope="col" style="text-align: center">Customer</th>
							<th scope="col" style="text-align: center">Nama</th>
                            <th scope="col" style="text-align: center">Barang</th>
                            <th scope="col" style="text-align: center">Kg</th>
                            <th scope="col" style="text-align: center">Total</th>
                            <th scope="col" style="text-align: center">Notes</th>
                            <th scope="col">Cek</th>
                        </tr>
                    </thead>
    
                     <tbody>
                         
                    </tbody> 
                </table>
              </form>
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
        var dataTable = $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: true,
	    //'scrollX': true,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax: 
            {
                url: "{{ route('get-surats') }}",
		data: {
			filterpost : 1,
		}
            },
            columns: 
            [
                {  data: 'DT_RowIndex', orderable: false, searchable: false },
				
                {data: 'NO_BUKTI', name: 'NO_BUKTI'},
                {data: 'TGL', name: 'TGL'},
				{data: 'NO_SO', name: 'NO_SO'},
				{data: 'KODEC', name: 'KODEC'},
				{data: 'NAMAC', name: 'NAMAC'},			
                {data: 'NA_BRG', name: 'NA_BRG'},
				{
				  data: 'KG', 
			      name: 'KG',
			      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				},	
				{
				  data: 'TOTAL', 
			      name: 'TOTAL',
			      render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				},	
				{data: 'NOTES', name: 'NOTES'},
                {data: 'cek', name: 'cek'},			

				
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
                        "targets": [7,8]
                      }
				
            ],
        });
    });
</script>
@endsection
