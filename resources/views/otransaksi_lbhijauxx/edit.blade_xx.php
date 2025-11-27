@extends('layouts.plain')

<style>
    .card {

    }

    .form-control:focus {
        background-color: #b5e5f9 !important;
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

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dropdown with Select2</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
</head>


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

                    <form action="{{($tipx=='new')? url('/lbhijau/store?flagz='.$flagz.'&golz='.$golz.'') : url('/lbhijau/update/'.$header->NO_ID.'&flagz='.$flagz.'&golz='.$golz.'' ) }}" method="POST" name ="entri" id="entri" >
  
                        @csrf
                        <div class="tab-content mt-3">
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

                            <div class="form-group row">
                                {{-- <div class="col-md-1" align="right">
                                    <label for="NO_BUKTI" class="form-label">Bukti#</label>
                                </div> --}}
								

                                   <input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
                                    placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

									<input name="tipx" class="form-control tipx" id="tipx" value="{{$tipx}}" hidden>
									<input name="flagz" class="form-control flagz" id="flagz" value="{{$flagz}}" hidden>
									<input name="golz" class="form-control golz" id="golz" value="{{$golz}}" hidden>

								
								
                                {{-- <div class="col-md-2">
                                    <input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI"
                                    placeholder="Masukkan Bukti#" value="{{$header->NO_BUKTI}}" readonly>
                                </div> --}}

								<!-- code text box baru -->
								<div class="col-md-2 form-group row special-input-label">
									<input type="text" class="NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI" 
									value="{{$header->NO_BUKTI}}" placeholder=" " readonly>
									<label for="NO_BUKTI">Bukti#</label>
								</div>
								<!-- tutupannya -->

								{{-- <div class="col-md-3" align="right">
									<label style="color:red">*</label>									
                                    <label for="KODES" class="form-label">Suplier</label>
                                </div>
                               	<div class="col-md-2 input-group" >
                                  <input type="text" class="form-control KODES" id="KODES" name="KODES" placeholder="Pilih Suplier"value="{{$header->KODES}}" style="text-align: left" readonly >
                                </div> --}}

								<div class="col-md-3"></div>

								<!-- code text box baru -->
								<div class="col-md-3 form-group row special-input-label">
									<input type="text" class="KODES" id="KODES" name="KODES" 
										value="{{$header->KODES}}" placeholder=" " readonly>
									<label for="KODES">Suplier* (*pilih suplier)</label>
								</div>
								<!-- tutupannya -->
							</div>

							<div class="form-group row">
                                {{-- <div class="col-md-1" align="right">
                                    <label for="TGL" class="form-label">Tgl</label>
                                </div>
                                <div class="col-md-2">
								  <input class="form-control date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}">
                                </div> --}}

								<!-- code text box baru -->
								<div class="col-md-2 form-group row special-input-label">
									<input class="date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}">
									<label for="TGL">Tgl</label>
								</div>
								<!-- tutupannya -->

								<div class="col-md-3"></div>

								{{-- <div class="col-md-2 input-group" >
									<input type="text" class="form-control NAMAS" id="NAMAS" name="NAMAS" placeholder="Pilih Suplier"value="{{$header->NAMAS}}" style="text-align: left" readonly >
									<button type="button" class="btn btn-primary" onclick="browseSuplier()"><i class="fa fa-search"></i></button>
								</div> --}}
								<!-- code text box baru -->
								<div class="col-md-3 form-group row special-input-label">
									<input type="text" class="NAMAS" id="NAMAS" name="NAMAS" 
										value="{{$header->NAMAS}}" placeholder=" " readonly>
									<label for="NAMAS"></label>
								</div>
								<div class="col-md-3 form-group row special-input-label">
									<button type="button" class="btn btn-primary" onclick="browseSuplier()"><i class="fa fa-search"></i></button>
									<label for="NAMAS"></label>
								</div>
								<!-- tutupannya -->
							</div>


							{{-- <div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="JTEMPO" class="form-label">Jatuh Tempo</label>
								</div>
								<div class="col-md-2">
									<input class="form-control date" id="JTEMPO" name="JTEMPO" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->JTEMPO))}}">
								</div> 
								
								
                            </div> --}}
							

                            {{-- <div class="form-group row">

								<div class="col-md-1" align="right">
									<label style="color:red">*</label>									
                                    <label for="KODES" class="form-label">Suplier</label>
                                </div>
                               	<div class="col-md-2 input-group" >
                                  <input type="text" class="form-control KODES" id="KODES" name="KODES" placeholder="Pilih Suplier"value="{{$header->KODES}}" style="text-align: left" readonly >
        						  <button type="button" class="btn btn-primary" onclick="browseSuplier()"><i class="fa fa-search"></i></button>
                                </div>
                            </div> --}}
							

							{{-- <div class="form-group row">


								<div class="col-md-1" align="left">
                                    <label for="NAMAS" class="form-label"></label>
                                </div>
								<div class="col-md-4">
                                    <input type="text" class="form-control NAMAS" id="NAMAS" name="NAMAS" placeholder="-" 
									value="{{$header->NAMAS}}" readonly>
                                </div>

								
                            </div> --}}

							
							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="TYPE" class="form-label">Type</label>
								  </div>
								  <div class="col-md-2">
											<!-- <input type="text" class="form-control BNK" id="BNK" name="BNK" placeholder="Masukkan Bnk"> -->
											  <select id="TYPE" class="form-control"  name="TYPE">
												<option value="-" {{ ( $header->TYPE== '-') ? 'selected' : '' }} disable selected hidden>--Pilih Tipe--</option>
												<option value="C" {{ ( $header->TYPE== 'C') ? 'selected' : '' }}>CASH</option>
												<option value="H" {{ ( $header->TYPE== 'H') ? 'selected' : '' }}>HIJAU</option>
												<option value="V" {{ ( $header->TYPE== 'V') ? 'selected' : '' }}>VARIAN</option>
												<option value="B" {{ ( $header->TYPE== 'B') ? 'selected' : '' }}>BANK</option>
											  </select>
								</div>

								<div class="col-md-1" align="right">
									<label for="BANK" class="form-label">Bank</label>
								  </div>
								  <div class="col-md-2">
											<!-- <input type="text" class="form-control BNK" id="BNK" name="BNK" placeholder="Masukkan Bnk"> -->
											  <select id="BANK" class="form-control"  name="BANK">
												<option value="-" {{ ( $header->BANK== '-') ? 'selected' : '' }} disable selected hidden>--Pilih Bank--</option>
												<option value="BCA1" {{ ( $header->BANK== 'BCA1') ? 'selected' : '' }}>KREDIT BCA</option>
												<option value="BCA2" {{ ( $header->BANK== 'BCA2') ? 'selected' : '' }}>DEBET BCA</option>
												<option value="BNI" {{ ( $header->BANK== 'BNI') ? 'selected' : '' }}>DEBET B.N.I</option>
												<option value="VISA" {{ ( $header->BANK== 'VISA') ? 'selected' : '' }}>VISA CARD</option>
												<option value="MASTER" {{ ( $header->BANK== 'MASTER') ? 'selected' : '' }}>MASTER CARD</option>
												<option value="DINNERS" {{ ( $header->BANK== 'DINNERS') ? 'selected' : '' }}>DINNERS CARD</option>
												<option value="BCA3" {{ ( $header->BANK== 'BCA3') ? 'selected' : '' }}>BCA EVERYDAY</option>
												<option value="BCA4" {{ ( $header->BANK== 'BCA4') ? 'selected' : '' }}>BCA NON EVERYDAY</option>
											  </select>
								</div>

								<div class="col-md-1"></div>

								<!-- code text box baru -->
								<div class="col-md-2 form-group row special-input-label">
									<input class="date" id="TG_MULAI" name="TG_MULAI" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TG_MULAI))}}">
									<label for="TG_MULAI">Mulai Dari</label>
								</div>
								<!-- tutupannya -->

								<!-- code text box baru -->
								<div class="col-md-2 form-group row special-input-label">
									<input class="date" id="TG_AKHIR" name="TG_AKHIR" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TG_AKHIR))}}">
									<label for="TG_AKHIR">Sampai Dengan</label>
								</div>
								<!-- tutupannya -->
							</div>
							
							<div class="form-group row">
								<!-- code text box baru -->
								<div class="col-md-2 form-group row special-input-label">
									<input type="text" class="QTY_BELI" id="QTY_BELI" name="QTY_BELI" 
										value="{{$header->QTY_BELI}}" placeholder=" ">
									<label for="QTY_BELI">Jumlah Beli</label>
								</div>
								<!-- tutupannya -->

								<div class="col-md-1"></div>

								<div class="col-md-1" align="right">
									<label for="JNS" class="form-label">Hubungan</label>
								</div>
								<div class="col-md-2">
									<select id="JNS" class="form-control"  name="JNS">
										<option value="-" {{ ( $header->JNS== '-') ? 'selected' : '' }} disable selected hidden>--Pilih Jenis--</option>
										<option value="DAN" {{ ( $header->JNS== 'DAN') ? 'selected' : '' }}>DAN</option>
										<option value="ATAU" {{ ( $header->JNS== 'ATAU') ? 'selected' : '' }}>ATAU</option>
									</select>
								</div>

								<div class="col-md-1"></div>

								<!-- code text box baru -->
								<div class="col-md-2 form-group row special-input-label">
									{{-- <input class="time" id="JM_MULAI" name="JM_MULAI" type="time" autocomplete="off" value="00:00:00"> --}}
									<input class="time" id="JM_MULAI" name="JM_MULAI" type="time" autocomplete="off" value="00:00:00">
									<label for="JM_MULAI">Jam</label>
								</div>
								<!-- tutupannya -->
								<!-- code text box baru -->
								<div class="col-md-2 form-group row special-input-label">
									{{-- <input class="time" id="JM_MULAI" name="JM_MULAI" type="time" autocomplete="off" value="00:00:00"> --}}
									<input class="time" id="JM_AKHIR" name="JM_AKHIR" type="time" autocomplete="off" value="00:00:00">
									<label for="JM_AKHIR">Sampai Dengan</label>
								</div>
								<!-- tutupannya -->

							</div>

							<div class="form-group row">
								<!-- code text box baru -->
								<div class="col-md-2 form-group row special-input-label">
									<input type="text" class="RP_BELI" id="RP_BELI" name="RP_BELI" 
										value="{{$header->RP_BELI}}" placeholder=" ">
									<label for="RP_BELI">Total Beli</label>
								</div>
								<!-- tutupannya -->

								<div class="col-md-5"></div>

								<div class="col-md-1" align="right">
									<label for="CBG" class="form-label">Cabang :</label>
								</div>

								<div class="col-md-1"align="right">
									<input type="checkbox" class="form-check-input" id="TGZ" name="TGZ" value="1" {{ ($header->TGZ == 1) ? 'checked' : '' }}>
									<label for="TGZ">TGZ</label>
								</div>

								<div class="col-md-1"align="right">
									<input type="checkbox" class="form-check-input" id="TMM" name="TMM" value="1" {{ ($header->TMM == 1) ? 'checked' : '' }}>
									<label for="TMM">TMM</label>
								</div>

								<div class="col-md-1"align="right">
									<input type="checkbox" class="form-check-input" id="SOP" name="SOP" value="1" {{ ($header->SOP == 1) ? 'checked' : '' }}>
									<label for="SOP">SOP</label>
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="JNS" class="form-label">Kondisi</label>
								</div>
								<div class="col-md-2">
									<select id="JNS" class="form-control"  name="JNS">
										<option value="-" {{ ( $header->JNS== '-') ? 'selected' : '' }} disable selected hidden>--Pilih Jenis--</option>
										<option value="D" {{ ( $header->JNS== 'D') ? 'selected' : '' }}>BELANJA BARANG PROMO</option>
										<option value="A" {{ ( $header->JNS== 'A') ? 'selected' : '' }}>TOTAL SEMUA BELANJA</option>
									</select>
								</div>

								<!-- code text box baru -->
								<div class="col-md-2 form-group row special-input-label">
									<input type="text" class="RP_BELI_MAX" id="RP_BELI_MAX" name="RP_BELI_MAX" 
										value="{{$header->RP_BELI_MAX}}" placeholder=" ">
									<label for="RP_BELI_MAX">MAX</label>
								</div>
								<!-- tutupannya -->
							</div>

							<div class="form-group row">
								<div class="col-md-1"></div>
								<div class="col-md-2"align="right">
									<input type="checkbox" class="form-check-input" id="KELIPATAN" name="KELIPATAN" value="1" {{ ($header->KELIPATAN == 1) ? 'checked' : '' }}>
									<label for="KELIPATAN">Berlaku Kelipatan</label>
								</div>
							</div>
							
							<div class="form-group row">
								<!-- code text box baru -->
								<div class="col-md-6 form-group row special-input-label">
									<input type="text" class="KET" id="KET" name="KET" 
										value="{{$header->KET}}" placeholder=" ">
									<label for="KET">Keterangan</label>
								</div>
								<!-- tutupannya -->
							</div>


                        <div class="tab-content mt-3">
							
                            <table id="datatable" class="table table-striped table-border">
                                <thead>
                                    <tr>
										<th width="100px" style="text-align:center">No.</th>
	
										<th width="100px" style="text-align:center">Qty</th> 

										<th {{( $golz =='B') ? '' : 'hidden' }} width="150px">
                                            <label style="color:red;font-size:20px; text-align:center">*</label>
                                            <label for="KD_BRG" class="form-label">Kode</label>
                                        </th>
										<th {{( $golz =='B') ? '' : 'hidden' }} width="600px" style="text-align:center">Nama Hadiah / Uraian</th>

										{{-- <th {{( $golz =='J' || $golz =='N') ? '' : 'hidden' }} width="100px">
                                            <label style="color:red;font-size:20px">*</label>
                                            <label for="KD_BRG" class="form-label">Barang</label>
                                        </th> --}}
										<th {{( $golz =='J' || $golz =='N') ? '' : 'hidden' }} width="200px" style="text-align:center">Nama Hadiah / Uraian</th>

                                        {{-- <th width="200px" style="text-align:center">Satuan</th>
                                        <th width="200px" style="text-align:center">Harga</th>
               
                                        <th width="200px" style="text-align:center">Total</th>
                                        <th width="200px" style="text-align:center">Ket</th>

                                        <th></th> --}}
                                       						
                                    </tr>
                                </thead>
        
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
										    <input name="QTY[]"  onclick="select()" onblur="hitung()" value="{{$detail->QTY}}" id="QTY{{$no}}" type="text" style="text-align: right"  class="form-control QTY" >
										</td> 
										<td {{( $golz =='Y') ? '' : 'hidden' }}>
                                            <input name="KD_BRG[]" id="KD_BRG{{$no}}" type="text" value="{{$detail->KD_BRG}}"
                                              class="form-control KD_BRG "  onblur="browseBahan({{$no}})" >
										</td>
                                        <td {{( $golz =='B') ? '' : 'hidden' }}>
                                            <input name="NA_BRG[]" id="NA_BRG{{$no}}" type="text" class="form-control KD_BRG" value="{{$detail->NA_BRG}}" readonly required>
                                        </td>

										{{-- <td {{( $golz =='J' || $golz =='N') ? '' : 'hidden' }}>
                                            <input name="KD_BRG[]" id="KD_BRG{{$no}}" type="text" class="form-control KD_BRG " 
											value="{{$detail->KD_BRG}}" onblur="browseBarang({{$no}})">
                                        </td>

										<td {{( $golz =='J' || $golz =='N') ? '' : 'hidden' }}>
                                            <input name="NA_BRG[]" id="NA_BRG{{$no}}" type="text" class="form-control NA_BRG " value="{{$detail->NA_BRG}}">
                                        </td>
                                        <td>
                                            <input name="SATUAN[]" id="SATUAN{{$no}}" type="text" class="form-control SATUAN" value="{{$detail->SATUAN}}" readonly required>
                                        </td>										
										                        
                                        																						
										<td>
										    <input name="HARGA[]"  onclick="select()" onblur="hitung()" value="{{$detail->HARGA}}" id="HARGA{{$no}}" type="text" style="text-align: right"  class="form-control HARGA">
										</td>
	
										<td>
										    <input name="TOTAL[]" onclick="select()" onblur="hitung()"  value="{{$detail->TOTAL}}" id="TOTAL{{$no}}" type="text" style="text-align: right"  class="form-control TOTAL" readonly>
										</td>

										 
                                        <td>
                                          <input name="KET[]" id="KET{{$no}}" type="text" class="form-control KET" value="{{$detail->KET}}"  >
                                        </td>    --}}
				
										<td>
											<button type='button' id='DELETEX{{$no}}'  class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
										</td> 

                                    </tr>
								
								<?php $no++; ?>
								@endforeach
                                </tbody>

								<tfoot>
                                    <td></td>
									<td {{( $golz =='B') ? '' : 'hidden' }}></td>
									<td {{( $golz =='B') ? '' : 'hidden' }}></td>
									<td {{( $golz =='J' || $golz =='N') ? '' : 'hidden' }}></td>
									<td {{( $golz =='J' || $golz =='N') ? '' : 'hidden' }}></td>
									<td></td>									
                                    {{-- <td><input class="form-control TTOTAL_QTY  text-primary" style="text-align: right"  id="TTOTAL_QTY" name="TTOTAL_QTY" value="{{$header->TOTAL_QTY}}" readonly></td> --}}
                                    <td></td>
									<!-- <td><input class="form-control TTOTAL  text-primary" style="text-align: right"  id="TTOTAL" name="TTOTAL" value="{{$header->TOTAL}}" readonly></td> -->
                                    <td></td>
                                </tfoot>
                            </table>
							
                            <div class="col-md-2 row">
                               <a type="button" id='PLUSX' onclick="tambah()" class="fas fa-plus fa-sm md-3" style="font-size: 20px" ></a>
					
							</div>		
						
					</div>
                        </div> 

                        <hr style="margin-top: 30px; margin-buttom: 30px">
						<!-- dari sini shelvi-->

						{{-- <div class="tab-content mt-6">
						
							<div class="form-group row">
                                <div class="col-md-8" align="right">
                                    <label for="TTOTAL" class="form-label">Ttotal</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text"  onclick="select()" onkeyup="hitung()" class="form-control TTOTAL" id="TTOTAL" name="TTOTAL" placeholder="" value="{{$header->TOTAL}}" style="text-align: right" readonly>
                                </div>
							</div>


							
						</div> --}}
						
						<!-- sampai sini shelvi-->
						   
						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button type="button" id='TOPX'  onclick="location.href='{{url('/lbhijau/edit/?idx=' .$idx. '&tipx=top&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-primary">Top</button>
								<button type="button" id='PREVX' onclick="location.href='{{url('/lbhijau/edit/?idx='.$header->NO_ID.'&tipx=prev&flagz='.$flagz.'&golz='.$golz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Prev</button>
								<button type="button" id='NEXTX' onclick="location.href='{{url('/lbhijau/edit/?idx='.$header->NO_ID.'&tipx=next&flagz='.$flagz.'&golz='.$golz.'&buktix='.$header->NO_BUKTI )}}'" class="btn btn-outline-primary">Next</button>
								<button type="button" id='BOTTOMX' onclick="location.href='{{url('/lbhijau/edit/?idx=' .$idx. '&tipx=bottom&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button type="button" id='NEWX' onclick="location.href='{{url('/lbhijau/edit/?idx=0&tipx=new&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-warning">New</button>
								<button type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button type="button" id='UNDOX' onclick="location.href='{{url('/lbhijau/edit/?idx=' .$idx. '&tipx=undo&flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-info">Undo</button>  
								<button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button type="button" id='HAPUSX'  onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								<button type="button" id='CLOSEX'  onclick="location.href='{{url('/lbhijau?flagz='.$flagz.'&golz='.$golz.'' )}}'" class="btn btn-outline-secondary">Close</button>
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


	<div class="modal fade" id="browseBahanModal" tabindex="-1" role="dialog" aria-labelledby="browseBahanModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="browseBahanModalLabel">Cari Item</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<table class="table table-stripped table-bordered" id="table-bbahan">
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
	
	
	

