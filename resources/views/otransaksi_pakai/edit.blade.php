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

                    <form action="{{($tipx=='new')? url('/pakai/store?flagz='.$flagz.'&golz='.$golz.'') : url('/pakai/update/'.$header->NO_ID.'&flagz='.$flagz.'&golz='.$golz.'' ) }}" method="POST" name ="entri" id="entri" >
  
	    			      @csrf
        
                        <div class="tab-content mt-3">
        
                            <div class="form-group row">
								<div class="col-md-1" align="left">
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

								<div class="col-md-1" align="right">
                                    <label for="TGL" class="form-label">Tgl</label>
                                </div>
                                <div class="col-md-2">
                                   <input class="form-control date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}" >
                                </div>
                            </div>
        
							<div class="form-group row">
                                <div class="col-md-1" align="left">
									<label style="color:red;font-size:20px">* </label>								
                                    <label class="form-label">OK#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control NO_OK" id="NO_OK" name="NO_OK" placeholder="No Order Kerja" value="{{$header->NO_ORDER}}" readonly>
                                </div>
                                <div class="col-md-1" align="right">
                                    <label class="form-label">SO#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control NO_SO" id="NO_SO" name="NO_SO" placeholder="No Sales Order" value="{{$header->NO_SO}}" readonly>
                                </div>
                                <div class="col-md-1" align="right">
									<label style="color:red;font-size:20px">* </label>
                                    <label class="form-label">Proses</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control KD_PRSH" id="KD_PRSH" name="KD_PRSH" placeholder="Kode Proses" value="{{$header->KD_PRS}}"  readonly>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control NO_PRS" id="NO_PRS" name="NO_PRS" placeholder="No Proses" value="{{$header->NO_PRS}}" readonly>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-1" align="left">
                                    <label class="form-label">Barang</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control KD_BRG" id="KD_BRG" name="KD_BRG" placeholder="Barang#" value="{{$header->KD_BRG}}" readonly>
                                </div>
                                <div class="col-md-1" align="right">			
                                    <label class="form-label">FO#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control NO_FO" id="NO_FO" name="NO_FO" placeholder="No Formula" value="{{$header->NO_FO}}" readonly>
                                </div>
                                <div class="col-md-1">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control NA_PRSH" id="NA_PRSH" name="NA_PRSH" placeholder="Nama Proses" value="{{$header->NA_PRS}}" readonly>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-1" align="left">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control NA_BRG" id="NA_BRG" name="NA_BRG" placeholder="Nama Barang" value="{{$header->NA_BRG}}" readonly>
                                </div>
                            </div>

							
                            <div class="form-group row">
                                <div class="col-md-1" align="left">
                                    <label class="form-label">Qty In</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control QTYI" id="QTYI" name="QTYI" value="{{$header->QTY_IN}}" onclick="select()" onkeyup="hitung()">
                                </div>
                                <div class="col-md-1" align="right">
                                    <label class="form-label">Qty Out</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control QTYO" id="QTYO" name="QTYO" value="{{$header->QTY_OUT}}" onclick="select()" onkeyup="hitung()">
                                </div>
                                <div class="col-md-1" align="right">
                                    <label class="form-label">Satuan</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control SATUANH" id="SATUANH" name="SATUANH" placeholder="Satuan" value="{{$header->SATUAN}}" readonly>
                                </div>
                            </div>

							<div class="form-group row">
                                <div class="col-md-1" align="left">
                                    <label for="NOTES" class="form-label">Notes</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NOTES" id="NOTES" name="NOTES" placeholder="Masukkan Notes" value="{{$header->NOTES}}">
                                </div>
                            </div>
							
                            
                            <hr style="margin-top: 30px; margin-buttom: 30px">
							
							<div style="overflow-y:scroll;" class="col-md-12 scrollable" align="right">
							
								<table id="datatable" class="table table-striped table-border">

									<thead>
										<tr>
											<th style="text-align: center;" width="75px">No.</th>
											<th style="text-align: center;">
											<label style="color:red;font-size:20px">* </label>									
											<label class="form-label">Kode</label></th>
											<th style="text-align: center;">Uraian</th>
											<th style="text-align: center;">Satuan</th>
											<th style="text-align: center;">Qty FO</th>
											<th style="text-align: center;">Qty Asli</th>
											<th style="text-align: center;">Ket</th>
											<th style="text-align: center;"></th>
											<th></th>										
										</tr>
										
									</thead>
									<tbody id="detailPakai">
									
									<?php $no=0 ?>
									@foreach ($detail as $detail)
										<tr>
											<td>
												<input type="hidden" name="NO_ID[]" id="NO_ID{{$no}}" type="text" value="{{$detail->NO_ID}}" 
												class="form-control NO_ID" onkeypress="return tabE(this,event)" readonly>
												
												<input name="REC[]" id="REC{{$no}}" type="text" value="{{$detail->REC}}" class="form-control REC" onkeypress="return tabE(this,event)" readonly>
											</td>
											
											<td>
												<input name="KD_BHN[]" id="KD_BHN{{$no}}" type="text" class="form-control KD_BHN" placeholder="Bahan#" value="{{$detail->KD_BHN}}" readonly required>
											</td>
											<td>
												<input name="NA_BHN[]" id="NA_BHN{{$no}}" type="text" class="form-control NA_BHN" value="{{$detail->NA_BHN}}" readonly required>
											</td>
											<td>
												<input name="SATUAN[]" id="SATUAN{{$no}}" type="text" class="form-control SATUAN" placeholder="Satuan" value="{{$detail->SATUAN}}" readonly required>
											</td>
											<td>
												<input name="QTY[]" onclick="select()" onkeyup="hitung()" id="QTY{{$no}}" type="text" style="text-align: right"  class="form-control QTY text-primary" value="{{$detail->QTY}}" readonly> 
											</td>  
											<td>
												<input name="QTYX[]" onclick="select()" onkeyup="hitung()" id="QTYX{{$no}}" type="text" style="text-align: right"  class="form-control QTYX text-primary" value="{{$detail->QTYX}}" readonly>
											</td>   
											
											<td>
												<input name="KET[]" id="KET{{$no}}" type="text" class="form-control KET" placeholder="Ket" value="{{$detail->KET}}" required>
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
										<td><input class="form-control TTOTAL_QTY  text-primary font-weight-bold" style="text-align: right"  id="TTOTAL_QTY" name="TTOTAL_QTY" value="{{$header->TOTAL_QTY}}" readonly></td>
										<td></td>
										<td></td>
									</tfoot>
								</table>					
							</div>

							<!-- <div class="col-md-2 row">
								<a type="button" id='PLUSX' onclick="tambah()" class="fas fa-plus fa-sm md-3" style="font-size: 20px" ></a>

							</div>			 -->
							
                        </div> 
						
						<hr style="margin-top: 30px; margin-buttom: 30px">	
						
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button type="button" id='TOPX'  onclick="location.href='{{url('/pakai/edit/?idx=' .$idx. '&tipx=top&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-primary">Top</button>
								<button type="button" id='PREVX' onclick="location.href='{{url('/pakai/edit/?idx='.$header->NO_ID.'&tipx=prev&flagz='.$flagz.'&golz='.$golz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Prev</button>
								<button type="button" id='NEXTX' onclick="location.href='{{url('/pakai/edit/?idx='.$header->NO_ID.'&tipx=next&flagz='.$flagz.'&golz='.$golz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Next</button>
								<button type="button" id='BOTTOMX' onclick="location.href='{{url('/pakai/edit/?idx=' .$idx. '&tipx=bottom&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button type="button" id='NEWX' onclick="location.href='{{url('/pakai/edit/?idx=0&tipx=new&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-warning">New</button>
								<button type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button type="button" id='UNDOX' onclick="location.href='{{url('/pakai/edit/?idx=' .$idx. '&tipx=undo&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-info">Undo</button>  
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button type="button" id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								<button type="button" id='CLOSEX'  onclick="location.href='{{url('/pakai?flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-secondary">Close</button>
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
	

	<div class="modal fade" id="browseOkModal" tabindex="-1" role="dialog" aria-labelledby="browseOkModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseOkModalLabel">Cari Order Kerja</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-ok">
				<thead>
					<tr>
						<th>No Bukti</th>
						<th>Kode Proses</th>
						<th>Proses</th>
						<th>Kode</th>
						<th>Nama</th>
						<th>Qty</th>
						<th>FO#</th>
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

	<div class="modal fade" id="browseXdModal" tabindex="-1" role="dialog" aria-labelledby="browseXdModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseXdModalLabel">Lihat Proses</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-xd">
				<thead>
					<tr>
						<th>Kode</th>
						<th>Nama</th>
						<th>Urutan</th>
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

	<div class="modal fade" id="browsePrsModal" tabindex="-1" role="dialog" aria-labelledby="browsePrsModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browsePrsModalLabel">Lihat Proses</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-prs">
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
	
	<div class="modal fade" id="browseBhnModal" tabindex="-1" role="dialog" aria-labelledby="browseBhnModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseBhnModalLabel">Lihat Proses</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bhn">
				<thead>
					<tr>
						<th>Kode</th>
						<th>Nama</th>
						<th>Jenis</th>
						<th>Satuan</th>
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
					// tambah();
					// var nomer = idrow-1;
					// console.log("REC"+nomor);
					// document.getElementById("REC"+nomor).focus();
					// // form.submit();
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
		
		$("#QTYI").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#QTYO").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#TTOTAL_QTY").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});


		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {

			$("#QTY" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#QTYX" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});

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
		
		
//////////////////////////////////////////////////////////////////////////////////////////////////////		
		
		var dTableOk;
		loadDataOk = function(){
			$.ajax(
			{
				type: 'GET',    
				url: "{{url('pakai/browseOk')}}",
				data: {

					'FLAG': "{{$flagz}}",
				},
				success: function( response )
				{
					resp = response;
					if(dTableOk){
						dTableOk.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableOk.row.add([
							'<a href="javascript:void(0);" onclick="chooseOk(\''+resp[i].NO_BUKTI+'\',  \''+resp[i].KD_PRS+'\', \''+resp[i].NA_PRS+'\', \''+resp[i].NO_PRS+'\',  \''+resp[i].SATUAN+'\',  \''+resp[i].KD_BRG+'\',  \''+resp[i].NA_BRG+'\',  \''+resp[i].PROSES+'\',  \''+resp[i].NO_SO+'\',  \''+resp[i].NO_FO+'\')">'+resp[i].NO_BUKTI+'</a>',
							resp[i].KD_PRS,
							resp[i].NA_PRS,
							resp[i].KD_BRG,
							resp[i].NA_BRG,
							Intl.NumberFormat('en-US').format(resp[i].PROSES),	
							resp[i].NO_FO,
						]);
					}
					dTableOk.draw();
				}
			});
		}
		
		dTableOk = $("#table-ok").DataTable({
            columnDefs: [
                {
                    "className": "dt-right", 
                    "targets": 5,
                },			
				
            ],
		});
		
		browseOk = function(){
			loadDataOk();
			$("#browseOkModal").modal("show");
		}
		
		chooseOk = function(NO_BUKTI, KD_PRS, NA_PRS, NO_PRS, SATUAN, KD_BRG, NA_BRG, PROSES, NO_SO, NO_FO){
			$("#NO_OK").val(NO_BUKTI);
			$("#KD_PRSH").val(KD_PRS);
			$("#NA_PRSH").val(NA_PRS);
			$("#NO_PRS").val(NO_PRS);
			$("#KD_BRG").val(KD_BRG);
			$("#NA_BRG").val(NA_BRG);
			$("#QTYI").val(PROSES);		
			$("#SATUANH").val(SATUAN);		
			$("#NO_SO").val(NO_SO);		
			$("#NO_FO").val(NO_FO);			
			$("#browseOkModal").modal("hide");
			$("#QTYI").autoNumeric('update');
			$("#QTYO").autoNumeric('update');
			getFod(NO_FO);
			
			// if (NO_PRS=='1')
			// {
			// 	cekOrderkWIP(function(data) {
			// 		if(data)
			// 		{
			// 			alert("Masih ada order kerja WIP yang belum diproses!");
			// 		}
			// 	});
			// }
		}
		
		$("#NO_OK").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseOk();
			}
		}); 
