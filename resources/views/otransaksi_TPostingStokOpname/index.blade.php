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

        .btn-allin {
            background: #17a2b8;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 10px 25px;
            font-size: 15px;
        }

        .btn-allin:hover {
            background: #138496;
            color: #fff;
        }

        .btn-proses {
            background: #28a745;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 10px 25px;
            font-size: 15px;
        }

        .btn-proses:hover {
            background: #218838;
            color: #fff;
        }

        .btn-cetak {
            background: #5bc0de;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 10px 25px;
            font-size: 15px;
        }

        .btn-cetak:hover {
            background: #31b0d5;
            color: #fff;
        }

        .table thead th {
            background: #343a40;
            color: white;
            border: none;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            cursor: pointer;
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

        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .info-alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        /* DataTable Loading & Empty State */
        .dataTables_processing {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 200px;
            margin-left: -100px;
            margin-top: -26px;
            text-align: center;
            padding: 1em 0;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .dataTables_processing::after {
            content: "Memuat data...";
            display: block;
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }

        .dataTables_empty {
            text-align: center;
            padding: 40px 20px !important;
            color: #666;
            font-size: 16px;
            background: #f8f9fa;
        }

        .dataTables_empty::before {
            content: "📋";
            display: block;
            font-size: 48px;
            margin-bottom: 15px;
        }

        table.dataTable tbody tr.odd {
            background-color: #ffffff;
        }

        table.dataTable tbody tr.even {
            background-color: #f9f9f9;
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
                                <div class="info-alert">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Informasi:</strong> Maksimal 6 dokumen dapat diproses dalam sekali posting.
                                    Proses ini akan mengupdate stok dan tidak dapat
                                    dibatalkan.
                                </div>

                                <div class="filter-section">
                                    <div class="row">
                                        <div class="col-md-8 text-right">
                                            <button type="button" id="btnAllIn" class="btn btn-allin">
                                                <i class="fas fa-check-double"></i> ALL IN
                                            </button>
                                            <button type="button" id="btnProses" class="btn btn-proses">
                                                <i class="fas fa-cog"></i> PROSES
                                            </button>
                                            {{-- <button type="button" id="btnPrint" class="btn btn-cetak">
                                                <i class="fas fa-print"></i> CETAK
                                            </button> --}}
                                        </div>
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
                                    <table class="table-striped table-bordered table-hover table" id="tablePosting"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th width="50px" class="text-center">
                                                </th>
                                                <th width="60px"></th>
                                                <th width="120px" class="text-center">No Bukti</th>
                                                <th width="140px">No Posting</th>
                                                <th width="100px" class="text-center">Tanggal</th>
                                                <th>Notes</th>
                                                <th width="100px" class="text-right">Total</th>
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
@endsection

@section('javascripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var table;
        const MAX_DOCUMENTS = 6;

        $(document).ready(function() {
            console.log('=== MEMUAT HALAMAN POSTING STOK OPNAME ===');
            loadData();

            // Page length change
            $('#pageLength').on('change', function() {
                console.log('Page length berubah ke:', $(this).val());
                table.page.len($(this).val()).draw();
            });

            // Search box
            $('#searchBox').on('keyup', function() {
                console.log('Search:', $(this).val());
                table.search($(this).val()).draw();
            });

            $('#btnAllIn').on('click', function() {

                // Ambil semua checkbox item yang tampil di tabel
                var totalItems = $('#tablePosting').find('.cek-item').length;

                if (totalItems === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak Ada Data',
                        text: 'Tidak ada item yang bisa dipilih.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    return;
                }

                // Centang semua
                $('#tablePosting').find('.cek-item').prop('checked', true);

                // Centang juga checkbox header jika ada
                $('#checkAll').prop('checked', true);

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: totalItems + ' data telah dipilih',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            // Button Proses
            $('#btnProses').on('click', function() {
                console.log('Tombol Proses diklik');

                var selectedItems = [];
                $('.cek-item:checked').each(function() {
                    selectedItems.push($(this).val());
                });

                if (selectedItems.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Tidak ada data yang dipilih untuk diproses',
                        confirmButtonColor: '#f39c12'
                    });
                    return;
                }

                if (selectedItems.length > MAX_DOCUMENTS) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Peringatan',
                        html: '<p>Maksimal <strong>' + MAX_DOCUMENTS + '</strong> dokumen.</p>' +
                            '<p>Anda memilih <strong class="text-danger">' + selectedItems.length +
                            '</strong></p>',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Posting',
                    html: '<div class="text-center">' +
                        '<p>Anda akan memposting <strong class="text-primary">' + selectedItems
                        .length + '</strong> dokumen Stok Opname</p>' +
                        '<hr>' +
                        '<p class="text-danger"><small><i class="fas fa-exclamation-triangle"></i> Proses ini tidak dapat dibatalkan.</small></p>' +
                        '</div>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-check"></i> Ya, Proses!',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: false,
                    preConfirm: () => {

                        $('#btnProses').prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin"></i> MEMPROSES...');
                        $('#LOADX').show();

                        Swal.showLoading();

                        return $.ajax({
                                url: "{{ route('tpostingstokopname_posting_bulk') }}",
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    no_bukti_list: selectedItems,
                                    flagz: 'FS'
                                },
                                timeout: 60000
                            })
                            .then(response => {
                                $('#btnProses').prop('disabled', false).html(
                                    '<i class="fas fa-cog"></i> PROSES');
                                $('#LOADX').hide();

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    html: '<div class="text-center">' +
                                        '<p><strong>' + response.message +
                                        '</strong></p>' +
                                        '<p class="text-muted"><small>' +
                                        selectedItems.length +
                                        ' dokumen berhasil diposting.</small></p>' +
                                        '</div>',
                                    timer: 2000,
                                    confirmButtonText: 'OK',
                                    confirmButtonColor: '#28a745'
                                }).then(() => {

                                    // === RELOAD TABEL ===
                                    table.ajax.reload(null, false);
                                    $('#checkAll').prop('checked', false);

                                    let list = selectedItems.join(',');
                                    let printUrl =
                                        "{{ route('tpostingstokopname_print') }}" +
                                        "?list=" + list;
                                    window.open(printUrl, "_blank");

                                });

                            })
                            .catch(xhr => {
                                $('#btnProses').prop('disabled', false).html(
                                    '<i class="fas fa-cog"></i> PROSES');
                                $('#LOADX').hide();

                                var errorMsg = xhr.responseJSON?.error ?? xhr.statusText ??
                                    'Error tidak diketahui';

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Posting Gagal!',
                                    html: '<p class="text-danger">' + errorMsg +
                                        '</p>',
                                    confirmButtonColor: '#d33'
                                });
                            });

                    }
                });
            });

        });

        function loadData() {

            if ($.fn.DataTable.isDataTable('#tablePosting')) {
                $('#tablePosting').DataTable().destroy();
            }

            table = $('#tablePosting').DataTable({
                processing: true,
                serverSide: true,
                deferLoading: 0,
                ajax: {
                    url: "{{ route('get-tpostingstokopname-post') }}",
                    type: "GET",
                    data: function(d) {
                        d.flagz = 'AO';
                    },
                    error: function(xhr, error, code) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error Load Data',
                            html: `
                        <div class="text-left">
                            <p><strong>Terjadi kesalahan saat memuat data:</strong></p>
                            <p class="text-danger">
                                ${xhr.responseJSON?.error || xhr.statusText || 'Gagal memuat data'}
                            </p>
                            <hr>
                            <p><small><strong>Troubleshooting:</strong><br>
                            1. Pastikan user memiliki CBG<br>
                            2. Periksa console browser (F12)<br>
                            3. Cek storage/logs/laravel.log<br>
                            4. Pastikan koneksi database aktif</small></p>
                        </div>`,
                            footer: `<small>Status: ${xhr.status} - ${xhr.statusText}</small>`,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                },
                language: {
                    processing: '<div class="spinner-border text-primary"></div><br>Memuat data...',
                    emptyTable: "Tidak ada data stok opname yang belum diposting",
                    zeroRecords: "Tidak ada data yang sesuai dengan pencarian",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    lengthMenu: "Tampilkan _MENU_ data",
                    search: "Cari:",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                },
                columns: [{
                        data: 'cek_checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'bukt',
                        name: 'bukt'
                    },
                    {
                        data: 'no_bukti',
                        name: 'no_bukti'
                    },
                    {
                        data: 'tgl',
                        name: 'tgl'
                    },
                    {
                        data: 'notes',
                        name: 'notes'
                    },
                    {
                        data: 'total_qty',
                        name: 'total_qty',
                        className: 'text-right'
                    }
                ],
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "Semua"]
                ],
                order: [
                    [2, 'asc']
                ],
                dom: 'rt<"row mt-3"<"col-md-6"i><"col-md-6"p>>'
            });

            table.ajax.reload();

            // Jika response kosong → munculkan notifikasi
            table.on('xhr.dt', function(e, settings, json, xhr) {
                if (json?.data?.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Informasi',
                        text: 'Tidak ada data stok opname yang belum diposting',
                        timer: 3000,
                        timerProgressBar: true,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false
                    });
                }
            });

        }
    </script>
@endsection
