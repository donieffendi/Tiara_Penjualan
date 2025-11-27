@extends('layouts.plain')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .card {

    }

	
    .form-control:focus {
        background-color: #b5e5f9 !important;
    }
	
	.table-scrollable {
		margin: 0;
		padding: 0;
	}

	table {
		table-layout: fixed !important;
	}

	.uppercase {
		text-transform: uppercase;
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
	

    /* style tambahan baru */
    .form-control:disabled,
    .form-control[readonly] {
        background-color: #f7d8b4 !important;
        opacity: 1;
    }

    .row {
        margin-bottom: 8px !important;
    }

	/* menghilangkan padding */
	.content-header {
		padding: 0 !important;
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

                    <form action="{{($tipx=='new')? url('/orderpbl/store?flagz='.$flagz.'&golz='.$golz.'') : url('/orderpbl/update/'.$header->NO_ID.'?flagz='.$flagz.'&golz='.$golz.'' ) }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
                        <div class="tab-content mt-3">

                            <div class="form-group row">
                                <div class="col-md-1" align="right">
                                    <label for="no_bukti" class="form-label">Bukti#</label>
                                </div>
								

                                   <input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
                                    placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

									<input name="tipx" class="form-control tipx" id="tipx" value="{{$tipx}}" hidden>
									<input name="flagz" class="form-control flagz" id="flagz" value="{{$flagz}}" hidden>
									<input name="golz" class="form-control golz" id="golz" value="{{$golz}}" hidden>

								
								
                                <div class="col-md-2">
                                    <input type="text" class="form-control no_bukti" id="no_bukti" name="no_bukti"
                                    placeholder="Masukkan Bukti#" value="{{$header->no_bukti}}" readonly>
                                </div>

								<div class="col-md-3"></div>

								<div class="col-md-1" align="right">								
									<label for="kodes" class="form-label">Pesan Ke Supplier</label>
								</div>
								<div class="col-md-2 input-group" >
									<input type="text" class="form-control kodes" id="kodes" name="kodes" placeholder="Kode Sup"value="{{$header->kodes}}" style="text-align: left">
									<label>s/d</label>
									<input type="text" class="form-control kodes2" id="kodes2" name="kodes2" placeholder="Kode Sup"value="{{$header->kodes2}}" style="text-align: left">
								</div>

								<div class="col-md-3">
									<input type="text" class="form-control namas" id="namas" name="namas" placeholder="Nama Sup"value="{{$header->namas}}" style="text-align: left">
								</div>
							</div>

							<div class="form-group row">
                                <div class="col-md-1" align="right">
                                    <label for="tgl" class="form-label">tgl</label>
                                </div>
                                <div class="col-md-2">
								  <input class="form-control date" id="tgl" name="tgl" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->tgl))}}">
                                </div>

								<div class="col-md-3"></div>

								<div class="col-md-1" align="right">								
									<label for="LH" class="form-label">Untuk LH</label>
								</div>
								<div class="col-md-2 input-group">
                                    <input type="text" class="form-control LPH1" id="LPH1" name="LPH1" placeholder="0.00" value="{{$header->LPH1}}">
									<label>s/d</label>
									<input type="text" class="form-control LPH2" id="LPH2" name="LPH2" placeholder="0.00" value="{{$header->LPH2}}">
                                </div>
                            </div>
		
							</div>
							
							<div class="form-group row">
								<div class="col-md-1" align="right">									
									<label for="notes" class="form-label">Notes</label>
								</div>
								<div class="col-md-5">
                                    <input type="text" class="form-control notes" id="notes" name="notes"
                                    placeholder="Masukkan notes Jika Ada" value="{{$header->notes}}">
                                </div>

								<div class="col-md-1" align="right">								
									<label for="HARI" class="form-label">Untuk Kebutuhan</label>
								</div>
								<div class="col-md-2">
                                    <input type="text" class="form-control HARI" id="HARI" name="HARI"
                                    placeholder="HARI" value="{{$header->HARI}}">
                                </div>
							</div>

							<div class="form-group row">
								<div class="col-md-6"></div>

								<div class="col-md-1" align="right">								
									<label for="SUB" class="form-label">Sub</label>
								</div>
								<div class="col-md-2 input-group">
                                    <input type="text" class="form-control SUB1" id="SUB1" name="SUB1" placeholder="Sub" value="{{$header->SUB1}}">
									<label>s/d</label>
									<input type="text" class="form-control SUB2" id="SUB2" name="SUB2" placeholder="Sub" value="{{$header->SUB2}}">
                                </div>
							</div>

							<div class="form-group row">
								<div class="col-md-2 offset-md-7">
									<button type="button" class="btn btn-success w-100" id="btnProses" name="btnProses" onclick="proses()">
										<i class="fa fa-download"></i> Proses
									</button>
								</div>
							</div>

							
							<!-- loader tampil di modal  -->
							<div class="loader" style="z-index: 1055;" id='LOADX' ></div>
							<!-- batas load -->

							<div class="table-responsive">	
								<!-- <table id="datatable" class="table table-striped table-border table-scrollable">                 -->
								<table id="datatable" class="table table-striped table-border">   

									<thead>
										<tr>
											<th width="75px" style="text-align:center">No.</th>	
											<th width="100px" style="text-align:center">Supplier</th>										
											<th width="100px" style="text-align:center">Kode</th>
											<th width="300px" style="text-align:center">Nama Barang</th>
											<th width="100px" style="text-align:center">Ukuran</th> 
											<th width="100px" style="text-align:center">Kemasan</th>
											<th width="100px" style="text-align:center">Qty</th>
											<th width="100px" style="text-align:center">LPH</th>
											<th width="100px" style="text-align:center">Smin</th>
											<th width="100px" style="text-align:center">Sa</th>
											<th width="100px" style="text-align:center">Sp</th>
											<th width="150px" style="text-align:center">Harga</th>
											<th width="150px" style="text-align:center">Total</th>
											<th width="200px" style="text-align:center">Notes</th>
											<th></th>
																
										</tr>
									</thead>
									<tbody id="detailPpd">
			
									<tbody>
									<?php $no=0 ?>
									@foreach ($detail as $detail)		
										<tr>
											<td>
												<input type="hidden" name="NO_ID[]{{$no}}" id="NO_ID" type="text" style="text-align:center" value="{{$detail->NO_ID}}" 
												class="form-control NO_ID" onkeypress="return tabE(this,event)" readonly>
												
												<input name="rec[]" id="rec{{$no}}" type="text" style="text-align:center" value="{{$detail->rec}}" class="form-control rec" onkeypress="return tabE(this,event)" readonly style="text-align:center">
											</td>

											<td>
												<input name="kodesd[]" id="kodesd{{$no}}" type="text" style="text-align:center" class="form-control kodesd " value="{{$detail->kodes}}" readonly>
											</td>

											<td>
												<input name="KD_BRG[]" id="KD_BRG{{$no}}" type="text" style="text-align:center" class="form-control KD_BRG " value="{{$detail->KD_BRG}}" readonly>
											</td>

											<td>
												<input name="NA_BRG[]" id="NA_BRG{{$no}}" type="text" class="form-control NA_BRG " value="{{$detail->NA_BRG}}" readonly>
											</td>

											<td>
												<input name="ket_uk[]" id="ket_uk{{$no}}" type="text" style="text-align: center" class="form-control ket_uk" value="{{$detail->ket_uk}}" readonly required>
											</td>

											<td>
												<input name="ket_kem[]" id="ket_kem{{$no}}" type="text" style="text-align: center" class="form-control ket_kem" value="{{$detail->ket_kem}}" readonly required>
											</td>

											<td>
												<input name="qty[]"  onclick="select()" onblur="hitung()" value="{{$detail->qty}}" id="qty{{$no}}" type="text" style="text-align: right"  class="form-control qty" >
											</td>

											<td>
												<input name="lph[]" value="{{$detail->lph}}" id="lph{{$no}}" type="text" style="text-align: right"  class="form-control lph" readonly>
											</td>

											<td>
												<input name="SRMIN[]" id="SRMIN{{$no}}" type="text" style="text-align: right" class="form-control SRMIN" value="{{$detail->SRMIN}}" readonly required>
											</td>										
											
											<td>
												<input name="qtybrg[]" value="{{$detail->qtybrg}}" id="qtybrg{{$no}}" type="text" style="text-align: right"  class="form-control qtybrg" readonly>
											</td>

											<td>
												<input name="qtypo[]" value="{{$detail->qtypo}}" id="qtypo{{$no}}" type="text" style="text-align: right"  class="form-control qtypo" readonly>
											</td>

											<td>
												<input name="harga[]"  onclick="select()" onblur="hitung()" value="{{$detail->harga}}" id="harga{{$no}}" type="text" style="text-align: right"  class="form-control harga" readonly>
											</td>

											<td>
												<input name="TOTAL[]"  onclick="select()" onblur="hitung()" value="{{$detail->TOTAL}}" id="TOTAL{{$no}}" type="text" style="text-align: right"  class="form-control TOTAL" readonly>
											</td>

											<td>
												<input name="notesd[]" id="notesd{{$no}}" type="text" class="form-control notesd" value="{{$detail->notes}}"  >
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
										<td></td>									
										<td></td>
										<td></td>
										<td><input class="form-control ttotal_qty  text-primary" style="text-align: right"  id="ttotal_qty" name="ttotal_qty" value="{{$header->total_qty}}" readonly></td>
										<td></td>
										<td></td>
										<td></td>
									</tfoot>
								</table>
							</div>
							<div hidden class="col-md-2 row">
                               <a type="button" id='PLUSX' onclick="tambah()" class="fas fa-plus fa-sm md-3" style="font-size: 20px" ></a>
							</div>   
						<!-- scroll -->

						<!--</div> -->
							
						<!-- batas -->

						</div>
					</div> 
						
						
						   
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button hidden type="button" id='TOPX'  onclick="location.href='{{url('/orderpbl/edit/?idx=' .$idx. '&tipx=top&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-primary">Top</button>
								<button hidden type="button" id='PREVX' onclick="location.href='{{url('/orderpbl/edit/?idx='.$header->NO_ID.'&tipx=prev&flagz='.$flagz.'&golz='.$golz.'&buktix='.$header->no_bukti )}}'" class="btn btn-outline-primary">Prev</button>
								<button hidden type="button" id='NEXTX' onclick="location.href='{{url('/orderpbl/edit/?idx='.$header->NO_ID.'&tipx=next&flagz='.$flagz.'&golz='.$golz.'&buktix='.$header->no_bukti )}}'" class="btn btn-outline-primary">Next</button>
								<button hidden type="button" id='BOTTOMX' onclick="location.href='{{url('/orderpbl/edit/?idx=' .$idx. '&tipx=bottom&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button hidden type="button" id='NEWX' onclick="location.href='{{url('/orderpbl/edit/?idx=0&tipx=new&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-warning">New</button>
								<button hidden type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button hidden type="button" id='UNDOX' onclick="location.href='{{url('/orderpbl/edit/?idx=' .$idx. '&tipx=undo&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-info">Undo</button>  
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button hidden type="button" id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								
								<!-- <button type="button" id='CLOSEX'  onclick="location.href='{{url('/orderpbl?flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-secondary">Close</button>  -->
								
								<!-- tombol close sweet alert -->
							     	<button type="button" id='CLOSEX' onclick="closeTrans()" class="btn btn-outline-secondary">Close</button></div>   
							</div>
						</div>
						
						
                    </form>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>


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

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script> -->

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
					// console.log("ID"+nomor);
					// document.getElementById("ID"+nomor).focus();
					// form.submit();
				}
				return false;
			}
		});


		$tipx = $('#tipx').val();
		$searchx = $('#CARI').val();
		
		
        if (( $tipx == 'new' ))
		{
			 baru();
			//  tambah();		 
		}


        if ( $tipx != 'new' )
		{
			 ganti();		

			    var initkode1 ="{{ $header->KODES }}";			 
			    var initcombo1 ="{{ $header->NAMAS }}";
		    	var defaultOption1 = { id: initkode1, text: initcombo1 }; // Set your default option ID and text
                var newOption1 = new Option(defaultOption1.text, defaultOption1.id, true, true);
                $('#KODES').append(newOption1).trigger('change');
			 
		}    
		
		$("#ttotal_qty").autoNumeric('init', {mDec: '0', aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});


		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#qty" + i.toString()).autoNumeric('init', {mDec: '0', aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#harga" + i.toString()).autoNumeric('init', {mDec: '0', aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#TOTAL" + i.toString()).autoNumeric('init', {mDec: '0', aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#SRMIN" + i.toString()).autoNumeric('init', {mDec: '2', aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#lph" + i.toString()).autoNumeric('init', {mDec: '2', aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#qtybrg" + i.toString()).autoNumeric('init', {mDec: '0', aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#qtypo" + i.toString()).autoNumeric('init', {mDec: '0', aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
		}	
		
        $('body').on('click', '.btn-delete', function() {
			var val = $(this).parents("tr").remove();
			baris--;
			hitung();
			nomor();
			
		});

		$('.date').datepicker({  
            dateFormat: 'dd-mm-yy'
		});
		
		
 	
		
		// CHOOSE Supplier
 		var dTableBSuplier;
		loadDataBSuplier = function(){
		
			$.ajax(
			{
				type: 'GET', 		
				url: '{{url('sup/browse_sup')}}',
				// data: {
				// 	'GOL': 'Y',
				// },

				
			    beforeSend: function(){
					$("#LOADX").show();
				},


				success: function( response )
				{
					$("#LOADX").hide(); 

					resp = response;
					if(dTableBSuplier){
						dTableBSuplier.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBSuplier.row.add([
							'<a href="javascript:void(0);" onclick="chooseSuplier(\''+resp[i].KODES+'\',  \''+resp[i].NAMAS+'\',  \''+resp[i].ALMT_K+'\', \''+resp[i].KOTA+'\')">'+resp[i].KODES+'</a>',
							resp[i].NAMAS,
							resp[i].ALMT_K,
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
		
		chooseSuplier = function(KODES, NAMAS, ALMT_K, KOTA){
			console.log(KODES)
			$("#KODES").val(KODES);
			$("#NAMAS").val(NAMAS);
			$("#ALAMAT").val(ALMT_K);
			$("#KOTA").val(KOTA);	
			$("#browseSuplierModal").modal("hide");
		
		}

	
		
		$("#KODES").keypress(function(e){

			if(e.keyCode == 46){
				 e.preventDefault();
				 browseSuplier();
			}
		}); 

		//////////////////////////////////////////////////////

		var dTableBBarang;
		var rowidBarang;
		loadDataBBarang = function(){
		
			$.ajax(
			{
				type: 'GET',    
				url: "{{url('brg/browse')}}",
				async : false,
				data: {
						'KD_BRG': $("#KD_BRG"+rowidBarang).val(),
						'KODES' : $("#KODES").val(),
						'JENIS' : '8'
				},
				success: function( response )

				{
					resp = response;
					
					
					if ( resp.length > 1 )
					{	
							if(dTableBBarang){
								dTableBBarang.clear();
							}
							for(i=0; i<resp.length; i++){
								
								dTableBBarang.row.add([
									'<a href="javascript:void(0);" onclick="chooseBarang(\''+resp[i].KD_BRG+'\', \''+resp[i].NA_BRG+'\' , \''+resp[i].KET_UK+'\' )">'+resp[i].KD_BRG+'</a>',
									resp[i].NA_BRG,
									resp[i].KET_UK,
								]);
							}
							dTableBBarang.draw();
					
					}
					else
					{
						$("#KD_BRG"+rowidBarang).val(resp[0].KD_BRG);
						$("#NA_BRG"+rowidBarang).val(resp[0].NA_BRG);
						$("#KET_UK"+rowidBarang).val(resp[0].KET_UK);
					}
				}
			});
		}
		
		dTableBBarang = $("#table-bbarang").DataTable({
			
		});

		browseBarang = function(rid){
			rowidBarang = rid;
			$("#NA_BRG"+rowidBarang).val("");			
			loadDataBBarang();
	
			
			if ( $("#NA_BRG"+rowidBarang).val() == '' ) {				
					$("#browseBarangModal").modal("show");
			}	
		}
		
		chooseBarang = function(KD_BRG,NA_BRG,KET_UK){
			$("#KD_BRG"+rowidBarang).val(KD_BRG);
			$("#NA_BRG"+rowidBarang).val(NA_BRG);	
			$("#KET_UK"+rowidBarang).val(KET_UK);
			$("#browseBarangModal").modal("hide");
		}

		$("#KD_BRG").keypress(function(e){

			if(e.keyCode == 46){
				 e.preventDefault();
				 browseBarang();
			}
		}); 

	});

///////////////////////////////////////		
	
	function proses(){
		var mulai = (idrow == baris) ? idrow - 1 : idrow;
		$.ajax({
			type: 'GET',
			url: "{{ url('orderpbl/browse') }}",
			async: false,
			data: {
				kodes:  $("#kodes").val(),
				kodes2: $("#kodes2").val(),
				LPH1:   $("#LPH1").val(),
				LPH2:   $("#LPH2").val(),
				SUB1:   $("#SUB1").val(),
				SUB2:   $("#SUB2").val()
			},
			success: function(response) {
				resp = response;
				var html = '';
				for (i = 0; i < resp.length; i++) {
					html += `<tr>
								<td>
									<input name="NO_ID[]" id="NO_ID${idrow}" type="hidden" style="text-align:center" class="form-control NO_ID" value="new" readonly>
									<input name="rec[]" id="rec${idrow}" type="text" style="text-align:center" class="rec form-control" onkeypress="return tabE(this,event)" readonly>
								</td>

								<td>
									<input name="kodesd[]" data-rowid="${idrow}" onblur="browseBarang(${idrow})" id="kodesd${idrow}" type="text" style="text-align:center" class="form-control kodesd" value="${resp[i].kodes}" readonly>
								</td>

								<td>
									<input name="KD_BRG[]" data-rowid="${idrow}" onblur="browseBarang(${idrow})" id="KD_BRG${idrow}" type="text" style="text-align:center" class="form-control KD_BRG" value="${resp[i].KD_BRG}" readonly>
								</td>

								<td>
									<input name="NA_BRG[]" id="NA_BRG${idrow}" type="text" class="form-control NA_BRG" value="${resp[i].NA_BRG}" required readonly>
								</td>

								<td>
									<input name="ket_uk[]" id="ket_uk${idrow}" type="text" class="form-control ket_uk" style="text-align:center" value="${resp[i].ket_uk}" readonly required>
								</td>

								<td>
									<input name="ket_kem[]" id="ket_kem${idrow}" type="text" class="form-control ket_kem" style="text-align:center" value="${resp[i].ket_kem}" readonly required>
								</td>

								<td>
									<input name="qty[]" id="qty${idrow}" onblur="hitung()" type="text" class="form-control qty text-primary" style="text-align:right" value="0.00" required>
								</td>

								<td>
									<input name="lph[]" id="lph${idrow}" type="text" class="form-control lph text-primary" style="text-align:right" value="${resp[i].lph ?? 0}" readonly required>
								</td>

								<td>
									<input name="SRMIN[]" id="SRMIN${idrow}" type="text" class="form-control SRMIN text-primary" style="text-align:right" value="${resp[i].SRMIN ?? 0}" readonly required>
								</td>

								<td>
									<input name="qtybrg[]" id="qtybrg${idrow}" type="text" class="form-control qtybrg text-primary" style="text-align:right" value="0.00" required>
								</td>

								<td>
									<input name="qtypo[]" id="qtypo${idrow}" type="text" class="form-control qtypo text-primary" style="text-align:right" value="0.00" required>
								</td>

								<td>
									<input name="harga[]" id="harga${idrow}" type="text" class="form-control harga text-primary" style="text-align:right" value="${resp[i].harga ?? 0}" readonly required>
								</td>

								<td>
									<input name="TOTAL[]" id="TOTAL${idrow}" onblur="hitung()" type="text" class="form-control TOTAL text-primary" style="text-align:right" readonly required>
								</td>

								<td>
									<input name="notesd[]" id="notesd${idrow}" type="text" class="form-control notesd">
								</td>

								<td>
									<button type='button' id='DELETEX'  class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
								</td>
							</tr>`;
				}
				$('#detailPpd').html(html);

				$(".qty").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".qty").autoNumeric('update');

				$(".lph").autoNumeric('init', {
					mDec: '2',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".lph").autoNumeric('update');

				$(".SRMIN").autoNumeric('init', {
					mDec: '2',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".SRMIN").autoNumeric('update');

				$(".qtybrg").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".qtybrg").autoNumeric('update');

				$(".qtypo").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".qtypo").autoNumeric('update');

				$(".harga").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".harga").autoNumeric('update');

				$(".TOTAL").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".TOTAL").autoNumeric('update');

				idrow = resp.length;
				baris = resp.length;

				nomor();
				hitung();
			}
		});
	}



	function cekDetail(){
		var cekBarang = '';
		$(".KD_BRG").each(function() {
			
			let z = $(this).closest('tr');
			var KD_BRGX = z.find('.KD_BRG').val();
			
			if( KD_BRGX =="" )
			{
					cekBarang = '1';
					
			}	
		});
		
		return cekBarang;
	}


	function simpan() {
		hitung();
		
		var tgl = $('#tgl').val();
		var bulanPer = {{session()->get('periode')['bulan']}};
		var tahunPer = {{session()->get('periode')['tahun']}};
		
        var check = '0';

			if ( $('#KD_BRG').val()=='' ) 
            {				
			    check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Barang# Harus Diisi.'
				});
				return; // Stop function execution
			}
			
			if (baris==0)
			{
				check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Data detail kosong (Tambahkan 1 baris kosong jika ingin mengosongi detail)'
				});
				return; // Stop function execution
			}

			if (tgl.substring(3, 5) != bulanPer) {
				check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Bulan tidak sama dengan Periode'
				});
				return; // Stop function execution
			}

			if (tgl.substring(tgl.length - 4) != tahunPer) {
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
		
    function nomor() {
		var i = 1;
		$(".rec").each(function() {
			$(this).val(i++);
		});
		
	//	hitung();
	
	}

	function hitung() {
		let ttotal_qty = 0;
		let grandTotal = 0;

		$(".qty").each(function() {
			let z = $(this).closest('tr');
			let qtyX   = parseFloat(z.find('.qty').val().replace(/,/g, ''))   || 0;
			let hargaX = parseFloat(z.find('.harga').val().replace(/,/g, '')) || 0;

			let totalX = qtyX * hargaX;

			// set nilai TOTAL dengan autoNumeric agar otomatis format ribuan
			z.find('.TOTAL').autoNumeric('set', totalX);

			ttotal_qty += qtyX;
		});

		$("#ttotal_qty").autoNumeric('set', ttotal_qty);
	}
	

	
  
	function baru() {
		
		 kosong();
		 hidup();
	
	}
	
	function ganti() {
		
		//  mati();
		 hidup();
	
	}
	
	function batal() {
		
		// alert($header[0]->NO_BUKTI);
		
		 //$('#NO_BUKTI').val($header[0]->NO_BUKTI);	
		 mati();
	
	}
	
 
	function jtempo() {

		    
		$.ajax(
		{
			type: 'GET',    
			url: "{{url('po/jtempo')}}",
			async : false,
			data: {
					'TGL' : $("#TGL").val(),
					'SUB' : $("#HARI").val(),
			},
			success: function( response )

			{
				resp = response;
				$("#JTEMPO").val( resp );
				
			}
		});

	}
	
	function ambil_hari() {

		    
		$.ajax(
		{
			type: 'GET',    
			url: "{{url('sup/browse_hari')}}",
			data: {
					'KODES' : $("#KODES").val(),
			},
			
			success: function( response )

			{
				resp = response;
				$("#NAMAS").val( resp[0].NAMAS );
				$("#PKP").val( resp[0].PKP );
				$("#HARI").val( resp[0].HARI );
	
        		if ( $("#PKP").val() == '1' )
        		{

                     document.getElementById("PKP").checked = true;
                    	
        		}
        
                else
                {
                     document.getElementById("PKP").checked = false;
                    
                }
        				
			}
		});
		
		   
	
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

		$("#CARI").attr("readonly", true);	
	    $("#SEARCHX").attr("disabled", true);
		
	    $("#PLUSX").attr("hidden", false)
		   
			$("#no_bukti").attr("readonly", true);		   
			$("#TGL").attr("readonly", false);
			$("#JTEMPO").attr("readonly", false);
	    	$("#KODES").attr("disabled", false);
	        $("#PKP").attr("disabled", true);
	        $("#ZPKP").attr("disabled", true);
			
			$("#NOTES").attr("readonly", false);
    		$("#NOTES").attr("disabled", false);				

		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#ID" + i.toString()).attr("readonly", true);
			$("#KD_BRG" + i.toString()).attr("readonly", false);
			$("#NA_BHN" + i.toString()).attr("readonly", true);
			$("#NA_BRG" + i.toString()).attr("readonly", true);
			$("#satuan" + i.toString()).attr("readonly", true);
			$("#qty" + i.toString()).attr("readonly", false);
			$("#qtybrg" + i.toString()).attr("readonly", false);
			$("#qtypo" + i.toString()).attr("readonly", false);
			$("#harga" + i.toString()).attr("readonly", true);
			$("#total" + i.toString()).attr("readonly", true);
			$("#DISK" + i.toString()).attr("readonly", false);
			$("#KET" + i.toString()).attr("readonly", false);
			$("#DELETEX" + i.toString()).attr("hidden", false);

			$tipx = $('#tipx').val(); 
			
			if ( $tipx != 'new' )
			{
				$("#KD_BHN" + i.toString()).attr("readonly", true);	
				$("#KD_BHN" + i.toString()).removeAttr('onblur');
				
				$("#KD_BRG" + i.toString()).attr("readonly", true);	
				$("#KD_BRG" + i.toString()).removeAttr('onblur');
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
		
		$("#no_bukti").attr("readonly", true);	
		
		$("#TGL").attr("readonly", true);
		$("#JTEMPO").attr("readonly", true);
		$("#KODES").attr("disabled", true);
		$("#NOTES").attr("readonly", true);
		$("#NOTES").attr("disabled", true);
		
		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#rec" + i.toString()).attr("readonly", true);
			$("#KD_BHN" + i.toString()).attr("readonly", true);
			$("#NA_BHN" + i.toString()).attr("readonly", true);
			$("#KD_BRG" + i.toString()).attr("readonly", true);
			$("#NA_BRG" + i.toString()).attr("readonly", true);
			$("#satuan" + i.toString()).attr("readonly", true);
			$("#qty" + i.toString()).attr("readonly", true);
			$("#qtybrg" + i.toString()).attr("readonly", true);
			$("#qtypo" + i.toString()).attr("readonly", true);
			$("#harga" + i.toString()).attr("readonly", true);
			$("#total" + i.toString()).attr("readonly", true);
			$("#DISK" + i.toString()).attr("readonly", true);
			$("#KET" + i.toString()).attr("readonly", true);
			
			$("#DELETEX" + i.toString()).attr("hidden", true);
		}


		
	}


	function kosong() {
				
		 $('#no_bukti').val("+");		
		 $('#KODES').val("");	
		 $('#NAMAS').val("");	
		 $('#NOTES').val("");	
		 $('#ttotal_qty').val("0");
		 
		//  $('#PKP').val("0")
		 $('#HARI').val("0")
		 
		var html = '';
		$('#detailx').html(html);	
		
	}

	
	// sweetalert untuk tombol hapus dan close
	
	function hapusTrans() {
		let text = "Hapus Transaksi "+$('#no_bukti').val()+"?";

		var loc ='';
		var flagz = "{{ $flagz }}";
		var golz = "{{ $golz }}";
		
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
	            	loc = "{{ url('/orderpbl/delete/'.$header->NO_ID) }}" + '?flagz=' + encodeURIComponent(flagz) + '&golz=' + encodeURIComponent(golz);

		            // alert(loc);
	            	window.location = loc;
		
				});
			}
		});
	}
	
	function closeTrans() {
		console.log("masuk");
		var loc ='';
		var flagz = "{{ $flagz }}";
		var golz = "{{ $golz }}";
		
		Swal.fire({
			title: 'Are you sure?',
			text: 'Do you really want to close this page? Unsaved changes will be lost.',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes, close it',
			cancelButtonText: 'No, stay here'
		}).then((result) => {
			if (result.isConfirmed) {
	        	loc = "{{ url('/orderpbl/') }}" + '?flagz=' + encodeURIComponent(flagz) + '&golz=' + encodeURIComponent(golz);
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
		
		var flagz = "{{ $flagz }}";
		var golz = "{{ $golz }}";
		var cari = $("#CARI").val();
		var loc = "{{ url('/orderpbl/edit/') }}" + '?idx={{ $header->NO_ID}}&tipx=search&flagz=' + encodeURIComponent(flagz) + '&golz=' + encodeURIComponent(golz) +'&buktix=' +encodeURIComponent(cari);
		window.location = loc;
		
	}


    function tambah() {

        var x = document.getElementById('datatable').insertRow(baris + 1);
 
		html=`<tr>

                <td>
 					<input name='NO_ID[]' id='NO_ID${idrow}' type='hidden' class='form-control NO_ID' value='new' readonly> 
					<input name='rec[]' id='rec${idrow}' type='text' class='rec form-control' onkeypress='return tabE(this,event)' readonly>
	            </td>

				<td>
				    <input name='kodes[]' data-rowid=${idrow} onblur='browseBarang(${idrow})' id='kodes${idrow}' type='text' class='form-control  kodes' readonly>
				</td>

				<td>
				    <input name='KD_BRG[]' data-rowid=${idrow} onblur='browseBarang(${idrow})' id='KD_BRG${idrow}' type='text' class='form-control  KD_BRG' readonly>
				</td>

                <td>
				    <input name='NA_BRG[]'   id='NA_BRG${idrow}' type='text' class='form-control  NA_BRG' required readonly>
                </td>

				<td>
				    <input name='ket_uk[]'   id='ket_uk${idrow}' type='text' style='text-align: center' class='form-control  ket_uk' readonly required>
                </td>

				<td>
				    <input name='ket_kem[]'   id='ket_kem${idrow}' type='text' style='text-align: center' class='form-control  ket_kem' readonly required>
                </td>

				<td>
		            <input name='qty[]' value='0' id='qty${idrow}' type='text' style='text-align: right' class='form-control qty text-primary' required >
                </td>

				<td>
		            <input name='lph[]' value='0' id='lph${idrow}' type='text' style='text-align: right' class='form-control lph text-primary' required >
                </td>

				<td>
		            <input name='SRMIN[]' value='0' id='SRMIN${idrow}' type='text' style='text-align: right' class='form-control SRMIN text-primary' required >
                </td>
				
				<td>
		            <input name='qtybrg[]' value='0' id='qtybrg${idrow}' type='text' style='text-align: right' class='form-control qtybrg text-primary' required >
                </td>

				<td>
		            <input name='qtypo[]' value='0' id='qtypo${idrow}' type='text' style='text-align: right' class='form-control qtypo text-primary' required >
                </td>

				<td>
		            <input name='harga[]' value='0' id='harga${idrow}' type='text' style='text-align: right' class='form-control harga text-primary' required >
                </td>

				<td>
		            <input name='TOTAL[]' value='0' id='TOTAL${idrow}' type='text' style='text-align: right' class='form-control TOTAL text-primary' required >
                </td>

				<td>
					<input name='notesd[]' id='notesd${idrow}' type='text' class='form-control  notesd' required>
                </td>
				
                <td>
					<button type='button' id='DELETEX${idrow}'  class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
                </td>				
         </tr>`;
				
        x.innerHTML = html;
        var html='';
		
		
		
		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#qty" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});


			$("#lph" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});

			$("#SRMIN" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});		
			
			$("#qtybrg" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});	
			
			$("#qtypo" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});
			
			$("#harga" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});
			
			$("#TOTAL" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			})	 
		}

		// $("#KD_BHN"+idrow).keypress(function(e){
		// 	if(e.keyCode == 46){
		// 		e.preventDefault();
		// 		browseBarang(eval($(this).data("rowid")));
		// 	}
		// }); 


        idrow++;
        baris++;
        nomor();
		
		$(".ronly").on('keydown paste', function(e) {
             e.preventDefault();
             e.currentTarget.blur();
         });
     }
</script>
<!-- 
<script src="autonumeric.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.5.4"></script>
<script src="https://unpkg.com/autonumeric"></script> -->
@endsection