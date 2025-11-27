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
                        <h1 class="m-0">Data Rekanan</h1>
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
                                    action="{{ $tipx == 'new' ? url('/rekanan/store/') : url('/rekanan/update/' . $header->NO_ID) }}"
                                    method="POST" name ="entri" id="entri">
                                    @csrf
                                    <div class="tab-content mt-3">
                                        <div class="tab-pane active">
                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="KODE" class="form-label">Kode</label>
                                                </div>

                                                <input type="text" class="form-control NO_ID" id="NO_ID"
                                                    name="NO_ID" placeholder="Masukkan NO_ID"
                                                    value="{{ $header->NO_ID ?? '' }}" hidden readonly>

                                                <input name="tipx" class="form-control flagz" id="tipx"
                                                    value="{{ $tipx }}" hidden>


                                                <div class="col-md-2">
                                                    <input type="text" class="form-control KODE" id="KODE"
                                                        placeholder="Masukkan Kode" value="{{ $header->KODE }}" readonly>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-1">
                                                    <label for="NAMA" class="form-label">Nama</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control NAMA" id="NAMA"
                                                        name="NAMA" placeholder="Masukkan Nama"
                                                        value="{{ $header->NAMA }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 col-md-12 form-group row">
                                            <div class="col-md-4">
                                                <button type="button" hidden id='TOPX'
                                                    onclick="location.href='{{ url('/rekanan/edit/?idx=' . $idx . '&tipx=top') }}'"
                                                    class="btn btn-outline-primary">Top</button>
                                                <button type="button" hidden id='PREVX'
                                                    onclick="location.href='{{ url('/rekanan/edit/?idx=' . $header->NO_ID . '&tipx=prev&kodex=' . $header->ACNO) }}'"
                                                    class="btn btn-outline-primary">Prev</button>
                                                <button type="button" hidden id='NEXTX'
                                                    onclick="location.href='{{ url('/rekanan/edit/?idx=' . $header->NO_ID . '&tipx=next&kodex=' . $header->ACNO) }}'"
                                                    class="btn btn-outline-primary">Next</button>
                                                <button type="button" hidden id='BOTTOMX'
                                                    onclick="location.href='{{ url('/rekanan/edit/?idx=' . $idx . '&tipx=bottom') }}'"
                                                    class="btn btn-outline-primary">Bottom</button>
                                            </div>
                                            <div class="col-md-5">
                                                <button type="button" hidden id='NEWX'
                                                    onclick="location.href='{{ url('/rekanan/edit/?idx=0&tipx=new') }}'"
                                                    class="btn btn-warning">New</button>
                                                <button type="button" hidden id='EDITX' onclick='hidup()'
                                                    class="btn btn-secondary">Edit</button>
                                                <button type="button" hidden id='UNDOX'
                                                    onclick="location.href='{{ url('/rekanan/edit/?idx=' . $idx . '&tipx=undo') }}'"
                                                    class="btn btn-info">Undo</button>
                                                <button type="button" id='SAVEX' onclick='simpan()'
                                                    class="btn btn-success" class="fa fa-save"></i>Save</button>

                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" hidden id='HAPUSX' onclick="hapusTrans()"
                                                    class="btn btn-outline-danger">Hapus</button>
                                                <button type="button" id='CLOSEX'
                                                    onclick="location.href='{{ url('/rekanan') }}'"
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

                    $("#KODE").attr("readonly", false);

                } else {
                    $("#KODE").attr("readonly", true);

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

                $('#KODE').val("");
                $('#NAMA').val("");
                $('#TGL').val("");
                $('#TGL_SLS').val("");
            }

            function hapusTrans() {
                let text = "Hapus Master " + $('#KODE').val() + "?";
                if (confirm(text) == true) {
                    window.location = "{{ url('/rekanan/delete/' . $header->NO_ID) }}'";
                    //return true;
                }
                return false;
            }

            function CariBukti() {

                var cari = $("#CARI").val();
                var loc = "{{ url('/rekanan/edit/') }}" + '?idx={{ $header->NO_ID }}&tipx=search&kodex=' + encodeURIComponent(
                    cari);
                window.location = loc;

            }

            function simpan() {
                document.getElementById("entri").submit()
            }
        </script>
    @endsection
