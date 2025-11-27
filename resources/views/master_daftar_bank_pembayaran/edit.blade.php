@extends('layouts.plain')

<style>
    .card {

    }

    .form-control:focus {
        background-color: #E0FFFF !important;
    }
</style>

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
               <h1 class="m-0">Bank Pembayaran</h1>	
            </div>

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

                    <form action="{{($tipx=='new')? url('/bank-byr/store/') : url('/bank-byr/update/'.$header->NO_ID ) }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
        
                        <div class="tab-content mt-3">

								<div class="form-group row">
									<div class="col-md-1">
										<label for="KODE" class="form-label">Kode</label>
									</div>

										<input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
											placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

										<input name="tipx" class="form-control flagz" id="tipx" value="{{$tipx}}" hidden>
										
									<div class="col-md-2">
										<input type="text" class="form-control KODE" id="KODE" name="KODE"
										placeholder="Masukkan Kode" value="{{$header->KODE}}">
									</div>

									<div class="col-md-1">
										<label for="CR_CARD" class="form-label">Credit Card</label>
									</div>
									<div class="col-md-2">
										<input type="text" class="form-control CR_CARD" id="CR_CARD" name="CR_CARD"
										placeholder="" value="{{$header->CR_CARD}}">
									</div>        
									
									<div class="col-md-1">
										<label for="TYPE" class="form-label">Type</label>
									</div>
									<div class="col-md-2">
                                        <select id="TYPE" class="form-control"  name="TYPE">
											<option value="K" {{ ($header->TYPE == 'K') ? 'selected' : '' }}>K</option>
											<option value="D" {{ ($header->TYPE == 'D') ? 'selected' : '' }}>D</option>
											<option value="T" {{ ($header->TYPE == 'T') ? 'selected' : '' }}>T</option>
                                        </select>
                                    </div>	
								</div>
			
								<div class="form-group row">
									<div class="col-md-1">
										<label for="NOBANK" class="form-label">No.Bank</label>
									</div>
									<div class="col-md-4">
										<input type="text" class="form-control NOBANK" id="NOBANK" name="NOBANK"
										placeholder="Masukkan No Bank" value="{{$header->NOBANK}}">
									</div>
								</div>
			
								<div class="form-group row">
									<div class="col-md-1" align="left">
										<label for="NM_BANK" class="form-label">Nama Kartu</label>
									</div>
									<div class="col-md-3">
										<input type="text" class="form-control NM_BANK" id="NM_BANK" name="NM_BANK" placeholder="Masukkan Nama Kartu" value="{{$header->NM_BANK}}" >
									</div>

								</div>

								<div class="form-group row">
									<div class="col-md-1" align="left">
										<label for="BANK" class="form-label">Bank</label>
									</div>
									<div class="col-md-2">
										<input type="text" class="form-control BANK" id="BANK" name="BANK" placeholder="" value="{{$header->BANK}}" >
									</div>
								</div>
	
								<div class="form-group row">
									<div class="col-md-1" align="left">
										<label for="BATAS" class="form-label">Batas Min Pembelian</label>
									</div>
									<div class="col-md-2">
										<input type="text" class="form-control BATAS" id="BATAS" name="BATAS" placeholder="" value="{{$header->BATAS}}" >
									</div>
								</div>

								<div class="form-group row">
									<div class="col-md-1" align="left">
										<label for="BY_CARD" class="form-label">Biaya Admin Kartu</label>
									</div>
									<div class="col-md-2">
										<input type="text" class="form-control BY_CARD" id="BY_CARD" name="BY_CARD" placeholder="" value="{{$header->BY_CARD}}" >
									</div>
								</div>

						</div>
        
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button type="button" hidden id='TOPX'  onclick="location.href='{{url('/bank-byr/edit/?idx=' .$idx. '&tipx=top')}}'" class="btn btn-outline-primary">Top</button>
								<button type="button" hidden id='PREVX' onclick="location.href='{{url('/bank-byr/edit/?idx='.$header->NO_ID.'&tipx=prev&kodex='.$header->KODE )}}'" class="btn btn-outline-primary">Prev</button>
								<button type="button" hidden id='NEXTX' onclick="location.href='{{url('/bank-byr/edit/?idx='.$header->NO_ID.'&tipx=next&kodex='.$header->KODE )}}'" class="btn btn-outline-primary">Next</button>
								<button type="button" hidden id='BOTTOMX' onclick="location.href='{{url('/bank-byr/edit/?idx=' .$idx. '&tipx=bottom')}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button type="button" hidden id='NEWX' onclick="location.href='{{url('/bank-byr/edit/?idx=0&tipx=new')}}'" class="btn btn-warning">New</button>
								<button type="button" hidden id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button type="button" hidden id='UNDOX' onclick="location.href='{{url('/bank-byr/edit/?idx=' .$idx. '&tipx=undo' )}}'" class="btn btn-info">Undo</button> 
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button type="button" hidden id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								<button type="button" id='CLOSEX'  onclick="location.href='{{url('/bank-byr' )}}'" class="btn btn-outline-secondary">Close</button>


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
@endsection

@section('footer-scripts')
<script>
    var target;
	var idrow = 1;

    $(document).ready(function () {

 		$tipx = $('#tipx').val();

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
					document.getElementById("KODE").focus();
					// form.submit();
				}
				return false;
			}
		});
				
        if ( $tipx == 'new' )
		{
			 baru();			
		}

        if ( $tipx != 'new' )
		{
			 //mati();	
    		 ganti();
		}    
	
    });


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
		  	
			$("#KODE").attr("readonly", false);	

		   }
		else
		{
	     	$("#KODE").attr("readonly", false);	

		   }
		   
		$("#NM_BANK").attr("readonly", false);	
		$("#NOBANK").attr("readonly", false);			
		$("#TYPE").attr("readonly", false);		
		$("#BATAS").attr("readonly", false);			
		$("#CR_CARD").attr("readonly", false);	
		$("#BY_CARD").attr("readonly", false);	
		 $('#BANK').attr("readonly", false);	
	
	
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
		
		$("#KODE").attr("readonly", true);			
		$("#NM_BANK").attr("readonly", true);	
		$("#NOBANK").attr("readonly", true);			
		$("#TYPE").attr("readonly", true);		
		$("#BATAS").attr("readonly", true);			
		$("#CR_CARD").attr("readonly", true);	
		$("#BY_CARD").attr("readonly", true);			
		$("#BANK").attr("readonly", true);	
		
	}


	function kosong() {
				
		 $('#KODE').val("");	
		 $('#NM_BANK').val("");	
		 $('#NOBANK').val("");	
		 $('#TYPE').val("");		

		 $('#BATAS').val("0");	
		 $('#CR_CARD').val("");	
		 $('#BY_CARD').val("0");	
		 $('#BANK').val("");	


		 
	}
	
	function hapusTrans() {
		let text = "Hapus Master "+$('#KODE').val()+"?";
		if (confirm(text) == true) 
		{
			window.location ="{{url('/bank-byr/delete/'.$header->NO_ID )}}'";
			//return true;
		} 
		return false;
	}

    
	function simpan() {
        hasilCek=0;
		$tipx = $('#tipx').val();
				
        // if ( $tipx == 'new' )
		// {
		// 	cekSup($('#KODE').val());		
		// }
		

        (hasilCek==0) ? document.getElementById("entri").submit() : alert('Kode '+$('#KODE').val()+' sudah ada!');
	}
</script>
@endsection

