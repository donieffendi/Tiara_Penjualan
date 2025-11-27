@extends('layouts.main')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
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
                <!-- Tabs -->
                <ul class="nav nav-tabs">
                    <li class="nav-item active">
                        <a class="nav-link active" href="#truck" id="truck_tab" data-toggle="tab">Beli</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#driver" id="driver_tab" data-toggle="tab">Jual</a>
                    </li>
                </ul>
                {{-- <div class="card">
                    <div class="card-body">
                    
                    </div>
                </div> --}}
            </div>

            <div class="tab-content mt-3">
                <!-- Tab Truck -->
                <div id="truck" class="tab-pane active">
                    <div class="form-group row">
                        <!-- Bagian Kiri -->
                            <div class="col-md-6">
                                <!-- Chart Presentase Jumlah Ban Truck -->
                                <div class="card card-success">
                                    <div class="card-header">
                                        <h3 class="card-title">Presentase Pembelian Total</h3>
                        
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
                                            <canvas id="banChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chart Dummy 1 Row 2 -->
                                <div class="card card-success">
                                    <div class="card-header">
                                        <h3 class="card-title">Jumlah Dummy 1 Row 2</h3>

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
                                            <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <!-- Bagian Kanan -->
                            <div class="col-md-6">
                                <!-- Chart Presentase Jenis Truck -->
                                <div class="card card-danger">
                                    <div class="card-header">
                                        <h3 class="card-title">Presentase Jumlah Jenis Truck</h3>
                        
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
                                            <canvas id="jenisChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chart Chart Dummy 2 Row 2 -->
                                <div class="card card-danger">
                                    <div class="card-header">
                                        <h3 class="card-title">Chart Dummy 2 Row 2</h3>
                        
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
                                            <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>

                <!-- Tab Driver -->
                <div id="driver" class="tab-pane">
                    <div class="form-group row">
                        <!-- Bagian Kiri -->
                            <div class="col-md-6">
                                <!-- CHART JUMLAH TRUCK -->
                                <div class="card card-success">
                                    <div class="card-header">
                                        <h3 class="card-title">Jumlah Pendaftar</h3>
                        
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
                                            <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chart Ban Truck -->
                                <div class="card card-success">
                                    <div class="card-header">
                                        <h3 class="card-title">Jumlah Pendaftar</h3>
                        
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
                                            <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <!-- Bagian Kanan -->
                            <div class="col-md-6">
                                <!-- Chart Truck -->
                                <div class="card card-danger">
                                    <div class="card-header">
                                        <h3 class="card-title">Jumlah Pendaftar</h3>
                        
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
                                            <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chart Truck2 -->
                                <div class="card card-danger">
                                    <div class="card-header">
                                        <h3 class="card-title">Jumlah Pendaftar</h3>
                        
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
                                            <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
@endsection

@section('javascripts')
<script>
    $(function () {
        // Pie Chart
            // Pie Chart Options
            var pieChartOptions = {
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
            }
        // Chart Presentase Jumlah Ban Truck
            var ban7    = parseInt('{{$btotal["ban7"]}}');
            var ban9    = parseInt('{{$btotal["ban9"]}}');
            var ban11   = parseInt('{{$btotal["ban11"]}}');

            // Canvas
            var banChartCanvas = $('#banChart').get(0).getContext('2d');

            // Declare Chart Presentase Jumlah Ban
            new Chart(banChartCanvas, {
                type: 'pie',
                data: {
                    labels: ['Ban 7', 'Ban 9', 'Ban 11'],
                    datasets: [{
                        label: 'Data Jumlah Ban',
                        data: [
                            ban7, ban9, ban11
                        ],
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)'
                        ],
                        hoverOffset: 4
                    }]
                },
                options: pieChartOptions,
                plugins: [ChartDataLabels],
            });

//-----------------------------------------------------------------------------------------------------------------
        // Chart Presentase Jenis Truck
            var engkel      = parseInt('{{$jenis["engkel"]}}');
            var tronton     = parseInt('{{$jenis["tronton"]}}');
            var tractor     = parseInt('{{$jenis["tractor"]}}');

            // Canvas
            var jenisChartCanvas = $('#jenisChart').get(0).getContext('2d');


            // Declare Chart Presentase Jumlah Ban
            new Chart(jenisChartCanvas, {
                type: 'pie',
                data: {
                    labels: ['Engkel', 'Tractor Head', 'Tronton'],
                    datasets: [{
                        label: 'Data Jenis Truck',
                        data: [
                            engkel, tractor, tronton
                        ],
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)'
                        ],
                        hoverOffset: 4
                    }]
                },
                options: pieChartOptions,
                plugins: [ChartDataLabels],
            });
    });
</script>
@endsection

