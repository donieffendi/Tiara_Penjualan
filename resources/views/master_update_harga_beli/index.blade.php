@extends('layouts.plain')
@section('styles')
    <link rel="stylesheet" href="{{ url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="{{ url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
@endsection

<style>
    th {
        font-size: 13px;
    }

    td {
        font-size: 13px;
    }
</style>

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Master Update Harga Beli</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item active">Master Update Harga Beli</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status -->
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <form
                                            action="{{url('/update-hrg-beli/store/')}}"
                                            method="POST" name ="entri" id="entri">
                                            @csrf
                                            <div class="col-md-2">
                                                <label for="cabang" class="mr-2">Cabang</label>
                                                <select id="CBG" class="form-control" name="CBG">
                                                    <option value="" disabled selected hidden>--Pilih Cabang--</option>

                                                    @foreach($CBG as $item)
                                                        <option value="{{ $item->KODE }}" 
                                                            {{ session('filter_cbg') == $item->KODE ? 'selected' : '' }}>
                                                            {{ $item->KODE }}
                                                        </option>
                                                    @endforeach
                                                </select> 
                                            </div>
                                            <div class="form-group mr-3">
                                                <div class="col-md-2">
                                                    <label for="KD_BRG" class="mr-2">Sub Item</label>
                                                    <input type="text" name="KD_BRG" id="KD_BRG" class="form-control"
                                                        value="">
                                                </div>
                                            </div>
                                            <div class="form-group mr-3">
                                                <div class="col-md-2">
                                                    <label for="HB_AWAL" class="mr-2">HB Awal</label>
                                                    <input type="text" name="HB_AWAL" id="HB_AWAL"
                                                        class="form-control text-right" value="">
                                                </div>
                                            </div>
                                            <div class="form-group mr-3">
                                                <div class="col-md-2">
                                                    <label for="edit_hb" class="mr-2">Edit HB</label>
                                                    <input type="text" name="HB_BARU" id="edit_hb"
                                                        class="form-control text-right" value="">
                                                </div>
                                            </div>
                                            <div class="form-group mr-3">
                                                <div class="col-md-2">
                                                    <button type="submit" id="btnProses" class="btn btn-dark">PROSES</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <table class="table table-fixed table-striped table-border table-hover nowrap datatable"
                                    id="datatable">
                                    <thead class="table-dark">
                                        <tr>

                                            <th scope="col" style="text-align: center">No</th>
                                            <th scope="col" style="text-align: center">-</th>
                                            <th scope="col" style="text-align: center">Kode</th>
                                            <th scope="col" style="text-align: center">Nama</th>
                                            <th scope="col" style="text-align: center">Ukuran</th>
                                            <th scope="col" style="text-align: center">Keterangan</th>
                                            <th scope="col" style="text-align: center">Cabang</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.card -->
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
        $(document).ready(function() {
            var dataTable = $('.datatable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: true,
                'scrollY': '400px',
                "order": [
                    [0, "asc"]
                ],
                ajax: {
                    url: '{{ route('get-update-hrg-beli') }}'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                    {
                        data: 'KD_BRG',
                        name: 'KD_BRG'
                    },
                    {
                        data: 'NA_BRG',
                        name: 'NA_BRG'
                    },
                    {
                        data: 'KET_UK',
                        name: 'KET_UK'
                    },
                    {
                        data: 'KET',
                        name: 'KET'
                    },
                    {
                        data: 'CBG',
                        name: 'CBG'
                    },
                ],

                columnDefs: [{
                    "className": "dt-center",
                    "targets": 0
                }],

                dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                    "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                    "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",

                stateSave: true,

            });

            // $("div.test_btn").html(
            //     '<a class="btn btn-lg btn-md btn-success" href="{{ url('sup/edit?idx=0&tipx=new') }}"> <i class="fas fa-plus fa-sm md-3" ></i></a'
            //     );
                  
            // --- Tambahkan script ambil harga awal ---
            $('#KD_BRG').on('blur', function() {
                let kode = $(this).val();

                if (kode !== '') {
                    $.ajax({
                        url: '{{ url("update-hrg-beli/getHargaAwal") }}',
                        type: 'GET',
                        data: { KD_BRG: kode },
                        success: function(response) {
                            let nilai = parseFloat(response.HB) || 0;

                            // Format jadi 15,500.00
                            let formatted = nilai.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });

                            $('#HB_AWAL').val(formatted);
                        },
                        error: function() {
                            $('#HB_AWAL').val('0');
                        }
                    });
                }
            });

            // Saat tekan ENTER di Sub Item, fokus ke Edit HB
            $('#KD_BRG').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $('#edit_hb').focus();
                }
            });
        });
    </script>
@endsection
