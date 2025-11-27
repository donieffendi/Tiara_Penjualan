@extends('layouts.main')

<style>
    .card {

    }

    .form-control:focus {
        background-color: #E0FFFF !important;
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
</style>

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">

        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <div class="content">
        <div class="container-fluid">
        <div class="row">
            <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <form action="{{($tipx=='new')? url('/jual/store?flagz='.$flagz.'&golz='.$golz.'') : url('/jual/update/'.$header->NO_ID.'&flagz='.$flagz.'&golz='.$golz.'' ) }}" method="POST" name ="entri" id="entri" >
  
	    			      @csrf
        
                        <div class="tab-content mt-3">
        
                            <div class="form-group row">
								<div class="col-md-1" align="right">
                                    <label for="NO_BUKTI" class="form-label">Bukti#</label>
                                </div>
								

                                   <input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
                                    placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

									<input name="tipx" class="form-control tipx" id="tipx" value="{{$tipx}}" hidden >
									<input name="flagz" class="form-control flagz" id="flagz" value="{{$flagz}}" hidden >
									<input name="golz" class="form-control golz" id="golz" value="{{$golz}}" hidden >
			
								
                                <div class="col-md-2">
                                    <input type="text" class="form-control NO" id="NO_BUKTI" name="NO_BUKTI"
                                    placeholder="Masukkan Bukti#" value="{{$header->NO_BUKTI}}" readonly >
                                </div>
        
                                <div class="col-md-1">
                                    <label for="TGL" class="form-label">Tgl</label>
                                </div>
                                <div class="col-md-2">
								
								  <input class="form-control date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}">
								
                                </div>
                            </div>

							

							<div class="form-group row">
								<div {{( $flagz =='JL') ? '' : 'hidden' }} class="col-md-1" align="right">
									<label style="color:red">*</label>									
                                    <label for="NO_SURATS" class="form-label">SJ#</label>
                                </div>
                               	<div {{( $flagz =='JL') ? '' : 'hidden' }} class="col-md-2 input-group" >
                                  <input type="text" class="form-control NO_SURATS" id="NO_SURATS" name="NO_SURATS" placeholder="Pilih SJ"value="{{$header->NO_SURATS}}" style="text-align: left" readonly >
        						  <button type="button" class="btn btn-primary" onclick="browseSurats()"><i class="fa fa-search"></i></button>
                                </div>

								<div {{( $flagz =='AJ') ? '' : 'hidden' }} class="col-md-1" align="right">
									<label style="color:red">*</label>									
                                    <label for="NO_JUAL" class="form-label">Jual#</label>
                                </div>
                               	<div {{( $flagz =='AJ') ? '' : 'hidden' }} class="col-md-2 input-group" >
                                  <input type="text" class="form-control NO_JUAL" id="NO_JUAL" name="NO_JUAL" placeholder="Pilih SJ"value="{{$header->NO_JUAL}}" style="text-align: left" readonly >
        						  <button type="button" class="btn btn-primary" onclick="browseJual()"><i class="fa fa-search"></i></button>
                                </div>

								<div class="col-md-1" align="right">
									<!-- <label style="color:red">*</label>									 -->
                                    <label for="NO_SPM" class="form-label">SPM#</label>
                                </div>
                               	<div class="col-md-2 input-group" >
                                  <input type="text" class="form-control NO_SPM" id="NO_SPM" name="NO_SPM" placeholder="Pilih SPM"value="{{$header->NO_SPM}}" style="text-align: left" readonly >
        				
                                </div>
                            
                            	<div class="col-md-1" align="right">
									<!-- <label style="color:red">*</label>									 -->
                                    <label for="NO_SO" class="form-label">SO#</label>
                                </div>
                               	<div class="col-md-2 input-group" >
                                  <input type="text" class="form-control NO_SO" id="NO_SO" name="NO_SO" placeholder="Pilih SO"value="{{$header->NO_SO}}" style="text-align: left" readonly >
        			
                                </div>
                            </div>
							

                            <div class="form-group row">
								<div class="col-md-1" align="right">
                                    <label for="KODEC" class="form-label">Customer#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control KODEC" id="KODEC" name="KODEC" placeholder="Masukkan Customer#" value="{{$header->KODEC}}" readonly>
                                </div>

								<div class="col-md-1" align="left">
                                    <label for="NAMAC" class="form-label"></label>
                                </div>
								<div class="col-md-4">
                                    <input type="text" class="form-control NAMAC" id="NAMAC" name="NAMAC" placeholder="-" value="{{$header->NAMAC}}" readonly>
                                </div>
                            </div>
							
							
                            <div class="form-group row">

								<div class="col-md-1" align="right">
                                    <label for="ALAMAT" class="form-label"></label>
                                </div>
								<div class="col-md-4">
                                    <input type="text" class="form-control ALAMAT" id="ALAMAT" name="ALAMAT" value="{{$header->ALAMAT}}"placeholder="Alamat" readonly >
                                </div>

								
								<div class="col-md-1" align="right">
                                    <label for="KOTA" class="form-label"></label>
                                </div>
								<div class="col-md-2">
                                    <input type="text" class="form-control KOTA" id="KOTA" name="KOTA" value="{{$header->KOTA}}"placeholder="Kota" readonly>
                                </div>
                            </div>
							
							
                            <div class="form-group row">
								<div class="col-md-1" align="left">
									<label style="color:red;font-size:20px">* </label>	
                                    <label for="KODEP" class="form-label">Sales#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control KODEP" id="KODEP" name="KODEP" placeholder="Kode Pegawai" value="{{$header->KODEP}}" readonly>
                                </div>

								<div class="col-md-4">
                                    <input type="text" class="form-control NAMAP" id="NAMAP" name="NAMAP" placeholder="-" value="{{$header->NAMAP}}" readonly>
                                </div>
                            </div>
                            
							<div class="form-group row">
                                <div class="col-md-1">
                                    <label for="NOTES" class="form-label">Notes</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NOTES" id="NOTES" name="NOTES" placeholder="Masukkan NOTES" value="{{$header->NOTES}}">
                                    <input type="text"  onclick="select()" onkeyup="hitung()" class="form-control NETT" id="NETT" name="NETT" placeholder="NETT" value="{{$header->NETT}}" style="text-align: right" hidden eadonly>
                                </div>
        

                            </div>
							
                            
                            <hr style="margin-top: 30px; margin-buttom: 30px">
							
							<div style="overflow-y:scroll;" class="col-md-12 scrollable" align="right">
							
								<table id="datatable" class="table table-striped table-border">

									<thead>
										<tr>
											<th style="text-align: center;">No.</th>

											<th {{( $golz =='B') ? '' : 'hidden' }} width="100px">
												<label style="color:red;font-size:20px">*</label>
												<label for="KD_BHN" class="form-label">Bahan</label>
											</th>
											<th {{( $golz =='B') ? '' : 'hidden' }} width="200px" style="text-align:center">Nama</th>

											<th {{( $golz =='J') ? '' : 'hidden' }} width="100px">
												<label style="color:red;font-size:20px">*</label>
												<label for="KD_BRG" class="form-label">Barang</label>
											</th>
											<th {{( $golz =='J') ? '' : 'hidden' }} width="200px" style="text-align:center">Nama</th>
											
											<th style="text-align: center;">Satuan</th>
											<th style="text-align: center;">Qty</th>
											<th style="text-align: center;">Harga</th>
											<th style="text-align: center;">Total</th>								
											<th style="text-align: center;">Ket</th>
											<th></th>										
										</tr>
										
									</thead>
									<tbody>

                                    <tbody id="detailSod">
								
									<?php $no=0 ?>
									@foreach ($detail as $detail)
										<tr>
											<td>
												<input type="hidden" name="NO_ID[]" id="NO_ID{{$no}}" type="text" value="{{$detail->NO_ID}}" 
												class="form-control NO_ID" onkeypress="return tabE(this,event)" readonly>
												
												<input name="REC[]" id="REC{{$no}}" type="text" value="{{$detail->REC}}" class="form-control REC" onkeypress="return tabE(this,event)" readonly>
											</td>
											
											<td {{( $golz =='B') ? '' : 'hidden' }}>
												<input name="KD_BHN[]" id="KD_BHN{{$no}}" type="text" value="{{$detail->KD_BHN}}"
												class="form-control KD_BHN "  onblur="browseBahan({{$no}})" >
											</td>
											<td {{( $golz =='B') ? '' : 'hidden' }}>
												<input name="NA_BHN[]" id="NA_BHN{{$no}}" type="text" class="form-control KD_BHN" value="{{$detail->NA_BHN}}" readonly required>
											</td>

											<td {{( $golz =='J') ? '' : 'hidden' }}>
												<input name="KD_BRG[]" id="KD_BRG{{$no}}" type="text" class="form-control KD_BRG " 
												value="{{$detail->KD_BRG}}" onblur="browseBarang({{$no}})">
											</td>

											<td {{( $golz =='J') ? '' : 'hidden' }}>
												<input name="NA_BRG[]" id="NA_BRG{{$no}}" type="text" class="form-control NA_BRG " value="{{$detail->NA_BRG}}">
											</td>

											<td>
												<input name="SATUAN[]" id="SATUAN{{$no}}" type="text" value="{{$detail->SATUAN}}" class="form-control SATUAN" readonly required>
											</td>
											
											<td><input name="QTY[]"  onblur="hitung()" value="{{$detail->QTY}}" id="QTY{{$no}}" type="text" style="text-align: right"  class="form-control QTY text-primary" ></td>
											<td><input name="HARGA[]"  onblur="hitung()" value="{{$detail->HARGA}}" id="HARGA{{$no}}" type="text" style="text-align: right"  class="form-control HARGA text-primary" ></td>
											<td>
												<input name="TOTAL[]"  onblur="hitung()" value="{{$detail->TOTAL}}" id="TOTAL{{$no}}" type="text" style="text-align: right"  class="form-control TOTAL text-primary" readonly >
											</td>
											<td>
												<input name="KET[]" id="KET{{$no}}" type="text" value="{{$detail->KET}}" class="form-control KET" >
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
										<td {{( $golz =='B') ? '' : 'hidden' }}></td>
										<td {{( $golz =='B') ? '' : 'hidden' }}></td>
										<td {{( $golz =='J') ? '' : 'hidden' }}></td>
										<td {{( $golz =='J') ? '' : 'hidden' }}></td>
										<td></td>		
										<td><input class="form-control TTOTAL_QTY  text-primary font-weight-bold" style="text-align: right"  id="TTOTAL_QTY" name="TTOTAL_QTY" value="{{$header->TOTAL_QTY}}" readonly></td>
										<td></td>
									   <td><input class="form-control TTOTAL  text-primary font-weight-bold" style="text-align: right"  id="TTOTAL" name="TTOTAL" value="{{$header->TOTAL}}" readonly></td> 
										<td></td>
									</tfoot>



								</table>					
							</div>

							<div class="col-md-2 row">
								<a type="button" id='PLUSX' onclick="tambah()" class="fas fa-plus fa-sm md-3" style="font-size: 20px" ></a>

							</div>			
							
                        </div> 
						
						<hr style="margin-top: 30px; margin-buttom: 30px">		
                                 

						
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button type="button" id='TOPX'  onclick="location.href='{{url('/jual/edit/?idx=' .$idx. '&tipx=top&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-primary">Top</button>
								<button type="button" id='PREVX' onclick="location.href='{{url('/jual/edit/?idx='.$header->NO_ID.'&tipx=prev&flagz='.$flagz.'&golz='.$golz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Prev</button>
								<button type="button" id='NEXTX' onclick="location.href='{{url('/jual/edit/?idx='.$header->NO_ID.'&tipx=next&flagz='.$flagz.'&golz='.$golz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Next</button>
								<button type="button" id='BOTTOMX' onclick="location.href='{{url('/jual/edit/?idx=' .$idx. '&tipx=bottom&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button type="button" id='NEWX' onclick="location.href='{{url('/jual/edit/?idx=0&tipx=new&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-warning">New</button>
								<button type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button type="button" id='UNDOX' onclick="location.href='{{url('/jual/edit/?idx=' .$idx. '&tipx=undo&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-info">Undo</button>  
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button type="button" id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								<button type="button" id='CLOSEX'  onclick="location.href='{{url('/jual?flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-secondary">Close</button>
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
	


	<div class="modal fade" id="browseSuratsModal" tabindex="-1" role="dialog" aria-labelledby="browseSuratsModalLabel" aria-hidden="true">
	  <div class="modal-dialog mw-100 w-75" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseSuratsModalLabel">Cari Surat Jalan</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bsurats">
				<thead>
					<tr>
						<th>No Surat</th>
						<th>SPM#</th>
						<th>SO</th>
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

	<div class="modal fade" id="browseJualModal" tabindex="-1" role="dialog" aria-labelledby="browseJualModalLabel" aria-hidden="true">
	  <div class="modal-dialog mw-100 w-75" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseJualModalLabel">Cari Penjualan</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bjual">
				<thead>
					<tr>
						<th>No Jual</th>
						<th>SO</th>
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


	<div class="modal fade" id="browseBarangModal" tabindex="-1" role="dialog" aria-labelledby="browseBarangModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseBarangModalLabel">Cari Item</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bbarang">
				<thead>
					<tr>
						<th>Item#</th>
						<th>Nama</th>
						<th>Satuan</th>
						<th>Qty</th>
						<th>Kirim</th>
						<th>Sisa</th>
						<th>Harga</th>	
						
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


	<div class="modal fade" id="browseBahanModal" tabindex="-1" role="dialog" aria-labelledby="browseBahanModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseBahanModalLabel">Cari Item</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bbahan">
				<thead>
					<tr>
						<th>Item#</th>
						<th>Nama</th>
						<th>Satuan</th>
						<th>Qty</th>
						<th>Kirim</th>
						<th>Sisa</th>
						<th>Harga</th>	
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
<!-- TAMBAH 1 -->
<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

	var idrow = 1;
	var baris = 1;
    function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}

