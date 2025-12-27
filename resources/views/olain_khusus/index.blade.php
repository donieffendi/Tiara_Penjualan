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
        background-color: #FFFFFF;
        color: #000000;
    }


    .datatable tbody td {
        padding: 5px !important;
        background-color: #FFFFFF;
    }

    .datatable {
        border-right: solid 2px #000;
        border-left: solid 2px #000;
    }


    .btn-secondary {
        background-color: #42047e !important;
    }

    th {
        font-size: 12px;
    }

    td {
        font-size: 12px;
    }

    /* menghilangkan padding */
    .content-header {
        padding: 0 !important;
    }

    .badge-warning {
        background-color: #5a01d5 !important;
        /* Warna default badge-warning (kuning) */
        color: white !important;
        /* Warna teks putih */
    }

    .badge-success {
        background-color: #068f3f !important;
        /* Warna default badge-warning (kuning) */
        color: white !important;
        /* Warna teks putih */
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
            text: "Data has been deleted. {{session('status')}}",
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

                            <form method="POST" id="entri" action="{{url('khusus/posting')}}">

                                <input name="flagz" class="form-control flagz" id="flagz" value="{{$flagz}}" hidden>

                                <!-- <button class="btn btn-danger" type="button"  onclick="simpan()">Posting</button> -->

                                <table class="table table-fixed table-striped table-border table-hover nowrap datatable"
                                    id="datatable">


                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col" style="text-align: center">#</th>
                                            <th scope="col" style="text-align: center">-</th>
                                            <th scope="col" style="text-align: center">No. Bukti</th>
                                            <th scope="col" style="text-align: center">Tgl</th>
                                            <th scope="col" style="text-align: center">Kode</th>
                                            <th scope="col" style="text-align: center">Supplier</th>
                                            <th scope="col" style="text-align: center">Notes</th>
                                            <th scope="col" style="text-align: center">Cbg</th>
                                            <th scope="col" style="text-align: center">Nama File</th>
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
            "order": [
                [0, "asc"]
            ],
            ajax: {
                url: "{{ route('get-khusus') }}",
                data: {
                    flagz: $('#flagz').val(),
                }
            },

            columns: [
                {
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action'
                },
                {
                    data: 'NO_BUKTI',
                    name: 'NO_BUKTI',
                    render: function(data, type, row, meta) {
                        return ' <h5><span class="badge badge-pill badge-warning">' + data +
                            '</span></h5>';
                    }
                },
                {
                    data: 'TGL',
                    name: 'TGL'
                },
                {
                    data: 'KODES',
                    name: 'KODES'
                },
                {
                    data: 'NAMAS',
                    name: 'NAMAS'
                },
                {
                    data: 'NOTES',
                    name: 'NOTES'
                },
                {
                    data: 'CBG',
                    name: 'CBG'
                },
                {
                    data: 'NA_FILE',
                    name: 'NA_FILE'
                }
            ],
            columnDefs: [{
                    "className": "dt-center",
                    "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8]
                },
                {
                    targets: 3,
                    render: $.fn.dataTable.render.moment('DD-MM-YYYY')
                },
            ],
            lengthMenu: [
                [8, 10, 20, 50, 100, -1],
                [8, 10, 20, 50, 100, "All"]
            ],
            dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'> <'col-md-4'f>>" +
                "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
            stateSave: true,

        });

        $("div.test_btn").html(
            `<a class="btn btn-lg btn-md btn-success" href="{{url('khusus/edit?flagz='.$flagz.'&idx=0&tipx=new')}}"><i class="fas fa-plus fa-sm md-3"></i></a>`
        );

        // tutupanya
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