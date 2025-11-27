@extends('layouts.plain')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ $title }}</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item active">{{ $title }}</li>
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
                            <div class="card-body">

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label>Periode</label>
                                        <select class="form-control" id="cbPeriode">
                                            <option value="">- Pilih Periode -</option>
                                            @foreach ($periodeList as $periode)
                                                <option value="{{ $periode }}">{{ $periode }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-primary" id="btnTampil">
                                            <i class="fas fa-search mr-1"></i>Tampil
                                        </button>
                                        {{-- <button type="button" class="btn btn-success ml-2" id="btnExcel">
											<i class="fas fa-file-excel mr-1"></i>Excel
										</button> --}}
                                        <button type="button" class="btn btn-success ml-2" id="btnPrint">
                                            <i class="fas fa-file-excel mr-1"></i>Print
                                        </button>
                                        <button type="button" class="btn btn-secondary ml-2" id="btnClose">
                                            <i class="fas fa-times mr-1"></i>Close
                                        </button>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <table id="tblPersetujuan" class="table-bordered table-striped table">
                                            <thead>
                                                <tr>
                                                    <th>Bentuk Sarana</th>
                                                    <th>Nomer Sarana</th>
                                                    <th>Nama Supplier</th>
                                                    <th>Merek Yang Dipromosikan</th>
                                                    <th>Jenis Produk</th>
                                                    <th>Tanggal Berlaku</th>
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

    </div>
@endsection

@section('javascripts')
    <script>
        let table;
        const cbgMst = "{{ $cbgMst }}";

        $(document).ready(function() {

            table = $('#tblPersetujuan').DataTable({
                processing: true,
                serverSide: false,
                paging: true,
                searching: true,
                ordering: true,
                columns: [{
                        data: 'KD_SARANA'
                    },
                    {
                        data: 'LOKASI'
                    },
                    {
                        data: 'NAMAS'
                    },
                    {
                        data: 'MEREK'
                    },
                    {
                        data: 'JNS_PRODUK'
                    },
                    {
                        data: 'PERIODE'
                    }
                ]
            });

            // ======================
            // TAMPIL DATA
            // ======================
            $('#btnTampil').on('click', function() {
                let periode = $('#cbPeriode').val().trim();

                $.ajax({
                    url: "{{ route('phlaporanpersetujuan.get-data') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        periode: periode,
                        cbg: cbgMst
                    },
                    success: function(response) {
                        if (response.success) {
                            table.clear();
                            if (response.data && response.data.length > 0) {
                                table.rows.add(response.data);
                            }
                            table.draw();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat mengambil data');
                    }
                });
            });

            // ======================
            // EXPORT EXCEL
            // ======================
            $('#btnExcel').on('click', function() {
                let periode = $('#cbPeriode').val().trim();

                if (!periode) {
                    alert('Cek Periode!');
                    $('#cbPeriode').focus();
                    return;
                }

                $.ajax({
                    url: "{{ route('phlaporanpersetujuan.export-excel') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        periode: periode,
                        cbg: cbgMst
                    },
                    success: function(response) {
                        if (response.success) {
                            let ws_data = [
                                ['Bentuk Sarana', 'Nomer Sarana', 'Nama Supplier',
                                    'Merek Yang Dipromosikan', 'Jenis Produk',
                                    'Tanggal Berlaku'
                                ]
                            ];

                            response.data.forEach(row => {
                                ws_data.push([
                                    row.KD_SARANA,
                                    row.LOKASI,
                                    row.NAMAS,
                                    row.MEREK,
                                    row.JNS_PRODUK,
                                    row.PERIODE
                                ]);
                            });

                            let ws = XLSX.utils.aoa_to_sheet(ws_data);
                            let wb = XLSX.utils.book_new();
                            XLSX.utils.book_append_sheet(wb, ws, 'Persetujuan Sewa');
                            XLSX.writeFile(wb, 'Laporan_Persetujuan_Sewa_' + periode.replace(
                                '/', '_') + '.xlsx');
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat export excel');
                    }
                });
            });

            // ======================
            // PRINT
            // ======================
            $('#btnPrint').on('click', function() {
                let periode = $('#cbPeriode').val().trim();

                $.ajax({
                    url: "{{ route('phlaporanpersetujuan.print') }}",
                    method: 'GET',
                    data: {
                        _token: '{{ csrf_token() }}',
                        periode: periode,
                        cbg: cbgMst
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(blob) {
                        let fileURL = URL.createObjectURL(blob);
                        window.open(fileURL, '_blank');
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat print');
                    }
                });
            });

            // ======================
            // CLOSE PAGE
            // ======================
            $('#btnClose').on('click', function() {
                window.location.href = "{{ url('/') }}";
            });

        });
    </script>
@endsection
