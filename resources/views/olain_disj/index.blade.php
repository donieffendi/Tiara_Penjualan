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
		        <h1 class="m-0">Diskon Jenjang</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Diskon Jenjang</li>
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
				     		            <th scope="col" style="text-align: center">-</th>							
                            <th scope="col" style="text-align: center">KD_BRG</th>
                            <th scope="col" style="text-align: center">NA_BRG</th>
                            <th scope="col" style="text-align: right">QTY1</th>
                            <th scope="col" style="text-align: right">QTY2</th>
                            <th scope="col" style="text-align: right">QTY3</th>
                            <th scope="col" style="text-align: right">QTY4</th>
                            <th scope="col" style="text-align: right">DIS1</th>
                            <th scope="col" style="text-align: right">DIS2</th>
                            <th scope="col" style="text-align: right">DIS3</th>
                            <th scope="col" style="text-align: right">DIS4</th>
                            <th scope="col" style="text-align: right">TH1</th>
                            <th scope="col" style="text-align: right">TH2</th>
                            <th scope="col" style="text-align: right">TH3</th>
                            <th scope="col" style="text-align: right">TH4</th>
                            <th scope="col" style="text-align: center">KELIPATAN</th>
                            <th scope="col" style="text-align: center">TGL_MULAI</th>
                            <th scope="col" style="text-align: center">TGL_SELESAI</th>
                            <th scope="col" style="text-align: center">JAM_MULAI</th>
                            <th scope="col" style="text-align: center">JAM_SELESAI</th>
                            <th scope="col" style="text-align: center">AKTIF</th>
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
        ajax: 
        {
            url: '{{ route('get-disj') }}'
        },
        columns: 
        [
            {  data: 'DT_RowIndex', orderable: false, searchable: false },
            {data: 'action',name: 'action'},
            {data: 'KD_BRG', name: 'KD_BRG'},
            {data: 'NA_BRG', name: 'NA_BRG'},		
            {data: 'QTY1', name: 'QTY1', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            {data: 'QTY2', name: 'QTY2', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            {data: 'QTY3', name: 'QTY3', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            {data: 'QTY4', name: 'QTY4', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            {data: 'DIS1', name: 'DIS1', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            {data: 'DIS2', name: 'DIS2', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            {data: 'DIS3', name: 'DIS3', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            {data: 'DIS4', name: 'DIS4', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            {data: 'TH1', name: 'TH1', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            {data: 'TH2', name: 'TH2', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            {data: 'TH3', name: 'TH3', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            {data: 'TH4', name: 'TH4', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            {data: 'KELIPATAN', name: 'KELIPATAN', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            {data: 'TGL_MULAI', name: 'TGL_MULAI', render: $.fn.dataTable.render.moment('DD-MM-YYYY')},
            {data: 'TGL_SELESAI', name: 'TGL_SELESAI', render: $.fn.dataTable.render.moment('DD-MM-YYYY')},
            {data: 'JAM_MULAI', name: 'JAM_MULAI', render: $.fn.dataTable.render.moment('HH:mm:ss')},
            {data: 'JAM_SELESAI', name: 'JAM_SELESAI', render: $.fn.dataTable.render.moment('HH:mm:ss')},
            {
                data: 'AKTIF',
                name: 'AKTIF',
                render: function(data, type, row, meta) {
                    if (row['AKTIF'] == "0") {
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
                "targets": [0,1,2,3,17,18,19,20,21]
            },
            {
                "className": "dt-right", 
                "targets": [4,5,6,7,8,9,10,11,12,13,14,15,16]
            }
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