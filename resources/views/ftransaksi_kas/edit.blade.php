@extends('layouts.plain')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .card {

    }

    .form-control:focus {
        background-color: #E0FFFF !important;
    }
	
	
	.select2-container .option {
		display: flex;
		justify-content: space-between;
	}

	.select2-container .col1 {
		font-weight: bold;
	}

	.select2-container .col2 {
		color: grey;
	}
	
	.select2-drop-active {
		margin-top: -25px;
	}
	
		
	.select2-results { 
	   background-color: #E0FFFF; 
	}
	
	
	.NACNO_KET {
        background-color: #FFFACD !important;
		
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
	
</style>

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
        <div class="row mb-2">
            <!-- /.col -->
        </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <div class="content">
        <div class="container-fluid">
        <div class="row">
            <div class="col-12">
            <div class="card">
                <div class="card-body">

																	
                    <form action="{{($tipx=='new')? url('/kas/store?flagz='.$flagz.'') : url('/kas/update/'.$header->NO_ID.'&flagz='.$flagz.'' ) }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
        
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
        
                            <div class="form-group row">
                                <div class="col-md-1">
                                    <label for="NO_BUKTI" class="form-label">Bukti#.</label>
                                </div>
								
				
                                <input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
                                    value="{{$header->NO_ID ?? ''}}" hidden readonly>
								<input name="tipx" class="form-control tipx" id="tipx" value="{{$tipx}}" hidden >
								<input name="flagz" class="form-control flagz" id="flagz" value="{{$flagz}}" hidden >
								<input name="searchx" class="form-control searchx" id="searchx" value="{{$searchx ?? ''}}" hidden >
									
                                <div class="col-md-2">

                                    <input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI"
                                    placeholder="Masukkan Bukti#" value="{{$header->NO_BUKTI ?? ''}}" >
 	
								</div>

                                <div class="col-md-1">
                                    <label for="TGL" class="form-label">Tgl</label>
                                </div>
                                
								<div class="col-md-2">
 
								  <input class="form-control date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}" >
								
								</div>		
								
								
								<div class="col-md-6"></div>
					
								<div class="col-md-3 input-group">

									<input type="text" hidden class="form-control CARI" id="CARI" name="CARI"
                                    placeholder="Cari Bukti#" value="" >
									<button type="button" hidden id='SEARCHX'  onclick="CariBukti()" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>

								</div> 
 	
	
							</div>
							
		
                            <div class="form-group row">
                                <div class="col-md-1">	
                                    <label for="BACNO" class="form-label">Cash#</label>
                                </div>
								
                                <div class="col-md-3" >
                                   <select id="BACNO"  name="BACNO" style="width: 100%" ></select>        							      
                                </div>
		
							</div>	
        
        
							<div class="form-group row">
                                <!-- <div class="col-md-1">
                                    <label for="KET" class="form-label">Ket</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control KET" id="KET" name="KET" value="{{$header->KET ?? ''}}"  placeholder="Masukkan Keterangan">
                                </div>
                            </div> -->

							<!-- code text box baru -->
								<div class="col-md-5 form-group row special-input-label">

									<input type="text" class="KET" id="KET" name="KET" 
										value="{{$header->KET}}" placeholder=" " >
									<label for="KET">Ket</label>
								</div>
								<!-- tutupannya -->
                     
							<!-- loader tampil di modal  -->
							<div class="loader" style="z-index: 1055;" id='LOADX' ></div>
                            
                            <table id="datatable" class="table table-striped table-border">
                                <thead>
                                    <tr>
                                        <th width="100px">No.</th>
                                        <th width="200px">
											<label style="color:red;font-size:20px">* </label>									
                                            <label for="BACNO" class="form-label">Account</label></th>
                                        <!-- <th width="200px">-</th> -->
										<th width="200px">
                                            <!-- <label style="color:red;font-size:20px">* </label> -->
                                            <label for="NACNO" class="form-label">Nama Account</label>
                                        </th>
                                        <th width="200px">
											<label style="color:red;font-size:20px">* </label>									
                                            <label for="NO_HUT" class="form-label">No Faktur</label>
										</th>
                                        <th width="600px">Uraian</th>
                                        <th width="200px">Jumlah</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
								<?php $no=0 ?>
								@foreach ($detail as $detail)
                                    <tr>
                                         <td>
                                            <input type="hidden" name="NO_ID[]" id="NO_ID{{$no}}" type="text" value="{{$detail->NO_ID}}" 
                                            class="form-control NO_ID" onkeypress="return tabE(this,event)" readonly>
                                            
                                            <input name="REC[]" id="REC{{$no}}" type="text" value="{{$detail->REC}}" 
                                            class="form-control REC" onkeypress="return tabE(this,event)" readonly>
                                        </td>

                                        <td>
                                            <input name="ACNO[]" id="ACNO{{$no}}" onclick="browseAccount({{$no}})" type="text" value="{{$detail->ACNO}}"
                                              class="form-control ACNO "  readonly onclick="getNacno(this.id)">
										</td>
			
										<td>
											<input name="NACNO[]" id="NACNO{{$no}}" type="text" value="{{$detail->NACNO}}"
											class="form-control NACNO" readonly >
										</td>

										<td>
											<input name="NO_HUT[]" id="NO_HUT{{$no}}" type="text" class="form-control NO_HUT " 
											value="{{$detail->NO_FAKTUR}}" onblur="browseHut({{$no}})">
										</td>
									
										<td>
                                            <input name="URAIAN[]" id="URAIAN{{$no}}" type="text" value="{{$detail->URAIAN}}"
                                            class="form-control URAIAN" required>
                                        </td>
										<td>
                                            <input name="JUMLAH[]"  onblur="hitung()" style="text-align: right" id="JUMLAH{{$no}}" type="text" value="{{$detail->JUMLAH}}"
                                            class="form-control JUMLAH">
                                        </td>
                                        
										<td>
                                            <button type="button" id="DELETEX{{$no}}" class="btn btn-sm btn-circle btn-outline-danger btn-delete" onclick="">
                                                <i class="fa fa-fw fa-trash"></i>
                                            </button>
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
									<td><input class="form-control TJUMLAH  text-primary font-weight-bold"
                                         style="text-align: right" id="TJUMLAH" name="TJUMLAH"
                                         value="{{$header->JUMLAH ?? ''}}" readonly></td>
									<td></td>
                                </tfoot>
                            </table>     
                            <div class="col-md-2 row">
                               <a type="button" id='PLUSX' onclick="tambah()" class="fas fa-plus fa-sm md-3" ></a>					
							</div>			
                            
                           
                        </div>


						        
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button hidden type="button" id='TOPX'  onclick="location.href='{{url('/kas/edit/?idx=' .$idx. '&tipx=top&flagz='.$flagz.'' )}}'" class="btn btn-outline-primary">Top</button>
								<button hidden type="button" id='PREVX' onclick="location.href='{{url('/kas/edit/?idx='.$header->NO_ID.'&tipx=prev&flagz='.$flagz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Prev</button>
								<button hidden type="button" id='NEXTX' onclick="location.href='{{url('/kas/edit/?idx='.$header->NO_ID.'&tipx=next&flagz='.$flagz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Next</button>
								<button hidden type="button" id='BOTTOMX' onclick="location.href='{{url('/kas/edit/?idx=' .$idx. '&tipx=bottom&flagz='.$flagz.'' )}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button hidden type="button" id='NEWX' onclick="location.href='{{url('/kas/edit/?idx=0&tipx=new&flagz='.$flagz.'' )}}'" class="btn btn-warning">New</button>
								<button hidden type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button hidden type="button" id='UNDOX' onclick="location.href='{{url('/kas/edit/?idx=' .$idx. '&tipx=undo&flagz='.$flagz.'' )}}'" class="btn btn-info">Undo</button>  
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button hidden type="button" id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								
								<!-- <button type="button" id='CLOSEX'  onclick="location.href='{{url('/kas?flagz='.$flagz.'' )}}'" class="btn btn-outline-secondary">Close</button> -->
								
								<!-- tombol close sweet alert -->
								<button type="button" id='CLOSEX' onclick="closeTrans()" class="btn btn-outline-secondary">Close</button></div>
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
	
	

	
	<div class="modal fade" id="browseAccountModal" tabindex="-1" role="dialog" aria-labelledby="browseAccountModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseAccountModalLabel">Cari Account</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-baccount">
				<thead>
					<tr>
						<th>Account#</th>
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


	<div class="modal fade" id="browseAccount1Modal" tabindex="-1" role="dialog" aria-labelledby="browseAccount1ModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseAccount1ModalLabel">Cari Account</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-baccount1">
				<thead>
					<tr>
						<th>Account#</th>
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

	<div class="modal fade" id="browseHutModal" tabindex="-1" role="dialog" aria-labelledby="browseHutModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseHutModalLabel">Cari Item</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bhut">
				<thead>
					<tr>
						<th>No Bukti</th>
						<th>Tgl</th>
						<th>Bayar</th>
						
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

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
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

// TAMBAH HITUNG
	$(document).ready(function() {

		setTimeout(function(){

		$("#LOADX").hide();

		},500);

	<?php $searchx = '' ?>
    idrow=<?=$no?>;
    baris=<?=$no?>;



    $('#BACNO').select2({
		
		placeholder:'Pilih Cash Account',
		allowClear: true,
        ajax: {
			url: '{{url('account/browsecash')}}',
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
                        id: item.ACNO, // The ID of the user
                        text: item.NAMA // The text to display
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
					tambah();
					// var nomer = idrow-1;
					// console.log("REC"+nomor);
					// document.getElementById("REC"+nomor).focus();
					// form.submit();
				}
				return false;
			}
		});

		$("#TJUMLAH").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});

		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#JUMLAH" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
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
			 
			    var initkode ="{{ $header->BACNO }}";	
			    var initcombo ="{{ $header->BNAMA }}";
				var defaultOption = { id: initkode, text: initcombo }; // Set your default option ID and text
                var newOption = new Option(defaultOption.text, defaultOption.id, true, true);
                $('#BACNO').append(newOption).trigger('change');
			 
			 
		}    
		
	
		
		
