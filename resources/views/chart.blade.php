@extends('layouts.main')
<style>
    .tab-content > .tab-pane:not(.active),
    .pill-content > .pill-pane:not(.active) {
        display: block;
        height: 0;
        overflow-y: hidden;
    } 
</style>

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Status -->
    @if (session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>
    @endif

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <ul class="nav nav-tabs">
                    <li class="nav-item active">
                        <a class="nav-link active" href="#beli" id="beli_tab" data-toggle="tab">Pembelian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#jual" id="jual_tab" data-toggle="tab">Penjualan</a>
                    </li>
                </ul>
                {{-- <div class="card">
                    <div class="card-body">
                    
                    </div>
                </div> --}}
            </div>

            <div class="tab-content mt-3">
                <div id="beli" class="tab-pane active">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title">Total Kg</h3>
                    
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart">
                                        <canvas id="beliKgChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-danger">
                                <div class="card-header">
                                    <h3 class="card-title">Total Rp</h3>
                    
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart">
                                        <canvas id="beliRpChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <!-- PASTE DIBAWAH INI -->
                        <!-- DISINI BATAS AWAL KOOLREPORT-->
                        <div class="report-content col-md-12">
                            <legend class="font-weight-bold">Invoice Beli belum terbayar > 30 hari</legend>
                            <?php
                            use \koolreport\datagrid\DataTables;

                            if($hasilBeli)
                            {
                                DataTables::create(array(
                                    "dataSource" => $hasilBeli,
                                    "name" => "exampleBeli",
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
                                        "NAMAS" => array(
                                            "label" => "Suplier",
                                        ),
                                        "HARI" => array(
                                            "label" => "Hari",
                                            "footerText" => "<b>Grand Total :</b>",
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
                                                "targets" => [4,5],
                                            ),
                                        ),
                                        "order" => [],
                                        "paging" => true,
                                        // "pageLength" => 12,
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

                <div id="jual" class="tab-pane">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title">Total Kg</h3>
                    
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart">
                                        <canvas id="jualKgChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-danger">
                                <div class="card-header">
                                    <h3 class="card-title">Total Rp</h3>
                    
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart">
                                        <canvas id="jualRpChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <!-- PASTE DIBAWAH INI -->
                        <!-- DISINI BATAS AWAL KOOLREPORT-->
                        <div class="report-content col-md-12">
                            <legend class="font-weight-bold">Invoice Jual belum terbayar > 30 hari</legend>
                            <?php
                            // use \koolreport\datagrid\DataTables;

                            if($hasilJual)
                            {
                                DataTables::create(array(
                                    "dataSource" => $hasilJual,
                                    "name" => "exampleJual",
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
                                        "NAMAC" => array(
                                            "label" => "Customer",
                                        ),
                                        "HARI" => array(
                                            "label" => "Hari",
                                            "footerText" => "<b>Grand Total :</b>",
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
                                                "targets" => [4,5],
                                            ),
                                        ),
                                        "order" => [],
                                        "paging" => true,
                                        // "pageLength" => 12,
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
@endsection

@section('javascripts')
<script>
    $(function () {
            var belikgCanvas = $('#beliKgChart').get(0).getContext('2d');
            var jualkgCanvas = $('#jualKgChart').get(0).getContext('2d');
            var kgChartOptions = {
                responsive              : true,
                maintainAspectRatio     : false,
                tooltips: {
                    enabled: false
                },
                plugins: {
                    datalabels: {
                        // display: false,
                        color: '#000000',
                        formatter: function(value, context) {
                            return Intl.NumberFormat('en-US').format(value);
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            // color: "#ccc",
                            // borderDash: [20, 4],
                            // borderColor: "black",
                            // tickColor: "black"
                        },
                        // title: {
                        //     display: true,
                        //     text: "Periode",
                        //     color: "green",
                        //     font: {
                        //     size: 14,
                        //     weight: "bold"
                        //     }
                        // }
                    },
                    y: {
                        // grid: {
                        //     color: "#ccc",
                        //     borderDash: [20, 4],
                        //     borderColor: "black",
                        //     tickColor: "black"
                        // },
                        title: {
                            display: true,
                            text: "Berat (kg)",
                            color: "green",
                            font: {
                            size: 14,
                            weight: "bold"
                            }
                        },
                        ticks: {
                            callback: (value, index, values) => {
                                return Intl.NumberFormat('en-US').format(value);
                            }
                        }
                    }
                },
            }

            var dataBeliKg = @json($belikg);
            var dataJualKg = @json($jualkg);
            new Chart(belikgCanvas, {
                type: 'bar',
                data: {
                    labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label : 'Tahun 2023',
                        data: dataBeliKg,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: kgChartOptions,
                plugins: [ChartDataLabels],
            })
            
            new Chart(jualkgCanvas, {
                type: 'bar',
                data: {
                    labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label : 'Tahun 2023',
                        data: dataJualKg,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: kgChartOptions,
                plugins: [ChartDataLabels],
            })


            var belirpCanvas = $('#beliRpChart').get(0).getContext('2d');
            var jualrpCanvas = $('#jualRpChart').get(0).getContext('2d');
            var rpOptions  = {
                maintainAspectRatio : false,
                responsive : true,
                tooltips: {
                    enabled: false
                },
                plugins: {
                    datalabels: {
                        formatter: (value, ctx) => {
                            let sum = 0;
                            let dataArr = ctx.chart.data.datasets[0].data;
                            dataArr.map(data => {
                                sum += data;
                            });
                            let percentage = (value*100 / sum).toFixed(2)+"%";
                            return percentage;
                        },
                        color: '#fff',
                    },
                }
            };

            var databeliRp = @json($belirp);
            var datajualRp = @json($jualrp);
            new Chart(belirpCanvas, {
                    type: 'pie',
                    data: {
                        labels: ['Jan', 'Feb','Mar','Apr','May','Jun','Jul','Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [{
                            label: 'Tahun 2023',
                            data: databeliRp,
                            backgroundColor: [
                                'rgb(255, 99, 132)',
                                'rgb(54, 162, 235)',
                                'rgb(98, 246, 157)',
                                'rgb(255, 249, 75)',
                                'rgb(237, 75, 255)',
                                'rgb(98, 255, 255)',

                                'rgb(47, 6, 253)',
                                'rgb(216, 218, 184)',
                                'rgb(141, 183, 0)',
                                'rgb(74, 5, 154)',
                                'rgb(255, 152, 16)',
                                'rgb(14, 50, 30)',
                            ],
                            hoverOffset: 4
                        }]
                    },
                    options: rpOptions,
                    plugins: [ChartDataLabels],
                });
                new Chart(jualrpCanvas, {
                    type: 'pie',
                    data: {
                        labels: ['Jan', 'Feb','Mar','Apr','May','Jun','Jul','Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [{
                            label: 'Tahun 2023',
                            data: datajualRp,
                            backgroundColor: [
                                'rgb(255, 99, 132)',
                                'rgb(54, 162, 235)',
                                'rgb(98, 246, 157)',
                                'rgb(255, 249, 75)',
                                'rgb(237, 75, 255)',
                                'rgb(98, 255, 255)',

                                'rgb(47, 6, 253)',
                                'rgb(216, 218, 184)',
                                'rgb(141, 183, 0)',
                                'rgb(74, 5, 154)',
                                'rgb(255, 152, 16)',
                                'rgb(14, 50, 30)',
                            ],
                            hoverOffset: 4
                        }]
                    },
                    options: rpOptions,
                    plugins: [ChartDataLabels],
                });
    });
</script>
@endsection