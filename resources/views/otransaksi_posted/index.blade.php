@extends('layouts.main')
@section('styles')
<!-- <link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}"> -->
<link rel="stylesheet" href="{{asset('foxie_js_css/jquery.dataTables.min.css')}}" />

@endsection

<style>
    .card {
        padding: 5px 10px !important;
    }

    .table thead {
        background-color: #8a2be2;
        color: #ffff;
    }

    .datatable tbody td {
        padding: 5px !important;
    }

    .datatable {
        border-right: solid 2px #000;
        border-left: solid 2px #000;
    }
	
    .table tbody:nth-child(2) {
        background-color: #d3ffce;
    }

    .btn-secondary {
        background-color: #42047e !important;
    }
    
    th { font-size: 13px; }
    td { font-size: 13px; }
</style>

@section('content')
<div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Cetak Slip Bank</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Cetak Slip Bank</li>
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
                            <th scope="col" style="text-align: center">#</th>
							              <th scope="col" style="text-align: center">Pilih</th>							
                            <th scope="col" style="text-align: left">Kodecus</th>
                            <th scope="col" style="text-align: center">Pemasaran</th>
                            <th scope="col" style="text-align: left">Toko</th>
                            <th scope="col" style="text-align: left">Flag</th>
                            <th scope="col" style="text-align: left">No BG</th>
                            <th scope="col" style="text-align: left">Bank</th>
                            <th scope="col" style="text-align: right">Jumlah</th>
                            <th scope="col" style="text-align: right">Jtempo</th>
                            <th scope="col" style="text-align: left">Cair Bank</th>
                            <th scope="col" style="text-align: left">Biaya</th>
                            <th scope="col" style="text-align: left">Tanggal setor</th>
                        </tr>
                    </thead>
    
                    <tbody>
                    </tbody> 
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('javascripts')
<script>
  $(document).ready(function() {
        var dataTable = $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: true,
			'scrollX': true,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax: 
            {
                url: "{{ route('get-cetakslip') }}"
            },
            columns: 
            [
                {data: 'DT_RowIndex', orderable: false, searchable: false },
				        {data: 'action', name: 'action'},
                {data: 'KODEC', name: 'KODEC'},
                {data: 'WILAYAH1', name: 'WILAYAH1'},
                {data: 'KODEC', name: 'KODEC'},
                {data: 'FLAG', name: 'FLAG'},
                {data: 'BANK', name: 'BANK'},		
                {data: 'NO_CHBG', name: 'NO_CHBG'},		
                {data: 'TOTAL', name: 'TOTAL', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},	
                {data: 'JTEMPO', name: 'JTEMPO'},		
                {data: 'BANK_SETOR', name: 'BANK_SETOR'},		
                {data: 'BIAYA', name: 'BIAYA', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
                {data: 'TGL_SETOR', name: 'TGL_SETOR'},
            ],
            columnDefs: 
            [
                {
                    "className": "dt-center", 
                    "targets": 0
                },			
                {
                    "className": "dt-right", 
                    "targets": [8,11]
                },			
                {
                  targets: [9,12],
                  render: $.fn.dataTable.render.moment( 'DD-MM-YYYY' )
                },
            ],
            lengthMenu: 
            [
                [8, 10, 20, 50, 100, -1],
                [8, 10, 20, 50, 100, "All"]
            ],
            dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
        });
        @if (Auth::user()->divisi!="pembelian")
          $("div.test_btn").html('<a class="btn btn-lg btn-md btn-success" href="{{url('surats/create')}}"> <i class="fas fa-plus fa-sm md-3" ></i></a');
        @endif
    });
</script>
@endsection