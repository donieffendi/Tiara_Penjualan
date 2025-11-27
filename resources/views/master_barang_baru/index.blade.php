@extends('layouts.plain')
@section('styles')
<link rel="stylesheet" href="{{asset('foxie_js_css/jquery.dataTables.min.css')}}" />

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
		        <h1 class="m-0">Master Pengajuan Barang Baru</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Master Pengajuan Barang Baru</li>
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
                            <th scope="col" style="text-align: center">Sub</th>
                            <th scope="col" style="text-align: center">Kode</th>
                            <th scope="col" style="text-align: center">Nama</th>
                            <th scope="col" style="text-align: center">Ukuran</th>
                            <th scope="col" style="text-align: center">Kemasan</th>
                            <th scope="col" style="text-align: center">Suplier</th>
                            <th scope="col" style="text-align: center">Harga yg ditawarkan</th>
                            <th scope="col" style="text-align: center">Dis c.1</th>
                            <th scope="col" style="text-align: center">Dis c.2</th>
                            <th scope="col" style="text-align: center">Dis c.3</th>
                            <th scope="col" style="text-align: center">LPH</th>
                            <th scope="col" style="text-align: center">HSO</th>
                            <th scope="col" style="text-align: center">LPH</th>
                            <th scope="col" style="text-align: center">DTR</th>
                            <th scope="col" style="text-align: center">Kd lak</th>
                            <th scope="col" style="text-align: center">Typ</th>
                            <th scope="col" style="text-align: center">Min Toko</th>
                            <th scope="col" style="text-align: center">Max Toko</th>
                            <th scope="col" style="text-align: center">Min Gdg</th>
                            <th scope="col" style="text-align: center">Max Gdg</th>
                            <th scope="col" style="text-align: center">HJ_GZ</th>
                            <th scope="col" style="text-align: center">HB_GZ</th>
                            <th scope="col" style="text-align: center">KLK</th>
                            <th scope="col" style="text-align: center">Tarik</th>
                            <th scope="col" style="text-align: center">Masa Exp</th>
                            <th scope="col" style="text-align: center">Kem Pabrik</th>
                            <th scope="col" style="text-align: center">KMP 1</th>
                            <th scope="col" style="text-align: center">KMP 2</th>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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
                url: '{{ route('get-brg-baru') }}'
            },
            columns: 
            [
                {  data: 'DT_RowIndex', orderable: false, searchable: false },
				        {data: 'action', name: 'action'},
				        {data: 'SUB', name: 'SUB'},
                {data: 'KD_BRG', name: 'KD_BRG'},
                {data: 'NA_BRG', name: 'NA_BRG'},				
                {data: 'KET_UK', name: 'KET_UK'},
                {data: 'KET_KEM', name: 'KET_KEM'},
                {data: 'NAMAS', name: 'NAMAS'},
                {data: 'HARGA', name: 'HARGA', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
                {data: 'D1', name: 'D1', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'D2', name: 'D2', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'D3', name: 'D3', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'LPH', name: 'LPH', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'HARGA', name: 'HARGA', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
                {data: 'LPH_LALU', name: 'LPH_LALU', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'DTR', name: 'DTR', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'KDLAKU', name: 'KDLAKU'},
                {data: 'TYPE', name: 'TYPE'},
                {data: 'SMIN', name: 'SMIN', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
                {data: 'SMAX', name: 'SMAX', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
                {data: 'SRMIN', name: 'SRMIN', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'SRMAX', name: 'SRMAX', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'HJ', name: 'HJ', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
                {data: 'HB', name: 'HB', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
                {data: 'KLK', name: 'KLK'},
                {data: 'TARIK', name: 'TARIK'},
                {data: 'MASA_EXP', name: 'MASA_EXP'},
                {data: 'KMP', name: 'KMP'},
                {data: 'KMP1', name: 'KMP1'},
                {data: 'KMP2', name: 'KMP2'},
            ],

            columnDefs: [
                {
                    "className": "dt-center", 
                    "targets": 0
                },
                {
                    "className": "dt-right", 
                    "targets": [8,9,10,11,12,13,14]
                }
            ],
            dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
				    stateSave:true,
        });
        
        $("div.test_btn").html('<a class="btn btn-lg btn-md btn-success" href="{{url('brg-baru/edit?idx=0&tipx=new')}}"> <i class="fas fa-plus fa-sm md-3" ></i></a');
    });
	
</script>
@endsection