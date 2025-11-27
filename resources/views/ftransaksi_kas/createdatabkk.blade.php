@extends('layouts.main')

<style>
    .card {

    }

    .form-control:focus {
        background-color: #E0FFFF !important;
    }
</style>


@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">Proses Data BKK</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="/pemasarankas">Proses Data BKK</a></li>
						<li class="breadcrumb-item active">Add</li>
					</ol>
				</div>
			</div>
        </div>
    </div>
	
    <!-- Status -->
    @if (session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>
    @endif

    <div class="content">
        <div class="container-fluid">
        <div class="row">
            <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="storedatabkk" id="entri"  method="POST">
                        @csrf
                        {{-- <ul class="nav nav-tabs">
                            <li class="nav-item active">
                                <a class="nav-link active" href="#data" data-toggle="tab">Data</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#dokumen" data-toggle="tab">Dokumen</a>
                            </li>
                        </ul> --}}
        
                        <div class="tab-content mt-3">
						
							<div class="form-group row">
							<div class="col-md-1" align="left"><strong style="font-size: 13px;">PMS</strong></div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control WILAYAH" id="WILAYAH" name="WILAYAH" placeholder="" readonly >
                                </div>
							</div>
								
							<div class="form-group row">
								<div class="col-md-1" align="left"><strong style="font-size: 13px;">No BKK</strong></div>
								<div class="col-md-2">
									<input type="text" class="form-control nobukti1" id="nobukti1" name="nobukti1" placeholder="" readonly >
								</div>
								<div class="col-md-1" align="left"><strong style="font-size: 13px;">s.d</strong></div>
								<div class="col-md-2">
									<input type="text" class="form-control nobukti2" id="nobukti2" onblur="hitung()" name="nobukti2" placeholder="" readonly >
								</div>
								
								<div class="col-md-1">
									<input type="text" class="form-control BUKTI" id="BUKTI" name="BUKTI" value="0" placeholder=""  readonly>
								</div>
								<div class="col-md-2" align="left"><strong style="font-size: 13px;">Jumlah BKK PMS</strong></div>
                            </div>
							
							<div class="form-group row">
                                <div class="col-md-1">
                                    <label for="TGL" class="form-label">Tgl Baru</label>
                                </div>
                                <div class="col-md-2">
                                   <input class="form-control date" id="TGL_AMBIL" name="TGL_AMBIL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{ now()->format('d-m-Y') }}" >
                                </div>
								
								<div class="col-md-4">										
									<a type="button" onclick="getKasPms('cari')" class="btn btn-success">CARI DATA PMS</a>
                                </div>
                            </div>

							<div class="form-group row">
								<div class="col-md-1" align="left"><strong style="font-size: 13px;">Ubah No Kasir</strong></div>
								<div class="col-md-2">
									<input type="text" class="form-control nokasir1" id="nokasir1" name="nokasir1" placeholder="" value="{{ session()->get('filter_nokasir1') }}" >
								</div>
								<div class="col-md-1" align="left"><strong style="font-size: 13px;">s.d</strong></div>
								<div class="col-md-2">
									<input type="text" class="form-control nokasir2" id="nokasir2" name="nokasir2" placeholder="ZZZ" value="{{ session()->get('filter_nokasir2') }}">
								</div>
								
								<div class="col-md-4">
									<button type="button" onclick="getKasPms('isi')" class="btn btn-success" name="isi"><i class="fa fa-save"></i> ISI OTOMATIS</button>
                                </div>
                            </div>
							
  

							<table id="datatable" class="table table-striped table-border">
                                <thead>
                                    <tr>
										<th width="50px" style="text-align: center;">No</th>
										<th width="100px" style="text-align: center;">BKK PWK</th>
                                        <th style="text-align: center;">No Bukti Kasir</th>
                                        <th width="100px" style="text-align: center;">Tanggal</th>
                                        <th width="300px" style="text-align: center;">Uraian</th>
                                        <th style="text-align: center;">Ket</th>
                                        <th style="text-align: center;">Jumlah</th>
                                        <th style="text-align: center;">Tgl-Ambil</th>
										<th></th>	
                                    </tr>
                                </thead>
								
	
								<tbody id="detailKas">
                                    <tr>
										<td>
                                            <input name="REC[]" id="REC0" type="text" value="1" class="form-control REC" onkeypress="return tabE(this,event)" readonly>
                                        </td>
                                        <td>
                                            <input name="BKK_PWK[]" id="BKK_PWK0" type="text" class="form-control BKK_PWK" readonly >
                                        </td>
                                        <td>
                                            <input name="NO_BUKTI[]" id="NO_BUKTI0" type="text" class="form-control NO_BUKTI" readonly >
                                        </td>
										<td>
											<input name="TGL[]" id="TGL0" type="text" class="date form-control text_input" data-date-format="dd-mm-yyyy" value="<?php if (isset($_POST["tampilkan"])) {
																																										} else echo date('d-m-Y'); ?>" onclick="select()" readonly>
										</td>
                                        <td>
                                            <input name="URAIAN[]" id="URAIAN0" type="text" class="form-control URAIAN" readonly>
                                        </td>
                                        <td>
                                            <input name="KET[]" id="KET0" type="text" class="form-control KET" readonly>
                                        </td>
										<td><input name="JUMLAH[]" id="JUMLAH0" type="text" style="text-align: right"  class="form-control JUMLAH text-primary" readonly></td>
                                        <td>
											<input name="TGL_AMBIL[]" id="TGL_AMBIL0" type="text" class="date form-control text_input" data-date-format="dd-mm-yyyy" value="<?php if (isset($_POST["tampilkan"])) {
																																							} else echo date('d-m-Y'); ?>" onclick="select()" readonly>
										</td>
                                    </tr>
                                </tbody>
								<tfoot>
									</tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><input class="form-control TJUMLAH  text-primary " style="text-align: right" id="TJUMLAH" name="TJUMLAH" value="0" readonly></td>
                                    <td></td>
									</tr>
                                </tfoot>
                            </table>

                        <div class="mt-3">
                            <div class="form-group row">
                                <div class="col-md-2">
                                </div>
                                <div class="col-md-4">
								<!--
									<button type="button" onclick="simpan()" class="btn btn-success"><i class="fa fa-save"></i> Save</button>										
									<a type="button" href="javascript:javascript:history.go(-1)" class="btn btn-danger">Cancel</a>
								-->
									<button type="submit" class="btn btn-success" name="proses"><i class="fa fa-save"></i> PROSES</button>
                                </div>
							</div>
                        </div>
                    </form>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
	
	
	<div class="modal fade" id="browseWilaModal" tabindex="-1" role="dialog" aria-labelledby="browseWilaModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseWilaModalLabel">Cari Wilayah</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bwila">
				<thead>
					<tr>
						<th>Wilayah</th>
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
	
	<div class="modal fade" id="browseBkk1Modal" tabindex="-1" role="dialog" aria-labelledby="browseBkk1ModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseBkk1ModalLabel">No BKK</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bbkk1">
				<thead>
					<tr>
						<th>No Bukti</th>
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

