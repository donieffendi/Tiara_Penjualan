@extends('layouts.main')

<style>
    .card {

    }

    .form-control:focus {
        background-color: #b5e5f9 !important;
    }
</style>

@section('content')

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dropdown with Select2</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
</head>


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

                    <form action="{{($tipx=='new')? url('/spm/store?flagz='.$flagz.'&golz='.$golz.'') : url('/spm/update/'.$header->NO_ID.'&flagz='.$flagz.'&golz='.$golz.'' ) }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
                        <div class="tab-content mt-3">
                            <div class="form-group row">
                                <div class="col-md-1" align="left">
                                    <label for="NO_BUKTI" class="form-label">Bukti#</label>
                                </div>
								

                                   <input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
                                    placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

									<input name="tipx" class="form-control tipx" id="tipx" value="{{$tipx}}" hidden>
									<input name="flagz" class="form-control flagz" id="flagz" value="{{$flagz}}" hidden>
									<input name="golz" class="form-control golz" id="golz" value="{{$golz}}" hidden>

								
								
                                <div class="col-md-2">
                                    <input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI"
                                    placeholder="Masukkan Bukti#" value="{{$header->NO_BUKTI}}" readonly>
                                </div>

                                <div class="col-md-1" align="right">
                                    <label for="TGL" class="form-label">Tgl</label>
                                </div>
                                <div class="col-md-2">
								  <input class="form-control date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}">
                                </div>

								<div class="col-md-2" align="right">
									<label for="JTEMPO" class="form-label">Jatuh Tempo</label>
								</div>
								<div class="col-md-2">
									<input class="form-control date" id="JTEMPO" name="JTEMPO" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->JTEMPO))}}">
								</div> 
								
								
                            </div>

                            <!-- <div class="form-group row">

								<div class="col-md-1" align="right">
									<label for="supxz">No Paspor</label>
								</div>
								<div class="col-md-4">
									<select name="supxz" class="form-control" id="supxz" required>
											<option value="">Select Suplier</option>
										@foreach ($sup as $sup)
											<option value="{{ $sup->KODES }}">{{ $sup->NAMAS }}</option>
										@endforeach
									</select>
								</div>
							
                            </div> -->
							

							<div class="form-group row">
								<div class="col-md-1" align="left">
									<label style="color:red">*</label>									
                                    <label for="NO_SO" class="form-label">SO#</label>
                                </div>
                               	<div class="col-md-2 input-group" >
                                  <input type="text" class="form-control NO_SO" id="NO_SO" name="NO_SO" placeholder="Pilih SO"value="{{$header->NO_SO}}" style="text-align: left" readonly >

                                </div>
                            </div>
                            
							<div class="form-group row">
								<div class="col-md-1" align="left">
									<!-- <label style="color:red;font-size:20px">* </label>	 -->
                                    <label for="KODEC" class="form-label">Cust</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control KODEC" id="KODEC" name="KODEC" placeholder="Kode Customer" value="{{$header->KODEC}}" readonly>
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

								<div class="col-md-2">
									<input type="text" class="form-control KOTA" id="KOTA" name="KOTA" value="{{$header->KOTA}}"placeholder="Kota" readonly>
								</div>
                            </div>


							<div class="form-group row">
								<div class="col-md-1" align="left">
									<!-- <label style="color:red;font-size:20px">* </label>	 -->
                                    <label for="KODEP" class="form-label">Sales#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control KODEP" id="KODEP" name="KODEP" placeholder="Kode Customer" value="{{$header->KODEP}}" readonly>
                                </div>

								<div class="col-md-4">
                                    <input type="text" class="form-control NAMAP" id="NAMAP" name="NAMAP" placeholder="-" value="{{$header->NAMAP}}" readonly>
                                </div>
                            </div>
                            
							<!-- <div class="form-group row">
                                <div class="col-md-1" align="left">
									<label style="color:red;font-size:20px">* </label>	
                                    <label for="TRUCK" class="form-label">Truck</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control TRUCK" id="TRUCK" name="TRUCK" placeholder="Masukkan Truck" value="{{$header->TRUCK}}">
                                </div>

                                <div class="col-md-1" align="right">
									<label style="color:red;font-size:20px">* </label>	
                                    <label for="SOPIR" class="form-label">Sopir</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control SOPIR" id="SOPIR" name="SOPIR" placeholder="Sopir" value="{{$header->SOPIR}}">
                                </div>
								
								<div class="col-md-1" align="left">
                                    <label class="form-label">Via</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control VIA" id="VIA" name="VIA" placeholder="Via" value="{{$header->VIA}}">
                                </div>
                            </div> -->

							<div class="form-group row">
                                <div class="col-md-1" align="left">
									<!-- <label style="color:red">*</label>									 -->
                                    <label for="NOTES" class="form-label">Notes</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NOTES" id="NOTES" name="NOTES" value="{{$header->NOTES}}" placeholder="Masukkan Notes" >
                                </div>
        
                            </div>
							


                        <div class="tab-content mt-3">
							
                            <table id="datatable" class="table table-striped table-border">
                                <thead>
                                    <tr>
										<th style="text-align: center;">No.</th>
                                        <!-- <th style="text-align: center;">
									       <label style="color:red;font-size:20px">* </label>									
                                           <label for="KD_BRG" class="form-label">SO#</label></th> -->
                                        <th {{($golz == 'B') ? 'hidden' : '' }} style="text-align: center;">Kode Barang</th>
                                        <th {{($golz == 'B') ? 'hidden' : '' }} style="text-align: center;">Uraian</th>
                                        <th {{($golz == 'J') ? 'hidden' : '' }} style="text-align: center;">Kode Bahan</th>
                                        <th {{($golz == 'J') ? 'hidden' : '' }} style="text-align: center;">Uraian</th>
                                        <th style="text-align: center;">Satuan</th>
                                        <th style="text-align: center;">Qty</th>
										<th style="text-align: center;">Ket</th>
										<th style="text-align: center;"></th>
                                        <th></th>
                                       						
                                    </tr>
                                </thead>
        
                                <tbody id="detailSod">
								<?php $no=0 ?>
								@foreach ($detail as $detail)		
                                    <tr>
                                        <td>
                                            <input type="hidden" name="NO_ID[]{{$no}}" id="NO_ID" type="text" value="{{$detail->NO_ID}}" 
                                            class="form-control NO_ID" onkeypress="return tabE(this,event)" readonly>
											
                                            <input name="REC[]" id="REC{{$no}}" type="text" value="{{$detail->REC}}" class="form-control REC" onkeypress="return tabE(this,event)" readonly style="text-align:center">

											<!-- <input name="NO_TERIMA[]" id="NO_TERIMA{{$no}}" type="text" class="form-control NO_TERIMA" value="{{$detail->NO_TERIMA}}" readonly hidden>
											<input name="MERK[]" id="MERK{{$no}}" type="text" class="form-control MERK" value="{{$detail->MERK}}" readonly hidden>
											<input name="NO_SERI[]" id="NO_SERI{{$no}}" type="text" class="form-control NO_SERI" value="{{$detail->NO_SERI}}" readonly hidden>
											<input name="TYP[]" id="TYP{{$no}}" type="text" class="form-control TYP" readonly hidden>
											<input name="ID_SOD[]" id="ID_SOD{{$no}}" type="text" class="form-control ID_SOD" value="{{$detail->ID_SOD}}" readonly hidden> -->
                                        
										</td>
									

										<!-- <td>
                                            <input name="NO_SO[]" data-rowid={{$no}}  id="NO_SO{{$no}}" type="text" class="form-control NO_SO" placeholder="No SO" value="{{$detail->NO_SO}}">
                                        </td> -->
                                        <td {{($golz == 'B') ? 'hidden' : '' }}>
                                            <input name="KD_BRG[]"  id="KD_BRG{{$no}}" type="text" class="form-control KD_BRG" placeholder="Barang#" value="{{$detail->KD_BRG}}">
                                        </td>
                                        <td {{($golz == 'B') ? 'hidden' : '' }}>
                                            <input name="NA_BRG[]" id="NA_BRG{{$no}}" type="text" class="form-control NA_BRG" placeholder="Nama" value="{{$detail->NA_BRG}}">
                                        </td>

										<td {{($golz == 'J') ? 'hidden' : '' }}>
                                            <input name="KD_BHN[]"  id="KD_BHN{{$no}}" type="text" class="form-control KD_BHN" placeholder="Barang#" value="{{$detail->KD_BHN}}">
                                        </td>
                                        <td {{($golz == 'J') ? 'hidden' : '' }}>
                                            <input name="NA_BHN[]" id="NA_BHN{{$no}}" type="text" class="form-control NA_BHN" placeholder="Nama" value="{{$detail->NA_BHN}}">
                                        </td>


                                        <td>
                                            <input name="SATUAN[]" id="SATUAN{{$no}}" type="text" class="form-control SATUAN" placeholder="Satuan" value="{{$detail->SATUAN}}">
                                        </td>
										<td>
											<input name="QTY[]" onkeyup="hitung()" id="QTY{{$no}}" type="text" style="text-align: right"  class="form-control QTY text-primary" value="{{$detail->QTY}}">

											<input name="HARGA[]" onkeyup="hitung()" id="HARGA{{$no}}" type="text" style="text-align: right"  class="form-control HARGA text-primary" value="{{$detail->HARGA}}" hidden >

											<input name="TOTAL[]" onkeyup="hitung()" id="TOTAL{{$no}}" type="text" style="text-align: right"  class="form-control TOTAL text-primary" value="{{$detail->TOTAL}}" hidden readonly>
										</td>
										<td>
                                            <input name="KET[]" id="KET{{$no}}" type="text" class="form-control KET" placeholder="Ket" value="{{$detail->KET}}" required>
                                        </td>

                                    </tr>
								
								<?php $no++; ?>
								@endforeach
                                </tbody>

								<tfoot>
								<td></td>
                                    <td></td>
                                    <td {{($golz == 'B') ? 'hidden' : '' }}></td>
                                    <td {{($golz == 'B') ? 'hidden' : '' }}></td>
                                    <td {{($golz == 'J') ? 'hidden' : '' }}></td>
                                    <td {{($golz == 'J') ? 'hidden' : '' }}></td>
                                    <td><input class="form-control TQTY  text-primary font-weight-bold" style="text-align: right"  id="TQTY" name="TQTY" value="{{$header->TOTAL_QTY}}" readonly></td>
                                    <td></td>
                                    <td><input class="form-control TTOTAL  text-primary font-weight-bold" hidden style="text-align: right"  id="TTOTAL" name="TTOTAL" value="{{$header->TOTAL}}" readonly></td>
                                    <td></td>
                                </tfoot>
                            </table>

							<div class="col-md-2 row">
								<a type="button" id='PLUSX' onclick="tambah()" class="fas fa-plus fa-sm md-3" style="font-size: 20px" ></a>

							</div>	
						
					</div>
                        </div> 

                        <hr style="margin-top: 30px; margin-buttom: 30px">
						<!-- dari sini shelvi-->
						
						<!-- sampai sini shelvi-->
						   
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button type="button" id='TOPX'  onclick="location.href='{{url('/spm/edit/?idx=' .$idx. '&tipx=top&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-primary">Top</button>
								<button type="button" id='PREVX' onclick="location.href='{{url('/spm/edit/?idx='.$header->NO_ID.'&tipx=prev&flagz='.$flagz.'&golz='.$golz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Prev</button>
								<button type="button" id='NEXTX' onclick="location.href='{{url('/spm/edit/?idx='.$header->NO_ID.'&tipx=next&flagz='.$flagz.'&golz='.$golz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Next</button>
								<button type="button" id='BOTTOMX' onclick="location.href='{{url('/spm/edit/?idx=' .$idx. '&tipx=bottom&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button type="button" id='NEWX' onclick="location.href='{{url('/spm/edit/?idx=0&tipx=new&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-warning">New</button>
								<button type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button type="button" id='UNDOX' onclick="location.href='{{url('/spm/edit/?idx=' .$idx. '&tipx=undo&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-info">Undo</button>  
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button type="button" id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								<button type="button" id='CLOSEX'  onclick="location.href='{{url('/spm?flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-secondary">Close</button>
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
			<h5 class="modal-title" id="browseCustModalLabel">Cari Sales Order</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-so">
				<thead>
					<tr>
						<th>No Bukti</th>
						<th>Tanggal</th>
						<!-- <th>No Terima</th> -->
						<th>Customer</th>
						<th>Kode</th>
						<th>Barang</th>
						<th>Satuan</th>
						<!-- <th>Seri#</th> -->
						<!-- <th>Ket</th> -->
						<th>Qty</th>
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


	
	
	

