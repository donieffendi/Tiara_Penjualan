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
    /* perubahan tab warna di form edit  */
	.nav-item .nav-link.active {
		background-color: red !important; /* Use !important to ensure it overrides */
		color: white !important;
        /* border-radius: 10; */
	}
</style>

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Data Barang Kasir</h1>
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
                                    action="{{ $tipx == 'new' ? url('/dbrg2/store/') : url('/dbrg2/update/' . $header->NO_ID) }}"
                                    method="POST" name ="entri" id="entri">
                                    @csrf
                                    <div class="tab-content mt-3">
                                        <div class="form-group row">
                                            <div class="col-md-1" align="right">
                                                <label for="SUB2">Kelompok Barang</label>
                                            </div>
                                            <input type="text" class="form-control NO_ID" id="NO_ID"
                                                name="NO_ID" placeholder="Masukkan NO_ID"
                                                value="{{ $header->NO_ID ?? '' }}" hidden readonly>

                                            <input name="tipx" class="form-control flagz" id="tipx"
                                                value="{{ $tipx }}" hidden>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" name="SUB2" id="SUB2"
                                                    value="{{ $header->SUB2 }}" placeholder="Masukkan Sub">
                                            </div>

                                            <div class="col-md-2">
                                                <input type="text" class="form-control" name="KELOMPOK" id="KELOMPOK"
                                                    value="{{ $header->KELOMPOK }}" placeholder="Masukkan Kelompok">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-md-1" align="right">
                                                <label for="SUB">Kode Barang</label>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" name="SUB" id="SUB"
                                                    value="{{ $header->SUB }}" placeholder="Masukkan Sub">
                                            </div>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" name="KDBAR" id="KDBAR"
                                                    value="{{ $header->KDBAR }}" placeholder="Masukkan No. Item">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-md-1" align="right">
                                                <label for="NA_BRG">Nama</label>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" name="NA_BRG" id="NA_BRG"
                                                    value="{{ $header->NA_BRG }}" placeholder="Masukkan Nama Barang">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-md-1" align="right">
                                                <label for="BARCODE">Barcode</label>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" name="BARCODE" id="BARCODE"
                                                    value="{{ $header->BARCODE }}" placeholder="Masukkan Barcode">
                                            </div>
                                        </div>

                                        <div class="tab-content">
                                            <ul class="nav nav-tabs">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-toggle="tab" href="#main">Main</a>
                                                </li>
                                            </ul>

                                            <div class="tab-content mt-3">
                                                <div class="tab-pane show active" id="main">
                                                    <div class="form-group row">
                                                        <div class="col-md-1" align="right">
                                                            <label for="KET_KEM">Kemasan</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control" name="KET_KEM" id="KET_KEM"
                                                                value="{{ $header->KET_KEM }}" placeholder="Masukkan Kemasan">
                                                        </div>
                                                        <div class="col-md-1" align="right">
                                                            <label for="KET_UK">Ukuran</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control" name="KET_UK" id="KET_UK"
                                                                value="{{ $header->KET_UK }}" placeholder="Masukkan Ukuran">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-md-1" align="right">
                                                            <label for="SUPP">Supplier</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control" name="SUPP" id="SUPP"
                                                                value="{{ $header->SUPP }}" placeholder="Masukkan Kemasan">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-md-1" align="right">
                                                            <label for="NSUP">Nama Supplier</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control " name="NSUP" id="NSUP"
                                                                value="{{ $header->NSUP }}" placeholder="Masukkan Nama Supplier">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-md-1" align="right">
                                                            <label for="HJGZ">Harga TGZ</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control text-right" name="HJGZ" id="HJGZ"
                                                                value="{{ $header->HJGZ }}" placeholder="Masukkan Harga TGZ">
                                                        </div>
                                                        <div class="col-md-1" align="right">
                                                            <label for="HJMM">Harga TMM</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control text-right" name="HJMM" id="HJMM"
                                                                value="{{ $header->HJMM }}" placeholder="Masukkan Harga TMM">
                                                        </div>
                                                        <div class="col-md-1" align="right">
                                                            <label for="HJSP">Harga SOP</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control text-right" name="HJSP" id="HJSP"
                                                                value="{{ $header->HJSP }}" placeholder="Masukkan Harga SOP">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-md-1" align="right">
                                                            <label for="TGDIS_M" class="form-label">Diskon Mulai</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input class="form-control date" id="TGDIS_M" name="TGDIS_M" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGDIS_M))}}">
                                                        </div>
                                                        <div class="col-md-1" align="right">
                                                            <label for="TGDIS_A" class="form-label">Sampai Dengan</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input class="form-control date" id="TGDIS_A" name="TGDIS_A" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGDIS_A))}}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-md-1" align="right">
                                                            <label for="JAM">Jam</label>
                                                        </div>
                                                        <div class="col-md-1">
                                                            <input type="text" class="form-control" name="JAM" id="JAM"
                                                                value="{{ $header->JAM }}" placeholder="HH:MM:SS" pattern="\d{2}:\d{2}:\d{2}">
                                                        </div>
                                                        <div class="col-md-1" align="right">
                                                            <label for="JAMSLS">Sampai Dengan</label>
                                                        </div>
                                                        <div class="col-md-1">
                                                            <input type="text" class="form-control" name="JAMSLS" id="JAMSLS"
                                                                value="{{ $header->JAMSLS }}" placeholder="HH:MM:SS" pattern="\d{2}:\d{2}:\d{2}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-md-1" align="right">
                                                            <label for="DISGZ">Diskon TGZ</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control text-right" name="DISGZ" id="DISGZ"
                                                                value="{{ $header->DISGZ }}" placeholder="Masukkan Diskon TGZ">
                                                        </div>
                                                        <div class="col-md-1" align="right">
                                                            <label for="DISMM">Diskon TMM</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control text-right" name="DISMM" id="DISMM"
                                                                value="{{ $header->DISMM }}" placeholder="Masukkan Diskon TMM">
                                                        </div>
                                                        <div class="col-md-1" align="right">
                                                            <label for="DISSP">Diskon SOP</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control text-right" name="DISSP" id="DISSP"
                                                                value="{{ $header->DISSP }}" placeholder="Masukkan Diskon SOP">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-md-1" align="right">
                                                            <label for="HS">HS</label>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control text-right" name="HS" id="HS"
                                                                value="{{ $header->HS }}" placeholder="Masukkan Nilai HS">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-md-1" align="right">
                                                            <label for="HJ_VIP">HJ VIP</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control text-right" name="HJ_VIP" id="HJ_VIP"
                                                                value="{{ $header->HJ_VIP }}" placeholder="Masukkan Nilai HJ VIP">
                                                        </div>
                                                        <div class="col-md-1" align="right">
                                                            <label for="MARGIN">Margin</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control text-right" name="MARGIN" id="MARGIN"
                                                                value="{{ $header->MARGIN }}" placeholder="Masukkan Nilai Margin">
                                                        </div>
                                                        <div class="col-md-1" align="right">
                                                            <label for="FR">FR</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control text-right" name="FR" id="FR"
                                                                value="{{ $header->FR }}" placeholder="Masukkan Nilai FR">
                                                        </div>

                                                        <div class="col-md-2"align="left">
                                                            <input type="checkbox" class="form-check-input" id="PPN" name="PPN" value="1" {{ ($header->PPN == 1) ? 'checked' : '' }}>
                                                            <label for="PPN">PPN</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3 col-md-12 form-group row">
                                            <div class="col-md-4">
                                                <button type="button" hidden id='TOPX'
                                                    onclick="location.href='{{ url('/dbrg2/edit/?idx=' . $idx . '&tipx=top') }}'"
                                                    class="btn btn-outline-primary">Top</button>
                                                <button type="button" hidden id='PREVX'
                                                    onclick="location.href='{{ url('/dbrg2/edit/?idx=' . $header->NO_ID . '&tipx=prev&kodex=' . $header->SUB) }}'"
                                                    class="btn btn-outline-primary">Prev</button>
                                                <button type="button" hidden id='NEXTX'
                                                    onclick="location.href='{{ url('/dbrg2/edit/?idx=' . $header->NO_ID . '&tipx=next&kodex=' . $header->SUB) }}'"
                                                    class="btn btn-outline-primary">Next</button>
                                                <button type="button" hidden id='BOTTOMX'
                                                    onclick="location.href='{{ url('/dbrg2/edit/?idx=' . $idx . '&tipx=bottom') }}'"
                                                    class="btn btn-outline-primary">Bottom</button>
                                            </div>
                                            <div class="col-md-5">
                                                <button type="button" hidden id='NEWX'
                                                    onclick="location.href='{{ url('/dbrg2/edit/?idx=0&tipx=new') }}'"
                                                    class="btn btn-warning">New</button>
                                                <button type="button" hidden id='EDITX' onclick='hidup()'
                                                    class="btn btn-secondary">Edit</button>
                                                <button type="button" hidden id='UNDOX'
                                                    onclick="location.href='{{ url('/dbrg2/edit/?idx=' . $idx . '&tipx=undo') }}'"
                                                    class="btn btn-info">Undo</button>
                                                <button type="button" id='SAVEX' onclick='simpan()'
                                                    class="btn btn-success" class="fa fa-save"></i>Save</button>

                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" hidden id='HAPUSX' onclick="hapusTrans()"
                                                    class="btn btn-outline-danger">Hapus</button>
                                                <button type="button" id='CLOSEX'
                                                    onclick="location.href='{{ url('/dbrg2') }}'"
                                                    class="btn btn-outline-secondary">Close</button>


                                            </div>
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
        <script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js"></script>
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

                $("#HJGZ").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
                $("#HJMM").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
                $("#HJSP").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
                $("#DISGZ").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
                $("#DISMM").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
                $("#DISSP").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
                $("#HJ_VIP").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
                $("#MARGIN").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
                $("#FR").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});

                $('.date').datepicker({
                    dateFormat: 'dd-mm-yy'
                });

                $(function() {
                    $('#JAM, #JAMSLS').timepicker({
                        timeFormat: 'HH:mm:ss',
                        stepHour: 1,
                        stepMinute: 1,
                        stepSecond: 1
                    });
                });
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

                    $("#SUB").attr("readonly", false);

                } else {
                    $("#SUB").attr("readonly", true);

                }

                $("#KD_BRG").attr("readonly", false);
                $("#NA_BRG").attr("readonly", false);
                $("#KELOMPOK").attr("readonly", false);
				$("#HB").attr("readonly", false);
				$("#HJ").attr("readonly", false);
				$("#TKP").attr("readonly", false);
				$("#FLAGSTOK").attr("readonly", false);
				$("#SUPP").attr("readonly", false);
				$("#NAMAS").attr("readonly", false);
				$("#MARGIN").attr("readonly", false);
				$("#MARGIN2").attr("readonly", false);
				$("#HJ2").attr("readonly", false);
				$("#DIS").attr("readonly", false);
				$("#STAND").attr("readonly", false);
				$("#TYPE").attr("readonly", false);
				$("#BARCODE").attr("readonly", false);
				$("#LOK_TG").attr("readonly", false);
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

                $("#SUB").attr("readonly", true);
                $("#KD_BRG").attr("readonly", true);
                $("#NA_BRG").attr("readonly", true);
                $("#KELOMPOK").attr("readonly", true);
				$("#HB").attr("readonly", true);
				$("#HJ").attr("readonly", true);
				$("#TKP").attr("readonly", true);
				$("#FLAGSTOK").attr("readonly", true);
				$("#SUPP").attr("readonly", true);
				$("#NAMAS").attr("readonly", true);
				$("#MARGIN").attr("readonly", true);
				$("#MARGIN2").attr("readonly", true);
				$("#HJ2").attr("readonly", true);
				$("#DIS").attr("readonly", true);
				$("#STAND").attr("readonly", true);
				$("#TYPE").attr("readonly", true);
				$("#BARCODE").attr("readonly", true);
				$("#LOK_TG").attr("readonly", true);
            }


            function kosong() {
                $('#HJGZ').val("0.00");
                $('#HJMM').val("0.00");
                $('#HJSP').val("0.00");
                $('#DISHJ').val("0.00");
                $('#DISMM').val("0.00");
                $('#DISSP').val("0.00");
                $('#HJ_VIP').val("0.00");
                $('#MARGIN').val("0.00");
                $('#FR').val("0.00");
            }

            function hapusTrans() {
                let text = "Hapus Master " + $('#SUB').val() + "?";
                if (confirm(text) == true) {
                    window.location = "{{ url('/dbrg2/delete/' . $header->NO_ID) }}'";
                    //return true;
                }
                return false;
            }

            function CariBukti() {

                var cari = $("#CARI").val();
                var loc = "{{ url('/dbrg2/edit/') }}" + '?idx={{ $header->NO_ID }}&tipx=search&kodex=' + encodeURIComponent(
                    cari);
                window.location = loc;

            }

            function simpan() {
                document.getElementById("entri").submit()
            }
        </script>
    @endsection
