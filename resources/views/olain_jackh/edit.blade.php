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
               <h1 class="m-0">Barang Hadiah Betiz</h1>	
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

                    <form action="{{($tipx=='new')? url('/jackh/store/') : url('/jackh/update/'.$header->NO_ID ) }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
        
                        <div class="tab-content mt-3">

							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="kodeh" class="form-label">Kode</label>
								</div>

									<input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
										placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

									<input name="tipx" class="form-control flagz" id="tipx" value="{{$tipx}}" hidden>
									
								<div class="col-md-2">
									<input type="text" class="form-control kodeh" id="kodeh" name="kodeh"
									placeholder="Masukkan Kode" value="{{$header->kodeh}}">
								</div>

							</div>
		
							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="namah" class="form-label">Nama</label>
								</div>
								<div class="col-md-4">
									<input type="text" class="form-control namah" id="namah" name="namah"
									placeholder="Masukkan Nama" value="{{$header->namah}}">
								</div>
							</div>
		
							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="undian" class="form-label">Undian</label>
								</div>
								<div class="col-md-1">
									<input type="text" class="form-control undian" id="undian" name="undian" placeholder="Masukkan No. Undian" value="{{$header->undian}}" >
								</div>

								<div class="col-md-1" align="right">
									<label for="gol" class="form-label">Golongan</label>
								</div>
								<div class="col-md-1">
									<input type="text" class="form-control gol" id="gol" name="gol" placeholder="Masukkan Golongan" value="{{$header->gol}}" >
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="max" class="form-label">Max</label>
								</div>
								<div class="col-md-1">
									<input type="text" class="form-control max" id="max" name="max" placeholder="Masukkan Nilai" value="{{$header->max}}" >
								</div>

								<div class="col-md-1" align="right">
									<label for="kirim" class="form-label">Kirim</label>
								</div>
								<div class="col-md-1">
									<input type="text" class="form-control kirim" id="kirim" name="kirim" placeholder="Masukkan Nilai" value="{{$header->kirim}}" >
								</div>
							</div>
						</div>
        
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button type="button" hidden id='TOPX'  onclick="location.href='{{url('/jackh/edit/?idx=' .$idx. '&tipx=top')}}'" class="btn btn-outline-primary">Top</button>
								<button type="button" hidden id='PREVX' onclick="location.href='{{url('/jackh/edit/?idx='.$header->NO_ID.'&tipx=prev&kodex='.$header->kodeh )}}'" class="btn btn-outline-primary">Prev</button>
								<button type="button" hidden id='NEXTX' onclick="location.href='{{url('/jackh/edit/?idx='.$header->NO_ID.'&tipx=next&kodex='.$header->kodeh )}}'" class="btn btn-outline-primary">Next</button>
								<button type="button" hidden id='BOTTOMX' onclick="location.href='{{url('/jackh/edit/?idx=' .$idx. '&tipx=bottom')}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button type="button" hidden id='NEWX' onclick="location.href='{{url('/jackh/edit/?idx=0&tipx=new')}}'" class="btn btn-warning">New</button>
								<button type="button" hidden id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button type="button" hidden id='UNDOX' onclick="location.href='{{url('/jackh/edit/?idx=' .$idx. '&tipx=undo' )}}'" class="btn btn-info">Undo</button> 
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button type="button" hidden id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								<button type="button" id='CLOSEX'  onclick="location.href='{{url('/jackh' )}}'" class="btn btn-outline-secondary">Close</button>


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
					document.getElementById("kodeh").focus();
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
		  	
			$("#kodeh").attr("readonly", false);	

		   }
		else
		{
	     	$("#kodeh").attr("readonly", false);	

		   }
		   
		$("#namah").attr("readonly", false);	
		$("#undian").attr("readonly", false);			
		$("#gol").attr("readonly", false);		
		$("#max").attr("readonly", false);			
		$("#kirim").attr("readonly", false);	
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
		
		$("#kodeh").attr("readonly", true);			
		$("#namah").attr("readonly", true);	
		$("#undian").attr("readonly", true);			
		$("#gol").attr("readonly", true);		
		$("#kirim").attr("readonly", true);			
		$("#max").attr("readonly", true);
		
	}


	function kosong() {	

		 $('#gol').val("0");	
		 $('#max').val("0");	
		 $('#kirim').val("0");	


		 
	}
	
	function hapusTrans() {
		let text = "Hapus Master "+$('#kodeh').val()+"?";
		if (confirm(text) == true) 
		{
			window.location ="{{url('/jackh/delete/'.$header->NO_ID )}}'";
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
		

        (hasilCek==0) ? document.getElementById("entri").submit() : alert('Kode '+$('#kodeh').val()+' sudah ada!');
	}
</script>
@endsection