// TAMBAH HITUNG
	$(document).ready(function() {
		
		idrow=<?php echo $no; ?>;
		baris=<?php echo $no; ?>;

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
         //    tambah();				 
		}

        if ( $tipx != 'new' )
		{
			 ganti();			
		}    
		
		$("#TTOTAL_QTY").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#TTOTAL").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#PPN").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#NETT").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});


		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#QTY" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#HARGA" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#TOTAL" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});


		}
		
		$('body').on('click', '.btn-delete', function() {
			var val = $(this).parents("tr").remove();
			baris--;
			hitung();
			nomor();
		});
		
		$(".date").datepicker({
			'dateFormat': 'dd-mm-yy',
		})
		
		

//////////////////////////////////////////////////

		var dTableBSurats;
		loadDataBSurats = function(){
		
			$.ajax(
			{
				type: 'GET', 		
				url: '{{url('jual/browseSurat')}}',
				data: {
					'GOL': "{{$golz}}",
				},
				success: function( response )
				{
					resp = response;
					if(dTableBSurats){
						dTableBSurats.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBSurats.row.add([
							'<a href="javascript:void(0);" onclick="chooseSurats(\''+resp[i].NO_BUKTI+'\' , \''+resp[i].NO_SPM+'\',  \''+resp[i].NO_SO+'\', \''+resp[i].KODEC+'\',  \''+resp[i].NAMAC+'\', \''+resp[i].ALAMAT+'\',  \''+resp[i].KOTA+'\' , \''+resp[i].KODEP+'\',  \''+resp[i].NAMAP+'\'  )">'+resp[i].NO_BUKTI+'</a>',
							resp[i].NO_SPM,
							resp[i].NO_SO,
							resp[i].KODEC,
							resp[i].NAMAC,
							resp[i].ALAMAT,
							resp[i].KOTA,
						]);
					}
					dTableBSurats.draw();
				}
			});
		}
		
		dTableBSurats = $("#table-bsurats").DataTable({
			
		});
		
		browseSurats = function(){
			loadDataBSurats();
			$("#browseSuratsModal").modal("show");
		}
		
		chooseSurats = function(NO_BUKTI, NO_SPM, NO_SO, KODEC,NAMAC, ALAMAT, KOTA, KODEP, NAMAP ){
			$("#NO_SURATS").val(NO_BUKTI);
			$("#NO_SPM").val(NO_BUKTI);
			$("#NO_SO").val(NO_SO);
			$("#KODEC").val(KODEC);
			$("#NAMAC").val(NAMAC);
			$("#ALAMAT").val(ALAMAT);
			$("#KOTA").val(KOTA);
			$("#KODEP").val(KODEP);
			$("#NAMAP").val(NAMAP);			
			
			$("#browseSuratsModal").modal("hide");
			
			getSuratsd(NO_BUKTI);
								
			
		}
		
		$("#NO_SURAT").keypress(function(e){

			if(e.keyCode == 46){
				 e.preventDefault();
				 browseSurats();
			}
		}); 
