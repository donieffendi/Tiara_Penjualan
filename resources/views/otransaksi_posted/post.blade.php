@extends('layouts.plain')
@section('styles')
<link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Posted</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Posted</li>
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
              <form id="entri" action="{{url('posted/posting')}}">
                @csrf	  
                <button class="btn btn-danger" type="button"  onclick="simpan()">Posted</button>


                <!-- <div class="form-group row">
                    <div class="col-md-2" align="right">
                        <label for="JENIS" class="form-label">Pilih Print</label>
                    </div>
                    <div class="col-md-2">
                        <select id="JENIS" class="form-control" name="JENIS">
                          <option value="DANAMON" >DANAMON</option>
                          <option value="BRI" >BRI</option>
                          <option value="BCA-SETORAN" >BCA SETORAN</option>
                          <option value="BCA-SETORAN-KLIRING" >BCA SETORAN KLIRING</option>
                          <option value="DANAMON2" >DANAMON 2</option>
                          <option value="UOB" >UOB</option>
                          <option value="DANAMON-1B" >DANAMON 1B</option>
                          <option value="BRI-NEW" >BRI NEW</option>
                        </select>
                    </div>
                </div> -->

                
                
                <table class="table table-fixed table-striped table-border table-hover nowrap datatable" id="datatable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="text-align: center">#</th>		
                            <th scope="col">Pilih</th>	
                            <th scope="col" style="text-align: left">No Bukti</th>
                            <th scope="col" style="text-align: center">Tgl</th>
                            <th scope="col" style="text-align: left">No PO</th>
                            <th scope="col" style="text-align: left">Kodes</th>
                            <th scope="col" style="text-align: left">Namas</th>
                            <th scope="col" style="text-align: left">Total Qty</th>
                            <th scope="col" style="text-align: right">Notes</th>
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

  <div class="modal fade" id="browsePpModal" tabindex="-1" role="dialog" aria-labelledby="browsePpModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="browsePpModalLabel">Cari Pp</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
              </div>
                <div class="modal-body">
                  <table class="table table-stripped table-bordered" id="table-bpp">
                    <thead>
                      <tr>
                        <th>No PP</th>
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
<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>

<script>
  $(document).ready(function() {
      var dataTable = $('.datatable').DataTable({
          processing: true,
          serverSide: true,
 //         autoWidth: true,
 //         'scrollX': true,
          'scrollY': '400px',
          "order": [[ 0, "asc" ]],
          ajax: 
          {
              url: "{{ route('get-posted') }}",
              data: 
              {
                filterpost : 1,
                NO_PP : $('#NO_PP').val(),
              }
          },
          columns: 
          [
              {data: 'DT_RowIndex', orderable: false, searchable: false },
              {data: 'cek', name: 'cek'},	
              {data: 'NO_BUKTI', name: 'NO_BUKTI'},
              {data: 'TGL', name: 'TGL'},
              {data: 'NO_PO', name: 'NO_PO'},
              {data: 'KODES', name: 'KODES'},	
              {data: 'NAMAS', name: 'NAMAS'},	
              {data: 'TOTAL_QTY', name: 'TOTAL_QTY', render: $.fn.dataTable.render.number( ',', '.', 0, '' )},	
              {data: 'NOTES', name: 'NOTES'},
          ],
		  
          columnDefs: 
          [
            {
                "className": "dt-center", 
                "targets": 0
            },		
            {
                "className": "dt-right", 
                "targets": 7
            },			
            {
              targets: 3,
              render: $.fn.dataTable.render.moment( 'DD-MM-YYYY' )
            },
          ],
      });

      //CHOOSE Wilayah
      var dTableBWilayah;
        loadDataBWilayah = function(){
          $.ajax(
          {
            type: 'GET',    
            url: '{{url('cetakslip/browsewilayah')}}',
            success: function( response )
            {
              resp = response;
              if(dTableBWilayah){
                dTableBWilayah.clear();
              }
              for(i=0; i<resp.length; i++){
                
                dTableBWilayah.row.add([
                  '<a href="javascript:void(0);" onclick="chooseWilayah(\''+resp[i].WILAYAH+'\',\''+resp[i].WILAYAH1+'\')">'+resp[i].WILAYAH+'</a>',
                  resp[i].WILAYAH1,
                ]);
              }
              dTableBWilayah.draw();
            }
          });
        }

        dTableBWilayah = $("#table-bwilayah").DataTable({
          
        });
        
        browseWilayah = function(){
          loadDataBWilayah();
          $("#browseWilayahModal").modal("show");
        }
        
        chooseWilayah = function(WILAYAH,WILAYAH1){
          $("#WILAYAH").val(WILAYAH);
          $("#WILAYAH1").val(WILAYAH1);
          $("#browseWilayahModal").modal("hide");
        }
        
        $("#WILAYAH").keypress(function(e){
          if(e.keyCode == 46){
            e.preventDefault();
            browseWilayah();
          }
        }); 
		
//////////////////////////////////////////////////////////////////////////////////////////////////

      //CHOOSE Pp
        var dTableBPp;
        loadDataBPp = function(){
          $.ajax(
          {
            type: 'GET',    
            url: '{{url('cetakslip/browsepp')}}',
            success: function( response )
            {
              resp = response;
              if(dTableBPp){
                dTableBPp.clear();
              }
              for(i=0; i<resp.length; i++){
                
                dTableBPp.row.add([
                  '<a href="javascript:void(0);" onclick="choosePp(\''+resp[i].NO_BUKTI+'\')">'+resp[i].NO_BUKTI+'</a>',
                  resp[i].TGL,
                  resp[i].TJTEMPO,
                ]);
              }
              dTableBPp.draw();
            }
          });
        }

        dTableBPp = $("#table-bpp").DataTable({
          
        });
        
        browsePp = function(){
          loadDataBPp();
          $("#browsePpModal").modal("show");
        }
        
        choosePp = function(NO_BUKTI){
          $("#NO_PP").val(NO_BUKTI);
          $("#browsePpModal").modal("hide");
        }
        
        $("#NO_PP").keypress(function(e){
          if(e.keyCode == 46){
            e.preventDefault();
            browsePp();
          }
        }); 
		
//////////////////////////////////////////////////////////////////////////////////////////////////
      
  });

  function cekQty(){
		$(".qtyt").each(function() {
      var qtyt = parseFloat($(this).val().replace(/,/g, ''));

      let z = $(this).closest('tr');
      var centang = z.find('.cek:checked').val();
      
      if(qtyt<0 && centang)
      {
        console.log(qtyt);
        return qtyt;
      }
		});
	}
  
  function hitung() {
		$(".qtyt").each(function() {
		  $(this).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
		});
  }

	function simpanx() {
    // console.log(cekQty());
  }

	function simpan() {
		
		var check = '0';

		if ($('#wilayah1').val() == '') {
			check = '1';
			alert("Wilayah Harus di pilih.");
		}	
		
			if ($('#KOTA_SETOR').val() == '') {
			check = '1';
			alert("Kota Setor Harus di isi.");
		}	


		(check==0) ? document.getElementById("entri").submit() : alert('Masih ada kesalahan');
   
	}

  
</script>
@endsection
