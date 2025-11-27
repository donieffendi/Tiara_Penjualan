@extends('layouts.plain')
@section('styles')
	<link rel="stylesheet" href="{{ asset('foxie_js_css/jquery.dataTables.min.css') }}" />
@endsection

<style>
	.card {
		padding: 5px 10px !important;
	}

	.table th {
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

	/* Layout untuk 2 datatable */
	.left-panel {
		width: 30%;
		min-height: 500px;
	}

	.right-panel {
		width: 70%;
		min-height: 500px;
	}

	.input-group-text {
		background-color: #6c757d;
		color: white;
		font-weight: bold;
	}

	.form-control:focus {
		border-color: #8a2be2;
		box-shadow: 0 0 0 0.2rem rgba(138, 43, 226, 0.25);
	}

	.faktur-info {
		background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
		border-left: 4px solid #8a2be2;
	}

	.invoice-table {
		max-height: 400px;
		overflow-y: auto;
	}

	.btn-menu {
		background: linear-gradient(135deg, #8a2be2 0%, #6a1b9a 100%);
		border: none;
		color: white;
	}

	.btn-menu:hover {
		background: linear-gradient(135deg, #6a1b9a 0%, #8a2be2 100%);
		color: white;
	}

	.status-badge {
		font-size: 11px;
		padding: 3px 8px;
	}

	.badge-created {
		background-color: #28a745;
		color: white;
	}

	.badge-pending {
		background-color: #ffc107;
		color: #212529;
	}
</style>

@section('content')
	<!-- Sweetalert -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<div class="content-wrapper">

		<!-- Status Messages -->
		@if (session('status'))
			<div class="alert alert-success">
				{{ session('status') }}
			</div>
			<script>
				Swal.fire({
					title: 'Success!',
					text: '{{ session('status') }}',
					icon: 'success',
					confirmButtonText: 'OK'
				})
			</script>
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

				<!-- Main Layout: Left Panel (List Faktur) + Right Panel (Form & Detail) -->
				<div class="row">

					<!-- LEFT PANEL - List Invoice Available for Faktur -->
					<div class="col-md-4 left-panel">
						<div class="card h-100">
							<div class="card-header">
								<h5><i class="fas fa-list"></i> List Invoice</h5>
								<button type="button" class="btn btn-menu btn-sm float-right" onclick="toggleFakturMenu()">
									<i class="fas fa-bars"></i> Menu
								</button>
							</div>
							<div class="card-body p-0">
								<div id="fakturMenuPanel" style="display: none;">
									<div class="border-bottom p-3">
										<div class="form-group mb-2">
											<label for="txtPeriode">Periode:</label>
											<input type="text" class="form-control form-control-sm" id="txtPeriode"
												value="{{ session('periode')['bulan'] ?? '' }}/{{ session('periode')['tahun'] ?? '' }}" readonly>
										</div>
										<button type="button" class="btn btn-primary btn-sm" onclick="loadAvailableFaktur()">
											<i class="fas fa-search"></i> Load Data
										</button>
									</div>
								</div>

								<table class="table-striped table-sm table" id="availableFakturTable">
									<thead class="table-dark">
										<tr>
											<th style="width: 15%">Invoice</th>
											<th style="width: 15%">Faktur</th>
											<th style="width: 15%">Total</th>
											<th style="width: 15%">Tgl</th>
											<th style="width: 20%">User</th>
											<th style="width: 20%">Status</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<!-- RIGHT PANEL - Form Input & Detail -->
					<div class="col-md-8 right-panel">
						<div class="card h-100">
							<div class="card-header">
								<h5><i class="fas fa-file-invoice"></i> {{ $flagz == 'FK' ? 'MUTIARA DEWATA JAYA.PT' : 'Faktur Details' }}</h5>
							</div>
							<div class="card-body">

								<!-- Faktur Information Form -->
								<div class="row mb-4">
									<div class="col-12">
										<div class="card faktur-info">
											<div class="card-body">
												<div class="row g-3">
													<div class="col-12">
														<div class="form-group">
															<label for="txtNoInvoice">No.Invoice:</label>
															<div class="input-group">
																<input type="text" class="form-control form-control-sm" id="txtNoInvoice" placeholder="No Invoice...">
															</div>
														</div>
													</div>
													<div class="col-12">
														<div class="form-group">
															<label for="txtNoFaktur">No.Faktur:</label>
															<input type="text" class="form-control form-control-sm" id="txtNoFaktur" placeholder="No Faktur...">
														</div>
													</div>
													<div class="col-12">
														<div class="form-group">
															<label for="txtMasaPajak">Masa Pajak:</label>
															<input type="text" class="form-control form-control-sm" id="txtMasaPajak" placeholder="Masa Pajak..." readonly>
														</div>
													</div>
													<div class="col-12">
														<div class="form-group">
															<label for="txtNamaPlgn">Nama Plgn:</label>
															<input type="text" class="form-control form-control-sm" id="txtNamaPlgn" placeholder="Nama Pelanggan">
														</div>
													</div>
													<div class="col-12">
														<div class="form-group">
															<label for="txtAlamatPlgn">Alamat Plgn:</label>
															<input type="text" class="form-control form-control-sm" id="txtAlamatPlgn" placeholder="Alamat Pelanggan">
														</div>
													</div>
													<div class="col-12">
														<div class="form-group">
															<label for="txtNPWP">NPWP:</label>
															<input type="text" class="form-control form-control-sm" id="txtNPWP" placeholder="NPWP">
														</div>
													</div>
													<div class="col-12">
														<div class="form-group">
															<label for="txtTglPajak">Tgl Pajak:</label>
															<div class="input-group">
																<span class="form-control form-control-sm" id="txtTglPajak"
																	style="background-color: #e9ecef; border: 1px solid #ced4da;">{{ now()->format('d/m/Y H:i:s') }}</span>
																<div class="input-group-append">
																	<span class="input-group-text">| <span id="lblUser">{{ Auth::user()->name ?? 'User' }}</span></span>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Summary Information -->
								<div class="row mb-3">
									<div class="col-md-4">
										<div class="form-group">
											<label for="txtTotalDPP">Total DPP:</label>
											<input type="text" class="form-control form-control-sm text-right" id="txtTotalDPP" readonly>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label for="txtTotalPPN">Total PPN:</label>
											<input type="text" class="form-control form-control-sm text-right" id="txtTotalPPN" readonly>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label for="txtTotalAmount">Total Amount:</label>
											<input type="text" class="form-control form-control-sm text-right" id="txtTotalAmount" readonly>
										</div>
									</div>
								</div>

								<!-- Action Buttons -->
								<div class="row mt-3">
									<div class="col-12">
										<div class="text-center">
											<button type="button" class="btn btn-success mr-2" onclick="saveFaktur()" id="btnSave">
												<i class="fas fa-save"></i> SAVE
											</button>
											<button type="button" class="btn btn-info mr-2" onclick="generateCSV()" id="btnCSV">
												<i class="fas fa-file-csv"></i> CSV
											</button>
											<button type="button" class="btn btn-warning mr-2" onclick="refreshData()">
												<i class="fas fa-refresh"></i> Refresh
											</button>
											<button type="button" class="btn btn-primary" onclick="printFaktur()">
												<i class="fas fa-print"></i> Print
											</button>
										</div>
									</div>
								</div>

							</div>
						</div>
					</div>
				</div>

				<!-- Print Form Section -->
				<div class="row mt-4">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header">
								<h5><i class="fas fa-print"></i> Print Faktur Options</h5>
							</div>
							<div class="card-body">
								<form id="printForm" method="POST">
									@csrf
									<div class="row">
										<div class="col-md-3">
											<label for="txtFakturDari">No Faktur Dari:</label>
											<input type="text" class="form-control" name="txtFakturDari" id="txtFakturDari" placeholder="FK2501-0001">
										</div>
										<div class="col-md-1 d-flex align-items-center justify-content-center">
											<span style="margin-top: 25px; font-weight: bold;">s/D</span>
										</div>
										<div class="col-md-3">
											<label for="txtFakturSampai">No Faktur Sampai:</label>
											<input type="text" class="form-control" name="txtFakturSampai" id="txtFakturSampai" placeholder="FK2501-0010">
										</div>
										<div class="col-md-5 d-flex align-items-end">
											<button type="button" class="btn btn-primary mr-2" onclick="printRange()">
												<i class="fas fa-print"></i> Print Range
											</button>
											<button type="button" class="btn btn-success mr-2" onclick="printReport()">
												<i class="fas fa-file-pdf"></i> Laporan
											</button>
											<button type="button" class="btn btn-info" onclick="printAllFaktur()">
												<i class="fas fa-print"></i> Print All
											</button>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

				<input name="flagz" class="form-control flagz" id="flagz" value="{{ $flagz }}" hidden>
				<input type="hidden" id="currentNoSJ" value="">
				<input type="hidden" id="currentStatus" value="new">

			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

	<script>
		$(document).ready(function() {
			// Initialize
			loadAvailableFaktur();
			updateCurrentDateTime();

			// Auto-load invoice data when No.Invoice changes and Enter pressed
			$('#txtNoInvoice').on('keyup', function(e) {
				if (e.keyCode === 13 && $(this).val().trim() !== '') {
					loadFakturByInvoice($(this).val().trim());
				}
			});

			// Double click on available invoice to load for faktur
			$(document).on('dblclick', '#availableFakturTable tbody tr', function() {
				const noSJ = $(this).data('no-sj');
				const kodec = $(this).data('kodec');
				const noFaktur = $(this).data('no-faktur');

				if (noSJ && noSJ !== '') {
					if (noFaktur && noFaktur !== '') {
						$('#currentStatus').val('edit');
					} else {
						$('#currentStatus').val('new');
					}
					$('#currentNoSJ').val(noSJ);
					loadFakturForEdit(noSJ, kodec);
				}
			});

			// Update date time every second
			setInterval(updateCurrentDateTime, 1000);
		});

		// Toggle Faktur Menu (equivalent to btnMenuClick in Delphi)
		function toggleFakturMenu() {
			const panel = document.getElementById('fakturMenuPanel');
			if (panel.style.display === 'none') {
				panel.style.display = 'block';
				loadAvailableFaktur();
			} else {
				panel.style.display = 'none';
			}
		}

		// Update current date time
		function updateCurrentDateTime() {
			const now = new Date();
			const formatted = now.toLocaleString('id-ID', {
				day: '2-digit',
				month: '2-digit',
				year: 'numeric',
				hour: '2-digit',
				minute: '2-digit',
				second: '2-digit'
			});
			$('#txtTglPajak').val(formatted);
		}

		// Load Available Faktur (equivalent to com2 query in Delphi)
		function loadAvailableFaktur() {
			const periode = $('#txtPeriode').val();

			if (!periode) {
				console.log('Periode tidak tersedia');
				return;
			}

			$.ajax({
				url: '{{ route('get-tfaktur') }}',
				method: 'GET',
				data: {
					per: periode
				},
				success: function(response) {
					let tableHtml = '';

					response.data.forEach(function(item) {
						const statusBadge = item.NO_FAKTUR ?
							'<span class="badge status-badge badge-created">Sudah Faktur</span>' :
							'<span class="badge status-badge badge-pending">Belum Faktur</span>';

						tableHtml += `
							<tr data-no-sj="${item.NO_SJ || ''}" data-kodec="${item.KODEC || ''}" data-no-faktur="${item.NO_FAKTUR || ''}" style="cursor: pointer;">
								<td><small>${item.NO_SJ || '-'}</small></td>
								<td><small>${item.NO_FAKTUR || '-'}</small></td>
								<td><small>${parseInt(item.TOTALX || 0).toLocaleString('id-ID')}</small></td>
								<td><small>${item.TGLX || '-'}</small></td>
								<td><small>${item.USERX || '-'}</small></td>
								<td>${statusBadge}</td>
							</tr>
						`;
					});

					$('#availableFakturTable tbody').html(tableHtml);
				},
				error: function(xhr) {
					console.error('Error loading available faktur:', xhr);
					let errorMessage = 'Gagal memuat data faktur tersedia';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMessage = xhr.responseJSON.error;
					}

					Swal.fire({
						title: 'Error!',
						text: errorMessage,
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Load Faktur by Invoice (equivalent to loading data by invoice)
		function loadFakturByInvoice(noInvoice) {
			const periode = $('#txtPeriode').val();

			$.ajax({
				url: '{{ route('get-detail-tfaktur') }}',
				method: 'GET',
				data: {
					no_sj: noInvoice,
					per: periode
				},
				success: function(response) {
					if (!response) {
						Swal.fire({
							title: 'Info!',
							text: 'Data tidak ditemukan!',
							icon: 'info',
							confirmButtonText: 'OK'
						});
						return;
					}

					// Fill form with data
					$('#txtNoInvoice').val(response.NO_SJ || '');
					$('#txtNoFaktur').val(response.NO_FAKTUR || '');
					$('#txtNamaPlgn').val(response.NAMA || '');
					$('#txtAlamatPlgn').val(response.ALAMAT || '');
					$('#txtNPWP').val(response.NPWP || '');
					$('#txtMasaPajak').val(response.MASA_PAJAK || '');

					// Fill summary
					$('#txtTotalDPP').val(parseInt(response.DPPX || 0).toLocaleString('id-ID'));
					$('#txtTotalPPN').val(parseInt(response.PPNX || 0).toLocaleString('id-ID'));
					$('#txtTotalAmount').val(parseInt(response.TOTALX || 0).toLocaleString('id-ID'));

					// Set current status
					$('#currentNoSJ').val(response.NO_SJ);
					if (response.NO_FAKTUR && response.NO_FAKTUR !== '') {
						$('#currentStatus').val('edit');
						$('#btnSave').html('<i class="fas fa-edit"></i> UPDATE');
					} else {
						$('#currentStatus').val('new');
						$('#btnSave').html('<i class="fas fa-save"></i> SAVE');
					}
				},
				error: function(xhr) {
					let errorMessage = 'Gagal memuat data faktur';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMessage = xhr.responseJSON.error;
					}

					Swal.fire({
						title: 'Error!',
						text: errorMessage,
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Load Faktur for Edit (equivalent to cxGrid1DBTableView1DblClick in Delphi)
		function loadFakturForEdit(noSJ, kodec) {
			loadFakturByInvoice(noSJ);
		}

		// Save Faktur (equivalent to btnSimpanClick in Delphi)
		function saveFaktur() {
			const noInvoice = $('#txtNoInvoice').val().trim();
			const noFaktur = $('#txtNoFaktur').val().trim();
			const namaPlgn = $('#txtNamaPlgn').val().trim();
			const alamatPlgn = $('#txtAlamatPlgn').val().trim();
			const npwp = $('#txtNPWP').val().trim();

			// Validation
			if (noInvoice === '') {
				Swal.fire({
					title: 'Error!',
					text: 'No.Invoice tidak boleh kosong!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			if (noFaktur === '') {
				Swal.fire({
					title: 'Error!',
					text: 'No.Faktur tidak boleh kosong!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			if (namaPlgn === '') {
				Swal.fire({
					title: 'Error!',
					text: 'Nama Pelanggan tidak boleh kosong!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			if (alamatPlgn === '') {
				Swal.fire({
					title: 'Error!',
					text: 'Alamat Pelanggan tidak boleh kosong!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			if (npwp === '') {
				Swal.fire({
					title: 'Error!',
					text: 'NPWP tidak boleh kosong!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			// Check faktur validity first
			const periode = $('#txtPeriode').val();

			// Show loading
			Swal.fire({
				title: 'Validating...',
				text: 'Sedang memvalidasi data faktur',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			$.ajax({
				url: '{{ route('tfaktur.cekfaktur') }}',
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					faktur: noFaktur,
					per: periode
				},
				success: function(response) {
					if (response.success) {
						// Validation passed, proceed with save
						saveFakturData();
					}
				},
				error: function(xhr) {
					Swal.close();
					let errorMessage = 'Gagal validasi faktur';

					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMessage = xhr.responseJSON.error;
					}

					Swal.fire({
						title: 'Error!',
						text: errorMessage,
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Save Faktur Data
		function saveFakturData() {
			const noInvoice = $('#txtNoInvoice').val().trim();
			const noFaktur = $('#txtNoFaktur').val().trim();
			const namaPlgn = $('#txtNamaPlgn').val().trim();
			const alamatPlgn = $('#txtAlamatPlgn').val().trim();
			const npwp = $('#txtNPWP').val().trim();

			Swal.fire({
				title: 'Menyimpan...',
				text: 'Sedang memproses data faktur',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			$.ajax({
				url: '{{ route('tfaktur.updatefaktur') }}',
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					faktur: noFaktur,
					npwp: npwp,
					nama: namaPlgn,
					alamat: alamatPlgn,
					no_sj: noInvoice
				},
				success: function(response) {
					Swal.fire({
						title: 'Success!',
						text: 'Save Data Success',
						icon: 'success',
						confirmButtonText: 'OK'
					}).then(() => {
						refreshData();
					});
				},
				error: function(xhr) {
					Swal.close();
					let errorMessage = 'Gagal menyimpan data';

					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMessage = xhr.responseJSON.error;
					} else if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMessage = xhr.responseJSON.message;
					}

					Swal.fire({
						title: 'Error!',
						text: errorMessage,
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Generate CSV (equivalent to btnCSVClick in Delphi)
		function generateCSV() {
			const noFaktur = $('#txtNoFaktur').val().trim();
			const alamatPlgn = $('#txtAlamatPlgn').val().trim();

			if (noFaktur === '') {
				Swal.fire({
					title: 'Error!',
					text: 'No.Faktur tidak boleh kosong!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			// Show loading
			Swal.fire({
				title: 'Generating CSV...',
				text: 'Sedang membuat file CSV',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			$.ajax({
				url: '{{ route('tfaktur.generatecsv') }}',
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					faktur: noFaktur,
					alamat: alamatPlgn
				},
				success: function(response) {
					Swal.close();

					if (response.success) {
						// Create and download CSV file
						const blob = new Blob([response.content], {
							type: 'text/csv'
						});
						const url = window.URL.createObjectURL(blob);
						const a = document.createElement('a');
						a.href = url;
						a.download = response.filename;
						document.body.appendChild(a);
						a.click();
						document.body.removeChild(a);
						window.URL.revokeObjectURL(url);

						Swal.fire({
							title: 'Success!',
							text: response.message,
							icon: 'success',
							confirmButtonText: 'OK'
						});
					}
				},
				error: function(xhr) {
					Swal.close();
					let errorMessage = 'Gagal generate CSV';

					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMessage = xhr.responseJSON.error;
					}

					Swal.fire({
						title: 'Error!',
						text: errorMessage,
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Refresh Data (equivalent to btnRefreshClick in Delphi)
		function refreshData() {
			// Clear all fields
			$('#txtNoInvoice, #txtNoFaktur, #txtNamaPlgn, #txtAlamatPlgn, #txtNPWP, #txtMasaPajak').val('');
			$('#txtTotalDPP, #txtTotalPPN, #txtTotalAmount').val('');
			$('#currentNoSJ').val('');
			$('#currentStatus').val('new');
			$('#btnSave').html('<i class="fas fa-save"></i> SAVE');

			// Reload available faktur
			loadAvailableFaktur();
		}

		// Print Faktur
		function printFaktur() {
			const noSJ = $('#currentNoSJ').val();

			if (!noSJ) {
				Swal.fire({
					title: 'Warning!',
					text: 'Pilih invoice yang akan dicetak!',
					icon: 'warning',
					confirmButtonText: 'OK'
				});
				return;
			}

			window.open(`{{ route('tfaktur_print_single') }}?no_sj=${noSJ}`, '_blank');
		}

		// Print Range Function
		function printRange() {
			const txtFakturDari = document.getElementById('txtFakturDari').value.trim();
			const txtFakturSampai = document.getElementById('txtFakturSampai').value.trim();

			if (!txtFakturDari || !txtFakturSampai) {
				Swal.fire({
					title: 'Error!',
					text: 'No Faktur Dari dan Sampai harus diisi!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			window.open(`{{ route('tfaktur_print_range') }}?start_sj=${txtFakturDari}&end_sj=${txtFakturSampai}`, '_blank');
		}

		// Print Report Function
		function printReport() {
			window.open('{{ route('tfaktur_jasper') }}', '_blank');
		}

		// Print All Faktur Function
		function printAllFaktur() {
			window.open('{{ route('tfaktur_print_all') }}', '_blank');
		}

		// Utility function to format date
		function formatDate(dateString) {
			if (!dateString) return '';

			const date = new Date(dateString);
			const day = String(date.getDate()).padStart(2, '0');
			const month = String(date.getMonth() + 1).padStart(2, '0');
			const year = date.getFullYear();

			return `${day}-${month}-${year}`;
		}

		// Utility function to format currency
		function formatCurrency(amount) {
			if (!amount) return '0';
			return parseInt(amount).toLocaleString('id-ID');
		}

		// Delete function (disabled like in original)
		function deleteRow(link) {
			Swal.fire({
				title: 'Error!',
				text: 'Hapus data tidak diizinkan!',
				icon: 'error',
				confirmButtonText: 'OK'
			});
		}

		// Handle keyboard shortcuts
		$(document).on('keydown', function(e) {
			// F5 for refresh
			if (e.key === 'F5') {
				e.preventDefault();
				refreshData();
			}

			// Ctrl+S for save
			if (e.ctrlKey && e.key === 's') {
				e.preventDefault();
				saveFaktur();
			}

			// Ctrl+P for print
			if (e.ctrlKey && e.key === 'p') {
				e.preventDefault();
				printFaktur();
			}
		});

		// Auto-format NPWP input
		$('#txtNPWP').on('input', function() {
			let value = $(this).val().replace(/\D/g, ''); // Remove non-digits

			// Format NPWP: XX.XXX.XXX.X-XXX.XXX
			if (value.length > 0) {
				if (value.length <= 2) {
					value = value;
				} else if (value.length <= 5) {
					value = value.substring(0, 2) + '.' + value.substring(2);
				} else if (value.length <= 8) {
					value = value.substring(0, 2) + '.' + value.substring(2, 5) + '.' + value.substring(5);
				} else if (value.length <= 9) {
					value = value.substring(0, 2) + '.' + value.substring(2, 5) + '.' + value.substring(5, 8) + '.' + value.substring(8);
				} else if (value.length <= 12) {
					value = value.substring(0, 2) + '.' + value.substring(2, 5) + '.' + value.substring(5, 8) + '.' + value.substring(8, 9) + '-' +
						value.substring(9);
				} else if (value.length <= 15) {
					value = value.substring(0, 2) + '.' + value.substring(2, 5) + '.' + value.substring(5, 8) + '.' + value.substring(8, 9) + '-' +
						value.substring(9, 12) + '.' + value.substring(12);
				}
			}

			$(this).val(value);
		});

		// Auto-format No.Faktur input (ensure proper format)
		$('#txtNoFaktur').on('input', function() {
			let value = $(this).val().toUpperCase();

			// Remove any characters that are not alphanumeric or dash
			value = value.replace(/[^A-Z0-9-]/g, '');

			$(this).val(value);
		});

		// Validation on blur for No.Faktur
		$('#txtNoFaktur').on('blur', function() {
			const value = $(this).val().trim();

			if (value && value.length > 0) {
				// Basic format validation (can be customized based on requirements)
				const fakturPattern = /^[A-Z]{2}\d{4}-\d{4}$/; // Example: FK2501-0001

				if (!fakturPattern.test(value)) {
					Swal.fire({
						title: 'Warning!',
						text: 'Format No.Faktur mungkin tidak sesuai. Contoh format: FK2501-0001',
						icon: 'warning',
						confirmButtonText: 'OK'
					});
				}
			}
		});

		// Auto-complete customer data when NPWP is entered
		$('#txtNPWP').on('blur', function() {
			const npwp = $(this).val().trim();

			if (npwp && npwp.length >= 15) {
				// Try to get customer data by NPWP
				$.ajax({
					url: '{{ route('tfaktur.get-supplier') }}',
					method: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						npwp: npwp
					},
					success: function(response) {
						if (response.nam) {
							$('#txtNamaPlgn').val(response.nam);
						}
						if (response.alm) {
							$('#txtAlamatPlgn').val(response.alm);
						}
					},
					error: function(xhr) {
						// Silently fail - customer might be new
						console.log('Customer not found by NPWP');
					}
				});
			}
		});

		// Export functionality
		function exportData(format) {
			const periode = $('#txtPeriode').val();

			if (!periode) {
				Swal.fire({
					title: 'Error!',
					text: 'Periode tidak tersedia!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			// Show loading
			Swal.fire({
				title: 'Exporting...',
				text: 'Sedang memproses export data',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			window.location.href = `{{ route('tfaktur') }}/export?format=${format}&per=${periode}`;

			// Close loading after a delay
			setTimeout(() => {
				Swal.close();
			}, 2000);
		}

		// Search functionality
		function searchFaktur(searchTerm) {
			const table = $('#availableFakturTable tbody');
			const rows = table.find('tr');

			if (!searchTerm) {
				rows.show();
				return;
			}

			rows.each(function() {
				const row = $(this);
				const text = row.text().toLowerCase();

				if (text.includes(searchTerm.toLowerCase())) {
					row.show();
				} else {
					row.hide();
				}
			});
		}

		// Add search box to the left panel
		function addSearchBox() {
			const searchHtml = `
				<div class="border-bottom p-2">
					<div class="input-group input-group-sm">
						<input type="text" class="form-control" id="searchFaktur" placeholder="Cari invoice/faktur...">
						<div class="input-group-append">
							<button class="btn btn-outline-secondary" type="button" onclick="searchFaktur('')">
								<i class="fas fa-times"></i>
							</button>
						</div>
					</div>
				</div>
			`;

			$('#availableFakturTable').parent().prepend(searchHtml);

			// Add search event
			$('#searchFaktur').on('input', function() {
				searchFaktur($(this).val());
			});
		}

		// Initialize search box after DOM is ready
		$(document).ready(function() {
			setTimeout(addSearchBox, 100);
		});

		// Handle window resize
		$(window).on('resize', function() {
			// Adjust table heights if needed
			const windowHeight = $(window).height();
			const availableHeight = windowHeight - 300; // Adjust based on header/footer

			$('.left-panel .card-body, .invoice-table').css('max-height', availableHeight + 'px');
		});

		// Initialize on load
		$(window).trigger('resize');

		// Context menu for table rows (right-click functionality)
		$(document).on('contextmenu', '#availableFakturTable tbody tr', function(e) {
			e.preventDefault();

			const noSJ = $(this).data('no-sj');
			const noFaktur = $(this).data('no-faktur');

			if (!noSJ) return;

			const contextMenu = `
				<div class="context-menu" style="position: fixed; top: ${e.pageY}px; left: ${e.pageX}px; background: white; border: 1px solid #ccc; border-radius: 4px; padding: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 1000;">
					<div class="context-item" onclick="loadFakturForEdit('${noSJ}', ''); $('.context-menu').remove();" style="padding: 5px 10px; cursor: pointer; border-bottom: 1px solid #eee;">
						<i class="fas fa-edit"></i> Edit
					</div>
					<div class="context-item" onclick="printSingleFaktur('${noSJ}'); $('.context-menu').remove();" style="padding: 5px 10px; cursor: pointer; border-bottom: 1px solid #eee;">
						<i class="fas fa-print"></i> Print
					</div>
					${noFaktur ? `<div class="context-item" onclick="exportSingleCSV('${noFaktur}'); $('.context-menu').remove();" style="padding: 5px 10px; cursor: pointer;">
														<i class="fas fa-file-csv"></i> Export CSV
													</div>` : ''}
				</div>
			`;

			$('.context-menu').remove(); // Remove existing menu
			$('body').append(contextMenu);

			// Remove menu when clicking elsewhere
			$(document).one('click', function() {
				$('.context-menu').remove();
			});
		});

		// Print single faktur from context menu
		function printSingleFaktur(noSJ) {
			window.open(`{{ route('tfaktur_print_single') }}?no_sj=${noSJ}`, '_blank');
		}

		// Export single CSV from context menu
		function exportSingleCSV(noFaktur) {
			$.ajax({
				url: '{{ route('tfaktur.generatecsv') }}',
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					faktur: noFaktur,
					alamat: ''
				},
				success: function(response) {
					if (response.success) {
						const blob = new Blob([response.content], {
							type: 'text/csv'
						});
						const url = window.URL.createObjectURL(blob);
						const a = document.createElement('a');
						a.href = url;
						a.download = response.filename;
						document.body.appendChild(a);
						a.click();
						document.body.removeChild(a);
						window.URL.revokeObjectURL(url);
					}
				},
				error: function(xhr) {
					Swal.fire({
						title: 'Error!',
						text: 'Gagal export CSV',
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Auto-save functionality (draft save every 30 seconds if data changed)
		let lastData = '';
		let dataChanged = false;

		function checkDataChanges() {
			const currentData = JSON.stringify({
				noInvoice: $('#txtNoInvoice').val(),
				noFaktur: $('#txtNoFaktur').val(),
				namaPlgn: $('#txtNamaPlgn').val(),
				alamatPlgn: $('#txtAlamatPlgn').val(),
				npwp: $('#txtNPWP').val()
			});

			if (currentData !== lastData) {
				dataChanged = true;
				lastData = currentData;
			}
		}

		// Check for changes every 5 seconds
		setInterval(checkDataChanges, 5000);

		// Warning before page unload if data changed
		$(window).on('beforeunload', function(e) {
			if (dataChanged) {
				const message = 'Ada perubahan data yang belum disimpan. Yakin ingin meninggalkan halaman?';
				e.returnValue = message;
				return message;
			}
		});

		// Clear data changed flag after successful save
		function clearDataChangedFlag() {
			dataChanged = false;
			lastData = JSON.stringify({
				noInvoice: $('#txtNoInvoice').val(),
				noFaktur: $('#txtNoFaktur').val(),
				namaPlgn: $('#txtNamaPlgn').val(),
				alamatPlgn: $('#txtAlamatPlgn').val(),
				npwp: $('#txtNPWP').val()
			});
		}

		// Update saveFakturData to clear flag
		const originalSaveFakturData = saveFakturData;
		saveFakturData = function() {
			originalSaveFakturData();
			// Clear flag in success callback
			const originalAjax = $.ajax;
			$.ajax = function(options) {
				const originalSuccess = options.success;
				options.success = function(response) {
					if (originalSuccess) originalSuccess(response);
					clearDataChangedFlag();
				};
				return originalAjax(options);
			};
		};
	</script>
@endsection
