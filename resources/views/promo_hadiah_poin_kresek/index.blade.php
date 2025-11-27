@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">{{ $title }}</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item active">{{ $title }}</li>
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
							<form id="configForm">
								@csrf
								<input type="hidden" name="flag" id="flag" value="{{ $flag }}">

								<div class="row mb-3">
									<div class="col-md-3">
										<label>Cabang</label>
										<select name="cabang" id="cabang" class="form-control" required>
											<option value="">Pilih Cabang</option>
											@foreach ($cbg as $c)
											<option value="{{ $c->kode }}" {{ $selectedCbg == $c->kode ? 'selected' : '' }}>
												{{ $c->kode . ' - ' . $c->na_toko }}
											</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-9 text-right">
										<label>&nbsp;</label><br>
										<button type="submit" class="btn btn-primary">
											<i class="fas fa-save mr-1"></i>SAVE
										</button>
									</div>
								</div>

								<hr>

								<div class="row mb-3">
									<div class="col-md-3">
										<label>Status Poin {{ $flag == 'POIN' ? 'Kresek' : 'EDC' }}</label>
									</div>
									<div class="col-md-9">
										<div class="form-check">
											<input type="checkbox" class="form-check-input" name="status_poin" id="status_poin" value="1"
												{{ isset($config->status_poin) && $config->status_poin == 1 ? 'checked' : '' }}>
											<label class="form-check-label" for="status_poin" id="statusLabel">
												Off
											</label>
										</div>
									</div>
								</div>

								<hr>

								<div class="row mb-3">
									<div class="col-md-2">
										<label>Mode</label>
										<select name="mode" id="mode" class="form-control">
											<option value="Normal">Normal</option>
											<option value="Khusus">Khusus</option>
										</select>
									</div>
									<div class="col-md-2">
										<label>Jam Diskon</label>
										<input type="text" class="form-control text-center" id="jam_diskon" name="jam_diskon" value="{{ $config->jam_diskon ?? '00:00:00' }}"
											placeholder="HH:MM:SS" maxlength="8">
									</div>
									<div class="col-md-8 text-right">
										<label>&nbsp;</label><br>
										<button type="button" class="btn btn-primary" onclick="saveJamDiskon()">
											<i class="fas fa-save mr-1"></i>SAVE
										</button>
									</div>
								</div>

								<hr>

								<div class="row">
									<div class="col-md-2">
										<label>Tgl Mulai</label>
										<select class="form-control" id="tgl_mulai" name="tgl_mulai">
											@for ($i = 1; $i <= 31; $i++)
												<option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
												{{ isset($config->tgl_mulai) && $config->tgl_mulai == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
												{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
												</option>
												@endfor
										</select>
									</div>
									<div class="col-md-2">
										<label>Tgl Selesai</label>
										<select class="form-control" id="tgl_selesai" name="tgl_selesai">
											@for ($i = 1; $i <= 31; $i++)
												<option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
												{{ isset($config->tgl_selesai) && $config->tgl_selesai == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
												{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
												</option>
												@endfor
										</select>
									</div>
									<div class="col-md-2">
										<label>Jam Diskon</label>
										<input type="text" class="form-control text-center" value="{{ $config->jam_diskon ?? '00:00:00' }}" readonly>
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
<script>
	var FLAG = '{{ $flag }}';

	$(document).ready(function() {
		$('#cabang').on('change', function() {
			var cbg = $(this).val();
			if (cbg) {
				loadConfig(cbg);
			}
		});

		$('#configForm').on('submit', function(e) {
			e.preventDefault();
			saveConfig();
		});

		$('#status_poin').on('change', function() {
			if ($(this).is(':checked')) {
				$('#statusLabel').text('On');
			} else {
				$('#statusLabel').text('Off');
			}
		});

		$('#jam_diskon').on('input', function() {
			var value = $(this).val().replace(/[^0-9]/g, '');
			if (value.length >= 2) {
				value = value.substring(0, 2) + ':' + value.substring(2);
			}
			if (value.length >= 5) {
				value = value.substring(0, 5) + ':' + value.substring(5, 7);
			}
			$(this).val(value);
		});

		@if($selectedCbg)
		if ($('#status_poin').is(':checked')) {
			$('#statusLabel').text('On');
		}
		@endif
	});

	function loadConfig(cbg) {
		var routeUrl = FLAG == 'POIN' ? "{{ route('phpoinkresek.get-config') }}" : "{{ route('phpoinfc.get-config') }}";

		$.ajax({
			url: routeUrl,
			method: 'GET',
			data: {
				cabang: cbg,
				flag: FLAG
			},
			success: function(response) {
				if (response.success && response.data) {
					$('#status_poin').prop('checked', response.data.status_poin == 1);
					$('#statusLabel').text(response.data.status_poin == 1 ? 'On' : 'Off');
					$('#tgl_mulai').val(response.data.tgl_mulai);
					$('#tgl_selesai').val(response.data.tgl_selesai);
					$('#jam_diskon').val(response.data.jam_diskon);
				} else {
					$('#status_poin').prop('checked', false);
					$('#statusLabel').text('Off');
					$('#tgl_mulai').val('01');
					$('#tgl_selesai').val('31');
					$('#jam_diskon').val('00:00:00');
				}
			}
		});
	}

	function saveConfig() {
		var cbg = $('#cabang').val();
		if (!cbg) {
			alert('Harap pilih cabang');
			return;
		}

		var routeUrl = FLAG == 'POIN' ? "{{ route('phpoinkresek.save-config') }}" : "{{ route('phpoinfc.save-config') }}";
		$.ajax({
			url: routeUrl,
			method: 'POST',
			data: {
				_token: '{{ csrf_token() }}',
				cabang: cbg,
				flag: FLAG,
				status_poin: $('#status_poin').is(':checked') ? 1 : 0,
				tgl_mulai: $('#tgl_mulai').val(),
				tgl_selesai: $('#tgl_selesai').val(),
				jam_diskon: $('#jam_diskon').val()
			},
			success: function(response) {
				if (response.success) {
					alert(response.message);
				} else {
					alert('Error: ' + response.message);
				}
			},
			error: function(xhr) {
				alert('Terjadi kesalahan: ' + (xhr.responseJSON?.message || 'Unknown error'));
			}
		});
	}

	function saveJamDiskon() {
		saveConfig();
	}
</script>
@endsection