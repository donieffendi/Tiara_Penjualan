@extends('layouts.plain')
@section('styles')
<link rel="stylesheet" href="{{asset('foxie_js_css/jquery.dataTables.min.css')}}" />
@endsection

<style>  
    th { font-size: 13px; }
    td { font-size: 13px; }

    .badge-warning {
        background-color: #06ba00 !important; /* Warna default badge-warning (kuning) */
        color: white !important; /* Warna teks putih */
    }
</style>

@section('content')
<div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
		        <h1 class="m-0">Rekap Label Harian</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Rekap Label Harian</li>
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

                

                  <!-- Filter Sub -->
                <div class="form-group row" style="padding-left:20px">
                  <label for="CBG" class="form-label">Cabang : </label>
                  <div class = "col-md-2">
                    <select id="CBG" class="form-control"  name="CBG">
                      <option value="" {{ session('filter_cbg') == '' ? 'selected' : '' }} disable selected hidden>--Pilih Cabang--</option>
                      <option value="TGZ" {{ session('filter_cbg') == 'TGZ' ? 'selected' : '' }}>TGZ</option>
                      <option value="TMM" {{ session('filter_cbg') == 'TMM' ? 'selected' : '' }}>TMM</option>
                      <option value="SOP" {{ session('filter_cbg') == 'SOP' ? 'selected' : '' }}>SOP</option>
                    </select> 
                  </div>
                  <div class="col-md-1" align="right">
                      <label for="TGL" class="form-label">Tgl Mulai :</label>
                  </div>
                  <div class="col-md-2">
                      <input class="form-control date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y')}}">
                  </div>
                </div>

                <div class="form-group row" style="padding-left:20px">
                    <div class="col-md-1">
                        <button type="button" class="btn btn-primary" id="btnTampil" style="white-space: nowrap;">
                            Tampilkan
                        </button>
                    </div>
                </div>

                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="text-align: center">No. Usulan</th>					
                            <th scope="col" style="text-align: center">Tanggal</th>					
                            <th scope="col" style="text-align: center">Sub Item</th>
                            <th scope="col" style="text-align: center">Nama Barang</th>
                            <th scope="col" style="text-align: center">Ket. Uk</th>
                            <th scope="col" style="text-align: center">Hrg. Lama</th>
                            <th scope="col" style="text-align: center">Hrg. Baru</th>
                            <th scope="col" style="text-align: center">Posted</th>
                            <th scope="col" style="text-align: center">Tanggal Posted</th>
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
<!-- filter kolom di index -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- batas filter  -->
<script>
  $(document).ready(function() {
    $('.date').datepicker({  
      dateFormat: 'dd-mm-yy'
    });

    var dataTable = $('.datatable').DataTable({
      processing: true,
      serverSide: true,
      searching: true,
      autoWidth: false,
      paging: false,
      scrollX: true,
      scrollY: '400px',
      order: [[0, "asc"]],
      ajax: {
          url: '{{ route('get-rkplabel') }}',
          data: function(d) {
              d.TGL = $('#TGL').val(),
              d.CBG = $('#CBG').val()
          }
      },
      deferLoading: 0, // <--- ini mencegah load otomatis
      columns: [
          {data: 'NO_BUKTI', name: 'NO_BUKTI'},
          {data: 'TGL', name: 'TGL', render: $.fn.dataTable.render.moment('DD-MM-YYYY')},
          {data: 'KD_BRG', name: 'KD_BRG'},
          {data: 'NA_BRG', name: 'NA_BRG',
              render: function(data){
                  return '<h5><span class="badge badge-pill badge-warning">' + data + '</span></h5>';
              }
          },
          {data: 'KET_UK', name: 'KET_UK'},
          {data: 'HJ_LAMA', name: 'HJ_LAMA', render: $.fn.dataTable.render.number(',', '.', 0, '')},
          {data: 'HJ_BARU', name: 'HJ_BARU', render: $.fn.dataTable.render.number(',', '.', 0, '')},
          {data: 'POSTED', name: 'POSTED'},
          {data: 'TGL_POSTED', name: 'TGL_POSTED', render: $.fn.dataTable.render.moment('DD-MM-YYYY')}
      ],

        columnDefs: [
            {
                "className": "dt-center", 
                "targets": [0,1,2,3,4,7]
            },
            {
                "className": "dt-right", 
                "targets": [5,6,8]
            }
        ],

        dom: "<'row'<'col-md-6'><'col-md-6'>>" +
            "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
            "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
        
        stateSave:true,
    });
        
        // Trigger reload saat nilai filter berubah
        $('#btnTampil').on('click', function() {
            dataTable.ajax.reload();
        });

  });
	
</script>
@endsection