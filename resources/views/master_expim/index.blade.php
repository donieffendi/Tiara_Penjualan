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
                    <h1 class="m-0">Export-Import SQL</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Export-Import SQL</li>
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
                            <form method="POST" action="{{ url('expim/import') }}" enctype="multipart/form-data" id="importForm">
                                @csrf
                                <div class="form-group row">
                                    <div class="col-md-2">
                                        <input class="form-control date tglDr" id="tglDr" name="tglDr" type="text" autocomplete="off" value="{{ session()->get('filter_tglDari') }}">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-auto">
                                        <a href="{{ url('expim/export') }}" class="btn btn-secondary">Export</a>
                                    </div>

                                    <div class="col-md-auto">
                                        <button type="button" id="btnImport" class="btn btn-secondary">Import</button>
                                        <input type="file" name="import_file" id="import_file" accept=".sql" style="display:none;">
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
<script>
$(document).ready(function() {
    $('.date').datepicker({
        dateFormat: 'dd-mm-yy'
    });

    // Saat tombol Import diklik → buka file explorer
    $('#btnImport').click(function() {
        $('#import_file').click();
    });

    // Saat file dipilih → submit form otomatis
    $('#import_file').change(function() {
        if (this.files.length > 0) {
            $('#importForm').submit();
        }
    });
});
</script>
@endsection
