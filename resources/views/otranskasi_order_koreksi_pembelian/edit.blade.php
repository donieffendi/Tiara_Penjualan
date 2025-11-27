@extends('layouts.plain')

@section('content')
    <style>
        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .form-group.row {
            margin-bottom: 0.5rem !important;
        }

        .col-form-label {
            padding-top: calc(0.25rem + 1px);
            padding-bottom: calc(0.25rem + 1px);
            font-size: 0.875rem;
        }

        .text-right {
            text-align: right !important;
        }

        #detailTable {
            margin-bottom: 0;
            font-size: 0.875rem;
        }

        #detailTable th,
        #detailTable td {
            padding: 4px 8px;
            vertical-align: middle;
        }

        .entry-section {
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #dee2e6;
            margin-top: 10px;
        }

        .content-wrapper {
            padding: 10px !important;
        }

        .card {
            margin-bottom: 10px;
        }

        .card-body {
            padding: 15px;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .input-group-sm .btn {
            padding: 0 8px;
        }

        .table-responsive {
            max-height: 300px;
            overflow-y: auto;
        }

        #detailTable thead {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
        }
    </style>

    <div class="content-wrapper">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Order Koreksi Pembelian - {{ $status == 'simpan' ? 'New' : 'Edit' }}
                                </h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('torderkoreksipembelian.store') }}" method="POST" name="entri"
                                    id="entri">
                                    @csrf
                                    <input type="hidden" id="status" name="status" value="{{ $status }}">
                                    <input type="hidden" id="cbg" name="cbg" value="{{ $cbg }}">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label for="no_bukti" class="col-md-3 col-form-label">No Bukti</label>
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control form-control-sm"
                                                        id="no_bukti" name="no_bukti"
                                                       value="{{ $header->NO_BUKTI ?? '+' }}" readonly>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="tgl" class="col-md-3 col-form-label">Tanggal</label>
                                                <div class="col-md-6">
                                                    <input type="date" class="form-control form-control-sm"
                                                        id="tgl" name="tgl"
                                                        value="{{ $header && $header->TGL ? date('Y-m-d', strtotime($header->TGL)) : date('Y-m-d') }}"
                                                        required>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="notes" class="col-md-3 col-form-label">Notes</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control form-control-sm"
                                                        id="notes" name="notes" value="{{ $header->NOTES ?? '' }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="kodes" class="col-md-3 col-form-label">Supplier</label>
                                                <div class="col-md-6">
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" class="form-control form-control-sm"
                                                            id="kodes" name="kodes" value="{{ $header->KODES ?? '' }}"
                                                            required>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                            onclick="browseSupplier()">...</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-md-4 col-form-label">Pesan Ke Supplier (s/d)</label>

                                                <div class="col-md-3">
                                                    <input type="SUPP1" class="form-control form-control-sm"
                                                        id="SUPP1" name="SUPP1" value="{{ $header->SUPP1 ?? '' }}">
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="SUPP2" class="form-control form-control-sm"
                                                        id="SUPP2" name="SUPP2" value="{{ $header->SUPP2 ?? '' }}">
                                                </div>
                                            </div>


                                            <div class="form-group row">
                                                <label class="col-md-4 col-form-label"></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control form-control-sm"
                                                        id="NAMAS" name="NAMAS" value="{{ $header->NAMAS ?? '' }}"
                                                        readonly>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="lph1" class="col-md-4 col-form-label">Untuk LH (s/d)</label>
                                                <div class="col-md-3">
                                                    <input type="LPH1" class="form-control form-control-sm"
                                                        id="LPH1" name="LPH1" value="{{ $header->LPH1 ?? '' }}">
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="LPH2" class="form-control form-control-sm"
                                                        id="LPH2" name="LPH2" value="{{ $header->LPH2 ?? '' }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="hari" class="col-md-4 col-form-label">Untuk Kebutuhan
                                                    (hari)</label>
                                                <div class="col-md-3">
                                                    <input type="number" class="form-control form-control-sm text-right"
                                                        id="HARI" name="HARI" value="{{ $header->HARI ?? 0 }}"
                                                        step="0.01">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="sub" class="col-md-4 col-form-label">Sub</label>
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control form-control-sm"
                                                        id="SUB" name="SUB" value="{{ $header->SUB ?? '' }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-4 col-form-label">Sub1 s/d Sub2</label>
                                                <div class="col-md-3">
                                                    <input type="text" class="form-control form-control-sm"
                                                        id="SUB1" name="SUB1" value="{{ $header->SUB1 ?? '' }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="text" class="form-control form-control-sm"
                                                        id="SUB2" name="SUB2" value="{{ $header->SUB2 ?? '' }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="SPL" class="col-md-4 col-form-label">Pesan Ke</label>
                                                <div class="col-md-6">
                                                    <select class="form-control form-control-sm" id="SPL"
                                                        name="SPL">
                                                        <option value="">-- Pilih --</option>
                                                        <option value="L"
                                                            {{ isset($header) && $header->SP == 'L' ? 'selected' : '' }}>
                                                            L</option>
                                                        <option value="Z"
                                                            {{ isset($header) && $header->SP == 'Z' ? 'selected' : '' }}>
                                                            S</option>
                                                        <option value="D"
                                                            {{ isset($header) && $header->SP == 'D' ? 'selected' : '' }}>
                                                            D</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-primary btn-sm"
                                                        onclick="prosesSub()">Proses</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- <div class="row">
                                        <div class="col-md-12">
                                            <div class="entry-section">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label for="kd_brg" class="form-label mb-1">Kode</label>
                                                        <div class="input-group input-group-sm">
                                                            <input type="text" class="form-control form-control-sm"
                                                                id="kd_brg" name="kd_brg">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary"
                                                                onclick="browseBarang()">...</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label for="na_brg_entry" class="form-label mb-1">Nama
                                                            Barang</label>
                                                        <input type="text" class="form-control form-control-sm"
                                                            id="na_brg_entry" readonly>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <label for="qty_entry" class="form-label mb-1">Qty</label>
                                                        <input type="number"
                                                            class="form-control form-control-sm text-right" id="qty_entry"
                                                            step="0.01">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <label for="harga_entry" class="form-label mb-1">Harga</label>
                                                        <input type="number"
                                                            class="form-control form-control-sm text-right"
                                                            id="harga_entry" step="0.01">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label for="total_entry" class="form-label mb-1">Total</label>
                                                        <input type="number"
                                                            class="form-control form-control-sm text-right"
                                                            id="total_entry" readonly>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label for="notes_entry" class="form-label mb-1">Notes</label>
                                                        <input type="text" class="form-control form-control-sm"
                                                            id="notes_entry">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}

                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <div class="table-responsive">
                                                <table class="table-sm table-bordered table" id="detailTable">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 3%;">No</th>
                                                            <th style="width: 8%;">Supp</th>
                                                            <th style="width: 10%;">Kode</th>
                                                            <th style="width: 18%;">Nama Barang</th>
                                                            <th style="width: 8%;">Ukuran</th>
                                                            <th style="width: 8%;">Kemasan</th>
                                                            <th style="width: 7%;" class="text-right">Qty</th>
                                                            <th style="width: 7%;" class="text-right">Lph</th>
                                                            <th style="width: 7%;" class="text-right">Sa</th>
                                                            <th style="width: 7%;" class="text-right">Sp</th>
                                                            <th style="width: 8%;" class="text-right">Harga</th>
                                                            <th style="width: 10%;" class="text-right">Total</th>
                                                            <th style="width: 12%;">Notes</th>
                                                            <th style="width: 7%;" class="text-right">Psn</th>
                                                        </tr>
                                                    </thead>
                                             <tbody id="detailTableBody">
    @if ($status == 'simpan' && !empty($detail))
        @foreach ($detail as $index => $item)
            <tr>
                {{-- NOMOR --}}
                <td class="text-center">{{ $index + 1 }}</td>

                {{-- KODES --}}
                <td>
                    <input type="text"
                        class="form-control form-control-sm"
                        name="details[{{ $index }}][kodes]"
                        value="{{ $item->kodes }}">
                </td>

                {{-- KD_BRG --}}
                <td>
                    <input type="text"
                        class="form-control form-control-sm"
                        name="details[{{ $index }}][kd_brg]"
                        value="{{ $item->KD_BRG }}">
                </td>

                {{-- NA_BRG --}}
                <td>
                    <input type="text"
                        class="form-control form-control-sm"
                        name="details[{{ $index }}][na_brg]"
                        value="{{ $item->NA_BRG }}">
                </td>

                {{-- KET UK --}}
                <td>
                    <input type="text"
                        class="form-control form-control-sm"
                        name="details[{{ $index }}][ket_uk]"
                        value="{{ $item->ket_uk ?? '' }}">
                </td>

                {{-- KET KEM --}}
                <td>
                    <input type="text"
                        class="form-control form-control-sm"
                        name="details[{{ $index }}][ket_kem]"
                        value="{{ $item->ket_kem ?? '' }}">
                </td>

                {{-- QTY --}}
                <td>
                    <input type="text" 
                        class="form-control form-control-sm text-right w-100"
                        name="details[{{ $index }}][qty]"
                        value="{{ number_format($item->qty, 2, ',', '.') }}">
                </td>

                {{-- LPH --}}
                <td>
                    <input type="text" 
                        class="form-control form-control-sm text-right w-100"
                        name="details[{{ $index }}][lph]"
                        value="{{ number_format($item->lph, 2, ',', '.') }}">
                </td>

                {{-- QTYBRG --}}
                <td>
                    <input type="text"
                        class="form-control form-control-sm text-right w-100"
                        name="details[{{ $index }}][qtybrg]"
                        value="{{ number_format($item->qtybrg, 2, ',', '.') }}">
                </td>

                {{-- QTYPO --}}
                <td>
                    <input type="text"
                        class="form-control form-control-sm text-right w-100"
                        name="details[{{ $index }}][qtypo]"
                        value="{{ number_format($item->qtypo, 2, ',', '.') }}">
                </td>

                {{-- HARGA --}}
                <td>
                    <input type="text"
                        class="form-control form-control-sm text-right w-100"
                        name="details[{{ $index }}][harga]"
                        value="{{ number_format($item->harga, 2, ',', '.') }}">
                </td>

                {{-- TOTAL --}}
                <td>
                    <input type="text"
                        class="form-control form-control-sm text-right w-100"
                        name="details[{{ $index }}][total]"
                        value="{{ number_format($item->TOTAL, 2, ',', '.') }}">
                </td>

                {{-- NOTES --}}
                <td>
                    <input type="text"
                        class="form-control form-control-sm"
                        name="details[{{ $index }}][notes]"
                        value="{{ $item->notes ?? '' }}">
                </td>

                {{-- RIIL --}}
                <td>
                    <input type="text"
                        class="form-control form-control-sm text-right w-100"
                        name="details[{{ $index }}][riil]"
                        value="{{ number_format($item->riil ?? 0, 2, ',', '.') }}">
                </td>

                {{-- NO_ID --}}
                <input type="hidden"
                    name="details[{{ $index }}][NO_ID]"
                    value="{{ $item->NO_ID ?? 0 }}">

            </tr>
        @endforeach
    @endif
