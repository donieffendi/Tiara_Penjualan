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

                <form method="POST" id="entri" action="{{url('brg-baru/proses')}}">
                    @csrf
                    <!-- Tampilkan Filename -->
                    <div class="form-group row" style="padding-left:20px">
                        <label><strong>FileName :</strong></label>
                        <div class="col-md-2">
                            <input class="form-control nama_file" id="nama_file" name="nama_file"
                            type="text" autocomplete="off" value="{{ session()->get('filter_namaFile') }}"> 
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-warning" id="btnFilterNamaFile" style="white-space: nowrap;">
                                Tampilkan
                            </button>
                            <button type="button" class="btn btn-success" id="btnPrint" style="white-space: nowrap;">
                                Print
                            </button>
                            <button type="button" class="btn btn-primary" id="btnFilterNamaFile" style="white-space: nowrap;">
                                Proses
                            </button>
                        </div>
                    </div>
                </form>

              <!-- filter kolom di index -->

                <!-- Button to open modal -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#columnModal">
                    Filter Columns
                </button>
                <!-- Modal -->
                <div class="modal fade" id="columnModal" tabindex="-1" aria-labelledby="columnModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="columnModalLabel">Toggle Columns</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close">X</button>
                            </div>
                            <div class="modal-body">
                                <!-- Column visibility checkboxes -->
                                <form id="columnToggleForm">
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="0" id="columnNo" checked>
                                        <label class="form-check-label" for="columnNo">No</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="1" id="columnAction" checked>
                                        <label class="form-check-label" for="columnAction">Action</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="2" id="columnSub" checked>
                                        <label class="form-check-label" for="columnSub">Sub</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="3" id="columnKode" checked>
                                        <label class="form-check-label" for="columnKode">Kode</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="4" id="columnNama" checked>
                                        <label class="form-check-label" for="columnNama">Nama</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="5" id="columnUkuran" checked>
                                        <label class="form-check-label" for="columnUkuran">Ukuran</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="6" id="columnKemasan" checked>
                                        <label class="form-check-label" for="columnKemasan">Kemasan</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="7" id="columnSuplier" checked>
                                        <label class="form-check-label" for="columnSuplier">Suplier</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="8" id="columnHarga" checked>
                                        <label class="form-check-label" for="columnHarga">Harga yang Ditawarkan</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="9" id="columnDisc1" >
                                        <label class="form-check-label" for="columnDisc1">Disc.1</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="10" id="columnDisc2" >
                                        <label class="form-check-label" for="columnDisc2">Disc.2</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="11" id="columnDisc3" >
                                        <label class="form-check-label" for="columnDisc3">Disc.3</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="12" id="columnLPHGZ" >
                                        <label class="form-check-label" for="columnLPHGZ">LPH GZ</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="13" id="columnLPHSO" >
                                        <label class="form-check-label" for="columnLPHSO">LPH SO</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="14" id="columnLPHTM" >
                                        <label class="form-check-label" for="columnLPHTM">LPH TM</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="15" id="columnDTR" >
                                        <label class="form-check-label" for="columnDTR">DTR</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="16" id="columnKdlaku" >
                                        <label class="form-check-label" for="columnKdlaku">Kdlaku</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="17" id="columnType" >
                                        <label class="form-check-label" for="columnType">Type</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="18" id="columnMinToko" >
                                        <label class="form-check-label" for="columnMinToko">Min Toko</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="19" id="columnMaxToko" >
                                        <label class="form-check-label" for="columnMaxToko">Max Toko</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="20" id="columnMinGdg" >
                                        <label class="form-check-label" for="columnMinGdg">Min Gdg</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="21" id="columnMaxGdg" >
                                        <label class="form-check-label" for="columnMaxGdg">Max Gdg</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="22" id="columnHJGZ" >
                                        <label class="form-check-label" for="columnHJGZ">HJ_GZ</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="23" id="columnHBGZ" >
                                        <label class="form-check-label" for="columnHBGZ">HB_GZ</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="24" id="columnKLK" >
                                        <label class="form-check-label" for="columnKLK">KLK</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="25" id="columnTarik" >
                                        <label class="form-check-label" for="columnTarik">Tarik</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="26" id="columnMasa" >
                                        <label class="form-check-label" for="columnMasa">Masa</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="27" id="columnKemPabrik" >
                                        <label class="form-check-label" for="columnKemPabrik">Kem Pabrik</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="28" id="columnKMP1" >
                                        <label class="form-check-label" for="columnKMP1">KMP 1</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox" value="29" id="columnKMP2" >
                                        <label class="form-check-label" for="columnKMP2">KMP 2</label>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary"
                                    id="applyColumnToggle">Apply</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div></div>

              <!-- batas filter -->

                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="text-align: center">No</th>
				     		<th scope="col" style="text-align: center">-</th>
				     		<th scope="col" style="text-align: center">Sub</th>
                            <th scope="col" style="text-align: center">Kode</th>
                            <th scope="col" style="text-align: center">Nama</th>
                            <th scope="col" style="text-align: center">Ukuran</th>
							<th scope="col" style="text-align: center">Kemasan</th>
                            <th scope="col" style="text-align: center">Suplier</th>
							<th scope="col" style="text-align: center">Harga yang Ditawarkan</th>
                            <th scope="col" style="text-align: center">Disc.1</th>
                            <th scope="col" style="text-align: center">Disc.2</th>
                            <th scope="col" style="text-align: center">Disc.3</th>
							<th scope="col" style="text-align: center">LPH GZ</th>
							<th scope="col" style="text-align: center">LPH SO</th>
							<th scope="col" style="text-align: center">LPH TM</th>
							<th scope="col" style="text-align: center">DTR</th>
							<th scope="col" style="text-align: center">Kdlaku</th>
							<th scope="col" style="text-align: center">Type</th>
							<th scope="col" style="text-align: center">Min Toko</th>
							<th scope="col" style="text-align: center">Max Toko</th>
							<th scope="col" style="text-align: center">Min Gdg</th>
							<th scope="col" style="text-align: center">Max Gdg</th>
							<th scope="col" style="text-align: center">HJ_GZ</th>
							<th scope="col" style="text-align: center">HB_GZ</th>
							<th scope="col" style="text-align: center">KLK</th>
							<th scope="col" style="text-align: center">Tarik</th>
							<th scope="col" style="text-align: center">Masa</th>
							<th scope="col" style="text-align: center">Kem Pabrik</th>
							<th scope="col" style="text-align: center">KMP 1</th>
							<th scope="col" style="text-align: center">KMP 2</th>
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

