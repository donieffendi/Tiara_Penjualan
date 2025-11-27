@extends('layouts.plain')
@section('styles')
<link rel="stylesheet" href="{{url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
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
                                            value="2" id="columnKode" checked>
                                        <label class="form-check-label" for="columnKode">Kode</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="3" id="columnNama" checked>
                                        <label class="form-check-label" for="columnNama">Nama</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="4" id="columnUkr" checked>
                                        <label class="form-check-label" for="columnUkr">Ukuran</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="5" id="columnKem">
                                        <label class="form-check-label" for="columnKem">Kemasan</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="6" id="columnPPN">
                                        <label class="form-check-label" for="columnPPN">PPN</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="7" id="columnTarik">
                                        <label class="form-check-label" for="columnTarik">Tarik</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="8" id="columnExp">
                                        <label class="form-check-label" for="columnExp">Exp</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="9" id="columnSupp">
                                        <label class="form-check-label" for="columnSupp">Supp</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="10" id="columnNamas">
                                        <label class="form-check-label" for="columnNamas">Nama</label>
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

              <!-- batas filter -->

                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="text-align: center">No</th>
				     		<th scope="col" style="text-align: center">-</th>
                            <th scope="col" style="text-align: center">Kode</th>
                            <th scope="col" style="text-align: center">Nama</th>
							<th scope="col" style="text-align: center">Ukuran</th>
							<th scope="col" style="text-align: center">Kemasan</th>
							<th scope="col" style="text-align: center">PPN</th>
							<th scope="col" style="text-align: center">Tarik</th>
							<th scope="col" style="text-align: center">Exp</th>
							<th scope="col" style="text-align: center">SUPP</th>
							<th scope="col" style="text-align: center">Nama Supplier</th>
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

  $(document).ready(function() {
        var dataTable = $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: true,
            // 'scrollX': true,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax:
            {
                url: '{{ route('get-brg') }}'
            },
            columns:
            [
                {  data: 'DT_RowIndex', orderable: false, searchable: false },
                {
                  data: 'action',
                  name: 'action'
                },
				{data: 'KD_BRG', name: 'KD_BRG'},
                {data: 'NA_BRG', name: 'NA_BRG',
                  render : function ( data, type, row, meta )
                  {
                    return ' <h5><span class="badge badge-pill badge-warning">' + data + '</span></h5>';
                  }
                },
                {data: 'KET_UK', name: 'KET_UK'},
                {data: 'KET_KEM', name: 'KET_KEM'},
                {data: 'PPN', name: 'PPN'},
                {data: 'TARIK', name: 'TARIK'},
                {data: 'MASA_EXP', name: 'MASA_EXP'},
                {data: 'SUPP', name: 'SUPP'},
                {data: 'NAMAS', name: 'NAMAS'},
            ],
            columnDefs: [
                {
                    // "className": "dt-center",
                    // "targets": [0,1,2,3]
                },
            ],
            dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",

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

        $("div.test_btn").html('<a class="btn btn-lg btn-md btn-success" href="{{url('brg/edit?idx=0&tipx=new')}}"> <i class="fas fa-plus fa-sm md-3" ></i></a');
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