////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////

		var dTableBJual;
		loadDataBJual = function(){
		
			$.ajax(
			{
				type: 'GET', 		
				url: '{{url('jual/browse')}}',
				data: {
					'GOL': "{{$golz}}",
				},
				success: function( response )
				{
					resp = response;
					if(dTableBJual){
						dTableBJual.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBJual.row.add([
							'<a href="javascript:void(0);" onclick="chooseJual(\''+resp[i].NO_BUKTI+'\' , \''+resp[i].NO_SO+'\', \''+resp[i].KODEC+'\',  \''+resp[i].NAMAC+'\', \''+resp[i].ALAMAT+'\',  \''+resp[i].KOTA+'\',  \''+resp[i].KODEP+'\',  \''+resp[i].NAMAP+'\'  )">'+resp[i].NO_BUKTI+'</a>',
							resp[i].NO_SO,
							resp[i].KODEC,
							resp[i].NAMAC,
							resp[i].ALAMAT,
							resp[i].KOTA,
						]);
					}
					dTableBJual.draw();
				}
			});
		}
		
		dTableBJual = $("#table-bjual").DataTable({
			
		});
		
		browseJual = function(){
			loadDataBJual();
			$("#browseJualModal").modal("show");
		}
		
		chooseJual = function(NO_BUKTI, NO_SO, KODEC,NAMAC, ALAMAT, KOTA, KODEP, NAMAP ){
			$("#NO_JUAL").val(NO_BUKTI);
			$("#NO_SO").val(NO_SO);
			$("#KODEC").val(KODEC);
			$("#NAMAC").val(NAMAC);
			$("#ALAMAT").val(ALAMAT);
			$("#KOTA").val(KOTA);
			$("#KODEP").val(KODEP);
			$("#NAMAP").val(NAMAP);
			
			$("#browseJualModal").modal("hide");
			
			getSuratsd(NO_BUKTI);
					
		}
		
		$("#NO_JUAL").keypress(function(e){

			if(e.keyCode == 46){
				 e.preventDefault();
				 browseJual();
			}
		}); 
