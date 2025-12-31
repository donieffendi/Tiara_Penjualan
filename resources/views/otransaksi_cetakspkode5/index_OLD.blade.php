@extends('layouts.plain')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .card {
            padding: 15px;
        }

        .form-control:focus {
            background-color: #b5e5f9;
        }

        .btn-save {
            background: #28a745;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 12px 30px;
            font-size: 16px;
        }

        .btn-save:hover {
            background: #218838;
            color: #fff;
        }

        .btn-proses {
            background: #007bff;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 12px 30px;
            font-size: 16px;
        }

        .btn-proses:hover {
            background: #0056b3;
            color: #fff;
        }

        .btn-print {
            background: #17a2b8;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 12px 30px;
            font-size: 16px;
        }

        .btn-print:hover {
            background: #138496;
            color: #fff;
        }

        .btn-folder {
            background: #ffc107;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 12px 30px;
            font-size: 16px;
        }

        .btn-folder:hover {
            background: #e0a800;
            color: #fff;
        }

        .table thead th {
            background: #343a40;
            color: white;
            border: none;
        }

        .loader {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 100px;
            aspect-ratio: 1;
            background:
                radial-gradient(farthest-side, #ffa516 90%, #0000) center/16px 16px,
                radial-gradient(farthest-side, green 90%, #0000) bottom/12px 12px;
            background-repeat: no-repeat;
            animation: l17 1s infinite linear;
            z-index: 9999;
            display: none;
        }

        .loader::before {
            content: "";
            position: absolute;
            width: 8px;
            aspect-ratio: 1;
            inset: auto 0 16px;
            margin: auto;
            background: #ccc;
            border-radius: 50%;
            transform-origin: 50% calc(100% + 10px);
            animation: inherit;
            animation-duration: 0.5s;
        }

        @keyframes l17 {
            100% {
                transform: rotate(1turn)
            }
        }

        .input-group-sm {
            margin-bottom: 10px;
        }

        .editable-cell {
            cursor: pointer;
            min-height: 30px;
            padding: 5px;
        }

        .editable-cell:hover {
            background-color: #f0f0f0;
        }

        .form-inline label {
            margin-right: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ $judul }}</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                @if (isset($warning))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Perhatian!</strong> {{ $warning }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (isset($error))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> {{ $error }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><strong>No Bukti JL/BL (Cetak
                                                        Ulang)</strong></span>
                                            </div>
                                            <input type="text" class="form-control" id="txtBukti"
                                                placeholder="Masukkan No Bukti">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><strong>No SP (Ambil data)</strong></span>
                                            </div>
                                            <input type="text" class="form-control" id="txtSP"
                                                placeholder="Masukkan No SP">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <button type="button" id="btnSave" class="btn btn-save">
                                            <i class="fas fa-save"></i> SIMPAN
                                        </button>
                                        <button type="button" id="btnProses" class="btn btn-proses">
                                            <i class="fas fa-cogs"></i> PROSES
                                        </button>
                                        <button type="button" id="btnPrint" class="btn btn-print">
                                            <i class="fas fa-print"></i> CETAK
                                        </button>
                                        <button type="button" id="btnFolder" class="btn btn-folder">
                                            <i class="fas fa-folder-open"></i> BUKA FOLDER
                                        </button>
                                    </div>
                                </div>

                                <hr>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-inline">
                                            <label>Tampilkan</label>
                                            <select class="form-control form-control-sm mx-2" id="pageLength">
                                                <option value="10">10</option>
                                                <option value="25" selected>25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                                <option value="-1">Semua</option>
                                            </select>
                                            <label>data</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <div class="form-inline float-right">
                                            <label>Cari:</label>
                                            <input type="text" class="form-control form-control-sm ml-2" id="searchBox">
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table-striped table-bordered table-hover table" id="tableCetakSP"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th width="50px" class="text-center">No</th>
                                                <th width="120px">No Bukti</th>
                                                <th width="100px" class="text-center">Tanggal</th>
                                                <th width="100px">Kode Brg</th>
                                                <th>Nama Barang</th>
                                                <th width="150px">Kemasan</th>
                                                <th width="80px" class="text-center">Qty</th>
                                                <th width="100px">Kode Supp</th>
                                                <th>Supplier</th>
                                                <th width="80px" class="text-center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="loader" id="LOADX"></div>

    <!-- Modal Browse Bukti -->
    <div class="modal fade" id="modalBrowseBukti" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Browse No Bukti JL/BL</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table-striped table-bordered table" id="tableBrowseBukti">
                        <thead>
                            <tr>
                                <th>No Bukti</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Browse Supplier -->
    <div class="modal fade" id="modalBrowseSupplier" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Browse Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table-striped table-bordered table" id="tableBrowseSupplier">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Supplier</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Browse Barang -->
    <div class="modal fade" id="modalBrowseBarang" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Browse Barang</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table-striped table-bordered table" id="tableBrowseBarang">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Kemasan</th>
                                <th>Kode Supp</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('koolreport/KoolDataTable/KoolDataTable.js') }}"></script>

    <script>
        var table;
        var currentEditRow = null;

        $(document).ready(function() {
            // Initialize DataTable
            // table = $('#tableCetakSP').KoolDataTable({
            //     processing: true,
            //     serverSide: true,
            //     ajax: "{{ route('get-tcetakspkode5-data') }}",
            //     columns: [{
            //             data: 'DT_RowIndex',
            //             name: 'DT_RowIndex',
            //             orderable: false,
            //             searchable: false,
            //             className: 'text-center'
            //         },
            //         {
            //             data: 'no_bukti',
            //             name: 'no_bukti'
            //         },
            //         {
            //             data: 'tgl_format',
            //             name: 'tgl',
            //             className: 'text-center'
            //         },
            //         {
            //             data: 'kd_brg',
            //             name: 'kd_brg',
            //             className: 'editable-cell editable-kd-brg'
            //         },
            //         {
            //             data: 'na_brg',
            //             name: 'na_brg'
            //         },
            //         {
            //             data: 'ket_kem',
            //             name: 'ket_kem'
            //         },
            //         {
            //             data: 'qty',
            //             name: 'qty',
            //             className: 'text-center editable-cell editable-qty'
            //         },
            //         {
            //             data: 'kodes',
            //             name: 'kodes',
            //             className: 'editable-cell editable-kodes'
            //         },
            //         {
            //             data: 'namas',
            //             name: 'namas'
            //         },
            //         {
            //             data: 'action',
            //             name: 'action',
            //             orderable: false,
            //             searchable: false,
            //             className: 'text-center'
            //         }
            //     ],
            //     pageLength: 25,
            //     lengthMenu: [
            //         [10, 25, 50, 100, -1],
            //         [10, 25, 50, 100, "Semua"]
            //     ],
            //     order: [
            //         [3, 'asc']
            //     ],
            //     dom: 'rt<"row mt-3"<"col-md-6"i><"col-md-6"p>>'
            // });

            $('#pageLength').on('change', function() {
                table.page.len($(this).val()).draw();
            });

            $('#searchBox').on('keyup', function() {
                table.search($(this).val()).draw();
            });

            // Handle input No Bukti
            // $('#txtBukti').on('keypress', function(e) {
            //     if (e.which === 13) {
            //         var noBukti = $(this).val().trim();

            //         if (!noBukti) {
            //             return;
            //         }

            //         $('#LOADX').show();

            //         $.ajax({
            //             url: "{{ route('tcetakspkode5_browse') }}",
            //             type: 'GET',
            //             data: {
            //                 type: 'bukti'
            //             },
            //             success: function(response) {
            //                 $('#LOADX').hide();

            //                 var exists = response.filter(function(item) {
            //                     return item.no_bukti === noBukti;
            //                 });

            //                 if (exists.length === 0) {
            //                     Swal.fire({
            //                         title: 'Data tidak ditemukan',
            //                         text: 'Lihat list datanya?',
            //                         icon: 'question',
            //                         showCancelButton: true,
            //                         confirmButtonText: 'Ya',
            //                         cancelButtonText: 'Tidak'
            //                     }).then((result) => {
            //                         if (result.isConfirmed) {
            //                             showBrowseBukti();
            //                         }
            //                     });
            //                 } else {
            //                     loadFromBukti(noBukti);
            //                 }
            //             },
            //             error: function(xhr) {
            //                 $('#LOADX').hide();
            //                 Swal.fire({
            //                     icon: 'error',
            //                     title: 'Error',
            //                     text: xhr.responseJSON?.error || 'Terjadi kesalahan'
            //                 });
            //             }
            //         });
            //     }
            // });

            // Handle input No SP
            $('#txtSP').on('keypress', function(e) {
                if (e.which === 13) {
                    var noSP = $(this).val().trim();

                    if (!noSP) {
                        return;
                    }

                    printSP(noSP);
                }
            });

            // Handle Save Button
            $('#btnSave').on('click', function() {
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Simpan data?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        saveData();
                    }
                });
            });

            // Handle Proses Button
            $('#btnProses').on('click', function() {
                Swal.fire({
                    title: 'Konfirmasi Proses',
                    text: 'Lanjut proses?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        prosesData();
                    }
                });
            });

            // Handle Print Button
            $('#btnPrint').on('click', function() {
                // Akan print semua SP yang sudah diproses
                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'Fitur cetak akan menampilkan semua SP yang sudah diproses'
                });
            });

            // Handle Folder Button
            $('#btnFolder').on('click', function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'Fitur buka folder hanya tersedia di aplikasi desktop'
                });
            });

            // Handle editable cells
            $('#tableCetakSP').on('click', '.editable-kd-brg', function() {
                var cell = $(this);
                var row = table.row(cell.parent());
                var data = row.data();

                currentEditRow = {
                    cell: cell,
                    row: row,
                    data: data,
                    field: 'kd_brg'
                };

                var input = $('<input type="text" class="form-control form-control-sm" value="' + data
                    .kd_brg + '">');
                cell.html(input);
                input.focus();

                input.on('blur', function() {
                    cell.text(data.kd_brg);
                });

                input.on('keypress', function(e) {
                    if (e.which === 13) {
                        var newValue = $(this).val().trim();

                        if (newValue) {
                            loadBarang(newValue);
                        }
                    }
                });
            });

            $('#tableCetakSP').on('click', '.editable-kodes', function() {
                var cell = $(this);
                var row = table.row(cell.parent());
                var data = row.data();

                currentEditRow = {
                    cell: cell,
                    row: row,
                    data: data,
                    field: 'kodes'
                };

                var input = $('<input type="text" class="form-control form-control-sm" value="' + data
                    .kodes + '">');
                cell.html(input);
                input.focus();

                input.on('blur', function() {
                    cell.text(data.kodes);
                });

                input.on('keypress', function(e) {
                    if (e.which === 13) {
                        var newValue = $(this).val().trim();

                        if (newValue) {
                            loadSupplier(newValue);
                        }
                    }
                });
            });

            $('#tableCetakSP').on('click', '.editable-qty', function() {
                var cell = $(this);
                var row = table.row(cell.parent());
                var data = row.data();

                var input = $('<input type="number" class="form-control form-control-sm" value="' + data
                    .qty + '">');
                cell.html(input);
                input.focus();

                input.on('blur', function() {
                    var newQty = parseFloat($(this).val()) || 0;
                    data.qty = newQty;
                    data.total = newQty * parseFloat(data.harga || 0);
                    cell.text(newQty);
                });

                input.on('keypress', function(e) {
                    if (e.which === 13) {
                        $(this).blur();
                    }
                });
            });

            // $('#txtBukti').keydown(function(e) {
            //     if (e.keyCode === 13) {
            //         e.preventDefault();

            //         let bukti = $('#txtBukti').val().trim();

            //         $.ajax({
            //             url: "{{ route('tcetakspkode5.cetak_ulang') }}",
            //             type: "GET",
            //             data: {
            //                 bukti: bukti,
            //                 _token: "{{ csrf_token() }}"
            //             },
            //             success: function(res) {

            //                 window.open(res.url, "_blank");

            //             },
            //             error: function(xhr) {
            //                 Swal.fire("Error", "Terjadi kesalahan server!", "error");
            //             }
            //         });
            //     }
            // });
            $('#txtBukti').keydown(function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();

                    let bukti = $('#txtBukti').val().trim();
                    if (bukti === "") return;

                    // langsung buka jalur cetak tanpa AJAX
                    let url = "{{ route('tcetakspkode5.cetak_ulang') }}" + "?bukti=" + bukti;
                    window.open(url, "_blank");
                }
            });

            $('#txtSP').keydown(function(e) {

                if (e.keyCode === 13) { // ENTER
                    e.preventDefault();

                    let noSP = $('#txtSP').val().trim();

                    if (noSP === '') {
                        Swal.fire('Peringatan', 'No SP tidak boleh kosong', 'warning');
                        return;
                    }

                    $.ajax({
                        url: "",
                        method: "POST",
                        data: {
                            bukti: noSP,
                            _token: "{{ csrf_token() }}"
                        },
                        beforeSend: function() {
                            Swal.showLoading();
                        },
                        success: function(res) {
                            Swal.close();

                            if (res.status === 'not_found') {
                                Swal.fire({
                                    title: res.message,
                                    icon: "question",
                                    showCancelButton: true,
                                    confirmButtonText: "Lihat List",
                                    cancelButtonText: "Batal"
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // PANGGIL MODAL LIST SP
                                        $('#modalListSP').modal('show');
                                    }
                                });

                                return;
                            }

                            // Jika berhasil
                            if (res.status === 'success') {

                                Swal.fire({
                                    title: 'Sukses',
                                    text: res.message,
                                    icon: 'success'
                                });

                                // RESET INPUT
                                $('#txtSP').val("");

                                // tampilData(res.data);
                            }

                        },
                        error: function(xhr) {
                            Swal.close();
                            Swal.fire('Error', 'Terjadi kesalahan server', 'error');
                        }
                    });

                }
            });




            // Handle checkbox hapus
            $('#tableCetakSP').on('change', '.chk-hapus', function() {
                var id = $(this).data('id');
                var checked = $(this).is(':checked');

                // Update data di tabel
                var rowData = table.rows().data();
                for (var i = 0; i < rowData.length; i++) {
                    if (rowData[i].no_id == id) {
                        rowData[i].hps = checked ? 1 : 0;
                        break;
                    }
                }
            });

            // Browse Bukti Modal
            $('#tableBrowseBukti').on('click', 'tr', function() {
                var noBukti = $(this).find('td:first').text();
                $('#txtBukti').val(noBukti);
                $('#modalBrowseBukti').modal('hide');
                loadFromBukti(noBukti);
            });

            // Browse Supplier Modal
            $('#tableBrowseSupplier').on('click', 'tr', function() {
                var kodes = $(this).find('td:first').text();
                var namas = $(this).find('td:eq(1)').text();

                if (currentEditRow) {
                    currentEditRow.data.kodes = kodes;
                    currentEditRow.data.namas = namas;
                    currentEditRow.cell.text(kodes);
                    currentEditRow.row.cells(null, 8).every(function() {
                        $(this.node()).text(namas);
                    });
                }

                $('#modalBrowseSupplier').modal('hide');
            });

            // Browse Barang Modal
            $('#tableBrowseBarang').on('click', 'tr', function() {
                var kdBrg = $(this).find('td:eq(0)').text();
                var naBrg = $(this).find('td:eq(1)').text();
                var ketKem = $(this).find('td:eq(2)').text();
                var kodes = $(this).find('td:eq(3)').text();

                if (currentEditRow) {
                    currentEditRow.data.kd_brg = kdBrg;
                    currentEditRow.data.na_brg = naBrg;
                    currentEditRow.data.ket_kem = ketKem;
                    currentEditRow.data.kodes = kodes;

                    currentEditRow.cell.text(kdBrg);
                    table.ajax.reload(null, false);
                }

                $('#modalBrowseBarang').modal('hide');
            });
        });

        function showBrowseBukti() {
            $('#LOADX').show();

            $.ajax({
                url: "{{ route('tcetakspkode5_browse') }}",
                type: 'GET',
                data: {
                    type: 'bukti'
                },
                success: function(response) {
                    $('#LOADX').hide();

                    var tbody = $('#tableBrowseBukti tbody');
                    tbody.empty();

                    response.forEach(function(item) {
                        var tgl = moment(item.tgl).format('DD-MM-YYYY');
                        tbody.append('<tr><td>' + item.no_bukti + '</td><td>' + tgl + '</td></tr>');
                    });

                    $('#modalBrowseBukti').modal('show');
                },
                error: function(xhr) {
                    $('#LOADX').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.error || 'Terjadi kesalahan'
                    });
                }
            });
        }

        function loadBarang(kdBrg) {
            $('#LOADX').show();

            $.ajax({
                url: "{{ route('tcetakspkode5_browse') }}",
                type: 'GET',
                data: {
                    type: 'barang'
                },
                success: function(response) {
                    $('#LOADX').hide();

                    var barang = response.filter(function(item) {
                        return item.kd_brg === kdBrg;
                    });

                    if (barang.length === 0) {
                        // Show browse modal
                        var tbody = $('#tableBrowseBarang tbody');
                        tbody.empty();

                        response.forEach(function(item) {
                            tbody.append('<tr><td>' + item.kd_brg + '</td><td>' + item.na_brg +
                                '</td><td>' + item.ket_kem +
                                '</td><td>' + item.kodes + '</td></tr>');
                        });

                        $('#modalBrowseBarang').modal('show');
                    } else {
                        // Update row data
                        if (currentEditRow) {
                            var item = barang[0];
                            currentEditRow.data.kd_brg = item.kd_brg;
                            currentEditRow.data.na_brg = item.na_brg;
                            currentEditRow.data.ket_kem = item.ket_kem;
                            currentEditRow.data.kodes = item.kodes;
                            currentEditRow.data.sub = item.sub;
                            currentEditRow.data.kdbar = item.kdbar;
                            currentEditRow.data.klaku = item.kdlaku;
                            currentEditRow.data.kemasan = item.kemasan;
                            currentEditRow.data.type = item.type;

                            table.ajax.reload(null, false);
                        }
                    }
                },
                error: function(xhr) {
                    $('#LOADX').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.error || 'Terjadi kesalahan'
                    });
                }
            });
        }

        function loadSupplier(kodes) {
            $('#LOADX').show();

            $.ajax({
                url: "{{ route('tcetakspkode5_browse') }}",
                type: 'GET',
                data: {
                    type: 'supplier'
                },
                success: function(response) {
                    $('#LOADX').hide();

                    var supplier = response.filter(function(item) {
                        return item.kodes === kodes;
                    });

                    if (supplier.length === 0) {
                        // Show browse modal
                        var tbody = $('#tableBrowseSupplier tbody');
                        tbody.empty();

                        response.forEach(function(item) {
                            tbody.append('<tr><td>' + item.kodes + '</td><td>' + item.namas +
                                '</td></tr>');
                        });

                        $('#modalBrowseSupplier').modal('show');
                    } else {
                        // Update row data
                        if (currentEditRow) {
                            var item = supplier[0];
                            currentEditRow.data.kodes = item.kodes;
                            currentEditRow.data.namas = item.namas;
                            currentEditRow.cell.text(item.kodes);

                            table.ajax.reload(null, false);
                        }
                    }
                },
                error: function(xhr) {
                    $('#LOADX').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.error || 'Terjadi kesalahan'
                    });
                }
            });
        }

        function loadFromBukti(noBukti) {
            $('#LOADX').show();

            $.ajax({
                url: "{{ url('/tcetakspkode5/load-from-bukti') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    no_bukti: noBukti
                },
                success: function(response) {
                    $('#LOADX').hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    $('#txtBukti').val('');
                    table.ajax.reload();
                },
                error: function(xhr) {
                    $('#LOADX').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.error || 'Terjadi kesalahan'
                    });
                }
            });
        }

        function printSP(noSP) {
            window.open("{{ url('/tcetakspkode5/jasper') }}?no_bukti=" + noSP, '_blank');
            $('#txtSP').val('');
        }

        function saveData() {
            var tableData = [];

            table.rows().every(function() {
                var data = this.data();
                tableData.push({
                    no_id: data.no_id,
                    no_bukti: data.no_bukti,
                    tgl: data.tgl,
                    kd_brg: data.kd_brg,
                    na_brg: data.na_brg,
                    ket_kem: data.ket_kem,
                    qty: data.qty,
                    harga: data.harga,
                    total: data.total,
                    kodes: data.kodes,
                    namas: data.namas,
                    sub: data.sub,
                    kdbar: data.kdbar,
                    klaku: data.klaku,
                    kemasan: data.kemasan,
                    type: data.type,
                    hps: data.hps || 0
                });
            });

            $('#LOADX').show();

            $.ajax({
                url: "{{ url('/tcetakspkode5/store') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    data: tableData
                },
                success: function(response) {
                    $('#LOADX').hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    table.ajax.reload();
                },
                error: function(xhr) {
                    $('#LOADX').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.error || 'Terjadi kesalahan'
                    });
                }
            });
        }

        function prosesData() {
            // Save data terlebih dahulu
            var tableData = [];

            table.rows().every(function() {
                var data = this.data();
                tableData.push({
                    no_id: data.no_id,
                    no_bukti: data.no_bukti,
                    tgl: data.tgl,
                    kd_brg: data.kd_brg,
                    na_brg: data.na_brg,
                    ket_kem: data.ket_kem,
                    qty: data.qty,
                    harga: data.harga,
                    total: data.total,
                    kodes: data.kodes,
                    namas: data.namas,
                    sub: data.sub,
                    kdbar: data.kdbar,
                    klaku: data.klaku,
                    kemasan: data.kemasan,
                    type: data.type,
                    hps: data.hps || 0
                });
            });

            $('#LOADX').show();
            $('#btnProses').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');

            // Save first
            $.ajax({
                url: "{{ url('/tcetakspkode5/store') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    data: tableData
                },
                success: function() {
                    // Then proses
                    $.ajax({
                        url: "{{ url('/tcetakspkode5/proses') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            $('#LOADX').hide();
                            $('#btnProses').prop('disabled', false).html(
                                '<i class="fas fa-cogs"></i> PROSES');

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                html: response.message + '<br><small>Total SP: ' + response
                                    .po_list.length + '</small>',
                                timer: 3000,
                                showConfirmButton: false
                            });

                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            $('#LOADX').hide();
                            $('#btnProses').prop('disabled', false).html(
                                '<i class="fas fa-cogs"></i> PROSES');

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.error || 'Terjadi kesalahan'
                            });
                        }
                    });
                },
                error: function(xhr) {
                    $('#LOADX').hide();
                    $('#btnProses').prop('disabled', false).html('<i class="fas fa-cogs"></i> PROSES');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal menyimpan data: ' + (xhr.responseJSON?.error ||
                            'Terjadi kesalahan')
                    });
                }
            });
        }
    </script>
@endsection