///////////////////////////////////////////////////////////////////////
 
		$(".ACNO").each(function() {
			var getid = $(this).attr('id');
			var noid = getid.substring(4,11);
            
			
			
			$("#ACNO"+noid).keypress(function(e){
				if(e.keyCode == 46){
					e.preventDefault();
					browseAccount(noid);
				}
			}); 
		}); 

//////////////////////////////////////////////////////

		var dTableBHut;
		var rowidHut;
		loadDataBHut = function(){
		
			$.ajax(
			{
				type: 'GET',    
				url: "{{url('hut/browse')}}",
				async : false,
				data: {
						'NO_HUT': $("#NO_HUT"+rowidHut).val(),
					
				},
				success: function( response )

				{
					resp = response;
					
					
					if ( resp.length > 1 )
					{	
							if(dTableBHut){
								dTableBHut.clear();
							}
							for(i=0; i<resp.length; i++){
								
								dTableBHut.row.add([
									'<a href="javascript:void(0);" onclick="chooseHut(\''+resp[i].NO_HUT+'\')">'+resp[i].NO_HUT+'</a>',
									resp[i].TGL,
									resp[i].BAYAR,
								]);
							}
							dTableBHut.draw();
					
					}
					else
					{
						$("#NO_HUT"+rowidHut).val(resp[0].NO_HUT);
					}
				}
			});
		}
		
		dTableBHut = $("#table-bhut").DataTable({
			
		});

		browseHut = function(rid){
			rowidHut = rid;
			$("#TGL"+rowidHut).val("");			
			loadDataBHut();
	
			
			if ( $("#TGL"+rowidHut).val() == '' ) {				
					$("#browseHutModal").modal("show");
			}	
		}
		
		chooseHut = function(NO_HUT){
			$("#NO_HUT"+rowidHut).val(NO_HUT);
			$("#browseHutModal").modal("hide");
		}
		
	////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////		
		
		
		//CHOOSE Bacno
 		var dTableBAccount1;
		loadDataBAccount1 = function(){
			$.ajax(
			{
				type: 'GET',    
				url: '{{url('account/browsecash')}}',
				success: function( response )
				{
					resp = response;
					if(dTableBAccount1){
						dTableBAccount1.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBAccount1.row.add([
							'<a href="javascript:void(0);" onclick="chooseAccount1(\''+resp[i].ACNO+'\',\''+resp[i].NAMA+'\')">'+resp[i].ACNO+'</a>',
							resp[i].NAMA,
						]);
					}
					dTableBAccount1.draw();
				}
			});
		}
		
		dTableBAccount1 = $("#table-baccount1").DataTable({
			
		});
		
		browseAccount1 = function(){
			loadDataBAccount1();
			$("#browseAccount1Modal").modal("show");
		}
		
		chooseAccount1 = function(ACNO,NAMA){
			$("#BACNO").val(ACNO);
			$("#BNAMA").val(NAMA);
			$("#browseAccount1Modal").modal("hide");
		}
		
		$("#BACNO").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseAccount1();
			}
		}); 
		
