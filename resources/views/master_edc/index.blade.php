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
		        <h1 class="m-0">Master EDC</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Master EDC</li>
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
          <!-- PANEL KIRI -->
          <div class="col-md-6">
              <div class="card">
                  <div class="card-body">
                    <div class="form-group row" style="padding-left:20px">
                      {{-- <div class="col-md-2" align="right">
                        <label><strong>Cabang :</strong></label>
                      </div>
                      <div class="col-md-2">
                        <select name="cbg" id="cbg" class="form-control cbg" style="width: 200px">
                            <option value="">--Pilih Cabang--</option>
                            @foreach($cbg as $cbgD)
                                <option value="{{$cbgD->KODE}}"  {{ (session()->get('filter_cbg') == $cbgD->KODE) ? 'selected' : '' }}>{{$cbgD->KODE}}</option>
                            @endforeach
                        </select>
                      </div> --}}
                      <div class="col-md-2">
                          <button type="button" class="btn btn-warning" id="btnTampil" style="white-space: nowrap;">
                            Tampilkan
                          </button>
                      </div>
                    </div>
                    <table class="table table-striped table-bordered nowrap" id="datatable_left">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>-</th>
                                <th>Kode</th>
                                <th>Nama Bank</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                  </div>
              </div>
          </div>

          <!-- PANEL KANAN -->
          <div class="col-md-6">
              <div class="card">
                  <div class="card-body">
                    <div class="form-group row" style="padding-left:20px">
                      <label><strong>No.EDC :</strong></label>
                      <div class="col-md-2">
                          <input class="form-control EDC" id="EDC" name="EDC"
                          type="text" autocomplete="off" value="{{ session()->get('EDC') }}"> 
                      </div>
                      <div class="col-md-2">
                          <input class="form-control NA_BANK" id="NA_BANK" name="NA_BANK" placeholder="014 BCA"
                          type="text" autocomplete="off" value="{{ session()->get('NA_BANK') }}"> 
                      </div>
                    </div>
                    <table class="table table-striped table-bordered nowrap" id="datatable_right">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>-</th>
                                <th>Kode EDC</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                  </div>
              </div>
          </div>
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

      // DataTable KIRI
      $('#datatable_left').DataTable({
          processing: true,
          serverSide: true,
          scrollY: '400px',
          ajax: {
              url: '{{ route("get-edc") }}'
          },
          columns: [
              {data: 'DT_RowIndex', orderable: false, searchable: false},
              {data: 'action', name: 'action'},
              {data: 'KODE', name: 'KODE'},
              {data: 'NM_BANK', name: 'NM_BANK'},
          ],
          columnDefs: [
                {
                    "className": "dt-center", 
                    "targets": [0,1,2,3]
                }
            ],
      });


      // DataTable KANAN
      $('#datatable_right').DataTable({
          processing: true,
          serverSide: true,
          scrollY: '400px',
          ajax: {
              url: '{{ route("get-kode") }}' // route berbeda untuk kanan
          },
          columns: [
              {data: 'DT_RowIndex', orderable: false, searchable: false},
              {data: 'action', name: 'action'},
              {data: 'KD_EDC', name: 'KD_EDC'},
          ],
          columnDefs: [
                {
                    "className": "dt-center", 
                    "targets": [0,1,2]
                }
            ],
      });

  });
</script>
@endsection