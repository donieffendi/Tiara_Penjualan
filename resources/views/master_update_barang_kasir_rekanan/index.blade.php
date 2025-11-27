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
		        <h1 class="m-0">Master Usulan Barang Kasir Rekanan</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Master Usulan Barang Kasir Rekanan</li>
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


                  <!-- Filter Sub -->
                <div class="form-group row" style="padding-left:20px">
                    <label><strong>Rekanan:</strong></label>
                    <div class="col-md-2">
                      <select name="rekan" id="rekan" class="form-control" required>
													<option value="">Pilih Rekanan</option>
													@foreach ($rekan as $rekan)
														<option value="{{ $rekan->NAMA }}" {{ session()->get('filter_rekan') == $rekan->NAMA ? 'selected' : '' }}>
															{{ $rekan->NAMA }}
														</option>
													@endforeach
												</select>
                    </div>
                    <!-- CEK -->
                      <div class="col-md-1" align="right">
                          <input type="checkbox" class="form-check-input" id="CEK" name="CEK" value="1">
                          <label for="CEK">Cek</label>
                      </div>
                      <div class="col-md-1">
                          <input type="checkbox" class="form-check-input" id="CEK" name="CEK" value="0">
                          <label for="CEK">UnChek</label>
                      </div>
                </div>

                <div class="form-group row" style="padding-left:20px">
                    <label><strong>Sub Item:</strong></label>
                    <div class="col-md-2">
                      <input class="form-control SUB" id="SUB" name="SUB"
                            type="text" autocomplete="off" value="" placeholder="Masukkan Kd Barang"> 
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-warning" id="btnTampil" style="white-space: nowrap;">
                            Tampilkan
                        </button>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger" id="btnProses" style="white-space: nowrap;">
                            Prosses
                        </button>
                    </div>
                </div>
                <!--  -->

                <div class="form-group row" style="padding-left:20px">
                    <label><strong>Copy Data Rekan:</strong></label>
                    <div class="col-md-2">
                      <select name="copy" id="copy" class="form-control" required>
													<option value="">Pilih Rekanan</option>
													@foreach ($copy as $copy)
														<option value="{{ $copy->NAMA }}" {{ session()->get('filter_copy') == $copy->NAMA ? 'selected' : '' }}>
															{{ $copy->NAMA }}
														</option>
													@endforeach
												</select>
                    </div>

                   <div class="col-md-1">
                        <button type="button" class="btn btn-primary" id="btnTampil" style="white-space: nowrap;">
                            Ambil Data
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
                            <th scope="col" style="text-align: center">Kemasan</th>
                            <th scope="col" style="text-align: center">HB</th>
							              <th scope="col" style="text-align: center">Jual</th>
							              <th scope="col" style="text-align: center">Cek</th>
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
                url: '{{ route('get-usl-brg-rekanan') }}'
            },
            columns: 
            [
                {data: 'DT_RowIndex', orderable: false, searchable: false },
				        {data: 'action', name: 'action'},
				        {data: 'KD_BRG', name: 'KD_BRG'},
                {data: 'NA_BRG', name: 'NA_BRG'},
                {data: 'KET_KEM', name: 'KET_KEM'},				
                {data: 'HB', name: 'HB'},
				        {data: 'JUAL', name: 'JUAL'},
				        {data: 'CEK', name: 'CEK'},
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
        
        // $("div.test_btn").html('<a class="btn btn-lg btn-md btn-success" href="{{url('sup/edit?idx=0&tipx=new')}}"> <i class="fas fa-plus fa-sm md-3" ></i></a');
    
     });
	
</script>
@endsection