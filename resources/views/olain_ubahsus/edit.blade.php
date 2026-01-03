@extends('layouts.plain')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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

	/* menghilangkan padding */
	.content-header {
		padding: 0 !important;
	}

	.big-checkbox {
		transform: scale(2);
	}

</style>

@section('content')
<!--
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dropdown with Select2</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
</head> -->


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
                    <form action="{{($tipx=='new')? url('/ubahsus/store/') : url('/ubahsus/update/'.$header[0]->NO_BUKTI ) }}" method="POST" name ="entri" id="entri" >

                        @csrf
                        <div class="tab-content mt-3">

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

                            <div class="form-group row">
                                <div class="col-md-1" align="right">
                                    <label for="NO_BUKTI" class="form-label">Bukti#</label>
                                </div>


                                   <input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
                                    placeholder="Masukkan NO_ID" value="{{$header[0]->NO_ID ?? 0}}" hidden readonly>

									<input name="tipx" class="form-control tipx" id="tipx" value="{{$tipx}}" hidden>

                                <div class="col-md-2">
                                    <input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI"
                                    placeholder="Masukkan Bukti#" value="{{$header[0]->NO_BUKTI ?? ''}}" readonly>
                                </div>

								<div class="col-md-1" align="right">
									<label for="SUB1" class="form-label">Sub/Item</label>
								</div>
								<div class="col-md-1">
									<input type="text" class="form-control SUB1" id="SUB1" name="SUB1"
									placeholder="Masukkan Sub">
								</div>

								<div class="col-md-1" align="right">
									<label for="RAK" class="form-label">Rak</label>
								</div>
								<div class="col-md-1">
									<input type="text" class="form-control RAK" id="RAK" name="RAK"
									placeholder="Masukkan Sub">
								</div>

								<div class="col-md-1"></div>

								<div class="col-md-1" align="right">
									<label for="USER" class="form-label">User : </label> {{Auth::user()->username}}
								</div>
                            </div>

							<div class="form-group row">
								<div class="col-md-1" align="right">
                                    <label for="TG_SMP" class="form-label">Tanggal</label>
                                </div>
                                <div class="col-md-2">
								  <input class="form-control" id="TG_SMP" name="TG_SMP" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header[0]->TG_SMP ?? now()))}}" readonly>
                                </div>

								<div class="col-md-1" align="right">
									<label for="SUB2" class="form-label">s/d</label>
								</div>
								<div class="col-md-1">
									<input type="text" class="form-control SUB2" id="SUB2" name="SUB2"
									placeholder="Masukkan Sub">
								</div>

								<div class="col-md-3"></div>

								<div class="col-md-2">
									<input type="checkbox" class="form-check-input big-checkbox" id="POSTED"name="POSTED" placeholder="Masukkan Aktif/Tidak" value="1" {{ (!empty($header) && $header[0]->POSTED == 1) ? 'checked' : '' }}>
									<label for="POSTED">Posted</label>
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-1" align="right">
									<label for="NOTES" class="form-label">Notes</label>
								</div>
								<div class="col-md-2">
									<input type="text" class="form-control NOTES" id="NOTES" name="NOTES" placeholder="Masukkan Keterangan" value="{{$header[0]->NOTES ?? ''}}">
								</div>
								<div class="col-md-1"></div>
								<div class="col-md-1">
									<button class="btn btn-primary" type="button" id="AMBIL" class="AMBIL" name="AMBIL" onclick="getSod()">Ambil</button>
								</div>
								<div class="col-md-1"></div>
								<div class="col-md-3">
									<button class="btn btn-danger" type="button" id="HAPUS" class="HAPUS" name="HAPUS">Hapus Detail</button>
								</div>
							</div>

							<!-- loader tampil di modal  -->
							<div class="loader" style="z-index: 1055;" id='LOADX' ></div>


                        <div class="tab-content mt-3">

                            <table id="datatable" class="table table-striped table-border">
                                <thead>
                                    <tr>
										<th width="50px" style="text-align: center;">No.</th>
                                        <th width="150px" style="text-align: center;">
									       <label style="color:red;font-size:20px">* </label>
                                           <label for="KD_BRG" class="form-label">Sub Item</label></th>
                                        <th width="550px" style="text-align: center;">Nama Barang</th>
                                        <th width="150px" style="text-align: center;">Ukuran</th>
                                        <th width="150px" style="text-align: center;">Kemasan</th>
                                        <th width="75px" style="text-align: right;">Panjang</th>
                                        <th width="75px" style="text-align: right;">Lebar</th>
                                        <th width="75px" style="text-align: right;">Tinggi</th>
                                        <th width="75px" style="text-align: right;">P.Rak (cm)</th>
                                        <th width="75px" style="text-align: right;">DTR Khusus</th>
                                        <th width="75px" style="text-align: right;">Susun</th>
                                        <th width="75px" style="text-align: right;">Muka</th>
                                        <th width="75px" style="text-align: right;">DTR 1 Muka</th>
                                        <th width="75px" style="text-align: right;">DTR 1 Manual</th>
                                        <th width="75px" style="text-align: center;">KLK</th>
                                        <th width="75px" style="text-align: center;">LPH</th>
                                        <th width="75px" style="text-align: right;">Kapasitas Rak</th>
                                        <th width="75px" style="text-align: right;">Keperluan</th>
                                        <th width="75px" style="text-align: right;">Keperluan Bulat</th>
                                        <th width="75px" style="text-align: right;">DTR Ori</th>
                                        <th width="75px" style="text-align: right;">DTR Lama</th>
                                        <th width="75px" style="text-align: right;">DTR Baru</th>
                                        <th width="75px" style="text-align: right;">DTR2</th>
                                        <th width="75px" style="text-align: right;">S min</th>
                                        <th width="75px" style="text-align: right;">S max</th>
                                        <th></th>
                                    </tr>
                                </thead>

								<tbody id="detailSod">
								<?php $no=0 ?>
								
								@foreach ($header ?? [[]] as $key => $item)
									<tr>
                                        <td>
                                            <input type="hidden" name="NO_ID[]{{$no}}" id="NO_ID" type="text" value="{{$item->NO_ID ?? 0}}"
                                            class="form-control NO_ID" onkeypress="return tabE(this,event)" readonly>

                                            <input name="REC[]" id="REC{{$no}}" type="text" value="{{$no+1}}" class="form-control REC" onkeypress="return tabE(this,event)" readonly style="text-align:center">
                                        </td>


										<td>
                                            <input name="KD_BRG[]" id="KD_BRG{{$no}}" type="text" class="form-control KD_BRG "
											value="{{$item->KD_BRG ?? ''}}" onkeyup="loadDataBBarang({{$no}})" readonly>
                                        </td>

										<td>
                                            <input name="NA_BRG[]" id="NA_BRG{{$no}}" type="text" class="form-control NA_BRG " value="{{$item->NA_BRG ?? ''}}" readonly>
                                        </td>

										<td>
                                            <input name="KET_UK[]" id="KET_UK{{$no}}" type="text" class="form-control KET_UK " value="{{$item->KET_UK ?? ''}}" readonly>
                                        </td>

										<td>
                                            <input name="KET_KEM[]" id="KET_KEM{{$no}}" type="text" class="form-control KET_KEM " value="{{$item->KET_KEM ?? ''}}" readonly>
                                        </td>
										
										<td>
											<input name="PANJANG[]" value="{{$item->PANJANG ?? 0}}" id="PANJANG{{$no}}" type="text" style="text-align: right"  class="form-control PANJANG text-primary" readonly>
										</td>

										<td>
											<input name="LEBAR[]" value="{{$item->LEBAR ?? 0}}" id="LEBAR{{$no}}" type="text" style="text-align: right"  class="form-control LEBAR text-primary" readonly>
										</td>

										<td>
											<input name="TINGGI[]" value="{{$item->TINGGI ?? 0}}" id="TINGGI{{$no}}" type="text" style="text-align: right"  class="form-control TINGGI text-primary" readonly>
										</td>

										<td>
											<input name="PANJANG_SHELF[]" value="{{$item->PANJANG_SHELF ?? 0}}" id="PANJANG_SHELF{{$no}}" type="text" style="text-align: right"  class="form-control PANJANG_SHELF text-primary">
										</td>

										<td>
											<input name="DTRK[]" value="0" id="DTRK{{$no}}" type="text" style="text-align: right"  class="form-control DTRK text-primary">
										</td>

										<td>
											<input name="SUSUN[]" value="{{$item->SUSUN ?? 0}}" id="SUSUN{{$no}}" type="text" style="text-align: right"  class="form-control SUSUN text-primary" readonly>
										</td>

										<td>
											<input name="MUKA[]" value="{{$item->MUKA ?? 0}}" id="MUKA{{$no}}" type="text" style="text-align: right"  class="form-control MUKA text-primary" readonly>
										</td>

										<td>
											<input name="DTR_1M[]" value="{{$item->DTR_1M ?? 0}}" id="DTR_1M{{$no}}" type="text" style="text-align: right"  class="form-control DTR_1M text-primary" readonly>
										</td>

										<td>
											<input name="DTR_MANUAL[]" value="{{$item->DTR_MANUAL ?? 0}}" id="DTR_MANUAL{{$no}}" type="text" style="text-align: right"  class="form-control DTR_MANUAL text-primary" readonly>
										</td>

										<td>
                                            <input name="KLK[]" id="KLK{{$no}}" type="text" class="form-control KLK " value="{{$item->KLK ?? ''}} " readonly>
                                        </td>

										<td>
                                            <input name="LPH[]" id="LPH{{$no}}" type="text" class="form-control LPH " value="{{$item->LPH ?? ''}}" readonly>
                                        </td>

										<td>
											<input name="KAPRAK[]" value="{{$item->KAPRAK ?? 0}}" id="KAPRAK{{$no}}" type="text" style="text-align: right"  class="form-control KAPRAK text-primary" >
										</td>

										<td>
											<input name="PERLU[]" value="{{$item->PERLU ?? 0}}" id="PERLU{{$no}}" type="text" style="text-align: right"  class="form-control PERLU text-primary">
										</td>

										<td>
											<input name="PERLUB[]" value="{{$item->PERLUB ?? 0}}" id="PERLUB{{$no}}" type="text" style="text-align: right"  class="form-control PERLUB text-primary">
										</td>

										<td>
											<input name="DTR_ORI[]" value="{{$item->DTR_ORI ?? 0}}" id="DTR_ORI{{$no}}" type="text" style="text-align: right"  class="form-control DTR_ORI text-primary">
										</td>

										<td>
											<input name="DTR_LAMA[]" value="{{$item->DTR_LAMA ?? 0}}" id="DTR_LAMA{{$no}}" type="text" style="text-align: right"  class="form-control DTR_LAMA text-primary" readonly>
										</td>

										<td>
											<input name="DTR[]" value="{{$item->DTR ?? 0}}" id="DTR{{$no}}" type="text" style="text-align: right"  class="form-control DTR text-primary">
										</td>

										<td>
											<input name="DTR2[]" value="{{$item->DTR2 ?? 0}}" id="DTR2{{$no}}" type="text" style="text-align: right"  class="form-control DTR2 text-primary" readonly>
										</td>

										<td>
											<input name="SMIN[]" value="{{$item->SMIN ?? 0}}" id="SMIN{{$no}}" type="text" style="text-align: right"  class="form-control SMIN text-primary" readonly>
										</td>

										<td>
											<input name="SMAX[]" value="{{$item->SMAX ?? 0}}" id="SMAX{{$no}}" type="text" style="text-align: right"  class="form-control SMAX text-primary" readonly>
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
                                    {{-- <td><input class="form-control TTOTAL_QTY  text-primary font-weight-bold" style="text-align: right"  id="TTOTAL_QTY" name="TTOTAL_QTY" value="{{$header->TOTAL_QTY ?? 0}}" readonly></td> --}}
                                	<td></td>
								</tfoot>
                            </table>

                            {{-- <div class="col-md-2 row">
                               <a type="button" id='PLUSX' onclick="tambah()" class="fas fa-plus fa-sm md-3" style="font-size: 20px" ></a>

							</div> --}}

					</div>
                        </div>

                        <hr style="margin-top: 30px; margin-buttom: 30px">
						<!-- dari sini shelvi-->

						<!-- sampai sini shelvi-->

						<div class="mt-3 col-md-12 form-group row">
							<div class="col-md-4">
								<button hidden type="button" id='TOPX'  onclick="location.href='{{url('/ubahsus/edit/?idx=' .$idx. '&tipx=top')}}'" class="btn btn-outline-primary">Top</button>
								<button hidden type="button" id='PREVX' onclick="location.href='{{url('/ubahsus/edit/?idx='.($header[0]->NO_ID ?? 0).'&tipx=prev&buktix='.($header[0]->NO_BUKTI ?? '') )}}'" class="btn btn-outline-primary">Prev</button>
								<button hidden type="button" id='NEXTX' onclick="location.href='{{url('/ubahsus/edit/?idx='.($header[0]->NO_ID ?? 0).'&tipx=next&buktix='.($header[0]->NO_BUKTI ?? '') )}}'" class="btn btn-outline-primary">Next</button>
								<button hidden type="button" id='BOTTOMX' onclick="location.href='{{url('/ubahsus/edit/?idx=' .$idx. '&tipx=bottom')}}'" class="btn btn-outline-primary">Bottom</button>
							</div>
							<div class="col-md-5">
								<button hidden type="button" id='NEWX' onclick="location.href='{{url('/ubahsus/edit/?idx=0&tipx=new')}}'" class="btn btn-warning">New</button>
								<button hidden type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button hidden type="button" id='UNDOX' onclick="location.href='{{url('/ubahsus/edit/?idx=' .$idx. '&tipx=undo' )}}'" class="btn btn-info">Undo</button> 
								<button type="button" id='SAVEX' onclick='simpan()' class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button hidden type="button" id='HAPUSX' hidden onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								
								<!-- <button type="button" id='CLOSEX'  onclick="location.href='{{url('/ubahsus' )}}'" class="btn btn-outline-secondary">Close</button> -->

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


