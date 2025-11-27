@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Laporan Koreksi Stock Barang</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Laporan Koreksi Stock Barang</li>
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
                            <form method="POST" action="{{url('jasper-stockb-report')}}">
                                @csrf

                                <!-- Filter Tanggal -->
                                <div class="form-group">
                                    <label for="tglDr"><strong>Tanggal :</strong></label>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-2">
                                        <input class="form-control date tglDr" id="tglDr" name="tglDr" type="text" autocomplete="off" value="{{ session()->get('filter_tglDari') }}">
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <span>s.d.</span>
                                    </div>
                                    <div class="col-md-2">
                                        <input class="form-control date tglSmp" id="tglSmp" name="tglSmp" type="text" autocomplete="off" value="{{ session()->get('filter_tglSampai') }}">
                                    </div>
                                </div>

                                <!-- Filter Jenis -->
                                <div class="form-group row">
                                    <div class="col-md-2">
                                        <label for="flag"><strong>Jenis :</strong></label>
                                        <select name="flag" id="flag" class="form-control">
                                            <option value="KZ" {{ session()->get('filter_flag') == 'KZ' ? 'selected' : '' }}>Koreksi Stock</option>
                                            <option value="RZ" {{ session()->get('filter_flag') == 'RZ' ? 'selected' : '' }}>Retur Outlet</option>
                                            <option value="TZ" {{ session()->get('filter_flag') == 'TZ' ? 'selected' : '' }}>Terima Retur</option>
                                            <option value="SZ" {{ session()->get('filter_flag') == 'SZ' ? 'selected' : '' }}>Stock Opname</option>
                                            <option value="MZ" {{ session()->get('filter_flag') == 'MZ' ? 'selected' : '' }}>Musnah</option>
                                        </select>
                                    </div>

                                    <div class="col-md-1"></div>

                                    <div class="col-md-2">
                                        <label for="posted"><strong>Posted :</strong></label>
                                        <select name="posted" id="posted" class="form-control">
                                            <option value="0" {{ session()->get('filter_posted') == '0' ? 'selected' : '' }}>BELUM POSTING</option>
                                            <option value="1" {{ session()->get('filter_posted') == '1' ? 'selected' : '' }}>SUDAH POSTING</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="form-group">
                                    <button class="btn btn-primary" type="submit" name="filter">Filter</button>
                                    <button class="btn btn-danger" type="button" onclick="window.location='{{url("rstockb")}}'">Reset</button>
                                    <button class="btn btn-warning" type="submit" formtarget="_blank">Cetak</button>
                                </div>
                            </form>

                            <!-- KoolReport Table -->
                            <div class="report-content" style="max-width: 100%; overflow-x: auto;">
                                <?php
                                use \koolreport\datagrid\DataTables;

                                if ($hasil) {
                                    DataTables::create(array(
                                        "dataSource" => $hasil,
                                        "name" => "example",
                                        "fastRender" => true,
                                        "fixedHeader" => true,
                                        'scrollX' => true,
                                        "showFooter" => true,
                                        "columns" => array(
                                            "NO_BUKTI" => array("label" => "Bukti#"),
                                            "TGL" => array("label" => "Tanggal"),
                                            "KD_BRG" => array("label" => "Barang#"),
                                            "NA_BRG" => array("label" => "-"),
                                            "QTY" => array(
                                                "label" => "Qty",
                                                "type" => "number",
                                                "decimals" => 2,
                                                "decimalPoint" => ".",
                                                "thousandSeparator" => ",",
                                                "footer" => "sum",
                                                "footerText" => "<b>@value</b>"
                                            ),
                                            "NOTES" => array("label" => "Notes")
                                        ),
                                        "cssClass" => array(
                                            "table" => "table table-hover table-striped table-bordered compact",
                                            "th" => "label-title",
                                            "td" => "detail",
                                            "tf" => "footerCss"
                                        ),
                                        "options" => array(
                                            "columnDefs" => array(
                                                array(
                                                    "className" => "dt-right", 
                                                    "targets" => [4],
                                                ),
                                            ),
                                            "order" => [],
                                            "paging" => true,
                                            "searching" => true,
                                            "colReorder" => true,
                                            "select" => true,
                                            "dom" => 'Blfrtip',
                                            "buttons" => array(
                                                array(
                                                    "extend" => 'collection',
                                                    "text" => 'Export',
                                                    "buttons" => [
                                                        'copy', 'excel', 'csv', 'pdf', 'print'
                                                    ],
                                                ),
                                            ),
                                        ),
                                    ));
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascripts')
<script>
    $(document).ready(function() {
        $('.date').datepicker({
            dateFormat: 'dd-mm-yy'
        });
    });
</script>
@endsection
