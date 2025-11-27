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
		        <h1 class="m-0">Keperluan Barang Dan Jasa</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Keperluan Barang Dan Jasa</li>
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
                            <th scope="col" style="text-align: center">No Bukti</th>
                            <th scope="col" style="text-align: center">Tanggal</th>
                            <th scope="col" style="text-align: center">Dept</th>
                            <th scope="col" style="text-align: center">Nama</th>
							              <th scope="col" style="text-align: center">User</th>
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
        'scrollY': '400px',
        "order": [[ 0, "asc" ]],
        ajax: 
        {
            url: '{{ route('get-brg-jasa') }}'
        },
        columns: 
        [
            {  data: 'DT_RowIndex', orderable: false, searchable: false },
            {data: 'action', name: 'action'},
            {data: 'NO_BUKTI', name: 'NO_BUKTI'},
            {data: 'TGL', name: 'TGL'},
            {data: 'KD_DEPT', name: 'KD_DEPT'},				
            {data: 'DEPT', name: 'DEPT'},
            {data: 'USRNM', name: 'USRNM'},
        ],

        columnDefs: [
            {
                "className": "dt-center", 
                "targets": 0
            }
        ],
        dom: "<'row'<'col-md-6'><'col-md-6'>>" +
              "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
              "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
        stateSave:true,
    });
    
    $("div.test_btn").html(`<a class="btn btn-lg btn-md btn-success" href="{{url('brg-jasa/edit?idx=0&tipx=new')}}">
                              <i class="fas fa-plus fa-sm md-3" ></i></a>
                            <button type="button" id="btnPrintLap" class="btn btn-warning btn-md">
                              <i class="fas fa-print"></i> Print Laporan
                            </button>
                          `);

    $('#btnPrintLap').on('click', function() {
        Swal.fire({
            title: 'Cetak Laporan Barang & Jasa?',
            text: "Laporan akan dibuka di tab baru.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Cetak!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                // buka jasper report di tab baru tanpa filter
                window.open(`{{ url('brg-jasa/printlap') }}`, '_blank');
            }
        });
    });
  });
	
</script>
@endsection