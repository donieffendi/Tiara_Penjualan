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
                            <form id="entri" method="POST" action="{{url('postlbkt/posting')}}">
                                @csrf	  
                                <button class="btn btn-danger" type="button"  onclick="simpan()">Proses</button>

                                    <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                                        <thead class="table-dark">
                                            <tr>
                                                <th scope="col" style="text-align: center">No.</th>					
                                                <th scope="col" style="text-align: center">No. Bukti</th>
                                                <th scope="col" style="text-align: center">No. Posting</th>
                                                <th scope="col" style="text-align: center">Tanggal</th>
                                                <th scope="col" style="text-align: center">Notes</th>
                                                <th scope="col" style="text-align: right">Total</th>
                                                <th scope="col" style="text-align: center">Cek</th>
                                            </tr>
                                        </thead>
                        
                                        <tbody>
                                        </tbody> 
                                    </table>
                            </form>
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
            ajax: 
            {
                url: "{{ route('get-postlbkt') }}",
				// data: 
                // {
                //     flagz : $('#flagz').val(),
				   
                // }
            },

            columns: 
            [

                { data: 'DT_RowIndex', orderable: false, searchable: false },
                {data: 'NO_BUKTI', name: 'NO_BUKTI',
                  render : function ( data, type, row, meta )
                  {
                    return ' <h5><span class="badge badge-pill badge-warning">' + data + '</span></h5>';
                  }
                },
                {data: 'BUKT', name: 'BUKT'},
                {data: 'TGL', name: 'TGL'},
                {data: 'NOTES', name: 'NOTES'},
                {data: 'TOTAL_QTY', name: 'TOTAL_QTY', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
                {data: 'cek', name: 'cek'},	
            ],
            columnDefs: 
            [
                {
                    "className": "dt-center", 
                    "targets": [0,1,2,3,4,6]
                },
                {
                    "className": "dt-right", 
                    "targets": 5
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
    });
	
    function simpan() {
        let ids = [];
        $("input[name='cek[]']:checked").each(function() {
            ids.push($(this).val());
        });

        if (ids.length == 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Tidak ada data yang dipilih!'
            });
            return;
        }

        // taruh ids di input hidden
        $('<input>').attr({
            type: 'hidden',
            name: 'ids',
            value: JSON.stringify(ids)
        }).appendTo('#entri');

        document.getElementById("entri").submit();
    }

</script>
@endsection
