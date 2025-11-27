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
</style>

<style>
    .x{
        Color : red
    }

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

              <!-- filter kolom di index -->

                <!-- Button to open modal -->
                <div class="form-group row">
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#columnModal">
                            Filter Columns
                        </button>
                    </div>
                    <div class="col-md-1">
                        <form method="POST" id="postingForm" action="{{ url('posting_stock_kirim/') }}">
                            @csrf
                            <button class="btn btn-primary" type="submit">Posted</button>
                        </form>
                    </div>
                </div>
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
                                            value="0" id="columnDetail" checked>
                                        <label class="form-check-label" for="columnDetail">Detail</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="1" id="columnNo" checked>
                                        <label class="form-check-label" for="columnNo">No</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="2" id="columnAction" checked>
                                        <label class="form-check-label" for="columnAction">Action</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="3" id="columnBukti" checked>
                                        <label class="form-check-label" for="columnBukti">Bukti#</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="4" id="columnTgl" checked>
                                        <label class="form-check-label" for="columnTgl">Tgl</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="5" id="columnTqty">
                                        <label class="form-check-label" for="columnTqty">Total-Qty</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-checkbox" type="checkbox"
                                            value="6" id="columnNotes">
                                        <label class="form-check-label" for="columnNotes">Posted</label>
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

              <input name="flagz"  class="form-control flagz" id="flagz" value="KM" hidden >

                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="text-align: center"></th>
                            <th scope="col" style="text-align: center">#</th>
				     		<th scope="col" style="text-align: center">-</th>
                            <th scope="col" style="text-align: left">Bukti#</th>
                            <th scope="col" style="text-align: left">Tgl</th>
                            <th scope="col" style="text-align: right">Total_Qty</th>
                            <th scope="col" style="text-align: left">Posted</th>
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
                url: "{{ route('get-kirim') }}",
				        data:
                {
                    flagz : $('#flagz').val(),

                }
            },

            columns:
            [
                //add tombol +
                {
                    data: null, // Column for the button
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {

                      // tanpa ada POST (posting) di atas
                        return `<button class="btn btn-success btn-sm toggle-button" data-no_bukti="${row.NO_BUKTI}" onclick="toggleButton(this)">+</button>`;
                    }
                },
                  // tutupannya

                { data: 'DT_RowIndex', orderable: false, searchable: false },
			          { data: 'action', name: 'action'},
                {data: 'NO_BUKTI', name: 'NO_BUKTI',
                  render : function ( data, type, row, meta )
                  {
                    return ' <h5><span class="x">' + data + '</span></h5>';
                  }
                },
                {data: 'TGL', name: 'TGL'},
                {
                  data: 'TOTAL_QTY',
                  name: 'TOTAL_QTY',
                  render: $.fn.dataTable.render.number( ',', '.', 0, '' )
				        },
                        {
                        data: 'POSTED',
                        name: 'POSTED',
                        render: function(data, type, row, meta) {
                            let checked = row['POSTED'] == 1 ? "checked" : "";
                            let id = row['NO_ID'] ? row['NO_ID'] : 'undefined';

                            return `
            <div class="form-check d-flex align-items-center" style="margin-top: -3px;">
                <input type="checkbox" class="form-check-input update-posted" data-id="${id}" id="checkbox-${id}" ${checked} style="transform: scale(2);">
                <label class="form-check-label" for="checkbox-${id}"></label>
            </div>
        `;
                        }
                    }
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
                  targets: 4,
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

        // $("div.test_btn").html('<a class="btn btn-lg btn-md btn-success" href="{{url('kirim/edit?flagz='.$flagz.'&idx=0&tipx=new')}}"> <i class="fas fa-plus fa-sm md-3" ></i></a');

        // function buat ganti tombol + onclick
        window.toggleButton = function(button) {
            const no_bukti = $(button).data('no_bukti'); // Get the no_bukti from data attribute

            if (button.innerText === '+') {
                button.innerText = '-';
                button.classList.remove('btn-success');
                button.classList.add('btn-danger');

                // Fetch and show detail data using no_bukti
                $.ajax({
                    url: '{{ route('get-detail-kirim') }}', // Define the route to fetch detail data
                    method: 'GET',
                    data: {
                        no_bukti: no_bukti
                    }, // Pass no_bukti in the request
                    success: function(response) {
                        console.log(response);

                        let totalQty = 0;
                        let detailHtml = `
                            <div class="p-3">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Kode</th>
                                            <th>Nama</th>
                                            <th>Satuan</th>
                                            <th>Qty-Comp</th>
                                            <th>Qty-Real</th>
                                            <th>Qty</th>
                                            <th>Ket</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                        response.forEach((item, index) => {
                            totalQty += parseFloat(item.QTY);

                            detailHtml += `
                                <tr>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem;">${index + 1}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem;">${item.KD_BRG}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem;">${item.NA_BRG}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem;">${item.SATUAN}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: right">${parseFloat(item.QTYC).toFixed(2)}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: right">${parseFloat(item.QTYR).toFixed(2)}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: right">${parseFloat(item.QTY).toFixed(2)}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem;">${item.KET}</div></td>
                                </tr>
                            `;
                        });

                        detailHtml += `
                                    <tr>
                                        <td colspan="6" style="text-align: right;"><strong>Total:</strong></td>
                                        <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: right">${totalQty.toFixed(2)}</div></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        `;

                        // Insert the detail row below the clicked row
                        var detailRow = `<tr class="detail-row">
                                <td colspan="11">${detailHtml}</td>
                              </tr>`;

                        $(button).closest('tr').after(detailRow);
                    }
                });
                } else {
                    button.innerText = '+';
                    button.classList.remove('btn-danger');
                    button.classList.add('btn-success');

                    // Remove the detail row if it exists
                    $(button).closest('tr').next('.detail-row').remove();
                }
            };

          // tutupannya
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

    $(document).on('submit', '#postingForm', function(e) {
            e.preventDefault(); // Mencegah submit form default

            let formData = {}; // Objek untuk menyimpan data POSTED

            $('.update-posted').each(function() {
                let id = $(this).data('id');
                let posted = $(this).is(':checked') ? 1 : 0;
                formData[id] = posted;
            });

            console.log("Data yang dikirim:", formData); // Debugging

            $.ajax({
                url: '/posting_stock_kirim',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    posted: formData
                },
                success: function(response) {
                    alert(response.message);
                },
                error: function(xhr) {
                    alert('Gagal memperbarui status.');
                }
            });
        });
</script>
@endsection