@endsection

@section('footer-scripts')
<script src="{{ asset('js/autoNumerics/autoNumeric.min.js') }}"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>

<!-- tambahan untuk sweetalert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- tutupannya -->

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


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
				url: '{{url('zsup/browse')}}',
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
					// console.log("KD_BRG"+nomor);
					// document.getElementById("KD_BRG"+nomor).focus();
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
			// tambah();
		}

        if ( $tipx != 'new' )
		{
			ganti();
		}


		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {

			$("#PANJANG" + i.toString()).autoNumeric('init', {mDec: '0', aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#LEBAR" + i.toString()).autoNumeric('init', {mDec: '0', aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#TINGGI" + i.toString()).autoNumeric('init', {mDec: '0', aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#PANJANG_SHELF" + i.toString()).autoNumeric('init', {mDec: '0', aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
			$("#PERLUB" + i.toString()).autoNumeric('init', {mDec: '0', aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
		}


        $('body').on('click', '.btn-delete', function() {
			var val = $(this).parents("tr").remove();
			baris--;
			nomor();

		});

		$('.date').datepicker({
            dateFormat: 'dd-mm-yy'
		});


		//////////////////////////////////////////////////////


		//////////////////////////////////////////////////////

		var dTableBBarang;
		var rowidBarang;
		loadDataBBarang = function(urut){
			rowidBarang = urut;
			$.ajax(
			{
				type: 'GET',
				url: "{{url('brg/browse_ubahsus1')}}",
				data: {
					'KD_BRG': $("#KD_BRG"+rowidBarang).val(),
				},

				async : false,

				success: function( response )

				{

					resp = response;
					
						$("#KD_BRG"+rowidBarang).val(resp[0].KD_BRG);
						$("#BARCODE"+rowidBarang).val(resp[0].BARCODE);
						$("#NMBAR"+rowidBarang).val(resp[0].NA_BRG);
						$("#KET_UK"+rowidBarang).val(resp[0].KET_UK);
					
				}
			});
		}

		////////////////////////////////////////////////////

		var dTableBBarcode;
		var rowidBarcode;
		loadDataBBarcode = function(urut){
			rowidBarcode = urut;
			$.ajax(
			{
				type: 'GET',
				url: "{{url('brg/browse_ubahsus2')}}",
				data: {
					'BARCODE': $("#BARCODE"+rowidBarcode).val(),
				},

				async : false,

				success: function( response )

				{

					resp = response;
			
						$("#BARCODE"+rowidBarcode).val(resp[0].BARCODE);
						$("#KD_BRG"+rowidBarcode).val(resp[0].KD_BRG);
						$("#NMBAR"+rowidBarcode).val(resp[0].NA_BRG);
						$("#KET_UK"+rowidBarcode).val(resp[0].KET_UK);
				}
			});
		}

		////////////////////////////////////////////////////
	});

	function getSod() {

		var mulai = (idrow == baris) ? idrow - 1 : idrow;

		$.ajax({
			type: 'GET',
			url: "{{url('ubahsus/ambil-detail')}}",
			data: {
				SUB1: $("#SUB1").val(),
				SUB2: $("#SUB2").val(),
			},
			success: function(resp) {
				var html = '';
				for (i = 0; i < resp.length; i++) {
					html += `<tr>
								<td><input name='REC[]' id='REC${i}' value=${resp[i].REC+1} type='text' class='REC form-control' onkeypress='return tabE(this,event)' readonly></td>
								<td><input name='KD_BRG[]' data-rowid=${i} id='KD_BRG${i}' value="${resp[i].KD_BRG}" type='text' class='form-control KD_BRG' readonly></td>
								<td><input name='NA_BRG[]' data-rowid=${i} id='NA_BRG${i}' value="${resp[i].NA_BRG}" type='text' class='form-control  NA_BRG' readonly></td>
								<td><input name='KET_UK[]' data-rowid=${i} id='KET_UK${i}' value="${resp[i].KET_UK}" type='text' class='form-control  KET_UK' placeholder="Satuan"  readonly></td>
								<td><input name='KET_KEM[]' data-rowid=${i} id='KET_KEM${i}' value="${resp[i].KET_KEM}" type='text' class='form-control  KET_KEM' placeholder="Satuan"  readonly></td>
								<td>
									<input name='PANJANG[]' onclick='select()' onkeyup='hitung()' id='PANJANG${i}' value="${resp[i].PANJANG}" type='text' style='text-align: right' class='form-control PANJANG text-primary' readonly>
								</td>

								<td>
									<input name='LEBAR[]' onclick='select()' onkeyup='hitung()' id='LEBAR${i}' value="${resp[i].LEBAR}" type='text' style='text-align: right' class='form-control LEBAR text-primary' readonly>
								</td>

								<td>
									<input name='TINGGI[]' onclick='select()' onkeyup='hitung()' id='TINGGI${i}' value="${resp[i].TINGGI}" type='text' style='text-align: right' class='form-control TINGGI text-primary' readonly>
								</td>

								<td>
									<input name='PANJANG_SHELF[]' onclick='select()' onkeyup='hitung()' id='PANJANG_SHELF${i}' value="${resp[i].PANJANG_SHELF}" type='text' style='text-align: right' class='form-control PANJANG_SHELF text-primary' readonly>
								</td>

								<td>
									<input name='DTRK[]' onclick='select()' onkeyup='hitung()' id='DTRK${i}' value="0" type='text' style='text-align: right' class='form-control DTRK text-primary'>
								</td>

								<td>
									<input name='SUSUN[]' onclick='select()' onkeyup='hitung()' id='SUSUN${i}' value="${resp[i].SUSUN}" type='text' style='text-align: right' class='form-control SUSUN text-primary' readonly>
								</td>

								<td>
									<input name='MUKA[]' onclick='select()' onkeyup='hitung()' id='MUKA${i}' value="${resp[i].MUKA}" type='text' style='text-align: right' class='form-control MUKA text-primary' readonly>
								</td>

								<td>
									<input name='DTR_1M[]' onclick='select()' onkeyup='hitung()' id='DTR_1M${i}' value="${resp[i].DTR_1M}" type='text' style='text-align: right' class='form-control DTR_1M text-primary' readonly>
								</td>

								<td>
									<input name='DTR_MANUAL[]' onclick='select()' onkeyup='hitung()' id='DTR_MANUAL${i}' value="${resp[i].DTR_MANUAL}" type='text' style='text-align: right' class='form-control DTR_MANUAL text-primary' readonly>
								</td>
								<td><input name='KLK[]' data-rowid=${i} id='KLK${i}' value="${resp[i].KLK}" type='text' class='form-control  KLK' placeholder="Satuan"  readonly></td>
								<td><input name='LPH[]' data-rowid=${i} id='LPH${i}' value="${resp[i].LPH}" type='text' class='form-control  LPH' placeholder="Satuan"  readonly></td>
								<td>
									<input name='KAPRAK[]' onclick='select()' onkeyup='hitung()' id='KAPRAK${i}' value="0" type='text' style='text-align: right' class='form-control KAPRAK text-primary'>
								</td>
								<td>
									<input name='PERLU[]' onclick='select()' onkeyup='hitung()' id='PERLU${i}' value="0" type='text' style='text-align: right' class='form-control PERLU text-primary'>
								</td>
								<td>
									<input name='PERLUB[]' onclick='select()' onkeyup='hitung()' id='PERLUB${i}' value="0" type='text' style='text-align: right' class='form-control PERLUB text-primary'>
								</td>
								<td>
									<input name='DTR_ORI[]' onclick='select()' onkeyup='hitung()' id='DTR_ORI${i}' value="${resp[i].DTR_ORI}" type='text' style='text-align: right' class='form-control DTR_ORI text-primary' readonly>
								</td>
								<td>
									<input name='DTR_LAMA[]' onclick='select()' onkeyup='hitung()' id='DTR_LAMA${i}' value="${resp[i].DTR_LAMA}" type='text' style='text-align: right' class='form-control DTR_LAMA text-primary' readonly>
								</td>
								<td>
									<input name='DTR[]' onclick='select()' onkeyup='hitung()' id='DTR${i}' value="0" type='text' style='text-align: right' class='form-control DTR text-primary'>
								</td>
								<td>
									<input name='DTR2[]' onclick='select()' onkeyup='hitung()' id='DTR2${i}' value="${resp[i].DTR2}" type='text' style='text-align: right' class='form-control DTR2 text-primary' readonly>
								</td>
								<td>
									<input name='SMIN[]' onclick='select()' onkeyup='hitung()' id='SMIN${i}' value="${resp[i].SMIN}" type='text' style='text-align: right' class='form-control SMIN text-primary' readonly>
								</td>
								<td>
									<input name='SMAX[]' onclick='select()' onkeyup='hitung()' id='SMAX${i}' value="${resp[i].SMAX}" type='text' style='text-align: right' class='form-control SMAX text-primary' readonly>
								</td>
								<td><button type='button' class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button></td>
							</tr>`;
				}
				$('#detailSod').html(html);

				$(".PANJANG").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".PANJANG").autoNumeric('update');

				$(".LEBAR").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".LEBAR").autoNumeric('update');

				$(".TINGGI").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".TINGGI").autoNumeric('update');

				$(".PANJANG_SHELF").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".PANJANG_SHELF").autoNumeric('update');

				$(".SUSUN").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".SUSUN").autoNumeric('update');

				$(".MUKA").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".MUKA").autoNumeric('update');

				$(".DTR_1M").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".DTR_1M").autoNumeric('update');

				$(".DTR_MANUAL").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".DTR_MANUAL").autoNumeric('update');
				
				$(".KAPRAK").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".KAPRAK").autoNumeric('update');

				$(".PERLU").autoNumeric('init', {
					mDec: '2',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".PERLU").autoNumeric('update');

				$(".DTR_ORI").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".DTR_ORI").autoNumeric('update');

				$(".DTR_LAMA").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".DTR_LAMA").autoNumeric('update');

				$(".DTR").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".DTR").autoNumeric('update');

				$(".DTR2").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".DTR2").autoNumeric('update');

				$(".SMIN").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".SMIN").autoNumeric('update');

				$(".SMAX").autoNumeric('init', {
					mDec: '0',
					aSign: '<?php echo ''; ?>',
					vMin: '-999999999.99'
				});
				$(".SMAX").autoNumeric('update');

				idrow = resp.length;
				baris = resp.length;

				nomor();
				hitung();
			}
		});
	}


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

		var tgl = $('#TG_SMP').val();
		var bulanPer = {{session()->get('periode')['bulan']}};
		var tahunPer = {{session()->get('periode')['tahun']}};

        var check = '0';

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


			if ( tgl.substring(3,5) != bulanPer )
			{

				check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Bulan tidak sama dengan Periode'
				});
				return; // Stop function execution
				alert("Bulan tidak sama dengan Periode");
			}


			if ( tgl.substring(tgl.length-4) != tahunPer )
			{
				check = '1';
				Swal.fire({
					icon: 'warning',
					title: 'Warning',
					text: 'Tahun tidak sama dengan Periode'
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
			$("#NOTES").attr("readonly", false);
			$("#TTOTAL_QTY").attr("readonly", true);
	    	$("#KODES").attr("disabled", false);
	        $("#PKP").attr("disabled", true);


		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#KD_BRG" + i.toString()).attr("readonly", true);
			$("#BARCODE" + i.toString()).attr("readonly", false);
			$("#NMBAR" + i.toString()).attr("readonly", true);
			$("#KET_UK" + i.toString()).attr("readonly", true);
			$("#QTY" + i.toString()).attr("readonly", false);
			$("#HARGA" + i.toString()).attr("readonly", false);
			$("#TOTAL" + i.toString()).attr("readonly", true);
			$("#DPP" + i.toString()).attr("readonly", true);
			$("#PPN" + i.toString()).attr("readonly", true);
			$("#KET" + i.toString()).attr("readonly", false);
			$("#DELETEX" + i.toString()).attr("hidden", false);

			$tipx = $('#tipx').val();
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
		$("#NOTES").attr("readonly", true);
		$("#KODES").attr("disabled", true);
		$("#PKP").attr("disabled", true);
		$("#TTOTAL_QTY").attr("readonly", true);


		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#REC" + i.toString()).attr("readonly", true);
			$("#KD_BRG" + i.toString()).attr("readonly", true);
			$("#NA_BRG" + i.toString()).attr("readonly", true);
			$("#SATUAN" + i.toString()).attr("readonly", true);
			$("#QTYC" + i.toString()).attr("readonly", true);
			$("#QTYR" + i.toString()).attr("readonly", true);
			$("#QTY" + i.toString()).attr("readonly", true);
			$("#HARGA" + i.toString()).attr("readonly", true);
			$("#TOTAL" + i.toString()).attr("readonly", true);
			$("#DPP" + i.toString()).attr("readonly", true);
			$("#PPN" + i.toString()).attr("readonly", true);
			$("#KET" + i.toString()).attr("readonly", true);

			$("#DELETEX" + i.toString()).attr("hidden", true);
		}



	}


	function kosong() {

		 $('#NO_BUKTI').val("+");
		 $('#NOTES').val("");
		 $('#TTOTAL_QTY').val("0.00");

		var html = '';
		$('#detailx').html(html);

	}

	// sweetalert untuk tombol hapus dan close

	function hapusTrans() {
		let text = "Hapus Transaksi "+$('#NO_BUKTI').val()+"?";

		var loc ='';

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
	            	loc = "{{ url('/ubahsus/delete/'.($header[0]->NO_ID ?? 0)) }}";

		            // alert(loc);
	            	window.location = loc;

				});
			}
		});
	}

	function closeTrans() {
		console.log("masuk");
		var loc ='';

		Swal.fire({
			title: 'Are you sure?',
			text: 'Do you really want to close this page? Unsaved changes will be lost.',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes, close it',
			cancelButtonText: 'No, stay here'
		}).then((result) => {
			if (result.isConfirmed) {
	        	loc = "{{ url('/ubahsus/') }}";
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

		var cari = $("#CARI").val();
		var loc = "{{ url('/ubahsus/edit/') }}" + '?idx={{ $header[0]->NO_ID ?? 0}}&tipx=search&buktix=' +encodeURIComponent(cari);
		window.location = loc;

	}


    // function tambah() {

    //     var x = document.getElementById('datatable').insertRow(baris + 1);

	// 	html=`<tr>

    //             <td>
 	// 				<input name='NO_ID[]' id='NO_ID${idrow}' type='hidden' class='form-control NO_ID' value='new' readonly>
	// 				<input name='REC[]' id='REC${idrow}' type='text' class='REC form-control' onkeypress='return tabE(this,event)' readonly>
	//             </td>

    //             <td>
	// 			    <input name='KD_BRG[]' data-rowid=${idrow} onkeyup='loadDataBBarang(${idrow})' id='KD_BRG${idrow}' type='text' class='form-control  KD_BRG' >
    //             </td>

	// 			<td>
	// 			    <input name='BARCODE[]' data-rowid=${idrow} onkeyup='loadDataBBarcode(${idrow})' id='BARCODE${idrow}' type='text' class='form-control  BARCODE' required>
    //             </td>

    //             <td>
	// 			    <input name='NMBAR[]'   id='NMBAR${idrow}' type='text' class='form-control  NMBAR' required readonly>
    //             </td>

    //             <td>
	// 			    <input name='KET_UK[]'   id='KET_UK${idrow}' type='text' class='form-control  KET_UK' readonly required>
    //             </td>

	// 			<td>
	// 	            <input name='QTY[]' value='0' id='QTY${idrow}' type='text' style='text-align: right' class='form-control QTY text-primary' >
    //             </td>

    //             <td>
	// 				<button type='button' id='DELETEX${idrow}'  class='btn btn-sm btn-circle btn-outline-danger btn-delete' onclick=''> <i class='fa fa-fw fa-trash'></i> </button>
    //             </td>
    //      </tr>`;

    //     x.innerHTML = html;
    //     var html='';



	// 	jumlahdata = 100;
	// 	for (i = 0; i <= jumlahdata; i++) {

	// 		$("#QTY" + i.toString()).autoNumeric('init', {
	// 			aSign: '<?php echo ''; ?>',
	// 			vMin: '-999999999.99'
	// 		});


	// 	}


    //     idrow++;
    //     baris++;
    //     nomor();

	// 	$(".ronly").on('keydown paste', function(e) {
    //          e.preventDefault();
    //          e.currentTarget.blur();
    //      });
    //  }
</script>
<!--
<script src="autonumeric.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.5.4"></script>
<script src="https://unpkg.com/autonumeric"></script> -->
@endsection
