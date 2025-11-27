@extends('layouts.main')

<style>
    .card {

    }

    .form-control:focus {
        background-color: #E0FFFF !important;
    }
</style>

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">Lihat Surat {{$header->NO_BUKTI}}</h1>	
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="{{url('/surats')}}">Transaksi Surat Jalan</a></li>
						<li class="breadcrumb-item active">Lihat {{$header->NO_BUKTI}}</li>
					</ol>
				</div>
			</div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
        <div class="row">
            <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{url('/surats/update/'.$header->NO_ID)}}" id="entri" method="POST">
                        @csrf
                        <div class="tab-content mt-3">
        
                            <div class="form-group row">
                                <div class="col-md-2" align="right">
                                    <label for="NO_BUKTI" class="form-label">Bukti#</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NO_BUKTI" disabled id="NO_BUKTI" name="NO_BUKTI"
                                    placeholder="Masukkan Bukti#" value="{{$header->NO_BUKTI}}" readonly style="width:140px">
                                </div>
                                <div class="col-md-2" align="right">
                                    <label for="TGL" class="form-label">Tanggal</label>
                                </div>
                                <div class="col-md-4">				
								  <input class="form-control date" disabled id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}" style="width:140px">
								</div>
                            </div>
        
                            <div class="form-group row">
								<div class="col-md-2" align="right">
									<label style="color:red;font-size:20px">* </label>
                                    <label for="NO_SO" class="form-label">SO#</label>
                                </div>
                                <div class="col-md-4 input-group">
                                    <input type="text" class="form-control NO_SO" disabled id="NO_SO" name="NO_SO" placeholder="Masukkan SO#" value="{{$header->NO_SO}}" readonly>
        			    			<button type="button" class="btn btn-danger" onclick="hapusSO()">&#10006</button>
                                </div>
                            </div>
							
							<div class="form-group row">
                                <div class="col-md-2" align="right">
                                    <label for="KODEC" class="form-label">Customer#</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control KODEC" disabled id="KODEC" name="KODEC" placeholder="Masukkan Customer#" value="{{$header->KODEC}}" readonly style="width:140px">
                                </div>
                                <div class="col-md-2" align="right">
                                    <label for="NAMAC" class="form-label">Nama</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NAMAC" disabled id="NAMAC" name="NAMAC" placeholder="Nama Customer" value="{{$header->NAMAC}}" readonly>
                                </div>
                            </div>
							
							<div class="form-group row">
                                <div class="col-md-2" align="right">
                                    <label for="ALAMAT" class="form-label">Alamat</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control ALAMAT" disabled id="ALAMAT" name="ALAMAT" placeholder="Alamat Customer" value="{{$header->ALAMAT}}" readonly>
                                </div>
                                <div class="col-md-2" align="right">
                                    <label for="KOTA" class="form-label">Kota</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control KOTA" disabled id="KOTA" name="KOTA" placeholder="Kota Customer" value="{{$header->KOTA}}" readonly>
                                </div>
                            </div>

							<div class="form-group row">
                                <div class="col-md-2" align="right">
                                    <label for="KD_BRG" class="form-label">Barang</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control KD_BRG" disabled id="KD_BRG" name="KD_BRG" placeholder="Masukkan Barang" value="{{$header->KD_BRG}}" readonly style="width:140px">
                                </div>
                                <div class="col-md-2" align="right">
                                    <label for="NA_BRG" class="form-label">-</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NA_BRG" disabled id="NA_BRG" name="NA_BRG" placeholder="Nama Barang" value="{{$header->NA_BRG}}" readonly>
                                </div>
                        	</div>
                        
							<div class="form-group row">
                                <div class="col-md-2" align="right">
                                    <label for="SISA" class="form-label">Sisa-SO</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text"  class="form-control SISA" disabled id="SISA" name="SISA" placeholder="-" value="{{ number_format( $header->SISA, 2, '.', ',') }}" style="text-align: right; width:140px" readonly>
                                </div>
                           </div>
							
                        	<div class="form-group row">
                                <div class="col-md-2" align="right">
                                    <label for="TRUCK" class="form-label">Truck</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control TRUCK" disabled id="TRUCK" name="TRUCK" placeholder="Masukkan Truck" value="{{$header->TRUCK}}" style="width:140px">
                                </div>
                                <div class="col-md-2" align="right">
                                    <label for="SOPIR" class="form-label">Sopir</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control SOPIR" disabled id="SOPIR" name="SOPIR" placeholder="Masukkan Sopir" value="{{$header->SOPIR}}">
                                </div>
                            </div>
                            
							<div class="form-group row">
                                <div class="col-md-2" align="right">
									<label style="color:red;font-size:20px">* </label>		
                                    <label for="KODET" class="form-label">Tujuan</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control KODET" disabled id="KODET" name="KODET" placeholder="Masukkan Tujuan#" value="{{$header->KODET}}" readonly style="width:140px">
                                </div>
                                <div class="col-md-2" align="right">
                                    <label for="NAMAT" class="form-label">-</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NAMAT" disabled id="NAMAT" name="NAMAT" placeholder="-" value="{{$header->NAMAT}}" readonly>
                                </div>
                            </div>
							
							<div class="form-group row">
                                <div class="col-md-2" align="right">
                                    <label for="KG" class="form-label">Kg</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" onclick="select()" disabled onkeyup="hitung()" class="form-control KG" id="KG" name="KG" placeholder="Masukkan Kg" value="{{ number_format( $header->KG, 2, '.', ',') }}" style="text-align: right; width:140px" {{ ($header->MASUK!=0 && $header->KELUAR!=0) ? 'readonly' : '' }}>
                                </div>
                                <div class="col-md-2" align="right">
                                    <label for="HARGA" class="form-label">Harga</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" onclick="select()" disabled onkeyup="hitung()" class="form-control HARGA" id="HARGA" name="HARGA" placeholder="HARGA" value="{{ number_format( $header->HARGA, 2, '.', ',') }}" style="text-align: right; width:140px">
                                </div>
                            </div>

							<div class="form-group row">
                                <div class="col-md-2" align="right">
                                    <label for="TOTAL" class="form-label">Total</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control TOTAL" disabled id="TOTAL" name="TOTAL" placeholder="TOTAL" value="{{ number_format( $header->TOTAL, 2, '.', ',') }}" style="text-align: right; width:140px" readonly>
                                </div>
                            </div>						
							
							<div class="form-group row">
                                <div class="col-md-2" align="right">
                                    <label for="NOTES" class="form-label">Notes</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NOTES" disabled id="NOTES" name="NOTES" placeholder="Masukkan Notes" value="{{$header->NOTES}}">
                                </div>
                            </div>	

                        </div>

						<hr style="margin-top: 30px; margin-buttom: 30px">

                        <div class="mt-3">
                            <div class="form-group row">
                                <div class="col-md-2">
                                </div>
                                <div class="col-md-4">
