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
		        <h1 class="m-0">Data Barang Food Centre</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Data Barang Food Centre</li>
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
                            <th scope="col" style="text-align: center">Kode</th>
                            <th scope="col" style="text-align: center">Nama</th>
                            <th scope="col" style="text-align: right">Harga Jual</th>
                            <th scope="col" style="text-align: right">Harga Beli</th>
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
            "order": [[ 0, "asc" ]],
            ajax: 
            {
                url: '{{ route('get-dbrg') }}'
            },
            columns: 
            [
                {  data: 'DT_RowIndex', orderable: false, searchable: false },
				        {data: 'action',name: 'action'},
                {data: 'KD_BRG', name: 'KD_BRG'},
                {data: 'NA_BRG', name: 'NA_BRG'},
                {data: 'HJ', name: 'HJ', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},				
                {data: 'HB', name: 'HB', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            ],

            columnDefs: [
                {
                    "className": "dt-center", 
                    "targets": [0,1,2,3]
                },
                {
                    "className": "dt-right", 
                    "targets": [4,5]
                }
            ],
            dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                  "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                  "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
            stateSave:true,
        });
        
         $("div.test_btn").html(`<a class="btn btn-lg btn-md btn-success" href="{{url('dbrg/edit?idx=0&tipx=new')}}"> 
                                  <i class="fas fa-plus fa-sm md-3" ></i>
                                </a>
                                <button type="button" id="btnPrint" class="btn btn-primary btn-md">
                                  <i class="fas fa-print"></i> Print
                                </button>
                              `);
        
        $('#btnPrint').on('click', function() {
          // ambil nilai pencarian aktif di DataTables
          let searchValue = $('.dataTables_filter input').val();

          Swal.fire({
              title: 'Cetak Daftar Barang Food Center ?',
              text: "Akan mencetak semua data yang sesuai filter pencarian.",
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: 'Ya, Cetak!',
              cancelButtonText: 'Batal',
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33'
          }).then((result) => {
              if (result.isConfirmed) {
                  // buka Jasper dengan parameter search
                  window.open(`{{ url('dbrg2/cetak') }}?search=${encodeURIComponent(searchValue)}`, '_blank');
              }
          });
        });
    });
	
</script>
@endsection