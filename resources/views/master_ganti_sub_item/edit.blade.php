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
               <h1 class="m-0">Ganti Sub Item</h1>	
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

                    <form action="{{($tipx=='new')? url('/gsub/store/') : url('/gsub/update/'.$header->NO_ID ) }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
        
                        <div class="tab-content mt-3">	
							
								<div class="form-group row">
									<div class="col-md-1">
										<label for="no_bukti" class="form-label">No. Bukti</label>
									</div>

										<input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
										placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

										<input name="tipx" class="form-control flagz" id="tipx" value="{{$tipx}}" hidden>
											
									
									<div class="col-md-2">
										<input type="text" class="form-control no_bukti" id="no_bukti" name="no_bukti"
										placeholder="Masukkan No Bukti" value="{{$header->no_bukti}}" readonly>
									</div>                         
								</div>

								<div class="form-group row">
									<div class="col-md-1">
										<label for="tgl" class="form-label">Tanggal</label>
									</div>
									<div class="col-md-2">
										<input class="form-control date" id="tgl" name="tgl" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->tgl))}}">
                                	</div>
									<div class="col-md-1">
										<label for="notes" class="form-label">Notes</label>
									</div>
									<div class="col-md-4">
										<input type="text" class="form-control notes" id="notes" name="notes"
										placeholder="Masukkan notes" value="{{$header->notes}}">
									</div>                                                
								</div>
			
			
						</div>

						<!-- ------------------------------------------------------------------------------- -->
                                
                        <hr style="margin-top: 30px; margin-buttom: 30px">
							
                            <div style="overflow-y:scroll; height:300px;" class="col-md-12 scrollable  fixTableHead fixTableFoot" align="right">
                                
                                <table id="datatable" class="table table-striped table-border table-scrollable">
                                    <thead>
                                        <tr>
                                            <th width="35px">No.</th>
                                            <th width="150px">Kode</th>
                                            <th width="500">Nama Barang</th>
                                            <th width="150px">Ukuran</th>
                                            <th width="150">Kemasan</th>
                                            <th width="150px">Kode Baru</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php $no=0 ?>
                                    @foreach ($detail as $detail)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="NO_ID[]{{$no}}" id="NO_ID" type="text" value="{{$detail->NO_ID}}" 
                                                class="form-control NO_ID" onkeypress="return tabE(this,event)" readonly>
                                                    
                                                <input name="rec[]" id="rec{{$no}}" type="text" value="{{$detail->rec}}" 
                                                class="form-control rec"  readonly>
                                            </td>
                                            <td>
												<input name="KD_BRG[]" id="KD_BRG{{$no}}" type="text" class="form-control KD_BRG "
												value="{{$item->KD_BRG ?? ''}}" onkeyup="loadDataBBarang({{$no}})">
											</td>	
                                            <td>
                                                <input name="NA_BRG[]" id="NA_BRG{{$no}}" type="text" value="{{$detail->NA_BRG}}"
                                                class="form-control NA_BRG "  readonly>
                                            </td>		
                                            <td>
                                                <input name="ket_uk[]" id="ket_uk{{$no}}" type="text" value="{{$detail->ket_uk}}"
                                                class="form-control ket_uk" readonly >
                                            </td>	
                                            <td>
                                                <input name="ket_kem[]" id="ket_kem{{$no}}" type="text" value="{{$detail->ket_kem}}"
                                                class="form-control ket_kem" readonly >
                                            </td>
                                            <td>
                                                <input name="KD_BRG2[]" id="KD_BRG2{{$no}}" type="text" value="{{$detail->KD_BRG2}}"
                                                class="form-control KD_BRG2" readonly >
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
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>     
                                                            
                            </div>

                            {{-- <div class="col-md-2 row">
                                <a type="button" id='PLUSX' onclick="tambah()" class="fas fa-plus fa-sm md-3" style="font-size: 20px" ></a>
                            </div> --}}

                        <!-- ------------------------------------------------------------------------------- -->
                                
        
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button type="button" hidden id='TOPX'  onclick="location.href='{{url('/gsub/edit/?idx=' .$idx. '&tipx=top')}}'" class="btn btn-outline-primary">Top</button>
								<button type="button" hidden id='PREVX' onclick="location.href='{{url('/gsub/edit/?idx='.$header->NO_ID.'&tipx=prev&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Prev</button>
								<button type="button" hidden id='NEXTX' onclick="location.href='{{url('/gsub/edit/?idx='.$header->NO_ID.'&tipx=next&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Next</button>
								<button type="button" hidden id='BOTTOMX' onclick="location.href='{{url('/gsub/edit/?idx=' .$idx. '&tipx=bottom')}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button type="button" hidden id='NEWX' onclick="location.href='{{url('/gsub/edit/?idx=0&tipx=new')}}'" class="btn btn-warning">New</button>
								<button type="button" hidden id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button type="button" hidden id='UNDOX' onclick="location.href='{{url('/gsub/edit/?idx=' .$idx. '&tipx=undo' )}}'" class="btn btn-info">Undo</button> 
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button type="button" hidden id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								<button type="button" id='CLOSEX'  onclick="location.href='{{url('/gsub' )}}'" class="btn btn-outline-secondary">Close</button>


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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    var target;
	var idrow = 1;

    function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}

    $(document).ready(function () {

		idrow=<?=$no?>;
        baris=<?=$no?>;

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
					tambah();
					// var nomer = idrow-1;
					document.getElementById("NO_BUKTI").focus();
					// form.submit();
				}
				return false;
			}
		});
		
		$('body').on('click', '.btn-delete', function() {
			var val = $(this).parents("tr").remove();
			baris--;
			nomor();
		});
		
		$(".date").datepicker({
			'dateFormat': 'dd-mm-yy',
		})

		$tipx = $('#tipx').val();
				
        if ( $tipx == 'new' )
		{
			baru();			
			tambah();			
		}

        if ( $tipx != 'new' )
		{
			 //mati();	
    		 ganti();
		}    

		$('.date').datepicker({  
            dateFormat: 'dd-mm-yy'
		});

		////////////////////////////

		var dTableBBarang;
		var rowidBarang;
		loadDataBBarang = function(urut){
			rowidBarang = urut;
			$.ajax(
			{
				type: 'GET',
				url: "{{url('gsub/browse_barang')}}",
				data: {
					'KD_BRG': $("#KD_BRG"+rowidBarang).val(),
				},

				async : false,

				success: function( response )

				{

					resp = response;
					
						$("#KD_BRG"+rowidBarang).val(resp[0].KD_BRG);
						$("#NA_BRG"+rowidBarang).val(resp[0].NA_BRG);
						$("#ket_uk"+rowidBarang).val(resp[0].KET_UK);
						$("#ket_kem"+rowidBarang).val(resp[0].KET_KEM);
				}
			});
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
		
		
		$("#no_bukti").attr("readonly", true);
		$("#tgl").attr("readonly", false);	
		$("#notes").attr("readonly", false);
	
		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#rec" + i.toString()).attr("readonly", true);
			$("#KD_BRG" + i.toString()).attr("readonly", false);
			$("#NA_BRG" + i.toString()).attr("readonly", true);
			$("#ket_uk" + i.toString()).attr("readonly", true);
			$("#ket_kem" + i.toString()).attr("readonly", true);
			$("#KD_BRG2" + i.toString()).attr("readonly", false);
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
		
		$("#no_bukti").attr("readonly", true);
		$("#notes").attr("readonly", false);			
		$("#tgl").attr("readonly", false);	
		
		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", false);
			$("#KD_BRG" + i.toString()).attr("readonly", true);
			$("#NA_BRG" + i.toString()).attr("readonly", false);
			$("#ket_uk" + i.toString()).attr("readonly", false);
			$("#ket_kem" + i.toString()).attr("readonly", false);
			$("#KD_BRG2" + i.toString()).attr("readonly", true);
			$("#DELETEX" + i.toString()).attr("hidden", true);
		}	
	

		
	}


	function kosong() {
				
		$("#no_bukti").val("+");	
		$("#notes").val("");		

		var html = '';
		$('#detailx').html(html);
		 
	}
	
	function hapusTrans() {
		let text = "Hapus Master "+$('#no_bukti').val()+"?";
		if (confirm(text) == true) 
		{
			window.location ="{{url('/gsub/delete/'.$header->NO_ID )}}'";
			//return true;
		} 
		return false;
	}

	function tambah() {
        var x = document.getElementById('datatable').insertRow(baris + 1);
        html=`<tr>

                <td>
                    <input type="hidden" name="NO_ID[]" id="NO_ID${idrow}" type="text" value="new" readonly>
                    <input name="rec[]" id="rec${idrow}" type="text" onkeypress="return tabE(this,event)"
                    class="form-control rec" readonly>
                </td>
                <td>
                    <input name="KD_BRG[]" onkeyup='loadDataBBarang(${idrow})' data-rowid=${idrow} id="KD_BRG${idrow}" type="text"
                    class="form-control KD_BRG">
                </td>	
                <td>
                    <input name="NA_BRG[]" data-rowid=${idrow} id="NA_BRG${idrow}" type="text"
                    class="form-control NA_BRG" readonly>
                </td>		
                <td>
                    <input name="ket_uk[]" id="ket_uk${idrow}" type="text" class="form-control ket_uk" readonly>
                </td>	
                <td>
                    <input name="ket_kem[]" id="ket_kem${idrow}" type="text" class="form-control ket_kem" readonly>
                </td>
                <td>
                    <input name="KD_BRG2[]" onkeydown="tambahBarisBaru(event, ${idrow})" id="KD_BRG2${idrow}" type="text" class="form-control KD_BRG2">
                </td>

                <td>
					<button type="button" class="btn btn-sm btn-circle btn-outline-danger btn-delete" id='DELETEX${idrow}' onclick=''><i class="fa fa-fw fa-trash"></i></button>
                </td>					
         </tr>`;
				
        x.innerHTML = html;
        var html='';
		
		idrow++;
		baris++;
		nomor();
        hidup();

		$(".ronly").on('keydown paste', function(e) {
			e.preventDefault();
			e.currentTarget.blur();
		});
		$(".date").datepicker({
			'dateFormat': 'dd-mm-yy',
		});
    }

	function tambahBarisBaru(e, rowid) {
		if (e.key === 'Enter') {
			e.preventDefault();
			tambah(); // buat baris baru

			// beri jeda sedikit agar nomor()/hidup() selesai dulu
			setTimeout(function() {
				$("#KD_BRG" + (idrow - 1)).focus();
			}, 100);
		}
	}

    function nomor() {
		var i = 1;
		$(".REC").each(function() {
			$(this).val(i++);
		});
		// hitug();
	}

    
	function simpan() {
        hasilCek=0;
		$tipx = $('#tipx').val();
				
        // if ( $tipx == 'new' )
		// {
		// 	cekSup($('#KODES').val());		
		// }
		

        (hasilCek==0) ? document.getElementById("entri").submit() : alert('Masih ada kesalahan');
	}
</script>
@endsection