///////////////////////////////////////////////////////////////////////////////////////////////////
		var dTableXd;
		loadDataXd = function(){
			$.ajax(
			{
				type: 'GET',    
				url: "{{url('pakai/browseXd')}}",
				data: {
					nobukti: $("#NO_OK").val(),
				},
				success: function( resp )
				{
					if(dTableXd){
						dTableXd.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableXd.row.add([
							'<a href="javascript:void(0);" onclick="chooseXd(\''+resp[i].KD_PRS+'\',  \''+resp[i].NA_PRS+'\', \''+resp[i].NO_PRS+'\')">'+resp[i].KD_PRS+'</a>',
							resp[i].NA_PRS,
							resp[i].NO_PRS,
						]);
					}
					dTableXd.draw();
				}
			});
		}
		
		dTableXd = $("#table-xd").DataTable({
			"ordering": false,
		});
		
		browseXd = function(){
			loadDataXd();
			$("#browseXdModal").modal("show");
		}
		
		chooseXd = function(KD_PRS, NA_PRS, NO_PRS){
			$("#KD_PRSH").val(KD_PRS);
			$("#NA_PRSH").val(NA_PRS);			
			$("#NO_PRS").val(NO_PRS);
			$("#browseXdModal").modal("hide");
			if (NO_PRS=='1')
			{
				cekOrderkWIP(function(data) {
					if(data)
					{
						alert("Masih ada order kerja WIP yang belum diproses!");
					}
				});
			}
			else
			{
				getFod($("#NO_FO").val());
			}
		}
		
		$("#KD_PRSH").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseXd();
			}
		}); 
