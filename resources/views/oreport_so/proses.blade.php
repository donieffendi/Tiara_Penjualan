@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
	<!-- Status -->
    @if (session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>

        <script>
            Swal.fire({
              title: 'INFO!',
              text: '{{session('status')}}',
              icon: 'success',
              confirmButtonText: 'OK'
            })
        </script>
    @endif 
    <!-- tutupannya -->
	<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0">Posting SO</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Posting SO</li>
			</ol>
		</div>
		</div>
	</div>
	</div>
	
	<div class="content">
		<div class="container-fluid">
		<div class="row">
			<div class="col-12">
			<div class="card">
				<div class="card-body">
					<form method="GET" action="{{url('prosesso')}}">
					@csrf
						<div class="form-group row justify-content-center">
							<div class="col-md-auto">
								<button type="submit" name="ambil" class="btn btn-primary">Ambil Data</button>
							</div>
						</div>

						<div class="form-group row justify-content-center text-center">
							<div class="col-md-auto">
								<h1><strong>{{ $judul1 }}</strong></h1>
							</div>
						</div>

						<div class="form-group row justify-content-center text-center">
							<div class="col-md-auto">
								<h3><strong>{{ $judul2 }}</strong></h3>
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
@endsection

@section('javascripts')

@endsection
