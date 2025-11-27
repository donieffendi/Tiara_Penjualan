@extends('layouts.plain')
@section('styles')
<link rel="stylesheet" href="{{url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
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
		        <h1 class="m-0">Ubah Tanggal SO</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Ubah Tanggal SO</li>
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
                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>							
                            <th scope="col" style="text-align: center">SUB</th>
                            <th scope="col" style="text-align: center">KELOMPOK</th>
                            <th scope="col" style="text-align: center">TYPE</th>
                            <th scope="col" style="text-align: center">Dari Tanggal</th>
                            <th scope="col" style="text-align: center">Sampai Tanggal</th>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
  $(document).ready(function() {
      var dataTable = $('.datatable').DataTable({
          processing: true,
          serverSide: true,
          autoWidth: true,
          // paging: false,
          'scrollY': '400px',
          "order": [[ 0, "asc" ]],
          ajax: {
              url: '{{ route('get-sog') }}'
          },
          columns: 
          [
              {data: 'SUB', name: 'SUB'},
              {data: 'KELOMPOK', name: 'KELOMPOK'},
              {data: 'TYPE', name: 'TYPE'},				
              {
                data: 'TGL_AWAL_SO',
                render: function(data, type, row) {
                    let tgl = moment(data).format('DD-MM-YYYY'); // tampil dd-mm-yyyy

                    return `
                        <input type="text" class="form-control form-control-sm tgl-awal"
                              data-sub="${row.SUB}"
                              value="${tgl}">
                    `;
                }
              },
              {
                data: 'TGL_AKHIR_SO',
                render: function(data, type, row) {
                    let tgl = moment(data).format('DD-MM-YYYY');

                    return `
                        <input type="text" class="form-control form-control-sm tgl-akhir"
                              data-sub="${row.SUB}"
                              value="${tgl}">
                    `;
                }
              }

          ],

          columnDefs: [
              {
                  "className": "dt-center", 
                  "targets": [0,1,2,3,4]
              },
              {
                targets: [3,4],
                render: $.fn.dataTable.render.moment( 'DD-MM-YYYY' )
              },
          ],

          dom: "<'row'<'col-md-6'><'col-md-6'>>" +
              "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
              "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
          
          stateSave:true,

      });

      dataTable.on('draw.dt', function () {
        $('.tgl-awal, .tgl-akhir').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true
        });
      });

      $('#datatable').on('change', '.tgl-awal, .tgl-akhir', function() {

        let sub  = $(this).data('sub');
        let val  = $(this).val(); // dd-mm-yyyy
        let kolom = $(this).hasClass('tgl-awal') ? 'TGL_AWAL_SO' : 'TGL_AKHIR_SO';

        $.ajax({
            url: "{{ route('sog.updateTanggal') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                sub: sub,
                kolom: kolom,
                value: val
            },
            success: function(res) {
                Swal.fire("Berhasil", "Tanggal berhasil diupdate!", "success");
            },
            error: function() {
                Swal.fire("Error", "Gagal update tanggal!", "error");
            }
        });
      });

    });

	
</script>
@endsection