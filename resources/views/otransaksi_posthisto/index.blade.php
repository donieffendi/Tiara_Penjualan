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
        background-color: #000000 !important;
    }
      
    th { font-size: 12px; }
    td { font-size: 12px; }

    /* menghilangkan padding */
    .content-header {
        padding: 0 !important;
    }

    .badge-warning {
        background-color: #d5014b !important; /* Warna default badge-warning (kuning) */
        color: white !important; /* Warna teks putih */
    }

    .badge-success {
        background-color: #068f3f !important; /* Warna default badge-warning (kuning) */
        color: white !important; /* Warna teks putih */
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
			  
              <form method="POST" id="entri" action="{{url('posthisto/posting')}}">
                <button class="btn btn-dark" type="button"  onclick="allin()">All In</button>
                <button class="btn btn-danger" type="button"  onclick="simpan()">Proses</button>

                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                   

                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="text-align: center">#</th>
                            <th scope="col" style="text-align: center">-</th>							
                            <th scope="col" style="text-align: center">No. Bukti</th>
                            <th scope="col" style="text-align: center">Tanggal</th>
                            <th scope="col" style="text-align: center">Jenis Perubahan</th>
                            <th scope="col" style="text-align: center">Cek</th>
                        </tr>
                    </thead>
    
                    <tbody>
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

        var dataTable = $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            paging: false,
            // 'scrollX': true,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax: 
            {
                url: "{{ route('get-posthisto') }}",
            },

            columns: 
            [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'action', name: 'action'},
                { data: 'NO_BUKTI', name: 'NO_BUKTI',
                  render : function ( data, type, row, meta )
                  {
                    return ' <h5><span class="badge badge-pill badge-warning">' + data + '</span></h5>';
                  }
                },
                { data: 'TGL', name: 'TGL'},	
                { data: 'STAT', name: 'STAT'},
                { data: 'cek', name: 'cek'},
            ],
            columnDefs: 
            [
                {
                    "className": "dt-center", 
                    "targets": [0,1,2,3,4,5]
                },			
                {
                  targets: 3,
                  render: $.fn.dataTable.render.moment( 'DD-MM-YYYY' )
                },
            ],
            lengthMenu: 
            [
                [8, 10, 20, 50, 100, -1],
                [8, 10, 20, 50, 100, "All"]
            ],
            dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
            stateSave: true,

        });
    });
	
	
	// === Tombol ALL IN (Checklist semua data) ===
    window.allin = function() {
        $('.cek').prop('checked', true);
    };

    // === Tombol PROSES (kirim data ke controller) ===
    window.simpan = function() {
        var ids = [];

        // ambil semua id yang dicentang
        $('.cek:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length === 0) {
            Swal.fire('Oops!', 'Tidak ada data yang dipilih.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Yakin?',
            text: "Data yang dicentang akan di-posting.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, proses!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('posthisto-posting') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        ids: ids
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Sukses!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload(); // ✅ reload page setelah klik OK
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.error || 'Terjadi kesalahan.', 'error');
                    }
                });
            }
        });
    };
	
	
	
	
</script>
@endsection