<script>
	var idrow = 1;
	var baris = 1;
	
    function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}

	$(document).ready(function() {

		$("#TOTAL").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#BIAYA").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#TJUMLAH").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});


		
		$(".date").datepicker({
			'dateFormat': 'dd-mm-yy',
		})



		var dTableBWila;
		loadDataBWila = function(){
			$.ajax(
			{
				type: 'GET',    
				url: '{{url('wila/browsewilayah')}}',
				success: function( response )
				{
					resp = response;
					if(dTableBWila){
						dTableBWila.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBWila.row.add([
							'<a href="javascript:void(0);" onclick="chooseWila(\''+resp[i].WILAYAH+'\',  \''+resp[i].WILAYAH1+'\' )">'+resp[i].WILAYAH+'</a>',
							resp[i].WILAYAH1,
						]);
					}
					dTableBWila.draw();
				}
			});
		}
		
		dTableBWila = $("#table-bwila").DataTable({
			
		});
		
		browseWila = function(){
			loadDataBWila();
			$("#browseWilaModal").modal("show");
		}
		
		chooseWila = function(WILAYAH, WILAYAH1){
			$("#WILAYAH").val(WILAYAH);
			$("#WILAYAH1").val(WILAYAH1);
			
			$("#browseWilaModal").modal("hide");
		}
		
		$("#WILAYAH").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseWila(0);
			}
		});