///////////////////////////////////////////////////////////////////////////////////////////////////
 		var dTablePrs;
		var rowidPrs;
		loadDataPrs = function(){
			$.ajax(
			{
				type: 'GET',    
				url: "{{url('pakai/browsePrs')}}",
				success: function( response )
				{
					resp = response;
					if(dTablePrs){
						dTablePrs.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTablePrs.row.add([
							'<a href="javascript:void(0);" onclick="choosePrs(\''+resp[i].KD_PRS+'\',  \''+resp[i].NA_PRS+'\')">'+resp[i].KD_PRS+'</a>',
							resp[i].NA_PRS,
						]);
					}
					dTablePrs.draw();
				}
			});
		}
		
		dTablePrs = $("#table-prs").DataTable({
			
		});
		
		browsePrs = function(rid){
			rowidPrs = rid;
			loadDataPrs();
			$("#browsePrsModal").modal("show");
		}
		
		choosePrs = function(KD_PRS,NA_PRS){
			$("#KD_PRS"+rowidPrs).val(KD_PRS);
			$("#NA_PRS"+rowidPrs).val(NA_PRS);		
			$("#browsePrsModal").modal("hide");
		}
		
		
		$("#KD_PRS0").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browsePrs(0);
			}
		}); 
////////////////////////////////////////////////////////////////////////////////////
 		var dTableBhn;
		var rowidBhn;
		loadDataBhn = function(){
			$.ajax(
			{
				type: 'GET',    
				url: "{{url('pakai/browseBhn')}}",
				success: function( response )
				{
					resp = response;
					if(dTableBhn){
						dTableBhn.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBhn.row.add([
							'<a href="javascript:void(0);" onclick="chooseBhn(\''+resp[i].KD_BHN+'\',  \''+resp[i].NA_BHN+'\',  \''+resp[i].SATUAN+'\')">'+resp[i].KD_BHN+'</a>',
							resp[i].NA_BHN,
							resp[i].JENIS,
							resp[i].SATUAN,
						]);
					}
					dTableBhn.draw();
				}
			});
		}
		
		dTableBhn = $("#table-bhn").DataTable({
			
		});
		
		browseBhn = function(rid){
			rowidBhn = rid;
			loadDataBhn();
			$("#browseBhnModal").modal("show");
		}
		
		chooseBhn = function(KD_BHN,NA_BHN,SATUAN){
			$("#KD_BHN"+rowidBhn).val(KD_BHN);
			$("#NA_BHN"+rowidBhn).val(NA_BHN);		
			$("#SATUAN"+rowidBhn).val(SATUAN);		
			$("#browseBhnModal").modal("hide");
		}
		
		$("#KD_BHN0").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseBhn(0);
			}
		}); 
	});
