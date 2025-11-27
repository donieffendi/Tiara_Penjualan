@extends('layouts.plain')
@section('styles')
<link rel="stylesheet" href="{{url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
@endsection

<style>  
    th { font-size: 13px; }
    td { font-size: 13px; }
</style>

@section('content')
<div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
		        <h1 class="m-0">Kasir Grab</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Kasir Grab</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Status -->
    @if (session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>
    @endif

    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>							
                            <th scope="col" style="text-align: center">Sub</th>
                            <th scope="col" style="text-align: center">Sub Item</th>
                            <th scope="col" style="text-align: center">Nama Barang</th>
                            <th scope="col" style="text-align: right">Harga SPM</th>
                            <th scope="col" style="text-align: right">Harga Grab</th>
							<th scope="col" style="text-align: center">Turun Harga</th>
							<th scope="col" style="text-align: center">Tgl Mulai</th>
							<th scope="col" style="text-align: center">Tgl Akhir</th>
							<th scope="col" style="text-align: center">Harga OK</th>
							<th scope="col" style="text-align: center">LPH</th>
							<th scope="col" style="text-align: right">Stok</th>
							<th scope="col" style="text-align: center">Bintang</th>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  $(document).ready(function() {
        var dataTable = $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: true,
            paging: true,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax: {
                url: '{{ route('get-kasirgrab') }}'
            },
            columns: 
            [
				{data: 'SUB', name: 'SUB'},
                {data: 'KD_BRG', name: 'KD_BRG'},
                {data: 'NA_BRG', name: 'NA_BRG'},
                {data: 'HJ_ASLI', name: 'HJ_ASLI', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},				
                {data: 'HJ_GRAB', name: 'HJ_GRAB', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},				
                {data: 'TRN_HRG', name: 'TRN_HRG'},
				{data: 'TGL1', name: 'TGL1'},
				{data: 'TGL2', name: 'TGL2'},
				{data: 'HARGA', name: 'HARGA', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
				{data: 'LPH', name: 'LPH'},
				{data: 'STOK', name: 'STOK', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
				{data: 'BINTANG', name: 'BINTANG'},
            ],

            columnDefs: [
                {
                    "className": "dt-center", 
                    "targets": [0,1,2,5,6,7,8,11]
                },
				{
					"className": "dt-right",
					"targets": [3,4,9,10]
				},
            ],

            dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
				    
            stateSave:true,

        });

        // tombol tampil ditekan
        $('#btnTampil').on('click', function() {
            dataTable.ajax.reload();
        });
        
        // === tambahkan ini setelah DataTable di-initialize ===
        dataTable.on('xhr.dt', function (e, settings, json, xhr) {
          console.log("XHR event fired:", json);

          if (json && json.data && json.data.length > 0) {
              console.log("Row pertama:", json.data[0]);
          } else {
              console.warn("Data kosong atau JSON null");
          }
        });
    });

	
</script>
@endsection