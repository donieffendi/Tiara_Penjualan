@extends('layouts.plain')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ $status == 'simpan' ? 'New' : 'Edit' }} Proses Stock Opname</h1>
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
                                        <label>Sub <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="sub"
                                            id="sub" value="{{ $header->sub ?? '' }}" placeholder="Sub kategori"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <input type="text" class="form-control form-control-sm" name="notes"
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
                                        <label>Sub</label>
                                        <input type="text" class="form-control form-control-sm" id="sub"
                                            value="001">
                                    </div>
                                </div>

                                <!-- Kdlaku -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Kdlaku</label>
                                        <select class="form-control form-control-sm" id="cbkdlaku">
                                            <option value="ALL">ALL</option>
                                            <option value="0">0</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- L/H Dari -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>L/H Dari</label>
                                        <input type="text" class="form-control form-control-sm" id="lph1"
                                            value="0,00">
                                    </div>
                                </div>

                                <!-- L/H S/D -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>L/H S/D</label>
                                        <input type="text" class="form-control form-control-sm" id="lph2"
                                            value="999,00">
                                    </div>
                                </div>

                                <!-- Tidak Ada Transaksi -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Tidak Ada Transaksi</label>
                                        <input type="number" class="form-control form-control-sm" id="tat"
                                            value="0">
                                    </div>
                                </div>

                                <!-- Pertahankan Data -->
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input" id="pertahankan">
                                            <label class="form-check-label">Pertahankan Data</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data R/L -->
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input" id="dataRL">
                                            <label class="form-check-label">Data R/L</label>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- ROW 2 -->
                            <div class="row">

                                <!-- Item From -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Item From</label>
                                        <input type="text" class="form-control form-control-sm" id="item1"
                                            placeholder="Item awal">
                                    </div>
                                </div>

                                <!-- Item To -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Item To</label>
                                        <input type="text" class="form-control form-control-sm" id="item2"
                                            value="ZZZZ" placeholder="Item akhir">
                                    </div>
                                </div>

                                <!-- Supplier -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Supplier</label>
                                        <input type="text" class="form-control form-control-sm" id="supp"
                                            placeholder="Kode supplier">
                                    </div>
                                </div>
                                <!-- Cek From -->
                                {{-- <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Cek From</label>
                                        <input type="number" class="form-control form-control-sm" id="cek1"
                                            placeholder="No awal">
                                    </div>
                                </div>

                                <!-- Cek To -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Cek To</label>
                                        <input type="number" class="form-control form-control-sm" id="cek2"
                                            placeholder="No akhir">
                                    </div>
                                </div> --}}

                                <!-- All In -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" id="btn-allin">
                                            <i class="fas fa-download"></i> All In
                                        </button>
                                    </div>
                                </div>

                            </div>


                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-success btn-sm" id="btn-cek-all">
                                        <i class="fas fa-check"></i> Cek All
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" id="btn-uncek-all">
                                        <i class="fas fa-times"></i> Uncek All
                                    </button>

                                    <button id="btn-hapus-saldo" type="button"  class="btn btn-danger">
                                        Hapus Positif
                                    </button>
                                    <button id="btn-hapus-nol" type="button"  class="btn btn-danger">
                                        Hapus Nol
                                    </button>
                                    <button id="btn-hapus-negatif" type="button"  class="btn btn-danger">
                                        Hapus Negatif
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table-bordered table-striped table-sm table" id="table-detail">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th width="5%" class="text-center">No</th>
                                            <th width="12%" class="text-center">Kode</th>
                                            <th width="25%" class="text-center">Nama Barang</th>
                                            <th width="10%" class="text-center">Ukuran</th>
                                            <th width="10%" class="text-center">Harga</th>
                                            <th width="10%" class="text-center">Stok</th>
                                            <th width="10%" class="text-center">Ket</th>
                                            <th width="8%" class="text-center">Cek</th>
                                            <th width="5%" class="text-center">
                                                <button type="button" class="btn btn-xs btn-danger" id="btn-clear-all"
                                                    title="Clear All">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-detail">
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
                                                <td colspan="9" class="text-center">Tidak ada data. Gunakan tombol "All
                                                    In" untuk memuat data barang.</td>
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
                let item1 = $('#item1').val().trim() || '';
                let item2 = $('#item2').val().trim() || 'ZZZZ';
                let supp = $('#supp').val().trim();

                // kolom baru:
                let kdlaku = $('#cbkdlaku').val();
                let lph1 = $('#lph1').val();
                let lph2 = $('#lph2').val();
                let tat = $('#tat').val();
                let pertahankan = $('#pertahankan').is(':checked') ? 1 : 0;
                let dataRL = $('#dataRL').is(':checked') ? 1 : 0;

                if (!sub && !supp) {
                    Swal.fire('Peringatan', 'Sub atau Supplier harus diisi', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Memuat Data...',
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('tprosesstockopname.browse') }}",
                    data: {
                        sub: sub,
                        item1: item1,
                        item2: item2,
                        supp: supp,
                        kdlaku: kdlaku,
                        lph1: lph1,
                        lph2: lph2,
                        tat: tat,
                        pertahankan: pertahankan,
                        dataRL: dataRL
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

            // Cek All
            $('#btn-cek-all').click(function() {
                let cek1 = parseInt($('#cek1').val()) || 0;
                let cek2 = parseInt($('#cek2').val()) || 0;

                if (cek1 > 0 && cek2 > 0) {
                    // Cek berdasarkan range
                    $('#tbody-detail tr').each(function(index) {
                        let rowNo = index + 1;
                        if (rowNo >= cek1 && rowNo <= cek2) {
                            $(this).find('.cek-item').prop('checked', true);
                        }
                    });
                } else {
                    // Cek semua
                    $('.cek-item').prop('checked', true);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Item berhasil dicek',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            $('#btn-hapus-saldo').click(function() {

                $('#tbody-detail tr').each(function() {

                    let saldo = parseFloat($(this).find('.saldo').text()) || 0;

                    if (saldo > 0) {
                        $(this).remove();
                    }
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Row dengan saldo > 0 berhasil dihapus',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

             $('#btn-hapus-nol').click(function() {

                $('#tbody-detail tr').each(function() {

                    let saldo = parseFloat($(this).find('.saldo').text()) || 0;

                    if (saldo = 0) {
                        $(this).remove();
                    }
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Row dengan saldo = 0 berhasil dihapus',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

             $('#btn-hapus-negatif').click(function() {

                $('#tbody-detail tr').each(function() {

                    let saldo = parseFloat($(this).find('.saldo').text()) || 0;

                    if (saldo < 0) {
                        $(this).remove();
                    }
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Row dengan saldo < 0 berhasil dihapus',
                    timer: 1500,
                    showConfirmButton: false
                });
            });


            // Uncek All
            $('#btn-uncek-all').click(function() {
                $('.cek-item').prop('checked', false);

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Semua item di-uncek',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

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
            $('#btn-save').click(function(e) {
                e.preventDefault();

                let tgl = $('input[name="tgl"]').val();
                let sub = $('input[name="sub"]').val().trim();

                if (!tgl) {
                    Swal.fire('Peringatan', 'Tanggal harus diisi', 'warning');
                    $('input[name="tgl"]').focus();
                    return;
                }

                if (!sub) {
                    Swal.fire('Peringatan', 'Sub harus diisi', 'warning');
                    $('input[name="sub"]').focus();
                    return;
                }

                let hasDetail = false;
                let hasCeked = false;

                $('#tbody-detail tr').each(function() {
                    if (!$(this).find('td').first().attr('colspan')) {
                        let kdBrg = $(this).find('input[name*="[kd_brg]"]').val();
                        if (kdBrg && kdBrg.trim() !== '') {
                            hasDetail = true;
                            if ($(this).find('.cek-item').is(':checked')) {
                                hasCeked = true;
                            }
                        }
                    }
                });

                if (!hasDetail) {
                    Swal.fire('Peringatan', 'Detail barang harus diisi', 'warning');
                    return;
                }

                if (!hasCeked) {
                    Swal.fire('Peringatan', 'Minimal satu item harus di-cek', 'warning');
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
                        // Use FormData to properly serialize the form
                        let formData = new FormData($('#form-stock-opname')[0]);

                        // Convert FormData to regular object for AJAX
                        let data = {};
                        formData.forEach(function(value, key) {
                            // Handle array notation properly
                            if (key.indexOf('[') > -1) {
                                // Parse array notation like detail[0][kd_brg]
                                let matches = key.match(/^(.+?)\[(\d+)\]\[(.+)\]$/);
                                if (matches) {
                                    let arrayName = matches[1];
                                    let index = matches[2];
                                    let fieldName = matches[3];

                                    if (!data[arrayName]) {
                                        data[arrayName] = [];
                                    }
                                    if (!data[arrayName][index]) {
                                        data[arrayName][index] = {};
                                    }
                                    data[arrayName][index][fieldName] = value;
                                } else {
                                    data[key] = value;
                                }
                            } else {
                                data[key] = value;
                            }
                        });

                        // Add checkbox values (unchecked checkboxes don't submit)
                        $('#tbody-detail tr').each(function(index) {
                            if (!$(this).find('td').first().attr('colspan')) {
                                if (!data.detail) data.detail = [];
                                if (!data.detail[index]) data.detail[index] = {};
                                data.detail[index].cek = $(this).find('.cek-item').is(
                                    ':checked') ? 1 : 0;
                            }
                        });

                        return $.ajax({
                            url: "{{ route('tprosesstockopname.store') }}",
                            type: 'POST',
                            data: data,
                            dataType: 'json'
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: result.value.message || 'Save Data Success',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = "{{ route('tprosesstockopname') }}";
                        });
                    }
                }).catch((error) => {
                    if (error && error.responseJSON) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.responseJSON.message ||
                                'Terjadi kesalahan saat menyimpan data',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
@endsection
