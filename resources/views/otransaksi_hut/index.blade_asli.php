@extends('layouts.plain')
@section('styles')
<!-- <link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}"> -->
<link rel="stylesheet" href="{{asset('foxie_js_css/jquery.dataTables.min.css')}}" />



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

				<input name="flagz"  class="form-control flagz" id="flagz" value="{{$flagz}}" hidden >


                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="text-align: center"></th>
                            <th scope="col" style="text-align: center">#</th>
				     		            <th scope="col" style="text-align: center">-</th>							
                            <th scope="col" style="text-align: center">Bukti#</th>
                            <th scope="col" style="text-align: center">Tgl</th>
                            
                            <th scope="col" style="text-align: center">Suplier#</th>
                            <th scope="col" style="text-align: center">Nama</th>
							              <th scope="col" style="text-align: center">Kota</th>
                            <th scope="col" style="text-align: center">Bayar</th>
                            <th scope="col" style="text-align: center">Notes</th>
                            <th scope="col" style="text-align: center">Posted</th>
                           
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
<script>
  $(document).ready(function() {
        var dataTable = $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            // 'scrollX': true,
            // 'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax: 
            {
                url: "{{ route('get-hut') }}",
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

                {data: 'DT_RowIndex', orderable: false, searchable: false },
			          {data: 'action', name: 'action'},
                {data: 'NO_BUKTI', name: 'NO_BUKTI'},
                {data: 'TGL', name: 'TGL'},
                //{data: 'NO_PO', name: 'NO_PO'},
                {data: 'KODES', name: 'KODES'},
                {data: 'NAMAS', name: 'NAMAS',
                  render : function ( data, type, row, meta )
                  {
                    return ' <h5><span class="badge badge-pill badge-warning">' + data + '</span></h5>';
                  }
                },
                {data: 'KOTA', name: 'KOTA'},
                {data: 'BAYAR', name: 'BAYAR', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},				
                {data: 'NOTES', name: 'NOTES'},
                { data: 'POSTED', name: 'POSTED',
                  render : function(data, type, row, meta) {
                    if(row['POSTED']=="0"){
                        return '';
                    }else{
                        return '<input type="checkbox" checked style="pointer-events: none;">';
                    }
                  }
                },

            ],

            columnDefs: 
            [
                {
                    "className": "dt-center", 
                    "targets": 0
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
				stateSave:true,

        });
		
        $("div.test_btn").html('<a class="btn btn-lg btn-md btn-success" href="{{url('hut/edit?flagz='.$flagz.'&idx=0&tipx=new')}}"> <i class="fas fa-plus fa-sm md-3" ></i></a');

        // function buat ganti tombol + onclick
        window.toggleButton = function(button) {
            const no_bukti = $(button).data('no_bukti'); // Get the no_bukti from data attribute

            if (button.innerText === '+') {
                button.innerText = '-';
                button.classList.remove('btn-success');
                button.classList.add('btn-danger');

                // Fetch and show detail data using no_bukti
                $.ajax({
                    url: '{{ route('get-detail-hut') }}', // Define the route to fetch detail data
                    method: 'GET',
                    data: {
                        no_bukti: no_bukti
                    }, // Pass no_bukti in the request
                    success: function(response) {
                        console.log(response);

                        // sesuaikan disini (1) untuk total
                        let totalTotal = 0;
                        // batas 

                        let detailHtml = `
                            <div class="p-3">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Faktur#</th>
                                            <th>Total</th>
                                            <th>Bayar</th>
                                            <th>Sisa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                        response.forEach((item, index) => {

                            // sesuaikan disini (2)
                            totalTotal += parseFloat(item.TOTAL);
                            // batas

                            detailHtml += `
                                <tr>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem;">${index + 1}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem;">${item.NO_FAKTUR}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: right">${parseFloat(item.TOTAL).toFixed(2)}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: right">${parseFloat(item.BAYAR).toFixed(2)}</div></td>
                                    <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: right">${parseFloat(item.SISA).toFixed(2)}</div></td>
                                </tr>
                            `;
                        });

                        detailHtml += `
                                    <tr>
                                        <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                                        <td><div style="background-color: #f7d8b4; padding: 0.5rem; text-align: right">${totalTotal.toFixed(2)}</div></td>
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
	
</script>
@endsection
