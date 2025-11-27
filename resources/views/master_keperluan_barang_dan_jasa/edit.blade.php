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
               <h1 class="m-0">Keperluan Barang Dan Jasa</h1>	
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

                    <form action="{{($tipx=='new')? url('/brg-jasa/store/') : url('/brg-jasa/update/'.$header->NO_ID ) }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
        
                        <div class="tab-content mt-3">	
							
								<div class="form-group row">
									<div class="col-md-1">
										<label for="NO_BUKTI" class="form-label">No. Urut</label>
									</div>

										<input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
										placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

										<input name="tipx" class="form-control flagz" id="tipx" value="{{$tipx}}" hidden>
											
									
									<div class="col-md-2">
										<input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI"
										placeholder="Masukkan No Bukti" value="{{$header->NO_BUKTI}}" readonly>
									</div>    
									<div class="col-md-1">
										<label for="KD_DEPT" class="form-label">Kode Dept</label >
									</div>
									<div class="col-md-2">
										<input type="text" class="form-control KD_DEPT" id="KD_DEPT" name="KD_DEPT" onblur="browseDept()"
										placeholder="Masukkan Kode Suplier" value="{{$header->KD_DEPT}}" readonly>
									</div>    
									<div class="col-md-1">
                                            <input {{$header->POSTED==1 ? 'checked':''}} type="checkbox" class="form-check-input" id="POSTED" name="POSTED" value="1">
                                            <label for="POSTED">Posted</label>
                                    </div>                          
								</div>

								<div class="form-group row">
									<div class="col-md-1">
										<label for="TGL" class="form-label">Tanggal</label>
									</div>
									<div class="col-md-2">
										<input class="form-control date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}">
                                	</div>
									<div class="col-md-1">
										<label for="DEPT" class="form-label"></label>
									</div>
									<div class="col-md-4">
										<input type="text" class="form-control DEPT" id="DEPT" name="DEPT"
										placeholder="Masukkan Nama Suplier" value="{{$header->DEPT}}" readonly>
									</div>                                                 
								</div>
			
								<div class="form-group row">
									<div class="col-md-1">
										<label for="NOTES" class="form-label">Notes</label>
									</div>
									<div class="col-md-4">
										<input type="text" class="form-control NOTES" id="NOTES" name="NOTES"
										placeholder="Masukkan Notes" value="{{$header->NOTES}}">
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
                                            <th width="500px">Nama Barang dan Jasa</th>
                                            <th width="150">Type / Ukuran</th>
                                            <th width="150px">Merk</th>
                                            <th width="150">Kemasan</th>
                                            <th width="150px">Qty</th>
                                            <th width="150px">Harga</th>
                                            <th width="150px">Total</th>
                                            <th width="150px">Batas</th>
                                            <th width="150px">POS</th>
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
                                                    
                                                <input name="REC[]" id="REC{{$no}}" type="text" value="" 
                                                class="form-control REC"  readonly>
                                            </td>
                                            <td>
                                                <input name="NA_BRG[]" id="NA_BRG{{$no}}" type="text" value="{{$detail->NA_BRG}}"
                                                class="form-control NA_BRG "  readonly>
                                            </td>		
                                            <td>
                                                <input name="UKURAN[]" id="UKURAN{{$no}}" type="text" value="{{$detail->UKURAN}}"
                                                class="form-control UKURAN" readonly >
                                            </td>	
                                            <td>
                                                <input name="MERK[]" id="MERK{{$no}}" type="text" value="{{$detail->MERK}}"
                                                class="form-control MERK" readonly >
                                            </td>
                                            <td>
                                                <input name="SATUAN[]" id="SATUAN{{$no}}" type="text" value="{{$detail->SATUAN}}"
                                                class="form-control SATUAN" readonly >
                                            </td>
                                            <td>
                                                <input name="QTY[]" style="text-align: right" onblur="hitung()" id="QTY{{$no}}" type="text" value="{{$detail->QTY}}"
                                                    class="form-control QTY" required>
                                            </td>
                                            <td>
                                                <input name="HARGA[]" style="text-align: right" onblur="hitung()" id="HARGA{{$no}}" type="text" value="{{$detail->HARGA}}"
                                                    class="form-control HARGA" required>
                                            </td>
                                            <td>
                                                <input name="TOTAL[]" style="text-align: right" onblur="hitung()" id="TOTAL{{$no}}" type="text" value="{{$detail->TOTAL}}"
                                                    class="form-control TOTAL" readonly required>
                                            </td>
                                            <td>
                                                <input name="BATAS1[]" id="BATAS1{{$no}}" type="text" data-date-format="dd-mm-yyyy" autocomplete="off" value="{{date('d-m-Y',strtotime($detail->BATAS1))}}"
                                                class="form-control date BATAS1" readonly >
                                            </td>
											<td>
                                                <input name="POS[]" id="POS{{$no}}" type="text" value="{{$detail->POS}}"
                                                class="form-control POS" readonly >
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
                                            <td>
                                                <input class="form-control TQTY  text-primary font-weight-bold"
                                                    style="text-align: right" id="TQTY" name="TQTY"
                                                    value="{{$header->TOTAL ?? ''}}" readonly>
                                            </td>
                                            <td>
                                                <input class="form-control THARGA  text-primary font-weight-bold"
                                                    style="text-align: right" id="THARGA" name="THARGA"
                                                    value="{{$header->TOTAL ?? ''}}" readonly>
                                            </td>
                                            <td>
                                                <input class="form-control TTOTAL  text-primary font-weight-bold"
                                                    style="text-align: right" id="TTOTAL" name="TTOTAL"
                                                    value="{{$header->TOTAL ?? ''}}" readonly>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>     
                                                            
                            </div>

                            <div class="col-md-2 row">
                                <a type="button" id='PLUSX' onclick="tambah()" class="fas fa-plus fa-sm md-3" style="font-size: 20px" ></a>
                            </div>

                        <!-- ------------------------------------------------------------------------------- -->
                                
        
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button type="button" hidden id='TOPX'  onclick="location.href='{{url('/brg-jasa/edit/?idx=' .$idx. '&tipx=top')}}'" class="btn btn-outline-primary">Top</button>
								<button type="button" hidden id='PREVX' onclick="location.href='{{url('/brg-jasa/edit/?idx='.$header->NO_ID.'&tipx=prev&kodex='.$header->ACNO )}}'" class="btn btn-outline-primary">Prev</button>
								<button type="button" hidden id='NEXTX' onclick="location.href='{{url('/brg-jasa/edit/?idx='.$header->NO_ID.'&tipx=next&kodex='.$header->ACNO )}}'" class="btn btn-outline-primary">Next</button>
								<button type="button" hidden id='BOTTOMX' onclick="location.href='{{url('/brg-jasa/edit/?idx=' .$idx. '&tipx=bottom')}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button type="button" hidden id='NEWX' onclick="location.href='{{url('/brg-jasa/edit/?idx=0&tipx=new')}}'" class="btn btn-warning">New</button>
								<button type="button" hidden id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button type="button" hidden id='UNDOX' onclick="location.href='{{url('/brg-jasa/edit/?idx=' .$idx. '&tipx=undo' )}}'" class="btn btn-info">Undo</button> 
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button type="button" hidden id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								<button type="button" id='CLOSEX'  onclick="location.href='{{url('/brg-jasa' )}}'" class="btn btn-outline-secondary">Close</button>


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

	<div class="modal fade" id="browseDeptModal" tabindex="-1" role="dialog" aria-labelledby="browseDeptModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl" role="document">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="browseDeptModalLabel">Cari Dept</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-stripped table-bordered" id="table-bdept">
					<thead>
						<tr>
							<th>Kode</th>
							<th>Department</th>
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
					// tambah();
					// var nomer = idrow-1;
					document.getElementById("KD_DEPT").focus();
					// form.submit();
				}
				return false;
			}
		});

		$("#TQTY").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#THARGA").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#TTOTAL").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		jumlahdata = 100;
		
		for (i = 0; i <= jumlahdata; i++) {
			$("#QTY" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#HARGA" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});$("#TOTAL" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});$("#TOTAL" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#TOTAL" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});$("#TOTAL" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});$("#TOTAL" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});

		}
		
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
		}

        if ( $tipx != 'new' )
		{
			 //mati();	
    		 ganti();
		}    

		$('.date').datepicker({  
            dateFormat: 'dd-mm-yy'
		});

		//////////////////////////////////////
		// CHOOSE Dept
		var dTableBDept;
		loadDataBDept = function(){

			$.ajax(
			{
				type: 'GET',
				url: '{{url('brg-jasa/browse_dept')}}',


				beforeSend: function(){
					$("#LOADX").show();
				},


				success: function( response )
				{
					$("#LOADX").hide();

					resp = response;
					if(dTableBDept){
						dTableBDept.clear();
					}
					for(i=0; i<resp.length; i++){

						dTableBDept.row.add([
							'<a href="javascript:void(0);" onclick="chooseDept(\''+resp[i].KD+'\',  \''+resp[i].DEP+'\')">'+resp[i].KD+'</a>',
							resp[i].DEP,
						]);
					}
					dTableBDept.draw();
				}
			});
		}

		dTableBDept = $("#table-bdept").DataTable({

		});

		browseDept = function(){
			loadDataBDept();
			$("#browseDeptModal").modal("show");
		}

		chooseDept = function(KD,DEP){
			$("#KD_DEPT").val(KD);
			$("#DEPT").val(DEP);
			$("#browseDeptModal").modal("hide");

		}

		$("#KD_DEPT").keypress(function(e){

			if(e.keyCode == 13){
				e.preventDefault();
				browseKode();
			}
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
		
		
		$("#NO_BUKTI").attr("readonly", true);	
		$("#KD_DEPT").attr("readonly", true);	
		$("#DEPT").attr("readonly", true);	
		$("#NOTES").attr("readonly", false);					
		$("#TGL").attr("readonly", false);
	
		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#NA_BRG" + i.toString()).attr("readonly", false);
			$("#UKURAN" + i.toString()).attr("readonly", false);
			$("#MERK" + i.toString()).attr("readonly", false);
			$("#SATUAN" + i.toString()).attr("readonly", false);
			$("#QTY" + i.toString()).attr("readonly", false);
			$("#HARGA" + i.toString()).attr("readonly", false);
			$("#TOTAL" + i.toString()).attr("readonly", false);
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
		
		$("#NO_BUKTI").attr("readonly", true);			
		$("#KD_DEPT").attr("readonly", true);	
		$("#DEPT").attr("readonly", true);	
		$("#NOTES").attr("readonly", false);			
		$("#NO_BUKTI").attr("readonly", false);		
		$("#TGL").attr("readonly", false);	
		
		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", false);
			$("#NA_BRG" + i.toString()).attr("readonly", true);
			$("#UKURAN" + i.toString()).attr("readonly", true);
			$("#MERK" + i.toString()).attr("readonly", true);
			$("#SATUAN" + i.toString()).attr("readonly", true);
			$("#QTY" + i.toString()).attr("readonly", true);
			$("#HARGA" + i.toString()).attr("readonly", true);
			$("#TOTAL" + i.toString()).attr("readonly", true);
			$("#DELETEX" + i.toString()).attr("hidden", true);
		}	
	

		
	}


	function kosong() {
				
		$("#NO_BUKTI").val("");			
		$("#KD_DEPT").val("");	
		$("#DEPT").val("");	
		$("#NOTES").val("");			
		$("#NO_BUKTI").val("");		

		var html = '';
		$('#detailx').html(html);
		 
	}
	
	function hapusTrans() {
		let text = "Hapus Master "+$('#KD_DEPT').val()+"?";
		if (confirm(text) == true) 
		{
			window.location ="{{url('/brg-jasa/delete/'.$header->NO_ID )}}'";
			//return true;
		} 
		return false;
	}

	function tambah() {
        var x = document.getElementById('datatable').insertRow(baris + 1);
        html=`<tr>

                <td>
                    <input type="hidden" name="NO_ID[]" id="NO_ID${idrow}" type="text" value="new" readonly>
                    <input name="REC[]" id="REC${idrow}" type="text" onkeypress="return tabE(this,event)"
                    class="form-control REC" readonly>
                </td>
                <td>
                    <input name="NA_BRG[]" data-rowid=${idrow} id="NA_BRG${idrow}" type="text"
                    class="form-control NA_BRG">
                </td>		
                <td>
                    <input name="UKURAN[]" id="UKURAN${idrow}" type="text" class="form-control UKURAN" readonly >
                </td>	
                <td>
                    <input name="MERK[]" id="MERK${idrow}" type="text" class="form-control MERK" readonly >
                </td>
                <td>
                    <input name="SATUAN[]" id="SATUAN${idrow}" type="text" class="form-control SATUAN" readonly >
                </td>
                <td>
                    <input name="QTY[]" style="text-align: right" onblur="hitung()" id="QTY${idrow}" type="text" value="0"
                    class="form-control QTY" required>
                </td>
                <td>
                    <input name="HARGA[]" style="text-align: right" onblur="hitung()" id="HARGA${idrow}" type="text" value="0"
                    class="form-control HARGA" required>
                </td>
                <td>
                    <input name="TOTAL[]" style="text-align: right" onblur="hitung()" id="TOTAL${idrow}" type="text" value="0"
                    class="form-control TOTAL" readonly required>
                </td>
                <td>
                    <input name="BATAS1[]" data-rowid=${idrow} id="BATAS1${idrow}" type="text" data-date-format="dd-mm-yyyy" autocomplete="off" value=""
                    class="form-control date BATAS1">
                </td>
				<td>
                    <input name="POS[]" id="POS${idrow}" type="text" class="form-control POS">
                </td>
                <td>
					<button type="button" class="btn btn-sm btn-circle btn-outline-danger btn-delete" id='DELETEX${idrow}' onclick=''><i class="fa fa-fw fa-trash"></i></button>
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
		
		idrow++;
		baris++;
		nomor();
		hitung();
        hidup();

		$(".ronly").on('keydown paste', function(e) {
			e.preventDefault();
			e.currentTarget.blur();
		});
		$(".date").datepicker({
			'dateFormat': 'dd-mm-yy',
		});
    }

    function nomor() {
		var i = 1;
		$(".REC").each(function() {
			$(this).val(i++);
		});
		// hitug();
	}

    function hitung() {
		var TQTY = 0;
		var THARGA = 0;
		var TTOTAL = 0;

		$(".QTY").each(function() {
			var tr = $(this).closest('tr');
			var qty = parseFloat($(this).val().replace(/,/g, '')) || 0;
			var harga = parseFloat(tr.find(".HARGA").val().replace(/,/g, '')) || 0;

			// hitung total per baris
			var total = qty * harga;
			tr.find(".TOTAL").val(numberWithCommas(total.toFixed(2)));

			// akumulasi
			TQTY += qty;
			THARGA += harga;
			TTOTAL += total;
		});

		// isi total bawah
		$('#TQTY').val(numberWithCommas(TQTY));
		$("#TQTY").autoNumeric('update');

		$('#THARGA').val(numberWithCommas(THARGA));
		$("#THARGA").autoNumeric('update');

		$('#TTOTAL').val(numberWithCommas(TTOTAL));
		$("#TTOTAL").autoNumeric('update');
	}


     
    var hasilCek;
	function cekSup(kodes) {
		$.ajax({
			type: "GET",
			url: "{{url('sup/ceksup')}}",
            async: false,
			data: ({ KODES: kodes, }),
			success: function(data) {
                if (data.length > 0) {
                    $.each(data, function(i, item) {
                        hasilCek=data[i].ADA;
                    });
                }
			},
			error: function() {
				alert('Error cekSup occured');
			}
		});
		return hasilCek;
	}
    
	function simpan() {
        hasilCek=0;
		$tipx = $('#tipx').val();
				
        // if ( $tipx == 'new' )
		// {
		// 	cekSup($('#KODES').val());		
		// }
		

        (hasilCek==0) ? document.getElementById("entri").submit() : alert('Barang Jasa '+$('#KD_DEPT').val()+' sudah ada!');
	}
</script>
@endsection

