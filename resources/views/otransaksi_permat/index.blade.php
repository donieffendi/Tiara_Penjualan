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
		        <h1 class="m-0">Perubahan Masa Tarik</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Perubahan Masa Tarik</li>
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
                <div class="form-group row" style="padding-left:20px">
                    <label><strong>Sub :</strong></label>
                    <div class="col-md-1">
                        <input class="form-control sub" id="sub" name="sub"
                        type="text" autocomplete="off" value="{{ session()->get('sub') }}"> 
                    </div>
                    <div class="col-md-3">
                      <button type="button" class="btn btn-warning" id="btnTampil" style="white-space: nowrap;">
                          Tampilkan
                      </button>
                    </div>
                </div>
                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
											
                            <th scope="col" style="text-align: center">No</th>
				     		            <th scope="col" style="text-align: center">-</th>							
                            <th scope="col" style="text-align: center">Sub Item</th>
                            <th scope="col" style="text-align: center">Nama Barang</th>
                            <th scope="col" style="text-align: center">Ukuran</th>
                            <th scope="col" style="text-align: center">Kemasan</th>
							              <th scope="col" style="text-align: center">Type</th>							              
                            <th scope="col" style="text-align: right">Tarik</th>
                            <th scope="col" style="text-align: right">Masa Exp</th>
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
            paging: false,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax: {
                url: '{{ route('get-permat') }}',
                data: function (d) {
                    d.sub = $('#sub').val(); 
                }
            },
            columns: 
            [
                {data: 'DT_RowIndex', orderable: false, searchable: false },
				        {data: 'action', name: 'action'},
				        {data: 'KD_BRG', name: 'KD_BRG'},
                {data: 'NA_BRG', name: 'NA_BRG'},
                {data: 'KET_UK', name: 'KET_UK'},				
                {data: 'KET_KEM', name: 'KET_KEM'},
				        {data: 'TARIK_TIPE', name: 'TARIK_TIPE'},
                {data: 'TARIK', name: 'TARIK', $render: $.fn.dataTable.render.number(',','.', 0,'')},
                {data: 'MASA_EXP', name: 'MASA_EXP', $render: $.fn.dataTable.render.number(',','.', 0,'')},
            ],

            columnDefs: [
                {
                    "className": "dt-right", 
                    "targets": [7,8]
                }
            ],

           dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'B><'col-md-4'f>>" +
                "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
				    buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel"></i> Export Excel',
                    className: 'btn btn-success btn-md',
                    title: 'Perubahan Masa Tarik',
                    exportOptions: {
                        columns: ':visible:not(:first-child)' // abaikan kolom No dan Action
                    }
                }
            ],
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