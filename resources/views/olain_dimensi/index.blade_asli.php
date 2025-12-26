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
		        <h1 class="m-0">Barang Order Ke DC</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Barang Order Ke DC</li>
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
                            <th scope="col" style="text-align: center">No</th>
				     		            <th scope="col" style="text-align: center">Sub</th>							
                            <th scope="col" style="text-align: center">No Item</th>
                            <th scope="col" style="text-align: center">Nama Barang</th>
                            <th scope="col" style="text-align: center">Ukuran</th>
                            <th scope="col" style="text-align: center">Kemasan</th>
                            <th scope="col" style="text-align: center">LPH</th>
                            <th scope="col" style="text-align: center">DTR</th>
                            <th scope="col" style="text-align: center">DTR2</th>
                            <th scope="col" style="text-align: center">Supp</th>
                            <th scope="col" style="text-align: center">Order Ke DC</th>
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
        'scrollX': true,
        "order": [[ 0, "asc" ]],
        deferloading: 0,
        ajax: 
        {
            url: '{{ route('get-ondc') }}'
        },
        columns: 
        [
            {  data: 'DT_RowIndex', orderable: false, searchable: false },
            {data: 'SUB', name: 'SUB'},
            {data: 'KDBAR', name: 'KDBAR'},
            {data: 'NA_BRG', name: 'NA_BRG'},
            {data: 'KET_UK', name: 'KET_UK'},
            {data: 'KET_KEM', name: 'KET_KEM'},
            {data: 'LPH', name: 'LPH'},
            {data: 'DTR', name: 'DTR'},
            {data: 'DTR2', name: 'DTR2'},
            {data: 'SUPP', name: 'SUPP'},
            {
                data: 'ON_DC',
                name: 'ON_DC',
                render: function(data, type, row, meta) {
                    if (row['ON_DC'] == "0") {
                        return '';
                    } else {
                        return '<input type="checkbox" checked style="pointer-events: none; scale: 1.5">';
                    }
                }
            },
        ],

        columnDefs: [
            
            {
                "className": "dt-center", 
                "targets": [0,1,2,3,4,5,6,7,8,9,10]
            },
        ],
        dom: "<'row'<'col-md-6'><'col-md-6'>>" +
              "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
              "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
        stateSave:true,
    });  
  });

  function prosesDisj(url) {
      if (!confirm('Proses data ini?')) return;

      $.ajax({
          url: url,
          type: 'POST',
          data: {
              _token: '{{ csrf_token() }}'
          },
          success: function(res) {
              if (res.success) {
                  alert(res.message);
                  $('#datatable').DataTable().ajax.reload(null, false);
              }
          },
          error: function() {
              alert('Gagal memproses data');
          }
      });
  }
	
</script>
@endsection