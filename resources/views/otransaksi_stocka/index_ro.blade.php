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

    .table tbody:nth-child(2) {
        background-color: #d3ffce;
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
<div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
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
                                            value="2" id="columnBukti" checked>
                                        <label class="form-check-label" for="columnBukti">Bukti#</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="3" id="columnTgl" checked>
                                        <label class="form-check-label" for="columnTgl">Tgl</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="4" id="columnTqty">
                                        <label class="form-check-label" for="columnTqty">Total-Qty</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="5" id="columnNotes">
                                        <label class="form-check-label" for="columnNotes">Notes</label>
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

              <input name="flagz"  class="form-control flagz" id="flagz" value="{{$flagz}}" hidden >

                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="text-align: center">#</th>
				     		            <th scope="col" style="text-align: center">-</th>
                            <th scope="col" style="text-align: left">Bukti#</th>
                            <th scope="col" style="text-align: left">Tgl</th>
                            <th scope="col" style="text-align: right">Total_Qty</th>
                            <th scope="col" style="text-align: left">Notes</th>
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
            autoWidth: false,
            // 'scrollX': true,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax:
            {
                url: "{{ route('get-stocka') }}",
				        data:
                {
                    flagz : $('#flagz').val(),

                }
            },

            columns:
            [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
			          { data: 'action', name: 'action'},
                {data: 'NO_BUKTI', name: 'NO_BUKTI'},
                {data: 'TGL', name: 'TGL'},
                {
                  data: 'TOTAL_QTY',
                  name: 'TOTAL_QTY',
                  render: $.fn.dataTable.render.number( ',', '.', 0, '' )
				        },
                {data: 'NOTES', name: 'NOTES'}
            ],
            columnDefs:
            [
                {
                    "className": "dt-center",
                    "targets": [0,1,2,5]
                },
                {
                    "className": "dt-right",
                    "targets": 4
                },
                {
                  targets: 3,
                  render: $.fn.dataTable.render.moment( 'DD-MM-YYYY' )
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

        $("div.test_btn").html('<a class="btn btn-lg btn-md btn-success" href="{{url('stocka/edit_ro?flagz='.$flagz.'&idx=0&tipx=new')}}"> <i class="fas fa-plus fa-sm md-3" ></i></a');
    });

</script>
@endsection
