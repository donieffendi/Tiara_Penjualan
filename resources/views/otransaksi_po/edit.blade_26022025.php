@extends('layouts.plain')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .card {

    }

	
    .form-control:focus {
        background-color: #b5e5f9 !important;
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
	

    /* style tambahan baru */
    .form-control:disabled,
    .form-control[readonly] {
        background-color: #f7d8b4 !important;
        opacity: 1;
    }

    .row {
        margin-bottom: 8px !important;
    }

	/* menghilangkan padding */
	.content-header {
		padding: 0 !important;
	}

</style>

@section('content')



<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">

        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
        <div class="row">
            <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <form action="{{($tipx=='new')? url('/po/store?flagz='.$flagz.'&golz='.$golz.'') : url('/po/update/'.$header->NO_ID.'&flagz='.$flagz.'&golz='.$golz.'' ) }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
                        <div class="tab-content mt-3">

                            <div class="form-group row">
                                <div class="col-md-1" align="left">
                                    <label for="NO_BUKTI" class="form-label">Bukti#</label>
                                </div>
								

                                   <input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
                                    placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

									<input name="tipx" class="form-control tipx" id="tipx" value="{{$tipx}}" hidden>
									<input name="flagz" class="form-control flagz" id="flagz" value="{{$flagz}}" hidden>
									<input name="golz" class="form-control golz" id="golz" value="{{$golz}}" hidden>

								
								
                                <div class="col-md-2">
                                    <input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI"
                                    placeholder="Masukkan Bukti#" value="{{$header->NO_BUKTI}}" readonly>
                                </div>

                                <div class="col-md-1" align="right">
                                    <label for="TGL" class="form-label">Tgl</label>
                                </div>
                                <div class="col-md-2">
								  <input class="form-control date" id="TGL" onchange="jtempo()" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}">
                                </div>

								<div class="col-md-1" align="right">
									<label for="JTEMPO" class="form-label">Tgl Kirim</label>
								</div>
								<div class="col-md-2">
									<input class="form-control date" id="JTEMPO" name="JTEMPO" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->JTEMPO))}}">
								</div> 
								
								
                            </div>



                            <div class="form-group row">
                                <div class="col-md-1" align="left">	
                                    <label for="KODES" class="form-label">Suplier#</label>
                                </div>
								
                                <div class="col-md-3" >
                                   <select id="KODES"  onchange="ambil_hari()" name="KODES" style="width: 100%" ></select>        							      
                                    <input type="text" hidden class="form-control NAMAS" id="NAMAS" name="NAMAS" value="{{$header->NAMAS}}" placeholder="Masukkan Nama" >
                                    <input type="text" hidden class="form-control HARI" id="HARI" name="HARI" value="{{$header->HARI}}" placeholder="Masukkan Hari" >

                                </div>
		
                                <div class="col-md-3" >
									<input type="checkbox" class="form-check-input" id="PKP" name="PKP" readonly  value="{{$header->PKP}}" {{ ($header->PKP == 1) ? 'checked' : '' }}>
									<label for="PKP" class="form-label">Pkp</label>
									<input type="text" hidden class="form-control ZPKP" id="ZPKP" name="ZPKP" value="{{$header->PKP}}" placeholder="Masukkan Pkp" >
									
								</div>
		
							</div>
							
							<div {{( $golz =='J' ) ? 'hidden' : '' }} class="form-group row">
								<div class="col-md-1" align="left">
									<label style="color:red">*</label>									
									<label for="NO_SO" class="form-label">SO#</label>
								</div>
								<div class="col-md-2 input-group" >
									<input type="text" class="form-control NO_SO" id="NO_SO" name="NO_SO" placeholder="Pilih SO"value="{{$header->NO_SO}}" style="text-align: left" readonly >
									<button type="button" class="btn btn-primary" onclick="browseSo()"><i class="fa fa-search"></i></button>
								</div>
							</div>


							<!-- loader tampil di modal  -->
							<div class="loader" style="z-index: 1055;" id='LOADX' ></div>
							<!-- batas load -->

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

							<div class="form-group row" {{( $golz =='C' ) ? 'hidden' : '' }}>

								<div class="col-md-1" align="left">
									<label style="color:red">*</label>									
									<label for="NO_PP" class="form-label">No PP</label>
								</div>
								<div class="col-md-2 input-group" >
									<input type="text" class="form-control NO_PP" id="NO_PP" name="NO_PP" placeholder="Pilih Bukti PP"value="{{$header->NO_PP}}" style="text-align: left" readonly >
									<button type="button" class="btn btn-primary" onclick="browsePp()"><i class="fa fa-search"></i></button>
								</div>
								
							</div>

							<div class="form-group row">
								<!-- code text box baru -->
								<div class="col-md-5 form-group row special-input-label">

									<input type="text" class="NOTES" id="NOTES" name="NOTES" 
										value="{{$header->NOTES}}" placeholder=" " >
									<label for="NOTES">Notes</label>
								</div>
								<!-- tutupannya -->

                                <!-- <div class="col-md-1" align="left">
                                    <label for="NOTES" class="form-label">Notes</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NOTES" id="NOTES" name="NOTES" value="{{$header->NOTES}}" placeholder="Masukkan Notes" >
                                </div> -->

								<!-- <div class="input-field col s12 m6">
									<input type="text" id="NOTES" name="NOTES"
										value="{{ $header->NOTES ?? '' }}">
									<label for="NOTES">Notes</label>
								</div> -->

								<!-- <div class="col-md-1" align="right">
                                    <label for="GUDANG" class="form-label">Gudang</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control GUDANG" id="GUDANG" name="GUDANG" placeholder="pilih Gudang"  value="{{$header->GUDANG}}" style="width:140px" readonly >
                                </div> -->
        
                            </div>
							
					<!--		 <div style="overflow-y:scroll; height:200px;" class="col-md-12 scrollable" align="right"> -->
					<!--		<div style="overflow-y:scroll; " class="col-md-12 scrollable" align="right"> -->
								
								<!-- <table id="datatable" class="table table-striped table-border table-scrollable">                 -->
								<table id="datatable" class="table table-striped table-border">   

                                <thead>
                                    <tr>
										<th width="50px" style="text-align:center">No.</th>
	
										<th width="200px" style="text-align:center">Barang</th>
										
										<th width="200px" style="text-align:center">Nama</th>

                                        <th width="100px" style="text-align:center">Stn</th>
                                        <th width="100px" style="text-align:center">Qty</th> 
                                        <th {{( $golz =='C' ) ? 'hidden' : '' }} width="150px" style="text-align:center">Harga</th>
               
                                        <th {{( $golz =='C' ) ? 'hidden' : '' }} width="150px" style="text-align:center">Total</th>
										<th {{( $golz =='C' ) ? 'hidden' : '' }} width="150px" style="text-align:center">Diskon</th>
										<th width="150px" style="text-align:center">Ket</th>
    

                                        <th></th>
                                       						
                                    </tr>
                                </thead>
								<tbody id="detailPpd">
        
								<tbody>
								<?php $no=0 ?>
								@foreach ($detail as $detail)		
                                    <tr>
                                        <td>
                                            <input type="hidden" name="NO_ID[]{{$no}}" id="NO_ID" type="text" value="{{$detail->NO_ID}}" 
                                            class="form-control NO_ID" onkeypress="return tabE(this,event)" readonly>
											
                                            <input name="REC[]" id="REC{{$no}}" type="text" value="{{$detail->REC}}" class="form-control REC" onkeypress="return tabE(this,event)" readonly style="text-align:center">
                                        </td>

										<td>
                                            <input name="KD_BRG[]" id="KD_BRG{{$no}}" type="text" class="form-control KD_BRG " 
											value="{{$detail->KD_BRG}}" onblur="browseBarang({{$no}})">
										</td>

										<td>
                                            <input name="NA_BRG[]" id="NA_BRG{{$no}}" type="text" class="form-control NA_BRG " value="{{$detail->NA_BRG}}">
                                        </td>
                                        <td>
                                            <input name="SATUAN[]" id="SATUAN{{$no}}" type="text" class="form-control SATUAN" value="{{$detail->SATUAN}}" readonly required>
                                        </td>										
										<td>
										    <input name="QTY[]"  onclick="select()" onblur="hitung()" value="{{$detail->QTY}}" id="QTY{{$no}}" type="text" style="text-align: right"  class="form-control QTY" >
										</td>                         
                                        																						
										<td {{( $golz =='C' ) ? 'hidden' : '' }}>
										    <input name="HARGA[]"  onclick="select()" onblur="hitung()" value="{{$detail->HARGA}}" id="HARGA{{$no}}" type="text" style="text-align: right"  class="form-control HARGA">
										</td>
	
										<td {{( $golz =='C' ) ? 'hidden' : '' }}>
										    <input name="TOTAL[]" onclick="select()" onblur="hitung()"  value="{{$detail->TOTAL}}" id="TOTAL{{$no}}" type="text" style="text-align: right"  class="form-control TOTAL" readonly>
						
											<input name="PPNX[]" hidden onblur="hitung()"  value="{{$detail->PPN}}" id="PPNX{{$no}}" type="text" style="text-align: right"  class="form-control PPNX" readonly>
											<input name="DPP[]"  hidden onblur="hitung()"  value="{{$detail->DPP}}" id="DPP{{$no}}" type="text" style="text-align: right"  class="form-control DPP" readonly>
										</td>
										<td {{( $golz =='C' ) ? 'hidden' : '' }}>
											<input name="DISK[]" onclick="select()" onblur="hitung()"  value="{{$detail->DISK}}"  id="DISK{{$no}}" type="text" style="text-align: right"  class="form-control DISK" >
                                        </td> 
										<td>
											<input name="KET[]" id="KET{{$no}}" type="text" class="form-control KET" value="{{$detail->KET}}"  >
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
									<td></td>
									<td></td>
									<!-- <td><input class="form-control TTOTAL  text-primary" style="text-align: right"  id="TTOTAL" name="TTOTAL" value="{{$header->TOTAL}}" readonly></td> -->
                                    <td></td>
                                </tfoot>
                            </table>   
						<!-- scroll -->

						<!--</div> -->
							
						<!-- batas -->

						</div>
					</div> 


						<div class="tab-content mt-6">
						
							<div class="form-group row" >
								<div class="col-md-1" align="center" >
								</div>

                                <div class="col-md-1" align="center" hidden >
                                	<a type="button" id='PLUSX' onclick="tambah()" class="fas fa-plus fa-sm md-3" style="font-size: 20px" ></a>
                                </div>

                                <div class="col-md-3" align="right">
                                    <label for="total" class="form-label">Total Qty</label>
                                </div>

                                <div class="col-md-2" >
                                    <input type="text"  onclick="select()" onkeyup="hitung()" class="form-control TTOTAL_QTY" id="TTOTAL_QTY" name="TTOTAL_QTY" placeholder="" value="{{$header->TOTAL_QTY}}" style="text-align: right" readonly>
                                </div>

                                <div class="col-md-1" align="right" {{( $golz =='C' ) ? 'hidden' : '' }}>
								<label for="total" class="form-label">Total</label>
                                
                                </div>


                                <div class="col-md-2" align="right" {{( $golz =='C' ) ? 'hidden' : '' }}>
                                    
									<input type="text"  hidden onclick="select()" onkeyup="hitung()" class="form-control TTOTAL" id="TTOTAL" name="TTOTAL" placeholder="" value="{{$header->TOTAL}}" style="text-align: right" readonly>
             						<input type="text"  onclick="select()" onkeyup="hitung()" class="form-control TDPP" id="TDPP" name="TDPP" placeholder="" value="{{$header->TDPP}}" style="text-align: right" readonly>
             
								</div>
							</div>

                            <div class="form-group row" {{( $golz =='C' ) ? 'hidden' : '' }}>
                                <div class="col-md-7" align="right">
                                    <label for="TPPN" class="form-label">Ppn</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text"  onclick="select()" onkeyup="hitung()" class="form-control TPPN" id="TPPN" name="TPPN" placeholder="" value="{{$header->TPPN}}" style="text-align: right" readonly>
                                </div>
							</div>

                            <!-- <div class="form-group row" {{( $golz =='C' ) ? 'hidden' : '' }}> -->
                            <div class="form-group row" hidden >
                                <div class="col-md-7" align="right">
                                    <label for="TDISK" class="form-label">Diskon</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text"  onclick="select()" onkeyup="hitung()" class="form-control TDISK" id="TDISK" name="TDISK" placeholder="" value="{{$header->TDISK}}" style="text-align: right" readonly>
                                </div>
							</div>
							
                            <div class="form-group row" {{( $golz =='C' ) ? 'hidden' : '' }}>
                                <div class="col-md-7" align="right">
                                    <label for="NETT" class="form-label">Nett</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text"  onclick="select()" onkeyup="hitung()" class="form-control NETT" id="NETT" name="NETT" placeholder="" value="{{$header->NETT}}" style="text-align: right" readonly>
                                </div>
							</div>
							
						</div>
						
						
						   
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button hidden type="button" id='TOPX'  onclick="location.href='{{url('/po/edit/?idx=' .$idx. '&tipx=top&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-primary">Top</button>
								<button hidden type="button" id='PREVX' onclick="location.href='{{url('/po/edit/?idx='.$header->NO_ID.'&tipx=prev&flagz='.$flagz.'&golz='.$golz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Prev</button>
								<button hidden type="button" id='NEXTX' onclick="location.href='{{url('/po/edit/?idx='.$header->NO_ID.'&tipx=next&flagz='.$flagz.'&golz='.$golz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Next</button>
								<button hidden type="button" id='BOTTOMX' onclick="location.href='{{url('/po/edit/?idx=' .$idx. '&tipx=bottom&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button hidden type="button" id='NEWX' onclick="location.href='{{url('/po/edit/?idx=0&tipx=new&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-warning">New</button>
								<button hidden type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button hidden type="button" id='UNDOX' onclick="location.href='{{url('/po/edit/?idx=' .$idx. '&tipx=undo&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-info">Undo</button>  
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button hidden type="button" id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								
								<!-- <button type="button" id='CLOSEX'  onclick="location.href='{{url('/po?flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-secondary">Close</button>  -->
								
								<!-- tombol close sweet alert -->
							     	<button type="button" id='CLOSEX' onclick="closeTrans()" class="btn btn-outline-secondary">Close</button></div>   
							</div>
						</div>
						
						
                    </form>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>


 	<div class="modal fade" id="browseSuplierModal" tabindex="-1" role="dialog" aria-labelledby="browseSuplierModalLabel" aria-hidden="true">
	  <div class="modal-dialog mw-100 w-75" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseSuplierModalLabel">Cari Suplier</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bsuplier">
				<thead>
					<tr>
						<th>Suplier</th>
						<th>Nama</th>
						<th>Alamat</th>
						<th>Kota</th>
						<th>Status PKP</th>
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


	<div class="modal fade" id="browseBarangModal" tabindex="-1" role="dialog" aria-labelledby="browseBarangModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseBarangModalLabel">Cari Item</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bbarang">
				<thead>
					<tr>
						<th>Item#</th>
						<th>Nama</th>
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

	<div class="modal fade" id="browseSoModal" tabindex="-1" role="dialog" aria-labelledby="browseSoModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseSoModalLabel">Cari SO</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bso">
				<thead>
					<tr>
						<th>No Bukti</th>
						<th>Tgl</th>
						<th>Customer</th>
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

	<div class="modal fade" id="browsePpModal" tabindex="-1" role="dialog" aria-labelledby="browsePpModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browsePpModalLabel">Cari Bukti Pp</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bpp">
				<thead>
					<tr>
						<th>No Pp</th>
						<th>Tgl</th>
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