@endsection

@section('footer-scripts')
<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script> -->

<script>
	var idrow = 1;
	var baris = 1;

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
		$searchx = $('#CARI').val();
		
		
        if ( $tipx == 'new' )
		{
			 baru();
            //  tambah();				 
		}

        if ( $tipx != 'new' )
		{
			 ganti();			
		}    
		
		$("#TQTY").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#TTOTAL").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});


		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#QTY" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#HARGA" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#TOTAL" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
		}	


		
		// $('#supxz').select2({
        //     minimumInputLength:2,
        //     placeholder:'Select Suplier',
        //     ajax:{
        //         url:route('sup/browsesupz'),
        //         dataType:'json',
        //         processResults:data=>{
                    
        //             return {
        //                 results:data.map(res=>{
        //                     return {text:res.NAMAS,id:res.KODES}
        //                 })
        //             }
        //         }
        //     }
        // })
		
		
				
		
        $('body').on('click', '.btn-delete', function() {
			var val = $(this).parents("tr").remove();
			baris--;
			nomor();
			
		});

		$('.date').datepicker({  
            dateFormat: 'dd-mm-yy'
		});
		
		
 	
		
///////////////////////////////////////////////////////////////////////
	

//////////////////////////////////////////////////////////////////

	var dTableSo;
	var rowidSo;
	loadDataSo = function(){
		
		$.ajax(
		{
			type: 'GET',    
			url: "{{url('spm/browseSo')}}",
			data: {
				// kdbrg: kode,
				'GOL': "{{$golz}}",
			},
			success: function( response )
			{
				resp = response;
				if(dTableSo){
					dTableSo.clear();
				}
				for(i=0; i<resp.length; i++){
					
					dTableSo.row.add([
						'<a href="javascript:void(0);" onclick="chooseSo(\''+resp[i].NO_BUKTI+'\',  \''+resp[i].KODEC+'\',  \''+resp[i].NAMAC+'\',  \''+resp[i].ALAMAT+'\',  \''+resp[i].KOTA+'\', \''+resp[i].KODEP+'\',  \''+resp[i].NAMAP+'\',   \''+resp[i].KD_BRG+'\',  \''+resp[i].NA_BRG+'\',  \''+resp[i].SATUAN+'\',  \''+resp[i].SISA+'\', \''+resp[i].NO_ID+'\',  \''+resp[i].HARGA+'\')">'+resp[i].NO_BUKTI+'</a>',
						resp[i].TGL,
						resp[i].NAMAC,
						resp[i].KD_BRG,
						resp[i].NA_BRG,
						resp[i].SATUAN,
						resp[i].QTY,
						resp[i].KIRIM,
						resp[i].SISA,
					]);
				}
				dTableSo.draw();
			}
		});
	}
	
	dTableSo = $("#table-so").DataTable({

		columnDefs: 
		[
			{
				className: "dt-right", 
				targets: [6,7,8],
			},		
			{
				targets: 1,
				render: $.fn.dataTable.render.moment( 'DD-MM-YYYY' ),
			}
		],

	});
	
		browseSo = function(rid){
			rowidSo = rid;
			loadDataSo();
			$("#browseSoModal").modal("show");
		}
	
	chooseSo = function(NO_BUKTI,KODEC,NAMAC,ALAMAT,KOTA, KODEP, NAMAP ){
		$("#NO_SO").val(NO_BUKTI);
		$("#KODEC").val(KODEC);
		$("#NAMAC").val(NAMAC);
		$("#ALAMAT").val(ALAMAT);
		$("#KOTA").val(KOTA);
		$("#KODEP").val(KODEP);
		$("#NAMAP").val(NAMAP);
			
		$("#browseSoModal").modal("hide");

		getSod(NO_BUKTI);
	}
	
	$("#NO_SO").keypress(function(e){
		if(e.keyCode == 46){
			e.preventDefault();
			browseSo();
		}
	}); 

