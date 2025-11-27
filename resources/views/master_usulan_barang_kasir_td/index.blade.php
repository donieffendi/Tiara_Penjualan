@extends('layouts.plain')
@section('styles')
<link rel="stylesheet" href="{{url('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
@endsection

<style>  
    th { font-size: 13px; }
    td { font-size: 13px; }

    .badge-warning {
        background-color: #06ba00 !important; /* Warna default badge-warning (kuning) */
        color: white !important; /* Warna teks putih */
    }
</style>

@section('content')
<div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
		        <h1 class="m-0">Master Usulan Barang Kasir Td</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Master Usulan Barang Kasir Td</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Status -->
    @if (session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>
    @endif

    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">

                <!-- CEK -->
                <div class="form-group row" style="padding-left:30px">
                  <div class="col-md-1">
                      <input type="checkbox" id="checkAll" class="form-check-input">
                      <label for="checkAll">Check</label>
                  </div>
                  <div class="col-md-1">
                      <input type="checkbox" id="uncheckAll" class="form-check-input">
                      <label for="uncheckAll">Uncheck</label>
                  </div>
                </div>
                <!--  -->

                  <!-- Filter Sub -->
                <div class="form-group row" style="padding-left:20px">
                  <label><strong>Sub Item:</strong></label>
                    <div class="col-md-2">
                      <input class="form-control SUB" id="SUB" name="SUB"
                            type="text" autocomplete="off" value="{{ session()->get('SUB') }}"> 
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-warning" id="btnFilterSub" style="white-space: nowrap;">
                            Tampilkan
                        </button>

                        <button type="button" class="btn btn-danger" id="btnProses" style="white-space: nowrap;">
                            Prosses
                        </button>
                    </div>
                </div>
                <!--  -->
                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
											
                            <th scope="col" style="text-align: center">No</th>					
                            <th scope="col" style="text-align: center">Kode</th>
                            <th scope="col" style="text-align: center">Nama</th>
                            <th scope="col" style="text-align: center">Kemasan</th>
                            <th scope="col" style="text-align: center">HB</th>
							              <th scope="col" style="text-align: center">Cek</th>
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
            searching: true,
            autoWidth: false,
            paging: false,
            'scrollX': true,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax: 
            {
                url: '{{ route('get-usl-brg-td') }}',
                data: function (d) {
                    d.sub = $('#SUB').val();
                }
            },
            columns: 
            [
                {data: 'DT_RowIndex', orderable: false, searchable: false },
				        {data: 'KD_BRG', name: 'KD_BRG'},
                {data: 'NA_BRG', name: 'NA_BRG',
                  render : function ( data, type, row, meta )
                    {
                        return ' <h5><span class="badge badge-pill badge-warning">' + data + '</span></h5>';
                    }
                },	
                {data: 'KET_KEM', name: 'KET_KEM'},
                {data: 'HB', name: 'HB', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},			
				        { data: 'JTD', name: 'JTD',
                  render : function(data, type, row, meta) {
                    if(row['JTD']=="0"){
                        return '<input type="checkbox" style="transform: scale(2);">';
                    }else{
                        return '<input type="checkbox" checked style="transform: scale(2);">';
                    }
                  }
                },
            ],

            columnDefs: [
                {
                    "className": "dt-center", 
                    "targets": [0,1,2,3,5]
                },
                {
                    "className": "dt-right", 
                    "targets": 4
                }
            ],

            dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
				    
            stateSave:true,

        });

        $('#SUB').on('keydown', function (e) {
          if (e.key === 'Enter') {
              e.preventDefault();
              $('#btnFilterSub').click();
          }
        });
        
        // === CHECK ALL ===
        $('#checkAll').on('change', function() {
            const checked = $(this).is(':checked');
            // centang semua checkbox di dalam tabel
            $('#datatable').find('input[type="checkbox"]').prop('checked', checked);
        });

        // === UNCHECK ALL ===
        $('#uncheckAll').on('change', function() {
            const unchecked = $(this).is(':checked');
            if (unchecked) {
                $('#datatable').find('input[type="checkbox"]').prop('checked', false);
                // reset juga checkbox checkAll agar tidak nyala
                $('#checkAll').prop('checked', false);
            }
        });

        // Jika kamu ingin check/uncheck tetap sinkron saat user klik manual di tabel
        $('#datatable').on('change', 'input[type="checkbox"]', function() {
            const total = $('#datatable input[type="checkbox"]').length;
            const checked = $('#datatable input[type="checkbox"]:checked').length;
            // Jika semua checkbox di tabel tercentang, maka checkAll ikut nyala
            $('#checkAll').prop('checked', total === checked);
        });

        // Trigger reload saat nilai filter berubah
        $('#btnFilterSub').on('click', function() {
            $('#datatable').DataTable().ajax.reload();
        });

        // Proses
        $("#btnProses").on("click", function () {
          let dataToSend = [];
          $('#datatable').find('tbody tr').each(function() {
              let rowData = dataTable.row(this).data();
              let jtd = $(this).find('input[type="checkbox"]').is(':checked') ? 1 : 0;
              dataToSend.push({
                  KD_BRG: rowData.KD_BRG,
                  JTD: jtd
              });
          });

          $.ajax({
              url: '{{ route('uslBrgTd-proses') }}',   
              type: "POST",
              data: {
                  _token: '{{ csrf_token() }}', 
                  items: dataToSend,  
              },
              beforeSend: function () {
                  $("#btnProses").prop("disabled", true).text("Processing...");
              },
              success: function (res) {
                  alert(res.message);
                  $("#btnProses").prop("disabled", false).text("Proses");
                  $('#datatable').DataTable().ajax.reload();
              },
              error: function (xhr) {
                  console.error(xhr.responseText);
                  alert("Proses gagal!");
                  $("#btnProses").prop("disabled", false).text("Proses");
              },
          });
        });

  });
	
</script>
@endsection