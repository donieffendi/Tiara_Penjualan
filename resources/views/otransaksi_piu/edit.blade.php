@extends('layouts.main')

<style>
    .card {

    }

    .form-control:focus {
        background-color: #b5e5f9 !important;
    }
</style>

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">

        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
        <div class="row">
            <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <form action="{{($tipx=='new')? url('/piu/store?flagz='.$flagz.'') : url('/piu/update/'.$header->NO_ID.'&flagz='.$flagz.'' ) }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
                        <div class="tab-content mt-3">
        
                            <div class="form-group row">
                                <div class="col-md-1" align="right">
                                    <label for="NO_BUKTI" class="form-label">Bukti#</label>
                                </div>
								

                                   <input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
                                    placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

									<input name="tipx" class="form-control tipx" id="tipx" value="{{$tipx}}" hidden>
									<input name="flagz" class="form-control flagz" id="flagz" value="{{$flagz}}" hidden>

								
                                <div class="col-md-2">
                                    <input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI"
                                    placeholder="Masukkan Bukti#" value="{{$header->NO_BUKTI}}" readonly>
                                </div>

                            </div>

                            <div class="form-group row">
                                <div class="col-md-1" align="right">
                                    <label for="TGL" class="form-label">Tgl</label>
                                </div>
                                <div class="col-md-2">
								  <input class="form-control date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}">
                                </div>

                            </div>
        

							<div class="form-group row">
							
								<div class="col-md-1" align="right">
									<label style="color:red">*</label>									
                                    <label for="KODEC" class="form-label">Customer#</label>
                                </div>
                               	<div class="col-md-2 input-group" >
                                  <input type="text" class="form-control KODEC" id="KODEC" name="KODEC" placeholder="Pilih Customer"value="{{$header->KODEC}}" style="text-align: left" readonly >
        						  <button type="button" class="btn btn-primary" onclick="browseCust()"><i class="fa fa-search"></i></button>
                                </div>
								
								<div class="col-md-1" align="right">
                                    <label for="NAMAC" class="form-label"></label>
                                </div>
								<div class="col-md-4">
                                    <input type="text" class="form-control NAMAC" id="NAMAC" name="NAMAC" placeholder="-" value="{{$header->NAMAC}}" readonly>
                                </div>
                            </div>
	
 
							<div class="form-group row">
                                <div class="col-md-1" align="right">
									<label style="color:red">*</label>									
                                    <label for="NOTES" class="form-label">Notes</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control NOTES" id="NOTES" name="NOTES" value="{{$header->NOTES}}" placeholder="Masukkan Notes" >
                                </div>

								<div class="col-md-1">								
									<label for="KODEP" class="form-label">Sales</label>
								</div>

								<div class="col-md-1 input-group" >
								<input type="text" class="form-control KODEP" id="KODEP" name="KODEP" placeholder=""value="{{$header->KODEP}}" style="text-align: left" readonly >
								</div>

								<div class="col-md-2">
									<input type="text" class="form-control NAMAP" id="NAMAP" name="NAMAP" placeholder="" value="{{$header->NAMAP}}" readonly>
								</div>

								<div class="col-md-1">								
									<label for="KOM" class="form-label">Komisi</label>
								</div>
								

 								<div class="col-md-2">
									<input type="text" hidden class="form-control KOM" onclick="select()"  id="KOM" name="KOM" placeholder="" value="{{ number_format($header->KOM, 2, '.', ',') }}" style="text-align: right; width:140px" readonly>

									<input type="text" class="form-control TKOM" onclick="select()"  id="TKOM" name="TKOM" placeholder="" value="{{ number_format($header->TKOM, 2, '.', ',') }}" style="text-align: right; width:140px" readonly>
								</div> 
								
                            </div>


	
                            <table id="datatable" class="table table-striped table-border">
                                <thead>
                                    <tr>
										<th width="100px" style="text-align:center">No.</th>  
                                        <th width="200px" style="text-align:center">
								        	<label style="color:red;font-size:20px">* </label>									
                                            <label for="BACNO" class="form-label">Faktur#</label></th>
                                        <th width="200px" style="text-align:right">Total</th>
                                        <th width="200px" style="text-align:right">Bayar</th>
                                        <th width="200px" style="text-align:right">Sisa</th> 
                                        <th></th>										
                                    </tr>
                                </thead>
        
								<tbody>
								
								<?php $no=0 ?>
								@foreach ($detail as $detail)	
                                    <tr>
                                        <td>
                                            <input type="hidden" name="NO_ID[]{{$no}}" id="NO_ID" type="text" value="{{$detail->NO_ID}}" 
                                            class="form-control NO_ID" onkeypress="return tabE(this,event)" readonly>
                                            <input name="REC[]" id="REC{{$no}}" type="text" value="{{$detail->REC}}" class="form-control REC" onkeypress="return tabE(this,event)" readonly style="text-align:center">
                                        </td>
                                        <td>
                                            <input name="NO_FAKTUR[]" id="NO_FAKTUR{{$no}}" type="text" class="form-control NO_FAKTUR" value="{{$detail->NO_FAKTUR}}" readonly required>
                                        </td>
										<td>
										    <input name="TOTAL[]" onclick="select()" onblur="hitung()" value="{{$detail->TOTAL}}" id="TOTAL{{$no}}" type="text" style="text-align: right"  class="form-control TOTAL" readonly >
										</td>    
										<td>
										    <input name="BAYAR[]" onclick="select()" onblur="hitung()" value="{{$detail->BAYAR}}" id="BAYAR{{$no}}" type="text" style="text-align: right"  class="form-control BAYAR">
										</td>       
										<td>
										    <input name="SISA[]" onclick="select()" onblur="hitung()" value="{{$detail->SISA}}" id="SISA{{$no}}" type="text" style="text-align: right"  class="form-control SISA">
										</td>   
										
										<td>
											<button type='button' id='DELETEX{{$no}}'  class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
										</td>
                                    </tr>
								
								<?php $no++; ?>
								@endforeach
                                </tbody>

								<tfoot>
                                    <td></td>
                                    <td></td>
                                    <td></td>

                                    <<td><input class="form-control TBAYAR  text-block font-weight-bold" style="text-align: right"  id="TBAYAR" name="TBAYAR" value="{{$header->BAYAR}}" readonly></td>

                                    <td></td>                                   
                                    <td></td>                                   
                                 
                                  
                                    
                                    
                                </tfoot>
                                
                                
                            </table>
						
								
						</form>
					</div>                               
				</div>

						<div class="col-md-2 row">
							<a type="button" id='PLUSX' onclick="tambah()" class="fas fa-plus fa-sm md-3" style="font-size: 20px" ></a>

						</div>
						
						<hr style="margin-top: 30px; margin-buttom: 30px">		
                                 
						
                                 
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button type="button" id='TOPX'  onclick="location.href='{{url('/piu/edit/?idx=' .$idx. '&tipx=top&flagz='.$flagz.'' )}}'" class="btn btn-outline-primary">Top</button>
								<button type="button" id='PREVX' onclick="location.href='{{url('/piu/edit/?idx='.$header->NO_ID.'&tipx=prev&flagz='.$flagz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Prev</button>
								<button type="button" id='NEXTX' onclick="location.href='{{url('/piu/edit/?idx='.$header->NO_ID.'&tipx=next&flagz='.$flagz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Next</button>
								<button type="button" id='BOTTOMX' onclick="location.href='{{url('/piu/edit/?idx=' .$idx. '&tipx=bottom&flagz='.$flagz.'' )}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button type="button" id='NEWX' onclick="location.href='{{url('/piu/edit/?idx=0&tipx=new&flagz='.$flagz.'' )}}'" class="btn btn-warning">New</button>
								<button type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button type="button" id='UNDOX' onclick="location.href='{{url('/piu/edit/?idx=' .$idx. '&tipx=undo&flagz='.$flagz.'' )}}'" class="btn btn-info">Undo</button>  
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button type="button" id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								<button type="button" id='CLOSEX'  onclick="location.href='{{url('/piu?flagz='.$flagz.'' )}}'" class="btn btn-outline-secondary">Close</button>
							</div>
						</div>
			
			            
						
						
			
            </div>
        </div>
        </div>
    </div>

	
	

	<div class="modal fade" id="browseJualModal" tabindex="-1" role="dialog" aria-labelledby="browseJualModalLabel" aria-hidden="true">
	  <div class="modal-dialog mw-100 w-75" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseJualModalLabel">Cari Jual#</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bjual">
				<thead>
					<tr>
						<th>Jual#</th>
						<th>Tgl</th>
						<th>Kode</th>
						<th>-</th>
						<th>Total</th>
						<th>Bayar</th>
						<th>Sisa</th>	
						<th>Pilih</th>		 					
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		  </div>
		  <div class="modal-footer">
			<button type="button" onclick="chooseJualArr()"  class="btn btn-secondary" data-dismiss="modal">Close</button>
		  </div>
		</div>
	  </div>
	</div>
		
	
 	<div class="modal fade" id="browseCustModal" tabindex="-1" role="dialog" aria-labelledby="browseCustModalLabel" aria-hidden="true">
	  <div class="modal-dialog mw-100 w-75" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseCustModalLabel">Cari Customer</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bcust">
				<thead>
					<tr>
						<th>Customer</th>
						<th>Nama</th>
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
	
	
@endsection