<!--									<button type="button" onclick="simpan()" class="btn btn-success"><i class="fa fa-save"></i> Save</button>										
									<a type="button" href="javascript:javascript:history.go(-1)" class="btn btn-danger">Cancel</a>
-->
                                </div>
							</div>
                        </div>
                    </form>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
	
	
	<div class="modal fade" id="browseSoModal" tabindex="-1" role="dialog" aria-labelledby="browseSoModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseSoModalLabel">Cari So#</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bso">
				<thead>
					<tr>
						<th>So#</th>
						<th>Customer</th>
						<th>Barang#</th>
						<th>Tujuan</th>
						<th>Harga</th>
						<th>Kg</th>
						<th>Kirim</th>						
						<th>Sisa</th>	
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

	
	<div class="modal fade" id="browseTujuanModal" tabindex="-1" role="dialog" aria-labelledby="browseTujanModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseSuplierModalLabel">Cari Tujuan</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-btujuan">
				<thead>
					<tr>
						<th>Tujuan</th>
						<th>-</th>
						<th>Alamat</th>
						<th>Kota</th>
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
	
	
	<div class="modal fade" id="browseTruckModal" tabindex="-1" role="dialog" aria-labelledby="browseTruckModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseTruckModalLabel">Cari Truck</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-btruck">
				<thead>
					<tr>
						<th>NoPol</th>
						<th>Sopir</th>
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

