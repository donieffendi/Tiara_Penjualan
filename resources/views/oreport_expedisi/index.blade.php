@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Laporan Expedisi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">Laporan Expedisi</li>
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
                    <form method="POST" action="{{url('jasper-expedisi')}}">
                    @csrf
                    <div class="form-group row">
                        <div class="col-md-2" align="left"><strong style="font-size: 16px;">Suplier :</strong></div> 
                        <div class="col-md-2">
                            <input type="text" class="form-control kodes1" id="kodes1" name="kodes1" placeholder="Pilih Suplier# 1" value="{{ session()->get('filter_kodes1') }}" readonly>
                        </div>  
                        <div class="col-md-3">
                            <input type="text" class="form-control namas1" id="namas1" name="namas1" placeholder="Nama" value="{{ session()->get('filter_namas1') }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-2" align="left"><strong style="font-size: 16px;">s/d</strong></div> 
                        <div class="col-md-2">
                            <input type="text" class="form-control kodes2" id="kodes2" name="kodes2" placeholder="Pilih Suplier# 2" value="{{ session()->get('filter_kodes2') }}" readonly>
                        </div>  
                        <div class="col-md-3">
                            <input type="text" class="form-control namas2" id="namas2" name="namas2" placeholder="Nama" value="{{ session()->get('filter_namas2') }}" readonly>
                        </div>
                    </div>
                    
                    <!-- Filter Tanggal -->
                    <div class="form-group row">
                        <div class="col-md-2" align="left"><strong style="font-size: 16px;">Tanggal</strong></div> 
                        <div class="col-md-2">
                            <input class="form-control date tglDr" id="tglDr" name="tglDr"
                            type="text" autocomplete="off" value="{{ session()->get('filter_tglDari') }}"> 
                        </div>
                        <div>s.d.</div> 
                        <div class="col-md-2">
                            <input class="form-control date tglSmp" id="tglSmp" name="tglSmp"
                            type="text" autocomplete="off" value="{{ session()->get('filter_tglSampai') }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-2" align="left"><strong style="font-size: 16px;">Jenis Truk</strong></div> 
                        <div class="col-md-2">
                                <select id="milik" class="form-control" name="milik">
                                    <option value="" {{ (session()->get('filter_milik') == '') ? 'selected' : '' }}>Semua</option>
                                    <option value="GUDANG" {{ (session()->get('filter_milik') == 'GUDANG') ? 'selected' : '' }}>Gudang</option>
                                    <option value="NON-GUDANG" {{ (session()->get('filter_milik') == 'NON-GUDANG') ? 'selected' : '' }}>Non-Gudang</option>
                                </select>
                        </div>

                        <div class="col-md-1" align="right"><strong style="font-size: 16px;">Bayar</strong></div> 
                        <div class="col-md-2">
                                <select id="bayar" class="form-control" name="bayar">
                                    <option value="" {{ (session()->get('filter_bayar') == '') ? 'selected' : '' }}>Semua</option>
                                    <option value="Y" {{ (session()->get('filter_bayar') == 'Y') ? 'selected' : '' }}>Sudah</option>
                                    <option value="T" {{ (session()->get('filter_bayar') == 'T') ? 'selected' : '' }}>Belum</option>
                                </select>
                        </div>
                    </div>
                    
                    <button class="btn btn-primary" type="submit" id="filter" class="filter" name="filter">Filter</button>
                    <button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rexpedisi")}}'">Reset</button>
                    <button class="btn btn-warning" type="submit" id="cetak" class="cetak" formtarget="_blank">Cetak</button>
                    </form>
                    <div style="margin-bottom: 15px;"></div>
                    
                    <!-- PASTE DIBAWAH INI -->
                    <!-- DISINI BATAS AWAL KOOLREPORT-->
                    <div class="report-content" col-md-12>
                        <?php
                        use \koolreport\datagrid\DataTables;

                        if($hasil)
                        {
                            DataTables::create(array(
                                "dataSource" => $hasil,
                                "name" => "example",
                                "fastRender" => true,
                                "fixedHeader" => true,
                                'scrollX' => true,
                                "showFooter" => true,
                                "showFooter" => "bottom",
                                "columns" => array(
                                    "NO_BUKTI" => array(
                                        "label" => "Bukti#",
                                    ),
                                    "TGL" => array(
                                        "label" => "Tanggal",
                                    ),
                                    "TGL_MUAT" => array(
                                        "label" => "Tanggal Muat",
                                    ),
                                    "TGL_BONGKAR" => array(
                                        "label" => "Tanggal Bongkar",
                                    ),
                                    "KODES" => array(
                                        "label" => "Suplier#",
                                    ),
                                    "NAMAS" => array(
                                        "label" => "-",
                                    ),
                                    "TRUCK" => array(
                                        "label" => "Truk",
                                    ),
                                    "NAMAT" => array(
                                        "label" => "Tujuan",
                                    ),
                                    "NO_HUT" => array(
                                        "label" => "No Bayar",
                                        "footerText" => "<b>Grand Total :</b>",
                                    ),
                                    "KG" => array(
                                        "label" => "Kg",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
                                    "HARGA" => array(
                                        "label" => "Harga",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
                                    "B_INAP" => array(
                                        "label" => "Biaya Inap",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
                                    "B_MSOL" => array(
                                        "label" => "Biaya Solar",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
                                    "TOTAL" => array(
                                        "label" => "Total",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
                                    "BAYAR" => array(
                                        "label" => "Bayar",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
                                    "NOM_UM" => array(
                                        "label" => "U.Muka",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                        "footerText" => "<b>@value</b>",
                                    ),
                                ),
                                "cssClass" => array(
                                    "table" => "table table-hover table-striped table-bordered compact",
                                    "th" => "label-title",
                                    "td" => "detail",
                                    "tf" => "footerCss"
                                ),
                                "options" => array(
                                    "columnDefs"=>array(
                                        array(
                                            "className" => "dt-right", 
                                            "targets" => [9,10,11,12,13,14],
                                        ),
                                    ),
                                    "order" => [],
                                    "paging" => true,
                                    "pageLength" => 12,
                                    "searching" => true,
                                    "colReorder" => true,
                                    "select" => true,
                                    "dom" => 'Blfrtip', // B e dilangi
                                    // "dom" => '<"row"<col-md-6"B><"col-md-6"f>> <"row"<"col-md-12"t>><"row"<"col-md-12">>',
                                    "buttons" => array(
                                        array(
                                            "extend" => 'collection',
                                            "text" => 'Export',
                                            "buttons" => [
                                                'copy',
                                                'excel',
                                                'csv',
                                                'pdf',
                                                'print'
                                            ],
                                        ),
                                    ),
                                ),
                            ));
                        }
                        ?>
                    </div>
                    <!-- DISINI BATAS AKHIR KOOLREPORT-->
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
</div>

	<div class="modal fade" id="browseSupModal" tabindex="-1" role="dialog" aria-labelledby="browseSupModalLabel" aria-hidden="true">
	  	<div class="modal-dialog" role="document">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="browseSupModalLabel">Cari Suplier</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-stripped table-bordered" id="table-sup">
					<thead>
						<tr>
							<th>Suplier#</th>
							<th>Nama</th>
							<th>Alamat</th>
							<th>Kota</th>
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

@section('javascripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $('.date').datepicker({  
            dateFormat: 'dd-mm-yy'
        }); 
    });
        
    var dTableSup;
    loadDataSup = function(indeks){
    
        $.ajax(
        {
            type: 'GET', 		
            url: "{{url('sup/browse')}}",
            data: {
                'GOL': 'Z',
            },
            success: function(resp)
            {
                if(dTableSup){
                    dTableSup.clear();
                }
                for(i=0; i<resp.length; i++){
                    
                    dTableSup.row.add([
                        '<a href="javascript:void(0);" onclick="chooseSup(\''+resp[i].KODES+'\',  \''+resp[i].NAMAS+'\', \''+indeks+'\')">'+resp[i].KODES+'</a>',
                        resp[i].NAMAS,
                        resp[i].ALAMAT,
                        resp[i].KOTA,
                    ]);
                }
                dTableSup.draw();
            }
        });
    }
    
    dTableSup = $("#table-sup").DataTable({
        
    });
    
    browseSup = function(indeks){
        loadDataSup(indeks);
        $("#browseSupModal").modal("show");
    }
    
    chooseSup = function(KODES, NAMAS, indeks){
        $("#kodes"+indeks).val(KODES);
        $("#namas"+indeks).val(NAMAS);	
        $("#browseSupModal").modal("hide");
    }
    
    $("#kodes1").keypress(function(e){
        if(e.keyCode == 46){
            e.preventDefault();
            browseSup(1);
        }
    });

    $("#kodes2").keypress(function(e){
        if(e.keyCode == 46){
            e.preventDefault();
            browseSup(2);
        }
    });
</script>
@endsection