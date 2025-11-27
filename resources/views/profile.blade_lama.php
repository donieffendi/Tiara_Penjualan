@extends('layouts.main')

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
	});
</script>
@endsection