<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script> -->

<!-- tambahan untuk sweetalert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- tutupannya -->

<script>
	var idrow = 1;
	var baris = 1;

	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
	
    $(document).ready(function () {

		
		setTimeout(function(){

		$("#LOADX").hide();

		},500);

    idrow=<?=$no?>;
    baris=<?=$no?>;

    $('#KODES').select2({
		
		placeholder:'Pilih Suplier',
		allowClear: true,
        ajax: {
			url: '{{url('sup/browse')}}',
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
                        id: item.KODES, // The ID of the user
                        text: item.NAMAS2 // The text to display
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


		$tipx = $('#tipx').val();
		$golz = $('#golz').val();
		$searchx = $('#CARI').val();
		
		
        if (( $tipx == 'new' ) && ( $golz != 'C' ))
		{
			 baru();
			//  tambah();		 
		}


        if ( $tipx != 'new' )
		{
			 ganti();		

			    var initkode1 ="{{ $header->KODES }}";			 
			    var initcombo1 ="{{ $header->NAMAS }}";
		    	var defaultOption1 = { id: initkode1, text: initcombo1 }; // Set your default option ID and text
                var newOption1 = new Option(defaultOption1.text, defaultOption1.id, true, true);
                $('#KODES').append(newOption1).trigger('change');
			 
		}    
		
		$("#TTOTAL_QTY").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#TTOTAL").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#TDISK").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#TPPN").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#TDPP").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#NETT").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});


		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#QTY" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			
			$("#HARGA" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#PPNX" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#DPP" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#DISK" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});

			$("#TOTAL" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
		}	
		
        $('body').on('click', '.btn-delete', function() {
			var val = $(this).parents("tr").remove();
			baris--;
			hitung();
			nomor();
			
		});

		$('.date').datepicker({  
            dateFormat: 'dd-mm-yy'
		});
		
		
 	
		
//		CHOOSE Supplier
 		var dTableBSuplier;
		loadDataBSuplier = function(){
		
			$.ajax(
			{
				type: 'GET', 		
				url: '{{url('sup/browse')}}',
				// data: {
				// 	'GOL': 'Y',
				// },

				
			    beforeSend: function(){
					$("#LOADX").show();
				},


				success: function( response )
				{
					$("#LOADX").hide(); 

					resp = response;
					if(dTableBSuplier){
						dTableBSuplier.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBSuplier.row.add([
							'<a href="javascript:void(0);" onclick="chooseSuplier(\''+resp[i].KODES+'\',  \''+resp[i].NAMAS+'\', \''+resp[i].HARI+'\',  \''+resp[i].ALAMAT+'\', \''+resp[i].KOTA+'\', \''+resp[i].PKP+'\')">'+resp[i].KODES+'</a>',
							resp[i].NAMAS,
							resp[i].ALAMAT,
							resp[i].KOTA,
							resp[i].PKP2,
						]);
					}
					dTableBSuplier.draw();
				}
			});
		}
		
		dTableBSuplier = $("#table-bsuplier").DataTable({
			
		});
		
		browseSuplier = function(){
			loadDataBSuplier();
			$("#browseSuplierModal").modal("show");
		}
		
		chooseSuplier = function(KODES,NAMAS, HARI, ALAMAT, KOTA, PKP){
			$("#KODES").val(KODES);
			$("#NAMAS").val(NAMAS);
			$("#HARI").val(HARI);
			$("#ALAMAT").val(ALAMAT);
			$("#KOTA").val(KOTA);			
			$("#PKP").val(PKP);			
			$("#browseSuplierModal").modal("hide");
		
		}

	
		
		$("#KODES").keypress(function(e){

			if(e.keyCode == 46){
				 e.preventDefault();
				 browseSuplier();
			}
		}); 

		//////////////////////////////////////////////////////

		var dTableBBarang;
		var rowidBarang;
		loadDataBBarang = function(){
		
			$.ajax(
			{
				type: 'GET',    
				url: "{{url('vbrg/browse_beli')}}",
				async : false,
				data: {
						'KD_BRG': $("#KD_BRG"+rowidBarang).val(),
						//  PKP : $("#PKP").val(), 	
						// 'GOL': "{{$golz}}",			
					
				},
				success: function( response )

				{
					resp = response;
					
					
					if ( resp.length > 1 )
					{	
							if(dTableBBarang){
								dTableBBarang.clear();
							}
							for(i=0; i<resp.length; i++){
								
								dTableBBarang.row.add([
									'<a href="javascript:void(0);" onclick="chooseBarang(\''+resp[i].KD_BRG+'\', \''+resp[i].NA_BRG+'\' , \''+resp[i].SATUAN+'\' )">'+resp[i].KD_BRG+'</a>',
									resp[i].NA_BRG,
									resp[i].SATUAN,
								]);
							}
							dTableBBarang.draw();
					
					}
					else
					{
						$("#KD_BRG"+rowidBarang).val(resp[0].KD_BRG);
						$("#NA_BRG"+rowidBarang).val(resp[0].NA_BRG);
						$("#SATUAN"+rowidBarang).val(resp[0].SATUAN);
					}
				}
			});
		}
		
		dTableBBarang = $("#table-bbarang").DataTable({
			
		});

		browseBarang = function(rid){
			rowidBarang = rid;
			$("#NA_BRG"+rowidBarang).val("");			
			loadDataBBarang();
	
			
			if ( $("#NA_BRG"+rowidBarang).val() == '' ) {				
					$("#browseBarangModal").modal("show");
			}	
		}
		
		chooseBarang = function(KD_BRG,NA_BRG,SATUAN){
			$("#KD_BRG"+rowidBarang).val(KD_BRG);
			$("#NA_BRG"+rowidBarang).val(NA_BRG);	
			$("#SATUAN"+rowidBarang).val(SATUAN);
			$("#browseBarangModal").modal("hide");
		}
		
	////////////////////////////////////////////////////

	//////////////////////////////////////////////////////////////////

		// CHOOSE Pp
		var dTableBPp;
		loadDataBPp = function(){
		
			$.ajax(
			{
				type: 'GET', 		
				url: '{{url('pp/browse')}}',

				beforeSend: function(){
					$("#LOADX").show();
				},

				success: function( response )
				{
					$("#LOADX").hide();

					resp = response;
					if(dTableBPp){
						dTableBPp.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBPp.row.add([
							'<a href="javascript:void(0);" onclick="choosePp(\''+resp[i].NO_PP+'\' )">'+resp[i].NO_PP+'</a>',
							resp[i].TGL,
						]);
					}
					dTableBPp.draw();
				}
			});
		}
		
		dTableBPp = $("#table-bpp").DataTable({
			
		});
		
		browsePp = function(){
			loadDataBPp();
			$("#browsePpModal").modal("show");
		}
		
		choosePp = function( NO_PP){

			$("#NO_PP").val(NO_PP);
			$("#browsePpModal").modal("hide");
			
			getPpd(NO_PP);
		}
		
		$("#NO_PP").keypress(function(e){

			if(e.keyCode == 46){
				 e.preventDefault();
				 browsePp();
			}
		}); 

////////////////////////////////////////////////////////////////////


	//////////////////////////////////////////////////////////////////

	var dTableSo;
	var rowidSo;
	loadDataSo = function(){
		
		$.ajax(
		{
			type: 'GET',    
			url: "{{url('so/browse')}}",
			// data: {
			// 	'GOL': "{{$golz}}",
			// 	'NO_DO': $("#NO_DO").val(),
			// },

			beforeSend: function(){
					$("#LOADX").show();
				},

			success: function( response )
			{
				$("#LOADX").hide();

				resp = response;
				if(dTableSo){
					dTableSo.clear();
				}
				for(i=0; i<resp.length; i++){
					
					dTableSo.row.add([
						'<a href="javascript:void(0);" onclick="chooseSo(\''+resp[i].NO_BUKTI+'\' )">'+resp[i].NO_BUKTI+'</a>',
						resp[i].TGL,
						resp[i].NAMAC,
					]);
				}
				dTableSo.draw();
			}
		});
	}
	
	dTableSo = $("#table-bso").DataTable({

	});
	
	browseSo = function(rid){
		rowidSo = rid;
		loadDataSo();
		$("#browseSoModal").modal("show");
	}
	
	chooseSo = function(NO_BUKTI ){
		$("#NO_SO").val(NO_BUKTI);

		$("#browseSoModal").modal("hide");

		// if ( $("#PKP").val() == '1' )
		// {

		// 	document.getElementById("PKP").checked = true;
				
		// }

		// else
		// {
		// 	document.getElementById("PKP").checked = false;
			
		// }

		getSod(NO_BUKTI);
	}
	
	$("#NO_SO").keypress(function(e){
		if(e.keyCode == 46){
			e.preventDefault();
			browseSo();
		}
	}); 

	//////////////////////////////////////////////////////////////////

	function getSod(bukti)
	{
		
		var mulai = (idrow==baris) ? idrow-1 : idrow;

		$.ajax(
			{
				type: 'GET',    
				url: "{{url('so/browse_detail')}}",
				data: {
					nobukti: bukti,
				},
				success: function( resp )
				{
					var html = '';
					for(i=0; i<resp.length; i++){
						html+=`<tr>
                                    <td><input name='REC[]' id='REC${i}' value=${resp[i].REC+1} type='text' class='REC form-control' onkeypress='return tabE(this,event)' readonly></td>
                                    <td><input name='KD_BRG[]' data-rowid=${i} id='KD_BRG${i}' value="${resp[i].KD_BRG}" type='text' class='form-control KD_BRG' readonly></td>
                                    <td><input name='NA_BRG[]' data-rowid=${i} id='NA_BRG${i}' value="${resp[i].NA_BRG}" type='text' class='form-control  NA_BRG' readonly></td>
                                    <td><input name='SATUAN[]' data-rowid=${i} id='SATUAN${i}' value="${resp[i].SATUAN}" type='text' class='form-control  SATUAN' placeholder="Satuan"  readonly></td>
                                    
                                    <td>
										<input name='QTY[]' onclick='select()' onkeyup='hitung()' id='QTY${i}' value="${resp[i].QTY}" type='text' style='text-align: right' class='form-control QTY text-primary' readonly >
									
										<input name='HARGA[]' hidden onclick='select()' onkeyup='hitung()' id='HARGA${i}' value="0" type='text' style='text-align: right' class='form-control HARGA text-primary' readonly >
										<input name='TOTAL[]' hidden onclick='select()' onkeyup='hitung()' id='TOTAL${i}' value="0" type='text' style='text-align: right' class='form-control TOTAL text-primary' readonly >
										<input name='PPNX[]' hidden onclick='select()' onkeyup='hitung()' id='PPNX${i}' value="0" type='text' style='text-align: right' class='form-control PPNX text-primary' readonly >
										<input name='DPP[]' hidden onclick='select()' onkeyup='hitung()' id='DPP${i}' value="0" type='text' style='text-align: right' class='form-control DPP text-primary' readonly >
										<input name='DISK[]' hidden onclick='select()' onkeyup='hitung()' id='DISK${i}' value="0" type='text' style='text-align: right' class='form-control DISK text-primary' readonly >
									
									</td>
           																		
                                    <td>
										<input name='KET[]' id='KET${i}' value="${resp[i].KET}" type='text' class='form-control  KET' required>
									</td>
                                    <td><button type='button' class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button></td>
                                </tr>`;
					}
					$('#detailSod').html(html);

					$(".QTY").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".QTY").autoNumeric('update');

					$(".HARGA").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".HARGA").autoNumeric('update');

					$(".TOTAL").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".TOTAL").autoNumeric('update');

					$(".PPNX").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".PPNX").autoNumeric('update');

					$(".DPP").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".DPP").autoNumeric('update');

					$(".DISK").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".DISK").autoNumeric('update');


					idrow=resp.length;
					baris=resp.length;

					nomor();
					hitung();
				}
			});
	}