@section('footer-scripts')
<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>

<script>
	var idrow = 1;
    function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}

	$(document).ready(function() {
		$("#SISA").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#KG").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#HARGA").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#TOTAL").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});

		$(".date").datepicker({
			'dateFormat': 'dd-mm-yy',
		})
		
		hitung=function() {

			var KGX = parseFloat($('#KG').val().replace(/,/g, ''));
			var HARGAX = parseFloat($('#HARGA').val().replace(/,/g, ''));
		
            var TOTALX = HARGAX * KGX;
			$('#TOTAL').val(numberWithCommas(TOTALX));	
		    $("#TOTAL").autoNumeric('update');	
		
		
		}		

		///////////////////////////////////////////////////////////////////////

 		var dTableBSo;
		loadDataBSo = function(){
			$.ajax(
			{
				type: 'GET',    
				url: '{{url('so/browse')}}',
				success: function( response )
				{
					resp = response;
					if(dTableBSo){
						dTableBSo.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBSo.row.add([
							'<a href="javascript:void(0);" onclick="chooseSo(\''+resp[i].NO_BUKTI+'\',  \''+resp[i].KODEC+'\', \''+resp[i].NAMAC+'\', \''+resp[i].ALAMAT+'\', \''+resp[i].KOTA+'\' , \''+resp[i].KD_BRG+'\' , \''+resp[i].NA_BRG+'\' , \''+resp[i].KG+'\', \''+resp[i].HARGA+'\', \''+resp[i].KIRIM+'\', \''+resp[i].SISA+'\', \''+resp[i].KODET+'\', \''+resp[i].NAMAT+'\'  )">'+resp[i].NO_BUKTI+'</a>',
							resp[i].NAMAC,
							resp[i].NA_BRG,
							resp[i].NAMAT,
							resp[i].HARGA,
							resp[i].KG,
							resp[i].KIRIM,
							resp[i].SISA,
						]);
					}
					dTableBSo.draw();
				}
			});
		}
		
		dTableBSo = $("#table-bso").DataTable({
			columnDefs: [
				{
                    className: "dt-right", 
					targets:  [4,5,6,7],
					render: $.fn.dataTable.render.number( ',', '.', 2, '' )
				}
			],
		});
		
		browseSo = function(){
			loadDataBSo();
			$("#browseSoModal").modal("show");
		}
		
		chooseSo = function(NO_BUKTI,KODEC,NAMAC,ALAMAT, KOTA, KD_BRG, NA_BRG, KG, HARGA, KIRIM, SISA, KODET, NAMAT){
			$("#NO_SO").val(NO_BUKTI);
			$("#KODEC").val(KODEC);
			$("#NAMAC").val(NAMAC);
			$("#ALAMAT").val(ALAMAT);
			$("#KOTA").val(KOTA);
			$("#KD_BRG").val(KD_BRG);
			$("#NA_BRG").val(NA_BRG);			
			$("#HARGA").val(HARGA);	
			$("#SISA").val(SISA);
		    @if (($header->MASUK == 0) && ($header->KELUAR == 0))
			   $("#KG").val(SISA);
		    @endif
			$("#KODET").val(KODET);
			$("#NAMAT").val(NAMAT);
			$("#browseSoModal").modal("hide");
		}
		
		$("#NO_SO").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseSo();
			}
		}); 
		
		/////////////////////////////////////////////////////////////////////////

 		var dTableBTruck;
		loadDataBTruck = function(){
			$.ajax(
			{
				type: 'GET',    
				url: '{{url('truck/browse')}}',
				success: function( response )
				{
					resp = response;
					if(dTableBTruck){
						dTableBTruck.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBTruck.row.add([
							'<a href="javascript:void(0);" onclick="chooseTruck(\''+resp[i].NOPOL+'\' , \''+resp[i].SOPIR+'\'  )">'+resp[i].NOPOL+'</a>',
							resp[i].SOPIR,
							
						]);
					}
					dTableBTruck.draw();
				}
			});
		}
		
		dTableBTruck = $("#table-btruck").DataTable({
			
		});
		
		browseTruck = function(){
			loadDataBTruck();
			$("#browseTruckModal").modal("show");
		}
		
		chooseTruck = function(NOPOL, SOPIR){
			$("#TRUCK").val(NOPOL);
			$("#SOPIR").val(SOPIR);
			$("#browseTruckModal").modal("hide");
		}
		
		$("#TRUCK").keypress(function(e){

			if(e.keyCode == 46){
				e.preventDefault();
				browseTruck();
			}
		}); 

