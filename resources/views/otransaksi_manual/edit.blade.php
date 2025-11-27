@extends('layouts.plain')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
  
                    <form action="{{($tipx=='new')? url('/manual/store/') : url('/manual/update/'.$header->NO_ID ) }}" method="POST" name ="entri" id="entri" >
  
                      @csrf
						
                        <ul class="nav nav-tabs">
                            <li class="nav-item active">
                                <a class="nav-link active" href="#manualInfo" data-toggle="tab">Manual</a>
                            </li>
                        </ul>
        
                        <div class="tab-content mt-3">

							<!-- style text box model baru -->

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


							<div id="manualInfo" class="tab-pane active">
        
                            <div class="form-group row">
 
                                    <input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
                                    placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

									<input name="tipx" class="form-control flagz" id="tipx" value="{{$tipx}}" hidden>

								<!-- code text box baru -->
								<div class="col-md-3 form-group row special-input-label">

									<input type="text" class="NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI" 
										value="{{$header->NO_BUKTI}}" placeholder=" " >
									<label for="NO_BUKTI">No Bukti</label>
								</div>
								<!-- tutupannya -->  

								<div class="col-md-1" align="right">
                                    <label for="TGL" class="form-label">Tgl</label>
                                </div>
                                <div class="col-md-2">
								  <input class="form-control date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}">
                                </div>
							</div>	

							</div> 
							
							<div class="form-group row">

								<div class="col-md-1">	
									<label for="KODEC" class="form-label">Customer#</label>
								</div>
								
								<div class="col-md-3" >
								<select id="KODEC"  name="KODEC" style="width: 100%" ></select>        							 
								
									<input type="text" hidden class="form-control NAMAC" id="NAMAC" name="NAMAC" placeholder="" value="{{$header->NAMAC}}" readonly>
								</div>

                            </div>
        

							<div class="form-group row">
								<!-- code text box baru -->
								<div class="col-md-3 form-group row special-input-label">

									<input type="text" onclick="select()" class="POIN" id="POIN" name="POIN" 
										value="{{$header->POIN}}" placeholder=" " >
									<label for="POIN">Poin</label>
								</div>
								<!-- tutupannya --> 
							</div>
							
							<div class="form-group row">
							<!-- code text box baru -->
								<div class="col-md-3 form-group row special-input-label">

									<input type="text" class="NOTES" id="NOTES" name="NOTES" 
										value="{{$header->NOTES}}" placeholder=" " >
									<label for="NOTES">Notes</label>
								</div>
							<!-- tutupannya -->  
							</div>

							<!-- loader tampil di modal  -->
							<div class="loader" style="z-index: 1055;" id='LOADX' ></div>
 
							
						 </div>

							
							
						</div>
                                
                        </div>
        
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button hidden type="button" id='TOPX'  onclick="location.href='{{url('/manual/edit/?idx=' .$idx. '&tipx=top')}}'" class="btn btn-outline-primary">Top</button>
								<button hidden type="button" id='PREVX' onclick="location.href='{{url('/manual/edit/?idx='.$header->NO_ID.'&tipx=prev&kodex='.$header->KODEC )}}'" class="btn btn-outline-primary">Prev</button>
								<button hidden type="button" id='NEXTX' onclick="location.href='{{url('/manual/edit/?idx='.$header->NO_ID.'&tipx=next&kodex='.$header->KODEC )}}'" class="btn btn-outline-primary">Next</button>
								<button hidden type="button" id='BOTTOMX' onclick="location.href='{{url('/manual/edit/?idx=' .$idx. '&tipx=bottom')}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button hidden type="button" id='NEWX' onclick="location.href='{{url('/manual/edit/?idx=0&tipx=new')}}'" class="btn btn-warning">New</button>
								<button hidden type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button hidden type="button" id='UNDOX' onclick="location.href='{{url('/manual/edit/?idx=' .$idx. '&tipx=undo' )}}'" class="btn btn-info">Undo</button> 
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button hidden type="button" id='HAPUSX' hidden onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								
								<!-- <button type="button" id='CLOSEX'  onclick="location.href='{{url('/manual' )}}'" class="btn btn-outline-secondary">Close</button> -->

								<!-- tombol close sweet alert -->
								<button type="button" id='CLOSEX' onclick="closeTrans()" class="btn btn-outline-secondary">Close</button>
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

	<div class="modal fade" id="browsePegawaiModal" tabindex="-1" role="dialog" aria-labelledby="browsePegawaiModalLabel" aria-hidden="true">
	 <div class="modal-dialog mw-100 w-75" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browsePegawaiModalLabel">Cari Pegawai</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-pegawai">
				<thead>
					<tr>
						<th>Kode</th>
						<th>Nama</th>
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

	<div class="modal fade" id="browseKotaModal" tabindex="-1" role="dialog" aria-labelledby="browseKotaModalLabel" aria-hidden="true">
	 <div class="modal-dialog mw-100 w-75" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseKotaModalLabel">Cari Kota</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-kota">
				<thead>
					<tr>
						<th>Kota</th>
						<th>Ring</th>
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

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>


