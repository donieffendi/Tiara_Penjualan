@extends('layouts.plain')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ $status == 'simpan' ? 'New' : 'Edit' }} Koreksi Stock Opname</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <form id="form-stock-opname" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="{{ $status }}">

                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-sm btn-success" id="btn-save">
                                        <i class="fas fa-save"></i> Save
                                    </button>
                                    <a href="{{ route('tprosesstockopname') }}" class="btn btn-sm btn-danger">
                                        <i class="fas fa-times"></i> Exit
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if (isset($error) && $error)
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <strong>Error!</strong> {{ $error }}
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>No Bukti</label>
                                        <input type="text" class="form-control form-control-sm" name="no_bukti"
                                            value="{{ $header->no_bukti ?? '+' }}" readonly
                                            style="background-color: #e9ecef; font-weight: bold;">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" name="tgl"
                                            value="{{ $header->tgl ?? date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Type</label>
                                        <input type="text" class="form-control form-control-sm" name="type"
                                            value="{{ $header->notes ?? '' }}" placeholder="Keterangan">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Sub</label>
                                        <input type="text" class="form-control form-control-sm" name="sub"
                                            value="{{ $header->notes ?? '' }}" placeholder="Keterangan">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- ROW 1 -->
                            <div class="row">

                                <!-- Sub -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>No SO</label>
                                        <input type="text" class="form-control form-control-sm" name="no_so"
                                            id="no_so">
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table-bordered table-striped table-sm table" id="table-detail">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th style="width:4%" class="text-center">No</th>
                                            <th style="width:8%" class="text-center">Kode</th>
                                            <th style="width:20%" class="text-center">Nama Barang</th>
                                            <th style="width:6%" class="text-center">Stok</th>
                                            <th style="width:6%" class="text-center">Riil</th>
                                            <th style="width:6%" class="text-center">Qty</th>
                                            <th style="width:7%" class="text-center">Qty Apps</th>
                                            <th style="width:8%" class="text-center">Harga</th>
                                            <th style="width:8%" class="text-center">Total</th>
                                            <th style="width:10%" class="text-center">Keterangan</th>
                                            <th style="width:6%" class="text-center">Selisih</th>
                                            <th style="width:4%" class="text-center">Cek</th>
                                            <th style="width:7%" class="text-center">Qty Trans</th>
                                            <th style="width:6%" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    {{-- <tbody id="tbody-detail">
                                        @if (!empty($detail) && count($detail) > 0)
                                            @foreach ($detail as $key => $row)
                                                <tr data-no-id="{{ $row->no_id ?? 0 }}">
                                                    <td class="text-center">{{ $key + 1 }}</td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="detail[{{ $key }}][kd_brg]"
                                                            value="{{ $row->kd_brg }}" readonly
                                                            style="background-color: #e9ecef;">
                                                        <input type="hidden" name="detail[{{ $key }}][no_id]"
                                                            value="{{ $row->no_id ?? 0 }}">
                                                        <input type="hidden" name="detail[{{ $key }}][rec]"
                                                            value="{{ $key + 1 }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="detail[{{ $key }}][na_brg]"
                                                            value="{{ $row->na_brg }}" readonly
                                                            style="background-color: #e9ecef;">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="detail[{{ $key }}][stand]"
                                                            value="{{ $row->STAND ?? '' }}" readonly
                                                            style="background-color: #e9ecef;">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            class="form-control form-control-sm text-right"
                                                            name="detail[{{ $key }}][hj]"
                                                            value="{{ $row->hj }}" readonly
                                                            style="background-color: #e9ecef;">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            class="form-control form-control-sm text-right saldo"
                                                            name="detail[{{ $key }}][saldo]"
                                                            value="{{ $row->saldo }}" readonly
                                                            style="background-color: #e9ecef;">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="detail[{{ $key }}][supp]"
                                                            value="{{ $row->SUPP ?? '' }}" readonly
                                                            style="background-color: #e9ecef;">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="checkbox" class="cek-item"
                                                            name="detail[{{ $key }}][cek]" value="1"
                                                            {{ ($row->cek ?? 0) == 1 ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button"
                                                            class="btn btn-xs btn-danger btn-delete-row">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="14" class="text-center">Tidak ada data. Gunakan tombol "All
                                                    In" untuk memuat data barang.</td>
                                            </tr>
                                        @endif
                                    </tbody> --}}
                                    <tbody id="tbody-detail">
                                        @if (!empty($detail) && count($detail) > 0)
                                            @foreach ($detail as $key => $row)
                                                <tr data-no-id="{{ $row->no_id ?? 0 }}">
                                                    {{-- No --}}
                                                    <td class="text-center">{{ $key + 1 }}</td>

                                                    {{-- Kode Barang --}}
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="detail[{{ $key }}][kd_brg]"
                                                            value="{{ $row->kd_brg }}" readonly
                                                            style="background-color:#e9ecef">
                                                        <input type="hidden" name="detail[{{ $key }}][no_id]"
                                                            value="{{ $row->no_id ?? 0 }}">
                                                        <input type="hidden" name="detail[{{ $key }}][rec]"
                                                            value="{{ $key + 1 }}">
                                                    </td>

                                                    {{-- Nama Barang --}}
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="detail[{{ $key }}][na_brg]"
                                                            value="{{ $row->na_brg }}" readonly
                                                            style="background-color:#e9ecef">
                                                    </td>

                                                    {{-- Saldo --}}
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="detail[{{ $key }}][saldo]"
                                                            value="{{ $row->saldo ?? '' }}" readonly
                                                            style="background-color:#e9ecef">
                                                    </td>

                                                    {{-- Riil --}}
                                                    <td>
                                                        <input type="text"
                                                            class="form-control form-control-sm text-right"
                                                            name="detail[{{ $key }}][riil]"
                                                            value="{{ $row->riil ?? 0 }}" readonly
                                                            style="background-color:#e9ecef">
                                                    </td>

                                                    {{-- Qty --}}
                                                    <td>
                                                        <input type="text"
                                                            class="form-control form-control-sm text-right qty"
                                                            name="detail[{{ $key }}][qty]"
                                                            value="{{ $row->qty ?? 0 }}" readonly
                                                            style="background-color:#e9ecef">
                                                    </td>

                                                    {{-- Qty Apps --}}
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="detail[{{ $key }}][qty_apps]"
                                                            value="{{ $row->qty_apps ?? 0 }}" readonly
                                                            style="background-color:#e9ecef">
                                                    </td>

                                                    {{-- Harga --}}
                                                    <td>
                                                        <input type="text"
                                                            class="form-control form-control-sm text-right harga"
                                                            name="detail[{{ $key }}][harga]"
                                                            value="{{ $row->harga ?? 0 }}" readonly
                                                            style="background-color:#e9ecef">
                                                    </td>

                                                    {{-- Total --}}
                                                    <td>
                                                        <input type="text"
                                                            class="form-control form-control-sm text-right total"
                                                            name="detail[{{ $key }}][total]"
                                                            value="{{ $row->total ?? 0 }}" readonly
                                                            style="background-color:#e9ecef">
                                                    </td>

                                                    {{-- Keterangan --}}
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="detail[{{ $key }}][ket]"
                                                            value="{{ $row->ket ?? '' }}" readonly
                                                            style="background-color:#e9ecef">
                                                    </td>

                                                    {{-- Qty Indi --}}
                                                    <td>
                                                        <input type="text"
                                                            class="form-control form-control-sm text-right qty_indi"
                                                            name="detail[{{ $key }}][qty_indi]"
                                                            value="{{ $row->qty_indi ?? 0 }}" readonly
                                                            style="background-color:#e9ecef">
                                                    </td>

                                                    {{-- Qty Trans --}}
                                                    <td>
                                                        <input type="text"
                                                            class="form-control form-control-sm text-right qty_trans"
                                                            name="detail[{{ $key }}][qty_trans]"
                                                            value="{{ $row->qty_trans ?? 0 }}" readonly
                                                            style="background-color:#e9ecef">
                                                    </td>

                                                    {{-- Cek --}}
                                                    <td class="text-center">
                                                        <input type="checkbox" class="cek-item"
                                                            name="detail[{{ $key }}][cek]" value="1"
                                                            {{ ($row->cek ?? 0) == 1 ? 'checked' : '' }}>
                                                    </td>

                                                    {{-- Aksi --}}
                                                    <td class="text-center">
                                                        <button type="button"
                                                            class="btn btn-xs btn-danger btn-delete-row">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="14" class="text-center">
                                                    Tidak ada data. Gunakan tombol <strong>"All In"</strong> untuk memuat
                                                    data barang.
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('javascripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let rowIndex = {{ count($detail ?? []) }};

            // All In - Load barang berdasarkan kriteria
            $('#btn-allin').click(function() {
                let sub = $('#sub').val().trim();


                $.ajax({
                    url: "{{ route('tprosesstockopname.browse') }}",
                    data: {
                        sub: sub
                    },
                    success: function(data) {
                        Swal.close();

                        if (data.length === 0) {
                            Swal.fire('Info', 'Tidak ada data barang ditemukan', 'info');
                            return;
                        }

                        $('#tbody-detail').empty();
                        rowIndex = 0;

                        data.forEach(function(item) {
                            addRowFromData(item);
                        });

                        Swal.fire('Berhasil', data.length + ' barang berhasil dimuat',
                            'success');
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal memuat data',
                            'error');
                    }
                });
            });


            function addRowFromData(item) {
                let newRow = `
                <tr data-no-id="0">
                    <td class="text-center">${rowIndex + 1}</td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][kd_brg]" value="${item.KD_BRG}" readonly style="background-color: #e9ecef;">
                        <input type="hidden" name="detail[${rowIndex}][no_id]" value="0">
                        <input type="hidden" name="detail[${rowIndex}][rec]" value="${rowIndex + 1}">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][na_brg]" value="${item.NA_BRG || ''}" readonly style="background-color: #e9ecef;">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][stand]" value="${item.KET_UK || ''}" readonly style="background-color: #e9ecef;">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-right" name="detail[${rowIndex}][hj]" value="${item.HJ || 0}" readonly style="background-color: #e9ecef;">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-right saldo" name="detail[${rowIndex}][saldo]" value="${item.saldo || 0}" readonly style="background-color: #e9ecef;">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="detail[${rowIndex}][supp]" value="${item.SUPP || ''}" readonly style="background-color: #e9ecef;">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="cek-item" name="detail[${rowIndex}][cek]" value="1" checked>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-danger btn-delete-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

                $('#tbody-detail').append(newRow);
                rowIndex++;
            }
            // Delete Row
            $(document).on('click', '.btn-delete-row', function() {
                $(this).closest('tr').remove();

                if ($('#tbody-detail tr').length === 0) {
                    $('#tbody-detail').html(
                        '<tr><td colspan="9" class="text-center">Tidak ada data</td></tr>');
                }

                // Renumber rows
                $('#tbody-detail tr').each(function(index) {
                    if (!$(this).find('td').first().attr('colspan')) {
                        $(this).find('td:first').text(index + 1);
                        $(this).find('input[name*="[rec]"]').val(index + 1);
                    }
                });
            });

            // Clear All
            $('#btn-clear-all').click(function() {
                Swal.fire({
                    title: 'Hapus Semua?',
                    text: 'Semua detail akan dihapus',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#tbody-detail').html(
                            '<tr><td colspan="9" class="text-center">Tidak ada data</td></tr>');
                        rowIndex = 0;
                    }
                });
            });

            // Auto-set item2 when item1 changes
            $('#item1').on('change', function() {
                let item1 = $(this).val().trim();
                if (item1) {
                    $('#item2').val(item1);
                } else {
                    $('#item2').val('ZZZZ');
                }
            });

            // Save
            $('#btn-save').on('click', function(e) {
                e.preventDefault();

                let tgl = $('input[name="tgl"]').val();

                if (!tgl) {
                    Swal.fire('Peringatan', 'Tanggal harus diisi', 'warning');
                    $('input[name="tgl"]').focus();
                    return;
                }

                let hasDetail = false;

                $('#tbody-detail tr').each(function() {
                    if (!$(this).find('td').first().attr('colspan')) {
                        let kdBrg = $(this).find('input[name*="[kd_brg]"]').val();
                        if (kdBrg && kdBrg.trim() !== '') {
                            hasDetail = true;
                        }
                    }
                });

                if (!hasDetail) {
                    Swal.fire('Peringatan', 'Detail barang harus diisi', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Simpan Data?',
                    text: 'Data akan disimpan ke database',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {

                        let formData = new FormData(document.getElementById(
                            'form-stock-opname'));

                        return $.ajax({
                            url: "{{ route('tprosesstockopname.store-koreksi_so') }}",
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: 'json'
                        }).catch(xhr => {
                            Swal.showValidationMessage(
                                xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat menyimpan data'
                            );
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: result.value?.message || 'Save Data Success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = "{{ route('tprosesstockopname') }}";
                        });
                    }
                });
            });


            $('#no_so').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();

                    let noSo = $(this).val();

                    if (noSo === '') {
                        alert('No SO tidak boleh kosong');
                        return;
                    }

                    loadNoSO(noSo);
                }
            });

            function loadNoSO(noSo) {

                $.ajax({
                    url: "{{ route('tprosesstockopname.browse-koreksi-so') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        no_so: noSo
                    },
                    beforeSend: function() {
                        $('#tbody-detail').html(`
                    <tr>
                        <td colspan="9" class="text-center">Loading...</td>
                    </tr>
                `);
                    },
                    success: function(res) {
                        // console.log(res);

                        if (!res.data || res.data.length === 0) {
                            $('#tbody-detail').html(`
                        <tr>
                            <td colspan="9" class="text-center">Data tidak ditemukan</td>
                        </tr>
                    `);
                            return;
                        }

                        let tbody = '';
                        let no = 1;

                        res.data.forEach(function(row, index) {

                            tbody += `
                        <tr>
                            <td class="text-center">${no++}</td>

                            <td>
                                <input type="text" class="form-control form-control-sm"
                                    name="detail[${index}][kd_brg]"
                                    value="${row.kd_brg}" readonly
                                    style="background-color:#e9ecef">
                                <input type="hidden" name="detail[${index}][no_id]" value="0">
                                <input type="hidden" name="detail[${index}][rec]" value="${index + 1}">
                            </td>

                            <td>
                                <input type="text" class="form-control form-control-sm"
                                    name="detail[${index}][na_brg]"
                                    value="${row.na_brg}" readonly
                                    style="background-color:#e9ecef">
                            </td>

                            <td>
                                <input type="text" class="form-control form-control-sm"
                                    name="detail[${index}][saldo]"
                                    value="${row.saldo ?? ''}" readonly
                                    style="background-color:#e9ecef">
                            </td>

                            <td>
                                <input type="text"
                                    class="form-control form-control-sm text-right"
                                    name="detail[${index}][riil]"
                                    value="${row.riil}" readonly
                                    style="background-color:#e9ecef">
                            </td>

                            <td>
                                <input type="text"
                                    class="form-control form-control-sm text-right qty"
                                    name="detail[${index}][qty]"
                                    value="${row.qty}" readonly
                                    style="background-color:#e9ecef">
                            </td>

                            <td>
                                <input type="text"
                                    class="form-control form-control-sm"
                                    name="detail[${index}][qty_apps]"
                                    value="${row.qty_apps}" readonly
                                    style="background-color:#e9ecef">
                            </td>
                            <td>
                                <input type="text"
                                    class="form-control form-control-sm text-right harga"
                                    name="detail[${index}][harga]"
                                    value="${row.harga}" readonly
                                    style="background-color:#e9ecef">
                            </td>

                            <td>
                                <input type="text"
                                    class="form-control form-control-sm text-right total"
                                    name="detail[${index}][total]"
                                    value="0" readonly
                                    style="background-color:#e9ecef">
                            </td>

                            <td>
                                <input type="text" class="form-control form-control-sm"
                                    name="detail[${index}][ket]"
                                    value="" readonly
                                    style="background-color:#e9ecef">
                            </td>

                            <td>
                                <input type="text"
                                    class="form-control form-control-sm text-right qty_indi"
                                    name="detail[${index}][qty_indi]"
                                    value="${row.qty_indi}" readonly
                                    style="background-color:#e9ecef">
                            </td>
                            <td>
                                <input type="text"
                                    class="form-control form-control-sm text-right qty_trans"
                                    name="detail[${index}][qty_trans]"
                                    value="${row.qty_trans}" readonly
                                    style="background-color:#e9ecef">
                            </td>

                            <td class="text-center">
                                <input type="checkbox"
                                    class="cek-item"
                                    name="detail[${index}][cek]"
                                    value="1">
                            </td>

                            <td class="text-center">
                                <button type="button"
                                    class="btn btn-xs btn-danger btn-delete-row">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                        });

                        $('#tbody-detail').html(tbody);
                    },
                    error: function(xhr) {

                        let msg = 'Terjadi kesalahan';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }

                        alert(msg);

                        $('#tbody-detail').html(`
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data</td>
                    </tr>
                `);

                        $('#no_so').val('').focus();
                    }
                });
            }



        });
    </script>
@endsection