</tbody>

                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <button type="button" id="SAVEX" onclick="simpan()"
                                                class="btn btn-success btn-sm">
                                                <i class="fas fa-save"></i> Save
                                            </button>
                                            <button type="button" id="CLOSEX" onclick="closeForm()"
                                                class="btn btn-secondary btn-sm">
                                                <i class="fas fa-times"></i> Close
                                            </button>
                                            @if ($status == 'edit')
                                                <button type="button" onclick="printData('{{ $no_bukti }}')"
                                                    class="btn btn-info btn-sm">
                                                    <i class="fas fa-print"></i> Print
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="browseSupplierModal" tabindex="-1" aria-labelledby="browseSupplierModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="browseSupplierModalLabel">Browse Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-sm" id="searchSupplier"
                            placeholder="Cari supplier...">
                    </div>
                    <div class="table-responsive">
                        <table class="table-bordered table-sm table" id="supplierTable">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Supplier</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="supplierTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="browseBarangModal" tabindex="-1" aria-labelledby="browseBarangModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="browseBarangModalLabel">Browse Barang</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-sm" id="searchBarang"
                            placeholder="Cari barang...">
                    </div>
                    <div class="table-responsive">
                        <table class="table-bordered table-sm table" id="barangTable">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Barang</th>
                                    <th>Ukuran</th>
                                    <th>Kemasan</th>
                                    <th>Harga</th>
                                    <th>Saldo</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="barangTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        var detailRowIndex = {{ $status == 'edit' && !empty($detail) ? count($detail) : 0 }};
        var TEMP_BARANG = {};

        $(document).ready(function() {
            @if ($status == 'simpan')
                $('#tgl').focus();
            @else
                $('#kd_brg').focus();
            @endif

            $('#searchSupplier').on('keyup', function() {
                var query = $(this).val();
                if (query.length > 2) {
                    searchSupplier(query);
                } else if (query.length === 0) {
                    searchSupplier('');
                }
            });

            $('#searchBarang').on('keyup', function() {
                var query = $(this).val();
                if (query.length > 2) {
                    searchBarang(query);
                }
            });

            $('#qty_entry, #harga_entry').on('input', function() {
                calculateTotal();
            });

            $('body').on('keydown', function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    handleEnterKey(e.target);
                    return false;
                }
            });
        });

        function handleEnterKey(element) {
            var $element = $(element);
            var id = $element.attr('id');

            switch (id) {
                case 'tgl':
                    $('#kodes').focus().select();
                    break;
                case 'kodes':
                    handleKodesEnter($element.val().trim());
                    break;
                case 'notes':
                    $('#exp').focus().select();
                    break;
                case 'kd_brg':
                    handleKdBrgEnter($element.val().trim());
                    break;
                case 'qty_entry':
                    $('#harga_entry').focus().select();
                    break;
                case 'harga_entry':
                    $('#notes_entry').focus().select();
                    break;
                case 'notes_entry':
                    handleNotesEntryEnter();
                    break;
                default:
                    var form = $element.parents('form:eq(0)');
                    var focusable = form.find('input,select,textarea').filter(':visible:not([readonly])');
                    var next = focusable.eq(focusable.index(element) + 1);
                    if (next.length) {
                        next.focus().select();
                    }
                    break;
            }
        }

        function handleKodesEnter(kodes) {
            if (kodes) {
                $.ajax({
                    url: '{{ route('torderkoreksipembelian.detail') }}',
                    type: 'GET',
                    data: {
                        kodes: kodes,
                        type: 'supplier'
                    },
                    success: function(response) {
                        if (response.exists && response.data) {
                            $('#kodes').val(response.data.kodes);
                            $('#namas').val(response.data.namas);
                            $('#notes').focus().select();
                        } else {
                            browseSupplier();
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal mengambil data supplier'
                        });
                    }
                });
            }
        }

        function handleKdBrgEnter(kd_brg) {
            if (kd_brg) {
                $.ajax({
                    url: '{{ route('torderkoreksipembelian.detail') }}',
                    type: 'GET',
                    data: {
                        kd_brg: kd_brg,
                        type: 'barang'
                    },
                    success: function(response) {
                        if (response.exists && response.data) {
                            TEMP_BARANG = response.data;
                            $('#na_brg_entry').val(response.data.na_brg);
                            $('#harga_entry').val(response.data.hb || 0);
                            $('#qty_entry').focus().select();
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning',
                                text: 'Barang tidak ditemukan'
                            });
                            clearEntryForm();
                            $('#kd_brg').focus().select();
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal mengambil data barang'
                        });
                    }
                });
            }
        }

        function handleNotesEntryEnter() {
            var kd_brg = $('#kd_brg').val().trim();
            var qty = parseFloat($('#qty_entry').val()) || 0;
            var harga = parseFloat($('#harga_entry').val()) || 0;
            var notes = $('#notes_entry').val().trim();

            if (!kd_brg || !TEMP_BARANG.kd_brg) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Entri barang dulu!'
                });
                $('#kd_brg').focus().select();
                return;
            }

            if (qty <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Qty harus lebih dari 0!'
                });
                $('#qty_entry').focus().select();
                return;
            }

            var kodes = $('#kodes').val();
            var total = qty * harga;

            addToDetailTable(kodes, TEMP_BARANG, qty, harga, total, notes);
            clearEntryForm();
            $('#kd_brg').focus();
        }

        function calculateTotal() {
            var qty = parseFloat($('#qty_entry').val()) || 0;
            var harga = parseFloat($('#harga_entry').val()) || 0;
            $('#total_entry').val((qty * harga).toFixed(2));
        }

        function addToDetailTable(kodes, barang, qty, harga, total, notes) {
            var lph = parseFloat(barang.lph) || 0;
            var qtybrg = parseFloat(barang.saldo) || 0;
            var qtypo = parseFloat(barang.totalpo) || 0;
            var kemasan = parseFloat(barang.kemasan) || 0;
            var riil = qty;

            var newRow = `
				<tr>
					<td class="text-center">${detailRowIndex + 1}</td>
					<td>${kodes}</td>
					<td>${barang.kd_brg}</td>
					<td>${barang.na_brg}</td>
					<td>${barang.ket_uk || ''}</td>
					<td>${barang.ket_kem || ''}</td>
					<td class="text-right">${formatNumber(qty)}</td>
					<td class="text-right">${formatNumber(lph)}</td>
					<td class="text-right">${formatNumber(qtybrg)}</td>
					<td class="text-right">${formatNumber(qtypo)}</td>
					<td class="text-right">${formatNumber(harga)}</td>
					<td class="text-right">${formatNumber(total)}</td>
					<td>${notes}</td>
					<td class="text-right">${formatNumber(riil)}</td>
					<input type="hidden" name="details[${detailRowIndex}][no_id]" value="0">
					<input type="hidden" name="details[${detailRowIndex}][rec]" value="${detailRowIndex + 1}">
					<input type="hidden" name="details[${detailRowIndex}][kodes]" value="${kodes}">
					<input type="hidden" name="details[${detailRowIndex}][kd_brg]" value="${barang.kd_brg}">
					<input type="hidden" name="details[${detailRowIndex}][na_brg]" value="${barang.na_brg}">
					<input type="hidden" name="details[${detailRowIndex}][ket_uk]" value="${barang.ket_uk || ''}">
					<input type="hidden" name="details[${detailRowIndex}][ket_kem]" value="${barang.ket_kem || ''}">
					<input type="hidden" name="details[${detailRowIndex}][kemasan]" value="${kemasan}">
					<input type="hidden" name="details[${detailRowIndex}][qty]" value="${qty}">
					<input type="hidden" name="details[${detailRowIndex}][lph]" value="${lph}">
					<input type="hidden" name="details[${detailRowIndex}][qtybrg]" value="${qtybrg}">
					<input type="hidden" name="details[${detailRowIndex}][qtypo]" value="${qtypo}">
					<input type="hidden" name="details[${detailRowIndex}][harga]" value="${harga}">
					<input type="hidden" name="details[${detailRowIndex}][total]" value="${total}">
					<input type="hidden" name="details[${detailRowIndex}][notes]" value="${notes}">
					<input type="hidden" name="details[${detailRowIndex}][riil]" value="${riil}">
				</tr>`;

            $('#detailTableBody').append(newRow);
            detailRowIndex++;
        }

        function clearEntryForm() {
            $('#kd_brg').val('');
            $('#na_brg_entry').val('');
            $('#qty_entry').val('');
            $('#harga_entry').val('');
            $('#total_entry').val('');
            $('#notes_entry').val('');
            TEMP_BARANG = {};
        }

        function browseSupplier() {
            $('#browseSupplierModal').modal('show');
            searchSupplier('');
        }

        function searchSupplier(query) {
            $.ajax({
                url: '{{ route('torderkoreksipembelian.browse') }}',
                type: 'GET',
                data: {
                    q: query,
                    type: 'supplier'
                },
                success: function(response) {
                    var tbody = $('#supplierTableBody');
                    tbody.empty();
                    if (response.length > 0) {
                        response.forEach(function(item) {
                            var row = '<tr>';
                            row += '<td>' + item.kodes + '</td>';
                            row += '<td>' + item.namas + '</td>';
                            row +=
                                '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectSupplier(\'' +
                                item.kodes +
                                '\', \'' + escapeHtml(item.namas) + '\')">Select</button></td>';
                            row += '</tr>';
                            tbody.append(row);
                        });
                    } else {
                        tbody.append('<tr><td colspan="3" class="text-center">No data found</td></tr>');
                    }
                }
            });
        }

        function selectSupplier(kodes, namas) {
            $('#kodes').val(kodes);
            $('#namas').val(namas);
            $('#browseSupplierModal').modal('hide');
            $('#notes').focus();
        }

        function browseBarang() {
            $('#browseBarangModal').modal('show');
        }

        function searchBarang(query) {
            $.ajax({
                url: '{{ route('torderkoreksipembelian.browse') }}',
                type: 'GET',
                data: {
                    q: query,
                    type: 'barang'
                },
                success: function(response) {
                    var tbody = $('#barangTableBody');
                    tbody.empty();
                    if (response.length > 0) {
                        response.forEach(function(item) {
                            var row = '<tr>';
                            row += '<td>' + item.kd_brg + '</td>';
                            row += '<td>' + item.na_brg + '</td>';
                            row += '<td>' + (item.ket_uk || '') + '</td>';
                            row += '<td>' + (item.ket_kem || '') + '</td>';
                            row += '<td class="text-right">' + formatNumber(item.hb || 0) + '</td>';
                            row += '<td class="text-right">' + formatNumber(item.saldo || 0) + '</td>';
                            row +=
                                '<td><button type="button" class="btn btn-primary btn-sm" onclick="selectBarang(' +
                                JSON.stringify(
                                    item).replace(/"/g, '&quot;') + ')">Select</button></td>';
                            row += '</tr>';
                            tbody.append(row);
                        });
                    } else {
                        tbody.append('<tr><td colspan="7" class="text-center">No data found</td></tr>');
                    }
                }
            });
        }

        function selectBarang(barang) {
            TEMP_BARANG = barang;
            $('#kd_brg').val(barang.kd_brg);
            $('#na_brg_entry').val(barang.na_brg);
            $('#harga_entry').val(barang.hb || 0);
            $('#browseBarangModal').modal('hide');
            $('#qty_entry').focus().select();
        }

        function prosesSub() {
            let data = {
                SUPP1: $("#SUPP1").val(),
                SUPP2: $("#SUPP2").val(),
                LPH1: $("#LPH1").val(),
                LPH2: $("#LPH2").val(),
                HARI: $("#HARI").val(),
                SUB1: $("#SUB1").val(),
                SUB2: $("#SUB2").val(),
                SPL: $("#SPL").val(),
                _token: "{{ csrf_token() }}"
            };

            $.ajax({
                url: "{{ route('proses.sub') }}",
                type: "POST",
                data: data,
               success: function(response) {
    console.log(response);

    if (response.status === 'OK') {

        // Tampilkan Swal
        Swal.fire("Sukses", "Proses selesai!", "success");

        // Bersihkan table lama
        $("#detailTableBody").empty();

        let rows = response.data;
        let nomor = 1;

        rows.forEach(function(item, index) {

            let row = `
                <tr>
                    <td class="text-center">${nomor++}</td>
                    <td>${item.kodes}</td>
                    <td>${item.kd_brg}</td>
                    <td>${item.na_brg}</td>
                    <td>${item.ket_uk ?? ''}</td>
                    <td>${item.kemasan ?? ''}</td>
                    <td class="text-right">${parseFloat(item.qty).toLocaleString('id-ID')}</td>
                    <td class="text-right">${parseFloat(item.lph).toLocaleString('id-ID')}</td>
                    <td class="text-right">${parseFloat(item.qtybrg).toLocaleString('id-ID')}</td>
                    <td class="text-right">${parseFloat(item.qtypo).toLocaleString('id-ID')}</td>
                    <td class="text-right">${parseFloat(item.harga).toLocaleString('id-ID')}</td>
                    <td class="text-right">${parseFloat(item.total).toLocaleString('id-ID')}</td>
                    <td>${item.notes ?? ''}</td>
                    <td class="text-right">${item.psn ?? ''}</td>

                    <!-- hidden input -->
                    <input type="hidden" name="details[${index}][kodes]" value="${item.kodes}">
                    <input type="hidden" name="details[${index}][kd_brg]" value="${item.kd_brg}">
                    <input type="hidden" name="details[${index}][na_brg]" value="${item.na_brg}">
                    <input type="hidden" name="details[${index}][ket_uk]" value="${item.ket_uk}">
                    <input type="hidden" name="details[${index}][ket_kem]" value="${item.kemasan}">
                    <input type="hidden" name="details[${index}][kemasan]" value="${item.nkemasan}">
                    <input type="hidden" name="details[${index}][qty]" value="${item.qty}">
                    <input type="hidden" name="details[${index}][lph]" value="${item.lph}">
                    <input type="hidden" name="details[${index}][qtybrg]" value="${item.qtybrg}">
                    <input type="hidden" name="details[${index}][qtypo]" value="${item.qtypo}">
                    <input type="hidden" name="details[${index}][harga]" value="${item.harga}">
                    <input type="hidden" name="details[${index}][total]" value="${item.total}">
                    <input type="hidden" name="details[${index}][notes]" value="${item.notes}">
                    <input type="hidden" name="details[${index}][riil]" value="${item.psn}">
                </tr>
            `;

            $("#detailTableBody").append(row);
        });

    } else {
        Swal.fire("Error", response.message, "error");
    }
},
                error: function(xhr) {
                    Swal.fire("Error", "Terjadi kesalahan server", "error");
                }
            });
        }

        function simpan() {
            var periode = '{{ is_array($periode) ? $periode['periode'] ?? ($periode[0] ?? date('m.Y')) : $periode }}';
            var tgl = $('#tgl').val();
            var tglDate = new Date(tgl);
            var month = String(tglDate.getMonth() + 1).padStart(2, '0');
            var year = tglDate.getFullYear();
            var periodeMonth = periode.substr(0, 2);
            var periodeYear = periode.substr(-4);
			let dataForm = $('#entri').serialize();

            if (month !== periodeMonth) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Month is not the same as Periode.'
                });
                return;
            }

            if (year != periodeYear) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Year is not the same as Periode.'
                });
                return;
            }

            if ($('#kodes').val().trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Supplier tidak boleh kosong.'
                });
                $('#kodes').focus();
                return;
            }

            var detailCount = $('#detailTableBody tr').length;
            if (detailCount === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Detail barang tidak boleh kosong.'
                });
                return;
            }

            Swal.fire({
                title: 'Menyimpan...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            $.ajax({
                url: '{{ route('torderkoreksipembelian.store') }}',
                type: 'POST',
				data: dataForm,

                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Save Data Success',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '{{ route('torderkoreksipembelian') }}';
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    var errorMessage = 'Terjadi kesalahan saat menyimpan data';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        function closeForm() {
            window.location.href = '{{ route('torderkoreksipembelian') }}';
        }

        function printData(no_bukti) {
            $.ajax({
                url: "{{ route('torderkoreksipembelian.print') }}",
                type: 'POST',
                data: {
                    no_bukti: no_bukti,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.data && response.data.length > 0) {
                        var printWindow = window.open('', '_blank');
                        var printContent = generatePrintContent(response.data);
                        printWindow.document.write(printContent);
                        printWindow.document.close();
                        printWindow.print();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning',
                            text: 'Tidak ada data untuk dicetak'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mencetak data'
                    });
                }
            });
        }

        function generatePrintContent(data) {
            var content =
                `<!DOCTYPE html><html><head><title>Order Koreksi Pembelian</title><style>body{font-family:Arial,sans-serif;font-size:12px}table{width:100%;border-collapse:collapse;margin-top:10px}th,td{border:1px solid #000;padding:5px;text-align:left}th{background-color:#f0f0f0;font-weight:bold}.text-center{text-align:center}.text-right{text-align:right}.header{text-align:center;margin-bottom:20px}.info{margin-bottom:10px}</style></head><body><div class="header"><h2>ORDER KOREKSI PEMBELIAN</h2></div><div class="info"><table style="border:none"><tr style="border:none"><td style="border:none;width:100px"><strong>No Bukti</strong></td><td style="border:none">: ${data[0].NO_BUKTI}</td><td style="border:none;width:100px"><strong>Periode</strong></td><td style="border:none">: ${data[0].per}</td></tr><tr style="border:none"><td style="border:none"><strong>Supplier</strong></td><td style="border:none">: ${data[0].namas}</td><td style="border:none"><strong>Hari</strong></td><td style="border:none">: ${data[0].hari}</td></tr><tr style="border:none"><td style="border:none"><strong>Notes</strong></td><td colspan="3" style="border:none">: ${data[0].notes||''}</td></tr></table></div><table><thead><tr><th class="text-center">No</th><th>Kode</th><th>Nama Barang</th><th>Ukuran</th><th>Kemasan</th><th class="text-right">Qty</th><th class="text-right">MO</th><th class="text-right">Harga</th><th class="text-right">Total</th></tr></thead><tbody>`;
            var totalQty = 0,
                totalAmount = 0;
            data.forEach((item, index) => {
                totalQty += parseFloat(item.qty) || 0;
                totalAmount += parseFloat(item.total) || 0;
                content +=
                    `<tr><td class="text-center">${index+1}</td><td>${item.KD_BRG}</td><td>${item.NA_BRG}</td><td>${item.ket_uk||''}</td><td>${item.ket_kem}</td><td class="text-right">${formatNumber(parseFloat(item.qty))}</td><td class="text-right">${parseFloat(item.mo).toLocaleString('id-ID',{minimumFractionDigits:0})}</td><td class="text-right">${formatNumber(parseFloat(item.harga))}</td><td class="text-right">${formatNumber(parseFloat(item.total))}</td></tr>`;
            });
            content +=
                `<tr><td colspan="5" class="text-center"><strong>TOTAL</strong></td><td class="text-right"><strong>${formatNumber(totalQty)}</strong></td><td colspan="2"></td><td class="text-right"><strong>${formatNumber(totalAmount)}</strong></td></tr></tbody></table></body></html>`;
            return content;
        }

        function formatNumber(num) {
            return parseFloat(num).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function escapeHtml(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }
    </script>
@endsection