//////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////

       
////////////////////////////////////////////////////////////////////


	function getSod(bukti)
	{
		
		var mulai = (idrow==baris) ? idrow-1 : idrow;

		$.ajax(
			{
				type: 'GET',    
				url: "{{url('spm/browse_detail')}}",
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

										<input name='HARGA[]' onclick='select()' onkeyup='hitung()' id='HARGA${i}' value="${resp[i].HARGA}" type='text' style='text-align: right' class='form-control HARGA text-primary' hidden readonly> 

										<input name='TOTAL[]' onclick='select()' onkeyup='hitung()' id='TOTAL${i}' value="${resp[i].TOTAL}" type='text' style='text-align: right' class='form-control TOTAL text-primary' hidden readonly> 
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

//////////////////////////////////////////////////////////////////


}); 


//////////////////////////////////////////////////////////////////
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
		
		var tgl = $('#TGL').val();
		var bulanPer = {{session()->get('periode')['bulan']}};
		var tahunPer = {{session()->get('periode')['tahun']}};
		
        var check = '0';
		
		
		
			// if (cekDetail())
			// {	
			//     check = '1';
			// 	alert("#Barang ada yang kosong. ")
			// }
			
			
			$(".NO_SO").each(function() {
			var noso = $(this).val();
			if(noso=='')
			{
				var val = $(this).parents("tr").remove();
				baris--;
				nomor();
			}
		});

		if ( $('#KODEC').val()=='' ) 
		{			
			check = '1';
			alert("No Customer Harus diisi.");
		}
		// if ( $('#TRUCK').val()=='' ) 
		// {			
		// 	check = '1';
		// 	alert("Truk Harus diisi.");
		// }
		// if ( $('#SOPIR').val()=='' ) 
		// {			
		// 	check = '1';
		// 	alert("Sopir Harus diisi.");
		// }
		if ( tgl.substring(3,5) != bulanPer ) 
		{
			check = '1';
			alert("Bulan ("+tgl+") tidak sama dengan Periode");
		}	
		if ( tgl.substring(tgl.length-4) != tahunPer )
		{
			check = '1';
			alert("Tahun ("+tgl+") tidak sama dengan Periode");
		}	 
	
		// if ( check == '0' )
		// {
		// 	document.getElementById("entri").submit();  
		// }

		(check==0) ? document.getElementById("entri").submit() : alert('Masih ada kesalahan');
			
	}
		
    function nomor() {
		var i = 1;
		$(".REC").each(function() {
			$(this).val(i++);
		});
		
	//	hitung();
	
	}

   function hitung() {
	var TQTY = 0;
		var TTOTAL = 0;

		$(".QTY").each(function() {
			let z = $(this).closest('tr');
			var QTYX = parseFloat($(this).val().replace(/,/g, ''));
			var HARGAX = parseFloat(z.find('.HARGA').val().replace(/,/g, ''));
		
            var TOTALX = HARGAX * QTYX;
			z.find('.TOTAL').val(TOTALX);
		    z.find('.TOTAL').autoNumeric('update');
		
            TQTY += QTYX;	
            TTOTAL += TOTALX;	
		});
		

		if(isNaN(TQTY)) TQTY = 0;
		if(isNaN(TTOTAL)) TTOTAL = 0;

		$('#TQTY').val(numberWithCommas(TQTY));		
		$('#TTOTAL').val(numberWithCommas(TTOTAL));		

		$("#TQTY").autoNumeric('update');
		$("#TTOTAL").autoNumeric('update');
	}


	$(".NO_SO").keypress(function(e){
		if(e.keyCode == 46){
			e.preventDefault();
			browseSo(eval($(this).data("rowid")));
		}
	});
	
  
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
			$("#NO_SO").attr("readonly", true);		   
			$("#TGL").attr("readonly", false);
			$("#JTEMPO").attr("readonly", false);
			$("#KODES").attr("readonly", true);
			$("#NAMAS").attr("readonly", true);			
			$("#ALAMAT").attr("readonly", true);
			$("#KOTA").attr("readonly", true);
			$("#TRUCK").attr("readonly", false);
			$("#SOPIR").attr("readonly", false);
			$("#VIA").attr("readonly", false);

			
			$("#NOTES").attr("readonly", false);
				

		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#KD_BRG" + i.toString()).attr("readonly", true);
			$("#NA_BRG" + i.toString()).attr("readonly", true);
			$("#KD_BHN" + i.toString()).attr("readonly", true);
			$("#NA_BHN" + i.toString()).attr("readonly", true);
			$("#SATUAN" + i.toString()).attr("readonly", true);
			$("#QTY" + i.toString()).attr("readonly", false);
			$("#HARGA" + i.toString()).attr("readonly", false);
			$("#TOTAL" + i.toString()).attr("readonly", true);
			$("#KET" + i.toString()).attr("readonly", false);
			$("#DELETEX" + i.toString()).attr("hidden", false);

			$tipx = $('#tipx').val();
		
			
			// if ( $tipx != 'new' )
			// {
			// 	$("#NO_SO" + i.toString()).attr("readonly", true);	
			// 	$("#NO_SO" + i.toString()).removeAttr('onblur');
				
			// 	$("#KD_BRG" + i.toString()).attr("readonly", true);	
			// 	$("#KD_BRG" + i.toString()).removeAttr('onblur');
			// } 
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
	    $(".NO_SO").attr("readonly", true);	
		
		$("#TGL").attr("readonly", true);
		$("#JTEMPO").attr("readonly", true);
		$("#KODES").attr("readonly", true);
		$("#NAMAS").attr("readonly", true);
		$("#ALAMAT").attr("readonly", true);
		$("#KOTA").attr("readonly", true);
		$("#TRUCK").attr("readonly", true);
		$("#SOPIR").attr("readonly", true);
		$("#VIA").attr("readonly", true);
	
		
		$("#NOTES").attr("readonly", true);

		
		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#KD_BRG" + i.toString()).attr("readonly", true);
			$("#NA_BRG" + i.toString()).attr("readonly", true);
			$("#KD_BHN" + i.toString()).attr("readonly", true);
			$("#NA_BHN" + i.toString()).attr("readonly", true);
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
		 $('#KODEC').val("");	
		 $('#NAMAC').val("");	
		 $('#ALAMAT').val("");	
		 $('#KOTA').val("");	
		 $('#NOTES').val("");	
		 $('#TTOTAL_QTY').val("0.00");	
		 $('#TTOTAL').val("0.00");
		 
		var html = '';
		$('#detailx').html(html);	
		
	}
	
	function hapusTrans() {
		let text = "Hapus Transaksi "+$('#NO_BUKTI').val()+"?";
		if (confirm(text) == true) 
		{
			window.location ="{{url('/spm/delete/'.$header->NO_ID .'/?flagz='.$flagz.'' )}}";
			//return true;
		} 
		return false;
	}
	

	function CariBukti() {
		
		var flagz = "{{ $flagz }}";
		var cari = $("#CARI").val();
		var loc = "{{ url('/spm/edit/') }}" + '?idx={{ $header->NO_ID}}&tipx=search&flagz=' + encodeURIComponent(flagz) + '&buktix=' +encodeURIComponent(cari);
		window.location = loc;
		
	}


    // function tambah() {

    //     var x = document.getElementById('datatable').insertRow(baris + 1);
 
	// 	html=`<tr>

    //             <td>
 	// 				<input name='NO_ID[]' id='NO_ID${idrow}' type='hidden' class='form-control NO_ID' value='new' readonly> 
	// 				<input name='REC[]' id='REC${idrow}' type='text' class='REC form-control' onkeypress='return tabE(this,event)' readonly>
	//             </td>

	// 			<td>
	// 				<input name='KD_BHN[]' data-rowid=${idrow} id='KD_BHN${idrow}' type='text' class='form-control KD_BHN' >
	// 			</td>
	// 			<td>
	// 				<input name='NA_BHN[]' id='NA_BHN${idrow}' type='text' class='form-control  NA_BHN'  readonly>
	// 			</td>


    //             <td>
	// 			    <input name='SATUAN[]' id='SATUAN${idrow}' type='text' class='form-control SATUAN'>
    //             </td>

	// 			<td>
	// 	            <input name='QTY[]' onclick='select()' onblur='hitung()' value='0' id='QTY${idrow}' type='text' style='text-align: right' class='form-control QTY text-primary' required >
    //             </td>

	// 			<td>
	// 	            <input name='HARGA[]' onclick='select()' onblur='hitung()' value='0' id='HARGA${idrow}' type='text' style='text-align: right' class='form-control HARGA text-primary' required >
    //             </td>

				
	// 			<td>
	// 	            <input name='TOTAL[]' onclick='select()' onblur='hitung()' value='0' id='TOTAL${idrow}' type='text' style='text-align: right' class='form-control TOTAL text-primary' >
    //             </td>				
					
    //             <td>
	// 			    <input name='KET[]'   id='KET${idrow}' type='text' class='form-control  KET' required>
    //             </td>
				
    //             <td>
	// 				<button type='button' id='DELETEX${idrow}'  class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
    //             </td>				
    //      </tr>`;
				
    //     x.innerHTML = html;
    //     var html='';
		
		
		
	// 	jumlahdata = 100;
	// 	for (i = 0; i <= jumlahdata; i++) {
	// 		$("#QTY" + i.toString()).autoNumeric('init', {
	// 			aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});


	// 		$("#HARGA" + i.toString()).autoNumeric('init', {
	// 			aSign: '<?php echo ''; ?>',
	// 			vMin: '-999999999.99'
	// 		});

	// 		$("#TOTAL" + i.toString()).autoNumeric('init', {
	// 			aSign: '<?php echo ''; ?>',
	// 			vMin: '-999999999.99'
	// 		});			 

					
	// 	}

		


    //     idrow++;
    //     baris++;
    //     nomor();
		
	// 	$(".ronly").on('keydown paste', function(e) {
    //          e.preventDefault();
    //          e.currentTarget.blur();
    //      });
    // }
</script>
<!-- 
<script src="autonumeric.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.5.4"></script>
<script src="https://unpkg.com/autonumeric"></script> -->
@endsection