<!-- filter kolom di index -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- batas filter  -->

<script>

  // filter kolom di index
    window.addEventListener('message', (event) => {
      if (event.origin !== window.location.origin) {
          console.warn('Origin mismatch!');
          return;
      }

      const currentData = event.data;
      console.log(currentData); // Use currentData as needed
    });
  // batas filter

    $('#btnTampil').on('click', function () {

        console.log($('#nafile').val());
        let data = {
            jenis: 'BRG',
            type: 'NEW',
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
            autoWidth: false,
            'scrollX': true,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax:
            {
                url: '{{ route('get-brg-baru') }}',
                data: function (d) {
                    d.nama_file = $('#nama_file').val();
                }
            },
            columns:
            [
                {  data: 'DT_RowIndex', orderable: false, searchable: false },
                {   data: 'action', name: 'action' },
				{   data: 'SUB', name: 'SUB' },
				{   data: 'KD_BRG', name: 'KD_BRG' },
                {   data: 'NA_BRG', name: 'NA_BRG',
                    render : function ( data, type, row, meta )
                    {
                        return ' <h5><span class="badge badge-pill badge-warning">' + data + '</span></h5>';
                    }
                },
                {   data: 'KET_UK', name: 'KET_UK' },
                {   data: 'KET_KEM', name: 'KET_KEM' },
                {   data: 'SUPP', name: 'SUPP' },
                {   data: 'HB', name: 'HB' },
                {   data: 'D1', name: 'D1' },
                {   data: 'D2', name: 'D2' },
                {   data: 'D3', name: 'D3' },
                {   data: 'LPH_GZ', name: 'LPH_GZ' },
                {   data: 'LPH_SO', name: 'LPH_SO' },
                {   data: 'LPH_TM', name: 'LPH_TM' },
                {   data: 'DTR', name: 'DTR' },
                {   data: 'KDLAKU', name: 'KDLAKU' },
                {   data: 'TYPE', name: 'TYPE' },
                {   data: 'MIN_TOKO', name: 'MIN_TOKO' },
                {   data: 'MAX_TOKO', name: 'MAX_TOKO' },
                {   data: 'MIN_GDG', name: 'MIN_GDG' },
                {   data: 'MAX_GDG', name: 'MAX_GDG' },
                {   data: 'HJ_GZ', name: 'HJ_GZ' },
                {   data: 'HB_GZ', name: 'HB_GZ' },
                {   data: 'KLK', name: 'KLK' },
                {   data: 'TARIK', name: 'TARIK' },
                {   data: 'MASA_TARIK', name: 'MASA_TARIK' },
                {   data: 'KEM_P', name: 'KEM_P' },
                {   data: 'KMP1', name: 'KMP1' },
                {   data: 'KMP2', name: 'KMP2' },
            ],
            columnDefs: [
                {
                    "className": "dt-center",
                    "targets": 0
                },
            ],
                dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",

			    stateSave:false,
        });


        // filter kolom di index
        // Handle column visibility toggle
        $('#applyColumnToggle').on('click', function() {
            $('#columnToggleForm .column-checkbox').each(function() {
                var column = dataTable.column($(this).val());
                column.visible($(this).is(':checked'));
            });
            $('#columnModal').modal('hide'); // Close the modal
        });

        $('#columnToggleForm .column-checkbox').each(function() {
            var column = dataTable.column($(this).val());
            column.visible($(this).is(':checked'));
        });

        // batas filter

        // $("div.test_btn").html('<a class="btn btn-lg btn-md btn-success" href="{{url('brg/edit?idx=0&tipx=new')}}"> <i class="fas fa-plus fa-sm md-3" ></i></a');
    });

    $('#btnProses').on('click', function () {
        $.ajax({
            url: '{{ url("brg-baru/proses") }}',
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
