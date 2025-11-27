@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Kartu Stok</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">Kartu Stok</li>
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
                    <form method="POST" action="{{url('jasper-stok-kartu')}}">
                    @csrf
                    <div class="form-group row">
                        <!--
                        <div class="col-md-3">
                            <label><strong>Barang :</strong></label>
                            
                            <select name="brg" id="brg" class="form-control brg" style="width: 200px">
                                <option value="{{ session()->get('filter_brg') }}">{{ session()->get('filter_brg') ? session()->get('filter_brg') : '--Pilih Barang--' }}</option>
                                @foreach($brg as $brgD)
                                    <option value="{{$brgD->KD_BRG}}">{{$brgD->KD_BRG}} {{$brgD->NA_BRG}}</option>
                                @endforeach
                            </select>
                        </div> -->
                        
                        <!-- <div class="col-md-1" align="right"><strong>Barang :</strong></div> 
                        <div class="col-md-2">
                            <input type="text" class="form-control brg1" id="brg1" name="brg1" placeholder="Pilih Barang# 1" value="{{ session()->get('filter_brg1') }}" readonly>
                        </div>  -->
                        <div class="col-md-1" align="right"><strong>Barang :</strong></div>
                        <div class="col-md-2">
                            <select class="form-control brg1" name="brg1" id="brg1" onchange="fillBrg(this.id)">
									<option value="{{ session()->get('filter_brg1') }}" {{ (session()->get('filter_brg1') != '') ? 'selected' : '' }}>{{ session()->get('filter_brg1') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control nabrg1" id="nabrg1" name="nabrg1" placeholder="Nama" value="{{ session()->get('filter_nabrg1') }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <!-- <div class="col-md-1" align="right"><strong>s/d</strong></div> 
                        <div class="col-md-2">
                            <input type="text" class="form-control brg2" id="brg2" name="brg2" placeholder="Pilih Barang# 2" value="{{ session()->get('filter_brg2') }}" readonly>
                        </div> --> 
                        <div class="col-md-1" align="right"><strong>s/d</strong></div>
                        <div class="col-md-2">
                            <select class="form-control brg2" name="brg2" id="brg2" onchange="fillBrg(this.id)">
									<option value="{{ session()->get('filter_brg2') }}" {{ (session()->get('filter_brg2') != '') ? 'selected' : '' }}>{{ session()->get('filter_brg2') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control nabrg2" id="nabrg2" name="nabrg2" placeholder="Nama" value="{{ session()->get('filter_nabrg2') }}" readonly>
                        </div>
                    </div>
                    
                    <!-- Filter Tanggal -->
                    <div class="form-group row">
                        <div class="col-md-3">
                            <input class="form-control date tglDr" id="tglDr" name="tglDr"
                            type="text" autocomplete="off" value="{{ session()->get('filter_tglDr')}}"> 
                        </div>
                        <div>s.d.</div> 
                        <div class="col-md-3">
                            <input class="form-control date tglSmp" id="tglSmp" name="tglSmp"
                            type="text" autocomplete="off" value="{{ session()->get('filter_tglSmp')}}">
                        </div>
                    </div>
                    
                    <button class="btn btn-primary" type="submit" id="filter" class="filter" name="filter">Filter</button>
                    <button class="btn btn-danger" type="button" id="resetfilter" class="resetfilter" onclick="window.location='{{url("rkarstk")}}'">Reset</button>
                    <button class="btn btn-warning" type="submit" id="cetak" class="cetak" formtarget="_blank">Cetak</button>
                    </form>
                    <div style="margin-bottom: 15px;"></div>
                    
                    <!-- PASTE DIBAWAH INI -->
                    <!-- DISINI BATAS AWAL KOOLREPORT-->
                    <div class="report-content">
                        <?php
                        use \koolreport\datagrid\DataTables;

                        if($hasil)
                        {
                            DataTables::create(array(
                                "dataSource" => $hasil,
                                "name" => "example",
                                "fastRender" => true,
                                "fixedHeader" => true,
                                "showFooter" => true,
                                "showFooter" => "bottom",
                                "columns" => array(
                                    "NO_BUKTI" => array(
                                        "label" => "Bukti"
                                    ),
                                    "TGL" => array(
                                        "label" => "Tgl",
                                        "type"=>"datetime",
                                        "format"=>"Y-m-d",
                                        "displayFormat"=>"d/m/Y" ,
                                    ),
                                    "KD_BRG" => array(
                                        "label" => "Barang#"
                                    ),
                                    "NA_BRG" => array(
                                        "label" => "-"
                                    ),
                                    "URAIAN" => array(
                                        "label" => "Uraian",
                                        "footerText"=>"Grand Total :",
                                    ),
                                    "AWAL" => array(
                                        "label" => "Awal",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                    ),
                                    "MASUK" => array(
                                        "label" => "Masuk",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                    ),
                                    "KELUAR" => array(
                                        "label" => "Keluar",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                    ),
                                    "LAIN" => array(
                                        "label" => "Lain",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
                                        "footer" => "sum",
                                    ),
                                    "SALDO" => array(
                                        "label" => "Saldo",
                                        "type" => "number",
                                        "decimals" => 2,
                                        "decimalPoint" => ".",
                                        "thousandSeparator" => ",",
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
                                            "targets" => [5,6,7,8],
                                        ),
                                    ),
                                    "order" => [],
                                    "paging" => true,
                                    "searching" => true,
                                    "colReorder" => true,
                                    "fixedHeader" => true,
                                    "select" => true,
                                    "showFooter" => true,
                                    "showFooter" => "bottom",
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
                                )
                            ));
                        }
                        ?>
                    </div>
                    <!-- DISINI BATAS AKHIR KOOLREPORT-->
                    <!--
                    <table class="table table-fixed table-striped table-border table-hover nowrap datatable">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" style="text-align: center">#</th>
                                <th scope="col" style="text-align: center">Bukti</th>
                                <th scope="col" style="text-align: center">Tgl</th>
                                <th scope="col" style="text-align: center">Barang#</th>
                                <th scope="col" style="text-align: center">-</th>
                                <th scope="col" style="text-align: center">Uraian</th>
                                <th scope="col" style="text-align: center">Awal</th>
                                <th scope="col" style="text-align: center">Masuk</th>
                                <th scope="col" style="text-align: center">Keluar</th>
                                <th scope="col" style="text-align: center">Saldo</th>

                            </tr>
                        </thead>
                        <tbody>
                        </tbody> 
                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th>Total</th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                    -->
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
</div>
	<div class="modal fade" id="browseBrgModal" tabindex="-1" role="dialog" aria-labelledby="browseBrgModalLabel" aria-hidden="true">
	  	<div class="modal-dialog" role="document">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="browseBrgModalLabel">Cari Barang</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-stripped table-bordered" id="table-brg">
					<thead>
						<tr>
							<th>Barang#</th>
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

@section('javascripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        select2_kd_brg();
		
        $('.date').datepicker({  
            dateFormat: 'dd-mm-yy'
        }); 
        
        /*
        function fill_datatable( brg = '', tglDr = '', tglSmp = '')
        {
            var dataTable = $('.datatable').DataTable({
                dom: '<"row"<"col-4"B>>fltip',
                lengthMenu: [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                processing: true,
                serverSide: true,
                autoWidth: true,
                //'scrollX': true,
                'scrollY': '400px',
                "order": [[ 0, "asc" ]],
                ajax: 
                {
                    url: "{{ url('get-stok-kartu') }}",
                    data: {
                        brg: brg,
                        tglDr: tglDr,
                        tglSmp: tglSmp
                    }
                },
                columns: 
                [
                    {data: 'DT_RowIndex', orderable: false, searchable: false },
                    {data: 'NO_BUKTI', name: 'NO_BUKTI'},
                    {data: 'TGL', name: 'TGL'},
                    {data: 'KD_BRG', name: 'KD_BRG'},
                    {data: 'NA_BRG', name: 'NA_BRG'},
                    {data: 'URAIAN', name: 'URAIAN'},
                    {
                     data: 'AWAL', 
                     name: 'AWAL',
                     render: $.fn.dataTable.render.number( ',', '.', 2, '' )
                    },	
                    {
                     data: 'MASUK', 
                     name: 'MASUK',
                     render: $.fn.dataTable.render.number( ',', '.', 2, '' )
                    },	
                    {
                     data: 'KELUAR', 
                     name: 'KELUAR',
                     render: $.fn.dataTable.render.number( ',', '.', 2, '' )
                    },	
                    {
                     data: 'SALDO', 
                     name: 'SALDO',
                     render: $.fn.dataTable.render.number( ',', '.', 2, '' )
                    },	
                 ],
                 
                 columnDefs: [
                  {
                    "className": "dt-center", 
                    "targets": 0
                  },
                  {
                    targets: 2,
                    render: $.fn.dataTable.render.moment( 'DD-MM-YYYY' )
                  },
                  {
                    "className": "dt-right", 
                    "targets": [6,7,8,9]
                  }
               
                 ],
                
                ///////////////////////////////////////////////////
                
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();
         
                    // Remove the formatting to get integer data for summation
                    var intVal = function (i) {
                        return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                    };
         
                    // Total over this page
                    pageDebetTotal = api
                        .column(7, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    pageKreditTotal = api
                        .column(8, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
         
                    // Update footer
                    $(api.column(7).footer()).html(pageDebetTotal.toLocaleString('en-US'));
                    $(api.column(8).footer()).html(pageKreditTotal.toLocaleString('en-US'));
                },
                
                
            });
        }
        
        $('#filter').click(function() {
            var brg = $('#brg').val();
            var tglDr = $('#tglDr').val();
            var tglSmp = $('#tglSmp').val();
            
            if (brg != '' || (tglDr != '' && tglSmp != ''))
            {
                $('.datatable').DataTable().destroy();
                fill_datatable(brg, tglDr, tglSmp);
            }
        });
        $('#resetfilter').click(function() {
            var brg = '';
            var tglDr = '';
            var tglSmp = '';

            $('.datatable').DataTable().destroy();
            fill_datatable(brg, tglDr, tglSmp);
        });
        */
    });
    
    var dTableBrg;
    loadDataBrg = function(indeks){
    
        $.ajax(
        {
            type: 'GET', 		
            url: "{{url('brg/browse')}}",
            data: {
                'GOL': 'Y',
            },
            success: function( response )
            {
                resp = response;
                if(dTableBrg){
                    dTableBrg.clear();
                }
                for(i=0; i<resp.length; i++){
                    
                    dTableBrg.row.add([
                        '<a href="javascript:void(0);" onclick="chooseBrg(\''+resp[i].KD_BRG+'\',  \''+resp[i].NA_BRG+'\', \''+indeks+'\')">'+resp[i].KD_BRG+'</a>',
                        resp[i].NA_BRG,
                        resp[i].SATUAN,
                    ]);
                }
                dTableBrg.draw();
            }
        });
    }
    
    dTableBrg = $("#table-brg").DataTable({
        
    });
    
    browseBrg = function(indeks){
        loadDataBrg(indeks);
        $("#browseBrgModal").modal("show");
    }
    
    chooseBrg = function(KD_BRG, NA_BRG, indeks){
        $("#brg"+indeks).val(KD_BRG);
        $("#nabrg"+indeks).val(NA_BRG);	
        $("#browseBrgModal").modal("hide");
    }
    /*
    $("#brg1").keypress(function(e){
        if(e.keyCode == 46){
            e.preventDefault();
            browseBrg(1);
        }
    });

    $("#brg2").keypress(function(e){
        if(e.keyCode == 46){
            e.preventDefault();
            browseBrg(2);
        }
    }); */
	
    function select2_kd_brg() {
        $('#brg1').select2({
            ajax: {
                url: "{{ url('brg/get-select-kdbrg') }}",
                dataType: "json",
                type: "GET",
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page
                    }
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: data.total_count
                        }
                    };
                },
                cache: true
            },
			allowClear: true,
            dropdownCssClass: "bigdrop",
            // dropdownAutoWidth: true,
            placeholder: 'Pilih Barang ...',
            minimumInputLength: 0,
            templateResult: format,
            templateSelection: formatSelection,
            theme: "classic",
        });
        
        $('#brg2').select2({
            ajax: {
                url: "{{ url('brg/get-select-kdbrg') }}",
                dataType: "json",
                type: "GET",
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page
                    }
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: data.total_count
                        }
                    };
                },
                cache: true
            },
			allowClear: true,
            dropdownCssClass: "bigdrop",
            // dropdownAutoWidth: true,
            placeholder: 'Pilih Barang ...',
            minimumInputLength: 0,
            templateResult: format,
            templateSelection: formatSelection,
            theme: "classic",
        });
    }

    function format(repo) {
        if (repo.loading) {
            return repo.text;
        }

        var $container = $(
            "<div class='select2-result-repository clearfix text_input'>" +
            "<div class='select2-result-repository__title text_input'></div>" +
            "</div>"
        );

        $container.find(".select2-result-repository__title").text(repo.text+' - '+repo.na_brg);
        return $container;
    }

	var kdbarang = '';
	var namabarang = '';

    function formatSelection(repo) {
        kdbarang = repo.id;
        namabarang = repo.na_brg;
        return repo.text;
    }
    
	function fillBrg(id) {
        $('#na'+id).val(namabarang);
        if(kdbarang=="{{ session()->get('filter_brg1') }}" && id=='brg1') 
        {
            $('#na'+id).val("{{ session()->get('filter_nabrg1') }}");
        }
        if(kdbarang=="{{ session()->get('filter_brg2') }}" && id=='brg2') 
        {
            $('#na'+id).val("{{ session()->get('filter_nabrg2') }}");
        }
	}
</script>
@endsection