///////////////////////////////////////////////////////////////////////

		var dTableBTujuan;
		var rowidTujuan;
		loadDataBTujuan = function(){
			$.ajax(
			{
				type: 'GET',    
				url: "{{url('tujuan/browse')}}",
				data: {
					'GOL': 'Z',
				},
				success: function( response )
				{
					resp = response;
					if(dTableBTujuan){
						dTableBTujuan.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBTujuan.row.add([
							'<a href="javascript:void(0);" onclick="chooseTujuan(\''+resp[i].KODET+'\',  \''+resp[i].NAMAT+'\',   \''+resp[i].ALAMAT+'\', \''+resp[i].KOTA+'\' )">'+resp[i].KODET+'</a>',
							resp[i].NAMAT,
							resp[i].ALAMAT,
							resp[i].KOTA,
							
						]);
					}
					dTableBTujuan.draw();
				}
			});
		}
		
		dTableBTujuan = $("#table-btujuan").DataTable({
			
		});
		
		browseTujuan = function(){
			loadDataBTujuan();
			$("#browseTujuanModal").modal("show");
		}
		
		chooseTujuan = function(KODET,NAMAT){
			$("#KODET").val(KODET);
			$("#NAMAT").val(NAMAT);			
			$("#browseTujuanModal").modal("hide");
		}
		
		$("#KODET").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseTujuan();
			}
		}); 
		
	});		

	function hapusSO()
	{
		var notes = $("#NOTES").val();
		var no_so = $("#NO_SO").val();
		if (no_so!='')
		{
			notes=='' ? $("#NOTES").val(no_so + ';') : $("#NOTES").val(notes + ';' + no_so);
		}
		
		$("#NO_SO").val('');
		$("#KODEC").val('');
		$("#NAMAC").val('');
		$("#ALAMAT").val('');
		$("#KOTA").val('');
	}

 	function simpan() {
		hitung();
		
		var tgl = $('#TGL').val();
		var bulanPer = {{session()->get('periode')['bulan']}};
		var tahunPer = {{session()->get('periode')['tahun']}};
		
        var check = '0';
		
		
			if ( $('#NO_SO').val()=='' ) 
            {			
			    check = '1';
				alert("SO# Harus diisi.");
			}
			
			if ( $('#TRUCK').val()=='' ) 
            {			
			    check = '1';
				alert("Truck# Harus diisi.");
			}
			
			if ( tgl.substring(3,5) != bulanPer ) 
			{
				check = '1';
				alert("Bulan tidak sama dengan Periode");
			}	
			

			if ( tgl.substring(tgl.length-4) != tahunPer )
			{
				check = '1';
				alert("Tahun tidak sama dengan Periode");
		    }	 

        
			if ( check == '0' )
			{
		    	document.getElementById("entri").submit();  
			}
			
	}
	
</script>
@endsection