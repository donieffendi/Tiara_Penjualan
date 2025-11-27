@extends('layouts.main')

@section('content')
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0">Posting {{$judul}}</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
			<li class="breadcrumb-item active">Posting {{$judul}}</li>
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

	<!-- Main content -->
	<div class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-6">
					<div class="card">
						<div class="card-body">
							<form method="POST" action="{{url('posting/proses')}}">
							@csrf
								<div class="form-group nowrap">
									<input name="jenis" type="hidden" value="{{$jenis}}">
									<label><strong>Transaksi :</strong></label>
									<select name="tabel" id="tabel" class="form-control tabel" style="width: 200px">
										<option value="">--Pilih Transaksi--</option>
										@foreach($ket as $ketD)
											<option value="{{$ketD['tabel']}}">{{$ketD['ket']}}</option>
										@endforeach
									</select>
								</div>
								
								<!-- Filter Tanggal -->
								<div class="form-group row">
									<div class="col-md-4">
										<input class="form-control date tglDr" id="tglDr" name="tglDr"
										type="text" autocomplete="off" value="{{ now()->format('d-m-Y') }}"> 
									</div>
									<div>s.d.</div> 
									<div class="col-md-4">
										<input class="form-control date tglSmp" id="tglSmp" name="tglSmp"
										type="text" autocomplete="off" value="{{ now()->format('d-m-Y') }}">
									</div>
								</div>
								
								<button class="btn btn-danger proses" type="submit" id="proses">Posting</button>
							</form>
							<div style="margin-bottom: 15px;"></div>
						</div>
					</div>
				</div>
				<div class="col-6">
					<div class="card">
						<div class="card-body">
							<form method="POST" action="{{url('posting/bukaposting')}}" id="bukaposting">
							@csrf
								<div class="form-group nowrap">
									<input name="jenis" type="hidden" value="{{$jenis}}">
									<label><strong>Transaksi :</strong></label>
									<select name="tabelbuka" id="tabelbuka" class="form-control tabelbuka" style="width: 200px" onchange="browseBukti()">
										<option value="">--Pilih Transaksi--</option>
										@foreach($ket as $ketD)
											<option value="{{$ketD['tabel']}}">{{$ketD['ket']}}</option>
										@endforeach
									</select>
								</div>
								
								<!-- Filter Tanggal -->
								<div class="form-group row">
									<div class="col-md-2">
										<label class="form-label" style="font-size:18px">Bukti#</label>
									</div>
									<div class="col-md-4">
										<input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI" placeholder="Masukkan Bukti#" onclick="browseBukti()">
									</div>
									<div class="col-md-2">
										<input type="checkbox" id="statusBukti" class="form-control" style="pointer-events: none;">
									</div>
								</div>
								
								<button class="btn btn-success" type="button" onclick="bukaPost()">Buka Posting</button>
							</form>
							<div style="margin-bottom: 15px;"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('javascripts')
<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
<script>
	$(document).ready(function() {
		$('.date').datepicker({  
			dateFormat: 'dd-mm-yy'
		}); 


	});
	
	browseBukti = function(){
		var nobukti = $("#NO_BUKTI").val();
		var tabel = $("#tabelbuka").val()
		var hasilCek='';

		$.ajax(
		{
			type: 'GET',    
			url: "{{url('posting/browsebukti')}}",
			data: {
				'NO_BUKTI': nobukti,
				'TABEL': tabel,
			},
			success: function(resp)
			{
                if (resp.length > 0) {
                    $.each(resp, function(i, item) {
                        hasilCek=resp[i].POSTED;
                    });
                }

				if (hasilCek=='1') {
					$('#statusBukti').prop('checked', true).change();
				}
				else {
					$('#statusBukti').prop('checked', false).change();
				}
			},
			error: function() {
				console.log('Error browsebukti occured');
			}
		});
	}
	
	$("#NO_BUKTI").keyup(function(e){
		if ($("#NO_BUKTI").val().length>7){
			browseBukti();
		}
		else {
			$('#statusBukti').prop('checked', false).change();
		}
	}); 
	
	function bukaPost() {
		browseBukti();
		
		var nobukti = $("#NO_BUKTI").val();
		var tabel = $("#tabelbuka").val()

		if (nobukti!='' && tabel!='' && $('#statusBukti').is(':checked')==true)
		{
			document.getElementById("bukaposting").submit();  
		}
		else if ($('#statusBukti').is(':checked')==false)
		{
			alert("Transaksi " + nobukti + " sudah terbuka..");
		}
		else
		{
			alert("Cek pilihan Transaksi atau No Bukti!");
		}
 	}

</script>
@endsection
