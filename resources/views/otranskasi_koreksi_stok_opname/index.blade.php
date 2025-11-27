@extends('layouts.plain')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Koreksi Stock Opname</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item active">Koreksi Stock Opname</li>
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
                                    <div class="col-md-6">
                                        <a href="{{ route('tkoreksistokopname.edit', ['status' => 'simpan']) }}"
                                            class="btn btn-primary">
                                            <i class="fas fa-plus"></i> New
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="datatable" class="table-bordered table-striped table-sm table">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="20%">No Bukti</th>
                                            <th width="15%">Tgl</th>
                                            <th width="15%">Total Qty</th>
                                            <th width="20%">Notes</th>
                                            <th width="10%">Posted</th>
                                            <th width="15%">Action</th>
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
    <script>
        $(document).ready(function() {
            var table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('tkoreksistokopname.get-data') }}"
                },
                columns: [{
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
                        data: 'TGL',
                        name: 'TGL',
                        className: 'text-center'
                    },
                    {
                        data: 'TOTAL_QTY',
                        name: 'TOTAL_QTY',
                        className: 'text-right'
                    },
                    {
                        data: 'NOTES',
                        name: 'NOTES'
                    },
                    {
                        data: 'POSTED',
                        name: 'POSTED',
                        className: 'text-center'
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
        });

        function editData(noBukti) {
            window.location.href = "{{ route('tkoreksistokopname.edit') }}?status=edit&no_bukti=" + noBukti;
        }

        function deleteData(noBukti) {
            Swal.fire({
                title: 'Hapus Data?',
                text: 'Apakah kode ini ' + noBukti + ' akan di hapus?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('tkoreksistokopname.delete', '') }}/" + noBukti,
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

        function printData(noBukti) {
            $.ajax({
                url: "{{ route('tkoreksistokopname.print') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    no_bukti: noBukti
                },
                success: function(response) {
                    if (response.data && response.data.length > 0) {
                        var printWindow = window.open('', '_blank');
                        printWindow.document.write('<html><head><title>Print Koreksi Stock Opname</title>');
                        printWindow.document.write(
                            '<style>body{font-family:Arial;font-size:12px;} table{width:100%;border-collapse:collapse;margin-top:20px;} th,td{border:1px solid #000;padding:5px;} th{background-color:#f0f0f0;} .text-center{text-align:center;} .text-right{text-align:right;}</style>'
                        );
                        printWindow.document.write('</head><body>');
                        printWindow.document.write('<h2>Koreksi Stock Opname</h2>');
                        printWindow.document.write('<p><strong>Toko:</strong> ' + (response.data[0].nmtoko ||
                            '') + '</p>');
                        printWindow.document.write('<p><strong>No. Bukti:</strong> ' + noBukti + '</p>');
                        printWindow.document.write(
                            '<table><thead><tr><th>No</th><th>Item/Sub</th><th>Kode</th><th>Nama Barang</th><th>Kemasan</th><th>Qty</th><th>HJ</th><th>HB</th><th>Total HB</th><th>Alasan</th></tr></thead><tbody>'
                        );

                        response.data.forEach(function(item, index) {
                            printWindow.document.write(
                                '<tr>' +
                                '<td class="text-center">' + (index + 1) + '</td>' +
                                '<td>' + (item.ITEMSUB || '') + '</td>' +
                                '<td>' + (item.KD || '') + '</td>' +
                                '<td>' + (item.NA_BRG || '') + '</td>' +
                                '<td>' + (item.KET_KEM || '') + '</td>' +
                                '<td class="text-right">' + parseFloat(item.qty || 0).toFixed(2) +
                                '</td>' +
                                '<td class="text-right">' + parseFloat(item.HJ || 0).toFixed(0) +
                                '</td>' +
                                '<td class="text-right">' + parseFloat(item.HB || 0).toFixed(0) +
                                '</td>' +
                                '<td class="text-right">' + parseFloat(item.total_hb || 0).toFixed(
                                    0) + '</td>' +
                                '<td>' + (item.ALASAN || '') + '</td>' +
                                '</tr>'
                            );
                        });

                        printWindow.document.write('</tbody></table></body></html>');
                        printWindow.document.close();
                        printWindow.print();
                    } else {
                        Swal.fire('Info', 'Tidak ada data untuk dicetak', 'info');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Gagal memuat data print', 'error');
                }
            });
        }
    </script>
@endsection