////////////////////////////////////////////////////////////////////////////////////
	function getFod(bukti)
	{
		$.ajax(
			{
				type: 'GET',  
				url: "{{url('orderk/browseFodPrs')}}",
				data: {
					NO_FO : $("#NO_FO").val(),
					KD_PRSH : $("#KD_PRSH").val(),
				},
				success: function( resp )
				{
					var html = '';
					for(i=0; i<resp.length; i++){
						html+=`<tr>
                                    <td><input name='NO_ID[]' id='NO_ID${i}' type='hidden' class='form-control NO_ID' value='new' readonly> <input name='REC[]' id=REC${i} value=${resp[i].REC+1} type='text' class='REC form-control' onkeypress='return tabE(this,event)' style='text-align: center;' readonly></td>
                                    <td><input name='KD_BHN[]' data-rowid=${i} id=KD_BHN${i} value="${resp[i].KD_BHN}" type='text' class='form-control KD_BHN' required readonly></td>
                                    <td><input name='NA_BHN[]' data-rowid=${i} id=NA_BHN${i} value="${resp[i].NA_BHN}" type='text' class='form-control  NA_BHN' required readonly></td>
                                    <td><input name='SATUAN[]' data-rowid=${i} id=SATUAN${i} value="${resp[i].SATUAN}" type='text' class='form-control  SATUAN' placeholder="Satuan" required readonly></td>
                                    <td><input name='QTY[]' onclick='select()' onkeyup='hitung()' id=QTY${i} value="${resp[i].QTY}" type='text' style='text-align: right' class='form-control QTY text-primary' required></td>
                                    <td><input name='QTYX[]' onclick='select()' onkeyup='hitung()' id=QTYX${i} value="0" type='text' style='text-align: right' class='form-control QTYX text-primary' required> </td>
                                    <td><input name='KET[]' id=KET${i} value="${resp[i].KET}" type='text' class='form-control  KET' required></td>
                                    <td><button type='button' class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button></td>
                                </tr>`;
					}
					$('#detailPakai').html(html);
					$(".QTY").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".QTY").autoNumeric('update');
					
					$(".QTYX").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".QTYX").autoNumeric('update');
					
					idrow=resp.length;
					baris=resp.length;

					nomor();
				}
			});
	}


