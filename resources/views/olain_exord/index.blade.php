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
		        <h1 class="m-0">Export Orderan TS</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Export Orderan TS</li>
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
                <form id="formFilter">
                  @csrf
                  <div class="row" style="padding-left:650px">
                      <div class="col-md-1">
                        <label for="TGL">Tanggal</label>
                      </div>
                      <div class="col-md-2">
                        <input class="form-control date" id="TGL" name="TGL"
                                data-date-format="dd-mm-yyyy" type="text" autocomplete="off"
                                value="{{ session('filter_tgl') }}">
                      </div>
                  </div>

                  <div class="row" style="padding-left:650px">
                      <div class="col-md-1">
                        <label for="cbg">Cabang</label>
                      </div>
                      <div class="col-md-2">
                        <select name="cbg" id="cbg" class="form-control cbg" style="width: 200px">
                          <option value="">--Pilih Cabang--</option>
                          @foreach($cbg as $cbgD)
                            <option value="{{$cbgD->KODE}}" {{ session()->get('filter_cbg')== $cbgD->KODE ? 'selected' : '' }}>{{$cbgD->KODE}}</option>
                          @endforeach
                        </select>
                      </div>
                  </div>

                  <div class="row" style="padding-left:875px">
                      <div class="col-md-3">
                        <button type="button" class="btn btn-dark" id="btnProses" style="white-space: nowrap;">
                            Proses
                        </button>
                      </div>
                  </div>
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

    $('.date').datepicker({
        format: "dd-mm-yyyy",
        autoclose: true,
        todayHighlight: true
    });

    $('#btnProses').click(function() {
      $.ajax({
          url: "{{ route('proses-order') }}",
          type: "POST",
          data: $('#formFilter').serialize(),
          success: function(resp){
              if(resp.reprint){
                  Swal.fire({
                      title: 'Orderan sudah pernah diproses',
                      text: 'Cetak ulang?',
                      showCancelButton: true,
                  }).then(r=>{
                      if(r.isConfirmed){
                          window.location = '{{ url('cetak-order') }}?cbg='+resp.cbg+'&tgl='+resp.tgl+'&shift='+resp.shift;
                      }
                  });
              } else {
                  window.location = '{{ url('cetak-order') }}?cbg='+resp.cbg+'&tgl='+resp.tgl+'&shift='+resp.shift;
              }
          },
          error: function(xhr){
              console.log(xhr.responseText);
              Swal.fire('Error', 'Gagal proses', 'error');
          }
      });
    });
  });

	
</script>
@endsection