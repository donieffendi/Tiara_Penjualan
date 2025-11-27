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
                        <h1 class="m-0">Data Daftar Supplier</h1>
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
                                    action="{{ $tipx == 'new' ? url('/sup/store/') : url('/sup/update/' . $header->NO_ID) }}"
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
                                                    <label for="STAND" class="form-label">Stand</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control STAND" id="STAND"
                                                        name="STAND" placeholder="Masukkan nama"
                                                        value="{{ $header->STAND }}">
                                                </div>
												<div class="col-md-1">
                                                    <label for="MARGIN" class="form-label">Margin</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control MARGIN" id="MARGIN"
                                                        name="MARGIN" placeholder="Masukkan nama"
                                                        value="{{ $header->MARGIN }}">
                                                </div>
                                            </div>

											<div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="AN_BP" class="form-label">Atas Nama</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control AN_BP" id="AN_BP"
                                                        name="AN_BP" placeholder="Masukkan nama"
                                                        value="{{ $header->AN_BP }}">
                                                </div>
                                            </div>

											<div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="NO_REK" class="form-label">No. Rekening</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control NO_REK" id="NO_REK"
                                                        name="NO_REK" placeholder="Masukkan nama"
                                                        value="{{ $header->NO_REK }}">
                                                </div>
                                            </div>

											<div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="NAMA_BP" class="form-label">Nama Bank</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control NAMA_BP" id="NAMA_BP"
                                                        name="NAMA_BP" placeholder="Masukkan nama"
                                                        value="{{ $header->NAMA_BP }}">
                                                </div>
                                            </div>

											<div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="KOTA_BP" class="form-label">Kota Bank</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control KOTA_BP" id="KOTA_BP"
                                                        name="KOTA_BP" placeholder="Masukkan nama"
                                                        value="{{ $header->KOTA_BP }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 col-md-12 form-group row">
                                            <div class="col-md-4">
                                                <button type="button" hidden id='TOPX'
                                                    onclick="location.href='{{ url('/sup/edit/?idx=' . $idx . '&tipx=top') }}'"
                                                    class="btn btn-outline-primary">Top</button>
                                                <button type="button" hidden id='PREVX'
                                                    onclick="location.href='{{ url('/sup/edit/?idx=' . $header->NO_ID . '&tipx=prev&kodex=' . $header->ACNO) }}'"
                                                    class="btn btn-outline-primary">Prev</button>
                                                <button type="button" hidden id='NEXTX'
                                                    onclick="location.href='{{ url('/sup/edit/?idx=' . $header->NO_ID . '&tipx=next&kodex=' . $header->ACNO) }}'"
                                                    class="btn btn-outline-primary">Next</button>
                                                <button type="button" hidden id='BOTTOMX'
                                                    onclick="location.href='{{ url('/sup/edit/?idx=' . $idx . '&tipx=bottom') }}'"
                                                    class="btn btn-outline-primary">Bottom</button>
                                            </div>
                                            <div class="col-md-5">
                                                <button type="button" hidden id='NEWX'
                                                    onclick="location.href='{{ url('/sup/edit/?idx=0&tipx=new') }}'"
                                                    class="btn btn-warning">New</button>
                                                <button type="button" hidden id='EDITX' onclick='hidup()'
                                                    class="btn btn-secondary">Edit</button>
                                                <button type="button" hidden id='UNDOX'
                                                    onclick="location.href='{{ url('/sup/edit/?idx=' . $idx . '&tipx=undo') }}'"
                                                    class="btn btn-info">Undo</button>
                                                <button type="button" id='SAVEX' onclick='simpan()'
                                                    class="btn btn-success" class="fa fa-save"></i>Save</button>

                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" hidden id='HAPUSX' onclick="hapusTrans()"
                                                    class="btn btn-outline-danger">Hapus</button>
                                                <button type="button" id='CLOSEX'
                                                    onclick="location.href='{{ url('/sup') }}'"
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
                    window.location = "{{ url('/sup/delete/' . $header->NO_ID) }}'";
                    //return true;
                }
                return false;
            }

            function CariBukti() {

                var cari = $("#CARI").val();
                var loc = "{{ url('/sup/edit/') }}" + '?idx={{ $header->NO_ID }}&tipx=search&kodex=' + encodeURIComponent(
                    cari);
                window.location = loc;

            }

            function simpan() {
                document.getElementById("entri").submit()
            }
        </script>
    @endsection