//////////////////////////////////////////////////////////////////////////	

		var dTableBBkk1;
	    var rowidBukti;
		
		loadDataBBkk1 = function(){
			$.ajax(
			{
				type: 'GET',    
				url: '{{url('kas/browsebkk1')}}',
				data: {
					 WILX : $("#WILAYAH").val(),
				},
				success: function( response )
				{
					resp = response;
					if(dTableBBkk1){
						dTableBBkk1.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBBkk1.row.add([
							'<a href="javascript:void(0);" onclick="chooseBkk1(\''+resp[i].NO_BUKTI+'\')">'+resp[i].NO_BUKTI+'</a>',
						]);
					}
					dTableBBkk1.draw();
				}
			});
		}
		
		dTableBBkk1 = $("#table-bbkk1").DataTable({
			
		});
		
		browseBkk1 = function(rid){
			rowidBukti = rid;
			loadDataBBkk1();
			$("#browseBkk1Modal").modal("show");
		}
		
		chooseBkk1 = function(NO_BUKTI){
			
			if ( rowidBukti == '1' )
		    {		
				$("#nobukti1").val(NO_BUKTI);
			}
			else
			{
				$("#nobukti2").val(NO_BUKTI);
			}				
			$("#browseBkk1Modal").modal("hide");
		}
		
		$("#nobukti1").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseBkk1(1);
			}
		});

		$("#nobukti2").keypress(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseBkk1(2);
			}
		});
		
//////////////////////////////////////////////////////////////////////////	


		
		
	});		

	function hitung(){
		
		var darix = $("#nobukti1").val() ; 
		var sampaix = $("#nobukti2").val() ; 
	    
		if ( darix != '' )
	    {		
	       if ( sampaix != '' )
		   {
			   var jmlx =  sampaix - darix;
			   $(".BUKTI").val(jmlx);
		   }
		}
		
		
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

 	function simpan() {
		
		var tgl = $('#TGL').val();
		var bulanPer = {{session()->get('periode')['bulan']}};
		var tahunPer = {{session()->get('periode')['tahun']}};
		
        var check = '0';
		
		
			if ( $('#NO_PO').val()=='' ) 
            {			
			    check = '1';
				alert("PO# Harus diisi.");
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
		
	}
	
	function getKasPms($stat)
	{

     
		
		var mulai = (idrow==baris) ? idrow-1 : idrow;
		
		$.ajax(
			{
				type: 'GET',    
				url: "{{url('kas/browse_kas_pms')}}",
				data: {
					 STATX : $stat,
					 BUKTIX : $("#nobukti1").val(),
					 BUKTIY : $("#nobukti2").val(),
					 WILX : $("#WILAYAH").val(),
					 TGL_AMBIL : $("#TGL_AMBIL").val(),
					 nokasir1 : $("#nokasir1").val(),
					 nokasir2 : $("#nokasir2").val(),
				},
				success: function( resp )
				{
					var html = '';
					for(i=0; i<resp.length; i++){
						html+=`<tr>
                                    <td><input id='REC${i}' value=${resp[i].REC+1} type='text' class='REC form-control' onkeypress='return tabE(this,event)' readonly></td>
                                    <td><input data-rowid=${i} id='BKK_PWK${i}' value="${resp[i].BKK_PWK}" type='text' class='form-control BKK_PWK'  readonly ></td>
                                    <td><input data-rowid=${i} id='NO_BUKTI${i}' value="${resp[i].NO_BUKTI}" type='text' class='form-control  NO_BUKTI' readonly></td>
                                    <td><input data-rowid=${i} id='TGL${i}' value="${resp[i].TGL}" type='text' class='form-control  TGL' readonly></td>
                                    <td><input  data-rowid=${i} id='URAIAN${i}' value="${resp[i].URAIAN}" type='text' class='form-control URAIAN'  readonly ></td>
                                    <td><input data-rowid=${i} id='KET${i}' value="${resp[i].KET}" type='text' class='form-control KET'  readonly ></td>
                                    <td><input onblur="hitung()" id='JUMLAH${i}' value="${resp[i].JUMLAH}" type='text' style='text-align: right' class='form-control JUMLAH text-primary' readonly></td>
                                    <td><input data-rowid=${i} id='TGL_AMBIL${i}' value="${resp[i].TGL_AMBIL}" type='text' class='form-control  TGL_AMBIL' readonly></td>

                                    
                                </tr>`;
					}
					$('#detailKas').html(html);

					$(".JUMLAH").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".JUMLAH").autoNumeric('update');

					idrow=resp.length;
					baris=resp.length;
					hitung();
					nomor();
				}
			});
	}







		
</script>
@endsection