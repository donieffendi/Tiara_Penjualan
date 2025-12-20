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
                                            Eksport SO (belum)
                                        </button>

                                        <button id="import-so" type="button" class="btn btn-warning">
                                            Import SO (belum)
                                        </button>

                                        <a href="{{ route('tprosesstockopname.koreksi', ['status' => 'simpan']) }}"
                                            id="koreksi-so" type="button" class="btn btn-danger">
                                            Koreksi SO (belum)
                                        </a>

                                    </div>

                                </div>
                            </div>
                            {{-- <div class="card-body">
                                <table id="datatable" class="table-bordered table-striped table-sm table">
                                    <thead>
                                        <tr>
                                            <th width="3%" class="text-center">
                                                <input type="checkbox" id="check-all">
                                            </th>
                                            <th width="5%">No</th>
                                            <th width="20%">No Bukti</th>
                                            <th width="15%">Tanggal</th>
                                            <th width="15%">Sub</th>
                                            <th width="15%">Username</th>
                                            <th width="10%">POSTED</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div> --}}
                            <div class="card-body">

                                <!-- ===== TAB MENU ===== -->
                                <ul class="nav nav-tabs" id="soTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="so1-tab" data-toggle="tab" href="#tab-so1"
                                            role="tab">Setalah buat SO</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="so2-tab" data-toggle="tab" href="#tab-so2"
                                            role="tab">Setelah Koreksi (belum)</a>
                                    </li>
                                </ul>

                                <!-- ===== TAB ISI ===== -->
                                <div class="tab-content mt-3">

                                    <!-- TAB SO 1 -->
                                    <div class="tab-pane fade show active" id="tab-so1" role="tabpanel">
                                        <table id="datatable-so1" class="table table-bordered table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th class="text-center"><input type="checkbox" id="check-all-so1"></th>
                                                    <th>No</th>
                                                    <th>No Bukti</th>
                                                    <th>Tanggal</th>
                                                    <th>Sub</th>
                                                    <th>Username</th>
                                                    <th>Posted</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>

                                    <!-- TAB SO 2 -->
                                    <div class="tab-pane fade" id="tab-so2" role="tabpanel">
                                        <table id="datatable-so2" class="table table-bordered table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th class="text-center"><input type="checkbox" id="check-all-so2"></th>
                                                    <th>No</th>
                                                    <th>No Bukti</th>
                                                    <th>Tanggal</th>
                                                    <th>Sub</th>
                                                    <th>Username</th>
                                                    <th>Posted</th>
                                                    <th>Action</th>
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
        </div>
    </div>
@endsection

@section('javascripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- <script>
    $(document).ready(function() {

        // ==============================
        // INIT DATATABLE
        // ==============================


        // ==============================
        // LOAD TABLE DEFAULT (SO1)
        // ==============================
        loadTable('SO1');


        // ==============================
        // TAB CLICK HANDLER
        // ==============================
        $('#tab-so1').on('click', function() {
            loadTable('SO1');
        });

        $('#tab-so2').on('click', function() {
            loadTable('SO2');
        });


        // ==============================
        // PRINT
        // ==============================
        $('#print-so').click(function() {
            let selected = $('.pilih-bukti:checked').map(function() {
                return $(this).val();
            }).get();

            if (selected.length === 0) {
                return Swal.fire('Oops!', 'Pilih minimal 1 No Bukti dulu.', 'warning');
            }

            let url = "{{ route('tprosesstockopname.print') }}" + "?nobukti=" + selected.join(',');
            window.open(url, "_blank");
        });


        // ==============================
        // BUAT SO2
        // ==============================
        $('#buat-so2').click(function() {

            let selected = $('.pilih-bukti:checked').val();

            if (!selected) {
                return Swal.fire('Oops!', 'Pilih 1 No Bukti dulu.', 'warning');
            }

            if (!(selected.startsWith('XO') || selected.startsWith('XG'))) {
                return Swal.fire('Tidak Valid', 'Hanya No Bukti XO atau XG yang dapat diproses.', 'error');
            }

            Swal.fire({
                title: "Yakin?",
                text: "Buat SO2 untuk nomor " + selected + " ?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Ya, Buat!",
                cancelButtonText: "Batal"
            }).then(result => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route('tprosesstockopname.buat-so2') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        no_bukti: selected
                    },
                    success: function(res) {

                        if (res.success) {
                            Swal.fire("Berhasil!", "SO2 baru dibuat: " + res.bukti_baru, "success");
                            $('#datatable').DataTable().ajax.reload();
                        } else {
                            Swal.fire("Gagal", res.message, "error");
                        }
                    },
                    error: function() {
                        Swal.fire("Error", "Terjadi kesalahan server.", "error");
                    }
                });
            });

        });

    });

    // ==============================
    // EDIT DATA
    // ==============================
    function editData(noBukti) {
        window.location.href =
            "{{ route('tprosesstockopname.edit') }}?status=edit&no_bukti=" + noBukti;
    }

    // ==============================
    // DELETE DATA
    // ==============================
    function deleteData(noBukti) {

        Swal.fire({
            title: 'Hapus Data?',
            text: 'Data akan dihapus permanen',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!'
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ route('tprosesstockopname.delete', '') }}/" + noBukti,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    Swal.fire('Berhasil!', response.message, 'success');
                    $('#datatable').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus data', 'error');
                }
            });
        });

    }
</script> --}}
    <script>
        $(document).ready(function() {
            let activeTab = 'SO1';

            function loadTable(tipe) {

                activeTab = tipe;

                let tableId = tipe === 'SO1' ?
                    '#datatable-so1' :
                    '#datatable-so2';

                let url = "{{ route('tprosesstockopname.get-data', ['tab' => '__TAB__']) }}";
                url = url.replace('__TAB__', tipe);
                if ($.fn.DataTable.isDataTable(tableId)) {
                    $(tableId).DataTable().destroy();
                }

                $(tableId).DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    ajax: {
                        url: url,
                        data: {
                            tipe: tipe
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error Loading Data',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    },
                    columns: [{
                            data: 'NO_BUKTI',
                            className: 'text-center',
                            orderable: false,
                            searchable: false,
                            render: data =>
                                `<input type="checkbox" class="pilih-bukti" value="${data}">`
                        },
                        {
                            data: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
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
                            data: 'SUB'
                        },
                        {
                            data: 'USRNM'
                        },
                        {
                            data: 'POSTED',
                            className: 'text-center',
                            render: (data, type, row) =>
                                `<input type="checkbox" class="cek-posted" data-id="${row.NO_BUKTI}" ${data == 1 ? 'checked' : ''}>`
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        }
                    ],
                    order: [
                        [1, 'desc']
                    ]
                });

            }

            loadTable('SO1');

            $('#so1-tab').on('click', function() {
                loadTable('SO1');
            });

            $('#so2-tab').on('click', function() {
                loadTable('SO2');
            });


            window.editData = function(noBukti) {

                if (activeTab === 'SO1') {
                    window.location.href =
                        "{{ route('tprosesstockopname.edit') }}" +
                        "?status=edit&no_bukti=" + noBukti;
                } else {
                    window.location.href =
                        "{{ route('tprosesstockopname.koreksi') }}" +
                        "?status=edit&no_bukti=" + noBukti;
                }
            };


            window.deleteData = function(noBukti) {
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
                                Swal.fire('Error', xhr.responseJSON?.message ||
                                    'Gagal menghapus data',
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
                                Swal.fire("Error", "Terjadi kesalahan server.",
                                    "error");
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