///////////////////////////////////////////////////////////////////////////////////////////////


	function simpan() {

		hitung();
		
		var tgl = $('#TGL').val();
		var bulanPer = {{session()->get('periode')['bulan']}};
		var tahunPer = {{session()->get('periode')['tahun']}};
		
        var check = '0';

			$(".KD_PRS").each(function() {
				var kdprs = $(this).val();
				if(kdprs=='')
				{
					var val = $(this).parents("tr").remove();
					baris--;
					nomor();
				}
			});

			if ( $('#NO_OK').val()=='' ) 
			{			
				check = '1';
				alert("Order Kerja Harus diisi.");
			}

			if ( $('#NO_SO').val()=='' ) 
			{			
				check = '1';
				alert("SO Harus diisi.");
			}
			
			if ( $('#NO_FO').val()=='' ) 
			{			
				check = '1';
				alert("Formula Harus diisi.");
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

			if ( $('#KD_BRG').val()=='' ) 
            {				
			    check = '1';
				alert("Bahan# Harus Diisi.");
			}

        
			if ( $('#KODEC').val()=='' ) 
            {				
			    check = '1';
				alert("Customer# Harus Diisi.");
			}

			// if ( check == '0' )
			// {
			// 	cekOrderkWIP(function(data) {
			// 		if(data)
			// 		{
			// 			alert("Masih ada order kerja WIP yang belum diproses!");
			// 		}
			// 		else
			// 		{
			// 			document.getElementById("entri").submit();  
			// 		}
			// 	}); 
			// }
			
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
		var QTYI = parseFloat($("#QTYI").val().replace(/,/g, ''));



		$(".QTY").each(function() {
			
			let z = $(this).closest('tr');
			var QTYX = parseFloat(z.find('.QTYX').val().replace(/,/g, ''));
			var QTY = parseFloat(z.find('.QTY').val().replace(/,/g, ''));

            // var QTY = QTYI * QTYX;
			// z.find('.QTY').val(QTY);
		    // z.find('.QTY').autoNumeric('update');
		
            TTOTAL_QTY +=QTY;	
		});
		
		if(isNaN(TTOTAL_QTY)) TTOTAL_QTY = 0;
	
		$('#TTOTAL_QTY').val(numberWithCommas(TTOTAL_QTY));		
		$("#TTOTAL_QTY").autoNumeric('update');

		
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
			$("#SATUAN" + i.toString()).attr("readonly", true);
			$("#QTY" + i.toString()).attr("readonly", true);
			$("#QTYX" + i.toString()).attr("readonly", false);
			$("#HARGA" + i.toString()).attr("readonly", true);
			$("#TOTAL" + i.toString()).attr("readonly", false);
			$("#DPP" + i.toString()).attr("readonly", true);
			$("#PPN" + i.toString()).attr("readonly", true);
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
			$("#DPP" + i.toString()).attr("readonly", true);
			$("#PPNX" + i.toString()).attr("readonly", true);
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
			window.location ="{{url('/pakai/delete/'.$header->NO_ID .'/?flagz='.$flagz.'&golz=' .$golz.'' )}}";
			//return true;
		} 
		return false;
	}
	

	function CariBukti() {
		
		var flagz = "{{ $flagz }}";
		var cari = $("#CARI").val();
		var loc = "{{ url('/pakai/edit/') }}" + '?idx={{ $header->NO_ID}}&tipx=search&flagz=' + encodeURIComponent(flagz) + '&golz=' + encodeURIComponent(golz) + '&buktix=' +encodeURIComponent(cari);
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
				    <input name='KD_BRG[]' data-rowid=${idrow} onblur='browseBarang(${idrow})' id='KD_BRG${idrow}' type='text' class='form-control  KD_BRG' >
                </td>
                <td>
				    <input name='NA_BRG[]'   id='NA_BRG${idrow}' type='text' class='form-control  NA_BRG' required readonly>
                </td>

                <td>
				    <input name='SATUAN[]'   id='SATUAN${idrow}' type='text' class='form-control  SATUAN' readonly required>
                </td>
				
				<td>
		            <input name='QTY[]'  onblur='hitung()' value='0' id='QTY${idrow}' type='text' style='text-align: right' class='form-control QTY text-primary' required >
                </td>
				
				<td>
		            <input name='QTYX[]'  onblur='hitung()' value='0' id='QTYX${idrow}' type='text' style='text-align: right' class='form-control QTYX text-primary' required >
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
			$("#QTYX" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			
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