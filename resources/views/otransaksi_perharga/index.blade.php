@extends('layouts.plain')
@section('styles')
<link rel="stylesheet" href="{{url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
<link rel="stylesheet" href="{{url('https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css') }}">

@endsection

<style>
    th { font-size: 13px; }
    td { font-size: 13px; }

    /* menghilangkan padding */
    .content-header {
        padding: 0 !important;
    }

    .badge-warning {
        background-color: #06ba00 !important; /* Warna default badge-warning (kuning) */
        color: white !important; /* Warna teks putih */
    }
</style>

@section('content')

<!-- Sweetalert delete -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!--  -->

<div class="content-wrapper">

    <!-- Status -->
    <!-- @if (session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>

        <script>
            Swal.fire({
              title: 'Deleted!',
              text: 'Data has been deleted. {{session('status')}}',
              icon: 'success',
              confirmButtonText: 'OK'
            })
        </script>
    @endif -->
    <!-- tutupannya -->


    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">

                <!-- Filter Sub -->
                <form method="POST" id="entri" action="{{url('perharga/proses')}}">
                    @csrf
                    <div class="form-group row" style="padding-left:20px">
                        <label><strong>Nama File:</strong></label>
                        <div class="col-md-2">
                            <input class="form-control nafile" id="nafile" name="nafile"
                            type="text" autocomplete="off" value="{{ session()->get('nafile') }}"> 
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-warning" id="btnTampil" style="white-space: nowrap;">
                                Tampilkan
                            </button>
                            <button type="button" class="btn btn-success" id="btnProses" style="white-space: nowrap;">
                                Prosses
                            </button>
                            <button type="button" class="btn btn-primary" id="btnPrint" style="white-space: nowrap;">
                                Print
                            </button>
                        </div>
                    </div>
                </form>
                <!-- Tutup Filter Sub -->

                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="text-align: center">No</th>
				     		<th scope="col" style="text-align: center">-</th>
                            <th scope="col" style="text-align: center">Sub Item</th>
                            <th scope="col" style="text-align: center">Nama Barang</th>
							<th scope="col" style="text-align: center">Ukuran</th>
							<th scope="col" style="text-align: center">Kemasan</th>
                            <th scope="col" style="text-align: right">H Jual</th>
                            <th scope="col" style="text-align: right">LPH</th>
                            <th scope="col" style="text-align: center">Usul</th>
                            <th scope="col" style="text-align: center">Kdlaku</th>
                            <th scope="col" style="text-align: center">KLK</th>
                            <th scope="col" style="text-align: right">DTR</th>
                            <th scope="col" style="text-align: right">Srmin</th>
                            <th scope="col" style="text-align: right">Srmax</th>
                            <th scope="col" style="text-align: right">Srmax</th>
                            <th scope="col" style="text-align: right">MO</th>
                            <th scope="col" style="text-align: center">Supplier</th>
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
<script src="{{url('AdminLTE/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{url('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>
<script src="{{url('http://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js') }}"></script>
<!-- Buttons dan Export -->
<script src="{{url('https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js') }}"></script>
<script src="{{url('https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js') }}"></script>
<script src="{{url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js') }}"></script>


<!-- filter kolom di index -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- batas filter  -->

<script>

    $(document).ready(function() {
		$('.date').datepicker({  
			dateFormat: 'dd-mm-yy'
		}); 
	});

    $('#btnTampil').on('click', function () {

        console.log($('#nafile').val());
        let data = {
            jenis: 'BRG',
            type: 'HRG',
            na_file: $('#nafile').val(),
            _token: '{{ csrf_token() }}'
        };


        // Hancurkan DataTable sebelumnya jika ada
        if ($.fn.DataTable.isDataTable('.datatable')) {
            $('.datatable').DataTable().destroy();
        }

        dataTable = $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            lengthMenu: [[-1], ["All"]],
            autoWidth: false,
            'scrollX': true,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax:
            {
                url: '{{ route('perharga-tampil') }}',
                type: 'POST',
                data: data
            },
            columns:
            [
                {  data: 'DT_RowIndex', orderable: false, searchable: false },
                {   data: 'action', name: 'action' },
                {   data: 'kd_brg', name: 'kd_brg' },
                {   data: 'na_brg', name: 'na_brg',
                    render : function ( data, type, row, meta )
                    {
                        return ' <h5><span class="badge badge-pill badge-warning">' + data + '</span></h5>';
                    }
                },
                {   data: 'ket_uk', name: 'ket_uk' },
                {   data: 'ket_kem', name: 'ket_kem' },
                {   data: 'hj', name: 'hj', render: $.fn.dataTable.render.number( ',', '.', 0, '' ) },
                {   data: 'lph', name: 'lph', render: $.fn.dataTable.render.number( ',', '.', 2, '' ) },
                {   data: 'lhusul', name: 'lhusul' },
                {   data: 'kdlaku', name: 'kdlaku' },
                {   data: 'klk', name: 'klk'},
                {   data: 'dtr', name: 'dtr', render: $.fn.dataTable.render.number( ',', '.', 0, '' ) },
                {   data: 'srmin', name: 'srmin', render: $.fn.dataTable.render.number( ',', '.', 2, '' ) },
                {   data: 'srmax', name: 'srmax', render: $.fn.dataTable.render.number( ',', '.', 2, '' ) },
                {   data: 'MO', name: 'MO', render: $.fn.dataTable.render.number( ',', '.', 0, '' ) },
                {   data: 'supp', name: 'supp'}
            ],
            columnDefs: [
                {
                    "className": "dt-center",
                    "targets": [0,1,2,3,4,5,8,9,10,16]
                },
                {
                    "className": "dt-right",
                    "targets": [6,7,11,12,13,14,15]
                },
            ],
                dom: "<'row'<'col-md-6'l><'col-md-6'f>>" +
                    "<'row'<'col-md-12'B>>" +
                    "<'row'<'col-md-12'tr>>" +
                    "<'row'<'col-md-5'i><'col-md-7'p>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Data Galeri'
                    }
                ]

                // stateSave:false,
        });

        // Trigger reload saat nilai filter berubah
        $('#btnFilterSub').on('click', function() {
            $('#datatable').DataTable().ajax.reload();
        });

        // $("div.test_btn").html('<a class="btn btn-lg btn-md btn-success" href="{{url('ubl/edit?idx=0&tipx=new')}}"> <i class="fas fa-plus fa-sm md-3" ></i></a');
    });

    $('#btnProses').on('click', function () {
        $.ajax({
            url: '{{ url("perkem/proses") }}',
            type: 'POST',
            data: {
                na_file: $('#nafile').val(),
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                alert('Data berhasil di sahkan!');
                // bisa juga tampilkan response untuk debug
                console.log(response);
                $('#datatable').DataTable().ajax.reload();
            },
            error: function (xhr) {
                alert('Gagal sahkan data.');
                console.error(xhr.responseText);
            }
        });
    });

    function deleteRow(link) {
        console.log('Masuk');
        Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = link;
            }
        });
    }

</script>
@endsection
