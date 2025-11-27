@extends('layouts.plain')
@section('styles')
<link rel="stylesheet" href="{{asset('foxie_js_css/jquery.dataTables.min.css')}}" />

@endsection

<style>
    th { font-size: 13px; }
    td { font-size: 13px; }

    /* menghilangkan padding */
    .content-header {
        padding: 0 !important;
    }

    .badge-warning {
        background-color: #06ba00 !important; /* Warna default badge-warning (kuning) */
        color: white !important; /* Warna teks putih */
    }
</style>

@section('content')

<!-- Sweetalert delete -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!--  -->

<div class="content-wrapper">

    <!-- Status -->
    <!-- @if (session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>

        <script>
            Swal.fire({
              title: 'Deleted!',
              text: 'Data has been deleted. {{session('status')}}',
              icon: 'success',
              confirmButtonText: 'OK'
            })
        </script>
    @endif -->
    <!-- tutupannya -->


    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">


                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="text-align: center">No.</th>
				     		<th scope="col" style="text-align: center">-</th>							
                            <th scope="col" style="text-align: center">Kata</th>
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
            'scrollX': true,
            'scrollY': '400px',
            "order": [[ 0, "asc" ]],
            ajax:
            {
                url: '{{ route('get-greet') }}',
            },
            columns:
            [
                {  data: 'DT_RowIndex', orderable: false, searchable: false },
                {   data: 'action', name: 'action' },
				{   data: 'KATA', name: 'KATA' }
            ],
            columnDefs: [
                {
                    "className": "dt-center",
                    "targets": [0,1,2]
                }
            ],
                dom: "<'row'<'col-md-6'><'col-md-6'>>" +
                     "<'row'<'col-md-2'l><'col-md-6 test_btn m-auto'><'col-md-4'f>>" +
                     "<'row'<'col-md-12't>><'row'<'col-md-12'ip>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Data Galeri'
                    }
                ]

                // stateSave:false,
        });

        $("div.test_btn").html('<a class="btn btn-lg btn-md btn-success" href="{{ url('greet/edit/0?tipx=new') }}"> <i class="fas fa-plus fa-sm md-3"></i></a>');
    });

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

</script>
@endsection