//////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////

	function getPpd(bukti)
	{
		
		var mulai = (idrow==baris) ? idrow-1 : idrow;

		$.ajax(
			{
				type: 'GET',    
				url: "{{url('pp/browse_ppd')}}",
				data: {
					nobukti: bukti,
				},
				success: function( resp )
				{
					var html = '';
					for(i=0; i<resp.length; i++){
						html+=`<tr>
                                    <td><input name='REC[]' id='REC${i}' value=${resp[i].REC+1} type='text' class='REC form-control' onkeypress='return tabE(this,event)' readonly></td>
                                    
									<td>
										<input name='KD_BRG[]' id='KD_BRG${i}' value="${resp[i].KD_BRG}" type='text' class='form-control KD_BRG' readonly>
						            </td>
						            <td>
						 			    <input name='NA_BRG[]' id='NA_BRG${i}' value="${resp[i].NA_BRG}" type='text' class='form-control  NA_BRG' readonly>
						            </td>
									<td>
										<input name='SATUAN[]' id='SATUAN${i}' value="${resp[i].SATUAN}" type='text' class='form-control  SATUAN' readonly>
									</td>
									<td>
										<input name='QTY[]' onclick='select()' onblur='hitung()' id='QTY${i}' value="${resp[i].QTY}" type='text' style='text-align: right' class='form-control QTY text-primary' readonly >
									</td>               
                                        																						
									<td {{( $golz =='C' ) ? 'hidden' : '' }}>
										<input name='HARGA[]'  onclick='select()' onblur='hitung()' value="0" id='HARGA{{$no}}' type='text' style='text-align: right'  class='form-control HARGA'>
									</td>

									<td {{( $golz =='C' ) ? 'hidden' : '' }}>
										<input name='TOTAL[]' onclick='select()' onblur='hitung()'  value="0" id='TOTAL{{$no}}' type='text' style='text-align: right'  class='form-control TOTAL' readonly>
					
										<input name='PPNX[]' hidden onblur='hitung()'  value="0" id='PPNX{{$no}}' type='text' style='text-align: right'  class='form-control PPNX' readonly>
										<input name='DPP[]'  hidden onblur='hitung()'  value="0" id='DPP{{$no}}' type='text' style='text-align: right'  class='form-control DPP' readonly>
									</td>
									<td {{( $golz =='C' ) ? 'hidden' : '' }}>
										<input name='DISK[]' onclick='select()' onblur='hitung()'  value="0"  id='DISK{{$no}}' type='text' style='text-align: right'  class='form-control DISK' >
									</td> 
									<td>
										<input name='KET[]' id='KET{{$no}}' type='text' class='form-control KET' value=""  >
									</td>   
				

									<td><button hidden type='button' class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button></td>
                                </tr>`;
					}
					$('#detailPpd').html(html);

					$(".QTY").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".QTY").autoNumeric('update');
					
					$(".HARGA").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".HARGA").autoNumeric('update');
					
					$(".TOTAL").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".TOTAL").autoNumeric('update');
					
					$(".PPNX").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".PPNX").autoNumeric('update');
					
					$(".DPP").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".DPP").autoNumeric('update');
					
					$(".DISK").autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
					$(".DISK").autoNumeric('update');
					
					idrow=resp.length;
					baris=resp.length;

					nomor();
					hitung();
				}
			});
	}

