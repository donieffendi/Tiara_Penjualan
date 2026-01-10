@extends('layouts.plain')
@section('styles')
<!-- <link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}"> -->
<link rel="stylesheet" href="{{asset('foxie_js_css/jquery.dataTables.min.css')}}" />

@endsection

<style>
    .card {
        padding: 5px 10px !important;
    }

    .table thead {
        background-color: #8a2be2;
        color: #ffff;
    }

    .datatable tbody td {
        padding: 5px !important;
    }

    .datatable {
        border-right: solid 2px #000;
        border-left: solid 2px #000;
    }
	

    .btn-secondary {
        background-color: #42047e !important;
    }
    
    th { font-size: 13px; }
    td { font-size: 13px; }

    /* menghilangkan padding */
    .content-header {
        padding: 0 !important;
    }

</style>


@section('content')


<!-- Sweetalert delete -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!--  -->

<div class="content-wrapper">


    <!-- Status -->
    @if (session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>

        <!-- tambahan notifikasinya untuk delete di index -->
        <script>
            Swal.fire({
					title: 'Deleted!',
					text: 'Data has been deleted. {{session('status')}}',
					icon: 'success',
					confirmButtonText: 'OK'
				})
        </script>
        <!-- tutupannya -->

    @endif

    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">

                <div class="form-group row" style="padding-left:20px">
                    <div class="col-md-2">
                      <label><strong>Cabang :</strong></label>
                      <select name="cbg" id="cbg" class="form-control cbg" style="width: 200px">
                          <option value="">--Pilih Cabang--</option>
                          @foreach($cbg as $cbgD)
                              <option value="{{$cbgD->KODE}}"  {{ (session()->get('filter_cbg') == $cbgD->KODE) ? 'selected' : '' }}>{{$cbgD->KODE}}</option>
                          @endforeach
                      </select>
                    </div>

                    <div class="col-md-2">
                        <label><strong>Tanggal :</strong></label>
                        <input class="form-control date" id="tgl" name="tgl" type="text" autocomplete="off" value="{{ session('filter_tgl') ? date('d-m-Y', strtotime(session('filter_tgl'))) : date('d-m-Y') }}">
                    </div>
                </div>

                <div class="form-group row" style="padding-left:20px">
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary" id="btnTampil" style="white-space: nowrap;">
                            Tampilkan
                        </button>
                    </div>
                </div>

                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>				
                            <th scope="col" style="text-align: center">No. Bukti#</th>
                            <th scope="col" style="text-align: center">Tangal</th>
                            <th scope="col" style="text-align: center">Sub Item</th>
                            <th scope="col" style="text-align: center">Nama Barang</th>
                            <th scope="col" style="text-align: center">Ket. Uk</th>
                            <th scope="col" style="text-align: right">Harga Lama</th>
                            <th scope="col" style="text-align: right">Harga Baru</th>
                            <th scope="col" style="text-align: center">Posted</th>
                            <th scope="col" style="text-align: center">Tanggal Post</th>
                            <th scope="col" style="text-align: center">User</th>
                        </tr>
                    </thead>
    
                    <tbody>
                    </tbody> 
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
@endsection

@section('javascripts')

<!-- filter kolom di index -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- batas filter  -->

<script>
  $(document).ready(function() {
	  

			  
        var dataTable = $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            // 'scrollX': true,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            deferLoading: 0,
            ajax: 
            {
                url: "{{ route('get-rekaprak') }}",
                data: function(d) {
                    d.cbg = $('#cbg').val();
                    d.tgl = $('#tgl').val();
                }
            },

            columns: 
            [
                {data: 'NO_BUKTI', name: 'NO_BUKTI'},
                {data: 'TGL', name: 'TGL'},								
                {data: 'KD_BRG', name: 'KD_BRG'},
                {data: 'NA_BRG', name: 'NA_BRG',
                    render : function ( data, type, row, meta )
                    {
                        return ' <h5><span class="badge badge-pill badge-warning">' + data + '</span></h5>';
                    }
                },
                {data: 'KET_UK', name: 'KET_UK'},
                {data: 'HJ_LAMA', name: 'HJ_LAMA', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
                {data: 'HJ_BARU', name: 'HJ_BARU', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
                { data: 'POSTED', name: 'POSTED',
                  render : function(data, type, row, meta) {
                    if(row['POSTED']=="0"){
                        return '';
                    }else{
                        return '<input type="checkbox" checked style="pointer-events: none; transform: scale(2);">';
                    }
                  }
                },
                {data: 'TGL_POSTED', name: 'TGL_POSTED'},
                {data: 'USRNM', name: 'USRNM'},
            ],
            columnDefs: 
            [
                {
                    "className": "dt-center", 
                    "targets": [0,1,2,3,4,7,8,9]
                },
                {
                    "className": "dt-right", 
                    "targets": [5,6]
                },	
                {
                  targets: 1,
                  render: $.fn.dataTable.render.moment( 'DD-MM-YYYY' )
                },
                {
                    targets: 8,
                    render: $.fn.dataTable.render.moment('YYYY-MM-DD HH:mm:ss','DD-MM-YYYY')
                }
            ],
            lengthMenu: 
            [
                [8, 10, 20, 50, 100, -1],
                [8, 10, 20, 50, 100, "All"]
            ],
            dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
        });
        
        $('.date').datepicker({
            dateFormat: 'dd-mm-yy'
        });

        // tombol tampil ditekan
        $('#btnTampil').on('click', function() {
            dataTable.ajax.reload();
        });
    });

</script>
@endsection