//////////////////////////////////////////////////////////////////////////////////////////////////
		
//////////////////////////////////////////////////////////////////////
		
 		var dTableBAccount;
		var rowidAccount;
		loadDataBAccount = function(){
			$.ajax(
			{
				type: 'GET',    
				url: "{{url('account/browse')}}",
				success: function( response )
				{
					resp = response;
					if(dTableBAccount){
						dTableBAccount.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBAccount.row.add([
							'<a href="javascript:void(0);" onclick="chooseAccount(\''+resp[i].ACNO+'\',\''+resp[i].NAMA+'\')">'+resp[i].ACNO+'</a>',
							resp[i].NAMA,
						]);
					}
					dTableBAccount.draw();
				}
			});
		}
		
		dTableBAccount = $("#table-baccount").DataTable({
			
		});
		
		browseAccount = function(rid){
			rowidAccount = rid;
			loadDataBAccount();
			$("#browseAccountModal").modal("show");
		}
		
		chooseAccount = function(ACNO,NAMA){
			$("#ACNO"+rowidAccount).val(ACNO);
			$("#NACNO"+rowidAccount).val(NAMA);
            $("#NACNO_KET").val(NAMA);			
			$("#browseAccountModal").modal("hide");
		}
		
		
		$("#ACNO0").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseAccount(0);
			}
		}); 
		
		
	
		
	});