@endsection

@section('footer-scripts')
<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script> -->

<script>
	var idrow = 1;
	var baris = 1;

	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
	
    $(document).ready(function () {
    idrow=<?=$no?>;
    baris=<?=$no?>;

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
		$searchx = $('#CARI').val();
		
		
        if ( $tipx == 'new' )
		{
			 baru();
             tambah();				 
		}

        if ( $tipx != 'new' )
		{
			 ganti();			
		}    
		
		$("#TTOTAL_QTY").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#TTOTAL").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		// $("#PPN").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});
		$("#NETT").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});


		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#QTY" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			
			$("#HARGA" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			// $("#PPNX" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			// $("#DPP" + i.toStri?ng()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});

			$("#TOTAL" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
		}	


		
		// $('#supxz').select2({
        //     minimumInputLength:2,
        //     placeholder:'Select Suplier',
        //     ajax:{
        //         url:route('sup/browsesupz'),
        //         dataType:'json',
        //         processResults:data=>{
                    
        //             return {
        //                 results:data.map(res=>{
        //                     return {text:res.NAMAS,id:res.KODES}
        //                 })
        //             }
        //         }
        //     }
        // })
		
		
				
		
        $('body').on('click', '.btn-delete', function() {
			var val = $(this).parents("tr").remove();
			baris--;
			hitung();
			nomor();
			
			
		});

		$('.date').datepicker({  
            dateFormat: 'dd-mm-yy'
		});

		$(".time").timepicker({  
            timeFormat: "HH:mm:ss",
			interval: 15,
			dynamic: false,
			dropdown: true,
			scrollbar: true
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
				success: function( response )
				{
					resp = response;
					if(dTableBSuplier){
						dTableBSuplier.clear();
					}
					for(i=0; i<resp.length; i++){
						
						dTableBSuplier.row.add([
							'<a href="javascript:void(0);" onclick="chooseSuplier(\''+resp[i].KODES+'\',  \''+resp[i].NAMAS+'\', \''+resp[i].ALAMAT+'\', \''+resp[i].KOTA+'\', \''+resp[i].PKP+'\')">'+resp[i].KODES+'</a>',
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
		
		chooseSuplier = function(KODES,NAMAS, ALAMAT, KOTA, PKP){
			$("#KODES").val(KODES);
			$("#NAMAS").val(NAMAS);
			$("#ALAMAT").val(ALAMAT);
			$("#KOTA").val(KOTA);			
			$("#PKP").val(PKP);			
			$("#browseSuplierModal").modal("hide");
		}

		var PKP=$("#PKP").val();	
		
		if (PKP == 1 ) 
		{
		$("#PKP").prop('checked', true)
		} 
		else 
		{
		$("#PKP").prop('checked', false)
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
				url: "{{url('brg/browse')}}",
				async : false,
				data: {
						'KD_BRG': $("#KD_BRG"+rowidBarang).val(),	
						'GOL': "{{$golz}}",			
					
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
		
		
		/* $("#RAK0").onblur(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseRak(0);
			}
		});  */

		////////////////////////////////////////////////////

		//////////////////////////////////////////////////////

		var dTableBBahan;
		var rowidBahan;
		loadDataBBahan = function(){
		
			$.ajax(
			{
				type: 'GET',    
				url: "{{url('bhn/browse')}}",
				async : false,
				data: {
						'KD_BHN': $("#KD_BHN"+rowidBahan).val(),
						PKP : $("#PKP").val(), 	
						'GOL': "{{$golz}}",
					
				},
				success: function( response )

				{
					resp = response;
					
					
					if ( resp.length > 1 )
					{	
							if(dTableBBahan){
								dTableBBahan.clear();
							}
							for(i=0; i<resp.length; i++){
								
								dTableBBahan.row.add([
									'<a href="javascript:void(0);" onclick="chooseBahan(\''+resp[i].KD_BHN+'\', \''+resp[i].NA_BHN+'\' , \''+resp[i].SATUAN+'\' )">'+resp[i].KD_BHN+'</a>',
									resp[i].NA_BHN,
									resp[i].SATUAN,
								]);
							}
							dTableBBahan.draw();
					
					}
					else
					{
						$("#KD_BHN"+rowidBahan).val(resp[0].KD_BHN);
						$("#NA_BHN"+rowidBahan).val(resp[0].NA_BHN);
						$("#SATUAN"+rowidBahan).val(resp[0].SATUAN);
					}
				}
			});
		}
		
		dTableBBahan = $("#table-bbahan").DataTable({
			
		});

		browseBahan = function(rid){
			rowidBahan = rid;
			$("#NA_BHN"+rowidBahan).val("");			
			loadDataBBahan();
	
			
			if ( $("#NA_BHN"+rowidBahan).val() == '' ) {				
					$("#browseBahanModal").modal("show");
			}	
		}
		
		chooseBahan = function(KD_BHN,NA_BHN,SATUAN){
			$("#KD_BHN"+rowidBahan).val(KD_BHN);
			$("#NA_BHN"+rowidBahan).val(NA_BHN);	
			$("#SATUAN"+rowidBahan).val(SATUAN);
			$("#browseBahanModal").modal("hide");
		}
		
		
		/* $("#RAK0").onblur(function(e){
			if(e.keyCode == 46){
				e.preventDefault();
				browseRak(0);
			}
		});  */

		////////////////////////////////////////////////////
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
		
		
		
			// if (cekDetail())
			// {	
			//     check = '1';
			// 	alert("#Barang ada yang kosong. ")
			// }
			
			
			if ( $('#KODES').val()=='' ) 
            {				
			    check = '1';
				alert("Suplier# Harus Diisi.");
			}

			if ( $('#KD_BRG').val()=='' ) 
            {				
			    check = '1';
				alert("Barang# Harus Diisi.");
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
			
			if (baris==0)
			{
				check = '1';
				alert("Data detail kosong (Tambahkan 1 baris kosong jika ingin mengosongi detail)");
			}

			if ( check == '0' )
			{
				hitung();	
		      	document.getElementById("entri").submit();  
			}
			
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


		
		$(".QTY").each(function() {
			
			let z = $(this).closest('tr');
			var QTYX = parseFloat(z.find('.QTY').val().replace(/,/g, ''));
			var HARGAX = parseFloat(z.find('.HARGA').val().replace(/,/g, ''));
	

            var TOTALX  =  ( QTYX * HARGAX );
			z.find('.TOTAL').val(TOTALX);


		    z.find('.HARGA').autoNumeric('update');			
		    z.find('.QTY').autoNumeric('update');	
		    z.find('.TOTAL').autoNumeric('update');				
	

            TTOTAL_QTY +=QTYX;		
            TTOTAL +=TOTALX;							
		
		});

		
		
		if(isNaN(TTOTAL_QTY)) TTOTAL_QTY = 0;

		$('#TTOTAL_QTY').val(numberWithCommas(TTOTAL_QTY));		
		$("#TTOTAL_QTY").autoNumeric('update');
		
		if(isNaN(TTOTAL)) TTOTAL = 0;

		$('#TTOTAL').val(numberWithCommas(TTOTAL));		
		$("#TTOTAL").autoNumeric('update');




		
	}
	

	
  
	function baru() {
		
		 kosong();
		 hidup();
	
	}
	
	function ganti() {
		
		 mati();
	
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
//	    $("#CLOSEX").attr("disabled", true);

		$("#CARI").attr("readonly", true);	
	    $("#SEARCHX").attr("disabled", true);
		
	    $("#PLUSX").attr("hidden", false)
		   
			$("#NO_BUKTI").attr("readonly", true);		   
			$("#TGL").attr("readonly", false);
			$("#JTEMPO").attr("readonly", false);
			$("#KODES").attr("readonly", true);
			$("#NAMAS").attr("readonly", true);			
			$("#ALAMAT").attr("readonly", true);
			$("#KOTA").attr("readonly", true);

			
			$("#NOTES").attr("readonly", false);
				

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
			$("#KET" + i.toString()).attr("readonly", false);
			$("#DELETEX" + i.toString()).attr("hidden", false);

			$tipx = $('#tipx').val();
		
			
			if ( $tipx != 'new' )
			{
				$("#KD_BHN" + i.toString()).attr("readonly", true);	
			//	$("#KD_BHN" + i.toString()).removeAttr('onblur');
				
				$("#KD_BRG" + i.toString()).attr("readonly", false);	
			//	$("#KD_BRG" + i.toString()).removeAttr('onblur');
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
		
	    $(".NO_BUKTI").attr("readonly", true);	
		
		$("#TGL").attr("readonly", true);
		$("#JTEMPO").attr("readonly", true);
		$("#KODES").attr("readonly", true);
		$("#NAMAS").attr("readonly", true);
		$("#ALAMAT").attr("readonly", true);
		$("#KOTA").attr("readonly", true);
	
		
		$("#NOTES").attr("readonly", true);

		
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

		 
		var html = '';
		$('#detailx').html(html);	
		
	}
	
	function hapusTrans() {
		let text = "Hapus Transaksi "+$('#NO_BUKTI').val()+"?";
		if (confirm(text) == true) 
		{
			window.location ="{{url('/lbhijau/delete/'.$header->NO_ID .'/?flagz='.$flagz.'&golz=' .$golz.'' )}}";
			//return true;
		} 
		return false;
	}
	

	function CariBukti() {
		
		var flagz = "{{ $flagz }}";
		var golz = "{{ $golz }}";
		var cari = $("#CARI").val();
		var loc = "{{ url('/lbhijau/edit/') }}" + '?idx={{ $header->NO_ID}}&tipx=search&flagz=' + encodeURIComponent(flagz) + '&golz=' + encodeURIComponent(golz) + '&buktix=' +encodeURIComponent(cari);
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
		            <input name='QTY[]' onclick='select()' onblur='hitung()' value='0' id='QTY${idrow}' type='text' style='text-align: right' class='form-control QTY text-primary' required >
                </td>
				
                <td {{( $golz =='B') ? '' : 'hidden' }} >
				    <input name='KD_BRG[]' data-rowid=${idrow} onblur='browseBahan(${idrow})' id='KD_BRG${idrow}' type='text' class='form-control  KD_BRG' >
                </td>
                <td {{( $golz =='B') ? '' : 'hidden' }} >
				    <input name='NA_BRG[]'   id='NA_BRG${idrow}' type='text' class='form-control  NA_BRG' required readonly>
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