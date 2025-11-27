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
		        <h1 class="m-0">Master Import SQL</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Master Import SQL</li>
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
                <div class="form-group row">
                  <div class="col-md-1">
                      <select name="jns" id="jns" class="form-control jns" style="width: 150px">
                          <option value="">--Pilih Jenis--</option>
                          @foreach($jns as $jnsD)
                              <option value="{{$jnsD->KODE}}"  {{ (session()->get('filter_jns') == $jnsD->KODE) ? 'selected' : '' }}>{{$jnsD->KODE}}</option>
                          @endforeach
                      </select>
                  </div>
                  <div class="col-md-2">
                      <input class="form-control ket" id="ket" name="ket"
                      type="text" autocomplete="off" value="{{ session()->get('ket') }}"> 
                  </div>
                  <div class="col-md-2">
                      <input class="form-control jenis" id="jenis" name="jenis"
                      type="text" autocomplete="off" value="{{ session()->get('jenis') }}"> 
                  </div>
                  <div class="col-md-3">
                      <button type="button" class="btn btn-danger" id="btnProses" style="white-space: nowrap;">
                          Proses
                      </button>
                    </div>
                </div>



                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
											
                            <th scope="col" style="text-align: center">No</th>
				     		            <th scope="col" style="text-align: center">-</th>							
                            <th scope="col" style="text-align: center">No Usulan</th>
                            <th scope="col" style="text-align: center">No File</th>
                            <th scope="col" style="text-align: center">Tgl Posted</th>
                            <th scope="col" style="text-align: center">No Supplier</th>
							              <th scope="col" style="text-align: center">Sub Item</th>
                            <th scope="col" style="text-align: center">Posted</th>
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
                url: '{{ route('get-import-sql') }}'
            },
            columns: 
            [
                {data: 'DT_RowIndex', orderable: false, searchable: false },
				        {data: 'action', name: 'action'},
				        {data: 'NO_BUKTI', name: 'NO_BUKTI'},
                {data: 'NA_FILE', name: 'NA_FILE'},
                {data: 'TGL', name: 'TGL'},				
                {data: 'KODES', name: 'KODES'},
				        {data: 'KD_BRG', name: 'KD_BRG'},
                {data: 'PROSES', name: 'PROSES'},
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

        $('#jns').change(function() {
          var kode = $(this).val();
          if (kode) {
              $.ajax({
                  url: '{{ route("get-dataubahno", ":kode") }}'.replace(':kode', kode),
                  type: 'GET',
                  dataType: 'json',
                  success: function(data) {
                      $('#ket').val(data.ket);
                      $('#jenis').val(data.jns);
                  },
                  error: function(xhr, status, error) {
                      Swal.fire({
                          icon: 'error',
                          title: 'Gagal Mengambil Data',
                          text: 'Terjadi kesalahan saat mengambil data dari server.',
                          footer: '<small>Kode: ' + kode + '</small>',
                          confirmButtonColor: '#d33'
                      });
                  }
              });
          } else {
              $('#ket').val('');
              $('#jenis').val('');
          }
        });

        $('#btnProses').click(function() {
          var jns = $('#jns').val();

          if (!jns) {
              Swal.fire({
                  icon: 'warning',
                  title: 'Peringatan',
                  text: 'Silakan pilih jenis terlebih dahulu!',
              });
              return;
          }

          Swal.fire({
              title: 'Yakin ingin memproses data?',
              text: "Proses ini akan menjalankan update dan prosedur SQL.",
              icon: 'question',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Ya, Proses Sekarang!',
              cancelButtonText: 'Batal'
          }).then((result) => {
              if (result.isConfirmed) {
                  $.ajax({
                      url: '{{ route("proses-import") }}',
                      type: 'POST',
                      data: {
                          _token: '{{ csrf_token() }}',
                          jns: jns
                      },
                      beforeSend: function() {
                          Swal.fire({
                              title: 'Memproses...',
                              text: 'Silakan tunggu sebentar',
                              allowOutsideClick: false,
                              didOpen: () => {
                                  Swal.showLoading();
                              }
                          });
                      },
                      success: function(response) {
                          if (response.success) {
                              Swal.fire({
                                  icon: 'success',
                                  title: 'Sukses!',
                                  text: response.message,
                                  timer: 2000,
                                  showConfirmButton: false
                              });
                              $('.datatable').DataTable().ajax.reload(null, false);
                          } else {
                              Swal.fire({
                                  icon: 'error',
                                  title: 'Gagal',
                                  text: response.message
                              });
                          }
                      },
                      error: function(xhr) {
                          Swal.fire({
                              icon: 'error',
                              title: 'Error Server',
                              text: xhr.responseText
                          });
                      }
                  });
              }
          });
        });     
        // $("div.test_btn").html('<a class="btn btn-lg btn-md btn-success" href="{{url('sup/edit?idx=0&tipx=new')}}"> <i class="fas fa-plus fa-sm md-3" ></i></a');
    
     });
	
</script>
@endsection