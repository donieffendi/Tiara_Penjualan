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
		        <h1 class="m-0">Master Barang</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Master Barang</li>
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
                    <label><strong>Sub :</strong></label>
                    <div class="col-md-1">
                        <input class="form-control sub" id="sub" name="sub"
                        type="text" autocomplete="off" value="{{ session()->get('sub') }}"> 
                    </div>
                    <div class="col-md-3">
                      <button type="button" class="btn btn-warning" id="btnTampil" style="white-space: nowrap;">
                          Tampilkan
                      </button>
                    </div>
                </div>
                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
											
                            <th scope="col" style="text-align: center">No</th>
				     		            <th scope="col" style="text-align: center">-</th>							
                            <th scope="col" style="text-align: center">Kode</th>
                            <th scope="col" style="text-align: center">Nama</th>
                            <th scope="col" style="text-align: center">Ukuran</th>
                            <th scope="col" style="text-align: center">Kemasan</th>
                            <th scope="col" style="text-align: center">Merk</th>
							              <th scope="col" style="text-align: center">Supplier</th>
							              <th scope="col" style="text-align: center">Nama Supplier</th>
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
            deferLoading: 0,
            ajax: {
                url: '{{ route('get-brg') }}',
                data: function (d) {
                    d.sub = $('#sub').val(); 
                }
            },
            columns: 
            [
                {data: 'DT_RowIndex', orderable: false, searchable: false },
				        {data: 'action', name: 'action'},
				        {data: 'kd_brg', name: 'kd_brg'},
                {data: 'na_brg', name: 'na_brg'},
                {data: 'ket_uk', name: 'ket_uk'},				
                {data: 'ket_kem', name: 'ket_kem'},				
                {data: 'merk', name: 'merk'},
				        {data: 'supp', name: 'supp'},
				        {data: 'nama', name: 'nama'},
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

        // tombol tampil ditekan
        $('#btnTampil').on('click', function() {
            dataTable.ajax.reload();
        });
        
        // === tambahkan ini setelah DataTable di-initialize ===
        dataTable.on('xhr.dt', function (e, settings, json, xhr) {
          console.log("XHR event fired:", json);

          if (json && json.data && json.data.length > 0) {
              console.log("Row pertama:", json.data[0]);
          } else {
              console.warn("Data kosong atau JSON null");
          }
        });

        $("div.test_btn").html(`
            <a class="btn btn-lg btn-md btn-success" href="{{url('brg/edit?idx=0&tipx=new')}}">
                <i class="fas fa-plus fa-sm md-3"></i>
            </a>
            <button type="button" id="btnPrint" class="btn btn-primary btn-md">
                <i class="fas fa-print"></i> Print
            </button>
        `);
        
        $('#btnPrint').on('click', function() {
          let sub = $('#sub').val();

          if (!sub) {
              Swal.fire({
                  icon: 'warning',
                  title: 'Sub belum diisi!',
                  text: 'Silakan isi Sub terlebih dahulu sebelum mencetak data.',
                  confirmButtonText: 'OK',
                  confirmButtonColor: '#3085d6'
              });
              return;
          }

          Swal.fire({
              title: 'Cetak Data Barang?',
              text: "Laporan akan dibuka di tab baru sesuai filter Sub yang dipilih.",
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: 'Ya, Cetak!',
              cancelButtonText: 'Batal',
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33'
          }).then((result) => {
              if (result.isConfirmed) {
                  // buka jasper report di tab baru
                  window.open(`{{ url('brg/print') }}?sub=${sub}`, '_blank');
              }
          });
        });
    });

	
</script>
@endsection