//////////////////////////////////////////////////////////////////

	function cekDetail(){
		var cekAcno = '';
		$(".ACNO").each(function() {
			
			let z = $(this).closest('tr');
			var ACNOX = z.find('.ACNO').val();
			
			if( ACNOX =="" )
			{
					cekAcno = '1';
					
			}	
		});
		
		return cekAcno;
	}



///////////////////////////////////////
            function getNacno(id) {
				var urut = id.substring(4, 9);
				$('#NACNO_KET').val($('#NACNO'+urut).val());
			}
			

  			function simpan() {

                hitung();

                var tgl = $('#TGL').val();
                var bulanPer = {{ session()->get('periode')['bulan'] }};
                var tahunPer = {{ session()->get('periode')['tahun'] }};

                var check = '0';

				// sweet alert saat save 

				if (cekDetail()) {
                    check = '1';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Ada Akun# Kosong Didetail.'
                    });
                    return; // Stop function execution
                }

                if ($('#BACNO').val() == null ) {
                    check = '1';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Kas# Harus diisi.'
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
		$(".REC").each(function() {
			$(this).val(i++);
		});
	//	hitung();
	}

    function hitung() {
		var TJUMLAH = 0;


		$(".JUMLAH").each(function() {
			var val = parseFloat($(this).val().replace(/,/g, ''));
			if(isNaN(val)) val = 0;
			TJUMLAH+=val;
		});
		
		if(isNaN(TJUMLAH)) TJUMLAH = 0;

		$('#TJUMLAH').val(numberWithCommas(TJUMLAH));
		$("#TJUMLAH").autoNumeric('update');

	}
	
		$(".ACNO").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseAccount(eval($(this).data("rowid")));
			}
		}); 




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
		   
			$("#NO_BUKTI").attr("readonly", true);		   
			$("#TGL").attr("readonly", false);
			$("#BACNO").attr("readonly", true);
    		$("#BACNO").attr("disabled", false);
			$("#BNAMA").attr("readonly", true);
			$("#KET").attr("readonly", false);
		
	

		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#ACNO" + i.toString()).attr("readonly", true);
			$("#NACNO" + i.toString()).attr("readonly", true);
			$("#URAIAN" + i.toString()).attr("readonly", false);
			$("#JUMLAH" + i.toString()).attr("readonly", false);
			$("#DELETEX" + i.toString()).attr("hidden", false);
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
		$("#BACNO").attr("readonly", true);
		$("#BACNO").attr("disabled", true);
		$("#BNAMA").attr("readonly", true);
		$("#KET").attr("readonly", true);

		
		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#ACNO" + i.toString()).attr("readonly", true);
			$("#NACNO" + i.toString()).attr("readonly", true);
			$("#URAIAN" + i.toString()).attr("readonly", true);
			$("#JUMLAH" + i.toString()).attr("readonly", true);
			$("#DELETEX" + i.toString()).attr("hidden", true);
		}


		
	}


	function kosong() {
				
		 $('#NO_BUKTI').val("+");	
	//	 $('#TGL').val("");	
		 $('#BACNO').val("");	
		 $('#BNAMA').val("");	
		 $('#KET').val("");	
		 $('#TJUMLAH').val("0.00");	
		 
		 
		var html = '';
		$('#detailx').html(html);	
		
	}

	// sweetalert untuk tombol hapus dan close
	
	function hapusTrans() {
		let text = "Hapus Transaksi "+$('#NO_BUKTI').val()+"?";

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
					window.location =
						"{{ url('/kas/delete/' . $header->NO_ID . '/?flagz=' . $flagz . '') }}";
				});
			}
		});
	}
	
	function closeTrans() {
		console.log("masuk");

		Swal.fire({
			title: 'Are you sure?',
			text: 'Do you really want to close this page? Unsaved changes will be lost.',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes, close it',
			cancelButtonText: 'No, stay here'
		}).then((result) => {
			if (result.isConfirmed) {
				window.location = "{{ url('/kas?flagz=' . $flagz) }}";
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
		var cari = $("#CARI").val();
		var loc = "{{ url('/kas/edit/') }}" + '?idx={{ $header->NO_ID}}&tipx=search&flagz=' + encodeURIComponent(flagz) + '&buktix=' +encodeURIComponent(cari);
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
				    <input name='ACNO[]' data-rowid=${idrow} onclick='browseAccount(${idrow})' id='ACNO${idrow}' type='text' class='form-control  ACNO' required readonly>
                </td>
                <td>
				    <input name='NACNO[]'   id='NACNO${idrow}' type='text' class='form-control  NACNO' required readonly>
                </td>

				<td>
					<input name='NO_HUT[]' data-rowid=${idrow} onblur='browseHut(${idrow})' id='NO_HUT${idrow}' type='text' class='form-control  NO_HUT' >
				</td>

                <td>
				    <input name='URAIAN[]'   id='URAIAN${idrow}' type='text' class='form-control  URAIAN' required>
                </td>
				
				<td>
		            <input name='JUMLAH[]'  onclick='select()' onblur='hitung()' value='0' id='JUMLAH${idrow}' type='text' style='text-align: right' class='form-control JUMLAH text-primary' required >
                </td>

                <td>
					<button type='button' id='DELETEX${idrow}'  class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
                </td>				
         </tr>`;
				
        x.innerHTML = html;
        var html='';
		
		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#JUMLAH" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
		}
		
		
		$("#ACNO"+idrow).keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseAccount(eval($(this).data("rowid")));
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
</script>
@endsection
