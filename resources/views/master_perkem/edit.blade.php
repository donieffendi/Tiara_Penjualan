@extends('layouts.plain')
<style>
    .card {

    }

    .form-control:focus {
        background-color: #E0FFFF !important;
    }

	/* perubahan tab warna di form edit  */
	.nav-item .nav-link.active {
		background-color: red !important; /* Use !important to ensure it overrides */
		color: white !important;
	}
/* query LOADX */

.loader {
      position: fixed;
        top: 50%;
        left: 50%;
      width: 100px;
      aspect-ratio: 1;
      background:
        radial-gradient(farthest-side,#ffa516 90%,#0000) center/16px 16px,
        radial-gradient(farthest-side,green   90%,#0000) bottom/12px 12px;
      background-repeat: no-repeat;
      animation: l17 1s infinite linear;
      position: relative;
    }
    .loader::before {    
      content:"";
      position: absolute;
      width: 8px;
      aspect-ratio: 1;
      inset: auto 0 16px;
      margin: auto;
      background: #ccc;
      border-radius: 50%;
      transform-origin: 50% calc(100% + 10px);
      animation: inherit;
      animation-duration: 0.5s;
    }
    @keyframes l17 { 
      100%{transform: rotate(1turn)}
    }

	/* penutup LOADX */

	/* menghilangkan padding */
	.content-header {
		padding: 0 !important;
	}


</style>

@section('content')
<div class="content-wrapper">

    <div class="content">
        <div class="container-fluid">
        <div class="row">
            <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <form action="{{($tipx=='new')? url('/brg/store/') : url('/brg/update/'.$header->NO_ID ) }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
                        {{-- <ul class="nav nav-tabs">
                            <li class="nav-item active">
                                <a class="nav-link active" href="#data" data-toggle="tab">Data</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#dokumen" data-toggle="tab">Nilai</a>
                            </li>
                        </ul> --}}
        
                        <div class="tab-content mt-3">

							<!-- style textbox model baru -->
						<style>
								/* Ensure specificity with class targeting */
								.form-group.special-input-label {
									position: relative;
									margin-left: 5px ;
								}
						
								/* Ensure only bottom border for input */
								.form-group.special-input-label input {
									width: 100%;
									padding: 10px 0;
									border: none !important;
									border-bottom: 2px solid #ccc !important;
									outline: none !important;
									font-size: 16px !important;
									background: transparent !important; /* Remove any background color */
								}
						
								/* Bottom border color change on focus */
								.form-group.special-input-label input:focus {
									border-bottom: 2px solid #007BFF !important; /* Change color on focus */
								}
						
								/* Style the label with a higher specificity */
								.form-group.special-input-label label {
									position: absolute;
									top: 12px;
									color: #888 !important;
									font-size: 16px !important;
									transition: 0.3s ease all;
									pointer-events: none;
								}
						
								/* Move label above input when focused or has content */
								.form-group.special-input-label input:focus + label,
								.form-group.special-input-label input:not(:placeholder-shown) + label {
									top: -10px !important;
									font-size: 12px !important;
									color: #007BFF !important;
								}
							</style>
						<!-- tutupannya -->

                            <div class="form-group row">

                                    <input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
                                    placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

									<input name="tipx" class="form-control flagz" id="tipx" value="{{$tipx}}" hidden>
		 							

								<!-- code text box baru -->
								<div class="col-md-2 form-group row special-input-label">

									<input type="text" class="KD_BRG" id="KD_BRG" name="KD_BRG" 
										value="{{$header->KD_BRG}}" placeholder=" " readonly>
									<label for="KD_BRG">Kode</label>
								</div>
								<!-- tutupannya -->

								<div class="col-md-1">
								</div>

								<!-- code text box baru -->
								<div class="col-md-3 form-group row special-input-label">

									<input type="text" class="NA_BRG" id="NA_BRG" name="NA_BRG" 
										value="{{$header->NA_BRG}}" placeholder=" " >
									<label for="NA_BRG">Nama</label>
								</div>
								<!-- tutupannya -->

                            </div>

							
                            <div class="form-group row">

								<!-- code text box baru -->
								<div class="col-md-2 form-group row special-input-label">

									<input type="text" class="KODES" id="KODES" name="KODES" 
										value="{{$header->KODES}}" placeholder=" " >
									<label for="KODES">Supplier *(Pilih)</label>
								</div>
								<div class="col-md-1 form-group row special-input-label">
									<button type="button" class="btn btn-primary" onclick="browseSuplier()" style="width:40px"><i class="fa fa-search"></i></button>
								</div>
								<!-- tutupannya -->
        
								<div class="col-md-2 form-group row special-input-label">
									<input type="text" class="NAMAS" id="NAMAS" name="NAMAS" 
										value="{{$header->NAMAS}}" placeholder=" " >
									<label for="NAMAS"></label>
								</div>
                            </div>

							<!-- loader tampil di modal  -->
							<div class="loader" style="z-index: 1055;" id='LOADX' ></div>
                        </div>

						
        
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button hidden type="button" id='TOPX'  onclick="location.href='{{url('/brg/edit/?idx=' .$idx. '&tipx=top')}}'" class="btn btn-outline-primary">Top</button>
								<button hidden type="button" id='PREVX' onclick="location.href='{{url('/brg/edit/?idx='.$header->NO_ID.'&tipx=prev&kodex='.$header->KD_BRG )}}'" class="btn btn-outline-primary">Prev</button>
								<button hidden type="button" id='NEXTX' onclick="location.href='{{url('/brg/edit/?idx='.$header->NO_ID.'&tipx=next&kodex='.$header->KD_BRG )}}'" class="btn btn-outline-primary">Next</button>
								<button hidden type="button" id='BOTTOMX' onclick="location.href='{{url('/brg/edit/?idx=' .$idx. '&tipx=bottom')}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button hidden type="button" id='NEWX' onclick="location.href='{{url('/brg/edit/?idx=0&tipx=new')}}'" class="btn btn-warning">New</button>
								<button hidden type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button hidden type="button" id='UNDOX' onclick="location.href='{{url('/brg/edit/?idx=' .$idx. '&tipx=undo' )}}'" class="btn btn-info">Undo</button> 
								<button type="button" id='SAVEX' onclick='simpan()' class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button hidden type="button" id='HAPUSX' hidden onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								
								<!-- <button type="button" id='CLOSEX'  onclick="location.href='{{url('/brg' )}}'" class="btn btn-outline-secondary">Close</button> -->

								<!-- tombol close sweet alert -->
								<button type="button" id='CLOSEX' onclick="closeTrans()" class="btn btn-outline-secondary">Close</button></div>
							</div>
						</div>		
		
                    </form>
                </div>
            </div>
            <!-- /.card -->
            </div>
        </div>
        <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->


	<div class="modal fade" id="browseSuplierModal" tabindex="-1" role="dialog" aria-labelledby="browseSuplierModalLabel" aria-hidden="true">
	 <div class="modal-dialog mw-100 w-75" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseSuplierModalLabel">Cari Suplier</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bsuplier">
				<thead>
					<tr>
						<th>Suplier</th>
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
<!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>

<!-- tambahan untuk sweetalert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- tutupannya -->

<script>

	var idrow = 1;
	var baris = 1;

	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}


    $(document).ready(function () {

		setTimeout(function(){

		$("#LOADX").hide();

		},500);

		
		$('body').on('keydown', 'input, select', function(e) {
			if (e.key === "Enter") {
				var self = $(this), form = self.parents('form:eq(0)'), focusable, next;
				focusable = form.find('input,select,textarea').filter(':visible');
				next = focusable.eq(focusable.index(this)+1);
				console.log(next);
				if (next.length) {
					next.focus().select();
				} else {
					// tambah();
					// var nomer = idrow-1;
					// console.log("REC"+nomor);
					// document.getElementById("REC"+nomor).focus();
					// form.submit();
				}
				return false;
			}
		});
		
 		$tipx = $('#tipx').val();
				
        if ( $tipx == 'new' )
		{
			 baru();	
             tambah();
			 
			 $("#RING0").val('LOKAL');
			 tambah();
			 $("#RING1").val('1');
			 tambah();
			 $("#RING2").val('2');
			 tambah();
			 $("#RING3").val('3');
			 
		}

        if ( $tipx != 'new' )
		{
			 //mati();	
    		 ganti();
		} 

		$('body').on('click', '.del', function() {
			var val = $(this).parents("tr").remove();
			baris--;
			nomor();
			
		});

		$('.date').datepicker({  
            dateFormat: 'dd-mm-yy'
		});

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////			
		
		
		//CHOOSE Supplier
		var dTableBSuplier;
		loadDataBSuplier = function(){
			$.ajax(
			{
				type: 'GET',    
				url: '{{url('sup/browse')}}',

				success: function( response )
				{
			
					resp = response;
					if(dTableBSuplier){
						dTableBSuplier.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBSuplier.row.add([
							'<a href="javascript:void(0);" onclick="chooseSuplier(\''+resp[i].KODES+'\',  \''+resp[i].NAMAS+'\', \''+resp[i].ALAMAT+'\',  \''+resp[i].KOTA+'\')">'+resp[i].KODES+'</a>',
							resp[i].NAMAS,
							resp[i].ALAMAT,
							resp[i].KOTA,
						]);
					}
					dTableBSuplier.draw();
				}
			});
		}
		
		dTableBSuplier = $("#table-bsuplier").DataTable({
			
		});
		
		browseSuplier = function(){
			loadDataBSuplier();
			$("#browseSuplierModal").modal("show");
		}
		
		chooseSuplier = function(KODES,NAMAS, ALAMAT, KOTA){
			$("#KODES").val(KODES);
			$("#NAMAS").val(NAMAS);
			$("#ALAMAT").val(ALAMAT);
			$("#KOTA").val(KOTA);
			$("#browseSuplierModal").modal("hide");
		}
		
		$("#KODES").keypress(function(e){

			if(e.keyCode == 46){
				e.preventDefault();
				browseSuplier();
			}
		}); 
		
		
//////////////////////////////////////////////////////////////////////////////////////////////////


		// //CHOOSE Acno
		// var dTableBAcno;
		// loadDataBAcno = function(){
		// 	$.ajax(
		// 	{
		// 		type: 'GET',    
		// 		url: '{{url('account/browse')}}',

		// 		beforeSend: function(){
		// 			$("#LOADX").show();
		// 		},

		// 		success: function( response )
		// 		{
		// 			$("#LOADX").hide();
			
		// 			resp = response;
		// 			if(dTableBAcno){
		// 				dTableBAcno.clear();
		// 			}
		// 			for(i=0; i<resp.length; i++){
						
		// 				dTableBAcno.row.add([
		// 					'<a href="javascript:void(0);" onclick="chooseAcno(\''+resp[i].ACNO+'\',  \''+resp[i].NAMA+'\' )">'+resp[i].ACNO+'</a>',
		// 					resp[i].NAMA,
		// 				]);
		// 			}
		// 			dTableBAcno.draw();
		// 		}
		// 	});
		// }
		
		// dTableBAcno = $("#table-bacno").DataTable({
			
		// });
		
		// browseAcno = function(){
		// 	loadDataBAcno();
		// 	$("#browseAcnoModal").modal("show");
		// }
		
		// chooseAcno = function(ACNO,NAMA){
		// 	$("#ACNOA").val(ACNO);
		// 	$("#NACNOA").val(NAMA);
		// 	$("#browseAcnoModal").modal("hide");
		// }
		
		// $("#ACNOA").keypress(function(e){

		// 	if(e.keyCode == 46){
		// 		e.preventDefault();
		// 		browseAcno();
		// 	}
		// }); 
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////////

		//////////////////////////////////////////////////////////////////////////////////////////////////


		// //CHOOSE Acno
		// var dTableBAcnob;
		// loadDataBAcnob = function(){
		// 	$.ajax(
		// 	{
		// 		type: 'GET',    
		// 		url: '{{url('account/browse')}}',

		// 		beforeSend: function(){
		// 			$("#LOADX").show();
		// 		},

		// 		success: function( response )
		// 		{
		// 			$("#LOADX").hide();
			
		// 			resp = response;
		// 			if(dTableBAcnob){
		// 				dTableBAcnob.clear();
		// 			}
		// 			for(i=0; i<resp.length; i++){
						
		// 				dTableBAcnob.row.add([
		// 					'<a href="javascript:void(0);" onclick="chooseAcnob(\''+resp[i].ACNO+'\',  \''+resp[i].NAMA+'\' )">'+resp[i].ACNO+'</a>',
		// 					resp[i].NAMA,
		// 				]);
		// 			}
		// 			dTableBAcnob.draw();
		// 		}
		// 	});
		// }
		
		// dTableBAcnob = $("#table-bacnob").DataTable({
			
		// });
		
		// browseAcnob = function(){
		// 	loadDataBAcnob();
		// 	$("#browseAcnobModal").modal("show");
		// }
		
		// chooseAcnob = function(ACNO,NAMA){
		// 	$("#ACNOB").val(ACNO);
		// 	$("#NACNOB").val(NAMA);
		// 	$("#browseAcnobModal").modal("hide");
		// }
		
		// $("#ACNOB").keypress(function(e){

		// 	if(e.keyCode == 46){
		// 		e.preventDefault();
		// 		browseAcnob();
		// 	}
		// }); 
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////


		// //CHOOSE Grup
		// var dTableBGrup;
		// loadDataBGrup = function(){
		// 	$.ajax(
		// 	{
		// 		type: 'GET',    
		// 		url: '{{url('grup/browse')}}',

		// 		success: function( response )
		// 		{
			
		// 			resp = response;
		// 			if(dTableBGrup){
		// 				dTableBGrup.clear();
		// 			}
		// 			for(i=0; i<resp.length; i++){
						
		// 				dTableBGrup.row.add([
		// 					'<a href="javascript:void(0);" onclick="chooseGrup(\''+resp[i].KODE+'\',  \''+resp[i].NAMA+'\' )">'+resp[i].KODE+'</a>',
		// 					resp[i].NAMA,
		// 				]);
		// 			}
		// 			dTableBGrup.draw();
		// 		}
		// 	});
		// }
		
		// dTableBGrup = $("#table-bgrup").DataTable({
			
		// });
		
		// browseGrup = function(){
		// 	loadDataBGrup();
		// 	$("#browseGrupModal").modal("show");
		// }
		
		// chooseGrup = function(KODE,NAMA){
		// 	$("#KD_GRUP").val(KODE);
		// 	$("#NA_GRUP").val(NAMA);
		// 	$("#browseGrupModal").modal("hide");
		// }
		
		// $("#KD_GRUP").keypress(function(e){

		// 	if(e.keyCode == 46){
		// 		e.preventDefault();
		// 		browseGrup();
		// 	}
		// }); 
		
		
//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////


		// //CHOOSE Lokasi
		// var dTableBLokasi;
		// loadDataBLokasi = function(){
		// 	$.ajax(
		// 	{
		// 		type: 'GET',    
		// 		url: '{{url('lokasi/browse')}}',

		// 		success: function( response )
		// 		{
			
		// 			resp = response;
		// 			if(dTableBLokasi){
		// 				dTableBLokasi.clear();
		// 			}
		// 			for(i=0; i<resp.length; i++){
						
		// 				dTableBLokasi.row.add([
		// 					'<a href="javascript:void(0);" onclick="chooseLokasi( \''+resp[i].NAMA+'\' )">'+resp[i].KODE+'</a>',
		// 					resp[i].NAMA,
		// 				]);
		// 			}
		// 			dTableBLokasi.draw();
		// 		}
		// 	});
		// }
		
		// dTableBLokasi = $("#table-blokasi").DataTable({
			
		// });
		
		// browseLokasi = function(){
		// 	loadDataBLokasi();
		// 	$("#browseLokasiModal").modal("show");
		// }
		
		// chooseLokasi = function(NAMA){
		// 	$("#LOKASI").val(NAMA);
		// 	$("#browseLokasiModal").modal("hide");
		// }
		
		// $("#LOKASI").keypress(function(e){

		// 	if(e.keyCode == 46){
		// 		e.preventDefault();
		// 		browseLokasi();
		// 	}
		// }); 
		
		
//////////////////////////////////////////////////////////////////////////////////////////////////
		
//////////////////////////////////////////////////////////////////////////////////////////////////


		// //CHOOSE Komisi
		// var dTableBKomisi;
		// loadDataBKomisi = function(){
		// 	$.ajax(
		// 	{
		// 		type: 'GET',    
		// 		url: '{{url('komisi/browse')}}',

		// 		beforeSend: function(){
		// 			$("#LOADX").show();
		// 		},

		// 		success: function( response )
		// 		{
		// 			$("#LOADX").hide();
			
		// 			resp = response;
		// 			if(dTableBKomisi){
		// 				dTableBKomisi.clear();
		// 			}
		// 			for(i=0; i<resp.length; i++){
						
		// 				dTableBKomisi.row.add([
		// 					'<a href="javascript:void(0);" onclick="chooseKomisi(\''+resp[i].TYPE+'\',  \''+resp[i].KOM+'\' )">'+resp[i].TYPE+'</a>',
		// 					resp[i].KOM,
		// 				]);
		// 			}
		// 			dTableBKomisi.draw();
		// 		}
		// 	});
		// }
		
		// dTableBKomisi = $("#table-bkomisi").DataTable({
			
		// });
		
		// browseKomisi = function(){
		// 	loadDataBKomisi();
		// 	$("#browseKomisiModal").modal("show");
		// }
		
		// chooseKomisi = function(TYPE,KOM){
		// 	$("#TYPE_KOM").val(TYPE);
		// 	$("#KOM").val(KOM);
		// 	$("#browseKomisiModal").modal("hide");
		// }
		
		// $("#TYPE_KOM").keypress(function(e){

		// 	if(e.keyCode == 46){
		// 		e.preventDefault();
		// 		browseKomisi();
		// 	}
		// }); 
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////////


		
    });

	function nomor() {
		var i = 1;
		$(".REC").each(function() {
			$(this).val(i++);
		});
		
	//	hitung();
	
	}

	function hitung() {
		// var TTOTAL_QTY = 0;
		// // var TTOTAL = 0;


		
		// $(".QTY").each(function() {
			
		// 	let z = $(this).closest('tr');
		// 	var QTYX = parseFloat(z.find('.QTY').val().replace(/,/g, ''));
		// 	// var HARGAX = parseFloat(z.find('.HARGA').val().replace(/,/g, ''));
	
	
        //     // var TOTALX  =  ( QTYX * HARGAX );
		// 	// z.find('.TOTAL').val(TOTALX);

		//     // z.find('.HARGA').autoNumeric('update');			
		//     // z.find('.QTY').autoNumeric('update');	
		//     // z.find('.TOTAL').autoNumeric('update');			

        //     TTOTAL_QTY +=QTYX;		
        //     // TTOTAL +=TOTALX;				
		
		// });
		

		
		// if(isNaN(TTOTAL_QTY)) TTOTAL_QTY = 0;

		// $('#TTOTAL_QTY').val(numberWithCommas(TTOTAL_QTY));		
		// $("#TTOTAL_QTY").autoNumeric('update');
		
		// if(isNaN(TTOTAL)) TTOTAL = 0;

		// $('#TTOTAL').val(numberWithCommas(TTOTAL));		
		// $("#TTOTAL").autoNumeric('update');



		
	}

	function baru() {
		
		 kosong();
		 hidup();
		 
	}
	
	function ganti() {
		
		// mati();
		hidup();
	
	}
	
	function batal() {
			
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
	    $("#CLOSEX").attr("disabled", false);
		
		
 		$tipx = $('#tipx').val();
		
        if ( $tipx == 'new' )		
		{	
		  	
			$("#KD_BRG").attr("readonly", true);	

		   }
		else
		{
	     	$("#KD_BRG").attr("readonly", true);	

		}
		   
		
		$("#NA_BRG").attr("readonly", false);
		$("#KODES").attr("readonly", true);
		$("#NAMAS").attr("readonly", true);		
	
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
		
		$("#KD_BRG").attr("readonly", true);			
		$("#NA_BRG").attr("readonly", true);

		$("#KODES").attr("readonly", true);
		$("#NAMAS").attr("readonly", true)
		
	}


	function kosong() {
				
		 $('#KD_BRG').val("+");	
		 $('#NA_BRG').val("");	
		 $('#KODES').val("");	
		 $('#NAMAS').val("");		 
	}
	
	// function hapusTrans() {
	// 	let text = "Hapus Master "+$('#KD_BRG').val()+"?";
	// 	if (confirm(text) == true) 
	// 	{
	// 		window.location ="{{url('/brg/delete/'.$header->NO_ID )}}'";
	// 		//return true;
	// 	} 
	// 	return false;
	// }

	// sweetalert untuk tombol hapus dan close
	
	function hapusTrans() {
		let text = "Apakah Kode ini "+$('#KD_BRG').val()+"Akan Dihapus ?";

		var loc ='';
		
		Swal.fire({
			title: 'Are you sure?',
			text: text,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, delete it!',
			cancelButtonText: 'Cancel'
		}).then((result) => {
			if (result.isConfirmed) {
				// Show a success message before redirecting to delete the data
				Swal.fire({
					title: 'Deleted!',
					text: 'Data has been deleted.',
					icon: 'success',
					confirmButtonText: 'OK'
				}).then(() => {
					// Redirect to delete the data after user confirms the success message
	            	loc = "{{ url('/brg/delete/'.$header->NO_ID) }}"  ;

		            // alert(loc);
	            	window.location = loc;
		
				});
			}
		});
	}
	
	function closeTrans() {
		console.log("masuk");
		var loc ='';
		
		Swal.fire({
			title: 'Are you sure?',
			text: 'Do you really want to close this page? Unsaved changes will be lost.',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes, close it',
			cancelButtonText: 'No, stay here'
		}).then((result) => {
			if (result.isConfirmed) {
	        	loc = "{{ url('/brg/') }}" ;
				window.location = loc ;
			} else {
				Swal.fire({
					icon: 'info',
					title: 'Cancelled',
					text: 'You stayed on the page'
				});
			}
		});
	}

	// tutupannya

	function CariBukti() {
		
		var cari = $("#CARI").val();
		var loc = "{{ url('/brg/edit/') }}" + '?idx={{ $header->NO_ID}}&tipx=search&kodex=' +encodeURIComponent(cari);
		window.location = loc;
		
	}

    var hasilCek;

	function cekBarang(kdbrg) {
		$.ajax({
			type: "GET",
			url: "{{url('brg/cekbarang')}}",
            async: false,
			data: ({ KD_BRG: kdbrg, }),
			success: function(data) {
                // hasilCek=data;
                if (data.length > 0) {
                    $.each(data, function(i, item) {
                        hasilCek=data[i].ADA;
                    });
                }
			},
			error: function() {
				alert('Error cekBarang occured');
			}
		});
		return hasilCek;
	}

   


	function simpan() {
		
        // var hasilCek = '0';

		
		// 	$tipx = $('#tipx').val();
					
		// 	if ( $tipx == 'new' )
		// 	{
		// 		cekBarang($('#KD_BRG').val());		
		// 	}
		
		// 	if (hasilCek == '0') {
		// 		Swal.fire({
		// 			title: 'Are you sure?',
		// 			text: 'Are you sure you want to save?',
		// 			icon: 'question',
		// 			showCancelButton: true,
		// 			confirmButtonText: 'Yes, save it!',
		// 			cancelButtonText: 'No, cancel',
		// 		}).then((result) => {
		// 			if (result.isConfirmed) {
		// 				document.getElementById("entri").submit();
		// 			} else {
		// 				Swal.fire({
		// 					icon: 'info',
		// 					title: 'Cancelled',
		// 					text: 'Your data was not saved'
		// 				});
		// 			}
		// 		});
		// 	} else {
		// 		Swal.fire({
		// 			icon: 'error',
		// 			title: 'Error',
		// 			text: 'Masih ada kesalahan'
		// 		});
		// 	}

		// tutupannya	

			hasilCek=0;
			$tipx = $('#tipx').val();
			
			if ( $('#KD_BRG').val()=='' ) 
            {				
			    hasilCek = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Kode Barang# Harus Diisi.'
				});
				return; // Stop function execution
			}

			if ( $('#NA_BRG').val()=='' ) 
            {				
			    hasilCek = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Nama Barang# Harus Diisi.'
				});
				return; // Stop function execution
			}


			if ( $tipx == 'new' )
			{
				cekBarang($('#KD_BRG').val());		
			}
			

			(hasilCek==0) ? document.getElementById("entri").submit() : alert('Barang '+$('#KD_BRG').val()+' sudah ada!');
		
	
			$("#LOADX").hide();
	}


	function tambah() {
	}
</script>
@endsection