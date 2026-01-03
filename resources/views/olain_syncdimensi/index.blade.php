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
		        <h1 class="m-0">Sinkron Dimensi Barang</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Sinkron Dimensi Barang</li>
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

                  <div class="row" style="padding-left:750px">
                      <div class="col-md-3">
                        <input type="file" id="fileDimensi" accept=".dbf" hidden>
                        <button type="button" class="btn btn-primary" id="btnSync" style="white-space: nowrap;">
                            Ambil Data
                        </button>
                      </div>
                  </div>
                  
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

    $('#btnSync').click(function () {

      $.get("{{ url('olain/syncdimensi/cek') }}", function (res) {

          if (res.exists) {
              Swal.fire({
                  title: 'Sinkron sudah pernah diproses',
                  text: 'Jam ' + res.jam + ' (' + res.user + '). Sinkron ulang?',
                  icon: 'question',
                  showCancelButton: true,
                  confirmButtonText: 'Ya',
                  cancelButtonText: 'Tidak'
              }).then((result) => {
                  if (result.isConfirmed) {
                      $('#fileDimensi').click();
                  }
              });
          } else {
              $('#fileDimensi').click();
          }

      });

    });

    $('#fileDimensi').change(function () {

      let formData = new FormData();
      formData.append('file', this.files[0]);

      $.ajax({
          url: "{{ url('olain/syncdimensi/proses') }}",
          method: "POST",
          data: formData,
          processData: false,
          contentType: false,
          headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          success: function (res) {
              Swal.fire('Sukses', res.message, 'success');
          },
          error: function () {
              Swal.fire('Error', 'Proses gagal', 'error');
          }
      });

    });
  });

	
</script>
@endsection