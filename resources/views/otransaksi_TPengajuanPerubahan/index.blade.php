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
            padding: 8px 20px;
            font-weight: 600;
            font-size: 14px;
            margin: 0 3px;
        }

        .btn-new {
            background: #28a745;
            border: none;
            color: #fff;
        }

        .btn-new:hover {
            background: #218838;
            color: #fff;
        }

        .table thead th {
            background: #343a40;
            color: white;
            border: none;
            font-size: 13px;
            padding: 12px 8px;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table tbody td {
            padding: 8px;
            font-size: 13px;
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

        .text-right {
            text-align: right !important;
        }

        .text-center {
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
                                <!-- Info Box -->
                                <div class="info-box">
                                    <p class="mb-1"><strong>Petunjuk:</strong></p>
                                    <ul class="mb-0">
                                        <li>Klik <strong>NEW</strong> untuk menambah pengajuan perubahan baru</li>
                                        <li>Klik <strong>Edit</strong> untuk mengubah data pengajuan</li>
                                        <li>Klik <strong>Detail</strong> untuk melihat detail item pengajuan</li>
                                        <li>Klik <strong>Hapus</strong> untuk menghapus data pengajuan (hanya data yang
                                            belum diposting)</li>
                                        <li>Data pengajuan perubahan meliputi: Ubah Kartu (UK), Ubah Harga (UH), Ubah Data
                                            (UD), dan Ubah Jualan (UJ)</li>
                                    </ul>
                                </div>

                                <div class="mb-3 text-right">
                                    <button type="button" id="btnNew" class="btn btn-action btn-new">
                                        <i class="fas fa-plus"></i> NEW
                                    </button>
                                </div>
                                <div class="mb-3 text-right">
                                    <button type="button" id="btnUsulan" class="btn btn-action btn-new">
                                        <i class="fas fa-plus"></i> Usulan Harga Margin
                                    </button>
                                </div>

                                <hr>

                                <!-- Data Table -->
                                <div class="table-responsive">
                                    <table class="table-striped table-bordered table-hover table" id="tableData"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th width="50px" class="text-center">No</th>
                                                <th width="180px">No Pengajuan</th>
                                                <th width="120px" class="text-center">Tanggal</th>
                                                <th>Uraian</th>
                                                <th width="100px" class="text-center">Posted</th>
                                                <th width="280px" class="text-center">Aksi</th>
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
    </div>
    <div class="modal fade" id="modalUsulan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Usulan Harga Margin</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <form id="formUsulan">

                        <div class="form-group">
                            <label>SUB</label>
                            <input type="text" id="SUB" name="SUB" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>KDBAR 1</label>
                            <input type="text" id="KDBAR1" name="KDBAR1" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>KDBAR 2</label>
                            <input type="text" id="KDBAR2" name="KDBAR2" class="form-control">
                        </div>
                    </form>

                </div>

                <div class="modal-footer">
                    <button type="button" id="btnSaveUsulan" class="btn btn-primary">
                        Proses
                    </button>
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

        $(document).ready(function() {
            // Initialize DataTable
            table = $('#tableData').DataTable({
                ajax: {
                    url: '{{ route('pengajuanperubahan_cari') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                        className: 'text-center'
                    },
                    {
                        data: 'NO_BUKTI'
                    },
                    {
                        data: 'TGL',
                        className: 'text-center'
                    },
                    {
                        data: 'URAIAN'
                    },
                    {
                        data: 'POSTED',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        className: 'text-center'
                    }
                ],
                order: [
                    [2, 'desc']
                ],
                processing: true
            });

            // Button New - navigate to edit page with 'new' as no_bukti
            $('#btnNew').on('click', function(e) {
                e.preventDefault();
                var url = '{{ url('tpengajuanperubahan/edit/new') }}';
                console.log('Navigating to:', url);
                window.location.href = url;
            });

            // Button Edit
            $(document).on('click', '.btn-edit', function() {
                var nobukti = $(this).data('nobukti');
                window.location.href = '{{ route('pengajuanperubahan') }}/edit/' + encodeURIComponent(
                    nobukti);
            });

            $(document).on('click', '.btn-print', function() {
                let nobukti = $(this).data('nobukti');

                let url = '{{ route('tpengajuanperubahan.print') }}?no_bukti=' + encodeURIComponent(
                nobukti);

                window.open(url, '_blank');
            });


            // Button Detail
            $(document).on('click', '.btn-detail', function() {
                var nobukti = $(this).data('nobukti');
                showDetail(nobukti);
            });

            // Button Delete
            $(document).on('click', '.btn-delete', function() {
                if ($(this).prop('disabled')) {
                    return;
                }

                var nobukti = $(this).data('nobukti');

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Apakah No. Bukti ' + nobukti + ' akan dihapus?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-check"></i> Ya, Hapus!',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteData(nobukti);
                    }
                });
            });
        });

        function deleteData(nobukti) {
            $('#LOADX').show();

            $.ajax({
                url: '{{ route('pengajuanperubahan_proses') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    action: 'delete',
                    no_bukti: nobukti
                },
                success: function(response) {
                    $('#LOADX').hide();

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        table.ajax.reload();
                    }
                },
                error: function(xhr) {
                    $('#LOADX').hide();

                    var errorMsg = 'Gagal menghapus data';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                }
            });
        }

        function showDetail(nobukti) {
            Swal.fire({
                title: 'Detail Pengajuan: ' + nobukti,
                html: '<div id="detailContent">Loading...</div>',
                width: '1200px',
                showCloseButton: true,
                showConfirmButton: false,
                didOpen: function() {
                    loadDetailData(nobukti);
                }
            });
        }

        function loadDetailData(nobukti) {
            $.ajax({
                url: '{{ route('pengajuanperubahan') }}/detail/' + encodeURIComponent(nobukti),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    var html =
                        '<div class="table-responsive"><table class="table table-sm table-bordered" style="font-size: 12px;">';
                    html += '<thead><tr>';
                    html +=
                        '<th>No</th><th>Kode</th><th>Uraian</th><th>HJ Lama</th><th>HJ</th><th>HJ Baru</th>';
                    html += '<th>LPH</th><th>LPH Baru</th><th>DTR</th><th>DTR Baru</th>';
                    html += '<th>KK</th><th>KK Baru</th><th>Catatan</th>';
                    html += '<th>MOO</th><th>MOO Baru</th><th>Cabang</th><th>Ordr</th>';
                    html += '</tr></thead><tbody>';

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(item, index) {
                            html += '<tr>';
                            html += '<td>' + (index + 1) + '</td>';
                            html += '<td>' + item.KD_BRG + '</td>';
                            html += '<td>' + item.NA_BRG + '</td>';
                            html += '<td class="text-right">' + item.HJ_LAMA + '</td>';
                            html += '<td class="text-right">' + item.HJ + '</td>';
                            html += '<td class="text-right">' + item.HJ_BARU + '</td>';
                            html += '<td class="text-right">' + item.LPH + '</td>';
                            html += '<td class="text-right">' + item.LPH_BARU + '</td>';
                            html += '<td class="text-right">' + item.DTR + '</td>';
                            html += '<td class="text-right">' + item.DTR_BARU + '</td>';
                            html += '<td>' + (item.KK || '') + '</td>';
                            html += '<td>' + (item.KK_BARU || '') + '</td>';
                            html += '<td>' + (item.CATATAN || '') + '</td>';
                            html += '<td class="text-right">' + item.MOO + '</td>';
                            html += '<td class="text-right">' + item.MOO_BARU + '</td>';
                            html += '<td>' + (item.CABANG || '') + '</td>';
                            html += '<td>' + (item.ORDR || '') + '</td>';
                            html += '</tr>';
                        });
                    } else {
                        html += '<tr><td colspan="17" class="text-center">Tidak ada data</td></tr>';
                    }

                    html += '</tbody></table></div>';
                    $('#detailContent').html(html);
                },
                error: function() {
                    $('#detailContent').html('<p class="text-danger">Gagal memuat data</p>');
                }
            });
        }

        $('#btnUsulan').on('click', function() {
            $('#modalUsulan').modal('show');
        });

        $('#btnSaveUsulan').on('click', function() {
            let data = {
                SUB: $('#SUB').val().trim(),
                KDBAR1: $('#KDBAR1').val().trim(),
                KDBAR2: $('#KDBAR2').val().trim(),
                _token: '{{ csrf_token() }}'
            };

            // $.ajax({
            //     url: "{{ route('tpengajuanperubahan.usulan-save') }}",
            //     method: "POST",
            //     data: data,
            //     success: function (res) {
            //         if (res.success) {
            //             alert('Usulan berhasil disimpan');
            //             $('#modalUsulan').modal('hide');
            //         } else {
            //             alert(res.message);
            //         }
            //     },
            //     error: function () {
            //         alert('Terjadi kesalahan saat menyimpan usulan');
            //     }
            // });
            $.ajax({
                url: "{{ route('tpengajuanperubahan.usulan-save') }}",
                method: "POST",
                data: data,
                success: function(res) {
                    if (res.success) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Proses',
                            html: `
                    <div style="font-size:16px; margin-top:8px;">
                        Usulan berhasil disimpan.<br>
                        <b>No. Bukti: ${res.bukti}</b>
                    </div>
                `,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6'
                        });

                        $('#modalUsulan').modal('hide');

                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal',
                            text: res.message,
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat menyimpan usulan',
                        confirmButtonText: 'OK'
                    });
                }
            });

        });
    </script>
@endsection
