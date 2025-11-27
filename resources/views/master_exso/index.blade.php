@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
    @if (session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>
        <script>
            Swal.fire({
              title: 'INFO!',
              text: '{{session('status')}}',
              icon: 'success',
              confirmButtonText: 'OK'
            })
        </script>
    @endif

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Export Manual SO</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Export Manual SO</li>
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
                            <form method="POST" action="{{ url('exso/export') }}" enctype="multipart/form-data" id="importForm">
                                @csrf
                                <div class="form-group row">
                                    <div class="col-md-1" align="right">
                                        <label class="form-label">Tanggal</label>
                                    </div>
                                    <div class="col-md-2">
                                        <input class="form-control date tgl" id="tgl" name="tgl" type="text" autocomplete="off" value="{{ session()->get('filter_tgl') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-1" align="right">
                                        <label class="form-label">Sub</label>
                                    </div>
                                    <div class="col-md-2">						
                                        <input type="text" class="form-control sub" id="sub" name="sub" placeholder="Masukkan Sub" value="{{ session()->get('filter_sub') }}">
                                    </div>
                                </div>

                                <div class="form-group row" style="padding-left:170px">
                                    <div class="col-md-auto">
                                        <a href="#" id="btnExport" class="btn btn-secondary">Export</a>
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
@endsection

@section('javascripts')
<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('.date').datepicker({
        dateFormat: 'dd-mm-yy'
    });

    $('#btnExport').click(function (e) {
        e.preventDefault();

        let sub = $('#sub').val().trim();
        
        // VALIDASI: SUB tidak boleh kosong
        if (sub === "") {
            Swal.fire({
                title: "SUB Belum Diisi!",
                text: "Silakan isi SUB terlebih dahulu.",
                icon: "error",
                confirmButtonText: "OK"
            });
            return; // stop proses
        }

        Swal.fire({
            title: "Yakin Kirim Data?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
        }).then((result) => {
            if (!result.isConfirmed) return;

            // Ambil nilai form
            let sub = $('#sub').val();
            let tgl = $('#tgl').val(); // dd-mm-yyyy

            // Convert ke yyyy-mm-dd
            let parts = tgl.split("-");
            let tglSend = parts[2] + "-" + parts[1] + "-" + parts[0];

            $.ajax({
                url: "{{ url('exso/export') }}",   // route Laravel
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    sub: sub,
                    tgl: tglSend
                },
                success: function (res) {
                    Swal.fire("Berhasil!", res.message, "success");
                },
                error: function (xhr) {
                    Swal.fire("Error!", "Gagal mengirim data.", "error");
                }
            });
        });
    });
});
</script>
@endsection
