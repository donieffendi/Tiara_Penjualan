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
		        <h1 class="m-0">Hapus Barang -Lama Kosong</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Hapus Barang -Lama Kosong</li>
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
                <form method="POST" id="entri" action="{{url('hbrg/proses')}}">
                    @csrf
                  <div class="form-group row" style="padding-left:20px">
                    <button type="button" class="btn btn-dark" id="btnClear" style="white-space: nowrap;">
                          Clear Data
                      </button>
                  </div>
                  <div class="form-group row">
                    <div class="col-md-2">
                      <label><strong>Cabang :</strong></label>
                      <select name="cbg" id="cbg" class="form-control cbg" style="width: 200px">
                          <option value="">--Pilih Cabang--</option>
                          @foreach($cbg as $cbgD)
                              <option value="{{$cbgD->KODE}}"  {{ (session()->get('filter_cbg') == $cbgD->KODE) ? 'selected' : '' }}>{{$cbgD->KODE}}</option>
                          @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="form-group row">
                    <div class="col-md-3">
                      <button type="button" class="btn btn-warning" id="btnTampil" style="white-space: nowrap;">
                          Tampilkan
                      </button>
                      <button type="button" class="btn btn-primary" id="btnPrint" style="white-space: nowrap;">
                          Print
                      </button>
                      <button type="button" class="btn btn-danger" id="btnProses" style="white-space: nowrap;">
                          Prosses
                      </button>
                    </div>
                  </div>
                </form>
                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="text-align: center">No</th>
				     		            <th scope="col" style="text-align: center">-</th>							
                            <th scope="col" style="text-align: center">Sub Item</th>
                            <th scope="col" style="text-align: center">Nama Barang</th>
                            <th scope="col" style="text-align: center">Ukuran</th>
                            <th scope="col" style="text-align: center">KD</th>
                            <th scope="col" style="text-align: center">Saldo</th>
                            <th scope="col" style="text-align: center">Tgl Terima</th>
                            <th scope="col" style="text-align: center">Akhir Trm</th>
                            <th scope="col" style="text-align: center">Bukti Kosong</th>
                            <th scope="col" style="text-align: center">Catatan</th>
                            <th scope="col" style="text-align: center">cek</th>
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
            deferloading: 0,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax: 
            {
                url: '{{ route('get-hbrg') }}',
                data: function (d) {
                  d.cbg = $('#cbg').val();
                }
            },
            columns: 
            [
                {  data: 'DT_RowIndex', orderable: false, searchable: false },
				        {data: 'action', name: 'action'},
                {data: 'sub', name: 'sub'},
                {data: 'na_brg', name: 'na_brg'},
                {data: 'ket_uk', name: 'ket_uk'},				
                {data: 'kdlaku', name: 'kdlaku'},
                {data: 'saldo', name: 'saldo'},
                {data: 'TGL_TERIMA', name: 'TGL_TERIMA'},
                {data: 'AKHIR_TRM', name: 'AKHIR_TRM'},
                {data: 'BUKTI_KOSONG', name: 'BUKTI_KOSONG'},
                {data: 'CATATAN', name: 'CATATAN'},
                {data: 'cek', name: 'cek'},
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
        
        // $("div.test_btn").html('<a class="btn btn-lg btn-md btn-success" href="{{url('hbrg/edit?idx=0&tipx=new')}}"> <i class="fas fa-plus fa-sm md-3" ></i></a');

        $('#btnTampil').on('click', function () {

          // Tampilkan loading
          Swal.fire({
              title: 'Sedang memuat data...',
              text: 'Mohon tunggu sebentar.',
              allowOutsideClick: false,
              didOpen: () => {
                  Swal.showLoading();
              }
          });

          // Reload datatable
          dataTable.ajax.reload(function () {
              // Tutup loading setelah datatables selesai
              Swal.close();
          });
        });

        $('#btnClear').click(function () {
          Swal.fire({
              title: 'Yakin hapus semua data?',
              text: "Data di tabel brgdel akan dikosongkan permanent!",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Ya, hapus!',
              cancelButtonText: 'Batal'
          }).then((result) => {
              if (result.isConfirmed) {

                  $.ajax({
                      url: "{{ route('hbrg.clear') }}",
                      type: 'POST',
                      data: {
                          _token: "{{ csrf_token() }}"
                      },
                      success: function (response) {

                          Swal.fire({
                              icon: 'success',
                              title: 'Berhasil!',
                              text: response.message,
                          }).then(() => {
                              location.reload();
                          });

                      },
                      error: function () {
                          Swal.fire({
                              icon: 'error',
                              title: 'Gagal!',
                              text: 'Terjadi kesalahan saat menghapus data.',
                          });
                      }
                  });

              }
          });
        });

        $('#btnProses').on('click', function() {
          Swal.fire({
              title: "Proses data?",
              icon: "question",
              showCancelButton: true,
              confirmButtonText: "Ya, proses",
          }).then((result) => {
              if (result.isConfirmed) {

                  // ambil semua checkbox yang dicentang
                  var checked = [];
                  $('.cek:checked').each(function() {
                      checked.push($(this).val());
                  });

                  if (checked.length === 0) {
                      Swal.fire("Tidak ada data", "Centang dulu data yang mau diproses.", "warning");
                      return;
                  }

                  Swal.fire({
                      title: "Memproses...",
                      didOpen: () => {
                          Swal.showLoading();
                      },
                      allowOutsideClick: false
                  });

                  $.ajax({
                      url: "{{ url('hbrg/proses') }}",
                      type: "POST",
                      data: {
                          cek: checked,        // <--- PENTING
                          _token: "{{ csrf_token() }}"
                      },
                      success: function(res) {
                          Swal.close();
                          Swal.fire("Selesai", "Proses berhasil!", "success");
                          dataTable.ajax.reload();
                      },
                      error: function() {
                          Swal.close();
                          Swal.fire("Error", "Terjadi kesalahan server", "error");
                      }
                  });
              }
          });
        });

        $('#btnPrint').on('click', function() {
          // ambil nilai pencarian aktif di DataTables
          let searchValue = $('.dataTables_filter input').val();

          Swal.fire({
              title: 'Cetak Data ?',
              text: "Akan mencetak semua data yang sesuai filter pencarian.",
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: 'Ya, Cetak!',
              cancelButtonText: 'Batal',
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33'
          }).then((result) => {
              if (result.isConfirmed) {
                  // buka Jasper dengan parameter search
                  window.open(`{{ url('hbrg/cetak') }}?search=${encodeURIComponent(searchValue)}`, '_blank');
              }
          });
        });
      });
	
</script>
@endsection