@section('footer-scripts')
<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>

<script>
	var idrow = 1;
	var baris = 1;

	var idrow2 = 1;
	var baris2 = 1;
	
	
	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
	
    $(document).ready(function () {
		idrow=<?=$no?>;
		baris=<?=$no?>;

		
		$('body').on('keydown', 'input, select', function(e) {
			if (e.key === "Enter") {
				var self = $(this), form = self.parents('form:eq(0)'), focusable, next;
				focusable = form.find('input,select,textarea').filter(':visible');
				next = focusable.eq(focusable.index(this)+1);
				console.log(next);
				if (next.length) {
					next.focus().select();
				} else {
					tambah();
					// var nomer = idrow-1;
					// console.log("REC"+nomor);
					// document.getElementById("REC"+nomor).focus();
					// form.submit();
				}
				return false;
			}
		});

		$tipx = $('#tipx').val();
		$searchx = $('#CARI').val();
		
		
        if ( $tipx == 'new' )
		{
			 baru();	
             tambah();				 
		}

        if ( $tipx != 'new' )
		{
			 ganti();			
		}    
		

		
		$("#TBAYAR").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});	
		$("#TKOM").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});		
		$("#KOM").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});				

		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#TOTAL" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#BAYAR" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#SISA" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
		}	


		$(".NO_FAKTUR").each(function() {
			var getid = $(this).attr('id');
			var noid = getid.substring(9,12);

			$("#NO_FAKTUR"+noid).keypress(function(e){
				if(e.keyCode == 46){
					e.preventDefault();
					browseBeli(noid);
				}
			}); 
		});
		
        $('body').on('click', '.btn-delete', function() {
			var val = $(this).parents("tr").remove();
			baris--;
			hitung();
			nomor();
			
		});

		$('.date').datepicker({  
            dateFormat: 'dd-mm-yy'
		});
		
		
 	
	/////////////////////////////////////////////////////////	

		var dTableBCust;
		loadDataBCust = function(){
		
			$.ajax(
			{
				type: 'GET', 		
				url: '{{url('cust/browse')}}',

				success: function( response )
				{
					resp = response;
					if(dTableBCust){
						dTableBCust.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBCust.row.add([
							'<a href="javascript:void(0);" onclick="chooseCustomer(\''+resp[i].KODEC+'\',  \''+resp[i].NAMAC+'\', \''+resp[i].ALAMAT+'\',  \''+resp[i].KOTA+'\',  \''+resp[i].KODEP+'\',  \''+resp[i].NAMAP+'\',  \''+resp[i].KOM+'\')">'+resp[i].KODEC+'</a>',
							resp[i].NAMAC,
							resp[i].ALAMAT,
							resp[i].KOTA,
						]);
					}
					dTableBCust.draw();
				}
			});
		}
		
		dTableBCust = $("#table-bcust").DataTable({
			
		});
		
		browseCust = function(){
			loadDataBCust();
			$("#browseCustModal").modal("show");
		}
		
		chooseCustomer = function(KODEC,NAMAC, ALAMAT, KOTA, KODEP, NAMAP, KOM){
			$("#KODEC").val(KODEC);
			$("#NAMAC").val(NAMAC);
			$("#ALAMAT").val(ALAMAT);
			$("#KOTA").val(KOTA);			
			$("#KODEP").val(KODEP);			
			$("#NAMAP").val(NAMAP);			
			$("#KOM").val(KOM);			
			$("#browseCustModal").modal("hide");
		}
		
		$("#KODEC").keypress(function(e){

			if(e.keyCode == 46){
				 e.preventDefault();
				 browseCust();
			}
		}); 


		/////////////////////////////////////////////////////////////////////////////
	
	
	
		/////////////////////////////////////////////////////
		///////////////////////////////////////
		var dTableBJual;
		var rowidJual;

		loadDataBJual = function(){
			var dataDetail = $("input[name='NO_FAKTUR[]']").map(function() {
				var isi = "''";
				if ($(this).val()) {
					isi = "'" + $(this).val() + "'";
				}
				return isi;
			}).get();
	
			
			$.ajax(
			{
				type: 'GET',    
				url: "{{url('jual/browseuang')}}",
				data: {
					'KODEC': $("#KODEC").val(),
					listDetail: dataDetail, 
				},
				success: function( response )
				{
					resp = response;
					if(dTableBJual){
						dTableBJual.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBJual.row.add([
							'<a href="javascript:void(0);" onclick="chooseJual(\''+resp[i].NO_BUKTI+'\',  \''+resp[i].KODEC+'\',   \''+resp[i].NAMAC+'\',  \''+resp[i].TOTAL+'\' ,  \''+resp[i].BAYAR+'\', \''+resp[i].SISA+'\'   )">'+resp[i].NO_BUKTI+'<input id="pilihFaktur'+i+'" hidden value="'+resp[i].NO_BUKTI+'"></a>',
							resp[i].TGL,
							resp[i].KODEC,
							resp[i].NAMAC,
							'<label for="pilihTotal" id="pilihTotal'+i+'" value="'+resp[i].TOTAL+'">'+Intl.NumberFormat('en-US').format(resp[i].TOTAL)+'</label>',
							Intl.NumberFormat('en-US').format(resp[i].BAYAR),
							'<label for="pilihSisa" id="pilihSisa'+i+'" value="'+resp[i].SISA+'">'+Intl.NumberFormat('en-US').format(resp[i].SISA)+'</label>',
							'<input type="checkbox" id="pilih'+i+'" value="'+resp[i].KD_BRG+'"></input>',							
						]);
					}
					dTableBJual.draw();
				}
			});
		}
		
		dTableBJual = $("#table-bjual").DataTable({
			
			columnDefs: [
				{
					targets:  [4,5,6],
					className: 'dt-body-right'
				}
			],
		});
		
		browseJual = function(rid){
			rowidJual = rid;
			loadDataBJual();
			$("#browseJualModal").modal("show");
		}
		
		chooseJual = function(NO_BUKTI,KODEC, NAMAC, TOTAL, BAYAR, SISA){
			$("#NO_FAKTUR"+rowidJual).val(NO_BUKTI);
			$("#TOTAL"+rowidJual).val(SISA);
			$("#BAYAR"+rowidJual).val(SISA);	
			$("#TOTAL"+rowidJual).autoNumeric('update');
			$("#BAYAR"+rowidJual).autoNumeric('update');

			
			$("#browseJualModal").modal("hide");
			hitung();
		}
		
		
		chooseJualArr = function(){
			var jualDipilih = $("input[type='checkbox']").map(function() {
				var idx = dTableBJual.row(this).index();
				var kode = null;
				if($(this).prop("checked"))
				{
					kode = '"'+$(this).val()+'"';
				} 
				return kode;
			}).get();
			var fakturDipilih = $("input[type='checkbox']").map(function() {
				var kode = null;
				if($(this).prop("checked"))
				{
					var idx = (this.id).substring(5, 7);
					kode = '"' + $("#pilihFaktur"+idx).val() + '"';
				} 
				return kode;
			}).get();
			var totalDipilih = $("input[type='checkbox']").map(function() {
				var kode = null;
				if($(this).prop("checked"))
				{
					var idx = (this.id).substring(5, 7);
					kode = '"' + $("#pilihTotal"+idx).text() + '"';
				} 
				return kode;
			}).get();
			var sisaDipilih = $("input[type='checkbox']").map(function() {
				var kode = null;
				if($(this).prop("checked"))
				{
					var idx = (this.id).substring(5, 7);
					kode = '"' + $("#pilihSisa"+idx).text() + '"';
				} 
				return kode;
			}).get();

			var fakturArr = JSON.parse("[" + fakturDipilih + "]");
			var totalArr = JSON.parse("[" + totalDipilih + "]");
			var sisaArr = JSON.parse("[" + sisaDipilih + "]");

			while (idrow<(rowidJual+fakturArr.length))
			{
				tambah();
			};

			for (i=0 ; i<fakturArr.length ; i++) 
			{
				$("#NO_FAKTUR"+(rowidJual+i)).val(fakturArr[i]);
				$("#TOTAL"+(rowidJual+i)).val(totalArr[i]);
				$("#BAYAR"+(rowidJual+i)).val(sisaArr[i]);
			};

			$("#browseJualModal").modal("hide");
			hitung();
		}
		
		$("#NO_FAKTUR0").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseJual(0);
			}
		}); 


 	//////////////////////////////////////////////////
		
		
    });

	function cekDetail(){
		var cekFaktur = '';
		$(".BAYAR").each(function() {
			let z = $(this).closest('tr');
			var TOTALX = parseFloat(z.find('.TOTAL').val().replace(/,/g, ''));
			var BAYARX = parseFloat(z.find('.BAYAR').val().replace(/,/g, ''));
			var FAKTURX = z.find('.NO_FAKTUR').val();
			if(FAKTURX.substring(0,2)!="UM")
			{
				if(BAYARX>TOTALX){
					cekFaktur = FAKTURX;
				};		
			}		
		});

		return cekFaktur;
	}

 	function simpan() {
		hitung();
		
		var tgl = $('#TGL').val();
		var bulanPer = "<?=session()->get('periode')['bulan']?>";
		var tahunPer = "<?=session()->get('periode')['tahun']?>";
		//var TBAYARX = parseFloat($('#TBAYAR').val().replace(/,/g, ''));
		
        var check = '0';
		
			if (cekDetail())
			{	
			    check = '1';
				alert("Faktur# "+cekDetail()+" lebih bayar! ")
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

			if ( $('#NO_BUKTI').val()=='' ) 
            {				
			    check = '1';
				alert("Bukti# Harus Diisi.");
			}
			
			if ( $('#KODEC').val()=='' ) 
            {				
			    check = '1';
				alert("Suplier# Harus Diisi.");
			}
			
			if (baris==0)
			{
				check = '1';
				alert("Data detail kosong (Tambahkan 1 baris kosong jika ingin mengosongi detail)");
			}
			
			if ( check == '0' )
			{
		    	document.getElementById("entri").submit();  
			}

		
	}
		
    function nomor() {
		var i = 1;
		$(".REC").each(function() {
			$(this).val(i++);
		});
	//	hitung();
	}


    function hitung() {
		var TBAYAR = 0;
		var TKOM = 0;

		$(".BAYAR").each(function() {
			
			let z = $(this).closest('tr');
			var TOTALX = parseFloat(z.find('.TOTAL').val().replace(/,/g, ''));
			var BAYARX = parseFloat(z.find('.BAYAR').val().replace(/,/g, ''));
		
            var SISAX  = TOTALX - BAYARX;
			z.find('.SISA').val(SISAX);

		    z.find('.TOTAL').autoNumeric('update');			
		    z.find('.BAYAR').autoNumeric('update');	
		    z.find('.SISA').autoNumeric('update');			
		
            TBAYAR +=BAYARX;				
		
		});
		
		var KOM = parseFloat($('#KOM').val().replace(/,/g, ''));
		
		TKOM =  TBAYAR * KOM / 100;

		if(isNaN(TBAYAR)) TBAYAR = 0;

		$('#TBAYAR').val(numberWithCommas(TBAYAR));		
		$("#TBAYAR").autoNumeric('update');

		if(isNaN(TKOM)) TKOM = 0;

		$('#TKOM').val(numberWithCommas(TKOM));		
		$("#TKOM").autoNumeric('update');
		
		
	}


	

	function baru() {
		
		 kosong();
		 hidup();
	
	}
	
	function ganti() {
		
		 mati();
	
	}
	
	function batal() {
		
		// alert($header[0]->NO_BUKTI);
		
		 //$('#NO_BUKTI').val($header[0]->NO_BUKTI);	
		 mati();
	
	}
	
 

	
	
	function hidup() {

		
		$("#TOPX").attr("disabled", true);
	    $("#PREVX").attr("disabled", true);
	    $("#NEXTX").attr("disabled", true);
	    $("#BOTTOMX").attr("disabled", true);

	    $("#NEWX").attr("disabled", true);
	    $("#EDITX").attr("disabled", true);
	    $("#UNDOX").attr("disabled", false);
	    $("#SAVEX").attr("disabled", false);
		
	    $("#HAPUSX").attr("disabled", true);
	    $("#CLOSEX").attr("disabled", true);

		$("#CARI").attr("readonly", true);	
	    $("#SEARCHX").attr("disabled", true);
		
	    $("#PLUSX").attr("hidden", false)
		   
			$("#NO_BUKTI").attr("readonly", true);		   
			$("#TGL").attr("readonly", false);
			$("#KODEC").attr("readonly", true);
			$("#NAMAC").attr("readonly", true);

			$("#NOTES").attr("readonly", false);
		
	

		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#NO_FAKTUR" + i.toString()).attr("readonly", true);
			$("#TOTAL" + i.toString()).attr("readonly", true);
			$("#BAYAR" + i.toString()).attr("readonly", false);
			$("#SISA" + i.toString()).attr("readonly", true);
			$("#DELETEX" + i.toString()).attr("hidden", false);

			$tipx = $('#tipx').val();
		
			
			if ( $tipx != 'new' )
			{
				$("#NO_FAKTUR" + i.toString()).removeAttr('onclick');	
			}
		}


		
	}


	function mati() {

		
	    $("#TOPX").attr("disabled", false);
	    $("#PREVX").attr("disabled", false);
	    $("#NEXTX").attr("disabled", false);
	    $("#BOTTOMX").attr("disabled", false);


	    $("#NEWX").attr("disabled", false);
	    $("#EDITX").attr("disabled", false);
	    $("#UNDOX").attr("disabled", true);
	    $("#SAVEX").attr("disabled", true);
	    $("#HAPUSX").attr("disabled", false);
	    $("#CLOSEX").attr("disabled", false);

		$("#CARI").attr("readonly", false);	
	    $("#SEARCHX").attr("disabled", false);
		
		
	    $("#PLUSX").attr("hidden", true)
		
	    $(".NO_BUKTI").attr("readonly", true);	
		
		$("#TGL").attr("readonly", true);
		$("#KODEC").attr("readonly", true);
		$("#NAMAC").attr("readonly", true);
		$("#NOTES").attr("readonly", true);

		
		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#NO_FAKTUR" + i.toString()).attr("readonly", true);
			$("#TOTAL" + i.toString()).attr("readonly", true);
			$("#BAYAR" + i.toString()).attr("readonly", true);
			$("#SISA" + i.toString()).attr("readonly", true);
			$("#DELETEX" + i.toString()).attr("hidden", true);
		}


		
	}


	function kosong() {
				
		 $('#NO_BUKTI').val("+");	
	//	 $('#TGL').val("");	
		 $('#KODEC').val("");	
		 $('#NAMAC').val("");		
		 $('#NOTES').val("");	

		 
		var html = '';
		$('#detailx').html(html);	
		
	}
	

	function hapusTrans() {
		let text = "Hapus Transaksi "+$('#NO_BUKTI').val()+"?";
		if (confirm(text) == true) 
		{
			window.location ="{{url('/piu/delete/'.$header->NO_ID .'/?flagz='.$flagz.'' )}}";
			//return true;
		} 
		return false;
	}
	

	function CariBukti() {
		
		var flagz = "{{ $flagz }}";
		var cari = $("#CARI").val();
		var loc = "{{ url('/piu/edit/') }}" + '?idx={{ $header->NO_ID}}&tipx=search&flagz=' + encodeURIComponent(flagz) + '&buktix=' +encodeURIComponent(cari);
		window.location = loc;
		
	}




    function tambah() {

        var x = document.getElementById('datatable').insertRow(baris + 1);
 
		html=`<tr>

                <td>
 					<input name='NO_ID[]' id='NO_ID${idrow}' type='hidden' class='form-control NO_ID' value='new' readonly> 
					<input name='REC[]' id='REC${idrow}' type='text' class='REC form-control' onkeypress='return tabE(this,event)' readonly>
	            </td>
						       
                <td>
				    <input name='NO_FAKTUR[]' onclick="browseJual(${idrow})" data-rowid=${idrow}  id='NO_FAKTUR${idrow}' type='text' class='form-control  NO_FAKTUR' required readonly>
                </td>
				
				<td>
		            <input name='TOTAL[]' onclick='select()' onblur='hitung()' value='0' id='TOTAL${idrow}' type='text' style='text-align: right' class='form-control TOTAL text-primary' required readonly >
                </td>

				<td>
		            <input name='BAYAR[]' onclick='select()' onblur='hitung()' value='0' id='BAYAR${idrow}' type='text' style='text-align: right' class='form-control BAYAR text-primary' required >
                </td>

				<td>
		            <input name='SISA[]' onclick='select()' onblur='hitung()' value='0' id='SISA${idrow}' type='text' style='text-align: right' class='form-control SISA text-primary' required readonly >
                </td>
                
                <td>
					<button type='button' id='DELETEX${idrow}'  class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
                </td>				
         </tr>`;
				
        x.innerHTML = html;
        var html='';
		
		
		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#TOTAL" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});

			$("#BAYAR" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});
			
			$("#SISA" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});			 


		}

		$("#NO_FAKTUR"+idrow).keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseJual(eval($(this).data("rowid")));
			}
		}); 
		

        idrow++;
        baris++;
        nomor();
		
		$(".ronly").on('keydown paste', function(e) {
             e.preventDefault();
             e.currentTarget.blur();
         });
		 
    }
	
	//////////////////////////////////////
	

	
	
	
</script>

@endsection