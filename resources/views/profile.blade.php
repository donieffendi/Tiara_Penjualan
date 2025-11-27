@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
	<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0">Profile User</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
			<li class="breadcrumb-item active">Profile User</li>
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
				<div class="col-lg-12">
					<ul class="nav nav-tabs">
						<li class="nav-item active">
							<a class="nav-link active" href="#" id="gantipass" data-toggle="tab">Ganti Password</a>
						</li>
					</ul>
				</div>
				<div class="tab-content col-lg-12">
					<div id="gantipass" class="tab-pane active">
						<div class="card">
							<div class="card-body">
								<form method="POST" id="entri" action="{{url('profile/update')}}">
								@csrf
									<div class="form-group row">
										<label for="inputpassword" class="col-md-3 control-label">
											Password
											<label style="color:red;font-size:20px">* </label>
										</label>
										<div class="col-md-9">
											<div class="input-group">
														<span class="input-group-append">
															<span class="input-group-text"><i class="livicon" data-name="key" data-size="16" data-loop="true" data-c="#000" data-hc="#000"></i></span>
														</span>
												<input type="password" id="password" placeholder="Password" name="password" class="form-control"/>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<label for="inputnumber" class="col-md-3 control-label">
											Konfirmasi Password
											<label style="color:red;font-size:20px">* </label>
										</label>
										<div class="col-md-9">
											<div class="input-group">
														<span class="input-group-append">
															<span class="input-group-text"><i class="livicon" data-name="key" data-size="16" data-loop="true" data-c="#000" data-hc="#000"></i></span>
														</span>
												<input type="password" id="password-confirm" placeholder="Confirm Password" name="confirm_password" class="form-control"/>
											</div>
										</div>
									</div>
									
									<div class="form-group row">
										<div class="col-md-9 ml-auto">
											<button class="btn btn-primary" type="submit" id="change-password">Simpan</button>
											<a type="button" href="{{url('/')}}" class="btn btn-light">Batalkan</a>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<ul class="nav nav-tabs">
						<li class="nav-item active">
							<a class="nav-link active" href="#" id="gantipass" data-toggle="tab">Setting Web</a>
						</li>
					</ul>
				</div>
				<div class="tab-content col-lg-12">
					<div id="gantipass" class="tab-pane active">
						<div class="card">
							<div class="card-body">
								<form method="POST" id="entri2" action="{{url('profile/setting/update')}}">
								@csrf
									<div class="form-group row">
										<label for="inputpassword" class="col-md-3 control-label">
											Jenis Huruf
											<label style="color:red;font-size:20px">* </label>
										</label>
										<div class="col-md-9">
											<div class="input-group">
														<span class="input-group-append">
															<span class="input-group-text"><i class="livicon" data-name="key" data-size="16" data-loop="true" data-c="#000" data-hc="#000"></i></span>
														</span>
												<select class="form-control" id="font_family" name="font_family">
													 <option value="Arial, sans-serif">Arial</option>
														<option <?php if(Auth::user()->font_family =="Helvetica, sans-serif") { echo "selected"; } ?> value="Helvetica, sans-serif">Helvetica</option>
														<option <?php if(Auth::user()->font_family =="Tahoma, sans-serif") { echo "selected"; } ?> value="Tahoma, sans-serif">Tahoma</option>
														<option <?php if(Auth::user()->font_family =="Verdana, sans-serif") { echo "selected"; } ?> value="Verdana, sans-serif">Verdana</option>
														<option <?php if(Auth::user()->font_family =="Georgia, serif") { echo "selected"; } ?> value="Georgia, serif">Georgia</option>
														<option <?php if(Auth::user()->font_family =="Times New Roman, serif") { echo "selected"; } ?> value="Times New Roman, serif">Times New Roman</option>
														<option <?php if(Auth::user()->font_family =="Courier New, monospace") { echo "selected"; } ?> value="Courier New, monospace">Courier New</option>
														<option <?php if(Auth::user()->font_family =="Arial Black, sans-serif") { echo "selected"; } ?> value="Arial Black, sans-serif">Arial Black</option>
														<option <?php if(Auth::user()->font_family =="Impact, sans-serif") { echo "selected"; } ?> value="Impact, sans-serif">Impact</option>
												</select>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<label for="inputnumber" class="col-md-3 control-label">
											Ukuran Huruf
											<label style="color:red;font-size:20px">* </label>
										</label>
										<div class="col-md-9">
											<div class="input-group">
														<span class="input-group-append">
															<span class="input-group-text"><i class="livicon" data-name="key" data-size="16" data-loop="true" data-c="#000" data-hc="#000"></i></span>
														</span>
												<input type="number" min="1" max="100" id="font_size" value="<?php echo Auth::user()->font_size;?>" placeholder="" name="font_size" class="form-control"/>
											</div>
										</div>
									</div>
									
									<div class="form-group row">
										<div class="col-md-9 ml-auto">
											<button class="btn btn-primary" type="submit" id="change-setting">Simpan</button>
											<a type="button" href="{{url('/')}}" class="btn btn-light">Batalkan</a>
										</div>
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
@endsection

@section('javascripts')
<script>
	$(document).ready(function () {
		$('#change-password').click(function (e) {
			e.preventDefault();
			var check = false;
			if ($('#password').val() ===""){
				alert('Password belum diisi');
			}
			else if  ($('#password').val() !== $('#password-confirm').val()) {
				alert("Password konfirmasi tidak sama");
			}
			else if  ($('#password').val() === $('#password-confirm').val()) {
				check = true;
			}

			if(check == true){
				document.getElementById("entri").submit();
			}
		});
		
		$('#change-setting').click(function (e) {
			e.preventDefault();
			var check = false;
			if ($('#font_family').val() ===""){
				alert('Jenis Huruf belum diisi');
			}
			else if ($('#font-size').val() ===""){
				alert('Ukuran Huruf belum diisi');
			}
			else{
				document.getElementById("entri2").submit();
			}
		});
	});
</script>
@endsection