<!-- tambahan untuk sweetalert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- tutupannya -->

<script>
    var target;
	var idrow = 1;

	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}

    $(document).ready(function () {

		setTimeout(function(){

		$("#LOADX").hide();

		},500);	

		$('#KODEC').select2({
		
		placeholder:'Pilih Customer',
		allowClear: true,
        ajax: {
			url: '{{url('cust/browse')}}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term // Search term
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(item => ({
                        id: item.KODEC, // The ID of the user
                        text: item.NAMAC // The text to display
                    }))
                };
            },
            cache: true
        },
		
		
		
	});

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
		}

        if ( $tipx != 'new' )
		{
			 //mati();	
    		 ganti();

			 
			var initkode1 ="{{ $header->KODEC }}";                
			var initcombo1 ="{{ $header->NAMAC }}";
			var defaultOption1 = { id: initkode1, text: initcombo1 }; // Set your default option ID and text
			var newOption1 = new Option(defaultOption1.text, defaultOption1.id, true, true);
			$('#KODEC').append(newOption1).trigger('change');
			
		} 

		
		$('.date').datepicker({  
            dateFormat: 'dd-mm-yy'
		});

		

		$("#POIN").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		// $("#HARI").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999'});
		
    });

	//////////////////////////////////////////////////////////////////////////////////////////////////


		//CHOOSE Pegawai
		var dTableBPegawai;
		loadDataBPegawai = function(){
			$.ajax(
			{
				type: 'GET',    
				url: '{{url('pegawai/browse')}}',

				// beforeSend: function(){
				// 	$("#LOADX").show();
				// },

				success: function( response )
				{
					// $("#LOADX").hide();
			
					resp = response;
					if(dTableBPegawai){
						dTableBPegawai.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBPegawai.row.add([
							'<a href="javascript:void(0);" onclick="choosePegawai(\''+resp[i].KODEP+'\',  \''+resp[i].NAMAP+'\' )">'+resp[i].KODEP+'</a>',
							resp[i].NAMAP,
						]);
					}
					dTableBPegawai.draw();
				}
			});
		}
		
		dTableBPegawai = $("#table-pegawai").DataTable({
			
		});
		
		browsePegawai = function(){
			loadDataBPegawai();
			$("#browsePegawaiModal").modal("show");
		}
		
		choosePegawai = function(KODEP,NAMAP){
			$("#KODEP").val(KODEP);
			$("#NAMAP").val(NAMAP);
			$("#browsePegawaiModal").modal("hide");
		}
		
		$("#KODEP").keypress(function(e){

			if(e.keyCode == 46){
				e.preventDefault();
				browsePegawai();
			}
		}); 
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////


		//CHOOSE Kota
		var dTableBKota;
		loadDataBKota = function(){
			$.ajax(
			{
				type: 'GET',    
				url: '{{url('kota/browse')}}',

				success: function( response )
				{
			
					resp = response;
					if(dTableBKota){
						dTableBKota.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBKota.row.add([
							'<a href="javascript:void(0);" onclick="chooseKota(\''+resp[i].KOTA+'\',  \''+resp[i].RING+'\' )">'+resp[i].KOTA+'</a>',
							resp[i].RING,
						]);
					}
					dTableBKota.draw();
				}
			});
		}
		
		dTableBKota = $("#table-kota").DataTable({
			
		});
		
		browseKota = function(){
			loadDataBKota();
			$("#browseKotaModal").modal("show");
		}
		
		chooseKota = function(KOTA,RING){
			$("#KOTA").val(KOTA);
			$("#browseKotaModal").modal("hide");
		}
		
		$("#KOTA").keypress(function(e){

			if(e.keyCode == 46){
				e.preventDefault();
				browseKota();
			}
		}); 
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////////


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
		
		
 		// $tipx = $('#tipx').val();
		
        // if ( $tipx == 'new' )		
		// {	
		  	
		// 	$("#NO_BUKTI").attr("readonly", false);	
		// 	$("#INDUK").attr("readonly", false);	
		// 	$("#NO_MEMBER").attr("readonly", false);	

		//    }
		// else
		// {
		// 	$("#NO_BUKTI").attr("readonly", false);	
		// 	$("#INDUK").attr("readonly", false);	
	    //  	$("#NO_MEMBER").attr("readonly", true);	

		// }
		   
		
		$("#NO_BUKTI").attr("readonly", true);	
		$("#TGL").attr("disabled", false);			
		$("#KODEC").attr("readonly", false);		
		$("#NAMAC").attr("readonly", false);			
		$("#POIN").attr("readonly", false);	
		$("#NOTES").attr("readonly", false);			
		$("#AKT").attr("readonly", false);		
		$('#KONTAK').attr("readonly", false);
		
		//document.getElementById("KET").disabled = false;
		
	
	
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
		
		$("#NO_BUKTI").attr("readonly", true);	
		$("#TGL").attr("disabled", true);			
		$("#KODEC").attr("readonly", true);		
		$("#NAMAC").attr("readonly", true);			
		$("#POIN").attr("readonly", true);	
		$("#NOTES").attr("readonly", true);			
		$("#AKT").attr("readonly", true);		
		$('#KONTAK').attr("readonly", true);
		
	}


	function kosong() {
				
		 $('#KODEC').val("");	
		 $('#NAMAC').val("");	
		 $('#ALAMAT').val("");	
		 $('#KOTA').val("");		

		 $('#TELPON1').val("");	
		 $('#FAX').val("");	
		 $('#HP').val("");	
		 $('#AKT').val("0");		
		 $('#KONTAK').val("");

		 $('#EMAIL').val("");	
		 $('#NPWP').val("");	
		 $('#KET').val("");	

		 $('#POIN').val("0");

		 
	}
	
	// function hapusTrans() {
	// 	let text = "Hapus Master "+$('#KODEC').val()+"?";
	// 	if (confirm(text) == true) 
	// 	{
	// 		window.location ="{{url('/member/delete/'.$header->NO_ID )}}'";
	// 		//return true;
	// 	} 
	// 	return false;
	// }

	// sweetalert untuk tombol hapus dan close
	
	function hapusTrans() {
		let text = "Hapus Transaksi "+$('#NO_BUKTI').val()+"?";

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
	            	loc = "{{ url('/manual/delete/'.$header->NO_ID) }}"  ;

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
	        	loc = "{{ url('/manual/') }}" ;
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
		var loc = "{{ url('/member/edit/') }}" + '?idx={{ $header->NO_ID}}&tipx=search&kodex=' +encodeURIComponent(cari);
		window.location = loc;
		
	}
	

     var hasilCek;
	function cekCust(kodec) {
		$.ajax({
			type: "GET",
			url: "{{url('member/cekmember')}}",
            async: false,
			data: ({ KODEC: kodec, }),
			success: function(data) {
                if (data.length > 0) {
                    $.each(data, function(i, item) {
                        hasilCek=data[i].ADA;
                    });
                }
			},
			error: function() {
				alert('Error cekCust occured');
			}
		});
		return hasilCek;
	}
    
	function simpan() {

		var tgl = $('#TGL').val();
		var bulanPer = {{session()->get('periode')['bulan']}};
		var tahunPer = {{session()->get('periode')['tahun']}};
		
        var check = '0';

		if ( tgl.substring(3,5) != bulanPer ) 
			{
				
				check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Bulan tidak sama dengan Periode'
				});
				return; // Stop function execution
				alert("Bulan tidak sama dengan Periode");
			}	
			

			if ( tgl.substring(tgl.length-4) != tahunPer )
			{
				check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Tahun tidak sama dengan Periode'
				});
				return; // Stop function execution
				
		    }	

			if (check == '0') {
				Swal.fire({
					title: 'Are you sure?',
					text: 'Are you sure you want to save?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Yes, save it!',
					cancelButtonText: 'No, cancel',
				}).then((result) => {
					if (result.isConfirmed) {
						document.getElementById("entri").submit();
					} else {
						Swal.fire({
							icon: 'info',
							title: 'Cancelled',
							text: 'Your data was not saved'
						});
					}
				});
			} else {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Masih ada kesalahan'
				});
			}

		// tutupannya	
			
	      $("#LOADX").hide();
	}
</script>
@endsection

