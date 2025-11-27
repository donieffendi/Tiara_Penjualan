@extends('layouts.plain')
@section('styles')
	<!-- <link rel="stylesheet" href="{{ url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}"> -->
	<link rel="stylesheet" href="{{ asset('foxie_js_css/jquery.dataTables.min.css') }}" />
@endsection

<style>
	.card {
		padding: 5px 10px !important;
	}

	.table thead th {
		background-color: #8a2be2;
		color: #ffff;
	}

	.datatable tbody td {
		padding: 5px !important;
	}

	.datatable {
		border-right: solid 2px #000;
		border-left: solid 2px #000;
	}

	.btn-secondary {
		background-color: #42047e !important;
	}

	th {
		font-size: 13px;
	}

	td {
		font-size: 13px;
	}

	/* menghilangkan padding */
	.content-header {
		padding: 0 !important;
	}

	/* Style untuk form sections */
	.section-pink {
		background-color: #ffb3ba;
		padding: 15px;
		border-radius: 5px;
		margin-bottom: 15px;
	}

	.section-orange {
		background-color: #ffd4a3;
		padding: 15px;
		border-radius: 5px;
		margin-bottom: 15px;
	}

	/* Custom styling untuk table kecil */
	.small-table {
		height: 300px;
		overflow-y: auto;
	}

	.small-table table {
		font-size: 12px;
	}

	.form-control-sm {
		height: calc(1.5em + 0.5rem + 2px);
		padding: 0.25rem 0.5rem;
		font-size: 0.875rem;
	}
</style>

