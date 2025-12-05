@extends('layouts.plain')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Proses Stock Opname</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item active">Proses Stock Opname</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-6 d-flex flex-wrap align-items-start gap-2">

                                        <a href="{{ route('tprosesstockopname.edit', ['status' => 'simpan']) }}"
                                            class="btn btn-primary">
                                            <i class="fas fa-plus"></i> New
                                        </a>

                                        <button id="print-so" type="button" class="btn btn-secondary">
                                            Print
                                        </button>

                                        <button id="buat-so2" type="button" class="btn btn-success">
                                            Buat SO2
                                        </button>

                                        <button id="eksport-so" type="button" class="btn btn-info text-white">
                                            Eksport SO
                                        </button>

                                        <button id="import-so" type="button" class="btn btn-warning">
                                            Import SO
                                        </button>

                                        <a href="{{ route('tprosesstockopname.koreksi', ['status' => 'simpan']) }}" id="koreksi-so" type="button" class="btn btn-danger">
                                            Koreksi SO
                                        </a>

                                    </div>

                                </div>
                            </div>
                            <div class="card-body">
                                <table id="datatable" class="table-bordered table-striped table-sm table">
                                    <thead>
                                        <tr>
                                            <th width="3%" class="text-center">
                                                <input type="checkbox" id="check-all">
                                            </th>
                                            <th width="5%">No</th>
                                            <th width="20%">No Bukti</th>
                                            <th width="15%">Tanggal</th>
                                            <th width="15%">Total Qty</th>
                                            <th width="15%">Notes</th>
                                            <th width="10%">Type</th>
                                        </tr>
                                    </thead>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            var table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('tprosesstockopname.get-data') }}",
                    error: function(xhr, error, code) {
                        console.error('DataTables AJAX error:', xhr.responseJSON);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Loading Data',
                            text: xhr.responseJSON?.message || xhr.responseJSON?.error ||
                                'Terjadi kesalahan saat memuat data',
                            footer: 'Silakan periksa log atau hubungi administrator'
                        });
                    }
                },
                columns: [{
                        data: 'NO_BUKTI',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data) {
                            return `<input type="checkbox" class="pilih-bukti" value="${data}">`;
                        }
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'NO_BUKTI',
                        name: 'NO_BUKTI'
                    },
                    {
                        data: 'tgl',
                        name: 'tgl',
                        className: 'text-center'
                    },
                    {
                        data: 'total_qty',
                        name: 'total_qty'
                    },
                    {
                        data: 'NOTES',
                        name: 'NOTES'
                    },
                    {
                        data: 'TYPE',
                        name: 'TYPE',
                        className: 'text-center'
                    }
                ],
                order: [
                    [1, 'desc']
                ]
            });

            // Session messages
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '{{ session('error') }}'
                });
            @endif

            @if (session('warning'))
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: '{{ session('warning') }}'
                });
            @endif

            @if (session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Informasi',
                    text: '{{ session('info') }}'
                });
            @endif
        });

        function editData(noBukti) {
            window.location.href = "{{ route('tprosesstockopname.edit') }}?status=edit&no_bukti=" + noBukti;
        }

        function deleteData(noBukti) {
            Swal.fire({
                title: 'Hapus Data?',
                text: 'Data akan dihapus permanen',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('tprosesstockopname.delete', '') }}/" + noBukti,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            $('#datatable').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus data',
                                'error');
                        }
                    });
                }
            });
        }

        $('#print-so').click(function() {

            let selected = [];

            $('.pilih-bukti:checked').each(function() {
                selected.push($(this).val());
            });

            if (selected.length === 0) {
                Swal.fire('Oops!', 'Pilih minimal 1 No Bukti dulu.', 'warning');
                return;
            }

            let url = "{{ route('tprosesstockopname.print') }}" + "?nobukti=" + selected.join(',');

            window.open(url, "_blank");
        });

        $('#buat-so2').click(function() {

            let selected = $('.pilih-bukti:checked').val();

            if (!selected) {
                Swal.fire('Oops!', 'Pilih 1 No Bukti dulu.', 'warning');
                return;
            }

            if (!(selected.startsWith('XO') || selected.startsWith('XG'))) {
                Swal.fire('Tidak Valid', 'Hanya No Bukti XO atau XG yang dapat diproses.', 'error');
                return;
            }

            Swal.fire({
                title: "Yakin?",
                text: "Buat SO2 untuk nomor " + selected + " ?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Ya, Buat!",
                cancelButtonText: "Batal"
            }).then((result) => {

                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('tprosesstockopname.buat-so2') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            no_bukti: selected
                        },
                        success: function(res) {

                            if (res.success) {
                                Swal.fire("Berhasil!",
                                    "SO2 baru dibuat: " + res.bukti_baru,
                                    "success"
                                );

                                $('#datatable').DataTable().ajax.reload();
                            } else {
                                Swal.fire("Gagal", res.message, "error");
                            }
                        },
                        error: function(xhr) {
                            Swal.fire("Error", "Terjadi kesalahan server.", "error");
                        }
                    });
                }
            });
        });
    </script>
@endsection