////////////////////////////////////////////////////////////////////




	function getSuratsd(bukti)
	{
		
	
		var mulai = (idrow==baris) ? idrow-1 : idrow;

		$.ajax(
			{
				type: 'GET',    
				url: "{{url('jual/browse_detail')}}",
				data: {

					nobukti: bukti,
				},
				success: function( resp )
				{
					var html = '';
					for(i=0; i<resp.length; i++){
				
						html+=`<tr>
                                    <td><input name='REC[]' id='REC${i}' value=${resp[i].REC+1} type='text' class='REC form-control' onkeypress='return tabE(this,event)' readonly></td>
                                    <td {{($golz == 'B') ? 'hidden' : '' }} ><input name='KD_BRG[]' data-rowid=${i} id='KD_BRG${i}' value="${resp[i].KD_BRG}" type='text' class='form-control KD_BRG' readonly></td>
                                    <td {{($golz == 'B') ? 'hidden' : '' }}><input name='NA_BRG[]' data-rowid=${i} id='NA_BRG${i}' value="${resp[i].NA_BRG}" type='text' class='form-control  NA_BRG' readonly></td>
                                    <td {{($golz == 'J') ? 'hidden' : '' }} ><input name='KD_BHN[]' data-rowid=${i} id='KD_BHN${i}' value="${resp[i].KD_BHN}" type='text' class='form-control KD_BHN' readonly></td>
                                    <td {{($golz == 'J') ? 'hidden' : '' }} ><input name='NA_BHN[]' data-rowid=${i} id='NA_BHN${i}' value="${resp[i].NA_BHN}" type='text' class='form-control  NA_BHN' readonly></td>
                                    <td><input name='SATUAN[]' data-rowid=${i} id='SATUAN${i}' value="${resp[i].SATUAN}" type='text' class='form-control  SATUAN' placeholder="Satuan"  readonly></td>
                                    <td>
										<input name='QTY[]' onclick='select()' onkeyup='hitung()' id='QTY${i}' value="${resp[i].QTY}" type='text' style='text-align: right' class='form-control QTY text-primary' readonly >
									</td>
									<td>
										<input name='HARGA[]' onclick='select()' onkeyup='hitung()' id='HARGA${i}' value="${resp[i].HARGA}" type='text' style='text-align: right' class='form-control HARGA text-primary' readonly> 
									</td>
									<td>
										<input name='TOTAL[]' onclick='select()' onkeyup='hitung()' id='TOTAL${i}' value="${resp[i].TOTAL}" type='text' style='text-align: right' class='form-control TOTAL text-primary' readonly> 
									</td>

                                    <td><input name='KET[]' id='KET${i}' value="${resp[i].KET}" type='text' class='form-control  KET' required></td>
                                    <td><button type='button' class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button></td>
                                </tr>`;
					}
					$('#detailSod').html(html);

					$(".QTY").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".QTY").autoNumeric('update');

					$(".HARGA").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".HARGA").autoNumeric('update');

					$(".TOTAL").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".TOTAL").autoNumeric('update');
					

					
					
					/*
					$(".KD_BHN").each(function() {
						var getid = $(this).attr('id');
						var noid = getid.substring(6,11);

						$("#KD_BHN"+noid).keypress(function(e){
							if(e.keyCode == 46){
								e.preventDefault();
								browseBhn(noid);
							}
						}); 
					});*/

					idrow=resp.length;
					baris=resp.length;

					nomor();
				}
			});
	}
	

	});




