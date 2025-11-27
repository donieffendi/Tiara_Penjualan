@extends('layouts.plain')

<style>
	.card {}

	.form-control:focus {
		background-color: #b5e5f9 !important;
	}

	/* perubahan tab warna di form edit  */
	.nav-item .nav-link.active {
		background-color: red !important;
		/* Use !important to ensure it overrides */
		color: white !important;
		/* border-radius: 10; */
	}

	/* menghilangkan padding */
	.content-header {
		padding: 0 !important;
	}

	/* query LOADX */
	.loader {
		position: fixed;
		top: 50%;
		left: 50%;
		width: 100px;
		aspect-ratio: 1;
		background:
			radial-gradient(farthest-side, #ffa516 90%, #0000) center/16px 16px,
			radial-gradient(farthest-side, green 90%, #0000) bottom/12px 12px;
		background-repeat: no-repeat;
		animation: l17 1s infinite linear;
		position: relative;
	}

	.loader::before {
		content: "";
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
		100% {
			transform: rotate(1turn)
		}
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
								<ul class="nav nav-tabs">
									<li class="nav-item active">
										<a class="nav-link active" href="#details" data-toggle="tab">Detail</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" href="#terkait" data-toggle="tab">Barang Terkait</a>
									</li>
								</ul>
								<div class="tab-content mt-3">
									<div id="details" class="tab-pane active">

										<form action="{{ $tipx == 'new' ? url('/lbhijau/store?typez=' . $typez . '') : url('/lbhijau/update/' . $header->NO_ID . '&typez=' . $typez . '') }}"
											method="POST" name ="entri" id="entri">
											@csrf
											<div class="tab-content mt-3">
												<style>
													/* Ensure specificity with class targeting */
													.form-group.special-input-label {
														position: relative;
														margin-left: 5px;
													}

													/* Ensure only bottom border for input */
													.form-group.special-input-label input {
														width: 100%;
														padding: 10px 0;
														border: none !important;
														border-bottom: 2px solid #ccc !important;
														outline: none !important;
														font-size: 16px !important;
														background: transparent !important;
														/* Remove any background color */
													}

													/* Bottom border color change on focus */
													.form-group.special-input-label input:focus {
														border-bottom: 2px solid #007BFF !important;
														/* Change color on focus */
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
													.form-group.special-input-label input:focus+label,
													.form-group.special-input-label input:not(:placeholder-shown)+label {
														top: -10px !important;
														font-size: 12px !important;
														color: #007BFF !important;
													}
												</style>

												<div class="form-group row">
													{{-- <div class="col-md-1" align="right">
											<label for="NO_BUKTI" class="form-label">Bukti#</label>
										</div> --}}

													<input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID" placeholder="Masukkan NO_ID"
														value="{{ $header->NO_ID ?? '' }}" hidden readonly>

													<input name="tipx" class="form-control tipx" id="tipx" value="{{ $tipx }}" hidden>
													<input name="typez" class="form-control typez" id="typez" value="{{ $typez }}" hidden>

													{{-- <div class="col-md-2">
											<input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI"
											placeholder="Masukkan Bukti#" value="{{$header->NO_BUKTI}}" readonly>
										</div> --}}

													<!-- code text box baru -->
													<div class="col-md-2 form-group row special-input-label">
														<input type="text" class="NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI" value="{{ $header->NO_BUKTI }}" placeholder=" " readonly>
														<input type="text" class="NO_BUKTI2" id="NO_BUKTI2" name="NO_BUKTI2" value="{{ $header->NO_BUKTI2 }}" placeholder=" " hidden>
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
														<input type="text" class="KODES" id="KODES" name="KODES" value="{{ $header->KODES }}" placeholder=" " readonly>
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
														<input class="date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off"
															value="{{ date('d-m-Y', strtotime($header->TGL)) }}">
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
														<input type="text" class="NAMAS" id="NAMAS" name="NAMAS" value="{{ $header->NAMAS }}" placeholder=" " readonly>
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
													<div class="col-md-1" align="left">
														<label for="TYPE" class="form-label">Type</label>
													</div>
													<div class="col-md-2">
														<!-- <input type="text" class="form-control BNK" id="BNK" name="BNK" placeholder="Masukkan Bnk"> -->
														<select id="TYPE" class="form-control" name="TYPE">
															<option value="-" {{ $header->TYPE == '-' ? 'selected' : '' }} disable selected hidden>--Pilih Tipe--</option>
															<option value="C" {{ $header->TYPE == 'C' ? 'selected' : '' }}>CASH</option>
															<option value="H" {{ $header->TYPE == 'H' ? 'selected' : '' }}>HIJAU</option>
															<option value="V" {{ $header->TYPE == 'V' ? 'selected' : '' }}>VARIAN</option>
															<option value="B" {{ $header->TYPE == 'B' ? 'selected' : '' }}>BANK</option>
														</select>
													</div>

													<div class="col-md-1" align="left">
														<label for="NKARTU" class="form-label">Bank</label>
													</div>
													<div class="col-md-2">
														<!-- <input type="text" class="form-control BNK" id="BNK" name="BNK" placeholder="Masukkan Bnk"> -->
														<select id="NKARTU" class="form-control" name="NKARTU">
															<option value="-" {{ $header->NKARTU == '-' ? 'selected' : '' }} disable selected hidden>--Pilih Bank--</option>
															<option value="BCA1" {{ $header->NKARTU == 'BCA1' ? 'selected' : '' }}>KREDIT BCA</option>
															<option value="BCA2" {{ $header->NKARTU == 'BCA2' ? 'selected' : '' }}>DEBET BCA</option>
															<option value="BNI" {{ $header->NKARTU == 'BNI' ? 'selected' : '' }}>DEBET B.N.I</option>
															<option value="VISA" {{ $header->NKARTU == 'VISA' ? 'selected' : '' }}>VISA CARD</option>
															<option value="MASTER" {{ $header->NKARTU == 'MASTER' ? 'selected' : '' }}>MASTER CARD</option>
															<option value="DINNERS" {{ $header->NKARTU == 'DINNERS' ? 'selected' : '' }}>DINNERS CARD</option>
															<option value="BCA3" {{ $header->NKARTU == 'BCA3' ? 'selected' : '' }}>BCA EVERYDAY</option>
															<option value="BCA4" {{ $header->NKARTU == 'BCA4' ? 'selected' : '' }}>BCA NON EVERYDAY</option>
														</select>
													</div>

													<div class="col-md-1"></div>

													<!-- code text box baru -->
													<div class="col-md-2 form-group row special-input-label">
														<input class="date" id="TG_MULAI" name="TG_MULAI" data-date-format="dd-mm-yyyy" type="text" autocomplete="off"
															value="{{ date('d-m-Y', strtotime($header->TG_MULAI)) }}">
														<label for="TG_MULAI">Mulai Dari</label>
													</div>
													<!-- tutupannya -->

													<!-- code text box baru -->
													<div class="col-md-2 form-group row special-input-label">
														<input class="date" id="TG_AKHIR" name="TG_AKHIR" data-date-format="dd-mm-yyyy" type="text" autocomplete="off"
															value="{{ date('d-m-Y', strtotime($header->TG_AKHIR)) }}">
														<label for="TG_AKHIR">Sampai Dengan</label>
													</div>
													<!-- tutupannya -->
												</div>

												<div class="form-group row">
													<!-- code text box baru -->
													<div class="col-md-2 form-group row special-input-label">
														<input type="text" class="QTY_BELI" id="QTY_BELI" name="QTY_BELI" value="{{ $header->QTY_BELI }}" placeholder=" ">
														<label for="QTY_BELI">Jumlah Beli</label>
													</div>
													<!-- tutupannya -->

													<div class="col-md-1"></div>

													<div class="col-md-1" align="left">
														<label for="JNS" class="form-label">Hubungan</label>
													</div>
													<div class="col-md-2">
														<select id="JNS" class="form-control" name="JNS">
															<option value="-" {{ $header->JNS == '-' ? 'selected' : '' }} disable selected hidden>--Pilih Jenis--</option>
															<option value="AND" {{ $header->JNS == 'AND' ? 'selected' : '' }}>AND</option>
															<option value="OR" {{ $header->JNS == 'OR' ? 'selected' : '' }}>OR</option>
														</select>
													</div>

													<div class="col-md-1"></div>

													<!-- code text box baru -->
													<div class="col-md-2 form-group row special-input-label">
														<input class="time" id="JM_MULAI" name="JM_MULAI" type="text" value="{{ date('H:i:s', strtotime($header->JM_MULAI)) }}">
														<label for="JM_MULAI">Jam Mulai</label>
													</div>
													<!-- tutupannya -->
													<!-- code text box baru -->
													<div class="col-md-2 form-group row special-input-label">
														<input class="time" id="JM_AKHIR" name="JM_AKHIR" type="text" value="{{ date('H:i:s', strtotime($header->JM_AKHIR)) }}">
														<label for="JM_AKHIR">Sampai Dengan</label>
													</div>

													<!-- tutupannya -->

												</div>

												<div class="form-group row">
													<!-- code text box baru -->
													<div class="col-md-2 form-group row special-input-label">
														<input type="text" class="RP_BELI" id="RP_BELI" name="RP_BELI" value="{{ $header->RP_BELI }}" placeholder=" ">
														<label for="RP_BELI">Total Beli</label>
													</div>
													<!-- tutupannya -->

													<div class="col-md-2"></div>

													<div class="col-md-1" align="right">
														<label for="CBG" class="form-label">Cabang :</label>
													</div>

													<div class="col-md-1"align="right">
														<input type="checkbox" class="form-check-input" id="TGZ" name="TGZ" value="1"
															{{ $header->TGZ == 1 ? 'checked' : '' }}>
														<label for="TGZ">TGZ</label>
													</div>

													<div class="col-md-1"align="right">
														<input type="checkbox" class="form-check-input" id="TMM" name="TMM" value="1"
															{{ $header->TMM == 1 ? 'checked' : '' }}>
														<label for="TMM">TMM</label>
													</div>

													<div class="col-md-1"align="right">
														<input type="checkbox" class="form-check-input" id="SOP" name="SOP" value="1"
															{{ $header->SOP == 1 ? 'checked' : '' }}>
														<label for="SOP">SOP</label>
													</div>

													<div class="col-md-1"align="right">
														<input type="checkbox" class="form-check-input" id="TYA" name="TYA" value="1"
															{{ $header->TYA == 1 ? 'checked' : '' }}>
														<label for="TYA">Yeh Aya</label>
													</div>

													<div class="col-md-1"align="right">
														<input type="checkbox" class="form-check-input" id="FSC" name="FSC" value="1"
															{{ $header->FSC == 1 ? 'checked' : '' }}>
														<label for="FSC">F. Cokro</label>
													</div>

													<div class="col-md-1"align="right">
														<input type="checkbox" class="form-check-input" id="FSG" name="FSG" value="1"
															{{ $header->FSG == 1 ? 'checked' : '' }}>
														<label for="FSG">F. Gateng</label>
													</div>

													<div class="col-md-1"align="right">
														<input type="checkbox" class="form-check-input" id="FSS" name="FSS" value="1"
															{{ $header->FSS == 1 ? 'checked' : '' }}>
														<label for="FSS">F. Sanglah</label>
													</div>
												</div>

												<div class="form-group row">
													<div class="col-md-1" align="left">
														<label for="KONDISI" class="form-label">Kondisi</label>
													</div>
													<div class="col-md-2">
														<select id="KONDISI" class="form-control" name="KONDISI">
															<option value="-" {{ $header->KONDISI == '-' ? 'selected' : '' }} disable selected hidden>--Pilih Jenis--</option>
															<option value="D" {{ $header->KONDISI == 'D' ? 'selected' : '' }}>BELANJA BARANG PROMO</option>
															<option value="A" {{ $header->KONDISI == 'A' ? 'selected' : '' }}>TOTAL SEMUA BELANJA</option>
														</select>
													</div>

													<!-- code text box baru -->
													<div class="col-md-2 form-group row special-input-label">
														<input type="text" class="RP_BELI_MAX" id="RP_BELI_MAX" name="RP_BELI_MAX" value="{{ $header->RP_BELI_MAX }}" placeholder=" ">
														<label for="RP_BELI_MAX">MAX</label>
													</div>
													<!-- tutupannya -->
												</div>

												<div class="form-group row">
													<div class="col-md-1"></div>
													<div class="col-md-2"align="left">
														<input type="checkbox" class="form-check-input" id="KELIPATAN" name="KELIPATAN" value="1"
															{{ $header->KELIPATAN == 1 ? 'checked' : '' }}>
														<label for="KELIPATAN">Berlaku Kelipatan</label>
													</div>
												</div>

												<div class="form-group row">
													<!-- code text box baru -->
													<div class="col-md-6 form-group row special-input-label">
														<input type="text" class="KET" id="KET" name="KET" value="{{ $header->KET }}" placeholder=" ">
														<label for="KET">Keterangan</label>
													</div>
													<!-- tutupannya -->
												</div>

												<div class="tab-content mt-3">

													<table id="datatable" class="table-striped table-border table">
														<thead>
															<tr>
																<th width="100px" style="text-align:center">No.</th>

																<th width="100px" style="text-align:center">Qty</th>

																<th width="150px">
																	<label style="color:red;font-size:20px; text-align:center">*</label>
																	<label for="KD_BRG" class="form-label">Kode</label>
																</th>
																<th width="600px" style="text-align:center">Nama Hadiah / Uraian</th>

															</tr>
														</thead>

														<tbody>
															<?php $no = 0; ?>
															@foreach ($detail as $detail)
																<tr>
																	<td>
																		<input type="hidden" name="NO_ID[]{{ $no }}" id="NO_ID" type="text" value="{{ $detail->NO_ID }}"
																			class="form-control NO_ID" onkeypress="return tabE(this,event)" readonly>

																		<input name="REC[]" id="REC{{ $no }}" type="text" value="{{ $detail->REC }}" class="form-control REC"
																			onkeypress="return tabE(this,event)" readonly style="text-align:center">
																	</td>

																	<td>
																		<input name="QTY[]" onclick="select()" onblur="hitung()" value="{{ $detail->QTY }}" id="QTY{{ $no }}"
																			type="text" style="text-align: right" class="form-control QTY">
																	</td>
																	<td>
																		<input name="KD_BRG[]" id="KD_BRG{{ $no }}" type="text" value="{{ $detail->KD_BRG }}" class="form-control KD_BRG"
																			onblur="browseBarang({{ $no }})">
																	</td>
																	<td>
																		<input name="NA_BRG[]" id="NA_BRG{{ $no }}" type="text" class="form-control KD_BRG" value="{{ $detail->NA_BRG }}"
																			readonly required>
																	</td>

																	<td>
																		<button type='button' id='DELETEX{{ $no }}' class='btn btn-sm btn-circle btn-outline-danger btn-delete'
																			onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
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
															{{-- <td><input class="form-control TTOTAL_QTY  text-primary" style="text-align: right"  id="TTOTAL_QTY" name="TTOTAL_QTY" value="{{$header->TOTAL_QTY}}" readonly></td> --}}
															<td></td>
															<!-- <td><input class="form-control TTOTAL text-primary" style="text-align: right"  id="TTOTAL" name="TTOTAL" value="{{ $header->TOTAL }}" readonly></td> -->
															<td></td>
														</tfoot>
													</table>

													<div class="col-md-2 row">
														<a type="button" id='PLUSX' onclick="tambah()" class="fas fa-plus fa-sm md-3" style="font-size: 20px"></a>

													</div>

												</div>
											</div>

											<hr style="margin-top: 30px; margin-buttom: 30px">
											<div class="col-md-12 form-group row mt-3">
												<div class="col-md-4">
													<button hidden type="button" id='TOPX'
														onclick="location.href='{{ url('/lbhijau/edit/?idx=' . $idx . '&tipx=top&typez=' . $typez . '') }}'"
														class="btn btn-outline-primary">Top</button>
													<button hidden type="button" id='PREVX'
														onclick="location.href='{{ url('/lbhijau/edit/?idx=' . $header->NO_ID . '&tipx=prev&typez=' . $typez . '&buktix=' . $header->NO_BUKTI) }}'"
														class="btn btn-outline-primary">Prev</button>
													<button hidden type="button" id='NEXTX'
														onclick="location.href='{{ url('/lbhijau/edit/?idx=' . $header->NO_ID . '&tipx=next&typez=' . $typez . '&buktix=' . $header->NO_BUKTI) }}'"
														class="btn btn-outline-primary">Next</button>
													<button hidden type="button" id='BOTTOMX'
														onclick="location.href='{{ url('/lbhijau/edit/?idx=' . $idx . '&tipx=bottom&typez=' . $typez . '') }}'"
														class="btn btn-outline-primary">Bottom</button>
												</div>
												<div class="col-md-5">
													<button hidden type="button" id='NEWX' onclick="location.href='{{ url('/lbhijau/edit/?idx=0&tipx=new&typez=' . $typez . '') }}'"
														class="btn btn-warning">New</button>
													<button hidden type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>
													<button hidden type="button" id='UNDOX'
														onclick="location.href='{{ url('/lbhijau/edit/?idx=' . $idx . '&tipx=undo&typez=' . $typez . '') }}'" class="btn btn-info">Undo</button>
													<button type="button" id='SAVEX' onclick='simpan()' class="btn btn-success" class="fa fa-save"></i>Save</button>

												</div>
												<div class="col-md-3">
													<button hidden type="button" id='HAPUSX' onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
													<!-- <button type="button" id='CLOSEX'  onclick="location.href='{{ url('/lbhijau?typez=' . $typez . '') }}'" class="btn btn-outline-secondary">Close</button> -->
													<!-- tombol close sweet alert -->
													<button type="button" id='CLOSEX' onclick="closeTrans()" class="btn btn-outline-secondary">Close</button>
												</div>
											</div>
										</form>
									</div>

									<div id="terkait" class="tab-pane">
										<form action="">
											@csrf
											<div class="tab-content mt-3">
												<style>
													/* Ensure specificity with class targeting */
													.form-group.special-input-label {
														position: relative;
														margin-left: 5px;
													}

													/* Ensure only bottom border for input */
													.form-group.special-input-label input {
														width: 100%;
														padding: 10px 0;
														border: none !important;
														border-bottom: 2px solid #ccc !important;
														outline: none !important;
														font-size: 16px !important;
														background: transparent !important;
														/* Remove any background color */
													}

													/* Bottom border color change on focus */
													.form-group.special-input-label input:focus {
														border-bottom: 2px solid #007BFF !important;
														/* Change color on focus */
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
													.form-group.special-input-label input:focus+label,
													.form-group.special-input-label input:not(:placeholder-shown)+label {
														top: -10px !important;
														font-size: 12px !important;
														color: #007BFF !important;
													}
												</style>

												<div class="form-group row">
													<!-- code text box baru -->
													<div class="col-md-2 form-group row special-input-label">
														<input type="text" class="SUP1TAB2" id="SUP1TAB2" name="SUP1TAB2" value="" placeholder=" ">
														<label for="SUP1TAB2">Supplier</label>
													</div>
													<div class="col-md-1" align="right">
														<label for="" class="form-label">s/d</label>
													</div>
													<div class="col-md-2 form-group row special-input-label">
														<input type="text" class="SUP2TAB2" id="SUP2TAB2" name="SUP2TAB2" value="" placeholder=" ">
														<label for="SUP2TAB2">Supplier</label>
													</div>
													<!-- tutupannya -->
												</div>

												<div class="form-group row">
													<!-- code text box baru -->
													<div class="col-md-2 form-group row special-input-label">
														<input type="text" class="SUB1TAB2" id="SUB1TAB2" name="SUB1TAB2" value="" placeholder=" ">
														<label for="SUB1TAB2">Sub</label>
													</div>
													<div class="col-md-1" align="right">
														<label for="" class="form-label">s/d</label>
													</div>
													<div class="col-md-2 form-group row special-input-label">
														<input type="text" class="SUB2TAB2" id="SUB2TAB2" name="SUB2TAB2" value="" placeholder=" ">
														<label for="SUB2TAB2">Sub</label>
													</div>
													<div class="col-md-1">
													</div>
													<div class="col-md-3">
														<button type="button" id='' onclick='tampilkan()' class="btn btn-success" class="fa fa-save"></i>CARI</button>
														<button type="button" id='' onclick='proseskan()' class="btn btn-success" class="fa fa-save"></i>TAMBAHKAN</button>
														<button type="button" id='' onclick="hapuskan(0)" class="btn btn-danger">HAPUS SEMUA</button>
													</div>
													<!-- tutupannya -->
												</div>

												<div class="form-group row">
													<!-- code text box baru -->
													<div class="col-md-2 form-group row special-input-label">
														<input type="text" class="SUB_ITEMTAB2" id="SUB_ITEMTAB2" name="SUB_ITEMTAB2" value="" placeholder=" ">
														<label for="SUB_ITEMTAB2">Sub Item</label>
													</div>

													<div class="col-md-2 form-group row special-input-label" hidden>
														<input type="text" class="KDBRGTAB2_ALL" id="KDBRGTAB2_ALL" name="KDBRGTAB2_ALL" value="{{ $header->BRG }}" placeholder=" ">
														<label for="KDBRGTAB2_ALL"></label>
													</div>
												</div>

												<div class="tab-content mt-3">

													<table id="datatable_detail" class="table-striped table-border table">
														<thead>
															<tr>
																<th width="200px" style="text-align:center">Sub item</th>
																<th width="300px" style="text-align:center">Nama Barang</th>
																<th width="100px" style="text-align:center">Ukuran</th>
																<th width="100px" style="text-align:center">Kemasan</th>
																<th width="100px" style="text-align:center">-</th>
															</tr>
														</thead>
														<tbody>

															@foreach ($detail2 as $detail2)
																<tr>
																	<td><input name='KD_BRGTAB2[]' id='KD_BRGTAB2{{ $detail2->NO_ID }}' value="{{ $detail2->KD_BRG }}" type='text'
																			class='form-control KD_BRGTAB2' required readonly></td>
																	<td><input name='NA_BRGTAB2[]' id='NA_BRGTAB2{{ $detail2->NO_ID }}' value="{{ $detail2->NA_BRG }}" type='text'
																			class='form-control NA_BRGTAB2' required readonly></td>
																	<td><input name='KET_UKTAB2[]' id='KET_UKTAB2{{ $detail2->NO_ID }}' value="{{ $detail2->KET_UK }}" type='text'
																			class='form-control KET_UKTAB2' placeholder="Satuan" required readonly></td>
																	<td><input name='KET_KEMTAB2[]' id='KET_KEMTAB2{{ $detail2->NO_ID }}' value="{{ $detail2->KET_KEM }}" type='text'
																			class='form-control KET_KEMTAB2' placeholder="Satuan" required readonly></td>
																	<td>
																		<button type='button' id='DELETEXTAB2' class='btn btn-sm btn-circle btn-outline-danger btn-deletetab2'
																			onclick='hapuskan({{ $detail2->NO_ID }})'> <i class='fa fa-fw fa-trash'></i> </button>
																	</td>
																</tr>
															@endforeach
														</tbody>

														<tfoot>
														</tfoot>
													</table>
												</div>
										</form>
									</div>
								</div>
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
						<table class="table-stripped table-bordered table" id="table-bsuplier">
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
						<table class="table-stripped table-bordered table" id="table-bbarang">
							<thead>
								<tr>
									<th>Item#</th>
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
						<table class="table-stripped table-bordered table" id="table-bbahan">
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
		<script src="{{ asset('foxie_js_css/bootstrap.bundle.min.js') }}"></script>

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

			$(document).ready(function() {
				idrow = <?= $no ?>;
				baris = <?= $no ?>;

				$('body').on('keydown', 'input, select', function(e) {
					if (e.key === "Enter") {
						var self = $(this),
							form = self.parents('form:eq(0)'),
							focusable, next;
						focusable = form.find('input,select,textarea').filter(':visible');
						next = focusable.eq(focusable.index(this) + 1);
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


				if ($tipx == 'new') {
					baru();
					tambah();
				}

				if ($tipx != 'new') {
					ganti();
				}


				jumlahdata = 100;
				for (i = 0; i <= jumlahdata; i++) {
					$("#QTY" + i.toString()).autoNumeric('init', {
						aSign: '<?php echo ''; ?>',
						vMin: '-999999999.99'
					});
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

				//tab2
				$('body').on('click', '.btn-deletetab2', function() {
					var val = $(this).parents("tr").remove();
				});

				$('.date').datepicker({
					dateFormat: 'dd-mm-yy'
				});

				// $(".time").timepicker({
				//     timeFormat: "HH:mm:ss",
				// 	interval: 15,
				// 	dynamic: false,
				// 	dropdown: true,
				// 	scrollbar: true
				// });




				//		CHOOSE Supplier
				var dTableBSuplier;
				loadDataBSuplier = function() {

					$.ajax({
						type: 'GET',
						url: '{{ url('sup/browse') }}',
						// data: {
						// 	'GOL': 'Y',
						// },
						success: function(response) {
							resp = response;
							if (dTableBSuplier) {
								dTableBSuplier.clear();
							}
							for (i = 0; i < resp.length; i++) {

								dTableBSuplier.row.add([
									'<a href="javascript:void(0);" onclick="chooseSuplier(\'' + resp[i].KODES + '\',  \'' + resp[
										i].NAMAS + '\', \'' + resp[i].ALAMAT + '\', \'' + resp[i].KOTA + '\', \'' + resp[i].PKP +
									'\')">' + resp[i].KODES + '</a>',
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

				browseSuplier = function() {
					loadDataBSuplier();
					$("#browseSuplierModal").modal("show");
				}

				chooseSuplier = function(KODES, NAMAS, ALAMAT, KOTA, PKP) {
					$("#KODES").val(KODES);
					$("#NAMAS").val(NAMAS);
					$("#ALAMAT").val(ALAMAT);
					$("#KOTA").val(KOTA);
					$("#PKP").val(PKP);
					$("#browseSuplierModal").modal("hide");
				}

				var PKP = $("#PKP").val();

				if (PKP == 1) {
					$("#PKP").prop('checked', true)
				} else {
					$("#PKP").prop('checked', false)
				}

				$("#KODES").keypress(function(e) {

					if (e.keyCode == 46) {
						e.preventDefault();
						browseSuplier();
					}
				});






				//////////////////////////////////////////////////////

				var dTableBBarang;
				var rowidBarang;
				loadDataBBarang = function() {

					$.ajax({
						type: 'GET',
						url: "{{ url('brg/browse') }}",
						async: false,
						data: {
							'KD_BRG': $("#KD_BRG" + rowidBarang).val(),

						},
						success: function(response)

						{
							resp = response;


							if (resp.length > 1) {
								if (dTableBBarang) {
									dTableBBarang.clear();
								}
								for (i = 0; i < resp.length; i++) {

									dTableBBarang.row.add([
										'<a href="javascript:void(0);" onclick="chooseBarang(\'' + resp[i].KD_BRG + '\', \'' + resp[
											i].NA_BRG + '\', \'' + resp[i].KD_BRG2 + '\'  )">' + resp[i].KD_BRG + '</a>',
										resp[i].NA_BRG,
									]);
								}
								dTableBBarang.draw();

							} else {
								$("#KD_BRG" + rowidBarang).val(resp[0].KD_BRG);
								$("#NA_BRG" + rowidBarang).val(resp[0].NA_BRG);
							}
						}
					});
				}

				dTableBBarang = $("#table-bbarang").DataTable({

				});

				browseBarang = function(rid) {
					rowidBarang = rid;
					$("#NA_BRG" + rowidBarang).val("");
					loadDataBBarang();


					if ($("#NA_BRG" + rowidBarang).val() == '') {
						$("#browseBarangModal").modal("show");
					}
				}

				chooseBarang = function(KD_BRG, NA_BRG, KD_BRG2) {
					$("#KD_BRG" + rowidBarang).val(KD_BRG);
					$("#NA_BRG" + rowidBarang).val(NA_BRG);
					$("#KD_BRG2" + rowidBarang).val(KD_BRG2);
					$("#browseBarangModal").modal("hide");
				}

				awal_tampilkan();

				/* $("#RAK0").onblur(function(e){
					if(e.keyCode == 46){
						e.preventDefault();
						browseRak(0);
					}
				});  */

				////////////////////////////////////////////////////
			});



			///////////////////////////////////////




			function cekDetail() {
				var cekBarang = '';
				$(".KD_BRG").each(function() {

					let z = $(this).closest('tr');
					var KD_BRGX = z.find('.KD_BRG').val();

					if (KD_BRGX == "") {
						cekBarang = '1';

					}
				});

				return cekBarang;
			}


			function simpan() {
				hitung();

				var tgl = $('#TGL').val();
				var bulanPer = {{ session()->get('periode')['bulan'] }};
				var tahunPer = {{ session()->get('periode')['tahun'] }};

				var check = '0';



				// if (cekDetail())
				// {
				//     check = '1';
				// 	alert("#Barang ada yang kosong. ")
				// }


				if ($('#KODES').val() == '') {
					check = '1';
					alert("Suplier# Harus Diisi.");
				}

				if ($('#KD_BRG').val() == '') {
					check = '1';
					alert("Barang# Harus Diisi.");
				}



				if (tgl.substring(3, 5) != bulanPer) {
					check = '1';
					alert("Bulan tidak sama dengan Periode");
				}


				if (tgl.substring(tgl.length - 4) != tahunPer) {
					check = '1';
					alert("Tahun tidak sama dengan Periode");
				}

				if (baris == 0) {
					check = '1';
					alert("Data detail kosong (Tambahkan 1 baris kosong jika ingin mengosongi detail)");
				}

				if (check == '0') {
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

			function hitung() {}




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


					if ($tipx != 'new') {
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

			// function hapusTrans() {
			// 	let text = "Hapus Transaksi "+$('#NO_BUKTI').val()+"?";
			// 	if (confirm(text) == true)
			// 	{
			// 		window.location ="{{ url('/lbhijau/delete/' . $header->NO_ID . '/?typez=' . $typez . '') }}";
			// 		//return true;
			// 	}
			// 	return false;
			// }

			function hapusTrans() {
				let text = "Hapus Transaksi " + $('#NO_BUKTI').val() + "?";

				var loc = '';
				var typez = "{{ $typez }}";

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
							loc = "{{ url('/lbhijau/delete/' . $header->NO_ID) }}" + '?typez=' + encodeURIComponent(typez);

							// alert(loc);
							window.location = loc;

						});
					}
				});
			}

			function closeTrans() {
				console.log("masuk");
				var loc = '';
				var typez = "{{ $typez }}";

				Swal.fire({
					title: 'Are you sure?',
					text: 'Do you really want to close this page? Unsaved changes will be lost.',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Yes, close it',
					cancelButtonText: 'No, stay here'
				}).then((result) => {
					if (result.isConfirmed) {
						loc = "{{ url('/lbhijau/') }}" + '?typez=' + encodeURIComponent(typez);
						window.location = loc;
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

				var typez = "{{ $typez }}";
				var cari = $("#CARI").val();
				var loc = "{{ url('/lbhijau/edit/') }}" + '?idx={{ $header->NO_ID }}&tipx=search&typez=' + encodeURIComponent(typez) + '&buktix=' +
					encodeURIComponent(cari);
				window.location = loc;

			}


			function tambah() {
				console.log(baris);
				var x = document.getElementById('datatable').insertRow(baris + 1);
				console.log(x);

				html = `<tr>

                <td>
 					<input name='NO_ID[]' id='NO_ID${idrow}' type='hidden' class='form-control NO_ID' value='new' readonly>
					<input name='REC[]' id='REC${idrow}' type='text' class='REC form-control' onkeypress='return tabE(this,event)' readonly>
	            </td>

				<td>
		            <input name='QTY[]' onclick='select()' onblur='hitung()' value='0' id='QTY${idrow}' type='text' style='text-align: right' class='form-control QTY text-primary' required >
                </td>

                <td >
				    <input name='KD_BRG[]' data-rowid=${idrow} onblur='browseBarang(${idrow})' id='KD_BRG${idrow}' type='text' class='form-control  KD_BRG' >
				    <input name='KD_BRG2[]' data-rowid=${idrow} id='KD_BRG2${idrow}' type='text' class='form-control  KD_BRG2' hidden>
                </td>
                <td >
				    <input name='NA_BRG[]'   id='NA_BRG${idrow}' type='text' class='form-control  NA_BRG' required readonly>
                </td>

                <td>
					<button type='button' id='DELETEX${idrow}'  class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
                </td>
         </tr>`;

				x.innerHTML = html;
				var html = '';



				jumlahdata = 100;
				for (i = 0; i <= jumlahdata; i++) {
					$("#QTY" + i.toString()).autoNumeric('init', {
						aSign: '<?php echo ''; ?>',
						vMin: '-999999999',
						vMax: '999999999',
						decimalPlaces: 0, // Tidak ada angka desimal
						digitGroupSeparator: ',', // Pemisah ribuan
						decimalCharacter: '', // Tidak ada karakter desimal
						emptyInputBehavior: 'zero'
					});
				}



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



			function awal_tampilkan() {
				var type = $('#TYPE').val();
				var kdBrgList = $('#KDBRGTAB2_ALL').val().split(','); // Pecah string berdasarkan koma
				var html = '';

				if (type != "V") {
					// Loop setiap kode barang
					$('#datatable_detail').empty();
					kdBrgList.forEach(function(kd_brg, index) {
						$.ajax({
							type: 'POST',
							url: "http://192.168.0.2/admin-apf-app/public/api/barang_spm",
							data: JSON.stringify({
								sup_x: '',
								sup_y: '',
								sub_x: '',
								sub_y: '',
								kd_brg: kd_brg.trim() // Hilangkan spasi ekstra
							}),
							contentType: "application/json",
							dataType: "json",
							success: function(resp) {
								var data = resp.data;
								var html = '';
								for (var i = 0; i < data.length; i++) {
									html += `<tr>
								<td><input name='KD_BRGTAB2[]' data-rowid=${index} id='KD_BRGTAB2${index}' value="${data[i].kd_brg}" type='text' class='form-control KD_BRGTAB2' required readonly></td>
								<td><input name='NA_BRGTAB2[]' data-rowid=${index} id='NA_BRGTAB2${index}' value="${data[i].na_brg}" type='text' class='form-control NA_BRGTAB2' required readonly></td>
								<td><input name='KET_UKTAB2[]' data-rowid=${index} id='KET_UKTAB2${index}' value="${data[i].ket_uk}" type='text' class='form-control KET_UKTAB2' placeholder="Satuan" required readonly></td>
								<td><input name='KET_KEMTAB2[]' data-rowid=${index} id='KET_KEMTAB2${index}' value="${data[i].ket_kem}" type='text' class='form-control KET_KEMTAB2' placeholder="Satuan" required readonly></td>
								<td>
									<button type='button' id='DELETEXTAB2${index}' class='btn btn-sm btn-circle btn-outline-danger btn-deletetab2'>
										<i class='fa fa-fw fa-trash'></i>
									</button>
								</td>
							</tr>`;
								}

								// Setelah looping selesai, tambahkan ke tabel
								// $('#datatable_detail').append(html);

								$('#datatable_detail').append(html);
								updateHiddenField();
							}
						});
					});
				}

			}

			function tampilkan() {
				var mulai = (idrow == baris) ? idrow - 1 : idrow;
				$.ajax({
					type: 'POST',
					url: "http://192.168.0.2/admin-apf-app/public/api/barang_spm",
					data: JSON.stringify({
						sup_x: $('#SUP1TAB2').val(),
						sup_y: $('#SUP2TAB2').val(),
						sub_x: $('#SUB1TAB2').val(),
						sub_y: $('#SUB2TAB2').val(),
						kd_brg: $('#SUB_ITEMTAB2').val()
					}),
					contentType: "application/json",
					dataType: "json",
					success: function(resp) {
						var data = resp.data;
						var html = '';
						for (i = 0; i < data.length; i++) {
							html += `<tr>
								<td><input name='KD_BRGTAB2[]' data-rowid=${i} id='KD_BRGTAB2${i}' value="${data[i].kd_brg}" type='text' class='form-control KD_BRGTAB2' required readonly></td>
								<td><input name='NA_BRGTAB2[]' data-rowid=${i} id='NA_BRGTAB2${i}' value="${data[i].na_brg}" type='text' class='form-control  NA_BRGTAB2' required readonly></td>
								<td><input name='KET_UKTAB2[]' data-rowid=${i} id='KET_UKTAB2${i}' value="${data[i].ket_uk}" type='text' class='form-control  KET_UKTAB2' placeholder="Satuan" required readonly></td>
								<td><input name='KET_KEMTAB2[]' data-rowid=${i} id='KET_KEMTAB2${i}' value="${data[i].ket_kem}" type='text' class='form-control  KET_KEMTAB2' placeholder="Satuan" required readonly></td>
								<td>
									<button type='button' id='DELETEXTAB2${i}'  class='btn btn-sm btn-circle btn-outline-danger btn-deletetab2' '> <i class='fa fa-fw fa-trash'></i> </button>
								</td>
							</tr>`;
						}
						$('#datatable_detail').append(html);
						updateHiddenField();
					},
					error: function(xhr, status, error) {
						// alert("Terjadi kesalahan: " + error);
						alert("Barang Terkait Tidak Ada ! ");
					}
				});
			}

			function proseskan() {
				updateHiddenField();
				var nobukti = $('#NO_BUKTI').val();
				var nobukti2 = $('#NO_BUKTI2').val();
				var kdbrgtab2_all = $('#KDBRGTAB2_ALL').val();

				var dataBarang = [];
				$('.KD_BRGTAB2').each(function(index) {
					dataBarang.push({
						kd_brg: $(this).val(),
						na_brg: $('.NA_BRGTAB2').eq(index).val(),
						ket_uk: $('.KET_UKTAB2').eq(index).val(),
						ket_kem: $('.KET_KEMTAB2').eq(index).val()
					});
				});

				// var kd_brgtab2 = [];
				// $('.KD_BRGTAB2').each(function() {
				// 	kd_brgtab2.push($(this).val());
				// });

				// Mendapatkan token CSRF dari meta tag
				var csrfToken = $('meta[name="csrf-token"]').attr('content');

				$.ajax({
					type: 'POST',
					url: "{{ url('lbhijau/proseskan') }}",
					data: {
						// kd_brgtab2: kd_brgtab2,
						dataBarang: dataBarang,
						kdbrgtab2_all: kdbrgtab2_all,
						nobukti: nobukti,
						nobukti2: nobukti2
					},
					headers: {
						'X-CSRF-TOKEN': csrfToken // Menambahkan token CSRF ke header
					},
					success: function(resp) {
						alert('HS berhasil diperbarui!');
					},
					error: function(xhr) {
						alert('Terjadi kesalahan: ' + xhr.responseText);
					}
				});
			}

			function hapuskan(id) {
				updateHiddenField();
				var nobukti = $('#NO_BUKTI').val();
				var nobukti2 = $('#NO_BUKTI2').val();
				var kdbrgtab2_all = $('#KDBRGTAB2_ALL').val();
				var id = id;
				// console,log(kdbrgtab2_all);
				if (id != 0) {
					// Tampilkan konfirmasi sebelum menghapus
					var konfirmasi = confirm("Apakah Anda yakin ingin menghapus item ini?");
					if (!konfirmasi) {
						return; // Jika pengguna menekan "Batal", hentikan eksekusi fungsi
					}
				} else if (id == 0) {
					// Tampilkan konfirmasi sebelum menghapus
					var konfirmasi = confirm("Apakah Anda yakin ingin menghapus seluruh item ini?");
					if (!konfirmasi) {
						return; // Jika pengguna menekan "Batal", hentikan eksekusi fungsi
					}
				}

				$.ajax({
					type: 'GET',
					url: "{{ url('lbhijau/hapuskan') }}",
					data: {
						id: id,
						kdbrgtab2_all: kdbrgtab2_all,
						nobukti: nobukti,
						nobukti2: nobukti2
					},
					success: function(resp) {
						if (id != 0) {
							alert('HS berhasil dihapus!');
							updateHiddenField();
						} else if (id == 0) {
							alert('HS berhasil dihapus!');
							// Jika resp.id == 0, hapus seluruh isi tabel
							$('#datatable_detail').empty();
							updateHiddenField();
						}
					},
					error: function(xhr) {
						alert('Terjadi kesalahan: ' + xhr.responseText);
					}
				});
			}

			function updateHiddenField() {
				let kdBrgArray = [];
				document.querySelectorAll(".KD_BRGTAB2").forEach((input, index) => {
					kdBrgArray.push(`${input.value}`);
				});

				document.getElementById("KDBRGTAB2_ALL").value = kdBrgArray.join(",");
			}
		</script>
		<!--
	<script src="autonumeric.min.js" type="text/javascript"></script>
	<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.5.4"></script>
	<script src="https://unpkg.com/autonumeric"></script> -->
	@endsection
