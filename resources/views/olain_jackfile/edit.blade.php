@extends('layouts.plain')
@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection
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
               <h1 class="m-0">Entry Syarat Betiz</h1>	
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

                    <form action="{{($tipx=='new')? url('/jackfile/store/') : url('/jackfile/update/'.$header->NO_ID ) }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
        
                        <div class="tab-content mt-3">

							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="J_MIN" class="form-label">Jackpot [Min]</label>
								</div>

									<input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
										placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

									<input name="tipx" class="form-control flagz" id="tipx" value="{{$tipx}}" hidden>
									
								<div class="col-md-2">
									<input type="text" class="form-control J_MIN" id="J_MIN" name="J_MIN"
									placeholder="Masukkan Kode" style="text-align: right" value="{{$header->J_MIN}}">
								</div>

								<div class="col-md-1" align="right">
									<label for="J_BELANJA" class="form-label">Minimum Purchase</label>
								</div>
								<div class="col-md-2">
									<input type="text" class="form-control J_BELANJA" id="J_BELANJA" name="J_BELANJA"
									placeholder="Masukkan Nama" style="text-align: right" value="{{$header->J_BELANJA}}">
								</div>
							</div>
		
							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="J_JACK" class="form-label">Jackpot [Max]</label>
								</div>
								<div class="col-md-2">
									<input type="text" class="form-control J_JACK" id="J_JACK" name="J_JACK"
									placeholder="Masukkan Nama" style="text-align: right" value="{{$header->J_JACK}}">
								</div>

								<div class="col-md-1" align="right">
									<label for="J_PERSEN" class="form-label">Amount Saving [%]</label>
								</div>
								<div class="col-md-2">
									<input type="text" class="form-control J_PERSEN" id="J_PERSEN" name="J_PERSEN"
									placeholder="Masukkan Nama" style="text-align: right" value="{{$header->J_PERSEN}}">
								</div>
							</div>
		
							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="J_UTAMA" class="form-label">Grand Price</label>
								</div>
								<div class="col-md-2">
									<input type="text" class="form-control J_UTAMA" id="J_UTAMA" name="J_UTAMA" placeholder="Masukkan No. Undian" style="text-align: right" value="{{$header->J_UTAMA}}" >
								</div>

								<div class="col-md-1" align="right">
									<label for="J_JAM" class="form-label">Jam Periode</label>
								</div>
								<div class="col-md-2">
									<input class="form-control time" id="J_JAM" name="J_JAM"
										type="text" autocomplete="off"
										value="{{ $tipx == 'new' ? date('H:i') : ($header->J_JAM ? date('H:i', strtotime($header->J_JAM)) : '00:00') }}">
								</div>

								<div class="col-md-1" align="right">
									<label for="JAM_AK" class="form-label">s/d</label>
								</div>
								<div class="col-md-2">
									<input class="form-control time" id="JAM_AK" name="JAM_AK"
										type="text" autocomplete="off"
										value="{{ $tipx == 'new' ? date('H:i') : ($header->JAM_AK ? date('H:i', strtotime($header->JAM_AK)) : '00:00') }}">
								</div>

							</div>

							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="J_SATU" class="form-label">First Price</label>
								</div>
								<div class="col-md-2">
									<input type="text" class="form-control J_SATU" id="J_SATU" name="J_SATU" placeholder="Masukkan Nilai" style="text-align: right" value="{{$header->J_SATU}}" >
								</div>

								<div class="col-md-1" align="right">
                                    <label for="TGL_AW" class="form-label">Periode Tanggal</label>
                                </div>
                                <div class="col-md-2">
								  <input class="form-control date" id="TGL_AW" name="TGL_AW" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL_AW))}}">
                                </div>

								<div class="col-md-1" align="right">
                                    <label for="TGL_AK" class="form-label">s/d</label>
                                </div>
                                <div class="col-md-2">
								  <input class="form-control date" id="TGL_AK" name="TGL_AK" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL_AK))}}">
                                </div>
							</div>

							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="J_DUA" class="form-label">Second Price</label>
								</div>
								<div class="col-md-2">
									<input type="text" class="form-control J_DUA" id="J_DUA" name="J_DUA" placeholder="Masukkan Nilai" style="text-align: right" value="{{$header->J_DUA}}" >
								</div>

								<div class="col-md-1" align="right">
									<label for="J_PILIH" class="form-label">Cross Check Digits</label>
								</div>
								<div class="col-md-2">
									<input type="text" class="form-control J_PILIH" id="J_PILIH" name="J_PILIH" placeholder="Masukkan Nilai" value="{{$header->J_PILIH}}" >
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="J_HIBUR" class="form-label">Third Price</label>
								</div>
								<div class="col-md-2">
									<input type="text" class="form-control J_HIBUR" id="J_HIBUR" name="J_HIBUR" placeholder="Masukkan Nilai" style="text-align: right" value="{{$header->J_HIBUR}}" >
								</div>

								<div class="col-md-1" align="right">
									<label for="JUMJACK" class="form-label">Amount</label>
								</div>
								<div class="col-md-2">
									<input type="text" class="form-control JUMJACK" id="JUMJACK" name="JUMJACK" placeholder="Masukkan Nilai" style="text-align: right" value="{{$header->JUMJACK}}" >
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="JARAK" class="form-label">Delay Waktu</label>
								</div>
								<div class="col-md-2">
									<input type="text" class="form-control JARAK" id="JARAK" name="JARAK" placeholder="Masukkan Nilai" value="{{$header->JARAK}}" >
								</div>
								<div class="col-md-1" align="right">
									<label for="STTS" class="form-label">Status</label>
								</div>
								<div class="col-md-1"></div>
								<div class="col-md-2">
									<input type="checkbox" class="form-check-input" id="aktv" name="aktv" value="1" {{ ($header->aktv == 1) ? 'checked' : '' }}>
									<label class="form-check-label" for="aktv">AKTIF</label>
								</div>
							</div>
						</div>
        
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button type="button" hidden id='TOPX'  onclick="location.href='{{url('/jackfile/edit/?idx=' .$idx. '&tipx=top')}}'" class="btn btn-outline-primary">Top</button>
								<button type="button" hidden id='PREVX' onclick="location.href='{{url('/jackfile/edit/?idx='.$header->NO_ID.'&tipx=prev&kodex='.$header->kodeh )}}'" class="btn btn-outline-primary">Prev</button>
								<button type="button" hidden id='NEXTX' onclick="location.href='{{url('/jackfile/edit/?idx='.$header->NO_ID.'&tipx=next&kodex='.$header->kodeh )}}'" class="btn btn-outline-primary">Next</button>
								<button type="button" hidden id='BOTTOMX' onclick="location.href='{{url('/jackfile/edit/?idx=' .$idx. '&tipx=bottom')}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button type="button" hidden id='NEWX' onclick="location.href='{{url('/jackfile/edit/?idx=0&tipx=new')}}'" class="btn btn-warning">New</button>
								<button type="button" hidden id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button type="button" hidden id='UNDOX' onclick="location.href='{{url('/jackfile/edit/?idx=' .$idx. '&tipx=undo' )}}'" class="btn btn-info">Undo</button> 
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button type="button" hidden id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								<button type="button" id='CLOSEX'  onclick="location.href='{{url('/jackfile' )}}'" class="btn btn-outline-secondary">Close</button>


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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
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

		$("#J_MIN").autoNumeric('init', {mDec: 0, aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#J_JACK").autoNumeric('init', {mDec: 0, aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#J_UTAMA").autoNumeric('init', {mDec: 0, aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#J_SATU").autoNumeric('init', {mDec: 0, aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#J_DUA").autoNumeric('init', {mDec: 0, aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#J_HIBUR").autoNumeric('init', {mDec: 0, aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#JARAK").autoNumeric('init', {mDec: 0, aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});

		$("#J_BELANJA").autoNumeric('init', {mDec: 0, aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#J_PERSEN").autoNumeric('init', {mDec: 2, aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#JUMJACK").autoNumeric('init', {mDec: 0, aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});

		$(".date").datepicker({
			'dateFormat': 'dd-mm-yy',
			// 'minDate' : 0,
		});
		
		flatpickr(".time", {
			enableTime: true,
			noCalendar: true,
			dateFormat: "H:i",
			time_24hr: true,
			secondIncrement: 1,
			allowInput: true
		});
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
			window.location ="{{url('/jackfile/delete/'.$header->NO_ID )}}'";
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

