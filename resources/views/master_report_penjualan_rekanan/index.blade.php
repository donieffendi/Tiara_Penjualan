@extends('layouts.plain')
@section('styles')
<link rel="stylesheet" href="{{url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
{{-- <link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}"> --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
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
		        <h1 class="m-0">Master Report Penjualan Rekanan</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Master Report Penjualan Rekanan</li>
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
                  <div class="col-md-2">
                    <label><strong>Tanggal Dari :</strong></label>
                    <input class="form-control date tglDr" id="tglDr" name="tglDr"
                    type="text" autocomplete="off" value="{{ session()->get('filter_tglDari') }}"> 
                  </div>
                  <div class="col-md-2">
                    <label><strong>Tanggal Sampai :</strong></label>
                    <input class="form-control date tglSmp" id="tglSmp" name="tglSmp"
                    type="text" autocomplete="off" value="{{ session()->get('filter_tglSampai') }}">
                  </div>
                </div>

                <div class="form-group row" style="padding-left:20px">
                  <div class="col-md-2">
                    <label><strong>Periode :</strong></label>
                    <select name="perio" id="perio" class="form-control perio" style="width: 200px">
                      <option value="">--Pilih Periode--</option>
                      @foreach($per as $perD)
                        <option value="{{$perD->PERIO}}" {{ session()->get('filter_per')== $perD->PERIO ? 'selected' : '' }}>{{$perD->PERIO}}</option>
                      @endforeach
                    </select>
                  </div>

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

                <div class="form-group row" style="padding-left:20px">
                    <div class="col-md-1">
                        <label><strong>Sub :</strong></label>
                        <input class="form-control sub1" id="sub1" name="sub1"
							          type="text" autocomplete="off" value="{{ session()->get('filter_sub1') }}"> 
                    </div>
                    
                        <label>S/d</label>

                    <div class="col-md-1">
                        <label><strong>Sub :</strong></label>
                        <input class="form-control sub2" id="sub2" name="sub2"
							          type="text" autocomplete="off" value="{{ session()->get('filter_sub2') }}">
                    </div>
                </div>
                <div class="form-group row" style="padding-left:20px">
                  <div class="col-md-5">
                    <button type="button" class="btn btn-warning" id="btnTampil" style="white-space: nowrap;">
                        Tampilkan
                    </button>
    
                    <button type="button" class="btn btn-primary" id="btnPrint" style="white-space: nowrap;">
                        Print
                    </button>
                  
                    <button type="button" class="btn btn-success" id="btnExcell" style="white-space: nowrap;">
                        Excell
                    </button>
                  </div>
                </div>

                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
											
                            <th scope="col" style="text-align: center">No</th>
				     		            <th scope="col" style="text-align: center">-</th>							
                            <th scope="col" style="text-align: center">Rekanan</th>
                            <th scope="col" style="text-align: center">No. Kasir</th>
                            <th scope="col" style="text-align: center">Tanggal</th>
                            <th scope="col" style="text-align: center">Sub Item</th>
							              <th scope="col" style="text-align: center">Nama Barang</th>
							              <th scope="col" style="text-align: right">Qty</th>
							              <th scope="col" style="text-align: right">NPPN</th>
							              <th scope="col" style="text-align: right">DPP</th>
                            <th scope="col" style="text-align: right">Total</th>
                            <th scope="col" style="text-align: right">Komisi</th>
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
                url: '{{ route('get-rjual-rekanan') }}',
                data: function (d) {
                  d.tglDr = $('#tglDr').val();
                  d.tglSmp = $('#tglSmp').val();
                  d.perio = $('#perio').val();
                  d.cbg = $('#cbg').val();
                  d.sub1 = $('#sub1').val();
                  d.sub2 = $('#sub2').val();
                },
                dataSrc: function(json) {
                  if (!json.data || json.data.length === 0) {
                      return []; // biar kosong kalau belum tampil
                  }
                  return json.data;
                }
            },
            columns: 
            [
                {data: 'DT_RowIndex', orderable: false, searchable: false },
				        {data: 'action', name: 'action'},
				        {data: 'REKANAN', name: 'REKANAN'},
                {data: 'NO_BUKTI', name: 'NO_BUKTI'},
                {data: 'TGL', name: 'TGL', render: $.fn.dataTable.render.moment('DD-MM-YYYY')},				
                {data: 'KD_BRG', name: 'KD_BRG'},
				        {data: 'NA_BRG', name: 'NA_BRG'},
                {data: 'QTY', name: 'QTY', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
                {data: 'NPPN', name: 'NPPN', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'DPP', name: 'DPP', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'TOTAL', name: 'TOTAL', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},
                {data: 'KOMISI', name: 'KOMISI', render: $.fn.dataTable.render.number( ',', '.', 2, '' )},

            ],

            columnDefs: [
                {
                    "className": "dt-center", 
                    "targets": [0,1,2,3,4,5,6]
                },
                {
                    "className": "dt-right", 
                    "targets": [7,8,9,10,11]
                },
            ],

            dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
				    
            stateSave:true,
            deferloading: 0,
        });

        // $('#btnTampil').click(function() {
        //   dataTable.ajax.reload();
        // });

        $('#btnTampil').click(function() {
          // alert('tombol tampil diklik');
          dataTable.ajax.reload();
        });
      
        $('.date').datepicker({  
          dateFormat: 'dd-mm-yy'
        });
        
        // $('#btnPrint').on('click', function() {
        //   // ambil nilai pencarian aktif di DataTables
        //   let searchValue = $('.dataTables_filter input').val();

        //   Swal.fire({
        //       title: 'Cetak Data Rekanan?',
        //       text: "Akan mencetak semua data yang sesuai filter pencarian.",
        //       icon: 'question',
        //       showCancelButton: true,
        //       confirmButtonText: 'Ya, Cetak!',
        //       cancelButtonText: 'Batal',
        //       confirmButtonColor: '#3085d6',
        //       cancelButtonColor: '#d33'
        //   }).then((result) => {
        //       if (result.isConfirmed) {
        //           // buka Jasper dengan parameter search
        //           window.open(`{{ url('rjual-rekanan/print') }}?search=${encodeURIComponent(searchValue)}`, '_blank');
        //       }
        //   });
        // });

        $('#btnPrint').on('click', function() {
          let searchValue = $('.dataTables_filter input').val();
          let cbg   = $('#cbg').val();
          let perio = $('#perio').val();
          let tglDr = $('#tglDr').val();
          let tglSmp= $('#tglSmp').val();
          let sub1  = $('#sub1').val();
          let sub2  = $('#sub2').val();

          Swal.fire({
              title: 'Cetak Data Rekanan?',
              text: "Akan mencetak semua data yang sesuai filter pencarian.",
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: 'Ya, Cetak!',
              cancelButtonText: 'Batal',
          }).then((result) => {
              if (result.isConfirmed) {

                  let url = `{{ url('rjual-rekanan/print') }}?
                      cbg=${encodeURIComponent(cbg)}&
                      perio=${encodeURIComponent(perio)}&
                      tglDr=${encodeURIComponent(tglDr)}&
                      tglSmp=${encodeURIComponent(tglSmp)}&
                      sub1=${encodeURIComponent(sub1)}&
                      sub2=${encodeURIComponent(sub2)}&
                      search=${encodeURIComponent(searchValue)}`;

                  window.open(url, '_blank');
              }
          });
        });

        $('#btnExcell').click(function() {
          let params = $.param({
              tglDr: $('#tglDr').val(),
              tglSmp: $('#tglSmp').val(),
              cbg: $('#cbg').val(),
              sub1: $('#sub1').val(),
              sub2: $('#sub2').val()
          });
          window.location.href = "{{ url('rjual-rekanan/export-excel') }}?" + params;
        });
    
    });
	
</script>
@endsection