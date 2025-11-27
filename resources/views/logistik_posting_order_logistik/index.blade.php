@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Posting Transaksi Logistik</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Posting Transaksi Logistik</li>
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
								<!-- Filter Form -->
								<form id="filterForm" method="GET" action="{{ route('get-lpostingtransaksilogistik') }}">
									@csrf
									<div class="row align-items-end mb-3">
										<div class="col-3">
											<label for="cbg">Cabang</label>
											<select name="cbg" id="cbg" class="form-control" required>
												<option value="">Pilih Cabang</option>
												@foreach ($cbg as $cabang)
													<option value="{{ $cabang->kode }}" {{ session('user_cabang') == $cabang->kode ? 'selected' : '' }}>
														{{ $cabang->kode }}
													</option>
												@endforeach
											</select>
										</div>
										<div class="col-2">
											<button class="btn btn-primary" type="submit" name="action" value="filter">
												<i class="fas fa-search mr-1"></i>Tampil
											</button>
										</div>
									</div>
								</form>

								<!-- Action Buttons -->
								<div class="row mb-3">
									<div class="col-12">
										<button class="btn btn-success" id="btnPost" onclick="postData()">
											<i class="fas fa-paper-plane mr-1"></i>Post
										</button>
										<button class="btn btn-info" id="btnCheckAll" onclick="checkAll()">
											<i class="fas fa-check-square mr-1"></i>Cek Semua
										</button>
										<button class="btn btn-warning" id="btnUncheckAll" onclick="uncheckAll()">
											<i class="fas fa-square mr-1"></i>Batal Cek
										</button>
									</div>
								</div>

								<!-- Data Table -->
								<div class="table-responsive">
									<table class="table-striped table-bordered table" id="postingTable" style="display:none;">
										<thead>
											<tr>
												<th style="width: 40px;">
													<input type="checkbox" id="checkAllHeader">
												</th>
												<th>No Bukti</th>
												<th>Tanggal</th>
												<th>Notes</th>
												<th>Total</th>
												<th>Cek</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>

								<div id="messageArea"></div>

								<!-- Loading Panel -->
								<div id="loadingPanel" style="display: none;" class="p-3 text-center">
									<div class="spinner-border" role="status">
										<span class="sr-only">Loading...</span>
									</div>
									<div class="mt-2">Memproses data...</div>
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
		var postingDataTable;
		var selectedBukti = [];

		$(document).ready(function() {
			// Initialize DataTable
			postingDataTable = $('#postingTable').DataTable({
				"processing": true,
				"serverSide": true,
				"paging": true,
				"pageLength": 25,
				"searching": true,
				"ordering": true,
				"info": true,
				"autoWidth": false,
				"responsive": true,
				"scrollX": true,
				"ajax": {
					"url": "{{ route('get-lpostingtransaksilogistik') }}",
					"type": "GET",
					"data": function(d) {
						d.cbg = $('#cbg').val();
					}
				},
				"columns": [{
						"data": "cek_box",
						"orderable": false,
						"searchable": false,
						"width": "40px"
					},
					{
						"data": "no_bukti"
					},
					{
						"data": "tgl"
					},
					{
						"data": "notes"
					},
					{
						"data": "total",
						"className": "text-right"
					},
					{
						"data": "cek",
						"className": "text-center"
					}
				],
				"columnDefs": [{
						"className": "text-right",
						"targets": [4]
					},
					{
						"className": "text-center",
						"targets": [0, 5]
					}
				]
			});

			// Form submission
			$('#filterForm').on('submit', function(e) {
				e.preventDefault();
				loadData();
			});

			// Checkbox handling
			$(document).on('change', '.cek-item', function() {
				var bukti = $(this).val();
				if ($(this).is(':checked')) {
					if (selectedBukti.indexOf(bukti) === -1) {
						selectedBukti.push(bukti);
					}
				} else {
					var index = selectedBukti.indexOf(bukti);
					if (index !== -1) {
						selectedBukti.splice(index, 1);
					}
				}
				updateCheckAllStatus();
			});

			// Check all header
			$('#checkAllHeader').on('change', function() {
				if ($(this).is(':checked')) {
					checkAll();
				} else {
					uncheckAll();
				}
			});

			// Auto load data if cbg is already set
			@if (session('user_cabang'))
				loadData();
			@endif
		});

		function loadData() {
			if (!validateForm()) return;

			postingDataTable.ajax.reload(function(json) {
				if (json.recordsTotal > 0) {
					$('#postingTable').show();
					$('#messageArea').empty();
					selectedBukti = [];
					updateCheckAllStatus();
				} else {
					$('#postingTable').hide();
					$('#messageArea').html(`
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Tidak ada data ditemukan untuk cabang yang dipilih.
                    </div>
                `);
				}
			});
		}

		function validateForm() {
			var cbg = $('#cbg').val();

			if (!cbg) {
				alert('Harap pilih cabang');
				$('#cbg').focus();
				return false;
			}

			return true;
		}

		function checkAll() {
			$('.cek-item').prop('checked', true);
			selectedBukti = [];
			$('.cek-item').each(function() {
				selectedBukti.push($(this).val());
			});
			$('#checkAllHeader').prop('checked', true);
		}

		function uncheckAll() {
			$('.cek-item').prop('checked', false);
			selectedBukti = [];
			$('#checkAllHeader').prop('checked', false);
		}

		function updateCheckAllStatus() {
			var totalCheckboxes = $('.cek-item').length;
			var checkedCheckboxes = $('.cek-item:checked').length;

			if (checkedCheckboxes === 0) {
				$('#checkAllHeader').prop('indeterminate', false);
				$('#checkAllHeader').prop('checked', false);
			} else if (checkedCheckboxes === totalCheckboxes) {
				$('#checkAllHeader').prop('indeterminate', false);
				$('#checkAllHeader').prop('checked', true);
			} else {
				$('#checkAllHeader').prop('indeterminate', true);
			}
		}

		function postData() {
			if (selectedBukti.length === 0) {
				alert('Harap pilih data yang akan diposting');
				return;
			}

			if (!confirm('Apakah Anda yakin akan memposting ' + selectedBukti.length + ' dokumen?')) {
				return;
			}

			$('#loadingPanel').show();
			$('#btnPost').prop('disabled', true);

			$.ajax({
				url: '{{ route('lpostingtransaksilogistik.store') }}',
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					selected_bukti: selectedBukti
				},
				success: function(response) {
					$('#loadingPanel').hide();
					$('#btnPost').prop('disabled', false);

					if (response.success) {
						alert(response.message);
						loadData();
						selectedBukti = [];
					} else {
						alert('Error: ' + response.message);
					}
				},
				error: function(xhr) {
					$('#loadingPanel').hide();
					$('#btnPost').prop('disabled', false);
					alert('Terjadi kesalahan: ' + (xhr.responseJSON?.message || 'Unknown error'));
				}
			});
		}
	</script>
@endsection
