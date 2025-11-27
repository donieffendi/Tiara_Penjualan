<nav class="main-header navbar navbar-expand navbar-white navbar-light">
	<!-- Left navbar links -->
	<ul class="navbar-nav">
		<li class="nav-item">
			<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
		</li>
		<li class="nav-item d-none d-sm-inline-block">
			<a href="{{ url('/') }}" class="nav-link">Home</a>
		</li>

		<li class="nav-item d-none d-sm-inline-block">
			<a href="javascript:void(0)" data-toggle="modal" data-target="#periodeModal" id="periode" class="nav-link">
				<b>
					Periode
					{{ is_array(session('periode')) ? session('periode')['bulan'] ?? '-' : '-' }}
					/
					{{ is_array(session('periode')) ? session('periode')['tahun'] ?? '-' : '-' }}
				</b>
			</a>
		</li>

	</ul>

	<!-- Right Side Of Navbar -->
	<ul class="navbar-nav ml-auto">
		<!-- Settings Dropdown -->
		@auth
			<x-dropdown id="settingsDropdown">
				<x-slot name="trigger">
					{{ Auth::user()->name . ' - ' . Auth::user()->CBG }}
				</x-slot>

				<x-slot name="content">

					<!-- di ganti pake onclick untuk ganti profile -->

					<x-dropdown-link onclick="addTab('Profile', '{{ url('profile') }}')">
						{{ __('Profil') }}
					</x-dropdown-link>
					<form method="POST" action="{{ route('logout') }}">
						@csrf

						<x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
							{{ __('Log Out') }}
						</x-dropdown-link>
					</form>
				</x-slot>
			</x-dropdown>
		@endauth
	</ul>
</nav>
<!-- /.navbar -->

<div class="modal fade" id="periodeModal" tabindex="-1" role="dialog" aria-labelledby="periodeLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" style="max-width: 250px">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="periodeLabel"> <i class="fas fa-cogs"></i> Ganti Periode</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="post" id="gantiPer" action="{{ url('periode') }}" class="user">
					@csrf
					<div class="form-group">
						<select class="form-control form-control-user" id="bulanPeriode" placeholder="Pilih Bulan..." name="bulan">
							<option value='01'>01</option>
							<option value='02'>02</option>
							<option value='03'>03</option>
							<option value='04'>04</option>
							<option value='05'>05</option>
							<option value='06'>06</option>
							<option value='07'>07</option>
							<option value='08'>08</option>
							<option value='09'>09</option>
							<option value='10'>10</option>
							<option value='11'>11</option>
							<option value='12'>12</option>
						</select>
						<!-- </datalist> -->
					</div>
					<div class="form-group">
						<input type="text" value="{{ session('periode')['tahun'] }}" class="form-control form-control-user" id="tahunPeriode" placeholder="Tahun..."
							name="tahun">
					</div>

					<button type="button" class="btn btn-primary btn-user btn-block" onclick="cekFormatPeriode()">Ubah Periode</button>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
			</form>
		</div>
	</div>
</div>

@section('footer-scripts')
	<script>
		function cekFormatPeriode() {
			var cek = 0;
			var bulanPer = $("#bulanPeriode").val();
			var bulanArr = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
			var tahunPer = $("#tahunPeriode").val();

			if (bulanPer.length != 2) {
				cek = 1;
				alert("Bulan harus 2 digit!");
			}

			if (tahunPer.length != 4) {
				cek = 1;
				alert("Tahun harus 4 digit!");
			}

			if (!(bulanArr.includes(bulanPer))) {
				cek = 1;
				alert("Bulan periode salah!");
			}

			if (cek == '0') {
				document.getElementById("gantiPer").submit();
			}
		}
	</script>
@endsection
