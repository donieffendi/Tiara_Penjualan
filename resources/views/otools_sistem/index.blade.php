@extends('layouts.plain')

@section('content')
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 70px;
  height: 34px;
}

.switch input { display: none; }

.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0;
  right: 0; bottom: 0;
  background-color: #ccc;
  transition: .3s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px; width: 26px;
  left: 4px; bottom: 4px;
  background-color: white;
  transition: .3s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:checked + .slider:before {
  transform: translateX(36px);
}

.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>


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
			<h1 class="m-0">Perpanjangan Waktu SO</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item active">Perpanjangan Waktu SO</li>
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
							<form action="{{ url('sistem/toggle') }}" method="POST">
								@csrf

								{{-- Hidden data --}}
								<input type="hidden" name="statusProgram" value="{{ $statusProgram }}">
								<input type="hidden" name="statusAktif" value="{{ $statusAktif }}">
								<input type="hidden" name="confirm" value="1">

								<div class="text-center mb-3">
									<h3>Status Sekarang:
										<span class="text-primary">
											{{ $toggleState ? 'AKTIF' : 'NON AKTIF' }}
										</span>
									</h3>
								</div>

								{{-- Toggle Button --}}
								<div class="form-group text-center">
									<label style="font-size: 20px;">Perpanjang Waktu SO</label>
									<br>

									<label class="switch">
										<input id="toggleSO" type="checkbox" name="toggle" {{ $toggleState ? 'checked' : '' }}>
										<span class="slider round"></span>
									</label>

									<div id="toggleText" style="font-size: 18px; margin-top: 8px; font-weight: bold;">
										{{ $toggleState ? 'ON' : 'OFF' }}
									</div>
								</div>

								<div class="text-center mt-4">
									<button class="btn btn-primary btn-lg" type="submit">
										Simpan Perubahan
									</button>
								</div>
							</form>
						</div>
					</div>
					<div class="text-center mt-4">
                        <h2>{{ $label1 }}</h2>
                        <h3>{{ $label2 }}</h3>
                    </div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('javascripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('success'))
<script>
    Swal.fire('INFO!', '{{ session('success') }}', 'success');
</script>
@endif

@if ($errors->any())
<script>
    Swal.fire('Error!', '{{ $errors->first() }}', 'error');
</script>
@endif

{{-- SCRIPT UNTUK TOGGLE ON / OFF --}}
<script>
	document.addEventListener('DOMContentLoaded', function() {
		const toggle = document.getElementById('toggleSO');
		const toggleText = document.getElementById('toggleText');

		function updateLabel() {
			toggleText.textContent = toggle.checked ? 'ON' : 'OFF';
			toggleText.style.color = toggle.checked ? 'green' : 'red';
		}

		toggle.addEventListener('change', updateLabel);

		updateLabel(); // set awal
	});
</script>
@endsection


