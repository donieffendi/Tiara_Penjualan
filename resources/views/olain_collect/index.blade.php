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
		        <h1 class="m-0">Master Collection</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Master Collection</li>
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

                <div class="row" style="padding-left:20px">
                    <div class="col-md-3">
                      <button type="button" class="btn btn-primary" id="btnExport" style="white-space: nowrap;">
                          Export TXT
                      </button>
                    </div>
                </div>

                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="text-align: center">No</th>						
                            <th scope="col" style="text-align: center">Sub</th>
                            <th scope="col" style="text-align: center">No Item</th>
                            <th scope="col" style="text-align: center">Nama Barang</th>
                            <th scope="col" style="text-align: center">Barcode</th>
                            <th scope="col" style="text-align: center">Kemasan</th>
                            <th scope="col" style="text-align: right">LPH</th>
                            <th scope="col" style="text-align: right">Stok Toko</th>
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
    var dataTable = $('.datatable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: true,
        'scrollY': '400px',
        // 'scrollX': true,
        "order": [[ 0, "asc" ]],
        ajax: 
        {
            url: '{{ route('get-collect') }}'
        },
        columns: 
        [
            {  data: 'DT_RowIndex', orderable: false, searchable: false },
            {data: 'SUB', name: 'SUB'},
            {data: 'KDBAR', name: 'KDBAR'},
            {data: 'NA_BRG', name: 'NA_BRG'},
            {data: 'BARCODE', name: 'BARCODE'},
            {data: 'KET_KEM', name: 'KET_KEM'},
            {data: 'LPH', name: 'LPH', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
            {data: 'AK00', name: 'AK00', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
        ],

        columnDefs: [
            
            {
                "className": "dt-center", 
                "targets": [0,1,2,3,4,5]
            },
            {
                "className": "dt-right", 
                "targets": [6,7]
            }
        ],
        dom: "<'row'<'col-md-6'><'col-md-6'>>" +
              "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
              "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
        stateSave:true,
    });

    $('#btnExport').on('click', function(){
      window.location.href = '{{ route("collect.export-txt") }}';
    });  
  });
	
</script>
@endsection