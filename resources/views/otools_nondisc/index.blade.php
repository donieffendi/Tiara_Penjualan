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
		        <h1 class="m-0">Daftar Barang Non Poin</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Daftar Barang Non Poin</li>
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
                    <label><strong>Sub Item :</strong></label>
                    <div class="col-md-1">
                        <input class="form-control kd_brg" id="kd_brg" name="kd_brg"
                        type="text" autocomplete="off" value="{{ session()->get('kd_brg') }}"> 
                    </div>
                    <div class="col-md-3">
                        <input class="form-control na_brg" id="na_brg" name="na_brg"
                        type="text" autocomplete="off" value="{{ session()->get('na_brg') }}" readonly> 
                    </div>
                    <div class="col-md-3">
                      <button type="button" class="btn btn-warning" id="btnTambah" style="white-space: nowrap;">
                          Tambahkan
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
                            <th scope="col" style="text-align: center">Ket. Ukuran</th>
                            <th scope="col" style="text-align: center">Ket. Kemasan</th>
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
            paging: false,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax: {
                url: '{{ route('get-nondisc') }}',
            },
            columns: 
            [
                {data: 'DT_RowIndex', orderable: false, searchable: false },
				        {data: 'action', name: 'action'},
				        {data: 'KD_BRG', name: 'KD_BRG'},
                {data: 'NA_BRG', name: 'NA_BRG'},
                {data: 'KET_UK', name: 'KET_UK'},				
                {data: 'KET_KEM', name: 'KET_KEM'}
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

        $('#btnTambah').on('click', function () {
          let kdBrg = $('#kd_brg').val();
          let nabrg = $('#na_brg').val();

          if (!kdBrg) {
              Swal.fire('Peringatan', 'Sub Item harus diisi', 'warning');
              return;
          }

          Swal.fire({
              title: 'Konfirmasi',
              text: 'Tambahkan item ' + nabrg + ' ke daftar?',
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: 'Ya',
              cancelButtonText: 'Batal'
          }).then((result) => {
              if (result.isConfirmed) {

                  $.ajax({
                      url: '{{ route("nondisc.store") }}',
                      type: 'POST',
                      data: {
                          kd_brg: kdBrg,
                          _token: '{{ csrf_token() }}'
                      },
                      success: function (res) {
                          if (res.success) {
                              Swal.fire('Sukses', res.message, 'success');

                              // reset input
                              $('#kd_brg').val('').focus();

                              // reload datatable
                              $('.datatable').DataTable().ajax.reload(null, false);
                          } else {
                              Swal.fire('Gagal', res.message, 'error');
                          }
                      },
                      error: function (xhr) {
                          Swal.fire('Error', 'Terjadi kesalahan server', 'error');
                          console.error(xhr.responseText);
                      }
                  });

              }
          });
        });

        $('#kd_brg').on('change blur', function () {
          let kdBrg = $(this).val().trim();

          if (!kdBrg) {
              $('#na_brg').val('');
              return;
          }

          $.ajax({
              url: '{{ route("nondisc.lookup-brg") }}',
              type: 'GET',
              data: { kd_brg: kdBrg },
              success: function (res) {
                  if (res.success) {
                      $('#na_brg').val(res.na_brg);
                  } else {
                      $('#na_brg').val('');
                      Swal.fire('Info', res.message, 'info');
                  }
              },
              error: function () {
                  $('#na_brg').val('');
                  Swal.fire('Warning', 'Sub Item tidak ditemukan', 'warning');
              }
          });
        });

        $('#kd_brg').on('keypress', function(e){
          if(e.which === 13){
              $('#btnTambah').focus();
          }
        });
  });

  function deleteRow(url) {
    Swal.fire({
        title: 'Yakin hapus data?',
        text: 'Data Non Disc akan dihapus!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {

            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (res) {
                    Swal.fire('Berhasil!', 'Data berhasil dihapus.', 'success');

                    // reload datatable
                    $('.dataTable').DataTable().ajax.reload(null, false);
                },
                error: function () {
                    Swal.fire('Error!', 'Gagal menghapus data.', 'error');
                }
            });

        }
    });
  }

	
</script>
@endsection