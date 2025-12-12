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
		        <h1 class="m-0">Cetak Ulang Kasir (94,91)</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Cetak Ulang Kasir (94,91)</li>
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
                      <label for="per">Periode</label>
                      <select name="per" id="per" class="form-control" required>
                        <option value="">Pilih Periode</option>
                        @foreach ($per as $periode)
                          <option value="{{ $periode->PERIO }}" {{ session()->get('filter_per') == $periode->PERIO ? 'selected' : '' }}>
                            {{ $periode->PERIO }}
                          </option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-md-2">
                      <label for="cbg">Cabang</label>
                      <select name="cbg" id="cbg" class="form-control" required>
                        <option value="">Pilih Cabang</option>
                        @foreach ($cbg as $cabang)
                          <option value="{{ $cabang->KODE }}" {{ session()->get('filter_cbg') == $cabang->KODE ? 'selected' : '' }}>
                            {{ $cabang->KODE }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                </div>

                <div class="form-group row" style="padding-left:20px">
                  <div class="col-md-2">
                    <label for="tglDr">Tanggal</label>
											<input class="form-control date tglDr" id="tglDr" name="tglDr"
											type="text" autocomplete="off" value="{{ session()->get('filter_tglDari') }}">
                  </div>
                  <div class="col-md-2">
                    <label for="tglSmp">s.d.</label>
                    <input class="form-control date tglSmp" id="tglSmp" name="tglSmp"
                    type="text" autocomplete="off" value="{{ session()->get('filter_tglSampai') }}">
                  </div>
                </div>

                <div class="form-group row" style="padding-left:20px">
                  <div class="col-md-2">
                    <label for="bukti1">No. Struk</label>
                    <input class="form-control" id="bukti1" name="bukti1"
                    type="text" autocomplete="off" value="{{ session()->get('filter_bukti1') }}">
                  </div>
                  <div class="col-md-2">
                    <label for="bukti2">s.d.</label>
                    <input class="form-control" id="bukti2" name="bukti2"
                    type="text" autocomplete="off" value="{{ session()->get('filter_bukti2') }}">
                  </div>
                </div>

                <div class="form-group row" style="padding-left:20px">
                  <div class="col-md-2">
                    <label for="ksr">Kasir</label>
                    <input class="form-control" id="ksr" name="ksr"
                    type="text" autocomplete="off" value="{{ session()->get('filter_ksr') }}">
                  </div>
                </div>

                <div class="row" style="padding-left:20px">
                    <div class="col-md-3">
                      <button type="button" class="btn btn-primary" id="btnTampil" style="white-space: nowrap;">
                          Tampilkan
                      </button>

                      <button type="button" class="btn btn-dark" id="btnPrint" style="white-space: nowrap;">
                          Print
                      </button>
                    </div>
                </div>
                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
											
                            <th scope="col" style="text-align: center">No. Struk</th>				
                            <th scope="col" style="text-align: center">Tanggal</th>
                            <th scope="col" style="text-align: center">Kode P.</th>
                            <th scope="col" style="text-align: center">Nama P.</th>
                            <th scope="col" style="text-align: center">Kasir</th>
                            <th scope="col" style="text-align: center">Kode Barang</th>
                            <th scope="col" style="text-align: center">Nama Barang</th>
							              <th scope="col" style="text-align: center">Qty</th>
							              <th scope="col" style="text-align: center">Harga</th>
							              <th scope="col" style="text-align: center">Total</th>
							              <th scope="col" style="text-align: center">PPN</th>
							              <th scope="col" style="text-align: center">Total Akhir</th>
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
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
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
                url: '{{ route('get-cetakksr') }}',
                data: function (d) {
                    d.per = $('#per').val();
                    d.cbg = $('#cbg').val();
                    d.tglDr = $('#tglDr').val();
                    d.tglSmp = $('#tglSmp').val();
                    d.bukti1 = $('#bukti1').val();
                    d.bukti2 = $('#bukti2').val();
                    d.ksr = $('#ksr').val();
                }
            },
            columns: 
            [
				        {data: 'no_bukti', name: 'no_bukti'},
                {data: 'tgl', name: 'tgl', render: $.fn.dataTable.render.moment('DD-MM-YYYY')},
                {data: 'kodeC', name: 'kodeC'},				
                {data: 'namaC', name: 'namaC'},				
                {data: 'ksr', name: 'ksr'},
				        {data: 'KD_BRG', name: 'KD_BRG'},
				        {data: 'NA_BRG', name: 'NA_BRG'},
				        {data: 'qty', name: 'qty', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
				        {data: 'xwe', name: 'xwe', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
				        {data: 'total', name: 'total', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
				        {data: 'ppn', name: 'ppn', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
                {data: 'totala', name: 'totala', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
            ],

            columnDefs: [
                {
                    "className": "dt-center", 
                    "targets": [0,1,2,3,4,5,6]
                },
                {
                    "className": "dt-right", 
                    "targets": [7,8,9,10,11]
                }
            ],

            dom: "<'row'<'col-md-6'B><'col-md-6'>>" +  // <-- tambahkan B untuk tombol di kiri
                  "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                  "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
				    
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-md'
                }
            ],
            stateSave:true,

        });

        // tombol tampil ditekan
        $('#btnTampil').on('click', function() {
            dataTable.ajax.reload();
        });
        
        $('#btnPrint').on('click', function () {
          Swal.fire({
              title: 'Cetak Per Struk?',
              text: "Setiap No. Struk (NO_BUKTI) akan dicetak menjadi 1 file PDF.",
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: 'Ya, Cetak!',
              cancelButtonText: 'Batal',
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33'
          }).then((result) => {
              if (result.isConfirmed) {

                  let allData = $('.datatable').DataTable().rows().data().toArray();

                  if (allData.length === 0) {
                      Swal.fire('Tidak ada data untuk dicetak!', '', 'warning');
                      return;
                  }

                  // Ambil NO_BUKTI unik
                  let buktiList = [...new Set(allData.map(x => x.no_bukti))];

                  // Ambil parameter lain
                  let per     = $('#per').val();
                  let cbg     = $('#cbg').val();
                  let tglDr   = $('#tglDr').val();
                  let tglSmp  = $('#tglSmp').val();
                  let ksr     = $('#ksr').val();

                  // Loop print per NO_BUKTI
                  buktiList.forEach(function (bukti, idx) {
                      setTimeout(() => {
                          window.open(
                              `{{ url('cetakksr/print') }}?per=${per}` +
                              `&cbg=${cbg}` +
                              `&tglDr=${tglDr}` +
                              `&tglSmp=${tglSmp}` +
                              `&bukti1=${bukti}` +
                              `&bukti2=${bukti}` +
                              `&ksr=${ksr}`,
                              '_blank'
                          );
                      }, idx * 600); // delay agar tidak diblokir popup browser
                  });

              }
          });
      });


        $('.date').datepicker({
            dateFormat: 'dd-mm-yy'
        });
    });

	
</script>
@endsection