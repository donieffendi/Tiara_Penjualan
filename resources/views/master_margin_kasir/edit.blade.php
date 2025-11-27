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
               <h1 class="m-0">Margin Kasir</h1>	
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

                    <form action="{{($tipx=='new')? url('/margin-ksr/store/') : url('/margin-ksr/update/'.$header->NO_ID ) }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
						
                        <div class="tab-content mt-3">
							
								<div class="form-group row">
									<div class="col-md-1">
										<label for="JNS" class="form-label">Kode</label>
									</div>

										<input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
										placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

										<input name="tipx" class="form-control flagz" id="tipx" value="{{$tipx}}" hidden>
											
									
									<div class="col-md-2">
										<input type="text" class="form-control JNS" id="JNS" name="JNS"
										placeholder="Masukkan Kode" value="{{$header->JNS}}">
									</div>                                
								</div>

								<div class="form-group row">
									<div class="col-md-1">
										<label for="NAMA" class="form-label">Nama</label>
									</div>
									<div class="col-md-2">
										<input type="text" class="form-control NAMA" id="NAMA" name="NAMA"
										placeholder="Masukkan Nama" value="{{$header->NAMA}}">
									</div>                                    
								</div>

								<div class="form-group row">
									<div class="col-md-1">
										<label for="MARGIN" class="form-label">Margin</label>
									</div>
									<div class="col-md-2">
										<input type="text" class="form-control MARGIN" id="MARGIN" name="MARGIN"
										placeholder="Masukkan Margin" value="{{$header->MARGIN}}">
									</div>                                              
								</div>

						</div>
        
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button type="button" hidden id='TOPX'  onclick="location.href='{{url('/margin-ksr/edit/?idx=' .$idx. '&tipx=top')}}'" class="btn btn-outline-primary">Top</button>
								<button type="button" hidden id='PREVX' onclick="location.href='{{url('/margin-ksr/edit/?idx='.$header->NO_ID.'&tipx=prev&kodex='.$header->JNS )}}'" class="btn btn-outline-primary">Prev</button>
								<button type="button" hidden id='NEXTX' onclick="location.href='{{url('/margin-ksr/edit/?idx='.$header->NO_ID.'&tipx=next&kodex='.$header->JNS )}}'" class="btn btn-outline-primary">Next</button>
								<button type="button" hidden id='BOTTOMX' onclick="location.href='{{url('/margin-ksr/edit/?idx=' .$idx. '&tipx=bottom')}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button type="button" hidden id='NEWX' onclick="location.href='{{url('/margin-ksr/edit/?idx=0&tipx=new')}}'" class="btn btn-warning">New</button>
								<button type="button" hidden id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button type="button" hidden id='UNDOX' onclick="location.href='{{url('/margin-ksr/edit/?idx=' .$idx. '&tipx=undo' )}}'" class="btn btn-info">Undo</button> 
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button type="button" hidden id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								<button type="button" id='CLOSEX'  onclick="location.href='{{url('/margin-ksr' )}}'" class="btn btn-outline-secondary">Close</button>


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
<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
<!-- tambahan untuk sweetalert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- tutupannya -->
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
					document.getElementById("JNS").focus();
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
	
		$("#MARGIN").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-9999999999999.99'});
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
		  	
			$("#JNS").attr("readonly", false);	

		   }
		else
		{
	     	$("#JNS").attr("readonly", true);	

		   }
		   
		$("#NAMA").attr("readonly", false);	
		$("#MARGIN").attr("readonly", false);	
	
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
		
		$("#JNS").attr("readonly", true);			
		$("#NAMA").attr("readonly", true);
		$("#MARGIN").attr("readonly", true);
	}


	function kosong() {
				
		 $('#JNS').val("");
		 $('#NAMA').val("");	
		 $('#MARGIN').val("0.00");		

	}
	
	function hapusTrans() {
		let text = "Hapus Master "+$('#JNS').val()+"?";
		if (confirm(text) == true) 
		{
			window.location ="{{url('/margin-ksr/delete/'.$header->NO_ID )}}'";
			//return true;
		} 
		return false;
	}

	// function simpan() {
    //     hasilCek=0;
	// 	$tipx = $('#tipx').val();

    //     (hasilCek==0) ? document.getElementById("entri").submit() : alert('Masih ada kesalahan');
	// }

	function simpan() {

        var check = '0';

			if ( $('#JNS').val()=='' ) 
			{				
				hasilCek = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Kode Harus Diisi.'
				});
				return; // Stop function execution
			}

			if ( $('#NAMA').val()=='' ) 
			{				
				hasilCek = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Nama# Harus Diisi.'
				});
				return; // Stop function execution
			}

			if ( $('#MARGIN').val()=='' ) 
			{				
				hasilCek = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Margin Harus Diisi.'
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

