@extends('layouts.plain')
@section('styles')
<!-- <link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}"> -->
<link rel="stylesheet" href="{{asset('foxie_js_css/jquery.dataTables.min.css')}}" />

@endsection

<style>

    .card {
        padding: 5px 10px !important;
    }


    .table thead {
        background-color: #FFFFFF;
        color: #000000;
    }


    .datatable tbody td {
        padding: 5px !important;
        background-color: #FFFFFF;
    }

    .datatable {
        border-right: solid 2px #000;
        border-left: solid 2px #000;
    }
	
 
    .btn-secondary {
        background-color: #42047e !important;
    }
      
    th { font-size: 12px; }
    td { font-size: 12px; }

    /* menghilangkan padding */
    .content-header {
        padding: 0 !important;
    }

</style>


@section('content')
<!-- Sweetalert delete -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!--  -->
<div class="content-wrapper">


    <!-- Status -->
    @if (session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>

        <!-- tambahan notifikasinya untuk delete di index -->
        <script>
            Swal.fire({
					title: 'Deleted!',
					text: 'Data has been deleted. {{session('status')}}',
					icon: 'success',
					confirmButtonText: 'OK'
				})
        </script>
        <!-- tutupannya -->

    @endif

    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
              <form method="POST" id="entri" action="{{url('po/posting')}}">

              <input name="flagz"  class="form-control flagz" id="flagz" value="{{$flagz}}" hidden >
              <input name="golz"  class="form-control golz" id="golz" value="{{$golz}}" hidden >
 
                <!-- <button class="btn btn-danger" type="button"  onclick="simpan()">Posting</button> -->

                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable_index">
                    <thead class="table-dark">
                        <tr>
                            {{-- <th scope="col" style="text-align: center"></th> --}}
                            <th scope="col" style="text-align: center">Detail</th>
                            <th scope="col" style="text-align: center">V</th>	
                            <th scope="col" style="text-align: center">Suplier#</th>
                            <th scope="col" style="text-align: center">Nama</th>
                            <th scope="col" style="text-align: center">Total-Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($detail as $detail)
                            <tr data-child-name="{{$detail->NAMAS}}" data-child-value="{{$detail->KODES}}">
                                <td class="dt-control">
                                    <input class="btn btn-primary btn-sm w-100" type="button" value="Detail">
                                </td>
                                <td><input type="checkbox"  class="form-control CETAK"></td>
                                <td>{{$detail->KODES}}</td>
                                <td>{{$detail->NAMAS}}</td>
                                <td>{{$detail->TOTAL_QTY}}</td>
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
  
@endsection

@section('javascripts')

<!-- filter kolom di index -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- batas filter  -->

<script>
  $(document).ready(function() {
        var table = $('#datatable_index').DataTable({
            dom: "<'row'<'col-md-6'B><'col-md-6'>>" +
                 "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                 "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>", // DataTable layout
            buttons: [],
            order: [[1, "asc"]],
            colReorder: true
        });

        $('#datatable_index tbody').on('click', 'td.dt-control', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);

            if (row.child.isShown()) {
                console.log('1');
                row.child.hide();
                tr.removeClass('shown');
            } else {
                console.log('2');
                 // Tutup baris lain yang terbuka
                $('#datatable_index tbody tr.shown').each(function() {
                    var otherRow = table.row(this);
                    if (otherRow.child.isShown()) {
                        otherRow.child.hide();
                        $(this).removeClass('shown');
                    }
                });

                row.child(format(tr.data('child-value'),tr.data('child-name'))).show();
                tr.addClass('shown');
                detail(tr.data('child-name'), tr.data('child-value'));
            }
        });

        $("div.test_btn").html(
         '<a class="btn btn-lg btn-md btn-success" href="{{url('po/edit?flagz='.$flagz.'&golz='.$golz.'&idx=0&tipx=new')}}"> Proses All PO </a> '
        );
        // tutupanya
    });

    function format(KODES, NAMAS) {
        return `
            <div class="card" style="background: linear-gradient(145deg, #A8D0E6 0%, #f1f8fc 100%); margin-top: 15px; border-radius: 12px; padding: 25px; box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);">
                <h5 class="card-title" style="font-weight: bold; color: #333333;">Detail Bongkar #${NAMAS}</h5>
                <div class="row">
                    <!-- Tabel Detail PO -->
                    <div class="col-md-12">
                        <table id="detBeli" class="table table-striped table-bordered" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <thead class="thead-light">
                                <tr>
                                    <th>NO BUKTI</th>
                                    <th>KODE BARANG</th>
                                    <th>NAMA BARANG</th>
                                    <th>QTY</th>
                                </tr>
                            </thead>
                            <tbody id="Beli${KODES}"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }

    function detail(NAMAS, KODES) {
        $.ajax({
            type: 'get',
            url: "{{ route('po/otomatis/detailotomatis') }}",
            data: { KODES: KODES },
            dataType: 'json',
            success: function(response) {
                $('#detBeli').DataTable({
                    data: response,
                    columns: [
                        { data: "NO_BUKTI" },
                        { data: "KD_BRG" },
                        { data: "NA_BRG" },
                        { data: "QTY", render: $.fn.dataTable.render.number( ',', '.', 0, '' )},
                    ],
                    buttons: [],
                });
            },
            error: function() {
                $('#Beli' + KODES).html('');
            }
        });
    }

    function deleteRow(link) {
        console.log('Masuk');
        Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = link;
            }
        });
    }
	
	
	function simpan() {
    var check = '0';
    var min = '0';
		
	
	document.getElementById("entri").submit();

	}
	
	
	
	
</script>
@endsection
