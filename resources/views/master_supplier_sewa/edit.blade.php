@extends('layouts.plain')
@section('styles')
    <link rel="stylesheet" href="{{ url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="{{ url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
@endsection

<style>
    .card {}

    .form-control:focus {
        background-color: #E0FFFF !important;
    }
</style>

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Data Supplier Sewa</h1>
                    </div>

                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <form
                                    action="{{ $tipx == 'new' ? url('/sup-sewa/store/') : url('/sup-sewa/update/' . $header->NO_ID) }}"
                                    method="POST" name ="entri" id="entri">
                                    @csrf
                                    <div class="tab-content mt-3">
                                        <div class="tab-pane active">
                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="KODES" class="form-label">Kode</label>
                                                </div>

                                                <input type="text" class="form-control NO_ID" id="NO_ID"
                                                    name="NO_ID" placeholder="Masukkan NO_ID"
                                                    value="{{ $header->NO_ID ?? '' }}" hidden readonly>

                                                <input name="tipx" class="form-control flagz" id="tipx"
                                                    value="{{ $tipx }}" hidden>


                                                <div class="col-md-2">
                                                    <input type="text" class="form-control KODES" id="KODES"
                                                        placeholder="Masukkan kode" value="{{ $header->KODES }}" readonly>
                                                </div>
                                                <div class="col-md-1">
                                                    <label for="KTP" class="form-label">No.KTP</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control KTP" id="KTP"
                                                        name="KTP" placeholder="Masukkan nama"
                                                        value="{{ $header->KTP }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="NAMAS" class="form-label">Nama</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control NAMAS" id="NAMAS"
                                                        name="NAMAS" placeholder="Masukkan nama"
                                                        value="{{ $header->NAMAS }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="KD_DIST" class="form-label">Distributor</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control KD_DIST" id="KD_DIST"
                                                        name="KD_DIST" placeholder="Masukkan nama"
                                                        value="{{ $header->KD_DIST }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control NAMA_DIST" id="NAMA_DIST"
                                                        name="NAMA_DIST" placeholder="Masukkan nama"
                                                        value="{{ $header->NAMA_DIST }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="PRODUK" class="form-label">Jenis Produk</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control PRODUK" id="PRODUK"
                                                        name="PRODUK" placeholder="Masukkan nama"
                                                        value="{{ $header->PRODUK }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="AL_PRSH" class="form-label">Alamat</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control AL_PRSH" id="AL_PRSH"
                                                        name="AL_PRSH" placeholder="Masukkan nama"
                                                        value="{{ $header->AL_PRSH }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="AL_PRSH" class="form-label"></label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control AL_PRSH2" id="AL_PRSH2"
                                                        name="AL_PRSH2" placeholder="Masukkan nama"
                                                        value="{{ $header->AL_PRSH2 }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="KOTA" class="form-label">Kota</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control KOTA" id="KOTA"
                                                        name="KOTA" placeholder="Masukkan nama"
                                                        value="{{ $header->KOTA }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="NO_TELP" class="form-label">No.Telp</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control NO_TELP" id="NO_TELP"
                                                        name="NO_TELP" placeholder="Masukkan no telp"
                                                        value="{{ $header->NO_TELP }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="S_PJK" class="form-label">Status Pajak</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <select class="form-control S_PJK" id="S_PJK" name="S_PJK">
                                                        <option value="">-- Pilih Status Pajak --</option>
                                                        <option value="P0"
                                                            {{ $header->S_PJK == 'P0' ? 'selected' : '' }}>P0
                                                        </option>
                                                        <option value="P1"
                                                            {{ $header->S_PJK == 'P1' ? 'selected' : '' }}>P1
                                                        </option>
                                                        <option value="P2"
                                                            {{ $header->S_PJK == 'P2' ? 'selected' : '' }}>P2
                                                        </option>
                                                        <option value="P2"
                                                            {{ $header->S_PJK == 'P3' ? 'selected' : '' }}>P3
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="NPWP" class="form-label">NPWP</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control NPWP" id="NPWP"
                                                        name="NPWP" placeholder="" value="{{ $header->NPWP }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="CARA_BYR" class="form-label">Sistem Pembayaran</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <select class="form-control CARA_BYR" id="CARA_BYR" name="CARA_BYR">
                                                        <option value="">-- Pilih Sistem Pembayaran --</option>
                                                        <option value="TUNAI" {{ $header->CARA_BYR == 'TUNAI' ? 'selected' : '' }}>Tunai</option>
                                                        <option value="TRF" {{ $header->CARA_BYR == 'TRF' ? 'selected' : '' }}>Transfer</option>
                                                        <option value="POTONGTAGIHAN" {{ $header->CARA_BYR == 'POTONGTAGIHAN' ? 'selected' : '' }}>Potong Tagihan</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="CARA_BYR" class="form-label"></label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control CARA_BYR2" id="CARA_BYR2"
                                                        name="CARA_BYR2" placeholder=""
                                                        value="{{ $header->CARA_BYR2 }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="KET" class="form-label">Ket. Bayar</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control KET" id="KET"
                                                        name="KET" placeholder="" value="{{ $header->KET }}">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="EMAIL" class="form-label">Email</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control EMAIL" id="EMAIL"
                                                        name="EMAIL" placeholder="" value="{{ $header->EMAIL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 col-md-12 form-group row">
                                            <div class="col-md-4">
                                                <button type="button" hidden id='TOPX'
                                                    onclick="location.href='{{ url('/sup-sewa/edit/?idx=' . $idx . '&tipx=top') }}'"
                                                    class="btn btn-outline-primary">Top</button>
                                                <button type="button" hidden id='PREVX'
                                                    onclick="location.href='{{ url('/sup-sewa/edit/?idx=' . $header->NO_ID . '&tipx=prev&kodex=' . $header->ACNO) }}'"
                                                    class="btn btn-outline-primary">Prev</button>
                                                <button type="button" hidden id='NEXTX'
                                                    onclick="location.href='{{ url('/sup-sewa/edit/?idx=' . $header->NO_ID . '&tipx=next&kodex=' . $header->ACNO) }}'"
                                                    class="btn btn-outline-primary">Next</button>
                                                <button type="button" hidden id='BOTTOMX'
                                                    onclick="location.href='{{ url('/sup-sewa/edit/?idx=' . $idx . '&tipx=bottom') }}'"
                                                    class="btn btn-outline-primary">Bottom</button>
                                            </div>
                                            <div class="col-md-5">
                                                <button type="button" hidden id='NEWX'
                                                    onclick="location.href='{{ url('/sup-sewa/edit/?idx=0&tipx=new') }}'"
                                                    class="btn btn-warning">New</button>
                                                <button type="button" hidden id='EDITX' onclick='hidup()'
                                                    class="btn btn-secondary">Edit</button>
                                                <button type="button" hidden id='UNDOX'
                                                    onclick="location.href='{{ url('/sup-sewa/edit/?idx=' . $idx . '&tipx=undo') }}'"
                                                    class="btn btn-info">Undo</button>
                                                <button type="button" id='SAVEX' onclick='simpan()'
                                                    class="btn btn-success" class="fa fa-save"></i>Save</button>

                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" hidden id='HAPUSX' onclick="hapusTrans()"
                                                    class="btn btn-outline-danger">Hapus</button>
                                                <button type="button" id='CLOSEX'
                                                    onclick="location.href='{{ url('/sup-sewa') }}'"
                                                    class="btn btn-outline-secondary">Close</button>


                                            </div>
                                        </div>


                                </form>
                            </div>
                        </div>
                        <!-- /.card -->
                    </div>
                </div>
                <!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    @endsection

    @section('footer-scripts')
        <script>
            var target;
            var idrow = 1;

            $(document).ready(function() {

                $tipx = $('#tipx').val();

                $('body').on('keydown', 'input, select', function(e) {
                    if (e.key === "Enter") {
                        var self = $(this),
                            form = self.parents('form:eq(0)'),
                            focusable, next;
                        focusable = form.find('input,select,textarea').filter(':visible');
                        next = focusable.eq(focusable.index(this) + 1);
                        console.log(next);
                        if (next.length) {
                            next.focus().select();
                        } else {
                            document.getElementById("KODE").focus();
                        }
                        return false;
                    }
                });

                if ($tipx == 'new') {
                    baru();
                }

                if ($tipx != 'new') {
                    ganti();
                }

            });


            function baru() {

                kosong();
                hidup();

            }

            function ganti() {

                // mati();
                hidup();

            }


            function batal() {

                mati();

            }


            function hidup() {

                $("#TOPX").attr("disabled", true);
                $("#PREVX").attr("disabled", true);
                $("#NEXTX").attr("disabled", true);
                $("#BOTTOMX").attr("disabled", true);

                $("#NEWX").attr("disabled", true);
                $("#EDITX").attr("disabled", true);
                $("#UNDOX").attr("disabled", false);
                $("#SAVEX").attr("disabled", false);

                $("#HAPUSX").attr("disabled", true);
                $("#CLOSEX").attr("disabled", false);


                $tipx = $('#tipx').val();

                if ($tipx == 'new') {

                    $("#KODES").attr("readonly", true);

                } else {
                    $("#KODES").attr("readonly", true);

                }

                $("#NAMA").attr("readonly", false);
                $("#TGL").attr("readonly", false);
                $("#TGL_SLS").attr("readonly", false);
            }


            function mati() {

                $("#TOPX").attr("disabled", false);
                $("#PREVX").attr("disabled", false);
                $("#NEXTX").attr("disabled", false);
                $("#BOTTOMX").attr("disabled", false);

                $("#NEWX").attr("disabled", false);
                $("#EDITX").attr("disabled", false);
                $("#UNDOX").attr("disabled", true);
                $("#SAVEX").attr("disabled", true);
                $("#HAPUSX").attr("disabled", false);
                $("#CLOSEX").attr("disabled", false);

                $("#KODE").attr("readonly", true);
                $("#NAMA").attr("readonly", true);
                $("#TGL").attr("readonly", true);
                $("#TGL_SLS").attr("readonly", true);
            }


            function kosong() {

                $('#KODES').val("");
                $('#NAMA').val("");
                $('#TGL').val("");
                $('#TGL_SLS').val("");
            }

            function hapusTrans() {
                let text = "Hapus Master " + $('#KODES').val() + "?";
                if (confirm(text) == true) {
                    window.location = "{{ url('/sup-sewa/delete/' . $header->NO_ID) }}'";
                    //return true;
                }
                return false;
            }

            function CariBukti() {

                var cari = $("#CARI").val();
                var loc = "{{ url('/sup-sewa/edit/') }}" + '?idx={{ $header->NO_ID }}&tipx=search&kodex=' +
                    encodeURIComponent(
                        cari);
                window.location = loc;

            }

            function simpan() {
                document.getElementById("entri").submit()
            }
        </script>
    @endsection