//////////////////////////////////////////////////////////////////


	function simpan() {

		hitung();
		
		var tgl = $('#TGL').val();
		var bulanPer = {{session()->get('periode')['bulan']}};
		var tahunPer = {{session()->get('periode')['tahun']}};
		
        var check = '0';
		
		
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

			if ( $('#KD_BRG').val()=='' ) 
            {				
			    check = '1';
				alert("Bahan# Harus Diisi.");
			}

			if ( $('#KD_BHN').val()=='' ) 
            {				
			    check = '1';
				alert("Bahan# Harus Diisi.");
			}

        
			if ( $('#KODEC').val()=='' ) 
            {				
			    check = '1';
				alert("Customer# Harus Diisi.");
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
		var TTOTAL_QTY = 0;
		var TTOTAL = 0;
		var PPNX = 0;
		var NETTX = 0;


		$(".QTY").each(function() {
			
			let z = $(this).closest('tr');
			var QTYX = parseFloat(z.find('.QTY').val().replace(/,/g, ''));
			var HARGAX = parseFloat(z.find('.HARGA').val().replace(/,/g, ''));

			var FLAGZ = $('#flagz').val();
	
			if (FLAGZ == 'AJ'){
				
				var QTYX  = ( QTYX * -1 ) ;			
				z.find('.QTY').autoNumeric('update');

			}

			var TOTALX = QTYX * HARGAX;

		
			z.find('.QTY').val(QTYX);			
		    z.find('.QTY').autoNumeric('update');

			z.find('.HARGA').val(HARGAX);			
		    z.find('.HARGA').autoNumeric('update');

			z.find('.TOTAL').val(TOTALX);			
		    z.find('.TOTAL').autoNumeric('update');

		
            TTOTAL_QTY +=QTYX;				
            TTOTAL +=TOTALX;	
			
		});
		
		
		NETTX = PPNX + TTOTAL;
		
		if(isNaN(TTOTAL_QTY)) TTOTAL_QTY = 0;

		$('#TTOTAL_QTY').val(numberWithCommas(TTOTAL_QTY));		
		$("#TTOTAL_QTY").autoNumeric('update');


		if(isNaN(TTOTAL)) TTOTAL_QTY = 0;

		$('#TTOTAL').val(numberWithCommas(TTOTAL));		
		$("#TTOTAL").autoNumeric('update');

		$('#PPN').val(numberWithCommas(PPNX));		
		$("#PPN").autoNumeric('update');

		$('#NETT').val(numberWithCommas(NETTX));		
		$("#NETT").autoNumeric('update');

		
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
//	    $("#CLOSEX").attr("disabled", true);

		$("#CARI").attr("readonly", true);	
	    $("#SEARCHX").attr("disabled", true);
		
	    $("#PLUSX").attr("hidden", false)
		   
			$("#NO_BUKTI").attr("readonly", true);		   
			$("#TGL").attr("readonly", false);
			$("#NO_SO").attr("readonly", true);
			$("#KODEC").attr("readonly", true);			
			$("#NAMAC").attr("readonly", true);
			$("#ALAMAT").attr("readonly", true);
			$("#KOTA").attr("readonly", true);			
			$("#NOTES").attr("readonly", false);
		
	

		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#KD_BHN" + i.toString()).attr("readonly", false);
			$("#KD_BRG" + i.toString()).attr("readonly", false);
			$("#NA_BHN" + i.toString()).attr("readonly", true);
			$("#NA_BRG" + i.toString()).attr("readonly", true);
			$("#SATUAN" + i.toString()).attr("readonly", false);
			$("#QTY" + i.toString()).attr("readonly", false);
			$("#HARGA" + i.toString()).attr("readonly", true);
			$("#TOTAL" + i.toString()).attr("readonly", false);

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
		
	    $(".NO_BUKTI").attr("readonly", true);	
		
		$("#TGL").attr("readonly", true);
		$("#KODEC").attr("readonly", true);			
		$("#NAMAC").attr("readonly", true);
		$("#ALAMAT").attr("readonly", true);
		$("#KOTA").attr("readonly", true);	
		$("#NOTES").attr("readonly", true);

		
		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#KD_BHN" + i.toString()).attr("readonly", true);
			$("#NA_BHN" + i.toString()).attr("readonly", true);
			$("#KD_BRG" + i.toString()).attr("readonly", true);
			$("#NA_BRG" + i.toString()).attr("readonly", true);
			$("#SATUAN" + i.toString()).attr("readonly", true);
			$("#QTY" + i.toString()).attr("readonly", true);
			$("#HARGA" + i.toString()).attr("readonly", true);
			$("#TOTAL" + i.toString()).attr("readonly", true);

			$("#KET" + i.toString()).attr("readonly", true);
			$("#DELETEX" + i.toString()).attr("hidden", true);
		}


		
	}


	function kosong() {
				
		 $('#NO_BUKTI').val("+");	
		 $('#NO_SO').val("");
		 $('#NO_SPM').val("");
		 $('#NO_SURATS').val("");		 
		 $('#KODEC').val("");
		 $('#NAMAC').val("");
		 $('#ALAMAT').val("");
		 $('#KOTA').val("");		 
	//	 $('#TGL').val("");	
		 $('#NOTES').val("");	
		 $('#TTOTAL_QTY').val("0.00");	
		 $('#TTOTAL').val("0.00");

		 $('#PPN').val("0.00");	
		 $('#NETT').val("0.00");
		 $('#DPP').val("0.00");
		 $('#PPNX').val("0.00");
		 
		var html = '';
		$('#detailx').html(html);	
		
	}
	
	function hapusTrans() {
		let text = "Hapus Transaksi "+$('#NO_BUKTI').val()+"?";
		if (confirm(text) == true) 
		{
			window.location ="{{url('/jual/delete/'.$header->NO_ID .'/?flagz='.$flagz.'&golz=' .$golz.'' )}}";
			//return true;
		} 
		return false;
	}
	

	function CariBukti() {
		
		var flagz = "{{ $flagz }}";
		var cari = $("#CARI").val();
		var loc = "{{ url('/jual/edit/') }}" + '?idx={{ $header->NO_ID}}&tipx=search&flagz=' + encodeURIComponent(flagz) + '&golz=' + encodeURIComponent(golz) + '&buktix=' +encodeURIComponent(cari);
		window.location = loc;
		
	}

    function tambah() {

        var x = document.getElementById('datatable').insertRow(baris + 1);
 
		html=`<tr>

                <td>
 					<input name='NO_ID[]' id='NO_ID${idrow}' type='hidden' class='form-control NO_ID' value='new' readonly> 
					<input name='REC[]' id='REC${idrow}' type='text' class='REC form-control' onkeypress='return tabE(this,event)' readonly>
	            </td>
						       
                <td {{( $golz =='B') ? '' : 'hidden' }} >
				    <input name='KD_BHN[]' data-rowid=${idrow} onblur='browseBahan(${idrow})' id='KD_BHN${idrow}' type='text' class='form-control  KD_BHN' >
                </td>
                <td {{( $golz =='B') ? '' : 'hidden' }} >
				    <input name='NA_BHN[]'   id='NA_BHN${idrow}' type='text' class='form-control  NA_BHN' required readonly>
                </td>

				<td {{( $golz =='J') ? '' : 'hidden' }} >
				    <input name='KD_BRG[]' data-rowid=${idrow} onblur='browseBarang(${idrow})' id='KD_BRG${idrow}' type='text' class='form-control  KD_BRG' >
                </td>
                <td {{( $golz =='J') ? '' : 'hidden' }} >
				    <input name='NA_BRG[]'   id='NA_BRG${idrow}' type='text' class='form-control  NA_BRG' required readonly>
                </td>

                <td>
				    <input name='SATUAN[]'   id='SATUAN${idrow}' type='text' class='form-control  SATUAN' readonly required>
                </td>
				
				<td>
		            <input name='QTY[]'  onblur='hitung()' value='0' id='QTY${idrow}' type='text' style='text-align: right' class='form-control QTY text-primary' required >
                </td>

				<td>
		            <input name='HARGA[]'  onblur='hitung()' value='0' id='HARGA${idrow}' type='text' style='text-align: right' class='form-control HARGA text-primary' required >
                </td>
				
				<td>
		            <input name='TOTAL[]'  onblur='hitung()' value='0' id='TOTAL${idrow}' type='text' style='text-align: right' class='form-control TOTAL text-primary' required >
                </td>
				
				
                <td>
				    <input name='KET[]'   id='KET${idrow}' type='text' class='form-control  KET' required>
                </td>
				
                <td>
					<button type='button' id='DELETEX${idrow}'  class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
                </td>				
         </tr>`;
				
        x.innerHTML = html;
        var html='';
		
 

		jumlahdata = 100;
		
		for (i = 0; i <= jumlahdata; i++) {
			$("#QTY" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#HARGA" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#TOTAL" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});

	
		}
		
		// $("#KD_BRG"+idrow).keypress(function(e){
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



<script src="autonumeric.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.5.4"></script>
<script src="https://unpkg.com/autonumeric"></script>

@endsection