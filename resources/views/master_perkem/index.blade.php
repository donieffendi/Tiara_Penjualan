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
                <form method="POST" id="entri" action="{{url('perkem/proses')}}">
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
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="2" id="columnNamafile" checked>
                                        <label class="form-check-label" for="columnNamafile">Ur</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="3" id="columnTgl" checked>
                                        <label class="form-check-label" for="columnTgl">Tgl</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="4" id="columnNamaSub" checked>
                                        <label class="form-check-label" for="columnNamaSub">Sub</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="5" id="columnItem" checked>
                                        <label class="form-check-label" for="columnItem">Item</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="6" id="columnNamab" checked>
                                        <label class="form-check-label" for="columnNamab">Nama Barang</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="7" id="columnUkuran" checked>
                                        <label class="form-check-label" for="columnUkuran">Ukuran</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="8" id="columnKemasL" checked>
                                        <label class="form-check-label" for="columnKemasL">Kemasan Lama</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="9" id="columnKemasB" checked>
                                        <label class="form-check-label" for="columnKemasB">Kemasan Baru</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="10" id="columnKLK" checked>
                                        <label class="form-check-label" for="columnKLK">KLK</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="11" id="columnKLK2" checked>
                                        <label class="form-check-label" for="columnKLK2">KLK Baru</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="12" id="columnMO" checked>
                                        <label class="form-check-label" for="columnMO">MO</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="13" id="columnMO2" checked>
                                        <label class="form-check-label" for="columnMO2">MO Baru</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="14" id="columnKetKem" checked>
                                        <label class="form-check-label" for="columnKetKem">Ket Kemasan</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="15" id="columnKMP" checked>
                                        <label class="form-check-label" for="columnKMP">Kem P</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="16" id="columnSUPL" checked>
                                        <label class="form-check-label" for="columnSUPL">SUPP Lama</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="17" id="columnSUPB" checked>
                                        <label class="form-check-label" for="columnSUPB">SUPP Baru</label>
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
				     		<th scope="col" style="text-align: center">Ur</th>
                            <th scope="col" style="text-align: center">Tgl</th>
                            <th scope="col" style="text-align: center">Sub</th>
                            <th scope="col" style="text-align: center">Item</th>
                            <th scope="col" style="text-align: center">Nama Barang</th>
							<th scope="col" style="text-align: center">Ukuran</th>
							<th scope="col" style="text-align: center">Kemasan Lama</th>
                            <th scope="col" style="text-align: center">Kemasan Baru</th>
                            <th scope="col" style="text-align: center">KLK</th>
                            <th scope="col" style="text-align: center">KLK Baru</th>
                            <th scope="col" style="text-align: center">MO</th>
                            <th scope="col" style="text-align: center">MO Baru</th>
                            <th scope="col" style="text-align: center">Ket Kemasan</th>
                            <th scope="col" style="text-align: center">Kem P</th>
                            <th scope="col" style="text-align: center">SUPP Lama</th>
                            <th scope="col" style="text-align: center">SUPP Baru</th>
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
            type: 'KMS',
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
                url: '{{ route('perkem-tampil') }}',
                type: 'POST',
                data: data
            },
            columns:
            [
                {  data: 'DT_RowIndex', orderable: false, searchable: false },
                {   data: 'action', name: 'action' },
                {   data: 'NA_FILE', name: 'NA_FILE' },
                {   data: 'TG_SMP', name: 'TG_SMP' },
                {   data: 'SUB', name: 'SUB' },
				{   data: 'KDBAR', name: 'KDBAR' },
                {   data: 'NA_BRG', name: 'NA_BRG',
                    render : function ( data, type, row, meta )
                    {
                        return ' <h5><span class="badge badge-pill badge-warning">' + data + '</span></h5>';
                    }
                },
                {   data: 'KET_UK', name: 'KET_UK' },
                {   data: 'KEM_LM', name: 'KEM_LM' },
                {   data: 'KEM_BR', name: 'KEM_BR' },
                {   data: 'KLK_LM', name: 'KLK_LM' },
                {   data: 'KLK_BR', name: 'KLK_BR' },
                {   data: 'MO_LM', name: 'MO_LM' },
                {   data: 'MO_BR', name: 'MO_BR' },
                {   data: 'KET_KEM', name: 'KET_KEM' },
                {   data: 'KEM_P', name: 'KEM_P' },
                {   data: 'SUPPLAMA', name: 'SUPPLAMA' },
                {   data: 'SUPPBARU', name: 'SUPPBARU' },
            ],
            columnDefs: [
                {
                    "className": "dt-center",
                    "targets": [0,1,2,3]
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