@section('content')
	<!-- Sweetalert delete -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<!--  -->

	<div class="content-wrapper">

		<!-- Status -->
		@if (session('status'))
			<div class="alert alert-success">
				{{ session('status') }}
			</div>

			<!-- tambahan notifikasinya untuk delete di index -->
			<script>
				Swal.fire({
					title: 'Success!',
					text: '{{ session('status') }}',
					icon: 'success',
					confirmButtonText: 'OK'
				})
			</script>
			<!-- tutupannya -->
		@endif

		@if (session('error'))
			<div class="alert alert-danger">
				{{ session('error') }}
			</div>

			<script>
				Swal.fire({
					title: 'Error!',
					text: '{{ session('error') }}',
					icon: 'error',
					confirmButtonText: 'OK'
				})
			</script>
		@endif

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">

								<!-- ROW 1: Form Fields + Daftar Transaksi Retur DataTable -->
								<div class="row">
									<!-- Left Side: Faktur Pajak Retur Form -->
									<div class="col-md-6">
										<div class="section-pink">
											<h5><strong>{{ $judul }}</strong></h5>
											<form id="formBuatFaktur">
												@csrf
												<div class="row">
													<div class="col-md-6">
														<label for="txtkodes"><strong>Kode Supplier:</strong></label>
														<div class="input-group input-group-sm">
															<input type="text" class="form-control form-control-sm" id="txtkodes" name="txtkodes" placeholder="Masukkan kode supplier">
															<button type="button" class="btn btn-primary btn-sm" onclick="cariSupplier()">
																<i class="fas fa-search"></i>
															</button>
														</div>
													</div>
													<div class="col-md-6">
														<label for="dtfaktur"><strong>Tanggal Cetak:</strong></label>
														<input type="date" class="form-control form-control-sm" id="dtfaktur" name="dtfaktur" value="{{ date('Y-m-d') }}">
													</div>
												</div>
												<div class="row mt-2">
													<div class="col-md-6">
														<label for="txtpajak"><strong>Nomor Seri Pajak:</strong></label>
														<input type="text" class="form-control form-control-sm" id="txtpajak" name="txtpajak" placeholder="Masukkan nomor seri pajak">
													</div>
													<div class="col-md-6">
														<label for="dtseripajak"><strong>Tanggal Seri Pajak:</strong></label>
														<input type="date" class="form-control form-control-sm" id="dtseripajak" name="dtseripajak" value="{{ date('Y-m-d') }}">
													</div>
												</div>
												<div class="row mt-3">
													<div class="col-md-6">
														<button type="button" class="btn btn-info btn-sm btn-block" onclick="updateFaktur()">
															<i class="fas fa-sync"></i> UPDATE!!!
														</button>
													</div>
													<div class="col-md-6">
														<button type="button" class="btn btn-success btn-sm btn-block" onclick="simpanFaktur()">
															<i class="fas fa-save"></i> Simpan Faktur
														</button>
													</div>
												</div>
												<div class="row mt-2">
													<div class="col-md-12">
														<p class="mb-0"><strong>CPTNOBUKTI:</strong> <span id="lblNoBukti">-</span></p>
													</div>
												</div>
											</form>
										</div>
									</div>

									<!-- Right Side: Daftar Transaksi Retur DataTable -->
									<div class="col-md-6">
										<div class="section-orange">
											<h6><strong>Daftar Transaksi Retur</strong></h6>
											<div class="table-responsive">
												<table class="table-bordered table-sm table" id="tblRetur">
													<thead class="table-dark">
														<tr>
															<th>No Bukti</th>
															<th>No Form Bayar</th>
															<th>Tanggal</th>
															<th>Total</th>
															<th>Cek</th>
														</tr>
													</thead>
													<tbody>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>

								<!-- ROW 2: Main DataTable + Print Controls -->
								<div class="row mt-3">
									<!-- Left Side: Main DataTable -->
									<div class="col-md-8">
										<input name="flagz" class="form-control flagz" id="flagz" value="{{ $flagz }}" hidden>
										<input name="koderetur" class="form-control koderetur" id="koderetur" value="{{ $koderetur }}" hidden>

										<table class="table-striped table-border table-hover nowrap datatable table table-fixed" id="datatable">
											<thead class="table-dark">
												<tr>
													<th scope="col" style="text-align: center"></th>
													<th scope="col" style="text-align: center">#</th>
													<th scope="col" style="text-align: center">-</th>
													<th scope="col" style="text-align: center">
														<input type="checkbox" id="checkAll" class="form-control" style="width: 20px; margin: 0 auto;">
													</th>
													<th scope="col" style="text-align: center">No Bukti</th>
													<th scope="col" style="text-align: center">No Faktur</th>
													<th scope="col" style="text-align: center">Tanggal</th>
													<th scope="col" style="text-align: center">Penagih</th>
													<th scope="col" style="text-align: center">Nama</th>
													<th scope="col" style="text-align: center">User</th>
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>
										<div class="mt-2">
											<small>Rec (0)</small>
										</div>
									</div>

									<!-- Right Side: Print Controls -->
									<div class="col-md-4">
										<div class="section-orange">
											<h6><strong>Print bersama</strong></h6>
											<div class="form-group">
												<label for="printBukti1"><strong>No:</strong></label>
												<input type="text" class="form-control form-control-sm" id="printBukti1" name="printBukti1" placeholder="Nomor bukti dari">
											</div>
											<div class="form-group mt-2">
												<label for="printBukti2"><strong>S/d No:</strong></label>
												<input type="text" class="form-control form-control-sm" id="printBukti2" name="printBukti2" placeholder="Nomor bukti sampai">
											</div>
											<div class="mt-3">
												<button type="button" class="btn btn-primary btn-sm btn-block" onclick="prosesoPrint()">
													<i class="fas fa-file-pdf"></i> Tunjukkan
												</button>
											</div>
											<div class="mt-3">
												<button type="button" class="btn btn-success btn-sm btn-block" onclick="showBuatCsv()">
													<i class="fas fa-file-csv"></i> Buat CSV Faktur
												</button>
											</div>
											<div class="mt-3">
												<label for="txtcekfaktur"><strong>Cek Faktur:</strong></label>
												<div class="input-group input-group-sm">
													<input type="text" class="form-control form-control-sm" id="txtcekfaktur" name="txtcekfaktur" placeholder="Nomor bukti">
													<button type="button" class="btn btn-info btn-sm" onclick="cekFaktur()">
														<i class="fas fa-search"></i>
													</button>
												</div>
											</div>
											<div id="hasilCekFaktur" class="mt-2"></div>
										</div>
									</div>
								</div>

								<!-- CSV Export Modal -->
								<div class="modal fade" id="csvModal" tabindex="-1" aria-labelledby="csvModalLabel" aria-hidden="true">
									<div class="modal-dialog">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="modal-title" id="csvModalLabel">Buat CSV Faktur</h5>
												<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
											</div>
											<div class="modal-body">
												<form id="formCsv">
													<div class="form-group">
														<label for="csvBukti1"><strong>No Bukti Dari:</strong></label>
														<input type="text" class="form-control" id="csvBukti1" name="csvBukti1" placeholder="Nomor bukti dari">
													</div>
													<div class="form-group mt-2">
														<label for="csvBukti2"><strong>S/d No:</strong></label>
														<input type="text" class="form-control" id="csvBukti2" name="csvBukti2" placeholder="Nomor bukti sampai">
													</div>
													<div class="form-group mt-2">
														<label for="namaFile"><strong>Nama File:</strong></label>
														<input type="text" class="form-control" id="namaFile" name="namaFile" placeholder="Masukkan nama file CSV">
													</div>
												</form>
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
												<button type="button" class="btn btn-success" onclick="generateCsv()">
													<i class="fas fa-download"></i> Generate CSV
												</button>
											</div>
										</div>
									</div>
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
	<script src="{{ asset('foxie_js_css/jquery.dataTables.min.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>

	<script>
		var table;
		var flagz = '{{ $flagz }}';
		var koderetur = '{{ $koderetur }}';
		var returTable;

		$(document).ready(function() {
			// Initialize Main DataTable
			table = $('#datatable').DataTable({
				"processing": true,
				"serverSide": true,
				"pageLength": 50,
				"lengthMenu": [
					[10, 25, 50, 100, -1],
					[10, 25, 50, 100, "All"]
				],
				"ajax": {
					"url": "{{ route('tfpajakretur.getdata') }}",
					"type": "POST",
					"data": function(d) {
						d._token = "{{ csrf_token() }}";
						d.flagz = flagz;
					}
				},
				"columns": [{
						data: 'action',
						name: 'action',
						className: 'text-center',
						orderable: false,
						searchable: false
					},
					{
						data: 'delete',
						name: 'delete',
						className: 'text-center',
						orderable: false,
						searchable: false
					},
					{
						data: 'check',
						name: 'check',
						className: 'text-center',
						orderable: false,
						searchable: false
					},
					{
						data: 'no_bukti',
						name: 'no_bukti',
						className: 'text-center'
					},
					{
						data: 'no_faktur',
						name: 'no_faktur',
						className: 'text-center'
					},
					{
						data: 'tgl',
						name: 'tgl',
						className: 'text-center'
					},
					{
						data: 'kodep',
						name: 'kodep',
						className: 'text-center'
					},
					{
						data: 'namac',
						name: 'namac'
					},
					{
						data: 'usrnm',
						name: 'usrnm',
						className: 'text-center'
					}
				],
				"columnDefs": [{
					"targets": [0],
					"visible": true,
					"searchable": false
				}],
				"order": [
					[1, 'asc']
				]
			});

			// Initialize Retur Table as Server-side DataTable
			returTable = $('#tblRetur').DataTable({
				"processing": true,
				"serverSide": true,
				"paging": false,
				"searching": false,
				"info": false,
				"ordering": false,
				"autoWidth": false,
				"scrollY": "250px",
				"scrollCollapse": true,
				"ajax": {
					"url": "{{ route('tfpajakretur.getreturdata') }}",
					"type": "POST",
					"data": function(d) {
						d._token = "{{ csrf_token() }}";
						d.kodes = $('#txtkodes').val();
					}
				},
				"columns": [{
						data: 'no_bukti',
						name: 'no_bukti',
						title: 'No Bukti'
					},
					{
						data: 'no_fak01',
						name: 'no_fak01',
						title: 'No Form Bayar'
					},
					{
						data: 'tgl',
						name: 'tgl',
						title: 'Tanggal'
					},
					{
						data: 'total',
						name: 'total',
						title: 'Total',
						className: 'text-right'
					},
					{
						data: 'cek',
						name: 'cek',
						title: 'Cek'
					}
				]
			});

			// Check All checkbox functionality
			$('#checkAll').on('click', function() {
				$('.datatable tbody input[type="checkbox"]').prop('checked', this.checked);
			});

			// Individual checkbox click handler
			$(document).on('click', '.datatable tbody input[type="checkbox"]', function() {
				if (!this.checked) {
					$('#checkAll').prop('checked', false);
				} else {
					var allChecked = $('.datatable tbody input[type="checkbox"]:checked').length === $(
						'.datatable tbody input[type="checkbox"]').length;
					$('#checkAll').prop('checked', allChecked);
				}
			});

			// Auto reload retur table when supplier code changes
			$('#txtkodes').on('change blur', function() {
				if ($(this).val()) {
					returTable.ajax.reload();
				}
			});
		});

		// Supplier search function
		function cariSupplier() {
			var kodeSupplier = $('#txtkodes').val();
			if (!kodeSupplier) {
				Swal.fire('Error', 'Masukkan kode supplier terlebih dahulu', 'error');
				return;
			}

			$.ajax({
				url: "{{ route('tfpajakretur.getsupplierretur') }}",
				type: "POST",
				data: {
					_token: "{{ csrf_token() }}",
					kodes: kodeSupplier,
					flagz: flagz
				},
				dataType: "json",
				success: function(response) {
					if (response.success) {
						var data = response.data;

						// Clear existing data
						returTable.clear();

						if (data.length > 0) {
							data.forEach(function(item, index) {
								returTable.row.add([
									item.NO_BUKTI,
									item.NO_FORMBAYAR,
									item.TGL,
									formatNumber(item.TOTAL),
									'<input type="checkbox" name="selected_retur[]" value="' + item.NO_BUKTI +
									'" class="retur-checkbox">'
								]);
							});
						}

						// Draw the table
						returTable.draw();

						// Update record count
						$('.small-table').next().find('small').text('Rec (' + data.length + ')');
					} else {
						Swal.fire('Error', response.message || 'Supplier tidak ditemukan', 'error');
						returTable.clear().draw();
						$('.small-table').next().find('small').text('Rec (0)');
					}
				},
				error: function(xhr) {
					Swal.fire('Error', 'Terjadi kesalahan saat mencari supplier', 'error');
					returTable.clear().draw();
					$('.small-table').next().find('small').text('Rec (0)');
				}
			});
		}

		// Update faktur function
		function updateFaktur() {
			if (!$('#txtkodes').val()) {
				Swal.fire('Error', 'Masukkan kode supplier terlebih dahulu', 'error');
				return;
			}

			var formData = {
				_token: "{{ csrf_token() }}",
				kodes: $('#txtkodes').val(),
				tgl_cetak: $('#dtfaktur').val(),
				no_seri: $('#txtpajak').val(),
				tgl_seri: $('#dtseripajak').val(),
				flagz: flagz
			};

			$.ajax({
				url: "{{ route('tfpajakretur.updatefaktur') }}",
				type: "POST",
				data: formData,
				dataType: "json",
				success: function(response) {
					if (response.success) {
						$('#lblNoBukti').text(response.no_bukti || 'Generated');
						// Reload both tables
						table.ajax.reload();
						returTable.ajax.reload();
						Swal.fire('Success', 'Data berhasil diupdate!', 'success');
					} else {
						Swal.fire('Error', response.message, 'error');
					}
				},
				error: function(xhr) {
					var errorMsg = 'Terjadi kesalahan saat update faktur';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMsg = xhr.responseJSON.message;
					}
					Swal.fire('Error', errorMsg, 'error');
				}
			});
		}

		// Save faktur function
		function simpanFaktur() {
			if (!$('#txtkodes').val()) {
				Swal.fire('Error', 'Masukkan kode supplier terlebih dahulu!', 'error');
				return;
			}

			var checkedRows = $('#datatable tbody input[type="checkbox"]:checked');
			if (checkedRows.length === 0) {
				Swal.fire('Error', 'Pilih minimal satu data untuk disimpan!', 'error');
				return;
			}

			var selectedData = [];
			checkedRows.each(function() {
				var row = table.row($(this).closest('tr')).data();
				selectedData.push(row.NO_BUKTI);
			});

			var formData = {
				_token: "{{ csrf_token() }}",
				kodes: $('#txtkodes').val(),
				tgl_cetak: $('#dtfaktur').val(),
				no_seri: $('#txtpajak').val(),
				tgl_seri: $('#dtseripajak').val(),
				selected_data: selectedData,
				flagz: flagz
			};

			$.ajax({
				url: "{{ route('tfpajakretur.store') }}",
				type: "POST",
				data: formData,
				dataType: "json",
				success: function(response) {
					if (response.success) {
						Swal.fire('Success', 'Faktur berhasil disimpan!', 'success');
						table.ajax.reload();
						returTable.ajax.reload();
						$('#lblNoBukti').text(response.no_bukti || '-');
					} else {
						Swal.fire('Error', response.message || 'Terjadi kesalahan!', 'error');
					}
				},
				error: function(xhr) {
					console.error('Error:', xhr);
					Swal.fire('Error', 'Terjadi kesalahan sistem!', 'error');
				}
			});
		}

		$.ajax({
			url: "{{ route('tfpajakretur.store') }}",
			type: "POST",
			data: formData,
			dataType: "json",
			success: function(response) {
				if (response.success) {
					Swal.fire('Success', response.message, 'success').then(() => {
						// Reset form
						$('#formBuatFaktur')[0].reset();
						returTable.clear().draw();
						$('#lblNoBukti').text('-');
						$('.small-table').next().find('small').text('Rec (0)');

						// Reload main table
						table.ajax.reload();
					});
				} else {
					Swal.fire('Error', response.message, 'error');
				}
			},
			error: function(xhr) {
				var errorMsg = 'Terjadi kesalahan saat menyimpan faktur';
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMsg = xhr.responseJSON.message;
				}
				Swal.fire('Error', errorMsg, 'error');
			}
		});
		}

		// Print functions
		function prosesoPrint() {
			var bukti1 = $('#printBukti1').val();
			var bukti2 = $('#printBukti2').val();

			if (!bukti1 || !bukti2) {
				Swal.fire('Error', 'Nomor bukti dari dan sampai harus diisi', 'error');
				return;
			}

			var url = "{{ route('tfpajakretur.print') }}" + "?bukti1=" + bukti1 + "&bukti2=" + bukti2;
			window.open(url, '_blank');
		}

		// Show CSV modal
		function showBuatCsv() {
			$('#csvModal').modal('show');
		}

		// CSV generation function
		function generateCsv() {
			var bukti1 = $('#csvBukti1').val();
			var bukti2 = $('#csvBukti2').val();
			var namaFile = $('#namaFile').val();

			if (!bukti1 || !bukti2 || !namaFile) {
				Swal.fire('Error', 'Semua field harus diisi', 'error');
				return;
			}

			$.ajax({
				url: "{{ route('tfpajakretur.generatecsv') }}",
				type: "POST",
				data: {
					_token: "{{ csrf_token() }}",
					bukti1: bukti1,
					bukti2: bukti2,
					nama_file: namaFile,
					flagz: flagz
				},
				dataType: "json",
				success: function(response) {
					if (response.success) {
						Swal.fire('Success', response.message, 'success').then(() => {
							// Download file if URL provided
							if (response.download_url) {
								window.open(response.download_url, '_blank');
							}
							$('#csvModal').modal('hide');
						});
					} else {
						Swal.fire('Error', response.message, 'error');
					}
				},
				error: function(xhr) {
					var errorMsg = 'Terjadi kesalahan saat generate CSV';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMsg = xhr.responseJSON.message;
					}
					Swal.fire('Error', errorMsg, 'error');
				}
			});
		}

		// Cek faktur function
		function cekFaktur() {
			var noBukti = $('#txtcekfaktur').val();
			if (!noBukti) {
				Swal.fire('Error', 'Masukkan nomor bukti terlebih dahulu', 'error');
				return;
			}

			$.ajax({
				url: "{{ url('tfpajakretur/cek') }}",
				type: "POST",
				data: {
					_token: "{{ csrf_token() }}",
					no_bukti: noBukti
				},
				success: function(response) {
					$('#hasilCekFaktur').html(response.html || 'Tidak ada data');
				},
				error: function(xhr) {
					console.error('Error:', xhr);
					$('#hasilCekFaktur').html('<span class="text-danger">Error checking faktur</span>');
				}
			});
		}
		var hasil = '<div class="alert alert-success alert-sm p-2">';
		hasil += '<strong>Faktur ditemukan:</strong><br>';
		hasil += '<small>No Bukti: ' + (response.data.NO_BUKTI || '-') + '</small><br>';
		hasil += '<small>No Faktur: ' + (response.data.NO_FAKTUR || '-') + '</small><br>';
		hasil += '<small>Tanggal: ' + (response.data.TGL || '-') + '</small><br>';
		hasil += '<small>Supplier: ' + (response.data.NAMAC || '-') + '</small>';
		hasil += '</div>';
		$('#hasilCekFaktur').html(hasil);
		}
		else {
			$('#hasilCekFaktur').html('<div class="alert alert-warning alert-sm p-2"><small>Faktur tidak ditemukan</small></div>');
		}
		},
		error: function(xhr) {
		$('#hasilCekFaktur').html('<div class="alert alert-danger alert-sm p-2"><small>Terjadi kesalahan saat mencari faktur</small></div>');
		}
		});
		}

		// Detail function
		function detail(id) {
			window.location.href = "{{ url('tfpajakretur/show') }}/" + id;
		}

		// Edit function
		function edit(id) {
			window.location.href = "{{ url('tfpajakretur/edit') }}/" + id;
		}

		// Delete function
		function deleteData(id) {
			Swal.fire({
				title: 'Konfirmasi',
				text: 'Apakah Anda yakin ingin menghapus data ini?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Ya, hapus!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({
						url: "{{ url('tfpajakretur') }}/" + id,
						type: "DELETE",
						data: {
							_token: "{{ csrf_token() }}"
						},
						success: function(response) {
							if (response.success) {
								Swal.fire('Deleted!', response.message, 'success');
								table.ajax.reload();
							} else {
								Swal.fire('Error!', response.message, 'error');
							}
						},
						error: function(xhr) {
							Swal.fire('Error!', 'Terjadi kesalahan saat menghapus data', 'error');
						}
					});
				}
			});
		}

		// Utility function for number formatting
		function formatNumber(num) {
			return new Intl.NumberFormat('id-ID').format(num);
		}

		// Tab switching function (if needed for SPM/BSN)
		function switchTab(flag) {
			flagz = flag;
			table.ajax.reload();

			// Update form elements if needed
			$('#flagz').val(flag);

			// Update any UI elements based on flag
			if (flag === 'BSN') {
				$('.flag-specific').show();
			} else {
				$('.flag-specific').hide();
			}
		}
	</script>
@endsection
