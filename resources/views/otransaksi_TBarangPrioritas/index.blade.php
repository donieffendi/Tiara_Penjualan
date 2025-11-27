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

        .btn-action {
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 10px 25px;
            font-size: 15px;
            margin-right: 10px;
        }

        .btn-tampil {
            background: #007bff;
        }

        .btn-tampil:hover {
            background: #0056b3;
            color: #fff;
        }

        .btn-refresh {
            background: #17a2b8;
        }

        .btn-refresh:hover {
            background: #138496;
            color: #fff;
        }

        .btn-print {
            background: #6c757d;
        }

        .btn-print:hover {
            background: #545b62;
            color: #fff;
        }

        .btn-proses {
            background: #28a745;
        }

        .btn-proses:hover {
            background: #218838;
            color: #fff;
        }

        .btn-action:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        .table thead th {
            background: #343a40;
            color: white;
            border: none;
            font-size: 12px;
            padding: 10px 6px;
            text-align: center;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table tbody td {
            padding: 6px;
            font-size: 12px;
            vertical-align: middle;
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

        .text-right-col {
            text-align: right !important;
        }

        .text-center-col {
            text-align: center !important;
        }

        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .info-box strong {
            color: #0056b3;
        }

        .badge-cbg {
            font-size: 16px;
            padding: 8px 15px;
            border-radius: 5px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .button-group {
            margin-bottom: 20px;
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
                    <div class="col-sm-6 text-right">
                        @if (isset($cbg))
                            <span class="badge badge-cbg badge-primary">CBG:
                                {{ is_array($cbg) ? implode(', ', $cbg) : $cbg ?? '-' }}</span>
                            <span class="badge badge-cbg badge-info">Periode:
                                {{ is_array($periode) ? implode(', ', $periode) : $periode ?? '-' }}</span>
                        @endif
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
                                <!-- Info Box -->
                                <div class="info-box">
                                    <p class="mb-1"><strong>Petunjuk:</strong></p>
                                    <ul class="mb-0">
                                        <li>Klik <strong>TAMPIL</strong> untuk generate dan menampilkan data barang
                                            prioritas</li>
                                        <li>Klik <strong>REFRESH</strong> untuk memperbarui tampilan data</li>
                                        <li>Klik <strong>PRINT</strong> untuk mencetak laporan barang prioritas</li>
                                        <li>Klik <strong>PROSES</strong> untuk mengirim data ke file DBF</li>
                                    </ul>
                                </div>

                                <!-- Button Group -->
                                <div class="button-group">
                                    <button type="button" id="btnTampil" class="btn btn-action btn-tampil">
                                        <i class="fas fa-eye"></i> TAMPIL
                                    </button>
                                    <button type="button" id="btnRefresh" class="btn btn-action btn-refresh">
                                        <i class="fas fa-sync-alt"></i> REFRESH
                                    </button>
                                    <button type="button" id="btnPrint" class="btn btn-action btn-print">
                                        <i class="fas fa-print"></i> PRINT
                                    </button>
                                    <button type="button" id="btnProses" class="btn btn-action btn-proses">
                                        <i class="fas fa-paper-plane"></i> PROSES
                                    </button>
                                </div>

                                <hr>

                                <!-- Table Section -->
                                <div class="table-wrapper mt-3">
                                    <table class="table-striped table-bordered table-hover table" id="tablePrioritas"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th width="40px">No</th>
                                                <th width="120px">Kode Barang</th>
                                                <th width="80px">Sub</th>
                                                <th width="300px">Nama Barang</th>
                                                <th width="100px">Ukuran</th>
                                                <th width="100px">Kemasan</th>
                                                <th width="80px">LPH</th>
                                                <th width="80px">Saldo</th>
                                                <th width="120px">Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="9" class="text-center">Klik tombol TAMPIL untuk menampilkan
                                                    data</td>
                                            </tr>
                                        </tbody>
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
        var tablePrioritas;
        var dataPrioritas = [];

        $(document).ready(function() {
            // Initialize DataTable
            initTable();

            // Button Tampil
            $('#btnTampil').on('click', function() {
                tampilData();
            });

            // Button Refresh
            $('#btnRefresh').on('click', function() {
                refreshData();
            });

            // Button Print
            $('#btnPrint').on('click', function() {
                printData();
            });

            // Button Proses
            $('#btnProses').on('click', function() {
                prosesData();
            });
        });

        function initTable() {
            tablePrioritas = $('#tablePrioritas').DataTable({
                data: [],
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                        className: 'text-center'
                    },
                    {
                        data: 'KD_BRG',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'SUB',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'NA_BRG',
                        defaultContent: '-'
                    },
                    {
                        data: 'KET_UK',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'KET_KEM',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'LPH',
                        className: 'text-right',
                        render: function(data) {
                            return formatNumber(data, 2);
                        },
                        defaultContent: '0'
                    },
                    {
                        data: 'SALDO',
                        className: 'text-right',
                        render: function(data) {
                            return formatNumber(data, 2);
                        },
                        defaultContent: '0'
                    },
                    {
                        data: 'TGL',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return moment(data).format('DD/MM/YYYY');
                            }
                            return '-';
                        },
                        defaultContent: '-'
                    }
                ],
                paging: true,
                pageLength: 50,
                searching: true,
                ordering: true,
                info: true,
                scrollX: true,
                language: {
                    emptyTable: "Tidak ada data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    lengthMenu: "Tampilkan _MENU_ data",
                    search: "Cari:",
                    zeroRecords: "Tidak ditemukan data yang sesuai",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });
        }

        function tampilData() {
            $('#LOADX').show();
            $('#btnTampil').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> LOADING...');

            $.ajax({
                url: '{{ route('barangprioritas_tampil') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#LOADX').hide();
                    $('#btnTampil').prop('disabled', false).html('<i class="fas fa-eye"></i> TAMPIL');

                    if (response.success && response.data.length > 0) {
                        dataPrioritas = response.data;

                        tablePrioritas.clear();
                        tablePrioritas.rows.add(response.data);
                        tablePrioritas.draw();

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil dimuat: ' + response.count + ' item',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        dataPrioritas = [];
                        tablePrioritas.clear().draw();
                        Swal.fire({
                            icon: 'info',
                            title: 'Informasi',
                            text: 'Tidak ada data barang prioritas'
                        });
                    }
                },
                error: function(xhr) {
                    $('#LOADX').hide();
                    $('#btnTampil').prop('disabled', false).html('<i class="fas fa-eye"></i> TAMPIL');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.error || 'Gagal mengambil data'
                    });
                }
            });
        }

        function refreshData() {
            $('#LOADX').show();
            $('#btnRefresh').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> LOADING...');

            $.ajax({
                url: '{{ route('barangprioritas_refresh') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#LOADX').hide();
                    $('#btnRefresh').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> REFRESH');

                    if (response.success && response.data.length > 0) {
                        dataPrioritas = response.data;

                        tablePrioritas.clear();
                        tablePrioritas.rows.add(response.data);
                        tablePrioritas.draw();

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil diperbarui: ' + response.count + ' item',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        dataPrioritas = [];
                        tablePrioritas.clear().draw();
                        Swal.fire({
                            icon: 'info',
                            title: 'Informasi',
                            text: 'Tidak ada data barang prioritas'
                        });
                    }
                },
                error: function(xhr) {
                    $('#LOADX').hide();
                    $('#btnRefresh').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> REFRESH');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.error || 'Gagal memperbarui data'
                    });
                }
            });
        }

        function printData() {
            if (dataPrioritas.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Tidak ada data untuk dicetak. Klik TAMPIL terlebih dahulu.'
                });
                return;
            }

            $('#LOADX').show();
            $('#btnPrint').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> LOADING...');

            // $.ajax({
            //     url: '{{ route('barangprioritas_print') }}',
            //     type: 'POST',
            //     data: {
            //         _token: '{{ csrf_token() }}'
            //     },
            //     success: function(response) {
            //         $('#LOADX').hide();
            //         $('#btnPrint').prop('disabled', false).html('<i class="fas fa-print"></i> PRINT');

            //         if (response.success) {

            //             Swal.fire({
            //                 icon: 'success',
            //                 title: 'Print Siap',
            //                 html: `
            //         Data siap dicetak<br>
            //         Total: <b>${response.count}</b> item<br>
            //         User: <b>${response.user}</b><br>
            //         Cabang: <b>${response.cbg}</b>
            //     `,
            //                 showCancelButton: true,
            //                 confirmButtonText: 'Tampilkan Jasper',
            //                 cancelButtonText: 'Batal'
            //             }).then((result) => {

            //                 if (result.isConfirmed) {
            //                     $.ajax({
            //                         url: '{{ route('barangprioritas_print') }}',
            //                         type: 'GET',
            //                         xhrFields: {
            //                             responseType: 'blob'
            //                         },
            //                         success: function(pdfBlob) {
            //                             let fileURL = URL.createObjectURL(pdfBlob);
            //                             window.open(fileURL, '_blank');
            //                         },
            //                         error: function() {
            //                             Swal.fire({
            //                                 icon: 'error',
            //                                 title: 'Gagal',
            //                                 text: 'Gagal menampilkan file jasper'
            //                             });
            //                         }
            //                     });
            //                 }

            //             });

            //         } else {
            //             Swal.fire({
            //                 icon: 'error',
            //                 title: 'Error',
            //                 text: 'Gagal menyiapkan data untuk print'
            //             });
            //         }
            //     },
            //     error: function(xhr) {
            //         $('#LOADX').hide();
            //         $('#btnPrint').prop('disabled', false).html('<i class="fas fa-print"></i> PRINT');
            //         Swal.fire({
            //             icon: 'error',
            //             title: 'Error',
            //             text: xhr.responseJSON?.error || 'Gagal menyiapkan print'
            //         });
            //     }
            // });
			$.ajax({
    url: '{{ route('barangprioritas_print') }}',
    type: 'GET',
    xhrFields: { responseType: 'blob' },
    success: function(pdfBlob) {
        let fileURL = URL.createObjectURL(pdfBlob);
        window.open(fileURL, '_blank');
    }
});


        }

        function prosesData() {
            if (dataPrioritas.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Tidak ada data untuk diproses. Klik TAMPIL terlebih dahulu.'
                });
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin ingin memproses data barang prioritas?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Proses',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#LOADX').show();
                    $('#btnProses').prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin"></i> MEMPROSES...');

                    $.ajax({
                        url: '{{ route('barangprioritas_proses') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            $('#LOADX').hide();
                            $('#btnProses').prop('disabled', false).html(
                                '<i class="fas fa-paper-plane"></i> PROSES');

                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    html: response.message
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Proses gagal'
                                });
                            }
                        },
                        error: function(xhr) {
                            $('#LOADX').hide();
                            $('#btnProses').prop('disabled', false).html(
                                '<i class="fas fa-paper-plane"></i> PROSES');
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.error || 'Proses gagal'
                            });
                        }
                    });
                }
            });
        }

        function formatNumber(num, decimals) {
            var n = parseFloat(num);
            if (isNaN(n)) return '0';

            return n.toLocaleString('id-ID', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }
    </script>
@endsection
