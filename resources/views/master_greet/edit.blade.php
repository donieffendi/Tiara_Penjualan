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
                    <form action="{{ ($tipx == 'new') ? url('/greet/store') : url('/greet/update') }}"
						method="POST" name="entri" id="entri">

                        @csrf
                        <div class="tab-content mt-3">
							<div class="form-group row">

								{{-- Baris 1 --}}
								<div class="col-md-1 text-right">
									<label class="form-label">Baris 1</label>
								</div>
								<div class="col-md-3">
									<input type="text"
										class="form-control"
										name="KATA[1]"
										value="{{ $tipx == 'new' ? '' : old('KATA.1', $header->KATA[0] ?? '') }}"
										placeholder="Kata untuk baris 1">
								</div>

								{{-- Baris 2 --}}
								<div class="col-md-1 text-right">
									<label class="form-label">Baris 2</label>
								</div>
								<div class="col-md-3">
									<input type="text"
										class="form-control"
										name="KATA[2]"
										value="{{ $tipx == 'new' ? '' : old('KATA.2', $header->KATA[1] ?? '') }}"
										placeholder="Kata untuk baris 2">
								</div>

								{{-- Baris 3 --}}
								<div class="col-md-1 text-right">
									<label class="form-label">Baris 3</label>
								</div>
								<div class="col-md-3">
									<input type="text"
										class="form-control"
										name="KATA[3]"
										value="{{ $tipx == 'new' ? '' : old('KATA.3', $header->KATA[2] ?? '') }}"
										placeholder="Kata untuk baris 3">
								</div>

							</div>
						</div>

					</div>


                        <hr style="margin-top: 30px; margin-buttom: 30px">
						<!-- dari sini shelvi-->

						<!-- sampai sini shelvi-->

						<div class="mt-3 col-md-32 form-group row">
							<div class="col-md-4">
								{{-- <button hidden type="button" id='TOPX'  onclick="location.href='{{url('/greet/edit/?idx=' .$idx. '&tipx=top')}}'" class="btn btn-outline-primary">Top</button>
								<button hidden type="button" id='PREVX' onclick="location.href='{{url('/greet/edit/?idx='.($header[0]->NO_ID ?? 0).'&tipx=prev&buktix='.($header[0]->KATA ?? '') )}}'" class="btn btn-outline-primary">Prev</button>
								<button hidden type="button" id='NEXTX' onclick="location.href='{{url('/greet/edit/?idx='.($header[0]->NO_ID ?? 0).'&tipx=next&buktix='.($header[0]->KATA ?? '') )}}'" class="btn btn-outline-primary">Next</button>
								<button hidden type="button" id='BOTTOMX' onclick="location.href='{{url('/greet/edit/?idx=' .$idx. '&tipx=bottom')}}'" class="btn btn-outline-primary">Bottom</button> --}}
							</div>
							<div class="col-md-5">
								{{-- <button hidden type="button" id='NEWX' onclick="location.href='{{url('/greet/edit/?idx=0&tipx=new')}}'" class="btn btn-warning">New</button>
								<button hidden type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>                    
								<button hidden type="button" id='UNDOX' onclick="location.href='{{url('/greet/edit/?idx=' .$idx. '&tipx=undo' )}}'" class="btn btn-info">Undo</button>  --}}
								<button type="button" id='SAVEX' onclick='simpan()' class="btn btn-success" class="fa fa-save"></i>Save</button>

							</div>
							<div class="col-md-3">
								<button hidden type="button" id='HAPUSX' hidden onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
								
								<!-- <button type="button" id='CLOSEX'  onclick="location.href='{{url('/greet' )}}'" class="btn btn-outline-secondary">Close</button> -->

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
		}

        if ( $tipx != 'new' )
		{
			 ganti();
		}
	});



///////////////////////////////////////


	function simpan() {
		var check = '0';
		var baris = 3; // jumlah baris input
		var isEmpty = true;

		// cek apakah ada input KATA yang terisi
		$("input[name^='KATA']").each(function() {
			if ($(this).val().trim() !== "") {
				isEmpty = false;
			}
		});

		if (isEmpty) {
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'Semua kolom KATA masih kosong! Harap isi minimal satu baris.'
			});
			return;
		}

		if (baris == 0) {
			check = '1';
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'Data detail kosong (Tambahkan 1 baris kosong jika ingin mengosongi detail)'
			});
			return;
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

		$("#LOADX").hide();
	}

    function nomor() {
		var i = 1;
		$(".baris").each(function() {
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

		$("#KATA").attr("readonly", true);
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

	    $(".KATA").attr("readonly", true);
	}


	function kosong() {

		 $('#KATA').val("");
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
	        	loc = "{{ url('/greet/') }}";
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
</script>
@endsection