//////////////////////////////////////////////////////////////////

	});



///////////////////////////////////////		
    



	function cekDetail(){
		var cekBarang = '';
		$(".KD_BRG").each(function() {
			
			let z = $(this).closest('tr');
			var KD_BRGX = z.find('.KD_BRG').val();
			
			if( KD_BRGX =="" )
			{
					cekBarang = '1';
					
			}	
		});
		
		return cekBarang;
	}


 	function simpan() {
		hitung();
		
		var tgl = $('#TGL').val();
		var bulanPer = {{session()->get('periode')['bulan']}};
		var tahunPer = {{session()->get('periode')['tahun']}};
		
        var check = '0';

			// if ( $('#GUDANG').val()=='' ) 
            // {				
			//     check = '1';
			// 	Swal.fire({
			// 		icon: 'warning',
			// 		title: 'Warning',
			// 		text: 'Gudang# Harus Dipilih.'
			// 	});
			// 	return; // Stop function execution
			// }

			if ( $('#NAMAS').val()=='' ) 
            {				
			    check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Supplier# Harus Diisi.'
				});
				return; // Stop function execution
			}
			

			if ( $('#KD_BRG').val()=='' ) 
            {				
			    check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Barang# Harus Diisi.'
				});
				return; // Stop function execution
			}
			
			if (baris==0)
			{
				check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Data detail kosong (Tambahkan 1 baris kosong jika ingin mengosongi detail)'
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
		var TTOTAL_QTY = 0;
		var TTOTAL = 0;
		var TDISK = 0;
		var TDPPX = 0;
		var TPPNX = 0;
		var NETTX = 0;

		
		$(".QTY").each(function() {
			
			let z = $(this).closest('tr');
			var QTYX = parseFloat(z.find('.QTY').val().replace(/,/g, ''));
			var HARGAX = parseFloat(z.find('.HARGA').val().replace(/,/g, ''));
			var PPNX = parseFloat(z.find('.PPNX').val().replace(/,/g, ''));
			var DISKX = parseFloat(z.find('.DISK').val().replace(/,/g, ''));

	     	z.find('.DISK').val(DISKX);
	     		
			var PKPX = $('#PKP').val();

/////////////////////////////////////////////////////////////////////////

			// if( DISKX < 100 )

			// {
			// 	var DISKXX = ( QTYX * HARGAX) * DISKX / 100 ;

			// }
			
			// z.find('.DISK').val(DISKXX);


///////////////////////////////////////////////////////////////////////////////////////
    
            
            var TOTALX  =  ( QTYX * HARGAX ) - DISKX;
            
			z.find('.TOTAL').val(TOTALX);


			var DPPX = 0 ;
			var PPNX = 0;
			
            DPPX = TOTALX;
	     	z.find('.DPP').val(DPPX);

			if (PKPX == '0' ) {
			    PPNX = 0;
			    
			} 

	     		
			if (PKPX == '1' ) {
			    DPPX = TOTALX * 100/111;
			    PPNX = TOTALX - DPPX;
	     	    z.find('.DPP').val(DPPX);
	     	
			} 


            
			z.find('.PPNX').val(PPNX);	

		    z.find('.HARGA').autoNumeric('update');			
		    z.find('.QTY').autoNumeric('update');	
		    z.find('.TOTAL').autoNumeric('update');				
		    z.find('.DPP').autoNumeric('update');			
		    z.find('.DISK').autoNumeric('update');			
		    z.find('.PPNX').autoNumeric('update');		

            TTOTAL_QTY +=QTYX;		
            TTOTAL +=TOTALX;				
            TPPNX +=PPNX;
            TDPPX +=DPPX;
            
            TDISK +=DISKX;				
		
		});

		
		NETTX = TDPPX + TPPNX ;
		
		if(isNaN(TTOTAL_QTY)) TTOTAL_QTY = 0;

		$('#TTOTAL_QTY').val(numberWithCommas(TTOTAL_QTY));		
		$("#TTOTAL_QTY").autoNumeric('update');
		
		if(isNaN(TTOTAL)) TTOTAL = 0;

		$('#TTOTAL').val(numberWithCommas(TTOTAL));		
		$("#TTOTAL").autoNumeric('update');

		if(isNaN(TDISK)) TDISK = 0;

		$('#TDISK').val(numberWithCommas(TDISK));		
		$("#TDISK").autoNumeric('update');


		$('#TDPP').val(numberWithCommas(TDPPX));		
		$("#TDPP").autoNumeric('update');
		
		$('#TPPN').val(numberWithCommas(TPPNX));		
		$("#TPPN").autoNumeric('update');

		$('#NETT').val(numberWithCommas(NETTX));		
		$("#NETT").autoNumeric('update');

		
	}
	

	
  
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
	
 
	function jtempo() {

		    
		$.ajax(
		{
			type: 'GET',    
			url: "{{url('po/jtempo')}}",
			async : false,
			data: {
					'TGL' : $("#TGL").val(),
					'HARI' : $("#HARI").val(),
			},
			success: function( response )

			{
				resp = response;
				$("#JTEMPO").val( resp );
				
			}
		});

	}
	
	function ambil_hari() {

		    
		$.ajax(
		{
			type: 'GET',    
			url: "{{url('sup/browse_hari')}}",
			data: {
					'KODES' : $("#KODES").val(),
			},
			
			success: function( response )

			{
				resp = response;
				$("#NAMAS").val( resp[0].NAMAS );
				$("#PKP").val( resp[0].PKP );
				$("#HARI").val( resp[0].HARI );
	
        		if ( $("#PKP").val() == '1' )
        		{

                     document.getElementById("PKP").checked = true;
                    	
        		}
        
                else
                {
                     document.getElementById("PKP").checked = false;
                    
                }
        				
			}
		});
		
		   
	
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
			$("#JTEMPO").attr("readonly", false);
	    	$("#KODES").attr("disabled", false);
	        $("#PKP").attr("disabled", true);
	        $("#ZPKP").attr("disabled", true);
			
			$("#NOTES").attr("readonly", false);
    		$("#NOTES").attr("disabled", false);				

		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#KD_BHN" + i.toString()).attr("readonly", false);
			$("#KD_BRG" + i.toString()).attr("readonly", false);
			$("#NA_BHN" + i.toString()).attr("readonly", true);
			$("#NA_BRG" + i.toString()).attr("readonly", true);
			$("#SATUAN" + i.toString()).attr("readonly", true);
			$("#QTY" + i.toString()).attr("readonly", false);
			$("#HARGA" + i.toString()).attr("readonly", false);
			$("#TOTAL" + i.toString()).attr("readonly", true);
			$("#DISK" + i.toString()).attr("readonly", false);
			$("#KET" + i.toString()).attr("readonly", false);
			$("#DELETEX" + i.toString()).attr("hidden", false);

			$tipx = $('#tipx').val();
			// $posted = $('#POSTED').val();
		
			
			// if ( $posted == '1' )
			// {
			// 	$("#REC" + i.toString()).attr("readonly", true);
			// 	$("#KD_BRG" + i.toString()).attr("readonly", true);
			// 	$("#NA_BRG" + i.toString()).attr("readonly", true);
			// 	$("#SATUAN" + i.toString()).attr("readonly", true);
			// 	$("#QTY" + i.toString()).attr("readonly", true);
			// 	$("#KET" + i.toString()).attr("readonly", true);
			// 	$("#DELETEX" + i.toString()).attr("hidden", true);
			// } 
			
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
		
		$("#NO_BUKTI").attr("readonly", true);	
		
		$("#TGL").attr("readonly", true);
		$("#JTEMPO").attr("readonly", true);
		$("#KODES").attr("disabled", true);
		$("#NOTES").attr("readonly", true);
		$("#NOTES").attr("disabled", true);
		
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
			$("#DISK" + i.toString()).attr("readonly", true);
			$("#KET" + i.toString()).attr("readonly", true);
			
			$("#DELETEX" + i.toString()).attr("hidden", true);
		}


		
	}


	function kosong() {
				
		 $('#NO_BUKTI').val("+");		
		 $('#KODES').val("");	
		 $('#NAMAS').val("");	
		 $('#ALAMAT').val("");	
		 $('#KOTA').val("");	
		 $('#NOTES').val("");	
		 $('#TTOTAL_QTY').val("0.00");	
		 $('#TTOTAL').val("0.00");
		 $('#TDISK').val("0.00");
		 $('#TPPN').val("0.00")
		 $('#TDPP').val("0.00")
		 $('#NETT').val("0.00")
		 
		//  $('#PKP').val("0")
		 $('#HARI').val("0")
		 
		var html = '';
		$('#detailx').html(html);	
		
	}

	
	// sweetalert untuk tombol hapus dan close
	
	function hapusTrans() {
		let text = "Hapus Transaksi "+$('#NO_BUKTI').val()+"?";

		var loc ='';
		var flagz = "{{ $flagz }}";
		var golz = "{{ $golz }}";
		
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
	            	loc = "{{ url('/po/delete/'.$header->NO_ID) }}" + '?flagz=' + encodeURIComponent(flagz) + 
						  '&golz=' + encodeURIComponent(golz) ;

		            // alert(loc);
	            	window.location = loc;
		
				});
			}
		});
	}
	
	function closeTrans() {
		console.log("masuk");
		var loc ='';
		var flagz = "{{ $flagz }}";
		var golz = "{{ $golz }}";
		
		Swal.fire({
			title: 'Are you sure?',
			text: 'Do you really want to close this page? Unsaved changes will be lost.',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes, close it',
			cancelButtonText: 'No, stay here'
		}).then((result) => {
			if (result.isConfirmed) {
	        	loc = "{{ url('/po/') }}" + '?flagz=' + encodeURIComponent(flagz) + '&golz=' + encodeURIComponent(golz) ;
				window.location = loc ;
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
		var golz = "{{ $golz }}";
		var cari = $("#CARI").val();
		var loc = "{{ url('/po/edit/') }}" + '?idx={{ $header->NO_ID}}&tipx=search&flagz=' + encodeURIComponent(flagz) + '&golz=' + encodeURIComponent(golz) + '&buktix=' +encodeURIComponent(cari);
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
		            <input name='QTY[]' onclick='select()' onblur='hitung()' value='0' id='QTY${idrow}' type='text' style='text-align: right' class='form-control QTY text-primary' required >
                </td>

				<td {{( $golz =='C' ) ? 'hidden' : '' }}>
		            <input name='HARGA[]' onclick='select()' onblur='hitung()' value='0' id='HARGA${idrow}' type='text' style='text-align: right' class='form-control HARGA text-primary' required >
                </td>

				
				<td {{( $golz =='C' ) ? 'hidden' : '' }}>
		            <input name='TOTAL[]' onclick='select()' onblur='hitung()' value='0' id='TOTAL${idrow}' type='text' style='text-align: right' class='form-control TOTAL text-primary' readonly required >

		            <input name='PPNX[]'  hidden onblur='hitung()' value='0' id='PPNX${idrow}' type='text' style='text-align: right' class='form-control PPNX text-primary' readonly required >
					<input name='DPP[]'  hidden onblur='hitung()' value='0' id='DPP${idrow}' type='text' style='text-align: right' class='form-control DPP text-primary' readonly required >
				</td>

				<td {{( $golz =='C' ) ? 'hidden' : '' }}>
					<input name='DISK[]'  onclick='select()' onblur='hitung()' value='0' id='DISK${idrow}' type='text' style='text-align: right' class='form-control DISK text-primary' required >
				</td>
				<td>
					<input name='KET[]' id='KET${idrow}' type='text' class='form-control  KET' required>
                </td>
				
                <td>
					<button type='button' id='DELETEX${idrow}'  class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
                </td>				
         </tr>`;
				
        x.innerHTML = html;
        var html='';
		
		
		
		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#QTY" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});


			$("#HARGA" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});

			$("#TOTAL" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});		
			
			$("#DPP" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});	
			
			$("#DISK" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});
			
			$("#PPNX" + i.toString()).autoNumeric('init', {
				aSign: '<?php echo ''; ?>',
				vMin: '-999999999.99'
			});	 

					
		}

		// $("#KD_BHN"+idrow).keypress(function(e){
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
<!-- 
<script src="autonumeric.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.5.4"></script>
<script src="https://unpkg.com/autonumeric"></script> -->
@endsection