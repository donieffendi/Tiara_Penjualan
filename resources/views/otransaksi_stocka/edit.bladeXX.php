@extends('layouts.plain')

<style>
    .card {}

    .form-control:focus {
        background-color: #b5e5f9 !important;
    }

    /* menghilangkan padding */
    .content-header {
        padding: 0 !important;
    }
</style>

@section('content')

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dropdown with Select2</title>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    </head>


    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">

            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <form
                                    action="{{ $tipx == 'new' ? url('/stocka/store?flagz=' . $flagz . '') : url('/stocka/update/' . $header->NO_ID . '&flagz=' . $flagz . '') }}"
                                    method="POST" name ="entri" id="entri">

                                    @csrf
                                    <div class="tab-content mt-3">
                                        <div class="form-group row">
                                            <div class="col-md-1" align="right">
                                                <label for="NO_BUKTI" class="form-label">Bukti#</label>
                                            </div>


                                            <input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
                                                placeholder="Masukkan NO_ID" value="{{ $header->NO_ID ?? '' }}" hidden
                                                readonly>

                                            <input name="tipx" class="form-control tipx" id="tipx"
                                                value="{{ $tipx }}" hidden>
                                            <input name="flagz" class="form-control flagz" id="flagz"
                                                value="{{ $flagz }}" hidden>



                                            <div class="col-md-2">
                                                <input type="text" class="form-control NO_BUKTI" id="NO_BUKTI"
                                                    name="NO_BUKTI" placeholder="Masukkan Bukti#"
                                                    value="{{ $header->NO_BUKTI }}" readonly>
                                            </div>

                                            <div class="col-md-1" align="right">
                                                <label for="TGL" class="form-label">Tgl</label>
                                            </div>
                                            <div class="col-md-2">
                                                <input class="form-control date" id="TGL" name="TGL"
                                                    data-date-format="dd-mm-yyyy" type="text" autocomplete="off"
                                                    value="{{ date('d-m-Y', strtotime($header->TGL)) }}">
                                            </div>

                                        </div>

                                        <div class="form-group row">
                                            <div class="col-md-1" align="right">
                                                <!-- <label style="color:red">*</label>									 -->
                                                <label for="NOTES" class="form-label">Notes</label>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control NOTES" id="NOTES"
                                                    name="NOTES" value="{{ $header->NOTES }}"
                                                    placeholder="Masukkan Notes">
                                            </div>

                                        </div>



                                        <div class="tab-content mt-3">

                                            <table id="datatable" class="table table-striped table-border">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align: center;">No.</th>
                                                        <th style="text-align: center;">
                                                            <label style="color:red;font-size:20px">* </label>
                                                            <label for="KD_BRG" class="form-label">Kode Barang</label>
                                                        </th>
                                                        <th style="text-align: center;">Nama Barang</th>
                                                        <th style="text-align: center;">Stn</th>
                                                        <th {{ ($flagz == 'RO' || $flagz == 'TG') ? 'hidden' : '' }} style="text-align: center;">Qty-Comp</th>
                                                        <th {{ ($flagz == 'RO' || $flagz == 'TG') ? 'hidden' : '' }} style="text-align: center;">Qty Selisih</th>
                                                        <th style="text-align: center;">Qty </th>
                                                        <th style="text-align: center;">Ket</th>

                                                        <th></th>

                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <?php $no = 0; ?>
                                                    @foreach ($detail as $detail)
                                                        <tr>
                                                            <td>
                                                                <input type="hidden" name="NO_ID[]{{ $no }}"
                                                                    id="NO_ID" type="text"
                                                                    value="{{ $detail->NO_ID }}" class="form-control NO_ID"
                                                                    onkeypress="return tabE(this,event)" readonly>

                                                                <input name="REC[]" id="REC{{ $no }}"
                                                                    type="text" value="{{ $detail->REC }}"
                                                                    class="form-control REC"
                                                                    onkeypress="return tabE(this,event)" readonly
                                                                    style="text-align:center">
                                                            </td>


                                                            <td>
                                                                <input name="KD_BRG[]" id="KD_BRG{{ $no }}"
                                                                    type="text" value="{{ $detail->KD_BRG }}"
                                                                    class="form-control KD_BRG" readonly required>
                                                            </td>
                                                            <td>
                                                                <input name="NA_BRG[]" id="NA_BRG{{ $no }}"
                                                                    type="text" value="{{ $detail->NA_BRG }}"
                                                                    class="form-control NA_BRG" readonly required>
                                                            </td>
                                                            <td>
                                                                <input name="SATUAN[]" id="SATUAN{{ $no }}"
                                                                    type="text" value="{{ $detail->SATUAN }}"
                                                                    class="form-control SATUAN" readonly required>
                                                            </td>

                                                            <td {{ ($flagz == 'RO' || $flagz == 'TG') ? 'hidden' : '' }}>
                                                                <input name="QTYC[]" onclick="select()"
                                                                    onkeyup="hitung()" value="{{ $detail->QTYC }}"
                                                                    id="QTYC{{ $no }}" type="text"
                                                                    style="text-align: right"
                                                                    class="form-control QTYC text-primary">
                                                            </td>

                                                            <td>
                                                                <input name="QTYR[]" onclick="select()"
                                                                    onkeyup="hitung()" value="{{ $detail->QTYR }}"
                                                                    id="QTYR{{ $no }}" type="text"
                                                                    style="text-align: right"
                                                                    class="form-control QTYR text-primary">
                                                            </td>
                                                            <td {{ ($flagz == 'RO' || $flagz == 'TG') ? 'hidden' : '' }}>
                                                                <input name="QTY[]" onclick="select()"
                                                                    onkeyup="hitung()" value="{{ $detail->QTY }}"
                                                                    id="QTY{{ $no }}" type="text"
                                                                    style="text-align: right"
                                                                    class="form-control QTY text-primary" readonly></td>

                                                            <td>
                                                                <input name="KET[]" id="KET{{ $no }}"
                                                                    type="text" class="form-control KET"
                                                                    value="{{ $detail->KET }}" required>
                                                            </td>

                                                            <td>
                                                                <button type='button' id='DELETEX{{ $no }}'
                                                                    class='btn btn-sm btn-circle btn-outline-danger btn-delete'
                                                                    onclick=''> <i class='fa fa-fw fa-trash'></i>
                                                                </button>
                                                            </td>

                                                        </tr>

                                                        <?php $no++; ?>
                                                    @endforeach
                                                </tbody>

                                                <tfoot>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td {{ ($flagz == 'RO' || $flagz == 'TG') ? 'hidden' : '' }}></td>
                                                    <td>
                                                        <input class="form-control TTOTAL_QTY  text-primary font-weight-bold"
                                                            style="text-align: right" id="TTOTAL_QTY" name="TTOTAL_QTY"
                                                            value="{{ $header->TOTAL_QTY }}" readonly></td>
                                                    <td></td>
                                                    <td></td>
                                                </tfoot>
                                            </table>

                                            <div class="col-md-2 row">
                                                <a type="button" id='PLUSX' onclick="tambah()"
                                                    class="fas fa-plus fa-sm md-3" style="font-size: 20px"></a>

                                            </div>

                                        </div>
                                    </div>

                                    <hr style="margin-top: 30px; margin-buttom: 30px">
                                    <!-- dari sini shelvi-->

                                    <!-- sampai sini shelvi-->

                                    <div class="mt-3 col-md-12 form-group row">
                                        <div class="col-md-4">
                                            <button hidden type="button" id='TOPX'
                                                onclick="location.href='{{ url('/stocka/edit/?idx=' . $idx . '&tipx=top&flagz=' . $flagz . '') }}'"
                                                class="btn btn-outline-primary">Top</button>
                                            <button hidden type="button" id='PREVX'
                                                onclick="location.href='{{ url('/stocka/edit/?idx=' . $header->NO_ID . '&tipx=prev&flagz=' . $flagz . '&buktix=' . $header->NO_BUKTI) }}'"
                                                class="btn btn-outline-primary">Prev</button>
                                            <button hidden type="button" id='NEXTX'
                                                onclick="location.href='{{ url('/stocka/edit/?idx=' . $header->NO_ID . '&tipx=next&flagz=' . $flagz . '&buktix=' . $header->NO_BUKTI) }}'"
                                                class="btn btn-outline-primary">Next</button>
                                            <button hidden type="button" id='BOTTOMX'
                                                onclick="location.href='{{ url('/stocka/edit/?idx=' . $idx . '&tipx=bottom&flagz=' . $flagz . '') }}'"
                                                class="btn btn-outline-primary">Bottom</button>
                                        </div>
                                        <div class="col-md-5">
                                            <button hidden type="button" id='NEWX'
                                                onclick="location.href='{{ url('/stocka/edit/?idx=0&tipx=new&flagz=' . $flagz . '') }}'"
                                                class="btn btn-warning">New</button>
                                            <button hidden type="button" id='EDITX' onclick='hidup()'
                                                class="btn btn-secondary">Edit</button>
                                            <button hidden type="button" id='UNDOX'
                                                onclick="location.href='{{ url('/stocka/edit/?idx=' . $idx . '&tipx=undo&flagz=' . $flagz . '') }}'"
                                                class="btn btn-info">Undo</button>
                                            <button type="button" id='SAVEX' onclick='simpan()'
                                                class="btn btn-success" class="fa fa-save"></i>Save</button>

                                        </div>
                                        <div class="col-md-3">
                                            <button hidden type="button" id='HAPUSX' onclick="hapusTrans()"
                                                class="btn btn-outline-danger">Hapus</button>
                                            <!-- <button type="button" id='CLOSEX'  onclick="location.href='{{ url('/stocka?flagz=' . $flagz . '') }}'" class="btn btn-outline-secondary">Close</button> -->
                                            <!-- tombol close sweet alert -->
                                            <button type="button" id='CLOSEX' onclick="closeTrans()"
                                                class="btn btn-outline-secondary">Close</button>
                                        </div>
                                    </div>


                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="modal fade" id="browseBarangModal" tabindex="-1" role="dialog"
            aria-labelledby="browseBarangModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="browseBarangModalLabel">Cari Item</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-stripped table-bordered" id="table-bbarang">
                            <thead>
                                <tr>
                                    <th>Item#</th>
                                    <th>Nama</th>
                                    <th>Satuan</th>

                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('footer-scripts')
        <script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> -->
        <script src="{{ asset('foxie_js_css/bootstrap.bundle.min.js') }}"></script>

        <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script> -->

        <!-- tambahan untuk sweetalert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- tutupannya -->

        <script>
            var idrow = 1;
            var baris = 1;

            function numberWithCommas(x) {
                return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            $(document).ready(function() {
                idrow = <?= $no ?>;
                baris = <?= $no ?>;

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
                            tambah();
                            var nomer = idrow - 1;
                            console.log("KD_BRG" + nomor);
                            document.getElementById("KD_BRG" + nomor).focus();
                            // form.submit();
                        }
                        return false;
                    }
                });


                $tipx = $('#tipx').val();
                $searchx = $('#CARI').val();


                if ($tipx == 'new') {
                    baru();
                    tambah();
                }

                if ($tipx != 'new') {
                    ganti();
                }

                $("#TTOTAL_QTY").autoNumeric('init', {
                    aSign: '<?php echo ''; ?>',
                    vMin: '-999999999.99'
                });


                jumlahdata = 100;
                for (i = 0; i <= jumlahdata; i++) {
                    $("#QTYC" + i.toString()).autoNumeric('init', {
                        aSign: '<?php echo ''; ?>',
                        vMin: '-999999999.99'
                    });
                    $("#QTYR" + i.toString()).autoNumeric('init', {
                        aSign: '<?php echo ''; ?>',
                        vMin: '-999999999.99'
                    });
                    $("#QTY" + i.toString()).autoNumeric('init', {
                        aSign: '<?php echo ''; ?>',
                        vMin: '-999999999.99'
                    });
                }


                $('body').on('click', '.btn-delete', function() {
                    var val = $(this).parents("tr").remove();
                    baris--;
                    hitung();
                    nomor();

                });

                $('.date').datepicker({
                    dateFormat: 'dd-mm-yy'
                });




                //////////////////////////////////////////////////////

                var dTableBBarang;
                var rowidBarang;
                loadDataBBarang = function() {

                    $.ajax({
                        type: 'GET',
                        url: "{{ url('brg/browse_beli') }}",
                        async: false,
                        data: {
                            'KD_BRG': $("#KD_BRG" + rowidBarang).val(),

                        },
                        success: function(response)

                        {
                            resp = response;


                            if (resp.length > 1) {
                                if (dTableBBarang) {
                                    dTableBBarang.clear();
                                }
                                for (i = 0; i < resp.length; i++) {

                                    dTableBBarang.row.add([
                                        '<a href="javascript:void(0);" onclick="chooseBarang(\'' +
                                        resp[i].KD_BRG + '\', \'' + resp[i].NA_BRG + '\' , \'' +
                                        resp[i].SATUAN + '\', \'' + resp[i].AK12 + '\' )">' + resp[
                                            i].KD_BRG + '</a>',
                                        resp[i].NA_BRG,
                                        resp[i].SATUAN,
                                    ]);
                                }
                                dTableBBarang.draw();

                            } else {
                                $("#KD_BRG" + rowidBarang).val(resp[0].KD_BRG);
                                $("#NA_BRG" + rowidBarang).val(resp[0].NA_BRG);
                                $("#SATUAN" + rowidBarang).val(resp[0].SATUAN);
                            }
                        }
                    });
                }

                dTableBBarang = $("#table-bbarang").DataTable({

                });

                browseBarang = function(rid) {
                    rowidBarang = rid;
                    $("#NA_BRG" + rowidBarang).val("");
                    loadDataBBarang();


                    if ($("#NA_BRG" + rowidBarang).val() == '') {
                        $("#browseBarangModal").modal("show");
                    }
                }

                chooseBarang = function(KD_BRG, NA_BRG, SATUAN, AK12) {
                    $("#KD_BRG" + rowidBarang).val(KD_BRG);
                    $("#NA_BRG" + rowidBarang).val(NA_BRG);
                    $("#SATUAN" + rowidBarang).val(SATUAN);
                    $("#QTYC" + rowidBarang).val(AK12);
                    $("#browseBarangModal").modal("hide");
                }


                /* $("#RAK0").onblur(function(e){
                	if(e.keyCode == 46){
                		e.preventDefault();
                		browseRak(0);
                	}
                });  */

                ////////////////////////////////////////////////////
            });



            ///////////////////////////////////////




            function cekDetail() {
                var cekBarang = '';
                $(".KD_BRG").each(function() {

                    let z = $(this).closest('tr');
                    var KD_BRGX = z.find('.KD_BRG').val();

                    if (KD_BRGX == "") {
                        cekBarang = '1';

                    }
                });

                return cekBarang;
            }


            // function simpan() {
            // 	hitung();

            // 	var tgl = $('#TGL').val();
            // 	var bulanPer = {{ session()->get('periode')['bulan'] }};
            // 	var tahunPer = {{ session()->get('periode')['tahun'] }};

            //     var check = '0';



            // 		// if (cekDetail())
            // 		// {
            // 		//     check = '1';
            // 		// 	alert("#Barang ada yang kosong. ")
            // 		// }




            // 		if ( $('#KD_BRG').val()=='' )
            //         {
            // 		    check = '1';
            // 			alert("Barang# Harus Diisi.");
            // 		}


            // 		if ( tgl.substring(3,5) != bulanPer )
            // 		{
            // 			check = '1';
            // 			alert("Bulan tidak sama dengan Periode");
            // 		}


            // 		if ( tgl.substring(tgl.length-4) != tahunPer )
            // 		{
            // 			check = '1';
            // 			alert("Tahun tidak sama dengan Periode");
            // 	    }

            // 		if (baris==0)
            // 		{
            // 			check = '1';
            // 			alert("Data detail kosong (Tambahkan 1 baris kosong jika ingin mengosongi detail)");
            // 		}

            // 		if ( check == '0' )
            // 		{
            // 			hitung();
            // 	      	document.getElementById("entri").submit();
            // 		}

            // }

            function simpan() {
                hitung();

                var tgl = $('#TGL').val();
                var bulanPer = {{ session()->get('periode')['bulan'] }};
                var tahunPer = {{ session()->get('periode')['tahun'] }};

                var check = '0';

                if ($('#NOTES').val() == '') {
                    check = '1';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Notes Harus Diisi.'
                    });
                    return; // Stop function execution
                }

                if ($('#KD_BRG').val() == '') {
                    check = '1';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Barang# Harus Diisi.'
                    });
                    return; // Stop function execution
                }


                if (tgl.substring(3, 5) != bulanPer) {

                    check = '1';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Bulan tidak sama dengan Periode'
                    });
                    return; // Stop function execution
                    alert("Bulan tidak sama dengan Periode");
                }


                if (tgl.substring(tgl.length - 4) != tahunPer) {
                    check = '1';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Tahun tidak sama dengan Periode'
                    });
                    return; // Stop function execution

                }

                if (baris == 0) {
                    check = '1';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Data detail kosong (Tambahkan 1 baris kosong jika ingin mengosongi detail)'
                    });
                    return; // Stop function execution
                }

                if (check == '0') {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'Are you sure you want to save?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, save it!',
                        cancelButtonText: 'No, cancel',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById("entri").submit();
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'Cancelled',
                                text: 'Your data was not saved'
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Masih ada kesalahan'
                    });
                }

                // tutupannya

                $("#LOADX").hide();
            }

            function nomor() {
                var i = 1;
                $(".REC").each(function() {
                    $(this).val(i++);
                });

                //	hitung();

            }

            function hitung() {
                var TTOTAL_QTY = 0;


                $(".QTY").each(function() {

                    let z = $(this).closest('tr');
                    var QTYRX = parseFloat(z.find('.QTYR').val().replace(/,/g, ''));
                    var QTYCX = parseFloat(z.find('.QTYC').val().replace(/,/g, ''));
                    var QTYX;

                    if('{{$flagz}}' == 'KZ'){
                        if (QTYRX < 0) {
                            QTYX = QTYCX - Math.abs(QTYRX);
                        } else {
                            QTYX = QTYCX + QTYRX;
                        }
                    }else if('{{$flagz}}' == 'RO'){
                        QTYX = QTYCX - QTYRX;
                    }else if('{{$flagz}}' == 'TG'){
                        QTYX = QTYCX + QTYRX;
                    }


                    z.find('.QTY').val(QTYX);
                    z.find('.QTY').autoNumeric('update');
                    TTOTAL_QTY += QTYX;

                });


                if (isNaN(TTOTAL_QTY)) TTOTAL_QTY = 0;

                $('#TTOTAL_QTY').val(numberWithCommas(TTOTAL_QTY));
                $("#TTOTAL_QTY").autoNumeric('update');

            }




            function baru() {

                kosong();
                hidup();

            }

            function ganti() {

                //  mati();
                hidup();

            }

            function batal() {

                // alert($header[0]->NO_BUKTI);

                //$('#NO_BUKTI').val($header[0]->NO_BUKTI);
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

                $("#CARI").attr("readonly", true);
                $("#SEARCHX").attr("disabled", true);

                $("#PLUSX").attr("hidden", false)

                $("#NO_BUKTI").attr("readonly", true);
                $("#TGL").attr("readonly", false);
                $("#NOTES").attr("readonly", false);
                $("#TTOTAL_QTY").attr("readonly", true);


                jumlahdata = 100;
                for (i = 0; i <= jumlahdata; i++) {
                    $("#REC" + i.toString()).attr("readonly", true);
                    $("#KD_BRG" + i.toString()).attr("readonly", false);
                    $("#NA_BRG" + i.toString()).attr("readonly", true);
                    $("#SATUAN" + i.toString()).attr("readonly", true);
                    $("#QTYC" + i.toString()).attr("readonly", true);
                    $("#QTYR" + i.toString()).attr("readonly", false);
                    $("#QTY" + i.toString()).attr("readonly", true);
                    $("#KET" + i.toString()).attr("readonly", false);
                    $("#DELETEX" + i.toString()).attr("hidden", false);

                    $tipx = $('#tipx').val();


                    if ($tipx != 'new') {
                        $("#KD_BRG" + i.toString()).attr("readonly", true);
                        $("#KD_BRG" + i.toString()).removeAttr('onblur');
                    }
                }


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

                $("#CARI").attr("readonly", false);
                $("#SEARCHX").attr("disabled", false);


                $("#PLUSX").attr("hidden", true)

                $(".NO_BUKTI").attr("readonly", true);
                $("#TGL").attr("readonly", true);
                $("#NOTES").attr("readonly", true);
                $("#TTOTAL_QTY").attr("readonly", true);


                jumlahdata = 100;
                for (i = 0; i <= jumlahdata; i++) {
                    $("#REC" + i.toString()).attr("readonly", true);
                    $("#KD_BRG" + i.toString()).attr("readonly", true);
                    $("#NA_BRG" + i.toString()).attr("readonly", true);
                    $("#SATUAN" + i.toString()).attr("readonly", true);
                    $("#QTYC" + i.toString()).attr("readonly", true);
                    $("#QTYR" + i.toString()).attr("readonly", true);
                    $("#QTY" + i.toString()).attr("readonly", true);
                    $("#KET" + i.toString()).attr("readonly", true);

                    $("#DELETEX" + i.toString()).attr("hidden", true);
                }



            }


            function kosong() {

                $('#NO_BUKTI').val("+");
                $('#NOTES').val("");
                $('#TTOTAL_QTY').val("0.00");

                var html = '';
                $('#detailx').html(html);

            }

            // function hapusTrans() {
            // 	let text = "Hapus Transaksi "+$('#NO_BUKTI').val()+"?";
            // 	if (confirm(text) == true)
            // 	{
            // 		window.location ="{{ url('/stocka/delete/' . $header->NO_ID . '/?flagz=' . $flagz . '') }}";
            // 		//return true;
            // 	}
            // 	return false;
            // }

            function hapusTrans() {
                let text = "Hapus Transaksi " + $('#NO_BUKTI').val() + "?";

                var loc = '';
                var flagz = "{{ $flagz }}";

                Swal.fire({
                    title: 'Are you sure?',
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show a success message before redirecting to delete the data
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Data has been deleted.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Redirect to delete the data after user confirms the success message
                            loc = "{{ url('/stocka/delete/' . $header->NO_ID) }}" + '?flagz=' +
                                encodeURIComponent(flagz);

                            // alert(loc);
                            window.location = loc;

                        });
                    }
                });
            }

            function closeTrans() {
                console.log("masuk");
                var loc = '';
                var flagz = "{{ $flagz }}";

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you really want to close this page? Unsaved changes will be lost.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, close it',
                    cancelButtonText: 'No, stay here'
                }).then((result) => {
                    if (result.isConfirmed) {
                        loc = "{{ url('/stocka/') }}" + '?flagz=' + encodeURIComponent(flagz);
                        window.location = loc;
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Cancelled',
                            text: 'You stayed on the page'
                        });
                    }
                });
            }

            // tutupannya


            function CariBukti() {

                var flagz = "{{ $flagz }}";
                var cari = $("#CARI").val();
                var loc = "{{ url('/stocka/edit/') }}" + '?idx={{ $header->NO_ID }}&tipx=search&flagz=' + encodeURIComponent(
                    flagz) + '&buktix=' + encodeURIComponent(cari);
                window.location = loc;

            }


            function tambah() {

                var x = document.getElementById('datatable').insertRow(baris + 1);

                html = `<tr>

                <td>
 					<input name='NO_ID[]' id='NO_ID${idrow}' type='hidden' class='form-control NO_ID' value='new' readonly>
					<input name='REC[]' id='REC${idrow}' type='text' class='REC form-control' onkeypress='return tabE(this,event)' readonly>
	            </td>

                <td>
				    <input name='KD_BRG[]' data-rowid=${idrow} onblur='browseBarang(${idrow})' id='KD_BRG${idrow}' type='text' class='form-control  KD_BRG' >
                </td>
                <td>
				    <input name='NA_BRG[]'   id='NA_BRG${idrow}' type='text' class='form-control  NA_BRG' required readonly>
                </td>

                <td>
				    <input name='SATUAN[]'   id='SATUAN${idrow}' type='text' class='form-control  SATUAN' readonly required>
                </td>

				<td>
		            <input name='QTYC[]' onclick='select()' onblur='hitung()' value='0' id='QTYC${idrow}' type='text' style='text-align: right' class='form-control QTYC text-primary' readonly >
                </td>

				<td>
		            <input name='QTYR[]' onclick='select()' onblur='hitung()' value='0' id='QTYR${idrow}' type='text' style='text-align: right' class='form-control QTYR text-primary' required >
                </td>

				<td>
		            <input name='QTY[]' onclick='select()' onblur='hitung()' value='0' id='QTY${idrow}' type='text' style='text-align: right' class='form-control QTY text-primary' readonly >
                </td>

                <td>
				    <input name='KET[]'   id='KET${idrow}' type='text' class='form-control  KET' required>
                </td>

                <td>
					<button type='button' id='DELETEX${idrow}'  class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
                </td>
         </tr>`;

                x.innerHTML = html;
                var html = '';



                jumlahdata = 100;
                for (i = 0; i <= jumlahdata; i++) {
                    $("#QTYC" + i.toString()).autoNumeric('init', {
                        aSign: '<?php echo ''; ?>',
                        vMin: '-999999999.99'
                    });


                    $("#QTYR" + i.toString()).autoNumeric('init', {
                        aSign: '<?php echo ''; ?>',
                        vMin: '-999999999.99'
                    });

                    $("#QTY" + i.toString()).autoNumeric('init', {
                        aSign: '<?php echo ''; ?>',
                        vMin: '-999999999.99'
                    });


                }


                idrow++;
                baris++;
                nomor();

                $(".ronly").on('keydown paste', function(e) {
                    e.preventDefault();
                    e.currentTarget.blur();
                });
            }
        </script>
        <!--
    <script src="autonumeric.min.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.5.4"></script>
    <script src="https://unpkg.com/autonumeric"></script> -->
    @endsection
