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
			<h1 class="m-0">Export-Import SQL</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Export-Import SQL</li>
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
					<form method="GET" action="{{url('expim')}}">
					@csrf
						
						<div class="form-group row">
							<div class="col-md-2">
								<input class="form-control date tglDr" id="tglDr" name="tglDr" type="text" autocomplete="off" value="{{ session()->get('filter_tglDari') }}">
							</div>
						</div>
					
						<div class="form-group row">
							<div class="col-md-auto">
								<button type="submit" name="export" class="btn btn-secondary">Export</button>
							</div>

							<div class="col-md-auto">
								<button type="submit" name="import" class="btn btn-secondary">Import</button>
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
<script src="{{asset('foxie_js_css/bootstrap.bundle.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('.date').datepicker({
            dateFormat: 'dd-mm-yy'
        });
    });
</script>