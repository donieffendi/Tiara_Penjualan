@extends('layouts.plain')
@section('styles')
    <link rel="stylesheet" href="{{ url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Approval Usulan </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item active">Approval Usulan</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status -->
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
        @if (session('gagal'))
            <div class="alert alert-danger">
                {{ session('gagal') }}
            </div>
        @endif

        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="{{ route('ubhnpost1') }}">
                                    @csrf
                                    <!-- <button class="btn btn-success" type="button"  onclick="simpan()">Approv</button>

                                                    <button class="btn btn-danger" type="button"  onclick="tolak()">Reject</button> -->

                                    <table class="table table-fixed table-striped table-border table-hover nowrap datatable"
                                        id="datatable">
                                        <thead class="table-dark">
                                            <tr>
                                                <th scope="col" style="text-align: center">#</th>
                                                <th scope="col">Cek</th>
                                                <th scope="col">Cek</th>
                                                <th scope="col">Status</th>
                                                <th scope="col" style="text-align: center">Bukti#</th>
                                                <th scope="col" style="text-align: center">Tgl</th>
                                                <th scope="col" style="text-align: center">ACC 1</th>
                                                <th scope="col" style="text-align: center">ACC 2</th>
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

    <div class="modal fade" id="browsePPModal" tabindex="-1" role="dialog" aria-hidden="true"
        aria-labelledby="browsePPModal">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Informasi Detail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="tabContent">
                        <li class="nav-item active"><a class="nav-link active" href="#pp" data-toggle="tab">Detail</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="pp">
                            <legend class="font-weight-bold">Details</legend>
                            <table class="table table-responsive table-stripped table-bordered" id="table-pp">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>Supplier</th>
                                        <th>harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascripts')
    <script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var dataTable = $('.datatable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: true,
                // 'scrollX': true,
                'scrollY': '400px',
                "order": [
                    [0, "asc"]
                ],
                ajax: {
                    url: "{{ route('get-ubhn') }}",
                    data: {
                        filterpost: 1,
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'sta',
                        name: 'sta'
                    },

                    {
                        data: 'TGL',
                        name: 'TGL',
                        render: $.fn.dataTable.render.moment('DD-MM-YYYY')
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
                        data: 'TOTAL_QTY',
                        name: 'TOTAL_QTY',
                        render: $.fn.dataTable.render.number(',', '.', 2, '')
                    },
                    {
                        data: 'USRNM',
                        name: 'USRNM'
                    },
                    {
                        data: 'POSTED',
                        name: 'POSTED',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            let posted = parseInt(row.POSTED, 10);
                            let tolak = parseInt(row.TOLAK, 10);
                            if (posted === 1) {
                                return '<div class="text-center"><i class="fa-solid fa-check text-success"></i></div>';
                            } else if (tolak === 1) {
                                return '<div class="text-center"><i class="bi bi-xmark text-danger fw-bold fs-1"></i></div>';
                            } else {
                                return `
                <button class="btn btn-success btn-sm acc-btn" data-id="${row.NO_BUKTI}">ACC</button>
                <button class="btn btn-danger btn-sm tolak-btn" data-id="${row.NO_BUKTI}">TOLAK</button>`;
                            }
                        }
                    },
                    {
                        data: 'POSTED1',
                        name: 'POSTED1',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            let posted = parseInt(row.POSTED1, 10);
                            let tolak = parseInt(row.TOLAK, 10);
                            if (posted === 1) {
                                return '<div class="text-center"><i class="fa-solid fa-check text-success"></i></div>';
                            } else if (tolak === 1) {
                                return '<div class="text-center"><i class="fa-solid fa-xmark text-danger"></i></div>';
                            } else {
                                return `
                <button class="btn btn-success btn-sm acc-btn2" data-id="${row.NO_BUKTI}">ACC</button>
                <button class="btn btn-danger btn-sm tolak-btn2" data-id="${row.NO_BUKTI}">TOLAK</button>`;
                            }
                        }
                    }


                ],

                columnDefs: [

                    {
                        className: "dt-center klikheader",
                        targets: 3,
                        createdCell: function(td, cellData, rowData, row, col) {
                            $(td).css('color', 'blue');
                        }
                    },
                    {
                        "className": "dt-center",
                        "targets": 0
                    },
                    {
                        "className": "dt-right",
                        "targets": [6]
                    },
                ],
            });

            ///////////////////////////////////
            $(document).on('click', '.acc-btn', function() {
                let id = $(this).data('id');
                let btnContainer = $(this).closest('td'); // Dapatkan parent container

                $.ajax({
                    url: "/ubhn/acc/" + id,
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(response) {
                        setTimeout(function() {
                            dataTable.ajax.reload(null,
                                false); // 🔥 Tunggu sebentar sebelum reload
                        }, 500);
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            });

            $(document).on('click', '.tolak-btn', function() {
                let id = $(this).data('id');
                let btnContainer = $(this).closest('td'); // Dapatkan parent container

                $.ajax({
                    url: "/ubhn/tolak/" + id,
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(response) {
                        setTimeout(function() {
                            dataTable.ajax.reload(null,
                                false); // 🔥 Tunggu sebentar sebelum reload
                        }, 500);
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            });


            $(document).on('click', '.acc-btn2', function() {
                let id = $(this).data('id');
                let btnContainer = $(this).closest('td'); // Dapatkan parent container

                $.ajax({
                    url: "/ubhn/acc2/" + id,
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(response) {
                        setTimeout(function() {
                            dataTable.ajax.reload(null,
                                false); // 🔥 Tunggu sebentar sebelum reload
                        }, 500);
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            });

            $(document).on('click', '.tolak-btn2', function() {
                let id = $(this).data('id');
                let btnContainer = $(this).closest('td'); // Dapatkan parent container

                $.ajax({
                    url: "/ubhn/tolak2/" + id,
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(response) {
                        setTimeout(function() {
                            dataTable.ajax.reload(null,
                                false); // 🔥 Tunggu sebentar sebelum reload
                        }, 500);
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            });

            $('#datatable tbody').on('click', 'td.klikheader', function() {
                var tr = $(this).closest('tr');
                var row = dataTable.row(tr);
                var nobukti = dataTable.cell(this).data();

                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    // Open this row
                    row.child(format(nobukti, row.data())).show();
                    tr.addClass('shown');
                }

            });


        });


        function getinfo(nobukti) {
            browsePp(nobukti);
        }

        function format(bukti, rowData) {
            var div = $('<div/>').addClass('loading').text('Memuat...');

            $.ajax({
                type: 'GET',
                url: "{{ url('ubhn/browse') }}",
                data: {
                    NO_BUKTI: bukti,
                },
                success: function(response) {
                    var baris = '<table id="detailtabel">' +
                        '<tr>' +
                        '<th>KODES</th>' +
                        '<th>NAMAS</th>' +
                        '<th>KET</th>' +
                        '<th>FLAG</th>' +
                        '<th>PER</th>' +
                        '<th>GOL</th>' +
                        '<th>NM_BRG</th>' +
                        '<th>MERK</th>' +
                        '<th>UKURAN</th>' +
                        '<th>KD_BRG</th>' +
                        '<th>KLP</th>' +
                        '<th>HARGA</th>' +
                        '<th>DISC</th>' +
                        '<th>BY_ANGKUT</th>' +
                        '<th>PPN</th>' +
                        '<th>KET_KMS</th>' +
                        '<th>MO</th>' +
                        '<th>KLK</th>' +
                        '<th>N_POINT</th>' +
                        '<th>KIRA_LPP</th>' +
                        '<th>KET_X</th>' +
                        '<th>KET_PB</th>' +
                        '</tr>';
                    var isi = '';
                    for (i = 0; i < response.length; i++) {
                        isi = '<tr>' +
                            '<td style="text-align:center">' + response[i].KODES + '</td>' +
                            '<td style="text-align:right">' + response[i].NAMAS + '</td>' +
                            '<td style="text-align:right">' + response[i].KET + '</td>' +
                            '<td style="text-align:right">' + response[i].FLAG + '</td>' +
                            '<td style="text-align:right">' + response[i].PER + '</td>' +
                            '<td style="text-align:right">' + response[i].GOL + '</td>' +
                            '<td style="text-align:right">' + response[i].NM_BRG + '</td>' +
                            '<td style="text-align:right">' + response[i].MERK + '</td>' +
                            '<td style="text-align:right">' + response[i].UKURAN + '</td>' +
                            '<td style="text-align:center">' + response[i].KD_BRG + '</td>' +
                            '<td style="text-align:right">' + response[i].KLP + '</td>' +
                            '<td style="text-align:right">' + response[i].HARGA + '</td>' +
                            '<td style="text-align:right">' + response[i].DISC + '</td>' +
                            '<td style="text-align:right">' + response[i].BY_ANGKUT + '</td>' +
                            '<td style="text-align:right">' + response[i].PPN + '</td>' +
                            '<td style="text-align:right">' + response[i].KET_KMS + '</td>' +
                            '<td style="text-align:right">' + response[i].MO + '</td>' +
                            '<td style="text-align:right">' + response[i].KLK + '</td>' +
                            '<td style="text-align:right">' + response[i].N_POINT + '</td>' +
                            '<td style="text-align:right">' + response[i].KIRA_LPP + '</td>' +
                            '<td style="text-align:right">' + response[i].KET_X + '</td>' +
                            '<td style="text-align:right">' + response[i].KET_PB + '</td>' +
                            '</tr>';
                        baris += isi;
                    }
                    baris += "</table>"
                    div.html(baris).removeClass('loading');
                }
            });

            return div;
        }


        function simpan() {
            var check = '0';
            var min = '0';

            document.getElementById("entri").submit();

        }
    </script>
@endsection
