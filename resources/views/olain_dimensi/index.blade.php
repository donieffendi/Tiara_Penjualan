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
		        <h1 class="m-0">Master Dimensi dan Susun Barang</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Master Dimensi dan Susun Barang</li>
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
                <form id="formImportRak" enctype="multipart/form-data">
                  @csrf
                  <input type="file" name="file" id="fileRak" accept=".xls,.xlsx" hidden>
                  <div class="form-group row" style="padding-left:20px">
                      <div class="col-md-2">
                        <label for="susun">Susun :</label>
                        <select name="susun" id="susun" class="form-control" required>
                          <option value="SEMUA" {{ session()->get('filter_susun') == 'SEMUA' ? 'selected' : '' }}>SEMUA</option>
                          <option value="ADA" {{ session()->get('filter_susun') == 'ADA' ? 'selected' : '' }}>ADA</option>
                          <option value="KOSONG" {{ session()->get('filter_susun') == 'KOSONG' ? 'selected' : '' }}>KOSONG</option>
                        </select>
                      </div>

                      <div class="col-md-2">
                        <label for="dimensi">Dimensi :</label>
                        <select name="dimensi" id="dimensi" class="form-control" required>
                          <option value="SEMUA" {{ session()->get('filter_dimensi') == 'SEMUA' ? 'selected' : '' }}>SEMUA</option>
                          <option value="ADA" {{ session()->get('filter_dimensi') == 'ADA' ? 'selected' : '' }}>ADA</option>
                          <option value="KOSONG" {{ session()->get('filter_dimensi') == 'KOSONG' ? 'selected' : '' }}>KOSONG</option>
                        </select>
                      </div>
                  </div>

                  <div class="row" style="padding-left:20px">
                      <div class="col-md-3">
                        <button type="button" class="btn btn-primary" id="btnTampil" style="white-space: nowrap;">
                            Tampilkan
                        </button>
                        <button type="button" class="btn btn-success" id="btnImport" style="white-space: nowrap;">
                            Import Excel No. Rak
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
                              <th scope="col" style="text-align: center">Rak Toko</th>
                              <th scope="col" style="text-align: center">P(cm)</th>
                              <th scope="col" style="text-align: center">L(cm)</th>
                              <th scope="col" style="text-align: center">T(cm)</th>
                              <th scope="col" style="text-align: center">Muka</th>
                              <th scope="col" style="text-align: center">Susun</th>
                              <th scope="col" style="text-align: center">DTR 1 MUKA</th>
                              <th scope="col" style="text-align: center">DTR</th>
                              <th scope="col" style="text-align: center">DTR2</th>
                              <th scope="col" style="text-align: center">DTR2_BORONG</th>
                              <th scope="col" style="text-align: center">>>DTR2</th>
                              <th scope="col" style="text-align: center">Tgl Mulai DTR2</th>
                              <th scope="col" style="text-align: center">Tgl Selesai DTR2</th>
                              <th scope="col" style="text-align: center">DTR Khusus</th>
                              <th scope="col" style="text-align: center">Tanda</th>
                              <th scope="col" style="text-align: center">SR Min</th>
                              <th scope="col" style="text-align: center">SR Max</th>
                          </tr>				
                      </thead>
      
                      <tbody>
                          
                      </tbody> 
                  </table>
                </form>
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
            paging: true,
            'scrollY': '400px',
            'scrollX': true,
            "order": [[ 0, "asc" ]],
            deferLoading: 0,
            ajax: {
                url: '{{ route('get-dimensi') }}',
                data: function (d) {
                    d.susun = $('#susun').val();
                    d.dimensi = $('#dimensi').val();
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
                {data: 'RAK_TOKO', name: 'RAK_TOKO'},
                {data: 'PANJANG', name: 'PANJANG', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'LEBAR', name: 'LEBAR', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'TINGGI', name: 'TINGGI', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'MUKA', name: 'MUKA', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'SUSUN', name: 'SUSUN', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'DTR_1M', name: 'DTR_1M', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'DTR', name: 'DTR', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'DTR2', name: 'DTR2', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'DTR_MANUAL', name: 'DTR_MANUAL', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'EX_DTR2', name: 'EX_DTR2', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'TGL_AW_DTR2', name: 'TGL_AW_DTR2', render: $.fn.dataTable.render.moment( 'DD-MM-YYYY' )},
                {data: 'TGL_AK_DTR2', name: 'TGL_AK_DTR2', render: $.fn.dataTable.render.moment( 'DD-MM-YYYY' )},
                {data: 'TYPE', name: 'TYPE'},
                {data: 'TANDA', name: 'TANDA'},
                {data: 'SMIN_TK', name: 'SMIN_TK', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'SMAX_TK', name: 'SMAX_TK', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
            ],

            columnDefs: [
                {
                  "className": "dt-center", 
                  "targets": [0,1,2,3,4,5,6,7,18,19,20,21]
                },
            ],

            dom: "<'row'<'col-md-6'><'col-md-6'>>" +  // <-- tambahkan B untuk tombol di kiri
                  "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                  "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
            stateSave:true,

        });

        // tombol tampil ditekan
        $('#btnTampil').on('click', function() {
            dataTable.ajax.reload();
        });

        $('#btnImport').on('click', function(){
            $('#fileRak').click();
        });

        $('#fileRak').on('change', function(){
            let formData = new FormData($('#formImportRak')[0]);

            Swal.fire({
                title: 'Import No Rak?',
                text: 'Pastikan format Excel benar (SUBITEM/BARCODE | NOMOR RAK)',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("dimensi.import-rak") }}',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res){
                            Swal.fire('Sukses', res.message, 'success');
                            $('#datatable').DataTable().ajax.reload();
                        },
                        error: function(err){
                            Swal.fire('Error', err.responseJSON.message, 'error');
                        }
                    });
                }
            });
        });
    });

	
</script>
@endsection