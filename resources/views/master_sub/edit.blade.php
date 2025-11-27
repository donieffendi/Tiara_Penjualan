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
               <h1 class="m-0">Data Sub</h1>	
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
					
                    <form action="{{($tipx=='new')? url('/sub/store/') : url('/sub/update/'.str_pad($header->SUB, 3, '0', STR_PAD_LEFT))  }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
        
                        <div class="tab-content mt-3">
							
								<div class="form-group row">
									<div class="col-md-1">
										<label for="SUB" class="form-label">SUB</label>
									</div>

										<input name="tipx" class="form-control flagz" id="tipx" value="{{$tipx}}" hidden>
											
									
									<div class="col-md-2">
										<input type="text" class="form-control SUB" id="SUB" name="SUB"
										placeholder="Masukkan Sub" value="{{ str_pad($header->SUB, 3, '0', STR_PAD_LEFT) }}" readonly>
									</div>                                
								</div>

								<div class="form-group row">
									<div class="col-md-1">
										<label for="KELOMPOK" class="form-label">Kelompok</label>
									</div>
									<div class="col-md-4">
										<input type="text" class="form-control KELOMPOK" id="KELOMPOK" name="KELOMPOK"
										placeholder="Masukkan Kelompok" value="{{$header->KELOMPOK}}">
									</div>                                               
								</div>
			
								<div class="form-group row">
									<div class="col-md-1">
										<label for="PERSEN" class="form-label">Persen</label>
									</div>
									<div class="col-md-4">
										<input type="text" class="form-control PERSEN" id="PERSEN" name="PERSEN"
										placeholder="" value="{{$header->PERSEN}}" readonly>
									</div>

									<div class="col-md-2">
										<input type="text" class="form-control PERSEN_HJ" id="PERSEN_HJ "name="PERSEN_HJ"
										placeholder="" value="{{$header->PERSEN_HJ}}">
									</div>
								</div>
			
								<div class="form-group row">
									<div class="col-md-1" align="left">
										<label for="TYPE" class="form-label">Type</label>
									</div>
                                    <div class="col-md-2">
                                        <select id="TYPE" class="form-control"  name="TYPE">
											<option value="PB" {{ ($header->TYPE == 'PB') ? 'selected' : '' }}>PB</option>
											<option value="NF" {{ ($header->TYPE == 'NF') ? 'selected' : '' }}>NF</option>
											<option value="ST" {{ ($header->TYPE == 'ST') ? 'selected' : '' }}>ST</option>
											<option value="FO" {{ ($header->TYPE == 'FO') ? 'selected' : '' }}>FO</option>
											<option value="F2" {{ ($header->TYPE == 'F2') ? 'selected' : '' }}>F2</option>
											<option value="FF" {{ ($header->TYPE == 'FF') ? 'selected' : '' }}>FF</option>
                                        </select>
                                    </div>	

								</div>

						</div>
        
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button type="button" hidden id='TOPX'  onclick="location.href='{{url('/sub/edit/?idx=' .$idx. '&tipx=top')}}'" class="btn btn-outline-primary">Top</button>
								<button type="button" hidden id='PREVX' onclick="location.href='{{url('/sub/edit/?idx='.$header->SUB.'&tipx=prev&kodex='.$header->SUB )}}'" class="btn btn-outline-primary">Prev</button>
								<button type="button" hidden id='NEXTX' onclick="location.href='{{url('/sub/edit/?idx='.$header->SUB.'&tipx=next&kodex='.$header->SUB )}}'" class="btn btn-outline-primary">Next</button>
								<button type="button" hidden id='BOTTOMX' onclick="location.href='{{url('/sub/edit/?idx=' .$idx. '&tipx=bottom')}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button type="button" hidden id='NEWX' onclick="location.href='{{url('/sub/edit/?idx=0&tipx=new')}}'" class="btn btn-warning">New</button>
								<button type="button" hidden id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button type="button" hidden id='UNDOX' onclick="location.href='{{url('/sub/edit/?idx=' .$idx. '&tipx=undo' )}}'" class="btn btn-info">Undo</button> 
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button type="button" hidden id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								<button type="button" id='CLOSEX'  onclick="location.href='{{url('/sub' )}}'" class="btn btn-outline-secondary">Close</button>


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
					document.getElementById("SUB").focus();
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
		  	
			$("#SUB").attr("readonly", false);	

		}
		else
		{
	     	$("#SUB").attr("readonly", true);	

		}
		   
		$("#KELOMPOK").attr("readonly", false);	
		$("#PERSEN").attr("readonly", false);			
		$("#PERSEN_HJ").attr("readonly", false);		
		$("#TYPE").attr("readonly", false);	
	
	
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
		
		$("#SUB").attr("readonly", true);			
		$("#KELOMPOK").attr("readonly", true);	
		$("#PERSEN").attr("readonly", true);			
		$("#PERSEN_HJ").attr("readonly", true);		
		$("#TYPE").attr("readonly", true);	
		
		
	}


	function kosong() {
				
		 $('#SUB').val("");	
		 $('#KELOMPOK').val("");	
		 $('#PERSEN').val("");	
		 $('#PERSEN_HJ').val("");
		 $('#TYPE').val("");	

	}
	
	function hapusTrans() {
		let text = "Hapus Master "+$('#SUB').val()+"?";
		if (confirm(text) == true) 
		{
			window.location ="{{url('/sub/delete/'.$header->SUB )}}'";
			//return true;
		} 
		return false;
	}

     
    var hasilCek;
	function cekSub(sub) {
		$.ajax({
			type: "GET",
			url: "{{url('sub/ceksub')}}",
            async: false,
			data: ({ SUB: sub, }),
			success: function(data) {
                if (data.length > 0) {
                    $.each(data, function(i, item) {
                        hasilCek=data[i].ADA;
                    });
                }
			},
			error: function() {
				alert('Error cekSub occured');
			}
		});
		return hasilCek;
	}
    
	function simpan() {
        hasilCek=0;
		$tipx = $('#tipx').val();
				
        if ( $tipx == 'new' )
		{
			cekSub($('#SUB').val());		
		}
		

        (hasilCek==0) ? document.getElementById("entri").submit() : alert('Sub '+$('#SUB').val()+' sudah ada!');
	}
</script>
@endsection

