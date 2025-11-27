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

    <!-- HEADER -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <!-- Bisa ditambahkan title -->
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Invoice Agenda</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- SPLIT SCREEN -->
    <div class="container-fluid">
      <div class="row split-wrapper">

        <!-- ===================== LEFT ===================== -->
        <div class="col-md-6 split-left">
          <div class="content">
            <div class="container-fluid">
              <div class="row">
                <div class="col-12">

                  <div class="card">
                    <div class="card-body">

                      <div class="form-group row" style="padding-left:20px">
                        <label><strong>No :</strong></label>
                        <div class="col-md-1">
                          <input class="form-control NOMOR" id="NOMOR" name="NOMOR"
                            type="text" autocomplete="off" readonly> 
                        </div>
                        <label><strong>Kode :</strong></label>
                        <div class="col-md-2">
                          <input class="form-control KODEC" id="KODEC" name="KODEC"
                                type="text" autocomplete="off" value="{{ session()->get('KODEC') }}"> 
                        </div>
                        <div class="col-md-3">
                          <button type="button" class="btn btn-dark" id="btnTambah" style="white-space: nowrap;">
                            Tambah
                          </button>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label><strong>Nama :</strong></label>
                        <div class="col-md-4">
                          <input class="form-control NAMAC" id="NAMAC" name="NAMAC"
                                type="text" autocomplete="off" value="{{ session()->get('NAMAC') }}"> 
                        </div>
                      </div>
                      <table class="table table-striped table-bordered nowrap" id="datatable_left">
                        <thead class="table-dark">
                          <tr>
                            <th scope="col" style="text-align: center">No</th>
                            
                            <th scope="col" style="text-align: center">Member</th>
                            <th scope="col" style="text-align: center">Nama</th>
                          </tr>		
                        </thead>
                        <tbody></tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- ===================== END LEFT ===================== -->


        <!-- ===================== RIGHT ===================== -->
        <div class="col-md-6 split-right">
          <div class="content">
            <div class="container-fluid">
              
              <div class="row">
                <div class="col-12">
                  
                  <div class="card">
                    <div class="card-body">
                      <h1>FRR - FRESHINDO RENON</h1>
                      <div class="form-group row align-items-center" style="padding-left:20px; display:flex;">
    
                        <label class="mr-2"><strong>Sub Item :</strong></label>

                        <div class="col-md-2">
                            <input class="form-control subitem" id="subitem" name="subitem"
                            type="text" autocomplete="off" value="{{ session()->get('subitem') }}">
                        </div>

                        <!-- TEXT DI SEBELAH KANAN BUTTON -->
                        <div class="col-md-6 d-flex align-items-left">
                            <h2 style="margin:0; padding-left:10px;">MUTIARA DEWATA JAYA.PT</h2>
                        </div>
                      </div>
                      <table class="table table-striped table-bordered nowrap" id="datatable_right">
                        <thead class="table-dark">
                          <tr>
                            
                            <th style="text-align: center">SUB</th>
                            <th style="text-align: center">Sub Item</th>
                            <th style="text-align: center">Nama Barang</th>
                            <th style="text-align: center">HB</th>
                            <th style="text-align: center">Harga</th>
                          </tr>
                        </thead>
                        <tbody></tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- ===================== END RIGHT ===================== -->
      </div>
    </div>

</div>



    <!-- Status -->
    @if (session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>
    @endif

    
  </div>
  <!-- /.content-wrapper -->
@endsection

@section('javascripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {

      // DataTable Kanan
      var tableRight = $('#datatable_right').DataTable({
          processing: true,
          serverSide: true,
          scrollY: '400px',
          ajax: {
              url: '{{ route("get-invoice") }}'
          },
          columns: [
              
              {data: 'SUB', name: 'SUB'},
              {data: 'KD_BRG', name: 'KD_BRG'},
              {data: 'NA_BRG', name: 'NA_BRG'},				
              {data: 'HB', name: 'HB', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
              {data: 'HARGA', name: 'HARGA', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
          ],
          columnDefs: [
                {
                    "className": "dt-center", 
                    "targets": [0,1,2]
                },
                {
                    "className": "dt-right", 
                    "targets": [3,4]
                },
            ],
      });

      $('#subitem').on('keyup', function () {
        tableRight
            .column(1)     // kolom Sub Item = index 1
            .search(this.value)
            .draw();
      });
      
      // =============== AMBIL NOMOR OTOMATIS ===============
      $.ajax({
          url: '{{ route("get-next-nomor") }}',
          type: 'GET',
          success: function(res) {
              $('#NOMOR').val(res.next_nomor);
          }
      });

      // DataTable Kiri
      $('#datatable_left').DataTable({
          processing: true,
          serverSide: true,
          scrollY: '400px',
          ajax: {
              url: '{{ route("get-kiri") }}' // route berbeda untuk kanan
          },
          columns: [
              {data: 'DT_RowIndex', orderable: false, searchable: false},
              
              {data: 'KODEC', name: 'KODEC'},
              {data: 'NAMAC', name: 'NAMAC'},
          ],
          columnDefs: [
                {
                    "className": "dt-center", 
                    "targets": [0,1,2]
                }
            ],
      });

      $('#btnTambah').on('click', function() {

        let kodec = $('#KODEC').val();
        let namac = $('#NAMAC').val();

        if (kodec.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Kode member tidak boleh kosong!',
                timer: 1500,
                showConfirmButton: false
            });
            return;
        }

        if (namac.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Nama member tidak boleh kosong!',
                timer: 1500,
                showConfirmButton: false
            });
            return;
        }

        $.ajax({
            url: '{{ route("invoice.store") }}',
            type: 'POST',
            data: {
                KODEC: kodec,
                NAMAC: namac,
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {

                Swal.fire({
                    icon: 'success',
                    title: res.message,
                    timer: 1200,
                    showConfirmButton: false
                });

                // reload datatable kiri
                $('#datatable_left').DataTable().ajax.reload();

                // kosongkan input
                $('#KODEC').val('');
                $('#NAMAC').val('');
            },

            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Terjadi kesalahan!',
                });
            }
        });
      });
    });
	
</script>
@endsection