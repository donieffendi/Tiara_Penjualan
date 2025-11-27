@extends('layouts.main')

<style>
	.bg-card {
		background-repeat: no-repeat;
		background-position: center center;
		background-size: cover;
		background-image: url("img/kedele01.jpg");
		height: 550px;
	}

	.card-header {
		background-color: rgb(244 221 179 / 67%) !important;
	}

	.logo2 {
		max-width: 25%;
	}

	.logo2 img {
		width: 100%;
		height: 100%;
		background-color: rgb(244 221 179 / 67%);
		border-radius: 50%;
		box-shadow: 0px 0px 3px #5f5f5f,
			0px 0px 0px 5px #ecf0f3,
			8px 8px 15px #a7aaa7,
			-8px -8px 15px #f4ddb3;
	}
</style>

@section('content')
	<!-- Content Wrapper. Contains page content -->
	<div class="content-wrapper">
		<!-- Content Header (Page header) -->
		<div class="content-header">
		</div>
		<!-- /.content-header -->

		<!-- Main content -->
		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<div class="card bg-card">

						</div>
						<!-- /.card -->
					</div>
				</div>
				<!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>
		<!-- /.content -->
	</div>
	<!-- /.content-wrapper -->
@endsection
