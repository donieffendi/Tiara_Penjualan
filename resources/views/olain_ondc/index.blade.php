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
                <div class="form-group row" style="padding-left:20px">
                    <div class="col-md-2">
                      <label for="ondc">Order Ke DC</label>
                      <select name="ondc" id="ondc" class="form-control" required>
                        <option value="SEMUA" {{ session()->get('filter_ondc') == 'SEMUA' ? 'selected' : '' }}>SEMUA</option>
                        <option value="YA" {{ session()->get('filter_ondc') == 'YA' ? 'selected' : '' }}>YA</option>
                        <option value="TIDAK" {{ session()->get('filter_ondc') == 'TIDAK' ? 'selected' : '' }}>TIDAK</option>
                      </select>
                    </div>
                </div>

                <div class="form-group row" style="padding-left:20px">
                  <div class="col-md-2">
                    <label for="sub1x">Sub :</label>
											<input class="form-control sub1x" id="sub1x" name="sub1x"
											type="text" autocomplete="off" value="{{ session()->get('filter_sub1x') }}">
                  </div>
                  <div class="col-md-2">
                    <label for="sub2x">s.d.</label>
                    <input class="form-control date sub2x" id="sub2x" name="sub2x"
                    type="text" autocomplete="off" value="{{ session()->get('filter_sub2x') }}">
                  </div>
                </div>

                <div class="form-group row" style="padding-left:20px">
                  <div class="col-md-2">
                    <label for="sup1x">Supplier :</label>
                    <input class="form-control" id="sup1x" name="sup1x"
                    type="text" autocomplete="off" value="{{ session()->get('filter_sup1x') }}">
                  </div>
                  <div class="col-md-2">
                    <label for="sup2x">s.d.</label>
                    <input class="form-control" id="sup2x" name="sup2x"
                    type="text" autocomplete="off" value="{{ session()->get('filter_sup2x') }}">
                  </div>
                </div>

                <div class="row" style="padding-left:20px">
                    <div class="col-md-3">
                      <button type="button" class="btn btn-primary" id="btnTampil" style="white-space: nowrap;">
                          Tampilkan
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script>
  $(document).ready(function() {
        var dataTable = $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: true,
            paging: false,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            deferLoading: 0,
            ajax: {
                url: '{{ route('get-ondc') }}',
                data: function (d) {
                    d.ondc = $('#ondc').val();
                    d.sub1x = $('#sub1x').val();
                    d.sub2x = $('#sub2x').val();
                    d.sup1x = $('#sup1x').val();
                    d.sup2x = $('#sup2x').val();
                }
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

            dom: "<'row'<'col-md-6'B><'col-md-6'>>" +  // <-- tambahkan B untuk tombol di kiri
                  "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                  "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
				    
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-md'
                }
            ],
            stateSave:true,

        });

        // tombol tampil ditekan
        $('#btnTampil').on('click', function() {
            dataTable.ajax.reload();
        });
    });

	
</script>
@endsection