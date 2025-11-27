@extends('layouts.main')
@section('styles')
<link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Posting Status SLS {{$judul}}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Posting Status SLS {{$judul}}</li>
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
    @if (session('gagal'))
        <div class="alert alert-danger">
            {{session('gagal')}}
        </div>
    @endif

    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
              <form method="POST" id="entri" action="{{url('/postingsls/posting')}}">
                @csrf	  
								<input name="jenis" type="hidden" value="{{$jenis}}">
                <button class="btn btn-danger" type="button"  onclick="simpan()">Posting</button>
                
                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="text-align: center">#</th>		
                            <th scope="col">Cek</th>			
                            <th scope="col" style="text-align: center">Bukti#</th>
                            <th scope="col" style="text-align: center">Tgl</th>
                            @if ($jenis=="po")		
                              <th scope="col" style="text-align: center">Suplier</th>
                            @elseif ($jenis=="so")		
                              <th scope="col" style="text-align: center">Customer</th>
                            @endif		
                            <th scope="col" style="text-align: center">Barang</th>
                            <th scope="col" style="text-align: center">Tujuan</th>
                            <th scope="col" style="text-align: center">Kg</th>
                            <th scope="col" style="text-align: center">Sisa</th>
                            <th scope="col" style="text-align: center">Total</th>
                            <th scope="col" style="text-align: center">Notes</th>
                            <th scope="col" style="text-align: center">Tujuan</th>
                        </tr>
                    </thead>
    
                    <tbody>
                    </tbody> 
                </table>
              </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('javascripts')
<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>

<script>
  $(document).ready(function() {
      var dataTable = $('.datatable').DataTable({
          processing: true,
          serverSide: true,
          autoWidth: true,
          scrollX: true,
          scrollY: '400px',
          "order": [[ 0, "asc" ]],
          ajax: 
          {
              url: "{{ url('get-post-sls') }}",
              data: 
              {
                filterpost : 1,
                JNS : "{{$jenis}}",
              }
          },
          columns: 
          [
              {data: 'DT_RowIndex', orderable: false, searchable: false },
              {data: 'cek', name: 'cek'},	
              {data: 'NO_BUKTI', name: 'NO_BUKTI'},
              {data: 'TGL', name: 'TGL', render: $.fn.dataTable.render.moment( 'DD-MM-YYYY' )},
              @if ($jenis=="po")		
                {data: 'NAMAS', name: 'NAMAS'},
              @elseif ($jenis=="so")		
                {data: 'NAMAC', name: 'NAMAC'},		
              @endif		
              {data: 'NA_BRG', name: 'NA_BRG'},
              {data: 'NAMAT', name: 'NAMAT'},
              {data: 'KG', name: 'KG', render: $.fn.dataTable.render.number( ',', '.', 2, '' ), className: 'dt-right KG'},	
              {data: 'SISA', name: 'SISA', render: $.fn.dataTable.render.number( ',', '.', 2, '' ), className: 'dt-right SISA'},	
              {data: 'TOTAL', name: 'TOTAL', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},	
              {data: 'NOTES', name: 'NOTES'},
              {data: 'NAMAT', name: 'NAMAT'},
          ],
          columnDefs: 
          [
            {
                "className": "dt-center", 
                "targets": 0
            },
            {
              "className": "dt-right", 
              "targets": [7,8]
            },
          ],
      });
      
  });

	function simpan() {
		document.getElementById("entri").submit();
	}
</script>
@endsection
