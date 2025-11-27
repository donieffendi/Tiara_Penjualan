@extends('layouts.plain')
<style>
    .content-wrapper {
        min-height: 1000px !important;
        height: auto !important;
    }

    .chart-container {
        padding: 20px;
    }

    .chart-card {
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        padding: 15px;
        background-color: #fff;
        border-radius: 10px;
        text-align: center;
    }

    .chart-header {
        margin-bottom: 15px;
    }

    canvas {
        max-height: 300px;
    }
</style>

@section('content')
    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item active">Home</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row chart-container">
                    <!-- Bar Chart for Beli -->
                    <div class="col-lg-6 col-md-12">
                        <div class="chart-card">
                            <h5 class="chart-header">Bar Chart - Beli</h5>
                            <label for="chartSelectorBeli">Select Data:</label>
                            <select class="form-select mb-3" id="chartSelectorBeli">
                                <option value="total">Total Amount</option>
                                <option value="quantity">Quantity</option>
                            </select>
                            <canvas id="barChartBeli"></canvas>
                        </div>
                    </div>

                    <!-- Bar Chart for Jual -->
                    <div class="col-lg-6 col-md-12">
                        <div class="chart-card">
                            <h5 class="chart-header">Bar Chart - Jual</h5>
                            <label for="chartSelectorJual">Select Data:</label>
                            <select class="form-select mb-3" id="chartSelectorJual">
                                <option value="total">Total Amount</option>
                                <option value="quantity">Quantity</option>
                            </select>
                            <canvas id="barChartJual"></canvas>
                        </div>
                    </div>

                    <!-- Pie Chart for Beli -->
                    <div class="col-lg-6 col-md-12 mt-4">
                        <div class="chart-card">
                            <h5 class="chart-header">Pie Chart - Beli</h5>
                            <canvas id="pieChartBeli"></canvas>
                        </div>
                    </div>

                    <!-- Pie Chart for Jual -->
                    <div class="col-lg-6 col-md-12 mt-4">
                        <div class="chart-card">
                            <h5 class="chart-header">Pie Chart - Jual</h5>
                            <canvas id="pieChartJual"></canvas>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <!-- Piutang Table -->
                    <div class="col-12 mt-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Tabel Piutang Jatuh Tempo</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped table-bordered table-hover nowrap datatable"
                                    id="datatable-piutang">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col" style="text-align: center">No</th>
                                            <th scope="col" style="text-align: center">No Bukti</th>
                                            <th scope="col" style="text-align: center">Nama</th>
                                            <th scope="col" style="text-align: center">Sisa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($piutang as $index => $item)
                                            <tr>
                                                <td style="text-align: center">{{ $index + 1 }}</td>
                                                <td style="text-align: center">{{ $item->NO_BUKTI }}</td>
                                                <td style="text-align: center">{{ $item->NAMAS }}</td>
                                                <td style="text-align: right">{{ number_format($item->SISA, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Beli Table -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Table Beli Jatuh Tempo</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped table-bordered table-hover nowrap datatable"
                                    id="datatable-beli">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col" style="text-align: center">No</th>
                                            <th scope="col" style="text-align: center">No Bukti</th>
                                            <th scope="col" style="text-align: center">Nama</th>
                                            <th scope="col" style="text-align: center">Sisa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($beli as $index => $item)
                                            <tr>
                                                <td style="text-align: center">{{ $index + 1 }}</td>
                                                <td style="text-align: center">{{ $item->NO_BUKTI }}</td>
                                                <td style="text-align: center">{{ $item->NAMAS }}</td>
                                                <td style="text-align: right">{{ number_format($item->SISA, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Saldo Table -->
                    <div class="col-12 mt-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Tabel Saldo Kas Bank</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped table-bordered table-hover nowrap datatable"
                                    id="datatable-saldo">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col" style="text-align: center">No</th>
                                            <th scope="col" style="text-align: center">No Bukti</th>
                                            <th scope="col" style="text-align: center">Nama</th>
                                            <th scope="col" style="text-align: center">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($saldo as $index => $item)
                                            <tr>
                                                <td style="text-align: center">{{ $index + 1 }}</td>
                                                <td style="text-align: center">{{ $item->NO_BUKTI }}</td>
                                                <td style="text-align: center">{{ $item->NAMAS }}</td>
                                                <td style="text-align: right">{{ number_format($item->SALDO, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data from server
        const bar_beli_total = @json($bar_beli_total);
        const bar_beli_qty = @json($bar_beli_qty);
        const pie_beli = @json($pie_beli);

        const bar_jual_total = @json($bar_jual_total);
        const bar_jual_qty = @json($bar_jual_qty);
        const pie_jual = @json($pie_jual);

        // Bar Chart Data Extraction - Beli
        const monthsBeli = bar_beli_total.map(data => `${data["month"]}`);
        const totalDataBeli = bar_beli_total.map(data => parseFloat(data["SUM(TOTAL)"]));
        const quantityDataBeli = bar_beli_qty.map(data => parseFloat(data["SUM(TOTAL)"]));

        // Bar Chart Data Extraction - Jual
        const monthsJual = bar_jual_total.map(data => `${data["month"]}`);
        const totalDataJual = bar_jual_total.map(data => parseFloat(data["SUM(TOTAL)"]));
        const quantityDataJual = bar_jual_qty.map(data => parseFloat(data["SUM(TOTAL)"]));

        // Bar Chart for Beli
        const ctxBarBeli = document.getElementById('barChartBeli').getContext('2d');
        let barChartBeli = new Chart(ctxBarBeli, {
            type: 'bar',
            data: {
                labels: monthsBeli,
                datasets: [{
                    label: 'Total Amount',
                    data: totalDataBeli,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Dropdown to Update Bar Chart (Beli)
        document.getElementById('chartSelectorBeli').addEventListener('change', function() {
            const selectedValue = this.value;
            barChartBeli.data.datasets[0].data = selectedValue === 'total' ? totalDataBeli : quantityDataBeli;
            barChartBeli.data.datasets[0].label = selectedValue === 'total' ? 'Total Amount' : 'Quantity';
            barChartBeli.update();
        });

        // Bar Chart for Jual
        const ctxBarJual = document.getElementById('barChartJual').getContext('2d');
        let barChartJual = new Chart(ctxBarJual, {
            type: 'bar',
            data: {
                labels: monthsJual,
                datasets: [{
                    label: 'Total Amount',
                    data: totalDataJual,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Dropdown to Update Bar Chart (Jual)
        document.getElementById('chartSelectorJual').addEventListener('change', function() {
            const selectedValue = this.value;
            barChartJual.data.datasets[0].data = selectedValue === 'total' ? totalDataJual : quantityDataJual;
            barChartJual.data.datasets[0].label = selectedValue === 'total' ? 'Total Amount' : 'Quantity';
            barChartJual.update();
        });

        // Pie Chart for Beli
        const labelsPieBeli = pie_beli.map(data => data.namas);
        const valuesPieBeli = pie_beli.map(data => parseFloat(data["sum(total)"]));

        const ctxPieBeli = document.getElementById('pieChartBeli').getContext('2d');
        new Chart(ctxPieBeli, {
            type: 'pie',
            data: {
                labels: labelsPieBeli,
                datasets: [{
                    data: valuesPieBeli,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        align: 'end',
                        labels: {
                            boxWidth: 10
                        }
                    }
                }
            }
        });

        // Pie Chart for Jual
        const labelsPieJual = pie_jual.map(data => data.namac);
        const valuesPieJual = pie_jual.map(data => parseFloat(data["sum(total)"]));

        const ctxPieJual = document.getElementById('pieChartJual').getContext('2d');
        new Chart(ctxPieJual, {
            type: 'pie',
            data: {
                labels: labelsPieJual,
                datasets: [{
                    data: valuesPieJual,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 205, 86, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 205, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        align: 'end',
                        labels: {
                            boxWidth: 10
                        }
                    }
                }
            }
        });
    </script>

    <!-- Include DataTables JavaScript -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Beli Table
            $('#datatable-beli').DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: false,
                paging: true,
                searching: true,
                info: true
            });

            // Initialize Piutang Table
            $('#datatable-piutang').DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: false,
                paging: true,
                searching: true,
                info: true
            });

            // Initialize Saldo Table
            $('#datatable-saldo').DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: false,
                paging: true,
                searching: true,
                info: true
            });
        });
    </script>
@endsection
